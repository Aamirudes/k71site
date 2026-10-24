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

class RealUsedMarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Verified Seller User & Vendor
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

        $vendor = Vendor::firstOrCreate(
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
                'address' => 'Shivaji Nagar, Kishangarh, Ajmer District',
                'description' => 'Verified Pre-Owned Electronics, Flagship Smartphones, Premium Furnishings, and Automotive Specialists.',
                'status' => 'approved',
                'approved_at' => now()->subMonths(11),
                'colour_palette' => 'cobalt',
            ]
        );

        // 2. Fetch or create relevant categories
        $mobileCat = Category::where('slug', 'used-mobiles-tablets')->orWhere('slug', 'used-used-mobiles-tablets')->orWhere('slug', 'used-mobiles')->first()
            ?: Category::create(['name' => 'Mobiles & Tablets', 'slug' => 'used-mobiles', 'vertical' => 'used', 'is_active' => true]);

        $laptopCat = Category::where('slug', 'used-laptops-electronics')->orWhere('slug', 'used-electronics')->first()
            ?: Category::create(['name' => 'Laptops & Electronics', 'slug' => 'used-electronics', 'vertical' => 'used', 'is_active' => true]);

        $vehicleCat = Category::where('slug', 'used-vehicles-bikes')->orWhere('slug', 'used-vehicles')->first()
            ?: Category::create(['name' => 'Cars & Vehicles', 'slug' => 'used-vehicles', 'vertical' => 'used', 'is_active' => true]);

        $furnitureCat = Category::where('slug', 'used-furniture')->orWhere('slug', 'pre-loved-used-goods-thrift-used-furniture-decor')->first()
            ?: Category::create(['name' => 'Furniture & Decor', 'slug' => 'used-furniture', 'vertical' => 'used', 'is_active' => true]);

        $applianceCat = Category::where('slug', 'used-appliances')->orWhere('slug', 'used-home-appliances')->first()
            ?: Category::create(['name' => 'Appliances & TV', 'slug' => 'used-appliances', 'vertical' => 'used', 'is_active' => true]);

        $commercialCat = Category::where('slug', 'pre-loved-used-goods-thrift-scrap-industrial-recyclables')->orWhere('slug', 'used-commercial')->first()
            ?: Category::create(['name' => 'Commercial & Machinery', 'slug' => 'used-commercial', 'vertical' => 'used', 'is_active' => true]);

        // 3. Real Listings Dataset
        $realListings = [
            // MOBILES
            [
                'title' => 'Apple iPhone 14 Pro 128GB Deep Purple (Battery 91% with Bill & Box)',
                'slug' => 'apple-iphone-14-pro-128gb-deep-purple',
                'category_id' => $mobileCat->id,
                'price' => 6250000, // ₹ 62,500
                'brand' => 'Apple',
                'condition' => 'Like New',
                'location' => 'Madanganj, Kishangarh',
                'description' => "Original Indian bill and box available. Battery health is 91%. Not a single scratch or dent, always kept with tempered glass and Spigen cover. FaceID, TrueTone, and all cameras 100% working. Available for physical testing at Madanganj.",
                'specs' => [
                    'Brand' => 'Apple',
                    'Model' => 'iPhone 14 Pro',
                    'Storage' => '128 GB',
                    'Battery Health' => '91%',
                    'Condition' => 'Like New',
                    'Warranty' => 'Expired (Under AppleCare)',
                    'Bill & Box' => 'Both Available',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1695048133142-1a20484d2569?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'iPhone 13 128GB Midnight (Spotless, 10 Months Used)',
                'slug' => 'iphone-13-128gb-midnight-spotless',
                'category_id' => $mobileCat->id,
                'price' => 3800000, // ₹ 38,000
                'brand' => 'Apple',
                'condition' => 'Like New',
                'location' => 'Shivaji Nagar, Kishangarh',
                'description' => "Single-hand used iPhone 13 128GB Midnight. Battery health 89%. Comes with original type-C cable, bill and box. Zero scratches on screen or back glass. Reason for sale: upgraded to 16 Pro.",
                'specs' => [
                    'Brand' => 'Apple',
                    'Model' => 'iPhone 13',
                    'Storage' => '128 GB',
                    'Battery Health' => '89%',
                    'Condition' => 'Like New',
                    'Bill & Box' => 'Available',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Samsung Galaxy S23 Ultra 5G 256GB Phantom Black (With S-Pen)',
                'slug' => 'samsung-galaxy-s23-ultra-5g-phantom-black',
                'category_id' => $mobileCat->id,
                'price' => 6800000, // ₹ 68,000
                'brand' => 'Samsung',
                'condition' => 'Like New',
                'location' => 'Makrana Road, Kishangarh',
                'description' => "Flagship Samsung S23 Ultra with 200MP camera and Snapdragon 8 Gen 2 processor. 12GB RAM, 256GB Storage. Original S-Pen, Samsung fast charger, and bill included. No issues whatsoever.",
                'specs' => [
                    'Brand' => 'Samsung',
                    'Model' => 'Galaxy S23 Ultra',
                    'RAM / ROM' => '12GB / 256GB',
                    'Camera' => '200MP Quad Camera',
                    'Condition' => 'Like New',
                    'Bill & Box' => 'Available',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'OnePlus 11 5G 16GB/256GB Titan Black (80W SuperVOOC Charger)',
                'slug' => 'oneplus-11-5g-16gb-256gb-titan-black',
                'category_id' => $mobileCat->id,
                'price' => 3250000, // ₹ 32,500
                'brand' => 'OnePlus',
                'condition' => 'Good Condition',
                'location' => 'Bus Stand, Kishangarh',
                'description' => "OnePlus 11 5G in excellent working order. Hasselblad camera tuning, Snapdragon 8 Gen 2, charges 0 to 100% in 25 mins. Includes original 80W power adapter, cable and bill.",
                'specs' => [
                    'Brand' => 'OnePlus',
                    'Model' => 'OnePlus 11',
                    'RAM / ROM' => '16GB / 256GB',
                    'Processor' => 'Snapdragon 8 Gen 2',
                    'Condition' => 'Good Condition',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // VEHICLES & CARS
            [
                'title' => 'Maruti Suzuki Swift Dzire VXi 2020 (Petrol, 1st Owner, 42,000 KM)',
                'slug' => 'maruti-suzuki-swift-dzire-vxi-2020-petrol',
                'category_id' => $vehicleCat->id,
                'price' => 54500000, // ₹ 5,45,000
                'brand' => 'Maruti Suzuki',
                'condition' => 'Good Condition',
                'location' => 'Kutchery Road, Ajmer',
                'description' => "Maruti Dzire VXi 2020 model in pristine Pearl White. First owner, doctor driven, service record available with Maruti Authorised workshop. Comprehensive insurance valid till Dec 2026. Chilled AC, power steering, reverse parking sensors.",
                'specs' => [
                    'Make & Model' => 'Maruti Suzuki Dzire VXi',
                    'Year' => '2020',
                    'Fuel Type' => 'Petrol',
                    'KM Driven' => '42,000 km',
                    'Ownership' => '1st Owner',
                    'Transmission' => 'Manual',
                    'Insurance' => 'Valid Comprehensive',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Hyundai i20 Asta Optional Diesel 2019 (Sunroof, 54,000 KM, Fiery Red)',
                'slug' => 'hyundai-i20-asta-optional-diesel-2019',
                'category_id' => $vehicleCat->id,
                'price' => 49000000, // ₹ 4,90,000
                'brand' => 'Hyundai',
                'condition' => 'Good Condition',
                'location' => 'Madanganj, Kishangarh',
                'description' => "Top model Hyundai i20 with factory electric sunroof, touchscreen infotainment, push button start, alloy wheels, and rear parking camera. Diesel engine gives 22+ kmpl on highway. Non-accidental, clean chassis.",
                'specs' => [
                    'Make & Model' => 'Hyundai Elite i20 Asta(O)',
                    'Year' => '2019',
                    'Fuel Type' => 'Diesel (CRDi)',
                    'KM Driven' => '54,000 km',
                    'Sunroof' => 'Yes (Factory Electric)',
                    'Ownership' => '1st Owner',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Mahindra Thar LX 4x4 Hard Top Diesel 2021 (Black, 35,000 KM)',
                'slug' => 'mahindra-thar-lx-4x4-hard-top-diesel-2021',
                'category_id' => $vehicleCat->id,
                'price' => 128000000, // ₹ 12,80,000
                'brand' => 'Mahindra',
                'condition' => 'Like New',
                'location' => 'Jaipur Road, Kishangarh',
                'description' => "Mahindra Thar LX 4WD Manual Hard Top in Napoli Black. 2.2L mHawk Diesel engine. 18-inch original alloy wheels, touchscreen navigation, dual airbags, hill assist. Zero off-road abuse, city commute only.",
                'specs' => [
                    'Make & Model' => 'Mahindra Thar LX 4WD',
                    'Year' => '2021',
                    'Fuel Type' => 'Diesel (mHawk)',
                    'KM Driven' => '35,000 km',
                    'Drive' => '4x4 with Low Ratio',
                    'Roof' => 'Factory Hard Top',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // BIKES & MOTORCYCLES
            [
                'title' => 'Royal Enfield Classic 350 Stealth Black 2022 (Dual Channel ABS, 14,000 KM)',
                'slug' => 'royal-enfield-classic-350-stealth-black-2022',
                'category_id' => $vehicleCat->id,
                'price' => 14800000, // ₹ 1,48,000
                'brand' => 'Royal Enfield',
                'condition' => 'Like New',
                'location' => 'Old City, Kishangarh',
                'description' => "Next-Gen J-Series engine Royal Enfield Classic 350 in Matte Stealth Black with alloy wheels and tubeless tyres. Dual channel ABS, smooth thump, zero vibrations. Both keys, insurance and RC ready for transfer.",
                'specs' => [
                    'Brand' => 'Royal Enfield',
                    'Model' => 'Classic 350 (Dark Edition)',
                    'Year' => '2022',
                    'KM Driven' => '14,000 km',
                    'Brakes' => 'Dual ABS + Alloy Wheels',
                    'Color' => 'Stealth Black',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Honda Activa 6G Deluxe 2023 (Single Owner, LED Headlight, 8,200 KM)',
                'slug' => 'honda-activa-6g-deluxe-2023',
                'category_id' => $vehicleCat->id,
                'price' => 6400000, // ₹ 64,000
                'brand' => 'Honda',
                'condition' => 'Brand New',
                'location' => 'Shivaji Nagar, Kishangarh',
                'description' => "Practically brand new Honda Activa 6G Deluxe. Metallic grey colour, LED headlamp, external fuel filling, combi-braking system. 50+ kmpl average. No loan, clean papers.",
                'specs' => [
                    'Brand' => 'Honda',
                    'Model' => 'Activa 6G Deluxe',
                    'Year' => '2023',
                    'KM Driven' => '8,200 km',
                    'Condition' => 'Mint / Like New',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // LAPTOPS & ELECTRONICS
            [
                'title' => 'Apple MacBook Air M1 8GB / 256GB Space Grey (Battery Health 94%, Box)',
                'slug' => 'apple-macbook-air-m1-space-grey',
                'category_id' => $laptopCat->id,
                'price' => 5200000, // ₹ 52,000
                'brand' => 'Apple',
                'condition' => 'Like New',
                'location' => 'Kutchery Road, Ajmer',
                'description' => "Apple MacBook Air M1 chip. Extremely clean laptop, used only for office documents and web surfing. Battery cycle count only 84, health 94%. Comes with original MagSafe/Type-C 30W adapter, cable and original retail box.",
                'specs' => [
                    'Brand' => 'Apple',
                    'Processor' => 'Apple M1 (8-core CPU / 7-core GPU)',
                    'RAM' => '8 GB Unified Memory',
                    'SSD' => '256 GB',
                    'Battery Health' => '94% (84 cycles)',
                    'Screen' => '13.3-inch Retina Display',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Dell XPS 15 9520 (Intel Core i7 12th Gen, 16GB RAM, RTX 3050, 512GB SSD)',
                'slug' => 'dell-xps-15-9520-core-i7-12th-gen',
                'category_id' => $laptopCat->id,
                'price' => 8400000, // ₹ 84,000
                'brand' => 'Dell',
                'condition' => 'Like New',
                'location' => 'Vaishali Nagar, Jaipur',
                'description' => "Premium Dell XPS 15 with carbon fiber palm rest, 16:10 FHD+ InfinityEdge display, NVIDIA GeForce RTX 3050 graphics, and Intel Core i7-12700H 14-core CPU. Ideal for video editing, coding, and heavy multitasking.",
                'specs' => [
                    'Brand' => 'Dell',
                    'Processor' => 'Intel Core i7-12700H (14 Cores)',
                    'Graphics' => 'NVIDIA RTX 3050 4GB',
                    'RAM / SSD' => '16GB DDR5 / 512GB NVMe SSD',
                    'Display' => '15.6-inch FHD+ 500 nits',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Sony PlayStation 5 Disc Edition (With 2 DualSense Controllers + 3 Games)',
                'slug' => 'sony-playstation-5-disc-edition-bundle',
                'category_id' => $laptopCat->id,
                'price' => 3850000, // ₹ 38,500
                'brand' => 'Sony',
                'condition' => 'Good Condition',
                'location' => 'Madanganj, Kishangarh',
                'description' => "PS5 Disc Edition in perfect working state. Includes 2 original DualSense controllers (White + Midnight Black), HDMI 2.1 cable, stand, and 3 physical game discs (Spider-Man Miles Morales, God of War Ragnarok, FIFA 23).",
                'specs' => [
                    'Brand' => 'Sony',
                    'Model' => 'PlayStation 5 (Disc Edition)',
                    'Storage' => '825 GB Ultra-Fast SSD',
                    'Accessories' => '2 Wireless Controllers + 3 Games',
                    'Condition' => 'Good Condition',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // FURNITURE & HOME
            [
                'title' => 'L-Shaped Fabric Recliner Sofa Set (Dark Grey, 7-Seater with Ottomans)',
                'slug' => 'l-shaped-fabric-recliner-sofa-set-7-seater',
                'category_id' => $furnitureCat->id,
                'price' => 2650000, // ₹ 26,500
                'brand' => 'Godrej Interio',
                'condition' => 'Like New',
                'location' => 'Shivaji Nagar, Kishangarh',
                'description' => "Spacious 7-seater sectional corner sofa set with high-density foam cushions and soft suede fabric. Includes 2 matching ottomans and 5 accent cushions. Zero stains, no sagging. Selling due to house renovation.",
                'specs' => [
                    'Type' => 'Sectional L-Shape Sofa',
                    'Seating Capacity' => '7 Persons',
                    'Material' => 'Solid Teak Frame + High Density Foam',
                    'Fabric' => 'Stain-Resistant Suede Fabric',
                    'Condition' => 'Like New / Clean',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1580481077195-c546a3628e93?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Solid Sheesham Wood 6-Seater Dining Table with Cushioned Chairs',
                'slug' => 'solid-sheesham-wood-6-seater-dining-table',
                'category_id' => $furnitureCat->id,
                'price' => 2100000, // ₹ 21,000
                'brand' => 'Handcrafted Rajasthan',
                'condition' => 'Good Condition',
                'location' => 'RIICO Industrial Area, Kishangarh',
                'description' => "100% genuine heavy Sheesham (Indian Rosewood) dining table with 6 ergonomic chairs. Walnut natural finish highlighting grain texture. Includes 8mm tempered glass top cover. Very sturdy and lifetime durable.",
                'specs' => [
                    'Material' => '100% Pure Sheesham Wood',
                    'Capacity' => '6 Seater',
                    'Finish' => 'Natural Honey Walnut Finish',
                    'Top Glass' => '8mm Toughened Glass Included',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1617806118233-18e1de247200?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // APPLIANCES
            [
                'title' => 'LG 55-inch 4K UHD Smart OLED TV with Magic Remote (WebOS, Dolby Vision)',
                'slug' => 'lg-55-inch-4k-uhd-smart-oled-tv',
                'category_id' => $applianceCat->id,
                'price' => 3600000, // ₹ 36,000
                'brand' => 'LG',
                'condition' => 'Like New',
                'location' => 'Madanganj, Kishangarh',
                'description' => "LG 55-inch Cinema Screen 4K Ultra HD Smart TV with WebOS, built-in Netflix, Prime Video, YouTube, AirPlay 2, and Apple HomeKit. Comes with Magic Motion Remote and wall mount bracket. Crystal clear picture quality.",
                'specs' => [
                    'Brand' => 'LG',
                    'Screen Size' => '55 Inch',
                    'Resolution' => '4K Ultra HD (3840 x 2160)',
                    'Smart OS' => 'LG webOS with Magic Remote',
                    'Audio' => '20W Dolby Atmos',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Samsung 324L Double Door Inverter Refrigerator (Convertible 5-in-1, Silver)',
                'slug' => 'samsung-324l-double-door-inverter-refrigerator',
                'category_id' => $applianceCat->id,
                'price' => 1950000, // ₹ 19,500
                'brand' => 'Samsung',
                'condition' => 'Good Condition',
                'location' => 'Civil Lines, Ajmer',
                'description' => "Samsung 324-litre double door frost-free refrigerator with Digital Inverter technology and Twin Cooling Plus. Energy efficient 3-star rating. Toughened glass shelves, deodorizing filter. Excellent cooling.",
                'specs' => [
                    'Brand' => 'Samsung',
                    'Capacity' => '324 Litres',
                    'Type' => 'Double Door Frost-Free',
                    'Compressor' => 'Digital Inverter (10 Year Warranty)',
                    'Condition' => 'Clean & Fully Operational',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1584992236310-6edddc08acff?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // COMMERCIAL & INDUSTRIAL (KISHANGARH SPECIAL)
            [
                'title' => 'Mahindra Bolero Maxi Truck Plus 2019 (Power Steering, Good Tyres)',
                'slug' => 'mahindra-bolero-maxi-truck-plus-2019',
                'category_id' => $commercialCat->id,
                'price' => 44000000, // ₹ 4,40,000
                'brand' => 'Mahindra',
                'condition' => 'Good Condition',
                'location' => 'Marble Slurry Bypass, Kishangarh',
                'description' => "Mahindra Bolero pickup maxi truck in solid condition. Heavy duty suspension for marble slab transportation, new Apollo tyres installed last month, fitness certificate and insurance up to date.",
                'specs' => [
                    'Make & Model' => 'Mahindra Bolero Maxi Truck Plus',
                    'Year' => '2019',
                    'Payload Capacity' => '1.5 Tons',
                    'Fuel' => 'Diesel',
                    'Passing' => 'Commercial Goods Carrier',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'title' => 'Industrial 15 HP Electric Marble Cutting Gangsaw Motor with Starter',
                'slug' => 'industrial-15-hp-electric-marble-cutting-motor',
                'category_id' => $commercialCat->id,
                'price' => 3800000, // ₹ 38,000
                'brand' => 'Kirloskar',
                'condition' => 'Good Condition',
                'location' => 'RIICO Marble Area, Kishangarh',
                'description' => "Kirloskar 15 HP 3-Phase heavy industrial induction electric motor. Suitable for marble gangsaw cutter, edge polishing, or slurry pump. Rewinding untouched, 100% pure copper coil, comes with L&T Star-Delta starter box.",
                'specs' => [
                    'Brand' => 'Kirloskar Electric',
                    'Power' => '15 HP (11 kW)',
                    'Phase' => '3 Phase 415V',
                    'RPM' => '1440 RPM',
                    'Starter' => 'L&T Starter Included',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                ],
            ],
        ];

        // 4. Upsert listings into database
        foreach ($realListings as $itemData) {
            $images = $itemData['images'];
            unset($itemData['images']);

            $slug = $itemData['slug'];
            $title = $itemData['title'];
            $price = $itemData['price'];
            $cond = $itemData['condition'];
            $loc = $itemData['location'];

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $itemData['category_id'],
                    'vertical' => 'used',
                    'listing_type' => 'classified',
                    'title' => $title,
                    'description' => $itemData['description'],
                    'short_description' => Str::limit($itemData['description'], 120),
                    'brand' => $itemData['brand'] ?? 'Standard',
                    'base_price' => $price,
                    'compare_at_price' => (int) ($price * 1.2),
                    'gst_rate' => 0,
                    'sku' => 'USED-' . strtoupper(Str::random(6)),
                    'status' => 'active',
                    'is_featured' => true,
                    'is_negotiable' => true,
                    'is_sold' => false,
                    'total_stock' => 1,
                    'published_at' => now()->subDays(random_int(1, 5)),
                    'custom_fields' => [
                        'ad_id' => (string) random_int(1857100000, 1857999999),
                        'user_id' => $sellerUser->id,
                        'badge' => 'VERIFIED',
                        'deal_label' => 'Verified Deal',
                        'location' => $loc,
                        'contact_phone' => '+919549311580',
                        'condition' => $cond,
                        'views_past_hour' => random_int(4, 18),
                        'bill_available' => 'Yes, Original Available',
                        'specs' => array_merge(['Condition' => $cond, 'Location' => $loc], $itemData['specs'] ?? []),
                    ],
                ]
            );

            // Re-seed images
            ProductImage::where('product_id', $product->id)->delete();
            foreach ($images as $idx => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $imgUrl,
                    'alt_text' => $title,
                    'sort_order' => $idx,
                ]);
            }
        }
    }
}
