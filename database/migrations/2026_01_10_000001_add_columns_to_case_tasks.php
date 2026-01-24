<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('case_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('case_tasks', 'process_id')) {
                $table->foreignId('process_id')->nullable()->after('task_id')->constrained('processes')->nullOnDelete();
            }

            if (! Schema::hasColumn('case_tasks', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('process_id')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('case_tasks', 'due_date')) {
                $table->timestamp('due_date')->nullable()->after('assigned_to');
            }
        });
    }

    public function down(): void
    {
        Schema::table('case_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('case_tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }

            if (Schema::hasColumn('case_tasks', 'assigned_to')) {
                $table->dropConstrainedForeignId('assigned_to');
            }

            if (Schema::hasColumn('case_tasks', 'process_id')) {
                $table->dropConstrainedForeignId('process_id');
            }
        });
    }
};
