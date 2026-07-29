<?php

namespace App\Services\PageBuilder\Popup;

use App\Models\Popup;
use App\Models\Page;
use App\Services\PageBuilder\Core\Renderer;
use Illuminate\Support\Facades\Cache;

class PopupService
{
    protected Renderer $renderer;

    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    public function getPublishedPopups(): array
    {
        return Cache::remember('published_popups', 300, function () {
            return Popup::published()->ordered()->with('page')->get()->all();
        });
    }

    public function renderPopup(Popup $popup): string
    {
        if (!$popup->isPublished()) return '';
        $page = $popup->page;
        if (!$page) return '';
        $content = $this->renderer->render($page, ['with_container' => false, 'theme' => 'popup']);
        $settings = $popup->settings ?? [];
        $width = $settings['width'] ?? '500px';
        $position = $settings['position'] ?? 'center';
        $animation = $settings['animation'] ?? 'fadeIn';
        $closeBtn = $settings['close_button'] ?? true;
        $overlay = $settings['overlay'] ?? true;

        $popupStyle = "width:{$width};";
        $positionClasses = str_replace('_', '-', $position);

        $triggers = $popup->triggers ?? [];
        $triggerAttrs = htmlspecialchars(json_encode($triggers), ENT_QUOTES);

        $overlayStyle = $overlay ? '' : 'pointer-events:none;background:transparent';
        $closeBtnHtml = $closeBtn ? '<button class="pb-popup-close" style="position:absolute;top:10px;right:12px;background:none;border:none;font-size:1.5rem;cursor:pointer;color:#94a3b8;z-index:1;padding:0;line-height:1">&times;</button>' : '';

        return <<<HTML
<div class="pb-popup-overlay" data-popup-id="{$popup->id}" data-triggers='{$triggerAttrs}' style="position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.5);backdrop-filter:blur(2px);{$overlayStyle}">
    <div class="pb-popup-content" style="background:#fff;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.2);{$popupStyle}max-height:90vh;overflow-y:auto;position:relative;animation:popupFadeIn .3s ease">
        {$closeBtnHtml}
        {$content}
    </div>
</div>
HTML;
    }

    public function renderAllPopups(): string
    {
        $popups = $this->getPublishedPopups();
        $html = '';
        foreach ($popups as $popup) {
            $html .= $this->renderPopup($popup);
        }
        return $html;
    }

    public function clearCache(): void
    {
        Cache::forget('published_popups');
    }
}
