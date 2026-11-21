{{-- OBJECT GALLERY BESPOKE COLLECTION PAGE --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Object Gallery');
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $currentSearch = trim((string) request('search', ''));
    $currentCategory = trim((string) ($activeCategorySlug ?? request('category', '')));
    $currentSort = trim((string) request('sort', 'newest'));
    $activeCatObj = ($vendorCategories ?? collect())->firstWhere('slug', $currentCategory);
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'EXHIBIT SEARCH: "' . $currentSearch . '"' : 'PERMANENT EXHIBITION CATALOGUE');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<div class="w-full bg-[#ffffff] text-[#111111] py-10 sm:py-16">
    <div class="max-w-[1560px] mx-auto px-5 sm:px-10 space-y-10">
        {{-- Header Spread --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 pb-6 border-b border-black/[0.08]">
            <div class="space-y-1.5">
                <span class="text-[10px] font-mono uppercase tracking-[0.26em] text-[#767676]">PAVILION ACCESSION INDEX</span>
                <h1 class="text-3xl sm:text-5xl font-serif tracking-tight">{{ $collectionTitle }}</h1>
                <p class="text-xs font-mono text-[#767676]">{{ $totalCount }} PIECES IN ARCHIVAL RECORD</p>
            </div>

            <form action="{{ $storeCollectionUrl }}" method="GET" class="flex flex-wrap items-center gap-3">
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter exhibits..."
                       class="min-h-[40px] px-3.5 text-xs font-mono bg-[#fbfbfa] border border-black/15 text-[#111111] placeholder-[#767676] focus:outline-none focus:border-black">
                <select name="sort" onchange="this.form.submit()" aria-label="Sort exhibits"
                        class="min-h-[40px] px-3 text-xs font-mono bg-[#fbfbfa] border border-black/15 text-[#111111] focus:outline-none">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Index: Chrono</option>
                    <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Valuation: Asc</option>
                    <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Valuation: Desc</option>
                </select>
            </form>
        </div>

        {{-- Left Exhibition Aside + Plinth Grid Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            {{-- Persistent Left Exhibition Rail (3 cols on desktop) --}}
            <aside class="lg:col-span-3 lg:sticky lg:top-28 space-y-8">
                <div class="space-y-3 pb-6 border-b border-black/[0.08]">
                    <span class="text-[10px] font-mono uppercase tracking-[0.24em] text-black font-bold block">PAVILION DIRECTORY</span>
                    <nav class="flex flex-col gap-2 font-mono text-xs">
                        <a href="{{ $storeCollectionUrl }}" class="flex items-center justify-between py-1.5 hover:text-black {{ $currentCategory === '' ? 'font-bold text-black underline underline-offset-4' : 'text-[#767676]' }}">
                            <span>ROOM 00 · ALL EXHIBITS</span>
                            <span>{{ $totalCount }}</span>
                        </a>
                        @foreach(($vendorCategories ?? collect()) as $cIdx => $cat)
                            <a href="{{ $storeCollectionUrl }}?category={{ urlencode($cat->slug) }}"
                               class="flex items-center justify-between py-1.5 hover:text-black {{ $currentCategory === $cat->slug ? 'font-bold text-black underline underline-offset-4' : 'text-[#767676]' }}">
                                <span class="truncate">ROOM {{ str_pad((string)($cIdx + 1), 2, '0', STR_PAD_LEFT) }} · {{ $cat->name }}</span>
                                <span class="text-[10px]">{{ $cat->products_count ?? '' }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>

                <div class="space-y-2 text-xs text-[#767676]">
                    <p class="font-mono text-[10px] uppercase tracking-widest text-black font-bold">Curatorial Note</p>
                    <p class="font-serif italic leading-relaxed">
                        {{ strip_tags((string)($vendor->description ?: 'Objects are accessioned into the permanent collection for tactile materiality and timeless utilitarian harmony.')) }}
                    </p>
                </div>
            </aside>

            {{-- Right Museum Plinth Wall (9 cols) --}}
            <div class="lg:col-span-9 space-y-8">
                @if(is_countable($products) && count($products) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach($products as $product)
                            @include('storefront.themes.bespoke.object-gallery.card', ['product' => $product])
                        @endforeach
                    </div>

                    @if(method_exists($products, 'links'))
                        <div class="pt-8 flex justify-center text-xs font-mono">{{ $products->links() }}</div>
                    @endif
                @else
                    <div class="py-24 text-center border border-black/[0.08] space-y-3 bg-[#fbfbfa]">
                        <p class="text-xs font-mono uppercase tracking-widest text-[#767676]">Archival Status</p>
                        <h3 class="text-xl font-serif">No accessioned works found in this pavilion</h3>
                        <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !text-xs font-mono">Reset Pavilion</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
