<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'is_published')) {
                $table->boolean('is_published')->default(false)->after('category_id');
            }

            if (! Schema::hasColumn('reports', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('is_published');
            }

            if (! Schema::hasColumn('reports', 'published_by')) {
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete()->after('published_at');
            }
        });
    }

    public function down()
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'published_by')) {
                $table->dropForeign(['published_by']);
                $table->dropColumn('published_by');
            }

            if (Schema::hasColumn('reports', 'published_at')) {
                $table->dropColumn('published_at');
            }

            if (Schema::hasColumn('reports', 'is_published')) {
                $table->dropColumn('is_published');
            }
        });
    }
};
