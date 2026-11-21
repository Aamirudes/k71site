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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Exhibition Search: "' . $currentSearch . '"' : 'Permanent Exhibition Catalogue');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="left-rail-gallery" class="w-full py-8 sm:py-14 px-5 sm:px-10 lg:px-12 space-y-10">
    {{-- Museum Header Spread --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 pb-6" style="border-bottom: 1px solid var(--t-border);">
        <div class="space-y-1.5">
            <span class="text-[10px] font-mono uppercase tracking-[0.24em]" style="color: var(--t-muted);">EXHIBITION ARCHIVE // {{ strtoupper($manifest['name']) }}</span>
            <h1 class="k71-heading text-3xl sm:text-4xl lg:text-5xl tracking-tight">{{ $collectionTitle }}</h1>
            <p class="text-xs font-mono" style="color: var(--t-muted);">{{ $totalCount }} {{ \Illuminate\Support\Str::plural('CATALOGUED OBJECT', $totalCount) }}</p>
        </div>

        <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2">
            @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
            @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
            <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter exhibits..."
                   class="min-h-[40px] px-3.5 text-xs font-mono rounded"
                   style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
            <select name="sort" onchange="this.form.submit()" aria-label="Sort exhibits"
                    class="min-h-[40px] px-3 text-xs font-mono rounded"
                    style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
                <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Index: Recent</option>
                <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Valuation: Asc</option>
                <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Valuation: Desc</option>
            </select>
        </form>
    </div>

    {{-- Museum Plinth Grid --}}
    @if(is_countable($products) && count($products) > 0)
        <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
            @foreach($products as $product)
                <div data-k71-card-item class="h-full">
                    @include('storefront.themes.partials.card', ['product' => $product])
                </div>
            @endforeach
        </div>

        @if(method_exists($products, 'links'))
            <div class="pt-8 flex justify-center font-mono text-xs">{{ $products->links() }}</div>
        @endif
    @else
        <div class="py-24 text-center space-y-3" style="border: 1px solid var(--t-border);">
            <p class="text-xs font-mono uppercase tracking-widest" style="color: var(--t-muted);">Archive State</p>
            <h3 class="k71-heading text-xl">No curated objects catalogued in this pavilion</h3>
            <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !text-xs font-mono">Reset Catalog Index</a>
        </div>
    @endif
</section>
