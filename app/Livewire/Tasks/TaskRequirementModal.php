<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TaskRequirementModal extends Component
{
    public $show = false;

    public $requirement_id = null;

    public $task_id;

    public $name;

    public $field_type;

    public $is_required = false;

    public $options = ''; // <-- harus string!

    protected $rules = [
        'task_id' => 'required|exists:tasks,id',
        'name' => 'required|string|max:255',
        'field_type' => 'required|string',
        'options' => 'nullable|string',
    ];

    protected $listeners = [
        'open-requirement-modal' => 'open',
    ];

    public function open()
    {
        $this->resetValidation();
        $this->reset([
            'task_id',
            'name',
            'field_type',
            'is_required',
            'options',
            'requirement_id',
        ]);

        $this->show = true;
    }

    public function save()
    {
        $this->validate();

        try {

            // --- FIX UTAMA ---
            $optionsValue = $this->options;

            if (is_array($optionsValue)) {
                $optionsValue = json_encode($optionsValue, JSON_UNESCAPED_UNICODE);
            }

            if ($optionsValue === '' || $optionsValue === null) {
                $optionsValue = json_encode([]); // atau null
            }

            DB::table('task_requirements')->insert([
                'task_id' => $this->task_id,
                'name' => $this->name,
                'field_type' => $this->field_type,
                'is_required' => $this->is_required ? 1 : 0,
                'options' => $optionsValue,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->show = false;
            $this->dispatch('close-requirement-modal');
            session()->flash('success', 'Requirement saved.');

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }

    public function render()
    {
        $tasks = Task::orderBy('id')->get();

        return view('livewire.tasks.task-requirement-modal', compact('tasks'));
    }
}
