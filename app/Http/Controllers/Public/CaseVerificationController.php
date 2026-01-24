<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CaseModel;
use Illuminate\Http\Request;

class CaseVerificationController extends Controller
{
    /**
     * Show public verification page for a case (locale-aware)
     *
     * @param string $locale
     * @param string $case_number
     */
    public function show(string $locale, string $case_number)
    {
        // Ensure only public cases are visible here
        $case = CaseModel::with(['translations', 'status', 'report'])
            ->where('case_number', $case_number)
            ->where('is_public', true)
            ->firstOrFail();

        // The 'setlocale' middleware in routes should already set application locale
        // But ensure we pick a translation in the view (or controller) as needed.

        return view('public.verify-case', compact('case'));
    }
}
