@php
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
    $navUnreadMessagesCount = 0;
    if (auth()->check()) {
        try {
            $navUnreadMessagesCount = \App\Models\Message::where('recipient_id', auth()->id())->whereNull('read_at')->count();
            if ($navUnreadMessagesCount === 0 && \Illuminate\Support\Facades\Schema::hasTable('chat_messages')) {
                $navUnreadMessagesCount = \Illuminate\Support\Facades\DB::table('chat_messages')->where('recipient_id', auth()->id())->where('is_read', 0)->count();
            }
        } catch (\Throwable $e) {
            $navUnreadMessagesCount = 0;
        }
    }
@endphp

<header x-data="{ 
            searchOpen: false, 
            userMenuOpen: false, 
            cartBadgeCount: {{ $cartCount }}
        }" 
        @cart-count-updated.window="cartBadgeCount = $event.detail.count"
        class="sticky top-0 z-50 bg-white/90 backdrop-blur-xl border-b border-slate-200/80 shadow-xs transition-all">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-18">
            
            <!-- Left: Hamburger + Logo -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Hamburger Trigger Button -->
                <button @click="$dispatch('open-hamburger')" 
                        type="button"
                        class="p-2 -ml-2 rounded-xl text-slate-800 hover:text-slate-950 hover:bg-slate-100 transition-all duration-200 focus:outline-none"
                        aria-label="Open Navigation Menu">
                    <svg width="24" height="24" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path>
                    </svg>
                </button>

                <!-- Brand Logo -->
                <div class="flex items-center gap-2">
                    @if($currentVendor)
                        <div class="flex items-center gap-2">
                            @if($currentVendor->logo_url)
                                <img src="{{ $currentVendor->logo_url }}" alt="{{ $currentVendor->shop_name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                            @endif
                            <a href="{{ route('home') }}" class="font-sans text-lg sm:text-xl font-black tracking-tight text-slate-900 leading-none">
                                {{ $currentVendor->shop_name }}
                            </a>
                        </div>
                    @else
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group" aria-label="K71 Homepage">
                            <div class="h-9 w-9 rounded-xl overflow-hidden bg-indigo-600 p-1 flex items-center justify-center shadow-md shadow-indigo-600/20 group-hover:scale-105 transition-transform shrink-0 border border-indigo-700">
                                <img src="{{ asset('images/k71-logo.png') }}" alt="K71" class="w-full h-full object-contain">
                            </div>
                            <div class="flex items-baseline gap-1.5 leading-none">
                                <span class="font-sans text-2xl font-black tracking-tight text-slate-900 group-hover:text-indigo-600 transition-colors">
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

            <!-- Desktop Middle: Clean Integrated Search Bar with Live Autocomplete -->
            <div class="hidden md:flex flex-1 max-w-md lg:max-w-xl mx-4 lg:mx-8 relative"
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
                           class="w-full pl-10 pr-20 py-2.5 rounded-full border border-slate-200/90 bg-slate-50/90 text-xs sm:text-sm text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all placeholder:text-slate-400 font-medium">
                    <div class="absolute left-3.5 top-3 text-slate-400">
                        <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path></svg>
                    </div>
                    <button type="submit" class="absolute right-1.5 top-1.5 px-3.5 py-1 rounded-full bg-slate-900 hover:bg-black text-white text-[11px] font-bold tracking-wide transition shadow-xs cursor-pointer">
                        Search
                    </button>
                </form>

                <!-- Desktop Autocomplete Results Dropdown -->
                <div x-show="open && (suggestions.length > 0 || loading)" 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="absolute top-full left-0 right-0 mt-2 bg-white/95 backdrop-blur-xl border border-slate-200/80 rounded-2xl shadow-xl overflow-hidden z-50 divide-y divide-slate-100"
                     style="display: none;">
                    
                    <div x-show="loading" class="p-3 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                        <svg class="animate-spin h-3.5 w-3.5 text-slate-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
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
                    
                    <div x-show="suggestions.length > 0" class="p-2 bg-slate-50/80 text-center border-t border-slate-100">
                        <a :href="'{{ route('catalog.index') }}?search=' + encodeURIComponent(q)" class="text-[11px] font-bold text-slate-700 hover:text-black">
                            See all results for "<span x-text="q"></span>" →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Actions: Mobile Search + Catalog + Messages + Wishlist + Cart + Account -->
            <div class="flex items-center gap-1 sm:gap-2">

                <!-- Mobile-Only Search Button -->
                <button @click="searchOpen = !searchOpen" 
                        class="md:hidden p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-all duration-200"
                        aria-label="Search">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                    </svg>
                </button>

                <!-- Stores Directory Link -->
                <a href="{{ route('stores.index') }}" class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ request()->routeIs('stores.index') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100' }} transition">
                    <span>🏪 Stores</span>
                </a>

                <!-- Catalog Link -->
                <a href="{{ route('catalog.index') }}" class="hidden lg:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold {{ request()->routeIs('catalog.*') ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'text-slate-700 hover:text-slate-950 hover:bg-slate-100' }} transition">
                    <span>Catalog</span>
                </a>

                <!-- Direct Messages Quick Access (Header) -->
                <a href="{{ route('messages.index') }}" 
                   class="p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-all duration-200 relative"
                   title="Direct Messages"
                   aria-label="Direct Messages">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.874-.91 4.54 4.54 0 00.91-2.122A7.95 7.95 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    @if($navUnreadMessagesCount > 0)
                        <span class="absolute top-1 right-1 bg-indigo-600 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center shadow-xs animate-pulse">
                            {{ $navUnreadMessagesCount > 99 ? '99+' : $navUnreadMessagesCount }}
                        </span>
                    @endif
                </a>

                <!-- Wishlist (Desktop - Triggers Mini-Wishlist Popup) -->
                <button type="button" 
                        @click="$dispatch('open-mini-wishlist')" 
                        class="hidden sm:flex p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-all duration-200 relative cursor-pointer"
                        aria-label="Wishlist">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                    </svg>
                    <span id="global-wishlist-badge" 
                          class="absolute top-1 right-1 bg-rose-500 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center {{ $navWishlistCount > 0 ? '' : 'hidden' }}">
                        {{ $navWishlistCount }}
                    </span>
                </button>

                <!-- Cart Bag Button (Triggers Mini-Cart Sheet) -->
                <button @click="$dispatch('open-mini-cart')" 
                        type="button"
                        class="p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-all duration-200 relative cursor-pointer"
                        aria-label="View Shopping Cart">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"></path>
                    </svg>
                    <!-- Counter Badge -->
                    <span x-show="cartBadgeCount > 0"
                          x-text="cartBadgeCount"
                          class="absolute top-1 right-1 bg-indigo-600 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center shadow-xs"></span>
                </button>

                <!-- Desktop User Menu -->
                <div class="hidden sm:block relative ml-1">
                    @guest
                        <button type="button" 
                                @click="window.openAuthModal ? window.openAuthModal() : $dispatch('open-auth-modal')"
                                class="px-4 py-2 rounded-full bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-all duration-200 shadow-sm shadow-indigo-600/20 cursor-pointer">
                            Sign In
                        </button>
                    @else
                        <div class="relative">
                            <button @click="userMenuOpen = !userMenuOpen" 
                                    @click.away="userMenuOpen = false" 
                                    class="w-8 h-8 rounded-full bg-indigo-600 text-white font-black flex items-center justify-center text-xs shadow-xs focus:outline-none hover:bg-indigo-700 transition-colors cursor-pointer">
                                {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
                            </button>

                            <div x-show="userMenuOpen" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl py-2 border border-slate-200 z-50 origin-top-right text-slate-900"
                                 style="display: none;">
                                <div class="px-4 py-2.5 border-b border-slate-100 bg-slate-50/50">
                                    <p class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                    <p class="text-[11px] text-slate-500 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('account') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">My Account</a>
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">My Orders</a>
                                    <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">Wishlist</a>
                                    <a href="{{ route('messages.index') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">Messages</a>
                                    <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-950">Notifications</a>
                                    @if(auth()->user()->hasRole('vendor'))
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 text-xs font-bold text-indigo-600 hover:bg-indigo-50">Vendor Dashboard</a>
                                    @endif
                                    @if(auth()->user()->hasRole('admin'))
                                        <div class="border-t border-slate-100 my-1"></div>
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-xs font-bold text-slate-900 hover:bg-slate-50">Admin Console</a>
                                    @endif
                                </div>
                                <div class="border-t border-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-xs font-bold text-red-600 hover:bg-red-50">
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

    <!-- Integrated Category Navigation Row -->
    <div class="border-t border-slate-100/80 bg-white/60 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center gap-1 sm:gap-2 py-1.5 overflow-x-auto no-scrollbar scroll-smooth" aria-label="Commerce Categories">
                @foreach($navCategories as $catKey => $cat)
                    <a href="{{ $cat['url'] }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition-all duration-200 shrink-0 {{ $cat['active'] ? 'bg-zinc-950 text-white shadow-xs' : 'text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100/80' }}">
                        <span class="text-xs">{{ $cat['icon'] }}</span>
                        <span>{{ $cat['name'] }}</span>
                        @if(!empty($cat['badge']))
                            <span class="px-1.5 py-0.5 rounded-full text-[9px] font-black tracking-wider uppercase {{ $cat['active'] ? 'bg-rose-500 text-white' : 'bg-rose-100 text-rose-700' }}">
                                {{ $cat['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>
        </div>
    </div>

    <!-- Search Overlay with Live Autocomplete -->
    <div x-show="searchOpen" 
         x-data="{
            mq: '',
            msuggestions: [],
            mloading: false,
            mfetchSuggest() {
                if(this.mq.trim().length < 2) {
                    this.msuggestions = [];
                    return;
                }
                this.mloading = true;
                fetch('/api/search/suggest?q=' + encodeURIComponent(this.mq))
                    .then(r => r.json())
                    .then(data => {
                        this.msuggestions = data.results || [];
                        this.mloading = false;
                    })
                    .catch(() => { this.mloading = false; });
            }
         }"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.away="searchOpen = false"
         class="bg-white/98 backdrop-blur-xl border-t border-slate-200 p-4 shadow-xl"
         style="display: none;">
        <div class="max-w-3xl mx-auto relative">
            <form action="{{ route('catalog.index') }}" method="GET">
                <input type="text" name="search" x-model="mq"
                       @input.debounce.250ms="mfetchSuggest()"
                       placeholder="Search products, brands, services, machinery, cars, thrift..." 
                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900 focus:bg-white focus:border-slate-900 focus:ring-2 focus:ring-slate-900/10 outline-none transition-all placeholder:text-slate-400 font-medium"
                       x-ref="navSearchInput"
                       x-init="$watch('searchOpen', val => { if(val) setTimeout(() => $refs.navSearchInput.focus(), 100) })">
                <div class="absolute left-3.5 top-3.5 text-slate-400">
                    <svg width="20" height="20" class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                    </svg>
                </div>
            </form>

            <!-- Mobile Suggestions List -->
            <div x-show="msuggestions.length > 0 || mloading" class="mt-3 bg-white border border-slate-200 rounded-xl divide-y divide-slate-100 max-h-72 overflow-y-auto">
                <div x-show="mloading" class="p-3 text-center text-xs text-slate-400 flex items-center justify-center gap-2">
                    <svg class="animate-spin h-3.5 w-3.5 text-slate-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Searching...</span>
                </div>

                <template x-for="item in msuggestions" :key="item.id">
                    <a :href="item.url" class="flex items-center gap-3 p-2.5 hover:bg-slate-50 transition">
                        <img :src="item.thumb" :alt="item.title" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold text-slate-900 truncate" x-text="item.title"></p>
                            <div class="flex items-center gap-2 text-[10px] text-slate-500 mt-0.5">
                                <span class="font-mono font-bold text-slate-900" x-text="item.price"></span>
                                <span class="uppercase px-1 py-0.2 rounded bg-slate-100 text-slate-600 font-bold" x-text="item.vertical"></span>
                            </div>
                        </div>
                    </a>
                </template>

                <div x-show="msuggestions.length > 0" class="p-2 bg-slate-50 text-center">
                    <a :href="'{{ route('catalog.index') }}?search=' + encodeURIComponent(mq)" class="text-xs font-bold text-slate-900">
                        View all results for "<span x-text="mq"></span>" →
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
