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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Search: ' . $currentSearch : 'Complete Typographic Matrix');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="swiss-manifesto" class="w-full py-8 sm:py-14" style="background-color: var(--t-bg);">
    <div class="max-w-[1480px] mx-auto px-4 sm:px-8 space-y-8">
        {{-- Coordinate Index Bar --}}
        <div class="py-2.5 px-4 flex items-center justify-between text-xs font-mono uppercase tracking-[0.24em]" style="border-top: 2px solid var(--t-text); border-bottom: 1px solid var(--t-border); background-color: var(--t-surface);">
            <span>[01/CATALOGUE] // {{ strtoupper($manifest['name']) }}</span>
            <span>SYSTEM GRID · {{ $totalCount }} ENTRIES</span>
        </div>

        {{-- Publication Header Spread --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-end pb-8" style="border-bottom: 2px solid var(--t-text);">
            <div class="lg:col-span-7 space-y-3">
                <span class="text-[10px] font-mono uppercase tracking-[0.28em]" style="color: var(--t-accent);">SWISS RATIONALIST MATRIX</span>
                <h1 class="k71-heading text-4xl sm:text-6xl font-black uppercase tracking-tight leading-none">{{ $collectionTitle }}</h1>
            </div>
            <div class="lg:col-span-5 flex flex-wrap items-center justify-start lg:justify-end gap-3">
                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Index filter..."
                           class="min-h-[40px] px-3 font-mono text-xs rounded-none w-44"
                           style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort matrix"
                            class="min-h-[40px] px-3 font-mono text-xs rounded-none"
                            style="background-color: var(--t-surface); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Index: Chrono</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Asc</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: Desc</option>
                    </select>
                </form>
            </div>
        </div>

        {{-- Categorical Hairline Strip --}}
        <nav class="flex items-center gap-6 overflow-x-auto k71-no-scrollbar font-mono text-xs uppercase tracking-wider pb-3" style="border-bottom: 1px solid var(--t-border);">
            <a href="{{ $storeCollectionUrl }}" class="shrink-0 font-bold {{ $currentCategory === '' ? 'underline underline-offset-4' : 'opacity-65' }}">00 // ALL</a>
            @foreach(($vendorCategories ?? collect()) as $cIdx => $cat)
                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                   class="shrink-0 hover:opacity-100 transition {{ $currentCategory === $cat->slug ? 'font-bold underline underline-offset-4' : 'opacity-65' }}">
                    {{ str_pad((string)($cIdx + 1), 2, '0', STR_PAD_LEFT) }} // {{ $cat->name }}
                </a>
            @endforeach
        </nav>

        {{-- Numbered Swiss Matrix Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-0" style="border: 1px solid var(--t-border);">
                @foreach($products as $pIdx => $product)
                    <div data-k71-card-item class="p-4 relative" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
                        <span class="absolute top-2 right-2 text-[10px] font-mono font-bold" style="color: var(--t-muted);">[{{ str_pad((string)($pIdx + 1), 2, '0', STR_PAD_LEFT) }}]</span>
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'links'))
                <div class="pt-8 flex justify-center font-mono text-xs">{{ $products->links() }}</div>
            @endif
        @else
            <div class="py-24 text-center font-mono space-y-3" style="border: 1px solid var(--t-border);">
                <p class="text-xs uppercase">[ EMPTY MATRIX ]</p>
                <h3 class="text-lg font-bold">No entries located for active filter sequence</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !rounded-none !text-xs font-mono">Reset Query</a>
            </div>
        @endif
    </div>
</section>
