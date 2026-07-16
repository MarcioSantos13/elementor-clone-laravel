<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Editando: {{ $page->title }} - {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.11/dist/katex.min.js"></script>
@include('page-builder.editor.css')
</head>
<body>
@include('page-builder.editor.toolbar')

<div class="pb-layout">
    @include('page-builder.editor.widget-panel')
    @include('page-builder.editor.canvas')
    @include('page-builder.editor.settings-panel')
</div>

@include('page-builder.editor.navigator')
@include('page-builder.editor.scripts')
</body>
</html>