<?php

namespace App\Livewire\Charts;

use App\Models\ChartDataset;
use Livewire\Component;
use Livewire\WithFileUploads;

class CsvUpload extends Component
{
    use WithFileUploads;

    public $file;
    public $dataset = 'klasifikasi_perkara';
    public $year;
    public $preview = [];



    protected $rules = [
        'file' => 'required|file|mimes:csv,txt|max:2048',
        'dataset' => 'required|string|max:100',
        'year' => 'nullable|integer|min:2000|max:2099',
    ];

    public function updatedFile()
    {
        $this->validateOnly('file');
        $this->preview();
    }

    public function preview()
    {
        $path = $this->file->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        $this->preview = [
            'header' => $header,
            'rows' => array_slice($rows, 0, 10),
            'total' => count($rows),
        ];
    }

    public function import()
    {
        $this->validate();

        $path = $this->file->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);

        $labelIdx = 0;
        $valueIdx = 1;
        $yearIdx = null;

        foreach ($header as $i => $col) {
            $lower = strtolower(trim($col));
            if (in_array($lower, ['year', 'tahun', 'tahun_perkara'])) {
                $yearIdx = $i;
            } elseif (in_array($lower, ['value', 'jumlah', 'total', 'count', 'no_perkara', 'no perkara'])) {
                $valueIdx = $i;
            } elseif (in_array($lower, ['label', 'name', 'klasifikasi', 'klasifikasi_perkara', 'klasifikasi perkara', 'category'])) {
                $labelIdx = $i;
            }
        }

        $imported = 0;
        foreach ($rows as $row) {
            if (count($row) < 2) {
                continue;
            }

            $label = trim($row[$labelIdx]);
            $rawValue = str_replace(['.', ','], '', $row[$valueIdx]);
            $value = (int) $rawValue;
            $rowYear = $yearIdx !== null ? (int) trim($row[$yearIdx]) : ($this->year ? (int) $this->year : null);

            if ($label === 'null' || $label === '' || $value === 0) {
                continue;
            }

            ChartDataset::updateOrCreate(
                ['dataset' => $this->dataset, 'label' => $label, 'year' => $rowYear],
                ['value' => $value]
            );

            $imported++;
        }

        $this->preview = [];
        $this->file = null;

        session()->flash('success', "{$imported} data berhasil diimport ke '{$this->dataset}'");
    }

    public function deleteDataset(string $dataset)
    {
        ChartDataset::where('dataset', $dataset)->delete();
        session()->flash('success', "Dataset '{$dataset}' berhasil dihapus");
    }

    public function editDataset(string $dataset)
    {
        $this->dataset = $dataset;
        $this->year = null;
        $this->file = null;
        $this->preview = [];
        session()->flash('success', "Dataset '{$dataset}' — upload file CSV baru untuk mengganti data");
    }

    public function render()
    {
        $datasets = ChartDataset::select('dataset')
            ->selectRaw('count(*) as total')
            ->selectRaw('count(distinct year) as years')
            ->groupBy('dataset')
            ->orderBy('dataset')
            ->get();

        return view('livewire.charts.csv-upload', [
            'datasets' => $datasets,
        ])->layout('layouts.internal');
    }
}
