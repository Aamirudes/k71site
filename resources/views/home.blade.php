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
                'description' => 'India\'s Premier Direct Product Showcase & Curated Multi-Vertical Marketplace',
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

    // Combine products for a rich catalog showcase
    $allCatalogProducts = $featuredProducts->merge($newArrivals)->unique('id');
@endphp

<x-layouts.public 
    :title="$seo['seo_meta_title'] ?? 'K71 • Direct Product Showcase & Deals Hub'"
    :metaDescription="$seo['seo_meta_description'] ?? 'Shop directly from verified Indian sellers: trending fashion, mobiles, electronics, artisan dining, certified services, and budget deals with PhonePe escrow protection.'"
    :schemaJson="$schemaData"
    :fullWidth="true"
>
    <!-- Background Ethereal Canvas Mesh -->
    <div class="relative w-full overflow-hidden bg-[#FAFAF9] dark:bg-zinc-950 text-zinc-900 dark:text-zinc-100">
        
        <!-- Ambient Luminous Glow Orbs (Pure CSS, Non-blocking) -->
        <div class="absolute top-0 left-1/4 -translate-x-1/2 w-[700px] h-[500px] rounded-full bg-gradient-to-tr from-slate-200/50 via-indigo-100/30 to-transparent dark:from-indigo-950/20 blur-[140px] pointer-events-none" aria-hidden="true"></div>
        <div class="absolute top-80 right-0 w-[600px] h-[600px] rounded-full bg-gradient-to-bl from-amber-100/40 via-rose-100/25 to-transparent dark:from-rose-950/20 blur-[160px] pointer-events-none" aria-hidden="true"></div>
        <div class="absolute top-[1800px] left-0 w-[800px] h-[700px] rounded-full bg-gradient-to-r from-teal-100/30 via-slate-100/40 to-transparent dark:from-teal-950/20 blur-[150px] pointer-events-none" aria-hidden="true"></div>

        <!-- ==========================================
             0. FLIPKART & MEESHO TOP CATEGORY SLIDER (STICKY & RESPONSIVE)
             ========================================== -->
        <section class="w-full bg-white/95 dark:bg-zinc-900/95 border-b border-black/[0.05] dark:border-white/10 shadow-[0_2px_12px_rgba(0,0,0,0.03)] backdrop-blur-xl sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-2.5 sm:py-3.5">
                <div class="flex items-center gap-4 sm:gap-7 overflow-x-auto no-scrollbar scroll-smooth py-1 px-1 touch-pan-x" 
                     style="scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                    
                    <!-- 1. All Catalog (K71Mart) -->
                    <a href="{{ route('catalog.index') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-zinc-100 to-zinc-50 dark:from-zinc-800 dark:to-zinc-700 border border-black/[0.08] dark:border-white/10 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-black/20 transition-all duration-300 relative">
                            🛍️
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-rose-600 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">HOT</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">All Deals</span>
                    </a>

                    <!-- 2. Mobiles & Tech -->
                    <a href="{{ route('catalog.index', ['search' => 'phone']) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-sky-50 to-indigo-50 dark:from-sky-950/40 dark:to-indigo-950/40 border border-sky-100 dark:border-sky-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-sky-300 transition-all duration-300 relative">
                            📱
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-sky-600 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">5G</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Mobiles</span>
                    </a>

                    <!-- 3. Fashion & Apparel -->
                    <a href="{{ route('catalog.index', ['search' => 'saree']) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-pink-50 to-rose-50 dark:from-pink-950/40 dark:to-rose-950/40 border border-pink-100 dark:border-pink-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-pink-300 transition-all duration-300 relative">
                            👗
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-pink-600 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">SALE</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Fashion</span>
                    </a>

                    <!-- 4. Electronics -->
                    <a href="{{ route('catalog.index', ['search' => 'laptop']) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-indigo-50 to-purple-50 dark:from-indigo-950/40 dark:to-purple-950/40 border border-indigo-100 dark:border-indigo-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-indigo-300 transition-all duration-300 relative">
                            💻
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Electronics</span>
                    </a>

                    <!-- 5. Cloud Dining & Food -->
                    <a href="{{ k71_vertical_url('food') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/40 border border-amber-200/60 dark:border-amber-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-amber-400 transition-all duration-300 relative">
                            🍲
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-amber-600 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">FRESH</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Dining</span>
                    </a>

                    <!-- 6. Professional Services -->
                    <a href="{{ k71_vertical_url('services') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-purple-50 to-violet-50 dark:from-purple-950/40 dark:to-violet-950/40 border border-purple-200/60 dark:border-purple-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-purple-400 transition-all duration-300 relative">
                            ⚡
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-purple-700 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">PRO</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Services</span>
                    </a>

                    <!-- 7. Pre-Owned Marketplace -->
                    <a href="{{ k71_vertical_url('used') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-teal-50 to-emerald-50 dark:from-teal-950/40 dark:to-emerald-950/40 border border-teal-200/60 dark:border-teal-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-teal-400 transition-all duration-300 relative">
                            ♻️
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-teal-700 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">DEALS</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Pre-Owned</span>
                    </a>

                    <!-- 8. B2B Wholesale RFQs -->
                    <a href="{{ k71_vertical_url('enquiry') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-amber-50 to-yellow-50 dark:from-amber-950/40 dark:to-yellow-950/40 border border-amber-300/60 dark:border-amber-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-amber-500 transition-all duration-300 relative">
                            📋
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-amber-800 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">B2B</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Wholesale</span>
                    </a>

                    <!-- 9. Shoppable Video Reels -->
                    <a href="{{ k71_vertical_url('shorts') }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-gradient-to-tr from-rose-50 to-red-50 dark:from-rose-950/40 dark:to-red-950/40 border border-rose-200/60 dark:border-rose-800/40 shadow-xs flex items-center justify-center text-xl sm:text-2xl group-hover:scale-108 group-hover:shadow-md group-hover:border-rose-400 transition-all duration-300 relative">
                            ▶
                            <span class="absolute -top-1.5 -right-1 px-1.5 py-0.2 rounded-full bg-rose-600 text-[8px] font-black text-white uppercase tracking-wider shadow-xs">LIVE</span>
                        </div>
                        <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">Reels</span>
                    </a>

                    <!-- Real Database Categories -->
                    @if(!empty($categories))
                        @foreach($categories->take(6) as $cat)
                            <a href="{{ route('catalog.index', ['category' => $cat->slug]) }}" class="group flex flex-col items-center shrink-0 w-[58px] sm:w-[72px] text-center cursor-pointer">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-white dark:bg-zinc-800 border border-black/[0.08] dark:border-white/10 shadow-xs flex items-center justify-center text-lg overflow-hidden group-hover:scale-108 group-hover:shadow-md group-hover:border-black/20 transition-all duration-300 relative">
                                    @if($cat->sample_image)
                                        <img src="{{ \App\Services\ImageService::url($cat->sample_image, 120) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-zinc-700 dark:text-zinc-300 font-bold text-xs uppercase">{{ substr($cat->name, 0, 2) }}</span>
                                    @endif
                                </div>
                                <span class="text-[10px] sm:text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-black dark:group-hover:text-white mt-1.5 tracking-tight truncate w-full">{{ $cat->name }}</span>
                            </a>
                        @endforeach
                    @endif

                </div>
            </div>
        </section>

        <div class="relative z-10 space-y-10 sm:space-y-14 pb-20 sm:pb-28">

            <!-- ==========================================
                 1. HIGH-IMPACT PROMOTIONAL PRODUCT HERO (AMAZON & MEESHO STYLE)
                 ========================================== -->
            <section class="pt-4 sm:pt-6 max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 items-stretch">
                    
                    <!-- Main High-Converting Hero Banner (8 cols) -->
                    <div class="lg:col-span-8 rounded-[2rem] sm:rounded-[2.5rem] bg-gradient-to-br from-indigo-950 via-slate-900 to-zinc-950 text-white p-6 sm:p-10 shadow-xl relative overflow-hidden flex flex-col justify-between border border-white/10 min-h-[340px] sm:min-h-[420px]">
                        <!-- Ambient luminous glow inside banner -->
                        <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-rose-500/20 blur-[90px] pointer-events-none" aria-hidden="true"></div>
                        <div class="absolute -left-20 -bottom-20 w-80 h-80 rounded-full bg-indigo-500/20 blur-[90px] pointer-events-none" aria-hidden="true"></div>

                        <!-- Top Tagline & Trust Chip -->
                        <div class="relative z-10 flex flex-wrap items-center gap-2.5">
                            <span class="px-3 py-1 rounded-full bg-rose-600 text-white text-[10px] sm:text-xs font-black uppercase tracking-wider shadow-sm">
                                🔥 Mega Product Bazaar
                            </span>
                            <span class="px-3 py-1 rounded-full bg-white/10 text-zinc-300 text-[10px] sm:text-xs font-bold backdrop-blur-md border border-white/10">
                                ⚡ Lowest Prices Guaranteed
                            </span>
                        </div>

                        <!-- Hero Main Headline: Direct Product Value -->
                        <div class="relative z-10 my-4 sm:my-6 space-y-2 sm:space-y-3">
                            <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-[1.08] text-white">
                                Direct Products. <br>
                                <span class="bg-gradient-to-r from-amber-300 via-rose-300 to-pink-400 bg-clip-text text-transparent">
                                    Factory Direct Prices.
                                </span>
                            </h1>
                            <p class="text-xs sm:text-base text-zinc-300 max-w-xl font-normal leading-relaxed">
                                Shop trending fashion, smartphones, home living, cloud dining, and marble craft directly from verified Indian sellers with PhonePe escrow protection.
                            </p>
                        </div>

                        <!-- Instant Action Links & Trust Badges -->
                        <div class="relative z-10 space-y-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <a href="#product-showcase" 
                                   class="px-6 sm:px-8 py-3.5 sm:py-4 rounded-2xl bg-white hover:bg-zinc-100 text-zinc-950 font-black text-xs sm:text-sm tracking-wide shadow-lg active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                                    <span>Browse All Products</span>
                                    <span>&darr;</span>
                                </a>
                                <a href="{{ route('catalog.index', ['sort' => 'price_low']) }}" 
                                   class="px-6 sm:px-8 py-3.5 sm:py-4 rounded-2xl bg-white/15 hover:bg-white/25 text-white font-bold text-xs sm:text-sm tracking-wide border border-white/20 backdrop-blur-md active:scale-95 transition-all flex items-center gap-2 cursor-pointer">
                                    <span>₹199 Budget Store</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>

                            <!-- Trust Mini Badges -->
                            <div class="flex flex-wrap items-center gap-4 sm:gap-6 text-[11px] sm:text-xs text-zinc-300 font-medium pt-1">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-black">✓</span>
                                    <span>Free Delivery on 1st Order</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-black">✓</span>
                                    <span>7-Day Easy Returns</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-[10px] font-black">✓</span>
                                    <span>PhonePe Safe Escrow</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: 2 Quick-Pick Product Tiles (4 cols) -->
                    <div class="lg:col-span-4 grid grid-cols-2 lg:grid-cols-1 gap-3.5 sm:gap-4">
                        @php 
                            $tileProd1 = $featuredProducts->first() ?? $newArrivals->first();
                            $tileProd2 = $featuredProducts->skip(1)->first() ?? $newArrivals->skip(1)->first();
                        @endphp

                        <!-- Tile 1 -->
                        @if($tileProd1)
                        <a href="{{ route('product.show', $tileProd1->slug) }}" 
                           class="group p-3.5 sm:p-4 rounded-[1.8rem] bg-white dark:bg-zinc-900 border border-black/[0.06] dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-700 dark:text-amber-300 font-black text-[9px] uppercase tracking-wider">
                                    ⭐ Trending Pick
                                </span>
                                @if($tileProd1->compare_at_price > $tileProd1->base_price)
                                    <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-black text-[9px]">
                                        {{ round((($tileProd1->compare_at_price - $tileProd1->base_price) / $tileProd1->compare_at_price) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>
                            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 mb-2 relative">
                                @if($tileProd1->primary_image_url)
                                    <img src="{{ $tileProd1->primary_image_url }}" alt="{{ $tileProd1->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🛍️</div>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-zinc-950 dark:text-white truncate">{{ $tileProd1->title }}</h4>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-sm font-black text-zinc-950 dark:text-white">{{ $tileProd1->formatted_base_price }}</span>
                                    @if($tileProd1->compare_at_price > $tileProd1->base_price)
                                        <span class="text-[10px] text-zinc-400 line-through">{{ $tileProd1->formatted_compare_at_price }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endif

                        <!-- Tile 2 -->
                        @if($tileProd2)
                        <a href="{{ route('product.show', $tileProd2->slug) }}" 
                           class="group p-3.5 sm:p-4 rounded-[1.8rem] bg-white dark:bg-zinc-900 border border-black/[0.06] dark:border-white/10 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-black text-[9px] uppercase tracking-wider">
                                    ✨ Best Value
                                </span>
                                @if($tileProd2->compare_at_price > $tileProd2->base_price)
                                    <span class="px-1.5 py-0.5 rounded bg-rose-600 text-white font-black text-[9px]">
                                        {{ round((($tileProd2->compare_at_price - $tileProd2->base_price) / $tileProd2->compare_at_price) * 100) }}% OFF
                                    </span>
                                @endif
                            </div>
                            <div class="aspect-[4/3] rounded-xl overflow-hidden bg-zinc-100 dark:bg-zinc-800 mb-2 relative">
                                @if($tileProd2->primary_image_url)
                                    <img src="{{ $tileProd2->primary_image_url }}" alt="{{ $tileProd2->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">👗</div>
                                @endif
                            </div>
                            <div>
                                <h4 class="text-xs font-bold text-zinc-950 dark:text-white truncate">{{ $tileProd2->title }}</h4>
                                <div class="flex items-baseline gap-2 mt-1">
                                    <span class="text-sm font-black text-zinc-950 dark:text-white">{{ $tileProd2->formatted_base_price }}</span>
                                    @if($tileProd2->compare_at_price > $tileProd2->base_price)
                                        <span class="text-[10px] text-zinc-400 line-through">{{ $tileProd2->formatted_compare_at_price }}</span>
                                    @endif
                                </div>
                            </div>
                        </a>
                        @endif

                    </div>

                </div>
            </section>

            <!-- ==========================================
                 2. MEESHO & AMAZON SIGNATURE: SHOP BY BUDGET HUB
                 ========================================== -->
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="p-4 sm:p-6 rounded-[2rem] bg-white/90 dark:bg-zinc-900/90 border border-black/[0.06] dark:border-white/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] backdrop-blur-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xl">🏷️</span>
                            <h2 class="text-base sm:text-xl font-black text-zinc-950 dark:text-white tracking-tight">Shop by Budget &amp; Deals</h2>
                        </div>
                        <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white flex items-center gap-1">
                            <span>All Price Stores</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <!-- 4 Budget Corners Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        
                        <!-- 1. Under 199 Store -->
                        <a href="{{ route('catalog.index', ['max_price' => 199]) }}" 
                           class="group p-3.5 sm:p-4 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50/60 dark:from-amber-950/20 dark:to-orange-950/20 border border-amber-200/80 dark:border-amber-800/40 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl mb-2 font-black">
                                ₹
                            </div>
                            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-widest block">Daily Value</span>
                            <h3 class="text-sm sm:text-base font-black text-zinc-950 dark:text-white mt-0.5">Under ₹199 Store</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">Everyday essentials &amp; accessories</p>
                        </a>

                        <!-- 2. Under 499 Store -->
                        <a href="{{ route('catalog.index', ['max_price' => 499]) }}" 
                           class="group p-3.5 sm:p-4 rounded-2xl bg-gradient-to-br from-pink-50 to-rose-50/60 dark:from-pink-950/20 dark:to-rose-950/20 border border-pink-200/80 dark:border-pink-800/40 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-xl bg-pink-500/10 text-pink-600 flex items-center justify-center text-xl mb-2 font-black">
                                👗
                            </div>
                            <span class="text-[10px] font-bold text-pink-700 uppercase tracking-widest block">Popular</span>
                            <h3 class="text-sm sm:text-base font-black text-zinc-950 dark:text-white mt-0.5">Under ₹499 Store</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">Trending fashion &amp; gadget picks</p>
                        </a>

                        <!-- 3. Under 999 Store -->
                        <a href="{{ route('catalog.index', ['max_price' => 999]) }}" 
                           class="group p-3.5 sm:p-4 rounded-2xl bg-gradient-to-br from-indigo-50 to-sky-50/60 dark:from-indigo-950/20 dark:to-sky-950/20 border border-indigo-200/80 dark:border-indigo-800/40 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center text-xl mb-2 font-black">
                                💎
                            </div>
                            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest block">Boutiques</span>
                            <h3 class="text-sm sm:text-base font-black text-zinc-950 dark:text-white mt-0.5">Under ₹999 Store</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">Marble handicrafts &amp; ethnic wear</p>
                        </a>

                        <!-- 4. Flat 50-80% Off Clearance -->
                        <a href="{{ route('catalog.index', ['sort' => 'discount']) }}" 
                           class="group p-3.5 sm:p-4 rounded-2xl bg-gradient-to-br from-rose-50 to-red-50/60 dark:from-rose-950/20 dark:to-red-950/20 border border-rose-200/80 dark:border-rose-800/40 hover:shadow-md transition-all">
                            <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 flex items-center justify-center text-xl mb-2 font-black">
                                🔥
                            </div>
                            <span class="text-[10px] font-bold text-rose-700 uppercase tracking-widest block">Steal Deals</span>
                            <h3 class="text-sm sm:text-base font-black text-zinc-950 dark:text-white mt-0.5">Flat 50-80% OFF</h3>
                            <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mt-1 line-clamp-1">Verified factory clearance stock</p>
                        </a>

                    </div>
                </div>
            </section>

            <!-- ==========================================
                 3. UNIVERSAL DYNAMIC SEARCH DOCK (FAST & COMPACT)
                 ========================================== -->
            <section class="max-w-4xl mx-auto px-3 sm:px-6">
                <div class="p-3 sm:p-4 rounded-[2rem] bg-white/90 dark:bg-zinc-900/90 border border-black/[0.08] dark:border-white/10 shadow-[0_12px_40px_rgba(0,0,0,0.04)] backdrop-blur-2xl relative"
                     x-data="{
                        query: '',
                        results: [],
                        loading: false,
                        isOpen: false,
                        fetchSuggestions() {
                            if(this.query.trim().length < 2) {
                                this.results = [];
                                this.isOpen = false;
                                return;
                            }
                            this.loading = true;
                            fetch('/api/search/suggest?q=' + encodeURIComponent(this.query))
                                .then(r => r.json())
                                .then(data => {
                                    this.results = data.results || [];
                                    this.isOpen = true;
                                    this.loading = false;
                                })
                                .catch(() => { this.loading = false; });
                        }
                     }"
                     @click.away="isOpen = false">
                    
                    <!-- Search Input Form -->
                    <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                        <div class="relative flex-1 w-full flex items-center">
                            <div class="pl-3.5 text-zinc-400">
                                <svg class="w-5 h-5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" x-model="query"
                                   @input.debounce.250ms="fetchSuggestions()"
                                   @focus="if(results.length > 0) isOpen = true"
                                   autocomplete="off"
                                   placeholder="Search 10,000+ products, fashion, mobiles, cloud dining, services..."
                                   class="w-full px-3.5 py-3 bg-transparent text-sm font-medium text-zinc-950 dark:text-white placeholder-zinc-400 focus:outline-none">
                            
                            <div x-show="loading" class="pr-3 text-zinc-400" style="display: none;">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full sm:w-auto px-7 py-3 rounded-xl bg-zinc-950 hover:bg-black dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 font-bold text-xs sm:text-sm tracking-wide shadow-md active:scale-95 transition shrink-0 cursor-pointer">
                            Search Products
                        </button>
                    </form>

                    <!-- Live Dynamic Suggestions Dropdown -->
                    <div x-show="isOpen && (results.length > 0 || loading)" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute top-full left-0 right-0 mt-3 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-2xl border border-black/[0.08] dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden z-50 divide-y divide-zinc-100 dark:divide-zinc-800"
                         style="display: none;">
                        
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3.5 p-3 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition group">
                                <img :src="item.thumb" :alt="item.title" class="w-12 h-12 rounded-xl object-cover border border-zinc-200 dark:border-zinc-700 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-zinc-950 dark:text-white truncate group-hover:text-black" x-text="item.title"></p>
                                    <div class="flex items-center gap-2.5 text-[11px] text-zinc-500 mt-0.5">
                                        <span class="font-mono font-bold text-zinc-900 dark:text-zinc-100" x-text="item.price"></span>
                                        <span class="uppercase px-1.5 py-0.2 rounded bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 font-bold text-[9px]" x-text="item.vertical"></span>
                                        <span class="truncate text-zinc-400" x-text="item.vendor"></span>
                                    </div>
                                </div>
                                <span class="text-zinc-400 group-hover:text-zinc-900 dark:group-hover:text-white group-hover:translate-x-1 transition-all text-xs font-bold pr-2">&rarr;</span>
                            </a>
                        </template>

                        <div x-show="results.length > 0" class="p-2.5 bg-zinc-50/90 dark:bg-zinc-800/90 text-center">
                            <a :href="'{{ route('catalog.index') }}?search=' + encodeURIComponent(query)" class="text-xs font-bold text-zinc-800 dark:text-zinc-200 hover:text-black">
                                See all matching items for "<span x-text="query"></span>" &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Trending Search Keywords -->
                    <div class="pt-2.5 px-2 pb-0.5 border-t border-black/[0.04] dark:border-white/10 mt-2 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-zinc-400 font-bold uppercase text-[10px] tracking-widest">Trending:</span>
                        <a href="{{ route('catalog.index', ['search' => 'silk']) }}" class="px-2.5 py-1 rounded-full bg-zinc-100/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[11px] font-medium transition">Silk Sarees</a>
                        <a href="{{ route('catalog.index', ['search' => 'phone']) }}" class="px-2.5 py-1 rounded-full bg-zinc-100/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[11px] font-medium transition">5G Mobiles</a>
                        <a href="{{ k71_vertical_url('food') }}" class="px-2.5 py-1 rounded-full bg-zinc-100/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[11px] font-medium transition">Biryani &amp; Sweets</a>
                        <a href="{{ k71_vertical_url('services') }}" class="px-2.5 py-1 rounded-full bg-zinc-100/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[11px] font-medium transition">Marble Polishing</a>
                        <a href="{{ k71_vertical_url('used') }}" class="px-2.5 py-1 rounded-full bg-zinc-100/80 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 text-[11px] font-medium transition">Used iPhones</a>
                    </div>
                </div>
            </section>

            <!-- ==========================================
                 4. TRENDING DEALS OF THE DAY (AMAZON LIGHTNING SLIDER)
                 ========================================== -->
            @php 
                $dealsProducts = $featuredProducts->isNotEmpty() ? $featuredProducts : $newArrivals;
            @endphp
            @if($dealsProducts->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8" x-data="{
                scrollLeft() {
                    this.$refs.dealsTrack.scrollBy({ left: -300, behavior: 'smooth' });
                },
                scrollRight() {
                    this.$refs.dealsTrack.scrollBy({ left: 300, behavior: 'smooth' });
                }
            }">
                <div class="p-4 sm:p-6 rounded-[2rem] bg-white/90 dark:bg-zinc-900/90 border border-black/[0.06] dark:border-white/10 shadow-[0_8px_30px_rgba(0,0,0,0.02)] backdrop-blur-xl space-y-4">
                    
                    <!-- Header with Live Ticking Countdown -->
                    <div class="flex items-center justify-between pb-3 border-b border-black/[0.05] dark:border-white/10 flex-wrap gap-2">
                        <div class="flex items-center gap-2.5 sm:gap-3 flex-wrap">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-lg font-black">⚡</span>
                                <div>
                                    <h2 class="text-base sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tight leading-tight">Trending Deals of the Day</h2>
                                    <p class="text-[10px] sm:text-xs text-zinc-500 dark:text-zinc-400">Handpicked boutique offers with express pan-India dispatch</p>
                                </div>
                            </div>
                            
                            <!-- Timer Chip with simulated dynamic countdown -->
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-amber-900 dark:text-amber-200 text-xs font-bold"
                                 x-data="{
                                     hours: 5, minutes: 24, seconds: 48,
                                     init() {
                                         setInterval(() => {
                                             if (this.seconds > 0) { this.seconds--; }
                                             else {
                                                 this.seconds = 59;
                                                 if (this.minutes > 0) { this.minutes--; }
                                                 else { this.minutes = 59; this.hours = this.hours > 0 ? this.hours - 1 : 12; }
                                             }
                                         }, 1000);
                                     }
                                 }">
                                <span>⏰</span>
                                <span class="font-mono text-[11px]">Ends in <span x-text="String(hours).padStart(2,'0') + ':' + String(minutes).padStart(2,'0') + ':' + String(seconds).padStart(2,'0')"></span></span>
                            </div>
                        </div>

                        <!-- Chevrons & View All -->
                        <div class="flex items-center gap-2">
                            <a href="{{ route('catalog.index') }}" class="px-3.5 py-1.5 rounded-xl bg-zinc-950 hover:bg-black dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 text-xs font-bold transition shadow-xs hidden sm:inline-flex items-center gap-1">
                                <span>View All</span>
                                <span>&rarr;</span>
                            </a>
                            
                            <button type="button" @click="scrollLeft()" class="w-8 h-8 rounded-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 flex items-center justify-center transition active:scale-95 cursor-pointer" aria-label="Previous Products">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="scrollRight()" class="w-8 h-8 rounded-full bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 flex items-center justify-center transition active:scale-95 cursor-pointer" aria-label="Next Products">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Products Horizontal Slider Track -->
                    <div x-ref="dealsTrack" 
                         class="flex items-stretch gap-3 sm:gap-4 overflow-x-auto no-scrollbar scroll-smooth py-1 px-0.5 touch-pan-x"
                         style="scrollbar-width: none; -webkit-overflow-scrolling: touch;">
                        @foreach($dealsProducts->take(12) as $prod)
                            <div class="w-[180px] sm:w-[220px] shrink-0">
                                <x-product-card :product="$prod" />
                            </div>
                        @endforeach
                    </div>

                </div>
            </section>
            @endif

            <!-- ==========================================
                 5. MAIN DIRECT PRODUCT SHOWCASE (MEESHO / AMAZON ULTRA-RESPONSIVE GRID)
                 ========================================== -->
            <section id="product-showcase" class="max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8 space-y-6"
                     x-data="{
                        activeTab: 'all',
                        filterProducts(itemCategory, itemPrice, isDeal) {
                            if (this.activeTab === 'all') return true;
                            if (this.activeTab === 'deals') return isDeal;
                            if (this.activeTab === 'under499') return itemPrice <= 49900;
                            if (this.activeTab === 'fashion') return (itemCategory || '').toLowerCase().includes('fashion') || (itemCategory || '').toLowerCase().includes('clothing') || (itemCategory || '').toLowerCase().includes('saree') || (itemCategory || '').toLowerCase().includes('apparel');
                            if (this.activeTab === 'tech') return (itemCategory || '').toLowerCase().includes('tech') || (itemCategory || '').toLowerCase().includes('mobile') || (itemCategory || '').toLowerCase().includes('electronic') || (itemCategory || '').toLowerCase().includes('laptop');
                            return true;
                        }
                     }">
                
                <!-- Header with Quick-Filter Pills -->
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pb-3 border-b border-black/[0.06] dark:border-white/10">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-black text-[9px] uppercase tracking-wider">
                                DIRECT SHOWCASE
                            </span>
                            <span class="text-xs text-zinc-500 font-bold">1-Tap Add to Bag</span>
                        </div>
                        <h2 class="text-xl sm:text-3xl font-black text-zinc-950 dark:text-white tracking-tight mt-1">
                            Explore Trending Products
                        </h2>
                    </div>

                    <!-- Category Filter Tabs (Instant Alpine filtering) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-1 text-xs font-bold" style="scrollbar-width: none;">
                        <button type="button" @click="activeTab = 'all'"
                                :class="activeTab === 'all' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 shadow-sm' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            All Items
                        </button>
                        <button type="button" @click="activeTab = 'deals'"
                                :class="activeTab === 'deals' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 shadow-sm' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            🔥 Top Deals
                        </button>
                        <button type="button" @click="activeTab = 'under499'"
                                :class="activeTab === 'under499' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 shadow-sm' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            ⚡ Under ₹499
                        </button>
                        <button type="button" @click="activeTab = 'fashion'"
                                :class="activeTab === 'fashion' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 shadow-sm' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            👗 Fashion
                        </button>
                        <button type="button" @click="activeTab = 'tech'"
                                :class="activeTab === 'tech' ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 shadow-sm' : 'bg-white dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700'"
                                class="px-3.5 py-1.5 rounded-full transition whitespace-nowrap cursor-pointer">
                            📱 Mobiles &amp; Tech
                        </button>
                    </div>
                </div>

                <!-- ULTRA-RESPONSIVE PRODUCT GRID: 2-COL MOBILE, 3-4 COL TABLET, 4-5 COL DESKTOP -->
                @if($allCatalogProducts->isNotEmpty())
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5 gap-2.5 sm:gap-4 lg:gap-5">
                        @foreach($allCatalogProducts as $prod)
                            @php
                                $catSlug = $prod->category?->slug ?? '';
                                $catName = $prod->category?->name ?? '';
                                $hasDiscount = ($prod->compare_at_price > $prod->base_price);
                            @endphp
                            <div x-show="filterProducts('{{ addslashes($catName . ' ' . $catSlug) }}', {{ $prod->base_price }}, {{ $hasDiscount ? 'true' : 'false' }})"
                                 x-transition:enter="transition ease-out duration-200"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100">
                                <x-product-card :product="$prod" />
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-10 rounded-2xl bg-white dark:bg-zinc-900 border border-black/[0.06] text-center">
                        <p class="text-xs sm:text-sm text-zinc-500">Products are loading directly from verified boutiques.</p>
                    </div>
                @endif

                <!-- View All Full Catalog Button -->
                <div class="text-center pt-4">
                    <a href="{{ route('catalog.index') }}" 
                       class="inline-flex items-center gap-2 px-8 py-3.5 rounded-2xl bg-zinc-950 hover:bg-black dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 font-bold text-xs sm:text-sm tracking-wide shadow-md active:scale-95 transition">
                        <span>Load More Products</span>
                        <span>&rarr;</span>
                    </a>
                </div>

            </section>

            <!-- ==========================================
                 6. CLOUD DINING & RESTAURANTS (CLEAN VISUAL TILES)
                 ========================================== -->
            @if($foodRestaurants->isNotEmpty() || $featuredFood->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">🍲</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tight leading-tight">Artisan Kitchens &amp; Dining</h2>
                            <p class="text-[11px] text-zinc-500">Hygiene-certified cloud kitchens with fresh doorstep dispatch</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('food') }}" class="text-xs sm:text-sm font-bold text-amber-700 dark:text-amber-400 hover:underline flex items-center gap-1">
                        <span>All Menus</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach(($foodRestaurants->isNotEmpty() ? $foodRestaurants : $featuredFood)->take(4) as $item)
                        @php
                            $isVendor = $item instanceof \App\Models\Vendor;
                            $rTitle = $isVendor ? $item->shop_name : $item->title;
                            $rImg = $isVendor ? ($item->logo_url ?: $item->cover_url) : $item->primary_image_url;
                            $rUrl = $isVendor ? route('food.restaurant', $item->slug) : ($item->vendor ? route('food.restaurant', $item->vendor->slug) : k71_vertical_url('food'));
                        @endphp
                        <a href="{{ $rUrl }}" class="group bg-white dark:bg-zinc-900 rounded-2xl sm:rounded-3xl border border-black/[0.06] dark:border-white/10 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-video overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                @if($rImg)
                                    <img src="{{ format_image_url($rImg) }}" alt="{{ $rTitle }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🍲</div>
                                @endif
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-amber-600 text-white text-[9px] font-black uppercase tracking-wider">
                                    Fresh
                                </span>
                            </div>
                            <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <h3 class="font-bold text-xs sm:text-sm text-zinc-950 dark:text-white group-hover:text-amber-700 transition truncate">{{ $rTitle }}</h3>
                                    <p class="text-[10px] text-zinc-400 mt-0.5">Express delivery in 30-45 mins</p>
                                </div>
                                <div class="pt-2 mt-2 border-t border-black/[0.04] dark:border-white/10 flex items-center justify-between text-xs font-bold text-amber-700">
                                    <span>View Menu</span>
                                    <span>&rarr;</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 7. VERIFIED SERVICES & SPECIALISTS (VISUAL GIG CARDS)
                 ========================================== -->
            @if($featuredServices->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">⚡</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tight leading-tight">Verified Services &amp; Specialists</h2>
                            <p class="text-[11px] text-zinc-500">Marble restoration, web engineering, photo shoots &amp; home repairs</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('services') }}" class="text-xs sm:text-sm font-bold text-purple-700 dark:text-purple-400 hover:underline flex items-center gap-1">
                        <span>All Services</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">
                    @foreach($featuredServices->take(4) as $service)
                        @php
                            $basicPkg = $service->gigPackages->firstWhere('tier', 'basic') ?? $service->gigPackages->first();
                            $startingPrice = $basicPkg ? $basicPkg->price : ($service->base_price / 100);
                        @endphp
                        <a href="{{ route('services.show', $service->slug) }}" class="group bg-white dark:bg-zinc-900 rounded-2xl sm:rounded-3xl border border-black/[0.06] dark:border-white/10 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-video overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                <img src="{{ $service->primary_image_url }}" alt="{{ $service->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-purple-700 text-white text-[9px] font-black uppercase tracking-wider">
                                    Vetted
                                </span>
                            </div>
                            <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <span class="text-[10px] font-bold text-purple-700 dark:text-purple-400 block truncate">{{ $service->vendor?->shop_name ?? 'Specialist' }}</span>
                                    <h3 class="font-bold text-xs sm:text-sm text-zinc-950 dark:text-white group-hover:text-purple-700 transition truncate mt-0.5">{{ $service->title }}</h3>
                                </div>
                                <div class="pt-2 mt-2 border-t border-black/[0.04] dark:border-white/10 flex items-center justify-between">
                                    <span class="text-xs font-black text-zinc-950 dark:text-white">From ₹{{ number_format($startingPrice) }}</span>
                                    <span class="text-[11px] font-bold text-purple-700">Book &rarr;</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 8. VERIFIED PRE-OWNED DEALS (RE-COMMERCE)
                 ========================================== -->
            @if($featuredUsed->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">♻️</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tight leading-tight">Pre-Owned Inspected Deals</h2>
                            <p class="text-[11px] text-zinc-500">Certified second-hand smartphones, laptops, machinery &amp; vehicles</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('used') }}" class="text-xs sm:text-sm font-bold text-teal-700 dark:text-teal-400 hover:underline flex items-center gap-1">
                        <span>All Used Deals</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($featuredUsed->take(4) as $item)
                        <a href="{{ route('used.show', $item->slug) }}" class="group bg-white dark:bg-zinc-900 rounded-2xl sm:rounded-3xl border border-black/[0.06] dark:border-white/10 shadow-xs hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-square overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-teal-800 text-white text-[9px] font-black uppercase tracking-wider">
                                    {{ $item->getCustomField('condition', 'Inspected') }}
                                </span>
                            </div>
                            <div class="p-3">
                                <h4 class="text-xs font-bold text-zinc-950 dark:text-white truncate group-hover:text-teal-700 transition">{{ $item->title }}</h4>
                                <div class="text-sm font-black text-zinc-950 dark:text-white mt-1">{{ $item->formatted_base_price }}</div>
                                <span class="text-[10px] text-zinc-400 block mt-0.5">📍 {{ $item->getCustomField('city', 'India') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 9. SHOPPABLE STORIES & REELS (9:16 VIDEO ROW)
                 ========================================== -->
            @if($latestShorts->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">▶</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tight leading-tight">Shoppable Video Stories</h2>
                            <p class="text-[11px] text-zinc-500">Watch products in motion with 1-tap checkout</p>
                        </div>
                    </div>
                    <a href="{{ k71_vertical_url('shorts') }}" class="text-xs sm:text-sm font-bold text-rose-600 hover:underline flex items-center gap-1">
                        <span>Watch Reels Feed</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                    @foreach($latestShorts->take(6) as $short)
                        <a href="{{ route('shorts.show', $short->id) }}" class="group relative aspect-[9/16] rounded-2xl sm:rounded-3xl overflow-hidden bg-zinc-950 shadow-xs hover:shadow-xl transition-all duration-300 block">
                            @if($short->thumbnail_url)
                                <img src="{{ $short->thumbnail_url }}" alt="{{ $short->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white text-3xl">▶</div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-transparent to-black/20 p-3 flex flex-col justify-between text-white">
                                <span class="self-start px-2 py-0.5 rounded-full bg-rose-600 text-[8px] font-black uppercase tracking-wider">
                                    LIVE
                                </span>
                                <div>
                                    <h4 class="text-xs font-bold line-clamp-2 leading-snug drop-shadow-sm">{{ $short->title }}</h4>
                                    <span class="text-[10px] text-zinc-300 mt-1 block">Watch &amp; Buy &rarr;</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 10. B2B FACTORY REQUIREMENTS & RFQS
                 ========================================== -->
            @if($featuredEnquiries->isNotEmpty())
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-5">
                <div class="flex items-center justify-between pb-3 border-b border-black/[0.06] dark:border-white/10">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl">📋</span>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-zinc-950 dark:text-white tracking-tight leading-tight">Live B2B &amp; Factory Enquiries</h2>
                            <p class="text-[11px] text-zinc-500">Post requirement or quote on verified wholesale buyers</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('enquiry.create') }}" class="px-3 py-1.5 rounded-xl bg-zinc-950 hover:bg-black dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 text-xs font-bold transition shadow-xs">
                            + Post RFQ
                        </a>
                        <a href="{{ k71_vertical_url('enquiry') }}" class="text-xs sm:text-sm font-bold text-amber-800 dark:text-amber-400 hover:underline">
                            All RFQs &rarr;
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                    @foreach($featuredEnquiries->take(4) as $enq)
                        <div class="p-4 rounded-2xl bg-white dark:bg-zinc-900 border border-black/[0.06] dark:border-white/10 shadow-xs flex flex-col justify-between space-y-3">
                            <div>
                                <div class="flex items-center justify-between text-[10px] text-zinc-400 font-mono">
                                    <span>#{{ $enq->enquiry_number }}</span>
                                    <span class="px-1.5 py-0.2 rounded bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 font-bold uppercase">{{ $enq->status }}</span>
                                </div>
                                <h4 class="text-xs sm:text-sm font-bold text-zinc-950 dark:text-white mt-1.5 line-clamp-2">{{ $enq->title }}</h4>
                                <div class="mt-2 text-[11px] text-zinc-500 flex items-center gap-2">
                                    <span>Qty: <strong class="text-zinc-800 dark:text-zinc-200">{{ $enq->quantity }}</strong></span>
                                    <span>•</span>
                                    <span>📍 {{ $enq->city }}</span>
                                </div>
                            </div>
                            <div class="pt-2 border-t border-black/[0.04] dark:border-white/10 flex items-center justify-between text-xs">
                                <span class="font-bold text-zinc-950 dark:text-white">{{ $enq->formatted_budget }}</span>
                                <a href="{{ route('enquiry.show', $enq->enquiry_number) }}" class="px-3 py-1 rounded-lg bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-900 dark:text-white font-bold text-[11px] transition">
                                    Quote &rarr;
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 11. SELLER ONBOARDING CALLOUT
                 ========================================== -->
            <section class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
                <div class="p-6 sm:p-10 rounded-[2.5rem] bg-zinc-950 text-white shadow-xl relative overflow-hidden border border-white/10 flex flex-col md:flex-row items-center justify-between gap-6">
                    <div class="space-y-2 text-center md:text-left">
                        <span class="px-3 py-1 rounded-full bg-white/10 text-zinc-300 text-[10px] font-bold uppercase tracking-widest border border-white/10">
                            Indian Merchant Program
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            Start Selling Direct on K71 Marketplace
                        </h3>
                        <p class="text-xs sm:text-sm text-zinc-400 max-w-lg leading-relaxed">
                            Zero setup fee for retail boutiques, dining kitchens, verified specialists, pre-owned sellers, and marble factories with automated daily PhonePe settlements.
                        </p>
                    </div>

                    <div class="flex items-center gap-3 shrink-0 w-full md:w-auto">
                        <a href="{{ route('vendor.register') }}" 
                           class="w-full md:w-auto px-7 py-3.5 rounded-2xl bg-white hover:bg-zinc-100 text-zinc-950 font-black text-xs sm:text-sm tracking-wide shadow-md text-center transition active:scale-95">
                            Open Your Store &rarr;
                        </a>
                        <button type="button" 
                                onclick="if(window.openAuthModal) { window.openAuthModal(null, 'login', 'seller'); } else { window.location.href = '{{ route('login') }}'; }"
                                class="w-full md:w-auto px-6 py-3.5 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs sm:text-sm border border-white/15 text-center transition">
                            Seller Login
                        </button>
                    </div>
                </div>
            </section>

            <!-- ==========================================
                 12. TRUST & BUYER PROTECTION PILLARS
                 ========================================== -->
            <section class="max-w-6xl mx-auto px-3 sm:px-6">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                    <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 border border-black/[0.06] dark:border-white/10 shadow-xs text-center">
                        <span class="text-2xl block mb-1">🛡️</span>
                        <h4 class="text-xs font-bold text-zinc-950 dark:text-white">Verified Merchants</h4>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">GST &amp; Aadhaar Vetted</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 border border-black/[0.06] dark:border-white/10 shadow-xs text-center">
                        <span class="text-2xl block mb-1">⚡</span>
                        <h4 class="text-xs font-bold text-zinc-950 dark:text-white">PhonePe Escrow</h4>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">100% Safe Settlements</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 border border-black/[0.06] dark:border-white/10 shadow-xs text-center">
                        <span class="text-2xl block mb-1">📦</span>
                        <h4 class="text-xs font-bold text-zinc-950 dark:text-white">Fast Dispatch</h4>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">Express Pan-India Logistics</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white/80 dark:bg-zinc-900/80 border border-black/[0.06] dark:border-white/10 shadow-xs text-center">
                        <span class="text-2xl block mb-1">💬</span>
                        <h4 class="text-xs font-bold text-zinc-950 dark:text-white">Direct Chat</h4>
                        <p class="text-[10px] text-zinc-500 dark:text-zinc-400 mt-0.5">WhatsApp &amp; Live In-App DM</p>
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-layouts.public>
