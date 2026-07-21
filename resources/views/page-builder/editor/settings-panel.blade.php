<div class="pb-panel pb-panel-right">
    <div class="pb-settings" id="settings-panel">
        <div class="pb-settings-empty" id="settings-empty">
            <div class="pse-icon">&#9881;</div>
            <p>Selecione um elemento na tela<br>para editar suas configurações</p>
        </div>
        <div id="settings-form" class="pb-settings-form">
            <div class="pb-settings-header">
                <div>
                    <h3 id="settings-title">Element</h3>
                    <span class="pb-sh-type" id="settings-type">type</span>
                </div>
                <button onclick="editor.deleteSelected()" style="background:none;border:none;color:var(--pb-danger);cursor:pointer;font-size:1.1rem" title="Excluir elemento">&#128465;</button>
            </div>
            <div class="pb-editor-tabs" id="editor-tabs">
                <button class="pb-editor-tab active" data-etab="content" onclick="editor.switchEditorTab('content')">Content</button>
                <button class="pb-editor-tab" data-etab="style" onclick="editor.switchEditorTab('style')">Style</button>
                <button class="pb-editor-tab" data-etab="advanced" onclick="editor.switchEditorTab('advanced')">Advanced</button>
            </div>
            <div class="pb-settings-body" id="settings-body"></div>
        </div>
        <div id="page-settings-form" class="pb-settings-form">
            <div class="pb-settings-header">
                <div>
                    <h3>Layout da Página</h3>
                    <span class="pb-sh-type">Configurações da página</span>
                </div>
                <button onclick="editor.hidePageSettings()" style="background:none;border:none;color:var(--pb-text2);cursor:pointer;font-size:1.1rem" title="Fechar">&#10005;</button>
            </div>
            <div class="pb-settings-body" id="page-settings-body"></div>
        </div>
    </div>
</div>