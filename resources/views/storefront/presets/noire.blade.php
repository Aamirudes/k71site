{{-- PRESET 02: NOIRÉ (Architectural Brutalist Dark Luxury · Asymmetric Campaign Split · Monospace Dispatch Terminal) --}}
@php
    $flagshipImg = null;
    $cartCount = 0;
    $currentCategory = request('category');
    $currentSearch = request('search');
    $currentSort = request('sort', 'newest');

    if (empty($isServiceWebsite)) {
        $flagship = $featuredProducts->first() ?? $products->first();
        $flagshipImg = $flagship?->images->first()?->path ? \App\Services\ImageService::url($flagship->images->first()->path, 1200) : ($storeBanner ?: 'https://images.unsplash.com/photo-1509631179647-0177331693ae?auto=format&fit=crop&w=1600&q=80');
        if (auth()->check()) {
            $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
            $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
        }
    }
@endphp

<div class="storefront-preset-noire min-h-screen flex flex-col font-mono selection:bg-white selection:text-black bg-[#09090b] text-[#fafafa]"
     style="--sf-accent: #ffffff; --sf-primary: #09090b;"
     x-data="{ drawerOpen: false }">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    @if(!empty($isServiceWebsite))
        @php $nav = $providerSite['nav_labels'] ?? []; $id = $providerSite['identity'] ?? []; @endphp
        <div class="w-full bg-[#121215] border-b border-zinc-800/80 py-1.5 px-4 overflow-hidden text-[10px] font-mono tracking-[0.2em] uppercase text-zinc-400 select-none">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <span class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                    <span>// {{ strtoupper($id['provider_mode_label'] ?? 'STUDIO PRACTICE') }}: {{ strtoupper($vendor->city ?: 'GLOBAL REMOTE') }} //</span>
                </span>
                <span class="hidden sm:inline-block text-zinc-400">{{ strtoupper($id['availability_label'] ?? 'AVAILABLE FOR PROJECTS') }}</span>
                <span class="text-white">{{ strtoupper($vendor->slug) }} [LIVE]</span>
            </div>
        </div>

        <header class="sticky top-0 z-40 bg-[#09090b]/95 backdrop-blur-xl border-b border-zinc-800/90 text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 shrink-0">
                    <button type="button" @click="drawerOpen = true" class="p-2 -ml-2 text-white hover:text-zinc-300 transition cursor-pointer flex items-center justify-center shrink-0" aria-label="Open menu">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ $vendor->storefront_url }}" class="flex items-center gap-3 group">
                        @if(!empty($storeLogo))
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-8 sm:h-10 w-auto object-contain">
                        @else
                            <div class="w-8 h-8 rounded-none border border-white flex items-center justify-center font-mono font-black text-xs text-white">
                                {{ $id['initials'] ?? strtoupper(substr($storeName, 0, 2)) }}
                            </div>
                        @endif
                        <div class="flex flex-col">
                            <span class="font-mono font-black text-sm sm:text-lg tracking-[0.2em] uppercase text-white group-hover:text-zinc-300 transition">{{ $storeName }}</span>
                            <span class="text-[9px] font-mono text-zinc-400 tracking-widest uppercase">{{ $id['work_mode_label'] ?? 'INDEPENDENT' }}</span>
                        </div>
                    </a>
                </div>

                <nav class="hidden md:flex items-center gap-6 text-xs font-mono tracking-wider uppercase text-zinc-400">
                    <a href="#overview" class="hover:text-white transition">[ {{ strtoupper($nav['overview'] ?? 'Overview') }} ]</a>
                    <a href="#portfolio" class="hover:text-white transition">[ {{ strtoupper($nav['work'] ?? 'Work') }} ]</a>
                    <a href="#services" class="hover:text-white transition">[ {{ strtoupper($nav['services'] ?? 'Services') }} ]</a>
                    <a href="#about" class="hover:text-white transition">[ {{ strtoupper($nav['about'] ?? 'About') }} ]</a>
                    <a href="#reviews" class="hover:text-white transition">[ {{ strtoupper($nav['reviews'] ?? 'Reviews') }} ]</a>
                </nav>

                <a href="#contact" class="px-4 py-2 border border-white bg-white text-black hover:bg-zinc-200 transition text-xs font-mono font-bold uppercase tracking-wider">
                    {{ $id['cta_primary_text'] ?? 'Start a Project' }}
                </a>
            </div>
        </header>

        @include('storefront.sections.service.main')
    @else
    {{-- Top Architectural Live Marquee Tape --}}
    <div class="w-full bg-[#121215] border-b border-zinc-800/80 py-1.5 px-4 overflow-hidden text-[10px] font-mono tracking-[0.2em] uppercase text-zinc-400 select-none">
        <div class="flex items-center justify-between max-w-7xl mx-auto">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-white animate-pulse"></span>
                <span>// LIVE STUDIO DISPATCH: {{ strtoupper($vendor->city ?: 'DIRECT WORKSHOP') }} //</span>
            </span>
            <span class="hidden sm:inline-block text-zinc-400">100% VERIFIED AUTHENTIC • DOORSTEP PAN-INDIA TRANSIT</span>
            <span class="text-white">K71 MERCHANT ARCHIVE [ACTIVE]</span>
        </div>
    </div>

    {{-- Stark Monochrome Architectural Header --}}
    <header class="sticky top-0 z-40 bg-[#09090b]/95 backdrop-blur-xl border-b border-zinc-800/90 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            
            <!-- Left: Hamburger Drawer Trigger + Minimalist Store Logo & Coordinates -->
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" 
                        @click="drawerOpen = true"
                        class="p-2 -ml-2 text-white hover:text-zinc-300 transition cursor-pointer flex items-center justify-center shrink-0"
                        aria-label="Open boutique menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ $vendor->storefront_url }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-8 sm:h-10 w-auto object-contain filter invert contrast-200">
                    @else
                        <div class="w-8 h-8 rounded-none border border-white flex items-center justify-center font-mono font-black text-xs text-white">
                            {{ strtoupper(substr($storeName, 0, 2)) }}
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-mono font-black text-sm sm:text-lg tracking-[0.2em] uppercase text-white group-hover:text-zinc-300 transition">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono text-zinc-500 tracking-widest uppercase">
                            EDITION // 2026
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Live Catalog Filter Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-6 text-xs font-mono tracking-wider uppercase text-zinc-400">
                <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" class="hover:text-white transition {{ !$currentCategory ? 'text-white font-bold underline underline-offset-8' : '' }}">
                    [ ALL ARCHIVE ]
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ $vendor->storefrontUrl(['category' => $c->slug], 'catalog') }}" class="hover:text-white transition {{ $currentCategory === $c->slug ? 'text-white font-bold underline underline-offset-8' : '' }}">
                        [ {{ strtoupper($c->name) }} ]
                    </a>
                @endforeach
                <a href="#trust" class="hover:text-white transition">[ PROTOCOL ]</a>
            </nav>

            <!-- Right: Search & Stark Bag Counter Button (With Bag Icon, No Plain Text) -->
            <div class="flex items-center gap-3">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="p-2 sm:px-3 sm:py-2 rounded-none border border-white hover:bg-white hover:text-black transition-all duration-200 flex items-center gap-2 cursor-pointer shadow-sm text-white"
                        aria-label="Shopping Cart">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 bg-white text-black text-[10px] font-mono font-bold">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </div>
    </header>

    <main class="flex-1 space-y-4 sm:space-y-6">
        
        {{-- ASYMMETRIC HIGH-FASHION CAMPAIGN SPLIT STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 border border-zinc-800 bg-[#121215] overflow-hidden shadow-2xl">
                
                <!-- Left Campaign Visual (7 Cols) -->
                <div class="lg:col-span-7 relative min-h-[240px] sm:min-h-[380px] lg:min-h-[600px] overflow-hidden bg-black flex flex-col justify-between p-5 sm:p-8 lg:p-10 group">
                    <img src="{{ $flagshipImg }}" 
                         alt="{{ $flagship->title ?? $storeName }}" 
                         class="absolute inset-0 w-full h-full object-cover object-center filter grayscale contrast-[1.15] opacity-80 group-hover:scale-105 group-hover:grayscale-0 transition-all duration-700">
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/40 to-black/60 pointer-events-none"></div>

                    <!-- Top Monospace Tag -->
                    <div class="relative z-10 flex items-center justify-between text-[11px] font-mono tracking-widest text-white/80">
                        <span class="px-2.5 py-1 bg-black/80 border border-white/20 uppercase text-zinc-300">
                            CAMPAIGN NO. 01 // {{ strtoupper($vendor->city ?: 'STUDIO') }}
                        </span>
                        <span class="px-2.5 py-1 bg-white text-black font-bold uppercase">
                            AUTHENTIC DROP
                        </span>
                    </div>

                    <!-- Lower Visual Stage Headline -->
                    <div class="relative z-10 space-y-3">
                        <p class="text-xs font-mono uppercase tracking-[0.3em] text-zinc-400">
                            {{ $vendor->tagline ?: 'High-Precision Handcrafted Collection' }}
                        </p>
                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black font-mono tracking-tight text-white uppercase leading-[0.95]">
                            {{ $storeName }}
                        </h1>
                        <p class="text-xs sm:text-sm text-zinc-300 max-w-lg leading-relaxed line-clamp-2">
                            {{ $vendor->description ?: 'Strict standard product engineering. 100% genuine verified units packaged straight from our studio with trackable pan-India delivery.' }}
                        </p>
                    </div>
                </div>

                <!-- Right Diagnostic Dispatch Terminal (5 Cols) -->
                <div class="lg:col-span-5 p-5 sm:p-8 lg:p-10 flex flex-col justify-between border-t lg:border-t-0 lg:border-l border-zinc-800 bg-[#0d0d10] space-y-5 sm:space-y-6">
                    
                    <!-- Diagnostic Terminal Header -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-800 text-[10px] text-zinc-400 uppercase tracking-widest">
                            <span class="text-zinc-400">TERMINAL TELEMETRY</span>
                            <span class="text-white font-bold flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-white animate-pulse"></span>
                                READY FOR DISPATCH
                            </span>
                        </div>

                        <!-- Technical Specification Matrix -->
                        <div class="space-y-2 text-xs font-mono text-zinc-300">
                            <div class="flex justify-between py-1 border-b border-zinc-800/60">
                                <span class="text-zinc-500 uppercase">MERCHANT ID:</span>
                                <span class="font-bold text-white uppercase">{{ $vendor->slug }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-800/60">
                                <span class="text-zinc-500 uppercase">ORIGIN:</span>
                                <span class="text-white uppercase">{{ $vendor->city ?: 'INDIA WORKSHOP' }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-800/60">
                                <span class="text-zinc-500 uppercase">ACTIVE CATALOG:</span>
                                <span class="text-white font-bold">{{ $products->total() ?? $products->count() }} SKUs</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-zinc-800/60">
                                <span class="text-zinc-500 uppercase">BUYER PROTOCOL:</span>
                                <span class="text-white font-bold">K71 ESCROW PROTECTED</span>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Anchor Trigger -->
                    <div>
                        <a href="#catalog" class="block text-center py-3.5 px-6 bg-white hover:bg-zinc-200 text-black font-mono font-black text-xs uppercase tracking-widest transition-all duration-200 shadow-lg cursor-pointer">
                            [ EXPLORE CATALOG ({{ $products->total() ?? $products->count() }} SKUS) ↓ ]
                        </a>
                    </div>

                </div>

            </div>
        </section>

        {{-- ARCHITECTURAL LOOKBOOK PRODUCT GRID (ZERO TOP GAP) --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-0 pb-4">
            
            <!-- Category Filtering & Sort Bar -->
            <div class="p-4 sm:p-5 border border-zinc-800 bg-[#121215] mb-5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-mono font-black uppercase tracking-wider text-white">
                        PRODUCT ARCHIVE // 2026
                    </h2>
                    <p class="text-xs text-zinc-400 font-mono mt-0.5">
                        Showing {{ $products->total() ?? $products->count() }} authentic pieces ready for doorstep courier
                    </p>
                </div>

                <!-- Department Selector Tabs -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                       class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ !$currentCategory ? 'bg-white text-black font-bold' : 'bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white' }}">
                        ALL [{{ $products->total() ?? $products->count() }}]
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ $vendor->storefrontUrl(['category' => $c->slug], 'catalog') }}" 
                           class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ $currentCategory === $c->slug ? 'bg-white text-black font-bold' : 'bg-zinc-900 border border-zinc-800 text-zinc-400 hover:text-white' }}">
                            {{ strtoupper($c->name) }} [{{ $c->products_count }}]
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Product Lookbook Grid -->
            @if($products->isEmpty())
                <div class="py-16 text-center border border-zinc-800 bg-[#121215] p-8 space-y-3">
                    <span class="text-2xl font-mono text-zinc-500 block">[ EMPTY ARCHIVE ]</span>
                    <p class="text-xs text-zinc-400">No items match this category filter.</p>
                    <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" class="inline-block mt-3 px-5 py-2 border border-white text-xs font-mono uppercase tracking-widest text-white hover:bg-white hover:text-black transition">
                        RESET FILTER
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($products as $product)
                        @php
                            $priceRupees = number_format($product->base_price / 100, 2);
                            $hasComparePrice = !empty($product->compare_at_price) && $product->compare_at_price > $product->base_price;
                            $comparePriceRupees = $hasComparePrice ? number_format($product->compare_at_price / 100, 2) : null;
                            $discountPct = $hasComparePrice ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                            
                            $imgFirst = $product->images->first()?->path ? \App\Services\ImageService::url($product->images->first()->path, 600) : asset('images/product-placeholder.svg');
                            $isOutOfStock = $product->total_stock <= 0 && $product->variants->sum('stock') <= 0;
                            $defaultVariantId = $product->variants->first()?->id;
                        @endphp

                        <div class="group flex flex-col justify-between border border-zinc-800 bg-[#121215] hover:border-zinc-500 transition-all duration-300 relative">
                            
                            <!-- Image Frame -->
                            <div class="relative aspect-[3/4] w-full bg-black overflow-hidden">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="{{ $product->title }}">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover filter contrast-[1.08] transition-transform duration-500 group-hover:scale-105 {{ $isOutOfStock ? 'opacity-40 grayscale' : '' }}">
                                </a>

                                <!-- Stark Badges -->
                                <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10">
                                    @if($hasComparePrice && $discountPct > 0)
                                        <span class="px-2 py-0.5 bg-white text-black font-mono font-black text-[9px] uppercase tracking-wider">
                                            -{{ $discountPct }}%
                                        </span>
                                    @endif
                                    @if($isOutOfStock)
                                        <span class="px-2 py-0.5 bg-zinc-900 border border-zinc-700 text-zinc-400 font-mono text-[9px] uppercase">
                                            ARCHIVED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2.5 right-2.5 z-10">
                                    <span class="px-2 py-0.5 bg-black/80 border border-white/20 text-white font-mono text-[9px] uppercase">
                                        VERIFIED
                                    </span>
                                </div>
                            </div>

                            <!-- Product Info -->
                            <div class="p-3.5 sm:p-4 flex flex-col justify-between flex-1 gap-3">
                                <div>
                                    @if($product->category)
                                        <span class="text-[9px] font-mono uppercase tracking-widest text-zinc-400 block mb-1 font-bold">
                                            // {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="text-xs sm:text-sm font-bold font-mono text-white uppercase line-clamp-2 leading-snug group-hover:text-zinc-300 transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                    <div class="text-[10px] font-mono text-zinc-300 pt-1 font-semibold">
                                        DISPATCH: {{ strtoupper($vendor->city ?: 'STUDIO') }}
                                    </div>
                                </div>

                                <!-- Price & Action -->
                                <div class="pt-2 border-t border-zinc-800/80 space-y-2">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-sm sm:text-base font-black font-mono text-white">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($hasComparePrice)
                                            <span class="text-xs font-mono text-zinc-500 line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}, this)"
                                                class="w-full py-2.5 px-3 border border-zinc-700 hover:border-white bg-zinc-900 hover:bg-white text-zinc-300 hover:text-black font-mono font-bold text-xs uppercase tracking-wider transition-all duration-200 flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                            <span>+ ADD TO BAG</span>
                                        </button>
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

                @if(method_exists($products, 'links') && $products->hasPages())
                    <div class="mt-10 flex justify-center font-mono">
                        {{ $products->fragment('catalog')->links() }}
                    </div>
                @endif
            @endif

        </section>

        {{-- Studio Shorts & Video Reels Showcase (Shown Just Below Product) --}}
        @include('storefront.sections.video_showcase')

        {{-- Genuine Customer Reviews (Immediately Above Happy Customers) --}}
        @include('storefront.sections.reviews_section')

        {{-- Happy Customers Photos & Videos Slider (Shown Just Below Reviews) --}}
        @include('storefront.sections.happy_customers')

        {{-- MONOSPACE DIAGNOSTIC TRUST PROTOCOL --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-6 sm:p-10 border border-zinc-800 bg-[#121215]">
                <div class="pb-6 mb-6 border-b border-zinc-800 flex items-center justify-between">
                    <span class="text-xs font-mono font-bold tracking-[0.2em] uppercase text-zinc-400">
                        // PROTOCOL SPECIFICATIONS & GUARANTEES
                    </span>
                    <span class="text-[10px] font-mono text-zinc-300 font-bold">AUTHENTICATED MERCHANT</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 font-mono">
                    <div class="space-y-1.5 p-3.5 border border-zinc-800/80 bg-zinc-950/40">
                        <span class="text-xs font-bold text-zinc-500 block">01 // DISPATCH</span>
                        <h4 class="text-xs font-bold text-white uppercase">Direct Studio Origin</h4>
                        <p class="text-[11px] text-zinc-400">Units package directly from {{ $vendor->city ?: 'the vendor workshop' }}.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 border border-zinc-800/80 bg-zinc-950/40">
                        <span class="text-xs font-bold text-zinc-500 block">02 // ESCROW</span>
                        <h4 class="text-xs font-bold text-white uppercase">K71 Buyer Protection</h4>
                        <p class="text-[11px] text-zinc-400">Funds released only after confirmed doorstep receipt.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 border border-zinc-800/80 bg-zinc-950/40">
                        <span class="text-xs font-bold text-zinc-500 block">03 // COURIER</span>
                        <h4 class="text-xs font-bold text-white uppercase">Express Pan-India</h4>
                        <p class="text-[11px] text-zinc-400">Real-time trackable door-to-door surface & air transit.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 border border-zinc-800/80 bg-zinc-950/40">
                        <span class="text-xs font-bold text-zinc-500 block">04 // EXCHANGE</span>
                        <h4 class="text-xs font-bold text-white uppercase">{{ $vendor->settings?->return_window_days ?: 7 }}-Day Merchant Window</h4>
                        <p class="text-[11px] text-zinc-400">Direct size and product replacement support.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Client Services & WhatsApp Concierge Desk --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Minimal Dark Footer --}}
    @include('storefront.sections.footer')
    @endif

    {{-- Slide-From-Left Menu Drawer --}}
    @include('storefront.sections.theme_drawer')

</div>
