{{-- Shared Tactile Luxury Overlay Controls for Product Cards: Wishlist Heart + Glass Hover Quick-View & Quick-Add Bar --}}
@php
    $qvDesc = \Illuminate\Support\Str::limit(strip_tags((string) ($product->description ?? ($product->material ? 'Crafted in ' . $product->material . '.' : 'Curated piece from ' . ($vendor->shop_name ?? 'K71 Store') . '.'))), 180);
    $qvCat = $product->category?->name ?? ($vendor->shop_name ?? 'Featured');
    $qvPriceText = '₹' . number_format($pricePaise / 100, 0);
@endphp
<button type="button"
        class="k71-wish-pill"
        data-k71-wish-btn
        data-wish-id="{{ $product->id }}"
        data-wish-title="{{ e($product->title) }}"
        aria-label="Save {{ $product->title }} to shortlist"
        title="Save to shortlist">
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
    </svg>
</button>

<div class="k71-card-actions-bar">
    <button type="button"
            data-k71-quickview
            data-qv-id="{{ $product->id }}"
            data-qv-title="{{ e($product->title) }}"
            data-qv-cat="{{ e($qvCat) }}"
            data-qv-price="{{ $qvPriceText }}"
            data-qv-desc="{{ e($qvDesc) }}"
            data-qv-img="{{ $primaryImg }}"
            data-qv-url="{{ $productUrl }}"
            class="flex-1 min-h-[34px] px-2.5 text-[11px] font-bold uppercase tracking-wider rounded cursor-pointer inline-flex items-center justify-center gap-1.5 hover:opacity-80 transition"
            style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
        <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
        <span>Quick View</span>
    </button>

    @if(!$isSoldOut && empty($isCataloguePreview) && method_exists($product, 'isPurchase') && $product->isPurchase() && $variants->count() <= 1)
        <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form class="shrink-0">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            @if($firstVariant)<input type="hidden" name="variant_id" value="{{ $firstVariant->id }}">@endif
            <input type="hidden" name="quantity" value="1">
            <button type="submit"
                    class="min-h-[34px] px-3 text-[11px] font-bold uppercase tracking-wider rounded cursor-pointer hover:opacity-90 transition"
                    style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                + Bag
            </button>
        </form>
    @else
        <a href="{{ $productUrl }}"
           class="min-h-[34px] px-3 text-[11px] font-bold uppercase tracking-wider rounded inline-flex items-center justify-center hover:opacity-90 transition"
           style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
            Inspect
        </a>
    @endif
</div>
