@props([
    'title' => 'K71 Food — Order Online from Top Restaurants & Cloud Kitchens',
    'metaDescription' => 'Order fresh artisan biryani, wood-fired pizza, burgers, pure veg thalis, and desserts on K71 Food. 25-minute superfast doorstep delivery, live kitchen tracking, and verified hygiene.',
    'canonicalUrl' => null,
])

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
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Fast Dispatch</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">Average delivery under 25-30 mins from local verified kitchens.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-emerald-400 flex items-center justify-center text-xl shrink-0">🛡️</div>
                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Tamper-Proof Safe</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">100% sanitized, hygienically packed, seal-locked food boxes.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-[#C9A24A] flex items-center justify-center text-xl shrink-0">🍲</div>
                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Fresh Quality</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">Cooked fresh to order by certified culinary masters.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 text-[#ECD2F3] flex items-center justify-center text-xl shrink-0">💳</div>
                    <div>
                        <h4 class="text-xs font-semibold text-white uppercase tracking-[0.14em]">Zero Surge Pricing</h4>
                        <p class="text-[11px] text-[#EFE9F2]/55 mt-0.5">Transparent prices, direct restaurant rates &amp; verified COD/UPI.</p>
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
                        K71 Food is an independent, fresh food ordering destination empowering cloud kitchens, legacy restaurants, and artisan bakers to serve hungry foodies with supreme hygiene and speed.
                    </p>
                    <div class="pt-2">
                        <a href="{{ k71_vertical_url('main') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-white/5 hover:bg-white/10 text-[#ECD2F3] text-xs font-semibold transition border border-white/15">
                            <span>← Visit K71 Super-Marketplace</span>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Popular Cuisines -->
                <div>
                    <h5 class="text-xs font-semibold text-[#C9A24A] uppercase tracking-[0.14em] mb-3">Popular Cuisines</h5>
                    <ul class="space-y-2 text-xs text-[#EFE9F2]/65">
                        <li><a href="{{ route('food.index', ['search' => 'Biryani']) }}" class="hover:text-[#FFCDFF] transition">Dum Biryani</a></li>
                        <li><a href="{{ route('food.index', ['search' => 'Pizza']) }}" class="hover:text-[#FFCDFF] transition">Wood-Fired Pizza</a></li>
                        <li><a href="{{ route('food.index', ['search' => 'Burger']) }}" class="hover:text-[#FFCDFF] transition">Smash Burgers</a></li>
                        <li><a href="{{ route('food.index', ['search' => 'North Indian']) }}" class="hover:text-[#FFCDFF] transition">North Indian Thalis</a></li>
                        <li><a href="{{ route('food.index', ['search' => 'Dessert']) }}" class="hover:text-[#FFCDFF] transition">Cakes &amp; Desserts</a></li>
                    </ul>
                </div>

                <!-- Col 3: Delivery Zones -->
                <div>
                    <h5 class="text-xs font-semibold text-[#C9A24A] uppercase tracking-[0.14em] mb-3">Delivery Hubs</h5>
                    <ul class="space-y-2 text-xs text-[#EFE9F2]/65">
                        <li><button type="button" @click="setLocation('Jaipur, Rajasthan');" class="hover:text-[#FFCDFF] transition text-left">Jaipur Central &amp; Malviya</button></li>
                        <li><button type="button" @click="setLocation('Delhi NCR');" class="hover:text-[#FFCDFF] transition text-left">Delhi NCR Hub</button></li>
                        <li><button type="button" @click="setLocation('Mumbai, Maharashtra');" class="hover:text-[#FFCDFF] transition text-left">Mumbai Metro</button></li>
                        <li><button type="button" @click="setLocation('Bengaluru, Karnataka');" class="hover:text-[#FFCDFF] transition text-left">Bengaluru Tech Hub</button></li>
                        <li><button type="button" @click="setLocation('Lucknow, Uttar Pradesh');" class="hover:text-[#FFCDFF] transition text-left">Lucknow Awadh Hub</button></li>
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
                    &copy; {{ date('Y') }} K71 Food • All rights reserved • Certified FSSAI Compliant Food Delivery
                </div>
                <div class="flex items-center gap-4">
                    <span>Cash on Delivery</span>
                    <span>•</span>
                    <span>UPI / PhonePe</span>
                    <span>•</span>
                    <span>Fast Doorstep Delivery</span>
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
        
        <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full p-6 shadow-2xl border border-[#E6E2E8] space-y-4"
             @click.outside="locationModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-[#E6E2E8]">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📍</span>
                    <h3 class="text-lg font-semibold text-[#1A1018]" style="font-family: var(--font-grail-serif);">Select Delivery Location</h3>
                </div>
                <button @click="locationModalOpen = false" class="w-8 h-8 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-sm font-bold transition">
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

            <!-- Manual Search Input with instant Set button -->
            <div class="flex items-center gap-2">
                <input type="text" 
                       x-model="locationSearch"
                       @keydown.enter.prevent="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                       placeholder="Type your area, city, or delivery pincode..."
                       class="grail-input-light flex-1 px-4 py-2.5 text-xs">
                <button type="button"
                        @click="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                        :disabled="!locationSearch.trim()"
                        class="grail-btn-obsidian px-4 py-2.5 disabled:opacity-40 text-xs shrink-0">
                    Apply
                </button>
            </div>

            <!-- Popular Indian Delivery Cities -->
            <div>
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#1A1018]/45 block mb-2">Popular Cities</span>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto no-scrollbar">
                    <template x-for="city in filteredCities" :key="city">
                        <button type="button" 
                                @click="setLocation(city); locationModalOpen = false;"
                                class="p-2.5 rounded-xl border border-[#E6E2E8] hover:border-[#1A1018] hover:bg-[#F8EFFA] text-left text-xs font-semibold text-[#1A1018] transition flex items-center gap-2">
                            <span>📍</span>
                            <span x-text="city" class="truncate"></span>
                        </button>
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
                            <p class="text-[11px] text-[#1A1018]/55 truncate" x-text="cartTotalCount + ' item(s) selected'"></p>
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
                            <p class="text-xs text-[#1A1018]/60 max-w-xs mx-auto">Explore delicious menus from verified local restaurants and add your cravings.</p>
                            <button @click="cartDrawerOpen = false" class="grail-btn-obsidian px-5 py-2.5 text-xs">
                                Browse Cravings →
                            </button>
                        </div>
                    </template>

                    <template x-for="item in cartItems" :key="item.id">
                        <div class="flex items-start justify-between gap-3 p-3.5 rounded-[18px] bg-white border border-[#E6E2E8]">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-3.5 rounded-xs border-2 flex items-center justify-center p-0.5" 
                                          :class="item.is_veg ? 'border-emerald-600' : 'border-rose-600'">
                                        <span class="w-1.5 h-1.5 rounded-full" :class="item.is_veg ? 'bg-emerald-600' : 'bg-rose-600'"></span>
                                    </span>
                                    <h5 class="text-xs font-semibold text-[#1A1018] truncate" x-text="item.title"></h5>
                                </div>
                                <div class="text-[11px] text-[#1A1018]/55 font-medium mt-0.5" x-text="item.restaurant_name"></div>
                                <div class="text-xs font-bold text-[#1A1018] mt-1" x-text="'₹' + item.price"></div>
                            </div>

                            <!-- Qty Controls -->
                            <div class="flex items-center gap-2 bg-[#FAFAF8] rounded-full border border-[#E6E2E8] px-2.5 py-1 shadow-2xs shrink-0">
                                <button type="button" @click="updateCartItem(item.id, -1)" class="text-[#1A1018]/60 hover:text-[#1A1018] font-black text-xs px-1 cursor-pointer">−</button>
                                <span class="text-xs font-bold text-[#1A1018]" x-text="item.quantity"></span>
                                <button type="button" @click="updateCartItem(item.id, 1)" class="text-[#EA580C] font-black text-xs px-1 cursor-pointer">+</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Drawer Footer Summary -->
                <div x-show="cartItems.length > 0" class="p-4 sm:p-6 border-t border-[#E6E2E8] bg-white space-y-3">
                    <div class="space-y-1.5 text-xs">
                        <div class="flex justify-between text-[#1A1018]/65">
                            <span>Item Subtotal</span>
                            <span class="font-semibold text-[#1A1018]" x-text="'₹' + cartSubtotal"></span>
                        </div>
                        <div class="flex justify-between text-[#1A1018]/65">
                            <span>Restaurant Delivery Fee</span>
                            <span class="font-semibold text-[#1A1018]" x-text="'₹' + cartDeliveryFee"></span>
                        </div>
                        <div class="flex justify-between text-[#1A1018]/65">
                            <span>Taxes &amp; Packaging</span>
                            <span class="font-semibold text-[#1A1018]" x-text="'₹' + cartTaxes"></span>
                        </div>
                        <div class="border-t border-[#E6E2E8] pt-2 flex justify-between text-sm font-bold text-[#1A1018]">
                            <span>To Pay</span>
                            <span x-text="'₹' + cartGrandTotal"></span>
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
            currentLocation: localStorage.getItem('k71_food_location') || 'Jaipur Central (Express Hub)',
            popularCities: [
                'Jaipur, Rajasthan',
                'Delhi NCR Hub',
                'Mumbai, Maharashtra',
                'Bengaluru, Karnataka',
                'Hyderabad, Telangana',
                'Lucknow, Uttar Pradesh',
                'Pune, Maharashtra',
                'Kolkata, West Bengal',
                'Chennai, Tamil Nadu',
                'Ahmedabad, Gujarat'
            ],
            cartItems: [],
            cartTotalCount: 0,
            cartSubtotal: 0,
            cartDeliveryFee: 40,
            cartTaxes: 15,
            cartGrandTotal: 0,
            checkoutUrl: '{{ route('cart.index') }}',

            init() {
                this.fetchCartData();
            },
            get filteredCities() {
                if (!this.locationSearch) return this.popularCities;
                return this.popularCities.filter(c => c.toLowerCase().includes(this.locationSearch.toLowerCase()));
            },
            setLocation(name) {
                this.currentLocation = name;
                localStorage.setItem('k71_food_location', name);
                window.dispatchEvent(new CustomEvent('food-location-changed', { detail: name }));
                if (window.showToast) {
                    window.showToast('Delivery zone updated to ' + name + ' 📍');
                }
            },
            detectGPS() {
                this.detectingGps = true;
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
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
                                const place = data.address?.suburb || data.address?.city_district || data.address?.city || data.address?.town || data.address?.state_district || 'Detected Area';
                                const state = data.address?.state ? ', ' + data.address?.state : '';
                                this.setLocation(`${place}${state}`);
                                this.detectingGps = false;
                                this.locationModalOpen = false;
                                return;
                            }
                        } catch (e) {
                            // Fallback to coordinates
                        }
                        this.setLocation('GPS Location (' + lat.toFixed(2) + ', ' + lon.toFixed(2) + ')');
                        this.detectingGps = false;
                        this.locationModalOpen = false;
                    },
                    (err) => {
                        this.setLocation('Jaipur Central (Express Hub)');
                        this.detectingGps = false;
                        this.locationModalOpen = false;
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
                                title: i.product_title || i.title || i.product?.title || 'Dish',
                                restaurant_name: i.vendor_name || i.restaurant_name || i.vendor?.shop_name || 'Kitchen Hub',
                                price: (i.unit_price ? i.unit_price / 100 : (i.price_at_add ? i.price_at_add / 100 : (i.price ? i.price : 199))),
                                quantity: i.quantity,
                                is_veg: i.is_veg !== undefined ? i.is_veg : true,
                                vendor_id: i.vendor_id
                            }));
                            this.cartTotalCount = data.count !== undefined ? data.count : this.cartItems.reduce((acc, it) => acc + it.quantity, 0);
                            this.cartSubtotal = data.subtotal ? (data.subtotal / 100) : this.cartItems.reduce((acc, it) => acc + (it.price * it.quantity), 0);
                            this.cartGrandTotal = this.cartSubtotal + this.cartDeliveryFee + this.cartTaxes;
                            if (this.cartItems.length > 0 && this.cartItems[0].vendor_id) {
                                this.checkoutUrl = '/checkout/' + this.cartItems[0].vendor_id;
                            } else {
                                this.checkoutUrl = '{{ route('cart.index') }}';
                            }
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
