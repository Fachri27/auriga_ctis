<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class TaskRequirementCase extends Component
{
    use WithFileUploads;

    public $open = false;

    public $case_task_id;

    public $task;

    public $requirements = [];
    public $taskFiles = [];

    protected $listeners = [
        'open-task-requirement-modal' => 'openModal',
    ];

    private function loadTask()
    {
        $this->task = DB::table('case_tasks')
            ->join('tasks', 'tasks.id', '=', 'case_tasks.task_id')
            ->join('task_translations', function ($q) {
                $q->on('task_translations.task_id', '=', 'case_tasks.task_id')
                    ->where('task_translations.locale', 'id');
            })
            ->where('case_tasks.id', $this->case_task_id)
            ->select('case_tasks.*', 'task_translations.name', 'tasks.process_id')
            ->first();
    }

    private function loadRequirements()
    {
        $this->requirements = [];

        $task = DB::table('case_tasks')->where('id', $this->case_task_id)->first();

        $rows = DB::table('task_requirements')
            ->leftJoin('case_task_requirements', function ($q) {
                $q->on('case_task_requirements.requirement_id', '=', 'task_requirements.id')
                    ->where('case_task_requirements.case_task_id', $this->case_task_id);
            })
            ->where('task_requirements.task_id', $task->task_id)
            ->select(
                'task_requirements.*',
                'case_task_requirements.id as filled_id',
                'case_task_requirements.value'
            )
            ->get();

        foreach ($rows as $req) {
            $this->requirements[] = [
                'requirement_id' => $req->id,
                'name' => $req->name,
                'field_type' => $req->field_type,
                'options' => $req->options ? json_decode($req->options, true) : null,
                'is_required' => $req->is_required,
                'value' => $req->value,
            ];
        }
    }

    public function openModal($id)
    {
        $this->case_task_id = $id;

        $this->loadTask();
        $this->loadRequirements();
        $this->open = true;
    }

    public function save()
    {
        foreach ($this->requirements as $req) {

            $value = $req['value'] ?? null;

            // HANDLE FILE REQUIREMENT
            if ($req['field_type'] === 'file') {

                if (! isset($this->taskFiles[$req['requirement_id']])) {
                    if ($req['is_required']) {
                        throw new \Exception("File '{$req['name']}' wajib diupload");
                    }

                    continue;
                }

                $file = $this->taskFiles[$req['requirement_id']];

                $value = $file->store(
                    "case_tasks/{$this->case_task_id}",
                    'public'
                );
            }

            DB::table('case_task_requirements')->updateOrInsert(
                [
                    'case_task_id' => $this->case_task_id,
                    'requirement_id' => $req['requirement_id'],
                ],
                [
                    'value' => $value,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }

        DB::table('case_tasks')
            ->where('id', $this->case_task_id)
            ->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

        // timeline
        DB::table('case_timelines')->insert([
            'case_id' => $this->task->case_id,
            'process_id' => $this->task->process_id,
            'actor_id' => auth()->id(),
            'notes' => "Task '{$this->task->name}' submitted",
            'created_at' => now(),
        ]);

        $this->open = false;

        // refresh case detail page
        $this->dispatch('refresh-case-detail');
    }

    public function render()
    {
        return view('livewire.cases.task-requirement-case');
    }
}
