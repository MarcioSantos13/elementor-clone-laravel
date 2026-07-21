<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new App\Services\PageBuilder\Core\HtmlImportService();
$html = '<h1>Teste</h1><p>Paragrafo</p><img src="test.jpg"><ul><li>A</li></ul><blockquote>Citacao</blockquote>';
$result = $service->convert($html, 'Teste');
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
