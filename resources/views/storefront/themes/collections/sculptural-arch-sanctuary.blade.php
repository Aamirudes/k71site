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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Formula: ' . $currentSearch : 'Sanctuary Ritual Collection');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="sculptural-arch-sanctuary" class="w-full py-12 sm:py-20">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 space-y-12">
        {{-- Sculptural Sanctuary Header --}}
        <div class="p-8 sm:p-14 text-center space-y-4 rounded-3xl" style="background-color: var(--t-surface); border: 1px solid var(--t-border);">
            <span class="inline-block px-3 py-1 text-[10px] uppercase tracking-[0.26em] rounded-full font-semibold" style="background-color: var(--t-surface-alt); color: var(--t-accent);">
                SANCTUARY ARCH // {{ strtoupper($manifest['name']) }}
            </span>
            <h1 class="k71-heading text-3xl sm:text-5xl lg:text-6xl tracking-tight leading-tight max-w-3xl mx-auto">{{ $collectionTitle }}</h1>
            <p class="text-xs sm:text-sm max-w-md mx-auto leading-relaxed" style="color: var(--t-muted);">{{ $totalCount }} botanical rituals and organic forms crafted for everyday wellness.</p>

            <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="pt-4 flex flex-wrap items-center justify-center gap-2">
                @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Search formulas..."
                       class="min-h-[42px] px-4 text-xs rounded-full w-48 text-center"
                       style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                <select name="sort" onchange="this.form.submit()" aria-label="Sort sanctuary"
                        class="min-h-[42px] px-4 text-xs rounded-full"
                        style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                    <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest Rituals</option>
                    <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low</option>
                    <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High</option>
                </select>
            </form>

            {{-- Soft Arched Category Pills --}}
            <nav class="pt-6 flex flex-wrap items-center justify-center gap-2 text-xs" style="border-top: 1px solid var(--t-border);">
                <a href="{{ $storeCollectionUrl }}" class="px-4 py-2 rounded-full font-medium transition"
                   style="background-color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === '' ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                    All Rituals
                </a>
                @foreach(($vendorCategories ?? collect()) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-4 py-2 rounded-full font-medium transition hover:opacity-75"
                       style="background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'var(--t-surface-alt)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : 'var(--t-text)' }};">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Sanctuary Arch Grid --}}
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
            <div class="p-16 text-center space-y-3 rounded-2xl" style="border: 1px solid var(--t-border);">
                <h3 class="k71-heading text-xl">No formulas currently in this collection</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !rounded-full">View All Formulas</a>
            </div>
        @endif
    </div>
</section>
