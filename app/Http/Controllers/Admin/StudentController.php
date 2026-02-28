<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Division;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->per_page ?? 10;
        $search = $request->search;

        $students = User::where('role', 'student')
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

        return view('admin.students.index', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required|string|max:255',
            'password' => 'required|min:6',
            'email' => 'nullable|required_without:username|email|unique:users,email',
            'username' => 'nullable|required_without:email|unique:users,username',
        ]);

        User::create([
            'division_id' => $request->division_id,   // ✅ add this
            'name' => $request->name,
            'email' => $request->email ?: null,
            'username' => $request->username ?: null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'student',
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    public function update(Request $request, User $student)
    {
        $request->validate([
            'division_id' => 'required|exists:divisions,id',
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $student->id,
            'username' => 'nullable|unique:users,username,' . $student->id,
            'password' => 'nullable|min:6',
        ]);

        $student->division_id = $request->division_id; // ✅ add this
        $student->name = $request->name;
        $student->email = $request->email;
        $student->username = $request->username;

        if ($request->password) {
            $student->password = Hash::make($request->password);
            $student->plain_password = $request->password;
        }

        $student->save();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    public function destroy(User $student)
    {
        $student->delete();
        return back()->with('success', 'Student deleted successfully.');
    }
public function create()
{
    $divisions = Division::orderBy('name')->get();
    return view('admin.students.create', compact('divisions'));
}

public function edit(User $student)
{
    abort_if($student->role !== 'student', 404);

    $divisions = Division::orderBy('name')->get();
    return view('admin.students.edit', compact('student', 'divisions'));
}
}