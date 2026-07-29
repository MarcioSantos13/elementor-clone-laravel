@extends('page-builder.layouts.app')

@section('title', isset($themeTemplate) ? 'Edit Theme Template' : 'New Theme Template')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>{{ isset($themeTemplate) ? 'Edit Theme Template' : 'New Theme Template' }}</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">Configure the template type, conditions, and associated page</p>
        </div>
        <a href="{{ route('page-builder.themes.index') }}" class="btn btn-secondary">
            &larr; Back
        </a>
    </div>

    <div class="card">
        <form action="{{ isset($themeTemplate) ? route('page-builder.themes.update', $themeTemplate) : route('page-builder.themes.store') }}" method="POST">
            @csrf
            @if(isset($themeTemplate)) @method('PUT') @endif

            <div class="form-row">
                <div class="form-group" style="flex:2">
                    <label for="title">Title <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="{{ old('title', $themeTemplate->title ?? '') }}"
                           placeholder="Ex: Main Header, Blog Footer">
                </div>

                <div class="form-group" style="flex:1">
                    <label for="type">Type <span class="required">*</span></label>
                    <select id="type" name="type" class="form-control form-select" required>
                        <option value="">Select type...</option>
                        @foreach($types as $key => $label)
                            <option value="{{ $key }}" {{ (old('type', $themeTemplate->type ?? request('type', '')) === $key) ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="page_id">Associated Page <span class="optional">(optional)</span></label>
                <select id="page_id" name="page_id" class="form-control form-select">
                    <option value="">-- Create new blank page --</option>
                    @foreach($pages as $p)
                        <option value="{{ $p->id }}" {{ old('page_id', $themeTemplate->page_id ?? '') == $p->id ? 'selected' : '' }}>
                            {{ $p->title }}
                        </option>
                    @endforeach
                </select>
                <p style="font-size:.78rem;color:#94a3b8;margin-top:.35rem">
                    If no page is selected, a new page will be created automatically.
                </p>
            </div>

            @if(!isset($themeTemplate))
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control form-select">
                    <option value="draft" selected>Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
            @endif

            <div style="display:flex;gap:.75rem;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid #e2e8f0">
                <button type="submit" class="btn btn-primary">
                    {{ isset($themeTemplate) ? 'Update Template' : 'Create Template' }}
                </button>
                @if(!isset($themeTemplate))
                    <button type="submit" name="next" value="editor" class="btn btn-primary" style="background:linear-gradient(135deg,#22c55e,#16a34a)">
                        Create & Open Editor
                    </button>
                @endif
                <a href="{{ route('page-builder.themes.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
