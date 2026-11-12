<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Cart;
use App\Models\Category;
use App\Models\EmailOtp;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ReservedSlug;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class NewUserAndVendorRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('public');
    }

    public function test_fresh_db_empty_roles_table_supports_modal_and_full_registration_without_500(): void
    {
        // Deliberately clear all seeded roles (simulating an unseeded fresh DB)
        Role::query()->delete();
        $this->assertSame(0, Role::count());

        // 1. Modal quick registration with mixed-case email, spaces, Unicode/emoji and long >50 char name
        $modalRes = $this->post(route('register'), [
            'name' => 'Aarav 🌟 ' . str_repeat('LongFirst', 6) . ' ' . str_repeat('LongLast', 7),
            'email' => '  AARAV.MODAL@EXAMPLE.COM ',
            'password' => 'StrongPass123!',
        ]);

        $modalRes->assertRedirect(route('otp.show'));

        $modalUser = User::where('email', 'aarav.modal@example.com')->first();
        $this->assertNotNull($modalUser);
        $this->assertLessThanOrEqual(50, mb_strlen($modalUser->first_name));
        $this->assertLessThanOrEqual(50, mb_strlen($modalUser->last_name));
        $this->assertLessThanOrEqual(20, strlen($modalUser->username));
        $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $modalUser->username);
        $this->assertTrue($modalUser->hasRole('customer'));

        // 2. Symbol-only name modal registration still yields valid fallback ASCII username <= 20 chars
        $symbolRes = $this->post(route('register'), [
            'name' => '!!! --- @@@',
            'email' => 'unicode@example.com',
            'password' => 'StrongPass123!',
        ]);
        $symbolRes->assertRedirect(route('otp.show'));

        $symbolUser = User::where('email', 'unicode@example.com')->first();
        $this->assertNotNull($symbolUser);
        $this->assertLessThanOrEqual(20, strlen($symbolUser->username));
        $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $symbolUser->username);

        // 3. Full registration with uppercase duplicate email fails validation cleanly (302), never 500
        $dupRes = $this->post(route('register'), [
            'first_name' => 'Duplicate',
            'last_name' => 'Person',
            'username' => strtoupper($modalUser->username),
            'email' => 'AARAV.MODAL@EXAMPLE.COM',
            'password' => 'StrongPass123!',
        ]);
        $dupRes->assertStatus(302);
        $dupRes->assertSessionHasErrors(['email', 'username']);
    }

    public function test_otp_verify_handles_case_insensitive_email_and_missing_user_safely(): void
    {
        $role = Role::firstOrCreate(['name' => 'customer'], ['guard_name' => 'web']);
        $user = User::factory()->create([
            'email' => 'otpuser@example.com',
            'email_verified_at' => null,
            'password' => Hash::make('Secret123!'),
        ]);
        $user->roles()->syncWithoutDetaching([$role->id]);

        app(OtpService::class)->generateAndSend('  OTPUSER@EXAMPLE.COM ', 'registration', $user->id);
        $otpCode = Mail::sent(\App\Mail\OtpMail::class)->last()->code;

        // 1. Valid OTP exists for a deleted/missing user -> must redirect cleanly instead of crashing on null->update()
        app(OtpService::class)->generateAndSend('nobody@example.com', 'registration');
        $missingCode = Mail::sent(\App\Mail\OtpMail::class)->last()->code;

        $missingRes = $this->withSession([
            'verify_email' => 'nobody@example.com',
            'verify_purpose' => 'registration',
        ])->post(route('otp.verify'), [
            'code' => $missingCode,
        ]);
        $missingRes->assertRedirect(route('login'));

        // 2. Valid verification with uppercase session email succeeds and verifies account
        $verifyRes = $this->withSession([
            'verify_email' => 'OTPUSER@EXAMPLE.COM',
            'verify_purpose' => 'registration',
            'login_role' => 'customer',
        ])->post(route('otp.verify'), [
            'code' => $otpCode,
        ]);

        $verifyRes->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertNotNull($user->fresh()->email_verified_at);

        // 3. Case-insensitive email login works seamlessly
        $this->post(route('logout'));
        $loginRes = $this->post(route('login'), [
            'login' => 'OTPUSER@EXAMPLE.COM',
            'password' => 'Secret123!',
            'role' => 'customer',
        ]);
        $loginRes->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_google_oauth_handles_empty_roles_unicode_names_missing_email_and_trashed_users(): void
    {
        Role::query()->delete();
        $this->assertSame(0, Role::count());

        // 1. New user with Unicode/emoji name over 50 chars and uppercase email
        $fakeGoogleUser = new class {
            public string $id = 'google-uid-1001';
            public string $name = '🌟🔥✨ SuperExtremelyLongUnicodeDisplayNameOverFiftyCharactersRightHere';
            public string $email = 'GOOGLE.NEW@EXAMPLE.COM';
        };

        $provider = \Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($fakeGoogleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);

        $res = $this->get('/auth/google/callback');
        $res->assertRedirect(route('home'));

        $created = User::where('email', 'google.new@example.com')->first();
        $this->assertNotNull($created);
        $this->assertLessThanOrEqual(50, strlen($created->first_name));
        $this->assertLessThanOrEqual(20, strlen($created->username));
        $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $created->username);
        $this->assertTrue($created->hasRole('customer'));
        $this->assertAuthenticatedAs($created);

        // 2. Empty email from Google provider redirects cleanly instead of crashing
        $this->post(route('logout'));
        $noEmailGoogleUser = new class {
            public string $id = 'google-uid-noemail';
            public string $name = 'No Email User';
            public string $email = '';
        };
        $provider2 = \Mockery::mock();
        $provider2->shouldReceive('user')->once()->andReturn($noEmailGoogleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider2);

        $noEmailRes = $this->get('/auth/google/callback');
        $noEmailRes->assertRedirect(route('login'));
        $noEmailRes->assertSessionHasErrors('login');

        // 3. Soft-deleted user cannot recreate duplicate email 500 via Google OAuth
        $created->delete();
        $trashedProvider = \Mockery::mock();
        $trashedProvider->shouldReceive('user')->once()->andReturn($fakeGoogleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($trashedProvider);

        $trashedRes = $this->get('/auth/google/callback');
        $trashedRes->assertRedirect(route('login'));
        $trashedRes->assertSessionHasErrors('login');
    }

    public function test_vendor_registration_validation_duplicate_prevention_and_trashed_restore(): void
    {
        Role::query()->delete();
        $this->assertSame(0, Role::count());

        // 1. Guest registers via POST /sell with spaces, uppercase, formatted >15 char phone
        $guestSellRes = $this->post('/sell', [
            'shop_name' => 'Kishangarh Stone Emporium',
            'slug' => '  KISHANGARH-STONE-2026  ',
            'vendor_type' => 'marble',
            'first_name' => 'Rohan',
            'last_name' => 'Mehta',
            'username' => '  ROHAN_MEHTA  ',
            'email' => '  ROHAN.MARBLE@EXAMPLE.COM ',
            'phone' => '+91 98765-43210',
            'password' => 'StrongPass123!',
        ]);

        $guestSellRes->assertRedirect(route('otp.show'));

        $rohan = User::where('email', 'rohan.marble@example.com')->first();
        $this->assertNotNull($rohan);
        $this->assertSame('rohan_mehta', $rohan->username);
        $this->assertSame('+919876543210', $rohan->phone);
        $this->assertLessThanOrEqual(15, strlen((string) $rohan->phone));
        $this->assertTrue($rohan->hasRole('customer'));
        $this->assertTrue($rohan->hasRole('vendor'));

        $rohanVendor = Vendor::where('user_id', $rohan->id)->first();
        $this->assertNotNull($rohanVendor);
        $this->assertSame('kishangarh-stone-2026', $rohanVendor->slug);
        $this->assertSame('approved', $rohanVendor->status);

        // 2. Verify validation endpoints reject reserved slugs, >30-char slugs, invalid usernames cleanly
        $this->getJson('/api/validate-slug?slug=admin')
            ->assertOk()
            ->assertJson(['available' => false]);
        $this->getJson('/api/validate-slug?slug=' . str_repeat('a', 31))
            ->assertOk()
            ->assertJson(['available' => false]);
        $this->getJson('/api/validate-username?username=' . $rohan->username)
            ->assertOk()
            ->assertJson(['available' => false]);
        $this->getJson('/api/validate-slug?slug=totally-fresh-slug-2026')
            ->assertOk()
            ->assertJson(['available' => true]);

        // 3. Soft-deleted vendor slug stays blocked in validation AND user can re-onboard cleanly without SQL 1062
        $rohan->update(['email_verified_at' => now()]);
        $rohanVendor->delete();

        $this->getJson('/api/validate-slug?slug=kishangarh-stone-2026')
            ->assertOk()
            ->assertJson(['available' => false]);

        $this->actingAs($rohan);

        // Reserved slug must return validation error cleanly
        $reservedRes = $this->post('/sell', [
            'shop_name' => 'Admin Store',
            'slug' => 'admin',
            'vendor_type' => 'clothing',
        ]);
        $reservedRes->assertStatus(302);
        $reservedRes->assertSessionHasErrors('slug');

        // Re-onboard previously soft-deleted vendor user with a fresh slug -> restores vendor row without unique violation
        $reOnboardRes = $this->post('/sell', [
            'shop_name' => 'Kishangarh Stone Reborn',
            'slug' => 'kishangarh-reborn-2026',
            'vendor_type' => 'electronics',
        ]);
        $reOnboardRes->assertRedirect(route('vendor.dashboard'));

        $restoredVendor = Vendor::where('user_id', $rohan->id)->first();
        $this->assertNotNull($restoredVendor);
        $this->assertSame($rohanVendor->id, $restoredVendor->id);
        $this->assertSame('kishangarh-reborn-2026', $restoredVendor->slug);
        $this->assertNull($restoredVendor->deleted_at);

        // Visiting /sell again as an existing vendor redirects cleanly to dashboard (never duplicate vendor error)
        $this->get('/sell')->assertRedirect(route('vendor.dashboard'));
        $this->post('/sell', [
            'shop_name' => 'Second Store Attempt',
            'slug' => 'second-store-attempt',
            'vendor_type' => 'clothing',
        ])->assertRedirect(route('vendor.dashboard'));

        // Logged-in customer registering a store for the first time
        $customer = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($customer);
        $this->get('/sell')->assertOk();

        $custSell = $this->post('/sell', [
            'shop_name' => 'Customer New Boutique',
            'slug' => 'cust-new-boutique',
            'vendor_type' => 'clothing',
            'phone' => '9876501122',
        ]);
        $custSell->assertRedirect(route('vendor.dashboard'));
        $this->get('/vendor/dashboard')->assertOk();
    }

    public function test_all_18_vendor_types_and_all_15_storefront_presets_render_and_publish_for_brand_new_stores(): void
    {
        $vendorTypes = [
            'clothing', 'mens', 'food', 'feasto', 'services', 'used',
            'machinery', 'electronics', 'automotive', 'digital_services',
            'local_services', 'real_estate', 'home_living', 'beauty',
            'groceries', 'toys_sports', 'shorts', 'b2b',
        ];

        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['guard_name' => 'web']);
        $customerRole = Role::firstOrCreate(['name' => 'customer'], ['guard_name' => 'web']);

        $firstVendor = null;
        $firstUser = null;

        foreach ($vendorTypes as $index => $type) {
            $user = User::factory()->create([
                'email' => "new_{$type}_{$index}@example.com",
                'username' => substr("v_{$type}_{$index}", 0, 20),
                'email_verified_at' => now(),
            ]);
            $user->roles()->syncWithoutDetaching([$customerRole->id, $vendorRole->id]);

            $this->actingAs($user);
            $slug = substr("s-{$index}-" . str_replace('_', '-', $type), 0, 30);

            $sellRes = $this->post('/sell', [
                'shop_name' => "Fresh {$type} Store #{$index}",
                'slug' => $slug,
                'vendor_type' => $type,
                'phone' => '9876543210',
            ]);
            $sellRes->assertRedirect(route('vendor.dashboard'));

            $vendor = Vendor::where('slug', $slug)->first();
            $this->assertNotNull($vendor, "Vendor not created for type {$type}");

            if ($index === 0) {
                $firstVendor = $vendor;
                $firstUser = $user;
            }

            // Every vendor portal page must render cleanly for a brand-new 0-product store
            $vendorPages = [
                '/vendor/dashboard',
                '/vendor/profile',
                '/vendor/settings',
                '/vendor/design',
                '/vendor/design/editor',
                '/vendor/products',
                '/vendor/products/create',
                '/vendor/services',
                '/vendor/services/create',
                '/vendor/coupons',
                '/vendor/orders',
                '/vendor/bookings',
                '/vendor/enquiries',
                '/vendor/offers',
                '/vendor/messages',
                '/vendor/shorts',
                '/account',
                '/account/listings',
                "/s/{$slug}",
            ];

            foreach ($vendorPages as $url) {
                $resp = $this->get($url);
                $this->assertNotEquals(500, $resp->getStatusCode(), "URL [{$url}] crashed for vendor_type={$type}");
                $this->assertTrue(in_array($resp->getStatusCode(), [200, 302], true), "Unexpected status {$resp->getStatusCode()} on [{$url}] for {$type}");
            }

            // Saving vendor settings WITHOUT upi_id key must succeed cleanly
            $settingsRes = $this->put(route('vendor.settings.update'), [
                'payment_mode' => 'both',
                'processing_days' => 2,
                'return_window_days' => 7,
            ]);
            $settingsRes->assertStatus(302);
            $settingsRes->assertSessionHasNoErrors();

            // Publishing design directly on brand-new vendor (with NO prior VendorStorefrontConfig record)
            $publishRes = $this->postJson(route('vendor.design.publish'));
            $publishRes->assertOk()->assertJson(['status' => 'success']);
        }

        // Sweep all 16 presets (11 signature + 5 legacy aliases) on a 0-product store
        $this->actingAs($firstUser);
        $presets = [
            'velora', 'noire', 'aurel', 'chroma', 'orbit',
            'zenith', 'solaris', 'nexus', 'elysium', 'vogue', 'kinetic',
            'atelier', 'object', 'commerce', 'market', 'signature',
        ];
        foreach ($presets as $preset) {
            $this->postJson(route('vendor.design.activate'), ['preset' => $preset])
                ->assertOk()
                ->assertJson(['status' => 'success', 'preset' => $preset]);

            $this->get("/s/{$firstVendor->slug}")->assertOk();
        }
    }

    public function test_account_deletion_form_works_and_truncates_username_under_20_chars(): void
    {
        // Force a high user ID >= 100 so 'deleted_' . $id . '_' . time() would have exceeded 20 chars before fix
        $user = User::factory()->create([
            'id' => 12345,
            'email' => 'deleteme12345@example.com',
            'username' => 'deleteme12345',
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        // Account page HTML must contain confirm_delete=1 and must NOT spoof DELETE method on POST /account/delete
        $accountRes = $this->get(route('account'));
        $accountRes->assertOk();
        $accountRes->assertSee('name="confirm_delete" value="1"', false);
        $accountRes->assertDontSee('name="_method" value="DELETE"', false);

        $deleteRes = $this->post(route('account.delete'), [
            'confirm_delete' => '1',
        ]);
        $deleteRes->assertRedirect(route('home'));

        $trashed = User::withTrashed()->find(12345);
        $this->assertNotNull($trashed);
        $this->assertNotNull($trashed->deleted_at);
        $this->assertLessThanOrEqual(20, strlen($trashed->username));
    }

    public function test_freelance_become_seller_gig_edit_symbol_titles_classifieds_cart_checkout_and_fleet(): void
    {
        Role::query()->delete();
        $this->assertSame(0, Role::count());

        // 1. Freelancer become-seller on fresh DB (empty roles) attaches vendor role so /vendor/dashboard works
        $freelancer = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($freelancer);

        $becomeRes = $this->post(route('services.store_seller'), [
            'shop_name' => '★★★ +++', // Symbol-only shop name tests fallback slug
            'tagline' => 'Fast UI/UX Specialist',
            'description' => 'Design and full-stack engineering services.',
            'skills' => 'Figma, Laravel',
            'contact_phone' => '+91 98111-22233',
        ]);
        $becomeRes->assertRedirect(route('account.listings', ['tab' => 'services']));

        $freelancer->refresh();
        $this->assertTrue($freelancer->hasRole('vendor'));
        $this->assertTrue($freelancer->hasRole('customer'));
        $this->get('/vendor/dashboard')->assertOk();

        // 2. Gig creation & editing via BOTH services.gig.update AND vendor.services.edit/update
        $serviceCat = Category::create([
            'name' => 'Web Design',
            'slug' => 'web-design-srv',
            'vertical' => 'services',
            'is_active' => true,
        ]);

        $this->post(route('vendor.services.store'), [
            'title' => '!!! @@@', // Symbol-only gig title tests slug fallback
            'category_id' => $serviceCat->id,
            'description' => 'Complete landing page architecture and clean Figma source kit delivery.',
            'basic_title' => 'Basic Design',
            'basic_description' => 'Single screen layout',
            'basic_price' => 1500,
            'basic_delivery_days' => 2,
            'basic_revisions' => 2,
        ])->assertRedirect(route('vendor.services.index'));

        $gig = Product::withoutGlobalScopes()->where('vendor_id', $freelancer->vendor->id)->first();
        $this->assertNotNull($gig);
        $this->assertNotEmpty($gig->slug);

        // Vendor studio edit + update (routes previously threw BadMethodCallException)
        $this->get(route('vendor.services.edit', $gig))->assertOk();
        $this->put(route('vendor.services.update', $gig), [
            'title' => 'Updated UI Architecture Gig',
            'category_id' => $serviceCat->id,
            'description' => 'Updated full UI package with responsive prototypes.',
            'base_price' => 2500,
            'packages' => [
                'basic' => [
                    'name' => 'Basic Updated',
                    'price' => 2500,
                    'delivery_days' => 3,
                    'revisions' => 'Unlimited',
                    'description' => 'Includes unlimited tweaks',
                ],
            ],
        ])->assertRedirect(route('vendor.services.index'));

        $basicPkg = ProductGigPackage::where('product_id', $gig->id)->where('tier', 'basic')->first();
        $this->assertNotNull($basicPkg);
        $this->assertSame('Basic Updated', $basicPkg->title);
        $this->assertEquals(2500.00, (float) $basicPkg->price);
        $this->assertSame(99, $basicPkg->revisions);

        // Public services edit update (ServicesController::updateService previously referenced missing GigPackage)
        $this->put(route('services.gig.update', $gig), [
            'title' => 'Publicly Updated Gig',
            'category_id' => $serviceCat->id,
            'description' => 'Still clean and validated.',
            'base_price' => 3200,
            'packages' => [
                'basic' => [
                    'name' => 'Starter Tier',
                    'price' => 3200,
                    'delivery_days' => 2,
                    'revisions' => '4 Revisions',
                    'description' => 'Fast delivery',
                ],
            ],
        ])->assertRedirect(route('services.show', $gig->slug));

        $basicPkg->refresh();
        $this->assertSame('Starter Tier', $basicPkg->title);
        $this->assertEquals(3200.00, (float) $basicPkg->price);
        $this->assertSame(4, $basicPkg->revisions);

        // 3. Product creation & duplication with symbol-only title + 95-char SKU
        $ecomCat = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics-cat',
            'vertical' => 'ecommerce',
            'is_active' => true,
        ]);

        $this->post(route('vendor.products.store'), [
            'title' => '### $$$',
            'category_id' => $ecomCat->id,
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Authentic gadget item ready for shipping.',
            'base_price' => 1999,
            'gst_rate' => 18,
            'sku' => str_repeat('SKU48', 9) . '123',
            'total_stock' => 10,
            'status' => 'active',
        ])->assertRedirect(route('vendor.products.index'));

        $prod = Product::withoutGlobalScopes()->where('title', '### $$$')->first();
        $this->assertNotNull($prod);
        $this->assertStringStartsWith('product', $prod->slug);

        // 4. Cart update with null variant_id (simple product without variants) & gig package
        $variantlessProd = Product::create([
            'vendor_id' => $freelancer->vendor->id,
            'category_id' => $ecomCat->id,
            'title' => 'Simple Variantless Ceramic Bowl',
            'slug' => 'simple-variantless-bowl',
            'vertical' => 'ecommerce',
            'listing_type' => 'purchase',
            'description' => 'Handcrafted simple item with no variants.',
            'base_price' => 50000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $this->postJson(route('cart.add'), [
            'product_id' => $variantlessProd->id,
            'quantity' => 1,
        ])->assertOk()->assertJson(['success' => true]);

        $cart = Cart::where('user_id', $freelancer->id)->first();
        $cartItem = $cart?->items()->where('product_id', $variantlessProd->id)->first();
        $this->assertNotNull($cartItem);
        $this->assertNull($cartItem->variant_id);

        $this->putJson(route('cart.update', $cartItem), ['quantity' => 3])
            ->assertOk()
            ->assertJson(['count' => 3]);

        // Also test Gig Package add-to-cart (variant_id is null, gig_package_id set)
        $this->postJson(route('cart.add'), [
            'product_id' => $gig->id,
            'gig_package_id' => $basicPkg->id,
            'quantity' => 1,
        ])->assertOk()->assertJson(['success' => true]);

        $this->get(route('cart.index'))->assertOk();

        // Attach a 96-char variant SKU to verify duplicate truncates before appending -COPY (<= 100 chars)
        \App\Models\ProductVariant::create([
            'product_id' => $prod->id,
            'sku' => str_repeat('VAR96_', 16),
            'stock' => 5,
        ]);

        $dupRes = $this->post(route('vendor.products.duplicate', $prod));
        $dupRes->assertStatus(302);
        $clonedProd = Product::withoutGlobalScopes()->where('id', '!=', $prod->id)->where('title', '### $$$ (Copy)')->first();
        $this->assertNotNull($clonedProd);
        $this->assertLessThanOrEqual(100, strlen((string) $clonedProd->variants()->first()?->sku));

        // 5. Classified ad posting auto-creates category when 0 used categories exist
        $this->postJson(route('used.post-ad'), [
            'title' => '### Pre-Owned Tablet',
            'category' => 'Tablets',
            'price' => 12000,
            'city' => 'Jaipur',
            'phone' => '9876543210',
            'description' => 'Great battery life and screen.',
        ])->assertOk()->assertJson(['success' => true]);

        // 6. Shorts save with symbol-only title
        $this->postJson(route('vendor.shorts.save'), [
            'title' => '🔥🔥🔥',
            'video_url' => 'https://example.com/reel.mp4',
        ])->assertOk()->assertJson(['success' => true]);

        // 7. Unauthenticated delivery API guards return 401 JSON instead of null->update() 500
        $this->post(route('logout'));
        $this->postJson(route('delivery.location'), ['latitude' => 26.5, 'longitude' => 74.8])
            ->assertStatus(401);
        $this->getJson(route('delivery.check_assignments'))
            ->assertStatus(401);

        // Register delivery partner with formatted long name/phone & empty fleet role auto-creation
        $riderRes = $this->post(route('delivery.register'), [
            'name' => str_repeat('Rider', 11) . ' ' . str_repeat('Last', 8),
            'phone' => '+91-98765-11223',
            'email' => '  RIDER.NEW@EXAMPLE.COM ',
            'vehicle_type' => 'bike',
            'city' => 'Kishangarh',
            'state' => 'Rajasthan',
        ]);
        $riderRes->assertRedirect(route('delivery.dashboard'));
        $riderUser = User::where('email', 'rider.new@example.com')->first();
        $this->assertNotNull($riderUser);
        $this->assertLessThanOrEqual(20, strlen($riderUser->username));
        $this->assertLessThanOrEqual(50, strlen($riderUser->first_name));
        $this->assertLessThanOrEqual(15, strlen((string) $riderUser->phone));

        // 8. Guest cart, checkout page, & guest checkout with variantless product, long name & formatted phone
        $this->post(route('logout'));
        VendorSetting::where('vendor_id', $freelancer->vendor->id)->update(['cod_enabled' => true]);

        $this->postJson(route('cart.add'), [
            'product_id' => $variantlessProd->id,
            'quantity' => 1,
        ])->assertOk()->assertJson(['success' => true]);

        $this->get(route('cart.index'))->assertOk();
        $this->get(route('checkout.vendor', $freelancer->vendor->id))->assertOk();

        $checkoutRes = $this->post(route('checkout.process', $freelancer->vendor->id), [
            'name' => str_repeat('Guest', 11) . ' ' . str_repeat('Last', 8),
            'email' => 'GUESTCHECKOUT2026@EXAMPLE.COM',
            'phone' => '+91 98765-09876',
            'address_line_1' => '12 Marble Market Road',
            'city' => 'Kishangarh',
            'state' => 'Rajasthan',
            'pincode' => '305801',
            'payment_method' => 'cod',
            'delivery_mode' => 'express',
        ]);
        $checkoutRes->assertRedirect();

        $guestUser = User::where('email', 'guestcheckout2026@example.com')->first();
        $this->assertNotNull($guestUser);
        $this->assertLessThanOrEqual(20, strlen($guestUser->username));
        $this->assertLessThanOrEqual(50, strlen($guestUser->first_name));
        $this->assertLessThanOrEqual(50, strlen($guestUser->last_name));
        $this->assertLessThanOrEqual(15, strlen((string) $guestUser->phone));
    }

    public function test_used_classified_on_empty_vendor_database_succeeds_without_vendor_record_and_validates_cleanly(): void
    {
        $this->assertSame(0, Vendor::count());

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        // Invalid payload returns clean 422 JSON validation error instead of 500
        $invalidRes = $this->postJson(route('used.post-ad'), [
            'title' => 'Bad',
            'price' => 0,
            'city' => '',
        ]);
        $invalidRes->assertStatus(422);

        // Valid individual classified succeeds cleanly even with 0 vendor records
        $res = $this->postJson(route('used.post-ad'), [
            'title' => 'Used Wooden Sofa',
            'category' => 'Furniture',
            'price' => 4500,
            'city' => 'Jaipur',
            'phone' => '9876543210',
            'description' => 'Solid wood sofa set.',
        ]);

        $res->assertOk();
        $res->assertJson(['success' => true]);
        $this->assertDatabaseHas('products', [
            'user_id' => $user->id,
            'vendor_id' => null,
            'vertical' => 'used',
            'title' => 'Used Wooden Sofa',
        ]);
    }
}
