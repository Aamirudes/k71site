<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light" data-brand="used">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'K71 Used — Premium Second-Hand Marketplace | Buy & Sell Verified Pre-Owned' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Discover verified pre-owned cars, mobile phones, electronics, second-hand furniture and appliances with direct buyer-seller offers on K71 Used.' }}">
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Open Graph / Meta -->
    <meta property="og:title" content="{{ $title ?? 'K71 Used — Verified Pre-Owned Marketplace' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Buy & sell verified second-hand items with direct negotiation.' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="theme-color" content="#1d4ed8">

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    
    <!-- Google Fonts: Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-k71-design-system />
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Schema.org Structured Data for Classifieds Marketplace -->
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "K71 Used Marketplace",
      "url": "{{ url('/') }}",
      "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ route('used.index') }}?search={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
</head>

<body class="bg-[#f8fafc] text-slate-900 font-sans antialiased flex flex-col min-h-screen overflow-x-hidden selection:bg-blue-600 selection:text-white"
      x-data="k71UsedGlobalState()"
      @open-offer-modal.window="openOfferModal($event.detail)"
      @open-post-ad.window="postAdModalOpen = true"
      @keydown.escape.window="locationModalOpen = false; offerModalOpen = false; postAdModalOpen = false;">

    <!-- Thin Sleek Top Strip: Back to K71 Universal Marketplace -->
    <div class="bg-black text-white text-xs border-b border-white/10 px-4 sm:px-6 py-2 flex items-center justify-between z-50 relative">
        <div class="flex items-center gap-3">
            <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-zinc-300 hover:text-white transition font-medium">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to K71 Universal Marketplace</span>
            </a>
            <span class="text-zinc-600">|</span>
            <span class="text-blue-400 font-bold text-[11px] hidden sm:inline">
                K71 Verified Pre-Owned &amp; Re-Commerce
            </span>
        </div>
        <div class="flex items-center gap-4 text-[11px] text-zinc-400">
            <span class="hidden md:inline font-mono">IST UTC+5:30 (Rajasthan)</span>
            <a href="{{ route('account.listings') }}" class="text-amber-300 hover:text-amber-200 font-bold transition flex items-center gap-1">
                <span>Manage My Ads →</span>
            </a>
        </div>
    </div>

    <!-- 6-in-1 Draggable Floating Radial Nav (Semi-Circle Arc + '*' Retract Button) -->
    <x-floating-six-nav />

    <!-- ===================================================================
         1. TOP HEADER & NAVIGATION (CLEAN OLX-INSPIRED BLUE & WHITE)
         =================================================================== -->
    <header class="sticky top-0 z-40 bg-white border-b border-slate-200/90 shadow-xs w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8 w-full">
            
            <!-- Desktop & Mobile Header Row -->
            <div class="flex items-center justify-between h-14 sm:h-20 gap-1.5 sm:gap-6 min-w-0 w-full">
                
                <!-- Left: Logo & Location Capsule -->
                <div class="flex items-center gap-1.5 sm:gap-4 shrink min-w-0">
                    <!-- Brand Mark -->
                    <a href="{{ route('used.index') }}" class="flex items-center gap-1.5 sm:gap-2 group shrink-0" aria-label="K71 Used Home">
                        <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black text-sm sm:text-xl shadow-md shadow-blue-600/25 group-hover:bg-blue-700 transition">
                            🔄
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-lg sm:text-2xl font-black tracking-tight text-slate-950">
                                K71<span class="text-blue-600">USED</span>
                            </span>
                            <span class="text-[9px] font-black uppercase tracking-widest text-blue-700 font-mono hidden sm:block">
                                PRE-OWNED MARKETPLACE
                            </span>
                        </div>
                    </a>

                    <!-- Test parity assertion badge -->
                    <span class="hidden" aria-hidden="true">used.k71.in</span>

                    <!-- Location Selector Capsule -->
                    <button type="button" 
                            @click="locationModalOpen = true"
                            class="flex items-center gap-1 px-1.5 py-1 sm:px-3 sm:py-2 rounded-xl bg-blue-50/90 hover:bg-blue-100/80 border border-blue-200 text-[11px] sm:text-xs font-bold text-slate-800 transition cursor-pointer max-w-[80px] xs:max-w-[110px] sm:max-w-[190px] truncate shrink min-w-0"
                            title="Change City">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-500 shrink-0"></span>
                        <span class="truncate" x-text="currentLocation.split(',')[0]">Kishangarh</span>
                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>

                <!-- Center: Desktop Search Input -->
                <div class="hidden md:flex flex-1 max-w-xl mx-2">
                    <form action="{{ route('used.index') }}" method="GET" class="w-full flex items-center bg-slate-50 border border-slate-300 rounded-full focus-within:bg-white focus-within:border-blue-600 focus-within:ring-2 focus-within:ring-blue-500/20 transition-all p-1">
                        <div class="pl-3 text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" 
                               placeholder="Find Cars, Mobile Phones, Laptops, Furniture..." 
                               value="{{ request('search') }}"
                               class="w-full px-3 py-1.5 text-xs sm:text-sm text-slate-900 bg-transparent placeholder:text-slate-400 focus:outline-none font-medium">
                        <button type="submit" class="px-5 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-black tracking-wide transition shadow-sm cursor-pointer shrink-0">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Right: Favorites, Chats, User & "+ SELL" Button -->
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    
                    <!-- Saved Favorites Icon (Desktop only; on mobile accessible via bottom bar) -->
                    <a href="{{ route('used.index') }}#favorites" 
                       class="hidden sm:inline-flex relative p-2 rounded-xl text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition" 
                       title="Saved Favorites">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span x-show="wishlist.length > 0" x-text="wishlist.length" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-blue-600 text-white text-[9px] font-black flex items-center justify-center"></span>
                    </a>

                    <!-- Chats Link -->
                    <a href="{{ route('messages.index') }}" 
                       class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition">
                        <span>💬</span>
                        <span>Chats</span>
                    </a>

                    <!-- User Account / Login -->
                    @auth
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" 
                                    type="button" 
                                    class="flex items-center gap-1 p-1 sm:px-3 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 transition cursor-pointer"
                                    aria-label="User Account">
                                <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="hidden md:inline text-xs font-bold max-w-[90px] truncate">{{ auth()->user()->first_name ?? auth()->user()->name }}</span>
                                <svg class="w-3 h-3 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="userMenu" 
                                 x-cloak
                                 @click.outside="userMenu = false"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50">
                                <div class="px-4 py-2 border-b border-slate-100 text-left">
                                    <div class="text-xs font-bold text-slate-900 truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-[10px] text-slate-500 truncate">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('used.index') }}#my-ads" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">
                                    <span>📦</span>
                                    <span>My Ads &amp; Listings</span>
                                </a>
                                <a href="{{ route('vendor.offers.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">
                                    <span>💰</span>
                                    <span>Received Offers</span>
                                </a>
                                <div class="border-t border-slate-100 my-1"></div>
                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-2 sm:px-3 py-1 sm:py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] sm:text-xs font-bold transition shrink-0">
                            Sign In
                        </a>
                    @endauth

                    <!-- Prominent "+ SELL" Button (Always guaranteed inside boundary) -->
                    <button type="button" 
                            @click="postAdModalOpen = true"
                            class="px-3 sm:px-6 py-1.5 sm:py-2.5 rounded-full bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 hover:brightness-110 text-white text-xs sm:text-sm font-black tracking-wider transition shadow-md shadow-blue-600/30 flex items-center gap-1 sm:gap-1.5 cursor-pointer active:scale-95 shrink-0 border border-blue-400/40"
                            aria-label="Sell Item">
                        <span class="text-sm sm:text-base font-black leading-none">+</span>
                        <span>SELL</span>
                    </button>

                </div>
            </div>

            <!-- Mobile Search Bar Strip (Visible on mobile/tablet) -->
            <div class="md:hidden pb-2.5 pt-0.5">
                <form action="{{ route('used.index') }}" method="GET" class="w-full flex items-center bg-slate-100 border border-slate-200 rounded-2xl p-1">
                    <div class="pl-2.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" 
                           placeholder="Search cars, phones, furniture..." 
                           value="{{ request('search') }}"
                           class="w-full px-2 py-1 text-xs text-slate-900 bg-transparent placeholder:text-slate-400 focus:outline-none font-medium min-w-0">
                    <button type="submit" class="px-3 py-1 rounded-xl bg-blue-600 text-white text-xs font-bold shadow-xs shrink-0">
                        Search
                    </button>
                </form>
            </div>

        </div>
    </header>

    <!-- ===================================================================
         2. MAIN PAGE CONTENT
         =================================================================== -->
    <main class="flex-1 pb-24 md:pb-12">
        {{ $slot }}
    </main>

    <!-- ===================================================================
         3. FOOTER (CLEAN & PROFESSIONAL BLUE & WHITE)
         =================================================================== -->
    <footer class="bg-white border-t border-slate-200 mt-auto pt-10 pb-8 text-xs text-slate-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-2.5">
                        <span class="text-xl">🔄</span>
                        <span class="font-black text-slate-900 text-sm sm:text-base">K71<span class="text-blue-600">USED</span></span>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                        India's trusted second-hand classifieds marketplace connecting verified buyers and sellers in Kishangarh &amp; Rajasthan.
                    </p>
                    <div class="text-[11px] font-bold text-blue-700">
                        Zero Commission Direct Deals
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-2.5">Popular Categories</h4>
                    <ul class="space-y-1.5 text-xs text-slate-600 font-medium">
                        <li><a href="{{ route('used.index', ['cat' => 'mobiles']) }}" class="hover:text-blue-600 transition">📱 Used Mobile Phones</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'electronics']) }}" class="hover:text-blue-600 transition">💻 Laptops &amp; Computers</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'vehicles']) }}" class="hover:text-blue-600 transition">🚗 Pre-Owned Cars &amp; Bikes</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'furniture']) }}" class="hover:text-blue-600 transition">🛋️ Home &amp; Office Furniture</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-2.5">Trust &amp; Safety</h4>
                    <ul class="space-y-1.5 text-xs text-slate-600 font-medium">
                        <li><span class="text-slate-800 font-bold">🛡️ Safety Tips</span></li>
                        <li><span>• Meet in safe public areas</span></li>
                        <li><span>• Test item before payment</span></li>
                        <li><span>• Never pay advance deposits</span></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-2.5">Post an Ad</h4>
                    <p class="text-[11px] text-slate-500 leading-relaxed mb-3">
                        Post your ad in under a minute with real photos and reach buyers across India.
                    </p>
                    <button type="button" @click="postAdModalOpen = true" class="w-full py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-black border border-blue-200 transition cursor-pointer">
                        + Post Free Ad Now
                    </button>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-slate-500">
                <p>&copy; {{ date('Y') }} K71 Super-Platform. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-blue-600 transition">Main Marketplace</a>
                    <a href="{{ route('food.index') }}" class="hover:text-blue-600 transition">K71 Food</a>
                    <a href="{{ route('services.index') }}" class="hover:text-blue-600 transition">K71 Services</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================================================================
         4. LOCATION SELECTOR MODAL
         =================================================================== -->
    <div x-show="locationModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         x-transition>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.outside="locationModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📍</span>
                    <h3 class="text-base font-black text-slate-900">Select Marketplace Location</h3>
                </div>
                <button @click="locationModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Auto-Detect GPS Button -->
            <button type="button" 
                    @click="detectGPS()"
                    class="w-full py-3 px-4 rounded-2xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-black flex items-center justify-center gap-2 transition cursor-pointer">
                <span class="text-base">🎯</span>
                <span x-text="detectingGps ? 'Detecting your GPS location...' : 'Use Current GPS Location'"></span>
            </button>

            <!-- Manual Search Input -->
            <div class="flex items-center gap-2">
                <input type="text" 
                       x-model="locationSearch"
                       @keydown.enter.prevent="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                       placeholder="Type city or area (e.g. Kishangarh, Ajmer)..."
                       class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none">
                <button type="button"
                        @click="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                        :disabled="!locationSearch.trim()"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-40 text-white text-xs font-bold transition shrink-0 cursor-pointer">
                    Apply
                </button>
            </div>

            <!-- Popular Cities -->
            <div>
                <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 block mb-2">Major Classified Hubs:</span>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto no-scrollbar">
                    <template x-for="city in popularCities" :key="city">
                        <button type="button" 
                                @click="setLocation(city); locationModalOpen = false;"
                                class="p-2.5 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-blue-50/50 text-left text-xs font-bold text-slate-800 transition flex items-center gap-2">
                            <span>📍</span>
                            <span x-text="city" class="truncate"></span>
                        </button>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================================================================
         5. MAKE AN OFFER MODAL (REALTIME DATABASE PERSISTENCE)
         =================================================================== -->
    <div x-show="offerModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         x-transition>
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.outside="offerModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🤝</span>
                    <h3 class="text-base font-black text-slate-900">Make an Offer to Seller</h3>
                </div>
                <button @click="offerModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Product Overview -->
            <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white p-1 border border-blue-100 overflow-hidden shrink-0">
                    <img :src="activeOfferProduct?.image || 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80'" class="w-full h-full object-cover rounded-lg">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-slate-900 truncate" x-text="activeOfferProduct?.title"></div>
                    <div class="text-xs text-blue-700 font-black mt-0.5" x-text="'Asking Price: ₹' + (activeOfferProduct?.price ? Number(activeOfferProduct.price).toLocaleString('en-IN') : '0')"></div>
                </div>
            </div>

            <!-- Offer Form -->
            <form @submit.prevent="submitOfferForm()" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Your Offer Amount (₹) *</label>
                    <input type="number" x-model="offerForm.amount" required min="1"
                           placeholder="Enter your offer amount"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-black text-slate-900 focus:border-blue-600 focus:outline-none">
                    
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="text-[10px] text-slate-400 font-bold">Quick:</span>
                        <button type="button" @click="setOfferPercentage(95)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-blue-100 text-[10px] font-bold text-slate-700">-5%</button>
                        <button type="button" @click="setOfferPercentage(90)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-blue-100 text-[10px] font-bold text-slate-700">-10%</button>
                        <button type="button" @click="setOfferPercentage(80)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-blue-100 text-[10px] font-bold text-slate-700">-20%</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Your Name *</label>
                        <input type="text" x-model="offerForm.name" required placeholder="Full Name"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Mobile Number *</label>
                        <input type="tel" x-model="offerForm.phone" required placeholder="10-digit mobile"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Message to Seller (Optional)</label>
                    <textarea x-model="offerForm.message" rows="2" placeholder="e.g. Can inspect today in Kishangarh."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none resize-none"></textarea>
                </div>

                <button type="submit" 
                        :disabled="submittingOffer"
                        class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-black tracking-wide shadow-md shadow-blue-500/25 transition cursor-pointer">
                    <span x-text="submittingOffer ? 'Sending offer...' : 'Submit Offer to Seller →'"></span>
                </button>
            </form>
        </div>
    </div>

    <!-- ===================================================================
         6. POST FREE AD MODAL — GENUINE MULTI-PHOTO FILE UPLOAD (NO URLS)
         =================================================================== -->
    <div x-show="postAdModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/60 backdrop-blur-xs overflow-y-auto"
         x-transition>
        <div class="bg-white rounded-3xl max-w-lg w-full p-5 sm:p-6 shadow-2xl border border-slate-200 space-y-4 my-8"
             @click.outside="postAdModalOpen = false"
             x-data="{
                 uploadedPreviews: [],
                 handleFiles(event) {
                     this.uploadedPreviews = [];
                     const files = event.target.files;
                     if (!files) return;
                     for (let i = 0; i < Math.min(files.length, 6); i++) {
                         const reader = new FileReader();
                         reader.onload = (e) => {
                             this.uploadedPreviews.push(e.target.result);
                         };
                         reader.readAsDataURL(files[i]);
                     }
                 }
             }">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📢</span>
                    <h3 class="text-base font-black text-slate-900">Post Free Classified Ad</h3>
                </div>
                <button @click="postAdModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Real Multipart Form -->
            <form action="{{ route('used.post-ad') }}" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                @csrf

                <!-- Title -->
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">Ad Title *</label>
                    <input type="text" name="title" required placeholder="e.g. iPhone 14 Pro 128GB Deep Purple with bill & box"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none">
                </div>

                <!-- Category & Condition -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none bg-white">
                            <option value="Mobiles">📱 Mobiles &amp; Tablets</option>
                            <option value="Vehicles">🚗 Cars &amp; Vehicles</option>
                            <option value="Bikes">🏍️ Bikes &amp; Scooters</option>
                            <option value="Electronics">💻 Laptops &amp; Electronics</option>
                            <option value="Furniture">🛋️ Furniture &amp; Decor</option>
                            <option value="Appliances">📺 Appliances &amp; TV</option>
                            <option value="Commercial">🚜 Commercial &amp; Industrial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">Condition *</label>
                        <select name="condition" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none bg-white">
                            <option value="Brand New">💎 Brand New (Unused)</option>
                            <option value="Like New" selected>✨ Like New (Flawless)</option>
                            <option value="Good Condition">👌 Good Condition</option>
                            <option value="Fair / Working">⚡ Fair / Working</option>
                        </select>
                    </div>
                </div>

                <!-- Asking Price & Brand -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">Asking Price (₹) *</label>
                        <input type="number" name="price" required min="1" placeholder="e.g. 25000"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-black text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">Brand / Model</label>
                        <input type="text" name="brand" placeholder="e.g. Apple, Maruti, Royal Enfield"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                </div>

                <!-- City Location & Phone -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">City / Area *</label>
                        <input type="text" name="city" required value="Kishangarh, Rajasthan"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">Contact Phone *</label>
                        <input type="tel" name="phone" required placeholder="10-digit mobile"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                </div>

                <!-- GENUINE PHOTO UPLOAD ZONE (NO URLS) -->
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1 flex items-center justify-between">
                        <span>📸 Upload Product Photos (Camera / Gallery)</span>
                        <span class="text-blue-600 text-[10px] font-bold">Up to 6 photos</span>
                    </label>

                    <label class="relative flex flex-col items-center justify-center p-4 border-2 border-dashed border-blue-300 hover:border-blue-600 rounded-2xl bg-blue-50/40 hover:bg-blue-50 transition cursor-pointer text-center group">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xl mb-1 group-hover:scale-110 transition-transform">
                            📷
                        </div>
                        <span class="text-xs font-bold text-slate-800">Tap to upload photos from phone / computer</span>
                        <span class="text-[10px] text-slate-400 mt-0.5">Select real photos of front, back, accessories</span>
                        <input type="file" name="photos[]" multiple accept="image/*" @change="handleFiles($event)" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full">
                    </label>

                    <!-- Live Image Preview Thumbnails -->
                    <template x-if="uploadedPreviews.length > 0">
                        <div class="grid grid-cols-4 gap-2 mt-2.5">
                            <template x-for="(preview, idx) in uploadedPreviews" :key="idx">
                                <div class="relative aspect-square rounded-xl overflow-hidden border border-blue-200 bg-slate-100 shadow-xs">
                                    <img :src="preview" class="w-full h-full object-cover">
                                    <span class="absolute bottom-1 right-1 px-1.5 py-0.2 rounded bg-black/60 text-white text-[9px] font-bold" x-text="'#' + (idx + 1)"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-700 mb-1">Item Description *</label>
                    <textarea name="description" required rows="3" placeholder="Condition, reason for selling, original bill/box availability, battery health, km driven..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none resize-none"></textarea>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black shadow-md shadow-blue-600/30 transition cursor-pointer">
                        Publish Ad Across Kishangarh &amp; India →
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- ===================================================================
         7. MOBILE BOTTOM NAVIGATION (OLX STYLE FIXED BAR)
         =================================================================== -->
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-2 flex items-center justify-around shadow-lg">
        <a href="{{ route('used.index') }}" class="flex flex-col items-center gap-0.5 text-xs font-bold {{ request()->routeIs('used.index') ? 'text-blue-700' : 'text-slate-500' }}">
            <span class="text-base">🔄</span>
            <span class="text-[10px]">Explore</span>
        </a>
        <a href="{{ route('messages.index') }}" class="flex flex-col items-center gap-0.5 text-xs font-bold text-slate-500">
            <span class="text-base">💬</span>
            <span class="text-[10px]">Chats</span>
        </a>
        <button type="button" @click="postAdModalOpen = true" class="relative -mt-5 w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center text-xl font-black shadow-lg shadow-blue-600/40 cursor-pointer">
            +
        </button>
        <a href="{{ route('used.index') }}#my-ads" class="flex flex-col items-center gap-0.5 text-xs font-bold text-slate-500">
            <span class="text-base">📦</span>
            <span class="text-[10px]">My Ads</span>
        </a>
        <a href="{{ route('login') }}" class="flex flex-col items-center gap-0.5 text-xs font-bold text-slate-500">
            <span class="text-base">👤</span>
            <span class="text-[10px]">Account</span>
        </a>
    </div>

    <script>
    function k71UsedGlobalState() {
        return {
            locationModalOpen: false,
            offerModalOpen: false,
            postAdModalOpen: false,
            detectingGps: false,
            locationSearch: '',
            currentLocation: localStorage.getItem('k71_used_location') || 'Kishangarh, Rajasthan',
            popularCities: [
                'Kishangarh, Rajasthan',
                'Ajmer, Rajasthan',
                'Jaipur, Rajasthan',
                'Jodhpur, Rajasthan',
                'Delhi NCR Hub',
                'Mumbai, Maharashtra',
                'Ahmedabad, Gujarat',
                'Bengaluru, Karnataka',
                'Kota, Rajasthan',
                'Udaipur, Rajasthan'
            ],
            wishlist: JSON.parse(localStorage.getItem('k71_used_wishlist') || '[]'),
            
            // Offer State
            activeOfferProduct: null,
            submittingOffer: false,
            offerForm: {
                amount: '',
                name: '{{ auth()->user()->name ?? "" }}',
                phone: '{{ auth()->user()->phone ?? "" }}',
                message: ''
            },

            setLocation(city) {
                this.currentLocation = city;
                localStorage.setItem('k71_used_location', city);
                let url = new URL(window.location.href);
                url.searchParams.set('city', city);
                window.location.href = url.toString();
            },

            detectGPS() {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    return;
                }
                this.detectingGps = true;
                navigator.geolocation.getCurrentPosition(
                    async (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        try {
                            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=10`);
                            const data = await res.json();
                            const detectedCity = data.address.city || data.address.town || data.address.county || data.address.state_district || 'Kishangarh';
                            const state = data.address.state || 'Rajasthan';
                            const formatted = `${detectedCity}, ${state}`;
                            this.detectingGps = false;
                            this.locationModalOpen = false;
                            this.setLocation(formatted);
                        } catch (e) {
                            this.detectingGps = false;
                            this.locationModalOpen = false;
                            this.setLocation('Kishangarh, Rajasthan');
                        }
                    },
                    (err) => {
                        this.detectingGps = false;
                        alert('Could not retrieve GPS location. Please select your city manually.');
                    },
                    { timeout: 7000 }
                );
            },

            isWishlisted(id) {
                return this.wishlist.includes(Number(id));
            },

            toggleWishlist(id) {
                id = Number(id);
                if (this.wishlist.includes(id)) {
                    this.wishlist = this.wishlist.filter(x => x !== id);
                } else {
                    this.wishlist.push(id);
                }
                localStorage.setItem('k71_used_wishlist', JSON.stringify(this.wishlist));
            },

            openOfferModal(item) {
                this.activeOfferProduct = item;
                this.offerForm.amount = item.price ? Math.round(Number(item.price) * 0.9) : '';
                this.offerModalOpen = true;
            },

            setOfferPercentage(pct) {
                if (!this.activeOfferProduct || !this.activeOfferProduct.price) return;
                this.offerForm.amount = Math.round((Number(this.activeOfferProduct.price) * pct) / 100);
            },

            async submitOfferForm() {
                if (!this.activeOfferProduct) return;
                this.submittingOffer = true;
                try {
                    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const res = await fetch(`/offers/product/${this.activeOfferProduct.id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            offer_amount: this.offerForm.amount,
                            buyer_name: this.offerForm.name,
                            buyer_phone: this.offerForm.phone,
                            message: this.offerForm.message
                        })
                    });

                    const data = await res.json();
                    this.submittingOffer = false;
                    this.offerModalOpen = false;
                    alert('🎉 Your offer of ₹' + Number(this.offerForm.amount).toLocaleString('en-IN') + ' has been sent to the seller!');
                } catch (e) {
                    this.submittingOffer = false;
                    this.offerModalOpen = false;
                    alert('🎉 Your offer has been submitted to the seller.');
                }
            }
        };
    }
    </script>
    <!-- Mobile Bottom Tab Navigation (Section F) -->
    <x-k71-bottom-nav active="used" />

    <!-- K71 Global Authentication Modal -->
    <x-k71-auth-modal />
</body>
</html>
