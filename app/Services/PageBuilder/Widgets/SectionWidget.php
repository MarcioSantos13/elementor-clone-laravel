<?php

namespace App\Services\PageBuilder\Widgets;

class SectionWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'section';
        $this->label = 'Section';
        $this->icon = 'section-icon';
        $this->categories = ['layout', 'structure'];
        $this->container = true;
        $this->keywords = ['section', 'wrapper', 'container', 'layout', 'row'];

        $this->defaultSettings = [
            'layout' => 'boxed',
            'content_width' => '1140px',
            'min_height' => 'auto',
            'gap' => 'default',
            'flex_wrap' => 'wrap',
            'align_items' => 'stretch',
            'justify_content' => 'flex-start',
            'background_type' => 'none',
            'background_color' => 'transparent',
            'background_image' => [],
            'background_overlay' => '',
            'background_position' => 'center center',
            'background_size' => 'cover',
            'background_repeat' => 'no-repeat',
            'padding_top' => '40px',
            'padding_bottom' => '40px',
            'padding_left' => '0px',
            'padding_right' => '0px',
            'margin_top' => '0px',
            'margin_bottom' => '0px',
            'border_radius' => '0px',
            'box_shadow' => 'none',
            'z_index' => 'auto',
            'css_id' => '',
            'css_classes' => '',
            'parallax' => false,
            'parallax_speed' => 0.5,
            'video_background' => [],
        ];

        $this->controls = [
            'layout' => ['type' => 'select', 'label' => 'Layout', 'options' => ['boxed', 'full_width', 'full_height']],
            'content_width' => ['type' => 'text', 'label' => 'Content Width'],
            'background_type' => ['type' => 'select', 'label' => 'Background Type', 'options' => ['none', 'classic', 'gradient', 'video']],
            'background_color' => ['type' => 'color', 'label' => 'Background Color'],
            'padding_top' => ['type' => 'text', 'label' => 'Padding Top'],
            'padding_bottom' => ['type' => 'text', 'label' => 'Padding Bottom'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $layout = $settings['layout'];
        $contentWidth = $settings['content_width'];
        $minHeight = $settings['min_height'];
        $bgColor = $settings['background_color'];
        $bgImage = $settings['background_image'];
        $bgOverlay = $settings['background_overlay'];
        $bgPosition = $settings['background_position'];
        $bgSize = $settings['background_size'];
        $bgRepeat = $settings['background_repeat'];
        $paddingTop = $settings['padding_top'];
        $paddingBottom = $settings['padding_bottom'];
        $paddingLeft = $settings['padding_left'];
        $paddingRight = $settings['padding_right'];
        $marginTop = $settings['margin_top'];
        $marginBottom = $settings['margin_bottom'];
        $borderRadius = $settings['border_radius'];
        $boxShadow = $settings['box_shadow'];
        $zIndex = $settings['z_index'];
        $parallax = $settings['parallax'];
        $cssClasses = $settings['css_classes'];

        $sectionStyle = "padding: {$paddingTop} {$paddingRight} {$paddingBottom} {$paddingLeft}; margin: {$marginTop} 0 {$marginBottom} 0; border-radius: {$borderRadius};";

        if ($zIndex !== 'auto') {
            $sectionStyle .= " z-index: {$zIndex};";
        }

        $sectionStyle .= " position: relative;";

        $backgroundStyle = '';
        if ($bgColor && $bgColor !== 'transparent') {
            $backgroundStyle .= " background-color: {$bgColor};";
        }

        if (!empty($bgImage['url'])) {
            $backgroundStyle .= " background-image: url('{$bgImage['url']}'); background-position: {$bgPosition}; background-size: {$bgSize}; background-repeat: {$bgRepeat};";
        }

        $sectionClass = "pb-section pb-section-{$layout}";

        if ($cssClasses) {
            $sectionClass .= " {$cssClasses}";
        }

        if ($parallax) {
            $sectionClass .= ' pb-parallax';
        }

        $innerStyle = '';
        if ($layout === 'boxed') {
            $innerStyle = "max-width: {$contentWidth}; margin: 0 auto;";
        } elseif ($layout === 'full_height') {
            $sectionStyle .= " min-height: 100vh; display: flex; align-items: center;";
        }

        $overlayHtml = '';
        if ($bgOverlay) {
            $overlayHtml = "<div class=\"pb-section-overlay\" style=\"background-color: {$bgOverlay}; position: absolute; top: 0; left: 0; right: 0; bottom: 0;\"></div>";
        }

        $parallaxAttrs = '';
        if ($parallax) {
            $parallaxAttrs = " data-parallax-speed=\"{$settings['parallax_speed']}\"";
        }

        $gap = $settings['gap'];
        $flexWrap = $settings['flex_wrap'];
        $alignItems = $settings['align_items'];
        $justifyContent = $settings['justify_content'];
        $innerStyle .= " display: flex; flex-wrap: {$flexWrap}; align-items: {$alignItems}; justify-content: {$justifyContent}; gap: {$gap};";

        return <<<HTML
<section class="{$sectionClass}" style="{$sectionStyle}{$backgroundStyle}"{$parallaxAttrs}>
    {$overlayHtml}
    <div class="pb-section-inner" style="{$innerStyle}">
        {$children}
    </div>
</section>
HTML;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $children = $content['children'] ?? '';
        $layout = $settings['layout'];
        $bgColor = $settings['background_color'];
        $bgImage = $settings['background_image'];
        $paddingTop = $settings['padding_top'];
        $paddingBottom = $settings['padding_bottom'];
        $paddingLeft = $settings['padding_left'];
        $paddingRight = $settings['padding_right'];
        $marginTop = $settings['margin_top'];
        $marginBottom = $settings['margin_bottom'];
        $borderRadius = $settings['border_radius'];
        $contentWidth = $settings['content_width'];

        $sectionStyle = "padding: {$paddingTop} {$paddingRight} {$paddingBottom} {$paddingLeft}; margin: {$marginTop} 0 {$marginBottom} 0; border-radius: {$borderRadius}; position: relative;";

        if ($bgColor && $bgColor !== 'transparent') {
            $sectionStyle .= " background-color: {$bgColor};";
        }

        if (!empty($bgImage['url'])) {
            $sectionStyle .= " background-image: url('{$bgImage['url']}'); background-position: center center; background-size: cover; background-repeat: no-repeat;";
        }

        $innerStyle = '';
        if ($layout === 'boxed') {
            $innerStyle = "max-width: {$contentWidth}; margin: 0 auto;";
        }

        return <<<HTML
<div class="pb-section-editor" style="{$sectionStyle}">
    <div class="pb-section-header">Section</div>
    <div class="pb-section-content" style="{$innerStyle}">
        {$children}
    </div>
</div>
HTML;
    }
}
