<?php

namespace App\Livewire\Verification;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\CaseModel;

class PendingReview extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        // Business rule: cases not public and not yet verified
        $cases = CaseModel::with(['status', 'translations'])
            ->where('is_public', false)
            ->whereNull('verified_by')
            ->orderByDesc('event_date')
            ->paginate(15);

        return view('livewire.verification.pending-review', compact('cases'))->layout('layouts.internal');
    }
}
