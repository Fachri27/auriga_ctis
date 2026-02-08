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
            'case.create', 'case.view', 'case.update', 'case.change-status',
            'case.publish', 'case.map.publish', 'case.publish.request',
            'case.publish.approve', 'case.unpublish',

            // Case tasks
            'case.task.submit', 'case.task.approve', 'case.task.view',

            // Documents
            'case.document.upload', 'case.document.delete', 'case.document.view',

            // Actors & Timeline
            'case.actor.manage', 'case.timeline.view', 'case.timeline.add',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superadmin   = Role::firstOrCreate(['name' => 'superadmin']);
        $admin        = Role::firstOrCreate(['name' => 'admin']);
        $cso          = Role::firstOrCreate(['name' => 'cso']);
        $investigator = Role::firstOrCreate(['name' => 'investigator']);
        $public       = Role::firstOrCreate(['name' => 'public']);

        // superadmin → semua permission
        $superadmin->syncPermissions(Permission::all());

        // admin
        $admin->syncPermissions([
            'report.view', 'report.verify', 'report.reject', 'report.publish',
            'case.create', 'case.view', 'case.update', 'case.change-status',
            'case.publish', 'case.publish.approve', 'case.unpublish',
            'case.map.publish',
            'case.task.view', 'case.task.submit', 'case.task.approve',
            'case.document.upload', 'case.document.delete', 'case.document.view',
            'case.actor.manage', 'case.timeline.view', 'case.timeline.add',
        ]);

        // investigator
        $investigator->syncPermissions([
            'report.view',
            'case.create', 'case.view', 'case.update',
            'case.task.view', 'case.task.submit',
            'case.timeline.view',
            'case.publish.request',
        ]);

        // cso
        $cso->syncPermissions([
            'report.view',
            'case.create', 'case.view', 'case.update',
            'case.publish', 'case.publish.request',
            'case.document.upload', 'case.document.view',
            'case.timeline.view', 'case.timeline.add',
            'case.actor.manage',
        ]);

        // public
        $public->syncPermissions([
            'report.view',
            'case.view',
            'case.document.view',
            'case.timeline.view',
        ]);
    }
}
