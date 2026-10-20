<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Report;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TrustPromotionsAndAdminTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $vendorUser;
    private User $otherVendorUser;
    private Vendor $vendor;
    private Vendor $otherVendor;
    private User $adminUser;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create([
            'first_name' => 'Kavita',
            'last_name' => 'Iyer',
            'email' => 'kavita@example.com',
        ]);

        $this->vendorUser = User::factory()->create([
            'first_name' => 'Meera',
            'last_name' => 'Patel',
            'email' => 'meera@bandhejcrafts.test',
        ]);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Bandhej Heritage Crafts',
            'slug' => 'bandhej-heritage',
            'status' => 'approved',
            'phone' => '9822334455',
            'email' => 'info@bandhejcrafts.test',
            'city' => 'Ahmedabad',
            'state' => 'Gujarat',
            'pincode' => '380001',
        ]);

        VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => true,
        ]);

        // Second Vendor
        $this->otherVendorUser = User::factory()->create([
            'first_name' => 'Rajesh',
            'last_name' => 'Kumar',
            'email' => 'rajesh@othercrafts.test',
        ]);
        $this->otherVendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->otherVendor = Vendor::create([
            'user_id' => $this->otherVendorUser->id,
            'shop_name' => 'Jaipur Blue Pottery',
            'slug' => 'jaipur-blue-pottery',
            'status' => 'approved',
            'phone' => '9822998877',
            'email' => 'sales@bluepottery.test',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302002',
        ]);

        // Admin User (conforming to IsAdmin middleware)
        $this->adminUser = User::factory()->create([
            'first_name' => 'Master',
            'last_name' => 'Admin',
            'email' => 'crab.hashir@gmail.com',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $category = Category::firstOrCreate(
            ['slug' => 'sarees'],
            [
                'name' => 'Sarees',
                'vertical' => 'main',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $category->id,
            'vertical' => 'main',
            'listing_type' => 'purchase',
            'title' => 'Pure Mulberry Silk Bandhani Saree',
            'slug' => 'pure-mulberry-silk-bandhani-saree',
            'description' => 'Authentic hand-tied bandhani saree with zari border.',
            'base_price' => 850000, // ₹8,500
            'gst_rate' => 12,
            'status' => 'active',
            'total_stock' => 10,
        ]);
    }

    public function test_customer_can_submit_product_review(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('reviews.store', $this->product), [
                'rating' => 5,
                'title' => 'Exceptional silk fabric and fast delivery',
                'body' => 'The zari borders and vibrant red bandhani dots are 100% authentic. Completely in love with the drape!',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'rating' => 5,
            'title' => 'Exceptional silk fabric and fast delivery',
            'is_approved' => true,
        ]);
    }

    public function test_vendor_can_reply_to_product_review(): void
    {
        $review = Review::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'rating' => 5,
            'title' => 'Beautiful craftsmanship',
            'body' => 'Really happy with this authentic saree.',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.reviews.reply', $review), [
                'vendor_reply' => 'Thank you Kavita ji! Our Kutch weavers appreciate your generous feedback.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $review->refresh();
        $this->assertTrue($review->hasVendorReply());
        $this->assertEquals('Thank you Kavita ji! Our Kutch weavers appreciate your generous feedback.', $review->vendor_reply);
        $this->assertNotNull($review->vendor_replied_at);
    }

    public function test_unauthorized_vendor_cannot_reply_to_another_vendors_review(): void
    {
        $review = Review::create([
            'product_id' => $this->product->id,
            'user_id' => $this->buyer->id,
            'rating' => 4,
            'title' => 'Nice product',
            'body' => 'Good quality.',
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->otherVendorUser)
            ->post(route('vendor.reviews.reply', $review), [
                'vendor_reply' => 'Imposter response attempt',
            ]);

        $response->assertStatus(403);
        $review->refresh();
        $this->assertFalse($review->hasVendorReply());
    }

    public function test_customer_can_submit_report_against_product(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('reports.store'), [
                'reportable_type' => 'product',
                'reportable_id' => $this->product->id,
                'reason' => 'copyright',
                'details' => 'This design matches my copyrighted pattern.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reports', [
            'user_id' => $this->buyer->id,
            'reportable_type' => Product::class,
            'reportable_id' => $this->product->id,
            'reason' => 'copyright',
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_view_reports_and_resolve_action(): void
    {
        $report = Report::create([
            'user_id' => $this->buyer->id,
            'reportable_type' => Product::class,
            'reportable_id' => $this->product->id,
            'reason' => 'fake_item',
            'details' => 'Synthetic polyester blend falsely advertised as pure mulberry silk.',
            'status' => 'pending',
        ]);

        // Admin views reports index
        $indexResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.reports.index'));

        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Trust &amp; Moderation Queue', false);
        $indexResponse->assertSee('Synthetic polyester blend');

        // Admin resolves report by taking action (blocking the product)
        $resolveResponse = $this->actingAs($this->adminUser)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.reports.resolve', $report), [
                'action' => 'block_product',
                'admin_notes' => 'Lab test confirmed synthetic fibres. Product deactivated.',
            ]);

        $resolveResponse->assertRedirect();
        $resolveResponse->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('action_taken', $report->status);
        $this->assertEquals('block_product', $report->action_taken);
        $this->assertEquals($this->adminUser->id, $report->resolved_by);

        $this->product->refresh();
        $this->assertEquals('inactive', $this->product->status);
    }

    public function test_admin_can_dismiss_unfounded_report(): void
    {
        $report = Report::create([
            'user_id' => $this->buyer->id,
            'reportable_type' => Product::class,
            'reportable_id' => $this->product->id,
            'reason' => 'spam',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.reports.resolve', $report), [
                'action' => 'dismiss',
                'admin_notes' => 'Product is genuine with Silk Mark certification.',
            ]);

        $response->assertRedirect();

        $report->refresh();
        $this->assertEquals('dismissed', $report->status);
        $this->product->refresh();
        $this->assertEquals('active', $this->product->status);
    }

    public function test_admin_can_create_and_toggle_promotion(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.promotions.store'), [
                'title' => 'Diwali Handcraft Festival 2026',
                'badge' => 'DIWALI25',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'min_spend' => 1500,
                'starts_at' => Carbon::now()->subDay()->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::now()->addDays(10)->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $promo = Promotion::where('badge', 'DIWALI25')->first();
        $this->assertNotNull($promo);
        $this->assertTrue($promo->is_active);
        $this->assertTrue($promo->isLive());

        // Admin toggles promotion to disabled
        $this->actingAs($this->adminUser)
            ->withSession(['admin_2fa_passed' => true])
            ->post(route('admin.promotions.toggle', $promo));

        $promo->refresh();
        $this->assertFalse($promo->is_active);
        $this->assertFalse($promo->isLive());
    }

    public function test_admin_can_view_audit_logs(): void
    {
        AuditLog::create([
            'user_id' => $this->adminUser->id,
            'auditable_type' => Product::class,
            'auditable_id' => $this->product->id,
            'action' => 'updated',
            'old_values' => ['status' => 'active'],
            'new_values' => ['status' => 'inactive'],
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->withSession(['admin_2fa_passed' => true])
            ->get(route('admin.audit_logs.index'));

        $response->assertStatus(200);
        $response->assertSee('System Audit Logs');
        $response->assertSee('Product #' . $this->product->id);
    }

    public function test_non_admin_cannot_access_admin_moderation(): void
    {
        // Guest
        $response = $this->get(route('admin.reports.index'));
        $response->assertRedirect(route('admin.login'));

        // Normal Buyer
        $response = $this->actingAs($this->buyer)->get(route('admin.reports.index'));
        $response->assertRedirect(route('home'));

        // Vendor
        $response = $this->actingAs($this->vendorUser)->get(route('admin.reports.index'));
        $response->assertRedirect(route('vendor.dashboard'));
    }
}
