<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            // Reports
            'report.view', 'report.verify', 'report.reject', 'report.publish',

            // Cases
            'case.create', 'case.view', 'case.update', 'case.change-status', 'case.publish', 'case.map.publish', 'case.publish.request', 'case.publish.approve', 'case.unpublish',

            // Case tasks
            'case.task.submit', 'case.task.approve', 'case.task.view',

            // Documents
            'case.document.upload', 'case.document.delete', 'case.document.view',

            // Actors & Timeline
            'case.actor.manage', 'case.timeline.view', 'case.timeline.add',

            // Misc / system
            'system.all',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // LEGACY: previous minimal permissions (kept for rollback/debug)
        /*
        $legacy_permissions = [
            'category.view', 'category.create', 'category.update', 'category.delete',
            'process.view', 'process.create', 'process.update', 'process.delete',
            'task.view', 'task.create', 'task.update', 'task.delete',
            'report.view', 'report.verify',
            'case.view', 'case.update',
        ];

        foreach ($legacy_permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }
        */

        $super = Role::firstOrCreate(['name' => 'superadmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $cso = Role::firstOrCreate(['name' => 'cso']);
        $investigator = Role::firstOrCreate(['name' => 'investigator']);
        $public = Role::firstOrCreate(['name' => 'public']);

        // Assign permissions
        // superadmin gets everything (via system.all AND all permissions as safety)
        $super->givePermissionTo(Permission::pluck('name')->toArray());
        $super->givePermissionTo('system.all');

        // admin: broad access
        $adminPerms = [
            'report.view', 'report.verify', 'report.reject', 'report.publish',
            'case.create', 'case.view', 'case.update', 'case.change-status', 'case.publish', 'case.publish.approve', 'case.unpublish', 'case.map.publish',
            'case.task.view', 'case.task.submit', 'case.task.approve',
            'case.document.upload', 'case.document.delete', 'case.document.view',
            'case.actor.manage', 'case.timeline.view', 'case.timeline.add',
        ];
        $admin->givePermissionTo($adminPerms);

        // investigator: mostly view/update assigned cases and tasks (can request publish)
        $investigator->givePermissionTo([
            'report.view', 'case.create', 'case.view', 'case.update', 'case.task.view', 'case.task.submit', 'case.timeline.view', 'case.publish.request',
        ]);

        // cso: support role with publish request ability (and limited publish)
        $cso->givePermissionTo([
            'report.view', 'case.create', 'case.view', 'case.update', 'case.publish', 'case.publish.request', 'case.document.upload', 'case.document.view', 'case.timeline.view', 'case.timeline.add', 'case.actor.manage',
        ]);

        // public: read-only public content
        $public->givePermissionTo(['report.view', 'case.view', 'case.document.view', 'case.timeline.view']);
    }
}
