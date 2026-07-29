<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\Element;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TemplateManager
{
    protected array $templates;

    public function __construct()
    {
        $this->templates = $this->defaultTemplates();
    }

    public function all(): array
    {
        return $this->templates;
    }

    public function list(): array
    {
        $list = [];
        foreach ($this->templates as $key => $tmpl) {
            $list[$key] = ['name' => $tmpl['name'], 'description' => $tmpl['description']];
        }
        return $list;
    }

    public function has(string $key): bool
    {
        return isset($this->templates[$key]);
    }

    public function get(string $key): ?array
    {
        return $this->templates[$key] ?? null;
    }

    public function apply(Page $page, string $templateKey): Page
    {
        $template = $this->get($templateKey);

        if (!$template) {
            throw new \InvalidArgumentException("Template '{$templateKey}' not found");
        }

        DB::beginTransaction();
        try {
            $page->elements()->delete();
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();

            $this->importElements($page, $template['elements']);

            DB::commit();

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importToPage(Page $page, string $templateKey): void
    {
        $template = $this->get($templateKey);
        if (!$template) return;

        $page->settings = array_merge($page->settings ?? [], $template['settings']);
        $page->save();

        $this->importElements($page, $template['elements']);
    }

    protected function importElements(Page $page, array $elements, ?int $parentId = null, ?string $widgetType = null): void
    {
        foreach ($elements as $index => $elData) {
            $type = $widgetType ?? $elData['type'];
            $children = $elData['children'] ?? [];

            $element = new Element();
            $element->page_id = $page->id;
            $element->parent_id = $parentId;
            $element->type = $type;
            $element->uuid = (string) Str::uuid();
            $element->name = $elData['settings']['name'] ?? ucfirst($type);
            $element->settings = $elData['settings'] ?? [];
            $element->content = [];
            $element->styles = [];
            $element->order = $index;
            $element->save();

            if ($children) {
                $childType = $type === 'section' ? 'column' : null;
                $this->importElements($page, $children, $element->id, $childType);
            }
        }
    }

    protected function defaultTemplates(): array
    {
        return [
            'blank' => [
                'name' => 'Blank Page',
                'description' => 'Start from scratch with an empty canvas',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [],
            ],
            'landing' => [
                'name' => 'Landing Page',
                'description' => 'Hero section with gradient background, features, and CTA',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => '135', 'color1' => '#0f172a', 'color2' => '#1e1b4b', 'position1' => '0', 'position2' => '100'], 'padding_top' => '120px', 'padding_bottom' => '120px', 'min_height' => '90vh', 'align_items' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Build Something Amazing', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '16px', 'letter_spacing' => '-1px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.25rem;color:#94a3b8;max-width:600px;margin:0 auto;">Create stunning websites with our intuitive drag-and-drop page builder. No coding required.</p>', 'alignment' => 'center', 'font_size' => '20px', 'line_height' => '1.7', 'margin_bottom' => '32px']],
                            ['type' => 'button', 'settings' => ['text' => 'Get Started Free', 'link' => '#', 'background_color' => '#6366f1', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '600', 'box_shadow' => '0 4px 20px rgba(99,102,241,.4)']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '60px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Powerful Features', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Everything you need to build professional websites in minutes</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px', 'padding_left' => '20px', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-bolt', 'icon_size' => '36', 'icon_color' => '#6366f1', 'title' => 'Lightning Fast', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Optimized for speed with automatic caching and lazy loading.</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px', 'padding_left' => '20px', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-mobile-alt', 'icon_size' => '36', 'icon_color' => '#6366f1', 'title' => 'Fully Responsive', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Every layout looks perfect on any device or screen size.</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px', 'padding_left' => '20px', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-puzzle-piece', 'icon_size' => '36', 'icon_color' => '#6366f1', 'title' => '50+ Widgets', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Rich library of widgets for any content type you need.</p>', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => '135', 'color1' => '#6366f1', 'color2' => '#8b5cf6', 'position1' => '0', 'position2' => '100'], 'padding_top' => '80px', 'padding_bottom' => '80px', 'border_radius' => '16px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Ready to Get Started?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#e0e7ff;max-width:560px;margin:0 auto 32px;font-size:1.1rem;">Join thousands of creators building amazing websites today.</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'button', 'settings' => ['text' => 'Start Building', 'link' => '#', 'background_color' => '#ffffff', 'text_color' => '#6366f1', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700', 'box_shadow' => '0 4px 16px rgba(0,0,0,.15)']],
                        ]],
                    ]],
                ],
            ],
            'about' => [
                'name' => 'About Page',
                'description' => 'Company presentation with hero, team, and stats sections',
                'settings' => ['container_width' => '1140px', 'page_background' => '#f8fafc', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '80px', 'border_radius' => '0px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Our Story', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-size:1.05rem;line-height:1.8;">Founded in 2020, we set out to democratize web design. Our drag-and-drop page builder empowers anyone to create professional websites without writing a single line of code.</p><p style="color:#475569;font-size:1.05rem;line-height:1.8;margin-top:16px;">Today, over 50,000 creators use our platform to build stunning websites for their businesses, portfolios, and passions.</p>', 'font_size' => '17px', 'line_height' => '1.8']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/about-us/600/500', 'alt' => 'About us', 'width' => '600', 'height' => '500'], 'width' => '100%', 'border_radius' => '12px', 'object_fit' => 'cover']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0f172a', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Active Users', 'number' => '50000', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Pages Built', 'number' => '250000', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Team Members', 'number' => '42', 'suffix' => '', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Countries', 'number' => '85', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.5rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Meet Our Team', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Talented people behind the product</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'image_box', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/team1/400/400', 'alt' => 'Sarah Johnson', 'width' => '400', 'height' => '400'], 'title' => 'Sarah Johnson', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">CEO & Co-Founder</p>', 'alignment' => 'center', 'border_radius' => '50%']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'image_box', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/team2/400/400', 'alt' => 'Michael Chen', 'width' => '400', 'height' => '400'], 'title' => 'Michael Chen', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">CTO & Co-Founder</p>', 'alignment' => 'center', 'border_radius' => '50%']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'image_box', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/team3/400/400', 'alt' => 'Emily Davis', 'width' => '400', 'height' => '400'], 'title' => 'Emily Davis', 'title_color' => '#0f172a', 'description' => '<p style="color:#64748b;">Head of Design</p>', 'alignment' => 'center', 'border_radius' => '50%']],
                        ]],
                    ]],
                ],
            ],
            'contact' => [
                'name' => 'Contact Page',
                'description' => 'Contact form with Google Maps integration',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Get in Touch', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">Have a question or want to work together? We would love to hear from you.</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_right' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Send Us a Message', 'tag' => 'h2', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '24px']],
                            ['type' => 'form', 'settings' => ['fields' => [['label' => 'Name', 'placeholder' => 'Your name', 'type' => 'text', 'required' => true, 'width' => '100'], ['label' => 'Email', 'placeholder' => 'Your email', 'type' => 'email', 'required' => true, 'width' => '100'], ['label' => 'Subject', 'placeholder' => 'Subject', 'type' => 'text', 'required' => true, 'width' => '100'], ['label' => 'Message', 'placeholder' => 'Your message', 'type' => 'textarea', 'required' => true, 'width' => '100']], 'submit_text' => 'Send Message', 'button_background' => '#6366f1', 'button_text_color' => '#ffffff', 'button_border_radius' => '8px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Find Us Here', 'tag' => 'h2', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '24px']],
                            ['type' => 'google_maps', 'settings' => ['address' => '123 Main Street, New York, NY 10001', 'zoom' => '14', 'height' => '400px', 'border_radius' => '12px']],
                        ]],
                    ]],
                ],
            ],
            'portfolio' => [
                'name' => 'Portfolio Page',
                'description' => 'Showcase your work with gallery, testimonials, and more',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Our Work', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Explore our latest projects and case studies</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '20px', 'padding_bottom' => '60px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '30px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="display:inline-flex;gap:12px;flex-wrap:wrap;justify-content:center;"><span style="background:#6366f1;color:#fff;padding:8px 20px;border-radius:50px;font-weight:600;cursor:pointer;">All</span><span style="background:#f1f5f9;color:#475569;padding:8px 20px;border-radius:50px;cursor:pointer;">Web Design</span><span style="background:#f1f5f9;color:#475569;padding:8px 20px;border-radius:50px;cursor:pointer;">Branding</span><span style="background:#f1f5f9;color:#475569;padding:8px 20px;border-radius:50px;cursor:pointer;">Mobile</span></p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'gallery', 'settings' => ['images' => [['url' => 'https://picsum.photos/seed/port1/600/500', 'alt' => 'Project 1', 'width' => '600', 'height' => '500'], ['url' => 'https://picsum.photos/seed/port2/600/500', 'alt' => 'Project 2', 'width' => '600', 'height' => '500'], ['url' => 'https://picsum.photos/seed/port3/600/500', 'alt' => 'Project 3', 'width' => '600', 'height' => '500']], 'columns' => '3', 'gap' => '16', 'border_radius' => '12px', 'image_height' => '320px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'What Clients Say', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Hear from our satisfied clients</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Working with this team was an absolute pleasure. They brought our vision to life and exceeded every expectation."</p>', 'name' => 'Alex Rivera', 'position' => 'CEO', 'company' => 'TechVentures', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"The quality of design and attention to detail is unmatched. Highly recommend for any digital project."</p>', 'name' => 'Lisa Park', 'position' => 'Marketing Director', 'company' => 'BrandStudio', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"A game-changer for our business. The platform is intuitive and the results speak for themselves."</p>', 'name' => 'James Wilson', 'position' => 'Founder', 'company' => 'GrowthLab', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                    ]],
                ],
            ],
            'blog-post' => [
                'name' => 'Blog Post',
                'description' => 'Single blog post with featured image, content, author box, and share buttons',
                'settings' => ['container_width' => '800px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '0px', 'padding_bottom' => '0px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/blog-hero/1200/600', 'alt' => 'Featured image', 'width' => '1200', 'height' => '600'], 'width' => '100%', 'border_radius' => '0px', 'object_fit' => 'cover', 'height' => '400px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Getting Started with Modern Web Design', 'tag' => 'h1', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '800', 'margin_bottom' => '12px', 'letter_spacing' => '-.5px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;font-size:0.9rem;">By Sarah Johnson &bull; Published on March 15, 2026 &bull; 5 min read</p>', 'alignment' => 'left', 'font_size' => '14px', 'margin_bottom' => '32px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#334155;line-height:1.8;font-size:1.05rem;">Web design has evolved dramatically over the past decade. Gone are the days when building a beautiful website required extensive coding knowledge and months of development time. Today, drag-and-drop page builders have democratized web design, enabling anyone to create professional-grade websites in a fraction of the time.</p><p style="color:#334155;line-height:1.8;font-size:1.05rem;margin-top:20px;">In this post, we will explore the essential principles of modern web design and how you can leverage our page builder to create stunning layouts without writing a single line of code.</p><h2 style="color:#0f172a;font-size:1.5rem;font-weight:700;margin-top:32px;margin-bottom:16px;">Why Visual Building Matters</h2><p style="color:#334155;line-height:1.8;font-size:1.05rem;">Visual page builders have transformed the web development landscape. They provide real-time feedback, eliminate the back-and-forth between design and development, and empower content creators to maintain their own websites.</p><p style="color:#334155;line-height:1.8;font-size:1.05rem;margin-top:16px;">Whether you are a business owner, a marketer, or a creative professional, being able to build and iterate on your website quickly is a superpower in today digital-first world.</p><h2 style="color:#0f172a;font-size:1.5rem;font-weight:700;margin-top:32px;margin-bottom:16px;">Key Features of Our Page Builder</h2><ul style="color:#334155;line-height:1.8;font-size:1.05rem;"><li>Intuitive drag-and-drop interface with live preview</li><li>50+ professionally designed widgets and elements</li><li>Fully responsive layouts that look great on any device</li><li>Custom CSS and JavaScript for advanced users</li><li>Built-in SEO tools and performance optimization</li></ul><p style="color:#334155;line-height:1.8;font-size:1.05rem;margin-top:20px;">Start building your dream website today. No coding required, just pure creativity.</p>', 'font_size' => '16px', 'line_height' => '1.8']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '32px', 'padding_bottom' => '32px', 'border_radius' => '12px', 'margin_top' => '20px', 'margin_bottom' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/author/150/150', 'alt' => 'Sarah Johnson', 'width' => '150', 'height' => '150'], 'width' => '80px', 'border_radius' => '50%', 'object_fit' => 'cover']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-9', 'text_align' => 'left'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'About the Author', 'content' => '<p style="color:#475569;">Sarah Johnson is the CEO and co-founder of our platform. With over 15 years of experience in web design and development, she is passionate about making web creation accessible to everyone.</p>']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '20px', 'padding_bottom' => '40px', 'border_top' => '1px solid #e2e8f0'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#0f172a;font-weight:600;margin-bottom:12px;">Share this post:</p>', 'alignment' => 'left', 'font_size' => '14px']],
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'facebook', 'url' => '#', 'color' => '#1877F2'], ['platform' => 'twitter', 'url' => '#', 'color' => '#1DA1F2'], ['platform' => 'linkedin', 'url' => '#', 'color' => '#0A66C2'], ['platform' => 'pinterest', 'url' => '#', 'color' => '#E60023'], ['platform' => 'whatsapp', 'url' => '#', 'color' => '#25D366']], 'columns' => '5', 'icon_size' => '40', 'gap' => '10', 'alignment' => 'left']],
                        ]],
                    ]],
                ],
            ],
        ];
    }
}
