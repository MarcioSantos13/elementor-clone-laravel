<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Services\PageBuilder\Core\HtmlImportService;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HtmlImportController extends Controller
{
    public function __construct(
        private HtmlImportService $htmlImport,
        private PageBuilderService $pageBuilder,
    ) {}

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'html' => 'required_without:url|string|max:512000',
            'url'  => 'required_without:html|nullable|string|max:2048',
            'title' => 'nullable|string|max:255',
        ]);

        try {
            if (!empty($validated['url'])) {
                $html = $this->htmlImport->fetchUrl($validated['url']);
            } else {
                $html = $validated['html'];
            }

            $title = $validated['title'] ?? 'Página Importada';
            $converted = $this->htmlImport->convert($html, $title);

            $slug = Str::slug($converted['title'] ?? $title);
            if (Page::where('slug', $slug)->exists()) {
                $converted['title'] = ($converted['title'] ?? $title) . ' ' . date('H:i');
            }

            $page = $this->pageBuilder->importPage($converted);

            $widgetCount = $this->countWidgets($converted['elements'] ?? []);

            return response()->json([
                'message'      => 'Página importada com sucesso',
                'page_id'      => $page->id,
                'redirect_url' => route('page-builder.editor', $page),
                'title'        => $page->title,
                'widgets_count'=> $widgetCount,
            ], 201);
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
