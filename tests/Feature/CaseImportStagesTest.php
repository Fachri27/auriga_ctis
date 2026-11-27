<?php

namespace Tests\Feature;

use App\Livewire\Cases\CaseImport;
use App\Models\{CaseModel, CaseTranslation, Status, User};
use App\Services\CaseImport\{CaseDocumentParser, CaseImporter, DocxReader, LocationMatcher};
use App\Services\GoogleDriveService;
use Database\Seeders\{RolePermissionSeeder, StatusSeeder};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{DB, Http};
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

/**
 * Bagian "Kualifikasi Kasus" (dropdown Google Docs) dan tabel "Tahapan Kasus" di dokumen kasus.
 */
class CaseImportStagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * .docx minimal. Blok: string (paragraf), ['dropdown' => nilai] (dropdown Google Docs),
     * atau ['table' => [[sel, sel], ...]] dengan sel berupa string atau daftar paragraf.
     */
    private function docx(array $blocks): string
    {
        $p = fn (string $text) => '<w:p><w:r><w:t xml:space="preserve">'.htmlspecialchars($text).'</w:t></w:r></w:p>';
        $body = '';
        foreach ($blocks as $block) {
            if (is_string($block)) {
                $body .= $p($block);
            } elseif (isset($block['dropdown'])) {
                $value = htmlspecialchars($block['dropdown']);
                $body .= '<w:p><w:sdt><w:sdtPr><w:alias w:val="Kualifikasi Kasus"/><w:dropDownList w:lastValue="'.$value.'">'
                    .'<w:listItem w:displayText="Administrasi" w:value="Administrasi"/><w:listItem w:displayText="Pidana" w:value="Pidana"/></w:dropDownList></w:sdtPr>'
                    .'<w:sdtContent><w:r><w:t xml:space="preserve">'.$value.'</w:t></w:r></w:sdtContent></w:sdt><w:r></w:r></w:p>';
            } else {
                $body .= '<w:tbl>';
                foreach ($block['table'] as $row) {
                    $body .= '<w:tr>';
                    foreach ($row as $cell) {
                        $body .= '<w:tc>'.(implode('', array_map($p, (array) $cell)) ?: '<w:p><w:r></w:r></w:p>').'</w:tc>';
                    }
                    $body .= '</w:tr>';
                }
                $body .= '</w:tbl>';
            }
        }

        $path = tempnam(sys_get_temp_dir(), 'test-docx');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'.$body.'</w:body></w:document>');
        $zip->close();
        $binary = file_get_contents($path);
        unlink($path);

        return $binary;
    }

    /** Dokumen seperti templat terbaru: tabel tahapan pidana untuk semua kualifikasi, lalu tabel info perkara. */
    private function administrasiCase(): string
    {
        return $this->docx([
            'Tumpahan Minyak Pertamina RU V di Teluk Balikpapan',
            'Terlapor: Pertamina RU V',
            'Lokasi: Teluk Balikpapan, Kalimantan Timur',
            'Kronologi',
            'Pipa minyak mentah patah pada 31 Maret 2018.',
            'Perkembangan',
            'KLHK menjatuhkan sanksi administratif.',
            'Kualifikasi Kasus',
            ['dropdown' => 'Administrasi'],
            'Tahapan Kasus',
            ['table' => [
                ['Terbuka', ''], ['Dipublikasi', ''], ['Penyelidikan', ''], ['Penyidikan', ''], ['Penuntutan', ''],
                ['Persidangan', ''],
                ['Vonis', ['Pertamina RU dijatuhkan sanksi administratif berupa paksaan pemerintah', 'Audit lingkungan diwajibkan.']],
                ['Hukum Tetap', 'Tidak ada upaya hukum lanjutan.'],
            ]],
            ['table' => [['Pengadilan', 'PN Balikpapan'], ['Nomor perkara', '99/Pdt.G/2019']]],
            'Lesson Learning',
            'Sanksi administratif harus transparan.',
            'Sumber',
            'Mongabay Indonesia.',
        ]);
    }

    private function parse(string $docx): array
    {
        return app(CaseDocumentParser::class)->parse(app(DocxReader::class)->read($docx));
    }

    public function test_parser_reads_kualifikasi_dropdown_and_stage_table(): void
    {
        $parsed = $this->parse($this->administrasiCase());

        $this->assertSame('administrasi', $parsed['kualifikasi']);
        // Vonis pada kasus administrasi = tahap Sanksi; paragraf satu sel digabung per baris.
        $this->assertSame(['sanksi' => "Pertamina RU dijatuhkan sanksi administratif berupa paksaan pemerintah\nAudit lingkungan diwajibkan."], $parsed['keterangan_tahapan']);
        $this->assertSame('sanksi', $parsed['tahap']);
        $this->assertSame(['description', 'perkembangan', 'pembelajaran', 'sumber', 'kualifikasi', 'tahapan'], $parsed['sections']);

        // Tabel tahapan tidak lagi bocor ke Perkembangan; tabel info perkara tetap ikut di sana.
        $this->assertSame('<p>KLHK menjatuhkan sanksi administratif.</p><p>Pengadilan</p><p>PN Balikpapan</p><p>Nomor perkara</p><p>99/Pdt.G/2019</p>', $parsed['perkembangan']);
        $this->assertSame('<p>Sanksi administratif harus transparan.</p>', $parsed['pembelajaran']);
        $this->assertSame(['Keterangan tahap "Hukum Tetap" tidak disimpan: tahap ini tidak ada di halaman publik untuk kualifikasi Administrasi.'], $parsed['warnings']);
    }

    public function test_stage_rows_map_to_each_track(): void
    {
        $table = fn (array $filled) => ['table' => array_map(fn ($stage) => [$stage, $filled[$stage] ?? ''],
            ['Terbuka', 'Dipublikasi', 'Penyelidikan', 'Penyidikan', 'Penuntutan', 'Persidangan', 'Vonis', 'Hukum Tetap'])];
        $doc = fn (array $kualifikasi, array $filled) => $this->docx(['Judul', 'Kronologi', 'Isi pada 2020.', ...$kualifikasi, 'Tahapan Kasus', $table($filled)]);

        // Pidana: status = tahapan terjauh yang terisi.
        $parsed = $this->parse($doc(['Kualifikasi Kasus', ['dropdown' => 'Pidana']], ['Terbuka' => 'Dilaporkan warga.', 'Penyidikan' => 'Dua tersangka.', 'Persidangan' => 'Sidang di PN Sidrap.']));
        $this->assertSame('pidana', $parsed['kualifikasi']);
        $this->assertSame(['terbuka' => 'Dilaporkan warga.', 'penyidikan' => 'Dua tersangka.', 'persidangan' => 'Sidang di PN Sidrap.'], $parsed['keterangan_tahapan']);
        $this->assertSame('trial', $parsed['tahap']);

        // Perdata (label "Kualifikasi Kasus: ..."): Persidangan = Pembuktian; Hukum Tetap menandai status inkracht.
        $parsed = $this->parse($doc(['Kualifikasi Kasus: Perdata'], ['Persidangan' => 'Saksi ahli diperiksa.', 'Vonis' => 'Ganti rugi Rp1 miliar.', 'Hukum Tetap' => 'Kasasi ditolak.']));
        $this->assertSame('perdata', $parsed['kualifikasi']);
        $this->assertSame(['pembuktian' => 'Saksi ahli diperiksa.', 'vonis' => 'Ganti rugi Rp1 miliar.'], $parsed['keterangan_tahapan']);
        $this->assertSame('berkekuatan-hukum-tetap', $parsed['tahap']);

        // Tabel kosong: tidak ada keterangan, status tetap (Terbuka).
        $parsed = $this->parse($doc(['Kualifikasi Kasus', ['dropdown' => 'Pidana']], []));
        $this->assertNull($parsed['keterangan_tahapan']);
        $this->assertNull($parsed['tahap']);
        $this->assertSame([], $parsed['warnings']);

        // Kualifikasi tak dikenal: diberi peringatan, tahapan dibaca sebagai pidana.
        $parsed = $this->parse($doc(['Kualifikasi Kasus', 'Lainnya'], ['Penuntutan' => 'Dakwaan dibacakan.']));
        $this->assertNull($parsed['kualifikasi']);
        $this->assertSame('prosecution', $parsed['tahap']);
        $this->assertStringContainsString('Kualifikasi kasus "Lainnya" tidak dikenali', $parsed['warnings'][0]);
    }

    public function test_parser_reads_kualifikasi_perkara_heading(): void
    {
        // Sebagian dokumen memakai judul "Kualifikasi Perkara"; isinya tidak boleh bocor ke Perkembangan.
        $parsed = $this->parse($this->docx([
            'Gugatan Kebakaran Lahan terhadap PT Contoh',
            'Kronologi',
            'Kebakaran terjadi pada 2015.',
            'Perkembangan',
            'Gugatan dikabulkan.',
            'Kualifikasi Perkara',
            ['dropdown' => 'Perdata (gugatan lingkungan hidup oleh pemerintah)'],
            'Tahapan Kasus',
            ['table' => [['Persidangan', 'Sidang pembuktian.'], ['Vonis', 'Ganti rugi Rp1 triliun.']]],
        ]));

        $this->assertSame('perdata', $parsed['kualifikasi']);
        $this->assertSame('<p>Gugatan dikabulkan.</p>', $parsed['perkembangan']);
        $this->assertSame(['pembuktian' => 'Sidang pembuktian.', 'vonis' => 'Ganti rugi Rp1 triliun.'], $parsed['keterangan_tahapan']);
        $this->assertSame('vonis', $parsed['tahap']);
    }

    public function test_long_stage_notes_are_kept_whole(): void
    {
        $note = str_repeat('Majelis hakim mengabulkan sebagian gugatan dan menghukum tergugat membayar ganti rugi. ', 12);
        $parsed = $this->parse($this->docx([
            'Gugatan Karhutla', 'Kronologi', 'Kebakaran pada 2019.',
            'Kualifikasi Kasus', ['dropdown' => 'Perdata'],
            'Tahapan Kasus', ['table' => [['Vonis', trim($note)]]],
        ]));

        $this->assertGreaterThan(500, mb_strlen($parsed['keterangan_tahapan']['vonis']));
        $this->assertSame(trim($note), $parsed['keterangan_tahapan']['vonis']);
        $this->assertSame([], array_filter($parsed['warnings'], fn ($w) => str_contains($w, 'dipotong')));
    }

    public function test_old_documents_have_no_kualifikasi_or_stages(): void
    {
        $parsed = $this->parse($this->docx(['Judul', 'Kronologi', 'Isi pada 2020.', 'Perkembangan', 'Masih berjalan.']));

        $this->assertNull($parsed['kualifikasi']);
        $this->assertNull($parsed['keterangan_tahapan']);
        $this->assertNull($parsed['tahap']);
        $this->assertSame(['description', 'perkembangan'], $parsed['sections']);
    }

    public function test_import_saves_kualifikasi_stage_notes_and_status(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $this->actingAs(User::factory()->create());
        $this->app->instance(LocationMatcher::class, new class extends LocationMatcher
        {
            public function match(?string $location): ?array
            {
                return null;
            }
        });
        $categoryId = DB::table('categories')->insertGetId(['slug' => 'pencemaran', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);

        $importer = app(CaseImporter::class);
        $preview = $importer->prepare('batch1', 'upload:pertamina', 'pertamina.docx', $this->administrasiCase());
        $this->assertSame('administrasi', $preview['kualifikasi']);
        $this->assertSame('Sanksi', $preview['tahap']);

        $case = $importer->import('batch1', 'upload:pertamina', [$categoryId], '2018-03-31', null, null);

        $this->assertSame('administrasi', $case->kualifikasi);
        $this->assertSame('sanksi', $case->status->key);
        $this->assertFalse((bool) $case->is_public);
        $this->assertSame(
            ['sanksi' => "Pertamina RU dijatuhkan sanksi administratif berupa paksaan pemerintah\nAudit lingkungan diwajibkan."],
            $case->translations->firstWhere('locale', 'id')->keterangan_tahapan,
        );
        $this->assertStringNotContainsString('Tahapan Kasus', $case->translations->firstWhere('locale', 'id')->perkembangan);
        // Teks "Lokasi:" dokumen disimpan apa adanya, walau titik petanya tidak ditemukan.
        $this->assertSame('Teluk Balikpapan, Kalimantan Timur', $case->lokasi);
        $this->assertContains('Titik peta tidak ditemukan otomatis; lokasi tetap tampil sesuai dokumen. Atur titik bila kasus perlu ditampilkan di peta.', $preview['warnings']);
    }

    public function test_preview_table_shows_kualifikasi_and_stages(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->app->instance(LocationMatcher::class, new class extends LocationMatcher
        {
            public function match(?string $location): ?array
            {
                return null;
            }
        });

        Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('uploads', [
                UploadedFile::fake()->createWithContent('pertamina.docx', $this->administrasiCase()),
                UploadedFile::fake()->createWithContent('lama.docx', $this->docx(['Kasus lama', 'Kronologi', 'Isi pada 2020.'])),
            ])
            ->assertSeeInOrder(['Dokumen', 'Kualifikasi', 'Tahapan', 'Lokasi (titik peta)'])
            ->assertSeeInOrder(['Tumpahan Minyak Pertamina', 'pertamina.docx', 'Administrasi', 'Status saat impor:', 'Sanksi', 'Sanksi:', 'Pertamina RU dijatuhkan sanksi administratif'])
            ->assertSeeInOrder(['Kasus lama', 'lama.docx', 'Tidak ada di dokumen', 'Status saat impor:', 'Terbuka', 'Dokumen belum memakai tabel Tahapan Kasus.']);
    }

    public function test_sync_command_applies_documents_to_imported_cases(): void
    {
        $this->seed(StatusSeeder::class);
        $open = Status::findByKey('open');
        $newFormat = $this->importedCase('CASE-ADMIN', 'docAdmin', $open->id, 'pidana', ['terbuka' => 'Catatan CMS']);
        $oldFormat = $this->importedCase('CASE-LAMAA', 'docLama', $open->id, 'pidana');

        config(['services.google_drive.api_key' => 'drive-key']);
        Http::fake([
            'www.googleapis.com/drive/v3/files/docAdmin/export*' => Http::response($this->administrasiCase()),
            'www.googleapis.com/drive/v3/files/docAdmin*' => Http::response(['id' => 'docAdmin', 'name' => '1. Pertamina', 'mimeType' => GoogleDriveService::GOOGLE_DOC]),
            'www.googleapis.com/drive/v3/files/docLama*' => fn ($request) => str_contains($request->url(), 'alt=media')
                ? Http::response($this->docx(['Kasus lama', 'Kronologi', 'Isi pada 2020.']))
                : Http::response(['id' => 'docLama', 'name' => '2. Lama.docx', 'mimeType' => GoogleDriveService::DOCX]),
        ]);

        $this->artisan('case:sync-tahapan', ['--dry-run' => true])
            ->expectsOutputToContain('1 dokumen berformat baru (dry run, belum disimpan), 1 format lama (dilewati), 0 gagal dibaca.')
            ->assertSuccessful();
        $this->assertSame('pidana', $newFormat->fresh()->kualifikasi);

        $this->artisan('case:sync-tahapan')->assertSuccessful();

        $newFormat->refresh();
        $this->assertSame('administrasi', $newFormat->kualifikasi);
        $this->assertSame('sanksi', $newFormat->status->key);
        $this->assertSame(['sanksi' => "Pertamina RU dijatuhkan sanksi administratif berupa paksaan pemerintah\nAudit lingkungan diwajibkan."],
            CaseTranslation::where('case_id', $newFormat->id)->where('locale', 'id')->first()->keterangan_tahapan);
        // Dokumen format lama tidak mengubah apa pun.
        $this->assertSame('pidana', $oldFormat->fresh()->kualifikasi);
        $this->assertSame($open->id, $oldFormat->fresh()->status_id);
    }

    public function test_sync_command_fills_empty_locations_from_documents(): void
    {
        $this->seed(StatusSeeder::class);
        $open = Status::findByKey('open');
        $empty = $this->importedCase('CASE-LOKA1', 'docLokasi', $open->id, 'pidana');
        $filled = $this->importedCase('CASE-LOKA2', 'docIsi', $open->id, 'pidana');
        $filled->update(['lokasi' => 'Diisi di CMS']);

        config(['services.google_drive.api_key' => 'drive-key']);
        $oldDocument = fn (string $id) => fn ($request) => str_contains($request->url(), 'alt=media')
            ? Http::response($this->docx(['Kasus lama', 'Lokasi: Perairan Selat Malaka, WPP-NRI 571', 'Kronologi', 'Isi pada 2020.']))
            : Http::response(['id' => $id, 'name' => "{$id}.docx", 'mimeType' => GoogleDriveService::DOCX]);
        Http::fake([
            'www.googleapis.com/drive/v3/files/docLokasi*' => $oldDocument('docLokasi'),
            'www.googleapis.com/drive/v3/files/docIsi*' => $oldDocument('docIsi'),
        ]);

        $this->artisan('case:sync-tahapan')
            ->expectsOutputToContain('1 kasus diisi lokasinya dari teks "Lokasi:" dokumen.')
            ->assertSuccessful();

        $this->assertSame('Perairan Selat Malaka, WPP-NRI 571', $empty->fresh()->lokasi);
        $this->assertSame('Diisi di CMS', $filled->fresh()->lokasi);
    }

    private function importedCase(string $number, string $fileId, int $statusId, string $kualifikasi, ?array $notes = null): CaseModel
    {
        $case = CaseModel::create(['case_number' => $number, 'source_ref' => "gdrive:{$fileId}", 'status_id' => $statusId, 'kualifikasi' => $kualifikasi]);
        CaseTranslation::create(['case_id' => $case->id, 'locale' => 'id', 'title' => "Kasus {$number}", 'keterangan_tahapan' => $notes]);

        return $case;
    }
}
