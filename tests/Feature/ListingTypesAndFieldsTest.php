<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CategoryField;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingTypesAndFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendorUser;
    protected Vendor $vendor;
    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Fashion & Apparel',
            'slug' => 'fashion-apparel',
            'vertical' => 'marketplace',
            'is_active' => true,
        ]);

        $this->vendorUser = User::factory()->create();
        $vendorRole = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
        $this->vendorUser->roles()->syncWithoutDetaching([$vendorRole->id]);

        $this->vendor = Vendor::create([
            'user_id' => $this->vendorUser->id,
            'shop_name' => 'Royal Heritage Boutique',
            'slug' => 'royal-heritage',
            'phone' => '9876543210',
            'email' => 'vendor@royalheritage.test',
            'address' => '123 Market Street',
            'city' => 'Jaipur',
            'state' => 'Rajasthan',
            'pincode' => '302001',
            'status' => 'approved',
        ]);

        VendorSetting::create([
            'vendor_id' => $this->vendor->id,
            'upi_enabled' => true,
            'cod_enabled' => true,
            'shipping_type' => 'flat',
            'shipping_flat_rate' => 6000,
        ]);
    }

    public function test_product_model_has_vertical_listing_type_and_custom_fields_attributes(): void
    {
        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'title' => 'Paneer Butter Masala',
            'slug' => 'paneer-butter-masala',
            'description' => 'Rich creamy North Indian paneer gravy.',
            'base_price' => 35000,
            'gst_rate' => 5,
            'total_stock' => 50,
            'status' => 'active',
            'custom_fields' => [
                'dietary_type' => 'Veg (Vegetarian)',
                'spice_level' => 'Medium Spicy',
                'prep_time_minutes' => 20,
            ],
        ]);

        $this->assertEquals('food', $product->vertical);
        $this->assertEquals('purchase', $product->listing_type);
        $this->assertTrue($product->isPurchase());
        $this->assertFalse($product->isEnquiry());
        $this->assertEquals('Veg (Vegetarian)', $product->getCustomField('dietary_type'));
        $this->assertEquals(20, $product->getCustomField('prep_time_minutes'));

        // Test scope queries
        $foodProducts = Product::forVertical('food')->get();
        $this->assertCount(1, $foodProducts);

        $purchaseProducts = Product::listingType('purchase')->get();
        $this->assertCount(1, $purchaseProducts);
    }

    public function test_cart_add_blocks_non_purchase_listing_types_server_side(): void
    {
        $enquiryProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'enquiry',
            'listing_type' => 'enquiry',
            'title' => 'Wholesale Silk Fabric Rolls',
            'slug' => 'wholesale-silk-fabric-rolls',
            'description' => 'Bulk sourcing min order 100 meters.',
            'base_price' => 50000,
            'gst_rate' => 5,
            'total_stock' => 1000,
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $enquiryProduct->id,
            'sku' => 'SILK-100M',
            'stock' => 100,
            'is_active' => true,
        ]);

        // Attempt to add enquiry listing to cart
        $response = $this->postJson('/cart/add', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsString('cannot be added to the shopping cart', $response->json('message'));

        // Test Contact Seller listing
        $usedProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'title' => 'Pre-owned iPhone 13 Pro',
            'slug' => 'pre-owned-iphone-13-pro',
            'description' => 'Mint condition with original bill and box.',
            'base_price' => 4500000,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
        ]);

        $usedVariant = ProductVariant::create([
            'product_id' => $usedProduct->id,
            'sku' => 'IPHONE13-USED',
            'stock' => 1,
            'is_active' => true,
        ]);

        $responseUsed = $this->postJson('/cart/add', [
            'variant_id' => $usedVariant->id,
            'quantity' => 1,
        ]);

        $responseUsed->assertStatus(422);
        $this->assertStringContainsString('cannot be added to the shopping cart', $responseUsed->json('message'));
    }

    public function test_cart_add_allows_purchase_listing_type(): void
    {
        $purchaseProduct = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'title' => 'Chanderi Handloom Kurta',
            'slug' => 'chanderi-handloom-kurta',
            'description' => 'Handcrafted pure cotton chanderi.',
            'base_price' => 249900,
            'gst_rate' => 5,
            'total_stock' => 25,
            'status' => 'active',
        ]);

        $variant = ProductVariant::create([
            'product_id' => $purchaseProduct->id,
            'sku' => 'CHAND-M',
            'stock' => 10,
            'is_active' => true,
        ]);

        $response = $this->postJson('/cart/add', [
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Added to cart.',
        ]);
    }

    public function test_category_fields_seeded_and_queryable_for_verticals(): void
    {
        $foodFields = CategoryField::getFieldsFor('food');
        $this->assertNotEmpty($foodFields);
        $this->assertTrue($foodFields->contains('name', 'dietary_type'));
        $this->assertTrue($foodFields->contains('name', 'spice_level'));

        $usedFields = CategoryField::getFieldsFor('used');
        $this->assertNotEmpty($usedFields);
        $this->assertTrue($usedFields->contains('name', 'condition'));
        $this->assertTrue($usedFields->contains('name', 'warranty_status'));

        $serviceFields = CategoryField::getFieldsFor('services');
        $this->assertNotEmpty($serviceFields);
        $this->assertTrue($serviceFields->contains('name', 'service_mode'));

        $enquiryFields = CategoryField::getFieldsFor('enquiry');
        $this->assertNotEmpty($enquiryFields);
        $this->assertTrue($enquiryFields->contains('name', 'minimum_order_quantity'));
    }

    public function test_vendor_can_create_product_with_vertical_and_custom_fields(): void
    {
        $this->actingAs($this->vendorUser);

        $payload = [
            'title' => 'Special Dum Biryani Handi',
            'category_id' => $this->category->id,
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'base_price' => 420.00,
            'gst_rate' => 5,
            'total_stock' => 30,
            'status' => 'active',
            'description' => 'Authentic slow-cooked dum biryani with saffron.',
            'custom_fields' => [
                'dietary_type' => 'Non-Veg',
                'cuisine' => 'Hyderabadi',
                'prep_time_minutes' => 35,
                'spice_level' => 'Spicy',
            ],
            'variants' => [
                ['name' => 'Regular Handi', 'stock' => 20, 'price' => 420.00, 'sku' => 'BIRYANI-REG'],
                ['name' => 'Jumbo Family Handi', 'stock' => 10, 'price' => 899.00, 'sku' => 'BIRYANI-JUMBO'],
            ],
        ];

        $response = $this->post(route('vendor.products.store'), $payload);
        $response->assertRedirect(route('vendor.products.index'));

        $created = Product::withoutGlobalScopes()->where('slug', 'special-dum-biryani-handi')->first();
        $this->assertNotNull($created);
        $this->assertEquals('food', $created->vertical);
        $this->assertEquals('purchase', $created->listing_type);
        $this->assertEquals('Non-Veg', $created->getCustomField('dietary_type'));
        $this->assertEquals('Hyderabadi', $created->getCustomField('cuisine'));
        $this->assertEquals(35, $created->getCustomField('prep_time_minutes'));
    }

    public function test_vendor_can_update_product_vertical_and_custom_fields(): void
    {
        $this->actingAs($this->vendorUser);

        $product = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'title' => 'Designer Leather Bag',
            'slug' => 'designer-leather-bag',
            'description' => 'Original crafted leather.',
            'base_price' => 199900,
            'gst_rate' => 18,
            'total_stock' => 5,
            'status' => 'active',
        ]);

        $updatePayload = [
            'title' => 'Pre-Owned Designer Leather Bag',
            'category_id' => $this->category->id,
            'vertical' => 'used',
            'listing_type' => 'contact_seller',
            'base_price' => 1200.00,
            'gst_rate' => 18,
            'total_stock' => 1,
            'status' => 'active',
            'description' => 'Rare gently used vintage piece.',
            'custom_fields' => [
                'condition' => 'Gently Used (Minor Signs of Wear)',
                'age_months' => 14,
                'warranty_status' => 'Out of Warranty',
            ],
        ];

        $response = $this->put(route('vendor.products.update', $product), $updatePayload);
        $response->assertRedirect(route('vendor.products.index'));

        $product->refresh();
        $this->assertEquals('used', $product->vertical);
        $this->assertEquals('contact_seller', $product->listing_type);
        $this->assertEquals('Gently Used (Minor Signs of Wear)', $product->getCustomField('condition'));
        $this->assertEquals(14, $product->getCustomField('age_months'));
    }

    public function test_category_fields_api_endpoint_returns_json(): void
    {
        $this->actingAs($this->vendorUser);

        $response = $this->getJson(route('vendor.products.category_fields', ['vertical' => 'services']));
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonFragment(['name' => 'service_mode']);
    }

    public function test_product_detail_page_renders_listing_type_specific_elements(): void
    {
        // 1. Purchase listing renders Add to Bag
        $purchaseProd = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'title' => 'Festive Silk Saree',
            'slug' => 'festive-silk-saree',
            'description' => 'Gorgeous handloom silk saree with zari border.',
            'base_price' => 450000,
            'gst_rate' => 5,
            'total_stock' => 10,
            'status' => 'active',
        ]);

        ProductVariant::create([
            'product_id' => $purchaseProd->id,
            'sku' => 'SILK-FREE',
            'stock' => 10,
            'is_active' => true,
        ]);

        $res1 = $this->get('/product/' . $purchaseProd->slug);
        $res1->assertStatus(200);
        $res1->assertSee('Add to Bag');

        // 2. Enquiry listing renders Request Custom Quotation
        $enquiryProd = Product::create([
            'vendor_id' => $this->vendor->id,
            'category_id' => $this->category->id,
            'vertical' => 'enquiry',
            'listing_type' => 'enquiry',
            'title' => 'Italian White Marble Slabs',
            'slug' => 'italian-white-marble-slabs',
            'description' => 'Imported Italian marble for luxury flooring.',
            'base_price' => 1500000,
            'gst_rate' => 18,
            'total_stock' => 100,
            'status' => 'active',
            'custom_fields' => [
                'minimum_order_quantity' => 500,
                'lead_time_days' => 15,
            ],
        ]);

        ProductVariant::create([
            'product_id' => $enquiryProd->id,
            'sku' => 'MARBLE-SQFT',
            'stock' => 100,
            'is_active' => true,
        ]);

        $res2 = $this->get('/product/' . $enquiryProd->slug);
        $res2->assertStatus(200);
        $res2->assertSee('Request Custom Quotation / Bulk Enquiry');
        $res2->assertSee('Minimum Order Quantity');
        $res2->assertSee('500');
    }
}
