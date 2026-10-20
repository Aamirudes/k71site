<?php

namespace Tests\Feature;

use App\Models\Bid;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class AuctionsAndMessagingTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private User $secondBuyer;
    private User $vendorUser;
    private Vendor $vendor;
    private Category $category;
    private Product $auctionProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create([
            'first_name' => 'Aditya',
            'last_name' => 'Sharma',
            'email' => 'aditya@example.com',
        ]);

        $this->secondBuyer = User::factory()->create([
            'first_name' => 'Vikram',
            'last_name' => 'Mehta',
            'email' => 'vikram@example.com',
        ]);

        $this->vendorUser = User::factory()->create([
            'first_name' => 'Sunil',
            'last_name' => 'Joshi',
            'email' => 'sunil@heritageauctions.test',
        ]);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Heritage Art & Antiquities',
            'slug' => 'heritage-art',
            'status' => 'approved',
            'phone' => '9820011223',
            'email' => 'curator@heritageart.test',
            'city' => 'Udaipur',
            'state' => 'Rajasthan',
            'pincode' => '313001',
        ]);

        VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => true,
        ]);

        $this->category = Category::firstOrCreate(
            ['slug' => 'vintage-collectibles'],
            [
                'name' => 'Vintage & Collectibles',
                'vertical' => 'main',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->auctionProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'main',
            'listing_type' => 'auction',
            'title' => 'Antique Hand-Carved Brass Urli 1920',
            'slug' => 'antique-hand-carved-brass-urli-1920',
            'description' => 'Rare museum quality royal urli bowl from Mewar region.',
            'base_price' => 500000, // ₹5,000 starting price
            'bid_increment' => 50000, // ₹500 increment
            'gst_rate' => 18,
            'auction_status' => 'live',
            'auction_starts_at' => Carbon::now()->subDay(),
            'auction_ends_at' => Carbon::now()->addDays(2),
            'status' => 'active',
            'total_stock' => 1,
        ]);
    }

    public function test_auction_page_displays_live_bidding_details_and_min_bid(): void
    {
        $response = $this->get(route('auctions.show', $this->auctionProduct->slug));

        $response->assertStatus(200);
        $response->assertSee('Live Auction');
        $response->assertSee('Current High Bid');
        $response->assertSee('₹5,000.00'); // Base price formatted
        $response->assertSee('Place Bid');
    }

    public function test_authenticated_user_can_place_valid_bid(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 5500, // ₹5,500
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bids', [
            'product_id' => $this->auctionProduct->id,
            'user_id' => $this->buyer->id,
            'bid_amount' => 550000,
            'status' => 'winning',
        ]);

        $this->auctionProduct->refresh();
        $this->assertEquals(550000, $this->auctionProduct->current_bid);
        $this->assertEquals($this->buyer->id, $this->auctionProduct->winning_bidder_id);
        $this->assertEquals(1, $this->auctionProduct->bids_count);
    }

    public function test_bid_below_minimum_increment_is_rejected(): void
    {
        // First bid by Buyer 1: ₹5,500
        $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 5500,
            ]);

        $this->auctionProduct->refresh();
        // Minimum next bid is now ₹5,500 + ₹500 = ₹6,000

        // Buyer 2 tries to bid ₹5,700 (below minimum required)
        $response = $this->actingAs($this->secondBuyer)
            ->from(route('auctions.show', $this->auctionProduct->slug))
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 5700,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('bid_amount');

        // Verify second bid was NOT created
        $this->assertDatabaseMissing('bids', [
            'user_id' => $this->secondBuyer->id,
            'bid_amount' => 570000,
        ]);
    }

    public function test_previous_highest_bid_marked_as_outbid_when_new_higher_bid_placed(): void
    {
        // Buyer 1 places winning bid of ₹5,500
        $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 5500,
            ]);

        $bid1 = Bid::where('user_id', $this->buyer->id)->first();
        $this->assertEquals('winning', $bid1->status);

        // Buyer 2 outbids with ₹6,000
        $this->actingAs($this->secondBuyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 6000,
            ]);

        $bid1->refresh();
        $this->assertEquals('outbid', $bid1->status);

        $bid2 = Bid::where('user_id', $this->secondBuyer->id)->first();
        $this->assertEquals('winning', $bid2->status);

        $this->auctionProduct->refresh();
        $this->assertEquals(600000, $this->auctionProduct->current_bid);
        $this->assertEquals($this->secondBuyer->id, $this->auctionProduct->winning_bidder_id);
    }

    public function test_highest_bidder_cannot_outbid_themselves(): void
    {
        // Buyer 1 places winning bid of ₹5,500
        $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 5500,
            ]);

        // Buyer 1 tries to bid again immediately
        $response = $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 6000,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'You are already the highest bidder on this auction!');

        // Bids count should still be 1
        $this->auctionProduct->refresh();
        $this->assertEquals(1, $this->auctionProduct->bids_count);
    }

    public function test_bidding_rejected_when_auction_has_ended(): void
    {
        $this->auctionProduct->update([
            'auction_status' => 'ended',
            'auction_ends_at' => Carbon::now()->subMinute(),
        ]);

        $response = $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 6000,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This auction has ended or is not currently active.');
        $this->assertDatabaseCount('bids', 0);
    }

    public function test_anti_sniping_extends_auction_if_bid_placed_in_last_five_minutes(): void
    {
        $nearEndTime = Carbon::now()->addMinutes(3);
        $this->auctionProduct->update([
            'auction_ends_at' => $nearEndTime,
        ]);

        $this->actingAs($this->buyer)
            ->post(route('auctions.bid', $this->auctionProduct), [
                'bid_amount' => 5500,
            ]);

        $this->auctionProduct->refresh();

        // The new auction_ends_at should be nearEndTime + 5 minutes
        $this->assertTrue($this->auctionProduct->auction_ends_at->greaterThan($nearEndTime));
        $this->assertEquals(
            $nearEndTime->copy()->addMinutes(5)->format('Y-m-d H:i'),
            $this->auctionProduct->auction_ends_at->format('Y-m-d H:i')
        );
    }

    public function test_customer_can_send_direct_message_to_seller(): void
    {
        $response = $this->actingAs($this->buyer)
            ->post(route('messages.store'), [
                'vendor_id' => $this->vendor->id,
                'product_id' => $this->auctionProduct->id,
                'subject' => 'Authenticity inquiry for Antique Urli',
                'body' => 'Can you provide provenance or previous royal collection certificates for this urli?',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('conversations', [
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'product_id' => $this->auctionProduct->id,
            'subject' => 'Authenticity inquiry for Antique Urli',
        ]);

        $conversation = Conversation::where('user_id', $this->buyer->id)->first();

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $this->buyer->id,
            'body' => 'Can you provide provenance or previous royal collection certificates for this urli?',
            'is_read' => false,
        ]);
    }

    public function test_vendor_can_reply_to_customer_message(): void
    {
        $conversation = Conversation::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'product_id' => $this->auctionProduct->id,
            'subject' => 'Urli authenticity',
            'last_message_at' => now(),
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $this->buyer->id,
            'body' => 'Is this urli genuine solid brass?',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->vendorUser)
            ->post(route('vendor.messages.reply', $conversation->id), [
                'body' => 'Yes, tested 100% solid brass with authentic patina and markings.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'vendor',
            'sender_id' => $this->vendor->id,
            'body' => 'Yes, tested 100% solid brass with authentic patina and markings.',
            'is_read' => false,
        ]);
    }

    public function test_viewing_conversation_marks_incoming_messages_as_read(): void
    {
        $conversation = Conversation::create([
            'vendor_id' => $this->vendor->id,
            'user_id' => $this->buyer->id,
            'subject' => 'Urli delivery query',
            'last_message_at' => now(),
        ]);

        $buyerMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $this->buyer->id,
            'body' => 'Do you ship to Mumbai with wooden crate packing?',
            'is_read' => false,
        ]);

        $vendorMessage = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'vendor',
            'sender_id' => $this->vendor->id,
            'body' => 'Yes, customized wooden crate shipping is complimentary.',
            'is_read' => false,
        ]);

        // When vendor views the thread, buyer's message becomes read
        $this->actingAs($this->vendorUser)
            ->get(route('vendor.messages.show', $conversation->id));

        $buyerMessage->refresh();
        $this->assertTrue($buyerMessage->is_read);
        $this->assertNotNull($buyerMessage->read_at);

        // Vendor's message is still unread for customer
        $vendorMessage->refresh();
        $this->assertFalse($vendorMessage->is_read);

        // When customer views the thread, vendor's message becomes read
        $this->actingAs($this->buyer)
            ->get(route('messages.show', $conversation->id));

        $vendorMessage->refresh();
        $this->assertTrue($vendorMessage->is_read);
        $this->assertNotNull($vendorMessage->read_at);
    }

    public function test_customer_can_view_and_mark_notifications_as_read(): void
    {
        $notificationId = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => 'App\Notifications\OutbidNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->buyer->id,
            'data' => json_encode([
                'title' => 'Outbid Alert',
                'message' => 'Someone placed a higher bid on Antique Urli.',
                'icon' => '🔨',
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Customer views notifications page
        $response = $this->actingAs($this->buyer)->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('Outbid Alert');
        $response->assertSee('Someone placed a higher bid on Antique Urli.');

        // Customer marks single notification as read
        $this->actingAs($this->buyer)
            ->post(route('notifications.read', $notificationId));

        $this->assertNotNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));
    }
}
