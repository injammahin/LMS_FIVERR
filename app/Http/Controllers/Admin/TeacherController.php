<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'name'       => ['required', 'string', 'max:255'],
            'password'   => ['required', 'string', 'min:6'],
            'login_type' => ['required', Rule::in(['email', 'username'])],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email'),
                Rule::requiredIf(fn () => $request->login_type === 'email'),
            ],
            'username' => [
                'nullable',
                Rule::unique('users', 'username'),
                Rule::requiredIf(fn () => $request->login_type === 'username'),
            ],
        ]);

        $teacher = User::create([
            'name' => $request->name,
            'email' => $request->login_type === 'email' ? $request->email : null,
            'username' => $request->login_type === 'username' ? $request->username : null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password, // ⚠️ not recommended in production
            'role' => 'teacher',
            'is_active' => true,
        ]);

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
            'name'       => ['required', 'string', 'max:255'],
            'password'   => ['nullable', 'string', 'min:6'],
            'login_type' => ['required', Rule::in(['email', 'username'])],

            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($teacher->id),
                Rule::requiredIf(fn () => $request->login_type === 'email'),
            ],
            'username' => [
                'nullable',
                Rule::unique('users', 'username')->ignore($teacher->id),
                Rule::requiredIf(fn () => $request->login_type === 'username'),
            ],
        ]);

        $teacher->name = $request->name;

        // ✅ Only one login method is stored
        $teacher->email = $request->login_type === 'email' ? $request->email : null;
        $teacher->username = $request->login_type === 'username' ? $request->username : null;

        if ($request->filled('password')) {
            $teacher->password = Hash::make($request->password);
            $teacher->plain_password = $request->password; // ⚠️ not recommended in production
        }

        $teacher->save();

        return redirect()
            ->route('admin.teachers.index')
            ->with('success', 'Teacher updated successfully.');
    }

    public function destroy(User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        // detach relation if exists
        if (method_exists($teacher, 'coursesTeaching')) {
            $teacher->coursesTeaching()->detach();
        }

        $teacher->delete();

        return back()->with('success', 'Teacher deleted successfully.');
    }

    /**
     * ✅ Admin suspend/reactivate teacher
     */
    public function toggleStatus(User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $teacher->is_active = !$teacher->is_active;
        $teacher->save();

        return back()->with(
            'success',
            $teacher->is_active ? 'Teacher activated successfully.' : 'Teacher suspended successfully.'
        );
    }

    /**
     * ✅ Assign Courses UI
     * GET: /admin/teachers/{teacher}/courses
     */
    public function editCourses(Request $request, User $teacher)
    {
        abort_if($teacher->role !== 'teacher', 404);

        $divisionId = $request->get('division_id');
        $subjectId  = $request->get('subject_id');
        $search     = $request->get('search');

        $divisions = Division::orderBy('name')->get();

        $subjectsQuery = Subject::with('division')->orderBy('name');
        if ($divisionId) {
            $subjectsQuery->where('division_id', $divisionId);
        }
        $subjects = $subjectsQuery->get();

        $coursesQuery = Course::with(['subject.division'])->orderBy('title');

        if ($subjectId) {
            $coursesQuery->where('subject_id', $subjectId);
        } elseif ($divisionId) {
            $coursesQuery->whereHas('subject', fn ($q) => $q->where('division_id', $divisionId));
        }

        if ($search) {
            $coursesQuery->where('title', 'like', "%{$search}%");
        }

        $courses = $coursesQuery->paginate(15)->withQueryString();

        $assigned = method_exists($teacher, 'coursesTeaching')
            ? $teacher->coursesTeaching()->pluck('courses.id')->toArray()
            : [];

        return view('admin.teachers.courses', compact(
            'teacher',
            'courses',
            'assigned',
            'divisions',
            'subjects',
            'divisionId',
            'subjectId',
            'search'
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

        if (!method_exists($teacher, 'coursesTeaching')) {
            return back()->with('error', 'coursesTeaching() relation not found on User model.');
        }

        $teacher->coursesTeaching()->sync($courseIds);

        return redirect()
            ->route('admin.teachers.courses.edit', $teacher->id)
            ->with('success', 'Courses assigned successfully.');
    }
}