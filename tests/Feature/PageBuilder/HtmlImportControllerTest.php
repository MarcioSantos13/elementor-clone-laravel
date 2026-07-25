<?php

namespace Tests\Feature\PageBuilder;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HtmlImportControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_import_html(): void
    {
        $html = '<html><head><title>Test</title></head><body><h1>Hello World</h1><p>This is a test page.</p></body></html>';

        $response = $this->actingAs($this->user)
            ->postJson(route('page-builder.html-import'), [
                'html' => $html,
            ]);

        $response->assertStatus(202);
        $response->assertJson(['queued' => true]);
    }

    public function test_import_requires_html_or_url(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('page-builder.html-import'), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['html']);
    }

    public function test_import_validates_url_format(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('page-builder.html-import'), [
                'url' => 'not-a-url',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['url']);
    }

    public function test_guest_cannot_import(): void
    {
        $response = $this->postJson(route('page-builder.html-import'), [
            'html' => '<p>test</p>',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_import_validates_html_size(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson(route('page-builder.html-import'), [
                'html' => str_repeat('a', 600000),
            ]);

        $response->assertStatus(422);
    }
}
