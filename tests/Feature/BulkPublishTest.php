<?php

namespace Tests\Feature;

use App\Livewire\Cases\CaseList;
use App\Mail\NewCaseMail;
use App\Mail\NewCasesDigestMail;
use App\Models\CaseSubscription;
use App\Models\User;
use App\Services\CasePublisher;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StatusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class BulkPublishTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RolePermissionSeeder::class, StatusSeeder::class]);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        Mail::fake();
    }

    private function createCase(string $number, bool $public = false): int
    {
        $id = DB::table('cases')->insertGetId([
            'case_number' => $number,
            'is_public' => $public,
            'published_at' => $public ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('case_translations')->insert(['case_id' => $id, 'locale' => 'id', 'title' => "Judul {$number}", 'description' => '<p>Isi</p>', 'created_at' => now(), 'updated_at' => now()]);

        return $id;
    }

    public function test_selected_cases_are_published_with_one_digest_email_per_subscriber(): void
    {
        $a = $this->createCase('CASE-AAAAA');
        $b = $this->createCase('CASE-BBBBB');
        $c = $this->createCase('CASE-CCCCC', public: true);
        CaseSubscription::create(['email' => 'semua@contoh.id', 'case_id' => null]);
        CaseSubscription::create(['email' => 'satu@contoh.id', 'case_id' => $a]);

        Livewire::actingAs($this->admin)->test(CaseList::class)
            ->call('togglePage', [$a, $b, $c], true)
            ->assertSee('3 kasus dipilih')
            ->call('bulkPublish')
            ->assertSet('selected', [])
            ->assertSee('2 kasus dipublikasikan. 1 kasus sudah publik sebelumnya (dilewati).')
            ->assertSee('2 kasus belum punya titik peta')
            ->assertSee('Email ringkasan dikirim ke 2 subscriber.');

        $this->assertTrue((bool) DB::table('cases')->where('id', $a)->value('is_public'));
        $this->assertNotNull(DB::table('cases')->where('id', $b)->value('published_at'));

        // Subscriber semua kasus: satu email ringkasan berisi 2 kasus; subscriber kasus A: email kasus biasa.
        Mail::assertQueued(NewCasesDigestMail::class, fn ($m) => $m->hasTo('semua@contoh.id') && count($m->cases) === 2);
        Mail::assertQueued(NewCaseMail::class, fn ($m) => $m->hasTo('satu@contoh.id') && $m->case->case_number === 'CASE-AAAAA');
        Mail::assertQueuedCount(2);
    }

    public function test_all_matching_cases_can_be_published_without_email(): void
    {
        $drafts = [$this->createCase('CASE-D0001'), $this->createCase('CASE-D0002'), $this->createCase('CASE-D0003')];
        $published = $this->createCase('CASE-P0001', public: true);
        CaseSubscription::create(['email' => 'semua@contoh.id', 'case_id' => null]);

        Livewire::actingAs($this->admin)->test(CaseList::class)
            ->set('filter', 'draft')
            ->assertSee('CASE-D0001')
            ->assertDontSee('CASE-P0001')
            ->call('togglePage', [$drafts[0]], true)
            ->call('selectAllMatching')
            ->assertSee('3 kasus dipilih')
            ->set('notify', false)
            ->call('bulkPublish');

        $this->assertSame(4, DB::table('cases')->where('is_public', true)->count());
        Mail::assertNothingQueued();
    }

    public function test_selected_cases_can_be_unpublished(): void
    {
        $a = $this->createCase('CASE-AAAAA', public: true);
        $b = $this->createCase('CASE-BBBBB');

        Livewire::actingAs($this->admin)->test(CaseList::class)
            ->call('togglePage', [$a, $b], true)
            ->call('bulkUnpublish')
            ->assertSee('1 kasus dibatalkan publikasinya. 1 kasus memang belum publik (dilewati).');

        $this->assertFalse((bool) DB::table('cases')->where('id', $a)->value('is_public'));
        $this->assertSame(1, DB::table('case_timelines')->where('case_id', $a)->count());
    }

    public function test_users_without_publish_permission_cannot_bulk_publish(): void
    {
        $a = $this->createCase('CASE-AAAAA');
        $investigator = User::factory()->create();
        $investigator->assignRole('investigator');

        Livewire::actingAs($investigator)->test(CaseList::class)
            ->set('selected', [(string) $a])
            ->assertDontSee('Publikasikan')
            ->call('bulkPublish')
            ->assertSee('Anda tidak memiliki izin untuk mempublikasikan kasus.');

        $this->assertFalse((bool) DB::table('cases')->where('id', $a)->value('is_public'));
    }

    public function test_publishing_a_single_case_still_sends_the_regular_email(): void
    {
        $a = $this->createCase('CASE-AAAAA');
        CaseSubscription::create(['email' => 'semua@contoh.id', 'case_id' => null]);

        $this->actingAs($this->admin);
        $publisher = app(CasePublisher::class);

        $this->assertTrue($publisher->publish($a));
        $this->assertFalse($publisher->publish($a));
        $this->assertSame(1, $publisher->notifySubscribers([$a]));

        Mail::assertQueued(NewCaseMail::class, fn ($m) => $m->hasTo('semua@contoh.id'));
        Mail::assertNotQueued(NewCasesDigestMail::class);
    }
}
