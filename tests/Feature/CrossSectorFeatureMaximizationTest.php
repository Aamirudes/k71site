<?php

namespace Tests\Feature;

use App\Http\Controllers\SitemapController;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CrossSectorFeatureMaximizationTest extends TestCase
{
    use RefreshDatabase;

    private function makeVendor(string $name, bool $subscribed = true, string $sector = 'products'): Vendor
    {
        $user = User::factory()->create([
            'email' => Str::slug($name) . '-' . uniqid() . '@example.com',
        ]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $name,
            'slug' => Str::slug($name) . '-' . uniqid(),
            'contact_email' => 'contact@' . uniqid() . '.com',
            'contact_phone' => '9876543210',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'status' => 'approved',
            'verification_status' => 'verified',
            'vendor_type' => $sector === 'services' ? 'services' : ($sector === 'food' ? 'food' : 'clothing'),
            'website_status' => 'published',
        ]);

        if ($subscribed) {
            $this->grantVendorTestSubscription($vendor, $sector);
        }

        return $vendor;
    }

    public function test_sitemaps_and_merchant_feed_hide_unsubscribed_vendors_and_route_sectors_accurately(): void
    {
        SitemapController::clearCache();

        $visibleVendor = $this->makeVendor('Visible Studio', true, 'all');
        $hiddenVendor = $this->makeVendor('Unsubscribed Studio', false, 'products');
        $cat = Category::create(['name' => 'Curated Goods', 'slug' => 'curated-goods-' . uniqid(), 'is_active' => true]);

        $visibleProd = Product::create([
            'vendor_id' => $visibleVendor->id,
            'category_id' => $cat->id,
            'title' => 'Marble Lotus Tray',
            'slug' => 'marble-lotus-tray-' . uniqid(),
            'description' => 'Hand carved stone tray',
            'base_price' => 149900,
            'gst_rate' => 12,
            'total_stock' => 10,
            'status' => 'active',
            'vertical' => 'marketplace',
        ]);

        $serviceProd = Product::create([
            'vendor_id' => $visibleVendor->id,
            'category_id' => $cat->id,
            'title' => 'Interior Architecture Audit',
            'slug' => 'interior-audit-' . uniqid(),
            'description' => 'Full architectural plan review',
            'base_price' => 499900,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
            'vertical' => 'services',
            'listing_type' => 'booking',
        ]);

        $hiddenProd = Product::create([
            'vendor_id' => $hiddenVendor->id,
            'category_id' => $cat->id,
            'title' => 'Secret Unpaid Listing',
            'slug' => 'secret-unpaid-' . uniqid(),
            'description' => 'Should never appear in sitemaps',
            'base_price' => 99900,
            'gst_rate' => 5,
            'total_stock' => 10,
            'status' => 'active',
            'vertical' => 'marketplace',
        ]);

        $productsXml = $this->get('/sitemap-products.xml');
        $productsXml->assertStatus(200);
        $productsXml->assertSee('/product/' . $visibleProd->slug);
        $productsXml->assertSee('/services/' . $serviceProd->slug);
        $productsXml->assertDontSee($hiddenProd->slug);

        $storesXml = $this->get('/sitemap-stores.xml');
        $storesXml->assertStatus(200);
        $storesXml->assertSee($visibleVendor->slug);
        $storesXml->assertDontSee($hiddenVendor->slug);

        $feedXml = $this->get('/feeds/google-merchant.xml');
        $feedXml->assertStatus(200);
        $feedXml->assertSee('Marble Lotus Tray');
        $feedXml->assertDontSee('Secret Unpaid Listing');
    }

    public function test_catalog_swapped_price_range_and_noindex_robots_work(): void
    {
        $vendor = $this->makeVendor('Catalog Accuracy Store', true, 'all');
        $cat = Category::create(['name' => 'Ethnic', 'slug' => 'ethnic-' . uniqid(), 'is_active' => true]);

        Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Cotton Kurta Set',
            'slug' => 'cotton-kurta-' . uniqid(),
            'description' => 'Breathable summer weave',
            'base_price' => 180000, // ₹1,800
            'gst_rate' => 5,
            'total_stock' => 8,
            'status' => 'active',
        ]);

        // Swapped min_price=3000 & max_price=1000 still finds ₹1,800 item and sets noindex, follow
        $filteredResp = $this->get('/catalog?min_price=3000&max_price=1000');
        $filteredResp->assertStatus(200);
        $filteredResp->assertSee('Cotton Kurta Set');
        $filteredResp->assertSee('<meta name="robots" content="noindex, follow">', false);

        // Vendor dashboard emits noindex, nofollow
        $vendorResp = $this->actingAs($vendor->user)->get('/vendor/products');
        $vendorResp->assertStatus(200);
        $vendorResp->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_unified_multi_sector_search_suggest_returns_stores_and_all_verticals(): void
    {
        $vendor = $this->makeVendor('Obsidian Atelier', true, 'all');
        $cat = Category::create(['name' => 'Studio Gear', 'slug' => 'studio-gear-' . uniqid(), 'is_active' => true]);

        Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Obsidian Lens Kit',
            'slug' => 'obsidian-lens-' . uniqid(),
            'description' => 'Cinema optics',
            'base_price' => 250000,
            'gst_rate' => 18,
            'total_stock' => 5,
            'status' => 'active',
            'vertical' => 'marketplace',
        ]);

        $svc = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Obsidian Retouching Service',
            'slug' => 'obsidian-retouch-' . uniqid(),
            'description' => 'Pro color grading',
            'base_price' => 120000,
            'gst_rate' => 18,
            'total_stock' => 5,
            'status' => 'active',
            'vertical' => 'services',
            'listing_type' => 'booking',
        ]);

        $resp = $this->getJson('/api/search/suggest?q=Obsidian');
        $resp->assertStatus(200);
        $resp->assertJsonStructure(['results', 'stores', 'listings']);
        $resp->assertJsonFragment(['type' => 'store', 'title' => 'Obsidian Atelier']);
        $resp->assertJsonFragment(['title' => 'Obsidian Retouching Service', 'url' => route('services.show', $svc->slug)]);
    }

    public function test_vendor_product_bulk_action_enforces_quota_and_csv_exports_work(): void
    {
        $vendor = $this->makeVendor('Bulk Ops Store', false, 'products');
        $this->grantVendorTestSubscription($vendor, 'products', ['max_active_products' => 1]);

        $cat = Category::create(['name' => 'Decor', 'slug' => 'decor-' . uniqid(), 'is_active' => true]);

        $p1 = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => '=FormulaAttack Vase',
            'slug' => 'formula-vase-' . uniqid(),
            'description' => 'Draft 1',
            'base_price' => 50000,
            'gst_rate' => 5,
            'total_stock' => 3,
            'status' => 'draft',
        ]);

        $p2 = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Second Draft Bowl',
            'slug' => 'second-bowl-' . uniqid(),
            'description' => 'Draft 2',
            'base_price' => 70000,
            'gst_rate' => 5,
            'total_stock' => 10,
            'status' => 'draft',
        ]);

        // Bulk activate respects max_active_products=1 (1 activates, 1 skipped)
        $bulkResp = $this->actingAs($vendor->user)->post('/vendor/products/bulk-action', [
            'action' => 'activate',
            'product_ids' => [$p1->id, $p2->id],
        ]);
        $bulkResp->assertRedirect();
        $this->assertEquals(1, Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('status', 'active')->count());

        // CSV Export escapes leading '=' formula character
        $exportResp = $this->actingAs($vendor->user)->get('/vendor/products/export');
        $exportResp->assertStatus(200);
        $csv = $exportResp->streamedContent();
        $this->assertStringContainsString("'=FormulaAttack Vase", $csv);

        // Vendor Orders CSV export respects filters
        $buyer = User::factory()->create(['first_name' => 'Riya']);
        Order::create([
            'order_number' => 'ORD-EXP-1001',
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'address_snapshot' => ['name' => 'Riya', 'phone' => '9999999999'],
            'subtotal' => 50000,
            'shipping_amount' => 0,
            'tax_amount' => 2500,
            'total_amount' => 50000,
            'item_count' => 1,
            'status' => 'delivered',
            'payment_method' => 'upi',
            'payment_status' => 'paid',
        ]);

        $ordersCsv = $this->actingAs($vendor->user)->get('/vendor/orders/export?status=delivered');
        $ordersCsv->assertStatus(200);
        $this->assertStringContainsString('ORD-EXP-1001', $ordersCsv->streamedContent());
    }

    public function test_checkout_index_clears_exhausted_per_customer_coupon(): void
    {
        $vendor = $this->makeVendor('Coupon Sync Store', true, 'all');
        $cat = Category::create(['name' => 'Shirts', 'slug' => 'shirts-' . uniqid(), 'is_active' => true]);
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Oxford Shirt',
            'slug' => 'oxford-shirt-' . uniqid(),
            'description' => 'Classic weave',
            'base_price' => 200000,
            'gst_rate' => 5,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $buyer = User::factory()->create();
        $cart = Cart::create(['user_id' => $buyer->id]);
        $cart->items()->create([
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price_at_add' => 200000,
        ]);

        $coupon = Coupon::create([
            'vendor_id' => $vendor->id,
            'code' => 'ONCEONLY',
            'type' => 'flat',
            'value' => 50000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
            'per_customer_limit' => 1,
            'is_active' => true,
        ]);

        $prevOrder = Order::create([
            'order_number' => 'ORD-PREV-1',
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'address_snapshot' => ['name' => 'Buyer', 'pincode' => '400001'],
            'subtotal' => 200000,
            'shipping_amount' => 0,
            'tax_amount' => 10000,
            'total_amount' => 150000,
            'item_count' => 1,
            'status' => 'delivered',
            'payment_method' => 'upi',
            'payment_status' => 'paid',
        ]);

        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $buyer->id,
            'order_id' => $prevOrder->id,
            'discount_amount' => 50000,
        ]);

        $resp = $this->actingAs($buyer)
            ->withSession(["applied_coupon_{$vendor->id}" => 'ONCEONLY'])
            ->get("/checkout/{$vendor->id}");

        $resp->assertStatus(200);
        $this->assertEquals(0, $resp->viewData('discount'));
        $this->assertNull($resp->viewData('appliedCoupon'));
    }
}
