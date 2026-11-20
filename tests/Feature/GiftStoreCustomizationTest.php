<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GiftStoreCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    protected function createCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'custom-gifts'],
            ['name' => 'Custom Gifts & Keepsakes', 'vertical' => 'marketplace', 'is_active' => true]
        );
    }

    protected function createGiftVendor(string $slug = 'artisan-gifts'): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create([
            'email' => "{$slug}@k71test.local",
            'username' => str_replace('-', '_', $slug),
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => 'Artisan Keepsake Studio',
            'slug' => $slug,
            'vendor_type' => 'gift_store',
            'operating_sectors' => ['products'],
            'layout_preset' => 'velora',
            'status' => 'approved',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $plan = SubscriptionPlan::firstOrCreate(
            ['code' => 'test-gift-pro'],
            [
                'name' => 'Pro Gifts',
                'slug' => 'test-gift-pro',
                'sector_key' => 'products',
                'tier_key' => 'pro',
                'is_free' => true,
                'is_active' => true,
                'price_paise' => 0,
                'billing_interval' => 'monthly',
                'features' => [
                    'themes_allowed' => ['*'],
                    'max_products' => 1000,
                    'max_active_products' => 1000,
                    'custom_domain' => true,
                ],
            ]
        );

        VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'sector_key' => 'products',
            'plan_name_snapshot' => 'Pro Gifts',
            'price_amount_snapshot' => 0,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_end' => now()->addYear(),
        ]);

        \App\Models\VendorSetting::firstOrCreate(
            ['vendor_id' => $vendor->id],
            ['cod_enabled' => true, 'upi_enabled' => true]
        );

        return [$user, $vendor];
    }

    public function test_vendor_can_register_as_gift_store_type(): void
    {
        $response = $this->post(route('vendor.register'), [
            'shop_name' => 'Bespoke Wooden Gifts',
            'slug' => 'bespoke-wooden-gifts',
            'vendor_type' => 'gift_store',
            'first_name' => 'Artisan',
            'last_name' => 'Maker',
            'username' => 'bespoke_artisan',
            'email' => 'artisan@bespokegifts.test',
            'phone' => '9876543210',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect();

        $vendor = Vendor::where('slug', 'bespoke-wooden-gifts')->first();
        $this->assertNotNull($vendor);
        $this->assertEquals('gift_store', $vendor->vendor_type);
        $this->assertTrue($vendor->isGiftStore());
        $this->assertFalse($vendor->isRestaurant());
        $this->assertEquals('marketplace', $vendor->getVerticalKey());
        $this->assertTrue($vendor->isMarketplacePhysical());

        $sec = $vendor->getSectorConfig();
        $this->assertEquals('gift_store', $sec['key']);
        $this->assertEquals('Personalized Gift Orders & Production Queue', $sec['orders_name']);
    }

    public function test_gift_vendor_can_create_and_update_customizable_product(): void
    {
        [$user, $vendor] = $this->createGiftVendor();
        $category = $this->createCategory();

        $this->actingAs($user);

        // 1. Create customizable gift product
        $response = $this->post(route('vendor.products.store'), [
            'title' => 'Personalized Wooden Keepsake Box',
            'category_id' => $category->id,
            'base_price' => 999.00,
            'gst_rate' => 12,
            'total_stock' => 50,
            'status' => 'active',
            'description' => 'Solid Beechwood keepsake box with custom precision laser engraving.',
            'gift_customization' => [
                'is_customizable' => '1',
                'allow_custom_text' => '1',
                'is_text_required' => '1',
                'text_prompt' => 'Enter Recipient Name or Custom Message',
                'text_char_limit' => 35,
                'text_extra_price' => 50.00,
                'allow_font_selection' => '1',
                'available_fonts' => 'Classic Serif, Modern Script, Bold Minimal, Vintage Engraving',
                'allow_color_selection' => '1',
                'available_colors' => 'Gold Foil, Silver Shimmer, Rose Gold',
                'allow_photo_upload' => '1',
                'photo_prompt' => 'Upload Memory Photo',
                'photo_extra_price' => 99.00,
                'allow_gift_wrap' => '1',
                'gift_wrap_price' => 40.00,
                'allow_greeting_card' => '1',
                'greeting_card_price' => 25.00,
                'production_days' => 3,
            ],
        ]);

        $response->assertRedirect();

        $product = Product::where('vendor_id', $vendor->id)->firstOrFail();
        $this->assertTrue($product->isGiftCustomizable());

        $cfg = $product->getGiftCustomizationConfig();
        $this->assertTrue($cfg['is_customizable']);
        $this->assertTrue($cfg['allow_custom_text']);
        $this->assertTrue($cfg['is_text_required']);
        $this->assertEquals(35, $cfg['text_char_limit']);
        $this->assertEquals(5000, $cfg['text_price_paise']); // 50 * 100
        $this->assertEquals(9900, $cfg['photo_price_paise']); // 99 * 100
        $this->assertEquals(4000, $cfg['gift_wrap_price_paise']); // 40 * 100
        $this->assertEquals(2500, $cfg['greeting_card_price_paise']); // 25 * 100
        $this->assertEquals(3, $cfg['production_days']);

        // 2. Update the product
        $this->put(route('vendor.products.update', $product), [
            'title' => 'Personalized Wooden Keepsake Box (Deluxe Edition)',
            'category_id' => $category->id,
            'base_price' => 1199.00,
            'gst_rate' => 12,
            'total_stock' => 45,
            'status' => 'active',
            'description' => 'Updated luxury beechwood keepsake box with satin ribbon.',
            'gift_customization' => [
                'is_customizable' => '1',
                'allow_custom_text' => '1',
                'text_prompt' => 'Enter Monogram or Dedication',
                'text_char_limit' => 45,
                'text_extra_price' => 60.00,
                'allow_photo_upload' => '1',
                'photo_extra_price' => 120.00,
                'allow_gift_wrap' => '1',
                'gift_wrap_price' => 50.00,
                'allow_greeting_card' => '1',
                'greeting_card_price' => 30.00,
                'production_days' => 2,
            ],
        ])->assertRedirect();

        $updated = $product->fresh();
        $this->assertEquals('Personalized Wooden Keepsake Box (Deluxe Edition)', $updated->title);
        $updatedCfg = $updated->getGiftCustomizationConfig();
        $this->assertEquals(45, $updatedCfg['text_char_limit']);
        $this->assertEquals(6000, $updatedCfg['text_price_paise']);
        $this->assertEquals(5000, $updatedCfg['gift_wrap_price_paise']);
    }

    public function test_customer_can_add_customized_gift_item_to_cart_with_server_price_validation(): void
    {
        [$user, $vendor] = $this->createGiftVendor();
        $category = $this->createCategory();

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Custom Engraved Watch Plaque',
            'slug' => 'custom-engraved-watch-plaque',
            'description' => 'Personalized watch stand plaque with laser-engraved monogram.',
            'base_price' => 100000, // ₹1,000.00
            'total_stock' => 20,
            'status' => 'active',
            'gift_customization_config' => [
                'is_customizable' => true,
                'allow_custom_text' => true,
                'text_char_limit' => 30,
                'text_price_paise' => 7500, // ₹75.00
                'allow_font_selection' => true,
                'available_fonts' => ['Classic Serif', 'Modern Script'],
                'allow_color_selection' => true,
                'available_colors' => ['Gold Foil', 'Silver Shimmer'],
                'allow_gift_wrap' => true,
                'gift_wrap_price_paise' => 4500, // ₹45.00
                'allow_greeting_card' => true,
                'greeting_card_price_paise' => 3000, // ₹30.00
                'production_days' => 2,
            ],
        ]);

        // Customer adds customized item to cart
        $response = $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'customization' => [
                'custom_text' => 'Happy Anniversary Noah & Ava',
                'font' => 'Modern Script',
                'color' => 'Gold Foil',
                'gift_wrap' => '1',
                'greeting_card' => '1',
                'greeting_card_message' => 'To 10 wonderful years together!',
            ],
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $cart = Cart::where('session_id', session()->getId())->firstOrFail();
        $this->assertCount(1, $cart->items);

        $item = $cart->items->first();
        $this->assertEquals($product->id, $item->product_id);
        $this->assertTrue($item->hasGiftCustomization());

        // Expected price: Base (100000) + Text (7500) + Wrap (4500) + Card (3000) = 115000 paise
        $this->assertEquals(115000, $item->price_at_add);

        $custData = $item->customization_data;
        $this->assertEquals('Happy Anniversary Noah & Ava', $custData['custom_text']);
        $this->assertEquals('Modern Script', $custData['font']);
        $this->assertEquals('Gold Foil', $custData['color']);
        $this->assertTrue($custData['gift_wrap']);
        $this->assertTrue($custData['greeting_card']);
        $this->assertEquals('To 10 wonderful years together!', $custData['greeting_card_message']);
        $this->assertEquals(15000, $custData['total_customization_paise']); // 7500 + 4500 + 3000
    }

    public function test_two_gifts_with_different_personalizations_remain_separate_cart_items(): void
    {
        [$user, $vendor] = $this->createGiftVendor();
        $category = $this->createCategory();

        $customer = User::factory()->create(['email' => 'giftbuyer2@k71test.local']);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);
        $customer->roles()->syncWithoutDetaching([$customerRole->id]);
        $this->actingAs($customer);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Custom Ceramic Coffee Mug',
            'slug' => 'custom-ceramic-coffee-mug',
            'description' => 'Custom ceramic mug with recipient name.',
            'base_price' => 49900, // ₹499.00
            'total_stock' => 50,
            'status' => 'active',
            'gift_customization_config' => [
                'is_customizable' => true,
                'allow_custom_text' => true,
                'text_char_limit' => 25,
                'text_price_paise' => 5000, // ₹50.00
            ],
        ]);

        // 1. Add mug with name "Sophia"
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'customization' => [
                'custom_text' => 'Sophia',
            ],
        ])->assertStatus(200);

        // 2. Add same product with name "Alexander"
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'customization' => [
                'custom_text' => 'Alexander',
            ],
        ])->assertStatus(200);

        $cart = Cart::where('user_id', $customer->id)->firstOrFail();

        // Must remain 2 separate distinct line items in the cart!
        $this->assertCount(2, $cart->items()->get());
        $texts = $cart->items()->get()->map(fn ($i) => $i->customization_data['custom_text'])->sort()->values()->all();
        $this->assertEquals(['Alexander', 'Sophia'], $texts);

        // 3. Add same product with "Sophia" again -> this one matches and increments quantity!
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'customization' => [
                'custom_text' => 'Sophia',
            ],
        ])->assertStatus(200);

        $this->assertCount(2, $cart->items()->get()); // Still 2 line items

        $sophiaItem = $cart->items()->get()->first(fn ($i) => ($i->customization_data['custom_text'] ?? '') === 'Sophia');
        $this->assertEquals(2, $sophiaItem->quantity); // Incremented to 2
    }

    public function test_customer_photo_upload_is_stored_safely_and_included_in_order(): void
    {
        [$user, $vendor] = $this->createGiftVendor();
        $category = $this->createCategory();

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Custom Photo Canvas Frame',
            'slug' => 'custom-photo-canvas-frame',
            'description' => 'Museum-grade photo canvas with custom print.',
            'base_price' => 129900, // ₹1,299.00
            'total_stock' => 15,
            'status' => 'active',
            'gift_customization_config' => [
                'is_customizable' => true,
                'allow_photo_upload' => true,
                'photo_price_paise' => 15000, // ₹150.00
                'allow_custom_text' => true,
                'text_price_paise' => 0,
            ],
        ]);

        $fakePhoto = UploadedFile::fake()->image('wedding_portrait.jpg', 800, 600);

        $this->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'customization' => [
                'custom_text' => 'Lucas & Maya · Established 2026',
            ],
            'customization_photo' => $fakePhoto,
        ])->assertStatus(200);

        $cart = Cart::where('session_id', session()->getId())->firstOrFail();
        $item = $cart->items->first();

        $this->assertNotNull($item->customization_data);
        $this->assertNotEmpty($item->customization_data['photo_path']);
        $this->assertNotEmpty($item->customization_data['photo_url']);
        Storage::disk('public')->assertExists($item->customization_data['photo_path']);

        // Base 129900 + Photo 15000 = 144900
        $this->assertEquals(144900, $item->price_at_add);
    }

    public function test_checkout_preserves_immutable_customization_snapshot_in_order_items(): void
    {
        [$vendorUser, $vendor] = $this->createGiftVendor();
        $category = $this->createCategory();

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Personalized Leather Wallet',
            'slug' => 'personalized-leather-wallet',
            'description' => 'Top-grain leather wallet with custom hot-stamped initials.',
            'base_price' => 89900,
            'total_stock' => 30,
            'status' => 'active',
            'gift_customization_config' => [
                'is_customizable' => true,
                'allow_custom_text' => true,
                'text_price_paise' => 5000,
                'allow_font_selection' => true,
                'allow_gift_wrap' => true,
                'gift_wrap_price_paise' => 4000,
            ],
        ]);

        $customer = User::factory()->create([
            'email' => 'giftbuyer@k71test.local',
        ]);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);
        $customer->roles()->syncWithoutDetaching([$customerRole->id]);

        $this->actingAs($customer);

        // Add to cart with customization
        $this->postJson(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'customization' => [
                'custom_text' => 'H.M.',
                'font' => 'Vintage Engraving',
                'color' => 'Gold Foil',
                'gift_wrap' => '1',
            ],
        ])->assertStatus(200);

        // Perform checkout
        $response = $this->post(route('checkout.process', $vendor), [
            'name' => 'Hashir Buyer',
            'email' => 'giftbuyer@k71test.local',
            'phone' => '9876543210',
            'address_line_1' => '221B Baker St',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();

        $order = Order::where('vendor_id', $vendor->id)->latest()->firstOrFail();
        $this->assertCount(1, $order->items);

        $orderItem = $order->items->first();
        $this->assertTrue($orderItem->hasGiftCustomization());

        $custSnapshot = $orderItem->customization_data;
        $this->assertEquals('H.M.', $custSnapshot['custom_text']);
        $this->assertEquals('Vintage Engraving', $custSnapshot['font']);
        $this->assertEquals('Gold Foil', $custSnapshot['color']);
        $this->assertTrue($custSnapshot['gift_wrap']);

        // Snapshot contains variant_sku with personalized label
        $this->assertStringContainsString('Personalized: "H.M."', $orderItem->snapshot['variant_sku']);
        $this->assertStringContainsString('+ Gift Wrap', $orderItem->snapshot['variant_sku']);

        // Vendor order detail view displays customization details
        $this->actingAs($vendorUser)
            ->get(route('vendor.orders.show', $order))
            ->assertStatus(200)
            ->assertSee('Production Personalization Specs')
            ->assertSee('H.M.')
            ->assertSee('Vintage Engraving')
            ->assertSee('Gold Foil')
            ->assertSee('Luxury Gift Wrap Required');

        // Customer order tracking view displays personalization details
        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertStatus(200)
            ->assertSee('Personalized Gift')
            ->assertSee('H.M.')
            ->assertSee('Vintage Engraving');
    }

    public function test_strict_regression_guard_standard_products_and_other_vendors_are_unaffected(): void
    {
        $apparelUser = User::factory()->create(['email' => 'apparel@k71test.local', 'username' => 'apparel_shop']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $apparelUser->roles()->syncWithoutDetaching([$vendorRole->id]);
        $apparelVendor = Vendor::create([
            'user_id' => $apparelUser->id,
            'shop_name' => 'Classic Cotton Boutique',
            'slug' => 'classic-cotton-boutique',
            'vendor_type' => 'clothing',
            'operating_sectors' => ['products'],
            'status' => 'approved',
            'verification_status' => 'verified',
            'is_active' => true,
        ]);

        $plan = SubscriptionPlan::firstOrCreate(
            ['code' => 'test-apparel-pro'],
            [
                'name' => 'Pro Apparel',
                'slug' => 'test-apparel-pro',
                'sector_key' => 'products',
                'tier_key' => 'pro',
                'is_free' => true,
                'is_active' => true,
                'price_paise' => 0,
                'billing_interval' => 'monthly',
                'features' => [
                    'themes_allowed' => ['*'],
                    'max_products' => 1000,
                    'max_active_products' => 1000,
                    'custom_domain' => true,
                ],
            ]
        );

        VendorSubscription::create([
            'vendor_id' => $apparelVendor->id,
            'plan_id' => $plan->id,
            'sector_key' => 'products',
            'plan_name_snapshot' => 'Pro Apparel',
            'price_amount_snapshot' => 0,
            'status' => 'active',
            'starts_at' => now(),
            'current_period_end' => now()->addYear(),
        ]);

        $category = $this->createCategory();

        $standardProduct = Product::create([
            'vendor_id' => $apparelVendor->id,
            'category_id' => $category->id,
            'title' => 'Standard Linen T-Shirt',
            'slug' => 'standard-linen-t-shirt',
            'description' => 'Classic breathable linen t-shirt.',
            'base_price' => 79900,
            'total_stock' => 50,
            'status' => 'active',
            'gift_customization_config' => null, // Not a customizable gift
        ]);

        $this->assertFalse($standardProduct->isGiftCustomizable());

        $customer = User::factory()->create(['email' => 'apparelbuyer@k71test.local']);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);
        $customer->roles()->syncWithoutDetaching([$customerRole->id]);
        $this->actingAs($customer);

        // 1. Add standard product
        $this->postJson(route('cart.add'), [
            'product_id' => $standardProduct->id,
            'quantity' => 1,
        ])->assertStatus(200);

        $cart = Cart::where('user_id', $customer->id)->firstOrFail();
        $this->assertCount(1, $cart->items()->get());
        $this->assertNull($cart->items()->first()->customization_data);
        $this->assertFalse($cart->items()->first()->hasGiftCustomization());
        $this->assertEquals(79900, $cart->items()->first()->price_at_add);

        // 2. Add second of same standard product -> increments quantity without duplicating
        $this->postJson(route('cart.add'), [
            'product_id' => $standardProduct->id,
            'quantity' => 1,
        ])->assertStatus(200);

        $this->assertCount(1, $cart->items()->get());
        $this->assertEquals(2, $cart->items()->first()->quantity);
    }
}
