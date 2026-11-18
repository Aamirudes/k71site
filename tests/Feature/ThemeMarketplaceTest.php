<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\StorefrontTheme;
use App\Models\SubscriptionPlan;
use App\Models\ThemeAuditLog;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Models\VendorThemeEntitlement;
use App\Models\VendorThemeRequest;
use App\Services\Storefront\ThemeMarketplaceRegistry;
use App\Services\Storefront\ThemeMarketplaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
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

        $plan = SubscriptionPlan::firstOrCreate(
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

        VendorSubscription::create([
            'vendor_id' => $vendor->id,
            'plan_id' => $plan->id,
            'sector_key' => 'clothing',
            'plan_name_snapshot' => 'Pro Retail',
            'price_amount_snapshot' => 0,
            'is_free_snapshot' => true,
            'currency' => 'INR',
            'duration_days' => 365,
            'entitlements_snapshot' => ['allow_storefront_customization' => true],
            'status' => VendorSubscription::STATUS_ACTIVE,
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
            'stock' => 0,
            'price_override' => $pricePaise,
        ]);

        return $product;
    }

    public function test_registry_has_61_themes_and_50_unique_compositional_signatures_with_per_theme_css(): void
    {
        $registry = app(ThemeMarketplaceRegistry::class);
        $service = app(ThemeMarketplaceService::class);

        $legacy = $registry->getLegacyThemes();
        $newThemes = $registry->getNewThemes();
        $all = $registry->getAllThemes();

        $this->assertCount(11, $legacy);
        $this->assertCount(50, $newThemes);
        $this->assertCount(61, $all);

        // Verify all 50 Section 3 theme IDs exist & have individual per-theme CSS files
        foreach (array_keys($newThemes) as $themeId) {
            $this->assertFileExists(public_path("css/themes/{$themeId}.css"), "Missing per-theme CSS for {$themeId}");
        }

        // Verify Theme Uniqueness Matrix: ZERO duplicate compositional signatures across all 50 themes
        // and all 15 genuinely distinct page architectures + 6 distinct PDP architectures!
        $matrix = $registry->getThemeUniquenessMatrix();
        $uniqueSignatures = collect($matrix)->pluck('signature')->unique();
        $uniqueArchitectures = collect($matrix)->pluck('page_architecture')->unique();
        $uniquePdpArchs = collect($newThemes)->pluck('layout.pdp_architecture')->unique();
        $this->assertCount(50, $uniqueSignatures, 'Two or more themes share an identical structural signature!');
        $this->assertCount(15, $uniqueArchitectures, 'All 15 distinct page architectures must be represented across the 50 themes!');
        $this->assertCount(6, $uniquePdpArchs, 'All 6 distinct PDP architectures must be represented across the 50 themes!');

        // Idempotent DB sync
        $service->syncThemesToDatabase();
        $service->syncThemesToDatabase();
        $this->assertEquals(61, StorefrontTheme::count());
        $this->assertEquals(11, StorefrontTheme::where('is_legacy', true)->count());
        $this->assertEquals(50, StorefrontTheme::where('is_legacy', false)->count());
    }

    public function test_all_50_new_themes_render_homepage_collection_and_pdp_with_scoped_css(): void
    {
        [$user, $vendor] = $this->makeVendorUser('sweep-store', 'Sweep Boutique');
        $product = $this->seedProductForVendor($vendor, 'Architectural Coat', 650000);
        $service = app(ThemeMarketplaceService::class);
        $registry = app(ThemeMarketplaceRegistry::class);
        $service->syncThemesToDatabase();

        $this->actingAs($user);

        foreach (array_keys($registry->getNewThemes()) as $themeId) {
            $resHome = $this->get(route('vendor.themes.preview_frame', ['themeId' => $themeId, 'page' => 'home']));
            $resHome->assertStatus(200);
            $resHome->assertSee('data-k71-theme="' . $themeId . '"', false);
            $resHome->assertSee("css/themes/{$themeId}.css", false);
            $resHome->assertSee('Architectural Coat');

            $resCol = $this->get(route('vendor.themes.preview_frame', ['themeId' => $themeId, 'page' => 'collection']));
            $resCol->assertStatus(200);
            $resCol->assertSee('Architectural Coat');

            $resPdp = $this->get(route('vendor.themes.preview_frame', ['themeId' => $themeId, 'page' => 'product', 'product' => $product->slug]));
            $resPdp->assertStatus(200);
            $resPdp->assertSee('Architectural Coat');
            $resPdp->assertSee('6,500');
        }
    }

    public function test_signed_new_tab_preview_security_cross_vendor_blocking_and_preview_toggle(): void
    {
        $admin = $this->makeAdmin();
        [$userA, $vendorA] = $this->makeVendorUser('vendor-one', 'Vendor One');
        [$userB, $vendorB] = $this->makeVendorUser('vendor-two', 'Vendor Two');
        $this->seedProductForVendor($vendorA, 'Exclusive Coat A');

        $service = app(ThemeMarketplaceService::class);
        $service->syncThemesToDatabase();

        $validSignedUrl = URL::temporarySignedRoute('vendor.themes.preview', now()->addHour(), [
            'themeId' => 'maison-elite',
            'v' => $vendorA->id,
        ]);

        // Valid signed preview works for Vendor A and emits PREVIEW — NOT LIVE + noindex
        $res = $this->actingAs($userA)->get($validSignedUrl);
        $res->assertStatus(200);
        $res->assertSee('PREVIEW — NOT LIVE');
        $res->assertSee('Maison Élite');
        $res->assertSee('Vendor One');
        $res->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        // Relative signed iframe URL works cleanly when page/category/search/sort/product params are appended
        $signedFrameBase = URL::temporarySignedRoute('vendor.themes.preview_frame', now()->addHours(6), [
            'themeId' => 'maison-elite',
            'v' => $vendorA->id,
        ], absolute: false);

        $this->actingAs($userA)->get($signedFrameBase . '&page=home')->assertStatus(200)->assertSee('Exclusive Coat A');
        $this->actingAs($userA)->get($signedFrameBase . '&page=collection&search=Exclusive&sort=price_low')->assertStatus(200)->assertSee('Exclusive Coat A');
        $this->actingAs($userA)->get($signedFrameBase . '&page=product')->assertStatus(200)->assertSee('Exclusive Coat A');
        $this->actingAs($userA)->get($signedFrameBase . 'tampered&page=home')->assertStatus(403);
        $this->actingAs($userB)->get($signedFrameBase . '&page=home')->assertStatus(403);

        // Vendor B CANNOT use Vendor A's signed preview URL
        $this->actingAs($userB)->get($validSignedUrl)->assertStatus(403);

        // Tampered signature is rejected
        $this->actingAs($userA)->get($validSignedUrl . 'tampered')->assertStatus(403);

        // Disabling preview in Admin blocks vendor preview
        $theme = StorefrontTheme::where('theme_id', 'maison-elite')->firstOrFail();
        $service->updateThemeCatalogue($theme, ['is_preview_enabled' => false], $admin);
        $this->actingAs($userA)->get($validSignedUrl)->assertStatus(403);

        // Admin QA Preview works for Admin and blocks non-admin Vendor
        $this->actingAs($admin)->get(route('admin.themes.preview', 'chronograph'))
            ->assertStatus(200)
            ->assertSee('PREVIEW — NOT LIVE')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $this->actingAs($userA)->get(route('admin.themes.preview', 'chronograph'))->assertRedirect(route('vendor.dashboard'));
    }

    public function test_pricing_integrity_missing_or_zero_price_never_silently_makes_paid_theme_free(): void
    {
        $admin = $this->makeAdmin();
        [$user, $vendor] = $this->makeVendorUser('price-check', 'Price Check Store');
        $service = app(ThemeMarketplaceService::class);
        $service->syncThemesToDatabase();

        $theme = StorefrontTheme::where('theme_id', 'chronograph')->firstOrFail();

        // Null price -> NOT free
        $this->assertNull($theme->price_paise);
        $this->assertFalse($service->isVendorEntitled($vendor, 'chronograph'));

        // Price = 0 WITHOUT is_free=true -> STILL NOT free!
        $service->updateThemeCatalogue($theme, ['price_rupees' => 0, 'is_free' => false], $admin);
        $this->assertFalse($service->isVendorEntitled($vendor, 'chronograph'));

        // Admin intentionally sets is_free = true -> NOW entitled
        $service->updateThemeCatalogue($theme->fresh(), ['price_rupees' => 0, 'is_free' => true], $admin);
        $this->assertTrue($service->isVendorEntitled($vendor, 'chronograph'));
    }

    public function test_whatsapp_request_manual_payment_entitlement_draft_separation_and_publication(): void
    {
        $admin = $this->makeAdmin();
        [$user, $vendor] = $this->makeVendorUser('flagship-shop', 'Flagship Shop');
        $product = $this->seedProductForVendor($vendor, 'Chronograph Steel Watch', 820000);

        $service = app(ThemeMarketplaceService::class);
        $service->syncThemesToDatabase();

        $theme = StorefrontTheme::where('theme_id', 'chronograph')->firstOrFail();
        $service->updateThemeCatalogue($theme, ['price_rupees' => 7999], $admin);
        $service->updateMarketplaceSettings(['whatsapp_number' => '+91 98765 00001'], $admin);

        // 1. Vendor requests access -> price snapshot captured, WhatsApp link built, NOT entitled
        $this->actingAs($user)
            ->post(route('vendor.themes.request', 'chronograph'))
            ->assertRedirect(route('vendor.themes.index'));

        $req = VendorThemeRequest::where('vendor_id', $vendor->id)->where('theme_id', 'chronograph')->firstOrFail();
        $this->assertEquals(799900, $req->price_snapshot_paise);
        $this->assertFalse($service->isVendorEntitled($vendor, 'chronograph'));
        $waUrl = $service->buildWhatsAppRequestUrl($req);
        $this->assertStringContainsString('https://wa.me/919876500001?text=', $waUrl);
        $this->assertStringContainsString(rawurlencode($req->reference_code), $waUrl);

        // 2. Unentitled vendor cannot save draft or publish
        $this->actingAs($user)->post(route('vendor.themes.draft.save', 'chronograph'), ['config' => ['hero' => ['headline' => 'Nope']]])->assertStatus(403);
        $this->actingAs($user)->post(route('vendor.themes.publish', 'chronograph'))->assertStatus(403);

        // 3. Admin confirms payment -> STILL not entitled until explicit grant
        $this->actingAs($admin)->post(route('admin.themes.requests.confirm_payment', $req), [
            'payment_reference' => 'WA-REF-901',
            'payment_notes' => 'Verified via WhatsApp receipt',
        ])->assertSessionHas('success');
        $this->assertTrue($req->fresh()->isPaymentConfirmed());
        $this->assertFalse($service->isVendorEntitled($vendor, 'chronograph'));

        // 4. Admin grants access -> STILL does not silently publish live storefront
        $this->actingAs($admin)->post(route('admin.themes.requests.grant', $req))->assertSessionHas('success');
        $this->assertTrue($service->isVendorEntitled($vendor, 'chronograph'));
        $this->assertEquals('velora', $service->resolvePublicThemeId($vendor));

        // 5. Vendor saves draft -> public store STILL serves velora and does NOT leak draft
        $this->actingAs($user)->post(route('vendor.themes.draft.save', 'chronograph'), [
            'config' => ['hero' => ['headline' => 'PRIVATE DRAFT HOROLOGY']],
        ])->assertSessionHas('success');

        $this->get(route('storefront.show', $vendor->slug))
            ->assertStatus(200)
            ->assertDontSee('PRIVATE DRAFT HOROLOGY');

        // 6. Vendor explicitly publishes -> live store, collection, and PDP now render Chronograph!
        $this->actingAs($user)->post(route('vendor.themes.publish', 'chronograph'))->assertSessionHas('success');
        $this->assertEquals('chronograph', $service->resolvePublicThemeId($vendor));

        $this->get(route('storefront.show', $vendor->slug))
            ->assertStatus(200)
            ->assertSee('data-k71-theme="chronograph"', false)
            ->assertSee('PRIVATE DRAFT HOROLOGY')
            ->assertSee('Chronograph Steel Watch');

        $this->get(route('storefront.collection', $vendor->slug))
            ->assertStatus(200)
            ->assertSee('Chronograph Steel Watch');

        $this->get(route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]))
            ->assertStatus(200)
            ->assertSee('data-pdp-layout="technical-spec-lab"', false)
            ->assertSee('8,200');

        // 7. Admin revokes entitlement -> preserves draft & product, falls back live safely, blocks re-publish
        $ent = VendorThemeEntitlement::where('vendor_id', $vendor->id)->where('theme_id', 'chronograph')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.themes.entitlements.revoke', $ent), ['reason' => 'Merchant refund'])->assertSessionHas('success');

        $this->assertFalse($service->isVendorEntitled($vendor, 'chronograph'));
        $this->assertEquals('velora', $service->resolvePublicThemeId($vendor));
        $this->assertDatabaseHas('vendor_theme_drafts', ['vendor_id' => $vendor->id, 'theme_id' => 'chronograph']);
        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->actingAs($user)->post(route('vendor.themes.publish', 'chronograph'))->assertStatus(403);

        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.payment.confirmed', 'theme_id' => 'chronograph']);
        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.entitlement.granted', 'theme_id' => 'chronograph']);
        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.published', 'theme_id' => 'chronograph']);
        $this->assertDatabaseHas('theme_audit_logs', ['action' => 'theme.entitlement.revoked', 'theme_id' => 'chronograph']);
    }

    public function test_all_11_original_legacy_themes_remain_preserved_and_render_cleanly(): void
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
}
