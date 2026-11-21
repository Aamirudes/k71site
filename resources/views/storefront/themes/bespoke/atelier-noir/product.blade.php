{{-- ATELIER NOIR BESPOKE PRODUCT DETAIL PAGE (50/50 Split Sticky Atelier) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Atelier Noir');
    $storeHomeUrl = route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = route('storefront.collection', ['slug' => $vendor->slug]);
    $images = ($product->images ?? collect())->map(fn ($img) => $img->url)->filter()->values();
    if ($images->isEmpty()) {
        $images = collect([asset('images/theme-editorial/fashion-editorial.jpg')]);
    }
    $pricePaise = (int) ($product->base_price ?? 0);
    $comparePaise = (int) ($product->compare_at_price ?? 0);
    $hasDiscount = $comparePaise > $pricePaise && $pricePaise > 0;
    $variants = $product->variants ?? collect();
    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? 0);
    $isSoldOut = $totalStock <= 0;
@endphp

<div class="w-full bg-[#08080a] text-[#f5f2eb]">
    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen">
        {{-- Left 7 Cols: Vertical Stacked Photography --}}
        <div class="lg:col-span-7 border-r border-white/10 bg-[#0d0d10] flex flex-col divide-y divide-white/10">
            @foreach($images as $idx => $imgUrl)
                <div class="w-full aspect-[4/5] lg:min-h-[90vh] overflow-hidden">
                    <img src="{{ $imgUrl }}" alt="{{ $product->title }} · Plate {{ $idx + 1 }}" class="w-full h-full object-cover">
                </div>
            @endforeach
        </div>

        {{-- Right 5 Cols: Pinned Haute Atelier Purchase Sanctuary --}}
        <div class="lg:col-span-5 p-6 sm:p-12 lg:p-16 flex flex-col justify-between space-y-10 lg:sticky lg:top-20 lg:h-[calc(100vh-5rem)] overflow-y-auto">
            <div class="space-y-6">
                <nav class="text-[10px] font-mono uppercase tracking-[0.26em] text-[#8a857e] flex items-center gap-2">
                    <a href="{{ $storeHomeUrl }}" class="hover:text-white transition">ATELIER</a>
                    <span>/</span>
                    <a href="{{ $storeCollectionUrl }}" class="hover:text-white transition">{{ $product->category?->name ?? 'COLLECTION' }}</a>
                </nav>

                <div class="space-y-2">
                    <span class="text-[10px] font-mono uppercase tracking-[0.3em] text-[#8a857e] block">
                        HAUTE EDITION // REF: {{ $product->sku ?: 'NOIR-' . $product->id }}
                    </span>
                    <h1 class="text-3xl sm:text-5xl font-serif italic text-[#f5f2eb] leading-[1.02]">
                        {{ $product->title }}
                    </h1>
                </div>

                <div class="flex items-baseline gap-4 pt-2 border-t border-white/10">
                    <span class="text-2xl sm:text-3xl font-mono text-[#f5f2eb]">
                        ₹{{ number_format($pricePaise / 100, 0) }}
                    </span>
                    @if($hasDiscount)
                        <span class="text-sm font-mono line-through text-[#8a857e]">
                            ₹{{ number_format($comparePaise / 100, 0) }}
                        </span>
                    @endif
                </div>

                @if(!empty($product->description))
                    <div class="space-y-2 text-xs sm:text-sm text-[#8a857e] leading-relaxed">
                        <p class="font-mono text-[10px] uppercase tracking-widest text-[#f5f2eb]">Curatorial Notes</p>
                        <p class="font-sans">{{ strip_tags((string)$product->description) }}</p>
                    </div>
                @endif

                {{-- Real K71 Purchase Form with Bespoke Styling --}}
                <div class="pt-6 border-t border-white/10">
                    @include('storefront.themes.pdp._purchase_form', [
                        'ctaLabel' => 'Acquire Piece',
                        'variantHeading' => 'SELECT SILHOUETTE / SIZE',
                    ])
                </div>
            </div>

            {{-- Provenance & Material Specs --}}
            <div class="pt-8 border-t border-white/10 text-xs font-mono text-[#8a857e] space-y-2">
                <div class="flex justify-between">
                    <span>CRAFT ORIGIN:</span>
                    <span class="text-[#f5f2eb]">{{ $vendor->city ?: 'VERIFIED ATELIER' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>AVAILABILITY:</span>
                    <span class="text-[#f5f2eb]">{{ $isSoldOut ? 'ARCHIVED' : "{$totalStock} PIECES RESERVED" }}</span>
                </div>
                @if(!empty($product->material))
                    <div class="flex justify-between">
                        <span>FABRICATION:</span>
                        <span class="text-[#f5f2eb]">{{ $product->material }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
