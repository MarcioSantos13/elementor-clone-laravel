import state from './state.js';
import { escHtml, apiFetch, showToast, toastError } from './utils.js';

export function collabJoin() {
    apiFetch(`/page-builder/pages/${state.pageId}/collab/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
    }).catch(() => {});
}

export function collabLeave() {
    if (!state.pageId) return;
    apiFetch(`/page-builder/pages/${state.pageId}/collab/leave`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
    }).catch(() => {});
}

export function collabHeartbeat() {
    setInterval(() => {
        if (!state.pageId) return;
        apiFetch(`/page-builder/pages/${state.pageId}/collab/heartbeat`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
            body: JSON.stringify({ cursor_position: state.selectedId ? { element_id: state.selectedId } : null }),
        }).then(data => {
            if (data && data.active_users) renderCollabUsers(data.active_users);
        }).catch(() => {});
    }, 15000);
}

function renderCollabUsers(users) {
    let bar = document.getElementById('collab-users-bar');
    if (!bar) {
        bar = document.createElement('div');
        bar.id = 'collab-users-bar';
        bar.style.cssText = 'display:flex;gap:4px;align-items:center;margin-left:auto;padding:0 8px';
        const toolbar = document.querySelector('.pb-toolbar-right');
        if (toolbar) toolbar.appendChild(bar);
    }
    const myId = window._pageData?.user_id;
    bar.innerHTML = users.filter(u => u.user_id !== myId).map(u =>
        `<div title="${escHtml(u.name)}" style="width:28px;height:28px;border-radius:50%;background:${u.color};color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:600;border:2px solid #fff;margin-left:-6px;cursor:default">${escHtml(u.name.charAt(0).toUpperCase())}</div>`
    ).join('');
}

export function bindCollabPresence() {
    window.addEventListener('beforeunload', () => collabLeave());
}

export function lockElement(elementId) {
    return apiFetch(`/page-builder/pages/${state.pageId}/elements/${elementId}/lock`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
    }).catch(err => {
        if (err.message && err.message.includes('being edited')) {
            showToast(err.message, 'error');
        }
        throw err;
    });
}

export function unlockElement(elementId) {
    return apiFetch(`/page-builder/pages/${state.pageId}/elements/${elementId}/unlock`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
    }).catch(() => {});
}
