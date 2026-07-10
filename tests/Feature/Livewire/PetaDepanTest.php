<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Peta\PetaDepan;
use App\Models\Category;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PetaDepanTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_with_categories_and_statuses()
    {
        Category::create(['slug' => 'forestry']);
        Status::create(['key' => 'open', 'name' => 'Open']);

        Livewire::test(PetaDepan::class)
            ->assertStatus(200)
            ->assertSee('forestry')
            ->assertSee('Open');
    }

    public function test_apply_filter_dispatches_event()
    {
        Livewire::test(PetaDepan::class)
            ->set('keyword', 'test keyword')
            ->set('sector', 'forestry')
            ->set('status', 'open')
            ->set('location', 'Jakarta')
            ->call('applyFilter')
            ->assertDispatched('apply-leaflet-filter');
    }

    public function test_apply_filter_with_empty_params()
    {
        Livewire::test(PetaDepan::class)
            ->call('applyFilter')
            ->assertDispatched('apply-leaflet-filter');
    }
}
