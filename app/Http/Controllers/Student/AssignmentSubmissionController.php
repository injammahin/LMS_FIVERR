<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionController extends Controller
{
    public function store(Request $request, Course $course, Assignment $assignment)
    {
        abort_if($assignment->course_id !== $course->id, 404);

        $user = auth()->user();

        // ✅ Division guard (same as your other controllers)
        $divisionId = optional(optional($course->subject)->division)->id;
        abort_if((int)$user->division_id !== (int)$divisionId, 403);

        // ✅ published only
        abort_if(($assignment->status ?? 'draft') !== 'published', 403);

        // ✅ attempt limit
        if (!empty($assignment->max_attempts)) {
            $used = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('user_id', $user->id)
                ->count();

            if ($used >= (int)$assignment->max_attempts) {
                return back()->with('error', 'You have reached the maximum attempts for this assignment.');
            }
        }

        // ✅ due date / late rules
        $now = now();
        if (!empty($assignment->due_at)) {
            $due = $assignment->due_at;

            if ($now->greaterThan($due)) {
                // late
                if (!$assignment->allow_late) {
                    return back()->with('error', 'Submission is closed. The due date has passed.');
                }

                if (!empty($assignment->late_until) && $now->greaterThan($assignment->late_until)) {
                    return back()->with('error', 'Submission is closed. Late submission window is over.');
                }
            }
        }

        // ✅ Validation based on submission_type
        $rules = [];

        if (in_array($assignment->submission_type, ['text', 'text_file'])) {
            $rules['submission_text'] = ['nullable', 'string', 'max:100000'];
        }

        if (in_array($assignment->submission_type, ['file', 'text_file'])) {
            $rules['submission_file'] = ['nullable', 'file', 'max:51200', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'];
        }

        $validated = $request->validate($rules);

        // ✅ require at least one field depending on type
        $hasText = !empty(trim((string)($validated['submission_text'] ?? '')));
        $hasFile = $request->hasFile('submission_file');

        if ($assignment->submission_type === 'text' && !$hasText) {
            return back()->withErrors(['submission_text' => 'Text submission is required.'])->withInput();
        }

        if ($assignment->submission_type === 'file' && !$hasFile) {
            return back()->withErrors(['submission_file' => 'File submission is required.'])->withInput();
        }

        if ($assignment->submission_type === 'text_file' && !$hasText && !$hasFile) {
            return back()->withErrors(['submission_text' => 'Please submit text or upload a file.'])->withInput();
        }

        // ✅ Upload file if provided
        $filePath = null;
        if ($hasFile) {
            $filePath = $request->file('submission_file')->store('assignment_submissions', 'public');
        }

        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'submission_text' => $hasText ? $validated['submission_text'] : null,
            'submission_file' => $filePath,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        return back()->with('success', 'Assignment submitted successfully.');
    }
}