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
            'showcase' => [
                'name' => 'Showcase Completo',
                'description' => 'Template futurista completo: hero com vídeo, botões com links, contadores, ícones, progress bars, countdown, galeria com lightbox, carousel, imagens com link, tabs, accordion, preços, depoimentos, animação Lottie, formulário, mapa e redes sociais',
                'settings' => ['container_width' => '1140px', 'page_background' => '#0b1120', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 160, 'color1' => '#020617', 'color2' => '#312e81', 'position1' => 0, 'position2' => 100], 'padding_top' => '100px', 'padding_bottom' => '100px', 'min_height' => '90vh', 'align_items' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_right' => '40px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'tip', 'title' => 'Nova versão 3.0', 'content' => '<p>Interface mais rápida, inteligente e futurista.</p>', 'show_icon' => true, 'border_radius' => '50px', 'margin_bottom' => '24px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Construa o Futuro com o Page Builder', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#f8fafc', 'alignment' => 'left', 'font_weight' => '800', 'letter_spacing' => '-1px', 'margin_bottom' => '16px', 'text_shadow' => '0 0 24px rgba(129,140,248,.55)']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.2rem;color:#a5b4fc;line-height:1.7;margin:0;">Uma plataforma visual que combina <strong style="color:#e0e7ff;">vídeos</strong>, <strong style="color:#e0e7ff;">imagens</strong>, <strong style="color:#e0e7ff;">animações</strong> e <strong style="color:#e0e7ff;">links</strong> — arraste, solte e publique sem escrever nada de código.</p>', 'alignment' => 'left', 'font_size' => '19px', 'line_height' => '1.7', 'margin_bottom' => '32px']],
                            ['type' => 'button', 'settings' => ['text' => 'Começar Agora', 'link' => '#precos', 'link_target' => '_self', 'size' => 'large', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#a855f7', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '32px', 'font_weight' => '600', 'box_shadow' => '0 8px 32px rgba(139,92,246,.5)', 'alignment' => 'left']],
                            ['type' => 'button', 'settings' => ['text' => '▶ Ver Demonstração', 'link' => '#video', 'link_target' => '_self', 'size' => 'large', 'background_type' => 'classic', 'background_color' => 'transparent', 'text_color' => '#e2e8f0', 'border_color' => '#6366f1', 'border_width' => '2px', 'border_radius' => '50px', 'padding_left_right' => '32px', 'font_weight' => '600', 'alignment' => 'left']],
                            ['type' => 'divider', 'settings' => ['width' => '100', 'thickness' => '1', 'color' => '#1e293b', 'space_before' => '32', 'space_after' => '16']],
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'github', 'url' => 'https://github.com', 'color' => '#94a3b8'], ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'color' => '#0A66C2'], ['platform' => 'twitter', 'url' => 'https://twitter.com', 'color' => '#1DA1F2'], ['platform' => 'youtube', 'url' => 'https://youtube.com', 'color' => '#FF0000']], 'columns' => '4', 'icon_size' => '36', 'gap' => '12', 'alignment' => 'left']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_left' => '40px'], 'children' => [
                            ['type' => 'video', 'settings' => ['video_type' => 'youtube', 'video_url' => 'https://www.youtube.com/watch?v=ScMzIvxBSi4', 'aspect_ratio' => '16:9', 'width' => '100%', 'alignment' => 'center', 'controls' => true, 'autoplay' => false, 'loop' => false, 'mute' => false]],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0b1120', 'padding_top' => '40px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Números que Inspiram', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Contadores animados com prefixo e sufixo</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'divider', 'settings' => ['width' => '60', 'thickness' => '3', 'color' => '#22d3ee', 'space_before' => '24', 'space_after' => '0']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '60px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Usuários Ativos', 'suffix' => 'K+', 'number' => '250', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#22d3ee', 'font_size' => '2.6rem']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Páginas Criadas', 'suffix' => 'M', 'number' => '1.5', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#a78bfa', 'font_size' => '2.6rem']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Widgets', 'suffix' => '+', 'number' => '30', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#34d399', 'font_size' => '2.6rem']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Países', 'prefix' => 'R$', 'suffix' => 'M', 'number' => '12', 'duration' => '2000', 'separator' => false, 'alignment' => 'center', 'color' => '#f0abfc', 'font_size' => '2.6rem']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Recursos Futuristas', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Tudo que você precisa para construir páginas impressionantes</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'divider', 'settings' => ['width' => '60', 'thickness' => '3', 'color' => '#22d3ee', 'space_before' => '24', 'space_after' => '0']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#111827', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-bolt', 'size' => '44', 'color' => '#22d3ee', 'alignment' => 'center', 'margin_bottom' => '16px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Rápido & Leve', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Performance otimizada com cache inteligente e carregamento preguiçoso (lazy loading).</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'progress_bar', 'settings' => ['title' => 'Performance', 'percentage' => '98', 'color' => '#22d3ee', 'background_color' => '#1e293b', 'border_radius' => '10px', 'alignment' => 'left', 'margin_top' => '24px', 'animate' => true]],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#111827', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-mobile-alt', 'size' => '44', 'color' => '#a78bfa', 'alignment' => 'center', 'margin_bottom' => '16px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Responsivo', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Layout adaptável para celular (375px), tablet (768px) e desktop automaticamente.</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'progress_bar', 'settings' => ['title' => 'Cobertura', 'percentage' => '100', 'color' => '#a78bfa', 'background_color' => '#1e293b', 'border_radius' => '10px', 'alignment' => 'left', 'margin_top' => '24px', 'animate' => true]],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#111827', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'icon', 'settings' => ['icon' => 'fas fa-puzzle-piece', 'size' => '44', 'color' => '#34d399', 'alignment' => 'center', 'margin_bottom' => '16px']],
                            ['type' => 'heading', 'settings' => ['title' => '30+ Widgets', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Biblioteca completa de widgets para qualquer tipo de conteúdo: vídeo, imagem, preços, contagem regressiva e mais.</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'progress_bar', 'settings' => ['title' => 'Cobertura Visual', 'percentage' => '85', 'color' => '#34d399', 'background_color' => '#1e293b', 'border_radius' => '10px', 'alignment' => 'left', 'margin_top' => '24px', 'animate' => true]],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Galeria de Imagens', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Galeria em grade, carousel e lightbox</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'divider', 'settings' => ['width' => '60', 'thickness' => '3', 'color' => '#22d3ee', 'space_before' => '24', 'space_after' => '0']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                            ['type' => 'gallery', 'settings' => ['images' => [
                                ['url' => 'https://picsum.photos/seed/pb-gallery-1/600/400', 'alt' => 'Projeto futurista 1', 'caption' => 'Dashboard', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-2/600/400', 'alt' => 'Projeto futurista 2', 'caption' => 'App Mobile', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-3/600/400', 'alt' => 'Projeto futurista 3', 'caption' => 'Website', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-4/600/400', 'alt' => 'Projeto futurista 4', 'caption' => 'Branding', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-5/600/400', 'alt' => 'Projeto futurista 5', 'caption' => 'Identidade Visual', 'width' => 600, 'height' => 400],
                                ['url' => 'https://picsum.photos/seed/pb-gallery-6/600/400', 'alt' => 'Projeto futurista 6', 'caption' => 'Social Media', 'width' => 600, 'height' => 400],
                            ], 'columns' => '3', 'gap' => '16', 'layout' => 'grid', 'show_caption' => true, 'border_radius' => '12', 'image_size' => 'cover']],
                            ['type' => 'carousel', 'settings' => ['images' => [
                                ['url' => 'https://picsum.photos/seed/pb-carousel-1/800/450', 'alt' => 'Slide 1', 'caption' => ''],
                                ['url' => 'https://picsum.photos/seed/pb-carousel-2/800/450', 'alt' => 'Slide 2', 'caption' => ''],
                                ['url' => 'https://picsum.photos/seed/pb-carousel-3/800/450', 'alt' => 'Slide 3', 'caption' => ''],
                            ], 'columns' => '2', 'autoplay' => true, 'loop' => true, 'show_dots' => true, 'show_arrows' => true, 'speed' => '3000', 'border_radius' => '16px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Imagens com Links', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Imagens com lightbox, link externo e efeitos de hover</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/pb-link-lightbox/600/400', 'alt' => 'Imagem com lightbox', 'width' => 600, 'height' => 400], 'caption' => 'Clique para abrir no lightbox', 'width' => '100%', 'border_radius' => '16px', 'enable_lightbox' => true, 'hover_animation' => 'zoom']],
                            ['type' => 'heading', 'settings' => ['title' => 'Saiba Mais', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '12px', 'link' => 'https://example.com', 'link_target' => '_blank']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Adicione <strong style="color:#e0e7ff;">links</strong> a qualquer imagem ou título: abra em nova aba, use âncoras da própria página ou aponte para qualquer URL.</p>', 'alignment' => 'left', 'font_size' => '15px', 'line_height' => '1.7']],
                            ['type' => 'button', 'settings' => ['text' => 'Visitar Site', 'link' => 'https://example.com', 'link_target' => '_blank', 'size' => 'medium', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#06b6d4', 'color2' => '#6366f1', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '24px', 'font_weight' => '600', 'alignment' => 'left']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'center', 'padding_left' => '20px'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/pb-link-external/600/400', 'alt' => 'Imagem com link externo', 'width' => 600, 'height' => 400], 'caption' => 'Abre em uma nova aba', 'width' => '100%', 'border_radius' => '16px', 'link' => 'https://example.com', 'link_target' => '_blank', 'hover_animation' => 'zoom']],
                            ['type' => 'video', 'settings' => ['video_type' => 'vimeo', 'video_url' => 'https://vimeo.com/76979871', 'aspect_ratio' => '16:9', 'width' => '100%', 'alignment' => 'center', 'controls' => true, 'autoplay' => false, 'loop' => false, 'mute' => false]],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Widgets Interativos', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Abas, accordion, progress bars e contagem regressiva</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'padding_right' => '20px'], 'children' => [
                            ['type' => 'tabs', 'settings' => ['tabs' => [
                                ['title' => 'Descrição', 'content' => '<p style="color:#cbd5e1;">O Page Builder foi projetado para ser intuitivo. Com a interface drag-and-drop você cria páginas complexas em minutos, não em horas.</p>'],
                                ['title' => 'Funcionalidades', 'content' => '<ul style="color:#cbd5e1;"><li>30+ widgets</li><li>Estilos inline</li><li>Responsivo</li><li>Colaboração</li><li>Revisões</li></ul>'],
                                ['title' => 'Preços', 'content' => '<p style="color:#cbd5e1;">Gratuito para uso pessoal. Planos premium a partir de R$29/mês com recursos avançados.</p>'],
                            ], 'orientation' => 'horizontal', 'active_tab' => 0, 'tab_color' => '#22d3ee', 'border_color' => '#1e293b', 'content_padding' => 20]],
                            ['type' => 'countdown', 'settings' => ['target_date' => '2027-12-31', 'days_label' => 'Dias', 'hours_label' => 'Horas', 'minutes_label' => 'Min', 'seconds_label' => 'Seg', 'alignment' => 'center', 'color' => '#f0abfc', 'background_color' => '#1e293b']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'padding_left' => '20px'], 'children' => [
                            ['type' => 'accordion', 'settings' => ['items' => [
                                ['title' => 'Como funciona o construtor?', 'content' => '<p style="color:#cbd5e1;">Arraste widgets do painel esquerdo para a tela. Cada widget tem configurações de conteúdo, estilo e avançado no painel direito.</p>', 'open' => true],
                                ['title' => 'Preciso saber programar?', 'content' => '<p style="color:#cbd5e1;">Não! O Page Builder foi feito para não-programadores. Todo o desenvolvimento é visual, sem código.</p>'],
                                ['title' => 'Posso exportar o HTML?', 'content' => '<p style="color:#cbd5e1;">Sim! Use o botão "Copy HTML" no editor para copiar o HTML renderizado. Ideal para usar no Moodle, WordPress ou qualquer CMS.</p>'],
                            ], 'tab_color' => '#22d3ee', 'border_color' => '#1e293b']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Preços', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Tabelas de preço com botões de link</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Starter', 'price' => '29', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => '1 página', 'included' => true], ['text' => 'Widgets básicos', 'included' => true], ['text' => 'Suporte padrão', 'included' => true], ['text' => 'Domínio personalizado', 'included' => false], ['text' => 'API Access', 'included' => false]], 'button_text' => 'Começar', 'button_link' => '#contato', 'featured' => false, 'featured_color' => '#22d3ee']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Pro', 'price' => '79', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => 'Páginas ilimitadas', 'included' => true], ['text' => 'Todos os widgets', 'included' => true], ['text' => 'Suporte prioritário', 'included' => true], ['text' => 'Domínio personalizado', 'included' => true], ['text' => 'API Access', 'included' => true]], 'button_text' => 'Assinar Pro', 'button_link' => '#contato', 'featured' => true, 'featured_color' => '#a855f7']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Enterprise', 'price' => '199', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => 'Equipes ilimitadas', 'included' => true], ['text' => 'SSO e auditoria', 'included' => true], ['text' => 'Suporte dedicado', 'included' => true], ['text' => 'SLA garantido', 'included' => true], ['text' => 'API Access', 'included' => true]], 'button_text' => 'Falar com Vendas', 'button_link' => '#contato', 'featured' => false, 'featured_color' => '#34d399']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '40px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'O Que Nossos Usuários Dizem', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Mais de 250 mil criadores confiam no Page Builder</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#1e293b', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.3)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"O Page Builder transformou a forma como criamos conteúdo. Intuitivo e poderoso."</p>', 'name' => 'Ana Silva', 'position' => 'CEO', 'company' => 'TechStart', 'avatar' => ['url' => 'https://picsum.photos/seed/avatar-ana/150/150', 'alt' => 'Ana Silva', 'width' => 150, 'height' => 150], 'rating' => '5', 'alignment' => 'center', 'text_color' => '#cbd5e1', 'name_color' => '#f8fafc']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#1e293b', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.3)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Finalmente um construtor que entrega qualidade profissional sem complicação."</p>', 'name' => 'Carlos Oliveira', 'position' => 'Marketing Director', 'company' => 'BrandLab', 'avatar' => ['url' => 'https://picsum.photos/seed/avatar-carlos/150/150', 'alt' => 'Carlos Oliveira', 'width' => 150, 'height' => 150], 'rating' => '5', 'alignment' => 'center', 'text_color' => '#cbd5e1', 'name_color' => '#f8fafc']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#1e293b', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '28px', 'padding_right' => '28px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.3)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Uso no Moodle para criar páginas educacionais. O Copy HTML é fantástico!"</p>', 'name' => 'Marina Costa', 'position' => 'Professora', 'company' => 'Universidade Federal', 'avatar' => ['url' => 'https://picsum.photos/seed/avatar-marina/150/150', 'alt' => 'Marina Costa', 'width' => 150, 'height' => 150], 'rating' => '5', 'alignment' => 'center', 'text_color' => '#cbd5e1', 'name_color' => '#f8fafc']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'radial', 'angle' => 0, 'color1' => '#1e1b4b', 'color2' => '#0b1120', 'position1' => 0, 'position2' => 100], 'padding_top' => '80px', 'padding_bottom' => '80px', 'align_items' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'lottie', 'settings' => ['lottie_url' => 'https://assets2.lottiefiles.com/packages/lf20_g1al0rje.json', 'loop' => true, 'autoplay' => true, 'speed' => '1', 'width' => '220', 'height' => '220', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Pronto para Começar?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f8fafc', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#a5b4fc;max-width:560px;margin:0 auto 32px;font-size:1.1rem;">Junte-se a milhares de criadores e construa sua página agora.</p>', 'alignment' => 'center', 'font_size' => '17px']],
                            ['type' => 'button', 'settings' => ['text' => 'Criar Página Grátis', 'link' => '#contato', 'link_target' => '_self', 'size' => 'large', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#ec4899', 'position1' => 0, 'position2' => 100], 'text_color' => '#ffffff', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700', 'box_shadow' => '0 8px 32px rgba(236,72,153,.45)', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '40px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Entre em Contato', 'tag' => 'h2', 'size' => 'xl', 'color' => '#f1f5f9', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;max-width:540px;margin:0 auto;">Tem alguma dúvida? Mande uma mensagem para nós</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0b1120', 'padding_top' => '0px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'background_color' => '#111827', 'padding_top' => '32px', 'padding_bottom' => '32px', 'padding_left' => '32px', 'padding_right' => '32px', 'border_radius' => '16px', 'box_shadow' => '0 8px 32px rgba(0,0,0,.35)'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Formulário de Contato', 'tag' => 'h3', 'size' => 'medium', 'color' => '#f1f5f9', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '24px']],
                            ['type' => 'form', 'settings' => ['form_name' => 'Contato Showcase', 'fields' => [['label' => 'Nome', 'placeholder' => 'Seu nome', 'type' => 'text', 'required' => true], ['label' => 'Email', 'placeholder' => 'seu@email.com', 'type' => 'email', 'required' => true], ['label' => 'Assunto', 'placeholder' => 'Assunto', 'type' => 'select', 'required' => true], ['label' => 'Mensagem', 'placeholder' => 'Sua mensagem', 'type' => 'textarea', 'required' => true, 'width' => '100']], 'button_text' => 'Enviar Mensagem', 'success_message' => 'Recebemos sua mensagem! Obrigado pelo contato.', 'button_color' => '#a855f7', 'button_text_color' => '#ffffff', 'button_width' => 'full', 'field_spacing' => 14, 'field_radius' => 8]],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_left' => '20px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Informações de Contato', 'content' => '<p style="color:#cbd5e1;"><strong style="color:#f1f5f9;">Endereço:</strong> Av. Paulista, 1000<br><strong style="color:#f1f5f9;">Telefone:</strong> (11) 99999-9999<br><strong style="color:#f1f5f9;">Email:</strong> contato@pagebuilder.com<br><strong style="color:#f1f5f9;">Horário:</strong> Seg-Sex, 9h-18h</p>', 'show_icon' => true, 'border_radius' => '12px']],
                            ['type' => 'google_maps', 'settings' => ['address' => 'Av. Paulista, 1000, São Paulo, SP', 'latitude' => '-23.5632', 'longitude' => '-46.6542', 'zoom' => '15', 'height' => '280px', 'map_type' => 'roadmap', 'border_radius' => '16px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#020617', 'padding_top' => '60px', 'padding_bottom' => '60px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'facebook', 'url' => 'https://facebook.com', 'color' => '#1877F2'], ['platform' => 'twitter', 'url' => 'https://twitter.com', 'color' => '#1DA1F2'], ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'color' => '#0A66C2'], ['platform' => 'instagram', 'url' => 'https://instagram.com', 'color' => '#E4405F'], ['platform' => 'youtube', 'url' => 'https://youtube.com', 'color' => '#FF0000'], ['platform' => 'github', 'url' => 'https://github.com', 'color' => '#94a3b8']], 'columns' => '6', 'icon_size' => '40', 'gap' => '12', 'alignment' => 'center']],
                            ['type' => 'divider', 'settings' => ['width' => '100', 'thickness' => '1', 'color' => '#1e293b', 'space_before' => '32', 'space_after' => '32']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;font-size:0.85rem;">&copy; 2026 Page Builder. Todos os direitos reservados. &bull; <a href="#" style="color:#a5b4fc;">Termos</a> &bull; <a href="#" style="color:#a5b4fc;">Privacidade</a></p>', 'alignment' => 'center', 'font_size' => '14px']],
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
