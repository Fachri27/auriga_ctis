<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_cases', function (Blueprint $table) {
            // Tuntutan jaksa (kolom "Tuntutan Penjara"/"Tuntutan Denda" tab CleanData), dibandingkan dengan vonis
            // di grafik rata-rata per tahun. Denda dibuat lebar: sheet memuat tuntutan sampai puluhan triliun.
            $table->decimal('tuntutan_penjara', 12, 2)->nullable()->after('penyertaan');
            $table->decimal('tuntutan_denda', 18, 2)->nullable()->after('subsidair');
        });
    }

    public function down(): void
    {
        Schema::table('raw_cases', function (Blueprint $table) {
            $table->dropColumn(['tuntutan_penjara', 'tuntutan_denda']);
        });
    }
};
