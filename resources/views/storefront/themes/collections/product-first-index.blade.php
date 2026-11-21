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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Index Search: ' . $currentSearch : 'Immediate Product Wall');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="product-first-index" class="w-full py-4 sm:py-8">
    <div class="max-w-[1560px] mx-auto px-4 sm:px-8 space-y-4">
        {{-- Minimalist Utility Header Bar --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 py-3" style="border-bottom: 1px solid var(--t-border);">
            <div class="flex items-center gap-3">
                <span class="text-xs uppercase font-bold tracking-wider">{{ $collectionTitle }}</span>
                <span class="text-xs" style="color: var(--t-muted);">({{ $totalCount }})</span>
            </div>

            <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex items-center gap-2">
                @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter..."
                       class="min-h-[34px] px-2.5 text-xs rounded border w-36 sm:w-44"
                       style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);">
                <select name="sort" onchange="this.form.submit()" aria-label="Sort wall"
                        class="min-h-[34px] px-2 text-xs rounded border"
                        style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Recent</option>
                    <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price ↑</option>
                    <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price ↓</option>
                </select>
            </form>
        </div>

        {{-- Category Department Filter Strip --}}
        <nav class="flex items-center gap-4 overflow-x-auto k71-no-scrollbar text-xs pb-2" style="border-bottom: 1px solid var(--t-border);">
            <a href="{{ $storeCollectionUrl }}" class="shrink-0 hover:underline {{ $currentCategory === '' ? 'font-bold underline' : '' }}" style="color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-muted)' }};">All</a>
            @foreach(($vendorCategories ?? collect()) as $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                   class="shrink-0 hover:underline {{ $currentCategory === $cat->slug ? 'font-bold underline' : '' }}"
                   style="color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-muted)' }};">
                    {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        {{-- Direct Borderless 5-Column Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4 pt-2">
                @foreach($products as $product)
                    <div data-k71-card-item class="h-full">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-8 flex justify-center text-xs">{{ $products->links() }}</div>
            @endif
        @else
            <div class="p-16 text-center space-y-2" style="border: 1px solid var(--t-border);">
                <p class="text-xs uppercase" style="color: var(--t-muted);">Zero Items</p>
                <h3 class="k71-heading text-lg">No pieces found</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !text-xs">Reset</a>
            </div>
        @endif
    </div>
</section>
