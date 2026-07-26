<div class="pb-panel" role="navigation" aria-label="Painel de widgets">
    <div class="pb-panel-tabs" role="tablist" aria-label="Abas do painel">
        <button class="pb-panel-tab active" data-tab="widgets" onclick="editor.switchTab('widgets')" role="tab" aria-selected="true">&#128161; Widgets</button>
        <button class="pb-panel-tab" data-tab="navigator" onclick="editor.switchTab('navigator')" role="tab" aria-selected="false">&#9776; Navigator</button>
        <button class="pb-panel-tab" data-tab="structure" onclick="editor.switchTab('structure')" role="tab" aria-selected="false">&#9776; Estrutura</button>
        <button class="pb-panel-tab" data-tab="layouts" onclick="editor.switchTab('layouts')" role="tab" aria-selected="false">&#128196; Layouts</button>
    </div>
    <div class="pb-panel-body" id="panel-widgets">
        <div class="pb-widget-search">
            <input type="text" id="widget-search-input" placeholder="Buscar widgets..." autocomplete="off" aria-label="Buscar widgets">
            <span class="pb-widget-search-icon">&#128269;</span>
        </div>
        <div class="pb-widget-groups-wrap">
            <div class="pb-widget-group" data-group="layout">
                <div class="pb-widget-group-title">Layout</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="section" data-search="seção section layout container"><span class="pb-widget-icon">&#9638;</span><span class="pb-widget-label">Seção</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="inner_section" data-search="inner section seção interna nested columns colunas"><span class="pb-widget-icon">&#9641;</span><span class="pb-widget-label">Inner Section</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="column" data-search="coluna column layout"><span class="pb-widget-icon">&#9646;</span><span class="pb-widget-label">Coluna</span></div>
                </div>
            </div>
            <div class="pb-widget-group" data-group="basic">
                <div class="pb-widget-group-title">Básicos</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="heading" data-search="título heading h1 h2 h3标题"><span class="pb-widget-icon">H</span><span class="pb-widget-label">Título</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="text" data-search="texto text parágrafo content"><span class="pb-widget-icon">T</span><span class="pb-widget-label">Texto</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="image" data-search="imagem image foto photo"><span class="pb-widget-icon">&#128247;</span><span class="pb-widget-label">Imagem</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="button" data-search="botão button cta link"><span class="pb-widget-icon">&#128206;</span><span class="pb-widget-label">Botão</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="video" data-search="vídeo video youtube vimeo"><span class="pb-widget-icon">&#127909;</span><span class="pb-widget-label">Vídeo</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="divider" data-search="divisor divider linha line separador"><span class="pb-widget-icon">&#128901;</span><span class="pb-widget-label">Divisor</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="spacer" data-search="espaçador spacer espaço gap"><span class="pb-widget-icon">&#8693;</span><span class="pb-widget-label">Espaçador</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="icon" data-search="ícone icon star"><span class="pb-widget-icon">&#11088;</span><span class="pb-widget-label">Ícone</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="gallery" data-search="galeria gallery fotos images"><span class="pb-widget-icon">&#128444;</span><span class="pb-widget-label">Galeria</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="form" data-search="formulário form contato contact"><span class="pb-widget-icon">&#128203;</span><span class="pb-widget-label">Formulário</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="tabs" data-search="abas tabs tab"><span class="pb-widget-icon">&#128209;</span><span class="pb-widget-label">Abas</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="accordion" data-search="accordion collapse expand"><span class="pb-widget-icon">&#129703;</span><span class="pb-widget-label">Accordion</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="icon_box" data-search="icon box ícone caixa"><span class="pb-widget-icon">&#128196;</span><span class="pb-widget-label">Icon Box</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="image_box" data-search="image box imagem caixa"><span class="pb-widget-icon">&#128444;</span><span class="pb-widget-label">Image Box</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="carousel" data-search="carrossel slider"><span class="pb-widget-icon">&#128256;</span><span class="pb-widget-label">Carrossel</span></div>
                </div>
            </div>
            <div class="pb-widget-group" data-group="pro">
                <div class="pb-widget-group-title">Pro</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="counter" data-search="counter contador número animado"><span class="pb-widget-icon">&#128200;</span><span class="pb-widget-label">Contador</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="progress_bar" data-search="progress bar progresso barra"><span class="pb-widget-icon">&#9612;</span><span class="pb-widget-label">Progresso</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="social_icons" data-search="social icons redes sociais"><span class="pb-widget-icon">&#128101;</span><span class="pb-widget-label">Social</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="testimonial" data-search="testimonial depoimento review"><span class="pb-widget-icon">&#128172;</span><span class="pb-widget-label">Testimonial</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="price_table" data-search="price table preço plano pricing"><span class="pb-widget-icon">&#128176;</span><span class="pb-widget-label">Price Table</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="countdown" data-search="countdown contagem regressiva timer"><span class="pb-widget-icon">&#9201;</span><span class="pb-widget-label">Countdown</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="google_maps" data-search="google maps mapa localização"><span class="pb-widget-icon">&#128205;</span><span class="pb-widget-label">Mapa</span></div>
                </div>
            </div>
            <div class="pb-widget-group" data-group="edu">
                <div class="pb-widget-group-title">Educacional</div>
                <div class="pb-widget-grid">
                    <div class="pb-widget-item" draggable="true" data-type="callout" data-search="callout alerta dica info warning"><span class="pb-widget-icon">&#9888;</span><span class="pb-widget-label">Callout</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="table" data-search="tabela table dados"><span class="pb-widget-icon">&#9638;</span><span class="pb-widget-label">Tabela</span></div>
                    <div class="pb-widget-item" draggable="true" data-type="math" data-search="matemática math fórmula formula latex"><span class="pb-widget-icon">&Sigma;</span><span class="pb-widget-label">Matemática</span></div>
                </div>
            </div>
            <div id="widget-search-empty" style="display:none;text-align:center;padding:2rem 1rem;color:var(--pb-text2);font-size:.8rem">
                <div style="font-size:2rem;margin-bottom:.5rem;opacity:.4">&#128269;</div>
                Nenhum widget encontrado
            </div>
        </div>
    </div>
    <div class="pb-panel-body" id="panel-navigator" style="display:none">
        <div class="pb-navigator-body" id="navigator-body"></div>
    </div>
    <div class="pb-panel-body" id="panel-structure" style="display:none">
        <ul class="pb-structure-tree" id="structure-tree"></ul>
    </div>
    <div class="pb-panel-body" id="panel-layouts" style="display:none">
        <div class="pb-layout-templates" id="layout-templates"></div>
    </div>
</div>