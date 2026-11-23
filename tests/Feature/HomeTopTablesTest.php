<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeTopTablesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_has_no_judge_or_prosecutor_top_10(): void
    {
        DB::table('raw_cases')->insert([
            'no_perkara' => '1/Pid.B/2022/PN', 'pengadilan' => 'PN Muara Teweh', 'kabupaten' => 'KABUPATEN BARITO UTARA',
            'nama_hakim' => 'HAKIM CONTOH', 'jaksa' => 'JAKSA CONTOH', 'tahun' => 2022,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        foreach (['/id', '/id?tahun=2022'] as $url) {
            $this->get($url)->assertOk()
                ->assertViewHas('tableData', fn ($tables) => array_column($tables, 'title') === ['Top 10 Pengadilan', 'Top 10 Kabupaten/Kota'])
                ->assertDontSee('HAKIM CONTOH')
                ->assertDontSee('JAKSA CONTOH');
        }
    }
}
