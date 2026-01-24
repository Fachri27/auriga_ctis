<?php

namespace App\Livewire\Verification;

use App\Models\CaseModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class AssignedToMe extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $userId = Auth::id();

        // Business rule: currently considers cases where `verified_by` equals the current user.
        // Adjust this query if your assignment logic differs (e.g., CaseActor or another column).
        $cases = CaseModel::with(['status', 'translations'])
            ->where('verified_by', $userId)
            ->orderByDesc('event_date')
            ->paginate(15);

        return view('livewire.verification.assigned-to-me', compact('cases'))->layout('layouts.internal');
    }
}
