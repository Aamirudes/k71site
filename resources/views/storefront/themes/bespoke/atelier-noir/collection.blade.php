{{-- ATELIER NOIR BESPOKE COLLECTION PAGE --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Atelier Noir');
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $currentSearch = trim((string) request('search', ''));
    $currentCategory = trim((string) ($activeCategorySlug ?? request('category', '')));
    $currentSort = trim((string) request('sort', 'newest'));
    $activeCatObj = ($vendorCategories ?? collect())->firstWhere('slug', $currentCategory);
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'QUERY // "' . $currentSearch . '"' : 'COMPLETE LOOKBOOK ARCHIVE');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<div class="w-full bg-[#08080a] text-[#f5f2eb] py-10 sm:py-16">
    <div class="max-w-[1560px] mx-auto px-5 sm:px-10 space-y-10">
        {{-- Lookbook Header Spread --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 pb-8 border-b border-white/10">
            <div class="space-y-2">
                <span class="text-[10px] font-mono uppercase tracking-[0.3em] text-[#8a857e]">CATALOGUE // VOL. IV</span>
                <h1 class="text-3xl sm:text-5xl font-serif italic tracking-tight">{{ $collectionTitle }}</h1>
                <p class="text-xs font-mono text-[#8a857e]">{{ $totalCount }} CATALOGUED EDITIONS IN ARCHIVE</p>
            </div>

            <form action="{{ $storeCollectionUrl }}" method="GET" class="flex flex-wrap items-center gap-3">
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter archive..."
                       class="min-h-[42px] px-4 text-xs font-mono bg-[#111114] border border-white/10 text-[#f5f2eb] placeholder-[#8a857e] focus:outline-none focus:border-white/40">
                <select name="sort" onchange="this.form.submit()" aria-label="Sort lookbook"
                        class="min-h-[42px] px-3 text-xs font-mono bg-[#111114] border border-white/10 text-[#f5f2eb] focus:outline-none">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Index: Chrono</option>
                    <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Valuation: Asc</option>
                    <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Valuation: Desc</option>
                </select>
            </form>
        </div>

        {{-- Roman Numeral Category Navigation Strip --}}
        <nav class="flex items-center gap-6 overflow-x-auto k71-no-scrollbar text-xs font-mono uppercase tracking-widest pb-3 border-b border-white/10 text-[#8a857e]">
            <a href="{{ $storeCollectionUrl }}" class="shrink-0 hover:text-white transition {{ $currentCategory === '' ? 'text-white underline underline-offset-8' : '' }}">
                00 · ALL EDITIONS
            </a>
            @foreach(($vendorCategories ?? collect()) as $cIdx => $cat)
                <a href="{{ $storeCollectionUrl }}?category={{ urlencode($cat->slug) }}"
                   class="shrink-0 hover:text-white transition {{ $currentCategory === $cat->slug ? 'text-white underline underline-offset-8' : '' }}">
                    {{ str_pad((string)($cIdx + 1), 2, '0', STR_PAD_LEFT) }} · {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        {{-- Staggered Lookbook Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-10">
                @foreach($products as $product)
                    @include('storefront.themes.bespoke.atelier-noir.card', ['product' => $product])
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-10 flex justify-center text-xs font-mono">{{ $products->links() }}</div>
            @endif
        @else
            <div class="py-24 text-center border border-white/10 space-y-3">
                <p class="text-xs font-mono uppercase tracking-widest text-[#8a857e]">Archive Note</p>
                <h3 class="text-xl font-serif italic">No catalogued pieces found in this edition</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !text-xs font-mono">Reset Filter</a>
            </div>
        @endif
    </div>
</div>
