<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('about_page_id')->constrained('about_pages')->cascadeOnDelete();
            $table->string('locale', 5);
            $table->string('title');
            $table->longText('content')->nullable();
            $table->longText('vision')->nullable();
            $table->longText('mission')->nullable();
            $table->timestamps();

            $table->unique(['about_page_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_page_translations');
    }
};
