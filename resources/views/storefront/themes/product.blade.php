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

    $pdpArch = $manifest['layout']['pdp_architecture'] ?? 'editorial-fullbleed';
    $fallbackArt = asset('images/theme-samples/' . ($manifest['layout']['sample_art'] ?? 'fashion-editorial') . '.svg');
    $images = ($product->images ?? collect())->map(fn ($img) => $img->url)->filter()->values();
    if ($images->isEmpty()) {
        $images = collect([$fallbackArt]);
    }

    $variants = $product->variants ?? collect();
    $hasMultipleVariants = $variants->count() > 1 || ($variants->count() === 1 && strcasecmp((string) $variants->first()?->sku, 'Default') !== 0 && !empty($variants->first()?->size ?: $variants->first()?->color));
    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? $product->stock_quantity ?? 0);
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
    if (!empty($product->material)) { $specs['Material'] = $product->material; }
    if (!empty($product->gender)) { $specs['Classification'] = ucfirst((string) $product->gender); }
    if (!empty($product->weight_grams)) { $specs['Weight / Pack'] = $product->weight_grams . ' g'; }
    if (is_array($product->custom_fields ?? null)) {
        foreach ($product->custom_fields as $cfKey => $cfVal) {
            if (is_scalar($cfVal) && trim((string) $cfVal) !== '') {
                $specs[ucwords(str_replace('_', ' ', (string) $cfKey))] = trim((string) $cfVal);
            }
        }
    }

    $pdpView = 'storefront.themes.pdp.' . $pdpArch;
    if (!view()->exists($pdpView)) {
        $pdpView = 'storefront.themes.pdp.editorial-fullbleed';
    }
@endphp

@include($pdpView)

{{-- Verified Customer Reviews & Related Products --}}
@if($reviews->isNotEmpty() || ($relatedProducts ?? collect())->isNotEmpty())
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 space-y-12">
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
@endif

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
