<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Role;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsedMarketplaceOlxSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Elite Seller User & Vendor (Yusuf Khan in Kishangarh, Rajasthan)
        $sellerUser = User::firstOrCreate(
            ['email' => 'yusufkhan@k71.in'],
            [
                'first_name' => 'Yusuf',
                'last_name' => 'Khan',
                'username' => 'yusufkhan',
                'phone' => '+919549311580',
                'password' => Hash::make('Hashir1284@0'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'terms_accepted_at' => now(),
            ]
        );

        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);
        $custRole = Role::firstOrCreate(['name' => 'customer']);
        $sellerUser->roles()->syncWithoutDetaching([$vendorRole->id, $custRole->id]);

        $vendor = Vendor::updateOrCreate(
            ['slug' => 'yusuf-khan-elite-deals'],
            [
                'user_id' => $sellerUser->id,
                'shop_name' => 'Yusuf khan',
                'vendor_type' => 'used',
                'contact_email' => 'yusufkhan@k71.in',
                'contact_phone' => '+91 95493 11580',
                'phone' => '+91 95493 11580',
                'city' => 'Kishangarh',
                'state' => 'Rajasthan',
                'pincode' => '305801',
                'address' => 'Shivaji Nagar, Kishangarh Ajmer District',
                'description' => 'Elite Verified Pre-Owned Electronics, Flagship Smartphones, Premium Furnishings, and Automotive Specialists.',
                'status' => 'approved',
                'approved_at' => now()->subMonths(11),
                'colour_palette' => 'cobalt',
                'social_links' => [
                    'whatsapp' => '+919549311580',
                    'member_since' => 'Nov 2025',
                    'items_listed' => '8748',
                    'badge' => 'ELITE SELLER',
                ],
            ]
        );

        // 2. Fetch or create relevant categories
        $mobileCat = Category::where('slug', 'used-mobiles-tablets')->orWhere('slug', 'used-used-mobiles-tablets')->first()
            ?: Category::firstOrCreate(['slug' => 'used-mobiles'], ['name' => 'Mobiles', 'vertical' => 'used', 'is_active' => true]);

        $furnitureCat = Category::where('slug', 'used-furniture')->orWhere('slug', 'pre-loved-used-goods-thrift-used-furniture-decor')->first()
            ?: Category::firstOrCreate(['slug' => 'used-furniture'], ['name' => 'Furniture', 'vertical' => 'used', 'is_active' => true]);

        $electronicsCat = Category::where('slug', 'used-laptops-electronics')->orWhere('slug', 'pre-loved-used-goods-thrift-second-hand-electronics')->first()
            ?: Category::firstOrCreate(['slug' => 'used-electronics'], ['name' => 'Electronics', 'vertical' => 'used', 'is_active' => true]);

        $vehicleCat = Category::where('slug', 'used-vehicles-bikes')->orWhere('slug', 'pre-loved-used-goods-thrift-used-vehicles')->first()
            ?: Category::firstOrCreate(['slug' => 'used-vehicles'], ['name' => 'Commercial Vehicles', 'vertical' => 'used', 'is_active' => true]);

        // 3. Define the exact items from the user's OLX screenshots
        $items = [
            [
                'slug' => 'iphone-14-promax-256gb-battery-98-with-bill-ios',
                'title' => 'Iphone 14promax storage battery health 98% WITH Bill IOS 26.5.2 Late',
                'category_id' => $mobileCat->id,
                'price' => 2200000, // ₹ 22,000
                'compare_price' => 2800000,
                'brand' => 'Apple',
                'sku' => 'IPHONE14PM-22K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'ELITE',
                'deal_label' => 'Fair Deal',
                'location' => 'Shivaji Nagar, Kishangarh Ajmer District',
                'ad_id' => '1857261533',
                'views_past_hour' => 6,
                'specs' => [
                    'Type' => 'Mobile Phones',
                    'Brand' => 'Apple',
                    'Model' => 'iPhone 14 Pro Max',
                    'Storage' => '256 GB',
                    'Battery Health' => '98%',
                    'Condition' => 'Like New / Flawless',
                    'Bill & Box' => 'Available with Original Cable',
                    'Warranty' => '3 Months Seller Warranty',
                ],
                'description' => 'Original iPhone 14 Pro Max 256GB Deep Purple. Battery health 98%, completely scratchless condition. Comes with original Apple invoice, box, and braided Type-C cable. Face ID and True Tone fully working. Immediate deal in Kishangarh. Genuine buyers can call or chat directly.',
                'images' => [
                    'https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'premium-wooden-sofa-set-3-plus-1-plus-1-with-center-table',
                'title' => 'PREMIUM WOODEN SOFA SET (3+1+1) WITH CENTER TABLE',
                'category_id' => $furnitureCat->id,
                'price' => 1700000, // ₹ 17,000
                'compare_price' => 2400000,
                'brand' => 'Handcrafted Sheesham',
                'sku' => 'SOFA-WOOD-17K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'FEATURED',
                'deal_label' => 'Top Value',
                'location' => 'Kishangarh, Rajasthan',
                'ad_id' => '1857102931',
                'views_past_hour' => 12,
                'specs' => [
                    'Type' => 'Living Room Furniture',
                    'Material' => 'Solid Sheesham Hardwood',
                    'Seating Capacity' => '5 Seater (3+1+1)',
                    'Table' => 'Glass Top Center Table Included',
                    'Condition' => 'Excellent Condition',
                ],
                'description' => 'Solid Pure Sheesham wood carving sofa set with heavy cushions and matching glass-top center table. No termites, no scratches, extremely sturdy construction. Selling due to house renovation in Kishangarh.',
                'images' => [
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'old-heritage-takta-sheesham-diwan-bed',
                'title' => 'OLD HERITAGE TAKTA / SOLID TEAKWOOD DIWAN',
                'category_id' => $furnitureCat->id,
                'price' => 800000, // ₹ 8,000
                'compare_price' => 1200000,
                'brand' => 'Vintage Rajasthan',
                'sku' => 'TAKTA-HERITAGE-8K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'FEATURED',
                'deal_label' => 'Fair Deal',
                'location' => 'Kishangarh, Rajasthan',
                'ad_id' => '1857182740',
                'views_past_hour' => 4,
                'specs' => [
                    'Type' => 'Antique Bed / Diwan',
                    'Wood' => 'Aged Sagwan (Teak)',
                    'Size' => '6x3 Feet Single Diwan',
                    'Condition' => 'Sturdy Antique Condition',
                ],
                'description' => 'Authentic vintage heavy heritage Takta diwan bed with traditional turned legs and natural oil polish. Heavy timber, lasts for generations. Available for immediate pickup in Kishangarh.',
                'images' => [
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'apple-iphone-navratri-limited-edition-white-box-pack',
                'title' => 'APPLE IPHONE NAVRATRI LIMITED EDITION (WHITE PACK)',
                'category_id' => $mobileCat->id,
                'price' => 2800000, // ₹ 28,000
                'compare_price' => 3500000,
                'brand' => 'Apple',
                'sku' => 'IPHONE-WHITE-28K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'ELITE',
                'deal_label' => 'Fair Deal',
                'location' => 'Kishangarh, Rajasthan',
                'ad_id' => '1857219904',
                'views_past_hour' => 9,
                'specs' => [
                    'Type' => 'Smartphones',
                    'Brand' => 'Apple',
                    'Storage' => '128 GB',
                    'Battery Health' => '95%',
                    'Condition' => 'Mint Box Pack',
                ],
                'description' => 'Special White Edition Apple iPhone in mint condition. Complete packaging, unused charging cable, and glass screen guard applied since day one.',
                'images' => [
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'hp-laptop-15s-intel-core-i5-12th-gen-16gb-ssd',
                'title' => 'HP Laptop 15s (Intel Core i5 12th Gen, 16GB RAM, 512GB SSD)',
                'category_id' => $electronicsCat->id,
                'price' => 3900000, // ₹ 39,000
                'compare_price' => 5400000,
                'brand' => 'HP',
                'sku' => 'HPLAP-15S-39K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'FEATURED',
                'deal_label' => 'Fair Deal',
                'location' => 'Topdara, Ajmer',
                'ad_id' => '1857394012',
                'views_past_hour' => 7,
                'specs' => [
                    'Type' => 'Laptop',
                    'Brand' => 'HP',
                    'Processor' => 'Intel Core i5-1235U 12th Gen',
                    'RAM' => '16 GB DDR4',
                    'Storage' => '512 GB PCIe NVMe M.2 SSD',
                    'Screen' => '15.6 inch Full HD IPS Anti-glare',
                    'Battery Backup' => '5+ Hours',
                    'OS' => 'Windows 11 Home Genuine + MS Office',
                ],
                'description' => 'HP Laptop 15s in pristine silver finish. Ultra-fast performance with 16GB RAM and Core i5 12th generation processor. Clean keyboard, flawless display, original 65W fast charger included. Ideal for coding, office work, and video editing. Can be tested thoroughly in Topdara, Ajmer.',
                'images' => [
                    'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'ford-figo-diesel-titanium-2018-first-owner',
                'title' => 'FORD FIGO DIESEL TITANIUM 2018 (FIRST OWNER, 62000 KM)',
                'category_id' => $vehicleCat->id,
                'price' => 24500000, // ₹ 2,45,000
                'compare_price' => 29000000,
                'brand' => 'Ford',
                'sku' => 'FORD-FIGO-245K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'ELITE',
                'deal_label' => 'Hot Deal',
                'location' => 'Kishangarh, Rajasthan',
                'ad_id' => '1857448102',
                'views_past_hour' => 15,
                'specs' => [
                    'Fuel' => 'Diesel',
                    'Transmission' => 'Manual',
                    'Kilometers' => '62,000 km',
                    'Owner' => '1st Owner',
                    'Mileage' => '23 km/l ARAI',
                    'Insurance' => 'Valid Comprehensive Insurance',
                ],
                'description' => 'White Ford Figo Titanium top model diesel in showroom condition. Alloy wheels, touchscreen infotainment, chilled automatic climate control, dual airbags. Registration RJ14, clean service history.',
                'images' => [
                    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'maruti-suzuki-eeco-7-seater-ac-cng-passing',
                'title' => 'MARUTI SUZUKI EECO 7 SEATER AC (CNG FITTED & PASSING)',
                'category_id' => $vehicleCat->id,
                'price' => 19500000, // ₹ 1,95,000
                'compare_price' => 23000000,
                'brand' => 'Maruti Suzuki',
                'sku' => 'EECO-7STR-195K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'ELITE',
                'deal_label' => 'Verified',
                'location' => 'Kishangarh, Rajasthan',
                'ad_id' => '1857501928',
                'views_past_hour' => 11,
                'specs' => [
                    'Seating' => '7 Seater',
                    'AC' => 'Factory Fitted Chilled AC',
                    'Fuel' => 'Petrol + CNG Endorsed in RC',
                    'Year' => '2019 Late',
                ],
                'description' => 'Clean white Maruti Suzuki Eeco 7 Seater with company AC and tested CNG kit. Runs super smooth, clean interiors, new tyres. Ready for taxi or family use in Kishangarh.',
                'images' => [
                    'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'sony-playstation-5-digital-edition-with-2-controllers',
                'title' => 'SONY PLAYSTATION 5 DIGITAL EDITION WITH 2 CONTROLLERS',
                'category_id' => $electronicsCat->id,
                'price' => 3450000, // ₹ 34,500
                'compare_price' => 4499000,
                'brand' => 'Sony',
                'sku' => 'PS5-DIGITAL-34K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'FEATURED',
                'deal_label' => 'Fair Deal',
                'location' => 'Jaipur, Rajasthan',
                'ad_id' => '1857609214',
                'views_past_hour' => 18,
                'specs' => [
                    'Model' => 'PlayStation 5 Digital',
                    'Storage' => '825 GB Ultra-Speed SSD',
                    'Accessories' => '2x DualSense Wireless Controllers + HDMI 2.1 Cable',
                    'Condition' => 'Flawless 10/10',
                ],
                'description' => 'Sony PS5 Digital Edition console with 2 genuine DualSense controllers and charging dock. Loaded with pre-installed games. Tested and in pristine working order.',
                'images' => [
                    'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'royal-enfield-classic-350-stealth-black-dual-channel-abs',
                'title' => 'ROYAL ENFIELD CLASSIC 350 STEALTH BLACK (DUAL ABS)',
                'category_id' => $vehicleCat->id,
                'price' => 14500000, // ₹ 1,45,000
                'compare_price' => 18500000,
                'brand' => 'Royal Enfield',
                'sku' => 'RE-CLASSIC-145K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'ELITE',
                'deal_label' => 'Top Rated',
                'location' => 'Kishangarh, Rajasthan',
                'ad_id' => '1857703819',
                'views_past_hour' => 22,
                'specs' => [
                    'Engine' => '349cc J-Series Single Cylinder',
                    'Color' => 'Matte Stealth Black with Red Accents',
                    'Odometer' => '14,200 km Genuine',
                    'Brakes' => 'Dual Channel ABS + Alloy Wheels',
                ],
                'description' => 'Single owner Royal Enfield Classic 350 Stealth Black with factory tubeless alloy wheels and touring seat. Serviced strictly at authorized RE showroom. Roaring thump and great mileage.',
                'images' => [
                    'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'slug' => 'office-executive-ergonomic-mesh-chair-high-back',
                'title' => 'OFFICE EXECUTIVE ERGONOMIC MESH CHAIR (HIGH BACK)',
                'category_id' => $furnitureCat->id,
                'price' => 420000, // ₹ 4,200
                'compare_price' => 750000,
                'brand' => 'ErgoComfort',
                'sku' => 'CHAIR-ERGO-42K',
                'is_featured' => true,
                'is_negotiable' => true,
                'badge' => 'FEATURED',
                'deal_label' => 'Value Pick',
                'location' => 'Kishangarh, Rajasthan',
                'ad_id' => '1857812903',
                'views_past_hour' => 5,
                'specs' => [
                    'Type' => 'Executive Office Chair',
                    'Support' => 'Adjustable Lumbar Support + 2D Armrests',
                    'Mechanism' => 'Multi-angle Tilt Lock Hydraulic Gas Lift',
                    'Condition' => 'Brand New Unboxed',
                ],
                'description' => 'Heavy duty ergonomic mesh office chair for work from home or business cabin. Breathable Korean mesh back with headrest and smooth rolling nylon castor wheels.',
                'images' => [
                    'https://images.unsplash.com/photo-1580481077195-c546a3628e93?auto=format&fit=crop&w=800&q=80',
                ],
            ],
        ];

        foreach ($items as $data) {
            $product = Product::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $data['category_id'],
                    'vertical' => 'used',
                    'listing_type' => 'classified',
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'short_description' => Str::limit($data['description'], 120),
                    'brand' => $data['brand'],
                    'base_price' => $data['price'],
                    'compare_at_price' => $data['compare_price'],
                    'gst_rate' => 0,
                    'sku' => $data['sku'],
                    'status' => 'active',
                    'is_featured' => $data['is_featured'],
                    'is_negotiable' => $data['is_negotiable'],
                    'is_sold' => false,
                    'total_stock' => 1,
                    'published_at' => now(),
                    'custom_fields' => [
                        'ad_id' => $data['ad_id'],
                        'badge' => $data['badge'],
                        'deal_label' => $data['deal_label'],
                        'location' => $data['location'],
                        'views_past_hour' => $data['views_past_hour'],
                        'specs' => $data['specs'],
                    ],
                ]
            );

            // Clean & re-attach images
            ProductImage::where('product_id', $product->id)->delete();
            foreach ($data['images'] as $idx => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $imgUrl,
                    'alt_text' => $data['title'],
                    'sort_order' => $idx,
                ]);
            }
        }
    }
}
