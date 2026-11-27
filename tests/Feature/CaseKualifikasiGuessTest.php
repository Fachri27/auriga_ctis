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
            ->expectsOutputToContain('2 kasus akan diubah (dry run, belum disimpan): 0 dari bagian Kualifikasi Perkara di dokumen, 2 tebakan.')
            ->assertSuccessful();
        $this->assertNull($empty->fresh()->kualifikasi);

        $this->artisan('case:isi-kualifikasi')->assertSuccessful();

        $this->assertSame('perdata', $empty->fresh()->kualifikasi);
        $this->assertSame('pidana', $byStatus->fresh()->kualifikasi);
        // Kualifikasi yang sudah terisi (dari dokumen atau CMS) tidak diubah.
        $this->assertSame('administrasi', $filled->fresh()->kualifikasi);

        $this->artisan('case:isi-kualifikasi')
            ->expectsOutputToContain('0 kasus diubah')
            ->assertSuccessful();
    }

    public function test_kualifikasi_written_in_case_text_wins_and_is_removed(): void
    {
        // Dokumen berjudul "Kualifikasi Perkara" dulu belum dikenali: bagiannya masuk ke Perkembangan (ID & EN),
        // lalu kualifikasinya ditebak perdata dan tahap Vonis tetap memakai jalur pidana/perdata.
        $this->seed(StatusSeeder::class);
        $case = CaseModel::create(['case_number' => 'CASE-WZOEZ', 'status_id' => Status::findByKey('vonis')->id, 'kualifikasi' => 'perdata']);
        CaseTranslation::create(['case_id' => $case->id, 'locale' => 'id', 'title' => 'Tumpahan Minyak di Teluk Balikpapan',
            'perkembangan' => '<p>KLHK menjatuhkan sanksi administratif.</p><p><strong>Kualifikasi Perkara</strong></p><p><strong>Administrasi</strong></p>',
            'keterangan_tahapan' => ['vonis' => 'Pertamina dijatuhi sanksi administratif.']]);
        CaseTranslation::create(['case_id' => $case->id, 'locale' => 'en', 'title' => 'Oil Spill in Balikpapan Bay',
            'perkembangan' => '<p>KLHK imposed administrative sanctions.</p><p><strong>Case Classification</strong></p><p><strong>Administrative</strong></p>',
            'keterangan_tahapan' => ['vonis' => 'Pertamina was given an administrative sanction.']]);
        $other = $this->case('CASE-NAGOP', Status::findByKey('trial')->id, 'pidana', 'Gugatan Kebakaran Lahan',
            'Gugatan dikabulkan.</p><p><strong>Kualifikasi Perkara</strong></p><p>Perdata (gugatan oleh pemerintah)</p><p><strong>Status</strong></p><p>Terbuka');

        $this->artisan('case:isi-kualifikasi', ['--dry-run' => true])
            ->expectsOutputToContain('2 kasus akan diubah (dry run, belum disimpan): 2 dari bagian Kualifikasi Perkara di dokumen, 0 tebakan.')
            ->assertSuccessful();
        $this->assertSame('perdata', $case->fresh()->kualifikasi);

        $this->artisan('case:isi-kualifikasi')->assertSuccessful();

        $case->refresh();
        $this->assertSame('administrasi', $case->kualifikasi);
        // Vonis bukan tahap jalur administrasi: keterangannya pindah ke Sanksi dan status ikut.
        $this->assertSame('sanksi', $case->status->key);
        $id = $case->translations()->where('locale', 'id')->first();
        $en = $case->translations()->where('locale', 'en')->first();
        $this->assertSame(['sanksi' => 'Pertamina dijatuhi sanksi administratif.'], $id->keterangan_tahapan);
        $this->assertSame(['sanksi' => 'Pertamina was given an administrative sanction.'], $en->keterangan_tahapan);
        $this->assertSame('<p>KLHK menjatuhkan sanksi administratif.</p>', $id->perkembangan);
        $this->assertSame('<p>KLHK imposed administrative sanctions.</p>', $en->perkembangan);

        // Nilai panjang dikenali dari kata pertamanya; paragraf setelahnya tidak ikut terhapus.
        $this->assertSame('perdata', $other->fresh()->kualifikasi);
        $this->assertSame('trial', $other->fresh()->status->key);
        $this->assertSame('<p>Gugatan dikabulkan.</p><p><strong>Status</strong></p><p>Terbuka</p>',
            $other->translations()->where('locale', 'id')->value('description'));

        $this->artisan('case:isi-kualifikasi')->expectsOutputToContain('0 kasus diubah')->assertSuccessful();
    }

    public function test_english_notes_follow_the_track_after_sync(): void
    {
        // case:sync-tahapan sudah mengisi kualifikasi & keterangan ID dari dokumen; keterangan EN masih jalur lama.
        $this->seed(StatusSeeder::class);
        $case = CaseModel::create(['case_number' => 'CASE-SYNCD', 'status_id' => Status::findByKey('sanksi')->id, 'kualifikasi' => 'administrasi']);
        CaseTranslation::create(['case_id' => $case->id, 'locale' => 'id', 'title' => 'Tumpahan Minyak',
            'perkembangan' => '<p>Sanksi.</p><p><strong>Kualifikasi Perkara</strong></p><p><strong>Administrasi</strong></p>',
            'keterangan_tahapan' => ['sanksi' => 'Sanksi administratif.']]);
        CaseTranslation::create(['case_id' => $case->id, 'locale' => 'en', 'title' => 'Oil Spill',
            'perkembangan' => '<p>Sanctions.</p><p><strong>Case Classification</strong></p><p><strong>Administrative</strong></p>',
            'keterangan_tahapan' => ['vonis' => 'Administrative sanction.']]);

        $this->artisan('case:isi-kualifikasi')->assertSuccessful();

        $en = $case->translations()->where('locale', 'en')->first();
        $this->assertSame(['sanksi' => 'Administrative sanction.'], $en->keterangan_tahapan);
        $this->assertSame('<p>Sanctions.</p>', $en->perkembangan);
        $this->assertSame('sanksi', $case->fresh()->status->key);
    }

    private function case(string $number, int $statusId, ?string $kualifikasi, string $title, string $description): CaseModel
    {
        $case = CaseModel::create(['case_number' => $number, 'status_id' => $statusId, 'kualifikasi' => $kualifikasi]);
        CaseTranslation::create(['case_id' => $case->id, 'locale' => 'id', 'title' => $title, 'description' => "<p>{$description}</p>"]);

        return $case;
    }
}
