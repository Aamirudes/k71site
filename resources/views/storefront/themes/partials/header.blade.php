@php
    $headerVariant = $manifest['layout']['header_variant'] ?? 'centered-wordmark';
    $headerCfg = $themeConfig['header'] ?? [];
    $isSticky = array_key_exists('sticky', $headerCfg) ? (bool) $headerCfg['sticky'] : true;
    $showSearch = array_key_exists('show_search', $headerCfg) ? (bool) $headerCfg['show_search'] : true;
    $logoSizeClass = match($headerCfg['logo_size'] ?? 'md') {
        'sm' => 'h-7',
        'lg' => 'h-11',
        default => 'h-9',
    };
    $useHeaderGlass = !empty($manifest['glass']['header_glass']) && ($glassStrength ?? 'normal') !== 'off';
    $stickyClass = $isSticky ? 'sticky top-0 z-40' : 'relative z-40';
    $headerSurface = $useHeaderGlass ? 'k71-glass' : '';
    $navCategories = ($vendorCategories ?? collect())->take(5);
@endphp

@if(!empty($headerCfg['announcement_enabled']) && !empty($headerCfg['announcement_text']))
    <div class="w-full px-4 py-2 text-center text-[11px] sm:text-xs font-medium tracking-wide"
         style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
        @if(!empty($headerCfg['announcement_link']))
            <a href="{{ $headerCfg['announcement_link'] }}" class="underline-offset-4 hover:underline">
                {{ $headerCfg['announcement_text'] }}
            </a>
        @else
            <span>{{ $headerCfg['announcement_text'] }}</span>
        @endif
    </div>
@endif

@if($headerVariant === 'masthead-editorial')
    {{-- MASTHEAD EDITORIAL HEADER (Editorial Journal) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-bg); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between py-2 text-[11px]" style="border-bottom: 1px solid var(--t-border); color: var(--t-muted);">
                <span class="k71-eyebrow">{{ $vendor->city ?: 'K71 Verified Store' }}</span>
                <span>{{ now()->format('F Y') }}</span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('cart.index') }}" class="hover:opacity-80 font-semibold" style="color: var(--t-text);">
                        Bag (<span data-k71-cart-count>{{ $cartCount }}</span>)
                    </a>
                </div>
            </div>
            <div class="py-4 sm:py-5 flex items-center justify-between gap-4 k71-masthead-rule">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] flex items-center justify-center" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-3 mx-auto lg:mx-0">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain">
                    @endif
                    <span class="k71-heading text-2xl sm:text-4xl tracking-tight">{{ $storeName }}</span>
                </a>
                <nav class="hidden lg:flex items-center gap-6 text-xs font-medium uppercase tracking-widest" aria-label="Store navigation">
                    <a href="{{ $storeHomeUrl }}" class="hover:opacity-70">Home</a>
                    <a href="{{ $storeCollectionUrl }}" class="hover:opacity-70">Catalogue</a>
                    @foreach($navCategories->take(3) as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-70">{{ $cat->name }}</a>
                    @endforeach
                </nav>
                <div class="flex items-center gap-2">
                    @if($showSearch)
                        <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-70" aria-label="Search store">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </header>

@elseif($headerVariant === 'floating-capsule' || $headerVariant === 'signature-glass')
    {{-- FLOATING GLASS CAPSULE / SIGNATURE 71 HEADER --}}
    <header class="k71-header {{ $stickyClass }} w-full px-3 sm:px-6 pt-3 pb-2">
        <div class="max-w-7xl mx-auto rounded-full px-4 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-4 {{ $useHeaderGlass ? 'k71-glass' : 'k71-surface' }}">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center rounded-full" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2.5 min-w-0">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain rounded-full">
                    @endif
                    <span class="k71-heading text-lg sm:text-xl truncate">{{ $storeName }}</span>
                </a>
            </div>
            <nav class="hidden lg:flex items-center gap-6 text-xs font-medium" aria-label="Store navigation">
                <a href="{{ $storeHomeUrl }}" class="hover:opacity-75 transition">Home</a>
                <a href="{{ $storeCollectionUrl }}" class="hover:opacity-75 transition">Shop All</a>
                @foreach($navCategories->take(4) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-75 transition">{{ $cat->name }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-1.5 sm:gap-2 shrink-0">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] rounded-full flex items-center justify-center hover:opacity-75" aria-label="Search store">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" class="min-h-[40px] px-3.5 rounded-full text-xs font-semibold flex items-center gap-1.5" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">
                    <span>Bag</span>
                    <span data-k71-cart-count class="text-[11px] font-bold">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@elseif($headerVariant === 'utility-search')
    {{-- UTILITY SEARCH-FIRST HEADER (Tech Precision, Digital Supply, Industrial Supply, Marketplace Pro, Conversion Atelier) --}}
    <header class="k71-header {{ $stickyClass }} w-full" style="background-color: var(--t-surface); border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 flex flex-wrap lg:flex-nowrap items-center justify-between gap-3">
            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
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
                        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search {{ $storeName }} products..."
                               class="w-full min-h-[44px] pl-4 pr-12 text-sm rounded-l-lg focus:outline-none"
                               style="background-color: var(--t-bg); color: var(--t-text); border: 1px solid var(--t-border);">
                        <button type="submit" class="min-h-[44px] px-4 rounded-r-lg text-xs font-semibold shrink-0"
                                style="background-color: var(--t-accent); color: var(--t-accent-contrast);" aria-label="Submit search">
                            Search
                        </button>
                    </div>
                </form>
            @endif
            <div class="order-2 lg:order-3 flex items-center gap-2 shrink-0">
                <a href="{{ $storeCollectionUrl }}" class="hidden sm:inline-flex items-center px-3 min-h-[44px] text-xs font-semibold hover:opacity-75">Catalogue</a>
                <a href="{{ auth()->check() ? route('account') : route('login') }}" class="hidden md:inline-flex items-center px-3 min-h-[44px] text-xs font-medium hover:opacity-75" style="color: var(--t-muted);">Account</a>
                <a href="{{ route('cart.index') }}" class="k71-btn k71-btn-primary !py-2 !px-4">
                    <span>Cart</span>
                    <span data-k71-cart-count class="px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-black/20">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@elseif($headerVariant === 'centered-wordmark')
    {{-- CENTERED WORDMARK LUXURY HEADER --}}
    <header class="k71-header {{ $stickyClass }} w-full {{ $headerSurface }}" style="{{ !$useHeaderGlass ? 'background-color: var(--t-bg);' : '' }} border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 grid grid-cols-3 items-center">
            <div class="flex items-center gap-4">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <nav class="hidden lg:flex items-center gap-6 text-xs uppercase tracking-widest font-medium" aria-label="Store navigation">
                    <a href="{{ $storeCollectionUrl }}" class="hover:opacity-70">Shop</a>
                    @foreach($navCategories->take(2) as $cat)
                        <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-70 truncate max-w-[110px]">{{ $cat->name }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="flex justify-center">
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2.5 text-center">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain">
                    @endif
                    <span class="k71-heading text-xl sm:text-2xl tracking-wide truncate max-w-[180px] sm:max-w-[260px]">{{ $storeName }}</span>
                </a>
            </div>
            <div class="flex items-center justify-end gap-2 sm:gap-4">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-70" aria-label="Search store">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" class="min-h-[44px] px-2 flex items-center gap-1.5 text-xs uppercase tracking-widest font-semibold hover:opacity-75">
                    <span>Bag</span>
                    <span data-k71-cart-count class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-[10px]" style="background-color: var(--t-accent); color: var(--t-accent-contrast);">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>

@else
    {{-- SPLIT-NAV / OVERLAY-GLASS / COMPACT-MINIMAL HEADER --}}
    <header class="k71-header {{ $stickyClass }} w-full {{ $headerSurface }}" style="{{ !$useHeaderGlass ? 'background-color: var(--t-bg);' : '' }} border-bottom: 1px solid var(--t-border);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" data-k71-open="k71-mobile-drawer" class="lg:hidden min-w-[44px] min-h-[44px] -ml-2 flex items-center justify-center" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ $storeHomeUrl }}" class="flex items-center gap-2.5 min-w-0">
                    @if(!empty($vendor->logo_url))
                        <img src="{{ $vendor->logo_url }}" alt="{{ $storeName }}" class="{{ $logoSizeClass }} w-auto object-contain rounded">
                    @endif
                    <span class="k71-heading text-lg sm:text-xl truncate">{{ $storeName }}</span>
                </a>
            </div>
            <nav class="hidden lg:flex items-center gap-6 text-xs font-semibold tracking-wide" aria-label="Store navigation">
                <a href="{{ $storeHomeUrl }}" class="hover:opacity-75">Home</a>
                <a href="{{ $storeCollectionUrl }}" class="hover:opacity-75">All Products</a>
                @foreach($navCategories->take(4) as $cat)
                    <a href="{{ $storeCollectionUrl }}{{ str_contains($storeCollectionUrl, '?') ? '&' : '?' }}category={{ urlencode($cat->slug) }}" class="hover:opacity-75">{{ $cat->name }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-2 shrink-0">
                @if($showSearch)
                    <button type="button" data-k71-open="k71-search-modal" class="min-w-[44px] min-h-[44px] flex items-center justify-center hover:opacity-75" aria-label="Search store">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                @endif
                <a href="{{ route('cart.index') }}" class="min-h-[44px] px-3 flex items-center gap-1.5 text-xs font-semibold rounded-lg" style="border: 1px solid var(--t-border);">
                    <span>Bag</span>
                    <span data-k71-cart-count class="font-bold" style="color: var(--t-accent);">{{ $cartCount }}</span>
                </a>
            </div>
        </div>
    </header>
@endif
