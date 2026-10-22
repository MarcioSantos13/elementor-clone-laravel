<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
    }

    public function test_user_can_list_projects(): void
    {
        Project::factory()->count(2)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->get(route('page-builder.projects.index'));

        $response->assertStatus(200);
        $response->assertViewHas('projects');
    }

    public function test_listing_creates_default_project(): void
    {
        $this->actingAs($this->user)->get(route('page-builder.projects.index'))->assertStatus(200);

        $this->assertDatabaseHas('projects', [
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
    }

    public function test_user_can_create_project(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('page-builder.projects.store'), [
            'name' => 'Meu Projeto',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('projects', [
            'user_id' => $this->user->id,
            'name' => 'Meu Projeto',
        ]);
    }

    public function test_user_can_update_own_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->putJson(route('page-builder.projects.update', $project), [
            'name' => 'Renomeado',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Renomeado']);
    }

    public function test_user_cannot_update_other_users_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->putJson(route('page-builder.projects.update', $project), [
            'name' => 'Hack',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_can_delete_project_and_items_move_to_default(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        $page = Page::factory()->create([
            'user_id' => $this->user->id,
            'project_id' => $project->id,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('page-builder.projects.destroy', $project));

        $response->assertStatus(200);
        $page->refresh();
        $this->assertNotEquals($project->id, $page->project_id);
        $this->assertNotNull($page->project_id);
    }

    public function test_default_project_cannot_be_deleted(): void
    {
        $project = Project::factory()->default()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->deleteJson(route('page-builder.projects.destroy', $project));

        $response->assertStatus(422);
    }

    public function test_user_can_move_pages_to_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);
        $page = Page::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user)->postJson(route('page-builder.projects.move', $project), [
            'type' => 'page',
            'ids' => [$page->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pages', ['id' => $page->id, 'project_id' => $project->id]);
    }

    public function test_guest_cannot_access_projects(): void
    {
        $this->get(route('page-builder.projects.index'))->assertRedirect(route('login'));
    }
}
