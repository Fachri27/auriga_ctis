<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kualifikasi kasus: jalur hukum yang ditempuh (pidana, perdata, administrasi), lihat CaseModel::KUALIFIKASI.
 * Tampil di halaman detail kasus publik di atas tahapan kasus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('kualifikasi', 20)->nullable()->after('status_id');
        });
    }

    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('kualifikasi');
        });
    }
};
