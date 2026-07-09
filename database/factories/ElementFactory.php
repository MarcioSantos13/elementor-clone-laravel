<?php

namespace Database\Factories;

use App\Models\Element;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ElementFactory extends Factory
{
    protected $model = Element::class;

    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'parent_id' => null,
            'uuid' => (string) Str::uuid(),
            'type' => 'heading',
            'name' => 'Heading',
            'order' => 0,
            'settings' => ['title' => 'Sample Heading', 'tag' => 'h2', 'size' => 'medium', 'color' => '#333333', 'alignment' => 'left'],
            'content' => [],
            'styles' => [],
            'responsive_settings' => [],
            'animation' => null,
            'effects' => null,
            'column_size' => 'col-12',
            'css_classes' => [],
            'css_id' => null,
        ];
    }

    public function section(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'section',
            'name' => 'Section',
            'settings' => ['layout' => 'full_width', 'background_color' => '#ffffff', 'padding_top' => '0px', 'padding_bottom' => '0px'],
        ]);
    }

    public function column(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'column',
            'name' => 'Column',
            'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'top', 'text_align' => 'left'],
        ]);
    }
}
