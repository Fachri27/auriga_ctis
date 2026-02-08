<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            StatusSeeder::class,
            ProvinceSeeder::class,
            CategorySeeder::class,
            // Use realistic combined seeder for process/task/templates
            RealisticProcessTaskSeeder::class,
            ReportSeeder::class,   // harus sebelum
            CaseSeeder::class,     // dijalankan
            RealisticCasesSeeder::class, // development helper: insert 10 realistic cases
            RealisticCaseSeeder::class,  // development helper: insert 1 realistic case with full workflow
            UpdateStatusNamesSeeder::class,
        ]);
    }
}
