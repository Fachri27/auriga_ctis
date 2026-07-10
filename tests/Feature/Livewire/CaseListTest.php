<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Cases\CaseList;
use App\Models\CaseModel;
use App\Models\CaseTranslation;
use App\Models\Status;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CaseListTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_with_empty_state()
    {
        Livewire::test(CaseList::class)
            ->assertStatus(200);
    }

    public function test_lists_cases()
    {
        $case = CaseModel::create(['case_number' => 'CS-001']);
        CaseTranslation::create([
            'case_id' => $case->id,
            'locale' => 'id',
            'title' => 'Test Case Title',
        ]);

        Livewire::test(CaseList::class)
            ->assertSee('CS-001');
    }

    public function test_search_filters_by_case_number()
    {
        CaseModel::create(['case_number' => 'CS-001']);
        CaseModel::create(['case_number' => 'CS-002']);

        Livewire::test(CaseList::class)
            ->set('search', 'CS-001')
            ->assertSee('CS-001')
            ->assertDontSee('CS-002');
    }

    public function test_filter_investigation()
    {
        $investigation = Status::create(['key' => 'investigation', 'name' => 'Investigation']);
        $open = Status::create(['key' => 'open', 'name' => 'Open']);

        $c1 = CaseModel::create(['case_number' => 'INV-001', 'status_id' => $investigation->id]);
        $c2 = CaseModel::create(['case_number' => 'OPEN-001', 'status_id' => $open->id]);

        Livewire::test(CaseList::class)
            ->set('filter', 'investigation')
            ->assertSee('INV-001')
            ->assertDontSee('OPEN-001');
    }

    public function test_filter_published()
    {
        $c1 = CaseModel::create(['case_number' => 'PUB-001', 'is_public' => true]);
        $c2 = CaseModel::create(['case_number' => 'PRIV-001', 'is_public' => false]);

        Livewire::test(CaseList::class)
            ->set('filter', 'published')
            ->assertSee('PUB-001')
            ->assertDontSee('PRIV-001');
    }

    public function test_filter_closed()
    {
        $closed = Status::create(['key' => 'closed', 'name' => 'Closed']);
        $open = Status::create(['key' => 'open', 'name' => 'Open']);

        $c1 = CaseModel::create(['case_number' => 'CLS-001', 'status_id' => $closed->id]);
        $c2 = CaseModel::create(['case_number' => 'OPN-001', 'status_id' => $open->id]);

        Livewire::test(CaseList::class)
            ->set('filter', 'closed')
            ->assertSee('CLS-001')
            ->assertDontSee('OPN-001');
    }

    public function test_filter_verif_me()
    {
        $user = User::factory()->create();

        $c1 = CaseModel::create(['case_number' => 'ME-001', 'verified_by' => $user->id]);
        $c2 = CaseModel::create(['case_number' => 'OTHER-001']);

        Livewire::actingAs($user)
            ->test(CaseList::class)
            ->set('filterVerif', 'me')
            ->assertSee('ME-001')
            ->assertDontSee('OTHER-001');
    }

    public function test_filter_verif_pending()
    {
        $open = Status::create(['key' => 'open', 'name' => 'Open']);
        $closed = Status::create(['key' => 'closed', 'name' => 'Closed']);

        $c1 = CaseModel::create(['case_number' => 'PEND-001', 'status_id' => $open->id]);
        $c2 = CaseModel::create(['case_number' => 'CLSD-001', 'status_id' => $closed->id]);

        Livewire::test(CaseList::class)
            ->set('filterVerif', 'pending')
            ->assertSee('PEND-001')
            ->assertDontSee('CLSD-001');
    }

    public function test_filter_verif_rejected()
    {
        $rejected = Status::create(['key' => 'rejected', 'name' => 'Rejected']);
        $open = Status::create(['key' => 'open', 'name' => 'Open']);

        $c1 = CaseModel::create(['case_number' => 'REJ-001', 'status_id' => $rejected->id]);
        $c2 = CaseModel::create(['case_number' => 'OPN-001', 'status_id' => $open->id]);

        Livewire::test(CaseList::class)
            ->set('filterVerif', 'rejected')
            ->assertSee('REJ-001')
            ->assertDontSee('OPN-001');
    }

    public function test_delete_case()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $case = CaseModel::create(['case_number' => 'DEL-001']);

        Livewire::test(CaseList::class)
            ->call('deleteCase', $case->id);

        $this->assertDatabaseMissing('cases', ['id' => $case->id]);
    }

    public function test_search_resets_pagination()
    {
        for ($i = 1; $i <= 15; $i++) {
            $num = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            CaseModel::create(['case_number' => "CS-{$num}"]);
        }

        $component = Livewire::test(CaseList::class);
        $component->call('gotoPage', 2);
        $component->set('search', 'CS-001');
        $component->assertSee('CS-001');
    }
}
