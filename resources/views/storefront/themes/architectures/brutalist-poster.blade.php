@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'RAW GRAPHIC COMMERCE · LIMITED RELEASES · ZERO COMPROMISE.')), 120);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
    $lead = $allProducts->first();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
@endphp

@if($sv === 'C')
{{-- VARIANT C (The Drop): FULL-WIDTH TOP COUNTDOWN BUNKER BANNER --}}
<section data-k71-hero-stage class="w-full py-6 sm:py-10">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="k71-brutal-box relative overflow-hidden min-h-[68vh] flex flex-col justify-between p-6 sm:p-12" style="background-color: #09090b; color: #ffffff;">
            <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" data-k71-hero-media class="absolute inset-0 w-full h-full object-cover opacity-55">
            <div class="relative z-10 flex flex-wrap items-center justify-between gap-3">
                <span class="px-3 py-1 text-xs font-mono font-black uppercase" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">LIVE DROP BUNKER // {{ strtoupper($manifest['name']) }}</span>
                <span class="px-3 py-1 text-xs font-mono font-bold border border-white/40">{{ $allProducts->count() }} LIMITED LOTS</span>
            </div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-end justify-between gap-6 pt-20">
                <h1 data-k71-hero-title class="k71-heading text-5xl sm:text-7xl xl:text-8xl font-black uppercase leading-[0.86] text-white">{{ $heroHeadline }}</h1>
                <a href="{{ $storeCollectionUrl }}" class="k71-brutal-shadow px-8 py-4 text-xs font-black uppercase tracking-[0.2em] shrink-0" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Enter Drop &rarr;</a>
            </div>
        </div>
    </div>
</section>
@elseif($sv === 'B')
{{-- VARIANT B (Sneaker Vault): INVERTED LEFT-VISUAL / RIGHT-COLLECTOR VAULT SPEC STACK --}}
<section data-k71-hero-stage class="w-full py-6 sm:py-10">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="k71-brutal-box grid grid-cols-1 lg:grid-cols-12 overflow-hidden" style="background-color: var(--t-surface);">
            <div class="lg:col-span-7 relative lg:border-r-2" style="border-color: var(--t-text); background-color: #0c0d10;">
                <div data-k71-hero-media data-k71-3d-card class="aspect-[4/3] overflow-hidden">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
            </div>
            <div class="lg:col-span-5 p-6 sm:p-10 flex flex-col justify-between gap-6">
                <div class="space-y-4">
                    <span class="inline-block px-3 py-1 text-[10px] font-mono font-black uppercase" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">COLLECTOR VAULT // 01</span>
                    <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl uppercase font-black leading-[0.9]">{{ $heroHeadline }}</h1>
                    <p class="text-xs sm:text-sm" style="color: var(--t-muted);">{{ $heroSub }}</p>
                </div>
                <a href="{{ $storeCollectionUrl }}" class="k71-brutal-shadow px-6 py-3.5 text-xs font-black uppercase tracking-[0.2em] text-center" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Browse Vault &rarr;</a>
            </div>
        </div>
    </div>
</section>
@else
{{-- VARIANT A (Street Culture): RAW BRUTALIST POSTER HERO WITH THICK ARCHITECTURAL INK BORDERS --}}
<section data-k71-hero-stage class="w-full py-6 sm:py-10">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="k71-brutal-box grid grid-cols-1 lg:grid-cols-12 overflow-hidden" style="background-color: var(--t-surface);">
            <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-between gap-8 lg:border-r-2" style="border-color: var(--t-text);">
                <div class="space-y-4">
                    <div data-k71-hero-meta class="inline-flex items-center gap-2 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em]"
                         style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                        <span>DROP // {{ strtoupper($manifest['name']) }}</span>
                    </div>
                    <h1 data-k71-hero-title class="k71-heading text-5xl sm:text-7xl xl:text-8xl uppercase leading-[0.88] font-black tracking-tighter">
                        {{ $heroHeadline }}
                    </h1>
                    <p data-k71-hero-meta class="text-sm sm:text-base font-medium max-w-xl" style="color: var(--t-muted);">
                        {{ $heroSub }}
                    </p>
                </div>

                <div data-k71-hero-meta class="flex flex-wrap items-center gap-3 pt-4" style="border-top: 2px solid var(--t-text);">
                    <a href="{{ $storeCollectionUrl }}" data-k71-magnetic class="k71-brutal-shadow px-7 py-3.5 text-xs font-black uppercase tracking-[0.18em] inline-flex items-center gap-2"
                       style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                        <span>Shop All Drops</span>
                        @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                    </a>
                    @if(!empty($heroCfg['release_deadline']))
                        <span class="px-4 py-3 text-xs font-mono font-bold uppercase" style="border: 2px solid var(--t-text);" data-k71-countdown="{{ $heroCfg['release_deadline'] }}"></span>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-5 relative flex flex-col justify-between" style="background-color: var(--t-surface-alt);">
                <div data-k71-hero-media data-k71-3d-card class="aspect-square lg:aspect-auto lg:flex-1 overflow-hidden relative">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                    @if($lead)
                        <div class="absolute top-4 right-4 px-3.5 py-2 font-mono text-sm font-black uppercase"
                             style="background-color: var(--t-text); color: var(--t-bg);">
                            ₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}
                        </div>
                    @endif
                </div>
                @if($lead)
                    <a href="{{ $prodUrl($lead) }}" class="p-4 flex items-center justify-between text-xs font-black uppercase tracking-wider hover:opacity-80"
                       style="border-top: 2px solid var(--t-text); background-color: var(--t-surface);">
                        <span class="truncate pr-3">FEATURED: {{ $lead->title }}</span>
                        <span>INSPECT &rarr;</span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- 2. GSAP SCROLL-SCRUBBED OVERSIZED MARQUEE TICKER --}}
<section class="w-full overflow-hidden py-4 my-4" style="background-color: var(--t-text); color: var(--t-bg); border-top: 2px solid var(--t-text); border-bottom: 2px solid var(--t-text);">
    <div data-k71-marquee class="flex items-center gap-10 whitespace-nowrap w-max px-4">
        @for($i = 0; $i < 4; $i++)
            <span class="k71-heading text-2xl sm:text-4xl font-black uppercase tracking-tight">{{ $storeName }}</span>
            <span class="text-xl font-mono" style="color: var(--t-accent);">///</span>
            <span class="k71-heading text-2xl sm:text-4xl font-black uppercase tracking-tight">{{ $allProducts->count() }} LIVE PIECES</span>
            <span class="text-xl font-mono" style="color: var(--t-accent);">///</span>
            @foreach(($vendorCategories ?? collect())->take(3) as $mCat)
                <span class="k71-heading text-2xl sm:text-4xl font-black uppercase tracking-tight">{{ $mCat->name }}</span>
                <span class="text-xl font-mono" style="color: var(--t-accent);">///</span>
            @endforeach
        @endfor
    </div>
</section>

{{-- 3. BRUTALIST POSTER PRODUCT GRID WITH HARD OFFSET SHADOWS --}}
<section class="w-full py-10 sm:py-14">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
            <h2 class="k71-heading text-3xl sm:text-5xl font-black uppercase tracking-tight">Catalog // Release Wall</h2>
            <a href="{{ $storeCollectionUrl }}" class="px-5 py-2.5 text-xs font-black uppercase tracking-widest" style="border: 2px solid var(--t-text);">View All &rarr;</a>
        </div>

        <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($allProducts->take(12) as $pIdx => $product)
                @php
                    $pImg = $product->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg');
                    $pLink = $prodUrl($product);
                    $variants = $product->variants ?? collect();
                    $hasVariants = $variants->isNotEmpty();
                    $pricePaise = (int) ($product->base_price ?? 0);
                    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? 1);
                    $isSoldOut = $totalStock <= 0;
                    $firstVariant = $variants->first();
                    $primaryImg = $pImg;
                    $productUrl = $pLink;
                @endphp
                <article data-k71-card-item data-k71-3d-card class="k71-product-card k71-brutal-shadow flex flex-col justify-between overflow-hidden" style="background-color: var(--t-surface);">
                    <div>
                        <div class="px-3.5 py-2 flex items-center justify-between text-[10px] font-mono font-black uppercase" style="border-bottom: 2px solid var(--t-text);">
                            <span>LOT #{{ str_pad((string)($pIdx + 1), 3, '0', STR_PAD_LEFT) }}</span>
                            <span class="truncate max-w-[120px]">{{ $product->category?->name ?? 'DROP' }}</span>
                        </div>
                        <div class="relative">
                            <a href="{{ $pLink }}" class="k71-card-media block aspect-square overflow-hidden relative" style="background-color: var(--t-surface-alt);">
                                <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
                                <span class="absolute top-2.5 left-2.5 px-2.5 py-1 text-xs font-mono font-black"
                                      style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                                    ₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}
                                </span>
                            </a>
                            @include('storefront.themes.partials._card_media_controls')
                        </div>
                        <div class="p-4">
                            <h3 class="k71-heading text-lg font-black uppercase leading-tight line-clamp-2">
                                <a href="{{ $pLink }}">{{ $product->title }}</a>
                            </h3>
                        </div>
                    </div>

                    <div class="p-3.5 pt-0">
                        @if($hasVariants)
                            <a href="{{ $pLink }}" class="w-full py-2.5 text-xs font-black uppercase tracking-wider flex items-center justify-center"
                               style="border: 2px solid var(--t-text);">Select Size / Variant</a>
                        @else
                            <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="w-full py-2.5 text-xs font-black uppercase tracking-wider cursor-pointer"
                                        style="background-color: var(--t-text); color: var(--t-bg);">+ Add To Bag</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
