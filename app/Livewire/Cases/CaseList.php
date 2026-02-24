<?php

namespace App\Livewire\Cases;

use App\Models\CaseModel;
use Livewire\{Component, WithPagination};

class CaseList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter = '';

    public $filterVerif = '';

    protected $listeners = [
        'case-created' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function deleteCase($id)
    {
        try {
            CaseModel::query()->whereKey($id)->delete();
            session()->flash('success', 'Cases deleted');
        } catch (\Throwable $th) {
            // throw $th;
            session()->flash('error', 'Failed to delete case');
        }
    }

    public function render()
    {
        // $this->filter = request('filter');
        $query = CaseModel::query()
            ->with(['translations', 'verifiedBy', 'status']);

        if ($this->filter === 'investigation') {

            $query->whereHas('status', fn ($q) => $q->where('key', 'investigation'));

        } elseif ($this->filter === 'published') {

            $query->where('is_public', true);

        } elseif ($this->filter === 'closed') {

            $query->whereHas('status', fn ($q) => $q->where('key', 'closed'));

        }

        if ($this->filterVerif === 'me') {
            $query->where('verified_by', auth()->id());
        }

        if ($this->filterVerif === 'pending') {
            $query->whereHas('status', fn ($q) => $q->where('key', 'open'));
        }

        if ($this->filterVerif === 'rejected') {
            $query->whereHas('status', fn ($q) => $q->where('key', 'rejected'));
        }

        // Apply search
        $query->when($this->search, fn ($q) => $q->where('case_number', 'like', "%{$this->search}%")
        );

        $cases = $query->orderByDesc('id')->paginate(10);

        return view('livewire.cases.case-list', compact('cases'))->layout('layouts.internal');
    }
}
