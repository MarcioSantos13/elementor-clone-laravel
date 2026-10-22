<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'is_default']);
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index('project_id');
        });

        Schema::table('theme_templates', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index('project_id');
        });

        Schema::table('popups', function (Blueprint $table) {
            $table->foreignId('project_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index('project_id');
        });

        $this->migrateExistingRecords();
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('theme_templates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::table('popups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_id');
        });

        Schema::dropIfExists('projects');
    }

    protected function migrateExistingRecords(): void
    {
        $userIds = collect()
            ->merge(\Illuminate\Support\Facades\DB::table('pages')->distinct()->pluck('user_id'))
            ->merge(\Illuminate\Support\Facades\DB::table('theme_templates')->distinct()->pluck('user_id'))
            ->merge(\Illuminate\Support\Facades\DB::table('popups')->distinct()->pluck('user_id'))
            ->unique()
            ->values();

        $now = now();

        foreach ($userIds as $userId) {
            $projectId = \Illuminate\Support\Facades\DB::table('projects')
                ->where('user_id', $userId)
                ->where('is_default', true)
                ->value('id');

            if (!$projectId) {
                $projectId = \Illuminate\Support\Facades\DB::table('projects')->insertGetId([
                    'user_id' => $userId,
                    'name' => 'Meus Projetos',
                    'slug' => 'meus-projetos',
                    'description' => null,
                    'settings' => json_encode([]),
                    'is_default' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            \Illuminate\Support\Facades\DB::table('pages')->where('user_id', $userId)->whereNull('project_id')->update(['project_id' => $projectId]);
            \Illuminate\Support\Facades\DB::table('theme_templates')->where('user_id', $userId)->whereNull('project_id')->update(['project_id' => $projectId]);
            \Illuminate\Support\Facades\DB::table('popups')->where('user_id', $userId)->whereNull('project_id')->update(['project_id' => $projectId]);
        }
    }
};
