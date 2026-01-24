<?php

namespace Tests\Feature;

use Spatie\Permission\Models\Permission;
use App\Jobs\SyncCaseGeometry;
use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use Tests\TestCase;

class PublishCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_publish_sets_pending_review()
    {
        Permission::create(['name' => 'case.publish.request', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('case.publish.request');

        $case = CaseModel::create(['case_number' => 'PUB-001', 'is_public' => false]);

        Livewire::actingAs($user)->test(\App\Http\Livewire\PublishCases::class)
            ->call('requestPublish', $case->id)
            ->assertEmitted('publishRequested');

        $this->assertDatabaseHas('cases', [
            'id' => $case->id,
            'publish_status' => 'pending_review',
            'publish_requested_by' => $user->id,
        ]);
    }

    public function test_approve_and_publish_dispatches_geometry_job_and_publishes()
    {
        Bus::fake();

        Permission::create(['name' => 'case.publish.approve', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('case.publish.approve');

        $case = CaseModel::create(['case_number' => 'PUB-002', 'latitude' => 1.23, 'longitude' => 3.21]);

        Livewire::actingAs($user)->test(\App\Http\Livewire\PublishCases::class)
            ->call('approveAndPublish', $case->id)
            ->assertEmitted('casePublished');

        $this->assertDatabaseHas('cases', ['id' => $case->id, 'is_public' => true, 'publish_status' => 'published']);

        Bus::assertDispatched(SyncCaseGeometry::class, function ($job) use ($case) {
            return $job->caseId === $case->id;
        });
    }
}
