<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Editando: {{ $page->title }} - {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --pb-primary: #6366f1;
    --pb-primary-hover: #4f46e5;
    --pb-primary-light: rgba(99,102,241,.15);
    --pb-bg: #0f0f1a;
    --pb-surface: #1a1a2e;
    --pb-surface2: #242442;
    --pb-surface3: #2e2e52;
    --pb-border: rgba(99,102,241,.15);
    --pb-text: #e2e8f0;
    --pb-text2: #94a3b8;
    --pb-accent: #818cf8;
    --pb-danger: #ef4444;
    --pb-success: #22c55e;
    --pb-warning: #f59e0b;
    --pb-canvas-bg: #f1f5f9;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: var(--pb-bg); color: var(--pb-text); overflow: hidden; height: 100vh; }

.pb-toolbar {
    height: 52px; background: var(--pb-surface); border-bottom: 1px solid var(--pb-border);
    display: flex; align-items: center; padding: 0 1rem; gap: .5rem; flex-shrink: 0;
    backdrop-filter: blur(12px); position: relative; z-index: 100;
}
.pb-toolbar-title { font-weight: 600; font-size: .9rem; display: flex; align-items: center; gap: .5rem; }
.pb-toolbar-title a { color: var(--pb-text2); text-decoration: none; font-size: 1.1rem; transition: color .2s; }
.pb-toolbar-title a:hover { color: var(--pb-text); }
.pb-toolbar-title a.btn-back { font-size: .78rem; color: var(--pb-text); background: var(--pb-surface2); border: 1px solid var(--pb-border); padding: .3rem .65rem; border-radius: 6px; transition: all .2s; }
.pb-toolbar-title a.btn-back:hover { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,.3); }
.pb-toolbar-badge {
    font-size: .65rem; padding: .15rem .55rem; border-radius: 20px; font-weight: 600;
    letter-spacing: .3px; text-transform: uppercase;
}
.pb-toolbar-spacer { flex: 1; }
.pb-toolbar button, .pb-toolbar a.tb-link {
    background: var(--pb-surface2); color: var(--pb-text); border: 1px solid var(--pb-border);
    padding: .4rem .7rem; border-radius: 6px; cursor: pointer; font-size: .78rem; text-decoration: none;
    transition: all .2s; display: inline-flex; align-items: center; gap: .35rem; white-space: nowrap;
}
.pb-toolbar button:hover, .pb-toolbar a.tb-link:hover { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,.3); }
.pb-toolbar button.active, .pb-toolbar a.tb-link.active { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); }
.pb-toolbar button:active { transform: translateY(0); }
.pb-toolbar .tb-divider { width: 1px; height: 22px; background: var(--pb-border); }
.pb-toolbar .btn-save { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); font-weight: 500; }
.pb-toolbar .btn-save:hover { box-shadow: 0 4px 14px rgba(99,102,241,.4); }
.pb-toolbar .btn-publish { background: var(--pb-success); color: #fff; border-color: var(--pb-success); font-weight: 500; }
.pb-toolbar .btn-publish:hover { box-shadow: 0 4px 14px rgba(34,197,94,.4); }

.pb-layout { display: flex; height: calc(100vh - 52px); }

.pb-panel {
    width: 280px; background: var(--pb-surface); display: flex; flex-direction: column;
    border-right: 1px solid var(--pb-border); flex-shrink: 0;
}
.pb-panel-right { border-right: none; border-left: 1px solid var(--pb-border); }

.pb-panel-tabs { display: flex; border-bottom: 1px solid var(--pb-border); background: rgba(0,0,0,.15); }
.pb-panel-tab {
    flex: 1; padding: .65rem .5rem; text-align: center; cursor: pointer; font-size: .78rem;
    color: var(--pb-text2); border-bottom: 2px solid transparent; background: none;
    border-top: none; border-left: none; border-right: none; transition: all .2s; font-weight: 500;
}
.pb-panel-tab.active { color: var(--pb-accent); border-bottom-color: var(--pb-accent); background: rgba(99,102,241,.05); }
.pb-panel-tab:hover { color: var(--pb-text); }
.pb-panel-body { flex: 1; overflow-y: auto; padding: .75rem; }
.pb-panel-body::-webkit-scrollbar { width: 5px; }
.pb-panel-body::-webkit-scrollbar-track { background: transparent; }
.pb-panel-body::-webkit-scrollbar-thumb { background: var(--pb-border); border-radius: 10px; }
.pb-panel-body::-webkit-scrollbar-thumb:hover { background: var(--pb-text2); }

.pb-widget-group { margin-bottom: 1rem; }
.pb-widget-group-title {
    font-size: .65rem; text-transform: uppercase; color: var(--pb-text2);
    margin-bottom: .5rem; font-weight: 700; letter-spacing: .8px;
}
.pb-widget-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.pb-widget-item {
    background: var(--pb-surface2); border: 1px solid var(--pb-border); border-radius: 8px;
    padding: .8rem .4rem; text-align: center; cursor: grab; font-size: .73rem;
    transition: all .2s cubic-bezier(.4,0,.2,1);
}
.pb-widget-item:hover {
    border-color: var(--pb-accent); background: var(--pb-primary-light);
    transform: translateY(-2px); box-shadow: 0 4px 12px rgba(99,102,241,.15);
}
.pb-widget-item:active { cursor: grabbing; transform: scale(.96); }
.pb-widget-item.dragging { opacity: .4; }
.pb-widget-icon { font-size: 1.3rem; margin-bottom: .25rem; display: block; opacity: .9; }
.pb-widget-label { display: block; font-weight: 500; }

.pb-structure-tree { list-style: none; }
.pb-structure-item {
    padding: .45rem .55rem; border-radius: 6px; cursor: pointer; font-size: .78rem;
    display: flex; align-items: center; gap: .4rem; margin-bottom: 2px;
    transition: all .15s;
}
.pb-structure-item:hover { background: var(--pb-surface2); }
.pb-structure-item.active { background: var(--pb-primary); color: #fff; box-shadow: 0 2px 8px rgba(99,102,241,.3); }
.pb-structure-item .si-icon { font-size: .85rem; }
.pb-structure-item .si-type { color: var(--pb-text2); font-size: .65rem; }
.pb-structure-item.active .si-type { color: rgba(255,255,255,.7); }
.pb-structure-children { padding-left: 1.2rem; list-style: none; }

.pb-canvas-wrap {
    flex: 1; overflow: auto; background: var(--pb-canvas-bg);
    display: flex; justify-content: center; padding: 2rem;
    background-image: radial-gradient(circle, rgba(0,0,0,.03) 1px, transparent 1px);
    background-size: 20px 20px;
}
.pb-canvas {
    width: 100%; max-width: 1200px; background: #fff; min-height: 600px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06), 0 6px 24px rgba(0,0,0,.1); border-radius: 8px;
    position: relative; transition: max-width .4s cubic-bezier(.4,0,.2,1);
}
.pb-canvas.is-mobile { max-width: 375px; }
.pb-canvas.is-tablet { max-width: 768px; }
.pb-canvas-dropzone { min-height: 200px; padding: 1rem; }
.pb-drop-cap:first-letter { font-size: 3em; float: left; line-height: 1; margin-right: 10px; }

.pb-el {
    position: relative; padding: .5rem; min-height: 30px; border: 2px solid transparent;
    transition: border-color .2s, box-shadow .2s; border-radius: 4px;
}
.pb-el:hover { border-color: rgba(99,102,241,.25); background: rgba(99,102,241,.02); }
.pb-el.selected { border-color: var(--pb-primary); box-shadow: 0 0 0 1px var(--pb-primary), 0 4px 12px rgba(99,102,241,.12); }
.pb-el.drop-over { border-color: var(--pb-accent); background: var(--pb-primary-light); }
.pb-el.drop-target { border-color: var(--pb-success) !important; background: rgba(34,197,94,.06); }

.pb-el-toolbar {
    display: none; position: absolute; top: -30px; left: 0; z-index: 50;
    background: var(--pb-primary); color: #fff; border-radius: 6px 6px 0 0;
    padding: 3px 8px; font-size: .68rem; gap: 2px; align-items: center;
    box-shadow: 0 -2px 8px rgba(99,102,241,.2);
}
.pb-el.selected > .pb-el-toolbar, .pb-el:hover > .pb-el-toolbar { display: inline-flex; }
.pb-el-name { font-weight: 600; margin-right: .5rem; }
.pb-el-type { opacity: .6; font-size: .6rem; text-transform: uppercase; letter-spacing: .5px; }
.pb-el-action {
    background: none; border: none; color: #fff; cursor: pointer; padding: 2px 6px;
    border-radius: 4px; font-size: 1rem; line-height: 1; transition: background .15s;
}
.pb-el-action:hover { background: rgba(255,255,255,.2); }
.pb-el-content { min-height: 20px; }

.pb-empty-canvas {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 400px; color: #94a3b8; border: 3px dashed #e2e8f0; border-radius: 16px;
    margin: 1rem; padding: 3rem 2rem; text-align: center; transition: all .3s;
    background: rgba(248,250,252,.4);
}
.pb-empty-canvas.drag-over { border-color: var(--pb-accent); background: rgba(99,102,241,.04); }
.pb-empty-canvas .pb-empty-icon { font-size: 3.5rem; margin-bottom: 1rem; opacity: .6; }
.pb-empty-canvas p { font-size: .9rem; line-height: 1.6; }
.pb-empty-canvas p strong { color: #64748b; }

.pb-layout-templates { padding: .5rem; }
.pb-layout-card {
    background: var(--pb-surface2); border: 1px solid var(--pb-border); border-radius: 10px;
    margin-bottom: .75rem; cursor: pointer; transition: all .2s; overflow: hidden;
}
.pb-layout-card:hover { border-color: var(--pb-accent); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,.15); }
.pb-layout-card.active { border-color: var(--pb-success); }
.pb-layout-card-preview {
    height: 90px; background: var(--pb-bg); display: flex; align-items: center;
    justify-content: center; font-size: 1.8rem; color: var(--pb-text2);
}
.pb-layout-card-info { padding: .6rem .75rem; }
.pb-layout-card-info h4 { font-size: .8rem; margin-bottom: .15rem; font-weight: 600; }
.pb-layout-card-info p { font-size: .68rem; color: var(--pb-text2); }
.pb-layout-card .pb-apply-btn {
    display: block; width: 100%; padding: .45rem; background: var(--pb-primary); color: #fff;
    border: none; cursor: pointer; font-size: .73rem; font-weight: 500;
    border-radius: 0 0 9px 9px; transition: background .2s;
}
.pb-layout-card .pb-apply-btn:hover { background: var(--pb-primary-hover); }
.pb-layout-card .pb-apply-btn:disabled { opacity: .5; cursor: not-allowed; }

.pb-settings { padding: 0; display: flex; flex-direction: column; height: 100%; }
.pb-settings-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    flex: 1; color: var(--pb-text2); text-align: center; padding: 2rem;
}
.pb-settings-empty .pse-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: .35; }
.pb-settings-header {
    padding: .75rem .85rem; border-bottom: 1px solid var(--pb-border);
    display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;
    background: rgba(0,0,0,.1);
}
.pb-settings-header h3 { font-size: .82rem; font-weight: 600; }
.pb-settings-header .pb-sh-type { font-size: .65rem; color: var(--pb-text2); text-transform: uppercase; letter-spacing: .5px; }
.pb-settings-body { padding: .75rem; overflow-y: auto; flex: 1; }
.pb-settings-body::-webkit-scrollbar { width: 5px; }
.pb-settings-body::-webkit-scrollbar-track { background: transparent; }
.pb-settings-body::-webkit-scrollbar-thumb { background: var(--pb-border); border-radius: 10px; }
.pb-settings-section { margin-bottom: 1.25rem; }
.pb-settings-section-title {
    font-size: .65rem; text-transform: uppercase; color: var(--pb-text2);
    font-weight: 700; letter-spacing: .8px; margin-bottom: .6rem; padding-bottom: .35rem;
    border-bottom: 1px solid var(--pb-border);
}
.pb-control { margin-bottom: .7rem; }
.pb-control label { display: block; font-size: .75rem; margin-bottom: .3rem; color: var(--pb-text2); font-weight: 500; }
.pb-control input, .pb-control select, .pb-control textarea {
    width: 100%; padding: .45rem .65rem; background: var(--pb-surface2); border: 1px solid var(--pb-border);
    border-radius: 6px; color: var(--pb-text); font-size: .8rem; transition: all .2s;
}
.pb-control input:focus, .pb-control select:focus, .pb-control textarea:focus {
    outline: none; border-color: var(--pb-accent); box-shadow: 0 0 0 3px var(--pb-primary-light);
}
.pb-control select { cursor: pointer; appearance: auto; }
.pb-control input[type="color"] { padding: 2px; height: 36px; cursor: pointer; border-radius: 6px; }
.pb-control input[type="number"] { width: 100%; }
.pb-control textarea { resize: vertical; min-height: 65px; font-family: inherit; }

.pb-toast {
    position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%) translateY(20px);
    background: var(--pb-surface); border: 1px solid var(--pb-border); backdrop-filter: blur(12px);
    padding: .65rem 1.2rem; border-radius: 10px; font-size: .8rem;
    box-shadow: 0 8px 32px rgba(0,0,0,.4); z-index: 9999;
    display: flex; gap: .75rem; align-items: center;
    animation: toastIn .3s cubic-bezier(.4,0,.2,1) forwards;
}
.pb-toast.pb-toast-out { animation: toastOut .25s cubic-bezier(.4,0,.2,1) forwards; }
.pb-toast button { background: var(--pb-primary); color: #fff; border: none; padding: .3rem .8rem; border-radius: 6px; cursor: pointer; font-size: .73rem; font-weight: 500; }
@keyframes toastIn { to { transform: translateX(-50%) translateY(0); opacity: 1; } }
@keyframes toastOut { to { transform: translateX(-50%) translateY(20px); opacity: 0; } }

.saving-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.4);
    display: flex; align-items: center; justify-content: center; z-index: 99999;
    backdrop-filter: blur(4px); animation: fadeIn .2s;
}
.saving-overlay .saving-card {
    background: var(--pb-surface); border: 1px solid var(--pb-border);
    border-radius: 16px; padding: 2rem 2.5rem; display: flex; flex-direction: column;
    align-items: center; gap: .75rem; box-shadow: 0 16px 48px rgba(0,0,0,.3);
}
.saving-overlay .spinner { width: 36px; height: 36px; border: 3px solid var(--pb-border); border-top-color: var(--pb-accent); border-radius: 50%; animation: spin .6s linear infinite; }
.saving-overlay .saving-text { font-size: .85rem; color: var(--pb-text2); }
@keyframes spin { to { transform: rotate(360deg); } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.pb-drag-ghost {
    position: fixed; pointer-events: none; z-index: 99999;
    background: var(--pb-primary); color: #fff; padding: .4rem .8rem;
    border-radius: 6px; font-size: .8rem; font-weight: 500;
    box-shadow: 0 8px 24px rgba(99,102,241,.4); transform: translate(-50%, -50%);
}
</style>

<div class="pb-toolbar">
    <span class="pb-toolbar-title"><a href="{{ route('page-builder.pages.index') }}" class="btn-back">&#8592; Voltar</a> {{ $page->title }}</span>
    <span class="pb-toolbar-badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}" style="background:{{ $page->status === 'published' ? 'rgba(34,197,94,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $page->status === 'published' ? '#22c55e' : '#f59e0b' }}">{{ $page->status }}</span>
    <div class="pb-toolbar-spacer"></div>
    <button id="pb-undo" onclick="editor.undo()" title="Desfazer (Ctrl+Z)">&#8630; <span style="font-size:.65rem;opacity:.6">Desfazer</span></button>
    <button id="pb-redo" onclick="editor.redo()" title="Refazer (Ctrl+Shift+Z)">&#8631; <span style="font-size:.65rem;opacity:.6">Refazer</span></button>
    <span class="tb-divider"></span>
    <button class="active" data-mode="desktop" onclick="editor.setResponsive('desktop')" title="Desktop">&#128421;</button>
    <button data-mode="tablet" onclick="editor.setResponsive('tablet')" title="Tablet">&#128241;</button>
    <button data-mode="mobile" onclick="editor.setResponsive('mobile')" title="Mobile">&#128241;</button>
    <span class="tb-divider"></span>
    <button onclick="editor.showPageSettings()" title="Configurações da Página" id="btn-page-settings">&#9881; <span style="font-size:.65rem;opacity:.6">Página</span></button>
    <a href="{{ route('page-builder.render', $page) }}?t={{ time() }}" target="_blank" class="tb-link">&#128065; <span style="font-size:.65rem;opacity:.6">Visualizar</span></a>
    <span class="tb-divider"></span>
    <button onclick="editor.exportPage()" title="Exportar como JSON">&#128229;</button>
    <button onclick="editor.copyHtml()" title="Copiar HTML da página">&#128203;</button>
    <span class="tb-divider"></span>
    <button onclick="editor.save()" class="btn-save">&#128190; Salvar</button>
    <button onclick="editor.publish()" class="btn-publish">&#128752; Publicar</button>
</div>

<div class="pb-layout">
    <div class="pb-panel">
        <div class="pb-panel-tabs">
            <button class="pb-panel-tab active" data-tab="widgets" onclick="editor.switchTab('widgets')">&#128161; Widgets</button>
            <button class="pb-panel-tab" data-tab="structure" onclick="editor.switchTab('structure')">&#9776; Estrutura</button>
            <button class="pb-panel-tab" data-tab="layouts" onclick="editor.switchTab('layouts')">&#128196; Layouts</button>
        </div>
        <div class="pb-panel-body" id="panel-widgets">
            <div class="pb-widget-group">
                <div class="pb-widget-group-title">Layout</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="section"><span class="pb-widget-icon">&#9638;</span><span class="pb-widget-label">Seção</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="column"><span class="pb-widget-icon">&#9646;</span><span class="pb-widget-label">Coluna</span></div>
                </div>
            </div>
            <div class="pb-widget-group">
                <div class="pb-widget-group-title">Básicos</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="heading"><span class="pb-widget-icon">H</span><span class="pb-widget-label">Título</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="text"><span class="pb-widget-icon">T</span><span class="pb-widget-label">Texto</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="image"><span class="pb-widget-icon">&#128247;</span><span class="pb-widget-label">Imagem</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="button"><span class="pb-widget-icon">&#128206;</span><span class="pb-widget-label">Botão</span></div>
                </div>
            </div>
        </div>
        <div class="pb-panel-body" id="panel-structure" style="display:none">
            <ul class="pb-structure-tree" id="structure-tree"></ul>
        </div>
        <div class="pb-panel-body" id="panel-layouts" style="display:none">
            <div class="pb-layout-templates" id="layout-templates"></div>
        </div>
    </div>

    <div class="pb-canvas-wrap" id="canvas-wrap">
        <div class="pb-canvas" id="canvas">
            <div class="pb-canvas-dropzone" id="canvas-dropzone">
                <div class="pb-empty-canvas" id="empty-canvas">
                    <div class="pb-empty-icon">&#128161;</div>
                    <p><strong>Arraste widgets do painel esquerdo</strong><br>para começar a construir sua página</p>
                </div>
            </div>
        </div>
    </div>

    <div class="pb-panel pb-panel-right">
        <div class="pb-settings" id="settings-panel">
            <div class="pb-settings-empty" id="settings-empty">
                <div class="pse-icon">&#9881;</div>
                <p>Selecione um elemento na tela<br>para editar suas configurações</p>
            </div>
            <div id="settings-form" style="display:none;height:100%;flex-direction:column">
                <div class="pb-settings-header">
                    <div>
                        <h3 id="settings-title">Element</h3>
                        <span class="pb-sh-type" id="settings-type">type</span>
                    </div>
                    <button onclick="editor.deleteSelected()" style="background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1.1rem" title="Excluir elemento">&#128465;</button>
                </div>
                <div class="pb-settings-body" id="settings-body"></div>
            </div>
            <div id="page-settings-form" style="display:none;height:100%;flex-direction:column">
                <div class="pb-settings-header">
                    <div>
                        <h3>Layout da Página</h3>
                        <span class="pb-sh-type">Configurações da página</span>
                    </div>
                    <button onclick="editor.hidePageSettings()" style="background:none;border:none;color:var(--pb-text2);cursor:pointer;font-size:1.1rem" title="Fechar">&#10005;</button>
                </div>
                <div class="pb-settings-body" id="page-settings-body"></div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/page-builder-editor.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => editor.init({{ $page->id }}, '{{ csrf_token() }}'));
document.addEventListener('click', (e) => {
    if (!e.target.closest('.pb-el') && !e.target.closest('.pb-structure-item') && !e.target.closest('.pb-settings') && !e.target.closest('.pb-toolbar')) {
        document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
        editor.selectedId = null;
        document.getElementById('settings-empty').style.display = '';
        document.getElementById('settings-form').style.display = 'none';
    }
});
</script>
</body>
</html>
