<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class GuestCheckoutAndVerticalThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_card_vertical_theme_resolution(): void
    {
        $vendorUser = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Gourmet Kitchen',
            'slug' => 'gourmet-kitchen',
            'phone' => '9876543210',
            'email' => 'kitchen@example.com',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'is_active' => true,
            'verification_status' => 'verified',
        ]);

        $foodCategory = Category::create([
            'name' => 'Biryani & Rice',
            'slug' => 'biryani-rice',
            'vertical' => 'food',
        ]);

        $foodProduct = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $foodCategory->id,
            'title' => 'Hyderabadi Dum Biryani',
            'slug' => 'hyderabadi-dum-biryani',
            'description' => 'Aromatic slow cooked biryani',
            'base_price' => 29900,
            'gst_rate' => 5,
            'stock_quantity' => 50,
            'status' => 'active',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $theme = $foodProduct->vertical_theme;
        $this->assertEquals('food', $theme['vertical']);
        $this->assertEquals('FOOD', $theme['label']);
        $this->assertStringContainsString('orange', $theme['card_border_hover']);
    }

    public function test_customer_can_register_without_password_confirmation(): void
    {
        Role::create(['name' => 'customer', 'guard_name' => 'web']);

        $response = $this->post(route('register'), [
            'first_name' => 'Priya',
            'last_name' => 'Sharma',
            'username' => 'priyasharma',
            'email' => 'priya@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'priya@example.com',
            'first_name' => 'Priya',
            'last_name' => 'Sharma',
        ]);
    }

    public function test_user_can_login_with_raw_10_digit_mobile_number(): void
    {
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        User::factory()->create([
            'email' => 'rahul@example.com',
            'phone' => '+919876543210',
            'email_verified_at' => now(),
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post(route('login'), [
            'login' => '9876543210',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        $response->assertRedirect();
        $this->assertAuthenticated();
    }

    public function test_guest_can_access_checkout_and_place_order(): void
    {
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);

        $vendorUser = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Royal Tandoor',
            'slug' => 'royal-tandoor',
            'phone' => '9876500000',
            'email' => 'royal@example.com',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'is_active' => true,
            'verification_status' => 'verified',
            'cod_enabled' => true,
        ]);

        VendorSetting::updateOrCreate(
            ['vendor_id' => $vendor->id],
            ['cod_enabled' => true, 'upi_enabled' => true]
        );

        $category = Category::create([
            'name' => 'Curries',
            'slug' => 'curries',
            'vertical' => 'food',
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Paneer Butter Masala',
            'slug' => 'paneer-butter-masala',
            'description' => 'Rich gravy curry',
            'base_price' => 24000,
            'gst_rate' => 5,
            'stock_quantity' => 30,
            'status' => 'active',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'PBM-REG',
            'stock' => 30,
            'price_override' => null,
        ]);

        // Start session and create guest cart with current session ID
        $this->withSession(['guest_active' => true]);
        $sessionId = Session::getId();

        $cart = Cart::create(['session_id' => $sessionId]);
        $cart->items()->create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'vendor_id' => $vendor->id,
            'quantity' => 2,
            'price_at_add' => 24000,
        ]);

        // 2. Guest visits checkout without logging in
        $checkoutView = $this->get(route('checkout.vendor', $vendor->id));
        $checkoutView->assertStatus(200);
        $checkoutView->assertSee('Instant Guest Checkout');

        // 3. Guest submits checkout
        $processRes = $this->post(route('checkout.process', $vendor->id), [
            'name' => 'Guest Foodie',
            'email' => 'guestfoodie@example.com',
            'phone' => '9876512345',
            'address_line_1' => 'Flat 402, Sunshine Apartments',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'payment_method' => 'cod',
            'delivery_mode' => 'express',
        ]);

        $this->assertDatabaseHas('orders', [
            'vendor_id' => $vendor->id,
            'payment_method' => 'cod',
        ]);

        $order = Order::where('vendor_id', $vendor->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('9876512345', $order->address_snapshot['phone']);

        // Redirects to success page
        $processRes->assertRedirect(route('orders.success', $order->id));

        // 4. Guest can view order success and invoice without 403
        $successRes = $this->get(route('orders.success', $order->id));
        $successRes->assertStatus(200);

        $invoiceRes = $this->get(route('orders.invoice', $order->id));
        $invoiceRes->assertStatus(200);

        $showRes = $this->get(route('orders.show', $order->id));
        $showRes->assertStatus(200);
    }
}
