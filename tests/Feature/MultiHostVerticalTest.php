<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiHostVerticalTest extends TestCase
{
    use RefreshDatabase;

    public function test_main_marketplace_vertical_is_resolved_by_default(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $this->assertEquals('main', $response->viewData('k71_vertical_key'));
        $this->assertEquals('Marketplace', $response->viewData('k71_vertical')['name']);
    }

    public function test_food_vertical_is_resolved_via_subdomain(): void
    {
        $response = $this->get('http://food.k71.in/');

        $response->assertStatus(200);
        $this->assertEquals('food', $response->viewData('k71_vertical_key'));
        $this->assertEquals('Food & Restaurants', $response->viewData('k71_vertical')['name']);
    }

    public function test_shorts_vertical_is_resolved_via_subdomain(): void
    {
        $response = $this->get('http://shorts.k71.in/');

        $response->assertStatus(200);
        $this->assertEquals('shorts', $response->viewData('k71_vertical_key'));
        $this->assertEquals('Shorts Video Commerce', $response->viewData('k71_vertical')['name']);
    }

    public function test_services_vertical_is_resolved(): void
    {
        $response = $this->get('http://services.k71.in/');

        $response->assertStatus(200);
        $this->assertEquals('services', $response->viewData('k71_vertical_key'));
    }

    public function test_used_vertical_is_resolved(): void
    {
        $response = $this->get('http://used.k71.in/');

        $response->assertStatus(200);
        $this->assertEquals('used', $response->viewData('k71_vertical_key'));
    }

    public function test_enquiry_vertical_is_resolved(): void
    {
        $response = $this->get('http://enquiry.k71.in/');

        $response->assertStatus(200);
        $this->assertEquals('enquiry', $response->viewData('k71_vertical_key'));
    }
}
