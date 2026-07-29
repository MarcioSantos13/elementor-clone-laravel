import state from './state.js';
import { escHtml, showToast, toastError, toastSuccess, structureIcon, apiFetch } from './utils.js';
import { renderCanvas, renderMath, elementHtml, renderStructure } from './canvas.js';
import { pushHistory, snapshotHistory, undo, redo, updateUndoButtons, _findElement } from './history.js';
import { bindDragDrop, refreshSortables, initContainerSortables, _saveElementOrder } from './dragdrop.js';
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

        state.renderCanvas = (els) => { renderCanvas(state, els); refreshSortables(state); };
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
        bindKeyboard();
        bindInlineEditing();
        bindZoom();
        autoSave();
        observeCanvas();
        collabJoin();
        collabHeartbeat();
        bindCollabPresence();
        bindWidgetSearch();
        bindResizablePanels();
        bindMultiSelect();
        initOnboarding();
        loadGlobalSettings();
    },

    undo() { undo(state); },
    redo() { redo(state); },
    save(silent) { save(silent); },
    publish() { publish(); },
    setResponsive(mode) { setResponsive(mode); },
    setResponsiveTab(device) { setResponsiveTab(device); },
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
    saveAsTemplate() { saveAsTemplate(); },
    copyHtml() { copyHtml(); },
    importHtml() { openHtmlImportModal(state.csrf); },
    copyStyles() { copyStyles(); },
    pasteStyles() { pasteStyles(); },
    duplicateSelected() { if (state.selectedId) duplicateElement(state.selectedId); },
    showSiteSettings() { showSiteSettings(); },
    hideSiteSettings() { hideSiteSettings(); },
    showRevisionHistory() { showRevisionHistory(); },
};

function selectElement(id, ctrlKey) {
    if (ctrlKey) {
        if (state.multiSelected && state.multiSelected.has(id)) {
            state.multiSelected.delete(id);
        } else {
            if (!state.multiSelected) state.multiSelected = new Set();
            state.multiSelected.add(id);
        }
        refreshMultiSelect();
        return;
    }
    state.multiSelected = null;
    state.selectedId = id;
    document.querySelectorAll('.pb-el.selected, .pb-el.multi-selected').forEach(el => {
        el.classList.remove('selected');
        el.classList.remove('multi-selected');
    });
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
    if (el) el.classList.add('selected');
    const si = document.querySelector(`.pb-structure-item[data-el-id="${id}"]`);
    if (si) si.classList.add('active');
    loadControls(id);
    removeMultiToolbar();
}

function loadElements() {
    apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
        headers: { 'X-CSRF-TOKEN': state.csrf },
    })
        .then(data => {
            const prevSelected = state.selectedId;
            state._lastElements = data.elements || [];
            renderCanvas(state, state._lastElements);
            refreshSortables(state);
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

const RESPONSIVE_KEYS = ['padding_top', 'padding_bottom', 'padding_left', 'padding_right', 'margin_top', 'margin_bottom', 'margin_left', 'margin_right', 'font_size', 'line_height', 'letter_spacing', 'width', 'max_width', 'height', 'border_radius', 'gap'];

function getResponsiveValue(key, ctrl, settings, styles, tab, device) {
    if (device !== 'desktop' && RESPONSIVE_KEYS.includes(key)) {
        const respKey = key + '_' + device;
        if (tab === 'style' || tab === 'advanced') {
            if (styles[respKey] !== undefined) return styles[respKey];
        }
        if (tab === 'content' || tab === 'advanced') {
            if (settings[respKey] !== undefined) return settings[respKey];
        }
    }
    if (tab === 'style') {
        return styles[key] !== undefined ? styles[key] : (ctrl.default !== undefined ? ctrl.default : '');
    } else if (tab === 'advanced') {
        return styles[key] !== undefined ? styles[key] : (settings[key] !== undefined ? settings[key] : (ctrl.default !== undefined ? ctrl.default : ''));
    } else {
        return settings[key] !== undefined ? settings[key] : (ctrl.default !== undefined ? ctrl.default : '');
    }
}

function renderControls() {
    const body = document.getElementById('settings-body');
    body.innerHTML = '';
    const controls = state.cachedControls || {};
    const settings = state.cachedSettings || {};
    const styles = state.cachedStyles || {};
    const elementId = state.cachedElementId;
    const tab = state.activeTab;
    const device = state.responsiveTab || 'desktop';
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
            let val = getResponsiveValue(key, ctrl, settings, styles, tab, device);
            const control = document.createElement('div');
            control.className = 'pb-control';
            const isResponsive = device !== 'desktop' && RESPONSIVE_KEYS.includes(key);
            if (isResponsive) {
                control.style.position = 'relative';
                const dot = document.createElement('span');
                dot.style.cssText = 'position:absolute;top:4px;right:4px;width:6px;height:6px;border-radius:50%;background:#6366f1;';
                dot.title = `Value for ${device}`;
                control.appendChild(dot);
            }
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
        video: () => createVideoInput(key, value, saveFn, elementId),
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
        gradient: () => createGradientInput(key, value, elementId),
        scroll_animation: () => createScrollAnimationInput(key, value, saveFn),
        text_shadow: () => createTextShadowInput(key, value, elementId),
        text_stroke: () => createTextStrokeInput(key, value, elementId),
        column_width: () => createColumnWidthInput(key, value, elementId),
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

function createVideoInput(key, value, saveFn, elementId) {
    const container = document.createElement('div');
    container.style.cssText = 'display:flex;flex-direction:column;gap:.35rem';
    const currentUrl = value || '';
    const dropZone = document.createElement('div');
    dropZone.style.cssText = 'border:2px dashed var(--pb-border);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;transition:all .2s;background:var(--pb-bg);position:relative';
    dropZone.innerHTML = `<div style="font-size:1.5rem;margin-bottom:.35rem;opacity:.5">&#127909;</div><div style="font-size:.72rem;color:var(--pb-text2)"><strong style="color:var(--pb-accent);cursor:pointer">Clique para selecionar</strong><br>ou arraste um video aqui</div><div style="font-size:.65rem;color:var(--pb-text2);margin-top:.3rem">MP4, WebM, OGG (max 50MB)</div>`;
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'video/mp4,video/webm,video/ogg';
    fileInput.style.display = 'none';
    const preview = document.createElement('div');
    preview.style.cssText = 'border-radius:6px;overflow:hidden;background:var(--pb-bg);min-height:50px;display:flex;align-items:center;justify-content:center;font-size:.7rem;color:var(--pb-text2);border:1px solid var(--pb-border)';
    const updatePreview = (url) => {
        if (url) preview.innerHTML = `<video src="${escHtml(url)}" style="width:100%;max-height:120px;object-fit:contain;border-radius:4px" controls></video>`;
        else preview.textContent = 'Nenhum video selecionado';
    };
    if (currentUrl) updatePreview(currentUrl);
    dropZone.appendChild(fileInput);
    dropZone.onclick = () => fileInput.click();
    fileInput.onchange = () => {
        const file = fileInput.files[0];
        if (!file) return;
        uploadVideoFile(file, (url) => { saveFn(key, url); updatePreview(url); });
    };
    dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--pb-accent)'; dropZone.style.background = 'var(--pb-primary-light)'; };
    dropZone.ondragleave = () => { dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)'; };
    dropZone.ondrop = (e) => {
        e.preventDefault();
        dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)';
        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('video/')) uploadVideoFile(file, (url) => { saveFn(key, url); updatePreview(url); });
    };
    container.appendChild(dropZone);
    container.appendChild(preview);
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

function createGradientInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const grad = (typeof value === 'object' && value !== null) ? value : { type: 'linear', angle: 180, color1: '#6366f1', color2: '#8b5cf6', position1: 0, position2: 100 };
    const defs = [
        { fk: 'type', label: 'Type', type: 'select', options: ['linear', 'radial'] },
        { fk: 'angle', label: 'Angle (deg)', type: 'number', min: 0, max: 360 },
        { fk: 'color1', label: 'Color 1', type: 'color' },
        { fk: 'position1', label: 'Position 1 (%)', type: 'number', min: 0, max: 100 },
        { fk: 'color2', label: 'Color 2', type: 'color' },
        { fk: 'position2', label: 'Position 2 (%)', type: 'number', min: 0, max: 100 },
    ];
    const preview = document.createElement('div');
    preview.style.cssText = 'height:32px;border-radius:6px;margin-bottom:4px;border:1px solid var(--pb-border)';
    const updatePreview = () => {
        if (grad.type === 'radial') preview.style.background = `radial-gradient(circle, ${grad.color1} ${grad.position1}%, ${grad.color2} ${grad.position2}%)`;
        else preview.style.background = `linear-gradient(${grad.angle}deg, ${grad.color1} ${grad.position1}%, ${grad.color2} ${grad.position2}%)`;
    };
    updatePreview();
    c.appendChild(preview);
    defs.forEach(({ fk, label, type, options, min, max }) => {
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
                if (opt === grad[fk]) o.selected = true;
                sel.appendChild(o);
            });
            sel.onchange = () => { grad[fk] = sel.value; updatePreview(); updateStyle(key, grad, elementId); };
            row.appendChild(sel);
        } else {
            const inp = document.createElement('input');
            inp.type = type; inp.value = grad[fk] || 0;
            if (min !== undefined) inp.min = min;
            if (max !== undefined) inp.max = max;
            if (type === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
            inp.oninput = () => { grad[fk] = type === 'number' ? parseInt(inp.value) || 0 : inp.value; updatePreview(); _debouncedStyle(key, elementId, () => updateStyle(key, grad, elementId)); };
            row.appendChild(inp);
        }
        c.appendChild(row);
    });
    return c;
}

function createScrollAnimationInput(key, value, saveFn) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const row = document.createElement('div');
    row.className = 'pb-control';
    const lbl = document.createElement('label');
    lbl.textContent = 'Scroll Animation';
    row.appendChild(lbl);
    const sel = document.createElement('select');
    ['none','fade-up','fade-down','fade-left','fade-right','zoom-in','zoom-out','slide-up','slide-down'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = opt;
        if (opt === (value || 'none')) o.selected = true;
        sel.appendChild(o);
    });
    sel.onchange = () => saveFn(key, sel.value);
    row.appendChild(sel);
    c.appendChild(row);
    const durRow = document.createElement('div');
    durRow.className = 'pb-control';
    durRow.style.display = (value && value !== 'none') ? '' : 'none';
    const durLabel = document.createElement('label');
    durLabel.textContent = 'Duration';
    durRow.appendChild(durLabel);
    const durSel = document.createElement('select');
    ['0.3s','0.6s','0.9s','1.2s'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = opt;
        if (opt === '0.6s') o.selected = true;
        durSel.appendChild(o);
    });
    durSel.onchange = () => saveFn(key + '_duration', durSel.value);
    durRow.appendChild(durSel);
    c.appendChild(durRow);
    const delayRow = document.createElement('div');
    delayRow.className = 'pb-control';
    delayRow.style.display = (value && value !== 'none') ? '' : 'none';
    const delayLabel = document.createElement('label');
    delayLabel.textContent = 'Delay';
    delayRow.appendChild(delayLabel);
    const delaySel = document.createElement('select');
    ['0s','0.1s','0.2s','0.3s','0.5s'].forEach(opt => {
        const o = document.createElement('option');
        o.value = opt; o.textContent = opt;
        o.selected = opt === '0s';
        delaySel.appendChild(o);
    });
    delaySel.onchange = () => saveFn(key + '_delay', delaySel.value);
    delayRow.appendChild(delaySel);
    c.appendChild(delayRow);
    sel.onchange = () => {
        saveFn(key, sel.value);
        const show = sel.value && sel.value !== 'none';
        durRow.style.display = show ? '' : 'none';
        delayRow.style.display = show ? '' : 'none';
    };
    return c;
}

function createTextShadowInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const shadow = typeof value === 'string' ? value : '';
    const parse = (v) => {
        const m = v.match(/(-?\d+\.?\d*)px\s+(-?\d+\.?\d*)px\s+(-?\d+\.?\d*)px\s+(#[0-9a-fA-F]+|rgba?\(.+?\))/);
        return m ? { x: m[1], y: m[2], blur: m[3], color: m[4] } : { x: '0px', y: '2px', blur: '4px', color: 'rgba(0,0,0,0.3)' };
    };
    const s = parse(shadow);
    const defs = [
        { fk: 'x', label: 'Horizontal', type: 'text' },
        { fk: 'y', label: 'Vertical', type: 'text' },
        { fk: 'blur', label: 'Blur', type: 'text' },
        { fk: 'color', label: 'Color', type: 'color' },
    ];
    const readAll = () => {
        const inputs = c.querySelectorAll('[data-fk]');
        const vals = {};
        inputs.forEach(i => vals[i.dataset.fk] = i.value);
        return `${vals.x || '0px'} ${vals.y || '2px'} ${vals.blur || '4px'} ${vals.color || 'rgba(0,0,0,0.3)'}`;
    };
    defs.forEach(({ fk, label, type }) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        const inp = document.createElement('input');
        inp.type = type; inp.value = s[fk] || '';
        inp.dataset.fk = fk;
        if (fk === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
        inp.oninput = () => _debouncedStyle(key, elementId, () => updateStyle(key, readAll(), elementId));
        row.appendChild(inp);
        c.appendChild(row);
    });
    return c;
}

function createTextStrokeInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:.25rem';
    const stroke = typeof value === 'string' ? value : '';
    const parse = (v) => {
        const m = v.match(/(-?\d+\.?\d*)px\s+(#[0-9a-fA-F]+|rgba?\(.+?\))/);
        return m ? { width: m[1], color: m[2] } : { width: '0px', color: '#000000' };
    };
    const s = parse(stroke);
    const defs = [
        { fk: 'width', label: 'Width', type: 'text' },
        { fk: 'color', label: 'Color', type: 'color' },
    ];
    const readAll = () => {
        const inputs = c.querySelectorAll('[data-fk]');
        const vals = {};
        inputs.forEach(i => vals[i.dataset.fk] = i.value);
        const w = parseFloat(vals.width) || 0;
        return w > 0 ? `${vals.width} ${vals.color}` : '';
    };
    defs.forEach(({ fk, label, type }) => {
        const row = document.createElement('div');
        row.className = 'pb-control';
        const lbl = document.createElement('label');
        lbl.textContent = label;
        row.appendChild(lbl);
        const inp = document.createElement('input');
        inp.type = type; inp.value = s[fk] || '';
        inp.dataset.fk = fk;
        if (fk === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
        inp.oninput = () => _debouncedStyle(key, elementId, () => updateStyle(key, readAll(), elementId));
        row.appendChild(inp);
        c.appendChild(row);
    });
    return c;
}

function createColumnWidthInput(key, value, elementId) {
    const c = document.createElement('div');
    c.style.cssText = 'display:flex;flex-direction:column;gap:4px;';
    const numVal = typeof value === 'number' ? value : (typeof value === 'string' && value.startsWith('col-') ? Math.round(parseInt(value.replace('col-', '')) / 12 * 100) : 33.33);
    const pct = Math.min(100, Math.max(0, numVal));
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;gap:8px;';
    const slider = document.createElement('input');
    slider.type = 'range'; slider.min = '1'; slider.max = '100'; slider.value = pct;
    slider.style.cssText = 'flex:1;accent-color:var(--pb-primary);cursor:pointer;';
    const label = document.createElement('span');
    label.style.cssText = 'font-size:.75rem;color:var(--pb-text);min-width:38px;text-align:right;font-weight:600;';
    label.textContent = pct + '%';
    slider.oninput = () => {
        label.textContent = slider.value + '%';
    };
    slider.onchange = () => {
        const newPct = parseInt(slider.value);
        const colNum = Math.max(1, Math.round(newPct / 100 * 12));
        updateSetting('column_width', `col-${colNum}`, elementId, true);
    };
    row.append(slider, label);
    c.appendChild(row);
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

function resolveResponsiveKey(key) {
    const device = state.responsiveTab || 'desktop';
    if (device !== 'desktop' && RESPONSIVE_KEYS.includes(key)) {
        return key + '_' + device;
    }
    return key;
}

function updateSetting(key, value, elementId, reload = true) {
    const resolvedKey = resolveResponsiveKey(key);
    state.dirty = true;
    if (state.cachedSettings) state.cachedSettings[resolvedKey] = value;
    const settings = {};
    settings[resolvedKey] = value;
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
    const resolvedKey = resolveResponsiveKey(key);
    state.dirty = true;
    if (state.cachedStyles) state.cachedStyles[resolvedKey] = value;
    const styles = {};
    styles[resolvedKey] = value;
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
                const elType = el.dataset.elType;
                if (elType === 'section' || elType === 'column') {
                    const wrapper = el.querySelector('.pb-section-editor, .pb-column-editor');
                    if (wrapper) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(`<div>${data.html}</div>`, 'text/html');
                        const newWrapper = doc.body.firstChild.firstElementChild;
                        if (newWrapper) {
                            const existingChildren = el.querySelector('.pb-el-children');
                            const sectionContent = newWrapper.querySelector('.pb-section-content, .pb-column-content');
                            if (existingChildren && sectionContent) {
                                sectionContent.appendChild(existingChildren);
                            }
                            wrapper.replaceWith(newWrapper);
                        }
                    } else {
                        el.innerHTML = data.html;
                    }
                    renderMath();
                } else {
                    const oldContent = el.querySelector('.pb-el-content');
                    if (oldContent) oldContent.innerHTML = data.html;
                    else el.innerHTML = `<div class="pb-el-content">${data.html}</div>`;
                    renderMath();
                }
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
    const frame = document.getElementById('device-frame');
    const notch = document.getElementById('device-notch');
    const label = document.getElementById('device-label');
    canvas.className = 'pb-canvas';
    document.body.classList.remove('responsive-tablet', 'responsive-mobile');
    if (mode !== 'desktop') {
        canvas.classList.add('is-' + mode);
        document.body.classList.add('responsive-' + mode);
        if (notch) notch.style.display = '';
        if (label) {
            label.style.display = '';
            const sizes = { tablet: 'Tablet — 768px', mobile: 'Mobile — 375px' };
            label.textContent = sizes[mode] || '';
        }
        if (frame) frame.className = 'pb-device-frame ' + mode;
    } else {
        if (notch) notch.style.display = 'none';
        if (label) label.style.display = 'none';
        if (frame) frame.className = 'pb-device-frame';
    }
}

function setResponsiveTab(device) {
    state.responsiveTab = device;
    document.querySelectorAll('.pb-resp-tab').forEach(b => b.classList.remove('active'));
    const btn = document.querySelector(`.pb-resp-tab[data-device="${device}"]`);
    if (btn) btn.classList.add('active');
    if (state.cachedElementId) renderControls();
    setResponsive(device);
}

let _globalSettings = { global_colors: [], global_fonts: [], system_fonts: [] };

function loadGlobalSettings() {
    return apiFetch(`/page-builder/pages/${state.pageId}/global-settings`)
        .then(data => { _globalSettings = data; })
        .catch(() => {});
}

function saveGlobalSettings(colors, fonts) {
    apiFetch(`/page-builder/pages/${state.pageId}/global-settings`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
        body: JSON.stringify({ global_colors: colors, global_fonts: fonts }),
    })
    .then(data => {
        _globalSettings.global_colors = data.global_colors;
        _globalSettings.global_fonts = data.global_fonts;
        toastSuccess('Configuracoes globais salvas!');
    })
    .catch(() => toastError('Falha ao salvar configuracoes globais'));
}

function showSiteSettings() {
    const panel = document.getElementById('settings-empty');
    const form = document.getElementById('settings-form');
    const pageForm = document.getElementById('page-settings-form');
    if (panel) panel.style.display = 'none';
    if (form) form.style.display = 'none';
    if (pageForm) pageForm.style.display = 'none';
    state._prevSelected = state.selectedId;
    if (state.selectedId) { deselectAll(); state.selectedId = null; }
    loadGlobalSettings();
    renderSiteSettings();
}

function renderSiteSettings() {
    const body = document.getElementById('settings-body');
    if (!body) return;
    body.innerHTML = '';
    document.getElementById('settings-title').textContent = 'Configuracoes do Site';
    document.getElementById('settings-type').textContent = 'global';
    const empty = document.getElementById('settings-empty');
    const form = document.getElementById('settings-form');
    if (empty) empty.style.display = 'none';
    if (form) form.style.display = 'block';
    document.getElementById('editor-tabs').style.display = 'none';
    document.getElementById('responsive-tabs').style.display = 'none';

    const colors = _globalSettings.global_colors || [];
    const fonts = _globalSettings.global_fonts || [];
    const systemFonts = _globalSettings.system_fonts || [];

    const colorsSection = document.createElement('div');
    colorsSection.className = 'pb-settings-section';
    colorsSection.innerHTML = '<div class="pb-settings-section-title">Cores Globais</div>';

    const colorList = document.createElement('div');
    colorList.id = 'global-colors-list';
    colorList.style.cssText = 'display:flex;flex-direction:column;gap:6px;margin-bottom:8px;';
    colors.forEach((c, i) => {
        colorList.appendChild(createGlobalColorRow(c, i, colors));
    });
    colorsSection.appendChild(colorList);

    const addColorBtn = document.createElement('button');
    addColorBtn.className = 'pb-btn-add-global';
    addColorBtn.textContent = '+ Adicionar Cor Global';
    addColorBtn.style.cssText = 'width:100%;padding:.4rem;background:rgba(99,102,241,.1);border:1px dashed rgba(99,102,241,.3);color:var(--pb-accent);border-radius:6px;cursor:pointer;font-size:.75rem;';
    addColorBtn.onclick = () => {
        colors.push({ name: 'Nova Cor', value: '#6366f1', system: false });
        saveGlobalSettings(colors, fonts);
        renderSiteSettings();
    };
    colorsSection.appendChild(addColorBtn);
    body.appendChild(colorsSection);

    const fontsSection = document.createElement('div');
    fontsSection.className = 'pb-settings-section';
    fontsSection.innerHTML = '<div class="pb-settings-section-title">Fontes Globais</div>';

    const fontList = document.createElement('div');
    fontList.id = 'global-fonts-list';
    fontList.style.cssText = 'display:flex;flex-direction:column;gap:6px;margin-bottom:8px;';
    fonts.forEach((f, i) => {
        fontList.appendChild(createGlobalFontRow(f, i, fonts, systemFonts));
    });
    fontsSection.appendChild(fontList);

    const addFontBtn = document.createElement('button');
    addFontBtn.className = 'pb-btn-add-global';
    addFontBtn.textContent = '+ Adicionar Fonte Global';
    addFontBtn.style.cssText = 'width:100%;padding:.4rem;background:rgba(99,102,241,.1);border:1px dashed rgba(99,102,241,.3);color:var(--pb-accent);border-radius:6px;cursor:pointer;font-size:.75rem;';
    addFontBtn.onclick = () => {
        fonts.push({ name: 'Nova Fonte', family: 'Inter, sans-serif' });
        saveGlobalSettings(colors, fonts);
        renderSiteSettings();
    };
    fontsSection.appendChild(addFontBtn);
    body.appendChild(fontsSection);
}

function createGlobalColorRow(color, index, colors) {
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;gap:6px;background:var(--pb-surface2);border-radius:6px;padding:6px 8px;';
    const swatch = document.createElement('input');
    swatch.type = 'color'; swatch.value = color.value || '#6366f1';
    swatch.style.cssText = 'width:28px;height:28px;border:none;border-radius:4px;cursor:pointer;padding:0;';
    swatch.onchange = () => { colors[index].value = swatch.value; saveGlobalSettings(colors, _globalSettings.global_fonts); };
    const nameInput = document.createElement('input');
    nameInput.value = color.name || '';
    nameInput.placeholder = 'Nome';
    nameInput.style.cssText = 'flex:1;background:var(--pb-surface3);border:1px solid var(--pb-border);border-radius:4px;padding:4px 8px;color:var(--pb-text);font-size:.75rem;';
    nameInput.onchange = () => { colors[index].name = nameInput.value; saveGlobalSettings(colors, _globalSettings.global_fonts); };
    const del = document.createElement('button');
    del.textContent = '×'; del.title = 'Remover';
    del.style.cssText = 'background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1rem;padding:0 4px;';
    del.onclick = () => { colors.splice(index, 1); saveGlobalSettings(colors, _globalSettings.global_fonts); renderSiteSettings(); };
    row.append(swatch, nameInput, del);
    return row;
}

function createGlobalFontRow(font, index, fonts, systemFonts) {
    const row = document.createElement('div');
    row.style.cssText = 'display:flex;align-items:center;gap:6px;background:var(--pb-surface2);border-radius:6px;padding:6px 8px;';
    const nameInput = document.createElement('input');
    nameInput.value = font.name || '';
    nameInput.placeholder = 'Nome';
    nameInput.style.cssText = 'flex:1;background:var(--pb-surface3);border:1px solid var(--pb-border);border-radius:4px;padding:4px 8px;color:var(--pb-text);font-size:.75rem;';
    nameInput.onchange = () => { fonts[index].name = nameInput.value; saveGlobalSettings(_globalSettings.global_colors, fonts); };
    const sel = document.createElement('select');
    sel.style.cssText = 'flex:1;background:var(--pb-surface3);border:1px solid var(--pb-border);border-radius:4px;padding:4px 8px;color:var(--pb-text);font-size:.75rem;';
    systemFonts.forEach(sf => {
        const opt = document.createElement('option');
        opt.value = sf.family; opt.textContent = sf.name;
        if (sf.family === font.family) opt.selected = true;
        sel.appendChild(opt);
    });
    sel.onchange = () => { fonts[index].family = sel.value; saveGlobalSettings(_globalSettings.global_colors, fonts); };
    const del = document.createElement('button');
    del.textContent = '×'; del.title = 'Remover';
    del.style.cssText = 'background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1rem;padding:0 4px;';
    del.onclick = () => { fonts.splice(index, 1); saveGlobalSettings(_globalSettings.global_colors, fonts); renderSiteSettings(); };
    row.append(nameInput, sel, del);
    return row;
}

function hideSiteSettings() {
    const form = document.getElementById('settings-form');
    const empty = document.getElementById('settings-empty');
    const pageForm = document.getElementById('page-settings-form');
    const tabs = document.getElementById('editor-tabs');
    const respTabs = document.getElementById('responsive-tabs');
    if (form) { form.style.display = ''; form.classList.remove('active'); }
    if (pageForm) pageForm.classList.remove('active');
    if (tabs) tabs.style.display = '';
    if (respTabs) respTabs.style.display = '';
    if (state._prevSelected) { selectElement(state._prevSelected); }
    else { if (form) form.style.display = 'none'; if (empty) empty.style.display = ''; }
}

function showRevisionHistory() {
    const panel = document.getElementById('settings-empty');
    const form = document.getElementById('settings-form');
    const pageForm = document.getElementById('page-settings-form');
    if (panel) panel.style.display = 'none';
    if (form) form.style.display = 'none';
    if (pageForm) pageForm.style.display = 'none';
    state._prevSelected = state.selectedId;
    if (state.selectedId) { deselectAll(); state.selectedId = null; }

    document.getElementById('settings-title').textContent = 'Historico de Revisoes';
    document.getElementById('settings-type').textContent = 'revisions';
    document.getElementById('editor-tabs').style.display = 'none';
    document.getElementById('responsive-tabs').style.display = 'none';
    form.style.display = 'block';

    const body = document.getElementById('settings-body');
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2)">Carregando...</div>';

    apiFetch(`/page-builder/pages/${state.pageId}/revisions`)
        .then(data => {
            const revisions = data.revisions?.data || data.revisions || [];
            body.innerHTML = '';
            if (revisions.length === 0) {
                body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2)">Nenhuma revisao encontrada</div>';
                return;
            }
            revisions.forEach(rev => {
                const card = document.createElement('div');
                card.style.cssText = 'background:var(--pb-surface2);border-radius:8px;padding:10px;margin-bottom:8px;border:1px solid var(--pb-border);';
                const date = new Date(rev.created_at).toLocaleString('pt-BR');
                const userName = rev.user?.name || 'Sistema';
                const version = rev.version || 'v1';
                card.innerHTML = `
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                        <span style="font-weight:600;font-size:.8rem;color:var(--pb-text);">${version}</span>
                        <span style="font-size:.65rem;color:var(--pb-text2);">${date}</span>
                    </div>
                    <div style="font-size:.7rem;color:var(--pb-text2);margin-bottom:6px;">por ${userName}</div>
                    <div style="display:flex;gap:6px;">
                        <button class="pb-rev-restore" data-rev-id="${rev.id}" style="flex:1;padding:4px 8px;background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.3);color:var(--pb-success);border-radius:4px;cursor:pointer;font-size:.7rem;">Restaurar</button>
                        <button class="pb-rev-diff" data-rev-id="${rev.id}" style="flex:1;padding:4px 8px;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.3);color:var(--pb-accent);border-radius:4px;cursor:pointer;font-size:.7rem;">Ver Diff</button>
                    </div>
                `;
                card.querySelector('.pb-rev-restore').onclick = () => {
                    if (!confirm(`Restaurar a revisao ${version}?`)) return;
                    apiFetch(`/page-builder/pages/${state.pageId}/revisions/${rev.id}/restore`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': state.csrf },
                    })
                    .then(() => { toastSuccess('Revisao restaurada! Recarregando...'); setTimeout(() => location.reload(), 800); })
                    .catch(() => toastError('Falha ao restaurar revisao'));
                };
                card.querySelector('.pb-rev-diff').onclick = () => {
                    showRevisionDiff(rev.id);
                };
                body.appendChild(card);
            });
        })
        .catch(() => { body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-danger)">Erro ao carregar revisoes</div>'; });
}

function showRevisionDiff(revId) {
    const body = document.getElementById('settings-body');
    if (!body) return;
    body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2)">Carregando diff...</div>';
    apiFetch(`/page-builder/pages/${state.pageId}/revisions/${revId}/diff`)
        .then(data => {
            body.innerHTML = '';
            const current = data.current || {};
            const previous = data.previous || {};
            const version = data.version || '?';
            const prevVersion = data.previous_version || 'Inicio';

            const header = document.createElement('div');
            header.style.cssText = 'display:flex;align-items:center;gap:8px;margin-bottom:12px;';
            header.innerHTML = `<button id="diff-back" style="background:none;border:none;color:var(--pb-accent);cursor:pointer;font-size:.8rem;">&#8592; Voltar</button><span style="font-weight:600;font-size:.85rem;">Diff: ${prevVersion} → ${version}</span>`;
            body.appendChild(header);
            document.getElementById('diff-back').onclick = () => showRevisionHistory();

            const diffContainer = document.createElement('div');
            diffContainer.style.cssText = 'background:var(--pb-surface2);border-radius:8px;padding:12px;font-family:monospace;font-size:.7rem;max-height:400px;overflow-y:auto;';

            const prevContent = JSON.stringify(previous.content || [], null, 2);
            const currContent = JSON.stringify(current.content || [], null, 2);
            const prevLines = prevContent.split('\n');
            const currLines = currContent.split('\n');
            const maxLines = Math.max(prevLines.length, currLines.length);

            for (let i = 0; i < maxLines; i++) {
                const pl = prevLines[i] || '';
                const cl = currLines[i] || '';
                const line = document.createElement('div');
                if (pl !== cl) {
                    if (pl && !cl) {
                        line.style.cssText = 'background:rgba(239,68,68,.15);color:var(--pb-danger);padding:1px 6px;border-radius:2px;';
                        line.textContent = `- ${pl}`;
                    } else if (!pl && cl) {
                        line.style.cssText = 'background:rgba(34,197,94,.15);color:var(--pb-success);padding:1px 6px;border-radius:2px;';
                        line.textContent = `+ ${cl}`;
                    } else {
                        line.style.cssText = 'background:rgba(245,158,11,.15);color:var(--pb-warning);padding:1px 6px;border-radius:2px;';
                        line.textContent = `~ ${cl}`;
                    }
                } else {
                    line.style.cssText = 'color:var(--pb-text2);padding:1px 6px;';
                    line.textContent = `  ${pl}`;
                }
                diffContainer.appendChild(line);
            }
            body.appendChild(diffContainer);
        })
        .catch(() => { body.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-danger)">Erro ao carregar diff</div>'; });
}

function switchTab(tab) {
    document.querySelectorAll('.pb-panel-tab').forEach(t => t.classList.remove('active'));
    document.querySelector(`.pb-panel-tab[data-tab="${tab}"]`).classList.add('active');
    document.getElementById('panel-widgets').style.display = tab === 'widgets' ? '' : 'none';
    document.getElementById('panel-navigator').style.display = tab === 'navigator' ? '' : 'none';
    document.getElementById('panel-structure').style.display = tab === 'structure' ? '' : 'none';
    document.getElementById('panel-layouts').style.display = tab === 'layouts' ? '' : 'none';
    if (tab === 'layouts') loadTemplates();
    if (tab === 'navigator') renderNavigator(state);
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
        const isInput = e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT' || e.target.isContentEditable;
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); editor.undo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) { e.preventDefault(); editor.redo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); editor.redo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); editor.save(); }
        if ((e.ctrlKey || e.metaKey) && e.key === '0') { e.preventDefault(); editor.zoomReset(); }
        if ((e.ctrlKey || e.metaKey) && e.key === '=') { e.preventDefault(); editor.zoomIn(); }
        if ((e.ctrlKey || e.metaKey) && e.key === '-') { e.preventDefault(); editor.zoomOut(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') { e.preventDefault(); editor.duplicateSelected(); }
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') { e.preventDefault(); editor.copyStyles(); }
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'V') { e.preventDefault(); editor.pasteStyles(); }
        if (e.key === 'F11') { e.preventDefault(); editor.toggleFullscreen(); }
        if (e.key === 'Escape' && state.isFullscreen) { editor.toggleFullscreen(); }
        else if (e.key === 'Escape' && state.multiSelected && state.multiSelected.size > 0) { editor.clearMultiSelect(); }
        else if (e.key === 'Escape' && document.getElementById('pb-finder-overlay')) { document.getElementById('pb-finder-overlay').remove(); }
        else if (e.key === 'Escape' && state.selectedId) { deselectAll(); state.selectedId = null; }
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') { e.preventDefault(); toggleFinder(); }
        if (e.key === 'Delete' && state.selectedId) { editor.deleteSelected(); }
        if (!isInput && state.selectedId) {
            if (e.key === 'Tab') { e.preventDefault(); navigateElements(e.shiftKey ? -1 : 1); }
        }
    });
}

function deselectAll() {
    document.querySelectorAll('.pb-el.selected, .pb-el.multi-selected').forEach(el => {
        el.classList.remove('selected');
        el.classList.remove('multi-selected');
    });
    document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
    document.getElementById('settings-empty').style.display = '';
    document.getElementById('settings-form').classList.remove('active');
    document.getElementById('page-settings-form').classList.remove('active');
}

function navigateElements(direction) {
    const els = Array.from(document.querySelectorAll('#canvas-dropzone .pb-el'));
    if (!els.length) return;
    const currentIdx = els.findIndex(el => el.dataset.elId === String(state.selectedId));
    let nextIdx = currentIdx + direction;
    if (nextIdx < 0) nextIdx = els.length - 1;
    if (nextIdx >= els.length) nextIdx = 0;
    const nextId = els[nextIdx].dataset.elId;
    if (nextId) selectElement(parseInt(nextId));
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
    document.getElementById('settings-form').style.display = '';
    document.getElementById('page-settings-form').classList.add('active');
    document.getElementById('editor-tabs').style.display = '';
    document.getElementById('responsive-tabs').style.display = '';
    renderPageSettings();
}

function hidePageSettings() {
    document.getElementById('page-settings-form').classList.remove('active');
    document.getElementById('settings-empty').style.display = '';
}

function copyStyles() {
    if (!state.selectedId) {
        showToast('Selecione um elemento para copiar estilos', 'error');
        return;
    }
    const styles = state.cachedStyles || {};
    if (Object.keys(styles).length === 0) {
        showToast('Nenhum estilo para copiar', 'error');
        return;
    }
    state._styleClipboard = JSON.parse(JSON.stringify(styles));
    showToast('Estilos copiados! Selecione outro elemento e use Ctrl+Shift+V para colar.', 'success');
}

function pasteStyles() {
    if (!state.selectedId) {
        showToast('Selecione um elemento para colar estilos', 'error');
        return;
    }
    if (!state._styleClipboard || Object.keys(state._styleClipboard).length === 0) {
        showToast('Nenhum estilo copiado. Use Ctrl+Shift+C primeiro.', 'error');
        return;
    }
    const elId = state.selectedId;
    const styles = state._styleClipboard;
    let applied = 0;
    const applyNext = (keys) => {
        if (keys.length === 0) {
            if (applied > 0) {
                showToast(`${applied} estilo(s) aplicado(s)!`, 'success');
                loadElements();
                setTimeout(() => selectElement(elId), 200);
            }
            return;
        }
        const [key, ...rest] = keys;
        const val = styles[key];
        if (val !== undefined && val !== '' && val !== null) {
            applied++;
            apiFetch(`/page-builder/elements/${elId}/styles`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
                body: JSON.stringify({ styles: { [key]: val } }),
            }).then(() => applyNext(rest)).catch(() => applyNext(rest));
        } else {
            applyNext(rest);
        }
    };
    applyNext(Object.keys(styles));
}

function saveAsTemplate() {
    const name = prompt('Nome do template:', document.title.replace('Editando: ', '').replace(' - PageBuilder', ''));
    if (!name) return;
    apiFetch('/page-builder/pages/' + state.pageId + '/export')
        .then(r => r.blob())
        .then(blob => {
            const reader = new FileReader();
            reader.onload = () => {
                try {
                    const data = JSON.parse(reader.result);
                    data.template_name = name;
                    const blob2 = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob2);
                    const a = document.createElement('a');
                    a.href = url; a.download = name.toLowerCase().replace(/\s+/g, '-') + '.json';
                    document.body.appendChild(a); a.click(); a.remove();
                    URL.revokeObjectURL(url);
                    showToast('Template exportado! Use Importar HTML para reutilizar.', 'success');
                } catch(e) { showToast('Erro ao criar template', 'error'); }
            };
            reader.readAsText(blob);
        })
        .catch(() => showToast('Erro ao exportar template', 'error'));
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

function uploadVideoFile(file, callback) {
    const formData = new FormData();
    formData.append('video', file);
    showToast('Enviando video...', 'info');
    apiFetch('/page-builder/upload-video', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
        body: formData,
    })
    .then(data => {
        if (data.url) { toastSuccess('Video enviado!'); callback(data.url); }
        else toastError('Falha ao enviar video');
    })
    .catch(() => toastError('Falha ao enviar video'));
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

function collabJoin() {
    apiFetch(`/page-builder/pages/${state.pageId}/collab/join`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
    }).catch(() => {});
}

function collabLeave() {
    if (!state.pageId) return;
    apiFetch(`/page-builder/pages/${state.pageId}/collab/leave`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
    }).catch(() => {});
}

function collabHeartbeat() {
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

function bindCollabPresence() {
    window.addEventListener('beforeunload', () => collabLeave());
}

function lockElement(elementId) {
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

function unlockElement(elementId) {
    return apiFetch(`/page-builder/pages/${state.pageId}/elements/${elementId}/unlock`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': state.csrf },
    }).catch(() => {});
}

function bindWidgetSearch() {
    const input = document.getElementById('widget-search-input');
    if (!input) return;
    const items = document.querySelectorAll('#panel-widgets .pb-widget-item');
    const groups = document.querySelectorAll('#panel-widgets .pb-widget-group');
    const empty = document.getElementById('widget-search-empty');
    input.addEventListener('input', () => {
        const q = input.value.toLowerCase().trim();
        let found = 0;
        items.forEach(item => {
            const search = (item.dataset.search || '') + ' ' + (item.dataset.type || '');
            const match = !q || search.toLowerCase().includes(q);
            item.classList.toggle('pb-widget-hidden', !match);
            if (match) found++;
        });
        groups.forEach(g => {
            const visible = g.querySelectorAll('.pb-widget-item:not(.pb-widget-hidden)').length;
            g.classList.toggle('pb-widget-hidden', visible === 0);
        });
        if (empty) empty.style.display = found === 0 ? '' : 'none';
    });
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') { input.value = ''; input.dispatchEvent(new Event('input')); input.blur(); }
    });
}

function bindResizablePanels() {
    const layout = document.querySelector('.pb-layout');
    if (!layout) return;
    const panels = layout.querySelectorAll('.pb-panel');
    panels.forEach(panel => {
        const handle = document.createElement('div');
        handle.className = 'pb-panel-resize';
        const isRight = panel.classList.contains('pb-panel-right');
        if (!isRight) panel.appendChild(handle);
        else panel.insertBefore(handle, panel.firstChild);
        let startX, startW;
        const onMove = (e) => {
            const dx = e.clientX - startX;
            const newW = isRight ? startW - dx : startW + dx;
            panel.style.width = Math.max(200, Math.min(500, newW)) + 'px';
        };
        const onUp = () => {
            handle.classList.remove('active');
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        };
        handle.addEventListener('mousedown', (e) => {
            e.preventDefault();
            startX = e.clientX;
            startW = panel.offsetWidth;
            handle.classList.add('active');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    });
}

function refreshMultiSelect() {
    document.querySelectorAll('.pb-el').forEach(el => {
        el.classList.remove('multi-selected');
    });
    if (state.multiSelected && state.multiSelected.size > 0) {
        state.multiSelected.forEach(id => {
            const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
            if (el) el.classList.add('multi-selected');
        });
        showMultiToolbar();
    } else {
        removeMultiToolbar();
    }
}

function showMultiToolbar() {
    removeMultiToolbar();
    const count = state.multiSelected ? state.multiSelected.size : 0;
    if (count < 2) return;
    const bar = document.createElement('div');
    bar.className = 'pb-multi-toolbar';
    bar.id = 'pb-multi-toolbar';
    bar.innerHTML = `
        <span class="pb-multi-count">${count} selecionados</span>
        <button class="pb-multi-btn" onclick="editor.duplicateSelected()">&#128203; Duplicar</button>
        <button class="pb-multi-btn danger" onclick="editor.deleteSelected()">&#128465; Excluir</button>
        <button class="pb-multi-btn" onclick="editor.clearMultiSelect()">&#10005; Limpar</button>
    `;
    document.body.appendChild(bar);
}

function removeMultiToolbar() {
    const bar = document.getElementById('pb-multi-toolbar');
    if (bar) bar.remove();
}

editor.duplicateSelected = function() {
    if (!state.multiSelected || state.multiSelected.size === 0) return;
    const ids = [...state.multiSelected];
    Promise.all(ids.map(id =>
        apiFetch(`/page-builder/elements/${id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': state.csrf } })
    )).then(() => { state.multiSelected = null; removeMultiToolbar(); loadElements(); })
     .catch(() => toastError('Falha ao duplicar elementos'));
};

editor.deleteSelected = function() {
    if (state.multiSelected && state.multiSelected.size > 0) {
        if (!confirm(`Excluir ${state.multiSelected.size} elementos?`)) return;
        const ids = [...state.multiSelected];
        Promise.all(ids.map(id =>
            apiFetch(`/page-builder/elements/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': state.csrf } })
        )).then(() => { state.multiSelected = null; removeMultiToolbar(); loadElements(); })
         .catch(() => toastError('Falha ao excluir elementos'));
    } else if (state.selectedId) {
        deleteElement(state.selectedId);
    }
};

editor.clearMultiSelect = function() {
    state.multiSelected = null;
    document.querySelectorAll('.pb-el.multi-selected').forEach(el => el.classList.remove('multi-selected'));
    removeMultiToolbar();
};

function toggleFinder() {
    const existing = document.getElementById('pb-finder-overlay');
    if (existing) { existing.remove(); return; }
    const overlay = document.createElement('div');
    overlay.id = 'pb-finder-overlay';
    overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:999999;display:flex;justify-content:center;padding-top:15vh;backdrop-filter:blur(4px);animation:fadeIn .15s';
    const modal = document.createElement('div');
    modal.style.cssText = 'width:480px;max-height:400px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:12px;overflow:hidden;box-shadow:0 16px 48px rgba(0,0,0,.4);display:flex;flex-direction:column;animation:tourPop .2s';
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.placeholder = 'Buscar widgets, configuracoes, acoes...';
    searchInput.style.cssText = 'width:100%;padding:14px 16px;background:transparent;border:none;border-bottom:1px solid var(--pb-border);color:var(--pb-text);font-size:.9rem;outline:none';
    const results = document.createElement('div');
    results.style.cssText = 'flex:1;overflow-y:auto;padding:4px';
    const actions = [
        { label: 'Salvar', icon: '&#128190;', action: () => editor.save() },
        { label: 'Publicar', icon: '&#128227;', action: () => editor.publish() },
        { label: 'Desfazer', icon: '&#8617;', action: () => editor.undo() },
        { label: 'Refazer', icon: '&#8618;', action: () => editor.redo() },
        { label: 'Navigator', icon: '&#9776;', action: () => editor.toggleNavigator() },
        { label: 'Exportar JSON', icon: '&#128230;', action: () => editor.exportPage() },
        { label: 'Copiar HTML', icon: '&#128196;', action: () => editor.copyHtml() },
        { label: 'Importar HTML', icon: '&#128229;', action: () => editor.importHtml() },
        { label: 'Configuracoes da Pagina', icon: '&#9881;', action: () => editor.showPageSettings() },
        { label: 'Configuracoes do Site', icon: '&#127968;', action: () => editor.showSiteSettings() },
        { label: 'Historico de Revisoes', icon: '&#128338;', action: () => editor.showRevisionHistory() },
        { label: 'Modo Desktop', icon: '&#128187;', action: () => editor.setResponsive('desktop') },
        { label: 'Modo Tablet', icon: '&#128241;', action: () => editor.setResponsive('tablet') },
        { label: 'Modo Mobile', icon: '&#128241;', action: () => editor.setResponsive('mobile') },
        { label: 'Zoom 100%', icon: '&#128269;', action: () => editor.zoomReset() },
        { label: 'Tela Cheia', icon: '&#9974;', action: () => editor.toggleFullscreen() },
    ];
    const widgetTypes = [
        { type: 'heading', label: 'Titulo' }, { type: 'text', label: 'Texto' },
        { type: 'image', label: 'Imagem' }, { type: 'button', label: 'Botao' },
        { type: 'video', label: 'Video' }, { type: 'section', label: 'Secao' },
        { type: 'column', label: 'Coluna' }, { type: 'divider', label: 'Divisor' },
        { type: 'spacer', label: 'Espacador' }, { type: 'icon', label: 'Icone' },
        { type: 'gallery', label: 'Galeria' }, { type: 'form', label: 'Formulario' },
        { type: 'tabs', label: 'Abas' }, { type: 'accordion', label: 'Accordion' },
        { type: 'callout', label: 'Callout' }, { type: 'table', label: 'Tabela' },
        { type: 'math', label: 'Matematica' }, { type: 'counter', label: 'Contador' },
        { type: 'progress_bar', label: 'Barra de Progresso' }, { type: 'social_icons', label: 'Social Icons' },
        { type: 'icon_box', label: 'Icon Box' }, { type: 'image_box', label: 'Image Box' },
        { type: 'testimonial', label: 'Testimonial' }, { type: 'price_table', label: 'Price Table' },
        { type: 'countdown', label: 'Countdown' }, { type: 'google_maps', label: 'Google Maps' },
        { type: 'carousel', label: 'Carrossel' },
    ];
    const renderResults = (q) => {
        results.innerHTML = '';
        const query = q.toLowerCase().trim();
        const matchedActions = query ? actions.filter(a => a.label.toLowerCase().includes(query)) : actions;
        if (matchedActions.length) {
            const sec = document.createElement('div');
            sec.style.cssText = 'padding:6px 10px;font-size:.65rem;color:var(--pb-text2);text-transform:uppercase;letter-spacing:.5px;font-weight:600';
            sec.textContent = 'Acoes';
            results.appendChild(sec);
            matchedActions.forEach(a => {
                const item = document.createElement('div');
                item.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 10px;cursor:pointer;border-radius:6px;font-size:.82rem;transition:background .1s';
                item.innerHTML = `<span style="width:20px;text-align:center;opacity:.6">${a.icon}</span><span>${a.label}</span>`;
                item.onmouseenter = () => item.style.background = 'var(--pb-surface2)';
                item.onmouseleave = () => item.style.background = 'transparent';
                item.onclick = () => { overlay.remove(); a.action(); };
                results.appendChild(item);
            });
        }
        const matchedWidgets = query ? widgetTypes.filter(w => w.label.toLowerCase().includes(query) || w.type.includes(query)) : [];
        if (matchedWidgets.length) {
            const sec = document.createElement('div');
            sec.style.cssText = 'padding:6px 10px;font-size:.65rem;color:var(--pb-text2);text-transform:uppercase;letter-spacing:.5px;font-weight:600;margin-top:4px';
            sec.textContent = 'Widgets';
            results.appendChild(sec);
            matchedWidgets.forEach(w => {
                const item = document.createElement('div');
                item.style.cssText = 'display:flex;align-items:center;gap:8px;padding:8px 10px;cursor:pointer;border-radius:6px;font-size:.82rem;transition:background .1s';
                item.innerHTML = `<span style="width:20px;text-align:center;opacity:.6">&#10010;</span><span>${w.label}</span><span style="font-size:.65rem;color:var(--pb-text2);margin-left:auto">${w.type}</span>`;
                item.onmouseenter = () => item.style.background = 'var(--pb-surface2)';
                item.onmouseleave = () => item.style.background = 'transparent';
                item.onclick = () => { overlay.remove(); document.querySelector(`.pb-widget-item[data-type="${w.type}"]`)?.scrollIntoView({ behavior: 'smooth', block: 'center' }); };
                results.appendChild(item);
            });
        }
        if (!matchedActions.length && !matchedWidgets.length) {
            results.innerHTML = '<div style="text-align:center;padding:2rem;color:var(--pb-text2);font-size:.82rem">Nenhum resultado encontrado</div>';
        }
    };
    searchInput.addEventListener('input', () => renderResults(searchInput.value));
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') overlay.remove();
        if (e.key === 'Enter') {
            const first = results.querySelector('div[style*="cursor:pointer"]');
            if (first) first.click();
        }
    });
    overlay.onclick = (e) => { if (e.target === overlay) overlay.remove(); };
    modal.appendChild(searchInput);
    modal.appendChild(results);
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    setTimeout(() => { searchInput.focus(); renderResults(''); }, 50);
}

function showColumnStructurePicker(state, sectionElId) {
    const existing = document.getElementById('pb-col-picker');
    if (existing) existing.remove();
    const sectionEl = document.querySelector(`.pb-el[data-el-id="${sectionElId}"]`);
    if (!sectionEl) return;
    const rect = sectionEl.getBoundingClientRect();
    const picker = document.createElement('div');
    picker.id = 'pb-col-picker';
    picker.style.cssText = `position:fixed;top:${rect.top}px;left:${rect.left}px;width:${rect.width}px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:10px;padding:12px;z-index:99999;box-shadow:0 8px 32px rgba(0,0,0,.3);animation:tourPop .2s`;
    const title = document.createElement('div');
    title.style.cssText = 'font-size:.75rem;font-weight:600;color:var(--pb-text2);margin-bottom:8px;text-align:center';
    title.textContent = 'Estrutura de Colunas';
    picker.appendChild(title);
    const layouts = [
        { cols: [50, 50], label: '50/50' },
        { cols: [33.33, 33.33, 33.33], label: '33/33/33' },
        { cols: [25, 25, 25, 25], label: '25/25/25/25' },
        { cols: [33.33, 66.67], label: '33/67' },
        { cols: [66.67, 33.33], label: '67/33' },
        { cols: [20, 60, 20], label: '20/60/20' },
    ];
    const grid = document.createElement('div');
    grid.style.cssText = 'display:grid;grid-template-columns:repeat(3,1fr);gap:6px';
    layouts.forEach(layout => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.style.cssText = 'padding:8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;cursor:pointer;transition:all .15s;display:flex;gap:2px;height:36px';
        layout.cols.forEach(pct => {
            const col = document.createElement('div');
            col.style.cssText = `flex:${pct};background:var(--pb-accent);border-radius:3px;opacity:.6`;
            btn.appendChild(col);
        });
        btn.onmouseenter = () => { btn.style.borderColor = 'var(--pb-accent)'; btn.style.background = 'var(--pb-primary-light)'; };
        btn.onmouseleave = () => { btn.style.borderColor = 'var(--pb-border)'; btn.style.background = 'var(--pb-surface2)'; };
        btn.onclick = () => {
            picker.remove();
            createColumnsForSection(state, sectionElId, layout.cols);
        };
        grid.appendChild(btn);
    });
    picker.appendChild(grid);
    document.body.appendChild(picker);
    const closeHandler = (e) => { if (!picker.contains(e.target)) { picker.remove(); document.removeEventListener('click', closeHandler); } };
    setTimeout(() => document.addEventListener('click', closeHandler), 10);
}

function createColumnsForSection(state, sectionId, colPercentages) {
    const promises = [];
    colPercentages.forEach((pct, idx) => {
        promises.push(
            apiFetch(`/page-builder/pages/${state.pageId}/elements`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': state.csrf },
                body: JSON.stringify({ type: 'column', parent_id: sectionId, settings: { column_width: pct } }),
            })
        );
    });
    Promise.all(promises).then(() => state.loadElements());
}

function initOnboarding() {
    if (localStorage.getItem('pb_onboarded_v2')) return;
    const steps = [
        { title: 'Bem-vindo ao Page Builder!', desc: 'Este é o editor visual. Arraste widgets do painel esquerdo para criar sua página.', target: '.pb-panel:not(.pb-panel-right)', arrow: 'right' },
        { title: 'Canvas', desc: 'Aqui você vê uma prévia ao vivo da sua página. Clique em qualquer elemento para editá-lo.', target: '#canvas', arrow: 'bottom' },
        { title: 'Painel de Configuracoes', desc: 'Selecione um elemento e edite suas configuracoes aqui (Conteudo, Estilo, Avancado).', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Modo Responsivo', desc: 'Teste como sua pagina aparece em Desktop, Tablet e Mobile.', target: '[data-mode="tablet"]', arrow: 'bottom' },
        { title: 'Navigator', desc: 'Use o Navigator para ver a arvore de elementos e reordenar com drag-and-drop.', target: '[data-tab="navigator"]', arrow: 'right' },
        { title: 'Duplicar com Ctrl+D', desc: 'Selecione um elemento e pressione <strong>Ctrl+D</strong> para duplicar rapidamente. Funciona no canvas e no Navigator.', target: '#canvas', arrow: 'bottom' },
        { title: 'Copiar/Colar Estilos', desc: 'Selecione um elemento, pressione <strong>Ctrl+Shift+C</strong> para copiar seus estilos. Selecione outro e pressione <strong>Ctrl+Shift+V</strong> para colar. Tambem disponivel nos menus de contexto (botao direito).', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Shape Dividers', desc: 'Nas configuracoes de uma Section (aba Estilo), adicione divisores de forma (waves, mountains, tilt, etc.) no topo e/ou base. Deixe suas secoes mais profissionais!', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Background Overlay', desc: 'Na aba Estilo de uma Section, configure um overlay semi-transparente sobre o background com cor, opacidade e blend mode. Perfeito para contraste com texto sobre imagens.', target: '.pb-panel-right', arrow: 'left' },
        { title: 'Atalhos de Teclado', desc: '<strong>Ctrl+Z</strong> Desfazer | <strong>Ctrl+Shift+Z</strong> Refazer | <strong>Ctrl+S</strong> Salvar | <strong>Ctrl+D</strong> Duplicar | <strong>Ctrl+Shift+C/V</strong> Copiar/Colar Estilos | <strong>Delete</strong> Excluir | <strong>F11</strong> Tela Cheia', target: '.pb-toolbar', arrow: 'bottom' },
    ];
    let currentStep = 0;
    const overlay = document.createElement('div');
    overlay.className = 'pb-tour-overlay';
    const tooltip = document.createElement('div');
    tooltip.className = 'pb-tour-tooltip';
    document.body.appendChild(overlay);
    document.body.appendChild(tooltip);
    function showStep(idx) {
        if (idx >= steps.length) { finish(); return; }
        const s = steps[idx];
        tooltip.className = 'pb-tour-tooltip arrow-' + s.arrow;
        tooltip.innerHTML = `
            <div class="pb-tour-step">Passo ${idx + 1} de ${steps.length}</div>
            <div class="pb-tour-title">${s.title}</div>
            <div class="pb-tour-desc">${s.desc}</div>
            <div class="pb-tour-actions">
                <button class="pb-tour-btn pb-tour-btn-primary" id="tour-next">${idx === steps.length - 1 ? 'Começar!' : 'Próximo'}</button>
                <button class="pb-tour-skip" id="tour-skip">Pular tour</button>
            </div>
        `;
        const target = document.querySelector(s.target);
        if (target) {
            const r = target.getBoundingClientRect();
            const tw = 300;
            let left = r.left + r.width / 2 - tw / 2;
            let top;
            if (s.arrow === 'bottom') top = r.bottom + 10;
            else if (s.arrow === 'right') { top = r.top + r.height / 2 - 60; left = r.right + 10; }
            else if (s.arrow === 'left') { top = r.top + r.height / 2 - 60; left = r.left - tw - 10; }
            else { top = r.top - 10; }
            left = Math.max(8, Math.min(window.innerWidth - tw - 8, left));
            top = Math.max(8, Math.min(window.innerHeight - 200, top));
            tooltip.style.left = left + 'px';
            tooltip.style.top = top + 'px';
        }
        document.getElementById('tour-next').onclick = () => { currentStep++; showStep(currentStep); };
        document.getElementById('tour-skip').onclick = finish;
    }
    function finish() {
        overlay.remove();
        tooltip.remove();
        localStorage.setItem('pb_onboarded_v2', '1');
    }
    setTimeout(() => showStep(currentStep), 500);
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
    if (!e.target.closest('.pb-el') && !e.target.closest('.pb-structure-item') && !e.target.closest('.pb-settings') && !e.target.closest('.pb-toolbar') && !e.target.closest('.pb-nav-context') && !e.target.closest('.pb-multi-toolbar')) {
        document.querySelectorAll('.pb-el.selected, .pb-el.multi-selected').forEach(el => {
            el.classList.remove('selected');
            el.classList.remove('multi-selected');
        });
        document.querySelectorAll('.pb-structure-item.active').forEach(el => el.classList.remove('active'));
        state.selectedId = null;
        state.multiSelected = null;
        removeMultiToolbar();
        document.getElementById('settings-empty').style.display = '';
        document.getElementById('settings-form').classList.remove('active');
    }
});
