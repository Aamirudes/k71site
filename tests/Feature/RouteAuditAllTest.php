<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteAuditAllTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $vendorUser;
    protected User $adminUser;
    protected Vendor $vendor;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);

        $this->vendorUser = User::factory()->create();
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Audit Premier Store',
            'slug' => 'audit-premier-store',
            'status' => 'approved',
            'vendor_type' => 'clothing',
            'phone' => '9876543210',
            'email' => 'vendor@audit.test',
            'address' => '123 Market St',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ]);

        $this->category = Category::create([
            'name' => 'Audit Electronics',
            'slug' => 'audit-electronics',
            'is_active' => true,
        ]);
    }

    public function test_all_public_marketplace_pages_return_ok_without_500(): void
    {
        $publicUrls = [
            '/',
            '/catalog',
            '/food',
            '/services',
            '/used',
            '/enquiry',
            '/b2b',
            '/stores',
            '/cart',
            '/wishlist',
            '/shorts',
            '/about-us',
            '/contact',
            '/terms-and-conditions',
            '/privacy-policy',
            '/refund-policy',
            '/shipping-policy',
            '/sell',
            '/login',
            '/register',
            '/enquiry/create',
        ];

        foreach ($publicUrls as $url) {
            $response = $this->get($url);
            $this->assertNotEquals(500, $response->getStatusCode(), "URL [{$url}] returned 500 Internal Server Error.");
            $this->assertTrue(
                in_array($response->getStatusCode(), [200, 301, 302]),
                "URL [{$url}] returned unexpected status: {$response->getStatusCode()}"
            );
        }
    }

    public function test_vertical_detail_pages_load_cleanly(): void
    {
        // 1. Retail Product
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'title' => 'Audit Flagship Laptop Pro',
            'slug' => 'audit-flagship-laptop-pro',
            'description' => 'Premium laptop description.',
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'base_price' => 7500000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
            'is_featured' => true,
        ]);

        $resProduct = $this->get('/product/' . $product->slug);
        $this->assertNotEquals(500, $resProduct->getStatusCode());
        $this->assertTrue(in_array($resProduct->getStatusCode(), [200, 302]));

        // 2. Used Product
        $usedItem = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'title' => 'Pre-owned DSLR 24MP',
            'slug' => 'pre-owned-dslr-24mp',
            'description' => 'Good condition with box.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 2500000,
            'gst_rate' => 0,
            'total_stock' => 1,
            'status' => 'active',
        ]);

        $resUsed = $this->get('/used/' . $usedItem->slug);
        $this->assertNotEquals(500, $resUsed->getStatusCode());
        $this->assertTrue(in_array($resUsed->getStatusCode(), [200, 302]));

        // 3. Service Product
        $serviceItem = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'title' => 'Home AC Deep Cleaning Service',
            'slug' => 'home-ac-deep-cleaning',
            'description' => 'Doorstep service with warranty.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 99900,
            'gst_rate' => 18,
            'total_stock' => 99,
            'status' => 'active',
        ]);

        $resService = $this->get('/services/' . $serviceItem->slug);
        $this->assertNotEquals(500, $resService->getStatusCode());
        $this->assertTrue(in_array($resService->getStatusCode(), [200, 302]));

        // 4. Storefront
        $resStore = $this->get('/s/' . $this->vendor->slug);
        $this->assertNotEquals(500, $resStore->getStatusCode());
        $this->assertTrue(in_array($resStore->getStatusCode(), [200, 302]));

        // 5. Food Restaurant Menu Page
        $foodVendorUser = User::factory()->create();
        $foodVendor = Vendor::create([
            'user_id' => $foodVendorUser->id,
            'shop_name' => 'Jaipur Kitchen Express',
            'slug' => 'jaipur-kitchen-express',
            'status' => 'approved',
            'vendor_type' => 'food',
            'phone' => '9829012345',
            'email' => 'kitchen@jaipur.test',
            'address' => 'MI Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ]);

        $dish = Product::create([
            'vendor_id' => $foodVendor->id,
            'category_id' => $this->category->id,
            'title' => 'Special Paneer Butter Masala',
            'slug' => 'paneer-butter-masala',
            'description' => 'Rich creamy gravy with fresh cottage cheese.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 32000,
            'gst_rate' => 5,
            'total_stock' => 50,
            'status' => 'active',
        ]);

        $resFoodRestaurant = $this->get('/food/restaurant/' . $foodVendor->slug);
        $this->assertNotEquals(500, $resFoodRestaurant->getStatusCode());
        $this->assertTrue(in_array($resFoodRestaurant->getStatusCode(), [200, 302]));
    }

    public function test_vendor_and_admin_authenticated_dashboards_return_ok(): void
    {
        // Vendor Dashboard
        $vendorRes = $this->actingAs($this->vendorUser)->get('/vendor/dashboard');
        $this->assertNotEquals(500, $vendorRes->getStatusCode());
        $this->assertTrue(in_array($vendorRes->getStatusCode(), [200, 302]));

        // Admin Dashboard
        $adminRes = $this->actingAs($this->adminUser)->get('/admin/dashboard');
        $this->assertNotEquals(500, $adminRes->getStatusCode());
        $this->assertTrue(in_array($adminRes->getStatusCode(), [200, 302]));

        // Admin Homepage Studio
        $adminHomeRes = $this->actingAs($this->adminUser)->get('/admin/homepage');
        $this->assertNotEquals(500, $adminHomeRes->getStatusCode());
        $this->assertTrue(in_array($adminHomeRes->getStatusCode(), [200, 302]));

        // Admin Orders
        $adminOrdersRes = $this->actingAs($this->adminUser)->get('/admin/orders');
        $this->assertNotEquals(500, $adminOrdersRes->getStatusCode());
        $this->assertTrue(in_array($adminOrdersRes->getStatusCode(), [200, 302]));

        // Admin Products
        $adminProdRes = $this->actingAs($this->adminUser)->get('/admin/products');
        $this->assertNotEquals(500, $adminProdRes->getStatusCode());
        $this->assertTrue(in_array($adminProdRes->getStatusCode(), [200, 302]));

        // Admin Vendors
        $adminVendorsRes = $this->actingAs($this->adminUser)->get('/admin/vendors');
        $this->assertNotEquals(500, $adminVendorsRes->getStatusCode());
        $this->assertTrue(in_array($adminVendorsRes->getStatusCode(), [200, 302]));
    }
}
