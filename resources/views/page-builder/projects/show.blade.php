@extends("page-builder.layouts.app")

@section("title", $project->name . " - Projeto")

@section("content")
    <div class="container">
        <div class="pb-hero">
            <div class="pb-hero-orb pb-hero-orb-1"></div>
            <div class="pb-hero-orb pb-hero-orb-2"></div>
            <div class="pb-hero-orb pb-hero-orb-3"></div>
            <div class="pb-hero-content">
                <div>
                    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.6rem">
                        <a href="{{ route(\"page-builder.projects.index\") }}" class="pb-hero-btn-ghost" style="padding:.4rem .6rem">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
                            Voltar ao Hub
                        </a>
                        <span style="color:rgba(255,255,255,.6);font-size:.8rem">Projetos / {{ $project->name }}</span>
                    </div>
                    <h1 class="pb-hero-title">{{ $project->name }}</h1>
                    <p class="pb-hero-sub">{{ $project->description ?: "Gerencie páginas, temas e popups deste projeto" }}</p>
                </div>
                <div style="display:flex;gap:.6rem;align-items:center;position:relative;z-index:2">
                    <div class="pb-card-dropdown">
                        <button class="pb-hero-btn-ghost" style="display:flex;align-items:center;gap:.4rem">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            Novo
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="pb-dropdown-menu" style="right:0;left:auto">
                            <a href="{{ route(\"page-builder.pages.create\") }}?project={{ $project->id }}" class="pb-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                Nova Página
                            </a>
                            <a href="{{ route(\"page-builder.themes.create\") }}?project={{ $project->id }}" class="pb-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                Novo Tema
                            </a>
                            <a href="{{ route(\"page-builder.popups.create\") }}?project={{ $project->id }}" class="pb-dropdown-item">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>
                                Novo Popup
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="pb-stats">
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $project->pages_count ?? $pages->count() }}</span>
                    <span class="pb-stat-label">Páginas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $project->theme_templates_count ?? $themes->count() }}</span>
                    <span class="pb-stat-label">Temas</span>
                </div>
                <div class="pb-stat-divider"></div>
                <div class="pb-stat">
                    <span class="pb-stat-number">{{ $project->popups_count ?? $popups->count() }}</span>
                    <span class="pb-stat-label">Popups</span>
                </div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:1rem;margin:1.5rem 0">
            <a href="{{ route(\"page-builder.pages.index\") }}?project={{ $project->id }}" class="pb-page-card" style="text-decoration:none">
                <div class="pb-card-body">
                    <div class="pb-card-title-row">
                        <h3 class="pb-card-title">Páginas</h3>
                        <span class="badge badge-published">{{ $project->pages_count ?? $pages->count() }}</span>
                    </div>
                    <span class="pb-card-slug">Criar, editar e publicar páginas</span>
                </div>
                <div class="pb-card-footer">
                    <span class="pb-card-edit-btn">Gerenciar <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
                </div>
            </a>
            <a href="{{ route(\"page-builder.themes.index\") }}?project={{ $project->id }}" class="pb-page-card" style="text-decoration:none">
                <div class="pb-card-body">
                    <div class="pb-card-title-row">
                        <h3 class="pb-card-title">Temas</h3>
                        <span class="badge badge-published">{{ $project->theme_templates_count ?? $themes->count() }}</span>
                    </div>
                    <span class="pb-card-slug">Header, Footer, Single, Archive</span>
                </div>
                <div class="pb-card-footer">
                    <span class="pb-card-edit-btn">Gerenciar <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
                </div>
            </a>
            <a href="{{ route(\"page-builder.popups.index\") }}?project={{ $project->id }}" class="pb-page-card" style="text-decoration:none">
                <div class="pb-card-body">
                    <div class="pb-card-title-row">
                        <h3 class="pb-card-title">Popups</h3>
                        <span class="badge badge-published">{{ $project->popups_count ?? $popups->count() }}</span>
                    </div>
                    <span class="pb-card-slug">Gatilhos, condições e exibição</span>
                </div>
                <div class="pb-card-footer">
                    <span class="pb-card-edit-btn">Gerenciar <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
                </div>
            </a>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin:1.2rem 0">
            <h2 style="font-size:1.1rem;font-weight:600">Recentes</h2>
            <a href="{{ route(\"page-builder.pages.index\") }}?project={{ $project->id }}" style="color:var(--pb-accent);text-decoration:none;font-size:.85rem">Ver tudo</a>
        </div>

        @if($pages->count())
            <div class="pb-pages-grid">
                @foreach($pages as $page)
                    <div class="pb-page-card">
                        <div class="pb-card-preview">
                            <div class="pb-card-preview-icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            </div>
                            <a href="{{ route(\"page-builder.editor\", $page) }}" class="pb-card-preview-edit">Abrir no Editor</a>
                        </div>
                        <div class="pb-card-body">
                            <div class="pb-card-title-row">
                                <h3 class="pb-card-title">{{ $page->title }}</h3>
                                <span class="badge badge-{{ $page->status === "published" ? "published" : "draft" }}">{{ $page->status === "published" ? "Publicada" : "Rascunho" }}</span>
                            </div>
                            <span class="pb-card-slug">/{{ $page->slug }}</span>
                        </div>
                        <div class="pb-card-footer">
                            <span class="pb-card-date">{{ $page->updated_at->diffForHumans() }}</span>
                            <a href="{{ route(\"page-builder.editor\", $page) }}" class="pb-card-edit-btn">Editar</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="empty-state">
                <div class="empty-icon">&#128196;</div>
                <h2>Nenhuma página ainda</h2>
                <p>Comece criando sua primeira página neste projeto.</p>
                <a href="{{ route(\"page-builder.pages.create\") }}?project={{ $project->id }}" class="btn btn-primary" style="margin-top:1rem">Criar primeira página</a>
            </div>
        @endif
    </div>
@endsection
