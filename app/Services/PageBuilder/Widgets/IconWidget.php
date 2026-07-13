<?php

namespace App\Services\PageBuilder\Widgets;

class IconWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'icon';
        $this->label = 'Icon';
        $this->icon = '⭐';
        $this->categories = ['general'];
        $this->keywords = ['icon', 'font awesome', 'symbol'];

        $this->defaultSettings = [
            'icon' => 'fas fa-star',
            'icon_size' => 48,
            'color' => '#6366f1',
            'align' => 'center',
            'link' => '',
            'link_new_tab' => false,
        ];

        $this->controls = [
            'icon' => ['type' => 'icon', 'label' => 'Icon'],
            'align' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'link' => ['type' => 'url', 'label' => 'Link'],
            'link_new_tab' => ['type' => 'boolean', 'label' => 'Open in New Tab'],
            'icon_size' => ['type' => 'number', 'label' => 'Size (px)', 'min' => 12, 'max' => 200, 'tab' => 'style'],
            'color' => ['type' => 'color', 'label' => 'Color', 'tab' => 'style'],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
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
        $icon = htmlspecialchars($settings['icon'], ENT_QUOTES, 'UTF-8');
        $size = (int) $settings['icon_size'];
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $align = $settings['align'];
        $link = $settings['link'];
        $newTab = $settings['link_new_tab'];

        $iconHtml = "<i class=\"{$icon}\" style=\"font-size: {$size}px; color: {$color}; line-height: 1;\"></i>";
        $wrapper = "<div style=\"text-align: {$align};\">{$iconHtml}</div>";

        if ($link) {
            $target = $newTab ? ' target="_blank" rel="noopener noreferrer"' : '';
            $href = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $wrapper = "<a href=\"{$href}\"{$target} class=\"pb-icon-link\" style=\"text-decoration: none; display: inline-block;\">{$wrapper}</a>";
            if ($align === 'center') {
                $wrapper = "<div style=\"text-align: center;\">{$wrapper}</div>";
            } elseif ($align === 'right') {
                $wrapper = "<div style=\"text-align: right;\">{$wrapper}</div>";
            }
        }

        return $wrapper;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $icon = htmlspecialchars($settings['icon'], ENT_QUOTES, 'UTF-8');
        $size = (int) $settings['icon_size'];
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $align = $settings['align'];

        return "<div style=\"text-align: {$align}; padding: 8px 0;\"><i class=\"{$icon}\" style=\"font-size: {$size}px; color: {$color}; line-height: 1;\"></i></div>";
    }
}
