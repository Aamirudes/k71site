{{-- OBJECT GALLERY BESPOKE HOMEPAGE (Museum Exhibition Hall & Curatorial Plinths) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Object Gallery');
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $leadProduct = $allProducts->first();
    $heroImg = $leadProduct?->images?->first()?->url ?? asset('images/theme-editorial/craft-pottery.jpg');
    $prodUrl = fn ($p) => route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]) . (!empty($isPreview) ? '?preview=1' : '');
@endphp

<div class="w-full bg-[#ffffff] text-[#111111]">
    {{-- SECTION 1: EXPANSIVE MUSEUM PLINTH HERO --}}
    <section class="py-12 sm:py-24 px-5 sm:px-10 max-w-[1560px] mx-auto border-b border-black/[0.08]">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-16 items-center">
            {{-- Left Dossier (5 cols) --}}
            <div class="lg:col-span-5 space-y-6 order-2 lg:order-1">
                <div class="space-y-2">
                    <span class="text-[10px] font-mono uppercase tracking-[0.28em] text-[#767676] block">
                        ACCESSION NO. {{ date('Y') }}.01 · ROTUNDA
                    </span>
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-serif tracking-tight text-[#111111] leading-[1.05]">
                        Utilitarian Form in Pure Light.
                    </h1>
                </div>

                <p class="text-xs sm:text-sm text-[#767676] font-sans leading-relaxed">
                    {{ strip_tags((string)($vendor->description ?: 'Objects curated for spatial permanence, structural discipline, and tactile honesty. Each piece is preserved with archival dignity.')) }}
                </p>

                <div class="pt-2 flex flex-wrap items-center gap-4">
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !px-7 !py-3 !text-[11px] !tracking-[0.18em]">
                        VIEW EXHIBITION INDEX →
                    </a>
                    @if($leadProduct)
                        <a href="{{ $prodUrl($leadProduct) }}" class="text-xs font-mono text-[#767676] hover:text-black underline underline-offset-4">
                            EXAMINE PIECE · ₹{{ number_format(($leadProduct->base_price ?? 0)/100, 0) }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- Right Floating Plinth Image (7 cols) --}}
            <div class="lg:col-span-7 order-1 lg:order-2">
                <div class="p-8 sm:p-16 bg-[#fbfbfa] border border-black/[0.08] flex items-center justify-center">
                    <div class="aspect-square w-full max-w-lg overflow-hidden bg-white shadow-sm border border-black/[0.05]">
                        <img src="{{ $heroImg }}" alt="{{ $storeName }} Curatorial Piece" class="w-full h-full object-cover">
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 2: CURATORIAL MANIFESTO STATEMENT --}}
    <section class="py-16 sm:py-24 px-6 text-center border-b border-black/[0.08] bg-[#fafaf8]">
        <div class="max-w-3xl mx-auto space-y-4">
            <span class="text-[10px] font-mono uppercase tracking-[0.3em] text-[#767676]">CURATORIAL PROVENANCE</span>
            <blockquote class="text-xl sm:text-3xl font-serif text-[#111111] leading-relaxed">
                &ldquo;An assembly of utilitarian forms elevated through restraint, material honesty, and spatial harmony.&rdquo;
            </blockquote>
            <p class="text-[11px] font-mono uppercase tracking-widest text-[#767676]">— {{ $storeName }} Department of Architecture</p>
        </div>
    </section>

    {{-- SECTION 3: 2-COLUMN PLINTH EXHIBITION WALL --}}
    <section class="py-16 sm:py-24 px-5 sm:px-10 max-w-[1560px] mx-auto">
        <div class="flex items-baseline justify-between pb-6 mb-12 border-b border-black/[0.08]">
            <div>
                <span class="text-[10px] font-mono uppercase tracking-[0.28em] text-[#767676]">PAVILION CATALOGUE</span>
                <h2 class="text-3xl sm:text-4xl font-serif text-[#111111]">Curated Objects on Plinth</h2>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="text-xs font-mono uppercase tracking-widest text-[#767676] hover:text-black transition">
                COMPLETE CATALOGUE [{{ $allProducts->count() }}] →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 lg:gap-14">
            @foreach($allProducts->take(6) as $product)
                @include('storefront.themes.bespoke.object-gallery.card', ['product' => $product])
            @endforeach
        </div>
    </section>
</div>
