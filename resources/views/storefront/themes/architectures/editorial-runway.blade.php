@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Curated pieces presented with quiet architectural precision.')), 140);
    $heroCta = trim((string) ($heroCfg['cta_label'] ?? '')) ?: 'Explore Collection';
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $leadProduct = $allProducts->first();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($leadProduct?->images?->first()?->url ?? $vendor->banner_url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
    $pair1 = $allProducts->slice(0, 2)->values();
    $pair2 = $allProducts->slice(2, 2)->values();
    $remaining = $allProducts->slice(4)->values();
@endphp

{{-- 1. FULL-BLEED RUNWAY CAMPAIGN HERO --}}
<section data-k71-hero-stage class="relative w-full min-h-[78vh] lg:min-h-[90vh] flex items-end overflow-hidden" style="background-color: #0b0b0d;">
    <div class="absolute inset-0 overflow-hidden">
        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-media data-k71-hero-scrub
             class="w-full h-full object-cover object-center opacity-85">
        <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(8,8,10,0.14) 0%, rgba(8,8,10,0.28) 52%, rgba(8,8,10,0.88) 100%);"></div>

        {{-- Interactive Shop-the-Look Hotspot (Shopify Prestige Pattern) --}}
        @if($leadProduct)
            <button type="button"
                    data-k71-quickview
                    data-qv-id="{{ $leadProduct->id }}"
                    data-qv-title="{{ $leadProduct->title }}"
                    data-qv-cat="{{ $leadProduct->category?->name ?? 'Runway Look' }}"
                    data-qv-price="₹{{ number_format(($leadProduct->base_price ?? 0) / 100, 0) }}"
                    data-qv-desc="{{ \Illuminate\Support\Str::limit(strip_tags((string) ($leadProduct->description ?? '')), 200) }}"
                    data-qv-img="{{ $heroImg }}"
                    data-qv-url="{{ $prodUrl($leadProduct) }}"
                    aria-label="Shop featured look: {{ $leadProduct->title }}"
                    class="hidden sm:inline-flex items-center gap-2.5 absolute top-[38%] right-[22%] z-20 px-3.5 py-2 rounded-full bg-white/92 backdrop-blur-md text-zinc-950 text-[11px] font-bold shadow-2xl hover:scale-105 transition cursor-pointer">
                <span class="relative flex h-2.5 w-2.5"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-zinc-950 opacity-60"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-zinc-950"></span></span>
                <span>Shop the Look · ₹{{ number_format(($leadProduct->base_price ?? 0) / 100, 0) }}</span>
            </button>
        @endif
    </div>

    <div class="relative z-10 w-full max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 pb-12 sm:pb-16 pt-28 flex flex-col lg:flex-row lg:items-end justify-between gap-8 text-white">
        <div class="max-w-3xl space-y-4">
            <p data-k71-hero-meta class="text-[11px] uppercase tracking-[0.28em] text-white/75 font-semibold">
                {{ trim((string) ($heroCfg['eyebrow'] ?? '')) ?: ($manifest['name'] . ' · Editorial Edition') }}
            </p>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl lg:text-7xl xl:text-8xl leading-[0.95] text-white">
                {{ $heroHeadline }}
            </h1>
            <p data-k71-hero-meta class="text-sm sm:text-base text-white/80 max-w-xl leading-relaxed">
                {{ $heroSub }}
            </p>
        </div>

        <div data-k71-hero-meta class="flex flex-wrap items-center gap-3 shrink-0">
            <a href="{{ $storeCollectionUrl }}" data-k71-magnetic
               class="inline-flex items-center gap-2.5 px-7 py-3.5 text-xs uppercase tracking-[0.2em] font-bold bg-white text-zinc-950 hover:bg-zinc-200 transition">
                <span>{{ $heroCta }}</span>
                @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
            </a>
            @if($leadProduct)
                <a href="{{ $prodUrl($leadProduct) }}"
                   class="inline-flex items-center gap-2 px-6 py-3.5 text-xs uppercase tracking-[0.18em] font-semibold border border-white/40 text-white hover:bg-white/10 transition">
                    <span>Featured Piece</span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- 2. MINIMAL EDITORIAL CATEGORY STRIP --}}
@if(($vendorCategories ?? collect())->isNotEmpty())
<section data-k71-reveal class="w-full py-6" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 flex items-center justify-between gap-6 overflow-x-auto k71-no-scrollbar">
        <span class="k71-eyebrow shrink-0" style="color: var(--t-muted);">Chapters</span>
        <div class="flex items-center gap-7">
            <a href="{{ $storeCollectionUrl }}" class="text-xs uppercase tracking-[0.18em] font-semibold shrink-0" style="color: var(--t-accent);">All</a>
            @foreach($vendorCategories->take(7) as $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                   class="text-xs uppercase tracking-[0.18em] font-medium shrink-0 hover:opacity-65 transition">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 3. ALTERNATING OFFSET EDITORIAL PAIRS (7-col + 5-col Asymmetric Lookbook) --}}
@if($pair1->isNotEmpty())
<section class="w-full py-14 sm:py-20">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 space-y-16 sm:space-y-24">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
            @foreach($pair1 as $idx => $item)
                @php
                    $colSpan = $idx === 0 ? 'lg:col-span-7' : 'lg:col-span-5 lg:mt-20';
                    $aspect = $idx === 0 ? 'aspect-[4/3]' : 'aspect-[3/4]';
                    $img = $item->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                @endphp
                <article class="{{ $colSpan }} group" data-k71-reveal @if($idx === 1) data-k71-parallax="24" @endif>
                    <a href="{{ $prodUrl($item) }}" class="block {{ $aspect }} overflow-hidden" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $img }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </a>
                    <div class="pt-5 flex items-baseline justify-between gap-4" style="border-bottom: 1px solid var(--t-border); padding-bottom: 1rem;">
                        <div class="min-w-0">
                            <p class="text-[10px] uppercase tracking-[0.22em] mb-1" style="color: var(--t-muted);">Look 0{{ $idx + 1 }} · {{ $item->category?->name ?? 'Collection' }}</p>
                            <h2 class="k71-heading text-xl sm:text-2xl truncate"><a href="{{ $prodUrl($item) }}">{{ $item->title }}</a></h2>
                        </div>
                        <p class="text-base sm:text-lg font-semibold shrink-0">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        @if($pair2->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
                @foreach($pair2 as $idx => $item)
                    @php
                        $colSpan = $idx === 0 ? 'lg:col-span-5 lg:mt-14' : 'lg:col-span-7';
                        $aspect = $idx === 0 ? 'aspect-[3/4]' : 'aspect-[4/3]';
                        $img = $item->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                    @endphp
                    <article class="{{ $colSpan }} group" data-k71-reveal @if($idx === 0) data-k71-parallax="20" @endif>
                        <a href="{{ $prodUrl($item) }}" class="block {{ $aspect }} overflow-hidden" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $img }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </a>
                        <div class="pt-5 flex items-baseline justify-between gap-4" style="border-bottom: 1px solid var(--t-border); padding-bottom: 1rem;">
                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-[0.22em] mb-1" style="color: var(--t-muted);">Look 0{{ $idx + 3 }} · {{ $item->category?->name ?? 'Collection' }}</p>
                                <h2 class="k71-heading text-xl sm:text-2xl truncate"><a href="{{ $prodUrl($item) }}">{{ $item->title }}</a></h2>
                            </div>
                            <p class="text-base sm:text-lg font-semibold shrink-0">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

{{-- 4. CURATED ARCHIVE GRID --}}
@if($remaining->isNotEmpty() || $allProducts->isNotEmpty())
<section class="w-full py-12 sm:py-16" style="border-top: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
            <div>
                <p class="k71-eyebrow mb-1.5" style="color: var(--t-accent);">Complete Edit</p>
                <h2 class="k71-heading text-3xl sm:text-4xl">The Collection</h2>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">View All Pieces</a>
        </div>
        <div data-k71-stagger-grid class="grid grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-9">
            @foreach(($remaining->isNotEmpty() ? $remaining : $allProducts)->take(9) as $product)
                <div data-k71-card-item>
                    @include('storefront.themes.partials.card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 5. REAL REVIEWS (ONLY IF PRESENT) --}}
@if(($vendorReviews ?? collect())->isNotEmpty())
<section data-k71-reveal class="w-full py-12 sm:py-16" style="border-top: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14">
        <p class="k71-eyebrow mb-2" style="color: var(--t-muted);">Client Notes</p>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            @foreach($vendorReviews->take(3) as $rev)
                <div class="p-6" style="border: 1px solid var(--t-border);">
                    <p class="text-xs font-semibold mb-2" style="color: var(--t-accent);">{{ number_format((float) $rev->rating, 1) }} / 5.0</p>
                    <p class="text-sm leading-relaxed mb-4">{{ \Illuminate\Support\Str::limit($rev->comment ?? '', 140) }}</p>
                    <p class="text-xs" style="color: var(--t-muted);">{{ $rev->user?->name ?? 'Verified Client' }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
