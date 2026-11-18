@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Modular precision flagship combining live specs, rapid discovery, and modern industrial clarity.')), 130);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $lead = $allProducts->first();
    $second = $allProducts->get(1);
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg'));
@endphp

{{-- 1. 12-COLUMN MODULAR BENTO BOX FLAGSHIP HERO --}}
<section data-k71-hero-stage class="w-full py-6 sm:py-10">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5">
            {{-- Cell 1: Main 7-Col Campaign Bento Tile --}}
            <div class="lg:col-span-7 rounded-2xl overflow-hidden relative min-h-[420px] sm:min-h-[500px] flex flex-col justify-end p-6 sm:p-10"
                 style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-media
                     class="absolute inset-0 w-full h-full object-cover opacity-35">
                <div class="absolute inset-0" style="background: linear-gradient(180deg, transparent 10%, var(--t-bg) 96%);"></div>

                <div class="relative z-10 space-y-4 max-w-xl">
                    <span data-k71-hero-meta class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-[0.2em]"
                          style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                        {{ $manifest['name'] }} · Flagship Bento
                    </span>
                    <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-5xl xl:text-6xl leading-[0.98]">
                        {{ $heroHeadline }}
                    </h1>
                    <p data-k71-hero-meta class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">
                        {{ $heroSub }}
                    </p>
                    <div data-k71-hero-meta class="flex flex-wrap items-center gap-3 pt-1">
                        <a href="{{ $storeCollectionUrl }}" data-k71-magnetic class="k71-btn k71-btn-primary">
                            <span>Shop All Products</span>
                            @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                        </a>
                    </div>
                </div>
            </div>

            {{-- Right 5-Col Bento Stack --}}
            <div class="lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-4 sm:gap-5">
                {{-- Cell 2: Live Spotlight Interactive Product Tile --}}
                @if($lead)
                    @php $lImg = $lead->images?->first()?->url ?? asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg'); @endphp
                    <div data-k71-3d-card class="rounded-2xl p-5 flex items-center gap-4" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                        <a href="{{ $prodUrl($lead) }}" class="w-28 h-28 sm:w-32 sm:h-32 rounded-xl overflow-hidden shrink-0" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $lImg }}" alt="{{ $lead->title }}" class="w-full h-full object-cover">
                        </a>
                        <div class="min-w-0 flex-1 space-y-2">
                            <p class="text-[10px] font-mono uppercase tracking-widest" style="color: var(--t-accent);">Spotlight SKU</p>
                            <h2 class="k71-heading text-base sm:text-lg leading-tight line-clamp-2">
                                <a href="{{ $prodUrl($lead) }}">{{ $lead->title }}</a>
                            </h2>
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <span class="text-base font-mono font-extrabold">₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</span>
                                <a href="{{ $prodUrl($lead) }}" class="px-3 py-1.5 rounded-lg text-[11px] font-bold"
                                   style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Inspect</a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Cell 3 & 4: Metrics + Quick Departments Bento Row --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-2xl p-5 flex flex-col justify-between" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                        <p class="text-[10px] font-mono uppercase tracking-widest" style="color: var(--t-muted);">Live Catalog</p>
                        <div class="pt-4">
                            <p class="k71-heading text-3xl sm:text-4xl font-extrabold">{{ $allProducts->count() }}</p>
                            <p class="text-xs mt-1" style="color: var(--t-muted);">Active Items</p>
                        </div>
                    </div>

                    <div class="rounded-2xl p-5 flex flex-col justify-between" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                        <p class="text-[10px] font-mono uppercase tracking-widest" style="color: var(--t-muted);">Departments</p>
                        <div class="flex flex-wrap gap-1.5 pt-3">
                            @foreach(($vendorCategories ?? collect())->take(4) as $bCat)
                                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($bCat->slug) }}"
                                   class="px-2.5 py-1 rounded-md text-[11px] font-semibold truncate max-w-full hover:opacity-75"
                                   style="background-color: var(--t-surface-alt);">
                                    {{ $bCat->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if($second)
                    @php $s2Img = $second->images?->first()?->url ?? asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg'); @endphp
                    <a href="{{ $prodUrl($second) }}" class="rounded-2xl p-4 flex items-center justify-between gap-4 hover:opacity-85 transition"
                       style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                        <div class="flex items-center gap-3 min-w-0">
                            <img src="{{ $s2Img }}" alt="{{ $second->title }}" class="w-14 h-14 rounded-lg object-cover shrink-0">
                            <div class="min-w-0">
                                <p class="text-[10px] font-mono uppercase" style="color: var(--t-muted);">Next Release</p>
                                <p class="text-sm font-bold truncate">{{ $second->title }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-mono font-bold shrink-0" style="color: var(--t-accent);">₹{{ number_format(($second->base_price ?? 0) / 100, 0) }}</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- 2. MODULAR SPEC-MATRIX PRODUCT SHOWCASE --}}
<section class="w-full py-10 sm:py-14">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <div>
                <p class="text-[11px] font-mono uppercase tracking-[0.2em] mb-1" style="color: var(--t-accent);">Module Grid</p>
                <h2 class="k71-heading text-2xl sm:text-4xl">Complete Lineup</h2>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Filter Full Catalog</a>
        </div>

        <div data-k71-stagger-grid class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            @foreach($allProducts->take(12) as $product)
                <div data-k71-card-item data-k71-3d-card>
                    @include('storefront.themes.partials.card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
