<?php

namespace Tests\Unit\Policies;

use App\Models\CaseModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CasePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_publish_permission_allows_user()
    {
        Permission::create(['name' => 'case.publish.request', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('case.publish.request');

        // Sanity check
        $this->assertTrue($user->hasPermissionTo('case.publish.request'));

        $case = CaseModel::create(['case_number' => 'TEST-001', 'is_public' => false]);

        // Policy dispatch may depend on Gate; assert policy method directly to avoid provider load order issues
        $this->assertTrue((new \App\Policies\CasePolicy())->requestPublish($user, $case));
    }

    public function test_approve_publish_requires_permission()
    {
        Permission::create(['name' => 'case.publish.approve', 'guard_name' => 'web']);

        $approver = User::factory()->create();
        $approver->givePermissionTo('case.publish.approve');

        $this->assertTrue($approver->hasPermissionTo('case.publish.approve'));

        $case = CaseModel::create(['case_number' => 'TEST-002']);

        $this->assertTrue((new \App\Policies\CasePolicy())->approvePublish($approver, $case));
    }

    public function test_unpublish_requires_permission()
    {
        Permission::create(['name' => 'case.unpublish', 'guard_name' => 'web']);

        $user = User::factory()->create();
        $user->givePermissionTo('case.unpublish');

        $this->assertTrue($user->hasPermissionTo('case.unpublish'));

        $case = CaseModel::create(['case_number' => 'TEST-003', 'is_public' => true]);

        $this->assertTrue((new \App\Policies\CasePolicy())->unpublish($user, $case));
    }
}
