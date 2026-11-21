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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Bento Filter: ' . $currentSearch : '12-Column Modular Flagship');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="bento-modular-flagship" class="w-full py-8 sm:py-14">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 space-y-8">
        {{-- Top Bento Control Matrix --}}
        <div class="grid grid-cols-1 md:grid-cols-12 gap-5">
            {{-- Big Tile (8 cols) --}}
            <div class="md:col-span-8 p-6 sm:p-10 rounded-2xl flex flex-col justify-between gap-6"
                 style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                <div class="space-y-2">
                    <span class="inline-block px-2.5 py-0.5 text-[10px] font-mono font-bold uppercase tracking-wider rounded" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                        BENTO 12-COL // {{ strtoupper($manifest['name']) }}
                    </span>
                    <h1 class="k71-heading text-3xl sm:text-5xl tracking-tight leading-tight">{{ $collectionTitle }}</h1>
                    <p class="text-xs sm:text-sm" style="color: var(--t-muted);">Precision-engineered hardware index with live telemetry and stock tracking.</p>
                </div>

                {{-- Horizontal Category Chips --}}
                <nav class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar pt-2">
                    <a href="{{ $storeCollectionUrl }}" class="px-3.5 py-1.5 rounded-lg text-xs font-bold shrink-0 transition"
                       style="background-color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === '' ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                        All SKUs
                    </a>
                    @foreach(($vendorCategories ?? collect()) as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                           class="px-3.5 py-1.5 rounded-lg text-xs font-semibold shrink-0 transition hover:opacity-80"
                           style="background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </nav>
            </div>

            {{-- Stat Tile (4 cols) --}}
            <div class="md:col-span-4 p-6 sm:p-8 rounded-2xl flex flex-col justify-between gap-4"
                 style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                <div>
                    <p class="font-mono text-[10px] uppercase tracking-widest" style="color: var(--t-muted);">Catalogue Stats</p>
                    <p class="font-mono text-3xl sm:text-4xl font-extrabold mt-1">{{ $totalCount }}</p>
                    <p class="text-xs mt-0.5" style="color: var(--t-muted);">Active SKUs in Matrix</p>
                </div>

                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="space-y-2">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter SKU..."
                           class="w-full min-h-[38px] px-3 font-mono text-xs rounded-lg"
                           style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort matrix"
                            class="w-full min-h-[38px] px-3 font-mono text-xs rounded-lg"
                            style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Recent First</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- Bento Grid (Spotlight Cards Span 2 Cols, Standard 1 Col) --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                @foreach($products as $pIdx => $product)
                    <div data-k71-card-item class="{{ $pIdx % 5 === 0 ? 'sm:col-span-2' : '' }} h-full">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-8 flex justify-center font-mono text-xs">{{ $products->links() }}</div>
            @endif
        @else
            <div class="p-16 text-center space-y-3 rounded-2xl" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
                <h3 class="k71-heading text-xl">No SKUs match the current query</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Reset Filter</a>
            </div>
        @endif
    </div>
</section>
