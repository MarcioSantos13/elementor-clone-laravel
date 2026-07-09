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
            <a href="#select-settings">Editar Configurações</a>
            <a href="#page-settings">Configurações da Página</a>
            <a href="#responsive">Visualização Responsiva</a>
            <a href="#templates">Usar Templates</a>
            <a href="#undo-redo">Desfazer &amp; Refazer</a>
            <a href="#save-publish">Salvar &amp; Publicar</a>
            <a href="#preview">Visualizar Página</a>
            <a href="#duplicate-delete">Duplicar &amp; Excluir</a>
            <a href="#showcase">Template Showcase Completo</a>
            <a href="#project-structure">Estrutura do Projeto</a>
            <a href="#improvements">Melhorias Implementadas</a>
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
                <p>Este construtor de páginas permite criar páginas visualmente arrastando widgets para uma tela, editando conteúdo e estilo, e publicando o resultado — sem necessidade de código.</p>
                <p><strong>O que você pode fazer:</strong></p>
                <ul>
                    <li>Criar páginas com título e status (rascunho / publicado)</li>
                    <li>Abrir um <strong>editor visual</strong> em tela cheia</li>
                    <li>Arrastar <strong>widgets</strong> (Título, Texto, Imagem, Botão, etc.) para a página</li>
                    <li>Selecionar qualquer elemento e editar suas <strong>configurações</strong> no painel direito</li>
                    <li>Alternar entre visualização <strong>desktop / tablet / mobile</strong></li>
                    <li>Aplicar <strong>templates</strong> prontos (Landing, Sobre, Contato, Showcase Completo)</li>
                    <li>Desfazer / refazer alterações (Ctrl+Z / Ctrl+Shift+Z)</li>
                    <li>Salvamento automático a cada 60 segundos, ou salvar / publicar manualmente</li>
                    <li>Duplicar, exportar, importar ou excluir páginas</li>
                </ul>
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
                <p>O editor é uma interface em tela cheia com tema escuro e três painéis:</p>
                <div class="panel-layout-ill">
                    <div class="panel-ill left"><strong>Widgets</strong><br><span style="font-size:.75rem">arraste itens para a tela</span></div>
                    <div class="panel-ill center"><strong>Canvas</strong><br><span style="font-size:.75rem">prévia da sua página</span></div>
                    <div class="panel-ill right"><strong>Configurações</strong><br><span style="font-size:.75rem">opções do elemento selecionado</span></div>
                </div>
                <ul>
                    <li><strong>Painel esquerdo</strong> — lista de widgets disponíveis. Arraste um para a tela.</li>
                    <li><strong>Centro (canvas)</strong> — mostra uma prévia ao vivo da página. Clique em qualquer elemento para selecioná-lo.</li>
                    <li><strong>Painel direito</strong> — mostra as configurações do elemento selecionado (ou da página).</li>
                    <li><strong>Barra superior</strong> — botões responsivos (desktop/tablet/mobile), salvar, publicar, desfazer/refazer.</li>
                </ul>
            </div>
        </section>

        {{-- ADDING WIDGETS --}}
        <section id="drag-widgets" class="step">
            <h2>5. Adicionar Widgets (Arrastar &amp; Soltar)</h2>
            <div class="step-body">
                <p>Cada widget adiciona um tipo diferente de conteúdo. Veja o que cada um faz:</p>
                <table class="widget-table">
                    <tr><th>Widget</th><th>O que cria</th></tr>
                    <tr><td><strong>Título (Heading)</strong></td><td>Um título grande (&lt;h1&gt;–&lt;h6&gt;) com tag, texto, alinhamento e cor configuráveis</td></tr>
                    <tr><td><strong>Texto</strong></td><td>Um parágrafo ou bloco de texto com conteúdo e cor configuráveis</td></tr>
                    <tr><td><strong>Imagem</strong></td><td>Uma imagem com URL, texto alternativo e largura configuráveis</td></tr>
                    <tr><td><strong>Botão</strong></td><td>Um botão clicável com texto, URL, alinhamento e cor configuráveis</td></tr>
                    <tr><td><strong>Seção</strong></td><td>Um contêiner estrutural (linha de largura total). Você coloca Colunas ou outros widgets dentro dela</td></tr>
                    <tr><td><strong>Coluna</strong></td><td>Uma coluna vertical dentro de uma Seção. Controla largura, alinhamento, padding, fundo</td></tr>
                </table>
                <p><strong>Como adicionar:</strong></p>
                <ol>
                    <li>No <strong>painel esquerdo</strong>, encontre o widget desejado</li>
                    <li><strong>Arraste</strong> (clique e segure) e <strong>solte</strong> na tela</li>
                    <li>O widget aparece instantaneamente no canvas</li>
                </ol>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> Comece arrastando uma <strong>Seção</strong> para a tela, depois arraste uma <strong>Coluna</strong> para dentro da seção, e então arraste widgets de conteúdo (Título, Texto, etc.) para dentro da coluna.
                </div>
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
        <section id="project-structure" class="step">
            <h2>15. Estrutura do Projeto</h2>
            <div class="step-body">
                <p>Este projeto é uma aplicação Laravel com a seguinte estrutura de alto nível:</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Backend (Laravel)</h3>
                <table class="widget-table">
                    <tr><th>Caminho</th><th>Descrição</th></tr>
                    <tr><td><code>app/Models/</code></td><td>Models Eloquent: <code>Page</code>, <code>PageElement</code>, <code>Widget</code></td></tr>
                    <tr><td><code>app/Http/Controllers/PageBuilder/</code></td><td>Controllers para API de páginas, renderização do editor, templates</td></tr>
                    <tr><td><code>app/Http/Controllers/PageBuilderController.php</code></td><td>Controller principal para operações CRUD e gerenciamento de templates</td></tr>
                    <tr><td><code>app/Services/PageBuilder/</code></td><td>Classes de serviço para renderização de páginas, lógica de construção de templates</td></tr>
                    <tr><td><code>database/migrations/</code></td><td>Arquivos de migração para as tabelas pages, page_elements e widgets</td></tr>
                    <tr><td><code>database/seeders/</code></td><td>Seeders para criar definições de templates e dados de widget de exemplo</td></tr>
                    <tr><td><code>routes/web.php</code></td><td>Rotas web para o page builder (páginas, editor, templates)</td></tr>
                    <tr><td><code>routes/api.php</code></td><td>Rotas de API para operações AJAX (salvar, atualizar, reordenar elementos)</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Frontend (Views & Assets)</h3>
                <table class="widget-table">
                    <tr><th>Caminho</th><th>Descrição</th></tr>
                    <tr><td><code>resources/views/page-builder/pages/</code></td><td>Views Blade para lista de páginas (index), formulário de criação, preview</td></tr>
                    <tr><td><code>resources/views/page-builder/editor/</code></td><td>View Blade para a interface do editor em tela cheia</td></tr>
                    <tr><td><code>resources/views/page-builder/layouts/</code></td><td>Arquivos de layout (app.blade.php) compartilhados entre as views do builder</td></tr>
                    <tr><td><code>resources/views/page-builder/partials/</code></td><td>Partials reutilizáveis (painel de widgets, painel de configurações, barra de ferramentas)</td></tr>
                    <tr><td><code>resources/views/page-builder/render/</code></td><td>Componentes Blade para renderizar cada tipo de widget no front-end</td></tr>
                    <tr><td><code>public/js/page-builder/</code></td><td>Arquivos JavaScript: drag-and-drop, desfazer/refazer, salvamento automático, lógica do editor</td></tr>
                    <tr><td><code>public/css/page-builder/</code></td><td>Arquivos CSS para a interface do editor e renderização front-end</td></tr>
                </table>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Fluxo de Dados Principal</h3>
                <ol>
                    <li><strong>Pages</strong> armazenam metadados (título, status, configurações de container, CSS personalizado)</li>
                    <li><strong>PageElements</strong> armazenam a árvore de widgets como uma estrutura aninhada (<code>parent_id</code> para hierarquia, <code>sort_order</code> para ordenação)</li>
                    <li>Cada <strong>PageElement</strong> tem um <code>widget_id</code> referenciando o model <strong>Widget</strong>, além de uma coluna JSON <code>settings</code> para configuração específica do widget</li>
                    <li>O <strong>editor</strong> carrega a página com todos os seus elementos via um endpoint da API, então renderiza o canvas usando partials recursivas</li>
                    <li><strong>Salvar</strong> envia a árvore completa de elementos como JSON para o servidor, que sincroniza com o banco de dados</li>
                    <li><strong>Templates</strong> são conjuntos predefinidos de PageElements que podem ser aplicados a páginas novas ou existentes</li>
                </ol>
            </div>
        </section>

        {{-- IMPROVEMENTS --}}
        <section id="improvements" class="step">
            <h2>16. Melhorias Implementadas</h2>
            <div class="step-body">
                <p>Durante o desenvolvimento, diversas melhorias de segurança, qualidade e arquitetura foram aplicadas. Abaixo o resumo de cada uma.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.1 Autorização (Policy)</h3>
                <p>Uma <strong>PagePolicy</strong> foi criada em <code>app/Policies/PagePolicy.php</code> para restringir ações de edição e exclusão de páginas ao usuário dono (<code>user_id</code>). A visualização permanece liberada para qualquer usuário logado. A policy é registrada via <code>AuthServiceProvider</code> em <code>app/Providers/AuthServiceProvider.php</code> e aplicada com <code>$this->authorize()</code> nos controllers de página, elemento e revisão.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.2 Sanitização de Conteúdo e Configurações</h3>
                <p>O método <code>sanitizeContent()</code> no <code>PageBuilderService</code> foi reforçado para sanitizar URLs em tags <code>&lt;a&gt;</code> e <code>&lt;img&gt;</code>, remover atributos <code>on*</code> (event handlers) e bloquear protocolo <code>javascript:</code>. O método <code>sanitizeSettings()</code> valida cada configuração de widget por tipo esperado (cores, URLs, HTML, texto puro, números, booleanos e aninhados), rejeitando valores inesperados.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.3 Tratamento de Erros no Editor JavaScript</h3>
                <p>Todas as 14 chamadas <code>fetch()</code> no editor JS (<code>public/js/page-builder-editor.js</code>) agora possuem encadeamento <code>.catch()</code> que exibe um toast de erro ao usuário, evitando que falhas silenciosas passem despercebidas.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.4 TemplateManager</h3>
                <p>A lógica de templates foi extraída do <code>PageController</code> para uma classe dedicada <code>TemplateManager</code> em <code>app/Services/PageBuilder/Core/TemplateManager.php</code>. Ela é registrada como singleton no <code>PageBuilderServiceProvider</code> e injetada no controller, seguindo o princípio da responsabilidade única. Os templates hardcoded foram movidos para o método <code>defaultTemplates()</code> do gerenciador.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.5 Settings com Valores Default (prepareSettings)</h3>
                <p>O método <code>prepareSettings()</code> foi adicionado ao <code>BaseWidget</code> e implementado em todos os 6 widgets (Heading, Text, Image, Button, Section, Column). Ele faz o merge automático das configurações salvas com os defaults definidos em <code>getDefaultSettings()</code>, eliminando lógica duplicada nos métodos <code>render()</code> e <code>renderEditor()</code>.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.6 Editor JS Extraído</h3>
                <p>O JavaScript do editor (<em>~630 linhas</em>) foi movido da view Blade <code>editor.blade.php</code> para um arquivo separado <code>public/js/page-builder-editor.js</code>. A view agora inclui o script via <code>&lt;script src&gt;</code>, melhorando a organização e permitindo cache pelo navegador.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.7 Factories e Testes Automatizados</h3>
                <p>Foram criadas <strong>factories</strong> para os models <code>Page</code>, <code>Element</code> e <code>Revision</code> (com traits <code>HasFactory</code> adicionadas). Um total de <strong>92 testes</strong> foram implementados:</p>
                <ul>
                    <li><strong>19 testes unitários</strong> — <code>PageBuilderServiceTest</code> (create, update, sanitize, elements, revisions, export/import, render)</li>
                    <li><strong>12 testes unitários</strong> — <code>TemplateManagerTest</code> (listagem, aplicação, importação, edge cases, estrutura de templates)</li>
                    <li><strong>13 testes unitários</strong> — <code>BaseWidgetTest</code> (getters, validação, prepareSettings, sanitização, isContainer, isDynamic)</li>
                    <li><strong>24 testes de feature</strong> — <code>PageControllerTest</code> (CRUD, auth, publish, templates, export/import)</li>
                    <li><strong>15 testes de feature</strong> — <code>ElementControllerTest</code> (CRUD, auth, reorder, move, settings, styles)</li>
                    <li><strong>9 testes de feature</strong> — <code>RevisionControllerTest</code> (list, show, restore, diff, delete, prune, auto-save, auth)</li>
                </ul>
                <p>Os testes usam <code>RefreshDatabase</code> com SQLite em memória e cobrem todos os cenários críticos de autorização, validação e fluxos de dados.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.8 Correções de Bugs</h3>
                <ul>
                    <li><strong>Status default:</strong> <code>createPage()</code> agora define status como <code>'draft'</code> quando não especificado.</li>
                    <li><strong>Max order nulo:</strong> <code>addElement()</code> trata <code>max('sort_order')</code> retornando <code>null</code> (tabela vazia).</li>
                    <li><strong>Config key:</strong> renomeada de <code>page-builder.templates</code> (conflito) para <code>page-builder.template_cache</code>.</li>
                    <li><strong>AuthorizesRequests:</strong> trait adicionada ao <code>Base Controller</code> (estava faltando).</li>
                </ul>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">16.9 composer.json Personalizado</h3>
                <p>O arquivo <code>composer.json</code> foi atualizado com <code>name</code>, <code>description</code> e <code>keywords</code> específicos do projeto, e o arquivo <code>_check_pwd.php</code> (resquício de instalação) foi removido.</p>
            </div>
        </section>

        {{-- MOODLE --}}
        <section id="moodle" class="step">
            <h2>17. Uso com Moodle 4.5+</h2>
            <div class="step-body">
                <p>O Page Builder pode ser integrado ao <strong>Moodle 4.5+</strong> para criar páginas ricas dentro da sua plataforma de aprendizado. Abaixo estão as instruções de uso.</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">17.1 Copiar HTML para o Moodle</h3>
                <ol>
                    <li>Crie sua página no Page Builder normalmente (use templates prontos ou comece do zero).</li>
                    <li>Na lista de páginas, clique em <strong>"Copy HTML"</strong> (ou no editor, clique no botão <strong>"Copy HTML"</strong> da barra de ferramentas).</li>
                    <li>O HTML renderizado da página será copiado para a área de transferência.</li>
                    <li>No Moodle, edite um recurso do tipo <strong>Página (Page)</strong> ou <strong>Livro (Book)</strong>, ou um bloco HTML.</li>
                    <li>No editor do Moodle, mude para o modo <strong>HTML</strong> (código fonte) e cole o conteúdo (<kbd>Ctrl+V</kbd>).</li>
                    <li>Salve as alterações. O conteúdo será exibido com os estilos inline preservados.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">17.2 Exportar e Importar entre Moodle e Page Builder</h3>
                <ol>
                    <li><strong>Exportar:</strong> Na lista de páginas, clique em <strong>"Export"</strong> para baixar um arquivo <code>.json</code> com toda a estrutura da página.</li>
                    <li><strong>Importar:</strong> Em qualquer instalação do Page Builder, clique em <strong>"Import"</strong> na lista de páginas, selecione o arquivo <code>.json</code> exportado e a página será recriada.</li>
                    <li>Isso permite transferir páginas entre diferentes instalações ou fazer backup antes de modificar.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">17.3 Renderização como Página Moodle</h3>
                <ol>
                    <li>O Page Builder pode ser incorporado ao Moodle como um <strong>recurso externo</strong> ou via <strong>iframe</strong>.</li>
                    <li>Use o parâmetro <code>?format=inner</code> na URL de renderização (<code>/page-builder/pages/{id}/render?format=inner</code>) para obter apenas o HTML do conteúdo, sem a estrutura completa da página (ideal para incorporação).</li>
                    <li>Você também pode usar <code>?format=inner&theme=none</code> para um HTML ainda mais limpo, apenas com os elementos e estilos inline.</li>
                    <li>No Moodle, crie um recurso <strong>"Página"</strong> e cole o HTML gerado no modo código fonte, ou use um <strong>bloco HTML</strong> para exibir conteúdo em áreas laterais.</li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">17.4 Dicas para Moodle</h3>
                <ul>
                    <li><strong>Estilos inline:</strong> Todo o CSS gerado pelo Page Builder é inline (atributo <code>style</code>), o que garante compatibilidade máxima com o editor do Moodle.</li>
                    <li><strong>Imagens:</strong> Use URLs públicas para imagens (ex.: placehold.co ou imagens hospedadas). Imagens locais do Page Builder não serão acessíveis pelo Moodle.</li>
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
