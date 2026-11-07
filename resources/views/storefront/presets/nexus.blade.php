{{-- PRESET 08: NEXUS (Neo-Tokyo Cyber Techwear · Tactical HUD & Electric Lime · Carbon Obsidian) --}}
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

<div class="storefront-preset-nexus min-h-screen flex flex-col font-mono selection:bg-[#a3e635] selection:text-black bg-[#0a0b0e] text-[#f3f4f6]"
     style="--sf-accent: #a3e635; --sf-primary: #0a0b0e;"
     x-data="{ drawerOpen: false }">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Live Cyber Telemetry Ticker --}}
    <div class="w-full bg-[#111318] border-b border-zinc-800 text-[10px] font-mono py-1.5 px-4 overflow-hidden select-none">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-none bg-[#a3e635] animate-pulse"></span>
                <span class="text-[#a3e635] font-bold">NEXUS//SYS: ONLINE</span>
                <span class="text-zinc-500 hidden sm:inline">// DISPATCH NODE: {{ strtoupper($vendor->city ?: 'CENTRAL HUB') }}</span>
            </span>
            <span class="hidden md:inline-block text-zinc-400">ENCRYPTED K71 ESCROW PROTOCOL · 100% VERIFIED HARDWARE/DROP</span>
            <span class="text-white font-bold">[PAN-INDIA AIR/ROAD TRANSIT]</span>
        </div>
    </div>

    {{-- Tactical HUD Header --}}
    <header class="sticky top-0 z-40 bg-[#0a0b0e]/95 backdrop-blur-xl border-b border-zinc-800 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            
            <!-- Left: Hamburger Drawer Trigger + Cyber Stencil Brand -->
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" 
                        @click="drawerOpen = true"
                        class="p-2 -ml-2 text-white hover:text-[#a3e635] transition cursor-pointer flex items-center justify-center shrink-0"
                        aria-label="Open boutique menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-none border border-[#a3e635] bg-[#111318] p-1 shadow-[0_0_10px_rgba(163,230,53,0.3)]">
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-full h-full object-contain filter invert">
                        </div>
                    @else
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-none border-2 border-[#a3e635] bg-[#111318] text-[#a3e635] flex items-center justify-center font-heading font-black text-sm sm:text-base shadow-[0_0_12px_rgba(163,230,53,0.35)]">
                            {{ strtoupper(substr($storeName, 0, 2)) }}
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-heading font-black text-base sm:text-xl tracking-wider text-white group-hover:text-[#a3e635] transition uppercase">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono text-zinc-500 tracking-widest uppercase">
                            SECTOR // {{ $vendor->slug }}
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Tactical Navigation Tabs -->
            <nav class="hidden md:flex items-center gap-1 border border-zinc-800 bg-[#111318] p-1 text-xs">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="px-3.5 py-1.5 transition {{ !$currentCategory ? 'bg-[#a3e635] text-black font-black shadow-xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                    [ ALL_SKUS // {{ $products->total() ?? $products->count() }} ]
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                       class="px-3.5 py-1.5 transition {{ $currentCategory === $c->slug ? 'bg-[#a3e635] text-black font-black shadow-xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800' }}">
                        [ {{ strtoupper($c->name) }} ]
                    </a>
                @endforeach
                <a href="#trust" class="px-3.5 py-1.5 text-zinc-400 hover:text-white transition">
                    [ SYS_SPECS ]
                </a>
            </nav>

            <!-- Right: Tactical Bag Button (With Bag Icon, No Plain Text) -->
            <div class="flex items-center gap-3">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="p-2 sm:px-3 sm:py-2 rounded-none border border-[#a3e635] bg-[#111318] hover:bg-[#a3e635] text-[#a3e635] hover:text-black transition-all duration-200 flex items-center gap-2 cursor-pointer shadow-[0_0_12px_rgba(163,230,53,0.2)]"
                        aria-label="Shopping Cart">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 bg-[#a3e635] text-black text-[10px] font-black font-mono">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </div>
    </header>

    <main class="flex-1 space-y-10 sm:space-y-14">
        
        {{-- CYBER COMMAND HUD HERO STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6">
            <div class="border-2 border-zinc-800 bg-[#111318] grid grid-cols-1 lg:grid-cols-12 relative overflow-hidden shadow-2xl">
                
                <!-- Corner Tactical Crosshairs -->
                <span class="absolute top-2 left-2 text-xs text-[#a3e635] font-mono pointer-events-none">[+]</span>
                <span class="absolute top-2 right-2 text-xs text-[#a3e635] font-mono pointer-events-none">[+]</span>
                <span class="absolute bottom-2 left-2 text-xs text-[#a3e635] font-mono pointer-events-none">[+]</span>
                <span class="absolute bottom-2 right-2 text-xs text-[#a3e635] font-mono pointer-events-none">[+]</span>

                <!-- Left Column: Tactical Diagnostics & Statement (7 Cols) -->
                <div class="lg:col-span-7 p-6 sm:p-12 lg:p-14 flex flex-col justify-between space-y-8 border-b lg:border-b-0 lg:border-r border-zinc-800">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-800 text-[10px] text-zinc-400">
                            <span class="text-[#a3e635] font-bold">// SEC_LEVEL: 01 // DROP: ACTIVE</span>
                            <span class="text-zinc-400 font-mono">LATENCY: 8ms</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-heading font-black tracking-tight text-white uppercase leading-[0.98]">
                            {{ $storeName }}
                        </h1>

                        <p class="text-xs sm:text-sm text-zinc-300 max-w-lg leading-relaxed">
                            {{ $vendor->description ?: 'High-durability technical garments and gear engineered with millimeter precision. Guaranteed 100% verified authentic units packaged straight from studio workshops.' }}
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-[11px] font-mono text-zinc-400 bg-black/60 p-3 border border-zinc-800">
                            <div>MERCHANT: <span class="text-white font-bold">{{ $vendor->slug }}</span></div>
                            <div>CATALOG: <span class="text-[#a3e635] font-bold">{{ $products->total() ?? $products->count() }} SKUS</span></div>
                            <div>TRANSIT: <span class="text-white">PAN-INDIA</span></div>
                            <div>ESCROW: <span class="text-[#a3e635]">PROTECTED</span></div>
                        </div>

                        <div class="pt-2">
                            <a href="#catalog" class="block text-center py-4 px-6 bg-[#a3e635] hover:bg-[#86c026] text-black font-mono font-black text-xs uppercase tracking-widest transition-all duration-200 shadow-[0_0_20px_rgba(163,230,53,0.3)] cursor-pointer">
                                [ INITIALIZE CATALOG DISCOVERY ({{ $products->total() ?? $products->count() }} SKUS) ↓ ]
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Visual Specimen Frame (5 Cols) -->
                <div class="lg:col-span-5 relative min-h-[300px] sm:min-h-[440px] bg-black overflow-hidden group">
                    <img src="{{ $flagshipImg }}" 
                         alt="{{ $flagship->title ?? $storeName }}" 
                         class="absolute inset-0 w-full h-full object-cover object-center filter grayscale contrast-125 group-hover:filter-none group-hover:scale-105 transition-all duration-700">
                    
                    <div class="absolute inset-0 bg-gradient-to-t from-black via-black/30 to-transparent pointer-events-none"></div>

                    <div class="absolute bottom-4 left-4 right-4 bg-black/90 p-3 border border-zinc-700 flex items-center justify-between">
                        <div class="min-w-0 pr-2">
                            <span class="text-[9px] text-[#a3e635] block font-mono">SPECIMEN NO. 01</span>
                            <span class="text-xs font-bold text-white truncate block">{{ $flagship->title ?? 'Featured Unit' }}</span>
                        </div>
                        @if($flagship)
                            <a href="#catalog" class="text-xs text-[#a3e635] hover:underline shrink-0">
                                DOCK &rarr;
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </section>

        {{-- TACTICAL HARDWARE LOOKBOOK ARCHIVE --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            
            <!-- Category Navigation -->
            <div class="border border-zinc-800 bg-[#111318] p-4 sm:p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg sm:text-xl font-heading font-black uppercase tracking-wider text-white">
                        HARDWARE INVENTORY // 2026
                    </h2>
                    <p class="text-xs text-zinc-400 mt-0.5">
                        Showing {{ $products->total() ?? $products->count() }} authenticated units ready for express dispatch
                    </p>
                </div>

                <!-- Categories -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ !$currentCategory ? 'bg-[#a3e635] text-black font-black' : 'bg-black border border-zinc-800 text-zinc-400 hover:text-white' }}">
                        ALL [{{ $products->total() ?? $products->count() }}]
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-3 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ $currentCategory === $c->slug ? 'bg-[#a3e635] text-black font-black' : 'bg-black border border-zinc-800 text-zinc-400 hover:text-white' }}">
                            {{ strtoupper($c->name) }} [{{ $c->products_count }}]
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="py-16 text-center border border-zinc-800 bg-[#111318] p-8 space-y-3">
                    <span class="text-2xl text-zinc-500 block">[ INVENTORY_DEPLETED ]</span>
                    <p class="text-xs text-zinc-400">No units match this category filter.</p>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block mt-3 px-5 py-2 border border-[#a3e635] text-xs font-mono uppercase tracking-widest text-[#a3e635] hover:bg-[#a3e635] hover:text-black transition">
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
                        @endphp
                        
                        <div class="group border border-zinc-800 bg-[#111318] hover:border-[#a3e635] transition-all duration-300 flex flex-col justify-between relative shadow-lg">
                            
                            <!-- Image Frame -->
                            <div class="relative aspect-[3/4] bg-black overflow-hidden">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>

                                @if($discountPct > 0)
                                    <div class="absolute top-2.5 left-2.5">
                                        <span class="px-2 py-0.5 bg-[#a3e635] text-black font-mono text-[9px] font-black uppercase tracking-wider">
                                            -{{ $discountPct }}%
                                        </span>
                                    </div>
                                @endif

                                @if($product->is_featured)
                                    <div class="absolute top-2.5 right-2.5">
                                        <span class="px-2 py-0.5 bg-white text-black font-mono text-[9px] font-black uppercase tracking-wider">
                                            CORE
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Details -->
                            <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <span class="text-[9px] font-mono text-zinc-500 uppercase tracking-widest block truncate mb-1">
                                        SKU: #{{ str_pad($product->id, 5, '0', STR_PAD_LEFT) }}
                                    </span>
                                    <h3 class="text-xs sm:text-sm font-heading font-bold text-white line-clamp-2 leading-snug group-hover:text-[#a3e635] transition uppercase">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="pt-2 border-t border-zinc-800 flex items-baseline justify-between gap-1">
                                    <div class="flex items-baseline gap-1.5 flex-wrap">
                                        <span class="text-sm sm:text-base font-black font-mono text-white">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($comparePriceRupees)
                                            <span class="text-xs font-mono text-zinc-500 line-through">
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
                                                class="w-full py-2.5 px-3 border border-zinc-700 hover:border-[#a3e635] hover:bg-[#a3e635] hover:text-black bg-black text-white font-mono text-[11px] font-black uppercase tracking-wider transition-all duration-200 cursor-pointer">
                                            + DOCK TO BAG
                                        </button>
                                    @else
                                        <div class="w-full py-2 text-center text-[10px] font-mono text-zinc-500 uppercase bg-black border border-zinc-800">
                                            [ DEPLETED ]
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

        {{-- TACTICAL ESCROW PROTOCOL --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="border border-zinc-800 bg-[#111318] p-6 sm:p-10">
                <div class="pb-5 mb-6 border-b border-zinc-800 flex items-center justify-between font-mono text-xs">
                    <span class="font-bold uppercase tracking-[0.2em] text-[#a3e635] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-none bg-[#a3e635]"></span>
                        // HARDENED PROTOCOL // DISPATCH ASSURANCES
                    </span>
                    <span class="text-zinc-400">K71 ESCROW LOCKED</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 font-mono text-xs">
                    <div class="space-y-1.5 p-3.5 bg-black border border-zinc-800">
                        <span class="text-[10px] text-[#a3e635] uppercase block font-bold">01 // ORIGIN</span>
                        <h4 class="font-bold text-white uppercase">Direct Studio</h4>
                        <p class="text-[11px] text-zinc-400">Packaging origin verified at {{ $vendor->city ?: 'central studio' }}.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-black border border-zinc-800">
                        <span class="text-[10px] text-[#a3e635] uppercase block font-bold">02 // ESCROW</span>
                        <h4 class="font-bold text-white uppercase">Funds Protected</h4>
                        <p class="text-[11px] text-zinc-400">Funds released only upon confirmed doorstep scan.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-black border border-zinc-800">
                        <span class="text-[10px] text-[#a3e635] uppercase block font-bold">03 // COURIER</span>
                        <h4 class="font-bold text-white uppercase">Tracked Transit</h4>
                        <p class="text-[11px] text-zinc-400">Real-time GPS trackable logistics across all Indian pincodes.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-black border border-zinc-800">
                        <span class="text-[10px] text-[#a3e635] uppercase block font-bold">04 // WARRANTY</span>
                        <h4 class="font-bold text-white uppercase">Exchange Window</h4>
                        <p class="text-[11px] text-zinc-400">{{ $vendor->settings?->return_window_days ?: 7 }}-day rapid sizing & unit exchange guarantee.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Dark Tactical Footer --}}
    @include('storefront.sections.footer')

    {{-- Slide-From-Left Menu Drawer --}}
    @include('storefront.sections.theme_drawer')

</div>

