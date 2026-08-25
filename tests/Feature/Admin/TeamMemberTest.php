<?php

namespace Tests\Feature\Admin;

use App\Models\Language;
use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TeamMemberTest extends TestCase
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

    public function test_guest_cannot_access_team_members(): void
    {
        $this->get(route('admin.team-members.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_team_members_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.team-members.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Team/Index')
                ->has('members'));
    }

    public function test_admin_can_create_team_member_with_avatar(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.team-members.store'), [
                'name' => 'Sara Demir',
                'linkedin_url' => 'https://linkedin.com/in/sara',
                'behance_url' => null,
                'instagram_url' => null,
                'source_locale' => 'en',
                'auto_translate' => false,
                'avatar' => UploadedFile::fake()->image('sara.jpg'),
                'translations' => [
                    'en' => [
                        'role' => 'Principal Architect',
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.team-members.index'));

        $this->assertDatabaseHas('team_members', [
            'name' => 'Sara Demir',
            'linkedin_url' => 'https://linkedin.com/in/sara',
        ]);

        $this->assertDatabaseHas('team_member_translations', [
            'role' => 'Principal Architect',
        ]);

        $member = TeamMember::query()->where('name', 'Sara Demir')->first();
        $this->assertNotNull($member);
        $this->assertNotNull($member->getFirstMedia('avatar'));
    }

    public function test_source_role_is_required(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.team-members.store'), [
                'name' => 'Sara Demir',
                'source_locale' => 'en',
                'translations' => [
                    'en' => ['role' => ''],
                ],
            ])
            ->assertSessionHasErrors('translations.en.role');
    }

    public function test_admin_can_update_team_member(): void
    {
        $english = Language::query()->where('code', 'en')->firstOrFail();
        $member = TeamMember::factory()->create([
            'name' => 'Old Name',
            'order' => 0,
            'linkedin_url' => null,
        ]);
        $member->translations()->create([
            'language_id' => $english->id,
            'role' => 'Old Role',
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.team-members.update', $member), [
                'name' => 'New Name',
                'linkedin_url' => 'https://linkedin.com/in/new',
                'behance_url' => 'https://behance.net/new',
                'instagram_url' => 'https://instagram.com/new',
                'source_locale' => 'en',
                'auto_translate' => false,
                'translations' => [
                    'en' => [
                        'role' => 'Design Director',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.team-members.index'));

        $this->assertDatabaseHas('team_members', [
            'id' => $member->id,
            'name' => 'New Name',
            'linkedin_url' => 'https://linkedin.com/in/new',
            'behance_url' => 'https://behance.net/new',
            'instagram_url' => 'https://instagram.com/new',
        ]);

        $this->assertDatabaseHas('team_member_translations', [
            'team_member_id' => $member->id,
            'role' => 'Design Director',
        ]);
    }

    public function test_admin_can_delete_team_member(): void
    {
        $member = TeamMember::factory()->create(['name' => 'To Delete']);

        $this->actingAs($this->admin)
            ->delete(route('admin.team-members.destroy', $member))
            ->assertRedirect(route('admin.team-members.index'));

        $this->assertDatabaseMissing('team_members', ['id' => $member->id]);
    }

    public function test_admin_can_reorder_team_members(): void
    {
        $first = TeamMember::factory()->create(['name' => 'First', 'order' => 0]);
        $second = TeamMember::factory()->create(['name' => 'Second', 'order' => 1]);

        $this->actingAs($this->admin)
            ->post(route('admin.team-members.reorder'), [
                'ids' => [$second->id, $first->id],
            ])
            ->assertRedirect(route('admin.team-members.index'));

        $this->assertSame(0, $second->fresh()->order);
        $this->assertSame(1, $first->fresh()->order);
    }
}
