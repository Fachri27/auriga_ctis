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
        Schema::table('cases', function (Blueprint $table) {
            $table->string('korban')->nullable()->after('longitude');
            $table->string('pekerjaan')->nullable()->after('korban');
            $table->enum('jenis_kelamin', ['L', 'P', 'A'])->nullable()->after('pekerjaan');
            $table->integer('jumlah_korban')->nullable()->after('jenis_kelamin');
            $table->string('konflik')->nullable()->after('jumlah_korban');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('korban');
            $table->dropColumn('pekerjaan');
            $table->dropColumn('jenis_kelamin');
            $table->dropColumn('jumlah_korban');
            $table->dropColumn('konflik');
        });
    }
};
