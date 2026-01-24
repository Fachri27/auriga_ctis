<?php

namespace App\Livewire\Tasks;

use App\Models\TaskRequirement;
use Livewire\Component;
use Livewire\WithPagination;

class TaskRequirementList extends Component
{
    use WithPagination;

    public $task;
    // public $requirements;
    public $search = '';
    public $perPage = 10;
    public $editId = null;
    public $inline = [];

    protected $listeners = [
        'task-created' => '$refresh',
        'task-updated' => '$refresh',
    ];

    public function mount()
    {

    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function startInlineUpdate(TaskRequirement $task)
    {
        $this->editId = $task->id;
        $this->inline[$task->id] = [
            'name' => $task->name ?? '',
            'is_required' => $task->is_required,
            'field_type' => $task->field_type,
        ];
    }

    public function cancelInlineUpdate()
    {
        $this->editId = null;
        unset($this->inline[$this->editId]);
    }

    public function saveInline($taskId)
    {
        $data = $this->inline[$taskId] ?? null;
        if(! $data) return;

        // $this->validateOnly("inline.$taskId.name");

        $task = TaskRequirement::find($taskId);
        if(! $task) return;

        $task->update([
            'name' => $data['name'],
            'is_required' => $data['is_required'] ? true : false,
            'field_type' => $data['field_type'],
        ]);

        $this->editId = null;
        $this->dispatch('task-updated');
    }

    public function deletedTask($id)
    {
        $t = TaskRequirement::find($id);
        if($t) $t->delete();
        $this->dispatch('task-updated');
        session()->flash('success', 'Tasks deleted successfully!');
    }


    public function render()
    {
        // $this->requirements = TaskRequirement::paginate($this->perPage);
        // dd($this->requirements);

        // if ($this->processFilter) {
        //     $query->where('process_id', $this->processFilter);
        // }

        // if ($this->search) {
        //     $query->whereHas('translations', function ($q) {
        //         $q->where('locale', 'id')
        //           ->where('name', 'like', '%'.$this->search.'%');
        //     });
        // }

        // $tasks = $query->paginate($this->perPage);
        return view('livewire.tasks.task-requirement-list', [
            'requirements' => TaskRequirement::paginate($this->perPage)
        ])->layout('layouts.internal');
    }
}
