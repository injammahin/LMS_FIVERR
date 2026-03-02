<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request)
    {
        // 👇 Get whatever you use in the login field (usually "email")
        $login = $request->input('email');

        // 👇 Support email OR username login (adjust if you only use email)
        $user = User::where('email', $login)
            ->orWhere('username', $login)
            ->first();

        // ✅ Stop suspended user BEFORE authenticate()
        if ($user && $user->is_active === false) {
            throw ValidationException::withMessages([
                'email' => 'Your account is suspended. Please contact admin.',
            ]);
        }

        // normal login
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        return match ($user->role) {
            'admin'   => redirect()->route('admin.dashboard'),
            'teacher' => redirect()->route('teacher.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            'staff'   => redirect()->route('staff.dashboard'),
            default   => redirect()->route('dashboard'),
        };
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}