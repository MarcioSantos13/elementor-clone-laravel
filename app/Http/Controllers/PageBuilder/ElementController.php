<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\WidgetManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ElementController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected Renderer $renderer;
    protected WidgetManager $widgetManager;

    public function __construct(PageBuilderService $pageBuilder, Renderer $renderer, WidgetManager $widgetManager)
    {
        $this->pageBuilder = $pageBuilder;
        $this->renderer = $renderer;
        $this->widgetManager = $widgetManager;
    }

    public function index(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $elements = $page->allElements()->get();

        return response()->json([
            'elements' => $this->buildTree($elements),
        ]);
    }

    public function store(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'type' => 'required|string',
            'settings' => 'nullable|array',
            'parent_id' => 'nullable|exists:elements,id',
        ]);

        $element = $this->pageBuilder->addElement($page, $validated['type'], $validated['settings'] ?? []);

        if (!empty($validated['parent_id'])) {
            $element->parent_id = $validated['parent_id'];
            $element->save();
        }

        return response()->json([
            'message' => 'Element added successfully',
            'element' => $element->load('children'),
        ], 201);
    }

    public function show(Element $element): JsonResponse
    {
        $this->authorize('view', $element->page);
        return response()->json([
            'element' => $element->load('children'),
        ]);
    }

    public function update(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'content' => 'nullable|array',
            'styles' => 'nullable|array',
            'responsive_settings' => 'nullable|array',
            'animation' => 'nullable|array',
            'effects' => 'nullable|array',
            'column_size' => 'nullable|string',
            'css_classes' => 'nullable|array',
            'css_id' => 'nullable|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $element = $this->pageBuilder->updateElement($element, $validated);

        return response()->json([
            'message' => 'Element updated successfully',
            'element' => $element->load('children'),
        ]);
    }

    public function destroy(Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $this->pageBuilder->removeElement($element);

        return response()->json([
            'message' => 'Element deleted successfully',
        ]);
    }

    public function duplicate(Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $newElement = $this->pageBuilder->duplicateElement($element);

        return response()->json([
            'message' => 'Element duplicated successfully',
            'element' => $newElement->load('children'),
        ]);
    }

    public function reorder(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:elements,id',
            'order.*.children' => 'nullable|array',
            'order.*.children.*' => 'integer|exists:elements,id',
        ]);

        $order = $validated['order'];

        foreach ($order as $index => $item) {
            Element::where('id', $item['id'])
                ->where('page_id', $page->id)
                ->update(['order' => $index, 'parent_id' => null]);

            if (isset($item['children'])) {
                foreach ($item['children'] as $childIndex => $childId) {
                    Element::where('id', $childId)
                        ->where('page_id', $page->id)
                        ->update(['order' => $childIndex, 'parent_id' => $item['id']]);
                }
            }
        }

        return response()->json([
            'message' => 'Elements reordered successfully',
        ]);
    }

    public function move(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'parent_id' => 'nullable|exists:elements,id',
            'order' => 'required|integer|min:0',
        ]);

        $element->parent_id = $validated['parent_id'] ?? null;
        $element->order = $validated['order'];
        $element->save();

        return response()->json([
            'message' => 'Element moved successfully',
            'element' => $element->fresh()->load('children'),
        ]);
    }

    public function updateSettings(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $element->settings = array_merge($element->settings ?? [], $validated['settings']);
        $element->save();
        $element->page->touch();

        return response()->json([
            'message' => 'Element settings updated successfully',
            'element' => $element->fresh(),
        ]);
    }

    public function updateStyles(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'styles' => 'required|array',
        ]);

        $element->styles = array_merge($element->styles ?? [], $validated['styles']);
        $element->save();
        $element->page->touch();

        return response()->json([
            'message' => 'Element styles updated successfully',
            'element' => $element->fresh(),
        ]);
    }

    public function renderElement(Element $element): JsonResponse
    {
        $this->authorize('view', $element->page);
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return response()->json(['html' => '<!-- Unknown widget -->', 'element_id' => $element->id]);
        }

        $childrenHtml = '';
        if ($element->children->isNotEmpty()) {
            foreach ($element->children as $child) {
                $childrenHtml .= $this->renderer->renderSingleElement($child);
            }
        }

        $innerHtml = $widget->renderEditor(
            $element->settings ?? [],
            array_merge($element->content ?? [], ['children' => $childrenHtml]),
            $element->styles ?? []
        );

        return response()->json([
            'html' => $innerHtml,
            'element_id' => $element->id,
        ]);
    }

    public function controls(Element $element): JsonResponse
    {
        $this->authorize('view', $element->page);
        $widgetControls = $this->renderer->getWidgetControls($element->type);

        if (!$widgetControls) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        return response()->json([
            'widget' => $widgetControls,
            'element' => $element->load('children'),
        ]);
    }

    public function widgetControls(string $type): JsonResponse
    {
        $widgetControls = $this->renderer->getWidgetControls($type);

        if (!$widgetControls) {
            return response()->json(['error' => 'Widget not found'], 404);
        }

        return response()->json($widgetControls);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $file = $request->file('image');
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('page-builder', $filename, 'public');

        if (!$path) {
            return response()->json(['error' => 'Falha ao fazer upload'], 500);
        }

        $url = parse_url(Storage::disk('public')->url($path), PHP_URL_PATH);

        return response()->json([
            'url' => $url,
            'filename' => $filename,
        ]);
    }

    protected function buildTree($elements): array
    {
        $byParent = [];
        foreach ($elements as $e) {
            $byParent[(int) ($e->parent_id ?? 0)][] = $e;
        }

        $build = function ($parentId) use ($byParent, &$build) {
            $result = [];
            foreach ($byParent[$parentId] ?? [] as $element) {
                $node = [
                    'id' => $element->id,
                    'uuid' => $element->uuid,
                    'type' => $element->type,
                    'name' => $element->name,
                    'order' => $element->order,
                    'settings' => $element->settings,
                    'content' => $element->content,
                    'styles' => $element->styles,
                    'column_size' => $element->column_size,
                ];
                $children = $build($element->id);
                if ($children) {
                    $node['children'] = $children;
                }
                $result[] = $node;
            }
            return $result;
        };

        return $build(0);
    }
}
