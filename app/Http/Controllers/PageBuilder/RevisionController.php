<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Revision;
use App\Services\PageBuilder\Core\PageBuilderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevisionController extends Controller
{
    protected PageBuilderService $pageBuilder;

    public function __construct(PageBuilderService $pageBuilder)
    {
        $this->pageBuilder = $pageBuilder;
    }

    public function index(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $revisions = $page->revisions()->with('user')->paginate(50);

        return response()->json([
            'revisions' => $revisions,
        ]);
    }

    public function show(Revision $revision): JsonResponse
    {
        $this->authorize('view', $revision->page);
        return response()->json([
            'revision' => $revision->load('user'),
        ]);
    }

    public function restore(Page $page, Revision $revision): JsonResponse
    {
        $this->authorize('update', $page);
        $this->pageBuilder->restoreRevision($page, $revision);

        return response()->json([
            'message' => 'Revision restored successfully',
            'page' => $page->fresh(),
        ]);
    }

    public function diff(Page $page, Revision $revision): JsonResponse
    {
        $this->authorize('view', $page);
        $previousRevision = Revision::where('page_id', $page->id)
            ->where('id', '<', $revision->id)
            ->orderBy('id', 'desc')
            ->first();

        return response()->json([
            'current' => $revision->content,
            'previous' => $previousRevision?->content,
            'version' => $revision->version,
            'previous_version' => $previousRevision?->version,
        ]);
    }

    public function destroy(Revision $revision): JsonResponse
    {
        $this->authorize('update', $revision->page);
        $revision->delete();

        return response()->json([
            'message' => 'Revision deleted successfully',
        ]);
    }

    public function prune(Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $keep = (int) request('keep', 50);

        $deleted = Revision::where('page_id', $page->id)
            ->orderBy('created_at', 'desc')
            ->skip($keep)
            ->take(1000)
            ->delete();

        return response()->json([
            'message' => "{$deleted} old revisions pruned",
            'remaining' => $page->revisions()->count(),
        ]);
    }

    public function autoSave(Request $request, Page $page): JsonResponse
    {
        $this->authorize('update', $page);
        $validated = $request->validate([
            'content' => 'sometimes|array',
            'settings' => 'sometimes|array',
        ]);

        $revision = new Revision();
        $revision->page_id = $page->id;
        $revision->user_id = auth()->id();
        $revision->content = $validated['content'] ?? $page->content;
        $revision->settings = $validated['settings'] ?? $page->settings;
        $revision->version = '1.0.' . time();
        $revision->label = 'Auto-save';
        $revision->type = 'auto_save';
        $revision->save();

        return response()->json([
            'message' => 'Auto-saved successfully',
            'revision' => $revision,
        ]);
    }
}
