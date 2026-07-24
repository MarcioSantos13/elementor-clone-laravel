import { escHtml } from './utils.js';

export function renderCanvas(state, elements, parentEl) {
    const dz = document.getElementById('canvas-dropzone');
    if (!parentEl) {
        dz.innerHTML = '';
        if (!elements || elements.length === 0) {
            dz.innerHTML = `<div class="pb-empty-canvas" id="empty-canvas"><div class="pb-empty-icon">&#128161;</div><p><strong>Arraste widgets do painel esquerdo</strong><br>para comecar a construir sua pagina</p></div>`;
            return;
        }
    }
    (elements || []).forEach(el => {
        const div = document.createElement('div');
        div.className = 'pb-el';
        div.dataset.elId = el.id;
        div.dataset.elType = el.type;
        div.dataset.isContainer = el.is_container ? 'true' : 'false';
        div.innerHTML = elementHtml(el);
        div.onclick = (e) => { e.stopPropagation(); state.onSelectElement(el.id); };
        const dragHandle = div.querySelector('.pb-el-drag');
        if (dragHandle) {
            dragHandle.ondragstart = (e) => {
                e.dataTransfer.setData('text/plain', String(el.id));
                e.dataTransfer.effectAllowed = 'move';
                div.style.opacity = '.4';
            };
            dragHandle.ondragend = () => { div.style.opacity = ''; };
        }
        if (parentEl) parentEl.appendChild(div);
        else dz.appendChild(div);
        if (el.children && el.children.length > 0) {
            const childContainer = document.createElement('div');
            childContainer.className = 'pb-el-children';
            div.appendChild(childContainer);
            renderCanvas(state, el.children, childContainer);
        }
    });
    if (!parentEl) state.renderMath();
}

export function renderMath() {
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
}

export function elementHtml(el) {
    let name = el.name || el.type;
    const s = el.settings || {};
    let preview = '';
    switch (el.type) {
        case 'heading': {
            const tagSizeMap = {h1:'2.2em',h2:'1.8em',h3:'1.4em',h4:'1.15em',h5:'1em',h6:'.85em'};
            const sizeMap = {small:'1.2em',medium:'2.5em',large:'3em',xl:'3.5em',xxl:'4.5em'};
            const fs = s.size && sizeMap[s.size] ? sizeMap[s.size] : (tagSizeMap[s.tag] || '1.8em');
            preview = `<${s.tag || 'h2'} style="text-align:${s.alignment||'left'};color:${s.color||'#333'};font-size:${fs};font-weight:${s.font_weight||'700'};line-height:${s.line_height||'1.4'}">${escHtml(s.title||'Heading')}</${s.tag || 'h2'}>`;
            break;
        }
        case 'text': preview = `<div style="text-align:${s.alignment||'left'};color:${s.color||'#666'};font-size:${s.font_size||'16px'};font-weight:${s.font_weight||'400'};line-height:${s.line_height||'1.7'}">${s.content||'<p>Text content</p>'}</div>`; break;
        case 'image':
            if (s.image && s.image.url) preview = `<div style="text-align:${s.alignment||'center'}"><img src="${escHtml(s.image.url)}" alt="${escHtml(s.image.alt||'')}" style="width:${s.width||'100%'};max-width:${s.max_width||'100%'};height:${s.height||'auto'};object-fit:${s.object_fit||'cover'};border-radius:${s.border_radius||'0px'};opacity:${s.opacity||1}"></div>`;
            else preview = `<div class="pb-image-placeholder" style="text-align:center;padding:2rem;color:#999">Nenhuma imagem selecionada</div>`;
            break;
        case 'button': {
            const sizeMap = {small:{p:'8px 16px',f:'14px'},medium:{p:'12px 24px',f:'16px'},large:{p:'16px 32px',f:'18px'},xl:{p:'20px 40px',f:'20px'}};
            const sz = sizeMap[s.size]||sizeMap.medium;
            const btn = `<button style="background-color:${s.background_color||'#007bff'};color:${s.text_color||'#fff'};border:${s.border_width||'0px'} solid ${s.border_color||'transparent'};border-radius:${s.border_radius||'4px'};padding:${sz.p};font-size:${sz.f};font-weight:${s.font_weight||'500'};cursor:pointer;display:inline-block">${escHtml(s.text||'Button')}</button>`;
            preview = s.alignment !== 'stretch' ? `<div style="text-align:${s.alignment||'left'}">${btn}</div>` : btn;
            break;
        }
        case 'callout': {
            const typeStyles = {info:{bg:'#eff6ff',border:'#3b82f6',icon:'&#9432;',text:'#1e3a5f',title:'#1e40af'},success:{bg:'#f0fdf4',border:'#22c55e',icon:'&#10004;',text:'#14532d',title:'#166534'},warning:{bg:'#fffbeb',border:'#f59e0b',icon:'&#9888;',text:'#78350f',title:'#92400e'},danger:{bg:'#fef2f2',border:'#ef4444',icon:'&#10060;',text:'#7f1d1d',title:'#991b1b'},tip:{bg:'#f0f9ff',border:'#0ea5e9',icon:'&#128161;',text:'#0c3547',title:'#0c4a6e'},definition:{bg:'#faf5ff',border:'#a855f7',icon:'&#128214;',text:'#581c87',title:'#6b21a8'},theorem:{bg:'#fff7ed',border:'#f97316',icon:'&#9878;',text:'#7c2d12',title:'#9a3412'},exercise:{bg:'#ecfdf5',border:'#10b981',icon:'&#9998;',text:'#064e3b',title:'#065f46'},note:{bg:'#f8fafc',border:'#64748b',icon:'&#128221;',text:'#475569',title:'#334155'}};
            const st = typeStyles[s.type]||typeStyles.info;
            const borderStyle = s.border_style==='none'?'border-left:none;':s.border_style==='full'?`border:2px solid ${st.border};`:`border-left:4px solid ${st.border};`;
            const titleHtml = s.title ? `<div style="font-weight:700;font-size:1rem;margin-bottom:6px;color:${st.title}">${escHtml(s.title)}</div>` : '';
            preview = `<div style="background:${st.bg};${borderStyle}padding:${s.padding||'16px 20px'};border-radius:${s.border_radius||'8px'};color:${st.text}"><div style="display:flex;align-items:flex-start;gap:10px"><span style="font-size:1.2em;flex-shrink:0">${escHtml(s.icon)||st.icon}</span><div style="flex:1">${titleHtml}${s.content||'<p>Conteúdo do callout</p>'}</div></div></div>`;
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
                    if (r===0&&hd) html += `<th style="background:#f1f5f9;border:${bw} solid ${bc};padding:${s.cell_padding||'10px 14px'};font-weight:600;text-align:left">Cabecalho ${c+1}</th>`;
                    else html += `<td style="border:${bw} solid ${bc};padding:${s.cell_padding||'10px 14px'}">Conteudo</td>`;
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
                ? `<div style="text-align:${s.alignment||'center'};padding:16px 0"><span class="pb-math" data-formula="${escHtml(formula)}" data-display="true" style="font-size:${s.font_size||'24px'};color:${s.color||'#333'}"></span>${s.label?`<div style="margin-top:6px;font-size:0.8em;color:#666;font-style:italic">${escHtml(s.label)}</div>`:''}</div>`
                : `<span class="pb-math" data-formula="${escHtml(formula)}" data-display="false" style="font-size:${s.font_size||'16px'};color:${s.color||'#333'}"></span>`;
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
                    embedUrl = 'https://www.youtube.com/embed/'+ytMatch[1]+'?'+params.join('&');
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
                preview = `<div style="${wStyle};${mAlign}"><div style="position:relative;padding-bottom:${pad};height:0;overflow:hidden;border-radius:8px"><iframe src="${escHtml(embedUrl)}" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" allowfullscreen allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" title="video"></iframe></div></div>`;
            } else {
                preview = '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">Nenhum video selecionado</div>';
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
                preview = '<div style="text-align:center;padding:2rem;color:#999;background:#f5f5f5;border-radius:8px">Nenhuma imagem selecionada</div>';
            } else {
                let ghtml = `<div style="display:grid;grid-template-columns:repeat(${cols},1fr);gap:${gGap}px">`;
                imgs.slice(0, 12).forEach(img => {
                    ghtml += `<div style="overflow:hidden;border-radius:${br}px;aspect-ratio:1;background:#f1f5f9"><img src="${escHtml(img.url||'')}" alt="${escHtml(img.alt||'')}" style="width:100%;height:100%;object-fit:cover;border-radius:${br}px"></div>`;
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
                fhtml += `<div style="margin-bottom:${fsp}px"><label style="display:block;margin-bottom:4px;font-size:13px;font-weight:500;color:#374151">${escHtml(f.label||'')}${req}</label>`;
                if (f.type === 'textarea') {
                    fhtml += `<div style="width:100%;min-height:50px;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#f9fafb;font-size:12px;color:#9ca3af">Textarea</div>`;
                } else if (f.type === 'select') {
                    fhtml += `<div style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#fff;font-size:12px;color:#9ca3af;display:flex;justify-content:space-between"><span>Select...</span><span>&#9660;</span></div>`;
                } else if (f.type === 'checkbox' || f.type === 'radio') {
                    fhtml += `<div style="display:flex;align-items:center;gap:6px"><input type="${f.type}" disabled style="width:auto"><span style="font-size:12px;color:#374151">${escHtml(f.label||'')}</span></div>`;
                } else {
                    fhtml += `<div style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:${fbr}px;background:#f9fafb;font-size:12px;color:#9ca3af">${escHtml(f.type||'text')}</div>`;
                }
                fhtml += '</div>';
            });
            const btnLabel = escHtml(s.button_text || 'Send');
            fhtml += `<button type="button" style="padding:10px 24px;background:${bc};color:${btc};border:none;border-radius:${fbr}px;font-size:13px;font-weight:500;cursor:default;${bw}">${btnLabel}</button>`;
            preview = fhtml;
            break;
        }
        case 'tabs': {
            const tabs = Array.isArray(s.tabs) ? s.tabs : [];
            const tc = s.tab_color || '#6366f1';
            const bc2 = s.border_color || '#e2e8f0';
            const ati = s.active_tab || 0;
            if (tabs.length === 0) { preview = '<div style="text-align:center;padding:1rem;color:#999">Nenhuma aba</div>'; break; }
            let thead = '<div style="display:flex;border-bottom:2px solid ' + bc2 + '">';
            let tbody = '';
            tabs.forEach((t, i) => {
                const active = i === ati;
                thead += `<button type="button" style="padding:8px 16px;font-size:13px;border:none;border-bottom:3px solid ${active?tc:'transparent'};margin-bottom:-2px;background:${active?'#fff':'transparent'};color:${active?tc:'#6b7280'};font-weight:${active?'600':'400'};cursor:default">${escHtml(t.title||'Tab '+(i+1))}</button>`;
                tbody += `<div style="display:${active?'block':'none'};padding:16px;font-size:13px;color:#6b7280">${t.content?escHtml(String(t.content).substring(0,100)):'...'}</div>`;
            });
            thead += '</div>';
            preview = thead + tbody;
            break;
        }
        case 'accordion': {
            const items = Array.isArray(s.items) ? s.items : [];
            const ac = s.tab_color || '#6366f1';
            const ab = s.border_color || '#e2e8f0';
            if (items.length === 0) { preview = '<div style="text-align:center;padding:1rem;color:#999">Nenhum item</div>'; break; }
            let ahtml = '';
            items.forEach((item, i) => {
                const isOpen = item.open;
                ahtml += `<div style="border:1px solid ${ab};border-radius:8px;overflow:hidden;margin-bottom:2px">`;
                ahtml += `<div style="display:flex;align-items:center;padding:10px 14px;font-size:13px;font-weight:500;background:${isOpen?ac:'#f9fafb'};color:${isOpen?'#fff':'#374151'}"><span style="display:inline-block;transform:rotate(${isOpen?'90':'0'}deg);margin-right:8px;font-size:10px">&#9654;</span>${escHtml(item.title||'Section '+(i+1))}</div>`;
                if (isOpen) ahtml += `<div style="padding:12px;font-size:12px;color:#6b7280;background:#fff">${item.content?escHtml(String(item.content).substring(0,80)):'...'}</div>`;
                ahtml += '</div>';
            });
            preview = ahtml;
            break;
        }
        case 'section': {
            const bgColor = s.background_color || 'transparent';
            const pt = s.padding_top || '40px';
            const pr = s.padding_right || '0px';
            const pb = s.padding_bottom || '40px';
            const pl = s.padding_left || '0px';
            const mt = s.margin_top || '0px';
            const mb = s.margin_bottom || '0px';
            const br = s.border_radius || '0px';
            const cw = s.content_width || '1140px';
            const layout = s.layout || 'boxed';
            const bgImage = s.background_image && s.background_image.url ? s.background_image.url : '';
            let secStyle = `padding:${pt} ${pr} ${pb} ${pl};margin:${mt} 0 ${mb} 0;border-radius:${br};position:relative;`;
            if (bgColor && bgColor !== 'transparent') secStyle += `background-color:${bgColor};`;
            if (bgImage) secStyle += `background-image:url('${escHtml(bgImage)}');background-position:center center;background-size:cover;background-repeat:no-repeat;`;
            const minH = s.min_height && s.min_height !== 'auto' ? `min-height:${s.min_height};` : '';
            if (minH) secStyle += minH;
            const innerStyle = layout === 'boxed' ? `max-width:${cw};margin:0 auto;` : '';
            preview = `<div class="pb-section-editor" style="${secStyle}"><div class="pb-section-header">Section</div><div class="pb-section-content" style="${innerStyle}"></div></div>`;
            break;
        }
        case 'column': {
            const colWidth = s.column_width || 'col-4';
            const vAlign = s.vertical_alignment || 'stretch';
            const cPos = s.content_position || 'top';
            const tAlign = s.text_align || '';
            const cBg = s.background_color || 'transparent';
            const cpt = s.padding_top || '10px';
            const cpr = s.padding_right || '10px';
            const cpb = s.padding_bottom || '10px';
            const cpl = s.padding_left || '10px';
            const cm = s.margin || '0px';
            const cbr = s.border_radius || '0px';
            let colStyle = `padding:${cpt} ${cpr} ${cpb} ${cpl};border-radius:${cbr};display:flex;flex-direction:column;align-self:${vAlign};justify-content:${cPos};`;
            if (tAlign) colStyle += `text-align:${tAlign};`;
            if (cm) colStyle += `margin:${cm};`;
            if (cBg && cBg !== 'transparent') colStyle += `background-color:${cBg};`;
            preview = `<div class="pb-column-editor ${colWidth}" style="${colStyle}"><div class="pb-column-header">Column</div><div class="pb-column-content"></div></div>`;
            break;
        }
        default: preview = `<div class="pb-el-placeholder">${el.type}</div>`;
    }
    return `<div class="pb-el-drag" draggable="true" title="Arrastar para reordenar">&#10023;</div><div class="pb-el-toolbar"><span class="pb-el-name">${escHtml(name)}</span><span class="pb-el-type">${el.type}</span><span style="flex:1"></span><button class="pb-el-action" onclick="event.stopPropagation();editor.duplicateElement(${el.id})" title="Duplicate">&#128203;</button><button class="pb-el-action" onclick="event.stopPropagation();editor.deleteElement(${el.id})" title="Delete">&#128465;</button></div><div class="pb-el-content">${preview}</div>`;
}

export function renderStructure(elements, parentUl) {
    const ul = parentUl || document.getElementById('structure-tree');
    if (!parentUl) ul.innerHTML = '';
    (elements || []).forEach(el => {
        const li = document.createElement('li');
        li.className = 'pb-structure-item';
        li.dataset.elId = el.id;
        li.innerHTML = `<span class="si-icon">${structureIcon(el.type)}</span><span>${el.name || el.type}</span><span class="si-type">${el.type}</span>`;
        li.onclick = (e) => { e.stopPropagation(); /* will be bound by editor */ };
        ul.appendChild(li);
        if (el.children && el.children.length > 0) {
            const childUl = document.createElement('ul');
            childUl.className = 'pb-structure-children';
            li.appendChild(childUl);
            renderStructure(el.children, childUl);
        }
    });
}
