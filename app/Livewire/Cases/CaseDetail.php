<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\{DB, Log};
use App\Models\{CaseModel, Status};
use App\Services\{CaseActionService, CaseStatusService};
use Livewire\{Component, WithFileUploads};

class CaseDetail extends Component
{
    use WithFileUploads;

    public $case_id;

    public $case;

    public $activeTab = 'overview';

    // public array $availableStatuses = [
    //     'investigation' => 'Penyelidikan',
    //     'prosecution' => 'Penuntutan',
    //     'trial' => 'Pengadilan',
    //     'closed' => 'Ditutup',
    //     'rejected' => 'Ditolak',
    //     'executed' => 'Eksekusi',
    //     'published' => 'Publikasi',
    //     'open' => 'Terbuka',
    //     'completed' => 'Selesai',
    //     'penyidikan' => 'Penyidikan',
    //     'vonis' => 'Vonis',
    //     'verdict' => 'Putusan',
    //     'berkekuatan-hukum-tetap' => 'Berkekuatan Hukum Tetap',
    //     'sanksi-administratif' => 'Sanksi Administrasi',
    // ];

    public array $availableStatuses = [];

    protected $listeners = [
        'refresh-case-detail' => '$refresh',
    ];

    public function mount($id)
    {
        // app()->setlocale(\session('locale', 'id')); // pastikan locale sudah di-set sebelum load case
        // Validate case ID
        if (! is_numeric($id) || $id <= 0) {
            abort(404, 'Invalid case ID');
        }

        $this->case_id = (int) $id;
        $this->loadCase();

        // Check if case exists
        if (! $this->case) {
            abort(404, 'Case not found');
        }

        $this->availableStatuses = Status::pluck('name','key')->toArray();
    }

    private function loadCase()
    {
        $locale = app()->getLocale();

        // Ambil semua translations untuk case ini
        $translations = DB::table('case_translations')
            ->where('case_id', $this->case_id)
            ->get();

        // Pilih translation: locale aktif → 'id' → apapun yang ada
        $trans = $translations->firstWhere('locale', $locale)
            ?? $translations->firstWhere('locale', 'id')
            ?? $translations->first();

        $this->case = DB::table('cases')
            ->leftJoin('case_translations', function ($q) use ($trans) {
                $q->on('case_translations.case_id', '=', 'cases.id')
                    ->where('case_translations.id', $trans?->id ?? 0);
            })
            ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
            ->leftJoin('statuses', 'statuses.id', '=', 'cases.status_id')
            ->select(
                'cases.*',
                'case_translations.title',
                'case_translations.summary',
                'case_translations.description',
                'categories.slug as category_name',
                'statuses.name as status_name',
                'statuses.key as status_key'
            )
            ->where('cases.id', $this->case_id)
            ->first();

        if ($this->case) {
            $this->case->bukti = json_decode($this->case->bukti, true) ?? [];
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    private function checkTaskCompletion($case_id)
    {
        try {
            // Check if all tasks are approved
            $pendingTasks = DB::table('case_tasks')
                ->where('case_id', $case_id)
                ->where('status', '!=', 'approved')
                ->count();

            $case = DB::table('cases')->where('id', $case_id)->first();

            if (! $case) {
                return;
            }

            // If all tasks are approved and not yet marked as completed
            if ($pendingTasks === 0 && ! $case->is_tasks_completed) {
                DB::table('cases')
                    ->where('id', $case_id)
                    ->update([
                        'is_tasks_completed' => true,
                        'tasks_completed_at' => now(),
                        'updated_at' => now(),
                    ]);

                // Log to timeline (but do NOT change legal status)
                DB::table('case_timelines')->insert([
                    'case_id' => $case_id,
                    'actor_id' => auth()->id(),
                    'notes' => 'All tasks have been approved. Task completion marked.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Log::info("Case {$case_id} - All tasks completed (status unchanged)");
            }
            // If tasks are no longer all approved, reset the flag
            elseif ($pendingTasks > 0 && $case->is_tasks_completed) {
                DB::table('cases')
                    ->where('id', $case_id)
                    ->update([
                        'is_tasks_completed' => false,
                        'tasks_completed_at' => null,
                        'updated_at' => now(),
                    ]);

                Log::info("Case {$case_id} - Task completion reset (pending tasks exist)");
            }
        } catch (\Throwable $th) {
            Log::error("Error in checkTaskCompletion for case {$case_id}: ".$th->getMessage());
            // Don't throw, just log the error
        }
    }

    public function approveTask($task_id)
    {
        // Authorization check - require case.task.approve or admin
        if (! (auth()->user()->can('case.task.approve') || (method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()))) {
            session()->flash('error', 'You do not have permission to approve tasks.');

            return;
        }

        // Validate input
        if (! is_numeric($task_id) || $task_id <= 0) {
            session()->flash('error', 'Invalid task ID.');

            return;
        }

        DB::beginTransaction();

        try {
            // Verify task exists and belongs to this case
            $task = DB::table('case_tasks')
                ->join('task_translations', function ($q) {
                    $q->on('task_translations.task_id', 'case_tasks.task_id')
                        ->where('task_translations.locale', 'id');
                })
                ->where('case_tasks.id', $task_id)
                ->where('case_tasks.case_id', $this->case_id)
                ->select('case_tasks.*', 'task_translations.name')
                ->first();

            if (! $task) {
                DB::rollBack();
                session()->flash('error', 'Task not found or does not belong to this case.');

                return;
            }

            // Check if already approved
            if ($task->status === 'approved') {
                DB::rollBack();
                session()->flash('error', 'Task is already approved.');

                return;
            }

            // Check if task is submitted (optional business rule)
            if ($task->status !== 'submitted' && $task->status !== 'pending') {
                DB::rollBack();
                session()->flash('error', 'Task must be submitted before approval.');

                return;
            }

            // Update task status with approved_by and approved_at
            DB::table('case_tasks')
                ->where('id', $task_id)
                ->where('case_id', $this->case_id)
                ->update([
                    'status' => 'approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                    'updated_at' => now(),
                ]);

            // Add timeline entry
            DB::table('case_timelines')->insert([
                'case_id' => $this->case_id,
                'actor_id' => auth()->id(),
                'notes' => "Task '{$task->name}' has been approved by ".auth()->user()->name.'.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Check and update task completion flags (does NOT change case status)
            $this->checkTaskCompletion($this->case_id);

            DB::commit();

            Log::info("Task {$task_id} approved for case {$this->case_id} by user ".auth()->id());
            session()->flash('success', 'Task approved successfully.');
            $this->dispatch('refresh-case-detail');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Error approving task {$task_id}: ".$th->getMessage());
            session()->flash('error', 'Failed to approve task: '.$th->getMessage());
            throw $th;
        }
    }

    public function publishCases()
    {
        // Log::critical('PUBLISH CLICKED', [
        //     'case_id' => $this->case_id,
        //     'user_id' => auth()->id(),
        // ]);
        if (! auth()->user()->can('case.publish')) {
            session()->flash('error', 'You do not have permission to publish cases.');

            return;
        }

        if($this->case->is_public) {
            $this->unpublishCase();
            return;
        }

        DB::beginTransaction();

        try {
            // 🔒 Ambil data fresh + lock
            $case = DB::table('cases')
                ->leftJoin('case_translations', function ($q) {
                    $q->on('case_translations.case_id', '=', 'cases.id')
                        ->where('case_translations.locale', 'id');
                })
                ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
                ->where('cases.id', $this->case_id)
                ->select('cases.*', 'case_translations.title', 'categories.slug as category_name')
                ->lockForUpdate()
                ->first();

            if (! $case) {
                throw new \Exception('Case not found.');
            }

            $firstPublish = is_null($case->published_at);

            // ✅ UPDATE VISIBILITY (PASTI JALAN)
            DB::table('cases')
                ->where('id', $this->case_id)
                ->update([
                    'is_public' => true,
                    'published_at' => $case->published_at ?? now(),
                    'updated_at' => now(),
                ]);

            // 📝 Timeline hanya sekali
            if ($firstPublish) {
                DB::table('case_timelines')->insert([
                    'case_id' => $this->case_id,
                    'actor_id' => auth()->id(),
                    'notes' => 'Case published (made public) by '.auth()->user()->name.'.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 🔄 Ambil ulang case SETELAH update
            $case = DB::table('cases')
                ->leftJoin('case_translations', function ($q) {
                    $q->on('case_translations.case_id', '=', 'cases.id')
                        ->where('case_translations.locale', 'id');
                })
                ->leftJoin('categories', 'categories.id', '=', 'cases.category_id')
                ->where('cases.id', $this->case_id)
                ->select('cases.*', 'case_translations.title', 'categories.slug as category_name')
                ->first();

            // 🌍 SYNC GEOMETRY (IDEMPOTENT & AMAN)
            $this->syncGeometry($case);
            

            DB::commit();

            // 🔁 Reload Livewire state
            $this->loadCase();
            $this->dispatch('refresh-case-detail');

            session()->flash('success', 'Case published successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Publish case error {$this->case_id}: ".$th->getMessage());
            session()->flash('error', $th->getMessage());
        }
    }

    protected function syncGeometry(object $case): void
{
    if (is_null($case->latitude) || is_null($case->longitude)) {
        return;
    }

    $lat = (float) $case->latitude;
    $lon = (float) $case->longitude;

    if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
        throw new \InvalidArgumentException('Invalid latitude / longitude.');
    }

    $wkt = sprintf('POINT(%.15f %.15f)', $lat, $lon);

    // Ambil semua translations — prioritas id, fallback ke yang pertama ada
    $translations = DB::table('case_translations')
        ->where('case_id', $this->case_id)
        ->get();

    $trans = $translations->firstWhere('locale', 'id')
          ?? $translations->first();

    $title     = $trans?->title ?? ('Case ' . $case->case_number);
    $plainDesc = $trans ? strip_tags($trans->description ?? '') : null;

    $exists = DB::table('case_geometries')
        ->where('case_id', $this->case_id)
        ->exists();

    if ($exists) {
        DB::statement(
            'UPDATE case_geometries
             SET geom             = ST_GeomFromText(?, 4326),
                 title            = ?,
                 category         = ?,
                 case_description = ?,
                 is_public        = 1,
                 updated_at       = ?
             WHERE case_id        = ?',
            [
                $wkt,
                $title,
                $case->category_name ?? null,
                $plainDesc,
                now(),
                $this->case_id,
            ]
        );
    } else {
        DB::statement(
            'INSERT INTO case_geometries
             (case_id, geom, title, category, case_description, status, is_public, created_at, updated_at)
             VALUES (?, ST_GeomFromText(?, 4326), ?, ?, ?, ?, ?, ?, ?)',
            [
                $this->case_id,
                $wkt,
                $title,
                $case->category_name ?? null,
                $plainDesc,
                'published',
                1,
                now(),
                now(),
            ]
        );
    }
}

    // unpublish case (for admins only, does NOT delete geometry)
    protected function unpublishCase()
    {
        if (! auth()->user()->can('case.publish')) {
            session()->flash('error', 'You do not have permission to unpublish cases.');
            return;
        }

        DB::beginTransaction();

        try {
            DB::table('cases')
                ->where('id', $this->case_id)
                ->update([
                    'is_public'  => 0,
                    'updated_at' => now(),
                ]);

            // Sembunyikan geometry dari peta (tidak dihapus)
            DB::table('case_geometries')
                ->where('case_id', $this->case_id)
                ->update([
                    'is_public'  => 0,
                    'updated_at' => now(),
                ]);

            DB::table('case_timelines')->insert([
                'case_id'    => $this->case_id,
                'actor_id'   => auth()->id(),
                'notes'      => 'Case unpublished (removed from public view) by ' . auth()->user()->name . '.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            $this->loadCase();
            $this->dispatch('refresh-case-detail');
            session()->flash('success', 'Case unpublished successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("Unpublish case error {$this->case_id}: " . $th->getMessage());
            session()->flash('error', $th->getMessage());
        }
    }


    /**
     * Execute an action on the case (action-based workflow).
     *
     * This method maps user actions to status transitions:
     * - complete_investigation → investigation → prosecution
     * - start_trial → prosecution → trial
     * - execute_verdict → trial → executed
     * - close_case → any allowed → closed
     *
     * @param  string  $actionKey  Action key (e.g., 'complete_investigation', 'close_case')
     * @param  string|null  $notes  Optional notes for timeline
     */
    public function executeAction(string $actionKey, ?string $notes = null)
    {
        // Authorization check
        if (! auth()->user()->can('case.update')) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mengubah status case.');

            return;
        }

        try {
            $actionService = app(CaseActionService::class);

            // Validate action is allowed
            $caseModel = CaseModel::findOrFail($this->case_id);
            if (! $actionService->isActionAllowed($caseModel, $actionKey)) {
                $currentStatus = $caseModel->status?->name ?? 'Unknown';
                session()->flash('error', "Aksi ini tidak diperbolehkan untuk status case saat ini ({$currentStatus}).");

                return;
            }

            // Execute the action (will transition status and log to timeline)
            $success = $actionService->executeAction($this->case_id, $actionKey, $notes);

            if ($success) {
                $this->loadCase();
                $this->dispatch('refresh-case-detail');
                $actionLabel = $this->getActionLabelIndonesian($actionKey);
                session()->flash('success', "Status case berhasil diubah: {$actionLabel}");
            } else {
                session()->flash('info', 'Status case sudah dalam kondisi target.');
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error("Error executing action '{$actionKey}' on case {$this->case_id}: ".$th->getMessage());
            session()->flash('error', 'Gagal mengubah status case. Silakan coba lagi atau hubungi administrator.');
        }
    }

    /**
     * Get action label in Indonesian for UI display.
     *
     * @return string Indonesian label
     */
    private function getActionLabelIndonesian(string $actionKey): string
    {
        return match ($actionKey) {
            'complete_investigation' => 'Naik ke Penuntutan',
            'start_trial' => 'Mulai Persidangan',
            'execute_verdict' => 'Eksekusi Putusan',
            'close_case' => 'Tutup Kasus',
            'reject_case' => 'Tolak Kasus',
            default => ucfirst(str_replace('_', ' ', $actionKey)),
        };
    }

    /**
     * Alias method for executeAction - for backward compatibility.
     */
    public function changeStatusAction(string $actionKey, ?string $notes = null)
    {
        $this->executeAction($actionKey, $notes);
    }

    /**
     * Change case legal status (legacy method, kept for backward compatibility).
     *
     * @deprecated Use executeAction() instead for action-based workflow
     *
     * This method should be used for explicit legal events only:
     * - investigation → prosecution → trial → executed → closed
     *
     * Task completion and publishing do NOT use this method.
     */
    public function changeCaseStatus($newStatusKey, $notes = null)
    {
        try {
            $statusService = app(CaseStatusService::class);
            $success = $statusService->changeStatus($this->case_id, $newStatusKey, $notes);

            if ($success) {
                $this->loadCase();
                $this->dispatch('refresh-case-detail');
                session()->flash('success', 'Case status updated successfully.');
            } else {
                session()->flash('info', 'Case already has this status.');
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('Error changing case status: '.$th->getMessage());
            session()->flash('error', 'Failed to change case status: '.$th->getMessage());
        }
    }

    /**
     * Force change status (audit-only). Visible only to super-admin/audit users in the UI.
     * This bypasses transition validation and should be used only for emergency or testing.
     */
    public function forceChangeCaseStatus($newStatusKey, $notes = null)
    {
        try {
            // Check permission: case.audit or super-admin role
            if (! (auth()->user()->can('case.audit') || auth()->user()->hasRole('super-admin'))) {
                throw new \Illuminate\Auth\Access\AuthorizationException('You do not have permission to force change case status.');
            }

            $statusService = app(CaseStatusService::class);
            $success = $statusService->forceChangeStatus($this->case_id, $newStatusKey, $notes);

            if ($success) {
                $this->loadCase();
                $this->dispatch('refresh-case-detail');
                session()->flash('success', 'Case status force-updated successfully.');
            } else {
                session()->flash('info', 'Case already has this status.');
            }
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Throwable $th) {
            Log::error('Error force-changing case status: '.$th->getMessage());
            session()->flash('error', 'Failed to force-change case status: '.$th->getMessage());
        }
    }

    /**
     * Get allowed actions for the current case status.
     * Used by the view to determine which action buttons to show.
     *
     * @return array Array of action keys with labels (Indonesian)
     */
    public function getAllowedActions(): array
    {
        try {
            $caseModel = CaseModel::findOrFail($this->case_id);
            $actionService = app(CaseActionService::class);
            $allowedActionKeys = $actionService->getAllowedActions($caseModel);

            $actions = [];
            foreach ($allowedActionKeys as $actionKey) {
                $actions[] = [
                    'key' => $actionKey,
                    'label' => $actionService->getActionLabelIndonesian($actionKey),
                    'label_en' => $actionService->getActionLabel($actionKey),
                ];
            }

            return $actions;
        } catch (\Throwable $th) {
            Log::error("Error getting allowed actions for case {$this->case_id}: ".$th->getMessage());

            return [];
        }
    }

    /**
     * Get current status group for UI display.
     *
     * @return string Status group name
     */
    public function getStatusGroup(): string
    {
        try {
            $caseModel = CaseModel::findOrFail($this->case_id);
            $actionService = app(CaseActionService::class);

            return $actionService->getStatusGroupForDisplay($caseModel->status?->key);
        } catch (\Throwable $th) {
            return 'unknown';
        }
    }

    // update status available statuses
    public function changeStatus(string $statusKey)
    {
        $status = DB::table('statuses')->where('key', $statusKey)->first();
        // if (! $status) {
        //     session()->flash('error', 'Status tidak valid.');
        // }

        DB::transaction(function () use ($statusKey) {
            // cek permission
            if (! auth()->user()->can('case.update')) {
                throw new \Illuminate\Auth\Access\AuthorizationException('Anda tidak memiliki izin untuk mengubah status case.');
            }

            $status = DB::table('statuses')->where('key', $statusKey)->first();
            if (! $status) {
                throw new \InvalidArgumentException('Status tidak valid.');
            }

            // update case status   
            
            DB::table('cases')
                ->where('id', $this->case_id)
                ->update([
                    'status_id' => $status->id,
                    'updated_at' => now(),
            ]);

            // timeline internal
            DB::table('case_timelines')->insert([
                'case_id' => $this->case_id,
                'actor_id' => auth()->id(),
                'notes' => "Status case diubah menjadi '{$statusKey}' oleh ".auth()->user()->name.'.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        session()->flash('success', 'Status berhasil diperbarui.');
        $this->loadCase(); // reload data
        $this->dispatch('refresh-case-detail');
    }

    public function render()
    {
        $caseModel = CaseModel::findOrFail($this->case_id);
        $actionService = app(CaseActionService::class);

        // Compute valid next statuses for UI (used when no action mapping exists)
        $statusService = app(\App\Services\CaseStatusService::class);
        $currentStatusKey = $caseModel->status?->key;
        $validNextKeys = $statusService->getValidNextStatuses($currentStatusKey);

        $validNextStatuses = [];
        if (! empty($validNextKeys)) {
            $st = DB::table('statuses')->whereIn('key', $validNextKeys)->get();
            foreach ($st as $s) {
                $validNextStatuses[] = ['key' => $s->key, 'label' => $s->name];
            }
        }

        // Provide all legal statuses for audit users (used by Audit dropdown)
        $allLegalStatuses = [];
        if (auth()->user()->can('case.audit') || auth()->user()->hasRole('super-admin')) {
            $all = DB::table('statuses')->whereIn('key', [
                'open', 'investigation', 'prosecution', 'trial', 'executed', 'closed', 'rejected',
            ])->get();

            foreach ($all as $s) {
                $allLegalStatuses[] = ['key' => $s->key, 'label' => $s->name];
            }
        }

        return view('livewire.cases.case-detail', [
            'tasks' => DB::table('case_tasks')
                ->join('task_translations', function ($q) {
                    $q->on('task_translations.task_id', 'case_tasks.task_id')
                        ->where('task_translations.locale', 'id');
                })
                ->where('case_tasks.case_id', $this->case_id)
                ->select('case_tasks.*', 'task_translations.name')
                ->get(),

            'timelines' => DB::table('case_timelines')
                ->where('case_id', $this->case_id)
                ->orderBy('created_at')
                ->get(),

            'documents' => DB::table('case_documents')
                ->where('case_id', $this->case_id)
                ->whereNull('deleted_at')
                ->get(),

            'discussions' => DB::table('case_discussions')
                ->join('users', 'users.id', '=', 'case_discussions.user_id')
                ->where('case_discussions.case_id', $this->case_id)
                ->select('case_discussions.*', 'users.name')
                ->orderBy('case_discussions.created_at')
                ->get(),

            'actors' => DB::table('case_actors')
                ->where('case_id', $this->case_id)
                ->get(),

            'allowedActions' => $this->getAllowedActions(),
            'statusGroup' => $this->getStatusGroup(),
            'validNextStatuses' => $validNextStatuses,
            'allLegalStatuses' => $allLegalStatuses,
        ])->layout('layouts.internal');
    }

    
}
