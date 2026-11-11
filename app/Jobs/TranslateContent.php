<?php

namespace App\Jobs;

use App\Exceptions\TranslationException;
use App\Services\ContentTranslationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Menerjemahkan sekumpulan record ID → EN di background (dipicu dari halaman Auto Translate).
 * Aman diulang: field EN yang sudah terisi dilewati.
 */
class TranslateContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    /**
     * @param  list<int>  $ids
     */
    public function __construct(
        public string $type,
        public array $ids,
    ) {}

    public function handle(ContentTranslationService $service): void
    {
        foreach ($this->ids as $id) {
            try {
                $service->translateRecord($this->type, $id);
            } catch (TranslationException $e) {
                if ($e->isPermanent()) {
                    // Kuota habis / key salah: percobaan ulang tidak akan berhasil.
                    $this->fail($e);

                    return;
                }

                throw $e;
            }
        }
    }
}
