<?php

namespace Tests\Feature;

use App\Exceptions\TranslationException;
use App\Jobs\TranslateContent;
use App\Livewire\Categories\CategoriesForm;
use App\Livewire\Translations\TranslationCenter;
use App\Services\ContentTranslationService;
use App\Services\DeepLTranslator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class AutoTranslateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.deepl.key' => 'test-key:fx', 'services.deepl.target_lang' => 'EN-US']);
    }

    /** DeepL palsu: setiap teks dikembalikan dengan awalan "[EN] ". */
    private function fakeDeepL(): void
    {
        Http::fake([
            '*/v2/translate' => fn (Request $request) => Http::response([
                'translations' => array_map(
                    fn ($text) => ['detected_source_language' => 'ID', 'text' => '[EN] '.$text],
                    $request['text'],
                ),
            ]),
            '*/v2/usage' => Http::response(['character_count' => 1200, 'character_limit' => 500000]),
        ]);
    }

    private function createCase(string $number, array $id, ?array $en = null, bool $deleted = false): int
    {
        $caseId = DB::table('cases')->insertGetId([
            'case_number' => $number,
            'deleted_at' => $deleted ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (array_filter(['id' => $id, 'en' => $en]) as $locale => $fields) {
            DB::table('case_translations')->insert($fields + [
                'case_id' => $caseId,
                'locale' => $locale,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $caseId;
    }

    public function test_translator_keeps_keys_and_sends_html_separately(): void
    {
        $this->fakeDeepL();

        $result = app(DeepLTranslator::class)->translate([
            'title' => 'Pencemaran sungai',
            'body' => '<p>Limbah <strong>tambang</strong></p>',
            'empty' => '',
            'media' => '<img src="peta.png">',
        ]);

        $this->assertSame([
            'title' => '[EN] Pencemaran sungai',
            'body' => '[EN] <p>Limbah <strong>tambang</strong></p>',
            'empty' => '',
            'media' => '<img src="peta.png">',
        ], $result);

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://api-free.deepl.com/v2/translate')
            && $r->hasHeader('Authorization', 'DeepL-Auth-Key test-key:fx')
            && $r['source_lang'] === 'ID'
            && $r['target_lang'] === 'EN-US'
            && ($r['tag_handling'] ?? null) === 'html'
            && $r['text'] === ['<p>Limbah <strong>tambang</strong></p>']);
        Http::assertSent(fn (Request $r) => str_ends_with($r->url(), '/v2/translate')
            && ! isset($r['tag_handling'])
            && $r['text'] === ['Pencemaran sungai']);
    }

    public function test_translator_uses_pro_endpoint_for_pro_keys(): void
    {
        config(['services.deepl.key' => 'pro-key']);
        $this->fakeDeepL();

        app(DeepLTranslator::class)->translate(['Judul']);

        Http::assertSent(fn (Request $r) => str_starts_with($r->url(), 'https://api.deepl.com/v2/translate'));
    }

    public function test_translator_reports_quota_exceeded(): void
    {
        Http::fake(['*' => Http::response(['message' => 'Quota exceeded'], 456)]);

        try {
            app(DeepLTranslator::class)->translate(['Judul']);
            $this->fail('TranslationException tidak dilempar');
        } catch (TranslationException $e) {
            $this->assertTrue($e->isPermanent());
            $this->assertStringContainsString('Kuota', $e->getMessage());
        }
    }

    public function test_translate_record_fills_only_empty_english_fields(): void
    {
        $this->fakeDeepL();

        $caseId = $this->createCase('CASE-AAAAA',
            ['title' => 'Kebakaran hutan', 'description' => '<p>Lahan gambut terbakar</p>', 'pembelajaran' => 'Pengawasan lemah'],
            ['title' => 'Forest fire (manual)', 'description' => '', 'pembelajaran' => null],
        );

        $count = app(ContentTranslationService::class)->translateRecord('case', $caseId);

        $en = DB::table('case_translations')->where('case_id', $caseId)->where('locale', 'en')->first();
        $this->assertSame(2, $count);
        $this->assertSame('Forest fire (manual)', $en->title);
        $this->assertSame('[EN] <p>Lahan gambut terbakar</p>', $en->description);
        $this->assertSame('[EN] Pengawasan lemah', $en->pembelajaran);
        $this->assertSame('[EN] Lahan gambut terbakar', $en->summary);
    }

    public function test_translate_record_fills_missing_english_stage_notes(): void
    {
        $this->fakeDeepL();

        $caseId = $this->createCase('CASE-TAHAP',
            ['title' => 'Tumpahan minyak', 'keterangan_tahapan' => json_encode(['sanksi' => 'Paksaan pemerintah', 'terbuka' => 'Dilaporkan warga'])],
            ['title' => 'Oil spill', 'keterangan_tahapan' => json_encode(['terbuka' => 'Reported by residents (manual)'])],
        );

        $this->assertSame(1, app(ContentTranslationService::class)->translateRecord('case', $caseId));

        $en = DB::table('case_translations')->where('case_id', $caseId)->where('locale', 'en')->first();
        $this->assertSame('Oil spill', $en->title);
        $this->assertSame(['terbuka' => 'Reported by residents (manual)', 'sanksi' => '[EN] Paksaan pemerintah'], json_decode($en->keterangan_tahapan, true));
    }

    public function test_pending_ids_and_missing_english_row(): void
    {
        $this->fakeDeepL();

        $missing = $this->createCase('CASE-BBBBB', ['title' => 'Tambang ilegal']);
        $this->createCase('CASE-CCCCC', ['title' => 'Sudah lengkap'], ['title' => 'Already complete']);
        $this->createCase('CASE-DDDDD', ['title' => 'Kasus terhapus'], null, deleted: true);

        $service = app(ContentTranslationService::class);
        $this->assertSame([$missing], $service->pendingIds('case')->all());

        $service->translateRecord('case', $missing);

        $this->assertSame('[EN] Tambang ilegal', DB::table('case_translations')
            ->where('case_id', $missing)->where('locale', 'en')->value('title'));
        $this->assertSame([], $service->pendingIds('case')->all());
    }

    public function test_form_button_fills_english_fields(): void
    {
        $this->fakeDeepL();

        Livewire::test(CategoriesForm::class)
            ->set('name_id', 'Pertambangan')
            ->call('autoTranslate')
            ->assertSet('name_en', '[EN] Pertambangan')
            ->assertDispatched('notify');
    }

    public function test_form_button_warns_when_deepl_not_configured(): void
    {
        config(['services.deepl.key' => null]);
        Http::fake();

        Livewire::test(CategoriesForm::class)
            ->set('name_id', 'Pertambangan')
            ->call('autoTranslate')
            ->assertSet('name_en', null)
            ->assertDispatched('notify-error');

        Http::assertNothingSent();
    }

    public function test_translation_center_queues_pending_records(): void
    {
        $this->fakeDeepL();
        Queue::fake();

        $caseId = $this->createCase('CASE-EEEEE', ['title' => 'Pencemaran laut']);

        Livewire::test(TranslationCenter::class)
            ->assertSee('Kasus')
            ->call('translateAll')
            ->assertSet('polling', true);

        Queue::assertPushed(TranslateContent::class, fn ($job) => $job->type === 'case' && $job->ids === [$caseId]);
    }
}
