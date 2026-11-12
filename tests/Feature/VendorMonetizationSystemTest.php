<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\Role;
use App\Models\Short;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionRenewalRequest;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use App\Models\VendorSubscription;
use App\Services\Monetization\CommissionService;
use App\Services\Monetization\SectorRegistryService;
use App\Services\Monetization\SubscriptionLifecycleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VendorMonetizationSystemTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Master Admin', 'description' => 'Full Access']
        );

        $admin = User::factory()->create([
            'email' => 'admin-monetization@marblemandi.org',
            'email_verified_at' => now(),
        ]);
        $admin->roles()->syncWithoutDetaching([$role->id]);

        return $admin;
    }

    private function adminSession(): array
    {
        return [
            'admin_authenticated' => true,
            'admin_2fa_verified' => true,
            'admin_2fa_passed' => true,
        ];
    }

    private function createVendorAccount(
        string $slug,
        string $vendorType = 'electronics',
        ?array $operatingSectors = null,
        string $verificationStatus = 'verified'
    ): array {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => ucfirst(str_replace('-', ' ', $slug)) . ' Store',
            'slug' => $slug,
            'vendor_type' => $vendorType,
            'primary_sector' => $operatingSectors[0] ?? app(SectorRegistryService::class)->normalizeSectorKey($vendorType),
            'operating_sectors' => $operatingSectors,
            'status' => $verificationStatus === 'verified' ? 'approved' : 'pending',
            'verification_status' => $verificationStatus,
            'verified_at' => $verificationStatus === 'verified' ? now() : null,
            'approved_at' => $verificationStatus === 'verified' ? now() : null,
            'contact_phone' => '9876543210',
            'whatsapp_number' => '9876543210',
        ]);

        VendorSetting::create([
            'vendor_id' => $vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => true,
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 5000,
        ]);

        return [$user, $vendor];
    }

    public function test_admin_sidebar_has_single_plans_and_subscriptions_entry_and_four_tabs_render(): void
    {
        $admin = $this->createAdminUser();

        $overview = $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->get(route('admin.vendor_management.overview'));

        $overview->assertOk()
            ->assertSee('Plans &amp; Subscriptions', false)
            ->assertSee('Overview')
            ->assertSee('Plan Builder')
            ->assertSee('Vendor Subscriptions')
            ->assertSee('Renewal Requests')
            // Legacy fragmented sidebar labels must NOT appear
            ->assertDontSee('Subscription Plans')
            ->assertDontSee('Expiry &amp; Reminders', false)
            ->assertDontSee('Monetization Reports')
            ->assertDontSee('Onboarding Leads')
            ->assertDontSee('Audit &amp; Renewal Req', false);

        // Plan Builder tab loads cleanly & seeds 3 tiers per core sector
        $plansTab = $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->get(route('admin.vendor_management.plans', ['sector' => 'products']));

        $plansTab->assertOk()
            ->assertSee('Starter')
            ->assertSee('Professional')
            ->assertSee('Elite');

        // Verify 3 tiers exist for all 5 core sectors
        foreach (['products', 'food', 'services', 'used', 'enquiry'] as $sectorKey) {
            $count = SubscriptionPlan::where('sector_key', $sectorKey)
                ->whereIn('tier_key', ['starter', 'professional', 'elite'])
                ->count();
            $this->assertSame(3, $count, "Expected 3 tier plans for sector {$sectorKey}");
        }

        // Vendor Subscriptions tab loads cleanly
        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->get(route('admin.vendor_management.index'))
            ->assertOk()
            ->assertSee('WhatsApp Onboarding Leads');

        // Renewal Requests tab loads cleanly
        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->get(route('admin.vendor_management.history'))
            ->assertOk()
            ->assertSee('Renewal Requests');
    }

    public function test_plan_builder_updates_inline_commissions_retains_vs_pushes_to_active_subscribers(): void
    {
        $admin = $this->createAdminUser();
        app(SectorRegistryService::class)->ensureDefaultThreeTierPlans();

        $plan = SubscriptionPlan::where('sector_key', 'products')
            ->where('tier_key', 'starter')
            ->firstOrFail();

        [, $retainedVendor] = $this->createVendorAccount('retained-merchant', 'electronics', ['products']);
        [, $pushedVendor] = $this->createVendorAccount('pushed-merchant', 'electronics', ['products']);

        $lifecycle = app(SubscriptionLifecycleService::class);
        $retainedSub = $lifecycle->assignSubscription($retainedVendor, $plan, 'products', [], $admin->id);
        $lifecycle->recordExternalPayment($retainedSub, ['external_payment_received' => true, 'external_payment_amount' => 49900], $admin->id);
        $lifecycle->activateSubscription($retainedSub, ['confirm_activation' => true], $admin->id);

        // Edit plan WITHOUT apply_to_existing: inline commission 3.5% + ₹25 fixed, 3 months, analytics ON
        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->put(route('admin.vendor_management.plans.update', $plan), [
                'name' => 'Products Starter',
                'sector_key' => 'products',
                'tier_key' => 'starter',
                'badge_text' => 'Value Tier',
                'price_rupees' => 799,
                'duration_unit' => 'months',
                'duration_value' => 3,
                'duration_days' => 90,
                'commission_type' => 'hybrid',
                'commission_rate' => 3.5,
                'commission_fixed_rupees' => 25,
                'benefits_text' => "Up to 80 Products\nPriority WhatsApp Onboarding",
                'status' => 'active',
                'ent_allow_analytics' => '1',
                'ent_max_products' => '80',
                'apply_to_existing' => '0',
                'change_notes' => 'Bumped to hybrid 3.5% + Rs25',
            ])
            ->assertRedirect();

        $plan->refresh();
        $retainedSub->refresh();

        $this->assertSame('hybrid', $plan->commission_type);
        $this->assertEquals(3.5, (float) $plan->commission_rate);
        $this->assertSame(2500, $plan->commission_fixed_paise);
        $this->assertSame(90, $plan->duration_days);
        $this->assertSame('months', $plan->duration_unit);
        $this->assertSame(3, $plan->duration_value);
        // Retained subscriber keeps original version snapshot!
        $this->assertSame(1, $retainedSub->plan_version_number);

        // Assign a second vendor AFTER update, and also test apply_to_existing = 1
        $newSub = $lifecycle->assignSubscription($pushedVendor, $plan, 'products', [], $admin->id);
        $lifecycle->recordExternalPayment($newSub, ['external_payment_received' => true, 'external_payment_amount' => 79900], $admin->id);
        $lifecycle->activateSubscription($newSub, ['confirm_activation' => true], $admin->id);

        $commissions = app(CommissionService::class);
        $rule = $commissions->resolveEffectiveRule($pushedVendor, 'products', null, $newSub->fresh());
        $this->assertSame('plan', $rule['source']);
        $this->assertSame('Products Starter Terms (Hybrid)', $rule['name']);
        $calc = $commissions->calculateAmounts(100000, $rule['rate_percent'], $rule['fixed_fee_paise']); // ₹1,000 -> 3.5% (₹35) + ₹25 = ₹60 (6000 paise)
        $this->assertSame(6000, $calc['calculated_commission_paise']);

        // Now push update with zero commission and apply_to_existing = 1
        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->put(route('admin.vendor_management.plans.update', $plan), [
                'name' => 'Products Starter',
                'sector_key' => 'products',
                'tier_key' => 'starter',
                'price_rupees' => 999,
                'duration_unit' => 'months',
                'duration_value' => 3,
                'duration_days' => 90,
                'commission_type' => 'zero',
                'status' => 'active',
                'apply_to_existing' => '1',
                'change_notes' => 'Zero commission promo pushed to existing active subscribers',
            ])
            ->assertRedirect();

        $retainedSub->refresh();
        $newSub->refresh();
        $this->assertSame(3, $retainedSub->plan_version_number);
        $this->assertSame('zero', $retainedSub->commission_type_snapshot);
        $this->assertSame('zero', $newSub->commission_type_snapshot);

        $zeroRule = $commissions->resolveEffectiveRule($retainedVendor, 'products', null, $retainedSub);
        $this->assertSame('plan', $zeroRule['source']);
        $this->assertSame('Products Starter (Zero Commission)', $zeroRule['name']);
        $zeroCalc = $commissions->calculateAmounts(100000, $zeroRule['rate_percent'], $zeroRule['fixed_fee_paise']);
        $this->assertSame(0, $zeroCalc['calculated_commission_paise']);
    }

    public function test_plan_feature_switches_and_quotas_gate_vendor_modules(): void
    {
        [$vendorUser, $vendor] = $this->createVendorAccount('gated-studio', 'agency', ['services', 'products', 'used']);

        // 1. Disable analytics + shorts boosts + cap shorts at 1 + cap portfolio media at 1 + disable offer negotiation
        $sub = $this->grantVendorTestSubscription($vendor, 'all', [
            'allow_analytics' => false,
            'max_active_shorts' => 1,
            'allow_short_boosts' => false,
            'max_portfolio_media' => 1,
            'allow_offer_negotiation' => false,
        ]);

        // Analytics blocked by feature-locked view
        $this->actingAs($vendorUser)
            ->get(route('vendor.analytics.index'))
            ->assertOk()
            ->assertSee('Analytics Not Included in Current Plan');

        // Enable analytics on snapshot -> loads dashboard analytics cleanly
        $entitlements = $sub->entitlements_snapshot;
        $entitlements['allow_analytics'] = true;
        $sub->update(['entitlements_snapshot' => $entitlements]);

        $this->actingAs($vendorUser)
            ->get(route('vendor.analytics.index'))
            ->assertOk()
            ->assertDontSee('Analytics Not Included in Current Plan');

        // Shorts quota & boost check: create 1 active short -> saving a 2nd short hits quota
        Short::create([
            'vendor_id' => $vendor->id,
            'title' => 'First Active Short',
            'slug' => 'first-active-short-' . uniqid(),
            'video_path' => 'shorts/sample.mp4',
            'video_url' => '/storage/shorts/sample.mp4',
            'status' => 'published',
        ]);

        $this->actingAs($vendorUser)
            ->postJson(route('vendor.shorts.save'), [
                'video_url' => '/storage/shorts/second.mp4',
                'title' => 'Second Short Blocked',
            ])
            ->assertStatus(403)
            ->assertJsonFragment(['success' => false]);

        $this->actingAs($vendorUser)
            ->postJson(route('vendor.shorts.boost'), [
                'short_id' => 1,
                'package_type' => 'standard',
            ])
            ->assertStatus(403)
            ->assertJsonFragment(['success' => false]);

        // Portfolio gallery upload exceeding max_portfolio_media=1 rejected cleanly
        $this->actingAs($vendorUser)
            ->post(route('vendor.portfolio.store'), [
                'title' => 'Architectural Stone Showcase',
                'status' => 'published',
                'gallery' => [
                    UploadedFile::fake()->image('one.jpg'),
                    UploadedFile::fake()->image('two.jpg'),
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Offer negotiation blocked when allow_offer_negotiation = false
        $category = Category::firstOrCreate(
            ['slug' => 'used-items'],
            ['name' => 'Used Items', 'sector' => 'used', 'is_active' => true]
        );
        $buyer = User::factory()->create();
        $usedProduct = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Refurbished Grinder',
            'slug' => 'refurbished-grinder-' . uniqid(),
            'description' => 'Working condition',
            'base_price' => 450000,
            'total_stock' => 1,
            'status' => 'active',
            'vertical' => 'used',
            'condition_grade' => 'good',
        ]);
        $offer = Offer::create([
            'product_id' => $usedProduct->id,
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'seller_id' => $vendorUser->id,
            'buyer_name' => $buyer->name,
            'buyer_email' => $buyer->email,
            'buyer_phone' => '9876543210',
            'offer_amount' => 400000,
            'status' => 'pending',
            'expires_at' => now()->addDays(2),
        ]);

        $this->actingAs($vendorUser)
            ->putJson(route('vendor.offers.respond', $offer), ['action' => 'accept'])
            ->assertStatus(403)
            ->assertJsonFragment(['success' => false]);
    }

    public function test_expired_subscription_shows_two_ribbon_monochrome_lock_screen_and_blocks_bypasses(): void
    {
        [$vendorUser, $vendor] = $this->createVendorAccount('expired-store', 'electronics', ['products']);

        $expiredAt = now()->subDays(2);
        $this->grantVendorTestSubscription($vendor, 'products', [], [
            'plan_name_snapshot' => 'Products Professional',
            'status' => VendorSubscription::STATUS_EXPIRED,
            'starts_at' => now()->subDays(32),
            'ends_at' => $expiredAt,
            'grace_ends_at' => now()->subDay(),
            'expired_at' => $expiredAt,
        ]);

        // Dashboard, products, shorts, analytics all lock with Monochrome Two-Ribbon Lock Screen
        foreach ([
            route('vendor.dashboard'),
            route('vendor.products.index'),
            route('vendor.shorts.index'),
            route('vendor.analytics.index'),
        ] as $lockedUrl) {
            $this->actingAs($vendorUser)
                ->get($lockedUrl)
                ->assertOk()
                ->assertSee('PLAN EXPIRED')
                ->assertSee('Contact Admin for Renewal')
                ->assertSee('Message Admin for Renewal')
                ->assertSee('Products Professional')
                ->assertSee($expiredAt->format('d M Y'))
                ->assertSee('Your store data is safe.');
        }

        // AJAX / JSON bypass attempt returns HTTP 403 locked=true
        $this->actingAs($vendorUser)
            ->postJson(route('vendor.shorts.ajax'), ['action' => 'zsu_vendor_get_shorts'])
            ->assertStatus(403)
            ->assertJson(['locked' => true, 'sector_key' => 'products']);

        // Direct POST creation attempt redirects back to locked dashboard with error
        $this->actingAs($vendorUser)
            ->post(route('vendor.products.store'), ['title' => 'Sneak Upload'])
            ->assertRedirect(route('vendor.dashboard', ['sector' => 'products']))
            ->assertSessionHas('error');
    }

    public function test_multi_sector_vendor_locks_only_expired_sector_while_active_sector_stays_open(): void
    {
        [$vendorUser, $vendor] = $this->createVendorAccount('multi-sector-biz', 'electronics', ['products', 'food']);

        // Food is ACTIVE
        $this->grantVendorTestSubscription($vendor, 'food', [], [
            'plan_name_snapshot' => 'Food Professional',
            'status' => VendorSubscription::STATUS_ACTIVE,
            'ends_at' => now()->addDays(25),
        ]);

        // Products is EXPIRED
        $this->grantVendorTestSubscription($vendor, 'products', [], [
            'plan_name_snapshot' => 'Products Starter',
            'status' => VendorSubscription::STATUS_EXPIRED,
            'ends_at' => now()->subDays(3),
            'grace_ends_at' => now()->subDay(),
        ]);

        // Active food menu stays unlocked
        $this->actingAs($vendorUser)
            ->get(route('vendor.food.menu'))
            ->assertOk()
            ->assertDontSee('PLAN EXPIRED');

        // Default /vendor/dashboard automatically picks active sector (food) cleanly
        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertDontSee('PLAN EXPIRED');

        // Visiting products or dashboard?sector=products renders Products lock screen + Other Active Sectors switcher
        $this->actingAs($vendorUser)
            ->get(route('vendor.products.index'))
            ->assertOk()
            ->assertSee('PLAN EXPIRED')
            ->assertSee('Products &amp; Marketplace Access Locked', false)
            ->assertSee('Open Food Workspace');

        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard', ['sector' => 'products']))
            ->assertOk()
            ->assertSee('PLAN EXPIRED')
            ->assertSee('Products &amp; Marketplace Access Locked', false);
    }

    public function test_full_end_to_end_renewal_request_reply_reject_and_approve_renew_unlocks_vendor(): void
    {
        $admin = $this->createAdminUser();
        app(SectorRegistryService::class)->ensureDefaultThreeTierPlans();
        $starterPlan = SubscriptionPlan::where('sector_key', 'products')->where('tier_key', 'starter')->firstOrFail();

        [$vendorUser, $vendor] = $this->createVendorAccount('renewal-flow-shop', 'electronics', ['products']);

        $expiredSub = $this->grantVendorTestSubscription($vendor, 'products', [], [
            'plan_id' => $starterPlan->id,
            'plan_name_snapshot' => 'Products Starter',
            'status' => VendorSubscription::STATUS_EXPIRED,
            'starts_at' => now()->subDays(40),
            'ends_at' => now()->subDays(5),
            'grace_ends_at' => now()->subDays(2),
        ]);

        // Confirm initially locked
        $this->actingAs($vendorUser)
            ->get(route('vendor.products.index'))
            ->assertOk()
            ->assertSee('PLAN EXPIRED');

        // 1. Vendor submits renewal request from lock screen
        $this->actingAs($vendorUser)
            ->post(route('vendor.subscription.request_renewal'), [
                'sector_key' => 'products',
                'requested_plan_id' => $starterPlan->id,
                'message' => 'Paid 499 via UPI UTR 998877665544, please renew Products Starter.',
            ])
            ->assertRedirect();

        $req = SubscriptionRenewalRequest::where('vendor_id', $vendor->id)->latest('id')->firstOrFail();
        $this->assertSame('pending', $req->status);

        // Admin sees it on Renewal Requests tab
        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->get(route('admin.vendor_management.history'))
            ->assertOk()
            ->assertSee('Paid 499 via UPI UTR 998877665544');

        // 2. Admin sends reply to vendor -> vendor sees Admin Response on lock screen
        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->post(route('admin.vendor_management.renewal_requests.reply', $req), [
                'admin_reply' => 'Checking your UTR with accounts right now.',
                'status' => 'reviewed',
            ])
            ->assertRedirect();

        $req->refresh();
        $this->assertSame('reviewed', $req->status);
        $this->assertSame('Checking your UTR with accounts right now.', $req->admin_reply);

        $this->actingAs($vendorUser)
            ->get(route('vendor.products.index'))
            ->assertOk()
            ->assertSee('Checking your UTR with accounts right now.');

        // 3. Test reject workflow on a secondary request
        $rejectedReq = SubscriptionRenewalRequest::create([
            'vendor_id' => $vendor->id,
            'vendor_subscription_id' => $expiredSub->id,
            'sector_key' => 'products',
            'requested_plan_id' => $starterPlan->id,
            'message' => 'Duplicate request without proof',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->post(route('admin.vendor_management.renewal_requests.reject', $rejectedReq), [
                'rejection_reason' => 'Duplicate ticket — original UTR request already in progress.',
            ])
            ->assertRedirect();

        $rejectedReq->refresh();
        $this->assertSame('rejected', $rejectedReq->status);
        $this->assertSame('Duplicate ticket — original UTR request already in progress.', $rejectedReq->rejection_reason);

        // Still locked before approval
        $this->actingAs($vendorUser)
            ->get(route('vendor.products.index'))
            ->assertOk()
            ->assertSee('PLAN EXPIRED');

        // 4. Admin clicks Approve & Renew Subscription on $req
        $this->actingAs($admin)
            ->withSession($this->adminSession())
            ->post(route('admin.vendor_management.renewal_requests.approve_renew', $req), [
                'plan_id' => $starterPlan->id,
                'duration_days' => 30,
                'external_payment_received' => '1',
                'external_payment_amount_rupees' => 499,
                'external_payment_method' => 'upi',
                'external_payment_ref' => '998877665544',
                'admin_reply' => 'Payment verified! Renewed for 30 days.',
            ])
            ->assertRedirect();

        $req->refresh();
        $expiredSub->refresh();
        $this->assertSame('fulfilled', $req->status);
        $this->assertNotNull($req->renewed_subscription_id);
        $this->assertSame(VendorSubscription::STATUS_ACTIVE, $expiredSub->status);
        $this->assertTrue($expiredSub->ends_at->isFuture());

        // 5. Vendor's lock immediately unlocks! Products & Dashboard work again without PLAN EXPIRED screen!
        $this->actingAs($vendorUser)
            ->get(route('vendor.products.index'))
            ->assertOk()
            ->assertDontSee('PLAN EXPIRED');

        $this->actingAs($vendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertDontSee('PLAN EXPIRED');
    }
}
