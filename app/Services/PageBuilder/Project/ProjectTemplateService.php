<?php

namespace App\Services\PageBuilder\Project;

use App\Models\Element;
use App\Models\Page;
use App\Models\Popup;
use App\Models\Project;
use App\Models\ThemeTemplate;
use App\Models\User;
use App\Services\PageBuilder\Core\TemplateManager;
use App\Services\PageBuilder\Popup\PopupService;
use App\Services\PageBuilder\Theme\ThemeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectTemplateService
{
    public const EXAMPLE_NAME = 'Modelo — Site Institucional';

    public function __construct(
        protected ProjectService $projects,
        protected TemplateManager $templateManager,
        protected ThemeService $themeService,
        protected PopupService $popupService,
    ) {}

    public function createExample(User $user, ?string $name = null): Project
    {
        return DB::transaction(function () use ($user, $name) {
            $project = $this->projects->create($user, [
                'name' => $name ?: self::EXAMPLE_NAME,
                'description' => 'Projeto completo com página inicial, sobre, contato, 404, header, footer, artigo, listagem e popup. Use como base para novos projetos.',
                'settings' => [
                    'is_project_template' => true,
                    'template_version' => 1,
                ],
            ]);

            foreach ($this->pageBlueprints() as $blueprint) {
                $this->createPage($user, $project, $blueprint);
            }

            foreach ($this->themeBlueprints() as $blueprint) {
                $page = $this->createTemplatePage($user, $project, $blueprint);
                $this->templateManager->applyThemeTemplate($page, $blueprint['type']);
                $status = $this->themeStatus($blueprint['type']);

                ThemeTemplate::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'page_id' => $page->id,
                    'title' => $blueprint['title'],
                    'slug' => $this->uniqueSlug(ThemeTemplate::class, $blueprint['title'], 'tema'),
                    'type' => $blueprint['type'],
                    'status' => $status,
                    'conditions' => $blueprint['conditions'],
                    'order' => $blueprint['order'],
                    'published_at' => $status === 'published' ? now() : null,
                ]);
            }

            $popupPage = $this->createTemplatePage($user, $project, [
                'title' => 'Newsletter (Popup)',
                'slug' => 'newsletter-popup',
                'settings' => ['container_width' => '520px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
            ]);
            $this->templateManager->applyPopupTemplate($popupPage, 'popup-newsletter');
            $popupStatus = Popup::published()->exists() ? 'draft' : 'published';

            Popup::create([
                'user_id' => $user->id,
                'project_id' => $project->id,
                'page_id' => $popupPage->id,
                'title' => 'Newsletter do Site',
                'slug' => $this->uniqueSlug(Popup::class, 'newsletter-do-site', 'popup'),
                'type' => 'popup',
                'status' => $popupStatus,
                'triggers' => [['type' => 'on_timer', 'value' => '6']],
                'settings' => [
                    'width' => '480px',
                    'position' => 'center',
                    'overlay' => true,
                    'close_button' => true,
                    'animation' => 'fadeIn',
                ],
                'conditions' => [],
                'order' => 0,
                'published_at' => $popupStatus === 'published' ? now() : null,
            ]);

            $this->clearCaches();

            return $project->fresh();
        });
    }

    public function duplicate(Project $source, User $user, ?string $name = null): Project
    {
        return DB::transaction(function () use ($source, $user, $name) {
            $source->load(['pages', 'themeTemplates', 'popups']);

            $settings = $source->settings ?? [];
            unset($settings['is_project_template']);

            $project = $this->projects->create($user, [
                'name' => $name ?: 'Cópia de '.$source->name,
                'description' => $source->description,
                'settings' => $settings,
            ]);

            $pageMap = [];
            foreach ($source->pages()->orderBy('id')->get() as $sourcePage) {
                $pageMap[$sourcePage->id] = $this->clonePage($sourcePage, $user, $project)->id;
            }

            foreach ($source->themeTemplates()->orderBy('id')->get() as $sourceTheme) {
                if (! isset($pageMap[$sourceTheme->page_id])) {
                    continue;
                }

                ThemeTemplate::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'page_id' => $pageMap[$sourceTheme->page_id],
                    'title' => $sourceTheme->title,
                    'slug' => $this->uniqueSlug(ThemeTemplate::class, $sourceTheme->title, 'tema'),
                    'type' => $sourceTheme->type,
                    'status' => $sourceTheme->status,
                    'conditions' => $sourceTheme->conditions,
                    'order' => $sourceTheme->order,
                    'published_at' => $sourceTheme->published_at,
                ]);
            }

            foreach ($source->popups()->orderBy('id')->get() as $sourcePopup) {
                if (! isset($pageMap[$sourcePopup->page_id])) {
                    continue;
                }

                Popup::create([
                    'user_id' => $user->id,
                    'project_id' => $project->id,
                    'page_id' => $pageMap[$sourcePopup->page_id],
                    'title' => $sourcePopup->title,
                    'slug' => $this->uniqueSlug(Popup::class, $sourcePopup->title, 'popup'),
                    'type' => $sourcePopup->type,
                    'status' => $sourcePopup->status,
                    'triggers' => $sourcePopup->triggers,
                    'settings' => $sourcePopup->settings,
                    'conditions' => $sourcePopup->conditions,
                    'order' => $sourcePopup->order,
                    'published_at' => $sourcePopup->published_at,
                ]);
            }

            $this->clearCaches();

            return $project->fresh();
        });
    }

    protected function createPage(User $user, Project $project, array $blueprint): Page
    {
        $page = Page::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => $blueprint['title'],
            'slug' => $this->uniqueSlug(Page::class, $blueprint['slug'] ?? $blueprint['title'], 'pagina'),
            'status' => $blueprint['status'] ?? 'draft',
            'content' => [],
            'settings' => array_merge([
                'container_width' => '1140px',
                'page_background' => '#ffffff',
                'content_padding' => '0px',
            ], $blueprint['settings'] ?? []),
            'meta_data' => $blueprint['meta_data'] ?? [],
            'template' => $blueprint['template'] ?? null,
        ]);

        $this->importElements($page, $blueprint['elements'] ?? []);

        return $page;
    }

    protected function createTemplatePage(User $user, Project $project, array $blueprint): Page
    {
        return Page::create([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'title' => $blueprint['title'],
            'slug' => $this->uniqueSlug(Page::class, $blueprint['slug'] ?? $blueprint['title'], 'pagina'),
            'status' => 'draft',
            'content' => [],
            'settings' => array_merge([
                'container_width' => 'full',
                'page_background' => '#ffffff',
                'content_padding' => '0px',
            ], $blueprint['settings'] ?? []),
            'meta_data' => ['internal' => true, 'template_type' => $blueprint['type'] ?? 'popup'],
            'template' => $blueprint['type'] ?? 'popup',
        ]);
    }

    protected function clonePage(Page $source, User $user, Project $project): Page
    {
        $page = $source->replicate();
        $page->user_id = $user->id;
        $page->project_id = $project->id;
        $page->slug = $this->uniqueSlug(Page::class, $source->slug, 'pagina');
        $page->save();

        $this->cloneElements($source, $page);

        return $page;
    }

    protected function cloneElements(Page $source, Page $target): void
    {
        $elements = $source->allElements()->get()
            ->groupBy(fn (Element $element) => $element->parent_id === null ? 'root' : (string) $element->parent_id);

        $clone = function (?int $sourceParentId, ?int $targetParentId) use (&$clone, $elements, $target): void {
            $key = $sourceParentId === null ? 'root' : (string) $sourceParentId;

            foreach (($elements[$key] ?? collect())->sortBy('order') as $sourceElement) {
                $element = $sourceElement->replicate();
                $element->page_id = $target->id;
                $element->parent_id = $targetParentId;
                $element->uuid = (string) Str::uuid();
                $element->save();

                $clone($sourceElement->id, $element->id);
            }
        };

        $clone(null, null);
    }

    protected function importElements(Page $page, array $elements, ?int $parentId = null): void
    {
        foreach (array_values($elements) as $index => $data) {
            $element = Element::create([
                'page_id' => $page->id,
                'parent_id' => $parentId,
                'uuid' => (string) Str::uuid(),
                'type' => $data['type'],
                'name' => $data['settings']['name'] ?? ucfirst($data['type']),
                'order' => $data['order'] ?? $index,
                'settings' => $data['settings'] ?? [],
                'content' => $data['content'] ?? [],
                'styles' => $data['styles'] ?? [],
                'responsive_settings' => $data['responsive_settings'] ?? [],
                'animation' => $data['animation'] ?? null,
                'effects' => $data['effects'] ?? null,
                'column_size' => $data['column_size'] ?? 'col-12',
                'css_classes' => $data['css_classes'] ?? null,
                'css_id' => $data['css_id'] ?? null,
            ]);

            if (! empty($data['children'])) {
                $this->importElements($page, $data['children'], $element->id);
            }
        }
    }

    protected function uniqueSlug(string $modelClass, string $base, string $fallback): string
    {
        $root = Str::slug($base) ?: $fallback;
        $slug = $root;
        $i = 2;

        while ($modelClass::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $root.'-'.$i++;
        }

        return $slug;
    }

    protected function themeStatus(string $type): string
    {
        if (! in_array($type, ['header', 'footer'], true)) {
            return 'published';
        }

        return ThemeTemplate::published()->byType($type)->exists() ? 'draft' : 'published';
    }

    protected function clearCaches(): void
    {
        $this->themeService->clearCache();
        $this->popupService->clearCache();
    }

    protected function pageBlueprints(): array
    {
        return [
            $this->homePage(),
            $this->aboutPage(),
            $this->contactPage(),
            $this->notFoundPage(),
        ];
    }

    protected function themeBlueprints(): array
    {
        return [
            [
                'title' => 'Header Principal',
                'slug' => 'header-principal',
                'type' => 'header',
                'conditions' => [['type' => 'all']],
                'order' => 0,
                'settings' => ['container_width' => 'full', 'page_background' => '#0b1120', 'content_padding' => '0px'],
            ],
            [
                'title' => 'Rodapé Principal',
                'slug' => 'rodape-principal',
                'type' => 'footer',
                'conditions' => [['type' => 'all']],
                'order' => 0,
                'settings' => ['container_width' => 'full', 'page_background' => '#020617', 'content_padding' => '0px'],
            ],
            [
                'title' => 'Artigo Principal',
                'slug' => 'artigo-principal',
                'type' => 'single',
                'conditions' => [['type' => 'singular']],
                'order' => 0,
                'settings' => ['container_width' => '800px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
            ],
            [
                'title' => 'Listagem Principal',
                'slug' => 'listagem-principal',
                'type' => 'archive',
                'conditions' => [],
                'order' => 0,
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
            ],
        ];
    }

    protected function homePage(): array
    {
        return [
            'title' => 'Início',
            'slug' => 'inicio',
            'status' => 'published',
            'template' => 'landing',
            'meta_data' => ['description' => 'Página inicial do site institucional de exemplo.'],
            'elements' => [
                $this->section([
                    'layout' => 'full_width',
                    'background_type' => 'gradient',
                    'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#0f172a', 'color2' => '#312e81', 'position1' => 0, 'position2' => 100],
                    'padding_top' => '110px',
                    'padding_bottom' => '110px',
                ], [
                    $this->column('col-7', [
                        $this->callout('Plataforma completa para criar sites profissionais', [
                            'type' => 'info',
                            'title' => 'Modelo de Projeto',
                            'icon' => 'fas fa-layer-group',
                            'border_radius' => '50px',
                            'margin_bottom' => '20px',
                        ]),
                        $this->heading('Construa seu próximo site com uma base pronta e editável', [
                            'tag' => 'h1',
                            'size' => 'xxl',
                            'color' => '#ffffff',
                            'font_weight' => '800',
                            'letter_spacing' => '-1px',
                            'margin_bottom' => '20px',
                        ]),
                        $this->text('<p style="color:#cbd5e1;font-size:1.15rem;line-height:1.8;">Este projeto reúne páginas, header, footer, artigo, listagem e popup. Personalize tudo no editor visual e use este modelo como ponto de partida.</p>', [
                            'font_size' => '18px',
                            'margin_bottom' => '30px',
                        ]),
                        $this->button('Explorar o projeto', [
                            'link' => '#recursos',
                            'background_type' => 'gradient',
                            'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#ec4899', 'position1' => 0, 'position2' => 100],
                            'border_radius' => '50px',
                            'padding_left_right' => '30px',
                            'font_weight' => '600',
                        ]),
                        $this->button('Falar com a equipe', [
                            'link' => '/p/contato',
                            'background_type' => 'classic',
                            'background_color' => 'transparent',
                            'text_color' => '#e0e7ff',
                            'border_color' => 'rgba(255,255,255,.45)',
                            'border_width' => '1px',
                            'border_radius' => '50px',
                            'padding_left_right' => '30px',
                            'font_weight' => '600',
                        ]),
                    ], ['padding_right' => '30px']),
                    $this->column('col-5', [
                        $this->image('https://picsum.photos/seed/project-template-hero/900/700', 'Equipe trabalhando em um novo projeto', [
                            'border_radius' => '20px',
                            'box_shadow' => '0 30px 80px rgba(0,0,0,.35)',
                        ]),
                    ]),
                ]),
                $this->section(['background_color' => '#ffffff'], [
                    $this->column('col-12', [
                        $this->heading('Tudo o que você precisa para começar', ['alignment' => 'center', 'size' => 'xl']),
                        $this->text('<p style="color:#64748b;">Uma estrutura completa, responsiva e pronta para receber a identidade da sua marca.</p>', [
                            'alignment' => 'center',
                            'margin_bottom' => '40px',
                        ]),
                    ], ['text_align' => 'center']),
                    $this->column('col-4', [
                        $this->iconBox('fas fa-bolt', 'Rápido de personalizar', '<p style="color:#64748b;">Troque textos, imagens e cores diretamente no editor visual, sem tocar em código.</p>'),
                    ]),
                    $this->column('col-4', [
                        $this->iconBox('fas fa-mobile-alt', 'Responsivo por padrão', '<p style="color:#64748b;">Layouts que se adaptam a celulares, tablets e desktops automaticamente.</p>'),
                    ]),
                    $this->column('col-4', [
                        $this->iconBox('fas fa-puzzle-piece', 'Widgets reutilizáveis', '<p style="color:#64748b;">Seções, formulários, mapas, ícones e muito mais para montar qualquer página.</p>'),
                    ]),
                ]),
                $this->section(['background_color' => '#f8fafc'], [
                    $this->column('col-6', [
                        $this->image('https://picsum.photos/seed/project-template-about/900/700', 'Reunião de planejamento', [
                            'border_radius' => '18px',
                        ]),
                    ], ['padding_right' => '30px']),
                    $this->column('col-6', [
                        $this->heading('Uma base pensada para projetos reais', ['size' => 'xl']),
                        $this->text('<p style="color:#475569;line-height:1.8;">O projeto de exemplo já inclui as páginas essenciais de um site institucional e uma identidade visual consistente. Duplique o projeto e substitua o conteúdo para lançar um novo site em minutos.</p>', [
                            'margin_bottom' => '24px',
                        ]),
                        $this->callout('Estrutura modular e fácil de manter', [
                            'type' => 'success',
                            'title' => 'Incluído no modelo',
                            'content' => '<p>Páginas Início, Sobre, Contato e 404, além de header, footer, artigo, listagem e popup de newsletter.</p>',
                        ]),
                        $this->button('Conhecer a estrutura', [
                            'link' => '/p/sobre',
                            'border_radius' => '8px',
                            'padding_left_right' => '24px',
                        ]),
                    ]),
                ]),
                $this->section(['background_color' => '#0f172a', 'padding_top' => '70px', 'padding_bottom' => '70px'], [
                    $this->column('col-3', [$this->counter('Páginas prontas', '4', '+')]),
                    $this->column('col-3', [$this->counter('Templates de tema', '4', '')]),
                    $this->column('col-3', [$this->counter('Widgets disponíveis', '30', '+')]),
                    $this->column('col-3', [$this->counter('Tempo para começar', '5', ' min')]),
                ]),
                $this->section(['background_color' => '#ffffff'], [
                    $this->column('col-12', [
                        $this->heading('O que nossos clientes dizem', ['alignment' => 'center', 'size' => 'xl']),
                        $this->text('<p style="color:#64748b;">Resultados reais de equipes que começaram com uma base sólida.</p>', [
                            'alignment' => 'center',
                            'margin_bottom' => '40px',
                        ]),
                    ], ['text_align' => 'center']),
                    $this->column('col-4', [
                        $this->testimonial('“Conseguimos lançar o site institucional em poucos dias. A estrutura do modelo economizou semanas de trabalho.”', 'Ana Martins', 'Diretora de Marketing', 'https://picsum.photos/seed/avatar-ana/160/160'),
                    ]),
                    $this->column('col-4', [
                        $this->testimonial('“A organização em projetos facilita muito a vida da equipe. Cada cliente tem seu próprio espaço.”', 'Bruno Costa', 'Fundador', 'https://picsum.photos/seed/avatar-bruno/160/160'),
                    ]),
                    $this->column('col-4', [
                        $this->testimonial('“O editor é simples e poderoso. Mesmo quem não é desenvolvedor consegue manter o site atualizado.”', 'Carla Souza', 'Gerente de Conteúdo', 'https://picsum.photos/seed/avatar-carla/160/160'),
                    ]),
                ]),
                $this->section([
                    'layout' => 'full_width',
                    'background_type' => 'gradient',
                    'background_gradient' => ['type' => 'linear', 'angle' => 120, 'color1' => '#4338ca', 'color2' => '#be185d', 'position1' => 0, 'position2' => 100],
                    'padding_top' => '80px',
                    'padding_bottom' => '80px',
                ], [
                    $this->column('col-12', [
                        $this->heading('Pronto para criar o seu próximo projeto?', ['alignment' => 'center', 'size' => 'xl', 'color' => '#ffffff']),
                        $this->text('<p style="color:#e0e7ff;">Duplique este projeto, ajuste a identidade visual e publique.</p>', [
                            'alignment' => 'center',
                            'margin_bottom' => '28px',
                        ]),
                        $this->button('Começar agora', [
                            'link' => '/p/contato',
                            'background_color' => '#ffffff',
                            'text_color' => '#3730a3',
                            'border_radius' => '50px',
                            'padding_left_right' => '34px',
                            'font_weight' => '700',
                            'alignment' => 'center',
                        ]),
                    ], ['text_align' => 'center']),
                ]),
            ],
        ];
    }

    protected function aboutPage(): array
    {
        return [
            'title' => 'Sobre',
            'slug' => 'sobre',
            'status' => 'published',
            'template' => 'about',
            'meta_data' => ['description' => 'Conheça a história, os valores e a equipe do site de exemplo.'],
            'elements' => [
                $this->section([
                    'background_type' => 'gradient',
                    'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#eef2ff', 'color2' => '#fdf2f8', 'position1' => 0, 'position2' => 100],
                    'padding_top' => '80px',
                    'padding_bottom' => '80px',
                ], [
                    $this->column('col-12', [
                        $this->callout('Quem somos', ['type' => 'info', 'title' => 'Nossa história', 'border_radius' => '50px']),
                        $this->heading('Criamos experiências digitais que conectam marcas e pessoas', ['tag' => 'h1', 'size' => 'xxl', 'alignment' => 'center', 'margin_bottom' => '20px']),
                        $this->text('<p style="color:#475569;font-size:1.1rem;line-height:1.8;">Somos uma equipe multidisciplinar apaixonada por design, tecnologia e resultados. Este conteúdo faz parte do projeto de exemplo e pode ser totalmente personalizado.</p>', [
                            'alignment' => 'center',
                            'font_size' => '18px',
                        ]),
                    ], ['text_align' => 'center']),
                ]),
                $this->section(['background_color' => '#ffffff'], [
                    $this->column('col-6', [
                        $this->image('https://picsum.photos/seed/project-template-team/900/700', 'Equipe reunida no escritório', ['border_radius' => '18px']),
                    ], ['padding_right' => '30px']),
                    $this->column('col-6', [
                        $this->heading('Uma parceria de longo prazo', ['size' => 'xl']),
                        $this->text('<p style="color:#475569;line-height:1.8;">Acreditamos em relações transparentes e em soluções construídas junto com cada cliente. Do planejamento à publicação, acompanhamos todas as etapas do projeto.</p>'),
                        $this->text('<p style="color:#475569;line-height:1.8;"><strong>Nossa missão</strong> é tornar a criação de sites simples, acessível e sustentável para equipes de todos os tamanhos.</p>', [
                            'margin_bottom' => '24px',
                        ]),
                        $this->button('Entre em contato', ['link' => '/p/contato', 'border_radius' => '8px']),
                    ]),
                ]),
                $this->section(['background_color' => '#f8fafc'], [
                    $this->column('col-12', [
                        $this->heading('Nossos valores', ['alignment' => 'center', 'size' => 'xl']),
                        $this->text('<p style="color:#64748b;">Princípios que guiam cada projeto que assumimos.</p>', [
                            'alignment' => 'center',
                            'margin_bottom' => '40px',
                        ]),
                    ], ['text_align' => 'center']),
                    $this->column('col-4', [
                        $this->iconBox('fas fa-handshake', 'Transparência', '<p style="color:#64748b;">Comunicação clara, prazos realistas e acompanhamento próximo em todas as etapas.</p>'),
                    ]),
                    $this->column('col-4', [
                        $this->iconBox('fas fa-lightbulb', 'Criatividade', '<p style="color:#64748b;">Soluções originais que respeitam a identidade e os objetivos de cada marca.</p>'),
                    ]),
                    $this->column('col-4', [
                        $this->iconBox('fas fa-chart-line', 'Resultados', '<p style="color:#64748b;">Sites pensados para gerar valor, conversões e crescimento sustentável.</p>'),
                    ]),
                ]),
                $this->section(['background_color' => '#0f172a', 'padding_top' => '70px', 'padding_bottom' => '70px'], [
                    $this->column('col-3', [$this->counter('Projetos entregues', '120', '+')]),
                    $this->column('col-3', [$this->counter('Clientes atendidos', '80', '+')]),
                    $this->column('col-3', [$this->counter('Anos de experiência', '10', '')]),
                    $this->column('col-3', [$this->counter('Satisfação', '98', '%')]),
                ]),
                $this->section(['background_color' => '#ffffff'], [
                    $this->column('col-12', [
                        $this->heading('Conheça a equipe', ['alignment' => 'center', 'size' => 'xl']),
                        $this->text('<p style="color:#64748b;">Pessoas que transformam ideias em experiências digitais.</p>', [
                            'alignment' => 'center',
                            'margin_bottom' => '40px',
                        ]),
                    ], ['text_align' => 'center']),
                    $this->column('col-4', [
                        $this->imageBox('https://picsum.photos/seed/team-ana/500/500', 'Ana Martins', '<p style="color:#64748b;">Diretora de Marketing</p>'),
                    ]),
                    $this->column('col-4', [
                        $this->imageBox('https://picsum.photos/seed/team-bruno/500/500', 'Bruno Costa', '<p style="color:#64748b;">Fundador e Estrategista</p>'),
                    ]),
                    $this->column('col-4', [
                        $this->imageBox('https://picsum.photos/seed/team-carla/500/500', 'Carla Souza', '<p style="color:#64748b;">Gerente de Conteúdo</p>'),
                    ]),
                ]),
                $this->section(['layout' => 'full_width', 'background_color' => '#4338ca', 'padding_top' => '70px', 'padding_bottom' => '70px'], [
                    $this->column('col-12', [
                        $this->heading('Vamos construir algo juntos?', ['alignment' => 'center', 'size' => 'xl', 'color' => '#ffffff']),
                        $this->text('<p style="color:#e0e7ff;margin-bottom:24px;">Conte-nos sobre o seu projeto e descubra como podemos ajudar.</p>', ['alignment' => 'center']),
                        $this->button('Solicitar orçamento', [
                            'link' => '/p/contato',
                            'background_color' => '#ffffff',
                            'text_color' => '#3730a3',
                            'border_radius' => '50px',
                            'alignment' => 'center',
                        ]),
                    ], ['text_align' => 'center']),
                ]),
            ],
        ];
    }

    protected function contactPage(): array
    {
        return [
            'title' => 'Contato',
            'slug' => 'contato',
            'status' => 'published',
            'template' => 'contact',
            'meta_data' => ['description' => 'Fale com a equipe do site de exemplo.'],
            'elements' => [
                $this->section([
                    'background_type' => 'gradient',
                    'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#0f172a', 'color2' => '#4338ca', 'position1' => 0, 'position2' => 100],
                    'padding_top' => '80px',
                    'padding_bottom' => '80px',
                ], [
                    $this->column('col-12', [
                        $this->heading('Fale com a nossa equipe', ['tag' => 'h1', 'size' => 'xxl', 'alignment' => 'center', 'color' => '#ffffff']),
                        $this->text('<p style="color:#c7d2fe;font-size:1.1rem;">Estamos prontos para ouvir você. Preencha o formulário ou use um dos nossos canais de atendimento.</p>', [
                            'alignment' => 'center',
                            'font_size' => '18px',
                        ]),
                    ], ['text_align' => 'center']),
                ]),
                $this->section(['background_color' => '#f8fafc', 'padding_top' => '70px', 'padding_bottom' => '70px'], [
                    $this->column('col-7', [
                        $this->heading('Envie uma mensagem', ['size' => 'large']),
                        $this->text('<p style="color:#64748b;">Responderemos em até um dia útil.</p>', ['margin_bottom' => '24px']),
                        $this->form('Contato', [
                            ['label' => 'Nome completo', 'placeholder' => 'Seu nome', 'type' => 'text', 'required' => true],
                            ['label' => 'E-mail', 'placeholder' => 'seu@email.com', 'type' => 'email', 'required' => true],
                            ['label' => 'Telefone', 'placeholder' => '(00) 00000-0000', 'type' => 'tel', 'required' => false],
                            ['label' => 'Assunto', 'placeholder' => 'Como podemos ajudar?', 'type' => 'text', 'required' => true],
                            ['label' => 'Mensagem', 'placeholder' => 'Escreva sua mensagem', 'type' => 'textarea', 'required' => true, 'width' => '100'],
                        ], [
                            'button_text' => 'Enviar mensagem',
                            'success_message' => 'Mensagem enviada com sucesso! Entraremos em contato em breve.',
                        ]),
                    ], [
                        'background_color' => '#ffffff',
                        'padding_top' => '32px',
                        'padding_bottom' => '32px',
                        'padding_left' => '32px',
                        'padding_right' => '32px',
                        'border_radius' => '16px',
                        'box_shadow' => '0 12px 40px rgba(15,23,42,.08)',
                    ]),
                    $this->column('col-5', [
                        $this->heading('Informações de contato', ['size' => 'large']),
                        $this->callout('Nossos canais', [
                            'type' => 'info',
                            'title' => 'Atendimento',
                            'content' => '<p><strong>Endereço:</strong> Av. Paulista, 1000 — São Paulo, SP<br><strong>Telefone:</strong> (11) 99999-9999<br><strong>E-mail:</strong> contato@exemplo.com<br><strong>Horário:</strong> Segunda a sexta, 9h às 18h</p>',
                            'margin_bottom' => '20px',
                        ]),
                        $this->googleMaps('Av. Paulista, 1000, São Paulo, SP', [
                            'height' => '300px',
                            'border_radius' => '16px',
                        ]),
                        $this->socialIcons([
                            ['platform' => 'instagram', 'url' => 'https://instagram.com', 'color' => '#E4405F'],
                            ['platform' => 'linkedin', 'url' => 'https://linkedin.com', 'color' => '#0A66C2'],
                            ['platform' => 'whatsapp', 'url' => 'https://wa.me/5511999999999', 'color' => '#25D366'],
                        ], ['alignment' => 'left']),
                    ], ['padding_left' => '30px']),
                ]),
            ],
        ];
    }

    protected function notFoundPage(): array
    {
        return [
            'title' => 'Página não encontrada',
            'slug' => '404',
            'status' => 'published',
            'template' => '404',
            'meta_data' => ['description' => 'Página de erro 404 do site de exemplo.'],
            'elements' => [
                $this->section([
                    'layout' => 'full_width',
                    'background_type' => 'gradient',
                    'background_gradient' => ['type' => 'radial', 'angle' => 0, 'color1' => '#1e1b4b', 'color2' => '#020617', 'position1' => 0, 'position2' => 100],
                    'padding_top' => '120px',
                    'padding_bottom' => '120px',
                ], [
                    $this->column('col-12', [
                        $this->heading('404', ['tag' => 'h1', 'size' => 'xxl', 'color' => '#818cf8', 'alignment' => 'center', 'font_weight' => '800']),
                        $this->heading('Página não encontrada', ['tag' => 'h2', 'size' => 'large', 'color' => '#ffffff', 'alignment' => 'center']),
                        $this->text('<p style="color:#94a3b8;">O endereço que você tentou acessar não existe ou foi movido. Volte para a página inicial para continuar navegando.</p>', [
                            'alignment' => 'center',
                            'margin_bottom' => '28px',
                        ]),
                        $this->button('Voltar para a home', [
                            'link' => '/p/inicio',
                            'background_type' => 'gradient',
                            'background_gradient' => ['type' => 'linear', 'angle' => 135, 'color1' => '#6366f1', 'color2' => '#a855f7', 'position1' => 0, 'position2' => 100],
                            'border_radius' => '50px',
                            'padding_left_right' => '32px',
                            'alignment' => 'center',
                        ]),
                    ], ['text_align' => 'center']),
                ]),
            ],
        ];
    }

    protected function section(array $settings, array $children): array
    {
        return [
            'type' => 'section',
            'settings' => array_merge([
                'layout' => 'boxed',
                'gap' => 'default',
                'flex_wrap' => 'wrap',
                'align_items' => 'stretch',
                'justify_content' => 'flex-start',
                'background_type' => 'classic',
                'background_color' => '#ffffff',
                'background_image' => [],
                'background_overlay' => '',
                'background_position' => 'center center',
                'background_size' => 'cover',
                'background_repeat' => 'no-repeat',
                'padding_top' => '80px',
                'padding_bottom' => '80px',
                'padding_left' => '0px',
                'padding_right' => '0px',
                'margin_top' => '0px',
                'margin_bottom' => '0px',
                'border_radius' => '0px',
                'box_shadow' => 'none',
                'z_index' => 'auto',
                'css_id' => '',
                'css_classes' => '',
                'parallax' => false,
                'parallax_speed' => 0.5,
                'video_background' => [],
            ], $settings),
            'children' => $children,
        ];
    }

    protected function column(string $width, array $children, array $settings = []): array
    {
        return [
            'type' => 'column',
            'settings' => array_merge([
                'column_width' => $width,
                'vertical_alignment' => 'top',
                'text_align' => 'left',
                'padding_top' => '0px',
                'padding_bottom' => '0px',
                'padding_left' => '0px',
                'padding_right' => '0px',
                'background_color' => 'transparent',
                'border_radius' => '0px',
                'box_shadow' => 'none',
            ], $settings),
            'children' => $children,
        ];
    }

    protected function heading(string $title, array $settings = []): array
    {
        return [
            'type' => 'heading',
            'settings' => array_merge([
                'title' => $title,
                'tag' => 'h2',
                'alignment' => 'left',
                'size' => 'default',
                'color' => '#0f172a',
                'font_family' => '',
                'font_weight' => '700',
                'letter_spacing' => 'normal',
                'line_height' => '1.2',
                'margin_bottom' => '16px',
                'link' => '',
                'link_target' => '_self',
                'enable_animation' => false,
                'animation_type' => 'fadeIn',
            ], $settings),
        ];
    }

    protected function text(string $content, array $settings = []): array
    {
        return [
            'type' => 'text',
            'settings' => array_merge([
                'content' => $content,
                'alignment' => 'left',
                'color' => '#475569',
                'font_size' => '16px',
                'font_family' => '',
                'font_weight' => '400',
                'line_height' => '1.7',
                'margin_bottom' => '16px',
                'drop_cap' => false,
                'column_count' => 1,
                'column_gap' => 'normal',
            ], $settings),
        ];
    }

    protected function button(string $text, array $settings = []): array
    {
        return [
            'type' => 'button',
            'settings' => array_merge([
                'text' => $text,
                'link' => '#',
                'link_target' => '_self',
                'alignment' => 'left',
                'size' => 'medium',
                'full_width' => false,
                'background_type' => 'classic',
                'background_color' => '#4338ca',
                'background_color_hover' => '#3730a3',
                'text_color' => '#ffffff',
                'text_color_hover' => '#ffffff',
                'border_color' => 'transparent',
                'border_color_hover' => 'transparent',
                'border_radius' => '8px',
                'border_width' => '0px',
                'padding_top_bottom' => '13px',
                'padding_left_right' => '26px',
                'font_size' => '15px',
                'font_weight' => '600',
                'icon' => '',
                'icon_position' => 'left',
                'icon_gap' => '8px',
                'hover_animation' => 'none',
                'css_id' => '',
                'css_classes' => '',
            ], $settings),
        ];
    }

    protected function image(string $url, string $alt, array $settings = []): array
    {
        return [
            'type' => 'image',
            'settings' => array_merge([
                'image' => ['url' => $url, 'alt' => $alt, 'width' => 900, 'height' => 700],
                'width' => '100%',
                'border_radius' => '12px',
                'object_fit' => 'cover',
                'alignment' => 'center',
            ], $settings),
        ];
    }

    protected function iconBox(string $icon, string $title, string $description, array $settings = []): array
    {
        return [
            'type' => 'icon_box',
            'settings' => array_merge([
                'icon' => $icon,
                'icon_size' => '36',
                'icon_color' => '#6366f1',
                'title' => $title,
                'title_color' => '#0f172a',
                'description' => $description,
                'alignment' => 'center',
            ], $settings),
        ];
    }

    protected function counter(string $title, string $number, string $suffix = ''): array
    {
        return [
            'type' => 'counter',
            'settings' => [
                'title' => $title,
                'number' => $number,
                'suffix' => $suffix,
                'duration' => '2000',
                'separator' => false,
                'alignment' => 'center',
                'color' => '#818cf8',
                'font_size' => '2.5rem',
            ],
        ];
    }

    protected function testimonial(string $content, string $name, string $position, string $avatar): array
    {
        return [
            'type' => 'testimonial',
            'settings' => [
                'content' => $content,
                'name' => $name,
                'position' => $position,
                'company' => '',
                'avatar' => ['url' => $avatar, 'alt' => $name, 'width' => 160, 'height' => 160],
                'rating' => '5',
                'alignment' => 'center',
                'text_color' => '#475569',
                'name_color' => '#0f172a',
            ],
        ];
    }

    protected function imageBox(string $url, string $title, string $description): array
    {
        return [
            'type' => 'image_box',
            'settings' => [
                'image' => ['url' => $url, 'alt' => $title, 'width' => 500, 'height' => 500],
                'title' => $title,
                'title_color' => '#0f172a',
                'description' => $description,
                'alignment' => 'center',
                'border_radius' => '50%',
            ],
        ];
    }

    protected function callout(string $content, array $settings = []): array
    {
        return [
            'type' => 'callout',
            'settings' => array_merge([
                'type' => 'info',
                'title' => '',
                'content' => $content,
                'icon' => 'fas fa-info-circle',
                'show_icon' => true,
                'border_radius' => '10px',
                'padding' => '18px',
                'margin_bottom' => '16px',
            ], $settings),
        ];
    }

    protected function form(string $name, array $fields, array $settings = []): array
    {
        return [
            'type' => 'form',
            'settings' => array_merge([
                'form_name' => $name,
                'fields' => $fields,
                'button_text' => 'Enviar',
                'button_color' => '#4338ca',
                'button_text_color' => '#ffffff',
                'button_width' => 'full',
                'success_message' => 'Enviado com sucesso!',
                'field_spacing' => 16,
                'field_radius' => 8,
            ], $settings),
        ];
    }

    protected function googleMaps(string $address, array $settings = []): array
    {
        return [
            'type' => 'google_maps',
            'settings' => array_merge([
                'address' => $address,
                'zoom' => '15',
                'height' => '300px',
                'map_type' => 'roadmap',
                'border_radius' => '12px',
            ], $settings),
        ];
    }

    protected function socialIcons(array $icons, array $settings = []): array
    {
        return [
            'type' => 'social_icons',
            'settings' => array_merge([
                'icons' => $icons,
                'columns' => (string) count($icons),
                'icon_size' => '36',
                'gap' => '12',
                'alignment' => 'left',
            ], $settings),
        ];
    }
}
