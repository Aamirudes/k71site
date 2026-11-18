@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'marble-stone') . '.svg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
@endphp

{{-- 1. FULL-VIEWPORT 4-COLUMN HORIZONTAL EXHIBITION FILMSTRIP FIRST VIEWPORT --}}
<section data-k71-hero-stage class="w-full" style="border-bottom: 1px solid var(--t-border);">
    <div class="px-4 sm:px-8 py-5 flex flex-wrap items-baseline justify-between gap-4" style="border-bottom: 1px solid var(--t-border);">
        <div class="flex flex-wrap items-baseline gap-4">
            <h1 data-k71-hero-title class="k71-heading text-2xl sm:text-3xl tracking-tight">{{ $heroHeadline }}</h1>
            <span data-k71-hero-meta class="text-[11px] font-mono uppercase tracking-[0.22em]" style="color: var(--t-muted);">
                {{ $manifest['name'] }} · Horizontal Gallery Wall
            </span>
        </div>
        <a href="{{ $storeCollectionUrl }}" data-k71-hero-meta class="text-xs font-mono uppercase tracking-[0.18em] font-bold hover:opacity-70" style="color: var(--t-accent);">
            Full Archive ({{ $allProducts->count() }}) &rarr;
        </a>
    </div>

    {{-- 4 Tall Side-by-Side Exhibition Columns Filling the First Viewport --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 divide-y sm:divide-y-0 sm:divide-x" style="border-color: var(--t-border);">
        @foreach($allProducts->take(4) as $idx => $item)
            @php $pImg = $item->images?->first()?->url ?? $fallbackArt; @endphp
            <a href="{{ $prodUrl($item) }}" data-k71-hero-media data-k71-3d-card class="group flex flex-col justify-between p-5 sm:p-6 min-h-[68vh] hover:opacity-95 transition" style="border-color: var(--t-border);">
                <div class="flex items-baseline justify-between mb-4">
                    <span class="k71-heading text-4xl sm:text-5xl opacity-25 font-light">0{{ $idx + 1 }}</span>
                    <span class="text-[10px] font-mono uppercase tracking-[0.2em]" style="color: var(--t-muted);">{{ $item->category?->name ?? 'Exhibit' }}</span>
                </div>

                <div class="aspect-[3/4] w-full overflow-hidden my-auto" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $pImg }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>

                <div class="pt-5 mt-5 flex items-end justify-between gap-3" style="border-top: 1px solid var(--t-border);">
                    <div class="min-w-0">
                        <h2 class="k71-heading text-base sm:text-lg leading-snug truncate">{{ $item->title }}</h2>
                        <p class="text-[11px] font-mono mt-0.5" style="color: var(--t-muted);">Inspect Exhibit &rarr;</p>
                    </div>
                    <span class="font-mono text-sm font-bold shrink-0">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</span>
                </div>
            </a>
        @endforeach
    </div>
</section>

{{-- 2. SECONDARY CURATED GRID --}}
@if($allProducts->count() > 4)
<section class="w-full py-14 sm:py-18">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8">
        <div class="flex items-end justify-between mb-8">
            <h2 class="k71-heading text-2xl sm:text-3xl">Further Works</h2>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">View Complete Catalogue</a>
        </div>
        <div data-k71-stagger-grid class="grid grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($allProducts->slice(4)->take(8) as $product)
                <div data-k71-card-item>
                    @include('storefront.themes.partials.card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
