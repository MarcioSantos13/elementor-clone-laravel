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

.pb-panel-tabs { display: flex; border-bottom: 1px solid var(--pb-border); background: rgba(0,0,0,.15); gap: 2px; padding: 4px; }
.pb-panel-tab {
    flex: 1; padding: .55rem .5rem; text-align: center; cursor: pointer; font-size: .75rem;
    color: var(--pb-text2); border: 1px solid transparent; background: transparent;
    border-radius: 6px; transition: all .2s; font-weight: 500;
}
.pb-panel-tab.active { color: var(--pb-accent); background: rgba(99,102,241,.12); border-color: rgba(99,102,241,.2); }
.pb-panel-tab:hover { color: var(--pb-text); background: rgba(255,255,255,.05); }
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
    position: relative; padding: .5rem .5rem .5rem 1.6rem; min-height: 30px; border: 2px solid transparent;
    transition: border-color .2s, box-shadow .2s; border-radius: 4px;
}
.pb-el:hover { border-color: rgba(99,102,241,.25); background: rgba(99,102,241,.02); }
.pb-el.selected { border-color: var(--pb-primary); box-shadow: 0 0 0 1px var(--pb-primary), 0 4px 12px rgba(99,102,241,.12); }
.pb-el.drop-over { border-color: var(--pb-accent); background: var(--pb-primary-light); }
.pb-el.drop-target { border-color: var(--pb-success) !important; background: rgba(34,197,94,.06); }

.pb-el-drag {
    position: absolute; left: 0; top: 0; bottom: 0; width: 1.2rem;
    display: flex; align-items: center; justify-content: center;
    cursor: grab; color: var(--pb-text2); opacity: 0; transition: opacity .15s;
    font-size: .65rem; letter-spacing: 1px; user-select: none; z-index: 5;
}
.pb-el:hover > .pb-el-drag, .pb-el.selected > .pb-el-drag { opacity: .6; }
.pb-el-drag:hover { opacity: 1 !important; color: var(--pb-primary); }
.pb-el-drag:active { cursor: grabbing; }

.drop-before, .drop-after {
    position: relative; z-index: 10;
}
.drop-before::before, .drop-after::after {
    content: ''; display: block; height: 3px; background: var(--pb-primary);
    border-radius: 2px; margin: 2px 0; box-shadow: 0 0 6px rgba(99,102,241,.4);
    animation: dropLinePulse 1s ease-in-out infinite;
}
.drop-before::before { margin-bottom: 4px; }
.drop-after::after { margin-top: 4px; }
@keyframes dropLinePulse { 0%,100% { opacity: 1; } 50% { opacity: .5; } }

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
.pb-editor-tabs {
    display: flex; border-bottom: 1px solid var(--pb-border); flex-shrink: 0; gap: 2px; padding: 4px;
}
.pb-editor-tab {
    flex: 1; padding: .45rem .5rem; text-align: center; font-size: .7rem; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px; cursor: pointer;
    background: transparent; border: 1px solid transparent; color: var(--pb-text2);
    border-radius: 6px; transition: all .2s;
}
.pb-editor-tab:hover { color: var(--pb-text); background: rgba(255,255,255,.05); }
.pb-editor-tab.active { color: var(--pb-accent); background: rgba(99,102,241,.12); border-color: rgba(99,102,241,.2); }
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
.pb-control input[type="color"] { padding: 2px; height: 42px; cursor: pointer; border-radius: 6px; flex-shrink: 0; width: auto; }
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

.pb-navigator-toggle {
    position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9990;
    width: 44px; height: 44px; border-radius: 50%; border: none; cursor: pointer;
    background: var(--pb-primary); color: #fff; font-size: 1.2rem;
    box-shadow: 0 4px 16px rgba(99,102,241,.4); transition: all .2s;
    display: flex; align-items: center; justify-content: center;
}
.pb-navigator-toggle:hover { transform: scale(1.1); box-shadow: 0 6px 24px rgba(99,102,241,.5); }

.pb-navigator {
    position: fixed; bottom: 5rem; right: 1.5rem; z-index: 9991;
    width: 280px; max-height: 60vh; background: var(--pb-surface);
    border: 1px solid var(--pb-border); border-radius: 12px;
    box-shadow: 0 12px 48px rgba(0,0,0,.3); display: none;
    flex-direction: column; overflow: hidden;
}
.pb-navigator.open { display: flex; }
.pb-navigator-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: .6rem .8rem; border-bottom: 1px solid var(--pb-border);
    background: rgba(0,0,0,.1); flex-shrink: 0;
}
.pb-navigator-header span { font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; }
.pb-navigator-header button { background: none; border: none; color: var(--pb-text2); cursor: pointer; font-size: .9rem; padding: 2px 6px; border-radius: 4px; }
.pb-navigator-header button:hover { background: var(--pb-surface2); color: var(--pb-text); }
.pb-navigator-body { overflow-y: auto; flex: 1; padding: .4rem 0; }
.pb-navigator-body::-webkit-scrollbar { width: 4px; }
.pb-navigator-body::-webkit-scrollbar-thumb { background: var(--pb-border); border-radius: 10px; }

.pb-nav-item {
    display: flex; align-items: center; gap: .4rem; padding: .35rem .6rem;
    cursor: pointer; font-size: .75rem; transition: background .15s;
    border-left: 3px solid transparent; user-select: none;
}
.pb-nav-item:hover { background: var(--pb-surface2); }
.pb-nav-item.active { background: rgba(99,102,241,.1); border-left-color: var(--pb-accent); color: var(--pb-accent); font-weight: 600; }
.pb-nav-item .nav-icon { font-size: .8rem; width: 18px; text-align: center; flex-shrink: 0; }
.pb-nav-item .nav-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pb-nav-item .nav-type { font-size: .6rem; color: var(--pb-text2); flex-shrink: 0; }
.pb-nav-item .nav-toggle { font-size: .6rem; cursor: pointer; padding: 0 2px; color: var(--pb-text2); transition: transform .2s; flex-shrink: 0; }
.pb-nav-item .nav-toggle.expanded { transform: rotate(90deg); }
.pb-nav-children { padding-left: 1rem; }

.pb-nav-item.drag-over { background: rgba(99,102,241,.15); border-left-color: var(--pb-accent); }
.pb-nav-item.drag-indicator { border-top: 2px solid var(--pb-accent); }

.pb-nav-context, .pb-canvas-context {
    position: fixed; z-index: 99999; background: var(--pb-surface);
    border: 1px solid var(--pb-border); border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0,0,0,.3); padding: .3rem 0; min-width: 160px;
}
.pb-nav-context-item, .pb-canvas-context-item {
    display: flex; align-items: center; gap: .5rem; padding: .4rem .8rem;
    font-size: .75rem; cursor: pointer; transition: background .1s;
}
.pb-nav-context-item:hover, .pb-canvas-context-item:hover { background: var(--pb-surface2); }
.pb-nav-context-item.danger, .pb-canvas-context-item.danger { color: var(--pb-danger); }
.pb-nav-context-sep, .pb-canvas-context-sep { height: 1px; background: var(--pb-border); margin: .2rem 0; }

.pb-nav-rename-input {
    font-size: .75rem; padding: 1px 4px; background: var(--pb-surface2);
    border: 1px solid var(--pb-accent); border-radius: 3px; color: var(--pb-text);
    width: 120px; outline: none;
}
</style>