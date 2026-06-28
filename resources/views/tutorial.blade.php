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
            <a href="#overview">Visão Geral</a>
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
        </div>

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

        {{-- CREATE PAGE --}}
        <section id="create-page" class="step">
            <h2>2. Criar uma Página</h2>
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
                    <li>Opcionalmente, escolha um <strong>Template</strong> (veja <a href="#templates">passo 8</a>)</li>
                    <li>Clique em <strong>"Criar &amp; Abrir Editor"</strong> para ir direto ao editor visual</li>
                </ol>
            </div>
        </section>

        {{-- THE EDITOR --}}
        <section id="editor" class="step">
            <h2>3. O Editor</h2>
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
            <h2>4. Adicionar Widgets (Arrastar &amp; Soltar)</h2>
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
            <h2>5. Selecionar &amp; Editar Configurações</h2>
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
            <h2>6. Configurações da Página</h2>
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
            <h2>7. Visualização Responsiva</h2>
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
            <h2>8. Usar Templates</h2>
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
            <h2>9. Desfazer &amp; Refazer</h2>
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
            <h2>10. Salvar &amp; Publicar</h2>
            <div class="step-body">
                <p>Dois botões na barra de ferramentas do editor permitem persistir seu trabalho:</p>
                <table class="widget-table">
                    <tr><th>Botão</th><th>O que faz</th></tr>
                    <tr><td><strong>Salvar Rascunho</strong></td><td>Salva o estado atual sem alterar a versão publicada. Sua página permanece como "rascunho" ou mantém seu estado publicado anterior.</td></tr>
                    <tr><td><strong>Publicar</strong></td><td>Salva e publica imediatamente a página — ela se torna visível para visitantes.</td></tr>
                </table>
                <div class="tip">
                    <strong>&#128161; Dica:</strong> O editor também faz <strong>salvamento automático</strong> a cada 60 segundos, para que você não perca trabalho se esquecer de salvar manualmente.
                </div>
            </div>
        </section>

        {{-- PREVIEW --}}
        <section id="preview" class="step">
            <h2>11. Visualizar uma Página</h2>
            <div class="step-body">
                <p>Na lista de páginas, clique em <strong>"Visualizar"</strong> ao lado de qualquer página.</p>
                <p>Isso abre uma prévia limpa da página com todos os widgets renderizados e estilizados como apareceriam no site ao vivo.</p>
                <p>Se a página ainda for um <strong>rascunho</strong>, apenas você (usuários logados) pode vê-la.</p>
            </div>
        </section>

        {{-- DUPLICATE / DELETE --}}
        <section id="duplicate-delete" class="step">
            <h2>12. Duplicar &amp; Excluir</h2>
            <div class="step-body">
                <p>Na lista de páginas, cada página tem botões de ação:</p>
                <table class="widget-table">
                    <tr><th>Ação</th><th>Como funciona</th></tr>
                    <tr><td><strong>Duplicar</strong></td><td>Cria uma cópia exata da página (incluindo todos os elementos) com "(cópia)" anexado ao título.</td></tr>
                    <tr><td><strong>Exportar</strong></td><td>Baixa a página como um arquivo <code>.json</code> — você pode compartilhar ou fazer backup.</td></tr>
                    <tr><td><strong>Importar</strong></td><td>Envie um arquivo <code>.json</code> previamente exportado para recriar a página.</td></tr>
                    <tr><td><strong>Excluir</strong></td><td>Remove permanentemente a página e todos os seus elementos. Uma mensagem de confirmação aparece após a exclusão.</td></tr>
                </table>
            </div>
        </section>

        {{-- SHOWCASE COMPLETO TEMPLATE --}}
        <section id="showcase" class="step">
            <h2>13. Template Showcase Completo</h2>
            <div class="step-body">
                <p>O template <strong>Showcase Completo</strong> é uma landing page de marketing completa construída inteiramente com o page builder. Ele demonstra técnicas avançadas de layout usando Seções aninhadas, Colunas e widgets estilizados. Siga o passo a passo abaixo para recriá-lo do zero:</p>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">13.1 Seção Hero</h3>
                <ol>
                    <li><strong>Arraste uma Seção</strong> da lista de widgets para a área da página (canvas). Esta será a seção principal do hero.</li>
                    <li><strong>Selecione a Seção</strong> clicando nela. No painel direito, configure:
                        <ul>
                            <li><strong>Cor de fundo:</strong> <code>#1a1a2e</code></li>
                            <li><strong>Padding superior/inferior:</strong> <code>100px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Coluna</strong> para dentro da Seção que você acabou de criar.</li>
                    <li><strong>Selecione a Coluna</strong> e no painel direito, em Alinhamento, escolha <strong>Centro</strong> (<code>text-align: center</code>).</li>
                    <li><strong>Arraste um widget Título</strong> para dentro da Coluna. Configure:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Bem-vindo ao Showcase"</code></li>
                            <li><strong>Tag:</strong> H1</li>
                            <li><strong>Cor:</strong> branca (<code>#ffffff</code>)</li>
                            <li><strong>Alinhamento:</strong> Centro</li>
                        </ul>
                    </li>
                    <li><strong>Arraste um widget Texto</strong> para dentro da Coluna (abaixo do título). Configure:
                        <ul>
                            <li><strong>Conteúdo:</strong> <code>"Crie landing pages bonitas com nosso construtor drag-and-drop"</code></li>
                            <li><strong>Cor:</strong> cinza claro (<code>#cccccc</code>)</li>
                            <li><strong>Alinhamento:</strong> Centro</li>
                        </ul>
                    </li>
                    <li><strong>Arraste um widget Botão</strong> para dentro da Coluna (abaixo do texto). Configure:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Começar Agora"</code></li>
                            <li><strong>Cor do fundo:</strong> branca (<code>#ffffff</code>)</li>
                            <li><strong>Cor do texto:</strong> escura (<code>#1a1a2e</code>)</li>
                            <li><strong>Border Radius:</strong> <code>50px</code> (formato pílula)</li>
                            <li><strong>Padding:</strong> <code>12px 36px</code></li>
                            <li><strong>Alinhamento:</strong> Centro</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">13.2 Seção de Recursos (Features)</h3>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página (ela virá abaixo da seção Hero). Configure:
                        <ul>
                            <li><strong>Cor de fundo:</strong> <code>#f8f9fa</code></li>
                            <li><strong>Padding superior/inferior:</strong> <code>80px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma segunda Seção</strong> para dentro da Seção que você acabou de criar. Esta seção interna servirá para limitar a largura do conteúdo. Configure:
                        <ul>
                            <li><strong>Largura máxima (max-width):</strong> <code>1140px</code></li>
                            <li><strong>Margem:</strong> <code>auto</code> (para centralizar)</li>
                        </ul>
                    </li>
                    <li><strong>Arraste um widget Título</strong> para dentro da seção interna. Configure:
                        <ul>
                            <li><strong>Texto:</strong> <code>"Recursos"</code></li>
                            <li><strong>Tag:</strong> H2</li>
                            <li><strong>Alinhamento:</strong> Centro</li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Seção</strong> para dentro da seção interna (abaixo do título). Esta será a linha que conterá as colunas de recursos.</li>
                    <li><strong>Arraste três Colunas</strong> para dentro desta nova Seção. Cada coluna terá automaticamente 33% de largura.</li>
                    <li><strong>Na primeira Coluna</strong>, arraste:
                        <ul>
                            <li>Um <strong>Título</strong> com texto <code>"Alta Performance"</code></li>
                            <li>Um <strong>Texto</strong> com a descrição do recurso (cor cinza escuro)</li>
                        </ul>
                    </li>
                    <li><strong>Na segunda Coluna</strong>, arraste:
                        <ul>
                            <li>Um <strong>Título</strong> com texto <code>"Arrastar & Soltar"</code></li>
                            <li>Um <strong>Texto</strong> com a descrição do recurso</li>
                        </ul>
                    </li>
                    <li><strong>Na terceira Coluna</strong>, arraste:
                        <ul>
                            <li>Um <strong>Título</strong> com texto <code>"Responsivo"</code></li>
                            <li>Um <strong>Texto</strong> com a descrição do recurso</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">13.3 Seção de Estatísticas (Stats)</h3>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Cor de fundo:</strong> <code>#16213e</code></li>
                            <li><strong>Padding superior/inferior:</strong> <code>60px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Seção</strong> para dentro dela (max-width: <code>1140px</code>, margem: <code>auto</code>).</li>
                    <li><strong>Arraste três Colunas</strong> lado a lado dentro da seção interna.</li>
                    <li><strong>Em cada Coluna</strong>, arraste:
                        <ul>
                            <li>Um <strong>Título</strong> com o número da estatística (ex.: <code>"500+"</code>, <code>"99%"</code>, <code>"24/7"</code>), cor branca, alinhamento centralizado</li>
                            <li>Um <strong>Texto</strong> com o rótulo (ex.: <code>"Projetos Concluídos"</code>, <code>"Satisfação do Cliente"</code>, <code>"Suporte"</code>), cor cinza, alinhamento centralizado</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">13.4 Seção de Galeria / Portfólio</h3>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Cor de fundo:</strong> branca (<code>#ffffff</code>)</li>
                            <li><strong>Padding superior/inferior:</strong> <code>80px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Seção</strong> para dentro dela (max-width: <code>1140px</code>, margem: <code>auto</code>).</li>
                    <li><strong>Arraste um Título</strong>: <code>"Nosso Trabalho"</code>, H2, centralizado.</li>
                    <li><strong>Arraste um Texto</strong>: <code>"Veja alguns dos nossos projetos recentes"</code>, centralizado.</li>
                    <li><strong>Arraste uma Seção</strong> (linha) e dentro dela <strong>três Colunas</strong> (33% cada).</li>
                    <li><strong>Em cada Coluna</strong>, arraste:
                        <ul>
                            <li>Uma <strong>Imagem</strong> com URL: <code>https://placehold.co/600x400/1a1a2e/ffffff?text=Projeto+1</code> (ajuste o número do projeto para 1, 2 e 3), largura <code>100%</code></li>
                            <li>Um <strong>Título</strong> com o nome do projeto (H3)</li>
                            <li>Um <strong>Texto</strong> com a descrição da categoria</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">13.5 Seção de Equipe (Team)</h3>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Cor de fundo:</strong> <code>#f0f0f5</code></li>
                            <li><strong>Padding superior/inferior:</strong> <code>80px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Seção</strong> para dentro dela (max-width: <code>1140px</code>, margem: <code>auto</code>).</li>
                    <li><strong>Arraste um Título</strong>: <code>"Conheça Nossa Equipe"</code>, H2, centralizado.</li>
                    <li><strong>Arraste uma Seção</strong> (linha) e dentro dela <strong>quatro Colunas</strong> (25% cada).</li>
                    <li><strong>Em cada Coluna</strong>, arraste:
                        <ul>
                            <li>Uma <strong>Imagem</strong> de avatar (URL de placeholder redondo, ex.: <code>https://placehold.co/150x150/1a1a2e/ffffff?text=Joao</code>), largura <code>150px</code>, centralizado</li>
                            <li>Um <strong>Título</strong> com o nome do membro (H4, centralizado)</li>
                            <li>Um <strong>Texto</strong> com o cargo (ex.: <code>"CEO & Fundador"</code>, centralizado, texto pequeno)</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">13.6 Seção CTA (Chamada para Ação)</h3>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Cor de fundo:</strong> <code>#0f3460</code></li>
                            <li><strong>Padding superior/inferior:</strong> <code>60px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Seção</strong> para dentro dela (max-width: <code>1140px</code>, margem: <code>auto</code>).</li>
                    <li><strong>Arraste duas Colunas</strong> lado a lado. Na primeira Coluna, deixe com <strong>70%</strong> de largura e na segunda com <strong>30%</strong>.</li>
                    <li><strong>Na Coluna da esquerda (70%):</strong>
                        <ul>
                            <li><strong>Título</strong>: <code>"Pronto para Começar?"</code>, H2, cor branca, alinhado à esquerda</li>
                            <li><strong>Texto</strong>: <code>"Junte-se a milhares de clientes satisfeitos usando nossa plataforma."</code>, cor cinza claro</li>
                        </ul>
                    </li>
                    <li><strong>Na Coluna da direita (30%):</strong>
                        <ul>
                            <li><strong>Botão</strong>: <code>"Fale Conosco"</code>, fundo claro, texto escuro, alinhado à direita, formato pílula</li>
                        </ul>
                    </li>
                </ol>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">13.7 Seção de Rodapé (Footer)</h3>
                <ol>
                    <li><strong>Arraste uma nova Seção</strong> para a página. Configure:
                        <ul>
                            <li><strong>Cor de fundo:</strong> <code>#1a1a2e</code></li>
                            <li><strong>Padding superior/inferior:</strong> <code>40px</code></li>
                        </ul>
                    </li>
                    <li><strong>Arraste uma Seção</strong> para dentro dela (max-width: <code>1140px</code>, margem: <code>auto</code>).</li>
                    <li><strong>Arraste quatro Colunas</strong> (25% cada).</li>
                    <li><strong>Em cada Coluna</strong>, arraste:
                        <ul>
                            <li>Um <strong>Título</strong> pequeno e maiúsculo para o nome da coluna (ex.: <code>"PRODUTO"</code>, <code>"EMPRESA"</code>, <code>"SUPORTE"</code>, <code>"REDES"</code>)</li>
                            <li>Um <strong>Texto</strong> com links ou descrição (cor cinza)</li>
                        </ul>
                    </li>
                    <li><strong>Fora das colunas</strong> (diretamente na seção interna), arraste um widget <strong>Texto</strong> de largura total com o copyright centralizado: <code>"&copy; 2025 Showcase. Todos os direitos reservados."</code></li>
                </ol>

                <div class="tip">
                    <strong>&#128161; Dica:</strong> O template Showcase Completo é o melhor exemplo de construção avançada de páginas. Ele usa Seções aninhadas (seções dentro de seções), larguras de coluna variadas, estilos diversos por seção e todos os tipos de widget. Abra-o no editor para ver exatamente como cada elemento está configurado.
                </div>

                <h3 style="font-size:1rem;margin-top:1.25rem;margin-bottom:.5rem">Tabela Resumo</h3>
                <table class="widget-table">
                    <tr><th>Seção</th><th>Fundo</th><th>Colunas</th><th>Widgets Principais</th></tr>
                    <tr><td>Hero</td><td><code>#1a1a2e</code></td><td>1</td><td>Título, Texto, Botão</td></tr>
                    <tr><td>Recursos</td><td><code>#f8f9fa</code></td><td>3</td><td>Título, Texto (seção aninhada para largura)</td></tr>
                    <tr><td>Estatísticas</td><td><code>#16213e</code></td><td>3</td><td>Título, Texto</td></tr>
                    <tr><td>Galeria</td><td>branco</td><td>3</td><td>Imagem, Título, Texto</td></tr>
                    <tr><td>Equipe</td><td><code>#f0f0f5</code></td><td>4</td><td>Imagem, Título, Texto</td></tr>
                    <tr><td>CTA</td><td><code>#0f3460</code></td><td>2 (70/30)</td><td>Título, Texto, Botão</td></tr>
                    <tr><td>Rodapé</td><td><code>#1a1a2e</code></td><td>4 + largura total</td><td>Título, Texto</td></tr>
                </table>
            </div>
        </section>

        {{-- PROJECT STRUCTURE --}}
        <section id="project-structure" class="step">
            <h2>14. Estrutura do Projeto</h2>
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
