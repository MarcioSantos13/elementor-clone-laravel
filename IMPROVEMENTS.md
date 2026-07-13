# Plano de Melhorias - Elementor Clone Laravel

> Passo a passo detalhado para tornar o Page Builder o mais próximo possível do Elementor.
> Cada passo pode ser implementado e testado individualmente.

---

## Resumo Executivo

| Categoria | Total | Prioridade |
|-----------|-------|------------|
| Novos Widgets | 8 | Alta |
| Style Tab (abas de estilo) | 5 | Alta |
| Navigator (painel de estrutura) | 4 | Alta |
| Controles Avançados | 6 | Média |
| UX do Editor | 8 | Média |
| Atalhos e Duplicação | 4 | Média |
| Temas e Presets | 3 | Baixa |
| Responsividade | 4 | Baixa |
| **Total de passos** | **42** | |

---

## FASE 1 — Novos Widgets (Alta Prioridade)

### Passo 1: Widget de Vídeo ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/VideoWidget.php`
**Config:** Adicionar em `config/page-builder.php` no array `widgets`

- Criar classe `VideoWidget` estendendo `BaseWidget`
- Type: `video`, Label: `Video`, Icon: `🎬`, Categories: `['general']`
- Controls:
  - `video_type` (select: youtube, vimeo, custom)
  - `video_url` (url)
  - `autoplay` (boolean)
  - `loop` (boolean)
  - `controls` (boolean)
  - `start_time` (number)
  - `aspect_ratio` (select: 16:9, 4:3, 1:1)
- `render()`: Gerar `<iframe>` com URL embed formatada (youtube-nocookie.com)

**Como testar:**
1. Criar página → Arrastar widget "Vídeo"
2. Colar URL YouTube → Ver preview
3. Mudar aspect ratio → Canvas atualiza

---

### Passo 2: Widget de Divisor (Divider) ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/DividerWidget.php`

- Controls:
  - `style` (select: solid, dashed, dotted, double)
  - `width` (number, 0-100, %)
  - `thickness` (number, 1-20, px)
  - `color` (color)
  - `space_before` (number, px)
  - `space_after` (number, px)
- `render()`: `<hr>` com estilos inline
- Adicionar categoria `['general']`

**Como testar:**
1. Arrastar widget Divider → Ver linha horizontal
2. Mudar cor/espessura → Atualiza em tempo real
3. Arrastar para dentro de section → Funciona

---

### Passo 3: Widget de Espaçador (Spacer) ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/SpacerWidget.php`

- Controls:
  - `space` (number, 0-500, px) — default 50
  - `custom_css` (textarea — opcional)
- `render()`: `<div style="height: Xpx"></div>`
- Visual no editor: Mostrar faixa de cor clara com a altura

**Como testar:**
1. Arrastar Spacer → Ver espaço em branco
2. Ajustar slider → Altura muda no canvas

---

### Passo 4: Widget de Ícone (Icon) ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/IconWidget.php`

- Controls:
  - `icon` (select com lista Font Awesome: fa-star, fa-heart, fa-check, etc.)
  - `icon_size` (number, 12-200, px)
  - `color` (color)
  - `align` (select: left, center, right)
  - `link` (url)
  - `link_new_tab` (boolean)
- `render()`: `<i class="fas fa-{icon}"></i>` + link se houver
- Carregar Font Awesome via CDN no editor.blade.php

**Como testar:**
1. Arrastar Icon → Ver ícone
2. Mudar ícone → Atualiza
3. Mudar cor/tamanho → Atualiza
4. Adicionar link → Clica e abre

---

### Passo 5: Widget de Galeria (Gallery) ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/GalleryWidget.php`

- Controls:
  - `images` (gallery — array de {url, alt})
  - `columns` (select: 1-6)
  - `gap` (number, 0-50, px)
  - `layout` (select: grid, masonry)
  - `caption` (boolean)
- `render()`: Grid CSS com `<figure>` e `<figcaption>`
- No editor: Upload múltiplo com drag-and-drop

**Como testar:**
1. Arrastar Gallery → Seletor de imagens
2. Selecionar 4+ imagens → Grid aparece
3. Mudar colunas → Layout atualiza

---

### Passo 6: Widget de Contato (Form) ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/FormWidget.php`

- Controls:
  - `fields` (repeater: label, type, required)
  - `button_text` (text)
  - `button_color` (color)
  - `submit_action` (select: email, redirect, webhook)
  - `email_to` (text)
  - `redirect_url` (url)
- `render()`: `<form>` com campos e botão estilizado
- Backend: `FormController@submit` para processar envio
- **Nota:** Requer migration para `form_submissions` table

**Como testar:**
1. Arrastar Form → Ver formulário com campos padrão
2. Adicionar/remover campos → Formulário atualiza
3. Preview → Submeter formulário → Verificar email/redirecionamento

---

### Passo 7: Widget de Abas (Tabs) ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/TabsWidget.php`

- Controls:
  - `tabs` (repeater: title, content — content é rich text)
  - `active_tab` (number)
  - `orientation` (select: horizontal, vertical)
- `render()`: `<div class="tabs">` com JS de toggle
- Container: `isContainer = true` → Cada tab aceita sub-elementos
- **Complexidade média-alta** — requer JS no render

**Como testar:**
1. Arrastar Tabs → Ver 2 abas padrão
2. Clicar em abas → Conteúdo alterna
3. Adicionar terceira aba → Funciona
4. Arrastar widgets dentro de abas → Nesting funciona

---

### Passo 8: Widget de Accordion ✅ IMPLEMENTADO
**Arquivo:** `app/Services/PageBuilder/Widgets/AccordionWidget.php`

- Controls:
  - `items` (repeater: title, content, open by default)
  - `active_item` (number)
  - `icon_position` (select: left, right)
- `render()`: Accordion HTML + CSS (details/summary ou divs com JS)
- Container: `isContainer = true`

**Como testar:**
1. Arrastar Accordion → Ver 2 itens
2. Clicar para expandir/colapsar → Funciona
3. Marcar "aberto por padrão" → Item começa aberto

---

## FASE 2 — Style Tab (Alta Prioridade)

### Passo 9: Criar abas de estilo no painel direito ✅ IMPLEMENTADO
**Arquivo:** `resources/views/page-builder/editor.blade.php` + `public/js/page-builder-editor.js`

Mudar o painel direito para ter 3 abas como o Elementor:
- **Content** (conteúdo) — o que já existe
- **Style** (estilo) — fundo, borda, tipografia
- **Advanced** (avançado) — margem, padding, animação, custom CSS

**Implementação:**
1. Adicionar tabs no HTML do painel direito
2. `loadControls()` deve renderizar os 3 tipos de aba
3. Cada aba filtra os controls por `tab` (content/style/advanced)

**Como testar:**
1. Selecionar widget → Ver 3 abas no painel
2. Clicar em "Style" → Ver controles de estilo
3. Clicar em "Advanced" → Ver margem/padding

---

### Passo 10: Controles de Style — Fundo ✅ IMPLEMENTADO
**Controles a adicionar em todos os widgets:**

```php
'background_color' => ['type' => 'color', 'tab' => 'style'],
'background_image' => ['type' => 'image', 'tab' => 'style'],
'background_size' => ['type' => 'select', 'options' => ['cover','contain','auto'], 'tab' => 'style'],
'background_position' => ['type' => 'text', 'tab' => 'style'],
'opacity' => ['type' => 'number', 'min' => 0, 'max' => 100, 'tab' => 'style'],
```

**render()** em BaseWidget precisa aplicar esses estilos inline.

**Como testar:**
1. Selecionar heading → Style → Background Color
2. Mudar cor → Ver fundo mudar no canvas
3. Adicionar imagem de fundo → Ver background-image

---

### Passo 11: Controles de Style — Borda e Sombra ✅ IMPLEMENTADO
```php
'border_width' => ['type' => 'number', 'tab' => 'style'],
'border_color' => ['type' => 'color', 'tab' => 'style'],
'border_radius' => ['type' => 'number', 'tab' => 'style'],
'box_shadow' => ['type' => 'shadow', 'tab' => 'style'],
```

- `shadow` control: offsetX, offsetY, blur, spread, color

**Como testar:**
1. Selecionar button → Style → Border
2. Adicionar borda 2px preta → Ver borda
3. Adicionar sombra → Ver sombra

---

### Passo 12: Controles de Style — Tipografia Global ✅ IMPLEMENTADO
```php
'typography' => ['type' => 'typography', 'tab' => 'style'],
```

- `typography` control: font_family, font_size, font_weight, line_height, letter_spacing
- Incluir Google Fonts selector
- Aplicar em heading, text, button, callout

**Como testar:**
1. Selecionar heading → Style → Typography
2. Mudar font-size → Texto muda
3. Mudar font-weight → Ficar bold
4. Mudar letter-spacing → Espaçamento muda

---

### Passo 13: Estilos de hover ✅ IMPLEMENTADO
Adicionar controles de hover em buttons e links:
```php
'hover_background' => ['type' => 'color', 'tab' => 'style'],
'hover_color' => ['type' => 'color', 'tab' => 'style'],
'hover_border_color' => ['type' => 'color', 'tab' => 'style'],
```

No render: Adicionar `:hover` inline via `<style>` tag ou usar CSS customizado.

**Como testar:**
1. Selecionar button → Style → Hover
2. Definir cor de hover → No preview, hover muda cor

---

## FASE 3 — Navigator (Alta Prioridade)

### Passo 14: Painel Navigator ✅ IMPLEMENTADO
**Arquivo:** `public/js/page-builder-editor.js` + `resources/views/page-builder/editor.blade.php`

O Elementor tem um painel flutuante que mostra a árvore de elementos.

**Implementação:**
1. Criar painel flutuante com `<div class="pb-navigator">`
2. Posicionar no canto inferior direito (ou toggle)
3. Listar todos os elementos recursivamente
4. Highlight no hover
5. Click seleciona o elemento
6. Toggle show/hide com botão

**Como testar:**
1. Criar section com 2 colunas e widgets
2. Abrir Navigator → Ver árvore
3. Clicar em elemento → Seleciona no canvas
4. Hover → Destaca no canvas

---

### Passo 15: Drag & Drop no Navigator ✅ IMPLEMENTADO
- Permitir reordenar elementos arrastando no Navigator
- Mover elementos entre containers (section → column)
- Atualizar canvas e salvar via API

**Como testar:**
1. No Navigator, arrastar heading para cima de outro → Ordem muda
2. Arrastar widget de column A para column B → Move

---

### Passo 16: Rename elements no Navigator ✅ IMPLEMENTADO
- Duplo clique no label do Navigator → Editar nome
- Nome customizado salva no settings como `_title`
- Visível apenas no Navigator e no tooltip do canvas

**Como testar:**
1. Selecionar widget → Duplo clique no Navigator
2. Digitar novo nome → Salva
3. Mouse hover no canvas → Tooltip mostra nome customizado

---

### Passo 17: Right-click context menu no Navigator ✅ IMPLEMENTADO
- Duplicate, Delete, Copy, Paste, Move Up/Down
- Atalhos de teclado para ações rápidas

**Como testar:**
1. Clicar botão direito no Navigator → Menu aparece
2. Duplicate → Widget é duplicado
3. Delete → Widget é removido

---

## FASE 4 — Controles Avançados (Média Prioridade)

### Passo 18: Controles de Margem e Padding ✅ IMPLEMENTADO
**Arquivo:** `public/js/page-builder-editor.js`

Controles visuais como o Elementor:
- 4 valores: top, right, bottom, left
- Link/unlink valores (lock para manter todos iguais)
- Input numérico + slider

```php
'margin' => ['type' => 'dimensions', 'tab' => 'advanced'],
'padding' => ['type' => 'dimensions', 'tab' => 'advanced'],
```

**Como testar:**
1. Selecionar section → Advanced → Padding
2. Aumentar padding → Espaço interno muda
3. Clicar link → Todos os lados mudam juntos

---

### Passo 19: Controle de Z-Index ✅ IMPLEMENTADO
```php
'z_index' => ['type' => 'number', 'tab' => 'advanced'],
```

**Como testar:**
1. Criar 2 widgets sobrepostos
2. Aumentar z-index de um → Ele fica na frente

---

### Passo 20: CSS ID e CSS Classes ✅ IMPLEMENTADO
```php
'css_id' => ['type' => 'text', 'tab' => 'advanced'],
'css_classes' => ['type' => 'text', 'tab' => 'advanced'],
```

**Como testar:**
1. Adicionar CSS ID "meu-botao" → No render, `<div id="meu-botao">`
2. Adicionar classe "highlight" → `<div class="highlight">`

---

### Passo 21: Custom CSS por widget ✅ IMPLEMENTADO
```php
'custom_css' => ['type' => 'custom_css', 'tab' => 'advanced'],
```

- `code_editor` control: textarea com monospace font
- Gerar `<style>` tag no render com `.pb-{id} { ... }`

**Como testar:**
1. Adicionar CSS: `color: red !important;`
2. Widget fica vermelho
3. Usar seletor `.elementor-widget-container` → Funciona

---

### Passo 22: Animações de entrada ✅ IMPLEMENTADO
**Arquivo:** `public/js/page-builder-editor.js` + `app/Services/PageBuilder/Core/Renderer.php`

```php
'animation' => ['type' => 'select', 'tab' => 'advanced', 
    'options' => ['none','fadeIn','slideInUp','slideInLeft','zoomIn','bounceIn']
],
'animation_duration' => ['type' => 'select', 'options' => ['slow','normal','fast']],
'animation_delay' => ['type' => 'number', 'min' => 0, 'max' => 5000, 'step' => 100],
```

- Carregar Animate.css via CDN
- No render: Adicionar classes CSS de animação

**Como testar:**
1. Selecionar heading → Advanced → Animation → fadeIn
2. Scroll até ele → Animação roda
3. Delay de 500ms → Espera meio segundo

---

### Passo 23: Responsividade por widget (visível/oculto) ✅ IMPLEMENTADO
```php
'visibility' => ['type' => 'visibility', 'tab' => 'advanced'],
```

- No render: Adicionar `display: none` com media queries

**Como testar:**
1. Selecionar widget → Advanced → Desktop: on, Tablet: on, Mobile: off
2. Preview em mobile → Widget não aparece
3. Preview em desktop → Widget aparece

---

## FASE 5 — UX do Editor (Média Prioridade)

### Passo 24: Preview em tempo real (live update) ✅ IMPLEMENTADO
**Arquivo:** `public/js/page-builder-editor.js`

Atualmente o canvas só atualiza ao perder foco do input. Mudar para atualizar enquanto digita:
- `oninput` em vez de `onchange` em todos os controles
- Debounce de 300ms para performance

**Como testar:**
1. Selecionar heading
2. Digitar no campo de título → Texto muda em tempo real
3. Ajustar cor com color picker → Cor muda instantaneamente

---

### Passo 25: Ctrl+Z Visual (botão de desfazer) ✅ IMPLEMENTADO
**Arquivo:** `resources/views/page-builder/editor.blade.php`

Já existe `undo()` e `redo()` no JS, mas precisa de botões visíveis:
- Botão ↩ (undo) e ↪ (redo) na toolbar
- Desabilitar quando não há histórico

**Como testar:**
1. Adicionar widget → Botão undo fica ativo
2. Clicar undo → Widget removido
3. Clicar redo → Widget volta

---

### Passo 26: Right-click context menu no canvas ✅ IMPLEMENTADO
- Botão direito em qualquer widget → Menu flutuante
- Opções: Edit, Duplicate, Copy, Paste, Delete, Move Up/Down, Navigator
- Implementar com `<div>` posicionado com `position: fixed`

**Como testar:**
1. Clicar botão direito em heading → Menu aparece
2. Duplicate → Heading duplicado
3. Delete → Heading removido

---

### Passo 27: Drag handle melhorado ✅ IMPLEMENTADO
- Barra de arrastar mais visível (hover no widget)
- Cursor `grab` em vez de `pointer`
- Tooltip "Drag to reorder"
- Indicador visual de onde vai cair (linha azul)

**Como testar:**
1. Hover em widget → Barra de arrastar aparece
2. Arrastar → Linha indicadora mostra posição
3. Soltar → Widget reposicionado

---

### Passo 28: Zoom do canvas ✅ IMPLEMENTADO
- Botão +/- na toolbar ou Ctrl+Scroll
- Indicador de zoom atual (100%, 75%, 50%)
- Reset com botão ou Ctrl+0

**Como testar:**
1. Clicar zoom out → Canvas diminui
2. Clicar zoom in → Canvas aumenta
3. Ctrl+0 → Volta para 100%

---

### Passo 29: Fullscreen mode ✅ IMPLEMENTADO
- Botão para expandir canvas (esconder painéis laterais)
- ESC para sair
- Botão no canto superior direito

**Como testar:**
1. Clicar fullscreen → Painéis somem, canvas ocupa tela toda
2. ESC → Painéis voltam

---

### Passo 30: Widget search/filtro
- Campo de busca no painel esquerdo
- Filtrar widgets por nome/descrição
- Realce no match

**Como testar:**
1. Digitar "video" no search → Apenas widget de vídeo aparece
2. Limpar → Todos voltam

---

### Passo 31: Indicador de dirty state (modificações não salvas)
- Mudar título da aba para "• Editando: Título" quando há mudanças não salvas
- Alert ao sair da página com mudanças pendentes

**Como testar:**
1. Editar algo → Título da aba ganha "•"
2. Tentar fechar aba → Browser alerta

---

## FASE 6 — Atalhos e Duplicação (Média Prioridade)

### Passo 32: Atalhos de teclado expandidos
**Arquivo:** `public/js/page-builder-editor.js` em `bindKeyboard()`

Adicionar:
- `Ctrl+D` → Duplicar selecionado
- `Ctrl+C` → Copiar
- `Ctrl+V` → Colar
- `Ctrl+Shift+V` → Colar mantendo estilo
- `Ctrl+A` → Selecionar todos
- `Arrow Up/Down` → Navegar entre widgets
- `Ctrl+Arrow` → Mover widget

**Como testar:**
1. Selecionar widget → Ctrl+D → Duplicado
2. Ctrl+C → Copiado
3. Ctrl+V → Colado

---

### Passo 33: Copy/Paste de widgets
- Ctrl+C salva o widget no clipboard (localStorage)
- Ctrl+V cola como novo widget abaixo do selecionado
- Clipboard persiste entre sessões

**Como testar:**
1. Selecionar heading estilizado → Ctrl+C
2. Criar nova section → Ctrl+V
3. Heading aparece com mesmos estilos

---

### Passo 34: Global clipboard no editor
- Botão "Copy" / "Paste" na toolbar
- Indicador visual de "Widget copied" no toast

**Como testar:**
1. Selecionar → Clicar "Copy" na toolbar
2. Toast "Widget copied!"
3. Em outro lugar → Clicar "Paste"
4. Widget aparece

---

### Passo 35: Multi-select
- Ctrl+Click para selecionar múltiplos widgets
- Arrastar área de seleção no canvas
- Delete/Copy dos selecionados

**Como testar:**
1. Ctrl+Click em 2 widgets → Ambos selecionados
2. Delete → Ambos removidos
3. Ctrl+D → Ambos duplicados

---

## FASE 7 — Temas e Presets (Baixa Prioridade)

### Passo 36: Starter Templates
**Arquivo:** `app/Services/PageBuilder/Core/TemplateManager.php`

Adicionar templates pronto como o Elementor:
- Landing Page
- About Us
- Contact
- Portfolio
- Blog Post

Cada template é um JSON com elementos pré-configurados.

**Como testar:**
1. Criar página → Escolher "Landing Page" template
2. Página é populada com seções pré-criadas
3. Editar textos/imagens

---

### Passo 37: Color Presets
- Paleta de cores predefinida no Style tab
- 10+ paletas (Ocean, Forest, Sunset, etc.)
- Click aplica todas as cores de uma vez

**Como testar:**
1. Selecionar section → Style → Color Preset
2. Escolher "Ocean" → Cores mudam
3. Escolher "Sunset" → Cores mudam

---

### Passo 38: Typography Presets
- Combinações de fonte pré-definidas
- "Heading: Montserrat Bold, Body: Open Sans"
- One-click apply

**Como testar:**
1. Selecionar heading → Typography Preset
2. Escolher "Classic" → Fontes mudam

---

## FASE 8 — Responsividade (Baixa Prioridade)

### Passo 39: Breakpoints visuais no editor
**Arquivo:** `resources/views/page-builder/editor.blade.php`

- Botões desktop/tablet/mobile na toolbar (já parcialmente existe)
- Mudar largura do canvas dinamicamente
- Indicador visual do dispositivo atual

**Como testar:**
1. Clicar "Tablet" → Canvas reduz para 768px
2. Clicar "Mobile" → Canvas reduz para 375px
3. Clicar "Desktop" → Volta ao tamanho total

---

### Passo 40: Estilos responsivos por widget
- Cada control de estilo deve ter ícone de dispositivo
- Permitir definir valor diferente para mobile/tablet
- Aplicar via media query

**Como testar:**
1. Selecionar heading → Style → Typography
2. Ícone de desktop/tablet/mobile
3. Em mobile: font-size 16px, desktop: 32px
4. Preview mobile → Texto menor

---

### Passo 41: Preview devices
- Botão "Preview" com seletor de dispositivo
- Abrir iframe com largura simulada
- Mostrar como ficaria em cada dispositivo

**Como testar:**
1. Clicar Preview → Menu de dispositivos
2. Selecionar iPhone → Modal com preview 375px
3. Selecionar iPad → Modal com preview 768px

---

### Passo 42: Mobile editing mode
- Modo de edição mobile: esconder widgets desktop
- Editar apenas widgets visíveis no mobile
- Indicador "Visível apenas em desktop"

**Como testar:**
1. Ativar modo mobile
2. Widget com visibility mobile off → aparece cinza/tracejado
3. Widget visível → Editar normalmente

---

## Ordem Recomendada de Implementação

| Semana | Passos | Motivo |
|--------|--------|--------|
| 1 | 1-4 | Widgets básicos (vídeo, divisor, spacer, ícone) |
| 2 | 5-8 | Widgets avançados (galeria, form, tabs, accordion) |
| 3 | 9-13 | Style Tab completo |
| 4 | 14-17 | Navigator |
| 5 | 18-23 | Controles avançados |
| 6 | 24-31 | UX do editor |
| 7 | 32-35 | Atalhos e copy/paste |
| 8 | 36-42 | Temas, responsividade, polish |

---

## Checklist de Verificação

Para cada passo, verificar:
- [ ] Widget/control funciona no editor
- [ ] Renderização HTML está correta
- [ ] CSS inline preservado (compatibilidade Moodle)
- [ ] Funciona em todos os modos (desktop, tablet, mobile)
- [ ] Undo/Redo funciona com a nova feature
- [ ] Auto-save preserva dados
- [ ] Não quebra testes existentes (`php artisan test`)

---

## Notas Técnicas

### Para adicionar um novo widget:
1. Criar arquivo em `app/Services/PageBuilder/Widgets/`
2. Estender `BaseWidget`
3. Definir `$type`, `$label`, `$icon`, `$categories`, `$controls`, `$defaultSettings`
4. Implementar `render()` e `prepareSettings()`
5. Registrar em `config/page-builder.php` → `widgets` array
6. Adicionar card no painel esquerdo do editor (HTML do `editor.blade.php`)
7. Testar com `php artisan test`

### Para adicionar um novo control type:
1. Adicionar factory no objeto `controlRenderers` em `page-builder-editor.js`
2. Adicionar CSS se necessário em `editor.blade.php`
3. Implementar `onchange` callback para `updateSetting()`

### Convenções:
- Todo CSS gerado deve ser **inline** (compatibilidade Moodle)
- Todo JS deve ter `.catch()` + toast de erro
- Usar `escHtml()` para sanitizar conteúdo renderizado
- Defaults em `prepareSettings()` de cada widget
