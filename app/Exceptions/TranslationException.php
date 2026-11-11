<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Kegagalan saat menerjemahkan lewat DeepL. Kode exception = HTTP status dari DeepL
 * (0 bila gagal terhubung), pesannya sudah ramah untuk ditampilkan ke admin.
 */
class TranslationException extends RuntimeException
{
    public static function fromStatus(int $status, ?string $detail = null): self
    {
        $message = match ($status) {
            403 => 'API key DeepL tidak valid atau tidak punya akses.',
            413 => 'Teks terlalu panjang untuk satu permintaan DeepL.',
            429, 529 => 'DeepL sedang membatasi permintaan. Coba lagi beberapa saat lagi.',
            456 => 'Kuota karakter DeepL untuk periode ini sudah habis.',
            default => "DeepL error ({$status})".($detail ? ": {$detail}" : '.'),
        };

        return new self($message, $status);
    }

    /**
     * Error yang tidak akan berhasil walau diulang (kuota habis / key salah).
     */
    public function isPermanent(): bool
    {
        return in_array($this->getCode(), [403, 456], true);
    }
}
