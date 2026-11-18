@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
@endphp

{{-- ZERO CONVENTIONAL HERO BANNER: PRODUCTS ARE THE FIRST VIEWPORT (SSENSE / AESOP / ACNE DISCIPLINE) --}}
<section data-k71-hero-stage class="w-full py-5 sm:py-7" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex flex-wrap items-baseline gap-x-5 gap-y-1">
            <h1 data-k71-hero-title class="k71-heading text-xl sm:text-2xl tracking-tight">{{ $heroHeadline }}</h1>
            <span data-k71-hero-meta class="text-xs font-mono uppercase tracking-wider" style="color: var(--t-muted);">
                {{ $allProducts->count() }} Live Objects · {{ $manifest['name'] }}
            </span>
        </div>

        {{-- Inline Quiet Filter & Category Bar Above the Fold --}}
        <div data-k71-hero-meta class="flex items-center gap-4 overflow-x-auto k71-no-scrollbar text-xs">
            <a href="{{ $storeCollectionUrl }}" class="font-bold underline underline-offset-4 shrink-0" style="color: var(--t-accent);">All</a>
            @foreach(($vendorCategories ?? collect())->take(7) as $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                   class="hover:opacity-65 shrink-0 transition" style="color: var(--t-muted);">
                    {{ $cat->name }}
                </a>
            @endforeach
            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}sort=price_low" class="hidden sm:inline font-mono text-[11px] pl-3 border-l shrink-0" style="border-color: var(--t-border); color: var(--t-muted);">Sort &darr;</a>
        </div>
    </div>
</section>

{{-- IMMEDIATE FULL-BLEED BORDERLESS PRODUCT WALL RIGHT ABOVE THE FOLD --}}
<section class="w-full py-6 sm:py-10">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8">
        <div data-k71-stagger-grid class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-x-4 sm:gap-x-6 gap-y-10 sm:gap-y-14">
            @foreach($allProducts->take(15) as $product)
                @php
                    $pImg = $product->images?->first()?->url ?? $fallbackArt;
                    $pLink = $prodUrl($product);
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item class="group flex flex-col">
                    <a href="{{ $pLink }}" class="block aspect-[3/4] w-full overflow-hidden mb-3" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]">
                    </a>
                    <div class="space-y-1 flex-1">
                        <p class="text-[10px] uppercase tracking-[0.16em]" style="color: var(--t-muted);">{{ $product->category?->name ?? $storeName }}</p>
                        <h2 class="text-xs sm:text-sm font-medium leading-snug line-clamp-2">
                            <a href="{{ $pLink }}" class="hover:opacity-70">{{ $product->title }}</a>
                        </h2>
                    </div>
                    <div class="pt-2 flex items-center justify-between gap-2 text-xs">
                        <span class="font-mono font-semibold">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</span>
                        @if($hasVariants)
                            <a href="{{ $pLink }}" class="text-[11px] uppercase tracking-wider hover:opacity-70" style="color: var(--t-muted);">Options</a>
                        @else
                            <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="text-[11px] uppercase tracking-wider font-semibold hover:opacity-70 cursor-pointer" style="color: var(--t-accent);">+ Bag</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
