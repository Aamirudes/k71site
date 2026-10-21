@php
    $currentVendor = app()->bound('current_vendor') ? app('current_vendor') : null;
    $activeVertical = $k71_vertical ?? config('k71.verticals.main');
    $currentKey = $k71_vertical_key ?? 'main';

    $allVerticals = [
        'main' => [
            'name' => 'Marketplace',
            'url' => k71_vertical_url('main'),
        ],
        'food' => [
            'name' => 'Food',
            'url' => k71_vertical_url('food'),
        ],
        'services' => [
            'name' => 'Services',
            'url' => k71_vertical_url('services'),
        ],
        'used' => [
            'name' => 'Pre-Loved',
            'url' => k71_vertical_url('used'),
        ],
        'enquiry' => [
            'name' => 'Quotes & B2B',
            'url' => k71_vertical_url('enquiry'),
        ],
        'shorts' => [
            'name' => 'Shorts',
            'url' => k71_vertical_url('shorts'),
            'badge' => 'LIVE',
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
            cartBadgeCount: {{ $cartCount }},
            isDark: document.documentElement.classList.contains('dark'),
            toggleTheme() {
                this.isDark = !this.isDark;
                if (this.isDark) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { dark: this.isDark } }));
            }
        }" 
        @cart-count-updated.window="cartBadgeCount = $event.detail.count"
        class="sticky top-0 z-50 bg-white/80 dark:bg-zinc-950/85 backdrop-blur-xl border-b border-zinc-200/60 dark:border-zinc-800/80 shadow-[0_1px_3px_rgba(0,0,0,0.02)] transition-colors duration-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-18">
            
            <!-- Left: Hamburger + Logo -->
            <div class="flex items-center gap-3 sm:gap-4">
                <!-- Hamburger Trigger Button -->
                <button @click="$dispatch('open-hamburger')" 
                        type="button"
                        class="p-2 -ml-2 rounded-xl text-zinc-700 dark:text-zinc-200 hover:text-black dark:hover:text-white hover:bg-zinc-100/70 dark:hover:bg-zinc-800/70 transition-all duration-200 focus:outline-none"
                        aria-label="Open Navigation Menu">
                    <svg width="24" height="24" class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"></path>
                    </svg>
                </button>

                <!-- Brand Logo -->
                <div class="flex items-center gap-2">
                    @if($currentVendor)
                        <div class="flex items-center gap-2">
                            @if($currentVendor->logo_url)
                                <img src="{{ $currentVendor->logo_url }}" alt="{{ $currentVendor->shop_name }}" class="w-8 h-8 rounded-lg object-cover border border-zinc-200 dark:border-zinc-700">
                            @endif
                            <a href="{{ route('home') }}" class="font-sans text-lg sm:text-xl font-bold tracking-tight text-zinc-900 dark:text-white leading-none">
                                {{ $currentVendor->shop_name }}
                            </a>
                        </div>
                    @else
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group" aria-label="K71 Homepage">
                            <div class="h-9 w-9 rounded-xl overflow-hidden bg-[#0600c2] p-1 flex items-center justify-center shadow-md shadow-blue-900/20 group-hover:scale-105 transition-transform shrink-0 border border-blue-900/30">
                                <img src="{{ asset('images/k71-logo.png') }}" alt="K71" class="w-full h-full object-contain">
                            </div>
                            <div class="flex flex-col leading-none">
                                <span class="font-sans text-xl sm:text-2xl font-black tracking-tight text-zinc-950 dark:text-white flex items-center gap-1.5">
                                    K71<span class="text-[9px] font-mono font-bold tracking-widest text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/60 px-1.5 py-0.5 rounded border border-blue-200/50 dark:border-blue-800/50">AGENCY</span>
                                </span>
                            </div>
                            @if($currentKey !== 'main')
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/50 text-blue-700 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/60">
                                    {{ $activeVertical['name'] ?? 'Hub' }}
                                </span>
                            @endif
                        </a>
                    @endif
                </div>
            </div>

            <!-- Desktop Middle: Minimalist Verticals Navigation Bar -->
            <nav class="hidden lg:flex items-center gap-1 bg-zinc-100/80 dark:bg-zinc-900/80 p-1 rounded-full border border-zinc-200/60 dark:border-zinc-800/80 backdrop-blur-md" aria-label="Category Verticals">
                @foreach($allVerticals as $vKey => $vMeta)
                    @php $isCurrent = $currentKey === $vKey; @endphp
                    <a href="{{ $vMeta['url'] }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs transition-all duration-200 flex items-center gap-1.5 {{ $isCurrent ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-xs font-semibold' : 'text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white hover:bg-white/60 dark:hover:bg-zinc-800/50 font-medium' }}">
                        <span>{{ $vMeta['name'] }}</span>
                        @if(isset($vMeta['badge']))
                            <span class="text-[8px] font-bold px-1.5 py-0.5 rounded-full bg-red-500 text-white tracking-wider animate-pulse">
                                {{ $vMeta['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <!-- Right Actions: Theme Toggle + Search + Wishlist + Cart + Account -->
            <div class="flex items-center gap-1 sm:gap-2">
                
                <!-- Theme Mode Toggle (Sun/Moon) -->
                <button @click="toggleTheme()" 
                        type="button"
                        class="p-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-zinc-100/70 dark:hover:bg-zinc-800/70 transition-all duration-200 focus:outline-none"
                        :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
                        aria-label="Toggle Theme Mode">
                    <!-- Sun (Visible in dark mode) -->
                    <svg x-show="isDark" class="w-5 h-5 text-amber-400 transition-transform duration-300 hover:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                    </svg>
                    <!-- Moon (Visible in light mode) -->
                    <svg x-show="!isDark" class="w-5 h-5 text-zinc-700 dark:text-zinc-300 transition-transform duration-300 hover:-rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                    </svg>
                </button>

                <!-- Search Button -->
                <button @click="searchOpen = !searchOpen" 
                        class="p-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-zinc-100/70 dark:hover:bg-zinc-800/70 transition-all duration-200"
                        aria-label="Search">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                    </svg>
                </button>

                <!-- Direct Messages Quick Access (Header) -->
                <a href="{{ route('messages.index') }}" 
                   class="p-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-zinc-100/70 dark:hover:bg-zinc-800/70 transition-all duration-200 relative"
                   title="Direct Messages"
                   aria-label="Direct Messages">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a.75.75 0 01-.874-.91 4.54 4.54 0 00.91-2.122A7.95 7.95 0 013 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
                    </svg>
                    @if($navUnreadMessagesCount > 0)
                        <span class="absolute top-1 right-1 bg-violet-600 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center shadow-xs animate-pulse">
                            {{ $navUnreadMessagesCount > 99 ? '99+' : $navUnreadMessagesCount }}
                        </span>
                    @endif
                </a>

                <!-- Wishlist (Desktop) -->
                <a href="{{ route('wishlist.index') }}" 
                   class="hidden sm:flex p-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-zinc-100/70 dark:hover:bg-zinc-800/70 transition-all duration-200 relative"
                   aria-label="Wishlist">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                    </svg>
                    <span id="global-wishlist-badge" 
                          class="absolute top-1 right-1 bg-[#0600c2] text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center {{ $navWishlistCount > 0 ? '' : 'hidden' }}">
                        {{ $navWishlistCount }}
                    </span>
                </a>

                <!-- Cart Bag Button (Triggers Mini-Cart Sheet) -->
                <button @click="$dispatch('open-mini-cart')" 
                        type="button"
                        class="p-2 rounded-xl text-zinc-700 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-zinc-100/70 dark:hover:bg-zinc-800/70 transition-all duration-200 relative"
                        aria-label="View Shopping Cart">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"></path>
                    </svg>
                    <!-- Counter Badge -->
                    <span x-show="cartBadgeCount > 0"
                          x-text="cartBadgeCount"
                          class="absolute top-1 right-1 bg-[#0600c2] text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center shadow-xs"></span>
                </button>

                <!-- Desktop User Menu -->
                <div class="hidden sm:block relative ml-1">
                    @guest
                        <a href="{{ route('login') }}" 
                           class="px-4 py-2 rounded-full bg-[#0600c2] text-white text-xs font-semibold hover:bg-blue-700 transition-all duration-200 shadow-sm shadow-blue-900/20">
                            Sign In
                        </a>
                    @else
                        <div class="relative">
                            <button @click="userMenuOpen = !userMenuOpen" 
                                    @click.away="userMenuOpen = false" 
                                    class="w-8 h-8 rounded-full bg-[#0600c2] text-white font-medium flex items-center justify-center text-xs shadow-xs focus:outline-none hover:bg-blue-700 transition-colors">
                                {{ strtoupper(substr(auth()->user()->first_name ?? 'U', 0, 1)) }}
                            </button>

                            <div x-show="userMenuOpen" 
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-56 bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl rounded-2xl shadow-xl py-2 border border-zinc-200/70 dark:border-zinc-800/80 z-50 origin-top-right text-zinc-900 dark:text-zinc-100"
                                 style="display: none;">
                                <div class="px-4 py-2.5 border-b border-zinc-100 dark:border-zinc-800">
                                    <p class="text-xs font-semibold text-zinc-900 dark:text-white truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                    <p class="text-[11px] text-zinc-400 truncate">{{ auth()->user()->email }}</p>
                                </div>
                                <div class="py-1">
                                    <a href="{{ route('account') }}" class="block px-4 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white">My Account</a>
                                    <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white">My Orders</a>
                                    <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white">Wishlist</a>
                                    <a href="{{ route('messages.index') }}" class="block px-4 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white">Messages</a>
                                    <a href="{{ route('notifications.index') }}" class="block px-4 py-2 text-xs text-zinc-700 dark:text-zinc-300 hover:bg-zinc-50 dark:hover:bg-zinc-800 hover:text-black dark:hover:text-white">Notifications</a>
                                    @if(auth()->user()->hasRole('vendor'))
                                        <div class="border-t border-zinc-100 dark:border-zinc-800 my-1"></div>
                                        <a href="{{ route('vendor.dashboard') }}" class="block px-4 py-2 text-xs font-semibold text-violet-600 dark:text-violet-400 hover:bg-violet-50/50 dark:hover:bg-violet-900/20">Vendor Dashboard</a>
                                    @endif
                                    @if(auth()->user()->hasRole('admin'))
                                        <div class="border-t border-zinc-100 dark:border-zinc-800 my-1"></div>
                                        <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-xs font-semibold text-zinc-900 dark:text-white hover:bg-zinc-50 dark:hover:bg-zinc-800">Admin Console</a>
                                    @endif
                                </div>
                                <div class="border-t border-zinc-100 dark:border-zinc-800 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-xs font-medium text-red-600 dark:text-red-400 hover:bg-red-50/50 dark:hover:bg-red-950/30">
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

    <!-- Search Overlay -->
    <div x-show="searchOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.away="searchOpen = false"
         class="bg-white/95 dark:bg-zinc-950/95 backdrop-blur-xl border-t border-zinc-200/60 dark:border-zinc-800/80 p-4 shadow-xl"
         style="display: none;">
        <div class="max-w-3xl mx-auto relative">
            <form action="{{ route('catalog.index') }}" method="GET">
                <input type="text" name="search" 
                       placeholder="Search products, brands, services, machinery, cars, thrift..." 
                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-zinc-200 dark:border-zinc-700 bg-zinc-50/80 dark:bg-zinc-900 text-sm text-zinc-900 dark:text-white focus:bg-white dark:focus:bg-zinc-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition-all placeholder:text-zinc-400"
                       x-ref="navSearchInput"
                       x-init="$watch('searchOpen', val => { if(val) setTimeout(() => $refs.navSearchInput.focus(), 100) })">
                <div class="absolute left-3.5 top-3.5 text-zinc-400">
                    <svg width="20" height="20" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                    </svg>
                </div>
            </form>
        </div>
    </div>
</header>
