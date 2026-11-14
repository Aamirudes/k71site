<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class ThemeToggleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('monetization_settings')) {
            Schema::create('monetization_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Test that the homepage header contains the theme toggle button and zero-flash bootstrapper.
     */
    public function test_homepage_renders_theme_toggle_and_bootstrapper(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);

        // Assert theme toggle button exists in header
        $response->assertSee('k71-theme-toggle-btn', false);
        $response->assertSee('localStorage.getItem(\'k71_theme\')', false);
        $response->assertSee('k71-light-mode', false);
        $response->assertSee('k71-dark-mode', false);
        $response->assertSee('Switch to Obsidian Dark Mode', false);
        $response->assertSee('Switch to Premium Light Glare', false);
    }

    /**
     * Test that vertical headers (Food, Services, Used, Enquiry) render the theme toggle cleanly.
     */
    public function test_vertical_headers_render_theme_toggle(): void
    {
        $verticals = [
            '/food',
            '/services',
            '/used',
            '/enquiry',
        ];

        foreach ($verticals as $path) {
            $response = $this->get($path);
            $response->assertStatus(200);
            $response->assertSee('k71-theme-toggle-btn', false);
            $response->assertSee('localStorage.getItem(\'k71_theme\')', false);
        }
    }
}
