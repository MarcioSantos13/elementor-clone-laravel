<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    use AuthorizesRequests;

    protected function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    protected function authorizeOwner(object $resource): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->isAdmin() || (int) ($resource->user_id ?? 0) === (int) $user->id),
            403
        );
    }
}
