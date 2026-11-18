@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $storeHomeUrl = $storeHomeUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=home')
        : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '')));
    $storeCollectionUrl = $storeCollectionUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=collection')
        : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : ''));

    $heroTpl = $manifest['layout']['hero_template'] ?? 'asymmetric-campaign';
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = $heroCfg['headline'] ?: $storeName;
    $heroSubheadline = $heroCfg['subheadline'] ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? '')), 180);
    $heroEyebrow = $heroCfg['eyebrow'] ?: ($vendor->city ? $vendor->city . ' · Official Store' : 'Official Storefront');
    $heroCtaLabel = $heroCfg['cta_label'] ?: 'Explore Collection';
    $heroCtaUrl = ($heroCfg['cta_url'] ?? '#collection') ?: '#collection';
    $heroReleaseLabel = $heroCfg['release_label'] ?? '';
    $heroDeadline = $heroCfg['release_deadline'] ?? '';

    $pool = ($featuredProducts ?? collect())->isNotEmpty() ? $featuredProducts : ($products ?? collect());
    $p1 = $pool->first();
    $p2 = $pool->skip(1)->first() ?? $p1;
    $p3 = $pool->skip(2)->first() ?? $p2;
    $heroImg = !empty($heroCfg['image_url']) ? $heroCfg['image_url'] : ($p1?->images?->first()?->url ?? $vendor->banner_url ?? asset('images/product-placeholder.svg'));
    $img2 = $p2?->images?->first()?->url ?? $heroImg;
    $img3 = $p3?->images?->first()?->url ?? $img2;
@endphp

@if($heroTpl === 'fullbleed-runway' || $heroTpl === 'interior-room-canvas')
    {{-- FULL-BLEED RUNWAY / ROOM-SCALE INTERIOR HERO --}}
    <section class="relative w-full min-h-[76vh] sm:min-h-[82vh] flex items-end overflow-hidden" data-k71-reveal>
        <div class="absolute inset-0">
            <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover" data-k71-parallax>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-black/15"></div>
        </div>
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 text-white">
            <div class="max-w-2xl space-y-4">
                <p class="k71-eyebrow text-white/80">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-6xl lg:text-7xl leading-[1.01] text-white">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base text-white/85 max-w-xl leading-relaxed">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-3 flex flex-wrap items-center gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn border border-white/50 text-white hover:bg-white hover:text-black">View Catalogue</a>
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'white-plinth-gallery')
    {{-- PURE GALLERY PLINTH HERO (Atelier Blanc, Minimal Sole, Jewelle) --}}
    <section class="w-full py-12 sm:py-20" style="border-bottom: 1px solid var(--t-border);">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
            <div class="lg:col-span-5 space-y-6" data-k71-reveal>
                <p class="k71-eyebrow" style="color: var(--t-muted);">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.06]">{{ $heroHeadline }}</h1>
                <div class="w-12 h-px" style="background-color: var(--t-text);"></div>
                @if($heroSubheadline)
                    <p class="text-sm leading-relaxed" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-outline">{{ $heroCtaLabel }} @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])</a>
                </div>
            </div>
            <div class="lg:col-span-7 flex justify-center" data-k71-reveal>
                <div class="w-full max-w-xl p-5 sm:p-8" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                    <div class="aspect-[4/5] sm:aspect-[5/4] overflow-hidden" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                    </div>
                    <div class="pt-4 flex items-center justify-between text-[11px] font-mono uppercase tracking-wider" style="color: var(--t-muted);">
                        <span>01 / Featured Study</span>
                        <span>{{ $storeName }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'obsidian-monolith' || $heroTpl === 'horology-macro-stage')
    {{-- OBSIDIAN MONOLITH / HOROLOGY MACRO STAGE (Obsidian, Chronograph, Pixel Foundry, Audio Atelier) --}}
    <section class="w-full py-12 sm:py-20" style="border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-5 space-y-6" data-k71-reveal>
                <div class="inline-flex items-center gap-2 text-[11px] font-mono uppercase tracking-widest" style="color: var(--t-accent);">
                    <span class="w-6 h-px" style="background-color: var(--t-accent);"></span>
                    <span>{{ $heroEyebrow }}</span>
                </div>
                <h1 class="k71-heading text-4xl sm:text-6xl leading-[1.02]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base leading-relaxed" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Specifications &amp; Archive</a>
                </div>
            </div>
            <div class="lg:col-span-7" data-k71-reveal>
                <div class="p-3 sm:p-4" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                    <div class="aspect-[16/10] overflow-hidden" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                    </div>
                    @if($p1)
                        <div class="pt-4 px-2 flex items-center justify-between gap-4 text-xs" style="border-top: 1px solid var(--t-border); margin-top: 0.75rem;">
                            <span class="font-mono uppercase tracking-wider truncate" style="color: var(--t-muted);">{{ $p1->sku ?: 'EDITION 01' }} · {{ $p1->title }}</span>
                            <span class="font-bold shrink-0" style="color: var(--t-accent);">₹{{ number_format(($p1->base_price ?? 0) / 100, 0) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'sneaker-vault-stage' || $heroTpl === 'street-poster-split' || $heroTpl === 'drop-launch-hero')
    {{-- SNEAKER VAULT / STREET POSTER / DROP LAUNCH HERO --}}
    <section class="w-full py-10 sm:py-16" style="border-bottom: 2px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-end">
            <div class="lg:col-span-7 space-y-5" data-k71-reveal>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="px-3 py-1 text-[11px] font-mono font-bold uppercase tracking-widest" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">{{ $heroReleaseLabel ?: $heroEyebrow }}</span>
                    @if($heroDeadline)
                        <span class="px-3 py-1 text-xs font-mono font-bold" style="border: 1px solid var(--t-border);" data-k71-countdown="{{ $heroDeadline }}">Loading...</span>
                    @endif
                </div>
                <h1 class="k71-heading text-5xl sm:text-7xl lg:text-8xl uppercase leading-[0.92]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base max-w-xl" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }} @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Browse All Drops</a>
                </div>
            </div>
            <div class="lg:col-span-5" data-k71-reveal>
                <div class="k71-card-shell overflow-hidden">
                    <div class="aspect-square overflow-hidden" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'diagonal-performance')
    {{-- DIAGONAL PERFORMANCE ATHLETIC HERO (Motion Lab, Velocity) --}}
    <section class="w-full py-12 sm:py-20 k71-diagonal-stage" style="background-color: var(--t-surface-alt);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <div class="lg:col-span-6 space-y-5" data-k71-reveal style="color: {{ !empty($manifest['palette']['is_dark']) ? '#f1f5f9' : '#ffffff' }};">
                <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-6xl uppercase leading-[0.98] text-white">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base text-white/80 max-w-lg">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn border border-white/40 text-white hover:bg-white hover:text-black">Shop Specs</a>
                </div>
            </div>
            <div class="lg:col-span-6" data-k71-reveal>
                <div class="aspect-[4/3] overflow-hidden rounded-lg shadow-2xl">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'bento-modular-hero')
    {{-- MODULAR BENTO HERO (Neo Commerce, Market Studio) --}}
    <section class="w-full py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-5">
            <div class="lg:col-span-7 p-7 sm:p-10 k71-surface flex flex-col justify-between gap-6" data-k71-reveal>
                <div class="space-y-4">
                    <span class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</span>
                    <h1 class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.03]">{{ $heroHeadline }}</h1>
                    @if($heroSubheadline)
                        <p class="text-sm sm:text-base max-w-lg" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                    @endif
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Explore All</a>
                </div>
            </div>
            <div class="lg:col-span-5 grid grid-cols-2 gap-4" data-k71-reveal>
                <div class="col-span-2 aspect-[16/10] overflow-hidden k71-surface">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
                <div class="aspect-square overflow-hidden k71-surface">
                    <img src="{{ $img2 }}" alt="{{ $storeName }} highlight" class="w-full h-full object-cover">
                </div>
                <div class="aspect-square overflow-hidden k71-surface">
                    <img src="{{ $img3 }}" alt="{{ $storeName }} highlight" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'color-block-duet')
    {{-- COLOR-BLOCKED DUET HERO (Pop Form, Color Theory) --}}
    <section class="w-full py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 overflow-hidden k71-card-shell" style="border-radius: var(--t-radius);">
                <div class="lg:col-span-6 p-8 sm:p-12 flex flex-col justify-between gap-6" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                    <p class="k71-eyebrow opacity-85">{{ $heroEyebrow }}</p>
                    <div class="space-y-4">
                        <h1 class="k71-heading text-4xl sm:text-6xl leading-[1.0]">{{ $heroHeadline }}</h1>
                        @if($heroSubheadline)
                            <p class="text-sm sm:text-base opacity-90 max-w-md">{{ $heroSubheadline }}</p>
                        @endif
                    </div>
                    <div>
                        <a href="{{ $heroCtaUrl }}" class="k71-btn" style="background-color: var(--t-accent-contrast); color: var(--t-accent);">{{ $heroCtaLabel }}</a>
                    </div>
                </div>
                <div class="lg:col-span-6 min-h-[280px] aspect-[4/3] lg:aspect-auto overflow-hidden" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'compact-catalog-banner')
    {{-- COMPACT UTILITY CATALOG BANNER (Circuit, Pure Science, Fresh Market) --}}
    <section class="w-full py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="k71-surface p-6 sm:p-8 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                <div class="md:col-span-8 space-y-2.5">
                    <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
                    <h1 class="k71-heading text-2xl sm:text-4xl">{{ $heroHeadline }}</h1>
                    @if($heroSubheadline)
                        <p class="text-xs sm:text-sm max-w-2xl" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                    @endif
                </div>
                <div class="md:col-span-4 flex md:justify-end gap-2.5">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Filter All</a>
                </div>
            </div>
        </div>
    </section>

@elseif($heroTpl === 'tech-product-centerpiece')
    {{-- TECH PRODUCT CENTERPIECE HERO (Future Form, Mono Tech) --}}
    <section class="w-full py-12 sm:py-18 text-center">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" data-k71-reveal>
            <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
            <h1 class="k71-heading text-4xl sm:text-6xl leading-[1.04] max-w-3xl mx-auto">{{ $heroHeadline }}</h1>
            @if($heroSubheadline)
                <p class="text-sm sm:text-base max-w-2xl mx-auto" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
            @endif
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Compare Lineup</a>
            </div>
            <div class="mt-8 overflow-hidden k71-surface aspect-[16/9] max-h-[500px]">
                <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
            </div>
        </div>
    </section>

@elseif($heroTpl === 'magazine-cover-spread' || $heroTpl === 'asymmetric-campaign')
    {{-- ASYMMETRIC CAMPAIGN / MAGAZINE SPREAD HERO (Maison Elite, The Curator, Sartorial, Sole Archive, Artifact) --}}
    <section class="w-full py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5 lg:pt-8 space-y-5" data-k71-reveal>
                <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-6xl leading-[1.02]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base leading-relaxed" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-3 flex flex-wrap gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">View Archive</a>
                </div>
            </div>
            <div class="lg:col-span-7 grid grid-cols-12 gap-4 items-end">
                <div class="col-span-8 aspect-[3/4] overflow-hidden k71-surface" data-k71-reveal>
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover" data-k71-parallax>
                </div>
                <div class="col-span-4 aspect-[3/4] overflow-hidden k71-surface mb-8" data-k71-reveal>
                    <img src="{{ $img2 }}" alt="{{ $storeName }} editorial" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

@else
    {{-- HERITAGE FRAMED / APOTHECARY SPLIT / CULINARY TABLE / LIFESTYLE ARCH / STUDIO OFFSET --}}
    <section class="w-full py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-6 space-y-5 order-2 lg:order-1" data-k71-reveal>
                <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.05]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base leading-relaxed max-w-lg" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Browse Collection</a>
                </div>
            </div>
            <div class="lg:col-span-6 order-1 lg:order-2" data-k71-reveal>
                <div class="overflow-hidden k71-surface aspect-[4/3] sm:aspect-[5/4] {{ $heroTpl === 'lifestyle-editorial-arch' ? 'rounded-t-[10rem]' : '' }}">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>
@endif
