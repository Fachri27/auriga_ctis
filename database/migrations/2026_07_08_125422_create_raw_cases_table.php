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
        Schema::create('raw_cases', function (Blueprint $table) {
            $table->id();
            $table->string('no_perkara')->nullable();
            $table->string('pengadilan')->nullable();
            $table->text('perkara')->nullable();
            $table->string('klasifikasi')->nullable();
            $table->string('klasifikasi_clean')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('pulau')->nullable();
            $table->string('terdakwa')->nullable();
            $table->integer('jumlah_terdakwa')->nullable();
            $table->string('subjek_hukum')->nullable();
            $table->string('penyertaan')->nullable();
            $table->decimal('vonis_penjara', 12, 2)->nullable();
            $table->decimal('subsidair', 12, 2)->nullable();
            $table->decimal('vonis_denda', 14, 2)->nullable();
            $table->string('vonis_putusan')->nullable();
            $table->decimal('biaya_perkara', 14, 2)->nullable();
            $table->string('jaksa')->nullable();
            $table->string('nama_hakim')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('raw_cases');
    }
};
