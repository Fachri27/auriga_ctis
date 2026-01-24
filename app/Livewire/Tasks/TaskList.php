<?php

namespace App\Livewire\Tasks;

use App\Models\Process;
use App\Models\Task;
use Livewire\Component;
use Livewire\WithPagination;

class TaskList extends Component
{
    use WithPagination;

    public $task;
    public $requirements;

    // inline editing
    public $editId = null;
    public $inline = [];

    public $search = '';
    public $processFilter;

    protected $listeners = [
        'requirementSaved' => 'reloadList'
    ];

    public function rules()
    {
        return [
            'inline.*.name' => 'nullable|string|max:255',
            'inline.*.due_days' => 'nullable|integer|min:0',
            'inline.*.is_required' => 'nullable|boolean',
        ];
    }


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function onProcessChange($processId)
    {
        $this->processFilter = $processId ?: null;
        $this->resetPage();
    }

    public function startInlineEdit(Task $task)
    {
        $this->editId = $task->id;
        $this->inline[$task->id] = [
            'name' => $task->translation('id')?->name ?? '',
            'due_days' => $task->due_days,
            'is_required' => $task->is_required ? 1 : 0,
        ];
    }

    public function cancelInlineEdit()
    {
        $this->editId = null;
        unset($this->inline[$this->editId]);
    }

    public function saveInline($taskId)
    {
        $data = $this->inline[$taskId] ?? null;
        if (! $data) return;

        $this->validateOnly("inline.$taskId.name");

        $task = Task::find($taskId);
        if (! $task) return;

        $task->update([
            'due_days' => $data['due_days'],
            'is_required' => (bool)$data['is_required'],
        ]);

        // update translation (id)
        $translation = $task->translations()->where('locale', 'id')->first();
        if ($translation) {
            $translation->update(['name' => $data['name']]);
        } else {
            $task->translations()->create([
                'locale' => 'id',
                'name' => $data['name'],
                'description' => null,
            ]);
        }

        $this->editId = null;
        $this->dispatch('task-updated');
    }

    public function deleteTask($id)
    {
        $t = Task::find($id);
        if ($t) $t->delete();
        $this->dispatch('task-updated');
        session()->flash('success', 'Tasks deleted successfully!');
    }

    public function render()
    {
        $query = Task::with('translations', 'process')->orderBy('created_at', 'desc');

        if ($this->processFilter) {
            $query->where('process_id', $this->processFilter);
        }

        if ($this->search) {
            $query->whereHas('translations', function ($q) {
                $q->where('locale', 'id')
                  ->where('name', 'like', '%'.$this->search.'%');
            });
        }

        $tasks = $query->paginate(10);
        $processes = Process::orderBy('order_no')->get();

        return view('livewire.tasks.task-list', compact('tasks','processes'))->layout('layouts.internal');
    }
}
