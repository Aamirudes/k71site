<?php

namespace Tests\Feature;

use App\Http\Controllers\MessageController;
use App\Models\Booking;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\DeliveryAssignment;
use App\Models\DeliveryPartner;
use App\Models\Enquiry;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProjectContract;
use App\Models\ProjectMilestone;
use App\Models\ProjectProposal;
use App\Models\Quote;
use App\Models\Review;
use App\Models\Role;
use App\Models\ServiceOrder;
use App\Models\ServiceProject;
use App\Models\Short;
use App\Models\UsedThread;
use App\Models\UsedTransaction;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
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
}
