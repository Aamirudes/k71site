{{-- PRESET 04: CHROMA (Avant-Garde Drop Culture · Bold Poster Campaign · High-Velocity Drop Matrix) --}}
@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $flagshipImg = $flagship?->images->first()?->path ? \App\Services\ImageService::url($flagship->images->first()->path, 1200) : ($storeBanner ?: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1600&q=80');
    $currentCategory = request('category');
    $currentSearch = request('search');
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
@endphp

<div class="storefront-preset-chroma min-h-screen flex flex-col font-body selection:bg-blue-600 selection:text-white bg-[#f8fafc] text-[#09090b]"
     style="--sf-accent: #2563eb; --sf-primary: #09090b;">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    @php
        $rawChromaEnabled = $config['header']['chroma_strip_enabled'] ?? null;
        $chromaStripEnabled = ($rawChromaEnabled === false || $rawChromaEnabled === 0 || $rawChromaEnabled === '0' || $rawChromaEnabled === 'false') 
            ? false 
            : (!isset($config['header']['chroma_strip_enabled']) ? true : (bool)$config['header']['chroma_strip_enabled']);
        $chromaStripText = $config['header']['chroma_strip_text'] ?? '⚡ LIMITED EDITION RUN LIVE • DISPATCH WITHIN 24 HOURS • 100% AUTHENTIC CREATIONS ⚡';
        $chromaStripBg = $config['header']['chroma_strip_bg'] ?? '#2563eb';
    @endphp

    @if($chromaStripEnabled && !empty($chromaStripText))
        {{-- High-Velocity Royal Blue Marquee Tape (Vendor Customizable & Removable) --}}
        <div class="w-full text-white font-mono text-[11px] font-bold py-2 px-4 overflow-hidden border-b-2 border-black tracking-wider uppercase select-none shadow-sm"
             style="background-color: {{ $chromaStripBg }};">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <span>{{ $chromaStripText }}</span>
                <span class="hidden sm:inline-block">DIRECT FROM {{ strtoupper($vendor->city ?: 'STUDIO') }} • VERIFIED K71 BOUTIQUE</span>
                <span>DOORSTEP PAN-INDIA DELIVERY</span>
            </div>
        </div>
    @endif

    {{-- Avant-Garde Graphic Header --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl border-b-2 border-black text-black">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 sm:h-20 flex items-center justify-between gap-4">
            
            <!-- Left: Chunky Graphic Sticker Brand -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="p-1 rounded-xl border-2 border-black bg-yellow-300 shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-8 sm:h-10 w-auto object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-xl border-2 border-black bg-blue-600 text-white font-black text-base flex items-center justify-center shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-black text-xl sm:text-2xl tracking-tighter uppercase block leading-none">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono font-bold uppercase text-blue-600 tracking-wider">
                            DROP // 2026 EDITION
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Graphic Category Pills (Desktop) -->
            <nav class="hidden md:flex items-center gap-3">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="px-4 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border-2 border-black transition {{ !$currentCategory ? 'bg-black text-white shadow-[2px_2px_0px_0px_rgba(37,99,235,1)]' : 'bg-white hover:bg-zinc-100 text-black' }}">
                    ALL RELEASES
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider border-2 border-black transition {{ $currentCategory === $c->slug ? 'bg-black text-white shadow-[2px_2px_0px_0px_rgba(37,99,235,1)]' : 'bg-white hover:bg-zinc-100 text-black' }}">
                        {{ $c->name }}
                    </a>
                @endforeach
            </nav>

            <!-- Right: Vibrant Electric Bag Button -->
            <div class="flex items-center gap-3">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="px-4 sm:px-5 py-2.5 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-mono font-black text-xs uppercase tracking-wider border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-0.5 hover:translate-y-0.5 hover:shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center gap-2 cursor-pointer">
                    <span>BAG</span>
                    <span id="cart-counter-badge" class="px-2 py-0.5 rounded-full bg-white text-blue-600 text-[10px] font-black">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </div>
    </header>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- HIGH-VELOCITY POSTER CAMPAIGN STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10">
            <div class="p-6 sm:p-12 rounded-3xl border-3 border-black bg-white shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] relative overflow-hidden">
                
                <!-- Background Graphic Watermark -->
                <div class="absolute -right-8 -bottom-12 select-none pointer-events-none opacity-5 font-black text-9xl uppercase tracking-tighter">
                    DROP 2026
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
                    
                    <!-- Left: Graphic Poster Headline (7 Cols) -->
                    <div class="lg:col-span-7 space-y-6">
                        
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-3 py-1 rounded-md text-[11px] font-black uppercase tracking-wider bg-yellow-300 text-black border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                ⚡ LIMITED RUN EDITION
                            </span>
                            <span class="px-3 py-1 rounded-md text-[11px] font-black uppercase tracking-wider bg-black text-white border-2 border-black">
                                DIRECT STUDIO DISPATCH
                            </span>
                        </div>

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black uppercase tracking-tight text-black leading-[0.92]">
                            {{ $storeName }}
                        </h1>

                        <p class="text-sm sm:text-base font-medium text-slate-700 max-w-lg leading-relaxed">
                            {{ $vendor->description ?: 'High-velocity releases. Uncompromising craftsmanship directly packaged from our verified workshop with insured doorstep express.' }}
                        </p>

                        <!-- Action Controls -->
                        <div class="flex flex-wrap items-center gap-3 pt-2">
                            <a href="#catalog" 
                               class="px-8 py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-mono font-black text-xs sm:text-sm uppercase tracking-wider border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all hover:scale-102 cursor-pointer">
                                COP THE DROP →
                            </a>
                            <a href="#trust" 
                               class="px-6 py-4 rounded-2xl bg-white hover:bg-zinc-100 text-black font-mono font-bold text-xs sm:text-sm uppercase tracking-wider border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] transition-all">
                                VERIFIED GUARANTEES
                            </a>
                        </div>

                    </div>

                    <!-- Right: Large Poster Visual (5 Cols) -->
                    <div class="lg:col-span-5 relative">
                        <div class="aspect-[4/5] rounded-3xl overflow-hidden border-3 border-black shadow-[6px_6px_0px_0px_rgba(37,99,235,1)] relative bg-yellow-100 group">
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $storeName }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                            
                            <!-- Sticker Badges -->
                            <div class="absolute top-4 left-4 z-10">
                                <span class="px-3 py-1 rounded-lg bg-black text-white font-mono font-black text-xs uppercase shadow-md">
                                    VERIFIED DISPATCH
                                </span>
                            </div>

                            @if($flagship)
                                <div class="absolute bottom-4 inset-x-4 p-4 rounded-2xl bg-white/95 backdrop-blur-md border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between">
                                    <div class="min-w-0 pr-2">
                                        <span class="text-[10px] font-bold text-blue-600 uppercase block">FLAGSHIP UNIT</span>
                                        <span class="text-xs font-black truncate block">{{ $flagship->title }}</span>
                                    </div>
                                    <button type="button" 
                                            onclick="window.quickAddToCart({{ $flagship->id }}, {{ $flagship->variants->first()?->id ?: 'null' }}, this)"
                                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-mono font-black text-xs uppercase tracking-wider border border-black shadow-xs shrink-0 cursor-pointer">
                                        ⚡ COP
                                    </button>
                                </div>
                            @endif

                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- HIGH-IMPACT DROP MATRIX --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            
            <div class="p-6 rounded-3xl border-2 border-black bg-white shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-black uppercase tracking-tight text-black">
                        THE DROP MATRIX // ACTIVE UNITS
                    </h2>
                    <p class="text-xs font-mono text-slate-500 mt-0.5">
                        {{ $products->total() ?? $products->count() }} authentic pieces ready for doorstep express dispatch
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-3.5 py-1.5 rounded-xl text-xs font-black uppercase border-2 border-black transition {{ !$currentCategory ? 'bg-blue-600 text-white' : 'bg-slate-100 text-black hover:bg-slate-200' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-3.5 py-1.5 rounded-xl text-xs font-black uppercase border-2 border-black transition {{ $currentCategory === $c->slug ? 'bg-blue-600 text-white' : 'bg-slate-100 text-black hover:bg-slate-200' }}">
                            {{ $c->name }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Products Matrix -->
            @if($products->isEmpty())
                <div class="py-16 text-center border-2 border-black rounded-3xl bg-white p-8 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] space-y-3">
                    <span class="text-3xl font-black block">📦</span>
                    <h3 class="text-base font-black uppercase">No active items in this category</h3>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block px-5 py-2.5 rounded-xl bg-black text-white font-mono font-bold text-xs uppercase">
                        Reset Category Filter
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 sm:gap-6">
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

                        <div class="group flex flex-col justify-between rounded-3xl border-2 border-black bg-white overflow-hidden shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] hover:translate-x-0.5 hover:-translate-y-1 hover:shadow-[6px_6px_0px_0px_rgba(37,99,235,1)] transition-all duration-300">
                            
                            <!-- Image Frame -->
                            <div class="relative aspect-square w-full bg-slate-100 overflow-hidden border-b-2 border-black">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="{{ $product->title }}">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-106 {{ $isOutOfStock ? 'opacity-40 grayscale' : '' }}">
                                </a>

                                <!-- Badges -->
                                <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10">
                                    @if($hasComparePrice && $discountPct > 0)
                                        <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-mono font-black text-[10px] uppercase border border-black shadow-xs">
                                            -{{ $discountPct }}%
                                        </span>
                                    @endif
                                    @if($isOutOfStock)
                                        <span class="px-2 py-0.5 rounded-md bg-black text-white font-mono text-[10px] uppercase">
                                            SOLD
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-2.5 right-2.5 z-10">
                                    <span class="px-2 py-0.5 rounded-md bg-yellow-300 text-black font-mono font-black text-[9px] uppercase border border-black">
                                        ✦ 100% AUTH
                                    </span>
                                </div>
                            </div>

                            <!-- Details Canvas -->
                            <div class="p-4 flex flex-col justify-between flex-1 gap-3">
                                <div>
                                    @if($product->category)
                                        <span class="text-[9px] font-mono font-bold uppercase text-blue-600 block mb-0.5">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="text-xs sm:text-sm font-black text-black uppercase line-clamp-2 leading-snug group-hover:text-blue-600 transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                    <div class="text-[10px] font-mono text-slate-800 pt-0.5 font-bold">
                                        STUDIO DISPATCH: {{ strtoupper($vendor->city ?: 'IN') }}
                                    </div>
                                </div>

                                <!-- Price & Action -->
                                <div class="pt-2 border-t-2 border-black/10 space-y-2">
                                    <div class="flex items-baseline justify-between">
                                        <span class="text-sm sm:text-base font-black font-mono text-black">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($hasComparePrice)
                                            <span class="text-xs font-mono text-slate-400 line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>

                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}, this)"
                                                class="w-full py-2.5 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-mono font-black text-xs uppercase tracking-wider border-2 border-black shadow-[2px_2px_0px_0px_rgba(0,0,0,1)] transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                            <span>⚡ QUICK COP</span>
                                        </button>
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

                @if(method_exists($products, 'links') && $products->hasPages())
                    <div class="mt-12 flex justify-center">
                        {{ $products->fragment('catalog')->links() }}
                    </div>
                @endif
            @endif

        </section>

        {{-- Studio Shorts & Video Reels Showcase (Shown Just Below Product) --}}
        @include('storefront.sections.video_showcase')

        {{-- Happy Customers Photos & Videos Slider (Shown Just Below Shorts) --}}
        @include('storefront.sections.happy_customers')

        {{-- BOLD GRAPHIC TRUST BOXES --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-8 sm:p-10 rounded-3xl border-3 border-black bg-yellow-300 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-xl sm:text-2xl font-black uppercase tracking-tight text-black">
                        ⚡ GUARANTEES &amp; POLICIES
                    </h3>
                    <span class="px-2.5 py-1 rounded bg-black text-white font-mono text-xs font-bold uppercase">
                        K71 VERIFIED
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-2xl bg-white border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] space-y-1">
                        <span class="font-black text-sm text-black block">✦ DIRECT STUDIO</span>
                        <p class="text-xs text-slate-700">Shipped straight from {{ $vendor->city ?: 'the merchant workshop' }}.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] space-y-1">
                        <span class="font-black text-sm text-black block">◈ 100% AUTHENTIC</span>
                        <p class="text-xs text-slate-700">Verified seller backed by K71 marketplace protection.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] space-y-1">
                        <span class="font-black text-sm text-black block">⚡ INSURED TRANSIT</span>
                        <p class="text-xs text-slate-700">Trackable pan-India delivery straight to your doorstep.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-white border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] space-y-1">
                        <span class="font-black text-sm text-black block">↺ EASY REPLACEMENT</span>
                        <p class="text-xs text-slate-700">{{ $vendor->settings?->return_window_days ?: 7 }}-day direct merchant size exchange.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Reviews --}}
        @include('storefront.sections.reviews_section')

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Minimal Bold Footer --}}
    @include('storefront.sections.footer')

</div>
