<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\ElementManager;
use App\Services\PageBuilder\Core\TemplateManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageApiController extends Controller
{
    public function __construct(
        protected PageBuilderService $pageBuilder,
        protected ElementManager $elementManager,
        protected TemplateManager $templateManager,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pages = Page::where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->input('per_page', 20));

        return response()->json($pages);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
        ]);

        $page = $this->pageBuilder->createPage($validated);

        $templateKey = $request->input('template');
        if ($templateKey && $this->templateManager->has($templateKey)) {
            $template = $this->templateManager->get($templateKey);
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();
        }

        return response()->json([
            'message' => 'Page created',
            'page' => $page,
        ], 201);
    }

    public function show(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $elements = $this->elementManager->buildTree($page->allElements()->get());

        return response()->json([
            'page' => $page,
            'elements' => $elements,
        ]);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'status' => 'nullable|in:draft,published',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'template' => 'nullable|string|max:255',
        ]);

        $page = $this->pageBuilder->updatePage($page, $validated);

        return response()->json([
            'message' => 'Page updated',
            'page' => $page,
        ]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);
        $page->delete();

        return response()->json([
            'message' => 'Page deleted',
        ]);
    }
}
