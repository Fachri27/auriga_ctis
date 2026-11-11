<?php

namespace App\Livewire\Cases;

use App\Models\CaseModel;
use App\Services\CasePublisher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Livewire\{Component, WithPagination};

class CaseList extends Component
{
    use WithPagination;

    public $search = '';

    public $filter = '';

    public $filterVerif = '';

    // Publikasi massal: ID kasus yang dicentang (lintas halaman), atau semua hasil filter.
    public array $selected = [];

    public bool $selectAllMatching = false;

    // Kirim email ke subscriber setelah publikasi massal (satu email ringkasan per subscriber).
    public bool $notify = true;

    protected $listeners = [
        'case-created' => '$refresh',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updated($property): void
    {
        // Hasil filter berubah, jadi "semua hasil filter" tidak lagi berarti kumpulan yang sama.
        if (in_array($property, ['search', 'filter', 'filterVerif'], true)) {
            $this->selectAllMatching = false;
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filter', 'filterVerif', 'selectAllMatching']);
        $this->resetPage();
    }

    /**
     * Centang/lepas semua kasus di halaman yang sedang tampil.
     *
     * @param  list<int|string>  $ids
     */
    public function togglePage(array $ids, bool $checked): void
    {
        $ids = array_map('strval', $ids);
        $this->selected = $checked
            ? array_values(array_unique([...$this->selected, ...$ids]))
            : array_values(array_diff($this->selected, $ids));

        if (! $checked) {
            $this->selectAllMatching = false;
        }
    }

    public function selectAllMatching(): void
    {
        $this->selectAllMatching = true;
    }

    public function clearSelection(): void
    {
        $this->reset(['selected', 'selectAllMatching']);
    }

    public function bulkPublish(): void
    {
        $this->runBulk(publish: true);
    }

    public function bulkUnpublish(): void
    {
        $this->runBulk(publish: false);
    }

    public function deleteCase($id)
    {
        try {
            CaseModel::query()->whereKey($id)->delete();
            session()->flash('success', 'Cases deleted');
        } catch (\Throwable $th) {
            // throw $th;
            session()->flash('error', 'Failed to delete case');
        }
    }

    public function render()
    {
        $cases = $this->filteredQuery()
            ->with(['translations', 'verifiedBy', 'status'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.cases.case-list', [
            'cases' => $cases,
            'selectedCount' => $this->selectAllMatching ? $cases->total() : count($this->selected),
        ])->layout('layouts.internal');
    }

    private function runBulk(bool $publish): void
    {
        if (! auth()->user()->can('case.publish')) {
            session()->flash('error', 'Anda tidak memiliki izin untuk mempublikasikan kasus.');

            return;
        }

        $ids = $this->selectAllMatching
            ? $this->filteredQuery()->pluck('id')->all()
            : array_map('intval', $this->selected);

        if ($ids === []) {
            return;
        }

        $publisher = app(CasePublisher::class);
        $changed = [];
        $failed = [];

        foreach ($ids as $id) {
            try {
                if ($publish ? $publisher->publish($id) : $publisher->unpublish($id)) {
                    $changed[] = $id;
                }
            } catch (\Throwable $e) {
                Log::error(($publish ? 'Bulk publish' : 'Bulk unpublish')." case {$id} failed: ".$e->getMessage());
                $failed[] = CaseModel::whereKey($id)->value('case_number') ?? "#{$id}";
            }
        }

        $skipped = count($ids) - count($changed) - count($failed);

        if ($publish) {
            $message = count($changed).' kasus dipublikasikan.';
            if ($skipped) {
                $message .= " {$skipped} kasus sudah publik sebelumnya (dilewati).";
            }

            $withoutPoint = $changed ? CaseModel::whereIn('id', $changed)->where(fn ($q) => $q->whereNull('latitude')->orWhereNull('longitude'))->count() : 0;
            if ($withoutPoint) {
                $message .= " {$withoutPoint} kasus belum punya titik peta sehingga tidak tampil di peta publik.";
            }

            if ($this->notify && $changed) {
                $recipients = $publisher->notifySubscribers($changed);
                $message .= $recipients ? " Email ringkasan dikirim ke {$recipients} subscriber." : '';
            }
        } else {
            $message = count($changed).' kasus dibatalkan publikasinya.';
            if ($skipped) {
                $message .= " {$skipped} kasus memang belum publik (dilewati).";
            }
        }

        session()->flash('success', $message);
        if ($failed) {
            session()->flash('error', 'Gagal diproses: '.implode(', ', $failed).'.');
        }

        $this->clearSelection();
    }

    private function filteredQuery(): Builder
    {
        $query = CaseModel::query();

        if ($this->filter === 'investigation') {

            $query->whereHas('status', fn ($q) => $q->where('key', 'investigation'));

        } elseif ($this->filter === 'published') {

            $query->where('is_public', true);

        } elseif ($this->filter === 'draft') {

            $query->where('is_public', false);

        } elseif ($this->filter === 'closed') {

            $query->whereHas('status', fn ($q) => $q->where('key', 'closed'));

        }

        if ($this->filterVerif === 'me') {
            $query->where('verified_by', auth()->id());
        }

        if ($this->filterVerif === 'pending') {
            $query->whereHas('status', fn ($q) => $q->where('key', 'open'));
        }

        if ($this->filterVerif === 'rejected') {
            $query->whereHas('status', fn ($q) => $q->where('key', 'rejected'));
        }

        // Apply search — cocokkan nomor kasus, judul (lewat terjemahan),
        // pelapor, maupun terlapor. Placeholder form menjanjikan "nomor kasus atau judul".
        $query->when($this->search, function ($q) {
            $s = '%' . $this->search . '%';
            $q->where(function ($q) use ($s) {
                $q->where('case_number', 'like', $s)
                    ->orWhere('pelapor', 'like', $s)
                    ->orWhere('terlapor', 'like', $s)
                    ->orWhereHas('translations', fn ($q) => $q->where('title', 'like', $s));
            });
        });

        return $query;
    }
}
