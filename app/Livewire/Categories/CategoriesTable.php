<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriesTable extends Component
{
    use WithPagination;
    public $search ='';
    public $showForm = false;
    public $editingId = null;

    protected $listeners = [
        'categorySaved' => '$refresh',
        'closeForm' => 'closeForm',
    ];

    public function openCreate()
    {
        $this->editingId = null;
        $this->showForm = true;
    }

    public function openEdit($id)
    {
        $this->editingId = $id;
        $this->showForm = true;
    }

    public function closeForm()
    {
        $this->showForm = false;
    }


    public function render()
    {
        $categories = Category::with('translations')
            ->whereHas('translations', function($query) {
                $query->where('slug', 'like', '%' . $this->search . '%');
            })->paginate(10);
        return view('livewire.categories.categories-table', compact('categories'))->layout('layouts.internal');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        session()->flash('success', 'Category saved successfully!');

    }
}
