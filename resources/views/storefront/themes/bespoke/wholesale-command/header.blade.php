{{-- WHOLESALE COMMAND BESPOKE HEADER (High-Density B2B Terminal) --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Wholesale Command');
    $storeHomeUrl = $storeHomeUrl ?? route('storefront.show', ['slug' => $vendor->slug]);
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $cartCount = $cartCount ?? 0;
    $navCategories = ($vendorCategories ?? collect())->take(5);
@endphp

<header class="sticky top-0 z-40 w-full bg-[#0b0f19] text-[#f9fafb] border-b border-[#1f2937]">
    {{-- Telemetry Ticker Ribbon --}}
    <div class="w-full bg-[#070a10] border-b border-[#1f2937] py-1.5 px-4 sm:px-8 flex items-center justify-between text-[10px] font-mono tracking-widest text-[#9ca3af]">
        <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-white font-bold">TERMINAL ONLINE</span>
            <span class="hidden sm:inline">· WAREHOUSE DISPATCH 24H</span>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sky-400 font-bold">TIER DISCOUNTS: UP TO 40% OFF</span>
            <span class="hidden md:inline">MERCHANT ID: #{{ str_pad((string)$vendor->id, 4, '0', STR_PAD_LEFT) }}</span>
        </div>
    </div>

    {{-- Main Command Bar --}}
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 py-3 flex items-center justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[40px] min-h-[40px] flex items-center justify-center -ml-2 text-white border border-[#1f2937] rounded" aria-label="Open trade menu">
                @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
            </button>
            <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2 min-w-0">
                <span class="px-2 py-0.5 rounded bg-sky-600 text-white text-[10px] font-mono font-bold uppercase tracking-wider shrink-0">B2B</span>
                <span class="text-lg sm:text-2xl font-mono font-bold tracking-tight text-white truncate">{{ $storeName }}</span>
            </a>
        </div>

        {{-- Direct SKU Search --}}
        <form action="{{ $storeCollectionUrl }}" method="GET" class="hidden md:flex flex-1 max-w-md mx-4">
            <div class="relative w-full flex">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Lookup SKU, Material, Lot..."
                       class="w-full min-h-[38px] px-3.5 font-mono text-xs bg-[#111827] border border-[#374151] rounded-l text-white focus:outline-none focus:border-sky-500">
                <button type="submit" class="min-h-[38px] px-4 bg-sky-600 hover:bg-sky-500 text-white text-xs font-mono font-bold uppercase rounded-r shrink-0">
                    SCAN SKU
                </button>
            </div>
        </form>

        <div class="flex items-center gap-2.5 shrink-0">
            <button type="button" data-k71-open="k71-search-modal" class="md:hidden min-w-[38px] min-h-[38px] flex items-center justify-center border border-[#1f2937] rounded text-zinc-300" aria-label="Search">
                @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
            </button>
            <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[38px] px-3.5 inline-flex items-center gap-2 bg-[#111827] border border-[#374151] hover:border-sky-500 rounded text-xs font-mono font-bold text-white transition">
                <span class="w-2 h-2 rounded-full bg-sky-400"></span>
                <span>LOT ORDER</span>
                <span class="px-1.5 py-0.2 rounded bg-sky-950 text-sky-300 border border-sky-800" data-k71-cart-count>{{ $cartCount }}</span>
            </a>
        </div>
    </div>

    {{-- Category Lot Rail --}}
    @if($navCategories->isNotEmpty())
        <div class="bg-[#111827] border-t border-[#1f2937] py-2 px-4 sm:px-8 flex items-center gap-2 overflow-x-auto k71-no-scrollbar font-mono text-[11px]">
            <span class="text-zinc-500 shrink-0 font-bold">DEPT:</span>
            <a href="{{ $storeCollectionUrl }}" class="px-2.5 py-1 rounded bg-sky-600 text-white shrink-0 font-bold">ALL LOTS</a>
            @foreach($navCategories as $cat)
                <a href="{{ $storeCollectionUrl }}?category={{ urlencode($cat->slug) }}" class="px-2.5 py-1 rounded bg-[#1f2937] text-zinc-300 hover:text-white shrink-0">
                    {{ strtoupper($cat->name) }}
                </a>
            @endforeach
        </div>
    @endif
</header>
