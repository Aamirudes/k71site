@php
    $storeName = $storeName ?? ($vendor->shop_name ?? 'K71 Store');
    $storeHomeUrl = $storeHomeUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=home')
        : ($vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '')));
    $storeCollectionUrl = $storeCollectionUrl ?? (!empty($previewFrameBaseUrl)
        ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=collection')
        : route('storefront.collection', ['slug' => $vendor->slug]) . (!empty($isPreview) ? '?preview=1' : ''));

    $sections = $themeConfig['sections'] ?? ($manifest['default_sections'] ?? []);
    $gridCols = (int) ($themeConfig['grid']['columns'] ?? ($manifest['layout']['default_columns'] ?? 4));
    $gridSpacing = $themeConfig['grid']['spacing'] ?? 'normal';
    $gapClass = match($gridSpacing) { 'compact' => 'gap-3 sm:gap-4', 'spacious' => 'gap-6 sm:gap-9', default => 'gap-4 sm:gap-6' };
    $colsClass = match($gridCols) { 2 => 'grid-cols-1 sm:grid-cols-2', 3 => 'grid-cols-2 lg:grid-cols-3', default => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4' };
@endphp

@foreach($sections as $sec)
    @continue(empty($sec['enabled']))
    @php $secType = $sec['type'] ?? ''; @endphp

    @if($secType === 'hero')
        @include('storefront.themes.partials.hero')

    @elseif($secType === 'category_nav' && ($vendorCategories ?? collect())->isNotEmpty())
        @php $catStyle = $sec['style'] ?? 'pill-rail'; @endphp
        <section class="w-full py-7 sm:py-10" data-k71-reveal>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @if($catStyle === 'oversized-marquee')
                    <div class="flex flex-wrap items-baseline gap-x-8 gap-y-3 py-4" style="border-top: 2px solid var(--t-text); border-bottom: 2px solid var(--t-text);">
                        @foreach($vendorCategories->take(8) as $cat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                               class="k71-heading text-2xl sm:text-4xl uppercase hover:opacity-65 transition">
                                {{ $cat->name }}
                                <span class="text-xs font-mono align-super" style="color: var(--t-accent);">{{ $cat->products_count ?? '' }}</span>
                            </a>
                        @endforeach
                    </div>
                @elseif($catStyle === 'framed-tiles')
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="k71-heading text-xl sm:text-2xl">Browse Departments</h2>
                        <a href="{{ $storeCollectionUrl }}" class="text-xs font-semibold inline-flex items-center gap-1 hover:opacity-75" style="color: var(--t-accent);">All Items @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                        @foreach($vendorCategories->take(6) as $cat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                               class="p-4 k71-surface flex flex-col justify-between gap-3 hover:opacity-85 transition min-h-[96px]">
                                <span class="k71-eyebrow" style="color: var(--t-muted);">{{ $cat->products_count ?? 0 }} pieces</span>
                                <span class="k71-heading text-sm sm:text-base font-semibold leading-snug">{{ $cat->name }}</span>
                            </a>
                        @endforeach
                    </div>
                @elseif($catStyle === 'hairline-index')
                    <div class="py-3" style="border-top: 1px solid var(--t-border); border-bottom: 1px solid var(--t-border);">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                            @foreach($vendorCategories->take(8) as $idx => $cat)
                                <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                                   class="flex items-center justify-between py-2.5 px-3 hover:opacity-70 transition" style="border-bottom: 1px solid var(--t-border);">
                                    <span class="text-xs font-mono" style="color: var(--t-muted);">{{ str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-sm font-medium flex-1 px-3 truncate">{{ $cat->name }}</span>
                                    @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                                </a>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2.5 overflow-x-auto k71-no-scrollbar pb-1">
                        <a href="{{ $storeCollectionUrl }}" class="shrink-0 min-h-[40px] px-4 rounded-full text-xs font-semibold inline-flex items-center" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">All Products</a>
                        @foreach($vendorCategories->take(10) as $cat)
                            <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                               class="shrink-0 min-h-[40px] px-4 rounded-full text-xs font-medium inline-flex items-center k71-surface hover:opacity-80 transition">
                                {{ $cat->name }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

    @elseif($secType === 'featured_showcase' && ($featuredProducts ?? collect())->isNotEmpty())
        @php
            $showStyle = $sec['style'] ?? 'spotlight-row';
            $spotItems = $featuredProducts->take(6);
        @endphp
        <section class="w-full py-10 sm:py-14" data-k71-reveal>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-6 sm:mb-8">
                    <div>
                        <p class="k71-eyebrow mb-1" style="color: var(--t-accent);">Curated Spotlight</p>
                        <h2 class="k71-heading text-2xl sm:text-3xl">Featured Selections</h2>
                    </div>
                    <a href="{{ $storeCollectionUrl }}" class="text-xs font-semibold inline-flex items-center gap-1 hover:opacity-75" style="color: var(--t-muted);">
                        Explore All @include('storefront.themes.partials.icon', ['name' => 'arrow-right', 'class' => 'w-3.5 h-3.5'])
                    </a>
                </div>

                @if($showStyle === 'horizontal-rail')
                    <div class="flex gap-4 overflow-x-auto k71-no-scrollbar pb-2 snap-x">
                        @foreach($spotItems as $fp)
                            <div class="snap-start shrink-0 w-[72vw] sm:w-[270px] lg:w-[290px]">
                                @include('storefront.themes.partials.card', ['product' => $fp])
                            </div>
                        @endforeach
                    </div>
                @elseif(in_array($showStyle, ['asymmetric-editorial', 'magazine-spread'], true) && $spotItems->count() >= 2)
                    @php
                        $lead = $spotItems->first();
                        $rest = $spotItems->skip(1)->take(3);
                        $leadUrl = !empty($previewFrameBaseUrl)
                            ? ($previewFrameBaseUrl . (str_contains($previewFrameBaseUrl, '?') ? '&' : '?') . 'page=product&product=' . urlencode($lead->slug))
                            : route('storefront.product', ['slug' => $vendor->slug, 'productSlug' => $lead->slug]);
                    @endphp
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
                        <div class="lg:col-span-6 k71-card-shell overflow-hidden flex flex-col">
                            <a href="{{ $leadUrl }}" class="block aspect-[4/3] w-full overflow-hidden" style="background-color: var(--t-surface-alt);">
                                <img src="{{ $lead->images?->first()?->url ?? asset('images/product-placeholder.svg') }}" alt="{{ $lead->title }}" loading="lazy" class="w-full h-full object-cover">
                            </a>
                            <div class="p-6 flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <p class="k71-eyebrow mb-1" style="color: var(--t-muted);">Lead Piece</p>
                                    <h3 class="k71-heading text-xl sm:text-2xl">{{ $lead->title }}</h3>
                                    <p class="text-base font-bold mt-1">₹{{ number_format(($lead->base_price ?? 0) / 100, 0) }}</p>
                                </div>
                                <a href="{{ $leadUrl }}" class="k71-btn k71-btn-primary">View Piece</a>
                            </div>
                        </div>
                        <div class="lg:col-span-6 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                            @foreach($rest as $fp)
                                @include('storefront.themes.partials.card', ['product' => $fp])
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-2 lg:grid-cols-4 {{ $gapClass }}">
                        @foreach($spotItems->take(4) as $fp)
                            @include('storefront.themes.partials.card', ['product' => $fp])
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

    @elseif($secType === 'editorial_feature')
        @php
            $edCfg = $themeConfig['editorial'] ?? [];
            $edHeading = trim((string) ($edCfg['heading'] ?? ''));
            $edBody = trim((string) ($edCfg['body'] ?? ''));
            $edImg = trim((string) ($edCfg['image_url'] ?? ''));
        @endphp
        @if($edHeading !== '' || $edBody !== '')
            <section class="w-full py-10 sm:py-16" data-k71-reveal>
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="k71-surface overflow-hidden grid grid-cols-1 lg:grid-cols-12 items-center">
                        @if($edImg !== '')
                            <div class="lg:col-span-6 aspect-[4/3] overflow-hidden" style="background-color: var(--t-surface-alt);">
                                <img src="{{ $edImg }}" alt="{{ $edHeading ?: $storeName }}" loading="lazy" class="w-full h-full object-cover">
                            </div>
                        @endif
                        <div class="{{ $edImg !== '' ? 'lg:col-span-6' : 'lg:col-span-12 max-w-3xl mx-auto text-center' }} p-8 sm:p-12 space-y-4">
                            @if(!empty($edCfg['eyebrow']))<p class="k71-eyebrow" style="color: var(--t-accent);">{{ $edCfg['eyebrow'] }}</p>@endif
                            @if($edHeading !== '')<h2 class="k71-heading text-2xl sm:text-4xl">{{ $edHeading }}</h2>@endif
                            @if($edBody !== '')<p class="text-sm sm:text-base leading-relaxed" style="color: var(--t-muted);">{{ $edBody }}</p>@endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

    @elseif($secType === 'product_grid')
        <section id="collection" class="w-full py-10 sm:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-6 sm:mb-8" data-k71-reveal>
                    <div>
                        <p class="k71-eyebrow mb-1" style="color: var(--t-accent);">Catalogue</p>
                        <h2 class="k71-heading text-2xl sm:text-3xl">{{ $sec['title'] ?? 'Collection' }}</h2>
                    </div>
                    <a href="{{ $storeCollectionUrl }}" class="k71-btn k71-btn-outline !min-h-[40px] !py-2">
                        @include('storefront.themes.partials.icon', ['name' => 'sliders', 'class' => 'w-3.5 h-3.5'])
                        <span>Filter &amp; View All</span>
                    </a>
                </div>

                @if(($products ?? collect())->isEmpty())
                    <div class="k71-surface p-10 text-center space-y-3 max-w-lg mx-auto">
                        <h3 class="k71-heading text-lg">No products listed yet</h3>
                        <p class="text-sm" style="color: var(--t-muted);">Products published by {{ $storeName }} will appear here automatically.</p>
                    </div>
                @else
                    <div class="grid {{ $colsClass }} {{ $gapClass }}">
                        @foreach($products as $product)
                            @include('storefront.themes.partials.card', ['product' => $product])
                        @endforeach
                    </div>
                    @if(method_exists($products, 'hasPages') && $products->hasPages())
                        <div class="mt-10 flex justify-center">{{ $products->links() }}</div>
                    @endif
                @endif
            </div>
        </section>

    @elseif($secType === 'reviews_social' && ($vendorReviews ?? collect())->isNotEmpty())
        @php $avgStoreRating = round((float) $vendorReviews->avg('rating'), 1); @endphp
        <section id="reviews" class="w-full py-10 sm:py-14" data-k71-reveal>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
                    <div>
                        <p class="k71-eyebrow mb-1" style="color: var(--t-accent);">Verified Buyer Feedback</p>
                        <h2 class="k71-heading text-2xl sm:text-3xl">Customer Reviews</h2>
                    </div>
                    <div class="px-4 py-2 rounded-full text-xs font-semibold k71-surface inline-flex items-center gap-2">
                        <span class="inline-flex items-center gap-1" style="color: var(--t-accent);">@include('storefront.themes.partials.icon', ['name' => 'star', 'class' => 'w-3.5 h-3.5']) {{ number_format($avgStoreRating, 1) }}</span>
                        <span style="color: var(--t-muted);">({{ $vendorReviews->count() }} {{ \Illuminate\Support\Str::plural('review', $vendorReviews->count()) }})</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($vendorReviews->take(6) as $rev)
                        <article class="k71-surface p-5 sm:p-6 flex flex-col justify-between gap-4">
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold" style="color: var(--t-accent);">{{ (int) $rev->rating }} / 5</span>
                                    <span style="color: var(--t-muted);">{{ $rev->created_at?->format('M d, Y') }}</span>
                                </div>
                                @if(!empty($rev->comment))
                                    <p class="text-sm leading-relaxed">{{ \Illuminate\Support\Str::limit($rev->comment, 220) }}</p>
                                @endif
                            </div>
                            <div class="pt-3 flex items-center justify-between gap-2 text-xs" style="border-top: 1px solid var(--t-border); color: var(--t-muted);">
                                <span class="font-semibold" style="color: var(--t-text);">{{ $rev->user?->name ?? 'Verified Buyer' }}</span>
                                @if(!empty($rev->product?->title))<span class="truncate max-w-[140px]">{{ $rev->product->title }}</span>@endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

    @elseif($secType === 'trust_policies')
        @php
            $returnDays = $vendor->settings?->return_window_days;
            $processingDays = $vendor->settings?->processing_days;
        @endphp
        <section id="policies" class="w-full py-10 sm:py-12" data-k71-reveal>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-6 sm:p-8 k71-surface grid grid-cols-1 sm:grid-cols-3 gap-6">
                    <div class="flex items-start gap-3">
                        <span class="p-2.5 rounded-lg shrink-0" style="background-color: var(--t-surface-alt); color: var(--t-accent);">@include('storefront.themes.partials.icon', ['name' => 'shield', 'class' => 'w-4 h-4'])</span>
                        <div>
                            <h3 class="text-sm font-semibold">{{ $storeName }}</h3>
                            <p class="text-xs mt-1 leading-relaxed" style="color: var(--t-muted);">{{ $vendor->city ? 'Dispatched from ' . $vendor->city . ($vendor->state ? ', ' . $vendor->state : '') . '.' : 'Verified store on K71 Marketplace.' }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="p-2.5 rounded-lg shrink-0" style="background-color: var(--t-surface-alt); color: var(--t-accent);">@include('storefront.themes.partials.icon', ['name' => 'truck', 'class' => 'w-4 h-4'])</span>
                        <div>
                            <h3 class="text-sm font-semibold">{{ $processingDays ? "Ships within {$processingDays} business days" : 'Tracked Order Fulfilment' }}</h3>
                            <p class="text-xs mt-1 leading-relaxed" style="color: var(--t-muted);">Orders processed securely through K71 checkout with tracking updates.</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="p-2.5 rounded-lg shrink-0" style="background-color: var(--t-surface-alt); color: var(--t-accent);">@include('storefront.themes.partials.icon', ['name' => 'refresh', 'class' => 'w-4 h-4'])</span>
                        <div>
                            <h3 class="text-sm font-semibold">{{ $returnDays ? "{$returnDays}-Day Return Policy" : 'Direct Merchant Assistance' }}</h3>
                            <p class="text-xs mt-1 leading-relaxed" style="color: var(--t-muted);">Return eligibility is clearly shown on each individual product page.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endforeach
