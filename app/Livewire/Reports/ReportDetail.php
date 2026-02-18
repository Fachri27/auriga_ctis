<?php

namespace App\Livewire\Reports;

use App\Models\Report;
use App\Services\CaseTaskGenerator;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Str;

class ReportDetail extends Component
{
    public $report;

    public $categoryId;

    protected $listeners = [
        'refresh-report-detail' => '$refresh',
    ];

    public function mount($id)
    {
        // Load report with translations and status to avoid null-related errors in views
        $this->report = Report::with(['translations', 'status'])->findOrFail($id);
    }

    public function verify()
    {
        // Authorization using policy (allows admins)
        if (! auth()->user()->can('verify', $this->report)) {
            session()->flash('error', 'Anda tidak memiliki izin untuk memverifikasi laporan.');

            return;
        }

        // ambil status verified by key (safer than name)
        $verified = DB::table('statuses')
            ->where('key', 'verified')
            ->value('id');

        if (! $verified) {
            session()->flash('error', 'Status "verified" tidak ditemukan di database.');

            return;
        }

        DB::table('reports')
            ->where('id', $this->report->id)
            ->update([
                'status_id' => $verified,
                'updated_at' => now(),
            ]);

        // Reload the report model to reflect status change
        // $this->report = Report::with(['translations', 'status'])->find($this->report->id);

        //convert to case
        $this->convertToCase();

        // session()->flash('success', 'Report verified.');

        // // Dispatch browser event for immediate feedback
        // $this->dispatch('notify', ['type' => 'success', 'message' => 'Report verified.']);

        // Refresh component state
        $this->dispatch('refresh-report-detail');
    }

    public function rejected()
    {
        // Authorization using policy (allows admins)
        if (! auth()->user()->can('reject', $this->report)) {
            session()->flash('error', 'Anda tidak memiliki izin untuk menolak laporan.');

            return;
        }

        // ambil status rejected by key
        $rejected = DB::table('statuses')
            ->where('key', 'rejected')
            ->value('id');

        if (! $rejected) {
            session()->flash('error', 'Status "rejected" tidak ditemukan di database.');

            return;
        }

        DB::table('reports')
            ->where('id', $this->report->id)
            ->update([
                'status_id' => $rejected,
                'updated_at' => now(),
            ]);

        // Reload report
        $this->report = Report::with(['translations', 'status'])->find($this->report->id);

        session()->flash('success', 'Report rejected.');

        // Dispatch browser event for immediate feedback
        $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Report rejected.']);

        // Refresh component state
        $this->dispatch('refresh-report-detail');
    }


    public function publish()
    {
        // Authorization using policy (allows admins)
        if (! auth()->user()->can('publish', $this->report)) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mempublikasikan laporan.');

            return;
        }

        try {
            DB::table('reports')
                ->where('id', $this->report->id)
                ->update([
                    'is_published' => true,
                    'published_at' => now(),
                    'published_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            // Reload report
            $this->report = Report::with(['translations', 'status'])->find($this->report->id);

            session()->flash('success', 'Report published.');
            $this->dispatchBrowserEvent('notify', ['type' => 'success', 'message' => 'Report published.']);

            // Refresh component state
            $this->dispatch('refresh-report-detail');
        } catch (\Throwable $th) {
            \Log::error('Publish report error: '.$th->getMessage());
            session()->flash('error', 'Gagal mempublikasikan laporan: '.$th->getMessage());
        }
    }

    public function convertToCase()
    {
        DB::beginTransaction();

        try {
            $report = DB::table('reports')->where('id', $this->report->id)->first();


            $translation = DB::table('report_translations')
                ->where('report_id', $report->id)
                ->where('locale', 'id')
                ->first();

            $categoryId = $report->category_ids;

            $caseNumber = 'CASE-'.strtoupper(Str::random(5));

            $title = Str::of($translation->description ?? "Laporan Publik #{$report->report_code}")
                ->words(8, '...');

            $summary = Str::of($translation->description ?? '')
                ->words(20, '...');

            // Get initial investigation status ID
            $investigationStatusId = DB::table('statuses')->where('key', 'investigation')->value('id');

            if (! $investigationStatusId) {
                throw new \Exception('Investigation status not found in database.');
            }

            // 1️⃣ CREATE CASE with initial investigation status
            $caseId = DB::table('cases')->insertGetId([
                'case_number' => $caseNumber,
                'report_id' => $report->id,
                'category_ids' => $categoryId,
                'status_id' => $investigationStatusId,
                'latitude' => $report->lat,
                'longitude' => $report->lng,
                'verified_by' => auth()->id(),
                'event_date' => now(),
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2️⃣ CASE TRANSLATION
            DB::table('case_translations')->insert([
                'case_id' => $caseId,
                'locale' => 'id',
                'title' => $title,
                'summary' => $summary,
                'description' => $translation->description ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 3️⃣ TIMELINE ENTRY (Action-Based Logging)
            // Log the "Convert to Case" action in the case timeline
            DB::table('case_timelines')->insert([
                'case_id' => $caseId,
                'actor_id' => auth()->id(),
                'notes' => 'Action: Convert to Case - Case created from report #'.$report->report_code.' by '.auth()->user()->name.'.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4️⃣ UPDATE REPORT STATUS
            DB::table('reports')->where('id', $report->id)->update([
                'status_id' => DB::table('statuses')->where('key', 'converted')->value('id'),
                'updated_at' => now(),
            ]);

            DB::commit();

            return redirect()->route('case.detail', $caseId);

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @deprecated Auto task generator (complex workflow)
     *
     * Legacy behavior (kept for reference):
     * - For each process in a category, create tasks and case_task entries
     * - Auto-generate requirements for each task
     *
     * This method is preserved as a comment-only reference to avoid deleting
     * historical workflow behavior. The simplified flow does NOT call this by default.
     */
    private function createCaseTask($caseId, $categoryId)
    {
        // Legacy implementation intentionally commented out for clarity.
        // If you need to restore full workflow behavior, uncomment and audit.

        /*
        if (! $categoryId) {
            return;
        }

        // ambil process berdasarkan category
        $processes = DB::table('processes')
            ->where('category_id', $categoryId)
            ->orderBy('order_no')
            ->get();

        foreach ($processes as $process) {

            // ambil task berdasarkan process
            $tasks = DB::table('tasks')
                ->where('process_id', $process->id)
                ->get();

            foreach ($tasks as $task) {

                // 1️⃣ insert case_task
                $caseTaskId = DB::table('case_tasks')->insertGetId([
                    'case_id' => $caseId,
                    'task_id' => $task->id,
                    'status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // 2️⃣ auto-generate requirements
                $requirements = DB::table('task_requirements')
                    ->where('task_id', $task->id)
                    ->get();

                foreach ($requirements as $req) {
                    DB::table('case_task_requirements')->insert([
                        'case_task_id' => $caseTaskId,
                        'requirement_id' => $req->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        */

        // NEW: For now we do nothing here. Use SimpleCaseService instead when needed.

    }

    public function render()
    {
        return view('livewire.reports.report-detail')->layout('layouts.internal');
    }
}
