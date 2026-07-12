<?php

namespace App\Services\PageBuilder\Widgets;

class CalloutWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'callout';
        $this->label = 'Callout';
        $this->icon = 'callout-icon';
        $this->categories = ['basic', 'educational'];
        $this->keywords = ['callout', 'alert', 'note', 'tip', 'definition', 'theorem', 'exercise', 'warning'];

        $this->defaultSettings = [
            'type' => 'info',
            'title' => '',
            'content' => '<p>Conteúdo do callout aqui.</p>',
            'icon' => '',
            'show_icon' => true,
            'border_radius' => '8px',
            'padding' => '16px 20px',
            'margin_bottom' => '20px',
        ];

        $this->controls = [
            'type' => ['type' => 'select', 'label' => 'Type', 'options' => ['info', 'success', 'warning', 'danger', 'tip', 'definition', 'theorem', 'exercise', 'note']],
            'title' => ['type' => 'text', 'label' => 'Title'],
            'content' => ['type' => 'wysiwyg', 'label' => 'Content', 'required' => true],
            'show_icon' => ['type' => 'boolean', 'label' => 'Show Icon'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius'],
            'padding' => ['type' => 'text', 'label' => 'Padding'],
            'margin_bottom' => ['type' => 'text', 'label' => 'Margin Bottom'],
        ];
    }

    protected function getTypeConfig(string $type): array
    {
        $types = [
            'info' => [
                'bg' => '#eff6ff', 'border' => '#3b82f6', 'icon' => '&#9432;', 'label' => 'Informação',
                'title_color' => '#1e40af', 'text_color' => '#1e3a5f',
            ],
            'success' => [
                'bg' => '#f0fdf4', 'border' => '#22c55e', 'icon' => '&#10004;', 'label' => 'Sucesso',
                'title_color' => '#166534', 'text_color' => '#14532d',
            ],
            'warning' => [
                'bg' => '#fffbeb', 'border' => '#f59e0b', 'icon' => '&#9888;', 'label' => 'Atenção',
                'title_color' => '#92400e', 'text_color' => '#78350f',
            ],
            'danger' => [
                'bg' => '#fef2f2', 'border' => '#ef4444', 'icon' => '&#10060;', 'label' => 'Perigo',
                'title_color' => '#991b1b', 'text_color' => '#7f1d1d',
            ],
            'tip' => [
                'bg' => '#f0f9ff', 'border' => '#0ea5e9', 'icon' => '&#128161;', 'label' => 'Dica',
                'title_color' => '#0c4a6e', 'text_color' => '#0c3547',
            ],
            'definition' => [
                'bg' => '#faf5ff', 'border' => '#a855f7', 'icon' => '&#128214;', 'label' => 'Definição',
                'title_color' => '#6b21a8', 'text_color' => '#581c87',
            ],
            'theorem' => [
                'bg' => '#fff7ed', 'border' => '#f97316', 'icon' => '&#9878;', 'label' => 'Teorema',
                'title_color' => '#9a3412', 'text_color' => '#7c2d12',
            ],
            'exercise' => [
                'bg' => '#ecfdf5', 'border' => '#10b981', 'icon' => '&#9998;', 'label' => 'Exercício',
                'title_color' => '#065f46', 'text_color' => '#064e3b',
            ],
            'note' => [
                'bg' => '#f8fafc', 'border' => '#64748b', 'icon' => '&#128221;', 'label' => 'Nota',
                'title_color' => '#334155', 'text_color' => '#475569',
            ],
        ];

        return $types[$type] ?? $types['info'];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $type = $settings['type'];
        $config = $this->getTypeConfig($type);
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $text = $settings['content'];
        $showIcon = $settings['show_icon'];
        $borderRadius = $settings['border_radius'];
        $padding = $settings['padding'];
        $marginBottom = $settings['margin_bottom'];

        $iconHtml = $showIcon ? "<span style=\"font-size:1.3rem;margin-right:10px;flex-shrink:0\">{$config['icon']}</span>" : '';
        $titleHtml = $title ? "<div style=\"font-weight:700;font-size:1rem;margin-bottom:6px;color:{$config['title_color']}\">{$title}</div>" : '';

        $children = $content['children'] ?? '';
        $safeBR = $this->safeCssValue($borderRadius);
        $safePad = $this->safeCssValue($padding);
        $safeMB = $this->safeCssValue($marginBottom);

        return "<div class=\"pb-callout pb-callout-{$type}\" style=\"background:{$config['bg']};border-left:4px solid {$config['border']};border-radius:{$safeBR};padding:{$safePad};margin-bottom:{$safeMB};color:{$config['text_color']}\"><div style=\"display:flex;align-items:flex-start\">{$iconHtml}<div style=\"flex:1\">{$titleHtml}<div class=\"pb-callout-content\">{$text}{$children}</div></div></div></div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $type = $settings['type'];
        $config = $this->getTypeConfig($type);
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $text = $settings['content'];
        $showIcon = $settings['show_icon'];
        $borderRadius = $settings['border_radius'];
        $padding = $settings['padding'];
        $marginBottom = $settings['margin_bottom'];

        $iconHtml = $showIcon ? "<span style=\"font-size:1.3rem;margin-right:10px;flex-shrink:0\">{$config['icon']}</span>" : '';
        $titleHtml = $title ? "<div style=\"font-weight:700;font-size:1rem;margin-bottom:6px;color:{$config['title_color']}\">{$title}</div>" : '';

        $children = $content['children'] ?? '';
        $safeBR = $this->safeCssValue($borderRadius);
        $safePad = $this->safeCssValue($padding);
        $safeMB = $this->safeCssValue($marginBottom);

        return "<div class=\"pb-callout-editor\" style=\"background:{$config['bg']};border-left:4px solid {$config['border']};border-radius:{$safeBR};padding:{$safePad};margin-bottom:{$safeMB};color:{$config['text_color']}\"><div style=\"display:flex;align-items:flex-start\">{$iconHtml}<div style=\"flex:1\">{$titleHtml}<div>{$text}{$children}</div></div></div></div>";
    }
}
