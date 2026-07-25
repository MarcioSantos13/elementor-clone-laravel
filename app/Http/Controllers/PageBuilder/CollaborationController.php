<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageBuilder\Core\CollaborationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollaborationController extends Controller
{
    public function __construct(
        protected CollaborationService $collaboration,
    ) {}

    public function join(Request $request, Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $this->collaboration->userEditingPage($page, $request->user());

        return response()->json([
            'message' => 'Joined collaboration',
            'color' => $this->collaboration->getActiveUsers($page),
        ]);
    }

    public function leave(Request $request, Page $page): JsonResponse
    {
        $this->collaboration->userLeftPage($page, $request->user());

        return response()->json(['message' => 'Left collaboration']);
    }

    public function heartbeat(Request $request, Page $page): JsonResponse
    {
        $cursorPosition = $request->input('cursor_position');
        $this->collaboration->heartbeat($page, $request->user(), $cursorPosition);

        return response()->json([
            'active_users' => $this->collaboration->getActiveUsers($page),
        ]);
    }

    public function activeUsers(Page $page): JsonResponse
    {
        $this->authorize('view', $page);
        $users = $this->collaboration->getActiveUsers($page);

        return response()->json(['active_users' => $users]);
    }

    public function lockElement(Request $request, Page $page, int $elementId): JsonResponse
    {
        $this->authorize('update', $page);
        $locked = $this->collaboration->lockElement($page, $elementId, $request->user());

        if (!$locked) {
            $lock = $this->collaboration->getElementLock($page, $elementId);
            return response()->json([
                'message' => 'Element is being edited by another user',
                'locked_by' => $lock['name'] ?? 'Unknown',
            ], 409);
        }

        return response()->json(['message' => 'Element locked']);
    }

    public function unlockElement(Request $request, Page $page, int $elementId): JsonResponse
    {
        $this->collaboration->unlockElement($page, $elementId, $request->user());

        return response()->json(['message' => 'Element unlocked']);
    }
}
