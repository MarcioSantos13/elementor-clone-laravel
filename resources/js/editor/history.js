import { toastError, apiFetch } from './utils.js';

export function _findElement(elements, id) {
    for (const el of elements) {
        if (el.id == id) return el;
        if (el.children) {
            const found = _findElement(el.children, id);
            if (found) return found;
        }
    }
    return null;
}

export function pushHistory(state, elements) {
    state.historyIndex++;
    state.history = state.history.slice(0, state.historyIndex);
    state.history.push(JSON.parse(JSON.stringify(elements || [])));
    updateUndoButtons(state);
}

export function snapshotHistory(state) {
    apiFetch(`/page-builder/pages/${state.pageId}/elements`)
        .then(data => pushHistory(state, data.elements))
        .catch(() => {});
}

export function undo(state) {
    if (state.historyIndex <= 0) return;
    state.historyIndex--;
    restoreHistory(state);
}

export function redo(state) {
    if (state.historyIndex >= state.history.length - 1) return;
    state.historyIndex++;
    restoreHistory(state);
}

export function restoreHistory(state) {
    const snapshot = state.history[state.historyIndex];
    state._lastElements = snapshot || [];
    state.renderCanvas(snapshot);
    state.renderMath();
    state.renderStructure(snapshot);
    state.renderNavigator();
    updateUndoButtons(state);

    apiFetch(`/page-builder/pages/${state.pageId}/elements/restore-snapshot`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ elements: snapshot }),
    }).catch(() => {});

    if (state.selectedId) {
        const found = _findElement(snapshot, state.selectedId);
        if (found) {
            state.loadControls(state.selectedId);
        } else {
            state.selectedId = null;
            document.getElementById('settings-empty').style.display = '';
            document.getElementById('settings-form').style.display = 'none';
        }
    }
}

export function updateUndoButtons(state) {
    const undoBtn = document.getElementById('pb-undo');
    const redoBtn = document.getElementById('pb-redo');
    if (undoBtn) undoBtn.style.opacity = state.historyIndex > 0 ? '1' : '.4';
    if (redoBtn) redoBtn.style.opacity = state.historyIndex < state.history.length - 1 ? '1' : '.4';
}
