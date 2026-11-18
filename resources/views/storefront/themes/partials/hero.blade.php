@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $storeHomeUrl = $storeHomeUrl ?? (!empty($isCataloguePreview)
        ? route('vendor.themes.preview_frame', ['themeId' => $manifest['id'], 'page' => 'home'])
        : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '')));
    $storeCollectionUrl = $storeCollectionUrl ?? (!empty($isCataloguePreview)
        ? route('vendor.themes.preview_frame', ['themeId' => $manifest['id'], 'page' => 'collection'])
        : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : ''));
    $heroVariant = $manifest['layout']['hero_variant'] ?? 'split-editorial';
    $heroHeight = $manifest['layout']['hero_height'] ?? 'min-h-[70vh]';
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = $heroCfg['headline'] ?: $storeName;
    $heroSubheadline = $heroCfg['subheadline'] ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? '')), 180);
    $heroEyebrow = $heroCfg['eyebrow'] ?: ($vendor->city ? $vendor->city . ' · Official Store' : 'Official Storefront');
    $heroCtaLabel = $heroCfg['cta_label'] ?: 'Explore Collection';
    $heroCtaUrl = ($heroCfg['cta_url'] ?? '#collection') ?: '#collection';
    $heroSecondLabel = $heroCfg['secondary_cta_label'] ?? '';
    $heroSecondUrl = $heroCfg['secondary_cta_url'] ?? $storeCollectionUrl;
    $heroReleaseLabel = $heroCfg['release_label'] ?? '';
    $heroDeadline = $heroCfg['release_deadline'] ?? '';

    $firstProduct = ($featuredProducts ?? collect())->first() ?? ($products ?? collect())->first();
    $fallbackHeroImg = $firstProduct?->images?->first()?->url ?? $vendor->banner_url ?? asset('images/product-placeholder.svg');
    $heroImg = !empty($heroCfg['image_url']) ? $heroCfg['image_url'] : $fallbackHeroImg;
    $secondProduct = ($featuredProducts ?? collect())->skip(1)->first() ?? ($products ?? collect())->skip(1)->first();
    $secondImg = $secondProduct?->images?->first()?->url ?? $heroImg;
@endphp

@if($heroVariant === 'fullscreen-campaign' || $heroVariant === 'immersive-bleed')
    {{-- FULLSCREEN CAMPAIGN / IMMERSIVE BLEED HERO --}}
    <section class="relative w-full {{ $heroHeight }} flex items-end overflow-hidden" data-k71-reveal>
        <div class="absolute inset-0">
            <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover" data-k71-parallax>
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/35 to-black/15"></div>
        </div>
        <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-20 text-white">
            <div class="max-w-2xl space-y-4">
                <p class="k71-eyebrow text-white/80">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-6xl lg:text-7xl leading-[1.03] text-white">{{ $heroHeadline }}</h1>
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

@elseif($heroVariant === 'layered-glass' || $heroVariant === 'spatial-orbital' || $heroVariant === 'signature-71')
    {{-- LAYERED GLASS / SPATIAL ORBITAL / SIGNATURE 71 HERO --}}
    <section class="relative w-full {{ $heroHeight }} flex items-center overflow-hidden py-12 sm:py-16">
        <div class="absolute inset-0 k71-hero-ambient pointer-events-none" aria-hidden="true"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">
            <div class="lg:col-span-6 space-y-5" data-k71-reveal>
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-semibold k71-glass">
                    <span class="w-2 h-2 rounded-full" style="background-color: var(--t-accent);"></span>
                    <span class="k71-eyebrow">{{ $heroEyebrow }}</span>
                </span>
                <h1 class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.04]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base leading-relaxed max-w-lg" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Browse All</a>
                </div>
            </div>
            <div class="lg:col-span-6 relative" data-k71-reveal>
                <div class="relative rounded-3xl overflow-hidden k71-glass p-2.5 sm:p-3.5" data-k71-tilt>
                    <div class="aspect-[4/3] sm:aspect-[16/11] w-full overflow-hidden rounded-2xl">
                        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                    </div>
                    @if($firstProduct)
                        <div class="mt-3 p-3.5 rounded-2xl flex items-center justify-between gap-3" style="background-color: var(--t-surface);">
                            <div class="min-w-0">
                                <p class="text-[11px] uppercase tracking-wider" style="color: var(--t-muted);">Featured</p>
                                <p class="text-sm font-semibold truncate">{{ $firstProduct->title }}</p>
                            </div>
                            <span class="text-sm font-bold shrink-0" style="color: var(--t-accent);">₹{{ number_format($firstProduct->base_price / 100, 0) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@elseif($heroVariant === 'typographic-bold' || $heroVariant === 'drop-release')
    {{-- BOLD TYPOGRAPHIC / DROP RELEASE HERO --}}
    <section class="w-full py-10 sm:py-16" style="border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-end">
            <div class="lg:col-span-7 space-y-5" data-k71-reveal>
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="k71-eyebrow px-2.5 py-1 rounded" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">{{ $heroReleaseLabel ?: $heroEyebrow }}</span>
                    @if($heroDeadline)
                        <span class="text-xs font-mono font-bold px-2.5 py-1 rounded" style="border: 1px solid var(--t-border);" data-k71-countdown="{{ $heroDeadline }}">Loading...</span>
                    @endif
                </div>
                <h1 class="k71-heading text-5xl sm:text-7xl lg:text-8xl uppercase leading-[0.94]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base max-w-xl" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Full Lineup</a>
                </div>
            </div>
            <div class="lg:col-span-5" data-k71-reveal>
                <div class="aspect-[4/4] sm:aspect-[4/5] w-full overflow-hidden k71-surface">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

@elseif($heroVariant === 'asymmetric-broken')
    {{-- ASYMMETRIC BROKEN-GRID HERO (Avant Atelier, Street Form, Object Studio, Kinetic Grid) --}}
    <section class="w-full py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">
            <div class="lg:col-span-5 lg:pt-10 space-y-5" data-k71-reveal>
                <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.02]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base leading-relaxed" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Explore Archive</a>
                </div>
            </div>
            <div class="lg:col-span-7 grid grid-cols-12 gap-4 items-end">
                <div class="col-span-8 overflow-hidden k71-surface aspect-[3/4]" data-k71-reveal>
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
                <div class="col-span-4 overflow-hidden k71-surface aspect-[3/4] mb-6" data-k71-reveal>
                    <img src="{{ $secondImg }}" alt="{{ $storeName }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

@elseif($heroVariant === 'color-blocked')
    {{-- COLOUR-BLOCKED HERO (Chromatic Studio, Pop Studio) --}}
    <section class="w-full py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 overflow-hidden k71-graphic-card" style="border-radius: var(--t-radius);">
                <div class="lg:col-span-6 p-8 sm:p-12 flex flex-col justify-between gap-6" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                    <p class="k71-eyebrow opacity-85">{{ $heroEyebrow }}</p>
                    <div class="space-y-4">
                        <h1 class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.02]">{{ $heroHeadline }}</h1>
                        @if($heroSubheadline)
                            <p class="text-sm sm:text-base opacity-90 max-w-md">{{ $heroSubheadline }}</p>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-3 pt-2">
                        <a href="{{ $heroCtaUrl }}" class="k71-btn" style="background-color: var(--t-accent-contrast); color: var(--t-accent);">{{ $heroCtaLabel }}</a>
                    </div>
                </div>
                <div class="lg:col-span-6 aspect-[4/3] lg:aspect-auto min-h-[280px] overflow-hidden" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </section>

@elseif($heroVariant === 'product-centered')
    {{-- PRODUCT-CENTERED MINIMAL HERO (Sculpted Minimal, Apple-Inspired Minimal, Tech Precision, Essentials Club, Conversion Atelier) --}}
    <section class="w-full py-10 sm:py-16 text-center">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6" data-k71-reveal>
            <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
            <h1 class="k71-heading text-4xl sm:text-6xl leading-[1.06] max-w-3xl mx-auto">{{ $heroHeadline }}</h1>
            @if($heroSubheadline)
                <p class="text-sm sm:text-base max-w-2xl mx-auto leading-relaxed" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
            @endif
            <div class="flex flex-wrap items-center justify-center gap-3 pt-1">
                <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Browse Catalogue</a>
            </div>
            <div class="mt-8 overflow-hidden k71-surface aspect-[16/9] max-h-[520px] mx-auto">
                <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
            </div>
        </div>
    </section>

@else
    {{-- SPLIT EDITORIAL / LIFESTYLE ROOM HERO (Default refined two-column composition) --}}
    <section class="w-full py-8 sm:py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-6 space-y-5 order-2 lg:order-1" data-k71-reveal>
                <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $heroEyebrow }}</p>
                <h1 class="k71-heading text-4xl sm:text-5xl lg:text-6xl leading-[1.05]">{{ $heroHeadline }}</h1>
                @if($heroSubheadline)
                    <p class="text-sm sm:text-base leading-relaxed max-w-lg" style="color: var(--t-muted);">{{ $heroSubheadline }}</p>
                @endif
                <div class="pt-2 flex flex-wrap items-center gap-3">
                    <a href="{{ $heroCtaUrl }}" class="k71-btn k71-btn-primary">{{ $heroCtaLabel }}</a>
                    @if($heroSecondLabel)
                        <a href="{{ $heroSecondUrl }}" class="k71-btn k71-btn-outline">{{ $heroSecondLabel }}</a>
                    @else
                        <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Shop All</a>
                    @endif
                </div>
            </div>
            <div class="lg:col-span-6 order-1 lg:order-2" data-k71-reveal>
                <div class="overflow-hidden k71-surface aspect-[4/3] sm:aspect-[5/4]">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover" data-k71-parallax>
                </div>
            </div>
        </div>
    </section>
@endif
