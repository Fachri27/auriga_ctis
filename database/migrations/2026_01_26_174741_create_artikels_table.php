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
        Schema::create('artikels', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->enum('type', ['internal', 'eksternal'])->default('internal');
            $table->string('image')->nullable();
            $table->date('published_at')->nullable();
            $table->enum('status', ['draft', 'active', 'inactive'])->default('draft');
            $table->string('link')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete(action: 'cascade');
            $table->foreignId('category_id')
            ->nullable()
            ->constrained('categories')
            ->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artikels');
    }
};
