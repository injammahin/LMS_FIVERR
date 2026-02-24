<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        User::create([
            'name' => $request->name,
            'email' => $request->email ?: null,
            'username' => $request->username ?: null,
            'password' => Hash::make($request->password),
            'plain_password' => $request->password,
            'role' => 'teacher',
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'Teacher created successfully.');
    }

    public function edit(User $teacher)
    {
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email|unique:users,email,' . $teacher->id,
            'username' => 'nullable|unique:users,username,' . $teacher->id,
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
        $teacher->delete();
        return back()->with('success', 'Teacher deleted successfully.');
    }

    
}