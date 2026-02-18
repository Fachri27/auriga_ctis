<?php

namespace App\Livewire\Cases;

use Illuminate\Support\Facades\DB;
use Livewire\{Component, WithFileUploads};

class UploadDocumentCase extends Component
{
    use WithFileUploads;

    public $open = false;

    public $processes = [];

    public $caseId;
    // public $docId;

    public $document_id;

    public $process_id;

    public $title;

    public $file;

    public $existing_file;

    public $existing_mime;

    public $mode = 'create';

    protected $listeners = [
        'open-upload-document-modal' => 'openModal',
        'open-edit-document-modal' => 'openEdit',
    ];

    public function mount($caseId = null)
    {
        $this->caseId = $caseId;
    }

    public function openModal($caseId)
    {
        $this->mode = 'create';
        $this->resetValidation();
        $this->resetForm();
        $this->caseId = $caseId;

        // get category id
        $categoryId = DB::table('cases')
            ->where('id', $this->caseId)
            ->value('category_id');

        // get processes based on category id
        $this->processes = DB::table('processes')
            ->where('category_id', $categoryId)
            ->get()
            ->toArray();

        // dd($this->processes);

        $this->open = true;
    }

    public function openEdit($docId)
    {
        $this->mode = 'edit';
        $this->resetValidation();
        $this->resetForm();

        $doc = DB::table('case_documents')->where('id', $docId)->first();

        if (! $doc) {
            return;
        }

        $this->document_id = $doc->id;
        $this->process_id = $doc->process_id ? (int) $doc->process_id : null;  // FIX
        $this->title = $doc->title;
        $this->existing_file = $doc->file_path;
        $this->existing_mime = $doc->mime;

        // ambil category melalui case_id
        $categoryId = DB::table('cases')
            ->where('id', $doc->case_id)
            ->value('category_id');

        $this->processes = DB::table('processes')
            ->where('category_id', $categoryId)
            ->get()
            ->toArray();

        $this->open = true;
    }

    public function resetForm()
    {
        $this->reset([
            'document_id',
            'process_id',
            'title',
            'file',
            'existing_file',
            'existing_mime',
        ]);

    }

    public function save()
    {
        if ($this->mode === 'create') {
            return $this->storeNew();
        }

        return $this->updateExisting();
    }

    public function storeNew()
    {
        $this->validate([
            // 'process_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        // store file
        $filePath = $this->file->store('storage/'.'case_documents', 'public');
        $mimeType = $this->file->getClientMimeType();

        DB::table('case_documents')->insert([
            'case_id' => $this->caseId,
            'uploaded_by' => auth()->id(),
            'process_id' => $this->process_id ?? null,
            'title' => $this->title,
            'file_path' => $filePath,
            'mime' => $mimeType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->open = false;

        $this->dispatch('refresh-case-detail');
    }

    public function updateExisting()
    {
        $this->validate([
            'process_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|max:10240', // max 10MB
        ]);

        $data = [
            'process_id' => $this->process_id,
            'title' => $this->title,
            'updated_at' => now(),
        ];

        if ($this->file) {
            $this->validate([
                'file' => 'file|max:10240',
            ]);
            // store new file
            $filePath = $this->file->store('storage/'.'case_documents', 'public');

            $data['file_path'] = $filePath;
            $data['mime'] = $this->file->getClientMimeType();

        }

        DB::table('case_documents')
            ->where('id', $this->document_id)
            ->update($data);

        $this->open = false;
        $this->dispatch('refresh-case-detail');
    }

    public function delete()
    {
        if (! $this->document_id) {
            return;
        }

        DB::table('case_documents')->where('id', $this->document_id)->delete();

        $this->open = false;
        $this->dispatch('refresh-case-detail');
        session()->flash('success', 'Dokumen berhasil dihapus.');

    }

    public function render()
    {

        return view('livewire.cases.upload-document-case');
    }
}
