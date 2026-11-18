@extends('storefront.themes.layout')

@section('theme_content')
@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $frameBase = $previewFrameBaseUrl ?? route('vendor.themes.preview_frame', ['themeId' => $manifest['id']]);
    $frameGlue = str_contains($frameBase, '?') ? '&' : '?';
    $storeHomeUrl = $storeHomeUrl ?? (!empty($isCataloguePreview)
        ? ($frameBase . $frameGlue . 'page=home')
        : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '')));
    $storeCollectionUrl = $storeCollectionUrl ?? (!empty($isCataloguePreview)
        ? ($frameBase . $frameGlue . 'page=collection')
        : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : ''));
    $glassStrength = $glassStrength ?? ($themeConfig['motion']['glass_strength'] ?? (($manifest['glass']['mode'] ?? 'none') === 'none' ? 'off' : 'normal'));
    $gridCols = (int) ($themeConfig['grid']['columns'] ?? ($manifest['layout']['default_columns'] ?? 4));
    $colsClass = match($gridCols) {
        2 => 'grid-cols-1 sm:grid-cols-2',
        3 => 'grid-cols-2 lg:grid-cols-3',
        default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
    };
    $currentSearch = trim((string) request('search', ''));
    $currentCategory = trim((string) ($activeCategorySlug ?? request('category', '')));
    $currentSort = trim((string) request('sort', 'newest'));
    $activeCatObj = ($vendorCategories ?? collect())->firstWhere('slug', $currentCategory);
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Search: "' . $currentSearch . '"' : 'All Products');
    $baseAction = !empty($isCataloguePreview) ? $frameBase : $storeCollectionUrl;
    $collectionLinkBase = $storeCollectionUrl;
@endphp

<section class="w-full py-8 sm:py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 sm:space-y-8">

        {{-- Collection Heading & Filter Bar --}}
        <div class="p-5 sm:p-8 {{ !empty($manifest['glass']['panel_glass']) ? 'k71-glass' : 'k71-surface' }} space-y-5">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
                <div class="space-y-1.5">
                    <nav class="text-xs flex items-center gap-2" style="color: var(--t-muted);" aria-label="Breadcrumb">
                        <a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Home</a>
                        <span>/</span>
                        <span style="color: var(--t-text);">{{ $collectionTitle }}</span>
                    </nav>
                    <h1 class="k71-heading text-3xl sm:text-4xl">{{ $collectionTitle }}</h1>
                    <p class="text-xs sm:text-sm" style="color: var(--t-muted);">
                        {{ method_exists($products, 'total') ? $products->total() : $products->count() }} {{ \Illuminate\Support\Str::plural('item', method_exists($products, 'total') ? $products->total() : $products->count()) }}
                    </p>
                </div>

                {{-- Search + Sort Controls --}}
                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2.5">
                    @if(!empty($isCataloguePreview))
                        <input type="hidden" name="page" value="collection">
                        @foreach(['v', 'expires', 'signature'] as $signedParam)
                            @if(request()->filled($signedParam))
                                <input type="hidden" name="{{ $signedParam }}" value="{{ request($signedParam) }}">
                            @endif
                        @endforeach
                    @endif
                    @if($currentCategory !== '')
                        <input type="hidden" name="category" value="{{ $currentCategory }}">
                    @endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter by keyword..."
                           class="min-h-[44px] px-3.5 text-xs sm:text-sm rounded-lg w-full sm:w-48"
                           style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort products"
                            class="min-h-[44px] px-3 text-xs sm:text-sm font-medium rounded-lg"
                            style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest First</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                    <button type="submit" class="k71-btn k71-btn-primary !min-h-[44px]">Apply</button>
                </form>
            </div>

            {{-- Category Filter Pills + Live Grid Density Switcher & Shortlist Filter --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3" style="border-top: 1px solid var(--t-border);">
                <div class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar">
                    <a href="{{ $collectionLinkBase }}"
                       class="shrink-0 min-h-[38px] px-3.5 rounded-full text-xs font-semibold flex items-center transition hover:opacity-80"
                       style="{{ $currentCategory === '' ? 'background-color: var(--t-accent); color: var(--t-accent-contrast);' : 'border: 1px solid var(--t-border); color: var(--t-text);' }}">
                        All
                    </a>
                    @foreach(($vendorCategories ?? collect()) as $cat)
                        <a href="{{ $collectionLinkBase }}{{ str_contains($collectionLinkBase, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}{{ $currentSort !== 'newest' ? '&sort=' . urlencode($currentSort) : '' }}"
                           class="shrink-0 min-h-[38px] px-3.5 rounded-full text-xs font-medium flex items-center transition hover:opacity-80"
                           style="{{ $currentCategory === $cat->slug ? 'background-color: var(--t-accent); color: var(--t-accent-contrast);' : 'border: 1px solid var(--t-border); color: var(--t-text);' }}">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" id="k71-filter-saved-btn"
                            class="min-h-[36px] px-3 rounded-full text-[11px] font-bold inline-flex items-center gap-1.5 cursor-pointer transition hover:opacity-80"
                            style="border: 1px solid var(--t-border); color: var(--t-text);"
                            aria-pressed="false">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
                        <span>Saved Only</span>
                    </button>

                    <div class="hidden lg:inline-flex items-center p-0.5 rounded-lg" style="border: 1px solid var(--t-border); background-color: var(--t-bg);" role="group" aria-label="Grid density">
                        @foreach([2 => '2-Col', 3 => '3-Col', 4 => '4-Col'] as $colNum => $colLbl)
                            <button type="button" data-k71-grid-cols="{{ $colNum }}"
                                    class="px-2.5 py-1.5 rounded-md text-[11px] font-mono font-bold cursor-pointer transition"
                                    style="{{ $gridCols === $colNum ? 'background-color: var(--t-accent); color: var(--t-accent-contrast);' : 'color: var(--t-muted);' }}">
                                {{ $colLbl }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Active Filter Reset --}}
            @if($currentCategory !== '' || $currentSearch !== '')
                <div class="flex flex-wrap items-center gap-2 text-xs">
                    <span style="color: var(--t-muted);">Active filters:</span>
                    @if($currentCategory !== '')
                        <span class="px-2.5 py-1 rounded-full font-semibold" style="background-color: var(--t-surface-alt);">Category: {{ $activeCatObj?->name ?? $currentCategory }}</span>
                    @endif
                    @if($currentSearch !== '')
                        <span class="px-2.5 py-1 rounded-full font-semibold" style="background-color: var(--t-surface-alt);">Search: "{{ $currentSearch }}"</span>
                    @endif
                    <a href="{{ $collectionLinkBase }}" class="underline underline-offset-4 font-semibold ml-1" style="color: var(--t-accent);">Clear all</a>
                </div>
            @endif
        </div>

        {{-- Product Grid / Empty State --}}
        @if($products->isEmpty())
            <div class="k71-surface p-12 text-center max-w-lg mx-auto space-y-4">
                <h2 class="k71-heading text-xl">No matching products found</h2>
                <p class="text-sm" style="color: var(--t-muted);">Try clearing your search term or selecting another category from {{ $storeName }}.</p>
                <a href="{{ $baseAction }}" class="k71-btn k71-btn-primary">View All Store Items</a>
            </div>
        @else
            <div id="k71-collection-grid" data-k71-stagger-grid class="grid {{ $colsClass }} gap-4 sm:gap-6">
                @foreach($products as $product)
                    <div data-k71-card-item data-product-id="{{ $product->id }}">
                        @include('storefront.themes.partials.card', ['product' => $product])
                    </div>
                @endforeach
            </div>

            @if(method_exists($products, 'hasPages') && $products->hasPages())
                <div class="pt-6 flex justify-center">
                    {{ $products->links() }}
                </div>
            @endif
        @endif
    </div>
</section>
@endsection
