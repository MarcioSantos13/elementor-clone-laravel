<?php

namespace App\Support;

use App\Models\Project;
use Illuminate\Http\Request;

class ActiveProject
{
    public const SESSION_KEY = 'active_project_id';

    public static function id(Request $request): ?int
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        $activeId = $request->session()->get(self::SESSION_KEY);

        if ($activeId) {
            $exists = $user->projects()->whereKey($activeId)->exists();
            if ($exists) {
                return (int) $activeId;
            }
        }

        $default = $user->defaultProject();

        $request->session()->put(self::SESSION_KEY, $default->id);

        return $default->id;
    }

    public static function set(Request $request, int $projectId): void
    {
        $request->session()->put(self::SESSION_KEY, $projectId);
    }

    public static function model(Request $request): ?Project
    {
        $id = self::id($request);

        return $id ? Project::find($id) : null;
    }
}
