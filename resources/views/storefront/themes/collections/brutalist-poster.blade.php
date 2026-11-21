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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'QUERY: ' . $currentSearch : 'ALL ACTIVE DROPS');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="brutalist-poster" class="w-full">
    {{-- Running Brutalist Ticker --}}
    <div class="w-full py-2 px-4 overflow-hidden text-xs font-mono font-black uppercase tracking-widest flex items-center justify-between"
         style="background-color: var(--t-text); color: var(--t-bg);">
        <span>CATALOGUE DROP // 100% VERIFIED AUTHENTIC // {{ $vendor->city ?: 'GLOBAL DISPATCH' }}</span>
        <span>TOTAL RELEASES: {{ $totalCount }}</span>
    </div>

    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 py-8 sm:py-14 space-y-8">
        {{-- Raw Poster Header --}}
        <div class="p-6 sm:p-10 space-y-6" style="border: 2px solid var(--t-text); background-color: var(--t-surface); box-shadow: 6px 6px 0px var(--t-text);">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="space-y-2">
                    <span class="inline-block px-2.5 py-1 text-[10px] font-mono font-black uppercase tracking-widest" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                        RELEASE VAULT // {{ strtoupper($manifest['name']) }}
                    </span>
                    <h1 class="k71-heading text-4xl sm:text-6xl font-black uppercase tracking-tight leading-none">{{ $collectionTitle }}</h1>
                </div>

                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="FILTER DROPS..."
                           class="min-h-[44px] px-4 font-mono font-black text-xs uppercase w-44 sm:w-56"
                           style="background-color: var(--t-bg); color: var(--t-text); border: 2px solid var(--t-text);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort releases"
                            class="min-h-[44px] px-3 font-mono font-black text-xs uppercase"
                            style="background-color: var(--t-bg); color: var(--t-text); border: 2px solid var(--t-text);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>DROP: LATEST</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>PRICE: LOW</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>PRICE: HIGH</option>
                    </select>
                </form>
            </div>

            {{-- Category Filter Buttons with Hard Shadow --}}
            <nav class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar pt-4 font-mono font-black text-xs uppercase" style="border-top: 2px solid var(--t-text);">
                <a href="{{ $storeCollectionUrl }}" class="px-4 py-2 shrink-0 transition"
                   style="border: 2px solid var(--t-text); background-color: {{ $currentCategory === '' ? 'var(--t-text)' : 'var(--t-bg)' }}; color: {{ $currentCategory === '' ? 'var(--t-bg)' : 'var(--t-text)' }};">
                    ALL
                </a>
                @foreach(($vendorCategories ?? collect()) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-4 py-2 shrink-0 transition"
                       style="border: 2px solid var(--t-text); background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-bg)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Hard-Bordered Release Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($products as $product)
                    <div data-k71-card-item class="h-full">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-8 flex justify-center font-mono font-bold">{{ $products->links() }}</div>
            @endif
        @else
            <div class="p-16 text-center font-mono space-y-3" style="border: 2px solid var(--t-text); background-color: var(--t-surface);">
                <h3 class="text-xl font-black uppercase">DROP EXHAUSTED</h3>
                <p class="text-xs">No pieces currently listed under this filter.</p>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !rounded-none !border-2 !border-black">VIEW ALL RELEASES</a>
            </div>
        @endif
    </div>
</section>
