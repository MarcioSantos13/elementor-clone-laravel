<?php

namespace App\Services\PageBuilder\Widgets;

class TextWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'text';
        $this->label = 'Text Editor';
        $this->icon = 'text-icon';
        $this->categories = ['basic', 'typography'];
        $this->keywords = ['text', 'paragraph', 'content', 'editor'];

        $this->defaultSettings = [
            'content' => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.</p>',
            'alignment' => 'left',
            'color' => '#666666',
            'font_size' => '16px',
            'font_family' => '',
            'font_weight' => '400',
            'line_height' => '1.7',
            'margin_bottom' => '20px',
            'drop_cap' => false,
            'column_count' => 1,
            'column_gap' => 'normal',
        ];

        $this->controls = [
            'content' => ['type' => 'textarea', 'label' => 'Content', 'required' => true],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right', 'justify']],
            'color' => ['type' => 'color', 'label' => 'Color'],
            'font_size' => ['type' => 'number', 'label' => 'Font Size', 'min' => 8, 'max' => 200],
            'font_family' => ['type' => 'text', 'label' => 'Font Family'],
            'line_height' => ['type' => 'number', 'label' => 'Line Height', 'min' => 0.5, 'max' => 5],
            'drop_cap' => ['type' => 'boolean', 'label' => 'Drop Cap'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = array_merge($this->defaultSettings, $settings);
        $text = $settings['content'];
        $alignment = $settings['alignment'];
        $color = $settings['color'];
        $fontSize = $settings['font_size'];
        $fontWeight = $settings['font_weight'];
        $lineHeight = $settings['line_height'];
        $fontFamily = $settings['font_family'];

        $style = "text-align: {$alignment}; color: {$color}; font-size: {$fontSize}; font-weight: {$fontWeight}; line-height: {$lineHeight};";

        if ($fontFamily) {
            $style .= " font-family: {$fontFamily};";
        }

        if ($settings['drop_cap']) {
            $style .= ' .pb-text:first-letter { font-size: 3em; float: left; line-height: 1; margin-right: 10px; }';
        }

        $columnStyle = '';
        if ($settings['column_count'] > 1) {
            $columnStyle = " column-count: {$settings['column_count']}; column-gap: {$settings['column_gap']};";
        }

        return "<div class=\"pb-text\" style=\"{$style}{$columnStyle}\">{$text}</div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = array_merge($this->defaultSettings, $settings);

        return "<div class=\"pb-text-editor\">{$settings['content']}</div>";
    }
}
