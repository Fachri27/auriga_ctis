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
        Schema::table('case_translations', function (Blueprint $table) {
            $table->string('pembelajaran')->nullable()->after('description');
            $table->string('perkembangan')->nullable()->after('pembelajaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_translations', function (Blueprint $table) {
            $table->dropColumn('pembelajaran');
            $table->dropColumn('perkembangan');
        });
    }
};
