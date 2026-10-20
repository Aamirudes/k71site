<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoodAndRestaurantTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $vendorUser;
    private Vendor $restaurant;
    private VendorSetting $restaurantSettings;
    private Category $foodCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = User::factory()->create();

        $this->vendorUser = User::factory()->create();
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->restaurant = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Royal Biryani & Thali Kitchen',
            'slug' => 'royal-biryani-kitchen',
            'status' => 'approved',
            'phone' => '9876543210',
            'email' => 'kitchen@royalbiryani.test',
            'address' => '12 Station Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'pickup_address' => [
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302001',
            ],
        ]);

        $this->restaurantSettings = VendorSetting::create([
            'vendor_id' => $this->restaurant->id,
            'is_restaurant' => true,
            'is_pure_veg' => false,
            'cuisine_types' => ['North Indian', 'Biryani', 'Mughlai'],
            'opening_time' => '11:00',
            'closing_time' => '23:00',
            'avg_prep_time_mins' => 30,
            'min_food_order_amount' => 15000, // ₹150
            'food_delivery_fee' => 4500,     // ₹45
            'food_delivery_mode' => 'both',
            'cod_enabled' => true,
            'upi_enabled' => true,
            'upi_id' => 'royalbiryani@okaxis',
        ]);

        $this->foodCategory = Category::create([
            'name' => 'Biryani & Rice',
            'slug' => 'biryani-and-rice',
            'vertical' => 'food',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_food_hub_loads_and_displays_restaurants_and_dishes(): void
    {
        $dish = Product::create([
            'vendor_id' => $this->restaurant->id,
            'category_id' => $this->foodCategory->id,
            'title' => 'Hyderabadi Dum Biryani Handi',
            'slug' => 'hyderabadi-dum-biryani-handi',
            'description' => 'Aromatic basmati rice cooked with fresh spices and tender meat.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 32000, // ₹320
            'total_stock' => 50,
            'gst_rate' => 5,
            'status' => 'active',
            'custom_fields' => [
                'dietary_type' => 'Non-Veg',
                'cuisine' => 'Biryani',
                'spice_level' => 'Medium',
                'serving_size' => '2 People',
            ],
        ]);

        $response = $this->get('/food');

        $response->assertStatus(200);
        $response->assertSee('food.k71.in');
        $response->assertSee('Royal Biryani &amp; Thali Kitchen', false);
        $response->assertSee('Hyderabadi Dum Biryani Handi');
    }

    public function test_pure_veg_filter_excludes_non_veg_items(): void
    {
        $nonVegDish = Product::create([
            'vendor_id' => $this->restaurant->id,
            'category_id' => $this->foodCategory->id,
            'title' => 'Mutton Galouti Kebab',
            'slug' => 'mutton-galouti-kebab',
            'description' => 'Melt in the mouth Lucknowi kebabs.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 38000,
            'total_stock' => 20,
            'gst_rate' => 5,
            'status' => 'active',
            'custom_fields' => [
                'dietary_type' => 'Non-Veg',
            ],
        ]);

        $vegDish = Product::create([
            'vendor_id' => $this->restaurant->id,
            'category_id' => $this->foodCategory->id,
            'title' => 'Paneer Tikka Platter',
            'slug' => 'paneer-tikka-platter',
            'description' => 'Charcoal roasted cottage cheese with bell peppers.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 26000,
            'total_stock' => 30,
            'gst_rate' => 5,
            'status' => 'active',
            'custom_fields' => [
                'dietary_type' => 'Pure Veg',
            ],
        ]);

        $response = $this->get('/food?pure_veg=1');

        $response->assertStatus(200);
        $response->assertSee('Paneer Tikka Platter');
        $response->assertDontSee('Mutton Galouti Kebab');
    }

    public function test_restaurant_menu_page_displays_details_and_dishes(): void
    {
        $dish = Product::create([
            'vendor_id' => $this->restaurant->id,
            'category_id' => $this->foodCategory->id,
            'title' => 'Special Lucknowi Biryani',
            'slug' => 'special-lucknowi-biryani',
            'description' => 'Royal authentic Dum Biryani.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 29900,
            'total_stock' => 15,
            'gst_rate' => 5,
            'status' => 'active',
            'custom_fields' => [
                'dietary_type' => 'Veg',
                'spice_level' => 'Mild',
            ],
        ]);

        ProductVariant::create([
            'product_id' => $dish->id,
            'sku' => 'Full Handi',
            'price_override' => 29900,
            'stock' => 15,
        ]);

        $response = $this->get('/food/restaurant/' . $this->restaurant->slug);

        $response->assertStatus(200);
        $response->assertSee('Royal Biryani &amp; Thali Kitchen', false);
        $response->assertSee('Special Lucknowi Biryani');
        $response->assertSee('Prep: ~30 mins');
        $response->assertSee('Delivery: ₹45');
    }

    public function test_food_order_checkout_sets_food_delivery_fee_and_notes(): void
    {
        $dish = Product::create([
            'vendor_id' => $this->restaurant->id,
            'category_id' => $this->foodCategory->id,
            'title' => 'Dal Makhani with Garlic Naan',
            'slug' => 'dal-makhani-garlic-naan',
            'description' => 'Creamy slow cooked black lentils served with 2 garlic naans.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 22000, // ₹220
            'total_stock' => 50,
            'gst_rate' => 5,
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $dish->id,
            'sku' => 'Combo Pack',
            'price_override' => 22000,
            'stock' => 50,
        ]);

        $this->actingAs($this->customer);

        // Add to cart
        $this->postJson('/cart/add', [
            'variant_id' => $variant->id,
            'quantity' => 2,
        ])->assertOk();

        // Place food delivery order
        $response = $this->post(route('checkout.process', $this->restaurant->id), [
            'address_line_1' => 'Flat 402, Civil Lines',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9123456789',
            'payment_method' => 'cod',
            'order_type' => 'food_delivery',
            'kitchen_notes' => 'Please make it extra spicy and pack extra chutney',
        ]);

        $order = Order::where('vendor_id', $this->restaurant->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->isFoodOrder());
        $this->assertTrue($order->isFoodDelivery());
        $this->assertEquals(4500, $order->shipping_amount); // ₹45 configured delivery fee
        $this->assertEquals('Please make it extra spicy and pack extra chutney', $order->kitchen_notes);
        $this->assertNotNull($order->estimated_delivery_time);
    }

    public function test_food_takeaway_order_has_zero_delivery_fee(): void
    {
        $dish = Product::create([
            'vendor_id' => $this->restaurant->id,
            'category_id' => $this->foodCategory->id,
            'title' => 'Veg Pulao Box',
            'slug' => 'veg-pulao-box',
            'description' => 'Fragrant spiced basmati rice with vegetables.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 18000,
            'total_stock' => 25,
            'gst_rate' => 5,
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $dish->id,
            'sku' => 'Box',
            'price_override' => 18000,
            'stock' => 25,
        ]);

        $this->actingAs($this->customer);

        $this->postJson('/cart/add', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ])->assertOk();

        $response = $this->post(route('checkout.process', $this->restaurant->id), [
            'address_line_1' => 'Pickup Counter',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9123456789',
            'payment_method' => 'cod',
            'order_type' => 'food_takeaway',
            'kitchen_notes' => 'Self pickup at 8:30 PM',
        ]);

        $order = Order::where('vendor_id', $this->restaurant->id)->latest()->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->isFoodTakeaway());
        $this->assertEquals(0, $order->shipping_amount); // ₹0 for takeaway
    }

    public function test_vendor_can_progress_food_order_lifecycle(): void
    {
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->restaurant->id,
            'order_number' => 'ORD-FOOD123',
            'order_type' => 'food_delivery',
            'item_count' => 1,
            'subtotal' => 25000,
            'shipping_amount' => 4500,
            'tax_amount' => 1250,
            'discount_amount' => 0,
            'total_amount' => 29500,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'address_snapshot' => [
                'line_1' => 'Plot 10, Malviya Nagar',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302017',
                'phone' => '9988776655',
            ],
            'kitchen_notes' => 'Less oil please',
        ]);

        $this->actingAs($this->vendorUser);

        // 1. Vendor views food order details (Shiprocket bypassed)
        $viewResponse = $this->get(route('vendor.orders.show', $order));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Food Order &amp; Kitchen Dispatch', false);
        $viewResponse->assertSee('Less oil please');

        // 2. Vendor starts cooking (transitions to processing)
        $processResponse = $this->put(route('vendor.orders.status', $order), [
            'status' => 'processing',
        ]);
        $processResponse->assertRedirect();
        $this->assertEquals('processing', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->packed_at);

        // 3. Vendor dispatches rider (transitions to shipped / out for delivery)
        $dispatchResponse = $this->put(route('vendor.orders.status', $order), [
            'status' => 'shipped',
        ]);
        $dispatchResponse->assertRedirect();
        $this->assertEquals('shipped', $order->fresh()->status);

        // 4. Vendor marks delivered
        $deliverResponse = $this->put(route('vendor.orders.status', $order), [
            'status' => 'delivered',
        ]);
        $deliverResponse->assertRedirect();
        $this->assertEquals('delivered', $order->fresh()->status);
    }
}
