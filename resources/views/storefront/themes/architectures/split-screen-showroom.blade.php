@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Tactile spatial living, honest natural materials, and calm architectural proportion.')), 150);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
    $splitGallery = $allProducts->take(3);
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($splitGallery->first()?->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
@endphp

@if($sv === 'B')
{{-- VARIANT B (Casa Moderna): INVERTED LEFT MONUMENTAL PHOTOGRAPHY / RIGHT STICKY MANIFESTO --}}
<section data-k71-hero-stage class="w-full grid grid-cols-1 lg:grid-cols-12" style="border-bottom: 1px solid var(--t-border);">
    <div class="lg:col-span-7 flex flex-col" style="border-right: 1px solid var(--t-border);">
        <div data-k71-hero-media class="aspect-[16/10] lg:min-h-[84vh] overflow-hidden" style="background-color: var(--t-surface-alt);">
            <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
        </div>
    </div>
    <div class="lg:col-span-5 lg:sticky lg:top-0 lg:h-screen flex flex-col justify-between p-8 sm:p-14" style="background-color: var(--t-surface);">
        <div class="space-y-5">
            <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $manifest['name'] }} · Architectural Interior</p>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl leading-[1.0]">{{ $heroHeadline }}</h1>
            <p class="text-sm leading-relaxed" style="color: var(--t-muted);">{{ $heroSub }}</p>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Enter Showroom &rarr;</a>
        </div>
        <div class="pt-6 text-xs font-mono" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">{{ $allProducts->count() }} Spatial Pieces Available</div>
    </div>
</section>
@elseif($sv === 'C')
{{-- VARIANT C (Botanical Lab): 3-PANEL APOTHECARY TRIPTYCH HERO --}}
<section data-k71-hero-stage class="w-full py-10 sm:py-14" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 space-y-8">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <p class="k71-eyebrow mb-1" style="color: var(--t-accent);">{{ $manifest['name'] }} · Formulation Study</p>
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl">{{ $heroHeadline }}</h1>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Shop Rituals</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($splitGallery as $tIdx => $tItem)
                @php $tImg = $tItem->images?->first()?->url ?? $heroImg; @endphp
                <a href="{{ $prodUrl($tItem) }}" data-k71-hero-media class="group block overflow-hidden" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
                    <div class="aspect-[4/5] overflow-hidden"><img src="{{ $tImg }}" alt="{{ $tItem->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700"></div>
                    <div class="p-4 flex justify-between items-center text-xs"><span class="font-bold truncate pr-2">{{ $tItem->title }}</span><span class="font-mono shrink-0">₹{{ number_format(($tItem->base_price ?? 0) / 100, 0) }}</span></div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@elseif($sv === 'D')
{{-- VARIANT D (Field & Found): 70/30 EXPEDITION WIDESCREEN HORIZON BANNER --}}
<section data-k71-hero-stage class="w-full py-8 sm:py-12" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        <div class="lg:col-span-8 relative min-h-[58vh] overflow-hidden" style="border-radius: var(--t-radius);">
            <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-media class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-transparent flex items-end p-8 text-white">
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl text-white">{{ $heroHeadline }}</h1>
            </div>
        </div>
        <div class="lg:col-span-4 p-7 flex flex-col justify-between gap-6" style="background-color: var(--t-surface); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
            <div class="space-y-4">
                <p class="text-[11px] font-mono uppercase tracking-[0.2em]" style="color: var(--t-accent);">{{ $manifest['name'] }} // Field Spec</p>
                <p class="text-sm leading-relaxed" style="color: var(--t-muted);">{{ $heroSub }}</p>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary w-full">Explore Field Gear</a>
        </div>
    </div>
</section>
@else
{{-- VARIANT A (Modest Luxe): TRUE 50/50 STICKY SPLIT-SCREEN ARCHITECTURAL SHOWROOM --}}
<section data-k71-hero-stage class="w-full grid grid-cols-1 lg:grid-cols-2" style="border-bottom: 1px solid var(--t-border);">
    {{-- Left Sticky Brand Manifesto & Department Directory --}}
    <div class="lg:sticky lg:top-0 lg:h-screen flex flex-col justify-between p-6 sm:p-12 lg:p-16" style="background-color: var(--t-surface); border-right: 1px solid var(--t-border);">
        <div class="space-y-6 pt-4">
            <p data-k71-hero-meta class="k71-eyebrow" style="color: var(--t-accent);">{{ $manifest['name'] }} · Spatial Showroom</p>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-5xl xl:text-6xl leading-[1.02]">
                {{ $heroHeadline }}
            </h1>
            <p data-k71-hero-meta class="text-sm sm:text-base leading-relaxed max-w-md" style="color: var(--t-muted);">
                {{ $heroSub }}
            </p>
            <div data-k71-hero-meta class="flex flex-wrap gap-3 pt-2">
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">
                    <span>Explore All Rooms</span>
                    @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                </a>
            </div>
        </div>

        <div data-k71-hero-meta class="pt-10 mt-10 space-y-3" style="border-top: 1px solid var(--t-border);">
            <p class="text-[10px] uppercase tracking-[0.22em]" style="color: var(--t-muted);">Material &amp; Room Index</p>
            <div class="flex flex-wrap gap-2">
                @foreach(($vendorCategories ?? collect())->take(6) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-3.5 py-1.5 text-xs font-medium hover:opacity-75 transition" style="border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Right Monumental Stacked Photography Column --}}
    <div class="flex flex-col">
        <div data-k71-hero-media class="relative aspect-[4/3] lg:aspect-auto lg:min-h-[75vh] overflow-hidden" style="background-color: var(--t-surface-alt);">
            <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
        </div>
        @foreach($splitGallery->skip(1) as $sIdx => $sItem)
            @php $sImg = $sItem->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'); @endphp
            <a href="{{ $prodUrl($sItem) }}" data-k71-reveal class="relative block aspect-[16/10] overflow-hidden group" style="border-top: 1px solid var(--t-border); background-color: var(--t-surface-alt);">
                <img src="{{ $sImg }}" alt="{{ $sItem->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                <div class="absolute inset-x-0 bottom-0 p-5 sm:p-7 bg-gradient-to-t from-black/75 via-black/30 to-transparent text-white flex items-end justify-between gap-4">
                    <div>
                        <p class="text-[10px] uppercase tracking-[0.22em] text-white/75">Study 0{{ $sIdx + 1 }}</p>
                        <p class="k71-heading text-xl sm:text-2xl text-white">{{ $sItem->title }}</p>
                    </div>
                    <span class="text-sm font-semibold shrink-0">₹{{ number_format(($sItem->base_price ?? 0) / 100, 0) }}</span>
                </div>
            </a>
        @endforeach
    </div>
</section>
@endif

{{-- 2. WIDE 16:10 SPATIAL SHOWROOM PRODUCT GALLERY --}}
<section class="w-full py-14 sm:py-20">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
            <div>
                <p class="k71-eyebrow mb-1" style="color: var(--t-accent);">Spatial Collection</p>
                <h2 class="k71-heading text-3xl sm:text-4xl">Crafted Pieces</h2>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Full Showroom</a>
        </div>

        <div data-k71-stagger-grid class="grid grid-cols-1 md:grid-cols-2 gap-8 sm:gap-10">
            @foreach($allProducts->take(8) as $product)
                @php
                    $pImg = $product->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                    $pLink = $prodUrl($product);
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item class="group flex flex-col gap-4">
                    <a href="{{ $pLink }}" class="block aspect-[16/10] w-full overflow-hidden" style="background-color: var(--t-surface-alt); border-radius: var(--t-radius);">
                        <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                    </a>
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-[11px] uppercase tracking-[0.18em] mb-1" style="color: var(--t-muted);">{{ $product->category?->name ?? 'Collection' }}</p>
                            <h3 class="k71-heading text-xl sm:text-2xl"><a href="{{ $pLink }}" class="hover:opacity-75">{{ $product->title }}</a></h3>
                        </div>
                        <div class="text-right shrink-0 space-y-2">
                            <p class="text-base sm:text-lg font-bold">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</p>
                            @if($hasVariants)
                                <a href="{{ $pLink }}" class="text-xs font-semibold underline underline-offset-4" style="color: var(--t-accent);">Configure</a>
                            @else
                                <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="text-xs font-semibold underline underline-offset-4 cursor-pointer" style="color: var(--t-accent);">+ Add to Bag</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
