<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\ThemeTemplate;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\Renderer;
use App\Services\PageBuilder\Core\TemplateManager;
use App\Services\PageBuilder\Theme\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ThemeTemplateController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected Renderer $renderer;
    protected TemplateManager $templateManager;
    protected ThemeService $themeService;

    public function __construct(
        PageBuilderService $pageBuilder,
        Renderer $renderer,
        TemplateManager $templateManager,
        ThemeService $themeService
    ) {
        $this->pageBuilder = $pageBuilder;
        $this->renderer = $renderer;
        $this->templateManager = $templateManager;
        $this->themeService = $themeService;
    }

    public function index(): View
    {
        $templates = ThemeTemplate::ordered()->with('user', 'page')->get();
        $grouped = $templates->groupBy('type');
        return view('page-builder.theme-templates.index', compact('templates', 'grouped'));
    }

    public function create(): View
    {
        $types = ThemeTemplate::typeOptions();
        $pages = Page::select('id', 'title')->orderBy('title')->get();
        return view('page-builder.theme-templates.create', compact('types', 'pages'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', ThemeTemplate::types()),
            'page_id' => 'nullable|exists:pages,id',
            'status' => 'nullable|in:draft,published',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $page = null;
            if (!empty($validated['page_id'])) {
                $page = Page::findOrFail($validated['page_id']);
            } else {
                $pageData = [
                    'title' => $validated['title'] . ' (' . ($validated['type'] ?? 'template') . ')',
                    'status' => 'draft',
                ];
                $page = $this->pageBuilder->createPage($pageData);
            }

            $slug = Str::slug($validated['title']) . '-' . Str::random(6);

            $template = ThemeTemplate::create([
                'user_id' => $user->id,
                'page_id' => $page->id,
                'title' => $validated['title'],
                'slug' => $slug,
                'type' => $validated['type'],
                'status' => $validated['status'] ?? 'draft',
                'conditions' => [],
                'order' => ThemeTemplate::byType($validated['type'])->count(),
            ]);

            DB::commit();

            $this->themeService->clearCache();

            $redirectTo = $request->input('next', 'index');

            if ($redirectTo === 'editor') {
                return redirect()->route('page-builder.themes.editor', $template)
                    ->with('success', "Theme template \"{$template->title}\" created!");
            }

            return redirect()->route('page-builder.themes.index')
                ->with('success', "Theme template \"{$template->title}\" created!");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(ThemeTemplate $themeTemplate): View
    {
        $types = ThemeTemplate::typeOptions();
        $pages = Page::select('id', 'title')->orderBy('title')->get();
        return view('page-builder.theme-templates.create', compact('themeTemplate', 'types', 'pages'));
    }

    public function update(Request $request, ThemeTemplate $themeTemplate): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', ThemeTemplate::types()),
            'status' => 'nullable|in:draft,published',
        ]);

        $themeTemplate->update($validated);

        if ($request->boolean('publish') || $validated['status'] === 'published') {
            $themeTemplate->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        $this->themeService->clearCache();

        return redirect()->route('page-builder.themes.index')
            ->with('success', "Theme template \"{$themeTemplate->title}\" updated!");
    }

    public function destroy(ThemeTemplate $themeTemplate): RedirectResponse
    {
        $title = $themeTemplate->title;
        $themeTemplate->delete();

        $this->themeService->clearCache();

        return redirect()->route('page-builder.themes.index')
            ->with('success', "Theme template \"{$title}\" deleted!");
    }

    public function editor(ThemeTemplate $themeTemplate): View
    {
        $page = $themeTemplate->page;
        if (!$page) {
            abort(404, 'Associated page not found');
        }

        $themeContext = [
            'template_id' => $themeTemplate->id,
            'template_type' => $themeTemplate->type,
            'template_title' => $themeTemplate->title,
        ];

        return view('page-builder.editor', [
            'page' => $page,
            'themeContext' => $themeContext,
        ]);
    }

    public function editConditions(ThemeTemplate $themeTemplate): View
    {
        return view('page-builder.theme-templates.conditions', compact('themeTemplate'));
    }

    public function getConditions(ThemeTemplate $themeTemplate): JsonResponse
    {
        return response()->json([
            'conditions' => $themeTemplate->conditions ?? [],
            'options' => ThemeService::conditionOptions(),
            'pages' => Page::select('id', 'title', 'slug')
                ->where('status', 'published')
                ->orderBy('title')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug]),
        ]);
    }

    public function updateConditions(Request $request, ThemeTemplate $themeTemplate): JsonResponse
    {
        $validated = $request->validate([
            'conditions' => 'nullable|array',
            'conditions.*.type' => 'required|string',
            'conditions.*.value' => 'nullable|string',
        ]);

        $themeTemplate->update([
            'conditions' => $validated['conditions'] ?? [],
        ]);

        $this->themeService->clearCache();

        return response()->json([
            'message' => 'Conditions updated!',
            'conditions' => $themeTemplate->fresh()->conditions,
        ]);
    }

    public function publish(ThemeTemplate $themeTemplate): JsonResponse
    {
        $themeTemplate->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->themeService->clearCache();

        return response()->json([
            'message' => 'Theme template published!',
            'template' => $themeTemplate->fresh(),
        ]);
    }

    public function unpublish(ThemeTemplate $themeTemplate): JsonResponse
    {
        $themeTemplate->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->themeService->clearCache();

        return response()->json([
            'message' => 'Theme template unpublished!',
            'template' => $themeTemplate->fresh(),
        ]);
    }

    public function render(ThemeTemplate $themeTemplate): \Illuminate\Http\Response
    {
        $html = $this->renderer->render($themeTemplate->page, [
            'with_container' => request('format') !== 'inner',
            'theme' => 'theme-template-' . $themeTemplate->type,
        ]);

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
