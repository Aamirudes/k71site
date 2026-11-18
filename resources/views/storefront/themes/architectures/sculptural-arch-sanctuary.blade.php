@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'botanical-beauty') . '.jpg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Sculptural forms, calm architectural arches, and tactile object curation.')), 140);
    $trio = $allProducts->take(3)->values();
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
@endphp

@if($sv === 'B')
{{-- VARIANT B (Jewelle): ASYMMETRIC LEFT MONUMENTAL ARCH + RIGHT HOUSE STORY & TWIN MINI ARCHES --}}
<section data-k71-hero-stage class="w-full py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
        @php $archLead = $trio->first(); @endphp
        <div class="lg:col-span-6">
            <div data-k71-hero-media class="aspect-[3/4] w-full max-w-lg mx-auto overflow-hidden rounded-t-full" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                <img src="{{ $archLead?->images?->first()?->url ?? $fallbackArt }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
            </div>
        </div>
        <div class="lg:col-span-6 space-y-8">
            <div class="space-y-4">
                <p data-k71-hero-meta class="k71-eyebrow" style="color: var(--t-accent);">{{ $manifest['name'] }} · Fine Maison</p>
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl leading-[1.02]">{{ $heroHeadline }}</h1>
                <p data-k71-hero-meta class="text-sm sm:text-base leading-relaxed" style="color: var(--t-muted);">{{ $heroSub }}</p>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary rounded-full px-8">Explore Maison</a>
            </div>
            <div class="grid grid-cols-2 gap-5 pt-4" style="border-top: 1px solid var(--t-border);">
                @foreach($trio->slice(1, 2) as $mini)
                    <a href="{{ $prodUrl($mini) }}" class="group flex items-center gap-3.5">
                        <div class="w-16 h-20 shrink-0 rounded-t-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $mini->images?->first()?->url ?? $fallbackArt }}" alt="{{ $mini->title }}" class="w-full h-full object-cover">
                        </div>
                        <div class="min-w-0">
                            <h2 class="k71-heading text-sm truncate">{{ $mini->title }}</h2>
                            <p class="font-mono text-xs mt-0.5" style="color: var(--t-accent);">₹{{ number_format(($mini->base_price ?? 0) / 100, 0) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@elseif($sv === 'C')
{{-- VARIANT C (Earth & Grain): FULL-WIDTH 4-ARCH HORIZON STRIP WITH LEFT-ALIGNED EDITORIAL HEADER --}}
<section data-k71-hero-stage class="w-full py-10 sm:py-14">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-8 space-y-10">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-6" style="border-bottom: 1px solid var(--t-border);">
            <div class="max-w-2xl space-y-2">
                <p data-k71-hero-meta class="k71-eyebrow" style="color: var(--t-accent);">{{ $manifest['name'] }} · Organic Horizon</p>
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-5xl">{{ $heroHeadline }}</h1>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline rounded-full">Browse Craft &rarr;</a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 items-end">
            @foreach($allProducts->take(4) as $hIdx => $item)
                <a href="{{ $prodUrl($item) }}" data-k71-hero-media class="group block text-center">
                    <div class="{{ $hIdx % 2 === 0 ? 'aspect-[3/4]' : 'aspect-[4/4]' }} w-full overflow-hidden rounded-t-full mb-4" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                        <img src="{{ $item->images?->first()?->url ?? $fallbackArt }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <h2 class="k71-heading text-base truncate">{{ $item->title }}</h2>
                    <p class="font-mono text-xs mt-1" style="color: var(--t-muted);">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</p>
                </a>
            @endforeach
        </div>
    </div>
</section>
@elseif($sv === 'D')
{{-- VARIANT D (Skin Ritual): TWIN MONUMENTAL APOTHECARY ARCHES WITH CENTERED FLOATING TITLE PILL --}}
<section data-k71-hero-stage class="w-full py-10 sm:py-14">
    <div class="max-w-6xl mx-auto px-5 sm:px-8">
        <div class="relative grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-10">
            @foreach($allProducts->take(2) as $aIdx => $item)
                <a href="{{ $prodUrl($item) }}" data-k71-hero-media class="group block">
                    <div class="aspect-[4/5] w-full overflow-hidden rounded-t-[12rem] rounded-b-3xl" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                        <img src="{{ $item->images?->first()?->url ?? $fallbackArt }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <div class="pt-4 flex items-baseline justify-between px-2">
                        <h2 class="k71-heading text-lg truncate">{{ $item->title }}</h2>
                        <span class="font-mono text-sm font-semibold">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</span>
                    </div>
                </a>
            @endforeach
            <div class="md:absolute md:inset-x-0 md:top-1/2 md:-translate-y-1/2 md:pointer-events-none flex justify-center mt-6 md:mt-0">
                <div class="md:pointer-events-auto px-8 py-6 rounded-3xl text-center max-w-md shadow-xl" style="background-color: var(--t-bg); border: 1px solid var(--t-border);">
                    <p data-k71-hero-meta class="text-[10px] uppercase tracking-[0.28em]" style="color: var(--t-accent);">{{ $manifest['name'] }} · Ritual</p>
                    <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-4xl mt-1">{{ $heroHeadline }}</h1>
                    <a href="{{ $storeCollectionUrl }}" class="inline-block mt-3 text-xs font-bold uppercase tracking-[0.2em] underline" style="color: var(--t-accent);">Shop Formulations &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</section>
@else
{{-- VARIANT A (Soft Form): CENTERED QUIET SANCTUARY + 3 SCULPTURAL ARCHWAY PEDESTALS --}}
<section data-k71-hero-stage class="w-full pt-12 sm:pt-16 pb-16 sm:pb-24">
    <div class="max-w-6xl mx-auto px-5 sm:px-8">
        <div class="text-center max-w-2xl mx-auto space-y-4 mb-12 sm:mb-16">
            <p data-k71-hero-meta class="k71-eyebrow" style="color: var(--t-accent);">{{ $manifest['name'] }} · Sanctuary</p>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl leading-[1.04]">{{ $heroHeadline }}</h1>
            <p data-k71-hero-meta class="text-sm sm:text-base leading-relaxed" style="color: var(--t-muted);">{{ $heroSub }}</p>
            <div data-k71-hero-meta class="pt-2">
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary rounded-full px-8">Explore Objects</a>
            </div>
        </div>

        {{-- Three Sculptural Arched Pedestal Frames (Center Arch Elevated) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 sm:gap-10 items-end">
            @foreach($trio as $tIdx => $item)
                @php
                    $tImg = $item->images?->first()?->url ?? $fallbackArt;
                    $elevate = $tIdx === 1 ? 'md:-translate-y-8' : '';
                @endphp
                <article data-k71-hero-media data-k71-3d-card class="{{ $elevate }} group text-center">
                    <a href="{{ $prodUrl($item) }}" class="block aspect-[3/4] w-full overflow-hidden rounded-t-full mb-5"
                       style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                        <img src="{{ $tImg }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </a>
                    <p class="text-[10px] uppercase tracking-[0.22em] mb-1" style="color: var(--t-muted);">{{ $item->category?->name ?? 'Edition' }}</p>
                    <h2 class="k71-heading text-lg sm:text-xl"><a href="{{ $prodUrl($item) }}">{{ $item->title }}</a></h2>
                    <p class="text-sm font-semibold mt-1" style="color: var(--t-accent);">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 2. TACTILE PEDESTAL COLLECTION --}}
<section class="w-full py-12 sm:py-16" style="border-top: 1px solid var(--t-border);">
    <div class="max-w-6xl mx-auto px-5 sm:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
            <h2 class="k71-heading text-2xl sm:text-3xl">Complete Collection</h2>
            <a href="{{ $storeCollectionUrl }}" class="text-xs uppercase tracking-[0.2em] font-semibold hover:opacity-70" style="color: var(--t-accent);">Shop All &rarr;</a>
        </div>
        <div data-k71-stagger-grid class="grid grid-cols-2 lg:grid-cols-3 gap-7 sm:gap-10">
            @foreach($allProducts->take(9) as $product)
                @php
                    $pImg = $product->images?->first()?->url ?? $fallbackArt;
                    $pLink = $prodUrl($product);
                    $variants = $product->variants ?? collect();
                    $hasVariants = $variants->isNotEmpty();
                    $pricePaise = (int) ($product->base_price ?? 0);
                    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? 1);
                    $isSoldOut = $totalStock <= 0;
                    $firstVariant = $variants->first();
                    $primaryImg = $pImg;
                    $productUrl = $pLink;
                @endphp
                <article data-k71-card-item class="k71-product-card group text-center flex flex-col items-center">
                    <div class="relative w-full mb-4">
                        <a href="{{ $pLink }}" class="k71-card-media block w-full aspect-[4/5] rounded-t-[3rem] rounded-b-xl overflow-hidden" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                            <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </a>
                        @include('storefront.themes.partials._card_media_controls')
                    </div>
                    <h3 class="k71-heading text-base sm:text-lg leading-snug"><a href="{{ $pLink }}">{{ $product->title }}</a></h3>
                    <p class="text-sm font-semibold mt-1 mb-3">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</p>
                    @if($hasVariants)
                        <a href="{{ $pLink }}" class="px-5 py-2 rounded-full text-xs font-semibold" style="border: 1px solid var(--t-border);">Choose Option</a>
                    @else
                        <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">
                            <button type="submit" class="px-5 py-2 rounded-full text-xs font-semibold cursor-pointer" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Add to Bag</button>
                        </form>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
