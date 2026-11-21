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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Search: "' . $currentSearch . '"' : 'The Complete Collection');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="editorial-runway" class="w-full py-10 sm:py-16">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 space-y-12">
        {{-- Editorial Masthead & Chapter Navigation --}}
        <header class="space-y-6 pb-8" style="border-bottom: 1px solid var(--t-border);">
            <div class="flex items-center justify-between text-xs font-mono uppercase tracking-[0.2em]" style="color: var(--t-muted);">
                <span>VOL. IV // {{ strtoupper($manifest['name']) }} CATALOGUE</span>
                <span>{{ $totalCount }} {{ \Illuminate\Support\Str::plural('PIECE', $totalCount) }}</span>
            </div>
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="space-y-2 max-w-2xl">
                    <p class="k71-eyebrow" style="color: var(--t-accent);">Runway Catalogue</p>
                    <h1 class="k71-heading text-4xl sm:text-6xl tracking-tight leading-[0.95]">{{ $collectionTitle }}</h1>
                </div>

                {{-- Filter & Sort Console --}}
                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-3">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Search collection..."
                           class="min-h-[42px] px-4 text-xs tracking-wider rounded-none w-44 sm:w-56"
                           style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort collection"
                            class="min-h-[42px] px-3 text-xs uppercase tracking-wider rounded-none"
                            style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </form>
            </div>

            {{-- Minimal Chapters Strip --}}
            <nav class="flex items-center gap-8 overflow-x-auto k71-no-scrollbar pt-4 text-xs uppercase tracking-[0.18em]" style="border-top: 1px solid var(--t-border);">
                <a href="{{ $storeCollectionUrl }}" class="shrink-0 {{ $currentCategory === '' ? 'font-bold' : '' }}" style="color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-muted)' }};">All Chapters</a>
                @foreach(($vendorCategories ?? collect()) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="shrink-0 hover:opacity-75 transition {{ $currentCategory === $cat->slug ? 'font-bold' : '' }}"
                       style="color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-muted)' }};">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        </header>

        {{-- Asymmetric Runway Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 sm:gap-12">
                @foreach($products as $pIdx => $product)
                    <div data-k71-card-item class="{{ $pIdx % 3 === 1 ? 'lg:translate-y-8' : '' }} transition-transform">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-12 flex justify-center">{{ $products->links() }}</div>
            @endif
        @else
            <div class="py-24 text-center space-y-4" style="border: 1px dashed var(--t-border);">
                <p class="k71-eyebrow">Catalogue Index</p>
                <h3 class="k71-heading text-2xl">No items found matching the selected criteria</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline">Clear Filters</a>
            </div>
        @endif
    </div>
</section>
