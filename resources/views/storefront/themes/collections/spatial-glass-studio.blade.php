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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Filtered: ' . $currentSearch : 'Spatial Hardware Studio');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="spatial-glass-studio" class="w-full py-8 sm:py-16">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 space-y-10">
        {{-- Floating Frosted Glass Capsule Bar --}}
        <div class="p-6 sm:p-10 rounded-3xl space-y-6 shadow-xl"
             style="background: color-mix(in srgb, var(--t-surface) 80%, transparent); -webkit-backdrop-filter: blur(24px); backdrop-filter: blur(24px); border: 1px solid var(--t-border);">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="space-y-2">
                    <span class="inline-flex items-center gap-2 text-[10px] font-bold uppercase tracking-[0.24em]" style="color: var(--t-accent);">
                        <span class="w-2 h-2 rounded-full" style="background-color: var(--t-accent);"></span>
                        SPATIAL 3D STUDIO // {{ strtoupper($manifest['name']) }}
                    </span>
                    <h1 class="k71-heading text-3xl sm:text-5xl tracking-tight leading-none">{{ $collectionTitle }}</h1>
                    <p class="text-xs sm:text-sm" style="color: var(--t-muted);">{{ $totalCount }} dimensional units in studio</p>
                </div>

                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2.5">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Filter units..."
                           class="min-h-[44px] px-4 text-xs rounded-full w-44 sm:w-56"
                           style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort studio"
                            class="min-h-[44px] px-4 text-xs font-semibold rounded-full"
                            style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Recent Units</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High</option>
                    </select>
                </form>
            </div>

            {{-- Floating Capsule Category Pills --}}
            <nav class="flex items-center gap-2 overflow-x-auto k71-no-scrollbar pt-3" style="border-top: 1px solid var(--t-border);">
                <a href="{{ $storeCollectionUrl }}" class="px-4 py-2 rounded-full text-xs font-bold shrink-0 transition"
                   style="background-color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === '' ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                    All Units
                </a>
                @foreach(($vendorCategories ?? collect()) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-4 py-2 rounded-full text-xs font-semibold shrink-0 transition hover:opacity-80"
                       style="background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- 3D Specular Floating Card Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
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
            <div class="p-16 text-center space-y-3 rounded-2xl" style="border: 1px solid var(--t-border); background-color: var(--t-surface);">
                <p class="text-xs uppercase font-bold" style="color: var(--t-muted);">Studio Empty</p>
                <h3 class="k71-heading text-xl">No products registered in this space</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary !rounded-full">View All Units</a>
            </div>
        @endif
    </div>
</section>
