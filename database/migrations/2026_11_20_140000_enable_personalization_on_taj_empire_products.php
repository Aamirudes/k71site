<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;

return new class extends Migration
{
    public function up(): void
    {
        $taj = Vendor::where('slug', 'taj-empire')
            ->orWhere('shop_name', 'Taj Empire')
            ->first();

        if (!$taj) {
            return;
        }

        $taj->update([
            'vendor_type' => 'gift_store',
            'operating_sectors' => ['products'],
        ]);

        $defaultGiftConfig = [
            'is_customizable' => true,
            'allow_custom_text' => true,
            'text_prompt' => 'Custom Name, Monogram or Engraving Message',
            'text_char_limit' => 45,
            'text_price_paise' => 0, // Complimentary laser engraving
            'is_text_required' => false,
            'allow_font_selection' => true,
            'available_fonts' => ['Classic Serif', 'Modern Script', 'Bold Minimal', 'Vintage Engraving'],
            'allow_color_selection' => true,
            'available_colors' => ['Gold Foil', 'Silver Shimmer', 'Midnight Black', 'Rose Gold', 'Emerald Green'],
            'allow_photo_upload' => true,
            'photo_prompt' => 'Upload High-Resolution Photo or Custom Crest',
            'photo_price_paise' => 0,
            'allow_gift_wrap' => true,
            'gift_wrap_price_paise' => 4900, // ₹49
            'allow_greeting_card' => true,
            'greeting_card_price_paise' => 2900, // ₹29
            'production_days' => 2,
        ];

        // 1. Enable personalization on all existing products of Taj Empire
        $existingProducts = $taj->products()->get();
        foreach ($existingProducts as $product) {
            $product->update([
                'gift_customization_config' => $defaultGiftConfig,
                'vertical' => 'marketplace',
                'listing_type' => 'standard_retail',
            ]);
        }

        // 2. Ensure at least 4 showcase handcrafted gift products exist for Taj Empire
        $giftShowcase = [
            [
                'title' => 'Royal Makrana Marble Keepsake Box with Brass Inlay',
                'slug' => 'royal-makrana-marble-keepsake-box-brass-inlay',
                'base_price' => 249900, // ₹2,499
                'compare_at_price' => 399900,
                'description' => 'Handcrafted from pure white Makrana marble with polished floral brass inlays. Perfectly sized for fine jewelry, heirloom watches, and keepsake treasures. Includes custom laser monogram engraving on the marble lid.',
                'total_stock' => 50,
                'specifications' => [
                    ['key' => 'Material', 'value' => '100% Makrana White Marble & Solid Brass Inlay'],
                    ['key' => 'Dimensions', 'value' => '15cm x 10cm x 6cm (L x W x H)'],
                    ['key' => 'Interior', 'value' => 'Plush Velvet Lining (Burgundy Wine)'],
                    ['key' => 'Engraving Area', 'value' => 'Center Lid Monogram / Custom Date'],
                    ['key' => 'Packaging', 'value' => 'Gold-Embossed Royal Keepsake Gift Box'],
                ],
                'variants' => [
                    ['sku' => 'TAJ-MBX-GOLD', 'name' => 'Polished Brass Inlay', 'price' => 249900, 'stock' => 25],
                    ['sku' => 'TAJ-MBX-SILVER', 'name' => 'German Silver Inlay', 'price' => 269900, 'stock' => 25],
                ],
            ],
            [
                'title' => 'Handcrafted Teak Wood & Brass Personalized Desk Organizer',
                'slug' => 'handcrafted-teak-wood-brass-desk-organizer',
                'base_price' => 189900, // ₹1,899
                'compare_at_price' => 299900,
                'description' => 'Solid Burma teak wood organizer featuring a dedicated smartphone stand, fountain pen dock, business card holder, and brass coin tray. Personalized with deep precision laser engraving of your recipient’s name and title.',
                'total_stock' => 45,
                'specifications' => [
                    ['key' => 'Material', 'value' => 'Seasoned Solid Burma Teak Wood'],
                    ['key' => 'Finish', 'value' => 'Hand-Rubbed Natural Beeswax Matte Finish'],
                    ['key' => 'Dimensions', 'value' => '28cm x 14cm x 4cm'],
                    ['key' => 'Engraving Area', 'value' => 'Front Edge Nameplate & Dedicated Desk Message'],
                    ['key' => 'Crafting Time', 'value' => '2 Business Days Hand-Assembly'],
                ],
                'variants' => [
                    ['sku' => 'TAJ-TEAK-WALNUT', 'name' => 'Dark Walnut Stain', 'price' => 189900, 'stock' => 25],
                    ['sku' => 'TAJ-TEAK-NATURAL', 'name' => 'Natural Golden Teak', 'price' => 189900, 'stock' => 20],
                ],
            ],
            [
                'title' => 'Bespoke Monogram Full-Grain Leather Travel Wallet & Passport Folio',
                'slug' => 'bespoke-monogram-full-grain-leather-travel-wallet',
                'base_price' => 149900, // ₹1,499
                'compare_at_price' => 249900,
                'description' => 'Crafted from vegetable-tanned full-grain leather that patinas beautifully over time. Holds 2 passports, 8 cards, boarding passes, and currency. Hand-embossed with custom gold-foil initials.',
                'total_stock' => 60,
                'specifications' => [
                    ['key' => 'Leather Grade', 'value' => '100% Vegetable-Tanned Full Grain Buffalo Hide'],
                    ['key' => 'Hardware', 'value' => 'Antiqued Solid Brass YKK Zipper'],
                    ['key' => 'Foil Stamp', 'value' => '24K Gold Foil / Blind Deboss (Up to 4 Initials)'],
                    ['key' => 'Capacity', 'value' => '2 Passports, 8 Cards, SIM Tool Pocket, Cash Slot'],
                ],
                'variants' => [
                    ['sku' => 'TAJ-LTHR-TAN', 'name' => 'Saddle Tan', 'price' => 149900, 'stock' => 30],
                    ['sku' => 'TAJ-LTHR-NOIR', 'name' => 'Midnight Obsidian Black', 'price' => 149900, 'stock' => 30],
                ],
            ],
            [
                'title' => 'Artisan Ceramic Couple Coffee Mug Set with Gold Calligraphy',
                'slug' => 'artisan-ceramic-couple-coffee-mug-set-gold',
                'base_price' => 99900, // ₹999
                'compare_at_price' => 159900,
                'description' => 'Wheel-thrown stoneware ceramic couple mugs finished in speckled ivory matte glaze with 12K real gold rim and hand-lettered names. The quintessential wedding, anniversary, or housewarming gift.',
                'total_stock' => 80,
                'specifications' => [
                    ['key' => 'Material', 'value' => 'High-Fire Stoneware Ceramic & 12K Gold Luster'],
                    ['key' => 'Capacity', 'value' => '350ml per mug (Set of 2 Mugs)'],
                    ['key' => 'Personalization', 'value' => 'Two Names + Anniversary Date / Est. Year'],
                    ['key' => 'Packaging', 'value' => 'Rigid Magnetic Gift Box with Foam Cutout'],
                ],
                'variants' => [
                    ['sku' => 'TAJ-MUG-MRMRS', 'name' => 'His & Hers / Couple Set (Ivory & Charcoal)', 'price' => 99900, 'stock' => 40],
                    ['sku' => 'TAJ-MUG-IVORY', 'name' => 'Dual Matte Ivory Set', 'price' => 99900, 'stock' => 40],
                ],
            ],
        ];

        $category = Category::firstOrCreate(
            ['slug' => 'personalized-gifts'],
            [
                'name' => 'Personalized Gifts',
                'icon' => '🎁',
                'is_active' => true,
                'vertical_type' => 'marketplace',
            ]
        );

        foreach ($giftShowcase as $item) {
            $product = Product::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'vendor_id' => $taj->id,
                    'category_id' => $category->id,
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'base_price' => $item['base_price'],
                    'compare_at_price' => $item['compare_at_price'],
                    'description' => $item['description'],
                    'total_stock' => $item['total_stock'],
                    'specifications' => $item['specifications'],
                    'gift_customization_config' => $defaultGiftConfig,
                    'is_active' => true,
                    'vertical' => 'marketplace',
                    'listing_type' => 'standard_retail',
                ]
            );

            // Ensure config is applied even if product already existed
            $product->update([
                'vendor_id' => $taj->id,
                'gift_customization_config' => $defaultGiftConfig,
                'is_active' => true,
                'vertical' => 'marketplace',
                'listing_type' => 'standard_retail',
            ]);

            // Ensure variants
            foreach ($item['variants'] as $v) {
                ProductVariant::firstOrCreate(
                    ['product_id' => $product->id, 'sku' => $v['sku']],
                    [
                        'name' => $v['name'],
                        'price_override' => $v['price'],
                        'stock' => $v['stock'],
                        'stock_quantity' => $v['stock'],
                        'attributes_hash' => ['type' => 'Option', 'option' => $v['name']],
                    ]
                );
            }
        }
    }

    public function down(): void
    {
        // Non-destructive rollback
    }
};
