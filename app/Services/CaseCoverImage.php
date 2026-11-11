<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Simpan foto sampul kasus di disk public. Foto selalu digambar ulang lewat GD menjadi JPEG baru, sehingga:
 * - metadata (EXIF: lokasi GPS, perangkat, waktu) dari kamera/ponsel ikut terhapus, dan
 * - isi selain gambar yang diselipkan ke berkas tidak ikut tersimpan.
 * Foto juga diputar sesuai orientasi kamera dan diperkecil agar halaman tetap ringan.
 */
class CaseCoverImage
{
    public const DIRECTORY = 'case-covers';

    /** Lebar maksimum hasil (px); cukup untuk halaman detail & pratinjau tautan. */
    public const MAX_WIDTH = 1600;

    public const QUALITY = 82;

    public function store(UploadedFile $file): string
    {
        if (! function_exists('imagecreatefromstring')) {
            throw new RuntimeException('Ekstensi PHP GD dibutuhkan untuk memproses foto sampul.');
        }

        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $image) {
            throw new RuntimeException('Berkas foto sampul tidak bisa dibaca sebagai gambar.');
        }

        $image = $this->orient($image, $file->getRealPath());
        $image = $this->resize($image);

        // Latar putih untuk PNG/WebP transparan, karena JPEG tidak punya transparansi.
        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imageinterlace($canvas, true);

        ob_start();
        imagejpeg($canvas, null, self::QUALITY);
        $jpeg = (string) ob_get_clean();

        $path = self::DIRECTORY.'/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $jpeg);

        return $path;
    }

    public function delete(?string $path): void
    {
        if ($path && str_starts_with($path, self::DIRECTORY.'/')) {
            Storage::disk('public')->delete($path);
        }
    }

    /** Putar foto sesuai tag Orientation EXIF (foto ponsel sering tersimpan miring). */
    private function orient(\GdImage $image, string $path): \GdImage
    {
        $exif = function_exists('exif_read_data') ? @exif_read_data($path) : false;
        $angle = match ((int) ($exif['Orientation'] ?? 1)) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $angle ? (imagerotate($image, $angle, 0) ?: $image) : $image;
    }

    private function resize(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        if ($width <= self::MAX_WIDTH) {
            return $image;
        }

        return imagescale($image, self::MAX_WIDTH, (int) round(imagesy($image) * self::MAX_WIDTH / $width), IMG_BICUBIC) ?: $image;
    }
}
