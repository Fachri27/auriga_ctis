<?php

namespace App\Livewire\Artikels;

use Illuminate\Support\Facades\Storage;
use App\Models\{Artikel, ArtikelTranslation};
use Illuminate\Support\Facades\Auth;
use DB;
use Str;
use Livewire\{Component, WithFileUploads};
use App\Livewire\Concerns\AutoTranslates;


class ArtikelForm extends Component
{
    use WithFileUploads;
    use AutoTranslates;
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

    public $excerpt_id;

    public $excerpt_en;

    public $content_id;

    public $content_en;

    public $categoryId;

    // PDF Analisis Auriga per bahasa (unggahan baru), berkas tersimpan, dan tanda hapus.
    public $pdf_id;

    public $pdf_en;

    public $existingPdf = ['id' => null, 'en' => null];

    public $removePdf = ['id' => false, 'en' => false];

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
                'categoryId' => $this->artikel->category_id,
                'excerpt_id' => $idTranslation->excerpt ?? '',
                'excerpt_en' => $enTranslation->excerpt ?? '',
                'content_id' => $idTranslation->content ?? '',
                'content_en' => $enTranslation->content ?? '',
            ]);

            $this->oldImage = $this->artikel->image;
            $this->image = null;
            $this->existingPdf = ['id' => $this->artikel->pdf_id, 'en' => $this->artikel->pdf_en];
        }
    }

    public function save()
    {
        $this->validate([
            'titleId' => 'required|string|max:255',
            'titleEn' => 'required|string|max:255',
            'type' => 'required|in:internal,eksternal',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,PNG|max:10048',
            'publishedAt' => 'nullable|date',
            'status' => 'required|in:draft,active,inactive',
            'link' => 'nullable|url|max:2048',
            'pdf_id' => 'nullable|file|mimes:pdf|max:51200',
            'pdf_en' => 'nullable|file|mimes:pdf|max:51200',
        ], [
            'link.url' => 'Tautan harus berupa alamat web lengkap, diawali https://.',
            'pdf_id.mimes' => 'File PDF (Indonesia) harus berupa PDF.',
            'pdf_en.mimes' => 'File PDF (English) harus berupa PDF.',
            'pdf_id.max' => 'File PDF maksimal 50 MB.',
            'pdf_en.max' => 'File PDF maksimal 50 MB.',
        ]);

        $artikel = $this->artikel ?? new Artikel;

        //create
        $data = [
            'slug' => Str::slug($this->titleId),
            'type' => $this->type,
            'published_at' => $this->publishedAt,
            'status' => $this->status,
            'link' => $this->link,
            'user_id' => Auth::id(),
            'category_id' => $this->categoryId,
        ];

        // image
        if ($this->image instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {

            // Hapus gambar lama
            if ($this->oldImage && Storage::disk('public')->exists($this->oldImage)) {
                Storage::disk('public')->delete($this->oldImage);
            }

            // Simpan gambar baru
            $filename = Str::slug($this->titleId).'-'.time().'.'.$this->image->getClientOriginalExtension();
            $path = $this->image->storeAs('pages', $filename, 'public');

            // Resize untuk meta
            $metaDir = storage_path('app/public/pages/meta');
            if (! file_exists($metaDir)) {
                mkdir($metaDir, 0755, true);
            }

            $data['image'] = $path;

        } else {
            // Tidak upload baru → tetap pakai gambar lama
            $data['image'] = $this->oldImage;
        }

        foreach (['id', 'en'] as $locale) {
            $data["pdf_{$locale}"] = $this->storePdf($locale);
        }

        $artikel->fill($data)->save();
        $artikel->refresh();

        // translations
        foreach(['id', 'en'] as $locale) {
            ArtikelTranslation::updateOrCreate(
                ['artikel_id' => $artikel->id, 'locale' => $locale],
                [
                    'title' => $locale === 'id' ? $this->titleId : $this->titleEn ?? '',
                    'excerpt' => $locale === 'id' ? $this->excerpt_id : $this->excerpt_en ?? '',
                    'content' => $locale === 'id' ? $this->content_id : $this->content_en ?? '',
                ]
            );
        }

        session()->flash('success', 'Resource berhasil disimpan.');

        return redirect()->route('artikel.index');
        
    }

    /**
     * Path PDF bahasa ini setelah disimpan: unggahan baru menggantikan berkas lama, tanda hapus mengosongkannya.
     */
    private function storePdf(string $locale): ?string
    {
        $upload = $this->{"pdf_{$locale}"};
        $existing = $this->existingPdf[$locale] ?? null;

        if ($upload instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            // Akhiran acak: unggahan pengganti tidak pernah memakai nama berkas yang sama dengan berkas lama.
            return $upload->storeAs('publikasi', Str::slug(Str::limit($this->titleId, 80, '')).'-'.$locale.'-'.Str::lower(Str::random(8)).'.pdf', 'public');
        }

        if ($this->removePdf[$locale] ?? false) {
            if ($existing) {
                Storage::disk('public')->delete($existing);
            }

            return null;
        }

        return $existing;
    }

    protected function translatableFields(): array
    {
        return [
            'titleId' => 'titleEn',
            'excerpt_id' => 'excerpt_en',
            'content_id' => 'content_en',
        ];
    }

    public function render()
    {
        $categories = DB::table('categories')->get();

        return view('livewire.artikels.artikel-form', compact('categories'))->layout('layouts.internal');
    }
}
