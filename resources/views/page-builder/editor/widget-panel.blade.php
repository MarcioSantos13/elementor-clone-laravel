<div class="pb-panel">
    <div class="pb-panel-tabs">
        <button class="pb-panel-tab active" data-tab="widgets" onclick="editor.switchTab('widgets')">&#128161; Widgets</button>
        <button class="pb-panel-tab" data-tab="structure" onclick="editor.switchTab('structure')">&#9776; Estrutura</button>
        <button class="pb-panel-tab" data-tab="layouts" onclick="editor.switchTab('layouts')">&#128196; Layouts</button>
    </div>
    <div class="pb-panel-body" id="panel-widgets">
        <div class="pb-widget-group">
            <div class="pb-widget-group-title">Layout</div>
            <div class="pb-widget-grid">
                <div class="pb-widget-item" draggable="true" data-type="section"><span class="pb-widget-icon">&#9638;</span><span class="pb-widget-label">Seção</span></div>
                <div class="pb-widget-item" draggable="true" data-type="column"><span class="pb-widget-icon">&#9646;</span><span class="pb-widget-label">Coluna</span></div>
            </div>
        </div>
        <div class="pb-widget-group">
            <div class="pb-widget-group-title">Básicos</div>
            <div class="pb-widget-grid">
                <div class="pb-widget-item" draggable="true" data-type="heading"><span class="pb-widget-icon">H</span><span class="pb-widget-label">Título</span></div>
                <div class="pb-widget-item" draggable="true" data-type="text"><span class="pb-widget-icon">T</span><span class="pb-widget-label">Texto</span></div>
                <div class="pb-widget-item" draggable="true" data-type="image"><span class="pb-widget-icon">&#128247;</span><span class="pb-widget-label">Imagem</span></div>
                <div class="pb-widget-item" draggable="true" data-type="button"><span class="pb-widget-icon">&#128206;</span><span class="pb-widget-label">Botão</span></div>
                <div class="pb-widget-item" draggable="true" data-type="video"><span class="pb-widget-icon">&#127909;</span><span class="pb-widget-label">Vídeo</span></div>
                <div class="pb-widget-item" draggable="true" data-type="divider"><span class="pb-widget-icon">&#128901;</span><span class="pb-widget-label">Divisor</span></div>
                <div class="pb-widget-item" draggable="true" data-type="spacer"><span class="pb-widget-icon">&#8693;</span><span class="pb-widget-label">Espaçador</span></div>
                <div class="pb-widget-item" draggable="true" data-type="icon"><span class="pb-widget-icon">&#11088;</span><span class="pb-widget-label">Ícone</span></div>
                <div class="pb-widget-item" draggable="true" data-type="gallery"><span class="pb-widget-icon">&#128444;</span><span class="pb-widget-label">Galeria</span></div>
                <div class="pb-widget-item" draggable="true" data-type="form"><span class="pb-widget-icon">&#128203;</span><span class="pb-widget-label">Formulário</span></div>
                <div class="pb-widget-item" draggable="true" data-type="tabs"><span class="pb-widget-icon">&#128209;</span><span class="pb-widget-label">Abas</span></div>
                <div class="pb-widget-item" draggable="true" data-type="accordion"><span class="pb-widget-icon">&#129703;</span><span class="pb-widget-label">Accordion</span></div>
            </div>
        </div>
        <div class="pb-widget-group">
            <div class="pb-widget-group-title">Educacional</div>
            <div class="pb-widget-grid">
                <div class="pb-widget-item" draggable="true" data-type="callout"><span class="pb-widget-icon">&#9888;</span><span class="pb-widget-label">Callout</span></div>
                <div class="pb-widget-item" draggable="true" data-type="table"><span class="pb-widget-icon">&#9638;</span><span class="pb-widget-label">Tabela</span></div>
                <div class="pb-widget-item" draggable="true" data-type="math"><span class="pb-widget-icon">&Sigma;</span><span class="pb-widget-label">Matemática</span></div>
            </div>
        </div>
    </div>
    <div class="pb-panel-body" id="panel-structure" style="display:none">
        <ul class="pb-structure-tree" id="structure-tree"></ul>
    </div>
    <div class="pb-panel-body" id="panel-layouts" style="display:none">
        <div class="pb-layout-templates" id="layout-templates"></div>
    </div>
</div>