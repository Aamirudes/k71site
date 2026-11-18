@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'An architectural exhibition of objects designed with restraint and clarity.')), 150);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
    $lead = $allProducts->first();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
@endphp

@if($sv === 'B')
{{-- VARIANT B (Sole Archive): ZERO-HERO IMMEDIATE 2-COLUMN WIDE ARCHIVAL SPECIMEN MATRIX --}}
<section data-k71-hero-stage class="w-full px-6 sm:px-10 lg:px-14 py-8 sm:py-12">
    <div class="flex flex-wrap items-baseline justify-between gap-4 pb-5 mb-8" style="border-bottom: 1px solid var(--t-border);">
        <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-4xl">{{ $heroHeadline }} — Archive Index</h1>
        <span data-k71-hero-meta class="text-xs font-mono uppercase tracking-widest" style="color: var(--t-muted);">{{ $allProducts->count() }} Catalogued Editions</span>
    </div>
    <div data-k71-stagger-grid class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($allProducts->take(8) as $aIdx => $item)
            @php $aImg = $item->images?->first()?->url ?? $heroImg; @endphp
            <article data-k71-card-item class="group p-5" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                <div class="flex justify-between text-[10px] font-mono uppercase mb-3" style="color: var(--t-muted);">
                    <span>ARCHIVE // 0{{ $aIdx + 1 }}</span>
                    <span>{{ $item->category?->name ?? 'EDITION' }}</span>
                </div>
                <a href="{{ $prodUrl($item) }}" class="block aspect-[16/10] overflow-hidden mb-4" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $aImg }}" alt="{{ $item->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                </a>
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="k71-heading text-lg truncate"><a href="{{ $prodUrl($item) }}">{{ $item->title }}</a></h2>
                    <span class="font-mono text-sm font-bold shrink-0">₹{{ number_format(($item->base_price ?? 0) / 100, 0) }}</span>
                </div>
            </article>
        @endforeach
    </div>
</section>
@elseif($sv === 'C')
{{-- VARIANT C (Artifact): FULL-WIDTH MONUMENTAL SCULPTURE TOP BANNER ABOVE GALLERY --}}
<section data-k71-hero-stage class="w-full">
    <div class="relative w-full h-[64vh] overflow-hidden" style="background-color: var(--t-surface-alt); border-bottom: 1px solid var(--t-border);">
        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-media class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/20 to-transparent flex items-end p-8 sm:p-14 text-white">
            <div class="max-w-2xl space-y-2">
                <p class="text-[11px] font-mono uppercase tracking-[0.25em] text-white/75">{{ $manifest['name'] }} · Exhibition Hall</p>
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl text-white">{{ $heroHeadline }}</h1>
            </div>
        </div>
    </div>
</section>
@elseif($sv === 'D')
{{-- VARIANT D (Heritage House): FORMAL DOUBLE-BORDERED CREST LEDGER HEADER --}}
<section data-k71-hero-stage class="w-full px-6 sm:px-10 lg:px-14 pt-10 pb-8">
    <div class="p-8 sm:p-12 text-center space-y-4" style="border: 3px double var(--t-border); background-color: var(--t-surface);">
        <p data-k71-hero-meta class="text-[11px] font-mono uppercase tracking-[0.28em]" style="color: var(--t-accent);">House Registry · Est. {{ date('Y') }}</p>
        <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl">{{ $heroHeadline }}</h1>
        <p data-k71-hero-meta class="text-sm max-w-xl mx-auto" style="color: var(--t-muted);">{{ $heroSub }}</p>
        <div class="pt-2"><a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">View House Catalogue</a></div>
    </div>
</section>
@else
{{-- VARIANT A (Atelier Blanc): MUSEUM EXHIBITION PLINTH HERO (Pairs with Persistent Left Gallery Rail) --}}
<section data-k71-hero-stage class="w-full px-6 sm:px-10 lg:px-16 pt-10 sm:pt-14 pb-16">
    <div class="max-w-5xl mx-auto">
        <div class="flex flex-wrap items-baseline justify-between gap-4 pb-6 mb-10" style="border-bottom: 1px solid var(--t-border);">
            <span data-k71-hero-meta class="text-[11px] font-mono uppercase tracking-[0.22em]" style="color: var(--t-muted);">
                Gallery Plate 00 · {{ $manifest['name'] }}
            </span>
            <span data-k71-hero-meta class="text-[11px] font-mono" style="color: var(--t-muted);">
                {{ $allProducts->count() }} Exhibited Works
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
            <div class="lg:col-span-5 space-y-6">
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-5xl xl:text-6xl leading-[1.03]">
                    {{ $heroHeadline }}
                </h1>
                <p data-k71-hero-meta class="text-sm leading-relaxed" style="color: var(--t-muted);">
                    {{ $heroSub }}
                </p>
                <div data-k71-hero-meta class="flex flex-wrap items-center gap-3 pt-2">
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">
                        <span>Enter Catalogue</span>
                        @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                    </a>
                </div>
            </div>

            <div class="lg:col-span-7">
                <div data-k71-hero-media data-k71-3d-card class="p-6 sm:p-10" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                    <div class="aspect-[4/3] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                        <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                    </div>
                    @if($lead)
                        <div class="pt-5 flex items-center justify-between gap-4 text-xs">
                            <span class="font-medium truncate">{{ $lead->title }}</span>
                            <a href="{{ $prodUrl($lead) }}" class="font-mono uppercase tracking-wider shrink-0 underline underline-offset-4" style="color: var(--t-accent);">
                                ₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }} · Inspect
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif

{{-- 2. UNFRAMED MUSEUM GALLERY WALL (Numbered Exhibition Plaques) --}}
@if($sv !== 'B')
<section class="w-full px-6 sm:px-10 lg:px-16 py-12 sm:py-16" style="border-top: 1px solid var(--t-border);">
    <div class="max-w-5xl mx-auto">
        <div class="flex items-end justify-between gap-4 mb-10">
            <div>
                <p class="text-[10px] font-mono uppercase tracking-[0.24em] mb-1" style="color: var(--t-muted);">Permanent Collection</p>
                <h2 class="k71-heading text-2xl sm:text-3xl">Curated Objects</h2>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="text-xs font-mono uppercase tracking-widest hover:opacity-70" style="color: var(--t-accent);">Index All &rarr;</a>
        </div>

        <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-12">
            @foreach($allProducts->take(12) as $pIdx => $product)
                @php
                    $pImg = $product->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                    $pLink = $prodUrl($product);
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item data-k71-3d-card class="group flex flex-col">
                    <div class="flex items-center justify-between text-[10px] font-mono uppercase tracking-[0.2em] mb-2.5" style="color: var(--t-muted);">
                        <span>NO. {{ str_pad((string)($pIdx + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="truncate max-w-[140px]">{{ $product->category?->name ?? 'Object' }}</span>
                    </div>
                    <a href="{{ $pLink }}" class="block p-4 mb-4 transition" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                        <div class="aspect-[4/5] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                    </a>
                    <h3 class="k71-heading text-base font-medium leading-snug mb-1">
                        <a href="{{ $pLink }}" class="hover:opacity-70">{{ $product->title }}</a>
                    </h3>
                    <div class="flex items-center justify-between gap-2 pt-2 mt-auto" style="border-top: 1px solid var(--t-border);">
                        <span class="text-sm font-mono font-semibold">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</span>
                        @if($hasVariants)
                            <a href="{{ $pLink }}" class="text-[11px] font-mono uppercase tracking-wider hover:opacity-70" style="color: var(--t-accent);">Options &rarr;</a>
                        @else
                            <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="text-[11px] font-mono uppercase tracking-wider hover:opacity-70 cursor-pointer" style="color: var(--t-accent);">+ Acquire</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
@endif
