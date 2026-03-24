<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Division;
class AssignmentSubmissionController extends Controller
{
    public function store(Request $request, Course $course, Assignment $assignment)
    {
        abort_if((int) $assignment->course_id !== (int) $course->id, 404);

        $user = auth()->user();

        // ✅ published only
        abort_if(($assignment->status ?? 'draft') !== 'published', 403);

        // ✅ attempt limit (works if you allow multiple submissions in DB)
        if (!empty($assignment->max_attempts)) {
            $used = AssignmentSubmission::where('assignment_id', $assignment->id)
                ->where('user_id', $user->id)
                ->count();

            if ($used >= (int) $assignment->max_attempts) {
                return back()->with('error', 'You have reached the maximum attempts for this assignment.');
            }
        }

        // ✅ due date / late rules
        $now = now();
        if (!empty($assignment->due_at)) {
            $due = $assignment->due_at;

            if ($now->greaterThan($due)) {
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

        if (in_array($assignment->submission_type, ['text', 'text_file'], true)) {
            $rules['submission_text'] = ['nullable', 'string', 'max:100000'];
        }

        if (in_array($assignment->submission_type, ['file', 'text_file'], true)) {
            $rules['submission_file'] = ['nullable', 'file', 'max:51200', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'];
        }

        $validated = $request->validate($rules);

        // ✅ require at least one field depending on type
        $hasText = !empty(trim((string) ($validated['submission_text'] ?? '')));
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

        /**
         * ✅ Delete previous uploaded file (ONLY for this user + this assignment)
         * We delete the latest submission file if a new file is uploaded.
         */
        $latestSubmission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        // ✅ Upload new file if provided
        $filePath = null;
        if ($hasFile) {
            // delete previous file before saving new one
            if (!empty($latestSubmission?->submission_file)) {
                Storage::disk('public')->delete($latestSubmission->submission_file);
            }

            $filePath = $request->file('submission_file')->store('assignment_submissions', 'public');
        }

        /**
         * ✅ Create new submission attempt
         * NOTE: This will work only if your DB allows multiple rows per user+assignment.
         * If you still have the unique constraint (assignment_id,user_id), use updateOrCreate instead.
         */
        AssignmentSubmission::create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'submission_text' => $hasText ? ($validated['submission_text'] ?? null) : null,
            'submission_file' => $filePath,
            'submitted_at' => now(),
            'status' => 'submitted',
        ]);

        return back()->with('success', 'Assignment submitted successfully.');
    }
}