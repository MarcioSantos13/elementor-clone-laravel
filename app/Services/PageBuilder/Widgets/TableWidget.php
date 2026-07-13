<?php

namespace App\Services\PageBuilder\Widgets;

class TableWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'table';
        $this->label = 'Table';
        $this->icon = 'table-icon';
        $this->categories = ['basic', 'educational'];
        $this->keywords = ['table', 'data', 'grid', 'matrix'];

        $this->defaultSettings = [
            'content' => '<table><thead><tr><th>Coluna 1</th><th>Coluna 2</th><th>Coluna 3</th></tr></thead><tbody><tr><td>Dado 1</td><td>Dado 2</td><td>Dado 3</td></tr><tr><td>Dado 4</td><td>Dado 5</td><td>Dado 6</td></tr></tbody></table>',
            'header_bg' => '#f1f5f9',
            'header_color' => '#1e293b',
            'border_color' => '#e2e8f0',
            'stripe' => true,
            'stripe_color' => '#f8fafc',
            'alignment' => 'left',
            'font_size' => '14px',
            'padding' => '10px 14px',
            'border_radius' => '8px',
        ];

        $this->controls = [
            'content' => ['type' => 'wysiwyg', 'label' => 'Table Content (HTML)', 'required' => true],
            'header_bg' => ['type' => 'color', 'label' => 'Header Background', 'tab' => 'style'],
            'header_color' => ['type' => 'color', 'label' => 'Header Text Color', 'tab' => 'style'],
            'border_color' => ['type' => 'color', 'label' => 'Border Color', 'tab' => 'style'],
            'stripe' => ['type' => 'boolean', 'label' => 'Alternating Rows', 'tab' => 'style'],
            'alignment' => ['type' => 'select', 'label' => 'Text Alignment', 'options' => ['left', 'center', 'right'], 'tab' => 'style'],
            'font_size' => ['type' => 'text', 'label' => 'Font Size', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
            'dimensions' => ['type' => 'dimensions', 'label' => 'Padding & Margin', 'tab' => 'advanced'],
            'z_index' => ['type' => 'number', 'label' => 'Z-Index', 'tab' => 'advanced'],
            'css_classes' => ['type' => 'text', 'label' => 'CSS Classes', 'tab' => 'advanced'],
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'tab' => 'advanced'],
            'custom_css' => ['type' => 'custom_css', 'label' => 'Custom CSS', 'tab' => 'advanced'],
            'animation' => ['type' => 'animation', 'label' => 'Animation', 'tab' => 'advanced'],
            'visibility' => ['type' => 'visibility', 'label' => 'Responsive Visibility', 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $html = $settings['content'];
        $headerBg = $settings['header_bg'];
        $headerColor = $settings['header_color'];
        $borderColor = $settings['border_color'];
        $stripe = $settings['stripe'];
        $stripeColor = $settings['stripe_color'];
        $alignment = $settings['alignment'];
        $fontSize = $settings['font_size'];
        $cellPadding = $settings['padding'];
        $borderRadius = $settings['border_radius'];

        $tableStyle = "width:100%;border-collapse:collapse;font-size:" . $this->safeCssValue($fontSize) . ";text-align:" . $this->safeCssValue($alignment) . ";border-radius:" . $this->safeCssValue($borderRadius) . ";overflow:hidden;border:1px solid " . $this->safeCssValue($borderColor);
        $thStyle = "background:" . $this->safeCssValue($headerBg) . ";color:" . $this->safeCssValue($headerColor) . ";padding:" . $this->safeCssValue($cellPadding) . ";border:1px solid " . $this->safeCssValue($borderColor) . ";font-weight:600";
        $tdStyle = "padding:" . $this->safeCssValue($cellPadding) . ";border:1px solid " . $this->safeCssValue($borderColor);

        $stripStyle = function ($tag) {
            return preg_replace('/\s+style\s*=\s*"[^"]*"/i', '', $tag);
        };

        $styled = preg_replace_callback('/<table([^>]*)>/i', function ($m) use ($stripStyle, $tableStyle) {
            $attrs = $stripStyle($m[1]);
            return '<table' . $attrs . ' style="' . $tableStyle . '">';
        }, $html, 1);
        $styled = preg_replace_callback('/<th([^>]*)>/i', function ($m) use ($stripStyle, $thStyle) {
            $attrs = $stripStyle($m[1]);
            return '<th' . $attrs . ' style="' . $thStyle . '">';
        }, $styled);
        $styled = preg_replace_callback('/<td([^>]*)>/i', function ($m) use ($stripStyle, $tdStyle) {
            $attrs = $stripStyle($m[1]);
            return '<td' . $attrs . ' style="' . $tdStyle . '">';
        }, $styled);

        if ($stripe) {
            $rowIndex = 0;
            $styled = preg_replace_callback('/<tr([^>]*)>/i', function ($m) use (&$rowIndex, $stripeColor, $stripStyle) {
                $rowIndex++;
                $bg = ($rowIndex % 2 === 0) ? "background:{$stripeColor};" : '';
                $attrs = $stripStyle($m[1]);
                return '<tr' . $attrs . ' style="' . $bg . '">';
            }, $styled);
        }

        return "<div class=\"pb-table\" style=\"overflow-x:auto;margin-bottom:20px\">{$styled}</div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        return $this->render($settings, $content, $styles);
    }
}
