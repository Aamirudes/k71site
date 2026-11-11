<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\Category;
use App\Models\FoodMenuCategory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductModifier;
use App\Models\ProductOptionGroup;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class K71FoodEcosystemTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $otherCustomer;
    private User $admin;
    private User $vendorUserA;
    private User $vendorUserB;
    private Vendor $kitchenA;
    private Vendor $kitchenB;
    private VendorSetting $settingsA;
    private VendorSetting $settingsB;
    private Category $foodCategory;
    private Category $retailCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);

        $this->customer = User::factory()->create([
            'first_name' => 'Aanya',
            'last_name' => 'Kapoor',
            'email' => 'aanya@foodtest.in',
        ]);

        $this->otherCustomer = User::factory()->create([
            'first_name' => 'Kabir',
            'last_name' => 'Rao',
            'email' => 'kabir@foodtest.in',
        ]);

        $this->admin = User::factory()->create([
            'first_name' => 'Master',
            'last_name' => 'Admin',
            'email' => 'admin@k71.in',
        ]);
        $this->admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->vendorUserA = User::factory()->create([
            'first_name' => 'Chef',
            'last_name' => 'Jaipur',
            'email' => 'chefa@foodtest.in',
        ]);
        $this->vendorUserA->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendorUserB = User::factory()->create([
            'first_name' => 'Chef',
            'last_name' => 'Delhi',
            'email' => 'chefb@foodtest.in',
        ]);
        $this->vendorUserB->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->kitchenA = Vendor::create([
            'user_id' => $this->vendorUserA->id,
            'shop_name' => 'Jaipur Spice Bowl',
            'slug' => 'jaipur-spice-bowl',
            'vendor_type' => 'food',
            'status' => 'approved',
            'phone' => '9829000111',
            'email' => 'spice@jaipur.test',
            'address' => '14 MI Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ]);

        $this->settingsA = VendorSetting::create([
            'vendor_id' => $this->kitchenA->id,
            'is_restaurant' => true,
            'is_pure_veg' => false,
            'is_accepting_food_orders' => true,
            'cuisine_types' => ['North Indian', 'Thali'],
            'opening_time' => '09:00',
            'closing_time' => '23:30',
            'avg_prep_time_mins' => 25,
            'min_food_order_amount' => 15000, // ₹150
            'food_delivery_fee' => 4000,     // ₹40
            'food_packaging_fee' => 1500,    // ₹15
            'food_free_delivery_above' => 50000, // Free delivery >= ₹500
            'food_delivery_mode' => 'both',
            'serviceable_pincodes' => json_encode(['302001', '302002']),
            'service_cities_json' => ['Jaipur'],
            'cod_enabled' => true,
            'upi_enabled' => true,
            'upi_id' => 'spicebowl@okicici',
        ]);

        $this->kitchenB = Vendor::create([
            'user_id' => $this->vendorUserB->id,
            'shop_name' => 'Connaught Pizza Oven',
            'slug' => 'connaught-pizza-oven',
            'vendor_type' => 'food',
            'status' => 'approved',
            'phone' => '9811000222',
            'email' => 'oven@delhi.test',
            'address' => 'Block B Connaught Place',
            'city' => 'Delhi',
            'state' => 'Delhi',
            'pincode' => '110001',
        ]);

        $this->settingsB = VendorSetting::create([
            'vendor_id' => $this->kitchenB->id,
            'is_restaurant' => true,
            'is_pure_veg' => true,
            'is_accepting_food_orders' => true,
            'cuisine_types' => ['Italian', 'Pizza'],
            'opening_time' => '10:00',
            'closing_time' => '23:30',
            'avg_prep_time_mins' => 20,
            'min_food_order_amount' => 10000,
            'food_delivery_fee' => 3000,
            'food_packaging_fee' => 1000,
            'food_delivery_mode' => 'both',
            'serviceable_pincodes' => json_encode(['110001']),
            'service_cities_json' => ['Delhi'],
            'cod_enabled' => true,
            'upi_enabled' => true,
            'upi_id' => 'pizzaoven@okaxis',
        ]);

        $this->foodCategory = Category::create([
            'name' => 'Main Course',
            'slug' => 'food-main-course',
            'vertical' => 'food',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->retailCategory = Category::create([
            'name' => 'Home Decor',
            'slug' => 'home-decor',
            'vertical' => 'marketplace',
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }

    private function makeFoodDish(Vendor $vendor, array $overrides = [], ?int $variantPricePaise = null): array
    {
        $price = $overrides['base_price'] ?? 22000;
        $stock = $overrides['total_stock'] ?? 40;
        $title = $overrides['title'] ?? 'Paneer Lababdar';

        $dish = Product::withoutGlobalScopes()->create(array_merge([
            'vendor_id' => $vendor->id,
            'category_id' => $this->foodCategory->id,
            'title' => $title,
            'slug' => 'dish-' . uniqid(),
            'description' => 'Freshly prepared kitchen signature dish.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => $price,
            'total_stock' => $stock,
            'gst_rate' => 5,
            'status' => 'active',
            'is_dish_available' => true,
            'dietary_label' => 'veg',
        ], $overrides));

        $variant = ProductVariant::withoutGlobalScopes()->create([
            'product_id' => $dish->id,
            'sku' => 'Regular Bowl',
            'price' => round(($variantPricePaise ?? $price) / 100, 2),
            'price_override' => $variantPricePaise ?? $price,
            'stock' => $stock,
            'stock_quantity' => $stock,
            'is_active' => true,
            'status' => 'active',
        ]);

        return [$dish, $variant];
    }

    public function test_location_persistence_serviceability_filtering_empty_state_and_reset(): void
    {
        $this->makeFoodDish($this->kitchenA, ['title' => 'Dal Baati Special']);
        $this->makeFoodDish($this->kitchenB, ['title' => 'Woodfired Margherita']);

        // 1. Set location to Jaipur 302001 -> only Jaipur Spice Bowl shown
        $this->post(route('food.location.set'), [
            'city' => 'Jaipur',
            'pincode' => '302001',
        ])->assertRedirect();

        $this->get(route('food.index'))
            ->assertOk()
            ->assertSee('Jaipur Spice Bowl')
            ->assertSee('Dal Baati Special')
            ->assertDontSee('Connaught Pizza Oven')
            ->assertDontSee('Woodfired Margherita');

        // 2. Unserviceable area -> helpful empty state shown
        $this->postJson(route('food.location.set'), [
            'city' => 'Udaipur',
            'pincode' => '313001',
        ])->assertOk()->assertJsonPath('success', true);

        $this->get(route('food.index'))
            ->assertOk()
            ->assertDontSee('Jaipur Spice Bowl')
            ->assertDontSee('Connaught Pizza Oven')
            ->assertSee('Udaipur 313001');

        // 3. Reset location -> all restaurants appear again
        $this->postJson(route('food.location.set'), ['clear' => true])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->get(route('food.index'))
            ->assertOk()
            ->assertSee('Jaipur Spice Bowl')
            ->assertSee('Connaught Pizza Oven');
    }

    public function test_unavailable_and_out_of_window_dishes_cannot_be_ordered(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(15, 0));

        try {
            [$soldOutDish, $soldOutVar] = $this->makeFoodDish($this->kitchenA, [
                'title' => 'Morning Poha Platter',
                'is_dish_available' => false,
            ]);

            [$lunchOnlyDish, $lunchOnlyVar] = $this->makeFoodDish($this->kitchenA, [
                'title' => 'Breakfast Masala Toast',
                'is_dish_available' => true,
                'available_from' => '07:00',
                'available_until' => '11:00',
            ]);

            [$availableDish, $availableVar] = $this->makeFoodDish($this->kitchenA, [
                'title' => 'Afternoon Veg Thali',
                'is_dish_available' => true,
                'available_from' => '12:00',
                'available_until' => '22:00',
            ]);

            // Trending dishes only include currently available dishes in window
            $this->get(route('food.index'))
                ->assertOk()
                ->assertSee('Afternoon Veg Thali')
                ->assertDontSee('Morning Poha Platter')
                ->assertDontSee('Breakfast Masala Toast');

            // Direct /cart/add fails with 422 on unavailable or out-of-window dishes
            $this->actingAs($this->customer);

            $this->postJson('/cart/add', [
                'product_id' => $soldOutDish->id,
                'variant_id' => $soldOutVar->id,
                'quantity' => 1,
            ])->assertStatus(422);

            $this->postJson('/cart/add', [
                'product_id' => $lunchOnlyDish->id,
                'variant_id' => $lunchOnlyVar->id,
                'quantity' => 1,
            ])->assertStatus(422);

            $this->postJson('/cart/add', [
                'product_id' => $availableDish->id,
                'variant_id' => $availableVar->id,
                'quantity' => 1,
            ])->assertOk();
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_option_groups_validation_server_side_pricing_and_special_instructions(): void
    {
        [$dish, $variant] = $this->makeFoodDish($this->kitchenA, ['base_price' => 20000], 20000);
        [$foreignDish] = $this->makeFoodDish($this->kitchenB, ['base_price' => 25000]);

        $spiceGroup = ProductOptionGroup::create([
            'product_id' => $dish->id,
            'name' => 'Spice Level',
            'selection_type' => 'single',
            'is_required' => true,
            'min_selections' => 1,
            'max_selections' => 1,
            'is_active' => true,
        ]);

        $mildOpt = ProductModifier::create([
            'product_id' => $dish->id,
            'option_group_id' => $spiceGroup->id,
            'group_name' => 'Spice Level',
            'modifier_name' => 'Mild Herb',
            'price_delta' => 0,
            'is_required' => true,
            'is_available' => true,
        ]);

        $fieryOpt = ProductModifier::create([
            'product_id' => $dish->id,
            'option_group_id' => $spiceGroup->id,
            'group_name' => 'Spice Level',
            'modifier_name' => 'Extra Fiery',
            'price_delta' => 25, // +₹25
            'is_required' => true,
            'is_available' => true,
        ]);

        $addonGroup = ProductOptionGroup::create([
            'product_id' => $dish->id,
            'name' => 'Sides & Toppings',
            'selection_type' => 'multi',
            'is_required' => false,
            'min_selections' => 0,
            'max_selections' => 2,
            'is_active' => true,
        ]);

        $cheeseOpt = ProductModifier::create([
            'product_id' => $dish->id,
            'option_group_id' => $addonGroup->id,
            'group_name' => 'Sides & Toppings',
            'modifier_name' => 'Extra Butter',
            'price_delta' => 30, // +₹30
            'is_available' => true,
        ]);

        $naanOpt = ProductModifier::create([
            'product_id' => $dish->id,
            'option_group_id' => $addonGroup->id,
            'group_name' => 'Sides & Toppings',
            'modifier_name' => 'Garlic Naan',
            'price_delta' => 50,
            'is_available' => true,
        ]);

        $raitaOpt = ProductModifier::create([
            'product_id' => $dish->id,
            'option_group_id' => $addonGroup->id,
            'group_name' => 'Sides & Toppings',
            'modifier_name' => 'Boondi Raita',
            'price_delta' => 40,
            'is_available' => true,
        ]);

        $soldOutOpt = ProductModifier::create([
            'product_id' => $dish->id,
            'option_group_id' => $addonGroup->id,
            'group_name' => 'Sides & Toppings',
            'modifier_name' => 'Truffle Drizzle',
            'price_delta' => 80,
            'is_available' => false,
        ]);

        $foreignOpt = ProductModifier::create([
            'product_id' => $foreignDish->id,
            'group_name' => 'Foreign',
            'modifier_name' => 'Olives',
            'price_delta' => 20,
            'is_available' => true,
        ]);

        $this->actingAs($this->customer);

        // Missing required group -> 422
        $this->postJson('/cart/add', [
            'product_id' => $dish->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'selected_modifiers' => [],
        ])->assertStatus(422);

        // Selecting 2 options in single-choice group -> 422
        $this->postJson('/cart/add', [
            'product_id' => $dish->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'selected_modifiers' => [$mildOpt->id, $fieryOpt->id],
        ])->assertStatus(422);

        // Exceeding max_selections (2) in multi group -> 422
        $this->postJson('/cart/add', [
            'product_id' => $dish->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'selected_modifiers' => [$mildOpt->id, $cheeseOpt->id, $naanOpt->id, $raitaOpt->id],
        ])->assertStatus(422);

        // Unavailable add-on -> 422
        $this->postJson('/cart/add', [
            'product_id' => $dish->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'selected_modifiers' => [$mildOpt->id, $soldOutOpt->id],
        ])->assertStatus(422);

        // Foreign dish add-on -> 422
        $this->postJson('/cart/add', [
            'product_id' => $dish->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'selected_modifiers' => [$mildOpt->id, $foreignOpt->id],
        ])->assertStatus(422);

        // Valid selection with forged client price ignored -> ₹200 + ₹25 + ₹30 = ₹255 (25500 paise)
        $this->postJson('/cart/add', [
            'product_id' => $dish->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 1,
            'selected_modifiers' => [$fieryOpt->id, $cheeseOpt->id],
            'special_instructions' => 'No onion, less oil please',
        ])->assertOk();

        $this->post(route('checkout.process', $this->kitchenA->id), [
            'address_line_1' => '21 Bapu Nagar',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876501234',
            'payment_method' => 'cod',
            'order_type' => 'food_delivery',
        ])->assertRedirect();

        $order = Order::where('vendor_id', $this->kitchenA->id)->latest()->first();
        $this->assertNotNull($order);
        /** @var OrderItem $orderItem */
        $orderItem = $order->items()->first();
        $this->assertEquals(25500, $orderItem->unit_price);
        $this->assertEquals(5500, $orderItem->addon_amount);
        $this->assertEquals('No onion, less oil please', $orderItem->special_instructions);
        $this->assertEquals(51000, $order->subtotal); // ₹510
    }

    public function test_single_restaurant_bag_conflict_preserves_retail_items_on_clear(): void
    {
        $retailVendorUser = User::factory()->create();
        $retailVendor = Vendor::create([
            'user_id' => $retailVendorUser->id,
            'shop_name' => 'K71 Craft Studio',
            'slug' => 'k71-craft-studio',
            'vendor_type' => 'clothing',
            'status' => 'approved',
        ]);
        VendorSetting::create(['vendor_id' => $retailVendor->id, 'cod_enabled' => true, 'upi_enabled' => true]);

        $retailProd = Product::withoutGlobalScopes()->create([
            'vendor_id' => $retailVendor->id,
            'category_id' => $this->retailCategory->id,
            'title' => 'Marble Coaster Set',
            'slug' => 'marble-coaster-set',
            'description' => 'Handcrafted stone coasters.',
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'base_price' => 49900,
            'total_stock' => 20,
            'gst_rate' => 12,
            'status' => 'active',
        ]);
        $retailVar = ProductVariant::withoutGlobalScopes()->create([
            'product_id' => $retailProd->id,
            'sku' => 'Set-4',
            'price' => 499,
            'price_override' => 49900,
            'stock' => 20,
            'is_active' => true,
            'status' => 'active',
        ]);

        [$dishA, $varA] = $this->makeFoodDish($this->kitchenA, ['title' => 'Jaipur Curry Bowl']);
        [$dishB, $varB] = $this->makeFoodDish($this->kitchenB, ['title' => 'CP Veg Pizza']);

        $this->actingAs($this->customer);

        // Add retail item + Kitchen A dish
        $this->postJson('/cart/add', ['variant_id' => $retailVar->id, 'quantity' => 1])->assertOk();
        $this->postJson('/cart/add', ['variant_id' => $varA->id, 'quantity' => 1])->assertOk();

        // Adding Kitchen B dish conflicts with 409 JSON
        $this->postJson('/cart/add', ['variant_id' => $varB->id, 'quantity' => 1])
            ->assertStatus(409)
            ->assertJsonPath('conflict', true)
            ->assertJsonPath('current_vendor_id', $this->kitchenA->id)
            ->assertJsonPath('new_vendor_id', $this->kitchenB->id);

        // Retry with clear_existing=1 -> Kitchen A item removed, Kitchen B added, Retail preserved!
        $this->postJson('/cart/add', [
            'variant_id' => $varB->id,
            'quantity' => 1,
            'clear_existing' => true,
        ])->assertOk();

        $cart = Cart::where('user_id', $this->customer->id)->firstOrFail();
        $vendorIdsInCart = $cart->items()->pluck('vendor_id')->all();
        $this->assertContains($retailVendor->id, $vendorIdsInCart);
        $this->assertContains($this->kitchenB->id, $vendorIdsInCart);
        $this->assertNotContains($this->kitchenA->id, $vendorIdsInCart);
    }

    public function test_authoritative_food_fees_free_threshold_takeaway_otp_and_mode_restrictions(): void
    {
        [$dish, $variant] = $this->makeFoodDish($this->kitchenA, ['base_price' => 20000], 20000);

        $this->actingAs($this->customer);

        // Below free delivery threshold (₹200 < ₹500): delivery ₹40 + packaging ₹15 = ₹255
        $this->postJson('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1])->assertOk();
        $this->post(route('checkout.process', $this->kitchenA->id), [
            'address_line_1' => '10 Civil Lines',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
            'order_type' => 'food_delivery',
        ])->assertRedirect();

        $order1 = Order::where('vendor_id', $this->kitchenA->id)->latest('id')->first();
        $this->assertEquals(4000, $order1->shipping_amount);
        $this->assertEquals(1500, $order1->packaging_amount);
        $this->assertEquals(25500, $order1->total_amount);

        // Above free delivery threshold (₹600 >= ₹500): delivery ₹0 + packaging ₹15 = ₹615
        $this->postJson('/cart/add', ['variant_id' => $variant->id, 'quantity' => 3])->assertOk();
        $this->post(route('checkout.process', $this->kitchenA->id), [
            'address_line_1' => '10 Civil Lines',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
            'order_type' => 'food_delivery',
        ])->assertRedirect();

        $order2 = Order::where('vendor_id', $this->kitchenA->id)->latest('id')->first();
        $this->assertEquals(0, $order2->shipping_amount);
        $this->assertEquals(1500, $order2->packaging_amount);
        $this->assertEquals(61500, $order2->total_amount);

        // Takeaway order: delivery ₹0 + packaging ₹15 + 4-digit pickup OTP
        $this->postJson('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1])->assertOk();
        $this->post(route('checkout.process', $this->kitchenA->id), [
            'address_line_1' => 'Self Pickup',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
            'order_type' => 'food_takeaway',
        ])->assertRedirect();

        $takeawayOrder = Order::where('vendor_id', $this->kitchenA->id)->latest('id')->first();
        $this->assertEquals('food_takeaway', $takeawayOrder->order_type);
        $this->assertEquals(0, $takeawayOrder->shipping_amount);
        $this->assertEquals(1500, $takeawayOrder->packaging_amount);
        $this->assertMatchesRegularExpression('/^\d{4}$/', (string) $takeawayOrder->pickup_code);

        // Restriction check: when kitchen allows only 'delivery', takeaway checkout is rejected cleanly
        $this->settingsA->update(['food_delivery_mode' => 'delivery']);
        $this->postJson('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1])->assertOk();
        $beforeCount = Order::where('vendor_id', $this->kitchenA->id)->count();

        $this->post(route('checkout.process', $this->kitchenA->id), [
            'address_line_1' => 'Self Pickup',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
            'order_type' => 'food_takeaway',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertEquals($beforeCount, Order::where('vendor_id', $this->kitchenA->id)->count());
    }

    public function test_paused_kitchen_blocks_cart_add_and_checkout(): void
    {
        [$dish, $variant] = $this->makeFoodDish($this->kitchenA, ['base_price' => 25000]);

        // Customer adds dish while open
        $this->actingAs($this->customer);
        $this->postJson('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1])->assertOk();

        // Kitchen pauses orders via vendor toggle route
        $this->actingAs($this->vendorUserA);
        $this->postJson(route('vendor.food.toggle_orders'), [
            'is_accepting_food_orders' => false,
            'paused_reason' => 'Oven maintenance break',
        ])->assertOk()->assertJsonPath('is_accepting_food_orders', false);

        // Customer is blocked from adding another dish & blocked from checking out
        $this->actingAs($this->customer);
        $this->postJson('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1])
            ->assertStatus(422);

        $beforeCount = Order::where('vendor_id', $this->kitchenA->id)->count();
        $this->post(route('checkout.process', $this->kitchenA->id), [
            'address_line_1' => '9 Tonk Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'phone' => '9876543210',
            'payment_method' => 'cod',
            'order_type' => 'food_delivery',
        ])->assertRedirect()->assertSessionHas('error');

        $this->assertEquals($beforeCount, Order::where('vendor_id', $this->kitchenA->id)->count());
    }

    public function test_vendor_menu_categories_crud_reorder_product_edit_and_idor_protection(): void
    {
        [$dish, $variant] = $this->makeFoodDish($this->kitchenA, ['title' => 'Classic Kadhai Paneer']);

        $this->actingAs($this->vendorUserA);

        $c1 = $this->postJson(route('vendor.food.categories.store'), ['name' => 'Starters'])
            ->assertCreated()
            ->json('category');
        $c2 = $this->postJson(route('vendor.food.categories.store'), ['name' => 'Curries'])
            ->assertCreated()
            ->json('category');

        $this->postJson(route('vendor.food.categories.reorder'), [
            'order' => [$c2['id'], $c1['id']],
        ])->assertOk();

        $this->assertEquals(1, FoodMenuCategory::find($c2['id'])->sort_order);
        $this->assertEquals(2, FoodMenuCategory::find($c1['id'])->sort_order);

        // Assign dish to Curries section & create option group via Vendor\ProductController::update
        $this->put(route('vendor.products.update', $dish), [
            'title' => 'Classic Kadhai Paneer Bowl',
            'category_id' => $this->foodCategory->id,
            'vertical' => 'food',
            'food_settings_present' => 1,
            'food_menu_category_id' => $c2['id'],
            'dietary_label' => 'pure_veg',
            'prep_time_mins' => 18,
            'menu_sort_order' => 3,
            'is_dish_available' => 1,
            'base_price' => 260,
            'gst_rate' => 5,
            'description' => 'Wok-tossed cottage cheese with bell peppers.',
            'total_stock' => 25,
            'status' => 'active',
            'option_groups_submitted' => 1,
            'option_groups' => [
                [
                    'name' => 'Gravy Preference',
                    'selection_type' => 'single',
                    'is_required' => 1,
                    'options' => [
                        ['modifier_name' => 'Rich Cashew Gravy', 'price_delta' => 0],
                        ['modifier_name' => 'Spicy Tadka', 'price_delta' => 20],
                    ],
                ],
            ],
        ])->assertRedirect(route('vendor.products.index'));

        $dish->refresh();
        $this->assertEquals($c2['id'], $dish->food_menu_category_id);
        $this->assertEquals('pure_veg', $dish->dietary_label);
        $this->assertEquals(18, $dish->prep_time_mins);
        $this->assertEquals(1, $dish->optionGroups()->count());

        // Toggle dish availability off & back on
        $this->patchJson(route('vendor.products.toggle_availability', $dish))
            ->assertOk()
            ->assertJsonPath('is_dish_available', false);

        // Vendor B cannot tamper with Kitchen A category or dish availability (403 IDOR check)
        $this->actingAs($this->vendorUserB);
        $this->putJson(route('vendor.food.categories.update', $c2['id']), ['name' => 'Hacked'])
            ->assertStatus(403);
        $this->deleteJson(route('vendor.food.categories.destroy', $c2['id']))
            ->assertStatus(403);
        $this->patchJson(route('vendor.products.toggle_availability', $dish))
            ->assertStatus(403);

        // Deleting section from Kitchen A sets dish food_menu_category_id to null cleanly
        $this->actingAs($this->vendorUserA);
        $this->deleteJson(route('vendor.food.categories.destroy', $c2['id']))->assertOk();
        $this->assertNull($dish->fresh()->food_menu_category_id);
    }

    public function test_food_order_lifecycle_and_customer_cancellation_cutoffs(): void
    {
        $orderCancelable = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->kitchenA->id,
            'order_number' => 'K71F-CAN1',
            'order_type' => 'food_takeaway',
            'pickup_code' => '4821',
            'item_count' => 1,
            'subtotal' => 20000,
            'shipping_amount' => 0,
            'packaging_amount' => 1500,
            'tax_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 21500,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'address_snapshot' => ['line_1' => 'Pickup', 'city' => 'Jaipur', 'state' => 'RJ', 'pincode' => '302001', 'phone' => '9876543210'],
        ]);

        // Cancelable before ready_for_pickup
        $this->actingAs($this->customer);
        $this->post(route('orders.cancel', $orderCancelable), ['reason' => 'Changed plan'])
            ->assertRedirect();
        $this->assertEquals('cancelled', $orderCancelable->fresh()->status);

        // Full lifecycle: pending -> processing -> ready_for_pickup -> shipped -> delivered
        $order = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->kitchenA->id,
            'order_number' => 'K71F-LIFE1',
            'order_type' => 'food_delivery',
            'item_count' => 1,
            'subtotal' => 30000,
            'shipping_amount' => 4000,
            'packaging_amount' => 1500,
            'tax_amount' => 1500,
            'discount_amount' => 0,
            'total_amount' => 35500,
            'status' => 'pending',
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'address_snapshot' => ['line_1' => 'MI Road', 'city' => 'Jaipur', 'state' => 'RJ', 'pincode' => '302001', 'phone' => '9876543210'],
        ]);

        $this->actingAs($this->vendorUserA);
        $this->put(route('vendor.orders.status', $order), ['status' => 'processing'])->assertRedirect();
        $this->assertEquals('preparing', $order->fresh()->food_fulfillment_stage);

        $this->put(route('vendor.orders.status', $order), ['status' => 'ready_for_pickup'])->assertRedirect();
        $this->assertEquals('ready_for_pickup', $order->fresh()->status);
        $this->assertEquals('ready', $order->fresh()->food_fulfillment_stage);

        // Once ready_for_pickup, customer cancellation is blocked
        $this->actingAs($this->customer);
        $this->post(route('orders.cancel', $order), ['reason' => 'Too late'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertEquals('ready_for_pickup', $order->fresh()->status);

        // Finish dispatch & delivery -> cannot transition out of delivered
        $this->actingAs($this->vendorUserA);
        $this->put(route('vendor.orders.status', $order), ['status' => 'shipped'])->assertRedirect();
        $this->put(route('vendor.orders.status', $order), ['status' => 'delivered'])->assertRedirect();
        $this->assertEquals('delivered', $order->fresh()->status);

        $this->put(route('vendor.orders.status', $order), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertEquals('delivered', $order->fresh()->status);
    }

    public function test_verified_food_order_review_rating_cache_vendor_reply_and_self_review_block(): void
    {
        $deliveredOrder = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->kitchenA->id,
            'order_number' => 'K71F-REV1',
            'order_type' => 'food_delivery',
            'item_count' => 1,
            'subtotal' => 25000,
            'shipping_amount' => 4000,
            'tax_amount' => 1250,
            'discount_amount' => 0,
            'total_amount' => 29000,
            'status' => 'delivered',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'address_snapshot' => ['line_1' => 'Jaipur', 'city' => 'Jaipur', 'state' => 'RJ', 'pincode' => '302001', 'phone' => '9876543210'],
        ]);

        // Unrelated user gets 403
        $this->actingAs($this->otherCustomer);
        $this->post(route('food.orders.review', $deliveredOrder), [
            'rating' => 1,
            'body' => 'Fake review attempt.',
        ])->assertStatus(403);

        // Customer submits genuine review
        $this->actingAs($this->customer);
        $this->post(route('food.orders.review', $deliveredOrder), [
            'rating' => 5,
            'title' => 'Piping hot delivery',
            'body' => 'Fresh paneer, authentic spices, great packaging.',
        ])->assertRedirect()->assertSessionHas('success');

        $review = Review::where('order_id', $deliveredOrder->id)->firstOrFail();
        $this->assertEquals('food_order', $review->review_type);
        $this->assertEquals(5.0, (float) $this->settingsA->fresh()->food_avg_rating);
        $this->assertEquals(1, (int) $this->settingsA->fresh()->food_review_count);
        $this->assertEquals(5.0, (float) $this->kitchenA->fresh()->rating);

        // Duplicate order review rejected
        $this->post(route('food.orders.review', $deliveredOrder), [
            'rating' => 4,
            'body' => 'Duplicate attempt',
        ])->assertRedirect()->assertSessionHas('error');

        // Self-review attempt by kitchen owner on own order gets 403
        $selfOrder = Order::create([
            'user_id' => $this->vendorUserA->id,
            'vendor_id' => $this->kitchenA->id,
            'order_number' => 'K71F-SELF',
            'order_type' => 'food_delivery',
            'item_count' => 1,
            'subtotal' => 20000,
            'shipping_amount' => 0,
            'tax_amount' => 1000,
            'discount_amount' => 0,
            'total_amount' => 20000,
            'status' => 'delivered',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'address_snapshot' => ['line_1' => 'Self', 'city' => 'Jaipur', 'state' => 'RJ', 'pincode' => '302001', 'phone' => '9876543210'],
        ]);

        $this->actingAs($this->vendorUserA);
        $this->post(route('food.orders.review', $selfOrder), [
            'rating' => 5,
            'body' => 'Self inflated 5 star rating',
        ])->assertStatus(403);

        // Restaurant posts official reply to customer's review
        $this->post(route('vendor.reviews.reply', $review), [
            'vendor_reply' => 'Thank you Aanya! Glad you loved the spices.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertEquals('Thank you Aanya! Glad you loved the spices.', $review->fresh()->vendor_reply);
        $this->assertNotNull($review->fresh()->vendor_replied_at);
    }

    public function test_one_click_reorder_adds_live_dishes_skips_sold_out_and_handles_conflict(): void
    {
        [$availDish, $availVar] = $this->makeFoodDish($this->kitchenA, ['title' => 'Jeera Rice Bowl', 'base_price' => 16000], 16000);
        [$soldOutDish, $soldOutVar] = $this->makeFoodDish($this->kitchenA, ['title' => 'Seasonal Halwa', 'is_dish_available' => false, 'base_price' => 12000]);
        [$kitchenBDish, $kitchenBVar] = $this->makeFoodDish($this->kitchenB, ['title' => 'Margherita', 'base_price' => 25000]);

        $pastOrder = Order::create([
            'user_id' => $this->customer->id,
            'vendor_id' => $this->kitchenA->id,
            'order_number' => 'K71F-PAST',
            'order_type' => 'food_delivery',
            'item_count' => 2,
            'subtotal' => 28000,
            'shipping_amount' => 4000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 32000,
            'status' => 'delivered',
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'address_snapshot' => ['line_1' => 'Jaipur', 'city' => 'Jaipur', 'state' => 'RJ', 'pincode' => '302001', 'phone' => '9876543210'],
        ]);

        OrderItem::create([
            'order_id' => $pastOrder->id,
            'product_id' => $availDish->id,
            'variant_id' => $availVar->id,
            'quantity' => 2,
            'unit_price' => 14000, // old price; reorder must recompute live price ₹160 (16000)
            'total_price' => 28000,
            'tax_amount' => 0,
            'gst_rate' => 5,
            'special_instructions' => 'Hot & fresh',
            'snapshot' => ['title' => 'Jeera Rice Bowl'],
        ]);
        OrderItem::create([
            'order_id' => $pastOrder->id,
            'product_id' => $soldOutDish->id,
            'variant_id' => $soldOutVar->id,
            'quantity' => 1,
            'unit_price' => 12000,
            'total_price' => 12000,
            'tax_amount' => 0,
            'gst_rate' => 5,
            'snapshot' => ['title' => 'Seasonal Halwa'],
        ]);

        $this->actingAs($this->customer);

        // Put Kitchen B dish in cart first -> reorder triggers 409 JSON conflict
        $this->postJson('/cart/add', ['variant_id' => $kitchenBVar->id, 'quantity' => 1])->assertOk();
        $this->postJson(route('food.reorder', $pastOrder))
            ->assertStatus(409)
            ->assertJsonPath('conflict', true);

        // Retry with clear_existing=1 -> adds live-priced available dish, skips sold-out dish cleanly
        $res = $this->postJson(route('food.reorder', $pastOrder), ['clear_existing' => true])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('added', 1);

        $this->assertEquals(['Seasonal Halwa'], $res->json('skipped'));

        $cart = Cart::where('user_id', $this->customer->id)->firstOrFail();
        $cartItem = $cart->items()->firstOrFail();
        $this->assertEquals($availDish->id, $cartItem->product_id);
        $this->assertEquals(16000, $cartItem->price_at_add);
        $this->assertEquals(2, $cartItem->quantity);
        $this->assertEquals('Hot & fresh', $cartItem->special_instructions);
    }

    public function test_saved_addresses_crud_checkout_prefill_and_fssai_verification_reset(): void
    {
        [$dish, $variant] = $this->makeFoodDish($this->kitchenA, ['base_price' => 22000]);

        // 1. Customer Address CRUD + IDOR + Default Promotion
        $this->actingAs($this->customer);

        $addr1 = $this->postJson(route('addresses.store'), [
            'label' => 'Home',
            'recipient_name' => 'Aanya Kapoor',
            'phone' => '9876543210',
            'address_line_1' => '18 Malviya Nagar',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ])->assertCreated()->json('address');
        $this->assertTrue((bool) $addr1['is_default']);

        $addr2 = $this->postJson(route('addresses.store'), [
            'label' => 'Office',
            'recipient_name' => 'Aanya Kapoor',
            'phone' => '9876500000',
            'address_line_1' => 'Tech Park Tonk Road',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302002',
            'is_default' => true,
        ])->assertCreated()->json('address');
        $this->assertTrue((bool) $addr2['is_default']);
        $this->assertFalse((bool) Address::find($addr1['id'])->is_default);

        // Checkout page prefills saved addresses for customer
        $this->postJson('/cart/add', ['variant_id' => $variant->id, 'quantity' => 1])->assertOk();
        $this->get(route('checkout.vendor', $this->kitchenA->id))
            ->assertOk()
            ->assertSee('Tech Park Tonk Road')
            ->assertSee('18 Malviya Nagar');

        // Delete default address -> remaining address promoted cleanly
        $this->deleteJson(route('addresses.destroy', $addr2['id']))->assertOk();
        $this->assertTrue((bool) Address::find($addr1['id'])->is_default);

        // Other user gets 403 accessing customer's address
        $this->actingAs($this->otherCustomer);
        $this->deleteJson(route('addresses.destroy', $addr1['id']))->assertStatus(403);

        // 2. Admin FSSAI verification + automatic reset when vendor updates license number
        $this->actingAs($this->admin);
        // Without license number -> fails safely
        $this->post(route('admin.vendors.verify_fssai', $this->kitchenA), ['verified' => 1])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->settingsA->update(['fssai_license_number' => '12226026000123']);
        $this->post(route('admin.vendors.verify_fssai', $this->kitchenA), ['verified' => 1])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue((bool) $this->settingsA->fresh()->fssai_verified);
        $this->assertNotNull($this->settingsA->fresh()->fssai_verified_at);

        // Changing FSSAI license number resets verification until admin reviews again
        $this->actingAs($this->vendorUserA);
        $this->put(route('vendor.settings.update'), [
            'food_settings_submitted' => 1,
            'payment_mode' => 'both',
            'is_accepting_food_orders' => 1,
            'fssai_license_number' => '12226026000999',
        ])->assertRedirect()->assertSessionHas('success');

        $freshSettings = $this->settingsA->fresh();
        $this->assertEquals('12226026000999', $freshSettings->fssai_license_number);
        $this->assertFalse((bool) $freshSettings->fssai_verified);
        $this->assertNull($freshSettings->fssai_verified_at);
    }
}

