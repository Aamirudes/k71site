<?php

namespace Tests\Feature;

use App\Models\DeliveryPartner;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryPartnerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_visiting_delivery_dashboard_does_not_become_delivery_partner(): void
    {
        $admin = User::factory()->create([
            'email' => 'crab.hashir@gmail.com',
            'first_name' => 'Hashir',
            'last_name' => 'Admin',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $admin->roles()->attach($adminRole);

        $this->assertEquals('admin', $admin->role);
        $this->assertFalse($admin->isDeliveryPartner());

        // Visit delivery dashboard
        $response = $this->actingAs($admin)->get('/delivery/dashboard');
        $response->assertStatus(200);

        // Crucial: Admin account must still be admin and NOT a delivery partner
        $this->assertEquals('admin', $admin->fresh()->role);
        $this->assertFalse($admin->fresh()->isDeliveryPartner());
        $this->assertFalse(DeliveryPartner::where('user_id', $admin->id)->exists());
    }

    public function test_vendor_visiting_delivery_dashboard_does_not_become_delivery_partner(): void
    {
        $vendorUser = User::factory()->create([
            'email' => 'vendor@test.com',
            'first_name' => 'Shop',
            'last_name' => 'Owner',
        ]);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);
        $vendorUser->roles()->attach($vendorRole);
        Vendor::create([
            'user_id' => $vendorUser->id,
            'shop_name' => 'Test Vendor',
            'slug' => 'test-vendor',
            'status' => 'approved',
        ]);

        $this->assertEquals('vendor', $vendorUser->role);
        $this->assertFalse($vendorUser->isDeliveryPartner());

        // Visit delivery dashboard
        $response = $this->actingAs($vendorUser)->get('/delivery/dashboard');
        $response->assertRedirect('/delivery-partner');

        // Crucial: Vendor account must still be vendor and NOT a delivery partner
        $this->assertEquals('vendor', $vendorUser->fresh()->role);
        $this->assertFalse($vendorUser->fresh()->isDeliveryPartner());
        $this->assertFalse(DeliveryPartner::where('user_id', $vendorUser->id)->exists());
    }

    public function test_registering_as_delivery_partner_assigns_fleet_role(): void
    {
        $response = $this->post('/delivery-partner', [
            'name' => 'Ramesh Rider',
            'phone' => '9829011223',
            'email' => 'rider@k71.in',
            'password' => 'secret123',
            'vehicle_type' => 'bike',
            'city' => 'Kishangarh',
            'state' => 'Rajasthan',
        ]);

        $response->assertRedirect('/delivery/dashboard');

        $riderUser = User::where('email', 'rider@k71.in')->first();
        $this->assertNotNull($riderUser);
        $this->assertEquals('fleet', $riderUser->role);
        $this->assertTrue($riderUser->isFleet());
        $this->assertTrue($riderUser->isDeliveryPartner());
        $this->assertFalse($riderUser->isAdmin());
        $this->assertFalse($riderUser->isVendor());
    }
}
