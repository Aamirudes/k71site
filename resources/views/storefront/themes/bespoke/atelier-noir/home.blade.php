{{-- ATELIER NOIR BESPOKE HOMEPAGE (Nocturnal Haute Couture Lookbook) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Atelier Noir');
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $allProducts = (isset($products) && method_exists($products, 'items')) ? collect($products->items())->values() : collect($products ?? [])->values();
    $leadProduct = $allProducts->first();
    $pair1 = $allProducts->slice(0, 2)->values();
    $pair2 = $allProducts->slice(2, 2)->values();
    $heroImg = $leadProduct?->images?->first()?->url ?? asset('images/theme-editorial/fashion-editorial.jpg');
    $prodUrl = fn ($p) => route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]) . (!empty($isPreview) ? '?preview=1' : '');
@endphp

<div class="w-full bg-[#08080a] text-[#f5f2eb]">
    {{-- SECTION 1: NOCTURNAL RUNWAY CURTAIN HERO --}}
    <section class="relative min-h-[85vh] lg:min-h-[92vh] flex items-end overflow-hidden border-b border-white/10">
        <div class="absolute inset-0">
            <img src="{{ $heroImg }}" alt="{{ $storeName }} Lookbook" class="w-full h-full object-cover object-center opacity-65">
            <div class="absolute inset-0 bg-gradient-to-t from-[#08080a] via-[#08080a]/40 to-transparent"></div>
        </div>

        <div class="relative z-10 max-w-[1560px] mx-auto px-5 sm:px-10 pb-16 sm:pb-24 w-full">
            <div class="max-w-2xl space-y-4">
                <span class="inline-block text-[10px] font-mono uppercase tracking-[0.32em] text-[#8a857e] px-2.5 py-1 border border-white/10 bg-black/40 backdrop-blur-md">
                    CAMPAIGN COLLECTION · VOL. IV
                </span>
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-serif italic tracking-tight leading-[0.94] text-[#f5f2eb]">
                    The Anatomy of Restraint.
                </h1>
                <p class="text-xs sm:text-sm text-[#8a857e] font-sans leading-relaxed max-w-lg">
                    {{ strip_tags((string)($vendor->description ?: 'An investigation into silence, negative space, and severe architectural tailoring.')) }}
                </p>
                <div class="pt-4 flex flex-wrap items-center gap-4">
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !px-8 !py-3.5 !text-[11px] !tracking-[0.22em]">
                        EXPLORE COLLECTION →
                    </a>
                    @if($leadProduct)
                        <a href="{{ $prodUrl($leadProduct) }}" class="text-xs font-mono tracking-widest text-[#8a857e] hover:text-[#f5f2eb] underline underline-offset-8 transition">
                            VIEW HERO PIECE [₹{{ number_format(($leadProduct->base_price ?? 0)/100, 0) }}]
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- SECTION 2: ASYMMETRIC 7/5 LOOKBOOK PAIR --}}
    @if($pair1->count() >= 2)
        <section class="py-16 sm:py-28 px-5 sm:px-10 max-w-[1560px] mx-auto border-b border-white/10">
            <div class="flex items-baseline justify-between pb-8 mb-10 border-b border-white/10 text-[10px] font-mono tracking-[0.28em] text-[#8a857e]">
                <span>LOOKBOOK SEQUENCE // PLATE 01 &amp; 02</span>
                <span>AUTUMN / WINTER</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-14 items-center">
                {{-- Left Item (7 cols, wide) --}}
                <div class="lg:col-span-7 space-y-4">
                    @php $pA = $pair1[0]; @endphp
                    <a href="{{ $prodUrl($pA) }}" class="block aspect-[4/5] overflow-hidden bg-[#111114] border border-white/10 group">
                        <img src="{{ $pA->images?->first()?->url ?? $heroImg }}" alt="{{ $pA->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                    </a>
                    <div class="flex items-baseline justify-between gap-4 pt-2">
                        <div>
                            <span class="text-[9px] font-mono uppercase tracking-widest text-[#8a857e]">EDITION NO. 01</span>
                            <h2 class="text-xl sm:text-2xl font-serif italic text-[#f5f2eb]"><a href="{{ $prodUrl($pA) }}">{{ $pA->title }}</a></h2>
                        </div>
                        <span class="font-mono text-sm text-[#f5f2eb]">₹{{ number_format(($pA->base_price ?? 0)/100, 0) }}</span>
                    </div>
                </div>

                {{-- Right Item (5 cols, taller offset) --}}
                <div class="lg:col-span-5 space-y-4 lg:pt-16">
                    @php $pB = $pair1[1]; @endphp
                    <a href="{{ $prodUrl($pB) }}" class="block aspect-[3/4] overflow-hidden bg-[#111114] border border-white/10 group">
                        <img src="{{ $pB->images?->first()?->url ?? $heroImg }}" alt="{{ $pB->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-700">
                    </a>
                    <div class="flex items-baseline justify-between gap-4 pt-2">
                        <div>
                            <span class="text-[9px] font-mono uppercase tracking-widest text-[#8a857e]">EDITION NO. 02</span>
                            <h2 class="text-lg sm:text-xl font-serif italic text-[#f5f2eb]"><a href="{{ $prodUrl($pB) }}">{{ $pB->title }}</a></h2>
                        </div>
                        <span class="font-mono text-sm text-[#f5f2eb]">₹{{ number_format(($pB->base_price ?? 0)/100, 0) }}</span>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- SECTION 3: ATELIER MANIFESTO QUOTE --}}
    <section class="py-20 sm:py-28 px-6 text-center border-b border-white/10 bg-[#0c0c0e]">
        <div class="max-w-3xl mx-auto space-y-6">
            <span class="text-[10px] font-mono uppercase tracking-[0.32em] text-[#8a857e]">PHILOSOPHY</span>
            <blockquote class="text-2xl sm:text-4xl font-serif italic text-[#f5f2eb] leading-snug">
                &ldquo;Form stripped of excess. Material elevated to art. Every piece is an edition, never a mass product.&rdquo;
            </blockquote>
            <p class="text-xs font-mono uppercase tracking-widest text-[#8a857e]">— {{ $storeName }} Curatorial Board</p>
        </div>
    </section>

    {{-- SECTION 4: COMPLETE CATALOGUE GRID --}}
    <section class="py-16 sm:py-24 px-5 sm:px-10 max-w-[1560px] mx-auto">
        <div class="flex items-baseline justify-between pb-6 mb-10 border-b border-white/10">
            <div>
                <span class="text-[10px] font-mono uppercase tracking-[0.28em] text-[#8a857e]">CURRENT ARCHIVE</span>
                <h2 class="text-3xl sm:text-4xl font-serif italic text-[#f5f2eb]">All Catalogued Works</h2>
            </div>
            <a href="{{ $storeCollectionUrl }}" class="text-xs font-mono tracking-widest text-[#8a857e] hover:text-[#f5f2eb] transition">
                VIEW ENTIRE ARCHIVE →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($allProducts->take(6) as $product)
                @include('storefront.themes.bespoke.atelier-noir.card', ['product' => $product])
            @endforeach
        </div>
    </section>
</div>
