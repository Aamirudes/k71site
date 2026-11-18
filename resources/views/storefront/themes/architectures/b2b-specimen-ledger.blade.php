@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]));
    $prodUrl = fn ($p) => $frameBase ? ($frameBase . $frameGlue . 'page=product&product=' . urlencode($p->slug)) : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]);

    $fallbackArt = asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'tech-hardware') . '.svg');
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $heroCfg = $themeConfig['hero'] ?? [];
    $heroHeadline = trim((string) ($heroCfg['headline'] ?? '')) ?: $storeName;
    $heroSub = trim((string) ($heroCfg['subheadline'] ?? '')) ?: \Illuminate\Support\Str::limit(strip_tags((string) ($vendor->description ?? 'Structured technical catalogue, SKU transparency, and direct procurement workflow.')), 140);
@endphp

{{-- 1. TECHNICAL PROCUREMENT & SPECIFICATION HEADER --}}
<section data-k71-hero-stage class="w-full py-8 sm:py-10" style="background-color: var(--t-surface); border-bottom: 2px solid var(--t-border);">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 grid grid-cols-1 lg:grid-cols-12 gap-6 items-end">
        <div class="lg:col-span-8 space-y-2.5">
            <div data-k71-hero-meta class="inline-flex items-center gap-2 text-[11px] font-mono uppercase tracking-[0.2em]" style="color: var(--t-accent);">
                <span>SPECIFICATION LEDGER // {{ strtoupper($manifest['name']) }}</span>
            </div>
            <h1 data-k71-hero-title class="k71-heading text-3xl sm:text-5xl tracking-tight">{{ $heroHeadline }}</h1>
            <p data-k71-hero-meta class="text-xs sm:text-sm max-w-2xl" style="color: var(--t-muted);">{{ $heroSub }}</p>
        </div>
        <div data-k71-hero-meta class="lg:col-span-4 grid grid-cols-3 gap-3 text-center">
            <div class="p-3" style="border: 1px solid var(--t-border); background-color: var(--t-bg);">
                <p class="font-mono text-xl font-extrabold">{{ $allProducts->count() }}</p>
                <p class="text-[10px] font-mono uppercase" style="color: var(--t-muted);">SKUs</p>
            </div>
            <div class="p-3" style="border: 1px solid var(--t-border); background-color: var(--t-bg);">
                <p class="font-mono text-xl font-extrabold">{{ ($vendorCategories ?? collect())->count() }}</p>
                <p class="text-[10px] font-mono uppercase" style="color: var(--t-muted);">Lines</p>
            </div>
            <div class="p-3" style="border: 1px solid var(--t-border); background-color: var(--t-bg);">
                <p class="font-mono text-xl font-extrabold">{{ $vendor->settings?->return_window_days ?: 7 }}d</p>
                <p class="text-[10px] font-mono uppercase" style="color: var(--t-muted);">Policy</p>
            </div>
        </div>
    </div>
</section>

{{-- 2. INTERACTIVE HORIZONTAL SPECIFICATION ROWS (TRADE / B2B / TECHNICAL CATALOGUE) --}}
<section class="w-full py-8 sm:py-12">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar">
                <a href="{{ $storeCollectionUrl }}" class="px-3.5 py-1.5 text-xs font-mono font-bold uppercase" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">All SKUs</a>
                @foreach(($vendorCategories ?? collect())->take(6) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-3.5 py-1.5 text-xs font-mono uppercase hover:opacity-75" style="border: 1px solid var(--t-border);">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
            <a href="{{ $storeCollectionUrl }}" class="text-xs font-mono font-bold uppercase hover:opacity-75" style="color: var(--t-accent);">Full Filter View &rarr;</a>
        </div>

        {{-- Table Header (Desktop) --}}
        <div class="hidden lg:grid lg:grid-cols-12 gap-4 px-5 py-2.5 text-[10px] font-mono uppercase tracking-[0.18em]"
             style="background-color: var(--t-surface); border: 1px solid var(--t-border); color: var(--t-muted);">
            <div class="col-span-1">Ref</div>
            <div class="col-span-5">Product &amp; Material Specification</div>
            <div class="col-span-2">Category</div>
            <div class="col-span-2 text-right">Unit Price</div>
            <div class="col-span-2 text-right">Action</div>
        </div>

        <div data-k71-stagger-grid class="divide-y" style="border: 1px solid var(--t-border); border-color: var(--t-border);">
            @foreach($allProducts->take(14) as $idx => $product)
                @php
                    $pImg = $product->images?->first()?->url ?? $fallbackArt;
                    $pLink = $prodUrl($product);
                    $hasVariants = ($product->variants ?? collect())->isNotEmpty();
                @endphp
                <article data-k71-card-item class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center p-4 sm:px-5 hover:opacity-95 transition" style="background-color: var(--t-surface); border-color: var(--t-border);">
                    <div class="hidden lg:block lg:col-span-1 font-mono text-xs" style="color: var(--t-muted);">
                        {{ $product->sku ?: ('SKU-' . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT)) }}
                    </div>

                    <div class="lg:col-span-5 flex items-center gap-4 min-w-0">
                        <a href="{{ $pLink }}" class="w-16 h-16 sm:w-20 sm:h-20 shrink-0 overflow-hidden" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                            <img src="{{ $pImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
                        </a>
                        <div class="min-w-0">
                            <p class="lg:hidden font-mono text-[10px] mb-0.5" style="color: var(--t-muted);">{{ $product->sku ?: ('SKU-' . str_pad((string)($idx + 1), 3, '0', STR_PAD_LEFT)) }}</p>
                            <h2 class="k71-heading text-sm sm:text-base font-bold leading-snug truncate">
                                <a href="{{ $pLink }}" class="hover:opacity-75">{{ $product->title }}</a>
                            </h2>
                            <p class="text-xs truncate mt-0.5" style="color: var(--t-muted);">
                                {{ $product->material ?: \Illuminate\Support\Str::limit(strip_tags((string) ($product->description ?? 'Verified catalog item')), 70) }}
                            </p>
                        </div>
                    </div>

                    <div class="hidden lg:block lg:col-span-2 text-xs font-mono uppercase truncate" style="color: var(--t-muted);">
                        {{ $product->category?->name ?? 'Standard' }}
                    </div>

                    <div class="lg:col-span-2 flex lg:block items-center justify-between lg:text-right">
                        <span class="lg:hidden text-xs font-mono uppercase" style="color: var(--t-muted);">Unit Price</span>
                        <span class="font-mono text-base sm:text-lg font-extrabold">₹{{ number_format(($product->base_price ?? 0) / 100, 0) }}</span>
                    </div>

                    <div class="lg:col-span-2 flex items-center justify-end gap-2">
                        @if($hasVariants)
                            <a href="{{ $pLink }}" class="w-full lg:w-auto px-4 py-2 text-xs font-mono font-bold uppercase text-center" style="border: 1px solid var(--t-text);">Configure</a>
                        @else
                            <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form class="w-full lg:w-auto flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="number" name="quantity" value="1" min="1" max="99" aria-label="Quantity"
                                       class="w-14 min-h-[36px] px-2 text-center font-mono text-xs" style="background-color: var(--t-bg); border: 1px solid var(--t-border);">
                                <button type="submit" class="flex-1 lg:flex-none px-4 min-h-[36px] text-xs font-mono font-bold uppercase cursor-pointer"
                                        style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Add</button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
