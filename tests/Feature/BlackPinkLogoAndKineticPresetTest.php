<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ScrollSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BlackPinkLogoAndKineticPresetTest extends TestCase
{
    use RefreshDatabase;

    private array $vendorCleanupDirs = [];
    private array $tempStorageFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->vendorCleanupDirs as $vendor) {
            if ($vendor instanceof Vendor) {
                ScrollSequenceService::clearVendorFrames($vendor);
            }
        }

        foreach ($this->tempStorageFiles as $relPath) {
            Storage::disk('public')->delete($relPath);
            $pub = public_path('storage/' . ltrim($relPath, '/'));
            if (is_file($pub)) {
                @unlink($pub);
            }
        }

        parent::tearDown();
    }

    private function createVendor(string $email, string $shopName, string $slug, array $overrides = []): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create(['email' => $email, 'email_verified_at' => now()]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create(array_merge([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'slug' => $slug,
            'status' => 'approved',
            'phone' => '9876543210',
            'email' => $email,
            'vendor_type' => 'clothing',
        ], $overrides));

        $this->vendorCleanupDirs[] = $vendor;

        return [$user, $vendor];
    }

    private function assertImageHasZeroCobaltPixels(string $absolutePath): void
    {
        $this->assertFileExists($absolutePath);
        $bytes = file_get_contents($absolutePath);
        $this->assertNotFalse($bytes);

        $img = @imagecreatefromstring($bytes);
        $this->assertNotFalse($img, "GD must decode {$absolutePath}");

        $w = imagesx($img);
        $h = imagesy($img);
        $stepX = max(1, (int) floor($w / 24));
        $stepY = max(1, (int) floor($h / 24));
        $cobaltPixels = 0;

        for ($y = 0; $y < $h; $y += $stepY) {
            for ($x = 0; $x < $w; $x += $stepX) {
                $rgba = imagecolorat($img, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha > 110) {
                    continue;
                }
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;
                if ($r < 45 && $g < 45 && $b > 115) {
                    $cobaltPixels++;
                }
            }
        }

        imagedestroy($img);
        $this->assertSame(0, $cobaltPixels, "Legacy cobalt-blue pixels found in {$absolutePath}");
    }

    public function test_brand_logo_favicon_and_default_avatar_are_black_pink_without_cobalt_pixels(): void
    {
        foreach ([
            public_path('images/k71-logo.png'),
            public_path('images/default-avatar.png'),
            public_path('images/logo.png'),
            public_path('favicon-32x32.png'),
            public_path('icon-192x192.png'),
        ] as $pngPath) {
            $this->assertImageHasZeroCobaltPixels($pngPath);
        }

        foreach ([
            public_path('favicon.svg'),
            public_path('images/icon.svg'),
            public_path('images/logo.svg'),
            public_path('images/logo-dark.svg'),
        ] as $svgPath) {
            $this->assertFileExists($svgPath);
            $svg = file_get_contents($svgPath);
            $this->assertStringNotContainsString('#0B00B8', strtoupper($svg));
            $this->assertStringNotContainsString('#0600C2', strtoupper($svg));
            $this->assertTrue(
                str_contains(strtoupper($svg), '#ECD2F3') || str_contains(strtoupper($svg), '#FFCDFF'),
                "Orchid pink missing in {$svgPath}"
            );
        }
    }

    public function test_chat_and_navigation_emit_obsidian_orchid_branding_with_black_sent_text(): void
    {
        $avatarUrl = format_user_avatar(null, 'Hashir');
        $this->assertStringNotContainsString('0095F6', strtoupper($avatarUrl));
        $this->assertStringNotContainsString('0600C2', strtoupper($avatarUrl));
        $this->assertStringContainsString('1A1018', strtoupper($avatarUrl));
        $this->assertStringContainsString('ECD2F3', strtoupper($avatarUrl));

        $homeResp = $this->get('/');
        $homeResp->assertOk();
        $homeHtml = $homeResp->getContent();
        $this->assertStringContainsString('k71-logo.png', $homeHtml);
        $this->assertStringNotContainsString('#0B00B8', strtoupper($homeHtml));

        $buyer = User::factory()->create(['email_verified_at' => now()]);
        $chatResp = $this->actingAs($buyer)->get('/messages');
        $chatResp->assertOk();
        $chatHtml = $chatResp->getContent();
        $this->assertStringContainsString('default-avatar.png', $chatHtml);
        $this->assertStringContainsString('#000000', $chatHtml);
        $this->assertStringNotContainsString('#0095F6', strtoupper($chatHtml));
        $this->assertStringNotContainsString('#0600C2', strtoupper($chatHtml));
        $this->assertStringNotContainsString('background=0095F6', $chatHtml);
    }

    public function test_migration_resets_legacy_cobalt_blue_trigger_image_and_user_avatars(): void
    {
        // Create a temporary cobalt-blue PNG in storage disk
        $img = imagecreatetruecolor(32, 32);
        $blue = imagecolorallocate($img, 11, 0, 184);
        imagefill($img, 0, 0, $blue);
        ob_start();
        imagepng($img);
        $blueBytes = ob_get_clean();
        imagedestroy($img);

        $bluePath = '_test_blue_migration/cobalt_old.png';
        Storage::disk('public')->put($bluePath, $blueBytes);
        $this->tempStorageFiles[] = $bluePath;

        // Also create a black-pink PNG which should be preserved
        $cleanImg = imagecreatetruecolor(32, 32);
        $black = imagecolorallocate($cleanImg, 13, 13, 13);
        imagefill($cleanImg, 0, 0, $black);
        ob_start();
        imagepng($cleanImg);
        $cleanBytes = ob_get_clean();
        imagedestroy($cleanImg);

        $cleanPath = '_test_blue_migration/obsidian_new.png';
        Storage::disk('public')->put($cleanPath, $cleanBytes);
        $this->tempStorageFiles[] = $cleanPath;

        Setting::set('floating_launcher_trigger_image', '/storage/' . $bluePath, 'navigation');
        $blueUser = User::factory()->create(['avatar' => $bluePath]);
        $cleanUser = User::factory()->create(['avatar' => $cleanPath]);

        $migration = require database_path('migrations/2026_11_12_000001_reset_legacy_blue_logo_settings.php');
        $migration->up();

        $this->assertNull(Setting::get('floating_launcher_trigger_image'));
        $this->assertNull($blueUser->fresh()->avatar);
        $this->assertSame($cleanPath, $cleanUser->fresh()->avatar);
    }

    public function test_kinetic_scroll_motion_is_hidden_by_default_and_admin_can_grant_compact_step_wizard(): void
    {
        [$vendorUserA, $vendorA] = $this->createVendor('kinetic-a@test.com', 'Obsidian Stone Craft', 'obsidian-stone');
        [$vendorUserB, $vendorB] = $this->createVendor('kinetic-b@test.com', 'Second Atelier', 'second-atelier');

        $category = Category::create([
            'name' => 'Sculptures',
            'slug' => 'sculptures-kinetic',
            'is_active' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $vendorA->id,
            'category_id' => $category->id,
            'title' => 'Calcutta Onyx Plinth',
            'slug' => 'calcutta-onyx-plinth',
            'description' => 'Hand-carved translucent onyx pedestal.',
            'base_price' => 450000,
            'total_stock' => 5,
            'status' => 'active',
        ]);

        // 1. Default state: hidden in design studio & forbidden across endpoints
        $this->assertFalse($vendorA->hasKineticScrollAccess());
        $designDefault = $this->actingAs($vendorUserA)->get(route('vendor.design.index'));
        $designDefault->assertOk();
        $designDefault->assertDontSee('id="kinetic-studio"', false);
        $designDefault->assertDontSee('id="preset-card-kinetic"', false);

        $this->actingAs($vendorUserA)->postJson(route('vendor.design.activate'), [
            'preset' => 'kinetic',
        ])->assertStatus(403);

        $this->actingAs($vendorUserA)->postJson(route('vendor.design.scroll_frames.batch'), [
            'frames' => [UploadedFile::fake()->image('frame_00001.jpg', 160, 90)],
        ])->assertStatus(403);

        // Non-admin cannot toggle Scroll Motion permission
        $this->actingAs($vendorUserA)->post(route('admin.vendors.toggle_kinetic_scroll', $vendorA), [
            'enabled' => 1,
        ])->assertRedirect();
        $this->assertFalse($vendorA->fresh()->hasKineticScrollAccess());

        // 2. Admin grants Scroll Motion access exclusively to Vendor A
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Master Admin', 'description' => 'Full Access']);
        $adminUser = User::factory()->create(['email' => 'admin@marblemandi.org', 'email_verified_at' => now()]);
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->actingAs($adminUser)
            ->withSession(['admin_authenticated' => true, 'admin_2fa_verified' => true, 'admin_2fa_passed' => true])
            ->post(route('admin.vendors.toggle_kinetic_scroll', $vendorA), ['enabled' => 1])
            ->assertRedirect();

        $vendorA->refresh();
        $vendorB->refresh();
        $this->assertTrue($vendorA->hasKineticScrollAccess());
        $this->assertFalse($vendorB->hasKineticScrollAccess());

        $this->actingAs($adminUser)
            ->withSession(['admin_authenticated' => true, 'admin_2fa_verified' => true, 'admin_2fa_passed' => true])
            ->get(route('admin.vendors'))
            ->assertOk()
            ->assertSee('Scroll Motion: ON');

        // 3. Vendor A sees compact 3-step wizard and preset card; Vendor B still blocked
        $vendorUserA = $vendorUserA->fresh();
        $vendorUserB = $vendorUserB->fresh();
        $designGranted = $this->actingAs($vendorUserA)->get(route('vendor.design.index'));
        $designGranted->assertOk();
        $designGranted->assertSee('id="kinetic-studio"', false);
        $designGranted->assertSee('id="kinetic-step-1"', false);
        $designGranted->assertSee('id="kinetic-step-2"', false);
        $designGranted->assertSee('id="kinetic-step-3"', false);
        $designGranted->assertSee('1 · Video');
        $designGranted->assertSee('2 · Scrub & Text');
        $designGranted->assertSee('3 · Publish');
        $designGranted->assertSee('id="preset-card-kinetic"', false);

        // Upload both .jpg and .png batches (backend normalizes everything into frame_%05d.jpg)
        $resp = $this->actingAs($vendorUserA)->postJson(route('vendor.design.scroll_frames.batch'), [
            'frames' => [
                UploadedFile::fake()->image('frame_00001.jpg', 160, 90),
                UploadedFile::fake()->image('frame_00002.png', 160, 90),
            ],
            'start_index' => 1,
            'reset_before' => true,
            'activate_kinetic' => true,
        ]);

        $resp->assertOk()->assertJson([
            'status' => 'success',
            'written' => 2,
            'count' => 2,
            'preset' => 'kinetic',
        ]);

        $seqDir = storage_path('app/public/' . ScrollSequenceService::vendorRelativeDir($vendorA->id));
        $frame1 = $seqDir . DIRECTORY_SEPARATOR . 'frame_00001.jpg';
        $frame2 = $seqDir . DIRECTORY_SEPARATOR . 'frame_00002.jpg';
        $this->assertFileExists($frame1);
        $this->assertFileExists($frame2);
        $this->assertSame(IMAGETYPE_JPEG, @getimagesize($frame1)[2]);
        $this->assertSame(IMAGETYPE_JPEG, @getimagesize($frame2)[2]);

        // Save custom Kinetic beat copy
        $this->actingAs($vendorUserA)->postJson(route('vendor.design.quick_settings'), [
            'scroll_seq_enabled' => true,
            'scroll_seq_height_vh' => 300,
            'scroll_seq_smoothing' => 0.14,
            'scroll_seq_beat1_title' => 'CARVED ONYX IN MOTION',
            'scroll_seq_cta_text' => 'Shop Stone Pieces',
        ])->assertOk();

        // Storefront renders pinned scroll stage with JPG frames followed by products
        auth()->logout();
        $storeResp = $this->get("/s/{$vendorA->slug}");
        $storeResp->assertOk();
        $html = $storeResp->getContent();

        $this->assertStringContainsString('id="k71-scroll-stage"', $html);
        $this->assertStringContainsString('id="k71-seq-canvas"', $html);
        $this->assertStringContainsString('frame_00001.jpg', $html);
        $this->assertStringContainsString('frame_00002.jpg', $html);
        $this->assertStringContainsString('CARVED ONYX IN MOTION', $html);
        $this->assertStringContainsString('Shop Stone Pieces', $html);
        $this->assertStringContainsString($product->title, $html);

        $stagePos = strpos($html, 'id="k71-scroll-stage"');
        $prodPos = strpos($html, 'id="kinetic-products"');
        $this->assertNotFalse($stagePos);
        $this->assertNotFalse($prodPos);
        $this->assertTrue($stagePos < $prodPos, 'Kinetic JPG scroll stage must appear before product catalog.');

        // Vendor B (unauthorized) receives 403 and does NOT delete Vendor A's JPG frames
        $this->actingAs($vendorUserB)->deleteJson(route('vendor.design.scroll_frames.clear'))
            ->assertStatus(403);
        $this->assertFileExists($frame1);

        // Vendor A clearing own frames removes them
        $this->actingAs($vendorUserA)->deleteJson(route('vendor.design.scroll_frames.clear'))
            ->assertOk()
            ->assertJson(['status' => 'success']);
        $this->assertFileDoesNotExist($frame1);

        // 4. Admin revokes Scroll Motion permission -> Vendor A reverts cleanly from kinetic
        $this->actingAs($adminUser)
            ->withSession(['admin_authenticated' => true, 'admin_2fa_verified' => true, 'admin_2fa_passed' => true])
            ->post(route('admin.vendors.toggle_kinetic_scroll', $vendorA), ['enabled' => 0])
            ->assertRedirect();

        auth()->logout();
        $this->assertFalse($vendorA->fresh()->hasKineticScrollAccess());
        $this->assertSame('velora', $vendorA->fresh()->layout_preset);
        $this->get("/s/{$vendorA->slug}")->assertOk()->assertDontSee('id="k71-scroll-stage"', false);
    }

    public function test_kinetic_preset_works_on_service_provider_website_without_cart_popup(): void
    {
        [$user, $vendor] = $this->createVendor('kinetic-service@test.com', 'K71 Architecture Studio', 'k71-arch-studio', [
            'vendor_type' => 'services',
            'provider_mode' => 'individual',
            'layout_preset' => 'kinetic',
            'can_use_kinetic_scroll' => true,
            'website_status' => 'published',
            'website_published_at' => now(),
            'headline' => 'Spatial Luxury Architecture',
            'tagline' => 'Architectural Visualization & Design Systems',
            'description' => 'We architect bespoke spatial experiences and digital flagships.',
        ]);

        $serviceCat = Category::firstOrCreate(
            ['slug' => 'architecture-design-services'],
            [
                'name' => 'Architecture & Design',
                'vertical' => 'services',
                'is_active' => true,
            ]
        );

        Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $serviceCat->id,
            'vertical' => 'services',
            'title' => '3D Spatial Architectural Concept',
            'slug' => '3d-spatial-arch-concept',
            'description' => 'Complete interior concept modeling and walkthrough.',
            'base_price' => 2500000,
            'total_stock' => 99,
            'status' => 'active',
            'show_on_website' => true,
        ]);

        $this->actingAs($user)->postJson(route('vendor.design.activate'), [
            'preset' => 'kinetic',
        ])->assertOk()->assertJson(['status' => 'success', 'preset' => 'kinetic']);

        auth()->logout();
        $resp = $this->get("/s/{$vendor->slug}");
        $resp->assertOk();
        $resp->assertSee('id="k71-scroll-stage"', false);
        $resp->assertSee('Spatial Luxury Architecture');
        $resp->assertSee('3D Spatial Architectural Concept');
        $resp->assertSee('id="overview"', false);
        $resp->assertSee('id="services"', false);
        $resp->assertSee('id="contact"', false);
        $resp->assertDontSee('k71-glass-cart-popup');
        $resp->assertDontSee('+ Add to Bag');
    }
}
