<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\{AboutPage, Artikel, CaseModel, Category, ChartDataset, RawCase};

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

        $activeCases = CaseModel::where('is_public', true)
            ->whereHas('status', function ($q) {
                $q->where('key', '<>', 'closed');
            })->count();

        $completedCases = CaseModel::where('is_public', true)
            ->whereHas('status', function ($q) {
                $q->where('key', 'closed');
            })->count();

        $provinceCovered = CaseModel::where('is_public', true)
            ->whereNotNull('province_id')
            ->distinct('province_id')
            ->count('province_id');


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
            ->latest('published_at')
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
        // FILTERS
        // =========================================
        $filterTahun = $request->input('tahun');
        $filterKlasifikasi = $request->input('klasifikasi');
        $filterPulau = $request->input('pulau');
        $hasFilters = $filterTahun || $filterKlasifikasi || $filterPulau;

        $yearRange = [2019, 2025];
        $filterOptions = [
            'tahun'        => RawCase::whereNotNull('tahun')->whereBetween('tahun', $yearRange)->distinct()->orderBy('tahun', 'desc')->pluck('tahun'),
            'klasifikasi'  => RawCase::whereBetween('tahun', $yearRange)->whereNotNull('klasifikasi_clean')->distinct()->orderBy('klasifikasi_clean')->pluck('klasifikasi_clean'),
            'pulau'        => RawCase::whereBetween('tahun', $yearRange)->whereNotNull('pulau')->distinct()->orderBy('pulau')->pluck('pulau'),
        ];

        // =========================================
        // CHART DATA (echarts)
        // =========================================
        $publicCharts = [];
        $tableData = [];
        $yearlyMergeDatasets = ['Perkara per Tahun', 'Terdakwa per Tahun', 'Pengadilan per Tahun'];

        if ($hasFilters) {
            $rcFull = RawCase::whereBetween('tahun', $yearRange);
            if ($filterTahun) $rcFull->where('tahun', (int) $filterTahun);
            if ($filterKlasifikasi) $rcFull->where('klasifikasi_clean', $filterKlasifikasi);
            if ($filterPulau) $rcFull->where('pulau', $filterPulau);

            // For line/bar charts, exclude tahun filter so they still show full year range
            $rcNoTahun = RawCase::whereBetween('tahun', $yearRange);
            if ($filterKlasifikasi) $rcNoTahun->where('klasifikasi_clean', $filterKlasifikasi);
            if ($filterPulau) $rcNoTahun->where('pulau', $filterPulau);

            // For pie chart, exclude klasifikasi filter (pie shows all categories)
            $rcPie = RawCase::whereBetween('tahun', $yearRange);
            if ($filterTahun) $rcPie->where('tahun', (int) $filterTahun);
            if ($filterPulau) $rcPie->where('pulau', $filterPulau);

            // KPIs from filtered data
            $totalPerkara = (clone $rcFull)->count();
            $totalTerdakwa = (clone $rcFull)->sum('jumlah_terdakwa');
            $jmlPengadilan = (clone $rcFull)->whereNotNull('pengadilan')->distinct()->count('pengadilan');
            $subjekCounts = (clone $rcFull)->whereNotNull('subjek_hukum')
                ->selectRaw("CASE WHEN subjek_hukum IN ('Korporasi','Koperasi') THEN 'Korporasi' ELSE subjek_hukum END as kelompok, COUNT(*) as cnt")
                ->groupBy('kelompok')->pluck('cnt', 'kelompok');
            $vonisCounts = (clone $rcFull)->whereNotNull('vonis_putusan')
                ->selectRaw('vonis_putusan, COUNT(*) as cnt')
                ->groupBy('vonis_putusan')->pluck('cnt', 'vonis_putusan');

            $kpiData = [
                ['title' => 'Perkara',      'display' => number_format($totalPerkara)],
                ['title' => 'Terdakwa',     'display' => number_format($totalTerdakwa)],
                ['title' => 'Pengadilan',   'display' => number_format($jmlPengadilan)],
                ['title' => 'Perorangan',   'display' => number_format($subjekCounts['Perorangan'] ?? 0)],
                ['title' => 'Korporasi',    'display' => number_format($subjekCounts['Korporasi'] ?? 0)],
            ];
            foreach (['Bersalah', 'Bebas', 'Lepas'] as $v) {
                $cnt = $vonisCounts[$v] ?? 0;
                $kpiData[] = ['title' => 'Vonis ' . $v, 'display' => number_format($cnt)];
            }

            // Perkara per Tahun (ignore tahun filter)
            $perkaraTahun = (clone $rcNoTahun)
                ->selectRaw('tahun as lbl, COUNT(*) as total')
                ->whereNotNull('tahun')
                ->groupBy('tahun')->orderBy('tahun')
                ->get()->map(fn($i) => ['label' => (string) $i->lbl, 'value' => (float) $i->total])->toArray();
            if (!empty($perkaraTahun)) {
                $id = 'pub-filtered-perkara-tahun';
                $publicCharts[] = ['id' => $id, 'title' => 'Perkara per Tahun', 'type' => 'line', 'data' => $perkaraTahun];
                $bar = ['id' => $id . '-bar', 'title' => 'Perkara per Tahun', 'type' => 'bar', 'data' => $perkaraTahun];
            }

            // Klasifikasi Perkara (uses all filters including tahun)
            $klasData = (clone $rcPie)
                ->selectRaw('klasifikasi_clean as lbl, COUNT(*) as total')
                ->whereNotNull('klasifikasi_clean')
                ->groupBy('klasifikasi_clean')->orderByDesc('total')
                ->get()->map(fn($i) => ['label' => $i->lbl, 'value' => (float) $i->total])->toArray();
            if (!empty($klasData)) {
                $publicCharts[] = ['id' => 'pub-filtered-klasifikasi', 'title' => 'Klasifikasi Perkara', 'type' => 'pie', 'data' => $klasData];
            }
            if (isset($bar)) $publicCharts[] = $bar;

            // Terdakwa per Tahun (ignore tahun filter)
            $terdakwaTahun = (clone $rcNoTahun)
                ->selectRaw('tahun as lbl, SUM(jumlah_terdakwa) as total')
                ->whereNotNull('tahun')
                ->groupBy('tahun')->orderBy('tahun')
                ->get()->map(fn($i) => ['label' => (string) $i->lbl, 'value' => (float) $i->total])->toArray();
            if (!empty($terdakwaTahun)) {
                $publicCharts[] = ['id' => 'pub-filtered-terdakwa-tahun', 'title' => 'Terdakwa per Tahun', 'type' => 'bar', 'data' => $terdakwaTahun];
            }

            // Pengadilan per Tahun (ignore tahun filter)
            $pengadilanTahun = (clone $rcNoTahun)
                ->selectRaw('tahun as lbl, COUNT(DISTINCT pengadilan) as total')
                ->whereNotNull('tahun')
                ->groupBy('tahun')->orderBy('tahun')
                ->get()->map(fn($i) => ['label' => (string) $i->lbl, 'value' => (float) $i->total])->toArray();
            if (!empty($pengadilanTahun)) {
                $publicCharts[] = ['id' => 'pub-filtered-pengadilan-tahun', 'title' => 'Pengadilan per Tahun', 'type' => 'bar', 'data' => $pengadilanTahun];
            }

            $publicYears = RawCase::whereBetween('tahun', $yearRange)->whereNotNull('tahun')
                ->distinct()->orderBy('tahun')->pluck('tahun')->toArray();

            // Top 10 tables from filtered
            $tableData = [];
            $topQueries = [
                ['title' => 'Top 10 Hakim',          'field' => 'nama_hakim'],
                ['title' => 'Top 10 Jaksa Penuntun Umum', 'field' => 'jaksa'],
                ['title' => 'Top 10 Pengadilan',      'field' => 'pengadilan'],
                ['title' => 'Top 10 Kabupaten/Kota',  'field' => 'kabupaten'],
            ];
            foreach ($topQueries as $tq) {
                $rows = (clone $rcFull)
                    ->whereNotNull($tq['field'])
                    ->selectRaw("{$tq['field']} as label, COUNT(*) as cnt")
                    ->groupBy($tq['field'])
                    ->orderByDesc('cnt')
                    ->take(10)
                    ->get()
                    ->map(fn($i) => ['label' => $i->label, 'value' => (int) $i->cnt])
                    ->toArray();
                $tableData[] = ['title' => $tq['title'], 'rows' => $rows];
            }
        } else {
            $chartDatasets = ChartDataset::select('dataset')
                ->groupBy('dataset')
                ->pluck('dataset');

            foreach ($chartDatasets as $ds) {
                if ($ds === 'KPI') continue;
                $hasYear = ChartDataset::where('dataset', $ds)->whereNotNull('year')->exists();
                $isYearly = $hasYear && in_array($ds, $yearlyMergeDatasets);

                if ($isYearly) {
                    $query = ChartDataset::where('dataset', $ds)
                        ->whereNotNull('year')
                        ->selectRaw('year as lbl, SUM(value) as total')
                        ->groupBy('year')->orderBy('year');
                } else {
                    $limit = $ds === 'Pengadilan' ? 200 : 30;
                    $query = ChartDataset::where('dataset', $ds)
                        ->orderBy('value', 'desc')->limit($limit);
                }

                $data = $query->get()->map(fn($item) => [
                    'label' => $item->lbl ?? $item->label,
                    'value' => (float) ($item->total ?? $item->value),
                ])->toArray();

                if (empty($data)) continue;

                $labelMap = [
                    'Perkara'              => 'Perkara',
                    'Terdakwa'             => 'Terdakwa',
                    'Kabupaten'            => 'Kabupaten',
                    'Pengadilan'           => 'Pengadilan',
                    'Klasifikasi Perkara'  => 'Klasifikasi Perkara',
                    'Vonis Putusan'        => 'Vonis Putusan',
                    'Perkara per Tahun'    => 'Perkara per Tahun',
                    'Terdakwa per Tahun'   => 'Terdakwa per Tahun',
                    'Pengadilan per Tahun' => 'Pengadilan per Tahun',
                    'Rata-rata Vonis Penjara' => 'Rata-rata Vonis Penjara',
                ];

                $chartTypeMap = [
                    'Perkara per Tahun'       => 'line',
                    'Klasifikasi Perkara'     => 'pie',
                    'Terdakwa per Tahun'      => 'bar',
                    'Pengadilan per Tahun'    => 'bar',
                    'Perkara'                 => 'hbar',
                    'Terdakwa'                => 'hbar',
                    'Kabupaten'               => 'hbar',
                    'Pengadilan'              => 'hbar',
                    'Vonis Putusan'           => 'pie',
                    'Rata-rata Vonis Penjara' => 'bar',
                ];

                $publicCharts[] = [
                    'id'       => 'pub-' . str_replace([' ', '/', '_'], '-', $ds),
                    'title'    => $labelMap[$ds] ?? $ds,
                    'type'     => $chartTypeMap[$ds] ?? 'bar',
                    'data'     => $data,
                ];

            }
            // Top 10 tables from raw_cases
            $tableData = [];
            $topQueries = [
                ['title' => 'Top 10 Hakim',          'field' => 'nama_hakim'],
                ['title' => 'Top 10 Jaksa Penuntun Umum', 'field' => 'jaksa'],
                ['title' => 'Top 10 Pengadilan',      'field' => 'pengadilan'],
                ['title' => 'Top 10 Kabupaten/Kota',  'field' => 'kabupaten'],
            ];
            foreach ($topQueries as $tq) {
                $rows = RawCase::whereBetween('tahun', $yearRange)->whereNotNull($tq['field'])
                    ->selectRaw("{$tq['field']} as label, COUNT(*) as cnt")
                    ->groupBy($tq['field'])
                    ->orderByDesc('cnt')
                    ->take(10)
                    ->get()
                    ->map(fn($i) => ['label' => $i->label, 'value' => (int) $i->cnt])
                    ->toArray();
                $tableData[] = ['title' => $tq['title'], 'rows' => $rows];
            }

            $names = ['Perkara per Tahun', 'Klasifikasi Perkara', 'Terdakwa per Tahun', 'Pengadilan per Tahun'];
            $byName = [];
            foreach ($publicCharts as $ch) $byName[$ch['title']] = $ch;
            $ordered = [];
            if (isset($byName['Perkara per Tahun'])) $ordered[] = $byName['Perkara per Tahun'];
            if (isset($byName['Klasifikasi Perkara'])) $ordered[] = $byName['Klasifikasi Perkara'];
            if (isset($byName['Perkara per Tahun'])) {
                $bar = $byName['Perkara per Tahun'];
                $bar['type'] = 'bar';
                $bar['id'] = $bar['id'] . '-bar';
                $ordered[] = $bar;
            }
            if (isset($byName['Terdakwa per Tahun'])) $ordered[] = $byName['Terdakwa per Tahun'];
            if (isset($byName['Pengadilan per Tahun'])) $ordered[] = $byName['Pengadilan per Tahun'];
            $publicCharts = $ordered;

            $publicYears = ChartDataset::whereIn('dataset', $yearlyMergeDatasets)
                ->whereNotNull('year')
                ->distinct()->orderBy('year')->pluck('year')->toArray();

            $totalPerkara = RawCase::whereBetween('tahun', $yearRange)->count();
            $totalTerdakwa = RawCase::whereBetween('tahun', $yearRange)->sum('jumlah_terdakwa');
            $jmlPengadilan = RawCase::whereBetween('tahun', $yearRange)->whereNotNull('pengadilan')->distinct()->count('pengadilan');
            $subjekCounts = RawCase::whereBetween('tahun', $yearRange)->whereNotNull('subjek_hukum')
                ->selectRaw("CASE WHEN subjek_hukum IN ('Korporasi','Koperasi') THEN 'Korporasi' ELSE subjek_hukum END as kelompok, COUNT(*) as cnt")
                ->groupBy('kelompok')->pluck('cnt', 'kelompok');
            $vonisCounts = RawCase::whereBetween('tahun', $yearRange)->whereNotNull('vonis_putusan')
                ->selectRaw('vonis_putusan, COUNT(*) as cnt')
                ->groupBy('vonis_putusan')->pluck('cnt', 'vonis_putusan');

            $kpiData = [
                ['title' => 'Perkara',      'display' => number_format($totalPerkara)],
                ['title' => 'Terdakwa',     'display' => number_format($totalTerdakwa)],
                ['title' => 'Pengadilan',   'display' => number_format($jmlPengadilan)],
                ['title' => 'Perorangan',   'display' => number_format($subjekCounts['Perorangan'] ?? 0)],
                ['title' => 'Korporasi',    'display' => number_format($subjekCounts['Korporasi'] ?? 0)],
            ];
            foreach (['Bersalah', 'Bebas', 'Lepas'] as $v) {
                $cnt = $vonisCounts[$v] ?? 0;
                $kpiData[] = ['title' => 'Vonis ' . $v, 'display' => number_format($cnt)];
            }
        }

        $viewData = compact(
            'cases', 'case', 'casesByCategory', 'status', 'casesPerMonth',
            'totalCases', 'activeCases', 'completedCases', 'provinceCovered',
            'artikels', 'kasus', 'categories',
            'publicCharts', 'publicYears', 'tableData', 'kpiData',
            'filterOptions', 'filterTahun', 'filterKlasifikasi', 'filterPulau',
        );

        if ($request->wantsJson()) {
            return response()->json([
                'kpiData'      => $kpiData,
                'publicCharts' => $publicCharts,
                'tableData'    => $tableData,
                'publicYears'  => $publicYears,
            ]);
        }

        return view('front.dashboard-user', $viewData);
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

    public function about($locale)
    {
        app()->setLocale($locale);

        $about = AboutPage::with('translations')->first();

        return view('front.about', compact('about'));
    }
}