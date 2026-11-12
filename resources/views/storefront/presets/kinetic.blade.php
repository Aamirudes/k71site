{{-- PRESET 11: KINETIC (Obsidian Cinema · Video-to-JPG Scroll Motion · Orchid Accents) --}}
@php
    $brandCfg = $config['brand'] ?? [];
    $storeName = $brandCfg['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brandCfg['logo_url'] ?? $vendor->logo_url;
    $currentCategory = request('category');
    $currentSearch = request('search');
    $currentSort = request('sort', 'latest');
    $prodLayout = $config['products']['layout'] ?? 'grid';
@endphp

<div class="storefront-preset-kinetic min-h-screen flex flex-col font-body selection:bg-[#ECD2F3] selection:text-[#1A1018] bg-[#000000] text-[#EFE9F2] relative overflow-x-clip"
     style="font-family: 'Switzer', var(--sf-font-body, system-ui, sans-serif); --sf-accent: {{ $theme['accent_color'] ?? '#ECD2F3' }}; --sf-primary: #EFE9F2;"
     x-data="{ drawerOpen: false }">

    @if(!empty($config['header']['announcement_enabled']) && !empty($config['header']['announcement_text']) && trim($config['header']['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    @if(!empty($isServiceWebsite))
        @php $nav = $providerSite['nav_labels'] ?? []; $id = $providerSite['identity'] ?? []; @endphp
        {{-- Floating Obsidian & Orchid Glass Capsule (Service Website — zero eCommerce bag/cart clutter) --}}
        <div class="fixed top-3 sm:top-5 inset-x-3 sm:inset-x-6 lg:inset-x-8 z-50 pointer-events-none">
            <header class="max-w-6xl mx-auto p-2 sm:p-2.5 rounded-full bg-[#0a0a0d]/75 backdrop-blur-2xl border border-white/16 ring-1 ring-[#ECD2F3]/25 shadow-[0_20px_50px_rgba(0,0,0,0.85)] text-white pointer-events-auto flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 pl-1 sm:pl-2 shrink-0">
                    <button type="button" @click="drawerOpen = true" class="p-1.5 sm:p-2 text-[#ECD2F3] hover:text-white transition cursor-pointer flex items-center justify-center shrink-0" aria-label="Open menu">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <a href="{{ $vendor->storefront_url }}" class="flex items-center gap-2.5 group">
                        <div class="relative flex items-center justify-center w-8 h-8 rounded-full bg-[#16161b] border border-[#ECD2F3]/50 overflow-hidden shrink-0">
                            @if($storeLogo)
                                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-[10px] font-bold text-[#ECD2F3]">{{ $id['initials'] ?? 'K7' }}</span>
                            @endif
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-sm sm:text-base tracking-tight text-white group-hover:text-[#ECD2F3] transition truncate max-w-[140px] sm:max-w-xs">{{ $storeName }}</span>
                            <span class="text-[9px] font-mono font-bold text-[#C9A24A]">{{ strtoupper($id['provider_mode_label'] ?? 'STUDIO') }}</span>
                        </div>
                    </a>
                </div>

                <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-white/[0.06] border border-white/12 text-xs font-medium">
                    <a href="#overview" class="px-3 py-1.5 rounded-full text-[#EFE9F2]/80 hover:text-white hover:bg-white/12 transition">{{ $nav['overview'] ?? 'Overview' }}</a>
                    <a href="#portfolio" class="px-3 py-1.5 rounded-full text-[#EFE9F2]/80 hover:text-white hover:bg-white/12 transition">{{ $nav['work'] ?? 'Work' }}</a>
                    <a href="#services" class="px-3 py-1.5 rounded-full text-[#EFE9F2]/80 hover:text-white hover:bg-white/12 transition">{{ $nav['services'] ?? 'Services' }}</a>
                    <a href="#about" class="px-3 py-1.5 rounded-full text-[#EFE9F2]/80 hover:text-white hover:bg-white/12 transition">{{ $nav['about'] ?? 'About' }}</a>
                    <a href="#reviews" class="px-3 py-1.5 rounded-full text-[#EFE9F2]/80 hover:text-white hover:bg-white/12 transition">{{ $nav['reviews'] ?? 'Reviews' }}</a>
                </nav>

                <a href="#contact" class="px-4 py-2 rounded-full bg-[#ECD2F3] hover:bg-[#F6E3FB] text-[#1A1018] font-bold text-xs tracking-wide transition shrink-0">
                    {{ $id['cta_primary_text'] ?? 'Start a Project' }}
                </a>
            </header>
        </div>

        {{-- ACT 1: Sticky Scroll-Scrubbed Motion Stage --}}
        @include('storefront.sections.scroll_sequence', [
            'seqSkipHref' => '#overview',
            'seqCtaHref' => ($providerSite['metrics']['portfolio_count'] ?? 0) > 0 ? '#portfolio' : (($providerSite['metrics']['services_count'] ?? 0) > 0 ? '#services' : '#contact'),
            'seqCtaLabel' => ($providerSite['metrics']['portfolio_count'] ?? 0) > 0 ? 'View Selected Work' : (($providerSite['metrics']['services_count'] ?? 0) > 0 ? 'Explore Services' : 'Start a Project'),
            'seqSecondaryHref' => '#contact',
            'seqSecondaryLabel' => 'Book Discovery Call',
            'seqUseH1' => false,
        ])

        {{-- ACT 2: Provider Portfolio, Services & Booking Flow --}}
        @include('storefront.sections.service.main')

        {{-- Navigation Drawer --}}
        @include('storefront.sections.theme_drawer')
    @else
        {{-- Retail Header --}}
        @include('storefront.sections.header')

        <main class="flex-1">
            {{-- ACT 1: Video-to-JPG Scroll-Scrubbed Motion Stage (matches Marketplace flow) --}}
            @include('storefront.sections.scroll_sequence', [
                'seqSkipHref' => '#kinetic-products',
                'seqCtaHref' => '#kinetic-products',
                'seqCtaLabel' => ($config['scroll_sequence']['cta_text'] ?? 'Explore Collection'),
                'seqSecondaryHref' => $vendor->storefrontUrl([], 'kinetic-products'),
                'seqSecondaryLabel' => 'All Releases',
            ])

            {{-- ACT 2: Direct Storefront Product Showcase immediately following scroll motion --}}
            <section id="kinetic-products"
                     data-section-id="sec_kinetic_products"
                     class="bg-[#000000] px-3 sm:px-6 lg:px-8 py-12 sm:py-16">
                <span id="catalog"></span>
                <div class="max-w-7xl mx-auto space-y-8">

                    {{-- Editorial Collection Header + Search & Sort Controls --}}
                    <div class="p-5 sm:p-8 rounded-3xl bg-[#0b0b0e] border border-white/12 flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                        <div class="space-y-2">
                            <div class="inline-flex items-center gap-2">
                                <span class="px-3 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold uppercase tracking-[0.14em]">
                                    Verified Drop
                                </span>
                                <span class="text-xs font-mono text-[#C9A24A]">
                                    {{ $products->total() ?? $products->count() }} {{ ($products->total() ?? $products->count()) === 1 ? 'Piece' : 'Pieces' }}
                                </span>
                            </div>
                            <h2 class="kinetic-display text-3xl sm:text-5xl text-white">
                                {{ $storeName }} Collection
                            </h2>
                            <p class="text-xs sm:text-sm text-[#EFE9F2]/68 max-w-xl">
                                Direct studio pieces with encrypted escrow checkout and insured Pan-India dispatch.
                            </p>
                        </div>

                        {{-- Search & Sort Form --}}
                        <form action="{{ $vendor->storefrontUrl([], 'kinetic-products') }}" method="GET" class="flex flex-wrap sm:flex-nowrap items-center gap-2.5 w-full lg:w-auto">
                            @if($currentCategory)
                                <input type="hidden" name="category" value="{{ $currentCategory }}">
                            @endif
                            <div class="relative flex-1 sm:w-60">
                                <input type="text"
                                       name="search"
                                       value="{{ $currentSearch }}"
                                       placeholder="Search collection..."
                                       class="w-full px-4 py-2.5 rounded-full bg-[#141418] border border-white/15 text-xs sm:text-sm text-[#EFE9F2] placeholder-[#EFE9F2]/40 focus:outline-none focus:border-[#ECD2F3]">
                            </div>
                            <select name="sort"
                                    onchange="this.form.submit()"
                                    class="px-4 py-2.5 rounded-full bg-[#141418] border border-white/15 text-xs sm:text-sm text-[#EFE9F2] focus:outline-none focus:border-[#ECD2F3] cursor-pointer">
                                <option value="latest" {{ $currentSort === 'latest' ? 'selected' : '' }}>Latest Drops</option>
                                <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>
                            <button type="submit" class="px-5 py-2.5 rounded-full bg-[#ECD2F3] hover:bg-[#F6E3FB] text-[#1A1018] text-xs font-bold uppercase tracking-wider cursor-pointer transition shrink-0">
                                Filter
                            </button>
                        </form>
                    </div>

                    {{-- Category Filter Pills --}}
                    @if($vendorCategories->isNotEmpty())
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 no-scrollbar">
                            <a href="{{ $vendor->storefrontUrl([], 'kinetic-products') }}"
                               class="px-4 py-2 rounded-full text-xs font-semibold tracking-wide transition shrink-0 {{ !$currentCategory ? 'bg-[#ECD2F3] text-[#1A1018]' : 'bg-[#141418] text-[#EFE9F2]/75 hover:text-white border border-white/12' }}">
                                All Pieces
                            </a>
                            @foreach($vendorCategories as $c)
                                <a href="{{ $vendor->storefrontUrl(['category' => $c->slug], 'kinetic-products') }}"
                                   class="px-4 py-2 rounded-full text-xs font-semibold tracking-wide transition shrink-0 {{ $currentCategory === $c->slug ? 'bg-[#ECD2F3] text-[#1A1018]' : 'bg-[#141418] text-[#EFE9F2]/75 hover:text-white border border-white/12' }}">
                                    {{ $c->name }} ({{ $c->products_count }})
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- Products Shelf / Grid --}}
                    @if($products->isEmpty())
                        <div class="py-16 px-6 text-center rounded-3xl bg-[#0b0b0e] border border-white/12 space-y-4">
                            <span class="inline-block px-3.5 py-1 rounded-full bg-[#ECD2F3]/15 text-[#ECD2F3] text-xs font-semibold">
                                {{ $currentSearch || $currentCategory ? 'No Matching Pieces' : 'Showcase Ready' }}
                            </span>
                            <h3 class="kinetic-display text-2xl sm:text-3xl text-white">
                                {{ $currentSearch || $currentCategory ? 'Adjust Your Filter' : 'New Releases Arriving Soon' }}
                            </h3>
                            <p class="text-xs sm:text-sm text-[#EFE9F2]/65 max-w-md mx-auto">
                                {{ $currentSearch || $currentCategory ? 'Clear your active search or category filter to view every item in this boutique.' : ($vendor->shop_name . ' is curating upcoming releases on K71.') }}
                            </p>
                            @if($currentSearch || $currentCategory)
                                <a href="{{ $vendor->storefrontUrl([], 'kinetic-products') }}" class="kinetic-cta-pill inline-flex items-center gap-2 px-6 py-2.5 text-xs">
                                    <span>Reset Catalog</span>
                                    <span>&rarr;</span>
                                </a>
                            @endif
                        </div>
                    @else
                        <div class="{{ $prodLayout === 'slider' ? 'flex gap-4 sm:gap-6 overflow-x-auto pb-3 snap-x snap-mandatory no-scrollbar' : 'grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6' }}">
                            @foreach($products as $product)
                                @php
                                    $priceRupees = number_format($product->base_price / 100, 2);
                                    $hasComparePrice = !empty($product->compare_at_price) && $product->compare_at_price > $product->base_price;
                                    $comparePriceRupees = $hasComparePrice ? number_format($product->compare_at_price / 100, 2) : null;
                                    $discountPct = $hasComparePrice ? round((($product->compare_at_price - $product->base_price) / $product->compare_at_price) * 100) : 0;
                                    $imgFirst = $product->images->first()?->path ? \App\Services\ImageService::url($product->images->first()->path, 600) : asset('images/product-placeholder.svg');
                                    $isOutOfStock = $product->total_stock <= 0 && $product->variants->sum('stock') <= 0;
                                    $defaultVariantId = $product->variants->first()?->id;
                                @endphp

                                <article class="group flex flex-col justify-between rounded-3xl p-3 sm:p-4 bg-[#111115] border border-white/12 hover:border-[#ECD2F3]/50 hover:shadow-[0_20px_50px_rgba(236,210,243,0.14)] transition-all duration-300 {{ $prodLayout === 'slider' ? 'shrink-0 w-[68vw] sm:w-[270px] snap-start' : '' }}">
                                    <div class="relative aspect-[4/5] w-full rounded-2xl bg-[#070709] overflow-hidden mb-3">
                                        <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full" aria-label="{{ $product->title }}">
                                            <img src="{{ $imgFirst }}"
                                                 alt="{{ $product->title }}"
                                                 loading="lazy"
                                                 class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105 {{ $isOutOfStock ? 'opacity-40 grayscale' : '' }}">
                                        </a>

                                        @if($hasComparePrice && $discountPct > 0)
                                            <span class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold uppercase tracking-wider">
                                                -{{ $discountPct }}%
                                            </span>
                                        @endif

                                        @if($isOutOfStock)
                                            <span class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full bg-black/85 border border-white/20 text-[#EFE9F2]/75 text-[10px] font-mono uppercase">
                                                Sold Out
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex-1 flex flex-col justify-between gap-3">
                                        <div>
                                            @if($product->category)
                                                <span class="text-[10px] font-semibold uppercase tracking-[0.14em] text-[#C9A24A] block mb-1">
                                                    {{ $product->category->name }}
                                                </span>
                                            @endif
                                            <h3 class="text-xs sm:text-sm font-semibold text-white line-clamp-2 leading-snug group-hover:text-[#ECD2F3] transition">
                                                <a href="{{ route('product.show', $product->slug) }}">
                                                    {{ $product->title }}
                                                </a>
                                            </h3>
                                        </div>

                                        <div class="pt-2.5 border-t border-white/10 space-y-2.5">
                                            <div class="flex items-baseline justify-between gap-2">
                                                <span class="text-sm sm:text-base font-bold text-white tabular-nums">
                                                    ₹{{ $priceRupees }}
                                                </span>
                                                @if($hasComparePrice)
                                                    <span class="text-xs text-[#EFE9F2]/45 line-through tabular-nums">
                                                        ₹{{ $comparePriceRupees }}
                                                    </span>
                                                @endif
                                            </div>

                                            @if(!$isOutOfStock)
                                                <button type="button"
                                                        onclick="window.quickAddToCart({{ $product->id }}, {{ $defaultVariantId ?: 'null' }}, this)"
                                                        class="w-full py-2.5 px-3 rounded-full bg-[#ECD2F3] hover:bg-[#F6E3FB] text-[#1A1018] font-bold text-xs tracking-wide transition-all flex items-center justify-center gap-1.5 cursor-pointer active:scale-95">
                                                    <span>+ Add to Bag</span>
                                                </button>
                                            @else
                                                <div class="w-full py-2 text-center text-[11px] font-medium text-[#EFE9F2]/45 bg-white/5 rounded-full border border-white/10">
                                                    Out of Stock
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        @if(method_exists($products, 'links') && $products->hasPages())
                            <div class="pt-6 flex justify-center">
                                {{ $products->fragment('kinetic-products')->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </section>

            {{-- Video Reels Showcase --}}
            @include('storefront.sections.video_showcase')

            {{-- Verified Reviews --}}
            @include('storefront.sections.reviews_section')

            {{-- Happy Customers Slider --}}
            @include('storefront.sections.happy_customers')

            {{-- Store Contact & Policies --}}
            @include('storefront.sections.contact_policies')
        </main>

        {{-- Storefront Footer & Slide Navigation Drawer --}}
        @include('storefront.sections.footer')
        @include('storefront.sections.theme_drawer')
    @endif
</div>
