<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\Renderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected Renderer $renderer;

    public function __construct(PageBuilderService $pageBuilder, Renderer $renderer)
    {
        $this->pageBuilder = $pageBuilder;
        $this->renderer = $renderer;
    }

    protected array $templates = [
        'blank' => [
            'name' => 'Blank Page',
            'description' => 'Start from scratch',
            'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
            'elements' => [],
        ],
        'landing' => [
            'name' => 'Landing Page',
            'description' => 'Hero section with CTA',
            'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
            'elements' => [
                ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#1a1a2e', 'padding_top' => '100px', 'padding_bottom' => '100px', 'min_height' => '80vh'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Welcome to Your New Website', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.2rem;color:#cdd6f4;">Build beautiful pages with our drag-and-drop builder</p>', 'alignment' => 'center']],
                        ['type' => 'button', 'settings' => ['text' => 'Get Started', 'link' => '#', 'background_color' => '#007bff', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px']],
                    ]],
                ]],
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Fast', 'tag' => 'h3', 'size' => 'medium', 'color' => '#333', 'alignment' => 'center']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Optimized for speed and performance</p>', 'alignment' => 'center']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Flexible', 'tag' => 'h3', 'size' => 'medium', 'color' => '#333', 'alignment' => 'center']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Drag and drop to build anything</p>', 'alignment' => 'center']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Modern', 'tag' => 'h3', 'size' => 'medium', 'color' => '#333', 'alignment' => 'center']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Built with the latest technology</p>', 'alignment' => 'center']],
                    ]],
                ]],
            ],
        ],
        'about' => [
            'name' => 'About Page',
            'description' => 'Company presentation',
            'settings' => ['container_width' => '960px', 'page_background' => '#f8f9fa', 'content_padding' => '40px'],
            'elements' => [
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '60px', 'padding_bottom' => '60px', 'border_radius' => '8px', 'margin_bottom' => '20px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'About Us', 'tag' => 'h1', 'size' => 'large', 'color' => '#333', 'alignment' => 'left']],
                        ['type' => 'text', 'settings' => ['content' => '<p>We are a passionate team dedicated to delivering exceptional digital experiences. Our mission is to empower creators with intuitive tools that bring their ideas to life.</p><p>Founded in 2024, we have helped hundreds of businesses establish their online presence with beautiful, functional websites.</p>', 'font_size' => '16px', 'line_height' => '1.8']],
                    ]],
                ]],
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '8px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Our Values', 'tag' => 'h2', 'size' => 'medium', 'color' => '#333', 'alignment' => 'left']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Innovation', 'tag' => 'h3', 'size' => 'small', 'color' => '#007bff']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Constantly pushing boundaries</p>']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Quality', 'tag' => 'h3', 'size' => 'small', 'color' => '#007bff']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Every detail matters to us</p>']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Support', 'tag' => 'h3', 'size' => 'small', 'color' => '#007bff']],
                        ['type' => 'text', 'settings' => ['content' => '<p>We are here to help you succeed</p>']],
                    ]],
                ]],
            ],
        ],
        'contact' => [
            'name' => 'Contact Page',
            'description' => 'Contact form layout',
            'settings' => ['container_width' => '800px', 'page_background' => '#f0f2f5', 'content_padding' => '40px'],
            'elements' => [
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '60px', 'padding_bottom' => '60px', 'border_radius' => '8px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Get in Touch', 'tag' => 'h1', 'size' => 'large', 'color' => '#333', 'alignment' => 'center']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="text-align:center;color:#666;">We would love to hear from you. Send us a message and we will respond as soon as possible.</p>']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-6'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Our Office', 'tag' => 'h3', 'size' => 'small', 'color' => '#333']],
                        ['type' => 'text', 'settings' => ['content' => '<p>123 Main Street<br>New York, NY 10001<br>United States</p><p>Email: hello@example.com<br>Phone: (555) 123-4567</p>']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-6'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Send a Message', 'tag' => 'h3', 'size' => 'small', 'color' => '#333']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Use the form below or reach out directly via email.</p>']],
                        ['type' => 'button', 'settings' => ['text' => 'Email Us', 'link' => 'mailto:hello@example.com', 'background_color' => '#007bff', 'text_color' => '#ffffff', 'size' => 'medium']],
                    ]],
                ]],
            ],
        ],
    ];

    public function index(): View
    {
        $pages = Page::latest()->paginate(20);
        return view('page-builder.pages.index', compact('pages'));
    }

    public function create(): View
    {
        return view('page-builder.pages.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            '_redirect' => 'nullable|in:index,editor',
        ]);

        $page = $this->pageBuilder->createPage($validated);

        $redirectTo = $request->input('_redirect', 'index');

        return $redirectTo === 'editor'
            ? redirect()->route('page-builder.editor', $page)
            : redirect()->route('page-builder.pages.index')->with('success', "Page \"{$page->title}\" created successfully!");
    }

    public function show(Page $page): View
    {
        $html = $this->pageBuilder->renderPage($page);
        return view('page-builder.pages.show', compact('page', 'html'));
    }

    public function edit(Page $page): View
    {
        return view('page-builder.editor', compact('page'));
    }

    public function listTemplates(): JsonResponse
    {
        $list = [];
        foreach ($this->templates as $key => $tmpl) {
            $list[$key] = ['name' => $tmpl['name'], 'description' => $tmpl['description']];
        }
        return response()->json(['templates' => $list]);
    }

    public function applyTemplate(Request $request, Page $page): JsonResponse
    {
        $templateKey = $request->input('template', 'blank');

        if (!isset($this->templates[$templateKey])) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $template = $this->templates[$templateKey];

        DB::beginTransaction();
        try {
            $page->elements()->delete();
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();

            $this->importTemplateElements($page, $template['elements']);

            DB::commit();

            return response()->json([
                'message' => "Template \"{$template['name']}\" applied",
                'page' => $page->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateLayout(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $page->settings = array_merge($page->settings ?? [], $validated['settings']);
        $page->save();

        return response()->json([
            'message' => 'Layout updated',
            'page' => $page,
        ]);
    }

    protected function importTemplateElements(Page $page, array $elements, ?int $parentId = null, string $widgetType = null): void
    {
        foreach ($elements as $index => $elData) {
            $type = $widgetType ?? $elData['type'];
            $children = $elData['children'] ?? [];

            $element = new Element();
            $element->page_id = $page->id;
            $element->parent_id = $parentId;
            $element->type = $type;
            $element->uuid = (string) \Illuminate\Support\Str::uuid();
            $element->name = $elData['settings']['name'] ?? ucfirst($type);
            $element->settings = $elData['settings'] ?? [];
            $element->content = [];
            $element->styles = [];
            $element->order = $index;
            $element->save();

            if ($children) {
                $childType = null;
                if ($type === 'section') $childType = 'column';
                $this->importTemplateElements($page, $children, $element->id, $childType);
            }
        }
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'status' => 'nullable|in:draft,published,archived',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'template' => 'nullable|string|max:255',
        ]);

        $page = $this->pageBuilder->updatePage($page, $validated);

        return response()->json([
            'message' => 'Page updated successfully',
            'page' => $page,
        ]);
    }

    public function destroy(Page $page): JsonResponse
    {
        $page->delete();

        return response()->json([
            'message' => 'Page deleted successfully',
        ]);
    }

    public function publish(Page $page): JsonResponse
    {
        $page->status = 'published';
        $page->save();

        return response()->json([
            'message' => 'Page published successfully',
            'page' => $page,
        ]);
    }

    public function unpublish(Page $page): JsonResponse
    {
        $page->status = 'draft';
        $page->save();

        return response()->json([
            'message' => 'Page unpublished successfully',
            'page' => $page,
        ]);
    }

    public function duplicate(Page $page): JsonResponse
    {
        $newPage = $page->replicate();
        $newPage->title = $page->title . ' (copy)';
        $newPage->slug = $page->slug . '-' . uniqid();
        $newPage->status = 'draft';
        $newPage->save();

        foreach ($page->elements as $element) {
            $newElement = $element->replicate();
            $newElement->page_id = $newPage->id;
            $newElement->save();
        }

        return response()->json([
            'message' => 'Page duplicated successfully',
            'page' => $newPage,
        ]);
    }

    public function export(Page $page): JsonResponse
    {
        $data = $this->pageBuilder->exportPage($page);

        return response()->json($data);
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'data.title' => 'required|string|max:255',
            'data.elements' => 'sometimes|array',
        ]);

        $page = $this->pageBuilder->importPage($validated['data']);

        return response()->json([
            'message' => 'Page imported successfully',
            'page' => $page,
        ], 201);
    }

    public function getData(Page $page): JsonResponse
    {
        $elements = $page->elements()->with('children')->get();
        $tree = $this->buildElementTree($elements);

        return response()->json([
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'settings' => $page->settings,
                'meta_data' => $page->meta_data,
                'template' => $page->template,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ],
            'elements' => $tree,
        ]);
    }

    public function render(Page $page): \Illuminate\Http\Response
    {
        $html = $this->pageBuilder->renderPage($page, [
            'with_container' => true,
            'theme' => request('theme', 'default'),
        ]);

        return response($html);
    }

    protected function buildElementTree($elements): array
    {
        $tree = [];

        foreach ($elements as $element) {
            $node = [
                'id' => $element->id,
                'uuid' => $element->uuid,
                'type' => $element->type,
                'name' => $element->name,
                'order' => $element->order,
                'settings' => $element->settings,
                'content' => $element->content,
                'styles' => $element->styles,
                'responsive_settings' => $element->responsive_settings,
                'animation' => $element->animation,
                'effects' => $element->effects,
                'column_size' => $element->column_size,
                'css_classes' => $element->css_classes,
                'css_id' => $element->css_id,
            ];

            if ($element->children->isNotEmpty()) {
                $node['children'] = $this->buildElementTree($element->children);
            }

            $tree[] = $node;
        }

        return $tree;
    }
}
