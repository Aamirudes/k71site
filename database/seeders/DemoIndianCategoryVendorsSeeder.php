<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoIndianCategoryVendorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates authentic Indian demo vendor stores for every marketplace category without creating products.
     */
    public function run(): void
    {
        $vendorsData = [
            [
                'vendor_type' => 'food',
                'shop_name' => 'Dawat-e-Khaas Royal Biryani & Kebabs',
                'slug' => 'dawat-e-khaas-biryani',
                'owner_name' => 'Mohammad Rehan Ansari',
                'owner_email' => 'dawat.biryani@k71.in',
                'owner_phone' => '9820112233',
                'city' => 'Lucknow',
                'state' => 'Uttar Pradesh',
                'pincode' => '226001',
                'address' => 'Shop 14, Hazratganj Heritage Market, Near Clock Tower',
                'tagline' => 'Traditional Awadhi Dum Pukht Handi Biryani, Kakori Kebabs & Shahi Phirni',
                'description' => 'Four generations of authentic Awadhi culinary tradition. Slow-cooked over charcoal wood fire with aged Basmati and hand-ground spices in sealed clay handis.',
                'logo' => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => true,
                'is_pure_veg' => false,
                'avg_prep_time' => 25,
                'cuisine' => 'Awadhi, Mughlai, Dum Biryani',
            ],
            [
                'vendor_type' => 'clothing',
                'shop_name' => 'Kashi Weaves & Banarasi Silks',
                'slug' => 'kashi-weaves-banarasi-silks',
                'owner_name' => 'Rajeshwar Kumar Mishra',
                'owner_email' => 'kashi.silks@k71.in',
                'owner_phone' => '9820223344',
                'city' => 'Varanasi',
                'state' => 'Uttar Pradesh',
                'pincode' => '221001',
                'address' => 'Ghat Road, Madanpura Silk Weaver Colony',
                'tagline' => 'Handloom Pure Katan Silk Sarees, Kadwa Zari & Royal Bridal Lehengas',
                'description' => 'Master weavers society delivering GI-tagged authentic Varanasi handwoven silk sarees, bridal brocades, and ceremonial dupattas direct from heritage pit looms.',
                'logo' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1617627143750-d86bc21e42bb?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'footwear',
                'shop_name' => 'Agra Leathercraft & Royal Jutti',
                'slug' => 'agra-leathercraft-royal-jutti',
                'owner_name' => 'Sunil Verma',
                'owner_email' => 'agra.leather@k71.in',
                'owner_phone' => '9820334455',
                'city' => 'Agra',
                'state' => 'Uttar Pradesh',
                'pincode' => '282001',
                'address' => 'Plot 8, Hing Ki Mandi Footwear Trade Center',
                'tagline' => 'Handcrafted Full-Grain Leather Formal Shoes, Kolhapuri Chappals & Mules',
                'description' => 'Agra artisanal shoemakers crafting Goodyear-welted formal oxfords, genuine crust leather boots, and zari-embroidered traditional wedding juttis.',
                'logo' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'groceries',
                'shop_name' => 'Desi Kisan Organic Staples & Spices',
                'slug' => 'desi-kisan-organic-staples',
                'owner_name' => 'Harish Patel',
                'owner_email' => 'desi.kisan@k71.in',
                'owner_phone' => '9820445566',
                'city' => 'Indore',
                'state' => 'Madhya Pradesh',
                'pincode' => '452001',
                'address' => 'Agro Mall Complex, Siyaganj Wholesale Market',
                'tagline' => 'Stone Chakki Sharbati Atta, Vedic Bilona A2 Gir Cow Ghee & Cold-Pressed Oils',
                'description' => 'Farmer-producer cooperative providing 100% adulteration-free stone chakki fresh flour, indigenous Gir cow A2 ghee, organic unpolished pulses, and wild forest honey.',
                'logo' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1506484381205-f7945653044d?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => true,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'electronics',
                'shop_name' => 'Bharat Tech Hub & Digital Sound',
                'slug' => 'bharat-tech-hub',
                'owner_name' => 'Arunachalam Venkatesh',
                'owner_email' => 'bharat.tech@k71.in',
                'owner_phone' => '9820556677',
                'city' => 'Bengaluru',
                'state' => 'Karnataka',
                'pincode' => '560002',
                'address' => 'SP Road Electronics Market, Silicon Plaza Unit 102',
                'tagline' => 'Brand Certified Mobiles, ANC Wireless Audio Gear & High-Speed Computing',
                'description' => 'Authorised distributor of smartphones, gaming monitors, mechanical keyboards, fast charging adapters, and hi-fi wireless audio components.',
                'logo' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'marble',
                'shop_name' => 'Makrana Imperial Stone & Granite Works',
                'slug' => 'makrana-imperial-stone',
                'owner_name' => 'Haji Mohammad Rafiq',
                'owner_email' => 'makrana.stone@k71.in',
                'owner_phone' => '9820667788',
                'city' => 'Makrana',
                'state' => 'Rajasthan',
                'pincode' => '341505',
                'address' => 'Gunawati Road, Near Quarry Gate No. 4',
                'tagline' => 'Direct Quarry Makrana Pure White Marble, Dungri Slabs & Temple Carvings',
                'description' => 'Quarry owners and gangsaw processors specializing in Taj Mahal-grade pure Makrana white marble, premium granite flooring, and bespoke architectural temple domes.',
                'logo' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1600585154526-990dced4db0d?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'services',
                'shop_name' => 'UrbanShilp Certified Home Repairs & Plumbers',
                'slug' => 'urbanshilp-home-repairs',
                'owner_name' => 'Deepak Ramesh Shinde',
                'owner_email' => 'urbanshilp.services@k71.in',
                'owner_phone' => '9820778899',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400053',
                'address' => 'Andheri Link Road, City Service Center',
                'tagline' => 'Background Verified Electricians, Master Plumbers, Split AC Service & Painting',
                'description' => 'On-demand verified household technicians delivering transparent fixed-rate electrical repairs, pipe leak detection, deep bathroom cleaning, and inverter setups.',
                'logo' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'digital_services',
                'shop_name' => 'VedicPixel Software & Creative Agency',
                'slug' => 'vedicpixel-creative-agency',
                'owner_name' => 'Ananya Sharma',
                'owner_email' => 'vedicpixel.studio@k71.in',
                'owner_phone' => '9820889900',
                'city' => 'Pune',
                'state' => 'Maharashtra',
                'pincode' => '411057',
                'address' => 'Phase 1 Hinjewadi IT Park, Tech Hub Building 4B',
                'tagline' => 'Full-Stack Web Platforms, Mobile Apps, Modern UI/UX & Brand Identity Systems',
                'description' => 'Boutique digital studio specializing in responsive Laravel, React, and mobile applications, high-converting Figma UI/UX designs, and motion design.',
                'logo' => 'https://images.unsplash.com/photo-1572044162444-ad60f128bdea?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'used',
                'shop_name' => 'PuranaBazaar Verified Pre-Loved & Thrift',
                'slug' => 'puranabazaar-verified-preloved',
                'owner_name' => 'Gaurav Singhal',
                'owner_email' => 'purana.bazaar@k71.in',
                'owner_phone' => '9820990011',
                'city' => 'Delhi NCR',
                'state' => 'Delhi',
                'pincode' => '110001',
                'address' => 'Connaught Place Outer Circle, Vintage Arcade Unit 12',
                'tagline' => '42-Point Inspected Used Mobiles, MacBooks, Two-Wheelers & Heritage Furniture',
                'description' => 'Carefully inspected, genuine pre-owned electronics, certified classic motorcycles, solid teak vintage furniture, and collectible horology watches.',
                'logo' => 'https://images.unsplash.com/photo-1556740758-90de374c12ad?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1526738549149-8e07eca6c147?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'beauty',
                'shop_name' => 'Kasturi Ayurveda & Herbal Wellness',
                'slug' => 'kasturi-ayurveda-wellness',
                'owner_name' => 'Dr. Meenakshi Pillai',
                'owner_email' => 'kasturi.ayurveda@k71.in',
                'owner_phone' => '9821001122',
                'city' => 'Kochi',
                'state' => 'Kerala',
                'pincode' => '682001',
                'address' => 'Fort Kochi Heritage Herbarium, Calvathy Road',
                'tagline' => 'Cold-Pressed Kumkumadi Tailam, Pure Saffron Face Elixirs & Ayurvedic Care',
                'description' => 'Traditional Ayurvedic apothecary preparing small-batch classical formulations, 100% natural herb-infused hair oils, pure steam-distilled Kannauj rose mist.',
                'logo' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1608248597359-59754f9a0c7c?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => true,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'home_living',
                'shop_name' => 'Shekhawati Teak & Royal Furniture',
                'slug' => 'shekhawati-royal-furniture',
                'owner_name' => 'Ratanlal Jangid',
                'owner_email' => 'shekhawati.teak@k71.in',
                'owner_phone' => '9821112233',
                'city' => 'Jodhpur',
                'state' => 'Rajasthan',
                'pincode' => '342001',
                'address' => 'Heavy Industrial Area, Marwar Crafts Zone',
                'tagline' => 'Kiln-Dried CP Teakwood Dining Sets, Jharokhas & Brass Antique Accents',
                'description' => 'Artisans of Thar desert hand-carving heritage solid Sheesham and CP Teak furniture, royal canopy beds, rustic bar cabinets, and ornate brass mirrors.',
                'logo' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'automotive',
                'shop_name' => 'Rajputana Auto Spares & Performance',
                'slug' => 'rajputana-auto-spares',
                'owner_name' => 'Manvendra Singh Rathore',
                'owner_email' => 'rajputana.auto@k71.in',
                'owner_phone' => '9821223344',
                'city' => 'Jaipur',
                'state' => 'Rajasthan',
                'pincode' => '302001',
                'address' => 'Transport Nagar Arterial Highway, Shop 42',
                'tagline' => 'OEM Four-Wheeler & Two-Wheeler Spares, High-Adhesion Chain Lubes & Touring Gear',
                'description' => 'Direct supplier of genuine motorcycle and car replacement spares, maintenance-free batteries, ceramic brake pads, and heavy-duty touring luggage racks.',
                'logo' => 'https://images.unsplash.com/photo-1486006920555-c77dce18193b?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'real_estate',
                'shop_name' => 'VastuPrime Gated High-Rise & Luxury Villas',
                'slug' => 'vastuprime-luxury-villas',
                'owner_name' => 'Vikramaditya Chauhan',
                'owner_email' => 'vastuprime.realty@k71.in',
                'owner_phone' => '9821334455',
                'city' => 'Gurugram',
                'state' => 'Haryana',
                'pincode' => '122002',
                'address' => 'Golf Course Extension Road, Tower A Suite 901',
                'tagline' => 'RERA Approved 3 & 4 BHK Luxury High-Rise Residences, Penthouses & Villa Plots',
                'description' => 'Premier real estate advisory showcasing certified OC-received ready luxury homes, Italian-marble fitted penthouses, and high-yield commercial showrooms.',
                'logo' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'toys_sports',
                'shop_name' => 'Arya Khel & Channapatna Wooden Toys',
                'slug' => 'arya-khel-wooden-toys',
                'owner_name' => 'Sowmya Murthy',
                'owner_email' => 'arya.khel@k71.in',
                'owner_phone' => '9821445566',
                'city' => 'Channapatna',
                'state' => 'Karnataka',
                'pincode' => '562160',
                'address' => 'Toy Town Craft Cluster, Bangalore-Mysore Highway',
                'tagline' => 'Natural Lacquer Non-Toxic Wooden Toys, Kashmir Willow Cricket Bats & Chess Sets',
                'description' => 'Traditional GI-certified artisan toys carved from ivory wood using vegetable dyes, handcrafted educational puzzles, and hand-pressed Kashmir willow cricket bats.',
                'logo' => 'https://images.unsplash.com/photo-1566576912321-d58ddd7a6088?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1516627145497-ae6968895b74?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
            [
                'vendor_type' => 'b2b',
                'shop_name' => 'Kalyan Heavy Machinery & Industrial Plant Tools',
                'slug' => 'kalyan-industrial-machinery',
                'owner_name' => 'Bhavin Patel',
                'owner_email' => 'kalyan.machinery@k71.in',
                'owner_phone' => '9821556677',
                'city' => 'Ahmedabad',
                'state' => 'Gujarat',
                'pincode' => '382445',
                'address' => 'GIDC Vatva Industrial Estate, Phase 4 Shed 88',
                'tagline' => 'CNC VMC Machines, Heavy Hydraulic Press, Diesel Gensets & Stone Cutting Bridges',
                'description' => 'Industrial manufacturing and wholesale plant supplier. Turnkey automated processing equipment, hydraulic stone block dressers, and high-torque CNC cutting units.',
                'logo' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=300&q=80',
                'banner' => 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=1200&q=80',
                'is_restaurant' => false,
                'is_pure_veg' => false,
                'avg_prep_time' => 0,
                'cuisine' => null,
            ],
        ];

        $vendorRole = \App\Models\Role::where('name', 'vendor')->first();

        foreach ($vendorsData as $v) {
            $nameParts = explode(' ', trim($v['owner_name']), 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Trader';
            $cleanName = Str::slug($firstName);
            $username = substr($cleanName ?: 'vendor', 0, 14) . rand(100, 999);

            // Find or create user with guaranteed login credentials
            $user = User::where('email', $v['owner_email'])->first();
            if (!$user) {
                $user = User::create([
                    'email' => $v['owner_email'],
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'username' => $username,
                    'phone' => $v['owner_phone'],
                    'password' => Hash::make('Vendor@123#K71'),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'terms_accepted_at' => now(),
                ]);
            } else {
                $user->password = Hash::make('Vendor@123#K71');
                $user->email_verified_at = $user->email_verified_at ?: now();
                $user->phone_verified_at = $user->phone_verified_at ?: now();
                $user->terms_accepted_at = $user->terms_accepted_at ?: now();
                $user->save();
            }

            if ($vendorRole && !$user->roles->contains($vendorRole->id)) {
                $user->roles()->attach($vendorRole->id);
            }

            // Find or create vendor
            $vendor = Vendor::where('slug', $v['slug'])->first();
            if (!$vendor) {
                $vendor = Vendor::create([
                    'user_id' => $user->id,
                    'slug' => $v['slug'],
                    'shop_name' => $v['shop_name'],
                    'vendor_type' => $v['vendor_type'],
                    'tagline' => $v['tagline'],
                    'description' => $v['description'],
                    'logo' => $v['logo'],
                    'banner' => $v['banner'],
                    'city' => $v['city'],
                    'state' => $v['state'],
                    'pincode' => $v['pincode'],
                    'address' => $v['address'],
                    'contact_email' => $v['owner_email'],
                    'contact_phone' => $v['owner_phone'],
                    'phone' => $v['owner_phone'],
                    'email' => $v['owner_email'],
                    'status' => 'approved',
                    'approved_at' => now(),
                    'rating' => 4.9,
                    'is_verified_pro' => true,
                    'store_policies' => 'Genuine authentic Indian craft guarantee. Safe pan-India doorstep delivery with hassle-free inspection policy.',
                ]);
            } else {
                $vendor->status = 'approved';
                $vendor->approved_at = $vendor->approved_at ?: now();
                $vendor->save();
            }

            // Ensure vendor settings are properly linked
            VendorSetting::updateOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'is_restaurant' => $v['is_restaurant'],
                    'is_pure_veg' => $v['is_pure_veg'],
                    'avg_prep_time_mins' => $v['avg_prep_time'],
                    'cuisine_types' => $v['cuisine'] ? json_encode(explode(', ', $v['cuisine'])) : null,
                    'food_delivery_fee' => 3500, // ₹35
                    'shipping_free_above' => 24900, // ₹249
                ]
            );
        }

        $this->command?->info('Created ' . count($vendorsData) . ' demo Indian category vendors (without products).');
    }
}
