@php
    $cardVariant = $manifest['layout']['card_variant'] ?? 'editorial-portrait';
    $aspect = $themeConfig['grid']['card_aspect'] ?? ($manifest['layout']['card_aspect'] ?? '3/4');
    $contentAlign = $themeConfig['grid']['content_align'] ?? 'left';
    $ratioClass = match($aspect) {
        '1/1' => 'aspect-square',
        '4/5' => 'aspect-[4/5]',
        '4/3' => 'aspect-[4/3]',
        default => 'aspect-[3/4]',
    };

    $primaryImg = $product->images?->first()?->url ?? asset('images/product-placeholder.svg');
    $secondaryImg = $product->images?->skip(1)?->first()?->url ?? null;
    $pricePaise = (int) ($product->base_price ?? 0);
    $comparePaise = (int) ($product->compare_at_price ?? 0);
    $hasDiscount = $comparePaise > $pricePaise && $pricePaise > 0;
    $discountPct = $hasDiscount ? (int) round((($comparePaise - $pricePaise) / $comparePaise) * 100) : 0;

    $variants = $product->variants ?? collect();
    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? 0);
    $isSoldOut = $totalStock <= 0;
    $inStockVariants = $variants->filter(fn ($v) => (int) ($v->stock ?? 0) > 0);
    $firstVariant = $inStockVariants->first() ?? $variants->first();

    $avgRating = (float) ($product->rating_avg ?? $product->rating ?? 0);
    $reviewCount = (int) ($product->reviews_count ?? 0);

    $productUrl = !empty($isCataloguePreview)
        ? route('vendor.themes.preview_frame', ['themeId' => $manifest['id'], 'page' => 'product', 'product' => $product->slug])
        : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]) . (!empty($isPreview) ? '?preview=1' : '');

    $useCardGlass = !empty($manifest['glass']['card_glass']) && ($glassStrength ?? 'normal') !== 'off';
    $isTilt = !empty($manifest['motion']['enable_tilt']);
    $isGraphic = in_array($manifest['id'], ['chromatic-studio', 'pop-studio'], true);
@endphp

<article class="k71-product-card group flex flex-col h-full overflow-hidden {{ $useCardGlass ? 'k71-glass' : ($cardVariant === 'minimal-borderless' ? '' : 'k71-surface') }} {{ $isGraphic ? 'k71-graphic-card' : '' }}"
         style="border-radius: var(--t-radius);"
         data-k71-reveal
         @if($isTilt) data-k71-tilt @endif>

    {{-- Media Plane --}}
    <a href="{{ $productUrl }}" class="k71-card-media relative block w-full {{ $ratioClass }} overflow-hidden" style="background-color: var(--t-surface-alt);">
        <img src="{{ $primaryImg }}"
             alt="{{ $product->title }}"
             loading="lazy"
             decoding="async"
             class="w-full h-full object-cover {{ $isSoldOut ? 'opacity-65' : '' }}">

        @if($secondaryImg)
            <img src="{{ $secondaryImg }}"
                 alt="{{ $product->title }} alternate view"
                 loading="lazy"
                 decoding="async"
                 class="k71-img-secondary absolute inset-0 w-full h-full object-cover">
        @endif

        {{-- Authentic Status Badges (only real data) --}}
        <div class="absolute top-2.5 left-2.5 flex flex-wrap gap-1.5 z-10">
            @if($isSoldOut)
                <span class="k71-card-badge px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded bg-black/80 text-white">Sold Out</span>
            @elseif($hasDiscount && $discountPct > 0)
                <span class="k71-card-badge px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider rounded" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">-{{ $discountPct }}%</span>
            @endif
        </div>
    </a>

    {{-- Card Body --}}
    <div class="flex-1 flex flex-col justify-between p-3.5 sm:p-4 gap-3 {{ $contentAlign === 'center' ? 'text-center items-center' : 'text-left' }}">
        <div class="space-y-1 w-full">
            @if(!empty($product->category?->name) && in_array($cardVariant, ['tech-spec-dense', 'gallery-object', 'magazine-mixed', 'signature-glass-card'], true))
                <p class="k71-eyebrow truncate" style="color: var(--t-muted);">{{ $product->category->name }}</p>
            @endif

            <h3 class="text-sm sm:text-[15px] font-semibold leading-snug line-clamp-2">
                <a href="{{ $productUrl }}" class="hover:opacity-75 transition">{{ $product->title }}</a>
            </h3>

            {{-- Real Rating (only shown if genuine rating exists) --}}
            @if($avgRating > 0 && $reviewCount > 0)
                <div class="flex items-center gap-1 text-[11px] {{ $contentAlign === 'center' ? 'justify-center' : '' }}" style="color: var(--t-muted);">
                    <span aria-hidden="true" style="color: var(--t-accent);">★</span>
                    <span class="font-semibold" style="color: var(--t-text);">{{ number_format($avgRating, 1) }}</span>
                    <span>({{ $reviewCount }})</span>
                </div>
            @endif

            {{-- Real Price & Compare-At --}}
            <div class="flex flex-wrap items-baseline gap-2 pt-0.5 {{ $contentAlign === 'center' ? 'justify-center' : '' }}">
                <span class="text-sm sm:text-base font-bold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                @if($hasDiscount)
                    <span class="text-xs line-through tabular-nums" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>
                @endif
            </div>

            {{-- Sneaker / Footwear / Variant Hints (only real variants) --}}
            @if(in_array($cardVariant, ['sneaker-size-forward', 'beauty-shade-soft'], true) && $inStockVariants->count() > 1)
                <div class="flex flex-wrap gap-1 pt-1 {{ $contentAlign === 'center' ? 'justify-center' : '' }}">
                    @foreach($inStockVariants->take(4) as $v)
                        @php
                            $vLabel = trim((string) ($v->size ?: ($v->color ?: ($v->name ?: ''))));
                        @endphp
                        @if($vLabel !== '' && strcasecmp($vLabel, 'Default') !== 0)
                            <span class="px-1.5 py-0.5 text-[10px] font-medium rounded" style="border: 1px solid var(--t-border); color: var(--t-muted);">{{ $vLabel }}</span>
                        @endif
                    @endforeach
                    @if($inStockVariants->count() > 4)
                        <span class="text-[10px] px-1" style="color: var(--t-muted);">+{{ $inStockVariants->count() - 4 }}</span>
                    @endif
                </div>
            @endif

            {{-- Tech / Industrial SKU & Material Spec Snippet (only real data) --}}
            @if($cardVariant === 'tech-spec-dense' && (!empty($product->sku) || !empty($product->material)))
                <p class="text-[11px] font-mono truncate pt-0.5" style="color: var(--t-muted);">
                    {{ $product->sku ? 'SKU: ' . $product->sku : '' }}{{ ($product->sku && $product->material) ? ' · ' : '' }}{{ $product->material ?: '' }}
                </p>
            @endif
        </div>

        {{-- Action Area (Always touch-accessible, never hover-only) --}}
        <div class="w-full pt-1">
            @if(in_array($cardVariant, ['conversion-direct', 'tech-spec-dense', 'dark-glass-pod', 'signature-glass-card'], true) && !$isSoldOut && empty($isCataloguePreview) && $product->isPurchase() && $variants->count() <= 1)
                <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form class="w-full">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    @if($firstVariant)
                        <input type="hidden" name="variant_id" value="{{ $firstVariant->id }}">
                    @endif
                    <input type="hidden" name="quantity" value="1">
                    <button type="submit" class="k71-btn k71-btn-primary w-full !min-h-[40px] !py-2 !text-xs">
                        Add to Bag
                    </button>
                </form>
            @else
                <a href="{{ $productUrl }}" class="k71-btn k71-btn-outline w-full !min-h-[40px] !py-2 !text-xs">
                    {{ $isSoldOut ? 'View Details' : ($variants->count() > 1 ? 'Select Option' : 'View Product') }}
                </a>
            @endif
        </div>
    </div>
</article>
