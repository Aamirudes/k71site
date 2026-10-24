<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Short;
use App\Models\ShortBoost;
use App\Models\ShortCoin;
use App\Models\ShortEvent;
use App\Models\SpinPrize;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ShortsFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendorUser;
    protected Vendor $vendor;
    protected Category $category;
    protected Product $product;
    protected Short $short;

    protected function setUp(): void
    {
        parent::setUp();

        $role = \App\Models\Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser = User::factory()->create([
            'email' => 'creator@k71.in',
        ]);
        $this->vendorUser->roles()->syncWithoutDetaching([$role->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'slug' => 'atelier-craft',
            'shop_name' => 'Atelier Craft',
            'description' => 'Fine handcrafted fashion & textiles',
            'status' => 'approved',
            'approved_at' => now(),
        ]);

        $this->category = Category::create([
            'name' => 'Kurtis & Sets',
            'slug' => 'kurtis-sets',
        ]);

        $this->product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'title' => 'Chanderi Silk Anarkali',
            'slug' => 'chanderi-silk-anarkali',
            'description' => 'Royal handwoven Chanderi silk anarkali set with zardozi embroidery.',
            'base_price' => 299900, // ₹2,999 in paise
            'compare_at_price' => 399900, // ₹3,999 in paise
            'gst_rate' => 5.00,
            'status' => 'active',
            'total_stock' => 10,
        ]);

        \App\Models\ProductVariant::create([
            'product_id' => $this->product->id,
            'title' => 'Standard',
            'sku' => 'CHAND-STD-01',
            'price' => 299900,
            'stock' => 10,
            'is_active' => true,
        ]);

        $this->short = Short::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'title' => 'Royal Chanderi Silk Styling',
            'slug' => 'royal-chanderi-silk-styling',
            'description' => 'Festive styling ideas with our signature Chanderi suit #fashion',
            'video_url' => 'https://assets.k71.in/videos/sample.mp4',
            'thumb_url' => 'https://assets.k71.in/thumbs/sample.jpg',
            'ratio' => '9:16',
            'duration' => 30,
            'status' => 'published',
            'views' => 100,
            'likes' => 20,
            'shares' => 5,
        ]);

        $this->short->products()->attach($this->product->id, ['sort_order' => 0]);
    }

    public function test_public_shorts_feed_renders_successfully(): void
    {
        $response = $this->get('/shorts');
        $response->assertStatus(200);
        $response->assertViewIs('shorts.index');
        $response->assertSee('window.ZSU =', false);
    }

    public function test_ajax_get_shorts_returns_structured_payload(): void
    {
        $response = $this->postJson('/shorts/ajax', [
            'action' => 'zsu_get_shorts',
            'page' => 1,
            'per_page' => 10,
            'filter' => 'latest',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'total' => 1,
        ]);

        $response->assertJsonPath('shorts.0.id', $this->short->id);
        $response->assertJsonPath('shorts.0.title', 'Royal Chanderi Silk Styling');
        $response->assertJsonPath('shorts.0.vendor_name', 'Atelier Craft');
    }

    public function test_recording_view_increments_counter_and_logs_event(): void
    {
        $initialViews = $this->short->views;

        $response = $this->postJson('/shorts/ajax', [
            'action' => 'zsu_record_view',
            'short_id' => $this->short->id,
            'watch_pct' => 85,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'recorded' => true]);

        $this->assertEquals($initialViews + 1, $this->short->fresh()->views);
        $this->assertDatabaseHas('short_events', [
            'short_id' => $this->short->id,
            'event_type' => 'view',
            'watch_pct' => 85,
        ]);
    }

    public function test_recording_like_toggles_and_creates_event(): void
    {
        $this->actingAs($this->vendorUser);
        $initialLikes = $this->short->likes;

        // 1. Like
        $response = $this->postJson('/shorts/ajax', [
            'action' => 'zsu_record_like',
            'short_id' => $this->short->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'action' => 'liked']);
        $this->assertEquals($initialLikes + 1, $this->short->fresh()->likes);

        // 2. Unlike (toggle)
        $unlikeResponse = $this->postJson('/shorts/ajax', [
            'action' => 'zsu_record_like',
            'short_id' => $this->short->id,
        ]);

        $unlikeResponse->assertStatus(200);
        $unlikeResponse->assertJson(['success' => true, 'action' => 'unliked']);
        $this->assertEquals($initialLikes, $this->short->fresh()->likes);
    }

    public function test_get_products_returns_tagged_store_items_with_pricing(): void
    {
        $response = $this->postJson('/shorts/ajax', [
            'action' => 'zsu_get_products',
            'short_id' => $this->short->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonPath('products.0.id', $this->product->id);
        $response->assertJsonPath('products.0.name', 'Chanderi Silk Anarkali');
        $response->assertJsonPath('products.0.price_raw', 2999);
        $response->assertJsonPath('products.0.in_stock', true);
    }

    public function test_spin_wheel_awards_verified_coupon_prize_per_decision_d8(): void
    {
        // Decision D8: Every prize must map to a real coupon in coupons table
        $response = $this->postJson('/shorts/spin/result');

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'won' => true]);

        $couponCode = $response->json('coupon_code');
        $this->assertNotEmpty($couponCode);

        // Verify coupon exists and is active in database
        $this->assertDatabaseHas('coupons', [
            'code' => $couponCode,
            'is_active' => true,
        ]);
    }

    public function test_vendor_can_save_short_and_launch_boost_campaign(): void
    {
        $this->actingAs($this->vendorUser);

        // Seed vendor coins balance
        ShortCoin::create([
            'user_id' => $this->vendorUser->id,
            'delta' => 1000,
            'balance' => 1000,
            'reason' => 'Starter coin grant',
        ]);

        // 1. Save new short
        $saveResponse = $this->postJson('/vendor/shorts/ajax', [
            'action' => 'zsu_save_short',
            'title' => 'Festive Saree Draping Tutorial',
            'description' => '5 unique ways to style your organza saree',
            'category_id' => $this->category->id,
            'video_url' => 'https://assets.k71.in/videos/saree.mp4',
            'ratio' => '9:16',
            'product_ids' => [$this->product->id],
        ]);

        $saveResponse->assertStatus(200);
        $saveResponse->assertJson(['success' => true]);
        $shortId = $saveResponse->json('short_id');

        $this->assertDatabaseHas('shorts', [
            'id' => $shortId,
            'vendor_id' => $this->vendor->id,
            'title' => 'Festive Saree Draping Tutorial',
        ]);

        // 2. Request standard boost (199 coins)
        $boostResponse = $this->postJson('/vendor/shorts/ajax', [
            'action' => 'zsu_request_boost',
            'short_id' => $shortId,
            'tier' => 'standard',
        ]);

        $boostResponse->assertStatus(200);
        $boostResponse->assertJson(['success' => true, 'is_boosted' => true]);

        // Short should now be boosted
        $newShort = Short::find($shortId);
        $this->assertTrue($newShort->is_boosted);
        $this->assertEquals('standard', $newShort->boost_type);

        // Balance should be deducted: 1000 - 199 = 801
        $latestCoin = ShortCoin::where('user_id', $this->vendorUser->id)->latest('id')->first();
        $this->assertEquals(801, $latestCoin->balance);
    }

    public function test_boost_expiry_command_expires_past_campaigns(): void
    {
        // Create an expired boost
        $boost = ShortBoost::create([
            'short_id' => $this->short->id,
            'vendor_id' => $this->vendor->id,
            'boost_type' => 'prime',
            'coins_spent' => 499,
            'starts_at' => now()->subDays(5),
            'ends_at' => now()->subHours(1), // already ended
            'status' => 'active',
        ]);

        $this->short->update(['is_boosted' => true, 'boost_type' => 'prime']);

        // Run console command
        Artisan::call('shorts:expire-boosts');

        $this->assertEquals('expired', $boost->fresh()->status);
        $this->assertFalse((bool) $this->short->fresh()->is_boosted);
        $this->assertNull($this->short->fresh()->boost_type);
    }

    public function test_trending_score_recalculation_command_updates_weighted_score(): void
    {
        // Clear events and seed specific events:
        // Score = (views * 1) + (likes * 4) + (shares * 6) + (clicks * 5) + (comments * 3)
        // 10 views (10*1=10) + 2 likes (2*4=8) + 1 share (1*6=6) = 24.00
        ShortEvent::where('short_id', $this->short->id)->delete();

        for ($i = 0; $i < 10; $i++) {
            ShortEvent::create(['short_id' => $this->short->id, 'event_type' => 'view', 'created_at' => now()->subDays(1)]);
        }
        for ($i = 0; $i < 2; $i++) {
            ShortEvent::create(['short_id' => $this->short->id, 'event_type' => 'like', 'created_at' => now()->subDays(1)]);
        }
        ShortEvent::create(['short_id' => $this->short->id, 'event_type' => 'share', 'created_at' => now()->subDays(1)]);

        Artisan::call('shorts:recalculate-trending');

        $this->assertEquals(24.00, (float) $this->short->fresh()->trending_score);
    }

    public function test_flipkart_shoppable_video_commerce_homepage_renders_all_components(): void
    {
        $response = $this->get('/shorts');

        $response->assertStatus(200);
        $response->assertSee('K71');
        $response->assertSee('SHORTS');
        $response->assertSee('Live Video Shopping');
        $response->assertSee('Trending Shoppable Reels');
        $response->assertSee('Shop What You Watch');
        $response->assertSee($this->short->title);
        $response->assertSee($this->product->title);
        $response->assertSee('flipkartShortsViewer', false);
    }

    public function test_tagged_product_can_be_added_directly_to_cart_via_ajax(): void
    {
        $this->withSession(['guest_active' => true]);

        $response = $this->postJson(route('cart.add'), [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Verify cart items table has record
        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 1,
        ]);
    }

    public function test_vertical_subdomain_routes_shorts_seamlessly(): void
    {
        $response = $this->get('http://shorts.marblemandi.org/');

        $response->assertStatus(200);
        $response->assertViewIs('shorts.index');
        $response->assertSee('K71');
        $response->assertSee('SHORTS');
    }
}

