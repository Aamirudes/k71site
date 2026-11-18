{{-- PDP ARCHITECTURE 5: RAW 2PX-BORDERED BRUTALIST POSTER SPEC SHEET --}}
<section data-pdp-layout="brutalist-poster-sheet" class="w-full py-6 sm:py-10">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <div style="border: 2px solid var(--t-text); background-color: var(--t-surface);">
            {{-- Giant Full-Width Uppercase Poster Title Bar --}}
            <div class="p-5 sm:p-8 flex flex-wrap items-end justify-between gap-4" style="border-bottom: 2px solid var(--t-text);">
                <div>
                    <span class="inline-block px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.2em] mb-2" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                        {{ $product->category?->name ?? 'DROP' }} // {{ $product->sku ?: 'LTD' }}
                    </span>
                    <h1 class="k71-heading text-4xl sm:text-6xl uppercase font-black leading-[0.9] tracking-tight">{{ $product->title }}</h1>
                </div>
                <div class="px-5 py-3 font-mono text-2xl sm:text-4xl font-black" style="background-color: var(--t-text); color: var(--t-bg);">
                    <span id="k71-pdp-price">₹{{ number_format($pricePaise / 100, 0) }}</span>
                </div>
            </div>

            {{-- Thick 2px Divided Image & Raw Purchase Block --}}
            <div class="grid grid-cols-1 lg:grid-cols-12">
                <div class="lg:col-span-7 lg:border-r-2" style="border-color: var(--t-text); background-color: var(--t-surface-alt);">
                    <button type="button" id="k71-pdp-main-trigger" data-k71-lightbox-src="{{ $images->first() }}" data-k71-lightbox-alt="{{ $product->title }}"
                            class="block w-full aspect-square sm:aspect-[4/3] overflow-hidden p-0 border-0 bg-transparent cursor-zoom-in">
                        <img id="k71-pdp-main-img" src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                    </button>
                </div>

                <div class="lg:col-span-5 p-6 sm:p-8 flex flex-col justify-between gap-6">
                    <div class="space-y-5">
                        @include('storefront.themes.pdp._purchase_form', [
                            'variantHeading' => 'SELECT SIZE / SPEC',
                            'variantGrid' => true,
                            'variantBtnClass' => 'rounded-none',
                            'qtyBoxClass' => 'rounded-none',
                            'ctaClass' => 'rounded-none uppercase tracking-widest font-black',
                            'ctaLabel' => 'LOCK IN BAG',
                        ])

                        @if(!empty($product->description))
                            <div class="pt-5" style="border-top: 2px solid var(--t-text);">
                                <p class="text-xs font-mono uppercase font-black mb-2">RELEASE NOTES</p>
                                <p class="text-sm leading-relaxed">{{ strip_tags((string) $product->description) }}</p>
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 grid grid-cols-2 gap-3 text-xs font-mono" style="border-top: 2px solid var(--t-text);">
                        <div><strong>STOCK:</strong> {{ $isSoldOut ? 'SOLD OUT' : "{$totalStock} UNITS" }}</div>
                        <div><strong>RETURNS:</strong> {{ $isReturnable && $returnDays > 0 ? "{$returnDays} DAYS" : 'POLICY' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
