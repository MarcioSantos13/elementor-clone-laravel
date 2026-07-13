const editor = {
    pageId: null,
    selectedId: null,
    activeTab: 'content',
    cachedControls: null,
    cachedSettings: null,
    cachedStyles: null,
    cachedElementId: null,
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
        this.observeCanvas();
    },

    observeCanvas() {
        const dz = document.getElementById('canvas-dropzone');
        if (!dz) return;
        let timer = null;
        const observer = new MutationObserver(() => {
            clearTimeout(timer);
            timer = setTimeout(() => this.renderMath(), 150);
        });
        observer.observe(dz, { childList: true, subtree: true, characterData: true });
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
                this._lastElements = data.elements || [];
                this.renderCanvas(data.elements);
                this.renderMath();
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
        if (!parentEl) this.renderMath();
    },

    renderMath() {
        if (typeof katex === 'undefined') return;
        document.querySelectorAll('#canvas-dropzone .pb-math, #settings-body .pb-math').forEach(el => {
            if (el.closest('[contenteditable="true"]')) return;
            if (el.dataset.katexRendered) return;
            try {
                katex.render(el.getAttribute('data-formula'), el, {
                    displayMode: el.getAttribute('data-display') === 'true',
                    throwOnError: false
                });
                el.dataset.katexRendered = '1';
            } catch (e) {
                el.textContent = el.getAttribute('data-formula');
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
            case 'callout': {
                const typeStyles = {info:{bg:'#e0f2fe',border:'#0284c7',icon:'&#8505;'},success:{bg:'#dcfce7',border:'#16a34a',icon:'&#10003;'},warning:{bg:'#fef9c3',border:'#ca8a04',icon:'&#9888;'},danger:{bg:'#fee2e2',border:'#dc2626',icon:'&#10007;'},tip:{bg:'#f0fdf4',border:'#22c55e',icon:'&#128161;'},definition:{bg:'#f3e8ff',border:'#9333ea',icon:'&#128218;'},theorem:{bg:'#fff7ed',border:'#ea580c',icon:'&#9878;'},exercise:{bg:'#ecfeff',border:'#0891b2',icon:'&#9998;'},note:{bg:'#f8fafc',border:'#64748b',icon:'&#128221;'}};
                const st = typeStyles[s.type]||typeStyles.info;
                const borderStyle = s.border_style==='none'?'border-left:none;':s.border_style==='full'?`border:2px solid ${st.border};`:`border-left:4px solid ${st.border};`;
                preview = `<div style="background:${st.bg};${borderStyle}padding:${s.padding||'16px'};border-radius:${s.border_radius||'8px'}"><div style="display:flex;align-items:flex-start;gap:10px"><span style="font-size:1.2em">${this.escHtml(s.icon)||st.icon}</span><div style="flex:1">${s.content||'<p>Conteúdo do callout</p>'}</div></div></div>`;
                break;
            }
            case 'table': {
                const rows = parseInt(s.rows)||3;
                const cols = parseInt(s.cols)||3;
                const hd = s.has_header!==false;
                const bw = s.border_width||'1px';
                const bc = s.border_color||'#e2e8f0';
                let html = `<table style="width:${s.width||'100%'};border-collapse:collapse;font-size:${s.font_size||'14px'}"><tbody>`;
                for (let r=0;r<rows;r++) {
                    html += '<tr>';
                    for (let c=0;c<cols;c++) {
                        if (r===0&&hd) html += `<th style="background:#f1f5f9;border:${bw} solid ${bc};padding:${s.cell_padding||'10px 14px'};font-weight:600;text-align:left">Cabeçalho ${c+1}</th>`;
                        else html += `<td style="border:${bw} solid ${bc};padding:${s.cell_padding||'10px 14px'}">Conteúdo</td>`;
                    }
                    html += '</tr>';
                }
                html += '</tbody></table>';
                preview = `<div style="overflow-x:auto">${html}</div>`;
                break;
            }
            case 'math': {
                const formula = s.formula||'x^2 + y^2 = z^2';
                const mode = s.display_mode===false?'inline':'block';
                preview = mode==='block'
                    ? `<div style="text-align:${s.alignment||'center'};padding:16px 0"><span class="pb-math" data-formula="${this.escHtml(formula)}" data-display="true" style="font-size:${s.font_size||'24px'};color:${s.color||'#333'}"></span>${s.label?`<div style="margin-top:6px;font-size:0.8em;color:#666;font-style:italic">${this.escHtml(s.label)}</div>`:''}</div>`
                    : `<span class="pb-math" data-formula="${this.escHtml(formula)}" data-display="false" style="font-size:${s.font_size||'16px'};color:${s.color||'#333'}"></span>`;
                break;
            }
            case 'video': {
                if (s.video_url) {
                    const ratioMap = {'16:9':'56.25%','4:3':'75%','1:1':'100%','21:9':'42.86%'};
                    const pad = ratioMap[s.aspect_ratio]||'56.25%';
                    let embedUrl = s.video_url;
                    const ytMatch = s.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/|youtube\.com\/v\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/);
                    if (ytMatch) {
                        const params = ['rel=0'];
                        params.push(s.controls!==false?'controls=1':'controls=0');
                        params.push(s.autoplay?'autoplay=1':'autoplay=0');
                        params.push(s.loop?'loop=1':'loop=0');
                        params.push(s.mute?'mute=1':'mute=0');
                        if (s.loop) params.push('playlist='+ytMatch[1]);
                        if (s.start_time>0) params.push('start='+s.start_time);
                        if (s.end_time>0) params.push('end='+s.end_time);
                        embedUrl = 'https://www.youtube-nocookie.com/embed/'+ytMatch[1]+'?'+params.join('&');
                    } else {
                        const vmMatch = s.video_url.match(/vimeo\.com\/(\d+)/);
                        if (vmMatch) {
                            const params = [];
                            params.push(s.autoplay?'autoplay=1':'autoplay=0');
                            params.push(s.loop?'loop=1':'loop=0');
                            params.push(s.mute?'muted=1':'muted=0');
                            params.push('title=0','byline=0','portrait=0');
                            embedUrl = 'https://player.vimeo.com/video/'+vmMatch[1]+'?'+params.join('&');
                        }
                    }
                    const wStyle = `width:${s.width||'100%'};max-width:${s.max_width||'100%'};margin:0`;
                    const mAlign = s.alignment==='center'?'margin-left:auto;margin-right:auto':s.alignment==='right'?'margin-left:auto':'';
                    preview = `<div style="${wStyle};${mAlign}"><div style="position:relative;padding-bottom:${pad};height:0;overflow:hidden;border-radius:8px"><iframe src="${this.escHtml(embedUrl)}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" title="video"></iframe></div></div>`;
                } else {
                    preview = '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">🎬 Nenhum vídeo selecionado</div>';
                }
                break;
            }
            case 'divider': {
                const w = s.width != null ? s.width : 100;
                const t = s.thickness || 1;
                const st = s.style || 'solid';
                const c = s.color || '#e2e8f0';
                const mt = s.space_before != null ? s.space_before : 20;
                const mb = s.space_after != null ? s.space_after : 20;
                preview = `<hr style="border:none;border-top:${t}px ${st} ${c};width:${w}%;margin:${mt}px auto ${mb}px">`;
                break;
            }
            case 'spacer': {
                const sp = s.space != null ? s.space : 50;
                preview = `<div style="height:${sp}px;background:repeating-linear-gradient(45deg,transparent,transparent 5px,rgba(99,102,241,.06) 5px,rgba(99,102,241,.06) 10px);border:1px dashed rgba(99,102,241,.25);border-radius:4px;position:relative"><span style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:.7rem;color:rgba(99,102,241,.6);pointer-events:none">${sp}px</span></div>`;
                break;
            }
            case 'icon': {
                const ic = s.icon || 'fas fa-star';
                const isz = s.icon_size || 48;
                const icc = s.color || '#6366f1';
                const ica = s.align || 'center';
                let icHtml = `<i class="${ic}" style="font-size:${isz}px;color:${icc};line-height:1"></i>`;
                if (s.link) {
                    const icTarget = s.link_new_tab ? ' target="_blank" rel="noopener noreferrer"' : '';
                    icHtml = `<a href="${s.link}"${icTarget} style="text-decoration:none;display:inline-block">${icHtml}</a>`;
                }
                preview = `<div style="text-align:${ica};padding:8px 0">${icHtml}</div>`;
                break;
            }
            case 'gallery': {
                const imgs = Array.isArray(s.images) ? s.images : [];
                const cols = s.columns || 3;
                const gGap = s.gap != null ? s.gap : 10;
                const br = s.border_radius != null ? s.border_radius : 4;
                if (imgs.length === 0) {
                    preview = '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">🖼️ Nenhuma imagem selecionada</div>';
                } else {
                    let ghtml = `<div style="display:grid;grid-template-columns:repeat(${cols},1fr);gap:${gGap}px">`;
                    imgs.slice(0, 12).forEach(img => {
                        ghtml += `<div style="overflow:hidden;border-radius:${br}px;aspect-ratio:1;background:#f1f5f9"><img src="${this.escHtml(img.url||'')}" alt="${this.escHtml(img.alt||'')}" style="width:100%;height:100%;object-fit:cover;border-radius:${br}px"></div>`;
                    });
                    if (imgs.length > 12) ghtml += `<div style="display:flex;align-items:center;justify-content:center;aspect-ratio:1;background:#f1f5f9;border-radius:${br}px;font-size:.75rem;color:#666">+${imgs.length - 12}</div>`;
                    ghtml += '</div>';
                    preview = ghtml;
                }
                break;
            }
            case 'form': {
                const fields = Array.isArray(s.fields) ? s.fields : [];
                const fbr = s.field_radius != null ? s.field_radius : 6;
                const fsp = s.field_spacing != null ? s.field_spacing : 12;
                const bc = s.button_color || '#6366f1';
                const btc = s.button_text_color || '#fff';
                const bw = s.button_width === 'full' ? 'width:100%;' : '';
                let fhtml = '';
                fields.forEach(f => {
                    const req = f.required ? ' <span style="color:#ef4444">*</span>' : '';
                    fhtml += `<div style="margin-bottom:${fsp}px"><label style="display:block;margin-bottom:4px;font-size:13px;font-weight:500;color:#374151">${this.escHtml(f.label||'')}${req}</label>`;
                    if (f.type === 'textarea') {
                        fhtml += `<div style="width:100%;min-height:50px;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#f9fafb;font-size:12px;color:#9ca3af">Textarea</div>`;
                    } else if (f.type === 'select') {
                        fhtml += `<div style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#fff;font-size:12px;color:#9ca3af;display:flex;justify-content:space-between"><span>Select...</span><span>▼</span></div>`;
                    } else if (f.type === 'checkbox' || f.type === 'radio') {
                        fhtml += `<div style="display:flex;align-items:center;gap:6px"><input type="${f.type}" disabled style="width:auto"><span style="font-size:12px;color:#374151">${this.escHtml(f.label||'')}</span></div>`;
                    } else {
                        fhtml += `<div style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#f9fafb;font-size:12px;color:#9ca3af">${this.escHtml(f.type||'text')}</div>`;
                    }
                    fhtml += '</div>';
                });
                const btnLabel = this.escHtml(s.button_text || 'Send');
                fhtml += `<button type="button" style="padding:10px 24px;background:${bc};color:${btc};border:none;border-radius:${fbr}px;font-size:13px;font-weight:500;cursor:default;${bw}">${btnLabel}</button>`;
                preview = fhtml;
                break;
            }
            case 'tabs': {
                const tabs = Array.isArray(s.tabs) ? s.tabs : [];
                const tc = s.tab_color || '#6366f1';
                const bc2 = s.border_color || '#e2e8f0';
                const ati = s.active_tab || 0;
                if (tabs.length === 0) { preview = '<div style="text-align:center;padding:1rem;color:#999">No tabs</div>'; break; }
                let thead = '<div style="display:flex;border-bottom:2px solid ' + bc2 + '">';
                let tbody = '';
                tabs.forEach((t, i) => {
                    const active = i === ati;
                    thead += `<button type="button" style="padding:8px 16px;font-size:13px;border:none;border-bottom:3px solid ${active?tc:'transparent'};margin-bottom:-2px;background:${active?'#fff':'transparent'};color:${active?tc:'#6b7280'};font-weight:${active?'600':'400'};cursor:default">${this.escHtml(t.title||'Tab '+(i+1))}</button>`;
                    tbody += `<div style="display:${active?'block':'none'};padding:16px;font-size:13px;color:#6b7280">${t.content?this.escHtml(String(t.content).substring(0,100)):'...'}</div>`;
                });
                thead += '</div>';
                preview = thead + tbody;
                break;
            }
            case 'accordion': {
                const items = Array.isArray(s.items) ? s.items : [];
                const ac = s.tab_color || '#6366f1';
                const ab = s.border_color || '#e2e8f0';
                if (items.length === 0) { preview = '<div style="text-align:center;padding:1rem;color:#999">No items</div>'; break; }
                let ahtml = '';
                items.forEach((item, i) => {
                    const isOpen = item.open;
                    ahtml += `<div style="border:1px solid ${ab};border-radius:8px;overflow:hidden;margin-bottom:2px">`;
                    ahtml += `<div style="display:flex;align-items:center;padding:10px 14px;font-size:13px;font-weight:500;background:${isOpen?ac:'#f9fafb'};color:${isOpen?'#fff':'#374151'}"><span style="display:inline-block;transform:rotate(${isOpen?'90':'0'}deg);margin-right:8px;font-size:10px">▶</span>${this.escHtml(item.title||'Section '+(i+1))}</div>`;
                    if (isOpen) ahtml += `<div style="padding:12px;font-size:12px;color:#6b7280;background:#fff">${item.content?this.escHtml(String(item.content).substring(0,80)):'...'}</div>`;
                    ahtml += '</div>';
                });
                preview = ahtml;
                break;
            }
            default: preview = `<div class="pb-el-placeholder">${el.type}</div>`;
        }
        return `<div class="pb-el-toolbar"><span class="pb-el-name">${this.escHtml(name)}</span><span class="pb-el-type">${el.type}</span><span style="flex:1"></span><button class="pb-el-action" onclick="event.stopPropagation();editor.duplicateElement(${el.id})" title="Duplicate">&#128203;</button><button class="pb-el-action" onclick="event.stopPropagation();editor.deleteElement(${el.id})" title="Delete">&#128465;</button></div><div class="pb-el-content">${preview}</div>`;
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
        const icons = { section: '&#9638;', column: '&#9646;', heading: 'H', text: 'T', image: '&#128247;', button: '&#128206;', callout: '&#9888;', table: '&#9638;', math: '&Sigma;' };
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
                this.cachedControls = widget.controls || {};
                this.cachedSettings = element.settings || {};
                this.cachedStyles = element.styles || {};
                this.cachedElementId = id;
                this.activeTab = 'content';
                this.syncEditorTabs();
                this.renderControls();
                this.renderMath();
            })
            .catch(err => { console.error('loadControls failed:', err); this.toastError('Falha ao carregar controles'); });
    },

    renderControls() {
        const body = document.getElementById('settings-body');
        body.innerHTML = '';
        const controls = this.cachedControls || {};
        const settings = this.cachedSettings || {};
        const styles = this.cachedStyles || {};
        const elementId = this.cachedElementId;
        const tab = this.activeTab;
        const filtered = {};
        for (const [key, ctrl] of Object.entries(controls)) {
            const ctrlTab = ctrl.tab || 'content';
            if (ctrlTab === tab) filtered[key] = ctrl;
        }
        const sections = this.groupControls(filtered);
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
                control.appendChild(this.createInput(key, ctrl, val, elementId));
                secDiv.appendChild(control);
            });
            body.appendChild(secDiv);
        }
    },

    switchEditorTab(tab) {
        this.activeTab = tab;
        this.syncEditorTabs();
        this.renderControls();
    },

    syncEditorTabs() {
        const tab = this.activeTab;
        document.querySelectorAll('#editor-tabs .pb-editor-tab').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.etab === tab);
        });
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
            wysiwyg: () => {
                const wrap = document.createElement('div');
                wrap.style.cssText = 'display:flex;flex-direction:column;border:1px solid var(--pb-border);border-radius:6px;overflow:hidden;background:var(--pb-bg)';

                const toolbar = document.createElement('div');
                toolbar.style.cssText = 'display:flex;flex-wrap:wrap;gap:2px;padding:4px 6px;background:var(--pb-surface2);border-bottom:1px solid var(--pb-border)';

                const focusContent = () => {
                    content.focus();
                    const range = document.createRange();
                    const sel = window.getSelection();
                    if (sel.rangeCount > 0) {
                        const existing = sel.getRangeAt(0);
                        if (content.contains(existing.startContainer)) return;
                    }
                    range.selectNodeContents(content);
                    range.collapse(false);
                    sel.removeAllRanges();
                    sel.addRange(range);
                };

                const insertHtmlAtCursor = (html) => {
                    content.focus();
                    const sel = window.getSelection();
                    const range = document.createRange();
                    if (sel.rangeCount > 0 && content.contains(sel.getRangeAt(0).startContainer)) {
                        range.setStart(sel.getRangeAt(0).startContainer, sel.getRangeAt(0).startOffset);
                    } else {
                        range.selectNodeContents(content);
                        range.collapse(false);
                    }
                    range.deleteContents();
                    const frag = range.createContextualFragment(html);
                    const lastNode = frag.lastChild;
                    range.insertNode(frag);
                    if (lastNode) {
                        range.setStartAfter(lastNode);
                        range.collapse(true);
                    }
                    sel.removeAllRanges();
                    sel.addRange(range);
                };

                const execCmd = (cmd, val) => {
                    focusContent();
                    document.execCommand(cmd, false, val || null);
                };

                const makeBtn = (label, title, cmd, val) => {
                    const b = document.createElement('button');
                    b.type = 'button';
                    b.innerHTML = label;
                    b.title = title;
                    b.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:13px;font-weight:600';
                    b.onmouseenter = () => { b.style.background = 'var(--pb-border)'; };
                    b.onmouseleave = () => { b.style.background = 'transparent'; b.style.borderColor = 'transparent'; };
                    b.onmousedown = (e) => { e.preventDefault(); execCmd(cmd, val); };
                    return b;
                };

                const content = document.createElement('div');
                content.contentEditable = 'true';
                content.id = `ctrl-${key}`;
                content.innerHTML = typeof value === 'string' ? value : '<p></p>';
                content.style.cssText = 'min-height:120px;max-height:400px;overflow-y:auto;padding:8px 10px;font-size:13px;line-height:1.6;color:var(--pb-text);outline:none';
                content.innerHTML = content.innerHTML || '<p></p>';

                toolbar.appendChild(makeBtn('B', 'Negrito', 'bold'));
                toolbar.appendChild(makeBtn('I', 'Itálico', 'italic'));
                toolbar.appendChild(makeBtn('U', 'Sublinhado', 'underline'));
                toolbar.appendChild(makeBtn('S', 'Tachado', 'strikeThrough'));

                const sep = document.createElement('span');
                sep.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px';
                toolbar.appendChild(sep);

                toolbar.appendChild(makeBtn('&#9650;', 'Título H2', 'formatBlock', 'h2'));
                toolbar.appendChild(makeBtn('&#182;', 'Parágrafo', 'formatBlock', 'p'));

                const sep2 = document.createElement('span');
                sep2.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px';
                toolbar.appendChild(sep2);

                const linkBtn = document.createElement('button');
                linkBtn.type = 'button';
                linkBtn.innerHTML = '&#128279;';
                linkBtn.title = 'Inserir link';
                linkBtn.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:13px';
                linkBtn.onmouseenter = () => { linkBtn.style.background = 'var(--pb-border)'; };
                linkBtn.onmouseleave = () => { linkBtn.style.background = 'transparent'; linkBtn.style.borderColor = 'transparent'; };
                linkBtn.onmousedown = (e) => {
                    e.preventDefault();
                    const url = prompt('URL do link:', 'https://');
                    if (url) execCmd('createLink', url);
                };
                toolbar.appendChild(linkBtn);

                const imgBtn = document.createElement('button');
                imgBtn.type = 'button';
                imgBtn.innerHTML = '&#128247;';
                imgBtn.title = 'Inserir imagem';
                imgBtn.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:13px';
                imgBtn.onmouseenter = () => { imgBtn.style.background = 'var(--pb-border)'; };
                imgBtn.onmouseleave = () => { imgBtn.style.background = 'transparent'; imgBtn.style.borderColor = 'transparent'; };

                const imgFileInput = document.createElement('input');
                imgFileInput.type = 'file';
                imgFileInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
                imgFileInput.style.display = 'none';

                const insertImageInEditor = (url) => {
                    const html = `<img src="${url}" style="max-width:100%;height:auto;border-radius:4px" alt="">`;
                    insertHtmlAtCursor(html);
                    setTimeout(() => {
                        focusContent();
                        debounceSave(content.innerHTML);
                    }, 50);
                };

                imgBtn.onmousedown = (e) => { e.preventDefault(); imgFileInput.click(); };
                imgFileInput.onchange = () => {
                    const file = imgFileInput.files[0];
                    if (!file) return;
                    this.uploadImageFile(file, (url) => {
                        insertImageInEditor(url);
                    });
                    imgFileInput.value = '';
                };
                toolbar.appendChild(imgBtn);

                const ytBtn = document.createElement('button');
                ytBtn.type = 'button';
                ytBtn.innerHTML = '&#9654;';
                ytBtn.title = 'Inserir vídeo YouTube';
                ytBtn.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:#ff0000;cursor:pointer;font-size:13px;font-weight:700';
                ytBtn.onmouseenter = () => { ytBtn.style.background = 'var(--pb-border)'; };
                ytBtn.onmouseleave = () => { ytBtn.style.background = 'transparent'; ytBtn.style.borderColor = 'transparent'; };
                ytBtn.onmousedown = (e) => {
                    e.preventDefault();
                    const url = prompt('Cole a URL do YouTube:', 'https://www.youtube.com/watch?v=');
                    if (!url) return;
                    let videoId = null;
                    const m1 = url.match(/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/);
                    const m2 = url.match(/youtu\.be\/([a-zA-Z0-9_-]+)/);
                    const m3 = url.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/);
                    if (m1) videoId = m1[1];
                    else if (m2) videoId = m2[1];
                    else if (m3) videoId = m3[1];
                    if (!videoId) { this.toastError('URL do YouTube não reconhecida'); return; }
                    const iframe = `<div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:8px;margin:12px 0"><iframe src="https://www.youtube-nocookie.com/embed/${videoId}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div>`;
                    insertHtmlAtCursor(iframe);
                    setTimeout(() => { focusContent(); debounceSave(content.innerHTML); }, 50);
                };
                toolbar.appendChild(ytBtn);

                const sep3 = document.createElement('span');
                sep3.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px';
                toolbar.appendChild(sep3);

                toolbar.appendChild(makeBtn('&#8226;', 'Lista', 'insertUnorderedList'));
                toolbar.appendChild(makeBtn('1.', 'Lista Numerada', 'insertOrderedList'));

                const sep4 = document.createElement('span');
                sep4.style.cssText = 'width:1px;background:var(--pb-border);margin:2px 4px';
                toolbar.appendChild(sep4);

                const tableBtn = document.createElement('button');
                tableBtn.type = 'button';
                tableBtn.innerHTML = '&#9638;';
                tableBtn.title = 'Inserir tabela';
                tableBtn.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:13px';
                tableBtn.onmouseenter = () => { tableBtn.style.background = 'var(--pb-border)'; };
                tableBtn.onmouseleave = () => { tableBtn.style.background = 'transparent'; tableBtn.style.borderColor = 'transparent'; };
                tableBtn.onmousedown = (e) => {
                    e.preventDefault();
                    const overlay = document.createElement('div');
                    overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:99999';
                    const modal = document.createElement('div');
                    modal.style.cssText = 'background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:12px;padding:20px;width:320px;box-shadow:0 16px 48px rgba(0,0,0,.3)';
                    modal.innerHTML = '<div style="font-size:14px;font-weight:600;margin-bottom:12px;color:var(--pb-text)">Inserir Tabela</div>' +
                        '<div style="display:flex;gap:10px;margin-bottom:12px">' +
                        '<label style="flex:1;font-size:12px;color:var(--pb-text2)">Linhas<input id="pb-tbl-rows" type="number" value="3" min="1" max="20" style="width:100%;margin-top:4px;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:13px"></label>' +
                        '<label style="flex:1;font-size:12px;color:var(--pb-text2)">Colunas<input id="pb-tbl-cols" type="number" value="3" min="1" max="10" style="width:100%;margin-top:4px;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:13px"></label>' +
                        '</div>' +
                        '<label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--pb-text2);margin-bottom:14px"><input id="pb-tbl-header" type="checkbox" checked> Cabeçalho</label>' +
                        '<div style="display:flex;gap:8px;justify-content:flex-end">' +
                        '<button id="pb-tbl-cancel" style="padding:6px 14px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);cursor:pointer;font-size:12px">Cancelar</button>' +
                        '<button id="pb-tbl-ok" style="padding:6px 14px;background:var(--pb-primary);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:12px;font-weight:500">Inserir</button>' +
                        '</div>';
                    overlay.appendChild(modal);
                    document.body.appendChild(overlay);
                    modal.querySelector('#pb-tbl-rows').focus();
                    const close = () => { document.removeEventListener('keydown', escHandler); overlay.remove(); };
                    const escHandler = (ev) => { if (ev.key === 'Escape') close(); };
                    document.addEventListener('keydown', escHandler);
                    const enterHandler = (ev) => { if (ev.key === 'Enter') { ev.preventDefault(); modal.querySelector('#pb-tbl-ok').click(); } };
                    modal.querySelector('#pb-tbl-rows').addEventListener('keydown', enterHandler);
                    modal.querySelector('#pb-tbl-cols').addEventListener('keydown', enterHandler);
                    modal.querySelector('#pb-tbl-cancel').onclick = close;
                    overlay.onclick = (ev) => { if (ev.target === overlay) close(); };
                    modal.querySelector('#pb-tbl-ok').onclick = () => {
                        const rows = parseInt(modal.querySelector('#pb-tbl-rows').value) || 3;
                        const cols = parseInt(modal.querySelector('#pb-tbl-cols').value) || 3;
                        const hasHeader = modal.querySelector('#pb-tbl-header').checked;
                        let html = '<table style="width:100%;border-collapse:collapse;margin:12px 0;font-size:14px">';
                        for (let r = 0; r < rows; r++) {
                            html += '<tr>';
                            for (let c = 0; c < cols; c++) {
                                if (r === 0 && hasHeader) html += `<th style="background:#f1f5f9;border:1px solid #e2e8f0;padding:8px 12px;font-weight:600;text-align:left">Cabeçalho ${c + 1}</th>`;
                                else html += `<td style="border:1px solid #e2e8f0;padding:8px 12px">Conteúdo</td>`;
                            }
                            html += '</tr>';
                        }
                        html += '</table>';
                        insertHtmlAtCursor(html);
                        setTimeout(() => { focusContent(); debounceSave(content.innerHTML); }, 50);
                        close();
                    };
                };
                toolbar.appendChild(tableBtn);

                const mathBtn = document.createElement('button');
                mathBtn.type = 'button';
                mathBtn.innerHTML = '&Sigma;';
                mathBtn.title = 'Inserir fórmula matemática (LaTeX)';
                mathBtn.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:14px;font-weight:700';
                mathBtn.onmouseenter = () => { mathBtn.style.background = 'var(--pb-border)'; };
                mathBtn.onmouseleave = () => { mathBtn.style.background = 'transparent'; mathBtn.style.borderColor = 'transparent'; };
                mathBtn.onmousedown = (e) => {
                    e.preventDefault();
                    const overlay = document.createElement('div');
                    overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:99999';
                    const modal = document.createElement('div');
                    modal.style.cssText = 'background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:12px;padding:20px;width:420px;box-shadow:0 16px 48px rgba(0,0,0,.3)';
                    modal.innerHTML = '<div style="font-size:14px;font-weight:600;margin-bottom:12px;color:var(--pb-text)">Fórmula Matemática (LaTeX)</div>' +
                        '<label style="font-size:12px;color:var(--pb-text2)">Fórmula LaTeX</label>' +
                        '<textarea id="pb-math-input" style="width:100%;margin-top:4px;padding:8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:13px;font-family:monospace;min-height:60px;resize:vertical" placeholder="Ex: x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}"></textarea>' +
                        '<div id="pb-math-preview" style="min-height:36px;margin:8px 0;padding:8px;background:var(--pb-surface2);border-radius:6px;text-align:center;font-size:18px;color:var(--pb-text)"></div>' +
                        '<label style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--pb-text2);margin-bottom:14px"><input id="pb-math-display" type="checkbox" checked> Modo display (centralizado, maior)</label>' +
                        '<div style="display:flex;gap:8px;justify-content:flex-end">' +
                        '<button id="pb-math-cancel" style="padding:6px 14px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);cursor:pointer;font-size:12px">Cancelar</button>' +
                        '<button id="pb-math-ok" style="padding:6px 14px;background:var(--pb-primary);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:12px;font-weight:500">Inserir</button>' +
                        '</div>';
                    overlay.appendChild(modal);
                    document.body.appendChild(overlay);
                    const input = modal.querySelector('#pb-math-input');
                    const preview = modal.querySelector('#pb-math-preview');
                    input.focus();
                    const updatePreview = () => {
                        const formula = input.value.trim();
                        if (!formula) { preview.innerHTML = '<span style="color:var(--pb-text2);font-size:12px">Pré-visualização</span>'; return; }
                        if (typeof katex !== 'undefined') {
                            try { katex.render(formula, preview, { displayMode: modal.querySelector('#pb-math-display').checked, throwOnError: false }); } catch (err) { preview.textContent = formula; }
                        } else {
                            preview.textContent = formula;
                        }
                    };
                    updatePreview();
                    input.oninput = updatePreview;
                    modal.querySelector('#pb-math-display').onchange = updatePreview;
                    const close = () => { document.removeEventListener('keydown', escHandler); overlay.remove(); };
                    const escHandler = (ev) => { if (ev.key === 'Escape') close(); };
                    document.addEventListener('keydown', escHandler);
                    modal.querySelector('#pb-math-cancel').onclick = close;
                    overlay.onclick = (ev) => { if (ev.target === overlay) close(); };
                    modal.querySelector('#pb-math-ok').onclick = () => {
                        const formula = input.value.trim();
                        if (!formula) { this.toastError('Digite uma fórmula LaTeX'); return; }
                        const isDisplay = modal.querySelector('#pb-math-display').checked;
                        const escapedFormula = this.escHtml(formula);
                        const span = `<span class="pb-math" data-formula="${escapedFormula}" data-display="${isDisplay}" style="font-size:${isDisplay ? '1.3em' : '1em'};color:#1e293b">${escapedFormula}</span>`;
                        insertHtmlAtCursor(isDisplay ? `<div style="text-align:center;padding:12px 0">${span}</div>` : span);
                        setTimeout(() => {
                            focusContent();
                            debounceSave(content.innerHTML);
                            this.renderMath();
                        }, 50);
                        close();
                    };
                };
                toolbar.appendChild(mathBtn);

                const sourceToggle = document.createElement('button');
                sourceToggle.type = 'button';
                sourceToggle.textContent = '</>';
                sourceToggle.title = 'Ver código fonte';
                sourceToggle.style.cssText = 'width:28px;height:26px;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;font-size:11px;font-family:monospace;font-weight:700;margin-left:auto';
                sourceToggle.onmouseenter = () => { sourceToggle.style.background = 'var(--pb-border)'; };
                sourceToggle.onmouseleave = () => { sourceToggle.style.background = 'transparent'; sourceToggle.style.borderColor = 'transparent'; };

                let sourceMode = false;
                let sourceTa = null;
                sourceToggle.onmousedown = (e) => {
                    e.preventDefault();
                    sourceMode = !sourceMode;
                    if (sourceMode) {
                        sourceTa = document.createElement('textarea');
                        sourceTa.value = content.innerHTML;
                        sourceTa.style.cssText = 'width:100%;min-height:120px;max-height:400px;padding:8px 10px;font-size:12px;font-family:monospace;background:var(--pb-surface2);color:var(--pb-text);border:none;resize:vertical;outline:none';
                        content.style.display = 'none';
                        wrap.appendChild(sourceTa);
                        sourceToggle.style.background = 'var(--pb-accent)';
                        sourceToggle.style.color = '#fff';
                    } else {
                        content.innerHTML = sourceTa.value;
                        sourceTa.remove();
                        sourceTa = null;
                        content.style.display = '';
                        sourceToggle.style.background = 'transparent';
                        sourceToggle.style.color = 'var(--pb-text)';
                        debounceSave(content.innerHTML);
                    }
                };
                toolbar.appendChild(sourceToggle);

                const debounceSave = (() => {
                    let timer;
                    return (html) => {
                        clearTimeout(timer);
                        timer = setTimeout(() => this.updateSetting(key, html, elementId), 400);
                    };
                })();

                content.oninput = () => { debounceSave(content.innerHTML); };

                const wysiwygPasteHandler = (e) => {
                    if (!wrap.isConnected) return;
                    const item = Array.from(e.clipboardData.items).find(i => i.type.startsWith('image/'));
                    if (!item) return;
                    e.preventDefault();
                    const file = item.getAsFile();
                    if (file) {
                        this.uploadImageFile(file, (url) => {
                            insertImageInEditor(url);
                        });
                    }
                };
                content.addEventListener('paste', wysiwygPasteHandler);

                wrap.appendChild(toolbar);
                wrap.appendChild(content);
                return wrap;
            },
            icon: () => {
                const container = document.createElement('div');
                container.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                const icons = [
                    'fas fa-star','fas fa-heart','fas fa-check','fas fa-times','fas fa-plus','fas fa-minus',
                    'fas fa-arrow-right','fas fa-arrow-left','fas fa-arrow-up','fas fa-arrow-down',
                    'fas fa-chevron-right','fas fa-chevron-left','fas fa-chevron-up','fas fa-chevron-down',
                    'fas fa-check-circle','fas fa-times-circle','fas fa-exclamation-circle','fas fa-info-circle',
                    'fas fa-question-circle','fas fa-lightbulb','fas fa-bell','fas fa-envelope',
                    'fas fa-phone','fas fa-map-marker-alt','fas fa-user','fas fa-users',
                    'fas fa-home','fas fa-cog','fas fa-search','fas fa-lock','fas fa-unlock',
                    'fas fa-download','fas fa-upload','fas fa-share','fas fa-link','fas fa-unlink',
                    'fas fa-edit','fas fa-trash','fas fa-copy','fas fa-folder','fas fa-file',
                    'fas fa-image','fas fa-video','fas fa-music','fas fa-book','fas fa-bookmark',
                    'fas fa-calendar','fas fa-clock','fas fa-sync','fas fa-spinner','fas fa-flag',
                    'fas fa-tag','fas fa-tags','fas fa-shopping-cart','fas fa-credit-card','fas fa-wallet',
                    'fas fa-chart-bar','fas fa-chart-line','fas fa-chart-pie','fas fa-trophy',
                    'fas fa-rocket','fas fa-bolt','fas fa-fire','fas fa-sun','fas fa-moon',
                    'fas fa-cloud','fas fa-umbrella','fas fa-leaf','fas fa-tree','fas fa-flower',
                    'fas fa-globe','fas fa-code','fas fa-terminal','fas fa-database','fas fa-server',
                    'fas fa-wifi','fas fa-bluetooth','fas fa-battery-full','fas fa-battery-half',
                    'fab fa-github','fab fa-google','fab fa-facebook','fab fa-twitter','fab fa-instagram',
                    'fab fa-youtube','fab fa-linkedin','fab fa-discord','fab fa-slack','fab fa-figma',
                ];
                const preview = document.createElement('div');
                preview.style.cssText = 'text-align:center;padding:8px;font-size:2rem;color:var(--pb-text)';
                const currentIcon = value || 'fas fa-star';
                preview.innerHTML = `<i class="${this.escHtml(currentIcon)}"></i>`;
                const search = document.createElement('input');
                search.type = 'text'; search.value = currentIcon;
                search.placeholder = 'fas fa-star';
                search.style.cssText = 'width:100%;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:12px;font-family:monospace';
                const grid = document.createElement('div');
                grid.style.cssText = 'display:grid;grid-template-columns:repeat(8,1fr);gap:2px;max-height:180px;overflow-y:auto;padding:4px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px';
                const renderGrid = (filter) => {
                    grid.innerHTML = '';
                    const filtered = filter ? icons.filter(i => i.includes(filter.toLowerCase())) : icons;
                    filtered.forEach(ic => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.innerHTML = `<i class="${ic}" style="font-size:14px"></i>`;
                        btn.title = ic;
                        btn.style.cssText = 'width:100%;aspect-ratio:1;display:flex;align-items:center;justify-content:center;border:1px solid transparent;border-radius:4px;background:transparent;color:var(--pb-text);cursor:pointer;transition:all .15s';
                        btn.onmouseenter = () => { btn.style.background = 'var(--pb-border)'; btn.style.borderColor = 'var(--pb-accent)'; };
                        btn.onmouseleave = () => { btn.style.background = 'transparent'; btn.style.borderColor = 'transparent'; };
                        btn.onclick = (e) => { e.preventDefault(); search.value = ic; preview.innerHTML = `<i class="${ic}"></i>`; this.updateSetting(key, ic, elementId); };
                        grid.appendChild(btn);
                    });
                };
                renderGrid('');
                search.oninput = () => { renderGrid(search.value); preview.innerHTML = `<i class="${this.escHtml(search.value)}"></i>`; this.updateSetting(key, search.value, elementId); };
                container.appendChild(preview);
                container.appendChild(search);
                    container.appendChild(grid);
                    return container;
                },
                gallery: () => {
                    const container = document.createElement('div');
                    container.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                    let images = Array.isArray(value) ? [...value] : [];
                    const update = () => { this.updateSetting(key, images, elementId); };
                    const renderList = () => {
                        list.innerHTML = '';
                        images.forEach((img, idx) => {
                            const item = document.createElement('div');
                            item.style.cssText = 'display:flex;align-items:center;gap:.5rem;padding:6px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;cursor:grab';
                            item.draggable = true;
                            item.dataset.idx = idx;
                            item.innerHTML = `<img src="${this.escHtml(img.url||'')}" style="width:40px;height:40px;object-fit:cover;border-radius:4px;flex-shrink:0"><div style="flex:1;min-width:0"><input type="text" value="${this.escHtml(img.alt||'')}" placeholder="Alt text" style="width:100%;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px;box-sizing:border-box"></div><button type="button" title="Move up" style="background:none;border:none;color:var(--pb-text2);cursor:pointer;font-size:12px;padding:2px">▲</button><button type="button" title="Move down" style="background:none;border:none;color:var(--pb-text2);cursor:pointer;font-size:12px;padding:2px">▼</button><button type="button" title="Remove" style="background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:14px;padding:2px">×</button>`;
                            item.querySelector('input').onchange = (e) => { images[idx].alt = e.target.value; update(); };
                            const btns = item.querySelectorAll('button');
                            btns[0].onclick = () => { if (idx > 0) { [images[idx-1], images[idx]] = [images[idx], images[idx-1]]; update(); renderList(); }};
                            btns[1].onclick = () => { if (idx < images.length-1) { [images[idx+1], images[idx]] = [images[idx], images[idx+1]]; update(); renderList(); }};
                            btns[2].onclick = () => { images.splice(idx, 1); update(); renderList(); };
                            item.ondragstart = (e) => { e.dataTransfer.setData('text/plain', idx); item.style.opacity = '.4'; };
                            item.ondragend = () => { item.style.opacity = '1'; };
                            item.ondragover = (e) => { e.preventDefault(); item.style.borderColor = 'var(--pb-accent)'; };
                            item.ondragleave = () => { item.style.borderColor = 'var(--pb-border)'; };
                            item.ondrop = (e) => { e.preventDefault(); item.style.borderColor = 'var(--pb-border)'; const from = parseInt(e.dataTransfer.getData('text/plain')); if (!isNaN(from) && from !== idx) { const moved = images.splice(from, 1)[0]; images.splice(idx, 0, moved); update(); renderList(); }};
                            list.appendChild(item);
                        });
                    };
                    const list = document.createElement('div');
                    list.style.cssText = 'display:flex;flex-direction:column;gap:4px;max-height:200px;overflow-y:auto';
                    renderList();
                    const btnRow = document.createElement('div');
                    btnRow.style.cssText = 'display:flex;gap:6px;flex-wrap:wrap';
                    const addBtn = document.createElement('button');
                    addBtn.type = 'button';
                    addBtn.textContent = '+ Add Images';
                    addBtn.style.cssText = 'flex:1;padding:8px;background:var(--pb-primary);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:12px;font-weight:500';
                    addBtn.onclick = () => {
                        const overlay = document.createElement('div');
                        overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:99999';
                        const modal = document.createElement('div');
                        modal.style.cssText = 'background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:12px;padding:20px;width:500px;max-height:80vh;overflow-y:auto;box-shadow:0 16px 48px rgba(0,0,0,.3)';
                        modal.innerHTML = '<div style="font-size:14px;font-weight:600;margin-bottom:12px;color:var(--pb-text)">Add Images</div>' +
                            '<div id="gallery-dropzone" style="border:2px dashed var(--pb-border);border-radius:8px;padding:2rem;text-align:center;cursor:pointer;transition:all .2s;margin-bottom:12px"><div style="font-size:1.5rem;margin-bottom:.35rem;opacity:.5">🖼️</div><div style="font-size:.72rem;color:var(--pb-text2)"><strong style="color:var(--pb-accent);cursor:pointer">Click to select</strong><br>or drag images here</div></div>' +
                            '<div id="gallery-url-row" style="display:flex;gap:6px;margin-bottom:12px"><input id="gallery-url-input" type="url" placeholder="Or paste image URL..." style="flex:1;padding:6px 8px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);font-size:12px"><button id="gallery-url-add" type="button" style="padding:6px 12px;background:var(--pb-primary);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:12px">Add</button></div>' +
                            '<div id="gallery-selected" style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:12px"></div>' +
                            '<div style="display:flex;gap:8px;justify-content:flex-end"><button id="gallery-cancel" style="padding:6px 14px;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;color:var(--pb-text);cursor:pointer;font-size:12px">Cancel</button><button id="gallery-ok" style="padding:6px 14px;background:var(--pb-primary);border:none;border-radius:6px;color:#fff;cursor:pointer;font-size:12px;font-weight:500">Add to Gallery</button></div>';
                        overlay.appendChild(modal);
                        document.body.appendChild(overlay);
                        let selected = [];
                        const renderSelected = () => {
                            const grid = modal.querySelector('#gallery-selected');
                            grid.innerHTML = '';
                            selected.forEach((s, i) => {
                                const d = document.createElement('div');
                                d.style.cssText = 'position:relative;aspect-ratio:1;border-radius:6px;overflow:hidden;border:1px solid var(--pb-border)';
                                d.innerHTML = `<img src="${this.escHtml(s.url)}" style="width:100%;height:100%;object-fit:cover"><button type="button" style="position:absolute;top:2px;right:2px;width:18px;height:18px;border-radius:50%;background:rgba(0,0,0,.6);color:#fff;border:none;cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center">×</button>`;
                                d.querySelector('button').onclick = () => { selected.splice(i, 1); renderSelected(); };
                                grid.appendChild(d);
                            });
                        };
                        const addFile = (file) => {
                            if (!file || !file.type.startsWith('image/')) return;
                            this.uploadImageFile(file, (url) => { selected.push({ url, alt: '' }); renderSelected(); });
                        };
                        const dz = modal.querySelector('#gallery-dropzone');
                        const fi = document.createElement('input');
                        fi.type = 'file'; fi.multiple = true; fi.accept = 'image/*'; fi.style.display = 'none';
                        dz.appendChild(fi);
                        dz.onclick = () => fi.click();
                        dz.ondragover = (e) => { e.preventDefault(); dz.style.borderColor = 'var(--pb-accent)'; };
                        dz.ondragleave = () => { dz.style.borderColor = 'var(--pb-border)'; };
                        dz.ondrop = (e) => { e.preventDefault(); dz.style.borderColor = 'var(--pb-border)'; Array.from(e.dataTransfer.files).forEach(addFile); };
                        fi.onchange = () => { Array.from(fi.files).forEach(addFile); };
                        modal.querySelector('#gallery-url-add').onclick = () => {
                            const url = modal.querySelector('#gallery-url-input').value.trim();
                            if (url) { selected.push({ url, alt: '' }); renderSelected(); modal.querySelector('#gallery-url-input').value = ''; }
                        };
                        const close = () => { overlay.remove(); };
                        modal.querySelector('#gallery-cancel').onclick = close;
                        overlay.onclick = (ev) => { if (ev.target === overlay) close(); };
                        modal.querySelector('#gallery-ok').onclick = () => { images = images.concat(selected); update(); renderList(); close(); };
                    };
                    btnRow.appendChild(addBtn);
                    container.appendChild(list);
                    container.appendChild(btnRow);
                    return container;
                },
                repeater: () => {
                    const container = document.createElement('div');
                    container.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                    let items = Array.isArray(value) ? value.map(v => ({...v})) : [];
                    const subFields = ctrl.fields || {};
                    const renderItems = () => {
                        list.innerHTML = '';
                        items.forEach((item, idx) => {
                            const card = document.createElement('div');
                            card.style.cssText = 'background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:6px;padding:8px;display:flex;flex-direction:column;gap:6px';
                            card.draggable = true;
                            card.dataset.idx = idx;
                            const header = document.createElement('div');
                            header.style.cssText = 'display:flex;align-items:center;gap:4px;font-size:.7rem;color:var(--pb-text2);cursor:grab';
                            header.innerHTML = `<span style="cursor:grab">⣿</span><span style="flex:1;font-weight:500;color:var(--pb-text)">${this.escHtml(item.label||item.type||'Field '+(idx+1))}</span>`;
                            const delBtn = document.createElement('button');
                            delBtn.type = 'button';
                            delBtn.textContent = '×';
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
                                    cb.type = 'checkbox';
                                    cb.checked = !!item[fk];
                                    cb.onchange = (e) => { items[idx][fk] = e.target.checked; updateRepeater(); };
                                    fRow.appendChild(cb);
                                } else {
                                    const inp = document.createElement('input');
                                    inp.type = 'text';
                                    inp.value = item[fk] || '';
                                    inp.style.cssText = 'flex:1;padding:3px 6px;background:var(--pb-surface);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:11px';
                                    inp.onchange = (e) => { items[idx][fk] = e.target.value; updateRepeater(); renderItems(); };
                                    fRow.appendChild(inp);
                                }
                                card.appendChild(fRow);
                            }
                            card.ondragstart = (e) => { e.dataTransfer.setData('text/plain', idx); card.style.opacity = '.4'; };
                            card.ondragend = () => { card.style.opacity = '1'; };
                            card.ondragover = (e) => { e.preventDefault(); card.style.borderColor = 'var(--pb-accent)'; };
                            card.ondragleave = () => { card.style.borderColor = 'var(--pb-border)'; };
                            card.ondrop = (e) => { e.preventDefault(); card.style.borderColor = 'var(--pb-border)'; const from = parseInt(e.dataTransfer.getData('text/plain')); if (!isNaN(from) && from !== idx) { const moved = items.splice(from, 1)[0]; items.splice(idx, 0, moved); renderItems(); updateRepeater(); }};
                            list.appendChild(card);
                        });
                    };
                    const updateRepeater = () => { this.updateSetting(key, items, elementId); };
                    const list = document.createElement('div');
                    list.style.cssText = 'display:flex;flex-direction:column;gap:4px;max-height:280px;overflow-y:auto';
                    renderItems();
                    const addBtn = document.createElement('button');
                    addBtn.type = 'button';
                    addBtn.textContent = '+ Add Item';
                    addBtn.style.cssText = 'padding:6px;background:var(--pb-surface2);border:1px dashed var(--pb-border);border-radius:6px;color:var(--pb-text2);cursor:pointer;font-size:11px;text-align:center;transition:all .2s';
                    addBtn.onmouseenter = () => { addBtn.style.borderColor = 'var(--pb-accent)'; addBtn.style.color = 'var(--pb-text)'; };
                    addBtn.onmouseleave = () => { addBtn.style.borderColor = 'var(--pb-border)'; addBtn.style.color = 'var(--pb-text2)'; };
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
                },
                typography: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
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
                            sel.onchange = () => this.updateStyle(fk, sel.value, elementId);
                            row.appendChild(sel);
                        } else {
                            const inp = document.createElement('input');
                            inp.type = type; inp.value = value || '';
                            if (type === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
                            inp.onchange = () => this.updateStyle(fk, inp.value, elementId);
                            row.appendChild(inp);
                        }
                        c.appendChild(row);
                    });
                    return c;
                },
                background: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                    const defs = [
                        { fk: 'backgroundColor', label: 'Background Color', type: 'color' },
                        { fk: 'backgroundImage', label: 'Background Image', type: 'url' },
                        { fk: 'backgroundPosition', label: 'Position', type: 'select', options: ['center center','left top','left center','left bottom','right top','right center','right bottom','center top','center bottom'] },
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
                            sel.onchange = () => {
                                let v = sel.value;
                                if (fk === 'backgroundImage' && v && !v.startsWith('url(')) v = v ? `url('${v}')` : '';
                                this.updateStyle(fk, v, elementId);
                            };
                            row.appendChild(sel);
                        } else {
                            const inp = document.createElement('input');
                            inp.type = type; inp.value = value || '';
                            if (fk === 'backgroundColor') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
                            inp.onchange = () => {
                                let v = inp.value;
                                if (fk === 'backgroundImage' && v && !v.startsWith('url(')) v = v ? `url('${v}')` : '';
                                this.updateStyle(fk, v, elementId);
                            };
                            row.appendChild(inp);
                        }
                        c.appendChild(row);
                    });
                    return c;
                },
                border: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
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
                            sel.onchange = () => this.updateStyle(fk, sel.value, elementId);
                            row.appendChild(sel);
                        } else {
                            const inp = document.createElement('input');
                            inp.type = type; inp.value = value || def || '';
                            if (fk === 'borderColor') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
                            inp.onchange = () => this.updateStyle(fk, inp.value, elementId);
                            row.appendChild(inp);
                        }
                        c.appendChild(row);
                    });
                    return c;
                },
                box_shadow: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
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
                        inp.onchange = () => this.updateStyle('boxShadow', readAll(), elementId);
                        row.appendChild(inp);
                        c.appendChild(row);
                    });
                    return c;
                },
                dimensions: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
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
                        lockBtn.type = 'button';
                        lockBtn.innerHTML = '🔗';
                        lockBtn.title = 'Link values (all sides equal)';
                        lockBtn.style.cssText = 'background:none;border:1px solid var(--pb-border);border-radius:4px;padding:2px 6px;cursor:pointer;font-size:12px;transition:all .15s';
                        lockBtn.onclick = () => {
                            isLinked[group.prefix] = !isLinked[group.prefix];
                            lockBtn.innerHTML = isLinked[group.prefix] ? '🔗' : '🔓';
                            lockBtn.style.borderColor = isLinked[group.prefix] ? 'var(--pb-accent)' : 'var(--pb-border)';
                            lockBtn.title = isLinked[group.prefix] ? 'Link values (all sides equal)' : 'Unlink values (independent sides)';
                        };
                        lockBtn.innerHTML = '🔗';
                        lockBtn.style.borderColor = 'var(--pb-accent)';
                        header.appendChild(lockBtn);
                        c.appendChild(header);
                        const grid = document.createElement('div');
                        grid.style.cssText = 'display:grid;grid-template-columns:repeat(4,1fr);gap:4px';
                        const inputs = [];
                        group.keys.forEach((side, idx) => {
                            const fk = group.prefix + side;
                            const wrap = document.createElement('div');
                            wrap.style.cssText = 'display:flex;flex-direction:column;align-items:center;gap:2px';
                            const sideLabel = document.createElement('span');
                            sideLabel.style.cssText = 'font-size:10px;color:var(--pb-text2);text-transform:uppercase';
                            sideLabel.textContent = side;
                            const inp = document.createElement('input');
                            inp.type = 'text';
                            inp.value = value || '';
                            inp.placeholder = '0';
                            inp.style.cssText = 'width:100%;padding:4px;text-align:center;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:12px;box-sizing:border-box';
                            inp.onchange = () => {
                                this.updateStyle(fk, inp.value, elementId);
                                if (isLinked[group.prefix]) {
                                    inputs.forEach((otherInp, oi) => {
                                        if (oi !== idx) {
                                            otherInp.value = inp.value;
                                            this.updateStyle(group.prefix + group.keys[oi], inp.value, elementId);
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
                },
                hover: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                    const defs = [
                        { fk: 'hoverBackgroundColor', label: 'Background Color', type: 'color' },
                        { fk: 'hoverTextColor', label: 'Text Color', type: 'color' },
                        { fk: 'hoverBorderColor', label: 'Border Color', type: 'color' },
                        { fk: 'hoverTransform', label: 'Transform', type: 'select', options: ['none','scale(1.05)','scale(0.98)','translateY(-2px)','translateY(2px)'] },
                        { fk: 'hoverTransition', label: 'Transition (ms)', type: 'text' },
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
                            sel.onchange = () => this.updateStyle(fk, sel.value, elementId);
                            row.appendChild(sel);
                        } else {
                            const inp = document.createElement('input');
                            inp.type = type; inp.value = value || '';
                            if (type === 'color') inp.style.cssText = 'height:32px;padding:2px;cursor:pointer';
                            inp.onchange = () => this.updateStyle(fk, inp.value, elementId);
                            row.appendChild(inp);
                        }
                        c.appendChild(row);
                    });
                    return c;
                },
                custom_css: () => {
                    const ta = document.createElement('textarea');
                    ta.id = `ctrl-${key}`;
                    ta.value = typeof value === 'string' ? value : '';
                    ta.placeholder = 'Ex: color: red !important;\nbackground: #fff;';
                    ta.spellcheck = false;
                    ta.style.cssText = 'width:100%;padding:.45rem .6rem;background:var(--pb-surface2);border:1px solid var(--pb-border);border-radius:4px;color:var(--pb-text);font-size:.78rem;min-height:80px;font-family:"SF Mono",Menlo,Monaco,Consolas,monospace;resize:vertical;box-sizing:border-box';
                    ta.onchange = (e) => this.updateSetting(key, e.target.value, elementId);
                    return ta;
                },
                animation: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                    const animRow = document.createElement('div');
                    animRow.className = 'pb-control';
                    const animLabel = document.createElement('label');
                    animLabel.textContent = 'Entrance Animation';
                    animRow.appendChild(animLabel);
                    const animSel = document.createElement('select');
                    animSel.id = `ctrl-${key}`;
                    ['none','fadeIn','fadeInUp','fadeInDown','fadeInLeft','fadeInRight','slideInUp','slideInDown','slideInLeft','slideInRight','zoomIn','zoomInUp','zoomInDown','bounceIn','bounceInUp','bounceInDown','flipInX','flipInY','rotateIn','lightSpeedIn'].forEach(opt => {
                        const o = document.createElement('option');
                        o.value = opt; o.textContent = opt;
                        if (opt === (value || 'none')) o.selected = true;
                        animSel.appendChild(o);
                    });
                    animSel.onchange = () => this.updateSetting(key, animSel.value, elementId);
                    animRow.appendChild(animSel);
                    c.appendChild(animRow);
                    const durRow = document.createElement('div');
                    durRow.className = 'pb-control';
                    const durLabel = document.createElement('label');
                    durLabel.textContent = 'Duration';
                    durRow.appendChild(durLabel);
                    const durSel = document.createElement('select');
                    ['slow','normal','fast'].forEach(opt => {
                        const o = document.createElement('option');
                        o.value = opt; o.textContent = opt;
                        const curDur = (value === 'none' || !value) ? 'normal' : 'normal';
                        durRow.style.display = (value && value !== 'none') ? '' : 'none';
                        if (opt === 'normal') o.selected = true;
                        durSel.appendChild(o);
                    });
                    const durKey = key + '_duration';
                    durSel.onchange = () => this.updateSetting(durKey, durSel.value, elementId);
                    durRow.appendChild(durSel);
                    c.appendChild(durRow);
                    const delayRow = document.createElement('div');
                    delayRow.className = 'pb-control';
                    delayRow.style.display = (value && value !== 'none') ? '' : 'none';
                    const delayLabel = document.createElement('label');
                    delayLabel.textContent = 'Delay (ms)';
                    delayRow.appendChild(delayLabel);
                    const delayInp = document.createElement('input');
                    delayInp.type = 'number'; delayInp.min = 0; delayInp.max = 5000; delayInp.step = 100;
                    delayInp.value = 0;
                    const delayKey = key + '_delay';
                    delayInp.onchange = () => this.updateSetting(delayKey, parseInt(delayInp.value) || 0, elementId);
                    delayRow.appendChild(delayInp);
                    c.appendChild(delayRow);
                    animSel.onchange = () => {
                        this.updateSetting(key, animSel.value, elementId);
                        const show = animSel.value && animSel.value !== 'none';
                        durRow.style.display = show ? '' : 'none';
                        delayRow.style.display = show ? '' : 'none';
                    };
                    return c;
                },
                visibility: () => {
                    const c = document.createElement('div');
                    c.style.cssText = 'display:flex;flex-direction:column;gap:.5rem';
                    const defs = [
                        { fk: 'visibility_desktop', label: '🖥️ Visible on Desktop', default: true },
                        { fk: 'visibility_tablet', label: '📱 Visible on Tablet', default: true },
                        { fk: 'visibility_mobile', label: '📲 Visible on Mobile', default: true },
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
                        cb.onchange = () => { updateSlider(); this.updateSetting(fk, cb.checked, elementId); };
                        sw.appendChild(cb);
                        sw.appendChild(slider);
                        row.appendChild(lbl);
                        row.appendChild(sw);
                        c.appendChild(row);
                    });
                    return c;
                },
            };
            return (types[ctrl.type] || types.text)();
    },

    updateSetting(key, value, elementId, reload = true) {
        this.dirty = true;
        if (this.cachedSettings) this.cachedSettings[key] = value;
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
        .then(() => {
            if (reload) this.reloadElement(elementId);
            this.snapshotHistory();
        })
        .catch(err => { console.error('updateSetting failed:', err); this.toastError('Falha ao atualizar configuração'); });
    },

    updateStyle(key, value, elementId, reload = true) {
        this.dirty = true;
        if (this.cachedStyles) this.cachedStyles[key] = value;
        const styles = {};
        styles[key] = value;
        fetch(`/page-builder/elements/${elementId}/styles`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ styles }),
        })
        .then(r => {
            if (!r.ok) throw new Error(`HTTP ${r.status}`);
            return r.json();
        })
        .then(() => {
            if (reload) this.reloadElement(elementId);
            this.snapshotHistory();
        })
        .catch(err => { console.error('updateStyle failed:', err); this.toastError('Falha ao atualizar estilo'); });
    },

    reloadElement(id) {
        fetch(`/page-builder/elements/${id}/render`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.json();
            })
            .then(data => {
                const el = document.querySelector(`.pb-el[data-el-id="${id}"]`);
                if (el) {
                    const oldContent = el.querySelector('.pb-el-content');
                    if (oldContent) oldContent.innerHTML = data.html;
                    else el.innerHTML = `<div class="pb-el-content">${data.html}</div>`;
                    this.renderMath();
                }
            })
            .catch(err => { console.error('reloadElement failed:', err); this.toastError('Falha ao recarregar elemento'); });
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

    save(silent) {
        if (this.saving) return;
        this.saving = true;
        let overlay = null;
        if (!silent) {
            overlay = document.createElement('div');
            overlay.className = 'saving-overlay';
            overlay.innerHTML = '<div class="saving-card"><div class="spinner"></div><span class="saving-text">Salvando...</span></div>';
            document.body.appendChild(overlay);
        }
        fetch(`/page-builder/pages/${this.pageId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ status: 'draft' }),
        })
        .then(r => r.json())
        .then(() => {
            this.saving = false;
            this.dirty = false;
            if (overlay) overlay.remove();
            if (!silent) this.toastSuccess('Página salva!');
        })
        .catch(() => { this.saving = false; if (overlay) overlay.remove(); this.toastError('Falha ao salvar'); });
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

    snapshotHistory() {
        fetch(`/page-builder/pages/${this.pageId}/elements`)
            .then(r => r.json())
            .then(data => this.pushHistory(data.elements))
            .catch(() => {});
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
        const snapshot = this.history[this.historyIndex];
        this._lastElements = snapshot || [];
        this.renderCanvas(snapshot);
        this.renderMath();
        this.renderStructure(snapshot);
        this.updateUndoButtons();

        fetch(`/page-builder/pages/${this.pageId}/elements/restore-snapshot`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ elements: snapshot }),
        }).catch(() => {});

        if (this.selectedId) {
            const found = this._findElement(snapshot, this.selectedId);
            if (found) {
                this.loadControls(this.selectedId);
            } else {
                this.selectedId = null;
                document.getElementById('settings-empty').style.display = '';
                document.getElementById('settings-form').style.display = 'none';
            }
        }
    },

    _findElement(elements, id) {
        for (const el of elements) {
            if (el.id == id) return el;
            if (el.children) {
                const found = this._findElement(el.children, id);
                if (found) return found;
            }
        }
        return null;
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
            if (target) {
                if (target.dataset.isContainer === 'true') {
                    target.classList.add('drop-over');
                } else {
                    const parentEl = target.parentElement ? target.parentElement.closest('.pb-el') : null;
                    if (parentEl && parentEl.dataset.isContainer === 'true') {
                        parentEl.classList.add('drop-over');
                    }
                }
            }
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
                    const parentEl = target.parentElement ? target.parentElement.closest('.pb-el') : null;
                    if (parentEl && parentEl.dataset.isContainer === 'true') {
                        parentId = parentEl.dataset.elId;
                    }
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
        const editableTypes = ['heading', 'text', 'button', 'callout'];
        dz.addEventListener('dblclick', (e) => {
            const el = e.target.closest('.pb-el');
            if (!el) return;
            if (e.target.closest('.pb-el-toolbar')) return;
            const type = el.dataset.elType;
            if (!editableTypes.includes(type)) return;
            let textEl = e.target.closest('h1, h2, h3, h4, h5, h6, p, span, a, button, label');
            if (!textEl) {
                const contentDiv = e.target.closest('.pb-el-content > div');
                if (contentDiv) textEl = contentDiv;
            }
            if (!textEl) return;
            if (el.dataset._editing) return;
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
                if (textEl._inlineKeydown) { textEl.removeEventListener('keydown', textEl._inlineKeydown); textEl._inlineKeydown = null; }
                const newHtml = textEl.innerHTML.trim();
                const origHtml = el.dataset._origHtml || '';
                if (newHtml && newHtml !== origHtml) {
                    const type = el.dataset.elType;
                    const key = { heading: 'title', text: 'content', button: 'text', callout: 'content' }[type] || 'title';
                    const elId = el.dataset.elId;
                    this.updateSetting(key, newHtml, elId, false);
                    if (this.selectedId == elId) {
                        const self = this;
                        setTimeout(function() { self.loadControls(elId); }, 100);
                    }
                }
            };
            textEl.addEventListener('blur', finish, { once: true });
            if (textEl._inlineKeydown) textEl.removeEventListener('keydown', textEl._inlineKeydown);
            textEl._inlineKeydown = (k) => {
                if (k.key === 'Enter' && !k.shiftKey) { k.preventDefault(); textEl.blur(); }
                if (k.key === 'Escape') { textEl.innerHTML = el.dataset._origHtml || ''; textEl.blur(); }
            };
            textEl.addEventListener('keydown', textEl._inlineKeydown);
        });
    },

    bindKeyboard() {
        document.addEventListener('keydown', e => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) { e.preventDefault(); this.undo(); }
            if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) { e.preventDefault(); this.redo(); }
            if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); this.redo(); }
            if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); this.save(); }
            if (e.key === 'Delete' && this.selectedId) { this.deleteSelected(); }
        });
    },

    autoSave() {
        setInterval(() => {
            if (this.dirty) this.save(true);
        }, 60000);
    },

    showToast(msg, type) {
        const existing = document.querySelectorAll('.pb-toast');
        existing.forEach(t => { t.classList.add('pb-toast-out'); setTimeout(() => t.remove(), 300); });
        const t = document.createElement('div');
        t.className = 'pb-toast';
        if (type === 'error') t.style.borderColor = 'rgba(239,68,68,.4)';
        if (type === 'success') t.style.borderColor = 'rgba(34,197,94,.4)';
        t.textContent = msg;
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

    toggleNavigator() {
        const nav = document.getElementById('navigator');
        nav.classList.toggle('open');
        if (nav.classList.contains('open')) this.renderNavigator();
    },

    renderNavigator() {
        const body = document.getElementById('navigator-body');
        body.innerHTML = '';
        const els = this._lastElements || [];
        this._renderNavItems(els, body, 0);
    },

    _renderNavItems(elements, container, depth) {
        (elements || []).forEach(el => {
            const item = document.createElement('div');
            item.className = 'pb-nav-item' + (this.selectedId === el.id ? ' active' : '');
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
            icon.innerHTML = this.structureIcon(el.type);

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
                this.selectElement(el.id);
                this.renderNavigator();
            };

            item.ondblclick = (e) => {
                e.stopPropagation();
                this._startNavRename(item, name, el);
            };

            item.oncontextmenu = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.selectElement(el.id);
                this.renderNavigator();
                this._showNavContext(e.clientX, e.clientY, el);
            };

            item.ondragover = (e) => { e.preventDefault(); item.classList.add('drag-over'); };
            item.ondragleave = () => item.classList.remove('drag-over');
            item.ondrop = (e) => {
                e.preventDefault();
                item.classList.remove('drag-over');
                const dragId = parseInt(e.dataTransfer.getData('text/plain'));
                if (dragId && dragId !== el.id) this._navMoveElement(dragId, el.id);
            };

            item.draggable = true;
            item.ondragstart = (e) => {
                e.dataTransfer.setData('text/plain', el.id);
                e.dataTransfer.effectAllowed = 'move';
            };

            container.appendChild(item);

            if (hasChildren) {
                const childDiv = document.createElement('div');
                childDiv.className = 'pb-nav-children';
                container.appendChild(childDiv);
                this._renderNavItems(el.children, childDiv, depth + 1);
            }
        });
    },

    _startNavRename(item, nameEl, el) {
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
            fetch(`/page-builder/elements/${el.id}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify({ name: newName }),
            }).catch(() => {});
        };

        input.onblur = save;
        input.onkeydown = (e) => {
            if (e.key === 'Enter') save();
            if (e.key === 'Escape') { input.replaceWith(nameEl); }
        };
    },

    _showNavContext(x, y, el) {
        this._hideNavContext();
        const ctx = document.createElement('div');
        ctx.className = 'pb-nav-context';
        ctx.style.left = x + 'px';
        ctx.style.top = y + 'px';

        const items = [
            { label: '✎ Rename', action: () => { const item = document.querySelector(`.pb-nav-item[data-el-id="${el.id}"] .nav-name`); if (item) this._startNavRename(item, item, el); } },
            { label: '⧉ Duplicate', action: () => this.duplicateElement(el.id) },
            { sep: true },
            { label: '↑ Move Up', action: () => this._navMoveRelative(el.id, -1) },
            { label: '↓ Move Down', action: () => this._navMoveRelative(el.id, 1) },
            { sep: true },
            { label: '⧉ Copy', action: () => { this._clipboard = JSON.parse(JSON.stringify(el)); this.toast('Elemento copiado'); } },
            { label: '📋 Paste (após)', action: () => this._navPasteAfter(el.id) },
            { sep: true },
            { label: '✕ Delete', cls: 'danger', action: () => this.deleteElement(el.id) },
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
            btn.onclick = (e) => { e.stopPropagation(); this._hideNavContext(); m.action(); };
            ctx.appendChild(btn);
        });

        document.body.appendChild(ctx);

        const closeCtx = (e) => {
            if (!ctx.contains(e.target)) { this._hideNavContext(); document.removeEventListener('click', closeCtx); }
        };
        setTimeout(() => document.addEventListener('click', closeCtx), 10);
    },

    _hideNavContext() {
        document.querySelectorAll('.pb-nav-context').forEach(c => c.remove());
    },

    _navMoveElement(dragId, targetId) {
        const findParent = (elements, id, parent) => {
            for (const el of elements) {
                if (el.id === id) return parent;
                if (el.children) {
                    const found = findParent(el.children, id, el);
                    if (found) return found;
                }
            }
            return null;
        };

        const findEl = (elements, id) => {
            for (const el of elements) {
                if (el.id === id) return el;
                if (el.children) {
                    const found = findEl(el.children, id);
                    if (found) return found;
                }
            }
            return null;
        };

        const els = this._lastElements || [];
        const dragEl = findEl(els, dragId);
        const targetEl = findEl(els, targetId);
        if (!dragEl || !targetEl) return;

        const dragParent = findParent(els, dragId, null);
        const targetParent = findParent(els, targetId, null);

        if (dragParent && dragParent.children) {
            dragParent.children = dragParent.children.filter(e => e.id !== dragId);
        } else {
            this._lastElements = els.filter(e => e.id !== dragId);
        }

        const insertInto = (el) => {
            if (!el.children) el.children = [];
            el.children.push(dragEl);
        };

        if (targetParent) {
            const siblings = dragParent && dragParent.id === targetParent.id ? (dragParent.children || this._lastElements) : (targetParent.children || this._lastElements);
            const idx = siblings.findIndex(e => e.id === targetId);
            siblings.splice(idx + 1, 0, dragEl);
        } else {
            const idx = this._lastElements.findIndex(e => e.id === targetId);
            this._lastElements.splice(idx + 1, 0, dragEl);
        }

        this.renderCanvas(this._lastElements);
        this.renderStructure(this._lastElements);
        this.renderNavigator();
        this.renderMath();
        this._saveElementOrder(dragId, targetId);
    },

    _navMoveRelative(elId, direction) {
        const els = this._lastElements || [];
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
            this.renderCanvas(els);
            this.renderStructure(els);
            this.renderNavigator();
            this.renderMath();
        }
    },

    _navPasteAfter(targetId) {
        if (!this._clipboard) return;
        const els = this._lastElements || [];
        const clone = JSON.parse(JSON.stringify(this._clipboard));
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
            this.renderCanvas(els);
            this.renderStructure(els);
            this.renderNavigator();
            this.renderMath();
        }
    },

    _saveElementOrder(dragId, targetId) {
        const buildOrder = (elements) => {
            return elements.map(el => ({
                id: el.id,
                children: el.children ? buildOrder(el.children) : [],
            }));
        };
        const order = buildOrder(this._lastElements || []);
        fetch(`/page-builder/pages/${this.pageId}/elements/reorder`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrf },
            body: JSON.stringify({ order }),
        }).catch(() => this.toastError('Falha ao reordenar'));
    },
};
