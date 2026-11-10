<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentClaim;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorDashboardPaymentClaimsTest extends TestCase
{
    use RefreshDatabase;

    private function createVendor(string $slug, string $shopName, string $vendorType = 'electronics', ?array $pickupAddress = null): array
    {
        $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $user = User::factory()->create();
        $user->roles()->syncWithoutDetaching([$role->id]);

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => $shopName,
            'slug' => $slug,
            'vendor_type' => $vendorType,
            'status' => 'approved',
            'pickup_address' => $pickupAddress,
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

    private function createUpiOrder(User $buyer, Vendor $vendor, string $orderNumber, string $paymentStatus = 'pending', string $status = 'pending'): Order
    {
        return Order::create([
            'user_id' => $buyer->id,
            'vendor_id' => $vendor->id,
            'order_number' => $orderNumber,
            'item_count' => 1,
            'subtotal' => 250000,
            'shipping_amount' => 5000,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 255000,
            'status' => $status,
            'payment_method' => 'upi',
            'payment_status' => $paymentStatus,
            'address_snapshot' => [
                'line_1' => '123 Tech Street',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302001',
                'phone' => '9876543210',
            ],
        ]);
    }

    public function test_multiple_vendors_with_unclaimed_upi_orders_can_load_dashboard_without_duplicate_utr_500(): void
    {
        $buyer = User::factory()->create();
        [$firstVendorUser, $firstVendor] = $this->createVendor('first-store', 'First Store', 'clothing');
        [$gadditoUser, $gadditoVendor] = $this->createVendor('gaddito', 'Gaddito', 'electronics', ['lat' => '26.5744']); // partial pickup_address missing lng

        $firstOrder = $this->createUpiOrder($buyer, $firstVendor, 'ORD-FIRST-1');
        $gadditoOrderOne = $this->createUpiOrder($buyer, $gadditoVendor, 'ORD-GAD-1', 'pending', 'pending');
        $gadditoOrderTwo = $this->createUpiOrder($buyer, $gadditoVendor, 'ORD-GAD-2', 'processing', 'processing');
        $gadditoCancelled = $this->createUpiOrder($buyer, $gadditoVendor, 'ORD-GAD-CAN', 'pending', 'cancelled');

        // Legacy WAITING_PROOF claim already exists on another vendor's order
        PaymentClaim::create([
            'order_id' => $firstOrder->id,
            'user_id' => $buyer->id,
            'utr_number' => 'WAITING_PROOF',
            'amount_claimed' => $firstOrder->total_amount,
            'status' => 'pending',
        ]);

        // First vendor loads dashboard cleanly
        $this->actingAs($firstVendorUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertSee('ORD-FIRST-1');

        // Second vendor (gaddito) loads dashboard with multiple unclaimed UPI orders without hitting SQL 1062
        $response = $this->actingAs($gadditoUser)->get(route('vendor.dashboard'));
        $response->assertOk()
            ->assertSee('Gaddito')
            ->assertSee('ORD-GAD-1')
            ->assertSee('ORD-GAD-2')
            ->assertSee('Not provided')
            ->assertViewHas('pendingPaymentClaims', function ($claims) use ($gadditoOrderOne, $gadditoOrderTwo, $gadditoCancelled) {
                $orderIds = $claims->pluck('order_id')->all();
                return in_array($gadditoOrderOne->id, $orderIds, true)
                    && in_array($gadditoOrderTwo->id, $orderIds, true)
                    && !in_array($gadditoCancelled->id, $orderIds, true);
            });

        // Ensure GET /vendor/dashboard does not insert synthetic WAITING_PROOF rows
        $this->assertDatabaseMissing('payment_claims', ['order_id' => $gadditoOrderOne->id]);
        $this->assertDatabaseMissing('payment_claims', ['order_id' => $gadditoOrderTwo->id]);
    }

    public function test_vendor_can_approve_and_reject_unclaimed_upi_orders_from_dashboard(): void
    {
        $buyer = User::factory()->create();
        [$gadditoUser, $gadditoVendor] = $this->createVendor('gaddito', 'Gaddito', 'electronics');

        $orderToApprove = $this->createUpiOrder($buyer, $gadditoVendor, 'ORD-APP-1');
        $orderToReject = $this->createUpiOrder($buyer, $gadditoVendor, 'ORD-REJ-1');

        $this->actingAs($gadditoUser)
            ->post(route('vendor.orders.approve_payment', $orderToApprove))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $orderToApprove->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);

        $this->actingAs($gadditoUser)
            ->post(route('vendor.orders.reject_payment', $orderToReject), [
                'rejection_reason' => 'UTR not credited yet',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $orderToReject->id,
            'payment_status' => 'failed',
        ]);

        // Dashboard reflects updated state cleanly (neither approved nor rejected order remains in pendingPaymentClaims)
        $this->actingAs($gadditoUser)
            ->get(route('vendor.dashboard'))
            ->assertOk()
            ->assertViewHas('pendingPaymentClaims', fn ($claims) => $claims->isEmpty())
            ->assertSee('ORD-APP-1')
            ->assertSee('ORD-REJ-1');
    }

    public function test_duplicate_utr_submission_returns_validation_error_instead_of_500(): void
    {
        $buyer = User::factory()->create();
        [, $vendor] = $this->createVendor('gaddito', 'Gaddito', 'electronics');

        $orderOne = $this->createUpiOrder($buyer, $vendor, 'ORD-UTR-1');
        $orderTwo = $this->createUpiOrder($buyer, $vendor, 'ORD-UTR-2');

        PaymentClaim::create([
            'order_id' => $orderOne->id,
            'user_id' => $buyer->id,
            'utr_number' => '412345678901',
            'amount_claimed' => $orderOne->total_amount,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($buyer)
            ->post(route('checkout.payment.process', $orderTwo), [
                'upi_transaction_id' => '412345678901',
            ]);

        $response->assertSessionHasErrors('upi_transaction_id');
    }

    public function test_cleanup_migration_clears_legacy_waiting_proof_values(): void
    {
        $buyer = User::factory()->create();
        [, $vendor] = $this->createVendor('gaddito', 'Gaddito', 'electronics');
        $order = $this->createUpiOrder($buyer, $vendor, 'ORD-MIG-1');

        PaymentClaim::create([
            'order_id' => $order->id,
            'user_id' => $buyer->id,
            'utr_number' => 'WAITING_PROOF',
            'amount_claimed' => $order->total_amount,
            'status' => 'pending',
        ]);

        $migration = require database_path('migrations/2026_11_10_100000_clean_waiting_proof_utr_in_payment_claims.php');
        $migration->up();

        $this->assertDatabaseHas('payment_claims', [
            'order_id' => $order->id,
            'utr_number' => null,
        ]);
    }
}
