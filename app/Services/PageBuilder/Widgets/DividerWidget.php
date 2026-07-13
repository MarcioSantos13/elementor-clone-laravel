<?php

namespace App\Services\PageBuilder\Widgets;

class DividerWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'divider';
        $this->label = 'Divider';
        $this->icon = '➖';
        $this->categories = ['general'];
        $this->keywords = ['divider', 'separator', 'line', 'hr'];

        $this->defaultSettings = [
            'style' => 'solid',
            'width' => 100,
            'thickness' => 1,
            'color' => '#e2e8f0',
            'space_before' => 20,
            'space_after' => 20,
        ];

        $this->controls = [
            'style' => ['type' => 'select', 'label' => 'Style', 'options' => ['solid', 'dashed', 'dotted', 'double']],
            'width' => ['type' => 'number', 'label' => 'Width (%)', 'min' => 1, 'max' => 100],
            'thickness' => ['type' => 'number', 'label' => 'Thickness (px)', 'min' => 1, 'max' => 20],
            'color' => ['type' => 'color', 'label' => 'Color', 'tab' => 'style'],
            'space_before' => ['type' => 'number', 'label' => 'Space Before (px)', 'min' => 0, 'max' => 200],
            'space_after' => ['type' => 'number', 'label' => 'Space After (px)', 'min' => 0, 'max' => 200],
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
        $style = htmlspecialchars($settings['style'], ENT_QUOTES, 'UTF-8');
        $width = (int) $settings['width'];
        $thickness = (int) $settings['thickness'];
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $spaceBefore = (int) $settings['space_before'];
        $spaceAfter = (int) $settings['space_after'];

        $lineStyle = "border: none; border-top: {$thickness}px {$style} {$color}; width: {$width}%; margin: {$spaceBefore}px auto {$spaceAfter}px;";

        return "<hr class=\"pb-divider\" style=\"{$lineStyle}\">";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $style = htmlspecialchars($settings['style'], ENT_QUOTES, 'UTF-8');
        $width = (int) $settings['width'];
        $thickness = (int) $settings['thickness'];
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $spaceBefore = (int) $settings['space_before'];
        $spaceAfter = (int) $settings['space_after'];

        $lineStyle = "border: none; border-top: {$thickness}px {$style} {$color}; width: {$width}%; margin: {$spaceBefore}px auto {$spaceAfter}px;";

        return "<hr class=\"pb-divider-editor\" style=\"{$lineStyle}\">";
    }
}
