<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorStorefrontConfig;
use App\Models\VendorStorefrontRevision;
use App\Services\Storefront\StorefrontPresetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontDesignTest extends TestCase
{
    use RefreshDatabase;

    private function createVendor(string $email, string $shopName, string $slug): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create(['email' => $email]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'slug' => $slug,
            'status' => 'approved',
            'phone' => '9876543210',
            'email' => $email,
            'vendor_type' => 'clothing',
        ]);

        return [$user, $vendor];
    }

    public function test_guest_cannot_access_store_design(): void
    {
        $response = $this->get('/vendor/design');
        $response->assertRedirect('/login');
    }

    public function test_vendor_can_view_store_design_hub_with_all_five_presets(): void
    {
        [$user, $vendor] = $this->createVendor('atelier.owner@test.com', 'Maison Atelier', 'maison-atelier');

        $response = $this->actingAs($user)->get('/vendor/design');
        $response->assertStatus(200);
        $response->assertSee('Store Design & Presets');
        $response->assertSee('Eleven Signature Storefront Presets');
        
        // Check signature presets are visible
        $response->assertSee('Velora');
        $response->assertSee('Noiré');
        $response->assertSee('Aurel');
        $response->assertSee('Chroma');
        $response->assertSee('Orbit');
        $response->assertSee('Zenith');
        $response->assertSee('Solaris');
        $response->assertSee('Nexus');
        $response->assertSee('Elysium');
        $response->assertSee('Vogue');
        $response->assertSee('Kinetic');
    }

    public function test_vendor_can_open_visual_editor(): void
    {
        [$user, $vendor] = $this->createVendor('editor.vendor@test.com', 'Studio Craft', 'studio-craft');

        $response = $this->actingAs($user)->get('/vendor/design/editor');
        $response->assertStatus(200);
        $response->assertSee('Visual Storefront Editor');
        $response->assertSee('Studio Craft');
        $response->assertSee('Desktop');
        $response->assertSee('Tablet');
        $response->assertSee('Mobile');
        $response->assertSee('Save Draft');
        $response->assertSee('Publish');
    }

    public function test_vendor_can_save_draft_and_it_does_not_affect_live_published_store(): void
    {
        [$user, $vendor] = $this->createVendor('draft.vendor@test.com', 'Draft Test Co', 'draft-test');
        $service = app(StorefrontPresetService::class);

        // Initial published state
        $config = $service->getDefaultConfig('atelier', $vendor);
        $config['brand']['store_name'] = 'Live Published Brand Name';
        
        VendorStorefrontConfig::create([
            'vendor_id' => $vendor->id,
            'preset_key' => 'atelier',
            'schema_version' => 2,
            'draft_config' => $config,
            'published_config' => $config,
            'is_published' => true,
        ]);

        // Save a draft with modified store name
        $modifiedConfig = $config;
        $modifiedConfig['brand']['store_name'] = 'Secret Draft Name Under Construction';

        $response = $this->actingAs($user)->postJson('/vendor/design/save', [
            'config' => $modifiedConfig,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        // Check live public storefront still shows published name
        $publicResponse = $this->get("/s/{$vendor->slug}");
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Live Published Brand Name');
        $publicResponse->assertDontSee('Secret Draft Name Under Construction');

        // Check preview endpoint shows the draft
        $previewResponse = $this->actingAs($user)->get("/vendor/design/preview");
        $previewResponse->assertStatus(200);
        $previewResponse->assertSee('Secret Draft Name Under Construction');
    }

    public function test_vendor_can_switch_presets_to_all_five_options(): void
    {
        [$user, $vendor] = $this->createVendor('switcher@test.com', 'Switch Store', 'switch-store');

        foreach (['atelier', 'object', 'commerce', 'market', 'signature'] as $preset) {
            $response = $this->actingAs($user)->postJson('/vendor/design/preset', [
                'preset' => $preset,
            ]);

            $response->assertStatus(200);
            $response->assertJson(['status' => 'success', 'preset' => $preset]);

            $this->assertEquals($preset, $vendor->fresh()->storefrontConfig->preset_key);
        }
    }

    public function test_vendor_can_publish_draft_and_public_store_reflects_changes(): void
    {
        [$user, $vendor] = $this->createVendor('publisher@test.com', 'Publisher Shop', 'publisher-shop');
        $service = app(StorefrontPresetService::class);

        $draftConfig = $service->getDefaultConfig('object', $vendor);
        $draftConfig['brand']['store_name'] = 'Officially Published Object Brand';
        $draftConfig['header']['announcement_text'] = 'Special VIP Grand Opening Discount';

        // Save draft
        $this->actingAs($user)->postJson('/vendor/design/save', ['config' => $draftConfig]);

        // Publish
        $pubResponse = $this->actingAs($user)->postJson('/vendor/design/publish');
        $pubResponse->assertStatus(200);
        $pubResponse->assertJson(['status' => 'success']);

        // Check live public storefront now shows new published name and announcement
        $publicResponse = $this->get("/s/{$vendor->slug}");
        $publicResponse->assertStatus(200);
        $publicResponse->assertSee('Officially Published Object Brand');
        $publicResponse->assertSee('Special VIP Grand Opening Discount');

        // Verify revision was created
        $this->assertDatabaseHas('vendor_storefront_revisions', [
            'vendor_id' => $vendor->id,
            'preset_key' => 'object',
        ]);
    }

    public function test_vendor_cannot_modify_another_vendors_storefront(): void
    {
        [$userA, $vendorA] = $this->createVendor('owner_a@test.com', 'Shop Alpha', 'shop-alpha');
        [$userB, $vendorB] = $this->createVendor('owner_b@test.com', 'Shop Beta', 'shop-beta');

        $service = app(StorefrontPresetService::class);
        $maliciousConfig = $service->getDefaultConfig('signature', $vendorB);
        $maliciousConfig['brand']['store_name'] = 'Defaced Beta';

        // When User B calls save, it should only update Vendor B, never Vendor A
        $response = $this->actingAs($userB)->postJson('/vendor/design/save', [
            'config' => $maliciousConfig,
        ]);
        $response->assertStatus(200);

        // Vendor A is untouched
        $this->assertNull($vendorA->fresh()->storefrontConfig);
        $this->assertEquals('Shop Beta', $vendorB->fresh()->shop_name);
    }

    public function test_public_storefront_renders_real_vendor_products_and_categories(): void
    {
        [$user, $vendor] = $this->createVendor('craftsman@test.com', 'Artisan Guild', 'artisan-guild');

        $category = Category::create([
            'name' => 'Handloom Silks',
            'slug' => 'handloom-silks',
            'status' => 'active',
        ]);

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Pure Mulberry Kanjivaram Silk Saree',
            'slug' => 'pure-mulberry-kanjivaram-silk-saree',
            'description' => 'Woven by master weavers with gold zari border.',
            'base_price' => 1250000, // 12,500.00
            'compare_at_price' => 1500000,
            'gst_rate' => 5.00,
            'status' => 'active',
            'total_stock' => 10,
        ]);

        $response = $this->get("/s/{$vendor->slug}");
        $response->assertStatus(200);
        $response->assertSee('Artisan Guild');
        $response->assertSee('Pure Mulberry Kanjivaram Silk Saree');
        $response->assertSee('₹12,500.00');
        $response->assertSee('Handloom Silks');
        $response->assertSee('k71-glass-cart-popup');
    }

    public function test_vendor_can_instantly_activate_and_publish_presets(): void
    {
        [$user, $vendor] = $this->createVendor('activator@test.com', 'Cyber Atelier', 'cyber-atelier');

        $presets = ['atelier', 'object', 'commerce', 'market', 'signature'];

        foreach ($presets as $preset) {
            $response = $this->actingAs($user)->postJson('/vendor/design/activate', [
                'preset' => $preset,
            ]);

            $response->assertStatus(200)
                     ->assertJson(['status' => 'success', 'preset' => $preset]);

            $this->assertEquals($preset, $vendor->fresh()->storefrontConfig->preset_key);
            $this->assertTrue($vendor->fresh()->storefrontConfig->is_published);

            // Public storefront renders with this preset and cart popup
            $publicResp = $this->get("/s/{$vendor->slug}");
            $publicResp->assertStatus(200);
            $publicResp->assertSee('k71-glass-cart-popup');
        }
    }

    public function test_vendor_can_save_quick_branding_settings(): void
    {
        [$user, $vendor] = $this->createVendor('branding@test.com', 'Branded Studio', 'branded-studio');

        $response = $this->actingAs($user)->postJson('/vendor/design/quick-settings', [
            'tagline' => 'Handcrafted luxury and bespoke tailoring.',
            'accent_color' => '#06b6d4',
            'announcement_enabled' => true,
            'announcement_text' => 'Flash Sale: 20% off all drops today only!',
            'announcement_link' => '#catalog',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);

        $config = $vendor->fresh()->storefrontConfig->published_config;
        $this->assertEquals('Handcrafted luxury and bespoke tailoring.', $config['brand']['description']);
        $this->assertEquals('#06b6d4', $config['theme']['accent_color']);
        $this->assertTrue($config['header']['announcement_enabled']);
        $this->assertEquals('Flash Sale: 20% off all drops today only!', $config['header']['announcement_text']);

        // Check public storefront has announcement and accent color
        $publicResp = $this->get("/s/{$vendor->slug}");
        $publicResp->assertStatus(200);
        $publicResp->assertSee('Flash Sale: 20% off all drops today only!');
    }

    public function test_vendor_can_activate_modern_signature_presets(): void
    {
        [$user, $vendor] = $this->createVendor('modern@test.com', 'Modern Maison', 'modern-maison');

        $modernPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit', 'zenith', 'solaris', 'nexus', 'elysium', 'vogue', 'kinetic'];

        foreach ($modernPresets as $preset) {
            $response = $this->actingAs($user)->postJson('/vendor/design/activate', [
                'preset' => $preset,
            ]);

            $response->assertStatus(200)
                     ->assertJson(['status' => 'success', 'preset' => $preset]);

            $this->assertEquals($preset, $vendor->fresh()->storefrontConfig->preset_key);

            // Public storefront renders with this preset and cart popup
            $publicResp = $this->get("/s/{$vendor->slug}");
            $publicResp->assertStatus(200);
            $publicResp->assertSee('k71-glass-cart-popup');
        }
    }

    public function test_vendor_can_manage_happy_customer_stories_and_social_links(): void
    {
        [$user, $vendor] = $this->createVendor('stories@test.com', 'Stories Boutique', 'stories-boutique');

        // 1. Add a custom story and social links
        $response = $this->actingAs($user)->postJson('/vendor/design/quick-settings', [
            'instagram' => 'https://instagram.com/storiesboutique',
            'whatsapp' => '9876543210',
            'happy_customers_enabled' => true,
            'new_hc_name' => 'Kavita Sen',
            'new_hc_location' => 'Jaipur, RJ',
            'new_hc_rating' => 5,
            'new_hc_comment' => 'Spectacular pure silk quality and arrived within 24 hours!',
            'new_hc_media_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);

        $config = $vendor->fresh()->storefrontConfig->published_config;
        $this->assertEquals('https://instagram.com/storiesboutique', $config['brand']['instagram']);
        $this->assertEquals('9876543210', $config['brand']['whatsapp']);
        $this->assertCount(1, $config['happy_customers']['items']);
        $this->assertEquals('Kavita Sen', $config['happy_customers']['items'][0]['customer_name']);

        // Check public storefront has the customer story
        $publicResp = $this->get("/s/{$vendor->slug}");
        $publicResp->assertStatus(200);
        $publicResp->assertSee('Kavita Sen');
        $publicResp->assertSee('Spectacular pure silk quality');

        // 2. Delete the custom story
        $delResp = $this->actingAs($user)->postJson('/vendor/design/quick-settings', [
            'delete_hc_idx' => 0,
        ]);
        $delResp->assertStatus(200)->assertJson(['status' => 'success']);

        $configAfter = $vendor->fresh()->storefrontConfig->published_config;
        $this->assertCount(0, $configAfter['happy_customers']['items']);
    }
}

