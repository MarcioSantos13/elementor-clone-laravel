<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Popup;
use App\Models\Project;
use App\Models\ThemeTemplate;
use App\Services\PageBuilder\Project\ProjectMoodleExportService;
use App\Services\PageBuilder\Project\ProjectService;
use App\Services\PageBuilder\Project\ProjectTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projects,
        protected ProjectTemplateService $templates,
        protected ProjectMoodleExportService $moodleExporter,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        $projects = Project::query()
            ->when(! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->withCount(['pages', 'themeTemplates', 'popups'])
            ->orderByDesc('is_default')
            ->latest()
            ->get();

        $user->defaultProject();

        return view('page-builder.projects.index', compact('projects'));
    }

    public function dashboard(Project $project): View
    {
        $this->authorize('view', $project);

        $project->loadCount(['pages', 'themeTemplates', 'popups']);

        $pages = $project->pages()->latest()->limit(10)->get();
        $themes = $project->themeTemplates()->latest()->limit(10)->get();
        $popups = $project->popups()->latest()->limit(10)->get();

        return view('page-builder.projects.show', compact('project', 'pages', 'themes', 'popups'));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:1000',
        ]);

        $project = $this->projects->create(auth()->user(), [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        if (! empty($validated['location'])) {
            $settings = $project->settings ?? [];
            $settings['location'] = $validated['location'];
            $project->settings = $settings;
            $project->save();
        }

        return response()->json([
            'message' => 'Projeto criado com sucesso',
            'project' => $project,
        ], 201);
    }

    public function createExample(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $project = $this->templates->createExample($request->user(), $validated['name'] ?? null);

        return $this->templateResponse($request, $project, 'Projeto de exemplo criado com sucesso');
    }

    public function duplicate(Request $request, Project $project): JsonResponse|RedirectResponse
    {
        $this->authorize('view', $project);
        $this->authorize('create', Project::class);

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
        ]);

        $duplicate = $this->templates->duplicate($project, $request->user(), $validated['name'] ?? null);

        return $this->templateResponse($request, $duplicate, 'Projeto criado a partir do modelo');
    }

    public function exportMoodle(Request $request, Project $project): BinaryFileResponse|RedirectResponse|JsonResponse
    {
        $this->authorize('view', $project);

        try {
            $path = $this->moodleExporter->build($project);
        } catch (Throwable $exception) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                ], 422);
            }

            return back()->with('error', $exception->getMessage());
        }

        return response()
            ->download($path, $this->moodleExporter->downloadName($project), [
                'Content-Type' => 'application/zip',
            ])
            ->deleteFileAfterSend(true);
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

    protected function templateResponse(Request $request, Project $project, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'project' => $project,
            ], 201);
        }

        return redirect()
            ->route('page-builder.projects.dashboard', $project)
            ->with('success', $message);
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
