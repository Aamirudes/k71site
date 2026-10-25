<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsedItemsAndOffersTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendorUser;
    private Vendor $vendor;
    private Category $usedCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();

        $this->vendorUser = User::factory()->create();
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Ajmer Tech Exchange',
            'slug' => 'ajmer-tech-exchange',
            'status' => 'approved',
            'phone' => '9829012345',
            'email' => 'tech@exchange.test',
            'address' => '44 Kutchery Road',
            'city' => 'Ajmer',
            'state' => 'Rajasthan',
            'pincode' => '305001',
            'pickup_address' => [
                'city' => 'Ajmer',
                'state' => 'Rajasthan',
                'pincode' => '305001',
            ],
        ]);

        VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => false,
        ]);

        $this->usedCategory = Category::create([
            'name' => 'Mobiles & Tablets',
            'slug' => 'used-mobiles',
            'vertical' => 'used',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_used_hub_loads_and_displays_listings_and_condition_filters(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Apple iPhone 14 Pro 128GB Deep Purple',
            'slug' => 'iphone-14-pro-deep-purple',
            'description' => 'Original box and bill included. 92% battery health.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 6400000, // ₹64,000
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
            'is_negotiable' => true,
            'custom_fields' => [
                'condition' => 'Like New',
                'age_months' => 10,
                'warranty_status' => 'AppleCare+ Active',
                'bill_available' => 'Yes',
            ],
        ]);

        $response = $this->get('/used');

        $response->assertStatus(200);
        $response->assertSee('used.k71.in');
        $response->assertSee('Apple iPhone 14 Pro');
        $response->assertSee('Like New');
    }

    public function test_condition_filter_filters_items(): void
    {
        $likeNew = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'MacBook Air M2 8GB 256GB Midnight',
            'slug' => 'macbook-air-m2-midnight',
            'description' => 'Used rarely for college work, spotless body.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 7200000,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
            'custom_fields' => [
                'condition' => 'Like New',
            ],
        ]);

        $heavilyUsed = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Old HP Pavilion Laptop Core i5',
            'slug' => 'old-hp-pavilion-laptop',
            'description' => 'Minor screen scratch and battery backup is 1 hr.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 1200000,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
            'custom_fields' => [
                'condition' => 'Heavily Used',
            ],
        ]);

        $response = $this->get('/used?condition=Like+New');

        $response->assertStatus(200);
        $response->assertSee('MacBook Air M2');
        $response->assertDontSee('Old HP Pavilion Laptop');
    }

    public function test_buyer_can_submit_offer_on_used_item(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Sony PlayStation 5 Disc Edition',
            'slug' => 'sony-ps5-disc-edition',
            'description' => 'Comes with 2 DualSense controllers.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 3800000, // ₹38,000
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
            'is_negotiable' => true,
        ]);

        $this->actingAs($this->buyer);

        $response = $this->postJson(route('offers.store', $product), [
            'buyer_name' => 'Vikram Rathore',
            'buyer_phone' => '9829123456',
            'offer_amount' => 34000, // Buyer offers ₹34,000
            'message' => 'I can pick it up today with cash.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $offer = Offer::where('product_id', $product->id)->first();
        $this->assertNotNull($offer);
        $this->assertEquals(3400000, $offer->offer_amount); // 34,000 * 100 paise
        $this->assertEquals('Vikram Rathore', $offer->buyer_name);
        $this->assertEquals('pending', $offer->status);
    }

    public function test_vendor_can_view_offers_in_inbox(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Canon EOS 200D DSLR Camera',
            'slug' => 'canon-eos-200d-dslr',
            'description' => 'Mint condition with 18-55mm lens.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 2800000,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'buyer_name' => 'Rahul Sharma',
            'buyer_phone' => '9876501234',
            'offer_amount' => 2500000, // ₹25,000
            'message' => 'Looking for photography practice.',
            'status' => 'pending',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->get(route('vendor.offers.index'));

        $response->assertStatus(200);
        $response->assertSee('Canon EOS 200D DSLR Camera');
        $response->assertSee('Rahul Sharma');
        $response->assertSee('₹25,000.00');
    }

    public function test_vendor_can_accept_offer(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Yamaha Acoustic Guitar F310',
            'slug' => 'yamaha-f310-guitar',
            'description' => 'Great tone, new strings installed.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 750000,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'buyer_name' => 'Ankit Joshi',
            'buyer_phone' => '9829000111',
            'offer_amount' => 650000,
            'status' => 'pending',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->put(route('vendor.offers.respond', $offer), [
            'action' => 'accept',
        ]);

        $response->assertRedirect();
        $this->assertEquals('accepted', $offer->fresh()->status);
        $this->assertNotNull($offer->fresh()->responded_at);
    }

    public function test_vendor_can_counter_offer(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'OnePlus 11 5G 16GB 256GB',
            'slug' => 'oneplus-11-5g-green',
            'description' => 'Flawless condition with 100W SuperVOOC charger.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 3600000,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'buyer_name' => 'Mehul Jain',
            'buyer_phone' => '9829222333',
            'offer_amount' => 3000000, // ₹30,000
            'status' => 'pending',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->put(route('vendor.offers.respond', $offer), [
            'action' => 'counter',
            'counter_amount' => 33500, // Vendor counters with ₹33,500
            'vendor_notes' => 'I can do ₹33,500 final since charger is brand new.',
        ]);

        $response->assertRedirect();
        $this->assertEquals('countered', $offer->fresh()->status);
        $this->assertEquals(3350000, $offer->fresh()->counter_amount);
    }

    public function test_vendor_can_mark_item_as_sold(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Royal Enfield Classic 350 Gunmetal Grey',
            'slug' => 're-classic-350-gunmetal',
            'description' => 'Single owner, 2021 model, 14,000 km run.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 14500000, // ₹1,45,000
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
            'is_sold' => false,
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->post(route('vendor.products.mark_sold', $product));

        $response->assertRedirect();
        $this->assertTrue($product->fresh()->isSold());
        $this->assertEquals(0, $product->fresh()->total_stock);
    }

    public function test_sold_item_rejects_new_offers(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Dell UltraSharp 27 Inch 4K Monitor',
            'slug' => 'dell-ultrasharp-27-4k',
            'description' => 'Color accurate IPS panel.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 2400000,
            'gst_rate' => 18,
            'total_stock' => 0,
            'status' => 'active',
            'is_sold' => true, // Already sold
        ]);

        $this->actingAs($this->buyer);

        $response = $this->postJson(route('offers.store', $product), [
            'buyer_name' => 'Late Buyer',
            'buyer_phone' => '9999999999',
            'offer_amount' => 22000,
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('already been marked as sold', $response->json('message'));
    }

    public function test_unauthorized_vendor_cannot_respond_to_another_vendors_offer(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'iPad Air 5th Gen M1',
            'slug' => 'ipad-air-5-m1',
            'description' => 'Space Grey 64GB WiFi.',
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 3800000,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
        ]);

        $offer = Offer::create([
            'product_id' => $product->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'buyer_name' => 'Buyer',
            'buyer_phone' => '9829000000',
            'offer_amount' => 3500000,
            'status' => 'pending',
        ]);

        // Second vendor attempts to respond to first vendor's offer
        $vendorUser2 = User::factory()->create();
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $vendorUser2->roles()->syncWithoutDetaching([$vendorRole->id]);

        $vendor2 = Vendor::create([
            'user_id' => $vendorUser2->id,
            'shop_name' => 'Other Store',
            'slug' => 'other-store',
            'status' => 'approved',
            'phone' => '9111111111',
            'email' => 'other@store.test',
            'address' => 'Station Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ]);

        $this->actingAs($vendorUser2);

        $response = $this->put(route('vendor.offers.respond', $offer), [
            'action' => 'accept',
        ]);

        $response->assertStatus(403);
    }

    public function test_used_listings_are_excluded_from_homepage_and_catalog(): void
    {
        $retailProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Brand New Wireless Mechanical Keyboard',
            'slug' => 'brand-new-wireless-keyboard',
            'description' => 'Original box sealed retail piece.',
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'base_price' => 450000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
            'is_featured' => true,
        ]);

        $usedProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Second Hand iPhone 12 Mini Blue',
            'slug' => 'second-hand-iphone-12-mini-blue',
            'description' => 'Used for 1 year with bill and charger.',
            'vertical' => 'used',
            'listing_type' => 'classified',
            'base_price' => 2200000,
            'gst_rate' => 0,
            'total_stock' => 1,
            'status' => 'active',
            'is_featured' => true,
        ]);

        // 1. Check Homepage - Unified 6-vertical homepage features used items in dedicated preview section
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Brand New Wireless Mechanical Keyboard');
        $homeResponse->assertSee('Second Hand iPhone 12 Mini Blue');

        // 2. Check Catalog - Retail catalog strictly excludes used items
        $catalogResponse = $this->get('/catalog');
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('Brand New Wireless Mechanical Keyboard');
        $catalogResponse->assertDontSee('Second Hand iPhone 12 Mini Blue');
    }

    public function test_accessing_used_listing_via_products_slug_redirects_to_used_show(): void
    {
        $usedProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->usedCategory->id,
            'title' => 'Pre-Owned Sony WH-1000XM4 Headphones',
            'slug' => 'pre-owned-sony-wh-1000xm4',
            'description' => 'Mint condition active noise cancelling headphones.',
            'vertical' => 'used',
            'listing_type' => 'classified',
            'base_price' => 1400000,
            'gst_rate' => 0,
            'total_stock' => 1,
            'status' => 'active',
        ]);

        // Access via regular e-commerce product view
        $response = $this->get('/product/' . $usedProduct->slug);
        $response->assertRedirect(route('used.show', $usedProduct->slug));

        // Follow redirect to dedicated second-hand view
        $followedResponse = $this->followRedirects($response);
        $followedResponse->assertStatus(200);
        $followedResponse->assertSee('Pre-Owned Sony WH-1000XM4 Headphones');
        $followedResponse->assertSee('Make Offer');

        // Also verify plural alias /products/{slug}
        $pluralResponse = $this->get('/products/' . $usedProduct->slug);
        $pluralFollowed = $this->followRedirects($pluralResponse);
        $pluralFollowed->assertStatus(200);
        $pluralFollowed->assertSee('Pre-Owned Sony WH-1000XM4 Headphones');
    }
}
