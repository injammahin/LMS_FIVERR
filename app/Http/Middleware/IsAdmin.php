<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        // Check if the logged-in user is an admin
        if (auth()->user() && auth()->user()->role === 'admin') {
            return $next($request);
        }

        // Redirect to home if not an admin
        return redirect('/');
    }
}