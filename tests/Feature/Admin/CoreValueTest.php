<?php

namespace Tests\Feature\Admin;

use App\Models\CoreValue;
use App\Models\Language;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreValueTest extends TestCase
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

    public function test_admin_can_create_core_value(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.core-values.store'), [
                'icon' => 'compass',
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Integrity',
                        'short_description' => 'We build with honesty.',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.about.edit', ['tab' => 'core_values']));

        $this->assertDatabaseHas('core_values', ['icon' => 'compass']);
        $this->assertDatabaseHas('core_value_translations', [
            'title' => 'Integrity',
        ]);
    }

    public function test_admin_can_update_core_value(): void
    {
        $coreValue = CoreValue::query()->create(['icon' => 'compass', 'order' => 0]);
        $english = Language::query()->where('code', 'en')->firstOrFail();

        $coreValue->translations()->create([
            'language_id' => $english->id,
            'title' => 'Old title',
            'short_description' => 'Old description',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.core-values.update', $coreValue), [
                'icon' => 'leaf',
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'title' => 'Craft',
                        'short_description' => 'Details matter.',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.about.edit', ['tab' => 'core_values']));

        $this->assertSame('leaf', $coreValue->fresh()->icon);
        $this->assertDatabaseHas('core_value_translations', [
            'core_value_id' => $coreValue->id,
            'title' => 'Craft',
        ]);
    }

    public function test_admin_can_delete_core_value(): void
    {
        $coreValue = CoreValue::query()->create(['icon' => 'compass', 'order' => 0]);

        $this->actingAs($this->admin)
            ->delete(route('admin.core-values.destroy', $coreValue))
            ->assertRedirect(route('admin.about.edit', ['tab' => 'core_values']));

        $this->assertDatabaseMissing('core_values', ['id' => $coreValue->id]);
    }

    public function test_admin_can_reorder_core_values(): void
    {
        $first = CoreValue::query()->create(['icon' => 'compass', 'order' => 0]);
        $second = CoreValue::query()->create(['icon' => 'leaf', 'order' => 1]);

        $this->actingAs($this->admin)
            ->post(route('admin.core-values.reorder'), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.about.edit', ['tab' => 'core_values']));

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }
}
