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
        // Only attempt to change column types if the columns exist
        if (Schema::hasColumn('case_translations', 'perkembangan') || Schema::hasColumn('case_translations', 'pembelajaran')) {
            Schema::table('case_translations', function (Blueprint $table) {
                if (Schema::hasColumn('case_translations', 'perkembangan')) {
                    $table->text('perkembangan')->nullable()->change();
                }
                if (Schema::hasColumn('case_translations', 'pembelajaran')) {
                    $table->text('pembelajaran')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop the columns if they exist to avoid SQL errors when rolling back
        if (Schema::hasColumn('case_translations', 'perkembangan') || Schema::hasColumn('case_translations', 'pembelajaran')) {
            Schema::table('case_translations', function (Blueprint $table) {
                if (Schema::hasColumn('case_translations', 'perkembangan')) {
                    $table->dropColumn('perkembangan');
                }
                if (Schema::hasColumn('case_translations', 'pembelajaran')) {
                    $table->dropColumn('pembelajaran');
                }
            });
        }
    }
};
