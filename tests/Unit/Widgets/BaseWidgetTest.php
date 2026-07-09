<?php

namespace Tests\Unit\Widgets;

use App\Services\PageBuilder\Widgets\BaseWidget;
use PHPUnit\Framework\TestCase;

class BaseWidgetTest extends TestCase
{
    private BaseWidget $widget;

    protected function setUp(): void
    {
        parent::setUp();

        $this->widget = new class extends BaseWidget {
            protected string $type = 'test';
            protected string $label = 'Test Widget';
            protected string $icon = 'icon-test';
            protected array $categories = ['basic'];
            protected array $defaultSettings = [
                'title' => 'Default Title',
                'count' => 5,
                'enabled' => true,
                'color' => '#ff0000',
                'url' => '',
            ];
            protected array $controls = [
                'title' => ['type' => 'text', 'max_length' => 100],
                'count' => ['type' => 'number', 'min' => 0, 'max' => 100],
                'enabled' => ['type' => 'switcher'],
                'color' => ['type' => 'color'],
                'url' => ['type' => 'url'],
            ];
            protected bool $container = false;

            public function render(array $settings, array $content = [], array $styles = []): string
            {
                return '<div>' . ($settings['title'] ?? '') . '</div>';
            }

            public function renderEditor(array $settings, array $content = [], array $styles = []): string
            {
                return $this->render($settings, $content, $styles);
            }
        };
    }

    public function test_get_type(): void
    {
        $this->assertEquals('test', $this->widget->getType());
    }

    public function test_get_label(): void
    {
        $this->assertEquals('Test Widget', $this->widget->getLabel());
    }

    public function test_get_icon(): void
    {
        $this->assertEquals('icon-test', $this->widget->getIcon());
    }

    public function test_get_categories(): void
    {
        $this->assertEquals(['basic'], $this->widget->getCategories());
    }

    public function test_get_default_settings(): void
    {
        $settings = $this->widget->getDefaultSettings();

        $this->assertArrayHasKey('title', $settings);
        $this->assertArrayHasKey('count', $settings);
        $this->assertArrayHasKey('enabled', $settings);
        $this->assertEquals('Default Title', $settings['title']);
    }

    public function test_validate_settings_merges_defaults(): void
    {
        $validated = $this->widget->validateSettings(['title' => 'Custom']);

        $this->assertEquals('Custom', $validated['title']);
        $this->assertEquals(5, $validated['count']);
        $this->assertEquals('1', $validated['enabled']);
    }

    public function test_prepare_settings_merges_with_defaults(): void
    {
        $reflection = new \ReflectionMethod($this->widget, 'prepareSettings');
        $result = $reflection->invoke($this->widget, ['title' => 'Custom']);

        $this->assertEquals('Custom', $result['title']);
        $this->assertEquals(5, $result['count']);
    }

    public function test_validate_settings_strips_html_from_text(): void
    {
        $validated = $this->widget->validateSettings(['title' => '<script>alert("xss")</script>Title']);

        $this->assertStringNotContainsString('<script>', $validated['title']);
        $this->assertStringContainsString('Title', $validated['title']);
    }

    public function test_validate_settings_clamps_numbers(): void
    {
        $validated = $this->widget->validateSettings(['count' => 200]);

        $this->assertEquals(100, $validated['count']);

        $validated = $this->widget->validateSettings(['count' => -10]);

        $this->assertEquals(0, $validated['count']);
    }

    public function test_validate_settings_validates_color(): void
    {
        $validated = $this->widget->validateSettings(['color' => '#00ff00']);

        $this->assertEquals('#00ff00', $validated['color']);

        $validated = $this->widget->validateSettings(['color' => 'not-a-color']);

        $this->assertEquals('#000000', $validated['color']);
    }

    public function test_validate_settings_validates_url(): void
    {
        $validated = $this->widget->validateSettings(['url' => 'https://example.com']);

        $this->assertEquals('https://example.com', $validated['url']);

        $validated = $this->widget->validateSettings(['url' => 'javascript:alert(1)']);

        $this->assertEquals('', $validated['url']);
    }

    public function test_is_container(): void
    {
        $this->assertFalse($this->widget->isContainer());
    }

    public function test_is_dynamic(): void
    {
        $this->assertFalse($this->widget->isDynamic());
    }
}
