import { toastError, apiFetch } from './utils.js';

function clearDropIndicators() {
    document.querySelectorAll('.drop-over, .drop-before, .drop-after, .drag-over').forEach(el => {
        el.classList.remove('drop-over', 'drop-before', 'drop-after', 'drag-over');
    });
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

export function bindDragDrop(state) {
    document.querySelectorAll('.pb-widget-item[draggable]').forEach(w => {
        w.addEventListener('dragstart', e => {
            e.dataTransfer.setData('text/plain', w.dataset.type);
            e.dataTransfer.effectAllowed = 'copy';
            w.classList.add('dragging');
            const ghost = document.createElement('div');
            ghost.className = 'pb-drag-ghost';
            ghost.textContent = w.dataset.type;
            ghost.id = 'drag-ghost';
            document.body.appendChild(ghost);
            e.dataTransfer.setDragImage(ghost, 0, 0);
        });
        w.addEventListener('dragend', () => {
            w.classList.remove('dragging');
            const ghost = document.getElementById('drag-ghost');
            if (ghost) ghost.remove();
            clearDropIndicators();
        });
    });
}

export function bindCanvasDrops(state) {
    const dz = document.getElementById('canvas-dropzone');
    const emptyCanvas = document.getElementById('empty-canvas');

    dz.addEventListener('dragover', e => {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        clearDropIndicators();
        const target = e.target.closest('.pb-el');
        if (target) {
            if (target.dataset.isContainer === 'true') {
                target.classList.add('drop-over');
            } else {
                const rect = target.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                target.classList.add(e.clientY < midY ? 'drop-before' : 'drop-after');
            }
        }
        if (emptyCanvas) emptyCanvas.classList.add('drag-over');
    });

    dz.addEventListener('dragleave', e => {
        const target = e.target.closest('.pb-el');
        if (target) target.classList.remove('drop-over', 'drop-before', 'drop-after');
        if (emptyCanvas) emptyCanvas.classList.remove('drag-over');
    });

    dz.addEventListener('drop', e => {
        e.preventDefault();
        e.stopPropagation();
        clearDropIndicators();
        if (emptyCanvas) emptyCanvas.classList.remove('drag-over');

        const data = e.dataTransfer.getData('text/plain');
        if (!data) return;

        if (/^\d+$/.test(data)) {
            _handleElementDrop(state, parseInt(data), e);
            return;
        }

        let parentId = null;
        let insertBeforeId = null;
        const target = e.target.closest('.pb-el');
        if (target) {
            if (target.dataset.isContainer === 'true') {
                parentId = target.dataset.elId;
            } else {
                const rect = target.getBoundingClientRect();
                const midY = rect.top + rect.height / 2;
                const isAbove = e.clientY < midY;
                const parentEl = target.parentElement ? target.parentElement.closest('.pb-el') : null;
                if (parentEl && parentEl.dataset.isContainer === 'true') {
                    parentId = parentEl.dataset.elId;
                }
                insertBeforeId = isAbove ? target.dataset.elId : null;
                if (!isAbove) {
                    const nextSibling = target.nextElementSibling;
                    if (nextSibling && nextSibling.dataset && nextSibling.dataset.elId) {
                        insertBeforeId = null;
                    }
                }
            }
        }

        state.showToast('Adicionando ' + data + '...', 'info');
        apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
            body: JSON.stringify({ type: data, parent_id: parentId ? parseInt(parentId) : null }),
        })
        .then(() => state.loadElements())
        .catch(err => toastError('Falha ao adicionar elemento: ' + (err.message || err)));
    });

    dz.addEventListener('contextmenu', e => {
        const el = e.target.closest('.pb-el');
        if (!el) return;
        e.preventDefault();
        e.stopPropagation();
        const elId = parseInt(el.dataset.elId);
        if (!elId) return;
        state.onSelectElement(elId);
        state.showCanvasContext(e.clientX, e.clientY, elId);
    });
}

export function _handleElementDrop(state, dragId, e) {
    const target = e.target.closest('.pb-el');
    if (!target || parseInt(target.dataset.elId) === dragId) return;

    const targetId = parseInt(target.dataset.elId);
    const rect = target.getBoundingClientRect();
    const midY = rect.top + rect.height / 2;
    const insertBefore = e.clientY < midY;

    const els = state._lastElements || [];
    const dragEl = findInTree(els, dragId);
    if (!dragEl) return;

    const parentList = findParentList(els, dragId);
    if (parentList) {
        const idx = parentList.findIndex(e => e.id === dragId);
        parentList.splice(idx, 1);
    }

    const siblings = findParentList(els, targetId) || els;
    const idx = siblings.findIndex(e => e.id === targetId);
    if (insertBefore) siblings.splice(idx, 0, dragEl);
    else siblings.splice(idx + 1, 0, dragEl);

    state.renderCanvas(state, els);
    state.renderStructure(els);
    state.renderNavigator(state);
    state.renderMath();
    _saveElementOrder(state);
}

export function _saveElementOrder(state) {
    const buildOrder = (elements) => {
        return elements.map(el => ({
            id: el.id,
            children: el.children ? buildOrder(el.children) : [],
        }));
    };
    const order = buildOrder(state._lastElements || []);
    apiFetch(`/page-builder/pages/${state.pageId}/elements/reorder`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ order }),
    }).catch(() => toastError('Falha ao reordenar'));
}
