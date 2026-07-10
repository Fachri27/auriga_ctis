<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Artikels\ArtikelTable;
use App\Models\Artikel;
use App\Models\ArtikelTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArtikelTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_with_empty_state()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Livewire::test(ArtikelTable::class)
            ->assertStatus(200)
            ->assertSee('Daftar Halaman');
    }

    public function test_lists_artikels()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $artikel = Artikel::create([
            'slug' => 'test-artikel',
            'type' => 'internal',
            'status' => 'active',
            'user_id' => $user->id,
        ]);
        ArtikelTranslation::create([
            'artikel_id' => $artikel->id,
            'locale' => 'id',
            'title' => 'Test Artikel Title',
            'excerpt' => 'Excerpt here',
        ]);

        Livewire::test(ArtikelTable::class)
            ->assertSee('Test Artikel Title');
    }

    public function test_search_filters_by_title()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $a1 = Artikel::create(['slug' => 'alpha', 'type' => 'internal', 'status' => 'active', 'user_id' => $user->id]);
        ArtikelTranslation::create(['artikel_id' => $a1->id, 'locale' => 'id', 'title' => 'Alpha Artikel']);

        $a2 = Artikel::create(['slug' => 'beta', 'type' => 'internal', 'status' => 'active', 'user_id' => $user->id]);
        ArtikelTranslation::create(['artikel_id' => $a2->id, 'locale' => 'id', 'title' => 'Beta Artikel']);

        Livewire::test(ArtikelTable::class)
            ->set('search', 'Alpha')
            ->assertSee('Alpha Artikel')
            ->assertDontSee('Beta Artikel');
    }

    public function test_filters_by_status()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $a1 = Artikel::create(['slug' => 'draft', 'type' => 'internal', 'status' => 'draft', 'user_id' => $user->id]);
        ArtikelTranslation::create(['artikel_id' => $a1->id, 'locale' => 'id', 'title' => 'Draft Artikel']);

        $a2 = Artikel::create(['slug' => 'active', 'type' => 'internal', 'status' => 'active', 'user_id' => $user->id]);
        ArtikelTranslation::create(['artikel_id' => $a2->id, 'locale' => 'id', 'title' => 'Active Artikel']);

        Livewire::test(ArtikelTable::class)
            ->set('status', 'draft')
            ->assertSee('Draft Artikel')
            ->assertDontSee('Active Artikel');
    }

    public function test_filters_by_type()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $a1 = Artikel::create(['slug' => 'internal-art', 'type' => 'internal', 'status' => 'active', 'user_id' => $user->id]);
        ArtikelTranslation::create(['artikel_id' => $a1->id, 'locale' => 'id', 'title' => 'Internal Artikel']);

        $a2 = Artikel::create(['slug' => 'eksternal-art', 'type' => 'eksternal', 'status' => 'active', 'user_id' => $user->id]);
        ArtikelTranslation::create(['artikel_id' => $a2->id, 'locale' => 'id', 'title' => 'Eksternal Artikel']);

        Livewire::test(ArtikelTable::class)
            ->set('type', 'eksternal')
            ->assertSee('Eksternal Artikel')
            ->assertDontSee('Internal Artikel');
    }

    public function test_deletes_artikel()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $artikel = Artikel::create([
            'slug' => 'delete-me',
            'type' => 'internal',
            'status' => 'draft',
            'user_id' => $user->id,
        ]);
        ArtikelTranslation::create([
            'artikel_id' => $artikel->id,
            'locale' => 'id',
            'title' => 'To Delete',
        ]);

        Livewire::test(ArtikelTable::class)
            ->call('delete', $artikel->id);

        $this->assertDatabaseMissing('artikels', ['id' => $artikel->id]);
    }
}
