<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DeliveryPartner;
use App\Models\Product;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ProductionFixesRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function createVendorAccount(string $slug = 'gaddito', string $shopName = 'Gaddito'): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create([
            'email' => "{$slug}@vendor.test",
            'password' => bcrypt('secret123'),
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'slug' => $slug,
            'vendor_type' => 'electronics',
            'status' => 'approved',
        ]);
        $this->grantVendorTestSubscription($vendor, 'all');

        return [$user, $vendor];
    }

    public function test_purge_fake_delivery_partners_migration_removes_fake_riders_only(): void
    {
        $fleetRole = Role::firstOrCreate(['name' => 'fleet'], ['display_name' => 'Fleet Partner']);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);

        $realUser = User::factory()->create(['email' => 'realrider@k71.in']);
        $realUser->roles()->attach($fleetRole);
        $realPartner = DeliveryPartner::create([
            'user_id' => $realUser->id,
            'name' => 'Vikram Real Rider',
            'phone' => '9829099111',
            'vehicle_type' => 'bike',
            'vehicle_number' => 'RJ-14-ZZ-9901',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'is_online' => true,
            'is_verified' => true,
        ]);

        $fakeUnlinked = DeliveryPartner::create([
            'name' => 'Orphan Fake Pilot',
            'phone' => '9829000001',
            'vehicle_type' => 'bike',
            'vehicle_number' => 'RJ-99-XX-0001',
            'city' => 'Kishangarh',
            'state' => 'Rajasthan',
        ]);

        $fakeSafiya = User::factory()->create(['email' => 'safiya.fake@k71.in']);
        $fakeSafiya->roles()->attach($customerRole);
        $safiyaPartner = DeliveryPartner::create([
            'user_id' => $fakeSafiya->id,
            'name' => 'Safiya Buyer',
            'phone' => '9829012345',
            'vehicle_type' => 'bike',
            'vehicle_number' => 'RJ-01-AB-1234',
            'city' => 'Kishangarh',
            'state' => 'Rajasthan',
        ]);

        $migration = require database_path('migrations/2026_11_10_160000_purge_fake_delivery_partners.php');
        $migration->up();

        $this->assertDatabaseHas('delivery_partners', ['id' => $realPartner->id]);
        $this->assertDatabaseMissing('delivery_partners', ['id' => $fakeUnlinked->id]);
        $this->assertDatabaseMissing('delivery_partners', ['id' => $safiyaPartner->id]);
    }

    public function test_fleet_login_and_navigation_work_like_vendor_and_never_show_fake_fallbacks(): void
    {
        $fleetRole = Role::firstOrCreate(['name' => 'fleet'], ['display_name' => 'Fleet Partner']);
        $rider = User::factory()->create([
            'first_name' => 'Arvind',
            'last_name' => 'Kumar',
            'email' => 'arvind.fleet@test.com',
            'password' => bcrypt('FleetPass123!'),
        ]);
        $rider->roles()->attach($fleetRole);

        DeliveryPartner::create([
            'user_id' => $rider->id,
            'name' => 'Arvind Kumar',
            'phone' => '9811122233',
            'vehicle_type' => 'bike',
            'vehicle_number' => null,
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'is_online' => true,
            'is_verified' => true,
        ]);

        // Login page renders Fleet Partner role pre-selected via ?role=fleet
        $this->get('/login?role=fleet')
            ->assertOk()
            ->assertSee('Fleet Partner')
            ->assertSee('delivery-partner');

        // Authenticating with role=fleet redirects directly to delivery dashboard
        $loginResp = $this->post('/login', [
            'role' => 'fleet',
            'login' => 'arvind.fleet@test.com',
            'password' => 'FleetPass123!',
        ]);
        $loginResp->assertRedirect(route('delivery.dashboard'));

        $this->actingAs($rider)
            ->get(route('delivery.dashboard'))
            ->assertOk()
            ->assertSee('Arvind Kumar')
            ->assertSee('Plate not added')
            ->assertDontSee('K71 Pilot')
            ->assertDontSee('RJ-01-AB-1234');

        // Vendor dashboard sees real registered fleet rider without fake plates or fake GPS ETAs
        [$vendorUser, $vendor] = $this->createVendorAccount('gaddito', 'Gaddito');
        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('Arvind Kumar')
            ->assertSee('GPS pending')
            ->assertDontSee('RJ-21-SD-4091')
            ->assertDontSee('1.2 km');
    }

    public function test_vendor_design_omits_top_banner_and_uses_canonical_subdomain_links(): void
    {
        config([
            'marketplace.domain' => 'marblemandi.org',
            'marketplace.use_subdomains' => true,
        ]);

        [$vendorUser, $vendor] = $this->createVendorAccount('gaddito', 'Gaddito');

        $designResp = $this->actingAs($vendorUser)->get('/vendor/design');
        $designResp->assertOk()
            ->assertDontSee('PRODUCT-FIRST STORE ENGINE')
            ->assertDontSee('Choose from ten signature website designs')
            ->assertSee('gaddito.marblemandi.org')
            ->assertDontSee('/s/gaddito')
            ->assertSee('Slide 1 Text Color')
            ->assertSee('Slide 2 Text Color')
            ->assertSee('Slide 3 Text Color')
            ->assertSee('hc-upload-progress-wrap')
            ->assertDontSee('Currently displaying 5 curated');

        // /s/{slug} must render 200 (never 302 loop when reverse-proxied by Cloudflare Worker)
        // while canonical & subdomain URLs resolve to gaddito.marblemandi.org
        $this->assertStringEndsWith('gaddito.marblemandi.org', $vendor->storefront_url);
        $this->get('https://marblemandi.org/s/gaddito')
            ->assertOk()
            ->assertSee('gaddito.marblemandi.org');
    }

    public function test_hero_slide_text_colors_happy_customers_upload_and_reviews_slider(): void
    {
        [$vendorUser, $vendor] = $this->createVendorAccount('gaddito', 'Gaddito');

        $category = Category::create([
            'name' => 'Decor',
            'slug' => 'decor',
            'status' => 'active',
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Marble Side Table',
            'slug' => 'marble-side-table',
            'description' => 'Handcrafted stone table.',
            'base_price' => 499900,
            'gst_rate' => 12.00,
            'status' => 'active',
            'total_stock' => 5,
        ]);

        // 1. Default storefront has ZERO fake happy customers and ZERO fake reviews
        $emptyStorefront = $this->get('/s/gaddito');
        $emptyStorefront->assertOk()
            ->assertDontSee('Ananya Sharma')
            ->assertDontSee('Meera & Rajesh Patel')
            ->assertDontSee('Aditi Sharma')
            ->assertDontSee('Karan Verma')
            ->assertDontSee('Pooja Reddy')
            ->assertSee('Verified Product Reviews');

        // 2. Story submission without media is rejected cleanly (422) instead of using fake stock photos
        $missingMediaResp = $this->actingAs($vendorUser)->postJson('/vendor/design/quick-settings', [
            'new_hc_name' => 'Rohan Mehta',
            'new_hc_comment' => 'Great stone quality!',
        ]);
        $missingMediaResp->assertStatus(422)
            ->assertJson(['status' => 'error']);

        // 3. Customize all 3 slide text colors + upload real customer image file
        $mirrorDir = public_path("storage/vendors/{$vendor->id}");
        $storageDir = storage_path("app/public/vendors/{$vendor->id}");

        try {
            $saveResp = $this->actingAs($vendorUser)->post('/vendor/design/quick-settings', [
                'slides' => [
                    0 => ['title' => 'Slide One', 'subtitle' => 'First', 'button_text' => 'Shop', 'link' => '#catalog', 'text_color' => '#fbbf24'],
                    1 => ['title' => 'Slide Two', 'subtitle' => 'Second', 'button_text' => 'Explore', 'link' => '#catalog', 'text_color' => '#38bdf8'],
                    2 => ['title' => 'Slide Three', 'subtitle' => 'Third', 'button_text' => 'Discover', 'link' => '#catalog', 'text_color' => '#f43f5e'],
                ],
                'happy_customers_enabled' => '1',
                'new_hc_name' => 'Farhan Qureshi',
                'new_hc_location' => 'Jaipur, RJ',
                'new_hc_rating' => 5,
                'new_hc_comment' => 'Genuine artisan polish and intact packaging.',
                'new_hc_media_file' => UploadedFile::fake()->image('happy-buyer.jpg', 400, 600),
            ], ['Accept' => 'application/json']);

            $saveResp->assertOk()->assertJson(['status' => 'success']);

            $cfg = $vendor->fresh()->storefrontConfig->published_config;
            $this->assertEquals('#fbbf24', $cfg['hero']['slides'][0]['text_color']);
            $this->assertEquals('#38bdf8', $cfg['hero']['slides'][1]['text_color']);
            $this->assertEquals('#f43f5e', $cfg['hero']['slides'][2]['text_color']);

            $uploadedRelUrl = $cfg['happy_customers']['items'][0]['media_url'];
            $this->assertStringStartsWith('/storage/', $uploadedRelUrl);
            $this->assertFileExists(public_path(ltrim($uploadedRelUrl, '/')));

            // Add 2 real approved buyer reviews so reviews horizontal slider activates
            $buyer1 = User::factory()->create(['first_name' => 'Zoya', 'last_name' => 'Khan']);
            $buyer2 = User::factory()->create(['first_name' => 'Dev', 'last_name' => 'Joshi']);

            Review::create([
                'product_id' => $product->id,
                'user_id' => $buyer1->id,
                'rating' => 5,
                'title' => 'Flawless packaging',
                'body' => 'Delivered in 48 hours with wooden crating.',
                'is_verified_purchase' => true,
                'is_approved' => true,
            ]);
            Review::create([
                'product_id' => $product->id,
                'user_id' => $buyer2->id,
                'rating' => 4,
                'title' => 'Solid craft',
                'body' => 'Heavy marble feel and exact dimensions.',
                'is_verified_purchase' => true,
                'is_approved' => true,
            ]);

            $liveStorefront = $this->get('/s/gaddito');
            $liveStorefront->assertOk()
                // Per-slide hero text colors rendered
                ->assertSee('color: #fbbf24;', false)
                ->assertSee('color: #38bdf8;', false)
                ->assertSee('color: #f43f5e;', false)
                // Real happy customer rendered with non-collapsing mobile card class
                ->assertSee('Farhan Qureshi')
                ->assertSee('hc-card')
                ->assertDontSee('Ananya Sharma')
                // Real reviews rendered in 1.1 mobile slider
                ->assertSee('Zoya')
                ->assertSee('Dev')
                ->assertSee('rev-slide-card')
                ->assertSee('w-[84vw]', false)
                ->assertDontSee('Aditi Sharma');
        } finally {
            File::deleteDirectory($mirrorDir);
            File::deleteDirectory($storageDir);
        }
    }
}

