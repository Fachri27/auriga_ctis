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
        // Only drop the columns if they exist to avoid SQL errors when rolling back
        if (Schema::hasColumn('case_translations', 'pembelajaran') || Schema::hasColumn('case_translations', 'perkembangan')) {
            Schema::table('case_translations', function (Blueprint $table) {
                if (Schema::hasColumn('case_translations', 'pembelajaran')) {
                    $table->dropColumn('pembelajaran');
                }
                if (Schema::hasColumn('case_translations', 'perkembangan')) {
                    $table->dropColumn('perkembangan');
                }
            });
        }
    }
};
