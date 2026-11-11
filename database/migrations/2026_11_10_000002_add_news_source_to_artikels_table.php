<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            // Tautan Google News bisa lebih dari 255 karakter
            $table->text('link')->nullable()->change();

            // Berita yang ditarik otomatis dari media lain (lihat news:pull)
            $table->string('source_name')->nullable()->after('link');
            $table->string('external_id')->nullable()->unique()->after('source_name');
        });
    }

    public function down(): void
    {
        Schema::table('artikels', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropColumn(['source_name', 'external_id']);
            $table->string('link')->nullable()->change();
        });
    }
};
