{{-- PRESET 10: VOGUE (Paris Runway Editorial · Glossy Lookbook Magazine · Bodoni Moda Typography) --}}
@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $flagshipImg = $flagship?->images->first()?->path 
        ? \App\Services\ImageService::url($flagship->images->first()->path, 1200) 
        : ($storeBanner ?: 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1600&q=80');
    $currentCategory = request('category');
    $currentSearch = request('search');
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
@endphp

<div class="storefront-preset-vogue min-h-screen flex flex-col font-body selection:bg-[#e11d48] selection:text-white bg-white text-[#09090b]"
     style="--sf-accent: #e11d48; --sf-primary: #09090b;">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Paris Runway Masthead Bar --}}
    <div class="w-full bg-[#09090b] text-white text-[10px] font-mono tracking-[0.25em] uppercase py-2 px-4 overflow-hidden select-none border-b border-black">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#e11d48]"></span>
                <span class="font-bold">PARIS EDITORIAL // {{ strtoupper($vendor->city ?: 'CENTRAL ATELIER') }}</span>
            </span>
            <span class="hidden md:inline-block">HAUTE COUTURE RUNWAY · INSURED PAN-INDIA DOORSTEP COURIER</span>
            <span class="text-[#e11d48] font-bold">K71 ESCROW PROTECTED</span>
        </div>
    </div>

    {{-- Glossy Magazine Cover Header --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl border-b-2 border-black text-[#09090b]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 sm:h-22 flex items-center justify-between gap-4">
            
            <!-- Left: Magazine Masthead Logo -->
            <div class="flex items-center gap-3.5 shrink-0">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="h-10 sm:h-12 w-auto p-1 border-2 border-black bg-white">
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-full w-auto object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 sm:w-12 sm:h-12 border-2 border-black bg-black text-white flex items-center justify-center font-heading text-lg font-black tracking-tighter">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-heading font-black text-lg sm:text-2xl tracking-tighter text-[#09090b] group-hover:text-[#e11d48] transition uppercase leading-none">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono tracking-[0.25em] text-[#e11d48] uppercase mt-0.5 font-bold">
                            VOL. 24 // RUNWAY ISSUE
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Editorial Lookbook Tabs -->
            <nav class="hidden md:flex items-center gap-1 border-2 border-black bg-white p-1 text-xs font-mono">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="px-4 py-1.5 transition {{ !$currentCategory ? 'bg-black text-white font-bold' : 'text-[#09090b] hover:bg-black hover:text-white' }}">
                    LOOKBOOK ({{ $products->total() ?? $products->count() }})
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                       class="px-4 py-1.5 transition {{ $currentCategory === $c->slug ? 'bg-black text-white font-bold' : 'text-[#09090b] hover:bg-black hover:text-white' }}">
                        {{ strtoupper($c->name) }}
                    </a>
                @endforeach
                <a href="#trust" class="px-4 py-1.5 text-[#09090b] hover:bg-black hover:text-white transition">
                    PROTOCOL
                </a>
            </nav>

            <!-- Right: Bold Crimson Bag Button -->
            <div class="flex items-center gap-3">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="px-4 py-2 border-2 border-black bg-[#e11d48] hover:bg-black text-white font-mono text-xs font-black tracking-widest uppercase transition-all duration-200 flex items-center gap-2 cursor-pointer shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]">
                    <span>BAG</span>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 bg-white text-black text-[10px] font-black">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </div>
    </header>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- MAGAZINE COVER EDITORIAL HERO STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8">
            <div class="border-3 border-black bg-white grid grid-cols-1 lg:grid-cols-12 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                
                <!-- Left Column: Oversized Magazine Headline (7 Cols) -->
                <div class="lg:col-span-7 p-6 sm:p-12 lg:p-16 flex flex-col justify-between space-y-8 border-b lg:border-b-0 lg:border-r-3 border-black">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b-2 border-black text-[11px] font-mono tracking-widest uppercase">
                            <span class="bg-black text-white px-2 py-0.5 font-bold">HAUTE COUTURE</span>
                            <span class="text-[#e11d48] font-bold">PARIS // 2026</span>
                        </div>

                        <h1 class="text-4xl sm:text-6xl lg:text-7xl font-heading font-black tracking-tighter text-[#09090b] uppercase leading-[0.94]">
                            <span class="italic font-normal block text-2xl sm:text-3xl text-zinc-600 mb-1">The New Silhouette</span>
                            {{ $storeName }}
                        </h1>

                        <p class="text-xs sm:text-base text-zinc-700 max-w-lg leading-relaxed font-body">
                            {{ $vendor->description ?: 'Uncompromising editorial elegance defined by sculptural tailoring, pristine materiality, and runway-standard presentation. Packaged straight from the atelier with insured doorstep dispatch.' }}
                        </p>
                    </div>

                    <div class="space-y-3">
                        <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                            <a href="#catalog" class="px-8 py-4 bg-black hover:bg-[#e11d48] text-white font-mono text-xs font-black uppercase tracking-widest transition-all duration-200 text-center shadow-[4px_4px_0px_0px_rgba(225,29,72,1)] cursor-pointer">
                                [ SHOP THE LOOKBOOK ({{ $products->total() ?? $products->count() }} PIECES) ↓ ]
                            </a>
                            <span class="text-[11px] font-mono text-zinc-500 text-center sm:text-left">
                                Verified Maker Seal · K71 Escrow
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Full-Bleed Runway Portrait (5 Cols) -->
                <div class="lg:col-span-5 relative min-h-[320px] sm:min-h-[460px] bg-zinc-100 overflow-hidden group">
                    <img src="{{ $flagshipImg }}" 
                         alt="{{ $flagship->title ?? $storeName }}" 
                         class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700">
                    
                    <div class="absolute bottom-4 left-4 right-4 bg-white/95 backdrop-blur-md p-3.5 border-2 border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] flex items-center justify-between">
                        <div class="min-w-0 pr-2">
                            <span class="text-[9px] font-mono uppercase text-[#e11d48] block font-bold tracking-wider">Runway Look No. 01</span>
                            <span class="text-xs font-heading font-black text-black truncate block uppercase">{{ $flagship->title ?? 'Cover Feature' }}</span>
                        </div>
                        @if($flagship)
                            <a href="#catalog" class="text-xs font-mono font-black text-black hover:text-[#e11d48] shrink-0">
                                VIEW &rarr;
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </section>

        {{-- RUNWAY LOOKBOOK PRODUCT ARCHIVE --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            
            <!-- Category Navigation -->
            <div class="border-2 border-black bg-white p-4 sm:p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                <div>
                    <h2 class="text-xl sm:text-2xl font-heading font-black tracking-tight uppercase text-black">
                        Runway Lookbook Archive
                    </h2>
                    <p class="text-xs font-mono text-zinc-600 mt-0.5">
                        Showing {{ $products->total() ?? $products->count() }} authentic pieces direct from the designer atelier
                    </p>
                </div>

                <!-- Categories -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-4 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ !$currentCategory ? 'bg-black text-white font-bold' : 'bg-white border-2 border-black text-black hover:bg-zinc-100' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-4 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ $currentCategory === $c->slug ? 'bg-black text-white font-bold' : 'bg-white border-2 border-black text-black hover:bg-zinc-100' }}">
                            {{ strtoupper($c->name) }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="py-16 text-center border-2 border-black bg-white p-8 space-y-3">
                    <span class="text-2xl font-heading italic text-zinc-400 block">[ Lookbook Depleted ]</span>
                    <p class="text-xs text-zinc-600">No items match this category filter.</p>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block mt-3 px-6 py-2 border-2 border-black text-xs font-mono uppercase tracking-widest text-black hover:bg-black hover:text-white transition">
                        Reset Lookbook
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
                        
                        <div class="group border-2 border-black bg-white hover:shadow-[6px_6px_0px_0px_rgba(0,0,0,1)] transition-all duration-300 flex flex-col justify-between overflow-hidden">
                            
                            <!-- Image Frame -->
                            <div class="relative aspect-[3/4] bg-zinc-100 overflow-hidden border-b-2 border-black">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>

                                @if($discountPct > 0)
                                    <div class="absolute top-2.5 left-2.5">
                                        <span class="px-2.5 py-0.5 bg-[#e11d48] text-white font-mono text-[9px] font-black uppercase tracking-wider">
                                            -{{ $discountPct }}%
                                        </span>
                                    </div>
                                @endif

                                @if($product->is_featured)
                                    <div class="absolute top-2.5 right-2.5">
                                        <span class="px-2.5 py-0.5 bg-black text-white font-mono text-[9px] font-black uppercase tracking-wider">
                                            RUNWAY
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Details -->
                            <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <span class="text-[9px] font-mono text-zinc-500 uppercase tracking-widest block truncate mb-1 font-bold">
                                        {{ $product->category?->name ?? 'DESIGNER ITEM' }}
                                    </span>
                                    <h3 class="text-xs sm:text-sm font-heading font-black text-black line-clamp-2 leading-snug group-hover:text-[#e11d48] transition uppercase">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="pt-2 border-t border-zinc-200 flex items-baseline justify-between gap-1">
                                    <div class="flex items-baseline gap-1.5 flex-wrap">
                                        <span class="text-sm sm:text-base font-black font-mono text-black">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($comparePriceRupees)
                                            <span class="text-xs font-mono text-zinc-400 line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Add to Wardrobe Action -->
                                <div class="pt-1">
                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $product->variants->first()?->id ?? 'null' }}, this)"
                                                class="w-full py-2.5 px-3 border-2 border-black hover:bg-black hover:text-white bg-white text-black font-mono text-[11px] font-black uppercase tracking-wider transition-all duration-200 cursor-pointer shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                            + ADD TO WARDROBE
                                        </button>
                                    @else
                                        <div class="w-full py-2 rounded-none text-center text-[10px] font-mono text-zinc-500 uppercase bg-zinc-100 border border-zinc-300">
                                            ARCHIVED
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

        {{-- EDITORIAL RUNWAY PROTOCOL --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="border-3 border-black bg-white p-6 sm:p-10 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                <div class="pb-5 mb-6 border-b-2 border-black flex items-center justify-between font-mono text-xs">
                    <span class="font-black uppercase tracking-[0.2em] text-[#e11d48] flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#e11d48]"></span>
                        EDITORIAL RUNWAY GUARANTEES
                    </span>
                    <span class="bg-black text-white px-2 py-0.5 font-bold">AUTHENTIC DROP</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 font-mono text-xs">
                    <div class="space-y-1.5 p-3.5 border-2 border-black bg-zinc-50 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <span class="text-[10px] text-[#e11d48] uppercase block font-bold">01 // ATELIER</span>
                        <h4 class="font-black text-black uppercase">Direct Dispatch</h4>
                        <p class="text-[11px] text-zinc-700">Packaging origin verified at {{ $vendor->city ?: 'designer atelier' }}.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 border-2 border-black bg-zinc-50 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <span class="text-[10px] text-[#e11d48] uppercase block font-bold">02 // ESCROW</span>
                        <h4 class="font-black text-black uppercase">Payment Shield</h4>
                        <p class="text-[11px] text-zinc-700">Buyer payment released strictly after delivery verification.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 border-2 border-black bg-zinc-50 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <span class="text-[10px] text-[#e11d48] uppercase block font-bold">03 // COURIER</span>
                        <h4 class="font-black text-black uppercase">Express Air/Road</h4>
                        <p class="text-[11px] text-zinc-700">Door-to-door trackable logistics throughout all of India.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 border-2 border-black bg-zinc-50 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                        <span class="text-[10px] text-[#e11d48] uppercase block font-bold">04 // EXCHANGE</span>
                        <h4 class="font-black text-black uppercase">Couture Exchange</h4>
                        <p class="text-[11px] text-zinc-700">{{ $vendor->settings?->return_window_days ?: 7 }}-day swift merchant replacement & size help.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Minimal Bold Editorial Footer --}}
    @include('storefront.sections.footer')

</div>
