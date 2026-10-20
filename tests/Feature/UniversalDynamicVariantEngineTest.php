<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\CategoryAttribute;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ProductModifier;
use App\Models\ProductVariant;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalDynamicVariantEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Vendor $vendor;
    protected Category $fashionCategory;
    protected Category $foodCategory;
    protected Category $gigCategory;
    protected Category $b2bCategory;
    protected Category $usedCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->vendor = Vendor::create([
            'user_id' => $this->user->id,
            'shop_name' => 'K71 Elite Universal Store',
            'slug' => 'k71-elite-store',
            'phone' => '9876543210',
            'email' => 'elite@k71.in',
            'is_verified' => true,
            'status' => 'approved',
        ]);

        \App\Models\VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'cod_enabled' => true,
            'upi_enabled' => true,
            'food_delivery_fee' => 4000,
        ]);

        \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
            ['key' => 'cod_marketplace_enabled'],
            ['value' => '1']
        );

        $this->fashionCategory = Category::create([
            'name' => 'Designer Apparel',
            'slug' => 'designer-apparel',
            'vertical_type' => 'fashion_matrix',
            'is_active' => true,
        ]);

        $this->foodCategory = Category::create([
            'name' => 'Fine Dining & Takeaway',
            'slug' => 'fine-dining',
            'vertical_type' => 'food_portion',
            'is_active' => true,
        ]);

        $this->gigCategory = Category::create([
            'name' => 'Brand & Logo Design',
            'slug' => 'brand-logo-design',
            'vertical_type' => 'digital_gig',
            'is_active' => true,
        ]);

        $this->b2bCategory = Category::create([
            'name' => 'Industrial Textile Looms',
            'slug' => 'industrial-machinery',
            'vertical_type' => 'b2b_machinery',
            'is_active' => true,
        ]);

        $this->usedCategory = Category::create([
            'name' => 'Certified Electronics',
            'slug' => 'certified-electronics',
            'vertical_type' => 'classified_asset',
            'is_active' => true,
        ]);
    }

    protected function createProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->fashionCategory->id,
            'title' => 'Test Product',
            'description' => 'Test description for item.',
            'slug' => 'test-product-' . uniqid(),
            'base_price' => 100000,
            'total_stock' => 50,
            'gst_rate' => 18,
            'listing_type' => 'purchase',
            'status' => 'active',
        ], $attributes));
    }

    public function test_category_attributes_api_returns_blueprint_for_vendor(): void
    {
        CategoryAttribute::create([
            'category_id' => $this->fashionCategory->id,
            'attribute_code' => 'fabric_gsm',
            'label' => 'Fabric Weight (GSM)',
            'input_type' => 'number',
            'is_variant_driver' => false,
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->user);
        $response = $this->getJson('/api/vendor/category-attributes?category_id=' . $this->fashionCategory->id);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'vertical_type' => 'fashion_matrix',
        ]);
        $response->assertJsonCount(1, 'attributes');
        $response->assertJsonFragment([
            'attribute_code' => 'fabric_gsm',
            'label' => 'Fabric Weight (GSM)',
        ]);
    }

    public function test_cart_adds_fashion_matrix_variant_with_server_price(): void
    {
        $product = $this->createProduct([
            'category_id' => $this->fashionCategory->id,
            'title' => 'Silk Embroidered Sherwani',
            'slug' => 'silk-embroidered-sherwani',
            'base_price' => 2500000, // ₹25,000
            'total_stock' => 10,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'SHERWANI-RED-XL',
            'price_override' => 2800000, // ₹28,000 in paise
            'price' => 28000.00,
            'stock' => 5,
            'attributes_hash' => ['color' => 'Crimson Red', 'size' => 'XL'],
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->actingAs($this->user);
        $response = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Added to cart.']);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertNotNull($cart);
        $this->assertEquals(1, $cart->items()->count());

        $item = $cart->items()->first();
        $this->assertEquals($variant->id, $item->variant_id);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(2800000, $item->price_at_add);
    }

    public function test_cart_adds_food_portions_with_modifier_price_deltas(): void
    {
        $product = $this->createProduct([
            'category_id' => $this->foodCategory->id,
            'title' => 'Woodfired Truffle Pizza',
            'slug' => 'woodfired-truffle-pizza',
            'base_price' => 45000, // ₹450
            'total_stock' => 50,
            'gst_rate' => 5,
        ]);

        $portionLarge = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'Large 12-inch',
            'price_override' => 65000, // ₹650 in paise
            'price' => 650.00,
            'stock' => 20,
            'attributes_hash' => ['portion' => 'Large 12-inch'],
            'is_active' => true,
            'status' => 'active',
        ]);

        $modCheese = ProductModifier::create([
            'product_id' => $product->id,
            'group_name' => 'Cheese Upgrades',
            'modifier_name' => 'Imported Burrata',
            'price_delta' => 120.00, // +₹120
            'is_required' => false,
        ]);

        $modDip = ProductModifier::create([
            'product_id' => $product->id,
            'group_name' => 'Dips',
            'modifier_name' => 'Garlic Herb Truffle Butter',
            'price_delta' => 50.00, // +₹50
            'is_required' => false,
        ]);

        $this->actingAs($this->user);
        $response = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $portionLarge->id,
            'quantity' => 1,
            'selected_modifiers' => [$modCheese->id, $modDip->id],
        ]);

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $item = $cart->items()->first();

        // Base 65000 paise + 12000 paise + 5000 paise = 82000 paise (₹820.00)
        $this->assertEquals(82000, $item->price_at_add);
        $this->assertCount(2, $item->selected_modifiers_json);
        $this->assertEquals('Imported Burrata', $item->selected_modifiers_json[0]['name']);
        $this->assertEquals('Garlic Herb Truffle Butter', $item->selected_modifiers_json[1]['name']);
    }

    public function test_cart_adds_digital_gig_milestone_package(): void
    {
        $product = $this->createProduct([
            'category_id' => $this->gigCategory->id,
            'title' => 'Luxury Brand Identity & Design System',
            'slug' => 'luxury-brand-identity',
            'base_price' => 500000, // ₹5,000 base
            'total_stock' => 999,
            'gst_rate' => 18,
        ]);

        // Default variant
        $defaultVar = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'Default',
            'price_override' => 500000,
            'price' => 5000.00,
            'stock' => 999,
            'is_active' => true,
            'status' => 'active',
        ]);

        $standardPackage = ProductGigPackage::create([
            'product_id' => $product->id,
            'tier' => 'standard',
            'title' => 'Complete Commercial Identity',
            'description' => '3 Logo concepts, stationery kit, and 3D mockup',
            'delivery_days' => 5,
            'revisions' => 5,
            'price' => 12500.00, // ₹12,500
            'features_json' => ['source_file' => true, 'commercial_rights' => true],
        ]);

        $this->actingAs($this->user);
        $response = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'gig_package_id' => $standardPackage->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $item = $cart->items()->first();

        $this->assertEquals($standardPackage->id, $item->gig_package_id);
        $this->assertEquals(1250000, $item->price_at_add); // ₹12,500 in paise
    }

    public function test_cart_applies_b2b_tiered_moq_volume_pricing(): void
    {
        $product = $this->createProduct([
            'category_id' => $this->b2bCategory->id,
            'title' => 'High-Speed Rapier Weaving Loom',
            'slug' => 'rapier-weaving-loom',
            'base_price' => 5000000, // Single sample ₹50,000 in paise
            'total_stock' => 100,
            'gst_rate' => 18,
            'b2b_tiers_json' => [
                ['min_qty' => 1, 'max_qty' => 4, 'price' => 500.00],
                ['min_qty' => 5, 'max_qty' => 19, 'price' => 420.00],
                ['min_qty' => 20, 'max_qty' => null, 'price' => 350.00],
            ],
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'LOOM-STD',
            'price_override' => 50000,
            'price' => 500.00,
            'stock' => 100,
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->actingAs($this->user);

        // Ordering 10 units triggers Tier 2 (₹420.00 = 42000 paise)
        $response = $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(200);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $item = $cart->items()->first();
        $this->assertEquals(42000, $item->price_at_add);
    }

    public function test_commercial_rfq_submission(): void
    {
        $product = $this->createProduct([
            'category_id' => $this->b2bCategory->id,
            'title' => 'Automatic CNC Marble Cutter',
            'slug' => 'cnc-marble-cutter',
            'base_price' => 35000000,
            'total_stock' => 5,
            'listing_type' => 'enquiry',
        ]);

        $this->actingAs($this->user);
        $response = $this->postJson(route('rfq.submit', $product->id), [
            'quantity' => '15 Units',
            'destination' => 'Mundra Port / 370421',
            'company_name' => 'Royal Granites & Marbles Ltd.',
            'phone' => '9829012345',
            'requirements' => 'Require 415V 3-phase power transformer and containerized sea freight quote.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['enquiry_number', 'enquiry_id']);

        $this->assertDatabaseHas('enquiries', [
            'vendor_id' => $this->vendor->id,
            'product_id' => $product->id,
            'buyer_name' => 'Royal Granites & Marbles Ltd.',
            'buyer_phone' => '9829012345',
        ]);
    }

    public function test_classifieds_make_offer_submission(): void
    {
        $product = $this->createProduct([
            'category_id' => $this->usedCategory->id,
            'title' => 'Apple MacBook Pro M3 Max (36GB / 1TB)',
            'slug' => 'macbook-pro-m3-max',
            'base_price' => 24000000, // ₹2,40,000
            'total_stock' => 1,
            'listing_type' => 'contact_seller',
            'is_negotiable' => true,
            'is_sold' => false,
        ]);

        $this->actingAs($this->user);
        $response = $this->postJson(route('classifieds.offer', $product->id), [
            'buyer_name' => 'Rahul Sharma',
            'buyer_phone' => '9988776655',
            'buyer_email' => 'rahul@example.com',
            'offer_amount' => 215000, // ₹2,15,000 offer
            'message' => 'Ready for instant UPI transfer upon inspection in Jaipur.',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('offers', [
            'product_id' => $product->id,
            'vendor_id' => $this->vendor->id,
            'buyer_name' => 'Rahul Sharma',
            'offer_amount' => 21500000, // in paise
            'status' => 'pending',
        ]);
    }

    public function test_checkout_freezes_item_meta_and_modifiers_into_order(): void
    {
        $product = $this->createProduct([
            'category_id' => $this->foodCategory->id,
            'title' => 'Signature Tandoori Feast',
            'slug' => 'tandoori-feast',
            'base_price' => 80000, // ₹800
            'total_stock' => 10,
            'gst_rate' => 5,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'Full Platter',
            'price_override' => 80000,
            'price' => 800.00,
            'stock' => 10,
            'is_active' => true,
            'status' => 'active',
        ]);

        $modifier = ProductModifier::create([
            'product_id' => $product->id,
            'group_name' => 'Breads',
            'modifier_name' => 'Butter Garlic Naan (2 pcs)',
            'price_delta' => 90.00,
            'is_required' => false,
        ]);

        $this->actingAs($this->user);

        // Add to cart
        $this->postJson('/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'selected_modifiers' => [$modifier->id],
        ]);

        // Process checkout
        $checkoutResponse = $this->post(route('checkout.process', $this->vendor->id), [
            'address_line_1' => 'Plot 42, Civil Lines',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302006',
            'phone' => '9876543210',
            'payment_method' => 'cod',
            'order_type' => 'food_delivery',
        ]);

        $checkoutResponse->assertRedirect();

        $order = Order::where('vendor_id', $this->vendor->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(1, $order->items()->count());

        $orderItem = $order->items()->first();
        $this->assertEquals(89000, $orderItem->unit_price); // 800 + 90 = 890 * 100
        $this->assertNotNull($orderItem->selected_modifiers_json);
        $this->assertEquals('Butter Garlic Naan (2 pcs)', $orderItem->selected_modifiers_json[0]['name']);
        $this->assertNotNull($orderItem->meta_json);
        $this->assertEquals('food_portion', $orderItem->meta_json['vertical']);
    }
}
