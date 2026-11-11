<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto sampul kasus: tampil di kartu kasus, bagian atas halaman detail, dan pratinjau tautan (og:image).
 * Keterangan foto per bahasa ada di case_translations; kredit foto tidak perlu diterjemahkan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->string('cover_image')->nullable()->after('bukti');
            $table->string('cover_credit', 150)->nullable()->after('cover_image');
        });

        Schema::table('case_translations', function (Blueprint $table) {
            $table->string('cover_caption', 255)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('case_translations', function (Blueprint $table) {
            $table->dropColumn('cover_caption');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['cover_image', 'cover_credit']);
        });
    }
};
