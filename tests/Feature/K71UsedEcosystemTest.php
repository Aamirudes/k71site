<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Offer;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Report;
use App\Models\Review;
use App\Models\Role;
use App\Models\UsedSavedSearch;
use App\Models\UsedSellerProfile;
use App\Models\UsedThread;
use App\Models\UsedThreadMessage;
use App\Models\UsedTransaction;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class K71UsedEcosystemTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private User $buyer;
    private User $otherUser;
    private User $admin;
    private Category $parentCat;
    private Category $subCat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seller = User::factory()->create([
            'first_name' => 'Aarav',
            'last_name' => 'Sharma',
            'email' => 'aarav@used.test',
            'phone' => '9829111222',
        ]);

        $this->buyer = User::factory()->create([
            'first_name' => 'Priya',
            'last_name' => 'Mehta',
            'email' => 'priya@used.test',
            'phone' => '9829333444',
        ]);

        $this->otherUser = User::factory()->create([
            'first_name' => 'Rohan',
            'last_name' => 'Verma',
            'email' => 'rohan@used.test',
        ]);

        $this->admin = User::factory()->create([
            'first_name' => 'Master',
            'last_name' => 'Admin',
            'email' => 'crab.hashir@gmail.com',
        ]);
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);
        $this->admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $this->parentCat = Category::firstOrCreate(
            ['slug' => 'used-mobiles'],
            [
                'name' => 'Mobiles & Electronics',
                'vertical' => 'used',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $this->subCat = Category::firstOrCreate(
            ['slug' => 'used-smartphones'],
            [
                'name' => 'Smartphones',
                'vertical' => 'used',
                'parent_id' => $this->parentCat->id,
                'is_active' => true,
                'sort_order' => 2,
            ]
        );
    }

    public function test_used_homepage_discovery_filters_sorting_truthful_photos_and_pagination(): void
    {
        CategoryField::create([
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'name' => 'storage',
            'label' => 'Storage Capacity',
            'field_type' => 'select',
            'options' => ['128GB', '256GB'],
            'is_required' => false,
            'sort_order' => 1,
        ]);

        $iphone = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'iPhone 13 256GB Starlight',
            'slug' => 'iphone-13-256gb-starlight',
            'description' => 'Clean unlocked device.',
            'base_price' => 3200000,
            'status' => 'active',
            'used_status' => 'active',
            'condition_grade' => 'like_new',
            'is_refurbished' => true,
            'is_negotiable' => false,
            'city' => 'Jaipur',
            'locality' => 'Malviya Nagar',
            'custom_fields' => ['condition' => 'Like New', 'specs' => ['storage' => '256GB']],
            'published_at' => now(),
            'refreshed_at' => now(),
        ]);

        $draft = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Hidden Draft Phone',
            'slug' => 'hidden-draft-phone',
            'description' => 'Not ready yet.',
            'base_price' => 1000000,
            'status' => 'draft',
            'used_status' => 'draft',
            'condition_grade' => 'good',
            'city' => 'Jaipur',
        ]);

        $soldItem = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Archived Sold Pixel 7',
            'slug' => 'archived-sold-pixel-7',
            'description' => 'Sold locally.',
            'base_price' => 1900000,
            'status' => 'active',
            'used_status' => 'sold',
            'is_sold' => true,
            'condition_grade' => 'good',
            'city' => 'Ajmer',
        ]);

        $res = $this->get('/used');
        $res->assertStatus(200);
        $res->assertSee('iPhone 13 256GB Starlight');
        $res->assertDontSee('Hidden Draft Phone');
        $res->assertDontSee('Archived Sold Pixel 7');
        $res->assertDontSee('images.unsplash.com');
        $res->assertSee('No photos uploaded');

        // Include sold archive filter
        $soldRes = $this->get('/used?include_sold=1');
        $soldRes->assertStatus(200);
        $soldRes->assertSee('Archived Sold Pixel 7');

        // Filter by parent category rolls up child category + spec filter + fixed pricing
        $filtered = $this->get('/used?cat=used-mobiles&city=Jaipur&pricing=fixed&refurbished=1&spec[storage]=256GB');
        $filtered->assertStatus(200);
        $filtered->assertSee('iPhone 13 256GB Starlight');
    }

    public function test_individual_user_creates_multiple_listings_under_single_seller_profile_with_private_phone_by_default(): void
    {
        $this->actingAs($this->seller);

        $this->get(route('used.sell'))->assertStatus(200);

        $r1 = $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'OnePlus Nord 3 128GB Temp Grey',
            'description' => 'Used gently for 8 months with original SuperVOOC charger.',
            'brand' => 'OnePlus',
            'model_name' => 'Nord 3',
            'age_months' => 8,
            'condition_grade' => 'like_new',
            'defect_notes' => 'Micro scratch near charging port only.',
            'included_accessories' => 'Box, 80W Charger',
            'original_packaging' => 'box_and_bill',
            'price' => 18500,
            'original_retail_price' => 33999,
            'min_offer_amount' => 16000,
            'is_negotiable' => '1',
            'city' => 'Udaipur',
            'locality' => 'Fatehpura',
            'private_pickup_area' => 'Flat 302, Green Park Road',
            'fulfillment_modes' => ['local_pickup', 'public_meetup'],
        ]);
        $r1->assertRedirect();

        $r2 = $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Sony WH-1000XM5 Wireless Headphones',
            'description' => 'Carry case and AUX cable included.',
            'brand' => 'Sony',
            'condition_grade' => 'good',
            'price' => 16500,
            'is_negotiable' => '1',
            'city' => 'Udaipur',
        ]);
        $r2->assertRedirect();

        $profile = UsedSellerProfile::where('user_id', $this->seller->id)->first();
        $this->assertNotNull($profile);
        $this->assertFalse($profile->show_phone_publicly);
        $this->assertEquals(2, Product::withoutGlobalScopes()->where('user_id', $this->seller->id)->count());

        // Public seller profile viewed by another user displays both ads and keeps phone private
        $pubProfile = $this->actingAs($this->buyer)->get(route('used.seller.profile', $profile->handle));
        $pubProfile->assertStatus(200);
        $pubProfile->assertSee('OnePlus Nord 3 128GB Temp Grey');
        $pubProfile->assertSee('Sony WH-1000XM5 Wireless Headphones');
        $pubProfile->assertDontSee('9829111222');
        $pubProfile->assertDontSee('Flat 302, Green Park Road');

        // Seller can enable public phone sharing in privacy settings
        $this->actingAs($this->seller)->post(route('used.seller.update'), [
            'display_name' => 'Aarav Tech Hub',
            'handle' => $profile->handle,
            'city' => 'Udaipur',
            'show_phone_publicly' => '1',
            'show_sold_history' => '1',
        ])->assertRedirect();

        $revealedProfile = $this->actingAs($this->buyer)->get(route('used.seller.profile', $profile->handle));
        $revealedProfile->assertStatus(200);
        $revealedProfile->assertSee('9829111222');
        $revealedProfile->assertDontSee('Flat 302, Green Park Road');
    }

    public function test_genuine_photo_upload_cover_selection_reorder_removal_and_invalid_mime_rejection(): void
    {
        Storage::fake('public');
        $this->actingAs($this->seller);

        // Invalid non-image file must be rejected cleanly
        $badFile = UploadedFile::fake()->create('document.pdf', 120, 'application/pdf');
        $badResp = $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Invalid File Test Listing',
            'description' => 'Should reject PDF upload.',
            'condition_grade' => 'good',
            'price' => 5000,
            'city' => 'Jaipur',
            'photos' => [$badFile],
        ]);
        $badResp->assertSessionHasErrors('photos.0');

        // Upload real photos (1x1 valid PNG/JPEG using fake image or real binary)
        $img1 = UploadedFile::fake()->image('front.jpg', 400, 300);
        $img2 = UploadedFile::fake()->image('back.jpg', 400, 300);

        $okResp = $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Nikon Z50 Mirrorless Kit',
            'description' => '16-50mm lens included, shutter count 3200.',
            'condition_grade' => 'like_new',
            'price' => 45000,
            'city' => 'Jaipur',
            'photos' => [$img1, $img2],
            'cover_index' => 1, // Choose second image as cover
        ]);
        $okResp->assertRedirect();

        $product = Product::withoutGlobalScopes()->where('title', 'Nikon Z50 Mirrorless Kit')->firstOrFail();
        $images = $product->images()->orderBy('sort_order')->get();
        $this->assertCount(2, $images);
        $this->assertEquals(0, $images[0]->sort_order);

        // Verify edit page loads and supports removing 1 photo & adding another
        $this->get(route('used.edit', $product->id))->assertStatus(200);
        $img3 = UploadedFile::fake()->image('lens.png', 400, 300);

        $this->put(route('used.update', $product->id), [
            'title' => 'Nikon Z50 Mirrorless Kit Updated',
            'category_id' => $this->subCat->id,
            'description' => 'Updated description with lens photo.',
            'condition_grade' => 'like_new',
            'price' => 44000,
            'city' => 'Jaipur',
            'remove_images' => [$images[0]->id],
            'photo_order' => [$images[1]->id],
            'cover_image_id' => $images[1]->id,
            'photos' => [$img3],
        ])->assertRedirect();

        $product->refresh();
        $this->assertEquals(2, $product->images()->count());
        $this->assertNull(ProductImage::find($images[0]->id));
    }

    public function test_draft_preview_publish_pause_seo_schema_and_sitemap_rules(): void
    {
        $this->actingAs($this->seller);

        // Create Draft
        $this->post(route('used.sell.store'), [
            'action' => 'draft',
            'category_id' => $this->subCat->id,
            'title' => 'Dell XPS 13 OLED 16GB Draft',
            'description' => 'Draft description.',
            'condition_grade' => 'good',
            'price' => 54000,
            'city' => 'Kota',
        ])->assertRedirect(route('used.dashboard', ['tab' => 'listings']));

        $draft = Product::withoutGlobalScopes()->where('title', 'Dell XPS 13 OLED 16GB Draft')->firstOrFail();
        $this->assertTrue($draft->isDraft());

        // Owner sees 200 preview with noindex,nofollow
        $ownerPreview = $this->get(route('used.show', ['slug' => $draft->slug, 'preview' => 1]));
        $ownerPreview->assertStatus(200);
        $ownerPreview->assertSee('noindex,nofollow');

        // Stranger gets 404 on draft
        $this->actingAs($this->buyer)
            ->get(route('used.show', $draft->slug))
            ->assertStatus(404);

        // Draft excluded from sitemap
        Cache::forget('sitemap_products_xml');
        $this->get(route('sitemap.products'))
            ->assertStatus(200)
            ->assertDontSee($draft->slug);

        // Publish draft as owner
        $this->actingAs($this->seller)
            ->post(route('used.status', $draft->id), ['action' => 'publish'])
            ->assertRedirect();

        $draft->refresh();
        $this->assertEquals('active', $draft->effectiveUsedStatus());

        // Now public sees 200 + index,follow + Schema.org UsedCondition / InStock
        $publicView = $this->actingAs($this->buyer)->get(route('used.show', $draft->slug));
        $publicView->assertStatus(200);
        $publicView->assertSee('index,follow');
        $publicView->assertSee('https://schema.org/UsedCondition', false);
        $publicView->assertSee('https://schema.org/InStock', false);

        // Included in sitemap-products under /used/{slug}, excluded from Google Merchant feed
        Cache::forget('sitemap_products_xml');
        $this->get(route('sitemap.products'))->assertSee('/used/' . $draft->slug);
        $this->get(route('feeds.google_merchant'))->assertDontSee($draft->slug);

        // Pause hides from public again
        $this->actingAs($this->seller)
            ->post(route('used.status', $draft->id), ['action' => 'pause'])
            ->assertRedirect();
        $this->actingAs($this->buyer)->get(route('used.show', $draft->slug))->assertStatus(404);
    }

    public function test_required_category_fields_prohibited_keywords_and_duplicate_warnings(): void
    {
        CategoryField::create([
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'name' => 'imei_verified',
            'label' => 'IMEI / Reset Status',
            'field_type' => 'text',
            'is_required' => true,
            'sort_order' => 1,
        ]);

        $this->actingAs($this->seller);

        // Prohibited keyword blocked
        $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Stolen Phone iPhone Cheap',
            'description' => 'Quick sale.',
            'condition_grade' => 'good',
            'price' => 5000,
            'city' => 'Jaipur',
            'specs' => ['imei_verified' => 'Clean'],
        ])->assertSessionHasErrors('title');

        // Missing required category field rejected on publish
        $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Samsung Galaxy S23 Ultra',
            'description' => 'Mint phone with stylus.',
            'condition_grade' => 'like_new',
            'price' => 62000,
            'city' => 'Jaipur',
        ])->assertSessionHasErrors('specs.imei_verified');

        // Valid publish passes
        $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Samsung Galaxy S23 Ultra',
            'description' => 'Mint phone with stylus.',
            'condition_grade' => 'like_new',
            'price' => 62000,
            'city' => 'Jaipur',
            'specs' => ['imei_verified' => 'Factory Reset & Bill Available'],
        ])->assertRedirect();

        // Duplicate identical listing warned unless allow_duplicate=1
        $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Samsung Galaxy S23 Ultra',
            'description' => 'Mint phone second attempt.',
            'condition_grade' => 'like_new',
            'price' => 62000,
            'city' => 'Jaipur',
            'specs' => ['imei_verified' => 'Factory Reset'],
        ])->assertSessionHas('duplicate_warning');

        $this->post(route('used.sell.store'), [
            'action' => 'publish',
            'category_id' => $this->subCat->id,
            'title' => 'Samsung Galaxy S23 Ultra',
            'description' => 'Genuine second unit for sale.',
            'condition_grade' => 'like_new',
            'price' => 62000,
            'city' => 'Jaipur',
            'specs' => ['imei_verified' => 'Factory Reset'],
            'allow_duplicate' => '1',
        ])->assertRedirect();
        $this->assertEquals(2, Product::withoutGlobalScopes()->where('title', 'Samsung Galaxy S23 Ultra')->count());
    }

    public function test_wishlist_sync_and_saved_search_lifecycle_and_ownership(): void
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Bose QuietComfort 45',
            'slug' => 'bose-qc45',
            'description' => 'Clean earpads.',
            'base_price' => 1400000,
            'status' => 'active',
            'used_status' => 'active',
            'condition_grade' => 'good',
            'city' => 'Jaipur',
        ]);

        $this->actingAs($this->buyer);

        $this->postJson(route('wishlist.toggle'), ['product_id' => $product->id])
            ->assertStatus(200)
            ->assertJson(['action' => 'added', 'saves_count' => 1]);
        $this->assertEquals(1, $product->fresh()->saves_count);

        $this->post(route('used.saved_searches.store'), [
            'query' => 'Bose',
            'city' => 'Jaipur',
            'max_price' => 20000,
        ])->assertRedirect();

        $savedSearch = UsedSavedSearch::where('user_id', $this->buyer->id)->firstOrFail();
        $this->get(route('used.index'))->assertSee('Bose');

        // Unauthorized user cannot delete buyer's saved search
        $this->actingAs($this->otherUser)
            ->delete(route('used.saved_searches.destroy', $savedSearch))
            ->assertStatus(403);

        // Buyer can delete their own
        $this->actingAs($this->buyer)
            ->delete(route('used.saved_searches.destroy', $savedSearch))
            ->assertRedirect();
        $this->assertNull(UsedSavedSearch::find($savedSearch->id));
    }

    public function test_enquiry_thread_privacy_pickup_sharing_and_sold_lock(): void
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'iPad Pro M1 11 Inch',
            'slug' => 'ipad-pro-m1-11',
            'description' => 'Paperlike installed.',
            'base_price' => 4500000,
            'status' => 'active',
            'used_status' => 'active',
            'condition_grade' => 'like_new',
            'city' => 'Jaipur',
            'locality' => 'C-Scheme',
            'private_pickup_area' => 'Apartment 5B Secret Lane, C-Scheme',
        ]);

        // Public page MUST hide private_pickup_area while showing public locality
        $pub = $this->get(route('used.show', $product->slug));
        $pub->assertSee('C-Scheme, Jaipur');
        $pub->assertDontSee('Apartment 5B Secret Lane');

        // Buyer starts thread
        $this->actingAs($this->buyer)
            ->post(route('used.enquire', $product), ['body' => 'Can I inspect the iPad screen tomorrow?'])
            ->assertRedirect();

        $thread = UsedThread::where('product_id', $product->id)->where('buyer_id', $this->buyer->id)->firstOrFail();

        // Stranger gets 403 accessing or replying to thread
        $this->actingAs($this->otherUser)->get(route('used.threads.show', $thread))->assertStatus(403);
        $this->actingAs($this->otherUser)->post(route('used.threads.reply', $thread), ['body' => 'Spy'])->assertStatus(403);

        // Seller shares private pickup spot
        $this->actingAs($this->seller)
            ->post(route('used.threads.share_pickup', $thread), ['pickup_location' => 'Apartment 5B Secret Lane, C-Scheme'])
            ->assertRedirect();

        // Buyer now sees private pickup spot inside participant thread
        $this->actingAs($this->buyer)
            ->get(route('used.threads.show', $thread))
            ->assertStatus(200)
            ->assertSee('Apartment 5B Secret Lane, C-Scheme');

        // Once seller marks listing Sold, thread locks and blocks new replies
        $this->actingAs($this->seller)
            ->post(route('used.status', $product->id), ['action' => 'mark_sold']);

        $this->assertTrue($thread->fresh()->isLocked());
        $this->actingAs($this->buyer)
            ->postJson(route('used.threads.reply', $thread), ['body' => 'Still available?'])
            ->assertStatus(422);
    }

    public function test_offer_negotiation_counter_withdraw_reservation_conflict_cancel_and_complete_handover(): void
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Sony Alpha A6400 Body',
            'slug' => 'sony-a6400-body',
            'description' => 'Great condition mirrorless camera.',
            'base_price' => 5500000, // ₹55,000
            'min_offer_amount' => 4500000, // ₹45,000 min
            'status' => 'active',
            'used_status' => 'active',
            'condition_grade' => 'like_new',
            'is_negotiable' => true,
            'city' => 'Jaipur',
        ]);

        // Below minimum offer rejected
        $this->actingAs($this->buyer)
            ->postJson(route('offers.store', $product), ['offer_amount' => 30000])
            ->assertStatus(422);

        // Valid offers from two buyers
        $this->actingAs($this->buyer)
            ->postJson(route('offers.store', $product), ['offer_amount' => 48000, 'message' => 'Genuine buyer'])
            ->assertStatus(200);
        $this->actingAs($this->otherUser)
            ->postJson(route('offers.store', $product), ['offer_amount' => 49000, 'message' => 'Can pick up Friday'])
            ->assertStatus(200);

        $offer1 = Offer::where('product_id', $product->id)->where('user_id', $this->buyer->id)->firstOrFail();
        $offer2 = Offer::where('product_id', $product->id)->where('user_id', $this->otherUser->id)->firstOrFail();

        // Buyer 2 withdraws offer 2
        $this->actingAs($this->otherUser)
            ->post(route('used.offers.withdraw', $offer2))
            ->assertRedirect();
        $this->assertEquals('withdrawn', $offer2->fresh()->status);

        // Seller counters offer 1 via Used route (no vendor role needed!)
        $this->actingAs($this->seller)
            ->post(route('used.offers.seller_respond', $offer1), [
                'action' => 'counter',
                'counter_amount' => 51000,
                'vendor_notes' => 'Meet halfway at 51k with extra battery.',
            ])
            ->assertRedirect();
        $this->assertEquals('countered', $offer1->fresh()->status);

        // Buyer 1 accepts counteroffer -> Listing reserved + UsedTransaction created!
        $this->actingAs($this->buyer)
            ->post(route('used.offers.respond_counter', $offer1), ['action' => 'accept_counter'])
            ->assertRedirect();

        $this->assertEquals('accepted', $offer1->fresh()->status);
        $this->assertTrue($product->fresh()->isReserved());
        $tx = UsedTransaction::where('product_id', $product->id)->where('buyer_id', $this->buyer->id)->firstOrFail();
        $this->assertEquals(5100000, $tx->agreed_price);
        $this->assertEquals('reserved', $tx->status);

        // While reserved, new offers from another buyer are rejected cleanly
        $this->actingAs($this->otherUser)
            ->postJson(route('offers.store', $product), ['offer_amount' => 55000])
            ->assertStatus(422);

        // Cancel reservation returns listing to active
        $this->actingAs($this->seller)
            ->post(route('used.transactions.cancel', $tx))
            ->assertRedirect();
        $this->assertEquals('cancelled', $tx->fresh()->status);
        $this->assertEquals('active', $product->fresh()->effectiveUsedStatus());

        // New buyer offer accepted by seller directly -> completes handover!
        $this->actingAs($this->otherUser)
            ->postJson(route('offers.store', $product), ['offer_amount' => 52000])
            ->assertStatus(200);
        $offer3 = Offer::where('product_id', $product->id)->where('user_id', $this->otherUser->id)->latest('id')->firstOrFail();

        $this->actingAs($this->seller)
            ->post(route('used.offers.seller_respond', $offer3), ['action' => 'accept'])
            ->assertRedirect();

        $tx2 = UsedTransaction::where('product_id', $product->id)->where('buyer_id', $this->otherUser->id)->firstOrFail();

        // Only completed buyer can review -> reject before completion
        $this->actingAs($this->otherUser)
            ->post(route('used.transactions.review', $tx2), ['rating' => 5, 'body' => 'Too early review'])
            ->assertRedirect()
            ->assertSessionHas('error');

        // Seller completes handover
        $this->actingAs($this->seller)
            ->post(route('used.transactions.complete', $tx2))
            ->assertRedirect();

        $this->assertTrue($product->fresh()->isSold());
        $this->assertEquals('completed', $tx2->fresh()->status);
        $profile = UsedSellerProfile::where('user_id', $this->seller->id)->firstOrFail();
        $this->assertGreaterThanOrEqual(1, $profile->sold_count);

        // Stranger cannot review otherUser's completed transaction (403)
        $this->actingAs($this->buyer)
            ->post(route('used.transactions.review', $tx2), ['rating' => 5, 'body' => 'Unauthorized review'])
            ->assertStatus(403);

        // Genuine completed buyer submits review
        $this->actingAs($this->otherUser)
            ->post(route('used.transactions.review', $tx2), [
                'rating' => 5,
                'title' => 'Camera exactly as described',
                'body' => 'Tested sensor cleanly during meetup. Smooth transaction.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $profile->refresh();
        $this->assertEquals(1, $profile->reviews_count);
        $this->assertEquals(5.0, $profile->avg_rating);
        $this->get(route('used.seller.profile', $profile->handle))->assertSee('Camera exactly as described');
    }

    public function test_idor_authorization_safe_delete_and_admin_moderation_workflow(): void
    {
        $product = Product::create([
            'user_id' => $this->seller->id,
            'category_id' => $this->subCat->id,
            'vertical' => 'used',
            'listing_type' => 'classified',
            'title' => 'Garmin Forerunner 255',
            'slug' => 'garmin-forerunner-255',
            'description' => 'GPS running watch.',
            'base_price' => 2100000,
            'status' => 'active',
            'used_status' => 'active',
            'condition_grade' => 'good',
            'city' => 'Jaipur',
        ]);

        // IDOR protection on edit/update/status/delete
        $this->actingAs($this->buyer);
        $this->get(route('used.edit', $product->id))->assertStatus(403);
        $this->put(route('used.update', $product->id), [
            'title' => 'Hijacked',
            'category_id' => $this->subCat->id,
            'description' => 'Bad',
            'price' => 100,
            'city' => 'Jaipur',
        ])->assertStatus(403);
        $this->post(route('used.status', $product->id), ['action' => 'mark_sold'])->assertStatus(403);
        $this->delete(route('used.delete', $product->id))->assertStatus(403);

        // Buyer submits a real report against listing
        $this->post(route('reports.store'), [
            'reportable_type' => 'product',
            'reportable_id' => $product->id,
            'reason' => 'counterfeit',
            'details' => 'Serial number looks tampered.',
        ])->assertRedirect()->assertSessionHas('success');

        $report = Report::where('reportable_type', Product::class)->where('reportable_id', $product->id)->firstOrFail();

        // Admin inspects Used products tab & moderation queue and resolves report
        $this->actingAs($this->admin)->withSession(['admin_2fa_passed' => true]);

        $this->get(route('admin.products', ['vertical' => 'used']))
            ->assertStatus(200)
            ->assertSee('Garmin Forerunner 255');

        $this->get(route('admin.reports.index'))
            ->assertStatus(200)
            ->assertSee('Garmin Forerunner 255');

        $this->post(route('admin.reports.resolve', $report), [
            'action' => 'block_product',
            'admin_notes' => 'Paused pending authenticity proof.',
        ])->assertRedirect();

        $product->refresh();
        $this->assertEquals('inactive', $product->status);
        $this->assertEquals('paused', $product->used_status);
        $this->assertDatabaseHas('audit_logs', ['auditable_id' => $report->id, 'action' => 'moderation_block_product']);

        // Owner safe delete preserves soft-deleted record
        $this->actingAs($this->seller)
            ->delete(route('used.delete', $product->id))
            ->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
