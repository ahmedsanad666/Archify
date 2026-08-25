<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\Statistic;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticTest extends TestCase
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

    public function test_admin_can_create_statistic(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.statistics.store'), [
                'count' => 42,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => ['label' => 'Projects completed'],
                ],
            ])
            ->assertRedirect(route('admin.about.edit', ['tab' => 'statistics']));

        $this->assertDatabaseHas('statistics', ['count' => 42]);
        $this->assertDatabaseHas('statistic_translations', [
            'label' => 'Projects completed',
        ]);
    }

    public function test_admin_can_update_statistic(): void
    {
        $statistic = Statistic::query()->create(['count' => 10, 'order' => 0]);
        $english = Language::query()->where('code', 'en')->firstOrFail();

        $statistic->translations()->create([
            'language_id' => $english->id,
            'label' => 'Old label',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.statistics.update', $statistic), [
                'count' => 25,
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => ['label' => 'Updated label'],
                ],
            ])
            ->assertRedirect(route('admin.about.edit', ['tab' => 'statistics']));

        $this->assertSame(25, $statistic->fresh()->count);
        $this->assertDatabaseHas('statistic_translations', [
            'statistic_id' => $statistic->id,
            'label' => 'Updated label',
        ]);
    }

    public function test_admin_can_delete_statistic(): void
    {
        $statistic = Statistic::query()->create(['count' => 5, 'order' => 0]);

        $this->actingAs($this->admin)
            ->delete(route('admin.statistics.destroy', $statistic))
            ->assertRedirect(route('admin.about.edit', ['tab' => 'statistics']));

        $this->assertDatabaseMissing('statistics', ['id' => $statistic->id]);
    }

    public function test_admin_can_reorder_statistics(): void
    {
        $first = Statistic::query()->create(['count' => 1, 'order' => 0]);
        $second = Statistic::query()->create(['count' => 2, 'order' => 1]);

        $this->actingAs($this->admin)
            ->post(route('admin.statistics.reorder'), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.about.edit', ['tab' => 'statistics']));

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }
}
