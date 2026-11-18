@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Engineered for dramatic contrast, precision optics, and nocturnal authority.')), 140);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $lead = $allProducts->first();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
@endphp

{{-- 1. NOCTURNAL SPOTLIGHT THEATER HERO + REAL PROCEDURAL WEBGL ATMOSPHERIC SHADER --}}
<section data-k71-hero-stage class="relative w-full min-h-[82vh] flex items-center overflow-hidden k71-cinema-glow" style="background-color: #070709; color: #f4f4f5;">
    <canvas data-k71-shader="obsidian" aria-hidden="true" class="pointer-events-none absolute inset-0 w-full h-full opacity-90"></canvas>
    <div class="max-w-[1440px] mx-auto w-full px-5 sm:px-10 lg:px-14 py-16 lg:py-20 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
        <div class="lg:col-span-6 space-y-6 relative z-10">
            <div data-k71-hero-meta class="inline-flex items-center gap-2.5 px-3.5 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-[0.24em]"
                 style="border: 1px solid rgba(255,255,255,0.16); color: var(--t-accent); background: rgba(255,255,255,0.03);">
                <span class="w-1.5 h-1.5 rounded-full" style="background-color: var(--t-accent);"></span>
                <span>{{ $manifest['name'] }} · Flagship Theater</span>
            </div>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl xl:text-7xl leading-[0.96] text-white">
                {{ $heroHeadline }}
            </h1>
            <p data-k71-hero-meta class="text-sm sm:text-base text-zinc-400 max-w-lg leading-relaxed">
                {{ $heroSub }}
            </p>
            <div data-k71-hero-meta class="flex flex-wrap items-center gap-3 pt-2">
                <a href="{{ $storeCollectionUrl }}" data-k71-magnetic class="k71-btn k71-btn-primary">
                    <span>Enter Showroom</span>
                    @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                </a>
                @if($lead)
                    <a href="{{ $prodUrl($lead) }}" class="px-6 py-3 text-xs uppercase tracking-[0.18em] font-semibold border border-zinc-700 text-zinc-200 hover:border-white transition">
                        Inspect Flagship · ₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}
                    </a>
                @endif
            </div>
        </div>

        <div class="lg:col-span-6">
            <div data-k71-hero-media data-k71-3d-card class="relative overflow-hidden rounded-xl border border-white/10 shadow-2xl bg-zinc-950">
                <div class="aspect-[4/3] sm:aspect-[5/4] overflow-hidden">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-scrub class="w-full h-full object-cover">
                </div>
                <div class="absolute inset-x-0 bottom-0 p-5 bg-gradient-to-t from-black/90 via-black/45 to-transparent flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] font-mono uppercase tracking-[0.22em] text-zinc-400">Spotlight Release</p>
                        <p class="text-sm sm:text-base font-bold text-white">{{ $lead?->title ?? $storeName }}</p>
                    </div>
                    @if($lead)
                        <span class="text-sm font-mono font-bold" style="color: var(--t-accent);">₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- 2. HORIZONTAL SCROLL-DRIVEN CINEMA REEL (GSAP Scrubbed on Desktop, Touch Swipe on Mobile) --}}
@if($allProducts->isNotEmpty())
<section class="w-full py-14 sm:py-20 overflow-hidden" style="background-color: #0b0c10; border-top: 1px solid rgba(255,255,255,0.08); border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 mb-8 flex items-end justify-between gap-4">
        <div>
            <p class="text-[10px] font-mono uppercase tracking-[0.26em] mb-1.5" style="color: var(--t-accent);">Cinema Sequence</p>
            <h2 class="k71-heading text-2xl sm:text-4xl text-white">Signature Showcase Reel</h2>
        </div>
        <span class="hidden sm:inline text-xs font-mono text-zinc-500">Scroll to glide reel &rarr;</span>
    </div>

    <div data-k71-hscroll-wrap class="w-full overflow-x-auto lg:overflow-hidden k71-no-scrollbar px-5 sm:px-10 lg:px-14">
        <div data-k71-hscroll-track class="flex gap-5 sm:gap-6 w-max">
            @foreach($allProducts->take(8) as $rIdx => $item)
                @php
                    $rImg = $item->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                @endphp
                <article data-k71-3d-card class="w-[78vw] sm:w-[360px] lg:w-[410px] shrink-0 rounded-xl overflow-hidden border border-white/10 bg-zinc-950/90 group">
                    <a href="{{ $prodUrl($item) }}" class="block aspect-[16/10] overflow-hidden bg-zinc-900">
                        <img src="{{ $rImg }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </a>
                    <div class="p-5 flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[10px] font-mono uppercase tracking-[0.2em] text-zinc-500">Scene 0{{ $rIdx + 1 }}</p>
                            <h3 class="k71-heading text-lg text-white truncate"><a href="{{ $prodUrl($item) }}">{{ $item->title }}</a></h3>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-sm font-mono font-bold" style="color: var(--t-accent);">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- 3. DARK ARCHITECTURAL SPEC GRID --}}
<section class="w-full py-14 sm:py-20">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
            <div>
                <p class="k71-eyebrow mb-1.5" style="color: var(--t-accent);">Full Vault</p>
                <h2 class="k71-heading text-3xl sm:text-4xl">Precision Editions</h2>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Explore Complete Vault</a>
        </div>
        <div data-k71-stagger-grid class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-7">
            @foreach($allProducts->take(12) as $product)
                <div data-k71-card-item data-k71-3d-card>
                    @include('storefront.themes.partials.card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
