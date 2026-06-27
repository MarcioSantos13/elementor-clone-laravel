<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->longText('content')->nullable();
            $table->json('settings')->nullable();
            $table->json('meta_data')->nullable();
            $table->string('template')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'slug']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
