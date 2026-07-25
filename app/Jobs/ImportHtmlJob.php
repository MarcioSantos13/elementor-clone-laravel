<?php

namespace App\Jobs;

use App\Services\PageBuilder\Core\HtmlImportService;
use App\Services\PageBuilder\Core\PageBuilderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ImportHtmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    public ?int $pageId = null;

    public function __construct(
        public string $html,
        public int $userId,
        public string $title = 'Página Importada',
    ) {}

    public function handle(): array
    {
        $htmlImport = app(HtmlImportService::class);
        $pageBuilder = app(PageBuilderService::class);

        $converted = $htmlImport->convert($this->html, $this->title);

        $page = $pageBuilder->importPage($converted);

        $this->pageId = $page->id;

        return [
            'page_id' => $page->id,
            'title' => $page->title,
        ];
    }
}
