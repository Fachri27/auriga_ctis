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
        // Add column only if missing. If 'pembelajaran' exists, place after it; otherwise add normally.
        if (!Schema::hasColumn('case_translations', 'dugaan_permasalahan')) {
            if (Schema::hasColumn('case_translations', 'pembelajaran')) {
                Schema::table('case_translations', function (Blueprint $table) {
                    $table->text('dugaan_permasalahan')->nullable()->after('pembelajaran');
                });
            } else {
                Schema::table('case_translations', function (Blueprint $table) {
                    $table->text('dugaan_permasalahan')->nullable();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('case_translations', 'dugaan_permasalahan')) {
            Schema::table('case_translations', function (Blueprint $table) {
                $table->dropColumn('dugaan_permasalahan');
            });
        }
    }
};
