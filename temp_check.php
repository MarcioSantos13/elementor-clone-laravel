<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use App\Models\Element;
use App\Models\Page;
use App\Services\PageBuilder\Core\PageBuilderService;

$page = Page::find(13);
$elements = $page->allElements()->orderBy('order')->get();

echo "=== Elements in DB ===\n";
foreach ($elements as $el) {
    echo "#{$el->id} type={$el->type} parent=" . ($el->parent_id ?? 'NULL') . " order={$el->order}\n";
}

echo "\n=== Render output ===\n";
$html = app(PageBuilderService::class)->renderPage($page);
file_put_contents(__DIR__ . '/temp_render.html', $html);

// Check which element types appear
$types = ['section', 'text', 'divider', 'video', 'image', 'heading', 'button', 'icon', 'callout', 'table', 'math', 'gallery', 'form', 'tabs', 'accordion', 'spacer'];
foreach ($types as $t) {
    $count = substr_count($html, "data-element-type=\"{$t}\"");
    if ($count > 0) echo "  {$t}: {$count}x\n";
}

echo "\n=== HTML length: " . strlen($html) . " bytes ===\n";
