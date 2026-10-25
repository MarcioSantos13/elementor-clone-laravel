<?php

namespace App\Services\PageBuilder\Project;

use App\Models\Page;
use App\Models\Project;
use App\Services\PageBuilder\Theme\ThemeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use XMLWriter;
use ZipArchive;

class ProjectMoodleExportService
{
    public function __construct(protected ThemeService $themeService) {}

    public function build(Project $project): string
    {
        $pages = $this->exportablePages($project);

        if ($pages->isEmpty()) {
            throw new RuntimeException('Publique ao menos uma página do projeto antes de exportar para o Moodle.');
        }

        $path = tempnam(sys_get_temp_dir(), 'pb-moodle-');

        if ($path === false) {
            throw new RuntimeException('Não foi possível criar o arquivo temporário do pacote.');
        }

        $media = [];
        $pageFiles = $this->pageFiles($pages);
        $slugMap = [];

        foreach ($pageFiles as $pageFile) {
            $slugMap[$pageFile['slug']] = $pageFile['filename'];
        }

        $zip = new ZipArchive;
        $opened = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            @unlink($path);

            throw new RuntimeException('Não foi possível criar o pacote Moodle.');
        }

        try {
            foreach ($pages as $page) {
                $file = $pageFiles[$page->id]['file'];
                $html = $this->themeService->renderPageWithTheme($page);

                $html = $this->rewriteInternalLinks($html, $slugMap);
                $html = $this->rewriteMediaUrls($html, $media);

                if (! $zip->addFromString($file, $html)) {
                    throw new RuntimeException("Não foi possível adicionar a página {$page->title} ao pacote.");
                }
            }

            $zip->addFromString('imsmanifest.xml', $this->manifest($project, $pageFiles, $media));

            foreach ($media as $item) {
                if (! $zip->addFile($item['source'], $item['file'])) {
                    throw new RuntimeException("Não foi possível adicionar o arquivo {$item['file']} ao pacote.");
                }
            }

            if (! $zip->close()) {
                throw new RuntimeException('Não foi possível finalizar o pacote Moodle.');
            }
        } catch (\Throwable $exception) {
            $zip->close();
            @unlink($path);

            throw $exception;
        }

        return $path;
    }

    public function downloadName(Project $project): string
    {
        return (Str::slug($project->name) ?: 'projeto').'-moodle-ims.zip';
    }

    protected function exportablePages(Project $project): Collection
    {
        return $project->pages()
            ->where('status', 'published')
            ->orderBy('id')
            ->get()
            ->filter(fn (Page $page) => ! (bool) data_get($page->meta_data ?? [], 'internal', false))
            ->values();
    }

    protected function pageFiles(Collection $pages): array
    {
        $files = [];
        $used = [];

        foreach ($pages as $page) {
            $base = Str::slug($page->slug ?: $page->title) ?: 'pagina-'.$page->id;
            $filename = $base.'.html';
            $suffix = 2;

            while (isset($used[$filename])) {
                $filename = $base.'-'.$suffix++.'.html';
            }

            $used[$filename] = true;
            $files[$page->id] = [
                'file' => 'pages/'.$filename,
                'filename' => $filename,
                'slug' => $page->slug,
                'title' => $page->title,
            ];
        }

        return $files;
    }

    protected function rewriteInternalLinks(string $html, array $slugMap): string
    {
        return preg_replace_callback(
            '~(\bhref\s*=\s*)(["\'])(?:https?://[^"\']+)?/p/([^"\'/?#]+)([^"\']*)\2~i',
            function (array $matches) use ($slugMap) {
                $slug = rawurldecode($matches[3]);

                if (! isset($slugMap[$slug])) {
                    return $matches[0];
                }

                return $matches[1].$matches[2].$slugMap[$slug].$matches[4].$matches[2];
            },
            $html
        ) ?? $html;
    }

    protected function rewriteMediaUrls(string $html, array &$media): string
    {
        $html = preg_replace_callback(
            '~(\b(?:src|href)\s*=\s*)(["\'])(?:https?://[^"\']+)?(/(?:storage|uploads)/[^"\']*)\2~i',
            function (array $matches) use (&$media) {
                $entry = $this->registerMedia($matches[3], $media);
                $url = $entry ? '../'.$entry : $this->absoluteUrl($matches[3]);

                return $matches[1].$matches[2].$url.$matches[2];
            },
            $html
        ) ?? $html;

        return preg_replace_callback(
            '~url\(\s*(["\']?)(?:https?://[^)"\']+)?(/(?:storage|uploads)/[^)"\']*)\1\s*\)~i',
            function (array $matches) use (&$media) {
                $entry = $this->registerMedia($matches[2], $media);
                $url = $entry ? '../'.$entry : $this->absoluteUrl($matches[2]);

                return 'url('.$matches[1].$url.$matches[1].')';
            },
            $html
        ) ?? $html;
    }

    protected function registerMedia(string $urlPath, array &$media): ?string
    {
        if (isset($media[$urlPath])) {
            return $media[$urlPath]['file'];
        }

        $path = parse_url($urlPath, PHP_URL_PATH);

        if (! is_string($path)) {
            return null;
        }

        $path = rawurldecode($path);
        $source = $this->sourcePath($path);

        if ($source === null) {
            return null;
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        $extension = preg_replace('/[^a-z0-9]/', '', $extension) ?: 'bin';
        $base = Str::slug(pathinfo($path, PATHINFO_FILENAME)) ?: 'arquivo';
        $entry = 'media/'.substr(sha1($path), 0, 12).'-'.$base.'.'.$extension;

        $media[$urlPath] = [
            'file' => $entry,
            'source' => $source,
        ];

        return $entry;
    }

    protected function sourcePath(string $path): ?string
    {
        if (str_starts_with($path, '/storage/')) {
            $relative = ltrim(Str::after($path, '/storage/'), '/');
            $disk = Storage::disk('public');

            if ($relative === '' || ! $disk->exists($relative)) {
                return null;
            }

            return $this->safePath($disk->path($relative), $disk->path(''));
        }

        if (str_starts_with($path, '/uploads/')) {
            $relative = ltrim(Str::after($path, '/uploads/'), '/');
            $candidate = public_path($relative);

            if (! is_file($candidate)) {
                return null;
            }

            return $this->safePath($candidate, public_path('uploads'));
        }

        return null;
    }

    protected function safePath(string $path, string $root): ?string
    {
        $realPath = realpath($path);
        $realRoot = realpath($root);

        if ($realPath === false || $realRoot === false) {
            return null;
        }

        if (! str_starts_with($realPath, $realRoot.DIRECTORY_SEPARATOR)) {
            return null;
        }

        return $realPath;
    }

    protected function absoluteUrl(string $path): string
    {
        return rtrim(config('app.url') ?: url('/'), '/').$path;
    }

    protected function manifest(Project $project, array $pageFiles, array $media): string
    {
        $writer = new XMLWriter;
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');
        $writer->startDocument('1.0', 'UTF-8');

        $writer->startElement('manifest');
        $writer->writeAttribute('identifier', 'pb-project-'.$project->id.'-'.(Str::slug($project->name) ?: 'projeto'));
        $writer->writeAttribute('version', '1.0');
        $writer->writeAttribute('xmlns', 'http://www.imsglobal.org/xsd/imscp_v1p1');
        $writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $writer->writeAttribute('xsi:schemaLocation', 'http://www.imsglobal.org/xsd/imscp_v1p1 http://www.imsglobal.org/xsd/imscp_v1p1.xsd');

        $writer->startElement('metadata');
        $writer->writeElement('schema', 'IMS Content');
        $writer->writeElement('schemaversion', '1.1.4');
        $writer->endElement();

        $writer->startElement('organizations');
        $writer->writeAttribute('default', 'ORG-1');
        $writer->startElement('organization');
        $writer->writeAttribute('identifier', 'ORG-1');
        $writer->writeAttribute('structure', 'rooted-hierarchy');

        $writer->startElement('item');
        $writer->writeAttribute('identifier', 'ITEM-ROOT');
        $writer->writeElement('title', $project->name);

        foreach ($pageFiles as $pageId => $pageFile) {
            $writer->startElement('item');
            $writer->writeAttribute('identifier', 'ITEM-PAGE-'.$pageId);
            $writer->writeAttribute('identifierref', 'RES-PAGE-'.$pageId);
            $writer->writeElement('title', $pageFile['title']);
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endElement();
        $writer->endElement();

        $writer->startElement('resources');

        foreach ($pageFiles as $pageId => $pageFile) {
            $writer->startElement('resource');
            $writer->writeAttribute('identifier', 'RES-PAGE-'.$pageId);
            $writer->writeAttribute('type', 'webcontent');
            $writer->writeAttribute('href', $pageFile['file']);

            $writer->startElement('file');
            $writer->writeAttribute('href', $pageFile['file']);
            $writer->endElement();

            foreach ($media as $item) {
                $writer->startElement('file');
                $writer->writeAttribute('href', $item['file']);
                $writer->endElement();
            }

            $writer->endElement();
        }

        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory();
    }
}
