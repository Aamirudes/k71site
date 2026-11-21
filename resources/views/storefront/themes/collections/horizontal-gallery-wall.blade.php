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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Heritage Archive: ' . $currentSearch : 'Complete Heritage Exhibition');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="horizontal-gallery-wall" class="w-full py-10 sm:py-16">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 space-y-12">
        {{-- Gilded Heritage Engraved Box Header --}}
        <div class="p-8 sm:p-12 text-center space-y-4" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
            <p class="text-[10px] uppercase tracking-[0.3em]" style="color: var(--t-accent);">HERITAGE HOUSE EXHIBITION // {{ strtoupper($manifest['name']) }}</p>
            <h1 class="k71-heading text-3xl sm:text-5xl lg:text-6xl tracking-wide">{{ $collectionTitle }}</h1>
            <p class="text-xs sm:text-sm max-w-lg mx-auto" style="color: var(--t-muted);">{{ $totalCount }} timeless objects and heirloom acquisitions documented in the house archives.</p>

            <div class="pt-6 flex flex-wrap items-center justify-center gap-3">
                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center justify-center gap-2">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Search archive..."
                           class="min-h-[42px] px-4 text-xs tracking-wider rounded-none w-48 text-center"
                           style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort archive"
                            class="min-h-[42px] px-3 text-xs uppercase tracking-wider rounded-none"
                            style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Chrono: Recent</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Acquisition: Asc</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Acquisition: Desc</option>
                    </select>
                </form>
            </div>

            {{-- Hairline Centered Categories --}}
            <nav class="pt-6 flex flex-wrap items-center justify-center gap-6 text-xs uppercase tracking-[0.2em]" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">
                <a href="{{ $storeCollectionUrl }}" class="hover:opacity-100 transition {{ $currentCategory === '' ? 'font-bold' : '' }}" style="color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-muted)' }};">All Pieces</a>
                @foreach(($vendorCategories ?? collect()) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="hover:opacity-100 transition {{ $currentCategory === $cat->slug ? 'font-bold' : '' }}"
                       style="color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-muted)' }};">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- 4-Column Heritage Filmstrip Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                @foreach($products as $product)
                    <div data-k71-card-item class="h-full">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-8 flex justify-center text-xs tracking-widest">{{ $products->links() }}</div>
            @endif
        @else
            <div class="p-16 text-center space-y-3" style="border: 1px solid var(--t-border);">
                <h3 class="k71-heading text-xl">Archive piece not found</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">Return to Index</a>
            </div>
        @endif
    </div>
</section>
