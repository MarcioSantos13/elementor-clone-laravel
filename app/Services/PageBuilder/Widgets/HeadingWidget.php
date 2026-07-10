<?php

namespace App\Services\PageBuilder\Widgets;

class HeadingWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'heading';
        $this->label = 'Heading';
        $this->icon = 'heading-icon';
        $this->categories = ['basic', 'typography'];
        $this->keywords = ['heading', 'title', 'h1', 'h2', 'h3'];

        $this->defaultSettings = [
            'title' => 'Your Heading Title',
            'tag' => 'h2',
            'alignment' => 'left',
            'size' => 'default',
            'color' => '#333333',
            'font_family' => '',
            'font_weight' => '700',
            'letter_spacing' => 'normal',
            'line_height' => '1.4',
            'margin_bottom' => '20px',
            'link' => '',
            'link_target' => '_self',
            'enable_animation' => false,
            'animation_type' => 'fadeIn',
        ];

        $this->controls = [
            'title' => ['type' => 'text', 'label' => 'Title', 'required' => true, 'max_length' => 500],
            'tag' => ['type' => 'select', 'label' => 'HTML Tag', 'options' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div'], 'default' => 'h2'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right', 'justify']],
            'size' => ['type' => 'select', 'label' => 'Size', 'options' => ['small', 'default', 'medium', 'large', 'xl', 'xxl']],
            'color' => ['type' => 'color', 'label' => 'Color'],
            'font_family' => ['type' => 'text', 'label' => 'Font Family'],
            'font_weight' => ['type' => 'select', 'label' => 'Font Weight', 'options' => ['300', '400', '500', '600', '700', '800', '900']],
            'link' => ['type' => 'url', 'label' => 'Link'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $tag = htmlspecialchars($settings['tag'], ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $color = $settings['color'];
        $fontFamily = $settings['font_family'];
        $fontWeight = $settings['font_weight'];
        $letterSpacing = $settings['letter_spacing'];
        $lineHeight = $settings['line_height'];
        $marginBottom = $settings['margin_bottom'];
        $link = $settings['link'];
        $linkTarget = $settings['link_target'];

        $tagSizeMap = ['h1' => '2.2em', 'h2' => '1.8em', 'h3' => '1.4em', 'h4' => '1.15em', 'h5' => '1em', 'h6' => '.85em'];
        $sizeMap = [
            'small' => '1.2em',
            'medium' => '2.5em',
            'large' => '3em',
            'xl' => '3.5em',
            'xxl' => '4.5em',
        ];

        $fontSize = $sizeMap[$settings['size']] ?? ($tagSizeMap[$settings['tag']] ?? '1.8em');

        $style = "text-align: {$alignment}; color: {$color}; font-size: {$fontSize}; font-weight: {$fontWeight}; line-height: {$lineHeight}; margin-bottom: {$marginBottom};";

        if ($fontFamily) {
            $style .= " font-family: {$fontFamily};";
        }

        if ($letterSpacing !== 'normal') {
            $style .= " letter-spacing: {$letterSpacing};";
        }

        $innerHtml = "<{$tag} class=\"pb-heading\" style=\"{$style}\">{$title}</{$tag}>";

        if ($link) {
            $target = htmlspecialchars($linkTarget, ENT_QUOTES, 'UTF-8');
            $href = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
            $innerHtml = "<a href=\"{$href}\" target=\"{$target}\" class=\"pb-heading-link\">{$innerHtml}</a>";
        }

        return $innerHtml;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $tag = htmlspecialchars($settings['tag'], ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars($settings['title'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $color = $settings['color'];
        $fontFamily = $settings['font_family'];
        $fontWeight = $settings['font_weight'];
        $letterSpacing = $settings['letter_spacing'];
        $lineHeight = $settings['line_height'];
        $marginBottom = $settings['margin_bottom'];

        $tagSizeMap = ['h1' => '2.2em', 'h2' => '1.8em', 'h3' => '1.4em', 'h4' => '1.15em', 'h5' => '1em', 'h6' => '.85em'];
        $sizeMap = [
            'small' => '1.2em',
            'medium' => '2.5em',
            'large' => '3em',
            'xl' => '3.5em',
            'xxl' => '4.5em',
        ];

        $fontSize = $sizeMap[$settings['size']] ?? ($tagSizeMap[$settings['tag']] ?? '1.8em');

        $style = "text-align: {$alignment}; color: {$color}; font-size: {$fontSize}; font-weight: {$fontWeight}; line-height: {$lineHeight}; margin-bottom: {$marginBottom};";

        if ($fontFamily) {
            $style .= " font-family: {$fontFamily};";
        }

        if ($letterSpacing !== 'normal') {
            $style .= " letter-spacing: {$letterSpacing};";
        }

        return "<{$tag} class=\"pb-heading-editor\" style=\"{$style}\">{$title}</{$tag}>";
    }
}
