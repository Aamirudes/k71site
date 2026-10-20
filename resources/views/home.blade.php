@php
    $appUrl = url('/');
    $appName = config('marketplace.name', 'K71');

    $hp = $homepageSettings ?? [];
    $seo = $seoSettings ?? [];

    $announcementEnabled = !isset($hp['announcement_enabled']) ? true : (bool)$hp['announcement_enabled'];
    $showCategories = !isset($hp['show_categories']) ? true : (bool)$hp['show_categories'];
    $showBoutiques = !isset($hp['show_boutiques']) ? true : (bool)$hp['show_boutiques'];
    $showFeatured = !isset($hp['show_featured_products']) ? true : (bool)$hp['show_featured_products'];
    $showArrivals = !isset($hp['show_new_arrivals']) ? true : (bool)$hp['show_new_arrivals'];
    $showPillars = !isset($hp['show_value_pillars']) ? true : (bool)$hp['show_value_pillars'];
    $showStats = !isset($hp['hero_stats_enabled']) ? true : (bool)$hp['hero_stats_enabled'];

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => $appUrl . '/#website',
                'url' => $appUrl,
                'name' => 'K71',
                'description' => 'India\'s All-Sector Multi-Vendor Marketplace & Video Commerce',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('catalog.index') . '?q={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                ]
            ],
            [
                '@type' => 'Organization',
                '@id' => $appUrl . '/#organization',
                'name' => 'K71',
                'url' => $appUrl,
                'logo' => asset('icon-192x192.png'),
            ]
        ]
    ];

    // Master 13 Ecosystem Sectors Specification with High-Resolution Curated Imagery
    $ecosystemSectors = [
        [
            'num' => '01',
            'id' => 'shorts',
            'icon' => '🎥',
            'title' => 'Shoppable Shorts & Live Commerce',
            'model' => 'Discovery Feed Layer',
            'flow_tag' => 'Shoppable Video Tag',
            'flow_key' => 'video',
            'flow_badge' => '🎥 Shoppable Video Tag',
            'flow_color' => 'rose',
            'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=600&q=80',
            'description' => 'Interactive shoppable discovery feed operating across all physical goods, cloud menus, and services with tap-to-buy video tags.',
            'url' => k71_vertical_url('shorts'),
            'cta' => 'Watch Shorts Feed',
            'subcategories' => [
                'Fashion & Inspo' => 'OOTD & Styling Guides',
                'Tech Unboxing' => 'Hands-on Reviews & Specs',
                'Auto Shorts' => 'Walkthroughs & Sounds',
                'Machinery Demos' => 'Live Cutting & Tests',
                'Home DIY' => 'Transformation Reels',
                'Beauty Tutorials' => 'Makeup & Skincare',
                'Food Quick-Clips' => 'Local Chef Reels',
                'Service Showcases' => 'Before/After Demos',
            ]
        ],
        [
            'num' => '02',
            'id' => 'clothing',
            'icon' => '👗',
            'title' => 'Clothing, Fashion & Accessories',
            'model' => 'Retail, Wholesale & Pre-Loved',
            'flow_tag' => 'Instant Buy (B2C/D2C)',
            'flow_key' => 'instant',
            'flow_badge' => '⚡ Instant Buy (B2C/D2C)',
            'flow_color' => 'pink',
            'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=600&q=80',
            'description' => 'Curated wardrobe collections, designer ethnic atelier, bridal lehengas, bespoke suits, luxury footwear, and fine gold jewelry.',
            'url' => route('catalog.index', ['category' => 'clothing-fashion-accessories']),
            'cta' => 'Shop Fashion Catalog',
            'subcategories' => [
                'Men’s Fashion' => 'Suits, Kurtas & Activewear',
                'Women’s Fashion' => 'Sarees, Lehengas & Western',
                'Kids & Baby' => 'Newborn & School Uniforms',
                'Footwear' => 'Sneakers, Formal & Boots',
                'Fine Jewelry' => 'Gold, Diamond & Watches',
                'Bags & Luggage' => 'Totes, Trolleies & Clutches',
                'Accessories' => 'Sunglasses, Belts & Frames',
            ]
        ],
        [
            'num' => '03',
            'id' => 'electronics',
            'icon' => '📱',
            'title' => 'Electronics, Appliances & IT',
            'model' => 'Retail, Refurbished & Bulk B2B',
            'flow_tag' => 'Instant Buy & Bulk B2B',
            'flow_key' => 'instant',
            'flow_badge' => '⚡ Instant Buy & Bulk B2B',
            'flow_color' => 'blue',
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
            'description' => 'Certified smartphones, high-performance computing rigs, smart home audio, 4K digital signage, and commercial appliances.',
            'url' => route('catalog.index', ['category' => 'electronics-appliances-it']),
            'cta' => 'Browse Electronics',
            'subcategories' => [
                'Mobiles & Gear' => 'Smartphones & Power Banks',
                'Computers & IT' => 'Laptops, GPUs & Networking',
                'Audio & Smart Home' => 'Earbuds, Drones & Speakers',
                'TV & Home Theater' => 'Smart TVs & Consoles',
                'Kitchen Appliances' => 'ACs, Fridges & Chimneys',
                'Commercial Electronics' => 'POS, CCTV & Digital Signage',
            ]
        ],
        [
            'num' => '04',
            'id' => 'machinery',
            'icon' => '🛠️',
            'title' => 'Heavy Industrial, Machinery & Construction',
            'model' => 'IndiaMART Style - Quote/RFQ',
            'flow_tag' => 'Request for Quote (RFQ / B2B)',
            'flow_key' => 'rfq',
            'flow_badge' => '📋 Request for Quote (RFQ)',
            'flow_color' => 'amber',
            'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=600&q=80',
            'description' => 'Direct B2B sourcing engine for marble gang saws, CNC lasers, hydraulic presses, excavators, transformers, and industrial steel.',
            'url' => k71_vertical_url('enquiry'),
            'cta' => 'Request Machinery RFQ',
            'subcategories' => [
                'Stone & Marble' => 'Granite Cutters & Edge Polishers',
                'Construction' => 'Excavators, Cranes & Mixers',
                'Manufacturing' => 'CNC Lathes & Hydraulic Presses',
                'Raw Materials' => 'Metals, Polymers & Industrial Yarns',
                'Power Supplies' => 'Transformers, Solar & Generators',
                'Tools & Hardware' => 'PPE Gear, Fasteners & Power Tools',
            ]
        ],
        [
            'num' => '05',
            'id' => 'automotive',
            'icon' => '🚗',
            'title' => 'Automotive, Vehicles & Transport',
            'model' => 'New, Used, Parts & Rentals',
            'flow_tag' => 'Lead / High-Cost Enquiry',
            'flow_key' => 'lead',
            'flow_badge' => '🚗 Lead / Direct Buy',
            'flow_color' => 'red',
            'image' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=600&q=80',
            'description' => 'Verified four-wheelers, EV scooters, commercial logistics trucks, original engine components, and mobile vehicle inspection.',
            'url' => route('catalog.index', ['category' => 'automotive-vehicles-transport']),
            'cta' => 'Explore Vehicles & Parts',
            'subcategories' => [
                'Cars & SUVs' => 'Sedans, Electric EVs & Luxury',
                'Bikes & Two-Wheelers' => 'Superbikes, Scooters & E-Bikes',
                'Commercial Heavy' => 'Trucks, Buses & Farm Tractors',
                'Spare Parts' => 'Engine Components, Tyres & Alloys',
                'Vehicle Services' => 'Inspection & Towing Support',
            ]
        ],
        [
            'num' => '06',
            'id' => 'digital-services',
            'icon' => '💼',
            'title' => 'Digital Services, Freelancing & Gigs',
            'model' => 'Fiverr Style - Gig/Milestone',
            'flow_tag' => 'Service Milestone Gig',
            'flow_key' => 'gig',
            'flow_badge' => '💼 Service Milestone Gig',
            'flow_color' => 'violet',
            'image' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=600&q=80',
            'description' => 'Tiered freelance milestone booking for full-stack engineering, AI/ML models, 3D product motion, SEO, and legal consulting.',
            'url' => k71_vertical_url('services'),
            'cta' => 'Hire Verified Freelancers',
            'subcategories' => [
                'Programming & Tech' => 'Web Apps, AI/ML & DevOps',
                'Graphics & Design' => 'Brand Identity, 3D & UI/UX',
                'Digital Marketing' => 'SEO Campaigns & Influencers',
                'Writing & Translation' => 'Copywriting & Technical Specs',
                'Business Consulting' => 'Accounting & Legal Incorporation',
                'Audio & Voiceover' => 'Sound Mixing & Multilingual VO',
            ]
        ],
        [
            'num' => '07',
            'id' => 'local-services',
            'icon' => '🧹',
            'title' => 'On-Demand Local & On-Site Services',
            'model' => 'Local Contractor / Booking Model',
            'flow_tag' => 'Time-Slot Booking',
            'flow_key' => 'booking',
            'flow_badge' => '⏱️ Time-Slot Booking',
            'flow_color' => 'indigo',
            'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=600&q=80',
            'description' => 'Background-checked technicians and doorstep wellness: Electricians, AC master mechanics, deep home sanitize, and salon experts.',
            'url' => k71_vertical_url('services'),
            'cta' => 'Book Local Technicians',
            'subcategories' => [
                'Home Maintenance' => 'Electricians, Plumbers & AC Care',
                'Cleaning & Pest' => 'Deep Sanitization & Sofa Shampoo',
                'Construction' => 'Interior Designers & Painters',
                'Events & Catering' => 'Photographers, DJs & Catering',
                'Personal Wellness' => 'At-Home Salon, Spa & Yoga',
            ]
        ],
        [
            'num' => '08',
            'id' => 'used-goods',
            'icon' => '🔄',
            'title' => 'Pre-Loved, Used Goods & Thrift',
            'model' => 'OLX/C2C Classifieds & Bidding',
            'flow_tag' => 'Peer-to-Peer (C2C Classified)',
            'flow_key' => 'p2p',
            'flow_badge' => '🤝 Peer-to-Peer Offer',
            'flow_color' => 'emerald',
            'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=600&q=80',
            'description' => 'Verified direct buyer-seller marketplace with condition inspection grades, real-time counter offers, and secure escrow settlement.',
            'url' => k71_vertical_url('used'),
            'cta' => 'Browse Pre-Owned Deals',
            'subcategories' => [
                'Used Electronics' => 'Pre-Owned Mobiles & MacBooks',
                'Pre-Owned Furniture' => 'Sofas, Dining Sets & Desks',
                'Used Vehicles' => 'Pre-Owned Bikes & Certified Cars',
                'Thrift Fashion' => 'Vintage Bags & Rare Watches',
                'Hobbies & Books' => 'Textbooks, Guitars & Gym Gear',
                'Industrial Recyclables' => 'E-Waste, Scrap & Storage Drums',
            ]
        ],
        [
            'num' => '09',
            'id' => 'real-estate',
            'icon' => '🏡',
            'title' => 'Real Estate, Properties & High-Ticket',
            'model' => 'Lead Enquiry & Brokerage',
            'flow_tag' => 'Lead / High-Cost Enquiry',
            'flow_key' => 'lead',
            'flow_badge' => '🏡 High-Ticket Lead Enquiry',
            'flow_color' => 'purple',
            'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?auto=format&fit=crop&w=600&q=80',
            'description' => 'Direct developer listings & high-yield commercial hubs: Luxury villas, Grade-A office floors, industrial sheds, and plots.',
            'url' => k71_vertical_url('enquiry'),
            'cta' => 'Inquire Property Deals',
            'subcategories' => [
                'Residential' => 'Villas, Penthouses & Plots',
                'Commercial' => 'Offices, Retail & Godowns',
                'Property Rentals' => 'Co-Living & Corporate Floors',
                'Industrial Real Estate' => 'Factory Sheds & Industrial Land',
            ]
        ],
        [
            'num' => '10',
            'id' => 'home-living',
            'icon' => '🏠',
            'title' => 'Home, Living, Furniture & Hardware',
            'model' => 'Retail & B2B Wholesale',
            'flow_tag' => 'Instant Buy & Bulk RFQ',
            'flow_key' => 'instant',
            'flow_badge' => '⚡ Instant Buy & RFQ',
            'flow_color' => 'teal',
            'image' => 'https://images.unsplash.com/photo-1618221195710-dd6b41faaea6?auto=format&fit=crop&w=600&q=80',
            'description' => 'Solid teak dining tables, ergonomic workstations, designer ambient lighting, sanitary faucets, and imported marble slabs.',
            'url' => route('catalog.index', ['category' => 'home-living-furniture-hardware']),
            'cta' => 'Shop Home & Living',
            'subcategories' => [
                'Designer Furniture' => 'Beds, Wardrobes & Sofas',
                'Home Decor' => 'Lamps, Rugs, Curtains & Mirrors',
                'Kitchenware' => 'Cookware, Cutlery & Storage',
                'Renovation' => 'Tiles, Marble, Faucets & Paint',
            ]
        ],
        [
            'num' => '11',
            'id' => 'beauty',
            'icon' => '💄',
            'title' => 'Beauty, Personal Care, Health & Wellness',
            'model' => 'Direct Buy & Virtual Try-On',
            'flow_tag' => 'Instant Buy (B2C/D2C)',
            'flow_key' => 'instant',
            'flow_badge' => '⚡ Instant Buy',
            'flow_color' => 'fuchsia',
            'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80',
            'description' => 'Clinical dermatological formulations, clean organic cosmetics, luxury attar, precision trimmers, and certified whey nutrition.',
            'url' => route('catalog.index', ['category' => 'beauty-personal-care-health-wellness']),
            'cta' => 'Explore Beauty & Care',
            'subcategories' => [
                'Skincare & Hair' => 'Serums, Cleansers & Sunscreen',
                'Makeup & Glam' => 'Foundations, Eye Palettes & Lip',
                'Fragrances' => 'Luxury Perfumes & Natural Attar',
                'Personal Grooming' => 'Stylers, Trimmers & Shavers',
                'Health & Wellness' => 'Supplements, Protein & Ayurvedic',
            ]
        ],
        [
            'num' => '12',
            'id' => 'groceries',
            'icon' => '🛒',
            'title' => 'Groceries, Daily Essentials & FMCG',
            'model' => 'Quick Commerce & Direct Cloud Menus',
            'flow_tag' => '30-Min Rapid Delivery',
            'flow_key' => 'instant',
            'flow_badge' => '⚡ 30-Min Rapid Delivery',
            'flow_color' => 'orange',
            'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80',
            'description' => 'Farm-fresh hydroponic greens, cold-pressed artisanal oils, hot chef biryanis, gourmet pantry coffee, and eco household cleaners.',
            'url' => k71_vertical_url('food'),
            'cta' => 'Order Food & Essentials',
            'subcategories' => [
                'Fresh Produce' => 'Organic Fruits & Crisp Greens',
                'Staples & Pantry' => 'Basmati, Pulses & Spices',
                'Snacks & Beverages' => 'Artisanal Teas & Cold Drinks',
                'Cloud Kitchens' => 'Live Chef Menus & 30m Delivery',
                'Household Care' => 'Detergents, Paper & Hygiene',
            ]
        ],
        [
            'num' => '13',
            'id' => 'toys-sports',
            'icon' => '🧸',
            'title' => 'Baby, Toys, Hobbies, Books & Sports',
            'model' => 'Retail & Community Exchange',
            'flow_tag' => 'Instant Buy (B2C/D2C)',
            'flow_key' => 'instant',
            'flow_badge' => '⚡ Instant Buy',
            'flow_color' => 'cyan',
            'image' => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=600&q=80',
            'description' => 'Certified infant gear, STEM robotics kits, competitive academic textbooks, professional sports racquets, and camping tents.',
            'url' => route('catalog.index', ['category' => 'baby-toys-hobbies-books-sports']),
            'cta' => 'Browse Hobbies & Sports',
            'subcategories' => [
                'Baby Care' => 'Strollers, Diapers & Nursing Gear',
                'Toys & STEM' => 'Robotics, Board Games & RC Cars',
                'Books & Media' => 'Academic Textbooks & Novels',
                'Sports & Fitness' => 'Cricket Kits, Dumbbells & Cycles',
            ]
        ],
    ];
@endphp

<x-layouts.public 
    :title="$seo['seo_meta_title'] ?? 'K71 | Curated Multi-Vendor Marketplace & Video Commerce'"
    :metaDescription="$seo['seo_meta_description'] ?? 'India\'s Curated Multi-Vendor Marketplace across 13 Unified Ecosystem Sectors — from Instant Fashion & Shoppable Shorts to Heavy Machinery RFQs, Milestone Gigs, and Pre-Loved Gear.'"
    :metaKeywords="$seo['seo_meta_keywords'] ?? 'marketplace, online shopping, b2b rfq, fiverr gigs, zomato food, olx pre-owned, verified sellers, fast delivery, k71'"
    :canonicalUrl="$seo['seo_canonical_url'] ?? url('/')"
    :robots="$seo['seo_robots'] ?? 'index, follow'"
    :ogTitle="$seo['seo_og_title'] ?? null"
    :ogDescription="$seo['seo_og_description'] ?? null"
    :ogImage="$seo['seo_og_image'] ?? null"
    :schemaJson="(!isset($seo['seo_schema_enabled']) || $seo['seo_schema_enabled']) ? $schemaData : null"
>
    <!-- ======================================================== -->
    <!-- ZEPTO SUPER APP HERO & QUICK COMMERCE (Exact Image 5 Style) -->
    <!-- ======================================================== -->
    <section class="mb-12">
        <!-- 1. Top Location Bar & Quick Brand Tabs (Zepto Bar) -->
        <div class="mb-4 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 p-3.5 bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 text-white rounded-3xl shadow-lg">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div class="cursor-pointer">
                    <div class="text-[10px] uppercase font-bold tracking-wider text-blue-200">Delivery Address</div>
                    <div class="flex items-center gap-1 text-xs sm:text-sm font-black text-white">
                        <span>1st Phase, J. P. Nagar, Bengaluru, KA 560078</span>
                        <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Sub-brand Quick Tabs (Zepto Style) -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar w-full md:w-auto">
                <a href="{{ route('catalog.index') }}" class="px-4 py-2 rounded-2xl bg-white text-blue-800 text-xs font-black shadow-md flex items-center gap-1.5 shrink-0">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>k71 core</span>
                </a>
                <a href="{{ route('catalog.index', ['sort' => 'discount']) }}" class="px-3.5 py-2 rounded-2xl bg-white/15 hover:bg-white/25 text-white text-xs font-black border border-white/20 backdrop-blur-md shrink-0 flex items-center gap-1">
                    <span>50% OFF ZONE</span>
                    <span>🔥</span>
                </a>
                <a href="{{ k71_vertical_url('food') }}" class="px-3.5 py-2 rounded-2xl bg-white/15 hover:bg-white/25 text-white text-xs font-black border border-white/20 backdrop-blur-md shrink-0 flex items-center gap-1">
                    <span>Super Mall</span>
                    <span>🛍️</span>
                </a>
                <a href="{{ k71_vertical_url('food') }}" class="px-3.5 py-2 rounded-2xl bg-emerald-500/80 hover:bg-emerald-500 text-white text-xs font-black border border-emerald-400/30 backdrop-blur-md shrink-0 flex items-center gap-1">
                    <span>Fresh</span>
                    <span>🥬</span>
                </a>
            </div>
        </div>

        <!-- 2. Big Prominent Search Bar (Zepto Style) -->
        <div class="mb-5">
            <form action="{{ route('catalog.index') }}" method="GET" class="relative">
                <div class="absolute left-4 top-3.5 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="q" placeholder='Search for "Festive Vibes", Mobiles, Earphones, Dresses, Biryani, CNC Lathes...' 
                       class="w-full pl-12 pr-32 py-4 rounded-3xl bg-white dark:bg-zinc-900 border border-blue-100 dark:border-zinc-800 text-sm font-medium focus:ring-2 focus:ring-blue-600 focus:outline-none transition shadow-sm text-zinc-900 dark:text-white">
                <button type="submit" class="absolute right-2.5 top-2 px-6 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black tracking-wide transition shadow-sm active:scale-95">
                    Search
                </button>
            </form>
        </div>

        <!-- 3. Category Row with 3D Badges (Zepto Style) -->
        <div class="flex items-center gap-4 sm:gap-6 overflow-x-auto no-scrollbar pb-3 mb-6">
            <a href="{{ route('catalog.index') }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-zinc-800 dark:to-zinc-900 border border-blue-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    🛍️
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">All</span>
            </a>
            <a href="{{ route('catalog.index', ['category' => 'clothing-fashion-accessories']) }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 dark:from-zinc-800 dark:to-zinc-900 border border-amber-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    🌙
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">Festive</span>
            </a>
            <a href="{{ route('catalog.index', ['category' => 'clothing-fashion-accessories']) }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-rose-50 to-pink-50 dark:from-zinc-800 dark:to-zinc-900 border border-rose-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    👕
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">Fashion</span>
            </a>
            <a href="{{ route('catalog.index', ['category' => 'electronics-appliances-it']) }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-zinc-800 dark:to-zinc-900 border border-blue-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    🎧
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">Electronics</span>
            </a>
            <a href="{{ route('catalog.index', ['category' => 'beauty-personal-care-health-wellness']) }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-pink-50 to-purple-50 dark:from-zinc-800 dark:to-zinc-900 border border-pink-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    💄
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">Beauty</span>
            </a>
            <a href="{{ k71_vertical_url('food') }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-zinc-800 dark:to-zinc-900 border border-emerald-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    🥗
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">Food Hub</span>
            </a>
            <a href="{{ k71_vertical_url('services') }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-purple-50 to-violet-50 dark:from-zinc-800 dark:to-zinc-900 border border-purple-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    🛠️
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">Services</span>
            </a>
            <a href="{{ k71_vertical_url('used') }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-zinc-100 to-zinc-200 dark:from-zinc-800 dark:to-zinc-900 border border-zinc-300 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    🤝
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">Pre-Owned</span>
            </a>
            <a href="{{ k71_vertical_url('enquiry') }}" class="flex flex-col items-center shrink-0 group text-center cursor-pointer">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-br from-amber-50 to-yellow-50 dark:from-zinc-800 dark:to-zinc-900 border border-amber-200 dark:border-zinc-700 flex items-center justify-center text-2xl group-hover:scale-105 transition shadow-xs">
                    🏭
                </div>
                <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 group-hover:text-blue-600">B2B RFQ</span>
            </a>
        </div>

        <!-- 4. SUPERSONIC SALE HERO BANNER (Exact Image 5) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-600 via-blue-500 to-indigo-600 p-6 sm:p-10 text-white shadow-2xl mb-6">
            <!-- Background Radial Glow -->
            <div class="absolute -top-12 -right-12 w-96 h-96 bg-cyan-400/20 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <!-- Left 3D Headphone Graphic -->
                <div class="hidden lg:block w-32 h-32 shrink-0">
                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=300&q=80" 
                         alt="Wireless Headphones" class="w-full h-full object-contain drop-shadow-2xl -rotate-12 animate-pulse">
                </div>

                <!-- Center Metallic Headline -->
                <div class="text-center flex-1">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black uppercase tracking-widest mb-3">
                        <span>⚡ LIVE MEGA EVENT ⚡</span>
                    </div>

                    <h2 class="text-4xl sm:text-6xl font-black italic tracking-tighter uppercase leading-none drop-shadow-md">
                        SUPERSONIC
                    </h2>
                    <div class="text-2xl sm:text-4xl font-black text-cyan-200 tracking-wider mt-1 mb-2">
                        ⚡ SALE ⚡
                    </div>
                    <div class="inline-block px-5 py-1.5 rounded-full bg-white text-blue-900 text-xs sm:text-sm font-black uppercase tracking-wider shadow-lg">
                        UP TO 90% OFF
                    </div>
                </div>

                <!-- Right Smartwatch Graphic -->
                <div class="hidden lg:block w-32 h-32 shrink-0">
                    <img src="https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=300&q=80" 
                         alt="Smartwatch" class="w-full h-full object-contain drop-shadow-2xl rotate-12">
                </div>
            </div>

            <!-- ₹0 FEES TRUST STRIP (Exact Image 5 Strip) -->
            <div class="mt-8 pt-4 border-t border-white/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="text-2xl font-black tracking-tight text-white">₹0 FEES</span>
                    <div class="flex flex-wrap items-center gap-3 text-xs font-bold text-blue-100">
                        <span class="flex items-center gap-1">✓ ₹0 Handling Fee</span>
                        <span class="flex items-center gap-1">✓ ₹0 Delivery Fee*</span>
                        <span class="flex items-center gap-1">✓ ₹0 Rain &amp; Surge Fee</span>
                    </div>
                </div>
                <div class="text-[11px] text-blue-100 font-bold shrink-0">
                    Powered by <strong class="text-white">NOISE</strong> • Co-Powered by <strong class="text-white">boAt</strong>
                </div>
            </div>
        </div>

        <!-- 5. HIGH-DENSITY VISUAL CATEGORY TILES (Image 5 2x3 Grid) -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-8">
            <!-- Tile 1: Charging Needs -->
            <a href="{{ route('catalog.index', ['q' => 'charger']) }}" 
               class="group p-4 rounded-3xl bg-gradient-to-b from-blue-50 to-white dark:from-zinc-900 dark:to-zinc-950 border border-blue-200 dark:border-zinc-800 flex flex-col justify-between items-center text-center shadow-xs hover:border-blue-500 transition duration-300">
                <div class="w-full">
                    <h4 class="text-xs font-black text-zinc-900 dark:text-white leading-tight">Charging Needs</h4>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-blue-600 text-white text-[9px] font-black uppercase">UP TO 80% OFF</span>
                </div>
                <div class="w-20 h-20 sm:w-24 sm:h-24 my-2 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=200&q=80" alt="Charger" class="max-h-full object-contain group-hover:scale-105 transition duration-300">
                </div>
            </a>

            <!-- Tile 2: Earphones & Headphones -->
            <a href="{{ route('catalog.index', ['q' => 'earphones']) }}" 
               class="group p-4 rounded-3xl bg-gradient-to-b from-blue-50 to-white dark:from-zinc-900 dark:to-zinc-950 border border-blue-200 dark:border-zinc-800 flex flex-col justify-between items-center text-center shadow-xs hover:border-blue-500 transition duration-300">
                <div class="w-full">
                    <h4 class="text-xs font-black text-zinc-900 dark:text-white leading-tight">Earphones &amp; Audio</h4>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-blue-600 text-white text-[9px] font-black uppercase">STARTING @ ₹69</span>
                </div>
                <div class="w-20 h-20 sm:w-24 sm:h-24 my-2 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=200&q=80" alt="Earphones" class="max-h-full object-contain group-hover:scale-105 transition duration-300">
                </div>
            </a>

            <!-- Tile 3: Home & Kitchen Appliances -->
            <a href="{{ route('catalog.index', ['q' => 'appliances']) }}" 
               class="group p-4 rounded-3xl bg-gradient-to-b from-blue-50 to-white dark:from-zinc-900 dark:to-zinc-950 border border-blue-200 dark:border-zinc-800 flex flex-col justify-between items-center text-center shadow-xs hover:border-blue-500 transition duration-300">
                <div class="w-full">
                    <h4 class="text-xs font-black text-zinc-900 dark:text-white leading-tight">Home Appliances</h4>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-blue-600 text-white text-[9px] font-black uppercase">STARTING @ ₹399</span>
                </div>
                <div class="w-20 h-20 sm:w-24 sm:h-24 my-2 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=200&q=80" alt="Appliance" class="max-h-full object-contain group-hover:scale-105 transition duration-300">
                </div>
            </a>

            <!-- Tile 4: Mobiles & Smartphones -->
            <a href="{{ route('catalog.index', ['q' => 'mobile']) }}" 
               class="group p-4 rounded-3xl bg-gradient-to-b from-blue-50 to-white dark:from-zinc-900 dark:to-zinc-950 border border-blue-200 dark:border-zinc-800 flex flex-col justify-between items-center text-center shadow-xs hover:border-blue-500 transition duration-300">
                <div class="w-full">
                    <h4 class="text-xs font-black text-zinc-900 dark:text-white leading-tight">Flagship Mobiles</h4>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-blue-600 text-white text-[9px] font-black uppercase">NEW LAUNCHES</span>
                </div>
                <div class="w-20 h-20 sm:w-24 sm:h-24 my-2 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=200&q=80" alt="Mobile" class="max-h-full object-contain group-hover:scale-105 transition duration-300">
                </div>
            </a>

            <!-- Tile 5: Grooming & Massagers -->
            <a href="{{ route('catalog.index', ['q' => 'trimmer']) }}" 
               class="group p-4 rounded-3xl bg-gradient-to-b from-blue-50 to-white dark:from-zinc-900 dark:to-zinc-950 border border-blue-200 dark:border-zinc-800 flex flex-col justify-between items-center text-center shadow-xs hover:border-blue-500 transition duration-300">
                <div class="w-full">
                    <h4 class="text-xs font-black text-zinc-900 dark:text-white leading-tight">Grooming Gear</h4>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-blue-600 text-white text-[9px] font-black uppercase">STARTING @ ₹349</span>
                </div>
                <div class="w-20 h-20 sm:w-24 sm:h-24 my-2 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1621607512214-68297480165e?auto=format&fit=crop&w=200&q=80" alt="Grooming" class="max-h-full object-contain group-hover:scale-105 transition duration-300">
                </div>
            </a>

            <!-- Tile 6: Speakers & Smart watches -->
            <a href="{{ route('catalog.index', ['q' => 'smartwatch']) }}" 
               class="group p-4 rounded-3xl bg-gradient-to-b from-blue-50 to-white dark:from-zinc-900 dark:to-zinc-950 border border-blue-200 dark:border-zinc-800 flex flex-col justify-between items-center text-center shadow-xs hover:border-blue-500 transition duration-300">
                <div class="w-full">
                    <h4 class="text-xs font-black text-zinc-900 dark:text-white leading-tight">Smart Watches</h4>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded-md bg-blue-600 text-white text-[9px] font-black uppercase">STARTING @ ₹499</span>
                </div>
                <div class="w-20 h-20 sm:w-24 sm:h-24 my-2 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=200&q=80" alt="Smartwatch" class="max-h-full object-contain group-hover:scale-105 transition duration-300">
                </div>
            </a>
        </div>

        <!-- 6. Bottom Sticky Free Delivery Pill (Exact Image 5 Bottom Pill) -->
        <div class="p-3.5 rounded-2xl bg-zinc-900 dark:bg-zinc-800 text-white flex items-center justify-between shadow-xl">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-base">
                    🛵
                </div>
                <div>
                    <h5 class="text-xs font-black">Unlock free delivery</h5>
                    <p class="text-[10px] text-zinc-400">Add items worth ₹99 to waive delivery charges completely.</p>
                </div>
            </div>
            <a href="{{ route('catalog.index') }}" class="px-4 py-1.5 rounded-xl bg-white text-zinc-950 text-xs font-black hover:bg-zinc-200 transition">
                Shop Deals
            </a>
        </div>
    </section>

    <!-- ======================================================== -->
    <!-- 5-VERTICAL SUPER APP SIGNATURE HUB (The Core Engines)     -->
    <!-- ======================================================== -->
    <section class="mb-14 sm:mb-20">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 text-xs font-bold uppercase tracking-wider mb-2 border border-blue-200 dark:border-blue-800/60">
                    <span class="w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                    <span>The K71 Super App Ecosystem</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white tracking-tight">
                    Five Specialized Transaction Engines
                </h2>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                    Direct access to our purpose-built marketplace verticals — each optimized for its unique trade flow:
                </p>
            </div>
            <span class="text-xs font-mono font-bold text-zinc-400 dark:text-zinc-500">Live &amp; Isolated Subdomains</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Hub Card 1: Used & P2P (Apple Minimalist B&W) -->
            <a href="{{ k71_vertical_url('used') }}" 
               class="group relative rounded-3xl p-5 bg-white dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 hover:border-zinc-900 dark:hover:border-white shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-12 h-12 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            🤝
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-zinc-950 text-white dark:bg-white dark:text-zinc-950">
                            Keypad Bidding
                        </span>
                    </div>
                    <span class="text-[10px] font-mono uppercase text-zinc-400 dark:text-zinc-500">used.{{ config('k71.root_domain', 'marblemandi.org') }}</span>
                    <h3 class="text-base font-black text-zinc-950 dark:text-white mt-1 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 transition-colors">
                        Pre-Owned &amp; Peer Deals
                    </h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Interactive on-screen numeric keypad price negotiation, certified vehicles, gadgets &amp; escrow wallet safety.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs font-bold text-zinc-900 dark:text-white">
                    <span>Make an Offer</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Hub Card 2: Food & Cloud Kitchens (Emerald Swiggy Style) -->
            <a href="{{ k71_vertical_url('food') }}" 
               class="group relative rounded-3xl p-5 bg-white dark:bg-zinc-900 border border-emerald-200/70 dark:border-emerald-900/40 hover:border-emerald-500 dark:hover:border-emerald-400 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            🍔
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white">
                            25-Min Dispatch
                        </span>
                    </div>
                    <span class="text-[10px] font-mono uppercase text-emerald-600 dark:text-emerald-400">food.{{ config('k71.root_domain', 'marblemandi.org') }}</span>
                    <h3 class="text-base font-black text-zinc-950 dark:text-white mt-1 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                        Fresh Food &amp; Menus
                    </h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Gourmet burgers, stone-baked pizzas, organic salads, pure veg toggles, and instant kitchen dispatch.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-emerald-100 dark:border-zinc-800 flex items-center justify-between text-xs font-bold text-emerald-600 dark:text-emerald-400">
                    <span>Order Food</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Hub Card 3: Pro Services (Lavender Urban Company Style) -->
            <a href="{{ k71_vertical_url('services') }}" 
               class="group relative rounded-3xl p-5 bg-white dark:bg-zinc-900 border border-purple-200/70 dark:border-purple-900/40 hover:border-purple-500 dark:hover:border-purple-400 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            ⚡
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-600 text-white">
                            1-Click Slots
                        </span>
                    </div>
                    <span class="text-[10px] font-mono uppercase text-purple-600 dark:text-purple-400">services.{{ config('k71.root_domain', 'marblemandi.org') }}</span>
                    <h3 class="text-base font-black text-zinc-950 dark:text-white mt-1 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors">
                        Verified Pro Services
                    </h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Background-checked electricians, plumbers, car repair, and deep steam cleaning with time-slot guarantees.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-purple-100 dark:border-zinc-800 flex items-center justify-between text-xs font-bold text-purple-600 dark:text-purple-400">
                    <span>Book Pro</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Hub Card 4: B2B Wholesale & RFQ (Crimson Wholesale Engine) -->
            <a href="{{ k71_vertical_url('enquiry') }}" 
               class="group relative rounded-3xl p-5 bg-white dark:bg-zinc-900 border border-red-200/70 dark:border-red-900/40 hover:border-red-500 dark:hover:border-red-400 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            🏭
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-red-600 text-white">
                            Factory Pricing
                        </span>
                    </div>
                    <span class="text-[10px] font-mono uppercase text-red-600 dark:text-red-400">enquiry.{{ config('k71.root_domain', 'marblemandi.org') }}</span>
                    <h3 class="text-base font-black text-zinc-950 dark:text-white mt-1 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors">
                        B2B Bulk &amp; Quotes
                    </h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Volume pricing slabs, barcode scanning, zero middlemen, and 60-second direct manufacturer quotation matching.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-red-100 dark:border-zinc-800 flex items-center justify-between text-xs font-bold text-red-600 dark:text-red-400">
                    <span>Post RFQ</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>

            <!-- Hub Card 5: Shoppable Shorts (Vibrant Video Commerce) -->
            <a href="{{ k71_vertical_url('shorts') }}" 
               class="group relative rounded-3xl p-5 bg-white dark:bg-zinc-900 border border-rose-200/70 dark:border-rose-900/40 hover:border-rose-500 dark:hover:border-rose-400 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <span class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 flex items-center justify-center text-2xl group-hover:scale-110 transition-transform">
                            🎥
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white animate-pulse">
                            Tap to Buy
                        </span>
                    </div>
                    <span class="text-[10px] font-mono uppercase text-rose-600 dark:text-rose-400">shorts.{{ config('k71.root_domain', 'marblemandi.org') }}</span>
                    <h3 class="text-base font-black text-zinc-950 dark:text-white mt-1 group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                        Shoppable Video Feed
                    </h3>
                    <p class="text-xs text-zinc-600 dark:text-zinc-400 mt-2 leading-relaxed">
                        Swipe through real creator unboxings, fashion styling guides, and machinery demos with instant 1-tap purchase.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-rose-100 dark:border-zinc-800 flex items-center justify-between text-xs font-bold text-rose-600 dark:text-rose-400">
                    <span>Watch Shorts</span>
                    <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                </div>
            </a>
        </div>
    </section>

    <!-- LIVE FLASH SALE PROMOTION BANNER (If Active) -->
    @php
        $activePromo = \App\Models\Promotion::where('is_active', true)
            ->where(function($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->latest()
            ->first();
    @endphp

    @if($activePromo)
        <div class="mb-14 p-5 rounded-3xl bg-zinc-950 dark:bg-zinc-900 text-white border border-zinc-800 shadow-xl flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-violet-600/20 text-violet-400 flex items-center justify-center shrink-0 border border-violet-500/30">
                    <svg width="24" height="24" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-full bg-violet-500/20 text-violet-300 text-[10px] font-bold tracking-wider uppercase border border-violet-500/30">Flash Promotion</span>
                        @if(!empty($activePromo->badge) || !empty($activePromo->coupon_code))
                            <span class="text-xs bg-white text-zinc-950 font-mono font-bold px-2.5 py-0.5 rounded-md">CODE: {{ $activePromo->badge ?: $activePromo->coupon_code }}</span>
                        @endif
                    </div>
                    <h3 class="text-base sm:text-lg font-semibold mt-1 text-white">{{ $activePromo->title }}</h3>
                    <p class="text-xs text-zinc-400 line-clamp-1">
                        Get {{ $activePromo->discount_value }}{{ ($activePromo->discount_type ?? 'percentage') === 'percentage' ? '%' : '₹' }} off your orders for a limited time.
                    </p>
                </div>
            </div>
            <a href="{{ route('catalog.index') }}" class="px-6 py-3 rounded-xl bg-white text-zinc-950 font-bold text-xs sm:text-sm hover:bg-zinc-100 transition-all shrink-0 shadow-sm active:scale-95">
                Shop Limited Drop &rarr;
            </a>
        </div>
    @endif

    <!-- SHOPPABLE SHORTS VIDEO COMMERCE REELS RAIL -->
    <div class="mb-14 sm:mb-20">
        <x-k71-shorts-rail title="🎥 Shoppable Video Commerce Feed" subtitle="Watch authentic creator reviews &amp; tap to purchase directly from video" :count="8" />
    </div>

    <!-- SECTION 1: VERIFIED BOUTIQUES & STORES -->
    @if($showBoutiques && isset($topVendors) && $topVendors->count() > 0)
        <section class="mb-14 sm:mb-20" x-data="{}">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400">Verified Merchants</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-zinc-950 dark:text-white tracking-tight mt-0.5">
                        Top Stores &amp; Service Providers
                    </h2>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-0.5 text-xs sm:text-sm">
                        Verified independent sellers, factories, restaurants, and tech pros
                    </p>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-xs sm:text-sm font-semibold text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white transition-colors flex items-center gap-1">
                    <span>All Stores</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <!-- Stores Snap Rail -->
            <div class="flex items-stretch gap-4 overflow-x-auto no-scrollbar snap-x snap-mandatory py-1 pb-4 scroll-smooth"
                 style="scrollbar-width: none; -ms-overflow-style: none;">
                @foreach($topVendors as $vendor)
                    <a href="{{ $vendor->storefront_url }}" 
                       style="width: 270px; min-width: 270px; max-width: 270px;"
                       class="group shrink-0 snap-start flex flex-col bg-white dark:bg-zinc-900 rounded-3xl overflow-hidden border border-zinc-200/70 dark:border-zinc-800 hover:border-violet-500/50 hover:shadow-lg transition-all duration-300 relative"
                       aria-label="{{ $vendor->shop_name }}">
                        <!-- Banner Cover -->
                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 relative overflow-hidden shrink-0" style="aspect-ratio: 3 / 1; height: 90px;">
                            @if($vendor->banner)
                                <img src="{{ \App\Services\ImageService::url($vendor->banner, 550) }}" 
                                     loading="lazy"
                                     decoding="async"
                                     width="270"
                                     height="90"
                                     style="width: 100%; height: 100%; object-fit: cover;" 
                                     class="group-hover:scale-105 transition-transform duration-500" 
                                     alt="{{ $vendor->shop_name }}"
                                     onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                            @else
                                <div class="w-full h-full bg-gradient-to-r from-violet-600/20 to-indigo-600/20 flex items-center justify-center">
                                    <span class="text-[10px] font-semibold text-zinc-400 uppercase tracking-wider">{{ $vendor->shop_name }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Store Meta Area -->
                        <div class="p-4 flex-1 flex flex-col justify-between relative z-10 min-w-0">
                            <div>
                                <!-- Logo + Shop Name -->
                                <div class="flex items-center gap-3 mb-2.5">
                                    <div style="width: 40px; height: 40px; min-width: 40px; min-height: 40px;" 
                                         class="rounded-xl bg-white dark:bg-zinc-800 p-0.5 shadow-xs border border-zinc-200 dark:border-zinc-700 shrink-0 relative overflow-hidden">
                                        @if($vendor->logo)
                                            <img src="{{ \App\Services\ImageService::url($vendor->logo, 100) }}" 
                                                 alt="{{ $vendor->shop_name }}" 
                                                 loading="lazy"
                                                 decoding="async"
                                                 width="40"
                                                 height="40"
                                                 style="width: 100%; height: 100%; object-fit: cover;"
                                                 class="rounded-lg" 
                                                 onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                        @else
                                            <div class="w-full h-full rounded-lg bg-violet-600 text-white flex items-center justify-center text-sm font-semibold">
                                                {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="font-semibold text-sm text-zinc-900 dark:text-white group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors truncate">
                                                {{ $vendor->shop_name }}
                                            </h3>
                                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500 text-white flex items-center justify-center shrink-0" title="Verified Seller">
                                                <svg class="w-2 h-2 fill-current" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                                            </span>
                                        </div>
                                        <span class="text-[10px] text-zinc-500 dark:text-zinc-400 block">
                                            {{ $vendor->products_count ?? 0 }} Catalog Items
                                        </span>
                                    </div>
                                </div>

                                <p class="text-xs text-zinc-500 dark:text-zinc-400 leading-relaxed overflow-hidden" 
                                    style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; word-break: break-word;">
                                    {{ \Illuminate\Support\Str::limit($vendor->description ?: 'Verified seller on K71 marketplace.', 85) }}
                                </p>
                            </div>

                            <div class="mt-3.5 pt-2.5 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between text-xs">
                                <span class="font-bold text-violet-600 dark:text-violet-400 flex items-center gap-1">
                                    <span>Visit Storefront</span>
                                    <span class="transition-transform group-hover:translate-x-1">&rarr;</span>
                                </span>
                                <span class="text-[9.5px] uppercase font-semibold text-zinc-400">Direct Merchant</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <!-- SECTION 2: SPOTLIGHT FEATURED PRODUCTS (Dynamic) -->
    @if($showFeatured && $featuredProducts->count() > 0)
        <section id="featured-products" class="mb-14 sm:mb-20">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400">Spotlight Picks</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-zinc-950 dark:text-white tracking-tight mt-0.5">
                        Featured Products &amp; Offerings
                    </h2>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-0.5 text-xs sm:text-sm">
                        Curated selections from verified stores across India
                    </p>
                </div>
                <a href="{{ route('catalog.index', ['featured' => 1]) }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-700 text-xs font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors shadow-xs">
                    View All &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-5 lg:gap-6">
                @foreach($featuredProducts->take(8) as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif

    <!-- SECTION 3: NEW ARRIVALS -->
    @if($showArrivals && $newArrivals->count() > 0)
        <section id="new-arrivals" class="mb-14 sm:mb-20 bg-zinc-50/70 dark:bg-zinc-900/60 rounded-3xl p-5 sm:p-8 border border-zinc-200/60 dark:border-zinc-800 shadow-xs">
            <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-3">
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-violet-600 dark:text-violet-400">Fresh Drops</span>
                    <h2 class="text-xl sm:text-2xl font-bold text-zinc-950 dark:text-white tracking-tight mt-0.5">
                        New Arrivals Across All Sectors
                    </h2>
                    <p class="text-zinc-500 dark:text-zinc-400 mt-0.5 text-xs sm:text-sm">
                        Latest products, machines, dishes, and gigs published this week
                    </p>
                </div>
                <a href="{{ route('catalog.index', ['sort' => 'newest']) }}" class="hidden sm:inline-flex items-center px-4 py-2 rounded-xl bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs font-semibold text-zinc-800 dark:text-zinc-200 hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-colors shadow-xs">
                    Explore New &rarr;
                </a>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-5 lg:gap-6">
                @foreach($newArrivals->take(8) as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>

            <div class="mt-8 text-center">
                <a href="{{ route('catalog.index') }}" class="inline-flex items-center justify-center px-8 py-3.5 border border-zinc-300 dark:border-zinc-700 rounded-xl text-xs sm:text-sm font-bold text-zinc-900 dark:text-white bg-white dark:bg-zinc-800 shadow-xs hover:bg-zinc-50 dark:hover:bg-zinc-700 transition-all active:scale-95 gap-2">
                    <span>Browse All Products in Catalog</span>
                    <span>&rarr;</span>
                </a>
            </div>
        </section>
    @endif

    <!-- SECTION 4: 13 MULTI-CATEGORY ECOSYSTEM SECTORS DIRECTORY -->
    <section class="mb-14 sm:mb-20" x-data="{ 
        activeFlow: 'all',
        matches(sectorKey) {
            return this.activeFlow === 'all' || this.activeFlow === sectorKey;
        }
    }">
        <div class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-violet-100 dark:bg-violet-950/60 text-violet-700 dark:text-violet-300 text-xs font-bold uppercase tracking-wider mb-2 border border-violet-200 dark:border-violet-800/60">
                    <span>Complete Multi-Sector Directory</span>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 dark:text-white tracking-tight">
                    13 Integrated Industry Sectors
                </h2>
                <p class="text-xs sm:text-sm text-zinc-600 dark:text-zinc-400 mt-1">
                    Every vertical on K71 is linked to a purpose-built transaction flow. Filter by trade intent:
                </p>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs sm:text-sm font-bold text-violet-600 dark:text-violet-400 hover:underline flex items-center gap-1 shrink-0">
                <span>View Full 160+ Category Tree</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Flow Filter Pills Bar -->
        <div class="mb-6 overflow-x-auto no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
            <div class="flex items-center gap-2 min-w-max pb-2">
                <button type="button" @click="activeFlow = 'all'" 
                        :class="activeFlow === 'all' ? 'bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer">
                    All 13 Sectors
                </button>
                <button type="button" @click="activeFlow = 'instant'" 
                        :class="activeFlow === 'instant' ? 'bg-pink-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5">
                    <span>⚡ Instant Buy (B2C/D2C)</span>
                </button>
                <button type="button" @click="activeFlow = 'rfq'" 
                        :class="activeFlow === 'rfq' ? 'bg-amber-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5">
                    <span>📋 Request for Quote (RFQ)</span>
                </button>
                <button type="button" @click="activeFlow = 'gig'" 
                        :class="activeFlow === 'gig' ? 'bg-violet-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5">
                    <span>💼 Milestone Gigs</span>
                </button>
                <button type="button" @click="activeFlow = 'booking'" 
                        :class="activeFlow === 'booking' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5">
                    <span>⏱️ Time-Slot Booking</span>
                </button>
                <button type="button" @click="activeFlow = 'p2p'" 
                        :class="activeFlow === 'p2p' ? 'bg-emerald-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5">
                    <span>🤝 Peer-to-Peer Offer</span>
                </button>
                <button type="button" @click="activeFlow = 'video'" 
                        :class="activeFlow === 'video' ? 'bg-rose-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 border border-zinc-200 dark:border-zinc-800'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition-all duration-200 cursor-pointer flex items-center gap-1.5">
                    <span>🎥 Shoppable Video Tag</span>
                </button>
            </div>
        </div>

        <!-- 13 Ecosystem Cards Grid -->
        <!-- 13 Ecosystem Cards Grid (Luxury Visual Showcase) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($ecosystemSectors as $sector)
                <div x-show="matches('{{ $sector['flow_key'] }}')"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="luxury-card group relative rounded-3xl overflow-hidden bg-white dark:bg-[#111827] border border-zinc-200/80 dark:border-white/10 shadow-sm hover:shadow-2xl transition-all duration-300 flex flex-col justify-between">
                    
                    <!-- Sector Visual Image Header Stage -->
                    <div class="relative w-full overflow-hidden bg-zinc-100 dark:bg-zinc-800" style="aspect-ratio: 16 / 9;">
                        @if(!empty($sector['image']))
                            <img src="{{ $sector['image'] }}" 
                                 alt="{{ $sector['title'] }}" 
                                 loading="lazy" 
                                 decoding="async"
                                 class="w-full h-full object-cover transition-transform duration-700 ease-out group-hover:scale-108">
                        @else
                            <div class="w-full h-full bg-gradient-to-tr from-violet-600 to-indigo-600"></div>
                        @endif

                        <!-- Dark Scrim Gradient for Ultimate Contrast -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/30 to-black/10"></div>

                        <!-- Top Floating Badges -->
                        <div class="absolute top-3 inset-x-3 flex items-center justify-between z-10">
                            <div class="flex items-center gap-2">
                                <span class="w-9 h-9 rounded-2xl bg-white/90 dark:bg-zinc-950/80 backdrop-blur-md flex items-center justify-center text-lg shadow-md border border-white/30">
                                    {{ $sector['icon'] }}
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black uppercase tracking-wider bg-black/60 text-white backdrop-blur-md border border-white/20">
                                    #{{ $sector['num'] }}
                                </span>
                            </div>

                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black tracking-wider uppercase bg-white/90 dark:bg-zinc-900/90 text-zinc-950 dark:text-white backdrop-blur-md shadow-md border border-white/30">
                                {{ $sector['model'] }}
                            </span>
                        </div>

                        <!-- Bottom Title Floating on Image -->
                        <div class="absolute bottom-3 inset-x-3.5 z-10">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[9.5px] font-black uppercase tracking-wider bg-{{ $sector['flow_color'] ?? 'emerald' }}-500 text-white shadow-md mb-1.5">
                                {{ $sector['flow_badge'] }}
                            </span>
                            <h3 class="text-base sm:text-lg font-black text-white leading-tight drop-shadow-md group-hover:text-emerald-300 transition-colors">
                                {{ $sector['title'] }}
                            </h3>
                        </div>
                    </div>

                    <!-- Card Body Content -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <!-- Description -->
                            <p class="text-xs text-zinc-600 dark:text-zinc-300 leading-relaxed">
                                {{ $sector['description'] }}
                            </p>

                            <!-- Subcategories Pills List -->
                            <div class="mt-4 pt-3.5 border-t border-zinc-100 dark:border-white/10">
                                <span class="text-[9.5px] uppercase font-bold tracking-wider text-zinc-400 dark:text-zinc-400 block mb-2">Key Categories:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($sector['subcategories'] as $subName => $subDetail)
                                        <a href="{{ route('catalog.index', ['q' => $subName]) }}" 
                                           class="px-2.5 py-1 rounded-xl bg-zinc-100 dark:bg-[#1E293B] text-zinc-700 dark:text-zinc-200 text-[11px] font-medium hover:bg-emerald-500 hover:text-white dark:hover:bg-emerald-500 dark:hover:text-zinc-950 transition-all duration-200 border border-zinc-200/60 dark:border-white/5"
                                           title="{{ $subDetail }}">
                                            {{ $subName }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Direct Action Link Button -->
                        <div class="mt-6 pt-3.5 border-t border-zinc-100 dark:border-white/10 flex items-center justify-between">
                            <a href="{{ $sector['url'] }}" 
                               class="text-xs font-black text-zinc-900 dark:text-emerald-400 hover:text-emerald-600 dark:hover:text-emerald-300 transition-colors flex items-center gap-1.5">
                                <span>{{ $sector['cta'] }}</span>
                                <span class="group-hover:translate-x-1.5 transition-transform duration-200">&rarr;</span>
                            </a>
                            <span class="text-[10px] font-mono font-bold text-zinc-400 dark:text-zinc-400 flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Verified</span>
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- SECTION 5: TRUST & PLATFORM ARCHITECTURE PILLARS -->
    @if($showPillars)
        <section class="relative py-12 sm:py-16 px-6 sm:px-10 rounded-3xl overflow-hidden mb-14 sm:mb-20 text-center border border-zinc-200/80 dark:border-zinc-800 bg-zinc-950 text-white">
            <div class="relative z-10 max-w-4xl mx-auto">
                <span class="inline-block px-3.5 py-1 rounded-full bg-violet-500/20 text-violet-300 text-xs font-semibold tracking-wider uppercase mb-3 border border-violet-500/30">
                    The K71 Unified Architecture
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-white mb-3">
                    Engineered for Multi-Tenancy &amp; Security
                </h2>
                <p class="text-zinc-400 text-xs sm:text-sm max-w-xl mx-auto mb-10 leading-relaxed">
                    Designed to run seamlessly on marblemandi.org with unified multi-category vertical routing and independent storefronts.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-left">
                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-violet-500/20 text-violet-400 flex items-center justify-center mb-3">
                            <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <h4 class="font-bold text-sm text-white mb-1">Escrow Protection</h4>
                        <p class="text-zinc-400 text-xs leading-relaxed">Payments captured securely via PhonePe &amp; banking gateways with verified disbursements.</p>
                    </div>

                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-pink-500/20 text-pink-400 flex items-center justify-center mb-3">
                            <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418"/></svg>
                        </div>
                        <h4 class="font-bold text-sm text-white mb-1">Storefront Architecture</h4>
                        <p class="text-zinc-400 text-xs leading-relaxed">Dedicated storefronts and curated category hubs across food, shorts, services, and pre-owned items.</p>
                    </div>

                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/20 text-amber-400 flex items-center justify-center mb-3">
                            <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                        </div>
                        <h4 class="font-bold text-sm text-white mb-1">AI-Matched RFQs</h4>
                        <p class="text-zinc-400 text-xs leading-relaxed">Heavy industrial and wholesale requests instantly matched with verified manufacturers.</p>
                    </div>

                    <div class="bg-white/5 border border-white/10 p-5 rounded-2xl">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center mb-3">
                            <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.125-.504 1.125-1.125V15.75m-19.5-.75h19.5"/></svg>
                        </div>
                        <h4 class="font-bold text-sm text-white mb-1">30-Min &amp; Pan-India</h4>
                        <p class="text-zinc-400 text-xs leading-relaxed">Integrated with hyper-local dispatch for food and Shiprocket express courier across India.</p>
                    </div>
                </div>
            </div>
        </section>
    @endif
</x-layouts.public>
