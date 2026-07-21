import state from './state.js';
import { escHtml, showToast, toastError, toastSuccess, structureIcon, apiFetch } from './utils.js';
import { renderCanvas, renderMath, elementHtml, renderStructure } from './canvas.js';
import { pushHistory, snapshotHistory, undo, redo, updateUndoButtons, _findElement } from './history.js';
import { bindDragDrop, bindCanvasDrops, _handleElementDrop, _saveElementOrder } from './dragdrop.js';
import { openHtmlImportModal } from './html-import.js';
import { toggleNavigator, renderNavigator as renderNav, _showNavContext, _hideNavContext, _showCanvasContext, _hideCanvasContext, _navMoveElement, _navMoveRelative, _navPasteAfter, _startNavRename } from './navigator.js';

function renderStructureWithSelect(elements, parentUl) {
    const ul = parentUl || document.getElementById('structure-tree');
    if (!parentUl) ul.innerHTML = '';
    (elements || []).forEach(el => {
        const li = document.createElement('li');
        li.className = 'pb-structure-item' + (state.selectedId === el.id ? ' active' : '');
        li.dataset.elId = el.id;
        li.innerHTML = `<span class="si-icon">${structureIcon(el.type)}</span><span>${el.name || el.type}</span><span class="si-type">${el.type}</span>`;
        li.onclick = (e) => { e.stopPropagation(); selectElement(el.id); };
        ul.appendChild(li);
        if (el.children && el.children.length > 0) {
            const childUl = document.createElement('ul');
            childUl.className = 'pb-structure-children';
            li.appendChild(childUl);
            renderStructureWithSelect(el.children, childUl);
        }
    });
}

const editor = {
    init(pageId, csrfToken) {
        state.pageId = pageId;
        state.csrf = csrfToken;

        state.renderCanvas = (els) => renderCanvas(state, els);
        state.renderMath = () => renderMath();
        state.renderStructure = (els) => renderStructureWithSelect(els);
        state.renderNavigator = (s) => renderNav(s || state);
        state.onSelectElement = (id) => selectElement(id);
        state.loadControls = (id) => loadControls(id);
        state.showToast = (msg, type) => showToast(msg, type);
        state.toastError = (msg) => toastError(msg);
        state.loadElements = () => loadElements();
        state.duplicateElement = (id) => duplicateElement(id);
        state.deleteElement = (id) => deleteElement(id);
        state.showCanvasContext = (x, y, elId) => _showCanvasContext(state, x, y, elId);
        state._saveElementOrder = () => _saveElementOrder(state);

        loadElements();
        loadPageData();
        bindDragDrop(state);
        bindCanvasDrops(state);
        bindKeyboard();
        bindInlineEditing();
        bindZoom();
        autoSave();
        observeCanvas();
    },

    undo() { undo(state); },
    redo() { redo(state); },
    save(silent) { save(silent); },
    publish() { publish(); },
    setResponsive(mode) { setResponsive(mode); },
    switchTab(tab) { switchTab(tab); },
    switchEditorTab(tab) { switchEditorTab(tab); },
    zoomIn() { setZoom(state.zoomLevel + 10); },
    zoomOut() { setZoom(state.zoomLevel - 10); },
    zoomReset() { setZoom(100); },
    toggleFullscreen() { toggleFullscreen(); },
    toggleNavigator() { toggleNavigator(state); },
    selectElement(id) { selectElement(id); },
    duplicateElement(id) { duplicateElement(id); },
    deleteElement(id) { deleteElement(id); },
    deleteSelected() { if (state.selectedId) deleteElement(state.selectedId); },
    showPageSettings() { showPageSettings(); },
    hidePageSettings() { hidePageSettings(); },
    exportPage() { window.open('/page-builder/pages/' + state.pageId + '/export', '_blank'); },
    copyHtml() { copyHtml(); },
    importHtml() { openHtmlImportModal(state.csrf); },
};

function selectElement(id) {
    state.selectedId = id;
    document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
    if (el) el.classList.add('selected');
    const si = document.querySelector(`.pb-structure-item[data-el-id="${id}"]`);
    if (si) si.classList.add('active');
    loadControls(id);
}

function loadElements() {
    apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
        headers: { 'X-CSRF-TOKEN': state.csrf },
    })
        .then(data => {
            const prevSelected = state.selectedId;
            state._lastElements = data.elements || [];
            renderCanvas(state, state._lastElements);
            renderMath();
            renderStructureWithSelect(state._lastElements);
            pushHistory(state, state._lastElements);
            renderNav(state);
            if (prevSelected && document.querySelector(`.pb-el[data-el-id="${prevSelected}"]`)) {
                selectElement(prevSelected);
            }
        })
        .catch(err => toastError('Falha ao carregar elementos: ' + (err.message || err)));
}

function loadPageData() {
    showToast('Carregando dados da pagina...', 'info');
    apiFetch(`/page-builder/pages/${state.pageId}/data`)
        .then(data => { window._pageData = data.page; })
        .catch(() => toastError('Falha ao carregar dados da pagina'));
}

function loadControls(id) {
    apiFetch(`/page-builder/elements/${id}/controls`)
        .then(data => {
            if (data.error) { console.error('Controls error:', data.error); return; }
            const widget = data.widget;
            const element = data.element;
            document.getElementById('settings-empty').style.display = 'none';
            document.getElementById('settings-form').classList.add('active');
            document.getElementById('settings-title').textContent = element.name || widget.label;
            document.getElementById('settings-type').textContent = widget.type;
            state.cachedControls = widget.controls || {};
            state.cachedSettings = element.settings || {};
            state.cachedStyles = element.styles || {};
            state.cachedElementId = id;
            state.activeTab = 'content';
            syncEditorTabs();
            renderControls();
            renderMath();
        })
        .catch(err => { console.error('loadControls failed:', err); toastError('Falha ao carregar controles: ' + (err.message || err)); });
}

function renderControls() {
    const body = document.getElementById('settings-body');
    body.innerHTML = '';
    const controls = state.cachedControls || {};
    const settings = state.cachedSettings || {};
    const styles = state.cachedStyles || {};
    const elementId = state.cachedElementId;
    const tab = state.activeTab;
    const filtered = {};
    for (const [key, ctrl] of Object.entries(controls)) {
        const ctrlTab = ctrl.tab || 'content';
        if (ctrlTab === tab) filtered[key] = ctrl;
    }
    const sections = groupControls(filtered);
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
            let val;
            if (tab === 'style') {
                val = styles[key] !== undefined ? styles[key] : (ctrl.default !== undefined ? ctrl.default : '');
            } else if (tab === 'advanced') {
                val = styles[key] !== undefined ? styles[key] : (settings[key] !== undefined ? settings[key] : (ctrl.default !== undefined ? ctrl.default : ''));
            } else {
                val = settings[key] !== undefined ? settings[key] : (ctrl.default !== undefined ? ctrl.default : '');
            }
            const control = document.createElement('div');
            control.className = 'pb-control';
            const label = document.createElement('label');
            label.textContent = ctrl.label || key;
            label.htmlFor = `ctrl-${key}`;
            control.appendChild(label);
            control.appendChild(createInput(key, ctrl, val, elementId));
            secDiv.appendChild(control);
        });
        body.appendChild(secDiv);
    }
}

function switchEditorTab(tab) {
    state.activeTab = tab;
    syncEditorTabs();
    renderControls();
}

function syncEditorTabs() {
    const tab = state.activeTab;
    document.querySelectorAll('#editor-tabs .pb-editor-tab').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.etab === tab);
    });
}

function groupControls(controls) {
    const sections = { '_default': [] };
    for (const [key, ctrl] of Object.entries(controls)) {
        const section = ctrl.section || '_default';
        if (!sections[section]) sections[section] = [];
        sections[section].push([key, ctrl]);
    }
    return sections;
}

function _debouncedSetting(key, elementId, fn) {
    const id = `s_${key}_${elementId}`;
    clearTimeout(state._timers[id]);
    state._timers[id] = setTimeout(fn, 300);
}

function _debouncedStyle(key, elementId, fn) {
    const id = `st_${key}_${elementId}`;
    clearTimeout(state._timers[id]);
    state._timers[id] = setTimeout(fn, 300);
}

function createInput(key, ctrl, value, elementId) {
    const isStyle = ctrl.tab === 'style';
    const saveFn = (k, v) => isStyle ? updateStyle(k, v, elementId) : updateSetting(k, v, elementId);
    const debouncedSave = (k, fn) => isStyle ? _debouncedStyle(k, elementId, fn) : _debouncedSetting(k, elementId, fn);

    const types = {
        text: () => {
            const inp = document.createElement('input');
            inp.type = 'text'; inp.id = `ctrl-${key}`; inp.value = value || '';
            inp.spellcheck = false;
            inp.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
            return inp;
        },
        number: () => {
            const inp = document.createElement('input');
            inp.type = 'number'; inp.id = `ctrl-${key}`; inp.value = value;
            if (ctrl.min !== undefined) inp.min = ctrl.min;
            if (ctrl.max !== undefined) inp.max = ctrl.max;
            inp.oninput = (e) => debouncedSave(key, () => saveFn(key, parseFloat(e.target.value) || 0));
            return inp;
        },
        textarea: () => {
            const ta = document.createElement('textarea');
            ta.id = `ctrl-${key}`; ta.value = typeof value === 'string' ? value : '';
            ta.spellcheck = false;
            ta.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
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
            sel.onchange = (e) => saveFn(key, e.target.value);
            return sel;
        },
        color: () => {
            const container = document.createElement('div');
            container.style.cssText = 'display:flex;gap:.5rem;align-items:center';
            const inp = document.createElement('input');
            inp.type = 'color'; inp.id = `ctrl-${key}`; inp.value = value || '#000000';
            const txt = document.createElement('input');
            txt.type = 'text'; txt.value = value || '#000000';
            txt.placeholder = '#000000';
            txt.style.cssText = 'flex:1';
            const update = (v) => { inp.value = v; txt.value = v; saveFn(key, v); };
            inp.oninput = (e) => debouncedSave(key, () => update(e.target.value));
            txt.oninput = (e) => { if (/^#[0-9a-f]{3,8}$/i.test(e.target.value)) debouncedSave(key, () => update(e.target.value)); };
            container.appendChild(inp);
            container.appendChild(txt);
            return container;
        },
        boolean: () => {
            const container = document.createElement('div');
            container.style.cssText = 'display:flex;align-items:center;gap:.5rem';
            const cb = document.createElement('input');
            cb.type = 'checkbox'; cb.id = `ctrl-${key}`; cb.checked = !!value;
            cb.onchange = (e) => saveFn(key, e.target.checked);
            container.appendChild(cb);
            return container;
        },
        url: () => {
            const inp = document.createElement('input');
            inp.type = 'url'; inp.id = `ctrl-${key}`; inp.value = value || '';
            inp.placeholder = 'https://...';
            inp.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
            return inp;
        },
        image: () => createImageInput(key, value, saveFn, elementId),
        wysiwyg: () => createWysiwygInput(key, value, saveFn, elementId),
        icon: () => createIconInput(key, value, saveFn, debouncedSave),
        gallery: () => createGalleryInput(key, value, saveFn),
        repeater: () => createRepeaterInput(key, value, ctrl, saveFn),
        typography: () => createTypographyInput(key, value, elementId),
        background: () => createBackgroundInput(key, value, elementId),
        border: () => createBorderInput(key, value, elementId),
        box_shadow: () => createBoxShadowInput(key, value, elementId),
        dimensions: () => createDimensionsInput(key, value, elementId),
        hover: () => createHoverInput(key, value, elementId),
        custom_css: () => {
            const ta = document.createElement('textarea');
            ta.id = `ctrl-${key}`;
            ta.value = typeof value === 'string' ? value : '';
            ta.placeholder = 'Ex: color: red !important;\nbackground: #fff;';
            ta.spellcheck = false;
            ta.style.cssText = 'width:100%;padding:.45rem .6rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:.78rem;min-height:80px;font-family:"SF Mono",Menlo,Monaco,Consolas,monospace;resize:vertical;box-sizing:border-box';
            ta.oninput = (e) => debouncedSave(key, () => saveFn(key, e.target.value));
            return ta;
        },
        animation: () => createAnimationInput(key, value, saveFn),
        visibility: () => createVisibilityInput(key, value, saveFn),
    };
    return (types[ctrl.type] || types.text)();
}

function createImageInput(key, value, saveFn, elementId) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    const currentUrl = value && value.url ? value.url : '';
    const dropZone = document.createElement('div');
    dropZone.style.cssText = 'border:2px dashed var(--pb-border);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;transition:all .2s;background:var(--pb-bg);position:relative';
    dropZone.innerHTML = `<div style="font-size:1.5rem;margin-bottom:.35rem;opacity:.5">&#128247;</div><div style="font-size:.72rem;color:var(--pb-text2)"><strong style="color:var(--pb-accent);cursor:pointer">Clique para selecionar</strong><br>ou arraste uma imagem aqui</div><div style="font-size:.65rem;color:var(--pb-text2);margin-top:.3rem">ou cole (Ctrl+V)</div>`;
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
    fileInput.style.display = 'none';
    const preview = document.createElement('div');
    preview.style.cssText = 'border-radius:6px;overflow:hidden;background:var(--pb-bg);min-height:50px;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--pb-text2);border:1px solid var(--pb-border)';
    const urlRow = document.createElement('div');
    urlRow.style.cssText = 'display:flex;gap:.35rem';
    const urlInput = document.createElement('input');
    urlInput.type = 'url'; urlInput.placeholder = 'Ou digite URL...';
    urlInput.value = currentUrl;
    urlInput.style.cssText = 'flex:1;padding:.4rem .55rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:.75rem';
    const updatePreview = (url) => {
        if (url) preview.innerHTML = `<img src="${escHtml(url)}" style="width:100%;max-height:100px;object-fit:contain;border-radius:4px">`;
        else preview.textContent = 'Nenhuma imagem';
    };
    const updateSetting = (url) => {
        const alt = (value && value.alt) || '';
        const w = (value && value.width) || 800;
        const h = (value && value.height) || 600;
        saveFn(key, { url, alt, width: w, height: h });
        updatePreview(url);
    };
    if (currentUrl) updatePreview(currentUrl);
    dropZone.appendChild(fileInput);
    dropZone.onclick = () => fileInput.click();
    fileInput.onchange = () => {
        const file = fileInput.files[0];
        if (!file) return;
        uploadImageFile(file, (url) => { updateSetting(url); urlInput.value = url; });
    };
    dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--pb-accent)'; dropZone.style.background = 'var(--pb-primary-light)'; };
    dropZone.ondragleave = () => { dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)'; };
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) uploadImageFile(file, (url) => { updateSetting(url); urlInput.value = url; });
    };
    urlInput.onchange = () => updateSetting(urlInput.value);
    urlRow.appendChild(urlInput);
    container.appendChild(dropZone);
    container.appendChild(preview);
    container.appendChild(urlRow);
    return container;
}

function createWysiwygInput(key, value, saveFn, elementId) {
    const wrap = document.createElement('div');
    wrap.style.cssText = 'display:flex;flex-direction:column;border:1px solid var(--pb-border);border-radius:6px;overflow:hidden;background:var(--pb-bg)';
    const toolbar = document.createElement('div');
    toolbar.style.cssText = 'display:flex;flex-wrap:wrap;gap:2px;padding:4px 6px;background:var(--pb-surface2);border-bottom:1px solid var(--pb-border)';
    const content = document.createElement('div');
    content.contentEditable = 'true';
    content.id = `ctrl-${key}`;
    content.innerHTML = typeof value === 'string' ? value : '<p></p>';
    content.style.cssText = 'min-height:120px;max-height:400px;overflow-y:auto;padding:8px 10px;font-size:13px;line-height:1.6;color:var(--pb-text);outline:none';
    content.innerHTML = content.innerHTML || '<p></p>';

    const execCmd = (cmd, val) => { content.focus(); document.execCommand(cmd, false, val || null); };
    const makeBtn = (label, title, cmd, val) => {
        const b = document.createElement('button');
        b.type = 'button'; b.innerHTML = label; b.title = title;
        b.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:13px;font-weight:600';
        b.onmouseenter = () => { b.style.background = 'var(--pb-border)'; };
        b.onmouseleave = () => { b.style.background = 'transparent'; b.style.borderColor = 'transparent'; };
        b.onmousedown = (e) => { e.preventDefault(); execCmd(cmd, val); };
        return b;
    };

    toolbar.appendChild(makeBtn('B', 'Negrito', 'bold'));
    toolbar.appendChild(makeBtn('I', 'Italico', 'italic'));
    toolbar.appendChild(makeBtn('U', 'Sublinhado', 'underline'));
    toolbar.appendChild(makeBtn('S', 'Tachado', 'strikeThrough'));
    const sep = document.createElement('span'); sep.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px'; toolbar.appendChild(sep);
    toolbar.appendChild(makeBtn('&#9650;', 'Titulo H2', 'formatBlock', 'h2'));
    toolbar.appendChild(makeBtn('&#182;', 'Paragrafo', 'formatBlock', 'p'));
    const sep2 = document.createElement('span'); sep2.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px'; toolbar.appendChild(sep2);
    toolbar.appendChild(makeBtn('&#8226;', 'Lista', 'insertUnorderedList'));
    toolbar.appendChild(makeBtn('1.', 'Lista Numerada', 'insertOrderedList'));

    const debounceSave = (() => { let timer; return (html) => { clearTimeout(timer); timer = setTimeout(() => saveFn(key, html), 300); }; })();
    content.oninput = () => { debounceSave(content.innerHTML); };

    wrap.appendChild(toolbar);
    wrap.appendChild(content);
    return wrap;
}

function createIconInput(key, value, saveFn, debouncedSave) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    const icons = ['fas fa-star','fas fa-heart','fas fa-check','fas fa-times','fas fa-plus','fas fa-minus','fas fa-arrow-right','fas fa-arrow-left','fas fa-arrow-up','fas fa-arrow-down','fas fa-chevron-right','fas fa-chevron-left','fas fa-check-circle','fas fa-times-circle','fas fa-exclamation-circle','fas fa-info-circle','fas fa-lightbulb','fas fa-bell','fas fa-envelope','fas fa-phone','fas fa-map-marker-alt','fas fa-user','fas fa-users','fas fa-home','fas fa-cog','fas fa-search','fas fa-lock','fas fa-download','fas fa-upload','fas fa-share','fas fa-link','fas fa-edit','fas fa-trash','fas fa-copy','fas fa-image','fas fa-video','fas fa-book','fas fa-calendar','fas fa-clock','fas fa-flag','fas fa-tag','fas fa-rocket','fas fa-bolt','fas fa-fire','fas fa-sun','fas fa-moon','fas fa-cloud','fas fa-globe','fas fa-code','fas fa-database','fas fa-wifi','fab fa-github','fab fa-google','fab fa-facebook','fab fa-twitter','fab fa-instagram','fab fa-youtube','fab fa-linkedin'];
    const preview = document.createElement('div');
    preview.style.cssText = 'text-align:center;padding:8px;font-size:2rem;color:var(--pb-text)';
    const currentIcon = value || 'fas fa-star';
    preview.innerHTML = `<i class="${escHtml(currentIcon)}"></i>`;
    const search = document.createElement('input');
    search.type = 'text'; search.value = currentIcon; search.placeholder = 'fas fa-star';
    search.style.cssText = 'width:100%;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:12px;font-family:monospace';
    const grid = document.createElement('div');
    grid.style.cssText = 'display:grid;grid-template-columns:repeat(8,1fr);gap:2px;max-height:140px;overflow-y:auto;padding:4px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px';
    const renderGrid = (filter) => {
        grid.innerHTML = '';
        const filtered = filter ? icons.filter(i => i.includes(filter.toLowerCase())) : icons;
        filtered.forEach(ic => {
            const btn = document.createElement('button');
            btn.type = 'button'; btn.innerHTML = `<i class="${ic}" style="font-size:14px"></i>`; btn.title = ic;
            btn.style.cssText = 'width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;transition:all .15s';
            btn.onmouseenter = () => { btn.style.background = 'var(--pb-border)'; btn.style.borderColor = 'var(--pb-accent)'; };
            btn.onmouseleave = () => { btn.style.background = 'transparent'; btn.style.borderColor = 'transparent'; };
            btn.onclick = (e) => { e.preventDefault(); search.value = ic; preview.innerHTML = `<i class="${ic}"></i>`; saveFn(key, ic); };
            grid.appendChild(btn);
        });
    };
    renderGrid('');
    search.oninput = () => { renderGrid(search.value); preview.innerHTML = `<i class="${escHtml(search.value)}"></i>`; debouncedSave(key, () => saveFn(key, search.value)); };
    container.appendChild(preview);
    container.appendChild(search);
    container.appendChild(grid);
    return container;
}

function createGalleryInput(key, value, saveFn) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    let images = Array.isArray(value) ? [...value] : [];
    const update = () => saveFn(key, images);
    const list = document.createElement('div');
    list.style.cssText = 'display:flex;flex-direction:column;gap:4px;max-height:200px;overflow-y:auto';
    const renderList = () => {
        list.innerHTML = '';
        images.forEach((img, idx) => {
            const item = document.createElement('div');
            item.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:6px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;cursor:grab';
            item.innerHTML = `<img src="${escHtml(img.url||'')}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0"><div style="flex:1;min-width:0"><input type="text" value="${escHtml(img.alt||'')}" placeholder="Alt text" style="width:100%;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px;box-sizing:border-box"></div><button type="button" title="Remove" style="background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:14px;padding:2px">\u00D7</button>`;
            item.querySelector('input').onchange = (e) => { images[idx].alt = e.target.value; update(); };
            item.querySelector('button').onclick = () => { images.splice(idx, 1); update(); renderList(); };
            list.appendChild(item);
        });
    };
    renderList();
    const addBtn = document.createElement('button');
    addBtn.type = 'button'; addBtn.textContent = '+ Adicionar Imagens';
    addBtn.style.cssText = 'flex:1;padding:8px;background:var(--pb-primary);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:12px;font-weight:500';
    addBtn.onclick = () => {
        const url = prompt('URL da imagem:');
        if (url) { images.push({ url, alt: '' }); update(); renderList(); }
    };
    container.appendChild(list);
    container.appendChild(addBtn);
    return container;
}

function createRepeaterInput(key, value, ctrl, saveFn) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    let items = Array.isArray(value) ? value.map(v => ({...v})) : [];
    const subFields = ctrl.fields || {};
    const list = document.createElement('div');
    list.style.cssText = 'display:flex;flex-direction:column;gap:4px;max-height:280px;overflow-y:auto';
    const updateRepeater = () => saveFn(key, items);
    const renderItems = () => {
        list.innerHTML = '';
        items.forEach((item, idx) => {
            const card = document.createElement('div');
            card.style.cssText = 'background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;padding:8px;display:flex;flex-direction:column;gap:6px';
            const header = document.createElement('div');
            header.style.cssText = 'display:flex;align-items:center;gap:4px;font-size:.7rem;color:var(--pb-text2);cursor:grab';
            header.innerHTML = `<span style="cursor:grab">\u28FF</span><span style="flex:1;font-weight:500;color:var(--pb-text)">${escHtml(item.label||item.type||'Field '+(idx+1))}</span>`;
            const delBtn = document.createElement('button');
            delBtn.type = 'button'; delBtn.textContent = '\u00D7';
            delBtn.style.cssText = 'background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:14px;padding:0 2px';
            delBtn.onclick = () => { items.splice(idx, 1); renderItems(); updateRepeater(); };
            header.appendChild(delBtn);
            card.appendChild(header);
            for (const [fk, fc] of Object.entries(subFields)) {
                const fRow = document.createElement('div');
                fRow.style.cssText = 'display:flex;align-items:center;gap:6px';
                const fLabel = document.createElement('label');
                fLabel.textContent = fc.label || fk;
                fLabel.style.cssText = 'font-size:.65rem;color:var(--pb-text2);min-width:60px';
                fRow.appendChild(fLabel);
                if (fc.type === 'select') {
                    const sel = document.createElement('select');
                    sel.style.cssText = 'flex:1;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px';
                    (fc.options||[]).forEach(opt => {
                        const o = document.createElement('option');
                        o.value = opt; o.textContent = opt;
                        if (opt === item[fk]) o.selected = true;
                        sel.appendChild(o);
                    });
                    sel.onchange = (e) => { items[idx][fk] = e.target.value; updateRepeater(); renderItems(); };
                    fRow.appendChild(sel);
                } else if (fc.type === 'boolean') {
                    const cb = document.createElement('input');
                    cb.type = 'checkbox'; cb.checked = !!item[fk];
                    cb.onchange = (e) => { items[idx][fk] = e.target.checked; updateRepeater(); };
                    fRow.appendChild(cb);
                } else {
                    const inp = document.createElement('input');
                    inp.type = 'text'; inp.value = item[fk] || '';
                    inp.style.cssText = 'flex:1;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px';
                    inp.onchange = (e) => { items[idx][fk] = e.target.value; updateRepeater(); renderItems(); };
                    fRow.appendChild(inp);
                }
                card.appendChild(fRow);
            }
            list.appendChild(card);
        });
    };
    renderItems();
    const addBtn = document.createElement('button');
    addBtn.type = 'button'; addBtn.textContent = '+ Adicionar Item';
    addBtn.style.cssText = 'padding:6px;background:var(--pb-surface2);border:1px dashed var(--pb-border);border-radius:6px;color:var(--pb-text2);cursor:pointer;font-size:11px;text-align:center;transition:all .2s';
    addBtn.onclick = () => {
        const newItem = {};
        for (const [fk, fc] of Object.entries(subFields)) {
            if (fc.type === 'boolean') newItem[fk] = false;
            else if (fc.type === 'select' && fc.options && fc.options.length) newItem[fk] = fc.options[0];
            else newItem[fk] = '';
        }
        items.push(newItem);
        updateRepeater();
        renderItems();
    };
    container.appendChild(list);
    container.appendChild(addBtn);
    return container;
}

function createTypographyInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'typography_font_family', label: 'Font Family', type: 'text' },
        { fk: 'typography_font_size', label: 'Font Size', type: 'text' },
        { fk: 'typography_font_weight', label: 'Font Weight', type: 'select', options: ['300','400','500','600','700','800','900'] },
        { fk: 'typography_line_height', label: 'Line Height', type: 'text' },
        { fk: 'typography_letter_spacing', label: 'Letter Spacing', type: 'text' },
        { fk: 'typography_text_transform', label: 'Text Transform', type: 'select', options: ['none','uppercase','lowercase','capitalize'] },
        { fk: 'typography_color', label: 'Text Color', type: 'color' },
    ];
    defs.forEach(({fk, label, type, options}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = opt;
                if (opt === (value || '')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || '';
            if (type === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createBackgroundInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'backgroundColor', label: 'Background Color', type: 'color' },
        { fk: 'backgroundImage', label: 'Background Image', type: 'url' },
        { fk: 'backgroundSize', label: 'Size', type: 'select', options: ['auto','cover','contain'] },
        { fk: 'backgroundRepeat', label: 'Repeat', type: 'select', options: ['no-repeat','repeat','repeat-x','repeat-y'] },
    ];
    defs.forEach(({fk, label, type, options}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = opt;
                if (opt === (value || '')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || '';
            if (fk === 'backgroundColor') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createBorderInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'borderWidth', label: 'Border Width', type: 'text', def: '0' },
        { fk: 'borderColor', label: 'Border Color', type: 'color', def: '#000000' },
        { fk: 'borderRadius', label: 'Border Radius', type: 'text', def: '0' },
        { fk: 'borderStyle', label: 'Border Style', type: 'select', options: ['none','solid','dashed','dotted','double'], def: 'solid' },
    ];
    defs.forEach(({fk, label, type, options, def}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = opt;
                if (opt === (value || def || '')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || def || '';
            if (fk === 'borderColor') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createBoxShadowInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'shadowHorizontal', label: 'Horizontal', type: 'text', def: '0' },
        { fk: 'shadowVertical', label: 'Vertical', type: 'text', def: '0' },
        { fk: 'shadowBlur', label: 'Blur', type: 'text', def: '0' },
        { fk: 'shadowSpread', label: 'Spread', type: 'text', def: '0' },
        { fk: 'shadowColor', label: 'Color', type: 'color', def: 'rgba(0,0,0,0.3)' },
    ];
    const readAll = () => {
        const h = c.querySelector('[data-fk="shadowHorizontal"]')?.value || '0';
        const v = c.querySelector('[data-fk="shadowVertical"]')?.value || '0';
        const b = c.querySelector('[data-fk="shadowBlur"]')?.value || '0';
        const s = c.querySelector('[data-fk="shadowSpread"]')?.value || '0';
        const co = c.querySelector('[data-fk="shadowColor"]')?.value || 'rgba(0,0,0,0.3)';
        return `${h}px ${v}px ${b}px ${s}px ${co}`;
    };
    defs.forEach(({fk, label, type, def}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        const inp = document.createElement('input');
        inp.type = type; inp.value = value || def || '';
        inp.dataset.fk = fk;
        if (fk === 'shadowColor') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
        inp.oninput = () => _debouncedStyle('boxShadow', elementId, () => updateStyle('boxShadow', readAll(), elementId));
        row.appendChild(inp);
        c.appendChild(row);
    });
    return c;
}

function createDimensionsInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const isLinked = { padding: true, margin: true };
    const groups = [
        { prefix: 'padding', label: 'Padding', keys: ['Top','Right','Bottom','Left'] },
        { prefix: 'margin', label: 'Margin', keys: ['Top','Right','Bottom','Left'] },
    ];
    groups.forEach(group => {
        const header = document.createElement('div');
        header.style.cssText = 'display:flex;align-items:center;justify-content:space-between;margin-top:.25rem';
        const hLabel = document.createElement('span');
        hLabel.style.cssText = 'font-size:12px;font-weight:600;color:var(--pb-text2)';
        hLabel.textContent = group.label;
        header.appendChild(hLabel);
        const lockBtn = document.createElement('button');
        lockBtn.type = 'button'; lockBtn.innerHTML = '\uD83D\uDD17';
        lockBtn.title = 'Link values';
        lockBtn.style.cssText = 'background:none;border:1px solid var(--pb-border);border-radius:4px;padding:2px 6px;cursor:pointer;font-size:12px;transition:all .15s';
        lockBtn.onclick = () => {
            isLinked[group.prefix] = !isLinked[group.prefix];
            lockBtn.innerHTML = isLinked[group.prefix] ? '\uD83D\uDD17' : '\uD83D\uDD13';
            lockBtn.style.borderColor = isLinked[group.prefix] ? 'var(--pb-accent)' : 'var(--pb-border)';
        };
        lockBtn.style.borderColor = 'var(--pb-accent)';
        header.appendChild(lockBtn);
        c.appendChild(header);
        const grid = document.createElement('div');
        grid.style.cssText = 'display:grid;grid-template-columns:repeat(4,1fr);gap:3px';
        const inputs = [];
        group.keys.forEach((side, idx) => {
            const fk = group.prefix + side;
            const wrap = document.createElement('div');
            wrap.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:2px';
            const sideLabel = document.createElement('span');
            sideLabel.style.cssText = 'font-size:10px;color:var(--pb-text2);text-transform:uppercase';
            sideLabel.textContent = side;
            const inp = document.createElement('input');
            inp.type = 'text'; inp.value = value || ''; inp.placeholder = '0';
            inp.style.cssText = 'width:100%;padding:4px;text-align:center;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:12px;box-sizing:border-box';
            inp.oninput = () => {
                _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
                if (isLinked[group.prefix]) {
                    inputs.forEach((otherInp, oi) => {
                        if (oi !== idx) {
                            otherInp.value = inp.value;
                            _debouncedStyle(group.prefix + group.keys[oi], elementId, () => updateStyle(group.prefix + group.keys[oi], inp.value, elementId));
                        }
                    });
                }
            };
            inputs.push(inp);
            wrap.appendChild(sideLabel);
            wrap.appendChild(inp);
            grid.appendChild(wrap);
        });
        c.appendChild(grid);
    });
    return c;
}

function createHoverInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const defs = [
        { fk: 'hoverBackgroundColor', label: 'Background Color', type: 'color' },
        { fk: 'hoverTextColor', label: 'Text Color', type: 'color' },
        { fk: 'hoverBorderColor', label: 'Border Color', type: 'color' },
        { fk: 'hoverTransform', label: 'Transform', type: 'select', options: ['none','scale(1.05)','scale(0.98)','translateY(-2px)','translateY(2px)'] },
    ];
    defs.forEach(({fk, label, type, options}) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        if (type === 'select') {
            const sel = document.createElement('select');
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt; o.textContent = opt;
                if (opt === (value || 'none')) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => updateStyle(fk, sel.value, elementId);
            row.appendChild(sel);
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = value || '';
            if (type === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
            inp.oninput = () => _debouncedStyle(fk, elementId, () => updateStyle(fk, inp.value, elementId));
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createAnimationInput(key, value, saveFn) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const animRow = document.createElement('div');
    animRow.className = 'pb-control';
    const animLabel = document.createElement('label');
    animLabel.textContent = 'Entrance Animation';
    animRow.appendChild(animLabel);
    const animSel = document.createElement('select');
    animSel.id = `ctrl-${key}`;
    ['none','fadeIn','fadeInUp','fadeInDown','fadeInLeft','fadeInRight','slideInUp','slideInDown','slideInLeft','slideInRight','zoomIn','bounceIn','rotateIn','lightSpeedIn'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = opt;
        if (opt === (value || 'none')) o.selected = true;
        animSel.appendChild(o);
    });
    animSel.onchange = () => saveFn(key, animSel.value);
    animRow.appendChild(animSel);
    c.appendChild(animRow);
    const durRow = document.createElement('div');
    durRow.className = 'pb-control';
    durRow.style.display = (value && value !== 'none') ? '' : 'none';
    const durLabel = document.createElement('label');
    durLabel.textContent = 'Duration';
    durRow.appendChild(durLabel);
    const durSel = document.createElement('select');
    ['slow','normal','fast'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = opt;
        if (opt === 'normal') o.selected = true;
        durSel.appendChild(o);
    });
    durSel.onchange = () => saveFn(key + '_duration', durSel.value);
    durRow.appendChild(durSel);
    c.appendChild(durRow);
    const delayRow = document.createElement('div');
    delayRow.className = 'pb-control';
    delayRow.style.display = (value && value !== 'none') ? '' : 'none';
    const delayLabel = document.createElement('label');
    delayLabel.textContent = 'Delay (ms)';
    delayRow.appendChild(delayLabel);
    const delayInp = document.createElement('input');
    delayInp.type = 'number'; delayInp.min = 0; delayInp.max = 5000; delayInp.step = 100; delayInp.value = 0;
    delayInp.onchange = () => saveFn(key + '_delay', parseInt(delayInp.value) || 0);
    delayRow.appendChild(delayInp);
    c.appendChild(delayRow);
    animSel.onchange = () => {
        saveFn(key, animSel.value);
        const show = animSel.value && animSel.value !== 'none';
        durRow.style.display = show ? '' : 'none';
        delayRow.style.display = show ? '' : 'none';
    };
    return c;
}

function createVisibilityInput(key, value, saveFn) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
    const defs = [
        { fk: 'visibility_desktop', label: 'Visible on Desktop', default: true },
        { fk: 'visibility_tablet', label: 'Visible on Tablet', default: true },
        { fk: 'visibility_mobile', label: 'Visible on Mobile', default: true },
    ];
    defs.forEach(({fk, label, default: def}) => {
        const row = document.createElement('div');
        row.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px';
        const lbl = document.createElement('span');
        lbl.style.cssText = 'font-size:12px;color:var(--pb-text)';
        lbl.textContent = label;
        const sw = document.createElement('label');
        sw.style.cssText = 'position:relative;display:inline-block;width:36px;height:20px;cursor:pointer';
        const cb = document.createElement('input');
        cb.type = 'checkbox';
        cb.checked = value !== undefined ? !!value : def;
        cb.style.cssText = 'opacity:0;width:0;height:0';
        const slider = document.createElement('span');
        slider.style.cssText = 'position:absolute;inset:0;background:var(--pb-border);border-radius:20px;transition:.2s';
        const before = document.createElement('span');
        before.style.cssText = 'position:absolute;height:14px;width:14px;left:3px;bottom:3px;background:white;border-radius:50%;transition:.2s';
        slider.appendChild(before);
        const updateSlider = () => {
            slider.style.background = cb.checked ? 'var(--pb-accent)' : 'var(--pb-border)';
            before.style.transform = cb.checked ? 'translateX(16px)' : '';
        };
        updateSlider();
        cb.onchange = () => { updateSlider(); saveFn(fk, cb.checked); };
        sw.appendChild(cb);
        sw.appendChild(slider);
        row.appendChild(lbl);
        row.appendChild(sw);
        c.appendChild(row);
    });
    return c;
}

function updateSetting(key, value, elementId, reload = true) {
    state.dirty = true;
    if (state.cachedSettings) state.cachedSettings[key] = value;
    const settings = {};
    settings[key] = value;
    apiFetch(`/page-builder/elements/${elementId}/settings`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ settings }),
    })
    .then(() => {
        if (reload) reloadElement(elementId);
        snapshotHistory(state);
    })
    .catch(err => { console.error('updateSetting failed:', err); toastError('Falha ao atualizar configuracao: ' + (err.message || err)); });
}

function updateStyle(key, value, elementId, reload = true) {
    state.dirty = true;
    if (state.cachedStyles) state.cachedStyles[key] = value;
    const styles = {};
    styles[key] = value;
    apiFetch(`/page-builder/elements/${elementId}/styles`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ styles }),
    })
    .then(() => {
        if (reload) reloadElement(elementId);
        snapshotHistory(state);
    })
    .catch(err => { console.error('updateStyle failed:', err); toastError('Falha ao atualizar estilo: ' + (err.message || err)); });
}

function reloadElement(id) {
    apiFetch(`/page-builder/elements/${id}/render`)
        .then(data => {
            const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
            if (el) {
                const oldContent = el.querySelector('.pb-el-content');
                if (oldContent) oldContent.innerHTML = data.html;
                else el.innerHTML = `<div class="pb-el-content">${data.html}</div>`;
                renderMath();
            }
        })
        .catch(err => { console.error('reloadElement failed:', err); toastError('Falha ao recarregar elemento'); });
}

function deleteElement(id) {
    if (!confirm('Excluir este elemento?')) return;
    apiFetch(`/page-builder/elements/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': state.csrf } })
        .then(() => {
            if (state.selectedId === id) { state.selectedId = null; document.getElementById('settings-empty').style.display = ''; document.getElementById('settings-form').classList.remove('active'); }
            loadElements();
        })
        .catch(() => toastError('Falha ao excluir elemento'));
}

function duplicateElement(id) {
    apiFetch(`/page-builder/elements/${id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': state.csrf } })
        .then(() => loadElements())
        .catch(() => toastError('Falha ao duplicar elemento'));
}

function save(silent) {
    if (state.saving) return;
    state.saving = true;
    let overlay = null;
    if (!silent) {
        overlay = document.createElement('div');
        overlay.className = 'saving-overlay';
        overlay.innerHTML = '<div class="saving-card"><div class="spinner"></div><span class="saving-text">Salvando...</span></div>';
        document.body.appendChild(overlay);
    }
    apiFetch(`/page-builder/pages/${state.pageId}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ status: 'draft' }),
    })
    .then(() => {
        state.saving = false;
        state.dirty = false;
        if (overlay) overlay.remove();
        if (!silent) toastSuccess('Pagina salva!');
    })
    .catch(() => { state.saving = false; if (overlay) overlay.remove(); toastError('Falha ao salvar'); });
}

function publish() {
    if (!confirm('Publicar esta pagina?')) return;
    apiFetch(`/page-builder/pages/${state.pageId}/publish`, { method: 'POST', headers: { 'X-CSRF-TOKEN': state.csrf } })
        .then(() => { toastSuccess('Pagina publicada!'); setTimeout(() => location.reload(), 500); })
        .catch(() => toastError('Falha ao publicar pagina'));
}

function setResponsive(mode) {
    state.responsiveMode = mode;
    document.querySelectorAll('.pb-toolbar [data-mode]').forEach(b => b.classList.remove('active'));
    document.querySelector(`.pb-toolbar [data-mode="${mode}"]`).classList.add('active');
    const canvas = document.getElementById('canvas');
    canvas.className = 'pb-canvas';
    if (mode !== 'desktop') canvas.classList.add('is-' + mode);
}

function switchTab(tab) {
    document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.pb-panel-tab[data-tab="${tab}"]`).classList.add('active');
    document.getElementById('panel-widgets').style.display = tab === 'widgets' ? '' : 'none';
    document.getElementById('panel-structure').style.display = tab === 'structure' ? '' : 'none';
    document.getElementById('panel-layouts').style.display = tab === 'layouts' ? '' : 'none';
    if (tab === 'layouts') loadTemplates();
}

function setZoom(level) {
    state.zoomLevel = Math.min(200, Math.max(25, level));
    const canvas = document.getElementById('canvas');
    if (canvas) {
        canvas.style.transform = `scale(${state.zoomLevel / 100})`;
        canvas.style.transformOrigin = 'top center';
    }
    const label = document.getElementById('pb-zoom-label');
    if (label) label.textContent = state.zoomLevel + '%';
}

function toggleFullscreen() {
    state.isFullscreen = !state.isFullscreen;
    const panels = document.querySelectorAll('.pb-panel');
    const layout = document.querySelector('.pb-layout');
    const btn = document.getElementById('pb-fullscreen');
    if (state.isFullscreen) {
        panels.forEach(p => p.style.display = 'none');
        if (layout) layout.style.display = 'block';
        btn.classList.add('active');
    } else {
        panels.forEach(p => p.style.display = '');
        if (layout) layout.style.display = '';
        btn.classList.remove('active');
    }
}

function bindZoom() {
    const wrap = document.getElementById('canvas-wrap');
    if (!wrap) return;
    wrap.addEventListener('wheel', (e) => {
        if (e.ctrlKey || e.metaKey) {
            e.preventDefault();
            const delta = e.deltaY > 0 ? -5 : 5;
            setZoom(state.zoomLevel + delta);
        }
    }, { passive: false });
}

function observeCanvas() {
    const dz = document.getElementById('canvas-dropzone');
    if (!dz) return;
    let timer = null;
    const observer = new MutationObserver(() => {
        clearTimeout(timer);
        timer = setTimeout(() => renderMath(), 150);
    });
    observer.observe(dz, { childList: true, subtree: true, characterData: true });
}

function bindKeyboard() {
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); editor.undo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) { e.preventDefault(); editor.redo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); editor.redo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); editor.save(); }
        if ((e.ctrlKey || e.metaKey) && e.key === '0') { e.preventDefault(); editor.zoomReset(); }
        if ((e.ctrlKey || e.metaKey) && e.key === '=') { e.preventDefault(); editor.zoomIn(); }
        if ((e.ctrlKey || e.metaKey) && e.key === '-') { e.preventDefault(); editor.zoomOut(); }
        if (e.key === 'F11') { e.preventDefault(); editor.toggleFullscreen(); }
        if (e.key === 'Escape' && state.isFullscreen) { editor.toggleFullscreen(); }
        if (e.key === 'Delete' && state.selectedId) { editor.deleteSelected(); }
    });
}

function bindInlineEditing() {
    const dz = document.getElementById('canvas-dropzone');
    const editableTypes = ['heading', 'text', 'button', 'callout'];
    dz.addEventListener('dblclick', (e) => {
        const el = e.target.closest('.pb-el');
        if (!el || e.target.closest('.pb-el-toolbar')) return;
        const type = el.dataset.elType;
        if (!editableTypes.includes(type)) return;
        let textEl = e.target.closest('h1, h2, h3, h4, h5, h6, p, span, a, button, label');
        if (!textEl) {
            const contentDiv = e.target.closest('.pb-el-content > div');
            if (contentDiv) textEl = contentDiv;
        }
        if (!textEl || el.dataset._editing) return;
        el.dataset._editing = '1';
        el.dataset._origHtml = textEl.innerHTML;
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
            const newHtml = textEl.innerHTML.trim();
            const origHtml = el.dataset._origHtml || '';
            if (newHtml && newHtml !== origHtml) {
                const key = { heading: 'title', text: 'content', button: 'text', callout: 'content' }[type] || 'title';
                const elId = el.dataset.elId;
                updateSetting(key, newHtml, elId, false);
                if (state.selectedId == elId) setTimeout(() => loadControls(elId), 100);
            }
        };
        textEl.addEventListener('blur', finish, { once: true });
        textEl.addEventListener('keydown', (k) => {
            if (k.key === 'Enter' && !k.shiftKey) { k.preventDefault(); textEl.blur(); }
            if (k.key === 'Escape') { textEl.innerHTML = el.dataset._origHtml || ''; textEl.blur(); }
        });
    });
}

function autoSave() {
    setInterval(() => {
        if (state.dirty) save(true);
    }, 60000);
}

function loadTemplates() {
    apiFetch('/page-builder/templates')
        .then(data => {
            const container = document.getElementById('layout-templates');
            container.innerHTML = '<div class="pb-widget-group-title" style="margin-bottom:.75rem">Escolha um modelo de layout</div>';
            for (const [key, tmpl] of Object.entries(data.templates)) {
                const previews = { blank: '&#9635;', landing: '&#127968;', about: '&#128100;', contact: '&#128222;', showcase: '&#127912;' };
                const card = document.createElement('div');
                card.className = 'pb-layout-card';
                card.innerHTML = `<div class="pb-layout-card-preview">${previews[key] || '&#9635;'}</div><div class="pb-layout-card-info"><h4>${tmpl.name}</h4><p>${tmpl.description}</p></div><button class="pb-apply-btn" data-template="${key}">Aplicar Modelo</button>`;
                card.querySelector('.pb-apply-btn').onclick = (e) => {
                    e.stopPropagation();
                    applyTemplate(key, e.target);
                };
                container.appendChild(card);
            }
        })
        .catch(() => toastError('Falha ao carregar modelos'));
}

function applyTemplate(key, btn) {
    if (!confirm('Aplicar este modelo? Irá substituir todo o conteudo existente.')) return;
    btn.disabled = true; btn.textContent = 'Aplicando...';
    apiFetch(`/page-builder/pages/${state.pageId}/apply-template`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ template: key }),
    })
    .then(() => {
        showToast('Modelo aplicado!');
        loadElements();
        btn.disabled = false; btn.textContent = 'Aplicar Modelo';
    })
    .catch(() => { btn.disabled = false; btn.textContent = 'Aplicar Modelo'; });
}

function showPageSettings() {
    state.selectedId = null;
    document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    document.getElementById('settings-empty').style.display = 'none';
    document.getElementById('settings-form').classList.remove('active');
    document.getElementById('page-settings-form').classList.add('active');
    renderPageSettings();
}

function hidePageSettings() {
    document.getElementById('page-settings-form').classList.remove('active');
    document.getElementById('settings-empty').style.display = '';
}

function copyHtml() {
    fetch('/page-builder/pages/' + state.pageId + '/render?format=inner', { headers: { 'Accept': 'text/html' } })
        .then(r => r.text())
        .then(html => {
            navigator.clipboard.writeText(html).then(() => showToast('HTML copiado!')).catch(() => {
                const ta = document.createElement('textarea');
                ta.value = html;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                ta.remove();
                showToast('HTML copiado!');
            });
        })
        .catch(() => toastError('Falha ao copiar HTML'));
}

function uploadImageFile(file, callback) {
    const formData = new FormData();
    formData.append('image', file);
    showToast('Enviando imagem...', 'info');
    apiFetch('/page-builder/upload', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
        body: formData,
    })
    .then(data => {
        if (data.url) { toastSuccess('Imagem enviada!'); callback(data.url); }
        else toastError('Falha ao enviar imagem');
    })
    .catch(() => toastError('Falha ao enviar imagem'));
}

function renderPageSettings() {
    const body = document.getElementById('page-settings-body');
    body.innerHTML = '';
    const currentPage = window._pageData || {};
    const s = currentPage.settings || {};
    const controls = [
        { key: 'container_width', label: 'Largura do Container', type: 'text', default: '1140px' },
        { key: 'page_background', label: 'Fundo da Pagina', type: 'color', default: '#ffffff' },
        { key: 'content_padding', label: 'Espacamento Interno', type: 'text', default: '0px' },
        { key: 'custom_css', label: 'CSS Personalizado', type: 'textarea', default: '' },
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
            const update = (v) => { inp.value = v; txt.value = v; updatePageSetting(ctrl.key, v); };
            inp.oninput = (e) => update(e.target.value);
            txt.oninput = (e) => { if (/^#[0-9a-f]{3,8}$/i.test(e.target.value)) update(e.target.value); };
            container.appendChild(inp); container.appendChild(txt);
            inputWrap.appendChild(container);
        } else if (ctrl.type === 'textarea') {
            const ta = document.createElement('textarea');
            ta.value = val || '';
            ta.style.cssText = 'width:100%;padding:.45rem .6rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:.8rem;min-height:100px;font-family:monospace';
            ta.onchange = (e) => updatePageSetting(ctrl.key, e.target.value);
            inputWrap.appendChild(ta);
        } else {
            const inp = document.createElement('input');
            inp.type = 'text'; inp.value = val || '';
            inp.onchange = (e) => updatePageSetting(ctrl.key, e.target.value);
            inputWrap.appendChild(inp);
        }
        body.appendChild(group);
    });
}

function updatePageSetting(key, value) {
    state.dirty = true;
    const settings = {};
    settings[key] = value;
    apiFetch(`/page-builder/pages/${state.pageId}/layout`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ settings }),
    }).catch(() => toastError('Falha ao atualizar configuracao da pagina'));
}

window.editor = editor;

document.addEventListener('DOMContentLoaded', () => {
    const pageIdEl = document.querySelector('[data-page-id]');
    const csrfEl = document.querySelector('[data-csrf]');
    if (pageIdEl && csrfEl) {
        editor.init(parseInt(pageIdEl.dataset.pageId), csrfEl.dataset.csrf);
    }
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.pb-el') && !e.target.closest('.pb-structure-item') && !e.target.closest('.pb-settings') && !e.target.closest('.pb-toolbar') && !e.target.closest('.pb-nav-context')) {
        document.querySelectorAll('.pb-el.selected').forEach(el => el.classList.remove('selected'));
        document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
        state.selectedId = null;
        document.getElementById('settings-empty').style.display = '';
        document.getElementById('settings-form').classList.remove('active');
    }
});
