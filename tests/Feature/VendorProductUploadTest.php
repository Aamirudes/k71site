<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VendorProductUploadTest extends TestCase
{
    use RefreshDatabase;

    private function createVendor(string $vendorType = 'clothing', bool $attachRole = true): array
    {
        $user = User::factory()->create([
            'email' => uniqid('vendor_', true) . '@k71.test',
        ]);

        if ($attachRole) {
            $role = Role::firstOrCreate(['name' => 'vendor'], ['display_name' => 'Vendor']);
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        $vendor = Vendor::create([
            'user_id' => $user->id,
            'shop_name' => 'Test Store ' . ucfirst($vendorType),
            'slug' => 'test-store-' . uniqid(),
            'vendor_type' => $vendorType,
            'is_verified' => true,
            'status' => 'approved',
        ]);
        $this->grantVendorTestSubscription($vendor, 'all');

        return [$user, $vendor];
    }

    private function createCategory(string $verticalType = 'fashion_matrix', string $name = 'Test Category'): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => 'cat-' . uniqid(),
            'vertical_type' => $verticalType,
            'is_active' => true,
        ]);
    }

    public function test_vendor_without_vendor_role_row_can_access_create_and_store_product(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->createVendor('marble', false);
        $category = $this->createCategory('b2b_machinery', 'Italian Marble Slabs');

        $this->assertFalse($user->hasRole('vendor'));
        $this->assertTrue($user->isVendor());

        $this->actingAs($user)
            ->get(route('vendor.products.create'))
            ->assertOk()
            ->assertSee('Launch Product to Store');

        $resp = $this->actingAs($user)->post(route('vendor.products.store'), [
            'title' => 'Makrana White Slab',
            'category_id' => $category->id,
            'vertical' => 'enquiry',
            'listing_type' => 'enquiry',
            'description' => 'Natural spotless marble slabs.',
            'base_price' => '0',
            'gst_rate' => '18',
            'total_stock' => '500',
            'status' => 'active',
            'enable_rfq' => '1',
            'images' => [
                UploadedFile::fake()->image('slab.jpg', 800, 800),
            ],
        ]);

        $resp->assertRedirect(route('vendor.products.index'));

        $product = Product::firstOrFail();
        $this->assertSame($vendor->id, $product->vendor_id);
        $this->assertSame('enquiry', $product->vertical);
        $this->assertSame('enquiry', $product->listing_type);
        $this->assertSame(0, $product->base_price);
        $this->assertCount(1, $product->images);
    }

    public function test_multi_image_upload_cover_ordering_and_large_image_above_5mb_succeeds(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->createVendor('clothing');
        $category = $this->createCategory('fashion_matrix', 'Banarasi Silk');

        $largeImg = UploadedFile::fake()->image('camera-raw.jpg', 1200, 1200)->size(6500);
        $coverImg = UploadedFile::fake()->image('cover-shot.png', 1000, 1000);

        $resp = $this->actingAs($user)->post(route('vendor.products.store'), [
            'title' => 'Handloom Katan Saree',
            'category_id' => $category->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'description' => "Multi-line description\nWith 'apostrophes' & heritage weave details.",
            'base_price' => '1234.55',
            'compare_at_price' => '1999.99',
            'gst_rate' => '5',
            'total_stock' => '15',
            'status' => 'active',
            'tags' => 'silk, festive, wedding',
            'is_returnable' => '1',
            'cover_image_index' => 1,
            'images' => [$largeImg, $coverImg],
        ]);

        $resp->assertRedirect(route('vendor.products.index'));

        $product = Product::with('images')->firstOrFail();
        $this->assertSame(123455, $product->base_price);
        $this->assertSame(199999, $product->compare_at_price);
        $this->assertTrue($product->is_returnable);
        $this->assertSame(['silk', 'festive', 'wedding'], $product->tags);

        $sorted = $product->images->sortBy('sort_order')->values();
        $this->assertCount(2, $sorted);
        $this->assertSame(0, $sorted[0]->sort_order);
        $this->assertSame(1, $sorted[1]->sort_order);
        $this->assertStringEndsWith('.png', $sorted[0]->path);
        $this->assertStringEndsWith('.jpg', $sorted[1]->path);
    }

    public function test_multiline_and_apostrophe_validation_bounce_renders_create_and_edit_cleanly(): void
    {
        [$user, $vendor] = $this->createVendor('clothing');
        $category = $this->createCategory('fashion_matrix', 'Kurtas');

        // Bounce on create with multiline + apostrophes + empty base_price
        $bounce = $this->actingAs($user)
            ->from(route('vendor.products.create'))
            ->post(route('vendor.products.store'), [
                'title' => "Men's Linen Kurta",
                'category_id' => $category->id,
                'description' => "Line one\nLine two with 'quotes'",
                'base_price' => '',
                'gst_rate' => '5',
                'total_stock' => '10',
                'status' => 'active',
                'specifications' => [
                    ['name' => 'Fabric', 'value' => "100% 'Pure' Linen"],
                ],
            ]);

        $bounce->assertRedirect(route('vendor.products.create'));
        $bounce->assertSessionHasErrors('base_price');

        $this->actingAs($user)
            ->get(route('vendor.products.create'))
            ->assertOk()
            ->assertSee('The base price field is required.');

        // Also test edit page with array tags and old() validation bounce
        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => "Women's Zari Shawl",
            'slug' => 'womens-zari-shawl',
            'description' => "Soft shawl\nSecond line",
            'base_price' => 250000,
            'gst_rate' => 5,
            'total_stock' => 12,
            'status' => 'active',
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'tags' => ['shawl', 'winter', "women's"],
        ]);

        $this->actingAs($user)
            ->get(route('vendor.products.edit', $product))
            ->assertOk()
            ->assertSee("shawl, winter, women&#039;s", false);

        $editBounce = $this->actingAs($user)
            ->from(route('vendor.products.edit', $product))
            ->put(route('vendor.products.update', $product), [
                'title' => "Updated's Shawl",
                'category_id' => $category->id,
                'description' => "Line A\nLine B",
                'base_price' => '',
                'gst_rate' => '5',
                'total_stock' => '12',
                'status' => 'active',
                'tags' => 'updated, tags',
            ]);

        $editBounce->assertRedirect(route('vendor.products.edit', $product));
        $this->actingAs($user)
            ->get(route('vendor.products.edit', $product))
            ->assertOk()
            ->assertSee('The base price field is required.');
    }

    public function test_retail_clothing_edit_update_preserves_purchase_type_status_stock_gst_and_images(): void
    {
        Storage::fake('public');
        [$user, $vendor] = $this->createVendor('clothing');
        $category = $this->createCategory('fashion_matrix', 'Designer Kurtis');

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $category->id,
            'title' => 'Original Kurti',
            'slug' => 'original-kurti',
            'description' => 'Cotton blend kurti.',
            'base_price' => 99900,
            'gst_rate' => 5,
            'total_stock' => 10,
            'status' => 'active',
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'is_returnable' => true,
            'tags' => ['cotton'],
        ]);

        $oldImg = $product->images()->create([
            'path' => 'products/old-img.jpg',
            'alt_text' => 'Old',
            'sort_order' => 0,
        ]);
        Storage::disk('public')->put('products/old-img.jpg', 'oldbytes');

        $newFile = UploadedFile::fake()->image('fresh-look.webp', 800, 800);

        $resp = $this->actingAs($user)->put(route('vendor.products.update', $product), [
            'title' => 'Updated Festive Kurti',
            'category_id' => $category->id,
            'vertical' => 'marketplace',
            'listing_type' => 'purchase',
            'description' => 'Updated festive wear description.',
            'base_price' => '1499.00',
            'gst_rate' => '28',
            'total_stock' => '77',
            'status' => 'draft',
            'tags' => 'silk, festive, wedding',
            'deleted_image_ids' => [$oldImg->id],
            'images' => [$newFile],
            'overall_image_order' => ['new:0'],
        ]);

        $resp->assertRedirect(route('vendor.products.index'));

        $fresh = $product->fresh(['images', 'modifiers', 'gigPackages']);
        $this->assertSame('Updated Festive Kurti', $fresh->title);
        $this->assertSame('purchase', $fresh->listing_type);
        $this->assertSame('draft', $fresh->status);
        $this->assertSame(77, $fresh->total_stock);
        $this->assertEquals(28, (int) $fresh->gst_rate);
        $this->assertFalse($fresh->is_returnable);
        $this->assertSame(['silk', 'festive', 'wedding'], $fresh->tags);
        $this->assertCount(0, $fresh->modifiers);
        $this->assertCount(0, $fresh->gigPackages);
        $this->assertCount(1, $fresh->images);
        $this->assertStringEndsWith('.webp', $fresh->images->first()->path);
    }

    public function test_feasto_real_estate_high_price_long_sku_and_empty_variant_rows_succeed(): void
    {
        [$feastoUser, $feastoVendor] = $this->createVendor('feasto');
        $groceryCat = $this->createCategory('standard_retail', 'Daily Staples');

        $feastoProduct = Product::create([
            'vendor_id' => $feastoVendor->id,
            'category_id' => $groceryCat->id,
            'title' => 'Organic Toor Dal',
            'slug' => 'organic-toor-dal',
            'description' => 'Unpolished 1kg pack.',
            'base_price' => 18500,
            'gst_rate' => 5,
            'total_stock' => 40,
            'status' => 'active',
            'vertical' => 'feasto',
            'listing_type' => 'purchase',
        ]);

        $this->actingAs($feastoUser)
            ->put(route('vendor.products.update', $feastoProduct), [
                'title' => 'Organic Toor Dal 1kg',
                'category_id' => $groceryCat->id,
                'vertical' => 'feasto',
                'listing_type' => 'purchase',
                'description' => 'Updated unpolished pack.',
                'base_price' => '195.00',
                'gst_rate' => '5',
                'total_stock' => '55',
                'status' => 'active',
            ])
            ->assertRedirect(route('vendor.products.index'));

        $this->assertSame('feasto', $feastoProduct->fresh()->vertical);
        $this->assertSame(19500, $feastoProduct->fresh()->base_price);

        // Real Estate > 2.14 Cr price + 100-char SKU + blank variant/portion rows
        [$reUser, $reVendor] = $this->createVendor('real_estate');
        $reCat = $this->createCategory('b2b_machinery', 'Luxury Villas');
        $longSku = str_repeat('VILLA2026-', 10); // 100 chars

        $this->actingAs($reUser)
            ->post(route('vendor.products.store'), [
                'title' => 'Palm Meadows Independent Villa',
                'category_id' => $reCat->id,
                'vertical' => 'enquiry',
                'listing_type' => 'enquiry',
                'description' => 'Corner freehold luxury villa.',
                'base_price' => '23500000',
                'gst_rate' => '5',
                'total_stock' => '3',
                'sku' => $longSku,
                'status' => 'active',
                'variants' => [
                    ['name' => '', 'sku' => '', 'color' => '', 'size' => '', 'price' => '', 'stock' => 0],
                ],
                'food_portions' => [
                    ['name' => '', 'price' => '', 'stock' => 0],
                ],
                'specifications' => [
                    ['name' => '', 'value' => ''],
                    ['name' => 'Carpet Area', 'value' => '4,200 Sq.Ft'],
                ],
            ])
            ->assertRedirect(route('vendor.products.index'));

        $villa = Product::where('title', 'Palm Meadows Independent Villa')->with('variants')->firstOrFail();
        $this->assertSame(2350000000, $villa->base_price);
        $this->assertSame($longSku, $villa->sku);
        $this->assertSame(3, $villa->total_stock);
        $this->assertCount(1, $villa->specifications_json);
        $this->assertSame('Default', $villa->variants->first()->sku);
        $this->assertSame(2350000000, $villa->variants->first()->price_override);
    }

    public function test_food_vendor_edit_sets_is_returnable_false_and_deletes_all_modifiers(): void
    {
        [$user, $vendor] = $this->createVendor('food');
        $foodCat = $this->createCategory('food_portion', 'Main Course');

        $dish = Product::create([
            'vendor_id' => $vendor->id,
            'category_id' => $foodCat->id,
            'title' => 'Paneer Butter Masala',
            'slug' => 'paneer-butter-masala',
            'description' => 'Rich tomato gravy.',
            'base_price' => 28000,
            'gst_rate' => 5,
            'total_stock' => 50,
            'status' => 'active',
            'vertical' => 'food',
            'listing_type' => 'purchase',
            'is_returnable' => true,
        ]);

        $dish->modifiers()->create([
            'group_name' => 'Add-ons',
            'modifier_name' => 'Extra Butter',
            'price_delta' => 25,
            'is_required' => false,
        ]);

        $this->actingAs($user)
            ->put(route('vendor.products.update', $dish), [
                'title' => 'Paneer Butter Masala',
                'category_id' => $foodCat->id,
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'description' => 'Freshly prepared gravy.',
                'base_price' => '290',
                'gst_rate' => '5',
                'total_stock' => '50',
                'status' => 'active',
                'is_returnable' => '0',
                'modifiers_submitted' => '1',
            ])
            ->assertRedirect(route('vendor.products.index'));

        $fresh = $dish->fresh('modifiers');
        $this->assertFalse($fresh->is_returnable);
        $this->assertCount(0, $fresh->modifiers);
    }

    public function test_k71_sync_public_storage_never_zeroes_existing_files(): void
    {
        $rel = 'products/test-sync-' . uniqid() . '.txt';
        $source = storage_path('app/public/' . $rel);
        $target = public_path('storage/' . $rel);

        try {
            File::ensureDirectoryExists(dirname($source));
            file_put_contents($source, 'non-empty-payload');

            k71_sync_public_storage($rel);

            $this->assertFileExists($source);
            $this->assertSame('non-empty-payload', file_get_contents($source));
            $this->assertFileExists($target);
            $this->assertSame('non-empty-payload', file_get_contents($target));
        } finally {
            @unlink($source);
            if (!is_link(public_path('storage'))) {
                @unlink($target);
            }
        }
    }
}
