{{-- PDP ARCHITECTURE 4: CENTERED MUSEUM EXHIBITION PLINTH + HORIZONTAL ACQUISITION BAR --}}
<section data-pdp-layout="gallery-plinth-museum" class="w-full py-10 sm:py-16">
    <div class="max-w-5xl mx-auto px-5 sm:px-10 space-y-10">
        <div class="text-center space-y-2">
            <p class="text-[11px] font-mono uppercase tracking-[0.24em]" style="color: var(--t-muted);">Exhibition Work · {{ $product->category?->name ?? $storeName }}</p>
            <h1 class="k71-heading text-3xl sm:text-5xl">{{ $product->title }}</h1>
        </div>

        {{-- Giant Centered Framed Plinth Artwork --}}
        <div class="p-6 sm:p-12 mx-auto max-w-3xl" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
            <button type="button" id="k71-pdp-main-trigger" data-k71-lightbox-src="{{ $images->first() }}" data-k71-lightbox-alt="{{ $product->title }}"
                    class="block w-full aspect-[4/3] overflow-hidden p-0 border-0 bg-transparent cursor-zoom-in">
                <img id="k71-pdp-main-img" src="{{ $images->first() }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
            </button>
        </div>

        {{-- Horizontal 3-Column Museum Acquisition Bar --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 pt-8" style="border-top: 1px solid var(--t-border);">
            <div class="lg:col-span-4 space-y-2">
                <p class="k71-eyebrow" style="color: var(--t-muted);">Curatorial Note</p>
                <p class="text-sm leading-relaxed">{{ strip_tags((string) ($product->description ?: 'Presented from the permanent collection of ' . $storeName . '.')) }}</p>
            </div>

            <div class="lg:col-span-3 space-y-2">
                <p class="k71-eyebrow" style="color: var(--t-muted);">Provenance</p>
                <dl class="text-xs space-y-1.5 font-mono">
                    <div><span style="color: var(--t-muted);">Price:</span> <strong id="k71-pdp-price" class="text-base">₹{{ number_format($pricePaise / 100, 0) }}</strong></div>
                    <div><span style="color: var(--t-muted);">Availability:</span> {{ $isSoldOut ? 'Unavailable' : "{$totalStock} in gallery" }}</div>
                    @foreach(array_slice($specs, 0, 3) as $k => $v)
                        <div><span style="color: var(--t-muted);">{{ $k }}:</span> {{ $v }}</div>
                    @endforeach
                </dl>
            </div>

            <div class="lg:col-span-5">
                @include('storefront.themes.pdp._purchase_form', ['ctaLabel' => 'Acquire Work'])
            </div>
        </div>
    </div>
</section>
