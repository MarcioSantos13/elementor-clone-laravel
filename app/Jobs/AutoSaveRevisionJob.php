<?php

namespace App\Jobs;

use App\Models\Page;
use App\Models\Revision;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutoSaveRevisionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public Page $page,
        public int $userId,
        public ?array $content = null,
        public ?array $settings = null,
    ) {}

    public function handle(): void
    {
        $revision = new Revision();
        $revision->page_id = $this->page->id;
        $revision->user_id = $this->userId;
        $revision->content = $this->content ?? $this->page->content;
        $revision->settings = $this->settings ?? $this->page->settings;
        $revision->version = '1.0.' . time();
        $revision->label = 'Auto-save';
        $revision->type = 'auto_save';
        $revision->save();

        $this->page->touch();
    }
}
