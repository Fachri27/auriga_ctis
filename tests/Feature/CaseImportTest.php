<?php

namespace Tests\Feature;

use App\Livewire\Cases\CaseForm;
use App\Livewire\Cases\CaseImport;
use App\Models\User;
use App\Services\CaseImport\CaseDocumentParser;
use App\Services\CaseImport\CategoryGuesser;
use App\Services\CaseImport\DocxReader;
use App\Services\CaseImport\LocationMatcher;
use App\Services\GoogleDriveService;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class CaseImportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Buat .docx minimal. Paragraf: string, atau [teks, 'bold' => bool, 'num' => numId, 'link' => url].
     */
    private function docx(array $paragraphs): string
    {
        $body = '';
        foreach ($paragraphs as $p) {
            $p = is_string($p) ? [$p] : $p;
            $props = isset($p['num']) ? '<w:pPr><w:numPr><w:ilvl w:val="0"/><w:numId w:val="'.$p['num'].'"/></w:numPr></w:pPr>' : '';
            $run = '<w:r>'.(! empty($p['bold']) ? '<w:rPr><w:b/></w:rPr>' : '').'<w:t xml:space="preserve">'.htmlspecialchars($p[0]).'</w:t></w:r>';
            if (isset($p['link'])) {
                $run .= '<w:hyperlink r:id="rId9"><w:r><w:t>'.htmlspecialchars($p['link']).'</w:t></w:r></w:hyperlink>';
            }
            $body .= "<w:p>{$props}{$run}</w:p>";
        }

        $w = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"';
        $path = tempnam(sys_get_temp_dir(), 'test-docx');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
        $zip->addFromString('word/document.xml', "<?xml version=\"1.0\"?><w:document {$w}><w:body>{$body}</w:body></w:document>");
        $zip->addFromString('word/numbering.xml', "<?xml version=\"1.0\"?><w:numbering {$w}>"
            .'<w:abstractNum w:abstractNumId="0"><w:lvl w:ilvl="0"><w:numFmt w:val="decimal"/></w:lvl></w:abstractNum>'
            .'<w:abstractNum w:abstractNumId="1"><w:lvl w:ilvl="0"><w:numFmt w:val="bullet"/></w:lvl></w:abstractNum>'
            .'<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num><w:num w:numId="2"><w:abstractNumId w:val="1"/></w:num></w:numbering>');
        $zip->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId9" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink" Target="https://contoh.id/berita" TargetMode="External"/></Relationships>');
        $zip->close();

        $binary = file_get_contents($path);
        unlink($path);

        return $binary;
    }

    private function sampleCase(string $title = 'Pengangkutan Kayu tanpa Dokumen Sah di Sidrap'): string
    {
        return $this->docx([
            [$title, 'bold' => true],
            'Penyidik: Balai Gakkum KLHK Wilayah Sulawesi',
            'Terlapor: SA dan SU',
            'Lokasi: Desa Arawa, Kecamatan Watang Pulu, Kabupaten Sidenreng Rappang, Sulawesi Selatan',
            'Tahun: 2023',
            ['Kronologi', 'bold' => true],
            'Pada 10 Februari 2023 tim operasi menghentikan truk bermuatan kayu & dokumen tidak sah.',
            ['Perkembangan', 'bold' => true],
            'Berkas perkara dinyatakan lengkap.',
            ['Lesson Learning', 'bold' => true],
            ['Pengawasan dokumen angkutan perlu diperkuat.', 'num' => 2],
            ['Makelar harus ikut dijerat.', 'num' => 2],
            ['Status', 'bold' => true],
            'Berkas P-21 (15 Maret 2023); penyidikan jaringan belum dimulai.',
            ['Sumber', 'bold' => true],
            ['Siaran pers Gakkum – ', 'num' => 1, 'link' => 'tautan'],
            '2.  Mongabay, liputan lapangan – https://mongabay.co.id/kayu-sidrap.',
        ]);
    }

    public function test_parser_maps_labels_and_sections(): void
    {
        $parsed = app(CaseDocumentParser::class)->parse(app(DocxReader::class)->read($this->sampleCase()));

        $this->assertSame('Pengangkutan Kayu tanpa Dokumen Sah di Sidrap', $parsed['title']);
        $this->assertSame('SA dan SU', $parsed['terlapor']);
        $this->assertNull($parsed['pelapor']);
        $this->assertSame('<p>Balai Gakkum KLHK Wilayah Sulawesi</p>', $parsed['instansi']);
        $this->assertStringStartsWith('Desa Arawa', $parsed['lokasi']);
        $this->assertSame('<p>Pada 10 Februari 2023 tim operasi menghentikan truk bermuatan kayu &amp; dokumen tidak sah.</p>', $parsed['description']);
        $this->assertSame('<ul><li>Pengawasan dokumen angkutan perlu diperkuat.</li><li>Makelar harus ikut dijerat.</li></ul>', $parsed['pembelajaran']);
        $this->assertSame(
            '<ol><li>Siaran pers Gakkum – <a href="https://contoh.id/berita" target="_blank" rel="noopener">tautan</a></li>'
            .'<li>Mongabay, liputan lapangan – <a href="https://mongabay.co.id/kayu-sidrap" target="_blank" rel="noopener">https://mongabay.co.id/kayu-sidrap</a>.</li></ol>',
            $parsed['sumber'],
        );
        $this->assertSame('2023-02-10', $parsed['event_date']);
        $this->assertSame(['description', 'perkembangan', 'pembelajaran', 'status', 'sumber'], $parsed['sections']);
        $this->assertSame([], $parsed['warnings']);
    }

    public function test_category_is_guessed_from_document_content(): void
    {
        $categories = [];
        foreach (['pertambangan' => 'Pertambangan', 'satwa' => 'Perdagangan Satwa Liar', 'korupsi' => 'Korupsi', 'environment' => 'Lingkungan Hidup'] as $slug => $name) {
            $categories[$slug] = DB::table('categories')->insertGetId(['slug' => $slug, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('category_translations')->insert(['category_id' => $categories[$slug], 'locale' => 'id', 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
        }
        $guesser = new CategoryGuesser;

        $mining = $guesser->guess(['title' => 'Tambang Nikel Ilegal di Konawe', 'description' => '<p>Penambangan nikel tanpa IUP merusak hutan.</p>']);
        $this->assertSame($categories['pertambangan'], $mining['id']);
        $this->assertStringContainsString('nikel', $mining['reason']);

        $wildlife = $guesser->guess(['title' => 'Perdagangan Sisik Trenggiling', 'description' => '<p>Satwa dilindungi dijual daring.</p>']);
        $this->assertSame($categories['satwa'], $wildlife['id']);

        $this->assertSame($categories['korupsi'], $guesser->guess(['title' => 'Suap Izin Ekspor', 'description' => '<p>KPK menetapkan tersangka korupsi.</p>'])['id']);

        // "rusa" di dalam "perusahaan" tidak dihitung sebagai satwa; tema lingkungan lain jatuh ke kategori payung.
        $pollution = $guesser->guess(['title' => 'Gugatan terhadap Enam Perusahaan', 'description' => '<p>Limbah perusahaan mencemari sungai.</p>']);
        $this->assertSame($categories['environment'], $pollution['id']);

        $this->assertNull($guesser->guess(['title' => 'Rapat koordinasi', 'description' => '<p>Tidak ada kata kunci.</p>']));
    }

    public function test_event_date_prefers_year_label(): void
    {
        $parser = app(CaseDocumentParser::class);

        $this->assertSame('2023-08-29', $parser->eventDate('2023', 'Sejak 12 Januari 2015 ... lalu pada 29 Agustus 2023 petugas menangkap RS.'));
        $this->assertSame('2017-01-01', $parser->eventDate('2017–2026', 'Izin terbit pada 2007.'));
        $this->assertSame('2018-03-01', $parser->eventDate(null, 'Kasus bermula pada Maret 2018.'));
        $this->assertNull($parser->eventDate(null, 'Tanpa tanggal.'));
    }

    public function test_location_text_is_split_into_admin_levels(): void
    {
        $matcher = new LocationMatcher;

        $parsed = $matcher->parse('Desa Karebbe, Kecamatan Malili, Luwu Timur, Sulawesi Selatan');
        $this->assertSame(['Karebbe'], $parsed['villages']);
        $this->assertSame(['Malili'], $parsed['districts']);
        $this->assertSame(['Luwu Timur'], $parsed['plain']);
        $this->assertSame('South Sulawesi', $parsed['province']);

        // Beberapa lokasi dicoba berurutan; "Kota X" mendahulukan "X City".
        $parts = $matcher->parts('Kecamatan Pauh, Kota Padang, Sumatera Barat; Pelabuhan Gresik, Jawa Timur');
        $this->assertSame(['Kecamatan Pauh, Kota Padang, Sumatera Barat', 'Pelabuhan Gresik, Jawa Timur'], $parts);
        $parsed = $matcher->parse($parts[0]);
        $this->assertSame(['Padang City', 'Padang'], $parsed['regencies']);
        $this->assertSame('West Sumatra', $parsed['province']);

        // Nama berarah memakai ejaan tabel ("Mendo Barat" → "West Mendo").
        $this->assertSame(['Mendo Barat', 'West Mendo'], $matcher->parse('Desa Penagan, Kecamatan Mendo Barat, Kabupaten Bangka')['districts']);

        // Awalan wilayah di tengah teks & singkatan provinsi.
        $parsed = $matcher->parse('Klangenan dan Desa Penguragan, Kawasan hutan Kabupaten Cirebon, Jabar');
        $this->assertSame(['Klangenan'], $parsed['plain']);
        $this->assertSame(['Penguragan'], $parsed['villages']);
        $this->assertSame(['Cirebon', 'Cirebon City'], $parsed['regencies']);
        $this->assertSame('West Java', $parsed['province']);

        // "Selat" tidak dibuang: "Selat Malaka" bukan Kabupaten Malaka.
        $parsed = $matcher->parse('Taman Nasional Komodo dan perairan Selat Sape, Nusa Tenggara Timur');
        $this->assertSame(['Komodo', 'Selat Sape'], $parsed['plain']);
        $this->assertSame('East Nusa Tenggara', $parsed['province']);
    }

    public function test_drive_service_lists_and_downloads_documents(): void
    {
        config(['services.google_drive.api_key' => 'drive-key']);
        Http::fake([
            'www.googleapis.com/drive/v3/files?*pageToken=p2*' => Http::response(['files' => [
                ['id' => 'doc10', 'name' => '10. Kasus Sepuluh', 'mimeType' => GoogleDriveService::GOOGLE_DOC],
            ]]),
            'www.googleapis.com/drive/v3/files?*' => Http::response(['nextPageToken' => 'p2', 'files' => [
                ['id' => 'doc2', 'name' => '2. Kasus Dua.docx', 'mimeType' => GoogleDriveService::DOCX],
            ]]),
            'www.googleapis.com/drive/v3/files/doc10/export*' => Http::response('DOCX-EXPORT'),
            'www.googleapis.com/drive/v3/files/doc2*' => Http::response('DOCX-FILE'),
        ]);

        $drive = app(GoogleDriveService::class);

        $this->assertSame('1fC9FXE3qBccudnuP9PyEJ3tlX-pAqQ75', GoogleDriveService::folderId('https://drive.google.com/drive/folders/1fC9FXE3qBccudnuP9PyEJ3tlX-pAqQ75?usp=sharing'));
        $this->assertSame(['doc2', 'doc10'], array_column($drive->listDocuments('folder123'), 'id'));
        $this->assertSame('DOCX-EXPORT', $drive->downloadDocx('doc10', GoogleDriveService::GOOGLE_DOC));
        $this->assertSame('DOCX-FILE', $drive->downloadDocx('doc2', GoogleDriveService::DOCX));
        $this->assertSame(['doc10' => 'DOCX-EXPORT', 'doc2' => 'DOCX-FILE'], $drive->downloadMany([
            ['id' => 'doc10', 'mimeType' => GoogleDriveService::GOOGLE_DOC],
            ['id' => 'doc2', 'mimeType' => GoogleDriveService::DOCX],
        ]));

        Http::assertSent(fn (Request $r) => str_contains($r->url(), 'key=drive-key') && str_contains(urldecode($r->url()), "'folder123' in parents"));
        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/files/doc10/export') && str_contains(urldecode($r->url()), 'wordprocessingml'));
    }

    public function test_drive_access_denied_is_reported(): void
    {
        config(['services.google_drive.api_key' => 'drive-key']);
        Http::fake(['*' => Http::response(['error' => ['message' => 'forbidden']], 403)]);

        $this->expectExceptionMessage('Akses Google Drive ditolak');

        app(GoogleDriveService::class)->listDocuments('folder123');
    }

    public function test_key_without_drive_permission_gets_specific_hint(): void
    {
        config(['services.google_drive.api_key' => 'drive-key']);
        Http::fake(['*' => Http::response(['error' => [
            'code' => 403,
            'message' => 'Requests to this API drive method google.apps.drive.v3.DriveFiles.List are blocked.',
            'details' => [['reason' => 'API_KEY_SERVICE_BLOCKED']],
        ]], 403)]);

        $this->expectExceptionMessage('belum mengizinkan Google Drive API');

        app(GoogleDriveService::class)->listDocuments('folder123');
    }

    public function test_import_from_drive_creates_draft_cases_once(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $categoryId = DB::table('categories')->insertGetId(['slug' => 'kehutanan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('category_translations')->insert(['category_id' => $categoryId, 'locale' => 'id', 'name' => 'Kehutanan', 'created_at' => now(), 'updated_at' => now()]);

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        config(['services.google_drive.api_key' => 'drive-key', 'services.deepl.key' => null]);
        Http::fake([
            'www.googleapis.com/drive/v3/files?*' => Http::response(['files' => [
                ['id' => 'docA', 'name' => '1. Kasus A', 'mimeType' => GoogleDriveService::GOOGLE_DOC],
                ['id' => 'docB', 'name' => '2. Kasus B.docx', 'mimeType' => GoogleDriveService::DOCX],
            ]]),
            'www.googleapis.com/drive/v3/files/docA/export*' => Http::response($this->sampleCase('Kasus A')),
            'www.googleapis.com/drive/v3/files/docB*' => Http::response($this->sampleCase('Kasus B')),
        ]);
        $this->app->instance(LocationMatcher::class, new class extends LocationMatcher
        {
            public function match(?string $location): ?array
            {
                return ['label' => 'Arawa, Watang Pulu, Sidenreng Rappang, South Sulawesi', 'lat' => -3.9, 'lng' => 119.8, 'precision' => 'desa'];
            }
        });

        $component = Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('folderUrl', 'https://drive.google.com/drive/folders/folder123abc')
            ->call('loadFolder')
            ->assertSet('total', 2)
            ->assertDispatched('case-import-read')
            ->call('processQueue')
            ->assertSet('queue', [])
            ->assertCount('rows', 2)
            // Kategori terisi otomatis dari isi dokumen (pengangkutan kayu → Kehutanan).
            ->assertSet('rows.0.category_id', $categoryId)
            ->assertSet('rows.1.category_id', $categoryId)
            ->assertSee('Otomatis dari isi dokumen: kayu')
            // Titik diatur manual di peta untuk baris pertama, dan dihapus untuk baris kedua.
            ->call('setPoint', 0, -0.2205123, 101.8731456)
            ->assertSet('rows.0.precision', 'manual')
            ->call('setPoint', 1, null, null)
            ->assertSet('rows.1.lat', null)
            ->call('import')
            ->assertHasNoErrors()
            ->assertRedirect(route('case.index'));

        $cases = DB::table('cases')->orderBy('id')->get();
        $this->assertCount(2, $cases);
        $this->assertSame(['gdrive:docA', 'gdrive:docB'], $cases->pluck('source_ref')->all());
        $this->assertFalse((bool) $cases[0]->is_public);
        $this->assertSame([DB::table('statuses')->where('key', 'open')->value('id')], $cases->pluck('status_id')->unique()->values()->all());
        $this->assertStringContainsString('P-21', DB::table('cases')->value('status_narasi'));
        $this->assertMatchesRegularExpression('/^CASE-[A-Z0-9]{5}$/', $cases[0]->case_number);
        $this->assertSame('2023-02-10', substr($cases[0]->event_date, 0, 10));
        $this->assertEqualsWithDelta(-0.2205123, (float) $cases[0]->latitude, 0.0000001);
        $this->assertEqualsWithDelta(101.8731456, (float) $cases[0]->longitude, 0.0000001);
        $this->assertNull($cases[1]->latitude);
        $this->assertSame([$categoryId], json_decode($cases[0]->category_ids, true));
        $this->assertSame('Kasus A', DB::table('case_translations')->where('case_id', $cases[0]->id)->where('locale', 'id')->value('title'));
        $this->assertSame('', DB::table('case_translations')->where('case_id', $cases[0]->id)->where('locale', 'en')->value('title'));

        // Impor ulang folder yang sama: semua dokumen sudah diimpor, tidak ada yang dibaca lagi.
        Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('folderUrl', 'folder123abc')
            ->call('loadFolder')
            ->assertHasErrors('folderUrl')
            ->assertSet('alreadyImported', 2)
            ->assertSet('queue', []);

        // Dokumen yang sudah diimpor tidak diunduh ulang dari Drive.
        $this->assertCount(1, Http::recorded(fn (Request $r) => str_contains($r->url(), '/files/docA/export')));

        $this->assertSame(2, DB::table('cases')->count());
    }

    private function fakeBlockedFolder($docB): void
    {
        config(['services.google_drive.api_key' => 'drive-key']);
        Http::fake([
            'www.googleapis.com/drive/v3/files?*' => Http::response(['files' => [
                ['id' => 'docA', 'name' => '1. Kasus A', 'mimeType' => GoogleDriveService::GOOGLE_DOC],
                ['id' => 'docB', 'name' => '2. Kasus B.docx', 'mimeType' => GoogleDriveService::DOCX],
            ]]),
            'www.googleapis.com/drive/v3/files/docA/export*' => Http::response($this->sampleCase('Kasus A')),
            'www.googleapis.com/drive/v3/files/docB*' => $docB,
        ]);
        $this->app->instance(LocationMatcher::class, new class extends LocationMatcher
        {
            public function match(?string $location): ?array
            {
                return null;
            }
        });
    }

    private function blockedPage()
    {
        // Halaman "Sorry…" Google saat unduhan otomatis diblokir sementara
        return Http::response('<html><title>Sorry...</title></html>', 403, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    public function test_reading_pauses_and_resumes_when_google_throttles(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->fakeBlockedFolder(Http::sequence()->pushResponse($this->blockedPage())->push($this->sampleCase('Kasus B')));

        $component = Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('folderUrl', 'folder123abc')
            ->call('loadFolder')
            ->call('processQueue')
            ->assertCount('rows', 1)
            ->assertSet('queue.0.id', 'docB')
            ->assertSet('resumeIn', 60)
            ->assertSee('Dilanjutkan otomatis');

        // Masih dalam jeda: tidak ada unduhan baru.
        $component->call('processQueue')->assertCount('rows', 1);
        Http::assertSentCount(3);

        $this->travel(61)->seconds();
        $component->call('processQueue')
            ->assertCount('rows', 2)
            ->assertSet('rows.1.title', 'Kasus B')
            ->assertSet('queue', [])
            ->assertSet('resumeIn', 0);
    }

    public function test_read_documents_can_be_imported_while_rest_is_paused(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $categoryId = DB::table('categories')->insertGetId(['slug' => 'kehutanan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->fakeBlockedFolder($this->blockedPage());
        $folder = 'https://drive.google.com/drive/folders/folder123abc';

        Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('folderUrl', $folder)
            ->call('loadFolder')
            ->call('processQueue')
            ->assertSet('queue.0.id', 'docB')
            ->assertSee('1 dokumen masih menunggu dibaca')
            ->call('import')
            ->assertHasNoErrors()
            ->assertRedirect(route('case.import', ['folder' => $folder]));

        $this->assertSame(['gdrive:docA'], DB::table('cases')->pluck('source_ref')->all());
        $this->assertStringContainsString('Masih ada 1 dokumen', session('success'));
    }

    public function test_incomplete_rows_are_summarized_near_import_button(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        DB::table('categories')->insertGetId(['slug' => 'kehutanan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->fakeBlockedFolder(Http::response($this->sampleCase('Kasus B')));

        Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('folderUrl', 'folder123abc')
            ->call('loadFolder')
            ->call('processQueue')
            ->set('rows.1.event_date', '')
            ->call('import')
            ->assertHasErrors(['rows.1.event_date', 'rows'])
            ->assertSee('1 dokumen belum lengkap (kategori/tanggal): Kasus B')
            ->assertSee('data-row-invalid', false)
            ->assertDispatched('case-import-invalid')
            ->assertNoRedirect();

        $this->assertSame(0, DB::table('cases')->count());
    }

    public function test_long_throttling_marks_rest_failed_and_can_be_retried(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->fakeBlockedFolder($this->blockedPage());

        $component = Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('folderUrl', 'folder123abc')
            ->call('loadFolder');

        for ($i = 0; $i < 6; $i++) {
            $component->call('processQueue');
            $this->travel(301)->seconds();
        }

        $component->assertSet('queue', [])
            ->assertSet('rows.1.title', '2. Kasus B.docx')
            ->assertSet('rows.1.selected', false)
            ->assertSee('memblokir unduhan otomatis')
            ->assertSee('Coba lagi yang gagal (1)')
            ->call('retryFailed')
            ->assertDispatched('case-import-read')
            ->assertSet('queue.0.id', 'docB')
            ->assertCount('rows', 1);
    }

    public function test_large_folder_is_imported_in_stages(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $categoryId = DB::table('categories')->insertGetId(['slug' => 'kehutanan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $files = array_map(fn ($i) => ['id' => "doc{$i}", 'name' => "{$i}. Kasus {$i}", 'mimeType' => GoogleDriveService::GOOGLE_DOC], range(1, 22));
        config(['services.google_drive.api_key' => 'drive-key']);
        Http::fake([
            'www.googleapis.com/drive/v3/files?*' => Http::response(['files' => $files]),
            'www.googleapis.com/drive/v3/files/*/export*' => fn (Request $r) => Http::response($this->sampleCase('Kasus '.str_replace(['https://www.googleapis.com/drive/v3/files/doc', '/export'], '', strtok($r->url(), '?')))),
        ]);
        $this->app->instance(LocationMatcher::class, new class extends LocationMatcher
        {
            public function match(?string $location): ?array
            {
                return null;
            }
        });
        $folder = 'https://drive.google.com/drive/folders/folder123abc';

        // Tahap 1: hanya 20 dokumen pertama yang dibaca.
        Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('folderUrl', $folder)
            ->set('perBatch', 20)
            ->call('loadFolder')
            ->assertSet('total', 20)
            ->assertSet('remaining', 2)
            ->assertSee('2 dokumen lagi menunggu tahap berikutnya')
            ->call('processQueue')
            ->assertCount('rows', 20)
            ->set('defaultCategoryId', $categoryId)
            ->call('applyDefaultCategory')
            ->call('import')
            ->assertRedirect(route('case.import', ['folder' => $folder]));

        $this->assertSame(20, DB::table('cases')->count());
        $this->assertStringContainsString('Masih ada 2 dokumen', session('success'));

        // Tahap 2: link folder terisi dari URL; 20 yang sudah diimpor dilewati tanpa diunduh.
        Livewire::withQueryParams(['folder' => $folder])->actingAs($admin)->test(CaseImport::class)
            ->assertSet('folderUrl', $folder)
            ->call('loadFolder')
            ->assertSet('alreadyImported', 20)
            ->assertSet('total', 2)
            ->assertSet('remaining', 0)
            ->call('processQueue')
            ->assertSet('rows.0.title', 'Kasus 21')
            ->assertSet('rows.1.title', 'Kasus 22');

        $this->assertCount(22, Http::recorded(fn (Request $r) => str_contains($r->url(), '/export')));
    }

    public function test_uploaded_docx_is_previewed(): void
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
            ->set('uploads', [UploadedFile::fake()->createWithContent('kasus.docx', $this->sampleCase('Kasus Upload'))])
            ->assertHasNoErrors()
            ->assertCount('rows', 1)
            ->assertSet('rows.0.title', 'Kasus Upload')
            ->assertSet('rows.0.selected', true)
            ->assertSee('Titik peta tidak ditemukan otomatis')
            ->assertSee('Atur titik')
            ->assertSee('Belum ada titik');
    }

    public function test_document_already_imported_by_title_is_not_selected(): void
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

        // Kasus yang sama sudah masuk lewat folder Drive.
        $caseId = DB::table('cases')->insertGetId(['case_number' => 'CASE-DRIVE', 'source_ref' => 'gdrive:abc', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('case_translations')->insert(['case_id' => $caseId, 'locale' => 'id', 'title' => 'Kasus Sama', 'created_at' => now(), 'updated_at' => now()]);

        Livewire::actingAs($admin)->test(CaseImport::class)
            ->set('uploads', [UploadedFile::fake()->createWithContent('kasus.docx', $this->sampleCase('Kasus Sama'))])
            ->assertSet('rows.0.existing', 'CASE-DRIVE')
            ->assertSet('rows.0.selected', false);
    }

    public function test_editing_case_keeps_case_number(): void
    {
        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $categoryId = DB::table('categories')->insertGetId(['slug' => 'lingkungan', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        $caseId = DB::table('cases')->insertGetId([
            'case_number' => 'CASE-ABCDE',
            'category_ids' => json_encode([$categoryId]),
            'status_id' => DB::table('statuses')->where('key', 'open')->value('id'),
            'event_date' => '2024-01-01',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('case_translations')->insert(['case_id' => $caseId, 'locale' => 'id', 'title' => 'Judul lama', 'created_at' => now(), 'updated_at' => now()]);

        Livewire::actingAs($admin)->test(CaseForm::class, ['caseId' => $caseId])
            ->set('title_id', 'Judul baru')
            ->call('save')
            ->assertRedirect(route('case.index'));

        $this->assertSame('CASE-ABCDE', DB::table('cases')->where('id', $caseId)->value('case_number'));
        $this->assertSame('Judul baru', DB::table('case_translations')->where('case_id', $caseId)->where('locale', 'id')->value('title'));
    }
}
