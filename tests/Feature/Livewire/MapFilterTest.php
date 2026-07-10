<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Peta\MapFilter;
use App\Models\Category;
use App\Models\Status;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MapFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_with_categories_and_statuses()
    {
        Category::create(['slug' => 'mining']);
        Status::create(['key' => 'closed', 'name' => 'Closed']);

        Livewire::test(MapFilter::class)
            ->assertStatus(200)
            ->assertSee('mining')
            ->assertSee('Closed');
    }

    public function test_apply_filter_dispatches_event()
    {
        Livewire::test(MapFilter::class)
            ->set('search', 'test')
            ->set('sector', 'mining')
            ->set('status', 'closed')
            ->set('lat', -6.2)
            ->set('lng', 106.8)
            ->call('applyFilter')
            ->assertDispatched('apply-leaflet-filter');
    }

    public function test_reset_filter_dispatches_event()
    {
        Livewire::test(MapFilter::class)
            ->set('search', 'something')
            ->set('sector', 'mining')
            ->set('status', 'closed')
            ->set('lat', -6.2)
            ->set('lng', 106.8)
            ->call('resetFilter')
            ->assertDispatched('reset-leaflet-filter');
    }

    public function test_reset_filter_clears_properties()
    {
        $component = Livewire::test(MapFilter::class)
            ->set('search', 'test')
            ->set('sector', 'mining')
            ->set('status', 'closed')
            ->set('lat', -6.2)
            ->set('lng', 106.8)
            ->call('resetFilter');

        $component->assertSet('search', '')
            ->assertSet('sector', '')
            ->assertSet('status', '')
            ->assertSet('lat', null)
            ->assertSet('lng', null);
    }

    public function test_set_location_updates_coordinates()
    {
        Livewire::test(MapFilter::class)
            ->dispatch('setLocation', -7.8, 110.4)
            ->assertSet('lat', -7.8)
            ->assertSet('lng', 110.4);
    }
}
