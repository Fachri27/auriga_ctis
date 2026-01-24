<?php

namespace App\Livewire\Cases;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;

class CaseDiscussion extends Component
{
    use WithFileUploads;

    public $caseId;

    public $message;
    public $attachments = [];

    public $discussions = [];

    protected $rules = [
        'message' => 'required|string',
        'attachments.*' => 'nullable|file|max:10240',
    ];

    public function mount($caseId)
    {
        $this->caseId = $caseId;
        $this->loadDiscussions();
    }

    public function loadDiscussions()
    {
        $this->discussions = DB::table('case_discussions')
            ->join('users', 'users.id', '=', 'case_discussions.user_id')
            ->where('case_discussions.case_id', $this->caseId)
            ->select('case_discussions.*', 'users.name')
            ->orderBy('case_discussions.created_at')
            ->get();
    }

    public function send()
    {
        $this->validate();

        $files = [];

        if ($this->attachments) {
            foreach ($this->attachments as $file) {
                // Store to public disk
                $path = $file->store('case_discussions', 'public');
                $files[] = $path;
            }
        }

        DB::table('case_discussions')->insert([
            'case_id' => $this->caseId,
            'user_id' => auth()->id(),
            'message' => $this->message,
            'attachments' => json_encode($files),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert timeline
        DB::table('case_timelines')->insert([
            'case_id' => $this->caseId,
            'actor_id' => auth()->id(),
            'notes' => "New discussion message added.",
            'created_at' => now(),
        ]);

        $this->reset(['message', 'attachments']);

        $this->loadDiscussions();

        // Notify parent (optional)
        $this->dispatch('refresh-case-detail');
    }

    public function render()
    {
        return view('livewire.cases.case-discussion');
    }
}
