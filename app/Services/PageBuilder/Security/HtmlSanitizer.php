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
        return preg_replace('/\s+on\w+="[^"]*"/si', '', $html);
    }

    private static function removeScriptTags(string $html): string
    {
        return preg_replace('/<script\b[^>]*>.*?<\/script>/si', '', $html);
    }

    private static function sanitizeUrls(string $html): string
    {
        $dangerous = ['javascript:', 'vbscript:', 'data:text/html', 'data:text/javascript'];

        return preg_replace_callback(
            '/((?:href|src|action|formaction|poster)\s*=\s*")([^"]*)(")/si',
            function ($matches) use ($dangerous) {
                $url = strtolower(trim($matches[2]));
                foreach ($dangerous as $prefix) {
                    if (str_starts_with($url, $prefix)) {
                        return $matches[1] . '#' . $matches[3];
                    }
                }
                return $matches[0];
            },
            $html
        );
    }
}
