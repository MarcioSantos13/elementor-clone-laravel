@extends('page-builder.layouts.app')

@section('title', 'Projetos')

@section('content')
    <div class="container">
        <div class="pb-hero">
            <div class="pb-hero-orb pb-hero-orb-1"></div>
            <div class="pb-hero-orb pb-hero-orb-2"></div>
            <div class="pb-hero-orb pb-hero-orb-3"></div>
            <div class="pb-hero-content">
                <div>
                    <div class="pb-hero-badge">Project Hub</div>
                    <h1 class="pb-hero-title">Meus Projetos</h1>
                    <p class="pb-hero-sub">Selecione um projeto para gerenciar páginas, temas e popups</p>
                </div>
                <div style="display:flex;gap:.6rem;align-items:center;position:relative;z-index:2">
                    <button onclick="document.getElementById('create-project-modal').style.display='flex'" class="pb-hero-btn-ghost" style="display:flex;align-items:center;gap:.4rem">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Novo Projeto
                    </button>
                </div>
            </div>
            <div class="pb-account">
                <span class="pb-account-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                <div class="pb-account-meta">
                    <span class="pb-account-name">{{ auth()->user()->name }}</span>
                    <span class="pb-account-email">{{ auth()->user()->email }}</span>
                </div>
                <span class="pb-account-role pb-account-role-{{ auth()->user()->role }}">{{ auth()->user()->role }}</span>
            </div>
            <div class="pb-stats">
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $projects->count() }}</span>
                    <span class="pb-stat-label">Projetos</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $projects->sum('pages_count') }}</span>
                    <span class="pb-stat-label">Páginas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $projects->sum('theme_templates_count') }}</span>
                    <span class="pb-stat-label">Temas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $projects->sum('popups_count') }}</span>
                    <span class="pb-stat-label">Popups</span>
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

        @if($projects->count())
            <div class="pb-pages-grid" style="margin-top:1.5rem">
                @foreach($projects as $project)
                    <a href="{{ route('page-builder.projects.dashboard', $project) }}" class="pb-page-card" style="text-decoration:none">
                        <div class="pb-card-preview">
                            <div class="pb-card-preview-icon">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <span class="pb-card-preview-edit">
                                Abrir Projeto
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </span>
                        </div>
                        <div class="pb-card-body">
                            <div class="pb-card-title-row">
                                <h3 class="pb-card-title">{{ $project->name }}</h3>
                                <div style="display:flex;gap:.4rem;align-items:center">
                                    @if($project->is_default)
                                        <span class="badge badge-published">padrão</span>
                                    @endif
                                    @unless($project->is_default)
                                        <button class="pb-card-more" type="button" onclick="event.preventDefault(); event.stopPropagation(); deleteProject({{ $project->id }}, '{{ addslashes($project->name) }}')" title="Excluir projeto">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    @endunless
                                </div>
                            </div>
                            @if($project->description)
                                <span class="pb-card-slug">{{ Str::limit($project->description, 80) }}</span>
                            @else
                                <span class="pb-card-slug">Sem descrição</span>
                            @endif
                        </div>
                        <div class="pb-card-footer">
                            <div class="pb-card-status">
                                <span class="pb-card-date">{{ $project->pages_count }} páginas • {{ $project->theme_templates_count }} temas • {{ $project->popups_count }} popups</span>
                            </div>
                            <span class="pb-card-edit-btn">Abrir</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">&#128193;</div>
                <h2>Nenhum projeto</h2>
                <p>Crie seu primeiro projeto para organizar páginas, temas e popups.</p>
                <button onclick="document.getElementById('create-project-modal').style.display='flex'" class="btn btn-primary" style="margin-top:1rem">Criar primeiro projeto</button>
            </div>
        @endif
    </div>

    <div id="create-project-modal" class="modal-overlay" style="display:none">
        <div class="modal-content" style="max-width:520px">
            <div class="modal-header">
                <h3>Criar novo projeto</h3>
                <button class="modal-close" onclick="document.getElementById('create-project-modal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="project-name">Nome do projeto <span class="required">*</span></label>
                    <input type="text" id="project-name" class="form-control" placeholder="Ex.: Site Institucional">
                </div>
                <div class="form-group">
                    <label for="project-location">Local do projeto <span class="optional">(opcional)</span></label>
                    <input type="text" id="project-location" class="form-control" placeholder="Ex.: D:\Meus Projetos\Site-Institucional">
                    <small style="color:#94a3b8;font-size:.75rem;display:block;margin-top:.35rem">Campo informativo, semelhante ao Unity Hub.</small>
                </div>
                <div class="form-group">
                    <label for="project-description">Descrição <span class="optional">(opcional)</span></label>
                    <textarea id="project-description" class="form-control" rows="3" placeholder="Descreva o objetivo deste projeto"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="document.getElementById('create-project-modal').style.display='none'">Cancelar</button>
                <button class="btn btn-primary" onclick="createProject()">Criar projeto</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF = '{{ csrf_token() }}';

        function createProject() {
            const name = document.getElementById('project-name').value.trim();
            const location = document.getElementById('project-location').value.trim();
            const description = document.getElementById('project-description').value.trim();
            if (!name) { alert('Informe um nome para o projeto'); return; }

            fetch('{{ route('page-builder.projects.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ name, location, description }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.project) { window.location.href = '/page-builder/projects/' + data.project.id + '/dashboard'; }
                else { alert(data.message || 'Erro ao criar projeto'); }
            })
            .catch(() => alert('Erro ao criar projeto'));
        }

        function deleteProject(id, name) {
            if (!confirm('Excluir o projeto "' + name + '"? Os itens serão movidos para o projeto padrão.')) return;
            fetch('/page-builder/projects/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => location.reload())
            .catch(() => alert('Erro ao excluir projeto'));
        }
    </script>
@endsection
