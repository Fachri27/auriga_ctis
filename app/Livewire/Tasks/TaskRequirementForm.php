<?php

namespace App\Livewire\Tasks;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class TaskRequirementForm extends Component
{
    public $requirement_id;

    public $task_id;

    public $name;

    public $field_type;

    public $is_required;

    public $options = [];

    protected $rules = [
        'task_id' => 'required|exists:tasks,id',
        'name' => 'required|string|max:255',
        'field_type' => 'required|string',
        'options' => 'nullable|array',
    ];

    public function mount($id)
    {
        $data = DB::table('task_requirements')->where('id', $id)->first();

        if (! $data) {
            abort(404);
        }

        $this->requirement_id = $id;
        $this->task_id = $data->task_id;
        $this->name = $data->name;
        $this->field_type = $data->field_type;
        $this->is_required = $data->is_required ? true : false;
        $this->options = $data->options ? explode(',', $data->options) : [];
    }

    // add/remove options
    public function addOption()
    {
        $this->options[] = '';
    }

    public function removeOption($i)
    {
        unset($this->options[$i]);
        $this->options = array_values($this->options);
    }

    public function save()
    {
        $this->validate();

        try {
            DB::table('task_requirements')
                ->where('id', $this->requirement_id)
                ->update([
                    'task_id' => $this->task_id,
                    'name' => $this->name,
                    'field_type' => $this->field_type,
                    'is_required' => $this->is_required ? 1 : 0,
                    'options' => json_encode($this->options),
                ]);

            session()->flash('success', 'Requirement updated successfully.');

            return redirect()->route('taskreq.index');

        } catch (\Throwable $e) {
            // session()->flash('error', 'Failed to update: ' . $e->getMessage());
            dd($e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.tasks.task-requirement-form', [
            'tasks' => Task::orderBy('id')->get(),
        ])->layout('layouts.internal');
    }
}
