{{-- PDP ARCHITECTURE 1: EDITORIAL FULL-BLEED BANNER + VERTICAL LOOKBOOK DOSSIER --}}
<section data-pdp-layout="editorial-fullbleed" class="w-full">
    {{-- Monumental Full-Bleed Top Campaign Image --}}
    <div class="relative w-full h-[58vh] sm:h-[68vh] overflow-hidden" style="background-color: #0b0b0d;">
        <button type="button" id="k71-pdp-main-trigger" data-k71-lightbox-src="{{ $images->first() }}" data-k71-lightbox-alt="{{ $product->title }}"
                class="block w-full h-full p-0 border-0 bg-transparent cursor-zoom-in">
            <img id="k71-pdp-main-img" src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-cover opacity-85">
        </button>
        <div class="pointer-events-none absolute inset-0" style="background: linear-gradient(180deg, rgba(0,0,0,0.12) 0%, rgba(0,0,0,0.78) 100%);"></div>
        <div class="absolute inset-x-0 bottom-0 max-w-7xl mx-auto px-5 sm:px-10 pb-8 sm:pb-12 flex flex-col sm:flex-row sm:items-end justify-between gap-4 text-white">
            <div class="space-y-1.5">
                <p class="text-[11px] uppercase tracking-[0.26em] text-white/75">{{ $product->category?->name ?? $storeName }} · Editorial Dossier</p>
                <h1 class="k71-heading text-3xl sm:text-5xl lg:text-6xl text-white leading-[0.98]">{{ $product->title }}</h1>
            </div>
            <div class="text-left sm:text-right shrink-0">
                <span id="k71-pdp-price" class="text-2xl sm:text-4xl font-bold tabular-nums text-white">₹{{ number_format($pricePaise / 100, 0) }}</span>
                @if($hasDiscount)
                    <span class="block text-xs line-through text-white/65">₹{{ number_format($comparePaise / 100, 0) }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Asymmetric Editorial Dossier Below --}}
    <div class="max-w-7xl mx-auto px-5 sm:px-10 py-10 sm:py-16 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-start">
        <div class="lg:col-span-7 space-y-6">
            @if(!empty($product->description))
                <div class="space-y-3 pb-6" style="border-bottom: 1px solid var(--t-border);">
                    <p class="k71-eyebrow" style="color: var(--t-accent);">Atelier Notes</p>
                    <p class="text-base sm:text-lg leading-relaxed whitespace-pre-line">{{ strip_tags((string) $product->description) }}</p>
                </div>
            @endif
            @if($images->count() > 1)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($images->skip(1)->take(4) as $idx => $imgUrl)
                        <button type="button" data-k71-lightbox-src="{{ $imgUrl }}" data-k71-lightbox-alt="{{ $product->title }}" class="block aspect-[3/4] overflow-hidden cursor-zoom-in p-0 border-0" style="background-color: var(--t-surface-alt);">
                            <img src="{{ $imgUrl }}" alt="{{ $product->title }} view {{ $idx + 2 }}" loading="lazy" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="lg:col-span-5 lg:sticky lg:top-24 space-y-6 p-6 sm:p-8" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
            <div class="flex items-center justify-between text-xs pb-4" style="border-bottom: 1px solid var(--t-border);">
                <span style="color: var(--t-muted);">{{ $isSoldOut ? 'Unavailable' : "In stock ({$totalStock})" }}</span>
                @if(!empty($product->sku))<span class="font-mono" style="color: var(--t-muted);">{{ $product->sku }}</span>@endif
            </div>

            @include('storefront.themes.pdp._purchase_form', ['ctaLabel' => 'Acquire Piece'])

            @if(!empty($specs))
                <dl class="divide-y text-xs pt-4" style="border-top: 1px solid var(--t-border); border-color: var(--t-border);">
                    @foreach($specs as $k => $v)
                        <div class="py-2.5 flex justify-between gap-4"><dt style="color: var(--t-muted);">{{ $k }}</dt><dd class="font-semibold text-right">{{ $v }}</dd></div>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>
</section>
