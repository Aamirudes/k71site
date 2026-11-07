@props([
    'hideSearch' => false,
    'hideCategories' => false,
])

@php
    $isMessages = request()->routeIs('messages.*') || request()->is('messages*');
    $hideSearch = $hideSearch || $isMessages;
    $hideCategories = $hideCategories || $isMessages;

    $currentVendor = app()->bound('current_vendor') ? app('current_vendor') : null;
    $activeVertical = $k71_vertical ?? config('k71.verticals.main');
    $currentKey = $k71_vertical_key ?? 'main';

    $navCategories = [
        'all' => [
            'name' => 'All',
            'url' => route('catalog.index'),
            'icon' => '✦',
            'active' => request()->routeIs('catalog.*') && !request()->filled('vertical'),
        ],
        'main' => [
            'name' => 'K71Mart',
            'url' => k71_vertical_url('main'),
            'icon' => '🛍️',
            'active' => request()->routeIs('home') || ($currentKey === 'main' && !request()->routeIs('catalog.*')),
        ],
        'food' => [
            'name' => 'Foods',
            'url' => k71_vertical_url('food'),
            'icon' => '🍽️',
            'active' => $currentKey === 'food' || request()->routeIs('food.*'),
        ],
        'services' => [
            'name' => 'Services',
            'url' => k71_vertical_url('services'),
            'icon' => '⚡',
            'active' => $currentKey === 'services' || request()->routeIs('services.*'),
        ],
        'used' => [
            'name' => 'Used',
            'url' => k71_vertical_url('used'),
            'icon' => '♻️',
            'active' => $currentKey === 'used' || request()->routeIs('used.*'),
        ],
        'enquiry' => [
            'name' => 'Enquiry',
            'url' => k71_vertical_url('enquiry'),
            'icon' => '📋',
            'active' => $currentKey === 'enquiry' || request()->routeIs('enquiry.*') || request()->routeIs('b2b.*'),
        ],
        'shorts' => [
            'name' => 'Shorts',
            'url' => k71_vertical_url('shorts'),
            'icon' => '▶',
            'badge' => 'LIVE',
            'active' => $currentKey === 'shorts' || request()->routeIs('shorts.*'),
        ],
    ];

    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
    $navWishlistCount = count(\App\Models\Wishlist::getUserWishlistIds());
@endphp

<!-- MAIN K71 MARKETPLACE HEADER (1-FORM WITH DOWN-ARROW ACCORDION SLIDE-IN FOR SEARCH & CATEGORIES) -->
<header x-data="{ 
            userMenuOpen: false, 
            cartBadgeCount: {{ $cartCount }},
            expandedForms: false
        }" 
        @cart-count-updated.window="cartBadgeCount = $event.detail.count"
        @click.outside="expandedForms = false"
        @keydown.escape.window="expandedForms = false;"
        class="sticky top-0 z-50 bg-white/95 backdrop-blur-xl border-b border-slate-200/80 shadow-xs transition-all relative">
    
    @if($currentVendor)
        <!-- Standalone Vendor Store Header: Small link Back to Marketplace -->
        <div class="bg-black text-white text-[11px] font-medium py-1.5 px-3 sm:px-6 lg:px-8 border-b border-zinc-900">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-zinc-300 hover:text-white transition-colors group">
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span class="font-semibold">← Back to Marketplace</span>
                </a>
                <div class="flex items-center gap-3 text-zinc-400 text-[10px]">
                    <span class="inline-flex items-center gap-1 text-emerald-400 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Official Brand Store
                    </span>
                    <span class="hidden sm:inline text-zinc-700">•</span>
                    <span class="hidden sm:inline text-zinc-400">Direct Store Dispatch</span>
                </div>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
        <!-- 1ST FORM: MAIN COMPACT SINGLE-LINE NAVBAR BAR -->
        <div class="flex items-center justify-between h-14 sm:h-16 gap-3">
            
            <!-- Left: Hamburger + Logo -->
            <div class="flex items-center gap-2 sm:gap-3.5 shrink-0">
                <!-- Hamburger Trigger Button -->
                <button @click="$dispatch('open-hamburger')" 
                        type="button"
                        class="p-2 -ml-1 rounded-xl text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-colors focus:outline-none cursor-pointer"
                        aria-label="Open Navigation Menu">
                    <svg width="22" height="22" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path>
                    </svg>
                </button>

                <!-- Brand Logo -->
                <div class="flex items-center gap-2">
                    @if($currentVendor)
                        <a href="{{ $currentVendor->storefront_url ?? route('storefront.show', $currentVendor->slug) }}" class="flex items-center gap-2.5 group" aria-label="{{ $currentVendor->shop_name }}">
                            @if($currentVendor->logo_url)
                                <img src="{{ $currentVendor->logo_url }}" alt="{{ $currentVendor->shop_name }}" class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl object-cover border border-slate-200 shadow-xs group-hover:scale-105 transition-transform">
                            @else
                                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-black text-white font-serif font-black flex items-center justify-center text-sm shadow-xs group-hover:scale-105 transition-transform">
                                    {{ strtoupper(substr($currentVendor->shop_name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="flex flex-col leading-tight">
                                <span class="font-sans text-base sm:text-lg font-black tracking-tight text-slate-900 group-hover:text-black leading-none truncate max-w-[140px] sm:max-w-xs">
                                    {{ $currentVendor->shop_name }}
                                </span>
                                <span class="text-[9px] font-bold uppercase tracking-wider text-emerald-600 mt-0.5">Verified Boutique</span>
                            </div>
                        </a>
                    @else
                        <a href="{{ route('home') }}" class="flex items-center gap-2 group" aria-label="K71 Homepage">
                            <div class="h-8 w-8 sm:h-9 sm:w-9 flex items-center justify-center group-hover:scale-105 transition-transform shrink-0">
                                <img src="{{ asset('images/k71-logo.png') }}" alt="K71" class="w-full h-full object-contain">
                            </div>
                            <div class="flex items-baseline gap-1 leading-none">
                                <span class="font-sans text-xl sm:text-2xl font-black tracking-tight text-slate-950 group-hover:text-black transition-colors">
                                    K71
                                </span>
                                <span class="text-[9px] font-black uppercase tracking-wider text-slate-400 font-mono hidden sm:inline">
                                    MARKETPLACE
                                </span>
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right Actions: Stores + Catalog + Messages + Wishlist + Cart + Account -->
            <div class="flex items-center gap-1 sm:gap-1.5 shrink-0">

                <!-- Stores Directory Link (Desktop) -->
                <a href="{{ route('stores.index') }}" class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ request()->routeIs('stores.index') ? 'bg-slate-100 text-slate-950 font-black' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100' }} transition">
                    <span>🏪 Stores</span>
                </a>

                <!-- Catalog Link (Desktop) -->
                <a href="{{ route('catalog.index') }}" class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ request()->routeIs('catalog.*') ? 'bg-slate-100 text-slate-950 font-black' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100' }} transition">
                    <span>Catalog</span>
                </a>

                <!-- Direct Messages Quick Access -->
                <a href="{{ route('messages.index') }}" 
                   id="nav-messages-link"
                   class="p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition relative cursor-pointer"
                   title="Direct Messages"
                   aria-label="Direct Messages">
                    <svg width="22" height="22" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.874-.91 4.54 4.54 0 00.91-2.122A7.95 7.95 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                </a>

                <!-- Wishlist (Visible on mobile and desktop) -->
                <button type="button" 
                        @click="$dispatch('open-mini-wishlist')" 
                        class="p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition relative cursor-pointer"
                        aria-label="Wishlist">
                    <svg width="22" height="22" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                    </svg>
                    <span id="global-wishlist-badge" 
                          class="absolute top-1 right-1 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center {{ $navWishlistCount > 0 ? '' : 'hidden' }}">
                        {{ $navWishlistCount }}
                    </span>
                </button>

                <!-- Cart Bag Button -->
                <button @click="$dispatch('open-mini-cart')" 
                        type="button"
                        class="p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition relative cursor-pointer"
                        aria-label="View Shopping Cart">
                    <svg width="22" height="22" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"></path>
                    </svg>
                    <!-- Counter Badge -->
                    <span x-show="cartBadgeCount > 0"
                          x-text="cartBadgeCount"
                          class="absolute top-1 right-1 bg-slate-900 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center shadow-xs"></span>
                </button>

                <!-- User Account Profile Button & Dropdown -->
                <div class="relative" x-data="{ open: false }">
                    @guest
                        <button @click="$dispatch('open-auth-modal', { view: 'login' })"
                                type="button"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-900 hover:text-black bg-slate-100 hover:bg-slate-200 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                            <span class="hidden sm:inline">Sign In</span>
                        </button>
                    @else
                        <button @click="open = !open" 
                                type="button" 
                                class="flex items-center gap-1.5 p-1 rounded-full hover:bg-slate-100 transition focus:outline-none cursor-pointer"
                                aria-label="User Account Menu">
                            <div class="w-8 h-8 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
                            </div>
                        </button>

                        <div x-show="open" 
                             @click.away="open = false" 
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="transform opacity-0 scale-95"
                             x-transition:enter-end="transform opacity-100 scale-100"
                             class="absolute right-0 mt-2 w-56 rounded-2xl bg-white shadow-xl border border-slate-100 py-2 z-50 divide-y divide-slate-100"
                             style="display: none;">
                            <div class="px-4 py-2">
                                <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('account') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">My Account</a>
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">My Orders</a>
                                <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">Wishlist</a>
                                <a href="{{ route('messages.index') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">Messages</a>
                                @if(auth()->user()->hasRole('vendor'))
                                    <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 text-xs font-bold text-slate-900 hover:bg-slate-50">Vendor Dashboard</a>
                                @endif
                                @if(auth()->user()->hasRole('admin'))
                                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-xs font-bold text-slate-900 hover:bg-slate-50">Admin Console</a>
                                @endif
                            </div>
                            <div class="py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50 cursor-pointer">
                                        Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>

            </div>

        </div>
    </div>

    <!-- SLIDE-IN ACCORDION: FORM 2 (SEARCH) & FORM 3 (CATEGORIES) -->
    <div x-show="expandedForms" 
         x-cloak
         x-transition:enter="transition-all duration-300 ease-out"
         x-transition:enter-start="opacity-0 -translate-y-3 max-h-0"
         x-transition:enter-end="opacity-100 translate-y-0 max-h-[600px]"
         x-transition:leave="transition-all duration-200 ease-in"
         x-transition:leave-start="opacity-100 translate-y-0 max-h-[600px]"
         x-transition:leave-end="opacity-0 -translate-y-3 max-h-0"
         class="overflow-hidden border-t border-slate-200/90 bg-white/98 backdrop-blur-2xl shadow-xl">
        
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3.5 space-y-3">
            
            <div class="flex items-center justify-between pb-1">
                <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Marketplace Search &amp; Verticals</span>
                <button type="button" 
                        @click="expandedForms = false" 
                        class="text-[11px] font-bold text-slate-500 hover:text-slate-900 flex items-center gap-1 cursor-pointer transition">
                    <span>Close</span>
                    <span>✕</span>
                </button>
            </div>

            <!-- FORM 2: FULL SEARCH BAR WITH AUTOCOMPLETE -->
            <div class="relative w-full"
                 x-data="{
                    q: '',
                    suggestions: [],
                    loading: false,
                    open: false,
                    fetchSuggest() {
                        if(this.q.trim().length < 2) {
                            this.suggestions = [];
                            this.open = false;
                            return;
                        }
                        this.loading = true;
                        fetch('/api/search/suggest?q=' + encodeURIComponent(this.q))
                            .then(r => r.json())
                            .then(data => {
                                this.suggestions = data.results || [];
                                this.open = true;
                                this.loading = false;
                            })
                            .catch(() => { this.loading = false; });
                    }
                 }"
                 @click.away="open = false">
                <form action="{{ route('catalog.index') }}" method="GET" class="w-full relative">
                    <input type="text" name="search" x-model="q"
                           @input.debounce.250ms="fetchSuggest()"
                           @focus="if(suggestions.length > 0) open = true"
                           autocomplete="off"
                           placeholder="Search products, verified stores, brands..." 
                           class="w-full pl-10 pr-24 py-2.5 rounded-full border border-slate-200 bg-slate-50 text-xs sm:text-sm text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all placeholder:text-slate-400 font-medium">
                    <div class="absolute left-3.5 top-3 text-slate-400">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path></svg>
                    </div>
                    <button type="submit" class="absolute right-1.5 top-1.5 px-4 py-1.5 rounded-full bg-slate-900 hover:bg-black text-white text-xs font-bold tracking-wide transition shadow-xs cursor-pointer">
                        Search
                    </button>
                </form>

                <!-- Autocomplete Dropdown -->
                <div x-show="open && (suggestions.length > 0 || loading)" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/80 rounded-2xl shadow-xl overflow-hidden z-50 divide-y divide-slate-100"
                     style="display: none;">
                    
                    <div x-show="loading" class="p-3 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                        <span>Searching marketplace...</span>
                    </div>

                    <template x-for="item in suggestions" :key="item.id">
                        <a :href="item.url" class="flex items-center gap-3 p-2.5 hover:bg-slate-50 transition group">
                            <img :src="item.thumb" :alt="item.title" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-900 truncate group-hover:text-black" x-text="item.title"></p>
                                <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                    <span class="font-mono font-bold text-slate-900" x-text="item.price"></span>
                                    <span class="uppercase px-1.5 py-0.2 rounded bg-slate-100 text-slate-600 font-bold" x-text="item.vertical"></span>
                                    <span class="truncate text-slate-400" x-text="item.vendor"></span>
                                </div>
                            </div>
                        </a>
                    </template>
                </div>
            </div>

            <!-- FORM 3: CATEGORY NAVIGATION ROW -->
            @if(!$hideCategories)
            <div class="pt-2 border-t border-slate-100">
                <nav class="flex items-center gap-1.5 sm:gap-2 py-1 overflow-x-auto no-scrollbar scroll-smooth overscroll-x-contain touch-pan-x" 
                     aria-label="Commerce Categories">
                    @foreach($navCategories as $catKey => $cat)
                        <a href="{{ $cat['url'] }}"
                           @click="expandedForms = false"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-150 shrink-0 select-none {{ $cat['active'] ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100 active:scale-95' }}">
                            <span class="text-xs leading-none">{{ $cat['icon'] }}</span>
                            <span>{{ $cat['name'] }}</span>
                            @if(!empty($cat['badge']))
                                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black tracking-wider uppercase {{ $cat['active'] ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-700' }}">
                                    {{ $cat['badge'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
            @endif

        </div>
    </div>

    <!-- Navigation Arrow Pull-Handle Positioned Fully Left Side (0 Margin, 0 Padding, 0 Border-Radius) -->
    <div class="absolute -bottom-3.5 left-0 m-0 p-0 z-30 pointer-events-none">
        <button type="button" 
                @click="expandedForms = !expandedForms"
                class="pointer-events-auto h-7 w-7 m-0 p-0 rounded-none bg-white dark:bg-zinc-900 border-r border-b border-t border-slate-300 dark:border-zinc-700 shadow-xs hover:bg-slate-100 dark:hover:bg-zinc-800 transition-all duration-200 flex items-center justify-center cursor-pointer group"
                aria-label="Toggle Navigation & Search Drawer"
                :title="expandedForms ? 'Close Search & Categories' : 'Explore Search & Categories'">
            <svg class="w-3.5 h-3.5 text-slate-700 group-hover:text-black dark:text-zinc-300 dark:group-hover:text-white transition-transform duration-300 transform"
                 :class="{ 'rotate-180 text-black dark:text-white': expandedForms }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>

</header>
