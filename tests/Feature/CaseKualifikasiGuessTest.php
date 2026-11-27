<?php

namespace Tests\Feature;

use App\Models\{CaseModel, CaseTranslation, Status};
use App\Services\CaseImport\KualifikasiGuesser;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tebakan kualifikasi untuk kasus yang dokumennya belum punya bagian "Kualifikasi Kasus".
 */
class CaseKualifikasiGuessTest extends TestCase
{
    use RefreshDatabase;

    private function guess(array $texts, ?string $status = null): string
    {
        return (new KualifikasiGuesser)->guess($texts, $status)['kualifikasi'];
    }

    public function test_guesses_track_from_case_content(): void
    {
        $this->assertSame('perdata', $this->guess([
            'title' => 'Gugatan Karhutla terhadap PT Contoh',
            'description' => '<p>KLHK menggugat perusahaan dan menuntut ganti rugi pemulihan lingkungan.</p>',
        ]));
        $this->assertSame('administrasi', $this->guess([
            'title' => 'Penyegelan Konsesi yang Terbakar',
            'perkembangan' => '<p>KLHK menjatuhkan sanksi administratif dan mencabut izin.</p>',
        ]));
        $this->assertSame('pidana', $this->guess([
            'title' => 'Tambang Batu Bara Ilegal',
            'status' => '<p>Dua tersangka ditahan, berkas dinyatakan P21.</p>',
        ]));
        // Gugatan ke PTUN atas izin pemerintah termasuk administrasi, bukan perdata.
        $this->assertSame('administrasi', $this->guess([
            'title' => 'Reklamasi di Pulau Pari',
            'perkembangan' => '<p>Warga mendaftarkan gugatan ke PTUN Jakarta; tergugat adalah BKPM.</p>',
        ]));
        // Gugatan praperadilan bagian dari proses pidana.
        $this->assertSame('pidana', $this->guess([
            'title' => 'Pembalakan Liar',
            'perkembangan' => '<p>Tersangka mengajukan gugatan praperadilan.</p>',
        ]));
        // Tanpa petunjuk: pidana, jalur bawaan halaman publik.
        $this->assertSame('pidana', $this->guess(['title' => 'Deforestasi di Papua']));
    }

    public function test_case_status_takes_precedence(): void
    {
        $gugatanText = ['title' => 'Gugatan Warga', 'description' => 'Warga menuntut ganti rugi.'];

        $this->assertSame('pidana', $this->guess($gugatanText, 'penyidikan'));
        $this->assertSame('administrasi', $this->guess($gugatanText, 'sanksi'));
        $this->assertSame('perdata', $this->guess(['title' => 'Kasus Limbah'], 'gugatan'));
        // Persidangan/Vonis tidak ada di jalur administrasi, jadi tebakannya pidana atau perdata.
        $this->assertSame('pidana', $this->guess(['title' => 'Sanksi administratif dan penyegelan', 'description' => 'Terdakwa divonis.'], 'vonis'));
    }

    public function test_command_fills_only_empty_kualifikasi(): void
    {
        $this->seed(StatusSeeder::class);
        $open = Status::findByKey('open')->id;
        $empty = $this->case('CASE-GUGAT', $open, null, 'Gugatan Karhutla terhadap PT Contoh', 'KLHK menuntut ganti rugi.');
        $byStatus = $this->case('CASE-SIDIK', Status::findByKey('penyidikan')->id, null, 'Perambahan Hutan', 'Belum ada kabar.');
        $filled = $this->case('CASE-ADMIN', $open, 'administrasi', 'Gugatan Warga', 'Tersangka ditahan.');

        $this->artisan('case:isi-kualifikasi', ['--dry-run' => true])
            ->expectsOutputToContain('2 kasus akan diisi (dry run, belum disimpan): 1 Pidana, 1 Perdata, 0 Administrasi.')
            ->assertSuccessful();
        $this->assertNull($empty->fresh()->kualifikasi);

        $this->artisan('case:isi-kualifikasi')->assertSuccessful();

        $this->assertSame('perdata', $empty->fresh()->kualifikasi);
        $this->assertSame('pidana', $byStatus->fresh()->kualifikasi);
        // Kualifikasi yang sudah terisi (dari dokumen atau CMS) tidak diubah.
        $this->assertSame('administrasi', $filled->fresh()->kualifikasi);

        $this->artisan('case:isi-kualifikasi')
            ->expectsOutputToContain('0 kasus diisi')
            ->assertSuccessful();
    }

    private function case(string $number, int $statusId, ?string $kualifikasi, string $title, string $description): CaseModel
    {
        $case = CaseModel::create(['case_number' => $number, 'status_id' => $statusId, 'kualifikasi' => $kualifikasi]);
        CaseTranslation::create(['case_id' => $case->id, 'locale' => 'id', 'title' => $title, 'description' => "<p>{$description}</p>"]);

        return $case;
    }
}
