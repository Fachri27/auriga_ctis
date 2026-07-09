<?php

namespace App\Console\Commands;

use App\Models\ChartDataset;
use App\Models\RawCase;
use App\Services\GoogleSheetsService;
use Illuminate\Console\Command;

class SyncChartFromSheets extends Command
{
    protected $signature = 'chart:sync
        {--sheets= : Comma-separated sheet names to sync}';

    protected $description = 'Sync chart data from Google Sheets to chart_data table';

    private array $headerMap = [];

    public function handle(GoogleSheetsService $sheets): int
    {
        $sheetNames = $this->option('sheets');
        $sheetNames = $sheetNames ? explode(',', $sheetNames) : null;

        $availableSheets = $sheets->getSheetNames();

        if (empty($availableSheets)) {
            $this->error('No sheets found.');
            return Command::FAILURE;
        }

        $this->info('Sheets: ' . implode(', ', $availableSheets));

        $targets = $sheetNames ?? $availableSheets;

        $rawSheet = $this->pickRawSheet($availableSheets);

        if (!$rawSheet) {
            $this->warn('No raw data sheet found (looking for Final, CleanData, or similar).');
            return Command::FAILURE;
        }

        $this->line("Reading raw data from: {$rawSheet}");

        try {
            $rows = $sheets->getDataAsJson("{$rawSheet}!A1:Z");
        } catch (\Throwable $e) {
            $this->error("Failed to read sheet: {$e->getMessage()}");
            return Command::FAILURE;
        }

        if (empty($rows)) {
            $this->warn('No data found.');
            return Command::FAILURE;
        }

        $this->info('Raw rows: ' . count($rows));

        $this->aggregateAndSync($rows);

        $this->importRawCases($rows);

        if (in_array('DashboardData', $availableSheets)) {
            $this->line("\nReading DashboardData sheet...");
            try {
                $dashboardRows = $sheets->getData("DashboardData!A1:Z");
                $this->syncDashboardData($dashboardRows);
            } catch (\Throwable $e) {
                $this->warn("Failed to read DashboardData: {$e->getMessage()}");
            }
        }

        if (in_array('Dashboard', $availableSheets)) {
            $this->line("\nReading Dashboard sheet for KPIs...");
            try {
                $kpiRows = $sheets->getData("Dashboard!A1:Z");
                $this->syncKPIs($kpiRows);
            } catch (\Throwable $e) {
                $this->warn("Failed to read Dashboard KPIs: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }

    private function pickRawSheet(array $available): ?string
    {
        $priority = ['Final', 'CleanData', 'Final 2019-2025'];

        foreach ($priority as $name) {
            if (in_array($name, $available)) {
                return $name;
            }
        }

        return $available[0] ?? null;
    }

    private function col(array $row, string ...$candidates): ?string
    {
        foreach ($candidates as $c) {
            if (isset($row[$c]) && $row[$c] !== '' && $row[$c] !== null) {
                $val = trim($row[$c]);
                return $val !== '' ? $val : null;
            }
        }
        return null;
    }

    private function aggregateAndSync(array $rows): void
    {
        ChartDataset::truncate();
        $datasets = [
            [
                'name' => 'Perkara',
                'groupBy' => fn($r) => $this->col($r, 'Perkara', 'No Perkara'),
                'filter' => fn($r) => true,
            ],
            [
                'name' => 'Terdakwa',
                'groupBy' => fn($r) => $this->col($r, 'Terdakwa'),
                'filter' => fn($r) => true,
            ],
            [
                'name' => 'Kabupaten',
                'groupBy' => fn($r) => $this->col($r, 'Kabupaten/Kota'),
                'filter' => fn($r) => true,
            ],
        ];

        foreach ($datasets as $ds) {
            $this->line("  Aggregating: {$ds['name']}...");

            $data = array_filter($rows, $ds['filter']);

            $grouped = [];

            foreach ($data as $row) {
                $key = trim($ds['groupBy']($row));
                if (!$key || $key === '-' || $key === '') {
                    continue;
                }

                $year = trim($this->col($row, 'Tahun') ?? '');

                if (!isset($grouped[$key])) {
                    $grouped[$key] = ['count' => 0, 'years' => []];
                }

                $grouped[$key]['count']++;
                if ($year !== '') {
                    $y = (int) $year;
                    $grouped[$key]['years'][$y] = ($grouped[$key]['years'][$y] ?? 0) + 1;
                }
            }

            arsort($grouped);

            $imported = 0;

            foreach ($grouped as $label => $info) {
                $shortLabel = mb_substr($label, 0, 250);

                ChartDataset::updateOrCreate(
                    ['dataset' => $ds['name'], 'label' => $shortLabel, 'year' => null],
                    ['value' => $info['count']],
                );

                foreach ($info['years'] as $year => $count) {
                    ChartDataset::updateOrCreate(
                        ['dataset' => $ds['name'], 'label' => $shortLabel, 'year' => (int) $year],
                        ['value' => $count],
                    );
                }

                $imported++;
            }

            $this->info("    {$imported} labels, total " . count($data) . " rows processed.");
        }
    }

    private function importRawCases(array $rows): void
    {
        $this->line('  Importing raw cases...');
        $colMap = [
            'No Perkara'            => 'no_perkara',
            'Pengadilan'            => 'pengadilan',
            'Perkara'               => 'perkara',
            'Klasifikasi Perkara SDA-LH' => 'klasifikasi',
            'Klasifikasi Perkara_Clean'  => 'klasifikasi_clean',
            'Tahun'                 => 'tahun',
            'Kabupaten/Kota'        => 'kabupaten',
            'Pulau'                 => 'pulau',
            'Terdakwa'              => 'terdakwa',
            'Jumlah Terdakwa'       => 'jumlah_terdakwa',
            'Subjek Hukum'          => 'subjek_hukum',
            'Penyertaan'            => 'penyertaan',
            'Vonis Penjara'         => 'vonis_penjara',
            'Subsidair'             => 'subsidair',
            'Vonis Denda'           => 'vonis_denda',
            'Vonis Putusan'         => 'vonis_putusan',
            'Biaya Perkara'         => 'biaya_perkara',
            'Jaksa Penuntut Umum'   => 'jaksa',
            'Nama Hakim'            => 'nama_hakim',
        ];
        $allColumns = array_values($colMap);
        $decimalCols = ['vonis_penjara', 'subsidair', 'vonis_denda', 'biaya_perkara'];

        RawCase::truncate();
        $chunks = array_chunk($rows, 500);
        $total = 0;
        foreach ($chunks as $chunk) {
            $inserts = [];
            foreach ($chunk as $row) {
                $data = [];
                foreach ($colMap as $sheetCol => $dbCol) {
                    $val = $row[$sheetCol] ?? null;
                    if (is_string($val)) $val = trim($val);
                    if ($val === '' || $val === null) continue;
                    if ($dbCol === 'tahun') {
                        $data[$dbCol] = (int) $val;
                    } elseif (in_array($dbCol, $decimalCols)) {
                        $data[$dbCol] = (float) preg_replace('/[^0-9.]/', '', str_replace(',', '.', str_replace('.', '', $val)));
                    } else {
                        $data[$dbCol] = $val;
                    }
                }
                if (empty($data)) continue;
                foreach ($allColumns as $col) {
                    if (!array_key_exists($col, $data)) {
                        $data[$col] = null;
                    }
                }
                $inserts[] = $data;
            }
            if (!empty($inserts)) {
                RawCase::insert($inserts);
                $total += count($inserts);
            }
        }
        $this->info("  {$total} raw cases imported.");
    }

    private function syncDashboardData(array $rows): void
    {
        if (empty($rows)) {
            $this->warn('DashboardData is empty.');
            return;
        }

        $header = array_shift($rows);
        $totalDataRows = 0;

        // Segment 1: Col A (0) = Tahun, Col B (1) = Jumlah Perkara
        // Dataset: "Perkara per Tahun"
        $this->line("  DashboardData: Perkara per Tahun...");
        ChartDataset::where('dataset', 'Perkara per Tahun')->delete();
        $cnt = 0;
        foreach ($rows as $row) {
            $tahun = $row[0] ?? '';
            $jumlah = $row[1] ?? '';
            if ($tahun === '' || $jumlah === '' || !is_numeric(trim($jumlah))) continue;
            ChartDataset::create([
                'dataset' => 'Perkara per Tahun',
                'label' => trim($tahun),
                'year' => (int) $tahun,
                'value' => (int) $jumlah,
            ]);
            $cnt++;
        }
        $this->info("    {$cnt} rows synced.");
        $totalDataRows += $cnt;

        // Segment 2: Col D (3) = Klasifikasi, Col E (4) = Jumlah Perkara
        // Dataset: "Klasifikasi Perkara"
        $this->line("  DashboardData: Klasifikasi Perkara...");
        ChartDataset::where('dataset', 'Klasifikasi Perkara')->delete();
        $cnt = 0;
        foreach ($rows as $row) {
            $klasifikasi = $row[3] ?? '';
            $jumlah = $row[4] ?? '';
            if ($klasifikasi === '' || $jumlah === '' || !is_numeric(trim($jumlah))) continue;
            ChartDataset::create([
                'dataset' => 'Klasifikasi Perkara',
                'label' => trim($klasifikasi),
                'year' => null,
                'value' => (int) $jumlah,
            ]);
            $cnt++;
        }
        $this->info("    {$cnt} rows synced.");
        $totalDataRows += $cnt;

        // Segment 3: Col G (6) = Vonis Putusan, Col H (7) = Jumlah Perkara
        // Dataset: "Vonis Putusan"
        $this->line("  DashboardData: Vonis Putusan...");
        ChartDataset::where('dataset', 'Vonis Putusan')->delete();
        $cnt = 0;
        foreach ($rows as $row) {
            $vonis = $row[6] ?? '';
            $jumlah = $row[7] ?? '';
            if ($vonis === '' || $jumlah === '' || !is_numeric(trim($jumlah))) continue;
            ChartDataset::create([
                'dataset' => 'Vonis Putusan',
                'label' => trim($vonis),
                'year' => null,
                'value' => (int) $jumlah,
            ]);
            $cnt++;
        }
        $this->info("    {$cnt} rows synced.");
        $totalDataRows += $cnt;

        // Segment 4: Col J (9) = Klasifikasi, Col K (10) = Rata-rata Vonis Penjara (bulan)
        // Dataset: "Rata-rata Vonis Penjara"
        $this->line("  DashboardData: Rata-rata Vonis Penjara...");
        ChartDataset::where('dataset', 'Rata-rata Vonis Penjara')->delete();
        $cnt = 0;
        foreach ($rows as $row) {
            $klasifikasi = $row[9] ?? '';
            $rata = $row[10] ?? '';
            if ($klasifikasi === '' || $rata === '' || !is_numeric(trim($rata))) continue;
            ChartDataset::create([
                'dataset' => 'Rata-rata Vonis Penjara',
                'label' => trim($klasifikasi),
                'year' => null,
                'value' => (float) $rata,
            ]);
            $cnt++;
        }
        $this->info("    {$cnt} rows synced.");
        $totalDataRows += $cnt;

        // Segment 5: Col M (12) = Tahun, Col N (13) = Jumlah Terdakwa
        // Dataset: "Terdakwa per Tahun"
        $this->line("  DashboardData: Terdakwa per Tahun...");
        ChartDataset::where('dataset', 'Terdakwa per Tahun')->delete();
        $cnt = 0;
        foreach ($rows as $row) {
            $tahun = $row[12] ?? '';
            $jumlah = $row[13] ?? '';
            if ($tahun === '' || $jumlah === '' || !is_numeric(trim($jumlah))) continue;
            ChartDataset::create([
                'dataset' => 'Terdakwa per Tahun',
                'label' => trim($tahun),
                'year' => (int) $tahun,
                'value' => (int) $jumlah,
            ]);
            $cnt++;
        }
        $this->info("    {$cnt} rows synced.");
        $totalDataRows += $cnt;

        // Segment 6: Col P (15) = Tahun, Col Q (16) = Jumlah Pengadilan
        // Dataset: "Pengadilan per Tahun"
        $this->line("  DashboardData: Pengadilan per Tahun...");
        ChartDataset::where('dataset', 'Pengadilan per Tahun')->delete();
        $cnt = 0;
        foreach ($rows as $row) {
            $tahun = $row[15] ?? '';
            $jumlah = $row[16] ?? '';
            if ($tahun === '' || $jumlah === '' || !is_numeric(trim($jumlah))) continue;
            ChartDataset::create([
                'dataset' => 'Pengadilan per Tahun',
                'label' => trim($tahun),
                'year' => (int) $tahun,
                'value' => (int) $jumlah,
            ]);
            $cnt++;
        }
        $this->info("    {$cnt} rows synced.");
        $totalDataRows += $cnt;

        // Segment 7: Col S (18) = Tahun, Col T (19) = Pengadilan, Col U (20) = count
        // Dataset: "Pengadilan"
        $this->line("  DashboardData: Pengadilan...");
        ChartDataset::where('dataset', 'Pengadilan')->delete();
        $cnt = 0;
        foreach ($rows as $row) {
            $tahun = $row[18] ?? '';
            $pengadilan = $row[19] ?? '';
            $jumlah = $row[20] ?? '';
            if ($tahun === '' || $pengadilan === '' || $jumlah === '' || !is_numeric(trim($jumlah))) continue;
            $shortLabel = mb_substr(trim($pengadilan), 0, 250);
            ChartDataset::create([
                'dataset' => 'Pengadilan',
                'label' => $shortLabel,
                'year' => (int) $tahun,
                'value' => (int) $jumlah,
            ]);
            $cnt++;
        }
        $this->info("    {$cnt} rows synced.");
        $totalDataRows += $cnt;

        $this->info("  Total DashboardData rows imported: {$totalDataRows}");
    }

    private function syncKPIs(array $rows): void
    {
        $kpiRow = null;
        foreach ($rows as $row) {
            $label = trim($row[0] ?? '');
            if ($label === 'Total Perkara') {
                $kpiRow = $row;
                break;
            }
        }

        if (!$kpiRow) {
            $this->warn('  KPI row not found (looking for "Total Perkara" in col A).');
            return;
        }

        $fields = [
            ['key' => 'total_perkara',     'col' => 1],
            ['key' => 'total_terdakwa',     'col' => 3],
            ['key' => 'pct_vonis_bersalah', 'col' => 5],
            ['key' => 'rata_vonis_penjara', 'col' => 7],
            ['key' => 'rata_vonis_denda',   'col' => 9],
            ['key' => 'jumlah_pengadilan',  'col' => 11],
        ];

        ChartDataset::where('dataset', 'KPI')->delete();
        $cnt = 0;
        foreach ($fields as $f) {
            $raw = trim($kpiRow[$f['col']] ?? '');
            if ($raw === '' || $raw === null) continue;
            ChartDataset::create([
                'dataset' => 'KPI',
                'label'   => $raw,
                'year'    => null,
                'value'   => $cnt,
            ]);
            $cnt++;
        }
        $this->info("  {$cnt} KPIs synced.");
    }
}
