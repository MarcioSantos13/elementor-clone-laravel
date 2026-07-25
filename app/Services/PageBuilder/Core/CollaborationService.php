<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CollaborationService
{
    private int $presenceTtl = 30;
    private int $heartbeatInterval = 15;

    public function userEditingPage(Page $page, User $user): void
    {
        $key = $this->presenceKey($page->id);
        $users = Cache::get($key, []);
        $users[$user->id] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'color' => $this->getUserColor($user->id),
            'last_seen' => now()->toIso8601String(),
            'cursor_position' => null,
        ];
        Cache::put($key, $users, $this->presenceTtl);
    }

    public function heartbeat(Page $page, User $user, ?array $cursorPosition = null): void
    {
        $key = $this->presenceKey($page->id);
        $users = Cache::get($key, []);
        $users[$user->id] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'color' => $this->getUserColor($user->id),
            'last_seen' => now()->toIso8601String(),
            'cursor_position' => $cursorPosition,
        ];
        Cache::put($key, $users, $this->presenceTtl);
    }

    public function userLeftPage(Page $page, User $user): void
    {
        $key = $this->presenceKey($page->id);
        $users = Cache::get($key, []);
        unset($users[$user->id]);
        if (empty($users)) {
            Cache::forget($key);
        } else {
            Cache::put($key, $users, $this->presenceTtl);
        }
    }

    public function getActiveUsers(Page $page): array
    {
        $key = $this->presenceKey($page->id);
        $users = Cache::get($key, []);

        return array_values(array_filter($users, function ($user) {
            $lastSeen = \Carbon\Carbon::parse($user['last_seen']);
            return $lastSeen->diffInSeconds(now()) < $this->presenceTtl;
        }));
    }

    public function getElementEditors(Page $page): array
    {
        $key = $this->elementEditorsKey($page->id);
        return Cache::get($key, []);
    }

    public function lockElement(Page $page, int $elementId, User $user): bool
    {
        $key = $this->elementEditorsKey($page->id);
        $editors = Cache::get($key, []);

        if (isset($editors[$elementId]) && $editors[$elementId]['user_id'] !== $user->id) {
            return false;
        }

        $editors[$elementId] = [
            'user_id' => $user->id,
            'name' => $user->name,
            'locked_at' => now()->toIso8601String(),
        ];
        Cache::put($key, $editors, 60);

        return true;
    }

    public function unlockElement(Page $page, int $elementId, User $user): void
    {
        $key = $this->elementEditorsKey($page->id);
        $editors = Cache::get($key, []);

        if (isset($editors[$elementId]) && $editors[$elementId]['user_id'] === $user->id) {
            unset($editors[$elementId]);
            Cache::put($key, $editors, 60);
        }
    }

    public function getElementLock(Page $page, int $elementId): ?array
    {
        $key = $this->elementEditorsKey($page->id);
        $editors = Cache::get($key, []);

        return $editors[$elementId] ?? null;
    }

    private function presenceKey(int $pageId): string
    {
        return "page_collab_{$pageId}";
    }

    private function elementEditorsKey(int $pageId): string
    {
        return "page_element_editors_{$pageId}";
    }

    private function getUserColor(int $userId): string
    {
        $colors = [
            '#ef4444', '#f97316', '#eab308', '#22c55e',
            '#06b6d4', '#3b82f6', '#8b5cf6', '#ec4899',
            '#f43f5e', '#14b8a6', '#6366f1', '#a855f7',
        ];
        return $colors[$userId % count($colors)];
    }
}
