<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ActorCases extends Component
{
    public $caseId;
    public $actor_id;
    public $type;
    public $name;
    public $description;
    public $metadata;
    public $mode = 'create';
    public $open = false;

    protected $listeners = [
        'open-actor-modal' => 'openModal',
        'open-edit-modal' => 'openEdit',
        'delete-actor' => 'deleteActor',
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

        $this->open = true;
    }

    public function openEdit($actorId)
    {
        $this->mode = 'edit';
        $this->resetValidation();
        $this->resetForm();

        $actor = DB::table('case_actors')->where('id', $actorId)->first();
        if (! $actor) {
            return;
        }

        $this->actor_id = $actor->id;
        $this->type = $actor->type;
        $this->name = $actor->name;
        $this->description = $actor->description;
        $this->metadata = $actor->metadata;

        $this->open = true;
    }

    public function deleteActor($actorId)
    {
        DB::table('case_actors')->where('id', $actorId)->delete();
        $this->dispatch('refresh-case-detail');
    }

    public function resetForm()
    {
        $this->type = null;
        $this->name = null;
        $this->description = null;
        $this->metadata = null;
    }

    public function save()
    {
        $this->validate([
            'type' => 'required|in:corporate,government,citizen',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if($this->mode == 'create') {
            DB::table('case_actors')->insert([
                'case_id' => $this->caseId,
                'type' => $this->type,
                'name' => $this->name,
                'description' => $this->description,
                'metadata' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('case_actors')->where('id', $this->actor_id)->update([
                'type' => $this->type,
                'name' => $this->name,
                'description' => $this->description,
                'metadata' => null,
                'updated_at' => now(),
            ]);
        }

        $this->open = false;
        $this->dispatch('refresh-case-detail');
    }
    public function render()
    {
        return view('livewire.cases.actor-cases');
    }
}
