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
    $collectionTitle = $activeCatObj?->name ?? ($currentSearch !== '' ? 'Vault Query: "' . $currentSearch . '"' : 'Nocturnal Spotlight Vault');
    $totalCount = method_exists($products, 'total') ? $products->total() : (is_countable($products) ? count($products) : 0);
@endphp

<section data-k71-collection="cinematic-dark-stage" class="w-full py-12 sm:py-20" style="background-color: #07070a; color: #f4f4f7;">
    <div class="max-w-[1440px] mx-auto px-5 sm:px-10 lg:px-14 space-y-12">
        {{-- Spotlight Banner --}}
        <div class="relative p-8 sm:p-14 overflow-hidden rounded-2xl" style="background: radial-gradient(circle at 50% 0%, #1c1c28 0%, #0c0c12 100%); border: 1px solid rgba(255,255,255,0.12);">
            <div class="relative z-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="space-y-3">
                    <span class="inline-flex items-center gap-2 text-[10px] font-mono uppercase tracking-[0.28em]" style="color: var(--t-accent);">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background-color: var(--t-accent);"></span>
                        LIVE VAULT // {{ strtoupper($manifest['name']) }}
                    </span>
                    <h1 class="k71-heading text-4xl sm:text-6xl text-white leading-[0.95]">{{ $collectionTitle }}</h1>
                    <p class="text-xs sm:text-sm text-zinc-400 font-mono">{{ $totalCount }} PIECES SECURED & AUTHENTICATED</p>
                </div>

                <form action="{{ explode('?', $baseAction)[0] }}" method="GET" class="flex flex-wrap items-center gap-2">
                    @if(!empty($isCataloguePreview))<input type="hidden" name="page" value="collection">@endif
                    @if($currentCategory !== '')<input type="hidden" name="category" value="{{ $currentCategory }}">@endif
                    <input type="search" name="search" value="{{ $currentSearch }}" placeholder="Search vault..."
                           class="min-h-[42px] px-4 text-xs font-mono rounded-lg w-44 sm:w-56 text-white"
                           style="background-color: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.18);">
                    <select name="sort" onchange="this.form.submit()" aria-label="Sort vault"
                            class="min-h-[42px] px-3 text-xs font-mono rounded-lg text-white"
                            style="background-color: #12121a; border: 1px solid rgba(255,255,255,0.18);">
                        <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest Drops</option>
                        <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                        <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    </select>
                </form>
            </div>

            {{-- Category Pills --}}
            <nav class="relative z-10 flex items-center gap-3 overflow-x-auto k71-no-scrollbar pt-6 mt-6 text-xs font-mono uppercase tracking-wider" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <a href="{{ $storeCollectionUrl }}" class="px-3.5 py-1.5 rounded-full shrink-0 transition" style="background-color: {{ $currentCategory === '' ? 'var(--t-accent)' : 'rgba(255,255,255,0.08)' }}; color: {{ $currentCategory === '' ? 'var(--t-accent-contrast)' : '#ccc' }};">ALL VAULT</a>
                @foreach(($vendorCategories ?? collect()) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                       class="px-3.5 py-1.5 rounded-full shrink-0 transition hover:bg-white/15"
                       style="background-color: {{ $currentCategory === $cat->slug ? 'var(--t-accent)' : 'rgba(255,255,255,0.08)' }}; color: {{ $currentCategory === $cat->slug ? 'var(--t-accent-contrast)' : '#ccc' }};">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- Dark Stage Grid --}}
        @if(is_countable($products) && count($products) > 0)
            <div data-k71-stagger-grid class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
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
            <div class="py-24 text-center space-y-3" style="border: 1px solid rgba(255,255,255,0.1);">
                <p class="text-xs font-mono uppercase text-zinc-500">Vault Alert</p>
                <h3 class="k71-heading text-xl text-white">No releases currently available in this vault tier</h3>
                <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-primary">View All Releases</a>
            </div>
        @endif
    </div>
</section>
