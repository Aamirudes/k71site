<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductGigPackage;
use App\Models\ProductImage;
use App\Models\Role;
use App\Models\ServiceOrder;
use App\Models\ServiceOrderMessage;
use App\Models\Short;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FreelanceMarketplaceAndAdminSuperpowersTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $buyerUser;
    protected User $vendorUser;
    protected Vendor $vendor;
    protected Category $category;
    protected Product $service;
    protected ProductGigPackage $basicPackage;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        // Admin User
        $this->adminUser = User::firstOrCreate(
            ['email' => 'crab.hashir@gmail.com'],
            [
                'first_name' => 'Hashir',
                'last_name' => 'Admin',
                'username' => 'masteradmin',
                'password' => bcrypt('AdminPassword123!'),
            ]
        );
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $this->adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        // Buyer User
        $this->buyerUser = User::factory()->create([
            'first_name' => 'Rajesh',
            'last_name' => 'Kumar',
            'username' => 'rajesh' . rand(100, 999),
        ]);

        // Vendor User & Vendor Profile
        $this->vendorUser = User::factory()->create([
            'first_name' => 'Aarav',
            'last_name' => 'Design',
            'username' => 'aarav' . rand(100, 999),
        ]);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'PixelForge Creative Lab',
            'slug' => 'pixelforge-creative-lab-' . rand(100, 999),
            'vendor_type' => 'service',
            'status' => 'approved',
            'tagline' => 'Full-Stack Branding & UI/UX Studio',
            'description' => 'We create exceptional brand systems, digital products, and design systems.',
            'city' => 'Bengaluru',
            'phone' => '9876543210',
            'email' => 'hello@pixelforge.test',
            'hourly_rate' => 2000,
            'rating' => 5.0,
            'completed_projects' => 45,
            'is_verified_pro' => true,
            'skills_json' => ['Figma', 'UI/UX', 'Logo Design', 'TailwindCSS'],
            'languages_json' => ['English', 'Hindi'],
        ]);

        // Category
        $this->category = Category::firstOrCreate(
            ['slug' => 'test-branding-design'],
            [
                'name' => 'Branding & Identity',
                'vertical' => 'services',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        // Service Product
        $this->service = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'title' => 'Minimalist Brand Identity and Logo System',
            'slug' => 'minimalist-brand-identity-system-' . rand(100, 999),
            'description' => 'Complete corporate logo design with vector master files and social media assets.',
            'vertical' => 'services',
            'listing_type' => 'gig',
            'base_price' => 199900,
            'gst_rate' => 18,
            'status' => 'active',
            'faqs_json' => [
                ['q' => 'Do you provide source files?', 'a' => 'Yes, all vector formats AI, EPS, SVG are included.']
            ],
            'addons_json' => [
                ['name' => 'Fast 24h Delivery', 'price' => 999, 'description' => 'Draft within 24 hours.']
            ],
            'requirements_prompt' => 'Please provide company name, tagline, and brand colors.',
        ]);

        ProductImage::create([
            'product_id' => $this->service->id,
            'path' => 'https://images.unsplash.com/photo-1626785774573-4b799315345d?auto=format&fit=crop&w=800&q=80',
            'alt_text' => $this->service->title,
            'sort_order' => 0,
        ]);

        // Packages
        $this->basicPackage = ProductGigPackage::create([
            'product_id' => $this->service->id,
            'tier' => 'basic',
            'title' => 'Starter Logo Kit',
            'description' => '2 Logo concepts + PNG & SVG files',
            'price' => 1999.00,
            'delivery_days' => 2,
            'revisions' => 2,
            'features_json' => ['2 Concepts', 'Print Files', 'Vector SVG'],
        ]);

        ProductGigPackage::create([
            'product_id' => $this->service->id,
            'tier' => 'standard',
            'title' => 'Standard Brand Pack',
            'description' => '3 Concepts + Source files + Social media kit',
            'price' => 4999.00,
            'delivery_days' => 4,
            'revisions' => 5,
            'features_json' => ['3 Concepts', 'Source Files (AI, EPS)', 'Social Media Kit'],
        ]);

        ProductGigPackage::create([
            'product_id' => $this->service->id,
            'tier' => 'premium',
            'title' => 'Corporate Identity Suite',
            'description' => '5 Concepts + Brand book + Stationery kit',
            'price' => 9999.00,
            'delivery_days' => 7,
            'revisions' => 10,
            'features_json' => ['5 Concepts', 'Brand Guidelines Book', 'Stationery Designs', 'Unlimited Revisions'],
        ]);
    }

    /**
     * 1. Services Discovery Hub renders correctly with purple identity.
     */
    public function test_services_discovery_hub_renders_successfully(): void
    {
        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('PixelForge Creative Lab');
        $response->assertSee('Minimalist Brand Identity and Logo System');
        $response->assertSee('Branding & Identity');
        $response->assertSee('Explore Services by Category');
        $response->assertSee('Trending Services & Packages');
    }

    /**
     * 2. Freelance Service Detail page shows 3-tier packages, FAQs, and seller summary.
     */
    public function test_service_detail_page_shows_packages_and_faqs(): void
    {
        $response = $this->get('/services/' . $this->service->slug);

        $response->assertStatus(200);
        $response->assertSee('Starter Logo Kit');
        $response->assertSee('Standard Brand Pack');
        $response->assertSee('Corporate Identity Suite');
        $response->assertSee('Do you provide source files?');
        $response->assertSee('Continue to Order');
        $response->assertSee('PixelForge Creative Lab');
    }

    /**
     * 3. Freelancer profile displays skills, hourly rate, and active gigs.
     */
    public function test_freelancer_profile_page_displays_skills_and_gigs(): void
    {
        $response = $this->get('/services/provider/' . $this->vendor->slug);

        $response->assertStatus(200);
        $response->assertSee('PixelForge Creative Lab');
        $response->assertSee('Figma');
        $response->assertSee('TailwindCSS');
        $response->assertSee('Minimalist Brand Identity and Logo System');
    }

    /**
     * 4. Buyer can reach checkout and place freelance service order.
     */
    public function test_buyer_can_place_service_order_and_enter_workspace(): void
    {
        $this->actingAs($this->buyerUser);

        $checkoutResponse = $this->get('/services/order/' . $this->service->id . '?tier=basic');
        $checkoutResponse->assertStatus(200);
        $checkoutResponse->assertSee('Order Customization & Requirements');

        $orderPayload = [
            'tier' => 'basic',
            'requirements' => 'Here are our branding specifications. We need a luxury modern logo for our tech company.',
            'selected_addons' => ['Fast 24h Delivery'],
        ];

        $postResponse = $this->post('/services/order/' . $this->service->id, $orderPayload);

        $this->assertDatabaseHas('service_orders', [
            'buyer_id' => $this->buyerUser->id,
            'vendor_id' => $this->vendor->id,
            'product_id' => $this->service->id,
            'package_tier' => 'basic',
            'status' => 'in_progress',
        ]);

        $order = ServiceOrder::where('buyer_id', $this->buyerUser->id)->latest()->first();
        $this->assertNotNull($order);
        $postResponse->assertRedirect(route('services.workspace', $order));

        // Test workspace view
        $workspaceResponse = $this->get(route('services.workspace', $order));
        $workspaceResponse->assertStatus(200);
        $workspaceResponse->assertSee($order->order_number);
        $workspaceResponse->assertSee('Project Discussion & Work Updates');
    }

    /**
     * 5. Workspace allows project messaging, deliverables submission, and revisions.
     */
    public function test_workspace_deliverables_and_completion_lifecycle(): void
    {
        $order = ServiceOrder::create([
            'order_number' => 'SO-TEST12345',
            'buyer_id' => $this->buyerUser->id,
            'vendor_id' => $this->vendor->id,
            'product_id' => $this->service->id,
            'gig_package_id' => $this->basicPackage->id,
            'package_tier' => 'basic',
            'package_title' => $this->basicPackage->title,
            'amount' => 199900,
            'delivery_days' => 2,
            'revisions_allowed' => 2,
            'revisions_used' => 0,
            'status' => 'in_progress',
            'requirements' => 'Make a modern blue logo.',
        ]);

        // Buyer sends message
        $this->actingAs($this->buyerUser);
        $msgResponse = $this->post(route('services.workspace.message', $order), [
            'message' => 'Hello provider, when will the first draft be ready?',
        ]);
        $msgResponse->assertSessionHas('success');
        $this->assertDatabaseHas('service_order_messages', [
            'service_order_id' => $order->id,
            'user_id' => $this->buyerUser->id,
            'message' => 'Hello provider, when will the first draft be ready?',
        ]);

        // Freelancer submits delivery
        $this->actingAs($this->vendorUser);
        $file = UploadedFile::fake()->create('logo_final.zip', 500);

        $delivResponse = $this->post(route('services.workspace.deliver', $order), [
            'notes' => 'Here are the completed logo files in all formats.',
            'delivery_files' => [$file],
        ]);
        $delivResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('delivered', $order->status);
        $this->assertNotNull($order->deliveries);

        // Buyer requests a revision
        $this->actingAs($this->buyerUser);
        $revResponse = $this->post(route('services.workspace.revision', $order), [
            'revision_notes' => 'Please make the shade of blue slightly darker.',
        ]);
        $revResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('revision_requested', $order->status);
        $this->assertEquals(1, $order->revisions_used);

        // Freelancer delivers again
        $this->actingAs($this->vendorUser);
        $file2 = UploadedFile::fake()->create('logo_revised.zip', 500);
        $this->post(route('services.workspace.deliver', $order), [
            'notes' => 'Adjusted blue color per your feedback.',
            'delivery_files' => [$file2],
        ]);

        $order->refresh();
        $this->assertEquals('delivered', $order->status);

        // Buyer approves and marks complete with 5-star review
        $this->actingAs($this->buyerUser);
        $approveResponse = $this->post(route('services.workspace.approve', $order), [
            'rating' => 5,
            'review_text' => 'Outstanding work and super fast delivery!',
        ]);
        $approveResponse->assertSessionHas('success');

        $order->refresh();
        $this->assertEquals('completed', $order->status);
        $this->assertNotNull($order->completed_at);
        $this->assertEquals(5, $order->buyer_rating);
    }

    /**
     * 6. Freelancer guided onboarding (become seller).
     */
    public function test_freelancer_onboarding_wizard(): void
    {
        $newUser = User::factory()->create([
            'first_name' => 'Kunal',
            'last_name' => 'Developer',
        ]);
        $this->actingAs($newUser);

        $viewResponse = $this->get(route('services.become_seller'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Become a K71 Service Provider');

        $postResponse = $this->post(route('services.store_seller'), [
            'shop_name' => 'Kunal Web Innovations',
            'tagline' => 'Specialist React and Laravel Developer',
            'description' => 'I build scalable web apps and e-commerce portals.',
            'skills' => 'React, Laravel, MySQL, Docker',
            'languages' => 'English, Hindi',
            'hourly_rate' => 1500,
            'city' => 'Pune',
            'contact_phone' => '9876500000',
        ]);

        $postResponse->assertRedirect(route('vendor.services.index'));
        $this->assertDatabaseHas('vendors', [
            'user_id' => $newUser->id,
            'shop_name' => 'Kunal Web Innovations',
            'vendor_type' => 'service',
        ]);
    }

    /**
     * 7. Vendor Freelance Studio allows gig creation.
     */
    public function test_vendor_service_studio_create_gig(): void
    {
        $this->actingAs($this->vendorUser);

        $indexRes = $this->get(route('vendor.services.index'));
        $indexRes->assertStatus(200);

        $createRes = $this->get(route('vendor.services.create'));
        $createRes->assertStatus(200);

        $img = UploadedFile::fake()->image('service_cover.jpg');

        $storeRes = $this->post(route('vendor.services.store'), [
            'title' => 'Complete Flutter Mobile Application for iOS and Android',
            'category_id' => $this->category->id,
            'description' => 'End to end mobile application development with responsive screens and backend API integration.',
            'basic_title' => 'Basic App Screen',
            'basic_description' => '1 Screen UI mockup in Flutter',
            'basic_price' => 2999,
            'basic_delivery_days' => 3,
            'basic_revisions' => 2,
            'basic_features' => "1 Screen\nFlutter code",
            'images' => [$img],
        ]);

        $storeRes->assertRedirect(route('vendor.services.index'));
        $this->assertDatabaseHas('products', [
            'vendor_id' => $this->vendor->id,
            'title' => 'Complete Flutter Mobile Application for iOS and Android',
            'vertical' => 'services',
        ]);
    }

    /**
     * 8. Master Admin Superpowers: Manage Users, change roles, and delete user.
     */
    public function test_admin_superpowers_manage_and_delete_users(): void
    {
        $this->actingAs($this->adminUser);
        session()->put('admin_authenticated', true);
        session()->put('admin_2fa_verified', true);

        // Visit Admin Users management
        $response = $this->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('User Directory &amp; Access Controls', false);
        $response->assertSee($this->buyerUser->email);

        // Delete user superpower
        $userToDelete = User::factory()->create(['first_name' => 'Spam', 'last_name' => 'User']);
        $deleteResponse = $this->delete(route('admin.users.destroy', $userToDelete));
        $deleteResponse->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['id' => $userToDelete->id]);
    }

    /**
     * 9. Master Admin Superpowers: Moderate and delete Services.
     */
    public function test_admin_superpowers_moderate_and_delete_services(): void
    {
        $this->actingAs($this->adminUser);
        session()->put('admin_authenticated', true);
        session()->put('admin_2fa_verified', true);

        $response = $this->get(route('admin.services.index'));
        $response->assertStatus(200);
        $response->assertSee('Services &amp; Gigs Moderation Center', false);
        $response->assertSee($this->service->title);

        // Toggle service status
        $statusResponse = $this->patch(route('admin.services.status', $this->service), [
            'status' => 'draft',
        ]);
        $statusResponse->assertSessionHas('success');
        $this->assertEquals('draft', $this->service->fresh()->status);

        // Delete service superpower
        $deleteResponse = $this->delete(route('admin.services.destroy', $this->service));
        $deleteResponse->assertSessionHas('success');
        $this->assertSoftDeleted('products', ['id' => $this->service->id]);
    }

    /**
     * 10. Master Admin Superpowers: Delete Products, Shorts, and Vendors.
     */
    public function test_admin_superpowers_delete_products_shorts_and_vendors(): void
    {
        $this->actingAs($this->adminUser);
        session()->put('admin_authenticated', true);
        session()->put('admin_2fa_verified', true);

        // Delete product
        $prod = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'title' => 'Product To Delete',
            'slug' => 'prod-to-delete-' . rand(100, 999),
            'description' => 'Test delete',
            'base_price' => 50000,
            'gst_rate' => 18,
            'status' => 'active',
        ]);
        $prodDelRes = $this->delete(route('admin.products.destroy', $prod));
        $prodDelRes->assertSessionHas('success');
        $this->assertSoftDeleted('products', ['id' => $prod->id]);

        // Delete short
        $short = Short::create([
            'vendor_id' => $this->vendor->id,
            'video_url' => 'https://example.com/video.mp4',
            'title' => 'Short To Delete',
            'slug' => 'short-to-delete-' . rand(100, 999),
            'caption' => 'Test Caption',
            'status' => 'approved',
        ]);
        $shortDelRes = $this->delete(route('admin.shorts.destroy', $short));
        $shortDelRes->assertSessionHas('success');
        $this->assertDatabaseMissing('shorts', ['id' => $short->id]);

        // Delete vendor
        $vendorUserToDelete = User::factory()->create();
        $vendorToDelete = Vendor::create([
            'user_id' => $vendorUserToDelete->id,
            'shop_name' => 'Vendor Store To Delete',
            'slug' => 'vendor-to-delete-' . rand(100, 999),
            'status' => 'approved',
        ]);
        $vendorDelRes = $this->delete(route('admin.vendors.destroy', $vendorToDelete));
        $vendorDelRes->assertSessionHas('success');
        $this->assertSoftDeleted('vendors', ['id' => $vendorToDelete->id]);
    }
}
