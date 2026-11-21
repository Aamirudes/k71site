{{-- OBJECT GALLERY BESPOKE HEADER (Museum Exhibition & Curatorial Pacing) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Object Gallery');
    $storeHomeUrl = $storeHomeUrl ?? route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $cartCount = $cartCount ?? 0;
    $navCategories = ($vendorCategories ?? collect())->take(4);
@endphp

<header class="sticky top-0 z-40 w-full bg-white/95 backdrop-blur-md border-b border-black/[0.08]">
    {{-- Top Exhibition Coordinate Hairline --}}
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 py-2 flex items-center justify-between text-[10px] font-mono uppercase tracking-[0.22em] text-[#767676] border-b border-black/[0.05]">
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 rounded-full bg-black"></span>
            <span>GALLERY EXHIBITION · PAVILION I</span>
        </div>
        <span>ACCESSION YEAR: {{ date('Y') }}</span>
        <span class="hidden sm:inline">PERMANENT COLLECTION</span>
    </div>

    {{-- Main Gallery Header Spread --}}
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 h-16 sm:h-20 flex items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center -ml-2 text-black" aria-label="Open gallery index">
                @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
            </button>
            <a href="{{ $storeHomeUrl }}" class="flex flex-col">
                <span class="text-2xl sm:text-3xl font-serif tracking-tight text-[#111111]">
                    {{ $storeName }}
                </span>
                <span class="text-[8px] font-mono uppercase tracking-[0.28em] text-[#767676] -mt-0.5">
                    MUSEUM OF UTILITARIAN OBJECTS
                </span>
            </a>
        </div>

        <nav class="hidden lg:flex items-center gap-8 text-[11px] font-mono uppercase tracking-[0.22em] text-[#767676]" aria-label="Exhibition rooms">
            <a href="{{ $storeHomeUrl }}" class="hover:text-black transition">ROOM 00 · OVERVIEW</a>
            <a href="{{ $storeCollectionUrl }}" class="hover:text-black transition">ROOM 01 · COMPLETE INDEX</a>
            @foreach($navCategories->take(2) as $cIdx => $cat)
                <a href="{{ $storeCollectionUrl }}?category={{ urlencode($cat->slug) }}" class="hover:text-black transition">
                    ROOM {{ str_pad((string)($cIdx + 2), 2, '0', STR_PAD_LEFT) }} · {{ strtoupper($cat->name) }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-3">
            <button type="button" data-k71-open="k71-search-modal" class="min-w-[40px] min-h-[40px] flex items-center justify-center text-[#767676] hover:text-black transition" aria-label="Search exhibits">
                @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </button>
            <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[38px] px-4 inline-flex items-center gap-2 border border-black/15 text-[10px] font-mono tracking-widest uppercase text-black hover:bg-black hover:text-white transition">
                <span>ACQUISITION</span>
                <span>(<span data-k71-cart-count>{{ $cartCount }}</span>)</span>
            </a>
        </div>
    </div>
</header>
