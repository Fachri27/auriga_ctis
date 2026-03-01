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
        Schema::table('case_geometries', function (Blueprint $table) {
            $table->text('case_description')->nullable()->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('case_geometries', function (Blueprint $table) {
            $table->dropColumn('case_description');
        });
    }
};
