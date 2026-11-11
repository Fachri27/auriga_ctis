<?php

namespace App\Livewire\Artikels;

use App\Models\Artikel;
use App\Services\News\{NewsAggregator, NewsEnricher};
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
        $pages = Artikel::with(['translation', 'user'])
            ->whereHas('translation', function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%');
            })
            // status & type adalah kolom tabel artikels, bukan artikel_translations
            ->when($this->status && $this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->type && $this->type !== 'all', fn ($q) => $q->where('type', $this->type))
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.artikels.artikel-table', compact('pages'))->layout('layouts.internal');
    }

    /**
     * Tarik berita penegakan hukum lingkungan & SDA sekarang (sama dengan jadwal news:pull).
     */
    public function pullNews(NewsAggregator $aggregator, NewsEnricher $enricher): void
    {
        $result = $aggregator->pull();
        // Dibatasi waktu agar permintaan tidak melewati batas eksekusi; sisanya dilengkapi penarikan terjadwal.
        $enriched = $enricher->enrich(20, seconds: 12);
        $images = $result['upgraded'] + $enriched['images'];

        $message = "{$result['created']} berita baru ditambahkan (dari {$result['relevant']} berita relevan)"
            .($images ? ", {$images} berita mendapat gambar." : '.');
        if ($result['failed_sources']) {
            $message .= ' Gagal membaca: '.implode(', ', $result['failed_sources']).'.';
        }

        session()->flash('success', $message);
    }

    /**
     * Sembunyikan/tampilkan artikel di situs (mis. berita otomatis yang kurang relevan).
     */
    public function toggleActive(Artikel $artikel): void
    {
        $artikel->update(['status' => $artikel->status === 'active' ? 'inactive' : 'active']);
    }

    public function delete(Artikel $artikel)
    {
        $artikel->delete();
        session()->flash('success', 'Artikel berhasil di hapus');
    }
}
