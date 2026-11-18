@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
@endphp

@if($sv === 'B')
{{-- VARIANT B (Minimal Sole): ASYMMETRIC 2-GIANT HERO OBJECTS + 4-COLUMN SNEAKER/OBJECT WALL --}}
<section data-k71-hero-stage class="w-full py-5" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 flex flex-wrap items-center justify-between gap-4">
        <h1 data-k71-hero-title class="k71-heading text-2xl sm:text-3xl tracking-tight">{{ $heroHeadline }}</h1>
        <div data-k71-hero-meta class="flex items-center gap-3 text-xs font-mono uppercase">
            <span>{{ $manifest['name'] }}</span>
            <span style="color: var(--t-muted);">//</span>
            <a href="{{ $storeCollectionUrl }}" class="font-bold underline" style="color: var(--t-accent);">All {{ $allProducts->count() }} Releases</a>
        </div>
    </div>
</section>
<section class="w-full py-6 sm:py-8">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 space-y-10">
        {{-- Top 2 Giant Widescreen Product Plinths Above the Fold --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($allProducts->take(2) as $fIdx => $item)
                @php $fImg = $item->images?->first()?->url ?? $fallbackArt; @endphp
                <a href="{{ $prodUrl($item) }}" data-k71-hero-media data-k71-3d-card class="group block overflow-hidden" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                    <div class="aspect-[16/10] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $fImg }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <div class="p-5 flex items-baseline justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] font-mono uppercase tracking-[0.2em]" style="color: var(--t-muted);">SILHOUETTE 0{{ $fIdx + 1 }}</p>
                            <h2 class="k71-heading text-xl sm:text-2xl truncate mt-0.5">{{ $item->title }}</h2>
                        </div>
                        <span class="font-mono text-base font-extrabold shrink-0">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        {{-- Remaining Compact 4-Col Index --}}
        <div data-k71-stagger-grid class="grid grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($allProducts->slice(2)->take(12) as $product)
                <div data-k71-card-item>@include('storefront.themes.partials.card', ['product' => $product])</div>
            @endforeach
        </div>
    </div>
</section>
@elseif($sv === 'C')
{{-- VARIANT C (Nordic Living): AIRY 3-COLUMN SCANDINAVIAN DAYLIGHT GALLERY WITH GENEROUS NEGATIVE SPACE --}}
<section data-k71-hero-stage class="w-full py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-5 sm:px-10 text-center space-y-4">
        <p data-k71-hero-meta class="text-[11px] uppercase tracking-[0.28em]" style="color: var(--t-muted);">{{ $manifest['name'] }} · Daylight Object Index</p>
        <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-5xl font-light tracking-tight">{{ $heroHeadline }}</h1>
        <div data-k71-hero-meta class="flex flex-wrap items-center justify-center gap-5 pt-2 text-xs">
            <a href="{{ $storeCollectionUrl }}" class="font-semibold underline underline-offset-4" style="color: var(--t-accent);">All Rooms</a>
            @foreach(($vendorCategories ?? collect())->take(6) as $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-65" style="color: var(--t-muted);">{{ $cat->name }}</a>
            @endforeach
        </div>
    </div>
</section>
<section class="w-full pb-16">
    <div class="max-w-7xl mx-auto px-5 sm:px-10">
        <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-12 gap-y-16">
            @foreach($allProducts->take(12) as $product)
                @php
                    $pImg = $product->images?->first()?->url ?? $fallbackArt;
                    $pLink = $prodUrl($product);
                @endphp
                <article data-k71-card-item class="group text-center">
                    <a href="{{ $pLink }}" class="block aspect-square w-full overflow-hidden mb-5" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.03]">
                    </a>
                    <p class="text-[10px] uppercase tracking-[0.22em]" style="color: var(--t-muted);">{{ $product->category?->name ?? 'Object' }}</p>
                    <h2 class="k71-heading text-base sm:text-lg mt-1"><a href="{{ $pLink }}">{{ $product->title }}</a></h2>
                    <p class="font-mono text-sm mt-1" style="color: var(--t-accent);">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@else
{{-- VARIANT A (Studio Label): ZERO HERO BANNER — TIGHT 5-COLUMN BORDERLESS PRODUCT WALL --}}
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
@endif
