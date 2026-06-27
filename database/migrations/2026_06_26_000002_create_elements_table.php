<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('elements')->cascadeOnDelete();
            $table->uuid('uuid')->unique();
            $table->string('type');
            $table->string('name');
            $table->integer('order')->default(0);
            $table->json('settings')->nullable();
            $table->json('content')->nullable();
            $table->json('styles')->nullable();
            $table->json('responsive_settings')->nullable();
            $table->json('animation')->nullable();
            $table->json('effects')->nullable();
            $table->string('column_size')->default('col-12');
            $table->json('css_classes')->nullable();
            $table->string('css_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['page_id', 'order']);
            $table->index('type');
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elements');
    }
};
