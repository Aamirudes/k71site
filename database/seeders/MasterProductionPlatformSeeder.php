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
        // 1. Roles Check
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $vendorRole = Role::firstOrCreate(['name' => 'vendor']);
        $customerRole = Role::firstOrCreate(['name' => 'customer']);

        // 2. Resolve conflicting usernames on existing accounts
        $conflictHashir = User::where('username', 'hashir')->where('email', '!=', 'crab.hashir@gmail.com')->first();
        if ($conflictHashir) {
            $conflictHashir->update(['username' => 'user_' . $conflictHashir->id]);
        }
        $conflictBuyer = User::where('username', 'k71buyer')->where('email', '!=', 'buyer@k71.in')->first();
        if ($conflictBuyer) {
            $conflictBuyer->update(['username' => 'user_' . $conflictBuyer->id]);
        }

        // Master Admin Enforced
        $admin = User::firstOrCreate(
            ['email' => 'crab.hashir@gmail.com'],
            [
                'first_name' => 'Hashir',
                'last_name' => 'Admin',
                'username' => 'hashir',
                'password' => Hash::make('Hashir1284@0'),
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

        // Demo Buyer Account
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

        // 3. Define 9 Production Vendors
        $vendorsData = [
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
            [
                'email' => 'stonecraft@k71.in',
                'name' => 'Rameshwar Sharma',
                'username' => 'stonecraftjalis',
                'slug' => 'stonecraft-architecture',
                'shop_name' => 'StoneCraft Mandir Architecture & Jalis',
                'vendor_type' => 'services',
                'city' => 'Makrana',
                'state' => 'Rajasthan',
                'pincode' => '341505',
                'address' => 'Choudhary Market, Bypass Road, Makrana',
                'phone' => '+91 98291 56789',
                'description' => 'Handcrafted white marble mandirs, intricate stone carved jalis, temple pillars, stone bathtubs, and custom residential water fountains.',
                'colour_palette' => 'slate',
                'layout_preset' => 'minimal',
                'product_preset' => 'compact',
                'upi_id' => 'stonecraft@upi',
                'logo' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1600&q=80',
            ],
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
                'description' => 'Direct-from-farm Daawat aged Basmati rice, wood-pressed mustard oils, Gir Cow A2 Desi Ghee, Kashmiri saffron, and premium California almonds.',
                'colour_palette' => 'sage',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'bansalkirana@okaxis',
                'logo' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1600&q=80',
            ],
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
                'description' => 'Artisan handcrafted genuine leather Kolhapuri chappals, classic brogues, and ethnic juttis with cushioned memory foam soles.',
                'colour_palette' => 'amber',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'royalfootwear@upi',
                'logo' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1472851294608-062f824d29cc?auto=format&fit=crop&w=1600&q=80',
            ],
            [
                'email' => 'tools@k71.in',
                'name' => 'Bhavesh Patel',
                'username' => 'bhaveshtools',
                'slug' => 'apex-power-tools',
                'shop_name' => 'Apex Industrial Diamond Tools & Machinery',
                'vendor_type' => 'marketplace',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '380002',
                'address' => 'GIDC Estate, Vatva, Ahmedabad',
                'phone' => '+91 98250 11223',
                'description' => 'Heavy-duty bridge cutting saws, diamond floor polishing pads, marble edge bevelers, and industrial dust vacuums.',
                'colour_palette' => 'cobalt',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'apextools@upi',
                'logo' => 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1600&q=80',
            ],
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
                'description' => 'Certified OEM replacement spares, synthetic motor lubricants, ceramic brake systems, and performance tuning accessories for 2-wheelers and 4-wheelers.',
                'colour_palette' => 'cobalt',
                'layout_preset' => 'grid',
                'product_preset' => 'standard',
                'upi_id' => 'speedcraft@upi',
                'logo' => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1600&q=80',
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
                    'terms_accepted_at' => now(),
                ]);
            } else {
                $user->update([
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

        // 4. Resolve Category Helpers
        $catMarble = Category::where('slug', 'building-renovation-materials')->orWhere('slug', 'enquiry-marble-granite')->first()
            ?? Category::firstOrCreate(['slug' => 'marble-granite-slabs'], ['name' => 'Marble & Granite Slabs', 'is_active' => true]);
        
        $catKirana = Category::where('slug', 'staples-cooking-essentials')->orWhere('slug', 'groceries-daily-essentials-fmcg')->first()
            ?? Category::firstOrCreate(['slug' => 'kirana-organic-staples'], ['name' => 'Kirana & Organic Staples', 'is_active' => true]);
        
        $catFood = Category::where('slug', 'food-dining')->orWhere('slug', 'artisan-biryanis')->first()
            ?? Category::firstOrCreate(['slug' => 'awadhi-biryanis-meals'], ['name' => 'Awadhi Biryanis & Meals', 'is_active' => true]);
        
        $catFashion = Category::where('slug', 'clothing-fashion-accessories')->orWhere('slug', 'handloom-sarees')->first()
            ?? Category::firstOrCreate(['slug' => 'handloom-sarees-couture'], ['name' => 'Handloom Sarees & Couture', 'is_active' => true]);

        $catFootwear = Category::where('slug', 'footwear')->first()
            ?? Category::firstOrCreate(['slug' => 'artisan-leather-footwear'], ['name' => 'Artisan Leather Footwear', 'is_active' => true]);

        $catTools = Category::where('slug', 'tools-hardware')->orWhere('slug', 'heavy-industrial-machinery-construction')->first()
            ?? Category::firstOrCreate(['slug' => 'industrial-stone-tools'], ['name' => 'Industrial Stone Tools & Machinery', 'is_active' => true]);

        $catServices = Category::where('slug', 'services-home-repairs')->orWhere('slug', 'professional-services')->first()
            ?? Category::firstOrCreate(['slug' => 'floor-polishing-services'], ['name' => 'Floor Polishing & Restoration', 'is_active' => true]);

        $catUsed = Category::where('slug', 'pre-owned-watches')->orWhere('slug', 'pre-loved-used-goods-thrift')->first()
            ?? Category::firstOrCreate(['slug' => 'vintage-watches-collectibles'], ['name' => 'Vintage Watches & Horology', 'is_active' => true]);

        $catRealEstate = Category::where('slug', 'like', '%residential%')->orWhere('slug', 'like', '%real-estate%')->first()
            ?? Category::firstOrCreate(['slug' => 'luxury-residential-properties'], ['name' => 'Residential Real Estate & Villas', 'vertical' => 'enquiry', 'is_active' => true]);

        $catAuto = Category::where('slug', 'like', '%automotive%')->orWhere('slug', 'like', '%auto%')->first()
            ?? Category::firstOrCreate(['slug' => 'automotive-spares-parts'], ['name' => 'Automotive Spares & Fluids', 'vertical' => 'marketplace', 'is_active' => true]);

        // 5. Products Definition (Rich, Alive, Diverse)
        $productsData = [
            // --- MARBLE & GRANITE ---
            [
                'vendor_slug' => 'royal-makrana-marble',
                'category_id' => $catMarble->id,
                'slug' => 'makrana-pure-white-marble-slabs',
                'title' => 'Makrana Pure White Marble Slabs (Diamond Cut Premium)',
                'description' => 'World-famous authentic Makrana white marble quarried directly from Rajasthan. Renowned for its brilliant calcium purity, mirror gloss luster, and zero discoloration over centuries. Ideal for luxury residential villa flooring, pooja rooms, and grand entrance foyers.',
                'base_price' => 4500000, // ₹45,000 for 100 sq ft lot (₹450/sq ft)
                'compare_at_price' => 5800000,
                'gst_rate' => 18,
                'hsn_code' => '680221',
                'sku' => 'MAK-WHT-100',
                'brand' => 'Makrana Heritage',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 50,
                'is_featured' => true,
                'custom_fields' => ['Origin' => 'Makrana, Rajasthan', 'Thickness' => '18mm', 'Finish' => 'High Mirror Polish', 'Application' => 'Living Room & Pooja Room Flooring'],
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'royal-makrana-marble',
                'category_id' => $catMarble->id,
                'slug' => 'italian-statuario-marble-bookmatched',
                'title' => 'Italian Statuario Marble Slabs (Bookmatched Pair)',
                'description' => 'Exclusive imported Italian Statuario marble with bold, dramatic grey veins flowing across a crystalline white background. Meticulously cut into matching bookmatched slab pairs for seamless pattern symmetry on statement walls and master suites.',
                'base_price' => 12000000, // ₹1,20,000 for 100 sq ft (₹1200/sq ft)
                'compare_at_price' => 14500000,
                'gst_rate' => 18,
                'hsn_code' => '680221',
                'sku' => 'ITA-STAT-200',
                'brand' => 'Italian Royal Slabs',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 25,
                'is_featured' => true,
                'custom_fields' => ['Origin' => 'Carrara, Italy', 'Thickness' => '20mm', 'Pattern' => 'Bookmatch Flow', 'Finish' => 'Epoxy Polish'],
                'images' => [
                    'https://images.unsplash.com/photo-1590381105924-c72589b9ef3f?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'royal-makrana-marble',
                'category_id' => $catMarble->id,
                'slug' => 'rajasthan-black-galaxy-granite-slabs',
                'title' => 'Rajasthan Black Galaxy Granite Slabs (Gold Flecks)',
                'description' => 'Dense volcanic granite with deep midnight black stone peppered with natural metallic golden bronzite flecks. Highly scratch-resistant, heat-proof, and impervious to kitchen stains. Perfect for kitchen countertops, stairs, and vanity tops.',
                'base_price' => 2200000, // ₹22,000 for 100 sq ft (₹220/sq ft)
                'compare_at_price' => 2800000,
                'gst_rate' => 18,
                'hsn_code' => '680223',
                'sku' => 'BLK-GAL-100',
                'brand' => 'Rajasthan Stone Guild',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 80,
                'is_featured' => true,
                'custom_fields' => ['Origin' => 'Chittoor / Rajasthan', 'Thickness' => '18mm', 'Finish' => 'Mirror Gloss Chamfered', 'Application' => 'Kitchen Countertops & Steps'],
                'images' => [
                    'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'royal-makrana-marble',
                'category_id' => $catMarble->id,
                'slug' => 'katni-beige-marble-tiles-box',
                'title' => 'Katni Beige Marble Tiles (600x600mm Box of 4)',
                'description' => 'Warm cream beige Indian marble tiles cut with precision diamond blades. Calibrated to uniform 16mm thickness with chamfered edges for fast, seamless installation in living areas and corridors.',
                'base_price' => 185000, // ₹1,850 per box
                'compare_at_price' => 240000,
                'gst_rate' => 18,
                'sku' => 'KAT-BEI-600',
                'brand' => 'Katni Stones',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 120,
                'is_featured' => false,
                'custom_fields' => ['Tile Size' => '600x600 mm', 'Box Coverage' => '15.5 sq ft', 'Finish' => 'Honed & Polished'],
                'images' => [
                    'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'stonecraft-architecture',
                'category_id' => $catMarble->id,
                'slug' => 'hand-carved-white-marble-mandir-temple',
                'title' => 'Hand-Carved Makrana White Marble Mandir (4 Feet Home Temple)',
                'description' => 'Exquisitely hand-chiseled temple made from pure Makrana marble by generational sculptors. Features intricately carved shikhar domes, peacock motifs, engraved pillars, and pull-out prasad trays.',
                'base_price' => 4800000, // ₹48,000
                'compare_at_price' => 6200000,
                'gst_rate' => 12,
                'sku' => 'TEMPLE-MAK-4FT',
                'brand' => 'StoneCraft Mandir',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 8,
                'is_featured' => true,
                'custom_fields' => ['Height' => '48 inches', 'Width' => '30 inches', 'Material' => '100% Makrana White Marble', 'Assembly' => 'Pre-Assembled Doorstep Delivery'],
                'images' => [
                    'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'stonecraft-architecture',
                'category_id' => $catMarble->id,
                'slug' => 'architectural-marble-jali-screen-panel',
                'title' => 'Architectural Geometric Marble Jali Screen Panel (3x2 Feet)',
                'description' => 'Precision water-jet and hand-finished marble jali for modern luxury partitions, balcony railings, facade ventilation, and prayer room dividers.',
                'base_price' => 850000, // ₹8,500
                'compare_at_price' => 1100000,
                'gst_rate' => 18,
                'sku' => 'JALI-MAK-3X2',
                'brand' => 'StoneCraft Jalis',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 15,
                'is_featured' => false,
                'custom_fields' => ['Dimensions' => '36 x 24 inches', 'Thickness' => '25mm', 'Design' => 'Moroccan Lattice'],
                'images' => [
                    'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- KIRANA & ORGANIC FOOD ---
            [
                'vendor_slug' => 'bansal-organic-kirana',
                'category_id' => $catKirana->id,
                'slug' => 'daawat-gold-aged-basmati-rice-5kg',
                'title' => 'Daawat Rozana Gold Aged Extra-Long Basmati Rice (5 Kg Bag)',
                'description' => 'Naturally aged for two years in Himalayan foothills. Grains elongate up to 24mm upon cooking with delicate nutty fragrance and non-sticky fluffy texture.',
                'base_price' => 62000, // ₹620
                'compare_at_price' => 75000,
                'gst_rate' => 0,
                'sku' => 'RICE-BAS-5KG',
                'brand' => 'Daawat Heritage',
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'total_stock' => 200,
                'is_featured' => true,
                'custom_fields' => ['Grain Length' => 'Aged Extra-Long', 'Dietary' => 'Vegetarian / Gluten-Free', 'Weight' => '5 Kg'],
                'images' => [
                    'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'bansal-organic-kirana',
                'category_id' => $catKirana->id,
                'slug' => 'pure-gir-cow-a2-bilona-desi-ghee-1l',
                'title' => 'Pure Organic Gir Cow A2 Bilona Desi Ghee (1 Litre Glass Jar)',
                'description' => 'Prepared using the ancient Vedic Bilona method from curdled whole milk of grass-fed Gir cows. Rich in granular texture, golden aroma, and natural butyric acid.',
                'base_price' => 145000, // ₹1,450
                'compare_at_price' => 180000,
                'gst_rate' => 12,
                'sku' => 'GHEE-GIR-1L',
                'brand' => 'Bansal Vedic Organics',
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'total_stock' => 75,
                'is_featured' => true,
                'custom_fields' => ['Method' => 'Traditional Vedic Wooden Bilona', 'Packaging' => 'UV-Protected Glass Jar', 'Volume' => '1000 ml'],
                'images' => [
                    'https://images.unsplash.com/photo-1628088062854-d1870b4553da?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'bansal-organic-kirana',
                'category_id' => $catKirana->id,
                'slug' => 'grade-a-kashmiri-mongra-saffron-5g',
                'title' => 'Grade-A Kashmiri Mongra Saffron / Kesar (5 Grams Sealed Tin)',
                'description' => 'Direct harvest from Pampore, Kashmir. Pure deep crimson stigmas with high crocin content, delivering intense natural color and celestial aroma in biryanis and sweets.',
                'base_price' => 165000, // ₹1,650
                'compare_at_price' => 210000,
                'gst_rate' => 5,
                'sku' => 'SAFF-KASH-5G',
                'brand' => 'Kashmiri Mongra',
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'total_stock' => 100,
                'is_featured' => false,
                'custom_fields' => ['Harvest' => 'Pampore, J&K', 'Purity' => '100% Mongra Stigma Only', 'Weight' => '5g'],
                'images' => [
                    'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'bansal-organic-kirana',
                'category_id' => $catKirana->id,
                'slug' => 'california-jumbo-almonds-1kg',
                'title' => 'California Jumbo Whole Almonds / Badam (1 Kg Vacuum Pack)',
                'description' => 'Crisp, premium jumbo California almonds. 100% natural, unpolished, cholesterol-free, packed with Vitamin E and healthy omega fatty acids.',
                'base_price' => 89000, // ₹890
                'compare_at_price' => 110000,
                'gst_rate' => 5,
                'sku' => 'ALM-CAL-1KG',
                'brand' => 'Royal Dry Fruits',
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'total_stock' => 150,
                'is_featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1508061253366-f7da158b6d46?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- FOOD & RESTAURANT ---
            [
                'vendor_slug' => 'nawabi-dastarkhwan',
                'category_id' => $catFood->id,
                'slug' => 'slow-cooked-awadhi-dum-mutton-biryani',
                'title' => 'Slow-Cooked Awadhi Dum Mutton Biryani (Sealed Clay Handi)',
                'description' => 'Aged fragrant basmati rice layered with tender marinated meat, saffron, and whole spices, sealed in traditional earthen handi with dough and dum cooked over coal.',
                'base_price' => 64900, // ₹649
                'compare_at_price' => 79900,
                'gst_rate' => 5,
                'sku' => 'BIRYANI-MUTTON-HANDI',
                'brand' => 'Nawabi Dastarkhwan',
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'total_stock' => 40,
                'is_featured' => true,
                'custom_fields' => ['Portion' => 'Serves 2-3', 'Spice Level' => 'Medium Royal', 'Includes' => 'Burani Raita & Salan', 'Packaging' => 'Sealed Terracotta Handi'],
                'images' => [
                    'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1589302168068-964664d93dc0?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'nawabi-dastarkhwan',
                'category_id' => $catFood->id,
                'slug' => 'lucknowi-galouti-kebab-platter',
                'title' => 'Lucknowi Melt-in-Mouth Galouti Kebab (Box of 6 with Ulte Tawe Parathas)',
                'description' => 'Finely minced meat blended with over 160 secret Awadhi spices and raw papaya tenderizer. Pan-grilled on mahi tawa to achieve signature melt-in-the-mouth texture.',
                'base_price' => 42000, // ₹420
                'compare_at_price' => 49900,
                'gst_rate' => 5,
                'sku' => 'KEBAB-GAL-6PC',
                'brand' => 'Nawabi Dastarkhwan',
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'total_stock' => 50,
                'is_featured' => true,
                'custom_fields' => ['Quantity' => '6 Kebabs + 2 Parathas', 'Accompaniments' => 'Mint Chutney & Pickled Onions'],
                'images' => [
                    'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'nawabi-dastarkhwan',
                'category_id' => $catFood->id,
                'slug' => 'shahi-zafrani-tukda-dessert',
                'title' => 'Royal Shahi Zafrani Tukda with Thick Rabdi & Pistachios',
                'description' => 'Golden fried bread steeped in aromatic cardamom saffron syrup, topped with thick reduced malai rabdi and silver vark.',
                'base_price' => 18000, // ₹180
                'compare_at_price' => 22000,
                'gst_rate' => 5,
                'sku' => 'SWEET-SHAHI-TUK',
                'brand' => 'Nawabi Dastarkhwan',
                'vertical' => 'food',
                'listing_type' => 'purchase',
                'total_stock' => 60,
                'is_featured' => false,
                'images' => [
                    'https://images.unsplash.com/photo-1541781774459-bb2af2f05b55?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- FASHION & ATELIER ---
            [
                'vendor_slug' => 'kavita-couture',
                'category_id' => $catFashion->id,
                'slug' => 'banarasi-kadwa-pure-katan-silk-saree',
                'title' => 'Pure Katan Silk Banarasi Kadwa Saree (Real Silver Zari)',
                'description' => 'Authentic Varanasi handloom masterwork in rich royal vermilion silk. Hand-woven Kadwa technique where each floral buta is individually crafted on the loom without loose floats.',
                'base_price' => 1450000, // ₹14,500
                'compare_at_price' => 1850000,
                'gst_rate' => 5,
                'sku' => 'BAN-SILK-001',
                'brand' => 'Kavita Atelier',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 12,
                'is_featured' => true,
                'custom_fields' => ['Fabric' => '100% Pure Katan Silk', 'Weave' => 'Handloom Kadwa', 'Certification' => 'Silk Mark Certified', 'Blouse' => 'Included (Unstitched 80cm)'],
                'images' => [
                    'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'kavita-couture',
                'category_id' => $catFashion->id,
                'slug' => 'lucknowi-chikankari-georgette-kurta-set',
                'title' => 'Hand-Embroidered Lucknowi Chikankari Georgette Kurta Set',
                'description' => 'Ethereal pastel mint green viscose georgette kurta embellished with intricate Bakhiya and Phanda stitches, paired with matching flared palazzo and dupatta.',
                'base_price' => 385000, // ₹3,850
                'compare_at_price' => 520000,
                'gst_rate' => 5,
                'sku' => 'CHK-KURTA-002',
                'brand' => 'Kavita Atelier',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 20,
                'is_featured' => true,
                'custom_fields' => ['Embroidery' => 'Hand Chikankari', 'Inner' => 'Cotton Slip Included', 'Care' => 'Dry Clean Only'],
                'images' => [
                    'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'kavita-couture',
                'category_id' => $catFashion->id,
                'slug' => 'crimson-bridal-velvet-lehenga-choli',
                'title' => 'Bridal Velvet Lehenga Choli in Deep Crimson with Zardozi',
                'description' => 'Opulent micro-velvet bridal lehenga hand-embellished with resham threadwork, zardozi gold bullion, and sequins. Includes dual dupattas and heavy border can-can flare.',
                'base_price' => 2850000, // ₹28,500
                'compare_at_price' => 3600000,
                'gst_rate' => 12,
                'sku' => 'LEH-BRD-003',
                'brand' => 'Kavita Bridal',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 5,
                'is_featured' => true,
                'custom_fields' => ['Lehenga Fabric' => 'Micro Velvet', 'Flare' => '4.2 Meters', 'Work' => 'Zardozi & Dabka'],
                'images' => [
                    'https://images.unsplash.com/photo-1546804784-896d0dca3805?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- FOOTWEAR ---
            [
                'vendor_slug' => 'royal-footwear',
                'category_id' => $catFootwear->id,
                'slug' => 'handcrafted-pure-leather-kolhapuri-chappals',
                'title' => 'Handcrafted Pure Buff Leather Kolhapuri Chappals (Cushioned)',
                'description' => 'Authentic Kolhapuri sandals hand-braided from vegetable-tanned buffalo leather. Upgraded with modern anti-skid TPR base and memory foam heel padding for all-day comfort.',
                'base_price' => 145000, // ₹1,450
                'compare_at_price' => 195000,
                'gst_rate' => 12,
                'sku' => 'KOLH-LTH-01',
                'brand' => 'Kolhapur Guild',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 60,
                'is_featured' => true,
                'custom_fields' => ['Upper Material' => '100% Genuine Leather', 'Sole' => 'Anti-Slip Comfort Bed', 'Color' => 'Cognac Tan'],
                'images' => [
                    'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'royal-footwear',
                'category_id' => $catFootwear->id,
                'slug' => 'goodyear-welted-tan-leather-oxford-brogues',
                'title' => 'Goodyear Welted Tan Leather Oxford Brogues (Formal)',
                'description' => 'Bench-grade executive formal dress shoes crafted from full-grain calfskin leather with precision brogue perforations and stacked leather heel.',
                'base_price' => 395000, // ₹3,950
                'compare_at_price' => 550000,
                'gst_rate' => 18,
                'sku' => 'BROGUE-TAN-02',
                'brand' => 'Royal Heritage Guild',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 35,
                'is_featured' => true,
                'custom_fields' => ['Construction' => 'Goodyear Welted', 'Leather' => 'Full-Grain Calfskin', 'Sole' => 'Compressed Leather'],
                'images' => [
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- INDUSTRIAL TOOLS & MACHINERY ---
            [
                'vendor_slug' => 'apex-power-tools',
                'category_id' => $catTools->id,
                'slug' => 'heavy-duty-3hp-bridge-saw-marble-cutter',
                'title' => 'Heavy-Duty 3HP Bridge Saw Marble & Granite Cutter Machine',
                'description' => 'Industrial precision water-cooled rail cutter for accurate 45-degree mitre cuts and slab sizing on granite, marble, and quartz. Features laser guide alignment and heavy cast-iron table.',
                'base_price' => 6500000, // ₹65,000
                'compare_at_price' => 7800000,
                'gst_rate' => 18,
                'sku' => 'SAW-BRG-3HP',
                'brand' => 'Apex Machinery',
                'vertical' => 'marketplace',
                'listing_type' => 'enquiry',
                'total_stock' => 10,
                'is_featured' => true,
                'custom_fields' => ['Motor Power' => '3.0 HP Copper Core', 'Blade Size' => '350mm Diamond Blade', 'Warranty' => '1 Year Comprehensive On-Site'],
                'images' => [
                    'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'apex-power-tools',
                'category_id' => $catTools->id,
                'slug' => '7-step-wet-dry-diamond-floor-polishing-pads',
                'title' => '7-Step Wet/Dry Diamond Floor Polishing Pads Kit (100mm)',
                'description' => 'Complete set from 50 grit to 3000 grit plus buff pad. Engineered with high-concentration industrial diamond resin for fast polishing and mirror shine on Italian marble and granite.',
                'base_price' => 280000, // ₹2,800
                'compare_at_price' => 360000,
                'gst_rate' => 18,
                'sku' => 'PADS-DIA-7STP',
                'brand' => 'Apex Diamond',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 90,
                'is_featured' => false,
                'custom_fields' => ['Diameter' => '4 inch (100mm)', 'Grits Included' => '50, 100, 200, 400, 800, 1500, 3000', 'Attachment' => 'Velcro Backed'],
                'images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- ON-DEMAND SERVICES ---
            [
                'vendor_slug' => 'diamond-gloss-polishing',
                'category_id' => $catServices->id,
                'slug' => 'italian-marble-diamond-grinding-crystallization',
                'title' => 'Italian Marble Diamond Floor Grinding & Crystallization (Doorstep Service)',
                'description' => 'Comprehensive 7-step planetary machine diamond grinding to remove lippage, deep stains, and scratches, followed by chemical crystallization for a mirror-finish optical reflection.',
                'base_price' => 1450000, // ₹14,500 base package for up to 500 sq ft
                'compare_at_price' => 1800000,
                'gst_rate' => 18,
                'sku' => 'SVC-POLISH-500',
                'brand' => 'Diamond Gloss',
                'vertical' => 'services',
                'listing_type' => 'booking',
                'total_stock' => 25,
                'is_featured' => true,
                'custom_fields' => ['Coverage' => 'Up to 500 Sq Ft', 'Process' => 'Diamond Grinding + Nano Crystallization', 'Duration' => '1 Full Day', 'Equipment' => 'Italian Klindex Planetary Machine'],
                'images' => [
                    'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- PRE-OWNED / COLLECTIBLES ---
            [
                'vendor_slug' => 'heritage-timepieces',
                'category_id' => $catUsed->id,
                'slug' => 'vintage-1984-seiko-automatic-diver-watch',
                'title' => 'Vintage 1984 Automatic Diver Watch (Verified Authentic & Serviced)',
                'description' => 'Collector-grade vintage automatic diver watch with original patinated dial, serviced caliber movement running within ±6s/day, and new pressure-tested sapphire crystal.',
                'base_price' => 3200000, // ₹32,000
                'compare_at_price' => 3800000,
                'gst_rate' => 18,
                'sku' => 'WAT-SEIKO-1984',
                'brand' => 'Seiko Vintage Guild',
                'vertical' => 'used',
                'listing_type' => 'purchase',
                'total_stock' => 1,
                'is_featured' => true,
                'is_negotiable' => true,
                'custom_fields' => ['Year' => '1984', 'Movement' => 'Automatic Mechanical', 'Condition' => 'Excellent Vintage (Serviced)', 'Water Resistance' => '150m Tested'],
                'images' => [
                    'https://images.unsplash.com/photo-1524805444758-089113d48a6d?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1522335789203-aabd1fc54bc9?auto=format&fit=crop&w=1000&q=80',
                ],
            ],
            [
                'vendor_slug' => 'heritage-timepieces',
                'category_id' => $catUsed->id,
                'slug' => 'antique-solid-brass-nautical-marine-sextant',
                'title' => 'Antique Solid Brass Nautical Marine Sextant in Teak Wood Box',
                'description' => 'Authentic vintage maritime navigational sextant with optical telescope, glass filters, and micrometer vernier scale housed in velvet-lined polished teak hardwood case.',
                'base_price' => 750000, // ₹7,500
                'compare_at_price' => 950000,
                'gst_rate' => 18,
                'sku' => 'ANT-SEXTANT-01',
                'brand' => 'Maritime Antiques',
                'vertical' => 'used',
                'listing_type' => 'purchase',
                'total_stock' => 3,
                'is_featured' => false,
                'is_negotiable' => true,
                'images' => [
                    'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- REAL ESTATE ASSETS & PROPERTIES ---
            [
                'vendor_slug' => 'royal-heritage-realty',
                'category_id' => $catRealEstate->id,
                'slug' => '3-bhk-luxury-gated-high-rise-jaipur',
                'title' => '3 BHK Luxury Gated High-Rise Apartment (Vastu Compliant)',
                'description' => 'Sunlit East-facing 3 BHK luxury apartment on 8th floor with wrap-around balconies, modular German kitchen, clubhouse access, swimming pool, and 2 covered car parkings. RERA approved project.',
                'base_price' => 850000000, // ₹85,00,000 in paise
                'compare_at_price' => 920000000,
                'gst_rate' => 5,
                'hsn_code' => '997211',
                'sku' => 'PROP-3BHK-801',
                'brand' => 'DLF Regal Gardens / Godrej',
                'vertical' => 'enquiry',
                'listing_type' => 'enquiry',
                'total_stock' => 4,
                'is_featured' => true,
                'custom_fields' => [
                    'Carpet Area' => '1,650 Sq.Ft (Super Built-up 2,100 Sq.Ft)',
                    'Configuration' => '3 Bedrooms + 3 Bathrooms + 2 Balconies',
                    'Furnishing Status' => 'Semi-Furnished with Modular Kitchen & Wardrobes',
                    'Facing & Floor' => 'North-East Vastu Compliant • 8th Floor of 14',
                    'Possession Status' => 'Ready to Move In (OC & CC Received)',
                    'RERA Registration No' => 'RAJ/RERA/P/2023/1089',
                    'Reserved Parking' => '2 Covered Basement Slots',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1000&q=80',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=1000&q=80',
                ],
            ],

            // --- AUTOMOTIVE OEM SPARES & OILS ---
            [
                'vendor_slug' => 'speedcraft-auto-spares',
                'category_id' => $catAuto->id,
                'slug' => 'fully-synthetic-5w-40-engine-oil-4l',
                'title' => 'Fully Synthetic 5W-40 Performance Engine Oil (4 Litres Pack)',
                'description' => 'Advanced high-viscosity synthetic lubricant with active cleansing agents. Provides superior thermal stability, cold-start protection, and maximizes fuel economy for turbo-petrol and diesel engines.',
                'base_price' => 249900, // ₹2,499 in paise
                'compare_at_price' => 320000,
                'gst_rate' => 18,
                'hsn_code' => '27101981',
                'sku' => 'OIL-SYNTH-5W40-4L',
                'brand' => 'Motul / Castrol Edge',
                'vertical' => 'marketplace',
                'listing_type' => 'purchase',
                'total_stock' => 50,
                'is_featured' => true,
                'custom_fields' => [
                    'Vehicle Compatibility' => 'Universal Multi-Brand (Petrol, Diesel & Turbocharged)',
                    'Viscosity Grade' => 'SAE 5W-40 API SN/CF',
                    'Volume' => '4 Litres Sealed Can',
                    'Warranty' => '100% Genuine Sealed Batch Guarantee',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1619642751034-765dfdf7c58e?auto=format&fit=crop&w=1000&q=80',
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
                'video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-cutting-stone-with-a-machine-42415-large.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=480&q=80',
                'views' => 45280,
                'likes' => 8420,
                'trending_score' => 285.5,
            ],
            [
                'slug' => 'unsealing-clay-handi-dum-biryani',
                'vendor_slug' => 'nawabi-dastarkhwan',
                'product_slug' => 'slow-cooked-awadhi-dum-mutton-biryani',
                'title' => 'Unsealing 4-Hour Dum Awadhi Mutton Biryani Clay Handi',
                'description' => 'Freshly unsealed after 4 hours of coal dum cooking. Notice the aroma, saffron rice color, and tender meat texture.',
                'video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-hands-serving-a-plate-of-rice-42469-large.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=480&q=80',
                'views' => 68420,
                'likes' => 12940,
                'trending_score' => 412.0,
            ],
            [
                'slug' => 'weaving-banarasi-silk-saree-handloom',
                'vendor_slug' => 'kavita-couture',
                'product_slug' => 'banarasi-kadwa-pure-katan-silk-saree',
                'title' => 'Weaving Pure Katan Silk Banarasi Saree on Handloom',
                'description' => 'Generational master artisans in Varanasi weaving real silver zari threads row by row using the legendary Kadwa technique.',
                'video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-hands-threading-a-sewing-machine-42352-large.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=480&q=80',
                'views' => 38910,
                'likes' => 7430,
                'trending_score' => 220.0,
            ],
            [
                'slug' => 'diamond-marble-floor-polishing-before-after',
                'vendor_slug' => 'diamond-gloss-polishing',
                'product_slug' => 'italian-marble-diamond-grinding-crystallization',
                'title' => 'Mirror-Finish Italian Marble Polishing Time-Lapse',
                'description' => 'Incredible optical reflection transformation after 7 steps of planetary diamond grinding and nano crystallization.',
                'video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-cleaning-floor-with-a-machine-42340-large.mp4',
                'thumb_url' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=480&q=80',
                'views' => 54100,
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
                    'category_id' => $catMarble->id,
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

        // 7. Seed Customer Reviews with 5-Star Ratings
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

        // 8. Seed Promotions & Coupons
        Promotion::updateOrCreate(
            ['slug' => 'marble-festival-sale'],
            [
                'vendor_id' => $vendorMap['royal-makrana-marble']->id ?? null,
                'title' => 'Flat ₹1,000 Off on Marble & Stone Slabs',
                'badge' => 'LIMITED TIME',
                'discount_type' => 'fixed',
                'discount_value' => 100000, // ₹1,000 in paise
                'min_spend' => 2000000, // min ₹20,000
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addMonths(6),
                'is_active' => true,
            ]
        );

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
                'category_id' => $catMarble->id,
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
                'category_id' => $catTools->id,
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
                'category_id' => $catTools->id,
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
    }
}
