@extends('page-builder.layouts.app')

@section('title', 'Pages')

@section('content')
    <div class="container">
        <div class="page-header">
            <h1>Páginas</h1>
            <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">+ Nova Página</a>
        </div>

        @if(session('success'))
            <div class="toast toast-success" id="success-toast">
                <span>&#10003;</span>
                <span>{{ session('success') }}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        @if($pages->count())
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Status</th>
                            <th>Slug</th>
                            <th>Atualizado</th>
                            <th class="th-actions">Ações</th>
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
                                    <a href="{{ route('page-builder.editor', $page) }}" class="btn btn-sm btn-primary">Editar</a>
                                    <a href="{{ route('page-builder.render', $page) }}" class="btn btn-sm btn-secondary" target="_blank">Ver</a>
                                    <button type="button" class="btn btn-sm btn-info" onclick="duplicatePage({{ $page->id }})" title="Duplicar">&#128203;</button>
                                    <a href="{{ route('page-builder.pages.export', $page) }}" class="btn btn-sm btn-secondary" download title="Exportar JSON">&#128229;</a>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="copyHtml({{ $page->id }})" title="Copiar HTML">&#128203;</button>
                                    <button type="button" class="btn btn-sm btn-secondary" onclick="openImportModal({{ $page->id }})" title="Importar JSON">&#128228;</button>
                                    <form action="{{ route('page-builder.pages.destroy', $page) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir &quot;{{ $page->title }}&quot;?')">&#128465;</button>
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
                <h2>Nenhuma página ainda</h2>
                <p>Crie sua primeira página para começar a usar o construtor.</p>
                <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">Criar Primeira Página</a>
            </div>
        @endif
    </div>

    <div id="import-modal" style="display:none">
        <div class="modal-overlay" onclick="closeImportModal()">
            <div class="modal-content" onclick="event.stopPropagation()">
                <div class="modal-header">
                    <h3>Importar Página</h3>
                    <button class="modal-close" onclick="closeImportModal()">&times;</button>
                </div>
                <div class="modal-body">
                    <p style="color:#64748b;margin-bottom:1rem">Selecione um arquivo .json exportado de outra página para importar.</p>
                    <input type="file" id="import-file" accept=".json" style="display:block;padding:.5rem;border:2px dashed #e2e8f0;border-radius:8px;width:100%;cursor:pointer">
                    <div id="import-error" style="color:#ef4444;margin-top:.5rem;display:none;font-size:.85rem"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeImportModal()">Cancelar</button>
                    <button class="btn btn-primary" onclick="importPage()" id="import-btn">Importar</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const csrf = '{{ csrf_token() }}';

        function duplicatePage(id) {
            fetch(`/page-builder/pages/${id}/duplicate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
            })
            .then(r => r.json())
            .then(() => { showToast('Página duplicada!'); location.reload(); })
            .catch(() => showToast('Falha ao duplicar', true));
        }

        function copyHtml(id) {
            fetch(`/page-builder/pages/${id}/render?format=inner`)
                .then(r => r.text())
                .then(html => {
                    navigator.clipboard.writeText(html).then(() => {
                        showToast('HTML copied to clipboard!');
                    }).catch(() => {
                        const ta = document.createElement('textarea');
                        ta.value = html;
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();
                        showToast('HTML copiado para a área de transferência!');
                    });
                })
                .catch(() => showToast('Falha ao copiar HTML', true));
        }

        function showToast(msg, isError) {
            const t = document.createElement('div');
            t.className = 'toast ' + (isError ? 'toast-error' : 'toast-success');
            t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;animation:slideInToast .3s cubic-bezier(.16,1,.3,1);padding:.75rem 1.25rem;border-radius:10px;font-size:.875rem;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.1);display:flex;align-items:center;gap:.5rem';
            if (isError) {
                t.style.background = '#fef2f2';
                t.style.color = '#991b1b';
                t.style.border = '1px solid #fecaca';
            } else {
                t.style.background = '#dcfce7';
                t.style.color = '#166534';
                t.style.border = '1px solid #bbf7d0';
            }
            t.innerHTML = (isError ? '&#10060; ' : '&#10003; ') + msg;
            document.body.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }, 3000);
        }

        let importPageId = null;

        function openImportModal(id) {
            importPageId = id || null;
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
                document.getElementById('import-error').textContent = 'Selecione um arquivo.';
                document.getElementById('import-error').style.display = '';
                return;
            }
            const btn = document.getElementById('import-btn');
            btn.disabled = true;
            btn.textContent = 'Importando...';
            const reader = new FileReader();
            reader.onload = function(e) {
                let data;
                try {
                    data = JSON.parse(e.target.result);
                } catch {
                    document.getElementById('import-error').textContent = 'Arquivo JSON inválido.';
                    document.getElementById('import-error').style.display = '';
                    btn.disabled = false; btn.textContent = 'Importar';
                    return;
                }
                fetch('/page-builder/pages/import', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ data }),
                })
                .then(r => r.json())
                .then(() => { showToast('Página importada!'); location.reload(); })
                .catch(() => {
                    document.getElementById('import-error').textContent = 'Falha na importação.';
                    document.getElementById('import-error').style.display = '';
                    btn.disabled = false; btn.textContent = 'Importar';
                });
            };
            reader.readAsText(file);
        }

        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('success-toast');
            if (toast) {
                setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .3s'; setTimeout(() => toast.remove(), 300); }, 4000);
            }
        });
    </script>
    @endpush
@endsection
