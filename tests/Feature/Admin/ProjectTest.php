<?php

namespace Tests\Feature\Admin;

use App\Models\Concept;
use App\Models\Language;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private ProjectCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(LanguageSeeder::class);
        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->category = ProjectCategory::query()->create(['order' => 0]);
    }

    public function test_guest_cannot_access_projects(): void
    {
        $this->get(route('admin.projects.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_projects_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.projects.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Projects/Index')
                ->has('projects')
                ->has('categories'));
    }

    public function test_admin_can_create_project_with_english_name_and_slug(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.projects.store'), [
                'project_category_id' => $this->category->id,
                'client_name' => 'Acme Corp',
                'location' => 'Istanbul',
                'year' => 2026,
                'video_url' => null,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'name' => 'Noir Residence',
                        'slug' => 'noir-residence',
                        'short_description' => 'A quiet home',
                        'description' => 'Full description',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('projects', [
            'client_name' => 'Acme Corp',
            'location' => 'Istanbul',
            'year' => 2026,
            'project_category_id' => $this->category->id,
        ]);

        $this->assertDatabaseHas('project_translations', [
            'name' => 'Noir Residence',
            'slug' => 'noir-residence',
            'translation_status' => 'manual',
        ]);
    }

    public function test_admin_can_update_project_translations_and_category(): void
    {
        $otherCategory = ProjectCategory::query()->create(['order' => 1]);
        $project = $this->makeProject();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.projects.update', $project), [
                'project_category_id' => $otherCategory->id,
                'client_name' => 'Updated Client',
                'location' => 'Ankara',
                'year' => 2025,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'name' => 'Updated Residence',
                        'slug' => 'updated-residence',
                    ],
                    'tr' => [
                        'name' => 'Güncellenmiş Konut',
                        'slug' => 'guncellenmis-konut',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'project_category_id' => $otherCategory->id,
            'client_name' => 'Updated Client',
            'location' => 'Ankara',
            'year' => 2025,
        ]);

        $this->assertDatabaseHas('project_translations', [
            'project_id' => $project->id,
            'name' => 'Updated Residence',
            'slug' => 'updated-residence',
        ]);

        $this->assertDatabaseHas('project_translations', [
            'project_id' => $project->id,
            'name' => 'Güncellenmiş Konut',
            'slug' => 'guncellenmis-konut',
        ]);
    }

    public function test_admin_can_delete_project(): void
    {
        $project = $this->makeProject();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.projects.destroy', $project));

        $response->assertRedirect(route('admin.projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    public function test_admin_can_attach_concepts_and_thumbnail(): void
    {
        Storage::fake('public');

        $english = Language::query()->where('code', 'en')->firstOrFail();
        $concept = Concept::query()->create(['icon' => 'home']);
        $concept->translations()->create([
            'language_id' => $english->id,
            'title' => 'Natural light',
            'short_description' => 'Lots of windows',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.projects.store'), [
                'project_category_id' => $this->category->id,
                'client_name' => 'Media Client',
                'location' => 'Izmir',
                'year' => 2024,
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'name' => 'Coastal Villa',
                        'slug' => 'coastal-villa',
                    ],
                ],
                'concept_ids' => [$concept->id],
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
            ]);

        $response->assertRedirect(route('admin.projects.index'));

        $project = Project::query()->where('client_name', 'Media Client')->firstOrFail();

        $this->assertTrue($project->concepts->contains('id', $concept->id));
        $this->assertNotNull($project->getFirstMedia('thumbnail'));
    }

    public function test_slug_is_generated_from_name_when_empty(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.projects.store'), [
                'project_category_id' => $this->category->id,
                'client_name' => 'Slug Client',
                'location' => 'Bodrum',
                'year' => 2023,
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'name' => 'Sea House',
                        'slug' => '',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.projects.index'));

        $this->assertDatabaseHas('project_translations', [
            'name' => 'Sea House',
            'slug' => 'sea-house',
        ]);
    }

    private function makeProject(): Project
    {
        $english = Language::query()->where('code', 'en')->firstOrFail();

        $project = Project::query()->create([
            'project_category_id' => $this->category->id,
            'client_name' => 'Test Client',
            'location' => 'Istanbul',
            'year' => 2026,
        ]);

        $project->translations()->create([
            'language_id' => $english->id,
            'name' => 'Original Name',
            'slug' => 'original-name',
            'translation_status' => 'manual',
        ]);

        return $project;
    }
}
