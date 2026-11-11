<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            // Kapan halaman berita aslinya sudah dicek untuk gambar & ringkasan (lihat NewsEnricher)
            $table->timestamp('enriched_at')->nullable()->after('external_id');
        });
    }

    public function down(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            $table->dropColumn('enriched_at');
        });
    }
};
