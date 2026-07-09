<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RevisionFactory extends Factory
{
    protected $model = Revision::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'user_id' => User::factory(),
            'version' => '1.0.' . fake()->numberBetween(0, 99),
            'label' => fake()->sentence(),
            'type' => 'manual',
            'content' => [],
            'settings' => [],
            'meta_data' => [],
            'diff' => [],
        ];
    }

    public function autoSave(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'auto_save',
            'label' => 'Auto-save',
        ]);
    }
}
