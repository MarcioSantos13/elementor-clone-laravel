<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Page $page;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_submit_form(): void
    {
        $response = $this->postJson(route('page-builder.form.submit', $this->page), [
            'form_name' => 'Contact Form',
            'data' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'message' => 'Hello!',
            ],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('form_submissions', [
            'page_id' => $this->page->id,
            'form_name' => 'Contact Form',
        ]);
    }

    public function test_can_submit_form_with_default_name(): void
    {
        $response = $this->postJson(route('page-builder.form.submit', $this->page), [
            'data' => ['field' => 'value'],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('form_submissions', [
            'form_name' => 'Contact Form',
        ]);
    }

    public function test_form_submission_stores_ip_address(): void
    {
        $this->postJson(route('page-builder.form.submit', $this->page), [
            'data' => ['field' => 'value'],
        ]);

        $submission = $this->page->formSubmissions()->first();
        $this->assertNotEmpty($submission->ip_address);
    }

    public function test_user_can_view_submissions(): void
    {
        $this->actingAs($this->user);

        $response = $this->getJson(route('page-builder.form.submissions', $this->page));

        $response->assertStatus(200);
    }

    public function test_user_cannot_view_other_users_submissions(): void
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->getJson(route('page-builder.form.submissions', $this->page));

        $response->assertStatus(403);
    }

    public function test_form_validates_data(): void
    {
        $response = $this->postJson(route('page-builder.form.submit', $this->page), []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('data');
    }

    public function test_form_data_must_be_array(): void
    {
        $response = $this->postJson(route('page-builder.form.submit', $this->page), [
            'data' => 'not-an-array',
        ]);

        $response->assertStatus(422);
    }
}
