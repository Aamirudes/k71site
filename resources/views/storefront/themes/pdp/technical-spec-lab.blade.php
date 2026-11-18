{{-- PDP ARCHITECTURE 3: 3-COLUMN ENGINEERING SPECIFICATION LAB --}}
<section data-pdp-layout="technical-spec-lab" class="w-full py-8 sm:py-12">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 space-y-8">
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 text-xs font-mono" style="border-bottom: 1px solid var(--t-border); color: var(--t-muted);">
            <span>[ SPEC SHEET // {{ strtoupper($product->sku ?: 'ITEM-' . $product->id) }} ]</span>
            <span>{{ $product->category?->name ?? 'HARDWARE' }} · {{ $isSoldOut ? 'OUT OF STOCK' : "QTY AVAILABLE: {$totalStock}" }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Col 1 (5 cols): Technical Viewer + Thumb Strip --}}
            <div class="lg:col-span-5 space-y-3">
                <div class="aspect-square overflow-hidden" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                    <button type="button" id="k71-pdp-main-trigger" data-k71-lightbox-src="{{ $images->first() }}" data-k71-lightbox-alt="{{ $product->title }}" class="block w-full h-full p-0 border-0 bg-transparent cursor-zoom-in">
                        <img id="k71-pdp-main-img" src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                    </button>
                </div>
                @if($images->count() > 1)
                    <div class="grid grid-cols-5 gap-2">
                        @foreach($images->take(5) as $idx => $imgUrl)
                            <button type="button" onclick="document.getElementById('k71-pdp-main-img').src=this.dataset.src;" data-src="{{ $imgUrl }}"
                                    class="aspect-square overflow-hidden cursor-pointer p-0" style="border: 1px solid var(--t-border);">
                                <img src="{{ $imgUrl }}" alt="Thumb {{ $idx + 1 }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Col 2 (4 cols): Technical Spec Table & Engineering Overview --}}
            <div class="lg:col-span-4 space-y-6">
                <div>
                    <p class="text-[10px] font-mono uppercase tracking-[0.2em] mb-1.5" style="color: var(--t-accent);">{{ $manifest['name'] }} Telemetry</p>
                    <h1 class="k71-heading text-2xl sm:text-4xl leading-tight">{{ $product->title }}</h1>
                </div>

                @if(!empty($product->description))
                    <div class="space-y-1.5">
                        <h2 class="text-[11px] font-mono uppercase tracking-wider" style="color: var(--t-muted);">Overview</h2>
                        <p class="text-xs sm:text-sm leading-relaxed whitespace-pre-line">{{ strip_tags((string) $product->description) }}</p>
                    </div>
                @endif

                <div class="space-y-2">
                    <h2 class="text-[11px] font-mono uppercase tracking-wider" style="color: var(--t-muted);">Technical Specifications</h2>
                    <dl class="divide-y text-xs font-mono" style="border-top: 1px solid var(--t-border); border-bottom: 1px solid var(--t-border); border-color: var(--t-border);">
                        <div class="py-2 flex justify-between gap-3"><dt style="color: var(--t-muted);">MERCHANT</dt><dd class="font-bold">{{ $storeName }}</dd></div>
                        <div class="py-2 flex justify-between gap-3"><dt style="color: var(--t-muted);">DISPATCH</dt><dd>{{ $processingDays > 0 ? "{$processingDays} Business Days" : 'Verified Dispatch' }}</dd></div>
                        <div class="py-2 flex justify-between gap-3"><dt style="color: var(--t-muted);">RETURNS</dt><dd>{{ $isReturnable && $returnDays > 0 ? "{$returnDays} Days" : 'Standard Policy' }}</dd></div>
                        @foreach($specs as $k => $v)
                            <div class="py-2 flex justify-between gap-3"><dt style="color: var(--t-muted);">{{ strtoupper($k) }}</dt><dd class="font-bold text-right">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            </div>

            {{-- Col 3 (3 cols): Dedicated High-Contrast Purchase Module --}}
            <div class="lg:col-span-3 p-5 sm:p-6 space-y-5 lg:sticky lg:top-24" style="background-color: var(--t-surface); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
                <div class="pb-4" style="border-bottom: 1px solid var(--t-border);">
                    <p class="text-[10px] font-mono uppercase tracking-widest mb-1" style="color: var(--t-muted);">Unit Price</p>
                    <span id="k71-pdp-price" class="font-mono text-3xl font-extrabold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                    @if($hasDiscount)
                        <span class="ml-2 text-xs line-through font-mono" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>
                    @endif
                </div>

                @include('storefront.themes.pdp._purchase_form', ['variantGrid' => true, 'ctaLabel' => 'Order Now'])
            </div>
        </div>
    </div>
</section>
