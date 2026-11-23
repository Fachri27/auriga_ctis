<?php

namespace Database\Seeders;

use App\Models\{Artikel, ArtikelTranslation};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Analisis Auriga contoh untuk melihat halaman publikasi: cover, deskripsi, tautan publikasi, dan tombol
 * "Unduh PDF: Indonesia / English". Cover dan PDF-nya dibuat di sini (berkas contoh, bukan laporan asli).
 *
 *   php artisan db:seed --class=AnalisisDemoSeeder
 *
 * Ditandai "[Contoh]" dan tidak dijalankan di production. Aman diulang: artikel dikenali dari slug-nya.
 */
class AnalisisDemoSeeder extends Seeder
{
    private const TITLE = '[Contoh] Enhancing the Capacity of Law Enforcement Agencies and Civil Servant Investigators (PPNS) in Natural Resources Sector';

    private const LINK = 'https://gakkum-sda.id/id/publikasi/detail/enhancing-the-capacity-of-law-enforcement-agencies-and-civil-servant-investigators-ppns-in-natural-resources-sector';

    private const CONTENT = [
        'id' => 'Situasi pandemi juga tak terhindarkan oleh tim proyek ini; implementasi proyek pun terkena dampak yang berarti. Cara menyelenggarakan rapat, pelatihan, atau diskusi bergeser ke medium daring (online). Pertemuan-pertemuan offline dilakukan hanya untuk yang benar-benar penting. Hal ini tentu saja berdampak terhadap proses pencapaian output. Yang masih bisa menjadi fokus adalah pengumpulan data, pelatihan daring, dan pengembangan dan penyelesaian kajian atau kertas kerja.',
        'en' => 'The pandemic also affected this project team, and the project\'s implementation was significantly impacted. Meetings, training, and discussions moved online. Offline meetings were held only when truly necessary. This naturally affected progress toward the outputs. The work that could stay in focus was data collection, online training, and the development and completion of studies and working papers.',
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('AnalisisDemoSeeder berisi artikel contoh, tidak dijalankan di production.');

            return;
        }

        $disk = Storage::disk('public');
        $disk->put('pages/contoh-analisis-cover.jpg', $this->cover());
        $disk->put('publikasi/contoh-analisis-id.pdf', $this->pdf('Bahasa Indonesia'));
        $disk->put('publikasi/contoh-analisis-en.pdf', $this->pdf('English'));

        $artikel = Artikel::updateOrCreate(['slug' => Str::slug(self::TITLE)], [
            'type' => 'internal',
            'status' => 'active',
            'published_at' => now()->toDateString(),
            'image' => 'pages/contoh-analisis-cover.jpg',
            'link' => self::LINK,
            'pdf_id' => 'publikasi/contoh-analisis-id.pdf',
            'pdf_en' => 'publikasi/contoh-analisis-en.pdf',
        ]);

        foreach (self::CONTENT as $locale => $text) {
            ArtikelTranslation::updateOrCreate(['artikel_id' => $artikel->id, 'locale' => $locale], [
                'title' => self::TITLE,
                'excerpt' => Str::limit($text, 160),
                'content' => "<p>{$text}</p>",
            ]);
        }

        $this->command?->info('Analisis contoh: /id/artikel/'.$artikel->slug);
    }

    /** Cover laporan potret (600 × 800) bergaya sampul publikasi Auriga. */
    private function cover(): string
    {
        $image = imagecreatetruecolor(600, 800);
        $white = imagecolorallocate($image, 255, 255, 255);
        $green = imagecolorallocate($image, 0x2F, 0x6C, 0x14);
        $lime = imagecolorallocate($image, 0x9B, 0xDB, 0x4D);
        $dark = imagecolorallocate($image, 0x0B, 0x1E, 0x07);
        $grey = imagecolorallocate($image, 0xE2, 0xE6, 0xDA);
        imagefill($image, 0, 0, $white);

        // Blok "foto" dan lingkaran dekoratif, seperti sampul laporan.
        imagefilledrectangle($image, 140, 150, 560, 470, $green);
        imagefilledellipse($image, 140, 470, 300, 300, $lime);
        imagefilledrectangle($image, 140, 470, 290, 620, $green);
        imagefilledrectangle($image, 40, 500, 130, 760, $grey);
        foreach ([60, 110, 160] as $radius) {
            imageellipse($image, 500, 120, $radius * 2, $radius * 2, $grey);
        }

        $font = collect([
            '/System/Library/Fonts/Supplemental/Arial Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans-Bold.ttf',
        ])->first(fn ($path) => is_file($path));

        $text = function (string $line, int $size, int $x, int $y, int $color) use ($image, $font) {
            $font
                ? imagettftext($image, $size, 0, $x, $y, $color, $font, $line)
                : imagestring($image, $size >= 14 ? 5 : 3, $x, $y - 14, $line, $color);
        };

        $text('AURIGA', 18, 40, 70, $green);
        $text('DOKUMEN CONTOH', 11, 400, 70, $dark);
        $y = 520;
        foreach (explode("\n", wordwrap('ENHANCING THE CAPACITY OF LAW ENFORCEMENT AGENCIES AND CIVIL SERVANT INVESTIGATORS (PPNS) IN NATURAL RESOURCES SECTOR', 24)) as $line) {
            $text($line, 15, 310, $y, $dark);
            $y += 26;
        }
        $text('YAYASAN AURIGA NUSANTARA', 10, 310, 760, $green);

        ob_start();
        imagejpeg($image, null, 85);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    /** PDF satu halaman berisi keterangan bahwa ini berkas contoh. */
    private function pdf(string $language): string
    {
        $lines = [
            'DOKUMEN CONTOH / SAMPLE DOCUMENT',
            '',
            ...explode("\n", wordwrap(Str::after(self::TITLE, '[Contoh] '), 70)),
            '',
            "Versi: {$language}",
            'Berkas ini hanya untuk menguji tombol Unduh PDF di halaman Analisis Auriga.',
            'This file only tests the Download PDF buttons on the Auriga Analysis page.',
        ];

        $stream = 'BT /F1 12 Tf 72 770 Td 18 TL';
        foreach ($lines as $line) {
            $stream .= ' ('.str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line).') Tj T*';
        }
        $stream .= ' ET';

        $objects = [
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>',
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
            '<< /Length '.strlen($stream)." >>\nstream\n{$stream}\nendstream",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[] = strlen($pdf);
            $pdf .= ($i + 1)." 0 obj\n{$object}\nendobj\n";
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n0000000000 65535 f \n";
        foreach ($offsets as $offset) {
            $pdf .= sprintf("%010d 00000 n \n", $offset);
        }

        return $pdf."trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF\n";
    }
}
