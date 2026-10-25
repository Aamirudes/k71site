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
    :title="$seo['seo_meta_title'] ?? 'K71 • Modern Multi-Vertical Marketplace'"
    :metaDescription="$seo['seo_meta_description'] ?? 'Discover products, order food, hire professionals, find verified second-hand deals, submit B2B requirements, and shop through video stories on K71.'"
    :schemaJson="$schemaData"
    :fullWidth="true"
>
    <!-- Background Ethereal Canvas Mesh -->
    <div class="relative w-full overflow-hidden bg-[#FAFAF9] text-zinc-900">
        
        <!-- Ambient Luminous Glow Orbs (Pure CSS, Non-blocking) -->
        <div class="absolute top-0 left-1/4 -translate-x-1/2 w-[700px] h-[500px] rounded-full bg-gradient-to-tr from-slate-200/50 via-indigo-100/30 to-transparent blur-[140px] pointer-events-none" aria-hidden="true"></div>
        <div class="absolute top-80 right-0 w-[600px] h-[600px] rounded-full bg-gradient-to-bl from-amber-100/40 via-rose-100/25 to-transparent blur-[160px] pointer-events-none" aria-hidden="true"></div>
        <div class="absolute top-[1800px] left-0 w-[800px] h-[700px] rounded-full bg-gradient-to-r from-teal-100/30 via-slate-100/40 to-transparent blur-[150px] pointer-events-none" aria-hidden="true"></div>

        <div class="relative z-10 space-y-24 sm:space-y-36 pb-28 sm:pb-36">

            <!-- ==========================================
                 1. HERO SECTION — SPACIOUS MINIMAL GLASSMORPHISM
                 ========================================== -->
            <section class="pt-10 sm:pt-18 lg:pt-24 max-w-7xl mx-auto px-6 sm:px-10 lg:px-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16 items-center">
                    
                    <!-- Left Column: Vision & Action (7 cols) -->
                    <div class="lg:col-span-7 space-y-8 text-center lg:text-left">
                        
                        <!-- Luminous Badge -->
                        <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/80 border border-black/[0.08] shadow-[0_2px_12px_rgba(0,0,0,0.04)] backdrop-blur-xl text-zinc-800 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="font-mono uppercase text-[11px] text-zinc-500 font-bold tracking-widest">ECOSYSTEM</span>
                            <span class="text-zinc-300">/</span>
                            <span class="text-zinc-700 font-medium">6 Curated Commerce Verticals</span>
                        </div>

                        <!-- Hero Main Headline -->
                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-zinc-950 tracking-tight leading-[1.08]">
                            Commerce, unified. <br class="hidden sm:inline">
                            <span class="bg-gradient-to-r from-zinc-950 via-zinc-800 to-zinc-500 bg-clip-text text-transparent">
                                Discover everything.
                            </span>
                        </h1>

                        <!-- Supporting Description -->
                        <p class="text-base sm:text-lg text-zinc-600 font-normal max-w-xl mx-auto lg:mx-0 leading-relaxed">
                            Discover boutique shopping, artisan cloud dining, certified professionals, inspected pre-owned tech, factory B2B enquiries, and interactive video reels — with PhonePe escrow protection.
                        </p>

                        <!-- Action Buttons -->
                        <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4 pt-2">
                            <a href="#marketplace-discovery" 
                               class="px-8 py-4 rounded-full bg-zinc-950 hover:bg-black text-white font-bold text-sm tracking-wide shadow-lg shadow-black/15 active:scale-[0.98] transition-all flex items-center gap-2 cursor-pointer">
                                <span>Explore Verticals</span>
                                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </a>
                            <a href="{{ route('vendor.register') }}" 
                               class="px-8 py-4 rounded-full bg-white/90 hover:bg-white text-zinc-900 font-bold text-sm tracking-wide border border-black/[0.08] shadow-xs hover:shadow-md active:scale-[0.98] transition-all backdrop-blur-xl flex items-center gap-2 cursor-pointer">
                                <span>Sell on K71</span>
                                <span class="text-zinc-400 font-normal">&rarr;</span>
                            </a>
                        </div>

                        <!-- Micro Metrics & Trust Badges -->
                        <div class="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-8 text-xs text-zinc-500 font-medium">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-[10px] font-black">✓</span>
                                <span>Verified GST Sellers</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-[10px] font-black">✓</span>
                                <span>Escrow Protected</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-[10px] font-black">✓</span>
                                <span>Direct WhatsApp Chat</span>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Interactive Glass Preview Mosaic (5 cols) -->
                    <div class="lg:col-span-5 relative">
                        <div class="p-3.5 sm:p-5 rounded-[2.5rem] bg-white/50 border border-white/80 shadow-[0_20px_60px_rgba(0,0,0,0.05)] backdrop-blur-2xl">
                            <div class="grid grid-cols-2 gap-3.5 sm:gap-4">
                                
                                <!-- Preview Tile 1: Shopping -->
                                @php $heroProd = $featuredProducts->first() ?? $newArrivals->first(); @endphp
                                <a href="{{ $heroProd ? route('product.show', $heroProd->slug) : route('catalog.index') }}"
                                   class="group p-4 rounded-2xl bg-white/85 hover:bg-white border border-black/[0.06] hover:border-black/15 shadow-[0_4px_16px_rgba(0,0,0,0.02)] hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                                    <div class="aspect-square rounded-xl overflow-hidden bg-zinc-100 mb-3 relative">
                                        @if($heroProd && $heroProd->primary_image_url)
                                            <img src="{{ $heroProd->primary_image_url }}" alt="{{ $heroProd->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-3xl">🛍️</div>
                                        @endif
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-black/75 text-white text-[9px] font-black uppercase tracking-wider backdrop-blur-xs">
                                            Retail
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Boutique</span>
                                        <h4 class="text-xs font-bold text-zinc-950 truncate mt-0.5">{{ $heroProd?->title ?? 'Everyday Retail' }}</h4>
                                        <span class="text-xs font-extrabold text-zinc-900 mt-1 block">
                                            {{ $heroProd ? $heroProd->formatted_base_price : 'Browse Stores' }}
                                        </span>
                                    </div>
                                </a>

                                <!-- Preview Tile 2: Food & Kitchens -->
                                @php $heroFood = $featuredFood->first(); @endphp
                                <a href="{{ k71_vertical_url('food') }}"
                                   class="group p-4 rounded-2xl bg-white/85 hover:bg-white border border-black/[0.06] hover:border-black/15 shadow-[0_4px_16px_rgba(0,0,0,0.02)] hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                                    <div class="aspect-square rounded-xl overflow-hidden bg-zinc-100 mb-3 relative">
                                        @if($heroFood && $heroFood->primary_image_url)
                                            <img src="{{ $heroFood->primary_image_url }}" alt="{{ $heroFood->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-3xl">🍲</div>
                                        @endif
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-amber-600/90 text-white text-[9px] font-black uppercase tracking-wider backdrop-blur-xs">
                                            Dining
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Kitchens</span>
                                        <h4 class="text-xs font-bold text-zinc-950 truncate mt-0.5">{{ $heroFood?->title ?? 'Curated Menus' }}</h4>
                                        <span class="text-xs font-extrabold text-amber-700 mt-1 block">
                                            Order Fresh &rarr;
                                        </span>
                                    </div>
                                </a>

                                <!-- Preview Tile 3: Freelance Services -->
                                @php $heroService = $featuredServices->first(); @endphp
                                <a href="{{ k71_vertical_url('services') }}"
                                   class="group p-4 rounded-2xl bg-white/85 hover:bg-white border border-black/[0.06] hover:border-black/15 shadow-[0_4px_16px_rgba(0,0,0,0.02)] hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                                    <div class="aspect-square rounded-xl overflow-hidden bg-zinc-100 mb-3 relative">
                                        @if($heroService && $heroService->primary_image_url)
                                            <img src="{{ $heroService->primary_image_url }}" alt="{{ $heroService->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-3xl">⚡</div>
                                        @endif
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-zinc-900/80 text-white text-[9px] font-black uppercase tracking-wider backdrop-blur-xs">
                                            Specialist
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Gigs &amp; Craft</span>
                                        <h4 class="text-xs font-bold text-zinc-950 truncate mt-0.5">{{ $heroService?->title ?? 'Talent & Skills' }}</h4>
                                        <span class="text-xs font-extrabold text-zinc-900 mt-1 block">
                                            Hire Talent &rarr;
                                        </span>
                                    </div>
                                </a>

                                <!-- Preview Tile 4: Shoppable Shorts -->
                                @php $heroShort = $latestShorts->first(); @endphp
                                <a href="{{ k71_vertical_url('shorts') }}"
                                   class="group p-4 rounded-2xl bg-white/85 hover:bg-white border border-black/[0.06] hover:border-black/15 shadow-[0_4px_16px_rgba(0,0,0,0.02)] hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
                                    <div class="aspect-square rounded-xl overflow-hidden bg-zinc-900 mb-3 relative text-white">
                                        @if($heroShort && $heroShort->thumbnail_url)
                                            <img src="{{ $heroShort->thumbnail_url }}" alt="{{ $heroShort->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-white text-3xl">▶</div>
                                        @endif
                                        <span class="absolute top-2 left-2 px-2 py-0.5 rounded-full bg-rose-600 text-white text-[9px] font-black uppercase tracking-wider">
                                            LIVE REEL
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-zinc-400 uppercase font-bold tracking-wider block">Video Story</span>
                                        <h4 class="text-xs font-bold text-zinc-950 truncate mt-0.5">{{ $heroShort?->title ?? 'Shoppable Reels' }}</h4>
                                        <span class="text-xs font-extrabold text-rose-600 mt-1 block">
                                            Watch &amp; Shop &rarr;
                                        </span>
                                    </div>
                                </a>

                            </div>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ==========================================
                 2. UNIVERSAL DYNAMIC SEARCH DOCK
                 ========================================== -->
            <section class="max-w-4xl mx-auto px-6 sm:px-8">
                <div class="p-3 sm:p-4 rounded-[2rem] bg-white/80 border border-black/[0.08] shadow-[0_20px_50px_rgba(0,0,0,0.06)] backdrop-blur-2xl relative"
                     x-data="{
                        query: '',
                        vertical: '',
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
                    
                    <!-- Vertical Switcher Bar -->
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-2.5 mb-2.5 border-b border-black/[0.04] text-xs font-medium">
                        <button type="button" @click="vertical = ''" 
                                :class="vertical === '' ? 'bg-zinc-900 text-white font-bold' : 'text-zinc-600 hover:text-black hover:bg-zinc-100'"
                                class="px-3 py-1 rounded-full transition whitespace-nowrap">
                            All Verticals
                        </button>
                        <a href="{{ route('catalog.index') }}" class="px-3 py-1 rounded-full text-zinc-600 hover:text-black hover:bg-zinc-100 transition whitespace-nowrap">
                            🛍️ Retail
                        </a>
                        <a href="{{ k71_vertical_url('food') }}" class="px-3 py-1 rounded-full text-zinc-600 hover:text-black hover:bg-zinc-100 transition whitespace-nowrap">
                            🍲 Dining
                        </a>
                        <a href="{{ k71_vertical_url('services') }}" class="px-3 py-1 rounded-full text-zinc-600 hover:text-black hover:bg-zinc-100 transition whitespace-nowrap">
                            ⚡ Services
                        </a>
                        <a href="{{ k71_vertical_url('used') }}" class="px-3 py-1 rounded-full text-zinc-600 hover:text-black hover:bg-zinc-100 transition whitespace-nowrap">
                            ♻️ Pre-Owned
                        </a>
                        <a href="{{ k71_vertical_url('enquiry') }}" class="px-3 py-1 rounded-full text-zinc-600 hover:text-black hover:bg-zinc-100 transition whitespace-nowrap">
                            📋 B2B RFQs
                        </a>
                        <a href="{{ k71_vertical_url('shorts') }}" class="px-3 py-1 rounded-full text-zinc-600 hover:text-black hover:bg-zinc-100 transition whitespace-nowrap">
                            ▶ Shorts
                        </a>
                    </div>

                    <!-- Search Input Form -->
                    <form action="{{ route('catalog.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-2">
                        <div class="relative flex-1 w-full flex items-center">
                            <div class="pl-4 text-zinc-400">
                                <svg class="w-5 h-5 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" x-model="query"
                                   @input.debounce.250ms="fetchSuggestions()"
                                   @focus="if(results.length > 0) isOpen = true"
                                   autocomplete="off"
                                   placeholder="Search products, dining menus, verified pros, used deals, bulk RFQs..."
                                   class="w-full px-3.5 py-3 bg-transparent text-sm font-medium text-zinc-950 placeholder-zinc-400 focus:outline-none">
                            
                            <div x-show="loading" class="pr-3 text-zinc-400" style="display: none;">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-zinc-950 hover:bg-black text-white font-bold text-xs sm:text-sm tracking-wide shadow-md active:scale-95 transition shrink-0 cursor-pointer">
                            Search Marketplace
                        </button>
                    </form>

                    <!-- Live Dynamic Suggestions Dropdown -->
                    <div x-show="isOpen && (results.length > 0 || loading)" 
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="absolute top-full left-0 right-0 mt-3 bg-white/95 backdrop-blur-2xl border border-black/[0.08] rounded-3xl shadow-2xl overflow-hidden z-50 divide-y divide-zinc-100"
                         style="display: none;">
                        
                        <template x-for="item in results" :key="item.id">
                            <a :href="item.url" class="flex items-center gap-3.5 p-3 hover:bg-zinc-50 transition group">
                                <img :src="item.thumb" :alt="item.title" class="w-12 h-12 rounded-xl object-cover border border-zinc-200 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs sm:text-sm font-bold text-zinc-950 truncate group-hover:text-black" x-text="item.title"></p>
                                    <div class="flex items-center gap-2.5 text-[11px] text-zinc-500 mt-0.5">
                                        <span class="font-mono font-bold text-zinc-900" x-text="item.price"></span>
                                        <span class="uppercase px-1.5 py-0.2 rounded bg-zinc-100 text-zinc-600 font-bold text-[9px]" x-text="item.vertical"></span>
                                        <span class="truncate text-zinc-400" x-text="item.vendor"></span>
                                    </div>
                                </div>
                                <span class="text-zinc-400 group-hover:text-zinc-900 group-hover:translate-x-1 transition-all text-xs font-bold pr-2">&rarr;</span>
                            </a>
                        </template>

                        <div x-show="results.length > 0" class="p-3 bg-zinc-50/90 text-center">
                            <a :href="'{{ route('catalog.index') }}?search=' + encodeURIComponent(query)" class="text-xs font-bold text-zinc-800 hover:text-black">
                                See all matching items for "<span x-text="query"></span>" &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- Curated Discovery Pills -->
                    <div class="pt-3 px-3 pb-1 border-t border-black/[0.04] mt-2 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-zinc-400 font-bold uppercase text-[10px] tracking-widest">Trending:</span>
                        <a href="{{ route('catalog.index', ['search' => 'silk']) }}" class="px-3 py-1 rounded-full bg-zinc-100/80 hover:bg-zinc-200/80 text-zinc-700 text-xs font-medium transition">Silk Sarees</a>
                        <a href="{{ k71_vertical_url('food') }}" class="px-3 py-1 rounded-full bg-zinc-100/80 hover:bg-zinc-200/80 text-zinc-700 text-xs font-medium transition">Biryani &amp; Sweets</a>
                        <a href="{{ k71_vertical_url('services') }}" class="px-3 py-1 rounded-full bg-zinc-100/80 hover:bg-zinc-200/80 text-zinc-700 text-xs font-medium transition">Marble Polishing</a>
                        <a href="{{ k71_vertical_url('used') }}" class="px-3 py-1 rounded-full bg-zinc-100/80 hover:bg-zinc-200/80 text-zinc-700 text-xs font-medium transition">iPhone &amp; Laptops</a>
                        <a href="{{ k71_vertical_url('enquiry') }}" class="px-3 py-1 rounded-full bg-zinc-100/80 hover:bg-zinc-200/80 text-zinc-700 text-xs font-medium transition">B2B Marble Slabs</a>
                    </div>
                </div>
            </section>

            <!-- ==========================================
                 3. THE 6 COMMERCE VERTICALS — LUXURY CARDS
                 ========================================== -->
            <section id="marketplace-discovery" class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 space-y-10">
                <div class="text-center max-w-2xl mx-auto space-y-3">
                    <span class="px-3.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-widest bg-white border border-black/[0.08] text-zinc-600 shadow-xs">
                        Architecture
                    </span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-zinc-950 tracking-tight">
                        Six Dedicated Commerce Channels
                    </h2>
                    <p class="text-sm sm:text-base text-zinc-500 font-normal">
                        Every vertical has tailored transaction contracts, logistics pipelines, and direct messaging.
                    </p>
                </div>

                <!-- 6 Glassmorphic Verticals Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
                    
                    <!-- 1. K71Mart (Everyday Retail) -->
                    <div class="group p-8 rounded-[2rem] bg-white/70 hover:bg-white backdrop-blur-xl border border-black/[0.06] hover:border-black/15 shadow-[0_4px_24px_rgba(0,0,0,0.02)] hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-zinc-100 text-zinc-900 border border-black/[0.06] flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform duration-300">
                                🛍️
                            </div>
                            <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest block">Everyday Shopping</span>
                            <h3 class="text-xl font-bold text-zinc-950 mt-1">K71Mart Retail Catalog</h3>
                            <p class="text-xs sm:text-sm text-zinc-600 mt-2.5 leading-relaxed">
                                Curated collections from verified brands, boutique fashion, electronics, lifestyle items, and handcrafted decor with express pan-India delivery.
                            </p>
                        </div>
                        <div class="pt-6 mt-6 border-t border-black/[0.04] flex items-center justify-between">
                            <span class="text-xs font-semibold text-zinc-500">100% Genuine Guarantee</span>
                            <a href="{{ route('catalog.index') }}" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-black text-white text-xs font-bold transition">
                                Shop Retail &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- 2. Foods & Kitchens -->
                    <div class="group p-8 rounded-[2rem] bg-white/70 hover:bg-white backdrop-blur-xl border border-black/[0.06] hover:border-black/15 shadow-[0_4px_24px_rgba(0,0,0,0.02)] hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-700 border border-amber-200/50 flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform duration-300">
                                🍲
                            </div>
                            <span class="text-[10px] font-bold text-amber-700 uppercase tracking-widest block">Artisan Dining</span>
                            <h3 class="text-xl font-bold text-zinc-950 mt-1">Cloud Kitchens &amp; Dining</h3>
                            <p class="text-xs sm:text-sm text-zinc-600 mt-2.5 leading-relaxed">
                                Fresh regional specialties, curated bakery goods, authentic cloud kitchens, and gourmet menus dispatched directly to your doorstep.
                            </p>
                        </div>
                        <div class="pt-6 mt-6 border-t border-black/[0.04] flex items-center justify-between">
                            <span class="text-xs font-semibold text-zinc-500">Live Kitchen Menus</span>
                            <a href="{{ k71_vertical_url('food') }}" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-black text-white text-xs font-bold transition">
                                Explore Menus &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- 3. Professional Services -->
                    <div class="group p-8 rounded-[2rem] bg-white/70 hover:bg-white backdrop-blur-xl border border-black/[0.06] hover:border-black/15 shadow-[0_4px_24px_rgba(0,0,0,0.02)] hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-700 border border-indigo-200/50 flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform duration-300">
                                ⚡
                            </div>
                            <span class="text-[10px] font-bold text-indigo-700 uppercase tracking-widest block">Vetted Talent</span>
                            <h3 class="text-xl font-bold text-zinc-950 mt-1">Services &amp; Craftsmanship</h3>
                            <p class="text-xs sm:text-sm text-zinc-600 mt-2.5 leading-relaxed">
                                Hire vetted specialists for marble restoration, web development, video production, photography, and home maintenance with milestone escrow safety.
                            </p>
                        </div>
                        <div class="pt-6 mt-6 border-t border-black/[0.04] flex items-center justify-between">
                            <span class="text-xs font-semibold text-zinc-500">Milestone Escrow</span>
                            <a href="{{ k71_vertical_url('services') }}" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-black text-white text-xs font-bold transition">
                                Hire Specialists &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- 4. Used Marketplace -->
                    <div class="group p-8 rounded-[2rem] bg-white/70 hover:bg-white backdrop-blur-xl border border-black/[0.06] hover:border-black/15 shadow-[0_4px_24px_rgba(0,0,0,0.02)] hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-teal-50 text-teal-700 border border-teal-200/50 flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform duration-300">
                                ♻️
                            </div>
                            <span class="text-[10px] font-bold text-teal-700 uppercase tracking-widest block">Inspected Deals</span>
                            <h3 class="text-xl font-bold text-zinc-950 mt-1">Used Marketplace</h3>
                            <p class="text-xs sm:text-sm text-zinc-600 mt-2.5 leading-relaxed">
                                Certified pre-owned electronics, smartphones, vehicles, and furniture directly from verified sellers with counter-offer negotiation.
                            </p>
                        </div>
                        <div class="pt-6 mt-6 border-t border-black/[0.04] flex items-center justify-between">
                            <span class="text-xs font-semibold text-zinc-500">Inspected Condition</span>
                            <a href="{{ k71_vertical_url('used') }}" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-black text-white text-xs font-bold transition">
                                Browse Used Deals &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- 5. B2B & Wholesale Enquiries -->
                    <div class="group p-8 rounded-[2rem] bg-white/70 hover:bg-white backdrop-blur-xl border border-black/[0.06] hover:border-black/15 shadow-[0_4px_24px_rgba(0,0,0,0.02)] hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-amber-900 border border-amber-200/50 flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform duration-300">
                                📋
                            </div>
                            <span class="text-[10px] font-bold text-amber-900 uppercase tracking-widest block">B2B &amp; Real Estate</span>
                            <h3 class="text-xl font-bold text-zinc-950 mt-1">B2B Wholesale &amp; RFQs</h3>
                            <p class="text-xs sm:text-sm text-zinc-600 mt-2.5 leading-relaxed">
                                Post custom requirements, receive direct factory quotations, order industrial machinery, source raw marble, or inquire about commercial real estate.
                            </p>
                        </div>
                        <div class="pt-6 mt-6 border-t border-black/[0.04] flex items-center justify-between">
                            <span class="text-xs font-semibold text-zinc-500">Structured RFQs</span>
                            <a href="{{ k71_vertical_url('enquiry') }}" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-black text-white text-xs font-bold transition">
                                Post RFQ &rarr;
                            </a>
                        </div>
                    </div>

                    <!-- 6. Shoppable Shorts -->
                    <div class="group p-8 rounded-[2rem] bg-white/70 hover:bg-white backdrop-blur-xl border border-black/[0.06] hover:border-black/15 shadow-[0_4px_24px_rgba(0,0,0,0.02)] hover:shadow-2xl hover:-translate-y-1.5 transition-all duration-500 flex flex-col justify-between">
                        <div>
                            <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200/50 flex items-center justify-center text-2xl mb-5 group-hover:scale-110 transition-transform duration-300">
                                ▶
                            </div>
                            <span class="text-[10px] font-bold text-rose-600 uppercase tracking-widest block">Video Commerce</span>
                            <h3 class="text-xl font-bold text-zinc-950 mt-1">Shoppable Video Reels</h3>
                            <p class="text-xs sm:text-sm text-zinc-600 mt-2.5 leading-relaxed">
                                Experience products in live motion through immersive short videos with one-tap checkout and surprise promotional reward spins.
                            </p>
                        </div>
                        <div class="pt-6 mt-6 border-t border-black/[0.04] flex items-center justify-between">
                            <span class="text-xs font-semibold text-rose-600 font-bold">Watch &amp; Buy</span>
                            <a href="{{ k71_vertical_url('shorts') }}" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-black text-white text-xs font-bold transition">
                                Watch Reels &rarr;
                            </a>
                        </div>
                    </div>

                </div>
            </section>

            <!-- ==========================================
                 4. TRENDING RETAIL PRODUCTS (K71MART)
                 ========================================== -->
            <section class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-black/[0.06]">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-400">Curated Showcase</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight mt-1">
                            Trending on K71Mart
                        </h2>
                    </div>
                    <a href="{{ route('catalog.index') }}" class="text-xs sm:text-sm font-bold text-zinc-900 hover:text-black transition flex items-center gap-1.5 group">
                        <span>View All Products</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
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
                    <div class="p-12 rounded-3xl bg-white/60 border border-black/[0.06] text-center">
                        <p class="text-xs sm:text-sm text-zinc-500 font-medium">Catalog items are currently loading from approved boutiques.</p>
                    </div>
                @endif
            </section>

            <!-- ==========================================
                 5. CLOUD DINING & RESTAURANTS
                 ========================================== -->
            @if($foodRestaurants->isNotEmpty() || $featuredFood->isNotEmpty())
            <section class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-black/[0.06]">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-amber-700">Artisan Kitchens</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight mt-1">
                            Curated Cloud Dining &amp; Food
                        </h2>
                    </div>
                    <a href="{{ k71_vertical_url('food') }}" class="text-xs sm:text-sm font-bold text-zinc-900 hover:text-black transition flex items-center gap-1.5 group">
                        <span>Explore All Kitchens</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                    @foreach(($foodRestaurants->isNotEmpty() ? $foodRestaurants : $featuredFood)->take(4) as $item)
                        @php
                            $isVendor = $item instanceof \App\Models\Vendor;
                            $rTitle = $isVendor ? $item->shop_name : $item->title;
                            $rImg = $isVendor ? ($item->logo_url ?: $item->cover_url) : $item->primary_image_url;
                            $rUrl = $isVendor ? route('food.restaurant', $item->slug) : ($item->vendor ? route('food.restaurant', $item->vendor->slug) : k71_vertical_url('food'));
                        @endphp
                        <a href="{{ $rUrl }}" class="group bg-white/70 hover:bg-white rounded-[2rem] border border-black/[0.06] hover:border-black/15 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-video overflow-hidden bg-zinc-100">
                                @if($rImg)
                                    <img src="{{ format_image_url($rImg) }}" alt="{{ $rTitle }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-3xl">🍲</div>
                                @endif
                                <span class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full bg-black/80 backdrop-blur-xs text-white text-[9.5px] font-black uppercase tracking-wider">
                                    Kitchen
                                </span>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <h3 class="font-bold text-sm sm:text-base text-zinc-950 group-hover:text-amber-800 transition truncate">{{ $rTitle }}</h3>
                                    <p class="text-xs text-zinc-500 mt-1 line-clamp-1">Verified hygiene &amp; express doorstep dispatch</p>
                                </div>
                                <div class="pt-3 border-t border-black/[0.04] flex items-center justify-between text-xs">
                                    <span class="text-emerald-700 font-bold flex items-center gap-1.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                        <span>Accepting Orders</span>
                                    </span>
                                    <span class="font-bold text-zinc-900 group-hover:translate-x-1 transition-transform">Menu &rarr;</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 6. VERIFIED SERVICES & SPECIALISTS
                 ========================================== -->
            @if($featuredServices->isNotEmpty())
            <section class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-black/[0.06]">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-indigo-700">Craft &amp; Skills</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight mt-1">
                            Verified Specialists &amp; Services
                        </h2>
                    </div>
                    <a href="{{ k71_vertical_url('services') }}" class="text-xs sm:text-sm font-bold text-zinc-900 hover:text-black transition flex items-center gap-1.5 group">
                        <span>Browse Services</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6">
                    @foreach($featuredServices->take(4) as $service)
                        @php
                            $basicPkg = $service->gigPackages->firstWhere('tier', 'basic') ?? $service->gigPackages->first();
                            $startingPrice = $basicPkg ? $basicPkg->price : ($service->base_price / 100);
                        @endphp
                        <div class="bg-white/70 hover:bg-white rounded-[2rem] border border-black/[0.06] hover:border-black/15 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col justify-between group">
                            <div class="relative aspect-video overflow-hidden bg-zinc-100">
                                <img src="{{ $service->primary_image_url }}" alt="{{ $service->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                <div class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full bg-indigo-900/80 backdrop-blur-xs text-white text-[9.5px] font-black uppercase tracking-wider">
                                    Verified Pro
                                </div>
                            </div>
                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <span class="text-[11px] font-bold text-indigo-700 truncate block">{{ $service->vendor?->shop_name ?? 'Specialist' }}</span>
                                    <h3 class="font-bold text-sm text-zinc-950 group-hover:text-indigo-900 transition line-clamp-2 leading-snug mt-1">
                                        {{ $service->title }}
                                    </h3>
                                </div>
                                <div class="pt-3 border-t border-black/[0.04] flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-zinc-400 uppercase font-semibold">Starting at</span>
                                        <div class="text-sm sm:text-base font-black text-zinc-950">₹{{ number_format($startingPrice) }}</div>
                                    </div>
                                    <a href="{{ route('services.show', $service->slug) }}" class="px-3.5 py-1.5 rounded-xl bg-zinc-100 group-hover:bg-zinc-950 text-zinc-800 group-hover:text-white text-xs font-bold transition">
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
                 7. VERIFIED PRE-OWNED & RE-COMMERCE
                 ========================================== -->
            @if($featuredUsed->isNotEmpty())
            <section class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-black/[0.06]">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-teal-700">Re-Commerce</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight mt-1">
                            Verified Pre-Owned Listings
                        </h2>
                    </div>
                    <a href="{{ k71_vertical_url('used') }}" class="text-xs sm:text-sm font-bold text-zinc-900 hover:text-black transition flex items-center gap-1.5 group">
                        <span>Explore Used Deals</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($featuredUsed->take(4) as $item)
                        <a href="{{ route('used.show', $item->slug) }}" class="group bg-white/70 hover:bg-white rounded-[2rem] border border-black/[0.06] hover:border-black/15 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-2xl transition-all duration-500 overflow-hidden flex flex-col justify-between">
                            <div class="relative aspect-square overflow-hidden bg-zinc-100">
                                <img src="{{ $item->primary_image_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                <span class="absolute top-3 left-3 px-2 py-0.5 rounded-full bg-teal-900/80 backdrop-blur-xs text-white text-[9px] font-black uppercase tracking-wider">
                                    {{ $item->getCustomField('condition', 'Pre-Owned') }}
                                </span>
                            </div>
                            <div class="p-4 space-y-1">
                                <h4 class="text-xs sm:text-sm font-bold text-zinc-950 truncate group-hover:text-teal-800 transition">{{ $item->title }}</h4>
                                <div class="text-sm font-black text-zinc-950">{{ $item->formatted_base_price }}</div>
                                <span class="text-[10px] text-zinc-400 block">📍 {{ $item->getCustomField('city', 'India') }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- ==========================================
                 8. B2B WHOLESALE & REAL ESTATE RFQS
                 ========================================== -->
            <section class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-black/[0.06]">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-amber-800">Business-to-Business</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight mt-1">
                            B2B Requirements &amp; Factory Enquiries
                        </h2>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('enquiry.create') }}" class="px-4 py-2 rounded-full bg-zinc-950 hover:bg-black text-white text-xs font-bold transition shadow-xs">
                            + Post Requirement
                        </a>
                        <a href="{{ k71_vertical_url('enquiry') }}" class="text-xs sm:text-sm font-bold text-zinc-900 hover:text-black transition flex items-center gap-1 group">
                            <span>All RFQs</span>
                            <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                        </a>
                    </div>
                </div>

                @if($featuredEnquiries->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        @foreach($featuredEnquiries->take(4) as $enq)
                            <div class="p-5 rounded-[2rem] bg-white/70 hover:bg-white border border-black/[0.06] hover:border-black/15 shadow-[0_4px_20px_rgba(0,0,0,0.02)] hover:shadow-xl transition-all duration-300 flex flex-col justify-between space-y-4">
                                <div>
                                    <div class="flex items-center justify-between text-[10px] text-zinc-400 font-mono">
                                        <span>#{{ $enq->enquiry_number }}</span>
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold uppercase">{{ $enq->status }}</span>
                                    </div>
                                    <h4 class="text-xs sm:text-sm font-bold text-zinc-950 mt-2 line-clamp-2 leading-snug">{{ $enq->title }}</h4>
                                    <div class="mt-2.5 text-xs text-zinc-500 flex items-center gap-2">
                                        <span>Qty: <strong class="text-zinc-800">{{ $enq->quantity }}</strong></span>
                                        <span>•</span>
                                        <span>📍 {{ $enq->city }}</span>
                                    </div>
                                </div>
                                <div class="pt-3 border-t border-black/[0.04] flex items-center justify-between text-xs">
                                    <div>
                                        <span class="text-[10px] text-zinc-400 block uppercase font-semibold">Budget</span>
                                        <span class="font-bold text-zinc-950">{{ $enq->formatted_budget }}</span>
                                    </div>
                                    <a href="{{ route('enquiry.show', $enq->enquiry_number) }}" class="px-3 py-1.5 rounded-xl bg-zinc-100 hover:bg-zinc-200 text-zinc-900 font-bold text-[11px] transition">
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
            <section class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12 space-y-8">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 pb-4 border-b border-black/[0.06]">
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-widest text-rose-600">Video Commerce</span>
                        <h2 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight mt-1">
                            Shoppable Stories in Motion
                        </h2>
                    </div>
                    <a href="{{ k71_vertical_url('shorts') }}" class="text-xs sm:text-sm font-bold text-zinc-900 hover:text-black transition flex items-center gap-1.5 group">
                        <span>Watch Reels Feed</span>
                        <span class="group-hover:translate-x-1 transition-transform">&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3.5 sm:gap-5">
                    @foreach($latestShorts->take(6) as $short)
                        <a href="{{ route('shorts.show', $short->id) }}" class="group relative aspect-[9/16] rounded-[2rem] overflow-hidden bg-zinc-950 shadow-xs hover:shadow-2xl transition-all duration-500 block">
                            @if($short->thumbnail_url)
                                <img src="{{ $short->thumbnail_url }}" alt="{{ $short->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white text-3xl">▶</div>
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-transparent to-black/20 p-3.5 flex flex-col justify-between text-white">
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
                 10. SELLER RECRUITMENT — GRAPHITE GLASS CARD
                 ========================================== -->
            <section class="max-w-7xl mx-auto px-6 sm:px-10 lg:px-12">
                <div class="p-8 sm:p-14 rounded-[3rem] bg-zinc-950 text-white shadow-2xl relative overflow-hidden border border-white/10">
                    <div class="absolute -right-24 -bottom-24 w-96 h-96 rounded-full bg-indigo-500/15 blur-[120px] pointer-events-none" aria-hidden="true"></div>
                    <div class="absolute -left-24 -top-24 w-80 h-80 rounded-full bg-rose-500/10 blur-[120px] pointer-events-none" aria-hidden="true"></div>

                    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                        <div class="lg:col-span-8 space-y-4 text-center lg:text-left">
                            <span class="inline-block px-3.5 py-1.5 rounded-full bg-white/10 text-zinc-300 text-xs font-bold uppercase tracking-widest border border-white/10">
                                Merchant Program
                            </span>
                            <h3 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-tight">
                                Grow your brand across India's next-gen ecosystem.
                            </h3>
                            <p class="text-sm sm:text-base text-zinc-400 max-w-xl leading-relaxed">
                                Join verified businesses selling retail goods, artisan food, professional services, inspected pre-owned assets, or B2B machinery with automated PhonePe payouts.
                            </p>
                        </div>

                        <div class="lg:col-span-4 flex flex-col sm:flex-row lg:flex-col items-stretch gap-3.5">
                            <a href="{{ route('vendor.register') }}" 
                               class="w-full py-4 px-6 rounded-2xl bg-white hover:bg-zinc-100 text-zinc-950 font-black text-xs sm:text-sm tracking-wide shadow-lg text-center transition active:scale-[0.98]">
                                Open Your Storefront &rarr;
                            </a>
                            <button type="button" 
                                    onclick="if(window.openAuthModal) { window.openAuthModal(null, 'login', 'seller'); } else { window.location.href = '{{ route('login') }}'; }"
                                    class="w-full py-3.5 px-6 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-bold text-xs sm:text-sm border border-white/15 text-center transition">
                                Existing Merchant Portal
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ==========================================
                 11. TRUST & PLATFORM INTEGRITY PILLARS
                 ========================================== -->
            <section class="max-w-6xl mx-auto px-6 sm:px-8">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                    <div class="p-6 rounded-[2rem] bg-white/70 border border-black/[0.06] shadow-[0_4px_20px_rgba(0,0,0,0.02)] text-center">
                        <span class="text-2xl block mb-2">🛡️</span>
                        <h4 class="text-xs sm:text-sm font-bold text-zinc-950">Verified Merchants</h4>
                        <p class="text-[11px] text-zinc-500 mt-1">Aadhaar &amp; GST Vetted Suppliers</p>
                    </div>
                    <div class="p-6 rounded-[2rem] bg-white/70 border border-black/[0.06] shadow-[0_4px_20px_rgba(0,0,0,0.02)] text-center">
                        <span class="text-2xl block mb-2">⚡</span>
                        <h4 class="text-xs sm:text-sm font-bold text-zinc-950">PhonePe Escrow</h4>
                        <p class="text-[11px] text-zinc-500 mt-1">Guaranteed milestone settlements</p>
                    </div>
                    <div class="p-6 rounded-[2rem] bg-white/70 border border-black/[0.06] shadow-[0_4px_20px_rgba(0,0,0,0.02)] text-center">
                        <span class="text-2xl block mb-2">📦</span>
                        <h4 class="text-xs sm:text-sm font-bold text-zinc-950">Tracked Logistics</h4>
                        <p class="text-[11px] text-zinc-500 mt-1">Express pan-India delivery</p>
                    </div>
                    <div class="p-6 rounded-[2rem] bg-white/70 border border-black/[0.06] shadow-[0_4px_20px_rgba(0,0,0,0.02)] text-center">
                        <span class="text-2xl block mb-2">💬</span>
                        <h4 class="text-xs sm:text-sm font-bold text-zinc-950">Direct WhatsApp</h4>
                        <p class="text-[11px] text-zinc-500 mt-1">One-tap direct buyer connection</p>
                    </div>
                </div>
            </section>

        </div>
    </div>
</x-layouts.public>
