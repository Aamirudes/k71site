<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicesAndBookingsTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendorUser;
    private Vendor $vendor;
    private Category $serviceCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();

        $this->vendorUser = User::factory()->create();
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Apex Cooling & Electricals',
            'slug' => 'apex-cooling-electricals',
            'status' => 'approved',
            'phone' => '9829011223',
            'email' => 'service@apexcooling.test',
            'address' => '77 Civil Lines',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302006',
            'pickup_address' => [
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302006',
            ],
        ]);

        VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => false,
            'service_experience_years' => 8,
            'service_radius_km' => 30,
            'service_working_hours' => '08:00 AM - 08:00 PM',
        ]);

        $this->serviceCategory = Category::firstOrCreate(
            ['slug' => 'services-appliance-repair'],
            [
                'name' => 'AC & Appliance Repair',
                'vertical' => 'services',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    public function test_services_hub_loads_and_displays_service_listings_and_category_filters(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Split AC Deep Foam Cleaning & Service',
            'slug' => 'split-ac-deep-foam-service',
            'description' => 'Complete indoor and outdoor unit jet pump service with antibacterial foam wash.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 59900, // ₹599.00
            'gst_rate' => 18,
            'total_stock' => 50,
            'status' => 'active',
            'custom_fields' => [
                'duration' => '60 mins',
                'service_warranty' => '30 Days Warranty',
            ],
        ]);

        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('services.k71.in');
        $response->assertSee('Apex Cooling & Electricals');
        $response->assertSee('Split AC Deep Foam Cleaning');
        $response->assertSee('₹599.00');
        $response->assertSee('AC & Appliance Repair');
    }

    public function test_city_filter_filters_service_providers(): void
    {
        $serviceJaipur = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Jaipur Inverter AC Repair',
            'slug' => 'jaipur-inverter-ac-repair',
            'description' => 'PCB diagnostics and gas charging.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 120000,
            'gst_rate' => 18,
            'total_stock' => 20,
            'status' => 'active',
        ]);

        $vendorUser2 = User::factory()->create();
        $vendorUser2->roles()->syncWithoutDetaching([Role::firstOrCreate(['name' => 'vendor'])->id]);
        $vendorAjmer = Vendor::create([
            'user_id' => $vendorUser2->id,
            'shop_name' => 'Ajmer Quick Plumber',
            'slug' => 'ajmer-quick-plumber',
            'status' => 'approved',
            'city' => 'Ajmer',
        ]);

        $serviceAjmer = Product::create([
            'vendor_id' => $vendorAjmer->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Ajmer Bathroom Leakage Repair',
            'slug' => 'ajmer-bathroom-leakage-repair',
            'description' => 'Pipe leakage fixing and fitting.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 45000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $response = $this->get('/services?city=Jaipur');

        $response->assertStatus(200);
        $response->assertSee('Jaipur Inverter AC Repair');
        $response->assertDontSee('Ajmer Bathroom Leakage Repair');
    }

    public function test_service_provider_profile_page_displays_details_and_services(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Refrigerator Gas Refilling Service',
            'slug' => 'refrigerator-gas-refill',
            'description' => 'Genuine refrigerant gas refilling with leakage check.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 149900,
            'gst_rate' => 18,
            'total_stock' => 15,
            'status' => 'active',
        ]);

        $response = $this->get('/services/provider/' . $this->vendor->slug);

        $response->assertStatus(200);
        $response->assertSee('Apex Cooling & Electricals');
        $response->assertSee('Refrigerator Gas Refilling Service');
        $response->assertSee('08:00 AM - 08:00 PM');
        $response->assertSee('8 Years Experience');
    }

    public function test_buyer_can_book_appointment_slot_on_service(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Washing Machine Drum Repair',
            'slug' => 'washing-machine-drum-repair',
            'description' => 'Front load and top load washing machine repair.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 85000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($this->buyer);

        $appointmentDate = date('Y-m-d', strtotime('+1 day'));
        $payload = [
            'customer_name' => 'Rohit Sharma',
            'customer_phone' => '9829011999',
            'customer_email' => 'rohit@k71.test',
            'service_address' => 'Plot 42, Vaishali Nagar, Jaipur 302021',
            'booking_date' => $appointmentDate,
            'booking_slot' => '11:00 AM - 01:00 PM',
            'notes' => 'Machine making heavy thumping noise during spin cycle.',
        ];

        $response = $this->post(route('services.book', $service->id), $payload);

        $booking = Booking::where('customer_phone', '9829011999')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals(85000, $booking->total_amount);
        $this->assertEquals($this->vendor->id, $booking->vendor_id);
        $this->assertEquals($service->id, $booking->product_id);
        $this->assertEquals('11:00 AM - 01:00 PM', $booking->booking_slot);

        $response->assertRedirect(route('bookings.show', $booking->booking_number));
    }

    public function test_booking_confirmation_tracker_screen_renders_details(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Microwave Oven Heating Repair',
            'slug' => 'microwave-oven-repair',
            'description' => 'Fix heating element and magnetron issues.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 65000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-20261019-TEST01',
            'product_id' => $service->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'customer_name' => 'Meera Patel',
            'customer_phone' => '9876543210',
            'customer_email' => 'meera@test.com',
            'service_address' => 'B-104 Malviya Nagar, Jaipur',
            'booking_date' => date('Y-m-d', strtotime('+2 days')),
            'booking_slot' => '02:00 PM - 04:00 PM',
            'total_amount' => 65000,
            'status' => 'pending',
            'notes' => 'Doorbell is not working, please call when outside.',
        ]);

        $response = $this->get(route('bookings.show', $booking->booking_number));

        $response->assertStatus(200);
        $response->assertSee('BK-20261019-TEST01');
        $response->assertSee('Meera Patel');
        $response->assertSee('Microwave Oven Heating Repair');
        $response->assertSee('Apex Cooling & Electricals');
        $response->assertSee('02:00 PM - 04:00 PM');
        $response->assertSee('Doorbell is not working');
    }

    public function test_booking_validation_requires_date_slot_and_contact(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Geyser Thermostat Replacement',
            'slug' => 'geyser-thermostat-repair',
            'description' => 'Geyser water not heating.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 45000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $this->actingAs($this->buyer);

        $response = $this->post(route('services.book', $service->id), []);

        $response->assertSessionHasErrors(['customer_name', 'customer_phone', 'service_address', 'booking_date', 'booking_slot']);
        $this->assertEquals(0, Booking::count());
    }

    public function test_vendor_can_view_bookings_in_dashboard(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'RO Water Purifier Filter Service',
            'slug' => 'ro-water-filter-service',
            'description' => 'Complete membrane and sediment filter change.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 79900,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-20261019-VVIEW1',
            'product_id' => $service->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'customer_name' => 'Pooja Verma',
            'customer_phone' => '9829000111',
            'service_address' => '12 C-Scheme, Jaipur',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'booking_slot' => '09:00 AM - 11:00 AM',
            'total_amount' => 79900,
            'status' => 'pending',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->get(route('vendor.bookings.index'));

        $response->assertStatus(200);
        $response->assertSee('BK-20261019-VVIEW1');
        $response->assertSee('Pooja Verma');
        $response->assertSee('RO Water Purifier Filter Service');
        $response->assertSee('09:00 AM - 11:00 AM');
    }

    public function test_vendor_can_confirm_booking(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Chimney Deep Cleaning Service',
            'slug' => 'chimney-deep-cleaning',
            'description' => 'Baffle filter and motor degreasing.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 99900,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-20261019-CONF01',
            'product_id' => $service->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'customer_name' => 'Amitabh Sen',
            'customer_phone' => '9829033445',
            'service_address' => 'Flat 502 Raja Park, Jaipur',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'booking_slot' => '04:00 PM - 06:00 PM',
            'total_amount' => 99900,
            'status' => 'pending',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->put(route('vendor.bookings.status', $booking->id), [
            'status' => 'confirmed',
            'vendor_notes' => 'Technician Vikram assigned with toolkit.',
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('confirmed', $booking->status);
        $this->assertNotNull($booking->confirmed_at);
        $this->assertEquals('Technician Vikram assigned with toolkit.', $booking->vendor_notes);
    }

    public function test_vendor_can_complete_booking(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Ceiling Fan Installation & Wiring',
            'slug' => 'ceiling-fan-installation',
            'description' => 'Safe installation with anchor bolt.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 24900,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-20261019-COMP01',
            'product_id' => $service->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'customer_name' => 'Sunil Gupta',
            'customer_phone' => '9829055667',
            'service_address' => 'Mansarovar Sector 7, Jaipur',
            'booking_date' => date('Y-m-d'),
            'booking_slot' => '11:00 AM - 01:00 PM',
            'total_amount' => 24900,
            'status' => 'confirmed',
            'confirmed_at' => now()->subHours(2),
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->put(route('vendor.bookings.status', $booking->id), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
        $this->assertNotNull($booking->completed_at);
    }

    public function test_vendor_can_cancel_booking_with_reason(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Electrical Short Circuit Inspection',
            'slug' => 'electrical-short-circuit-fix',
            'description' => 'Diagnostic and breaker replacement.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 39900,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-20261019-CANC01',
            'product_id' => $service->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'customer_name' => 'Kavita Joshi',
            'customer_phone' => '9829077889',
            'service_address' => 'Jagatpura Road, Jaipur',
            'booking_date' => date('Y-m-d', strtotime('+3 days')),
            'booking_slot' => '06:00 PM - 08:00 PM',
            'total_amount' => 39900,
            'status' => 'pending',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->put(route('vendor.bookings.status', $booking->id), [
            'status' => 'cancelled',
            'cancelled_reason' => 'Technician unavailable on public holiday',
        ]);

        $response->assertRedirect();
        $booking->refresh();
        $this->assertEquals('cancelled', $booking->status);
        $this->assertNotNull($booking->cancelled_at);
        $this->assertEquals('Technician unavailable on public holiday', $booking->cancelled_reason);
    }

    public function test_unauthorized_vendor_cannot_update_another_vendors_booking(): void
    {
        $service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->serviceCategory->id,
            'title' => 'Water Tank Cleaning 1000L',
            'slug' => 'water-tank-cleaning-1000l',
            'description' => 'UV antibacterial tank sterilization.',
            'vertical' => 'services',
            'listing_type' => 'booking',
            'base_price' => 119900,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $booking = Booking::create([
            'booking_number' => 'BK-20261019-AUTH01',
            'product_id' => $service->id,
            'vendor_id' => $this->vendor->id,
            'customer_name' => 'Deepak Rawat',
            'customer_phone' => '9829099887',
            'service_address' => 'Tonk Road, Jaipur',
            'booking_date' => date('Y-m-d', strtotime('+1 day')),
            'booking_slot' => '09:00 AM - 11:00 AM',
            'total_amount' => 119900,
            'status' => 'pending',
        ]);

        // Create Vendor B
        $vendorUserB = User::factory()->create();
        $vendorUserB->roles()->syncWithoutDetaching([Role::firstOrCreate(['name' => 'vendor'])->id]);
        Vendor::create([
            'user_id' => $vendorUserB->id,
            'shop_name' => 'Other Plumbers',
            'slug' => 'other-plumbers',
            'status' => 'approved',
        ]);

        $this->actingAs($vendorUserB);

        $response = $this->put(route('vendor.bookings.status', $booking->id), [
            'status' => 'confirmed',
        ]);

        $response->assertStatus(403);
        $booking->refresh();
        $this->assertEquals('pending', $booking->status);
    }
}
