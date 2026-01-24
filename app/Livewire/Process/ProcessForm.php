<?php

namespace App\Livewire\Process;

use App\Models\Category;
use App\Models\Process;
use App\Models\ProcessTranslation;
use Livewire\Component;

class ProcessForm extends Component
{
    public $processId;
    public $process;
    public $category_id;
    public $order_no = 1;
    public $is_active;
    public $name_id, $name_en;
    // public $categories;

    public function mount($processId = null)
    {
        if($processId) {
            if($processId) {
                $this->process = Process::findOrFail($processId);
                $this->category_id = $this->process->category_id;
                $this->order_no = $this->process->order_no;
                $this->is_active = $this->process->is_active;

                $idTrans = $this->process->translations->where('locale', 'id')->first();
                $enTrans = $this->process->translations->where('locale', 'en')->first();

                if($idTrans) {
                    $this->name_id = $idTrans->name;
                }

                if($enTrans) {
                    $this->name_en = $enTrans->name;
                }
            }
        }
    }

    public function save() 
    {
        $process = $this->process ?? new Process;

        $this->validate([
            'category_id' => 'required',
            'order_no' => 'required|integer|min:1',
            'name_id' => 'required|string',
            'name_en' => 'required|string',
        ]);

        $data = [
            'category_id' => $this->category_id,
            'order_no' => $this->order_no,
            'is_active' => $this->is_active,
        ];

        $process->fill($data)->save();
        $process->refresh();

        foreach(['id', 'en'] as $locale)
        {
            ProcessTranslation::updateOrCreate(
                ['process_id' => $process->id, 'locale' => $locale],
                [
                    'name' => $locale === 'id' ? $this->name_id : $this->name_en,
                ]
            );
        }

        session()->flash('success', 'Process saved successfully!');

        return redirect()->route('process.index');
    }

    public function render()
    {
        return view('livewire.process.process-form', [
            'categories' => Category::orderBy('slug')->get(),
        ])->layout('layouts.internal');
    }
}
