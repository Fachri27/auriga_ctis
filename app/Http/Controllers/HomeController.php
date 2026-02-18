<?php

namespace App\Http\Controllers;

// use App\Models\CaseModel;
use Illuminate\Support\Facades\DB;
use App\Models\{Artikel, ArtikelTranslation, CaseModel};

class HomeController extends Controller
{
    public function index($locale)
    {
        // =========================================
        // CASE LIST (PUBLIC)
        // =========================================
        $cases = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) use ($locale) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', $locale);
            })
            ->where('cases.is_public', true)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.title',
                'case_translations.description',
                'statuses.key as status_key',
                'statuses.name as status_name',
                'reports.evidence',
                'categories.slug'
            )
            ->latest('cases.created_at')
            ->get();


        // =========================================
        // FEATURED CASE (FIRST)
        // =========================================
        $case = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) use ($locale) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', $locale);
            })
            ->where('cases.is_public', true)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.title',
                'case_translations.description',
                'statuses.key as status_key',
                'statuses.name as status_name',
                'reports.evidence',
                'categories.slug'
            )
            ->latest('cases.created_at')
            ->first();


        // =========================================
        // CASES BY CATEGORY (CHART)
        // =========================================
        $casesByCategory = DB::table('cases')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('category_translations', function ($join) use ($locale) {
                $join->on('category_translations.category_id', '=', 'categories.id')
                    ->where('category_translations.locale', $locale);
            })
            ->where('cases.is_public', true)
            ->select(
                'cases.category_id',
                'categories.slug',
                'category_translations.name as category_name',
                DB::raw('count(cases.id) as count')
            )
            ->groupBy(
                'cases.category_id',
                'categories.slug',
                'category_translations.name'
            )
            ->get()
            ->map(function ($item) {
                $categoryName = $item->category_name ?? $item->slug ?? 'Uncategorized';

                return [
                    'category_id' => $item->category_id,
                    'category_name' => $categoryName,
                    'count' => $item->count,
                ];
            });


        // =========================================
        // CASES BY STATUS (CHART)
        // =========================================
        $status = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->where('cases.is_public', 1) // 🔥 penting
            ->select(
                'cases.status_id',
                'statuses.key as status_key',
                'statuses.name as status_name',
                DB::raw('count(*) as count')
            )
            ->groupBy(
                'cases.status_id',
                'statuses.key',
                'statuses.name'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'status_id' => $item->status_id,
                    'status_key' => $item->status_key ?? 'unknown',
                    'status_name' => $item->status_name ?? 'Unknown',
                    'count' => $item->count,
                ];
            });


        // =========================================
        // CASE DEVELOPMENT PER MONTH (LINE CHART)
        // =========================================
        // $casesPerMonth = DB::table('cases')
        //     ->select(
        //         DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
        //         DB::raw('count(id) as total')
        //     )
        //     ->where('is_public', true)
        //     ->groupBy('month')
        //     ->orderBy('month')
        //     ->get();

        $casesPerMonth = CaseModel::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('is_public', true)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->date,
                    'count' => $item->count,
                ];
            });


        // =========================================
        // TOTAL PUBLIC CASES
        // =========================================
        $totalCases = DB::table('cases')
            ->where('is_public', true)
            ->count();


        // =========================================
        // ARTIKEL
        // =========================================
        $artikels = Artikel::with(['translation' => function ($query) use ($locale) {
            $query->where('locale', $locale);
        }])
            ->where('status', 'active')
            ->latest()
            ->get();


        // =========================================
        // RETURN VIEW
        // =========================================
        return view('front.dashboard-user', compact(
            'cases',
            'case',
            'casesByCategory',
            'status',
            'casesPerMonth',
            'totalCases',
            'artikels'
        ));
    }


    public function getCases($caseNumber)
    {
        // list for sidebar/cards
        $cases = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', 'id');
            })
            ->where('cases.is_public', true)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.*',
                'statuses.key',
                'statuses.name',
                'reports.evidence',
                'categories.slug',
            )
            ->get();

        // specific case to show (must be public)
        $case = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', 'id');
            })
            ->where('cases.is_public', true)
            ->where('cases.case_number', $caseNumber)
            ->select(
                'cases.id',
                'cases.case_number',
                'case_translations.*',
                'statuses.key',
                'statuses.name',
                'reports.evidence',
                'categories.slug',
            )
            ->first();

        if (! $case) {
            abort(404, 'Case not found');
        }

        return view('front.dashboard-user', compact('cases', 'case'));
    }

    public function preview($locale, $slug) 
    {
        $case = DB::table('artikels')
            ->join('artikel_translations', 'artikel_translations.artikel_id', '=', 'artikels.id')
            ->where('artikels.slug', $slug)
            ->where('artikel_translations.locale', $locale)
            ->select('artikels.*', 'artikel_translations.title', 'artikel_translations.excerpt', 'artikel_translations.content')
            ->first();

            // \dd($case);

        // $case = Artikel::with('translation')
        //     ->where('slug', $slug)
        //     ->first();

        // \dd($case);

        return view('front.preview-artikel', compact('case'));
    }
}