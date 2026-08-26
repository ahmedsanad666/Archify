<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TestimonialTest extends TestCase
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

    public function test_guest_cannot_access_testimonials(): void
    {
        $this->get(route('admin.testimonials.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_testimonials_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.testimonials.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Testimonials/Index')
                ->has('testimonials'));
    }

    public function test_admin_can_create_testimonial_with_avatar(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.testimonials.store'), [
                'client_name' => 'Alex Morgan',
                'source_locale' => 'en',
                'auto_translate' => false,
                'avatar' => UploadedFile::fake()->image('alex.jpg'),
                'translations' => [
                    'en' => [
                        'quote' => 'An exceptional studio that redefined our space.',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', [
            'client_name' => 'Alex Morgan',
        ]);

        $this->assertDatabaseHas('testimonial_translations', [
            'quote' => 'An exceptional studio that redefined our space.',
        ]);

        $testimonial = Testimonial::query()->where('client_name', 'Alex Morgan')->first();
        $this->assertNotNull($testimonial);
        $this->assertNotNull($testimonial->getFirstMedia('avatar'));
    }

    public function test_source_quote_is_required(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.testimonials.store'), [
                'client_name' => 'Alex Morgan',
                'source_locale' => 'en',
                'translations' => [
                    'en' => ['quote' => ''],
                ],
            ])
            ->assertSessionHasErrors('translations.en.quote');
    }

    public function test_admin_can_update_testimonial(): void
    {
        $english = Language::query()->where('code', 'en')->firstOrFail();
        $testimonial = Testimonial::factory()->create([
            'client_name' => 'Old Client',
            'order' => 0,
        ]);
        $testimonial->translations()->create([
            'language_id' => $english->id,
            'quote' => 'Old quote',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.testimonials.update', $testimonial), [
                'client_name' => 'New Client',
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'quote' => 'Updated quote about craftsmanship.',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'client_name' => 'New Client',
        ]);

        $this->assertDatabaseHas('testimonial_translations', [
            'testimonial_id' => $testimonial->id,
            'quote' => 'Updated quote about craftsmanship.',
        ]);
    }

    public function test_admin_can_delete_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create(['client_name' => 'To Delete']);

        $this->actingAs($this->admin)
            ->delete(route('admin.testimonials.destroy', $testimonial))
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }

    public function test_admin_can_reorder_testimonials(): void
    {
        $first = Testimonial::factory()->create(['client_name' => 'First', 'order' => 0]);
        $second = Testimonial::factory()->create(['client_name' => 'Second', 'order' => 1]);

        $this->actingAs($this->admin)
            ->post(route('admin.testimonials.reorder'), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }
}
