{{-- PRESET 03: AUREL (Scandinavian Minimalist Atelier · Warm Linen & Bone · Curated Framed Gallery Showcase) --}}
@php
    $flagship = $featuredProducts->first() ?? $products->first();
    $flagshipImg = $flagship?->images->first()?->path ? \App\Services\ImageService::url($flagship->images->first()->path, 1000) : ($storeBanner ?: 'https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1600&q=80');
    $currentCategory = request('category');
    $currentSearch = request('search');
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
@endphp

<div class="storefront-preset-aurel min-h-screen flex flex-col font-body selection:bg-amber-200 selection:text-amber-950 bg-[#faf8f5] text-[#1c1917]"
     style="--sf-accent: #c28422; --sf-primary: #1c1917;">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    {{-- Unhurried Serene Atelier Header --}}
    <header class="sticky top-0 z-40 bg-[#faf8f5]/90 backdrop-blur-xl border-b border-stone-200/70 text-stone-900 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 sm:h-22 flex items-center justify-between gap-6">
            
            <!-- Left: Refined Serif Logotype -->
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="w-10 h-10 rounded-full overflow-hidden border border-stone-200/80 bg-white p-1">
                            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-9 h-9 rounded-full bg-stone-900 text-white flex items-center justify-center font-serif text-sm font-bold shadow-xs">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <span class="font-serif italic font-bold text-lg sm:text-2xl text-stone-900 tracking-tight block">
                            {{ $storeName }}
                        </span>
                        <span class="text-[10px] uppercase font-bold tracking-[0.2em] text-stone-400 block -mt-0.5">
                            {{ $vendor->city ? 'Studio · ' . $vendor->city : 'Artisan Atelier' }}
                        </span>
                    </div>
                </a>
            </div>

            <!-- Center: Inline Atelier Department Links (Desktop) -->
            <nav class="hidden md:flex items-center gap-8 text-xs font-medium tracking-wide text-stone-600">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="hover:text-stone-950 transition {{ !$currentCategory ? 'text-stone-950 font-bold border-b border-stone-900 pb-0.5' : '' }}">
                    All Editions
                </a>
                @foreach($vendorCategories->take(4) as $c)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" class="hover:text-stone-950 transition {{ $currentCategory === $c->slug ? 'text-stone-950 font-bold border-b border-stone-900 pb-0.5' : '' }}">
                        {{ $c->name }}
                    </a>
                @endforeach
                <a href="#trust" class="hover:text-stone-950 transition">Atelier Standards</a>
            </nav>

            <!-- Right: Elegant Cart Counter & Concierge Desk -->
            <div class="flex items-center gap-4">
                <button type="button" 
                        onclick="window.openK71Cart ? window.openK71Cart() : window.location.href='{{ route('cart.index') }}'"
                        class="px-4 py-2 rounded-full border border-stone-300 hover:border-stone-900 bg-white text-stone-900 text-xs font-semibold tracking-wider transition-all duration-200 flex items-center gap-2 cursor-pointer shadow-2xs hover:shadow-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                    <span>Bag</span>
                    <span id="cart-counter-badge" class="font-mono text-stone-500 font-bold">({{ $cartCount }})</span>
                </button>
            </div>

        </div>
    </header>

    <main class="flex-1 space-y-12 sm:space-y-16">
        
        {{-- CURATED ATELIER FRAMED GALLERY SHOWCASE --}}
        <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10">
            <div class="p-6 sm:p-12 rounded-3xl bg-white border border-stone-200/80 shadow-[0_4px_30px_rgba(0,0,0,0.02)]">
                
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12 items-center">
                    
                    <!-- Left: Poetic Atelier Narrative (6 Cols) -->
                    <div class="md:col-span-6 space-y-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] font-semibold uppercase tracking-widest text-amber-900 bg-amber-50 border border-amber-200/60">
                            <span>✦</span>
                            <span>Thoughtfully Crafted in {{ $vendor->city ?: 'Studio Atelier' }}</span>
                        </div>

                        <h1 class="text-3xl sm:text-5xl font-serif italic font-normal text-stone-900 leading-[1.12]">
                            The Art of Living with Unhurried Distinction
                        </h1>

                        <p class="text-xs sm:text-sm text-stone-600 leading-relaxed max-w-md">
                            {{ $vendor->description ?: 'Every weave, texture, and silhouette is carefully examined by our makers before dispatch. We create durable pieces designed to endure.' }}
                        </p>

                        <!-- Studio Attributes -->
                        <div class="pt-2 grid grid-cols-2 gap-4 border-t border-stone-100 text-xs text-stone-700">
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-stone-400 block mb-0.5">Origin</span>
                                <span class="font-serif italic font-bold">{{ $vendor->city ?: 'Direct Workshop' }}, India</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase font-bold tracking-widest text-stone-400 block mb-0.5">Availability</span>
                                <span class="font-serif italic font-bold">Limited Small Batches</span>
                            </div>
                        </div>

                        <div class="pt-2 flex items-center gap-4">
                            <a href="#catalog" class="px-7 py-3 rounded-full text-xs font-bold uppercase tracking-wider text-white bg-stone-900 hover:bg-stone-800 transition shadow-sm">
                                Explore Catalog ↓
                            </a>
                            <a href="#trust" class="text-xs font-bold text-stone-500 hover:text-stone-900 underline underline-offset-4 transition">
                                Atelier Promises
                            </a>
                        </div>
                    </div>

                    <!-- Right: Curated Portrait Photograph (6 Cols) -->
                    <div class="md:col-span-6 relative">
                        <div class="aspect-[4/5] rounded-2xl overflow-hidden border border-stone-200/80 shadow-lg relative bg-stone-100 group">
                            <img src="{{ $flagshipImg }}" 
                                 alt="{{ $storeName }}" 
                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-103">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent pointer-events-none"></div>

                            <!-- Floating Atelier Note -->
                            <div class="absolute bottom-4 inset-x-4 p-4 rounded-xl bg-white/90 backdrop-blur-md border border-white/60 shadow-md flex items-center justify-between text-xs text-stone-800">
                                <div>
                                    <span class="text-[10px] font-bold uppercase tracking-widest text-stone-400 block">Flagship Creation</span>
                                    <span class="font-serif italic font-bold truncate block max-w-[200px]">{{ $flagship->title ?? $storeName }}</span>
                                </div>
                                @if($flagship)
                                    <button type="button" 
                                            onclick="window.quickAddToCart({{ $flagship->id }}, {{ $flagship->variants->first()?->id ?: 'null' }}, this)"
                                            class="px-3.5 py-1.5 rounded-full text-xs font-bold bg-stone-900 hover:bg-stone-800 text-white transition cursor-pointer shrink-0">
                                        + Bag
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        {{-- ART GALLERY PRODUCT GRID (GENEROUS NEGATIVE SPACE) --}}
        <section id="catalog" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            
            <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-4 pb-6 border-b border-stone-200 mb-8">
                <div>
                    <h2 class="text-2xl sm:text-3xl font-serif italic text-stone-900">
                        The Seasonal Collection
                    </h2>
                    <p class="text-xs text-stone-500 mt-1">
                        {{ $products->total() ?? $products->count() }} curated pieces available for doorstep delivery
                    </p>
                </div>

                <!-- Understated Department Filter Tabs -->
                <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs">
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                       class="px-3 py-1 rounded-full transition {{ !$currentCategory ? 'bg-stone-900 text-white font-bold' : 'bg-stone-100 text-stone-600 hover:text-stone-950' }}">
                        All ({{ $products->total() ?? $products->count() }})
                    </a>
                    @foreach($vendorCategories as $c)
                        <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $c->slug]) }}#catalog" 
                           class="px-3 py-1 rounded-full transition {{ $currentCategory === $c->slug ? 'bg-stone-900 text-white font-bold' : 'bg-stone-100 text-stone-600 hover:text-stone-950' }}">
                            {{ $c->name }} ({{ $c->products_count }})
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Gallery Matrix -->
            @if($products->isEmpty())
                <div class="py-16 text-center max-w-sm mx-auto p-8 rounded-3xl bg-white border border-stone-200/80 shadow-xs space-y-3">
                    <span class="text-2xl font-serif italic text-stone-400">Atelier archive</span>
                    <p class="text-xs text-stone-500">No items available in this selected filter.</p>
                    <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="inline-block mt-3 px-5 py-2 rounded-full text-xs font-bold bg-stone-900 text-white transition">
                        View All Pieces
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 sm:gap-8">
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

                        <div class="group flex flex-col justify-between rounded-2xl bg-white border border-stone-200/80 overflow-hidden shadow-2xs hover:shadow-lg transition-all duration-300">
                            
                            <!-- 4:5 Aspect Frame -->
                            <div class="relative aspect-[4/5] w-full bg-stone-100 overflow-hidden">
                                <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="{{ $product->title }}">
                                    <img src="{{ $imgFirst }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-104 {{ $isOutOfStock ? 'opacity-40 grayscale' : '' }}">
                                </a>

                                <!-- Delicate Badges -->
                                <div class="absolute top-3 left-3 flex flex-col gap-1 z-10">
                                    @if($hasComparePrice && $discountPct > 0)
                                        <span class="px-2 py-0.5 rounded-full bg-stone-900 text-white text-[10px] font-bold">
                                            -{{ $discountPct }}%
                                        </span>
                                    @endif
                                    @if($isOutOfStock)
                                        <span class="px-2 py-0.5 rounded-full bg-stone-200 text-stone-700 text-[10px] font-medium">
                                            Archived
                                        </span>
                                    @endif
                                </div>

                                <div class="absolute top-3 right-3 z-10">
                                    <span class="px-2 py-0.5 rounded-full bg-white/90 backdrop-blur-sm text-stone-800 text-[10px] font-bold border border-stone-200/80">
                                        ✦ Verified
                                    </span>
                                </div>
                            </div>

                            <!-- Typography Canvas -->
                            <div class="p-4 sm:p-5 flex flex-col justify-between flex-1 gap-3">
                                <div>
                                    @if($product->category)
                                        <span class="text-[10px] font-bold uppercase tracking-[0.2em] text-stone-400 block mb-1">
                                            {{ $product->category->name }}
                                        </span>
                                    @endif
                                    <h3 class="text-xs sm:text-sm font-serif italic text-stone-900 line-clamp-2 leading-snug group-hover:text-amber-800 transition">
                                        <a href="{{ route('product.show', $product->slug) }}">
                                            {{ $product->title }}
                                        </a>
                                    </h3>
                                    <div class="text-[11px] text-stone-400 pt-1 flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Studio Crafted · In Stock</span>
                                    </div>
                                </div>

                                <!-- Pricing & Add to Bag on Single Line Down -->
                                <div class="pt-2.5 border-t border-stone-100 space-y-2">
                                    <div class="flex items-baseline justify-between">
                                        <div class="flex items-baseline gap-1.5">
                                            <span class="text-sm sm:text-base font-bold text-stone-900 font-serif">
                                                ₹{{ $priceRupees }}
                                            </span>
                                            @if($hasComparePrice)
                                                <span class="text-xs text-stone-400 line-through">
                                                    ₹{{ $comparePriceRupees }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!$isOutOfStock)
                                        <button type="button" 
                                                onclick="window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}, this)"
                                                class="w-full py-2 px-3 rounded-xl text-xs font-bold bg-stone-100 hover:bg-stone-900 text-stone-900 hover:text-white transition duration-200 cursor-pointer flex items-center justify-center gap-1.5 shadow-2xs">
                                            <span>+ Add to Bag</span>
                                        </button>
                                    @else
                                        <div class="w-full py-1.5 text-center text-[10px] font-medium text-stone-400 bg-stone-50 rounded-lg">
                                            Archived
                                        </div>
                                    @endif
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

                @if(method_exists($products, 'links') && $products->hasPages())
                    <div class="mt-12 flex justify-center font-body">
                        {{ $products->fragment('catalog')->links() }}
                    </div>
                @endif
            @endif

        </section>

        {{-- Studio Shorts & Video Reels Showcase (Shown Just Below Product) --}}
        @include('storefront.sections.video_showcase')

        {{-- SLOW CRAFT ATELIER STANDARDS --}}
        <section id="trust" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="p-8 sm:p-12 rounded-3xl bg-white border border-stone-200/80 shadow-xs">
                <div class="text-center max-w-xl mx-auto mb-8">
                    <span class="text-[10px] uppercase font-bold tracking-[0.25em] text-stone-400 block mb-1">Authentic Values</span>
                    <h3 class="text-2xl font-serif italic text-stone-900">Our Atelier Commitments</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-xs text-stone-600">
                    <div class="p-4 rounded-2xl bg-[#faf8f5] border border-stone-200/60 space-y-1.5">
                        <span class="font-serif italic font-bold text-base text-stone-900 block">✦ Direct Workshop</span>
                        <p class="leading-relaxed">Every piece leaves directly from {{ $vendor->city ?: 'our maker studio' }}.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#faf8f5] border border-stone-200/60 space-y-1.5">
                        <span class="font-serif italic font-bold text-base text-stone-900 block">◈ Verified Materials</span>
                        <p class="leading-relaxed">Inspected and verified on the K71 Merchant Platform.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#faf8f5] border border-stone-200/60 space-y-1.5">
                        <span class="font-serif italic font-bold text-base text-stone-900 block">⚡ Insured Transit</span>
                        <p class="leading-relaxed">Doorstep courier transit with full pan-India tracking.</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#faf8f5] border border-stone-200/60 space-y-1.5">
                        <span class="font-serif italic font-bold text-base text-stone-900 block">↺ Concierge Care</span>
                        <p class="leading-relaxed">{{ $vendor->settings?->return_window_days ?: 7 }}-day personal size and assistance window.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Genuine Reviews --}}
        @include('storefront.sections.reviews_section')

        {{-- Direct Concierge & Policies --}}
        @include('storefront.sections.contact_policies')

    </main>

    {{-- Minimalist Atelier Footer --}}
    @include('storefront.sections.footer')

</div>
