{{-- ATELIER NOIR BESPOKE PRODUCT CARD --}}
@php
    $productUrl = route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]) . (!empty($isPreview) ? '?preview=1' : '');
    $pricePaise = (int) ($product->base_price ?? 0);
    $comparePaise = (int) ($product->compare_at_price ?? 0);
    $hasDiscount = $comparePaise > $pricePaise && $pricePaise > 0;
    $imgUrl = $product->images?->first()?->url ?? asset('images/theme-editorial/fashion-editorial.jpg');
    $cardIdx = isset($loop) ? ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X'][$loop->index % 10] : 'I';
@endphp

<article class="group flex flex-col justify-between bg-[#111114] border border-white/10 hover:border-white/30 transition duration-500 overflow-hidden">
    <div class="relative aspect-[3/4] overflow-hidden bg-[#16161a]">
        <a href="{{ $productUrl }}" class="block w-full h-full">
            <img src="{{ $imgUrl }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
        </a>
        <div class="absolute top-3 left-3 flex items-center gap-2">
            <span class="px-2 py-0.5 text-[9px] font-mono tracking-widest uppercase bg-black/60 text-[#8a857e] backdrop-blur-md border border-white/10">
                {{ $cardIdx }} · EDITION
            </span>
            @if($hasDiscount)
                <span class="px-2 py-0.5 text-[9px] font-mono tracking-widest uppercase bg-[#f5f2eb] text-[#08080a] font-bold">
                    SALE
                </span>
            @endif
        </div>
    </div>

    <div class="p-5 flex-1 flex flex-col justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[9px] font-mono uppercase tracking-[0.22em] text-[#8a857e] block">
                {{ $product->category?->name ?? 'HAUTE COUTURE' }}
            </span>
            <h3 class="text-xl font-serif italic text-[#f5f2eb] group-hover:text-white transition leading-snug">
                <a href="{{ $productUrl }}">{{ $product->title }}</a>
            </h3>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-white/10">
            <span class="font-mono text-sm text-[#f5f2eb]">
                ₹{{ number_format($pricePaise / 100, 0) }}
            </span>
            <a href="{{ $productUrl }}" class="text-[10px] font-mono uppercase tracking-[0.2em] text-[#8a857e] hover:text-[#f5f2eb] transition underline underline-offset-4">
                ACQUIRE →
            </a>
        </div>
    </div>
</article>
