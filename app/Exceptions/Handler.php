<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // If user is not authenticated, send to login page
        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Session expired.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->guest(route('login'))
                ->with('error', 'Your session has expired. Please log in again.');
        });

        // If CSRF/session token expired, logout cleanly and redirect to login
        $this->renderable(function (TokenMismatchException $e, Request $request) {
            if (auth()->check()) {
                auth()->logout();
            }

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Session expired. Please log in again.',
                    'redirect' => route('login'),
                ], 419);
            }

            return redirect()->guest(route('login'))
                ->with('error', 'Your session has expired. Please log in again.');
        });

        // Catch generic 419 page expired errors too
        $this->renderable(function (Throwable $e, Request $request) {
            $statusCode = $e instanceof HttpExceptionInterface
                ? $e->getStatusCode()
                : null;

            if ($statusCode === 419) {
                if (auth()->check()) {
                    auth()->logout();
                }

                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Session expired. Please log in again.',
                        'redirect' => route('login'),
                    ], 419);
                }

                return redirect()->guest(route('login'))
                    ->with('error', 'Your session has expired. Please log in again.');
            }
        });
    }
}