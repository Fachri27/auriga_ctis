<?php

namespace App\Http\Controllers;

use App\Models\CaseModel;
use App\Services\ReverseGeocoder;

class PublicCaseController extends Controller
{
    public function show(string $locale, string $caseNumber, ReverseGeocoder $geo)
    {
        app()->setLocale($locale);

        // NOTE: Legacy eager-load included process translations for complex workflows.
        // OLD: 'timelines.process.translations' // deprecated for public UI
        $case = CaseModel::with([
            'translations',
            'category.translations',
            'status',
            // Public UI only needs the simplified timeline (notes + date)
            'timelines',
        ])
            ->where('case_number', $caseNumber)
            ->where('is_public', true)
            ->firstOrFail();

        // For the public view use the simplified timeline helper
        // $case->simple_timeline is available and returns chronological notes (title optional).

        $location = null;

        if ($case->latitude && $case->longitude) {
            $location = $geo->getLocation(
                (float) $case->latitude,
                (float) $case->longitude
            );
        }

        return view('public.case-detail', compact('case', 'location'));
    }
}
