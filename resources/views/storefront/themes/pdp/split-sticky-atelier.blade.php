{{-- PDP ARCHITECTURE 2: EDGE-TO-EDGE 50/50 FULL-BLEED SPLIT-SCREEN ATELIER --}}
<section data-pdp-layout="split-sticky-atelier" class="w-full grid grid-cols-1 lg:grid-cols-2" style="border-bottom: 1px solid var(--t-border);">
    {{-- Left 50% Edge-to-Edge Stacked Photography --}}
    <div class="flex flex-col divide-y" style="border-right: 1px solid var(--t-border); border-color: var(--t-border); background-color: var(--t-surface-alt);">
        @foreach($images->take(4) as $idx => $imgUrl)
            <button type="button" @if($idx === 0) id="k71-pdp-main-trigger" @endif
                    data-k71-lightbox-src="{{ $imgUrl }}" data-k71-lightbox-alt="{{ $product->title }}"
                    class="block w-full aspect-[4/4] lg:min-h-[82vh] overflow-hidden p-0 border-0 bg-transparent cursor-zoom-in">
                <img @if($idx === 0) id="k71-pdp-main-img" @endif src="{{ $imgUrl }}" alt="{{ $product->title }} angle {{ $idx + 1 }}" class="w-full h-full object-cover">
            </button>
        @endforeach
    </div>

    {{-- Right 50% Pinned Atelier Purchase Sanctuary --}}
    <div class="lg:sticky lg:top-0 lg:min-h-screen flex flex-col justify-center p-6 sm:p-12 lg:p-16 xl:p-20" style="background-color: var(--t-surface);">
        <div class="max-w-lg w-full mx-auto space-y-6">
            <nav class="text-[11px] uppercase tracking-[0.18em] flex items-center gap-2" style="color: var(--t-muted);">
                <a href="{{ $storeHomeUrl }}" class="hover:opacity-70">Store</a>
                <span>/</span>
                <a href="{{ $storeCollectionUrl }}" class="hover:opacity-70">{{ $product->category?->name ?? 'Collection' }}</a>
            </nav>

            <div class="space-y-3">
                <h1 class="k71-heading text-3xl sm:text-5xl leading-[1.02]">{{ $product->title }}</h1>
                <div class="flex items-baseline gap-3">
                    <span id="k71-pdp-price" class="text-2xl sm:text-3xl font-bold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                    @if($hasDiscount)
                        <span class="text-sm line-through" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>
                    @endif
                </div>
            </div>

            @if(!empty($product->description))
                <p class="text-sm leading-relaxed whitespace-pre-line" style="color: var(--t-muted);">{{ strip_tags((string) $product->description) }}</p>
            @endif

            @include('storefront.themes.pdp._purchase_form', ['ctaLabel' => 'Add to Bag'])

            @if(!empty($specs))
                <div class="pt-6 space-y-2" style="border-top: 1px solid var(--t-border);">
                    <p class="k71-eyebrow" style="color: var(--t-muted);">Material &amp; Provenance</p>
                    <dl class="divide-y text-xs" style="border-color: var(--t-border);">
                        @foreach($specs as $k => $v)
                            <div class="py-2 flex justify-between gap-4"><dt style="color: var(--t-muted);">{{ $k }}</dt><dd class="font-semibold">{{ $v }}</dd></div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>
</section>
