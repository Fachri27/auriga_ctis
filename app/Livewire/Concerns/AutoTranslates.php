<?php

namespace App\Livewire\Concerns;

use App\Exceptions\TranslationException;
use App\Services\DeepLTranslator;

/**
 * Tombol "Terjemahkan ID → EN" di form CMS (lihat <x-internal.translate-button />).
 *
 * Mengisi properti EN dari properti ID lewat DeepL. Hasilnya hanya mengisi form;
 * baru tersimpan setelah admin memeriksa dan menekan Simpan.
 */
trait AutoTranslates
{
    /**
     * Pasangan properti sumber (ID) => properti target (EN).
     *
     * @return array<string, string>
     */
    abstract protected function translatableFields(): array;

    public function autoTranslate(): void
    {
        $translator = app(DeepLTranslator::class);

        if (! $translator->isConfigured()) {
            $this->dispatch('notify-error', message: 'DeepL belum dikonfigurasi. Isi DEEPL_API_KEY di file .env.');

            return;
        }

        $sources = [];
        foreach ($this->translatableFields() as $from => $to) {
            if (DeepLTranslator::hasText($this->{$from})) {
                $sources[$to] = $this->{$from};
            }
        }

        if ($sources === []) {
            $this->dispatch('notify-error', message: 'Isi versi Bahasa Indonesia terlebih dahulu.');

            return;
        }

        try {
            foreach ($translator->translate($sources) as $to => $text) {
                $this->{$to} = $text;
            }
        } catch (TranslationException $e) {
            $this->dispatch('notify-error', message: $e->getMessage());

            return;
        }

        $this->dispatch('notify', type: 'success', message: count($sources).' field diterjemahkan ke Bahasa Inggris. Periksa hasilnya lalu simpan.');
    }
}
