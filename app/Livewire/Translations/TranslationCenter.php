<?php

namespace App\Livewire\Translations;

use App\Jobs\TranslateContent;
use App\Services\{ContentTranslationService, DeepLTranslator};
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

/**
 * Halaman CMS untuk menerjemahkan massal semua konten yang versi EN-nya masih kosong.
 * Pekerjaan dijalankan di queue (TranslateContent), jadi butuh queue worker yang berjalan.
 */
class TranslationCenter extends Component
{
    private const IDS_PER_JOB = 10;

    // Aktifkan auto-refresh angka setelah ada pekerjaan yang dijadwalkan.
    public bool $polling = false;

    public function translate(string $type): void
    {
        ContentTranslationService::config($type);

        $this->queue([$type]);
    }

    public function translateAll(): void
    {
        $this->queue(array_keys(ContentTranslationService::TYPES));
    }

    /**
     * @param  list<string>  $types
     */
    private function queue(array $types): void
    {
        if (! app(DeepLTranslator::class)->isConfigured()) {
            $this->dispatch('notify-error', message: 'DeepL belum dikonfigurasi. Isi DEEPL_API_KEY di file .env.');

            return;
        }

        $service = app(ContentTranslationService::class);
        $queued = 0;

        foreach ($types as $type) {
            foreach ($service->pendingIds($type)->chunk(self::IDS_PER_JOB) as $ids) {
                TranslateContent::dispatch($type, $ids->values()->all());
                $queued += $ids->count();
            }
        }

        if ($queued === 0) {
            $this->dispatch('notify', type: 'success', message: 'Semua konten sudah punya versi English.');

            return;
        }

        Cache::forget('deepl_usage');
        $this->polling = true;
        $this->dispatch('notify', type: 'success', message: "{$queued} item dijadwalkan untuk diterjemahkan di background.");
    }

    public function render()
    {
        $translator = app(DeepLTranslator::class);
        $configured = $translator->isConfigured();
        $rows = app(ContentTranslationService::class)->summary();

        if ($this->polling && collect($rows)->sum('pending') === 0) {
            $this->polling = false;
        }

        return view('livewire.translations.translation-center', [
            'rows' => $rows,
            'configured' => $configured,
            'usage' => $configured ? Cache::remember('deepl_usage', 60, fn () => $translator->usage()) : null,
        ])->layout('layouts.internal');
    }
}
