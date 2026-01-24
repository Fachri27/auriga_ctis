<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * CaseTaskGenerator
 *
 * LEGACY: This service auto-generates case tasks from processes/tasks/requirements.
 * It is kept for backward compatibility but is considered complex for public-facing
 * UIs. Prefer using `SimpleCaseService` for simple timeline/status flows.
 *
 * @deprecated Use the simplified flow (SimpleCaseService) for public/CMS UI.
 */
class CaseTaskGenerator
{
    /**
     * Generate case tasks + requirements from process/task templates
     * Returns number of tasks created or updated
     */
    public static function generate(int $caseId, int $categoryId): int
    {
        $created = 0;

        $tasks = DB::table('tasks')
            ->join('processes', 'processes.id', '=', 'tasks.process_id')
            ->where('processes.category_id', $categoryId)
            ->select(
                'tasks.id as task_id',
                'tasks.process_id',
                'tasks.due_days',
                'tasks.is_required'
            )
            ->get();

        foreach ($tasks as $task) {
            // compute due_date if due_days present
            $dueDate = $task->due_days ? now()->addDays($task->due_days) : null;

            // insert or update case task
            $existingId = DB::table('case_tasks')
                ->where('case_id', $caseId)
                ->where('task_id', $task->task_id)
                ->value('id');

            if (! $existingId) {
                $caseTaskId = DB::table('case_tasks')->insertGetId([
                    'case_id' => $caseId,
                    'task_id' => $task->task_id,
                    'process_id' => $task->process_id,
                    'status' => 'pending',
                    'due_date' => $dueDate,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $created++;
            } else {
                $caseTaskId = $existingId;
                DB::table('case_tasks')->where('id', $caseTaskId)->update([
                    'process_id' => $task->process_id,
                    'due_date' => $dueDate,
                    'updated_at' => now(),
                ]);
            }

            // create case task requirements if not exist
            $requirements = DB::table('task_requirements')
                ->where('task_id', $task->task_id)
                ->get();

            foreach ($requirements as $req) {
                DB::table('case_task_requirements')->updateOrInsert(
                    [
                        'case_task_id' => $caseTaskId,
                        'requirement_id' => $req->id,
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return $created;
    }
}
