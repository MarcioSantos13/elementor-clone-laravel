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
            'header_bg' => ['type' => 'color', 'label' => 'Header Background'],
            'header_color' => ['type' => 'color', 'label' => 'Header Text Color'],
            'border_color' => ['type' => 'color', 'label' => 'Border Color'],
            'stripe' => ['type' => 'boolean', 'label' => 'Alternating Rows'],
            'alignment' => ['type' => 'select', 'label' => 'Text Alignment', 'options' => ['left', 'center', 'right']],
            'font_size' => ['type' => 'text', 'label' => 'Font Size'],
            'padding' => ['type' => 'text', 'label' => 'Cell Padding'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius'],
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

        $tableStyle = "width:100%;border-collapse:collapse;font-size:{$fontSize};text-align:{$alignment};border-radius:{$borderRadius};overflow:hidden;border:1px solid {$borderColor}";

        $styled = preg_replace('/<table([^>]*)>/i', '<table$1 style="' . $tableStyle . '">', $html, 1);
        $styled = preg_replace('/<th([^>]*)>/i', '<th$1 style="background:' . $headerBg . ';color:' . $headerColor . ';padding:' . $cellPadding . ';border:1px solid ' . $borderColor . ';font-weight:600">', $styled);
        $styled = preg_replace('/<td([^>]*)>/i', '<td$1 style="padding:' . $cellPadding . ';border:1px solid ' . $borderColor . '">', $styled);

        if ($stripe) {
            $rowIndex = 0;
            $styled = preg_replace_callback('/<tr([^>]*)>/i', function ($m) use (&$rowIndex, $stripeColor) {
                $rowIndex++;
                $bg = ($rowIndex % 2 === 0) ? " background:{$stripeColor};" : '';
                return "<tr{$m[1]} style=\"{$bg}\">";
            }, $styled);
        }

        return "<div class=\"pb-table\" style=\"overflow-x:auto;margin-bottom:20px\">{$styled}</div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        return $this->render($settings, $content, $styles);
    }
}
