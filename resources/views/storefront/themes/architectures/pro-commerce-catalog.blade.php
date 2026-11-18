@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Fast department discovery, transparent pricing, and direct checkout across every SKU.')), 140);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $actionBase = explode('?', $storeCollectionUrl)[0];
@endphp

{{-- 1. HIGH-EFFICIENCY PRO-COMMERCE BANNER + INTEGRATED INSTANT SEARCH --}}
<section data-k71-hero-stage class="w-full py-6 sm:py-8" style="background-color: var(--t-surface); border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="space-y-2 max-w-2xl">
            <div data-k71-hero-meta class="flex items-center gap-2 text-[11px] font-bold uppercase tracking-wider" style="color: var(--t-accent);">
                @include('storefront.themes.partials.icon', ['name' => 'shield', 'class' => 'w-3.5 h-3.5'])
                <span>{{ $manifest['name'] }} · Verified Merchant Catalog</span>
            </div>
            <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-4xl lg:text-5xl leading-tight">{{ $heroHeadline }}</h1>
            <p data-k71-hero-meta class="text-xs sm:text-sm" style="color: var(--t-muted);">{{ $heroSub }}</p>
        </div>

        <div data-k71-hero-meta class="flex flex-wrap items-center gap-3 shrink-0">
            <div class="px-4 py-2.5 rounded-lg text-xs" style="background-color: var(--t-bg); border: 1px solid var(--t-border);">
                <span class="font-extrabold block">{{ $allProducts->count() }} Active SKUs</span>
                <span style="color: var(--t-muted);">Live Store Inventory</span>
            </div>
            @if($vendor->settings?->return_window_days)
                <div class="px-4 py-2.5 rounded-lg text-xs" style="background-color: var(--t-bg); border: 1px solid var(--t-border);">
                    <span class="font-extrabold block">{{ $vendor->settings->return_window_days }}-Day Returns</span>
                    <span style="color: var(--t-muted);">Verified Merchant Policy</span>
                </div>
            @endif
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !min-h-[46px] shrink-0">Browse All</a>
        </div>
    </div>
</section>

{{-- 2. DESKTOP STICKY LEFT DEPARTMENT RAIL + DENSE HIGH-CONVERSION PRODUCT MATRIX --}}
<section class="w-full py-8 sm:py-10">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col lg:flex-row gap-6 lg:gap-8">
        {{-- Sticky Left Department Sidebar --}}
        <aside class="lg:w-64 shrink-0">
            <div class="lg:sticky lg:top-24 p-5 space-y-5" style="background-color: var(--t-surface); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                <div>
                    <h2 class="k71-eyebrow mb-3" style="color: var(--t-muted);">Departments</h2>
                    <div class="flex lg:flex-col gap-1.5 overflow-x-auto k71-no-scrollbar">
                        <a href="{{ $storeCollectionUrl }}"
                           class="shrink-0 px-3 py-2 text-xs font-bold rounded-md flex items-center justify-between"
                           style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                            <span>All Products</span>
                            <span>{{ $allProducts->count() }}</span>
                        </a>
                        @foreach(($vendorCategories ?? collect())->take(10) as $cat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                               class="shrink-0 px-3 py-2 text-xs font-medium rounded-md flex items-center justify-between hover:opacity-75 transition"
                               style="border: 1px solid var(--t-border);">
                                <span class="truncate pr-2">{{ $cat->name }}</span>
                                <span class="font-mono text-[10px]" style="color: var(--t-muted);">{{ $cat->products_count ?? '' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="hidden lg:block pt-4 space-y-2 text-xs" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">
                    <p class="font-bold" style="color: var(--t-text);">Quick Sort</p>
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}sort=price_low" class="block hover:opacity-75">Price: Low to High</a>
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}sort=price_high" class="block hover:opacity-75">Price: High to Low</a>
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}sort=newest" class="block hover:opacity-75">Newest Arrivals</a>
                </div>
            </div>
        </aside>

        {{-- Right High-Speed Product Matrix --}}
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-5">
                <h2 class="k71-heading text-xl sm:text-2xl">Available Inventory ({{ $allProducts->count() }})</h2>
                <a href="{{ $storeCollectionUrl }}" class="text-xs font-bold hover:opacity-75" style="color: var(--t-accent);">Open Full Catalog &rarr;</a>
            </div>

            <div data-k71-stagger-grid class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5 sm:gap-5">
                @foreach($allProducts->take(16) as $product)
                    @php
                        $pImg = $product->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                        $pLink = $prodUrl($product);
                        $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                    @endphp
                    <article data-k71-card-item class="flex flex-col justify-between overflow-hidden" style="background-color: var(--t-surface); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                        <div>
                            <a href="{{ $pLink }}" class="block aspect-[4/3] overflow-hidden" style="background-color: var(--t-surface-alt);">
                                <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
                            </a>
                            <div class="p-3.5 space-y-1">
                                <p class="text-[10px] font-mono uppercase tracking-wider truncate" style="color: var(--t-muted);">
                                    {{ $product->sku ?? ($product->category?->name ?? 'IN STOCK') }}
                                </p>
                                <h3 class="text-xs sm:text-sm font-bold leading-snug line-clamp-2">
                                    <a href="{{ $pLink }}" class="hover:opacity-75">{{ $product->title }}</a>
                                </h3>
                            </div>
                        </div>

                        <div class="p-3.5 pt-2 flex items-center justify-between gap-2" style="border-top: 1px solid var(--t-border);">
                            <span class="text-sm sm:text-base font-extrabold">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</span>
                            @if($hasVariants)
                                <a href="{{ $pLink }}" class="px-2.5 py-1.5 text-[11px] font-bold rounded" style="border: 1px solid var(--t-border);">Options</a>
                            @else
                                <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="px-3 py-1.5 text-[11px] font-bold rounded cursor-pointer"
                                            style="background-color: var(--t-accent); color: var(--t-accent-contrast);">+ Add</button>
                                </form>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
