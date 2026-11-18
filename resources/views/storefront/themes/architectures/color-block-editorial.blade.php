@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $bands = $manifest['layout']['band_colors'] ?? ['#fffdf5', '#1d4ed8', '#facc15'];
    $bandLight = $bands[0] ?? '#faf7f2';
    $bandBold = $bands[1] ?? '#1d4ed8';
    $bandAccent = $bands[2] ?? '#facc15';
    $hexClean = ltrim((string) $bandAccent, '#');
    $accentLum = strlen($hexClean) === 6
        ? (0.299 * hexdec(substr($hexClean, 0, 2)) + 0.587 * hexdec(substr($hexClean, 2, 2)) + 0.114 * hexdec(substr($hexClean, 4, 2)))
        : 200;
    $bandAccentIsDark = $accentLum < 128;
    $bandAccentInk = $bandAccentIsDark ? '#ffffff' : '#111111';

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Deliberate chromatic art direction framing every product inside bold gallery fields.')), 140);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
    $lead = $allProducts->first();
    $featuredDuo = $allProducts->slice(1, 2)->values();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
@endphp

@if($sv === 'B')
{{-- VARIANT B (Sweet Atelier): CENTERED PATISSERIE COLOR-BLOCK POSTER --}}
<section data-k71-hero-stage class="w-full py-12 sm:py-16" style="background-color: {{ $bandBold }}; color: #ffffff;">
    <div class="max-w-4xl mx-auto px-5 text-center space-y-6">
        <span class="inline-block px-4 py-1 text-[11px] font-black uppercase tracking-[0.22em]" style="background-color: {{ $bandAccent }}; color: #111111;">{{ $manifest['name'] }}</span>
        <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl text-white">{{ $heroHeadline }}</h1>
        <div data-k71-hero-media class="max-w-2xl mx-auto p-4" style="background-color: {{ $bandLight }};">
            <div class="aspect-[16/10] overflow-hidden"><img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover"></div>
        </div>
        <a href="{{ $storeCollectionUrl }}" class="inline-block px-8 py-3.5 text-xs font-black uppercase tracking-[0.2em]" style="background-color: {{ $bandAccent }}; color: #111111;">Shop Collection &rarr;</a>
    </div>
</section>
@elseif($sv === 'D')
{{-- VARIANT D (Color Theory): INVERTED LEFT-PHOTO / RIGHT-TERRACOTTA COLOR FIELD --}}
<section data-k71-hero-stage class="w-full grid grid-cols-1 lg:grid-cols-12">
    <div class="lg:col-span-7 min-h-[60vh] overflow-hidden" style="background-color: {{ $bandLight }};">
        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-media class="w-full h-full object-cover">
    </div>
    <div class="lg:col-span-5 p-8 sm:p-14 flex flex-col justify-between gap-8" style="background-color: {{ $bandBold }}; color: #ffffff;">
        <div class="space-y-4">
            <span class="text-[11px] font-mono uppercase tracking-[0.25em] text-white/75">{{ $manifest['name'] }} · Study</span>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl text-white leading-[0.95]">{{ $heroHeadline }}</h1>
            <p class="text-sm text-white/85 leading-relaxed">{{ $heroSub }}</p>
        </div>
        <a href="{{ $storeCollectionUrl }}" class="px-7 py-3.5 text-xs font-black uppercase tracking-[0.2em] w-fit bg-white text-zinc-950">Explore Works &rarr;</a>
    </div>
</section>
@elseif($sv === 'C')
{{-- VARIANT C (Pop Form): 3-COLUMN VERTICAL CHROMATIC TRIPTYCH HERO --}}
<section data-k71-hero-stage class="w-full grid grid-cols-1 lg:grid-cols-3">
    <div class="p-8 sm:p-12 flex flex-col justify-between gap-6" style="background-color: {{ $bandBold }}; color: #ffffff;">
        <span class="text-xs font-black uppercase tracking-[0.22em]">{{ $manifest['name'] }}</span>
        <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-5xl text-white leading-[0.94]">{{ $heroHeadline }}</h1>
        <a href="{{ $storeCollectionUrl }}" class="px-6 py-3 text-xs font-black uppercase w-fit" style="background-color: {{ $bandAccent }}; color: #111111;">Shop All &rarr;</a>
    </div>
    <div class="min-h-[380px] overflow-hidden" style="background-color: {{ $bandLight }};">
        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-media class="w-full h-full object-cover">
    </div>
    <div class="p-8 sm:p-12 flex flex-col justify-between gap-6" style="background-color: {{ $bandAccent }}; color: #111111;">
        <span class="text-xs font-mono font-black uppercase">LIVE CATALOGUE // {{ $allProducts->count() }}</span>
        <p class="text-sm font-semibold leading-relaxed">{{ $heroSub }}</p>
        @if($lead)<a href="{{ $prodUrl($lead) }}" class="text-xs font-black uppercase underline underline-offset-4">Featured: {{ $lead->title }} · ₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</a>@endif
    </div>
</section>
@else
{{-- VARIANT A (Maison Gourmet): FULL-BLEED BOLD CHROMATIC ART-DIRECTION HERO --}}
<section data-k71-hero-stage class="w-full" style="background-color: {{ $bandBold }}; color: #ffffff;">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 py-12 sm:py-16 lg:py-20 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
        <div class="lg:col-span-6 space-y-6">
            <span data-k71-hero-meta class="inline-block px-3.5 py-1.5 text-[11px] font-black uppercase tracking-[0.22em]"
                  style="background-color: {{ $bandAccent }}; color: #111111;">
                {{ $manifest['name'] }} · Chromatic Edition
            </span>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl xl:text-7xl leading-[0.95] text-white">
                {{ $heroHeadline }}
            </h1>
            <p data-k71-hero-meta class="text-sm sm:text-base text-white/85 max-w-lg leading-relaxed">
                {{ $heroSub }}
            </p>
            <div data-k71-hero-meta class="flex flex-wrap gap-3 pt-2">
                <a href="{{ $storeCollectionUrl }}" data-k71-magnetic
                   class="px-7 py-3.5 text-xs font-black uppercase tracking-[0.18em] inline-flex items-center gap-2 transition hover:opacity-90"
                   style="background-color: {{ $bandAccent }}; color: #111111;">
                    <span>Explore Exhibition</span>
                    @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                </a>
            </div>
        </div>

        <div class="lg:col-span-6">
            <div data-k71-hero-media data-k71-3d-card class="p-4 sm:p-6" style="background-color: {{ $bandLight }}; color: #111111;">
                <div class="aspect-[4/3] overflow-hidden">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
                @if($lead)
                    <div class="pt-4 flex items-center justify-between gap-4">
                        <span class="k71-heading text-lg font-bold truncate">{{ $lead->title }}</span>
                        <a href="{{ $prodUrl($lead) }}" class="text-sm font-black shrink-0 underline underline-offset-4">₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- BAND 2: SECONDARY COLOR FIELD DEPARTMENT INDEX --}}
@if(($vendorCategories ?? collect())->isNotEmpty())
<section data-k71-reveal class="w-full py-8" style="background-color: {{ $bandAccent }}; color: {{ $bandAccentInk }};">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 flex flex-wrap items-center justify-between gap-4">
        <span class="text-xs font-black uppercase tracking-[0.22em]">Color-Coded Index</span>
        <div class="flex flex-wrap gap-2.5">
            @foreach($vendorCategories->take(6) as $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                   class="px-4 py-2 text-xs font-bold uppercase tracking-wider {{ $bandAccentIsDark ? 'bg-white text-zinc-950' : 'bg-black text-white' }} hover:opacity-80 transition">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- BAND 3: CONTRASTING FEATURED DIPTYCH --}}
@if($featuredDuo->isNotEmpty())
<section class="w-full py-14 sm:py-18" style="background-color: {{ $bandLight }}; color: #111111;">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            @foreach($featuredDuo as $dIdx => $dItem)
                @php $dImg = $dItem->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'); @endphp
                <a href="{{ $prodUrl($dItem) }}" data-k71-reveal class="block p-5 sm:p-7 group transition hover:-translate-y-1"
                   style="background-color: {{ $dIdx === 0 ? $bandBold : '#111111' }}; color: #ffffff;">
                    <div class="aspect-[16/10] overflow-hidden mb-5">
                        <img src="{{ $dImg }}" alt="{{ $dItem->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </div>
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/70 mb-1">Highlight 0{{ $dIdx + 1 }}</p>
                            <h2 class="k71-heading text-2xl sm:text-3xl text-white">{{ $dItem->title }}</h2>
                        </div>
                        <span class="px-3.5 py-1.5 text-sm font-black shrink-0" style="background-color: {{ $bandAccent }}; color: {{ $bandAccentInk }};">
                            ₹{{ number_format(($dItem->base_price ?? 0) / 100, 0) }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- BAND 4: GALLERY PRODUCT GRID --}}
<section class="w-full py-12 sm:py-16">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <h2 class="k71-heading text-3xl sm:text-4xl">Complete Collection</h2>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Shop All</a>
        </div>
        <div data-k71-stagger-grid class="grid grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-7">
            @foreach($allProducts->take(12) as $product)
                <div data-k71-card-item>
                    @include('storefront.themes.partials.card', ['product' => $product])
                </div>
            @endforeach
        </div>
    </div>
</section>
