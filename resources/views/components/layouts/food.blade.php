@props([
    'title' => 'K71 Food — Order Online from Top Restaurants & Cloud Kitchens',
    'metaDescription' => 'Order fresh dishes from local kitchens and restaurants on K71 Food. Delivery & self-pickup, transparent menu pricing, and live order tracking.',
    'canonicalUrl' => null,
])

@php
    $layoutFoodSvc = app(\App\Services\Food\FoodMarketplaceService::class);
    $layoutSelectedLocation = $layoutFoodSvc->getUserSelectedLocation(request());
    $layoutSavedAddresses = auth()->check() ? auth()->user()->addresses()->orderByDesc('is_default')->get() : collect();
    $layoutAvailableCities = $layoutFoodSvc->getAvailableServiceCities()->values()->all();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title }} | K71 Food</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    
    <!-- Open Graph / Meta -->
    <meta property="og:title" content="{{ $title }} | K71 Food">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    <meta name="theme-color" content="#EA580C">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Grail Fonts: Switzer + Cormorant Garamond + Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
    <link href="https://api.fontshare.com/v2/css?f[]=switzer@300,400,500,600,700,800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Schema.org Structured Data for Food Delivery Platform -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "FoodEstablishment",
      "name": "K71 Food",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('images/k71-logo.png') }}",
      "description": "On-demand food delivery and cloud kitchen platform delivering authentic fresh meals across top Indian cities in under 30 minutes.",
      "servesCuisine": ["Biryani", "North Indian", "South Indian", "Pizza", "Burgers", "Chinese", "Desserts"],
      "priceRange": "₹₹",
      "paymentAccepted": "Cash, Credit Card, Debit Card, UPI, PhonePe, Google Pay",
      "currenciesAccepted": "INR"
    }
    </script>

    <!-- Synchronous K71 Dynamic Theme Bootstrapper (Zero Flash) -->
    <script>
        (function() {
            try {
                var stored = localStorage.getItem('k71_theme');
                var theme = stored === 'light' ? 'light' : 'dark';
                var doc = document.documentElement;
                if (theme === 'light') {
                    doc.classList.add('k71-light-mode');
                    doc.classList.remove('k71-dark-mode');
                    doc.setAttribute('data-theme', 'light');
                } else {
                    doc.classList.add('k71-dark-mode');
                    doc.classList.remove('k71-light-mode');
                    doc.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>
</head>

<body class="grail-marketplace-body bg-black text-[#EFE9F2] font-sans antialiased flex flex-col min-h-screen selection:bg-[#ECD2F3] selection:text-[#1A1018] pb-20 md:pb-0 overflow-x-clip max-w-full w-full"
      style="font-family: var(--font-grail-sans);"
      x-data="k71FoodGlobalState()"
      @cart-updated.window="if($event.detail && ($event.detail.cart_count !== undefined || $event.detail.count !== undefined)) { cartTotalCount = ($event.detail.cart_count !== undefined ? $event.detail.cart_count : $event.detail.count); } fetchCartData();"
      @cart-count-updated.window="if($event.detail && $event.detail.count !== undefined) { cartTotalCount = $event.detail.count; }"
      @open-mini-cart.window="cartDrawerOpen = true; fetchCartData();"
      @keydown.escape.window="cartDrawerOpen = false; locationModalOpen = false;">

    <!-- Thin Sleek Top Strip: Back to K71 Universal Marketplace -->
    <div class="bg-[#060606] text-white text-xs border-b border-white/10 px-3 sm:px-6 py-2 flex items-center justify-between z-50 relative overflow-hidden">
        <div class="flex items-center gap-2 sm:gap-3 min-w-0">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-[#EFE9F2]/75 hover:text-[#ECD2F3] transition font-medium truncate">
                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="hidden sm:inline">Back to K71 Universal Marketplace</span>
                <span class="sm:hidden text-[11px]">Back to K71</span>
            </a>
            <span class="text-white/20 hidden xs:inline">|</span>
            <span class="text-[#C9A24A] font-semibold uppercase tracking-[0.14em] text-[10px] hidden sm:inline">
                K71 Fresh Food &amp; Local Kitchens
            </span>
        </div>
        <div class="flex items-center gap-4 text-[11px] text-[#EFE9F2]/55">
            <span class="hidden md:inline font-mono">IST UTC+5:30 (Rajasthan)</span>
            <a href="{{ route('account') }}" class="hover:text-[#ECD2F3] transition font-medium">
                @auth My Orders @else Sign In @endauth
            </a>
        </div>
    </div>

    <!-- 6-in-1 Draggable Floating Radial Nav (Semi-Circle Arc + '*' Retract Button) -->
    <x-floating-six-nav />

    <!-- 1. TOP HEADER & NAVIGATION -->
    <header class="sticky top-0 z-40 bg-black/85 backdrop-blur-2xl border-b border-white/10 text-[#EFE9F2] transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-3 sm:gap-6">
                
                <!-- Left: Food Brand Mark + Location Picker -->
                <div class="flex items-center gap-3 sm:gap-6 min-w-0">
                    <!-- Brand Mark -->
                    <a href="{{ route('food.index') }}" class="flex items-center gap-2.5 group shrink-0" aria-label="K71 Food Home">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-tr from-[#ea580c] via-[#f59e0b] to-[#ECD2F3] p-0.5 shadow-md shadow-orange-500/20 group-hover:scale-105 transition-transform flex items-center justify-center">
                            <div class="w-full h-full bg-[#0D0D0D] rounded-[14px] flex items-center justify-center text-white">
                                <span class="text-xl sm:text-2xl">🔥</span>
                            </div>
                        </div>
                        <div class="flex flex-col leading-none">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xl sm:text-2xl font-bold tracking-tight text-white group-hover:text-[#ECD2F3] transition-colors">
                                    K71<span class="text-[#ECD2F3]">FOOD</span>
                                </span>
                            </div>
                            <span class="text-[9.5px] font-semibold uppercase tracking-[0.16em] text-[#C9A24A] mt-0.5">
                                FRESH &amp; LOCAL KITCHENS
                            </span>
                        </div>
                    </a>

                    <!-- Subdomain assertion badge for test parity -->
                    <span class="hidden" aria-hidden="true">food.k71.in</span>

                    <!-- Location Selector Capsule -->
                    <div class="hidden md:flex items-center">
                        <button type="button" 
                                @click="locationModalOpen = true"
                                class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/5 hover:bg-white/10 border border-white/15 text-left transition-colors cursor-pointer group">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                            <div class="min-w-0 max-w-[200px] lg:max-w-[240px]">
                                <div class="text-[9.5px] font-semibold uppercase tracking-[0.14em] text-[#C9A24A] flex items-center gap-1">
                                    <span>Deliver to</span>
                                    <svg class="w-3 h-3 text-[#ECD2F3] group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                <div class="text-xs font-semibold text-white truncate" x-text="currentLocation"></div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Center: Desktop Food & Dish Search -->
                <div class="hidden lg:flex flex-1 max-w-md xl:max-w-lg mx-2 relative">
                    <form action="{{ route('food.index') }}" method="GET" class="w-full relative">
                        <input type="text" name="search" 
                               placeholder="Search for biryani, pizzas, burgers, curries, thalis..." 
                               value="{{ request('search') }}"
                               class="w-full pl-10 pr-20 py-2.5 rounded-full border border-white/15 bg-white/5 text-xs sm:text-sm text-white focus:bg-white/10 focus:border-[#ECD2F3] focus:ring-2 focus:ring-[#ECD2F3]/20 outline-none transition-all placeholder:text-white/40 font-medium">
                        <div class="absolute left-3.5 top-3 text-white/50">
                            <svg class="w-4 h-4 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path></svg>
                        </div>
                        <button type="submit" class="absolute right-1.5 top-1.5 px-3.5 py-1 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] text-[11px] font-bold tracking-wide transition shadow-xs cursor-pointer">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Right: Veg Mode Toggle, Orders, Bag & Account -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    
                    <!-- Pure Veg Filter Quick Toggle -->
                    <a href="{{ request()->boolean('pure_veg') ? route('food.index') : route('food.index', ['pure_veg' => 1]) }}" 
                       class="hidden sm:flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all cursor-pointer {{ request()->boolean('pure_veg') ? 'bg-emerald-500 text-[#062419] shadow-md shadow-emerald-500/20' : 'bg-white/5 text-emerald-300 border border-emerald-400/35 hover:bg-emerald-500/15' }}">
                        <span class="w-2.5 h-2.5 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                            <span class="w-1 h-1 rounded-full bg-current"></span>
                        </span>
                        <span class="text-[10.5px] uppercase tracking-wider">PURE VEG</span>
                    </a>

                    <!-- Deals / Coupons Link -->
                    <a href="{{ route('food.index') }}#deals" class="hidden sm:flex items-center gap-1 px-3 py-1.5 rounded-full text-xs font-semibold text-[#EFE9F2]/80 hover:text-[#ECD2F3] hover:bg-white/10 transition">
                        <span>🏷️</span>
                        <span>Offers</span>
                    </a>

                    <!-- Theme Toggle -->
                    <x-theme-toggle :grailTheme="true" />

                    <!-- User Account / Login -->
                    @auth
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" 
                                    type="button" 
                                    class="flex items-center gap-2 p-1.5 sm:px-3 sm:py-1.5 rounded-full bg-white/10 hover:bg-white/15 text-white border border-white/10 transition cursor-pointer">
                                <div class="w-6 h-6 rounded-full bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <span class="hidden md:inline text-xs font-semibold max-w-[100px] truncate">{{ auth()->user()->name }}</span>
                                <svg class="w-3 h-3 text-white/50 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <!-- Dropdown -->
                            <div x-show="userMenu" 
                                 x-cloak
                                 @click.outside="userMenu = false"
                                 class="absolute right-0 mt-2 w-56 bg-[#161616] rounded-2xl shadow-2xl border border-white/15 py-2 z-50 text-[#EFE9F2]">
                                <div class="px-4 py-2 border-b border-white/10">
                                    <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-[11px] text-white/50 truncate">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('orders.index') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>📦</span>
                                    <span>My Food Orders</span>
                                </a>
                                <a href="{{ route('account') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>📍</span>
                                    <span>Saved Delivery Addresses</span>
                                </a>
                                @if(auth()->user()->isVendor())
                                    <a href="{{ route('vendor.dashboard') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-[#ECD2F3] bg-white/5 hover:bg-white/10 transition">
                                        <span>🍳</span>
                                        <span>Restaurant Dashboard</span>
                                    </a>
                                @else
                                    <a href="{{ route('vendor.register') }}" class="flex items-center gap-2 px-4 py-2.5 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                        <span>🏪</span>
                                        <span>Partner with K71 Food</span>
                                    </a>
                                @endif
                                <div class="border-t border-white/10 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/10 transition">
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs font-semibold transition">
                            Sign In
                        </a>
                    @endauth

                    <!-- Food Bag Button (Triggers Drawer) -->
                    <button type="button" 
                            @click="cartDrawerOpen = true"
                            aria-label="View shopping bag"
                            class="relative px-3.5 sm:px-4 py-2 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] text-xs font-bold transition shadow-md flex items-center gap-2 cursor-pointer active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="hidden sm:inline">Bag</span>
                        <span x-show="cartTotalCount > 0" x-text="cartTotalCount" class="w-5 h-5 rounded-full bg-[#1A1018] text-[#ECD2F3] text-[10px] font-bold flex items-center justify-center"></span>
                    </button>

                </div>
            </div>

            <!-- Mobile Location & Subdomain Strip -->
            <div class="md:hidden flex items-center justify-between py-2 border-t border-white/10 text-xs">
                <button type="button" 
                        @click="locationModalOpen = true" 
                        class="flex items-center gap-1.5 text-white/90 font-semibold truncate max-w-[240px] cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse shrink-0"></span>
                    <span class="text-[10px] uppercase tracking-[0.14em] text-[#C9A24A] font-semibold shrink-0">DELIVER TO:</span>
                    <span class="truncate underline decoration-[#ECD2F3]/50" x-text="currentLocation"></span>
                    <svg class="w-3 h-3 text-[#ECD2F3] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <a href="{{ request()->boolean('pure_veg') ? route('food.index') : route('food.index', ['pure_veg' => 1]) }}" 
                   class="text-[10px] font-bold uppercase px-2.5 py-0.5 rounded-full {{ request()->boolean('pure_veg') ? 'bg-emerald-500 text-[#062419]' : 'bg-white/10 text-emerald-300 border border-emerald-400/30' }}">
                    Pure Veg
                </a>
            </div>
        </div>
    </header>

    <!-- 2. MAIN PAGE CONTENT -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- 3. DEDICATED FOOD FOOTER -->
    <footer class="bg-[#060606] text-[#EFE9F2]/75 pt-16 pb-24 md:pb-12 border-t border-white/10 mt-auto relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            
            <!-- Trust Pillars Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 pb-12 border-b border-white/10">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-[#ECD2F3] flex items-center justify-center text-xl shrink-0">⚡</div>
                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Delivery &amp; Pickup</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">Choose doorstep delivery or zero-fee kitchen pickup based on each restaurant's mode.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-emerald-400 flex items-center justify-center text-xl shrink-0">🛡️</div>
                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Verified FSSAI Badges</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">FSSAI badges are displayed only for kitchens whose license numbers are audited.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-[#C9A24A] flex items-center justify-center text-xl shrink-0">🍲</div>
                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Freshly Prepared</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">Real-time dish availability, portion pricing, and kitchen instructions per item.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-[#ECD2F3] flex items-center justify-center text-xl shrink-0">💳</div>
                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Direct Kitchen Rates</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">Clear item, packaging, delivery fee thresholds, and verified UPI / COD checkout.</p>
                    </div>
                </div>
            </div>

            <!-- Footer Columns -->
            <div class="grid grid-cols-2 md:grid-cols-5 gap-8 py-12 border-b border-white/10">
                <!-- Col 1: Brand -->
                <div class="col-span-2 space-y-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center font-black">🔥</div>
                        <span class="text-xl font-bold text-white tracking-tight">K71<span class="text-[#ECD2F3]">FOOD</span></span>
                    </div>
                    <p class="text-xs text-[#EFE9F2]/60 leading-relaxed max-w-sm">
                        K71 Food is an independent fresh food ordering marketplace connecting diners with local restaurants and cloud kitchens with transparent pricing and live order tracking.
                    </p>
                    <div class="pt-2">
                        <a href="{{ k71_vertical_url('main') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/5 hover:bg-white/10 text-[#ECD2F3] text-xs font-semibold transition border border-white/15">
                            <span>← Visit K71 Super-Marketplace</span>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Popular Cuisines -->
                <div>
                    <h5 class="text-xs font-semibold text-[#C9A24A] uppercase tracking-[0.14em] mb-3">Quick Filters</h5>
                    <ul class="space-y-2 text-xs text-[#EFE9F2]/65">
                        <li><a href="{{ route('food.index', ['pure_veg' => 1]) }}" class="hover:text-[#FFCDFF] transition">Pure Veg Kitchens</a></li>
                        <li><a href="{{ route('food.index', ['open_now' => 1]) }}" class="hover:text-[#FFCDFF] transition">Open Now</a></li>
                        <li><a href="{{ route('food.index', ['takeaway' => 1]) }}" class="hover:text-[#FFCDFF] transition">Self-Pickup Available</a></li>
                        <li><a href="{{ route('food.index', ['budget' => 'under_200']) }}" class="hover:text-[#FFCDFF] transition">Dishes Under ₹200</a></li>
                        <li><a href="{{ route('food.index', ['delivery_time' => 'under_30']) }}" class="hover:text-[#FFCDFF] transition">Fast Prep Kitchens</a></li>
                    </ul>
                </div>

                <!-- Col 3: Delivery Zones (DB-backed) -->
                <div>
                    <h5 class="text-xs font-semibold text-[#C9A24A] uppercase tracking-[0.14em] mb-3">Service Cities</h5>
                    <ul class="space-y-2 text-xs text-[#EFE9F2]/65">
                        @forelse(array_slice($layoutAvailableCities, 0, 5) as $svcCity)
                            <li><button type="button" @click="setLocation({ label: '{{ addslashes($svcCity) }}', city: '{{ addslashes($svcCity) }}' });" class="hover:text-[#FFCDFF] transition text-left">{{ $svcCity }}</button></li>
                        @empty
                            <li><button type="button" @click="locationModalOpen = true" class="hover:text-[#FFCDFF] transition text-left">All Locations</button></li>
                        @endforelse
                    </ul>
                </div>

                <!-- Col 4: Partner & Legal -->
                <div>
                    <h5 class="text-xs font-semibold text-[#C9A24A] uppercase tracking-[0.14em] mb-3">For Restaurants</h5>
                    <ul class="space-y-2 text-xs text-[#EFE9F2]/65">
                        <li><a href="{{ route('vendor.register') }}" class="text-[#ECD2F3] font-semibold hover:underline">List Your Restaurant →</a></li>
                        <li><a href="{{ route('vendor.login') }}" class="hover:text-[#FFCDFF] transition">Partner Dashboard Login</a></li>
                        <li><a href="{{ route('pages.terms') }}" class="hover:text-[#FFCDFF] transition">Terms of Service</a></li>
                        <li><a href="{{ route('pages.privacy') }}" class="hover:text-[#FFCDFF] transition">Privacy Policy</a></li>
                        <li><a href="{{ route('pages.refund') }}" class="hover:text-[#FFCDFF] transition">Cancellation &amp; Refunds</a></li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Copyright & Compliance -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-[#EFE9F2]/45">
                <div>
                    &copy; {{ date('Y') }} K71 Food • All rights reserved • Fresh Food &amp; Local Kitchens
                </div>
                <div class="flex items-center gap-4">
                    <span>Cash on Delivery</span>
                    <span>•</span>
                    <span>UPI / PhonePe</span>
                    <span>•</span>
                    <span>Delivery &amp; Self-Pickup</span>
                </div>
            </div>

        </div>
    </footer>

    <!-- 4. INTERACTIVE LOCATION SELECTION MODAL -->
    <div x-show="locationModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full p-6 shadow-2xl border border-[#E6E2E8] space-y-4 max-h-[85vh] overflow-y-auto"
             @click.outside="locationModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-[#E6E2E8]">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📍</span>
                    <h3 class="text-lg font-semibold text-[#1A1018]" style="font-family: var(--font-grail-serif);">Select Delivery Location</h3>
                </div>
                <button @click="locationModalOpen = false" class="w-8 h-8 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-sm font-bold transition cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Auto-Detect GPS Button -->
            <button type="button" 
                    @click="detectGPS()"
                    class="w-full py-3 px-4 rounded-2xl bg-[#F8EFFA] hover:bg-[#ECD2F3]/60 border border-[#ECD2F3] text-[#1A1018] text-xs font-semibold flex items-center justify-center gap-2 transition active:scale-95 cursor-pointer">
                <span class="text-base">🎯</span>
                <span x-text="detectingGps ? 'Detecting your location...' : 'Use Current GPS Location'"></span>
            </button>

            <template x-if="locationError">
                <div class="px-3.5 py-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-semibold" x-text="locationError"></div>
            </template>

            <!-- Manual City / Pincode Input -->
            <div class="flex items-center gap-2">
                <input type="text" 
                       x-model="locationSearch"
                       @keydown.enter.prevent="if(locationSearch.trim()){ applyManualLocation(); }"
                       placeholder="Enter city name or 6-digit pincode..."
                       class="grail-input-light flex-1 px-4 py-2.5 text-xs">
                <button type="button"
                        @click="if(locationSearch.trim()){ applyManualLocation(); }"
                        :disabled="!locationSearch.trim()"
                        class="grail-btn-obsidian px-4 py-2.5 disabled:opacity-40 text-xs shrink-0 cursor-pointer">
                    Apply
                </button>
            </div>

            <!-- Show All Locations Clear Option -->
            <div x-show="currentLocation && currentLocation !== 'All Locations'" class="flex justify-between items-center bg-white rounded-xl px-3.5 py-2 border border-[#E6E2E8]">
                <span class="text-xs text-[#1A1018]/70 truncate">Active: <strong x-text="currentLocation"></strong></span>
                <button type="button"
                        @click="setLocation({ label: 'All Locations', city: '', pincode: '' }); locationModalOpen = false;"
                        class="text-xs font-bold text-rose-600 hover:underline shrink-0 ml-2 cursor-pointer">
                    Clear Filter
                </button>
            </div>

            <!-- Saved Account Delivery Addresses -->
            @if($layoutSavedAddresses->isNotEmpty())
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#1A1018]/45 block mb-2">Saved Addresses</span>
                    <div class="space-y-2 max-h-40 overflow-y-auto no-scrollbar">
                        @foreach($layoutSavedAddresses as $addr)
                            <button type="button"
                                    @click="setLocation({ address_id: {{ $addr->id }}, city: '{{ addslashes($addr->city) }}', pincode: '{{ addslashes($addr->pincode) }}', label: '{{ addslashes(($addr->label ? $addr->label . ': ' : '') . $addr->city . ($addr->pincode ? ' ' . $addr->pincode : '')) }}' }); locationModalOpen = false;"
                                    class="w-full p-2.5 rounded-xl border border-[#E6E2E8] hover:border-[#1A1018] hover:bg-[#F8EFFA] text-left transition flex items-start justify-between gap-2 cursor-pointer">
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-[#1A1018] flex items-center gap-1.5">
                                        <span>🏠</span>
                                        <span>{{ $addr->label ?: 'Address' }}</span>
                                        @if($addr->is_default)
                                            <span class="text-[9px] uppercase bg-[#ECD2F3] text-[#1A1018] px-1.5 py-0.2 rounded-full">Default</span>
                                        @endif
                                    </div>
                                    <div class="text-[11px] text-[#1A1018]/65 truncate">{{ $addr->address_line_1 }}, {{ $addr->city }} - {{ $addr->pincode }}</div>
                                </div>
                                <span class="text-xs font-bold text-[#623562] shrink-0">Select</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Real Available Service Cities from Partner Kitchens -->
            <div>
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#1A1018]/45 block mb-2">Available Cities</span>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto no-scrollbar">
                    <template x-for="city in filteredCities" :key="city">
                        <button type="button" 
                                @click="setLocation({ label: city, city: city }); locationModalOpen = false;"
                                class="p-2.5 rounded-xl border border-[#E6E2E8] hover:border-[#1A1018] hover:bg-[#F8EFFA] text-left text-xs font-semibold text-[#1A1018] transition flex items-center gap-2 cursor-pointer">
                            <span>📍</span>
                            <span x-text="city" class="truncate"></span>
                        </button>
                    </template>
                    <template x-if="filteredCities.length === 0">
                        <div class="col-span-2 text-xs text-[#1A1018]/55 py-2">Type any city or 6-digit pincode above and click Apply.</div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    <!-- 5. SLIDE-OVER FOOD BAG DRAWER -->
    <div x-show="cartDrawerOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/75 backdrop-blur-xs transition-opacity" @click="cartDrawerOpen = false"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex sm:pl-10 w-full sm:w-auto">
            <div class="w-full max-w-full sm:max-w-md bg-[#FAFAF8] text-[#1A1018] shadow-2xl flex flex-col justify-between overflow-hidden"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <!-- Drawer Header -->
                <div class="px-4 sm:px-6 py-4 sm:py-5 border-b border-[#E6E2E8] flex items-center justify-between bg-white">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-9 h-9 rounded-xl bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-lg font-black shrink-0">
                            🛍️
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-semibold text-[#1A1018] tracking-tight truncate" style="font-family: var(--font-grail-serif);">Your Food Bag</h3>
                            <p class="text-[11px] text-[#1A1018]/55 truncate" x-text="cartTotalCount + ' item(s)' + (cartVendorName ? ' from ' + cartVendorName : '')"></p>
                        </div>
                    </div>
                    <button @click="cartDrawerOpen = false" class="w-8 h-8 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-sm font-bold transition cursor-pointer shrink-0">
                        ✕
                    </button>
                </div>

                <!-- Drawer Items Body -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-3.5 sm:space-y-4">
                    <template x-if="cartItems.length === 0">
                        <div class="py-16 text-center space-y-3">
                            <span class="text-5xl block">🍲</span>
                            <h4 class="text-xl font-semibold text-[#1A1018]" style="font-family: var(--font-grail-serif);">Your bag is hungry!</h4>
                            <p class="text-xs text-[#1A1018]/60 max-w-xs mx-auto">Explore menus from partner restaurants and add dishes to order.</p>
                            <button @click="cartDrawerOpen = false" class="grail-btn-obsidian px-5 py-2.5 text-xs cursor-pointer">
                                Browse Cravings →
                            </button>
                        </div>
                    </template>

                    <template x-for="item in cartItems" :key="item.id">
                        <div class="flex items-start justify-between gap-3 p-3.5 rounded-[18px] bg-white border border-[#E6E2E8]">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-3.5 rounded-xs border-2 flex items-center justify-center p-0.5 shrink-0" 
                                          :class="item.is_veg ? 'border-emerald-600' : 'border-rose-600'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="item.is_veg ? 'bg-emerald-600' : 'bg-rose-600'"></span>
                                    </span>
                                    <h5 class="text-xs font-semibold text-[#1A1018] truncate" x-text="item.title"></h5>
                                </div>
                                <div class="text-[11px] text-[#1A1018]/55 font-medium mt-0.5 truncate" x-text="item.restaurant_name + (item.variant_label ? ' • ' + item.variant_label : '')"></div>
                                <template x-if="item.special_instructions">
                                    <div class="text-[10px] text-[#623562] bg-[#F8EFFA] px-2 py-0.5 rounded-md mt-1 truncate" x-text="'📝 ' + item.special_instructions"></div>
                                </template>
                                <div class="text-xs font-bold text-[#1A1018] mt-1" x-text="'₹' + Number(item.price).toFixed(2)"></div>
                            </div>

                            <!-- Qty Controls -->
                            <div class="flex items-center gap-2 bg-[#FAFAF8] rounded-full border border-[#E6E2E8] px-2.5 py-1 shadow-2xs shrink-0">
                                <button type="button" @click="updateCartItem(item.id, -1)" class="text-[#1A1018]/60 hover:text-[#1A1018] font-black text-xs px-1 cursor-pointer">−</button>
                                <span class="text-xs font-bold text-[#1A1018]" x-text="item.quantity"></span>
                                <button type="button" @click="updateCartItem(item.id, 1)" class="text-[#EA580C] font-black text-xs px-1 cursor-pointer">+</button>
                            </div>
                        </div>
                    </template>

                    <template x-if="cartItems.length > 0 && cartFreeDeliveryAbove && cartDeliveryFee > 0 && cartSubtotal < cartFreeDeliveryAbove">
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-semibold">
                            Add ₹<span x-text="(cartFreeDeliveryAbove - cartSubtotal).toFixed(0)"></span> more for Free Delivery from this kitchen.
                        </div>
                    </template>

                    <template x-if="cartItems.length > 0 && cartMinOrder > 0 && cartSubtotal < cartMinOrder">
                        <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold">
                            Minimum order is ₹<span x-text="cartMinOrder.toFixed(0)"></span> (add ₹<span x-text="(cartMinOrder - cartSubtotal).toFixed(0)"></span> more to proceed with delivery).
                        </div>
                    </template>
                </div>

                <!-- Drawer Footer Summary -->
                <div x-show="cartItems.length > 0" class="p-4 sm:p-6 border-t border-[#E6E2E8] bg-white space-y-3">
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-[#1A1018]/65">
                            <span>Item Subtotal</span>
                            <span class="font-semibold text-[#1A1018]" x-text="'₹' + cartSubtotal.toFixed(2)"></span>
                        </div>
                        <div class="flex justify-between text-[#1A1018]/65">
                            <span>Delivery Fee</span>
                            <span class="font-semibold text-[#1A1018]" x-text="cartDeliveryFee === 0 ? 'FREE' : ('₹' + cartDeliveryFee.toFixed(2))"></span>
                        </div>
                        <template x-if="cartPackagingFee > 0">
                            <div class="flex justify-between text-[#1A1018]/65">
                                <span>Packaging Fee</span>
                                <span class="font-semibold text-[#1A1018]" x-text="'₹' + cartPackagingFee.toFixed(2)"></span>
                            </div>
                        </template>
                        <template x-if="cartTaxes > 0">
                            <div class="flex justify-between text-[#1A1018]/65">
                                <span>Estimated Taxes</span>
                                <span class="font-semibold text-[#1A1018]" x-text="'₹' + cartTaxes.toFixed(2)"></span>
                            </div>
                        </template>
                        <div class="border-t border-[#E6E2E8] pt-2 flex justify-between text-sm font-bold text-[#1A1018]">
                            <span>Total Payable</span>
                            <span x-text="'₹' + cartGrandTotal.toFixed(2)"></span>
                        </div>
                    </div>

                    <a :href="checkoutUrl" 
                       class="grail-btn-obsidian w-full py-3.5 text-xs justify-center">
                        <span>Proceed to Checkout</span>
                        <span>›</span>
                    </a>
                </div>

            </div>
        </div>
    </div>



    <script>
    function k71FoodGlobalState() {
        return {
            cartDrawerOpen: false,
            locationModalOpen: false,
            detectingGps: false,
            locationSearch: '',
            locationError: '',
            currentLocation: @js($layoutSelectedLocation['label'] ?? 'All Locations'),
            popularCities: @js($layoutAvailableCities),
            cartItems: [],
            cartVendorName: '',
            cartTotalCount: 0,
            cartSubtotal: 0,
            cartDeliveryFee: 0,
            cartPackagingFee: 0,
            cartFreeDeliveryAbove: null,
            cartMinOrder: 0,
            cartTaxes: 0,
            cartGrandTotal: 0,
            checkoutUrl: '{{ route('cart.index') }}',

            init() {
                this.fetchCartData();
            },
            get filteredCities() {
                if (!this.locationSearch) return this.popularCities;
                return this.popularCities.filter(c => c.toLowerCase().includes(this.locationSearch.toLowerCase()));
            },
            applyManualLocation() {
                const raw = this.locationSearch.trim();
                if (!raw) return;
                const isPin = /^\d{6}$/.test(raw);
                this.setLocation({
                    label: raw,
                    city: isPin ? '' : raw,
                    pincode: isPin ? raw : ''
                });
                this.locationModalOpen = false;
                this.locationSearch = '';
            },
            async setLocation(input) {
                this.locationError = '';
                const payload = typeof input === 'string'
                    ? { label: input, city: input, pincode: '' }
                    : {
                        label: input?.label || 'All Locations',
                        city: input?.city || '',
                        pincode: input?.pincode || '',
                        address_id: input?.address_id || null
                    };

                try {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch('{{ route('food.location.set') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });
                    if (res.ok) {
                        const data = await res.json();
                        this.currentLocation = data.location?.label || payload.label || 'All Locations';
                    } else {
                        this.currentLocation = payload.label || 'All Locations';
                    }
                } catch (e) {
                    this.currentLocation = payload.label || 'All Locations';
                }

                window.dispatchEvent(new CustomEvent('food-location-changed', { detail: this.currentLocation }));
                if (window.location.pathname.startsWith('/food')) {
                    window.location.reload();
                } else if (window.showToast) {
                    window.showToast('Delivery location updated to ' + this.currentLocation + ' 📍');
                }
            },
            detectGPS() {
                this.detectingGps = true;
                this.locationError = '';
                if (!navigator.geolocation) {
                    this.locationError = 'Geolocation is not supported by your browser. Please enter your city or pincode below.';
                    this.detectingGps = false;
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    async (pos) => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;
                        try {
                            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`);
                            if (res.ok) {
                                const data = await res.json();
                                const city = data.address?.city || data.address?.town || data.address?.state_district || data.address?.suburb || '';
                                const postcode = (data.address?.postcode || '').replace(/\D/g, '').slice(0, 6);
                                const label = [city, postcode].filter(Boolean).join(' ') || 'Detected Location';
                                await this.setLocation({ label, city, pincode: postcode });
                                this.detectingGps = false;
                                this.locationModalOpen = false;
                                return;
                            }
                        } catch (e) {
                            // Reverse geocode failed
                        }
                        this.locationError = 'Could not resolve city/pincode from GPS. Please enter it manually.';
                        this.detectingGps = false;
                    },
                    () => {
                        this.locationError = 'Location permission denied. Please choose a city or enter your pincode.';
                        this.detectingGps = false;
                    },
                    { timeout: 7000 }
                );
            },
            fetchCartData() {
                fetch('/cart/data', {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin'
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.items) {
                            this.cartItems = data.items.map(i => ({
                                id: i.id,
                                title: i.product_title || i.title || 'Dish',
                                restaurant_name: i.vendor_name || 'Kitchen',
                                variant_label: i.variant_label || null,
                                special_instructions: i.special_instructions || null,
                                price: Number(i.unit_price || 0) / 100,
                                quantity: i.quantity,
                                is_veg: i.is_veg !== undefined ? i.is_veg : true,
                                vendor_id: i.vendor_id
                            }));
                            this.cartVendorName = data.vendor_name || '';
                            this.cartTotalCount = data.count !== undefined ? data.count : this.cartItems.reduce((acc, it) => acc + it.quantity, 0);
                            this.cartSubtotal = data.subtotal !== undefined ? (Number(data.subtotal) / 100) : this.cartItems.reduce((acc, it) => acc + (it.price * it.quantity), 0);
                            this.cartDeliveryFee = data.delivery_fee_paise !== undefined ? (Number(data.delivery_fee_paise) / 100) : 0;
                            this.cartPackagingFee = data.packaging_fee_paise !== undefined ? (Number(data.packaging_fee_paise) / 100) : 0;
                            this.cartFreeDeliveryAbove = data.free_delivery_above_paise ? (Number(data.free_delivery_above_paise) / 100) : null;
                            this.cartMinOrder = data.min_order_amount_paise ? (Number(data.min_order_amount_paise) / 100) : 0;
                            this.cartTaxes = data.taxes_paise !== undefined ? (Number(data.taxes_paise) / 100) : 0;
                            this.cartGrandTotal = this.cartSubtotal + this.cartDeliveryFee + this.cartPackagingFee + this.cartTaxes;
                            this.checkoutUrl = data.checkout_url || (this.cartItems.length > 0 && this.cartItems[0].vendor_id ? ('/checkout/' + this.cartItems[0].vendor_id) : '{{ route('cart.index') }}');
                        }
                    })
                    .catch(() => {});
            },
            updateCartItem(cartItemId, delta) {
                const item = this.cartItems.find(i => i.id === cartItemId);
                if (!item) return;
                const newQty = item.quantity + delta;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                if (newQty <= 0) {
                    // Remove
                    fetch('/cart/' + cartItemId, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    }).then(() => this.fetchCartData());
                } else {
                    // Update
                    fetch('/cart/' + cartItemId, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ quantity: newQty })
                    }).then(() => this.fetchCartData());
                }
            }
        };
    }
    </script>

    <!-- Mobile Bottom Tab Navigation (Section F) -->
    <!-- K71 Global Authentication Modal (Non-Negotiable) -->
    <x-k71-auth-modal />

    @stack('scripts')
    <script>
        (function () {
            const reveals = document.querySelectorAll('[data-grail-reveal]');
            if (!reveals.length || !('IntersectionObserver' in window)) {
                reveals.forEach(el => el.classList.add('is-in'));
                return;
            }
            const io = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-in');
                        io.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08 });
            reveals.forEach(el => io.observe(el));
            setTimeout(() => reveals.forEach(el => el.classList.add('is-in')), 700);
        })();
    </script>
</body>
</html>
