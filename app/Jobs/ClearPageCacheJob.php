<?php

namespace App\Jobs;

use App\Models\Page;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class ClearPageCacheJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 10;

    public function __construct(
        public Page $page,
    ) {}

    public function handle(): void
    {
        $this->page->touch();

        try {
            Cache::tags("page:{$this->page->id}")->flush();
        } catch (\Throwable) {
        }
    }
}
