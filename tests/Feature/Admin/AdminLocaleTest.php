<?php

namespace Tests\Feature\Admin;

use App\Http\Middleware\SetLocale;
use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLocaleTest extends TestCase
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

    public function test_guest_cannot_update_admin_locale(): void
    {
        $this->put(route('admin.locale.update'), ['locale' => 'tr'])
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_switch_ui_locale_to_turkish(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.dashboard'))
            ->put(route('admin.locale.update'), ['locale' => 'tr'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertSame('tr', session(SetLocale::ADMIN_SESSION_KEY));

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale.code', 'tr')
                ->where('ui.admin.menu.dashboard', 'Panel'));
    }

    public function test_admin_can_switch_ui_locale_to_arabic_rtl(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.locale.update'), ['locale' => 'ar'])
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale.code', 'ar')
                ->where('locale.direction', 'rtl')
                ->where('ui.admin.menu.dashboard', 'لوحة التحكم'));
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.locale.update'), ['locale' => 'xx'])
            ->assertSessionHasErrors('locale');
    }

    public function test_public_locale_is_unaffected_by_admin_session(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.locale.update'), ['locale' => 'tr']);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->where('locale.code', 'en'));
    }
}
