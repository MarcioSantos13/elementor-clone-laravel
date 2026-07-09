<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\TemplateManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PageController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected Renderer $renderer;
    protected TemplateManager $templateManager;

    public function __construct(PageBuilderService $pageBuilder, Renderer $renderer, TemplateManager $templateManager)
    {
        $this->pageBuilder = $pageBuilder;
        $this->renderer = $renderer;
        $this->templateManager = $templateManager;
    }

    public function getTemplates(): array
    {
        return $this->templateManager->all();
    }

    public function index(): View
    {
        $pages = Page::latest()->paginate(20);
        return view('page-builder.pages.index', compact('pages'));
    }

    public function create(): View
    {
        $templates = [];
        foreach ($this->templateManager->all() as $key => $tmpl) {
            if ($key === 'blank') continue;
            $templates[$key] = $tmpl['name'];
        }
        return view('page-builder.pages.create', compact('templates'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            '_redirect' => 'nullable|in:index,editor',
        ]);

        $page = $this->pageBuilder->createPage($validated);

        $templateKey = $request->input('template');
        if ($templateKey && $this->templateManager->has($templateKey)) {
            $template = $this->templateManager->get($templateKey);
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();
            $this->importTemplateElements($page, $template['elements']);
        }

        $redirectTo = $request->input('_redirect', 'index');

        return $redirectTo === 'editor'
            ? redirect()->route('page-builder.editor', $page)
            : redirect()->route('page-builder.pages.index')->with('success', "Page \"{$page->title}\" created successfully!");
    }

    public function show(Page $page): View
    {
        $this->authorize('view', $page);
        $html = $this->pageBuilder->renderPage($page);
        return view('page-builder.pages.show', compact('page', 'html'));
    }

    public function edit(Page $page): View
    {
        $this->authorize('update', $page);
        return view('page-builder.editor', compact('page'));
    }

    public function listTemplates(): JsonResponse
    {
        return response()->json(['templates' => $this->templateManager->list()]);
    }

    public function applyTemplate(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $templateKey = $request->input('template', 'blank');

        if (!$this->templateManager->has($templateKey)) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $template = $this->templateManager->get($templateKey);

        DB::beginTransaction();
        try {
            $page->elements()->delete();
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();

            $this->importTemplateElements($page, $template['elements']);

            DB::commit();

            return response()->json([
                'message' => "Template \"{$template['name']}\" applied",
                'page' => $page->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateLayout(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $page->settings = array_merge($page->settings ?? [], $validated['settings']);
        $page->save();

        return response()->json([
            'message' => 'Layout updated',
            'page' => $page,
        ]);
    }

    protected function importTemplateElements(Page $page, array $elements, ?int $parentId = null, string $widgetType = null): void
    {
        foreach ($elements as $index => $elData) {
            $type = $widgetType ?? $elData['type'];
            $children = $elData['children'] ?? [];

            $element = new Element();
            $element->page_id = $page->id;
            $element->parent_id = $parentId;
            $element->type = $type;
            $element->uuid = (string) \Illuminate\Support\Str::uuid();
            $element->name = $elData['settings']['name'] ?? ucfirst($type);
            $element->settings = $elData['settings'] ?? [];
            $element->content = [];
            $element->styles = [];
            $element->order = $index;
            $element->save();

            if ($children) {
                $childType = null;
                if ($type === 'section') $childType = 'column';
                $this->importTemplateElements($page, $children, $element->id, $childType);
            }
        }
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'status' => 'nullable|in:draft,published,archived',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'template' => 'nullable|string|max:255',
        ]);

        $page = $this->pageBuilder->updatePage($page, $validated);

        return response()->json([
            'message' => 'Page updated successfully',
            'page' => $page,
        ]);
    }

    public function destroy(Page $page): \Illuminate\Http\RedirectResponse
    {
        $this->authorize('delete', $page);
        $title = $page->title;
        $page->delete();

        return redirect()->route('page-builder.pages.index')
            ->with('success', "Page \"{$title}\" deleted successfully!");
    }

    public function publish(Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $page->status = 'published';
        $page->save();

        return response()->json([
            'message' => 'Page published successfully',
            'page' => $page,
        ]);
    }

    public function unpublish(Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $page->status = 'draft';
        $page->save();

        return response()->json([
            'message' => 'Page unpublished successfully',
            'page' => $page,
        ]);
    }

    public function duplicate(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $newPage = $page->replicate();
        $newPage->title = $page->title . ' (copy)';
        $newPage->slug = $page->slug . '-' . uniqid();
        $newPage->status = 'draft';
        $newPage->save();

        $idMap = [];
        foreach ($page->allElements()->get() as $element) {
            $newElement = $element->replicate();
            $newElement->page_id = $newPage->id;
            $newElement->uuid = (string) Str::uuid();
            if ($element->parent_id && isset($idMap[$element->parent_id])) {
                $newElement->parent_id = $idMap[$element->parent_id];
            } else {
                $newElement->parent_id = null;
            }
            $newElement->save();
            $idMap[$element->id] = $newElement->id;
        }

        return response()->json([
            'message' => 'Page duplicated successfully',
            'page' => $newPage->fresh(),
        ]);
    }

    public function export(Page $page)
    {
        $this->authorize('view', $page);
        $data = $this->pageBuilder->exportPage($page);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = Str::slug($page->title) . '.json';

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'data.title' => 'required|string|max:255',
            'data.elements' => 'sometimes|array',
        ]);

        $page = $this->pageBuilder->importPage($validated['data']);

        return response()->json([
            'message' => 'Page imported successfully',
            'page' => $page,
        ], 201);
    }

    public function getData(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $tree = $this->buildElementTree($page->allElements()->get());

        return response()->json([
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'settings' => $page->settings,
                'meta_data' => $page->meta_data,
                'template' => $page->template,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ],
            'elements' => $tree,
        ]);
    }

    public function render(Page $page): \Illuminate\Http\Response
    {
        $this->authorize('view', $page);
        $html = $this->pageBuilder->renderPage($page, [
            'with_container' => request('format') !== 'inner',
            'theme' => request('theme', 'default'),
        ]);

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }

    protected function buildElementTree($elements): array
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
                    'responsive_settings' => $element->responsive_settings,
                    'animation' => $element->animation,
                    'effects' => $element->effects,
                    'column_size' => $element->column_size,
                    'css_classes' => $element->css_classes,
                    'css_id' => $element->css_id,
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
