<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\ProjectCategory;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(LanguageSeeder::class);
        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
    }

    public function test_guest_cannot_access_project_categories(): void
    {
        $this->get(route('admin.project-categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_project_categories_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.project-categories.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Categories/Index')
                ->has('categories'));
    }

    public function test_admin_can_create_category_with_english_name_and_slug(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.project-categories.store'), [
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'name' => 'Residential',
                        'slug' => 'residential',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.project-categories.index'));

        $this->assertDatabaseHas('project_categories', [
            'order' => 0,
        ]);

        $this->assertDatabaseHas('project_category_translations', [
            'name' => 'Residential',
            'slug' => 'residential',
        ]);
    }

    public function test_admin_can_update_category_translations(): void
    {
        $category = ProjectCategory::query()->create(['order' => 0]);
        $english = Language::query()->where('code', 'en')->firstOrFail();

        $category->translations()->create([
            'language_id' => $english->id,
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.project-categories.update', $category), [
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'name' => 'Commercial',
                        'slug' => 'commercial',
                    ],
                    'tr' => [
                        'name' => 'Ticari',
                        'slug' => 'ticari',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.project-categories.index'));

        $this->assertDatabaseHas('project_category_translations', [
            'project_category_id' => $category->id,
            'name' => 'Commercial',
            'slug' => 'commercial',
        ]);

        $this->assertDatabaseHas('project_category_translations', [
            'project_category_id' => $category->id,
            'name' => 'Ticari',
            'slug' => 'ticari',
        ]);
    }

    public function test_admin_can_delete_category(): void
    {
        $category = ProjectCategory::query()->create(['order' => 0]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.project-categories.destroy', $category));

        $response->assertRedirect(route('admin.project-categories.index'));
        $this->assertDatabaseMissing('project_categories', ['id' => $category->id]);
    }

    public function test_admin_can_reorder_categories(): void
    {
        $first = ProjectCategory::query()->create(['order' => 0]);
        $second = ProjectCategory::query()->create(['order' => 1]);
        $third = ProjectCategory::query()->create(['order' => 2]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.project-categories.reorder'), [
                'ids' => [$third->id, $first->id, $second->id],
            ]);

        $response->assertRedirect(route('admin.project-categories.index'));

        $this->assertSame(0, $third->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
        $this->assertSame(2, $second->fresh()->order);
    }

    public function test_slug_is_generated_from_name_when_empty(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.project-categories.store'), [
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'name' => 'Interior Design',
                        'slug' => '',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.project-categories.index'));

        $this->assertDatabaseHas('project_category_translations', [
            'name' => 'Interior Design',
            'slug' => 'interior-design',
        ]);
    }
}
