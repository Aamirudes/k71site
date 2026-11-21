{{-- OBJECT GALLERY BESPOKE PRODUCT CARD --}}
@php
    $productUrl = route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $product->slug]) . (!empty($isPreview) ? '?preview=1' : '');
    $pricePaise = (int) ($product->base_price ?? 0);
    $imgUrl = $product->images?->first()?->url ?? asset('images/theme-editorial/craft-pottery.jpg');
    $plinthNum = isset($loop) ? str_pad((string)($loop->iteration), 2, '0', STR_PAD_LEFT) : '01';
@endphp

<article class="p-6 sm:p-8 bg-[#fbfbfa] border border-black/[0.08] hover:border-black/30 transition duration-500 flex flex-col justify-between gap-6 group">
    <div class="flex items-center justify-between text-[10px] font-mono uppercase tracking-[0.22em] text-[#767676] pb-3 border-b border-black/[0.05]">
        <span>PLINTH {{ $plinthNum }} · EXHIBIT</span>
        <span>{{ $product->category?->name ?? 'OBJECT' }}</span>
    </div>

    {{-- Centered Elevated Artwork Plinth --}}
    <a href="{{ $productUrl }}" class="block aspect-square w-full overflow-hidden bg-white p-6 border border-black/[0.05]">
        <img src="{{ $imgUrl }}" alt="{{ $product->title }}" loading="lazy" class="w-full h-full object-contain group-hover:scale-105 transition duration-700">
    </a>

    <div class="space-y-3 pt-2">
        <div class="flex items-baseline justify-between gap-4">
            <h3 class="text-xl sm:text-2xl font-serif text-[#111111] leading-tight truncate">
                <a href="{{ $productUrl }}" class="hover:opacity-75 transition">{{ $product->title }}</a>
            </h3>
            <span class="font-mono text-sm font-semibold text-[#111111] shrink-0">
                ₹{{ number_format($pricePaise / 100, 0) }}
            </span>
        </div>

        @if(!empty($product->material))
            <p class="text-[11px] font-mono text-[#767676]">
                SPEC: {{ strtoupper($product->material) }}
            </p>
        @endif

        <div class="pt-3 border-t border-black/[0.05] flex items-center justify-between">
            <span class="text-[10px] font-mono uppercase tracking-widest text-[#767676]">STATUS: CATALOGUED</span>
            <a href="{{ $productUrl }}" class="text-[11px] font-mono uppercase tracking-wider text-black font-semibold underline underline-offset-4 hover:opacity-70 transition">
                EXAMINE DOSSIER →
            </a>
        </div>
    </div>
</article>
