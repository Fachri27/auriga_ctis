<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Berkas PDF Analisis Auriga (artikel internal) per bahasa, untuk tombol "Unduh PDF: Indonesia / English"
 * di halaman detailnya. Path di disk public.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            $table->string('pdf_id')->nullable()->after('link');
            $table->string('pdf_en')->nullable()->after('pdf_id');
        });
    }

    public function down(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            $table->dropColumn(['pdf_id', 'pdf_en']);
        });
    }
};
