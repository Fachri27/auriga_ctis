<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Status untuk tahapan kasus perdata (gugatan → pembuktian → vonis) dan administrasi (sanksi),
 * lihat App\Services\CaseStages. Status vonis sudah ada dan dipakai bersama jalur pidana.
 */
return new class extends Migration
{
    private const STATUSES = [
        'gugatan' => 'Gugatan',
        'pembuktian' => 'Pembuktian',
        'sanksi' => 'Sanksi',
    ];

    public function up(): void
    {
        foreach (self::STATUSES as $key => $name) {
            DB::table('statuses')->insertOrIgnore([
                'key' => $key,
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Status yang sudah dipakai kasus dibiarkan, agar status kasus tidak hilang.
        DB::table('statuses')
            ->whereIn('key', array_keys(self::STATUSES))
            ->whereNotExists(fn ($q) => $q->from('cases')->whereColumn('cases.status_id', 'statuses.id'))
            ->delete();
    }
};
