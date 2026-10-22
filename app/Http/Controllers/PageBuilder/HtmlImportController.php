<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Jobs\ImportHtmlJob;
use App\Services\PageBuilder\Core\HtmlImportService;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HtmlImportController extends Controller
{
    public function __construct(
        private HtmlImportService $htmlImport,
        private PageBuilderService $pageBuilder,
    ) {}

    public function import(Request $request): JsonResponse
    {
        $this->authorize('create', Page::class);

        $validated = $request->validate([
            'html' => 'required_without:url|string|max:512000',
            'url'  => 'required_without:html|nullable|url|max:2048',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            if (!empty($validated['url'])) {
                $html = $this->htmlImport->fetchUrl($validated['url']);
            } else {
                $html = $validated['html'];
            }

            $title = $validated['title'] ?? 'Página Importada';

            ImportHtmlJob::dispatch($html, auth()->id(), $title);

            return response()->json([
                'message'      => 'Importação iniciada em segundo plano',
                'queued'       => true,
            ], 202);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro ao importar HTML: '.$e->getMessage()], 500);
        }
    }

    public function fetch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url|max:2048',
        ]);

        try {
            $html = $this->htmlImport->fetchUrl($validated['url']);

            return response()->json([
                'html'       => $html,
                'size'       => strlen($html),
                'truncated'  => strlen($html) > 512000,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function countWidgets(array $elements): int
    {
        $count = 0;
        foreach ($elements as $el) {
            $type = $el['type'] ?? '';
            if (!in_array($type, ['section', 'column'])) {
                $count++;
            }
            if (!empty($el['children'])) {
                $count += $this->countWidgets($el['children']);
            }
        }
        return $count;
    }
}
