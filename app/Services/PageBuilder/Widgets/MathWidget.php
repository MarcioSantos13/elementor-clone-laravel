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
            'label' => ['type' => 'text', 'label' => 'Label (optional)'],
            'font_size' => ['type' => 'text', 'label' => 'Font Size', 'tab' => 'style'],
            'color' => ['type' => 'color', 'label' => 'Color', 'tab' => 'style'],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'margin_top' => ['type' => 'text', 'label' => 'Margin Top', 'tab' => 'advanced'],
            'margin_bottom' => ['type' => 'text', 'label' => 'Margin Bottom', 'tab' => 'advanced'],
            'dimensions' => ['type' => 'dimensions', 'label' => 'Padding & Margin', 'tab' => 'advanced'],
            'z_index' => ['type' => 'number', 'label' => 'Z-Index', 'tab' => 'advanced'],
            'css_classes' => ['type' => 'text', 'label' => 'CSS Classes', 'tab' => 'advanced'],
            'css_id' => ['type' => 'text', 'label' => 'CSS ID', 'tab' => 'advanced'],
            'custom_css' => ['type' => 'custom_css', 'label' => 'Custom CSS', 'tab' => 'advanced'],
            'animation' => ['type' => 'animation', 'label' => 'Animation', 'tab' => 'advanced'],
            'visibility' => ['type' => 'visibility', 'label' => 'Responsive Visibility', 'tab' => 'advanced'],
        ];
    }

    public function render(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $formula = $settings['formula'];
        $displayMode = $settings['display_mode'];
        $alignment = $this->safeCssValue($settings['alignment']);
        $fontSize = $this->safeCssValue($settings['font_size']);
        $color = $this->safeCssValue($settings['color']);
        $label = $settings['label'];
        $marginTop = $this->safeCssValue($settings['margin_top']);
        $marginBottom = $this->safeCssValue($settings['margin_bottom']);

        $formulaAttr = htmlspecialchars($formula, ENT_QUOTES, 'UTF-8');
        $labelHtml = $label ? "<span style=\"font-size:.85em;color:#64748b;margin-right:8px\">" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</span>" : '';
        $displayClass = $displayMode ? 'pb-math-display' : 'pb-math-inline';

        return "<div class=\"pb-math-wrapper\" style=\"text-align:{$alignment};margin-top:{$marginTop};margin-bottom:{$marginBottom}\">{$labelHtml}<span class=\"pb-math {$displayClass}\" data-formula=\"{$formulaAttr}\" data-display=\"" . ($displayMode ? 'true' : 'false') . "\" style=\"font-size:{$fontSize};color:{$color}\">{$formula}</span></div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $formula = $settings['formula'];
        $displayMode = $settings['display_mode'];
        $alignment = $this->safeCssValue($settings['alignment']);
        $fontSize = $this->safeCssValue($settings['font_size']);
        $color = $this->safeCssValue($settings['color']);
        $label = $settings['label'];
        $marginTop = $this->safeCssValue($settings['margin_top']);
        $marginBottom = $this->safeCssValue($settings['margin_bottom']);

        $formulaAttr = htmlspecialchars($formula, ENT_QUOTES, 'UTF-8');
        $labelHtml = $label ? "<span style=\"font-size:.85em;color:#64748b;margin-right:8px\">" . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . "</span>" : '';

        return "<div class=\"pb-math-wrapper\" style=\"text-align:{$alignment};margin-top:{$marginTop};margin-bottom:{$marginBottom}\">{$labelHtml}<span class=\"pb-math\" data-formula=\"{$formulaAttr}\" data-display=\"" . ($displayMode ? 'true' : 'false') . "\" style=\"font-size:{$fontSize};color:{$color}\">{$formula}</span></div>";
    }
}
