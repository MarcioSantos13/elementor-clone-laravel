<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Popup;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Popup\PopupService;
use App\Services\PageBuilder\Theme\ThemeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PopupController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected PopupService $popupService;

    public function __construct(PageBuilderService $pageBuilder, PopupService $popupService)
    {
        $this->pageBuilder = $pageBuilder;
        $this->popupService = $popupService;
    }

    public function index(): View
    {
        $popups = Popup::ordered()->with('user', 'page')->get();
        return view('page-builder.popups.index', compact('popups'));
    }

    public function create(): View
    {
        $types = Popup::types();
        $triggerTypes = Popup::triggerTypes();
        return view('page-builder.popups.create', compact('types', 'triggerTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', Popup::types()),
            'status' => 'nullable|in:draft,published',
            'triggers' => 'nullable|array',
            'settings' => 'nullable|array',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $pageData = [
                'title' => $validated['title'] . ' (Popup)',
                'status' => 'draft',
            ];
            $page = $this->pageBuilder->createPage($pageData);

            $slug = Str::slug($validated['title']) . '-' . Str::random(6);

            $popup = Popup::create([
                'user_id' => $user->id,
                'page_id' => $page->id,
                'title' => $validated['title'],
                'slug' => $slug,
                'type' => $validated['type'],
                'status' => $validated['status'] ?? 'draft',
                'triggers' => $validated['triggers'] ?? [],
                'settings' => $validated['settings'] ?? [],
                'conditions' => [],
                'order' => Popup::count(),
            ]);

            DB::commit();

            $this->popupService->clearCache();

            $redirectTo = $request->input('next', 'index');

            if ($redirectTo === 'editor') {
                return redirect()->route('page-builder.popups.editor', $popup)
                    ->with('success', "Popup \"{$popup->title}\" created!");
            }

            return redirect()->route('page-builder.popups.index')
                ->with('success', "Popup \"{$popup->title}\" created!");
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function edit(Popup $popup): View
    {
        $types = Popup::types();
        $triggerTypes = Popup::triggerTypes();
        return view('page-builder.popups.create', compact('popup', 'types', 'triggerTypes'));
    }

    public function update(Request $request, Popup $popup): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string|in:' . implode(',', Popup::types()),
            'status' => 'nullable|in:draft,published',
        ]);

        $popup->update($validated);

        if ($request->boolean('publish') || $validated['status'] === 'published') {
            $popup->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        $this->popupService->clearCache();

        return redirect()->route('page-builder.popups.index')
            ->with('success', "Popup \"{$popup->title}\" updated!");
    }

    public function destroy(Popup $popup): RedirectResponse
    {
        $title = $popup->title;
        $popup->delete();

        $this->popupService->clearCache();

        return redirect()->route('page-builder.popups.index')
            ->with('success', "Popup \"{$title}\" deleted!");
    }

    public function editor(Popup $popup): View
    {
        $page = $popup->page;
        if (!$page) {
            abort(404, 'Associated page not found');
        }

        $popupContext = [
            'popup_id' => $popup->id,
            'popup_title' => $popup->title,
            'popup_type' => $popup->type,
        ];

        return view('page-builder.editor', [
            'page' => $page,
            'popupContext' => $popupContext,
        ]);
    }

    public function getTriggers(Popup $popup): JsonResponse
    {
        return response()->json([
            'triggers' => $popup->triggers ?? [],
            'triggerTypes' => Popup::triggerTypes(),
        ]);
    }

    public function updateTriggers(Request $request, Popup $popup): JsonResponse
    {
        $validated = $request->validate([
            'triggers' => 'nullable|array',
            'triggers.*.type' => 'required|string',
            'triggers.*.value' => 'nullable|string',
        ]);

        $popup->update([
            'triggers' => $validated['triggers'] ?? [],
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Triggers updated!',
            'triggers' => $popup->fresh()->triggers,
        ]);
    }

    public function getConditions(Popup $popup): JsonResponse
    {
        return response()->json([
            'conditions' => $popup->conditions ?? [],
            'options' => ThemeService::conditionOptions(),
            'pages' => Page::select('id', 'title', 'slug')
                ->where('status', 'published')
                ->orderBy('title')
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'title' => $p->title, 'slug' => $p->slug]),
        ]);
    }

    public function updateConditions(Request $request, Popup $popup): JsonResponse
    {
        $validated = $request->validate([
            'conditions' => 'nullable|array',
            'conditions.*.type' => 'required|string',
            'conditions.*.value' => 'nullable|string',
        ]);

        $popup->update([
            'conditions' => $validated['conditions'] ?? [],
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Conditions updated!',
            'conditions' => $popup->fresh()->conditions,
        ]);
    }

    public function publish(Popup $popup): JsonResponse
    {
        $popup->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Popup published!',
            'popup' => $popup->fresh(),
        ]);
    }

    public function unpublish(Popup $popup): JsonResponse
    {
        $popup->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        $this->popupService->clearCache();

        return response()->json([
            'message' => 'Popup unpublished!',
            'popup' => $popup->fresh(),
        ]);
    }

    public function render(Popup $popup): \Illuminate\Http\Response
    {
        $html = $this->popupService->renderPopup($popup);

        return response($html)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');
    }
}
