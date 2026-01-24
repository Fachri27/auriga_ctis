<?php

namespace App\Livewire\Status;

// use DB;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StatusList extends Component
{
    use WithPagination;

    public $search = '';
    protected $listeners = [
        'status-created' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        try {
            DB::table('statuses')->where('id', $id)->delete();
            session()->flash('success', 'statuses deleted');
        } catch (\Throwable $th) {
            //throw $th;
            session()->flash('error', 'Failed to delete status');
        }
    }

    public function render()
    {
        $statuses = DB::table('statuses')
            ->when($this->search, fn($q) => 
                 $q->where('name', 'like', "%{$this->search}%")
        )
        ->paginate(10);
        return view('livewire.status.status-list', compact('statuses'))->layout('layouts.internal');
    }
}
