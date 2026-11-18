@extends('storefront.themes.layout')

@section('theme_content')
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $storeHomeUrl = $storeHomeUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=home')
        : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '')));
    $storeCollectionUrl = $storeCollectionUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=collection')
        : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : ''));

    $pdpTpl = $manifest['layout']['pdp_template'] ?? 'luxury-lookbook';
    $images = ($product->images ?? collect())->map(fn ($img) => $img->url)->filter()->values();
    if ($images->isEmpty()) {
        $images = collect([asset('images/product-placeholder.svg')]);
    }
    $variants = $product->variants ?? collect();
    $hasMultipleVariants = $variants->count() > 1 || ($variants->count() === 1 && strcasecmp((string) $variants->first()?->sku, 'Default') !== 0 && !empty($variants->first()?->size ?: $variants->first()?->color));
    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? 0);
    $isSoldOut = $totalStock <= 0;
    $firstAvailableVariant = $variants->first(fn ($v) => (int) ($v->stock ?? 0) > 0) ?? $variants->first();

    $pricePaise = (int) ($product->base_price ?? 0);
    $comparePaise = (int) ($product->compare_at_price ?? 0);
    $hasDiscount = $comparePaise > $pricePaise && $pricePaise > 0;
    $discountPct = $hasDiscount ? (int) round((($comparePaise - $pricePaise) / $comparePaise) * 100) : 0;

    $reviews = $productReviews ?? collect();
    $avgRating = $reviews->isNotEmpty() ? round((float) $reviews->avg('rating'), 1) : 0;

    $returnDays = $product->return_window_days !== null ? (int) $product->return_window_days : (int) ($vendor->settings?->return_window_days ?: 0);
    $isReturnable = $product->is_returnable ?? ($returnDays > 0);
    $processingDays = (int) ($vendor->settings?->processing_days ?: 0);

    $specs = [];
    if (!empty($product->brand)) { $specs['Brand / Maker'] = $product->brand; }
    if (!empty($product->sku)) { $specs['Reference / SKU'] = $product->sku; }
    if (!empty($product->material)) { $specs['Material / Composition'] = $product->material; }
    if (!empty($product->gender)) { $specs['Fit / Classification'] = ucfirst((string) $product->gender); }
    if (!empty($product->weight_grams)) { $specs['Weight / Pack'] = $product->weight_grams . ' g'; }
    if (is_array($product->custom_fields ?? null)) {
        foreach ($product->custom_fields as $cfKey => $cfVal) {
            if (is_scalar($cfVal) && trim((string) $cfVal) !== '') {
                $specs[ucwords(str_replace('_', ' ', (string) $cfKey))] = trim((string) $cfVal);
            }
        }
    }

    $isLookbookStacked = in_array($pdpTpl, ['luxury-lookbook'], true);
    $isFurnitureWide = in_array($pdpTpl, ['furniture-architectural'], true);
    $isSpecHeavy = in_array($pdpTpl, ['tech-comparison', 'horology-precision', 'specialist-modular'], true);
    $isRoutineAccordion = in_array($pdpTpl, ['skincare-routine', 'gourmet-culinary'], true);
    $isSneakerGrid = in_array($pdpTpl, ['sneaker-collector'], true);
@endphp

<section class="w-full py-6 sm:py-12" data-pdp-layout="{{ $pdpTpl }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

        {{-- Breadcrumb --}}
        <nav class="text-xs flex flex-wrap items-center gap-2" style="color: var(--t-muted);" aria-label="Breadcrumb">
            <a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Home</a>
            <span>/</span>
            <a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">Collection</a>
            @if($product->category)
                <span>/</span>
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($product->category->slug) }}" class="hover:opacity-75">{{ $product->category->name }}</a>
            @endif
            <span>/</span>
            <span class="truncate max-w-[220px]" style="color: var(--t-text);">{{ $product->title }}</span>
        </nav>

        {{-- Main PDP Architecture — Adapted per Theme Vertical --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">

            {{-- GALLERY STAGE --}}
            <div class="{{ ($isLookbookStacked || $isFurnitureWide) ? 'lg:col-span-7' : 'lg:col-span-6' }} space-y-4" data-k71-reveal>
                @if($isLookbookStacked && $images->count() > 1)
                    {{-- Luxury Lookbook Vertical Editorial Stack (Desktop) / Swipeable Main (Mobile) --}}
                    <div class="space-y-4">
                        @foreach($images->take(4) as $idx => $imgUrl)
                            <div class="relative overflow-hidden k71-surface aspect-[3/4]" style="background-color: var(--t-surface-alt);">
                                <button type="button" data-k71-lightbox-src="{{ $imgUrl }}" data-k71-lightbox-alt="{{ $product->title }}" class="block w-full h-full p-0 border-0 bg-transparent cursor-zoom-in">
                                    <img src="{{ $imgUrl }}" alt="{{ $product->title }} look {{ $idx + 1 }}" class="w-full h-full object-cover">
                                </button>
                                <span class="absolute bottom-3 right-3 px-2.5 py-1 text-[10px] font-mono bg-black/70 text-white rounded">0{{ $idx + 1 }} / 0{{ min(4, $images->count()) }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- Interactive Stage + Zoom Lightbox + Thumbnail Strip --}}
                    <div class="relative overflow-hidden k71-surface {{ $isFurnitureWide ? 'aspect-[16/10]' : ($isSneakerGrid ? 'aspect-square' : 'aspect-[4/5] sm:aspect-[4/4]') }}" style="background-color: var(--t-surface-alt);">
                        <button type="button" id="k71-pdp-main-trigger"
                                data-k71-lightbox-src="{{ $images->first() }}"
                                data-k71-lightbox-alt="{{ $product->title }}"
                                class="block w-full h-full cursor-zoom-in p-0 border-0 bg-transparent"
                                aria-label="Zoom product image">
                            <img id="k71-pdp-main-img" src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                        </button>
                        <span class="pointer-events-none absolute bottom-3 right-3 px-2.5 py-1.5 rounded-full text-[11px] font-semibold bg-black/70 text-white inline-flex items-center gap-1.5">
                            @include('storefront.themes.partials.icon', ['name' => 'zoom', 'class' => 'w-3.5 h-3.5'])
                            <span>Zoom</span>
                        </span>
                        @if($isSoldOut)
                            <span class="absolute top-3 left-3 px-3 py-1 rounded text-xs font-bold uppercase tracking-wider bg-black/80 text-white">Out of Stock</span>
                        @elseif($hasDiscount)
                            <span class="absolute top-3 left-3 px-3 py-1 rounded text-xs font-bold uppercase tracking-wider" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Save {{ $discountPct }}%</span>
                        @endif
                    </div>

                    @if($images->count() > 1)
                        <div class="grid grid-cols-4 sm:grid-cols-5 gap-2.5">
                            @foreach($images->take(8) as $idx => $imgUrl)
                                <button type="button"
                                        onclick="document.getElementById('k71-pdp-main-img').src=this.dataset.src; document.getElementById('k71-pdp-main-trigger').dataset.k71LightboxSrc=this.dataset.src;"
                                        data-src="{{ $imgUrl }}"
                                        class="aspect-square overflow-hidden rounded-lg p-0 cursor-pointer hover:opacity-85 transition"
                                        style="border: 1px solid var(--t-border); background-color: var(--t-surface-alt);"
                                        aria-label="View image {{ $idx + 1 }}">
                                    <img src="{{ $imgUrl }}" alt="{{ $product->title }} view {{ $idx + 1 }}" loading="lazy" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- PRODUCT DOSSIER & PURCHASE COLUMN --}}
            <div class="{{ ($isLookbookStacked || $isFurnitureWide) ? 'lg:col-span-5' : 'lg:col-span-6' }} k71-surface p-6 sm:p-8 space-y-6 lg:sticky lg:top-24" style="border-radius: var(--t-radius);" data-k71-reveal>
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        @if($product->category)
                            <p class="k71-eyebrow" style="color: var(--t-accent);">{{ $product->category->name }}</p>
                        @endif
                        @if(!empty($product->sku))
                            <span class="text-[11px] font-mono" style="color: var(--t-muted);">REF: {{ $product->sku }}</span>
                        @endif
                    </div>
                    <h1 class="k71-heading text-2xl sm:text-4xl leading-tight">{{ $product->title }}</h1>

                    @if($avgRating > 0)
                        <a href="#pdp-reviews" class="inline-flex items-center gap-1.5 text-xs font-semibold hover:opacity-75">
                            <span class="inline-flex items-center gap-1" style="color: var(--t-accent);">@include('storefront.themes.partials.icon', ['name' => 'star', 'class' => 'w-3.5 h-3.5']) {{ number_format($avgRating, 1) }}</span>
                            <span style="color: var(--t-muted);">({{ $reviews->count() }} verified {{ \Illuminate\Support\Str::plural('review', $reviews->count()) }})</span>
                        </a>
                    @endif
                </div>

                {{-- Real Price & Availability --}}
                <div class="pb-5 space-y-1.5" style="border-bottom: 1px solid var(--t-border);">
                    <div class="flex flex-wrap items-baseline gap-3">
                        <span id="k71-pdp-price" class="text-2xl sm:text-3xl font-extrabold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                        @if($hasDiscount)
                            <span class="text-sm line-through tabular-nums" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>
                            <span class="text-xs font-bold px-2 py-0.5 rounded" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">{{ $discountPct }}% OFF</span>
                        @endif
                    </div>
                    <p id="k71-pdp-stock-label" class="text-xs font-medium" style="color: {{ $isSoldOut ? '#dc2626' : 'var(--t-muted)' }};">
                        @if($isSoldOut)
                            Currently unavailable
                        @else
                            In stock ({{ $totalStock }} available) · Fulfilled by {{ $storeName }}
                        @endif
                    </p>
                </div>

                {{-- Real Purchase Form wired to K71 CartController::add --}}
                <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form data-k71-checkout-url="{{ route('checkout.index') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="variant_id" id="k71-selected-variant-id" value="{{ $firstAvailableVariant?->id }}">

                    @if($hasMultipleVariants)
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold uppercase tracking-wider">{{ $isSneakerGrid ? 'Select Size' : 'Select Option' }}</span>
                                <span id="k71-variant-status" class="font-mono" style="color: var(--t-muted);">
                                    {{ trim((string) ($firstAvailableVariant?->size ?: ($firstAvailableVariant?->color ?: ($firstAvailableVariant?->sku ?: 'Standard')))) }}
                                </span>
                            </div>
                            <div class="{{ $isSneakerGrid ? 'grid grid-cols-3 sm:grid-cols-4 gap-2' : 'flex flex-wrap gap-2' }}" role="radiogroup" aria-label="Product variants">
                                @foreach($variants as $v)
                                    @php
                                        $vStock = (int) ($v->stock ?? 0);
                                        $vDisabled = $vStock <= 0;
                                        $vPrice = (int) ($v->price_override ?: $pricePaise);
                                        $vNameParts = array_filter([trim((string) ($v->size ?? '')), trim((string) ($v->color ?? ''))]);
                                        $vLabel = !empty($vNameParts) ? implode(' / ', $vNameParts) : ($v->sku ?: ('Option #' . $v->id));
                                        $isSelected = $firstAvailableVariant && $firstAvailableVariant->id === $v->id;
                                    @endphp
                                    <button type="button"
                                            class="k71-variant-btn min-h-[44px] px-3.5 py-2 text-xs font-semibold rounded-lg transition {{ $vDisabled ? 'opacity-45 line-through cursor-not-allowed' : 'cursor-pointer' }}"
                                            data-variant-id="{{ $v->id }}"
                                            data-variant-price="₹{{ number_format($vPrice / 100, 0) }}"
                                            data-variant-label="{{ $vLabel }}"
                                            data-variant-stock="{{ $vStock }}"
                                            aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                                            {{ $vDisabled ? 'disabled aria-disabled=true' : '' }}
                                            style="{{ $isSelected ? 'background-color: var(--t-accent); color: var(--t-accent-contrast); border: 2px solid var(--t-accent);' : 'background-color: transparent; color: var(--t-text); border: 1px solid var(--t-border);' }}">
                                        {{ $vLabel }}@if($vDisabled) (Out)@endif
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <div class="flex items-center rounded-lg shrink-0" style="border: 1px solid var(--t-border);">
                            <button type="button" onclick="var i=document.getElementById('k71-qty'); i.value=Math.max(1, parseInt(i.value||1)-1);" class="min-w-[44px] min-h-[44px] font-bold" aria-label="Decrease quantity">&minus;</button>
                            <input type="number" id="k71-qty" name="quantity" value="1" min="1" max="{{ max(1, min(20, $totalStock)) }}"
                                   class="w-12 text-center text-sm font-semibold bg-transparent focus:outline-none" aria-label="Quantity">
                            <button type="button" onclick="var i=document.getElementById('k71-qty'); i.value=Math.min(parseInt(i.max||20), parseInt(i.value||1)+1);" class="min-w-[44px] min-h-[44px] font-bold" aria-label="Increase quantity">+</button>
                        </div>

                        @if(!empty($isCataloguePreview))
                            <button type="button" disabled class="k71-btn k71-btn-primary flex-1 opacity-75 cursor-not-allowed">
                                Preview Mode (Checkout Disabled)
                            </button>
                        @elseif($isSoldOut)
                            <button type="button" disabled class="k71-btn k71-btn-outline flex-1 opacity-60 cursor-not-allowed">
                                Out of Stock
                            </button>
                        @elseif($product->isPurchase())
                            <button type="submit" class="k71-btn k71-btn-primary flex-1">
                                @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                                <span>Add to Bag</span>
                            </button>
                        @else
                            <a href="{{ route('product.show', $product->slug) }}" class="k71-btn k71-btn-primary flex-1">View Full Listing</a>
                        @endif
                    </div>
                </form>

                {{-- Vertical-Tailored Information Architecture --}}
                @if($isRoutineAccordion)
                    {{-- Skincare / Gourmet Accordion Details --}}
                    <div class="pt-4 space-y-2" style="border-top: 1px solid var(--t-border);">
                        @if(!empty($product->description))
                            <details open class="p-3.5 rounded-lg" style="background-color: var(--t-surface-alt);">
                                <summary class="text-xs font-bold uppercase tracking-wider cursor-pointer">Description &amp; Usage Notes</summary>
                                <p class="text-sm leading-relaxed mt-2.5 whitespace-pre-line">{{ strip_tags((string) $product->description) }}</p>
                            </details>
                        @endif
                        @if(!empty($specs))
                            <details open class="p-3.5 rounded-lg" style="background-color: var(--t-surface-alt);">
                                <summary class="text-xs font-bold uppercase tracking-wider cursor-pointer">Ingredients, Pack &amp; Specifications</summary>
                                <dl class="divide-y text-xs mt-2.5" style="border-color: var(--t-border);">
                                    @foreach($specs as $specLabel => $specVal)
                                        <div class="py-1.5 flex items-center justify-between gap-4">
                                            <dt style="color: var(--t-muted);">{{ $specLabel }}</dt>
                                            <dd class="font-semibold text-right">{{ $specVal }}</dd>
                                        </div>
                                    @endforeach
                                </dl>
                            </details>
                        @endif
                    </div>
                @else
                    @if(!empty($product->description))
                        <div class="pt-5 space-y-2" style="border-top: 1px solid var(--t-border);">
                            <h2 class="k71-eyebrow" style="color: var(--t-muted);">{{ $isFurnitureWide ? 'Design & Craft Notes' : 'Product Overview' }}</h2>
                            <p class="text-sm leading-relaxed whitespace-pre-line">{{ strip_tags((string) $product->description) }}</p>
                        </div>
                    @endif

                    @if(!empty($specs))
                        <div class="pt-5 space-y-2.5" style="border-top: 1px solid var(--t-border);">
                            <h2 class="k71-eyebrow inline-flex items-center gap-1.5" style="color: var(--t-muted);">
                                @if($isFurnitureWide)
                                    @include('storefront.themes.partials.icon', ['name' => 'ruler', 'class' => 'w-3.5 h-3.5'])
                                    <span>Dimensions &amp; Materials</span>
                                @elseif($isSpecHeavy)
                                    <span>Technical Specifications</span>
                                @else
                                    <span>Details &amp; Attributes</span>
                                @endif
                            </h2>
                            <dl class="divide-y text-xs sm:text-sm" style="border-color: var(--t-border);">
                                @foreach($specs as $specLabel => $specVal)
                                    <div class="py-2 flex items-center justify-between gap-4">
                                        <dt style="color: var(--t-muted);">{{ $specLabel }}</dt>
                                        <dd class="font-semibold text-right">{{ $specVal }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    @endif
                @endif

                {{-- Authentic Delivery & Return Info --}}
                <div class="pt-5 grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">
                    <div class="p-3 rounded-lg flex items-start gap-2.5" style="background-color: var(--t-surface-alt);">
                        @include('storefront.themes.partials.icon', ['name' => 'truck', 'class' => 'w-4 h-4 shrink-0 mt-0.5'])
                        <div>
                            <p class="font-semibold" style="color: var(--t-text);">Dispatch</p>
                            <p class="mt-0.5">{{ $processingDays > 0 ? "Ships within {$processingDays} business days" : 'Verified order dispatch' }}</p>
                        </div>
                    </div>
                    <div class="p-3 rounded-lg flex items-start gap-2.5" style="background-color: var(--t-surface-alt);">
                        @include('storefront.themes.partials.icon', ['name' => 'refresh', 'class' => 'w-4 h-4 shrink-0 mt-0.5'])
                        <div>
                            <p class="font-semibold" style="color: var(--t-text);">Returns</p>
                            <p class="mt-0.5">{{ $isReturnable && $returnDays > 0 ? "{$returnDays}-day return window" : 'Per seller policy' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Verified Customer Reviews --}}
        @if($reviews->isNotEmpty())
            <section id="pdp-reviews" class="pt-8 space-y-6" style="border-top: 1px solid var(--t-border);">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <h2 class="k71-heading text-2xl">Verified Reviews ({{ $reviews->count() }})</h2>
                    <span class="text-sm font-bold inline-flex items-center gap-1" style="color: var(--t-accent);">@include('storefront.themes.partials.icon', ['name' => 'star', 'class' => 'w-4 h-4']) {{ number_format($avgRating, 1) }} / 5.0</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($reviews->take(9) as $rev)
                        <article class="k71-surface p-5 space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold" style="color: var(--t-accent);">{{ (int) $rev->rating }} / 5</span>
                                <span style="color: var(--t-muted);">{{ $rev->created_at?->format('M d, Y') }}</span>
                            </div>
                            @if(!empty($rev->comment))<p class="text-sm leading-relaxed">{{ $rev->comment }}</p>@endif
                            <p class="text-xs font-semibold pt-2" style="color: var(--t-muted);">{{ $rev->user?->name ?? 'Verified Buyer' }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Related Products --}}
        @if(($relatedProducts ?? collect())->isNotEmpty())
            <section class="pt-8 space-y-6" style="border-top: 1px solid var(--t-border);">
                <h2 class="k71-heading text-2xl">More from {{ $storeName }}</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6">
                    @foreach($relatedProducts->take(4) as $rel)
                        @include('storefront.themes.partials.card', ['product' => $rel])
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</section>

<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.k71-variant-btn');
    if (!btn || btn.disabled) return;
    document.querySelectorAll('.k71-variant-btn').forEach(function (b) {
        b.setAttribute('aria-pressed', 'false');
        b.style.backgroundColor = 'transparent';
        b.style.color = 'var(--t-text)';
        b.style.border = '1px solid var(--t-border)';
    });
    btn.setAttribute('aria-pressed', 'true');
    btn.style.backgroundColor = 'var(--t-accent)';
    btn.style.color = 'var(--t-accent-contrast)';
    btn.style.border = '2px solid var(--t-accent)';

    const hidden = document.getElementById('k71-selected-variant-id');
    if (hidden) hidden.value = btn.dataset.variantId || '';
    const priceEl = document.getElementById('k71-pdp-price');
    if (priceEl && btn.dataset.variantPrice) priceEl.textContent = btn.dataset.variantPrice;
    const statusEl = document.getElementById('k71-variant-status');
    if (statusEl && btn.dataset.variantLabel) statusEl.textContent = btn.dataset.variantLabel;
    const qtyEl = document.getElementById('k71-qty');
    if (qtyEl && btn.dataset.variantStock) qtyEl.max = Math.max(1, Math.min(20, parseInt(btn.dataset.variantStock, 10)));
});
</script>
@endsection
