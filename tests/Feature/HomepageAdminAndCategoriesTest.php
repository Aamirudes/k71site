<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomepageAdminAndCategoriesTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $buyerUser;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->buyerUser = User::factory()->create([
            'first_name' => 'Riya',
            'last_name' => 'Sharma',
            'email' => 'riya.buyer@example.com',
        ]);

        $this->adminUser = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'Master',
            'email' => 'admin.studio@example.com',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
    }

    private function tinyPngUpload(string $filename = 'thumb.png'): UploadedFile
    {
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+ip1sAAAAASUVORK5CYII=');
        return UploadedFile::fake()->createWithContent($filename, $png);
    }

    private function createActiveProduct(string $title, string $vertical = 'marketplace'): Product
    {
        $seller = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $seller->id,
            'shop_name' => 'Studio ' . $seller->id,
            'slug' => 'studio-' . $seller->id . '-' . uniqid(),
            'vendor_type' => $vertical === 'food' ? 'food' : ($vertical === 'services' ? 'services' : 'clothing'),
            'status' => 'approved',
        ]);

        $cat = Category::create([
            'name' => 'Cat ' . uniqid(),
            'slug' => 'cat-' . uniqid(),
            'vertical' => $vertical === 'food' || $vertical === 'services' ? $vertical : 'marketplace',
            'is_active' => true,
            'show_on_home' => true,
        ]);

        return Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'vertical' => $vertical,
            'listing_type' => $vertical === 'services' ? 'booking' : 'purchase',
            'title' => $title,
            'slug' => 'prod-' . uniqid(),
            'description' => 'Verified item description',
            'base_price' => 49900,
            'gst_rate' => 12,
            'total_stock' => 15,
            'status' => 'active',
        ]);
    }

    public function test_homepage_places_latest_products_immediately_after_scroll_stage(): void
    {
        $this->createActiveProduct('Kishangarh Onyx Lamp');

        $response = $this->get('/');
        $response->assertStatus(200);

        $html = $response->getContent();
        $scrollPos = strpos($html, 'id="k71-scroll-stage"');
        $latestPos = strpos($html, 'id="latest-products"');
        $budgetPos = strpos($html, 'id="budget-stores"');

        $this->assertNotFalse($scrollPos, 'Scroll animation stage must render.');
        $this->assertNotFalse($latestPos, 'Latest products section must render.');
        $this->assertNotFalse($budgetPos, 'Budget section must render.');
        $this->assertTrue(
            $scrollPos < $latestPos && $latestPos < $budgetPos,
            'Latest Products must appear immediately after #k71-scroll-stage and before #budget-stores.'
        );
    }

    public function test_admin_can_toggle_hero_carousel_visibility_while_preserving_single_h1(): void
    {
        $this->actingAs($this->adminUser)
            ->get('/admin/homepage')
            ->assertStatus(200)
            ->assertSee('3-Slide Hero Banner Carousel', false)
            ->assertSee('Homepage Categories', false);

        // Default: carousel shown and 1 H1
        $defaultHtml = $this->get('/')->assertStatus(200)->getContent();
        $this->assertStringContainsString('id="hero-carousel"', $defaultHtml);
        $this->assertSame(1, substr_count($defaultHtml, '<h1'));

        // Toggle carousel OFF
        $this->actingAs($this->adminUser)
            ->put('/admin/homepage', [
                'active_tab' => 'homepage',
                'show_hero_carousel' => '0',
                'scroll_seq_enabled' => '1',
                'show_categories' => '1',
                'show_trending_products' => '1',
            ])
            ->assertRedirect();

        $this->assertFalse((bool) Setting::get('show_hero_carousel', true));

        $hiddenHtml = $this->get('/')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString('id="hero-carousel"', $hiddenHtml);
        $this->assertStringContainsString('id="k71-scroll-stage"', $hiddenHtml);
        $this->assertSame(1, substr_count($hiddenHtml, '<h1'), 'Homepage must keep exactly one <h1> when carousel is hidden.');

        // Toggle carousel back ON
        $this->actingAs($this->adminUser)
            ->put('/admin/homepage', [
                'active_tab' => 'homepage',
                'show_hero_carousel' => '1',
            ])
            ->assertRedirect();

        $restoredHtml = $this->get('/')->assertStatus(200)->getContent();
        $this->assertStringContainsString('id="hero-carousel"', $restoredHtml);
        $this->assertSame(1, substr_count($restoredHtml, '<h1'));
    }

    public function test_all_homepage_visibility_toggles_control_rendered_sections(): void
    {
        $this->createActiveProduct('Bespoke Marble Bowl', 'marketplace');
        $this->createActiveProduct('Royal Paneer Thali', 'food');
        $this->createActiveProduct('Diamond Floor Polishing', 'services');

        $this->actingAs($this->adminUser)
            ->put('/admin/homepage', [
                'show_hero_carousel' => '0',
                'scroll_seq_enabled' => '0',
                'show_categories' => '0',
                'show_trending_products' => '0',
                'show_food_showcase' => '0',
                'show_services_showcase' => '0',
                'show_value_pillars' => '0',
                'show_seller_banner' => '0',
            ])
            ->assertRedirect();

        $html = $this->get('/')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString('id="hero-carousel"', $html);
        $this->assertStringNotContainsString('id="k71-scroll-stage"', $html);
        $this->assertStringNotContainsString('id="latest-products"', $html);
        $this->assertStringNotContainsString('Artisan Kitchens', $html);
        $this->assertStringNotContainsString('Verified Services', $html);
        $this->assertStringNotContainsString('PhonePe Escrow', $html);
        $this->assertStringNotContainsString('Start Selling Direct', $html);
        $this->assertSame(1, substr_count($html, '<h1'), 'Budget section becomes <h1> when all above-fold sections are hidden.');
    }

    public function test_admin_can_add_homepage_category_with_custom_image_beyond_six_items(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            Category::create([
                'name' => "Seeded Ribbon Cat {$i}",
                'slug' => "seeded-ribbon-cat-{$i}",
                'vertical' => 'marketplace',
                'sort_order' => $i,
                'is_active' => true,
                'show_on_home' => true,
            ]);
        }

        // Non-admin blocked
        $this->actingAs($this->buyerUser)
            ->post('/admin/homepage/categories', ['name' => 'Blocked Cat'])
            ->assertRedirect();
        $this->assertDatabaseMissing('categories', ['name' => 'Blocked Cat']);

        $resp = $this->actingAs($this->adminUser)
            ->post('/admin/homepage/categories', [
                'name' => 'Italian Marble Sculptures',
                'vertical' => 'marketplace',
                'icon' => '🏛️',
                'sort_order' => 8,
                'show_on_home' => '1',
                'is_active' => '1',
                'image' => $this->tinyPngUpload('sculpture.png'),
            ]);

        $resp->assertRedirect();

        $created = Category::where('slug', 'italian-marble-sculptures')->firstOrFail();
        $this->assertTrue($created->show_on_home);
        $this->assertNotEmpty($created->image);
        Storage::disk('public')->assertExists($created->image);

        $homeHtml = $this->get('/')->assertStatus(200)->getContent();
        $this->assertStringContainsString('Seeded Ribbon Cat 1', $homeHtml);
        $this->assertStringContainsString('Seeded Ribbon Cat 7', $homeHtml);
        $this->assertStringContainsString('Italian Marble Sculptures', $homeHtml);
        $this->assertStringContainsString($created->image, $homeHtml);
    }

    public function test_admin_can_edit_category_image_homepage_toggle_and_delete_unused_category(): void
    {
        $cat = Category::create([
            'name' => 'Old Lighting',
            'slug' => 'old-lighting',
            'vertical' => 'marketplace',
            'sort_order' => 2,
            'is_active' => true,
            'show_on_home' => true,
        ]);

        $unusedCat = Category::create([
            'name' => 'Temp Disposable',
            'slug' => 'temp-disposable',
            'vertical' => 'marketplace',
            'is_active' => true,
            'show_on_home' => true,
        ]);

        // Upload new custom image & rename category
        $this->actingAs($this->adminUser)
            ->put('/admin/homepage', [
                'active_tab' => 'categories',
                'category_meta' => [
                    $cat->id => [
                        'name' => 'Designer Lighting',
                        'icon' => '💡',
                        'sort_order' => 1,
                        'show_on_home' => '1',
                        'is_active' => '1',
                    ],
                ],
                'category_images' => [
                    $cat->id => $this->tinyPngUpload('lamp-custom.png'),
                ],
            ])
            ->assertRedirect();

        $cat->refresh();
        $this->assertSame('Designer Lighting', $cat->name);
        $this->assertNotEmpty($cat->image);
        Storage::disk('public')->assertExists($cat->image);

        $firstHome = $this->get('/')->assertStatus(200)->getContent();
        $this->assertStringContainsString('Designer Lighting', $firstHome);
        $this->assertStringContainsString($cat->image, $firstHome);

        // Hide category from homepage and remove custom image
        $oldImage = $cat->image;
        $this->actingAs($this->adminUser)
            ->put('/admin/homepage', [
                'active_tab' => 'categories',
                'category_meta' => [
                    $cat->id => [
                        'name' => 'Designer Lighting',
                        'show_on_home' => '0',
                        'is_active' => '1',
                    ],
                ],
                'remove_category_image' => [
                    $cat->id => '1',
                ],
            ])
            ->assertRedirect();

        $cat->refresh();
        $this->assertFalse($cat->show_on_home);
        $this->assertNull($cat->image);
        Storage::disk('public')->assertMissing($oldImage);

        $secondHome = $this->get('/')->assertStatus(200)->getContent();
        $this->assertStringNotContainsString('data-home-category="old-lighting"', $secondHome);

        // Delete unused category
        $this->actingAs($this->adminUser)
            ->delete('/admin/homepage/categories/' . $unusedCat->id)
            ->assertRedirect();
        $this->assertDatabaseMissing('categories', ['id' => $unusedCat->id]);
    }
}

