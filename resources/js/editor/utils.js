export function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

export function apiFetch(url, options = {}) {
    const headers = Object.assign({ 'Accept': 'application/json' }, options.headers || {});
    return fetch(url, Object.assign({}, options, { headers, credentials: 'same-origin' })).then(r => {
        if (r.redirected) {
            throw new Error('Sessao expirada. Recarregue a pagina.');
        }
        if (!r.ok) {
            return r.json().catch(() => r.text()).then(body => {
                const msg = typeof body === 'string' ? `HTTP ${r.status}` : (body.message || (body.errors ? Object.values(body.errors).flat().join(', ') : `HTTP ${r.status}`));
                throw new Error(msg);
            });
        }
        return r.json();
    });
}

export function showToast(msg, type) {
    const existing = document.querySelectorAll('.pb-toast');
    existing.forEach(t => { t.classList.add('pb-toast-out'); setTimeout(() => t.remove(), 300); });
    const t = document.createElement('div');
    t.className = 'pb-toast';
    if (type === 'error') t.style.borderColor = 'rgba(239,68,68,.4)';
    if (type === 'success') t.style.borderColor = 'rgba(34,197,94,.4)';
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => { t.classList.add('pb-toast-out'); setTimeout(() => t.remove(), 300); }, 2500);
}

export function toastError(msg) {
    showToast('\u26A0\uFE0F ' + msg, 'error');
}

export function toastSuccess(msg) {
    showToast('\u2705 ' + msg, 'success');
}

export function structureIcon(type) {
    const icons = { section: '&#9638;', column: '&#9646;', heading: 'H', text: 'T', image: '&#128247;', button: '&#128206;', callout: '&#9888;', table: '&#9638;', math: '&Sigma;' };
    return icons[type] || '&#9679;';
}
