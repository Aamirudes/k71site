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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Search: "' . $currentSearch . '"' : 'All Departments');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="pro-commerce-catalog" class="w-full py-6 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- Breadcrumb + Top Category Filter Rail --}}
        <div class="p-4 sm:p-5 rounded-xl space-y-3" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                <nav class="flex items-center gap-1.5" style="color: var(--t-muted);" aria-label="Breadcrumb">
                    <a href="{{ $storeHomeUrl }}" class="hover:underline">Home</a>
                    <span>/</span>
                    <span class="font-bold" style="color: var(--t-text);">{{ $collectionTitle }}</span>
                    <span>({{ $totalCount }} items)</span>
                </nav>
                <div class="flex items-center gap-2">
                    <span class="text-[11px] font-bold" style="color: var(--t-muted);">Sort by:</span>
                    <select onchange="window.location.href=this.value" aria-label="Sort options"
                            class="min-h-[36px] px-2 text-xs font-semibold rounded border"
                            style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);">
                        <option value="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}sort=newest{{ $currentCategory ? '&category=' . urlencode($currentCategory) : '' }}" {{ $currentSort === 'newest' ? 'selected' : '' }}>Featured</option>
                        <option value="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}sort=price_low{{ $currentCategory ? '&category=' . urlencode($currentCategory) : '' }}" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}sort=price_high{{ $currentCategory ? '&category=' . urlencode($currentCategory) : '' }}" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </div>
            </div>

            {{-- Quick Filter Pills --}}
            <div class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar pt-2" style="border-top: 1px solid var(--t-border);">
                <a href="{{ $storeCollectionUrl }}" class="px-3 py-1.5 rounded-lg text-xs font-bold shrink-0"
                   style="background-color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-bg)' }}; color: {{ $currentCategory === '' ? 'var(--t-accent-contrast)' : 'var(--t-text)' }}; border: 1px solid var(--t-border);">
                    All
                </a>
                @foreach(($vendorCategories ?? collect()) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-medium shrink-0 hover:opacity-80"
                       style="background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-bg)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : 'var(--t-text)' }}; border: 1px solid var(--t-border);">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- 2-Column Power Shopping Layout (Sidebar + Dense Grid) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Filter Sidebar (3 cols) --}}
            <aside class="hidden lg:block lg:col-span-3 p-5 rounded-xl space-y-6"
                   style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="space-y-4">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <div>
                        <label class="block text-xs font-bold uppercase mb-1.5" style="color: var(--t-muted);">Keyword</label>
                        <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter products..."
                               class="w-full min-h-[38px] px-3 text-xs rounded border"
                               style="background-color: var(--t-bg); border-color: var(--t-border); color: var(--t-text);">
                    </div>
                    <button type="submit" class="k71-btn k71-btn-primary w-full !min-h-[38px] !text-xs">Apply Filter</button>
                </form>

                <div class="space-y-3 pt-4" style="border-top: 1px solid var(--t-border);">
                    <h3 class="text-xs font-bold uppercase" style="color: var(--t-muted);">Departments</h3>
                    <div class="space-y-1 text-xs">
                        <a href="{{ $storeCollectionUrl }}" class="flex items-center justify-between py-1 hover:underline {{ $currentCategory === '' ? 'font-bold' : '' }}">
                            <span>All Products</span>
                            <span>{{ $totalCount }}</span>
                        </a>
                        @foreach(($vendorCategories ?? collect()) as $cat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                               class="flex items-center justify-between py-1 hover:underline {{ $currentCategory === $cat->slug ? 'font-bold' : '' }}">
                                <span>{{ $cat->name }}</span>
                                <span style="color: var(--t-muted);">{{ $cat->products_count ?? '' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-2 pt-4 text-xs" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">
                    <p class="font-bold text-zinc-900 dark:text-zinc-100">Direct Merchant Guarantee</p>
                    <p>Verified inventory fulfilled directly from {{ $vendor->city ?: 'the vendor' }}.</p>
                </div>
            </aside>

            {{-- High-Density Grid (9 cols) --}}
            <main class="lg:col-span-9 space-y-6">
                @if(is_countable($products) && count($products) > 0)
                    <div data-k71-stagger-grid class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
                        @foreach($products as $product)
                            <div data-k71-card-item class="h-full">
                                @include('storefront.themes.partials.card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($products, 'links'))
                        <div class="pt-6 flex justify-center">{{ $products->links() }}</div>
                    @endif
                @else
                    <div class="p-16 text-center space-y-3 rounded-xl" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
                        <h3 class="k71-heading text-lg font-bold">No products found</h3>
                        <p class="text-xs" style="color: var(--t-muted);">Try clearing your search or category filter.</p>
                        <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !min-h-[38px] !text-xs">Reset All</a>
                    </div>
                @endif
            </main>
        </div>
    </div>
</section>
