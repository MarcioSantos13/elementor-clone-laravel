<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\WidgetManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ElementController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected ElementManager $elementManager;
    protected Renderer $renderer;
    protected WidgetManager $widgetManager;

    public function __construct(PageBuilderService $pageBuilder, ElementManager $elementManager, Renderer $renderer, WidgetManager $widgetManager)
    {
        $this->pageBuilder = $pageBuilder;
        $this->elementManager = $elementManager;
        $this->renderer = $renderer;
        $this->widgetManager = $widgetManager;
    }

    public function index(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $elements = $page->allElements()->get();

        return response()->json([
            'elements' => $this->elementManager->buildTree($elements),
        ]);
    }

    public function store(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'type' => 'required|string',
            'settings' => 'nullable|array',
            'parent_id' => 'nullable|integer|exists:elements,id',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = Element::find($parentId);
            $parentWidget = $parent ? $this->widgetManager->getWidget($parent->type) : null;
            if (!$parentWidget || !$parentWidget->isContainer()) {
                $parentId = null;
            }
        }

        $element = $this->elementManager->create($page, $validated['type'], $validated['settings'] ?? [], $parentId);

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
        ]);

        $order = $validated['order'];
        $this->applyOrder($order, $page->id, null);

        return response()->json([
            'message' => 'Elements reordered successfully',
        ]);
    }

    private function applyOrder(array $items, int $pageId, ?int $parentId): void
    {
        foreach ($items as $index => $item) {
            Element::where('id', $item['id'])
                ->where('page_id', $pageId)
                ->update(['order' => $index, 'parent_id' => $parentId]);

            if (!empty($item['children']) && is_array($item['children'])) {
                if (isset($item['children'][0]) && is_int($item['children'][0])) {
                    foreach ($item['children'] as $childIndex => $childId) {
                        Element::where('id', $childId)
                            ->where('page_id', $pageId)
                            ->update(['order' => $childIndex, 'parent_id' => $item['id']]);
                    }
                } else {
                    $this->applyOrder($item['children'], $pageId, $item['id']);
                }
            }
        }
    }

    public function move(Request $request, Element $element): JsonResponse
    {
        $this->authorize('update', $element->page);
        $validated = $request->validate([
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('elements', 'id')->where('page_id', $element->page_id),
            ],
            'order' => 'required|integer|min:0',
        ]);

        $parentId = $validated['parent_id'] ?? null;

        if ($parentId) {
            $parent = Element::find($parentId);
            $parentWidget = $parent ? $this->widgetManager->getWidget($parent->type) : null;
            if (!$parentWidget || !$parentWidget->isContainer()) {
                return response()->json(['error' => 'Parent element must be a container'], 422);
            }

            $descendantIds = $this->getAllDescendantIds($element->id);
            if (in_array($parentId, $descendantIds)) {
                return response()->json(['error' => 'Cannot move element into its own descendant'], 422);
            }
        }

        $element->parent_id = $parentId;
        $element->order = $validated['order'];
        $element->save();

        return response()->json([
            'message' => 'Element moved successfully',
            'element' => $element->fresh()->load('children'),
        ]);
    }

    private function getAllDescendantIds(int $elementId): array
    {
        $ids = [];
        $children = Element::where('parent_id', $elementId)->pluck('id');
        foreach ($children as $childId) {
            $ids[] = $childId;
            $ids = array_merge($ids, $this->getAllDescendantIds($childId));
        }
        return $ids;
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

        try {
            $childrenHtml = '';
            if ($element->children->isNotEmpty()) {
                foreach ($element->children as $child) {
                    $childrenHtml .= $this->renderer->renderSingleElement($child);
                }
            }

            $innerHtml = $widget->renderEditor(
                array_merge($element->settings ?? [], $element->styles ?? []),
                array_merge($element->content ?? [], ['children' => $childrenHtml]),
                $element->styles ?? []
            );
        } catch (\Throwable $e) {
            $innerHtml = '<!-- Render error: ' . htmlspecialchars($e->getMessage()) . ' -->';
        }

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:10240',
        ]);

        $file = $request->file('image');

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        $allowedImageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        if (!in_array($mimeType, $allowedImageMimes)) {
            return response()->json(['error' => 'Tipo de arquivo inválido.'], 422);
        }

        $extension = match ($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            default => throw new \RuntimeException('Tipo não suportado'),
        };

        $filename = Str::uuid() . '.' . $extension;
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

    public function uploadVideo(Request $request): JsonResponse
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,webm,ogg|max:51200',
        ]);

        $file = $request->file('video');

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file->getPathname());
        finfo_close($finfo);

        $allowedVideoMimes = ['video/mp4', 'video/webm', 'video/ogg'];
        if (!in_array($mimeType, $allowedVideoMimes)) {
            return response()->json(['error' => 'Tipo de arquivo de vídeo inválido.'], 422);
        }

        $extension = match ($mimeType) {
            'video/mp4' => 'mp4',
            'video/webm' => 'webm',
            'video/ogg' => 'ogg',
            default => throw new \RuntimeException('Tipo não suportado'),
        };

        $filename = Str::uuid() . '.' . $extension;
        $path = $file->storeAs('page-builder/videos', $filename, 'public');

        if (!$path) {
            return response()->json(['error' => 'Falha ao fazer upload do vídeo'], 500);
        }

        $url = parse_url(Storage::disk('public')->url($path), PHP_URL_PATH);

        return response()->json([
            'url' => $url,
            'filename' => $filename,
        ]);
    }

    public function restoreSnapshot(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'elements' => 'required|array',
        ]);

        DB::beginTransaction();

        try {
            $snapshotIds = $this->collectSnapshotIds($validated['elements']);
            $page->elements()->whereNotIn('id', $snapshotIds)->forceDelete();

            $this->importSnapshotElements($page, $validated['elements'], null);

            $page->touch();

            DB::commit();

            $elements = $page->allElements()->get();

            return response()->json([
                'message' => 'Snapshot restored successfully',
                'elements' => $this->elementManager->buildTree($elements),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to restore snapshot: ' . $e->getMessage()], 500);
        }
    }

    protected function collectSnapshotIds(array $elements): array
    {
        $ids = [];
        foreach ($elements as $el) {
            if (isset($el['id'])) $ids[] = $el['id'];
            if (!empty($el['children']) && is_array($el['children'])) {
                $ids = array_merge($ids, $this->collectSnapshotIds($el['children']));
            }
        }
        return $ids;
    }

    protected function importSnapshotElements(Page $page, array $elements, ?int $parentId): void
    {
        foreach ($elements as $index => $snapshot) {
            $existing = null;
            if (isset($snapshot['id'])) {
                $existing = Element::where('id', $snapshot['id'])
                    ->where('page_id', $page->id)
                    ->withTrashed()
                    ->first();
            }

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->update([
                    'parent_id' => $parentId,
                    'type' => $snapshot['type'] ?? $existing->type,
                    'name' => $snapshot['name'] ?? $existing->name,
                    'order' => $index,
                    'settings' => $snapshot['settings'] ?? $existing->settings,
                    'content' => $snapshot['content'] ?? $existing->content,
                    'styles' => $snapshot['styles'] ?? $existing->styles,
                ]);
                $element = $existing;
            } else {
                $widget = $this->widgetManager->getWidget($snapshot['type'] ?? '');
                $defaultSettings = $widget ? $widget->getDefaultSettings() : [];

                $element = new Element();
                $element->page_id = $page->id;
                $element->parent_id = $parentId;
                $element->type = $snapshot['type'] ?? 'text';
                $element->name = $snapshot['name'] ?? ($snapshot['type'] ?? 'Element');
                $element->order = $index;
                $element->settings = $snapshot['settings'] ?? $defaultSettings;
                $element->content = $snapshot['content'] ?? [];
                $element->styles = $snapshot['styles'] ?? [];
                $element->save();
            }

            if (!empty($snapshot['children']) && is_array($snapshot['children'])) {
                $this->importSnapshotElements($page, $snapshot['children'], $element->id);
            }
        }
    }

}
