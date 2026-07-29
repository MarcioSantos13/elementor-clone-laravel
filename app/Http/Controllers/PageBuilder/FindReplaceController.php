<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Element;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FindReplaceController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate(['query' => 'required|string|min:1']);
        $query = $request->input('query');
        $pageId = $request->input('page_id');

        $elementsQuery = Element::query()
            ->where(function ($q) use ($query) {
                $q->where('settings', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('styles', 'like', "%{$query}%")
                  ->orWhere('name', 'like', "%{$query}%");
            });

        if ($pageId) {
            $elementsQuery->where('page_id', $pageId);
        }

        $results = $elementsQuery->limit(100)->get()->map(function ($el) {
            $match = '';
            $settings = $el->settings ?? [];
            foreach ($settings as $key => $val) {
                if (is_string($val) && stripos($val, request('query')) !== false) {
                    $match = $key . ': ' . substr($val, 0, 100);
                    break;
                }
            }
            return [
                'id' => $el->id,
                'page_id' => $el->page_id,
                'page_title' => $el->page->title ?? 'Unknown',
                'type' => $el->type,
                'name' => $el->name,
                'match' => $match,
            ];
        });

        $pages = Page::where('title', 'like', "%{$query}%")
            ->orWhere('slug', 'like', "%{$query}%")
            ->limit(20)->get(['id', 'title', 'slug']);

        return response()->json([
            'elements' => $results,
            'pages' => $pages,
            'total' => $results->count(),
        ]);
    }

    public function replace(Request $request): JsonResponse
    {
        $request->validate([
            'find' => 'required|string|min:1',
            'replace' => 'nullable|string',
            'page_id' => 'nullable|exists:pages,id',
        ]);

        $find = $request->input('find');
        $replace = $request->input('replace', '');
        $pageId = $request->input('page_id');

        $elementsQuery = Element::query();
        if ($pageId) {
            $elementsQuery->where('page_id', $pageId);
        }

        $count = 0;
        DB::beginTransaction();
        try {
            $elementsQuery->chunk(50, function ($elements) use ($find, $replace, &$count) {
                foreach ($elements as $el) {
                    $updated = false;
                    if (is_string($el->settings)) {
                        $el->settings = json_decode($el->settings, true);
                    }
                    if ($el->settings) {
                        $el->settings = $this->arrayStrReplace($find, $replace, $el->settings);
                        $updated = true;
                    }
                    if ($el->content) {
                        $el->content = $this->arrayStrReplace($find, $replace, $el->content);
                        $updated = true;
                    }
                    if ($el->styles) {
                        $el->styles = $this->arrayStrReplace($find, $replace, $el->styles);
                        $updated = true;
                    }
                    if ($el->name && stripos($el->name, $find) !== false) {
                        $el->name = str_ireplace($find, $replace, $el->name);
                        $updated = true;
                    }
                    if ($updated) {
                        $el->save();
                        $count++;
                    }
                }
            });
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Replace failed: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => "Replaced in {$count} elements", 'count' => $count]);
    }

    protected function arrayStrReplace($find, $replace, $data)
    {
        if (is_string($data)) {
            return str_ireplace($find, $replace, $data);
        }
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->arrayStrReplace($find, $replace, $value);
            }
        }
        return $data;
    }
}
