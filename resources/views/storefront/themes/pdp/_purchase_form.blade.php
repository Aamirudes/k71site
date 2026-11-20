{{-- Shared Layer A Real Commerce Purchase Engine (Preserves K71 Variants, Stock, Quantity, AJAX Cart) --}}
<form action="{{ route('cart.add') }}" method="POST" enctype="multipart/form-data" data-k71-cart-form data-k71-checkout-url="{{ route('checkout.index') }}" class="space-y-4">
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

    @if($product->isGiftCustomizable())
        @include('storefront.themes.pdp._gift_customizer', [
            'product' => $product,
            'pricePaise' => $pricePaise ?? ($firstAvailableVariant?->price_override ?: $product->base_price),
        ])
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
            <button type="submit"
                    @if($product->isGiftCustomizable())
                        onclick="const t = this.form.querySelector('input[name=\'customization[custom_text]\']'); if ({{ !empty($product->getGiftCustomizationConfig()['is_text_required']) ? 'true' : 'false' }} && (!t || !t.value.trim())) { event.preventDefault(); window.dispatchEvent(new CustomEvent('k71-open-gift-modal')); return false; }"
                    @endif
                    class="k71-btn k71-btn-primary flex-1 cursor-pointer {{ $ctaClass ?? '' }}">
                @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                <span>{{ $ctaLabel ?? ($product->isGiftCustomizable() ? 'Personalize & Add to Bag' : 'Add to Bag') }}</span>
            </button>
        @else
            <a href="{{ route('product.show', $product->slug) }}" class="k71-btn k71-btn-primary flex-1 {{ $ctaClass ?? '' }}">View Full Listing</a>
        @endif
    </div>

    {{-- Secondary Tactile Actions: Shortlist Heart + Share Link --}}
    <div class="flex items-center gap-2.5 pt-1">
        <button type="button"
                data-k71-wish-btn
                data-wish-id="{{ $product->id }}"
                data-wish-title="{{ e($product->title) }}"
                class="flex-1 min-h-[40px] px-3.5 text-xs font-semibold inline-flex items-center justify-center gap-2 cursor-pointer transition hover:opacity-80 {{ $qtyBoxClass ?? 'rounded-lg' }}"
                style="border: 1px solid var(--t-border); color: var(--t-text);">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
            <span>Save to Shortlist</span>
        </button>
        <button type="button"
                data-k71-share-btn
                data-share-title="{{ e($product->title) }}"
                class="min-h-[40px] px-3.5 text-xs font-semibold inline-flex items-center justify-center gap-1.5 cursor-pointer transition hover:opacity-80 {{ $qtyBoxClass ?? 'rounded-lg' }}"
                style="border: 1px solid var(--t-border); color: var(--t-text);">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            <span>Share</span>
        </button>
    </div>

    {{-- Luxury Collapsible Merchant Care & Fulfillment Accordion --}}
    <div class="divide-y pt-2 text-xs" style="border-top: 1px solid var(--t-border); border-color: var(--t-border);">
        <details class="group py-3">
            <summary class="flex items-center justify-between font-semibold cursor-pointer list-none select-none">
                <span>Fulfillment &amp; Dispatch</span>
                <span class="font-mono text-sm transition-transform group-open:rotate-45">+</span>
            </summary>
            <p class="pt-2 leading-relaxed" style="color: var(--t-muted);">
                Dispatched directly by {{ $vendor->shop_name ?? 'this verified K71 merchant' }}{{ !empty($vendor->city) ? (' from ' . $vendor->city) : '' }}.
                @if(!empty($processingDays)) Standard dispatch preparation within {{ $processingDays }} {{ \Illuminate\Support\Str::plural('day', $processingDays) }}. @endif
            </p>
        </details>
        <details class="group py-3">
            <summary class="flex items-center justify-between font-semibold cursor-pointer list-none select-none">
                <span>Returns &amp; Buyer Protection</span>
                <span class="font-mono text-sm transition-transform group-open:rotate-45">+</span>
            </summary>
            <p class="pt-2 leading-relaxed" style="color: var(--t-muted);">
                @if(!empty($isReturnable) && !empty($returnDays))
                    Eligible for {{ $returnDays }}-day merchant return window under K71 Marketplace guidelines.
                @else
                    Backed by K71 verified merchant order protection and secure checkout.
                @endif
            </p>
        </details>
    </div>
</form>
