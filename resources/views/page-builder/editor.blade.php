<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Editing: {{ $page->title }} - {{ config('app.name') }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
:root {
    --pb-primary: #007bff;
    --pb-primary-hover: #0056b3;
    --pb-bg: #1e1e2e;
    --pb-surface: #2a2a3e;
    --pb-surface2: #32324a;
    --pb-border: #3a3a52;
    --pb-text: #cdd6f4;
    --pb-text2: #9399b2;
    --pb-accent: #89b4fa;
    --pb-danger: #f38ba8;
    --pb-success: #a6e3a1;
    --pb-warning: #f9e2af;
    --pb-canvas-bg: #e8e8e8;
}

* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Inter', system-ui, -apple-system, sans-serif; background: var(--pb-bg); color: var(--pb-text); overflow: hidden; height: 100vh; }

.pb-toolbar {
    height: 48px; background: var(--pb-surface); border-bottom: 1px solid var(--pb-border);
    display: flex; align-items: center; padding: 0 1rem; gap: .75rem; flex-shrink: 0;
}
.pb-toolbar-title { font-weight: 600; font-size: .9rem; }
.pb-toolbar-badge { font-size: .7rem; padding: .15rem .5rem; border-radius: 10px; font-weight: 500; }
.pb-toolbar-spacer { flex: 1; }
.pb-toolbar button, .pb-toolbar a {
    background: var(--pb-surface2); color: var(--pb-text); border: 1px solid var(--pb-border);
    padding: .35rem .75rem; border-radius: 4px; cursor: pointer; font-size: .8rem; text-decoration: none;
}
.pb-toolbar button:hover, .pb-toolbar a:hover { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); }
.pb-toolbar button.active, .pb-toolbar a.active { background: var(--pb-primary); color: #fff; border-color: var(--pb-primary); }

.pb-layout { display: flex; height: calc(100vh - 48px); }

.pb-panel {
    width: 280px; background: var(--pb-surface); display: flex; flex-direction: column;
    border-right: 1px solid var(--pb-border); flex-shrink: 0;
}
.pb-panel-right { border-right: none; border-left: 1px solid var(--pb-border); }

.pb-panel-tabs { display: flex; border-bottom: 1px solid var(--pb-border); }
.pb-panel-tab {
    flex: 1; padding: .6rem; text-align: center; cursor: pointer; font-size: .8rem;
    color: var(--pb-text2); border-bottom: 2px solid transparent; background: none; border-top: none; border-left: none; border-right: none;
}
.pb-panel-tab.active { color: var(--pb-accent); border-bottom-color: var(--pb-accent); }
.pb-panel-tab:hover { color: var(--pb-text); }
.pb-panel-body { flex: 1; overflow-y: auto; padding: .75rem; }
.pb-panel-body::-webkit-scrollbar { width: 6px; }
.pb-panel-body::-webkit-scrollbar-thumb { background: var(--pb-border); border-radius: 3px; }

.pb-widget-group { margin-bottom: 1rem; }
.pb-widget-group-title { font-size: .7rem; text-transform: uppercase; color: var(--pb-text2); margin-bottom: .5rem; font-weight: 600; letter-spacing: .5px; }
.pb-widget-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; }
.pb-widget-item {
    background: var(--pb-surface2); border: 1px solid var(--pb-border); border-radius: 6px;
    padding: .75rem .5rem; text-align: center; cursor: grab; font-size: .75rem; transition: all .15s;
}
.pb-widget-item:hover { border-color: var(--pb-accent); background: rgba(137,180,250,.1); transform: translateY(-1px); }
.pb-widget-item:active { cursor: grabbing; }
.pb-widget-icon { font-size: 1.3rem; margin-bottom: .25rem; display: block; }
.pb-widget-label { display: block; }

.pb-structure-tree { list-style: none; }
.pb-structure-item {
    padding: .4rem .5rem; border-radius: 4px; cursor: pointer; font-size: .8rem;
    display: flex; align-items: center; gap: .4rem; margin-bottom: 2px;
}
.pb-structure-item:hover { background: var(--pb-surface2); }
.pb-structure-item.active { background: var(--pb-primary); color: #fff; }
.pb-structure-item .si-icon { font-size: .9rem; }
.pb-structure-item .si-type { color: var(--pb-text2); font-size: .7rem; }
.pb-structure-item.active .si-type { color: rgba(255,255,255,.7); }
.pb-structure-children { padding-left: 1.2rem; list-style: none; }

.pb-canvas-wrap {
    flex: 1; overflow: auto; background: var(--pb-canvas-bg);
    display: flex; justify-content: center; padding: 2rem;
}
.pb-canvas {
    width: 100%; max-width: 1200px; background: #fff; min-height: 600px;
    box-shadow: 0 2px 20px rgba(0,0,0,.12); border-radius: 4px; position: relative;
    transition: max-width .3s;
}
.pb-canvas.is-mobile { max-width: 375px; }
.pb-canvas.is-tablet { max-width: 768px; }
.pb-canvas-dropzone { min-height: 200px; padding: 1rem; }

.pb-el {
    position: relative; padding: .5rem; min-height: 30px; border: 2px solid transparent;
    transition: border-color .15s;
}
.pb-el:hover { border-color: rgba(0,123,255,.3); }
.pb-el.selected { border-color: var(--pb-primary); box-shadow: 0 0 0 1px var(--pb-primary); }
.pb-el.drop-target { border-color: var(--pb-success); background: rgba(166,227,161,.08); }

.pb-el-toolbar {
    display: none; position: absolute; top: -28px; left: -1px; z-index: 50;
    background: var(--pb-primary); color: #fff; border-radius: 4px 4px 0 0;
    padding: 2px 6px; font-size: .7rem; gap: 4px; align-items: center;
}
.pb-el.selected > .pb-el-toolbar, .pb-el:hover > .pb-el-toolbar { display: flex; }
.pb-el-name { font-weight: 500; margin-right: .5rem; }
.pb-el-type { opacity: .7; }
.pb-el-action {
    background: none; border: none; color: #fff; cursor: pointer; padding: 2px 4px;
    border-radius: 2px; font-size: .75rem; line-height: 1;
}
.pb-el-action:hover { background: rgba(255,255,255,.2); }
.pb-el-content { min-height: 20px; }

.pb-empty-canvas {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    min-height: 400px; color: #999; border: 3px dashed #ddd; border-radius: 12px;
    margin: 1rem; padding: 2rem; text-align: center;
}
.pb-empty-canvas .pb-empty-icon { font-size: 3rem; margin-bottom: 1rem; }
.pb-empty-canvas p { font-size: .9rem; }

.pb-layout-templates { padding: .5rem; }
.pb-layout-card {
    background: var(--pb-surface2); border: 2px solid var(--pb-border); border-radius: 8px;
    padding: .1rem; margin-bottom: .75rem; cursor: pointer; transition: all .15s; overflow: hidden;
}
.pb-layout-card:hover { border-color: var(--pb-accent); }
.pb-layout-card.active { border-color: var(--pb-success); }
.pb-layout-card-preview {
    height: 100px; background: var(--pb-bg); border-radius: 6px 6px 0 0;
    display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--pb-text2);
}
.pb-layout-card-info { padding: .6rem; }
.pb-layout-card-info h4 { font-size: .8rem; margin-bottom: .2rem; }
.pb-layout-card-info p { font-size: .7rem; color: var(--pb-text2); }
.pb-layout-card .pb-apply-btn {
    display: block; width: 100%; padding: .4rem; background: var(--pb-primary); color: #fff;
    border: none; cursor: pointer; font-size: .75rem; border-radius: 0 0 6px 6px;
}
.pb-layout-card .pb-apply-btn:hover { background: var(--pb-primary-hover); }
.pb-layout-card .pb-apply-btn:disabled { opacity: .5; cursor: wait; }

.pb-settings { padding: 0; }
.pb-settings-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%; color: var(--pb-text2); text-align: center; padding: 2rem;
}
.pb-settings-empty .pse-icon { font-size: 2.5rem; margin-bottom: 1rem; opacity: .5; }
.pb-settings-header {
    padding: .75rem; border-bottom: 1px solid var(--pb-border);
    display: flex; justify-content: space-between; align-items: center;
}
.pb-settings-header h3 { font-size: .85rem; }
.pb-settings-header .pb-sh-type { font-size: .7rem; color: var(--pb-text2); }
.pb-settings-body { padding: .75rem; overflow-y: auto; flex: 1; }
.pb-settings-section { margin-bottom: 1.25rem; }
.pb-settings-section-title {
    font-size: .7rem; text-transform: uppercase; color: var(--pb-text2);
    font-weight: 600; letter-spacing: .5px; margin-bottom: .6rem; padding-bottom: .3rem;
    border-bottom: 1px solid var(--pb-border);
}
.pb-control { margin-bottom: .75rem; }
.pb-control label { display: block; font-size: .78rem; margin-bottom: .25rem; color: var(--pb-text); }
.pb-control input, .pb-control select, .pb-control textarea {
    width: 100%; padding: .45rem .6rem; background: var(--pb-surface2); border: 1px solid var(--pb-border);
    border-radius: 4px; color: var(--pb-text); font-size: .8rem;
}
.pb-control input:focus, .pb-control select:focus, .pb-control textarea:focus {
    outline: none; border-color: var(--pb-accent); box-shadow: 0 0 0 2px rgba(137,180,250,.2);
}
.pb-control select { cursor: pointer; }
.pb-control input[type="color"] { padding: 2px; height: 36px; cursor: pointer; }
.pb-control input[type="number"] { width: 100%; }
.pb-control textarea { resize: vertical; min-height: 60px; font-family: inherit; }

.pb-undo-toast {
    position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
    background: var(--pb-surface); border: 1px solid var(--pb-border);
    padding: .6rem 1.2rem; border-radius: 8px; font-size: .8rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.3); z-index: 9999;
    display: flex; gap: 1rem; align-items: center;
}
.pb-undo-toast button { background: var(--pb-primary); color: #fff; border: none; padding: .3rem .8rem; border-radius: 4px; cursor: pointer; font-size: .75rem; }

.saving-overlay {
    position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.3);
    display: flex; align-items: center; justify-content: center; z-index: 99999;
}
.saving-overlay .spinner { width: 40px; height: 40px; border: 4px solid var(--pb-border); border-top-color: var(--pb-accent); border-radius: 50%; animation: spin .6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<div class="pb-toolbar">
    <span class="pb-toolbar-title"><a href="{{ route('page-builder.pages.index') }}" style="color:var(--pb-text);text-decoration:none">&larr;</a> {{ $page->title }}</span>
    <span class="pb-toolbar-badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}">{{ $page->status }}</span>
    <div class="pb-toolbar-spacer"></div>
    <button id="pb-undo" onclick="editor.undo()" title="Undo (Ctrl+Z)">&#8630; Undo</button>
    <button id="pb-redo" onclick="editor.redo()" title="Redo (Ctrl+Shift+Z)">&#8631; Redo</button>
    <div style="width:1px;height:20px;background:var(--pb-border)"></div>
    <button class="active" data-mode="desktop" onclick="editor.setResponsive('desktop')" title="Desktop">&#128421;</button>
    <button data-mode="tablet" onclick="editor.setResponsive('tablet')" title="Tablet">&#128241;</button>
    <button data-mode="mobile" onclick="editor.setResponsive('mobile')" title="Mobile">&#128241;</button>
    <div style="width:1px;height:20px;background:var(--pb-border)"></div>
    <button onclick="editor.showPageSettings()" title="Page Settings" id="btn-page-settings">&#9881; Page</button>
    <a href="{{ route('page-builder.render', $page) }}" target="_blank">&#128065; Preview</a>
    <button onclick="editor.save()" style="background:var(--pb-primary);color:#fff;border-color:var(--pb-primary)">&#128190; Save</button>
    <button onclick="editor.publish()" style="background:var(--pb-success);color:#1e1e2e;border-color:var(--pb-success)">&#128752; Publish</button>
</div>

<div class="pb-layout">
    <div class="pb-panel">
        <div class="pb-panel-tabs">
            <button class="pb-panel-tab active" data-tab="widgets" onclick="editor.switchTab('widgets')">&#9733; Widgets</button>
            <button class="pb-panel-tab" data-tab="structure" onclick="editor.switchTab('structure')">&#9776; Structure</button>
            <button class="pb-panel-tab" data-tab="layouts" onclick="editor.switchTab('layouts')">&#128196; Layouts</button>
        </div>
        <div class="pb-panel-body" id="panel-widgets">
            <div class="pb-widget-group">
                <div class="pb-widget-group-title">Layout</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="section"><span class="pb-widget-icon">&#9638;</span><span class="pb-widget-label">Section</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="column"><span class="pb-widget-icon">&#9646;</span><span class="pb-widget-label">Column</span></div>
                </div>
            </div>
            <div class="pb-widget-group">
                <div class="pb-widget-group-title">Basic</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="heading"><span class="pb-widget-icon">&#72;</span><span class="pb-widget-label">Heading</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="text"><span class="pb-widget-icon">&#84;</span><span class="pb-widget-label">Text</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="image"><span class="pb-widget-icon">&#128247;</span><span class="pb-widget-label">Image</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="button"><span class="pb-widget-icon">&#128206;</span><span class="pb-widget-label">Button</span></div>
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
                    <p><strong>Drag widgets from the left panel</strong><br>to start building your page</p>
                </div>
            </div>
        </div>
    </div>

    <div class="pb-panel pb-panel-right">
        <div class="pb-settings" id="settings-panel">
            <div class="pb-settings-empty" id="settings-empty">
                <div class="pse-icon">&#9881;</div>
                <p>Select an element on the canvas<br>to edit its settings</p>
            </div>
            <div id="settings-form" style="display:none;height:100%;flex-direction:column">
                <div class="pb-settings-header">
                    <div>
                        <h3 id="settings-title">Element</h3>
                        <span class="pb-sh-type" id="settings-type">type</span>
                    </div>
                    <button onclick="editor.deleteSelected()" style="background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1.1rem" title="Delete element">&#128465;</button>
                </div>
                <div class="pb-settings-body" id="settings-body"></div>
            </div>
            <div id="page-settings-form" style="display:none;height:100%;flex-direction:column">
                <div class="pb-settings-header">
                    <div>
                        <h3>Page Layout</h3>
                        <span class="pb-sh-type">Page settings</span>
                    </div>
                    <button onclick="editor.hidePageSettings()" style="background:none;border:none;color:var(--pb-text2);cursor:pointer;font-size:1.1rem" title="Close">&#10005;</button>
                </div>
                <div class="pb-settings-body" id="page-settings-body"></div>
            </div>
        </div>
    </div>
</div>

<script>
const editor = {
    pageId: {{ $page->id }},
    selectedId: null,
    history: [],
    historyIndex: -1,
    responsiveMode: 'desktop',
    csrf: '{{ csrf_token() }}',
    saving: false,
    dirty: false,

    init() {
        this.loadElements();
        this.loadPageData();
        this.bindDragDrop();
        this.bindKeyboard();
        this.bindCanvasDrops();
        this.bindInlineEditing();
        this.autoSave();
    },

    loadPageData() {
        fetch(`/page-builder/pages/${this.pageId}/data`)
            .then(r => r.json())
            .then(data => { window._pageData = data.page; });
    },

    loadElements() {
        fetch(`/page-builder/pages/${this.pageId}/elements`)
            .then(r => r.json())
            .then(data => {
                const prevSelected = this.selectedId;
                this.renderCanvas(data.elements);
                this.renderStructure(data.elements);
                this.pushHistory(data.elements);
                if (prevSelected && document.querySelector(`.pb-el[data-el-id="${prevSelected}"]`)) {
                    this.selectElement(prevSelected);
                }
            });
    },

    renderCanvas(elements, parentEl) {
        const dz = document.getElementById('canvas-dropzone');
        if (!parentEl) {
            dz.innerHTML = '';
            if (!elements || elements.length === 0) {
                dz.innerHTML = `<div class="pb-empty-canvas" id="empty-canvas"><div class="pb-empty-icon">&#128161;</div><p><strong>Drag widgets from the left panel</strong><br>to start building your page</p></div>`;
                return;
            }
        }
        (elements || []).forEach(el => {
            const div = document.createElement('div');
            div.className = 'pb-el';
            div.dataset.elId = el.id;
            div.dataset.elType = el.type;
            div.innerHTML = this.elementHtml(el);
            div.onclick = (e) => { e.stopPropagation(); this.selectElement(el.id); };
            if (parentEl) parentEl.appendChild(div);
            else dz.appendChild(div);
            if (el.children && el.children.length > 0) {
                const childContainer = document.createElement('div');
                childContainer.className = 'pb-el-children';
                div.appendChild(childContainer);
                this.renderCanvas(el.children, childContainer);
            }
        });
    },

    elementHtml(el) {
        let name = el.name || el.type;
        const s = el.settings || {};
        let preview = '';
        switch (el.type) {
            case 'heading': preview = `<${s.tag || 'h2'} style="text-align:${s.alignment||'left'};color:${s.color||'#333'};font-size:${({small:'1.2em',default:'2em',medium:'2.5em',large:'3em',xl:'3.5em',xxl:'4.5em'})[s.size]||'2em'};font-weight:${s.font_weight||'700'};line-height:${s.line_height||'1.4'}">${this.escHtml(s.title||'Heading')}</${s.tag || 'h2'}>`; break;
            case 'text': preview = `<div style="text-align:${s.alignment||'left'};color:${s.color||'#666'};font-size:${s.font_size||'16px'};font-weight:${s.font_weight||'400'};line-height:${s.line_height||'1.7'}">${s.content||'<p>Text content</p>'}</div>`; break;
            case 'image':
                if (s.image && s.image.url) preview = `<div style="text-align:${s.alignment||'center'}"><img src="${this.escHtml(s.image.url)}" alt="${this.escHtml(s.image.alt||'')}" style="width:${s.width||'100%'};max-width:${s.max_width||'100%'};height:${s.height||'auto'};object-fit:${s.object_fit||'cover'};border-radius:${s.border_radius||'0px'};opacity:${s.opacity||1}"></div>`;
                else preview = `<div class="pb-image-placeholder" style="text-align:center;padding:2rem;color:#999">No image selected</div>`;
                break;
            case 'button': {
                const sizeMap = {small:{p:'8px 16px',f:'14px'},medium:{p:'12px 24px',f:'16px'},large:{p:'16px 32px',f:'18px'},xl:{p:'20px 40px',f:'20px'}};
                const sz = sizeMap[s.size]||sizeMap.medium;
                const btn = `<button style="background-color:${s.background_color||'#007bff'};color:${s.text_color||'#fff'};border:${s.border_width||'0px'} solid ${s.border_color||'transparent'};border-radius:${s.border_radius||'4px'};padding:${sz.p};font-size:${sz.f};font-weight:${s.font_weight||'500'};cursor:pointer;display:inline-block">${this.escHtml(s.text||'Button')}</button>`;
                preview = s.alignment !== 'stretch' ? `<div style="text-align:${s.alignment||'left'}">${btn}</div>` : btn;
                break;
            }
            default: preview = `<div class="pb-el-placeholder">${el.type}</div>`;
        }
        return `<div class="pb-el-toolbar"><span class="pb-el-name">${name}</span><span class="pb-el-type">${el.type}</span><span style="flex:1"></span><button class="pb-el-action" onclick="event.stopPropagation();editor.duplicateElement(${el.id})" title="Duplicate">&#128203;</button><button class="pb-el-action" onclick="event.stopPropagation();editor.deleteElement(${el.id})" title="Delete">&#128465;</button></div><div class="pb-el-content">${preview}</div>`;
    },

    escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    },

    renderStructure(elements, parentUl) {
        const ul = parentUl || document.getElementById('structure-tree');
        if (!parentUl) ul.innerHTML = '';
        (elements || []).forEach(el => {
            const li = document.createElement('li');
            li.className = 'pb-structure-item';
            li.dataset.elId = el.id;
            li.innerHTML = `<span class="si-icon">${this.structureIcon(el.type)}</span><span>${el.name || el.type}</span><span class="si-type">${el.type}</span>`;
            li.onclick = (e) => { e.stopPropagation(); this.selectElement(el.id); };
            ul.appendChild(li);
            if (el.children && el.children.length > 0) {
                const childUl = document.createElement('ul');
                childUl.className = 'pb-structure-children';
                li.appendChild(childUl);
                this.renderStructure(el.children, childUl);
            }
        });
    },

    structureIcon(type) {
        const icons = { section: '&#9638;', column: '&#9646;', heading: 'H', text: 'T', image: '&#128247;', button: '&#128206;' };
        return icons[type] || '&#9679;';
    },

    selectElement(id) {
        this.selectedId = id;
        document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
        const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
        if (el) el.classList.add('selected');
        const si = document.querySelector(`.pb-structure-item[data-el-id="${id}"]`);
        if (si) si.classList.add('active');
        this.loadControls(id);
    },

    loadControls(id) {
        fetch(`/page-builder/elements/${id}/controls`)
            .then(r => r.json())
            .then(data => {
                if (data.error) return;
                const widget = data.widget;
                const element = data.element;
                document.getElementById('settings-empty').style.display = 'none';
                const sf = document.getElementById('settings-form');
                sf.style.display = '';
                document.getElementById('settings-title').textContent = element.name || widget.label;
                document.getElementById('settings-type').textContent = widget.type;
                this.renderControls(widget.controls || {}, element.settings || {}, id);
            });
    },

    renderControls(controls, settings, elementId) {
        const body = document.getElementById('settings-body');
        body.innerHTML = '';
        const sections = this.groupControls(controls);
        for (const [section, ctrls] of Object.entries(sections)) {
            const secDiv = document.createElement('div');
            secDiv.className = 'pb-settings-section';
            if (section !== '_default') {
                const title = document.createElement('div');
                title.className = 'pb-settings-section-title';
                title.textContent = section;
                secDiv.appendChild(title);
            }
            ctrls.forEach(([key, ctrl]) => {
                const val = settings[key] !== undefined ? settings[key] : (ctrl.default !== undefined ? ctrl.default : '');
                const control = document.createElement('div');
                control.className = 'pb-control';
                const label = document.createElement('label');
                label.textContent = ctrl.label || key;
                label.htmlFor = `ctrl-${key}`;
                control.appendChild(label);
                control.appendChild(this.createInput(key, ctrl, val, elementId));
                secDiv.appendChild(control);
            });
            body.appendChild(secDiv);
        }
    },

    groupControls(controls) {
        const sections = { '_default': [] };
        for (const [key, ctrl] of Object.entries(controls)) {
            const section = ctrl.section || '_default';
            if (!sections[section]) sections[section] = [];
            sections[section].push([key, ctrl]);
        }
        return sections;
    },

    createInput(key, ctrl, value, elementId) {
        const types = {
            text: () => {
                const inp = document.createElement('input');
                inp.type = 'text'; inp.id = `ctrl-${key}`; inp.value = value || '';
                inp.spellcheck = false;
                inp.onchange = (e) => this.updateSetting(key, e.target.value, elementId);
                return inp;
            },
            number: () => {
                const inp = document.createElement('input');
                inp.type = 'number'; inp.id = `ctrl-${key}`; inp.value = value;
                if (ctrl.min !== undefined) inp.min = ctrl.min;
                if (ctrl.max !== undefined) inp.max = ctrl.max;
                inp.onchange = (e) => this.updateSetting(key, parseFloat(e.target.value) || 0, elementId);
                return inp;
            },
            textarea: () => {
                const ta = document.createElement('textarea');
                ta.id = `ctrl-${key}`; ta.value = typeof value === 'string' ? value : '';
                ta.spellcheck = false;
                ta.onchange = (e) => this.updateSetting(key, e.target.value, elementId);
                return ta;
            },
            select: () => {
                const sel = document.createElement('select');
                sel.id = `ctrl-${key}`;
                (ctrl.options || []).forEach(opt => {
                    const o = document.createElement('option');
                    o.value = opt; o.textContent = opt;
                    if (opt === value) o.selected = true;
                    sel.appendChild(o);
                });
                sel.onchange = (e) => this.updateSetting(key, e.target.value, elementId);
                return sel;
            },
            color: () => {
                const container = document.createElement('div');
                container.style.cssText = 'display:flex;gap:.5rem;align-items:center';
                const inp = document.createElement('input');
                inp.type = 'color'; inp.id = `ctrl-${key}`; inp.value = value || '#000000';
                const txt = document.createElement('input');
                txt.type = 'text'; txt.value = value || '#000000';
                txt.style.cssText = 'flex:1';
                const update = (v) => { inp.value = v; txt.value = v; this.updateSetting(key, v, elementId); };
                inp.oninput = (e) => update(e.target.value);
                txt.onchange = (e) => { if (/^#[0-9a-f]{3,8}$/i.test(e.target.value)) update(e.target.value); };
                container.appendChild(inp);
                container.appendChild(txt);
                return container;
            },
            boolean: () => {
                const container = document.createElement('div');
                container.style.cssText = 'display:flex;align-items:center;gap:.5rem';
                const cb = document.createElement('input');
                cb.type = 'checkbox'; cb.id = `ctrl-${key}`; cb.checked = !!value;
                cb.onchange = (e) => this.updateSetting(key, e.target.checked, elementId);
                container.appendChild(cb);
                return container;
            },
            url: () => {
                const inp = document.createElement('input');
                inp.type = 'url'; inp.id = `ctrl-${key}`; inp.value = value || '';
                inp.onchange = (e) => this.updateSetting(key, e.target.value, elementId);
                return inp;
            },
        };
        return (types[ctrl.type] || types.text)();
    },
    updateSetting(key, value, elementId) {
        this.dirty = true;
        const settings = {};
        settings[key] = value;
        fetch(`/page-builder/elements/${elementId}/settings`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ settings }),
        })
        .then(r => r.json())
        .then(() => this.reloadElement(elementId));
    },


    reloadElement(id) {
        fetch(`/page-builder/elements/${id}/render`)
            .then(r => r.json())
            .then(data => {
                const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
                if (el) {
                    const oldContent = el.querySelector('.pb-el-content');
                    if (oldContent) oldContent.innerHTML = data.html;
                    else el.innerHTML = `<div class="pb-el-content">${data.html}</div>`;
                }
            });
    },

    deleteElement(id) {
        if (!confirm('Delete this element?')) return;
        fetch(`/page-builder/elements/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrf } })
            .then(r => r.json())
            .then(() => {
                if (this.selectedId === id) { this.selectedId = null; document.getElementById('settings-empty').style.display = ''; document.getElementById('settings-form').style.display = 'none'; }
                this.loadElements();
            });
    },

    deleteSelected() { if (this.selectedId) this.deleteElement(this.selectedId); },

    duplicateElement(id) {
        fetch(`/page-builder/elements/${id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf } })
            .then(r => r.json())
            .then(() => this.loadElements());
    },

    save() {
        if (this.saving) return;
        this.saving = true;
        const overlay = document.createElement('div');
        overlay.className = 'saving-overlay';
        overlay.innerHTML = '<div class="spinner"></div>';
        document.body.appendChild(overlay);
        fetch(`/page-builder/pages/${this.pageId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ status: 'draft' }),
        })
        .then(r => r.json())
        .then(() => {
            this.dirty = false;
            overlay.remove();
            this.showToast('Page saved!');
        })
        .catch(() => overlay.remove());
    },

    publish() {
        if (!confirm('Publish this page?')) return;
        fetch(`/page-builder/pages/${this.pageId}/publish`, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf } })
            .then(r => r.json())
            .then(() => { this.showToast('Page published!'); location.reload(); });
    },

    setResponsive(mode) {
        this.responsiveMode = mode;
        document.querySelectorAll('.pb-toolbar [data-mode]').forEach(b => b.classList.remove('active'));
        document.querySelector(`.pb-toolbar [data-mode="${mode}"]`).classList.add('active');
        const canvas = document.getElementById('canvas');
        canvas.className = 'pb-canvas';
        if (mode !== 'desktop') canvas.classList.add('is-' + mode);
    },

    switchTab(tab) {
        document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
        document.querySelector(`.pb-panel-tab[data-tab="${tab}"]`).classList.add('active');
        document.getElementById('panel-widgets').style.display = tab === 'widgets' ? '' : 'none';
        document.getElementById('panel-structure').style.display = tab === 'structure' ? '' : 'none';
        document.getElementById('panel-layouts').style.display = tab === 'layouts' ? '' : 'none';
        if (tab === 'layouts') this.loadTemplates();
    },

    pushHistory(elements) {
        this.historyIndex++;
        this.history = this.history.slice(0, this.historyIndex);
        this.history.push(JSON.parse(JSON.stringify(elements || [])));
        this.updateUndoButtons();
    },

    undo() {
        if (this.historyIndex <= 0) return;
        this.historyIndex--;
        this.restoreHistory();
    },

    redo() {
        if (this.historyIndex >= this.history.length - 1) return;
        this.historyIndex++;
        this.restoreHistory();
    },

    restoreHistory() {
        this.renderCanvas(this.history[this.historyIndex]);
        this.renderStructure(this.history[this.historyIndex]);
        this.updateUndoButtons();
    },

    updateUndoButtons() {
        document.getElementById('pb-undo').style.opacity = this.historyIndex > 0 ? '1' : '.4';
        document.getElementById('pb-redo').style.opacity = this.historyIndex < this.history.length - 1 ? '1' : '.4';
    },

    bindDragDrop() {
        document.querySelectorAll('.pb-widget-item[draggable]').forEach(w => {
            w.addEventListener('dragstart', e => {
                e.dataTransfer.setData('text/plain', w.dataset.type);
                e.dataTransfer.effectAllowed = 'copy';
            });
        });
    },

    bindCanvasDrops() {
        const dz = document.getElementById('canvas-dropzone');
        const canvas = document.getElementById('canvas');
        [dz, canvas].forEach(el => {
            el.addEventListener('dragover', e => { e.preventDefault(); e.dataTransfer.dropEffect = 'copy'; });
            el.addEventListener('drop', e => {
                e.preventDefault();
                const type = e.dataTransfer.getData('text/plain');
                if (!type) return;
                let parentId = null;
                const target = e.target.closest('.pb-el');
                if (target) parentId = target.dataset.elId;
                fetch(`/page-builder/pages/${this.pageId}/elements`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                    body: JSON.stringify({ type, parent_id: parentId }),
                })
                .then(r => r.json())
                .then(() => this.loadElements());
            });
        });
    },

    bindInlineEditing() {
        const dz = document.getElementById('canvas-dropzone');
        dz.addEventListener('dblclick', (e) => {
            const el = e.target.closest('.pb-el');
            if (!el) return;
            const textEl = e.target.closest('h1, h2, h3, h4, h5, h6, p, span, a, button, label');
            if (!textEl) return;
            if (el.dataset._editing) return;
            const originalContent = textEl.textContent;
            el.dataset._editing = '1';
            textEl.contentEditable = 'true';
            textEl.focus();
            const selection = window.getSelection();
            const range = document.createRange();
            range.selectNodeContents(textEl);
            selection.removeAllRanges();
            selection.addRange(range);
            const finish = () => {
                if (!el.dataset._editing) return;
                textEl.contentEditable = 'false';
                delete el.dataset._editing;
                const newText = textEl.textContent.trim();
                if (newText && newText !== originalContent) {
                    const type = el.dataset.elType;
                    const key = { heading: 'title', text: 'content', button: 'text' }[type] || 'title';
                    this.updateSetting(key, newText, el.dataset.elId);
                }
            };
            textEl.addEventListener('blur', finish, { once: true });
            textEl.addEventListener('keydown', (k) => {
                if (k.key === 'Enter' && !k.shiftKey) { k.preventDefault(); textEl.blur(); }
                if (k.key === 'Escape') { textEl.textContent = originalContent; textEl.blur(); }
            });
        });
    },

    bindKeyboard() {
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); this.undo(); }
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) { e.preventDefault(); this.redo(); }
            if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); this.save(); }
            if (e.key === 'Delete' && this.selectedId) { this.deleteSelected(); }
        });
    },

    autoSave() {
        setInterval(() => {
            if (this.dirty) this.save();
        }, 60000);
    },

    showToast(msg) {
        const t = document.createElement('div');
        t.className = 'pb-undo-toast';
        t.innerHTML = `<span>${msg}</span>`;
        document.body.appendChild(t);
        setTimeout(() => { t.style.opacity = '0'; t.style.transition = 'opacity .3s'; setTimeout(() => t.remove(), 300); }, 2000);
    },

    loadTemplates() {
        fetch('/page-builder/templates')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('layout-templates');
                container.innerHTML = '<div class="pb-widget-group-title" style="margin-bottom:.75rem">Choose a layout template</div>';
                for (const [key, tmpl] of Object.entries(data.templates)) {
                    const previews = { blank: '&#9635;', landing: '&#127968;', about: '&#128100;', contact: '&#128222;', showcase: '&#127912;' };
                    const card = document.createElement('div');
                    card.className = 'pb-layout-card';
                    card.innerHTML = `
                        <div class="pb-layout-card-preview">${previews[key] || '&#9635;'}</div>
                        <div class="pb-layout-card-info">
                            <h4>${tmpl.name}</h4>
                            <p>${tmpl.description}</p>
                        </div>
                        <button class="pb-apply-btn" data-template="${key}">Apply Template</button>
                    `;
                    card.querySelector('.pb-apply-btn').onclick = (e) => {
                        e.stopPropagation();
                        this.applyTemplate(key, e.target);
                    };
                    container.appendChild(card);
                }
            });
    },

    applyTemplate(key, btn) {
        if (!confirm('Apply this template? It will replace all existing content.')) return;
        btn.disabled = true; btn.textContent = 'Applying...';
        fetch(`/page-builder/pages/${this.pageId}/apply-template`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ template: key }),
        })
        .then(r => r.json())
        .then(() => {
            this.showToast('Template applied!');
            this.loadElements();
            btn.disabled = false; btn.textContent = 'Apply Template';
        })
        .catch(() => { btn.disabled = false; btn.textContent = 'Apply Template'; });
    },

    showPageSettings() {
        this.selectedId = null;
        document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
        document.getElementById('settings-empty').style.display = 'none';
        document.getElementById('settings-form').style.display = 'none';
        document.getElementById('page-settings-form').style.display = 'flex';
        this.renderPageSettings();
    },

    hidePageSettings() {
        document.getElementById('page-settings-form').style.display = 'none';
        document.getElementById('settings-empty').style.display = '';
    },

    renderPageSettings() {
        const body = document.getElementById('page-settings-body');
        body.innerHTML = '';
        const currentPage = window._pageData || {};
        const s = currentPage.settings || {};

        const controls = [
            { key: 'container_width', label: 'Container Width', type: 'text', default: '1140px' },
            { key: 'page_background', label: 'Page Background', type: 'color', default: '#ffffff' },
            { key: 'content_padding', label: 'Content Padding', type: 'text', default: '0px' },
            { key: 'custom_css', label: 'Custom CSS', type: 'textarea', default: '' },
        ];

        controls.forEach(ctrl => {
            const val = s[ctrl.key] !== undefined ? s[ctrl.key] : ctrl.default;
            const group = document.createElement('div');
            group.className = 'pb-settings-section';
            group.innerHTML = `<div class="pb-control"><label>${ctrl.label}</label></div>`;
            const inputWrap = group.querySelector('.pb-control');

            if (ctrl.type === 'color') {
                const container = document.createElement('div');
                container.style.cssText = 'display:flex;gap:.5rem;align-items:center';
                const inp = document.createElement('input'); inp.type = 'color'; inp.value = val;
                const txt = document.createElement('input'); txt.type = 'text'; txt.value = val; txt.style.cssText = 'flex:1';
                const update = (v) => { inp.value = v; txt.value = v; this.updatePageSetting(ctrl.key, v); };
                inp.oninput = (e) => update(e.target.value);
                txt.oninput = (e) => { if (/^#[0-9a-f]{3,8}$/i.test(e.target.value)) update(e.target.value); };
                container.appendChild(inp); container.appendChild(txt);
                inputWrap.appendChild(container);
            } else if (ctrl.type === 'textarea') {
                const ta = document.createElement('textarea');
                ta.value = val || '';
                ta.style.cssText = 'width:100%;padding:.45rem .6rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:.8rem;min-height:100px;font-family:monospace';
                ta.onchange = (e) => this.updatePageSetting(ctrl.key, e.target.value);
                inputWrap.appendChild(ta);
            } else {
                const inp = document.createElement('input');
                inp.type = 'text'; inp.value = val || ''; inp.style.cssText = 'width:100%;padding:.45rem .6rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:.8rem';
                inp.onchange = (e) => this.updatePageSetting(ctrl.key, e.target.value);
                inputWrap.appendChild(inp);
            }

            body.appendChild(group);
        });
    },

    updatePageSetting(key, value) {
        this.dirty = true;
        const settings = {};
        settings[key] = value;
        fetch(`/page-builder/pages/${this.pageId}/layout`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ settings }),
        });
    },
};

document.addEventListener('DOMContentLoaded', () => editor.init());
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
