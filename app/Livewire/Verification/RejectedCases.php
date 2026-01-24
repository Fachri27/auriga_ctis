<?php

namespace App\Livewire\Verification;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CaseModel;

class RejectedCases extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        // Business rule: find cases whose status name is 'rejected'
        // Adjust the status predicate to match your actual status naming
        $cases = CaseModel::with(['status', 'translations'])
            ->whereHas('status', function ($q) {
                $q->where('name', 'rejected');
            })
            ->orderByDesc('event_date')
            ->paginate(15);

        return view('livewire.verification.rejected-cases', compact('cases'))->layout('layouts.internal');
    }
}
