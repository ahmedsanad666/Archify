<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Support\UiTranslations;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class UiTranslationTest extends TestCase
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

    public function test_guest_cannot_access_translations(): void
    {
        $this->get(route('admin.translations.index'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_view_translations_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.translations.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Translations/Index')
                ->has('locales')
                ->has('activeLocale')
                ->has('translations')
                ->has('groups'));
    }

    public function test_admin_can_update_existing_translation_key(): void
    {
        $path = UiTranslations::pathFor('en');
        $original = File::get($path);
        $key = 'common.save';
        $previous = UiTranslations::flatten(UiTranslations::forLocale('en'))[$key] ?? 'Save';
        $next = $previous.'*';

        try {
            $this->actingAs($this->admin)
                ->put(route('admin.translations.update'), [
                    'locale' => 'en',
                    'translations' => [
                        $key => $next,
                    ],
                ])
                ->assertRedirect(route('admin.translations.index', ['locale' => 'en']));

            $flat = UiTranslations::flatten(UiTranslations::forLocale('en'));
            $this->assertSame($next, $flat[$key]);
        } finally {
            File::put($path, $original);
        }
    }

    public function test_unknown_keys_are_ignored(): void
    {
        $path = UiTranslations::pathFor('en');
        $original = File::get($path);

        try {
            $before = UiTranslations::flatten(UiTranslations::forLocale('en'));

            $this->actingAs($this->admin)
                ->put(route('admin.translations.update'), [
                    'locale' => 'en',
                    'translations' => [
                        'this.key.does.not.exist' => 'Should not be written',
                    ],
                ])
                ->assertRedirect(route('admin.translations.index', ['locale' => 'en']));

            $after = UiTranslations::flatten(UiTranslations::forLocale('en'));
            $this->assertArrayNotHasKey('this.key.does.not.exist', $after);
            $this->assertSame($before, $after);
        } finally {
            File::put($path, $original);
        }
    }

    public function test_invalid_locale_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->put(route('admin.translations.update'), [
                'locale' => 'xx',
                'translations' => [
                    'common.save' => 'Save',
                ],
            ])
            ->assertSessionHasErrors('locale');
    }
}
