<?php

namespace Tests\Feature\Admin;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCategoryTest extends TestCase
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

    public function test_guest_cannot_access_blog_categories(): void
    {
        $this->get(route('admin.blog-categories.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_blog_categories_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.blog-categories.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/BlogCategories/Index')
                ->has('categories'));
    }

    public function test_admin_can_create_category_with_english_name_slug_and_color(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.blog-categories.store'), [
                'color' => '#bd854f',
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'name' => 'Architecture',
                        'slug' => 'architecture',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.blog-categories.index'));

        $this->assertDatabaseHas('blog_categories', [
            'color' => '#bd854f',
            'order' => 0,
        ]);

        $this->assertDatabaseHas('blog_category_translations', [
            'name' => 'Architecture',
            'slug' => 'architecture',
        ]);
    }

    public function test_admin_can_update_category_translations(): void
    {
        $category = BlogCategory::query()->create([
            'color' => '#f9ba7f',
            'order' => 0,
        ]);
        $english = Language::query()->where('code', 'en')->firstOrFail();

        $category->translations()->create([
            'language_id' => $english->id,
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.blog-categories.update', $category), [
                'color' => '#d0c5b6',
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'name' => 'Interiors',
                        'slug' => 'interiors',
                    ],
                    'tr' => [
                        'name' => 'İç Mekan',
                        'slug' => 'ic-mekan',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.blog-categories.index'));

        $this->assertDatabaseHas('blog_categories', [
            'id' => $category->id,
            'color' => '#d0c5b6',
        ]);

        $this->assertDatabaseHas('blog_category_translations', [
            'blog_category_id' => $category->id,
            'name' => 'Interiors',
            'slug' => 'interiors',
        ]);

        $this->assertDatabaseHas('blog_category_translations', [
            'blog_category_id' => $category->id,
            'name' => 'İç Mekan',
            'slug' => 'ic-mekan',
        ]);
    }

    public function test_admin_can_delete_empty_category(): void
    {
        $category = BlogCategory::query()->create([
            'color' => '#f9ba7f',
            'order' => 0,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.blog-categories.destroy', $category));

        $response->assertRedirect(route('admin.blog-categories.index'));
        $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_with_blogs(): void
    {
        $category = BlogCategory::query()->create([
            'color' => '#f9ba7f',
            'order' => 0,
        ]);

        Blog::query()->create([
            'blog_category_id' => $category->id,
            'views_count' => 0,
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.blog-categories.index'))
            ->delete(route('admin.blog-categories.destroy', $category))
            ->assertRedirect(route('admin.blog-categories.index'))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseHas('blog_categories', ['id' => $category->id]);
    }

    public function test_admin_can_reorder_categories(): void
    {
        $first = BlogCategory::query()->create(['color' => '#f9ba7f', 'order' => 0]);
        $second = BlogCategory::query()->create(['color' => '#f9ba7f', 'order' => 1]);
        $third = BlogCategory::query()->create(['color' => '#f9ba7f', 'order' => 2]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.blog-categories.reorder'), [
                'ids' => [$third->id, $first->id, $second->id],
            ]);

        $response->assertRedirect(route('admin.blog-categories.index'));

        $this->assertSame(0, $third->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
        $this->assertSame(2, $second->fresh()->order);
    }

    public function test_invalid_color_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.blog-categories.store'), [
                'color' => 'not-a-hex',
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'name' => 'Design',
                        'slug' => 'design',
                    ],
                ],
            ])
            ->assertSessionHasErrors('color');
    }

    public function test_slug_is_generated_from_name_when_empty(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.blog-categories.store'), [
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'name' => 'Interior Design',
                        'slug' => '',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.blog-categories.index'));

        $this->assertDatabaseHas('blog_category_translations', [
            'name' => 'Interior Design',
            'slug' => 'interior-design',
        ]);

        $this->assertDatabaseHas('blog_categories', [
            'color' => '#f9ba7f',
        ]);
    }
}
