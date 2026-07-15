<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        // Redirect based on role. Sumber kebenaran role adalah Spatie Permission
        // (hasRole), sama dengan middleware InternalAccess — kolom users.role bisa
        // tidak sinkron sehingga admin/cso salah terdeteksi sebagai publik.
        if ($user->hasRole(['admin', 'cso'])) {
            // Internal users go to internal dashboard.
            // Use intended() to redirect back to originally requested internal route if available.
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Public users go to public homepage
        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
