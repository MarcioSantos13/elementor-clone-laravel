<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('version', 20);
            $table->string('label')->nullable();
            $table->string('type')->default('manual');
            $table->longText('content')->nullable();
            $table->json('settings')->nullable();
            $table->json('meta_data')->nullable();
            $table->json('diff')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'version']);
            $table->index(['page_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisions');
    }
};
