<?php

namespace App\Livewire\Tasks;

use App\Models\Process;
use App\Models\Task;
use App\Models\TaskTranslation;
use Livewire\Component;

class TaskModal extends Component
{
    public $show = false;
    public $process_id, $due_days;
    public $is_required;

    public $name_id, $name_en, $desc_id, $desc_en;

    protected $rules = [
        'process_id' => 'required|exists:processes,id',
        'name_id' => 'required|string|max:255',
    ];

    public function open()
    {
        $this->resetValidation();

        $this->show = true;
    }

    public function save()
    {
        $this->validate();

        $tasks = Task::create([
            'process_id' => $this->process_id,
            'due_days' => $this->due_days,
            'is_required' => $this->is_required,
        ]);

        // dd($tasks->id);

        foreach(['id', 'en'] as $locale){
            TaskTranslation::updateOrCreate(
                ['task_id' => $tasks->id, 'locale' => $locale],
                [
                    'name' => $locale === 'id' ? $this->name_id : $this->name_en,
                    'description' => $locale === 'id' ? $this->desc_id : $this->desc_en,
                ]
            );
        }

        $this->dispatch('close-task-modal');
        $this->dispatch('task-created');
        $this->reset([
            'process_id',
            'due_days',
            'is_required',
            'name_id',
            'name_en',
            'desc_id',
            'desc_en',
        ]);

    }
    public function render()
    {
        $processes = Process::orderBy('order_no')->get();
        return view('livewire.tasks.task-modal', compact('processes'));
    }
}
