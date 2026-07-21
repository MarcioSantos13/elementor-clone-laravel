@extends('page-builder.layouts.app')

@section('title', 'Tutorial')

@section('content')
    <div class="container tutorial">
        <div class="page-header">
            <h1>&#128214; Tutorial — Page Builder</h1>
            <a href="{{ route('page-builder.pages.index') }}" class="btn btn-secondary">&larr; Voltar para Páginas</a>
        </div>

        <div class="toc">
            <strong>Conteúdo</strong>
            <a href="#prerequisites">Pré-requisitos &amp; Instalação</a>
            <a href="#overview">Visão Geral</a>
            <a href="#license">Custo &amp; Licença</a>
            <a href="#auth">Criar Conta &amp; Acessar</a>
            <a href="#create-page">Criar uma Página</a>
            <a href="#editor">O Editor</a>
            <a href="#drag-widgets">Adicionar Widgets</a>
            <a href="#educational-widgets">Widgets Educacionais</a>
            <a href="#select-settings">Editar Configurações</a>
            <a href="#page-settings">Configurações da Página</a>
            <a href="#responsive">Visualização Responsiva</a>
            <a href="#templates">Usar Templates</a>
            <a href="#undo-redo">Desfazer &amp; Refazer</a>
            <a href="#save-publish">Salvar &amp; Publicar</a>
            <a href="#preview">Visualizar Página</a>
            <a href="#duplicate-delete">Duplicar &amp; Excluir</a>
            <a href="#showcase">Template Showcase Completo</a>
            <a href="#architecture">Arquitetura do Projeto</a>
            <a href="#database">Banco de Dados</a>
            <a href="#routes">Rotas</a>
            <a href="#quality">Qualidade &amp; Testes</a>
            <a href="#improvements">Melhorias Propostas</a>
            <a href="#moodle">Uso com Moodle 4.5+</a>
        </div>

        {{-- PREREQUISITES --}}
        <section id="prerequisites" class="step">
            <h2>0. Pré-requisitos &amp; Instalação</h2>
            <div class="step-body">
                <p>Antes de usar o Page Builder, você precisa ter o projeto rodando na sua máquina. Siga os passos abaixo:</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">O que você precisa ter instalado</h3>
                <table class="widget-table">
                    <tr><th>Ferramenta</th><th>Versão Mínima</th><th>Como verificar</th></tr>
                    <tr><td><strong>PHP</strong></td><td>8.2+</td><td><code>php -v</code></td></tr>
                    <tr><td><strong>Composer</strong></td><td>2.0+</td><td><code>composer -V</code></td></tr>
                    <tr><td><strong>Node.js</strong></td><td>18+</td><td><code>node -v</code></td></tr>
                    <tr><td><strong>NPM</strong></td><td>9+</td><td><code>npm -v</code></td></tr>
                    <tr><td><strong>Banco de dados</strong></td><td>SQLite ou MySQL/MariaDB</td><td>SQLite é o mais fácil para começar</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 1: Clonar o repositório</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>git clone https://github.com/seu-usuario/elementor-clone-laravel.git
cd elementor-clone-laravel</code></pre>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 2: Instalar dependências</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>composer install
npm install</code></pre>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 3: Configurar o ambiente</h3>
                <ol>
                    <li>Copie o arquivo de configuração:
                        <pre style="background:#1e1e2d;color:#a6e3a1;padding:.5rem 1rem;border-radius:6px;font-size:.85rem;margin:.5rem 0"><code>copy .env.example .env</code></pre>
                    </li>
                    <li>Gere a chave da aplicação:
                        <pre style="background:#1e1e2d;color:#a6e3a1;padding:.5rem 1rem;border-radius:6px;font-size:.85rem;margin:.5rem 0"><code>php artisan key:generate</code></pre>
                    </li>
                    <li>Configure o banco de dados no arquivo <code>.env</code>. Para SQLite (mais simples):
                        <pre style="background:#1e1e2d;color:#a6e3a1;padding:.5rem 1rem;border-radius:6px;font-size:.85rem;margin:.5rem 0"><code>DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite</code></pre>
                        Depois crie o arquivo: <code>type nul &gt; database\database.sqlite</code> (Windows) ou <code>touch database/database.sqlite</code> (Mac/Linux)
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 4: Rodar migrações e seeders</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>php artisan migrate
php artisan db:seed</code></pre>
                <p>O seeder cria automaticamente:</p>
                <ul>
                    <li>Um usuário de teste: <code>test@example.com</code> / <code>password</code></li>
                    <li>As definições de widgets disponíveis</li>
                    <li>Os 5 templates padrão (Blank, Landing, About, Contact, <strong>Showcase Completo</strong>)</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Passo 5: Iniciar o servidor</h3>
                <pre style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.85rem;overflow-x:auto"><code>php artisan serve</code></pre>
                <p>O Page Builder estará disponível em <strong><a href="http://localhost:8000" target="_blank">http://localhost:8000</a></strong>.</p>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> Se encontrar algum erro durante a instalação, verifique se todas as extensões PHP necessárias estão habilitadas (pdo_sqlite, mbstring, xml, curl, bcmath). O comando <code>php artisan about</code> mostra informações sobre o ambiente.
                </div>
            </div>
        </section>

        {{-- OVERVIEW --}}
        <section id="overview" class="step">
            <h2>1. Visão Geral</h2>

            <div class="step-body">
                <p>O <strong>Laravel Page Builder</strong> é um construtor de páginas visual drag-and-drop inspirado no Elementor. Permite criar páginas web completas arrastando widgets para uma tela, editando conteúdo e estilo em tempo real, sem escrever código.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Stack Tecnológica</h3>
                <table class="widget-table">
                    <tr><th>Camada</th><th>Tecnologia</th><th>Versão</th></tr>
                    <tr><td>Backend</td><td>Laravel</td><td>12.x</td></tr>
                    <tr><td>Frontend</td><td>JavaScript vanilla (sem frameworks)</td><td>ES2022+</td></tr>
                    <tr><td>Banco de Dados</td><td>SQLite (padrão) / MySQL / MariaDB</td><td>&#8212;</td></tr>
                    <tr><td>PHP</td><td>LTS</td><td>8.2+</td></tr>
                    <tr><td>Build</td><td>Vite + Tailwind CSS</td><td>7.x / 4.x</td></tr>
                    <tr><td>Testes</td><td>PHPUnit</td><td>11.x</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Números do Projeto</h3>
                <table class="widget-table">
                    <tr><th>Métrica</th><th>Valor</th></tr>
                    <tr><td>Widgets disponíveis</td><td>17 (Heading, Text, Image, Button, Section, Column, Callout, Table, Math, Video, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion)</td></tr>
                    <tr><td>Templates prontos</td><td>5 (Blank, Landing, About, Contact, Showcase Completo)</td></tr>
                    <tr><td>Rotas definidas</td><td>35+ (CRUD páginas, elementos, revisões, templates)</td></tr>
                    <tr><td>Testes automatizados</td><td>93 (45 unitários + 48 de feature)</td></tr>
                    <tr><td>Tabelas no banco</td><td>3 principais (pages, elements, revisions)</td></tr>
                    <tr><td>Views Blade</td><td>16 (login, register, tutorial, editor + 7 partials, pages)</td></tr>
                    <tr><td>Linhas de JS do editor</td><td>~2600 (6 módulos ES em resources/js/editor/)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Funcionalidades</h3>
                <ul>
                    <li>Criar páginas com título e status (rascunho / publicado)</li>
                    <li>Abrir um <strong>editor visual</strong> em tela cheia com tema escuro</li>
                    <li>Arrastar <strong>17 widgets</strong> (Heading, Text, Image, Button, Section, Column, Callout, Table, Math, Video, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion)</li>
                    <li>Selecionar qualquer elemento e editar suas <strong>configurações</strong> no painel direito</li>
                    <li><strong>Editor de texto rich-text (WYSIWYG)</strong> com toolbar: negrito, itálico, links, imagens, vídeos YouTube, listas, código fonte</li>
                    <li><strong>Inserir imagens</strong> no texto via upload ou colar (Ctrl+V) — imagem inline no conteúdo</li>
                    <li><strong>Inserir vídeos YouTube</strong> — cole a URL e o embed responsivo 16:9 é inserido automaticamente</li>
                    <li>Inline editing — duplo-clique para editar texto diretamente no canvas (preserva HTML)</li>
                    <li>Alternar entre visualização <strong>desktop / tablet / mobile</strong></li>
                    <li>Aplicar <strong>5 templates</strong> prontos</li>
                    <li>Desfazer / refazer alterações (Ctrl+Z / Ctrl+Shift+Z)</li>
                    <li>Salvamento automático a cada 60 segundos, ou salvar / publicar manualmente</li>
                    <li>Sistema de <strong>revisões</strong> com diff e restauração</li>
                    <li>Duplicar, exportar (JSON), importar, copiar HTML, excluir páginas</li>
                    <li><strong>Navegador (Navigator)</strong> — painel flutuante com árvore de elementos, drag-and-drop, renomear, menu de contexto</li>
                    <li><strong>Right-click context menu</strong> no canvas e no Navigator (Editar, Duplicar, Copiar, Colar, Mover, Excluir)</li>
                    <li><strong>Drag handle</strong> com indicadores visuais de posição (drop-before/drop-after)</li>
                    <li><strong>Zoom do canvas</strong> com Ctrl+Scroll, botões +/-, reset com Ctrl+0 (25%-200%)</li>
                    <li><strong>Tela cheia (Fullscreen)</strong> — esconder painéis laterais com botão ou F11</li>
                    <li><strong>Preview em tempo real</strong> — debounce 300ms em todos os controles (text, color, number, etc.)</li>
                    <li><strong>Estilo por widget</strong> — 3 abas (Content, Style, Advanced): tipografia, fundo, borda, sombra, hover, dimensões, animação, CSS customizado, visibilidade responsiva</li>
                    <li>Integração com <strong>Moodle 4.5+</strong> via HTML renderizado</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Arquitetura Resumida</h3>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.8rem;line-height:1.6;overflow-x:auto;font-family:monospace">
<pre style="margin:0">┌──────────────────────────────────────────────────┐
│                  FRONTEND                         │
│   editor.blade.php (7 partials)                   │
│   resources/js/editor/ (6 módulos ES)              │
│   state, utils, canvas, history, navigator, dragdrop
├──────────────────────────────────────────────────┤
│                CONTROLLERS                        │
│   PageController │ ElementController              │
│   RevisionController │ FormController             │
├──────────────────────────────────────────────────┤
│                 SERVICES                          │
│   PageBuilderService │ Renderer │ WidgetManager   │
│   TemplateManager │ ElementManager                │
├──────────────────────────────────────────────────┤
│                  WIDGETS (17)                     │
│   BaseWidget → Heading │ Text │ Image │ Button    │
│              Section │ Column │ Callout            │
│              Table │ Math │ Video │ Divider        │
│              Spacer │ Icon │ Gallery │ Form        │
│              Tabs │ Accordion                      │
├──────────────────────────────────────────────────┤
│              DATABASE (SQLite)                    │
│   pages → elements (árvore) → revisions           │
└──────────────────────────────────────────────────┘</pre>
                </div>
            </div>
        </section>

        {{-- LICENSE --}}
        <section id="license" class="step">
            <h2>1b. Custo &amp; Licença</h2>
            <div class="step-body">
                <p>O <strong>Laravel Page Builder</strong> é um projeto <strong>gratuito e open-source</strong>, licenciado sob a <strong>MIT License</strong>. Isso significa:</p>

                <table class="widget-table">
                    <tr><th>Pergunta</th><th>Resposta</th></tr>
                    <tr><td><strong>É gratuito?</strong></td><td>Sim. Não há custos de licença, assinatura ou uso comercial.</td></tr>
                    <tr><td><strong>Pode usar em produção?</strong></td><td>Sim. Não há restrições de uso — pessoal, educacional ou comercial.</td></tr>
                    <tr><td><strong>Pode modificar o código?</strong></td><td>Sim. Você pode alterar, estender e personalizar livremente.</td></tr>
                    <tr><td><strong>Pode redistribuir?</strong></td><td>Sim. A MIT License permite distribuir cópias, incluindo para uso comercial.</td></tr>
                    <tr><td><strong>Precisa dar crédito?</strong></td><td>Recomendado (não obrigatório). Manter a licença MIT nos arquivos originais é boa prática.</td></tr>
                    <tr><td><strong>Tem suporte oficial?</strong></td><td>Não. É um projeto independente. Use as issues do GitHub para reportar bugs ou sugestões.</td></tr>
                    <tr><td><strong>Tem versão premium?</strong></td><td>Não. Todas as funcionalidades estão disponíveis na versão open-source.</td></tr>
                </table>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O código-fonte está disponível no repositório do GitHub. Você pode clonar, modificar e hospedar em seu próprio servidor sem nenhuma restrição. Não há dependências de serviços pagos ou APIs externas obrigatórias.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">O que está incluído (gratuito)</h3>
                <ul>
                    <li>17 widgets (Heading, Text, Image, Button, Section, Column, Callout, Table, Math, Video, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion)</li>
                    <li>Editor visual completo com drag-and-drop, undo/redo, zoom, fullscreen</li>
                    <li>6 templates prontos (Blank, Landing, About, Contact, Moodle Course, Showcase)</li>
                    <li>Sistema de revisões com diff e restauração</li>
                    <li>Exportação/Importação de páginas (JSON)</li>
                    <li>Copiar HTML para uso em outros sistemas (Moodle, WordPress, etc.)</li>
                    <li>Suporte a LaTeX via KaTeX</li>
                    <li>93 testes automatizados</li>
                    <li>Integração com Moodle 4.5+</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Stack tecnológica (tudo open-source)</h3>
                <table class="widget-table">
                    <tr><th>Camada</th><th>Tecnologia</th><th>Licença</th></tr>
                    <tr><td>Backend</td><td>Laravel 12</td><td>MIT</td></tr>
                    <tr><td>Frontend</td><td>JavaScript vanilla</td><td>—</td></tr>
                    <tr><td>Banco de Dados</td><td>SQLite</td><td>Public Domain</td></tr>
                    <tr><td>Build</td><td>Vite</td><td>MIT</td></tr>
                    <tr><td>CSS</td><td>Tailwind CSS</td><td>MIT</td></tr>
                    <tr><td>Ícones</td><td>Font Awesome</td><td>MIT (icons) + SIL OFL (fonts)</td></tr>
                    <tr><td>Fórmulas</td><td>KaTeX</td><td>MIT</td></tr>
                    <tr><td>Fontes</td><td>Google Fonts (Inter)</td><td>SIL OFL</td></tr>
                </table>
                <p style="margin-top:.75rem;font-size:.88rem;color:#666">Toda a stack é composta por tecnologias open-source gratuitas. Não há custos ocultos.</p>
            </div>
        </section>

        {{-- AUTH --}}
        <section id="auth" class="step">
            <h2>2. Criar Conta &amp; Acessar</h2>
            <div class="step-body">
                <p>Antes de usar o Page Builder, você precisa de uma conta de usuário.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Criar uma conta</h3>
                <ol>
                    <li>Acesse a página <strong><a href="{{ route('register') }}">Registro</a></strong> clicando em "Criar conta" na tela de login.</li>
                    <li>Preencha:
                        <ul>
                            <li><strong>Nome</strong> — seu nome de exibição</li>
                            <li><strong>Email</strong> — deve ser único no sistema</li>
                            <li><strong>Senha</strong> — mínimo de 6 caracteres</li>
                            <li><strong>Confirmar Senha</strong> — repita a senha</li>
                        </ul>
                    </li>
                    <li>Clique em <strong>"Criar Conta"</strong>. Você será autenticado automaticamente e redirecionado para a lista de páginas.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Entrar</h3>
                <ol>
                    <li>Acesse a página de <strong><a href="{{ route('login') }}">Login</a></strong>.</li>
                    <li>Informe seu <strong>Email</strong> e <strong>Senha</strong>.</li>
                    <li>Clique em <strong>"Entrar"</strong>.</li>
                    <li>Em caso de erro, uma mensagem será exibida acima do formulário.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Sair</h3>
                <p>Clique em <strong>"Sair"</strong> no menu do Page Builder para encerrar a sessão.</p>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O único usuário pré-cadastrado é <code>test@example.com</code> com senha <code>password</code> (criado pelo seeder). Use a página de registro para adicionar novos usuários.
                </div>
            </div>
        </section>

        {{-- CREATE PAGE --}}
        <section id="create-page" class="step">
            <h2>3. Criar uma Página</h2>
            <div class="step-body">
                <p>Na lista de páginas, clique em <strong>"Nova Página"</strong> na barra de navegação superior.</p>
                <div class="illustration">
                    <div class="ill-preview" style="max-width:400px">
                        <div style="background:#f8f9fa;padding:1rem;border-radius:6px;border:1px solid #ddd;text-align:center">
                            <div style="font-size:.8rem;color:#888;margin-bottom:.5rem">cabeçalho da lista de páginas</div>
                            <div style="display:flex;gap:.5rem;justify-content:center">
                                <span style="display:inline-block;padding:.3rem .8rem;background:#007bff;color:#fff;border-radius:4px;font-size:.85rem">+ Nova Página</span>
                            </div>
                        </div>
                    </div>
                </div>
                <ol>
                    <li>Insira um <strong>Título</strong> (ex.: "Minha Landing Page")</li>
                    <li>Escolha um <strong>Status</strong> — <em>Rascunho</em> mantém oculta, <em>Publicado</em> a torna visível</li>
                    <li>Opcionalmente, escolha um <strong>Template</strong> (veja <a href="#templates">passo 10</a>)</li>
                    <li>Clique em <strong>"Criar &amp; Abrir Editor"</strong> para ir direto ao editor visual</li>
                </ol>
            </div>
        </section>

        {{-- THE EDITOR --}}
        <section id="editor" class="step">
            <h2>4. O Editor</h2>
            <div class="step-body">
                <p>O editor é uma interface em tela cheia com tema escuro. Ele carrega ao acessar <code>/page-builder/pages/{id}/editor</code>. A URL do canvas é acessível publicamente.</p>
                <div class="panel-layout-ill">
                    <div class="panel-ill left"><strong>Widgets</strong><br><span style="font-size:.75rem">arraste itens para a tela</span></div>
                    <div class="panel-ill center"><strong>Canvas</strong><br><span style="font-size:.75rem">prévia ao vivo da sua página</span></div>
                    <div class="panel-ill right"><strong>Configurações</strong><br><span style="font-size:.75rem">opções do elemento selecionado</span></div>
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Painel Esquerdo — Widgets</h3>
                <ul>
                    <li>Lista os 17 widgets disponíveis: Heading, Text, Image, Button, Section, Column, Callout, Table, Math, Video, Divider, Spacer, Icon, Gallery, Form, Tabs, Accordion</li>
                    <li>Cada widget mostra um ícone (emoji) e nome</li>
                    <li>O painel começa colapsado — clique no ícone de widgets (☰) na barra superior para expandir</li>
                    <li>Clique em um widget para adicioná-lo à página (ou arraste para o canvas)</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Centro — Canvas</h3>
                <ul>
                    <li>Exibe a prévia ao vivo da página (iframe de largura 100%)</li>
                    <li>Clique em qualquer elemento para selecioná-lo — ele fica com borda azul</li>
                    <li>O elemento selecionado aparece destacado no painel direito</li>
                    <li>Duplo-clique em textos de Título e Texto para edição inline</li>
                    <li>Botão de remover (✕) aparece ao passar o mouse sobre elementos</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Painel Direito — Configurações</h3>
                <ul>
                    <li>Mostra as configurações do elemento selecionado, ou da página se nada estiver selecionado</li>
                    <li>Cada configuração tem um campo de entrada (texto, cor, seleção, etc.)</li>
                    <li>O widget de texto exibe um <strong>editor WYSIWYG</strong> com toolbar para formatação, imagens e vídeos</li>
                    <li>As mudanças são aplicadas instantaneamente ao canvas</li>
                    <li>O painel começa colapsado — clique no ícone de configurações (⚙️) na barra superior para expandir</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Barra Superior</h3>
                <table class="widget-table">
                    <tr><th>Grupo</th><th>Botões</th><th>Função</th></tr>
                    <tr><td>Layout</td><td>☰, ⚙️</td><td>Expandir/colapsar painéis esquerdo e direito</td></tr>
                    <tr><td>Responsivo</td><td>🖥️ 📱 📲</td><td>Desktop (100%), Tablet (768px), Mobile (360px)</td></tr>
                    <tr><td>Ações</td><td>Desfazer / Refazer</td><td>Ctrl+Z / Ctrl+Shift+Z (máximo 50 estados)</td></tr>
                    <tr><td>Salvar</td><td>💾 Salvar</td><td>Salva como rascunho (sem publicar)</td></tr>
                    <tr><td>Publicar</td><td>🚀 Publicar</td><td>Muda status para "publicado"</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Carregamento do Editor</h3>
                <ol>
                    <li>A página é carregada via GET para <code>/page-builder/pages/{id}/editor</code></li>
                    <li>O JS busca <code>GET /api/page-builder/pages/{id}/elements</code> para obter a árvore de elementos</li>
                    <li>Os elementos são renderizados recursivamente (Seção → Coluna → widgets)</li>
                    <li>O painel de configurações é carregado via <code>GET /api/page-builder/widgets/{type}/controls</code></li>
                    <li>Salvamento automático inicia a cada 60 segundos</li>
                </ol>
            </div>
        </section>

        {{-- ADDING WIDGETS --}}
        <section id="drag-widgets" class="step">
            <h2>5. Adicionar Widgets (Arrastar &amp; Soltar)</h2>
            <div class="step-body">
                <p>Cada widget adiciona um tipo diferente de conteúdo. Veja o que cada um faz e suas configurações disponíveis:</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Visão Geral dos Widgets</h3>
                <table class="widget-table">
                    <tr><th>Widget</th><th>O que cria</th><th>Total de controles</th></tr>
                    <tr><td><strong>Título (Heading)</strong></td><td>Um título grande (&lt;h1&gt;–&lt;h6&gt;) com tag, texto, alinhamento e cor configuráveis</td><td>8</td></tr>
                    <tr><td><strong>Texto</strong></td><td>Um parágrafo ou bloco de texto com conteúdo rich-text (WYSIWYG), imagens, vídeos e listas</td><td>7</td></tr>
                    <tr><td><strong>Imagem</strong></td><td>Uma imagem com URL, texto alternativo e largura configuráveis</td><td>7</td></tr>
                    <tr><td><strong>Botão</strong></td><td>Um botão clicável com texto, URL, alinhamento e cor configuráveis</td><td>8</td></tr>
                    <tr><td><strong>Seção</strong></td><td>Um contêiner estrutural (linha de largura total). Você coloca Colunas ou outros widgets dentro dela</td><td>16</td></tr>
                    <tr><td><strong>Coluna</strong></td><td>Uma coluna vertical dentro de uma Seção. Controla largura, alinhamento, padding, fundo</td><td>10</td></tr>
                    <tr><td><strong>Callout</strong></td><td>Caixa de destaque educacional com 9 tipos (info, success, warning, danger, tip, definition, theorem, exercise, note)</td><td>7</td></tr>
                    <tr><td><strong>Table</strong></td><td>Tabela HTML estilizada com cabeçalho, linhas alternadas e bordas configuráveis</td><td>9</td></tr>
                    <tr><td><strong>Math</strong></td><td>Fórmula matemática LaTeX renderizada via KaTeX</td><td>8</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Título (Heading)</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Title</td><td>text (obrigatório)</td><td>Texto do título (máx. 500 caracteres)</td></tr>
                    <tr><td>HTML Tag</td><td>select</td><td>h1, h2, h3, h4, h5, h6, p, div</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right, justify</td></tr>
                    <tr><td>Size</td><td>select</td><td>small, default, medium, large, xl, xxl</td></tr>
                    <tr><td>Color</td><td>color</td><td>Qualquer cor (hex)</td></tr>
                    <tr><td>Font Family</td><td>text</td><td>Nome da fonte (ex: Arial, Georgia)</td></tr>
                    <tr><td>Font Weight</td><td>select</td><td>300, 400, 500, 600, 700, 800, 900</td></tr>
                    <tr><td>Link</td><td>url</td><td>URL para onde o título链接 (opcional)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Texto</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Content</td><td>wysiwyg (obrigatório)</td><td>Editor rich-text com toolbar (negrito, itálico, links, imagens, vídeos YouTube, listas, código fonte)</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right, justify</td></tr>
                    <tr><td>Color</td><td>color</td><td>Qualquer cor (hex)</td></tr>
                    <tr><td>Font Size</td><td>number</td><td>8–200px</td></tr>
                    <tr><td>Font Family</td><td>text</td><td>Nome da fonte</td></tr>
                    <tr><td>Line Height</td><td>number</td><td>0.5–5.0</td></tr>
                    <tr><td>Drop Cap</td><td>boolean</td><td>Sim/Não (primeira letra em destaque)</td></tr>
                </table>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Toolbar do Editor de Texto</h4>
                <p>Ao selecionar um widget de texto, o painel direito exibe um editor rich-text (WYSIWYG) com as seguintes ferramentas:</p>
                <table class="widget-table">
                    <tr><th>Botão</th><th>Função</th><th>Como usar</th></tr>
                    <tr><td><strong>B</strong></td><td>Negrito</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>I</strong></td><td>Itálico</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>U</strong></td><td>Sublinhado</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>S</strong></td><td>Tachado</td><td>Selecione o texto e clique</td></tr>
                    <tr><td><strong>&#9650;</strong></td><td>Título H2</td><td>Converte o parágrafo em título</td></tr>
                    <tr><td><strong>&#182;</strong></td><td>Parágrafo</td><td>Converte de volta para parágrafo</td></tr>
                    <tr><td><strong>&#128279;</strong></td><td>Inserir link</td><td>Pede a URL e cria um hyperlink</td></tr>
                    <tr><td><strong>&#128247;</strong></td><td>Inserir imagem</td><td>Abre seletor de arquivo, faz upload e insere inline</td></tr>
                    <tr><td><strong>&#9654;</strong></td><td>Vídeo YouTube</td><td>Pede a URL do YouTube e insere embed responsivo 16:9</td></tr>
                    <tr><td><strong>&#8226;</strong></td><td>Lista</td><td>Cria lista com marcadores</td></tr>
                    <tr><td><strong>1.</strong></td><td>Lista Numerada</td><td>Cria lista numerada</td></tr>
                    <tr><td><strong>&lt;/&gt;</strong></td><td>Código fonte</td><td>Alterna entre visual e edição HTML raw</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Você também pode <strong>colar imagens</strong> diretamente no editor (Ctrl+V) — elas são enviadas automaticamente e inseridas no texto. Para vídeos, clique no botão <strong>&#9654;</strong> e cole a URL do YouTube.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Imagem</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Image</td><td>image</td><td>URL da imagem</td></tr>
                    <tr><td>Caption</td><td>text</td><td>Legenda (máx. 300 caracteres)</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Width</td><td>text</td><td>Largura (ex: 100%, 300px)</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (ex: 8px, 50%)</td></tr>
                    <tr><td>Link</td><td>url</td><td>URL de destino ao clicar</td></tr>
                    <tr><td>Enable Lightbox</td><td>boolean</td><td>Sim/Não (abrir em lightbox)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Botão</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Button Text</td><td>text (obrigatório)</td><td>Texto do botão (máx. 100 caracteres)</td></tr>
                    <tr><td>Link</td><td>url</td><td>URL de destino</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right, stretch</td></tr>
                    <tr><td>Size</td><td>select</td><td>small, medium, large, xl</td></tr>
                    <tr><td>Background Color</td><td>color</td><td>Cor de fundo do botão</td></tr>
                    <tr><td>Text Color</td><td>color</td><td>Cor do texto</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (ex: 6px)</td></tr>
                    <tr><td>Font Size</td><td>number</td><td>10–100px</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Seção</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Layout</td><td>select</td><td>boxed, full_width, full_height</td></tr>
                    <tr><td>Content Width</td><td>text</td><td>Largura do conteúdo (ex: 1140px)</td></tr>
                    <tr><td>Min Height</td><td>text</td><td>Altura mínima (ex: 400px)</td></tr>
                    <tr><td>Align Items</td><td>select</td><td>stretch, flex-start, center, flex-end</td></tr>
                    <tr><td>Justify Content</td><td>select</td><td>flex-start, center, flex-end, space-between, space-around, space-evenly</td></tr>
                    <tr><td>Background Type</td><td>select</td><td>none, classic, gradient, video</td></tr>
                    <tr><td>Background Color</td><td>color</td><td>Cor de fundo</td></tr>
                    <tr><td>Padding Top</td><td>text</td><td>Espaçamento superior (ex: 80px)</td></tr>
                    <tr><td>Padding Bottom</td><td>text</td><td>Espaçamento inferior</td></tr>
                    <tr><td>Padding Left</td><td>text</td><td>Espaçamento esquerdo</td></tr>
                    <tr><td>Padding Right</td><td>text</td><td>Espaçamento direito</td></tr>
                    <tr><td>Margin Top</td><td>text</td><td>Margem superior</td></tr>
                    <tr><td>Margin Bottom</td><td>text</td><td>Margem inferior</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda</td></tr>
                    <tr><td>Box Shadow</td><td>text</td><td>Sombra (ex: 0 4px 6px rgba(0,0,0,0.1))</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Coluna</h3>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Column Width</td><td>select</td><td>col-1 até col-12 (grid de 12 colunas, padrão: col-4)</td></tr>
                    <tr><td>Vertical Alignment</td><td>select</td><td>stretch, flex-start, center, flex-end</td></tr>
                    <tr><td>Text Align</td><td>select</td><td>left, center, right, justify</td></tr>
                    <tr><td>Background Color</td><td>color</td><td>Cor de fundo</td></tr>
                    <tr><td>Padding Top</td><td>text</td><td>Espaçamento superior</td></tr>
                    <tr><td>Padding Bottom</td><td>text</td><td>Espaçamento inferior</td></tr>
                    <tr><td>Padding Left</td><td>text</td><td>Espaçamento esquerdo</td></tr>
                    <tr><td>Padding Right</td><td>text</td><td>Espaçamento direito</td></tr>
                    <tr><td>Margin</td><td>text</td><td>Margem externa</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda</td></tr>
                </table>

                <div class="tip">
                    <strong>&#128161; Dica — Unidades CSS:</strong> Nos campos de texto (Min Height, Padding, Margin, etc.), você pode usar diferentes unidades CSS:
                    <table class="widget-table" style="margin:.75rem 0">
                        <tr><th>Unidade</th><th>Exemplo</th><th>Significado</th></tr>
                        <tr><td><strong>vh</strong></td><td><code>90vh</code></td><td>Viewport Height — 90% da altura da tela do navegador</td></tr>
                        <tr><td><strong>vw</strong></td><td><code>100vw</code></td><td>Viewport Width — 100% da largura da tela</td></tr>
                        <tr><td><strong>px</strong></td><td><code>400px</code></td><td>Pixels — medida fixa</td></tr>
                        <tr><td><strong>%</strong></td><td><code>50%</code></td><td>Percentual — relativo ao elemento pai</td></tr>
                        <tr><td><strong>rem</strong></td><td><code>2rem</code></td><td>Relativo ao tamanho da fonte raiz (geralmente 16px)</td></tr>
                        <tr><td><strong>em</strong></td><td><code>1.5em</code></td><td>Relativo ao tamanho da fonte do elemento pai</td></tr>
                    </table>
                    Para uma <strong>seção hero</strong> que ocupa quase toda a tela, use <code>90vh</code> ou <code>100vh</code> no campo <em>Min Height</em>.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Como Adicionar</h3>
                <ol>
                    <li>No <strong>painel esquerdo</strong>, encontre o widget desejado</li>
                    <li><strong>Arraste</strong> (clique e segure) e <strong>solte</strong> na tela</li>
                    <li>O widget aparece instantaneamente no canvas</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Comece arrastando uma <strong>Seção</strong> para a tela, depois arraste uma <strong>Coluna</strong> para dentro da seção, e então arraste widgets de conteúdo (Título, Texto, etc.) para dentro da coluna. A estrutura é: Seção → Coluna → Widget.
                </div>
            </div>
        </section>

        {{-- EDUCATIONAL WIDGETS --}}
        <section id="educational-widgets" class="step">
            <h2>5b. Widgets Educacionais</h2>
            <div class="step-body">
                <p>Além dos widgets básicos, o Page Builder inclui widgets especiais para conteúdo educacional, disponíveis no grupo <strong>"Educacional"</strong> no painel esquerdo:</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Callout (Caixa de Destaque)</h3>
                <p>Caixas coloridas para destacar informações importantes no conteúdo. Perfeitas para definições, teoremas, exercícios, avisos e dicas.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Type</td><td>select</td><td><strong>info</strong> (azul), <strong>success</strong> (verde), <strong>warning</strong> (amarelo), <strong>danger</strong> (vermelho), <strong>tip</strong> (ciano), <strong>definition</strong> (roxo), <strong>theorem</strong> (laranja), <strong>exercise</strong> (verde escuro), <strong>note</strong> (cinza)</td></tr>
                    <tr><td>Title</td><td>text</td><td>Título do callout (opcional)</td></tr>
                    <tr><td>Content</td><td>wysiwyg (obrigatório)</td><td>Conteúdo rich-text — use o editor para formatação, imagens, links</td></tr>
                    <tr><td>Show Icon</td><td>boolean</td><td>Mostrar/esconder o ícone do tipo selecionado</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (padrão: 8px)</td></tr>
                    <tr><td>Padding</td><td>text</td><td>Espaçamento interno (padrão: 16px 20px)</td></tr>
                    <tr><td>Margin Bottom</td><td>text</td><td>Margem inferior (padrão: 20px)</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Cada tipo de callout tem cores e ícones automáticos. Use <strong>definition</strong> para definições matemáticas, <strong>theorem</strong> para teoremas e <strong>exercise</strong> para exercícios — perfeito para cursos de matemática!
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Table (Tabela)</h3>
                <p>Tabelas HTML estilizadas com cabeçalho destacado, linhas alternadas e bordas. Insira o HTML da tabela diretamente no editor.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Table Content</td><td>wysiwyg (obrigatório)</td><td>HTML da tabela (<code>&lt;table&gt;</code>, <code>&lt;thead&gt;</code>, <code>&lt;tbody&gt;</code>, <code>&lt;th&gt;</code>, <code>&lt;td&gt;</code>)</td></tr>
                    <tr><td>Header Background</td><td>color</td><td>Cor de fundo do cabeçalho (padrão: #f1f5f9)</td></tr>
                    <tr><td>Header Text Color</td><td>color</td><td>Cor do texto do cabeçalho (padrão: #1e293b)</td></tr>
                    <tr><td>Border Color</td><td>color</td><td>Cor das bordas (padrão: #e2e8f0)</td></tr>
                    <tr><td>Alternating Rows</td><td>boolean</td><td>Linhas alternadas com cores diferentes (ativado por padrão)</td></tr>
                    <tr><td>Text Alignment</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Font Size</td><td>text</td><td>Tamanho da fonte (padrão: 14px)</td></tr>
                    <tr><td>Cell Padding</td><td>text</td><td>Espaçamento das células (padrão: 10px 14px)</td></tr>
                    <tr><td>Border Radius</td><td>text</td><td>Raio da borda (padrão: 8px)</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para criar a tabela, insira o HTML básico no editor WYSIWYG. Exemplo: <code>&lt;table&gt;&lt;thead&gt;&lt;tr&gt;&lt;th&gt;Nome&lt;/th&gt;&lt;th&gt;Nota&lt;/th&gt;&lt;/tr&gt;&lt;/thead&gt;&lt;tbody&gt;&lt;tr&gt;&lt;td&gt;João&lt;/td&gt;&lt;td&gt;8.5&lt;/td&gt;&lt;/tr&gt;&lt;/tbody&gt;&lt;/table&gt;</code>
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Math (Fórmula Matemática)</h3>
                <p>Fórmulas matemáticas em LaTeX, renderizadas via <a href="https://katex.org" target="_blank">KaTeX</a>. Suporta display mode (centralizado e maior) e inline mode.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>LaTeX Formula</td><td>textarea (obrigatório)</td><td>Fórmula em sintaxe LaTeX (ex: <code>x = \frac{-b \pm \sqrt{b^2-4ac}}{2a}</code>)</td></tr>
                    <tr><td>Display Mode</td><td>boolean</td><td>Modo display — fórmula centralizada e maior (ativado por padrão)</td></tr>
                    <tr><td>Alignment</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Font Size</td><td>text</td><td>Tamanho da fonte (padrão: 1.3em)</td></tr>
                    <tr><td>Color</td><td>color</td><td>Cor da fórmula (padrão: #1e293b)</td></tr>
                    <tr><td>Label</td><td>text</td><td>Rótulo ao lado da fórmula (ex: "(1)" para numerar)</td></tr>
                    <tr><td>Margin Top</td><td>text</td><td>Margem superior (padrão: 16px)</td></tr>
                    <tr><td>Margin Bottom</td><td>text</td><td>Margem inferior (padrão: 16px)</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para fórmulas inline no texto do widget <strong>Texto</strong>, clique no botão &#945; na toolbar do WYSIWYG e digite a fórmula LaTeX. A fórmula será renderizada inline no parágrafo. O widget <strong>Math</strong> é ideal para fórmulas em destaque ou centralizadas.
                </div>

                <h4 style="font-size:.95rem;margin-top:1.25rem;margin-bottom:.5rem">Sintaxe LaTeX Básica</h4>
                <table class="widget-table">
                    <tr><th>Sintaxe</th><th>Resultado</th></tr>
                    <tr><td><code>x^2</code></td><td>x ao quadrado</td></tr>
                    <tr><td><code>\frac{a}{b}</code></td><td>fração a/b</td></tr>
                    <tr><td><code>\sqrt{x}</code></td><td>raiz quadrada de x</td></tr>
                    <tr><td><code>\sum_{i=1}^{n}</code></td><td>somatório de i=1 a n</td></tr>
                    <tr><td><code>\int_{a}^{b}</code></td><td>integral de a a b</td></tr>
                    <tr><td><code>\lim_{x \to 0}</code></td><td>limite quando x tende a 0</td></tr>
                    <tr><td><code>\alpha, \beta, \gamma</code></td><td>letras gregas &#945;, &#946;, &#947;</td></tr>
                    <tr><td><code>\leq, \geq, \neq</code></td><td>menor ou igual, maior ou igual, diferente</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Video (Vídeo)</h3>
                <p>Incorporar vídeos do YouTube e Vimeo com configuracoes de aspect ratio, autoplay, loop e mute.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Video URL</td><td>url</td><td>URL do YouTube ou Vimeo</td></tr>
                    <tr><td>Aspect Ratio</td><td>select</td><td>16:9, 4:3, 1:1, 21:9</td></tr>
                    <tr><td>Autoplay</td><td>boolean</td><td>Reproduzir automaticamente</td></tr>
                    <tr><td>Loop</td><td>boolean</td><td>Repetir vídeo</td></tr>
                    <tr><td>Mute</td><td>boolean</td><td>Silenciar áudio</td></tr>
                    <tr><td>Controls</td><td>boolean</td><td>Mostrar controles do player</td></tr>
                    <tr><td>Start/End Time</td><td>number</td><td>Tempo de início/fim em segundos</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Divider (Divisor)</h3>
                <p>Linha horizontal com configuracoes de estilo, espessura, cor e espacamento.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Style</td><td>select</td><td>solid, dashed, dotted, double</td></tr>
                    <tr><td>Width</td><td>number</td><td>Largura em % (0-100)</td></tr>
                    <tr><td>Thickness</td><td>number</td><td>Espessura em px (1-20)</td></tr>
                    <tr><td>Color</td><td>color</td><td>Cor da linha</td></tr>
                    <tr><td>Space Before/After</td><td>number</td><td>Espaçamento antes/depois em px</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Spacer (Espaçador)</h3>
                <p>Espaço em branco configurável para ajustar distâncias entre elementos.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Space</td><td>number</td><td>Altura em px (0-500, padrão: 50)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Icon (Ícone)</h3>
                <p>Ícone Font Awesome com configuracoes de tamanho, cor e link.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Icon</td><td>icon picker</td><td>70+ ícones Font Awesome (fas/fab)</td></tr>
                    <tr><td>Icon Size</td><td>number</td><td>Tamanho em px (12-200)</td></tr>
                    <tr><td>Color</td><td>color</td><td>Cor do ícone</td></tr>
                    <tr><td>Align</td><td>select</td><td>left, center, right</td></tr>
                    <tr><td>Link</td><td>url</td><td>Link opcional</td></tr>
                    <tr><td>Link New Tab</td><td>boolean</td><td>Abrir em nova aba</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Gallery (Galeria)</h3>
                <p>Galeria de imagens em layout grid ou masonry com suporte a upload múltiplo.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Images</td><td>gallery</td><td>Upload múltiplo com drag-and-drop</td></tr>
                    <tr><td>Columns</td><td>select</td><td>1-6 colunas</td></tr>
                    <tr><td>Gap</td><td>number</td><td>Espaçamento entre imagens (px)</td></tr>
                    <tr><td>Layout</td><td>select</td><td>grid, masonry</td></tr>
                    <tr><td>Show Captions</td><td>boolean</td><td>Mostrar legendas</td></tr>
                    <tr><td>Border Radius</td><td>number</td><td>Raio da borda (px)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Form (Formulário)</h3>
                <p>Formulário de contato com campos dinâmicos (text, email, textarea, select, checkbox, radio).</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Fields</td><td>repeater</td><td>Label, type, required — adicionar/remover campos</td></tr>
                    <tr><td>Button Text</td><td>text</td><td>Texto do botão de envio</td></tr>
                    <tr><td>Button Color</td><td>color</td><td>Cor de fundo do botão</td></tr>
                    <tr><td>Button Width</td><td>select</td><td>auto, full (largura total)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabs (Abas)</h3>
                <p>Abas de conteúdo com navigacao horizontal.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Tabs</td><td>repeater</td><td>Title, content — adicionar/remover abas</td></tr>
                    <tr><td>Active Tab</td><td>number</td><td>Aba ativa por padrão</td></tr>
                    <tr><td>Tab Color</td><td>color</td><td>Cor da aba ativa</td></tr>
                    <tr><td>Border Color</td><td>color</td><td>Cor da borda inferior</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Accordion (Acordeão)</h3>
                <p>Seções expansíveis/colapsáveis com conteúdo oculto.</p>
                <table class="widget-table">
                    <tr><th>Controle</th><th>Tipo</th><th>Opções</th></tr>
                    <tr><td>Items</td><td>repeater</td><td>Title, content, open — adicionar/remover itens</td></tr>
                    <tr><td>Tab Color</td><td>color</td><td>Cor do cabeçalho aberto</td></tr>
                    <tr><td>Border Color</td><td>color</td><td>Cor da borda</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Navegador &amp; Atalhos</h3>
                <p>O editor inclui funcionalidades avançadas de produtividade:</p>
                <table class="widget-table">
                    <tr><th>Feature</th><th>Descrição</th></tr>
                    <tr><td><strong>Navigator</strong></td><td>Painel flutuante com árvore de elementos — drag-and-drop, renomear (duplo clique), menu de contexto</td></tr>
                    <tr><td><strong>Right-click</strong></td><td>Menu de contexto no canvas: Editar, Duplicar, Mover cima/baixo, Copiar, Colar, Excluir</td></tr>
                    <tr><td><strong>Drag Handle</strong></td><td>Ícone ⣿ para arrastar reordenar com linhas indicadoras de posição</td></tr>
                    <tr><td><strong>Zoom</strong></td><td>Ctrl+Scroll ou botões +/- (25%-200%), Ctrl+0 para reset</td></tr>
                    <tr><td><strong>Fullscreen</strong></td><td>Botão ou F11 para esconder painéis laterais</td></tr>
                    <tr><td><strong>Atalhos</strong></td><td>Ctrl+Z desfazer, Ctrl+Shift+Z refazer, Ctrl+S salvar, Delete excluir</td></tr>
                    <tr><td><strong>Inline Editing</strong></td><td>Duplo-clique no canvas para editar texto diretamente (Heading, Text, Button, Callout)</td></tr>
                </table>
            </div>
        </section>

        {{-- EDITING WIDGET SETTINGS --}}
        <section id="select-settings" class="step">
            <h2>6. Selecionar &amp; Editar Configurações</h2>
            <div class="step-body">
                <ol>
                    <li><strong>Clique</strong> em qualquer elemento no canvas — ele ganha uma borda azul indicando que está selecionado</li>
                    <li>O <strong>painel direito</strong> é atualizado para mostrar todas as configurações editáveis daquele widget</li>
                    <li>Cada widget expõe seus próprios controles (ex.: Título tem <em>Nível</em>, <em>Texto</em>, <em>Alinhamento</em>, <em>Cor</em>)</li>
                    <li>Altere qualquer valor — o canvas <strong>atualiza em tempo real</strong></li>
                </ol>
                <div class="illustration">
                    <div class="ill-flow">
                        <span>Clique no elemento</span>
                        <span>&rarr;</span>
                        <span>Painel direito mostra controles</span>
                        <span>&rarr;</span>
                        <span>Altere o valor</span>
                        <span>&rarr;</span>
                        <span>Canvas atualiza ao vivo</span>
                    </div>
                </div>
            </div>
        </section>

        {{-- PAGE SETTINGS --}}
        <section id="page-settings" class="step">
            <h2>7. Configurações da Página</h2>
            <div class="step-body">
                <p>Você também pode alterar configurações que se aplicam a página inteira:</p>
                <ol>
                    <li>Na barra de ferramentas do editor, clique em <strong>"Config. da Página"</strong></li>
                    <li>O painel direito alterna para controles de nível de página</li>
                    <li>Configure:
                        <ul>
                            <li><strong>Largura do Container</strong> — largura máxima do conteúdo (ex.: 1140px, largura total)</li>
                            <li><strong>Fundo da Página</strong> — cor ou imagem de fundo para toda a página</li>
                            <li><strong>Padding do Conteúdo</strong> — espaçamento interno ao redor do conteúdo</li>
                            <li><strong>CSS Personalizado</strong> — CSS puro para customização avançada</li>
                        </ul>
                    </li>
                    <li>As alterações se aplicam instantaneamente à prévia no canvas</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Clique em "Config. da Página" novamente para desselecionar e voltar à edição de widgets.
                </div>
            </div>
        </section>

        {{-- RESPONSIVE --}}
        <section id="responsive" class="step">
            <h2>8. Visualização Responsiva</h2>
            <div class="step-body">
                <p>Veja como sua página fica em diferentes tamanhos de tela:</p>
                <ol>
                    <li>Na barra de ferramentas do editor, clique em um dos ícones responsivos:
                        <ul>
                            <li>&#128187; <strong>Desktop</strong> — largura total</li>
                            <li>&#128241; <strong>Tablet</strong> — 768px de largura</li>
                            <li>&#128241; <strong>Mobile</strong> — 375px de largura</li>
                        </ul>
                    </li>
                    <li>A largura do canvas se ajusta instantaneamente</li>
                    <li>Você pode editar configurações e adicionar widgets em qualquer ponto de quebra</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Use a visualização mobile para garantir que seu layout fique bem empilhado em telas pequenas.
                </div>
            </div>
        </section>

        {{-- TEMPLATES --}}
        <section id="templates" class="step">
            <h2>9. Usar Templates</h2>
            <div class="step-body">
                <p>Os templates fornecem um ponto de partida pré-populando a página com seções, colunas e widgets.</p>
                <p><strong>Ao criar uma nova página:</strong></p>
                <ol>
                    <li>No formulário de <strong>Criar Página</strong>, procure pela seção "Template"</li>
                    <li>Escolha entre:
                        <ul>
                            <li><strong>Página em Branco</strong> — começa vazia (padrão)</li>
                            <li><strong>Landing Page</strong> — seção hero + grade de recursos com títulos, textos e botões</li>
                            <li><strong>Sobre (About)</strong> — introdução da empresa com seções de missão/equipe</li>
                            <li><strong>Contato (Contact)</strong> — layout de formulário de contato com informações e área de formulário</li>
                            <li><strong>Showcase Completo</strong> — página de marketing multi-seção com hero, recursos, estatísticas, galeria e equipe</li>
                        </ul>
                    </li>
                    <li>Clique em <strong>"Criar &amp; Abrir Editor"</strong> para ver o template carregado no editor</li>
                    <li>Você pode então modificar qualquer elemento ou adicionar mais widgets</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Você também pode aplicar um template a uma página existente a partir da lista de páginas usando o botão "Aplicar Template" no menu de ações.
                </div>
            </div>
        </section>

        {{-- UNDO / REDO --}}
        <section id="undo-redo" class="step">
            <h2>10. Desfazer &amp; Refazer</h2>
            <div class="step-body">
                <p>Toda alteração feita no editor é rastreada, permitindo voltar ou avançar:</p>
                <ul>
                    <li><strong>Desfazer</strong> — pressione <kbd>Ctrl</kbd> + <kbd>Z</kbd> ou clique no botão &#8630; na barra de ferramentas</li>
                    <li><strong>Refazer</strong> — pressione <kbd>Ctrl</kbd> + <kbd>Shift</kbd> + <kbd>Z</kbd> ou clique no botão &#8631; na barra de ferramentas</li>
                </ul>
                <p>Funciona em todas as ações: adicionar, mover, excluir elementos, alterar configurações, etc.</p>
            </div>
        </section>

        {{-- SAVE & PUBLISH --}}
        <section id="save-publish" class="step">
            <h2>11. Salvar &amp; Publicar</h2>
            <div class="step-body">
                <p>Dois botões na barra de ferramentas do editor permitem persistir seu trabalho:</p>
                <table class="widget-table">
                    <tr><th>Botão</th><th>O que faz</th></tr>
                    <tr><td><strong>Salvar (Save)</strong></td><td>Envia os dados atuais da página para o servidor via requisição <code>PUT /page-builder/pages/{id}</code> com status <code>draft</code>. O editor exibe um toast "Page saved!" e o badge de status permanece como "draft".</td></tr>
                    <tr><td><strong>Publicar (Publish)</strong></td><td>Exibe uma confirmação: <em>"Publish this page?"</em>. Se confirmado, envia um <code>POST /page-builder/pages/{id}/publish</code> que altera o campo <code>status</code> da página no banco de dados para <code>published</code>. Após a resposta, exibe um toast "Page published!" e <strong>recarrega</strong> a página do editor para refletir o novo status.</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Fluxo detalhado do Publish</h3>
                <ol>
                    <li>O usuário clica no botão <strong>Publish</strong> (ícone &#128752;).</li>
                    <li>Uma caixa de diálogo <code>confirm()</code> pergunta: <em>"Publish this page?"</em>.</li>
                    <li>Se o usuário cancelar, nada acontece.</li>
                    <li>Se confirmar, o JavaScript envia uma requisição <code>POST</code> para a rota <code>/page-builder/pages/{id}/publish</code> com o token CSRF.</li>
                    <li>No servidor, o controller <code>PageController@publish()</code> (arquivo <code>app/Http/Controllers/PageBuilder/PageController.php:405</code>) executa:
                        <ul>
                            <li><code>$page->status = 'published'</code> — altera o status no model Eloquent.</li>
                            <li><code>$page->save()</code> — persiste a alteração no banco de dados.</li>
                            <li>Retorna um JSON: <code>{"message": "Page published successfully", "page": {...}}</code>.</li>
                        </ul>
                    </li>
                    <li>O JavaScript recebe a resposta, exibe um toast "Page published!" e chama <code>location.reload()</code>.</li>
                    <li>Após o recarregamento, o badge na barra de ferramentas do editor mostra <strong>published</strong> em verde.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Diferença entre Salvar e Publicar</h3>
                <ul>
                    <li><strong>Salvar</strong> apenas persiste os dados. O status da página não é alterado — permanece como estava (draft ou published).</li>
                    <li><strong>Publicar</strong> altera o status para <code>published</code> e também recarrega o editor para refletir visualmente a mudança.</li>
                    <li>Não há diferença de visibilidade no front-end público: todas as rotas de renderização são protegidas por <code>auth</code>, então apenas usuários logados podem ver qualquer página, seja rascunho ou publicada.</li>
                    <li>O status <code>published</code> vs <code>draft</code> serve como indicador de progresso e pode ser usado por integrações externas para saber quais páginas estão prontas para exibição.</li>
                </ul>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O editor também faz <strong>salvamento automático</strong> a cada 60 segundos (via <code>setInterval</code> no JavaScript), para que você não perca trabalho se esquecer de salvar manualmente. O auto-save usa o mesmo fluxo do botão "Save" — ou seja, não publica automaticamente.
                </div>
            </div>
        </section>

        {{-- PREVIEW --}}
        <section id="preview" class="step">
            <h2>12. Visualizar uma Página</h2>
            <div class="step-body">
                <p>Na lista de páginas, clique em <strong>"Visualizar"</strong> ao lado de qualquer página.</p>
                <p>Isso abre uma prévia limpa da página com todos os widgets renderizados e estilizados como apareceriam no site ao vivo.</p>
                <p>Se a página ainda for um <strong>rascunho</strong>, apenas você (usuários logados) pode vê-la.</p>
            </div>
        </section>

        {{-- DUPLICATE / DELETE --}}
        <section id="duplicate-delete" class="step">
            <h2>13. Ações nas Páginas (Listagem)</h2>
            <div class="step-body">
                <p>Na lista de páginas, cada página tem os seguintes botões de ação:</p>
                <table class="widget-table">
                    <tr><th>Ação</th><th>Como funciona</th></tr>
                    <tr><td><strong>Editar (Edit)</strong></td><td>Abre o editor visual em tela cheia para modificar o layout e conteúdo da página.</td></tr>
                    <tr><td><strong>Visualizar (View)</strong></td><td>Abre uma prévia limpa da página em uma nova aba, como os visitantes a veriam.</td></tr>
                    <tr><td><strong>Duplicar (Duplicate)</strong></td><td>Cria uma cópia exata da página (incluindo todos os elementos) com "(cópia)" anexado ao título. A página duplicada inicia como rascunho.</td></tr>
                    <tr><td><strong>Exportar (Export)</strong></td><td>Baixa a página como um arquivo <code>.json</code> — você pode compartilhar ou fazer backup. O nome do arquivo é baseado no título da página.</td></tr>
                    <tr><td><strong>Copiar HTML (Copy HTML)</strong></td><td>Copia o HTML renderizado da página para a área de transferência. Ideal para colar o conteúdo em outras ferramentas ou sistemas (como o Moodle).</td></tr>
                    <tr><td><strong>Importar (Import)</strong></td><td>Abre um modal para selecionar um arquivo <code>.json</code> previamente exportado. A página importada é recriada no sistema com novo ID.</td></tr>
                    <tr><td><strong>Excluir (Delete)</strong></td><td>Remove permanentemente a página e todos os seus elementos. Uma confirmação é solicitada antes da exclusão.</td></tr>
                </table>
                <div class="tip" style="margin-top:1rem">
                    <strong>&#128161; Dica:</strong> Use <strong>Exportar</strong> para fazer backup das suas páginas ou transferi-las entre instalações. Use <strong>Copiar HTML</strong> para colar o conteúdo renderizado em sistemas externos como Moodle, WordPress ou editores HTML.
                </div>
            </div>
        </section>

        {{-- SHOWCASE COMPLETO TEMPLATE --}}
        <section id="showcase" class="step">
            <h2>14. Template Showcase Completo</h2>
            <div class="step-body">
                <p>O template <strong>Showcase Completo</strong> é uma landing page de marketing completa construída inteiramente com o page builder. Ele demonstra técnicas avançadas de layout usando <strong>Seções com layout full_width e boxed</strong>, <strong>Colunas com larguras variadas</strong> e widgets estilizados.</p>

                <p>Este passo a passo reproduz <strong>exatamente</strong> o template que é aplicado automaticamente quando você seleciona "Showcase Completo" ao criar uma página. Cada cor, texto e configuração abaixo corresponde ao código-fonte do template.</p>

                <div class="tip">
                    <strong>&#128161; Dica Rápida:</strong> Você não precisa construir tudo manualmente! Basta criar uma página e selecionar o template <strong>"Showcase Completo"</strong> na hora da criação. O passo a passo abaixo é para quem quer entender como cada seção foi construída e personalizar depois.
                </div>
                <div class="info" style="background:#eff6ff;border-color:#93c5fd;padding:.75rem 1rem;border-radius:8px;margin:.75rem 0">
                    <strong>&#128204; Sobre as abas de configuração:</strong> Cada widget possui três abas no painel direito: <strong>Content</strong> (conteúdo principal), <strong>Style</strong> (aparência visual: cores, tipografia, background, border) e <strong>Advanced</strong> (espaçamento: padding, margin, z-index, CSS). As instruções abaixo indicam a aba correta entre parênteses quando a configuração não está na aba padrão (Content). Exemplo: <code>Padding superior: 120px (aba Advanced)</code>.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Visão Geral das Seções</h3>
                <p>O template possui <strong>6 seções</strong> que, de cima para baixo, formam a landing page completa:</p>
                <table class="widget-table">
                    <tr><th>#</th><th>Seção</th><th>Fundo</th><th>Layout</th><th>Colunas</th><th>Widgets</th></tr>
                    <tr><td>1</td><td><strong>Hero</strong></td><td><code>#0f172a</code> (escuro)</td><td>full_width, 90vh</td><td>1 (100%)</td><td>Título H1, Texto, Botão</td></tr>
                    <tr><td>2</td><td><strong>Serviços</strong></td><td><code>#ffffff</code> (branco)</td><td>boxed</td><td>1 + 3 (33% cada)</td><td>Título H2, Texto, 3 cards com Título H3 + Texto</td></tr>
                    <tr><td>3</td><td><strong>Estatísticas</strong></td><td><code>#1e293b</code> (escuro)</td><td>full_width</td><td>4 (25% cada)</td><td>4x Título H2 + Texto</td></tr>
                    <tr><td>4</td><td><strong>Equipe</strong></td><td><code>#ffffff</code> (branco)</td><td>boxed</td><td>1 + 4 (25% cada)</td><td>Título H2, Texto, 4x Título H4 + Texto</td></tr>
                    <tr><td>5</td><td><strong>Depoimentos</strong></td><td><code>#f8fafc</code> (cinza claro)</td><td>boxed</td><td>1 + 3 (33% cada)</td><td>Título H2, Texto, 3x cards com Texto + H4 + Texto</td></tr>
                    <tr><td>6</td><td><strong>CTA</strong></td><td><code>#3b82f6</code> (azul)</td><td>boxed</td><td>1 (100%)</td><td>Título H2, Texto, Botão</td></tr>
                </table>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">14.1 Seção Hero (Cabeçalho Principal)</h3>
                <p>Esta é a primeira coisa que o visitante vê. Ocupa quase toda a tela com fundo escuro e conteúdo centralizado.</p>
                <ol>
                    <li><strong>Arraste uma Seção</strong> da lista de widgets para a área da página (canvas).</li>
                    <li><strong>Selecione a Seção</strong> clicando nela. No painel direito, configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>full_width</code> (largura total da tela)</li>
                            <li><strong>Cor de fundo:</strong> <code>#0f172a</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>120px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>120px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Altura mínima:</strong> <code>90vh</code> (90% da altura da tela)</li>
                            <li><strong>Alinhar itens:</strong> <code>center</code> (centralizar verticalmente)</li>
                            <li><strong>Justificar conteúdo:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção.</li>
                    <li><strong>Selecione a Coluna</strong> e configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code> (largura total)</li>
                            <li><strong>Alinhamento vertical:</strong> <code>center</code></li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste um widget Título</strong> para dentro da Coluna. Configure:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Transforme Suas Ideias em Experiências Digitais"</code></li>
                            <li><strong>Tag:</strong> <code>H1</code></li>
                            <li><strong>Tamanho:</strong> <code>xxl</code></li>
                            <li><strong>Cor:</strong> <code>#ffffff</code> (branco) (aba <strong>Style</strong>)</li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Peso da fonte:</strong> <code>800</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Margem inferior:</strong> <code>24px</code> (aba <strong>Advanced</strong>, campo <em>Bottom Margin</em>)</li>
                        </ul>
                    </li>
                    <li><strong>Arraste um widget Texto</strong> para dentro da Coluna (abaixo do título). Configure:
                        <ul>
                            <li><strong>Conteúdo:</strong> <code>"Criamos soluções inovadoras que combinam design moderno, tecnologia de ponta e performance excepcional para impulsionar o seu negócio."</code></li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Tamanho da fonte:</strong> <code>20px</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Altura da linha:</strong> <code>1.8</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Margem inferior:</strong> <code>40px</code> (aba <strong>Advanced</strong>, campo <em>Bottom Margin</em>)</li>
                        </ul>
                        <div class="tip">
                            <strong>&#128161; Dica de estilo:</strong> Para o estilo exato como no template real, adicione no conteúdo HTML: <code>&lt;p style="font-size:1.25rem;color:#94a3b8;max-width:700px;margin:0 auto;"&gt;...&lt;/p&gt;</code>
                        </div>
                    </li>
                    <li><strong>Arraste um widget Botão</strong> para dentro da Coluna (abaixo do texto). Configure:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Comece Agora"</code></li>
                            <li><strong>Link:</strong> <code>#</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#3b82f6</code> (azul) (aba <strong>Style</strong>)</li>
                            <li><strong>Cor do texto:</strong> <code>#ffffff</code> (branco) (aba <strong>Style</strong>)</li>
                            <li><strong>Tamanho:</strong> <code>large</code></li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Border Radius:</strong> <code>50px</code> (formato pílula) (aba <strong>Style</strong>)</li>
                            <li><strong>Padding esquerda/direita:</strong> <code>40px</code> (aba <strong>Advanced</strong>, grupo <em>Padding &amp; Margin</em>)</li>
                            <li><strong>Peso da fonte:</strong> <code>600</code> (aba <strong>Style</strong>, grupo <em>Typography</em>)</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">14.2 Seção de Serviços (Features)</h3>
                <p>Três cards de serviço centralizados com fundo branco. Esta seção usa o layout <strong>boxed</strong> (largura contida) e uma coluna de cabeçalho separada das colunas de conteúdo.</p>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página (abaixo da Hero). Configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>boxed</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>100px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>100px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção. Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code> (largura total)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Padding inferior:</strong> <code>20px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste um <strong>Título</strong> e um <strong>Texto</strong>:
                        <ul>
                            <li><strong>Título:</strong> <code>"Nossos Serviços"</code>, tag <code>H2</code>, tamanho <code>xl</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>), margem inferior <code>16px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Oferecemos um conjunto completo de soluções para transformar sua presença digital"</code>, tamanho <code>18px</code> (aba <strong>Style</strong>)</li>
                        </ul>
                        <div class="tip">
                            <strong>&#128161; Dica:</strong> Para o texto ficar cinza e centralizado, use no HTML: <code>&lt;p style="color:#64748b;max-width:600px;margin:0 auto;"&gt;...&lt;/p&gt;</code>
                        </div>
                    </li>
                    <li><strong>Arraste uma nova Coluna</strong> para dentro da mesma Seção (abaixo da coluna de cabeçalho). Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-4</code> (um terço da largura)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#f8fafc</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>40px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>40px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Border Radius:</strong> <code>12px</code> (aba <strong>Style</strong>, grupo <em>Border</em>)</li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste um <strong>Título</strong> e um <strong>Texto</strong>:
                        <ul>
                            <li><strong>Título:</strong> <code>"Design Moderno"</code>, tag <code>H3</code>, tamanho <code>medium</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>600</code> (aba <strong>Style</strong>), margem inferior <code>12px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Interfaces elegantes e intuitivas criadas com as melhores práticas de UX/UI."</code>, tamanho <code>15px</code> (aba <strong>Style</strong>), altura da linha <code>1.7</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Repita o passo 4 e 5</strong> para criar mais duas colunas idênticas ao lado:
                        <ul>
                            <li><strong>Coluna 2:</strong> fundo <code>#f8fafc</code>, título <code>"Performance"</code>, texto <code>"Otimizado para velocidade e desempenho máximo em qualquer dispositivo."</code></li>
                            <li><strong>Coluna 3:</strong> fundo <code>#f8fafc</code>, título <code>"Suporte Dedicado"</code>, texto <code>"Equipe especializada pronta para ajudar em cada etapa do seu projeto."</code></li>
                        </ul>
                    </li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica importante:</strong> O page builder coloca colunas adicionadas à mesma seção lado a lado automaticamente. Ao adicionar 3 colunas com <code>col-4</code>, elas se distribuem em 33% cada. Não precisa de uma "Seção linha" extra — basta adicionar colunas direto na seção.
                </div>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">14.3 Seção de Estatísticas (Stats)</h3>
                <p>Quatro números grandes com fundo escuro, criando destaque visual.</p>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>full_width</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#1e293b</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>80px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>80px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Arraste quatro Colunas</strong> para dentro da Seção (uma de cada vez). Configure cada uma:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-3</code> (25% cada)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li><strong>Na primeira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"500+"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code> (azul, aba <strong>Style</strong>), peso <code>800</code> (aba <strong>Style</strong>), margem inferior <code>8px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Projetos Entregues"</code>, cor <code>#94a3b8</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na segunda Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"98%"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code> (aba <strong>Style</strong>), peso <code>800</code> (aba <strong>Style</strong>), margem inferior <code>8px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Satisfação dos Clientes"</code>, cor <code>#94a3b8</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na terceira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"50+"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code> (aba <strong>Style</strong>), peso <code>800</code> (aba <strong>Style</strong>), margem inferior <code>8px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Profissionais"</code>, cor <code>#94a3b8</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na quarta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"12+"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code> (aba <strong>Style</strong>), peso <code>800</code> (aba <strong>Style</strong>), margem inferior <code>8px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Anos de Experiência"</code>, cor <code>#94a3b8</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">14.4 Seção de Equipe (Team)</h3>
                <p>Apresentação dos quatro membros da equipe em colunas com fundo branco.</p>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>boxed</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>100px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>100px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção. Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code></li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Padding inferior:</strong> <code>30px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Nossa Equipe"</code>, tag <code>H2</code>, tamanho <code>xl</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>), margem inferior <code>16px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Conheça os profissionais que tornam tudo possível"</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste quatro Colunas</strong> para dentro da mesma Seção (abaixo da coluna de cabeçalho). Configure cada uma:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-3</code> (25% cada)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li><strong>Na primeira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Ana Silva"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>), margem inferior <code>4px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"CEO & Fundadora"</code>, cor <code>#64748b</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na segunda Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Carlos Oliveira"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>), margem inferior <code>4px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"CTO"</code>, cor <code>#64748b</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na terceira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Marina Costa"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>), margem inferior <code>4px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Head de Design"</code>, cor <code>#64748b</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na quarta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Rafael Santos"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>), margem inferior <code>4px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Lead Developer"</code>, cor <code>#64748b</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">14.5 Seção de Depoimentos (Testimonials)</h3>
                <p>Três cards de depoimentos de clientes com fundo cinza claro, criando contraste visual com as seções vizinhas.</p>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>boxed</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#f8fafc</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>100px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>100px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção. Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code></li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Padding inferior:</strong> <code>20px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"O Que Nossos Clientes Dizem"</code>, tag <code>H2</code>, tamanho <code>xl</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>), margem inferior <code>16px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Texto:</strong> <code>"A satisfação dos nossos clientes é a nossa maior recompensa"</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste três Colunas</strong> para dentro da mesma Seção (abaixo da coluna de cabeçalho). Configure cada uma:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-4</code> (33% cada)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>40px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>40px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Border Radius:</strong> <code>12px</code> (aba <strong>Style</strong>, grupo <em>Border</em>)</li>
                        </ul>
                    </li>
                    <li><strong>Na primeira Coluna</strong>, arraste (nesta ordem):
                        <ul>
                            <li><strong>Texto:</strong> <code>"A equipe transformou completamente nossa presença online."</code>, cor <code>#475569</code> (aba <strong>Style</strong>), estilo <em>itálico</em></li>
                            <li><strong>Título:</strong> <code>"João Mendes"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Texto:</strong> <code>"CEO, TechStart"</code>, cor <code>#94a3b8</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na segunda Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Profissionalismo e qualidade excepcionais."</code>, cor <code>#475569</code> (aba <strong>Style</strong>), estilo <em>itálico</em></li>
                            <li><strong>Título:</strong> <code>"Fernanda Lima"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Diretora, InnovateLab"</code>, cor <code>#94a3b8</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Na terceira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Resultados incríveis em tempo recorde."</code>, cor <code>#475569</code> (aba <strong>Style</strong>), estilo <em>itálico</em></li>
                            <li><strong>Título:</strong> <code>"Pedro Alves"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code> (aba <strong>Style</strong>), peso <code>700</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Texto:</strong> <code>"Fundador, WebPlus"</code>, cor <code>#94a3b8</code> (aba <strong>Style</strong>)</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">14.6 Seção CTA (Chamada para Ação)</h3>
                <p>A seção final com fundo azul brilhante que convida o visitante a entrar em contato. Centralizada com um botão grande.</p>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Layout:</strong> <code>boxed</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#3b82f6</code> (azul) (aba <strong>Style</strong>)</li>
                            <li><strong>Padding superior:</strong> <code>80px</code> (aba <strong>Advanced</strong>)</li>
                            <li><strong>Padding inferior:</strong> <code>80px</code> (aba <strong>Advanced</strong>)</li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção. Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code> (largura total)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                        </ul>
                    </li>
                    <li><strong>Dentro da Coluna</strong>, arraste um <strong>Título</strong>:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Pronto para Transformar seu Negócio?"</code></li>
                            <li><strong>Tag:</strong> <code>H2</code></li>
                            <li><strong>Tamanho:</strong> <code>xl</code></li>
                            <li><strong>Cor:</strong> <code>#ffffff</code> (branco) (aba <strong>Style</strong>)</li>
                            <li><strong>Peso:</strong> <code>700</code> (aba <strong>Style</strong>)</li>
                            <li><strong>Margem inferior:</strong> <code>16px</code> (aba <strong>Advanced</strong>, campo <em>Bottom Margin</em>)</li>
                        </ul>
                    </li>
                    <li><strong>Abaixo do título</strong>, arraste um <strong>Texto</strong>:
                        <ul>
                            <li><strong>Conteúdo:</strong> <code>"Entre em contato conosco hoje e descubra como podemos ajudar sua empresa a alcançar novos patamares."</code></li>
                        </ul>
                        <div class="tip">
                            <strong>&#128161; Dica:</strong> Para o estilo exato, use no HTML: <code>&lt;p style="color:#bfdbfe;max-width:600px;margin:0 auto 30px;font-size:1.15rem;"&gt;...&lt;/p&gt;</code>
                        </div>
                    </li>
                    <li><strong>Abaixo do texto</strong>, arraste um <strong>Botão</strong>:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Fale Conosco"</code></li>
                            <li><strong>Link:</strong> <code>#</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code> (branco) (aba <strong>Style</strong>)</li>
                            <li><strong>Cor do texto:</strong> <code>#3b82f6</code> (azul — mesma cor do fundo da seção) (aba <strong>Style</strong>)</li>
                            <li><strong>Tamanho:</strong> <code>large</code></li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Border Radius:</strong> <code>50px</code> (formato pílula) (aba <strong>Style</strong>)</li>
                            <li><strong>Padding esquerda/direita:</strong> <code>40px</code> (aba <strong>Advanced</strong>, grupo <em>Padding &amp; Margin</em>)</li>
                            <li><strong>Peso da fonte:</strong> <code>700</code> (aba <strong>Style</strong>, grupo <em>Typography</em>)</li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <div class="tip">
                    <strong>&#128161; Dica Final:</strong> O template Showcase Completo é o melhor exemplo de construção avançada de páginas. Ele demonstra: <strong>layouts full_width e boxed</strong>, <strong>colunas com larguras variadas</strong> (col-3, col-4, col-12), <strong>cores escuras e claras alternadas</strong> para ritmo visual, e <strong>todos os tipos de widget</strong> (Título, Texto, Botão). Para ver exatamente como cada elemento está configurado, crie uma página com o template e abra no editor — clique em cada elemento para inspecionar suas configurações no painel direito.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">14.7 Usando os Novos Recursos no Showcase</h3>
                <p>Com as melhorias implementadas (Fases 1–3), o showcase agora suporta muito mais do que o básico. Veja como aplicar os novos recursos em cada seção:</p>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Style Tab — Background na Seção Hero</h4>
                <p>Selecione a Seção Hero → aba <strong>Style</strong> → grupo <strong>Background</strong>:</p>
                <ul>
                    <li><strong>Background Color:</strong> <code>#0f172a</code> (aplica <code>background-color</code> via styles)</li>
                    <li><strong>Background Image:</strong> adicione uma imagem de fundo (URL pública)</li>
                    <li><strong>Size:</strong> <code>cover</code> para preencher toda a seção</li>
                    <li><strong>Repeat:</strong> <code>no-repeat</code></li>
                </ul>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> O Style Tab aplica estilos via <code>PUT /elements/{id}/styles</code>, que gera CSS inline no wrapper do elemento. Assim, a imagem de fundo funciona mesmo no Moodle.
                </div>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Style Tab — Border e Box Shadow nos Cards de Serviços</h4>
                <p>Selecione uma Coluna de serviço → aba <strong>Style</strong> → grupo <strong>Border</strong>:</p>
                <ul>
                    <li><strong>Border Width:</strong> <code>1px</code></li>
                    <li><strong>Border Color:</strong> <code>#e2e8f0</code></li>
                    <li><strong>Border Radius:</strong> <code>12px</code></li>
                    <li><strong>Border Style:</strong> <code>solid</code></li>
                </ul>
                <p>No grupo <strong>Box Shadow</strong>:</p>
                <ul>
                    <li><strong>Horizontal:</strong> <code>0</code>, <strong>Vertical:</strong> <code>4</code>, <strong>Blur:</strong> <code>12</code>, <strong>Spread:</strong> <code>0</code></li>
                    <li><strong>Color:</strong> <code>rgba(0,0,0,0.1)</code></li>
                </ul>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Style Tab — Hover Effects no Botão CTA</h4>
                <p>Selecione o Botão "Fale Conosco" → aba <strong>Style</strong> → grupo <strong>Hover Effects</strong>:</p>
                <ul>
                    <li><strong>Background Color:</strong> <code>#2563eb</code> (azul mais escuro no hover)</li>
                    <li><strong>Transform:</strong> <code>scale(1.05)</code> (botão cresce 5%)</li>
                    <li><strong>Transition:</strong> <code>300</code> ms (animação suave)</li>
                </ul>
                <p>O editor gera automaticamente uma tag <code>&lt;style&gt;</code> inline com <code>.pb-button:hover</code> — compatível com Moodle.</p>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Style Tab — Typography nos Títulos</h4>
                <p>Selecione qualquer Título → aba <strong>Style</strong> → grupo <strong>Typography</strong>:</p>
                <ul>
                    <li><strong>Font Family:</strong> <code>'Inter', sans-serif</code></li>
                    <li><strong>Font Size:</strong> <code>2.5rem</code></li>
                    <li><strong>Font Weight:</strong> <code>800</code></li>
                    <li><strong>Line Height:</strong> <code>1.2</code></li>
                    <li><strong>Letter Spacing:</strong> <code>-0.02em</code></li>
                    <li><strong>Text Transform:</strong> <code>uppercase</code></li>
                </ul>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Style Tab — Tabs e Accordion no Showcase</h4>
                <p>Para demonstrar os novos widgets de <strong>Abas</strong> e <strong>Accordion</strong>, adicione uma nova seção ao showcase:</p>
                <ol>
                    <li><strong>Arraste um widget Tabs</strong> para uma coluna. Configure no painel Content:
                        <ul>
                            <li>Adicione 3 abas: "Visão Geral", "Funcionalidades", "Preços"</li>
                            <li>Orientação: <code>horizontal</code></li>
                        </ul>
                    </li>
                    <li>Na aba <strong>Style</strong>:
                        <ul>
                            <li><strong>Active Tab Color:</strong> <code>#3b82f6</code></li>
                            <li><strong>Border Color:</strong> <code>#e2e8f0</code></li>
                            <li><strong>Content Padding:</strong> <code>20</code>px</li>
                        </ul>
                    </li>
                    <li><strong>Arraste um widget Accordion</strong> abaixo das tabs. Configure:
                        <ul>
                            <li>3 itens: "O que está incluído?", "Como funciona?", "Posso cancelar?"</li>
                            <li>Abra o primeiro item (<em>Open by Default</em>)</li>
                        </ul>
                    </li>
                    <li>Na aba <strong>Style</strong> do Accordion:
                        <ul>
                            <li><strong>Active Color:</strong> <code>#3b82f6</code></li>
                            <li><strong>Border Color:</strong> <code>#e2e8f0</code></li>
                            <li><strong>Content Padding:</strong> <code>16</code>px</li>
                        </ul>
                    </li>
                </ol>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Navigator — Gerenciando Elementos</h4>
                <p>O <strong>Navigator</strong> (botão ☰ no canto inferior direito) mostra a árvore completa de elementos. Veja como usá-lo no showcase:</p>
                <ol>
                    <li><strong>Abrir Navigator:</strong> clique no botão flutuante ☰ → painel aparece com a árvore</li>
                    <li><strong>Navegar:</strong> clique em qualquer item para selecioná-lo no canvas</li>
                    <li><strong>Reordenar:</strong> arraste um item para cima/baixo na árvore → a ordem muda no canvas</li>
                    <li><strong>Mover entre colunas:</strong> arraste um widget de uma coluna para outra</li>
                    <li><strong>Renomear:</strong> duplo clique no nome → digite novo nome → Enter para salvar</li>
                    <li><strong>Context menu:</strong> clique direito → Duplicate, Delete, Copy, Paste, Move Up/Down</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> O Navigator é especialmente útil quando a página tem muitos elementos empilhados. Em vez de clicar no canvas para achar um widget específico, use o Navigator para localizá-lo instantaneamente.
                </div>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Advanced Tab — Padding e Margin</h4>
                <p>Selecione qualquer elemento → aba <strong>Advanced</strong> → grupo <strong>Padding &amp; Margin</strong>:</p>
                <ul>
                    <li><strong>Padding Top:</strong> <code>20px</code></li>
                    <li><strong>Padding Bottom:</strong> <code>20px</code></li>
                    <li><strong>Margin Top:</strong> <code>40px</code></li>
                    <li><strong>Margin Bottom:</strong> <code>0</code></li>
                </ul>
                <p>No grupo abaixo, você pode adicionar <strong>CSS Classes</strong> (ex: <code>meu-estilo-custom</code>) e <strong>CSS ID</strong> (ex: <code>hero-titulo</code>) para referenciar em stylesheets externos.</p>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Widget de Vídeo na Seção Hero</h4>
                <p>Para transformar a Hero em uma seção com vídeo de fundo:</p>
                <ol>
                    <li><strong>Arraste o widget Vídeo</strong> para dentro da Seção Hero</li>
                    <li>Configure: <strong>Video Type:</strong> <code>youtube</code>, <strong>URL:</strong> <code>https://youtube.com/watch?v=...</code></li>
                    <li><strong>Aspect Ratio:</strong> <code>16:9</code>, <strong>Mute:</strong> ligado, <strong>Loop:</strong> ligado</li>
                    <li>No Style Tab → <strong>Border Radius:</strong> <code>0</code> para preencher toda a seção</li>
                </ol>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Widget de Galeria na Seção de Equipe</h4>
                <p>Substitua os cards de equipe por fotos usando o widget Galeria:</p>
                <ol>
                    <li><strong>Arraste o widget Galeria</strong> para a coluna de um membro da equipe</li>
                    <li>Configure: <strong>Columns:</strong> <code>1</code>, <strong>Image Fit:</strong> <code>cover</code></li>
                    <li>No Style Tab → <strong>Border Radius:</strong> <code>50%</code> (formato circular para fotos)</li>
                    <li><strong>Box Shadow:</strong> <code>0 4px 12px rgba(0,0,0,0.1)</code></li>
                </ol>

                <h4 style="font-size:.9rem;margin-top:1rem;margin-bottom:.5rem">Widget de Formulário no CTA</h4>
                <p>Adicione um formulário de contato na seção CTA:</p>
                <ol>
                    <li><strong>Arraste o widget Formulário</strong> para a coluna CTA</li>
                    <li>Configure os campos: Nome (text), Email (email), Mensagem (textarea)</li>
                    <li><strong>Button Text:</strong> <code>"Enviar Mensagem"</code></li>
                    <li>No Style Tab → <strong>Button Color:</strong> <code>#ffffff</code>, <strong>Button Text Color:</strong> <code>#3b82f6</code></li>
                    <li><strong>Field Border Radius:</strong> <code>8</code>px</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Paleta de Cores Utilizada</h3>
                <table class="widget-table">
                    <tr><th>Cor</th><th>Código</th><th>Uso</th></tr>
                    <tr><td style="background:#0f172a;color:#fff;padding:4px 8px;border-radius:4px">Azul muito escuro</td><td><code>#0f172a</code></td><td>Fundo do Hero, cor dos títulos da seção</td></tr>
                    <tr><td style="background:#1e293b;color:#fff;padding:4px 8px;border-radius:4px">Cinza escuro</td><td><code>#1e293b</code></td><td>Fundo da seção de Estatísticas</td></tr>
                    <tr><td style="background:#3b82f6;color:#fff;padding:4px 8px;border-radius:4px">Azul vibrante</td><td><code>#3b82f6</code></td><td>Botão Hero, números das estatísticas, fundo do CTA</td></tr>
                    <tr><td style="background:#f8fafc;padding:4px 8px;border-radius:4px;border:1px solid #ddd">Cinza muito claro</td><td><code>#f8fafc</code></td><td>Fundo dos cards de Serviços e Depoimentos</td></tr>
                    <tr><td style="background:#ffffff;padding:4px 8px;border-radius:4px;border:1px solid #ddd">Branco</td><td><code>#ffffff</code></td><td>Fundo de Serviços, Equipe e cards de Depoimentos</td></tr>
                    <tr><td style="background:#94a3b8;color:#fff;padding:4px 8px;border-radius:4px">Cinza médio</td><td><code>#94a3b8</code></td><td>Textos secundários (descrições, estatísticas)</td></tr>
                    <tr><td style="background:#64748b;color:#fff;padding:4px 8px;border-radius:4px">Cinza texto</td><td><code>#64748b</code></td><td>Textos descritivos (serviços, equipe)</td></tr>
                    <tr><td style="background:#475569;color:#fff;padding:4px 8px;border-radius:4px">Cinza itálico</td><td><code>#475569</code></td><td>Textos de depoimento (itálico)</td></tr>
                    <tr><td style="background:#bfdbfe;padding:4px 8px;border-radius:4px">Azul claro</td><td><code>#bfdbfe</code></td><td>Texto descritivo do CTA</td></tr>
                </table>
            </div>
        </section>

        {{-- PROJECT STRUCTURE --}}
        <section id="architecture" class="step">
            <h2>15. Arquitetura do Projeto</h2>
            <div class="step-body">
                <p>O projeto segue o padrão MVC do Laravel com uma camada adicional de <strong>Serviços</strong> e <strong>Widgets</strong> para isolar a lógica do page builder.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Diagrama de Arquitetura</h3>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.78rem;line-height:1.5;overflow-x:auto;font-family:monospace">
<pre style="margin:0">┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND (6 módulos ES)                   │
│  resources/js/editor/ ← editor.blade.php (partials)            │
│  drag-drop, undo/redo, auto-save, inline editing, panels       │
├─────────────────────────────────────────────────────────────────┤
│                     CONTROLLERS (3 classes)                      │
│  PageController      — CRUD páginas, templates, export/import   │
│  ElementController   — CRUD elementos, controles, upload        │
│  RevisionController  — revisões, diff, restore, auto-save       │
│  FormController      — processamento de formulários             │
├─────────────────────────────────────────────────────────────────┤
│                     SERVICES (5 classes)                         │
│  PageBuilderService  — orquestra tudo (create, update, render)  │
│  ElementManager      — CRUD elementos, árvore, reordenação      │
│  WidgetManager       — registra e gerencia widgets dinamicamente│
│  Renderer            — renderiza árvore de elementos → HTML     │
│  TemplateManager     — 5 templates predefinidos                 │
├─────────────────────────────────────────────────────────────────┤
│                     WIDGETS (7 classes)                          │
│  BaseWidget (abstract) → HeadingWidget                          │
│                        → TextWidget                             │
│                        → ImageWidget                            │
│                        → ButtonWidget                           │
│                        → SectionWidget                          │
│                        → ColumnWidget                           │
├─────────────────────────────────────────────────────────────────┤
│                     DATABASE (SQLite)                            │
│  pages → elements (árvore via parent_id) → revisions            │
└─────────────────────────────────────────────────────────────────┘</pre>
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Árvore de Diretórios do Page Builder</h3>
                <table class="widget-table">
                    <tr><th>Caminho</th><th>Arquivos</th><th>Descrição</th></tr>
                    <tr><td><code>app/Http/Controllers/PageBuilder/</code></td><td>4</td><td>PageController, ElementController, RevisionController, FormController</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Core/</code></td><td>5</td><td>PageBuilderService, ElementManager, WidgetManager, Renderer, TemplateManager</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Widgets/</code></td><td>17</td><td>BaseWidget + 16 widgets concretos</td></tr>
                    <tr><td><code>app/Providers/</code></td><td>1</td><td>PageBuilderServiceProvider (registra singletons e rotas)</td></tr>
                    <tr><td><code>config/page-builder.php</code></td><td>1</td><td>Configuração: lista de widgets habilitados, config de cache</td></tr>
                    <tr><td><code>database/migrations/</code></td><td>3</td><td>pages, elements, revisions</td></tr>
                    <tr><td><code>routes/page-builder.php</code></td><td>1</td><td>35+ rotas (resource pages + elementos + revisões)</td></tr>
                    <tr><td><code>resources/views/</code></td><td>16</td><td>Blade views (editor, pages, auth, tutorial + 7 partials editor/)</td></tr>
                    <tr><td><code>resources/js/editor/</code></td><td>6</td><td>Módulos ES: state, utils, canvas, history, navigator, dragdrop</td></tr>
                    <tr><td><code>tests/Unit/</code></td><td>4</td><td>BaseWidgetTest, PageBuilderServiceTest, TemplateManagerTest, ExampleTest</td></tr>
                    <tr><td><code>tests/Feature/</code></td><td>4</td><td>PageControllerTest, ElementControllerTest, RevisionControllerTest, ExampleTest</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Injeção de Dependências (Provider)</h3>
                <p>O <code>PageBuilderServiceProvider</code> registra todos os serviços como <strong>singletons</strong> (uma instância compartilhada):</p>
                <ul>
                    <li><code>WidgetManager</code> — lê <code>config/page-builder.php</code> e registra os 17 widgets</li>
                    <li><code>ElementManager</code> — CRUD elementos, buildTree() para construir árvore a partir do banco</li>
                    <li><code>Renderer</code> — recebe WidgetManager, renderiza árvore de elementos → HTML</li>
                    <li><code>PageBuilderService</code> — orquestra os três serviços acima</li>
                    <li><code>TemplateManager</code> — singleton independente, sem dependências</li>
                </ul>
                <p>O provider também carrega rotas, views e config publicável.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Fluxo de Dados Principal</h3>
                <ol>
                    <li><strong>Criar página</strong> → <code>PageController::store()</code> → <code>PageBuilderService::createPage()</code> → insere na tabela <code>pages</code></li>
                    <li><strong>Abrir editor</strong> → <code>PageController::edit()</code> → renderiza <code>editor.blade.php</code> → JS busca <code>GET /elements</code></li>
                    <li><strong>Arrastar widget</strong> → JS envia <code>POST /elements</code> → <code>ElementController::store()</code> → insere na tabela <code>elements</code></li>
                    <li><strong>Editar configuração</strong> → JS envia <code>PUT /elements/{id}/settings</code> → atualiza JSON <code>settings</code></li>
                    <li><strong>Salvar</strong> → <code>PUT /pages/{id}</code> → <code>PageBuilderService::updatePage()</code> → cria <code>Revision</code></li>
                    <li><strong>Renderizar</strong> → <code>Renderer::render()</code> → percorre árvore recursivamente → gera HTML com estilos inline</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Sistema de Widgets</h3>
                <p>Cada widget é uma classe que implementa <code>WidgetInterface</code> e estende <code>BaseWidget</code>. Cada um define:</p>
                <ul>
                    <li><code>__construct()</code> — tipo, label, ícone, categorias, configurações padrão</li>
                    <li><code>controls()</code> — array de configurações editáveis no painel</li>
                    <li><code>render()</code> — gera o HTML de exibição final (front-end)</li>
                    <li><code>renderEditor()</code> — gera o HTML para o canvas do editor (com atributos <code>data-element-id</code>)</li>
                </ul>
                <p>O <code>WidgetManager</code> registra widgets dinamicamente a partir do arquivo de config, permitindo habilitar/desabilitar widgets sem modificar código.</p>
            </div>
        </section>

        {{-- DATABASE --}}
        <section id="database" class="step">
            <h2>16. Banco de Dados</h2>
            <div class="step-body">
                <p>O projeto usa <strong>SQLite</strong> por padrão (configurado em <code>.env</code> como <code>DB_CONNECTION=sqlite</code>), mas é compatível com MySQL e MariaDB. São 3 tabelas principais além das padrão do Laravel (users, cache, jobs):</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Tabela <code>pages</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Referência ao usuário dono (cascade delete)</td></tr>
                    <tr><td><code>title</code></td><td>string</td><td>Título da página</td></tr>
                    <tr><td><code>slug</code></td><td>string (unique)</td><td>Slug para URLs (gerado automaticamente)</td></tr>
                    <tr><td><code>status</code></td><td>string</td><td>"draft" ou "published" (padrão: draft)</td></tr>
                    <tr><td><code>content</code></td><td>longText (nullable)</td><td>Conteúdo HTML renderizado</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Configurações: container_width, page_background, content_padding, css_custom</td></tr>
                    <tr><td><code>meta_data</code></td><td>json (nullable)</td><td>Metadados (SEO, tags, etc.)</td></tr>
                    <tr><td><code>template</code></td><td>string (nullable)</td><td>Nome do template aplicado</td></tr>
                    <tr><td><code>created_at / updated_at</code></td><td>timestamps</td><td>Datas de criação e atualização</td></tr>
                    <tr><td><code>deleted_at</code></td><td>softDeletes</td><td>Exclusão lógica</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>elements</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>page_id</code></td><td>foreignId</td><td>Página pai (cascade delete)</td></tr>
                    <tr><td><code>parent_id</code></td><td>foreignId (nullable)</td><td>Elemento pai — cria a <strong>árvore</strong> (Section → Column → Widget)</td></tr>
                    <tr><td><code>uuid</code></td><td>uuid (unique)</td><td>Identificador único universal para cada elemento</td></tr>
                    <tr><td><code>type</code></td><td>string</td><td>Tipo do widget: heading, text, image, button, section, column</td></tr>
                    <tr><td><code>name</code></td><td>string</td><td>Nome exibido no editor (ex: "Heading", "Section")</td></tr>
                    <tr><td><code>order</code></td><td>integer</td><td>Ordem dentro do pai (padrão: 0)</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Configurações do widget (varia por tipo)</td></tr>
                    <tr><td><code>content</code></td><td>json (nullable)</td><td>Conteúdo (textos, URLs, etc.)</td></tr>
                    <tr><td><code>styles</code></td><td>json (nullable)</td><td>Estilos CSS personalizados</td></tr>
                    <tr><td><code>responsive_settings</code></td><td>json (nullable)</td><td>Configurações por breakpoint</td></tr>
                    <tr><td><code>animation</code></td><td>json (nullable)</td><td>Animações de entrada</td></tr>
                    <tr><td><code>effects</code></td><td>json (nullable)</td><td>Efeitos visuais</td></tr>
                    <tr><td><code>column_size</code></td><td>string</td><td>Largura da coluna (padrão: "col-12")</td></tr>
                    <tr><td><code>css_classes</code></td><td>json (nullable)</td><td>Classes CSS adicionais</td></tr>
                    <tr><td><code>css_id</code></td><td>string (nullable)</td><td>ID CSS personalizado</td></tr>
                    <tr><td><code>deleted_at</code></td><td>softDeletes</td><td>Exclusão lógica</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Árvore de Elementos:</strong> A estrutura hierárquica é formada pelo campo <code>parent_id</code>. Uma Seção contém Colunas, que contêm widgets. Exemplo: Section (parent_id=null) → Column (parent_id=section.id) → Heading (parent_id=column.id).
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela <code>revisions</code></h3>
                <table class="widget-table">
                    <tr><th>Coluna</th><th>Tipo</th><th>Descrição</th></tr>
                    <tr><td><code>id</code></td><td>bigint (PK)</td><td>ID auto-increment</td></tr>
                    <tr><td><code>page_id</code></td><td>foreignId</td><td>Página revisada (cascade delete)</td></tr>
                    <tr><td><code>user_id</code></td><td>foreignId</td><td>Usuário que fez a revisão</td></tr>
                    <tr><td><code>version</code></td><td>string(20)</td><td>Versão da revisão (ex: "1.0", "2.3")</td></tr>
                    <tr><td><code>label</code></td><td>string (nullable)</td><td>Label descritivo (ex: "Versão inicial")</td></tr>
                    <tr><td><code>type</code></td><td>string</td><td>"manual" ou "auto" (padrão: manual)</td></tr>
                    <tr><td><code>content</code></td><td>longText (nullable)</td><td>Snapshot do conteúdo HTML da página</td></tr>
                    <tr><td><code>settings</code></td><td>json (nullable)</td><td>Snapshot das configurações da página</td></tr>
                    <tr><td><code>meta_data</code></td><td>json (nullable)</td><td>Snapshot dos metadados</td></tr>
                    <tr><td><code>diff</code></td><td>json (nullable)</td><td>Diff em relação à revisão anterior</td></tr>
                </table>
            </div>
        </section>

        {{-- ROUTES --}}
        <section id="routes" class="step">
            <h2>17. Rotas</h2>
            <div class="step-body">
                <p>Todas as rotas do page builder ficam em <code>routes/page-builder.php</code>, protegidas pelo middleware <code>web</code> + <code>auth</code>, com prefixo <code>/page-builder</code>.</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Rotas de Páginas</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages</code></td><td>PageController@index</td><td>Listar páginas</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/create</code></td><td>PageController@create</td><td>Formulário de criação</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages</code></td><td>PageController@store</td><td>Criar página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/editor</code></td><td>PageController@edit</td><td>Abrir editor visual</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/pages/{id}</code></td><td>PageController@update</td><td>Atualizar página</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/pages/{id}</code></td><td>PageController@destroy</td><td>Excluir página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/render</code></td><td>PageController@render</td><td>Renderizar HTML da página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/data</code></td><td>PageController@getData</td><td>Dados JSON da página</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/publish</code></td><td>PageController@publish</td><td>Publicar</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/unpublish</code></td><td>PageController@unpublish</td><td>Despublicar</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/duplicate</code></td><td>PageController@duplicate</td><td>Duplicar página</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/export</code></td><td>PageController@export</td><td>Exportar JSON</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/import</code></td><td>PageController@import</td><td>Importar JSON</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/templates</code></td><td>PageController@listTemplates</td><td>Listar templates</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/apply-template</code></td><td>PageController@applyTemplate</td><td>Aplicar template</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/pages/{id}/layout</code></td><td>PageController@updateLayout</td><td>Atualizar layout</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Elementos</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/elements</code></td><td>ElementController@index</td><td>Listar elementos da página</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements</code></td><td>ElementController@store</td><td>Criar elemento</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/elements/{id}</code></td><td>ElementController@show</td><td>Ver elemento</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/elements/{id}</code></td><td>ElementController@update</td><td>Atualizar elemento</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/elements/{id}</code></td><td>ElementController@destroy</td><td>Excluir elemento</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/elements/{id}/duplicate</code></td><td>ElementController@duplicate</td><td>Duplicar elemento</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/elements/reorder</code></td><td>ElementController@reorder</td><td>Reordenar elementos</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/elements/{id}/move</code></td><td>ElementController@move</td><td>Mover elemento</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/elements/{id}/settings</code></td><td>ElementController@updateSettings</td><td>Atualizar configurações</td></tr>
                    <tr><td>PUT</td><td><code>/page-builder/elements/{id}/styles</code></td><td>ElementController@updateStyles</td><td>Atualizar estilos</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/elements/{id}/render</code></td><td>ElementController@renderElement</td><td>Renderizar elemento</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/elements/{id}/controls</code></td><td>ElementController@controls</td><td>Controles do elemento</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/widgets/{type}/controls</code></td><td>ElementController@widgetControls</td><td>Controles por tipo de widget</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/upload</code></td><td>ElementController@uploadImage</td><td>Upload de imagem</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Rotas de Revisões</h3>
                <table class="widget-table">
                    <tr><th>Método</th><th>URL</th><th>Controller</th><th>Descrição</th></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/revisions</code></td><td>RevisionController@index</td><td>Listar revisões</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/revisions/{id}</code></td><td>RevisionController@show</td><td>Ver revisão</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/revisions/{id}/restore</code></td><td>RevisionController@restore</td><td>Restaurar revisão</td></tr>
                    <tr><td>GET</td><td><code>/page-builder/pages/{id}/revisions/{id}/diff</code></td><td>RevisionController@diff</td><td>Ver diff</td></tr>
                    <tr><td>DELETE</td><td><code>/page-builder/revisions/{id}</code></td><td>RevisionController@destroy</td><td>Excluir revisão</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/revisions/prune</code></td><td>RevisionController@prune</td><td>Limpar revisões antigas</td></tr>
                    <tr><td>POST</td><td><code>/page-builder/pages/{id}/revisions/auto-save</code></td><td>RevisionController@autoSave</td><td>Auto-save (a cada 60s)</td></tr>
                </table>
            </div>
        </section>

        {{-- QUALITY --}}
        <section id="quality" class="step">
            <h2>18. Qualidade &amp; Testes</h2>
            <div class="step-body">
                <p>O projeto tem uma suíte completa de testes automatizados com <strong>93 testes</strong> rodando em SQLite em memória.</p>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Resumo da Qualidade</h3>
                <table class="widget-table">
                    <tr><th>Métrica</th><th>Valor</th><th>Avaliação</th></tr>
                    <tr><td>Testes totais</td><td>93</td><td style="color:green">Boa cobertura</td></tr>
                    <tr><td>Testes unitários</td><td>45</td><td style="color:green">Widgets, Services, Templates</td></tr>
                    <tr><td>Testes de feature</td><td>48</td><td style="color:green">Controllers, Auth, Fluxos</td></tr>
                    <tr><td>Cobertura de controllers</td><td>3/3</td><td style="color:green">100% dos controllers</td></tr>
                    <tr><td>Cobertura de widgets</td><td>17/17</td><td style="color:green">BaseWidget + todos os 16 widgets</td></tr>
                    <tr><td>Sanitização de XSS</td><td>Implementada</td><td style="color:green">PageBuilderService::sanitizeContent()</td></tr>
                    <tr><td>Autorização (Policy)</td><td>Implementada</td><td style="color:green">PagePolicy em PageController</td></tr>
                    <tr><td>Tratamento de erros JS</td><td>14 fetch()</td><td style="color:green">Todos com .catch() + toast</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Testes Unitários (45)</h3>
                <table class="widget-table">
                    <tr><th>Classe de Teste</th><th>Testes</th><th>Cobre</th></tr>
                    <tr><td><code>BaseWidgetTest</code></td><td>13</td><td>Getters, validação, prepareSettings, sanitização, isContainer, isDynamic</td></tr>
                    <tr><td><code>PageBuilderServiceTest</code></td><td>19</td><td>Create, update, sanitize, elements, revisions, export/import, render</td></tr>
                    <tr><td><code>TemplateManagerTest</code></td><td>12</td><td>Listagem, aplicação, importação, edge cases, estrutura de templates</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Testes de Feature (48)</h3>
                <table class="widget-table">
                    <tr><th>Classe de Teste</th><th>Testes</th><th>Cobre</th></tr>
                    <tr><td><code>PageControllerTest</code></td><td>24</td><td>CRUD, auth, publish, templates, export/import, duplicate</td></tr>
                    <tr><td><code>ElementControllerTest</code></td><td>15</td><td>CRUD, auth, reorder, move, settings, styles, controls</td></tr>
                    <tr><td><code>RevisionControllerTest</code></td><td>9</td><td>List, show, restore, diff, delete, prune, auto-save, auth</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Como Rodar os Testes</h3>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:.75rem 1rem;border-radius:6px;font-size:.85rem;font-family:monospace;margin:.75rem 0">
<pre style="margin:0"># Rodar todos os testes
php artisan test

# Rodar apenas os testes unitários
php artisan test --testsuite=Unit

# Rodar com verbose
php artisan test --verbose</pre>
                </div>
            </div>
        </section>

        {{-- IMPROVEMENTS --}}
        <section id="improvements" class="step">
            <h2>19. Melhorias Propostas</h2>
            <div class="step-body">
                <p>O projeto já tem uma base sólida com 93 testes, sanitização XSS, autorização por Policy e tratamento de erros no JS. Abaixo estão as melhorias propostas, organizadas por prioridade.</p>

                <div class="tip">
                    <strong>&#128209; Plano Completo:</strong> O arquivo <code>IMPROVEMENTS.md</code> na raiz do projeto contém <strong>42 passos detalhados</strong> para implementar todas as melhorias, organizados em 8 fases. Cada passo pode ser testado individualmente. Siga a ordem recomendada no arquivo para implementar semana a semana.
                </div>

                <h3 style="font-size:1rem;margin-top:1rem;margin-bottom:.5rem">Alta Prioridade (Segurança &amp; Confiabilidade)</h3>
                <table class="widget-table">
                    <tr><th>#</th><th>Melhoria</th><th>Descrição</th><th>Impacto</th></tr>
                    <tr><td>1</td><td><strong>Rate Limiting</strong></td><td>Adicionar rate limiting nas rotas de API (save, upload) para evitar abuso</td><td>Segurança</td></tr>
                    <tr><td>2</td><td><strong>CSRF nas rotas API</strong></td><td>Verificar se todas as rotas POST/PUT/DELETE têm proteção CSRF (middleware web já aplica, mas validar)</td><td>Segurança</td></tr>
                    <tr><td>3</td><td><strong>Validação de Upload</strong></td><td>Adicionar validação de tipo MIME e tamanho no upload de imagens (atualmente aceita qualquer arquivo)</td><td>Segurança</td></tr>
                    <tr><td>4</td><td><strong>Soft Delete com Hard Limit</strong></td><td>Adicionar limpeza periódica de registros soft-deleted antigos (>30 dias) para evitar crescimento do banco</td><td>Performance</td></tr>
                    <tr><td>5</td><td><strong>Lock de Concorrência</strong></td><td>Usar <code>lockForUpdate()</code> ao salvar páginas com múltiplos usuários editando simultaneamente</td><td>Confiabilidade</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Média Prioridade (Funcionalidade)</h3>
                <table class="widget-table">
                    <tr><th>#</th><th>Melhoria</th><th>Descrição</th><th>Impacto</th></tr>
                    <tr><td>6</td><td><s>Widget de Vídeo</s></td><td><s>Adicionar widget para incorporar vídeos do YouTube/Vimeo com preview no editor</s> — <strong>Implementado</strong> via botão YouTube no editor WYSIWYG do widget de texto</td><td>Funcionalidade</td></tr>
                    <tr><td>7</td><td><strong>Widget de Ícone</strong></td><td>Widget de ícones com biblioteca Font Awesome ou similar</td><td>Funcionalidade</td></tr>
                    <tr><td>8</td><td><strong>Widget de Divisor</strong></td><td>Linha horizontal com estilo configurável (cor, espessura, tipo)</td><td>Funcionalidade</td></tr>
                    <tr><td>9</td><td><strong>Widget de Espaçador</strong></td><td>Espaço em branco com altura configurável para respiração visual</td><td>Funcionalidade</td></tr>
                    <tr><td>10</td><td><strong>Galeria de Imagens</strong></td><td>Widget para exibir múltiplas imagens em grid ou carrossel</td><td>Funcionalidade</td></tr>
                    <tr><td>11</td><td><strong>Formulário de Contato</strong></td><td>Widget de formulário com campos configuráveis e envio por email</td><td>Funcionalidade</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Resumo do Plano (42 Passos)</h3>
                <table class="widget-table">
                    <tr><th>Fase</th><th>Passos</th><th>Descrição</th><th>Semana</th></tr>
                    <tr><td>1</td><td>1–8</td><td>Novos Widgets: Vídeo, Divisor, Espaçador, Ícone, Galeria, Form, Tabs, Accordion</td><td>1–2</td></tr>
                    <tr><td>2</td><td>9–13</td><td>Style Tab: Fundo, Borda, Tipografia, Hover, abas Content/Style/Advanced</td><td>3</td></tr>
                    <tr><td>3</td><td>14–17</td><td>Navigator: Árvore de elementos, drag-and-drop, rename, context menu</td><td>4</td></tr>
                    <tr><td>4</td><td>18–23</td><td>Controles Avançados: Margem/Padding, Z-Index, CSS custom, Animações, Responsividade</td><td>5</td></tr>
                    <tr><td>5</td><td>24–31</td><td>UX do Editor: Live preview, Ctrl+Z visual, context menu, zoom, fullscreen, search</td><td>6</td></tr>
                    <tr><td>6</td><td>32–35</td><td>Atalhos: Ctrl+D, Copy/Paste, Multi-select</td><td>7</td></tr>
                    <tr><td>7</td><td>36–42</td><td>Temas, Presets, Responsividade completa</td><td>8</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Baixa Prioridade (UX &amp; Performance)</h3>
                <table class="widget-table">
                    <tr><th>#</th><th>Melhoria</th><th>Descrição</th><th>Impacto</th></tr>
                    <tr><td>12</td><td><strong>Ctrl+Z Visual</strong></td><td>Botão de desfazer visível no editor (além do atalho de teclado)</td><td>UX</td></tr>
                    <tr><td>13</td><td><strong>Preview em Tempo Real</strong></td><td>Ao editar configurações, atualizar o canvas instantaneamente (atualmente requer click fora)</td><td>UX</td></tr>
                    <tr><td>14</td><td><strong>Drag Handle Melhorado</strong></td><td>Ícone de arrastar mais visível e área de drag maior para facilitar reordenação</td><td>UX</td></tr>
                    <tr><td>15</td><td><strong>Cache de Widgets</strong></td><td>Cache os controles de widgets no localStorage para carregamento mais rápido do editor</td><td>Performance</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Melhorias Já Implementadas</h3>
                <ul>
                    <li><strong>PagePolicy</strong> — autorização por dono da página</li>
                    <li><strong>Sanitização XSS</strong> — <code>sanitizeContent()</code> e <code>sanitizeSettings()</code></li>
                    <li><strong>Tratamento de erros JS</strong> — 14 chamadas fetch() com .catch()</li>
                    <li><strong>TemplateManager</strong> — extraído do controller para classe dedicada</li>
                    <li><strong>prepareSettings()</strong> — merge automático de defaults em todos os 17 widgets</li>
                    <li><strong>Editor JS extraído</strong> — 2600+ linhas em arquivo separado para cache</li>
                    <li><strong>JS decomposto em módulos ES</strong> — 6 módulos: state, utils, canvas, history, navigator, dragdrop (via Vite pipeline)</li>
                    <li><strong>Editor Blade partials</strong> — 507 linhas split em 7 partials reutilizáveis (css, toolbar, widget-panel, canvas, settings-panel, navigator, scripts)</li>
                    <li><strong>Lógica duplicada extraída</strong> — buildTree() consolidado no ElementManager; controllers injetam ElementManager via DI</li>
                    <li><strong>FormSubmission model</strong> — model Eloquent dedicado para envios de formulário; FormController refatorado para usar Eloquent ao invés de DB::table()</li>
                    <li><strong>JS decomposto em módulos ES</strong> — 6 módulos: state, utils, canvas, history, navigator, dragdrop (via Vite pipeline)</li>
                    <li><strong>Editor Blade partials</strong> — 507 linhas split em 7 partials reutilizáveis (css, toolbar, widget-panel, canvas, settings-panel, navigator, scripts)</li>
                    <li><strong>Lógica duplicada extraída</strong> — buildTree() consolidado no ElementManager; controllers injetam ElementManager via DI</li>
                    <li><strong>FormSubmission model</strong> — model Eloquent dedicado para envios de formulário; FormController refatorado para usar Eloquent ao invés de DB::table()</li>
                    <li><strong>93 testes</strong> — cobertura completa de controllers, services e widgets</li>
                    <li><strong>Bug fixes</strong> — status default, max order nulo, config key, AuthorizesRequests</li>
                    <li><strong>Editor WYSIWYG</strong> — widget de texto com toolbar rich-text (negrito, itálico, links, imagens, vídeos, listas)</li>
                    <li><strong>Upload de imagens no texto</strong> — botão de imagem + colar (Ctrl+V) no editor WYSIWYG</li>
                    <li><strong>Vídeos YouTube</strong> — botão na toolbar que converte URL em embed responsivo com privacidade</li>
                    <li><strong>Edição inline corrigida</strong> — preserva HTML (innerHTML) ao invés de destruir com textContent</li>
                    <li><strong>Widget de Vídeo</strong> — <em>Passo 1 do IMPROVEMENTS.md</em> — widget dedicado com suporte a YouTube (youtube-nocookie.com), Vimeo e URL customizada; controles de autoplay, loop, mute, start/end time, aspect ratio e alinhamento; preview responsivo no canvas</li>
                    <li><strong>Widget de Divisor</strong> — <em>Passo 2 do IMPROVEMENTS.md</em> — linha horizontal com estilo (solid, dashed, dotted, double), largura, espessura, cor e espaçamento superior/inferior configuráveis</li>
                    <li><strong>Widget de Espaçador</strong> — <em>Passo 3 do IMPROVEMENTS.md</em> — espaço em branco com altura configurável (0–500px); preview com faixa tracejada e indicação de pixels no editor</li>
                    <li><strong>Widget de Ícone</strong> — <em>Passo 4 do IMPROVEMENTS.md</em> — biblioteca de 90+ ícones Font Awesome com grid de seleção, busca, cor, tamanho, alinhamento e link; novo tipo de controle <code>icon</code> no editor</li>
                    <li><strong>Widget de Galeria</strong> — <em>Passo 5 do IMPROVEMENTS.md</em> — upload múltiplo com modal de seleção, grid 1–6 colunas, layout masonry, captions, border radius, drag-to-reorder; novo tipo de controle <code>gallery</code> no editor</li>
                    <li><strong>Widget de Formulário</strong> — <em>Passo 6 do IMPROVEMENTS.md</em> — campos configuráveis (text, email, tel, number, textarea, select, checkbox, radio) com controles repeater; submit via AJAX; migration <code>form_submissions</code>; rotas de envio e listagem</li>
                    <li><strong>Widget de Abas</strong> — <em>Passo 7 do IMPROVEMENTS.md</em> — abas com título e conteúdo rich-text; orientação horizontal/vertical; troca de aba com JS; cor ativa configurável</li>
                    <li><strong>Widget de Accordion</strong> — <em>Passo 8 do IMPROVEMENTS.md</em> — itens expandíveis/colapsáveis com título e conteúdo; ícone de seta; open by default; animation de rotação; estilização por cor</li>
                    <li><strong>Style Tab — Abas Content/Style/Advanced</strong> — <em>Passos 9–12 do IMPROVEMENTS.md</em> — painel direito com 3 abas; controles de Background (cor, imagem, posição, size, repeat), Border (width, color, radius, style), Box Shadow (horizontal, vertical, blur, spread, color), Typography (font family, size, weight, line height, letter spacing, text transform, color), e Advanced (dimensions padding/margin, CSS classes, CSS ID). Todos os 17 widgets atualizados com abas Style e Advanced.</li>
                    <li><strong>Style Tab — Hover Effects</strong> — <em>Passo 13 do IMPROVEMENTS.md</em> — controles de hover (background color, text color, border color, transform, transition) para Button, Heading e Text; gera <code>&lt;style&gt;</code> tag inline com escopo por classe; transição configurável em ms</li>
                    <li><strong>Navigator</strong> — <em>Passos 14–17 do IMPROVEMENTS.md</em> — painel flutuante no canto inferior direito com árvore de elementos; toggle show/hide; highlight no hover e click; drag &amp; drop para reordenar; duplo clique para renomear; menu de contexto (right-click) com Duplicate, Delete, Copy, Paste, Move Up/Down</li>
                    <li><strong>Advanced Tab — Dimensions com Link</strong> — <em>Passo 18 do IMPROVEMENTS.md</em> — controle de margem/padding com grid visual 4 lados (Top/Right/Bottom/Left); botão de link/unlink (🔗/🔓) para sincronizar todos os lados; layout em grid 4 colunas com labels;</li>
                    <li><strong>Advanced Tab — Z-Index</strong> — <em>Passo 19 do IMPROVEMENTS.md</em> — controle numérico de Z-Index em todos os 17 widgets; controla empilhamento de camadas no layout</li>
                    <li><strong>Advanced Tab — CSS ID &amp; Classes</strong> — <em>Passo 20 do IMPROVEMENTS.md</em> — campos de CSS ID e CSS Classes em todos os 17 widgets; CSS ID gera <code>id=""</code> no wrapper; CSS Classes gera classes adicionais</li>
                    <li><strong>Advanced Tab — Custom CSS</strong> — <em>Passo 21 do IMPROVEMENTS.md</em> — editor de código monospace para CSS customizado por widget; gera <code>&lt;style&gt;</code> tag com seletor automático (<code>#css-id</code> ou <code>[data-element-id]</code>); todos os 17 widgets</li>
                    <li><strong>Advanced Tab — Animações de Entrada</strong> — <em>Passo 22 do IMPROVEMENTS.md</em> — seletor com 20 animações (fadeIn, slideInUp, zoomIn, bounceIn, etc.); duração (slow/normal/fast); delay em ms; carrega Animate.css via CDN; suporte inline no Moodle via <code>data-element-id</code></li>
                    <li><strong>Advanced Tab — Responsividade por Widget</strong> — <em>Passo 23 do IMPROVEMENTS.md</em> — toggle de visibilidade por dispositivo (Desktop/Tablet/Mobile); gera <code>@media</code> queries com <code>display:none</code> no render público; no editor, widgets ocultos aparecem com opacity reduzida</li>
                </ul>
            </div>
        </section>

        {{-- MOODLE --}}
        {{-- MOODLE --}}
        <section id="moodle" class="step">
            <h2>20. Uso com Moodle 4.5+</h2>
            <div class="step-body">
                <p>O Page Builder pode ser integrado ao <strong>Moodle 4.5+</strong> para criar páginas ricas dentro da sua plataforma de aprendizado. Abaixo estão as instruções detalhadas.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.1 Copiar HTML para o Moodle (recomendado)</h3>
                <ol>
                    <li>Crie sua página no Page Builder normalmente (use um modelo pronto ou comece do zero). Os modelos são fixos e nunca são alterados ao criar uma nova página — o sistema faz uma cópia dos dados para a página nova.</li>
                    <li>No editor visual, clique no botão <strong>"Copy HTML"</strong> (ícone de clipboard) na barra de ferramentas. Uma notificação "HTML copiado!" será exibida.</li>
                    <li>O HTML renderizado da página (com CSS inline) está na sua área de transferência.</li>
                    <li>No Moodle, edite um recurso do tipo <strong>Página (Page)</strong> ou <strong>Livro (Book)</strong>.</li>
                    <li>No editor do Moodle, clique no botão <strong>&lt;&gt;</strong> (Mostrar código fonte / HTML) e cole o conteúdo com <kbd>Ctrl+V</kbd>.</li>
                    <li>Salve a página. O conteúdo será exibido com todos os estilos preservados.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.2 Exportar como JSON (backup ou transferência entre instalações)</h3>
                <ol>
                    <li>Na lista de páginas do Page Builder, clique em <strong>"Export"</strong> na página desejada. Um arquivo <code>.json</code> será baixado com toda a estrutura da página.</li>
                    <li>Para importar em outra instalação do Page Builder, clique em <strong>"Import"</strong> na lista de páginas, faça upload do arquivo <code>.json</code> e a página será recriada.</li>
                    <li>Após importar, abra a página no editor e use o <strong>"Copy HTML"</strong> (passo 20.1) para colar o conteúdo no Moodle.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.3 Renderização como Página Moodle (iframe)</h3>
                <ol>
                    <li>O Page Builder pode ser incorporado ao Moodle como um <strong>recurso externo</strong> ou via <strong>iframe</strong>, apontando para a URL pública da página renderizada.</li>
                    <li>Use o parâmetro <code>?format=inner</code> na URL de renderização (<code>/page-builder/pages/{id}/render?format=inner</code>) para obter apenas o HTML do conteúdo, sem a estrutura completa da página.</li>
                    <li>Você também pode usar <code>?format=inner&theme=none</code> para um HTML ainda mais limpo, apenas com os elementos e estilos inline.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.4 Widgets Educacionais no Moodle</h3>
                <p>Os widgets educacionais (Callout, Table, Math) são especialmente úteis para criar conteúdo de cursos no Moodle:</p>
                <ul>
                    <li><strong>Callout:</strong> Caixas de destaque para definições, teoremas, exercícios, avisos e dicas. Cada tipo tem cores e ícones automáticos. O HTML renderizado mantém todos os estilos inline, compatível com o Moodle.</li>
                    <li><strong>Table:</strong> Tabelas estilizadas para dados, comparativos e exercícios. O CSS inline garante que as bordas e cores funcionem no editor do Moodle.</li>
                    <li><strong>Math (LaTeX):</strong> Fórmulas matemáticas renderizadas via KaTeX. No Moodle, a fórmula é salva como HTML estático com SVG/PNG, sem precisar do KaTeX no Moodle. Para fórmulas inline no texto, use o botão &#945; no editor WYSIWYG.</li>
                </ul>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Para cursos de matemática, combine os três widgets: use <strong>Callout (type=theorem)</strong> para enunciar teoremas, <strong>Math</strong> para as fórmulas em destaque, e <strong>Callout (type=exercise)</strong> para propostas de exercícios. Insira tabelas com <strong>Table</strong> para organizar dados e comparativos.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.5 Dicas para Moodle</h3>
                <ul>
                    <li><strong>Estilos inline:</strong> Todo o CSS gerado pelo Page Builder é inline (atributo <code>style</code>), garantindo compatibilidade máxima com o editor do Moodle — nenhum plugin adicional é necessário.</li>
                    <li><strong>Imagens:</strong> Use URLs públicas para imagens (ex.: placehold.co ou imagens hospedadas). Imagens enviadas para <code>storage/app/public/</code> no Page Builder não serão acessíveis pelo Moodle.</li>
                    <li><strong>Imagens no texto:</strong> O editor de texto (WYSIWYG) permite inserir imagens diretamente no conteúdo. Faça upload pela toolbar ou cole (Ctrl+V). As imagens ficam com CSS inline, compatíveis com o Moodle.</li>
                    <li><strong>Vídeos YouTube:</strong> Na toolbar do editor de texto, clique no botão <strong>&#9654;</strong> (vermelho) e cole a URL do vídeo. O embed é inserido como <code>&lt;iframe&gt;</code> com privacidade ativada (<code>youtube-nocookie.com</code>), recomendado pelo Moodle.</li>
                    <li><strong>Fórmulas matemáticas:</strong> O Page Builder usa KaTeX para renderizar LaTeX no editor. Ao copiar o HTML, as fórmulas são salvas como HTML estático — o Moodle não precisa de KaTeX instalado. Para fórmulas inline no parágrafo, use o botão &#945; no editor WYSIWYG.</li>
                    <li><strong>Responsividade:</strong> O HTML gerado mantém a responsividade. Teste em diferentes dispositivos após colar no Moodle.</li>
                    <li><strong>Limitação de largura:</strong> O Moodle pode aplicar estilos próprios de container. Use o parâmetro <code>?format=inner</code> para obter apenas o conteúdo bruto e ajuste margens no Moodle se necessário.</li>
                </ul>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O fluxo mais comum é: crie a página no Page Builder &rarr; clique em "Copy HTML" &rarr; cole no Moodle no modo HTML. Simples e rápido!
                </div>
            </div>
        </section>

        <div class="tutorial-footer">
            <p>Pronto! Você está pronto para criar páginas com o construtor visual.</p>
            <a href="{{ route('page-builder.pages.create') }}" class="btn btn-primary">Criar sua Primeira Página &rarr;</a>
        </div>
    </div>

    <style>
        .tutorial { max-width: 920px; }
        .tutorial h1 { font-size: 1.8rem; }
        .toc { background: #fff; border: 1px solid #ddd; border-radius: 8px; padding: 1.2rem 1.5rem; margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: .5rem 1.2rem; align-items: center; }
        .toc strong { font-size: .85rem; }
        .toc a { font-size: .85rem; color: #007bff; text-decoration: none; }
        .toc a:hover { text-decoration: underline; }
        .step { background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); padding: 1.5rem 2rem; margin-bottom: 1.5rem; }
        .step h2 { font-size: 1.25rem; margin-bottom: .75rem; padding-bottom: .5rem; border-bottom: 2px solid #007bff; display: inline-block; }
        .step-body { font-size: .95rem; line-height: 1.6; }
        .step-body p { margin-bottom: .75rem; }
        .step-body ul, .step-body ol { margin-bottom: .75rem; padding-left: 1.5rem; }
        .step-body li { margin-bottom: .3rem; }
        .step-body table { margin: .75rem 0; }
        .widget-table { width: 100%; border-collapse: collapse; font-size: .9rem; }
        .widget-table th, .widget-table td { padding: .5rem .75rem; border: 1px solid #eee; text-align: left; vertical-align: top; }
        .widget-table th { background: #f8f9fa; font-weight: 600; }
        .tip { background: #e7f3ff; border-left: 4px solid #007bff; padding: .75rem 1rem; border-radius: 4px; font-size: .88rem; margin: .75rem 0; }
        .panel-layout-ill { display: flex; gap: .5rem; margin: .75rem 0; }
        .panel-ill { flex: 1; text-align: center; padding: 1rem .5rem; border-radius: 6px; font-size: .8rem; }
        .panel-ill.left { background: #1e1e2d; color: #fff; }
        .panel-ill.center { background: #fff; border: 2px solid #333; color: #333; }
        .panel-ill.right { background: #f0f0f0; color: #333; }
        .ill-flow { display: flex; align-items: center; gap: .5rem; flex-wrap: wrap; background: #f8f9fa; padding: .75rem 1rem; border-radius: 6px; font-size: .85rem; }
        .ill-flow span { white-space: nowrap; }
        .ill-flow span:nth-child(even) { font-size: 1.2rem; color: #007bff; }
        .ill-preview { margin: .75rem 0; }
        kbd { background: #f0f0f0; border: 1px solid #ccc; border-radius: 3px; padding: .1rem .4rem; font-size: .8rem; font-family: inherit; }
        .tutorial-footer { text-align: center; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .tutorial-footer p { margin-bottom: 1rem; font-size: 1.05rem; }
    </style>
@endsection
