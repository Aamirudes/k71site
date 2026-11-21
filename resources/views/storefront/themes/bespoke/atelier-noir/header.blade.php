{{-- ATELIER NOIR BESPOKE HEADER (Nocturnal Haute Couture) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Atelier Noir');
    $storeHomeUrl = $storeHomeUrl ?? route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $cartCount = $cartCount ?? 0;
    $navCategories = ($vendorCategories ?? collect())->take(4);
@endphp

<header class="sticky top-0 z-40 w-full" style="background-color: rgba(8, 8, 10, 0.92); -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); border-bottom: 1px solid rgba(255, 255, 255, 0.08);">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 h-16 sm:h-20 flex items-center justify-between gap-4">
        {{-- Left Navigation (Roman Numeral Lookbook Chapters) --}}
        <div class="flex items-center gap-6">
            <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center -ml-2 text-[#f5f2eb]" aria-label="Open lookbook menu">
                @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
            </button>
            <nav class="hidden lg:flex items-center gap-8 text-[11px] font-mono uppercase tracking-[0.26em] text-[#8a857e]">
                <a href="{{ $storeCollectionUrl }}" class="hover:text-[#f5f2eb] transition">I · EDITIONS</a>
                <a href="{{ $storeCollectionUrl }}" class="hover:text-[#f5f2eb] transition">II · ARCHIVE</a>
                @foreach($navCategories->take(2) as $cIdx => $cat)
                    <a href="{{ $storeCollectionUrl }}?category={{ urlencode($cat->slug) }}" class="hover:text-[#f5f2eb] transition">
                        {{ ['III', 'IV'][$cIdx] ?? 'V' }} · {{ strtoupper($cat->name) }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Center Monogram / Brand Title --}}
        <div class="text-center">
            <a href="{{ $storeHomeUrl }}" class="inline-block group">
                <span class="block text-2xl sm:text-3xl lg:text-4xl font-serif italic tracking-tight text-[#f5f2eb] group-hover:opacity-80 transition">
                    {{ $storeName }}
                </span>
                <span class="block text-[8px] font-mono uppercase tracking-[0.34em] text-[#8a857e] -mt-0.5">
                    PARIS // HAUTE ATELIER
                </span>
            </a>
        </div>

        {{-- Right Controls --}}
        <div class="flex items-center gap-4 text-xs font-mono">
            <button type="button" data-k71-open="k71-search-modal" class="min-w-[40px] min-h-[40px] flex items-center justify-center text-[#8a857e] hover:text-[#f5f2eb] transition" aria-label="Search collection">
                @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </button>
            <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[38px] px-3.5 inline-flex items-center gap-2 border border-white/10 text-[10px] tracking-[0.2em] uppercase text-[#f5f2eb] hover:border-[#f5f2eb] transition">
                <span>BAG</span>
                <span class="text-[#8a857e]">[<span data-k71-cart-count>{{ $cartCount }}</span>]</span>
            </a>
        </div>
    </div>
</header>
