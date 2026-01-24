<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

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
            DB::table('cases')->where('id', $id)->delete();
            session()->flash('success', 'Cases deleted');
        } catch (\Throwable $th) {
            // throw $th;
            session()->flash('error', 'Failed to delete case');
        }
    }

    public function render()
    {
        // $this->filter = request('filter');
        $userId = auth()->id();
        $query = DB::table('cases')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->leftJoin('users', 'users.id', '=', 'cases.verified_by')
            ->select('cases.*', 'users.name as verified_by_name');

        if ($this->filter === 'investigation') {

            $query->where('statuses.key', 'investigation');

        } elseif ($this->filter === 'published') {

            $query->where('cases.is_public', true);

        } elseif ($this->filter === 'closed') {

            $query->where('statuses.key', 'closed');

        }

        if ($this->filterVerif === 'me') {
            $query->where('cases.verified_by', auth()->id());
        }

        if ($this->filterVerif === 'pending') {
            $query->where('statuses.key', 'open');
        }

        if ($this->filterVerif === 'rejected') {
            $query->where('statuses.key', 'rejected');
        }

        // Apply search
        $query->when($this->search, fn ($q) => $q->where('case_number', 'like', "%{$this->search}%")
        );

        $cases = $query->orderBy('id', 'desc')->paginate(10);

        return view('livewire.cases.case-list', compact('cases'))->layout('layouts.internal');
    }
}
