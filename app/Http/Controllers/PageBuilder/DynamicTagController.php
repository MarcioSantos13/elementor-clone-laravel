<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Services\PageBuilder\DynamicTags\DynamicTagService;
use Illuminate\Http\JsonResponse;

class DynamicTagController extends Controller
{
    protected DynamicTagService $dynamicTagService;

    public function __construct(DynamicTagService $dynamicTagService)
    {
        $this->dynamicTagService = $dynamicTagService;
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'groups' => $this->dynamicTagService->getTagGroups(),
            'tags' => $this->dynamicTagService->getFlatTags(),
        ]);
    }
}
