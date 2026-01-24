<?php

namespace App\Livewire\Peta;

use Livewire\Component;

class MapFilter extends Component
{
    public $search = '';
    public $sector;
    public $status = '';
    public $location = '';
    public $lat = null;
    public $lng = null;
    public $radius = 5; // km

    public function applyFilter()
    {
        $this->dispatch('apply-leaflet-filter', [
            'sector' => $this->sector,
            'status' => $this->status,
            'search' => $this->search,
            'lat' => $this->lat,
            'lng' => $this->lng,
            // 'radius' => $this->radius,
        ]);
    }

    public function resetFilter()
    {
        $this->sector = '';
        $this->status = '';
        $this->search = '';
        $this->lat = null;
        $this->lng = null;
        // $this->radius = 5;

        $this->dispatch('reset-leaflet-filter', [
            'sector' => '',
            'status' => '',
            'search' => '',
            'lat' => null,
            'lng' => null,
            'radius' => 5,
        ]);
    }

    protected $listeners = [
        'setLocation' => 'setLocation'
    ];

    public function setLocation($lat, $lng)
    {
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function render()
    {
        return view('livewire.peta.map-filter');
    }
}
