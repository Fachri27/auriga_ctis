<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is not authenticated, let them through (they'll be redirected to login)
        if (!$request->user()) {
            return $next($request);
        }

        // If user's email is not verified, redirect to verification notice
        if (!$request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice')->with('message', 'Please verify your email address first.');
        }

        return $next($request);
    }
}
