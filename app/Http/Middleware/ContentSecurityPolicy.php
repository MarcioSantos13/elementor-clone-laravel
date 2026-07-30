<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$response->headers->has('Content-Security-Policy')) {
            $csp = collect([
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://www.youtube.com https://player.vimeo.com",
                "style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com",
                "img-src 'self' data: blob: https: http:",
                "font-src 'self' data: https://cdnjs.cloudflare.com https://fonts.gstatic.com https://fonts.googleapis.com",
                "frame-src 'self' https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com",
                "connect-src 'self'",
                "media-src 'self' https:",
                "object-src 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ])->implode('; ');

            $response->headers->set('Content-Security-Policy', $csp);
        }

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        return $response;
    }
}

