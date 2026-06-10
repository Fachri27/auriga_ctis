<?php

namespace App\Livewire\Charts;

use App\Models\ChartDataset;
use Livewire\Component;

class DatasetChart extends Component
{
    public string $dataset = '';
    public string $title = '';
    public int $limit = 30;

    public function getChartData(): array
    {
        return ChartDataset::where('dataset', $this->dataset)
            ->orderBy('value', 'desc')
            ->limit($this->limit)
            ->get()
            ->map(fn($item) => [
                'label' => $item->label,
                'value' => $item->value,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.charts.dataset-chart', [
            'chartData' => $this->getChartData(),
        ])->layout('layouts.internal');
    }
}
