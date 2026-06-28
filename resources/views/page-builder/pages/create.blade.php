@extends('page-builder.layouts.app')

@section('title', 'Create Page')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Create New Page</h1>
            <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">&larr; Back to Pages</a>
        </div>

        <div class="card create-page-card">
            <form action="{{ route('page-builder.pages.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}" placeholder="e.g. Home, About Us, Contact" required autofocus>
                    @error('title')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control">
                            <option value="draft">Draft</option>
                            <option value="published">Published</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Template <span class="optional">(optional — pre-populates the page)</span></label>
                    <div class="template-grid">
                        <label class="template-card {{ old('template') === '' || old('template') === null ? 'selected' : '' }}">
                            <input type="radio" name="template" value="" {{ old('template') === '' || old('template') === null ? 'checked' : '' }}>
                            <div class="template-card-preview blank">&#9635;</div>
                            <div class="template-card-info">
                                <strong>Blank Page</strong>
                                <span>Start from scratch</span>
                            </div>
                        </label>
                        @foreach($templates as $key => $name)
                            <label class="template-card {{ old('template') === $key ? 'selected' : '' }}">
                                <input type="radio" name="template" value="{{ $key }}" {{ old('template') === $key ? 'checked' : '' }}>
                                <div class="template-card-preview {{ $key }}">
                                    @if($key === 'landing')&#127968;@elseif($key === 'about')&#128100;@elseif($key === 'contact')&#128222;@else&#9635;@endif
                                </div>
                                <div class="template-card-info">
                                    <strong>{{ $name }}</strong>
                                    <span>
                                        @if($key === 'landing')Hero section with CTA
                                        @elseif($key === 'about')Company presentation
                                        @elseif($key === 'contact')Contact form layout
                                        @else&#8205;@endif
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="_redirect" value="index" class="btn btn-primary">Create &amp; Back to List</button>
                    <button type="submit" name="_redirect" value="editor" class="btn btn-success">Create &amp; Open Editor</button>
                    <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <style>
        .create-page-card { max-width: 700px; }
        .template-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem; margin-top: .3rem; }
        .template-card {
            display: block; cursor: pointer; border: 2px solid #ddd; border-radius: 8px;
            overflow: hidden; transition: all .15s; background: #fff;
        }
        .template-card:hover { border-color: #007bff; }
        .template-card.selected { border-color: #007bff; box-shadow: 0 0 0 2px rgba(0,123,255,.2); }
        .template-card input[type="radio"] { display: none; }
        .template-card-preview {
            height: 90px; display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; background: #f8f9fa; color: #666;
        }
        .template-card-preview.landing { background: #1a1a2e; color: #fff; }
        .template-card-preview.about { background: #f0f2f5; color: #333; }
        .template-card-preview.contact { background: #e8ecf1; color: #333; }
        .template-card-info { padding: .6rem; }
        .template-card-info strong { display: block; font-size: .85rem; margin-bottom: .15rem; }
        .template-card-info span { font-size: .75rem; color: #888; }
        .form-actions { display: flex; gap: .75rem; align-items: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #eee; }
    </style>
@endsection
