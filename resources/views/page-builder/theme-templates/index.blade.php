@extends('page-builder.layouts.app')

@section('title', 'Theme Builder')

@section('content')
<div class="container">
    <div class="page-header">
        <div>
            <h1>Theme Builder</h1>
            <p style="color:#64748b;font-size:.875rem;margin-top:.25rem">Gerenciar templates de header, footer e layouts do site</p>
        </div>
        <a href="{{ route('page-builder.themes.create') }}" class="btn btn-primary">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Novo Template
        </a>
    </div>

    @if(session('success'))
        <div class="toast toast-success" id="session-toast">
            <span>{{ session('success') }}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @php
        $typeOrder = ['header', 'footer', 'single', 'archive', '404', 'search_results'];
        $typeLabels = [
            'header' => ['label' => 'Header', 'icon' => '🏰', 'desc' => 'Topo do site (logo, menu, etc.)'],
            'footer' => ['label' => 'Footer', 'icon' => '🏠', 'desc' => 'Rodapé do site (copyright, links)'],
            'single' => ['label' => 'Single', 'icon' => '📖', 'desc' => 'Layout de página única'],
            'archive' => ['label' => 'Archive', 'icon' => '📋', 'desc' => 'Listagem de páginas/posts'],
            '404' => ['label' => '404 Page', 'icon' => '❓', 'desc' => 'Página de erro 404'],
            'search_results' => ['label' => 'Search', 'icon' => '🔍', 'desc' => 'Resultados de busca'],
        ];
    @endphp

    @if($templates->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">🏗️</div>
            <h2>Nenhum template criado</h2>
            <p>Crie templates de header, footer e layouts para personalizar a aparência do site.</p>
            <a href="{{ route('page-builder.themes.create') }}" class="btn btn-primary">Criar Primeiro Template</a>
        </div>
    @else
        @foreach($typeOrder as $type)
            @php $info = $typeLabels[$type]; @endphp
            @if(isset($grouped[$type]) && $grouped[$type]->isNotEmpty())
                <div style="margin-bottom:2rem">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:1rem">
                        <span style="font-size:1.25rem">{{ $info['icon'] }}</span>
                        <h2 style="font-size:1.1rem;font-weight:600;color:#0f172a">{{ $info['label'] }}</h2>
                        <span style="font-size:.75rem;color:#64748b;background:#f1f5f9;padding:.15rem .5rem;border-radius:10px">{{ $grouped[$type]->count() }}</span>
                    </div>
                    <p style="color:#94a3b8;font-size:.82rem;margin-bottom:.75rem">{{ $info['desc'] }}</p>

                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem">
                        @foreach($grouped[$type] as $template)
                            @php
                                $conditionLabels = collect($template->conditions ?? [])->map(function($c) {
                                    $labels = \App\Services\PageBuilder\Theme\ThemeService::conditionOptions();
                                    $type = $c['type'] ?? '';
                                    $label = $labels[$type] ?? $type;
                                    if ($type === 'specific' && !empty($c['value'])) {
                                        $label .= ': ' . $c['value'];
                                    }
                                    return $label;
                                })->filter()->implode(', ');
                                if (empty($template->conditions)) $conditionLabels = 'All pages (no conditions)';
                            @endphp
                            <div style="background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:1.25rem;box-shadow:0 1px 3px rgba(0,0,0,.04);display:flex;flex-direction:column;gap:.65rem">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start">
                                    <div>
                                        <div style="font-weight:600;color:#0f172a;font-size:.95rem">{{ $template->title }}</div>
                                        <div style="display:flex;gap:.35rem;margin-top:.25rem;flex-wrap:wrap">
                                            <span class="badge {{ $template->status === 'published' ? 'badge-published' : 'badge-draft' }}">{{ $template->status }}</span>
                                            <span style="background:#eef2ff;color:#6366f1;padding:.1rem .45rem;border-radius:4px;font-size:.7rem;font-weight:500">{{ $info['label'] }}</span>
                                        </div>
                                    </div>
                                    <div style="display:flex;gap:.25rem">
                                        <a href="{{ route('page-builder.themes.editor', $template) }}" class="btn btn-sm btn-info" title="Edit with Page Builder">✏️</a>
                                        <a href="{{ route('page-builder.themes.conditions', $template) }}" class="btn btn-sm btn-secondary" title="Conditions">🎯</a>
                                        <a href="{{ route('page-builder.themes.render', $template) }}?t={{ time() }}" target="_blank" class="btn btn-sm btn-secondary" title="Preview">👁️</a>
                                        <form action="{{ route('page-builder.themes.destroy', $template) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this theme template?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Delete">🗑️</button>
                                        </form>
                                    </div>
                                </div>
                                <div style="font-size:.78rem;color:#64748b;display:flex;flex-direction:column;gap:.15rem">
                                    <span>Page: <strong>{{ $template->page->title ?? 'N/A' }}</strong></span>
                                    <span>Conditions: <strong>{{ $conditionLabels }}</strong></span>
                                    <span>Updated: {{ $template->updated_at->diffForHumans() }}</span>
                                </div>
                                <div style="display:flex;gap:.35rem;margin-top:.25rem">
                                    @if($template->isDraft())
                                        <form action="{{ route('page-builder.themes.publish', $template) }}" method="POST" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">📢 Publish</button>
                                        </form>
                                    @else
                                        <form action="{{ route('page-builder.themes.unpublish', $template) }}" method="POST" style="display:inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-secondary">⏸️ Unpublish</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div style="margin-bottom:2rem">
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.5rem">
                        <span style="font-size:1.25rem">{{ $info['icon'] }}</span>
                        <h2 style="font-size:1.1rem;font-weight:600;color:#0f172a">{{ $info['label'] }}</h2>
                    </div>
                    <div style="background:#f8fafc;border:1px dashed #e2e8f0;border-radius:10px;padding:1.5rem;text-align:center">
                        <span style="color:#94a3b8;font-size:.85rem">No {{ $info['label'] }} template yet.</span>
                        <a href="{{ route('page-builder.themes.create') }}?type={{ $type }}" style="color:#6366f1;font-size:.85rem;margin-left:.5rem">Create</a>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</div>

@if(session('success'))
<script>
    setTimeout(() => { const t = document.getElementById('session-toast'); if(t) t.remove(); }, 5000);
</script>
@endif
@endsection
