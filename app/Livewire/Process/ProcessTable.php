<?php

namespace App\Livewire\Process;

use App\Models\Category;
use App\Models\Process;
use Livewire\Component;
use Livewire\WithPagination;

class ProcessTable extends Component
{
    use WithPagination;

    public $search = '';

    public $categoryFilter = null;

    public function render()
    {
        $processes = Process::with('translations', 'category')
            ->when($this->search, fn ($q) => $q->whereHas('translations', fn ($qt) => $qt->where('name', 'like', "%{$this->search}%")
            ))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->orderBy('order_no', 'asc')
            ->paginate(10);

        $categories = Category::orderBy('slug')->get();

        return view('livewire.process.process-table', compact('processes', 'categories'))->layout('layouts.internal');
    }
}