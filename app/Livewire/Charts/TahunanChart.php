<?php

namespace App\Livewire\Charts;

use App\Models\ChartDataset;
use Livewire\Component;

class TahunanChart extends Component
{
    public string $dataset = 'putusan_perkara';
    public ?int $selectedYear = null;
    public string $view = 'tahunan';

    public function mount()
    {
        $this->selectedYear = (int) now()->format('Y');
    }

    public function getYears(): array
    {
        return ChartDataset::where('dataset', $this->dataset)
            ->whereNotNull('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();
    }

    public function getChartData(): array
    {
        $query = ChartDataset::where('dataset', $this->dataset);

        if ($this->view === 'tahunan') {
            $query = ChartDataset::where('dataset', $this->dataset)
                ->whereNotNull('year')
                ->selectRaw('year, SUM(value) as total')
                ->groupBy('year')
                ->orderBy('year');

            return $query->get()->map(fn($item) => [
                'label' => (string) $item->year,
                'value' => (int) $item->total,
            ])->toArray();
        }

        if ($this->selectedYear) {
            $query->where('year', $this->selectedYear);
        }

        return $query->orderBy('value', 'desc')
            ->get()
            ->map(fn($item) => [
                'label' => $item->label,
                'value' => $item->value,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.charts.tahunan-chart', [
            'chartData' => $this->getChartData(),
            'years' => $this->getYears(),
        ])->layout('layouts.internal');
    }
}
