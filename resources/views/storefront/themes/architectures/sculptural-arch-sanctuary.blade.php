@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'botanical-beauty') . '.svg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Sculptural forms, calm architectural arches, and tactile object curation.')), 140);
    $trio = $allProducts->take(3)->values();
@endphp

{{-- 1. CENTERED QUIET SANCTUARY + 3 SCULPTURAL ARCHWAY PEDESTALS --}}
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
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item class="group text-center flex flex-col items-center">
                    <a href="{{ $pLink }}" class="block w-full aspect-[4/5] rounded-t-[3rem] rounded-b-xl overflow-hidden mb-4" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                        <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </a>
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
