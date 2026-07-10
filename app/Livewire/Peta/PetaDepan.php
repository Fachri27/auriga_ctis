<?php

namespace App\Livewire\Peta;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class PetaDepan extends Component
{
    public $keyword = '';
    public $sector = '';
    public $status = '';
    public $location = '';

    public function applyFilter()
    {
        $this->dispatch('apply-leaflet-filter', [
            'sector' => $this->sector,
            'status' => $this->status,
            'keyword' => $this->keyword,
            'location' => $this->location,
        ]);
    }

    public function render()
    {
        return view('livewire.peta.peta-depan', [
            'categories' => DB::table('categories')->get(),
            'statuses' => DB::table('statuses')->get(),
        ]);
    }
}
