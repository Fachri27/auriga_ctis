<?php

namespace App\Livewire\Reports;

use App\Models\Report;
use Livewire\Component;
use Livewire\WithPagination;

class ReportTable extends Component
{
    use WithPagination;
    public function render()
    {
        $reports = Report::with('translations')
            ->orderBy('created_at', 'desc')
            ->paginate(5);
        return view('livewire.reports.report-table', compact('reports'))->layout('layouts.internal');
    }
}
