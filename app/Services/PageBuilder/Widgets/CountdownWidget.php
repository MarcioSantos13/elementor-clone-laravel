<?php

namespace App\Services\PageBuilder\Widgets;

class CountdownWidget extends BaseWidget
{
    public function __construct()
    {
        $this->type = 'countdown';
        $this->label = 'Countdown';
        $this->icon = '⏳';
        $this->categories = ['general', 'interactive'];
        $this->keywords = ['countdown', 'timer', 'clock', 'deadline'];

        $this->defaultSettings = [
            'target_date' => '2026-12-31',
            'days_label' => 'Dias',
            'hours_label' => 'Horas',
            'minutes_label' => 'Min',
            'seconds_label' => 'Seg',
            'color' => '#6366f1',
            'background_color' => '#f3f4f6',
            'alignment' => 'center',
        ];

        $this->controls = [
            'target_date' => ['type' => 'text', 'label' => 'Target Date (YYYY-MM-DD)', 'required' => true],
            'days_label' => ['type' => 'text', 'label' => 'Days Label', 'max_length' => 20],
            'hours_label' => ['type' => 'text', 'label' => 'Hours Label', 'max_length' => 20],
            'minutes_label' => ['type' => 'text', 'label' => 'Minutes Label', 'max_length' => 20],
            'seconds_label' => ['type' => 'text', 'label' => 'Seconds Label', 'max_length' => 20],
            'alignment' => ['type' => 'select', 'label' => 'Alignment', 'options' => ['left', 'center', 'right']],
            'color' => ['type' => 'color', 'label' => 'Number Color', 'tab' => 'style'],
            'background_color' => ['type' => 'color', 'label' => 'Box Background', 'tab' => 'style'],
            'background' => ['type' => 'background', 'label' => 'Background', 'tab' => 'style'],
            'border' => ['type' => 'border', 'label' => 'Border', 'tab' => 'style'],
            'box_shadow' => ['type' => 'box_shadow', 'label' => 'Box Shadow', 'tab' => 'style'],
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
        $targetDate = htmlspecialchars($settings['target_date'], ENT_QUOTES, 'UTF-8');
        $daysLabel = htmlspecialchars($settings['days_label'], ENT_QUOTES, 'UTF-8');
        $hoursLabel = htmlspecialchars($settings['hours_label'], ENT_QUOTES, 'UTF-8');
        $minutesLabel = htmlspecialchars($settings['minutes_label'], ENT_QUOTES, 'UTF-8');
        $secondsLabel = htmlspecialchars($settings['seconds_label'], ENT_QUOTES, 'UTF-8');
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $bgColor = htmlspecialchars($settings['background_color'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];

        $labels = [$daysLabel, $hoursLabel, $minutesLabel, $secondsLabel];
        $units = ['days', 'hours', 'minutes', 'seconds'];

        $html = '<div class="pb-countdown" data-target-date="' . $targetDate . '" style="text-align: ' . $alignment . ';">';
        $html .= '<div class="pb-countdown-items" style="display: flex; gap: 12px; justify-content: ' . ($alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start')) . '; flex-wrap: wrap;">';

        foreach ($units as $i => $unit) {
            $label = $labels[$i];
            $html .= '<div class="pb-countdown-box" style="min-width: 80px; padding: 16px 12px; background: ' . $bgColor . '; border-radius: 8px; text-align: center;">';
            $html .= '<div class="pb-countdown-value" data-unit="' . $unit . '" style="font-size: 36px; font-weight: 700; color: ' . $color . '; line-height: 1;">0</div>';
            $html .= '<div style="font-size: 12px; color: #6b7280; margin-top: 4px; text-transform: uppercase; letter-spacing: 1px;">' . $label . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';
        $html .= '</div>';

        $script = '<script>'
            . '(function(){'
            . 'var c=document.querySelector(".pb-countdown:not([data-bound])");'
            . 'if(!c)return;'
            . 'c.dataset.bound="1";'
            . 'var target=new Date(c.dataset.targetDate+"T23:59:59").getTime();'
            . 'function update(){'
            . 'var now=Date.now();'
            . 'var diff=Math.max(0,target-now);'
            . 'var d=Math.floor(diff/86400000);'
            . 'var h=Math.floor((diff%86400000)/3600000);'
            . 'var m=Math.floor((diff%3600000)/60000);'
            . 'var s=Math.floor((diff%60000)/1000);'
            . 'c.querySelector("[data-unit=days]").textContent=d;'
            . 'c.querySelector("[data-unit=hours]").textContent=h;'
            . 'c.querySelector("[data-unit=minutes]").textContent=m;'
            . 'c.querySelector("[data-unit=seconds]").textContent=s;'
            . '}'
            . 'update();setInterval(update,1000);'
            . '})();'
            . '</script>';

        return $html . $script;
    }

    public function renderEditor(array $settings, array $content = [], array $styles = []): string
    {
        $settings = $this->prepareSettings($settings);
        $daysLabel = htmlspecialchars($settings['days_label'], ENT_QUOTES, 'UTF-8');
        $hoursLabel = htmlspecialchars($settings['hours_label'], ENT_QUOTES, 'UTF-8');
        $minutesLabel = htmlspecialchars($settings['minutes_label'], ENT_QUOTES, 'UTF-8');
        $secondsLabel = htmlspecialchars($settings['seconds_label'], ENT_QUOTES, 'UTF-8');
        $color = htmlspecialchars($settings['color'], ENT_QUOTES, 'UTF-8');
        $bgColor = htmlspecialchars($settings['background_color'], ENT_QUOTES, 'UTF-8');
        $alignment = $settings['alignment'];

        $labels = [$daysLabel, $hoursLabel, $minutesLabel, $secondsLabel];
        $units = ['days', 'hours', 'minutes', 'seconds'];
        $sampleValues = [15, 8, 42, 37];

        $html = '<div style="text-align: ' . $alignment . '; padding: 8px 0;">';
        $html .= '<div style="display: flex; gap: 8px; justify-content: ' . ($alignment === 'center' ? 'center' : ($alignment === 'right' ? 'flex-end' : 'flex-start')) . '; flex-wrap: wrap;">';

        foreach ($units as $i => $unit) {
            $html .= '<div style="min-width: 64px; padding: 12px 8px; background: ' . $bgColor . '; border-radius: 6px; text-align: center;">';
            $html .= '<div style="font-size: 28px; font-weight: 700; color: ' . $color . '; line-height: 1;">' . $sampleValues[$i] . '</div>';
            $html .= '<div style="font-size: 10px; color: #6b7280; margin-top: 2px; text-transform: uppercase;">' . $labels[$i] . '</div>';
            $html .= '</div>';
        }

        $html .= '</div></div>';

        return $html;
    }
}
