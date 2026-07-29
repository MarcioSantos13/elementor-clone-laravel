<?php

namespace App\Http\Middleware;

use App\Services\PageBuilder\Theme\ThemeService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class InjectThemeTemplates
{
    protected ThemeService $themeService;

    public function __construct(ThemeService $themeService)
    {
        $this->themeService = $themeService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldInject($request, $response)) {
            return $response;
        }

        $content = $response->getContent();
        $pageSlug = $request->route('slug');

        $templates = $this->themeService->resolveAllForPage($pageSlug);
        $headerHtml = $this->themeService->renderTemplate($templates['header'] ?? null);
        $footerHtml = $this->themeService->renderTemplate($templates['footer'] ?? null);

        if ($headerHtml === '' && $footerHtml === '') {
            return $response;
        }

        $bodyPos = strrpos($content, '</body>');
        if ($bodyPos === false) {
            return $response;
        }

        $modified = substr($content, 0, $bodyPos);
        $modified .= $footerHtml;
        $modified .= substr($content, $bodyPos);

        $headPos = strpos($modified, '<body');
        if ($headPos !== false) {
            $closeTagPos = strpos($modified, '>', $headPos);
            if ($closeTagPos !== false) {
                $modified = substr($modified, 0, $closeTagPos + 1)
                    . $headerHtml
                    . substr($modified, $closeTagPos + 1);
            }
        }

        $response->setContent($modified);

        return $response;
    }

    protected function shouldInject(Request $request, Response $response): bool
    {
        if ($response->isRedirection()) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return false;
        }

        if ($request->expectsJson()) {
            return false;
        }

        if ($request->is('page-builder/*') || $request->is('_debugbar/*') || $request->is('telescope/*')) {
            return false;
        }

        if ($request->is('p/*')) {
            return false;
        }

        return true;
    }
}
