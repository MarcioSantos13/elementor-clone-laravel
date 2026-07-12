<?php

namespace App\Services\PageBuilder\Widgets;

class MathWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'math';
        $this->label = 'Math Formula';
        $this->icon = 'math-icon';
        $this->categories = ['basic', 'educational'];
        $this->keywords = ['math', 'latex', 'formula', 'equation', 'katex', 'integral', 'sum'];

        $this->defaultSettings = [
            'formula' => 'x = \\frac{-b \\pm \\sqrt{b^2 - 4ac}}{2a}',
            'display_mode' => true,
            'alignment' => 'center',
            'font_size' => '1.3em',
            'color' => '#1e293b',
            'label' => '',
            'margin_top' => '16px',
            'margin_bottom' => '16px',
        ];

        $this->controls = [
            'formula' => ['type' => 'textarea', 'label' => 'LaTeX Formula', 'required' => true],
            'display_mode' => ['type' => 'boolean', 'label' => 'Display Mode (centered, larger)'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'font_size' => ['type' => 'text', 'label' => 'Font Size'],
            'color' => ['type' => 'color', 'label' => 'Color'],
            'label' => ['type' => 'text', 'label' => 'Label (optional)'],
            'margin_top' => ['type' => 'text', 'label' => 'Margin Top'],
            'margin_bottom' => ['type' => 'text', 'label' => 'Margin Bottom'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $formula = htmlspecialchars($settings['formula'], ENT_QUOTES, 'UTF-8');
        $displayMode = $settings['display_mode'];
        $alignment = $settings['alignment'];
        $fontSize = $settings['font_size'];
        $color = $settings['color'];
        $label = htmlspecialchars($settings['label'], ENT_QUOTES, 'UTF-8');
        $marginTop = $settings['margin_top'];
        $marginBottom = $settings['margin_bottom'];

        $labelHtml = $label ? "<span style=\"font-size:.85em;color:#64748b;margin-right:8px\">{$label}</span>" : '';
        $displayClass = $displayMode ? 'pb-math-display' : 'pb-math-inline';

        return "<div class=\"pb-math-wrapper\" style=\"text-align:{$alignment};margin-top:{$marginTop};margin-bottom:{$marginBottom}\">{$labelHtml}<span class=\"pb-math {$displayClass}\" data-formula=\"{$formula}\" data-display=\"" . ($displayMode ? 'true' : 'false') . "\" style=\"font-size:{$fontSize};color:{$color}\">{$formula}</span></div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $formula = htmlspecialchars($settings['formula'], ENT_QUOTES, 'UTF-8');
        $displayMode = $settings['display_mode'];
        $alignment = $settings['alignment'];
        $fontSize = $settings['font_size'];
        $color = $settings['color'];
        $label = htmlspecialchars($settings['label'], ENT_QUOTES, 'UTF-8');
        $marginTop = $settings['margin_top'];
        $marginBottom = $settings['margin_bottom'];

        $labelHtml = $label ? "<span style=\"font-size:.85em;color:#64748b;margin-right:8px\">{$label}</span>" : '';

        return "<div class=\"pb-math-wrapper\" style=\"text-align:{$alignment};margin-top:{$marginTop};margin-bottom:{$marginBottom}\">{$labelHtml}<span class=\"pb-math\" data-formula=\"{$formula}\" data-display=\"" . ($displayMode ? 'true' : 'false') . "\" style=\"font-size:{$fontSize};color:{$color}\">{$formula}</span></div>";
    }
}
