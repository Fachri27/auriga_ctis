<?php

namespace App\Livewire\Charts;

use App\Models\ChartDataset;
use Livewire\Component;

class ChartsDashboard extends Component
{
    public function render()
    {
        $labelMap = [
            "pengadilan" => "Pengadilan",
            "terdakwa" => "Terdakwa",
            "perkara" => "Perkara",
        ];

        $datasets = ChartDataset::select("dataset")
            ->whereIn("dataset", [
                "pengadilan",
                "klasifikasi_perkara",
                "terdakwa",
                "perkara",
            ])
            ->groupBy("dataset")
            ->pluck("dataset");

        $charts = [];
        foreach ($datasets as $ds) {
            $isYearly = ChartDataset::where("dataset", $ds)
                ->whereNotNull("year")
                ->exists();

            $query = ChartDataset::where("dataset", $ds);

            if ($isYearly) {
                $query = ChartDataset::where("dataset", $ds)
                    ->whereNotNull("year")
                    ->selectRaw("year as lbl, SUM(value) as total")
                    ->groupBy("year")
                    ->orderBy("year");
            } else {
                $limit = $ds === "pengadilan" ? 200 : 30;
                $query = ChartDataset::where("dataset", $ds)
                    ->orderBy("value", "desc")
                    ->limit($limit);
            }

            $data = $query
                ->get()
                ->map(
                    fn($item) => [
                        "label" => $item->lbl ?? $item->label,
                        "value" => (int) ($item->total ?? $item->value),
                    ],
                )
                ->toArray();

            if (empty($data)) {
                continue;
            }

            $charts[] = [
                "id" => "ch-" . str_replace("_", "-", $ds),
                "title" =>
                    $labelMap[$ds] ?? ucfirst(str_replace("_", " ", $ds)),
                "data" => $data,
            ];
        }

        $allYears = ChartDataset::whereNotNull("year")
            ->distinct()
            ->orderBy("year")
            ->pluck("year")
            ->toArray();

        return view("livewire.charts.charts-dashboard", [
            "charts" => $charts,
            "allYears" => $allYears,
        ])->layout("layouts.internal");
    }
}
