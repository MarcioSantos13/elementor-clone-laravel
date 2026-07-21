<?php

namespace App\Services\PageBuilder\Core;

class HtmlImportService
{
    private int $maxSize = 512000;

    private array $blockTags = [
        'div', 'section', 'article', 'aside', 'header', 'footer', 'main', 'nav',
        'figure', 'figcaption', 'details', 'summary', 'fieldset', 'form',
    ];

    private array $headingTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];

    private array $listTags = ['ul', 'ol'];

    private array $skipTags = ['script', 'style', 'noscript', 'iframe', 'svg', 'head', 'meta', 'link', 'title'];

    public function fetchUrl(string $url): string
    {
        $parsed = parse_url($url);
        if (!$parsed || !in_array($parsed['scheme'] ?? '', ['http', 'https'])) {
            throw new \InvalidArgumentException('URL inválida. Apenas http/https são permitidos.');
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'follow_location' => true,
                'max_redirects' => 3,
                'user_agent' => 'Mozilla/5.0 (compatible; PageBuilder/1.0)',
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $html = @file_get_contents($url, false, $context);
        if ($html === false) {
            throw new \RuntimeException('Não foi possível acessar a URL fornecida.');
        }

        if (strlen($html) > $this->maxSize) {
            throw new \RuntimeException('O conteúdo excede o tamanho máximo de 500KB.');
        }

        return $html;
    }

    public function convert(string $html, string $fallbackTitle = 'Página Importada'): array
    {
        $html = $this->preProcess($html);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML('<div id="pb-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        if (!$loaded) {
            throw new \RuntimeException('Não foi possível analisar o HTML fornecido.');
        }

        $root = $dom->getElementById('pb-root');
        if (!$root) {
            throw new \RuntimeException('Erro ao processar o HTML.');
        }

        $title = $this->extractTitle($dom, $root, $fallbackTitle);
        $elements = $this->processChildren($root);

        $elements = $this->wrapInSections($elements);

        if (empty($elements)) {
            $elements = [[
                'type' => 'section',
                'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px'],
                'children' => [[
                    'type' => 'column',
                    'settings' => ['column_width' => 'col-12'],
                    'children' => [],
                ]],
            ]];
        }

        return [
            'title' => $title,
            'settings' => [
                'container_width' => '1140px',
                'page_background' => '#ffffff',
                'content_padding' => '0px',
            ],
            'elements' => $elements,
        ];
    }

    private function preProcess(string $html): string
    {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $html = preg_replace('/<body[^>]*>/i', '', $html);
        $html = preg_replace('/<\/body>/i', '', $html);
        $html = preg_replace('/<!\-\-.*?\-\->/s', '', $html);

        return trim($html);
    }

    private function extractTitle(\DOMDocument $dom, \DOMElement $root, string $fallback): string
    {
        $title = '';

        $titles = $dom->getElementsByTagName('title');
        if ($titles->length > 0) {
            $title = trim($titles->item(0)->textContent);
        }

        if (empty($title)) {
            $h1s = $root->getElementsByTagName('h1');
            if ($h1s->length > 0) {
                $title = trim($h1s->item(0)->textContent);
            }
        }

        if (empty($title)) {
            $metas = $dom->getElementsByTagName('meta');
            for ($i = 0; $i < $metas->length; $i++) {
                $m = $metas->item($i);
                if (strtolower($m->getAttribute('property')) === 'og:title') {
                    $title = trim($m->getAttribute('content'));
                    break;
                }
            }
        }

        if (empty($title)) {
            $title = $fallback;
        }

        $title = $this->cleanTitle($title);

        return mb_substr($title, 0, 255);
    }

    private function cleanTitle(string $title): string
    {
        $separators = [' - ', ' – ', ' | ', ' :: ', ' // '];
        foreach ($separators as $sep) {
            if (str_contains($title, $sep)) {
                $parts = explode($sep, $title, 2);
                $title = trim($parts[0]);
                break;
            }
        }

        $title = preg_replace('/\s*[\(\[].*?[\)\]]\s*$/', '', $title);

        return trim($title);
    }

    private function processChildren(\DOMElement $parent): array
    {
        $widgets = [];
        foreach ($parent->childNodes as $node) {
            if (!($node instanceof \DOMElement)) {
                if ($node instanceof \DOMText) {
                    $text = trim($node->textContent);
                    if (!empty($text) && !ctype_space($text)) {
                        $widgets[] = $this->makeText('<p>'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</p>');
                    }
                }
                continue;
            }

            $converted = $this->processNode($node);
            if (!empty($converted)) {
                if (is_array($converted) && isset($converted[0])) {
                    $widgets = array_merge($widgets, $converted);
                } else {
                    $widgets[] = $converted;
                }
            }
        }
        return $widgets;
    }

    private function processNode(\DOMElement $el): ?array
    {
        $tag = strtolower($el->tagName);

        if (in_array($tag, $this->skipTags)) {
            return null;
        }

        if ($tag === 'br') {
            return null;
        }

        if ($tag === 'hr') {
            return $this->makeDivider($el);
        }

        if (in_array($tag, $this->headingTags)) {
            return $this->convertHeading($el);
        }

        if ($tag === 'img' || $tag === 'figure') {
            return $this->convertImage($el);
        }

        if ($tag === 'table') {
            return $this->convertTable($el);
        }

        if (in_array($tag, $this->listTags)) {
            return $this->convertList($el);
        }

        if ($tag === 'blockquote') {
            return $this->convertBlockquote($el);
        }

        if ($tag === 'pre') {
            return $this->convertPre($el);
        }

        if ($tag === 'a') {
            return $this->convertLink($el);
        }

        if ($tag === 'p') {
            return $this->convertParagraph($el);
        }

        if (in_array($tag, $this->blockTags)) {
            return $this->convertContainer($el);
        }

        $text = trim($el->textContent);
        if (!empty($text)) {
            $html = $this->innerHtml($el);
            if (!empty(trim($html))) {
                return $this->makeText('<'.$tag.'>'.$html.'</'.$tag.'>');
            }
            return $this->makeText('<p>'.htmlspecialchars($text, ENT_QUOTES, 'UTF-8').'</p>');
        }

        return null;
    }

    private function convertHeading(\DOMElement $el): array
    {
        $tag = strtolower($el->tagName);
        $text = trim($el->textContent);
        if (empty($text)) return [];

        $styles = $this->extractStyles($el);
        $color = $styles['color'] ?? '#333333';

        return $this->makeHeading($text, $tag, $color);
    }

    private function convertParagraph(\DOMElement $el): array
    {
        $html = $this->innerHtml($el);
        $text = trim($el->textContent);

        if (empty($text)) return [];

        if ($this->looksLikeButton($el)) {
            return $this->convertPossibleButton($el);
        }

        $styles = $this->extractStyles($el);
        $settings = ['content' => '<p'.$this->buildStyleAttr($styles).'>'.($html ?: htmlspecialchars($text, ENT_QUOTES, 'UTF-8')).'</p>'];

        if (!empty($styles['text-align'])) {
            $settings['alignment'] = $styles['text-align'];
        }
        if (!empty($styles['color'])) {
            $settings['color'] = $styles['color'];
        }
        if (!empty($styles['font-size'])) {
            $settings['font_size'] = $styles['font-size'];
        }
        if (!empty($styles['line-height'])) {
            $settings['line_height'] = $styles['line-height'];
        }

        return $this->makeText(null, $settings);
    }

    private function convertImage(\DOMElement $el): array
    {
        if ($el->tagName === 'figure') {
            $imgs = $el->getElementsByTagName('img');
            if ($imgs->length === 0) return [];
            $img = $imgs->item(0);
            $src = $img->getAttribute('src');
            $alt = $img->getAttribute('alt');

            $caption = null;
            $figcaps = $el->getElementsByTagName('figcaption');
            if ($figcaps->length > 0) {
                $caption = trim($figcaps->item(0)->textContent);
            }

            $settings = [
                'image' => ['url' => $src, 'alt' => $alt, 'width' => 800, 'height' => 600],
                'width' => '100%',
                'alignment' => 'center',
            ];
            if ($caption) {
                $settings['caption'] = $caption;
            }
            return $this->makeWidget('image', $settings);
        }

        $src = $el->getAttribute('src');
        $alt = $el->getAttribute('alt');

        if (empty($src)) return [];

        $settings = [
            'image' => ['url' => $src, 'alt' => $alt ?? '', 'width' => 800, 'height' => 600],
            'width' => '100%',
            'alignment' => 'center',
        ];

        $styles = $this->extractStyles($el);
        if (!empty($styles['max-width'])) {
            $settings['max_width'] = $styles['max-width'];
        }
        if (!empty($styles['width']) && $styles['width'] !== '100%') {
            $settings['width'] = $styles['width'];
        }
        if (!empty($styles['border-radius'])) {
            $settings['border_radius'] = $styles['border-radius'];
        }

        return $this->makeWidget('image', $settings);
    }

    private function convertTable(\DOMElement $el): array
    {
        $html = $this->cleanTableHtml($el);
        return $this->makeText('<table'.$this->buildStyleAttr($this->extractStyles($el)).'>'.$html.'</table>');
    }

    private function cleanTableHtml(\DOMElement $el): string
    {
        $clone = clone $el;
        $this->removeAttributes($clone, ['class', 'id', 'style', 'width', 'height', 'cellspacing', 'cellpadding', 'border']);
        foreach ($clone->getElementsByTagName('*') as $node) {
            if ($node instanceof \DOMElement) {
                $this->removeAttributes($node, ['class', 'id', 'style', 'width', 'height', 'valign', 'align', 'bgcolor', 'border']);
            }
        }
        return $this->innerHtml($clone);
    }

    private function convertList(\DOMElement $el): array
    {
        $html = $this->cleanListHtml($el);
        $tag = strtolower($el->tagName);
        return $this->makeText('<'.$tag.'>'.$html.'</'.$tag.'>');
    }

    private function cleanListHtml(\DOMElement $el): string
    {
        $clone = clone $el;
        $this->removeAttributes($clone, ['class', 'id', 'style']);
        foreach ($clone->getElementsByTagName('*') as $node) {
            if ($node instanceof \DOMElement) {
                $this->removeAttributes($node, ['class', 'id', 'style']);
            }
        }
        return $this->innerHtml($clone);
    }

    private function convertBlockquote(\DOMElement $el): array
    {
        $html = $this->innerHtml($el);
        $text = trim($el->textContent);
        if (empty($text)) return [];

        return $this->makeWidget('callout', [
            'type' => 'note',
            'title' => '',
            'content' => '<p>'.$html.'</p>',
            'show_icon' => true,
        ]);
    }

    private function convertPre(\DOMElement $el): array
    {
        $code = $el->getElementsByTagName('code');
        $content = $code->length > 0 ? $code->item(0)->textContent : $el->textContent;
        $content = htmlspecialchars(trim($content), ENT_QUOTES, 'UTF-8');

        return $this->makeText('<pre style="background:#f1f5f9;padding:1rem;border-radius:6px;overflow-x:auto;font-size:.85rem"><code>'.$content.'</code></pre>');
    }

    private function convertLink(\DOMElement $el): array
    {
        $href = $el->getAttribute('href');
        $text = trim($el->textContent);
        if (empty($text)) return [];

        if ($this->isYouTubeUrl($href)) {
            $embedUrl = $this->convertYouTubeUrl($href);
            return $this->makeWidget('video', [
                'video_url' => $embedUrl,
                'aspect_ratio' => '16:9',
            ]);
        }

        if ($this->looksLikeButton($el)) {
            return $this->convertPossibleButton($el);
        }

        $html = '<a href="'.htmlspecialchars($href, ENT_QUOTES, 'UTF-8').'" target="_blank" rel="noopener">'.$this->innerHtml($el).'</a>';
        return $this->makeText($html);
    }

    private function convertContainer(\DOMElement $el): array
    {
        $tag = strtolower($el->tagName);
        $styles = $this->extractStyles($el);
        $children = $this->processChildren($el);

        if (empty($children)) return [];

        $bgColor = $styles['background-color'] ?? $styles['background'] ?? null;
        if ($bgColor && preg_match('/^#[0-9a-fA-F]{3,8}$/', $bgColor)) {
            // ok
        } elseif ($bgColor) {
            $bgColor = null;
        }

        $paddingTop = $styles['padding-top'] ?? $styles['padding'] ?? null;
        $paddingBottom = $styles['padding-bottom'] ?? $styles['padding'] ?? null;

        $sectionSettings = [
            'layout' => 'boxed',
            'background_color' => $bgColor ?: '#ffffff',
            'padding_top' => $paddingTop ?: '40px',
            'padding_bottom' => $paddingBottom ?: '40px',
        ];

        $columnChildren = [];
        foreach ($children as $child) {
            $columnChildren[] = $child;
        }

        return [[
            'type' => 'section',
            'settings' => $sectionSettings,
            'children' => [[
                'type' => 'column',
                'settings' => ['column_width' => 'col-12'],
                'children' => $columnChildren,
            ]],
        ]];
    }

    private function looksLikeButton(\DOMElement $el): bool
    {
        $classes = strtolower($el->getAttribute('class'));
        $btnPatterns = ['btn', 'button', 'cta', 'submit', 'action'];
        foreach ($btnPatterns as $p) {
            if (strpos($classes, $p) !== false) return true;
        }

        $styles = $this->extractStyles($el);
        if (!empty($styles['display']) && in_array($styles['display'], ['inline-block', 'block'])) {
            if (!empty($styles['background-color']) || !empty($styles['background'])) {
                if (!empty($styles['padding']) || !empty($styles['border-radius'])) {
                    return true;
                }
            }
        }

        $parent = $el->parentNode;
        if ($parent instanceof \DOMElement && strtolower($parent->tagName) === 'form') {
            $type = strtolower($el->getAttribute('type'));
            if ($type === 'submit' || $type === 'button') return true;
        }

        return false;
    }

    private function convertPossibleButton(\DOMElement $el): array
    {
        $text = trim($el->textContent);
        $href = $el->getAttribute('href') ?? '#';
        $styles = $this->extractStyles($el);

        $settings = [
            'text' => $text,
            'link' => $href ?: '#',
            'size' => 'medium',
            'alignment' => 'center',
            'border_radius' => '6px',
        ];

        if (!empty($styles['background-color']) || !empty($styles['background'])) {
            $settings['background_color'] = $styles['background-color'] ?? $styles['background'];
        }
        if (!empty($styles['color'])) {
            $settings['text_color'] = $styles['color'];
        }
        if (!empty($styles['border-radius'])) {
            $settings['border_radius'] = $styles['border-radius'];
        }

        return $this->makeWidget('button', $settings);
    }

    private function extractStyles(\DOMElement $el): array
    {
        $inlineStyle = $el->getAttribute('style');
        if (empty($inlineStyle)) return [];

        $styles = [];
        $pairs = explode(';', $inlineStyle);
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (empty($pair)) continue;
            $parts = explode(':', $pair, 2);
            if (count($parts) !== 2) continue;
            $prop = strtolower(trim($parts[0]));
            $val = trim($parts[1]);
            if (!empty($val)) {
                $styles[$prop] = $val;
            }
        }
        return $styles;
    }

    private function buildStyleAttr(array $styles): string
    {
        if (empty($styles)) return '';
        $parts = [];
        foreach ($styles as $k => $v) {
            $parts[] = $k.':'.$v;
        }
        return ' style="'.htmlspecialchars(implode('; ', $parts), ENT_QUOTES, 'UTF-8').'"';
    }

    private function innerHtml(\DOMElement $el): string
    {
        $html = '';
        foreach ($el->childNodes as $child) {
            $html .= $el->ownerDocument->saveHTML($child);
        }
        return $html;
    }

    private function removeAttributes(\DOMElement $el, array $attrs): void
    {
        foreach ($attrs as $attr) {
            if ($el->hasAttribute($attr)) {
                $el->removeAttribute($attr);
            }
        }
        foreach ($el->getElementsByTagName('*') as $child) {
            if ($child instanceof \DOMElement) {
                foreach ($attrs as $attr) {
                    if ($child->hasAttribute($attr)) {
                        $child->removeAttribute($attr);
                    }
                }
            }
        }
    }

    private function isYouTubeUrl(string $url): bool
    {
        return (bool) preg_match('/(youtube\.com\/watch|youtu\.be\/|youtube\.com\/embed|youtube\.com\/v|youtube\.com\/shorts)/i', $url);
    }

    private function convertYouTubeUrl(string $url): string
    {
        $patterns = [
            '/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/',
        ];
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $m)) {
                return 'https://www.youtube.com/embed/'.$m[1];
            }
        }
        return $url;
    }

    private function wrapInSections(array $elements): array
    {
        if (empty($elements)) return $elements;

        $result = [];
        $pending = [];

        foreach ($elements as $el) {
            if ($el['type'] === 'section') {
                if (!empty($pending)) {
                    $result[] = $this->buildSection($pending);
                    $pending = [];
                }
                $result[] = $el;
            } else {
                $pending[] = $el;
            }
        }

        if (!empty($pending)) {
            $result[] = $this->buildSection($pending);
        }

        return $result;
    }

    private function buildSection(array $children): array
    {
        return [
            'type' => 'section',
            'settings' => [
                'layout' => 'boxed',
                'background_color' => '#ffffff',
                'padding_top' => '30px',
                'padding_bottom' => '30px',
            ],
            'children' => [[
                'type' => 'column',
                'settings' => ['column_width' => 'col-12'],
                'children' => $children,
            ]],
        ];
    }

    private function makeHeading(string $text, string $tag = 'h2', string $color = '#333333'): array
    {
        return $this->makeWidget('heading', [
            'title' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8'),
            'tag' => $tag,
            'color' => $color,
            'alignment' => 'left',
            'font_weight' => '700',
        ]);
    }

    private function makeText(?string $html = null, array $extra = []): array
    {
        $settings = array_merge([
            'content' => $html ?? '',
            'alignment' => 'left',
            'font_size' => '16px',
            'line_height' => '1.7',
        ], $extra);

        return $this->makeWidget('text', $settings);
    }

    private function makeWidget(string $type, array $settings): array
    {
        return [
            'type' => $type,
            'settings' => $settings,
        ];
    }
}
