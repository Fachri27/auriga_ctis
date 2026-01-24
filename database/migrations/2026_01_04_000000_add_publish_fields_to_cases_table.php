<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Only add columns if they don't already exist (supports sqlite / test runs)
        if (! Schema::hasColumn('cases', 'publish_status')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->string('publish_status')->default('draft')->after('is_public'); // draft|pending_review|published|rejected|unpublished
            });
        }

        if (! Schema::hasColumn('cases', 'publish_requested_at')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->timestamp('publish_requested_at')->nullable()->after('publish_status');
            });
        }

        if (! Schema::hasColumn('cases', 'publish_requested_by')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->foreignId('publish_requested_by')->nullable()->constrained('users')->nullOnDelete()->after('publish_requested_at');
            });
        }

        if (! Schema::hasColumn('cases', 'published_at')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->timestamp('published_at')->nullable()->after('publish_requested_by');
            });
        }

        if (! Schema::hasColumn('cases', 'published_by')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete()->after('published_at');
            });
        }

        if (! Schema::hasColumn('cases', 'publish_notes')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->text('publish_notes')->nullable()->after('published_by');
            });
        }

        if (! Schema::hasColumn('cases', 'map_published_at')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->timestamp('map_published_at')->nullable()->after('publish_notes');
            });
        }

        if (! Schema::hasColumn('cases', 'map_published_by')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->foreignId('map_published_by')->nullable()->constrained('users')->nullOnDelete()->after('map_published_at');
            });
        }
    }

    public function down()
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropForeign(['publish_requested_by']);
            $table->dropForeign(['published_by']);
            $table->dropForeign(['map_published_by']);

            $table->dropColumn([
                'publish_status',
                'publish_requested_at',
                'publish_requested_by',
                'published_at',
                'published_by',
                'publish_notes',
                'map_published_at',
                'map_published_by',
            ]);
        });
    }
};
