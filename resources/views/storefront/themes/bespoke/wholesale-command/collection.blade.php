{{-- WHOLESALE COMMAND BESPOKE COLLECTION PAGE --}}
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'Wholesale Command');
    $storeCollectionUrl = $storeCollectionUrl ?? route('storefront.collection', ['slug' => $vendor->slug]);
    $currentSearch = trim((string) request('search', ''));
    $currentCategory = trim((string) ($activeCategorySlug ?? request('category', '')));
    $currentSort = trim((string) request('sort', 'newest'));
    $activeCatObj = ($vendorCategories ?? collect())->firstWhere('slug', $currentCategory);
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'QUERY: ' . $currentSearch : 'MASTER COMMERCIAL TRADE LEDGER');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
    $prodUrl = fn ($p) => route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]) . (!empty($isPreview) ? '?preview=1' : '');
@endphp

<div class="w-full bg-[#0b0f19] text-[#f9fafb] py-8 sm:py-12">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 space-y-6">
        {{-- Header Bar --}}
        <div class="p-5 sm:p-6 rounded-lg bg-[#111827] border border-[#1f2937] flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div class="space-y-1">
                <span class="text-[10px] font-mono text-sky-400 font-bold uppercase tracking-wider">B2B LOT INVENTORY // {{ strtoupper($storeName) }}</span>
                <h1 class="text-xl sm:text-3xl font-mono font-bold text-white">{{ $collectionTitle }}</h1>
                <p class="text-xs font-mono text-zinc-400">{{ $totalCount }} Verified Commercial Lots Catalogued</p>
            </div>

            <form action="{{ $storeCollectionUrl }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter SKU / Material..."
                       class="min-h-[38px] px-3 font-mono text-xs bg-[#0b0f19] border border-[#374151] rounded text-white focus:outline-none focus:border-sky-500">
                <select name="sort" onchange="this.form.submit()" aria-label="Sort ledger"
                        class="min-h-[38px] px-3 font-mono text-xs bg-[#0b0f19] border border-[#374151] rounded text-white focus:outline-none">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Recent Lots</option>
                    <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Unit Price: Low</option>
                    <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Unit Price: High</option>
                </select>
            </form>
        </div>

        {{-- Category Department Filter Strip --}}
        <nav class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar font-mono text-xs font-bold">
            <a href="{{ $storeCollectionUrl }}" class="px-3.5 py-1.5 rounded shrink-0 {{ $currentCategory === '' ? 'bg-sky-600 text-white' : 'bg-[#111827] text-zinc-400 border border-[#1f2937]' }}">
                ALL LOTS ({{ $totalCount }})
            </a>
            @foreach(($vendorCategories ?? collect()) as $cat)
                <a href="{{ $storeCollectionUrl }}?category={{ urlencode($cat->slug) }}"
                   class="px-3.5 py-1.5 rounded shrink-0 {{ $currentCategory === $cat->slug ? 'bg-sky-600 text-white' : 'bg-[#111827] text-zinc-400 border border-[#1f2937]' }}">
                    {{ strtoupper($cat->name) }}
                </a>
            @endforeach
        </nav>

        {{-- Desktop Structured Data Table --}}
        @if(is_countable($products) && count($products) > 0)
            <div class="hidden lg:block overflow-x-auto rounded-lg border border-[#1f2937] bg-[#111827]">
                <table class="w-full text-left font-mono text-xs divide-y divide-[#1f2937]">
                    <thead class="bg-[#0f172a] text-zinc-400 uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Lot #</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4">Stock In Warehouse</th>
                            <th class="py-3 px-4 text-right">Unit Price</th>
                            <th class="py-3 px-4 text-center">Direct Add to Order</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#1f2937]">
                        @foreach($products as $idx => $p)
                            @php
                                $price = (int) ($p->base_price ?? 0);
                                $img = $p->images?->first()?->url ?? asset('images/theme-editorial/tech-hardware.jpg');
                            @endphp
                            <tr class="hover:bg-[#1a2333] transition">
                                <td class="py-3.5 px-4 text-sky-400 font-bold">#{{ $p->sku ?: 'LOT-' . $p->id }}</td>
                                <td class="py-3.5 px-4 font-semibold text-white">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $img }}" alt="" class="w-10 h-10 rounded object-cover border border-[#374151]">
                                        <a href="{{ $prodUrl($p) }}" class="hover:text-sky-400 transition">{{ $p->title }}</a>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-zinc-400">{{ $p->category?->name ?? 'GENERAL' }}</td>
                                <td class="py-3.5 px-4 text-emerald-400 font-bold">{{ $p->total_stock ?: '150' }} units</td>
                                <td class="py-3.5 px-4 text-right font-bold text-white">₹{{ number_format($price / 100, 0) }}</td>
                                <td class="py-3.5 px-4 text-center">
                                    <form action="{{ route('cart.add') }}" method="POST" data-k71-cart-form class="inline-flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $p->id }}">
                                        <input type="number" name="quantity" value="10" min="1" class="w-16 h-8 px-2 bg-[#0b0f19] border border-[#374151] rounded text-center text-xs font-mono text-white">
                                        <button type="submit" class="h-8 px-3 rounded bg-sky-600 hover:bg-sky-500 text-white font-mono text-[11px] font-bold uppercase transition">
                                            + ADD LOT
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards --}}
            <div class="lg:hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($products as $product)
                    @include('storefront.themes.bespoke.wholesale-command.card', ['product' => $product])
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-8 flex justify-center text-xs font-mono">{{ $products->links() }}</div>
            @endif
        @else
            <div class="py-20 text-center rounded-lg border border-[#1f2937] bg-[#111827] space-y-2 font-mono">
                <p class="text-xs text-zinc-400 uppercase tracking-widest">Inventory Status</p>
                <h3 class="text-lg font-bold text-white">Zero lots catalogued under current filter</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !text-xs mt-2 inline-block">Reset Terminal Filter</a>
            </div>
        @endif
    </div>
</div>
