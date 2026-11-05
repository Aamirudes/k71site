@php
    $title = $section['title'] ?? 'Featured Selections';
    $subtitle = $section['subtitle'] ?? 'Handcrafted items available for immediate dispatch';
    $columns = (int) ($section['columns'] ?? 4);
    $aspectRatio = $section['aspect_ratio'] ?? '3:4';
    $showQuickBuy = $section['show_quick_buy'] ?? true;
    
    // Choose product list: either curated products, featured spotlight, or the paginated catalog products
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

<section id="catalog" 
         data-section-id="{{ $section['id'] }}"
         class="py-12 sm:py-16 lg:py-20"
         style="background-color: var(--sf-bg, #fafaf9);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header & In-Store Filters -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-10 gap-4 border-b border-stone-200/60 pb-6">
            <div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-stone-900 font-heading">
                    {{ $title }}
                </h2>
                @if(!empty($subtitle))
                    <p class="text-xs sm:text-sm text-stone-500 mt-1 max-w-xl">
                        {{ $subtitle }}
                    </p>
                @endif
                
                @if($currentSearch || $currentCategory)
                    <div class="flex items-center gap-2 mt-3 text-xs">
                        <span class="text-stone-400">Filtering:</span>
                        @if($currentCategory)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-stone-900 text-white font-medium text-[11px]">
                                {{ $vendorCategories->firstWhere('slug', $currentCategory)?->name ?? $currentCategory }}
                                <a href="{{ route('storefront.show', array_merge(request()->except('category', 'page'), ['slug' => $vendor->slug])) }}" class="ml-1 hover:text-stone-300">✕</a>
                            </span>
                        @endif
                        @if($currentSearch)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-stone-900 text-white font-medium text-[11px]">
                                "{{ $currentSearch }}"
                                <a href="{{ route('storefront.show', array_merge(request()->except('search', 'page'), ['slug' => $vendor->slug])) }}" class="ml-1 hover:text-stone-300">✕</a>
                            </span>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sort Dropdown -->
            <div class="flex items-center gap-3">
                <span class="text-xs text-stone-400 hidden sm:inline">Sort:</span>
                <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="flex items-center">
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    @if(request('search'))
                        <input type="hidden" name="search" value="{{ request('search') }}">
                    @endif
                    <select name="sort" 
                            onchange="this.form.submit()" 
                            class="text-xs font-semibold rounded-lg border border-stone-300 bg-white py-1.5 pl-3 pr-8 focus:outline-none focus:ring-1 focus:ring-stone-900 text-stone-700">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest Drops</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </form>
            </div>
        </div>

        @if($displayProducts->isEmpty())
            <!-- Clean Empty State -->
            <div class="py-16 text-center max-w-md mx-auto">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-stone-100 flex items-center justify-center text-3xl">
                    🛍️
                </div>
                <h3 class="text-lg font-bold text-stone-900 font-heading">No items found</h3>
                <p class="text-xs text-stone-500 mt-1">
                    {{ $currentSearch || $currentCategory ? 'Try adjusting your search or category filter.' : 'This boutique is currently updating its catalogue.' }}
                </p>
                @if($currentSearch || $currentCategory)
                    <a href="{{ route('storefront.show', $vendor->slug) }}" 
                       class="inline-block mt-4 px-4 py-2 text-xs font-bold rounded-lg bg-stone-900 text-white hover:bg-stone-800 transition">
                        View All Items
                    </a>
                @endif
            </div>
        @else
            <!-- Responsive Product Cards Grid -->
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

                    <div class="group flex flex-col h-full bg-white rounded-2xl overflow-hidden border border-stone-200/80 hover:shadow-xl transition-all duration-300 relative"
                         style="background-color: var(--sf-surface, #ffffff);">
                        
                        <!-- Product Media Canvas -->
                        <div class="relative w-full {{ $ratioClass }} bg-stone-100 overflow-hidden">
                            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full relative" aria-label="{{ $product->title }}">
                                <img src="{{ $imgFirst }}" 
                                     alt="{{ $product->title }}" 
                                     loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 {{ $isOutOfStock ? 'opacity-50 grayscale-[50%]' : '' }}">
                                
                                @if($imgSecond)
                                    <img src="{{ $imgSecond }}" 
                                         alt="{{ $product->title }} Alternate View" 
                                         loading="lazy"
                                         class="w-full h-full object-cover absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                @endif
                            </a>

                            <!-- Badges -->
                            <div class="absolute top-2.5 left-2.5 flex flex-col gap-1 z-10">
                                @if($hasComparePrice && $discountPct > 0)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-rose-600 text-white shadow-xs">
                                        -{{ $discountPct }}%
                                    </span>
                                @endif
                                @if($product->is_featured)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-amber-500 text-stone-950 shadow-xs">
                                        Spotlight
                                    </span>
                                @endif
                                @if($isOutOfStock)
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold tracking-wider uppercase bg-stone-900/90 text-stone-200 backdrop-blur-xs">
                                        Sold Out
                                    </span>
                                @endif
                            </div>

                            <!-- Floating Action Overlay (Desktop) -->
                            @if(!$isOutOfStock && $showQuickBuy)
                                <div class="absolute bottom-2.5 inset-x-2.5 z-10 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0 hidden sm:block">
                                    <button type="button" 
                                            onclick="if(window.quickAddToCart){ window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}); } else { window.location.href='{{ route('product.show', $product->slug) }}'; }"
                                            class="w-full py-2.5 px-3 rounded-xl bg-stone-900/95 hover:bg-stone-900 text-white text-xs font-bold uppercase tracking-wider shadow-md backdrop-blur-xs flex items-center justify-center gap-1.5 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                        <span>Quick Add</span>
                                    </button>
                                </div>
                            @endif
                        </div>

                        <!-- Product Information Details -->
                        <div class="p-3.5 sm:p-4 flex flex-col flex-1 justify-between gap-2">
                            <div>
                                @if($product->category)
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-stone-400 block mb-1">
                                        {{ $product->category->name }}
                                    </span>
                                @endif

                                <h3 class="text-xs sm:text-sm font-semibold text-stone-900 line-clamp-2 hover:text-stone-700 transition leading-snug">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        {{ $product->title }}
                                    </a>
                                </h3>
                            </div>

                            <!-- Pricing & Buy -->
                            <div class="pt-1 flex items-center justify-between gap-2">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-sm sm:text-base font-bold text-stone-950">
                                        ₹{{ $priceRupees }}
                                    </span>
                                    @if($hasComparePrice)
                                        <span class="text-xs text-stone-400 line-through">
                                            ₹{{ $comparePriceRupees }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Mobile Buy Button -->
                                @if(!$isOutOfStock)
                                    <button type="button" 
                                            onclick="if(window.quickAddToCart){ window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}); } else { window.location.href='{{ route('product.show', $product->slug) }}'; }"
                                            class="sm:hidden p-1.5 rounded-lg bg-stone-900 text-white hover:bg-stone-800 transition"
                                            aria-label="Add {{ $product->title }} to cart">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Catalog Pagination (If paginated) -->
            @if(method_exists($displayProducts, 'links') && $displayProducts->hasPages())
                <div class="mt-10 sm:mt-12 flex justify-center">
                    {{ $displayProducts->fragment('catalog')->links() }}
                </div>
            @endif
        @endif

    </div>
</section>

