<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Quote;
use App\Models\Role;
use App\Models\ServiceOrder;
use App\Models\Short;
use App\Models\ShortBoost;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use App\Models\VendorSubscription;
use App\Services\Admin\MarketplaceDiagnosticsService;
use App\Services\Catalog\ListingCompletenessService;
use App\Services\Catalog\RecommendationService;
use App\Services\Vendor\VendorWorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtremeProductionExpansionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function createVerifiedVendor(string $sector = 'all', string $slugPrefix = 'merchant'): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => ucfirst($slugPrefix) . ' Studio',
            'slug' => $slugPrefix . '-' . uniqid(),
            'status' => 'approved',
            'verification_status' => 'verified',
            'vendor_type' => $sector === 'food' ? 'food' : ($sector === 'services' ? 'service' : 'marketplace'),
            'city' => 'Jaipur',
            'approved_at' => now(),
        ]);

        VendorSetting::create([
            'vendor_id' => $vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => true,
        ]);

        $this->grantVendorTestSubscription($vendor, $sector);

        return [$user, $vendor];
    }

    public function test_workspace_blocks_malicious_deliverable_extensions_and_enforces_state_machine(): void
    {
        [$vendorUser, $vendor] = $this->createVerifiedVendor('services', 'gig-studio');
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $cat = Category::create(['name' => 'Design', 'slug' => 'design-exp', 'vertical' => 'services', 'is_active' => true]);

        $service = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'vertical' => 'services',
            'listing_type' => 'booking',
            'title' => 'Brand Identity System',
            'slug' => 'brand-identity-' . uniqid(),
            'description' => 'Complete custom brand identity package.',
            'base_price' => 500000,
            'status' => 'active',
            'total_stock' => 10,
        ]);

        $serviceOrder = ServiceOrder::create([
            'order_number' => 'SRV-TEST-' . uniqid(),
            'buyer_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'product_id' => $service->id,
            'package_tier' => 'basic',
            'package_title' => 'Basic Brand Kit',
            'amount' => 500000,
            'delivery_days' => 5,
            'revisions_allowed' => 2,
            'revisions_used' => 0,
            'status' => 'in_progress',
            'payment_status' => 'paid',
            'requirements' => 'Minimal dark theme logo',
        ]);

        // Buyer cannot approve order before delivery
        $this->actingAs($buyer)
            ->post(route('services.workspace.approve', $serviceOrder))
            ->assertSessionHas('error');
        $this->assertSame('in_progress', $serviceOrder->fresh()->status);

        // Vendor cannot upload spoofed .php payload as final deliverable
        $badFile = UploadedFile::fake()->create('shell.php', 10, 'application/x-php');
        $this->actingAs($vendorUser)
            ->post(route('services.workspace.deliver', $serviceOrder), [
                'notes' => 'Final payload',
                'delivery_files' => [$badFile],
            ])
            ->assertSessionHasErrors('delivery_files');
        $this->assertSame('in_progress', $serviceOrder->fresh()->status);

        // Clean PDF upload transitions in_progress -> delivered and notifies buyer
        $goodPdf = UploadedFile::fake()->create('brand-guidelines.pdf', 120, 'application/pdf');
        $this->actingAs($vendorUser)
            ->post(route('services.workspace.deliver', $serviceOrder), [
                'notes' => 'Attached PDF guidelines and brand manual.',
                'delivery_files' => [$goodPdf],
            ])
            ->assertSessionHas('success');

        $serviceOrder->refresh();
        $this->assertSame('delivered', $serviceOrder->status);
        $this->assertNotEmpty($serviceOrder->deliveries);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $buyer->id,
            'notifiable_type' => User::class,
        ]);

        // Buyer approves delivered work -> completed
        $this->actingAs($buyer)
            ->post(route('services.workspace.approve', $serviceOrder))
            ->assertSessionHas('success');
        $this->assertSame('completed', $serviceOrder->fresh()->status);

        // Completed order cannot raise dispute afterwards
        $this->actingAs($buyer)
            ->post(route('services.workspace.dispute', $serviceOrder), [
                'reason' => 'Late dispute check',
            ])
            ->assertSessionHas('error');
        $this->assertSame('completed', $serviceOrder->fresh()->status);
    }

    public function test_booking_slot_collision_idor_redaction_and_status_transitions(): void
    {
        [$vendorUser, $vendor] = $this->createVerifiedVendor('services', 'salon-pro');
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $intruder = User::factory()->create(['email_verified_at' => now()]);

        $cat = Category::create(['name' => 'Repair', 'slug' => 'repair-exp', 'vertical' => 'services', 'is_active' => true]);
        $service = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'vertical' => 'services',
            'listing_type' => 'booking',
            'title' => 'AC Deep Cleaning',
            'slug' => 'ac-deep-cleaning-' . uniqid(),
            'description' => 'Full split AC foam wash service.',
            'base_price' => 79900,
            'status' => 'active',
            'total_stock' => 10,
        ]);

        $date = now()->addDays(2)->toDateString();

        // Vendor cannot self-book
        $this->actingAs($vendorUser)->post(route('services.book', $service), [
            'booking_date' => $date,
            'booking_slot' => '10:00 AM',
            'customer_name' => 'Self Test',
            'customer_phone' => '9999999999',
            'service_address' => 'Vendor Studio Jaipur',
        ])->assertSessionHas('error');

        // Valid customer booking succeeds
        $res = $this->actingAs($customer)->post(route('services.book', $service), [
            'booking_date' => $date,
            'booking_slot' => '10:00 AM',
            'customer_name' => 'Aarav Mehta',
            'customer_phone' => '9876501234',
            'service_address' => '42 Secret Lane, Jaipur',
        ]);
        $booking = Booking::where('product_id', $service->id)->firstOrFail();
        $res->assertRedirect(route('bookings.show', $booking->booking_number));

        // Second booking in exact same active slot is blocked
        $this->actingAs($intruder)->post(route('services.book', $service), [
            'booking_date' => $date,
            'booking_slot' => '10:00 AM',
            'customer_name' => 'Duplicate Try',
            'customer_phone' => '9123456789',
            'service_address' => '77 Another Road, Jaipur',
        ])->assertSessionHas('error');
        $this->assertSame(1, Booking::where('product_id', $service->id)->count());

        // Signed-in unrelated user receives 403 on booking tracker
        $this->actingAs($intruder)->get(route('bookings.show', $booking->booking_number))->assertForbidden();

        // Unauthenticated viewer without booking session sees redacted contact details
        auth()->logout();
        session()->flush();
        $anonView = $this->get(route('bookings.show', $booking->booking_number));
        $anonView->assertOk();
        $anonView->assertDontSee('9876501234');
        $anonView->assertDontSee('42 Secret Lane, Jaipur');

        // Vendor confirms then completes, then cannot reopen to pending/confirmed
        $this->actingAs($vendorUser)->put(route('vendor.bookings.status', $booking), ['status' => 'confirmed'])
            ->assertSessionHas('success');
        $this->actingAs($vendorUser)->put(route('vendor.bookings.status', $booking), ['status' => 'completed'])
            ->assertSessionHas('success');
        $this->actingAs($vendorUser)->put(route('vendor.bookings.status', $booking), ['status' => 'confirmed'])
            ->assertSessionHas('error');
        $this->assertSame('completed', $booking->fresh()->status);
    }

    public function test_enquiry_accept_quote_prevents_idor_and_syncs_counters(): void
    {
        [$vendorUser, $vendor] = $this->createVerifiedVendor('enquiry', 'stone-hub');
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $intruder = User::factory()->create(['email_verified_at' => now()]);
        $cat = Category::create(['name' => 'Marble', 'slug' => 'marble-exp', 'vertical' => 'enquiry', 'is_active' => true]);

        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-SEC-' . uniqid(),
            'user_id' => $buyer->id,
            'category_id' => $cat->id,
            'title' => '1000 sqft Makrana White Slabs',
            'description' => 'Grade A polished slabs required.',
            'quantity' => '1000 sqft',
            'city' => 'Kishangarh',
            'buyer_name' => 'Buyer Corp',
            'buyer_phone' => '9988776655',
            'status' => 'open',
            'quotes_count' => 0,
        ]);

        $quote = Quote::create([
            'quote_number' => 'QT-TEST-' . uniqid(),
            'enquiry_id' => $enquiry->id,
            'vendor_id' => $vendor->id,
            'quote_amount' => 45000000,
            'notes' => 'Direct quarry rate including polish.',
            'status' => 'sent',
        ]);

        // Unrelated user gets 403 attempting to accept buyer's quote
        $this->actingAs($intruder)
            ->post(route('quotes.accept', $quote))
            ->assertForbidden();
        $this->assertSame('sent', $quote->fresh()->status);

        // Actual buyer owner accepts quote cleanly + creates audit log + notification
        $this->actingAs($buyer)
            ->post(route('quotes.accept', $quote))
            ->assertSessionHas('success');

        $this->assertSame('accepted', $quote->fresh()->status);
        $this->assertSame('closed', $enquiry->fresh()->status);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'enquiry_quote_accepted',
            'auditable_id' => $quote->id,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $vendorUser->id,
        ]);
    }

    public function test_cart_caps_variantless_stock_blocks_hidden_products_and_recalculates_b2b_tiers(): void
    {
        [$vendorUser, $vendor] = $this->createVerifiedVendor('products', 'bulk-mart');
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $cat = Category::create(['name' => 'Hardware', 'slug' => 'hardware-exp', 'vertical' => 'marketplace', 'is_active' => true]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'title' => 'Industrial Diamond Blade',
            'slug' => 'diamond-blade-' . uniqid(),
            'description' => 'Heavy duty marble cutting blade.',
            'base_price' => 100000, // ₹1000
            'total_stock' => 6,
            'status' => 'active',
            'b2b_tiers_json' => [
                ['min_qty' => 5, 'price' => 800], // ₹800 each at qty >= 5
            ],
        ]);

        $draftHidden = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'title' => 'Unpublished Secret Prototype',
            'slug' => 'secret-proto-' . uniqid(),
            'description' => 'Draft only.',
            'base_price' => 50000,
            'total_stock' => 10,
            'status' => 'draft',
        ]);

        $this->actingAs($buyer);

        // Draft product blocked from cart
        $this->postJson(route('cart.add'), ['product_id' => $draftHidden->id, 'quantity' => 1])
            ->assertStatus(422);

        // Wishlist also rejects draft product
        $this->postJson(route('wishlist.toggle'), ['product_id' => $draftHidden->id])
            ->assertStatus(422);

        // Add 3 units at regular ₹1000
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 3])
            ->assertOk();

        // Trying to add 4 more (cumulative 7 > stock 6) is rejected cleanly
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 4])
            ->assertStatus(400);

        // Add 2 more (cumulative 5 <= 6) triggers B2B tier recalculation -> ₹800 (80000 paise)
        $this->postJson(route('cart.add'), ['product_id' => $product->id, 'quantity' => 2])
            ->assertOk();

        $cart = Cart::where('user_id', $buyer->id)->firstOrFail();
        $item = $cart->items()->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(5, $item->quantity);
        $this->assertSame(80000, (int) $item->price_at_add);

        // Dropping back below tier MOQ via cart.update recalculates back to ₹1000 (100000 paise)
        $this->putJson(route('cart.update', $item), ['quantity' => 2])->assertOk();
        $this->assertSame(100000, (int) $item->fresh()->price_at_add);
    }

    public function test_shorts_ajax_blocks_anonymous_comments_and_hides_expired_boosted_vendors(): void
    {
        [$activeUser, $activeVendor] = $this->createVerifiedVendor('all', 'active-reel');
        [$expiredUser, $expiredVendor] = $this->createVerifiedVendor('all', 'expired-reel');

        // Expire second vendor's subscription
        VendorSubscription::where('vendor_id', $expiredVendor->id)->update([
            'status' => VendorSubscription::STATUS_EXPIRED,
            'ends_at' => now()->subDays(10),
            'grace_ends_at' => now()->subDays(5),
        ]);

        $cat = Category::create(['name' => 'Fashion', 'slug' => 'fashion-shorts-exp', 'is_active' => true]);

        $activeShort = Short::create([
            'vendor_id' => $activeVendor->id,
            'category_id' => $cat->id,
            'title' => 'Active Artisan Showcase',
            'slug' => 'active-artisan-' . uniqid(),
            'video_url' => 'https://example.com/a.mp4',
            'status' => 'published',
            'is_featured' => true,
        ]);

        $expiredBoostShort = Short::create([
            'vendor_id' => $expiredVendor->id,
            'category_id' => $cat->id,
            'title' => 'Expired Merchant Boosted Clip',
            'slug' => 'expired-boost-' . uniqid(),
            'video_url' => 'https://example.com/b.mp4',
            'status' => 'published',
            'is_featured' => true,
            'is_boosted' => true,
            'is_manual_boost' => true,
        ]);

        // Unauthenticated comment MUST return 401 (never impute to user_id=1)
        $this->postJson('/shorts/ajax', [
            'action' => 'zsu_post_comment',
            'short_id' => $activeShort->id,
            'content' => 'Nice saree!',
        ])->assertStatus(401);
        $this->assertDatabaseCount('short_comments', 0);

        // Boosted/featured/search must NOT expose expired vendor's short
        $feedRes = $this->postJson('/shorts/ajax', [
            'action' => 'zsu_get_shorts',
            'filter' => 'popular',
            'page' => 1,
        ])->assertOk();
        $ids = collect($feedRes->json('shorts'))->pluck('id')->all();
        $this->assertContains($activeShort->id, $ids);
        $this->assertNotContains($expiredBoostShort->id, $ids);

        $featuredRes = $this->postJson('/shorts/ajax', ['action' => 'zsu_get_featured'])->assertOk();
        $featIds = collect($featuredRes->json('shorts'))->pluck('id')->all();
        $this->assertNotContains($expiredBoostShort->id, $featIds);
    }

    public function test_recommendations_product_duplication_completeness_workspace_and_reconcile(): void
    {
        [$vendorUser, $vendor] = $this->createVerifiedVendor('all', 'os-atelier');
        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $cat = Category::create(['name' => 'Decor', 'slug' => 'decor-exp', 'vertical' => 'marketplace', 'is_active' => true]);

        $p1 = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'title' => 'Makrana Marble Urli Bowl',
            'slug' => 'makrana-urli-' . uniqid(),
            'description' => str_repeat('Hand-carved Makrana marble bowl. ', 3),
            'base_price' => 240000,
            'total_stock' => 12,
            'sku' => 'URLI-MAIN-001',
            'status' => 'active',
        ]);
        ProductVariant::create(['product_id' => $p1->id, 'sku' => 'URLI-MAIN-001-STD', 'stock' => 12, 'is_active' => true]);
        ProductImage::create(['product_id' => $p1->id, 'path' => 'products/urli.jpg', 'alt_text' => 'Makrana Marble Urli Bowl', 'sort_order' => 1]);

        $p2 = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'title' => 'Alabaster Candle Stand',
            'slug' => 'alabaster-stand-' . uniqid(),
            'description' => 'Handcrafted candle pedestal.',
            'base_price' => 95000,
            'total_stock' => 3, // Low stock -> triggers task queue item
            'status' => 'active',
        ]);

        // Historical real paid order containing both p1 and p2
        $pastOrder = Order::create([
            'order_number' => 'K71-PAIR-' . strtoupper(substr(uniqid(), -8)),
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'status' => 'pending',
            'payment_method' => 'upi',
            'payment_status' => 'paid',
            'subtotal' => 335000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'total_amount' => 335000,
            'item_count' => 2,
            'address_snapshot' => [
                'name' => $buyer->name,
                'phone' => '9876543210',
                'address_line_1' => '123 Heritage Lane',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302001',
            ],
        ]);
        OrderItem::create([
            'order_id' => $pastOrder->id,
            'product_id' => $p1->id,
            'quantity' => 1,
            'unit_price' => 240000,
            'total_price' => 240000,
            'tax_amount' => 0,
            'gst_rate' => 0,
            'snapshot' => ['title' => $p1->title],
        ]);
        OrderItem::create([
            'order_id' => $pastOrder->id,
            'product_id' => $p2->id,
            'quantity' => 1,
            'unit_price' => 95000,
            'total_price' => 95000,
            'tax_amount' => 0,
            'gst_rate' => 0,
            'snapshot' => ['title' => $p2->title],
        ]);

        // Visit p1 then p2 as buyer -> RecommendationService populates Recently Viewed + Bought Together + Related
        $this->actingAs($buyer)->get(route('product.show', $p1->slug))->assertOk();
        $this->actingAs($buyer)->get(route('product.show', $p2->slug))->assertOk();

        $recService = app(RecommendationService::class);
        $this->assertTrue($recService->getFrequentlyBoughtTogether($p1, 4)->contains('id', $p2->id));
        $this->assertTrue($recService->getRelatedProducts($p1, 4)->contains('id', $p2->id));
        $this->assertTrue($recService->getRecentlyViewed($p2->id, 4)->contains('id', $p1->id));

        // Completeness scoring distinguishes complete vs sparse listing
        $compService = app(ListingCompletenessService::class);
        $this->assertSame(100, $compService->evaluate($p1)['score']);
        $this->assertLessThan(85, $compService->evaluate($p2)['score']);

        // Repeated product duplication generates unique SKUs (-COPY, -COPY-2) cleanly
        $this->actingAs($vendorUser);
        $this->post(route('vendor.products.duplicate', $p1))->assertRedirect();
        $this->post(route('vendor.products.duplicate', $p1))->assertRedirect();

        $copies = Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->where('title', 'like', 'Makrana Marble Urli Bowl (Copy)%')->get();
        $this->assertCount(2, $copies);
        $this->assertCount(2, $copies->pluck('sku')->unique());

        // Dashboard loads with Action Queue & Timeline and OrderController blocks reopening cancelled orders
        $dash = $this->get(route('vendor.dashboard'));
        $dash->assertOk();
        $dash->assertSee('Priority Action Queue');
        $dash->assertSee('Store Activity Timeline');
        $dash->assertSee('Fulfill Active Orders');

        $this->put(route('vendor.orders.status', $pastOrder), ['status' => 'cancelled'])->assertSessionHas('success');
        $this->put(route('vendor.orders.status', $pastOrder), ['status' => 'processing'])->assertSessionHas('error');
        $this->assertSame('cancelled', $pastOrder->fresh()->status);

        // Reconciliation repairs enquiry quote counter & expired short boost deterministically
        $catEnq = Category::create(['name' => 'Bulk', 'slug' => 'bulk-exp', 'vertical' => 'enquiry', 'is_active' => true]);
        $driftedEnquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-DRIFT-' . uniqid(),
            'category_id' => $catEnq->id,
            'title' => 'Granite Tiles',
            'description' => '100 boxes',
            'quantity' => '100 boxes',
            'city' => 'Udaipur',
            'buyer_name' => 'Test Buyer',
            'buyer_phone' => '9876543210',
            'status' => 'open',
            'quotes_count' => 9, // Deliberate drift (0 actual quotes)
        ]);

        $short = Short::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Overdue Boosted Short',
            'slug' => 'overdue-short-' . uniqid(),
            'video_url' => 'https://example.com/s.mp4',
            'status' => 'published',
            'is_boosted' => true,
            'boost_type' => 'prime',
        ]);
        $boost = ShortBoost::create([
            'short_id' => $short->id,
            'vendor_id' => $vendor->id,
            'boost_type' => 'prime',
            'coins_spent' => 499,
            'starts_at' => now()->subDays(2),
            'ends_at' => now()->subHour(),
            'status' => 'active',
        ]);

        $preReport = app(MarketplaceDiagnosticsService::class)->inspect(false);
        $this->assertSame(1, $preReport['checks']['mismatched_enquiry_quotes_count']);
        $this->assertGreaterThanOrEqual(1, $preReport['checks']['overdue_active_short_boosts']);

        $exit = Artisan::call('k71:reconcile --fix-deterministic');
        $this->assertSame(0, $exit);
        $this->assertSame(0, (int) $driftedEnquiry->fresh()->quotes_count);
        $this->assertSame('expired', $boost->fresh()->status);
        $this->assertFalse((bool) $short->fresh()->is_boosted);

        // Non-admin blocked from admin diagnostics; admin permitted
        $this->actingAs($buyer)->get(route('admin.diagnostics'))->assertRedirect(route('home'));
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);
        $this->actingAs($admin)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.diagnostics'))
            ->assertOk()
            ->assertSee('Platform Consistency Monitor');
    }
}
