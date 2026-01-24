<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        /*
        |--------------------------------------------------------------------------
        | LOCATION TABLES
        |--------------------------------------------------------------------------
        */

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->index();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('provinces')->cascadeOnDelete();
            $table->string('code')->nullable()->index();
            $table->string('name');
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CATEGORIES + TRANSLATIONS
        |--------------------------------------------------------------------------
        */

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['category_id', 'locale']);
        });

        /*
        |--------------------------------------------------------------------------
        | PROCESS → TASK → REQUIREMENTS
        |--------------------------------------------------------------------------
        */

        Schema::create('processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->integer('order_no')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('process_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->timestamps();
            $table->unique(['process_id', 'locale']);
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained()->cascadeOnDelete();
            $table->integer('due_days')->nullable();
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('task_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['task_id', 'locale']);
        });

        Schema::create('task_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('field_type')->default('text');
            $table->boolean('is_required')->default(true);
            $table->json('options')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | STATUSES (STATUS CASE/REPORT)
        |--------------------------------------------------------------------------
        */

        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | REPORT SYSTEM
        |--------------------------------------------------------------------------
        */

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code')->unique();

            // IDENTITAS LENGKAP
            $table->string('nama_lengkap')->nullable();
            $table->string('nik')->nullable();
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->nullable();
            $table->string('pekerjaan')->nullable();
            $table->string('kewarganegaraan')->nullable();
            $table->string('status_perkawinan')->nullable();

            // LOKASI KEJADIAN ATAU TEMUAN
            $table->decimal('lat', 10, 7)->nullable()->index();
            $table->decimal('lng', 10, 7)->nullable()->index();

            // BUKTI
            $table->json('evidence')->nullable();

            // STATUS
            $table->foreignId('status_id')->nullable()
                ->constrained('statuses')->nullOnDelete();

            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            // USER (JIKA ADA)
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('report_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->text('description');
            $table->timestamps();
            $table->unique(['report_id', 'locale']);
        });

        /*
        |--------------------------------------------------------------------------
        | CASE SYSTEM
        |--------------------------------------------------------------------------
        */

        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();

            $table->foreignId('report_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('status_id')->nullable()->constrained('statuses')->nullOnDelete();

            $table->date('event_date')->nullable();

            $table->foreignId('province_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->boolean('is_public')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('case_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->unique(['case_id', 'locale']);
        });

        /*
        |--------------------------------------------------------------------------
        | CASE TIMELINES
        |--------------------------------------------------------------------------
        */

        Schema::create('case_timelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('process_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CASE DOCUMENTS
        |--------------------------------------------------------------------------
        */

        Schema::create('case_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('process_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');
            $table->string('mime')->nullable();
            $table->string('title')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CASE DISCUSSION (CHAT)
        |--------------------------------------------------------------------------
        */

        Schema::create('case_discussions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('message');
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('case_tasks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('case_id');
            $table->unsignedBigInteger('task_id');

            $table->enum('status', ['pending', 'submitted', 'approved', 'rejected'])
                ->default('pending');

            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('cases')->cascadeOnDelete();
            $table->foreign('task_id')->references('id')->on('tasks')->cascadeOnDelete();
        });

        Schema::create('case_task_requirements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('case_task_id');
            $table->unsignedBigInteger('requirement_id'); // FK task_requirements

            $table->text('value')->nullable(); // TEXT, FILE PATH, JSON

            $table->timestamps();

            $table->foreign('case_task_id')->references('id')->on('case_tasks')->cascadeOnDelete();
            $table->foreign('requirement_id')->references('id')->on('task_requirements')->cascadeOnDelete();
        });

        /*
        |--------------------------------------------------------------------------
        | NOTIFICATIONS
        |--------------------------------------------------------------------------
        */

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->nullableMorphs('notifiable');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | CASE ACTORS (PELAKU/KORBAN/INSTANSI)
        |--------------------------------------------------------------------------
        */

        Schema::create('case_actors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['corporate', 'government', 'citizen'])->default('citizen');
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('case_geometries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained()->cascadeOnDelete();
            $table->geometry('geom'); // POINT / POLYGON
            $table->string('title');
            $table->string('category')->nullable();
            $table->string('status')->default('draft');
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('case_actors');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('case_discussions');
        Schema::dropIfExists('case_documents');
        Schema::dropIfExists('case_timelines');

        Schema::dropIfExists('case_translations');
        Schema::dropIfExists('cases');

        Schema::dropIfExists('report_translations');
        Schema::dropIfExists('reports');

        Schema::dropIfExists('task_requirements');
        Schema::dropIfExists('task_translations');
        Schema::dropIfExists('tasks');

        Schema::dropIfExists('process_translations');
        Schema::dropIfExists('processes');

        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('categories');

        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');

        Schema::dropIfExists('statuses');
        Schema::dropIfExists('case_tasks');
        Schema::dropIfExists('case_task_requirements');
        Schema::dropIfExists('case_geometries');
    }
};
