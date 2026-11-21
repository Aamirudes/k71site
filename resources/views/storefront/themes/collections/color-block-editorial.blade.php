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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Color Query: ' . $currentSearch : 'Full-Bleed Chromatic Exhibition');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="color-block-editorial" class="w-full">
    {{-- Full-Bleed Saturated Color Block Header --}}
    <div class="w-full py-12 sm:py-16 px-5 sm:px-10 lg:px-14" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
        <div class="max-w-[1440px] mx-auto flex flex-col lg:flex-row lg:items-end justify-between gap-6">
            <div class="space-y-2">
                <span class="text-xs uppercase tracking-[0.25em] font-bold opacity-80">CHROMA ROOM // {{ strtoupper($manifest['name']) }}</span>
                <h1 class="k71-heading text-4xl sm:text-6xl tracking-tight leading-none">{{ $collectionTitle }}</h1>
                <p class="text-xs sm:text-sm opacity-85 font-medium">{{ $totalCount }} vibrant lifestyle pieces in palette</p>
            </div>

            <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2">
                @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Search palette..."
                       class="min-h-[44px] px-4 text-xs font-bold rounded-full w-44 sm:w-56"
                       style="background-color: var(--t-bg); color: var(--t-text); border: none;">
                <select name="sort" onchange="this.form.submit()" aria-label="Sort palette"
                        class="min-h-[44px] px-4 text-xs font-bold rounded-full"
                        style="background-color: var(--t-bg); color: var(--t-text); border: none;">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Chroma: Newest</option>
                    <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low</option>
                    <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High</option>
                </select>
            </form>
        </div>
    </div>

    {{-- Category Pills --}}
    <div class="w-full py-4 px-5 sm:px-10 lg:px-14" style="background-color: var(--t-surface); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-[1440px] mx-auto flex items-center gap-3 overflow-x-auto k71-no-scrollbar">
            <a href="{{ $storeCollectionUrl }}" class="px-4 py-2 rounded-full text-xs font-bold shrink-0 transition"
               style="background-color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === '' ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                All Colors
            </a>
            @foreach(($vendorCategories ?? collect()) as $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                   class="px-4 py-2 rounded-full text-xs font-bold shrink-0 transition hover:opacity-80"
                   style="background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Color Block Grid --}}
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 py-12 sm:py-16">
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
                @foreach($products as $product)
                    <div data-k71-card-item class="h-full">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-10 flex justify-center">{{ $products->links() }}</div>
            @endif
        @else
            <div class="p-16 text-center space-y-3" style="border: 1px solid var(--t-border);">
                <h3 class="k71-heading text-xl">No items in this color block</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">View Full Palette</a>
            </div>
        @endif
    </div>
</section>
