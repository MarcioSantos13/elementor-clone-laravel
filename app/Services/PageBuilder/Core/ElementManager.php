<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Element;
use App\Models\Page;
use App\Contracts\PageBuilder\WidgetInterface;
use Illuminate\Support\Collection;

class ElementManager
{
    protected WidgetManager $widgetManager;

    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    public function create(Page $page, string $widgetType, array $settings = [], ?int $parentId = null): Element
    {
        $widget = $this->widgetManager->getWidget($widgetType);

        if (!$widget) {
            throw new \InvalidArgumentException("Widget type '{$widgetType}' not found");
        }

        $element = new Element();
        $element->page_id = $page->id;
        $element->type = $widgetType;
        $element->name = $widget->getLabel();
        $element->settings = array_merge($widget->getDefaultSettings(), $settings);
        $element->content = [];
        $element->styles = [];
        $element->order = $this->getNextOrder($page, $parentId);

        if ($parentId) {
            $element->parent_id = $parentId;
        }

        $element->save();

        return $element;
    }

    public function update(Element $element, array $data): Element
    {
        $element->fill($data);

        if (isset($data['settings'])) {
            $widget = $this->widgetManager->getWidget($element->type);
            if ($widget) {
                $element->settings = $widget->validateSettings($data['settings']);
            }
        }

        $element->save();

        return $element;
    }

    public function delete(Element $element): bool
    {
        if ($element->children()->exists()) {
            $element->children()->delete();
        }

        return (bool) $element->delete();
    }

    public function duplicate(Element $element): Element
    {
        $newElement = $element->replicate();
        $newElement->uuid = (string) \Illuminate\Support\Str::uuid();
        $newElement->name = $element->name . ' (copy)';
        $newElement->order = $this->getNextOrder($element->page, $element->parent_id);
        $newElement->save();

        foreach ($element->children as $child) {
            $this->duplicateChild($child, $newElement->id);
        }

        return $newElement;
    }

    protected function duplicateChild(Element $element, int $newParentId): void
    {
        $newElement = $element->replicate();
        $newElement->uuid = (string) \Illuminate\Support\Str::uuid();
        $newElement->parent_id = $newParentId;
        $newElement->order = $element->order;
        $newElement->save();

        foreach ($element->children as $child) {
            $this->duplicateChild($child, $newElement->id);
        }
    }

    public function reorder(Page $page, array $order): void
    {
        foreach ($order as $index => $item) {
            if (isset($item['id'])) {
                Element::where('id', $item['id'])
                    ->where('page_id', $page->id)
                    ->update(['order' => $index]);

                if (isset($item['children'])) {
                    $this->reorderChildren($item['id'], $item['children']);
                }
            }
        }
    }

    protected function reorderChildren(int $parentId, array $children): void
    {
        foreach ($children as $index => $childId) {
            Element::where('id', $childId)
                ->where('parent_id', $parentId)
                ->update(['order' => $index]);
        }
    }

    public function getElementsTree(Page $page): Collection
    {
        return $page->elements()->with('children')->get();
    }

    public function getFlatElements(Page $page): Collection
    {
        return $page->allElements()->orderBy('order')->get();
    }

    public function moveElement(Element $element, ?int $newParentId, int $newOrder): void
    {
        $element->parent_id = $newParentId;
        $element->order = $newOrder;
        $element->save();
    }

    protected function getNextOrder(Page $page, ?int $parentId = null): int
    {
        $query = Element::where('page_id', $page->id);

        if ($parentId) {
            $query->where('parent_id', $parentId);
        } else {
            $query->whereNull('parent_id');
        }

        return ($query->max('order') ?? -1) + 1;
    }

    public function getWidgetForElement(Element $element): ?WidgetInterface
    {
        return $this->widgetManager->getWidget($element->type);
    }
}
