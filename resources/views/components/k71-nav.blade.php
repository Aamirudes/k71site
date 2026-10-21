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
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5 group" aria-label="MarbleMandi Homepage">
                            <div class="h-9 w-9 rounded-xl overflow-hidden bg-indigo-600 p-1 flex items-center justify-center shadow-md shadow-indigo-600/20 group-hover:scale-105 transition-transform shrink-0 border border-indigo-700">
                                <img src="{{ asset('images/k71-logo.png') }}" alt="M" class="w-full h-full object-contain">
                            </div>
                            <div class="flex flex-col leading-none">
                                <span class="font-sans text-xl sm:text-2xl font-black tracking-tight text-slate-900 flex items-center gap-1.5">
                                    Marble<span class="text-indigo-600">Mandi</span><span class="text-[9px] font-mono font-bold tracking-widest text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">DIRECT</span>
                                </span>
                            </div>
                            @if($currentKey !== 'main')
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    {{ $activeVertical['name'] ?? 'Hub' }}
                                </span>
                            @endif
                        </a>
                    @endif
                </div>
            </div>

            <!-- Desktop Middle: High-Contrast Verticals Navigation Bar -->
            <nav class="hidden lg:flex items-center gap-1 bg-slate-100 p-1.5 rounded-full border border-slate-200 shadow-inner" aria-label="Category Verticals">
                @foreach($allVerticals as $vKey => $vMeta)
                    @php $isCurrent = $currentKey === $vKey; @endphp
                    <a href="{{ $vMeta['url'] }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs transition-all duration-200 flex items-center gap-1.5 {{ $isCurrent ? 'bg-indigo-600 text-white shadow font-bold' : 'text-slate-700 hover:text-slate-950 hover:bg-white/90 font-semibold' }}">
                        <span>{{ $vMeta['name'] }}</span>
                        @if(isset($vMeta['badge']))
                            <span class="text-[8px] font-black px-1.5 py-0.5 rounded-full bg-rose-600 text-white tracking-wider animate-pulse">
                                {{ $vMeta['badge'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <!-- Right Actions: Search + Messages + Wishlist + Cart + Account -->
            <div class="flex items-center gap-1 sm:gap-2">

                <!-- Search Button -->
                <button @click="searchOpen = !searchOpen" 
                        class="p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-all duration-200"
                        aria-label="Search">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                    </svg>
                </button>

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

                <!-- Wishlist (Desktop) -->
                <a href="{{ route('wishlist.index') }}" 
                   class="hidden sm:flex p-2 rounded-xl text-slate-700 hover:text-slate-950 hover:bg-slate-100 transition-all duration-200 relative"
                   aria-label="Wishlist">
                    <svg width="24" height="24" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"></path>
                    </svg>
                    <span id="global-wishlist-badge" 
                          class="absolute top-1 right-1 bg-indigo-600 text-white text-[9px] font-bold px-1.5 py-0.2 rounded-full leading-tight min-w-[16px] text-center {{ $navWishlistCount > 0 ? '' : 'hidden' }}">
                        {{ $navWishlistCount }}
                    </span>
                </a>

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
                        <a href="{{ route('login') }}" 
                           class="px-4 py-2 rounded-full bg-indigo-600 text-white text-xs font-bold hover:bg-indigo-700 transition-all duration-200 shadow-sm shadow-indigo-600/20">
                            Sign In
                        </a>
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

    <!-- Search Overlay -->
    <div x-show="searchOpen" 
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
                <input type="text" name="search" 
                       placeholder="Search products, brands, services, machinery, cars, thrift..." 
                       class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-900 focus:bg-white focus:border-indigo-600 focus:ring-2 focus:ring-indigo-100 outline-none transition-all placeholder:text-slate-400 font-medium"
                       x-ref="navSearchInput"
                       x-init="$watch('searchOpen', val => { if(val) setTimeout(() => $refs.navSearchInput.focus(), 100) })">
                <div class="absolute left-3.5 top-3.5 text-slate-400">
                    <svg width="20" height="20" class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                    </svg>
                </div>
            </form>
        </div>
    </div>
</header>
