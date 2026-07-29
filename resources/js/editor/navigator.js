import Sortable from 'sortablejs';
import { structureIcon, apiFetch } from './utils.js';

let _navSortables = new Map();

export function toggleNavigator(state) {
    const navPanel = document.getElementById('panel-navigator');
    const isVisible = navPanel.style.display !== 'none';
    if (isVisible) {
        document.getElementById('panel-widgets').style.display = '';
        document.getElementById('panel-navigator').style.display = 'none';
        document.getElementById('panel-structure').style.display = 'none';
        document.getElementById('panel-layouts').style.display = 'none';
        document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
        document.querySelector('.pb-panel-tab[data-tab="widgets"]').classList.add('active');
    } else {
        document.getElementById('panel-widgets').style.display = 'none';
        document.getElementById('panel-navigator').style.display = '';
        document.getElementById('panel-structure').style.display = 'none';
        document.getElementById('panel-layouts').style.display = 'none';
        document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
        document.querySelector('.pb-panel-tab[data-tab="navigator"]').classList.add('active');
        renderNavigator(state);
    }
}

export function renderNavigator(state) {
    const body = document.getElementById('navigator-body');
    body.innerHTML = '';
    destroyNavSortables();

    const searchBox = document.createElement('div');
    searchBox.className = 'pb-nav-search';
    searchBox.innerHTML = '<input type="text" id="nav-search-input" placeholder="Buscar elementos..." autocomplete="off"><span class="pb-nav-search-icon">&#128269;</span>';
    body.appendChild(searchBox);

    const treeWrap = document.createElement('div');
    treeWrap.className = 'pb-nav-tree';
    body.appendChild(treeWrap);

    const els = state._lastElements || [];
    _renderNavItems(state, els, treeWrap, 0);
    _initNavSortable(treeWrap, state, null);

    const searchInput = document.getElementById('nav-search-input');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const q = searchInput.value.toLowerCase().trim();
            const items = treeWrap.querySelectorAll('.pb-nav-item');
            items.forEach(item => {
                if (!q) { item.style.display = ''; return; }
                const name = (item.querySelector('.nav-name')?.textContent || '').toLowerCase();
                const type = (item.dataset.elType || '').toLowerCase();
                const match = name.includes(q) || type.includes(q);
                item.style.display = match ? '' : 'none';
                if (match) {
                    let parent = item.parentElement;
                    while (parent && parent !== treeWrap) {
                        if (parent.classList.contains('pb-nav-children')) parent.style.display = '';
                        parent = parent.parentElement;
                    }
                }
            });
        });
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { searchInput.value = ''; searchInput.dispatchEvent(new Event('input')); searchInput.blur(); }
        });
    }
}

export function _renderNavItems(state, elements, container, depth) {
    (elements || []).forEach(el => {
        const item = document.createElement('div');
        item.className = 'pb-nav-item' + (state.selectedId === el.id ? ' active' : '');
        item.dataset.elId = el.id;
        item.dataset.elType = el.type;
        item.style.paddingLeft = (.6 + depth * .8) + 'rem';

        const hasChildren = el.children && el.children.length > 0;

        const toggle = document.createElement('span');
        toggle.className = 'nav-toggle' + (hasChildren ? ' expanded' : '');
        toggle.innerHTML = hasChildren ? '&#9654;' : '';
        toggle.style.visibility = hasChildren ? 'visible' : 'hidden';
        toggle.onclick = (e) => {
            e.stopPropagation();
            const ch = item.nextElementSibling;
            if (ch && ch.classList.contains('pb-nav-children')) {
                const visible = ch.style.display !== 'none';
                ch.style.display = visible ? 'none' : '';
                toggle.classList.toggle('expanded', !visible);
            }
        };

        const icon = document.createElement('span');
        icon.className = 'nav-icon';
        icon.innerHTML = structureIcon(el.type);

        const name = document.createElement('span');
        name.className = 'nav-name';
        name.textContent = el.name || el.type;

        const type = document.createElement('span');
        type.className = 'nav-type';
        type.textContent = el.type;

        item.appendChild(toggle);
        item.appendChild(icon);
        item.appendChild(name);
        item.appendChild(type);

        item.onclick = (e) => {
            e.stopPropagation();
            state.onSelectElement(el.id);
            renderNavigator(state);
        };

        item.ondblclick = (e) => {
            e.stopPropagation();
            _startNavRename(state, name, el);
        };

        item.oncontextmenu = (e) => {
            e.preventDefault();
            e.stopPropagation();
            state.onSelectElement(el.id);
            renderNavigator(state);
            _showNavContext(state, e.clientX, e.clientY, el);
        };

        container.appendChild(item);

        if (hasChildren) {
            const childDiv = document.createElement('div');
            childDiv.className = 'pb-nav-children';
            container.appendChild(childDiv);
            _renderNavItems(state, el.children, childDiv, depth + 1);
            _initNavSortable(childDiv, state, el.id);
        }
    });
}

function _initNavSortable(container, state, parentId) {
    if (_navSortables.has(container)) {
        _navSortables.get(container).destroy();
    }

    const sortable = Sortable.create(container, {
        group: {
            name: 'navigator',
            pull: true,
            put: true,
        },
        animation: 150,
        ghostClass: 'pb-nav-ghost',
        chosenClass: 'pb-nav-chosen',
        draggable: '.pb-nav-item',
        handle: '.pb-nav-item',
        onAdd(evt) {
            const dragId = parseInt(evt.item.dataset.elId);
            const newParentEl = evt.to.closest('.pb-nav-item');
            const newParentId = newParentEl ? parseInt(newParentEl.dataset.elId) : null;

            if (dragId) {
                _navMoveElement(state, dragId, newParentId);
            }
        },
        onUpdate(evt) {
            const dragId = parseInt(evt.item.dataset.elId);
            const siblingItems = Array.from(evt.to.children).filter(
                c => c.classList.contains('pb-nav-item') && c.dataset.elId
            );
            const newSiblingId = siblingItems[evt.newIndex + 1]
                ? parseInt(siblingItems[evt.newIndex + 1].dataset.elId)
                : null;

            if (dragId) {
                _navReorderElement(state, dragId, newParentId, newSiblingId);
            }
        },
    });

    _navSortables.set(container, sortable);
}

function destroyNavSortables() {
    _navSortables.forEach(s => s.destroy());
    _navSortables.clear();
}

export function _startNavRename(state, nameEl, el) {
    const input = document.createElement('input');
    input.className = 'pb-nav-rename-input';
    input.value = el.name || el.type;
    nameEl.replaceWith(input);
    input.focus();
    input.select();

    const save = () => {
        const newName = input.value.trim() || el.type;
        el.name = newName;
        nameEl.textContent = newName;
        input.replaceWith(nameEl);
        apiFetch(`/page-builder/elements/${el.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
            body: JSON.stringify({ name: newName }),
        }).catch(() => {});
    };

    input.onblur = save;
    input.onkeydown = (e) => {
        if (e.key === 'Enter') save();
        if (e.key === 'Escape') { input.replaceWith(nameEl); }
    };
}

export function _showNavContext(state, x, y, el) {
    _hideNavContext();
    const ctx = document.createElement('div');
    ctx.className = 'pb-nav-context';
    ctx.style.left = x + 'px';
    ctx.style.top = y + 'px';

    const items = [
        { label: '\u270E Rename', action: () => { const item = document.querySelector(`.pb-nav-item[data-el-id="${el.id}"] .nav-name`); if (item) _startNavRename(state, item, el); } },
        { label: '\u29C9 Duplicate', action: () => state.duplicateElement(el.id) },
        { sep: true },
        { label: '\u2191 Move Up', action: () => _navMoveRelative(state, el.id, -1) },
        { label: '\u2193 Move Down', action: () => _navMoveRelative(state, el.id, 1) },
        { sep: true },
        { label: '\u29C9 Copy', action: () => { state._clipboard = JSON.parse(JSON.stringify(el)); state.showToast('Elemento copiado', 'success'); } },
        { label: '\uD83D\uDCCB Paste (ap\u00F3s)', action: () => _navPasteAfter(state, el.id) },
        { sep: true },
        { label: '\uD83C\uDFA8 Copy Styles', action: () => { state.onSelectElement(el.id); setTimeout(() => { state._styleClipboard = JSON.parse(JSON.stringify(state.cachedStyles || {})); state.showToast('Estilos copiados!', 'success'); }, 100); } },
        { label: '\uD83C\uDFA8 Paste Styles', action: () => { state.onSelectElement(el.id); setTimeout(() => { if (!state._styleClipboard) { state.showToast('Nenhum estilo copiado', 'error'); return; } const elId = el.id; const styles = state._styleClipboard; let applied = 0; const keys = Object.keys(styles); const applyNext = (idx) => { if (idx >= keys.length) { if (applied > 0) { state.showToast(`${applied} estilo(s) aplicado(s)!`, 'success'); state.loadElements(); setTimeout(() => state.onSelectElement(elId), 200); } return; } const k = keys[idx]; const v = styles[k]; if (v !== undefined && v !== '' && v !== null) { applied++; fetch(`/page-builder/elements/${elId}/styles`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf }, body: JSON.stringify({ styles: { [k]: v } }) }).then(() => applyNext(idx + 1)).catch(() => applyNext(idx + 1)); } else { applyNext(idx + 1); } }; applyNext(0); }, 100); } },
        { sep: true },
        { label: '\u2715 Delete', cls: 'danger', action: () => state.deleteElement(el.id) },
    ];

    items.forEach(m => {
        if (m.sep) {
            const sep = document.createElement('div');
            sep.className = 'pb-nav-context-sep';
            ctx.appendChild(sep);
            return;
        }
        const btn = document.createElement('div');
        btn.className = 'pb-nav-context-item' + (m.cls ? ' ' + m.cls : '');
        btn.textContent = m.label;
        btn.onclick = (e) => { e.stopPropagation(); _hideNavContext(); m.action(); };
        ctx.appendChild(btn);
    });

    document.body.appendChild(ctx);
    const closeCtx = (e) => {
        if (!ctx.contains(e.target)) { _hideNavContext(); document.removeEventListener('click', closeCtx); }
    };
    setTimeout(() => document.addEventListener('click', closeCtx), 10);
}

export function _hideNavContext() {
    document.querySelectorAll('.pb-nav-context').forEach(c => c.remove());
}

export function _showCanvasContext(state, x, y, elId) {
    _hideCanvasContext();
    const ctx = document.createElement('div');
    ctx.className = 'pb-canvas-context';
    ctx.style.left = x + 'px';
    ctx.style.top = y + 'px';

    const items = [
        { label: '\u270E Editar', action: () => state.onSelectElement(elId) },
        { label: '\u29C9 Duplicar', action: () => state.duplicateElement(elId) },
        { sep: true },
        { label: '\u2191 Mover para cima', action: () => _navMoveRelative(state, elId, -1) },
        { label: '\u2193 Mover para baixo', action: () => _navMoveRelative(state, elId, 1) },
        { sep: true },
        { label: '\u29C9 Copiar', action: () => { const els = state._lastElements || []; const find = (list) => { for (const e of list) { if (e.id === elId) return e; if (e.children) { const f = find(e.children); if (f) return f; } } return null; }; const found = find(els); if (found) { state._clipboard = JSON.parse(JSON.stringify(found)); state.showToast('Elemento copiado', 'success'); } } },
        { label: '\uD83D\uDCCB Colar (ap\u00F3s)', action: () => _navPasteAfter(state, elId) },
        { sep: true },
        { label: '\uD83C\uDFA8 Copiar Estilos', action: () => { state.onSelectElement(elId); setTimeout(() => { state._styleClipboard = JSON.parse(JSON.stringify(state.cachedStyles || {})); state.showToast('Estilos copiados!', 'success'); }, 100); } },
        { label: '\uD83C\uDFA8 Colar Estilos', action: () => { state.onSelectElement(elId); setTimeout(() => { if (!state._styleClipboard) { state.showToast('Nenhum estilo copiado', 'error'); return; } const styles = state._styleClipboard; let applied = 0; const keys = Object.keys(styles); const applyNext = (idx) => { if (idx >= keys.length) { if (applied > 0) { state.showToast(`${applied} estilo(s) aplicado(s)!`, 'success'); state.loadElements(); setTimeout(() => state.onSelectElement(elId), 200); } return; } const k = keys[idx]; const v = styles[k]; if (v !== undefined && v !== '' && v !== null) { applied++; fetch(`/page-builder/elements/${elId}/styles`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf }, body: JSON.stringify({ styles: { [k]: v } }) }).then(() => applyNext(idx + 1)).catch(() => applyNext(idx + 1)); } else { applyNext(idx + 1); } }; applyNext(0); }, 100); } },
        { sep: true },
        { label: '\uD83C\uDF10 Salvar como Global Widget', action: () => { state.onSelectElement(elId); setTimeout(() => { if (typeof saveAsGlobalWidget === 'function') saveAsGlobalWidget(); else if (window.editor && window.editor.saveAsGlobalWidget) window.editor.saveAsGlobalWidget(); }, 50); } },
        { label: '\uD83D\uDD04 Sincronizar Global Widget', action: () => { if (typeof syncGlobalWidgets === 'function') syncGlobalWidgets(); else if (window.editor && window.editor.syncGlobalWidgets) window.editor.syncGlobalWidgets(); } },
        { sep: true },
        { label: '\u2715 Excluir', cls: 'danger', action: () => state.deleteElement(elId) },
    ];

    items.forEach(m => {
        if (m.sep) {
            const sep = document.createElement('div');
            sep.className = 'pb-canvas-context-sep';
            ctx.appendChild(sep);
            return;
        }
        const btn = document.createElement('div');
        btn.className = 'pb-canvas-context-item' + (m.cls ? ' ' + m.cls : '');
        btn.textContent = m.label;
        btn.onclick = (e) => { e.stopPropagation(); _hideCanvasContext(); m.action(); };
        ctx.appendChild(btn);
    });

    document.body.appendChild(ctx);
    const rect = ctx.getBoundingClientRect();
    if (rect.right > window.innerWidth) ctx.style.left = (x - rect.width) + 'px';
    if (rect.bottom > window.innerHeight) ctx.style.top = (y - rect.height) + 'px';
    const closeCtx = (e) => {
        if (!ctx.contains(e.target)) { _hideCanvasContext(); document.removeEventListener('click', closeCtx); }
    };
    setTimeout(() => document.addEventListener('click', closeCtx), 10);
}

export function _hideCanvasContext() {
    document.querySelectorAll('.pb-canvas-context').forEach(c => c.remove());
}

function findInTree(list, id) {
    for (const el of list) {
        if (el.id === id) return el;
        if (el.children) {
            const found = findInTree(el.children, id);
            if (found) return found;
        }
    }
    return null;
}

function findParentList(list, id) {
    for (const el of list) {
        if (el.id === id) return list;
        if (el.children) {
            const found = findParentList(el.children, id);
            if (found) return found;
        }
    }
    return null;
}

export function _navMoveElement(state, dragId, newParentId) {
    const els = state._lastElements || [];
    const dragEl = findInTree(els, dragId);
    if (!dragEl) return;

    const dragParent = findParentList(els, dragId);
    if (dragParent) {
        const idx = dragParent.findIndex(e => e.id === dragId);
        dragParent.splice(idx, 1);
    }

    if (newParentId) {
        const targetEl = findInTree(els, newParentId);
        if (targetEl && targetEl.children) {
            targetEl.children.push(dragEl);
        }
    } else {
        els.push(dragEl);
    }

    state.renderCanvas(state, els);
    state.renderStructure(els);
    renderNavigator(state);
    state.renderMath();
    state._saveElementOrder();
}

function _navReorderElement(state, dragId, targetParentId, beforeId) {
    const els = state._lastElements || [];
    const dragEl = findInTree(els, dragId);
    if (!dragEl) return;

    const dragParent = findParentList(els, dragId);
    if (dragParent) {
        const idx = dragParent.findIndex(e => e.id === dragId);
        dragParent.splice(idx, 1);
    }

    if (beforeId) {
        const insertParent = findParentList(els, beforeId);
        if (insertParent) {
            const idx = insertParent.findIndex(e => e.id === beforeId);
            insertParent.splice(idx, 0, dragEl);
        }
    } else if (targetParentId) {
        const targetEl = findInTree(els, targetParentId);
        if (targetEl && targetEl.children) {
            targetEl.children.push(dragEl);
        }
    } else {
        els.push(dragEl);
    }

    state.renderCanvas(state, els);
    state.renderStructure(els);
    renderNavigator(state);
    state.renderMath();
    state._saveElementOrder();
}

export function _navMoveRelative(state, elId, direction) {
    const els = state._lastElements || [];
    const findAndMove = (list) => {
        const idx = list.findIndex(e => e.id === elId);
        if (idx !== -1) {
            const newIdx = idx + direction;
            if (newIdx >= 0 && newIdx < list.length) {
                [list[idx], list[newIdx]] = [list[newIdx], list[idx]];
                return true;
            }
            return false;
        }
        for (const el of list) {
            if (el.children && findAndMove(el.children)) return true;
        }
        return false;
    };

    if (findAndMove(els)) {
        state.renderCanvas(state, els);
        state.renderStructure(els);
        renderNavigator(state);
        state.renderMath();
        state._saveElementOrder();
    }
}

export function _navPasteAfter(state, targetId) {
    if (!state._clipboard) return;
    const els = state._lastElements || [];
    const clone = JSON.parse(JSON.stringify(state._clipboard));
    delete clone.id;

    const findAndInsert = (list) => {
        const idx = list.findIndex(e => e.id === targetId);
        if (idx !== -1) {
            list.splice(idx + 1, 0, clone);
            return true;
        }
        for (const el of list) {
            if (el.children && findAndInsert(el.children)) return true;
        }
        return false;
    };

    if (findAndInsert(els)) {
        state.renderCanvas(state, els);
        state.renderStructure(els);
        renderNavigator(state);
        state.renderMath();
        state._saveElementOrder();
    }
}
