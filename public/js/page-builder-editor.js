const editor = {
    pageId: null,
    selectedId: null,
    history: [],
    historyIndex: -1,
    responsiveMode: 'desktop',
    csrf: '',
    saving: false,
    dirty: false,

    init(pageId, csrfToken) {
        this.pageId = pageId;
        this.csrf = csrfToken;
        this.loadElements();
        this.loadPageData();
        this.bindDragDrop();
        this.bindKeyboard();
        this.bindCanvasDrops();
        this.bindInlineEditing();
        this.autoSave();
    },

    toastError(msg) {
        this.showToast('\u26A0\uFE0F ' + msg, 'error');
    },

    toastSuccess(msg) {
        this.showToast('\u2705 ' + msg, 'success');
    },

    loadPageData() {
        this.showToast('Carregando dados da página...', 'info');
        fetch(`/page-builder/pages/${this.pageId}/data`)
            .then(r => r.json())
            .then(data => { window._pageData = data.page; })
            .catch(() => this.toastError('Falha ao carregar dados da página'));
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
            })
            .catch(() => this.toastError('Falha ao carregar elementos'));
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
            div.dataset.isContainer = el.is_container ? 'true' : 'false';
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
            case 'heading': {
                const tagSizeMap = {h1:'2.2em',h2:'1.8em',h3:'1.4em',h4:'1.15em',h5:'1em',h6:'.85em'};
                const sizeMap = {small:'1.2em',medium:'2.5em',large:'3em',xl:'3.5em',xxl:'4.5em'};
                const fs = s.size && sizeMap[s.size] ? sizeMap[s.size] : (tagSizeMap[s.tag] || '1.8em');
                preview = `<${s.tag || 'h2'} style="text-align:${s.alignment||'left'};color:${s.color||'#333'};font-size:${fs};font-weight:${s.font_weight||'700'};line-height:${s.line_height||'1.4'}">${this.escHtml(s.title||'Heading')}</${s.tag || 'h2'}>`;
                break;
            }
            case 'text': preview = `<div style="text-align:${s.alignment||'left'};color:${s.color||'#666'};font-size:${s.font_size||'16px'};font-weight:${s.font_weight||'400'};line-height:${s.line_height||'1.7'}">${s.content||'<p>Text content</p>'}</div>`; break;
            case 'image':
                if (s.image && s.image.url) preview = `<div style="text-align:${s.alignment||'center'}"><img src="${this.escHtml(s.image.url)}" alt="${this.escHtml(s.image.alt||'')}" style="width:${s.width||'100%'};max-width:${s.max_width||'100%'};height:${s.height||'auto'};object-fit:${s.object_fit||'cover'};border-radius:${s.border_radius||'0px'};opacity:${s.opacity||1}"></div>`;
                else preview = `<div class="pb-image-placeholder" style="text-align:center;padding:2rem;color:#999">Nenhuma imagem selecionada</div>`;
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
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then(data => {
                if (data.error) { console.error('Controls error:', data.error); return; }
                const widget = data.widget;
                const element = data.element;
                document.getElementById('settings-empty').style.display = 'none';
                const sf = document.getElementById('settings-form');
                sf.style.display = '';
                document.getElementById('settings-title').textContent = element.name || widget.label;
                document.getElementById('settings-type').textContent = widget.type;
                this.renderControls(widget.controls || {}, element.settings || {}, id);
            })
            .catch(err => { console.error('loadControls failed:', err); this.toastError('Falha ao carregar controles'); });
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
                inp.placeholder = 'https://...';
                inp.onchange = (e) => this.updateSetting(key, e.target.value, elementId);
                return inp;
            },
            image: () => {
                const container = document.createElement('div');
                container.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                const currentUrl = value && value.url ? value.url : '';
                const dropZone = document.createElement('div');
                dropZone.style.cssText = 'border:2px dashed var(--pb-border);border-radius:8px;padding:1rem;text-align:center;cursor:pointer;transition:all .2s;background:var(--pb-bg);position:relative';
                dropZone.innerHTML = `
                    <div style="font-size:1.5rem;margin-bottom:.35rem;opacity:.5">&#128247;</div>
                    <div style="font-size:.72rem;color:var(--pb-text2)">
                        <strong style="color:var(--pb-accent);cursor:pointer">Clique para selecionar</strong>
                        <br>ou arraste uma imagem aqui
                    </div>
                    <div style="font-size:.65rem;color:var(--pb-text2);margin-top:.3rem">ou cole (Ctrl+V)</div>
                `;
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
                    if (url) {
                        preview.innerHTML = `<img src="${this.escHtml(url)}" style="width:100%;max-height:100px;object-fit:contain;border-radius:4px">`;
                    } else {
                        preview.textContent = 'Nenhuma imagem';
                    }
                };
                const updateSetting = (url) => {
                    const alt = (value && value.alt) || '';
                    const w = (value && value.width) || 800;
                    const h = (value && value.height) || 600;
                    this.updateSetting(key, { url, alt, width: w, height: h }, elementId);
                    updatePreview(url);
                };
                if (currentUrl) updatePreview(currentUrl);
                dropZone.appendChild(fileInput);
                dropZone.onclick = () => fileInput.click();
                fileInput.onchange = () => {
                    const file = fileInput.files[0];
                    if (!file) return;
                    this.uploadImageFile(file, (url) => {
                        updateSetting(url);
                        urlInput.value = url;
                    });
                };
                dropZone.ondragover = (e) => { e.preventDefault(); dropZone.style.borderColor = 'var(--pb-accent)'; dropZone.style.background = 'var(--pb-primary-light)'; };
                dropZone.ondragleave = () => { dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)'; };
                dropZone.ondrop = (e) => {
                    e.preventDefault();
                    dropZone.style.borderColor = 'var(--pb-border)'; dropZone.style.background = 'var(--pb-bg)';
                    const file = e.dataTransfer.files[0];
                    if (file && file.type.startsWith('image/')) {
                        this.uploadImageFile(file, (url) => {
                            updateSetting(url);
                            urlInput.value = url;
                        });
                    }
                };
                if (this._imagePasteHandler) {
                    document.removeEventListener('paste', this._imagePasteHandler);
                }
                this._imagePasteHandler = (e) => {
                    if (!container.isConnected) return;
                    const item = Array.from(e.clipboardData.items).find(i => i.type.startsWith('image/'));
                    if (!item) return;
                    e.preventDefault();
                    const file = item.getAsFile();
                    if (file) {
                        this.uploadImageFile(file, (url) => {
                            updateSetting(url);
                            urlInput.value = url;
                        });
                    }
                };
                document.addEventListener('paste', this._imagePasteHandler);
                urlInput.onchange = () => updateSetting(urlInput.value);
                urlRow.appendChild(urlInput);
                container.appendChild(dropZone);
                container.appendChild(preview);
                container.appendChild(urlRow);
                return container;
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
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(() => this.reloadElement(elementId))
        .catch(err => { console.error('updateSetting failed:', err); this.toastError('Falha ao atualizar configuração'); });
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
            })
            .catch(() => this.toastError('Falha ao recarregar elemento'));
    },

    deleteElement(id) {
        if (!confirm('Excluir este elemento?')) return;
        fetch(`/page-builder/elements/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': this.csrf } })
            .then(r => r.json())
            .then(() => {
                if (this.selectedId === id) { this.selectedId = null; document.getElementById('settings-empty').style.display = ''; document.getElementById('settings-form').style.display = 'none'; }
                this.loadElements();
            })
            .catch(() => this.toastError('Falha ao excluir elemento'));
    },

    deleteSelected() { if (this.selectedId) this.deleteElement(this.selectedId); },

    duplicateElement(id) {
        fetch(`/page-builder/elements/${id}/duplicate`, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf } })
            .then(r => r.json())
            .then(() => this.loadElements())
            .catch(() => this.toastError('Falha ao duplicar elemento'));
    },

    save() {
        if (this.saving) return;
        this.saving = true;
        const overlay = document.createElement('div');
        overlay.className = 'saving-overlay';
        overlay.innerHTML = '<div class="saving-card"><div class="spinner"></div><span class="saving-text">Salvando...</span></div>';
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
            this.toastSuccess('Página salva!');
        })
        .catch(() => { overlay.remove(); this.toastError('Falha ao salvar'); });
    },

    publish() {
        if (!confirm('Publicar esta página?')) return;
        fetch(`/page-builder/pages/${this.pageId}/publish`, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf } })
            .then(r => r.json())
            .then(() => { this.toastSuccess('Página publicada!'); setTimeout(() => location.reload(), 500); })
            .catch(() => this.toastError('Falha ao publicar página'));
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
                document.querySelectorAll('.pb-el.drop-over').forEach(el => el.classList.remove('drop-over'));
                document.querySelectorAll('.pb-el.drop-target').forEach(el => el.classList.remove('drop-target'));
            });
        });
    },

    bindCanvasDrops() {
        const dz = document.getElementById('canvas-dropzone');
        const emptyCanvas = document.getElementById('empty-canvas');

        dz.addEventListener('dragover', e => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            const target = e.target.closest('.pb-el');
            document.querySelectorAll('.pb-el.drop-over').forEach(el => el.classList.remove('drop-over'));
            if (target && target.dataset.isContainer === 'true') target.classList.add('drop-over');
            if (emptyCanvas) emptyCanvas.classList.add('drag-over');
        });

        dz.addEventListener('dragleave', e => {
            const target = e.target.closest('.pb-el');
            if (target) target.classList.remove('drop-over');
            if (emptyCanvas) emptyCanvas.classList.remove('drag-over');
        });

        dz.addEventListener('drop', e => {
            e.preventDefault();
            e.stopPropagation();
            document.querySelectorAll('.pb-el.drop-over').forEach(el => el.classList.remove('drop-over'));
            document.querySelectorAll('.pb-el.drop-target').forEach(el => el.classList.remove('drop-target'));
            if (emptyCanvas) emptyCanvas.classList.remove('drag-over');
            const type = e.dataTransfer.getData('text/plain');
            if (!type) return;
            let parentId = null;
            const target = e.target.closest('.pb-el');
            if (target) {
                if (target.dataset.isContainer === 'true') {
                    parentId = target.dataset.elId;
                } else {
                    this.toastError('Este widget não aceita outros widgets dentro dele');
                    return;
                }
            }
            this.showToast('Adicionando ' + type + '...', 'info');
            fetch(`/page-builder/pages/${this.pageId}/elements`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({ type, parent_id: parentId }),
            })
            .then(r => r.json())
            .then(() => this.loadElements())
            .catch(() => this.toastError('Falha ao adicionar elemento'));
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

    showToast(msg, type) {
        const existing = document.querySelectorAll('.pb-toast');
        existing.forEach(t => { t.classList.add('pb-toast-out'); setTimeout(() => t.remove(), 300); });
        const t = document.createElement('div');
        t.className = 'pb-toast';
        if (type === 'error') t.style.borderColor = 'rgba(239,68,68,.4)';
        if (type === 'success') t.style.borderColor = 'rgba(34,197,94,.4)';
        t.innerHTML = `<span>${msg}</span>`;
        document.body.appendChild(t);
        setTimeout(() => { t.classList.add('pb-toast-out'); setTimeout(() => t.remove(), 300); }, 2500);
    },

    loadTemplates() {
        fetch('/page-builder/templates')
            .then(r => r.json())
            .then(data => {
                const container = document.getElementById('layout-templates');
                container.innerHTML = '<div class="pb-widget-group-title" style="margin-bottom:.75rem">Escolha um modelo de layout</div>';
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
                            <button class="pb-apply-btn" data-template="${key}">Aplicar Modelo</button>
                    `;
                    card.querySelector('.pb-apply-btn').onclick = (e) => {
                        e.stopPropagation();
                        this.applyTemplate(key, e.target);
                    };
                    container.appendChild(card);
                }
            })
            .catch(() => this.toastError('Falha ao carregar modelos'));
    },

    applyTemplate(key, btn) {
        if (!confirm('Aplicar este modelo? Irá substituir todo o conteúdo existente.')) return;
        btn.disabled = true; btn.textContent = 'Aplicando...';
        fetch(`/page-builder/pages/${this.pageId}/apply-template`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ template: key }),
        })
        .then(r => r.json())
        .then(() => {
            this.showToast('Modelo aplicado!');
            this.loadElements();
            btn.disabled = false; btn.textContent = 'Aplicar Modelo';
        })
        .catch(() => { btn.disabled = false; btn.textContent = 'Aplicar Modelo'; });
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

    exportPage() {
        window.open('/page-builder/pages/' + this.pageId + '/export', '_blank');
    },

    copyHtml() {
        fetch('/page-builder/pages/' + this.pageId + '/render?format=inner')
            .then(r => r.text())
            .then(html => {
                navigator.clipboard.writeText(html).then(() => {
                    this.showToast('HTML copiado!');
                }).catch(() => {
                    const ta = document.createElement('textarea');
                    ta.value = html;
                    document.body.appendChild(ta);
                    ta.select();
                    document.execCommand('copy');
                    ta.remove();
                    this.showToast('HTML copiado!');
                });
            })
            .catch(() => this.toastError('Falha ao copiar HTML'));
    },

    uploadImageFile(file, callback) {
        const formData = new FormData();
        formData.append('image', file);
        this.showToast('Enviando imagem...', 'info');
        fetch('/page-builder/upload', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': this.csrf },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.url) {
                this.toastSuccess('Imagem enviada!');
                callback(data.url);
            } else {
                this.toastError('Falha ao enviar imagem');
            }
        })
        .catch(() => this.toastError('Falha ao enviar imagem'));
    },

    renderPageSettings() {
        const body = document.getElementById('page-settings-body');
        body.innerHTML = '';
        const currentPage = window._pageData || {};
        const s = currentPage.settings || {};

        const controls = [
            { key: 'container_width', label: 'Largura do Container', type: 'text', default: '1140px' },
            { key: 'page_background', label: 'Fundo da Página', type: 'color', default: '#ffffff' },
            { key: 'content_padding', label: 'Espaçamento Interno', type: 'text', default: '0px' },
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
        }).catch(() => this.toastError('Falha ao atualizar configuração da página'));
    },
};
