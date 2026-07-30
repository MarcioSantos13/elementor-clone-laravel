<?php

namespace App\Jobs;

use App\Models\Page;
use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFormSubmissionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;

    public function __construct(
        public Page $page,
        public FormSubmission $submission,
    ) {}

    public function handle(): void
    {
        // Processar notificações da submissão do formulário
        $settings = $this->page->settings['form'] ?? [];

        if (!empty($settings['notification_email'])) {
            $emails = is_array($settings['notification_email'])
                ? $settings['notification_email']
                : [$settings['notification_email']];

            foreach ($emails as $email) {
                // Futuro: enviar email de notificação
            }
        }
    }
}
