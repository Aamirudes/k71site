@php
    $appUrl = url('/');
    $appName = config('k71.name', 'K71');
    $hp = $homepageSettings ?? [];
    $seo = $seoSettings ?? [];

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => $appUrl . '/#website',
                'url' => $appUrl,
                'name' => 'K71 Marketplace',
                'description' => 'India\'s Premier Multi-Sector Curated Marketplace & Verified Stores',
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
                'logo' => asset('images/k71-logo.png'),
            ]
        ]
    ];

    $curatedSectors = [
        [
            'id' => 'electronics',
            'icon' => '📱',
            'title' => 'Electronics & IT',
            'tag' => 'Mobiles, Laptops & Audio',
            'badge' => 'Gadgets',
            'color' => 'from-blue-500/10 to-indigo-500/10 text-blue-700 border-blue-200',
            'url' => route('catalog.index', ['category' => 'electronics-appliances-it']),
            'shape' => 'rounded-[2rem]',
        ],
        [
            'id' => 'clothing',
            'icon' => '👗',
            'title' => 'Fashion & Apparel',
            'tag' => 'Ethnic, Western & Footwear',
            'badge' => 'Apparel',
            'color' => 'from-pink-500/10 to-rose-500/10 text-pink-700 border-pink-200',
            'url' => route('catalog.index', ['category' => 'clothing-fashion-accessories']),
            'shape' => 'rounded-3xl',
        ],
        [
            'id' => 'automotive',
            'icon' => '🚗',
            'title' => 'Automotive & Spares',
            'tag' => 'OEM Parts, Oils & Accessories',
            'badge' => 'Auto Spares',
            'color' => 'from-amber-500/10 to-orange-500/10 text-amber-700 border-amber-200',
            'url' => route('catalog.index', ['category' => 'automotive-vehicles-transport']),
            'shape' => 'rounded-2xl',
        ],
        [
            'id' => 'home_living',
            'icon' => '🛋️',
            'title' => 'Home & Living',
            'tag' => 'Furniture, Decor & Kitchen',
            'badge' => 'Living',
            'color' => 'from-emerald-500/10 to-teal-500/10 text-emerald-700 border-emerald-200',
            'url' => route('catalog.index', ['category' => 'home-kitchen-furniture-living']),
            'shape' => 'rounded-[2.2rem]',
        ],
        [
            'id' => 'food',
            'icon' => '🍽️',
            'title' => 'Food & Gourmet',
            'tag' => 'Artisanal Foods & Cloud Kitchens',
            'badge' => 'Dining Hub',
            'color' => 'from-rose-500/10 to-red-500/10 text-rose-700 border-rose-200',
            'url' => k71_vertical_url('food'),
            'shape' => 'rounded-3xl',
        ],
        [
            'id' => 'services',
            'icon' => '🔧',
            'title' => 'Local Services',
            'tag' => 'Technicians, Repairs & Salon',
            'badge' => 'On-Demand',
            'color' => 'from-purple-500/10 to-indigo-500/10 text-purple-700 border-purple-200',
            'url' => k71_vertical_url('services'),
            'shape' => 'rounded-2xl',
        ],
        [
            'id' => 'used',
            'icon' => '♻️',
            'title' => 'Pre-Loved & Used',
            'tag' => 'Verified Second-Hand & Deals',
            'badge' => 'Re-Commerce',
            'color' => 'from-teal-500/10 to-cyan-500/10 text-teal-700 border-teal-200',
            'url' => k71_vertical_url('used'),
            'shape' => 'rounded-[2rem]',
        ],
        [
            'id' => 'enquiry',
            'icon' => '🏭',
            'title' => 'Machinery & B2B',
            'tag' => 'Industrial Tools & Bulk Supply',
            'badge' => 'Wholesale',
            'color' => 'from-cyan-500/10 to-blue-500/10 text-cyan-700 border-cyan-200',
            'url' => k71_vertical_url('enquiry'),
            'shape' => 'rounded-3xl',
        ],
    ];

    ];
@endphp

<x-layouts.public 
    :title="$seo['seo_meta_title'] ?? 'K71 • Curated Multi-Vendor Marketplace'"
    :metaDescription="$seo['seo_meta_description'] ?? 'Discover verified brand stores, boutique ateliers, and trending products on K71.'"
    :schemaJson="$schemaData"
>
    <!-- HERO SECTION WITH DYNAMIC GEOMETRIC SHAPES -->
    <div class="relative overflow-hidden mb-12 sm:mb-16">
        <!-- Geometric Ambient Mesh Shapes -->
        <div class="absolute -top-24 -left-20 w-80 h-80 rounded-full bg-gradient-to-tr from-indigo-500/20 via-purple-400/10 to-transparent blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/2 -right-24 w-96 h-96 rounded-full bg-gradient-to-bl from-cyan-400/20 via-blue-500/10 to-transparent blur-3xl pointer-events-none"></div>

        <div class="relative z-10 pt-4 sm:pt-8 pb-4 text-center max-w-4xl mx-auto px-4">
            
            <!-- Dynamic Geometric Pill Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white border border-slate-200/90 text-slate-900 text-xs font-black shadow-xs mb-5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>K71 UNIFIED MARKETPLACE</span>
                <span class="text-slate-300">•</span>
                <span class="text-indigo-600 font-bold">Verified Stores &amp; Secure Escrow</span>
            </div>

            <!-- Crisp Main Headline -->
            <h1 class="text-3xl sm:text-5xl font-black text-slate-950 tracking-tight leading-[1.15] mb-3">
                Discover Top Stores &amp; <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-cyan-600 bg-clip-text text-transparent">Trending Products</span>
            </h1>

            <p class="text-xs sm:text-sm text-slate-600 font-medium max-w-xl mx-auto leading-relaxed mb-6">
                Shop directly from authorized distributor storefronts, boutique ateliers, and certified suppliers nationwide.
            </p>

            <!-- Compact Liquid Geometric Search Box -->
            <div class="max-w-2xl mx-auto mb-5">
                <form action="{{ route('catalog.index') }}" method="GET" class="p-1.5 sm:p-2 rounded-2xl sm:rounded-full bg-white border-2 border-slate-200 shadow-lg shadow-slate-200/40 flex items-center gap-2 transition-all focus-within:border-indigo-600 focus-within:ring-4 focus-within:ring-indigo-100">
                    <div class="pl-3.5 text-indigo-600">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Search products, brands, model numbers, stores..." 
                        class="w-full bg-transparent border-none text-slate-950 text-xs sm:text-sm placeholder-slate-400 focus:ring-0 focus:outline-none font-semibold px-2"
                    >
                    <button 
                        type="submit" 
                        class="px-5 sm:px-6 py-2.5 rounded-xl sm:rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm tracking-wide shadow-md active:scale-95 transition-all shrink-0 cursor-pointer"
                    >
                        Search
                    </button>
                </form>
            </div>

            <!-- Quick Filter Chips with Diverse Shapes -->
            <div class="flex flex-wrap items-center justify-center gap-1.5 sm:gap-2 text-xs">
                <span class="text-slate-500 font-bold text-[11px] uppercase tracking-wider mr-1">Trending:</span>
                <a href="{{ route('catalog.index', ['q' => 'phone']) }}" class="px-3 py-1 rounded-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 font-semibold transition-all shadow-2xs hover:border-indigo-300">📱 5G Phones</a>
                <a href="{{ route('catalog.index', ['q' => 'fashion']) }}" class="px-3 py-1 rounded-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 font-semibold transition-all shadow-2xs hover:border-indigo-300">👕 Apparel</a>
                <a href="{{ route('catalog.index', ['q' => 'brake']) }}" class="px-3 py-1 rounded-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 font-semibold transition-all shadow-2xs hover:border-indigo-300">🛑 Auto Spares</a>
                <a href="{{ route('catalog.index', ['q' => 'laptop']) }}" class="px-3 py-1 rounded-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 font-semibold transition-all shadow-2xs hover:border-indigo-300">💻 Laptops</a>
                <a href="{{ route('catalog.index', ['q' => 'furniture']) }}" class="px-3 py-1 rounded-full bg-white hover:bg-slate-50 text-slate-800 border border-slate-200 font-semibold transition-all shadow-2xs hover:border-indigo-300">🛋️ Living</a>
            </div>

        </div>
    </div>

    <!-- SECTION 1: VERIFIED STORES & ATELIERS (STORES FIRST!) -->
    <section class="mb-14 sm:mb-18">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-indigo-50 text-indigo-700 border border-indigo-200">
                        Authorized Sellers
                    </span>
                    <span class="text-xs text-slate-400 font-mono">Storefronts</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1 flex items-center gap-2">
                    <span>🏪 Featured Stores &amp; Brands</span>
                </h2>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 group">
                <span>View All Stores</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>

        @if($topVendors->isNotEmpty())
            <!-- Dynamic Database Stores Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($topVendors as $vendor)
                    <div class="group bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-xl hover:border-indigo-300 transition-all flex flex-col justify-between">
                        <!-- Cover & Avatar -->
                        <div class="relative h-28 bg-gradient-to-r from-slate-800 to-indigo-900 overflow-hidden">
                            @if($vendor->cover_image_url)
                                <img src="{{ $vendor->cover_image_url }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-cover opacity-80 group-hover:scale-105 transition-transform duration-300">
                            @endif
                            <div class="absolute -bottom-5 left-4">
                                <div class="w-12 h-12 rounded-2xl bg-white p-1 border-2 border-white shadow-md overflow-hidden">
                                    @if($vendor->logo_url)
                                        <img src="{{ $vendor->logo_url }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-cover rounded-xl">
                                    @else
                                        <div class="w-full h-full bg-indigo-600 text-white font-black text-sm flex items-center justify-center rounded-xl">
                                            {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Info Body -->
                        <div class="p-4 pt-7 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <h3 class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors truncate">
                                        {{ $vendor->shop_name }}
                                    </h3>
                                    <span class="text-emerald-500 text-xs shrink-0" title="Verified Store">✓</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5 line-clamp-1">
                                    {{ $vendor->description ?? 'Authorized K71 Direct Seller' }}
                                </p>
                                <div class="flex items-center gap-2 mt-2 text-[10px] font-semibold text-slate-600">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700">
                                        {{ $vendor->products_count }} Products
                                    </span>
                                    <span class="text-amber-500 font-bold">★ 4.9</span>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-100">
                                <a href="{{ route('storefront.show', $vendor->slug) }}" class="w-full py-2 rounded-xl bg-slate-50 group-hover:bg-indigo-600 group-hover:text-white text-slate-800 text-xs font-bold text-center block transition-all">
                                    Visit Store &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Clean Seller Storefront Onboarding State -->
            <div class="p-8 sm:p-12 rounded-[2.5rem] bg-gradient-to-br from-white via-indigo-50/40 to-slate-50 border border-slate-200/90 shadow-2xs text-center relative overflow-hidden">
                <div class="max-w-xl mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg shadow-indigo-600/25">
                        🏪
                    </div>
                    <span class="text-[11px] font-black uppercase tracking-wider text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full border border-indigo-200/60 inline-block mb-2">
                        Zero Listing Fee Seller Onboarding
                    </span>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight mb-2">
                        Launch Your Branded Store on K71
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        Open your digital storefront with sector-tailored specifications, automated courier logistics, and direct PhonePe payouts.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('vendor.register') }}" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-md active:scale-95 transition-all">
                            Register Your Store &rarr;
                        </a>
                        <a href="{{ route('vendor.login') }}" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs sm:text-sm border border-slate-200 shadow-2xs transition-all">
                            Vendor Sign In
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- SECTION 2: TRENDING PRODUCTS & FRESH ARRIVALS (PRODUCTS SECTION!) -->
    <section class="mb-14 sm:mb-20">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                        Hot Arrivals
                    </span>
                    <span class="text-xs text-slate-400 font-mono">Real-Time</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight mt-1">
                    🔥 Trending Products &amp; Deals
                </h2>
            </div>

            <!-- Quick Category Pill Selector -->
            <div class="flex flex-wrap items-center gap-1.5">
                <a href="{{ route('catalog.index') }}" class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-600 text-white shadow-xs">All Items</a>
                <a href="{{ route('catalog.index', ['q' => 'electronics']) }}" class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">Electronics</a>
                <a href="{{ route('catalog.index', ['q' => 'fashion']) }}" class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">Fashion</a>
                <a href="{{ route('catalog.index', ['q' => 'spares']) }}" class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition">Auto Spares</a>
            </div>
        </div>

        @if($newArrivals->isNotEmpty())
            <!-- Dynamic Database Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3.5 sm:gap-6">
                @foreach($newArrivals as $prod)
                    @php
                        $cover = $prod->images->first()?->path ? asset('storage/' . $prod->images->first()->path) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80';
                        $pricePaise = $prod->base_price;
                        $comparePaise = $prod->compare_at_price;
                    @endphp
                    <div class="group bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs hover:shadow-xl hover:border-indigo-300 transition-all flex flex-col justify-between">
                        <a href="{{ route('product.show', $prod->slug) }}" class="block relative aspect-square bg-slate-100 overflow-hidden">
                            <img src="{{ $cover }}" alt="{{ $prod->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @if($comparePaise && $comparePaise > $pricePaise)
                                @php $pct = round((($comparePaise - $pricePaise) / $comparePaise) * 100); @endphp
                                <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-lg bg-rose-600 text-white font-black text-[10px] uppercase shadow-md">
                                    {{ $pct }}% OFF
                                </span>
                            @endif
                        </a>

                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                                    {{ $prod->category?->name ?? 'General' }}
                                </span>
                                <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-indigo-600 line-clamp-2 mt-0.5 transition-colors">
                                    <a href="{{ route('product.show', $prod->slug) }}">{{ $prod->title }}</a>
                                </h3>
                                @if($prod->vendor)
                                    <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                        <span>by</span>
                                        <span class="font-semibold text-slate-700 truncate">{{ $prod->vendor->shop_name }}</span>
                                    </p>
                                @endif
                            </div>

                            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-base sm:text-lg font-black text-slate-900 font-mono">
                                        ₹{{ number_format($pricePaise / 100, 2) }}
                                    </span>
                                    @if($comparePaise && $comparePaise > $pricePaise)
                                        <span class="text-xs text-slate-400 line-through font-mono ml-1">
                                            ₹{{ number_format($comparePaise / 100, 2) }}
                                        </span>
                                    @endif
                                </div>
                                <a href="{{ route('product.show', $prod->slug) }}" class="p-2 rounded-xl bg-slate-100 group-hover:bg-indigo-600 group-hover:text-white text-slate-700 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Clean Catalog Primed State -->
            <div class="p-8 sm:p-12 rounded-[2.5rem] bg-gradient-to-br from-white via-slate-50 to-indigo-50/30 border border-slate-200/90 shadow-2xs text-center relative overflow-hidden">
                <div class="max-w-xl mx-auto">
                    <div class="w-14 h-14 rounded-2xl bg-rose-500 text-white flex items-center justify-center text-2xl mx-auto mb-4 shadow-lg shadow-rose-500/25">
                        📦
                    </div>
                    <span class="text-[11px] font-black uppercase tracking-wider text-rose-600 bg-rose-50 px-3 py-1 rounded-full border border-rose-200/60 inline-block mb-2">
                        Production Marketplace Ready
                    </span>
                    <h3 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight mb-2">
                        The Catalog is Primed for Real Product Listings
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        All dummy products have been removed. Verified vendors can upload real listings with tailored sector attributes and inventory management.
                    </p>
                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('vendor.products.create') }}" class="px-6 py-3 rounded-xl bg-slate-950 hover:bg-slate-800 text-white font-bold text-xs sm:text-sm shadow-md active:scale-95 transition-all">
                            Upload First Listing &rarr;
                        </a>
                        <a href="{{ route('catalog.index') }}" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs sm:text-sm border border-slate-200 shadow-2xs transition-all">
                            Explore All Categories
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- SECTION 3: BROWSE BY SECTOR (GEOMETRIC SHAPE TILES) -->
    <section class="mb-14 sm:mb-20">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Structured Categories
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Explore retail and trade verticals tailored for specific sectors.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 group">
                <span>All Categories</span>
                <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
            </a>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
            @foreach($curatedSectors as $sec)
                <a href="{{ $sec['url'] }}" class="group p-4 bg-white {{ $sec['shape'] }} border border-slate-200/90 shadow-2xs hover:shadow-lg hover:border-indigo-400 transition-all flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl bg-gradient-to-br {{ $sec['color'] }} flex items-center justify-center text-xl shrink-0 group-hover:scale-110 transition-transform">
                        {{ $sec['icon'] }}
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors truncate">
                            {{ $sec['title'] }}
                        </h3>
                        <p class="text-[10px] text-slate-500 truncate mt-0.5">
                            {{ $sec['badge'] }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- SECTION 4: PLATFORM VALUE ASSURANCE (COMPACT SHAPES STRIP) -->
    <section class="mb-14">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-5xl mx-auto">
            <div class="p-4 rounded-3xl bg-white border border-slate-200/90 shadow-2xs text-center">
                <span class="text-2xl block mb-1">🛡️</span>
                <h4 class="text-xs font-black text-slate-950">Verified Sellers</h4>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">GST &amp; Aadhaar Verified</p>
            </div>
            <div class="p-4 rounded-3xl bg-white border border-slate-200/90 shadow-2xs text-center">
                <span class="text-2xl block mb-1">⚡</span>
                <h4 class="text-xs font-black text-slate-950">Instant PhonePe</h4>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">UPI, Cards &amp; NetBanking</p>
            </div>
            <div class="p-4 rounded-3xl bg-white border border-slate-200/90 shadow-2xs text-center">
                <span class="text-2xl block mb-1">📦</span>
                <h4 class="text-xs font-black text-slate-950">Tracked Logistics</h4>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">Shiprocket Express Courier</p>
            </div>
            <div class="p-4 rounded-3xl bg-white border border-slate-200/90 shadow-2xs text-center">
                <span class="text-2xl block mb-1">🔄</span>
                <h4 class="text-xs font-black text-slate-950">Buyer Protection</h4>
                <p class="text-[11px] text-slate-500 font-medium mt-0.5">7-Day Easy Returns</p>
            </div>
        </div>
    </section>

    <!-- SECTION 5: BECOME A VENDOR / SELLER INVITATION (ASYMMETRIC GEOMETRIC BANNER) -->
    <section class="mb-12">
        <div class="p-6 sm:p-10 rounded-[2.5rem] bg-gradient-to-r from-slate-950 via-indigo-950 to-slate-900 text-white shadow-2xl relative overflow-hidden border border-indigo-900/40">
            <div class="absolute -right-16 -bottom-16 w-64 h-64 rounded-full bg-cyan-500/20 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-6">
                <div class="max-w-xl text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-indigo-500/30 text-indigo-300 text-xs font-bold uppercase tracking-wider mb-2 border border-indigo-400/30">
                        ⚡ Seller Onboarding
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                        Sell on K71 with 0% Listing Fee
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-1.5 leading-relaxed">
                        Open your branded digital storefront in minutes. Enjoy instant PhonePe payouts, automated shipping, and access to thousands of verified buyers.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0 w-full sm:w-auto">
                    <a href="{{ route('vendor.register') }}" class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-white text-slate-950 font-black text-xs sm:text-sm hover:bg-slate-100 shadow-lg active:scale-95 transition-all text-center">
                        Register Your Store &rarr;
                    </a>
                    <a href="{{ route('vendor.login') }}" class="w-full sm:w-auto px-5 py-3 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 transition-all text-center">
                        Vendor Sign In
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
