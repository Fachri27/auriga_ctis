<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keterangan custom per tahapan kasus, per bahasa: {"gugatan": "Gugatan didaftarkan ...", ...}.
 * Tahapan yang tidak diisi memakai keterangan standar (lihat App\Services\CaseStages).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_translations', function (Blueprint $table) {
            $table->json('keterangan_tahapan')->nullable()->after('dugaan_permasalahan');
        });
    }

    public function down(): void
    {
        Schema::table('case_translations', function (Blueprint $table) {
            $table->dropColumn('keterangan_tahapan');
        });
    }
};
