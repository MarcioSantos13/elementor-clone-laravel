<?php

namespace App\Services\PageBuilder\Widgets;

class ImageWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'image';
        $this->label = 'Image';
        $this->icon = 'image-icon';
        $this->categories = ['basic', 'media'];
        $this->keywords = ['image', 'photo', 'picture', 'media'];

        $this->defaultSettings = [
            'image' => [
                'url' => '',
                'alt' => '',
                'width' => 800,
                'height' => 600,
            ],
            'caption' => '',
            'alignment' => 'center',
            'width' => '100%',
            'max_width' => '100%',
            'height' => 'auto',
            'border_radius' => '0px',
            'opacity' => 1,
            'link' => '',
            'link_target' => '_self',
            'enable_lightbox' => false,
            'object_fit' => 'cover',
            'hover_animation' => 'none',
        ];

        $this->controls = [
            'image' => ['type' => 'image', 'label' => 'Image'],
            'caption' => ['type' => 'text', 'label' => 'Caption', 'max_length' => 300],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'link' => ['type' => 'url', 'label' => 'Link'],
            'enable_lightbox' => ['type' => 'boolean', 'label' => 'Enable Lightbox'],
            'width' => ['type' => 'text', 'label' => 'Width', 'tab' => 'style'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'tab' => 'style'],
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
        $image = $settings['image'];
        $caption = htmlspecialchars($settings['caption'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $width = $settings['width'];
        $maxWidth = $settings['max_width'];
        $height = $settings['height'];
        $borderRadius = $settings['border_radius'];
        $opacity = $settings['opacity'];
        $link = $settings['link'];
        $linkTarget = $settings['link_target'];
        $enableLightbox = $settings['enable_lightbox'];
        $objectFit = $settings['object_fit'];
        $hoverAnimation = $settings['hover_animation'];

        if (empty($image['url'])) {
            return '<div class="pb-image-placeholder">No image selected</div>';
        }

        $imgStyle = "width: {$width}; max-width: {$maxWidth}; height: {$height}; object-fit: {$objectFit}; border-radius: {$borderRadius}; opacity: {$opacity};";

        $imgAttrs = [
            'src' => $image['url'],
            'alt' => $image['alt'] ?: $caption,
            'width' => $image['width'],
            'height' => $image['height'],
            'style' => $imgStyle,
            'class' => 'pb-image',
        ];

        if ($hoverAnimation !== 'none') {
            $imgAttrs['class'] .= ' pb-hover-' . $hoverAnimation;
        }

        $imgAttrStr = '';
        foreach ($imgAttrs as $key => $val) {
            $imgAttrStr .= " {$key}=\"" . htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8') . '"';
        }

        $imgHtml = "<img{$imgAttrStr}>";

        if ($caption) {
            $imgHtml = "<figure class=\"pb-image-wrapper\" style=\"text-align: {$alignment};\">{$imgHtml}<figcaption class=\"pb-image-caption\">{$caption}</figcaption></figure>";
        } else {
            $imgHtml = "<figure class=\"pb-image-wrapper\" style=\"text-align: {$alignment};\">{$imgHtml}</figure>";
        }

        if ($link && !$enableLightbox) {
            $target = htmlspecialchars($linkTarget, ENT_QUOTES, 'UTF-8');
            $href = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $imgHtml = "<a href=\"{$href}\" target=\"{$target}\" class=\"pb-image-link\">{$imgHtml}</a>";
        } elseif ($enableLightbox) {
            $imgHtml = "<a href=\"{$image['url']}\" class=\"pb-lightbox-trigger\" data-lightbox=\"{$image['alt']}\">{$imgHtml}</a>";
        }

        return $imgHtml;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $image = $settings['image'];

        if (empty($image['url'])) {
            return '<div class="pb-image-placeholder">Click to add image</div>';
        }

        $width = $settings['width'];
        $maxWidth = $settings['max_width'];
        $height = $settings['height'];
        $borderRadius = $settings['border_radius'];
        $opacity = $settings['opacity'];
        $objectFit = $settings['object_fit'];
        $alignment = $settings['alignment'];

        $imgStyle = "width: {$width}; max-width: {$maxWidth}; height: {$height}; object-fit: {$objectFit}; border-radius: {$borderRadius}; opacity: {$opacity};";

        return "<div style=\"text-align: {$alignment};\"><img src=\"{$image['url']}\" alt=\"{$image['alt']}\" class=\"pb-image-editor\" style=\"{$imgStyle}\"></div>";
    }
}
