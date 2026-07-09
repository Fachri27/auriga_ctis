<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('raw_cases', function (Blueprint $table) {
            $table->longText('terdakwa')->nullable()->change();
            $table->longText('subjek_hukum')->nullable()->change();
            $table->longText('penyertaan')->nullable()->change();
            $table->longText('vonis_putusan')->nullable()->change();
            $table->longText('jaksa')->nullable()->change();
            $table->longText('nama_hakim')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_cases', function (Blueprint $table) {
            $table->string('terdakwa')->nullable()->change();
            $table->string('subjek_hukum')->nullable()->change();
            $table->string('penyertaan')->nullable()->change();
            $table->string('vonis_putusan')->nullable()->change();
            $table->string('jaksa')->nullable()->change();
            $table->string('nama_hakim')->nullable()->change();
        });
    }
};
