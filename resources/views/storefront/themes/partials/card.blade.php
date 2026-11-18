@php
    $cardTpl = $manifest['layout']['card_template'] ?? 'luxury-atelier';
    $aspect = $themeConfig['grid']['card_aspect'] ?? ($manifest['layout']['card_aspect'] ?? '3/4');
    $contentAlign = $themeConfig['grid']['content_align'] ?? 'left';
    $ratioClass = match($aspect) { '1/1' => 'aspect-square', '4/5' => 'aspect-[4/5]', '4/3' => 'aspect-[4/3]', default => 'aspect-[3/4]' };

    $fallbackArt = asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg');
    $primaryImg = $product->images?->first()?->url ?? $fallbackArt;
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
    $cardIdx = isset($loop) ? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) : '01';

    $productUrl = !empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=product&product=' . urlencode($product->slug))
        : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]) . (!empty($isPreview) ? '?preview=1' : '');
@endphp

@if($cardTpl === 'fresh-dense-market')
    {{-- ULTRA-SCANNABLE HIGH-DENSITY MARKET CARD (Fresh Market, Market Studio) --}}
    <article class="k71-product-card k71-card-shell flex flex-col h-full overflow-hidden" style="border-radius: var(--t-radius);" data-k71-reveal>
        <a href="{{ $productUrl }}" class="k71-card-media relative block w-full aspect-square overflow-hidden" style="background-color: var(--t-surface-alt);">
            <img src="{{ $primaryImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover {{ $isSoldOut ? 'opacity-55' : '' }}">
            @if($hasDiscount && !$isSoldOut)
                <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-extrabold rounded" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">{{ $discountPct }}% OFF</span>
            @elseif($isSoldOut)
                <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold rounded bg-black/80 text-white">Out of stock</span>
            @endif
        </a>
        <div class="p-3 flex-1 flex flex-col justify-between gap-2">
            <div>
                <h3 class="text-xs sm:text-sm font-bold leading-snug line-clamp-2"><a href="{{ $productUrl }}" class="hover:opacity-75">{{ $product->title }}</a></h3>
                @if(!empty($product->weight_grams))
                    <p class="text-[11px] mt-0.5" style="color: var(--t-muted);">{{ $product->weight_grams }}g pack</p>
                @endif
            </div>
            <div class="flex items-center justify-between gap-2 pt-2" style="border-top: 1px solid var(--t-border);">
                <div>
                    <span class="text-sm sm:text-base font-extrabold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                    @if($hasDiscount)
                        <span class="block text-[10px] line-through tabular-nums" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>
                    @endif
                </div>
                @if(!$isSoldOut && empty($isCataloguePreview) && $product->isPurchase() && $variants->count() <= 1)
                    <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form>
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if($firstVariant)<input type="hidden" name="variant_id" value="{{ $firstVariant->id }}">@endif
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="k71-btn k71-btn-primary !min-h-[38px] !py-1.5 !px-3.5 !text-xs">Add</button>
                    </form>
                @else
                    <a href="{{ $productUrl }}" class="k71-btn k71-btn-outline !min-h-[38px] !py-1.5 !px-3 !text-xs">View</a>
                @endif
            </div>
        </div>
    </article>

@elseif($cardTpl === 'gallery-plinth')
    {{-- MUSEUM GALLERY PLINTH CARD (Atelier Blanc, Sole Archive, Artifact, Mono Tech, Forma Gallery) --}}
    <article class="k71-product-card k71-card-shell flex flex-col h-full gap-3" data-k71-reveal>
        <a href="{{ $productUrl }}" class="k71-card-frame k71-card-media relative block w-full {{ $ratioClass }} overflow-hidden">
            <img src="{{ $primaryImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover {{ $isSoldOut ? 'opacity-60' : '' }}">
            @if($secondaryImg)
                <img src="{{ $secondaryImg }}" alt="{{ $product->title }}" loading="lazy" class="k71-img-secondary absolute inset-0 w-full h-full object-cover">
            @endif
        </a>
        <div class="space-y-1">
            <div class="flex items-center justify-between text-[10px] font-mono uppercase tracking-widest" style="color: var(--t-muted);">
                <span>NO. {{ $cardIdx }}</span>
                <span>{{ $isSoldOut ? 'ARCHIVED' : '₹' . number_format($pricePaise / 100, 0) }}</span>
            </div>
            <h3 class="text-sm font-medium leading-snug"><a href="{{ $productUrl }}" class="hover:opacity-65">{{ $product->title }}</a></h3>
        </div>
    </article>

@elseif($cardTpl === 'sneaker-vault')
    {{-- SNEAKER COLLECTOR SIZE-FORWARD CARD (Sneaker Vault, Motion Lab, Minimal Sole) --}}
    <article class="k71-product-card k71-card-shell flex flex-col h-full overflow-hidden" style="border-radius: var(--t-radius);" data-k71-reveal>
        <a href="{{ $productUrl }}" class="k71-card-media relative block w-full {{ $ratioClass }} overflow-hidden" style="background-color: var(--t-surface-alt);">
            <img src="{{ $primaryImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
            <span class="absolute top-2.5 right-2.5 px-2 py-0.5 text-[10px] font-mono font-bold uppercase rounded" style="background-color: {{ $isSoldOut ? '#18181b' : 'var(--t-accent)' }}; color: {{ $isSoldOut ? '#fff' : 'var(--t-accent-contrast)' }};">
                {{ $isSoldOut ? 'SOLD OUT' : ($totalStock . ' IN STOCK') }}
            </span>
        </a>
        <div class="p-4 flex-1 flex flex-col justify-between gap-3">
            <div class="space-y-1.5">
                <h3 class="text-sm font-bold leading-snug line-clamp-2"><a href="{{ $productUrl }}" class="hover:opacity-75">{{ $product->title }}</a></h3>
                <div class="flex items-baseline gap-2">
                    <span class="text-base font-extrabold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                    @if($hasDiscount)<span class="text-xs line-through" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>@endif
                </div>
                @if($inStockVariants->isNotEmpty())
                    <div class="flex flex-wrap gap-1 pt-1">
                        @foreach($inStockVariants->take(5) as $v)
                            @php $lbl = trim((string) ($v->size ?: ($v->color ?: ''))); @endphp
                            @if($lbl !== '' && strcasecmp($lbl, 'Default') !== 0)
                                <span class="px-1.5 py-0.5 text-[10px] font-mono font-semibold rounded" style="border: 1px solid var(--t-border);">{{ $lbl }}</span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            <a href="{{ $productUrl }}" class="k71-btn k71-btn-primary w-full !min-h-[40px] !py-2 !text-xs">Select Size &amp; View</a>
        </div>
    </article>

@elseif($cardTpl === 'tech-spec-matrix' || $cardTpl === 'horology-spec')
    {{-- PRECISION SPECIFICATION CARD (Circuit, Future Form, Chronograph, Jewelle, Goldline, Sartorial, Pixel Foundry, The Specialist) --}}
    <article class="k71-product-card k71-card-shell flex flex-col h-full overflow-hidden" style="border-radius: var(--t-radius);" data-k71-reveal>
        <a href="{{ $productUrl }}" class="k71-card-media relative block w-full {{ $ratioClass }} overflow-hidden" style="background-color: var(--t-surface-alt);">
            <img src="{{ $primaryImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
            @if(!empty($product->sku))
                <span class="absolute bottom-2 left-2 px-2 py-0.5 text-[10px] font-mono bg-black/75 text-white rounded">{{ $product->sku }}</span>
            @endif
        </a>
        <div class="p-4 flex-1 flex flex-col justify-between gap-3">
            <div class="space-y-1.5">
                @if($product->category)
                    <p class="text-[10px] font-mono uppercase tracking-wider" style="color: var(--t-accent);">{{ $product->category->name }}</p>
                @endif
                <h3 class="text-sm font-bold leading-snug line-clamp-2"><a href="{{ $productUrl }}" class="hover:opacity-75">{{ $product->title }}</a></h3>
                @if(!empty($product->material) || !empty($product->brand))
                    <p class="text-[11px] truncate" style="color: var(--t-muted);">{{ implode(' · ', array_filter([$product->brand, $product->material])) }}</p>
                @endif
                @if($avgRating > 0 && $reviewCount > 0)
                    <div class="inline-flex items-center gap-1 text-[11px]" style="color: var(--t-accent);">
                        @include('storefront.themes.partials.icon', ['name' => 'star', 'class' => 'w-3 h-3'])
                        <span class="font-bold" style="color: var(--t-text);">{{ number_format($avgRating, 1) }}</span>
                        <span style="color: var(--t-muted);">({{ $reviewCount }})</span>
                    </div>
                @endif
            </div>
            <div class="pt-2.5 flex items-center justify-between gap-2" style="border-top: 1px solid var(--t-border);">
                <span class="text-sm sm:text-base font-extrabold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                <a href="{{ $productUrl }}" class="text-xs font-bold inline-flex items-center gap-1 hover:opacity-75" style="color: var(--t-accent);">
                    Specs @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                </a>
            </div>
        </div>
    </article>

@elseif($cardTpl === 'interior-architectural')
    {{-- ARCHITECTURAL INTERIOR / FURNITURE CARD (Casa Moderna, Nordic Living, Earth & Grain, Habitat Studio, Luxe Objects, Neo Commerce, Heritage House) --}}
    <article class="k71-product-card k71-card-shell flex flex-col h-full overflow-hidden" style="border-radius: var(--t-radius);" data-k71-reveal>
        <a href="{{ $productUrl }}" class="k71-card-media relative block w-full {{ $ratioClass }} overflow-hidden" style="background-color: var(--t-surface-alt);">
            <img src="{{ $primaryImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
            @if($secondaryImg)
                <img src="{{ $secondaryImg }}" alt="{{ $product->title }}" loading="lazy" class="k71-img-secondary absolute inset-0 w-full h-full object-cover">
            @endif
        </a>
        <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between gap-3">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="k71-heading text-base leading-snug line-clamp-2"><a href="{{ $productUrl }}" class="hover:opacity-75">{{ $product->title }}</a></h3>
                    @if(!empty($product->material))
                        <p class="text-xs mt-1 truncate" style="color: var(--t-muted);">{{ $product->material }}</p>
                    @endif
                </div>
                <span class="text-sm font-bold tabular-nums shrink-0">₹{{ number_format($pricePaise / 100, 0) }}</span>
            </div>
            <div class="pt-2.5 flex items-center justify-between text-[11px]" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">
                <span>{{ $variants->count() > 1 ? $variants->count() . ' finishes / options' : ($isSoldOut ? 'Unavailable' : 'Available') }}</span>
                <a href="{{ $productUrl }}" class="font-semibold hover:opacity-75" style="color: var(--t-text);">Details &rarr;</a>
            </div>
        </div>
    </article>

@else
    {{-- LUXURY ATELIER / OBSIDIAN MONOLITH / EDITORIAL SPREAD / APOTHECARY / GOURMET / DROP POSTER --}}
    <article class="k71-product-card k71-card-shell group flex flex-col h-full overflow-hidden" style="border-radius: var(--t-radius);" data-k71-reveal>
        <a href="{{ $productUrl }}" class="k71-card-media relative block w-full {{ $ratioClass }} overflow-hidden" style="background-color: var(--t-surface-alt);">
            <img src="{{ $primaryImg }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover {{ $isSoldOut ? 'opacity-60' : '' }}">
            @if($secondaryImg)
                <img src="{{ $secondaryImg }}" alt="{{ $product->title }}" loading="lazy" class="k71-img-secondary absolute inset-0 w-full h-full object-cover">
            @endif
            @if($isSoldOut)
                <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-black/80 text-white">Sold Out</span>
            @elseif($hasDiscount)
                <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">-{{ $discountPct }}%</span>
            @endif
        </a>
        <div class="p-4 flex-1 flex flex-col justify-between gap-3 {{ $contentAlign === 'center' ? 'text-center items-center' : 'text-left' }}">
            <div class="space-y-1 w-full">
                @if($product->category)
                    <p class="k71-eyebrow truncate" style="color: var(--t-muted);">{{ $product->category->name }}</p>
                @endif
                <h3 class="text-sm sm:text-[15px] font-semibold leading-snug line-clamp-2"><a href="{{ $productUrl }}" class="hover:opacity-70">{{ $product->title }}</a></h3>
                <div class="flex flex-wrap items-baseline gap-2 pt-0.5 {{ $contentAlign === 'center' ? 'justify-center' : '' }}">
                    <span class="text-sm sm:text-base font-bold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                    @if($hasDiscount)
                        <span class="text-xs line-through tabular-nums" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>
                    @endif
                </div>
            </div>
            <div class="w-full pt-1">
                @if($cardTpl === 'gourmet-pantry' && !$isSoldOut && empty($isCataloguePreview) && $product->isPurchase() && $variants->count() <= 1)
                    <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form class="w-full">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        @if($firstVariant)<input type="hidden" name="variant_id" value="{{ $firstVariant->id }}">@endif
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="k71-btn k71-btn-primary w-full !min-h-[40px] !py-2 !text-xs">Add to Bag</button>
                    </form>
                @else
                    <a href="{{ $productUrl }}" class="k71-btn k71-btn-outline w-full !min-h-[40px] !py-2 !text-xs">
                        {{ $isSoldOut ? 'View Details' : 'Explore Piece' }}
                    </a>
                @endif
            </div>
        </div>
    </article>
@endif
