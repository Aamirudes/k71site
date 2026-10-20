<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnquiryMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendorUser;
    private Vendor $vendor;
    private Category $enquiryCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create();

        $this->vendorUser = User::factory()->create();
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Marwar Textile Mills',
            'slug' => 'marwar-textile-mills',
            'status' => 'approved',
            'phone' => '9829055443',
            'email' => 'sales@marwartextiles.test',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ]);

        VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => false,
        ]);

        $this->enquiryCategory = Category::firstOrCreate(
            ['slug' => 'enquiry-wholesale-fabrics'],
            [
                'name' => 'Wholesale Fabrics & Apparel',
                'vertical' => 'enquiry',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );
    }

    public function test_enquiry_hub_loads_and_displays_active_rfqs_and_category_filters(): void
    {
        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-TEST01',
            'user_id' => $this->buyer->id,
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Bulk Order: 500 Meters 60x60 Cambric Cotton Fabric',
            'description' => 'Looking for reactive printed 100% cotton cambric fabric for summer kurtis.',
            'quantity' => '500 meters',
            'target_budget' => 6000000, // ₹60,000
            'city' => 'Jaipur',
            'urgency' => 'within_1_week',
            'buyer_name' => 'Ananya Sharma',
            'buyer_phone' => '9829011222',
            'status' => 'open',
            'quotes_count' => 0,
        ]);

        $response = $this->get('/enquiry');

        $response->assertStatus(200);
        $response->assertSee('enquiry.k71.in');
        $response->assertSee('Bulk Order: 500 Meters 60x60 Cambric Cotton Fabric');
        $response->assertSee('500 meters');
        $response->assertSee('₹60,000.00');
        $response->assertSee('Jaipur');
        $response->assertSee('Wholesale Fabrics & Apparel');
    }

    public function test_city_filter_filters_enquiries(): void
    {
        $enqJaipur = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-JPR01',
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Requirement for 1000 Meters Rayon Twill Jaipur',
            'description' => 'Heavy twill rayon needed in Jaipur.',
            'quantity' => '1000 meters',
            'city' => 'Jaipur',
            'buyer_name' => 'Vikram Singh',
            'buyer_phone' => '9829033221',
            'status' => 'open',
        ]);

        $enqSurat = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-SRT01',
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Requirement for 2000 Meters Polyester Georgette Surat',
            'description' => 'Micro polyester georgette needed in Surat.',
            'quantity' => '2000 meters',
            'city' => 'Surat',
            'buyer_name' => 'Dharmesh Patel',
            'buyer_phone' => '9829077665',
            'status' => 'open',
        ]);

        $response = $this->get('/enquiry?city=Jaipur');

        $response->assertStatus(200);
        $response->assertSee('Requirement for 1000 Meters Rayon Twill Jaipur');
        $response->assertDontSee('Requirement for 2000 Meters Polyester Georgette Surat');
    }

    public function test_buyer_can_post_new_requirement_rfq(): void
    {
        $this->actingAs($this->buyer);

        $payload = [
            'title' => 'Need 300 Custom Screen Printed Tote Bags',
            'category_id' => $this->enquiryCategory->id,
            'description' => '10oz canvas natural tote bags with 2-color brand logo for corporate event.',
            'quantity' => '300 units',
            'target_budget' => 25000, // ₹25,000
            'city' => 'Jaipur',
            'pincode' => '302015',
            'urgency' => 'immediate',
            'buyer_name' => 'Nikhil Agarwal',
            'buyer_phone' => '9829088776',
            'buyer_email' => 'nikhil@brandagency.test',
        ];

        $response = $this->post(route('enquiry.store'), $payload);

        $enquiry = Enquiry::where('buyer_phone', '9829088776')->first();
        $this->assertNotNull($enquiry);
        $this->assertEquals('open', $enquiry->status);
        $this->assertEquals(2500000, $enquiry->target_budget); // Stored in paise
        $this->assertEquals('300 units', $enquiry->quantity);
        $this->assertEquals('immediate', $enquiry->urgency);
        $this->assertEquals($this->buyer->id, $enquiry->user_id);

        $response->assertRedirect(route('enquiry.show', $enquiry->enquiry_number));
    }

    public function test_enquiry_submission_validation_requires_title_quantity_and_contact(): void
    {
        $this->actingAs($this->buyer);

        $response = $this->post(route('enquiry.store'), []);

        $response->assertSessionHasErrors(['title', 'description', 'quantity', 'city', 'buyer_name', 'buyer_phone']);
        $this->assertEquals(0, Enquiry::count());
    }

    public function test_vendor_can_view_enquiry_leads_in_dashboard(): void
    {
        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-LEAD1',
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Need 10,000 Custom Printed Corrugated Boxes',
            'description' => '3-ply craft paper corrugated packaging boxes with custom flexo printing.',
            'quantity' => '10,000 pcs',
            'target_budget' => 12000000, // ₹1,20,000
            'city' => 'Jaipur',
            'buyer_name' => 'Sunita Rao',
            'buyer_phone' => '9829044556',
            'status' => 'open',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->get(route('vendor.enquiries.index'));

        $response->assertStatus(200);
        $response->assertSee('ENQ-20261019-LEAD1');
        $response->assertSee('Need 10,000 Custom Printed Corrugated Boxes');
        $response->assertSee('10,000 pcs');
        $response->assertSee('Sunita Rao');
    }

    public function test_vendor_can_submit_quotation_for_enquiry(): void
    {
        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-QUOTE1',
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Requirement for 400 Denim Jackets Wholesale',
            'description' => '14oz washed indigo denim jackets with brass buttons.',
            'quantity' => '400 pieces',
            'target_budget' => 28000000, // ₹2,80,000
            'city' => 'Jaipur',
            'buyer_name' => 'Kunal Bhasin',
            'buyer_phone' => '9829077889',
            'status' => 'open',
            'quotes_count' => 0,
        ]);

        $this->actingAs($this->vendorUser);

        $payload = [
            'quote_amount' => 260000.00, // ₹2,60,000
            'lead_time_days' => 12,
            'notes' => '14oz denim with stone wash finish. Sample ready in 48 hours.',
            'valid_until' => date('Y-m-d', strtotime('+15 days')),
        ];

        $response = $this->post(route('vendor.enquiries.quote', $enquiry->id), $payload);

        $response->assertRedirect();
        
        $quote = Quote::where('enquiry_id', $enquiry->id)->where('vendor_id', $this->vendor->id)->first();
        $this->assertNotNull($quote);
        $this->assertEquals(26000000, $quote->quote_amount); // in paise
        $this->assertEquals(12, $quote->lead_time_days);
        $this->assertEquals('sent', $quote->status);

        $enquiry->refresh();
        $this->assertEquals(1, $enquiry->quotes_count);
        $this->assertEquals('quoted', $enquiry->status);
    }

    public function test_buyer_can_accept_vendor_quote(): void
    {
        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-ACCEPT1',
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Requirement for 250 Luxury Cotton Towel Sets',
            'description' => '600 GSM combed cotton bath sheets with embroidered hotel emblem.',
            'quantity' => '250 sets',
            'target_budget' => 15000000,
            'city' => 'Jaipur',
            'buyer_name' => 'Siddharth Roy',
            'buyer_phone' => '9829099887',
            'status' => 'quoted',
        ]);

        $quote = Quote::create([
            'quote_number' => 'QT-20261019-TEST01',
            'enquiry_id' => $enquiry->id,
            'vendor_id' => $this->vendor->id,
            'quote_amount' => 14000000, // ₹1,40,000
            'lead_time_days' => 8,
            'notes' => 'Direct mill price with GST invoice.',
            'status' => 'sent',
        ]);

        $response = $this->post(route('quotes.accept', $quote->id));

        $response->assertRedirect();
        $quote->refresh();
        $this->assertEquals('accepted', $quote->status);
        $this->assertNotNull($quote->accepted_at);

        $enquiry->refresh();
        $this->assertEquals('closed', $enquiry->status);
    }

    public function test_vendor_cannot_quote_on_closed_enquiry(): void
    {
        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-CLOSED1',
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Finished Requirement: 100 Wooden Display Stands',
            'description' => 'Supplier already contracted.',
            'quantity' => '100 stands',
            'city' => 'Jaipur',
            'buyer_name' => 'Prakash Jain',
            'buyer_phone' => '9829022334',
            'status' => 'closed',
        ]);

        $this->actingAs($this->vendorUser);

        $response = $this->post(route('vendor.enquiries.quote', $enquiry->id), [
            'quote_amount' => 50000.00,
            'lead_time_days' => 5,
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, Quote::count());
    }

    public function test_unauthorized_user_cannot_submit_vendor_quote(): void
    {
        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-UNAUTH1',
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Requirement for 500 Industrial Safety Helmets',
            'description' => 'ISI marked hard hats.',
            'quantity' => '500 pcs',
            'city' => 'Jaipur',
            'buyer_name' => 'Dinesh Sharma',
            'buyer_phone' => '9829011998',
            'status' => 'open',
        ]);

        // 1. Regular buyer (non-vendor) gets redirected by vendor.own middleware
        $this->actingAs($this->buyer);

        $response1 = $this->post(route('vendor.enquiries.quote', $enquiry->id), [
            'quote_amount' => 75000.00,
            'lead_time_days' => 3,
        ]);

        $response1->assertRedirect(route('vendor.register'));
        $this->assertEquals(0, Quote::count());

        // 2. Vendor B tries to quote on Vendor A's direct enquiry
        $directEnquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261019-DIRECT1',
            'vendor_id' => $this->vendor->id, // Directed specifically to Marwar Textile Mills
            'category_id' => $this->enquiryCategory->id,
            'title' => 'Exclusive custom print enquiry for Marwar Mills',
            'description' => 'Direct requirement for Marwar only.',
            'quantity' => '200 pcs',
            'city' => 'Jaipur',
            'buyer_name' => 'Direct Buyer',
            'buyer_phone' => '9829099112',
            'status' => 'open',
        ]);

        $vendorUserB = User::factory()->create();
        $vendorUserB->roles()->syncWithoutDetaching([Role::firstOrCreate(['name' => 'vendor'])->id]);
        Vendor::create([
            'user_id' => $vendorUserB->id,
            'shop_name' => 'Competing Fabrics Ltd',
            'slug' => 'competing-fabrics',
            'status' => 'approved',
        ]);

        $this->actingAs($vendorUserB);

        $response2 = $this->post(route('vendor.enquiries.quote', $directEnquiry->id), [
            'quote_amount' => 60000.00,
            'lead_time_days' => 5,
        ]);

        $response2->assertStatus(403);
        $this->assertEquals(0, Quote::count());
    }
}
