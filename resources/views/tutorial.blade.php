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
                    <tr><td><strong>PHP</strong></td><td>8.1+</td><td><code>php -v</code></td></tr>
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
                    <tr><td>Widgets disponíveis</td><td>9 (Título, Texto, Imagem, Botão, Seção, Coluna, Callout, Table, Math)</td></tr>
                    <tr><td>Templates prontos</td><td>5 (Blank, Landing, About, Contact, Showcase Completo)</td></tr>
                    <tr><td>Rotas definidas</td><td>35+ (CRUD páginas, elementos, revisões, templates)</td></tr>
                    <tr><td>Testes automatizados</td><td>93 (45 unitários + 48 de feature)</td></tr>
                    <tr><td>Tabelas no banco</td><td>3 principais (pages, elements, revisions)</td></tr>
                    <tr><td>Views Blade</td><td>9 (login, register, tutorial, editor, pages)</td></tr>
                    <tr><td>Linhas de JS do editor</td><td>~900</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Funcionalidades</h3>
                <ul>
                    <li>Criar páginas com título e status (rascunho / publicado)</li>
                    <li>Abrir um <strong>editor visual</strong> em tela cheia com tema escuro</li>
                    <li>Arrastar <strong>9 widgets</strong> (Título, Texto, Imagem, Botão, Seção, Coluna, Callout, Table, Math)</li>
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
                    <li>Integração com <strong>Moodle 4.5+</strong> via HTML renderizado</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Arquitetura Resumida</h3>
                <div style="background:#1e1e2d;color:#a6e3a1;padding:1rem;border-radius:6px;font-size:.8rem;line-height:1.6;overflow-x:auto;font-family:monospace">
<pre style="margin:0">┌──────────────────────────────────────────────────┐
│                  FRONTEND                         │
│   editor.blade.php + page-builder-editor.js       │
│   (900+ linhas JS vanilla, sem frameworks)         │
├──────────────────────────────────────────────────┤
│                CONTROLLERS                        │
│   PageController │ ElementController              │
│   RevisionController                              │
├──────────────────────────────────────────────────┤
│                 SERVICES                          │
│   PageBuilderService │ Renderer │ WidgetManager   │
│   TemplateManager │ ElementManager                │
├──────────────────────────────────────────────────┤
│                  WIDGETS                          │
│   BaseWidget → Heading │ Text │ Image │ Button    │
│              Section │ Column                     │
│              Callout │ Table │ Math               │
├──────────────────────────────────────────────────┤
│              DATABASE (SQLite)                    │
│   pages → elements (árvore) → revisions           │
└──────────────────────────────────────────────────┘</pre>
                </div>
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
                    <li>Lista os 9 widgets disponíveis: Título, Texto, Imagem, Botão, Seção, Coluna, Callout, Table, Math</li>
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
                            <li><strong>Cor de fundo:</strong> <code>#0f172a</code></li>
                            <li><strong>Padding superior:</strong> <code>120px</code></li>
                            <li><strong>Padding inferior:</strong> <code>120px</code></li>
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
                            <li><strong>Cor:</strong> <code>#ffffff</code> (branco)</li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Peso da fonte:</strong> <code>800</code></li>
                            <li><strong>Margem inferior:</strong> <code>24px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste um widget Texto</strong> para dentro da Coluna (abaixo do título). Configure:
                        <ul>
                            <li><strong>Conteúdo:</strong> <code>"Criamos soluções inovadoras que combinam design moderno, tecnologia de ponta e performance excepcional para impulsionar o seu negócio."</code></li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Tamanho da fonte:</strong> <code>20px</code></li>
                            <li><strong>Altura da linha:</strong> <code>1.8</code></li>
                            <li><strong>Margem inferior:</strong> <code>40px</code></li>
                        </ul>
                        <div class="tip">
                            <strong>&#128161; Dica de estilo:</strong> Para o estilo exato como no template real, adicione no conteúdo HTML: <code>&lt;p style="font-size:1.25rem;color:#94a3b8;max-width:700px;margin:0 auto;"&gt;...&lt;/p&gt;</code>
                        </div>
                    </li>
                    <li><strong>Arraste um widget Botão</strong> para dentro da Coluna (abaixo do texto). Configure:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Comece Agora"</code></li>
                            <li><strong>Link:</strong> <code>#</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#3b82f6</code> (azul)</li>
                            <li><strong>Cor do texto:</strong> <code>#ffffff</code> (branco)</li>
                            <li><strong>Tamanho:</strong> <code>large</code></li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Border Radius:</strong> <code>50px</code> (formato pílula)</li>
                            <li><strong>Padding esquerda/direita:</strong> <code>40px</code></li>
                            <li><strong>Peso da fonte:</strong> <code>600</code></li>
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
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code></li>
                            <li><strong>Padding superior:</strong> <code>100px</code></li>
                            <li><strong>Padding inferior:</strong> <code>100px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção. Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code> (largura total)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Padding inferior:</strong> <code>20px</code></li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste um <strong>Título</strong> e um <strong>Texto</strong>:
                        <ul>
                            <li><strong>Título:</strong> <code>"Nossos Serviços"</code>, tag <code>H2</code>, tamanho <code>xl</code>, cor <code>#0f172a</code>, peso <code>700</code>, margem inferior <code>16px</code></li>
                            <li><strong>Texto:</strong> <code>"Oferecemos um conjunto completo de soluções para transformar sua presença digital"</code>, tamanho <code>18px</code></li>
                        </ul>
                        <div class="tip">
                            <strong>&#128161; Dica:</strong> Para o texto ficar cinza e centralizado, use no HTML: <code>&lt;p style="color:#64748b;max-width:600px;margin:0 auto;"&gt;...&lt;/p&gt;</code>
                        </div>
                    </li>
                    <li><strong>Arraste uma nova Coluna</strong> para dentro da mesma Seção (abaixo da coluna de cabeçalho). Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-4</code> (um terço da largura)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#f8fafc</code></li>
                            <li><strong>Padding superior:</strong> <code>40px</code></li>
                            <li><strong>Padding inferior:</strong> <code>40px</code></li>
                            <li><strong>Border Radius:</strong> <code>12px</code></li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste um <strong>Título</strong> e um <strong>Texto</strong>:
                        <ul>
                            <li><strong>Título:</strong> <code>"Design Moderno"</code>, tag <code>H3</code>, tamanho <code>medium</code>, cor <code>#0f172a</code>, peso <code>600</code>, margem inferior <code>12px</code></li>
                            <li><strong>Texto:</strong> <code>"Interfaces elegantes e intuitivas criadas com as melhores práticas de UX/UI."</code>, tamanho <code>15px</code>, altura da linha <code>1.7</code></li>
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
                            <li><strong>Cor de fundo:</strong> <code>#1e293b</code></li>
                            <li><strong>Padding superior:</strong> <code>80px</code></li>
                            <li><strong>Padding inferior:</strong> <code>80px</code></li>
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
                            <li><strong>Título:</strong> <code>"500+"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code> (azul), peso <code>800</code>, margem inferior <code>8px</code></li>
                            <li><strong>Texto:</strong> <code>"Projetos Entregues"</code>, cor <code>#94a3b8</code></li>
                        </ul>
                    </li>
                    <li><strong>Na segunda Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"98%"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code>, peso <code>800</code>, margem inferior <code>8px</code></li>
                            <li><strong>Texto:</strong> <code>"Satisfação dos Clientes"</code>, cor <code>#94a3b8</code></li>
                        </ul>
                    </li>
                    <li><strong>Na terceira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"50+"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code>, peso <code>800</code>, margem inferior <code>8px</code></li>
                            <li><strong>Texto:</strong> <code>"Profissionais"</code>, cor <code>#94a3b8</code></li>
                        </ul>
                    </li>
                    <li><strong>Na quarta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"12+"</code>, tag <code>H2</code>, tamanho <code>xxl</code>, cor <code>#3b82f6</code>, peso <code>800</code>, margem inferior <code>8px</code></li>
                            <li><strong>Texto:</strong> <code>"Anos de Experiência"</code>, cor <code>#94a3b8</code></li>
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
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code></li>
                            <li><strong>Padding superior:</strong> <code>100px</code></li>
                            <li><strong>Padding inferior:</strong> <code>100px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção. Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code></li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Padding inferior:</strong> <code>30px</code></li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Nossa Equipe"</code>, tag <code>H2</code>, tamanho <code>xl</code>, cor <code>#0f172a</code>, peso <code>700</code>, margem inferior <code>16px</code></li>
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
                            <li><strong>Título:</strong> <code>"Ana Silva"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code>, peso <code>700</code>, margem inferior <code>4px</code></li>
                            <li><strong>Texto:</strong> <code>"CEO & Fundadora"</code>, cor <code>#64748b</code></li>
                        </ul>
                    </li>
                    <li><strong>Na segunda Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Carlos Oliveira"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code>, peso <code>700</code>, margem inferior <code>4px</code></li>
                            <li><strong>Texto:</strong> <code>"CTO"</code>, cor <code>#64748b</code></li>
                        </ul>
                    </li>
                    <li><strong>Na terceira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Marina Costa"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code>, peso <code>700</code>, margem inferior <code>4px</code></li>
                            <li><strong>Texto:</strong> <code>"Head de Design"</code>, cor <code>#64748b</code></li>
                        </ul>
                    </li>
                    <li><strong>Na quarta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"Rafael Santos"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code>, peso <code>700</code>, margem inferior <code>4px</code></li>
                            <li><strong>Texto:</strong> <code>"Lead Developer"</code>, cor <code>#64748b</code></li>
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
                            <li><strong>Cor de fundo:</strong> <code>#f8fafc</code></li>
                            <li><strong>Padding superior:</strong> <code>100px</code></li>
                            <li><strong>Padding inferior:</strong> <code>100px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção. Configure:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-12</code></li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Padding inferior:</strong> <code>20px</code></li>
                        </ul>
                    </li>
                    <li><strong>Dentro desta Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Título:</strong> <code>"O Que Nossos Clientes Dizem"</code>, tag <code>H2</code>, tamanho <code>xl</code>, cor <code>#0f172a</code>, peso <code>700</code>, margem inferior <code>16px</code></li>
                            <li><strong>Texto:</strong> <code>"A satisfação dos nossos clientes é a nossa maior recompensa"</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste três Colunas</strong> para dentro da mesma Seção (abaixo da coluna de cabeçalho). Configure cada uma:
                        <ul>
                            <li><strong>Largura:</strong> <code>col-4</code> (33% cada)</li>
                            <li><strong>Alinhamento do texto:</strong> <code>center</code></li>
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code></li>
                            <li><strong>Padding superior:</strong> <code>40px</code></li>
                            <li><strong>Padding inferior:</strong> <code>40px</code></li>
                            <li><strong>Border Radius:</strong> <code>12px</code></li>
                        </ul>
                    </li>
                    <li><strong>Na primeira Coluna</strong>, arraste (nesta ordem):
                        <ul>
                            <li><strong>Texto:</strong> <code>"A equipe transformou completamente nossa presença online."</code>, cor <code>#475569</code>, estilo <em>itálico</em></li>
                            <li><strong>Título:</strong> <code>"João Mendes"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code>, peso <code>700</code></li>
                            <li><strong>Texto:</strong> <code>"CEO, TechStart"</code>, cor <code>#94a3b8</code></li>
                        </ul>
                    </li>
                    <li><strong>Na segunda Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Profissionalismo e qualidade excepcionais."</code>, cor <code>#475569</code>, estilo <em>itálico</em></li>
                            <li><strong>Título:</strong> <code>"Fernanda Lima"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code>, peso <code>700</code></li>
                            <li><strong>Texto:</strong> <code>"Diretora, InnovateLab"</code>, cor <code>#94a3b8</code></li>
                        </ul>
                    </li>
                    <li><strong>Na terceira Coluna</strong>, arraste:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Resultados incríveis em tempo recorde."</code>, cor <code>#475569</code>, estilo <em>itálico</em></li>
                            <li><strong>Título:</strong> <code>"Pedro Alves"</code>, tag <code>H4</code>, tamanho <code>small</code>, cor <code>#0f172a</code>, peso <code>700</code></li>
                            <li><strong>Texto:</strong> <code>"Fundador, WebPlus"</code>, cor <code>#94a3b8</code></li>
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
                            <li><strong>Cor de fundo:</strong> <code>#3b82f6</code> (azul)</li>
                            <li><strong>Padding superior:</strong> <code>80px</code></li>
                            <li><strong>Padding inferior:</strong> <code>80px</code></li>
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
                            <li><strong>Cor:</strong> <code>#ffffff</code> (branco)</li>
                            <li><strong>Peso:</strong> <code>700</code></li>
                            <li><strong>Margem inferior:</strong> <code>16px</code></li>
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
                            <li><strong>Cor de fundo:</strong> <code>#ffffff</code> (branco)</li>
                            <li><strong>Cor do texto:</strong> <code>#3b82f6</code> (azul — mesma cor do fundo da seção)</li>
                            <li><strong>Tamanho:</strong> <code>large</code></li>
                            <li><strong>Alinhamento:</strong> <code>center</code></li>
                            <li><strong>Border Radius:</strong> <code>50px</code> (formato pílula)</li>
                            <li><strong>Padding esquerda/direita:</strong> <code>40px</code></li>
                            <li><strong>Peso da fonte:</strong> <code>700</code></li>
                        </ul>
                    </li>
                </ol>

                <hr style="margin:1.5rem 0;border:none;border-top:1px solid #eee">

                <div class="tip">
                    <strong>&#128161; Dica Final:</strong> O template Showcase Completo é o melhor exemplo de construção avançada de páginas. Ele demonstra: <strong>layouts full_width e boxed</strong>, <strong>colunas com larguras variadas</strong> (col-3, col-4, col-12), <strong>cores escuras e claras alternadas</strong> para ritmo visual, e <strong>todos os tipos de widget</strong> (Título, Texto, Botão). Para ver exatamente como cada elemento está configurado, crie uma página com o template e abra no editor — clique em cada elemento para inspecionar suas configurações no painel direito.
                </div>

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
│                        FRONTEND (730 linhas JS)                 │
│  page-builder-editor.js ← editor.blade.php                     │
│  drag-drop, undo/redo, auto-save, inline editing, panels       │
├─────────────────────────────────────────────────────────────────┤
│                     CONTROLLERS (3 classes)                      │
│  PageController      — CRUD páginas, templates, export/import   │
│  ElementController   — CRUD elementos, controles, upload        │
│  RevisionController  — revisões, diff, restore, auto-save       │
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
                    <tr><td><code>app/Http/Controllers/PageBuilder/</code></td><td>3</td><td>PageController, ElementController, RevisionController</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Core/</code></td><td>5</td><td>PageBuilderService, ElementManager, WidgetManager, Renderer, TemplateManager</td></tr>
                    <tr><td><code>app/Services/PageBuilder/Widgets/</code></td><td>7</td><td>BaseWidget + 6 widgets concretos</td></tr>
                    <tr><td><code>app/Providers/</code></td><td>1</td><td>PageBuilderServiceProvider (registra singletons e rotas)</td></tr>
                    <tr><td><code>config/page-builder.php</code></td><td>1</td><td>Configuração: lista de widgets habilitados, config de cache</td></tr>
                    <tr><td><code>database/migrations/</code></td><td>3</td><td>pages, elements, revisions</td></tr>
                    <tr><td><code>routes/page-builder.php</code></td><td>1</td><td>35+ rotas (resource pages + elementos + revisões)</td></tr>
                    <tr><td><code>resources/views/</code></td><td>9</td><td>Blade views (editor, pages, auth, tutorial)</td></tr>
                    <tr><td><code>public/js/</code></td><td>1</td><td>page-builder-editor.js (730 linhas)</td></tr>
                    <tr><td><code>tests/Unit/</code></td><td>4</td><td>BaseWidgetTest, PageBuilderServiceTest, TemplateManagerTest, ExampleTest</td></tr>
                    <tr><td><code>tests/Feature/</code></td><td>4</td><td>PageControllerTest, ElementControllerTest, RevisionControllerTest, ExampleTest</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Injeção de Dependências (Provider)</h3>
                <p>O <code>PageBuilderServiceProvider</code> registra todos os serviços como <strong>singletons</strong> (uma instância compartilhada):</p>
                <ul>
                    <li><code>WidgetManager</code> — lê <code>config/page-builder.php</code> e registra os 6 widgets</li>
                    <li><code>ElementManager</code> — recebe WidgetManager via construtor</li>
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
                    <tr><td>Cobertura de widgets</td><td>7/7</td><td style="color:green">BaseWidget + todos os 6 widgets</td></tr>
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
                    <li><strong>prepareSettings()</strong> — merge automático de defaults em todos os 6 widgets</li>
                    <li><strong>Editor JS extraído</strong> — 900+ linhas em arquivo separado para cache</li>
                    <li><strong>93 testes</strong> — cobertura completa de controllers, services e widgets</li>
                    <li><strong>Bug fixes</strong> — status default, max order nulo, config key, AuthorizesRequests</li>
                    <li><strong>Editor WYSIWYG</strong> — widget de texto com toolbar rich-text (negrito, itálico, links, imagens, vídeos, listas)</li>
                    <li><strong>Upload de imagens no texto</strong> — botão de imagem + colar (Ctrl+V) no editor WYSIWYG</li>
                    <li><strong>Vídeos YouTube</strong> — botão na toolbar que converte URL em embed responsivo com privacidade</li>
                    <li><strong>Edição inline corrigida</strong> — preserva HTML (innerHTML) ao invés de destruir com textContent</li>
                </ul>
            </div>
        </section>

        {{-- MOODLE --}}
        <section id="moodle" class="step">
            <h2>20. Uso com Moodle 4.5+</h2>
            <div class="step-body">
                <p>O Page Builder pode ser integrado ao <strong>Moodle 4.5+</strong> para criar páginas ricas dentro da sua plataforma de aprendizado. Abaixo estão as instruções de uso.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.1 Copiar HTML para o Moodle</h3>
                <ol>
                    <li>Crie sua página no Page Builder normalmente (use templates prontos ou comece do zero).</li>
                    <li>Na lista de páginas, clique em <strong>"Copy HTML"</strong> (ou no editor, clique no botão <strong>"Copy HTML"</strong> da barra de ferramentas).</li>
                    <li>O HTML renderizado da página será copiado para a área de transferência.</li>
                    <li>No Moodle, edite um recurso do tipo <strong>Página (Page)</strong> ou <strong>Livro (Book)</strong>, ou um bloco HTML.</li>
                    <li>No editor do Moodle, mude para o modo <strong>HTML</strong> (código fonte) e cole o conteúdo (<kbd>Ctrl+V</kbd>).</li>
                    <li>Salve as alterações. O conteúdo será exibido com os estilos inline preservados.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.2 Exportar e Importar entre Moodle e Page Builder</h3>
                <ol>
                    <li><strong>Exportar:</strong> Na lista de páginas, clique em <strong>"Export"</strong> para baixar um arquivo <code>.json</code> com toda a estrutura da página.</li>
                    <li><strong>Importar:</strong> Em qualquer instalação do Page Builder, clique em <strong>"Import"</strong> na lista de páginas, selecione o arquivo <code>.json</code> exportado e a página será recriada.</li>
                    <li>Isso permite transferir páginas entre diferentes instalações ou fazer backup antes de modificar.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">20.3 Renderização como Página Moodle</h3>
                <ol>
                    <li>O Page Builder pode ser incorporado ao Moodle como um <strong>recurso externo</strong> ou via <strong>iframe</strong>.</li>
                    <li>Use o parâmetro <code>?format=inner</code> na URL de renderização (<code>/page-builder/pages/{id}/render?format=inner</code>) para obter apenas o HTML do conteúdo, sem a estrutura completa da página (ideal para incorporação).</li>
                    <li>Você também pode usar <code>?format=inner&theme=none</code> para um HTML ainda mais limpo, apenas com os elementos e estilos inline.</li>
                    <li>No Moodle, crie um recurso <strong>"Página"</strong> e cole o HTML gerado no modo código fonte, ou use um <strong>bloco HTML</strong> para exibir conteúdo em áreas laterais.</li>
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
                    <li><strong>Estilos inline:</strong> Todo o CSS gerado pelo Page Builder é inline (atributo <code>style</code>), o que garante compatibilidade máxima com o editor do Moodle.</li>
                    <li><strong>Imagens:</strong> Use URLs públicas para imagens (ex.: placehold.co ou imagens hospedadas). Imagens locais do Page Builder não serão acessíveis pelo Moodle.</li>
                    <li><strong>Imagens no texto:</strong> O editor de texto (WYSIWYG) permite inserir imagens diretamente no conteúdo. Faça upload pela toolbar ou cole (Ctrl+V). As imagens ficam com CSS inline, compatíveis com o Moodle.</li>
                    <li><strong>Vídeos YouTube:</strong> Na toolbar do editor de texto, clique no botão <strong>&#9654;</strong> (vermelho) e cole a URL do vídeo. O embed é inserido como <code>&lt;iframe&gt;</code> com privacidade ativada (<code>youtube-nocookie.com</code>), recomendado pelo Moodle.</li>
                    <li><strong>Fórmulas matemáticas:</strong> O Page Builder usa KaTeX para renderizar LaTeX no editor. Ao copiar o HTML, as fórmulas são salvas como HTML estático — o Moodle não precisa de KaTeX instalado. Para fórmulas inline no parágrafo, use o botão &#945; no editor WYSIWYG.</li>
                    <li><strong>Responsividade:</strong> O HTML gerado mantém a responsividade. Teste em diferentes dispositivos após colar no Moodle.</li>
                    <li><strong>Limitação de largura:</strong> O Moodle pode aplicar estilos próprios de container. Use o parâmetro <code>?format=inner</code> para obter apenas o conteúdo bruto e ajuste margens no Moodle se necessário.</li>
                    <li><strong>Copiar HTML direto do editor:</strong> No editor visual, o botão "Copy HTML" na barra de ferramentas copia o HTML da página atual (salva) para a área de transferência — você nem precisa sair do editor.</li>
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
