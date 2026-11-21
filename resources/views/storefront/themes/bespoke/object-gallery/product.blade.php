{{-- OBJECT GALLERY BESPOKE PRODUCT DETAIL PAGE (Centered Museum Plinth) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Object Gallery');
    $storeHomeUrl = route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = route('storefront.collection', ['slug' => $vendor->slug]);
    $images = ($product->images ?? collect())->map(fn ($img) => $img->url)->filter()->values();
    if ($images->isEmpty()) {
        $images = collect([asset('images/theme-editorial/craft-pottery.jpg')]);
    }
    $pricePaise = (int) ($product->base_price ?? 0);
    $variants = $product->variants ?? collect();
    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? 0);
    $isSoldOut = $totalStock <= 0;
@endphp

<div class="w-full bg-[#ffffff] text-[#111111] py-10 sm:py-16">
    <div class="max-w-5xl mx-auto px-5 sm:px-10 space-y-12">
        {{-- Top Museum Room Tag --}}
        <div class="text-center space-y-2">
            <span class="text-[10px] font-mono uppercase tracking-[0.28em] text-[#767676]">
                ACCESSION RECORD · {{ $product->category?->name ?? 'SCULPTURAL OBJECT' }}
            </span>
            <h1 class="text-3xl sm:text-5xl font-serif text-[#111111]">
                {{ $product->title }}
            </h1>
        </div>

        {{-- Centered Framed Plinth Artwork --}}
        <div class="p-8 sm:p-16 bg-[#fbfbfa] border border-black/[0.08] flex items-center justify-center max-w-3xl mx-auto">
            <div class="aspect-square w-full max-w-lg bg-white p-6 shadow-sm border border-black/[0.05]">
                <img src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-contain">
            </div>
        </div>

        {{-- 3-Column Museum Dossier & Acquisition Module --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pt-8 border-t border-black/[0.08]">
            {{-- Col 1: Curatorial Dossier --}}
            <div class="lg:col-span-4 space-y-2">
                <p class="font-mono text-[10px] uppercase tracking-widest text-black font-bold">Curatorial Dossier</p>
                <p class="text-xs sm:text-sm text-[#767676] leading-relaxed font-sans">
                    {{ strip_tags((string)($product->description ?: 'An investigation into utilitarian permanence, honoring the intrinsic tactile character of its raw material.')) }}
                </p>
            </div>

            {{-- Col 2: Provenance & Specifications --}}
            <div class="lg:col-span-3 space-y-2 text-xs font-mono text-[#767676]">
                <p class="text-[10px] uppercase tracking-widest text-black font-bold">Provenance</p>
                <dl class="space-y-1.5 pt-1">
                    <div><span>VALUATION:</span> <strong class="text-black text-sm">₹{{ number_format($pricePaise / 100, 0) }}</strong></div>
                    <div><span>STATUS:</span> <span class="text-black">{{ $isSoldOut ? 'Unavailable' : "{$totalStock} in gallery" }}</span></div>
                    <div><span>REF SKU:</span> <span>{{ $product->sku ?: 'OBJ-' . $product->id }}</span></div>
                    @if(!empty($product->material))
                        <div><span>MATERIAL:</span> <span class="text-black">{{ $product->material }}</span></div>
                    @endif
                </dl>
            </div>

            {{-- Col 3: Acquisition Form --}}
            <div class="lg:col-span-5">
                @include('storefront.themes.pdp._purchase_form', [
                    'ctaLabel' => 'Acquire for Collection',
                    'variantHeading' => 'SELECT EDITION SPECIFICATION',
                ])
            </div>
        </div>
    </div>
</div>
