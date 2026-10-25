# Plano de Implementação: Hub de Projetos estilo Unity 3D

Data: 2026-10-25
Status: Planejado (Etapa 1 e 2 em andamento)

## Objetivo
Transformar a tela inicial pós-login para um "Hub de Projetos" estilo Unity Hub. Ao logar, o usuário deverá ir para a lista de projetos. Ao clicar em um projeto, será aberto um Dashboard do Projeto com acesso centralizado a Páginas, Temas e Popups.

## Situação Atual
- Pós-login redireciona para `/page-builder/pages` (routes/web.php)
- Já existe CRUD de Projetos: ProjectController, rotas em routes/page-builder.php, tela em resources/views/page-builder/projects/index.blade.php
- Project possui relacionamentos: pages(), themeTemplates(), popups()
- Listagem de páginas já filtra por projeto e exibe seletor

## Proposta UX/UI
- Hub de Projetos: cards com nome, descrição, contagem (páginas/temas/popups), última atualização
- Modal "Novo Projeto": campos Nome do projeto *, Local do projeto (informativo), Descrição (opcional)
- Dashboard por projeto: header com breadcrumb Projetos > [Projeto], cards rápidos para Páginas/Temas/Popups, lista "Recentes", botão + Novo com dropdown
- Breadcrumbs para manter contexto
- Empty states com CTAs diretos

## Arquivos a modificar/criar

| Arquivo | Ação | Descrição |
|---|---|---|
| routes/web.php | Alterar | Redirecionar pós-login para route(page-builder.projects.index) |
| routes/page-builder.php | Adicionar | Rota GET projects/{project}/dashboard -> ProjectController@dashboard |
| app/Http/Controllers/PageBuilder/ProjectController.php | Adicionar método | dashboard(Project $project) com authorize, carregar páginas/temas/popups recentes e contagens |
| resources/views/page-builder/projects/show.blade.php | Criar | Dashboard do projeto estilo Unity Hub |
| resources/views/page-builder/projects/index.blade.php | (Etapa 3) Redesenhar | Transformar em hub com cards e modal atualizado |
| app/Http/Controllers/PageBuilder/ProjectController.php@store | (Etapa 3) Ajustar | Aceitar location opcional (salvar em settings[location] ou description) |

## Plano por etapas

### Etapa 1: Fluxo pós-login (FAZER AGORA)
- [ ] Alterar routes/web.php: se autenticado redirecionar para page-builder.projects.index

### Etapa 2: Dashboard por projeto (FAZER AGORA)
- [ ] Adicionar rota em routes/page-builder.php
- [ ] Adicionar método dashboard() em ProjectController (com authorize('view', $project))
- [ ] Criar view resources/views/page-builder/projects/show.blade.php com layout moderno

### Etapa 3: Redesenhar Hub (FAZER DEPOIS)
- [ ] Reescrever projects/index.blade.php para cards
- [ ] Atualizar modal com campo "Local do projeto"
- [ ] Ajustar store() para aceitar location
- [ ] Atualizar links para apontar ao dashboard

### Etapa 4: Ajustes finais (FAZER DEPOIS)
- [ ] Atualizar navegação/breadcrumbs
- [ ] Revisar links "Ver páginas" para usar dashboard
- [ ] Testar fluxo completo

## Observações
- Manter compatibilidade com pages.index (pode continuar existindo)
- Não quebrar rotas existentes
- Usar padrões já existentes (cards, badges, modais, toasts)
- location é apenas informativo por enquanto (pode ir em settings[location])
