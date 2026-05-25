<?php

namespace App\Http\Controllers;

use App\Models\{Artikel, CaseModel, Category};
use App\Services\ReverseGeocoder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PublicCaseController extends Controller
{
    
    public function show(string $locale, string $caseNumber, ReverseGeocoder $geo)
    {
        app()->setLocale($locale);

        $case = CaseModel::with([
            'translations',
            'status',
            'timelines',
        ])
            ->where('case_number', $caseNumber)
            ->where('is_public', true)
            ->firstOrFail();

        $categories = Category::with('translations')
            ->whereIn('id', $case->category_ids ?? [])
            ->get();

        $location = null;

        if ($case->latitude && $case->longitude) {
            $location = $geo->getLocation(
                (float) $case->latitude,
                (float) $case->longitude
            );
        }

        // $artikel = Artikel::with('translation')
        //     ->where('status', 'active')
        //     ->when($case->category_ids, function ($q) use ($case) {
        //         $q->whereIn('category_id', $case->category_ids);  // ← pakai whereIn karena array
        //     })
        //     // ->select('artikels.*', 'artikel_translations.title', 'artikel_translations.excerpt', 'artikel_translations.content', 'categories.slug as category_name')
        //     ->latest()
        //     ->limit(6)
        //     ->get();
        //  dd($artikel);

        $artikels = DB::table('artikels')
            ->join('artikel_translations', 'artikel_translations.artikel_id', '=', 'artikels.id')
            ->join('categories', 'categories.id', '=', 'artikels.category_id')
            ->where('artikels.status', 'active')
            ->when($case->category_ids, function ($q) use ($case) {
                $q->whereIn('category_id', $case->category_ids);  // ← pakai whereIn karena array
            })
            ->where('artikel_translations.locale', $locale)
            ->select('artikels.*', 'artikel_translations.title', 'artikel_translations.excerpt', 'artikel_translations.content', 'categories.slug as category_name')
            ->take(3)
            ->get();

        return view('public.case-detail', compact('case', 'location', 'categories', 'artikels'));  // ← tambah categories
    }
}