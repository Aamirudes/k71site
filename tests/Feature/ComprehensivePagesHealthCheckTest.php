<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\Role;
use App\Models\Short;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensivePagesHealthCheckTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $vendorUser;
    protected User $buyerUser;
    protected Vendor $vendor;
    protected Product $product;
    protected Product $service;
    protected Short $short;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Admin
        $this->adminUser = User::factory()->create([
            'first_name' => 'Admin',
            'last_name' => 'K71',
            'email' => 'admin@k71.test',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // 2. Buyer
        $this->buyerUser = User::factory()->create([
            'first_name' => 'Buyer',
            'last_name' => 'User',
            'email' => 'buyer@k71.test',
        ]);

        // 3. Vendor
        $this->vendorUser = User::factory()->create([
            'first_name' => 'Vendor',
            'last_name' => 'Pro',
            'email' => 'vendor@k71.test',
        ]);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Marble & Design Studio',
            'slug' => 'marble-design-studio',
            'vendor_type' => 'both',
            'status' => 'approved',
            'city' => 'Jaipur',
            'hourly_rate' => 1500,
            'rating' => 4.9,
            'completed_projects' => 20,
            'is_verified_pro' => true,
        ]);

        $cat = Category::create([
            'name' => 'Italian Marble',
            'slug' => 'italian-marble',
            'vertical' => 'ecommerce',
            'is_active' => true,
        ]);

        $serviceCat = Category::create([
            'name' => 'Architectural Consulting',
            'slug' => 'architectural-consulting',
            'vertical' => 'services',
            'is_active' => true,
        ]);

        $this->product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $cat->id,
            'title' => 'Statuario White Slab',
            'slug' => 'statuario-white-slab',
            'description' => 'Fine Italian Statuario white marble slab',
            'base_price' => 1500000,
            'gst_rate' => 18,
            'vertical' => 'ecommerce',
            'listing_type' => 'product',
            'status' => 'active',
        ]);

        $this->service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $serviceCat->id,
            'title' => 'Luxury Villa Interior Architecture Design',
            'slug' => 'luxury-villa-interior-design',
            'description' => 'Complete blueprints and 3D architectural renders',
            'base_price' => 500000,
            'gst_rate' => 18,
            'vertical' => 'services',
            'listing_type' => 'gig',
            'status' => 'active',
            'faqs_json' => [['q' => 'What CAD files are supplied?', 'a' => 'DWG and PDF']],
        ]);

        ProductGigPackage::create([
            'product_id' => $this->service->id,
            'tier' => 'basic',
            'title' => 'Floor Plan Concept',
            'description' => '2D Floor layout and spatial arrangement',
            'price' => 5000,
            'delivery_days' => 3,
            'revisions' => 2,
            'features_json' => ['2D Layout', 'PDF Export'],
        ]);

        $this->short = Short::create([
            'vendor_id' => $this->vendor->id,
            'title' => 'Polishing Statuario Marble',
            'slug' => 'polishing-statuario-marble',
            'video_url' => 'https://example.com/test-short.mp4',
            'status' => 'approved',
        ]);
    }

    /**
     * Check public marketplace pages for no 500 errors.
     */
    public function test_public_pages_do_not_throw_500_errors(): void
    {
        $urls = [
            '/',
            '/products',
            '/products/' . $this->product->slug,
            '/shorts',
            '/services',
            '/services/' . $this->service->slug,
            '/services/provider/' . $this->vendor->slug,
            '/services/become-seller',
            '/login',
            '/register',
            '/admin/login',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(500, $response->getStatusCode(), "URL [{$url}] failed with HTTP 500!");
            $this->assertTrue(in_array($response->getStatusCode(), [200, 302]), "URL [{$url}] unexpected status: " . $response->getStatusCode());
        }
    }

    /**
     * Check authenticated customer pages for no 500 errors.
     */
    public function test_authenticated_customer_pages_do_not_throw_500(): void
    {
        $this->actingAs($this->buyerUser);

        $urls = [
            '/services/my-orders',
            '/services/order/' . $this->service->id . '?tier=basic',
            '/cart',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(500, $response->getStatusCode(), "Customer URL [{$url}] failed with HTTP 500!");
        }
    }

    /**
     * Check authenticated vendor pages for no 500 errors.
     */
    public function test_authenticated_vendor_pages_do_not_throw_500(): void
    {
        $this->actingAs($this->vendorUser);

        $urls = [
            '/vendor/dashboard',
            '/vendor/services',
            '/vendor/services/create',
            '/vendor/service-orders',
            '/vendor/products',
            '/vendor/shorts',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(500, $response->getStatusCode(), "Vendor URL [{$url}] failed with HTTP 500!");
            $this->assertTrue(in_array($response->getStatusCode(), [200, 302]), "Vendor URL [{$url}] status: " . $response->getStatusCode());
        }
    }

    /**
     * Check authenticated master admin superpower pages for no 500 errors.
     */
    public function test_authenticated_admin_pages_do_not_throw_500(): void
    {
        $this->actingAs($this->adminUser);
        session()->put('admin_authenticated', true);
        session()->put('admin_2fa_verified', true);

        $urls = [
            '/admin/dashboard',
            '/admin/users',
            '/admin/services',
            '/admin/products',
            '/admin/shorts',
            '/admin/vendors',
            '/admin/orders',
            '/admin/homepage',
            '/admin/navigation',
            '/admin/reports',
            '/admin/promotions',
            '/admin/audit-logs',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(500, $response->getStatusCode(), "Admin URL [{$url}] failed with HTTP 500!");
            $this->assertTrue(in_array($response->getStatusCode(), [200, 302]), "Admin URL [{$url}] status: " . $response->getStatusCode());
        }
    }
}
