<?php

namespace Tests\Feature\PageBuilder;

use App\Models\Element;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use App\Services\PageBuilder\Project\ProjectTemplateService;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

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

    public function test_user_can_create_complete_example_project(): void
    {
        $response = $this->actingAs($this->user)->postJson(route('page-builder.projects.example'));

        $response->assertCreated();
        $response->assertJsonPath('project.name', ProjectTemplateService::EXAMPLE_NAME);

        $project = Project::where('user_id', $this->user->id)
            ->where('name', ProjectTemplateService::EXAMPLE_NAME)
            ->firstOrFail();

        $this->assertTrue($project->settings['is_project_template']);
        $this->assertSame(9, $project->pages()->count());
        $this->assertSame(4, $project->themeTemplates()->count());
        $this->assertSame(1, $project->popups()->count());
        $this->assertSame(4, $project->pages()->where('status', 'published')->count());

        $project->pages()->with('allElements')->get()->each(function (Page $page) {
            $this->assertGreaterThan(0, $page->allElements->count(), "Página {$page->title} sem elementos");
        });
    }

    public function test_example_project_pages_render_with_theme_and_popup(): void
    {
        app(ProjectTemplateService::class)->createExample($this->user, 'Projeto Render');

        $this->get('/p/inicio')
            ->assertOk()
            ->assertSee('Construa seu próximo site com uma base pronta e editável')
            ->assertSee('Sua Marca')
            ->assertSee('Fique por dentro!');

        $this->get('/p/contato')
            ->assertOk()
            ->assertSee('Fale com a nossa equipe');

        $this->get('/p/404')
            ->assertOk()
            ->assertSee('Página não encontrada');
    }

    public function test_user_can_duplicate_project_with_pages_elements_themes_and_popups(): void
    {
        $source = app(ProjectTemplateService::class)->createExample($this->user, 'Projeto Base');

        $response = $this->actingAs($this->user)->postJson(route('page-builder.projects.duplicate', $source), [
            'name' => 'Projeto Clonado',
        ]);

        $response->assertCreated();

        $copy = Project::where('user_id', $this->user->id)
            ->where('name', 'Projeto Clonado')
            ->firstOrFail();

        $this->assertSame($source->pages()->count(), $copy->pages()->count());
        $this->assertSame($source->themeTemplates()->count(), $copy->themeTemplates()->count());
        $this->assertSame($source->popups()->count(), $copy->popups()->count());
        $this->assertSame(
            $source->pages()->with('allElements')->get()->sum(fn (Page $page) => $page->allElements->count()),
            $copy->pages()->with('allElements')->get()->sum(fn (Page $page) => $page->allElements->count()),
        );

        $this->assertEmpty(
            $source->pages()->pluck('id')->intersect($copy->pages()->pluck('id'))
        );
        $this->assertEmpty(
            $source->pages()->pluck('slug')->intersect($copy->pages()->pluck('slug'))
        );

        foreach ($copy->themeTemplates as $theme) {
            $this->assertSame($copy->id, $theme->project_id);
            $this->assertSame($copy->id, $theme->page->project_id);
        }

        foreach ($copy->popups as $popup) {
            $this->assertSame($copy->id, $popup->project_id);
            $this->assertSame($copy->id, $popup->page->project_id);
        }
    }

    public function test_user_can_export_project_as_moodle_ims_package(): void
    {
        $project = app(ProjectTemplateService::class)->createExample($this->user, 'Projeto Moodle');

        $response = $this->actingAs($this->user)->get(route('page-builder.projects.export.moodle', $project));

        $response->assertOk();
        $this->assertStringContainsString('zip', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($zipPath) === true);

        $manifest = $zip->getFromName('imsmanifest.xml');
        $this->assertNotFalse($manifest);
        $this->assertStringContainsString('IMS Content', $manifest);
        $this->assertStringContainsString('1.1.4', $manifest);

        $document = new DOMDocument;
        $this->assertTrue($document->loadXML($manifest));
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('imscp', 'http://www.imsglobal.org/xsd/imscp_v1p1');

        $this->assertSame(4, $xpath->query('//imscp:organization//imscp:item[@identifierref]')->length);
        $this->assertSame(4, $xpath->query('//imscp:resource')->length);
        $this->assertSame(4, $xpath->query('//imscp:resource/imscp:file[starts-with(@href, "pages/")]')->length);

        $home = $zip->getFromName('pages/inicio.html');
        $this->assertNotFalse($home);
        $this->assertStringContainsString('href="sobre.html"', $home);
        $this->assertStringContainsString('href="contato.html"', $home);
        $this->assertStringNotContainsString('href="/p/', $home);
        $this->assertNotFalse($zip->getFromName('pages/404.html'));
        $this->assertFalse($zip->getFromName('pages/header-principal.html'));

        $zip->close();
    }

    public function test_moodle_package_bundles_local_media_and_rewrites_urls(): void
    {
        $project = app(ProjectTemplateService::class)->createExample($this->user, 'Projeto Moodle com Mídia');
        $page = $project->pages()->where('slug', 'inicio')->firstOrFail();

        Storage::disk('public')->put('page-builder/export-test.png', 'fake-image');

        try {
            Element::create([
                'page_id' => $page->id,
                'type' => 'image',
                'name' => 'Imagem de teste',
                'order' => 999,
                'settings' => [
                    'image' => [
                        'url' => '/storage/page-builder/export-test.png',
                        'alt' => 'Teste',
                        'width' => 10,
                        'height' => 10,
                    ],
                ],
            ]);

            $response = $this->actingAs($this->user)
                ->get(route('page-builder.projects.export.moodle', $project));

            $response->assertOk();
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($response->baseResponse->getFile()->getPathname()) === true);

            $home = $zip->getFromName('pages/inicio.html');
            $this->assertNotFalse($home);
            $this->assertStringContainsString('../media/', $home);
            $this->assertStringNotContainsString('/storage/page-builder/export-test.png', $home);

            $mediaEntry = null;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = $zip->getNameIndex($i);
                if (is_string($name) && str_ends_with($name, '-export-test.png')) {
                    $mediaEntry = $name;
                    break;
                }
            }

            $this->assertNotNull($mediaEntry);
            $this->assertStringStartsWith('media/', $mediaEntry);
            $this->assertNotFalse($zip->getFromName($mediaEntry));

            $zip->close();
        } finally {
            Storage::disk('public')->delete('page-builder/export-test.png');
        }
    }

    public function test_user_cannot_export_other_users_project_to_moodle(): void
    {
        $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

        $this->actingAs($this->user)
            ->get(route('page-builder.projects.export.moodle', $project))
            ->assertForbidden();
    }

    public function test_project_without_published_pages_cannot_be_exported_to_moodle(): void
    {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->from(route('page-builder.projects.dashboard', $project))
            ->get(route('page-builder.projects.export.moodle', $project))
            ->assertRedirect(route('page-builder.projects.dashboard', $project))
            ->assertSessionHas('error');
    }

    public function test_user_cannot_duplicate_other_users_project(): void
    {
        $project = Project::factory()->create(['user_id' => $this->otherUser->id]);

        $response = $this->actingAs($this->user)->postJson(route('page-builder.projects.duplicate', $project));

        $response->assertForbidden();
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
