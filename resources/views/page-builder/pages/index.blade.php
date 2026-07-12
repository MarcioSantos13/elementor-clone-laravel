@extends('page-builder.layouts.app')

@section('title', 'Páginas')

@section('content')
    <div class="container">

        <div class="pb-hero">
            <div class="pb-hero-content">
                <div>
                    <h1 class="pb-hero-title">Páginas</h1>
                    <p class="pb-hero-sub">Gerencie suas páginas e cursos no construtor visual</p>
                </div>
                <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary pb-hero-btn">
                    <span>+</span> Nova Página
                </a>
            </div>
            <div class="pb-stats">
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $pages->total() }}</span>
                    <span class="pb-stat-label">Total</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number pb-stat-published">{{ $pages->getCollection()->where('status','published')->count() }}</span>
                    <span class="pb-stat-label">Publicadas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number pb-stat-draft">{{ $pages->getCollection()->where('status','draft')->count() }}</span>
                    <span class="pb-stat-label">Rascunhos</span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="toast toast-success" id="success-toast">
                <span>&#10003;</span>
                <span>{{ session('success') }}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        @if($pages->count())
            <div class="pb-toolbar">
                <div class="pb-search">
                    <span class="pb-search-icon">&#128269;</span>
                    <input type="text" id="search-input" placeholder="Buscar páginas..." class="pb-search-input">
                </div>
                <div class="pb-view-toggle">
                    <button class="pb-view-btn active" data-view="grid" onclick="setView('grid')" title="Grade">&#9638;</button>
                    <button class="pb-view-btn" data-view="list" onclick="setView('list')" title="Lista">&#9776;</button>
                </div>
            </div>

            <div id="pages-grid" class="pb-pages-grid">
                @foreach($pages as $page)
                    <div class="pb-page-card" data-title="{{ strtolower($page->title) }}" data-slug="{{ strtolower($page->slug) }}">
                        <div class="pb-card-header">
                            <span class="badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}">
                                {{ $page->status === 'published' ? 'Publicada' : 'Rascunho' }}
                            </span>
                            <div class="pb-card-dropdown">
                                <button class="pb-card-more" onclick="toggleDropdown(this)" title="Mais opções">&#8943;</button>
                                <div class="pb-dropdown-menu">
                                    <a href="{{ route('page-builder.editor', $page) }}" class="pb-dropdown-item">&#9998; Editar</a>
                                    <a href="{{ route('page-builder.render', $page) }}" class="pb-dropdown-item" target="_blank">&#128065; Visualizar</a>
                                    <button class="pb-dropdown-item" onclick="duplicatePage({{ $page->id }})">&#128203; Duplicar</button>
                                    <a href="{{ route('page-builder.pages.export', $page) }}" class="pb-dropdown-item" download>&#128229; Exportar JSON</a>
                                    <button class="pb-dropdown-item" onclick="copyHtml({{ $page->id }})">&#128203; Copiar HTML</button>
                                    <button class="pb-dropdown-item" onclick="openImportModal({{ $page->id }})">&#128228; Importar JSON</button>
                                    <hr class="pb-dropdown-divider">
                                    <form action="{{ route('page-builder.pages.destroy', $page) }}" method="POST" class="inline-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="pb-dropdown-item pb-dropdown-danger" onclick="return confirm('Excluir &quot;{{ $page->title }}&quot;?')">&#128465; Excluir</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="pb-card-body">
                            <h3 class="pb-card-title">{{ $page->title }}</h3>
                            <div class="pb-card-meta">
                                <span class="pb-card-slug">/{{ $page->slug }}</span>
                            </div>
                        </div>
                        <div class="pb-card-footer">
                            <span class="pb-card-date" title="{{ $page->updated_at->format('d/m/Y H:i') }}">
                                &#128337; {{ $page->updated_at->diffForHumans() }}
                            </span>
                            <a href="{{ route('page-builder.editor', $page) }}" class="btn btn-sm btn-primary pb-card-edit-btn">
                                &#9998; Editar
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="pages-list" class="pb-pages-list" style="display:none">
                @foreach($pages as $page)
                    <div class="pb-list-row" data-title="{{ strtolower($page->title) }}" data-slug="{{ strtolower($page->slug) }}">
                        <div class="pb-list-info">
                            <span class="badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}">
                                {{ $page->status === 'published' ? 'Publicada' : 'Rascunho' }}
                            </span>
                            <div>
                                <span class="pb-list-title">{{ $page->title }}</span>
                                <span class="pb-list-slug">/{{ $page->slug }}</span>
                            </div>
                        </div>
                        <div class="pb-list-actions">
                            <span class="pb-card-date">{{ $page->updated_at->diffForHumans() }}</span>
                            <a href="{{ route('page-builder.editor', $page) }}" class="btn btn-sm btn-primary">Editar</a>
                            <a href="{{ route('page-builder.render', $page) }}" class="btn btn-sm btn-secondary" target="_blank">Ver</a>
                            <button type="button" class="btn btn-sm btn-info" onclick="duplicatePage({{ $page->id }})" title="Duplicar">&#128203;</button>
                            <a href="{{ route('page-builder.pages.export', $page) }}" class="btn btn-sm btn-secondary" download title="Exportar JSON">&#128229;</a>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="copyHtml({{ $page->id }})" title="Copiar HTML">&#128203;</button>
                            <form action="{{ route('page-builder.pages.destroy', $page) }}" method="POST" class="inline-form">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Excluir &quot;{{ $page->title }}&quot;?')">&#128465;</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="pagination">
                {{ $pages->links() }}
            </div>
        @else
            <div class="pb-empty">
                <div class="pb-empty-visual">
                    <div class="pb-empty-circle">
                        <span class="pb-empty-icon">&#128196;</span>
                    </div>
                </div>
                <h2 class="pb-empty-title">Nenhuma página ainda</h2>
                <p class="pb-empty-text">Crie sua primeira página para começar a construir conteúdo visual com o page builder.</p>
                <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary" style="padding:.6rem 1.5rem;font-size:.95rem">
                    <span>+</span> Criar Primeira Página
                </a>
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

    @push('styles')
    <style>
        .pb-hero {
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 50%, #a78bfa 100%);
            border-radius: 16px; padding: 2rem 2.5rem; margin-bottom: 1.5rem;
            color: #fff; position: relative; overflow: hidden;
        }
        .pb-hero::before {
            content: ''; position: absolute; top: -50%; right: -20%; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,.12) 0%, transparent 70%);
            border-radius: 50%;
        }
        .pb-hero::after {
            content: ''; position: absolute; bottom: -30%; left: -10%; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,.08) 0%, transparent 70%);
            border-radius: 50%;
        }
        .pb-hero-content { display: flex; justify-content: space-between; align-items: center; position: relative; z-index: 1; }
        .pb-hero-title { font-size: 1.8rem; font-weight: 700; letter-spacing: -.5px; margin-bottom: .25rem; }
        .pb-hero-sub { font-size: .95rem; opacity: .85; }
        .pb-hero-btn { background: #fff !important; color: #6366f1 !important; font-weight: 600 !important; padding: .6rem 1.4rem !important; box-shadow: 0 4px 16px rgba(0,0,0,.15) !important; }
        .pb-hero-btn:hover { transform: translateY(-2px) !important; box-shadow: 0 8px 24px rgba(0,0,0,.2) !important; }
        .pb-hero-btn span { font-size: 1.1rem; font-weight: 700; }
        .pb-stats { display: flex; align-items: center; gap: 1.5rem; margin-top: 1.5rem; position: relative; z-index: 1; }
        .pb-stat { display: flex; flex-direction: column; }
        .pb-stat-number { font-size: 1.8rem; font-weight: 700; line-height: 1; }
        .pb-stat-label { font-size: .78rem; opacity: .8; margin-top: .2rem; text-transform: uppercase; letter-spacing: .5px; }
        .pb-stat-divider { width: 1px; height: 36px; background: rgba(255,255,255,.25); }
        .pb-stat-published { color: #bbf7d0; }
        .pb-stat-draft { color: #fde68a; }

        .pb-toolbar { display: flex; align-items: center; gap: .75rem; margin-bottom: 1.25rem; }
        .pb-search { position: relative; flex: 1; max-width: 400px; }
        .pb-search-icon { position: absolute; left: .75rem; top: 50%; transform: translateY(-50%); font-size: .9rem; opacity: .5; }
        .pb-search-input {
            width: 100%; padding: .55rem .75rem .55rem 2.2rem; border: 1px solid #e2e8f0; border-radius: 8px;
            font-size: .875rem; background: #fff; transition: all .2s; color: #1e293b;
        }
        .pb-search-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.12); }
        .pb-search-input::placeholder { color: #94a3b8; }
        .pb-view-toggle { display: flex; gap: 2px; background: #f1f5f9; border-radius: 8px; padding: 2px; }
        .pb-view-btn {
            padding: .4rem .65rem; border: none; border-radius: 6px; cursor: pointer;
            font-size: .9rem; background: transparent; color: #64748b; transition: all .15s;
        }
        .pb-view-btn.active { background: #fff; color: #6366f1; box-shadow: 0 1px 3px rgba(0,0,0,.08); }

        .pb-pages-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem; }
        .pb-page-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
            display: flex; flex-direction: column; transition: all .2s;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .pb-page-card:hover { border-color: #c7d2fe; box-shadow: 0 8px 24px rgba(99,102,241,.1); transform: translateY(-2px); }
        .pb-card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem 0; }
        .pb-card-body { flex: 1; padding: .75rem 1.25rem; }
        .pb-card-title { font-size: 1.05rem; font-weight: 600; color: #0f172a; margin-bottom: .35rem; line-height: 1.4; }
        .pb-card-meta { display: flex; align-items: center; gap: .5rem; }
        .pb-card-slug { font-size: .78rem; color: #94a3b8; font-family: ui-monospace, monospace; }
        .pb-card-footer {
            display: flex; justify-content: space-between; align-items: center;
            padding: .75rem 1.25rem; border-top: 1px solid #f1f5f9; background: #fafbfc;
            border-radius: 0 0 11px 11px;
        }
        .pb-card-date { font-size: .78rem; color: #94a3b8; }
        .pb-card-edit-btn { font-size: .8rem !important; }

        .pb-card-dropdown { position: relative; }
        .pb-card-more {
            background: none; border: none; font-size: 1.3rem; color: #94a3b8;
            cursor: pointer; padding: .1rem .4rem; border-radius: 4px; transition: all .15s;
            line-height: 1;
        }
        .pb-card-more:hover { background: #f1f5f9; color: #475569; }
        .pb-dropdown-menu {
            display: none; position: absolute; top: 100%; right: 0; z-index: 30;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            box-shadow: 0 8px 32px rgba(0,0,0,.12); min-width: 200px; padding: .35rem;
        }
        .pb-dropdown-menu.show { display: block; }
        .pb-dropdown-item {
            display: flex; align-items: center; gap: .5rem; width: 100%;
            padding: .5rem .75rem; border: none; background: none; cursor: pointer;
            font-size: .85rem; color: #475569; border-radius: 6px; text-align: left;
            text-decoration: none; transition: background .1s; font-family: inherit;
        }
        .pb-dropdown-item:hover { background: #f1f5f9; }
        .pb-dropdown-danger { color: #ef4444; }
        .pb-dropdown-danger:hover { background: #fef2f2; }
        .pb-dropdown-divider { border: none; border-top: 1px solid #e2e8f0; margin: .25rem 0; }

        .pb-pages-list { display: flex; flex-direction: column; gap: .5rem; }
        .pb-list-row {
            display: flex; justify-content: space-between; align-items: center;
            background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: .85rem 1.25rem; transition: all .15s;
        }
        .pb-list-row:hover { border-color: #c7d2fe; box-shadow: 0 2px 8px rgba(99,102,241,.08); }
        .pb-list-info { display: flex; align-items: center; gap: .75rem; }
        .pb-list-title { font-weight: 600; font-size: .95rem; color: #0f172a; display: block; }
        .pb-list-slug { font-size: .78rem; color: #94a3b8; font-family: ui-monospace, monospace; }
        .pb-list-actions { display: flex; align-items: center; gap: .4rem; }

        .pb-empty {
            text-align: center; padding: 4rem 2rem; background: #fff;
            border-radius: 16px; border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0,0,0,.04);
        }
        .pb-empty-visual { margin-bottom: 1.5rem; }
        .pb-empty-circle {
            display: inline-flex; align-items: center; justify-content: center;
            width: 100px; height: 100px; border-radius: 50%;
            background: linear-gradient(135deg, #eef2ff, #e0e7ff);
        }
        .pb-empty-icon { font-size: 2.8rem; opacity: .8; }
        .pb-empty-title { font-size: 1.3rem; font-weight: 700; color: #0f172a; margin-bottom: .5rem; }
        .pb-empty-text { color: #64748b; margin-bottom: 1.5rem; max-width: 400px; margin-left: auto; margin-right: auto; line-height: 1.6; }

        @media (max-width: 768px) {
            .pb-hero { padding: 1.5rem; }
            .pb-hero-content { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .pb-hero-title { font-size: 1.4rem; }
            .pb-stats { gap: 1rem; }
            .pb-stat-number { font-size: 1.4rem; }
            .pb-pages-grid { grid-template-columns: 1fr; }
            .pb-toolbar { flex-direction: column; align-items: stretch; }
            .pb-search { max-width: 100%; }
            .pb-list-row { flex-direction: column; align-items: flex-start; gap: .75rem; }
            .pb-list-actions { flex-wrap: wrap; }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        const csrf = '{{ csrf_token() }}';

        function setView(mode) {
            document.querySelectorAll('.pb-view-btn').forEach(b => b.classList.remove('active'));
            document.querySelector(`.pb-view-btn[data-view="${mode}"]`).classList.add('active');
            document.getElementById('pages-grid').style.display = mode === 'grid' ? '' : 'none';
            document.getElementById('pages-list').style.display = mode === 'list' ? '' : 'none';
        }

        function toggleDropdown(btn) {
            const menu = btn.nextElementSibling;
            document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => { if (m !== menu) m.classList.remove('show'); });
            menu.classList.toggle('show');
        }
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.pb-card-dropdown')) {
                document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => m.classList.remove('show'));
            }
        });

        document.getElementById('search-input')?.addEventListener('input', function() {
            const q = this.value.toLowerCase();
            document.querySelectorAll('#pages-grid .pb-page-card, #pages-list .pb-list-row').forEach(card => {
                const title = card.dataset.title || '';
                const slug = card.dataset.slug || '';
                card.style.display = (title.includes(q) || slug.includes(q)) ? '' : 'none';
            });
        });

        function duplicatePage(id) {
            document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => m.classList.remove('show'));
            fetch(`/page-builder/pages/${id}/duplicate`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrf },
            })
            .then(r => r.json())
            .then(() => { showToast('Página duplicada!'); location.reload(); })
            .catch(() => showToast('Falha ao duplicar', true));
        }

        function copyHtml(id) {
            document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => m.classList.remove('show'));
            fetch(`/page-builder/pages/${id}/render?format=inner`)
                .then(r => r.text())
                .then(html => {
                    navigator.clipboard.writeText(html).then(() => {
                        showToast('HTML copiado!');
                    }).catch(() => {
                        const ta = document.createElement('textarea');
                        ta.value = html;
                        document.body.appendChild(ta);
                        ta.select();
                        document.execCommand('copy');
                        ta.remove();
                        showToast('HTML copiado!');
                    });
                })
                .catch(() => showToast('Falha ao copiar HTML', true));
        }

        function showToast(msg, isError) {
            const t = document.createElement('div');
            t.className = 'toast ' + (isError ? 'toast-error' : 'toast-success');
            t.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;animation:slideInToast .3s cubic-bezier(.16,1,.3,1);padding:.75rem 1.25rem;border-radius:10px;font-size:.875rem;font-weight:500;box-shadow:0 8px 32px rgba(0,0,0,.1);display:flex;align-items:center;gap:.5rem';
            if (isError) {
                t.style.background = '#fef2f2'; t.style.color = '#991b1b'; t.style.border = '1px solid #fecaca';
            } else {
                t.style.background = '#dcfce7'; t.style.color = '#166534'; t.style.border = 1px solid #bbf7d0';
            }
            t.textContent = (isError ? '\u274C ' : '\u2705 ') + msg;
            document.body.appendChild(t);
            setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }, 3000);
        }

        let importPageId = null;

        function openImportModal(id) {
            document.querySelectorAll('.pb-dropdown-menu.show').forEach(m => m.classList.remove('show'));
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
                try { data = JSON.parse(e.target.result); } catch {
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
