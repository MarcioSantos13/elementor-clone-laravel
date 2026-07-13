<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\Element;
use App\Models\Revision;
use App\Contracts\PageBuilder\WidgetInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class PageBuilderService
{
    protected WidgetManager $widgetManager;
    protected ElementManager $elementManager;
    protected Renderer $renderer;
    protected array $config;

    public function __construct(
        WidgetManager $widgetManager,
        ElementManager $elementManager,
        Renderer $renderer
    ) {
        $this->widgetManager = $widgetManager;
        $this->elementManager = $elementManager;
        $this->renderer = $renderer;
        $this->config = config('page-builder');
    }

    /**
     * Criar uma nova pÃ¡gina
     */
    public function createPage(array $data): Page
    {
        DB::beginTransaction();

        try {
            $page = new Page();
            $page->fill($data);
            $page->user_id = auth()->id();
            $page->status = $data['status'] ?? 'draft';
            $page->slug = $this->generateUniqueSlug($data['title']);
            $page->save();

            // Criar revisÃ£o inicial
            $this->createRevision($page, 'Initial creation');

            DB::commit();

            Event::dispatch('page.created', $page);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function generateUniqueSlug(string $title, ?int $excludeId = null): string
    {
        $slug = Str::slug($title);
        $base = $slug;
        $counter = 1;

        while (Page::withTrashed()->where('slug', $slug)->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Atualizar pÃ¡gina
     */
    public function updatePage(Page $page, array $data): Page
    {
        DB::beginTransaction();

        try {
            // Salvar conteÃºdo atual antes de modificar
            $oldContent = $page->content;

            $page->fill($data);

            if (isset($data['content'])) {
                $page->content = $this->sanitizeContent($data['content']);
            }

            $page->save();

            // Criar revisÃ£o se conteÃºdo mudou
            if ($oldContent != $page->content) {
                $this->createRevision($page, 'Content updated');
            }

            DB::commit();

            // Limpar cache
            $this->clearPageCache($page);

            Event::dispatch('page.updated', $page);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Renderizar pÃ¡gina
     */
    public function renderPage(Page $page, array $options = []): string
    {
        $cacheKey = "page.{$page->id}.render." . md5(json_encode($options));

        if ($this->config['template_cache']['enabled'] ?? false) {
            return Cache::remember($cacheKey, $this->config['template_cache']['ttl'] ?? 3600, function () use ($page, $options) {
                return $this->renderer->render($page, $options);
            });
        }

        return $this->renderer->render($page, $options);
    }

    /**
     * Adicionar elemento Ã  pÃ¡gina
     */
    public function addElement(Page $page, string $widgetType, array $settings = []): Element
    {
        $widget = $this->widgetManager->getWidget($widgetType);

        if (!$widget) {
            throw new \InvalidArgumentException("Widget {$widgetType} not found");
        }

        DB::beginTransaction();

        try {
            $element = new Element();
            $element->page_id = $page->id;
            $element->type = $widgetType;
            $element->name = $widget->getLabel();
            $element->settings = array_merge($widget->getDefaultSettings(), $settings);
            $element->content = [];
            $element->styles = [];
            $element->order = ((int) ($page->elements()->max('order') ?? -1)) + 1;
            $element->save();

            DB::commit();

            // Limpar cache
            $this->clearPageCache($page);

            Event::dispatch('element.added', $element);

            return $element;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Atualizar elemento
     */
    public function updateElement(Element $element, array $data): Element
    {
        DB::beginTransaction();

        try {
            $element->fill($data);

            // Sanitizar settings
            if (isset($data['settings'])) {
                $element->settings = $this->sanitizeSettings($data['settings']);
            }

            $element->save();

            DB::commit();

            // Limpar cache da pÃ¡gina
            $this->clearPageCache($element->page);

            Event::dispatch('element.updated', $element);

            return $element;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Remover elemento
     */
    public function removeElement(Element $element): bool
    {
        DB::beginTransaction();

        try {
            $pageId = $element->page_id;

            // Remover elementos filhos
            if ($element->children()->exists()) {
                $element->children()->delete();
            }

            $element->delete();

            DB::commit();

            // Limpar cache
            $this->clearPageCache(Page::find($pageId));

            Event::dispatch('element.removed', $element);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Duplicar elemento
     */
    public function duplicateElement(Element $element): Element
    {
        DB::beginTransaction();

        try {
            $newElement = $element->replicate();
            $newElement->uuid = (string) Str::uuid();
            $newElement->name = $element->name . ' (copy)';
            $newElement->order = $element->page->elements()->max('order') + 1;
            $newElement->save();

            // Duplicar elementos filhos
            foreach ($element->children as $child) {
                $newChild = $child->replicate();
                $newChild->uuid = (string) Str::uuid();
                $newChild->parent_id = $newElement->id;
                $newChild->order = $child->order;
                $newChild->save();
            }

            DB::commit();

            // Limpar cache
            $this->clearPageCache($element->page);

            Event::dispatch('element.duplicated', $newElement);

            return $newElement;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Criar revisÃ£o
     */
    protected function createRevision(Page $page, string $label = null): Revision
    {
        $revision = new Revision();
        $revision->page_id = $page->id;
        $revision->user_id = auth()->id();
        $revision->content = $page->content;
        $revision->settings = $page->settings;
        $revision->meta_data = $page->meta_data;
        $revision->version = $this->generateVersionNumber($page);
        $revision->label = $label ?? 'Auto-save';
        $revision->type = 'manual';
        $revision->save();

        // Limitar nÃºmero de revisÃµes
        $limit = $this->config['revisions']['max_per_page'] ?? 50;
        if ($limit) {
            $page->revisions()
                ->orderBy('created_at', 'desc')
                ->skip($limit)
                ->take(100)
                ->delete();
        }

        return $revision;
    }

    /**
     * Restaurar revisÃ£o
     */
    public function restoreRevision(Page $page, Revision $revision): Page
    {
        DB::beginTransaction();

        try {
            $page->content = $revision->content;
            $page->settings = $revision->settings;
            $page->meta_data = $revision->meta_data;
            $page->save();

            $this->createRevision($page, 'Restored from revision #' . $revision->id);

            DB::commit();

            // Limpar cache
            $this->clearPageCache($page);

            Event::dispatch('page.restored', ['page' => $page, 'revision' => $revision]);

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Gerar nÃºmero de versÃ£o
     */
    protected function generateVersionNumber(Page $page): string
    {
        $lastRevision = $page->revisions()->latest()->first();

        if (!$lastRevision) {
            return '1.0.0';
        }

        $parts = explode('.', $lastRevision->version);
        $parts[2] = ($parts[2] ?? 0) + 1;

        return implode('.', $parts);
    }

    /**
     * Sanitizar conteÃºdo
     */
    protected function sanitizeContent(array $content): array
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return $this->sanitizeContent($item);
            }

            if (is_string($item) && $this->containsHtml($item)) {
                $allowed = '<p><br><strong><em><u><s><sup><sub><mark><ul><ol><li><dl><dt><dd><a><img><div><span><h1><h2><h3><h4><h5><h6><blockquote><pre><code><hr><table><thead><tbody><tr><th><td><figure><figcaption><video><source><iframe>';
                $clean = strip_tags($item, $allowed);

                $clean = preg_replace_callback('/<a\s[^>]*href\s*=\s*["\']([^"\']*)["\'][^>]*>/i', function ($m) {
                    $url = filter_var($m[1], FILTER_SANITIZE_URL);
                    if ($url && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || str_starts_with($url, '/'))) {
                        return str_replace($m[1], $url, $m[0]);
                    }
                    return str_replace($m[1], '#', $m[0]);
                }, $clean);

                $clean = preg_replace_callback('/<img\s[^>]*src\s*=\s*["\']([^"\']*)["\'][^>]*>/i', function ($m) {
                    $url = filter_var($m[1], FILTER_SANITIZE_URL);
                    if ($url && (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'data:image/'))) {
                        return str_replace($m[1], $url, $m[0]);
                    }
                    return str_replace($m[1], '', $m[0]);
                }, $clean);

                $clean = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $clean);
                $clean = preg_replace("/\s+on\w+\s*=\s*'[^']*'/i", '', $clean);
                $clean = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/i', '', $clean);
                $clean = preg_replace('/<([a-z]+)[^>]*javascript\s*:/i', '<$1', $clean);

                return $clean;
            }

            if (is_string($item)) {
                return htmlspecialchars($item, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            }

            return $item;
        }, $content);
    }

    /**
     * Sanitizar settings com base no tipo esperado
     */
    protected function sanitizeSettings(array $settings): array
    {
        $sanitized = [];

        foreach ($settings as $key => $value) {
            if (is_string($value)) {
                if (str_contains($key, 'color') || str_contains($key, '_color')) {
                    $sanitized[$key] = preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{4}|[0-9a-fA-F]{6}|[0-9a-fA-F]{8})$/', $value) ? $value : '#000000';
                } elseif (str_contains($key, 'url') || str_contains($key, 'link') || str_contains($key, 'src')) {
                    $sanitized[$key] = filter_var($value, FILTER_SANITIZE_URL);
                } elseif (str_contains($key, 'content') || str_contains($key, 'html') || $key === 'content') {
                    $allowed = '<p><br><strong><em><u><s><sup><sub><mark><ul><ol><li><dl><dt><dd><a><img><div><span><h1><h2><h3><h4><h5><h6><blockquote><pre><code><hr><table><thead><tbody><tr><th><td><figure><figcaption><video><source><iframe>';
                    $sanitized[$key] = strip_tags($value, $allowed);
                } elseif (in_array($key, ['css_classes', 'css_id', 'name', 'title', 'alt', 'text', 'label'])) {
                    $sanitized[$key] = strip_tags($value);
                } else {
                    $sanitized[$key] = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
                }
            } elseif (is_numeric($value)) {
                $sanitized[$key] = is_float($value) ? (float) $value : (int) $value;
            } elseif (is_bool($value)) {
                $sanitized[$key] = $value;
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeSettings($value);
            } elseif (is_null($value)) {
                $sanitized[$key] = null;
            } else {
                $sanitized[$key] = htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
            }
        }

        return $sanitized;
    }

    /**
     * Verificar se contÃ©m HTML
     */
    protected function containsHtml(string $string): bool
    {
        return $string !== strip_tags($string);
    }

    /**
     * Limpar cache da pÃ¡gina
     */
    protected function clearPageCache(Page $page): void
    {
        Cache::forget("page.{$page->id}.render." . md5(json_encode(['with_container' => true])));
        Cache::forget("page.{$page->id}.render." . md5(json_encode(['with_container' => false])));
        Cache::forget("page.{$page->id}.render." . md5(json_encode([])));
        Cache::forget("page.{$page->id}.json");
    }

    /**
     * Exportar pÃ¡gina para JSON
     */
    public function exportPage(Page $page, array $options = []): array
    {
        return [
            'version' => '1.0.0',
            'title' => $page->title,
            'slug' => $page->slug,
            'settings' => $page->settings,
            'meta_data' => $page->meta_data,
            'elements' => $this->exportElements($page->elements),
            'exported_at' => now()->toIso8601String(),
            'exported_by' => auth()->user()?->name ?? 'system',
        ];
    }

    /**
     * Exportar elementos recursivamente
     */
    protected function exportElements($elements): array
    {
        $result = [];

        foreach ($elements as $element) {
            $data = [
                'id' => $element->uuid,
                'type' => $element->type,
                'name' => $element->name,
                'settings' => $element->settings,
                'content' => $element->content,
                'styles' => $element->styles,
                'responsive_settings' => $element->responsive_settings,
                'animation' => $element->animation,
                'effects' => $element->effects,
                'column_size' => $element->column_size,
            ];

            if ($element->children->isNotEmpty()) {
                $data['children'] = $this->exportElements($element->children);
            }

            $result[] = $data;
        }

        return $result;
    }

    /**
     * Importar pÃ¡gina de JSON
     */
    public function importPage(array $data, Page $page = null): Page
    {
        DB::beginTransaction();

        try {
            if (!$page) {
                $page = new Page();
                $page->user_id = auth()->id();
            }

            $page->title = $data['title'] ?? 'Imported Page';
            $page->slug = Str::slug($page->title);
            $page->settings = $data['settings'] ?? [];
            $page->meta_data = $data['meta_data'] ?? [];
            $page->status = 'draft';
            $page->save();

            // Importar elementos
            if (isset($data['elements'])) {
                $this->importElements($page, $data['elements']);
            }

            DB::commit();

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Importar elementos recursivamente
     */
    protected function importElements(Page $page, array $elements, int $parentId = null): void
    {
        foreach ($elements as $index => $elementData) {
            $widget = $this->widgetManager->getWidget($elementData['type']);

            if (!$widget) {
                continue;
            }

            $element = new Element();
            $element->page_id = $page->id;
            $element->type = $elementData['type'];
            $element->name = $elementData['name'] ?? $widget->getLabel();
            $element->uuid = $elementData['id'] ?? (string) Str::uuid();
            $element->settings = $elementData['settings'] ?? $widget->getDefaultSettings();
            $element->content = $elementData['content'] ?? [];
            $element->styles = $elementData['styles'] ?? [];
            $element->responsive_settings = $elementData['responsive_settings'] ?? [];
            $element->animation = $elementData['animation'] ?? null;
            $element->effects = $elementData['effects'] ?? null;
            $element->column_size = $elementData['column_size'] ?? 'col-12';
            $element->order = $index;
            $element->parent_id = $parentId;
            $element->save();

            // Importar filhos recursivamente
            if (isset($elementData['children'])) {
                $this->importElements($page, $elementData['children'], $element->id);
            }
        }
    }
}
