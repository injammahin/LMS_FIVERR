<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentSubmissionController extends Controller
{
    public function index(Assignment $assignment)
    {
        $submissions = $assignment->submissions()->with('user')->latest()->paginate(20);
        return view('admin.assignments.submissions.index', compact('assignment','submissions'));
    }

    public function show(Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_if($submission->assignment_id !== $assignment->id, 404);
        $submission->load('user');
        return view('admin.assignments.submissions.show', compact('assignment','submission'));
    }

    public function grade(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        abort_if($submission->assignment_id !== $assignment->id, 404);

        $validated = $request->validate([
            'feedback' => ['nullable','string'],
            'feedback_file' => ['nullable','file','max:51200','mimes:pdf,doc,docx,zip,jpg,jpeg,png,webp'],
            'marks_awarded' => ['nullable','integer','min:0','max:100000'],
            'is_passed' => ['nullable','boolean'],
        ]);

        // validate grading type
        if ($assignment->grading_type === 'points') {
            if ($validated['marks_awarded'] === null) {
                return back()->withErrors(['marks_awarded' => 'Marks is required for points grading.'])->withInput();
            }
            if ($assignment->total_marks !== null && $validated['marks_awarded'] > $assignment->total_marks) {
                return back()->withErrors(['marks_awarded' => 'Marks cannot exceed total marks.'])->withInput();
            }
        } else {
            // pass_fail
            if (!isset($validated['is_passed'])) {
                return back()->withErrors(['is_passed' => 'Pass/Fail is required.'])->withInput();
            }
            $validated['marks_awarded'] = null;
        }

        // upload feedback file
        if ($request->hasFile('feedback_file')) {
            if ($submission->feedback_file) Storage::disk('public')->delete($submission->feedback_file);
            $submission->feedback_file = $request->file('feedback_file')->store('assignment_feedback', 'public');
        }

        $submission->update([
            'feedback' => $validated['feedback'] ?? null,
            'marks_awarded' => $validated['marks_awarded'] ?? null,
            'is_passed' => isset($validated['is_passed']) ? (bool)$validated['is_passed'] : null,
            'feedback_file' => $submission->feedback_file,
            'status' => 'graded',
        ]);

        return redirect()
            ->route('admin.assignments.submissions.show', [$assignment->id, $submission->id])
            ->with('success', 'Submission graded successfully.');
    }
}