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
                                    <button type="button" class="btn btn-sm btn-info" onclick="duplicatePage({{ $page->id }})">Duplicate</button>
                                    <a href="{{ route('page-builder.pages.export', $page) }}" class="btn btn-sm btn-secondary" download>Export</a>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="copyHtml({{ $page->id }})">Copy HTML</button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="openImportModal({{ $page->id }})">Import</button>
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

    <div class="modal" id="import-modal" style="display:none">
        <div class="modal-overlay" onclick="closeImportModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h3>Import Page</h3>
                <button class="modal-close" onclick="closeImportModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Select a .json file exported from another page to import it.</p>
                <input type="file" id="import-file" accept=".json">
                <div id="import-error" style="color:#dc3545;margin-top:.5rem;display:none"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeImportModal()">Cancel</button>
                <button class="btn btn-primary" onclick="importPage()" id="import-btn">Import</button>
            </div>
        </div>
    </div>

    <div class="toast" id="copy-toast" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;background:#28a745;color:#fff;padding:.75rem 1.25rem;border-radius:6px;z-index:9999;font-size:.9rem;box-shadow:0 4px 12px rgba(0,0,0,.2)"></div>

    @push('scripts')
    <script>
        const csrf = '{{ csrf_token() }}';

        function duplicatePage(id) {
            fetch(`/page-builder/pages/${id}/duplicate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
            })
            .then(r => r.json())
            .then(() => location.reload());
        }

        function copyHtml(id) {
            fetch(`/page-builder/pages/${id}/render?format=inner`)
                .then(r => r.text())
                .then(html => {
                    navigator.clipboard.writeText(html).then(() => {
                        showCopyToast('HTML copied to clipboard!');
                    }).catch(() => {
                        const ta = document.createElement('textarea');
                        ta.value = html;
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();
                        showCopyToast('HTML copied to clipboard!');
                    });
                });
        }

        function showCopyToast(msg) {
            const t = document.getElementById('copy-toast');
            t.textContent = msg;
            t.style.display = '';
            setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => { t.style.display = 'none'; t.style.opacity = '1'; }, 300); }, 2500);
        }

        let importPageId = null;

        function openImportModal(id) {
            importPageId = id;
            document.getElementById('import-modal').style.display = '';
            document.getElementById('import-file').value = '';
            document.getElementById('import-error').style.display = 'none';
            document.getElementById('import-btn').disabled = false;
        }

        function closeImportModal() {
            document.getElementById('import-modal').style.display = 'none';
            importPageId = null;
        }

        function importPage() {
            const fileInput = document.getElementById('import-file');
            const file = fileInput.files[0];
            if (!file) {
                document.getElementById('import-error').textContent = 'Please select a file.';
                document.getElementById('import-error').style.display = '';
                return;
            }
            const btn = document.getElementById('import-btn');
            btn.disabled = true;
            btn.textContent = 'Importing...';
            const reader = new FileReader();
            reader.onload = function(e) {
                let data;
                try {
                    data = JSON.parse(e.target.result);
                } catch {
                    document.getElementById('import-error').textContent = 'Invalid JSON file.';
                    document.getElementById('import-error').style.display = '';
                    btn.disabled = false; btn.textContent = 'Import';
                    return;
                }
                fetch('/page-builder/pages/import', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ data }),
                })
                .then(r => r.json())
                .then(() => { location.reload(); })
                .catch(() => {
                    document.getElementById('import-error').textContent = 'Import failed.';
                    document.getElementById('import-error').style.display = '';
                    btn.disabled = false; btn.textContent = 'Import';
                });
            };
            reader.readAsText(file);
        }

        const toast = document.getElementById('success-toast');
        if (toast) {
            setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 400); }, 4000);
        }
    </script>
    @endpush
@endsection
