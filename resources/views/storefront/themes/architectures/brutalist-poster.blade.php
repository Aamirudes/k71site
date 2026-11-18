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
    $lead = $allProducts->first();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg'));
@endphp

{{-- 1. RAW BRUTALIST POSTER HERO WITH THICK ARCHITECTURAL INK BORDERS --}}
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
                    $pImg = $product->images?->first()?->url ?? asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg');
                    $pLink = $prodUrl($product);
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item data-k71-3d-card class="k71-brutal-shadow flex flex-col justify-between overflow-hidden" style="background-color: var(--t-surface);">
                    <div>
                        <div class="px-3.5 py-2 flex items-center justify-between text-[10px] font-mono font-black uppercase" style="border-bottom: 2px solid var(--t-text);">
                            <span>LOT #{{ str_pad((string)($pIdx + 1), 3, '0', STR_PAD_LEFT) }}</span>
                            <span class="truncate max-w-[120px]">{{ $product->category?->name ?? 'DROP' }}</span>
                        </div>
                        <a href="{{ $pLink }}" class="block aspect-square overflow-hidden relative" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
                            <span class="absolute bottom-2.5 left-2.5 px-2.5 py-1 text-sm font-mono font-black"
                                  style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                                ₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}
                            </span>
                        </a>
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
