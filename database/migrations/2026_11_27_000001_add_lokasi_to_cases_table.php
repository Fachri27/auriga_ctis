<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            // Lokasi apa adanya dari dokumen kasus / CMS ("Teluk Balikpapan, Kalimantan Timur"). Ditampilkan di halaman
            // kasus menggantikan nama wilayah hasil reverse geocoding titik peta.
            $table->string('lokasi', 500)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('lokasi');
        });
    }
};
