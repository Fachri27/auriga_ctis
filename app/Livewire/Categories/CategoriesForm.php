<?php

namespace App\Livewire\Categories;

use App\Models\Category;
use App\Models\CategoryTranslation;
use Livewire\Component;
use Str;

class CategoriesForm extends Component
{
    public $category;

    public $categoryId;

    public $icon;

    public $slug;

    public $name_id;

    public $name_en;

    public $desc_id;

    public $desc_en;

    public $is_active = true;

    public function mount($categoryId = null)
    {
        if ($categoryId) {
            if ($categoryId) {
                $this->category = Category::findOrFail($categoryId);
                $this->slug = $this->category->slug;
                $this->icon = $this->category->icon;
                $this->is_active = $this->category->is_active;

                $idTrans = $this->category->translations->where('locale', 'id')->first();
                $enTrans = $this->category->translations->where('locale', 'en')->first();

                if ($idTrans) {
                    $this->name_id = $idTrans->name;
                    $this->desc_id = $idTrans->description;
                }

                if ($enTrans) {
                    $this->name_en = $enTrans->name;
                    $this->desc_en = $enTrans->description;
                }
            }

        }
    }

    public function save()
    {
        $category = $this->category ?? new Category;

        $this->validate([
            'name_id' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        $data = [
            'slug' => Str::slug($this->name_id),
            'icon' => $this->icon,
            'is_active' => $this->is_active,
        ];

        $category->fill($data)->save();
        $category->refresh();

        foreach (['id', 'en'] as $locale) {
            CategoryTranslation::updateOrCreate(
                ['category_id' => $category->id, 'locale' => $locale],
                [
                    'name' => $locale === 'id' ? $this->name_id : $this->name_en,
                    'description' => $locale === 'id' ? $this->desc_id : $this->desc_en,
                ]
            );
        }

        session()->flash('success', 'Category saved successfully!');

        return redirect()->route('categoris.index');
    }

    public function render()
    {
        return view('livewire.categories.categories-form')->layout('layouts.internal');
    }
}
