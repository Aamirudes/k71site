@php
    $title = $section['title'] ?? 'Boutique Catalog';
    $subtitle = $section['subtitle'] ?? 'Handcrafted items available for immediate pan-India dispatch';
    $columns = (int) ($section['columns'] ?? 4);
    $aspectRatio = $section['aspect_ratio'] ?? '3:4';
    
    $source = $section['source'] ?? 'all';
    $displayProducts = match($source) {
        'featured' => $featuredProducts,
        'newest' => $products->take(8),
        default => $products,
    };

    $colClass = match($columns) {
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-2 md:grid-cols-3',
        default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
    };

    $ratioClass = match($aspectRatio) {
        '1:1' => 'aspect-square',
        '4:5' => 'aspect-[4/5]',
        default => 'aspect-[3/4]',
    };

    $currentSearch = request('search');
    $currentCategory = request('category');
    $currentSort = request('sort', 'newest');
@endphp

<!-- CORE PRODUCT-FIRST DISCOVERY GRID -->
<section id="catalog" class="py-8 sm:py-14 relative z-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Filter, Sort & Catalog Header -->
        <div class="p-4 sm:p-6 rounded-3xl border border-white/60 shadow-xs mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4"
             style="background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(20px);">
            <div>
                <h2 class="text-xl sm:text-2xl font-black tracking-tight text-stone-900 font-heading">
                    {{ $title }}
                </h2>
                <p class="text-xs text-stone-500 mt-0.5">
                    Showing {{ $displayProducts->total() ?? $displayProducts->count() }} authentic items
                </p>
                
                @if($currentSearch || $currentCategory)
                    <div class="flex items-center gap-2 mt-2">
                        @if($currentCategory)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-900 text-white text-[11px] font-bold">
                                {{ $vendorCategories->firstWhere('slug', $currentCategory)?->name ?? $currentCategory }}
                                <a href="{{ route('storefront.show', array_merge(request()->except('category', 'page'), ['slug' => $vendor->slug])) }}#catalog" class="opacity-75 hover:opacity-100">✕</a>
                            </span>
                        @endif
                        @if($currentSearch)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-stone-900 text-white text-[11px] font-bold">
                                "{{ $currentSearch }}"
                                <a href="{{ route('storefront.show', array_merge(request()->except('search', 'page'), ['slug' => $vendor->slug])) }}#catalog" class="opacity-75 hover:opacity-100">✕</a>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sort Selector -->
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-stone-500">Sort by:</span>
                <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <select name="sort" 
                            onchange="this.form.submit()" 
                            class="text-xs font-bold rounded-2xl border border-stone-200/80 bg-white/90 py-2 pl-3.5 pr-8 focus:outline-none focus:ring-1 focus:ring-stone-900 shadow-2xs">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest Drops</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </form>
            </div>
        </div>

        @if($displayProducts->isEmpty())
            <div class="py-16 text-center max-w-sm mx-auto p-8 rounded-3xl border border-white/60 bg-white/70 backdrop-blur-xl shadow-lg">
                <span class="text-4xl block mb-3">🛍️</span>
                <h3 class="text-base font-bold text-stone-900 font-heading">No items matched</h3>
                <p class="text-xs text-stone-500 mt-1">Try resetting your category or search filter.</p>
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="inline-block mt-4 px-5 py-2 rounded-full text-xs font-bold bg-stone-900 text-white hover:bg-stone-800 transition">
                    View Entire Store
                </a>
            </div>
        @else
            <!-- FROSTED GLASS PRODUCT CARDS GRID -->
            <div class="grid {{ $colClass }} gap-4 sm:gap-6">
                @foreach($displayProducts as $product)
                    @php
                        $priceRupees = number_format($product->base_price / 100, 2);
                        $hasComparePrice = !empty($product->compare_at_price) && $product->compare_at_price > $product->base_price;
                        $comparePriceRupees = $hasComparePrice ? number_format($product->compare_at_price / 100, 2) : null;
                        $discountPct = $hasComparePrice ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                        
                        $imgFirst = $product->images->first()?->path ? \App\Services\ImageService::url($product->images->first()->path, 600) : asset('images/product-placeholder.svg');
                        $imgSecond = $product->images->count() > 1 ? \App\Services\ImageService::url($product->images[1]->path, 600) : null;
                        
                        $isOutOfStock = $product->total_stock <= 0 && $product->variants->sum('stock') <= 0;
                        $defaultVariantId = $product->variants->first()?->id;
                    @endphp

                    <div class="group flex flex-col h-full rounded-3xl overflow-hidden border border-white/80 shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_50px_rgba(0,0,0,0.12)] transition-all duration-300 relative hover:-translate-y-1"
                         style="background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
                        
                        <!-- Media Canvas with Aspect Ratio -->
                        <div class="relative w-full {{ $ratioClass }} bg-stone-100 overflow-hidden">
                            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="{{ $product->title }}">
                                <img src="{{ $imgFirst }}" 
                                     alt="{{ $product->title }}" 
                                     loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-106 {{ $isOutOfStock ? 'opacity-50 grayscale' : '' }}">
                                
                                @if($imgSecond)
                                    <img src="{{ $imgSecond }}" 
                                         alt="{{ $product->title }}" 
                                         loading="lazy"
                                         class="w-full h-full object-cover absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                @endif
                            </a>

                            <!-- Sale Badge Pill & Status -->
                            <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-10">
                                @if($hasComparePrice && $discountPct > 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-xs">
                                        -{{ $discountPct }}%
                                    </span>
                                @endif
                                @if($product->is_featured)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-400 text-stone-950 shadow-xs">
                                        Boutique Pick
                                    </span>
                                @endif
                                @if($isOutOfStock)
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-stone-900/90 text-stone-200">
                                        Sold Out
                                    </span>
                                @endif
                            </div>

                            <!-- Verified Studio Dispatch Seal (Trust Signal) -->
                            <div class="absolute top-3 right-3 z-10">
                                <span class="px-2.5 py-1 rounded-full text-[9px] sm:text-[10px] font-bold uppercase tracking-wider bg-black/60 backdrop-blur-md text-white border border-white/20 flex items-center gap-1 shadow-xs">
                                    <span class="text-amber-400">✦</span> Verified
                                </span>
                            </div>
                        </div>

                        <!-- Product Information Canvas -->
                        <div class="p-4 flex flex-col flex-1 justify-between gap-3 bg-white/40">
                            <div class="space-y-1">
                                @if($product->category)
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-stone-500 block">
                                        {{ $product->category->name }}
                                    </span>
                                @endif

                                <h3 class="text-xs sm:text-sm font-bold text-stone-900 line-clamp-2 leading-snug group-hover:text-stone-700 transition">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        {{ $product->title }}
                                    </a>
                                </h3>

                                <!-- Authenticity & Dispatch Trust Guarantee -->
                                <div class="flex items-center gap-1.5 text-[10px] sm:text-[11px] font-semibold text-emerald-700 pt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="truncate">100% Authentic · Studio Dispatch</span>
                                </div>
                            </div>

                            <!-- Price & Always Visible Add to Bag on Single Line Down -->
                            <div class="pt-2 border-t border-stone-100/80 space-y-2">
                                <div class="flex items-baseline justify-between">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-sm sm:text-base font-black text-stone-950">
                                            ₹{{ $priceRupees }}
                                        </span>
                                        @if($hasComparePrice)
                                            <span class="text-xs text-stone-400 line-through">
                                                ₹{{ $comparePriceRupees }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($hasComparePrice && $product->compare_at_price > $product->base_price)
                                        <span class="text-[10px] font-bold text-rose-600">
                                            Save ₹{{ number_format(($product->compare_at_price - $product->base_price) / 100, 2) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Add to Bag on Single Line Down -->
                                @if(!$isOutOfStock)
                                    <button type="button" 
                                            onclick="window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}, this)"
                                            class="w-full py-2.5 px-3 rounded-2xl text-xs font-black uppercase tracking-wider text-white shadow-sm transition flex items-center justify-center gap-1.5 active:scale-95 cursor-pointer hover:brightness-110"
                                            style="background: linear-gradient(135deg, var(--sf-primary, #18181b), #27272a); border: 1px solid rgba(255,255,255,0.2);"
                                            aria-label="Add {{ $product->title }} to bag">
                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <span>+ Add to Bag</span>
                                    </button>
                                @else
                                    <div class="w-full py-2 text-center text-[10px] font-bold text-stone-400 uppercase tracking-wider bg-stone-100 rounded-xl">
                                        Sold Out
                                    </div>
                                @endif
                            </div>

                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(method_exists($displayProducts, 'links') && $displayProducts->hasPages())
                <div class="mt-10 sm:mt-12 flex justify-center">
                    {{ $displayProducts->fragment('catalog')->links() }}
                </div>
            @endif
        @endif

    </div>
</section>
