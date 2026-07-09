<?php

namespace App\Livewire\Charts;

use App\Models\ChartDataset;
use Livewire\Component;

class ChartsDashboard extends Component
{
    public string $view = 'chart';

    public function downloadCsv($dataset)
    {
        $query = ChartDataset::where('dataset', $dataset)
            ->orderBy('year')
            ->orderBy('value', 'desc');

        $filename = str_replace([' ', '/'], '-', $dataset) . '-export.csv';

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Label', 'Tahun', 'Nilai']);
            foreach ($query->cursor() as $row) {
                fputcsv($file, [$row->label, $row->year ?? '-', $row->value]);
            }
            fclose($file);
        };

        return response()->streamDownload($callback, $filename);
    }

    public function render()
    {
        $labelMap = [
            "Perkara"                 => "Perkara",
            "Pengadilan"               => "Pengadilan",
            "Klasifikasi Perkara"      => "Klasifikasi Perkara",
            "Terdakwa"                 => "Terdakwa",
            "Kabupaten"                => "Kabupaten",
            "Vonis Putusan"            => "Vonis Putusan",
            "Perkara per Tahun"        => "Perkara per Tahun",
            "Terdakwa per Tahun"       => "Terdakwa per Tahun",
            "Pengadilan per Tahun"     => "Pengadilan per Tahun",
            "Rata-rata Vonis Penjara"  => "Rata-rata Vonis Penjara",
        ];

        $yearlyMergeDatasets = ['Perkara per Tahun', 'Terdakwa per Tahun', 'Pengadilan per Tahun'];

        $datasets = ChartDataset::select("dataset")
            ->groupBy("dataset")
            ->orderBy("dataset")
            ->pluck("dataset");

        $charts = [];
        $tableData = [];

        foreach ($datasets as $ds) {
            if ($ds === 'KPI') continue;
            $hasYear = ChartDataset::where("dataset", $ds)
                ->whereNotNull("year")
                ->exists();

            $isYearly = $hasYear && in_array($ds, $yearlyMergeDatasets);

            if ($isYearly) {
                $chartQuery = ChartDataset::where("dataset", $ds)
                    ->whereNotNull("year")
                    ->selectRaw("year as lbl, SUM(value) as total")
                    ->groupBy("year")
                    ->orderBy("year");
            } else {
                $limit = $ds === 'Pengadilan' ? 200 : ($hasYear ? 300 : 30);
                $chartQuery = ChartDataset::where("dataset", $ds)
                    ->orderBy("value", "desc")
                    ->limit($limit);
            }

            $data = $chartQuery
                ->get()
                ->map(
                    fn($item) => [
                        "label" => $item->lbl ?? $item->label,
                        "value" => (float) ($item->total ?? $item->value),
                    ],
                )
                ->toArray();

            if (empty($data)) {
                continue;
            }

            $chartTypeMap = [
                "Perkara per Tahun"        => "line",
                "Klasifikasi Perkara"      => "pie",
                "Terdakwa per Tahun"       => "bar",
                "Pengadilan per Tahun"     => "bar",
                "Perkara"                  => "hbar",
                "Terdakwa"                 => "hbar",
                "Kabupaten"                => "hbar",
                "Pengadilan"               => "hbar",
                "Vonis Putusan"            => "pie",
                "Rata-rata Vonis Penjara"  => "bar",
            ];

            $charts[] = [
                "id" => "ch-" . str_replace([" ", "/"], "-", $ds),
                "dataset" => $ds,
                "title" => $labelMap[$ds] ?? $ds,
                "type"  => $chartTypeMap[$ds] ?? "bar",
                "data" => $data,
            ];

            $tableQuery = ChartDataset::where("dataset", $ds)
                ->orderBy("year", "asc")
                ->orderBy("value", "desc");

            if ($isYearly) {
                $tableQuery->whereNotNull("year");
            }

            $tableData[] = [
                "dataset" => $ds,
                "title" => $labelMap[$ds] ?? $ds,
                "rows" => $tableQuery->limit(500)->get()->toArray(),
            ];
        }

        $byName = [];
        foreach ($charts as $ch) {
            $byName[$ch['title']] = $ch;
        }
        $ordered = [];
        if (isset($byName['Perkara per Tahun'])) {
            $ordered[] = $byName['Perkara per Tahun'];
        }
        if (isset($byName['Klasifikasi Perkara'])) {
            $ordered[] = $byName['Klasifikasi Perkara'];
        }
        if (isset($byName['Perkara per Tahun'])) {
            $bar = $byName['Perkara per Tahun'];
            $bar['type'] = 'bar';
            $bar['id'] = $bar['id'] . '-bar';
            $ordered[] = $bar;
        }
        if (isset($byName['Terdakwa per Tahun'])) {
            $ordered[] = $byName['Terdakwa per Tahun'];
        }
        if (isset($byName['Pengadilan per Tahun'])) {
            $ordered[] = $byName['Pengadilan per Tahun'];
        }
        $charts = $ordered;

        $allYearsAll = ChartDataset::whereIn("dataset", $yearlyMergeDatasets)
            ->whereNotNull("year")
            ->distinct()
            ->orderBy("year")
            ->pluck("year")
            ->toArray();

        return view("livewire.charts.charts-dashboard", [
            "charts" => $charts,
            "tableData" => $tableData,
            "allYears" => $allYearsAll,
        ])->layout("layouts.internal");
    }
}
