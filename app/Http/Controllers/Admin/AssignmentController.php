<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    public function index(Course $course)
    {
        $assignments = $course->assignments()->latest()->paginate(15);
        return view('admin.assignments.index', compact('course','assignments'));
    }

    public function create(Course $course)
    {
        return view('admin.assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],
            'attachment' => ['nullable','file','max:51200','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'],

            'submission_type' => ['required','in:text,file,text_file'],
            'grading_type' => ['required','in:points,pass_fail'],
            'total_marks' => ['nullable','integer','min:1','max:10000'],
            'max_attempts' => ['nullable','integer','min:1','max:100'],

            'due_at' => ['nullable','date'],
            'allow_late' => ['nullable','boolean'],
            'late_until' => ['nullable','date'],

            'status' => ['required','in:draft,published'],
        ]);

        // if points grading, total_marks recommended
        if (($validated['grading_type'] ?? null) === 'points' && empty($validated['total_marks'])) {
            return back()->withErrors(['total_marks' => 'Total marks is required for points grading.'])->withInput();
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('assignments', 'public');
        }

        $allowLate = $request->boolean('allow_late');

        if (!$allowLate) {
            $validated['late_until'] = null;
        }

        Assignment::create([
            'course_id' => $course->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'attachment' => $attachmentPath,

            'submission_type' => $validated['submission_type'],
            'grading_type' => $validated['grading_type'],
            'total_marks' => $validated['total_marks'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? null,

            'due_at' => $validated['due_at'] ?? null,
            'allow_late' => $allowLate,
            'late_until' => $validated['late_until'] ?? null,

            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.courses.assignments.index', $course->id)
            ->with('success', 'Assignment created successfully.');
    }

    public function edit(Course $course, Assignment $assignment)
    {
        abort_if($assignment->course_id !== $course->id, 404);
        return view('admin.assignments.edit', compact('course','assignment'));
    }

    public function update(Request $request, Course $course, Assignment $assignment)
    {
        abort_if($assignment->course_id !== $course->id, 404);

        $validated = $request->validate([
            'title' => ['required','string','max:255'],
            'description' => ['nullable','string'],

            'attachment' => ['nullable','file','max:51200','mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,jpeg,png,webp,zip'],
            'remove_attachment' => ['nullable','boolean'],

            'submission_type' => ['required','in:text,file,text_file'],
            'grading_type' => ['required','in:points,pass_fail'],
            'total_marks' => ['nullable','integer','min:1','max:10000'],
            'max_attempts' => ['nullable','integer','min:1','max:100'],

            'due_at' => ['nullable','date'],
            'allow_late' => ['nullable','boolean'],
            'late_until' => ['nullable','date'],

            'status' => ['required','in:draft,published'],
        ]);

        if (($validated['grading_type'] ?? null) === 'points' && empty($validated['total_marks'])) {
            return back()->withErrors(['total_marks' => 'Total marks is required for points grading.'])->withInput();
        }

        if ($request->boolean('remove_attachment') && $assignment->attachment) {
            Storage::disk('public')->delete($assignment->attachment);
            $assignment->attachment = null;
        }

        if ($request->hasFile('attachment')) {
            if ($assignment->attachment) Storage::disk('public')->delete($assignment->attachment);
            $assignment->attachment = $request->file('attachment')->store('assignments', 'public');
        }

        $allowLate = $request->boolean('allow_late');
        if (!$allowLate) $validated['late_until'] = null;

        $assignment->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'attachment' => $assignment->attachment,

            'submission_type' => $validated['submission_type'],
            'grading_type' => $validated['grading_type'],
            'total_marks' => $validated['total_marks'] ?? null,
            'max_attempts' => $validated['max_attempts'] ?? null,

            'due_at' => $validated['due_at'] ?? null,
            'allow_late' => $allowLate,
            'late_until' => $validated['late_until'] ?? null,

            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.courses.assignments.index', $course->id)
            ->with('success', 'Assignment updated successfully.');
    }

    public function destroy(Course $course, Assignment $assignment)
    {
        abort_if($assignment->course_id !== $course->id, 404);

        if ($assignment->attachment) Storage::disk('public')->delete($assignment->attachment);

        $assignment->delete();

        return redirect()
            ->route('admin.courses.assignments.index', $course->id)
            ->with('success', 'Assignment deleted successfully.');
    }
}