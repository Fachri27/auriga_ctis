<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create default admin user
        // $admin = User::create([
        //     'name' => 'Admin',
        //     'email' => 'admin@ctis.id',
        //     'password' => bcrypt('123456'),
        //     'role' => 'admin',
        // ]);

        // // Example public user
        // $public = User::create([
        //     'name' => 'Public User',
        //     'email' => 'user@ctis.id',
        //     'password' => bcrypt('123456'),
        //     'role' => 'public',
        // ]);

        // LEGACY: previous seeder that used role assignments via spatie
        
        $super = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@ctis.id',
            'password' => bcrypt('123456'),
        ]);
        $super->assignRole('superadmin');

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@ctis.id',
            'password' => bcrypt('123456'),
        ]);
        $admin->assignRole('admin');

        $cso = User::create([
            'name' => 'CSO',
            'email' => 'cso@ctis.id',
            'password' => bcrypt('123456'),
        ]);
        $cso->assignRole('cso');
    
    }
}
