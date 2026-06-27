<?php

namespace App\Services\PageBuilder\Core;

use App\Contracts\PageBuilder\WidgetInterface;
use Illuminate\Support\Collection;

class WidgetManager
{
    protected Collection $widgets;
    protected array $categories = [];

    public function __construct()
    {
        $this->widgets = collect();
        $this->categories = [];
    }

    public function register(WidgetInterface $widget): void
    {
        $this->widgets->put($widget->getType(), $widget);

        foreach ($widget->getCategories() as $category) {
            $this->categories[$category][] = $widget->getType();
        }
    }

    public function registerFromConfig(): void
    {
        $widgets = config('page-builder.widgets', []);

        foreach ($widgets as $widgetClass) {
            if (class_exists($widgetClass)) {
                $this->register(app($widgetClass));
            }
        }
    }

    public function getWidget(string $type): ?WidgetInterface
    {
        return $this->widgets->get($type);
    }

    public function getWidgets(): Collection
    {
        return $this->widgets;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getWidgetsByCategory(string $category): Collection
    {
        $types = $this->categories[$category] ?? [];

        return $this->widgets->only($types);
    }

    public function getCategoriesWithWidgets(): array
    {
        $result = [];

        foreach ($this->categories as $category => $types) {
            $result[$category] = $this->widgets->only($types)->values();
        }

        return $result;
    }

    public function hasWidget(string $type): bool
    {
        return $this->widgets->has($type);
    }

    public function getWidgetCount(): int
    {
        return $this->widgets->count();
    }

    public function removeWidget(string $type): void
    {
        $this->widgets->forget($type);

        foreach ($this->categories as $category => $types) {
            $this->categories[$category] = array_filter($types, fn($t) => $t !== $type);
        }
    }
}
