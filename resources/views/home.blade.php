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
                'description' => 'India\'s Premier Multi-Vertical Curated Marketplace & Verified Stores',
                'potentialAction' => [
                    '@type' => 'SearchAction',
                    'target' => route('catalog.index') . '?search={search_term_string}',
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
@endphp

<x-layouts.public 
    :title="$seo['seo_meta_title'] ?? 'K71 • Next-Generation Commerce Ecosystem'"
    :metaDescription="$seo['seo_meta_description'] ?? 'Discover products, order food, hire professionals, find second-hand deals, connect with suppliers, and shop through short videos on K71.'"
    :schemaJson="$schemaData"
>
    <div class="space-y-16 sm:space-y-24 pb-16">

        <!-- ==========================================
             1. HERO SECTION — THE K71 MARKETPLACE IDENTITY
             ========================================== -->
        <section class="relative pt-4 sm:pt-10 overflow-hidden">
            <!-- Subtle Ambient Light Orbs -->
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none" aria-hidden="true"></div>
            <div class="absolute top-1/2 -right-32 w-96 h-96 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none" aria-hidden="true"></div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left Column: Copy & Actions (7 cols) -->
                    <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                        
                        <!-- Trust Pill Badge -->
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/90 border border-zinc-200/80 shadow-xs text-zinc-800 text-xs font-semibold backdrop-blur-md">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-mono uppercase text-[10.5px] text-zinc-500 font-bold tracking-wider">Next-Gen Marketplace</span>
                            <span class="text-zinc-300">•</span>
                            <span>6 Commerce Ecosystems in One</span>
                        </div>

                        <!-- Main Headline -->
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-zinc-950 tracking-tight leading-[1.12]">
                            Everything you need. <br class="hidden sm:inline">
                            <span class="bg-gradient-to-r from-zinc-950 via-indigo-900 to-zinc-700 bg-clip-text text-transparent">
                                One marketplace.
                            </span>
                        </h1>

                        <!-- Supporting Description -->
                        <p class="text-sm sm:text-base text-zinc-600 font-medium max-w-xl mx-auto lg:mx-0 leading-relaxed">
                            Discover products, order food, hire professionals, find second-hand deals, connect with suppliers, and shop through short videos.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-3 pt-2">
                            <a href="#marketplace-discovery" 
                               class="px-6 sm:px-7 py-3.5 rounded-2xl bg-zinc-950 hover:bg-zinc-800 text-white font-bold text-xs sm:text-sm shadow-md shadow-zinc-950/15 active:scale-95 transition-all flex items-center gap-2">
                                <span>Explore Marketplace</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <a href="{{ route('vendor.register') }}" 
                               class="px-6 sm:px-7 py-3.5 rounded-2xl bg-white hover:bg-zinc-50 text-zinc-900 font-bold text-xs sm:text-sm border border-zinc-200/90 shadow-xs active:scale-95 transition-all flex items-center gap-2">
                                <span>Start Selling</span>
                                <span class="text-zinc-400">&rarr;</span>
                            </a>
                        </div>

                        <!-- Micro Metrics -->
                        <div class="pt-4 flex items-center justify-center lg:justify-start gap-6 text-xs text-zinc-500 font-medium">
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-600 font-bold">✓</span>
                                <span>Verified Sellers</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-600 font-bold">✓</span>
                                <span>PhonePe Escrow</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="text-emerald-600 font-bold">✓</span>
                                <span>Instant WhatsApp Chat</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Interactive Floating Glass Preview Mosaic (5 cols) -->
                    <div class="lg:col-span-5 relative">
                        <div class="grid grid-cols-2 gap-3.5 p-2 rounded-3xl bg-zinc-100/60 border border-zinc-200/60 backdrop-blur-xl">
                            
                            <!-- Preview Tile 1: Shopping -->
                            @php $heroProd = $featuredProducts->first() ?? $newArrivals->first(); @endphp
                            <a href="{{ $heroProd ? route('product.show', $heroProd->slug) : route('catalog.index') }}"
                               class="group p-3.5 rounded-2xl bg-white/90 border border-zinc-200/80 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between">
                                <div class="aspect-square rounded-xl overflow-hidden bg-zinc-50 mb-2.5 relative">
                                    @if($heroProd && $heroProd->primary_image_url)
                                        <img src="{{ $heroProd->primary_image_url }}" alt="{{ $heroProd->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-2xl">🛍️</div>
                                    @endif
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-zinc-950/80 text-white text-[9px] font-bold uppercase tracking-wider">Shopping</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-zinc-900 truncate">{{ $heroProd?->title ?? 'Everyday Retail' }}</h4>
                                    <span class="text-xs font-extrabold text-zinc-950 mt-0.5 block">
                                        {{ $heroProd ? $heroProd->formatted_base_price : 'Verified Stores' }}
                                    </span>
                                </div>
                            </a>

                            <!-- Preview Tile 2: Food & Kitchens -->
                            @php $heroFood = $featuredFood->first(); @endphp
                            <a href="{{ k71_vertical_url('food') }}"
                               class="group p-3.5 rounded-2xl bg-white/90 border border-rose-200/70 hover:border-rose-300 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between">
                                <div class="aspect-square rounded-xl overflow-hidden bg-rose-50 mb-2.5 relative">
                                    @if($heroFood && $heroFood->primary_image_url)
                                        <img src="{{ $heroFood->primary_image_url }}" alt="{{ $heroFood->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-2xl">🍽️</div>
                                    @endif
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-rose-600/90 text-white text-[9px] font-bold uppercase tracking-wider">Foods</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-zinc-900 truncate">{{ $heroFood?->title ?? 'Artisan Menus' }}</h4>
                                    <span class="text-xs font-extrabold text-rose-700 mt-0.5 block">Explore Kitchens &rarr;</span>
                                </div>
                            </a>

                            <!-- Preview Tile 3: Freelance Services -->
                            @php $heroService = $featuredServices->first(); @endphp
                            <a href="{{ k71_vertical_url('services') }}"
                               class="group p-3.5 rounded-2xl bg-white/90 border border-purple-200/70 hover:border-purple-300 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between">
                                <div class="aspect-square rounded-xl overflow-hidden bg-purple-50 mb-2.5 relative">
                                    @if($heroService && $heroService->primary_image_url)
                                        <img src="{{ $heroService->primary_image_url }}" alt="{{ $heroService->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-2xl">⚡</div>
                                    @endif
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-purple-700/90 text-white text-[9px] font-bold uppercase tracking-wider">Services</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-zinc-900 truncate">{{ $heroService?->title ?? 'Talent & Crafts' }}</h4>
                                    <span class="text-xs font-extrabold text-purple-800 mt-0.5 block">Hire Specialists &rarr;</span>
                                </div>
                            </a>

                            <!-- Preview Tile 4: Shoppable Shorts -->
                            @php $heroShort = $latestShorts->first(); @endphp
                            <a href="{{ k71_vertical_url('shorts') }}"
                               class="group p-3.5 rounded-2xl bg-white/90 border border-zinc-200/80 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between">
                                <div class="aspect-square rounded-xl overflow-hidden bg-zinc-900 mb-2.5 relative text-white">
                                    @if($heroShort && $heroShort->thumbnail_url)
                                        <img src="{{ $heroShort->thumbnail_url }}" alt="{{ $heroShort->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-white text-3xl">▶</div>
                                    @endif
                                    <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-black uppercase tracking-wider">LIVE</span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-zinc-900 truncate">{{ $heroShort?->title ?? 'Shoppable Videos' }}</h4>
                                    <span class="text-xs font-extrabold text-zinc-950 mt-0.5 block">Watch &amp; Buy &rarr;</span>
                                </div>
                            </a>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- ==========================================
             2. UNIVERSAL SEARCH & QUICK CATEGORY DOCK
             ========================================== -->
        <section class="max-w-4xl mx-auto px-4">
            <div class="p-2 sm:p-2.5 rounded-3xl bg-white/90 border border-zinc-200/80 shadow-xl shadow-zinc-200/40 backdrop-blur-xl">
                <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="relative flex-1 w-full flex items-center">
                        <div class="pl-4 text-zinc-400">
                            <svg class="w-5 h-5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                        </div>
                        <input type="text" name="search" 
                               placeholder="Search across all categories, food, services, used items, suppliers..."
                               class="w-full px-3 py-3 bg-transparent text-xs sm:text-sm font-semibold text-zinc-950 placeholder-zinc-400 focus:outline-none">
                    </div>
                    <button type="submit" 
                            class="w-full sm:w-auto px-7 py-3 rounded-2xl bg-zinc-950 hover:bg-zinc-800 text-white font-bold text-xs sm:text-sm tracking-wide shadow-md active:scale-95 transition shrink-0 cursor-pointer">
                        Search
                    </button>
                </form>

                <!-- Quick Suggestions -->
                <div class="pt-2 px-3 pb-1 border-t border-zinc-100 mt-2 flex flex-wrap items-center gap-2 text-xs">
                    <span class="text-zinc-400 font-bold uppercase text-[10px] tracking-wider">Suggested:</span>
                    <a href="{{ route('catalog.index', ['search' => 'phone']) }}" class="px-2.5 py-0.5 rounded-full bg-zinc-50 hover:bg-zinc-100 text-zinc-700 text-xs font-medium border border-zinc-200/60 transition">Mobiles</a>
                    <a href="{{ k71_vertical_url('food') }}" class="px-2.5 py-0.5 rounded-full bg-zinc-50 hover:bg-zinc-100 text-zinc-700 text-xs font-medium border border-zinc-200/60 transition">Pizza &amp; Biryani</a>
                    <a href="{{ k71_vertical_url('services') }}" class="px-2.5 py-0.5 rounded-full bg-zinc-50 hover:bg-zinc-100 text-zinc-700 text-xs font-medium border border-zinc-200/60 transition">Home Cleaning &amp; Tech</a>
                    <a href="{{ k71_vertical_url('used') }}" class="px-2.5 py-0.5 rounded-full bg-zinc-50 hover:bg-zinc-100 text-zinc-700 text-xs font-medium border border-zinc-200/60 transition">Used Cars &amp; Furniture</a>
                    <a href="{{ k71_vertical_url('enquiry') }}" class="px-2.5 py-0.5 rounded-full bg-zinc-50 hover:bg-zinc-100 text-zinc-700 text-xs font-medium border border-zinc-200/60 transition">B2B Marble Slabs</a>
                </div>
            </div>
        </section>

        <!-- ==========================================
             3. CATEGORY DISCOVERY — THE SIGNATURE K71 EXPERIENCE
             ========================================== -->
        <section id="marketplace-discovery" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="text-center max-w-2xl mx-auto space-y-2">
                <span class="px-3 py-1 rounded-full text-[10.5px] font-extrabold uppercase tracking-widest bg-zinc-100 text-zinc-700 border border-zinc-200/80">
                    Signature Commerce Hub
                </span>
                <h2 class="text-2xl sm:text-4xl font-black text-zinc-950 tracking-tight">
                    Explore K71's 6 Commerce Verticals
                </h2>
                <p class="text-xs sm:text-sm text-zinc-600 font-medium">
                    Each vertical is purposefully built with tailored pricing, escrow protections, and direct communication.
                </p>
            </div>

            <!-- Distinctive Editorial Grid for 6 Verticals -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
                
                <!-- A. K71Mart — Everyday Shopping -->
                <div class="group p-6 sm:p-7 rounded-3xl bg-white/90 hover:bg-white border border-zinc-200/80 hover:border-zinc-300 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-zinc-100 text-zinc-900 border border-zinc-200 flex items-center justify-center text-2xl font-bold mb-4 group-hover:scale-110 transition-transform">
                            🛍️
                        </div>
                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider block">Everyday Shopping</span>
                        <h3 class="text-lg font-bold text-zinc-950 mt-1">K71Mart Retail Catalog</h3>
                        <p class="text-xs text-zinc-600 mt-2 leading-relaxed">
                            Discover verified brands, boutique fashion, electronics, and home essentials with express pan-India tracked delivery.
                        </p>
                    </div>
                    <div class="pt-6 mt-4 border-t border-zinc-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500">100% Genuine Items</span>
                        <a href="{{ route('catalog.index') }}" class="px-4 py-2 rounded-xl bg-zinc-950 text-white hover:bg-zinc-800 text-xs font-bold transition">
                            Shop Products &rarr;
                        </a>
                    </div>
                </div>

                <!-- B. Foods — Restaurant Discovery -->
                <div class="group p-6 sm:p-7 rounded-3xl bg-white/90 hover:bg-white border border-rose-200/70 hover:border-rose-300 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center text-2xl font-bold mb-4 group-hover:scale-110 transition-transform">
                            🍽️
                        </div>
                        <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Dining &amp; Kitchens</span>
                        <h3 class="text-lg font-bold text-zinc-950 mt-1">Foods &amp; Restaurants</h3>
                        <p class="text-xs text-zinc-600 mt-2 leading-relaxed">
                            Discover authentic cloud kitchens, regional restaurants, and curated menus delivered hot to your doorstep.
                        </p>
                    </div>
                    <div class="pt-6 mt-4 border-t border-rose-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500">Live Kitchens</span>
                        <a href="{{ k71_vertical_url('food') }}" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold transition shadow-xs">
                            Explore Foods &rarr;
                        </a>
                    </div>
                </div>

                <!-- C. Services — Hire Professionals -->
                <div class="group p-6 sm:p-7 rounded-3xl bg-white/90 hover:bg-white border border-purple-200/70 hover:border-purple-300 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center text-2xl font-bold mb-4 group-hover:scale-110 transition-transform">
                            ⚡
                        </div>
                        <span class="text-[10px] font-bold text-purple-700 uppercase tracking-wider block">Freelancers &amp; Talent</span>
                        <h3 class="text-lg font-bold text-zinc-950 mt-1">Services &amp; Craftsmanship</h3>
                        <p class="text-xs text-zinc-600 mt-2 leading-relaxed">
                            Hire vetted specialists for tech, video editing, branding, marble polishing, and home maintenance with milestone protection.
                        </p>
                    </div>
                    <div class="pt-6 mt-4 border-t border-purple-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500">Milestone Escrow</span>
                        <a href="{{ k71_vertical_url('services') }}" class="px-4 py-2 rounded-xl bg-purple-700 hover:bg-purple-600 text-white text-xs font-bold transition shadow-xs">
                            Explore Services &rarr;
                        </a>
                    </div>
                </div>

                <!-- D. Used — Second-Hand Marketplace -->
                <div class="group p-6 sm:p-7 rounded-3xl bg-white/90 hover:bg-white border border-teal-200/70 hover:border-teal-300 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-700 border border-teal-200 flex items-center justify-center text-2xl font-bold mb-4 group-hover:scale-110 transition-transform">
                            ♻️
                        </div>
                        <span class="text-[10px] font-bold text-teal-700 uppercase tracking-wider block">Pre-Owned Deals</span>
                        <h3 class="text-lg font-bold text-zinc-950 mt-1">Used Marketplace</h3>
                        <p class="text-xs text-zinc-600 mt-2 leading-relaxed">
                            Buy and sell pre-loved smartphones, vehicles, laptops, and home furniture directly with zero middlemen markups.
                        </p>
                    </div>
                    <div class="pt-6 mt-4 border-t border-teal-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500">Make an Offer</span>
                        <a href="{{ k71_vertical_url('used') }}" class="px-4 py-2 rounded-xl bg-teal-700 hover:bg-teal-600 text-white text-xs font-bold transition shadow-xs">
                            Explore Used Deals &rarr;
                        </a>
                    </div>
                </div>

                <!-- E. Enquiry — B2B & Wholesale -->
                <div class="group p-6 sm:p-7 rounded-3xl bg-white/90 hover:bg-white border border-amber-200/70 hover:border-amber-300 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-800 border border-amber-200 flex items-center justify-center text-2xl font-bold mb-4 group-hover:scale-110 transition-transform">
                            📋
                        </div>
                        <span class="text-[10px] font-bold text-amber-800 uppercase tracking-wider block">Wholesale &amp; Real Estate</span>
                        <h3 class="text-lg font-bold text-zinc-950 mt-1">B2B Enquiry &amp; RFQ</h3>
                        <p class="text-xs text-zinc-600 mt-2 leading-relaxed">
                            Post bulk buying requirements, compare verified factory quotations, source raw stone, industrial machinery, and real estate.
                        </p>
                    </div>
                    <div class="pt-6 mt-4 border-t border-amber-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-zinc-500">Structured RFQs</span>
                        <a href="{{ k71_vertical_url('enquiry') }}" class="px-4 py-2 rounded-xl bg-amber-800 hover:bg-amber-700 text-white text-xs font-bold transition shadow-xs">
                            Find Suppliers &rarr;
                        </a>
                    </div>
                </div>

                <!-- F. Shorts — Shoppable Videos -->
                <div class="group p-6 sm:p-7 rounded-3xl bg-white/90 hover:bg-white border border-rose-200/70 hover:border-rose-300 shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-zinc-950 text-white border border-zinc-800 flex items-center justify-center text-xl font-bold mb-4 group-hover:scale-110 transition-transform">
                            ▶
                        </div>
                        <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Shoppable Video Commerce</span>
                        <h3 class="text-lg font-bold text-zinc-950 mt-1">Shorts Hub</h3>
                        <p class="text-xs text-zinc-600 mt-2 leading-relaxed">
                            Experience products in motion through engaging short videos with instant buy tags and lucky spin discount rewards.
                        </p>
                    </div>
                    <div class="pt-6 mt-4 border-t border-zinc-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-rose-600 font-bold">Watch &amp; Shop</span>
                        <a href="{{ k71_vertical_url('shorts') }}" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-bold transition shadow-xs">
                            Watch Shorts &rarr;
                        </a>
                    </div>
                </div>

            </div>
        </section>

        <!-- ==========================================
             4. K71MART — TRENDING PRODUCTS & FRESH DEALS
             ========================================== -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/80">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600">Everyday Commerce</span>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight mt-0.5">
                        Trending Products on K71Mart
                    </h2>
                </div>
                <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-zinc-900 hover:text-indigo-600 transition flex items-center gap-1">
                    <span>View All Products</span>
                    <span>&rarr;</span>
                </a>
            </div>

            @if($featuredProducts->isNotEmpty() || $newArrivals->isNotEmpty())
                @php $displayProducts = $featuredProducts->isNotEmpty() ? $featuredProducts : $newArrivals; @endphp
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($displayProducts->take(8) as $prod)
                        <x-product-card :product="$prod" />
                    @endforeach
                </div>
            @else
                <div class="p-8 rounded-3xl bg-zinc-50 border border-zinc-200 text-center">
                    <p class="text-xs text-zinc-500">Products are currently being loaded from verified suppliers.</p>
                </div>
            @endif
        </section>

        <!-- ==========================================
             5. FOODS & RESTAURANTS SHOWCASE
             ========================================== -->
        @if($foodRestaurants->isNotEmpty() || $featuredFood->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/80">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600">Artisan Dining</span>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight mt-0.5">
                        Popular Restaurants &amp; Cloud Kitchens
                    </h2>
                </div>
                <a href="{{ k71_vertical_url('food') }}" class="text-xs font-bold text-rose-600 hover:text-rose-700 transition flex items-center gap-1">
                    <span>Explore All Menus</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach(($foodRestaurants->isNotEmpty() ? $foodRestaurants : $featuredFood)->take(4) as $item)
                    @php
                        $isVendor = $item instanceof \App\Models\Vendor;
                        $rTitle = $isVendor ? $item->shop_name : $item->title;
                        $rImg = $isVendor ? ($item->logo_url ?: $item->cover_url) : $item->primary_image_url;
                        $rUrl = $isVendor ? route('food.restaurant', $item->slug) : ($item->vendor ? route('food.restaurant', $item->vendor->slug) : k71_vertical_url('food'));
                    @endphp
                    <a href="{{ $rUrl }}" class="group bg-white rounded-3xl border border-zinc-200/80 hover:border-rose-300 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div class="relative aspect-video overflow-hidden bg-rose-50">
                            @if($rImg)
                                <img src="{{ format_image_url($rImg) }}" alt="{{ $rTitle }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-3xl">🍽️</div>
                            @endif
                            <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-slate-900/80 backdrop-blur-xs text-white text-[9.5px] font-black uppercase tracking-wider">
                                Kitchen
                            </span>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-2">
                            <div>
                                <h3 class="font-bold text-sm text-zinc-950 group-hover:text-rose-600 transition truncate">{{ $rTitle }}</h3>
                                <p class="text-xs text-zinc-500 mt-0.5">Verified hygiene &amp; express doorstep dispatch</p>
                            </div>
                            <div class="pt-2 border-t border-zinc-100 flex items-center justify-between text-xs">
                                <span class="text-emerald-700 font-bold">● Taking Orders</span>
                                <span class="font-bold text-rose-600">View Menu &rarr;</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        <!-- ==========================================
             6. SERVICES & FREELANCE SPECIALISTS SHOWCASE
             ========================================== -->
        @if($featuredServices->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/80">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-700">Milestone Escrow</span>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight mt-0.5">
                        Featured Services &amp; Specialists
                    </h2>
                </div>
                <a href="{{ k71_vertical_url('services') }}" class="text-xs font-bold text-purple-700 hover:text-purple-800 transition flex items-center gap-1">
                    <span>Browse All Services</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                @foreach($featuredServices->take(4) as $service)
                    @php
                        $basicPkg = $service->gigPackages->firstWhere('tier', 'basic') ?? $service->gigPackages->first();
                        $startingPrice = $basicPkg ? $basicPkg->price : ($service->base_price / 100);
                    @endphp
                    <div class="bg-white rounded-3xl border border-zinc-200/80 hover:border-purple-300 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between group">
                        <div class="relative aspect-video overflow-hidden bg-purple-50">
                            <img src="{{ $service->primary_image_url }}" alt="{{ $service->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full bg-purple-700/90 backdrop-blur-xs text-white text-[9.5px] font-black uppercase tracking-wider">
                                Verified Pro
                            </div>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                            <div>
                                <span class="text-[11px] font-bold text-purple-700 truncate block">{{ $service->vendor?->shop_name ?? 'Specialist' }}</span>
                                <h3 class="font-bold text-xs sm:text-sm text-zinc-900 group-hover:text-purple-700 transition line-clamp-2 leading-snug mt-1">
                                    {{ $service->title }}
                                </h3>
                            </div>
                            <div class="pt-3 border-t border-zinc-100 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] text-zinc-400 uppercase font-semibold">Starting at</span>
                                    <div class="text-sm sm:text-base font-black text-purple-900">₹{{ number_format($startingPrice) }}</div>
                                </div>
                                <a href="{{ route('services.show', $service->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-purple-50 group-hover:bg-purple-700 text-purple-700 group-hover:text-white text-xs font-bold transition">
                                    Packages &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        <!-- ==========================================
             7. USED & RE-COMMERCE SHOWCASE
             ========================================== -->
        @if($featuredUsed->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/80">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-teal-700">Pre-Loved Deals</span>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight mt-0.5">
                        Fresh Pre-Owned Listings
                    </h2>
                </div>
                <a href="{{ k71_vertical_url('used') }}" class="text-xs font-bold text-teal-700 hover:text-teal-800 transition flex items-center gap-1">
                    <span>Explore Used Deals</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                @foreach($featuredUsed->take(4) as $item)
                    <a href="{{ route('used.show', $item->slug) }}" class="group bg-white rounded-3xl border border-zinc-200/80 hover:border-teal-300 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between">
                        <div class="relative aspect-square overflow-hidden bg-teal-50">
                            <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            <span class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full bg-teal-800/90 text-white text-[9px] font-black uppercase tracking-wider">
                                {{ $item->getCustomField('condition', 'Pre-Owned') }}
                            </span>
                        </div>
                        <div class="p-3.5 space-y-1">
                            <h4 class="text-xs font-bold text-zinc-900 truncate group-hover:text-teal-700 transition">{{ $item->title }}</h4>
                            <div class="text-sm font-black text-zinc-950">{{ $item->formatted_base_price }}</div>
                            <span class="text-[10px] text-zinc-400 block">📍 {{ $item->getCustomField('city', 'India') }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        <!-- ==========================================
             8. B2B ENQUIRY & REAL ESTATE PREVIEW
             ========================================== -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/80">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-800">Business-to-Business</span>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight mt-0.5">
                        B2B Wholesale &amp; Open Requirements
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('enquiry.create') }}" class="px-3.5 py-1.5 rounded-full bg-amber-800 hover:bg-amber-700 text-white text-xs font-bold transition shadow-xs">
                        + Post Requirement
                    </a>
                    <a href="{{ k71_vertical_url('enquiry') }}" class="text-xs font-bold text-zinc-900 hover:text-amber-800 transition flex items-center gap-1">
                        <span>All RFQs</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>

            <!-- RFQs Grid -->
            @if($featuredEnquiries->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($featuredEnquiries->take(4) as $enq)
                        <div class="p-4 rounded-3xl bg-white/90 border border-zinc-200/80 shadow-xs hover:shadow-lg transition-all flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-center justify-between text-[10px] text-zinc-400 font-mono">
                                    <span>#{{ $enq->enquiry_number }}</span>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold uppercase">{{ $enq->status }}</span>
                                </div>
                                <h4 class="text-xs sm:text-sm font-bold text-zinc-950 mt-1.5 line-clamp-2 leading-snug">{{ $enq->title }}</h4>
                                <div class="mt-2 text-xs text-zinc-600 flex items-center gap-2">
                                    <span>Qty: <strong>{{ $enq->quantity }}</strong></span>
                                    <span>•</span>
                                    <span>📍 {{ $enq->city }}</span>
                                </div>
                            </div>
                            <div class="pt-2.5 border-t border-zinc-100 flex items-center justify-between text-xs">
                                <div>
                                    <span class="text-[10px] text-zinc-400 block uppercase">Budget</span>
                                    <span class="font-bold text-amber-800">{{ $enq->formatted_budget }}</span>
                                </div>
                                <a href="{{ route('enquiry.show', $enq->enquiry_number) }}" class="px-3 py-1 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-800 font-bold text-[11px] transition">
                                    Quote &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <!-- ==========================================
             9. SHOPPABLE SHORTS VIDEO ROW
             ========================================== -->
        @if($latestShorts->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200/80">
                <div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-rose-600">Video Commerce</span>
                    <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight mt-0.5">
                        Shop Trending Items in Motion
                    </h2>
                </div>
                <a href="{{ k71_vertical_url('shorts') }}" class="text-xs font-bold text-zinc-900 hover:text-rose-600 transition flex items-center gap-1">
                    <span>Watch Fullscreen Feed</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4">
                @foreach($latestShorts->take(6) as $short)
                    <a href="{{ route('shorts.show', $short->id) }}" class="group relative aspect-[9/16] rounded-3xl overflow-hidden bg-zinc-900 shadow-xs hover:shadow-xl transition-all duration-300 block">
                        @if($short->thumbnail_url)
                            <img src="{{ $short->thumbnail_url }}" alt="{{ $short->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-white text-3xl">▶</div>
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-black/20 p-3 flex flex-col justify-between text-white">
                            <span class="self-start px-2 py-0.5 rounded-full bg-rose-600 text-[9px] font-black uppercase tracking-wider">
                                LIVE
                            </span>
                            <div>
                                <h4 class="text-xs font-bold line-clamp-2 leading-snug drop-shadow-sm">{{ $short->title }}</h4>
                                <span class="text-[10px] text-zinc-300 mt-1 block">Watch &amp; Shop &rarr;</span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
        @endif

        <!-- ==========================================
             10. SELLER ACQUISITION — START SELLING
             ========================================== -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-8 sm:p-12 rounded-[2.5rem] bg-gradient-to-br from-zinc-950 via-zinc-900 to-zinc-950 text-white shadow-2xl relative overflow-hidden border border-zinc-800">
                <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-indigo-500/15 blur-3xl pointer-events-none" aria-hidden="true"></div>

                <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                    <div class="lg:col-span-8 space-y-3 text-center lg:text-left">
                        <span class="inline-block px-3 py-1 rounded-full bg-white/10 text-zinc-300 text-xs font-bold uppercase tracking-wider border border-white/10">
                            ⚡ Seller Onboarding
                        </span>
                        <h3 class="text-2xl sm:text-4xl font-black text-white tracking-tight">
                            Your business deserves a bigger marketplace.
                        </h3>
                        <p class="text-xs sm:text-sm text-zinc-300 max-w-xl leading-relaxed">
                            List retail products, offer freelance services, showcase culinary menus, sell eligible pre-owned items, or connect directly with wholesale buyers.
                        </p>
                    </div>

                    <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col items-stretch gap-3">
                        <a href="{{ route('vendor.register') }}" 
                           class="w-full py-3.5 px-6 rounded-2xl bg-white hover:bg-zinc-100 text-zinc-950 font-black text-xs sm:text-sm shadow-lg text-center transition active:scale-95">
                            Register Your Business &rarr;
                        </a>
                        <button type="button" 
                                onclick="if(window.openAuthModal) { window.openAuthModal(null, 'login', 'seller'); } else { window.location.href = '{{ route('login') }}'; }"
                                class="w-full py-3 px-6 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs sm:text-sm border border-white/15 text-center transition">
                            Supplier Sign In
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==========================================
             11. TRUST & PLATFORM CLARITY PILLARS
             ========================================== -->
        <section class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <div class="p-5 rounded-3xl bg-white/80 border border-zinc-200/80 shadow-xs text-center">
                    <span class="text-2xl block mb-1.5">🛡️</span>
                    <h4 class="text-xs font-bold text-zinc-950">Verified Sellers</h4>
                    <p class="text-[11px] text-zinc-500 mt-0.5">GST &amp; Aadhaar Verified Suppliers</p>
                </div>
                <div class="p-5 rounded-3xl bg-white/80 border border-zinc-200/80 shadow-xs text-center">
                    <span class="text-2xl block mb-1.5">⚡</span>
                    <h4 class="text-xs font-bold text-zinc-950">PhonePe Escrow</h4>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Safe automated settlements</p>
                </div>
                <div class="p-5 rounded-3xl bg-white/80 border border-zinc-200/80 shadow-xs text-center">
                    <span class="text-2xl block mb-1.5">📦</span>
                    <h4 class="text-xs font-bold text-zinc-950">Tracked Logistics</h4>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Express nationwide courier delivery</p>
                </div>
                <div class="p-5 rounded-3xl bg-white/80 border border-zinc-200/80 shadow-xs text-center">
                    <span class="text-2xl block mb-1.5">💬</span>
                    <h4 class="text-xs font-bold text-zinc-950">Direct Communication</h4>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Instant WhatsApp buyer-seller link</p>
                </div>
            </div>
        </section>

    </div>
</x-layouts.public>
