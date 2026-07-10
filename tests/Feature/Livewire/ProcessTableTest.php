<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Process\ProcessTable;
use App\Models\Category;
use App\Models\Process;
use App\Models\ProcessTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProcessTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_renders_with_empty_state()
    {
        Livewire::test(ProcessTable::class)
            ->assertStatus(200)
            ->assertSee('Processes');
    }

    public function test_lists_processes()
    {
        $category = Category::create(['slug' => 'test-cat']);
        $process = Process::create(['category_id' => $category->id, 'order_no' => 1]);
        ProcessTranslation::create([
            'process_id' => $process->id,
            'locale' => 'id',
            'name' => 'Proses Test',
        ]);

        Livewire::test(ProcessTable::class)
            ->assertSee('Proses Test');
    }

    public function test_search_filters_processes()
    {
        $category = Category::create(['slug' => 'test-cat']);
        $p1 = Process::create(['category_id' => $category->id, 'order_no' => 1]);
        ProcessTranslation::create(['process_id' => $p1->id, 'locale' => 'id', 'name' => 'Process Alpha']);

        $p2 = Process::create(['category_id' => $category->id, 'order_no' => 2]);
        ProcessTranslation::create(['process_id' => $p2->id, 'locale' => 'id', 'name' => 'Process Beta']);

        Livewire::test(ProcessTable::class)
            ->set('search', 'Alpha')
            ->assertSee('Process Alpha')
            ->assertDontSee('Process Beta');
    }

    public function test_filters_by_category()
    {
        $cat1 = Category::create(['slug' => 'category-a']);
        $cat2 = Category::create(['slug' => 'category-b']);

        $p1 = Process::create(['category_id' => $cat1->id, 'order_no' => 1]);
        ProcessTranslation::create(['process_id' => $p1->id, 'locale' => 'id', 'name' => 'From Cat A']);

        $p2 = Process::create(['category_id' => $cat2->id, 'order_no' => 1]);
        ProcessTranslation::create(['process_id' => $p2->id, 'locale' => 'id', 'name' => 'From Cat B']);

        Livewire::test(ProcessTable::class)
            ->set('categoryFilter', $cat1->id)
            ->assertSee('From Cat A')
            ->assertDontSee('From Cat B');
    }

    public function test_paginates()
    {
        $category = Category::create(['slug' => 'test-cat']);
        for ($i = 1; $i <= 15; $i++) {
            $p = Process::create(['category_id' => $category->id, 'order_no' => $i]);
            ProcessTranslation::create(['process_id' => $p->id, 'locale' => 'id', 'name' => "Process $i"]);
        }

        $component = Livewire::test(ProcessTable::class);
        $component->assertSeeInOrder(['Process 1', 'Process 10'])
            ->assertDontSee('Process 11');

        $component->call('gotoPage', 2);
        $component->assertSee('Process 11')
            ->assertDontSee('Process 2');
    }

    public function test_deletes_process()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::create(['slug' => 'test-cat']);
        $process = Process::create(['category_id' => $category->id, 'order_no' => 1]);
        ProcessTranslation::create([
            'process_id' => $process->id,
            'locale' => 'id',
            'name' => 'To Delete',
        ]);

        Livewire::test(ProcessTable::class)
            ->call('delete', $process->id);

        $this->assertDatabaseMissing('processes', ['id' => $process->id]);
    }
}
