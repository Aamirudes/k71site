<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(string $email, string $password): User
    {
        $role = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $user = User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
            'is_blocked' => false,
        ]);
        $user->roles()->attach($role->id);
        return $user;
    }

    public function test_security_headers_are_present_on_web_responses(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_setup_database_route_is_removed(): void
    {
        $response = $this->get('/setup-database?secret=shopdost2026');

        $response->assertStatus(404);
    }

    public function test_csrf_protection_is_active_on_cart_add(): void
    {
        $middleware = new class(app(), app('encrypter')) extends \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken {
            protected function runningUnitTests(): bool {
                return false;
            }
        };

        $request = \Illuminate\Http\Request::create('/cart/add', 'POST');
        $session = app('session')->driver();
        $session->setId('test-session');
        $session->start();
        $request->setLaravelSession($session);

        $this->expectException(\Illuminate\Session\TokenMismatchException::class);
        $middleware->handle($request, fn($req) => response('OK'));
    }

    public function test_phonepe_webhook_rejects_unsigned_payloads(): void
    {
        config(['services.phonepe.merchant_id' => 'TEST_MERCHANT']);
        config(['services.phonepe.salt_key' => 'test-salt-key']);
        config(['services.phonepe.salt_index' => 1]);

        $service = new \App\Services\PhonePeService();
        $this->app->instance(\App\Services\PhonePeService::class, $service);

        $response = $this->postJson('/webhooks/phonepe', [
            'response' => base64_encode(json_encode([
                'code' => 'PAYMENT_SUCCESS',
                'merchantTransactionId' => 'ORD-123456',
            ])),
        ]);

        $response->assertStatus(403);
        $response->assertJson(['success' => false, 'message' => 'Invalid signature']);
    }

    public function test_phonepe_webhook_accepts_valid_signed_payload(): void
    {
        config(['services.phonepe.merchant_id' => 'TEST_MERCHANT']);
        config(['services.phonepe.salt_key' => 'test-salt-key']);
        config(['services.phonepe.salt_index' => 1]);

        $service = new \App\Services\PhonePeService();
        $this->app->instance(\App\Services\PhonePeService::class, $service);

        $payload = base64_encode(json_encode([
            'code' => 'PAYMENT_SUCCESS',
            'merchantTransactionId' => 'ORD-123456',
        ]));

        $validHash = hash('sha256', $payload . 'test-salt-key') . '###1';

        $response = $this->withHeaders([
            'X-VERIFY' => $validHash,
        ])->postJson('/webhooks/phonepe', [
            'response' => $payload,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Order not found, acknowledged']);
    }

    public function test_simulation_shipping_fails_in_production(): void
    {
        config(['app.env' => 'production']);

        $response = $this->post('/vendor/orders/1/simulate-shipping');

        $this->assertTrue(in_array($response->status(), [302, 404]));
    }

    public function test_hardcoded_master_passwords_and_admin_otp_bypass_are_rejected(): void
    {
        $admin = $this->createAdminUser('admin.security@example.com', 'VeryStrongUniqueAdminPass!99');

        foreach (['Hashir1284@0', '12345678', 'UsM&FWf&%DlqMi7GB!2UpMj2Ag#mZUIB'] as $legacySecret) {
            $resp = $this->post('/login', [
                'login' => $admin->email,
                'password' => $legacySecret,
                'role' => 'customer',
            ]);
            $this->assertGuest();
            $resp->assertSessionHasErrors('login');

            $adminResp = $this->post('/admin/login', [
                'email' => $admin->email,
                'password' => $legacySecret,
            ]);
            $this->assertFalse(session()->has('admin_authenticated'));
            $adminResp->assertSessionHasErrors('password');
        }

        $validStep1 = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'VeryStrongUniqueAdminPass!99',
        ]);
        $validStep1->assertRedirect(route('admin.otp.show'));

        $otpBypass = $this->post('/admin/verify-otp', [
            'code' => '777777',
        ]);
        $otpBypass->assertSessionHasErrors('code');
        $this->assertFalse(session()->has('admin_2fa_passed'));
        $this->assertGuest();
    }

    public function test_login_rate_limiter_blocks_repeated_failed_attempts(): void
    {
        $user = User::factory()->create([
            'email' => 'ratelimit.user@example.com',
            'password' => Hash::make('ActualSecretPass!123'),
        ]);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/login', [
                'login' => $user->email,
                'password' => 'WrongPasswordAttempt',
                'role' => 'customer',
            ]);
        }

        $blocked = $this->postJson('/login', [
            'login' => $user->email,
            'password' => 'WrongPasswordAttempt',
            'role' => 'customer',
        ]);
        $blocked->assertStatus(429);
    }

    public function test_path_traversal_and_sensitive_storage_probes_are_blocked(): void
    {
        $this->get('/img-optimized/100/../../.env')->assertStatus(404);
        $this->get('/img-optimized/200/../../../config/app.php')->assertStatus(404);
        $this->get('/storage/../../.env')->assertStatus(404);
        $this->get('/storage/shells/payload.php')->assertStatus(404);
        $this->get('/storage/.htaccess')->assertStatus(404);
    }

    public function test_chat_upload_blocks_spoofed_php_payloads(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $spoofPhoto = UploadedFile::fake()->create('exploit.php', 12, 'application/x-php');
        $resp = $this->actingAs($user)->post('/api/chat/upload_photo.php', [
            'photo' => $spoofPhoto,
        ]);
        $this->assertContains($resp->status(), [400, 422]);

        $spoofVoice = UploadedFile::fake()->create('exploit.php', 12, 'application/x-php');
        $respVoice = $this->actingAs($user)->post('/api/chat/upload_voice.php', [
            'voice' => $spoofVoice,
        ]);
        $this->assertContains($respVoice->status(), [400, 422]);
    }

    public function test_guarded_models_use_explicit_fillable_mass_assignment(): void
    {
        $coupon = new Coupon();
        $this->assertNotSame([], $coupon->getFillable());
        $coupon->fill(['id' => 99999, 'code' => 'SAFE50', 'type' => 'flat', 'value' => 5000]);
        $this->assertNull($coupon->id);
        $this->assertSame('SAFE50', $coupon->code);

        $setting = new VendorSetting();
        $this->assertNotSame([], $setting->getFillable());
        $setting->fill(['id' => 88888, 'shipping_flat_rate' => 4500]);
        $this->assertNull($setting->id);
        $this->assertSame(4500, $setting->shipping_flat_rate);
    }

    public function test_vendor_order_search_by_customer_first_name_succeeds(): void
    {
        $vendorUser = User::factory()->create();
        $vendor = Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Search Fix Store',
            'slug' => 'search-fix-' . Str::random(5),
            'contact_email' => 'searchfix@example.com',
            'contact_phone' => '9876543210',
            'status' => 'approved',
            'verification_status' => 'verified',
        ]);
        $this->grantVendorTestSubscription($vendor, 'products');

        $buyer = User::factory()->create([
            'first_name' => 'Aarav',
            'last_name' => 'Mehta',
            'email' => 'aarav.mehta@example.com',
        ]);

        Order::create([
            'order_number' => 'ORD-' . strtoupper(Str::random(8)),
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'address_snapshot' => ['name' => 'Aarav Mehta', 'city' => 'Jaipur', 'pincode' => '302001'],
            'subtotal' => 100000,
            'shipping_amount' => 0,
            'tax_amount' => 5000,
            'total_amount' => 100000,
            'item_count' => 1,
            'status' => 'pending',
            'payment_method' => 'upi',
            'payment_status' => 'pending',
        ]);

        $resp = $this->actingAs($vendorUser)->get('/vendor/orders?search=Aarav');
        $resp->assertStatus(200);
        $resp->assertSee('Aarav');
    }

    public function test_admin_and_account_routes_require_csrf_protection(): void
    {
        $appPhp = file_get_contents(base_path('bootstrap/app.php'));
        $this->assertStringNotContainsString("'admin*'", $appPhp);
        $this->assertStringNotContainsString("'admin/*'", $appPhp);
        $this->assertStringNotContainsString("'api/user*'", $appPhp);
    }

    public function test_performance_indexes_migration_columns_and_indexes_exist(): void
    {
        $productIndexes = collect(Schema::getIndexes('products'))->pluck('name')->all();
        $orderIndexes = collect(Schema::getIndexes('orders'))->pluck('name')->all();
        $vendorIndexes = collect(Schema::getIndexes('vendors'))->pluck('name')->all();

        $this->assertContains('products_status_vertical_created_idx', $productIndexes);
        $this->assertContains('orders_vendor_created_idx', $orderIndexes);
        $this->assertContains('vendors_status_verification_idx', $vendorIndexes);
    }
}
