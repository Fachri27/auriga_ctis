<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserDuaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminFachri = User::create([
            'name' => 'Admin Fachri',
            'email' => 'adminfachri@ctis.id',
            'password' => bcrypt('123456'),
            'email_verified_at' => now(), // Mark as verified for testing
        ]);
        $adminFachri->assignRole('admin');  
    }
}
