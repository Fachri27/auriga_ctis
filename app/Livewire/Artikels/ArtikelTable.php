<?php

namespace App\Livewire\Artikels;

use App\Models\Artikel;
use Livewire\Component;

class ArtikelTable extends Component
{
    public $search = '';

    public $status;

    // public $author;

    public $type;

    public $dataRange;

    protected $updatesQueryString = ['search', 'status', 'author', 'start_date', 'end_date', 'dataRange'];

    public function render()
    {
        // $trans = Artikel::with('translation')->get();
        // dd($trans);
        $pages = Artikel::with('translation')
            ->whereHas('translation', function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%');

                if ($this->status && $this->status !== 'all') {
                    $query->where('status', $this->status);
                }

                if ($this->type && $this->type !== 'all') {
                    $query->where('type', $this->type);
                }

            })->paginate(5);

        return view('livewire.artikels.artikel-table', compact('pages'))->layout('layouts.internal');
    }

    public function delete(Artikel $artikel)
    {
        $artikel->delete();
        session()->flash('success', 'Artikel berhasil di hapus');
    }
}
