<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    protected function authHeaders(array $extra = []): array
    {
        return array_merge([
            'Authorization' => "Bearer {$this->token}",
            'Accept' => 'application/json',
        ], $extra);
    }

    public function test_can_get_user(): void
    {
        $response = $this->getJson('/api/user', $this->authHeaders());
        $response->assertStatus(200);
        $response->assertJsonPath('email', $this->user->email);
    }

    public function test_unauthenticated_cannot_access_api(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
    }

    public function test_can_create_token(): void
    {
        $response = $this->postJson('/api/tokens', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['token', 'user']);
    }

    public function test_cannot_create_token_with_wrong_password(): void
    {
        $response = $this->postJson('/api/tokens', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401);
    }

    public function test_can_revoke_token(): void
    {
        $response = $this->deleteJson('/api/tokens', [], $this->authHeaders());
        $response->assertStatus(200);
    }

    public function test_can_list_pages_via_api(): void
    {
        Page::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/pages', $this->authHeaders());
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_create_page_via_api(): void
    {
        $response = $this->postJson('/api/pages', [
            'title' => 'API Page',
        ], $this->authHeaders());

        $response->assertStatus(201);
        $response->assertJsonPath('page.title', 'API Page');
        $this->assertDatabaseHas('pages', ['title' => 'API Page']);
    }

    public function test_can_show_page_via_api(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson("/api/pages/{$page->id}", $this->authHeaders());
        $response->assertStatus(200);
        $response->assertJsonPath('page.id', $page->id);
    }

    public function test_can_update_page_via_api(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->putJson("/api/pages/{$page->id}", [
            'title' => 'Updated Title',
        ], $this->authHeaders());

        $response->assertStatus(200);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Updated Title']);
    }

    public function test_can_delete_page_via_api(): void
    {
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson("/api/pages/{$page->id}", [], $this->authHeaders());
        $response->assertStatus(200);
        $this->assertSoftDeleted($page);
    }

    public function test_cannot_update_other_users_page_via_api(): void
    {
        $otherPage = Page::factory()->create();

        $response = $this->putJson("/api/pages/{$otherPage->id}", [
            'title' => 'Hacked',
        ], $this->authHeaders());

        $response->assertStatus(403);
    }
}
