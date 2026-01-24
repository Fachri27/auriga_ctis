<?php

namespace App\Livewire\Tasks;

use App\Models\Process;
use App\Models\Task;
use App\Models\TaskTranslation;
use Livewire\Component;

class TaskForm extends Component
{
    public $taskId;
    public $task;
    public $processes;
    public $process_id, $due_days;
    public $is_required;

    public $name_id, $name_en, $desc_id, $desc_en;

    protected $rules = [
        'process_id' => 'required|exists:processes,id',
        'name_id' => 'required|string|max:255',
    ];

    public function mount($id)
    {
        $this->processes = Process::orderBy('order_no')->get();
        $this->task = Task::with('translations')->findOrFail($id);
        $this->taskId = $this->task->id;

        $this->process_id = $this->task->process_id;
        $this->due_days = $this->task->due_days;
        $this->is_required = (bool)$this->task->is_required;
        
        $this->name_id = $this->task->translation('id')?->name ?? '';
        $this->name_en = $this->task->translation('en')?->name ?? '';
        $this->desc_id = $this->task->translation('id')?->description ?? '';
        $this->desc_en = $this->task->translation('en')?->description ?? '';
    }

    public function save()
    {
        $this->validate();

        $this->task->update([
            'process_id' => $this->process_id,
            'due_days' => $this->due_days,
            'is_required' => $this->is_required,
        ]);

        // dd($this->is_required);

        foreach(['id', 'en'] as $locale){
            TaskTranslation::updateOrCreate(
                ['task_id' => $this->task->id, 'locale' => $locale],
                [
                    'name' => $locale === 'id' ? $this->name_id : $this->name_en,
                    'description' => $locale === 'id' ? $this->desc_id : $this->desc_en,
                ]
            );
        }

        session()->flash('success', 'Task updated successfully');
        return redirect()->route('task.index');
    }

    public function render()
    {
        // $processes = Process::orderBy('order_no')->get();
        return view('livewire.tasks.task-form')->layout('layouts.internal');
    }
}
