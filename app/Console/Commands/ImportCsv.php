<?php

namespace App\Console\Commands;

use App\Models\ChartDataset;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class ImportCsv extends Command
{
    protected $signature = 'csv:import {file : Path to CSV file} {dataset : Dataset key (e.g. klasifikasi_perkara)} {--year= : Year for this data} {--value-col= : Force value column index} {--label-col= : Force label column index}';

    protected $description = 'Import CSV into chart_data table';

    public function handle(): int
    {
        $file = $this->argument('file');
        $dataset = $this->argument('dataset');
        $year = $this->option('year');

        if (!file_exists($file)) {
            $this->error("File not found: {$file}");
            return Command::FAILURE;
        }

        $rows = array_map('str_getcsv', file($file));
        $header = array_shift($rows);

        if (count($header) < 2) {
            $this->error('CSV must have at least 2 columns');
            return Command::FAILURE;
        }

        $labelIdx = (int) ($this->option('label-col') ?? 0);
        $valueIdx = (int) ($this->option('value-col') ?? 1);
        $yearIdx = null;

        if (!$this->option('label-col') && !$this->option('value-col')) {
            foreach ($header as $i => $col) {
                $lower = strtolower(trim(preg_replace('/\s+/', ' ', $col)));
                if (in_array($lower, ['year', 'tahun', 'tahun_perkara', 'tahun perkara'])) {
                    $yearIdx = $i;
                } elseif (in_array($lower, ['value', 'jumlah', 'total', 'count', 'no_perkara', 'no perkara', 'jumlah perkara', 'jumlah_perkara'])) {
                    $valueIdx = $i;
                } elseif (in_array($lower, ['label', 'name', 'klasifikasi', 'klasifikasi_perkara', 'klasifikasi perkara', 'klasifikasi perkara_clean', 'category', 'nama hakim', 'nama_hakim', 'jaksa penuntut umum', 'jaksa_penuntut_umum', 'kabupaten', 'pengadilan'])) {
                    $labelIdx = $i;
                } elseif (in_array($lower, ['terdakwa'])) {
                    $valueIdx = $i;
                }
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
            $rowYear = $yearIdx !== null ? (int) trim($row[$yearIdx]) : ($year ? (int) $year : null);

            if ($label === 'null' || $label === '' || $value === 0) {
                continue;
            }

            ChartDataset::updateOrCreate(
                ['dataset' => $dataset, 'label' => $label, 'year' => $rowYear],
                ['value' => $value]
            );

            $imported++;
        }

        $this->info("Imported {$imported} rows into '{$dataset}'" . ($year ? " (year: {$year})" : ''));
        return Command::SUCCESS;
    }
}
