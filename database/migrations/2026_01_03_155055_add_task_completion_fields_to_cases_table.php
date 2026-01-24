<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run the migrations.
     *
     * Adds task completion tracking fields to cases table.
     * These fields track task completion separately from legal case status.
     */
    public function up(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->boolean('is_tasks_completed')->default(false)->after('published_at');
            $table->timestamp('tasks_completed_at')->nullable()->after('is_tasks_completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn(['is_tasks_completed', 'tasks_completed_at']);
        });
    }
};
