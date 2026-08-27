<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(LanguageSeeder::class);
    }

    public function test_guest_can_view_home_with_expected_props(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Home')
                ->has('sliders')
                ->has('about')
                ->has('projects')
                ->has('services')
                ->has('statistics')
                ->has('testimonials')
                ->has('blogs')
                ->missing('faqs')
                ->missing('coreValues'));
    }

    public function test_public_nav_stub_pages_render(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Services/Index'));

        $this->get(route('blogs.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Blog/Index'));

        $this->get(route('contact'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('Contact'));
    }
}
