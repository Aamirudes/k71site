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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Display Query: ' . $currentSearch : 'Architectural Type Collection');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="typography-as-architecture" class="w-full py-8 sm:py-16">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 space-y-10">
        {{-- Giant Display Wordmark Header --}}
        <div class="space-y-4 pb-8" style="border-bottom: 2px solid var(--t-text);">
            <div class="flex items-center justify-between text-xs font-mono uppercase tracking-[0.25em]">
                <span style="color: var(--t-accent);">TYPOGRAPHIC ARCHIVE // {{ strtoupper($manifest['name']) }}</span>
                <span>{{ $totalCount }} EDITIONS</span>
            </div>
            <h1 class="k71-heading text-5xl sm:text-7xl lg:text-9xl uppercase font-black tracking-tighter leading-[0.88] break-words">
                {{ $collectionTitle }}
            </h1>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pt-4">
                <nav class="flex items-center gap-6 overflow-x-auto k71-no-scrollbar font-mono text-xs uppercase tracking-wider">
                    <a href="{{ $storeCollectionUrl }}" class="shrink-0 hover:underline {{ $currentCategory === '' ? 'font-black underline' : 'opacity-70' }}">All</a>
                    @foreach(($vendorCategories ?? collect()) as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                           class="shrink-0 hover:underline {{ $currentCategory === $cat->slug ? 'font-black underline' : 'opacity-70' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </nav>

                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex items-center gap-2">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter..."
                           class="min-h-[38px] px-3 font-mono text-xs rounded-none w-36 sm:w-44"
                           style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort collection"
                            class="min-h-[38px] px-3 font-mono text-xs rounded-none"
                            style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Chrono</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price ↑</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price ↓</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- Type-Framed Product Diptych Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-12">
                @foreach($products as $product)
                    <div data-k71-card-item class="h-full">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-10 flex justify-center font-mono">{{ $products->links() }}</div>
            @endif
        @else
            <div class="p-16 text-center font-mono space-y-3" style="border: 2px solid var(--t-text);">
                <h3 class="text-2xl font-black uppercase">NO EDITIONS FOUND</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !rounded-none">Reset Query</a>
            </div>
        @endif
    </div>
</section>
