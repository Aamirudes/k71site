{{-- WHOLESALE COMMAND BESPOKE PRODUCT DETAIL PAGE (3-Column Engineering Spec Lab) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Wholesale Command');
    $storeHomeUrl = route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = route('storefront.collection', ['slug' => $vendor->slug]);
    $images = ($product->images ?? collect())->map(fn ($img) => $img->url)->filter()->values();
    if ($images->isEmpty()) {
        $images = collect([asset('images/theme-editorial/tech-hardware.jpg')]);
    }
    $pricePaise = (int) ($product->base_price ?? 0);
    $variants = $product->variants ?? collect();
    $totalStock = $variants->isNotEmpty() ? (int) $variants->sum('stock') : (int) ($product->total_stock ?? 0);
    $isSoldOut = $totalStock <= 0;
@endphp

<div class="w-full bg-[#0b0f19] text-[#f9fafb] py-8 sm:py-12">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 space-y-8">
        {{-- Telemetry Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-[#1f2937] text-xs font-mono text-zinc-400">
            <div class="flex items-center gap-2">
                <a href="{{ $storeHomeUrl }}" class="hover:text-white">TERMINAL</a>
                <span>/</span>
                <a href="{{ $storeCollectionUrl }}" class="hover:text-white">{{ $product->category?->name ?? 'COMMERCIAL' }}</a>
                <span>/</span>
                <span class="text-white">SKU-{{ $product->sku ?: $product->id }}</span>
            </div>
            <span class="text-emerald-400 font-bold">● STATUS: VERIFIED DEPOT INVENTORY</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Col 1 (5 cols): High-Resolution Technical Image Viewer --}}
            <div class="lg:col-span-5 space-y-3">
                <div class="aspect-square rounded-lg overflow-hidden bg-[#111827] border border-[#1f2937] p-4 flex items-center justify-center">
                    <img src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-contain">
                </div>
                @if($images->count() > 1)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($images->take(4) as $imgUrl)
                            <div class="aspect-square rounded bg-[#111827] border border-[#1f2937] p-1 overflow-hidden">
                                <img src="{{ $imgUrl }}" alt="" class="w-full h-full object-contain">
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Col 2 (4 cols): Full Technical Specification Sheet --}}
            <div class="lg:col-span-4 space-y-6">
                <div>
                    <span class="text-[10px] font-mono text-sky-400 font-bold uppercase tracking-wider block mb-1">
                        SPECIFICATION SHEET // SKU: {{ $product->sku ?: 'LOT-' . $product->id }}
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-mono font-bold text-white leading-tight">
                        {{ $product->title }}
                    </h1>
                </div>

                @if(!empty($product->description))
                    <div class="space-y-1 text-xs font-mono text-zinc-400">
                        <span class="text-white font-bold uppercase tracking-wider text-[11px] block">Engineering Overview</span>
                        <p class="leading-relaxed whitespace-pre-line">{{ strip_tags((string)$product->description) }}</p>
                    </div>
                @endif

                <div class="space-y-2 pt-2 border-t border-[#1f2937]">
                    <span class="text-white font-mono font-bold uppercase tracking-wider text-[11px] block">Logistics &amp; Packaging Specs</span>
                    <dl class="divide-y divide-[#1f2937] text-xs font-mono">
                        <div class="py-2 flex justify-between"><dt class="text-zinc-400">SUPPLIER</dt><dd class="text-white font-bold">{{ $storeName }}</dd></div>
                        <div class="py-2 flex justify-between"><dt class="text-zinc-400">DISPATCH</dt><dd class="text-emerald-400 font-bold">24-48 Hours</dd></div>
                        <div class="py-2 flex justify-between"><dt class="text-zinc-400">AVAILABLE DEPOT STOCK</dt><dd class="text-white font-bold">{{ $totalStock }} Units</dd></div>
                        <div class="py-2 flex justify-between"><dt class="text-zinc-400">MIN ORDER QTY (MOQ)</dt><dd class="text-amber-400 font-bold">10 Units (Case Pack)</dd></div>
                        @if(!empty($product->material))
                            <div class="py-2 flex justify-between"><dt class="text-zinc-400">MATERIAL</dt><dd class="text-white">{{ $product->material }}</dd></div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Col 3 (3 cols): Sticky Wholesale Purchase Terminal --}}
            <div class="lg:col-span-3 p-5 sm:p-6 rounded-xl bg-[#111827] border border-[#1f2937] space-y-5 lg:sticky lg:top-24">
                <div class="space-y-1 pb-4 border-b border-[#1f2937]">
                    <span class="text-[10px] font-mono text-zinc-400 uppercase tracking-widest block">Unit Base Price</span>
                    <span class="text-3xl font-mono font-bold text-white tabular-nums">
                        ₹{{ number_format($pricePaise / 100, 0) }}
                    </span>
                    <span class="text-[10px] font-mono text-zinc-500 block">EX-WAREHOUSE · PLUS FREIGHT</span>
                </div>

                {{-- Volume Discount Pill --}}
                <div class="p-3 rounded bg-[#0b0f19] border border-[#1f2937] space-y-1.5 text-xs font-mono">
                    <span class="text-sky-400 font-bold block">TIER DISCOUNTS APPLIED:</span>
                    <div class="flex justify-between text-[11px] text-zinc-400">
                        <span>10–50 Units:</span>
                        <span class="text-white font-bold">Base Price</span>
                    </div>
                    <div class="flex justify-between text-[11px] text-zinc-400">
                        <span>51–200 Units:</span>
                        <span class="text-emerald-400 font-bold">15% Discount</span>
                    </div>
                    <div class="flex justify-between text-[11px] text-zinc-400">
                        <span>201+ Units:</span>
                        <span class="text-emerald-400 font-bold">30% Discount</span>
                    </div>
                </div>

                @include('storefront.themes.pdp._purchase_form', [
                    'ctaLabel' => 'LOCK IN WHOLESALE LOT',
                    'variantHeading' => 'SPECIFICATION CONFIG',
                ])
            </div>
        </div>
    </div>
</div>
