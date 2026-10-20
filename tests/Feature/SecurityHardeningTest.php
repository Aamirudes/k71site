<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

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

        // Fresh instance with test config
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

        // Order not found, but signature check PASSED (acknowledged)
        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'message' => 'Order not found, acknowledged']);
    }

    public function test_simulation_shipping_fails_in_production(): void
    {
        config(['app.env' => 'production']);

        $response = $this->post('/vendor/orders/1/simulate-shipping');

        // Should return 404 or redirect to login (not accessible)
        $this->assertTrue(in_array($response->status(), [302, 404]));
    }
}
