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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Filtered: ' . $currentSearch : 'Interior Showroom Collection');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="split-screen-showroom" class="w-full">
    <div class="grid grid-cols-1 lg:grid-cols-12 min-h-screen">
        {{-- Left Sticky Showroom Pillar (4 cols) --}}
        <aside class="lg:col-span-4 p-6 sm:p-10 lg:sticky lg:top-0 lg:h-screen lg:overflow-y-auto flex flex-col justify-between gap-8"
               style="border-right: 1px solid var(--t-border); background-color: var(--t-surface);">
            <div class="space-y-6">
                <nav class="text-xs" style="color: var(--t-muted);" aria-label="Breadcrumb">
                    <a href="{{ $storeHomeUrl }}" class="hover:underline">Showroom</a> / <span>{{ $collectionTitle }}</span>
                </nav>
                <div class="space-y-2">
                    <p class="k71-eyebrow" style="color: var(--t-accent);">Interior Architecture</p>
                    <h1 class="k71-heading text-3xl sm:text-4xl leading-tight">{{ $collectionTitle }}</h1>
                    <p class="text-xs sm:text-sm leading-relaxed" style="color: var(--t-muted);">
                        {{ $totalCount }} curated design pieces photographed in spatial interior environments.
                    </p>
                </div>

                {{-- Search & Sort --}}
                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="space-y-2">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter furniture, decor..."
                           class="w-full min-h-[42px] px-3.5 text-xs rounded"
                           style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort showroom"
                            class="w-full min-h-[42px] px-3 text-xs rounded"
                            style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Recent Additions</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </form>

                {{-- Categories Vertical List --}}
                <div class="space-y-2 pt-4" style="border-top: 1px solid var(--t-border);">
                    <p class="k71-eyebrow" style="color: var(--t-muted);">Departments</p>
                    <div class="space-y-1">
                        <a href="{{ $storeCollectionUrl }}" class="flex items-center justify-between py-2 text-xs font-semibold hover:opacity-75"
                           style="color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-text)' }};">
                            <span>All Rooms</span>
                            <span>{{ $totalCount }}</span>
                        </a>
                        @foreach(($vendorCategories ?? collect()) as $cat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                               class="flex items-center justify-between py-2 text-xs hover:opacity-75"
                               style="color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-muted)' }};">
                                <span>{{ $cat->name }}</span>
                                <span class="font-mono text-[10px]">{{ $cat->products_count ?? '' }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            <p class="text-[10px] uppercase tracking-widest pt-4" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">
                {{ $storeName }} Showroom
            </p>
        </aside>

        {{-- Right Scrollable Grid (8 cols) --}}
        <main class="lg:col-span-8 p-6 sm:p-10 space-y-8">
            @if(is_countable($products) && count($products) > 0)
                <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 sm:gap-8">
                    @foreach($products as $product)
                        <div data-k71-card-item class="h-full">
                            @include('storefront.themes.partials.card', ['product' => $product])
                        </div>
                    @endforeach
                </div>

                @if(method_exists($products, 'links'))
                    <div class="pt-8 flex justify-center">{{ $products->links() }}</div>
                @endif
            @else
                <div class="p-16 text-center space-y-3" style="border: 1px solid var(--t-border);">
                    <h3 class="k71-heading text-xl">No items in this collection</h3>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Reset Filter</a>
                </div>
            @endif
        </main>
    </div>
</section>
