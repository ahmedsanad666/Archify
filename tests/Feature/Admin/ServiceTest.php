<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceTest extends TestCase
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

    public function test_guest_cannot_access_services(): void
    {
        $this->get(route('admin.services.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_services_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Services/Index')
                ->has('services'));
    }

    public function test_admin_can_create_service(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.services.store'), [
                'icon' => 'building-arch',
                'order' => 0,
                'show_on_home' => true,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Architecture',
                        'short_description' => 'Full-service design.',
                        'included_items' => ['Concept', 'Documentation'],
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', [
            'icon' => 'building-arch',
            'show_on_home' => 1,
        ]);

        $this->assertDatabaseHas('service_translations', [
            'title' => 'Architecture',
        ]);
    }

    public function test_invalid_icon_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.services.store'), [
                'icon' => 'not-a-real-icon',
                'source_locale' => 'en',
                'translations' => [
                    'en' => ['title' => 'Architecture'],
                ],
            ])
            ->assertSessionHasErrors('icon');
    }

    public function test_source_title_is_required(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.services.store'), [
                'icon' => 'home',
                'source_locale' => 'en',
                'translations' => [
                    'en' => ['title' => ''],
                ],
            ])
            ->assertSessionHasErrors('translations.en.title');
    }

    public function test_admin_can_update_service(): void
    {
        $english = Language::query()->where('code', 'en')->firstOrFail();
        $service = Service::query()->create([
            'icon' => 'home',
            'order' => 0,
            'show_on_home' => false,
        ]);
        $service->translations()->create([
            'language_id' => $english->id,
            'title' => 'Old',
            'short_description' => 'Old desc',
            'included_items' => [],
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.services.update', $service), [
                'icon' => 'lamp',
                'order' => 2,
                'show_on_home' => true,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Lighting',
                        'short_description' => 'Ambient strategies.',
                        'included_items' => ['Fixtures'],
                    ],
                ],
            ])
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'icon' => 'lamp',
            'show_on_home' => 1,
        ]);

        $this->assertDatabaseHas('service_translations', [
            'service_id' => $service->id,
            'title' => 'Lighting',
        ]);
    }

    public function test_admin_can_delete_service(): void
    {
        $service = Service::query()->create([
            'icon' => 'home',
            'order' => 0,
            'show_on_home' => false,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.services.destroy', $service))
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_admin_can_reorder_services(): void
    {
        $first = Service::query()->create(['icon' => 'home', 'order' => 0, 'show_on_home' => false]);
        $second = Service::query()->create(['icon' => 'lamp', 'order' => 1, 'show_on_home' => false]);

        $this->actingAs($this->admin)
            ->post(route('admin.services.reorder'), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.services.index'));

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }
}
