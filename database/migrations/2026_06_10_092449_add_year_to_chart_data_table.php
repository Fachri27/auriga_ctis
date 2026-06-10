<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chart_data', function (Blueprint $table) {
            $table->integer('year')->nullable()->after('dataset');
            $table->dropUnique(['dataset', 'label']);
            $table->unique(['dataset', 'label', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('chart_data', function (Blueprint $table) {
            $table->dropUnique(['dataset', 'label', 'year']);
            $table->unique(['dataset', 'label']);
            $table->dropColumn('year');
        });
    }
};
