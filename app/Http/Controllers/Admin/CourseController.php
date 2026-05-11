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
        /*
        |--------------------------------------------------------------------------
        | Divisions
        |--------------------------------------------------------------------------
        | Division is now required for this page.
        | If no division is selected, first division will be selected by default.
        */
        $divisions = Division::orderBy('level')
            ->orderBy('name')
            ->get();

        $firstDivision = $divisions->first();

        if (!$firstDivision) {
            $courses = Course::with(['subject.division'])
                ->orderBy('id')
                ->paginate(10)
                ->withQueryString();

            $subjects = collect();
            $divisionId = null;
            $subjectId = null;
            $courseRuleMap = [];

            return view('admin.courses.index', compact(
                'courses',
                'divisions',
                'subjects',
                'divisionId',
                'subjectId',
                'courseRuleMap'
            ));
        }

        $divisionId = $request->filled('division_id')
            ? (int) $request->get('division_id')
            : (int) $firstDivision->id;

        $validDivision = $divisions->firstWhere('id', $divisionId);

        if (!$validDivision) {
            return redirect()->route('admin.courses.index', [
                'division_id' => $firstDivision->id,
            ]);
        }

        $subjectId = $request->filled('subject_id')
            ? (int) $request->get('subject_id')
            : null;

        /*
        |--------------------------------------------------------------------------
        | Validate Subject Against Selected Division
        |--------------------------------------------------------------------------
        | If subject does not belong to selected division, reset subject filter.
        */
        if ($subjectId) {
            $subjectExists = Subject::where('id', $subjectId)
                ->where('division_id', $divisionId)
                ->exists();

            if (!$subjectExists) {
                return redirect()->route('admin.courses.index', [
                    'division_id' => $divisionId,
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Subjects
        |--------------------------------------------------------------------------
        | Only show subjects from selected division.
        */
        $subjects = Subject::with('division')
            ->where('division_id', $divisionId)
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Courses
        |--------------------------------------------------------------------------
        | Always filter courses by selected division.
        */
        $coursesQuery = Course::with(['subject.division'])
            ->whereHas('subject', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            });

        if ($subjectId) {
            $coursesQuery->where('subject_id', $subjectId);
        }

        $courses = $coursesQuery
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Division-wise Course Rule Map
        |--------------------------------------------------------------------------
        | Assignment after every 5 courses.
        | Quiz after every 45 courses.
        |
        | Numbering resets per division.
        */
        $courseRuleMap = [];

        $divisionCourses = Course::with(['subject:id,division_id'])
            ->whereHas('subject', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            })
            ->orderBy('id')
            ->get();

        foreach ($divisionCourses->values() as $index => $course) {
            $courseNumber = $index + 1;

            $courseRuleMap[$course->id] = [
                'division_id' => $divisionId,
                'course_number' => $courseNumber,
                'show_assignment' => $courseNumber % 5 === 0,
                'show_quiz' => $courseNumber % 45 === 0,
            ];
        }

        return view('admin.courses.index', compact(
            'courses',
            'divisions',
            'subjects',
            'divisionId',
            'subjectId',
            'courseRuleMap'
        ));
    }

    public function create(Request $request)
    {
        $divisions = Division::orderBy('level')
            ->orderBy('name')
            ->get();

        $selectedDivisionId = $request->get('division_id');

        if (!$selectedDivisionId && $divisions->isNotEmpty()) {
            $selectedDivisionId = $divisions->first()->id;
        }

        $subjects = Subject::with('division')
            ->when($selectedDivisionId, function ($query) use ($selectedDivisionId) {
                $query->where('division_id', $selectedDivisionId);
            })
            ->orderBy('name')
            ->get();

        $selectedSubjectId = $request->get('subject_id');

        return view('admin.courses.create', compact(
            'divisions',
            'subjects',
            'selectedSubjectId',
            'selectedDivisionId'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'titles' => ['required', 'array', 'min:1'],
            'titles.*' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string'],
        ]);

        $subjectId = (int) $validated['subject_id'];

        $subject = Subject::findOrFail($subjectId);

        $thumbPath = null;

        if ($request->hasFile('thumbnail')) {
            $thumbPath = $request->file('thumbnail')->store('courses', 'public');
        }

        $titles = collect($validated['titles'])
            ->map(fn ($title) => trim($title))
            ->filter()
            ->unique(fn ($title) => mb_strtolower($title))
            ->values();

        if ($titles->isEmpty()) {
            return back()
                ->withErrors(['titles' => 'Please enter at least one course title.'])
                ->withInput();
        }

        $toInsert = [];

        foreach ($titles as $title) {
            $slug = Str::slug($title);
            $baseSlug = $slug;
            $counter = 1;

            while (
                Course::where('subject_id', $subjectId)->where('slug', $slug)->exists()
                || collect($toInsert)->contains(fn ($row) => $row['slug'] === $slug)
            ) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $toInsert[] = [
                'subject_id' => $subjectId,
                'title' => $title,
                'slug' => $slug,
                'description' => $validated['description'] ?? null,
                'thumbnail' => $thumbPath,
                'status' => $validated['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        Course::insert($toInsert);

        return redirect()
            ->route('admin.courses.index', [
                'division_id' => $subject->division_id,
                'subject_id' => $subjectId,
            ])
            ->with('success', count($toInsert) . ' course(s) created successfully.');
    }

    public function edit(Course $course)
    {
        $course->load('subject.division');

        $divisions = Division::orderBy('level')
            ->orderBy('name')
            ->get();

        $subjects = Subject::with('division')
            ->where('division_id', $course->subject?->division_id)
            ->orderBy('name')
            ->get();

        return view('admin.courses.edit', compact(
            'course',
            'divisions',
            'subjects'
        ));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['required', 'in:draft,published'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_thumbnail' => ['nullable', 'boolean'],
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);

        $slug = Str::slug($validated['title']);
        $baseSlug = $slug;
        $counter = 1;

        while (
            Course::where('subject_id', $validated['subject_id'])
                ->where('slug', $slug)
                ->where('id', '!=', $course->id)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter++;
        }

        if ($request->boolean('remove_thumbnail') && $course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
            $course->thumbnail = null;
        }

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }

            $course->thumbnail = $request->file('thumbnail')->store('courses', 'public');
        }

        $course->update([
            'subject_id' => $validated['subject_id'],
            'title' => $validated['title'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'],
            'thumbnail' => $course->thumbnail,
        ]);

        return redirect()
            ->route('admin.courses.index', [
                'division_id' => $subject->division_id,
                'subject_id' => $course->subject_id,
            ])
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->load('subject');

        $subjectId = $course->subject_id;
        $divisionId = $course->subject?->division_id;

        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }

        $course->delete();

        return redirect()
            ->route('admin.courses.index', [
                'division_id' => $divisionId,
                'subject_id' => $subjectId,
            ])
            ->with('success', 'Course deleted successfully.');
    }
}