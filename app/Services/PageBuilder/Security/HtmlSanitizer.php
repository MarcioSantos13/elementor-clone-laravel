<?php

namespace App\Services\PageBuilder\Security;

class HtmlSanitizer
{
    public static function sanitize(string $html, array $options = []): string
    {
        $html = self::removeEventHandlers($html);
        $html = self::sanitizeUrls($html);

        if ($options['strip_scripts'] ?? true) {
            $html = self::removeScriptTags($html);
        }

        return $html;
    }

    public static function sanitizeCustomCss(?string $css): string
    {
        if ($css === null || $css === '') {
            return '';
        }

        $css = preg_replace('/expression\s*\(/i', '', $css);
        $css = preg_replace('/javascript\s*:/i', '', $css);
        $css = preg_replace('/vbscript\s*:/i', '', $css);
        $css = preg_replace('/-moz-binding\s*:/i', '', $css);

        return strip_tags($css);
    }

    public static function sanitizeCustomJs(?string $js): string
    {
        if ($js === null || $js === '') {
            return '';
        }

        $js = preg_replace('/document\.write\s*\(/i', '// ', $js);
        $js = preg_replace('/eval\s*\(/i', '// ', $js);

        return $js;
    }

    private static function removeEventHandlers(string $html): string
    {
        $html = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/si', '', $html);
        $html = preg_replace("/\s+on\w+\s*=\s*'[^']*'/si", '', $html);

        return preg_replace('/\s+on\w+\s*=\s*[^\s>]+/si', '', $html);
    }

    private static function removeScriptTags(string $html): string
    {
        return preg_replace('/<script\b[^>]*>(.*?)<\/script>/si', '', $html);
    }

    private static function sanitizeUrls(string $html): string
    {
        $html = preg_replace('/\s+srcdoc\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/si', '', $html);

        return preg_replace_callback(
            '/((?:href|src|action|formaction|poster)\s*=\s*)("[^"]*"|\'[^\']*\'|[^\s>]+)/si',
            function ($matches) {
                $value = trim($matches[2], " \t\n\r\0\x0B\"'");

                if (self::isDangerousUrl($value)) {
                    return $matches[1] . '"#"';
                }

                return $matches[0];
            },
            $html
        );
    }

    private static function isDangerousUrl(string $url): bool
    {
        $url = html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $url = preg_replace('/[\x00-\x20]+/', '', $url);

        return (bool) preg_match(
            '/^(javascript|vbscript|data:text\/html|data:application\/xhtml\+xml|data:image\/svg\+xml)/i',
            $url
        );
    }
}
