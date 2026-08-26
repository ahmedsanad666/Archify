<?php

namespace Tests\Feature\Admin;

use App\Models\TeamMember;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
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

    public function test_guest_cannot_access_media_library(): void
    {
        $this->get(route('admin.media.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_media_library_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/MediaLibrary/Index')
                ->has('media')
                ->has('filters')
                ->has('modelTypes')
                ->has('collections'));
    }

    public function test_admin_can_filter_media_by_model_type_and_collection(): void
    {
        Storage::fake('public');

        $member = TeamMember::factory()->create(['name' => 'Filter Me']);
        $member
            ->addMedia(UploadedFile::fake()->image('avatar.jpg'))
            ->toMediaCollection('avatar');

        $this->actingAs($this->admin)
            ->get(route('admin.media.index', [
                'model_type' => TeamMember::class,
                'collection' => 'avatar',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/MediaLibrary/Index')
                ->where('filters.model_type', TeamMember::class)
                ->where('filters.collection', 'avatar')
                ->has('media.data', 1));
    }

    public function test_admin_can_delete_media(): void
    {
        Storage::fake('public');

        $member = TeamMember::factory()->create(['name' => 'With Avatar']);
        $media = $member
            ->addMedia(UploadedFile::fake()->image('to-delete.jpg'))
            ->toMediaCollection('avatar');

        $this->assertDatabaseHas('media', ['id' => $media->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.media.index'))
            ->delete(route('admin.media.destroy', $media->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('media', ['id' => $media->id]);
        $this->assertNull(Media::query()->find($media->id));
    }
}
