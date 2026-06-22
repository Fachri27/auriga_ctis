<?php

namespace App\Livewire\About;

use App\Models\{AboutPage, AboutPageTranslation};
use Livewire\Component;

class AboutForm extends Component
{
    public $aboutPage;

    public $title_id;
    public $title_en;
    public $content_id;
    public $content_en;
    public $vision_id;
    public $vision_en;
    public $mission_id;
    public $mission_en;

    public function mount()
    {
        $this->aboutPage = AboutPage::with('translations')->first();

        if ($this->aboutPage) {
            $idTranslation = $this->aboutPage->translations->firstWhere('locale', 'id');
            $enTranslation = $this->aboutPage->translations->firstWhere('locale', 'en');

            $this->fill([
                'title_id' => $idTranslation->title ?? '',
                'title_en' => $enTranslation->title ?? '',
                'content_id' => $idTranslation->content ?? '',
                'content_en' => $enTranslation->content ?? '',
                'vision_id' => $idTranslation->vision ?? '',
                'vision_en' => $enTranslation->vision ?? '',
                'mission_id' => $idTranslation->mission ?? '',
                'mission_en' => $enTranslation->mission ?? '',
            ]);
        }
    }

    public function save()
    {
        $this->validate([
            'title_id' => 'required|string|max:255',
            'title_en' => 'required|string|max:255',
        ]);

        $about = $this->aboutPage ?? new AboutPage;
        $about->save();
        $about->refresh();

        foreach (['id', 'en'] as $locale) {
            AboutPageTranslation::updateOrCreate(
                ['about_page_id' => $about->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id' ? $this->title_id : $this->title_en,
                    'content' => $locale === 'id' ? $this->content_id : $this->content_en ?? '',
                    'vision' => $locale === 'id' ? $this->vision_id : $this->vision_en ?? '',
                    'mission' => $locale === 'id' ? $this->mission_id : $this->mission_en ?? '',
                ]
            );
        }

        session()->flash('success', 'About page berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.about.about-form')->layout('layouts.internal');
    }
}
