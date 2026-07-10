<?php

namespace App\Livewire;

use App\Models\CaseSubscription;
use Livewire\Component;
use Livewire\WithPagination;

class SubscriptionList extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        CaseSubscription::findOrFail($id)->delete();

        session()->flash('success', 'Langganan dihapus.');
    }

    public function render()
    {
        $subscriptions = CaseSubscription::with('case')
            ->where('email', 'like', '%'.$this->search.'%')
            ->latest()
            ->paginate(10);

        return view('livewire.subscription-list', compact('subscriptions'))->layout('layouts.internal');
    }
}