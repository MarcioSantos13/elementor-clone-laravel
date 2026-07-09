@extends('page-builder.layouts.app')

@section('title', $page->title)

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>{{ $page->title }}</h1>
            <div class="actions">
                <a href="{{ route('page-builder.editor', $page) }}" class="btn btn-primary">Editar com Construtor</a>
                <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">Voltar</a>
            </div>
        </div>

        <div class="page-preview">
            {!! $html !!}
        </div>
    </div>
@endsection

