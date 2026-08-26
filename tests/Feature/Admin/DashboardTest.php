<?php

namespace Tests\Feature\Admin;

use App\Models\Lead;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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
            'name' => 'Elias',
        ]);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_dashboard_with_expected_props(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->has('greeting')
                ->where('greeting.admin_name', 'Elias')
                ->has('stats.projects')
                ->has('stats.services')
                ->has('stats.leads_this_week')
                ->has('stats.page_views')
                ->has('traffic')
                ->where('traffic.range', '30d')
                ->has('traffic.labels')
                ->has('traffic.values')
                ->has('recent_leads')
                ->has('recent_projects')
                ->has('pending_leads_count'));
    }

    public function test_dashboard_reflects_leads_and_projects(): void
    {
        $older = Lead::query()->create([
            'full_name' => 'Julianne Moore',
            'email' => 'julianne@example.com',
            'message' => 'Hello',
            'status' => 'pending',
            'interest_other' => 'Residential inquiry',
        ]);
        $older->forceFill([
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ])->save();

        $newer = Lead::query()->create([
            'full_name' => 'Marcus Thorne',
            'email' => 'marcus@example.com',
            'message' => 'Hi',
            'status' => 'contacted',
        ]);
        $newer->forceFill([
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ])->save();

        Project::factory()->create(['views_count' => 10]);
        Project::factory()->create(['views_count' => 5]);

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Dashboard')
                ->where('stats.projects.value', 2)
                ->where('stats.page_views.value', 15)
                ->where('pending_leads_count', 1)
                ->has('recent_leads', 2)
                ->where('recent_leads.0.full_name', 'Marcus Thorne')
                ->has('recent_projects', 2));
    }

    public function test_invalid_traffic_range_falls_back_to_30d(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['traffic_range' => 'invalid']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('traffic.range', '30d'));
    }

    public function test_traffic_range_7d_is_accepted(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.dashboard', ['traffic_range' => '7d']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('traffic.range', '7d')
                ->has('traffic.labels', 7)
                ->has('traffic.values', 7));
    }
}
