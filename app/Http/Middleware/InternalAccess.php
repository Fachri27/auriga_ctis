<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InternalAccess
{
    /**
     * Handle an incoming request.
     *
     * Only allows access for users with role 'admin' or 'cso'.
     * Public users are redirected to home page.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        // internal accses menggunakan permission role
        $user = auth()->user();

        // Only allow admin and cso roles
        if (! $user->hasRole(roles: ['admin', 'cso'])) {
            // Public users or invalid roles are redirected to home
            return redirect('/')->with('error', 'You do not have access to this area.');
        }

        return $next($request);
    }
}
