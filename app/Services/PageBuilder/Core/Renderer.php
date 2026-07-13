<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\Element;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class Renderer
{
    protected WidgetManager $widgetManager;
    protected string $theme = 'default';

    public function __construct(WidgetManager $widgetManager)
    {
        $this->widgetManager = $widgetManager;
    }

    public function render(Page $page, array $options = []): string
    {
        $this->theme = $options['theme'] ?? 'default';

        $pageSettings = $page->settings ?? [];

        $containerWidth = $pageSettings['container_width'] ?? $options['container_width'] ?? '1140px';
        $pageBackground = $pageSettings['page_background'] ?? '#ffffff';
        $contentPadding = $pageSettings['content_padding'] ?? '0px';

        $html = $this->renderElements($page->elements);

        $wrapperClass = $options['wrapper_class'] ?? 'page-builder-wrapper';
        $pageStyle = "background-color: {$pageBackground};";
        $innerStyle = "max-width: {$containerWidth}; margin: 0 auto; padding: {$contentPadding};";

        $content = <<<HTML
<div class="{$wrapperClass}" style="{$pageStyle}">
    <div class="pb-page-inner" style="{$innerStyle}">
        {$html}
    </div>
</div>
HTML;

        if ($options['with_container'] ?? true) {
            $lang = $options['lang'] ?? 'en';
            $hasMath = $page->elements()->where('type', 'math')->exists()
                || $page->elements()->where('type', 'text')->where('settings->content', 'LIKE', '%pb-math%')->exists();
            $renderStyles = $this->renderStyles($page, $hasMath);
            $renderScripts = $this->renderScripts($page, $hasMath);
            $title = htmlspecialchars($page->title, ENT_QUOTES, 'UTF-8');
            $content = <<<HTML
<!DOCTYPE html>
<html lang="{$lang}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body { margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
        * { box-sizing: border-box; }
    </style>
    {$renderStyles}
    {$renderScripts}
</head>
<body>
    {$content}
</body>
</html>
HTML;
        }

        return $content;
    }

    public function renderElements($elements, array $options = []): string
    {
        $html = '';

        foreach ($elements as $element) {
            $html .= $this->renderElement($element, $options);
        }

        return $html;
    }

    public function renderElement(Element $element, array $options = []): string
    {
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return "<!-- Unknown widget type: {$element->type} -->";
        }

        $childrenHtml = '';
        if ($widget->isContainer() && $element->children->isNotEmpty()) {
            $childrenHtml = $this->renderElements($element->children, $options);
        }

        $innerHtml = $widget->render(
            $element->settings ?? [],
            array_merge($element->content ?? [], ['children' => $childrenHtml]),
            $element->styles ?? []
        );

        $innerHtml = $this->processEmbeds($innerHtml);

        $attributes = $this->buildAttributes($element);
        $styleStr = $this->buildStyleString($element->styles ?? []);
        $styleAttr = $styleStr ? " style=\"{$styleStr}\"" : '';
        $cssId = $element->css_id ? " id=\"{$element->css_id}\"" : '';
        $cssClasses = "pb-element pb-{$element->type} {$element->column_size} {$this->getCssClasses($element)}";

        $settings = $element->settings ?? [];

        if (!empty($settings['animation']) && $settings['animation'] !== 'none') {
            $animClass = 'pb-animate ' . htmlspecialchars($settings['animation'], ENT_QUOTES);
            $duration = $settings['animation_duration'] ?? 'normal';
            if ($duration === 'slow') $animClass .= ' pb-animate-slow';
            elseif ($duration === 'fast') $animClass .= ' pb-animate-fast';
            $delay = (int) ($settings['animation_delay'] ?? 0);
            if ($delay > 0) $styleStr .= " animation-delay: {$delay}ms;";
            $styleAttr = $styleStr ? " style=\"{$styleStr}\"" : '';
            $cssClasses .= ' ' . $animClass;
        }

        $visDesktop = $settings['visibility_desktop'] ?? true;
        $visTablet = $settings['visibility_tablet'] ?? true;
        $visMobile = $settings['visibility_mobile'] ?? true;
        $visCss = '';
        if (!$visDesktop) $visCss .= ".pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}";
        if (!$visTablet) $visCss .= "@media(max-width:1024px){.pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}}";
        if (!$visMobile) $visCss .= "@media(max-width:767px){.pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}}";

        $customCss = $settings['custom_css'] ?? '';
        $customStyle = '';
        if ($customCss) {
            $selector = $cssId ? "#{$element->css_id}" : ".pb-element[data-element-id=\"{$element->id}\"]";
            $customStyle = "<style>{$selector} { {$customCss} }</style>";
        }

        $visStyle = $visCss ? "<style>{$visCss}</style>" : '';

        return $visStyle . $customStyle . <<<HTML
<div{$cssId} class="{$cssClasses}" data-element-id="{$element->id}" data-element-type="{$element->type}"{$attributes}{$styleAttr}>
    {$innerHtml}
</div>
HTML;
    }

    public function renderEditor(Page $page): string
    {
        $html = '';

        foreach ($page->elements as $element) {
            $html .= $this->renderElementEditor($element);
        }

        return $html;
    }

    public function renderElementEditor(Element $element): string
    {
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return '<div class="pb-editor-error">Unknown widget</div>';
        }

        $childrenHtml = '';
        if ($widget->isContainer() && $element->children->isNotEmpty()) {
            foreach ($element->children as $child) {
                $childrenHtml .= $this->renderElementEditor($child);
            }
        }

        $innerHtml = $widget->renderEditor(
            $element->settings ?? [],
            array_merge($element->content ?? [], ['children' => $childrenHtml]),
            $element->styles ?? []
        );

        $innerHtml = $this->processEmbeds($innerHtml);

        $cssId = $element->css_id ? " id=\"{$element->css_id}\"" : '';
        $classes = "pb-editor-element pb-{$element->type} {$element->column_size} {$this->getCssClasses($element)}";

        $settings = $element->settings ?? [];
        $visDesktop = $settings['visibility_desktop'] ?? true;
        $visTablet = $settings['visibility_tablet'] ?? true;
        $visMobile = $settings['visibility_mobile'] ?? true;
        $visStyle = '';
        if (!$visDesktop || !$visTablet || !$visMobile) {
            $visCss = '';
            if (!$visDesktop) $visCss .= ".pb-editor-element[data-element-id=\"{$element->id}\"]{opacity:.3;}";
            if (!$visTablet) $visCss .= "@media(max-width:1024px){.pb-editor-element[data-element-id=\"{$element->id}\"]{opacity:.3;}}";
            if (!$visMobile) $visCss .= "@media(max-width:767px){.pb-editor-element[data-element-id=\"{$element->id}\"]{opacity:.3;}}";
            $visStyle = "<style>{$visCss}</style>";
        }

        $customCss = $settings['custom_css'] ?? '';
        $customStyle = '';
        if ($customCss) {
            $selector = $cssId ? "#{$element->css_id}" : ".pb-editor-element[data-element-id=\"{$element->id}\"]";
            $customStyle = "<style>{$selector} { {$customCss} }</style>";
        }

        return $visStyle . $customStyle . <<<HTML
<div{$cssId} class="{$classes}" data-element-id="{$element->id}" data-element-type="{$element->type}" draggable="true">
    <div class="pb-element-toolbar">
        <span class="pb-element-name">{$element->name}</span>
        <div class="pb-element-actions">
            <button class="pb-btn-duplicate" title="Duplicate">⧉</button>
            <button class="pb-btn-edit" title="Edit">✎</button>
            <button class="pb-btn-delete" title="Delete">✕</button>
        </div>
    </div>
    <div class="pb-element-content">
        {$innerHtml}
    </div>
</div>
HTML;
    }

    protected function buildAttributes(Element $element): string
    {
        $attrs = [];

        if ($element->css_id) {
            $attrs['data-css-id'] = $element->css_id;
        }

        if ($element->animation) {
            $attrs['data-animation'] = json_encode($element->animation);
        }

        if ($element->effects) {
            $attrs['data-effects'] = json_encode($element->effects);
        }

        if ($element->responsive_settings) {
            $attrs['data-responsive'] = json_encode($element->responsive_settings);
        }

        if (empty($attrs)) {
            return '';
        }

        $html = '';
        foreach ($attrs as $key => $value) {
            $html .= " {$key}=\"" . htmlspecialchars($value, ENT_QUOTES) . '"';
        }

        return $html;
    }

    protected function buildStyleString(array $styles): string
    {
        if (empty($styles)) {
            return '';
        }

        $css = '';
        foreach ($styles as $property => $value) {
            $cssProperty = Str::kebab($property);
            $css .= "{$cssProperty}: {$value}; ";
        }

        return $css;
    }

    protected function buildInlineStyles(array $styles): string
    {
        if (empty($styles)) {
            return '';
        }

        $css = $this->buildStyleString($styles);

        return " style=\"{$css}\"";
    }

    protected function getCssClasses(Element $element): string
    {
        $classes = [];

        if ($element->css_classes && is_array($element->css_classes)) {
            $classes = array_merge($classes, $element->css_classes);
        }

        return implode(' ', $classes);
    }

    protected function processEmbeds(string $html): string
    {
        $processed = preg_replace_callback(
            '/<iframe\s[^>]*src=["\'](https?:\/\/(?:www\.)?(?:youtube\.com|youtu\.be|youtube-nocookie\.com)[^"\']+)["\'][^>]*>/i',
            function ($matches) {
                $url = $matches[1];
                $videoId = null;

                if (preg_match('/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $m)) {
                    $videoId = $m[1];
                } elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $m)) {
                    $videoId = $m[1];
                } elseif (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $m)) {
                    $videoId = $m[1];
                }

                if ($videoId) {
                    $iframe = str_replace($url, "https://www.youtube-nocookie.com/embed/{$videoId}", $matches[0]);
                    if (!str_contains($iframe, 'allow="')) {
                        $iframe = str_replace('<iframe', '<iframe allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen', $iframe);
                    }
                    return $iframe;
                }

                return $matches[0];
            },
            $html
        );

        $processed = preg_replace_callback(
            '/<a\s[^>]*href=["\'](https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+))["\'][^>]*>.*?<\/a>/i',
            function ($matches) {
                $videoId = $matches[2];
                return '<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/' . $videoId . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>';
            },
            $processed
        );

        return $processed;
    }

    protected function renderStyles(Page $page, bool $hasMath = false): string
    {
        $hasAnimations = $page->elements()->where('settings->animation', '!=', 'none')
            ->whereNotNull('settings->animation')->exists();

        $css = "\n<style>\n.pb-drop-cap:first-letter { font-size: 3em; float: left; line-height: 1; margin-right: 10px; }\n</style>\n";

        $css .= "\n<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\" crossorigin=\"anonymous\" referrerpolicy=\"no-referrer\">\n";

        if ($hasAnimations) {
            $css .= "\n<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css\">\n";
            $css .= "\n<style>.pb-animate { animation-fill-mode: both; }\n.pb-animate-slow { animation-duration: 1s; }\n.pb-animate-fast { animation-duration: 0.5s; }\n</style>\n";
        }

        if ($page->settings['custom_css'] ?? false) {
            $css .= "\n<style>\n{$page->settings['custom_css']}\n</style>\n";
        }

        if ($page->settings['google_fonts'] ?? false) {
            $fonts = $page->settings['google_fonts'];
            foreach ($fonts as $font) {
                $css .= "\n<link href=\"https://fonts.googleapis.com/css2?family={$font}:wght@300;400;500;600;700&display=swap\" rel=\"stylesheet\">\n";
            }
        }

        if ($hasMath) {
            $css .= "\n<link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css\">\n";
        }

        return $css;
    }

    protected function renderScripts(Page $page, bool $hasMath = false): string
    {
        $scripts = '';

        if ($page->settings['custom_js'] ?? false) {
            $scripts .= "\n<script>\n{$page->settings['custom_js']}\n</script>\n";
        }

        if ($page->settings['custom_js_footer'] ?? false) {
            $scripts .= "\n<script>\n{$page->settings['custom_js_footer']}\n</script>\n";
        }

        if ($hasMath) {
            $scripts .= "\n<script src=\"https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js\"></script>\n";
            $scripts .= "\n<script>\ndocument.addEventListener('DOMContentLoaded',function(){document.querySelectorAll('.pb-math').forEach(function(el){try{katex.render(el.getAttribute('data-formula'),el,{displayMode:el.getAttribute('data-display')==='true',throwOnError:false})}catch(e){el.textContent=el.getAttribute('data-formula')}})})\n</script>\n";
        }

        return $scripts;
    }

    public function renderSingleElement(Element $element): string
    {
        $widget = $this->widgetManager->getWidget($element->type);

        if (!$widget) {
            return "<!-- Unknown widget type: {$element->type} -->";
        }

        $childrenHtml = '';
        if ($element->children->isNotEmpty()) {
            foreach ($element->children as $child) {
                $childrenHtml .= $this->renderSingleElement($child);
            }
        }

        $innerHtml = $widget->render(
            $element->settings ?? [],
            array_merge($element->content ?? [], ['children' => $childrenHtml]),
            $element->styles ?? []
        );

        $innerHtml = $this->processEmbeds($innerHtml);

        $attributes = $this->buildAttributes($element);
        $styleStr = $this->buildStyleString($element->styles ?? []);
        $styleAttr = $styleStr ? " style=\"{$styleStr}\"" : '';
        $cssId = $element->css_id ? " id=\"{$element->css_id}\"" : '';
        $cssClasses = "pb-element pb-{$element->type} {$element->column_size} {$this->getCssClasses($element)}";

        $settings = $element->settings ?? [];

        if (!empty($settings['animation']) && $settings['animation'] !== 'none') {
            $animClass = 'pb-animate ' . htmlspecialchars($settings['animation'], ENT_QUOTES);
            $duration = $settings['animation_duration'] ?? 'normal';
            if ($duration === 'slow') $animClass .= ' pb-animate-slow';
            elseif ($duration === 'fast') $animClass .= ' pb-animate-fast';
            $delay = (int) ($settings['animation_delay'] ?? 0);
            if ($delay > 0) $styleStr .= " animation-delay: {$delay}ms;";
            $styleAttr = $styleStr ? " style=\"{$styleStr}\"" : '';
            $cssClasses .= ' ' . $animClass;
        }

        $visDesktop = $settings['visibility_desktop'] ?? true;
        $visTablet = $settings['visibility_tablet'] ?? true;
        $visMobile = $settings['visibility_mobile'] ?? true;
        $visCss = '';
        if (!$visDesktop) $visCss .= ".pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}";
        if (!$visTablet) $visCss .= "@media(max-width:1024px){.pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}}";
        if (!$visMobile) $visCss .= "@media(max-width:767px){.pb-element[data-element-id=\"{$element->id}\"]{display:none !important;}}";

        $customCss = $settings['custom_css'] ?? '';
        $customStyle = '';
        if ($customCss) {
            $selector = $cssId ? "#{$element->css_id}" : ".pb-element[data-element-id=\"{$element->id}\"]";
            $customStyle = "<style>{$selector} { {$customCss} }</style>";
        }

        $visStyle = $visCss ? "<style>{$visCss}</style>" : '';

        return $visStyle . $customStyle . <<<HTML
<div{$cssId} class="{$cssClasses}" data-element-id="{$element->id}" data-element-type="{$element->type}"{$attributes}{$styleAttr}>
    <div class="pb-element-toolbar">
        <span class="pb-el-name">{$element->name}</span>
        <span class="pb-el-type">{$element->type}</span>
    </div>
    <div class="pb-el-content">{$innerHtml}</div>
</div>
HTML;
    }

    public function getWidgetControls(string $type): ?array
    {
        $widget = $this->widgetManager->getWidget($type);
        if (!$widget) return null;

        return [
            'type' => $widget->getType(),
            'label' => $widget->getLabel(),
            'icon' => $widget->getIcon(),
            'categories' => $widget->getCategories(),
            'is_container' => $widget->isContainer(),
            'default_settings' => $widget->getDefaultSettings(),
            'controls' => $widget->getControls(),
        ];
    }

    public function renderJson(Page $page): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'settings' => $page->settings,
            'meta_data' => $page->meta_data,
            'elements' => $this->renderElementsJson($page->elements),
        ];
    }

    protected function renderElementsJson($elements): array
    {
        $result = [];

        foreach ($elements as $element) {
            $data = [
                'id' => $element->uuid,
                'type' => $element->type,
                'name' => $element->name,
                'settings' => $element->settings,
                'content' => $element->content,
                'styles' => $element->styles,
                'responsive_settings' => $element->responsive_settings,
                'animation' => $element->animation,
                'effects' => $element->effects,
                'column_size' => $element->column_size,
            ];

            if ($element->children->isNotEmpty()) {
                $data['children'] = $this->renderElementsJson($element->children);
            }

            $result[] = $data;
        }

        return $result;
    }
}
