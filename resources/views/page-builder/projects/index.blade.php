@extends('page-builder.layouts.app')

@section('title', 'Projetos')

@section('content')
    <div class="container">
        <div class="page-header">
            <div>
                <h1>Projetos</h1>
                <p style="color:#64748b;font-size:.9rem;margin-top:.25rem">Organize páginas, temas e popups em projetos</p>
            </div>
            <button onclick="document.getElementById('create-project-modal').style.display='flex'" class="btn btn-primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Novo Projeto
            </button>
        </div>

        @if(session('success'))
            <div class="toast toast-success" id="success-toast">
                <span>&#10003;</span>
                <span>{{ session('success') }}</span>
                <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        @if($projects->count())
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Projeto</th>
                            <th>Páginas</th>
                            <th>Temas</th>
                            <th>Popups</th>
                            <th class="th-actions">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr>
                                <td>
                                    <div class="td-title">
                                        {{ $project->name }}
                                        @if($project->is_default)
                                            <span class="badge badge-published" style="margin-left:.4rem">padrão</span>
                                        @endif
                                    </div>
                                    @if($project->description)
                                        <div style="color:#94a3b8;font-size:.8rem">{{ $project->description }}</div>
                                    @endif
                                </td>
                                <td>{{ $project->pages_count }}</td>
                                <td>{{ $project->theme_templates_count }}</td>
                                <td>{{ $project->popups_count }}</td>
                                <td class="td-actions">
                                    <div class="actions">
                                        <a href="{{ route('page-builder.pages.index', ['project' => $project->id]) }}" class="btn btn-secondary btn-sm">Ver páginas</a>
                                        @unless($project->is_default)
                                            <button class="btn btn-danger btn-sm" onclick="deleteProject({{ $project->id }}, '{{ addslashes($project->name) }}')">Excluir</button>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">&#128193;</div>
                <h2>Nenhum projeto</h2>
                <p>Crie seu primeiro projeto para organizar o conteúdo.</p>
            </div>
        @endif
    </div>

    <div id="create-project-modal" class="modal-overlay" style="display:none">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Novo Projeto</h3>
                <button class="modal-close" onclick="document.getElementById('create-project-modal').style.display='none'">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="project-name">Nome <span class="required">*</span></label>
                    <input type="text" id="project-name" class="form-control" placeholder="Meu projeto">
                </div>
                <div class="form-group">
                    <label for="project-description">Descrição <span class="optional">(opcional)</span></label>
                    <textarea id="project-description" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="document.getElementById('create-project-modal').style.display='none'">Cancelar</button>
                <button class="btn btn-primary" onclick="createProject()">Criar</button>
            </div>
        </div>
    </div>

    <script>
        const CSRF = '{{ csrf_token() }}';

        function createProject() {
            const name = document.getElementById('project-name').value.trim();
            const description = document.getElementById('project-description').value.trim();
            if (!name) { alert('Informe um nome'); return; }

            fetch('{{ route('page-builder.projects.store') }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ name, description }),
            })
            .then(r => r.json())
            .then(data => {
                if (data.project) { location.reload(); }
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
