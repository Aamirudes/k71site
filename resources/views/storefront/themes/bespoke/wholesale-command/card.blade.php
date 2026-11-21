{{-- WHOLESALE COMMAND BESPOKE PRODUCT CARD --}}
@php
    $productUrl = route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]) . (!empty($isPreview) ? '?preview=1' : '');
    $pricePaise = (int) ($product->base_price ?? 0);
    $imgUrl = $product->images?->first()?->url ?? asset('images/theme-editorial/tech-hardware.jpg');
    $stock = (int) ($product->total_stock ?? 120);
    $variants = $product->variants ?? collect();
    $firstVariant = $variants->first();
@endphp

<article class="p-4 rounded-lg bg-[#111827] border border-[#1f2937] hover:border-sky-500 transition duration-300 flex flex-col justify-between gap-4 font-mono">
    <div class="space-y-3">
        <div class="flex items-center justify-between text-[10px] text-zinc-400">
            <span class="px-2 py-0.5 rounded bg-[#1f2937] text-sky-400 font-bold uppercase">
                SKU: {{ $product->sku ?: 'LOT-' . $product->id }}
            </span>
            <span class="text-emerald-400 font-bold">
                ● {{ $stock }} UNITS
            </span>
        </div>

        <div class="flex gap-3 items-center">
            <a href="{{ $productUrl }}" class="w-20 h-20 shrink-0 rounded overflow-hidden bg-[#0b0f19] border border-[#374151]">
                <img src="{{ $imgUrl }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover">
            </a>
            <div class="min-w-0 flex-1 space-y-1">
                <h3 class="text-xs sm:text-sm font-bold text-white truncate">
                    <a href="{{ $productUrl }}" class="hover:text-sky-400 transition">{{ $product->title }}</a>
                </h3>
                <p class="text-[11px] text-zinc-400 truncate">
                    DEPT: {{ $product->category?->name ?? 'COMMERCIAL' }}
                </p>
                <div class="flex items-baseline gap-2">
                    <span class="text-sm sm:text-base font-bold text-white">₹{{ number_format($pricePaise / 100, 0) }}</span>
                    <span class="text-[10px] text-zinc-500">/ unit (min 10)</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Inline Quantity & Quick Add --}}
    <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form class="pt-3 border-t border-[#1f2937] flex items-center justify-between gap-2">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">
        @if($firstVariant)<input type="hidden" name="variant_id" value="{{ $firstVariant->id }}">@endif
        
        <div class="flex items-center gap-1.5 text-xs text-zinc-400">
            <span>QTY:</span>
            <input type="number" name="quantity" value="10" min="1" class="w-14 h-8 px-1.5 bg-[#0b0f19] border border-[#374151] rounded text-center text-xs font-mono text-white focus:outline-none focus:border-sky-500">
        </div>

        <button type="submit" class="h-8 px-3.5 rounded bg-sky-600 hover:bg-sky-500 text-white text-xs font-mono font-bold uppercase transition">
            + ADD LOT
        </button>
    </form>
</article>
