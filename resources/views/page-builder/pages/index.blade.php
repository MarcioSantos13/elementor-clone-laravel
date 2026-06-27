@extends('page-builder.layouts.app')

@section('title', 'Pages')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Pages</h1>
            <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">+ Create New Page</a>
        </div>

        @if(session('success'))
            <div class="toast toast-success" id="success-toast">
                <span>&#10003;</span>
                <span>{{ session('success') }}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        @if($pages->count())
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Slug</th>
                            <th>Updated</th>
                            <th class="th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pages as $page)
                            <tr>
                                <td class="td-title">{{ $page->title }}</td>
                                <td>
                                    <span class="badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}">
                                        {{ $page->status }}
                                    </span>
                                </td>
                                <td><code>{{ $page->slug }}</code></td>
                                <td class="td-date">{{ $page->updated_at->diffForHumans() }}</td>
                                <td class="td-actions">
                                    <a href="{{ route('page-builder.editor', $page) }}" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="{{ route('page-builder.render', $page) }}" class="btn btn-sm btn-secondary" target="_blank">View</a>
                                    <form action="{{ route('page-builder.pages.destroy', $page) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete &quot;{{ $page->title }}&quot;?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                {{ $pages->links() }}
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">&#128196;</div>
                <h2>No pages yet</h2>
                <p>Create your first page to get started with the Page Builder.</p>
                <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">Create Your First Page</a>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        const toast = document.getElementById('success-toast');
        if (toast) {
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 4000);
        }
    </script>
    @endpush
@endsection
