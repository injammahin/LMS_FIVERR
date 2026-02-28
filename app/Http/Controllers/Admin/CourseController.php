<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $subjectId = $request->get('subject_id');
        $divisionId = $request->get('division_id');

        $divisions = Division::orderBy('name')->get();

        $subjectsQuery = Subject::with('division')->orderBy('name');
        if ($divisionId) $subjectsQuery->where('division_id', $divisionId);
        $subjects = $subjectsQuery->get();

        $coursesQuery = Course::with(['subject.division'])->latest();
        if ($subjectId) $coursesQuery->where('subject_id', $subjectId);

        $courses = $coursesQuery->paginate(10)->withQueryString();

        return view('admin.courses.index', compact(
            'courses', 'divisions', 'subjects', 'divisionId', 'subjectId'
        ));
    }

    public function create(Request $request)
    {
        $divisions = Division::orderBy('name')->get();
        $subjects  = Subject::with('division')->orderBy('name')->get();

        $selectedSubjectId = $request->get('subject_id');
        $selectedDivisionId = $request->get('division_id');

        return view('admin.courses.create', compact(
            'divisions', 'subjects', 'selectedSubjectId', 'selectedDivisionId'
        ));
    }

    // ✅ BULK CREATE courses under one subject
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'titles'     => ['required', 'array', 'min:1'],
            'titles.*'   => ['required', 'string', 'max:255'],
            'status'     => ['required', 'in:draft,published'],
            'thumbnail'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description'=> ['nullable', 'string'],
        ]);

        $subjectId = (int) $validated['subject_id'];

        // thumbnail (same thumbnail for all bulk-created courses - optional)
        $thumbPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbPath = $request->file('thumbnail')->store('courses', 'public');
        }

        $titles = collect($validated['titles'])
            ->map(fn($t) => trim($t))
            ->filter()
            ->unique(fn($t) => mb_strtolower($t))
            ->values();

        if ($titles->isEmpty()) {
            return back()->withErrors(['titles' => 'Please enter at least one course title.'])->withInput();
        }

        $toInsert = [];
        foreach ($titles as $title) {
            $slug = Str::slug($title);

            $base = $slug;
            $i = 1;
            while (
                Course::where('subject_id', $subjectId)->where('slug', $slug)->exists()
                || collect($toInsert)->contains(fn($row) => $row['slug'] === $slug)
            ) {
                $slug = $base . '-' . $i++;
            }

            $toInsert[] = [
                'subject_id'   => $subjectId,
                'title'        => $title,
                'slug'         => $slug,
                'description'  => $validated['description'] ?? null,
                'thumbnail'    => $thumbPath,
                'status'       => $validated['status'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        Course::insert($toInsert);

        return redirect()
            ->route('admin.courses.index', ['subject_id' => $subjectId])
            ->with('success', count($toInsert) . ' course(s) created successfully.');
    }

    public function edit(Course $course)
    {
        $divisions = Division::orderBy('name')->get();
        $subjects  = Subject::with('division')->orderBy('name')->get();

        return view('admin.courses.edit', compact('course','divisions','subjects'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title'      => ['required', 'string', 'max:255'],
            'status'     => ['required', 'in:draft,published'],
            'description'=> ['nullable', 'string'],
            'thumbnail'  => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['title']);
        $base = $slug;
        $i = 1;
        while (
            Course::where('subject_id', $validated['subject_id'])
                ->where('slug', $slug)
                ->where('id', '!=', $course->id)
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        if ($request->boolean('remove_thumbnail') && $course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
            $course->thumbnail = null;
        }

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
            $course->thumbnail = $request->file('thumbnail')->store('courses', 'public');
        }

        $course->update([
            'subject_id'  => $validated['subject_id'],
            'title'       => $validated['title'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? null,
            'status'      => $validated['status'],
            'thumbnail'   => $course->thumbnail,
        ]);

        return redirect()
            ->route('admin.courses.index', ['subject_id' => $course->subject_id])
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $subjectId = $course->subject_id;

        if ($course->thumbnail) Storage::disk('public')->delete($course->thumbnail);
        $course->delete();

        return redirect()
            ->route('admin.courses.index', ['subject_id' => $subjectId])
            ->with('success', 'Course deleted successfully.');
    }
}