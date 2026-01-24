<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;
use App\Models\ProcessModel;
use App\Models\Task;
use App\Models\TaskTranslation;
use App\Models\TaskRequirement;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Process::all() as $process) {

            for ($i = 1; $i <= 3; $i++) {

                $task = Task::create([
                    'process_id' => $process->id,
                    'due_days' => rand(3, 14),
                    'is_required' => 1,
                ]);

                TaskTranslation::insert([
                    [
                        'task_id' => $task->id,
                        'locale' => 'id',
                        'name' => "Tugas {$i}",
                        'description' => "Deskripsi tugas {$i} dalam proses {$process->id}"
                    ],
                    [
                        'task_id' => $task->id,
                        'locale' => 'en',
                        'name' => "Task {$i}",
                        'description' => "Description task {$i} for process {$process->id}"
                    ],
                ]);

                TaskRequirement::insert([
                    [
                        'task_id' => $task->id,
                        'name' => 'Upload Document',
                        'field_type' => 'file',
                        'is_required' => 1,
                    ],
                    [
                        'task_id' => $task->id,
                        'name' => 'Notes',
                        'field_type' => 'text',
                        'is_required' => 0,
                    ],
                ]);
            }
        }
    }
}
