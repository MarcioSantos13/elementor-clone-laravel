<?php

namespace App\Services\PageBuilder\Widgets;

class SpacerWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'spacer';
        $this->label = 'Spacer';
        $this->icon = '↕️';
        $this->categories = ['general'];
        $this->keywords = ['spacer', 'space', 'gap', 'height'];

        $this->defaultSettings = [
            'space' => 50,
        ];

        $this->controls = [
            'space' => ['type' => 'number', 'label' => 'Space (px)', 'min' => 0, 'max' => 500, 'required' => true],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
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
        $space = (int) $settings['space'];

        return "<div class=\"pb-spacer\" style=\"height: {$space}px;\"></div>";
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $space = (int) $settings['space'];

        return "<div class=\"pb-spacer-editor\" style=\"height: {$space}px; background: repeating-linear-gradient(45deg, transparent, transparent 5px, rgba(99,102,241,.06) 5px, rgba(99,102,241,.06) 10px); border: 1px dashed rgba(99,102,241,.25); border-radius: 4px; position: relative;\"><span style=\"position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);font-size:.7rem;color:rgba(99,102,241,.6);pointer-events:none\">{$space}px</span></div>";
    }
}
