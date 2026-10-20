<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function createVendorWithUser(string $email, string $shopName, string $slug): array
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
        ]);

        return [$user, $vendor];
    }

    public function test_vendor_b_cannot_edit_vendor_a_product(): void
    {
        [$userA, $vendorA] = $this->createVendorWithUser('vendor_a@test.com', 'Shop A', 'shop-a');
        [$userB, $vendorB] = $this->createVendorWithUser('vendor_b@test.com', 'Shop B', 'shop-b');

        $category = \App\Models\Category::create([
            'name' => 'Fashion',
            'slug' => 'fashion',
            'status' => 'active',
        ]);

        $productA = Product::create([
            'vendor_id' => $vendorA->id,
            'category_id' => $category->id,
            'title' => 'Product A',
            'slug' => 'product-a',
            'description' => 'Product A description',
            'base_price' => 100000,
            'gst_rate' => 12.00,
            'status' => 'active',
        ]);

        // Login as Vendor B and attempt to edit/update Vendor A's product
        $response = $this->actingAs($userB)->get("/vendor/products/{$productA->id}/edit");

        // Should be 404 (due to VendorScope) or 403 (unauthorized)
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_vendor_b_cannot_view_vendor_a_order(): void
    {
        [$userA, $vendorA] = $this->createVendorWithUser('vendor_a@test.com', 'Shop A', 'shop-a');
        [$userB, $vendorB] = $this->createVendorWithUser('vendor_b@test.com', 'Shop B', 'shop-b');

        $orderA = Order::create([
            'order_number' => 'ORD-AAA-001',
            'vendor_id' => $vendorA->id,
            'user_id' => $userA->id,
            'subtotal' => 100000,
            'shipping_amount' => 5000,
            'tax_amount' => 12000,
            'total_amount' => 117000,
            'item_count' => 1,
            'payment_status' => 'paid',
            'payment_method' => 'phonepe',
            'status' => 'processing',
            'address_snapshot' => [
                'name' => 'John Doe',
                'phone' => '9999999999',
                'line_1' => '123 Street',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
            ],
        ]);

        // Login as Vendor B and attempt to view Vendor A's order
        $response = $this->actingAs($userB)->get("/vendor/orders/{$orderA->id}");

        $this->assertTrue(in_array($response->status(), [403, 404]));
    }
}
