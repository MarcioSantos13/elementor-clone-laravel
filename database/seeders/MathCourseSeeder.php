<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Element;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MathCourseSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1;

        $page = Page::create([
            'user_id' => $userId,
            'title' => 'Curso de Matemática - Equações do 2º Grau',
            'slug' => 'curso-matematica-equacoes-2-grau',
            'status' => 'published',
            'settings' => [
                'container_width' => '1140px',
                'page_background' => '#f8fafc',
                'content_padding' => '0px',
            ],
            'meta_data' => [],
        ]);

        // ── Section 1: Hero ──
        $hero = $this->section($page, 0, [
            'layout' => 'full_width',
            'min_height' => '60vh',
            'background_color' => '#1e3a5f',
            'padding_top' => '80px',
            'padding_bottom' => '80px',
            'padding_left' => '20px',
            'padding_right' => '20px',
            'justify_content' => 'center',
            'align_items' => 'center',
        ]);

        $this->heading($page, $hero, 0, 'Equações do 2º Grau', [
            'tag' => 'h1',
            'alignment' => 'center',
            'size' => 'large',
            'color' => '#ffffff',
            'font_weight' => '800',
        ]);

        $this->text($page, $hero, 1, 'Aprenda a resolver equações quadráticas de forma simples e prática. Domine a fórmula de Bhaskara e resolva qualquer problema!', [
            'alignment' => 'center',
            'color' => '#cbd5e1',
            'font_size' => '18px',
            'line_height' => '1.8',
        ]);

        $this->icon($page, $hero, 2, 'fas fa-arrow-down', [
            'icon_size' => 36,
            'color' => '#facc15',
            'align' => 'center',
        ]);

        // ── Section 2: Fórmula ──
        $formula = $this->section($page, 1, [
            'layout' => 'full_width',
            'background_color' => '#ffffff',
            'padding_top' => '60px',
            'padding_bottom' => '60px',
            'padding_left' => '20px',
            'padding_right' => '20px',
        ]);

        $this->heading($page, $formula, 0, 'A Fórmula de Bhaskara', [
            'tag' => 'h2',
            'alignment' => 'center',
            'size' => 'medium',
            'color' => '#1e3a5f',
        ]);

        $this->text($page, $formula, 1, 'A solução geral da equação ax² + bx + c = 0 é dada por:', [
            'alignment' => 'center',
            'color' => '#475569',
            'font_size' => '17px',
        ]);

        $this->math($page, $formula, 2, 'x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}', [
            'display_mode' => true,
            'alignment' => 'center',
            'label' => 'Fórmula:',
            'font_size' => '1.4em',
        ]);

        $this->text($page, $formula, 3, 'Onde <strong>a</strong>, <strong>b</strong> e <strong>c</strong> são os coeficientes da equação e <strong>Δ = b² − 4ac</strong> é o discriminante.', [
            'alignment' => 'center',
            'color' => '#64748b',
            'font_size' => '16px',
        ]);

        $this->divider($page, $formula, 4, [
            'width' => '40',
            'color' => '#e2e8f0',
            'space_before' => '30',
            'space_after' => '30',
        ]);

        $this->math($page, $formula, 5, '\\Delta = b^2 - 4ac', [
            'display_mode' => true,
            'alignment' => 'center',
            'label' => 'Discriminante:',
            'font_size' => '1.2em',
        ]);

        // ── Section 3: Tipos de raízes ──
        $tipos = $this->section($page, 2, [
            'layout' => 'full_width',
            'background_color' => '#f1f5f9',
            'padding_top' => '50px',
            'padding_bottom' => '50px',
            'padding_left' => '20px',
            'padding_right' => '20px',
        ]);

        $this->heading($page, $tipos, 0, 'Tipos de Raízes', [
            'tag' => 'h2',
            'alignment' => 'center',
            'size' => 'medium',
            'color' => '#1e3a5f',
        ]);

        $this->spacer($page, $tipos, 1, ['space' => 10]);

        $this->callout($page, $tipos, 2, 'Duas raízes reais e distintas quando Δ > 0', [
            'type' => 'success',
            'title' => 'Δ > 0 (Duas raízes reais)',
            'content' => 'Quando o discriminante é positivo, a equação possui duas raízes reais e diferentes.',
            'icon' => 'fas fa-check-circle',
            'show_icon' => true,
        ]);

        $this->callout($page, $tipos, 3, 'Uma raiz real quando Δ = 0', [
            'type' => 'warning',
            'title' => 'Δ = 0 (Raiz dupla)',
            'content' => 'Quando o discriminante é zero, a equação possui apenas uma raiz real (raiz dupla).',
            'icon' => 'fas fa-exclamation-triangle',
            'show_icon' => true,
        ]);

        $this->callout($page, $tipos, 4, 'Sem raízes reais quando Δ < 0', [
            'type' => 'info',
            'title' => 'Δ < 0 (Sem raízes reais)',
            'content' => 'Quando o discriminante é negativo, a equação não possui raízes reais (raízes complexas).',
            'icon' => 'fas fa-info-circle',
            'show_icon' => true,
        ]);

        // ── Section 4: Vídeo Aula ──
        $video = $this->section($page, 3, [
            'layout' => 'full_width',
            'background_color' => '#ffffff',
            'padding_top' => '60px',
            'padding_bottom' => '60px',
            'padding_left' => '20px',
            'padding_right' => '20px',
        ]);

        $this->heading($page, $video, 0, 'Videoaula: Resolvendo Equações do 2º Grau', [
            'tag' => 'h2',
            'alignment' => 'center',
            'size' => 'medium',
            'color' => '#1e3a5f',
        ]);

        $this->text($page, $video, 1, 'Assista ao vídeo abaixo e aprenda passo a passo como aplicar a fórmula de Bhaskara em exercícios práticos.', [
            'alignment' => 'center',
            'color' => '#64748b',
            'font_size' => '16px',
        ]);

        $this->video($page, $video, 2, [
            'video_url' => 'https://www.youtube.com/watch?v=aGhRCf0Hk5Y',
            'video_type' => 'youtube',
            'aspect_ratio' => '16:9',
            'alignment' => 'center',
            'width' => '100%',
            'max_width' => '800px',
        ]);

        // ── Section 5: Ícones com benefícios ──
        $icones = $this->section($page, 4, [
            'layout' => 'full_width',
            'background_color' => '#1e3a5f',
            'padding_top' => '60px',
            'padding_bottom' => '60px',
            'padding_left' => '20px',
            'padding_right' => '20px',
        ]);

        $this->heading($page, $icones, 0, 'Por que estudar Equações do 2º Grau?', [
            'tag' => 'h2',
            'alignment' => 'center',
            'size' => 'medium',
            'color' => '#ffffff',
        ]);

        $this->spacer($page, $icones, 1, ['space' => 20]);

        $this->icon($page, $icones, 2, 'fas fa-graduation-cap', [
            'icon_size' => 52,
            'color' => '#facc15',
            'align' => 'center',
        ]);
        $this->heading($page, $icones, 3, 'Base para Concursos', [
            'tag' => 'h4',
            'alignment' => 'center',
            'size' => 'default',
            'color' => '#ffffff',
        ]);
        $this->text($page, $icones, 4, 'Presente em provas de vestibular, ENEM e concursos públicos de todo o Brasil.', [
            'alignment' => 'center',
            'color' => '#94a3b8',
            'font_size' => '15px',
        ]);

        $this->icon($page, $icones, 5, 'fas fa-brain', [
            'icon_size' => 52,
            'color' => '#facc15',
            'align' => 'center',
        ]);
        $this->heading($page, $icones, 6, 'Raciocínio Lógico', [
            'tag' => 'h4',
            'alignment' => 'center',
            'size' => 'default',
            'color' => '#ffffff',
        ]);
        $this->text($page, $icones, 7, 'Desenvolve habilidades de pensamento lógico-matemático essenciais para a vida.', [
            'alignment' => 'center',
            'color' => '#94a3b8',
            'font_size' => '15px',
        ]);

        $this->icon($page, $icones, 8, 'fas fa-chart-line', [
            'icon_size' => 52,
            'color' => '#facc15',
            'align' => 'center',
        ]);
        $this->heading($page, $icones, 9, 'Aplicações Reais', [
            'tag' => 'h4',
            'alignment' => 'center',
            'size' => 'default',
            'color' => '#ffffff',
        ]);
        $this->text($page, $icones, 10, 'Usada em física, engenharia, economia e diversas áreas do conhecimento.', [
            'alignment' => 'center',
            'color' => '#94a3b8',
            'font_size' => '15px',
        ]);

        // ── Section 6: Formulário ──
        $form = $this->section($page, 5, [
            'layout' => 'full_width',
            'background_color' => '#f8fafc',
            'padding_top' => '60px',
            'padding_bottom' => '80px',
            'padding_left' => '20px',
            'padding_right' => '20px',
        ]);

        $this->heading($page, $form, 0, 'Matricule-se no Curso', [
            'tag' => 'h2',
            'alignment' => 'center',
            'size' => 'medium',
            'color' => '#1e3a5f',
        ]);

        $this->text($page, $form, 1, 'Preencha o formulário abaixo para garantir sua vaga. É rápido e gratuito!', [
            'alignment' => 'center',
            'color' => '#64748b',
            'font_size' => '16px',
        ]);

        $this->form($page, $form, 2);

        $this->button($page, $form, 3, [
            'text' => 'Baixar Material Gratuito',
            'link' => '#',
            'alignment' => 'center',
            'background_color' => '#16a34a',
            'text_color' => '#ffffff',
            'border_radius' => '8px',
            'icon' => 'fas fa-download',
            'icon_position' => 'left',
        ]);

        $this->command->info("Página criada: /page-builder/pages/{$page->id}/render");
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function section(Page $page, int $order, array $settings): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => null,
            'uuid' => (string) Str::uuid(),
            'type' => 'section',
            'name' => 'Section',
            'order' => $order,
            'settings' => $settings + [
                'gap' => 'default',
                'flex_wrap' => 'wrap',
                'align_items' => 'stretch',
                'justify_content' => 'flex-start',
                'background_type' => 'classic',
                'background_image' => [],
                'background_overlay' => '',
                'background_position' => 'center center',
                'background_size' => 'cover',
                'background_repeat' => 'no-repeat',
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
            ],
            'styles' => [],
        ]);
    }

    private function heading(Page $page, Element $parent, int $order, string $title, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'heading',
            'name' => 'Heading',
            'order' => $order,
            'settings' => array_merge([
                'title' => $title,
                'tag' => 'h2',
                'alignment' => 'left',
                'size' => 'default',
                'color' => '#333333',
                'font_family' => '',
                'font_weight' => '700',
                'letter_spacing' => 'normal',
                'line_height' => '1.4',
                'margin_bottom' => '20px',
                'link' => '',
                'link_target' => '_self',
                'enable_animation' => false,
                'animation_type' => 'fadeIn',
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function text(Page $page, Element $parent, int $order, string $content, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'text',
            'name' => 'Text Editor',
            'order' => $order,
            'settings' => array_merge([
                'content' => '<p>' . $content . '</p>',
                'alignment' => 'left',
                'color' => '#666666',
                'font_size' => '16px',
                'font_family' => '',
                'font_weight' => '400',
                'line_height' => '1.7',
                'margin_bottom' => '20px',
                'drop_cap' => false,
                'column_count' => 1,
                'column_gap' => 'normal',
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function math(Page $page, Element $parent, int $order, string $formula, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'math',
            'name' => 'Math Formula',
            'order' => $order,
            'settings' => array_merge([
                'formula' => $formula,
                'display_mode' => true,
                'alignment' => 'center',
                'label' => '',
                'font_size' => '1.2em',
                'color' => '#1e293b',
                'margin_top' => '0px',
                'margin_bottom' => '0px',
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function icon(Page $page, Element $parent, int $order, string $icon, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'icon',
            'name' => 'Icon',
            'order' => $order,
            'settings' => array_merge([
                'icon' => $icon,
                'icon_size' => 48,
                'color' => '#6366f1',
                'align' => 'center',
                'link' => '',
                'link_new_tab' => false,
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function spacer(Page $page, Element $parent, int $order, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'spacer',
            'name' => 'Spacer',
            'order' => $order,
            'settings' => array_merge([
                'space' => 50,
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function divider(Page $page, Element $parent, int $order, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'divider',
            'name' => 'Divider',
            'order' => $order,
            'settings' => array_merge([
                'style' => 'solid',
                'width' => '100',
                'thickness' => '1',
                'color' => '#e2e8f0',
                'space_before' => '20',
                'space_after' => '20',
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function video(Page $page, Element $parent, int $order, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'video',
            'name' => 'Video',
            'order' => $order,
            'settings' => array_merge([
                'video_type' => 'youtube',
                'video_url' => '',
                'autoplay' => false,
                'loop' => false,
                'controls' => true,
                'mute' => false,
                'start_time' => 0,
                'end_time' => 0,
                'aspect_ratio' => '16:9',
                'alignment' => 'center',
                'width' => '100%',
                'max_width' => '100%',
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function callout(Page $page, Element $parent, int $order, string $text, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'callout',
            'name' => 'Callout',
            'order' => $order,
            'settings' => array_merge([
                'type' => 'info',
                'title' => '',
                'content' => $text,
                'icon' => 'fas fa-info-circle',
                'show_icon' => true,
                'border_radius' => '8px',
                'padding' => '20px',
                'margin_bottom' => '16px',
            ], $overrides),
            'styles' => [],
        ]);
    }

    private function form(Page $page, Element $parent, int $order): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'form',
            'name' => 'Form',
            'order' => $order,
            'settings' => [
                'form_name' => 'Matrícula',
                'fields' => [
                    ['label' => 'Nome Completo', 'type' => 'text', 'required' => true, 'placeholder' => 'Seu nome completo'],
                    ['label' => 'E-mail', 'type' => 'email', 'required' => true, 'placeholder' => 'seu@email.com'],
                    ['label' => 'Telefone', 'type' => 'tel', 'required' => false, 'placeholder' => '(00) 00000-0000'],
                ],
                'button_text' => 'Enviar Inscrição',
                'button_color' => '#1e3a5f',
                'button_text_color' => '#ffffff',
                'button_width' => 'full',
                'success_message' => 'Inscrição enviada com sucesso!',
                'field_spacing' => 16,
                'field_radius' => 6,
            ],
            'styles' => [],
        ]);
    }

    private function button(Page $page, Element $parent, int $order, array $overrides = []): Element
    {
        return Element::create([
            'page_id' => $page->id,
            'parent_id' => $parent->id,
            'uuid' => (string) Str::uuid(),
            'type' => 'button',
            'name' => 'Button',
            'order' => $order,
            'settings' => array_merge([
                'text' => 'Click Here',
                'link' => '#',
                'link_target' => '_self',
                'alignment' => 'center',
                'size' => 'medium',
                'full_width' => false,
                'background_color' => '#007bff',
                'background_color_hover' => '#0056b3',
                'text_color' => '#ffffff',
                'text_color_hover' => '#ffffff',
                'border_color' => 'transparent',
                'border_color_hover' => 'transparent',
                'border_radius' => '4px',
                'border_width' => '0px',
                'padding_top_bottom' => '12px',
                'padding_left_right' => '24px',
                'font_size' => '16px',
                'font_weight' => '500',
                'icon' => '',
                'icon_position' => 'left',
                'icon_gap' => '8px',
                'hover_animation' => 'none',
                'css_id' => '',
                'css_classes' => '',
            ], $overrides),
            'styles' => [],
        ]);
    }
}
