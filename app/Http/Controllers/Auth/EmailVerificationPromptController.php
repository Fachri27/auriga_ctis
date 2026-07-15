<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            $role = $request->user()->role ?? 'public';

            // dashboard admin (route 'dashboard') berada di luar group {locale},
            // jadi tidak butuh parameter locale. Sedangkan 'dashboard-user' ada
            // di dalam group {locale} → wajib sertakan locale.
            if (in_array($role, ['admin', 'cso'])) {
                return redirect()->route('dashboard');
            }

            return redirect()->route('dashboard-user', ['locale' => app()->getLocale()]);
        }

        return view('auth.verify-email');
    }
}
