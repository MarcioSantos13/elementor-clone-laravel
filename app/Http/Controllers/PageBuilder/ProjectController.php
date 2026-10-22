<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use App\Models\Project;
use App\Models\ThemeTemplate;
use App\Models\Page;
use App\Services\PageBuilder\Project\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    public function __construct(protected ProjectService $projects) {}

    public function index(): View
    {
        $user = auth()->user();

        $projects = Project::query()
            ->when(!$user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->withCount(['pages', 'themeTemplates', 'popups'])
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $user->defaultProject();

        return view('page-builder.projects.index', compact('projects'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $project = $this->projects->create(auth()->user(), $validated);

        return response()->json([
            'message' => 'Projeto criado com sucesso',
            'project' => $project,
        ], 201);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $project = $this->projects->update($project, $validated);

        return response()->json([
            'message' => 'Projeto atualizado com sucesso',
            'project' => $project,
        ]);
    }

    public function destroy(Project $project): JsonResponse
    {
        $this->authorize('delete', $project);
        abort_if($project->is_default, 422, 'O projeto padrão não pode ser excluído.');

        $this->projects->delete($project);

        return response()->json([
            'message' => 'Projeto removido com sucesso',
        ]);
    }

    public function move(Request $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['page', 'theme_template', 'popup'])],
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        $model = match ($validated['type']) {
            'page' => Page::class,
            'theme_template' => ThemeTemplate::class,
            'popup' => Popup::class,
        };

        $moved = $model::query()
            ->whereIn('id', $validated['ids'])
            ->where('user_id', $project->user_id)
            ->update(['project_id' => $project->id]);

        return response()->json([
            'message' => "{$moved} item(ns) movido(s)",
            'moved' => $moved,
        ]);
    }
}
