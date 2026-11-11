{{-- PRESET 07: SOLARIS (Sun-Drenched Artisanal · Mediterranean Terracotta Arches · Roman Serif Luxury) --}}
@php
    $flagshipImg = null;
    $cartCount = 0;
    $currentCategory = request('category');
    $currentSearch = request('search');

    if (empty($isServiceWebsite)) {
        $flagship = $featuredProducts->first() ?? $products->first();
        $flagshipImg = $flagship?->images->first()?->path
            ? \App\Services\ImageService::url($flagship->images->first()->path, 1200)
            : ($storeBanner ?: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=1600&q=80');
        if (auth()->check()) {
            $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
            $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
        }
    }
@endphp

<div class="storefront-preset-solaris min-h-screen flex flex-col font-body selection:bg-[#c25e3e] selection:text-white bg-[#fcfaf6] text-[#291a13]"
     style="--sf-accent: #c25e3e; --sf-primary: #291a13;"
     x-data="{ drawerOpen: false }">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    @if(!empty($isServiceWebsite))
        @php $nav = $providerSite['nav_labels'] ?? []; $id = $providerSite['identity'] ?? []; @endphp
        <div class="sticky top-0 z-40 px-3 sm:px-6 py-2.5 transition-all">
            <header class="max-w-7xl mx-auto rounded-3xl bg-[#fcfaf6]/95 backdrop-blur-2xl border border-[#e8dccb] shadow-[0_10px_30px_rgba(41,26,19,0.06)] px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4">
                <div class="flex items-center gap-2.5 sm:gap-3.5 shrink-0">
                    <button type="button" @click="drawerOpen = true" class="p-2 -ml-2 text-[#291a13] hover:text-[#c25e3e] transition cursor-pointer flex items-center justify-center shrink-0" aria-label="Open menu">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ $vendor->storefront_url }}" class="flex items-center gap-3 group">
                        @if(!empty($storeLogo))
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-t-2xl rounded-b-lg border-2 border-[#c25e3e] bg-white p-1 overflow-hidden shadow-xs">
                                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-full h-full object-contain">
                            </div>
                        @else
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-t-2xl rounded-b-lg bg-[#c25e3e] text-white flex items-center justify-center font-heading text-sm sm:text-base font-bold shadow-xs">
                                {{ $id['initials'] ?? strtoupper(substr($storeName, 0, 1)) }}
                            </div>
                        @endif
                        <div class="flex flex-col">
                            <span class="font-heading font-black text-sm sm:text-lg tracking-wider text-[#291a13] group-hover:text-[#c25e3e] transition uppercase">{{ $storeName }}</span>
                            <span class="text-[9px] font-mono tracking-widest text-[#785b4f] uppercase">{{ $id['provider_mode_label'] ?? 'STUDIO ATELIER' }}</span>
                        </div>
                    </a>
                </div>

                <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-[#f3ede3] border border-[#e8dccb] text-xs font-mono">
                    <a href="#overview" class="px-3.5 py-1.5 rounded-full text-[#785b4f] hover:text-[#291a13] hover:bg-white transition">{{ strtoupper($nav['overview'] ?? 'Overview') }}</a>
                    <a href="#portfolio" class="px-3.5 py-1.5 rounded-full text-[#785b4f] hover:text-[#291a13] hover:bg-white transition">{{ strtoupper($nav['work'] ?? 'Work') }}</a>
                    <a href="#services" class="px-3.5 py-1.5 rounded-full text-[#785b4f] hover:text-[#291a13] hover:bg-white transition">{{ strtoupper($nav['services'] ?? 'Services') }}</a>
                    <a href="#about" class="px-3.5 py-1.5 rounded-full text-[#785b4f] hover:text-[#291a13] hover:bg-white transition">{{ strtoupper($nav['about'] ?? 'About') }}</a>
                    <a href="#reviews" class="px-3.5 py-1.5 rounded-full text-[#785b4f] hover:text-[#291a13] hover:bg-white transition">{{ strtoupper($nav['reviews'] ?? 'Reviews') }}</a>
                </nav>

                <a href="#contact" class="px-4 py-2 rounded-full bg-[#c25e3e] text-white hover:bg-[#291a13] transition text-xs font-bold">
                    {{ $id['cta_primary_text'] ?? 'Start a Project' }}
                </a>
            </header>
        </div>

        @include('storefront.sections.service.main')
    @else
    {{-- Sun-Warmed Marquee Ticker --}}
    <div class="w-full bg-[#291a13] text-[#e8dccb] text-[10px] font-mono tracking-[0.2em] uppercase py-2 px-4 overflow-hidden border-b border-[#3d291e]">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-[#c25e3e] animate-pulse"></span>
                <span>ORIGIN: {{ strtoupper($vendor->city ?: 'MEDITERRANEAN WORKSHOP') }}</span>
            </span>
            <span class="hidden md:inline-block">100% ARTISANAL BOTANICAL & EARTH CRAFT · PAN-INDIA TRANSIT</span>
            <span class="text-[#e09f3e] font-bold">SOLAR ATELIER</span>
        </div>
    </div>

    {{-- Terracotta Arched Floating Capsule Header --}}
    <div class="sticky top-0 z-40 px-3 sm:px-6 py-2.5 transition-all">
        <header class="max-w-7xl mx-auto rounded-3xl bg-[#fcfaf6]/95 backdrop-blur-2xl border border-[#e8dccb] shadow-[0_10px_30px_rgba(41,26,19,0.06)] px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-4">
            
            <!-- Left: Hamburger Drawer Trigger + Brand Emblem & Arched Monogram -->
            <div class="flex items-center gap-2.5 sm:gap-3.5 shrink-0">
                <button type="button" 
                        @click="drawerOpen = true"
                        class="p-2 -ml-2 text-[#291a13] hover:text-[#c25e3e] transition cursor-pointer flex items-center justify-center shrink-0"
                        aria-label="Open boutique menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ $vendor->storefront_url }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-t-2xl rounded-b-lg border-2 border-[#c25e3e] bg-white p-1 overflow-hidden shadow-xs">
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-t-2xl rounded-b-lg bg-[#c25e3e] text-white flex items-center justify-center font-heading text-sm sm:text-base font-bold shadow-xs">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-heading font-black text-sm sm:text-lg tracking-wider text-[#291a13] group-hover:text-[#c25e3e] transition uppercase">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono tracking-widest text-[#785b4f] uppercase">
                            ARTISAN REGISTRY // 2026
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Arched Department Navigation -->
            <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-[#f3ede3] border border-[#e8dccb] text-xs font-mono">
                <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                   class="px-4 py-1.5 rounded-full transition {{ !$currentCategory ? 'bg-[#c25e3e] text-white font-bold shadow-xs' : 'text-[#785b4f] hover:text-[#291a13] hover:bg-white' }}">
                    COLLECTION ({{ $products->total() ?? $products->count() }})
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ $vendor->storefrontUrl(['category' => $c->slug], 'catalog') }}" 
                       class="px-4 py-1.5 rounded-full transition {{ $currentCategory === $c->slug ? 'bg-[#c25e3e] text-white font-bold shadow-xs' : 'text-[#785b4f] hover:text-[#291a13] hover:bg-white' }}">
                        {{ strtoupper($c->name) }}
                    </a>
                @endforeach
                <a href="#trust" class="px-4 py-1.5 rounded-full text-[#785b4f] hover:text-[#291a13] transition font-bold">
                    PROVENANCE
                </a>
            </nav>

            <!-- Right: Bag Pill (With Bag Icon, No Plain Text) -->
            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="p-2 sm:px-3 sm:py-2 rounded-full bg-white text-black border border-[#e8dccb] hover:border-[#c25e3e] transition-all duration-200 shadow-md flex items-center gap-2 cursor-pointer hover:scale-105 active:scale-95"
                        aria-label="Shopping Cart">
                    <svg class="w-5 h-5 shrink-0 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 rounded-full bg-[#c25e3e] text-white text-[10px] font-mono font-black">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </header>
    </div>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- SUN-DRENCHED ARCHED ATELIER HERO STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-4">
            <div class="rounded-[2.5rem] p-6 sm:p-12 lg:p-16 border-2 border-[#e8dccb] bg-gradient-to-br from-[#f8f2e9] via-[#fcfaf6] to-[#f4ebe1] shadow-md relative overflow-hidden">
                
                <!-- Mediterranean Decorative Sun Arches -->
                <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full border-2 border-[#c25e3e]/15 pointer-events-none"></div>
                <div class="absolute -top-12 -right-12 w-56 h-56 rounded-full border border-[#c25e3e]/20 pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                    
                    <!-- Left Column: Terracotta Warm Editorial (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] font-mono tracking-widest uppercase bg-[#c25e3e]/10 text-[#c25e3e] border border-[#c25e3e]/20 font-bold">
                            <span>◈ SUN-CURED ARTISANAL CRAFT</span>
                            <span>•</span>
                            <span>{{ strtoupper($vendor->city ?: 'DIRECT STUDIO') }}</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-heading font-black tracking-tight text-[#291a13] leading-[1.08]">
                            {{ $storeName }}
                        </h1>

                        <p class="text-xs sm:text-base text-[#785b4f] leading-relaxed max-w-xl font-normal">
                            {{ $vendor->description ?: 'Bespoke artisanal creations infused with organic warmth, honest materials, and master workshop heritage. Direct merchant provenance with insured doorstep transit across India.' }}
                        </p>

                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <a href="#catalog" class="px-8 py-3.5 rounded-full bg-[#c25e3e] hover:bg-[#a84c2f] text-white font-mono text-xs font-bold uppercase tracking-wider transition-all duration-200 text-center shadow-md hover:scale-105 active:scale-95">
                                Explore Atelier ({{ $products->total() ?? $products->count() }} Items) ↓
                            </a>
                            <span class="text-[11px] font-mono text-[#785b4f] text-center sm:text-left">
                                Guaranteed Authentic · Escrow Shield
                            </span>
                        </div>

                    </div>

                    <!-- Right Column: Arched Visual Portal (5 Cols) -->
                    <div class="lg:col-span-5 flex justify-center">
                        <div class="relative w-full max-w-[340px] aspect-[3/4] rounded-t-[7rem] rounded-b-3xl overflow-hidden border-3 border-[#c25e3e] shadow-xl bg-[#291a13] group">
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $flagship->title ?? $storeName }}" 
                                 class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700">
                            
                            <div class="absolute inset-x-0 bottom-0 p-4 bg-gradient-to-t from-[#291a13] via-[#291a13]/70 to-transparent text-white">
                                <span class="text-[10px] font-mono uppercase tracking-widest text-[#e8dccb] block">Atelier Highlight</span>
                                <h4 class="font-heading font-bold text-sm truncate">{{ $flagship->title ?? $storeName }}</h4>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- ARCHED ARTISANAL PRODUCT ARCHIVE --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            
            <!-- Category Navigation -->
            <div class="p-4 sm:p-5 rounded-3xl bg-[#f3ede3] border border-[#e8dccb] mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-heading font-black tracking-tight text-[#291a13]">
                        Atelier Catalog
                    </h2>
                    <p class="text-xs font-mono text-[#785b4f] mt-0.5">
                        Discover authentic handcrafted items ready for insured dispatch
                    </p>
                </div>

                <!-- Categories -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                       class="px-4 py-1.5 rounded-full text-xs font-mono uppercase tracking-wider transition {{ !$currentCategory ? 'bg-[#c25e3e] text-white font-bold shadow-xs' : 'bg-white border border-[#e8dccb] text-[#785b4f] hover:text-[#291a13]' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ $vendor->storefrontUrl(['category' => $c->slug], 'catalog') }}" 
                           class="px-4 py-1.5 rounded-full text-xs font-mono uppercase tracking-wider transition {{ $currentCategory === $c->slug ? 'bg-[#c25e3e] text-white font-bold shadow-xs' : 'bg-white border border-[#e8dccb] text-[#785b4f] hover:text-[#291a13]' }}">
                            {{ strtoupper($c->name) }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="py-16 text-center rounded-3xl border border-[#e8dccb] bg-white p-8 space-y-3">
                    <span class="text-2xl font-heading text-[#785b4f] block">◈ Atelier Empty ◈</span>
                    <p class="text-xs text-[#785b4f]">No pieces match this selected category.</p>
                    <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" class="inline-block mt-3 px-6 py-2 rounded-full border border-[#c25e3e] text-xs font-mono uppercase tracking-widest text-[#c25e3e] hover:bg-[#c25e3e] hover:text-white transition">
                        Reset Filter
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
                        @endphp
                        
                        <div class="group rounded-3xl border border-[#e8dccb] bg-white hover:border-[#c25e3e] hover:shadow-lg transition-all duration-300 flex flex-col justify-between overflow-hidden">
                            
                            <!-- Arched Image Frame -->
                            <div class="relative aspect-[3/4] bg-[#f8f2e9] overflow-hidden">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>

                                @if($discountPct > 0)
                                    <div class="absolute top-2.5 left-2.5">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#c25e3e] text-white font-mono text-[9px] font-bold uppercase tracking-wider shadow-xs">
                                            -{{ $discountPct }}%
                                        </span>
                                    </div>
                                @endif

                                @if($product->is_featured)
                                    <div class="absolute top-2.5 right-2.5">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#291a13] text-[#e8dccb] font-mono text-[9px] font-bold uppercase tracking-wider shadow-xs">
                                            ARTISAN
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Details -->
                            <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <span class="text-[9px] font-mono text-[#785b4f] uppercase tracking-widest block truncate mb-1">
                                        {{ $product->category?->name ?? 'EARTH CRAFT' }}
                                    </span>
                                    <h3 class="text-xs sm:text-sm font-heading font-bold text-[#291a13] line-clamp-2 leading-snug group-hover:text-[#c25e3e] transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="pt-2 border-t border-[#f3ede3] flex items-baseline justify-between gap-1">
                                    <div class="flex items-baseline gap-1.5 flex-wrap">
                                        <span class="text-sm sm:text-base font-bold font-mono text-[#291a13]">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($comparePriceRupees)
                                            <span class="text-xs font-mono text-[#785b4f] line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Add to Bag Action -->
                                <div class="pt-1">
                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $product->variants->first()?->id ?? 'null' }}, this)"
                                                class="w-full py-2 px-3 rounded-xl bg-[#f8f2e9] hover:bg-[#c25e3e] hover:text-white text-[#291a13] font-mono text-[11px] font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer border border-[#e8dccb]">
                                            + Add to Bag
                                        </button>
                                    @else
                                        <div class="w-full py-2 rounded-xl text-center text-[10px] font-mono text-[#785b4f] uppercase bg-[#f3ede3]">
                                            Sold Out
                                        </div>
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

        {{-- Studio Shorts & Video Reels Showcase --}}
        @include('storefront.sections.video_showcase')

        {{-- Genuine Customer Reviews (Immediately Above Happy Customers) --}}
        @include('storefront.sections.reviews_section')

        {{-- Happy Customers Photos & Videos Slider (Shown Just Below Reviews) --}}
        @include('storefront.sections.happy_customers')

        {{-- PROVENANCE & VALUES GUARANTEES --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="rounded-3xl border border-[#e8dccb] bg-[#f8f2e9] p-6 sm:p-10 shadow-xs">
                <div class="pb-5 mb-6 border-b border-[#e8dccb] flex items-center justify-between font-mono text-xs">
                    <span class="font-bold uppercase tracking-[0.2em] text-[#c25e3e] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#c25e3e]"></span>
                        SOLARIS ATELIER PROVENANCE
                    </span>
                    <span class="text-[#785b4f]">VERIFIED MAKER SEAL</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 font-mono text-xs">
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-white border border-[#e8dccb]">
                        <span class="text-[10px] text-[#c25e3e] uppercase block font-bold">01 // MAKER DISPATCH</span>
                        <h4 class="font-bold text-[#291a13] uppercase">Single Workshop</h4>
                        <p class="text-[11px] text-[#785b4f]">Directly packaged in {{ $vendor->city ?: 'the maker studio' }}.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-white border border-[#e8dccb]">
                        <span class="text-[10px] text-[#c25e3e] uppercase block font-bold">02 // ESCROW</span>
                        <h4 class="font-bold text-[#291a13] uppercase">K71 Protection</h4>
                        <p class="text-[11px] text-[#785b4f]">Payment held until verified doorstep arrival.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-white border border-[#e8dccb]">
                        <span class="text-[10px] text-[#c25e3e] uppercase block font-bold">03 // COURIER</span>
                        <h4 class="font-bold text-[#291a13] uppercase">Insured Transit</h4>
                        <p class="text-[11px] text-[#785b4f]">Trackable door-to-door transit across all Indian pin codes.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 rounded-2xl bg-white border border-[#e8dccb]">
                        <span class="text-[10px] text-[#c25e3e] uppercase block font-bold">04 // ASSISTANCE</span>
                        <h4 class="font-bold text-[#291a13] uppercase">Maker Care</h4>
                        <p class="text-[11px] text-[#785b4f]">{{ $vendor->settings?->return_window_days ?: 7 }}-day direct size & quality assistance.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Warm Artisanal Footer --}}
    @include('storefront.sections.footer')
    @endif

    {{-- Slide-From-Left Menu Drawer --}}
    @include('storefront.sections.theme_drawer')

</div>

