{{-- Shared Layer A Real Commerce Purchase Engine (Preserves K71 Variants, Stock, Quantity, AJAX Cart) --}}
<form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form data-k71-checkout-url="{{ route('checkout.index') }}" class="space-y-4">
    @csrf
    <input type="hidden" name="product_id" value="{{ $product->id }}">
    <input type="hidden" name="variant_id" id="k71-selected-variant-id" value="{{ $firstAvailableVariant?->id }}">

    @if($hasMultipleVariants)
        <div class="space-y-2.5">
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold uppercase tracking-wider">{{ $variantHeading ?? 'Select Option' }}</span>
                <span id="k71-variant-status" class="font-mono" style="color: var(--t-muted);">
                    {{ trim((string) ($firstAvailableVariant?->size ?: ($firstAvailableVariant?->color ?: ($firstAvailableVariant?->sku ?: 'Standard')))) }}
                </span>
            </div>
            <div class="{{ !empty($variantGrid) ? 'grid grid-cols-3 sm:grid-cols-4 gap-2' : 'flex flex-wrap gap-2' }}" role="radiogroup" aria-label="Product variants">
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
                            class="k71-variant-btn min-h-[42px] px-3.5 py-2 text-xs font-semibold transition {{ $variantBtnClass ?? 'rounded-lg' }} {{ $vDisabled ? 'opacity-45 line-through cursor-not-allowed' : 'cursor-pointer' }}"
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
        <div class="flex items-center shrink-0 {{ $qtyBoxClass ?? 'rounded-lg' }}" style="border: 1px solid var(--t-border);">
            <button type="button" onclick="var i=document.getElementById('k71-qty'); i.value=Math.max(1, parseInt(i.value||1)-1);" class="min-w-[44px] min-h-[44px] font-bold cursor-pointer" aria-label="Decrease quantity">&minus;</button>
            <input type="number" id="k71-qty" name="quantity" value="1" min="1" max="{{ max(1, min(20, $totalStock)) }}"
                   class="w-12 text-center text-sm font-semibold bg-transparent focus:outline-none" aria-label="Quantity">
            <button type="button" onclick="var i=document.getElementById('k71-qty'); i.value=Math.min(parseInt(i.max||20), parseInt(i.value||1)+1);" class="min-w-[44px] min-h-[44px] font-bold cursor-pointer" aria-label="Increase quantity">+</button>
        </div>

        @if(!empty($isCataloguePreview))
            <button type="button" disabled class="k71-btn k71-btn-primary flex-1 opacity-75 cursor-not-allowed {{ $ctaClass ?? '' }}">
                Preview Mode (Checkout Disabled)
            </button>
        @elseif($isSoldOut)
            <button type="button" disabled class="k71-btn k71-btn-outline flex-1 opacity-60 cursor-not-allowed {{ $ctaClass ?? '' }}">
                Out of Stock
            </button>
        @elseif($product->isPurchase())
            <button type="submit" class="k71-btn k71-btn-primary flex-1 cursor-pointer {{ $ctaClass ?? '' }}">
                @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                <span>{{ $ctaLabel ?? 'Add to Bag' }}</span>
            </button>
        @else
            <a href="{{ route('product.show', $product->slug) }}" class="k71-btn k71-btn-primary flex-1 {{ $ctaClass ?? '' }}">View Full Listing</a>
        @endif
    </div>
</form>
