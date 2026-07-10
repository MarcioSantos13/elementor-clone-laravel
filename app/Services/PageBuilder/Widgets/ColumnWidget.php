<?php

namespace App\Services\PageBuilder\Widgets;

class ColumnWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'column';
        $this->label = 'Column';
        $this->icon = 'column-icon';
        $this->categories = ['layout', 'structure'];
        $this->container = true;
        $this->keywords = ['column', 'col', 'layout', 'structure', 'grid'];

        $this->defaultSettings = [
            'column_width' => 'col-4',
            'column_width_mobile' => 'col-12',
            'column_width_tablet' => '',
            'vertical_alignment' => 'stretch',
            'content_position' => 'top',
            'background_type' => 'none',
            'background_color' => 'transparent',
            'background_image' => [],
            'padding_top' => '10px',
            'padding_bottom' => '10px',
            'padding_left' => '10px',
            'padding_right' => '10px',
            'margin' => '0px',
            'border_radius' => '0px',
            'box_shadow' => 'none',
            'css_id' => '',
            'css_classes' => '',
            'responsive_hide_mobile' => false,
            'responsive_hide_tablet' => false,
            'responsive_hide_desktop' => false,
        ];

        $this->controls = [
            'column_width' => ['type' => 'select', 'label' => 'Column Width',
                'options' => [
                    'col-1', 'col-2', 'col-3', 'col-4', 'col-5', 'col-6',
                    'col-7', 'col-8', 'col-9', 'col-10', 'col-11', 'col-12',
                ],
                'default' => 'col-4',
            ],
            'vertical_alignment' => ['type' => 'select', 'label' => 'Vertical Alignment',
                'options' => ['stretch', 'flex-start', 'center', 'flex-end'],
            ],
            'text_align' => ['type' => 'select', 'label' => 'Text Align', 'options' => ['left', 'center', 'right', 'justify']],
            'background_color' => ['type' => 'color', 'label' => 'Background Color'],
            'padding_top' => ['type' => 'text', 'label' => 'Padding Top'],
            'padding_bottom' => ['type' => 'text', 'label' => 'Padding Bottom'],
            'padding_left' => ['type' => 'text', 'label' => 'Padding Left'],
            'padding_right' => ['type' => 'text', 'label' => 'Padding Right'],
            'margin' => ['type' => 'text', 'label' => 'Margin'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $columnWidth = $settings['column_width'];
        $verticalAlign = $settings['vertical_alignment'];
        $contentPosition = $settings['content_position'];
        $bgColor = $settings['background_color'];
        $bgImage = $settings['background_image'];
        $paddingTop = $settings['padding_top'];
        $paddingBottom = $settings['padding_bottom'];
        $paddingLeft = $settings['padding_left'];
        $paddingRight = $settings['padding_right'];
        $margin = $settings['margin'];
        $borderRadius = $settings['border_radius'];
        $boxShadow = $settings['box_shadow'];
        $cssClasses = $settings['css_classes'];

        $style = "padding: {$paddingTop} {$paddingRight} {$paddingBottom} {$paddingLeft}; border-radius: {$borderRadius}; display: flex; flex-direction: column; align-self: {$verticalAlign}; justify-content: {$contentPosition};";

        if ($margin) {
            $style .= " margin: {$margin};";
        }

        if ($bgColor && $bgColor !== 'transparent') {
            $style .= " background-color: {$bgColor};";
        }

        if (!empty($bgImage['url'])) {
            $bgPosition = $settings['background_position'] ?? 'center center';
            $bgSize = $settings['background_size'] ?? 'cover';
            $bgRepeat = $settings['background_repeat'] ?? 'no-repeat';
            $style .= " background-image: url('{$bgImage['url']}'); background-position: {$bgPosition}; background-size: {$bgSize}; background-repeat: {$bgRepeat};";
        }

        if ($boxShadow && $boxShadow !== 'none') {
            $style .= " box-shadow: {$boxShadow};";
        }

        $classes = "pb-column {$columnWidth}";

        if ($cssClasses) {
            $classes .= " {$cssClasses}";
        }

        $responsiveHide = '';
        if ($settings['responsive_hide_mobile']) {
            $classes .= ' pb-hide-mobile';
        }
        if ($settings['responsive_hide_tablet']) {
            $classes .= ' pb-hide-tablet';
        }
        if ($settings['responsive_hide_desktop']) {
            $classes .= ' pb-hide-desktop';
        }

        return <<<HTML
<div class="{$classes}" style="{$style}">
    {$children}
</div>
HTML;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $columnWidth = $settings['column_width'];
        $verticalAlign = $settings['vertical_alignment'];
        $contentPosition = $settings['content_position'];
        $textAlign = $settings['text_align'] ?? '';
        $bgColor = $settings['background_color'];
        $bgImage = $settings['background_image'];
        $paddingTop = $settings['padding_top'];
        $paddingBottom = $settings['padding_bottom'];
        $paddingLeft = $settings['padding_left'];
        $paddingRight = $settings['padding_right'];
        $margin = $settings['margin'];
        $borderRadius = $settings['border_radius'];
        $boxShadow = $settings['box_shadow'];
        $cssClasses = $settings['css_classes'];

        $style = "padding: {$paddingTop} {$paddingRight} {$paddingBottom} {$paddingLeft}; border-radius: {$borderRadius}; display: flex; flex-direction: column; align-self: {$verticalAlign}; justify-content: {$contentPosition};";
        if ($textAlign) {
            $style .= " text-align: {$textAlign};";
        }

        if ($margin) {
            $style .= " margin: {$margin};";
        }

        if ($bgColor && $bgColor !== 'transparent') {
            $style .= " background-color: {$bgColor};";
        }

        if (!empty($bgImage['url'])) {
            $style .= " background-image: url('{$bgImage['url']}'); background-position: center center; background-size: cover; background-repeat: no-repeat;";
        }

        if ($boxShadow && $boxShadow !== 'none') {
            $style .= " box-shadow: {$boxShadow};";
        }

        $classes = "pb-column-editor {$columnWidth}";
        if ($cssClasses) {
            $classes .= " {$cssClasses}";
        }

        return <<<HTML
<div class="{$classes}" style="{$style}">
    <div class="pb-column-header">Column</div>
    <div class="pb-column-content">
        {$children}
    </div>
</div>
HTML;
    }
}
