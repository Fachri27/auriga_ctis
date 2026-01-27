<?php

namespace App\Livewire\Artikels;

use App\Models\Artikel;
use DB;
use Livewire\Component;

class ArtikelForm extends Component
{
    public $artikel;

    public $titleId;

    public $titleEn;

    public $slug;

    public $type = 'internal';

    public $image;

    public $oldImage;

    public $publishedAt;

    public $status;

    public $link;

    public $userId;

    public $excerptId;

    public $excerptEn;

    public $contentId;

    public $contentEn;

    public function mount($artikelId = null)
    {
        if ($artikelId) {
            $this->artikel = Artikel::with('translation')->findOrFail($artikelId);

            $idTranslation = $this->artikel->translation->firstWhere('locale', 'id');
            $enTranslation = $this->artikel->translation->firstWhere('locale', 'en');

            $this->fill([
                'titleId' => $idTranslation->title ?? '',
                'titleEn' => $enTranslation->title ?? '',
                'slug' => $this->artikel->slug,
                'type' => $this->artikel->type,
                'image' => $this->artikel->image,
                'publishedAt' => $this->artikel->published_at,
                'status' => $this->artikel->status,
                'link' => $this->artikel->link,
                'excerptId' => $idTranslation->excerpt ?? '',
                'excerptEn' => $enTranslation->excerpt ?? '',
                'contentId' => $idTranslation->content ?? '',
                'contentEn' => $enTranslation->content ?? '',
            ]);

            $this->oldImage = $this->artikel->image;
            $this->image = null;
        }
    }

    public function save()
    {
        $this->validate([
            'titleId' => 'required|string|max:255',
            'titleEn' => 'required|string|max:255',
            'type' => 'required|in:parallax,default',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,PNG|max:5048',
            'publishedAt' => 'nullable|date',
            'status' => 'required|in:draft,active,inactive',

            // 🔥 konten HTML jangan difilter
            'contentId' => 'nullable|string',
            'contentEn' => 'nullable|string',
        ]);

        //create
        
    }

    public function render()
    {
        $categories = DB::table('categories')->get();

        return view('livewire.artikels.artikel-form', compact('categories'))->layout('layouts.internal');
    }
}
