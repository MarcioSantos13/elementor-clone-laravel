<?php

namespace App\Services\PageBuilder\Widgets;

class ButtonWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'button';
        $this->label = 'Button';
        $this->icon = 'button-icon';
        $this->categories = ['basic', 'interactive'];
        $this->keywords = ['button', 'link', 'cta', 'click'];

        $this->defaultSettings = [
            'text' => 'Click Here',
            'link' => '#',
            'link_target' => '_self',
            'alignment' => 'left',
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
        ];

        $this->controls = [
            'text' => ['type' => 'text', 'label' => 'Button Text', 'required' => true, 'max_length' => 100],
            'link' => ['type' => 'url', 'label' => 'Link'],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right', 'stretch']],
            'size' => ['type' => 'select', 'label' => 'Size', 'options' => ['small', 'medium', 'large', 'xl']],
            'icon' => ['type' => 'icon', 'label' => 'Icon'],
            'icon_position' => ['type' => 'select', 'label' => 'Icon Position', 'options' => ['left', 'right']],
            'background_color' => ['type' => 'color', 'label' => 'Background Color', 'tab' => 'style'],
            'text_color' => ['type' => 'color', 'label' => 'Text Color', 'tab' => 'style'],
            'border_radius' => ['type' => 'text', 'label' => 'Border Radius', 'tab' => 'style'],
            'font_size' => ['type' => 'number', 'label' => 'Font Size', 'min' => 10, 'max' => 100, 'tab' => 'style'],
            'typography' => ['type' => 'typography', 'label' => 'Typography', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
            'hover' => ['type' => 'hover', 'label' => 'Hover Effects', 'tab' => 'style'],
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
        $text = htmlspecialchars($settings['text'], ENT_QUOTES, 'UTF-8');
        $link = htmlspecialchars($settings['link'], ENT_QUOTES, 'UTF-8');
        $target = htmlspecialchars($settings['link_target'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $size = $settings['size'];
        $fullWidth = $settings['full_width'];
        $bgColor = $settings['background_color'];
        $bgHover = $settings['background_color_hover'];
        $textColor = $settings['text_color'];
        $textHover = $settings['text_color_hover'];
        $borderColor = $settings['border_color'];
        $borderHover = $settings['border_color_hover'];
        $borderRadius = $settings['border_radius'];
        $borderWidth = $settings['border_width'];
        $paddingTB = $settings['padding_top_bottom'];
        $paddingLR = $settings['padding_left_right'];
        $fontSize = $settings['font_size'];
        $fontWeight = $settings['font_weight'];
        $icon = $settings['icon'];
        $iconPosition = $settings['icon_position'];
        $iconGap = $settings['icon_gap'];

        $sizeMap = [
            'small' => ['padding' => '8px 16px', 'font' => '14px'],
            'medium' => ['padding' => '12px 24px', 'font' => '16px'],
            'large' => ['padding' => '16px 32px', 'font' => '18px'],
            'xl' => ['padding' => '20px 40px', 'font' => '20px'],
        ];

        if (isset($sizeMap[$size])) {
            $paddingTB = explode(' ', $sizeMap[$size]['padding'])[0];
            $paddingLR = explode(' ', $sizeMap[$size]['padding'])[1];
            $fontSize = $sizeMap[$size]['font'];
        }

        $style = "background-color: {$bgColor}; color: {$textColor}; border: {$borderWidth} solid {$borderColor}; border-radius: {$borderRadius}; padding: {$paddingTB} {$paddingLR}; font-size: {$fontSize}; font-weight: {$fontWeight}; cursor: pointer; display: inline-block; text-decoration: none; transition: all 0.3s ease;";

        if ($fullWidth) {
            $style .= ' width: 100%; text-align: center;';
        }

        $hoverStyle = "background-color: {$bgHover}; color: {$textHover}; border-color: {$borderHover};";

        $iconHtml = '';
        if ($icon) {
            $iconTag = "<i class=\"{$icon}\" style=\"margin-{$iconPosition}: {$iconGap}; vertical-align: middle;\"></i>";
            if ($iconPosition === 'left') {
                $iconHtml = $iconTag;
            } else {
                $iconHtml = $iconTag;
            }
        }

        $buttonContent = '';
        if ($icon && $iconPosition === 'left') {
            $buttonContent = "{$iconHtml}<span>{$text}</span>";
        } elseif ($icon && $iconPosition === 'right') {
            $buttonContent = "<span>{$text}</span>{$iconHtml}";
        } else {
            $buttonContent = $text;
        }

        $hoverData = "data-hover-style=\"{$hoverStyle}\"";
        $buttonHtml = "<a href=\"{$link}\" target=\"{$target}\" class=\"pb-button pb-button-{$size}\" style=\"{$style}\" {$hoverData}>{$buttonContent}</a>";

        $hoverStyle = $this->buildHoverStyle("pb-button-{$size}", $styles);

        if ($alignment !== 'stretch') {
            $buttonHtml = "<div style=\"text-align: {$alignment};\">{$buttonHtml}</div>";
        }

        return $hoverStyle . $buttonHtml;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $text = htmlspecialchars($settings['text'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];
        $size = $settings['size'];
        $fullWidth = $settings['full_width'];
        $bgColor = $settings['background_color'];
        $textColor = $settings['text_color'];
        $borderColor = $settings['border_color'];
        $borderRadius = $settings['border_radius'];
        $borderWidth = $settings['border_width'];
        $fontSize = $settings['font_size'];
        $fontWeight = $settings['font_weight'];

        $sizeMap = [
            'small' => ['padding' => '8px 16px', 'font' => '14px'],
            'medium' => ['padding' => '12px 24px', 'font' => '16px'],
            'large' => ['padding' => '16px 32px', 'font' => '18px'],
            'xl' => ['padding' => '20px 40px', 'font' => '20px'],
        ];

        if (isset($sizeMap[$size])) {
            $paddingTB = explode(' ', $sizeMap[$size]['padding'])[0];
            $paddingLR = explode(' ', $sizeMap[$size]['padding'])[1];
            $fontSize = $sizeMap[$size]['font'];
        } else {
            $paddingTB = $settings['padding_top_bottom'];
            $paddingLR = $settings['padding_left_right'];
        }

        $style = "background-color: {$bgColor}; color: {$textColor}; border: {$borderWidth} solid {$borderColor}; border-radius: {$borderRadius}; padding: {$paddingTB} {$paddingLR}; font-size: {$fontSize}; font-weight: {$fontWeight}; cursor: pointer; display: inline-block; text-decoration: none;";

        if ($fullWidth) {
            $style .= ' width: 100%; text-align: center;';
        }

        $hoverClass = "pb-button-{$size}-editor";
        $buttonHtml = "<button class=\"pb-button-editor {$hoverClass}\" style=\"{$style}\">{$text}</button>";

        $hoverStyle = $this->buildHoverStyle($hoverClass, $styles);

        if ($alignment !== 'stretch') {
            $buttonHtml = "<div style=\"text-align: {$alignment};\">{$buttonHtml}</div>";
        }

        return $hoverStyle . $buttonHtml;
    }
}
