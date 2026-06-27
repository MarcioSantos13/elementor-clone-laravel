@extends('page-builder.layouts.app')

@section('title', 'Create Page')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Create New Page</h1>
            <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">&larr; Back to Pages</a>
        </div>

        <div class="card">
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

                    <div class="form-group">
                        <label for="template">Template <span class="optional">(optional)</span></label>
                        <input type="text" name="template" id="template" class="form-control" value="{{ old('template') }}" placeholder="Leave blank for default">
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
@endsection
