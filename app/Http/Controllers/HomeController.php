<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\{Artikel, CaseModel, Category, ChartDataset};

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // =========================================
        // CASE LIST (PUBLIC)
        // =========================================
        $locale = $request->get('locale', app()->getLocale());

        $cases = DB::table('case_geometries')
            ->leftJoin('case_translations', function ($q) use ($locale) {
                $q->on('case_translations.case_id', '=', 'case_geometries.case_id')
                    ->where('case_translations.locale', $locale);
            })
            ->leftJoin('case_translations as ct_fallback', function ($q) {
                $q->on('ct_fallback.case_id', '=', 'case_geometries.case_id')
                    ->where('ct_fallback.locale', 'id');
            })
            ->where('case_geometries.is_public', 1)
            ->select(
                'case_geometries.*',
                // Pakai locale aktif, fallback ke id
                DB::raw('COALESCE(case_translations.title, ct_fallback.title, case_geometries.title) as title'),
                DB::raw('COALESCE(case_translations.description, ct_fallback.description) as case_description'),
            )
            ->take(3)
            ->orderByDesc('case_geometries.created_at')
            ->get();


        // =========================================
        // FEATURED CASE (FIRST PUBLIC)
        // =========================================
        $case = $cases->first();


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
                return [
                    'category_id'   => $item->category_id,
                    'category_name' => $item->category_name ?? $item->slug ?? 'Uncategorized',
                    'count'         => $item->count,
                ];
            });


        // =========================================
        // CASES BY STATUS (CHART)
        // =========================================
        $status = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->where('cases.is_public', 1)
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
                    'status_id'   => $item->status_id,
                    'status_key'  => $item->status_key  ?? 'unknown',
                    'status_name' => $item->status_name ?? 'Unknown',
                    'count'       => $item->count,
                ];
            });


        // =========================================
        // CASE DEVELOPMENT PER MONTH (LINE CHART)
        // =========================================
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
                    'date'  => $item->date,
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

        $kasus = CaseModel::with(['category', 'status'])
            ->whereNotNull('published_at')
            ->where('is_public', true)
            ->orderByDesc('event_date')
            ->get();

        // Get all categories used by all public cases
        $allCategoryIds = $kasus->pluck('category_ids')
            ->flatten()
            ->unique()
            ->filter()
            ->toArray();

        $categories = Category::with('translations')
            ->whereIn('id', $allCategoryIds)
            ->get();
        
        // =========================================
        // CHART DATA FROM CSV (echarts)
        // =========================================
        $chartDatasets = ChartDataset::select('dataset')
            ->groupBy('dataset')
            ->pluck('dataset');

        $publicCharts = [];
        foreach ($chartDatasets as $ds) {
            $isYearly = ChartDataset::where('dataset', $ds)->whereNotNull('year')->exists();

            $query = ChartDataset::where('dataset', $ds);

            if ($isYearly) {
                $query = ChartDataset::where('dataset', $ds)
                    ->whereNotNull('year')
                    ->selectRaw('year as lbl, SUM(value) as total')
                    ->groupBy('year')
                    ->orderBy('year');
            } else {
                $limit = $ds === 'pengadilan' ? 200 : 30;
                $query = ChartDataset::where('dataset', $ds)
                    ->orderBy('value', 'desc')
                    ->limit($limit);
            }

            $data = $query->get()->map(fn($item) => [
                'label' => $item->lbl ?? $item->label,
                'value' => (int) ($item->total ?? $item->value),
            ])->toArray();

            if (empty($data)) continue;

            $labelMap = [
                'klasifikasi_perkara' => 'Klasifikasi Perkara',
                'hakim_perkara' => 'Hakim - Jumlah Perkara',
                'hakim_terdakwa' => 'Hakim - Jumlah Terdakwa',
                'jaksa_perkara' => 'Jaksa - Jumlah Perkara',
                'pengadilan' => 'Pengadilan - Jumlah Perkara',
                'kabupaten' => 'Kabupaten - Jumlah Perkara',
                'terdakwa' => 'Terdakwa per Tahun',
                'perkara' => 'Perkara per Tahun',
            ];

            $publicCharts[] = [
                'id' => 'pub-' . str_replace('_', '-', $ds),
                'title' => $labelMap[$ds] ?? ucfirst(str_replace('_', ' ', $ds)),
                'data' => $data,
            ];
        }

        $publicYears = ChartDataset::whereNotNull('year')
            ->distinct()->orderBy('year')->pluck('year')->toArray();

        return view('front.dashboard-user', compact(
            'cases',
            'case',
            'casesByCategory',
            'status',
            'casesPerMonth',
            'totalCases',
            'artikels',
            'kasus',
            'categories',
            'publicCharts',
            'publicYears',
        ));
    }


    public function getCases($caseNumber)
    {
        $locale = app()->getLocale();

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
            ->get()
            ->map(function ($item) use ($locale) {
                $item->title       = $item->title ?? '<em>Judul tidak tersedia dalam ' . strtoupper($locale) . '</em>';
                $item->description = $item->description ?? null;

                if (is_string($item->evidence)) {
                    $item->evidence = json_decode($item->evidence, true) ?? [];
                }

                return $item;
            });

        $case = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('reports', 'reports.id', '=', 'cases.report_id')
            ->leftJoin('case_translations', function ($q) use ($locale) {
                $q->on('cases.id', '=', 'case_translations.case_id')
                    ->where('case_translations.locale', $locale);
            })
            ->where('cases.is_public', true)
            ->where('cases.case_number', $caseNumber)
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
            ->first();

        if (! $case) {
            abort(404, 'Case not found');
        }

        $case->title = $case->title ?? '<em>Judul tidak tersedia dalam ' . strtoupper($locale) . '</em>';

        if (is_string($case->evidence)) {
            $case->evidence = json_decode($case->evidence, true) ?? [];
        }

        return view('front.dashboard-user', compact('cases', 'case'));
    }

    public function preview($locale, $slug)
    {
        app()->setLocale($locale);

        $case = DB::table('artikels')
            ->join('artikel_translations', 'artikel_translations.artikel_id', '=', 'artikels.id')
            ->where('artikels.slug', $slug)
            ->where('artikel_translations.locale', $locale)
            ->select(
                'artikels.*',
                'artikel_translations.title',
                'artikel_translations.excerpt',
                'artikel_translations.content'
            )
            ->first();

        return view('front.preview-artikel', compact('case'));
    }
}