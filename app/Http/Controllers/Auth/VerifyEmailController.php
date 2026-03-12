<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\{RedirectResponse, Request};
use App\Http\Controllers\Controller;
use App\Models\User;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        try {
            $user = User::findOrFail($request->route('id'));

            // Verify the hash matches
            if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
                return redirect()->route('login')->with('error', 'Invalid or expired verification link.');
            }

            if ($user->hasVerifiedEmail()) {
                return redirect()->route('login')->with('status', 'Email already verified. You can now log in.');
            }

            if ($user->markEmailAsVerified()) {
                event(new Verified($user));
            }

            // Check if current user is authenticated - if yes, logout so they can login again
            if (auth()->check()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->with('status', 'Email verified successfully! You can now log in with your credentials.');
        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Verification failed. Please try again.');
        }
    }
}
