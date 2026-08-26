<?php

namespace Tests\Feature;

use Database\Seeders\LanguageSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(LanguageSeeder::class);
        $this->seed(SiteSettingSeeder::class);
    }

    public function test_home_view_source_includes_global_seo_meta_tags(): void
    {
        $description = 'Archify crafts thoughtful architecture and interiors with a focus on material, light, and lasting detail.';

        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee(
            '<meta inertia="description" name="description" content="'.$description.'">',
            false,
        );
        $response->assertSee(
            '<meta inertia="keywords" name="keywords" content="architecture, interior design, Archify, residential, hospitality">',
            false,
        );
        $response->assertSee(
            '<title inertia>Archify | Architecture &amp; interior design</title>',
            false,
        );
        $response->assertSee(
            '<meta inertia="og:title" property="og:title" content="Archify | Architecture &amp; interior design">',
            false,
        );
    }
}
