@php
    $headerTpl = $manifest['layout']['header_template'] ?? 'couture-centered';
    $headerCfg = $themeConfig['header'] ?? [];
    $isSticky = array_key_exists('sticky', $headerCfg) ? (bool) $headerCfg['sticky'] : true;
    $showSearch = array_key_exists('show_search', $headerCfg) ? (bool) $headerCfg['show_search'] : true;
    $logoSizeClass = match($headerCfg['logo_size'] ?? 'md') { 'sm' => 'h-7', 'lg' => 'h-11', default => 'h-9' };
    $stickyClass = $isSticky ? 'sticky top-0 z-40' : 'relative z-40';
    $navCategories = ($vendorCategories ?? collect())->take(5);
@endphp

@if(!empty($headerCfg['announcement_enabled']) && !empty($headerCfg['announcement_text']))
    <div class="w-full px-4 py-2 text-center text-[11px] sm:text-xs font-medium tracking-wide"
         style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
        @if(!empty($headerCfg['announcement_link']))
            <a href="{{ $headerCfg['announcement_link'] }}" class="underline-offset-4 hover:underline">{{ $headerCfg['announcement_text'] }}</a>
        @else
            <span>{{ $headerCfg['announcement_text'] }}</span>
        @endif
    </div>
@endif

@if($headerTpl === 'magazine-masthead')
    {{-- MAGAZINE MASTHEAD (The Curator, Artisan Pantry) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-2 text-[11px]" style="border-bottom: 1px solid var(--t-border); color: var(--t-muted);">
                <span class="k71-eyebrow">{{ $vendor->city ?: 'Editorial Commerce' }}</span>
                <span class="hidden sm:inline font-mono uppercase tracking-widest">{{ now()->format('F Y') }}</span>
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="inline-flex items-center gap-1.5 font-semibold" style="color: var(--t-text);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-3.5 h-3.5'])
                    <span>Bag (<span data-k71-cart-count>{{ $cartCount }}</span>)</span>
                </a>
            </div>
            <div class="py-4 flex items-center justify-between gap-4 k71-editorial-rule">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-3 mx-auto lg:mx-0">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain">
                    @endif
                    <span class="k71-heading text-2xl sm:text-4xl tracking-tight">{{ $storeName }}</span>
                </a>
                <nav class="hidden lg:flex items-center gap-6 text-xs uppercase tracking-widest font-medium" aria-label="Store navigation">
                    <a href="{{ $storeHomeUrl }}" class="hover:opacity-65">Index</a>
                    <a href="{{ $storeCollectionUrl }}" class="hover:opacity-65">Catalogue</a>
                    @foreach($navCategories->take(3) as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-65">{{ $cat->name }}</a>
                    @endforeach
                </nav>
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-70" aria-label="Search store">
                        @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </button>
                @endif
            </div>
        </div>
    </header>

@elseif($headerTpl === 'heritage-framed')
    {{-- HERITAGE DOUBLE-RULE FRAMED HEADER (Monogram, Sartorial, Chronograph, Goldline, Earth & Grain, Maison Gourmet, Heritage House) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 2px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-3.5 sm:py-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                        @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                    </button>
                    <a href="{{ $storeCollectionUrl }}" class="hidden lg:inline-flex items-center text-[11px] uppercase tracking-[0.22em] font-semibold hover:opacity-70">Catalogue</a>
                </div>
                <a href="{{ $storeHomeUrl }}" class="flex flex-col items-center text-center px-4 py-1" style="border-left: 1px solid var(--t-border); border-right: 1px solid var(--t-border);">
                    <span class="k71-heading text-xl sm:text-3xl tracking-wide">{{ $storeName }}</span>
                    @if($vendor->city)
                        <span class="text-[9px] uppercase tracking-[0.26em] mt-0.5" style="color: var(--t-muted);">{{ $vendor->city }}</span>
                    @endif
                </a>
                <div class="flex items-center gap-2 sm:gap-3">
                    @if($showSearch)
                        <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-70" aria-label="Search store">
                            @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                        </button>
                    @endif
                    <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[44px] px-3 flex items-center gap-1.5 text-[11px] uppercase tracking-[0.18em] font-semibold" style="border: 1px solid var(--t-border);">
                        @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-3.5 h-3.5'])
                        <span data-k71-cart-count>{{ $cartCount }}</span>
                    </a>
                </div>
            </div>
            @if($navCategories->isNotEmpty())
                <nav class="hidden lg:flex items-center justify-center gap-8 py-2 text-[11px] uppercase tracking-[0.2em]" style="border-top: 1px solid var(--t-border); color: var(--t-muted);" aria-label="Store categories">
                    <a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Home</a>
                    @foreach($navCategories as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-75">{{ $cat->name }}</a>
                    @endforeach
                </nav>
            @endif
        </div>
    </header>

@elseif($headerTpl === 'utility-catalog')
    {{-- UTILITY SEARCH-FIRST CATALOG HEADER (Circuit, Fresh Market, Pure Science, Denim District, Velocity, Habitat Studio, Market Studio, The Specialist) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-surface); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap lg:flex-nowrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2.5">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain rounded">
                    @endif
                    <span class="k71-heading text-lg sm:text-xl font-bold">{{ $storeName }}</span>
                </a>
            </div>
            @if($showSearch)
                <form action="{{ $storeCollectionUrl }}" method="GET" class="order-3 lg:order-2 w-full lg:flex-1 lg:max-w-xl flex items-center">
                    @if(!empty($isCataloguePreview))
                        <input type="hidden" name="page" value="collection">
                    @endif
                    <div class="relative w-full flex">
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search {{ $storeName }} by product, SKU, or spec..."
                               class="w-full min-h-[44px] pl-4 pr-4 text-sm rounded-l-lg focus:outline-none"
                               style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                        <button type="submit" class="min-h-[44px] px-4 rounded-r-lg text-xs font-semibold shrink-0 inline-flex items-center gap-1.5"
                                style="background-color: var(--t-accent); color: var(--t-accent-contrast);" aria-label="Submit search">
                            @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                            <span class="hidden sm:inline">Search</span>
                        </button>
                    </div>
                </form>
            @endif
            <div class="order-2 lg:order-3 flex items-center gap-2 shrink-0">
                <a href="{{ $storeCollectionUrl }}" class="hidden md:inline-flex items-center px-3 min-h-[44px] text-xs font-semibold hover:opacity-75">Catalogue</a>
                <a href="{{ auth()->check() ? route('account') : route('login') }}" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-75" aria-label="Account">
                    @include('storefront.themes.partials.icon', ['name' => 'user', 'class' => 'w-4 h-4'])
                </a>
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="k71-btn k71-btn-primary !py-2 !px-3.5">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                    <span data-k71-cart-count class="text-xs font-bold">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@elseif($headerTpl === 'floating-pill-nav')
    {{-- FLOATING CAPSULE NAVIGATION (Soft Form, Luxe Objects, Future Form, Botanical Lab, Sweet Atelier, Neo Commerce, Pop Form) --}}
    <header class="k71-header {{ $stickyClass }} w-full px-3 sm:px-6 pt-3 pb-2">
        <div class="max-w-7xl mx-auto rounded-full px-4 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-4 k71-surface shadow-sm">
            <div class="flex items-center gap-2.5 min-w-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center rounded-full" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2.5 min-w-0">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain rounded-full">
                    @endif
                    <span class="k71-heading text-lg sm:text-xl truncate">{{ $storeName }}</span>
                </a>
            </div>
            <nav class="hidden lg:flex items-center gap-6 text-xs font-semibold" aria-label="Store navigation">
                <a href="{{ $storeHomeUrl }}" class="hover:opacity-70">Home</a>
                <a href="{{ $storeCollectionUrl }}" class="hover:opacity-70">Shop All</a>
                @foreach($navCategories->take(4) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-70">{{ $cat->name }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-1.5 shrink-0">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center hover:opacity-70" aria-label="Search store">
                        @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[40px] px-3.5 rounded-full text-xs font-bold inline-flex items-center gap-1.5" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-3.5 h-3.5'])
                    <span data-k71-cart-count>{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@elseif($headerTpl === 'brutalist-bar')
    {{-- ARCHITECTURAL BRUTALIST BAR (Street Culture, Sneaker Vault, Mono Tech, Forma Gallery, The Drop) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 2px solid var(--t-text);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="k71-heading text-xl sm:text-2xl uppercase tracking-tight truncate">{{ $storeName }}</a>
            </div>
            <nav class="hidden lg:flex items-center gap-1 text-xs font-bold uppercase tracking-wider" aria-label="Store navigation">
                <a href="{{ $storeCollectionUrl }}" class="px-3 py-2 hover:opacity-70">All</a>
                @foreach($navCategories->take(4) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="px-3 py-2 hover:opacity-70">{{ $cat->name }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-2 shrink-0">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center" style="border: 1px solid var(--t-border);" aria-label="Search">
                        @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[44px] px-4 inline-flex items-center gap-2 text-xs font-extrabold uppercase tracking-wider" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                    <span data-k71-cart-count>{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@elseif($headerTpl === 'couture-centered' || $headerTpl === 'gallery-minimal')
    {{-- COUTURE CENTERED / GALLERY MINIMAL HEADER --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 grid grid-cols-3 items-center">
            <div class="flex items-center gap-5">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <nav class="hidden lg:flex items-center gap-6 text-[11px] uppercase tracking-[0.18em] font-medium" aria-label="Store navigation">
                    <a href="{{ $storeCollectionUrl }}" class="hover:opacity-65">Shop</a>
                    @foreach($navCategories->take(2) as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-65 truncate max-w-[115px]">{{ $cat->name }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="flex justify-center">
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2.5 text-center">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain">
                    @endif
                    <span class="k71-heading text-xl sm:text-2xl tracking-wide truncate max-w-[170px] sm:max-w-[260px]">{{ $storeName }}</span>
                </a>
            </div>
            <div class="flex items-center justify-end gap-2 sm:gap-3">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-65" aria-label="Search store">
                        @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[44px] px-2.5 inline-flex items-center gap-1.5 text-[11px] uppercase tracking-[0.16em] font-semibold hover:opacity-75">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                    <span data-k71-cart-count>{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@else
    {{-- OBSIDIAN OVERLAY / SPLIT STUDIO HEADER --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2.5 min-w-0">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain rounded">
                    @endif
                    <span class="k71-heading text-lg sm:text-xl truncate">{{ $storeName }}</span>
                </a>
            </div>
            <nav class="hidden lg:flex items-center gap-6 text-xs font-semibold tracking-wide" aria-label="Store navigation">
                <a href="{{ $storeHomeUrl }}" class="hover:opacity-70">Home</a>
                <a href="{{ $storeCollectionUrl }}" class="hover:opacity-70">Collection</a>
                @foreach($navCategories->take(4) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-70">{{ $cat->name }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-2 shrink-0">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-70" aria-label="Search store">
                        @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[44px] px-3 inline-flex items-center gap-1.5 text-xs font-semibold rounded" style="border: 1px solid var(--t-border);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                    <span data-k71-cart-count style="color: var(--t-accent);">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>
@endif
