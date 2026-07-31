<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\InjectThemeTemplates::class);
        $middleware->appendToGroup('web', \App\Http\Middleware\ContentSecurityPolicy::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(function ($request, \Throwable $e) {
            return $request->expectsJson() || $request->is('api/*');
        });

        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Resource not found'], 404);
            }
        });

        $exceptions->render(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['error' => 'Forbidden'], 403);
            }
        });
    })->create();
