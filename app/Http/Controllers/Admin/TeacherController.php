<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search;

        $teachers = User::where('role', 'teacher')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('username', 'like', "%$search%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:6',
            'email' => 'nullable|required_without:username|email|unique:users,email',
            'username' => 'nullable|required_without:email|unique:users,username',
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->email ?: null,
            'username' => $request->username ?: null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'teacher',
        ]);

        // ✅ best practice: go to assign courses page immediately
        return redirect()
            ->route('admin.teachers.courses.edit', $teacher->id)
            ->with('success', 'Teacher created successfully. Now assign courses.');
    }

    public function edit(User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $teacher->id,
            'username' => 'nullable|unique:users,username,' . $teacher->id,
            'password' => 'nullable|min:6',
        ]);

        $teacher->name = $request->name;
        $teacher->email = $request->email;
        $teacher->username = $request->username;

        if ($request->password) {
            $teacher->password = Hash::make($request->password);
            $teacher->plain_password = $request->password;
        }

        $teacher->save();

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        // optional: detach courses before delete
        $teacher->coursesTeaching()->detach();

        $teacher->delete();
        return back()->with('success', 'Teacher deleted successfully.');
    }

    /**
     * ✅ Assign Courses UI
     * GET: /admin/teachers/{teacher}/courses
     */
    public function editCourses(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        // filters
        $divisionId = $request->get('division_id');
        $subjectId  = $request->get('subject_id');
        $search     = $request->get('search');

        // dropdowns
        $divisions = Division::orderBy('name')->get();

        $subjectsQuery = Subject::with('division')->orderBy('name');
        if ($divisionId) $subjectsQuery->where('division_id', $divisionId);
        $subjects = $subjectsQuery->get();

        // course list
        $coursesQuery = Course::with(['subject.division'])->orderBy('title');

        if ($subjectId) {
            $coursesQuery->where('subject_id', $subjectId);
        } elseif ($divisionId) {
            $coursesQuery->whereHas('subject', fn($q) => $q->where('division_id', $divisionId));
        }

        if ($search) {
            $coursesQuery->where('title', 'like', "%{$search}%");
        }

        $courses = $coursesQuery->paginate(15)->withQueryString();

        // already assigned
        $assigned = $teacher->coursesTeaching()->pluck('courses.id')->toArray();

        return view('admin.teachers.courses', compact(
            'teacher', 'courses', 'assigned',
            'divisions', 'subjects',
            'divisionId', 'subjectId', 'search'
        ));
    }

    /**
     * ✅ Save assigned courses
     * POST: /admin/teachers/{teacher}/courses
     */
    public function updateCourses(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $validated = $request->validate([
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $courseIds = $validated['course_ids'] ?? [];

        // sync = match exactly selected
        $teacher->coursesTeaching()->sync($courseIds);

        return redirect()
            ->route('admin.teachers.courses.edit', $teacher->id)
            ->with('success', 'Courses assigned successfully.');
    }
}