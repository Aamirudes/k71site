@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? null;
    $frameGlue = $frameBase ? (str_contains($frameBase, '?') ? '&' : '?') : '?';
    $storeHomeUrl = $storeHomeUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=home') : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '')));
    $storeCollectionUrl = $storeCollectionUrl ?? ($frameBase ? ($frameBase . $frameGlue . 'page=collection') : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : ''));
    $baseAction = !empty($isCataloguePreview) ? $frameBase : $storeCollectionUrl;

    $currentSearch = trim((string) request('search', ''));
    $currentCategory = trim((string) ($activeCategorySlug ?? request('category', '')));
    $currentSort = trim((string) request('sort', 'newest'));
    $activeCatObj = ($vendorCategories ?? collect())->firstWhere('slug', $currentCategory);
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Query: ' . $currentSearch : 'Commercial Trade Specimen Ledger');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="b2b-specimen-ledger" class="w-full py-8 sm:py-12">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 space-y-6">
        {{-- Trade Header --}}
        <div class="p-5 sm:p-6 rounded-xl flex flex-col md:flex-row md:items-end justify-between gap-4"
             style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
            <div class="space-y-1">
                <span class="text-[10px] font-mono uppercase tracking-[0.2em] font-bold" style="color: var(--t-accent);">B2B LOT LEDGER // {{ strtoupper($manifest['name']) }}</span>
                <h1 class="k71-heading text-2xl sm:text-4xl font-extrabold tracking-tight">{{ $collectionTitle }}</h1>
                <p class="text-xs font-mono" style="color: var(--t-muted);">{{ $totalCount }} Active Specimen Lots Catalogued</p>
            </div>

            <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter SKU / Material..."
                       class="min-h-[38px] px-3 font-mono text-xs rounded border w-44"
                       style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);">
                <select name="sort" onchange="this.form.submit()" aria-label="Sort ledger"
                        class="min-h-[38px] px-3 font-mono text-xs rounded border"
                        style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Chrono: Recent</option>
                    <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Unit: Low to High</option>
                    <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Unit: High to Low</option>
                </select>
            </form>
        </div>

        {{-- Category Tabs --}}
        <nav class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar font-mono text-xs uppercase font-bold">
            <a href="{{ $storeCollectionUrl }}" class="px-3.5 py-1.5 rounded border shrink-0"
               style="background-color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-surface)' }}; color: {{ $currentCategory === '' ? 'var(--t-accent-contrast)' : 'var(--t-text)' }}; border-color: var(--t-border);">
                All Lots
            </a>
            @foreach(($vendorCategories ?? collect()) as $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                   class="px-3.5 py-1.5 rounded border shrink-0 hover:opacity-80"
                   style="background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-surface)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : 'var(--t-text)' }}; border-color: var(--t-border);">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        {{-- Structured Specimen Ledger Table on Desktop + Dense Cards on Mobile --}}
        @if(is_countable($products) && count($products) > 0)
            <div class="hidden md:block overflow-x-auto rounded-xl shadow-xs" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
                <table class="w-full text-left font-mono text-xs divide-y" style="border-color: var(--t-border);">
                    <thead class="text-[11px] uppercase tracking-wider" style="background-color: var(--t-surface-alt); color: var(--t-muted);">
                        <tr>
                            <th class="py-3 px-4">Lot / Item</th>
                            <th class="py-3 px-4">Line</th>
                            <th class="py-3 px-4">Classification</th>
                            <th class="py-3 px-4">Available Qty</th>
                            <th class="py-3 px-4 text-right">Unit Price</th>
                            <th class="py-3 px-4 text-center">Acquisition</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="border-color: var(--t-border);">
                        @foreach($products as $pIdx => $p)
                            @php
                                $price = (int) ($p->base_price ?? 0);
                                $prodUrl = !empty($previewFrameBaseUrl)
                                    ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=product&product=' . urlencode($p->slug))
                                    : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $p->slug]) . (!empty($isPreview) ? '?preview=1' : '');
                                $firstImg = $p->images?->first()?->url ?? asset('images/theme-editorial/' . ($manifest['layout']['sample_art'] ?? 'tech-hardware') . '.jpg');
                            @endphp
                            <tr class="hover:bg-black/5 dark:hover:bg-white/5 transition">
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $firstImg }}" alt="{{ $p->title }}" class="w-12 h-12 object-cover rounded shrink-0" style="background-color: var(--t-surface-alt);">
                                        <div>
                                            <a href="{{ $prodUrl }}" class="font-bold hover:underline line-clamp-1">{{ $p->title }}</a>
                                            <span class="text-[10px] block" style="color: var(--t-muted);">SKU: {{ $p->sku ?: ('LOT-' . str_pad((string)($pIdx + 1), 3, '0', STR_PAD_LEFT)) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4" style="color: var(--t-muted);">{{ $p->category?->name ?? 'Standard' }}</td>
                                <td class="py-3 px-4" style="color: var(--t-muted);">{{ $p->material ?: ($p->brand ?: 'Commercial Spec') }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-block px-2 py-0.5 text-[10px] rounded {{ (int)($p->total_stock ?? 0) > 0 ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-bold' : 'bg-red-500/15 text-red-600' }}">
                                        {{ (int)($p->total_stock ?? 0) > 0 ? ($p->total_stock . ' UNITS') : 'OUT' }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-sm">₹{{ number_format($price / 100, 0) }}</td>
                                <td class="py-3 px-4 text-center">
                                    <a href="{{ $prodUrl }}" class="k71-btn k71-btn-primary !min-h-[34px] !py-1 !px-3 !text-[11px] font-mono">Order Spec</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Card Stack --}}
            <div class="md:hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($products as $product)
                    <div data-k71-card-item class="h-full">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-6 flex justify-center font-mono text-xs">{{ $products->links() }}</div>
            @endif
        @else
            <div class="p-16 text-center font-mono space-y-2 rounded-xl" style="border: 1px solid var(--t-border);">
                <p class="font-bold uppercase">[ NO LOT ENTRIES ]</p>
                <p class="text-xs" style="color: var(--t-muted);">Zero specimens match the requested filter.</p>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !text-xs font-mono">Reset Ledger</a>
            </div>
        @endif
    </div>
</section>
