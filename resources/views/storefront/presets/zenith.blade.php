{{-- PRESET 06: ZENITH (Architectural High-Fashion Gallery · Swiss Editorial · Museum Slab Hierarchy) --}}
@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $flagshipImg = $flagship?->images->first()?->path 
        ? \App\Services\ImageService::url($flagship->images->first()->path, 1200) 
        : ($storeBanner ?: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1600&q=80');
    $currentCategory = request('category');
    $currentSearch = request('search');
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
@endphp

<div class="storefront-preset-zenith min-h-screen flex flex-col font-body selection:bg-[#1b3b2b] selection:text-white bg-[#f5f5f0] text-[#111813]"
     style="--sf-accent: #1b3b2b; --sf-primary: #111813;"
     x-data="{ drawerOpen: false }">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Fine Gallery Coordinate Marquee Bar --}}
    <div class="w-full bg-[#111813] text-stone-300 text-[10px] font-mono tracking-[0.25em] uppercase py-1.5 px-4 overflow-hidden border-b border-stone-800">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <span class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>STUDIO DISPATCH // {{ strtoupper($vendor->city ?: 'CENTRAL WORKSHOP') }}</span>
            </span>
            <span class="hidden md:inline-block">EXHIBITION 2026 · VERIFIED MERCHANT ATELIER</span>
            <span class="text-white font-bold">K71 ESCROW PROTECTED</span>
        </div>
    </div>

    {{-- Architectural Slab Header --}}
    <header class="sticky top-0 z-40 bg-[#f5f5f0]/95 backdrop-blur-xl border-b border-stone-300/80 text-[#111813] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
            
            <!-- Left: Hamburger Drawer Trigger + Gallery Identity & Monogram -->
            <div class="flex items-center gap-3 shrink-0">
                <button type="button" 
                        @click="drawerOpen = true"
                        class="p-2 -ml-2 text-[#111813] hover:text-[#1b3b2b] transition cursor-pointer flex items-center justify-center shrink-0"
                        aria-label="Open boutique menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-none border border-stone-400 bg-white p-1 flex items-center justify-center shadow-xs">
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="max-h-full max-w-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-none border border-[#111813] bg-[#111813] text-white flex items-center justify-center font-heading text-base font-bold">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex flex-col">
                        <span class="font-heading italic font-bold text-base sm:text-xl tracking-tight text-[#111813] group-hover:text-[#1b3b2b] transition">
                            {{ $storeName }}
                        </span>
                        <span class="text-[9px] font-mono uppercase tracking-[0.2em] text-stone-500">
                            GALLERY // ARCHIVE
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Architectural Department Pills -->
            <nav class="hidden md:flex items-center gap-1 border border-stone-300 bg-white p-1 rounded-full text-xs font-mono">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="px-3.5 py-1.5 rounded-full transition {{ !$currentCategory ? 'bg-[#111813] text-white font-bold' : 'text-stone-600 hover:text-black hover:bg-stone-100' }}">
                    EXHIBIT ({{ $products->total() ?? $products->count() }})
                </a>
                @foreach($vendorCategories->take(3) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                       class="px-3.5 py-1.5 rounded-full transition {{ $currentCategory === $c->slug ? 'bg-[#111813] text-white font-bold' : 'text-stone-600 hover:text-black hover:bg-stone-100' }}">
                        {{ strtoupper($c->name) }}
                    </a>
                @endforeach
                <a href="#trust" class="px-3 py-1.5 rounded-full text-stone-600 hover:text-black transition">
                    PROTOCOL
                </a>
            </nav>

            <!-- Right: Search & Minimalist Bag Counter Button (With Bag Icon, No Plain Text) -->
            <div class="flex items-center gap-2 sm:gap-3">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="p-2 sm:px-3 sm:py-2 rounded-none border border-[#111813] bg-white text-black hover:bg-stone-100 transition-all duration-200 flex items-center gap-2 cursor-pointer shadow-xs"
                        aria-label="Shopping Cart">
                    <svg class="w-5 h-5 shrink-0 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span id="cart-counter-badge" class="px-1.5 py-0.2 bg-black text-white text-[10px] font-mono font-black">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </div>
    </header>

    <main class="flex-1 space-y-10 sm:space-y-16">
        
        {{-- MONUMENTAL EDITORIAL TRIPTYCH HERO STAGE --}}
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8">
            <div class="border border-stone-300 bg-white grid grid-cols-1 lg:grid-cols-12 shadow-sm">
                
                <!-- Left Editorial Column: Monumental Headline (7 Cols) -->
                <div class="lg:col-span-7 p-6 sm:p-12 lg:p-16 flex flex-col justify-between space-y-8 border-b lg:border-b-0 lg:border-r border-stone-300">
                    <div class="space-y-4">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-stone-100 border border-stone-300 text-[10px] font-mono uppercase tracking-[0.2em] text-[#1b3b2b] font-bold">
                            <span>ISSUE NO. 24</span>
                            <span>•</span>
                            <span>{{ strtoupper($vendor->city ?: 'STUDIO WORKSHOP') }}</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl lg:text-6xl font-heading font-black tracking-tight text-[#111813] leading-[1.05]">
                            <span class="italic font-normal block text-stone-500 text-xl sm:text-2xl mb-1">Handcrafted Distinction</span>
                            {{ $storeName }}
                        </h1>

                        <p class="text-xs sm:text-base text-stone-600 max-w-lg leading-relaxed font-body">
                            {{ $vendor->description ?: 'Disciplined form, honest provenance, and uncompromising studio craftsmanship. Every piece leaves directly from our workshop with trackable pan-India delivery.' }}
                        </p>
                    </div>

                    <div class="pt-4 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <a href="#catalog" class="px-8 py-3.5 bg-[#111813] hover:bg-[#1b3b2b] text-white font-mono text-xs font-bold tracking-widest uppercase transition-all duration-200 text-center shadow-xs">
                            [ EXPLORE CATALOG ({{ $products->total() ?? $products->count() }} PIECES) ↓ ]
                        </a>
                        <span class="text-[11px] font-mono text-stone-400 text-center sm:text-left">
                            Verified K71 Escrow Protection
                        </span>
                    </div>
                </div>

                <!-- Right Visual Column: Fine Gallery Portrait (5 Cols) -->
                <div class="lg:col-span-5 relative min-h-[300px] sm:min-h-[420px] bg-stone-100 overflow-hidden group">
                    <img src="{{ $flagshipImg }}" 
                         alt="{{ $flagship->title ?? $storeName }}" 
                         class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700">
                    
                    <div class="absolute bottom-4 left-4 right-4 bg-white/95 backdrop-blur-md p-3 border border-stone-300 flex items-center justify-between">
                        <div class="min-w-0 pr-2">
                            <span class="text-[9px] font-mono uppercase text-stone-400 block tracking-wider">Curated Focus</span>
                            <span class="text-xs font-heading font-bold text-[#111813] truncate block">{{ $flagship->title ?? 'Featured Piece' }}</span>
                        </div>
                        @if($flagship)
                            <a href="#catalog" class="text-xs font-mono font-bold text-[#1b3b2b] hover:underline shrink-0">
                                VIEW &rarr;
                            </a>
                        @endif
                    </div>
                </div>

            </div>
        </section>

        {{-- SCULPTURAL GALLERY PRODUCT ARCHIVE --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
            
            <!-- Category Navigation Bar -->
            <div class="border border-stone-300 bg-white p-4 sm:p-5 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl sm:text-2xl font-heading font-black tracking-tight text-[#111813]">
                        Exhibition Catalog
                    </h2>
                    <p class="text-xs font-mono text-stone-500 mt-0.5">
                        Showing {{ $products->total() ?? $products->count() }} authentic pieces direct from workshop
                    </p>
                </div>

                <!-- Categories -->
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-3.5 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ !$currentCategory ? 'bg-[#111813] text-white font-bold' : 'bg-stone-100 border border-stone-300 text-stone-600 hover:text-black' }}">
                        ALL ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-3.5 py-1.5 text-xs font-mono uppercase tracking-wider transition {{ $currentCategory === $c->slug ? 'bg-[#111813] text-white font-bold' : 'bg-stone-100 border border-stone-300 text-stone-600 hover:text-black' }}">
                            {{ strtoupper($c->name) }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Product Grid -->
            @if($products->isEmpty())
                <div class="py-16 text-center border border-stone-300 bg-white p-8 space-y-3">
                    <span class="text-2xl font-heading italic text-stone-400 block">[ Gallery Room Empty ]</span>
                    <p class="text-xs text-stone-500">No items match this category filter.</p>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block mt-3 px-5 py-2 border border-[#111813] text-xs font-mono uppercase tracking-widest text-[#111813] hover:bg-[#111813] hover:text-white transition">
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
                        
                        <div class="group border border-stone-300 bg-white hover:border-[#111813] transition-all duration-300 flex flex-col justify-between relative shadow-xs">
                            
                            <!-- Image Frame -->
                            <div class="relative aspect-[3/4] bg-stone-100 overflow-hidden">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
                                </a>

                                @if($discountPct > 0)
                                    <div class="absolute top-2.5 left-2.5">
                                        <span class="px-2 py-0.5 bg-[#1b3b2b] text-white font-mono text-[9px] font-bold uppercase tracking-wider">
                                            -{{ $discountPct }}%
                                        </span>
                                    </div>
                                @endif

                                @if($product->is_featured)
                                    <div class="absolute top-2.5 right-2.5">
                                        <span class="px-2 py-0.5 bg-[#111813] text-white font-mono text-[9px] font-bold uppercase tracking-wider">
                                            EDITION
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Details -->
                            <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                                <div>
                                    <span class="text-[9px] font-mono text-stone-400 uppercase tracking-widest block truncate mb-1">
                                        {{ $product->category?->name ?? 'STUDIO ITEM' }}
                                    </span>
                                    <h3 class="text-xs sm:text-sm font-heading font-bold text-[#111813] line-clamp-2 leading-snug group-hover:text-[#1b3b2b] transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="pt-2 border-t border-stone-100 flex items-baseline justify-between gap-1">
                                    <div class="flex items-baseline gap-1.5 flex-wrap">
                                        <span class="text-sm sm:text-base font-bold font-mono text-[#111813]">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($comparePriceRupees)
                                            <span class="text-xs font-mono text-stone-400 line-through">
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
                                                class="w-full py-2 px-3 border border-stone-300 hover:border-[#111813] hover:bg-[#111813] hover:text-white bg-stone-50 font-mono text-[11px] font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer">
                                            + Add to Bag
                                        </button>
                                    @else
                                        <div class="w-full py-2 text-center text-[10px] font-mono text-stone-400 uppercase bg-stone-100">
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

        {{-- ARCHITECTURAL GALLERY PROTOCOL --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="border border-stone-300 bg-white p-6 sm:p-10 shadow-xs">
                <div class="pb-5 mb-6 border-b border-stone-200 flex items-center justify-between font-mono text-xs">
                    <span class="font-bold uppercase tracking-[0.2em] text-[#1b3b2b]">
                        // GALLERY PROTOCOL SPECIFICATIONS
                    </span>
                    <span class="text-stone-400">100% VERIFIED MAKER</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 font-mono text-xs">
                    <div class="space-y-1.5 p-3.5 bg-stone-50 border border-stone-200">
                        <span class="text-[10px] text-stone-400 uppercase block">01 // ORIGIN</span>
                        <h4 class="font-bold text-[#111813] uppercase">Studio Direct</h4>
                        <p class="text-[11px] text-stone-600">Dispatched straight from {{ $vendor->city ?: 'the maker workshop' }}.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-stone-50 border border-stone-200">
                        <span class="text-[10px] text-stone-400 uppercase block">02 // ESCROW</span>
                        <h4 class="font-bold text-[#111813] uppercase">K71 Protection</h4>
                        <p class="text-[11px] text-stone-600">Payment released only after doorstep receipt confirmation.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-stone-50 border border-stone-200">
                        <span class="text-[10px] text-stone-400 uppercase block">03 // TRANSIT</span>
                        <h4 class="font-bold text-[#111813] uppercase">Insured Courier</h4>
                        <p class="text-[11px] text-stone-600">Real-time trackable door-to-door transit across India.</p>
                    </div>
                    <div class="space-y-1.5 p-3.5 bg-stone-50 border border-stone-200">
                        <span class="text-[10px] text-stone-400 uppercase block">04 // CARE</span>
                        <h4 class="font-bold text-[#111813] uppercase">Direct Support</h4>
                        <p class="text-[11px] text-stone-600">{{ $vendor->settings?->return_window_days ?: 7 }}-day personal size & quality exchange.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Client Services & WhatsApp Concierge --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Minimal Architectural Footer --}}
    @include('storefront.sections.footer')

    {{-- Slide-From-Left Menu Drawer --}}
    @include('storefront.sections.theme_drawer')

</div>

