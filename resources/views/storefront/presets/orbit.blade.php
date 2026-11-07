{{-- PRESET 05: ORBIT (Cyber-Spatial · Tactile Spatial Tech · Holographic Telemetry Pods) --}}
@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $flagshipImg = $flagship?->images->first()?->path ? \App\Services\ImageService::url($flagship->images->first()->path, 1200) : ($storeBanner ?: 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1600&q=80');
    $currentCategory = request('category');
    $currentSearch = request('search');
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
@endphp

<div class="storefront-preset-orbit min-h-screen flex flex-col font-body selection:bg-cyan-500 selection:text-black bg-[#080d1a] text-[#f1f5f9]"
     style="--sf-accent: #06b6d4; --sf-primary: #080d1a;">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Floating Spatial Island Dock Capsule (Ultra-Modern Award-Winning Header) --}}
    <div class="fixed top-3 sm:top-5 inset-x-3 sm:inset-x-6 z-50 pointer-events-none">
        <header class="max-w-5xl mx-auto p-2 sm:p-2.5 rounded-full bg-slate-900/85 backdrop-blur-2xl border border-cyan-500/30 shadow-[0_12px_40px_rgba(6,182,212,0.18)] text-white pointer-events-auto flex items-center justify-between gap-4">
            
            <!-- Left: Pulsing System Status & Store Name -->
            <div class="flex items-center gap-3 pl-2 sm:pl-3 shrink-0">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-2.5 group">
                    <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 animate-pulse shadow-[0_0_10px_rgba(6,182,212,0.8)]"></span>
                    <span class="font-black text-sm sm:text-base tracking-tight text-white group-hover:text-cyan-300 transition truncate max-w-[140px] sm:max-w-xs">
                        {{ $storeName }}
                    </span>
                    <span class="hidden sm:inline-block text-[9px] font-mono px-2 py-0.5 rounded-full bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                        SPATIAL OS
                    </span>
                </a>
            </div>

            <!-- Center: Inline Spatial Department Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-5 text-xs font-mono tracking-wider text-slate-300">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="hover:text-cyan-300 transition {{ !$currentCategory ? 'text-cyan-400 font-bold' : '' }}">
                    [ CATALOG ]
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" class="hover:text-cyan-300 transition {{ $currentCategory === $c->slug ? 'text-cyan-400 font-bold' : '' }}">
                        [ {{ strtoupper($c->name) }} ]
                    </a>
                @endforeach
                <a href="#trust" class="hover:text-cyan-300 transition">[ TELEMETRY ]</a>
            </nav>

            <!-- Right: Interactive Tactile Bag Capsule -->
            <div class="flex items-center gap-2 pr-1">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="px-4 py-2 rounded-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-mono font-black text-xs uppercase tracking-wider transition-all duration-200 shadow-[0_0_15px_rgba(6,182,212,0.4)] flex items-center gap-1.5 cursor-pointer hover:scale-105 active:scale-95">
                    <span>BAG</span>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 rounded-full bg-slate-950 text-cyan-300 text-[10px] font-bold">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </header>
    </div>

    <!-- Space Anchor for Fixed Header -->
    <div class="h-20 sm:h-24"></div>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- SPATIAL HOLOGRAPHIC TELEMETRY STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-2 sm:pt-4">
            <div class="p-6 sm:p-12 rounded-3xl bg-slate-900/60 backdrop-blur-2xl border border-cyan-500/25 shadow-[0_0_50px_rgba(6,182,212,0.12)] relative overflow-hidden">
                
                <!-- Ambient Cyan Glow Bulb in Stage Background -->
                <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-cyan-500/15 blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-blue-600/10 blur-3xl pointer-events-none"></div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center relative z-10">
                    
                    <!-- Left: Telemetry Data & Actions (6 Cols) -->
                    <div class="lg:col-span-6 space-y-6">
                        
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-[10px] font-mono tracking-widest uppercase bg-cyan-500/10 text-cyan-300 border border-cyan-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-cyan-400"></span>
                            <span>NODE LOCATION: {{ strtoupper($vendor->city ?: 'DIRECT STUDIO') }}</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-white leading-[1.05]">
                            {{ $storeName }}
                        </h1>

                        <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-lg">
                            {{ $vendor->description ?: 'Spatial retail architecture with verified direct merchant dispatches. Every product is authenticated before entering doorstep express courier transit.' }}
                        </p>

                        <!-- Telemetry Grid -->
                        <div class="grid grid-cols-2 gap-3 p-4 rounded-2xl bg-slate-950/60 border border-cyan-500/20 text-xs font-mono">
                            <div>
                                <span class="text-[9px] uppercase tracking-widest text-slate-500 block">ENCRYPTION</span>
                                <span class="text-cyan-300 font-bold">256-BIT SSL PROTECTED</span>
                            </div>
                            <div>
                                <span class="text-[9px] uppercase tracking-widest text-slate-500 block">CATALOG INVENTORY</span>
                                <span class="text-white font-bold">{{ $products->total() ?? $products->count() }} VERIFIED SKUS</span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="#catalog" 
                               class="px-8 py-3.5 rounded-full bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-mono font-black text-xs uppercase tracking-wider transition-all shadow-[0_0_20px_rgba(6,182,212,0.4)] hover:scale-102">
                                INITIALIZE CATALOG ↓
                            </a>
                            <a href="#trust" 
                               class="px-6 py-3.5 rounded-full bg-slate-800 hover:bg-slate-700 text-white font-mono text-xs font-semibold tracking-wider border border-cyan-500/30 transition">
                                DIAGNOSTIC MATRIX
                            </a>
                        </div>

                    </div>

                    <!-- Right: Spatial Holographic Product Spotlight (6 Cols) -->
                    <div class="lg:col-span-6 relative">
                        <div class="aspect-[4/5] rounded-3xl overflow-hidden border border-cyan-500/30 shadow-[0_0_35px_rgba(6,182,212,0.2)] relative bg-slate-950 group">
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $storeName }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 filter contrast-[1.08]">
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent pointer-events-none"></div>

                            <!-- Holographic Floating Badge -->
                            <div class="absolute top-4 left-4 z-10">
                                <span class="px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-md border border-cyan-500/40 text-cyan-300 font-mono text-[10px] uppercase tracking-wider">
                                    ✦ VERIFIED DIRECT NODE
                                </span>
                            </div>

                            @if($flagship)
                                <div class="absolute bottom-4 inset-x-4 p-4 rounded-2xl bg-slate-900/90 backdrop-blur-xl border border-cyan-500/40 shadow-xl flex items-center justify-between">
                                    <div class="min-w-0 pr-3">
                                        <span class="text-[9px] font-mono uppercase text-cyan-400 block">PRIMARY SKU</span>
                                        <span class="text-xs font-bold text-white truncate block">{{ $flagship->title }}</span>
                                    </div>
                                    <button type="button" 
                                            onclick="window.quickAddToCart({{ $flagship->id }}, {{ $flagship->variants->first()?->id ?: 'null' }}, this)"
                                            class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-mono font-black text-xs uppercase tracking-wider shrink-0 cursor-pointer shadow-[0_0_12px_rgba(6,182,212,0.5)]">
                                        + DOCK
                                    </button>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- TACTILE MODULAR PODS PRODUCT GRID --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            
            <div class="p-6 rounded-3xl bg-slate-900/60 backdrop-blur-xl border border-cyan-500/20 mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-mono font-black uppercase tracking-wider text-cyan-300">
                        INVENTORY TELEMETRY // CATALOG
                    </h2>
                    <p class="text-xs font-mono text-slate-400 mt-0.5">
                        {{ $products->total() ?? $products->count() }} active verified units ready for immediate teleport
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-mono uppercase tracking-wider transition {{ !$currentCategory ? 'bg-cyan-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:text-white border border-slate-700' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-3.5 py-1.5 rounded-full text-xs font-mono uppercase tracking-wider transition {{ $currentCategory === $c->slug ? 'bg-cyan-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 hover:text-white border border-slate-700' }}">
                            {{ $c->name }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Modular Pods Grid -->
            @if($products->isEmpty())
                <div class="py-16 text-center border border-cyan-500/20 rounded-3xl bg-slate-900/60 p-8 space-y-3">
                    <span class="text-2xl font-mono text-cyan-400 block">[ NO SKUS FOUND ]</span>
                    <p class="text-xs text-slate-400">Reset your department query to view full store inventory.</p>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block px-5 py-2 rounded-full bg-cyan-500 text-slate-950 font-mono font-bold text-xs uppercase">
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

                        <div class="group flex flex-col justify-between rounded-3xl bg-slate-900/60 backdrop-blur-xl border border-cyan-500/20 hover:border-cyan-400/60 hover:shadow-[0_0_30px_rgba(6,182,212,0.18)] transition-all duration-300 overflow-hidden relative">
                            
                            <!-- Image Frame -->
                            <div class="relative aspect-square w-full bg-slate-950 overflow-hidden">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="{{ $product->title }}">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-106 {{ $isOutOfStock ? 'opacity-40 grayscale' : '' }}">
                                </a>

                                <!-- Telemetry Badges -->
                                <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10">
                                    @if($hasComparePrice && $discountPct > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-rose-500 text-white font-mono font-black text-[9px] uppercase">
                                            -{{ $discountPct }}%
                                        </span>
                                    @endif
                                    @if($isOutOfStock)
                                        <span class="px-2 py-0.5 rounded-full bg-slate-800 text-slate-400 font-mono text-[9px] uppercase">
                                            ARCHIVED
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2.5 right-2.5 z-10">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-950/80 border border-cyan-500/40 text-cyan-300 font-mono text-[9px] uppercase">
                                        VERIFIED
                                    </span>
                                </div>
                            </div>

                            <!-- Details Canvas -->
                            <div class="p-4 flex flex-col justify-between flex-1 gap-3">
                                <div>
                                    @if($product->category)
                                        <span class="text-[9px] font-mono uppercase tracking-widest text-cyan-400 block mb-0.5">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="text-xs sm:text-sm font-bold text-white uppercase line-clamp-2 leading-snug group-hover:text-cyan-300 transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                    <div class="text-[10px] font-mono text-slate-400 pt-0.5">
                                        ORIGIN: {{ strtoupper($vendor->city ?: 'HQ') }}
                                    </div>
                                </div>

                                <!-- Price & Action -->
                                <div class="pt-2 border-t border-slate-800/80 space-y-2">
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
                                                class="w-full py-2.5 px-3 rounded-xl bg-cyan-500/15 hover:bg-cyan-500 text-cyan-300 hover:text-slate-950 font-mono font-bold text-xs uppercase tracking-wider border border-cyan-500/30 transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95 shadow-xs">
                                            <span>+ DOCK TO BAG</span>
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

        {{-- HOLOGRAPHIC DIAGNOSTIC TRUST MATRIX --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-6 sm:p-10 rounded-3xl bg-slate-900/60 backdrop-blur-xl border border-cyan-500/25">
                <div class="pb-5 mb-6 border-b border-cyan-500/20 flex items-center justify-between font-mono">
                    <span class="text-xs font-bold uppercase tracking-widest text-cyan-300">
                        DIAGNOSTIC PROTOCOL // GUARANTEES
                    </span>
                    <span class="text-[10px] text-emerald-400">AUTHENTICATION PASSED</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 font-mono text-xs">
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-cyan-500/20 space-y-1">
                        <span class="text-cyan-400 font-bold block">01 // DIRECT NODE</span>
                        <p class="text-slate-400 text-[11px]">Dispatches from {{ $vendor->city ?: 'studio workshop' }}.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-cyan-500/20 space-y-1">
                        <span class="text-cyan-400 font-bold block">02 // VERIFIED SELLER</span>
                        <p class="text-slate-400 text-[11px]">K71 Buyer Protection escrow guarantee.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-cyan-500/20 space-y-1">
                        <span class="text-cyan-400 font-bold block">03 // EXPRESS TRANSIT</span>
                        <p class="text-slate-400 text-[11px]">Trackable courier transit across all pin codes.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950/60 border border-cyan-500/20 space-y-1">
                        <span class="text-cyan-400 font-bold block">04 // EXCHANGE MESH</span>
                        <p class="text-slate-400 text-[11px]">{{ $vendor->settings?->return_window_days ?: 7 }}-day direct size exchange.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Reviews --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Minimal Dark Footer --}}
    @include('storefront.sections.footer')

</div>
