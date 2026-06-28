<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Element;
use App\Services\PageBuilder\Core\PageBuilderService;
use App\Services\PageBuilder\Core\Renderer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    protected PageBuilderService $pageBuilder;
    protected Renderer $renderer;

    public function __construct(PageBuilderService $pageBuilder, Renderer $renderer)
    {
        $this->pageBuilder = $pageBuilder;
        $this->renderer = $renderer;
    }

    protected array $templates = [
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
                        ['type' => 'text', 'settings' => ['content' => '<p>We are a passionate team dedicated to delivering exceptional digital experiences. Our mission is to empower creators with intuitive tools that bring their ideas to life.</p><p>Founded in 2024, we have helped hundreds of businesses establish their online presence with beautiful, functional websites.</p>', 'font_size' => '16px', 'line_height' => '1.8']],
                    ]],
                ]],
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'border_radius' => '8px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Our Values', 'tag' => 'h2', 'size' => 'medium', 'color' => '#333', 'alignment' => 'left']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Innovation', 'tag' => 'h3', 'size' => 'small', 'color' => '#007bff']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Constantly pushing boundaries</p>']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Quality', 'tag' => 'h3', 'size' => 'small', 'color' => '#007bff']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Every detail matters to us</p>']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Support', 'tag' => 'h3', 'size' => 'small', 'color' => '#007bff']],
                        ['type' => 'text', 'settings' => ['content' => '<p>We are here to help you succeed</p>']],
                    ]],
                ]],
            ],
        ],
        'showcase' => [
            'name' => 'Showcase Completo',
            'description' => 'Template completo com hero, features, vídeo, estatísticas, equipe, depoimentos e contato',
            'settings' => ['container_width' => '1140px', 'page_background' => '#ffffff', 'content_padding' => '0px'],
            'elements' => [
                // ── HERO SECTION ──
                ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#0f172a', 'padding_top' => '120px', 'padding_bottom' => '120px', 'min_height' => '90vh', 'align_items' => 'center', 'justify_content' => 'center'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'vertical_alignment' => 'center', 'text_align' => 'center'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Transforme Suas Ideias em Experiências Digitais', 'tag' => 'h1', 'size' => 'xxl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '24px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="font-size:1.25rem;color:#94a3b8;max-width:700px;margin:0 auto;">Criamos soluções inovadoras que combinam design moderno, tecnologia de ponta e performance excepcional para impulsionar o seu negócio.</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '20px', 'line_height' => '1.8', 'margin_bottom' => '40px']],
                        ['type' => 'button', 'settings' => ['text' => 'Comece Agora', 'link' => '#', 'background_color' => '#3b82f6', 'text_color' => '#ffffff', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '600']],
                        ['type' => 'button', 'settings' => ['text' => 'Saiba Mais', 'link' => '#', 'background_color' => 'transparent', 'text_color' => '#94a3b8', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'border_color' => '#334155', 'border_width' => '2px', 'font_weight' => '600']],
                    ]],
                ]],
                // ── FEATURES / SERVIÇOS ──
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px', 'gap' => '30px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Nossos Serviços', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">Oferecemos um conjunto completo de soluções para transformar sua presença digital</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '18px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://cdn-icons-png.flaticon.com/128/1055/1055687.png', 'alt' => 'Design', 'width' => 80, 'height' => 80], 'width' => '80px', 'max_width' => '80px', 'alignment' => 'center', 'margin_bottom' => '20px']],
                        ['type' => 'heading', 'settings' => ['title' => 'Design Moderno', 'tag' => 'h3', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Interfaces elegantes e intuitivas criadas com as melhores práticas de UX/UI para encantar seus usuários.</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '15px', 'line_height' => '1.7']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://cdn-icons-png.flaticon.com/128/3242/3242257.png', 'alt' => 'Performance', 'width' => 80, 'height' => 80], 'width' => '80px', 'max_width' => '80px', 'alignment' => 'center']],
                        ['type' => 'heading', 'settings' => ['title' => 'Performance', 'tag' => 'h3', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Otimizado para velocidade e desempenho máximo, garantindo a melhor experiência em qualquer dispositivo.</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '15px', 'line_height' => '1.7']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#f8fafc', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://cdn-icons-png.flaticon.com/128/10337/10337689.png', 'alt' => 'Suporte', 'width' => 80, 'height' => 80], 'width' => '80px', 'max_width' => '80px', 'alignment' => 'center']],
                        ['type' => 'heading', 'settings' => ['title' => 'Suporte Dedicado', 'tag' => 'h3', 'size' => 'medium', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '600', 'margin_bottom' => '12px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Equipe especializada pronta para ajudar você em cada etapa do seu projeto, do planejamento à execução.</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '15px', 'line_height' => '1.7']],
                    ]],
                ]],
                // ── VIDEO SHOWCASE ──
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#0f172a', 'padding_top' => '100px', 'padding_bottom' => '100px', 'gap' => '40px', 'align_items' => 'center'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'padding_right' => '30px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Veja Nossa Plataforma em Ação', 'tag' => 'h2', 'size' => 'xl', 'color' => '#ffffff', 'alignment' => 'left', 'font_weight' => '700', 'margin_bottom' => '20px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;font-size:1.1rem;">Descubra como nossa plataforma pode revolucionar a forma como você cria e gerencia conteúdo digital. Assista ao vídeo demonstrativo e conheça todos os recursos.</p><p style="color:#94a3b8;font-size:1.1rem;margin-top:16px;">Interface intuitiva, componentes poderosos e total flexibilidade para criar páginas incríveis sem escrever uma linha de código.</p>', 'alignment' => 'left', 'color' => '#94a3b8', 'font_size' => '17px', 'line_height' => '1.8']],
                        ['type' => 'button', 'settings' => ['text' => 'Agende uma Demonstração', 'link' => '#', 'background_color' => '#3b82f6', 'text_color' => '#ffffff', 'size' => 'medium', 'alignment' => 'left', 'border_radius' => '50px', 'padding_left_right' => '32px', 'font_weight' => '600', 'margin_top' => '20px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-6', 'vertical_alignment' => 'center', 'background_color' => '#1e293b', 'border_radius' => '16px', 'padding_top' => '0px', 'padding_bottom' => '0px', 'padding_left' => '0px', 'padding_right' => '0px'], 'children' => [
                        ['type' => 'text', 'settings' => ['content' => '<div style="position:relative;width:100%;padding-bottom:56.25%;border-radius:16px;overflow:hidden;"><iframe style="position:absolute;top:0;left:0;width:100%;height:100%;border:none;" src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>', 'alignment' => 'left']],
                    ]],
                ]],
                // ── STATS / NÚMEROS ──
                ['type' => 'section', 'settings' => ['layout' => 'full_width', 'background_color' => '#1e293b', 'padding_top' => '80px', 'padding_bottom' => '80px', 'gap' => '0px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => '500+', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;font-size:1.1rem;">Projetos Entregues</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '17px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => '98%', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;font-size:1.1rem;">Satisfação dos Clientes</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '17px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => '50+', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;font-size:1.1rem;">Profissionais</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '17px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => '12+', 'tag' => 'h2', 'size' => 'xxl', 'color' => '#3b82f6', 'alignment' => 'center', 'font_weight' => '800', 'margin_bottom' => '8px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;font-size:1.1rem;">Anos de Experiência</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '17px']],
                    ]],
                ]],
                // ── TEAM ──
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#ffffff', 'padding_top' => '100px', 'padding_bottom' => '100px', 'gap' => '30px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '30px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Nossa Equipe', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">Conheça os profissionais apaixonados que tornam tudo possível</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '18px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_left' => '10px', 'padding_right' => '10px'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://i.pravatar.cc/300?img=1', 'alt' => 'Membro 1', 'width' => 200, 'height' => 200], 'width' => '200px', 'max_width' => '200px', 'alignment' => 'center', 'border_radius' => '50%', 'margin_bottom' => '16px']],
                        ['type' => 'heading', 'settings' => ['title' => 'Ana Silva', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">CEO & Fundadora</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '14px', 'margin_bottom' => '0px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_left' => '10px', 'padding_right' => '10px'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://i.pravatar.cc/300?img=8', 'alt' => 'Membro 2', 'width' => 200, 'height' => 200], 'width' => '200px', 'max_width' => '200px', 'alignment' => 'center', 'border_radius' => '50%', 'margin_bottom' => '16px']],
                        ['type' => 'heading', 'settings' => ['title' => 'Carlos Oliveira', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">CTO</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '14px', 'margin_bottom' => '0px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_left' => '10px', 'padding_right' => '10px'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://i.pravatar.cc/300?img=5', 'alt' => 'Membro 3', 'width' => 200, 'height' => 200], 'width' => '200px', 'max_width' => '200px', 'alignment' => 'center', 'border_radius' => '50%', 'margin_bottom' => '16px']],
                        ['type' => 'heading', 'settings' => ['title' => 'Marina Costa', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Head de Design</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '14px', 'margin_bottom' => '0px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-3', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px', 'padding_left' => '10px', 'padding_right' => '10px'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://i.pravatar.cc/300?img=3', 'alt' => 'Membro 4', 'width' => 200, 'height' => 200], 'width' => '200px', 'max_width' => '200px', 'alignment' => 'center', 'border_radius' => '50%', 'margin_bottom' => '16px']],
                        ['type' => 'heading', 'settings' => ['title' => 'Rafael Santos', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;">Lead Developer</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '14px', 'margin_bottom' => '0px']],
                    ]],
                ]],
                // ── TESTEMUNHOS ──
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#f8fafc', 'padding_top' => '100px', 'padding_bottom' => '100px', 'gap' => '30px'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'O Que Nossos Clientes Dizem', 'tag' => 'h2', 'size' => 'xl', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#64748b;max-width:600px;margin:0 auto;">A satisfação dos nossos clientes é a nossa maior recompensa</p>', 'alignment' => 'center', 'color' => '#64748b', 'font_size' => '18px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.06)'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://i.pravatar.cc/100?img=11', 'alt' => 'Cliente 1', 'width' => 80, 'height' => 80], 'width' => '80px', 'max_width' => '80px', 'alignment' => 'center', 'border_radius' => '50%', 'margin_bottom' => '16px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-style:italic;">"A equipe transformou completamente nossa presença online. O resultado superou todas as nossas expectativas!"</p>', 'alignment' => 'center', 'color' => '#475569', 'font_size' => '15px', 'line_height' => '1.7']],
                        ['type' => 'heading', 'settings' => ['title' => 'João Mendes', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">CEO, TechStart</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '13px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.06)'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://i.pravatar.cc/100?img=12', 'alt' => 'Cliente 2', 'width' => 80, 'height' => 80], 'width' => '80px', 'max_width' => '80px', 'alignment' => 'center', 'border_radius' => '50%', 'margin_bottom' => '16px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-style:italic;">"Profissionalismo e qualidade excepcionais. Recomendo para qualquer empresa que queira crescer digitalmente."</p>', 'alignment' => 'center', 'color' => '#475569', 'font_size' => '15px', 'line_height' => '1.7']],
                        ['type' => 'heading', 'settings' => ['title' => 'Fernanda Lima', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Diretora, InnovateLab</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '13px']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-4', 'text_align' => 'center', 'background_color' => '#ffffff', 'padding_top' => '40px', 'padding_bottom' => '40px', 'padding_left' => '30px', 'padding_right' => '30px', 'border_radius' => '12px', 'box_shadow' => '0 4px 20px rgba(0,0,0,0.06)'], 'children' => [
                        ['type' => 'image', 'settings' => ['image' => ['url' => 'https://i.pravatar.cc/100?img=26', 'alt' => 'Cliente 3', 'width' => 80, 'height' => 80], 'width' => '80px', 'max_width' => '80px', 'alignment' => 'center', 'border_radius' => '50%', 'margin_bottom' => '16px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#475569;font-style:italic;">"Resultados incríveis em tempo recorde. A plataforma é intuitiva e o suporte é simplesmente fantástico."</p>', 'alignment' => 'center', 'color' => '#475569', 'font_size' => '15px', 'line_height' => '1.7']],
                        ['type' => 'heading', 'settings' => ['title' => 'Pedro Alves', 'tag' => 'h4', 'size' => 'small', 'color' => '#0f172a', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '4px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#94a3b8;">Fundador, WebPlus</p>', 'alignment' => 'center', 'color' => '#94a3b8', 'font_size' => '13px']],
                    ]],
                ]],
                // ── CTA FINAL ──
                ['type' => 'section', 'settings' => ['layout' => 'boxed', 'background_color' => '#3b82f6', 'padding_top' => '80px', 'padding_bottom' => '80px', 'border_radius' => '0px', 'align_items' => 'center', 'justify_content' => 'center'], 'children' => [
                    ['type' => 'column', 'settings' => ['column_width' => 'col-12', 'text_align' => 'center', 'padding_top' => '20px', 'padding_bottom' => '20px'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Pronto para Transformar seu Negócio?', 'tag' => 'h2', 'size' => 'xl', 'color' => '#ffffff', 'alignment' => 'center', 'font_weight' => '700', 'margin_bottom' => '16px']],
                        ['type' => 'text', 'settings' => ['content' => '<p style="color:#bfdbfe;max-width:600px;margin:0 auto 30px;font-size:1.15rem;">Entre em contato conosco hoje e descubra como podemos ajudar sua empresa a alcançar novos patamares.</p>', 'alignment' => 'center', 'color' => '#bfdbfe', 'font_size' => '18px']],
                        ['type' => 'button', 'settings' => ['text' => 'Fale Conosco', 'link' => '#', 'background_color' => '#ffffff', 'text_color' => '#3b82f6', 'size' => 'large', 'alignment' => 'center', 'border_radius' => '50px', 'padding_left_right' => '40px', 'font_weight' => '700']],
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
                        ['type' => 'text', 'settings' => ['content' => '<p>123 Main Street<br>New York, NY 10001<br>United States</p><p>Email: hello@example.com<br>Phone: (555) 123-4567</p>']],
                    ]],
                    ['type' => 'column', 'settings' => ['column_width' => 'col-6'], 'children' => [
                        ['type' => 'heading', 'settings' => ['title' => 'Send a Message', 'tag' => 'h3', 'size' => 'small', 'color' => '#333']],
                        ['type' => 'text', 'settings' => ['content' => '<p>Use the form below or reach out directly via email.</p>']],
                        ['type' => 'button', 'settings' => ['text' => 'Email Us', 'link' => 'mailto:hello@example.com', 'background_color' => '#007bff', 'text_color' => '#ffffff', 'size' => 'medium']],
                    ]],
                ]],
            ],
        ],
    ];

    public function index(): View
    {
        $pages = Page::latest()->paginate(20);
        return view('page-builder.pages.index', compact('pages'));
    }

    public function create(): View
    {
        $templates = [];
        foreach ($this->templates as $key => $tmpl) {
            $templates[$key] = $tmpl['name'];
        }
        return view('page-builder.pages.create', compact('templates'));
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'nullable|in:draft,published',
            'template' => 'nullable|string|max:255',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            '_redirect' => 'nullable|in:index,editor',
        ]);

        $page = $this->pageBuilder->createPage($validated);

        $templateKey = $request->input('template');
        if ($templateKey && isset($this->templates[$templateKey])) {
            $template = $this->templates[$templateKey];
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();
            $this->importTemplateElements($page, $template['elements']);
        }

        $redirectTo = $request->input('_redirect', 'index');

        return $redirectTo === 'editor'
            ? redirect()->route('page-builder.editor', $page)
            : redirect()->route('page-builder.pages.index')->with('success', "Page \"{$page->title}\" created successfully!");
    }

    public function show(Page $page): View
    {
        $html = $this->pageBuilder->renderPage($page);
        return view('page-builder.pages.show', compact('page', 'html'));
    }

    public function edit(Page $page): View
    {
        return view('page-builder.editor', compact('page'));
    }

    public function listTemplates(): JsonResponse
    {
        $list = [];
        foreach ($this->templates as $key => $tmpl) {
            $list[$key] = ['name' => $tmpl['name'], 'description' => $tmpl['description']];
        }
        return response()->json(['templates' => $list]);
    }

    public function applyTemplate(Request $request, Page $page): JsonResponse
    {
        $templateKey = $request->input('template', 'blank');

        if (!isset($this->templates[$templateKey])) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $template = $this->templates[$templateKey];

        DB::beginTransaction();
        try {
            $page->elements()->delete();
            $page->settings = array_merge($page->settings ?? [], $template['settings']);
            $page->save();

            $this->importTemplateElements($page, $template['elements']);

            DB::commit();

            return response()->json([
                'message' => "Template \"{$template['name']}\" applied",
                'page' => $page->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateLayout(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $page->settings = array_merge($page->settings ?? [], $validated['settings']);
        $page->save();

        return response()->json([
            'message' => 'Layout updated',
            'page' => $page,
        ]);
    }

    protected function importTemplateElements(Page $page, array $elements, ?int $parentId = null, string $widgetType = null): void
    {
        foreach ($elements as $index => $elData) {
            $type = $widgetType ?? $elData['type'];
            $children = $elData['children'] ?? [];

            $element = new Element();
            $element->page_id = $page->id;
            $element->parent_id = $parentId;
            $element->type = $type;
            $element->uuid = (string) \Illuminate\Support\Str::uuid();
            $element->name = $elData['settings']['name'] ?? ucfirst($type);
            $element->settings = $elData['settings'] ?? [];
            $element->content = [];
            $element->styles = [];
            $element->order = $index;
            $element->save();

            if ($children) {
                $childType = null;
                if ($type === 'section') $childType = 'column';
                $this->importTemplateElements($page, $children, $element->id, $childType);
            }
        }
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|array',
            'status' => 'nullable|in:draft,published,archived',
            'settings' => 'nullable|array',
            'meta_data' => 'nullable|array',
            'template' => 'nullable|string|max:255',
        ]);

        $page = $this->pageBuilder->updatePage($page, $validated);

        return response()->json([
            'message' => 'Page updated successfully',
            'page' => $page,
        ]);
    }

    public function destroy(Page $page): \Illuminate\Http\RedirectResponse
    {
        $title = $page->title;
        $page->delete();

        return redirect()->route('page-builder.pages.index')
            ->with('success', "Page \"{$title}\" deleted successfully!");
    }

    public function publish(Page $page): JsonResponse
    {
        $page->status = 'published';
        $page->save();

        return response()->json([
            'message' => 'Page published successfully',
            'page' => $page,
        ]);
    }

    public function unpublish(Page $page): JsonResponse
    {
        $page->status = 'draft';
        $page->save();

        return response()->json([
            'message' => 'Page unpublished successfully',
            'page' => $page,
        ]);
    }

    public function duplicate(Page $page): JsonResponse
    {
        $newPage = $page->replicate();
        $newPage->title = $page->title . ' (copy)';
        $newPage->slug = $page->slug . '-' . uniqid();
        $newPage->status = 'draft';
        $newPage->save();

        foreach ($page->elements as $element) {
            $newElement = $element->replicate();
            $newElement->page_id = $newPage->id;
            $newElement->save();
        }

        return response()->json([
            'message' => 'Page duplicated successfully',
            'page' => $newPage,
        ]);
    }

    public function export(Page $page): JsonResponse
    {
        $data = $this->pageBuilder->exportPage($page);

        return response()->json($data);
    }

    public function import(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'data' => 'required|array',
            'data.title' => 'required|string|max:255',
            'data.elements' => 'sometimes|array',
        ]);

        $page = $this->pageBuilder->importPage($validated['data']);

        return response()->json([
            'message' => 'Page imported successfully',
            'page' => $page,
        ], 201);
    }

    public function getData(Page $page): JsonResponse
    {
        $elements = $page->elements()->with('children')->get();
        $tree = $this->buildElementTree($elements);

        return response()->json([
            'page' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'status' => $page->status,
                'settings' => $page->settings,
                'meta_data' => $page->meta_data,
                'template' => $page->template,
                'created_at' => $page->created_at,
                'updated_at' => $page->updated_at,
            ],
            'elements' => $tree,
        ]);
    }

    public function render(Page $page): \Illuminate\Http\Response
    {
        $html = $this->pageBuilder->renderPage($page, [
            'with_container' => true,
            'theme' => request('theme', 'default'),
        ]);

        return response($html);
    }

    protected function buildElementTree($elements): array
    {
        $tree = [];

        foreach ($elements as $element) {
            $node = [
                'id' => $element->id,
                'uuid' => $element->uuid,
                'type' => $element->type,
                'name' => $element->name,
                'order' => $element->order,
                'settings' => $element->settings,
                'content' => $element->content,
                'styles' => $element->styles,
                'responsive_settings' => $element->responsive_settings,
                'animation' => $element->animation,
                'effects' => $element->effects,
                'column_size' => $element->column_size,
                'css_classes' => $element->css_classes,
                'css_id' => $element->css_id,
            ];

            if ($element->children->isNotEmpty()) {
                $node['children'] = $this->buildElementTree($element->children);
            }

            $tree[] = $node;
        }

        return $tree;
    }
}
