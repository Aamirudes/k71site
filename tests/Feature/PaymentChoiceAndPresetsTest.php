<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\StorePreset;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentChoiceAndPresetsTest extends TestCase
{
    use RefreshDatabase;

    private function createVendorWithUser(string $email, string $shopName, string $slug): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create(['email' => $email]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'slug' => $slug,
            'status' => 'approved',
            'contact_phone' => '9876543210',
            'contact_email' => $email,
            'colour_palette' => 'sage',
            'layout_preset' => 'classic',
            'product_preset' => 'gallery',
        ]);

        $settings = VendorSetting::create([
            'vendor_id' => $vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => false,
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 5000,
            'processing_days' => 2,
            'return_window_days' => 7,
            'upi_id' => 'vendor@upi',
        ]);

        return [$user, $vendor, $settings];
    }

    private function createProductWithVariant(Vendor $vendor, int $pricePaise = 100000, int $stock = 10): array
    {
        $category = Category::firstOrCreate(
            ['slug' => 'apparel'],
            ['name' => 'Apparel', 'status' => 'active']
        );

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Test Silk Kurti',
            'slug' => 'test-silk-kurti-' . uniqid(),
            'description' => 'Fine handloom silk kurti',
            'base_price' => $pricePaise,
            'gst_rate' => 12.00,
            'status' => 'active',
            'total_stock' => $stock,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'M',
            'stock' => $stock,
            'price_override' => null,
        ]);

        return [$product, $variant];
    }

    public function test_vendor_can_choose_payment_modes(): void
    {
        [$user, $vendor, $settings] = $this->createVendorWithUser('vendor_pay@test.com', 'Pay Boutique', 'pay-boutique');

        // 1. Choose Both (UPI + COD)
        $response = $this->actingAs($user)->put(route('vendor.settings.update'), [
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 50,
            'processing_days' => 2,
            'return_window_days' => 7,
            'payment_mode' => 'both',
            'upi_id' => 'vendor@okhdfcbank',
        ]);
        $response->assertSessionHasNoErrors();
        $settings->refresh();
        $this->assertTrue((bool) $settings->upi_enabled);
        $this->assertTrue((bool) $settings->cod_enabled);

        // 2. Choose UPI Only
        $response = $this->actingAs($user)->put(route('vendor.settings.update'), [
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 50,
            'processing_days' => 2,
            'return_window_days' => 7,
            'payment_mode' => 'upi_only',
            'upi_id' => 'vendor@okhdfcbank',
        ]);
        $response->assertSessionHasNoErrors();
        $settings->refresh();
        $this->assertTrue((bool) $settings->upi_enabled);
        $this->assertFalse((bool) $settings->cod_enabled);

        // 3. Choose COD Only
        $response = $this->actingAs($user)->put(route('vendor.settings.update'), [
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 50,
            'processing_days' => 2,
            'return_window_days' => 7,
            'payment_mode' => 'cod_only',
            'upi_id' => 'vendor@okhdfcbank',
        ]);
        $response->assertSessionHasNoErrors();
        $settings->refresh();
        $this->assertFalse((bool) $settings->upi_enabled);
        $this->assertTrue((bool) $settings->cod_enabled);

        // 4. Server-side enforcement: cannot disable both
        $response = $this->actingAs($user)->put(route('vendor.settings.update'), [
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 50,
            'processing_days' => 2,
            'return_window_days' => 7,
            'upi_enabled' => 0,
            'cod_enabled' => 0,
            'upi_id' => 'vendor@okhdfcbank',
        ]);
        $response->assertSessionHasErrors(['payment_mode']);
    }

    public function test_checkout_enforces_vendor_payment_rules(): void
    {
        [$vendorUser, $vendor, $settings] = $this->createVendorWithUser('seller@test.com', 'Seller Studio', 'seller-studio');
        [$product, $variant] = $this->createProductWithVariant($vendor, 150000, 10); // ₹1,500

        // Create buyer with cart
        $buyer = User::factory()->create(['email' => 'buyer@test.com']);
        $cart = Cart::create(['user_id' => $buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price_at_add' => $product->base_price,
        ]);

        // Vendor only accepts UPI
        $settings->update(['upi_enabled' => true, 'cod_enabled' => false]);

        // Buyer checkout page loads
        $response = $this->actingAs($buyer)->get(route('checkout.vendor', $vendor->id));
        $response->assertOk();
        $response->assertSee('PhonePe');

        // Attempt forged COD order submission -> should fail validation (in:phonepe,upi)
        $processResponse = $this->actingAs($buyer)->post(route('checkout.process', $vendor->id), [
            'address_line_1' => '123 MG Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
        ]);
        $processResponse->assertSessionHasErrors(['payment_method']);
        $this->assertEquals(0, Order::count());

        // Now vendor enables COD as well
        $settings->update(['upi_enabled' => true, 'cod_enabled' => true]);

        // Buyer places COD order successfully
        $processResponse = $this->actingAs($buyer)->post(route('checkout.process', $vendor->id), [
            'address_line_1' => '123 MG Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals('cod', $order->payment_method);
        $this->assertEquals('pending', $order->payment_status);
        $processResponse->assertRedirect(route('orders.success', $order->id));
    }

    public function test_checkout_blocks_cod_when_order_exceeds_threshold(): void
    {
        [$vendorUser, $vendor, $settings] = $this->createVendorWithUser('seller_high@test.com', 'High Value Store', 'high-val');
        $settings->update(['upi_enabled' => true, 'cod_enabled' => true]);

        // ₹6,000 product (> ₹5,000 max COD threshold)
        [$product, $variant] = $this->createProductWithVariant($vendor, 600000, 5);

        $buyer = User::factory()->create(['email' => 'buyer_high@test.com']);
        $cart = Cart::create(['user_id' => $buyer->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'vendor_id' => $vendor->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price_at_add' => $product->base_price,
        ]);

        // Check index indicates COD disabled due to limit
        $response = $this->actingAs($buyer)->get(route('checkout.vendor', $vendor->id));
        $response->assertOk();
        $response->assertSee('5,000');

        // Submitting COD should be rejected
        $processResponse = $this->actingAs($buyer)->post(route('checkout.process', $vendor->id), [
            'address_line_1' => '123 Luxury Lane',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
        ]);
        $processResponse->assertSessionHasErrors(['payment_method']);
    }

    public function test_vendor_can_save_3x3x3_presets(): void
    {
        [$user, $vendor] = $this->createVendorWithUser('preset_vendor@test.com', 'Preset Atelier', 'preset-atelier');

        $response = $this->actingAs($user)->put(route('vendor.profile.update'), [
            'shop_name' => 'Preset Atelier Updated',
            'colour_palette' => 'midnight',
            'layout_preset' => 'editorial',
            'product_preset' => 'immersive',
        ]);
        $response->assertSessionHasNoErrors();

        $vendor->refresh();
        $this->assertEquals('midnight', $vendor->colour_palette);
        $this->assertEquals('editorial', $vendor->layout_preset);
        $this->assertEquals('immersive', $vendor->product_preset);
    }

    public function test_store_presets_table_has_seeded_presets(): void
    {
        $this->assertGreaterThanOrEqual(3, StorePreset::ofType('store_layout')->count());
        $this->assertGreaterThanOrEqual(3, StorePreset::ofType('colour_preset')->count());
        $this->assertGreaterThanOrEqual(3, StorePreset::ofType('product_layout')->count());

        $this->assertNotNull(StorePreset::where('key', 'sage')->first());
        $this->assertNotNull(StorePreset::where('key', 'midnight')->first());
        $this->assertNotNull(StorePreset::where('key', 'sunset')->first());
        $this->assertNotNull(StorePreset::where('key', 'gallery')->first());
        $this->assertNotNull(StorePreset::where('key', 'immersive')->first());
        $this->assertNotNull(StorePreset::where('key', 'showcase')->first());
    }
}
