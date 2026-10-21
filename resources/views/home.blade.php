@php
    $appUrl = url('/');
    $appName = config('marketplace.name', 'MarbleMandi');
    $hp = $homepageSettings ?? [];
    $seo = $seoSettings ?? [];

    $schemaData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => $appUrl . '/#website',
                'url' => $appUrl,
                'name' => $appName,
                'description' => 'India\'s Premier Multi-Sector Curated Marketplace',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('catalog.index') . '?q={search_term_string}',
                    'query-input' => 'required name=search_term_string'
                ]
            ],
            [
                '@type' => 'Organization',
                '@id' => $appUrl . '/#organization',
                'name' => $appName,
                'url' => $appUrl,
                'logo' => asset('icon-192x192.png'),
            ]
        ]
    ];

    $curatedSectors = [
        [
            'id' => 'electronics',
            'icon' => '📱',
            'title' => 'Electronics & IT',
            'tag' => 'Mobiles, Laptops & Audio',
            'badge' => 'Tech & Gadgets',
            'color' => 'from-blue-500/10 to-indigo-500/10 text-blue-700 border-blue-200',
            'url' => route('catalog.index', ['category' => 'electronics-appliances-it']),
            'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'clothing',
            'icon' => '👗',
            'title' => 'Fashion & Apparel',
            'tag' => 'Ethnic, Western & Footwear',
            'badge' => 'Curated Wardrobe',
            'color' => 'from-pink-500/10 to-rose-500/10 text-pink-700 border-pink-200',
            'url' => route('catalog.index', ['category' => 'clothing-fashion-accessories']),
            'image' => 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'automotive',
            'icon' => '🚗',
            'title' => 'Automotive & Spares',
            'tag' => 'OEM Parts, Oils & Accessories',
            'badge' => 'Vehicles & Parts',
            'color' => 'from-red-500/10 to-orange-500/10 text-red-700 border-red-200',
            'url' => route('catalog.index', ['category' => 'automotive-vehicles-transport']),
            'image' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'home_living',
            'icon' => '🛋️',
            'title' => 'Home & Living',
            'tag' => 'Furniture, Decor & Kitchen',
            'badge' => 'Living Spaces',
            'color' => 'from-emerald-500/10 to-teal-500/10 text-emerald-700 border-emerald-200',
            'url' => route('catalog.index', ['category' => 'home-kitchen-furniture-living']),
            'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'beauty',
            'icon' => '💄',
            'title' => 'Beauty & Wellness',
            'tag' => 'Skincare, Cosmetics & Fragrances',
            'badge' => 'Personal Care',
            'color' => 'from-fuchsia-500/10 to-purple-500/10 text-fuchsia-700 border-fuchsia-200',
            'url' => route('catalog.index', ['category' => 'beauty-personal-care-health-wellness']),
            'image' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'food',
            'icon' => '🍽️',
            'title' => 'Food & Restaurants',
            'tag' => 'Cloud Kitchens & Artisanal Food',
            'badge' => 'Dining Hub',
            'color' => 'from-amber-500/10 to-yellow-500/10 text-amber-700 border-amber-200',
            'url' => k71_vertical_url('food'),
            'image' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'services',
            'icon' => '🔧',
            'title' => 'Local Services',
            'tag' => 'Technicians, Repairs & Salon',
            'badge' => 'On-Demand',
            'color' => 'from-violet-500/10 to-indigo-500/10 text-violet-700 border-violet-200',
            'url' => k71_vertical_url('services'),
            'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'used',
            'icon' => '♻️',
            'title' => 'Pre-Loved / Used',
            'tag' => 'Verified Second-Hand & Deals',
            'badge' => 'C2C Marketplace',
            'color' => 'from-emerald-500/10 to-cyan-500/10 text-emerald-700 border-emerald-200',
            'url' => k71_vertical_url('used'),
            'image' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'enquiry',
            'icon' => '🏭',
            'title' => 'Machinery & B2B RFQ',
            'tag' => 'Marble Saws, Industrial & Steel',
            'badge' => 'Wholesale Quotes',
            'color' => 'from-orange-500/10 to-amber-500/10 text-orange-700 border-orange-200',
            'url' => k71_vertical_url('enquiry'),
            'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'toys_sports',
            'icon' => '🧸',
            'title' => 'Baby, Toys & Sports',
            'tag' => 'Fitness, Games & Infant Care',
            'badge' => 'Active Life',
            'color' => 'from-cyan-500/10 to-blue-500/10 text-cyan-700 border-cyan-200',
            'url' => route('catalog.index', ['category' => 'baby-toys-hobbies-books-sports']),
            'image' => 'https://images.unsplash.com/photo-1517649763962-0c623266ddc0?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'real_estate',
            'icon' => '🏢',
            'title' => 'Properties & Spaces',
            'tag' => 'Commercial, Rentals & Land',
            'badge' => 'Verified Leads',
            'color' => 'from-slate-500/10 to-zinc-500/10 text-slate-700 border-slate-200',
            'url' => route('catalog.index'),
            'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=500&q=80',
        ],
        [
            'id' => 'shorts',
            'icon' => '🎥',
            'title' => 'Shoppable Shorts',
            'tag' => 'Video Commerce & Demos',
            'badge' => 'Live Feed',
            'color' => 'from-rose-500/10 to-pink-500/10 text-rose-700 border-rose-200',
            'url' => k71_vertical_url('shorts'),
            'image' => 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=500&q=80',
        ],
    ];
@endphp

<x-layouts.public 
    :title="$seo['seo_meta_title'] ?? 'MarbleMandi • Curated Multi-Sector Marketplace'"
    :metaDescription="$seo['seo_meta_description'] ?? 'India\'s trusted multi-sector marketplace across verified retail, tech, automotive, food, and industrial sourcing.'"
    :schemaJson="$schemaData"
>
    <!-- Liquid Mesh Ambient Aura -->
    <div class="relative overflow-hidden mb-12 sm:mb-16">
        <div class="absolute -top-32 -left-32 w-96 sm:w-[600px] h-96 sm:h-[600px] rounded-full bg-gradient-to-tr from-indigo-200/50 via-purple-100/40 to-transparent blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 sm:w-[600px] h-96 sm:h-[600px] rounded-full bg-gradient-to-br from-cyan-200/40 via-blue-100/30 to-transparent blur-3xl pointer-events-none"></div>

        <!-- HERO SECTION -->
        <div class="relative z-10 pt-4 sm:pt-10 pb-8 text-center max-w-4xl mx-auto px-4">
            
            <!-- Quality Badge Pill -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white border border-slate-300 text-slate-900 text-xs font-black shadow-sm mb-6">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Verified Direct Marketplace</span>
                <span class="text-slate-300">•</span>
                <span class="text-indigo-600 font-black">Instant Checkout &amp; Secure Escrow</span>
            </div>

            <!-- Main Headline -->
            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-950 tracking-tight leading-[1.12] mb-5">
                Discover India's Premier <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-cyan-600 bg-clip-text text-transparent">Multi-Sector Marketplace</span>
            </h1>

            <p class="text-sm sm:text-base text-slate-700 font-medium max-w-2xl mx-auto leading-relaxed mb-8">
                Connect directly with verified brand distributors, boutique ateliers, certified automotive suppliers, and industrial suppliers nationwide.
            </p>

            <!-- Floating Liquid Glass Search Bar -->
            <div class="max-w-2xl mx-auto mb-6">
                <form action="{{ route('catalog.index') }}" method="GET" class="p-2 sm:p-2.5 rounded-2xl bg-white border-2 border-slate-200 shadow-xl shadow-slate-200/50 flex items-center gap-2 transition-all focus-within:border-indigo-600 focus-within:ring-4 focus-within:ring-indigo-100">
                    <div class="pl-3 text-indigo-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input 
                        type="text" 
                        name="q" 
                        placeholder="Search products, brands, model numbers, spare parts, fabrics..." 
                        class="w-full bg-transparent border-none text-slate-950 text-xs sm:text-sm placeholder-slate-400 focus:ring-0 focus:outline-none font-semibold px-2"
                    >
                    <button 
                        type="submit" 
                        class="px-5 sm:px-7 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-cyan-600 hover:from-indigo-700 hover:to-cyan-700 text-white font-black text-xs sm:text-sm tracking-wide shadow-md active:scale-95 transition-all shrink-0 cursor-pointer"
                    >
                        Search
                    </button>
                </form>
            </div>

            <!-- Trending Quick Tags -->
            <div class="flex flex-wrap items-center justify-center gap-2 text-xs">
                <span class="text-slate-700 font-extrabold text-xs uppercase tracking-wider">Popular Searches:</span>
                <a href="{{ route('catalog.index', ['q' => 'phone']) }}" class="px-3.5 py-1.5 rounded-full bg-white hover:bg-slate-50 text-slate-900 border border-slate-300 font-bold transition-all shadow-xs">📱 5G Smartphones</a>
                <a href="{{ route('catalog.index', ['q' => 'tshirt']) }}" class="px-3.5 py-1.5 rounded-full bg-white hover:bg-slate-50 text-slate-900 border border-slate-300 font-bold transition-all shadow-xs">👕 Cotton T-Shirts</a>
                <a href="{{ route('catalog.index', ['q' => 'brake']) }}" class="px-3.5 py-1.5 rounded-full bg-white hover:bg-slate-50 text-slate-900 border border-slate-300 font-bold transition-all shadow-xs">🛑 Brake Pads</a>
                <a href="{{ route('catalog.index', ['q' => 'laptop']) }}" class="px-3.5 py-1.5 rounded-full bg-white hover:bg-slate-50 text-slate-900 border border-slate-300 font-bold transition-all shadow-xs">💻 Laptops</a>
                <a href="{{ k71_vertical_url('food') }}" class="px-3.5 py-1.5 rounded-full bg-white hover:bg-slate-50 text-slate-900 border border-slate-300 font-bold transition-all shadow-xs">🍽️ Cloud Menus</a>
            </div>

            <!-- Platform Value Assurance Strip -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-10 pt-8 border-t border-slate-200/90 max-w-4xl mx-auto">
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs text-center">
                    <span class="text-2xl block mb-1">🛡️</span>
                    <h4 class="text-xs font-black text-slate-950">Verified Sellers</h4>
                    <p class="text-[11px] text-slate-600 font-medium mt-0.5">GST &amp; Aadhaar Verified</p>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs text-center">
                    <span class="text-2xl block mb-1">⚡</span>
                    <h4 class="text-xs font-black text-slate-950">Instant PhonePe</h4>
                    <p class="text-[11px] text-slate-600 font-medium mt-0.5">UPI, Cards &amp; NetBanking</p>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs text-center">
                    <span class="text-2xl block mb-1">📦</span>
                    <h4 class="text-xs font-black text-slate-950">Tracked Logistics</h4>
                    <p class="text-[11px] text-slate-600 font-medium mt-0.5">Shiprocket Express Courier</p>
                </div>
                <div class="p-4 rounded-2xl bg-white border border-slate-200 shadow-xs text-center">
                    <span class="text-2xl block mb-1">🔄</span>
                    <h4 class="text-xs font-black text-slate-950">Buyer Protection</h4>
                    <p class="text-[11px] text-slate-600 font-medium mt-0.5">7-Day Easy Returns</p>
                </div>
            </div>

        </div>
    </div>

    <!-- 12 TRADE VERTICALS SECTION -->
    <section class="mb-14 sm:mb-20">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Explore Marketplace Sectors
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Structured retail verticals tailored for specific industry workflows.
                </p>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 group">
                <span>View All</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </a>
        </div>

        <!-- Sector Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-5">
            @foreach($curatedSectors as $sec)
                <a href="{{ $sec['url'] }}" class="group relative rounded-2xl bg-white p-4 sm:p-5 border border-slate-200/90 shadow-xs hover:shadow-xl hover:border-indigo-400/50 transition-all duration-300 flex flex-col justify-between overflow-hidden">
                    <!-- Subtle Corner Gradient Accent -->
                    <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br {{ $sec['color'] }} rounded-bl-full opacity-60 group-hover:scale-110 transition-transform"></div>

                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl sm:text-3xl">{{ $sec['icon'] }}</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-md border {{ $sec['color'] }}">
                                {{ $sec['badge'] }}
                            </span>
                        </div>
                        <h3 class="text-sm sm:text-base font-extrabold text-slate-900 group-hover:text-indigo-600 transition-colors">
                            {{ $sec['title'] }}
                        </h3>
                        <p class="text-[11px] sm:text-xs text-slate-500 mt-1 leading-snug">
                            {{ $sec['tag'] }}
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-600">
                        <span>Browse Catalog</span>
                        <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- FEATURED PRODUCTS & FRESH ARRIVALS -->
    <section class="mb-14 sm:mb-20">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                    Trending Products &amp; Fresh Arrivals
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Curated authentic listings from authorized storefronts.
                </p>
            </div>
            <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-700 flex items-center gap-1 group">
                <span>View Full Catalog</span>
                <span class="group-hover:translate-x-0.5 transition-transform">&rarr;</span>
            </a>
        </div>

        @if($newArrivals->isNotEmpty())
            <!-- Dynamic Real Products Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($newArrivals as $prod)
                    @php
                        $cover = $prod->images->first()?->path ? asset('storage/' . $prod->images->first()->path) : 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=400&q=80';
                        $pricePaise = $prod->base_price;
                        $comparePaise = $prod->compare_at_price;
                    @endphp
                    <div class="group bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs hover:shadow-xl hover:border-slate-300 transition-all flex flex-col justify-between">
                        <a href="{{ route('product.show', $prod->slug) }}" class="block relative aspect-square bg-slate-100 overflow-hidden">
                            <img src="{{ $cover }}" alt="{{ $prod->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @if($comparePaise && $comparePaise > $pricePaise)
                                @php $pct = round((($comparePaise - $pricePaise) / $comparePaise) * 100); @endphp
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-rose-600 text-white font-black text-[10px] uppercase shadow">
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
                                        <span class="font-semibold text-slate-700">{{ $prod->vendor->shop_name }}</span>
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
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Fresh Launch State Spotlight (Database Clean & Ready for Uploads) -->
            <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-br from-indigo-50/80 via-white to-purple-50/80 border border-indigo-100 text-center shadow-sm relative overflow-hidden">
                <div class="max-w-xl mx-auto relative z-10">
                    <div class="w-16 h-16 rounded-3xl bg-white shadow-md text-indigo-600 flex items-center justify-center mx-auto mb-4 border border-indigo-100">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>

                    <span class="text-[11px] font-bold uppercase tracking-widest text-indigo-600 font-mono">
                        ✨ Fresh Marketplace Foundation
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight mt-1 mb-3">
                        Ready for Your First Catalog Upload
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed mb-6">
                        The platform has been freshly reset. All test data has been cleared. Sellers can now log into their vendor dashboard to upload authentic products with 1-tap category presets.
                    </p>

                    <div class="flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('vendor.register') }}" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs sm:text-sm shadow-md active:scale-95 transition-all">
                            Open Vendor Store &rarr;
                        </a>
                        <a href="{{ route('admin.login') }}" class="px-6 py-3 rounded-xl bg-white hover:bg-slate-50 text-slate-800 font-bold text-xs sm:text-sm border border-slate-200 shadow-2xs transition-all">
                            Master Admin Console
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </section>

    <!-- BECOME A VENDOR / SELLER INVITATION -->
    <section class="mb-12">
        <div class="p-8 sm:p-12 rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white shadow-2xl relative overflow-hidden">
            <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-cyan-500/20 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row items-center justify-between gap-8">
                <div class="max-w-xl text-center lg:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-indigo-500/30 text-indigo-300 text-xs font-bold uppercase tracking-wider mb-3 border border-indigo-400/30">
                        ⚡ Seller Onboarding
                    </span>
                    <h3 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                        Sell Across India with 0% Listing Fee
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 mt-2 leading-relaxed">
                        Whether you sell flagship electronics, automotive parts, apparel, or commercial machinery, MarbleMandi provides instant PhonePe payouts, automated Shiprocket shipping, and custom trade workflows.
                    </p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                    <a href="{{ route('vendor.register') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-white text-slate-950 font-black text-xs sm:text-sm hover:bg-slate-100 shadow-lg active:scale-95 transition-all text-center">
                        Register Your Store &rarr;
                    </a>
                    <a href="{{ route('vendor.login') }}" class="w-full sm:w-auto px-6 py-3.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/20 transition-all text-center">
                        Vendor Sign In
                    </a>
                </div>
            </div>
        </div>
    </section>

</x-layouts.public>
