<?php

namespace App\Livewire\Reports;

use App\Models\Report;
use Livewire\Component;

class ReportTable extends Component
{
    public function render()
    {
        $reports = Report::with('translations')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('livewire.reports.report-table', compact('reports'))->layout('layouts.internal');
    }
}
