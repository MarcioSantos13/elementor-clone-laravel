<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\User;
use App\Services\PageBuilder\Project\ProjectTemplateService;
use Illuminate\Database\Seeder;

class ExampleProjectSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'test@example.com')->first()
            ?? User::orderBy('id')->first();

        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $exists = Project::where('user_id', $user->id)
            ->where('name', ProjectTemplateService::EXAMPLE_NAME)
            ->exists();

        if ($exists) {
            $this->command?->info('Projeto de exemplo já existe.');

            return;
        }

        $project = app(ProjectTemplateService::class)->createExample($user);

        $this->command?->info("Projeto de exemplo criado: {$project->name}");
    }
}
