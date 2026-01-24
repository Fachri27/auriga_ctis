<?php

namespace Database\Seeders;

use App\Models\Process;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\ProcessTranslation;

class ProcessSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Category::all() as $category) {

            for ($i = 1; $i <= 2; $i++) {

                $process = Process::create([
                    'category_id' => $category->id,
                    'order_no' => $i,
                    'is_active' => 1,
                ]);

                ProcessTranslation::insert([
                    [
                        'process_id' => $process->id,
                        'locale' => 'id',
                        'name' => "Proses {$i} - {$category->slug}"
                    ],
                    [
                        'process_id' => $process->id,
                        'locale' => 'en',
                        'name' => "Process {$i} - {$category->slug}"
                    ],
                ]);
            }
        }
    }
}
