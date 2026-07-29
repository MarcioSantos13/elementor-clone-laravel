<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fonts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('family');
            $table->string('weight')->default('400');
            $table->string('style')->default('normal');
            $table->string('file_ttf')->nullable();
            $table->string('file_woff')->nullable();
            $table->string('file_woff2')->nullable();
            $table->string('url_ttf')->nullable();
            $table->string('url_woff')->nullable();
            $table->string('url_woff2')->nullable();
            $table->string('font_display')->default('swap');
            $table->boolean('is_global')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('family');
            $table->index('is_global');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_fonts');
    }
};
