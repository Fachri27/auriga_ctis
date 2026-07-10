<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            // null case_id = berlangganan kasus terbaru (semua kasus baru)
            $table->foreignId('case_id')->nullable()->constrained('cases')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['email', 'case_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_subscriptions');
    }
};
