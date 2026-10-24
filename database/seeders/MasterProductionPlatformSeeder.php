<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\Review;
use App\Models\Role;
use App\Models\Short;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterProductionPlatformSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles Setup
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // 2. Master Administrator & Buyer Accounts
        $admin = User::firstOrCreate(
            ['email' => 'crab.hashir@gmail.com'],
            [
                'first_name' => 'Hashir',
                'last_name' => 'Admin',
                'username' => 'hashir',
                'password' => Hash::make('Hashir1284@0'),
                'is_blocked' => 0,
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ]
        );
        $admin->update([
            'first_name' => 'Hashir',
            'last_name' => 'Admin',
            'username' => 'hashir',
            'password' => Hash::make('Hashir1284@0'),
            'is_blocked' => 0,
            'blocked_reason' => null,
            'email_verified_at' => $admin->email_verified_at ?: now(),
        ]);
        $admin->roles()->syncWithoutDetaching([$adminRole->id, $customerRole->id]);

        $buyer = User::firstOrCreate(
            ['email' => 'buyer@k71.in'],
            [
                'first_name' => 'K71',
                'last_name' => 'Buyer',
                'username' => 'k71buyer',
                'password' => Hash::make('Hashir1284@0'),
                'email_verified_at' => now(),
                'terms_accepted_at' => now(),
            ]
        );
        $buyer->roles()->syncWithoutDetaching([$customerRole->id]);

        // 3. Resolve Exact 14 Ecosystem Categories
        $catFood = Category::where('slug', 'food-dining')->orWhere('slug', 'awadhi-biryanis-meals')->first()
            ?? Category::firstOrCreate(['slug' => 'food-dining'], ['name' => 'Food & Dining', 'vertical' => 'food', 'icon' => '🍕', 'is_active' => true]);

        $catKirana = Category::where('slug', 'groceries-daily-essentials-fmcg')->orWhere('slug', 'staples-cooking-essentials')->first()
            ?? Category::firstOrCreate(['slug' => 'groceries-daily-essentials-fmcg'], ['name' => 'Groceries & Daily Essentials', 'vertical' => 'food', 'icon' => '🛒', 'is_active' => true]);

        $catFashion = Category::where('slug', 'clothing-fashion-accessories')->orWhere('slug', 'handloom-sarees')->first()
            ?? Category::firstOrCreate(['slug' => 'clothing-fashion-accessories'], ['name' => 'Clothing, Fashion & Accessories', 'vertical' => 'marketplace', 'icon' => '👗', 'is_active' => true]);

        $catFootwear = Category::where('slug', 'footwear')->first()
            ?? Category::firstOrCreate(['slug' => 'footwear'], ['name' => 'Footwear & Leather Goods', 'vertical' => 'marketplace', 'icon' => '👞', 'is_active' => true]);

        $catElectronics = Category::where('slug', 'electronics-appliances-it')->first()
            ?? Category::firstOrCreate(['slug' => 'electronics-appliances-it'], ['name' => 'Electronics, Appliances & IT', 'vertical' => 'marketplace', 'icon' => '📱', 'is_active' => true]);

        $catMachinery = Category::where('slug', 'heavy-industrial-machinery-construction')->first()
            ?? Category::firstOrCreate(['slug' => 'heavy-industrial-machinery-construction'], ['name' => 'Heavy Industrial, Machinery & Construction', 'vertical' => 'enquiry', 'icon' => '🛠️', 'is_active' => true]);

        $catHomeMarble = Category::where('slug', 'home-living-furniture-hardware')->first()
            ?? Category::firstOrCreate(['slug' => 'home-living-furniture-hardware'], ['name' => 'Home, Living, Furniture & Hardware', 'vertical' => 'marketplace', 'icon' => '🏠', 'is_active' => true]);

        $catServices = Category::where('slug', 'on-demand-local-on-site-services')->orWhere('slug', 'services-home-repairs')->first()
            ?? Category::firstOrCreate(['slug' => 'on-demand-local-on-site-services'], ['name' => 'On-Demand Local & On-Site Services', 'vertical' => 'services', 'icon' => '🧹', 'is_active' => true]);

        $catDigital = Category::where('slug', 'digital-services-freelancing-gigs')->first()
            ?? Category::firstOrCreate(['slug' => 'digital-services-freelancing-gigs'], ['name' => 'Digital Services, Freelancing & Gigs', 'vertical' => 'services', 'icon' => '💼', 'is_active' => true]);

        $catUsed = Category::where('slug', 'pre-loved-used-goods-thrift')->orWhere('slug', 'pre-owned-watches')->first()
            ?? Category::firstOrCreate(['slug' => 'pre-loved-used-goods-thrift'], ['name' => 'Pre-Loved, Used Goods & Thrift', 'vertical' => 'used', 'icon' => '🔄', 'is_active' => true]);

        $catRealEstate = Category::where('slug', 'real-estate-properties-high-ticket-assets')->orWhere('slug', 'like', '%residential%')->first()
            ?? Category::firstOrCreate(['slug' => 'real-estate-properties-high-ticket-assets'], ['name' => 'Real Estate, Properties & High-Ticket Assets', 'vertical' => 'enquiry', 'icon' => '🏡', 'is_active' => true]);

        $catAuto = Category::where('slug', 'automotive-vehicles-transport')->orWhere('slug', 'like', '%automotive%')->first()
            ?? Category::firstOrCreate(['slug' => 'automotive-vehicles-transport'], ['name' => 'Automotive, Vehicles & Transport', 'vertical' => 'marketplace', 'icon' => '🚗', 'is_active' => true]);

        $catBeauty = Category::where('slug', 'beauty-personal-care-health-wellness')->first()
            ?? Category::firstOrCreate(['slug' => 'beauty-personal-care-health-wellness'], ['name' => 'Beauty, Personal Care, Health & Wellness', 'vertical' => 'marketplace', 'icon' => '💄', 'is_active' => true]);

        $catSports = Category::where('slug', 'baby-toys-hobbies-books-sports')->first()
            ?? Category::firstOrCreate(['slug' => 'baby-toys-hobbies-books-sports'], ['name' => 'Baby, Toys, Hobbies, Books & Sports', 'vertical' => 'marketplace', 'icon' => '🧸', 'is_active' => true]);

        // 4. Exactly 1-to-1 Verified Vendors (1 per Category)
        $vendorsData = [
            // 1. Food
            [
                'email' => 'kitchen@k71.in',
                'name' => 'Chef Farhan Qureshi',
                'username' => 'farhanqureshi',
                'slug' => 'nawabi-dastarkhwan',
                'shop_name' => 'Nawabi Dastarkhwan Kitchen',
                'vendor_type' => 'food',
                'city' => 'Lucknow',
                'state' => 'Uttar Pradesh',
                'pincode' => '226001',
                'address' => 'Hazratganj Main Market, Near Mayfair Cinema, Lucknow',
                'phone' => '+91 94150 99887',
                'description' => 'Authentic slow-cooked Awadhi dum biryanis sealed in clay handis, melt-in-mouth Galouti kebabs, and Mughlai flatbreads.',
                'colour_palette' => 'terracotta',
                'layout_preset' => 'editorial',
                'product_preset' => 'immersive',
                'upi_id' => 'farhan@upi',
                'logo' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1600&q=80',
            ],
            // 2. Groceries
            [
                'email' => 'kirana@k71.in',
                'name' => 'Vijay Bansal',
                'username' => 'bansalkirana',
                'slug' => 'bansal-organic-kirana',
                'shop_name' => 'Bansal Kirana & Organic Mart',
                'vendor_type' => 'food',
                'city' => 'Delhi',
                'state' => 'Delhi',
                'pincode' => '110006',
                'address' => 'Shop 42, Khari Baoli Spice Market, Old Delhi',
                'phone' => '+91 98100 24680',
                'description' => 'Direct-from-farm Daawat aged Basmati rice, wood-pressed mustard oils, Gir Cow A2 Desi Ghee, Kashmiri saffron, and premium dry fruits.',
                'colour_palette' => 'sage',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'bansalkirana@okaxis',
                'logo' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1600&q=80',
            ],
            // 3. Clothing & Fashion
            [
                'email' => 'kavita@k71.in',
                'name' => 'Kavita Mehta',
                'username' => 'kavitamehta',
                'slug' => 'kavita-couture',
                'shop_name' => 'Kavita Couture Banarasi Atelier',
                'vendor_type' => 'clothing',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302001',
                'address' => 'Johari Bazaar, Near Hawa Mahal, Jaipur',
                'phone' => '+91 98290 88776',
                'description' => 'Pure Katan Silk handloom Banarasi sarees, designer bridal lehengas, and hand-embroidered Zardozi dupattas crafted by master weavers.',
                'colour_palette' => 'berry',
                'layout_preset' => 'editorial',
                'product_preset' => 'immersive',
                'upi_id' => 'kavitamehta@okaxis',
                'logo' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1600&q=80',
            ],
            // 4. Footwear
            [
                'email' => 'footwear@k71.in',
                'name' => 'Anil Patil',
                'username' => 'anilpatil',
                'slug' => 'royal-footwear',
                'shop_name' => 'Royal Leather Guild & Footwear',
                'vendor_type' => 'footwear',
                'city' => 'Kolhapur',
                'state' => 'Maharashtra',
                'pincode' => '416002',
                'address' => 'Chhatrapati Shivaji Market, Kolhapur',
                'phone' => '+91 98220 33445',
                'description' => 'Artisan handcrafted genuine leather Oxford shoes, classic brogues, and ethnic Kolhapuri chappals with cushioned memory foam soles.',
                'colour_palette' => 'amber',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'royalfootwear@upi',
                'logo' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=1600&q=80',
            ],
            // 5. Electronics
            [
                'email' => 'tech@k71.in',
                'name' => 'Siddharth Rao',
                'username' => 'siddharthtech',
                'slug' => 'nexatech-electronics',
                'shop_name' => 'NexaTech Flagship Electronics',
                'vendor_type' => 'electronics',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pincode' => '560001',
                'address' => 'Brigade Road, Central Tech Arcade, Bengaluru',
                'phone' => '+91 98800 12345',
                'description' => 'Authorized distributor of flagship 5G smartphones, ultra-slim laptops, AMOLED smartwatches, and premium wireless audio systems with official brand warranties.',
                'colour_palette' => 'cobalt',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'nexatech@upi',
                'logo' => 'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1600&q=80',
            ],
            // 6. Industrial Machinery
            [
                'email' => 'tools@k71.in',
                'name' => 'Bhavesh Patel',
                'username' => 'bhaveshtools',
                'slug' => 'apex-power-tools',
                'shop_name' => 'Apex Industrial Diamond Tools & Machinery',
                'vendor_type' => 'machinery',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380002',
                'address' => 'GIDC Estate, Vatva, Ahmedabad',
                'phone' => '+91 98250 11223',
                'description' => 'Heavy-duty bridge cutting saws, laser-welded diamond saw blades for granite, marble edge bevelers, and industrial fabrication tools.',
                'colour_palette' => 'cobalt',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'apextools@upi',
                'logo' => 'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1600&q=80',
            ],
            // 7. Home & Living (Marble Slabs)
            [
                'email' => 'makrana@k71.in',
                'name' => 'Haji Abdul Rehman',
                'username' => 'makranamarble',
                'slug' => 'royal-makrana-marble',
                'shop_name' => 'Royal Makrana Marbles & Slabs',
                'vendor_type' => 'marketplace',
                'city' => 'Kishangarh',
                'state' => 'Rajasthan',
                'pincode' => '305801',
                'address' => 'Industrial Area Phase 2, Kishangarh Industrial Corridor, Rajasthan',
                'phone' => '+91 98290 12345',
                'description' => 'Pioneers in Makrana Pure White Marble, Dungri slabs, Italian Statuario, and Rajasthan Black Galaxy Granites. Certified quarry-direct wholesale and retail supply across India.',
                'colour_palette' => 'emerald',
                'layout_preset' => 'editorial',
                'product_preset' => 'immersive',
                'upi_id' => 'makrana@upi',
                'logo' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1600&q=80',
            ],
            // 8. On-Demand Services
            [
                'email' => 'polishing@k71.in',
                'name' => 'Sanjay Verma',
                'username' => 'sanjayverma',
                'slug' => 'diamond-gloss-polishing',
                'shop_name' => 'Diamond Gloss Marble Polishing & Restoration',
                'vendor_type' => 'services',
                'city' => 'Gurugram',
                'state' => 'Haryana',
                'pincode' => '122001',
                'address' => 'Sector 29, Golf Course Road, Gurugram',
                'phone' => '+91 98110 55667',
                'description' => 'Italian marble crystallization, 7-step diamond floor grinding, crack filling, and epoxy nano-sealing doorstep services.',
                'colour_palette' => 'slate',
                'layout_preset' => 'minimal',
                'product_preset' => 'compact',
                'upi_id' => 'diamondgloss@upi',
                'logo' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80',
            ],
            // 9. Digital Services
            [
                'email' => 'codecraft@k71.in',
                'name' => 'Arjun Varma',
                'username' => 'arjuncodecraft',
                'slug' => 'codecraft-digital',
                'shop_name' => 'CodeCraft UI/UX & Web Solutions',
                'vendor_type' => 'digital_services',
                'city' => 'Hyderabad',
                'state' => 'Telangana',
                'pincode' => '500081',
                'address' => 'HITEC City, Cyber Towers Hub, Hyderabad',
                'phone' => '+91 99890 66778',
                'description' => 'Full-stack SaaS web development, enterprise React/Laravel architecture, bespoke Figma UI/UX design systems, and cloud DevOps optimization.',
                'colour_palette' => 'cobalt',
                'layout_preset' => 'editorial',
                'product_preset' => 'standard',
                'upi_id' => 'codecraft@upi',
                'logo' => 'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1600&q=80',
            ],
            // 10. Pre-Loved & Used
            [
                'email' => 'horology@k71.in',
                'name' => 'Vikram Singhania',
                'username' => 'vikramhorology',
                'slug' => 'heritage-timepieces',
                'shop_name' => 'Heritage Watches & Horology',
                'vendor_type' => 'used',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400023',
                'address' => 'Fort Heritage Arcade, Kala Ghoda, Mumbai',
                'phone' => '+91 98200 77889',
                'description' => 'Authenticated vintage mechanical watches, 1980s Seiko divers, Swiss automatic timepieces, and collectible antique maritime artifacts.',
                'colour_palette' => 'neutral',
                'layout_preset' => 'editorial',
                'product_preset' => 'immersive',
                'upi_id' => 'vikram@upi',
                'logo' => 'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1600&q=80',
            ],
            // 11. Real Estate
            [
                'email' => 'realty@k71.in',
                'name' => 'Surendra Singh Rathore',
                'username' => 'jaipurrealty',
                'slug' => 'royal-heritage-realty',
                'shop_name' => 'Royal Heritage Realty & Assets',
                'vendor_type' => 'real_estate',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302015',
                'address' => 'Tonk Road, Near B2 Bypass, Jaipur',
                'phone' => '+91 98290 44556',
                'description' => 'RERA-registered luxury high-rise residences, gated community independent duplex villas, and prime commercial plots across Rajasthan.',
                'colour_palette' => 'emerald',
                'layout_preset' => 'editorial',
                'product_preset' => 'immersive',
                'upi_id' => 'jaipurrealty@upi',
                'logo' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1600&q=80',
            ],
            // 12. Automotive Spares
            [
                'email' => 'auto@k71.in',
                'name' => 'Manish Kulkarni',
                'username' => 'speedcraftmotors',
                'slug' => 'speedcraft-auto-spares',
                'shop_name' => 'SpeedCraft Automotive & OEM Spares',
                'vendor_type' => 'automotive',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'pincode' => '411018',
                'address' => 'MIDC Industrial Area, Pimpri, Pune',
                'phone' => '+91 98230 77112',
                'description' => 'Certified OEM replacement spares, synthetic motor lubricants, ceramic brake systems, and performance tuning accessories.',
                'colour_palette' => 'cobalt',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'speedcraft@upi',
                'logo' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1600&q=80',
            ],
            // 13. Beauty & Wellness
            [
                'email' => 'wellness@k71.in',
                'name' => 'Dr. Meera Nambiar',
                'username' => 'vedicglow',
                'slug' => 'vedic-glow-organics',
                'shop_name' => 'Vedic Glow Ayurvedic Herbals & Organics',
                'vendor_type' => 'marketplace',
                'city' => 'Rishikesh',
                'state' => 'Uttarakhand',
                'pincode' => '249201',
                'address' => 'Swarg Ashram Road, Tapovan, Rishikesh',
                'phone' => '+91 98370 22334',
                'description' => 'Certified 100% pure Ayurvedic cold-pressed kumkumadi oils, wild saffron serums, herbal hair elixirs, and holistic wellness remedies.',
                'colour_palette' => 'sage',
                'layout_preset' => 'editorial',
                'product_preset' => 'immersive',
                'upi_id' => 'vedicglow@upi',
                'logo' => 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1600&q=80',
            ],
            // 14. Sports & Gear
            [
                'email' => 'sports@k71.in',
                'name' => 'Harpreet Singh',
                'username' => 'proactivesports',
                'slug' => 'proactive-sports',
                'shop_name' => 'ProActive Sports & Fitness Gear',
                'vendor_type' => 'marketplace',
                'city' => 'Meerut',
                'state' => 'Uttar Pradesh',
                'pincode' => '250002',
                'address' => 'Sports Goods Complex, Delhi Road, Meerut',
                'phone' => '+91 98371 44556',
                'description' => 'Professional Grade-1 English willow cricket bats, genuine leather balls, tournament badminton rackets, and precision athletic training gear.',
                'colour_palette' => 'amber',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'proactivesports@upi',
                'logo' => 'https://images.unsplash.com/photo-1624526267942-ab0ff8a3e972?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211?auto=format&fit=crop&w=1600&q=80',
            ],
        ];

        $vendorMap = [];
        foreach ($vendorsData as $v) {
            $conflictVendorUser = User::where('username', $v['username'])->where('email', '!=', $v['email'])->first();
            if ($conflictVendorUser) {
                $conflictVendorUser->update(['username' => 'user_' . $conflictVendorUser->id]);
            }
            $user = User::where('email', $v['email'])->orWhere('username', $v['username'])->first();
            if (!$user) {
                $user = User::create([
                    'email' => $v['email'],
                    'first_name' => explode(' ', $v['name'])[0] ?? 'Vendor',
                    'last_name' => explode(' ', $v['name'])[1] ?? 'Store',
                    'username' => $v['username'],
                    'password' => Hash::make('Password@123'),
                    'email_verified_at' => now(),
                ]);
            }
            $user->roles()->syncWithoutDetaching([$vendorRole->id]);

            $vendor = Vendor::updateOrCreate(
                ['slug' => $v['slug']],
                [
                    'user_id' => $user->id,
                    'shop_name' => $v['shop_name'],
                    'vendor_type' => $v['vendor_type'],
                    'description' => $v['description'],
                    'city' => $v['city'],
                    'state' => $v['state'],
                    'pincode' => $v['pincode'],
                    'address' => $v['address'],
                    'phone' => $v['phone'],
                    'email' => $v['email'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'colour_palette' => $v['colour_palette'],
                    'layout_preset' => $v['layout_preset'],
                    'product_preset' => $v['product_preset'],
                    'logo' => $v['logo'],
                    'banner' => $v['banner'],
                ]
            );

            VendorSetting::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'upi_id' => $v['upi_id'],
                    'cod_enabled' => ($v['vendor_type'] !== 'services' && $v['slug'] !== 'royal-makrana-marble'),
                    'upi_enabled' => 1,
                    'is_restaurant' => ($v['slug'] === 'nawabi-dastarkhwan' ? 1 : 0),
                    'is_pure_veg' => ($v['slug'] === 'bansal-organic-kirana' ? 1 : 0),
                    'cuisine_types' => ($v['slug'] === 'nawabi-dastarkhwan' ? ['Mughlai', 'Awadhi', 'Biryani'] : null),
                    'opening_time' => '09:00',
                    'closing_time' => '22:00',
                    'processing_days' => 2,
                    'return_window_days' => 7,
                ]
            );

            $vendorMap[$v['slug']] = $vendor;
        }

        // 5. Exactly 1 Flagship Product per Category (100% working with exact matching imagery)
        $productsData = [
            // 1. Food -> Nawabi Dastarkhwan
            [
                'vendor_slug' => 'nawabi-dastarkhwan',
                'category_id' => $catFood->id,
                'slug' => 'slow-cooked-awadhi-dum-mutton-biryani',
                'title' => 'Slow-Cooked Awadhi Dum Mutton Biryani (Handi)',
                'description' => 'Prepared in sealed clay handis with long-grain aged Basmati rice, prime tender goat cuts, golden fried onions, saffron, and traditional Awadhi whole potli spices. Served hot with creamy burani raita and mirchi ka salan.',
                'base_price' => 44900, // ₹449
                'compare_at_price' => 59900,
                'gst_rate' => 5,
                'hsn_code' => '210690',
                'sku' => 'BIR-MUT-AWD',
                'brand' => 'Dastarkhwan Royale',
                'vertical' => 'food',
                'listing_type' => 'food',
                'total_stock' => 100,
                'is_featured' => true,
                'custom_fields' => ['Portion' => 'Serves 2 (750g)', 'Spice Level' => 'Medium Royal', 'Packaging' => 'Clay Handi Sealed with Dough', 'Shelf Life' => 'Best consumed within 3 hours'],
                'images' => [
                    'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 2. Groceries -> Bansal Kirana
            [
                'vendor_slug' => 'bansal-organic-kirana',
                'category_id' => $catKirana->id,
                'slug' => 'pure-gir-cow-a2-bilona-desi-ghee-1l',
                'title' => 'Pure Organic Gir Cow A2 Bilona Desi Ghee (1 Litre Glass Jar)',
                'description' => 'Prepared traditionally using whole curd churning (Vedic Bilona method) from pasture-grazed Gir cows. Granular golden texture, rich natural aroma, zero preservatives or adulterants.',
                'base_price' => 149900, // ₹1,499
                'compare_at_price' => 189900,
                'gst_rate' => 12,
                'hsn_code' => '040590',
                'sku' => 'GHEE-GIR-1L',
                'brand' => 'Vedic Roots',
                'vertical' => 'food',
                'listing_type' => 'grocery',
                'total_stock' => 80,
                'is_featured' => true,
                'custom_fields' => ['Volume' => '1000ml', 'Source' => 'Grass-fed Gir Cows', 'Method' => 'Hand-churned Bilona', 'Packaging' => 'Food-grade glass jar'],
                'images' => [
                    'https://images.unsplash.com/photo-1589927986089-35812388d1f4?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 3. Fashion -> Kavita Couture
            [
                'vendor_slug' => 'kavita-couture',
                'category_id' => $catFashion->id,
                'slug' => 'banarasi-kadwa-pure-katan-silk-saree',
                'title' => 'Banarasi Kadwa Pure Katan Silk Saree (Gold & Silver Zari)',
                'description' => 'Heirloom handloom saree woven in pure mulberry Katan silk by Master Weavers in Varanasi. Intricate Kadwa bootis across the body with rich floral meenakari pallu and Silk Mark certified authenticity.',
                'base_price' => 899900, // ₹8,999
                'compare_at_price' => 1250000,
                'gst_rate' => 5,
                'hsn_code' => '500720',
                'sku' => 'SAR-KAT-RED',
                'brand' => 'Kavita Couture',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 15,
                'is_featured' => true,
                'custom_fields' => ['Fabric' => '100% Pure Katan Silk', 'Weave' => 'Handloom Kadwa', 'Length' => '6.3 Meters with unstitched blouse', 'Certification' => 'Silk Mark India Verified'],
                'images' => [
                    'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 4. Footwear -> Royal Leather Guild
            [
                'vendor_slug' => 'royal-footwear',
                'category_id' => $catFootwear->id,
                'slug' => 'handcrafted-full-grain-leather-oxford-shoes',
                'title' => 'Handcrafted Goodyear-Welted Full-Grain Leather Oxford Shoes',
                'description' => 'Exquisite artisan dress shoes hand-lasted in burnished cognac Italian full-grain leather. Built with authentic Goodyear welted double leather soles and cork bed that molds to your feet over time.',
                'base_price' => 429900, // ₹4,299
                'compare_at_price' => 649900,
                'gst_rate' => 18,
                'hsn_code' => '640359',
                'sku' => 'OXF-BRN-42',
                'brand' => 'Royal Leather Guild',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 30,
                'is_featured' => true,
                'custom_fields' => ['Leather' => 'Full-Grain Calfskin', 'Construction' => 'Goodyear Welted', 'Sole' => 'Vegetable-Tanned Hard Leather', 'Colour' => 'Cognac Burnished Brown'],
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 5. Electronics -> NexaTech Flagship Electronics
            [
                'vendor_slug' => 'nexatech-electronics',
                'category_id' => $catElectronics->id,
                'slug' => 'ultra-slim-5g-flagship-snapdragon-smartphone',
                'title' => 'Ultra-Slim 5G Flagship Snapdragon Smartphone (12GB+256GB, 120Hz AMOLED)',
                'description' => 'Cutting-edge performance powered by the Snapdragon 8 Gen 3 chipset. Stunning 6.78-inch 1.5K 120Hz LTPO curved AMOLED display, 50MP Sony OIS triple camera, and 100W SuperVOOC flash charging with 5000mAh battery.',
                'base_price' => 2999900, // ₹29,999
                'compare_at_price' => 3799900,
                'gst_rate' => 18,
                'hsn_code' => '851713',
                'sku' => 'NEX-5G-256',
                'brand' => 'NexaTech Prime',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 40,
                'is_featured' => true,
                'custom_fields' => ['RAM' => '12GB LPDDR5X', 'Storage' => '256GB UFS 4.0', 'Camera' => '50MP Sony IMX890 OIS', 'Battery' => '5000mAh with 100W Fast Charger'],
                'images' => [
                    'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 6. Industrial Machinery -> Apex Industrial Diamond Tools
            [
                'vendor_slug' => 'apex-power-tools',
                'category_id' => $catMachinery->id,
                'slug' => '400mm-laser-welded-diamond-saw-blade-marble-cutting',
                'title' => '400mm Laser-Welded Diamond Saw Blade for Marble & Granite Cutting',
                'description' => 'Industrial-grade segmented diamond circular cutting blade for high-speed bridge saws. Laser-welded cobalt-bronze matrix segments ensure vibration-free, chip-free cuts through hard Makrana marble and granites.',
                'base_price' => 485000, // ₹4,850
                'compare_at_price' => 620000,
                'gst_rate' => 18,
                'hsn_code' => '820239',
                'sku' => 'BLD-DIA-400',
                'brand' => 'Apex Diamond Pro',
                'vertical' => 'enquiry',
                'listing_type' => 'purchase',
                'total_stock' => 45,
                'is_featured' => true,
                'custom_fields' => ['Diameter' => '400mm (16 inch)', 'Bore Size' => '50mm / 60mm', 'Segment Height' => '15mm Premium Diamond', 'Speed' => 'Max 3000 RPM'],
                'images' => [
                    'https://images.unsplash.com/photo-1572981779307-38b8cabb2407?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 7. Home Living / Marble -> Royal Makrana Marbles
            [
                'vendor_slug' => 'royal-makrana-marble',
                'category_id' => $catHomeMarble->id,
                'slug' => 'makrana-pure-white-marble-slabs',
                'title' => 'Makrana Pure White Marble Slabs (Diamond Cut Premium 18mm)',
                'description' => 'World-famous authentic Makrana white marble quarried directly from Rajasthan. Renowned for its brilliant calcium purity, mirror gloss luster, and zero discoloration over centuries. Ideal for luxury residential villa flooring and pooja rooms.',
                'base_price' => 4500000, // ₹45,000 for 100 sq ft lot
                'compare_at_price' => 5800000,
                'gst_rate' => 18,
                'hsn_code' => '680221',
                'sku' => 'MAK-WHT-100',
                'brand' => 'Makrana Heritage',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 50,
                'is_featured' => true,
                'custom_fields' => ['Origin' => 'Makrana, Rajasthan', 'Thickness' => '18mm', 'Finish' => 'High Mirror Polish', 'Application' => 'Villa Flooring & Foyers'],
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 8. On-Demand Services -> Diamond Gloss Polishing
            [
                'vendor_slug' => 'diamond-gloss-polishing',
                'category_id' => $catServices->id,
                'slug' => 'italian-makrana-marble-mirror-polish-service',
                'title' => 'Italian & Makrana Marble Mirror-Polish & Crystallization Service',
                'description' => 'Professional doorstep stone floor rejuvenation using heavy Italian Klindex grinding machines, 7-grit diamond resin pads, and chemical crystallization for 95+ gloss unit reflections.',
                'base_price' => 450000, // ₹4,500 base service deposit
                'compare_at_price' => 600000,
                'gst_rate' => 18,
                'hsn_code' => '9987',
                'sku' => 'SRV-POL-100',
                'brand' => 'Diamond Gloss Pro',
                'vertical' => 'services',
                'listing_type' => 'service',
                'total_stock' => 999,
                'is_featured' => true,
                'custom_fields' => ['Coverage' => 'Up to 500 Sq. Ft.', 'Duration' => '1 Full Day Service', 'Equipment' => 'Italian Planetary Grinder', 'Warranty' => '2-Year Shine Guarantee'],
                'images' => [
                    'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 9. Digital Services -> CodeCraft Digital
            [
                'vendor_slug' => 'codecraft-digital',
                'category_id' => $catDigital->id,
                'slug' => 'enterprise-saas-web-application-ui-ux-design-system',
                'title' => 'Full-Stack SaaS Web Application & Mobile App UI/UX Design System',
                'description' => 'End-to-end product design and engineering sprint. Includes full design tokens in Figma, responsive Tailwind CSS layouts, scalable Laravel/Node API architecture, and production CI/CD setup.',
                'base_price' => 1499900, // ₹14,999
                'compare_at_price' => 2499900,
                'gst_rate' => 18,
                'hsn_code' => '998314',
                'sku' => 'DIG-SAAS-SPRINT',
                'brand' => 'CodeCraft Agency',
                'vertical' => 'services',
                'listing_type' => 'gig',
                'total_stock' => 10,
                'is_featured' => true,
                'custom_fields' => ['Deliverables' => 'Figma Components, Clean Code, API Spec', 'Delivery Days' => '10 Business Days', 'Revisions' => 'Unlimited Sprints', 'Tech Stack' => 'Laravel, React, Tailwind, MySQL'],
                'images' => [
                    'https://images.unsplash.com/photo-1507238691740-187a5b1d37b8?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 10. Pre-Loved / Used -> Heritage Watches
            [
                'vendor_slug' => 'heritage-timepieces',
                'category_id' => $catUsed->id,
                'slug' => 'vintage-1984-seiko-turtle-automatic-diver-watch',
                'title' => 'Vintage 1984 Seiko 6309-7040 "Turtle" Automatic Diver Watch',
                'description' => 'Collector-grade legendary cushion-case diver watch in all-original condition. Authentic Japan 6309 automatic movement recently cleaned and regulated. Bidirectional click bezel, tritium patina dial, and original rubber dive strap.',
                'base_price' => 1850000, // ₹18,500
                'compare_at_price' => 2400000,
                'gst_rate' => 12,
                'hsn_code' => '910211',
                'sku' => 'SEI-TRT-1984',
                'brand' => 'Seiko Vintage Horology',
                'vertical' => 'used',
                'listing_type' => 'classified',
                'total_stock' => 1,
                'is_featured' => true,
                'is_negotiable' => true,
                'custom_fields' => ['Year' => '1984', 'Movement' => 'Seiko 6309 Automatic 17 Jewels', 'Case' => '44mm Stainless Steel Cushion', 'Condition' => 'Excellent Vintage (Grade A)'],
                'images' => [
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 11. Real Estate -> Royal Heritage Realty
            [
                'vendor_slug' => 'royal-heritage-realty',
                'category_id' => $catRealEstate->id,
                'slug' => '3bhk-luxury-gated-high-rise-apartment-skyline-view',
                'title' => '3 BHK Luxury Gated High-Rise Apartment with Panoramic Skyline View (RERA Approved)',
                'description' => 'Architecturally designed 2,450 sq ft luxury residence on the 18th floor with uninterrupted Aravali skyline vistas. Italian marble flooring, VRV central air-conditioning, private clubhouse, infinity swimming pool, and 3 covered car parks.',
                'base_price' => 850000000, // ₹85,00,000
                'compare_at_price' => 950000000,
                'gst_rate' => 5,
                'hsn_code' => '9972',
                'sku' => 'REA-JPR-3BHK',
                'brand' => 'Royal Heritage Realty',
                'vertical' => 'enquiry',
                'listing_type' => 'quote_only',
                'total_stock' => 4,
                'is_featured' => true,
                'is_negotiable' => true,
                'custom_fields' => ['Configuration' => '3 BHK + Servant Room', 'Carpet Area' => '2,450 Sq. Ft.', 'Possession' => 'Ready to Move', 'RERA No.' => 'RAJ/P/2024/1184'],
                'images' => [
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 12. Automotive -> SpeedCraft Automotive
            [
                'vendor_slug' => 'speedcraft-auto-spares',
                'category_id' => $catAuto->id,
                'slug' => 'fully-synthetic-5w-40-performance-engine-oil-4l',
                'title' => 'Fully Synthetic 5W-40 Performance Engine Oil (4L Cans)',
                'description' => 'Premium German-engineered high-shear stability motor oil for turbocharged petrol and diesel engines. Advanced titanium fluid strength reduces friction, extends drain intervals up to 15,000 km, and protects under extreme thermal loads.',
                'base_price' => 249900, // ₹2,499
                'compare_at_price' => 320000,
                'gst_rate' => 18,
                'hsn_code' => '271019',
                'sku' => 'OIL-SYN-5W40',
                'brand' => 'SpeedCraft Performance',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 60,
                'is_featured' => true,
                'custom_fields' => ['Grade' => 'SAE 5W-40 Fully Synthetic', 'Specifications' => 'API SP / ACEA A3/B4', 'Capacity' => '4 Litres Bottle', 'Compatibility' => 'Cars, SUVs & Turbo Fleets'],
                'images' => [
                    'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 13. Beauty & Wellness -> Vedic Glow Ayurvedic
            [
                'vendor_slug' => 'vedic-glow-organics',
                'category_id' => $catBeauty->id,
                'slug' => 'kumkumadi-tailam-pure-saffron-miracle-face-oil',
                'title' => 'Kumkumadi Tailam 100% Pure Saffron Miracle Face Oil (30ml)',
                'description' => 'Ancient Ayurvedic beauty formulation crafted with Kashmiri saffron, sandalwood, vetiver, and 26 Himalayan therapeutic herbs cooked in cold-pressed sesame oil. Brightens skin, fades hyperpigmentation, and imparts a natural golden glow.',
                'base_price' => 99900, // ₹999
                'compare_at_price' => 149900,
                'gst_rate' => 12,
                'hsn_code' => '330499',
                'sku' => 'VED-KUM-30ML',
                'brand' => 'Vedic Glow Ayurveda',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 75,
                'is_featured' => true,
                'custom_fields' => ['Volume' => '30ml Glass Dropper', 'Ingredients' => 'Kashmiri Mogra Saffron, Sandalwood, Manjistha', 'Skin Type' => 'All Skin Types', 'Cruelty Free' => '100% Ayurvedic Herbals'],
                'images' => [
                    'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // 14. Sports & Gear -> ProActive Sports
            [
                'vendor_slug' => 'proactive-sports',
                'category_id' => $catSports->id,
                'slug' => 'grade-1-english-willow-handcrafted-cricket-bat',
                'title' => 'Grade-1 English Willow Handcrafted Cricket Bat (Embossed Grip)',
                'description' => 'Mastercraft tournament-spec cricket bat cleft from unbleached Grade-1 English willow. Full profile with thick 40mm edges, balanced mid-swell sweet spot, and pre-knocked toe guard with multi-piece cane handle for explosive power.',
                'base_price' => 649900, // ₹6,499
                'compare_at_price' => 899900,
                'gst_rate' => 12,
                'hsn_code' => '950699',
                'sku' => 'BAT-ENG-GR1',
                'brand' => 'ProActive Willow',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 20,
                'is_featured' => true,
                'custom_fields' => ['Willow' => 'Selected Grade 1 English Willow', 'Weight' => '1,180 grams (Light Pickup)', 'Grains' => '8-10 Straight Natural Grains', 'Handle' => 'Singapore Full Cane Semi-Oval'],
                'images' => [
                    'https://images.unsplash.com/photo-1624526267942-ab0ff8a3e972?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
        ];

        $productMap = [];
        foreach ($productsData as $p) {
            $vendor = $vendorMap[$p['vendor_slug']] ?? null;
            if (!$vendor) continue;

            $product = Product::updateOrCreate(
                ['slug' => $p['slug']],
                [
                    'vendor_id' => $vendor->id,
                    'category_id' => $p['category_id'],
                    'title' => $p['title'],
                    'description' => $p['description'],
                    'base_price' => $p['base_price'],
                    'compare_at_price' => $p['compare_at_price'] ?? null,
                    'gst_rate' => $p['gst_rate'] ?? 18,
                    'hsn_code' => $p['hsn_code'] ?? null,
                    'sku' => $p['sku'] ?? strtoupper(Str::random(8)),
                    'brand' => $p['brand'] ?? $vendor->shop_name,
                    'vertical' => $p['vertical'] ?? 'marketplace',
                    'listing_type' => $p['listing_type'] ?? 'purchase',
                    'total_stock' => $p['total_stock'] ?? 10,
                    'status' => 'active',
                    'is_featured' => $p['is_featured'] ?? false,
                    'is_negotiable' => $p['is_negotiable'] ?? false,
                    'custom_fields' => $p['custom_fields'] ?? null,
                    'country_of_origin' => 'India',
                    'published_at' => now(),
                ]
            );

            // Populate Product Images
            ProductImage::where('product_id', $product->id)->delete();
            foreach ($p['images'] as $idx => $imgUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $imgUrl,
                    'alt_text' => $product->title . ' - View ' . ($idx + 1),
                    'sort_order' => $idx,
                ]);
            }

            // Create default variant for direct cart purchases
            ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'sku' => 'Default'],
                [
                    'price_override' => $product->base_price,
                    'stock' => $product->total_stock,
                    'is_active' => true,
                ]
            );

            $productMap[$p['slug']] = $product;
        }

        // 6. Seed Shoppable Shorts / Reels (Live video commerce)
        $shortsData = [
            [
                'slug' => 'cutting-makrana-marble-slabs-factory',
                'vendor_slug' => 'royal-makrana-marble',
                'product_slug' => 'makrana-pure-white-marble-slabs',
                'title' => 'Live Diamond Saw Cutting Makrana White Marble Slabs',
                'description' => 'Watch raw marble blocks from Makrana quarry get water-jet cut into 18mm mirror-polish flooring slabs.',
                'video_url' => '/videos/shorts/short6.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=400&q=80',
                'views' => 14250,
                'likes' => 1120,
                'trending_score' => 240.5,
            ],
            [
                'slug' => 'unsealing-awadhi-dum-biryani-handi',
                'vendor_slug' => 'nawabi-dastarkhwan',
                'product_slug' => 'slow-cooked-awadhi-dum-mutton-biryani',
                'title' => 'Unsealing 4-Hour Dum Awadhi Mutton Biryani Clay Handi',
                'description' => 'Breaking the dough seal on our steaming clay handi dum biryani. Fragrant saffron rice, falling-off-the-bone tender meat.',
                'video_url' => '/videos/shorts/short2.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=400&q=80',
                'views' => 48200,
                'likes' => 4560,
                'trending_score' => 520.0,
            ],
            [
                'slug' => 'weaving-pure-katan-silk-banarasi-saree',
                'vendor_slug' => 'kavita-couture',
                'product_slug' => 'banarasi-kadwa-pure-katan-silk-saree',
                'title' => 'Weaving Pure Katan Silk Banarasi Saree on Handloom',
                'description' => 'Master artisan weaving real gold and silver zari threads on traditional handloom. 30 days of heritage craftsmanship in one drape.',
                'video_url' => '/videos/shorts/short1.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=400&q=80',
                'views' => 29100,
                'likes' => 3120,
                'trending_score' => 380.0,
            ],
            [
                'slug' => 'mirror-polish-italian-marble-timelapse',
                'vendor_slug' => 'diamond-gloss-polishing',
                'product_slug' => 'italian-makrana-marble-mirror-polish-service',
                'title' => 'Mirror-Finish Italian Marble Polishing Time-Lapse',
                'description' => 'Watch dull, scratched marble floors transform into a high-gloss 95+ unit mirror reflection with our diamond crystallization.',
                'video_url' => '/videos/shorts/short6.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=400&q=80',
                'views' => 61000,
                'likes' => 9810,
                'trending_score' => 310.0,
            ],
        ];

        foreach ($shortsData as $s) {
            $v = $vendorMap[$s['vendor_slug']] ?? null;
            if (!$v) continue;

            $short = Short::updateOrCreate(
                ['slug' => $s['slug']],
                [
                    'vendor_id' => $v->id,
                    'category_id' => $catHomeMarble->id,
                    'title' => $s['title'],
                    'description' => $s['description'],
                    'video_url' => $s['video_url'],
                    'thumb_url' => $s['thumb_url'],
                    'status' => 'published',
                    'is_featured' => true,
                    'is_boosted' => true,
                    'views' => $s['views'],
                    'likes' => $s['likes'],
                    'duration' => 30,
                    'trending_score' => $s['trending_score'],
                ]
            );

            if (isset($productMap[$s['product_slug']])) {
                $short->products()->syncWithoutDetaching([$productMap[$s['product_slug']]->id]);
            }
        }

        // 7. Seed Authentic Customer Reviews
        $reviewsData = [
            [
                'product_slug' => 'makrana-pure-white-marble-slabs',
                'user_name' => 'Rajesh Agrawal',
                'rating' => 5,
                'title' => 'Flawless Makrana Quality & Smooth Delivery to Jaipur',
                'comment' => 'Ordered 2,200 sq ft for our new residence in Jaipur. The slabs came perfectly packed in wooden crates without a single crack. After diamond polishing, the gloss is breathtaking. Highly recommend Royal Makrana!',
            ],
            [
                'product_slug' => 'slow-cooked-awadhi-dum-mutton-biryani',
                'user_name' => 'Dr. Sameer Siddiqui',
                'rating' => 5,
                'title' => 'Best authentic Awadhi Biryani I have tasted outside Lucknow!',
                'comment' => 'The clay handi arrived piping hot and tightly sealed. Meat was falling off the bone and the aroma of saffron and whole spices was pure nostalgia.',
            ],
            [
                'product_slug' => 'banarasi-kadwa-pure-katan-silk-saree',
                'user_name' => 'Pooja Kashyap',
                'rating' => 5,
                'title' => 'Pure heirloom quality silk saree!',
                'comment' => 'Bought this for my sister wedding reception. The real silver zari sheen is subtle yet royal. Silk Mark tag verified. 10/10 craftsmanship.',
            ],
            [
                'product_slug' => 'pure-gir-cow-a2-bilona-desi-ghee-1l',
                'user_name' => 'Ananya Sharma',
                'rating' => 5,
                'title' => 'Genuine golden granular ghee with real village aroma',
                'comment' => 'You can smell the authenticity the moment you open the jar. No chemicals or artificial yellow coloring. Pure A2 bilona bliss.',
            ],
        ];

        foreach ($reviewsData as $r) {
            $p = $productMap[$r['product_slug']] ?? null;
            if (!$p) continue;

            Review::updateOrCreate(
                ['product_id' => $p->id, 'user_id' => $buyer->id],
                [
                    'rating' => $r['rating'],
                    'title' => $r['title'],
                    'body' => $r['comment'],
                    'is_approved' => true,
                    'is_verified_purchase' => true,
                ]
            );
        }

        // 8. Seed Welcome Promo Coupon
        \App\Models\Coupon::updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'vendor_id' => null, // Global
                'type' => 'percentage',
                'value' => 10,
                'min_cart_value' => 50000, // min ₹500
                'starts_at' => now()->subDay(),
                'expires_at' => now()->addYear(),
                'is_active' => true,
            ]
        );

        // 9. Seed Authentic Active Buyer RFQs & Leads (B2B Marketplace)
        $rfqsData = [
            [
                'enquiry_number' => 'RFQ-2026-0081',
                'category_id' => $catHomeMarble->id,
                'title' => 'Bulk Sourcing: 25,000 Sq.Ft Premium Makrana White Slabs for 5-Star Hotel Project',
                'description' => 'Requirement for pure white crystalline Makrana marble slabs (18mm thickness, book-matched). Delivery needed on site in Jaipur within 45 days. Vendor must be GST registered with mining gate passes.',
                'quantity' => '25,000 Sq. Ft.',
                'target_budget' => 2000000.00,
                'city' => 'Jaipur',
                'pincode' => '302001',
                'urgency' => 'immediate',
                'buyer_name' => 'Rajputana Grand Hospitality Ltd',
                'buyer_phone' => '+91 98290 11223',
                'buyer_email' => 'procurement@rajputanagrand.com',
                'status' => 'open',
                'quotes_count' => 3,
            ],
            [
                'enquiry_number' => 'RFQ-2026-0082',
                'category_id' => $catMachinery->id,
                'title' => 'Supply of 500 MT Fe-550D TMT Rebars (8mm to 32mm) for Residential High-Rise',
                'description' => 'Looking for primary steel plant rolling (Tata/JSW/Jindal equivalent test certs mandatory). Staggered 100 MT monthly deliveries to site near Hinjewadi Phase 3, Pune.',
                'quantity' => '500 Metric Tons',
                'target_budget' => 32500000.00,
                'city' => 'Pune',
                'pincode' => '411057',
                'urgency' => 'within_month',
                'buyer_name' => 'Skyline Heights Infra Ventures',
                'buyer_phone' => '+91 94220 55667',
                'buyer_email' => 'purchase@skylineheights.in',
                'status' => 'open',
                'quotes_count' => 2,
            ],
            [
                'enquiry_number' => 'RFQ-2026-0083',
                'category_id' => $catAuto->id,
                'title' => 'Urgent: 2,500 Sets Heavy Duty Ventilated Brake Discs & Semi-Metallic Pads OEM Spec',
                'description' => 'Required for fleet overhaul. Must meet ISO/TS 16949 automotive manufacturing standards. Sample inspection required before full lot dispatch.',
                'quantity' => '2,500 Sets',
                'target_budget' => 1250000.00,
                'city' => 'Gurugram',
                'pincode' => '122016',
                'urgency' => 'immediate',
                'buyer_name' => 'SwiftWheels Logistics Fleet',
                'buyer_phone' => '+91 98110 33445',
                'buyer_email' => 'spares@swiftwheelsfleet.com',
                'status' => 'open',
                'quotes_count' => 4,
            ],
            [
                'enquiry_number' => 'RFQ-2026-0084',
                'category_id' => $catMachinery->id,
                'title' => 'Industrial Diamond Gang Saw Blades & Segmented Polishing Frankfurts (60 Sets)',
                'description' => 'Seeking direct manufacturer pricing for heavy marble block cutting blades. High concentration cobalt bond suitable for hard crystalline marble.',
                'quantity' => '60 Sets',
                'target_budget' => 480000.00,
                'city' => 'Kishangarh',
                'pincode' => '305801',
                'urgency' => 'flexible',
                'buyer_name' => 'Marwar Stone Processors Association',
                'buyer_phone' => '+91 97840 88990',
                'buyer_email' => 'tenders@marwarstone.org',
                'status' => 'open',
                'quotes_count' => 1,
            ],
        ];

        foreach ($rfqsData as $rfq) {
            Enquiry::updateOrCreate(
                ['enquiry_number' => $rfq['enquiry_number']],
                [
                    'user_id' => $buyer->id,
                    'category_id' => $rfq['category_id'],
                    'title' => $rfq['title'],
                    'description' => $rfq['description'],
                    'quantity' => $rfq['quantity'],
                    'target_budget' => $rfq['target_budget'],
                    'city' => $rfq['city'],
                    'pincode' => $rfq['pincode'],
                    'urgency' => $rfq['urgency'],
                    'buyer_name' => $rfq['buyer_name'],
                    'buyer_phone' => $rfq['buyer_phone'],
                    'buyer_email' => $rfq['buyer_email'],
                    'status' => $rfq['status'],
                    'quotes_count' => $rfq['quotes_count'],
                ]
            );
        }

        // 13. K71 Shoppable Video Commerce (Verified 9:16 Shorts with Catalog Product Tags)
        $this->call(RealShoppableShortsSeeder::class);
    }
}
