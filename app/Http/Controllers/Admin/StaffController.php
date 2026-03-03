<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Division;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search  = $request->search;

        $staffs = User::where('role', 'staff')
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

        return view('admin.staffs.index', compact('staffs'));
    }

    public function create()
    {
        return view('admin.staffs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|min:6',
            'email' => 'nullable|required_without:username|email|unique:users,email',
            'username' => 'nullable|required_without:email|unique:users,username',
        ]);

        $staff = User::create([
            'name' => $request->name,
            'email' => $request->email ?: null,
            'username' => $request->username ?: null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'staff',
            'is_active' => 1,
        ]);

        return redirect()
            ->route('admin.staffs.courses.edit', $staff->id)
            ->with('success', 'Staff created. Now assign courses.');
    }

    public function edit(User $staff)
    {
        abort_if($staff->role !== 'staff', 404);
        return view('admin.staffs.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $staff->id,
            'username' => 'nullable|unique:users,username,' . $staff->id,
            'password' => 'nullable|min:6',
        ]);

        $staff->name = $request->name;
        $staff->email = $request->email;
        $staff->username = $request->username;

        if ($request->password) {
            $staff->password = Hash::make($request->password);
            $staff->plain_password = $request->password;
        }

        $staff->save();

        return redirect()->route('admin.staffs.index')
            ->with('success', 'Staff updated successfully.');
    }

    public function destroy(User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $staff->coursesSupporting()->detach();
        $staff->delete();

        return back()->with('success', 'Staff deleted successfully.');
    }

    // ✅ Suspend/Activate staff
    public function toggleStatus(User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $staff->is_active = !$staff->is_active;
        $staff->save();

        return back()->with('success', $staff->is_active ? 'Staff activated.' : 'Staff suspended.');
    }

    /**
     * ✅ Assign Courses UI
     * GET: /admin/staffs/{staff}/courses
     */
    public function editCourses(Request $request, User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $divisionId = $request->get('division_id');
        $subjectId  = $request->get('subject_id');
        $search     = $request->get('search');

        $divisions = Division::orderBy('name')->get();

        $subjectsQuery = Subject::with('division')->orderBy('name');
        if ($divisionId) $subjectsQuery->where('division_id', $divisionId);
        $subjects = $subjectsQuery->get();

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

        $assigned = $staff->coursesSupporting()->pluck('courses.id')->toArray();

        return view('admin.staffs.courses', compact(
            'staff','courses','assigned',
            'divisions','subjects',
            'divisionId','subjectId','search'
        ));
    }

    /**
     * ✅ Save assigned courses
     * POST: /admin/staffs/{staff}/courses
     */
    public function updateCourses(Request $request, User $staff)
    {
        abort_if($staff->role !== 'staff', 404);

        $validated = $request->validate([
            'course_ids' => ['nullable','array'],
            'course_ids.*' => ['integer','exists:courses,id'],
        ]);

        $staff->coursesSupporting()->sync($validated['course_ids'] ?? []);

        return back()->with('success', 'Courses assigned successfully.');
    }
}