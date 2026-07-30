<?php

namespace App\Services\PageBuilder\GlobalWidget;

use App\Models\Element;
use App\Models\GlobalWidget;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\WidgetManager;
use Illuminate\Support\Facades\Cache;

class GlobalWidgetService
{
    protected Renderer $renderer;
    protected WidgetManager $widgetManager;

    public function __construct(Renderer $renderer, WidgetManager $widgetManager)
    {
        $this->renderer = $renderer;
        $this->widgetManager = $widgetManager;
    }

    public function getAll(): array
    {
        return Cache::remember('global_widgets_all', 300, function () {
            return GlobalWidget::orderBy('title')->with('user')->get()->all();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('global_widgets_all');
    }

    public function create(array $data): GlobalWidget
    {
        $this->clearCache();

        $data['user_id'] ??= auth()->id();

        return GlobalWidget::create([
            'user_id' => $data['user_id'],
            'title' => $data['title'],
            'type' => $data['type'],
            'settings' => $data['settings'] ?? [],
            'content' => $data['content'] ?? [],
            'styles' => $data['styles'] ?? [],
            'status' => $data['status'] ?? 'draft',
            'description' => $data['description'] ?? null,
        ]);
    }

    public function update(GlobalWidget $globalWidget, array $data): GlobalWidget
    {
        $this->clearCache();

        $globalWidget->update([
            'title' => $data['title'] ?? $globalWidget->title,
            'type' => $data['type'] ?? $globalWidget->type,
            'settings' => $data['settings'] ?? $globalWidget->settings,
            'content' => $data['content'] ?? $globalWidget->content,
            'styles' => $data['styles'] ?? $globalWidget->styles,
            'status' => $data['status'] ?? $globalWidget->status,
            'description' => $data['description'] ?? $globalWidget->description,
        ]);

        return $globalWidget->fresh();
    }

    public function delete(GlobalWidget $globalWidget): void
    {
        $this->clearCache();
        $globalWidget->delete();
    }

    public function renderWidget(GlobalWidget $globalWidget, array $options = []): string
    {
        $widget = $this->widgetManager->getWidget($globalWidget->type);

        if (!$widget) {
            return "<!-- Unknown global widget type: {$globalWidget->type} -->";
        }

        $settings = array_merge($globalWidget->settings ?? [], $globalWidget->styles ?? []);
        $html = $widget->render(
            $settings,
            $globalWidget->content ?? [],
            $globalWidget->styles ?? []
        );

        return $this->renderer->processEmbeds($html);
    }

    public function syncToElement(GlobalWidget $globalWidget, Element $element): Element
    {
        $element->update([
            'settings' => $globalWidget->settings,
            'content' => $globalWidget->content,
            'styles' => $globalWidget->styles,
        ]);

        return $element->fresh();
    }
}
