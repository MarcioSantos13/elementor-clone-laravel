<?php

return [
    'templates' => [
        'cache' => env('PAGE_BUILDER_CACHE', true),
        'cache_ttl' => env('PAGE_BUILDER_CACHE_TTL', 3600),
    ],

    'revisions' => [
        'max_per_page' => env('PAGE_BUILDER_MAX_REVISIONS', 50),
        'auto_save_interval' => env('PAGE_BUILDER_AUTO_SAVE_INTERVAL', 60),
    ],

    'widgets' => [
        \App\Services\PageBuilder\Widgets\HeadingWidget::class,
        \App\Services\PageBuilder\Widgets\TextWidget::class,
        \App\Services\PageBuilder\Widgets\ImageWidget::class,
        \App\Services\PageBuilder\Widgets\ButtonWidget::class,
        \App\Services\PageBuilder\Widgets\SectionWidget::class,
        \App\Services\PageBuilder\Widgets\ColumnWidget::class,
    ],

    'upload' => [
        'max_file_size' => env('PAGE_BUILDER_MAX_FILE_SIZE', 10240),
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp4', 'webm'],
        'path' => 'page-builder',
    ],

    'editor' => [
        'min_width' => 320,
        'max_width' => 1920,
        'breakpoints' => [
            'mobile' => 768,
            'tablet' => 1024,
            'desktop' => 1920,
        ],
    ],
];
