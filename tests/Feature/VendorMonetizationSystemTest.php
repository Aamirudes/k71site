<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\OnboardingLead;
use App\Models\Order;
use App\Models\OrderCommission;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Role;
use App\Models\SubscriptionNotification;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use App\Models\VendorSubscription;
use App\Services\Monetization\CommissionService;
use App\Services\Monetization\EntitlementService;
use App\Services\Monetization\SubscriptionLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorMonetizationSystemTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $vendorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $this->vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Customer']);

        $this->admin = User::factory()->create([
            'email' => 'hashir.admin@k71.test',
            'email_verified_at' => now(),
        ]);
        $this->admin->roles()->syncWithoutDetaching([$adminRole->id]);
    }

    private function createRawVendor(string $type = 'clothing', array $overrides = []): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->syncWithoutDetaching([$this->vendorRole->id]);

        $slug = 'vendor-' . uniqid();
        $vendor = Vendor::create(array_merge([
            'user_id' => $user->id,
            'shop_name' => 'Store ' . strtoupper($slug),
            'slug' => $slug,
            'vendor_type' => $type,
            'status' => 'pending',
            'verification_status' => 'pending_review',
            'phone' => '9876500001',
            'email' => $slug . '@example.com',
            'address' => '10 Main Market',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ], $overrides));

        VendorSetting::create([
            'vendor_id' => $vendor->id,
            'cod_enabled' => true,
            'upi_enabled' => true,
            'upi_id' => $slug . '@okaxis',
        ]);

        return [$user, $vendor];
    }

    public function test_01_new_vendor_registration_stays_pending_and_never_auto_approves_or_grants_access(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user)->post('/sell', [
            'shop_name' => 'New Unverified Artisan',
            'slug' => 'new-unverified-artisan',
            'vendor_type' => 'clothing',
            'phone' => '9876511122',
        ])->assertRedirect(route('vendor.dashboard'));

        $vendor = Vendor::where('slug', 'new-unverified-artisan')->firstOrFail();
        $this->assertSame('pending', $vendor->status);
        $this->assertSame('pending_review', $vendor->verification_status);
        $this->assertSame(0, VendorSubscription::where('vendor_id', $vendor->id)->count());

        // Attempting to hit product upload or storefront publish redirects cleanly (EnsureVendorApproved blocks pending)
        $cat = Category::create(['name' => 'Apparel', 'slug' => 'apparel-test', 'vertical' => 'ecommerce', 'is_active' => true]);
        $this->actingAs($user)->post(route('vendor.products.store'), [
            'title' => 'Blocked Silk Scarf',
            'category_id' => $cat->id,
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Should be blocked while pending verification.',
            'base_price' => 999,
            'gst_rate' => 5,
            'total_stock' => 5,
            'status' => 'active',
        ])->assertRedirect(route('vendor.dashboard'));

        $this->assertSame(0, Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->count());

        // Public storefront returns 403 suspended/unavailable page instead of leaking store
        auth()->logout();
        $this->get('/s/' . $vendor->slug)->assertStatus(403);
    }

    public function test_02_verified_vendor_without_active_subscription_cannot_publish_or_appear_publicly(): void
    {
        [$user, $vendor] = $this->createRawVendor('clothing');
        app(SubscriptionLifecycleService::class)->updateVerificationStatus($vendor, 'verified', $this->admin->id, 'Verified via WhatsApp');
        $vendor->refresh();

        $this->assertSame('verified', $vendor->verification_status);
        $this->assertSame('approved', $vendor->status);

        $cat = Category::create(['name' => 'Marble', 'slug' => 'marble-02', 'vertical' => 'ecommerce', 'is_active' => true]);

        // Pre-seed a product to confirm public visibility hides it when no active subscription exists
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Hidden Until Subscribed Vase',
            'slug' => 'hidden-until-subscribed-vase',
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Needs active subscription.',
            'base_price' => 150000,
            'total_stock' => 3,
            'status' => 'active',
        ]);

        // Blocked from publishing another product without subscription
        $this->actingAs($user)->post(route('vendor.products.store'), [
            'title' => 'Attempted Paid Product',
            'category_id' => $cat->id,
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Must fail without active subscription.',
            'base_price' => 500,
            'gst_rate' => 5,
            'total_stock' => 5,
            'status' => 'active',
        ])->assertStatus(302);
        $this->assertSame(1, Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->count());

        // Public catalog, PDP & storefront hide unsubscribed vendor
        auth()->logout();
        $this->get('/s/' . $vendor->slug)->assertStatus(403);
        $this->get('/product/' . $product->slug)->assertStatus(404);
        $this->get('/catalog?q=Hidden+Until+Subscribed')->assertDontSee('Hidden Until Subscribed Vase');
    }

    public function test_03_04_05_manual_workflow_assign_pending_record_payment_then_explicit_activate(): void
    {
        [$user, $vendor] = $this->createRawVendor('clothing');
        $this->actingAs($this->admin)->post(route('admin.vendor_management.verification.update', $vendor), [
            'verification_status' => 'verified',
            'verification_notes' => 'GST and WhatsApp KYC confirmed.',
        ])->assertStatus(302);

        $lifecycle = app(SubscriptionLifecycleService::class);
        $plan = $lifecycle->createOrUpdatePlan([
            'name' => 'Retail Growth',
            'sector_key' => 'products',
            'price_amount' => 149900,
            'duration_days' => 30,
            'entitlements' => [
                'max_active_products' => 15,
                'max_images_per_product' => 5,
                'allow_coupons' => true,
                'allow_shorts' => true,
                'allow_storefront_customization' => true,
                'allow_kinetic_scroll' => false,
            ],
        ], null, $this->admin->id);

        // 03: Assigning plan creates PENDING_ACTIVATION with zero active access
        $this->actingAs($this->admin)->post(route('admin.vendor_management.assign_plan', $vendor), [
            'plan_id' => $plan->id,
            'sector_key' => 'products',
            'duration_days' => 30,
            'internal_notes' => 'SECRET_WHATSAPP_BARGAIN_NOTE_ONLY_FOR_ADMIN',
            'admin_reason' => 'Assigned Retail Growth after WhatsApp call',
        ])->assertStatus(302);

        $sub = VendorSubscription::where('vendor_id', $vendor->id)->latest('id')->firstOrFail();
        $this->assertSame(VendorSubscription::STATUS_PENDING_ACTIVATION, $sub->status);
        $this->assertNull(app(EntitlementService::class)->resolveEffectiveSubscription($vendor->fresh(), 'products'));

        // 04: Recording external payment DOES NOT auto-activate!
        $this->actingAs($this->admin)->post(route('admin.vendor_management.subscriptions.payment', $sub), [
            'external_payment_received' => '1',
            'external_payment_amount_rupees' => '1499',
            'external_payment_method' => 'UPI',
            'external_payment_ref' => 'UTR4455667788',
        ])->assertStatus(302);

        $sub->refresh();
        $this->assertTrue($sub->external_payment_received);
        $this->assertSame(VendorSubscription::STATUS_PENDING_ACTIVATION, $sub->status);
        $this->assertNull(app(EntitlementService::class)->resolveEffectiveSubscription($vendor->fresh(), 'products'));

        // 05: Explicit admin activation confirmation grants exact snapshot entitlements and writes audit logs
        $this->actingAs($this->admin)->post(route('admin.vendor_management.subscriptions.activate', $sub), [
            'confirm_activation' => '1',
            'duration_days' => 30,
            'reason' => 'Confirmed payment in bank and WhatsApp',
        ])->assertStatus(302);

        $sub->refresh();
        $this->assertSame(VendorSubscription::STATUS_ACTIVE, $sub->status);
        $this->assertSame($this->admin->id, $sub->activated_by);
        $this->assertNotNull(app(EntitlementService::class)->resolveEffectiveSubscription($vendor->fresh(), 'products'));
        $this->assertDatabaseHas('audit_logs', [
            'vendor_id' => $vendor->id,
            'action' => 'subscription_activated',
        ]);
    }

    public function test_06_07_server_side_quota_and_feature_gating_block_disallowed_requests(): void
    {
        [$user, $vendor] = $this->createRawVendor('clothing');
        $this->grantVendorTestSubscription($vendor, 'products', [
            'max_active_products' => 1,
            'max_images_per_product' => 2,
            'allow_coupons' => false,
            'allow_shorts' => false,
            'allow_kinetic_scroll' => false,
            'allow_storefront_customization' => false,
        ]);

        $cat = Category::create(['name' => 'Decor', 'slug' => 'decor-06', 'vertical' => 'ecommerce', 'is_active' => true]);

        // 1st product succeeds (quota = 1)
        $this->actingAs($user)->post(route('vendor.products.store'), [
            'title' => 'Allowed First Product',
            'category_id' => $cat->id,
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Fits inside quota 1.',
            'base_price' => 500,
            'gst_rate' => 5,
            'total_stock' => 5,
            'status' => 'active',
        ])->assertRedirect(route('vendor.products.index'));

        // 2nd active product exceeds quota -> blocked!
        $this->actingAs($user)->post(route('vendor.products.store'), [
            'title' => 'Quota Exceeded Product',
            'category_id' => $cat->id,
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Must be blocked because max_active_products=1.',
            'base_price' => 600,
            'gst_rate' => 5,
            'total_stock' => 5,
            'status' => 'active',
        ])->assertSessionHas('error');
        $this->assertSame(1, Product::withoutGlobalScopes()->where('vendor_id', $vendor->id)->count());

        // Disallowed coupon creation blocked (403 JSON / redirect with error on web)
        $this->actingAs($user)->postJson(route('vendor.coupons.store'), [
            'code' => 'BLOCKED20',
            'type' => 'percentage',
            'value' => 20,
            'starts_at' => now()->toDateString(),
            'expires_at' => now()->addDays(5)->toDateString(),
            'per_customer_limit' => 1,
        ])->assertStatus(403);

        // Disallowed shorts upload blocked (403 JSON)
        $this->actingAs($user)->postJson(route('vendor.shorts.save'), [
            'title' => 'Blocked Short',
            'video_url' => 'https://example.com/v.mp4',
        ])->assertStatus(403);

        // Disallowed storefront preset activation blocked (403 JSON)
        $this->actingAs($user)->postJson(route('vendor.design.activate'), [
            'preset' => 'velora',
        ])->assertStatus(403);

        // Disallowed kinetic scroll blocked
        $this->actingAs($user)->postJson(route('vendor.design.activate'), [
            'preset' => 'kinetic',
        ])->assertStatus(403);
    }

    public function test_08_editing_plan_creates_new_version_without_mutating_existing_subscriber_snapshot(): void
    {
        [, $vendor] = $this->createRawVendor('clothing');
        app(SubscriptionLifecycleService::class)->updateVerificationStatus($vendor, 'verified', $this->admin->id);

        $lifecycle = app(SubscriptionLifecycleService::class);
        $plan = $lifecycle->createOrUpdatePlan([
            'name' => 'Pro Artisan',
            'sector_key' => 'products',
            'price_amount' => 99900,
            'duration_days' => 30,
            'entitlements' => ['max_active_products' => 10, 'allow_coupons' => true],
        ], null, $this->admin->id);

        $sub = $lifecycle->assignSubscription($vendor, $plan, 'products', [], $this->admin->id);
        $sub = $lifecycle->activateSubscription($sub, ['duration_days' => 30], $this->admin->id);

        $this->assertSame(1, $sub->plan_version_number);
        $this->assertSame(10, $sub->entitlements_snapshot['max_active_products']);

        // Admin edits plan -> v2 with limit 50
        $updatedPlan = $lifecycle->createOrUpdatePlan([
            'name' => 'Pro Artisan',
            'sector_key' => 'products',
            'price_amount' => 199900,
            'duration_days' => 30,
            'entitlements' => ['max_active_products' => 50, 'allow_coupons' => true],
        ], $plan, $this->admin->id, 'Bumped quota in v2');

        $this->assertSame(2, $updatedPlan->current_version);
        $sub->refresh();
        // Existing subscriber keeps immutable v1 snapshot!
        $this->assertSame(1, $sub->plan_version_number);
        $this->assertSame(10, $sub->entitlements_snapshot['max_active_products']);

        // Admin explicitly upgrades subscriber to v2 via apply-latest-version route
        $this->actingAs($this->admin)->post(route('admin.vendor_management.subscriptions.apply_version', $sub))
            ->assertStatus(302);

        $sub->refresh();
        $this->assertSame(2, $sub->plan_version_number);
        $this->assertSame(50, $sub->entitlements_snapshot['max_active_products']);
    }

    public function test_09_10_11_12_expiry_grace_deduplication_jit_guard_and_visibility_restoration(): void
    {
        [, $vendor] = $this->createRawVendor('clothing');
        $cat = Category::create(['name' => 'Textiles', 'slug' => 'textiles-09', 'vertical' => 'ecommerce', 'is_active' => true]);

        // 09: Overdue active subscription -> grace_period -> expired on command runs
        $sub = $this->grantVendorTestSubscription($vendor, 'products', [], [
            'starts_at' => now()->subDays(31),
            'ends_at' => now()->subHour(),
            'expiry_policy_snapshot' => [
                'hide_storefront' => true,
                'hide_listings' => true,
                'block_new_listings' => true,
                'block_editing' => true,
                'grace_days' => 2,
                'grace_keep_visible' => true,
                'grace_keep_features' => false,
            ],
            'grace_keep_visible' => true,
            'grace_keep_features' => false,
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Blockprint Jaipur Quilt',
            'slug' => 'blockprint-jaipur-quilt',
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Handcrafted quilt.',
            'base_price' => 250000,
            'total_stock' => 4,
            'status' => 'active',
        ]);

        // Run cron 1 -> enters grace_period
        $this->artisan('subscriptions:process-expiry')->assertExitCode(0);
        $sub->refresh();
        $this->assertSame(VendorSubscription::STATUS_GRACE_PERIOD, $sub->status);
        $notifCountAfterFirstGraceRun = SubscriptionNotification::where('vendor_subscription_id', $sub->id)->count();
        $this->assertSame(1, $notifCountAfterFirstGraceRun);

        // During grace with grace_keep_visible=true, storefront remains visible while grace_ends_at is future
        $this->get('/s/' . $vendor->slug)->assertStatus(200);

        // 10: Upcoming reminder deduplication
        [, $soonVendor] = $this->createRawVendor('clothing');
        $soonSub = $this->grantVendorTestSubscription($soonVendor, 'products', [], [
            'ends_at' => now()->addDays(3)->startOfDay()->addHours(12),
            'reminder_days_before' => [3],
            'auto_reminder_enabled' => true,
        ]);
        $this->artisan('subscriptions:process-expiry')->assertExitCode(0);
        $this->artisan('subscriptions:process-expiry')->assertExitCode(0); // Repeat run must NOT duplicate
        $this->assertSame(
            1,
            SubscriptionNotification::where('vendor_subscription_id', $soonSub->id)
                ->where('event_key', 'expiry_reminder')
                ->count()
        );

        // Force grace period end into the past and verify cron expires + hides storefront
        $sub->update(['grace_ends_at' => now()->subMinute()]);
        $this->artisan('subscriptions:process-expiry')->assertExitCode(0);
        $sub->refresh();
        $this->assertSame(VendorSubscription::STATUS_EXPIRED, $sub->status);

        // 12: Expired vendor vanishes from public views
        $this->get('/s/' . $vendor->slug)->assertStatus(403);
        $this->get('/product/' . $product->slug)->assertStatus(404);
        $this->get('/catalog?q=Blockprint')->assertDontSee('Blockprint Jaipur Quilt');

        // 11: Request-time JIT expiry guard catches overdue active subscription even without cron
        [, $jitVendor] = $this->createRawVendor('clothing');
        $jitSub = $this->grantVendorTestSubscription($jitVendor, 'products', [], [
            'ends_at' => now()->subDays(5),
            'expiry_policy_snapshot' => ['hide_storefront' => true, 'hide_listings' => true, 'grace_days' => 0],
        ]);
        $this->get('/s/' . $jitVendor->slug)->assertStatus(403);
        $this->assertSame(VendorSubscription::STATUS_EXPIRED, $jitSub->fresh()->status);

        // 12b: Admin manual renewal immediately restores public visibility!
        $this->actingAs($this->admin)->post(route('admin.vendor_management.subscriptions.renew', $sub), [
            'duration_days' => 30,
            'reason' => 'Renewed after WhatsApp transfer',
        ])->assertStatus(302);
        $this->assertSame(VendorSubscription::STATUS_ACTIVE, $sub->fresh()->status);
        $this->get('/s/' . $vendor->slug)->assertStatus(200);
        $this->get('/product/' . $product->slug)->assertStatus(200);
    }

    public function test_13_c2c_personal_used_listings_remain_visible_while_expired_commercial_used_vendor_hidden(): void
    {
        $usedCat = Category::create(['name' => 'Used Furniture', 'slug' => 'used-furniture-13', 'vertical' => 'used', 'is_active' => true]);

        // Individual C2C seller (no commercial vendor_id)
        $person = User::factory()->create();
        Product::create([
            'user_id' => $person->id,
            'vendor_id' => null,
            'category_id' => $usedCat->id,
            'title' => 'Personal PreOwned Teak Table',
            'slug' => 'personal-teak-table',
            'vertical' => 'used',
            'listing_type' => 'classified',
            'used_status' => 'active',
            'status' => 'active',
            'description' => 'Personal sale from home.',
            'base_price' => 450000,
            'city' => 'Jaipur',
            'published_at' => now(),
        ]);

        // Commercial 'used' dealer whose subscription expired
        [, $dealer] = $this->createRawVendor('used');
        $this->grantVendorTestSubscription($dealer, 'used', [], [
            'status' => VendorSubscription::STATUS_EXPIRED,
            'ends_at' => now()->subDays(2),
            'expired_at' => now()->subDays(2),
        ]);
        Product::create([
            'vendor_id' => $dealer->id,
            'user_id' => $dealer->user_id,
            'category_id' => $usedCat->id,
            'title' => 'Expired Commercial Dealer Sofa',
            'slug' => 'expired-commercial-dealer-sofa',
            'vertical' => 'used',
            'listing_type' => 'classified',
            'used_status' => 'active',
            'status' => 'active',
            'description' => 'Commercial dealer item.',
            'base_price' => 1500000,
            'city' => 'Jaipur',
            'published_at' => now(),
        ]);

        $res = $this->get('/used');
        $res->assertOk();
        $res->assertSee('Personal PreOwned Teak Table');
        $res->assertDontSee('Expired Commercial Dealer Sofa');
        $this->get('/used/personal-teak-table')->assertOk();
        $this->get('/used/expired-commercial-dealer-sofa')->assertStatus(404);
    }

    public function test_14_multi_sector_vendor_independence_food_active_while_products_expired(): void
    {
        [$user, $vendor] = $this->createRawVendor('food', ['operating_sectors' => ['food', 'products']]);

        // Active food subscription, EXPIRED products subscription
        $this->grantVendorTestSubscription($vendor, 'food');
        $this->grantVendorTestSubscription($vendor, 'products', [], [
            'status' => VendorSubscription::STATUS_EXPIRED,
            'ends_at' => now()->subDay(),
            'expired_at' => now()->subDay(),
        ]);

        $foodCat = Category::create(['name' => 'Thali', 'slug' => 'thali-14', 'vertical' => 'food', 'is_active' => true]);
        $retailCat = Category::create(['name' => 'Merch', 'slug' => 'merch-14', 'vertical' => 'ecommerce', 'is_active' => true]);

        Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $foodCat->id,
            'title' => 'Active Dal Baati Thali',
            'slug' => 'active-dal-baati-thali',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'description' => 'Fresh Rajasthani thali.',
            'base_price' => 25000,
            'total_stock' => 20,
            'status' => 'active',
        ]);

        Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $retailCat->id,
            'title' => 'Expired Retail Spice Jar',
            'slug' => 'expired-retail-spice-jar',
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Retail packaged masala.',
            'base_price' => 35000,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        // Public Food stays visible; Public Retail PDP is hidden; writing retail product blocked while writing food allowed
        $this->get('/food?city=Jaipur')->assertOk()->assertSee('Active Dal Baati Thali');
        $this->get('/product/expired-retail-spice-jar')->assertStatus(404);
        $this->assertTrue(app(EntitlementService::class)->authorizeSectorWrite($vendor, 'food')['allowed']);
        $this->assertFalse(app(EntitlementService::class)->authorizeSectorWrite($vendor, 'products')['allowed']);
    }

    public function test_15_16_17_18_commission_hierarchy_idempotency_reversal_and_snapshot_immutability(): void
    {
        [, $vendor] = $this->createRawVendor('clothing');
        $cat = Category::create(['name' => 'Sculptures', 'slug' => 'sculptures-15', 'vertical' => 'ecommerce', 'is_active' => true]);

        // Set up hierarchy rules bottom-up: Platform (5%) < Sector (6%) < Plan (7%) < Category (9%) < Vendor (11%)
        CommissionRule::where('scope', 'platform')->update(['rate_percent' => 5.00]);
        CommissionRule::create(['name' => 'Products Sector 6%', 'scope' => 'sector', 'sector_key' => 'products', 'rate_percent' => 6.00, 'is_active' => true]);

        $plan = SubscriptionPlan::create([
            'code' => 'COMM_TEST', 'name' => 'Comm Plan', 'slug' => 'comm-plan-test', 'sector_key' => 'products',
            'applicable_sectors' => ['products'], 'price_amount' => 10000, 'duration_days' => 30, 'status' => 'active', 'is_active' => true,
        ]);
        CommissionRule::create(['name' => 'Plan 7%', 'scope' => 'plan', 'plan_id' => $plan->id, 'rate_percent' => 7.00, 'is_active' => true]);
        CommissionRule::create(['name' => 'Cat 9%', 'scope' => 'category', 'category_id' => $cat->id, 'rate_percent' => 9.00, 'is_active' => true]);
        $vendorRule = CommissionRule::create(['name' => 'Vendor 11%', 'scope' => 'vendor', 'vendor_id' => $vendor->id, 'rate_percent' => 11.00, 'is_active' => true]);

        $sub = $this->grantVendorTestSubscription($vendor, 'products', [], ['plan_id' => $plan->id]);

        $commService = app(CommissionService::class);
        // 15: Verify precedence: Transaction Override (15%) > Vendor Override > Vendor Rule (11%) > Category (9%) > Plan (7%) > Sector (6%) > Platform (5%)
        $this->assertSame(15.0, $commService->resolveEffectiveRule($vendor, 'products', $cat->id, $sub, ['rate_percent' => 15.0])['rate_percent']);
        $this->assertSame('vendor', $commService->resolveEffectiveRule($vendor, 'products', $cat->id, $sub)['source']);
        $this->assertSame(11.0, $commService->resolveEffectiveRule($vendor, 'products', $cat->id, $sub)['rate_percent']);

        $vendorRule->update(['is_active' => false]);
        $this->assertSame('category', $commService->resolveEffectiveRule($vendor, 'products', $cat->id, $sub)['source']);
        $this->assertSame(9.0, $commService->resolveEffectiveRule($vendor, 'products', $cat->id, $sub)['rate_percent']);

        $product = Product::create([
            'vendor_id' => $vendor->id, 'category_id' => $cat->id, 'title' => 'Carved Statue', 'slug' => 'carved-statue-15',
            'vertical' => 'ecommerce', 'listing_type' => 'purchase', 'description' => 'Stone', 'base_price' => 100000, 'total_stock' => 5, 'status' => 'active',
        ]);
        $buyer = User::factory()->create();
        $order = Order::create([
            'order_number' => 'K71-COMM-001', 'user_id' => $buyer->id, 'vendor_id' => $vendor->id,
            'subtotal' => 100000, 'shipping_amount' => 5000, 'discount_amount' => 0, 'tax_amount' => 0, 'total_amount' => 105000, 'item_count' => 1,
            'status' => 'confirmed', 'payment_status' => 'paid', 'payment_method' => 'online',
            'address_snapshot' => ['name' => 'Buyer Name', 'phone' => '9876543210', 'city' => 'Jaipur', 'pincode' => '302001'],
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id,
            'quantity' => 1, 'unit_price' => 100000, 'total_price' => 100000, 'tax_amount' => 0, 'gst_rate' => 0,
            'snapshot' => ['title' => 'Carved Statue', 'price' => 100000],
        ]);

        // 16: Idempotent duplicate calls create ONLY 1 charge row (9% of 100000 = 9000)
        $c1 = $commService->recordOrderCommission($order);
        $c2 = $commService->recordOrderCommission($order);
        $this->assertSame($c1->id, $c2->id);
        $this->assertSame(9000, $c1->calculated_commission_paise);
        $this->assertSame(91000, $c1->vendor_net_paise);
        $this->assertSame(1, OrderCommission::where('transaction_id', $order->id)->where('event_type', 'charge')->count());

        // 18: Changing rule rates later NEVER mutates historical snapshots!
        CommissionRule::where('scope', 'category')->update(['rate_percent' => 22.00]);
        $this->assertEquals(9.0, (float) $c1->fresh()->rate_percent);
        $this->assertSame(9000, $c1->fresh()->calculated_commission_paise);

        // 17: Cancellation creates clean negative reversal row without overwriting original charge snapshot
        $rev = $commService->reverseOrderCommission($order, 'Customer cancelled order', $this->admin->id);
        $revDup = $commService->reverseOrderCommission($order, 'Duplicate webhook', $this->admin->id);
        $this->assertSame($rev->id, $revDup->id);
        $this->assertSame('reversal', $rev->event_type);
        $this->assertSame(-9000, $rev->calculated_commission_paise);
        $this->assertSame(9000, $c1->fresh()->calculated_commission_paise);
    }

    public function test_19_vendor_subscription_portal_hides_internal_notes_allows_renewal_request_and_has_no_checkout(): void
    {
        [$user, $vendor] = $this->createRawVendor('clothing');
        app(SubscriptionLifecycleService::class)->updateVerificationStatus($vendor, 'verified', $this->admin->id, 'INTERNAL_ADMIN_KYC_MEMO_DO_NOT_LEAK');

        $sub = $this->grantVendorTestSubscription($vendor, 'products', [], [
            'internal_notes' => 'TOP_SECRET_DISCOUNT_BARGAIN_NOTE',
        ]);

        $res = $this->actingAs($user)->get(route('vendor.subscription.index'));
        $res->assertOk();
        $res->assertSee($sub->plan_name_snapshot);
        $res->assertDontSee('TOP_SECRET_DISCOUNT_BARGAIN_NOTE');
        $res->assertDontSee('INTERNAL_ADMIN_KYC_MEMO_DO_NOT_LEAK');

        // Vendor submits manual offline renewal/upgrade request
        $this->actingAs($user)->post(route('vendor.subscription.request_renewal'), [
            'sector_key' => 'products',
            'message' => 'Please renew for 3 months via WhatsApp UPI.',
        ])->assertRedirect();

        $this->assertDatabaseHas('subscription_renewal_requests', [
            'vendor_id' => $vendor->id,
            'sector_key' => 'products',
            'status' => 'pending',
        ]);
    }

    public function test_20_onboarding_leads_workflow_and_admin_reports_csv_exports(): void
    {
        [, $vendor] = $this->createRawVendor('clothing');
        $this->grantVendorTestSubscription($vendor, 'products');

        $this->actingAs($this->admin)->post(route('admin.vendor_management.leads.store'), [
            'business_name' => 'Rajasthani Brass Craft',
            'contact_person' => 'Mahesh Soni',
            'whatsapp_number' => '+919829098290',
            'city' => 'Jaipur',
            'sector_key' => 'products',
            'status' => 'contacted',
        ])->assertRedirect();

        $lead = OnboardingLead::where('business_name', 'Rajasthani Brass Craft')->firstOrFail();

        $this->actingAs($this->admin)->put(route('admin.vendor_management.leads.update', $lead), [
            'status' => 'converted',
            'linked_vendor_id' => $vendor->id,
            'notes' => 'Onboarded cleanly.',
        ])->assertRedirect();

        $this->assertSame($vendor->id, $lead->fresh()->linked_vendor_id);

        // All admin Vendor Management workspace & CSV export routes return 200 cleanly
        $this->actingAs($this->admin)->get(route('admin.vendor_management.index'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.show', $vendor))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.leads'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.plans'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.commissions'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.history'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.automation'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.reports'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.reports.export'))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.vendor_management.commissions.export'))->assertOk();
    }
}
