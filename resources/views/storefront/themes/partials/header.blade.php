@php
    $headerTpl = $manifest['layout']['header_template'] ?? 'couture-centered';
    $headerCfg = $themeConfig['header'] ?? [];
    $isSticky = array_key_exists('sticky', $headerCfg) ? (bool) $headerCfg['sticky'] : true;
    $showSearch = array_key_exists('show_search', $headerCfg) ? (bool) $headerCfg['show_search'] : true;
    $logoSizeClass = match($headerCfg['logo_size'] ?? 'md') { 'sm' => 'h-7', 'lg' => 'h-11', default => 'h-9' };
    $stickyClass = $isSticky ? 'sticky top-0 z-40' : 'relative z-40';
    $navCategories = ($vendorCategories ?? collect())->take(6);
@endphp

@if(!empty($headerCfg['announcement_enabled']) && !empty($headerCfg['announcement_text']))
    <aside aria-label="Announcement" class="w-full px-4 py-2 text-center text-[11px] sm:text-xs font-medium tracking-wide"
         style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
        @if(!empty($headerCfg['announcement_link']))
            <a href="{{ $headerCfg['announcement_link'] }}" class="underline-offset-4 hover:underline">{{ $headerCfg['announcement_text'] }}</a>
        @else
            <span>{{ $headerCfg['announcement_text'] }}</span>
        @endif
    </aside>
@endif

@php
    $customHeader = 'storefront.themes.bespoke.' . ($manifest['id'] ?? '') . '.header';
@endphp

@if(view()->exists($customHeader))
    @include($customHeader)
@elseif($headerTpl === 'magazine-masthead')
    {{-- MAGAZINE MASTHEAD (The Curator, Artisan Pantry, etc.) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Top Publication Rule --}}
            <div class="flex items-center justify-between py-1.5 sm:py-2 text-[10px] sm:text-[11px]" style="border-bottom: 1px solid var(--t-border); color: var(--t-muted);">
                <div class="flex items-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full" style="background-color: var(--t-accent);"></span>
                    <span class="font-mono uppercase tracking-widest font-bold">{{ $vendor->city ?: 'EDITORIAL ISSUE' }}</span>
                </div>
                <span class="font-mono uppercase tracking-[0.2em]">{{ now()->format('M Y') }} · VOL. IV</span>
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="inline-flex items-center gap-1 font-semibold" style="color: var(--t-text);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-3.5 h-3.5'])
                    <span>BAG (<span data-k71-cart-count>{{ $cartCount }}</span>)</span>
                </a>
            </div>

            {{-- Main Title Spread --}}
            <div class="py-3 sm:py-4 flex items-center justify-between gap-4">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center -ml-2" aria-label="Open index">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-3 mx-auto lg:mx-0 text-center">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain">
                    @endif
                    <span class="k71-heading text-2xl sm:text-4xl lg:text-5xl tracking-tight leading-none">{{ $storeName }}</span>
                </a>
                <nav class="hidden lg:flex items-center gap-6 text-xs uppercase tracking-widest font-medium" aria-label="Store navigation">
                    <a href="{{ $storeHomeUrl }}" class="hover:opacity-65">Index</a>
                    <a href="{{ $storeCollectionUrl }}" class="hover:opacity-65">Catalogue</a>
                    @foreach($navCategories->take(3) as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-65">{{ $cat->name }}</a>
                    @endforeach
                </nav>
                <div class="flex items-center gap-2">
                    @if($showSearch)
                        <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-70" aria-label="Search store">
                            @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                        </button>
                    @endif
                </div>
            </div>

            {{-- Mobile Horizontally Scrollable Chapter Strip --}}
            @if($navCategories->isNotEmpty())
                <div class="lg:hidden py-2 overflow-x-auto k71-no-scrollbar flex items-center gap-4 text-[11px] font-mono uppercase tracking-wider" style="border-top: 1px solid var(--t-border);">
                    <span class="font-bold shrink-0" style="color: var(--t-accent);">CHAPTERS:</span>
                    <a href="{{ $storeCollectionUrl }}" class="shrink-0 hover:opacity-75">All</a>
                    @foreach($navCategories as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="shrink-0 hover:opacity-75" style="color: var(--t-muted);">{{ $cat->name }}</a>
                    @endforeach
                </div>
            @endif
        </div>
    </header>

@elseif($headerTpl === 'heritage-framed')
    {{-- HERITAGE DOUBLE-RULE FRAMED HEADER (Monogram, Sartorial, Chronograph, Goldline, Earth & Grain, Maison Gourmet, Heritage House) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 2px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="py-3 sm:py-4 flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 sm:gap-3">
                    <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                        @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                    </button>
                    <a href="{{ $storeCollectionUrl }}" class="hidden lg:inline-flex items-center text-[11px] uppercase tracking-[0.22em] font-semibold hover:opacity-70">Catalogue</a>
                </div>

                {{-- Centered Engraved Box Framing --}}
                <a href="{{ $storeHomeUrl }}" class="flex flex-col items-center text-center px-4 sm:px-8 py-1.5" style="border-left: 1px solid var(--t-border); border-right: 1px solid var(--t-border);">
                    <span class="k71-heading text-lg sm:text-2xl lg:text-3xl tracking-wide leading-tight">{{ $storeName }}</span>
                    <span class="text-[9px] uppercase tracking-[0.28em] mt-0.5" style="color: var(--t-muted);">
                        {{ $vendor->city ? ($vendor->city . ' · ') : '' }}HERITAGE MAISON
                    </span>
                </a>

                <div class="flex items-center gap-1.5 sm:gap-2">
                    @if($showSearch)
                        <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-70" aria-label="Search store">
                            @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                        </button>
                    @endif
                    <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[40px] px-3 flex items-center gap-1.5 text-[11px] uppercase tracking-[0.18em] font-semibold" style="border: 1px solid var(--t-border);">
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
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5 sm:py-3">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 min-w-0">
                    <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                        @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                    </button>
                    <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2 min-w-0">
                        @if(!empty($vendor->logo_url))
                            <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain rounded">
                        @endif
                        <span class="k71-heading text-base sm:text-xl font-bold truncate">{{ $storeName }}</span>
                    </a>
                </div>

                @if($showSearch)
                    <form action="{{ $storeCollectionUrl }}" method="GET" class="hidden md:flex flex-1 max-w-lg mx-4">
                        @if(!empty($isCataloguePreview))
                            <input type="hidden" name="page" value="collection">
                        @endif
                        <div class="relative w-full flex">
                            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search products, materials, specs..."
                                   class="w-full min-h-[40px] pl-4 pr-4 text-xs sm:text-sm rounded-l-lg focus:outline-none"
                                   style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                            <button type="submit" class="min-h-[40px] px-4 rounded-r-lg text-xs font-semibold shrink-0 inline-flex items-center gap-1.5"
                                    style="background-color: var(--t-accent); color: var(--t-accent-contrast);" aria-label="Submit search">
                                @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-3.5 h-3.5'])
                                <span>Search</span>
                            </button>
                        </div>
                    </form>
                @endif

                <div class="flex items-center gap-2 shrink-0">
                    @if($showSearch)
                        <button type="button" data-k71-open="k71-search-modal" class="md:hidden min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Search">
                            @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                        </button>
                    @endif
                    <a href="{{ auth()->check() ? route('account') : route('login') }}" class="hidden sm:flex min-w-[40px] min-h-[40px] items-center justify-center hover:opacity-75" aria-label="Account">
                        @include('storefront.themes.partials.icon', ['name' => 'user', 'class' => 'w-4 h-4'])
                    </a>
                    <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="k71-btn k71-btn-primary !min-h-[40px] !py-1.5 !px-3">
                        @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                        <span data-k71-cart-count class="text-xs font-bold">{{ $cartCount }}</span>
                    </a>
                </div>
            </div>

            {{-- Mobile Quick Department Rail --}}
            @if($navCategories->isNotEmpty())
                <div class="mt-2 pt-2 flex items-center gap-2 overflow-x-auto k71-no-scrollbar" style="border-top: 1px solid var(--t-border);">
                    <a href="{{ $storeCollectionUrl }}" class="px-2.5 py-1 rounded text-[11px] font-bold shrink-0" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">All</a>
                    @foreach($navCategories as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}"
                           class="px-2.5 py-1 rounded text-[11px] shrink-0 font-medium hover:opacity-75"
                           style="border: 1px solid var(--t-border); background-color: var(--t-bg); color: var(--t-muted);">
                            {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </header>

@elseif($headerTpl === 'floating-pill-nav')
    {{-- FLOATING CAPSULE NAVIGATION (Soft Form, Luxe Objects, Future Form, Botanical Lab, Sweet Atelier, Neo Commerce, Pop Form) --}}
    <header class="k71-header {{ $stickyClass }} w-full px-2 sm:px-6 pt-2 sm:pt-3 pb-1">
        <div class="max-w-7xl mx-auto rounded-full px-3 sm:px-6 h-13 sm:h-16 flex items-center justify-between gap-3 shadow-md"
             style="background-color: color-mix(in srgb, var(--t-surface) 88%, transparent); -webkit-backdrop-filter: blur(16px); backdrop-filter: blur(16px); border: 1px solid var(--t-border);">
            <div class="flex items-center gap-2 min-w-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[40px] min-h-[40px] -ml-1 flex items-center justify-center rounded-full" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2 min-w-0">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain rounded-full">
                    @endif
                    <span class="k71-heading text-base sm:text-xl truncate">{{ $storeName }}</span>
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
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[40px] min-h-[40px] rounded-full flex items-center justify-center hover:opacity-70" aria-label="Search store">
                        @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[38px] px-3.5 rounded-full text-xs font-bold inline-flex items-center gap-1.5" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-3.5 h-3.5'])
                    <span data-k71-cart-count>{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@elseif($headerTpl === 'brutalist-bar')
    {{-- ARCHITECTURAL BRUTALIST BAR (Street Culture, Sneaker Vault, Mono Tech, Forma Gallery, The Drop) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 2px solid var(--t-text);">
        {{-- Raw Running Ticker Banner on Mobile & Desktop --}}
        <div class="w-full py-1 px-4 overflow-hidden text-[10px] font-mono font-black uppercase tracking-widest flex items-center justify-between"
             style="background-color: var(--t-text); color: var(--t-bg);">
            <span class="truncate">DROP ACTIVE // VERIFIED AUTHENTIC // {{ $vendor->city ?: 'GLOBAL DISPATCH' }}</span>
            <span class="hidden sm:inline font-bold">24H SHIPPING</span>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 sm:h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-2.5 min-w-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center font-mono font-black" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <a href="{{ $storeHomeUrl }}" class="k71-heading text-lg sm:text-2xl uppercase tracking-tight font-black truncate">{{ $storeName }}</a>
            </div>
            <nav class="hidden lg:flex items-center gap-1 text-xs font-bold uppercase tracking-wider" aria-label="Store navigation">
                <a href="{{ $storeCollectionUrl }}" class="px-3 py-2 hover:opacity-70">All</a>
                @foreach($navCategories->take(4) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="px-3 py-2 hover:opacity-70">{{ $cat->name }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-2 shrink-0">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[40px] min-h-[40px] flex items-center justify-center" style="border: 2px solid var(--t-text);" aria-label="Search">
                        @include('storefront.themes.partials.icon', ['name' => 'search', 'class' => 'w-4 h-4'])
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[40px] px-3.5 inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider" style="background-color: var(--t-accent); color: var(--t-accent-contrast); border: 2px solid var(--t-text);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                    <span data-k71-cart-count>{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@elseif($headerTpl === 'couture-centered' || $headerTpl === 'gallery-minimal')
    {{-- COUTURE CENTERED / GALLERY MINIMAL HEADER (Maison Élite, Atelier Blanc, Modest Luxe, Jewelle, etc.) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-15 sm:h-20 grid grid-cols-3 items-center">
            <div class="flex items-center gap-4">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    @include('storefront.themes.partials.icon', ['name' => 'menu', 'class' => 'w-5 h-5'])
                </button>
                <nav class="hidden lg:flex items-center gap-6 text-[11px] uppercase tracking-[0.2em] font-medium" aria-label="Store navigation">
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
                    <span class="k71-heading text-lg sm:text-2xl lg:text-3xl tracking-wide truncate max-w-[180px] sm:max-w-[320px]">{{ $storeName }}</span>
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
    {{-- OBSIDIAN OVERLAY / SPLIT STUDIO HEADER (Obsidian, Runway 01, Motion Lab, Coffee Culture, etc.) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-15 sm:h-16 flex items-center justify-between gap-4">
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
                <a href="{{ route('cart.index') }}" data-k71-open="k71-cart-drawer" class="min-h-[40px] px-3 inline-flex items-center gap-1.5 text-xs font-semibold rounded" style="border: 1px solid var(--t-border);">
                    @include('storefront.themes.partials.icon', ['name' => 'bag', 'class' => 'w-4 h-4'])
                    <span data-k71-cart-count style="color: var(--t-accent);">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>
@endif
