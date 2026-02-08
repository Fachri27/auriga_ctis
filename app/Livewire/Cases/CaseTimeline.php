<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CaseTimeline extends Component
{
    public $open = false;

    public $processes = [];

    public $caseId;
    // public $docId;

    public $process_id;

    public $notes;

    public $timeline_id;

    public $mode = 'create';

    protected $listeners = [
        'open-upload-timeline-modal' => 'openModal',
        'open-edit-timeline-modal' => 'openEdit',
        'refresh-timeline' => '$refresh',
    ];

    public function mount($caseId = null)
    {
        $this->caseId = $caseId;
    }

    public function openModal($caseId)
    {
        $this->mode = 'create';
        $this->resetValidation();
        $this->resetForm();
        $this->caseId = $caseId;

        // get category id
        $categoryId = DB::table('cases')
            ->where('id', $this->caseId)
            ->value('category_id');

        // get processes based on category id
        $this->processes = DB::table('processes')
            ->where('category_id', $categoryId)
            ->get()
            ->toArray();

        // dd($this->processes);

        $this->open = true;
    }

    public function openEdit($timelineId)
    {
        $this->mode = 'edit';
        $this->resetValidation();
        $this->resetForm();

        $time = DB::table('case_timelines')->where('id', $timelineId)->first();

        if (! $time) {
            return;
        }

        $this->timeline_id = $time->id;
        $this->process_id = $time->process_id ? (int) $time->process_id : null;  // FIX
        $this->notes = $time->notes;

        // ambil category melalui case_id
        $categoryId = DB::table('cases')
            ->where('id', $time->case_id)
            ->value('category_id');

        $this->processes = DB::table('processes')
            ->where('category_id', $categoryId)
            ->get()
            ->toArray();

        $this->open = true;
    }

    public function resetForm()
    {
        $this->reset([
            'timeline_id',
            'process_id',
            'notes',
        ]);

    }

    public function save()
    {
        if ($this->mode === 'create') {
            return $this->storeNew();
        }

        return $this->updateExisting();

    }

    public function storeNew()
    {
        $this->validate([
            'notes' => 'required|string',
        ]);

        DB::table('case_timelines')->insert([
            'case_id' => $this->caseId,
            'actor_id' => auth()->id(),
            'process_id' => null,
            'notes' => $this->notes,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->open = false;

        // Refresh component state
        $this->dispatch('refresh-timeline');
        $this->dispatch('refresh-case-detail');
        
    }

    public function updateExisting()
    {
        $this->validate([
            'notes' => 'required|string',
        ]);

        $data = [
            'process_id' => null,
            'notes' => $this->notes,
            'updated_at' => now(),
        ];


        DB::table('case_timelines')
            ->where('id', $this->timeline_id)
            ->update($data);

        $this->open = false;
        $this->dispatch('refresh-case-detail');
    }

    public function delete()
    {
        if(! $this->timeline_id) {
            return;
        }

        DB::table('case_timelines')->where('id', $this->timeline_id)->delete();

        $this->open = false;

        $this->dispatch('refresh-case-detail');
        session()->flash('success', 'Timeline berhasil dihapus.');

    }


    public function render()
    {
        return view('livewire.cases.case-timeline');
    }
}
