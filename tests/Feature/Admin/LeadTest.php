<?php

namespace Tests\Feature\Admin;

use App\Models\Lead;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadTest extends TestCase
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

    public function test_guest_cannot_access_leads(): void
    {
        $this->get(route('admin.leads.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_leads_index_paginated_at_15(): void
    {
        for ($i = 1; $i <= 16; $i++) {
            Lead::query()->create([
                'full_name' => "Lead {$i}",
                'email' => "lead{$i}@example.com",
                'message' => "Message {$i}",
                'status' => 'pending',
            ]);
        }

        $this->actingAs($this->admin)
            ->get(route('admin.leads.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Leads/Index')
                ->has('leads.data', 15)
                ->where('leads.meta.total', 16)
                ->where('leads.meta.per_page', 15)
                ->has('filters'));
    }

    public function test_admin_can_filter_leads_by_pending_status(): void
    {
        Lead::query()->create([
            'full_name' => 'Pending Lead',
            'email' => 'pending@example.com',
            'message' => 'Pending message',
            'status' => 'pending',
        ]);

        Lead::query()->create([
            'full_name' => 'Contacted Lead',
            'email' => 'contacted@example.com',
            'message' => 'Contacted message',
            'status' => 'contacted',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.leads.index', ['status' => 'pending']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Leads/Index')
                ->has('leads.data', 1)
                ->where('leads.data.0.full_name', 'Pending Lead')
                ->where('leads.data.0.status', 'pending')
                ->where('filters.status', 'pending'));
    }

    public function test_invalid_status_filter_is_ignored(): void
    {
        Lead::query()->create([
            'full_name' => 'Any Lead',
            'email' => 'any@example.com',
            'message' => 'Hello',
            'status' => 'pending',
        ]);

        Lead::query()->create([
            'full_name' => 'Other Lead',
            'email' => 'other@example.com',
            'message' => 'Hello again',
            'status' => 'contacted',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.leads.index', ['status' => 'not-a-status']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Leads/Index')
                ->has('leads.data', 2)
                ->where('filters.status', null));
    }

    public function test_admin_can_mark_lead_as_contacted(): void
    {
        $lead = Lead::query()->create([
            'full_name' => 'Pending Lead',
            'email' => 'pending@example.com',
            'message' => 'Hello',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.leads.index'))
            ->patch(route('admin.leads.update-status', $lead), [
                'status' => 'contacted',
            ])
            ->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'contacted',
        ]);
    }

    public function test_admin_can_mark_lead_back_to_pending(): void
    {
        $lead = Lead::query()->create([
            'full_name' => 'Contacted Lead',
            'email' => 'contacted@example.com',
            'message' => 'Hello',
            'status' => 'contacted',
        ]);

        $this->actingAs($this->admin)
            ->from(route('admin.leads.index'))
            ->patch(route('admin.leads.update-status', $lead), [
                'status' => 'pending',
            ])
            ->assertRedirect(route('admin.leads.index'));

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'pending',
        ]);
    }

    public function test_invalid_lead_status_update_is_rejected(): void
    {
        $lead = Lead::query()->create([
            'full_name' => 'Lead',
            'email' => 'lead@example.com',
            'message' => 'Hello',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->patch(route('admin.leads.update-status', $lead), [
                'status' => 'archived',
            ])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'status' => 'pending',
        ]);
    }
}
