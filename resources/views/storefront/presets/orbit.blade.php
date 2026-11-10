{{-- PRESET 05: ORBIT (Kinetic Spatial Horizon · Layered Glass Depth · High-Contrast Telemetry) --}}
@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $flagshipImg = $flagship?->images->first()?->path 
        ? \App\Services\ImageService::url($flagship->images->first()->path, 1200) 
        : ($storeBanner ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1600&q=80');
    $currentCategory = request('category');
    $currentSearch = request('search');
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
@endphp

<div class="storefront-preset-orbit min-h-screen flex flex-col font-body selection:bg-cyan-400 selection:text-black bg-[#050711] text-[#f8fafc] relative overflow-hidden"
     style="--sf-accent: #38bdf8; --sf-primary: #050711;"
     x-data="{ drawerOpen: false }">

    {{-- Layered Cosmic Nebula Ambient Glows --}}
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[1200px] h-[600px] bg-gradient-to-b from-indigo-600/25 via-cyan-500/15 to-transparent blur-[120px] pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-0 w-[700px] h-[700px] bg-indigo-900/15 blur-[160px] pointer-events-none -z-10"></div>
    <div class="fixed top-1/3 left-0 w-[500px] h-[500px] bg-cyan-600/10 blur-[140px] pointer-events-none -z-10"></div>

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Aerodynamic Layered Glass Island Capsule (Ultra-Glassmorphic Floating Header) --}}
    <div class="fixed top-3 sm:top-5 inset-x-3 sm:inset-x-6 lg:inset-x-8 z-50 pointer-events-none">
        <header class="max-w-6xl mx-auto p-2 sm:p-2.5 rounded-full bg-slate-950/60 backdrop-blur-3xl border border-white/25 ring-1 ring-cyan-400/40 shadow-[0_20px_50px_rgba(0,0,0,0.8),inset_0_1px_2px_rgba(255,255,255,0.4)] text-white pointer-events-auto flex items-center justify-between gap-3">
            
            <!-- Left: Hamburger Drawer Trigger + Pulsing Celestial Orbital Bezel & Identity -->
            <div class="flex items-center gap-2 pl-1 sm:pl-2 shrink-0">
                <button type="button" 
                        @click="drawerOpen = true"
                        class="p-1.5 sm:p-2 text-cyan-300 hover:text-white transition cursor-pointer flex items-center justify-center shrink-0"
                        aria-label="Open boutique menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ $vendor->storefront_url }}" class="flex items-center gap-2.5 group">
                    <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-400 to-indigo-600 p-[1.5px] shadow-[0_0_15px_rgba(34,211,238,0.7)] shrink-0">
                        @if($storeLogo)
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-full h-full rounded-full object-cover">
                        @else
                            <div class="w-full h-full rounded-full bg-[#070a18] flex items-center justify-center">
                                <span class="w-2.5 h-2.5 rounded-full bg-cyan-300 shadow-[0_0_8px_rgba(34,211,238,1)]"></span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-col">
                        <span class="font-black text-sm sm:text-base tracking-tight text-white group-hover:text-cyan-300 transition truncate max-w-[130px] sm:max-w-xs drop-shadow-sm">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono font-bold text-slate-300 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_8px_rgba(34,211,238,1)]"></span> ORBIT // GLASS 2.0
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: High-Contrast Planetary Department Pills (Desktop Glassmorphic) -->
            <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-white/[0.06] backdrop-blur-2xl border border-white/15 text-xs font-mono">
                <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                   class="px-3.5 py-1.5 rounded-full transition {{ !$currentCategory ? 'bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(6,182,212,0.6)]' : 'text-slate-200 hover:text-white hover:bg-white/15' }}">
                    ALL ({{ $products->total() ?? $products->count() }})
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ $vendor->storefrontUrl(['category' => $c->slug], 'catalog') }}" 
                       class="px-3 py-1.5 rounded-full transition {{ $currentCategory === $c->slug ? 'bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(6,182,212,0.6)]' : 'text-slate-200 hover:text-white hover:bg-white/15' }}">
                        {{ strtoupper($c->name) }}
                    </a>
                @endforeach
                <a href="#trust" class="px-3 py-1.5 rounded-full text-slate-200 hover:text-white hover:bg-white/15 transition font-bold">
                    PROTOCOL
                </a>
            </nav>

            <!-- Right: Search & Interactive Bag Capsule -->
            <div class="flex items-center gap-2 pr-1">
                <a href="{{ $vendor->storefrontUrl([], 'catalog') }}"
                   class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/[0.08] hover:bg-white/20 text-white text-xs font-mono font-bold border border-white/20 backdrop-blur-xl transition shadow-xs"
                   title="Search store catalog">
                    <svg class="w-3.5 h-3.5 text-cyan-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>SEARCH</span>
                </a>

                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="p-2 sm:px-3 sm:py-2 rounded-full bg-gradient-to-r from-cyan-400 via-sky-400 to-indigo-500 hover:brightness-110 text-slate-950 font-mono font-black text-xs uppercase tracking-wider transition-all duration-200 shadow-[0_0_20px_rgba(34,211,238,0.6)] flex items-center gap-1.5 cursor-pointer hover:scale-105 active:scale-95"
                        aria-label="Shopping Cart">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 rounded-full bg-black text-white text-[10px] font-bold">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </header>
    </div>

    <!-- Space Anchor for Fixed Header -->
    <div class="h-20 sm:h-24"></div>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- THE GRAND KINETIC ORBITAL HORIZON (HERO STAGE WITH HIGH CONTRAST & DEPTH) --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-4">
            <div class="rounded-[2.5rem] sm:rounded-[3.5rem] p-6 sm:p-12 lg:p-16 border border-white/20 ring-1 ring-cyan-500/30 relative overflow-hidden bg-gradient-to-b from-[#0c1228]/95 via-[#080d1e]/95 to-[#04060d] shadow-[0_25px_80px_rgba(0,0,0,0.85),_inset_0_1px_2px_rgba(255,255,255,0.3)]">
                
                <!-- Concentric Planetary Orbital Horizon Rings -->
                <div class="absolute -top-32 -right-32 w-[650px] h-[650px] rounded-full border border-indigo-400/25 pointer-events-none"></div>
                <div class="absolute -top-16 -right-16 w-[500px] h-[500px] rounded-full border border-cyan-400/30 pointer-events-none"></div>
                <div class="absolute top-1/2 left-1/4 -translate-y-1/2 w-96 h-96 rounded-full bg-indigo-500/15 blur-3xl pointer-events-none"></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center relative z-10">
                    
                    <!-- Left Column: Spatial Telemetry & Brand Statement (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] font-mono tracking-widest uppercase bg-black/60 text-cyan-300 border border-cyan-400/40 shadow-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_8px_rgba(34,211,238,1)]"></span>
                            <span>NODE COORDINATES: {{ strtoupper($vendor->city ?: 'DIRECT STUDIO') }}</span>
                        </div>

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-[1.03]">
                            <span class="block bg-gradient-to-r from-white via-slate-100 to-cyan-200 bg-clip-text text-transparent drop-shadow-sm">
                                {{ $storeName }}
                            </span>
                        </h1>

                        <p class="text-xs sm:text-base text-slate-200 leading-relaxed max-w-xl font-normal drop-shadow-xs">
                            {{ $vendor->description ?: 'Kinetic spatial commerce with verified direct boutique provenance. Each piece is meticulously authenticated, insured, and expedited across Pan-India delivery corridors.' }}
                        </p>

                        <!-- Kinetic Spatial Telemetry Pods (High-Contrast & Visible) -->
                        <div class="grid grid-cols-2 gap-3 sm:gap-4 p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-black/70 backdrop-blur-2xl border border-white/15 text-xs font-mono shadow-md">
                            <div class="space-y-1">
                                <span class="text-[9px] uppercase tracking-widest text-slate-300 font-bold block">DISPATCH VELOCITY</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,1)]"></span>
                                    <span class="text-white font-extrabold text-xs sm:text-sm">EXPRESS 24H READY</span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] uppercase tracking-widest text-slate-300 font-bold block">CURATED ARCHIVE</span>
                                <span class="text-cyan-300 font-extrabold text-xs sm:text-sm block">
                                    {{ $products->total() ?? $products->count() }} VERIFIED SKUS
                                </span>
                            </div>
                        </div>

                        <!-- CTA Row -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="#catalog" 
                               class="px-8 py-4 rounded-full bg-gradient-to-r from-cyan-400 via-sky-400 to-indigo-500 hover:brightness-110 text-slate-950 font-mono font-black text-xs uppercase tracking-wider transition-all shadow-[0_0_30px_rgba(34,211,238,0.6)] hover:scale-102 active:scale-98">
                                EXPLORE ORBIT CATALOG ↓
                            </a>

                            @if($vendor->contact_phone)
                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $vendor->contact_phone) }}?text=Hello%20{{ urlencode($storeName) }},%20I%20am%20exploring%20your%20Orbit%20catalog%20on%20K71" 
                                   target="_blank"
                                   rel="noopener"
                                   class="px-6 py-4 rounded-full bg-white/10 hover:bg-white/20 text-white font-mono text-xs font-bold tracking-wider border border-white/25 transition flex items-center gap-2 shadow-xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span>CONCIERGE WA</span>
                                </a>
                            @else
                                <a href="#trust" 
                                   class="px-6 py-4 rounded-full bg-white/10 hover:bg-white/20 text-white font-mono text-xs font-bold tracking-wider border border-white/25 transition shadow-xs">
                                    DIAGNOSTICS PROTOCOL
                                </a>
                            @endif
                        </div>

                    </div>

                    <!-- Right Column: Kinetic Spotlight Centerpiece (5 Cols) -->
                    <div class="lg:col-span-5 relative">
                        <div class="aspect-[4/5] rounded-[2.5rem] overflow-hidden border border-white/20 ring-1 ring-cyan-500/40 shadow-[0_25px_60px_rgba(0,0,0,0.9),_0_0_40px_rgba(6,182,212,0.3)] relative bg-[#090d1a] group">
                            
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $storeName }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-106 filter contrast-[1.05]">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-black/20 to-transparent opacity-90 pointer-events-none"></div>

                            <!-- Holographic Orbit Tag -->
                            <div class="absolute top-4 left-4 z-10">
                                <span class="px-3.5 py-1.5 rounded-full bg-black/85 backdrop-blur-md border border-cyan-400/50 text-cyan-300 font-mono text-[10px] uppercase tracking-wider shadow-lg flex items-center gap-1.5 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                    <span>FLAGSHIP DROP</span>
                                </span>
                            </div>

                            @if($flagship)
                                <div class="absolute bottom-4 inset-x-4 p-4 rounded-2xl bg-[#0b0f20]/95 backdrop-blur-2xl border border-white/20 shadow-2xl flex flex-col gap-2.5">
                                    <div>
                                        <span class="text-[9px] font-mono font-bold uppercase tracking-widest text-cyan-400 block">FEATURED SELECTION</span>
                                        <span class="text-sm font-bold text-white truncate block">{{ $flagship->title }}</span>
                                        <span class="text-sm font-mono font-black text-cyan-300">₹{{ number_format($flagship->base_price / 100, 2) }}</span>
                                    </div>
                                    <button type="button" 
                                            onclick="window.quickAddToCart({{ $flagship->id }}, {{ $flagship->variants->first()?->id ?: 'null' }}, this)"
                                            class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-cyan-400 via-sky-400 to-indigo-500 hover:brightness-110 text-slate-950 font-mono font-black text-xs uppercase tracking-wider shrink-0 cursor-pointer shadow-[0_0_20px_rgba(34,211,238,0.6)] active:scale-95 transition">
                                        + DOCK TO BAG
                                    </button>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- PLANETARY CATEGORY DISCS (SLIDER FILTER) --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            
            <div class="p-4 sm:p-6 rounded-3xl bg-[#0b0f20]/80 backdrop-blur-xl border border-white/15 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-lg">
                <div>
                    <h2 class="text-base sm:text-xl font-mono font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                        INVENTORY CATALOG // {{ $products->total() ?? $products->count() }} PIECES
                    </h2>
                    <p class="text-xs font-mono text-slate-300 mt-0.5">
                        Direct merchant authenticated catalog with immediate door dispatch.
                    </p>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                       class="px-4 py-2 rounded-full text-xs font-mono uppercase tracking-wider transition shrink-0 {{ !$currentCategory ? 'bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(6,182,212,0.6)] ring-2 ring-cyan-400 ring-offset-2 ring-offset-[#050711]' : 'bg-white/10 text-slate-200 hover:text-white border border-white/15' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ $vendor->storefrontUrl(['category' => $c->slug], 'catalog') }}" 
                           class="px-4 py-2 rounded-full text-xs font-mono uppercase tracking-wider transition shrink-0 {{ $currentCategory === $c->slug ? 'bg-gradient-to-r from-cyan-500 to-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(6,182,212,0.6)] ring-2 ring-cyan-400 ring-offset-2 ring-offset-[#050711]' : 'bg-white/10 text-slate-200 hover:text-white border border-white/15' }}">
                            {{ $c->name }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Kinetic Modular Product Pods Grid -->
            @if($products->isEmpty())
                <div class="py-20 text-center border border-white/15 rounded-3xl bg-[#0b0f20]/60 p-8 space-y-4">
                    <span class="text-3xl font-mono text-cyan-400 block">[ NO PIECES FOUND ]</span>
                    <p class="text-xs text-slate-300">Reset your department query to view full store inventory.</p>
                    <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" class="inline-block px-6 py-2.5 rounded-full bg-cyan-500 text-slate-950 font-mono font-bold text-xs uppercase shadow-md">
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
                            $defaultVariantId = $product->variants->first()?->id;
                        @endphp

                        <div class="group flex flex-col justify-between rounded-[2rem] p-3 sm:p-4 bg-gradient-to-b from-white/[0.07] to-white/[0.02] border border-white/15 hover:border-cyan-400/60 hover:shadow-[0_15px_45px_rgba(6,182,212,0.25)] transition-all duration-300 backdrop-blur-md relative overflow-hidden">
                            
                            <!-- Product Image Canvas -->
                            <div class="relative aspect-square w-full rounded-2xl bg-[#080c1b] overflow-hidden mb-3">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="{{ $product->title }}">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-108 {{ $isOutOfStock ? 'opacity-40 grayscale' : '' }}">
                                </a>

                                <!-- Badges -->
                                <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10">
                                    @if($hasComparePrice && $discountPct > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white font-mono font-black text-[9px] uppercase shadow-xs">
                                            -{{ $discountPct }}%
                                        </span>
                                    @endif
                                    @if($isOutOfStock)
                                        <span class="px-2 py-0.5 rounded-full bg-black/85 text-slate-300 font-mono text-[9px] uppercase">
                                            ARCHIVED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2.5 right-2.5 z-10">
                                    <span class="px-2 py-0.5 rounded-full bg-black/85 backdrop-blur-md border border-cyan-400/50 text-cyan-300 font-mono text-[8px] uppercase font-bold">
                                        VERIFIED
                                    </span>
                                </div>
                            </div>

                            <!-- Details Canvas (High-Contrast Visible Text) -->
                            <div class="flex flex-col justify-between flex-1 gap-2.5">
                                <div>
                                    @if($product->category)
                                        <span class="text-[10px] font-mono font-bold uppercase tracking-widest text-cyan-400 block mb-0.5">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="text-xs sm:text-sm font-bold text-white uppercase line-clamp-2 leading-snug group-hover:text-cyan-300 transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                </div>

                                <!-- Price & Action on its own Single Line Down -->
                                <div class="pt-2 border-t border-white/15 space-y-2">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-base sm:text-lg font-black font-mono text-white">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($hasComparePrice)
                                            <span class="text-xs font-mono text-slate-400 line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Add button on Single Line Down -->
                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}, this)"
                                                class="w-full py-2.5 px-3 rounded-xl bg-gradient-to-r from-cyan-400 via-sky-400 to-indigo-500 hover:brightness-110 text-slate-950 font-mono font-black text-xs uppercase tracking-wider transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95 shadow-[0_0_15px_rgba(34,211,238,0.5)]">
                                            <span>+ DOCK TO BAG</span>
                                        </button>
                                    @else
                                        <div class="w-full py-2 text-center text-[10px] font-mono font-bold text-slate-400 uppercase bg-white/5 rounded-xl border border-white/10">
                                            Out of Stock
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

        {{-- Genuine Reviews (Immediately Above Happy Customers) --}}
        @include('storefront.sections.reviews_section')

        {{-- Happy Customers Photos & Videos Slider (Shown Just Below Reviews) --}}
        @include('storefront.sections.happy_customers')

        {{-- THE ORBITAL ARCHITECTURE PROTOCOL (DIAGNOSTICS MATRIX) --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-6 sm:p-10 rounded-[2.5rem] bg-[#0b0f20]/90 backdrop-blur-xl border border-white/20 shadow-2xl">
                <div class="pb-5 mb-6 border-b border-white/15 flex items-center justify-between font-mono">
                    <span class="text-xs font-bold uppercase tracking-widest text-cyan-300 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 shadow-[0_0_8px_rgba(34,211,238,1)]"></span>
                        ORBITAL PROTOCOL // BUYER GUARANTEES
                    </span>
                    <span class="text-[10px] text-emerald-400 font-bold">100% AUTHENTICATED</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono text-xs">
                    <div class="p-4 rounded-2xl bg-black/60 border border-white/15 space-y-1.5">
                        <span class="text-cyan-300 font-bold block">01 // DIRECT PROVENANCE</span>
                        <p class="text-slate-200 text-[11px] leading-relaxed">Direct dispatch from {{ $vendor->city ?: 'the verified boutique studio' }}.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-black/60 border border-white/15 space-y-1.5">
                        <span class="text-cyan-300 font-bold block">02 // ESCROW PROTECTION</span>
                        <p class="text-slate-200 text-[11px] leading-relaxed">Secured via K71 automated buyer protection escrow release.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-black/60 border border-white/15 space-y-1.5">
                        <span class="text-cyan-300 font-bold block">03 // PRIORITY TELEPORT</span>
                        <p class="text-slate-200 text-[11px] leading-relaxed">Real-time insured express courier transit across all Indian pin codes.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-black/60 border border-white/15 space-y-1.5">
                        <span class="text-cyan-300 font-bold block">04 // HASSLE-FREE EXCHANGE</span>
                        <p class="text-slate-200 text-[11px] leading-relaxed">{{ $vendor->settings?->return_window_days ?: 7 }}-day seamless size and quality replacement.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Dark High-Contrast Minimal Footer --}}
    @include('storefront.sections.footer')

    {{-- Slide-From-Left Menu Drawer --}}
    @include('storefront.sections.theme_drawer')

</div>
