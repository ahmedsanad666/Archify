<?php

namespace Tests\Feature\Admin;

use App\Models\Concept;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConceptTest extends TestCase
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

    public function test_guest_cannot_access_concepts(): void
    {
        $this->get(route('admin.concepts.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_concepts_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.concepts.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Concepts/Index')
                ->has('concepts'));
    }

    public function test_admin_can_create_concept(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.concepts.store'), [
                'icon' => 'compass',
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Creativity',
                        'short_description' => 'Pushing boundaries.',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.concepts.index'));

        $this->assertDatabaseHas('concepts', [
            'icon' => 'compass',
        ]);

        $this->assertDatabaseHas('concept_translations', [
            'title' => 'Creativity',
            'short_description' => 'Pushing boundaries.',
        ]);
    }

    public function test_admin_can_update_concept(): void
    {
        $english = Language::query()->where('code', 'en')->firstOrFail();
        $concept = Concept::query()->create(['icon' => 'leaf']);
        $concept->translations()->create([
            'language_id' => $english->id,
            'title' => 'Old Title',
            'short_description' => 'Old copy',
        ]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.concepts.update', $concept), [
                'icon' => 'ruler',
                'source_locale' => 'en',
                'translations' => [
                    'en' => [
                        'title' => 'Precision',
                        'short_description' => 'Attention to detail.',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.concepts.index'));

        $this->assertDatabaseHas('concepts', [
            'id' => $concept->id,
            'icon' => 'ruler',
        ]);

        $this->assertDatabaseHas('concept_translations', [
            'concept_id' => $concept->id,
            'title' => 'Precision',
            'short_description' => 'Attention to detail.',
        ]);
    }

    public function test_admin_can_delete_concept(): void
    {
        $concept = Concept::query()->create(['icon' => 'users']);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.concepts.destroy', $concept));

        $response->assertRedirect(route('admin.concepts.index'));
        $this->assertDatabaseMissing('concepts', ['id' => $concept->id]);
    }
}
