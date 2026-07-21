<div class="pb-toolbar">
    <span class="pb-toolbar-title"><a href="{{ route('page-builder.pages.index') }}" class="btn-back">&#8592; Voltar</a> {{ $page->title }}</span>
    <span class="pb-toolbar-badge badge-{{ $page->status === 'published' ? 'published' : 'draft' }}" style="background:{{ $page->status === 'published' ? 'rgba(34,197,94,.15)' : 'rgba(245,158,11,.15)' }};color:{{ $page->status === 'published' ? '#22c55e' : '#f59e0b' }}">{{ $page->status }}</span>
    <div class="pb-toolbar-spacer"></div>
    <button id="pb-undo" onclick="editor.undo()" title="Desfazer (Ctrl+Z)">&#8630; <span style="font-size:.65rem;opacity:.6">Desfazer</span></button>
    <button id="pb-redo" onclick="editor.redo()" title="Refazer (Ctrl+Shift+Z)">&#8631; <span style="font-size:.65rem;opacity:.6">Refazer</span></button>
    <span class="tb-divider"></span>
    <button class="active" data-mode="desktop" onclick="editor.setResponsive('desktop')" title="Desktop">&#128421;</button>
    <button data-mode="tablet" onclick="editor.setResponsive('tablet')" title="Tablet">&#128241;</button>
    <button data-mode="mobile" onclick="editor.setResponsive('mobile')" title="Mobile">&#128241;</button>
    <span class="tb-divider"></span>
    <button id="pb-zoom-out" onclick="editor.zoomOut()" title="Zoom Out (-)" style="padding:.4rem .5rem">&#8722;</button>
    <span id="pb-zoom-label" style="font-size:.7rem;color:var(--pb-text2);min-width:38px;text-align:center;cursor:pointer" onclick="editor.zoomReset()" title="Reset Zoom (Ctrl+0)">100%</span>
    <button id="pb-zoom-in" onclick="editor.zoomIn()" title="Zoom In (+)" style="padding:.4rem .5rem">+</button>
    <button id="pb-fullscreen" onclick="editor.toggleFullscreen()" title="Tela Cheia (F11)" style="padding:.4rem .5rem">&#9974;</button>
    <span class="tb-divider"></span>
    <button onclick="editor.showPageSettings()" title="Configurações da Página" id="btn-page-settings">&#9881; <span style="font-size:.65rem;opacity:.6">Página</span></button>
    <a href="{{ route('page-builder.render', $page) }}?t={{ time() }}" target="_blank" class="tb-link">&#128065; <span style="font-size:.65rem;opacity:.6">Visualizar</span></a>
    <span class="tb-divider"></span>
    <button onclick="editor.exportPage()" title="Exportar como JSON">&#128229;</button>
    <button onclick="editor.copyHtml()" title="Copiar HTML da página">&#128203;</button>
    <button onclick="editor.importHtml()" title="Importar HTML de site externo">&#128228; <span style="font-size:.65rem;opacity:.6">Importar HTML</span></button>
    <span class="tb-divider"></span>
    <button onclick="editor.save()" class="btn-save">&#128190; Salvar</button>
    <button onclick="editor.publish()" class="btn-publish">&#128752; Publicar</button>
</div>