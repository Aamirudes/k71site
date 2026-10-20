<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Order;
use App\Models\Bid;
use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscoverySeoPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Event::fake([
            \App\Events\AuditLogged::class,
        ]);
    }

    private function createVendor(string $name = 'Artisan Store', string $city = 'Mumbai'): Vendor
    {
        $user = User::factory()->create([
            'email' => strtolower(str_replace(' ', '', $name)) . '_' . uniqid() . '@example.com',
            'first_name' => 'Vendor',
            'last_name' => 'Owner',
        ]);

        return Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'description' => 'A curated maker studio in ' . $city,
            'contact_email' => 'contact@' . uniqid() . '.com',
            'contact_phone' => '9876543210',
            'city' => $city,
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => 'approved',
            'approved_at' => now(),
        ]);
    }

    private function createCategory(string $name = 'Apparel'): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . uniqid(),
            'is_active' => true,
        ]);
    }

    private function createProduct(Vendor $vendor, Category $category, array $attributes = []): Product
    {
        $title = $attributes['title'] ?? 'Sample Product';
        return Product::create(array_merge([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . uniqid(),
            'description' => 'A wonderful handcrafted product',
            'base_price' => 99900,
            'status' => 'active',
            'gst_rate' => 5,
            'total_stock' => 10,
        ], $attributes));
    }

    public function test_catalog_search_with_query_and_vertical_filter(): void
    {
        $vendor = $this->createVendor('Chef Hub', 'Delhi');
        $cat = $this->createCategory('Specialties');

        $foodProd = $this->createProduct($vendor, $cat, [
            'title' => 'Truffle Mushroom Pizza',
            'description' => 'Wood-fired sourdough artisan pizza',
            'base_price' => 59900,
            'vertical' => 'food',
            'listing_type' => 'purchase',
        ]);

        $serviceProd = $this->createProduct($vendor, $cat, [
            'title' => 'Suit Tailoring & Alteration',
            'description' => 'Master bespoke blazer tailoring service',
            'base_price' => 120000,
            'vertical' => 'services',
            'listing_type' => 'booking',
        ]);

        // Search with query and vertical filter
        $response = $this->get('/catalog?q=Pizza&vertical=food');
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->pluck('title')->contains('Truffle Mushroom Pizza'));
        $this->assertFalse($products->pluck('title')->contains('Suit Tailoring & Alteration'));

        // Query by vertical alone
        $servicesResponse = $this->get('/catalog?vertical=services');
        $servicesResponse->assertStatus(200);
        $servicesProducts = $servicesResponse->viewData('products');
        $this->assertTrue($servicesProducts->pluck('title')->contains('Suit Tailoring & Alteration'));
        $this->assertFalse($servicesProducts->pluck('title')->contains('Truffle Mushroom Pizza'));
    }

    public function test_catalog_price_min_and_price_max_filtering(): void
    {
        $vendor = $this->createVendor('Price Test Store');
        $cat = $this->createCategory('Curated Items');

        $this->createProduct($vendor, $cat, [
            'title' => 'Budget Cotton Scarf',
            'description' => 'Lightweight scarf',
            'base_price' => 30000, // ₹300
        ]);

        $this->createProduct($vendor, $cat, [
            'title' => 'Midrange Linen Shirt',
            'description' => 'Casual linen shirt',
            'base_price' => 150000, // ₹1,500
        ]);

        $this->createProduct($vendor, $cat, [
            'title' => 'Luxury Cashmere Overcoat',
            'description' => 'Handcrafted pure cashmere coat',
            'base_price' => 1200000, // ₹12,000
        ]);

        // Filter: price_min=1000 and price_max=3000
        $response = $this->get('/catalog?price_min=1000&price_max=3000');
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->pluck('title')->contains('Midrange Linen Shirt'));
        $this->assertFalse($products->pluck('title')->contains('Budget Cotton Scarf'));
        $this->assertFalse($products->pluck('title')->contains('Luxury Cashmere Overcoat'));
    }

    public function test_catalog_city_filter(): void
    {
        $jaipurVendor = $this->createVendor('Jaipur Craft House', 'Jaipur');
        $bengaluruVendor = $this->createVendor('Silicon Atelier', 'Bengaluru');
        $cat = $this->createCategory('Textiles');

        $this->createProduct($jaipurVendor, $cat, [
            'title' => 'Sanganeri Block Printed Kurti',
            'description' => 'Authentic natural dyed block print',
            'base_price' => 189900,
        ]);

        $this->createProduct($bengaluruVendor, $cat, [
            'title' => 'Tech Minimalist Backpack',
            'description' => 'Waterproof urban commuter bag',
            'base_price' => 249900,
        ]);

        $response = $this->get('/catalog?city=Jaipur');
        $response->assertStatus(200);
        $products = $response->viewData('products');
        $this->assertTrue($products->pluck('title')->contains('Sanganeri Block Printed Kurti'));
        $this->assertFalse($products->pluck('title')->contains('Tech Minimalist Backpack'));
    }

    public function test_catalog_instant_search_suggest_api(): void
    {
        $vendor = $this->createVendor('Quick Search Vendor');
        $cat = $this->createCategory('Accessories');

        $this->createProduct($vendor, $cat, [
            'title' => 'Vintage Chronograph Watch',
            'description' => 'Classic mechanical timepiece',
            'base_price' => 450000,
            'vertical' => 'used',
        ]);

        $response = $this->getJson('/catalog/suggest?q=Chronograph');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'results' => [
                '*' => ['id', 'title', 'slug', 'url', 'price', 'vertical', 'thumb', 'vendor']
            ]
        ]);
        $response->assertJsonFragment([
            'title' => 'Vintage Chronograph Watch',
            'vertical' => 'used',
        ]);
    }

    public function test_sitemaps_and_robots_are_accessible_and_valid(): void
    {
        // 1. Sitemap index
        $sitemapResponse = $this->get('/sitemap.xml');
        $sitemapResponse->assertStatus(200);
        $this->assertStringContainsString('application/xml', $sitemapResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('sitemap-products.xml', $sitemapResponse->getContent());
        $this->assertStringContainsString('sitemap-shorts.xml', $sitemapResponse->getContent());

        // 2. Robots.txt
        $robotsResponse = $this->get('/robots.txt');
        $robotsResponse->assertStatus(200);
        $this->assertStringContainsString('text/plain', $robotsResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('/sitemap.xml', $robotsResponse->getContent());
        $this->assertStringContainsString('Allow: /shorts', $robotsResponse->getContent());
        $this->assertStringContainsString('Allow: /food', $robotsResponse->getContent());
    }

    public function test_product_pdp_includes_schema_org_json_ld(): void
    {
        $vendor = $this->createVendor('Schema Brand');
        $cat = $this->createCategory('Fine Silk');

        $prod = $this->createProduct($vendor, $cat, [
            'title' => 'Kanjivaram Zari Silk Saree',
            'description' => 'Pure mulberry silk saree with pure silver zari work',
            'base_price' => 1500000,
        ]);

        $response = $this->get('/product/' . $prod->slug);
        $response->assertStatus(200);
        $this->assertStringContainsString('https://schema.org', $response->getContent());
        $this->assertStringContainsString('application/ld+json', $response->getContent());
        $this->assertStringContainsString('Kanjivaram Zari Silk Saree', $response->getContent());
    }

    public function test_customer_dpdp_data_export(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Priya',
            'last_name' => 'Sharma',
            'email' => 'priya.dpdp@example.com',
            'phone' => '9820012345',
        ]);

        $vendor = $this->createVendor('Export Vendor');
        $cat = $this->createCategory('Home');

        $prod = $this->createProduct($vendor, $cat, [
            'title' => 'Ceramic Planter',
            'description' => 'Glazed pottery',
            'base_price' => 89900,
        ]);

        // Create sample order
        Order::create([
            'order_number' => 'ORD-' . uniqid(),
            'user_id' => $user->id,
            'vendor_id' => $vendor->id,
            'subtotal' => 89900,
            'shipping_amount' => 0,
            'tax_amount' => 4495,
            'total_amount' => 94395,
            'item_count' => 1,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'address_snapshot' => [
                'shipping' => ['name' => 'Priya', 'street' => 'MG Road', 'city' => 'Pune', 'pincode' => '411001'],
                'billing' => ['name' => 'Priya', 'street' => 'MG Road', 'city' => 'Pune', 'pincode' => '411001'],
            ],
        ]);

        $response = $this->actingAs($user)->get('/account/export');
        $response->assertStatus(200);
        $this->assertStringContainsString('k71-data-export-user-' . $user->id . '.json', (string) $response->headers->get('Content-Disposition'));

        $jsonContent = json_decode($response->streamedContent(), true);
        $this->assertIsArray($jsonContent);
        $this->assertEquals('Digital Personal Data Protection Act (DPDP), 2023 - Republic of India', $jsonContent['compliance']);
        $this->assertEquals('priya.dpdp@example.com', $jsonContent['user']['email']);
        $this->assertCount(1, $jsonContent['orders']);
    }

    public function test_customer_dpdp_account_deletion_requires_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'deletion.test@example.com',
        ]);

        $response = $this->actingAs($user)->post('/account/delete', []);
        $response->assertSessionHasErrors('confirm_delete');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_customer_dpdp_account_deletion_anonymizes_and_soft_deletes(): void
    {
        $user = User::factory()->create([
            'first_name' => 'Aarav',
            'last_name' => 'Patel',
            'email' => 'aarav.patel@example.com',
            'phone' => '9988776655',
        ]);

        $response = $this->actingAs($user)->post('/account/delete', [
            'confirm_delete' => '1',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success');

        // Confirm user is logged out
        $this->assertGuest();

        // Confirm database record has been soft-deleted and personal identifiers erased
        $user->refresh();
        $this->assertNotNull($user->deleted_at);
        $this->assertEquals('Deleted', $user->first_name);
        $this->assertEquals('User', $user->last_name);
        $this->assertNull($user->phone);
        $this->assertStringContainsString('@erased.k71.in', $user->email);
    }
}
