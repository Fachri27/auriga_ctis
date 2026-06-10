<?php

namespace App\Livewire\Charts;

use App\Models\ChartDataset;
use Livewire\Component;

class KlasifikasiChart extends Component
{
    public string $dataset = 'klasifikasi_perkara';

    public function getChartData(): array
    {
        return ChartDataset::where('dataset', $this->dataset)
            ->orderBy('value', 'desc')
            ->get()
            ->map(fn($item) => [
                'label' => $item->label,
                'value' => $item->value,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.charts.klasifikasi-chart', [
            'chartData' => $this->getChartData(),
        ])->layout('layouts.internal');
    }
}
