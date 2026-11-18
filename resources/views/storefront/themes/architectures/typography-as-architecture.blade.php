@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Typography as architectural form — products curated inside bold editorial negative space.')), 140);
    $lead = $allProducts->first();
    $second = $allProducts->get(1);
    $leadImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? $fallbackArt);
    $secondImg = $second?->images?->first()?->url ?? $fallbackArt;
@endphp

{{-- 1. TYPOGRAPHY-AS-ARCHITECTURE FIRST VIEWPORT (Monumental Edge-to-Edge Type + Inset Plates) --}}
<section data-k71-hero-stage class="w-full pt-6 sm:pt-10 pb-14" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 lg:px-12">
        <div class="flex items-center justify-between text-[11px] font-mono uppercase tracking-[0.24em] pb-4 mb-6" style="border-bottom: 1px solid var(--t-border); color: var(--t-muted);">
            <span data-k71-hero-meta>{{ $manifest['name'] }} · ISSUE 01</span>
            <span data-k71-hero-meta>{{ $allProducts->count() }} CATALOGUED PIECES</span>
        </div>

        {{-- Oversized Monumental Display Wordmark --}}
        <h1 data-k71-hero-title class="k71-heading text-[13vw] sm:text-[10.5vw] lg:text-[8.6vw] uppercase leading-[0.84] tracking-[-0.04em] break-words">
            {{ $heroHeadline }}
        </h1>

        {{-- Architectural Diptych Anchored Directly Under/Beside Oversized Typography --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-10 mt-8 sm:mt-12 items-end">
            <div class="lg:col-span-4 space-y-6 order-2 lg:order-1">
                <p data-k71-hero-meta class="text-sm sm:text-base leading-relaxed" style="color: var(--t-muted);">
                    {{ $heroSub }}
                </p>
                <div data-k71-hero-meta class="flex flex-wrap gap-3">
                    <a href="{{ $storeCollectionUrl }}" data-k71-magnetic class="k71-btn k71-btn-primary">
                        <span>Explore Catalogue</span>
                        @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                    </a>
                </div>
                @if(($vendorCategories ?? collect())->isNotEmpty())
                    <div class="pt-5 flex flex-wrap gap-x-5 gap-y-2 text-xs uppercase tracking-[0.18em]" style="border-top: 1px solid var(--t-border);">
                        @foreach($vendorCategories->take(5) as $cat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-65 font-semibold">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:col-span-5 order-1 lg:order-2">
                @if($lead)
                    <a href="{{ $prodUrl($lead) }}" data-k71-hero-media class="block group">
                        <div class="aspect-[4/5] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $leadImg }}" alt="{{ $lead->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                        <div class="pt-3 flex items-baseline justify-between gap-3 text-xs">
                            <span class="font-bold uppercase tracking-wider truncate">01 · {{ $lead->title }}</span>
                            <span class="font-mono font-bold shrink-0">₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</span>
                        </div>
                    </a>
                @endif
            </div>

            <div class="lg:col-span-3 hidden lg:block order-3" data-k71-parallax="26">
                @if($second)
                    <a href="{{ $prodUrl($second) }}" class="block group">
                        <div class="aspect-[3/4] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $secondImg }}" alt="{{ $second->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </div>
                        <div class="pt-3 text-xs">
                            <p class="font-mono text-[10px]" style="color: var(--t-muted);">02 · FEATURED</p>
                            <p class="font-semibold truncate mt-0.5">{{ $second->title }}</p>
                            <p class="font-mono font-bold mt-0.5">₹{{ number_format(($second->base_price ?? 0) / 100, 0) }}</p>
                        </div>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>

{{-- 2. ASYMMETRIC TYPOGRAPHIC CATALOGUE --}}
<section class="w-full py-12 sm:py-16">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 lg:px-12">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-10">
            <h2 class="k71-heading text-3xl sm:text-5xl uppercase tracking-tight">Index / Works</h2>
            <a href="{{ $storeCollectionUrl }}" class="text-xs font-mono uppercase tracking-[0.2em] underline underline-offset-4">All Pieces &rarr;</a>
        </div>

        <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-y-12 gap-x-8">
            @foreach($allProducts->take(10) as $idx => $product)
                @php
                    $span = ($idx % 3 === 0) ? 'lg:col-span-6' : 'lg:col-span-3';
                    $aspect = ($idx % 3 === 0) ? 'aspect-[16/10]' : 'aspect-[3/4]';
                    $pImg = $product->images?->first()?->url ?? $fallbackArt;
                    $pLink = $prodUrl($product);
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item class="{{ $span }} flex flex-col justify-between group">
                    <div>
                        <a href="{{ $pLink }}" class="block {{ $aspect }} overflow-hidden mb-3.5" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                        </a>
                        <div class="flex items-baseline justify-between gap-3">
                            <h3 class="k71-heading text-lg sm:text-xl leading-snug"><a href="{{ $pLink }}">{{ $product->title }}</a></h3>
                            <span class="font-mono text-sm font-bold shrink-0">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</span>
                        </div>
                    </div>
                    <div class="pt-3 mt-3 flex items-center justify-between text-xs" style="border-top: 1px solid var(--t-border);">
                        <span class="font-mono uppercase text-[10px]" style="color: var(--t-muted);">NO. {{ str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) }}</span>
                        @if($hasVariants)
                            <a href="{{ $pLink }}" class="font-bold uppercase tracking-wider" style="color: var(--t-accent);">Select &rarr;</a>
                        @else
                            <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="font-bold uppercase tracking-wider cursor-pointer" style="color: var(--t-accent);">+ Bag</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
