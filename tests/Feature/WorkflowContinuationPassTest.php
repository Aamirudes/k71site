<?php

namespace Tests\Feature;

use App\Http\Controllers\MessageController;
use App\Models\Address;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryPartner;
use App\Models\Enquiry;
use App\Models\Offer;
use App\Models\Order;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\ProjectContract;
use App\Models\ProjectMilestone;
use App\Models\ProjectProposal;
use App\Models\Quote;
use App\Models\Review;
use App\Models\Role;
use App\Models\ServiceOrder;
use App\Models\ServiceProject;
use App\Models\Shipment;
use App\Models\Short;
use App\Models\ShortBoost;
use App\Models\ShortCoin;
use App\Models\SpinPrize;
use App\Models\UsedSellerProfile;
use App\Models\UsedThread;
use App\Models\UsedTransaction;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use App\Models\Wishlist;
use App\Services\PayoutService;
use App\Services\StockManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowContinuationPassTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $secondBuyer;
    private User $vendorUser;
    private Vendor $vendor;
    private Category $mainCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);

        $this->buyer = User::factory()->create([
            'first_name' => 'Riya',
            'last_name' => 'Kapoor',
            'email' => 'riya@workflowtest.in',
        ]);

        $this->secondBuyer = User::factory()->create([
            'first_name' => 'Arjun',
            'last_name' => 'Verma',
            'email' => 'arjun@workflowtest.in',
        ]);

        $this->vendorUser = User::factory()->create([
            'first_name' => 'Karan',
            'last_name' => 'Mehta',
            'email' => 'karan@workflowvendor.in',
        ]);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Mehta Fine Crafts',
            'slug' => 'mehta-fine-crafts',
            'status' => 'approved',
            'phone' => '9829011223',
            'email' => 'shop@mehtacrafts.test',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ]);

        VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => true,
            'is_restaurant' => false,
            'is_accepting_food_orders' => true,
            'storefront_preset' => 'aurel',
        ]);

        $this->grantVendorTestSubscription($this->vendor, 'all');

        $this->mainCategory = Category::firstOrCreate(
            ['slug' => 'handicraft-decor'],
            ['name' => 'Handicraft & Decor', 'vertical' => 'main', 'is_active' => true, 'sort_order' => 1]
        );
    }

    public function test_workflow_1_chat_security_forged_cookie_request_action_reply_spoofing_and_email_redaction(): void
    {
        MessageController::ensureChatSchema();

        // 1. Unauthenticated caller with forged k71_uid cookie MUST receive 401
        $forgedRes = $this->withCookie('k71_uid', (string) $this->buyer->id)
            ->getJson('/api/chat/get_conversations.php');
        $forgedRes->assertStatus(401);

        $u1 = min($this->buyer->id, $this->secondBuyer->id);
        $u2 = max($this->buyer->id, $this->secondBuyer->id);
        $convId = DB::table('chat_conversations')->insertGetId([
            'user_one_id' => $u1,
            'user_two_id' => $u2,
            'status' => 'pending',
            'requested_by' => $this->buyer->id,
            'last_message_at' => now()->toDateTimeString(),
        ]);

        // Unrelated other conversation for reply_to_id cross-thread spoofing check
        $otherConvId = DB::table('chat_conversations')->insertGetId([
            'user_one_id' => min($this->buyer->id, $this->vendorUser->id),
            'user_two_id' => max($this->buyer->id, $this->vendorUser->id),
            'status' => 'accepted',
            'requested_by' => $this->buyer->id,
            'last_message_at' => now()->toDateTimeString(),
        ]);
        $foreignMsgId = DB::table('chat_messages')->insertGetId([
            'conversation_id' => $otherConvId,
            'sender_id' => $this->vendorUser->id,
            'body' => 'Secret foreign thread text',
            'is_read' => 0,
            'created_at' => now()->toDateTimeString(),
        ]);

        // 2. Requester cannot self-accept their own pending request (403)
        $selfAccept = $this->actingAs($this->buyer)->postJson('/api/chat/request_action.php', [
            'conversation_id' => $convId,
            'action' => 'accept',
        ]);
        $selfAccept->assertStatus(403);

        // 3. Requester follow-up preserves 'pending' status (does not auto-accept)
        $followUp = $this->actingAs($this->buyer)->postJson('/api/chat/send_message.php', [
            'conversation_id' => $convId,
            'body' => 'Just checking if available',
        ]);
        $followUp->assertStatus(200)
            ->assertJsonPath('conversation_status', 'pending');

        // 4. Email PII is stripped from conversations and search_users
        $convList = $this->actingAs($this->secondBuyer)->getJson('/api/chat/get_conversations.php');
        $convList->assertStatus(200);
        $convJson = $convList->getContent();
        $this->assertStringNotContainsString($this->buyer->email, $convJson);
        $this->assertStringNotContainsString($this->secondBuyer->email, $convJson);

        $searchRes = $this->actingAs($this->secondBuyer)->getJson('/api/chat/search_users.php?q=Riya');
        $searchRes->assertStatus(200);
        $this->assertStringNotContainsString($this->buyer->email, $searchRes->getContent());

        // 5. Recipient accepts request -> works & status becomes accepted
        $acceptRes = $this->actingAs($this->secondBuyer)->postJson('/api/chat/request_action.php', [
            'conversation_id' => $convId,
            'action' => 'accept',
        ]);
        $acceptRes->assertStatus(200)->assertJsonPath('status', 'accepted');

        // 6. reply_to_id pointing to another conversation must NOT leak/spoof reply_to_body
        $spoofReply = $this->actingAs($this->secondBuyer)->postJson('/api/chat/send_message.php', [
            'conversation_id' => $convId,
            'body' => 'Replying normally',
            'reply_to_id' => $foreignMsgId,
            'reply_to_body' => 'Fake injected quote',
        ]);
        $spoofReply->assertStatus(200)
            ->assertJsonPath('message.reply_to_id', null)
            ->assertJsonPath('message.reply_to_body', null);

        // 7. Recipient declines -> subsequent sends into declined conversation blocked with 403
        $declineRes = $this->actingAs($this->secondBuyer)->postJson('/api/chat/request_action.php', [
            'conversation_id' => $convId,
            'action' => 'decline',
        ]);
        $declineRes->assertStatus(200)->assertJsonPath('status', 'declined');

        $sendAfterDecline = $this->actingAs($this->buyer)->postJson('/api/chat/send_message.php', [
            'conversation_id' => $convId,
            'body' => 'Trying after decline',
        ]);
        $sendAfterDecline->assertStatus(403);
    }

    public function test_workflow_2_auction_blocks_owner_self_bid_and_notifies_outbid_buyer(): void
    {
        $auctionProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'vertical' => 'main',
            'listing_type' => 'auction',
            'title' => 'Royal Makrana Marble Urn',
            'slug' => 'royal-makrana-marble-urn',
            'description' => 'Museum grade marble sculpture.',
            'base_price' => 200000, // ₹2,000
            'bid_increment' => 20000, // ₹200
            'gst_rate' => 18,
            'auction_status' => 'live',
            'auction_starts_at' => Carbon::now()->subHour(),
            'auction_ends_at' => Carbon::now()->addDay(),
            'status' => 'active',
            'total_stock' => 1,
        ]);

        // Vendor cannot bid on own auction
        $ownerBid = $this->actingAs($this->vendorUser)
            ->post(route('auctions.bid', $auctionProduct), ['bid_amount' => 2500]);
        $ownerBid->assertSessionHas('error');
        $this->assertDatabaseCount('bids', 0);

        // Buyer 1 places opening bid (₹2,000)
        $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $auctionProduct), ['bid_amount' => 2000])
            ->assertSessionHas('success');

        // Buyer 2 outbids (₹2,200) -> Buyer 1 gets outbid notification
        $this->actingAs($this->secondBuyer)
            ->post(route('auctions.bid', $auctionProduct), ['bid_amount' => 2200])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->buyer->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_workflow_3_used_marketplace_duplicate_offer_guard_and_thread_reply_checks(): void
    {
        $usedCategory = Category::firstOrCreate(
            ['slug' => 'used-audio'],
            ['name' => 'Used Audio', 'vertical' => 'used', 'is_active' => true, 'sort_order' => 1]
        );

        $usedItem = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $usedCategory->id,
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'title' => 'Sony WH-1000XM5 Headphones',
            'slug' => 'sony-xm5-used',
            'description' => 'Lightly used with carry case and bill.',
            'base_price' => 1800000, // ₹18,000
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
            'is_negotiable' => true,
            'custom_fields' => ['condition' => 'Like New'],
        ]);

        // First active offer succeeds
        $first = $this->actingAs($this->buyer)->postJson(route('offers.store', $usedItem), [
            'offer_amount' => 16000,
            'message' => 'Can pick up today.',
        ]);
        $first->assertStatus(200);
        $offerId = $first->json('offer_id');

        // Duplicate active pending offer blocked with 422
        $dup = $this->actingAs($this->buyer)->postJson(route('offers.store', $usedItem), [
            'offer_amount' => 16500,
        ]);
        $dup->assertStatus(422);

        // Withdraw existing offer, then re-offer succeeds
        $this->actingAs($this->buyer)->postJson(route('used.offers.withdraw', $offerId))->assertStatus(200);
        $reoffer = $this->actingAs($this->buyer)->postJson(route('offers.store', $usedItem), [
            'offer_amount' => 16800,
        ]);
        $reoffer->assertStatus(200);

        // Used thread reply blocked if product becomes paused
        $enq = $this->actingAs($this->buyer)->postJson(route('used.enquire', $usedItem), [
            'body' => 'Is battery holding charge?',
        ]);
        $enq->assertStatus(200);
        $threadId = $enq->json('thread_id');

        $usedItem->update(['status' => 'paused']);
        $pausedReply = $this->actingAs($this->buyer)->postJson(route('used.threads.reply', $threadId), [
            'body' => 'Hello?',
        ]);
        $pausedReply->assertStatus(422);
    }

    public function test_workflow_4_feasto_filters_closed_kitchen_dishes_and_zero_stock_and_renders_dynamic_fees(): void
    {
        $foodCategory = Category::firstOrCreate(
            ['slug' => 'thali-meals'],
            ['name' => 'Thali & Meals', 'vertical' => 'food', 'is_active' => true, 'sort_order' => 1]
        );

        // Closed/paused kitchen
        $pausedVendorUser = User::factory()->create();
        $pausedVendor = Vendor::create([
            'user_id' => $pausedVendorUser->id,
            'shop_name' => 'Paused Kitchen',
            'slug' => 'paused-kitchen',
            'vendor_type' => 'food',
            'status' => 'approved',
            'phone' => '9829099887',
            'email' => 'paused@kitchen.test',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
        ]);
        VendorSetting::create([
            'vendor_id' => $pausedVendor->id,
            'is_restaurant' => true,
            'is_accepting_food_orders' => false,
            'food_delivery_fee' => 3500,
        ]);
        $this->grantVendorTestSubscription($pausedVendor, 'food');

        Product::create([
            'vendor_id' => $pausedVendor->id,
            'category_id' => $foodCategory->id,
            'title' => 'Closed Kitchen Biryani',
            'slug' => 'closed-kitchen-biryani',
            'description' => 'Should be hidden on Feasto.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 25000,
            'total_stock' => 10,
            'gst_rate' => 5,
            'status' => 'active',
        ]);

        // Zero-stock dish on active vendor
        $this->vendor->update(['vendor_type' => 'food']);
        $this->vendor->settings()->update(['is_restaurant' => true, 'is_accepting_food_orders' => true, 'food_delivery_fee' => 4500]);

        Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $foodCategory->id,
            'title' => 'Sold Out Paneer Tikka',
            'slug' => 'sold-out-paneer-tikka',
            'description' => 'Zero stock dish.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 22000,
            'total_stock' => 0,
            'gst_rate' => 5,
            'status' => 'active',
        ]);

        Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $foodCategory->id,
            'title' => 'Active Rajasthani Dal Baati',
            'slug' => 'active-rajasthani-dal-baati',
            'description' => 'Freshly prepared Dal Baati Churma.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 28000,
            'total_stock' => 15,
            'gst_rate' => 5,
            'status' => 'active',
        ]);

        $response = $this->get('/feasto');
        $response->assertStatus(200);
        $response->assertSee('Active Rajasthani Dal Baati');
        $response->assertDontSee('Closed Kitchen Biryani');
        $response->assertDontSee('Sold Out Paneer Tikka');
        // No hardcoded fake ₹2 handling fee line item
        $response->assertDontSee('Handling &amp; Platform Fee', false);
        $response->assertSee('deliveryFeeRupees');
    }

    public function test_workflow_5_food_takeaway_checkout_succeeds_without_street_address_and_notifies(): void
    {
        $this->vendor->update(['vendor_type' => 'food']);
        $this->vendor->settings()->update([
            'is_restaurant' => true,
            'is_accepting_food_orders' => true,
            'food_delivery_mode' => 'both',
            'cod_enabled' => true,
        ]);

        $foodCat = Category::firstOrCreate(
            ['slug' => 'street-food'],
            ['name' => 'Street Food', 'vertical' => 'food', 'is_active' => true, 'sort_order' => 2]
        );

        $dish = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $foodCat->id,
            'title' => 'Takeaway Veg Pulao Combo',
            'slug' => 'takeaway-veg-pulao-combo',
            'description' => 'Hot pulao combo.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 19000,
            'total_stock' => 20,
            'gst_rate' => 5,
            'status' => 'active',
        ]);

        $cart = Cart::create([
            'user_id' => $this->buyer->id,
            'session_id' => 'test-takeaway-sess',
        ]);
        $cart->items()->create([
            'product_id' => $dish->id,
            'vendor_id' => $this->vendor->id,
            'quantity' => 1,
            'price_at_add' => 19000,
        ]);

        $checkoutRes = $this->actingAs($this->buyer)
            ->post(route('checkout.process', $this->vendor), [
                'name' => 'Riya Kapoor',
                'phone' => '9829011223',
                'payment_method' => 'cod',
                'order_type' => 'food_takeaway',
                // Deliberately omit address_line_1, city, state, pincode for counter pickup
            ]);

        $checkoutRes->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'order_type' => 'food_takeaway',
        ]);
        $order = \App\Models\Order::where('user_id', $this->buyer->id)->latest('id')->first();
        $this->assertNotEmpty($order->pickup_code);

        // Vendor + buyer both receive DB notification
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->vendorUser->id]);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->buyer->id]);
    }

    public function test_workflow_6_9_reviews_block_self_review_and_notify_vendor_on_customer_review(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'vertical' => 'main',
            'listing_type' => 'purchase',
            'title' => 'Marble Lotus Tray',
            'slug' => 'marble-lotus-tray',
            'description' => 'Handcrafted decor tray.',
            'base_price' => 120000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        // Vendor owner cannot review their own product
        $selfReview = $this->actingAs($this->vendorUser)
            ->post(route('reviews.store', $product), [
                'rating' => 5,
                'title' => 'Best product ever!',
                'body' => 'Fake self review.',
            ]);
        $selfReview->assertStatus(403);
        $this->assertDatabaseCount('reviews', 0);

        // Customer review updates avg_rating and notifies vendor
        $custReview = $this->actingAs($this->buyer)
            ->post(route('reviews.store', $product), [
                'rating' => 4,
                'title' => 'Solid craft',
                'body' => 'Nice marble finish and safe packaging.',
            ]);
        $custReview->assertRedirect();

        $product->refresh();
        $this->assertEquals(1, $product->review_count);
        $this->assertEquals(4.0, (float) $product->avg_rating);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->vendorUser->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_workflow_7_services_milestones_reject_unsafe_file_and_enforce_state_guards(): void
    {
        Storage::fake('public');

        $servicesCat = Category::firstOrCreate(
            ['slug' => 'web-dev'],
            ['name' => 'Web Development', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]
        );

        $project = ServiceProject::create([
            'buyer_id' => $this->buyer->id,
            'category_id' => $servicesCat->id,
            'project_number' => 'PRJ-TEST001',
            'title' => 'Custom Storefront Performance Audit',
            'slug' => 'custom-storefront-performance-audit',
            'description' => 'Detailed Core Web Vitals optimization and server tuning.',
            'budget_type' => 'fixed',
            'budget_min' => 500000,
            'budget_max' => 1000000,
            'work_mode' => 'remote',
            'status' => 'in_progress',
        ]);

        $proposal = ProjectProposal::create([
            'project_id' => $project->id,
            'vendor_id' => $this->vendor->id,
            'bid_amount' => 750000,
            'delivery_days' => 5,
            'cover_letter' => 'Experienced performance engineer ready to start immediately.',
            'status' => 'hired',
        ]);

        $contract = ProjectContract::create([
            'contract_number' => 'CON-TEST0001',
            'project_id' => $project->id,
            'proposal_id' => $proposal->id,
            'buyer_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'agreed_amount' => 750000,
            'payment_status' => 'escrow_funded',
            'status' => 'active',
            'started_at' => now(),
        ]);

        $milestone = ProjectMilestone::create([
            'contract_id' => $contract->id,
            'title' => 'Baseline Audit Report',
            'description' => 'Deliver audit PDF.',
            'amount' => 750000,
            'due_at' => now()->addDays(5)->toDateString(),
            'sort_order' => 1,
            'status' => 'pending',
        ]);

        // 1. Buyer cannot approve pending milestone before submission
        $earlyApprove = $this->actingAs($this->buyer)
            ->post(route('services.milestones.approve', [$contract, $milestone]));
        $earlyApprove->assertSessionHas('error');
        $this->assertEquals('pending', $milestone->fresh()->status);

        // 2. Provider uploading disguised .php deliverable is rejected
        $badFile = UploadedFile::fake()->create('payload.php', 10, 'application/x-php');
        $badSubmit = $this->actingAs($this->vendorUser)
            ->post(route('services.milestones.submit', [$contract, $milestone]), [
                'submitted_notes' => 'Submitting deliverable payload file here.',
                'deliverables' => [$badFile],
            ]);
        $badSubmit->assertSessionHasErrors();
        $this->assertEquals('pending', $milestone->fresh()->status);

        // 3. Valid PDF deliverable succeeds & notifies buyer
        $goodFile = UploadedFile::fake()->create('audit-report.pdf', 100, 'application/pdf');
        $goodSubmit = $this->actingAs($this->vendorUser)
            ->post(route('services.milestones.submit', [$contract, $milestone]), [
                'submitted_notes' => 'Completed full performance audit report attached.',
                'deliverables' => [$goodFile],
            ]);
        $goodSubmit->assertSessionHas('success');
        $this->assertEquals('submitted', $milestone->fresh()->status);
        $this->assertDatabaseHas('notifications', ['notifiable_id' => $this->buyer->id]);

        // 4. Buyer approves submitted milestone & notifies vendor
        $approveRes = $this->actingAs($this->buyer)
            ->post(route('services.milestones.approve', [$contract, $milestone]));
        $approveRes->assertSessionHas('success');
        $this->assertEquals('approved', $milestone->fresh()->status);

        // 5. Buyer cannot request revision on already approved milestone
        $revAfterApprove = $this->actingAs($this->buyer)
            ->post(route('services.milestones.revision', [$contract, $milestone]), [
                'revision_notes' => 'Trying to revert an approved milestone.',
            ]);
        $revAfterApprove->assertSessionHas('error');
        $this->assertEquals('approved', $milestone->fresh()->status);

        // 6. Contract completion succeeds once and guards duplicate completion
        $completeRes = $this->actingAs($this->buyer)
            ->post(route('services.contracts.complete', $contract), [
                'rating' => 5,
                'review_text' => 'Great delivery.',
            ]);
        $completeRes->assertSessionHas('success');
        $this->assertEquals('completed', $contract->fresh()->status);

        $doubleComplete = $this->actingAs($this->buyer)
            ->post(route('services.contracts.complete', $contract));
        $doubleComplete->assertSessionHas('info');
    }

    public function test_workflow_8_video_showcase_uses_real_counts_and_ajax_follow_view_like_share(): void
    {
        $short = Short::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'title' => 'Marble Carving BTS Reel',
            'slug' => 'marble-carving-bts-reel',
            'description' => 'Behind the scenes stone sculpting.',
            'video_url' => 'https://example.com/videos/carving.mp4',
            'thumb_url' => 'https://example.com/thumbs/carving.jpg',
            'likes' => 0,
            'shares' => 0,
            'views' => 0,
            'status' => 'published',
        ]);

        // Rendered storefront video_showcase partial must not inject fake hardcoded counts like 14/6/12/4
        $html = view('storefront.sections.video_showcase', [
            'section' => ['id' => 'shorts', 'title' => 'Reels'],
            'vendorShorts' => collect([$short->fresh('vendor', 'products')]),
            'vendor' => $this->vendor,
        ])->render();

        $this->assertStringContainsString('zsu_record_view', $html);
        $this->assertStringContainsString('zsu_record_like', $html);
        $this->assertStringContainsString('zsu_follow_vendor', $html);
        $this->assertStringNotContainsString('activeReel?.likes || 14', $html);
        $this->assertStringNotContainsString('activeReel?.shares || 6', $html);

        // Live AJAX endpoints increment real database metrics
        $this->postJson('/shorts/ajax', ['action' => 'zsu_record_view', 'short_id' => $short->id])
            ->assertStatus(200);
        $this->actingAs($this->buyer)
            ->postJson('/shorts/ajax', ['action' => 'zsu_record_like', 'short_id' => $short->id])
            ->assertStatus(200);
        $this->actingAs($this->buyer)
            ->postJson('/shorts/ajax', ['action' => 'zsu_record_share', 'short_id' => $short->id])
            ->assertStatus(200);
        $this->actingAs($this->buyer)
            ->postJson('/shorts/ajax', ['action' => 'zsu_follow_vendor', 'vendor_id' => $this->vendor->id])
            ->assertStatus(200);

        $short->refresh();
        $this->assertEquals(1, $short->views);
        $this->assertEquals(1, $short->likes);
        $this->assertEquals(1, $short->shares);
        $this->assertDatabaseHas('short_follows', [
            'follower_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
        ]);
    }

    public function test_workflow_10_enquiry_expired_quote_blocked_vendor_withdraw_quote_and_buyer_close(): void
    {
        $enquiryCat = Category::firstOrCreate(
            ['slug' => 'stone-blocks'],
            ['name' => 'Stone Blocks', 'vertical' => 'enquiry', 'is_active' => true, 'sort_order' => 1]
        );

        $enquiry = Enquiry::create([
            'enquiry_number' => 'ENQ-20261112-WF10',
            'user_id' => $this->buyer->id,
            'category_id' => $enquiryCat->id,
            'title' => 'Need 200 Makrana Marble Slabs',
            'description' => 'Commercial project supply in Jaipur.',
            'quantity' => '200 slabs',
            'city' => 'Jaipur',
            'buyer_name' => 'Riya Kapoor',
            'buyer_phone' => '9829011223',
            'status' => 'quoted',
            'quotes_count' => 2,
        ]);

        // Expired quote cannot be accepted
        $expiredQuote = Quote::create([
            'enquiry_id' => $enquiry->id,
            'vendor_id' => $this->vendor->id,
            'quote_number' => 'QT-EXP001',
            'quote_amount' => 5000000,
            'delivery_timeline' => '7 days',
            'valid_until' => now()->subDays(2)->toDateString(),
            'status' => 'sent',
        ]);

        $acceptExpired = $this->actingAs($this->buyer)->post(route('quotes.accept', $expiredQuote));
        $acceptExpired->assertSessionHas('error');
        $this->assertEquals('sent', $expiredQuote->fresh()->status);

        // Vendor withdraws active sent quote -> status withdrawn, count decremented, enquiry reverts to open
        $expiredQuote->update(['valid_until' => now()->addDays(5)->toDateString()]);
        $enquiry->update(['quotes_count' => 1, 'status' => 'quoted']);

        $withdrawRes = $this->actingAs($this->vendorUser)
            ->post(route('vendor.quotes.withdraw', $expiredQuote));
        $withdrawRes->assertSessionHas('success');
        $this->assertEquals('withdrawn', $expiredQuote->fresh()->status);
        $this->assertEquals(0, $enquiry->fresh()->quotes_count);
        $this->assertEquals('open', $enquiry->fresh()->status);

        // Non-owner cannot close buyer enquiry (403)
        $this->actingAs($this->secondBuyer)
            ->post(route('enquiry.close', $enquiry->enquiry_number))
            ->assertStatus(403);

        // Owner closes enquiry
        $this->actingAs($this->buyer)
            ->post(route('enquiry.close', $enquiry->enquiry_number))
            ->assertSessionHas('success');
        $this->assertEquals('closed', $enquiry->fresh()->status);
    }

    public function test_workflow_11_booking_customer_cancellation_releases_slot_and_notifies_vendor(): void
    {
        $servicesCat = Category::firstOrCreate(
            ['slug' => 'home-repair'],
            ['name' => 'Home Repair', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]
        );

        $serviceProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $servicesCat->id,
            'vertical' => 'services',
            'listing_type' => 'booking',
            'title' => 'Marble Floor Polish Visit',
            'slug' => 'marble-floor-polish-visit',
            'description' => 'Site visit and floor assessment.',
            'base_price' => 150000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $date = now()->addDays(2)->toDateString();

        // Book slot
        $bookRes = $this->actingAs($this->buyer)
            ->post(route('services.book', $serviceProduct), [
                'customer_name' => 'Riya Kapoor',
                'customer_phone' => '9829011223',
                'service_address' => '12 Civil Lines, Jaipur',
                'booking_date' => $date,
                'booking_slot' => '10:00 AM - 12:00 PM',
            ]);
        $bookRes->assertRedirect();

        $booking = Booking::where('product_id', $serviceProduct->id)->firstOrFail();

        // Non-owner signed-in buyer gets 403 trying to cancel another buyer's booking
        $this->actingAs($this->secondBuyer)
            ->post(route('bookings.cancel', $booking->booking_number))
            ->assertStatus(403);

        // Owner cancels booking -> vendor notified and slot released for next buyer
        $this->actingAs($this->buyer)
            ->post(route('bookings.cancel', $booking->booking_number), [
                'cancelled_reason' => 'Rescheduling for next week',
            ])
            ->assertSessionHas('success');

        $this->assertEquals('cancelled', $booking->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->vendorUser->id,
            'notifiable_type' => User::class,
        ]);

        // Another buyer can now book the freed slot
        $rebook = $this->actingAs($this->secondBuyer)
            ->post(route('services.book', $serviceProduct), [
                'customer_name' => 'Arjun Verma',
                'customer_phone' => '9829044556',
                'service_address' => '25 Vaishali Nagar, Jaipur',
                'booking_date' => $date,
                'booking_slot' => '10:00 AM - 12:00 PM',
            ]);
        $rebook->assertRedirect();
        $this->assertEquals(2, Booking::where('product_id', $serviceProduct->id)->count());
    }

    public function test_workflow_12_13_14_order_cancel_and_reject_restore_coupon_usage_and_flag_prepaid_refund(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'vertical' => 'main',
            'listing_type' => 'purchase',
            'title' => 'Marble Candle Stand',
            'slug' => 'marble-candle-stand',
            'description' => 'Hand-turned white marble candle holder.',
            'base_price' => 100000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $coupon = Coupon::create([
            'vendor_id' => $this->vendor->id,
            'code' => 'SAVE100TEST',
            'type' => 'flat',
            'value' => 10000,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(5),
            'is_active' => true,
            'usage_count' => 2,
            'total_limit' => 5,
            'per_customer_limit' => 1,
        ]);

        // Order 1 cancelled by customer -> restores stock & coupon usage
        $order1 = Order::create([
            'order_number' => 'K71-CANC01',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 100000,
            'discount_amount' => 10000,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 90000,
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'payment_method' => 'phonepe',
            'payment_status' => 'paid',
            'status' => 'pending',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);
        $order1->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'tax_amount' => 0,
            'gst_rate' => 18,
            'total_price' => 100000,
            'snapshot' => ['title' => $product->title],
        ]);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $this->buyer->id,
            'order_id' => $order1->id,
            'discount_amount' => 10000,
        ]);

        $this->actingAs($this->buyer)
            ->post(route('orders.cancel', $order1), ['reason' => 'Changed size'])
            ->assertRedirect();

        $this->assertEquals('cancelled', $order1->fresh()->status);
        $this->assertEquals('refund_pending', $order1->fresh()->payment_status);
        $this->assertEquals(12, $product->fresh()->total_stock);
        $this->assertEquals(1, $coupon->fresh()->usage_count);
        $this->assertDatabaseMissing('coupon_usages', ['order_id' => $order1->id]);

        // Order 2 cancelled by vendor via updateStatus -> flags prepaid refund_pending, notifies buyer & restores remaining coupon usage
        $order2 = Order::create([
            'order_number' => 'K71-VEND02',
            'user_id' => $this->secondBuyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 100000,
            'discount_amount' => 10000,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 90000,
            'coupon_id' => $coupon->id,
            'coupon_code' => $coupon->code,
            'payment_method' => 'phonepe',
            'payment_status' => 'paid',
            'status' => 'processing',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);
        $order2->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100000,
            'tax_amount' => 0,
            'gst_rate' => 18,
            'total_price' => 100000,
            'snapshot' => ['title' => $product->title],
        ]);
        CouponUsage::create([
            'coupon_id' => $coupon->id,
            'user_id' => $this->secondBuyer->id,
            'order_id' => $order2->id,
            'discount_amount' => 10000,
        ]);

        $this->actingAs($this->vendorUser)
            ->put(route('vendor.orders.status', $order2), [
                'status' => 'cancelled',
                'cancel_reason' => 'Out of stock',
            ])
            ->assertRedirect();

        $this->assertEquals('cancelled', $order2->fresh()->status);
        $this->assertEquals('refund_pending', $order2->fresh()->payment_status);
        $this->assertEquals(0, $coupon->fresh()->usage_count);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->secondBuyer->id,
            'notifiable_type' => User::class,
        ]);
    }

    public function test_workflow_15_delivery_assignment_state_machine_prevents_skip_and_double_delivery_inflation(): void
    {
        $fleetRole = Role::firstOrCreate(['name' => 'fleet'], ['display_name' => 'Fleet Partner', 'guard_name' => 'web']);
        $riderUser = User::factory()->create(['first_name' => 'Vikram', 'last_name' => 'Rider']);
        $riderUser->roles()->syncWithoutDetaching([$fleetRole->id]);

        $partner = DeliveryPartner::create([
            'user_id' => $riderUser->id,
            'name' => 'Vikram Rider',
            'phone' => '9829077788',
            'email' => $riderUser->email,
            'vehicle_type' => 'bike',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'is_online' => true,
            'is_verified' => true,
            'total_deliveries' => 0,
        ]);

        $this->vendor->update(['vendor_type' => 'food']);
        $this->vendor->settings()->update(['is_restaurant' => true]);

        $order = Order::create([
            'order_number' => 'K71-FLT01',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 25000,
            'discount_amount' => 0,
            'shipping_amount' => 3000,
            'tax_amount' => 0,
            'total_amount' => 28000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'pending',
            'order_type' => 'food_delivery',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);

        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.orders.assign_partner', $order), [
                'delivery_partner_id' => $partner->id,
            ])
            ->assertStatus(200);

        $assignment = DeliveryAssignment::where('order_id', $order->id)->firstOrFail();

        // Cannot skip from 'assigned' straight to 'picked_up' or 'delivered' (422)
        $this->actingAs($riderUser)
            ->postJson(route('delivery.assignments.pickup', $assignment))
            ->assertStatus(422);
        $this->actingAs($riderUser)
            ->postJson(route('delivery.assignments.deliver', $assignment))
            ->assertStatus(422);

        // Linear transitions: assigned -> accepted -> picked_up -> delivered
        $this->actingAs($riderUser)
            ->postJson(route('delivery.assignments.accept', $assignment))
            ->assertStatus(200);
        $this->assertEquals('accepted', $assignment->fresh()->status);

        $this->actingAs($riderUser)
            ->postJson(route('delivery.assignments.pickup', $assignment))
            ->assertStatus(200);
        $this->assertEquals('shipped', $order->fresh()->status);
        $this->assertEquals('out_for_delivery', $order->fresh()->food_fulfillment_stage);

        $this->actingAs($riderUser)
            ->postJson(route('delivery.assignments.deliver', $assignment))
            ->assertStatus(200);
        $this->assertEquals('delivered', $order->fresh()->status);
        $this->assertEquals('delivered', $order->fresh()->food_fulfillment_stage);
        $this->assertNotNull($order->fresh()->delivered_at);
        $this->assertEquals(1, $partner->fresh()->total_deliveries);

        // Replay click on deliver MUST fail with 422 and NOT double-increment total_deliveries
        $this->actingAs($riderUser)
            ->postJson(route('delivery.assignments.deliver', $assignment))
            ->assertStatus(422);
        $this->assertEquals(1, $partner->fresh()->total_deliveries);
    }

    public function test_workflow_16_17_18_19_cart_food_pause_shorts_guards_workspace_rating_and_used_delete_cleanup(): void
    {
        // Workflow 16: Cart update blocks paused food kitchen quantity increment
        $foodCat = Category::firstOrCreate(['slug' => 'biryani'], ['name' => 'Biryani', 'vertical' => 'food', 'is_active' => true, 'sort_order' => 1]);
        $this->vendor->update(['vendor_type' => 'food']);
        $this->vendor->settings()->update(['is_restaurant' => true, 'is_accepting_food_orders' => true]);
        $dish = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $foodCat->id,
            'title' => 'Tawa Pulao Special',
            'slug' => 'tawa-pulao-special',
            'description' => 'Freshly tossed spice pulao.',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 20000,
            'total_stock' => 20,
            'gst_rate' => 5,
            'status' => 'active',
        ]);

        $this->actingAs($this->buyer)
            ->postJson('/cart/add', ['product_id' => $dish->id, 'quantity' => 1])
            ->assertStatus(200);

        $cartItem = Cart::where('user_id', $this->buyer->id)->first()->items()->first();
        $this->vendor->settings()->update(['is_accepting_food_orders' => false]);

        $this->actingAs($this->buyer)
            ->putJson(route('cart.update', $cartItem), ['quantity' => 2])
            ->assertStatus(422);

        // Workflow 17: Shorts comment & wishlist reject unavailable entities
        $this->vendor->settings()->update(['is_accepting_food_orders' => true]);
        $hiddenShort = Short::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'title' => 'Pending Moderation Reel',
            'slug' => 'pending-moderation-reel',
            'description' => 'Draft reel.',
            'video_url' => 'https://example.com/videos/draft.mp4',
            'status' => 'draft',
        ]);
        $this->actingAs($this->buyer)
            ->postJson('/shorts/ajax', [
                'action' => 'zsu_post_comment',
                'short_id' => $hiddenShort->id,
                'content' => 'Nice!',
            ])
            ->assertStatus(404);
        $this->actingAs($this->buyer)
            ->postJson('/shorts/ajax', [
                'action' => 'zsu_wishlist_toggle',
                'product_id' => 999999,
            ])
            ->assertStatus(404);

        // Workflow 18: ServiceWorkspace approveOrder updates product review_count & avg_rating cache
        $svcCat = Category::firstOrCreate(['slug' => 'design-svc'], ['name' => 'Design Services', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]);
        $gig = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCat->id,
            'vertical' => 'services',
            'listing_type' => 'service',
            'title' => 'Brand Identity Design',
            'slug' => 'brand-identity-design',
            'description' => 'Logo & style guide design.',
            'base_price' => 250000,
            'gst_rate' => 18,
            'total_stock' => 10,
            'status' => 'active',
        ]);
        $svcOrder = ServiceOrder::create([
            'order_number' => 'SO-CACHE01',
            'buyer_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'product_id' => $gig->id,
            'package_tier' => 'basic',
            'package_title' => 'Basic Identity',
            'amount' => 250000,
            'delivery_days' => 3,
            'due_at' => now()->addDays(3),
            'revisions_allowed' => 2,
            'revisions_used' => 0,
            'status' => 'delivered',
            'requirements' => 'Need minimal luxury identity.',
        ]);

        $this->actingAs($this->buyer)
            ->post(route('services.workspace.approve', $svcOrder), [
                'rating' => 5,
                'review_text' => 'Super crisp brand kit!',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(1, $gig->fresh()->review_count);
        $this->assertEquals(5.0, (float) $gig->fresh()->avg_rating);

        // Workflow 19: Used listing destroy closes pending offers, reserved transactions, and threads cleanly
        $usedCat = Category::firstOrCreate(['slug' => 'used-cameras'], ['name' => 'Cameras', 'vertical' => 'used', 'is_active' => true, 'sort_order' => 1]);
        $usedCamera = Product::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->vendorUser->id,
            'category_id' => $usedCat->id,
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'title' => 'Fujifilm X-T30 Camera Body',
            'slug' => 'fujifilm-xt30-body',
            'description' => 'Clean shutter count under 4k.',
            'base_price' => 4500000,
            'total_stock' => 1,
            'status' => 'active',
            'is_negotiable' => true,
        ]);

        $offerRes = $this->actingAs($this->buyer)
            ->postJson(route('offers.store', $usedCamera), ['offer_amount' => 42000]);
        $offerId = $offerRes->json('offer_id');

        $tx = UsedTransaction::create([
            'transaction_number' => 'K71U-DEL001',
            'product_id' => $usedCamera->id,
            'seller_id' => $this->vendorUser->id,
            'buyer_id' => $this->buyer->id,
            'agreed_price' => 4200000,
            'quantity' => 1,
            'status' => 'reserved',
            'reserved_at' => now(),
        ]);

        // Self-reserve attempt blocked
        $this->actingAs($this->vendorUser)
            ->postJson(route('used.status', $usedCamera), [
                'action' => 'reserve',
                'buyer_id' => $this->vendorUser->id,
            ])
            ->assertStatus(422);

        // Delete listing -> offer closed, reserved transaction cancelled, thread locked
        $this->actingAs($this->vendorUser)
            ->deleteJson(route('used.delete', $usedCamera))
            ->assertStatus(200);

        $this->assertEquals('rejected', \App\Models\Offer::find($offerId)->status);
        $this->assertEquals('cancelled', $tx->fresh()->status);
        $this->assertEquals('locked', UsedThread::where('product_id', $usedCamera->id)->value('status'));
    }

    public function test_workflow_20_dpdp_account_export_uses_real_schema_fields(): void
    {
        Address::create([
            'user_id' => $this->buyer->id,
            'label' => 'Home',
            'full_name' => 'Riya Kapoor',
            'phone' => '9829011223',
            'line_1' => '12 Civil Lines',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'is_default' => true,
        ]);

        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'vertical' => 'main',
            'listing_type' => 'purchase',
            'title' => 'Italian Marble Tray',
            'slug' => 'italian-marble-tray',
            'description' => 'Carved marble serving tray.',
            'base_price' => 149900,
            'gst_rate' => 18,
            'total_stock' => 5,
            'status' => 'active',
        ]);

        $order = Order::create([
            'order_number' => 'K71-DPDP01',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 149900,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 22866,
            'total_amount' => 149900,
            'payment_method' => 'phonepe',
            'payment_status' => 'paid',
            'status' => 'delivered',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 149900,
            'tax_amount' => 22866,
            'gst_rate' => 18,
            'total_price' => 149900,
            'snapshot' => ['title' => 'Italian Marble Tray'],
        ]);

        $svcCat = Category::firstOrCreate(['slug' => 'repair-dpdp'], ['name' => 'Home Repair DPDP', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]);
        $svc = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCat->id,
            'vertical' => 'services',
            'listing_type' => 'booking',
            'title' => 'Marble Sealing',
            'slug' => 'marble-sealing',
            'description' => 'Stone sealing visit.',
            'base_price' => 200000,
            'gst_rate' => 18,
            'total_stock' => 5,
            'status' => 'active',
        ]);

        Booking::create([
            'booking_number' => 'BK-DPDP01',
            'product_id' => $svc->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'customer_name' => 'Riya Kapoor',
            'customer_phone' => '9829011223',
            'service_address' => '12 Civil Lines',
            'booking_date' => now()->addDays(2)->toDateString(),
            'booking_slot' => '11:00 AM - 01:00 PM',
            'total_amount' => 200000,
            'status' => 'confirmed',
        ]);

        $enqCat = Category::firstOrCreate(['slug' => 'stone-enq'], ['name' => 'Stone Slabs', 'vertical' => 'enquiry', 'is_active' => true, 'sort_order' => 1]);
        Enquiry::create([
            'enquiry_number' => 'ENQ-DPDP01',
            'user_id' => $this->buyer->id,
            'category_id' => $enqCat->id,
            'title' => 'Commercial Granite Slabs',
            'description' => '1000 sqft polished finish.',
            'quantity' => '1000 sqft',
            'target_budget' => 8500000,
            'city' => 'Jaipur',
            'buyer_name' => 'Riya Kapoor',
            'buyer_phone' => '9829011223',
            'status' => 'open',
        ]);

        Review::create([
            'product_id' => $product->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'order_id' => $order->id,
            'rating' => 5,
            'title' => 'Superb finish',
            'body' => 'Beautiful natural veining.',
            'is_verified_purchase' => true,
            'is_approved' => true,
        ]);

        $res = $this->actingAs($this->buyer)->get(route('account.export'));
        $res->assertStatus(200);

        $json = json_decode($res->streamedContent(), true);
        $this->assertEquals(149900, $json['orders'][0]['items'][0]['unit_price']);
        $this->assertEquals(149900, $json['orders'][0]['items'][0]['total_price']);
        $this->assertEquals('11:00 AM - 01:00 PM', $json['bookings'][0]['booking_slot']);
        $this->assertEquals(200000, $json['bookings'][0]['total_amount']);
        $this->assertEquals('Stone Slabs', $json['enquiries'][0]['category']);
        $this->assertEquals(8500000, $json['enquiries'][0]['target_budget']);
        $this->assertEquals('Beautiful natural veining.', $json['reviews'][0]['body']);
        $this->assertTrue($json['reviews'][0]['is_verified_purchase']);
        $this->assertCount(1, $json['addresses']);
    }

    public function test_workflow_21_vendor_coupon_entitlement_uppercase_and_clear_limits(): void
    {
        $this->actingAs($this->vendorUser)
            ->post(route('vendor.coupons.store'), [
                'code' => '  Festive25 ',
                'type' => 'percentage',
                'value' => 25,
                'max_discount' => 200,
                'min_cart_value' => 999,
                'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
                'total_limit' => 50,
                'per_customer_limit' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('vendor.coupons.index'));

        $coupon = Coupon::where('vendor_id', $this->vendor->id)->firstOrFail();
        $this->assertSame('FESTIVE25', $coupon->code);
        $this->assertEquals(20000, $coupon->max_discount);

        // Percentage above 100% must fail validation
        $this->actingAs($this->vendorUser)
            ->post(route('vendor.coupons.store'), [
                'code' => 'OVER100',
                'type' => 'percentage',
                'value' => 150,
                'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addDays(5)->format('Y-m-d H:i:s'),
                'per_customer_limit' => 1,
            ])
            ->assertSessionHasErrors('value');

        // Clearing optional max_discount / min_cart_value / total_limit updates DB to null
        $this->actingAs($this->vendorUser)
            ->put(route('vendor.coupons.update', $coupon), [
                'code' => 'festive25',
                'type' => 'percentage',
                'value' => 20,
                'max_discount' => null,
                'min_cart_value' => null,
                'total_limit' => null,
                'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
                'expires_at' => now()->addDays(15)->format('Y-m-d H:i:s'),
                'per_customer_limit' => 2,
                'is_active' => 1,
            ])
            ->assertRedirect(route('vendor.coupons.index'));

        $coupon->refresh();
        $this->assertSame('FESTIVE25', $coupon->code);
        $this->assertNull($coupon->max_discount);
        $this->assertNull($coupon->min_cart_value);
        $this->assertNull($coupon->total_limit);
    }

    public function test_workflow_22_services_rating_sort_and_active_order_delete_guard(): void
    {
        $svcCat = Category::firstOrCreate(['slug' => 'design-sort'], ['name' => 'Design Services', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]);

        $lowRatingHighSales = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCat->id,
            'vertical' => 'services',
            'listing_type' => 'gig',
            'title' => 'Popular Low Rated Logo',
            'slug' => 'popular-low-rated-logo',
            'description' => 'Basic logo gig.',
            'base_price' => 100000,
            'gst_rate' => 18,
            'sales_count' => 80,
            'avg_rating' => 2.10,
            'review_count' => 5,
            'status' => 'active',
        ]);

        $highRatingLowSales = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCat->id,
            'vertical' => 'services',
            'listing_type' => 'gig',
            'title' => 'Top Rated Brand System',
            'slug' => 'top-rated-brand-system',
            'description' => 'Five star brand gig.',
            'base_price' => 250000,
            'gst_rate' => 18,
            'sales_count' => 2,
            'avg_rating' => 4.95,
            'review_count' => 4,
            'status' => 'active',
        ]);

        $res = $this->get(route('services.index', ['sort' => 'rating']));
        $res->assertStatus(200);
        $html = $res->getContent();
        $this->assertTrue(strpos($html, 'Top Rated Brand System') < strpos($html, 'Popular Low Rated Logo'));

        // Active in_progress ServiceOrder blocks deleting the service gig
        ServiceOrder::create([
            'order_number' => 'SO-ACTV01',
            'buyer_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'product_id' => $highRatingLowSales->id,
            'package_tier' => 'basic',
            'package_title' => 'Basic',
            'amount' => 250000,
            'delivery_days' => 5,
            'due_at' => now()->addDays(5),
            'revisions_allowed' => 2,
            'revisions_used' => 0,
            'status' => 'in_progress',
            'requirements' => 'Work started.',
        ]);

        $this->actingAs($this->vendorUser)
            ->delete(route('vendor.services.destroy', $highRatingLowSales))
            ->assertSessionHas('error');
        $this->assertNotNull(Product::find($highRatingLowSales->id));

        // Non-services category cannot be saved onto a service gig
        $this->actingAs($this->vendorUser)
            ->put(route('vendor.services.update', $highRatingLowSales), [
                'title' => 'Top Rated Brand System',
                'category_id' => $this->mainCategory->id,
                'description' => 'Trying wrong vertical category.',
                'base_price' => 2500,
            ])
            ->assertSessionHasErrors('category_id');
    }

    public function test_workflow_23_24_payment_security_phonepe_and_shiprocket_webhooks(): void
    {
        config([
            'services.phonepe.merchant_id' => 'TEST_M',
            'services.phonepe.salt_key' => 'secret-salt-123',
            'services.phonepe.salt_index' => 1,
            'shipping.shiprocket.webhook_token' => 'sr-webhook-secret-99',
        ]);
        $this->app->instance(\App\Services\PhonePeService::class, new \App\Services\PhonePeService());

        $order = Order::create([
            'order_number' => 'K71-PAYSEC01',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 50000,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 50000,
            'payment_method' => 'phonepe',
            'payment_status' => 'pending',
            'status' => 'pending',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);

        // Forged query ?code=PAYMENT_SUCCESS without API verification or signature must NOT mark paid
        $this->actingAs($this->buyer)
            ->post(route('payment.phonepe.callback', $order), ['code' => 'PAYMENT_SUCCESS'])
            ->assertRedirect(route('orders.index'));
        $this->assertEquals('pending', $order->fresh()->payment_status);

        // X-Test-Bypass header bypass on webhook is completely rejected (403)
        $this->withHeaders(['X-Test-Bypass' => '1'])
            ->postJson('/webhooks/phonepe', ['code' => 'PAYMENT_SUCCESS', 'merchantTransactionId' => $order->order_number])
            ->assertStatus(403);

        // Properly signed PhonePe S2S webhook marks order paid, records commission & logs real from_status
        $encoded = base64_encode(json_encode([
            'code' => 'PAYMENT_SUCCESS',
            'merchantTransactionId' => $order->order_number,
        ]));
        $sig = hash('sha256', $encoded . 'secret-salt-123') . '###1';

        $this->withHeaders(['X-VERIFY' => $sig])
            ->postJson('/webhooks/phonepe', ['response' => $encoded])
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertEquals('processing', $order->status);
        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'processing',
        ]);
        $this->assertDatabaseHas('order_commissions', [
            'transaction_type' => 'order',
            'transaction_id' => $order->id,
            'event_type' => 'charge',
        ]);

        // Shiprocket webhook rejects missing/wrong token (403), accepts valid token, and never resurrects cancelled order
        $cancelledOrder = Order::create([
            'order_number' => 'K71-SR01',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 50000,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 50000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'status' => 'cancelled',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);
        Shipment::create([
            'order_id' => $cancelledOrder->id,
            'vendor_id' => $this->vendor->id,
            'awb_code' => 'AWB-CANC-77',
            'courier_name' => 'Delhivery',
            'status' => 'manifested',
        ]);

        $this->postJson('/webhook/shiprocket', ['awb' => 'AWB-CANC-77', 'current_status' => 'DELIVERED'])
            ->assertStatus(403);

        $this->withHeaders(['x-api-key' => 'sr-webhook-secret-99'])
            ->postJson('/webhook/shiprocket', ['awb' => 'AWB-CANC-77', 'current_status' => 'DELIVERED'])
            ->assertStatus(200);

        $this->assertEquals('cancelled', $cancelledOrder->fresh()->status);
    }

    public function test_workflow_25_26_27_28_29_settings_payout_guards_upi_claim_seller_block_and_notifications(): void
    {
        // PayoutService rejects cancelled order even if settlement_status was cleared
        $cancelledPaidOrder = Order::create([
            'order_number' => 'K71-POGUARD1',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 40000,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 40000,
            'payment_method' => 'phonepe',
            'payment_status' => 'paid',
            'settlement_status' => 'cleared',
            'status' => 'cancelled',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);

        try {
            app(PayoutService::class)->disburseVendorPayout($cancelledPaidOrder);
            $this->fail('Expected cancelled order payout exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('Cannot disburse payout for a cancelled order.', $e->getMessage());

            // CheckoutController::processPayment rejects cancelled order & notifies vendor on valid UPI claim
            $this->actingAs($this->buyer)
                ->post(route('checkout.payment.process', $cancelledPaidOrder), ['upi_transaction_id' => 'UTR123456789'])
                ->assertRedirect(route('orders.show', $cancelledPaidOrder));

            $upiOrder = Order::create([
                'order_number' => 'K71-UPICLM01',
                'user_id' => $this->buyer->id,
                'vendor_id' => $this->vendor->id,
                'subtotal' => 45000,
                'discount_amount' => 0,
                'shipping_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 45000,
                'payment_method' => 'upi',
                'payment_status' => 'pending',
                'status' => 'pending',
                'item_count' => 1,
                'address_snapshot' => ['city' => 'Jaipur'],
            ]);

            $this->actingAs($this->buyer)
                ->post(route('checkout.payment.process', $upiOrder), ['upi_transaction_id' => '  utr9988776655 '])
                ->assertRedirect(route('orders.success', $upiOrder));

            $this->assertDatabaseHas('payment_claims', [
                'order_id' => $upiOrder->id,
                'utr_number' => 'UTR9988776655',
            ]);
            $this->assertDatabaseHas('notifications', [
                'notifiable_id' => $this->vendorUser->id,
            ]);

            // Blocked Used seller profile returns 404
            $blockedUser = User::factory()->create(['is_blocked' => true]);
            UsedSellerProfile::create([
                'user_id' => $blockedUser->id,
                'display_name' => 'Blocked Seller',
                'handle' => 'blocked-seller-handle',
            ]);
            $this->actingAs($this->buyer)
                ->get(route('used.seller.profile', 'blocked-seller-handle'))
                ->assertStatus(404);

            // NotificationController JSON mark-all-read works cleanly
            $this->actingAs($this->vendorUser)
                ->postJson(route('notifications.read-all'))
                ->assertStatus(200)
                ->assertJson(['success' => true, 'unread_count' => 0]);

            // Food vendor settings can clear min_food_order_amount back to 0 cleanly
            $this->vendor->update(['vendor_type' => 'food']);
            $this->vendor->settings()->update(['is_restaurant' => true, 'min_food_order_amount' => 25000, 'upi_enabled' => true]);
            $this->actingAs($this->vendorUser)
                ->put(route('vendor.settings.update'), [
                    'food_settings_submitted' => 1,
                    'min_food_order_amount' => 0,
                    'payment_mode' => 'upi_only',
                ])
                ->assertRedirect();
            $this->assertEquals(0, $this->vendor->settings()->first()->min_food_order_amount);
            return;
        }
    }

    public function test_workflow_30_31_32_shorts_home_visibility_admin_notifications_and_studio_hardening(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'vertical' => 'main',
            'listing_type' => 'purchase',
            'title' => 'Zero Review Marble Lamp',
            'slug' => 'zero-review-marble-lamp',
            'description' => 'Carved lamp with zero reviews.',
            'base_price' => 120000,
            'gst_rate' => 18,
            'total_stock' => 6,
            'status' => 'active',
        ]);

        // Unrelated vendor product must not be taggable by this vendor
        $otherUser = User::factory()->create();
        $otherVendor = Vendor::create([
            'user_id' => $otherUser->id,
            'shop_name' => 'Other Store',
            'slug' => 'other-store-shorts',
            'email' => $otherUser->email,
            'vendor_type' => 'retail',
            'status' => 'approved',
        ]);
        $foreignProduct = Product::create([
            'vendor_id' => $otherVendor->id,
            'category_id' => $this->mainCategory->id,
            'vertical' => 'main',
            'listing_type' => 'purchase',
            'title' => 'Foreign Product',
            'slug' => 'foreign-product-shorts',
            'description' => 'Should not be tagged.',
            'base_price' => 99000,
            'gst_rate' => 18,
            'total_stock' => 4,
            'status' => 'active',
        ]);

        // Vendor ShortsStudio save scopes product_ids to own vendor only & validates category_id
        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.shorts.save'), [
                'title' => 'Bad Category Short',
                'video_url' => 'https://example.com/v.mp4',
                'category_id' => 999999,
            ])
            ->assertStatus(422);

        $saveRes = $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.shorts.save'), [
                'title' => 'Real Craft Short',
                'video_url' => 'https://example.com/craft.mp4',
                'thumb_url' => 'shorts/thumbs/craft.jpg',
                'category_id' => $this->mainCategory->id,
                'product_ids' => [$product->id, $foreignProduct->id],
            ])
            ->assertStatus(200);

        $shortId = (int) $saveRes->json('short_id');
        $short = Short::findOrFail($shortId);
        $this->assertEquals([$product->id], $short->products()->pluck('products.id')->all());
        $this->assertStringContainsString('shorts/thumbs/craft.jpg', (string) $short->thumbnail_url);

        // Homepage now renders published shorts & ShortsController returns nullable real ratings (not 4.8 / 18)
        $homeRes = $this->get('/');
        $homeRes->assertStatus(200)->assertSee('Real Craft Short');

        $shortsRes = $this->get('/shorts');
        $shortsRes->assertStatus(200);
        $shortsRes->assertViewHas('clientShorts', function ($clientShorts) {
            $first = collect($clientShorts)->firstWhere('title', 'Real Craft Short');
            return $first && isset($first['products_detail'][0])
                && $first['products_detail'][0]['rating'] === null
                && $first['products_detail'][0]['reviews_count'] === 0;
        });

        // Unpublished/pending Short is NOT exposed via /shorts?v={id} seoShort
        $pendingShort = Short::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'title' => 'Secret Pending Reel',
            'slug' => 'secret-pending-reel',
            'description' => 'Pending review.',
            'video_url' => 'https://example.com/pending.mp4',
            'status' => 'pending',
        ]);
        $pendingShortsRes = $this->get('/shorts?v=' . $pendingShort->id);
        $pendingShortsRes->assertStatus(200)->assertViewHas('seoShort', null);

        // Admin approve/reject notify vendor cleanly
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $adminUser = User::factory()->create();
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->actingAs($adminUser)
            ->postJson(route('admin.shorts.reject', $pendingShort), ['reason' => 'Low audio quality'])
            ->assertStatus(200);
        $this->assertEquals('rejected', $pendingShort->fresh()->status);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->vendorUser->id,
        ]);

        // Shorts boost rejects invalid tier & checks atomic balance inside transaction
        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.shorts.boost'), ['short_id' => $short->id, 'tier' => 'ultra_bogus'])
            ->assertStatus(422);

        ShortCoin::create([
            'user_id' => $this->vendorUser->id,
            'delta' => 500,
            'balance' => 500,
            'reason' => 'Promo coins',
        ]);
        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.shorts.boost'), ['short_id' => $short->id, 'tier' => 'standard'])
            ->assertStatus(200);
        $this->assertEquals(301, ShortCoin::where('user_id', $this->vendorUser->id)->orderByDesc('id')->first()->balance);
        $this->assertEquals(1, ShortBoost::where('short_id', $short->id)->count());
    }

    public function test_workflow_33_34_35_feasto_title_wishlist_stock_onboarding_gate_and_auth_blocks(): void
    {
        // Workflow 33: Feasto shows dishes whose titles contain 'Greatest'/'Latest' and Wishlist JSON reports real stock
        $foodCat = Category::firstOrCreate(['slug' => 'feasto-mains'], ['name' => 'Feasto Mains', 'vertical' => 'food', 'is_active' => true, 'sort_order' => 1]);
        $foodUser = User::factory()->create();
        $foodVendor = Vendor::create([
            'user_id' => $foodUser->id,
            'shop_name' => 'Saffron Kitchen',
            'slug' => 'saffron-kitchen-wf33',
            'email' => $foodUser->email,
            'vendor_type' => 'food',
            'status' => 'approved',
        ]);
        $this->grantVendorTestSubscription($foodVendor, 'food');
        VendorSetting::create([
            'vendor_id' => $foodVendor->id,
            'is_restaurant' => true,
            'is_accepting_food_orders' => true,
        ]);
        $dish = Product::create([
            'vendor_id' => $foodVendor->id,
            'category_id' => $foodCat->id,
            'title' => 'Greatest Kashmiri Biryani',
            'slug' => 'greatest-kashmiri-biryani',
            'description' => 'Rich aromatic saffron biryani.',
            'vertical' => 'food',
            'listing_type' => 'food',
            'base_price' => 32000,
            'total_stock' => 10,
            'is_dish_available' => true,
            'status' => 'active',
        ]);

        $this->get('/feasto')->assertStatus(200)->assertSee('Greatest Kashmiri Biryani');

        Wishlist::create(['user_id' => $this->buyer->id, 'product_id' => $dish->id]);
        $this->actingAs($this->buyer)
            ->getJson('/wishlist/json')
            ->assertStatus(200)
            ->assertJsonPath('items.0.in_stock', true);

        // Workflow 34: Service onboarding Step 1/Step 5 never self-approve pending vendor & Step 5 requires Services subscription
        $pendingUser = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($pendingUser)
            ->post(route('services.onboarding.save'), [
                'step' => 1,
                'provider_mode' => 'individual',
                'shop_name' => 'Pending Craft Studio',
                'headline' => 'Craft & Interior Design',
                'description' => 'Pending studio waiting for manual admin review.',
                'skills' => 'Interior, CAD',
                'work_mode' => 'remote',
            ])
            ->assertRedirect(route('services.onboarding', ['step' => 2]));

        $pendingVendor = $pendingUser->fresh()->vendor;
        $this->assertEquals('pending', $pendingVendor->status);

        // Resubmitting Step 1 must NOT escalate pending -> approved
        $this->actingAs($pendingUser)
            ->post(route('services.onboarding.save'), [
                'step' => 1,
                'provider_mode' => 'individual',
                'shop_name' => 'Pending Craft Studio V2',
                'headline' => 'Craft & Interior Design Studio',
                'description' => 'Updated pending studio waiting for manual admin review.',
                'skills' => 'Interior, CAD, 3D',
                'work_mode' => 'hybrid',
            ])
            ->assertRedirect(route('services.onboarding', ['step' => 2]));
        $this->assertEquals('pending', $pendingVendor->fresh()->status);

        // Step 5 publish is blocked without admin-activated Services subscription
        $this->actingAs($pendingUser)
            ->post(route('services.onboarding.save'), [
                'step' => 5,
                'publish_action' => 'publish',
            ])
            ->assertRedirect(route('services.onboarding', ['step' => 5]))
            ->assertSessionHas('error');
        $this->assertFalse($pendingVendor->fresh()->isWebsitePublished());

        // Workflow 35: Blocked user receives JSON 403 on modal login
        auth()->logout();
        $blockedUser = User::factory()->create([
            'password' => bcrypt('Secret123!'),
            'is_blocked' => true,
        ]);
        $this->postJson(route('login'), [
            'login' => $blockedUser->email,
            'password' => 'Secret123!',
            'role' => 'customer',
        ])->assertStatus(403)->assertJson(['success' => false]);
    }

    public function test_workflow_36_37_38_39_sku_validation_storefront_gates_self_message_guard_and_used_update(): void
    {
        // Workflow 36: Two SKU-less products succeed & duplicate SKU triggers clean validation error (not 500)
        $this->actingAs($this->vendorUser)
            ->post(route('vendor.products.store'), [
                'title' => 'Blank SKU Item One',
                'category_id' => $this->mainCategory->id,
                'description' => 'Product without SKU.',
                'base_price' => 500,
                'gst_rate' => 18,
                'sku' => '   ',
                'total_stock' => 5,
                'status' => 'active',
            ])
            ->assertRedirect(route('vendor.products.index'));

        $this->actingAs($this->vendorUser)
            ->post(route('vendor.products.store'), [
                'title' => 'Unique SKU Item Two',
                'category_id' => $this->mainCategory->id,
                'description' => 'Second product with empty SKU and then reused SKU.',
                'base_price' => 600,
                'gst_rate' => 18,
                'sku' => 'K71-DUP-SKU',
                'total_stock' => 5,
                'status' => 'active',
            ])
            ->assertRedirect(route('vendor.products.index'));

        $this->actingAs($this->vendorUser)
            ->post(route('vendor.products.store'), [
                'title' => 'Duplicate SKU Item Three',
                'category_id' => $this->mainCategory->id,
                'description' => 'Should fail validation cleanly.',
                'base_price' => 700,
                'gst_rate' => 18,
                'sku' => 'K71-DUP-SKU',
                'total_stock' => 5,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('sku');

        // Workflow 37: WebsiteHub updateIdentity allows clearing skills & hourly_rate when authenticated & subscribed
        $this->vendor->update([
            'vendor_type' => 'services',
            'skills_json' => ['Laravel', 'React'],
            'hourly_rate' => 2500,
        ]);
        $this->grantVendorTestSubscription($this->vendor->fresh(), 'services');

        $this->actingAs($this->vendorUser)
            ->post(route('vendor.website.identity'), [
                'shop_name' => $this->vendor->shop_name,
                'provider_mode' => 'individual',
                'headline' => 'Updated Studio Headline',
                'description' => 'Updated clean studio biography ready for client engagements.',
                'work_mode' => 'remote',
                'availability_status' => 'available',
                'skills_input' => '',
                'hourly_rate' => '',
            ])
            ->assertSessionHas('success');

        $this->vendor->refresh();
        $this->assertSame([], $this->vendor->skills_json);
        $this->assertNull($this->vendor->hourly_rate);

        // Workflow 38: MessageController blocks self-messaging own store & notifies vendor on buyer inquiry
        $this->actingAs($this->vendorUser)
            ->postJson('/messages', [
                'vendor_id' => $this->vendor->id,
                'body' => 'Testing self chat',
            ])
            ->assertStatus(422);

        $this->actingAs($this->buyer)
            ->postJson('/messages', [
                'vendor_id' => $this->vendor->id,
                'body' => 'Is custom polishing available?',
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $this->vendorUser->id,
        ]);

        // Workflow 39: UsedListingController::update validates required category fields & rejects wrong vertical category
        $usedCat = Category::firstOrCreate(['slug' => 'used-laptops-wf39'], ['name' => 'Used Laptops', 'vertical' => 'used', 'is_active' => true, 'sort_order' => 1]);
        CategoryField::create([
            'category_id' => $usedCat->id,
            'vertical' => 'used',
            'name' => 'ram_size',
            'label' => 'RAM Capacity',
            'field_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $usedDraft = Product::create([
            'vendor_id' => null,
            'user_id' => $this->buyer->id,
            'category_id' => $usedCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'ThinkPad T14 Gen 3',
            'slug' => 'thinkpad-t14-gen3-wf39',
            'description' => 'Mint condition business ultrabook.',
            'base_price' => 5200000,
            'total_stock' => 1,
            'quantity_available' => 1,
            'status' => 'draft',
            'used_status' => 'draft',
            'city' => 'Bengaluru',
        ]);

        // Reject non-used category_id on update
        $this->actingAs($this->buyer)
            ->putJson(route('used.update', $usedDraft), [
                'title' => 'ThinkPad T14 Gen 3',
                'category_id' => $this->mainCategory->id,
                'description' => 'Mint condition business ultrabook.',
                'price' => 52000,
                'city' => 'Bengaluru',
                'action' => 'publish',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('category_id');

        // Reject missing required category field when publishing
        $this->actingAs($this->buyer)
            ->putJson(route('used.update', $usedDraft), [
                'title' => 'ThinkPad T14 Gen 3',
                'category_id' => $usedCat->id,
                'description' => 'Mint condition business ultrabook.',
                'price' => 52000,
                'city' => 'Bengaluru',
                'action' => 'publish',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('specs.ram_size');

        // Succeeds once required spec provided
        $this->actingAs($this->buyer)
            ->putJson(route('used.update', $usedDraft), [
                'title' => 'ThinkPad T14 Gen 3',
                'category_id' => $usedCat->id,
                'description' => 'Mint condition business ultrabook.',
                'price' => 52000,
                'city' => 'Bengaluru',
                'specs' => ['ram_size' => '16GB'],
                'action' => 'publish',
            ])
            ->assertStatus(200);
        $this->assertEquals('active', $usedDraft->fresh()->status);
    }

    public function test_workflow_40_41_42_shorts_ajax_visibility_spin_coupon_validity_and_portfolio_quota(): void
    {
        // Workflow 40: ShortsAjaxController blocks metrics/likes/products on draft/hidden shorts & never fabricates 5.0 ratings
        $draftShort = Short::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'title' => 'Draft Hidden Short',
            'slug' => 'draft-hidden-short-wf40',
            'video_url' => 'https://example.com/draft.mp4',
            'status' => 'pending',
        ]);

        $this->postJson('/shorts/ajax', ['action' => 'zsu_record_view', 'short_id' => $draftShort->id])->assertStatus(404);
        $this->postJson('/shorts/ajax', ['action' => 'zsu_record_like', 'short_id' => $draftShort->id])->assertStatus(404);
        $this->postJson('/shorts/ajax', ['action' => 'zsu_record_share', 'short_id' => $draftShort->id])->assertStatus(404);
        $this->postJson('/shorts/ajax', ['action' => 'zsu_record_watch_pct', 'short_id' => $draftShort->id, 'pct' => 75])->assertStatus(404);
        $this->assertEquals(0, $draftShort->fresh()->views);

        $liveProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'vertical' => 'main',
            'listing_type' => 'purchase',
            'title' => 'Unrated Onyx Cuff',
            'slug' => 'unrated-onyx-cuff-wf40',
            'description' => 'Product with no reviews yet.',
            'base_price' => 89900,
            'gst_rate' => 18,
            'total_stock' => 5,
            'status' => 'active',
        ]);
        $liveShort = Short::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->mainCategory->id,
            'title' => 'Published Studio Short',
            'slug' => 'published-studio-short-wf40',
            'video_url' => 'https://example.com/live.mp4',
            'status' => 'published',
        ]);
        $liveShort->products()->sync([$liveProduct->id]);

        $this->getJson('/shorts/ajax?action=zsu_get_products&short_id=' . $liveShort->id)
            ->assertStatus(200)
            ->assertJsonPath('products.0.rating', null)
            ->assertJsonPath('products.0.review_count', 0);

        // Workflow 41: ShortsSpinController rejects expired coupons without triggering user cooldown, and serves valid coupons cleanly
        SpinPrize::query()->delete();
        Coupon::query()->delete();
        $expiredCoupon = Coupon::create([
            'vendor_id' => $this->vendor->id,
            'code' => 'EXPIRED50',
            'type' => 'percentage',
            'value' => 50,
            'starts_at' => now()->subDays(10),
            'expires_at' => now()->subDay(),
            'is_active' => true,
        ]);
        SpinPrize::create([
            'label' => 'Expired Prize',
            'color' => '#FF1493',
            'type' => 'coupon',
            'value' => 'BOGUSCODE',
            'coupon_id' => $expiredCoupon->id,
            'probability' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->buyer)
            ->postJson('/shorts/ajax', ['action' => 'zsu_home_spin_result'])
            ->assertStatus(404);
        $this->actingAs($this->buyer)
            ->getJson('/shorts/ajax?action=zsu_home_spin_check')
            ->assertStatus(200)
            ->assertJson(['can_spin' => true]);

        $validCoupon = Coupon::create([
            'vendor_id' => $this->vendor->id,
            'code' => 'LIVEWIN15',
            'type' => 'percentage',
            'value' => 15,
            'starts_at' => now()->subHour(),
            'expires_at' => now()->addDays(5),
            'total_limit' => 25,
            'is_active' => true,
        ]);
        SpinPrize::create([
            'label' => '15% Studio Off',
            'color' => '#00D2FF',
            'type' => 'coupon',
            'value' => 'LIVEWIN15',
            'coupon_id' => $validCoupon->id,
            'probability' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->buyer)
            ->postJson('/shorts/ajax', ['action' => 'zsu_home_spin_result'])
            ->assertStatus(200)
            ->assertJsonPath('coupon_code', 'LIVEWIN15');

        // Workflow 42: PortfolioController update/toggleFeatured checks sector authorization BEFORE deleting old cover_image
        Storage::fake('public');
        $this->vendor->update(['vendor_type' => 'services']);
        $this->grantVendorTestSubscription($this->vendor->fresh(), 'services');

        $portfolio = PortfolioProject::create([
            'vendor_id' => $this->vendor->id,
            'title' => 'Original Case Study',
            'slug' => 'original-case-study-wf42',
            'cover_image' => 'portfolio/' . $this->vendor->id . '/keep-cover.jpg',
            'status' => 'draft',
        ]);
        Storage::disk('public')->put($portfolio->cover_image, 'cover-bytes');

        // Expire/suspend subscription before update attempt -> old cover must NOT be deleted
        \App\Models\VendorSubscription::where('vendor_id', $this->vendor->id)->update(['status' => 'expired']);
        $this->vendor->update(['status' => 'suspended']);

        $newCover = UploadedFile::fake()->image('new.jpg', 400, 400);
        $this->actingAs($this->vendorUser)
            ->put(route('vendor.portfolio.update', $portfolio), [
                'title' => 'Blocked Update',
                'status' => 'published',
                'cover_image' => $newCover,
            ])
            ->assertSessionHas('error');

        Storage::disk('public')->assertExists('portfolio/' . $this->vendor->id . '/keep-cover.jpg');
        $this->assertEquals('Original Case Study', $portfolio->fresh()->title);

        $this->actingAs($this->vendorUser)
            ->patch(route('vendor.portfolio.toggle_featured', $portfolio))
            ->assertSessionHas('error');
        $this->assertFalse((bool) $portfolio->fresh()->is_featured);
    }

    public function test_workflow_43_44_45_46_service_reviews_settlement_exclusion_fleet_blocks_and_project_category(): void
    {
        // Workflow 43: Distinct service orders record 'service' typed reviews & recompute gig avg_rating cleanly
        $this->vendor->update(['vendor_type' => 'services']);
        $this->grantVendorTestSubscription($this->vendor->fresh(), 'services');
        $svcCatWf43 = Category::firstOrCreate(['slug' => 'svc-cat-wf43'], ['name' => 'Brand Identity', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]);
        $gig1 = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCatWf43->id,
            'vertical' => 'services',
            'listing_type' => 'service',
            'title' => 'Brand Identity Package',
            'slug' => 'brand-identity-pkg-wf43',
            'description' => 'Full logo and typography identity.',
            'base_price' => 150000,
            'total_stock' => 10,
            'status' => 'active',
        ]);
        $gig2 = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCatWf43->id,
            'vertical' => 'services',
            'listing_type' => 'service',
            'title' => 'Packaging Design Package',
            'slug' => 'packaging-design-pkg-wf43',
            'description' => 'Custom box dielines and print files.',
            'base_price' => 200000,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        $svcOrder1 = ServiceOrder::create([
            'order_number' => 'SVC-WF43-1',
            'buyer_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'product_id' => $gig1->id,
            'package_title' => 'Custom Branding',
            'amount' => 150000,
            'delivery_days' => 5,
            'revisions_allowed' => 2,
            'revisions_used' => 0,
            'status' => 'delivered',
        ]);
        $svcOrder2 = ServiceOrder::create([
            'order_number' => 'SVC-WF43-2',
            'buyer_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'product_id' => $gig2->id,
            'package_title' => 'Packaging Design',
            'amount' => 200000,
            'delivery_days' => 7,
            'revisions_allowed' => 2,
            'revisions_used' => 0,
            'status' => 'delivered',
        ]);

        $this->actingAs($this->buyer)->post(route('services.workspace.approve', $svcOrder1), ['rating' => 5, 'review_text' => 'First service review'])->assertSessionHas('success');
        $this->actingAs($this->buyer)->post(route('services.workspace.approve', $svcOrder2), ['rating' => 4, 'review_text' => 'Second service review'])->assertSessionHas('success');

        $this->assertEquals(2, Review::where('user_id', $this->buyer->id)->where('vendor_id', $this->vendor->id)->count());
        $this->assertDatabaseHas('reviews', ['service_order_id' => $svcOrder1->id, 'rating' => 5, 'review_type' => 'service']);
        $this->assertDatabaseHas('reviews', ['service_order_id' => $svcOrder2->id, 'rating' => 4, 'review_type' => 'service']);
        $this->assertEquals(5.0, (float) $gig1->fresh()->avg_rating);
        $this->assertEquals(4.0, (float) $gig2->fresh()->avg_rating);

        // Workflow 44: SettlementController excludes cancelled/refunded paid orders from payout settlement sums
        Order::create([
            'order_number' => 'K71-STL-LIVE1',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 100000,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'payment_method' => 'phonepe',
            'payment_status' => 'paid',
            'settlement_status' => 'cleared',
            'settled_at' => now(),
            'status' => 'delivered',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);
        Order::create([
            'order_number' => 'K71-STL-CANC1',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 200000,
            'discount_amount' => 0,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 200000,
            'payment_method' => 'phonepe',
            'payment_status' => 'paid',
            'settlement_status' => 'cleared',
            'settled_at' => now(),
            'status' => 'cancelled',
            'item_count' => 1,
            'address_snapshot' => ['city' => 'Jaipur'],
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $adminUser = User::factory()->create();
        $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);

        $stlRes = $this->actingAs($adminUser)->get(route('admin.settlements', ['month' => now('Asia/Kolkata')->format('Y-m')]));
        $stlRes->assertStatus(200);
        $stlRes->assertViewHas('settlements', function ($settlements) {
            return count($settlements) === 1
                && $settlements[0]['orders_count'] === 1
                && $settlements[0]['gross_rupees'] === '1,000.00';
        });

        // Workflow 45: DeliveryPartnerController parses string 'false' properly & locks out blocked riders
        $fleetRole = Role::firstOrCreate(['name' => 'fleet'], ['display_name' => 'Fleet Partner']);
        $riderUser = User::factory()->create(['is_blocked' => false]);
        $riderUser->roles()->syncWithoutDetaching([$fleetRole->id]);
        $riderPartner = DeliveryPartner::create([
            'user_id' => $riderUser->id,
            'name' => 'Test Rider',
            'phone' => '9876543210',
            'email' => $riderUser->email,
            'vehicle_type' => 'bike',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'is_online' => true,
            'is_verified' => true,
        ]);

        $this->actingAs($riderUser)
            ->postJson(route('delivery.location'), ['is_online' => 'false', 'latitude' => 26.9124, 'longitude' => 75.7873])
            ->assertStatus(200)
            ->assertJson(['is_online' => false]);
        $this->assertFalse((bool) $riderPartner->fresh()->is_online);

        $riderUser->update(['is_blocked' => true]);
        $this->actingAs($riderUser)
            ->postJson(route('delivery.location'), ['is_online' => true])
            ->assertStatus(401);

        // Workflow 46: ProjectMarketplaceController restricts project category_id to vertical='services'
        $foodCat = Category::firstOrCreate(['slug' => 'food-only-wf46'], ['name' => 'Food Only', 'vertical' => 'food', 'is_active' => true, 'sort_order' => 1]);
        $svcCat = Category::firstOrCreate(['slug' => 'svc-only-wf46'], ['name' => 'Design Systems', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]);

        $this->actingAs($this->buyer)
            ->post(route('services.projects.store'), [
                'title' => 'Need Full UI Brand System',
                'category_id' => $foodCat->id,
                'description' => 'Comprehensive design tokens, components and motion guidelines needed.',
                'budget_type' => 'fixed',
                'budget_min' => 5000,
                'work_mode' => 'remote',
            ])
            ->assertSessionHasErrors('category_id');

        $this->actingAs($this->buyer)
            ->post(route('services.projects.store'), [
                'title' => 'Need Full UI Brand System',
                'category_id' => $svcCat->id,
                'description' => 'Comprehensive design tokens, components and motion guidelines needed.',
                'budget_type' => 'fixed',
                'budget_min' => 5000,
                'work_mode' => 'remote',
            ])
            ->assertRedirect();
        $this->assertDatabaseHas('service_projects', ['title' => 'Need Full UI Brand System', 'category_id' => $svcCat->id]);
    }

    public function test_workflow_47_48_49_enquiry_guest_idor_catalog_type_coercion_and_offer_counter_closure(): void
    {
        // Workflow 47: EnquiryController rejects direct RFQ to suspended vendor & blocks unauthenticated/stranger acceptance of guest RFQ quotes
        $suspendedUser = User::factory()->create();
        $suspendedVendor = Vendor::create([
            'user_id' => $suspendedUser->id,
            'shop_name' => 'Suspended B2B Supplier',
            'slug' => 'suspended-b2b-wf47',
            'email' => $suspendedUser->email,
            'vendor_type' => 'enquiry',
            'status' => 'suspended',
        ]);

        $this->actingAs($this->buyer)
            ->post(route('enquiry.store'), [
                'title' => '1000 Ton Makrana White Slab',
                'description' => 'Need commercial quote for flooring project.',
                'quantity' => '1000 sq ft',
                'city' => 'Kishangarh',
                'buyer_name' => 'Buyer Co',
                'buyer_phone' => '9876500011',
                'vendor_id' => $suspendedVendor->id,
            ])
            ->assertSessionHas('error');
        $this->assertEquals(0, Enquiry::count());

        // Guest RFQ submitted cleanly (null user_id, guest session token set)
        $this->post(route('enquiry.store'), [
            'title' => 'Guest Marble Slab Inquiry',
            'description' => 'Need 500 slabs cut to size.',
            'quantity' => '500 units',
            'city' => 'Udaipur',
            'buyer_name' => 'Guest Builder',
            'buyer_phone' => '9988776611',
        ])->assertRedirect();

        $guestEnquiry = Enquiry::firstOrFail();
        $quote = Quote::create([
            'enquiry_id' => $guestEnquiry->id,
            'vendor_id' => $this->vendor->id,
            'quote_number' => 'QT-WF47-01',
            'quote_amount' => 250000,
            'lead_time_days' => 5,
            'status' => 'sent',
        ]);

        // Unrelated logged-in user (secondBuyer, with empty guest session) MUST receive 403 trying to accept guest quote
        $this->app['session']->flush();
        $this->actingAs($this->secondBuyer)
            ->post(route('quotes.accept', $quote))
            ->assertStatus(403);
        $this->assertEquals('sent', $quote->fresh()->status);

        // Workflow 48: CatalogController does not coerce '999-nonexistent' into category/vendor ID & hides suspended store filters
        $realCat = Category::first();
        $this->get('/catalog?category=' . $realCat->id . '-fake-coercion')
            ->assertStatus(200)
            ->assertViewHas('currentCategory', null);

        $this->get('/catalog?store=' . $suspendedVendor->id . '-fake-slug')
            ->assertStatus(200)
            ->assertViewHas('currentStore', null);
        $this->get('/catalog?store=' . $suspendedVendor->slug)
            ->assertStatus(200)
            ->assertViewHas('currentStore', null)
            ->assertDontSee('Suspended B2B Supplier');

        // Workflow 49: OfferController buyerRespondCounter reveals private_pickup_area into UsedThread, logs AuditLog & guards markSold
        $usedCat = Category::firstOrCreate(['slug' => 'used-audio-wf49'], ['name' => 'Used Audio', 'vertical' => 'used', 'is_active' => true, 'sort_order' => 1]);
        $usedItem = Product::create([
            'vendor_id' => null,
            'user_id' => $this->vendorUser->id,
            'category_id' => $usedCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Sony WH-1000XM5 Headphones',
            'slug' => 'sony-wh1000xm5-wf49',
            'description' => 'Lightly used noise cancelling headphones.',
            'base_price' => 2000000,
            'total_stock' => 1,
            'quantity_available' => 1,
            'is_negotiable' => true,
            'private_pickup_area' => 'C-Scheme Metro Gate 2, Jaipur',
            'status' => 'active',
            'used_status' => 'active',
            'city' => 'Jaipur',
        ]);

        $this->actingAs($this->buyer)
            ->post(route('offers.store', $usedItem), ['offer_amount' => 16000])
            ->assertSessionHas('success');
        $offer = Offer::where('product_id', $usedItem->id)->firstOrFail();

        $this->actingAs($this->vendorUser)
            ->put(route('used.offers.seller_respond', $offer), ['action' => 'counter', 'counter_amount' => 17500])
            ->assertSessionHas('success');

        $this->actingAs($this->buyer)
            ->post(route('used.offers.respond_counter', $offer), ['action' => 'accept_counter'])
            ->assertSessionHas('success');

        $this->assertEquals('reserved', $usedItem->fresh()->used_status);
        $this->assertDatabaseHas('used_threads', [
            'product_id' => $usedItem->id,
            'buyer_id' => $this->buyer->id,
            'shared_pickup_location' => 'C-Scheme Metro Gate 2, Jaipur',
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'auditable_type' => Offer::class,
            'auditable_id' => $offer->id,
            'action' => 'offer_accept_counter',
        ]);
    }

    public function test_workflow_50_51_52_53_dpdp_bid_export_order_locks_service_cleanup_and_food_deals(): void
    {
        // Workflow 50: DPDP export uses real Bid::$bid_amount & account deletion suspends direct used ads + delivery partner + vendor subs
        $auctionCat = Category::firstOrCreate(['slug' => 'collectibles-wf50'], ['name' => 'Collectibles', 'vertical' => 'used', 'is_active' => true, 'sort_order' => 1]);
        $auctionProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $auctionCat->id,
            'vertical' => 'products',
            'listing_type' => 'auction',
            'title' => 'Vintage Wristwatch WF50',
            'slug' => 'vintage-wristwatch-wf50',
            'description' => 'Collector timepiece',
            'base_price' => 500000,
            'starting_bid' => 500000,
            'reserve_price' => 1000000,
            'min_bid_increment' => 10000,
            'total_stock' => 1,
            'status' => 'active',
            'auction_status' => 'live',
            'auction_ends_at' => now()->addDays(2),
        ]);
        \App\Models\Bid::create([
            'product_id' => $auctionProduct->id,
            'user_id' => $this->buyer->id,
            'bid_amount' => 625000,
            'status' => 'winning',
        ]);

        $exportRes = $this->actingAs($this->buyer)->get('/account/export');
        $exportRes->assertOk();
        $json = json_decode($exportRes->streamedContent(), true);
        $this->assertSame(625000, $json['bids'][0]['amount'] ?? null);

        $erasureUser = User::factory()->create(['email_verified_at' => now()]);
        $erasureUsed = Product::create([
            'user_id' => $erasureUser->id,
            'category_id' => $auctionCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Erased Seller Camera',
            'slug' => 'erased-seller-camera-wf50',
            'description' => 'Camera',
            'base_price' => 1500000,
            'total_stock' => 1,
            'status' => 'active',
            'used_status' => 'active',
        ]);
        $fleetPartner = \App\Models\DeliveryPartner::create([
            'user_id' => $erasureUser->id,
            'name' => 'Erasure Rider',
            'phone' => '9876543299',
            'is_online' => true,
            'is_verified' => true,
        ]);
        $this->actingAs($erasureUser)->post('/account/delete', ['confirm_delete' => '1'])->assertRedirect('/');
        $this->assertSame('inactive', $erasureUsed->fresh()->status);
        $this->assertFalse((bool) $fleetPartner->fresh()->is_online);
        $this->assertFalse((bool) $fleetPartner->fresh()->is_verified);

        // Workflow 51: Vendor/OrderController blocks prepaid accept on cancelled orders, approves/rejects payment proof safely, and requires online fleet partner
        $cancelledOnlineOrder = Order::create([
            'order_number' => 'ORD-WF51-CANC',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 100000,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 100000,
            'item_count' => 1,
            'status' => 'cancelled',
            'payment_method' => 'phonepe',
            'payment_status' => 'pending',
            'settlement_status' => 'cleared',
            'address_snapshot' => ['shipping' => ['city' => 'Jaipur']],
        ]);
        $this->actingAs($this->vendorUser)
            ->post(route('vendor.orders.accept', $cancelledOnlineOrder))
            ->assertSessionHas('error');
        $this->actingAs($this->vendorUser)
            ->post(route('vendor.orders.approve_payment', $cancelledOnlineOrder))
            ->assertSessionHas('error');
        $this->assertSame('pending', $cancelledOnlineOrder->fresh()->payment_status);

        $paidOrder = Order::create([
            'order_number' => 'ORD-WF51-PAID',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'subtotal' => 120000,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 120000,
            'item_count' => 1,
            'status' => 'ready_for_pickup',
            'payment_method' => 'upi',
            'payment_status' => 'paid',
            'address_snapshot' => ['shipping' => ['city' => 'Jaipur']],
        ]);
        $this->actingAs($this->vendorUser)
            ->post(route('vendor.orders.reject_payment', $paidOrder), ['reason' => 'Tamper'])
            ->assertSessionHas('error');
        $this->assertSame('paid', $paidOrder->fresh()->payment_status);

        $offlineRiderUser = User::factory()->create(['email_verified_at' => now()]);
        $fleetRole = \App\Models\Role::firstOrCreate(['name' => 'fleet']);
        $offlineRiderUser->roles()->attach($fleetRole->id);
        $offlineRider = \App\Models\DeliveryPartner::create([
            'user_id' => $offlineRiderUser->id,
            'name' => 'Offline Rider WF51',
            'phone' => '9876543288',
            'is_online' => false,
            'is_verified' => true,
        ]);
        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.orders.assign_partner', $paidOrder), ['delivery_partner_id' => $offlineRider->id])
            ->assertStatus(422);
        $offlineRider->update(['is_online' => true]);
        $this->actingAs($this->vendorUser)
            ->postJson(route('vendor.orders.assign_partner', $paidOrder), ['delivery_partner_id' => $offlineRider->id])
            ->assertStatus(200);
        $this->assertSame('ready_for_pickup', $paidOrder->fresh()->status);

        // Workflow 52: ServiceOrderController rejects non-services product inside transaction
        $this->actingAs($this->buyer)
            ->post(route('services.order.place', $auctionProduct), [
                'tier' => 'basic',
                'requirements' => 'Build me a full store workflow test requirements',
            ])
            ->assertSessionHas('error');
        $this->assertSame(0, \App\Models\ServiceOrder::where('product_id', $auctionProduct->id)->count());

        // Workflow 53: FoodController hides exhausted coupons & escapes LIKE wildcards in food.suggest
        $foodUser = User::factory()->create(['email_verified_at' => now()]);
        $foodVendor = Vendor::create([
            'user_id' => $foodUser->id,
            'shop_name' => 'Saffron Spoon Kitchen WF53',
            'slug' => 'saffron-spoon-wf53',
            'vendor_type' => 'food',
            'city' => 'Jaipur',
            'status' => 'approved',
        ]);
        \App\Models\VendorSetting::create([
            'vendor_id' => $foodVendor->id,
            'is_restaurant' => true,
            'is_accepting_food_orders' => true,
            'cuisine_types' => 'Rajasthani',
        ]);
        $this->grantVendorTestSubscription($foodVendor, 'food');
        Coupon::create([
            'vendor_id' => $foodVendor->id,
            'code' => 'EXHAUSTED53',
            'type' => 'fixed',
            'value' => 5000,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(5),
            'total_limit' => 2,
            'usage_count' => 2,
        ]);
        Coupon::create([
            'vendor_id' => $foodVendor->id,
            'code' => 'LIVEFOOD53',
            'type' => 'fixed',
            'value' => 4000,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(5),
            'total_limit' => 10,
            'usage_count' => 1,
        ]);

        $this->get(route('food.restaurant', $foodVendor->slug))
            ->assertOk()
            ->assertDontSee('EXHAUSTED53')
            ->assertSee('LIVEFOOD53');
        $suggestPercent = $this->getJson(route('food.suggest', ['q' => '%%%']));
        $suggestPercent->assertOk()->assertJson(['restaurants' => [], 'dishes' => []]);
    }

    public function test_workflow_54_55_56_57_58_59_used_visibility_bookings_reviews_reports_and_wishlist(): void
    {
        // Workflow 54 & 55: UsedThreadController blocks reply on sold/unavailable ad & UsedSellerController enforces vendor visibility
        $usedCat = Category::firstOrCreate(['slug' => 'used-tech-wf54'], ['name' => 'Used Tech', 'vertical' => 'used', 'is_active' => true, 'sort_order' => 1]);
        $suspendedVendorUser = User::factory()->create(['email_verified_at' => now()]);
        $suspendedUsedVendor = Vendor::create([
            'user_id' => $suspendedVendorUser->id,
            'shop_name' => 'Paused Used Dealer WF55',
            'slug' => 'paused-used-dealer-wf55',
            'vendor_type' => 'used',
            'status' => 'suspended',
        ]);
        $sellerProfile = \App\Models\UsedSellerProfile::create([
            'user_id' => $suspendedVendorUser->id,
            'handle' => 'paused-dealer-wf55',
            'display_name' => 'Paused Used Dealer',
            'show_sold_history' => true,
        ]);
        $hiddenVendorAd = Product::create([
            'vendor_id' => $suspendedUsedVendor->id,
            'user_id' => $suspendedVendorUser->id,
            'category_id' => $usedCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Suspended Vendor iPad Pro WF55',
            'slug' => 'suspended-ipad-wf55',
            'description' => 'Should stay hidden',
            'base_price' => 4000000,
            'total_stock' => 1,
            'status' => 'active',
            'used_status' => 'active',
        ]);

        $this->get(route('used.seller.profile', $sellerProfile->handle))
            ->assertOk()
            ->assertDontSee('Suspended Vendor iPad Pro WF55');

        $soldAd = Product::create([
            'user_id' => $this->vendorUser->id,
            'category_id' => $usedCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Sold Mac Mini WF54',
            'slug' => 'sold-mac-mini-wf54',
            'description' => 'Sold item',
            'base_price' => 3000000,
            'total_stock' => 0,
            'status' => 'active',
            'used_status' => 'sold',
            'is_sold' => true,
        ]);
        $thread = \App\Models\UsedThread::create([
            'product_id' => $soldAd->id,
            'seller_id' => $this->vendorUser->id,
            'buyer_id' => $this->buyer->id,
            'status' => 'active',
        ]);
        $this->actingAs($this->buyer)
            ->postJson(route('used.threads.reply', $thread), ['body' => 'Still available?'])
            ->assertStatus(422);

        // Workflow 56: BookingController validates transition inside lock & booking view has no dummy phone fallback
        $svcCat = Category::firstOrCreate(['slug' => 'services-wf56'], ['name' => 'Services WF56', 'vertical' => 'services', 'is_active' => true, 'sort_order' => 1]);
        $svcProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCat->id,
            'vertical' => 'services',
            'listing_type' => 'booking',
            'title' => 'Home Consultation WF56',
            'slug' => 'home-consultation-wf56',
            'description' => 'On-site survey',
            'base_price' => 50000,
            'total_stock' => 10,
            'status' => 'active',
        ]);
        $booking = \App\Models\Booking::create([
            'booking_number' => 'BK-WF56-TEST',
            'product_id' => $svcProduct->id,
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'customer_name' => 'Rohan Verma',
            'customer_phone' => '9811223344',
            'service_address' => '21 Park Ave',
            'booking_date' => now()->addDay()->toDateString(),
            'booking_slot' => '10:00 AM',
            'total_amount' => 50000,
            'status' => 'cancelled',
        ]);
        $this->actingAs($this->vendorUser)
            ->put(route('vendor.bookings.status', $booking), ['status' => 'confirmed'])
            ->assertSessionHas('error');
        $this->assertSame('cancelled', $booking->fresh()->status);

        $this->actingAs($this->buyer)
            ->get(route('bookings.show', $booking->booking_number))
            ->assertOk()
            ->assertDontSee('919876543210');

        // Workflow 57: ReviewController food order review uses transactional duplicate lock
        $foodOrder = Order::create([
            'order_number' => 'ORD-WF57-FOOD',
            'user_id' => $this->buyer->id,
            'vendor_id' => $this->vendor->id,
            'order_type' => 'food_delivery',
            'subtotal' => 45000,
            'shipping_amount' => 0,
            'tax_amount' => 0,
            'total_amount' => 45000,
            'item_count' => 1,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'payment_method' => 'upi',
            'address_snapshot' => ['shipping' => ['city' => 'Jaipur']],
        ]);
        $this->actingAs($this->buyer)
            ->post(route('food.orders.review', $foodOrder), ['rating' => 5, 'body' => 'Delicious piping hot food!'])
            ->assertSessionHas('success');
        $this->actingAs($this->buyer)
            ->post(route('food.orders.review', $foodOrder), ['rating' => 1, 'body' => 'Duplicate attempt'])
            ->assertSessionHas('error');
        $this->assertSame(1, \App\Models\Review::where('order_id', $foodOrder->id)->count());

        // Workflow 58: ReportController blocks self-reporting account or own product
        $retailProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCat->id,
            'vertical' => 'products',
            'listing_type' => 'purchase',
            'title' => 'Active Retail Item WF58',
            'slug' => 'active-retail-item-wf58',
            'description' => 'Active listing',
            'base_price' => 25000,
            'total_stock' => 5,
            'status' => 'active',
        ]);
        $this->actingAs($this->vendorUser)
            ->postJson(route('reports.store'), [
                'reportable_type' => 'product',
                'reportable_id' => $retailProduct->id,
                'reason' => 'spam',
            ])
            ->assertStatus(422);
        $this->actingAs($this->buyer)
            ->postJson(route('reports.store'), [
                'reportable_type' => 'user',
                'reportable_id' => $this->buyer->id,
                'reason' => 'spam',
            ])
            ->assertStatus(422);

        // Workflow 59: WishlistController toggle count excludes hidden/inactive products
        $deadProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $svcCat->id,
            'vertical' => 'products',
            'listing_type' => 'purchase',
            'title' => 'Archived Item WF59',
            'slug' => 'archived-item-wf59',
            'description' => 'Now inactive',
            'base_price' => 10000,
            'total_stock' => 5,
            'status' => 'inactive',
        ]);
        Wishlist::create(['user_id' => $this->buyer->id, 'product_id' => $deadProduct->id]);
        $toggleRes = $this->actingAs($this->buyer)
            ->postJson(route('wishlist.toggle'), ['product_id' => $retailProduct->id]);
        $toggleRes->assertOk()
            ->assertJson(['success' => true, 'is_wishlisted' => true, 'count' => 1]);
    }
}
