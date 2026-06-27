@extends('page-builder.layouts.app')

@section('title', $page->title)

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>{{ $page->title }}</h1>
            <div class="actions">
                <a href="{{ route('page-builder.editor', $page) }}" class="btn btn-primary">Edit with Builder</a>
                <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">Back</a>
            </div>
        </div>

        <div class="page-preview">
            {!! $html !!}
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/page-builder.css') }}">
@endpush
