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
                'description' => 'Template completo com hero, features, video, estatisticas, equipe, depoimentos e contato',
                'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
                'elements' => [
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0f172a', 'padding_top' => '120px', 'padding_bottom' => '120px', 'min_height' => '90vh', 'align_items' => 'center', 'justify_content' => 'center'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Transforme Suas Ideias em Experiências Digitais', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '24px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.25rem;color:#94a3b8;max-width:700px;margin:0 auto;">Criamos soluções inovadoras que combinam design moderno, tecnologia de ponta e performance excepcional para impulsionar o seu negócio.</p>', 'alignment' => 'center', 'font_size' => '20px', 'line_height' => '1.8', 'margin_bottom' => '40px']],
                            ['type' => 'button', 'settings' => ['text' => 'Comece Agora', 'link' => '#', 'background_color' => '#3b82f6', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '600']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Nossos Serviços', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">Oferecemos um conjunto completo de soluções para transformar sua presença digital</p>', 'alignment' => 'center', 'font_size' => '18px']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '12px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Design Moderno', 'tag' => 'h3', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Interfaces elegantes e intuitivas criadas com as melhores práticas de UX/UI.</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '12px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Performance', 'tag' => 'h3', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Otimizado para velocidade e desempenho máximo em qualquer dispositivo.</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '12px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Suporte Dedicado', 'tag' => 'h3', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Equipe especializada pronta para ajudar em cada etapa do seu projeto.</p>', 'alignment' => 'center', 'font_size' => '15px', 'line_height' => '1.7']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#1e293b', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => '500+', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Projetos Entregues</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => '98%', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Satisfação dos Clientes</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => '50+', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Profissionais</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => '12+', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Anos de Experiência</p>', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '30px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Nossa Equipe', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">Conheça os profissionais que tornam tudo possível</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Ana Silva', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">CEO & Fundadora</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Carlos Oliveira', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">CTO</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Marina Costa', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Head de Design</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Rafael Santos', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Lead Developer</p>', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '100px', 'padding_bottom' => '100px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'O Que Nossos Clientes Dizem', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">A satisfação dos nossos clientes é a nossa maior recompensa</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '12px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-style:italic;">"A equipe transformou completamente nossa presença online."</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'João Mendes', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">CEO, TechStart</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '12px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-style:italic;">"Profissionalismo e qualidade excepcionais."</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Fernanda Lima', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Diretora, InnovateLab</p>', 'alignment' => 'center']],
                        ]],
                        ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '12px'], 'children' => [
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-style:italic;">"Resultados incríveis em tempo recorde."</p>', 'alignment' => 'center']],
                            ['type' => 'heading', 'settings' => ['title' => 'Pedro Alves', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Fundador, WebPlus</p>', 'alignment' => 'center']],
                        ]],
                    ]],
                    ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#3b82f6', 'padding_top' => '80px', 'padding_bottom' => '80px'], 'children' => [
                        ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center'], 'children' => [
                            ['type' => 'heading', 'settings' => ['title' => 'Pronto para Transformar seu Negócio?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                            ['type' => 'text', 'settings' => ['content' => '<p style="color:#bfdbfe;max-width:600px;margin:0 auto 30px;font-size:1.15rem;">Entre em contato conosco hoje e descubra como podemos ajudar sua empresa a alcançar novos patamares.</p>', 'alignment' => 'center']],
                            ['type' => 'button', 'settings' => ['text' => 'Fale Conosco', 'link' => '#', 'background_color' => '#ffffff', 'text_color' => '#3b82f6', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700']],
                        ]],
                    ]],
                ],
            ],
        ];
    }
}
