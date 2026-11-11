<?php

namespace App\Services;

use App\Models\RawCase;
use App\Services\CaseImport\LocationMatcher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Cache, Log};
use Illuminate\Support\Str;

/**
 * Statistik perkara dari indeksasi putusan pidana sektor SDA & lingkungan hidup (tabel raw_cases),
 * untuk mode Perkara di dashboard publik.
 */
class PerkaraStatistics
{
    /** Rentang tahun yang ditampilkan, sama dengan statistik perkara di Beranda. */
    public const YEARS = [2019, 2025];

    /** Ejaan kabupaten di data putusan yang berbeda dari nama resminya. */
    private const REGENCY_ALIASES = [
        'KABUPATEN MAROWALI' => 'Kabupaten Morowali',
        'KABUPATEN MAROWALI UTARA' => 'Kabupaten Morowali Utara',
        'KABUPATEN KEPUALAUAN MENTAWAI' => 'Kabupaten Kepulauan Mentawai',
        'KOTA SUNGGAI PENUH' => 'Kota Sungai Penuh',
        'KABUPATEN TANJUNG SELOR' => 'Kabupaten Bulungan',
        'KABUPATEN BANGKEP' => 'Kabupaten Banggai Kepulauan',
        'KABUPATEN MALUKU TENGGARA BARAT' => 'Kabupaten Kepulauan Tanimbar',
    ];

    /**
     * Satu kabupaten lazimnya diadili satu sampai beberapa pengadilan negeri. Kabupaten yang dicatat oleh lebih
     * banyak pengadilan dari ini diduga salah isi di sumber data (mis. 2.384 perkara "Kabupaten Barito Utara" dari
     * 75 pengadilan di berbagai pulau), jadi tidak dipetakan.
     */
    public const MAX_COURTS_PER_REGENCY = 5;

    public function __construct(private LocationMatcher $locations) {}

    /**
     * @return array{tahun: ?string, klasifikasi: ?string, pulau: ?string}
     */
    public static function filtersFrom(Request $request): array
    {
        return [
            'tahun' => $request->filled('tahun') ? (string) $request->query('tahun') : null,
            'klasifikasi' => $request->filled('klasifikasi') ? (string) $request->query('klasifikasi') : null,
            'pulau' => $request->filled('pulau') ? (string) $request->query('pulau') : null,
        ];
    }

    public function filterOptions(): array
    {
        $base = fn () => RawCase::whereBetween('tahun', self::YEARS);

        return [
            'tahun' => $base()->whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun'),
            'klasifikasi' => $base()->whereNotNull('klasifikasi_clean')->distinct()->orderBy('klasifikasi_clean')->pluck('klasifikasi_clean'),
            'pulau' => $base()->whereNotNull('pulau')->distinct()->orderBy('pulau')->pluck('pulau'),
        ];
    }

    /**
     * KPI dengan bentuk yang sama seperti di Beranda (judul + angka tampil).
     *
     * @return list<array{title: string, display: string}>
     */
    public function kpis(array $filters): array
    {
        $query = $this->query($filters);

        $subjek = (clone $query)->whereNotNull('subjek_hukum')
            ->selectRaw("CASE WHEN subjek_hukum IN ('Korporasi','Koperasi') THEN 'Korporasi' ELSE subjek_hukum END as kelompok, COUNT(*) as cnt")
            ->groupBy('kelompok')->pluck('cnt', 'kelompok');
        $vonis = (clone $query)->whereNotNull('vonis_putusan')
            ->selectRaw('vonis_putusan, COUNT(*) as cnt')
            ->groupBy('vonis_putusan')->pluck('cnt', 'vonis_putusan');

        $kpis = [
            ['title' => 'Perkara', 'display' => number_format((clone $query)->count())],
            ['title' => 'Terdakwa', 'display' => number_format((clone $query)->sum('jumlah_terdakwa'))],
            ['title' => 'Pengadilan', 'display' => number_format((clone $query)->whereNotNull('pengadilan')->distinct()->count('pengadilan'))],
            ['title' => 'Perorangan', 'display' => number_format($subjek['Perorangan'] ?? 0)],
            ['title' => 'Korporasi', 'display' => number_format($subjek['Korporasi'] ?? 0)],
        ];
        foreach (['Bersalah', 'Bebas', 'Lepas'] as $v) {
            $kpis[] = ['title' => 'Vonis '.$v, 'display' => number_format($vonis[$v] ?? 0)];
        }

        return $kpis;
    }

    /**
     * Jumlah perkara per klasifikasi (filter klasifikasi diabaikan, agar semua pilihan tetap tampil).
     *
     * @return list<array{label: string, value: int}>
     */
    public function classifications(array $filters): array
    {
        return $this->query($filters, ignore: ['klasifikasi'])
            ->whereNotNull('klasifikasi_clean')
            ->selectRaw('klasifikasi_clean as label, COUNT(*) as total')
            ->groupBy('klasifikasi_clean')->orderByDesc('total')
            ->get()->map(fn ($r) => ['label' => $r->label, 'value' => (int) $r->total])->all();
    }

    /**
     * Grafik batang per tahun (filter tahun diabaikan, agar rentang tahun tetap utuh).
     *
     * @return list<array{id: string, title: string, type: string, data: list<array{label: string, value: float}>}>
     */
    public function yearlyCharts(array $filters): array
    {
        $series = [
            'perkara' => ['Perkara per Tahun', 'COUNT(*)'],
            'terdakwa' => ['Terdakwa per Tahun', 'SUM(jumlah_terdakwa)'],
            'pengadilan' => ['Pengadilan per Tahun', 'COUNT(DISTINCT pengadilan)'],
        ];

        $charts = [];
        foreach ($series as $key => [$title, $aggregate]) {
            $data = $this->query($filters, ignore: ['tahun'])
                ->whereNotNull('tahun')
                ->selectRaw("tahun as lbl, {$aggregate} as total")
                ->groupBy('tahun')->orderBy('tahun')
                ->get()->map(fn ($r) => ['label' => (string) $r->lbl, 'value' => (float) $r->total])->all();

            if ($data) {
                $charts[] = ['id' => "perkara-{$key}-tahun", 'title' => $title, 'type' => 'bar', 'data' => $data];
            }
        }

        return $charts;
    }

    /**
     * Perkara per kabupaten beserta titik tengahnya, untuk peta. Perkara di kabupaten yang diduga salah isi
     * (lihat MAX_COURTS_PER_REGENCY) dihitung terpisah di 'doubtful'.
     *
     * @return array{points: list<array{name: string, lat: float, lng: float, total: int, terdakwa: int, klasifikasi: list<array{label: string, value: int}>}>, unmapped: int, doubtful: array{count: int, regencies: list<string>, courts: int}}
     */
    public function regencies(array $filters): array
    {
        $doubtful = $this->doubtfulRegencies();

        $rows = $this->query($filters)
            ->whereNotNull('kabupaten')->where('kabupaten', '<>', '')
            ->when($doubtful, fn ($q) => $q->whereNotIn('kabupaten', array_keys($doubtful)))
            ->selectRaw('kabupaten, klasifikasi_clean, COUNT(*) as total, SUM(jumlah_terdakwa) as terdakwa')
            ->groupBy('kabupaten', 'klasifikasi_clean')
            ->get();

        $coordinates = $this->coordinates();
        $points = [];
        $unmapped = (clone $this->query($filters))->where(fn ($q) => $q->whereNull('kabupaten')->orWhere('kabupaten', ''))->count();

        foreach ($rows as $row) {
            $key = Str::upper(trim($row->kabupaten));
            if (! isset($coordinates[$key])) {
                $unmapped += (int) $row->total;

                continue;
            }

            $points[$key] ??= [
                'name' => Str::title(Str::lower(trim($row->kabupaten))),
                'lat' => $coordinates[$key]['lat'],
                'lng' => $coordinates[$key]['lng'],
                'total' => 0,
                'terdakwa' => 0,
                'klasifikasi' => [],
            ];
            $points[$key]['total'] += (int) $row->total;
            $points[$key]['terdakwa'] += (int) $row->terdakwa;
            if ($row->klasifikasi_clean) {
                $points[$key]['klasifikasi'][] = ['label' => $row->klasifikasi_clean, 'value' => (int) $row->total];
            }
        }

        foreach ($points as &$point) {
            usort($point['klasifikasi'], fn ($a, $b) => $b['value'] <=> $a['value']);
        }
        unset($point);

        usort($points, fn ($a, $b) => $b['total'] <=> $a['total']);

        return [
            'points' => $points,
            'unmapped' => $unmapped,
            'doubtful' => [
                'count' => $doubtful ? (clone $this->query($filters))->whereIn('kabupaten', array_keys($doubtful))->count() : 0,
                'regencies' => array_map(fn ($name) => Str::title(Str::lower(trim($name))), array_keys($doubtful)),
                'courts' => array_sum($doubtful),
            ],
        ];
    }

    /**
     * Kabupaten yang dicatat oleh terlalu banyak pengadilan berbeda, beserta jumlah pengadilannya.
     *
     * @return array<string, int>
     */
    private function doubtfulRegencies(): array
    {
        return RawCase::whereBetween('tahun', self::YEARS)
            ->whereNotNull('kabupaten')->where('kabupaten', '<>', '')
            ->selectRaw('kabupaten, COUNT(DISTINCT pengadilan) as courts')
            ->groupBy('kabupaten')
            ->havingRaw('COUNT(DISTINCT pengadilan) > ?', [self::MAX_COURTS_PER_REGENCY])
            ->pluck('courts', 'kabupaten')->map(fn ($n) => (int) $n)->all();
    }

    private function query(array $filters, array $ignore = []): Builder
    {
        $query = RawCase::query()->whereBetween('tahun', self::YEARS);

        if ($filters['tahun'] && ! in_array('tahun', $ignore, true)) {
            $query->where('tahun', (int) $filters['tahun']);
        }
        if ($filters['klasifikasi'] && ! in_array('klasifikasi', $ignore, true)) {
            $query->where('klasifikasi_clean', $filters['klasifikasi']);
        }
        if ($filters['pulau'] && ! in_array('pulau', $ignore, true)) {
            $query->where('pulau', $filters['pulau']);
        }

        return $query;
    }

    /**
     * Titik tengah setiap nama kabupaten di data putusan (setingkat kabupaten saja), di-cache sehari.
     *
     * @return array<string, array{lat: float, lng: float}>
     */
    private function coordinates(): array
    {
        $cacheKey = 'perkara:regency-points:v1';
        if (is_array($cached = Cache::get($cacheKey))) {
            return $cached;
        }

        $names = RawCase::whereNotNull('kabupaten')->where('kabupaten', '<>', '')->distinct()->pluck('kabupaten');
        $points = [];

        try {
            foreach ($names as $name) {
                $key = Str::upper(trim($name));
                $match = $this->locations->match(self::REGENCY_ALIASES[$key] ?? $name);

                if ($match && $match['precision'] === 'kabupaten') {
                    $points[$key] = ['lat' => round($match['lat'], 5), 'lng' => round($match['lng'], 5)];
                }
            }
        } catch (\Throwable $e) {
            // Basis data wilayah tidak tersedia: peta perkara kosong (tidak di-cache), statistik lain tetap tampil.
            Log::warning('Peta perkara: gagal memetakan kabupaten: '.$e->getMessage());

            return [];
        }

        Cache::put($cacheKey, $points, now()->addDay());

        return $points;
    }
}
