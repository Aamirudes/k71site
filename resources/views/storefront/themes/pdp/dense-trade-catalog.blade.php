{{-- PDP ARCHITECTURE 6: HIGH-CLARITY COMMERCIAL / GROCERY / B2B TRADE PDP --}}
<section data-pdp-layout="dense-trade-catalog" class="w-full py-6 sm:py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 space-y-8">
        <nav class="text-xs flex flex-wrap items-center gap-2" style="color: var(--t-muted);">
            <a href="{{ $storeHomeUrl }}" class="hover:underline">Home</a><span>/</span>
            <a href="{{ $storeCollectionUrl }}" class="hover:underline">Catalog</a><span>/</span>
            <span style="color: var(--t-text);">{{ $product->title }}</span>
        </nav>

        <div class="p-5 sm:p-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start" style="background-color: var(--t-surface); border: 1px solid var(--t-border); border-radius: var(--t-radius);">
            <div class="lg:col-span-5 space-y-3">
                <div class="aspect-[4/3] overflow-hidden rounded-lg" style="background-color: var(--t-surface-alt); border: 1px solid var(--t-border);">
                    <button type="button" id="k71-pdp-main-trigger" data-k71-lightbox-src="{{ $images->first() }}" data-k71-lightbox-alt="{{ $product->title }}" class="block w-full h-full p-0 border-0 bg-transparent cursor-zoom-in">
                        <img id="k71-pdp-main-img" src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                    </button>
                </div>
                @if($images->count() > 1)
                    <div class="flex gap-2 overflow-x-auto">
                        @foreach($images->take(6) as $imgUrl)
                            <button type="button" onclick="document.getElementById('k71-pdp-main-img').src=this.dataset.src;" data-src="{{ $imgUrl }}" class="w-16 h-16 shrink-0 rounded overflow-hidden cursor-pointer p-0" style="border: 1px solid var(--t-border);">
                                <img src="{{ $imgUrl }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="lg:col-span-7 space-y-5">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <span class="px-2.5 py-0.5 rounded font-bold" style="background-color: var(--t-surface-alt); color: var(--t-accent);">{{ $product->category?->name ?? 'Catalog' }}</span>
                        @if(!empty($product->sku))<span class="font-mono" style="color: var(--t-muted);">SKU: {{ $product->sku }}</span>@endif
                        <span class="font-semibold" style="color: {{ $isSoldOut ? '#dc2626' : '#15803d' }};">{{ $isSoldOut ? 'Out of Stock' : "In Stock ({$totalStock})" }}</span>
                    </div>
                    <h1 class="k71-heading text-2xl sm:text-3xl font-extrabold">{{ $product->title }}</h1>
                </div>

                <div class="p-4 rounded-lg flex flex-wrap items-baseline justify-between gap-4" style="background-color: var(--t-bg); border: 1px solid var(--t-border);">
                    <div>
                        <span id="k71-pdp-price" class="text-3xl font-extrabold tabular-nums">₹{{ number_format($pricePaise / 100, 0) }}</span>
                        @if($hasDiscount)
                            <span class="ml-2 text-sm line-through" style="color: var(--t-muted);">₹{{ number_format($comparePaise / 100, 0) }}</span>
                            <span class="ml-2 text-xs font-bold px-2 py-0.5 rounded" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">Save {{ $discountPct }}%</span>
                        @endif
                    </div>
                    <span class="text-xs" style="color: var(--t-muted);">Fulfilled by {{ $storeName }}</span>
                </div>

                @include('storefront.themes.pdp._purchase_form', ['ctaLabel' => 'Add to Cart'])

                @if(!empty($product->description) || !empty($specs))
                    <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-6" style="border-top: 1px solid var(--t-border);">
                        @if(!empty($product->description))
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--t-muted);">Product Information</h2>
                                <p class="text-xs sm:text-sm leading-relaxed">{{ strip_tags((string) $product->description) }}</p>
                            </div>
                        @endif
                        @if(!empty($specs))
                            <div>
                                <h2 class="text-xs font-bold uppercase tracking-wider mb-2" style="color: var(--t-muted);">Specifications</h2>
                                <dl class="divide-y text-xs" style="border-color: var(--t-border);">
                                    @foreach($specs as $k => $v)
                                        <div class="py-1.5 flex justify-between gap-3"><dt style="color: var(--t-muted);">{{ $k }}</dt><dd class="font-semibold">{{ $v }}</dd></div>
                                    @endforeach
                                </dl>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
