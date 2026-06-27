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
            $page->slug = Str::slug($data['title']);
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

        // Verificar cache
        if ($this->config['templates']['cache'] ?? false) {
            return Cache::remember($cacheKey, $this->config['templates']['cache_ttl'] ?? 3600, function () use ($page, $options) {
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
            $element->order = $page->elements()->max('order') + 1;
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
        if ($this->config['revisions']['max_per_page'] ?? 50) {
            $limit = $this->config['revisions']['max_per_page'];
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
        // Implementar sanitizaÃ§Ã£o profunda
        return array_map(function ($item) {
            if (is_array($item)) {
                return $this->sanitizeContent($item);
            }

            // Sanitizar HTML se necessÃ¡rio
            if (is_string($item) && $this->containsHtml($item)) {
                return strip_tags($item, '<p><br><strong><em><u><ul><ol><li><a><img><div><span>');
            }

            return $item;
        }, $content);
    }

    /**
     * Sanitizar settings
     */
    protected function sanitizeSettings(array $settings): array
    {
        // Sanitizar cada setting baseado no tipo
        return $settings;
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
        Cache::forget("page.{$page->id}.render");
        Cache::forget("page.{$page->id}.json");

        // Limpar cache de todos os formatos
        Cache::forget("page.{$page->id}.render.*");
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
