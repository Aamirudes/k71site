@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'marble-stone') . '.jpg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
@endphp

@if($sv === 'B')
{{-- VARIANT B (Goldline): 2-COLUMN MONUMENTAL DIPTYCH EXHIBITION WALL --}}
<section data-k71-hero-stage class="w-full" style="border-bottom: 1px solid var(--t-border);">
    <div class="px-5 sm:px-10 py-6 text-center" style="border-bottom: 1px solid var(--t-border);">
        <p data-k71-hero-meta class="text-[10px] font-mono uppercase tracking-[0.3em]" style="color: var(--t-accent);">{{ $manifest['name'] }} · High Jewelry Diptych</p>
        <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-5xl mt-1">{{ $heroHeadline }}</h1>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x" style="border-color: var(--t-border);">
        @foreach($allProducts->take(2) as $idx => $item)
            @php $pImg = $item->images?->first()?->url ?? $fallbackArt; @endphp
            <a href="{{ $prodUrl($item) }}" data-k71-hero-media data-k71-3d-card class="group flex flex-col justify-between p-8 sm:p-12 min-h-[70vh]" style="border-color: var(--t-border);">
                <div class="flex items-center justify-between text-xs font-mono uppercase" style="color: var(--t-muted);">
                    <span>PLATE 0{{ $idx + 1 }}</span>
                    <span>{{ $item->category?->name ?? 'Fine Edition' }}</span>
                </div>
                <div class="aspect-[4/3] w-full overflow-hidden my-8" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $pImg }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <div class="flex items-baseline justify-between gap-4 pt-4" style="border-top: 1px solid var(--t-border);">
                    <h2 class="k71-heading text-2xl sm:text-3xl truncate">{{ $item->title }}</h2>
                    <span class="font-mono text-base font-bold shrink-0" style="color: var(--t-accent);">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@elseif($sv === 'C')
{{-- VARIANT C (Forma Gallery): 3-COLUMN ARCHITECTURAL EXHIBITION ROOMS WITH MUSEUM PLAQUES --}}
<section data-k71-hero-stage class="w-full" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1520px] mx-auto px-5 sm:px-10 py-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p data-k71-hero-meta class="text-[10px] font-mono uppercase tracking-[0.26em]" style="color: var(--t-muted);">{{ $manifest['name'] }} · Architectural Exhibition</p>
            <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-5xl mt-1">{{ $heroHeadline }}</h1>
        </div>
        <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">All {{ $allProducts->count() }} Exhibits</a>
    </div>
    <div class="max-w-[1520px] mx-auto px-5 sm:px-10 pb-12 grid grid-cols-1 md:grid-cols-3 gap-8">
        @foreach($allProducts->take(3) as $idx => $item)
            @php $pImg = $item->images?->first()?->url ?? $fallbackArt; @endphp
            <a href="{{ $prodUrl($item) }}" data-k71-hero-media class="group flex flex-col" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                <div class="aspect-[4/3] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $pImg }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </div>
                <div class="p-6 space-y-2">
                    <p class="text-[10px] font-mono uppercase tracking-[0.22em]" style="color: var(--t-accent);">ROOM 0{{ $idx + 1 }} · {{ $item->category?->name ?? 'OBJECT' }}</p>
                    <h2 class="k71-heading text-xl leading-snug">{{ $item->title }}</h2>
                    <p class="font-mono text-sm font-bold pt-2">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</p>
                </div>
            </a>
        @endforeach
    </div>
</section>
@elseif($sv === 'D')
{{-- VARIANT D (Artisan Pantry): ASYMMETRIC 1+3 PANTRY EXHIBITION (7-COL HERO PLATE + 5-COL 3 HORIZONTAL TASTING ROWS) --}}
<section data-k71-hero-stage class="w-full py-8 sm:py-10" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 space-y-6">
        <div class="flex flex-wrap items-baseline justify-between gap-4 pb-4" style="border-bottom: 1px solid var(--t-border);">
            <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-5xl">{{ $heroHeadline }}</h1>
            <a href="{{ $storeCollectionUrl }}" class="text-xs font-mono uppercase tracking-[0.2em] font-bold" style="color: var(--t-accent);">{{ $manifest['name'] }} Pantry &rarr;</a>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            @php $pLead = $allProducts->first(); @endphp
            @if($pLead)
                <a href="{{ $prodUrl($pLead) }}" data-k71-hero-media class="lg:col-span-7 relative min-h-[54vh] overflow-hidden group block" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $pLead->images?->first()?->url ?? $fallbackArt }}" alt="{{ $pLead->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 text-white flex items-end justify-between gap-4">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-[0.24em] opacity-80">01 · SIGNATURE HARVEST</span>
                            <h2 class="k71-heading text-2xl sm:text-4xl mt-1">{{ $pLead->title }}</h2>
                        </div>
                        <span class="font-mono text-lg font-bold shrink-0">₹{{ number_format(($pLead->base_price ?? 0) / 100, 0) }}</span>
                    </div>
                </a>
            @endif
            <div class="lg:col-span-5 flex flex-col justify-between gap-4">
                @foreach($allProducts->slice(1, 3) as $tIdx => $item)
                    @php $tImg = $item->images?->first()?->url ?? $fallbackArt; @endphp
                    <a href="{{ $prodUrl($item) }}" class="flex-1 p-4 flex items-center gap-4 group hover:opacity-90 transition" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                        <div class="w-24 h-24 shrink-0 overflow-hidden" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $tImg }}" alt="{{ $item->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-[10px] font-mono uppercase tracking-[0.2em]" style="color: var(--t-muted);">0{{ $tIdx + 2 }} · PANTRY</span>
                            <h3 class="k71-heading text-lg truncate mt-0.5">{{ $item->title }}</h3>
                            <p class="font-mono text-sm font-bold mt-1" style="color: var(--t-accent);">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>
@else
{{-- VARIANT A (Monogram): 4-COLUMN HORIZONTAL EXHIBITION FILMSTRIP FIRST VIEWPORT --}}
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
@endif

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
