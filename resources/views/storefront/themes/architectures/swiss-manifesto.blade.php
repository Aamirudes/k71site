@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Strict typographic hierarchy, disciplined Swiss grid proportions, and immediate pricing clarity.')), 150);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $sv = $manifest['layout']['structural_variant'] ?? 'A';
    $lead = $allProducts->first();
    $heroImg = trim((string) ($heroCfg['image_url'] ?? '')) ?: ($lead?->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.jpg'));
@endphp

@if($sv === 'C')
{{-- VARIANT C (Mono Tech): COMPACT INDUSTRIAL BLUEPRINT HEADER + IMMEDIATE 4-COL NUMBERED MATRIX --}}
<section data-k71-hero-stage class="w-full py-6 sm:py-8" style="border-bottom: 1px solid var(--t-border); background-color: var(--t-surface);">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <p class="text-[10px] font-mono uppercase tracking-[0.24em] mb-1" style="color: var(--t-accent);">[ INDUSTRIAL INDEX // {{ strtoupper($manifest['name']) }} ]</p>
            <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-5xl leading-none">{{ $heroHeadline }}</h1>
        </div>
        <div class="flex items-center gap-4 text-xs font-mono">
            <span style="color: var(--t-muted);">UNITS: {{ $allProducts->count() }}</span>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Full Blueprint &rarr;</a>
        </div>
    </div>
</section>
@elseif($sv === 'B')
{{-- VARIANT B (Sartorial): INVERTED LEFT-IMAGE / RIGHT-TAILORING DOSSIER --}}
<section data-k71-hero-stage class="w-full" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 grid grid-cols-1 lg:grid-cols-12">
        <div class="lg:col-span-5 py-8 lg:pr-8 lg:border-r" style="border-color: var(--t-border);">
            <div data-k71-hero-media class="aspect-[4/4] overflow-hidden" style="background-color: var(--t-surface-alt);">
                <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
            </div>
        </div>
        <div class="lg:col-span-7 py-10 lg:pl-10 flex flex-col justify-between gap-8">
            <div class="space-y-4">
                <p class="text-[11px] font-mono uppercase tracking-[0.22em]" style="color: var(--t-accent);">TAILORING LEDGER · {{ strtoupper($manifest['name']) }}</p>
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl leading-[0.96]">{{ $heroHeadline }}</h1>
                <p class="text-sm max-w-lg leading-relaxed" style="color: var(--t-muted);">{{ $heroSub }}</p>
            </div>
            <div><a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Commission / Browse &rarr;</a></div>
        </div>
    </div>
</section>
@elseif($sv === 'D')
{{-- VARIANT D (Pure Science): CLINICAL 6/6 FORMULATION SPEC TABLE HERO --}}
<section data-k71-hero-stage class="w-full py-10" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <div class="lg:col-span-6 space-y-5">
            <span class="px-2.5 py-1 text-[10px] font-mono uppercase font-bold" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">CLINICAL FORMULATION INDEX</span>
            <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl leading-[0.98]">{{ $heroHeadline }}</h1>
            <dl class="divide-y text-xs font-mono" style="border-top: 1px solid var(--t-border); border-bottom: 1px solid var(--t-border);">
                <div class="py-2 flex justify-between"><dt style="color: var(--t-muted);">ACTIVE FORMULATIONS</dt><dd class="font-bold">{{ $allProducts->count() }}</dd></div>
                <div class="py-2 flex justify-between"><dt style="color: var(--t-muted);">CATEGORY LINES</dt><dd class="font-bold">{{ ($vendorCategories ?? collect())->count() }}</dd></div>
            </dl>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Inspect Formulations</a>
        </div>
        <div class="lg:col-span-6">
            <div data-k71-hero-media class="aspect-[16/10] overflow-hidden" style="border: 1px solid var(--t-border);">
                <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
            </div>
        </div>
    </div>
</section>
@else
{{-- VARIANT A (The Curator): SWISS INTERNATIONAL TYPOGRAPHIC HEADER GRID --}}
<section data-k71-hero-stage class="w-full" style="border-bottom: 1px solid var(--t-border);">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-10">
        <div data-k71-hairline class="grid grid-cols-2 sm:grid-cols-4 py-3 text-[11px] font-mono uppercase tracking-[0.16em]" style="border-bottom: 1px solid var(--t-border); color: var(--t-muted);">
            <span>[ 00 / INDEX ]</span>
            <span>{{ $manifest['name'] }}</span>
            <span class="hidden sm:block">ITEMS: {{ str_pad((string) $allProducts->count(), 2, '0', STR_PAD_LEFT) }}</span>
            <span class="text-right">{{ $vendor->city ?: 'K71 NETWORK' }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12">
            <div class="lg:col-span-8 py-10 sm:py-14 lg:pr-10 flex flex-col justify-between gap-8" style="border-bottom: 1px solid var(--t-border);">
                <h1 data-k71-hero-title class="k71-heading text-4xl sm:text-6xl xl:text-7xl leading-[0.94] tracking-tight">
                    {{ $heroHeadline }}
                </h1>
                <div data-k71-hero-meta class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-6" style="border-top: 1px solid var(--t-border);">
                    <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">{{ $heroSub }}</p>
                    <div class="flex sm:justify-end items-end gap-3">
                        <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">
                            <span>Open Full Index</span>
                            @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-4 h-4'])
                        </a>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-4 py-8 lg:py-10 lg:pl-8 lg:border-l" style="border-color: var(--t-border);">
                <div data-k71-hero-media class="aspect-[4/5] w-full overflow-hidden mb-3" style="background-color: var(--t-surface-alt);">
                    <img src="{{ $heroImg }}" alt="{{ $heroHeadline }}" class="w-full h-full object-cover">
                </div>
                @if($lead)
                    <div class="flex items-center justify-between text-xs font-mono">
                        <span class="truncate pr-2">FIG.01 — {{ $lead->title }}</span>
                        <span class="font-bold shrink-0">₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endif

{{-- 2. STICKY LEFT SWISS INDEX + RIGHT HAIRLINE-BORDERED NUMBERED PRODUCT MATRIX --}}
<section class="w-full">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-8 lg:px-10">
        <div class="grid grid-cols-1 lg:grid-cols-12">
            <aside class="lg:col-span-3 py-8 lg:py-12 lg:pr-8 lg:border-r" style="border-color: var(--t-border);">
                <div class="lg:sticky lg:top-24 space-y-6">
                    <div>
                        <p class="text-[10px] font-mono uppercase tracking-[0.2em] mb-3" style="color: var(--t-muted);">[ 01 / DEPARTMENTS ]</p>
                        <ul class="space-y-0" style="border-top: 1px solid var(--t-border);">
                            <li style="border-bottom: 1px solid var(--t-border);">
                                <a href="{{ $storeCollectionUrl }}" class="flex items-center justify-between py-2.5 text-xs font-mono font-bold" style="color: var(--t-accent);">
                                    <span>ALL ITEMS</span>
                                    <span>{{ str_pad((string) $allProducts->count(), 2, '0', STR_PAD_LEFT) }}</span>
                                </a>
                            </li>
                            @foreach(($vendorCategories ?? collect())->take(8) as $cIdx => $cat)
                                <li style="border-bottom: 1px solid var(--t-border);">
                                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                                       class="flex items-center justify-between py-2.5 text-xs font-mono hover:opacity-65 transition">
                                        <span class="truncate pr-2">{{ str_pad((string)($cIdx + 1), 2, '0', STR_PAD_LEFT) }} / {{ strtoupper($cat->name) }}</span>
                                        <span style="color: var(--t-muted);">{{ $cat->products_count ?? '' }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    @if($vendor->settings?->return_window_days)
                        <div class="p-4 text-xs font-mono space-y-1" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                            <p class="font-bold">SPECIFICATION GUARANTEE</p>
                            <p style="color: var(--t-muted);">{{ $vendor->settings->return_window_days }}-Day Verified Return Policy</p>
                        </div>
                    @endif
                </div>
            </aside>

            <div class="lg:col-span-9 py-8 lg:py-12 lg:pl-8">
                <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3" style="border-left: 1px solid var(--t-border); border-bottom: 1px solid var(--t-border);">
                    @foreach($allProducts->take(12) as $idx => $product)
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
                        <article data-k71-card-item class="k71-product-card k71-swiss-cell p-5 flex flex-col justify-between gap-4 group">
                            <div>
                                <div class="flex items-center justify-between text-[10px] font-mono mb-3" style="color: var(--t-muted);">
                                    <span>[ {{ str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) }} ]</span>
                                    <span class="truncate max-w-[130px] uppercase">{{ $product->category?->name ?? 'STD' }}</span>
                                </div>
                                <div class="relative mb-4">
                                    <a href="{{ $pLink }}" class="k71-card-media block aspect-[4/3] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                                        <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                    </a>
                                    @include('storefront.themes.partials._card_media_controls')
                                </div>
                                <h3 class="k71-heading text-base font-bold leading-snug">
                                    <a href="{{ $pLink }}" class="hover:opacity-70">{{ $product->title }}</a>
                                </h3>
                            </div>

                            <div class="pt-3 flex items-center justify-between gap-2" style="border-top: 1px solid var(--t-border);">
                                <span class="text-lg font-mono font-extrabold tracking-tight">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</span>
                                @if($hasVariants)
                                    <a href="{{ $pLink }}" class="px-3 py-1.5 text-[10px] font-mono uppercase font-bold tracking-wider" style="border: 1px solid var(--t-text);">Select</a>
                                @else
                                    <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" class="px-3 py-1.5 text-[10px] font-mono uppercase font-bold tracking-wider cursor-pointer" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">+ Bag</button>
                                    </form>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
