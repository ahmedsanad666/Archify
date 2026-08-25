<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(LanguageSeeder::class);
    }

    public function test_home_uses_default_locale_without_prefix(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('locale.code', 'en'));

        $this->assertSame('en', app()->getLocale());
    }

    public function test_turkish_prefix_sets_locale(): void
    {
        $response = $this->get('/tr');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Home')
            ->where('locale.code', 'tr'));

        $this->assertSame('tr', app()->getLocale());
    }

    public function test_arabic_prefix_sets_rtl_locale(): void
    {
        $response = $this->get('/ar/about');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('About')
            ->where('locale.code', 'ar')
            ->where('locale.direction', 'rtl'));

        $this->assertSame('ar', app()->getLocale());
    }

    public function test_invalid_locale_returns_not_found(): void
    {
        $this->get('/xx')->assertNotFound();
    }

    public function test_public_static_pages_render(): void
    {
        $this->get('/about')->assertOk()->assertInertia(fn ($page) => $page->component('About'));
        $this->get('/team')->assertOk()->assertInertia(fn ($page) => $page->component('Team'));
        $this->get('/faq')->assertOk()->assertInertia(fn ($page) => $page->component('Faq'));
        $this->get('/tr/team')->assertOk()->assertInertia(fn ($page) => $page->component('Team'));
        $this->get('/ar/faq')->assertOk()->assertInertia(fn ($page) => $page->component('Faq'));
    }
}
