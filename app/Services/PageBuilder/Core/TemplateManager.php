<?php

namespace App\Services\PageBuilder\Core;

use App\Models\Page;
use App\Models\Element;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TemplateManager
{
    protected array $templates;

    public function __construct()
    {
        $this->templates = $this->defaultTemplates();
    }

    public function all(): array
    {
        return $this->templates;
    }

    public function list(): array
    {
        $list = [];
        foreach ($this->templates as $key => $tmpl) {
            $list[$key] = ['name' => $tmpl['name'], 'description' => $tmpl['description']];
        }
        return $list;
    }

    public function has(string $key): bool
    {
        return isset($this->templates[$key]);
    }

    public function get(string $key): ?array
    {
        return $this->templates[$key] ?? null;
    }

    public function apply(Page $page, string $templateKey): Page
    {
        $template = $this->get($templateKey);

        if (!$template) {
            throw new \InvalidArgumentException("Template '{$templateKey}' not found");
        }

        DB::beginTransaction();
        try {
            $page->elements()->delete();
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();

            $this->importElements($page, $template['elements']);

            DB::commit();

            return $page;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function importToPage(Page $page, string $templateKey): void
    {
        $template = $this->get($templateKey);
        if (!$template) return;

        $page->settings = array_merge($page->settings ?? [], $template['settings']);
        $page->save();

        $this->importElements($page, $template['elements']);
    }

    protected function importElements(Page $page, array $elements, ?int $parentId = null, ?string $widgetType = null): void
    {
        foreach ($elements as $index => $elData) {
            $type = $widgetType ?? $elData['type'];
            $children = $elData['children'] ?? [];

            $element = new Element();
            $element->page_id = $page->id;
            $element->parent_id = $parentId;
            $element->type = $type;
            $element->uuid = (string) Str::uuid();
            $element->name = $elData['settings']['name'] ?? ucfirst($type);
            $element->settings = $elData['settings'] ?? [];
            $element->content = [];
            $element->styles = [];
            $element->order = $index;
            $element->save();

            if ($children) {
                $childType = $type === 'section' ? 'column' : null;
                $this->importElements($page, $children, $element->id, $childType);
            }
        }
    }

    protected function defaultTemplates(): array
    {
        return [
            'blank' => [
                'name' => 'Blank Page',
                'description' => 'Start from scratch',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [],
            ],
            'landing' => [
                'name' => 'Landing Page',
                'description' => 'Hero section with CTA',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#1a1a2e', 'padding_top' => '100px', 'padding_bottom' => '100px', 'min_height' => '80vh'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Welcome to Your New Website', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.2rem;color:#cdd6f4;">Build beautiful pages with our drag-and-drop builder</p>', 'alignment' => 'center']],
                            ['type' => 'button', 'settings' => ['text' => 'Get Started', 'link' => '#', 'background_color' => '#007bff', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Fast', 'tag' => 'h3', 'size' => 'medium', 'color' => '#333', 'alignment' => 'center']],
                            ['type' => 'text', 'settings' => ['content' => '<p>Optimized for speed and performance</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Flexible', 'tag' => 'h3', 'size' => 'medium', 'color' => '#333', 'alignment' => 'center']],
                            ['type' => 'text', 'settings' => ['content' => '<p>Drag and drop to build anything</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Modern', 'tag' => 'h3', 'size' => 'medium', 'color' => '#333', 'alignment' => 'center']],
                            ['type' => 'text', 'settings' => ['content' => '<p>Built with the latest technology</p>', 'alignment' => 'center']],
                        ]],
                    ]],
                ],
            ],
            'about' => [
                'name' => 'About Page',
                'description' => 'Company presentation',
                'settings' => ['container_width' => '960px', 'page_background' => '#f8f9fa', 'content_padding' => '40px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '60px', 'padding_bottom' => '60px', 'border_radius' => '8px', 'margin_bottom' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'About Us', 'tag' => 'h1', 'size' => 'large', 'color' => '#333', 'alignment' => 'left']],
                            ['type' => 'text', 'settings' => ['content' => '<p>We are a passionate team dedicated to delivering exceptional digital experiences. Our mission is to empower creators with intuitive tools that bring their ideas to life.</p>', 'font_size' => '16px', 'line_height' => '1.8']],
                        ]],
                    ]],
                ],
            ],
            'contact' => [
                'name' => 'Contact Page',
                'description' => 'Contact form layout',
                'settings' => ['container_width' => '800px', 'page_background' => '#f0f2f5', 'content_padding' => '40px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '60px', 'padding_bottom' => '60px', 'border_radius' => '8px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Get in Touch', 'tag' => 'h1', 'size' => 'large', 'color' => '#333', 'alignment' => 'center']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="text-align:center;color:#666;">We would love to hear from you. Send us a message and we will respond as soon as possible.</p>']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Our Office', 'tag' => 'h3', 'size' => 'small', 'color' => '#333']],
                            ['type' => 'text', 'settings' => ['content' => '<p>123 Main Street<br>New York, NY 10001<br>United States</p>']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Send a Message', 'tag' => 'h3', 'size' => 'small', 'color' => '#333']],
                            ['type' => 'text', 'settings' => ['content' => '<p>Use the form below or reach out directly via email.</p>']],
                            ['type' => 'button', 'settings' => ['text' => 'Email Us', 'link' => 'mailto:hello@example.com', 'background_color' => '#007bff', 'text_color' => '#ffffff', 'size' => 'medium']],
                        ]],
                    ]],
                ],
            ],
            'moodle-course' => [
                'name' => 'Curso Moodle',
                'description' => 'Template educacional completo para cursos no Moodle 4.5 com cabeçalho, objetivos, cronograma, conteúdo, callouts, tabelas e contato',
                'settings' => ['container_width' => '960px', 'page_background' => '#f4f6f9', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#1d3b5c', 'padding_top' => '70px', 'padding_bottom' => '70px', 'min_height' => 'auto', 'align_items' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Nome do Curso', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.15rem;color:#cbd5e1;max-width:700px;margin:0 auto;">Breve descrição do curso. Informe o assunto, nível e principais competências desenvolvidas.</p>', 'alignment' => 'center', 'font_size' => '18px', 'line_height' => '1.7']],
                            ['type' => 'button', 'settings' => ['text' => 'Inscreva-se Agora', 'link' => '#', 'background_color' => '#f39c12', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '36px', 'font_weight' => '600', 'margin_top' => '16px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '50px', 'padding_bottom' => '50px', 'border_radius' => '8px', 'margin_top' => '-30px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Sobre o Curso', 'tag' => 'h2', 'size' => 'large', 'color' => '#1d3b5c', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p>Este curso foi desenvolvido para fornecer uma base sólida sobre o tema. Ao longo das aulas, você aprenderá conceitos fundamentais e práticas aplicadas com exemplos reais.</p><p>Não são necessários conhecimentos prévios específicos. Todo o material didático está incluído e acessível diretamente na plataforma Moodle.</p>', 'font_size' => '16px', 'line_height' => '1.8']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '8px', 'margin_top' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Objetivos de Aprendizagem', 'tag' => 'h2', 'size' => 'large', 'color' => '#1d3b5c', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '20px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_bottom' => '12px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'info', 'title' => 'Conhecimento Teórico', 'content' => '<p>Compreender os fundamentos e conceitos essenciais da disciplina, com base em referências atualizadas.</p>']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_bottom' => '12px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'success', 'title' => 'Habilidades Práticas', 'content' => '<p>Desenvolver competências aplicadas por meio de exercícios, estudos de caso e projetos guiados.</p>']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_bottom' => '12px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'warning', 'title' => 'Avaliação', 'content' => '<p>Avaliação contínua com quizzes, tarefas práticas e projeto final para certificação.</p>']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'text_align' => 'left', 'padding_bottom' => '12px'], 'children' => [
                            ['type' => 'callout', 'settings' => ['type' => 'danger', 'title' => 'Pré-requisitos', 'content' => '<p>Conhecimentos básicos na área são recomendados, mas não obrigatórios. Venha aprender conosco!</p>']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '8px', 'margin_top' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Cronograma do Curso', 'tag' => 'h2', 'size' => 'large', 'color' => '#1d3b5c', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'table', 'settings' => ['headings' => 'Módulo;Conteúdo;Carga Horária', 'rows' => "Módulo 1;Introdução e conceitos fundamentais;4h\nMódulo 2;Aprofundamento teórico;6h\nMódulo 3;Exercícios práticos e estudos de caso;8h\nMódulo 4;Projeto final e avaliação;6h"]],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '20px', 'border_radius' => '8px', 'margin_top' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left', 'padding_bottom' => '16px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Conteúdo Programático', 'tag' => 'h2', 'size' => 'large', 'color' => '#1d3b5c', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '20px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left', 'padding_bottom' => '16px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Módulo 1 — Fundamentos', 'tag' => 'h3', 'size' => 'medium', 'color' => '#1d3b5c', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<ul><li>Visão geral da disciplina</li><li>Principais teorias e abordagens</li><li>Contextualização histórica</li><li>Terminologia essencial</li></ul>', 'font_size' => '15px', 'line_height' => '1.8']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left', 'padding_bottom' => '16px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Módulo 2 — Aplicação Prática', 'tag' => 'h3', 'size' => 'medium', 'color' => '#1d3b5c', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<ul><li>Passo a passo para implementação</li><li>Ferramentas e recursos recomendados</li><li>Exercícios guiados com feedback</li><li>Estudo de caso real</li></ul>', 'font_size' => '15px', 'line_height' => '1.8']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left', 'padding_bottom' => '16px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Módulo 3 — Projeto Final', 'tag' => 'h3', 'size' => 'medium', 'color' => '#1d3b5c', 'alignment' => 'left', 'font_weight' => '600', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<ul><li>Definição do escopo do projeto</li><li>Desenvolvimento orientado</li><li>Apresentação e discussão dos resultados</li><li>Avaliação e certificação</li></ul>', 'font_size' => '15px', 'line_height' => '1.8']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#1d3b5c', 'padding_top' => '50px', 'padding_bottom' => '50px', 'border_radius' => '8px', 'margin_top' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Pronto para Começar?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#cbd5e1;max-width:600px;margin:0 auto 24px;">Inscreva-se agora e tenha acesso a todo o conteúdo, suporte dos instrutores e certificado ao final.</p>', 'alignment' => 'center', 'font_size' => '16px']],
                            ['type' => 'button', 'settings' => ['text' => 'Inscrever-se', 'link' => '#', 'background_color' => '#f39c12', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '30px', 'padding_bottom' => '30px', 'border_radius' => '8px', 'margin_top' => '20px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;font-size:0.85rem;">Dúvidas? Entre em contato pelo e-mail: professor@instituicao.edu.br</p>', 'alignment' => 'center']],
                        ]],
                    ]],
                ],
            ],
            'showcase' => [
                'name' => 'Showcase Completo',
                'description' => 'Template profissional completo com hero gradiente, serviços, contadores, portfólio, vídeo, depoimentos, pricing, FAQ e CTA',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => '135', 'color1' => '#0f172a', 'color2' => '#1e1b4b', 'position1' => '0', 'position2' => '100'], 'min_height' => '100vh', 'padding_top' => '0px', 'padding_bottom' => '0px', 'align_items' => 'center', 'justify_content' => 'center', 'gap' => '2rem', 'shape_divider_bottom' => 'waves', 'shape_divider_bottom_color' => '#ffffff', 'shape_divider_bottom_height' => '120'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Transforme Suas Ideias em', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '0px', 'font_family' => 'Inter, sans-serif', 'letter_spacing' => '-1px']],
                            ['type' => 'heading', 'settings' => ['title' => 'Experiências Extraordinárias', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#818cf8', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '28px', 'font_family' => 'Inter, sans-serif', 'letter_spacing' => '-1px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.2rem;color:#94a3b8;max-width:600px;margin:0 auto;">Criamos soluções digitais que unem design visionário, tecnologia de ponta e performance excepcional para impulsionar o seu negócio ao próximo nível.</p>', 'alignment' => 'center', 'font_size' => '20px', 'line_height' => '1.8', 'margin_bottom' => '36px']],
                            ['type' => 'button', 'settings' => ['text' => 'Comece Agora', 'link' => '#', 'background_color' => '#6366f1', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '600', 'box_shadow' => '0 4px 20px rgba(99,102,241,.4)']],
                            ['type' => 'button', 'settings' => ['text' => 'Saiba Mais', 'link' => '#', 'background_color' => 'transparent', 'text_color' => '#c7d2fe', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '500', 'border_color' => 'rgba(255,255,255,.2)', 'border_width' => '2px', 'margin_top' => '12px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '50px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#6366f1;font-weight:700;text-transform:uppercase;letter-spacing:3px;font-size:.8rem;margin-bottom:8px;">Nossos Serviços</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Tudo que você precisa para crescer', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Oferecemos um ecossistema completo de soluções para transformar sua presença digital</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '16px', 'box_shadow' => '0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.03)', 'css_classes' => 'pb-hover-lift'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-paint-brush', 'icon_size' => '32', 'icon_color' => '#6366f1', 'icon_position' => 'top', 'title' => 'Design Visionário', 'title_color' => '#0f172a', 'description' => '<p>Criamos interfaces elegantes e intuitivas que encantam usuários e impulsionam resultados.</p>', 'description_color' => '#64748b', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '16px', 'box_shadow' => '0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.03)', 'css_classes' => 'pb-hover-lift'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-bolt', 'icon_size' => '32', 'icon_color' => '#6366f1', 'icon_position' => 'top', 'title' => 'Performance Máxima', 'title_color' => '#0f172a', 'description' => '<p>Otimizamos cada detalhe para oferecer velocidade e desempenho excepcionais.</p>', 'description_color' => '#64748b', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '16px', 'box_shadow' => '0 1px 3px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.03)', 'css_classes' => 'pb-hover-lift'], 'children' => [
                            ['type' => 'icon_box', 'settings' => ['icon' => 'fas fa-headset', 'icon_size' => '32', 'icon_color' => '#6366f1', 'icon_position' => 'top', 'title' => 'Suporte Premium', 'title_color' => '#0f172a', 'description' => '<p>Equipe dedicada pronta para ajudar em cada etapa com agilidade e expertise.</p>', 'description_color' => '#64748b', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0f172a', 'padding_top' => '80px', 'padding_bottom' => '80px', 'shape_divider_top' => 'tilt', 'shape_divider_top_color' => '#ffffff', 'shape_divider_top_height' => '80', 'shape_divider_bottom' => 'tilt', 'shape_divider_bottom_color' => '#ffffff', 'shape_divider_bottom_height' => '80'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Projetos Entregues', 'number' => '500', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.8rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Satisfação', 'number' => '98', 'suffix' => '%', 'color' => '#818cf8', 'font_size' => '2.8rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Profissionais', 'number' => '50', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.8rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'counter', 'settings' => ['title' => 'Anos de Experiência', 'number' => '12', 'suffix' => '+', 'color' => '#818cf8', 'font_size' => '2.8rem', 'alignment' => 'center', 'duration' => '2000']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '50px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#6366f1;font-weight:700;text-transform:uppercase;letter-spacing:3px;font-size:.8rem;margin-bottom:8px;">Nosso Portfólio</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Projetos que transformaram negócios', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Conheça alguns dos projetos que entregamos com excelência</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '0px', 'padding_bottom' => '0px', 'border_radius' => '16px', 'box_shadow' => '0 1px 3px rgba(0,0,0,.04)'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/project1/600/400', 'alt' => 'Projeto 1', 'width' => '600', 'height' => '400'], 'width' => '100%', 'border_radius' => '16px 16px 0 0', 'object_fit' => 'cover']],
                            ['type' => 'heading', 'settings' => ['title' => 'E-commerce Plus', 'tag' => 'h3', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '6px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Plataforma de vendas completa com performance excepcional</p>', 'alignment' => 'left', 'font_size' => '14px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '0px', 'padding_bottom' => '0px', 'border_radius' => '16px', 'box_shadow' => '0 1px 3px rgba(0,0,0,.04)'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/project2/600/400', 'alt' => 'Projeto 2', 'width' => '600', 'height' => '400'], 'width' => '100%', 'border_radius' => '16px 16px 0 0', 'object_fit' => 'cover']],
                            ['type' => 'heading', 'settings' => ['title' => 'App Financeiro', 'tag' => 'h3', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '6px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Dashboard financeiro com análise de dados em tempo real</p>', 'alignment' => 'left', 'font_size' => '14px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '0px', 'padding_bottom' => '0px', 'border_radius' => '16px', 'box_shadow' => '0 1px 3px rgba(0,0,0,.04)'], 'children' => [
                            ['type' => 'image', 'settings' => ['image' => ['url' => 'https://picsum.photos/seed/project3/600/400', 'alt' => 'Projeto 3', 'width' => '600', 'height' => '400'], 'width' => '100%', 'border_radius' => '16px 16px 0 0', 'object_fit' => 'cover']],
                            ['type' => 'heading', 'settings' => ['title' => 'Portal Educacional', 'tag' => 'h3', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '6px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Plataforma de ensino com conteúdo interativo e gamificado</p>', 'alignment' => 'left', 'font_size' => '14px']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '50px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#6366f1;font-weight:700;text-transform:uppercase;letter-spacing:3px;font-size:.8rem;margin-bottom:8px;">Depoimentos</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'O que nossos clientes dizem', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">A satisfação dos nossos clientes é a nossa maior conquista</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"A equipe transformou completamente nossa presença online. O resultado superou todas as expectativas."</p>', 'name' => 'João Mendes', 'position' => 'CEO', 'company' => 'TechStart', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Profissionalismo e qualidade excepcionais. Recomendo a todas as empresas que buscam inovação."</p>', 'name' => 'Fernanda Lima', 'position' => 'Diretora', 'company' => 'InnovateLab', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '16px', 'box_shadow' => '0 4px 20px rgba(0,0,0,.06)'], 'children' => [
                            ['type' => 'testimonial', 'settings' => ['content' => '<p>"Resultados incríveis em tempo recorde. Uma parceria que faz toda a diferença no mercado."</p>', 'name' => 'Pedro Alves', 'position' => 'Fundador', 'company' => 'WebPlus', 'rating' => '5', 'alignment' => 'center', 'text_color' => '#475569', 'name_color' => '#0f172a']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '50px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#6366f1;font-weight:700;text-transform:uppercase;letter-spacing:3px;font-size:.8rem;margin-bottom:8px;">Nossos Planos</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Escolha o plano ideal para você', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Soluções flexíveis que se adaptam às necessidades do seu negócio</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Básico', 'price' => '29', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => '1 projeto ativo', 'included' => true], ['text' => '5GB de armazenamento', 'included' => true], ['text' => 'Suporte por email', 'included' => true], ['text' => 'Relatórios básicos', 'included' => false], ['text' => 'API personalizada', 'included' => false]], 'button_text' => 'Começar', 'button_link' => '#', 'featured' => false, 'featured_color' => '#6366f1']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '0px', 'padding_bottom' => '30px'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Profissional', 'price' => '79', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => '10 projetos ativos', 'included' => true], ['text' => '50GB de armazenamento', 'included' => true], ['text' => 'Suporte prioritário', 'included' => true], ['text' => 'Relatórios avançados', 'included' => true], ['text' => 'API personalizada', 'included' => false]], 'button_text' => 'Começar', 'button_link' => '#', 'featured' => true, 'featured_color' => '#6366f1']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'padding_top' => '30px', 'padding_bottom' => '30px'], 'children' => [
                            ['type' => 'price_table', 'settings' => ['title' => 'Enterprise', 'price' => '199', 'currency' => 'R$', 'period' => '/mês', 'features' => [['text' => 'Projetos ilimitados', 'included' => true], ['text' => '500GB de armazenamento', 'included' => true], ['text' => 'Suporte 24/7 dedicado', 'included' => true], ['text' => 'Relatórios personalizados', 'included' => true], ['text' => 'API personalizada', 'included' => true]], 'button_text' => 'Fale Conosco', 'button_link' => '#', 'featured' => false, 'featured_color' => '#6366f1']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '50px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#6366f1;font-weight:700;text-transform:uppercase;letter-spacing:3px;font-size:.8rem;margin-bottom:8px;">FAQ</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Perguntas Frequentes', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:540px;margin:0 auto;">Tire suas dúvidas sobre nossos serviços e processos</p>', 'alignment' => 'center', 'font_size' => '17px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'left'], 'children' => [
                            ['type' => 'accordion', 'settings' => ['items' => [['title' => 'Quanto tempo leva para entregar um projeto?', 'content' => '<p>O prazo varia conforme a complexidade do projeto. Em média, entregamos projetos completos em 4 a 8 semanas. Durante a consultoria inicial, definimos um cronograma detalhado alinhado às suas expectativas.</p>', 'open' => true], ['title' => 'Vocês oferecem suporte após a entrega?', 'content' => '<p>Sim! Oferecemos suporte técnico por 30 dias após a entrega para garantir que tudo funcione perfeitamente. Também temos planos de manutenção contínua para manter seu projeto sempre atualizado.</p>', 'open' => false], ['title' => 'Quais tecnologias vocês utilizam?', 'content' => '<p>Trabalhamos com as tecnologias mais modernas do mercado: Laravel, React, Vue.js, Node.js, entre outras. A escolha da stack ideal é definida em conjunto com o cliente baseado nas necessidades do projeto.</p>', 'open' => false], ['title' => 'Vocês trabalham com projetos internacionais?', 'content' => '<p>Sim! Atendemos clientes em todo o mundo. Nossa equipe é multilíngue e estamos preparados para colaborar remotamente com fusos horários diferentes.</p>', 'open' => false], ['title' => 'Como funciona o processo de orçamento?', 'content' => '<p>Agende uma consultoria gratuita onde entendemos suas necessidades, apresentamos propostas personalizadas e definimos o escopo do projeto. O orçamento é transparente e sem surpresas.</p>', 'open' => false]], 'icon_position' => 'right', 'tab_color' => '#6366f1', 'border_color' => '#e2e8f0', 'item_spacing' => '8', 'content_padding' => '20']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_type' => 'gradient', 'background_gradient' => ['type' => 'linear', 'angle' => '135', 'color1' => '#6366f1', 'color2' => '#8b5cf6', 'position1' => '0', 'position2' => '100'], 'padding_top' => '80px', 'padding_bottom' => '80px', 'border_radius' => '20px', 'box_shadow' => '0 20px 60px rgba(99,102,241,.3)'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Pronto para Transformar seu Negócio?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '16px', 'letter_spacing' => '-.5px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#e0e7ff;max-width:560px;margin:0 auto 32px;font-size:1.15rem;">Entre em contato hoje e descubra como podemos ajudar sua empresa a alcançar novos patamares digitais.</p>', 'alignment' => 'center', 'font_size' => '18px']],
                            ['type' => 'button', 'settings' => ['text' => 'Fale Conosco', 'link' => '#', 'background_color' => '#ffffff', 'text_color' => '#6366f1', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700', 'box_shadow' => '0 4px 16px rgba(0,0,0,.15)']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0f172a', 'padding_top' => '60px', 'padding_bottom' => '60px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'social_icons', 'settings' => ['icons' => [['platform' => 'facebook', 'url' => '#', 'color' => '#6366f1'], ['platform' => 'twitter', 'url' => '#', 'color' => '#6366f1'], ['platform' => 'instagram', 'url' => '#', 'color' => '#6366f1'], ['platform' => 'linkedin', 'url' => '#', 'color' => '#6366f1'], ['platform' => 'github', 'url' => '#', 'color' => '#6366f1']], 'columns' => '5', 'icon_size' => '44', 'gap' => '12', 'alignment' => 'center']],
                            ['type' => 'divider', 'settings' => ['style' => 'solid', 'width' => '60', 'thickness' => '1', 'color' => 'rgba(255,255,255,.1)', 'space_before' => '30', 'space_after' => '30']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;font-size:.85rem;">&copy; 2026 Showcase. Todos os direitos reservados. Desenvolvido com o Page Builder.</p>', 'alignment' => 'center', 'font_size' => '14px']],
                        ]],
                    ]],
                ],
            ],
        ];
    }
}
