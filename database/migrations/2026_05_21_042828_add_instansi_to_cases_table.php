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
        // Only add columns if they do not already exist
        if (!Schema::hasColumn('cases', 'status_narasi') || !Schema::hasColumn('cases', 'instansi')) {
            Schema::table('cases', function (Blueprint $table) {
                if (!Schema::hasColumn('cases', 'status_narasi')) {
                    $table->longText('status_narasi')->nullable();
                }
                if (!Schema::hasColumn('cases', 'instansi')) {
                    $table->longText('instansi')->nullable()->after('status_narasi');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cases', 'instansi')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->dropColumn('instansi');
            });
        }
    }
};
