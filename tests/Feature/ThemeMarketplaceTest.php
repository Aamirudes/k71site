<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\StorefrontTheme;
use App\Models\ThemeAuditLog;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorThemeDraft;
use App\Models\VendorThemeEntitlement;
use App\Models\VendorThemeRequest;
use App\Services\Storefront\ThemeMarketplaceRegistry;
use App\Services\Storefront\ThemeMarketplaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    protected function makeAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $user = User::factory()->create(['email' => 'admin@k71.test']);
        $user->roles()->syncWithoutDetaching([$role->id]);
        return $user;
    }

    protected function makeVendorUser(string $slug = 'atelier-test', string $shopName = 'Atelier Test'): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'slug' => $slug,
            'status' => 'approved',
            'verification_status' => 'verified',
            'vendor_type' => 'clothing',
            'city' => 'Mumbai',
            'description' => 'Authentic boutique goods on K71.',
            'layout_preset' => 'velora',
        ]);

        $plan = \App\Models\SubscriptionPlan::firstOrCreate(
            ['code' => 'test-retail-pro'],
            [
                'name' => 'Pro Retail',
                'slug' => 'test-retail-pro',
                'sector_key' => 'clothing',
                'tier_key' => 'pro',
                'is_free' => true,
                'price_amount' => 0,
                'currency' => 'INR',
                'duration_days' => 365,
                'is_active' => true,
                'status' => 'active',
            ]
        );

        \App\Models\VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'sector_key' => 'clothing',
            'plan_name_snapshot' => 'Pro Retail',
            'price_amount_snapshot' => 0,
            'is_free_snapshot' => true,
            'currency' => 'INR',
            'duration_days' => 365,
            'entitlements_snapshot' => ['allow_storefront_customization' => true],
            'status' => \App\Models\VendorSubscription::STATUS_ACTIVE,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addYear(),
        ]);

        return [$user, $vendor];
    }

    protected function seedProductForVendor(Vendor $vendor, string $title = 'Handcrafted Jacket', int $pricePaise = 499900): Product
    {
        $cat = Category::firstOrCreate(
            ['slug' => 'outerwear'],
            ['name' => 'Outerwear', 'is_active' => true]
        );

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title) . '-' . $vendor->id,
            'description' => 'Genuine K71 listed item with real pricing.',
            'base_price' => $pricePaise,
            'compare_at_price' => $pricePaise + 100000,
            'total_stock' => 12,
            'sku' => 'K71-JKT-01',
            'material' => 'Organic Cotton',
            'status' => 'active',
            'listing_type' => 'purchase',
            'is_featured' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'K71-JKT-M',
            'size' => 'M',
            'color' => 'Charcoal',
            'stock' => 7,
            'price_override' => $pricePaise,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'K71-JKT-L',
            'size' => 'L',
            'color' => 'Charcoal',
            'stock' => 0, // Sold-out variant must stay disabled
            'price_override' => $pricePaise,
        ]);

        return $product;
    }

    public function test_registry_contains_11_legacy_and_50_distinct_new_themes_synced_idempotently(): void
    {
        $registry = app(ThemeMarketplaceRegistry::class);
        $service = app(ThemeMarketplaceService::class);

        $legacy = $registry->getLegacyThemes();
        $newThemes = $registry->getNewThemes();
        $all = $registry->getAllThemes();

        $this->assertCount(11, $legacy);
        $this->assertCount(50, $newThemes);
        $this->assertCount(61, $all);

        // Idempotent DB sync
        $service->syncThemesToDatabase();
        $service->syncThemesToDatabase();
        $this->assertEquals(61, StorefrontTheme::count());
        $this->assertEquals(11, StorefrontTheme::where('is_legacy', true)->count());
        $this->assertEquals(50, StorefrontTheme::where('is_legacy', false)->count());

        // Verify distinct visual compositions exist across the 50 themes
        $headerVariants = collect($newThemes)->pluck('layout.header_variant')->unique();
        $heroVariants = collect($newThemes)->pluck('layout.hero_variant')->unique();
        $cardVariants = collect($newThemes)->pluck('layout.card_variant')->unique();
        $pdpVariants = collect($newThemes)->pluck('layout.pdp_variant')->unique();
        $footerVariants = collect($newThemes)->pluck('layout.footer_variant')->unique();

        $this->assertGreaterThanOrEqual(6, $headerVariants->count());
        $this->assertGreaterThanOrEqual(8, $heroVariants->count());
        $this->assertGreaterThanOrEqual(8, $cardVariants->count());
        $this->assertGreaterThanOrEqual(5, $pdpVariants->count());
        $this->assertGreaterThanOrEqual(5, $footerVariants->count());
    }

    public function test_all_50_new_themes_render_homepage_collection_and_product_detail_without_errors(): void
    {
        [$user, $vendor] = $this->makeVendorUser('sweep-store', 'Sweep Boutique');
        $product = $this->seedProductForVendor($vendor, 'Architectural Coat', 650000);
        $service = app(ThemeMarketplaceService::class);
        $registry = app(ThemeMarketplaceRegistry::class);
        $service->syncThemesToDatabase();

        $this->actingAs($user);

        foreach (array_keys($registry->getNewThemes()) as $themeId) {
            // 1. Homepage preview frame
            $resHome = $this->get(route('vendor.themes.preview_frame', ['themeId' => $themeId, 'page' => 'home']));
            $resHome->assertStatus(200);
            $resHome->assertSee('data-k71-theme="' . $themeId . '"', false);
            $resHome->assertSee('Architectural Coat');

            // 2. Collection preview frame
            $resCol = $this->get(route('vendor.themes.preview_frame', ['themeId' => $themeId, 'page' => 'collection']));
            $resCol->assertStatus(200);
            $resCol->assertSee('Architectural Coat');

            // 3. Product detail preview frame
            $resPdp = $this->get(route('vendor.themes.preview_frame', ['themeId' => $themeId, 'page' => 'product', 'product' => $product->slug]));
            $resPdp->assertStatus(200);
            $resPdp->assertSee('Architectural Coat');
            $resPdp->assertSee('6,500');
        }
    }

    public function test_unentitled_vendor_can_preview_and_request_but_cannot_customize_save_or_publish(): void
    {
        $admin = $this->makeAdmin();
        [$user, $vendor] = $this->makeVendorUser('locked-shop', 'Locked Shop');
        $this->seedProductForVendor($vendor);

        $service = app(ThemeMarketplaceService::class);
        $service->syncThemesToDatabase();

        // Admin sets individual price for maison-noir & configures official WhatsApp number
        $theme = StorefrontTheme::where('theme_id', 'maison-noir')->firstOrFail();
        $service->updateThemeCatalogue($theme, ['price_rupees' => 4999], $admin);
        $service->updateMarketplaceSettings(['whatsapp_number' => '+91 98765 00001'], $admin);

        // Vendor browses catalogue & previews
        $this->actingAs($user)
            ->get(route('vendor.themes.index'))
            ->assertStatus(200)
            ->assertSee('Maison Noir')
            ->assertSee('₹4,999');

        $this->actingAs($user)
            ->get(route('vendor.themes.preview', 'maison-noir'))
            ->assertStatus(200);

        // Unentitled vendor cannot customize, save draft, or publish
        $this->actingAs($user)
            ->post(route('vendor.themes.draft.save', 'maison-noir'), ['config' => ['hero' => ['headline' => 'Hacked']]])
            ->assertStatus(403);

        $this->actingAs($user)
            ->post(route('vendor.themes.publish', 'maison-noir'))
            ->assertStatus(403);

        // Vendor requests access -> creates pending request with price snapshot, does NOT grant access or alter live store
        $this->actingAs($user)
            ->post(route('vendor.themes.request', 'maison-noir'), ['vendor_note' => 'Please share UPI details on WhatsApp'])
            ->assertRedirect(route('vendor.themes.index'));

        $req = VendorThemeRequest::where('vendor_id', $vendor->id)->where('theme_id', 'maison-noir')->first();
        $this->assertNotNull($req);
        $this->assertEquals(499900, $req->price_snapshot_paise);
        $this->assertEquals(VendorThemeRequest::STATUS_REQUESTED, $req->status);
        $this->assertEquals(VendorThemeRequest::PAYMENT_UNVERIFIED, $req->payment_verification_status);
        $this->assertFalse($service->isVendorEntitled($vendor, 'maison-noir'));
        $this->assertEquals('velora', $service->resolvePublicThemeId($vendor));

        // Verify WhatsApp URL contains configured number, theme name, and reference code
        $waUrl = $service->buildWhatsAppRequestUrl($req);
        $this->assertStringContainsString('https://wa.me/919876500001?text=', $waUrl);
        $this->assertStringContainsString(rawurlencode($req->reference_code), $waUrl);
        $this->assertStringContainsString(rawurlencode('Maison Noir'), $waUrl);

        // Duplicate active pending request is rejected
        $this->actingAs($user)
            ->post(route('vendor.themes.request', 'maison-noir'))
            ->assertSessionHas('error');
        $this->assertEquals(1, VendorThemeRequest::where('vendor_id', $vendor->id)->where('theme_id', 'maison-noir')->count());
    }

    public function test_manual_payment_verification_entitlement_draft_separation_and_explicit_publication(): void
    {
        $admin = $this->makeAdmin();
        [$user, $vendor] = $this->makeVendorUser('flagship-shop', 'Flagship Shop');
        $product = $this->seedProductForVendor($vendor, 'Signature Silk Scarf', 320000);

        $service = app(ThemeMarketplaceService::class);
        $service->syncThemesToDatabase();

        $theme = StorefrontTheme::where('theme_id', 'signature-71')->firstOrFail();
        $service->updateThemeCatalogue($theme, ['price_rupees' => 7999], $admin);

        // 1. Vendor creates request
        $req = $service->createRequest($vendor, 'signature-71');

        // 2. Admin changes price mid-request -> confirmation without acknowledgement must fail
        $service->updateThemeCatalogue($theme->fresh(), ['price_rupees' => 8999], $admin);
        $this->actingAs($admin)
            ->post(route('admin.themes.requests.confirm_payment', $req), [
                'payment_reference' => 'WA-UTR-771',
                'payment_notes' => 'Verified via WhatsApp receipt',
            ])
            ->assertSessionHas('error');

        // 3. Admin confirms payment WITH explicit price-change acknowledgement -> STILL does not grant access yet!
        $this->actingAs($admin)
            ->post(route('admin.themes.requests.confirm_payment', $req), [
                'payment_reference' => 'WA-UTR-771',
                'payment_notes' => 'Honoring original request-time snapshot price',
                'acknowledge_price_change' => 1,
            ])
            ->assertSessionHas('success');

        $req->refresh();
        $this->assertTrue($req->isPaymentConfirmed());
        $this->assertFalse($service->isVendorEntitled($vendor, 'signature-71'));

        // 4. Admin explicitly grants entitlement -> STILL does not silently publish live storefront!
        $this->actingAs($admin)
            ->post(route('admin.themes.requests.grant', $req), ['notes' => 'Granted after payment verification'])
            ->assertSessionHas('success');

        $this->assertTrue($service->isVendorEntitled($vendor, 'signature-71'));
        $this->assertEquals('velora', $service->resolvePublicThemeId($vendor));

        // 5. Vendor saves draft -> live storefront MUST still serve velora and NOT leak draft headline!
        $this->actingAs($user)
            ->post(route('vendor.themes.draft.save', 'signature-71'), [
                'config' => [
                    'hero' => ['headline' => 'SECRET UNPUBLISHED HEADLINE'],
                    'palette' => ['accent' => '#ecd2f3'],
                ],
            ])
            ->assertSessionHas('success');

        $this->assertEquals('velora', $service->resolvePublicThemeId($vendor));
        $publicBefore = $this->get(route('storefront.show', $vendor->slug));
        $publicBefore->assertStatus(200);
        $publicBefore->assertDontSee('SECRET UNPUBLISHED HEADLINE');

        // 6. Vendor explicitly publishes -> live storefront now serves Signature 71 with real product & collection & PDP!
        $this->actingAs($user)
            ->post(route('vendor.themes.publish', 'signature-71'))
            ->assertSessionHas('success');

        $this->assertEquals('signature-71', $service->resolvePublicThemeId($vendor));

        $publicAfter = $this->get(route('storefront.show', $vendor->slug));
        $publicAfter->assertStatus(200);
        $publicAfter->assertSee('data-k71-theme="signature-71"', false);
        $publicAfter->assertSee('SECRET UNPUBLISHED HEADLINE');
        $publicAfter->assertSee('Signature Silk Scarf');

        $collectionRes = $this->get(route('storefront.collection', $vendor->slug));
        $collectionRes->assertStatus(200);
        $collectionRes->assertSee('Signature Silk Scarf');

        $pdpRes = $this->get(route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]));
        $pdpRes->assertStatus(200);
        $pdpRes->assertSee('Signature Silk Scarf');
        $pdpRes->assertSee('3,200');

        // 7. Admin revokes entitlement -> blocks future publish/edits, preserves draft & product, falls back live safely
        $entitlement = VendorThemeEntitlement::where('vendor_id', $vendor->id)->where('theme_id', 'signature-71')->firstOrFail();
        $this->actingAs($admin)
            ->post(route('admin.themes.entitlements.revoke', $entitlement), ['reason' => 'Refund issued per merchant request'])
            ->assertSessionHas('success');

        $this->assertFalse($service->isVendorEntitled($vendor, 'signature-71'));
        $this->assertEquals('velora', $service->resolvePublicThemeId($vendor));
        $this->assertDatabaseHas('vendor_theme_drafts', ['vendor_id' => $vendor->id, 'theme_id' => 'signature-71']);
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        $this->actingAs($user)
            ->post(route('vendor.themes.publish', 'signature-71'))
            ->assertStatus(403);

        // 8. Verify complete immutable audit trail exists
        $this->assertGreaterThanOrEqual(5, ThemeAuditLog::count());
        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.payment.confirmed', 'theme_id' => 'signature-71']);
        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.entitlement.granted', 'theme_id' => 'signature-71']);
        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.published', 'theme_id' => 'signature-71']);
        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.entitlement.revoked', 'theme_id' => 'signature-71']);
    }

    public function test_cross_vendor_isolation_and_non_admin_blocking(): void
    {
        [$userA, $vendorA] = $this->makeVendorUser('vendor-alpha', 'Vendor Alpha');
        [$userB, $vendorB] = $this->makeVendorUser('vendor-beta', 'Vendor Beta');
        $service = app(ThemeMarketplaceService::class);
        $service->syncThemesToDatabase();

        $reqB = $service->createRequest($vendorB, 'obsidian-glass');

        // Vendor A cannot cancel Vendor B's request
        $this->actingAs($userA)
            ->post(route('vendor.themes.request.cancel', $reqB))
            ->assertSessionHas('error');

        $this->assertEquals(VendorThemeRequest::STATUS_REQUESTED, $reqB->fresh()->status);

        // Non-admin vendor cannot access Admin Theme Management routes (redirected by IsAdmin & state untouched)
        $this->actingAs($userA)->get(route('admin.themes.index'))->assertRedirect(route('vendor.dashboard'));
        $this->actingAs($userA)->post(route('admin.themes.requests.confirm_payment', $reqB))->assertRedirect(route('vendor.dashboard'));
        $this->actingAs($userA)->post(route('admin.themes.requests.grant', $reqB))->assertRedirect(route('vendor.dashboard'));
        $this->assertFalse($reqB->fresh()->isPaymentConfirmed());
        $this->assertFalse($service->isVendorEntitled($vendorB, 'obsidian-glass'));
    }

    public function test_all_11_original_legacy_themes_still_render_cleanly(): void
    {
        [$user, $vendor] = $this->makeVendorUser('legacy-store', 'Legacy Boutique');
        $vendor->forceFill(['can_use_kinetic_scroll' => true])->save();
        $this->seedProductForVendor($vendor, 'Classic Silk Kurta', 299900);

        $registry = app(ThemeMarketplaceRegistry::class);
        foreach (array_keys($registry->getLegacyThemes()) as $legacyId) {
            $vendor->update(['layout_preset' => $legacyId]);
            $vendor->storefrontConfig()->updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'preset_key' => $legacyId,
                    'published_theme_id' => $legacyId,
                    'draft_theme_id' => $legacyId,
                    'published_config' => app(\App\Services\Storefront\StorefrontPresetService::class)->getDefaultConfig($legacyId, $vendor),
                    'is_published' => true,
                ]
            );
            $vendor->unsetRelation('storefrontConfig');

            $this->get(route('storefront.show', $vendor->slug))
                ->assertStatus(200)
                ->assertSee('Legacy Boutique');
        }
    }

    public function test_declined_request_cannot_create_entitlement_and_admin_pages_load_cleanly(): void
    {
        $admin = $this->makeAdmin();
        [$user, $vendor] = $this->makeVendorUser('declined-shop', 'Declined Shop');
        $service = app(ThemeMarketplaceService::class);
        $service->syncThemesToDatabase();

        $req = $service->createRequest($vendor, 'runway-one');

        $this->actingAs($admin)
            ->post(route('admin.themes.requests.decline', $req), ['reason' => 'Payment not received'])
            ->assertSessionHas('success');

        $this->assertEquals(VendorThemeRequest::STATUS_DECLINED, $req->fresh()->status);
        $this->assertFalse($service->isVendorEntitled($vendor, 'runway-one'));

        // Cannot grant from a declined request
        $this->actingAs($admin)
            ->post(route('admin.themes.requests.grant', $req))
            ->assertSessionHas('error');

        $this->assertFalse($service->isVendorEntitled($vendor, 'runway-one'));

        // All 4 admin theme management pages return HTTP 200
        $this->actingAs($admin)->get(route('admin.themes.index'))->assertStatus(200)->assertSee('Signature 71');
        $this->actingAs($admin)->get(route('admin.themes.requests'))->assertStatus(200)->assertSee($req->reference_code);
        $this->actingAs($admin)->get(route('admin.themes.entitlements'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.themes.audit'))->assertStatus(200);
    }
}
