<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Vendor;
use App\Services\Storefront\ThemeMarketplaceRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceThemesTest extends TestCase
{
    use RefreshDatabase;

    protected function makeVendorUser(): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => 'K71 Flagship Studio',
            'slug' => 'k71-flagship-studio',
            'status' => 'approved',
            'verification_status' => 'verified',
            'vendor_type' => 'clothing',
            'city' => 'Mumbai',
            'description' => 'Fine luxury crafts and commercial trade on K71.',
            'layout_preset' => 'velora',
        ]);

        $plan = SubscriptionPlan::firstOrCreate(
            ['code' => 'test-pro'],
            ['name' => 'Pro', 'slug' => 'test-pro', 'sector_key' => 'clothing', 'tier_key' => 'pro', 'is_free' => true, 'price_monthly' => 0]
        );

        $vendor->subscriptions()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addYear(),
        ]);

        return [$user, $vendor];
    }

    protected function seedProduct(Vendor $vendor): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'couture'], ['name' => 'Couture', 'is_active' => true]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $cat->id,
            'title' => 'Signature Architectural Garment',
            'slug' => 'signature-architectural-garment',
            'description' => 'Precision cut piece made with structural integrity.',
            'base_price' => 450000,
            'compare_at_price' => 550000,
            'total_stock' => 20,
            'sku' => 'K71-SIG-01',
            'material' => 'Japanese Wool',
            'status' => 'active',
            'listing_type' => 'purchase',
            'is_featured' => true,
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'K71-SIG-01-M',
            'size' => 'M',
            'color' => 'Noir',
            'stock' => 10,
            'price_override' => 450000,
        ]);

        return $product;
    }

    public function test_three_reference_themes_resolve_and_render_structurally_independent_experiences(): void
    {
        [$user, $vendor] = $this->makeVendorUser();
        $product = $this->seedProduct($vendor);

        $registry = app(ThemeMarketplaceRegistry::class);

        $themes = ['atelier-noir', 'object-gallery', 'wholesale-command'];

        // 1. Verify Registry recognizes all 3
        foreach ($themes as $themeId) {
            $this->assertTrue($registry->isValidThemeId($themeId), "Theme {$themeId} must be valid in registry");
            $this->assertTrue($registry->isNewTheme($themeId), "Theme {$themeId} must be recognized as modern storefront theme");
            $manifest = $registry->getManifest($themeId);
            $this->assertNotNull($manifest, "Manifest for {$themeId} must exist");
            $this->assertEquals($themeId, $manifest['id']);
        }

        // 2. Render and Inspect Homepage for each
        $homeAtelier = $this->get("/s/{$vendor->slug}?theme=atelier-noir");
        $homeAtelier->assertStatus(200);
        $homeAtelier->assertSee('CAMPAIGN COLLECTION · VOL. IV');
        $homeAtelier->assertSee('The Anatomy of Restraint.');

        $homeObject = $this->get("/s/{$vendor->slug}?theme=object-gallery");
        $homeObject->assertStatus(200);
        $homeObject->assertSee('ROTUNDA');
        $homeObject->assertSee('Utilitarian Form in Pure Light.');

        $homeWholesale = $this->get("/s/{$vendor->slug}?theme=wholesale-command");
        $homeWholesale->assertStatus(200);
        $homeWholesale->assertSee('TERMINAL ONLINE');
        $homeWholesale->assertSee('Industrial Wholesale Procurement Terminal');

        // Verify distinct signatures (not one identical HTML string)
        $this->assertNotEquals($homeAtelier->getContent(), $homeObject->getContent());
        $this->assertNotEquals($homeObject->getContent(), $homeWholesale->getContent());
        $this->assertNotEquals($homeAtelier->getContent(), $homeWholesale->getContent());

        // 3. Render and Inspect Collection Pages
        $colAtelier = $this->get("/s/{$vendor->slug}/collection?theme=atelier-noir");
        $colAtelier->assertStatus(200);
        $colAtelier->assertSee('COMPLETE LOOKBOOK ARCHIVE');

        $colObject = $this->get("/s/{$vendor->slug}/collection?theme=object-gallery");
        $colObject->assertStatus(200);
        $colObject->assertSee('PERMANENT EXHIBITION CATALOGUE');

        $colWholesale = $this->get("/s/{$vendor->slug}/collection?theme=wholesale-command");
        $colWholesale->assertStatus(200);
        $colWholesale->assertSee('MASTER COMMERCIAL TRADE LEDGER');

        // 4. Render and Inspect PDPs
        $pdpAtelier = $this->get("/s/{$vendor->slug}/product/{$product->slug}?theme=atelier-noir");
        $pdpAtelier->assertStatus(200);
        $pdpAtelier->assertSee('Acquire Piece');

        $pdpObject = $this->get("/s/{$vendor->slug}/product/{$product->slug}?theme=object-gallery");
        $pdpObject->assertStatus(200);
        $pdpObject->assertSee('Acquire for Collection');

        $pdpWholesale = $this->get("/s/{$vendor->slug}/product/{$product->slug}?theme=wholesale-command");
        $pdpWholesale->assertStatus(200);
        $pdpWholesale->assertSee('LOCK IN WHOLESALE LOT');
    }
}
