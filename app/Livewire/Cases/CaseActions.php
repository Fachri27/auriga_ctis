<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\{Auth, Storage};
use App\Models\{CaseAction, CaseActionFile, CaseModel};
use Livewire\{Component, WithFileUploads};

class CaseActions extends Component
{
    use WithFileUploads;

    public $case;

    public $showModal = false;

    public $title;
    public $description;
    
    public $files = [];

    public $mode = 'create';

    protected $listeners = [
        'open-create-action-modal' => 'openModal',
    ];

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'files.*' => 'file|max:5120'
    ];

    public function mount($caseId)
    {
        $this->case = CaseModel::findOrFail($caseId);
    }

    public function openModal($caseId)
    {
        $this->mode = 'create';
        $this->resetValidation();
        // $this->resetForm();
        $this->caseId = $caseId;

        // dd($this->processes);

        $this->showModal = true;
    }

    public function createAction()
    {
        $this->validate();

        $action = CaseAction::create([
            'case_id' => $this->case->id,
            'title' => $this->title,
            'description' => $this->description,
            'created_by' => Auth::id(),
        ]);

        foreach ($this->files ?? [] as $file) {

            $path = $file->store('case_actions', 'public');

            CaseActionFile::create([
                'case_action_id' => $action->id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
            ]);
        }

        $this->reset(['title', 'description', 'files']);
        $this->status = 'open';
        $this->showModal = false;

        session()->flash('success', 'Tugas berhasil dibuat');
    }

    public function updateStatus($actionId, $status)
    {
        $action = CaseAction::findOrFail($actionId);
        $action->update(['status' => $status]);
    }

    public function deleteAction($actionId)
    {
        $action = CaseAction::with('files')->findOrFail($actionId);

        foreach ($action->files as $file) {
            if ($file->file_path) {
                Storage::disk('public')->delete($file->file_path);
            }

            $file->delete();
        }

        $action->delete();

        session()->flash('success', 'Tugas berhasil dihapus');
    }

    public function render()
    {
        $actions = $this->case
            ->actions()
            ->with('files')
            ->latest()
            ->get();

        return view('livewire.cases.case-actions', [
            'actions' => $actions
        ]);
    }
}
