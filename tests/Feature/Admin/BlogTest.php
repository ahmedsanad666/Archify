<?php

namespace Tests\Feature\Admin;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private BlogCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(LanguageSeeder::class);
        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $this->category = BlogCategory::query()->create([
            'color' => '#f9ba7f',
            'order' => 0,
        ]);
    }

    public function test_guest_cannot_access_blogs(): void
    {
        $this->get(route('admin.blogs.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_blogs_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.blogs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Blogs/Index')
                ->has('blogs')
                ->has('categories')
                ->has('filters'));
    }

    public function test_admin_can_create_blog_with_english_title_and_slug(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.blogs.store'), [
                'blog_category_id' => $this->category->id,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Quiet Materials',
                        'slug' => 'quiet-materials',
                        'content' => 'A long enough article body for read time calculation with many words here.',
                        'meta_title' => 'Quiet Materials SEO',
                        'meta_description' => 'About materials',
                        'meta_keywords' => 'design, materials',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.blogs.index'));

        $this->assertDatabaseHas('blogs', [
            'blog_category_id' => $this->category->id,
            'views_count' => 0,
        ]);

        $this->assertDatabaseHas('blog_translations', [
            'title' => 'Quiet Materials',
            'slug' => 'quiet-materials',
            'translation_status' => 'manual',
        ]);

        $translation = Blog::query()->first()?->translations()->first();
        $this->assertNotNull($translation?->read_time);
        $this->assertGreaterThanOrEqual(1, $translation->read_time);
    }

    public function test_slug_is_generated_from_title_when_empty(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.blogs.store'), [
                'blog_category_id' => $this->category->id,
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'title' => 'Interior Light Study',
                        'slug' => '',
                        'content' => 'Body',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.blogs.index'));

        $this->assertDatabaseHas('blog_translations', [
            'title' => 'Interior Light Study',
            'slug' => 'interior-light-study',
        ]);
    }

    public function test_admin_can_update_blog_translations(): void
    {
        $blog = $this->makeBlog();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.blogs.update', $blog), [
                'blog_category_id' => $this->category->id,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Updated Title',
                        'slug' => 'updated-title',
                        'content' => 'Updated body',
                    ],
                    'tr' => [
                        'title' => 'Güncellenmiş Başlık',
                        'slug' => 'guncellenmis-baslik',
                        'content' => 'Güncellenmiş içerik',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.blogs.index'));

        $this->assertDatabaseHas('blog_translations', [
            'blog_id' => $blog->id,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
        ]);

        $this->assertDatabaseHas('blog_translations', [
            'blog_id' => $blog->id,
            'title' => 'Güncellenmiş Başlık',
            'slug' => 'guncellenmis-baslik',
        ]);
    }

    public function test_admin_can_delete_blog(): void
    {
        $blog = $this->makeBlog();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.blogs.destroy', $blog));

        $response->assertRedirect(route('admin.blogs.index'));
        $this->assertDatabaseMissing('blogs', ['id' => $blog->id]);
    }

    public function test_invalid_category_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.blogs.store'), [
                'blog_category_id' => 99999,
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'title' => 'Post',
                        'slug' => 'post',
                    ],
                ],
            ])
            ->assertSessionHasErrors('blog_category_id');
    }

    public function test_admin_can_upload_thumbnail(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('admin.blogs.store'), [
                'blog_category_id' => $this->category->id,
                'source_locale' => 'en',
                'auto_translate' => false,
                'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
                'translations' => [
                    'en' => [
                        'title' => 'With Thumbnail',
                        'slug' => 'with-thumbnail',
                        'content' => 'Body',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.blogs.index'));

        $blog = Blog::query()->first();
        $this->assertNotNull($blog);
        $this->assertNotEmpty($blog->getFirstMediaUrl('thumbnail'));
    }

    private function makeBlog(): Blog
    {
        $blog = Blog::query()->create([
            'blog_category_id' => $this->category->id,
            'views_count' => 0,
        ]);

        $english = Language::query()->where('code', 'en')->firstOrFail();

        $blog->translations()->create([
            'language_id' => $english->id,
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => 'Original content',
            'read_time' => 1,
            'translation_status' => 'manual',
        ]);

        return $blog;
    }
}
