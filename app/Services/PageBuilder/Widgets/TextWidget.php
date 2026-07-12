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
            'content' => ['type' => 'wysiwyg', 'label' => 'Content', 'required' => true],
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
        $settings = $this->prepareSettings($settings);
        $text = $settings['content'];
        $alignment = $settings['alignment'];
        $color = $settings['color'];
        $fontSize = $settings['font_size'];
        $fontWeight = $settings['font_weight'];
        $lineHeight = $settings['line_height'];
        $fontFamily = $settings['font_family'];
        $children = $content['children'] ?? '';

        $style = "text-align: {$this->safeCssValue($alignment)}; color: {$this->safeCssValue($color)}; font-size: {$this->safeCssValue($fontSize)}; font-weight: {$this->safeCssValue($fontWeight)}; line-height: {$this->safeCssValue($lineHeight)};";

        if ($fontFamily) {
            $style .= " font-family: {$this->safeCssValue($fontFamily)};";
        }

        $dropCss = '';
        if ($settings['drop_cap']) {
            $dropCss = ' pb-drop-cap';
        }

        $columnStyle = '';
        if ($settings['column_count'] > 1) {
            $columnStyle = " column-count: {$settings['column_count']}; column-gap: {$this->safeCssValue($settings['column_gap'])};";
        }

        return "<div class=\"pb-text{$dropCss}\" style=\"{$style}{$columnStyle}\">{$text}{$children}</div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $text = $settings['content'];
        $alignment = $settings['alignment'];
        $color = $settings['color'];
        $fontSize = $settings['font_size'];
        $fontWeight = $settings['font_weight'];
        $lineHeight = $settings['line_height'];
        $fontFamily = $settings['font_family'];
        $children = $content['children'] ?? '';

        $style = "text-align: {$this->safeCssValue($alignment)}; color: {$this->safeCssValue($color)}; font-size: {$this->safeCssValue($fontSize)}; font-weight: {$this->safeCssValue($fontWeight)}; line-height: {$this->safeCssValue($lineHeight)};";

        if ($fontFamily) {
            $style .= " font-family: {$this->safeCssValue($fontFamily)};";
        }

        $columnStyle = '';
        if ($settings['column_count'] > 1) {
            $columnStyle = " column-count: {$settings['column_count']}; column-gap: {$this->safeCssValue($settings['column_gap'])};";
        }

        return "<div class=\"pb-text-editor\" style=\"{$style}{$columnStyle}\">{$text}{$children}</div>";
    }
}
