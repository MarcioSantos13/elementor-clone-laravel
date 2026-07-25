import Sortable from 'sortablejs';
import { toastError, apiFetch } from './utils.js';

let _widgetPanelSortable = null;
let _canvasSortable = null;
const _containerSortables = new Map();

export function bindDragDrop(state) {
    _bindWidgetPanel(state);
    _bindCanvasDropzone(state);
}

function _bindWidgetPanel(state) {
    const panelBody = document.getElementById('panel-widgets');
    if (!panelBody) return;

    const widgetItems = panelBody.querySelectorAll('.pb-widget-grid');
    widgetItems.forEach(grid => {
        Sortable.create(grid, {
            group: {
                name: 'widgets',
                pull: 'clone',
                put: false,
            },
            sort: false,
            draggable: '.pb-widget-item',
            ghostClass: 'pb-widget-ghost',
            chosenClass: 'pb-widget-chosen',
            dragClass: 'pb-widget-dragging',
            animation: 150,
            filter: '.pb-widget-group-title',
            onStart(evt) {
                document.body.classList.add('pb-is-dragging');
                const item = evt.item;
                item.dataset._origParent = '';
            },
            onEnd(evt) {
                document.body.classList.remove('pb-is-dragging');
            },
        });
    });
}

function _bindCanvasDropzone(state) {
    const dz = document.getElementById('canvas-dropzone');
    if (!dz) return;

    if (_canvasSortable) _canvasSortable.destroy();

    _canvasSortable = Sortable.create(dz, {
        group: {
            name: 'canvas',
            pull: false,
            put: ['widgets'],
        },
        animation: 200,
        ghostClass: 'pb-sortable-ghost',
        chosenClass: 'pb-sortable-chosen',
        dragClass: 'pb-sortable-drag',
        handle: '.pb-el-drag',
        draggable: '.pb-el',
        emptyInsertThreshold: 60,
        onAdd(evt) {
            _handleNewWidgetDrop(state, evt);
        },
        onUpdate(evt) {
            _handleCanvasReorder(state, evt);
        },
    });
}

function _handleNewWidgetDrop(state, evt) {
    const item = evt.item;
    const type = item.dataset.type;

    if (item.parentNode) {
        item.parentNode.removeChild(item);
    }

    if (!type) return;

    let parentId = null;
    let insertBeforeId = null;

    const targetContainer = evt.to;
    const parentEl = targetContainer.closest('.pb-el[data-is-container="true"]');
    if (parentEl) {
        parentId = parseInt(parentEl.dataset.elId);
    }

    const siblings = Array.from(targetContainer.children).filter(
        el => el.classList.contains('pb-el') && el.dataset.elId
    );
    const newIndex = evt.newIndex;
    if (newIndex < siblings.length) {
        insertBeforeId = parseInt(siblings[newIndex].dataset.elId);
    }

    state.showToast('Adicionando ' + type + '...', 'info');

    apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({
            type: type,
            parent_id: parentId,
            insert_before_id: insertBeforeId,
        }),
    })
    .then(() => state.loadElements())
    .catch(err => toastError('Falha ao adicionar elemento: ' + (err.message || err)));
}

function _handleCanvasReorder(state, evt) {
    const el = evt.item;
    const elId = parseInt(el.dataset.elId);
    if (!elId) return;

    state.loadElements();
}

export function initContainerSortables(state) {
    destroyAllContainerSortables();

    const dz = document.getElementById('canvas-dropzone');
    if (dz) {
        _initSortableForContainer(dz, state, null);
    }

    document.querySelectorAll('.pb-el[data-is-container="true"] .pb-el-children').forEach(childContainer => {
        const parentEl = childContainer.closest('.pb-el[data-is-container="true"]');
        if (parentEl) {
            const parentId = parseInt(parentEl.dataset.elId);
            _initSortableForContainer(childContainer, state, parentId);
        }
    });
}

function _initSortableForContainer(container, state, parentId) {
    if (_containerSortables.has(container)) {
        _containerSortables.get(container).destroy();
    }

    const sortable = Sortable.create(container, {
        group: {
            name: 'canvas',
            pull: false,
            put: ['widgets', 'canvas'],
        },
        animation: 200,
        ghostClass: 'pb-sortable-ghost',
        chosenClass: 'pb-sortable-chosen',
        dragClass: 'pb-sortable-drag',
        handle: '.pb-el-drag',
        draggable: '.pb-el',
        emptyInsertThreshold: 40,
        onAdd(evt) {
            _handleNewWidgetDrop(state, evt);
        },
        onUpdate(evt) {
            _handleCanvasReorder(state, evt);
        },
    });

    _containerSortables.set(container, sortable);
}

export function destroyAllContainerSortables() {
    _containerSortables.forEach(s => s.destroy());
    _containerSortables.clear();
}

export function refreshSortables(state) {
    initContainerSortables(state);
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
