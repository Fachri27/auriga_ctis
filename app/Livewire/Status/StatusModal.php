<?php

namespace App\Livewire\Status;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StatusModal extends Component
{
    public $statusId = null;
    public $key, $name;
    public $show = false;

    protected $listeners = [
        'open-status-modal' => 'openModal',
        'edit-status-modal' => 'editModal',
    ];

    protected $rules = [
        'key' => 'required',
        'name' => 'required',
    ];

    // public function mount($id)
    // {
    //     $this->statusId = $id;
    // }

    public function openModal()
    {
        $this->resetForm();
        $this->show = true;
    }

    public function editModal($statusId)
    {
        $this->resetForm();
        $this->statusId = $statusId;

        $status = DB::table('statuses')->where('id', $statusId)->first();
        if(!$status) {
            return;
        }

        $this->key = $status->key;
        $this->name = $status->name;

        $this->show = true;
    }

    public function save()
    {
        $this->validate();
        DB::beginTransaction();
        try {
            if($this->statusId) {
                DB::table('statuses')
                    ->where('id', $this->statusId)
                    ->update([
                        'key' => $this->key,
                        'name' => $this->name,
                        'updated_at' => now(),
                    ]);
                
                $statusId = $this->statusId;

                DB::commit();
                $this->dispatch('close-status-modal');

                return;
            }

            $statusId = DB::table('statuses')->insertGetId([
                'key' => $this->key,
                'name' => $this->name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);


            DB::commit();
            $this->dispatch('close-status-modal');
        } catch (\Exception $e) {
            //throw $th;
            DB::rollBack();
            throw $e;
        }
    }

    private function resetForm()
    {
        $this->resetValidation();

        $this->reset([
            'statusId',
            'key',
            'name',
        ]);
    }

    public function render()
    {
        return view('livewire.status.status-modal');
    }
}
