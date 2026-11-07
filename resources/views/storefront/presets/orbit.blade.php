{{-- PRESET 05: ORBIT (Kinetic Orbital Horizon · Spatial Luxury · Holographic Floating Pods) --}}
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

<div class="storefront-preset-orbit min-h-screen flex flex-col font-body selection:bg-indigo-500 selection:text-white bg-[#07080f] text-[#f1f5f9] relative overflow-hidden"
     style="--sf-accent: #6366f1; --sf-primary: #07080f;">

    {{-- Subtle Ambient Celestial Aura Back-Glows --}}
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[550px] bg-gradient-to-b from-indigo-600/15 via-cyan-500/10 to-transparent blur-3xl pointer-events-none -z-10"></div>
    <div class="fixed bottom-0 right-0 w-[600px] h-[600px] bg-indigo-900/10 blur-[140px] pointer-events-none -z-10"></div>

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Floating Spatial Island Dock Capsule (Aerodynamic Header) --}}
    <div class="fixed top-3 sm:top-5 inset-x-3 sm:inset-x-6 lg:inset-x-8 z-50 pointer-events-none">
        <header class="max-w-6xl mx-auto p-2 sm:p-2.5 rounded-full bg-[#0b0f1e]/85 backdrop-blur-2xl border border-indigo-500/30 shadow-[0_16px_50px_rgba(0,0,0,0.7)] text-white pointer-events-auto flex items-center justify-between gap-3">
            
            <!-- Left: Pulsing Celestial Node & Store Identity -->
            <div class="flex items-center gap-2.5 pl-2 sm:pl-3 shrink-0">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-2.5 group">
                    <div class="relative flex items-center justify-center">
                        <span class="w-3 h-3 rounded-full bg-cyan-400 animate-ping opacity-75 absolute"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-300 shadow-[0_0_12px_rgba(34,211,238,0.9)] relative"></span>
                    </div>

                    @if($storeLogo)
                        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-7 h-7 rounded-full object-cover border border-white/20">
                    @endif

                    <span class="font-black text-sm sm:text-base tracking-tight text-white group-hover:text-cyan-300 transition truncate max-w-[130px] sm:max-w-xs">
                        {{ $storeName }}
                    </span>

                    <span class="hidden sm:inline-flex items-center gap-1 text-[9px] font-mono px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                        <span class="w-1 h-1 rounded-full bg-indigo-400"></span> ORBIT 2.0
                    </span>
                </a>
            </div>

            <!-- Center: Inline Spatial Department Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-white/[0.04] border border-white/5 text-xs font-mono">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="px-3.5 py-1.5 rounded-full transition {{ !$currentCategory ? 'bg-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(99,102,241,0.5)]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                    ALL ({{ $products->total() ?? $products->count() }})
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                       class="px-3 py-1.5 rounded-full transition {{ $currentCategory === $c->slug ? 'bg-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(99,102,241,0.5)]' : 'text-slate-300 hover:text-white hover:bg-white/5' }}">
                        {{ strtoupper($c->name) }}
                    </a>
                @endforeach
                <a href="#trust" class="px-3 py-1.5 rounded-full text-slate-400 hover:text-cyan-300 transition">
                    PROTOCOL
                </a>
            </nav>

            <!-- Right: Interactive Actions & Bag Dock -->
            <div class="flex items-center gap-2 pr-1">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog"
                   class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white/5 hover:bg-white/10 text-slate-300 text-xs font-mono border border-white/10 transition"
                   title="Search store catalog">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <span>SEARCH</span>
                </a>

                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="px-4 py-2 rounded-full bg-gradient-to-r from-cyan-400 via-indigo-500 to-indigo-600 hover:brightness-110 text-white font-mono font-black text-xs uppercase tracking-wider transition-all duration-200 shadow-[0_0_20px_rgba(99,102,241,0.5)] flex items-center gap-2 cursor-pointer hover:scale-105 active:scale-95">
                    <span>BAG</span>
                    <span id="cart-counter-badge" class="px-1.5 py-0.5 rounded-full bg-black/70 text-cyan-300 text-[10px] font-bold">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </header>
    </div>

    <!-- Space Anchor for Fixed Island Capsule -->
    <div class="h-20 sm:h-24"></div>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- THE GRAND KINETIC ORBITAL HORIZON (HERO STAGE) --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-4">
            <div class="rounded-[2.5rem] sm:rounded-[3.5rem] p-6 sm:p-12 lg:p-16 border border-indigo-500/25 relative overflow-hidden bg-gradient-to-b from-indigo-950/40 via-[#0a0e1c]/80 to-[#03050c] shadow-[0_20px_80px_rgba(99,102,241,0.18)]">
                
                <!-- Concentric Iridescent Planetary Orbital Rings in Background -->
                <div class="absolute -top-32 -right-32 w-[600px] h-[600px] rounded-full border border-indigo-400/20 blur-2xs pointer-events-none"></div>
                <div class="absolute -top-16 -right-16 w-[450px] h-[450px] rounded-full border border-cyan-400/20 blur-2xs pointer-events-none"></div>
                <div class="absolute top-1/2 left-1/4 -translate-y-1/2 w-96 h-96 rounded-full bg-indigo-500/10 blur-3xl pointer-events-none"></div>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center relative z-10">
                    
                    <!-- Left Column: Spatial Telemetry & Brand Statement (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[10px] font-mono tracking-widest uppercase bg-indigo-500/10 text-cyan-300 border border-indigo-500/30 shadow-xs">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_8px_rgba(34,211,238,0.8)]"></span>
                            <span>NODE COORDINATES: {{ strtoupper($vendor->city ?: 'DIRECT STUDIO') }}</span>
                        </div>

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight text-white leading-[1.03]">
                            <span class="block bg-gradient-to-r from-white via-slate-100 to-indigo-200 bg-clip-text text-transparent">
                                {{ $storeName }}
                            </span>
                        </h1>

                        <p class="text-xs sm:text-base text-slate-300 leading-relaxed max-w-xl font-normal">
                            {{ $vendor->description ?: 'Kinetic spatial commerce with verified direct boutique provenance. Each piece is meticulously authenticated, insured, and expedited across Pan-India delivery corridors.' }}
                        </p>

                        <!-- Kinetic Spatial Telemetry Pods -->
                        <div class="grid grid-cols-2 gap-3 sm:gap-4 p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-black/50 backdrop-blur-xl border border-indigo-500/20 text-xs font-mono">
                            <div class="space-y-1">
                                <span class="text-[9px] uppercase tracking-widest text-slate-400 block">DISPATCH VELOCITY</span>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]"></span>
                                    <span class="text-white font-bold text-xs sm:text-sm">EXPRESS 24H READY</span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[9px] uppercase tracking-widest text-slate-400 block">CURATED ARCHIVE</span>
                                <span class="text-cyan-300 font-bold text-xs sm:text-sm block">
                                    {{ $products->total() ?? $products->count() }} VERIFIED SKUS
                                </span>
                            </div>
                        </div>

                        <!-- CTA Row -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="#catalog" 
                               class="px-8 py-4 rounded-full bg-gradient-to-r from-cyan-400 via-indigo-500 to-indigo-600 hover:brightness-110 text-white font-mono font-black text-xs uppercase tracking-wider transition-all shadow-[0_0_30px_rgba(99,102,241,0.5)] hover:scale-102 active:scale-98">
                                EXPLORE ORBIT CATALOG ↓
                            </a>

                            @if($vendor->contact_phone)
                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $vendor->contact_phone) }}?text=Hello%20{{ urlencode($storeName) }},%20I%20am%20exploring%20your%20Orbit%20catalog%20on%20K71" 
                                   target="_blank"
                                   rel="noopener"
                                   class="px-6 py-4 rounded-full bg-white/5 hover:bg-white/10 text-slate-200 font-mono text-xs font-semibold tracking-wider border border-white/15 transition flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    <span>CONCIERGE WA</span>
                                </a>
                            @else
                                <a href="#trust" 
                                   class="px-6 py-4 rounded-full bg-white/5 hover:bg-white/10 text-slate-200 font-mono text-xs font-semibold tracking-wider border border-white/15 transition">
                                    DIAGNOSTICS PROTOCOL
                                </a>
                            @endif
                        </div>

                    </div>

                    <!-- Right Column: Kinetic Spotlight Centerpiece (5 Cols) -->
                    <div class="lg:col-span-5 relative">
                        <div class="aspect-[4/5] rounded-[2.5rem] overflow-hidden border border-indigo-400/30 shadow-[0_0_60px_rgba(99,102,241,0.25)] relative bg-[#090d1a] group">
                            
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $storeName }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter contrast-[1.05]">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-80 pointer-events-none"></div>

                            <!-- Holographic Orbit Tag -->
                            <div class="absolute top-4 left-4 z-10">
                                <span class="px-3.5 py-1.5 rounded-full bg-black/70 backdrop-blur-md border border-indigo-400/40 text-cyan-300 font-mono text-[10px] uppercase tracking-wider shadow-lg flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-pulse"></span>
                                    <span>FLAGSHIP DROP</span>
                                </span>
                            </div>

                            @if($flagship)
                                <div class="absolute bottom-4 inset-x-4 p-4 rounded-2xl bg-[#0b0f1e]/90 backdrop-blur-xl border border-indigo-500/40 shadow-2xl flex items-center justify-between gap-3">
                                    <div class="min-w-0 pr-2">
                                        <span class="text-[9px] font-mono uppercase tracking-widest text-indigo-300 block">FEATURED SELECTION</span>
                                        <span class="text-xs sm:text-sm font-bold text-white truncate block">{{ $flagship->title }}</span>
                                        <span class="text-xs font-mono font-bold text-cyan-300">₹{{ number_format($flagship->base_price / 100, 2) }}</span>
                                    </div>
                                    <button type="button" 
                                            onclick="window.quickAddToCart({{ $flagship->id }}, {{ $flagship->variants->first()?->id ?: 'null' }}, this)"
                                            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-400 to-indigo-500 hover:brightness-110 text-white font-mono font-black text-xs uppercase tracking-wider shrink-0 cursor-pointer shadow-[0_0_15px_rgba(99,102,241,0.5)] active:scale-95 transition">
                                        + DOCK
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
            
            <div class="p-4 sm:p-6 rounded-3xl bg-[#0b0f1e]/70 backdrop-blur-xl border border-indigo-500/20 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-base sm:text-xl font-mono font-black uppercase tracking-wider text-white flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400"></span>
                        INVENTORY CATALOG // {{ $products->total() ?? $products->count() }} PIECES
                    </h2>
                    <p class="text-xs font-mono text-slate-400 mt-0.5">
                        Direct merchant authenticated catalog with immediate door dispatch.
                    </p>
                </div>

                <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-none">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-4 py-2 rounded-full text-xs font-mono uppercase tracking-wider transition shrink-0 {{ !$currentCategory ? 'bg-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(99,102,241,0.5)] ring-2 ring-indigo-400 ring-offset-2 ring-offset-[#07080f]' : 'bg-white/5 text-slate-300 hover:text-white border border-white/10' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-4 py-2 rounded-full text-xs font-mono uppercase tracking-wider transition shrink-0 {{ $currentCategory === $c->slug ? 'bg-indigo-600 text-white font-bold shadow-[0_0_15px_rgba(99,102,241,0.5)] ring-2 ring-indigo-400 ring-offset-2 ring-offset-[#07080f]' : 'bg-white/5 text-slate-300 hover:text-white border border-white/10' }}">
                            {{ $c->name }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Kinetic Modular Product Pods Grid -->
            @if($products->isEmpty())
                <div class="py-20 text-center border border-indigo-500/20 rounded-3xl bg-[#0b0f1e]/60 p-8 space-y-4">
                    <span class="text-3xl font-mono text-cyan-400 block">[ NO PIECES FOUND ]</span>
                    <p class="text-xs text-slate-400">Reset your department query to view full store inventory.</p>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block px-6 py-2.5 rounded-full bg-indigo-600 text-white font-mono font-bold text-xs uppercase shadow-md">
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

                        <div class="group flex flex-col justify-between rounded-[2rem] p-3 sm:p-4 bg-gradient-to-b from-white/[0.05] to-white/[0.01] border border-white/10 hover:border-indigo-400/50 hover:shadow-[0_12px_45px_rgba(99,102,241,0.2)] transition-all duration-500 backdrop-blur-md relative overflow-hidden">
                            
                            <!-- Product Image Canvas -->
                            <div class="relative aspect-square w-full rounded-2xl bg-[#090d1a] overflow-hidden mb-3">
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
                                        <span class="px-2 py-0.5 rounded-full bg-black/80 text-slate-400 font-mono text-[9px] uppercase">
                                            ARCHIVED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2.5 right-2.5 z-10">
                                    <span class="px-2 py-0.5 rounded-full bg-black/70 backdrop-blur-md border border-indigo-500/30 text-cyan-300 font-mono text-[8px] uppercase">
                                        VERIFIED
                                    </span>
                                </div>
                            </div>

                            <!-- Details Canvas -->
                            <div class="flex flex-col justify-between flex-1 gap-2.5">
                                <div>
                                    @if($product->category)
                                        <span class="text-[9px] font-mono uppercase tracking-widest text-cyan-400 block mb-0.5">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="text-xs sm:text-sm font-bold text-white uppercase line-clamp-2 leading-snug group-hover:text-indigo-300 transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                </div>

                                <!-- Price & Action -->
                                <div class="pt-2 border-t border-white/10 space-y-2">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-sm sm:text-base font-black font-mono text-white">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($hasComparePrice)
                                            <span class="text-xs font-mono text-slate-500 line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}, this)"
                                                class="w-full py-2.5 px-3 rounded-xl bg-indigo-500/15 hover:bg-gradient-to-r hover:from-cyan-400 hover:to-indigo-500 text-indigo-300 hover:text-white font-mono font-bold text-xs uppercase tracking-wider border border-indigo-500/30 hover:border-transparent transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95 shadow-xs">
                                            <span>+ DOCK TO BAG</span>
                                        </button>
                                    @else
                                        <div class="w-full py-2 text-center text-[10px] font-mono text-slate-500 uppercase">
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

        {{-- THE ORBITAL ARCHITECTURE PROTOCOL (DIAGNOSTICS MATRIX) --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-6 sm:p-10 rounded-[2.5rem] bg-[#0b0f1e]/80 backdrop-blur-xl border border-indigo-500/25 shadow-xl">
                <div class="pb-5 mb-6 border-b border-indigo-500/20 flex items-center justify-between font-mono">
                    <span class="text-xs font-bold uppercase tracking-widest text-indigo-300 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-cyan-400"></span>
                        ORBITAL PROTOCOL // BUYER GUARANTEES
                    </span>
                    <span class="text-[10px] text-emerald-400 font-bold">100% AUTHENTICATED</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono text-xs">
                    <div class="p-4 rounded-2xl bg-black/50 border border-indigo-500/20 space-y-1.5">
                        <span class="text-cyan-400 font-bold block">01 // DIRECT PROVENANCE</span>
                        <p class="text-slate-400 text-[11px] leading-relaxed">Direct dispatch from {{ $vendor->city ?: 'the verified boutique studio' }}.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-black/50 border border-indigo-500/20 space-y-1.5">
                        <span class="text-cyan-400 font-bold block">02 // ESCROW PROTECTION</span>
                        <p class="text-slate-400 text-[11px] leading-relaxed">Secured via K71 automated buyer protection escrow release.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-black/50 border border-indigo-500/20 space-y-1.5">
                        <span class="text-cyan-400 font-bold block">03 // PRIORITY TELEPORT</span>
                        <p class="text-slate-400 text-[11px] leading-relaxed">Real-time insured express courier transit across all Indian pin codes.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-black/50 border border-indigo-500/20 space-y-1.5">
                        <span class="text-cyan-400 font-bold block">04 // HASSLE-FREE EXCHANGE</span>
                        <p class="text-slate-400 text-[11px] leading-relaxed">{{ $vendor->settings?->return_window_days ?: 7 }}-day seamless size and quality replacement.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Reviews --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Dark High-Contrast Minimal Footer --}}
    @include('storefront.sections.footer')

</div>