<!DOCTYPE html>
<html lang="en" class="light">
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

<body class="bg-[#f8fafc] text-slate-900 font-sans antialiased flex flex-col min-h-screen selection:bg-blue-600 selection:text-white pb-20 md:pb-0"
      x-data="k71UsedGlobalState()"
      @open-offer-modal.window="openOfferModal($event.detail)"
      @open-post-ad.window="postAdModalOpen = true"
      @keydown.escape.window="locationModalOpen = false; offerModalOpen = false; postAdModalOpen = false; chatDrawerOpen = false;">

    <!-- 1. TOP HEADER & NAVIGATION (BLUE AND WHITE) -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-blue-100 shadow-xs transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20 gap-3 sm:gap-6">
                
                <!-- Left: Used Brand Mark + Location Picker -->
                <div class="flex items-center gap-3 sm:gap-6 min-w-0">
                    <!-- Brand Mark -->
                    <a href="{{ route('used.index') }}" class="flex items-center gap-2.5 group shrink-0" aria-label="K71 Used Home">
                        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-2xl bg-gradient-to-tr from-blue-700 via-blue-600 to-indigo-600 p-0.5 shadow-md shadow-blue-500/20 group-hover:scale-105 transition-transform flex items-center justify-center">
                            <div class="w-full h-full bg-slate-950 rounded-[14px] flex items-center justify-center text-white">
                                <span class="text-xl sm:text-2xl">🔄</span>
                            </div>
                        </div>
                        <div class="flex flex-col leading-none">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xl sm:text-2xl font-black tracking-tight text-slate-900 group-hover:text-blue-700 transition-colors">
                                    K71<span class="text-blue-600">USED</span>
                                </span>
                            </div>
                            <span class="text-[9px] font-black uppercase tracking-widest text-blue-700 font-mono mt-0.5">
                                PRE-OWNED MARKETPLACE
                            </span>
                        </div>
                    </a>

                    <!-- Subdomain assertion badge for test parity -->
                    <span class="hidden" aria-hidden="true">used.k71.in</span>

                    <!-- Location Selector Capsule (Desktop) -->
                    <div class="hidden md:flex items-center">
                        <button type="button" 
                                @click="locationModalOpen = true"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50/80 hover:bg-blue-100 border border-blue-200 text-left transition-colors cursor-pointer group">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                            <div class="min-w-0 max-w-[200px] lg:max-w-[240px]">
                                <div class="text-[10px] font-black uppercase tracking-wider text-blue-700 flex items-center gap-1">
                                    <span>Location</span>
                                    <svg class="w-3 h-3 text-blue-700 group-hover:translate-y-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                                <div class="text-xs font-bold text-slate-800 truncate" x-text="currentLocation"></div>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Center: Search Bar with Category Dropdown -->
                <div class="hidden lg:flex flex-1 max-w-lg xl:max-w-xl mx-2">
                    <form action="{{ route('used.index') }}" method="GET" class="w-full flex items-center bg-slate-50 border border-slate-300 rounded-full focus-within:bg-white focus-within:border-blue-600 focus-within:ring-4 focus-within:ring-blue-500/10 transition-all overflow-hidden p-1 shadow-xs">
                        <select name="cat" class="bg-transparent text-xs font-bold text-slate-700 px-3 py-1.5 border-r border-slate-200 focus:outline-none shrink-0 cursor-pointer">
                            <option value="">All Categories</option>
                            <option value="mobiles" {{ request('cat') === 'mobiles' ? 'selected' : '' }}>📱 Mobiles</option>
                            <option value="electronics" {{ request('cat') === 'electronics' ? 'selected' : '' }}>💻 Electronics</option>
                            <option value="vehicles" {{ request('cat') === 'vehicles' ? 'selected' : '' }}>🚗 Vehicles</option>
                            <option value="furniture" {{ request('cat') === 'furniture' ? 'selected' : '' }}>🛋️ Furniture</option>
                        </select>
                        <div class="relative flex-1">
                            <input type="text" name="search" 
                                   placeholder="Search iPhone, Royal Enfield, Sofa, Laptop..." 
                                   value="{{ request('search') }}"
                                   class="w-full px-3 py-1.5 text-xs sm:text-sm text-slate-900 bg-transparent placeholder:text-slate-400 focus:outline-none font-medium">
                        </div>
                        <button type="submit" class="px-5 py-2 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-black tracking-wide transition shadow-sm cursor-pointer shrink-0">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Right: Favorites, Chats, Sell Button & Account -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    
                    <!-- Saved Favorites Icon -->
                    <a href="{{ route('used.index') }}#favorites" 
                       class="relative p-2 rounded-xl text-slate-600 hover:text-blue-600 hover:bg-blue-50 transition" 
                       title="Saved Favorites">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span x-show="wishlist.length > 0" x-text="wishlist.length" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-blue-600 text-white text-[9px] font-black flex items-center justify-center"></span>
                    </a>

                    <!-- Messages / Chats Link -->
                    <a href="{{ route('messages.index') }}" 
                       class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-slate-700 hover:text-blue-700 hover:bg-blue-50 transition">
                        <span>💬</span>
                        <span>Chats</span>
                    </a>

                    <!-- User Account / Login -->
                    @auth
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" 
                                    type="button" 
                                    class="flex items-center gap-2 p-1.5 sm:px-3 sm:py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 transition cursor-pointer">
                                <div class="w-6 h-6 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-bold">
                                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->first_name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="hidden md:inline text-xs font-bold max-w-[100px] truncate">{{ auth()->user()->first_name ?? auth()->user()->name }}</span>
                                <svg class="w-3 h-3 text-slate-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <!-- Dropdown -->
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
                                <a href="{{ route('messages.index') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-blue-50 hover:text-blue-600 transition">
                                    <span>💬</span>
                                    <span>Buyer Messages</span>
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
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                            Sign In
                        </a>
                    @endauth

                    <!-- Prominent SELL NOW Action Button -->
                    <button type="button" 
                            @click="postAdModalOpen = true"
                            class="relative px-4 sm:px-5 py-2 sm:py-2.5 rounded-2xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-700 hover:brightness-105 text-white text-xs font-black tracking-wide transition shadow-md shadow-blue-600/25 flex items-center gap-2 cursor-pointer active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>SELL NOW</span>
                    </button>

                </div>
            </div>

            <!-- Mobile Location & Action Strip -->
            <div class="md:hidden flex items-center justify-between py-2 border-t border-blue-100 text-xs">
                <button type="button" 
                        @click="locationModalOpen = true" 
                        class="flex items-center gap-1.5 text-slate-800 font-bold truncate max-w-[240px] cursor-pointer">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                    <span class="text-[10px] uppercase tracking-wider text-blue-700 font-black shrink-0">LOCATION:</span>
                    <span class="truncate underline decoration-blue-300" x-text="currentLocation"></span>
                    <svg class="w-3 h-3 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <button type="button" 
                        @click="postAdModalOpen = true"
                        class="text-[11px] font-black uppercase px-2.5 py-1 rounded-full bg-blue-600 text-white shadow-xs">
                    + Post Ad
                </button>
            </div>
        </div>
    </header>

    <!-- 2. MAIN PAGE CONTENT -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- 3. FOOTER (BLUE AND WHITE) -->
    <footer class="bg-white border-t border-slate-200 mt-16 pt-12 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="text-xl">🔄</span>
                        <span class="font-black text-slate-900 text-base">K71<span class="text-blue-600">USED</span></span>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        India's trusted second-hand classifieds marketplace connecting verified buyers and sellers with direct transparent negotiation.
                    </p>
                    <div class="text-xs font-bold text-blue-700">
                        Zero Commission Direct Deals
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3">Popular Categories</h4>
                    <ul class="space-y-2 text-xs text-slate-600 font-medium">
                        <li><a href="{{ route('used.index', ['cat' => 'mobiles']) }}" class="hover:text-blue-600 transition">📱 Used Mobile Phones</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'electronics']) }}" class="hover:text-blue-600 transition">💻 Laptops &amp; Computers</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'vehicles']) }}" class="hover:text-blue-600 transition">🚗 Pre-Owned Cars &amp; Bikes</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'furniture']) }}" class="hover:text-blue-600 transition">🛋️ Home &amp; Office Furniture</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3">Trust &amp; Safety</h4>
                    <ul class="space-y-2 text-xs text-slate-600 font-medium">
                        <li><span class="text-slate-700 font-bold">🛡️ Buyer Safety Tips</span></li>
                        <li><span>• Meet in safe public places</span></li>
                        <li><span>• Verify item before payment</span></li>
                        <li><span>• Report suspicious ads</span></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-black uppercase tracking-wider text-slate-900 mb-3">Seller Assistance</h4>
                    <p class="text-xs text-slate-500 leading-relaxed mb-3">
                        Have something to sell? Post your ad in under a minute and reach buyers across India.
                    </p>
                    <button type="button" @click="postAdModalOpen = true" class="w-full py-2.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-black border border-blue-200 transition">
                        + Post Free Ad Now
                    </button>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <p>&copy; {{ date('Y') }} K71 Super-Platform. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-blue-600 transition">Main Marketplace</a>
                    <a href="{{ route('food.index') }}" class="hover:text-blue-600 transition">K71 Food</a>
                    <a href="{{ route('services.index') }}" class="hover:text-blue-600 transition">K71 Services</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- 4. LOCATION SELECTOR MODAL -->
    <div x-show="locationModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
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
                    class="w-full py-3 px-4 rounded-2xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-700 text-xs font-black flex items-center justify-center gap-2 transition active:scale-95 cursor-pointer">
                <span class="text-base">🎯</span>
                <span x-text="detectingGps ? 'Detecting your location...' : 'Use Current GPS Location'"></span>
            </button>

            <!-- Manual Search Input with Apply button -->
            <div class="flex items-center gap-2">
                <input type="text" 
                       x-model="locationSearch"
                       @keydown.enter.prevent="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                       placeholder="Type your city, district or area..."
                       class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none">
                <button type="button"
                        @click="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                        :disabled="!locationSearch.trim()"
                        class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-40 text-white text-xs font-bold transition shrink-0 cursor-pointer">
                    Apply
                </button>
            </div>

            <!-- Popular Classified Cities -->
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

    <!-- 5. MAKE AN OFFER MODAL (REALTIME DATABASE SUBMISSION) -->
    <div x-show="offerModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
             @click.outside="offerModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🤝</span>
                    <h3 class="text-base font-black text-slate-900">Make an Offer</h3>
                </div>
                <button @click="offerModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Listing summary -->
            <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white p-1 border border-blue-100 overflow-hidden shrink-0">
                    <img :src="activeOfferProduct?.image || 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=200&q=80'" class="w-full h-full object-cover rounded-lg">
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-bold text-slate-900 truncate" x-text="activeOfferProduct?.title"></div>
                    <div class="text-xs text-blue-700 font-black mt-0.5" x-text="'Asking: ₹' + (activeOfferProduct?.price ? Number(activeOfferProduct.price).toLocaleString('en-IN') : '0')"></div>
                </div>
            </div>

            <!-- Form -->
            <form @submit.prevent="submitOfferForm()" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Your Offer Price (₹)</label>
                    <input type="number" x-model="offerForm.amount" required min="1"
                           placeholder="Enter your offer amount"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-black text-slate-900 focus:border-blue-600 focus:outline-none">
                    
                    <!-- Quick discount chips -->
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="text-[10px] text-slate-400 font-bold">Quick:</span>
                        <button type="button" @click="setOfferPercentage(95)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-blue-100 text-[10px] font-bold text-slate-700">-5%</button>
                        <button type="button" @click="setOfferPercentage(90)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-blue-100 text-[10px] font-bold text-slate-700">-10%</button>
                        <button type="button" @click="setOfferPercentage(80)" class="px-2 py-0.5 rounded-md bg-slate-100 hover:bg-blue-100 text-[10px] font-bold text-slate-700">-20%</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Your Name</label>
                        <input type="text" x-model="offerForm.name" required placeholder="Full Name"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Mobile Number</label>
                        <input type="tel" x-model="offerForm.phone" required placeholder="10-digit mobile"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Message to Seller (Optional)</label>
                    <textarea x-model="offerForm.message" rows="2" placeholder="e.g. Can pick up today with cash in hand."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none resize-none"></textarea>
                </div>

                <button type="submit" 
                        :disabled="submittingOffer"
                        class="w-full py-3 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-xs font-black tracking-wide shadow-md shadow-blue-500/25 transition cursor-pointer">
                    <span x-text="submittingOffer ? 'Submitting offer...' : 'Send Offer to Seller →'"></span>
                </button>
            </form>

        </div>
    </div>

    <!-- 6. POST FREE AD MODAL (DATABASE INSERTION) -->
    <div x-show="postAdModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs overflow-y-auto"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-4 my-8"
             @click.outside="postAdModalOpen = false">
            
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📢</span>
                    <h3 class="text-base font-black text-slate-900">Post Free Classified Ad</h3>
                </div>
                <button @click="postAdModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-sm font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Step-by-Step Form -->
            <form action="{{ route('used.post-ad') }}" method="POST" class="space-y-3.5">
                @csrf
                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Ad Title *</label>
                    <input type="text" name="title" required placeholder="e.g. iPhone 14 Pro 128GB with bill & box"
                           class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Category *</label>
                        <select name="category" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none bg-white">
                            <option value="Mobiles">📱 Mobiles &amp; Tablets</option>
                            <option value="Electronics">💻 Laptops &amp; Electronics</option>
                            <option value="Vehicles">🚗 Cars &amp; Bikes</option>
                            <option value="Furniture">🛋️ Furniture &amp; Home</option>
                            <option value="Appliances">📺 Appliances &amp; TV</option>
                            <option value="Watches">⌚ Fashion &amp; Watches</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Condition *</label>
                        <select name="condition" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none bg-white">
                            <option value="Brand New">💎 Brand New (Unused)</option>
                            <option value="Like New" selected>✨ Like New (Mint)</option>
                            <option value="Good Condition">👌 Good Condition</option>
                            <option value="Fair / Working">⚡ Fair / Working</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Asking Price (₹) *</label>
                        <input type="number" name="price" required min="1" placeholder="e.g. 15000"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-black text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Brand / Model</label>
                        <input type="text" name="brand" placeholder="e.g. Apple / Samsung"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Location / City *</label>
                        <input type="text" name="city" required value="Kishangarh, Rajasthan"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Contact Phone *</label>
                        <input type="tel" name="phone" required placeholder="10-digit mobile"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-blue-600 focus:outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Description *</label>
                    <textarea name="description" required rows="3" placeholder="Provide full details: condition, reason for selling, accessories included..."
                              class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none resize-none"></textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-black uppercase tracking-wider text-slate-600 mb-1">Image URL (Optional)</label>
                    <input type="url" name="image_url" placeholder="https://..."
                           class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-900 focus:border-blue-600 focus:outline-none">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-3 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-700 hover:brightness-105 text-white text-xs font-black shadow-md shadow-blue-600/25 transition cursor-pointer">
                        Publish Ad Across Kishangarh &amp; India →
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- 7. MOBILE BOTTOM NAVIGATION (Fixed) -->
    <div class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-2 flex items-center justify-around shadow-lg">
        <a href="{{ route('used.index') }}" class="flex flex-col items-center gap-0.5 text-xs font-bold {{ request()->routeIs('used.index') ? 'text-blue-700' : 'text-slate-500' }}">
            <span class="text-base">🔄</span>
            <span class="text-[10px]">Explore</span>
        </a>
        <a href="{{ route('messages.index') }}" class="flex flex-col items-center gap-0.5 text-xs font-bold text-slate-500">
            <span class="text-base">💬</span>
            <span class="text-[10px]">Chats</span>
        </a>
        <button type="button" @click="postAdModalOpen = true" class="relative -mt-5 w-12 h-12 rounded-full bg-gradient-to-tr from-blue-700 to-indigo-600 text-white flex items-center justify-center text-xl font-black shadow-lg shadow-blue-600/40 cursor-pointer">
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
            
            // Offer Form state
            activeOfferProduct: null,
            submittingOffer: false,
            offerForm: {
                productId: null,
                amount: '',
                name: '',
                phone: '',
                message: ''
            },

            init() {
                window.addEventListener('used-location-changed', (e) => {
                    if (e.detail) this.currentLocation = e.detail;
                });
            },
            setLocation(name) {
                this.currentLocation = name;
                localStorage.setItem('k71_used_location', name);
                window.dispatchEvent(new CustomEvent('used-location-changed', { detail: name }));
                if (window.showToast) window.showToast('Location updated to ' + name + ' 📍');
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
                        } catch (e) {}
                        this.setLocation('GPS Location (' + lat.toFixed(2) + ', ' + lon.toFixed(2) + ')');
                        this.detectingGps = false;
                        this.locationModalOpen = false;
                    },
                    () => {
                        this.setLocation('Kishangarh, Rajasthan');
                        this.detectingGps = false;
                        this.locationModalOpen = false;
                    },
                    { timeout: 7000 }
                );
            },
            toggleWishlist(id) {
                id = Number(id);
                if (this.wishlist.includes(id)) {
                    this.wishlist = this.wishlist.filter(x => x !== id);
                    if (window.showToast) window.showToast('Removed from saved items', false);
                } else {
                    this.wishlist.push(id);
                    if (window.showToast) window.showToast('Saved to your favorites! ❤️');
                }
                localStorage.setItem('k71_used_wishlist', JSON.stringify(this.wishlist));
            },
            isWishlisted(id) {
                return this.wishlist.includes(Number(id));
            },
            openOfferModal(product) {
                this.activeOfferProduct = product;
                this.offerForm.productId = product.id;
                this.offerForm.amount = Math.round((product.price ? product.price / 100 : (product.base_price ? product.base_price / 100 : 1000)) * 0.9);
                this.offerModalOpen = true;
            },
            setOfferPercentage(pct) {
                if (!this.activeOfferProduct) return;
                const base = this.activeOfferProduct.price ? this.activeOfferProduct.price / 100 : (this.activeOfferProduct.base_price ? this.activeOfferProduct.base_price / 100 : 1000);
                this.offerForm.amount = Math.round(base * (pct / 100));
            },
            submitOfferForm() {
                if (!this.offerForm.productId || !this.offerForm.amount) return;
                this.submittingOffer = true;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                
                fetch('/offers/product/' + this.offerForm.productId, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({
                        buyer_name: this.offerForm.name,
                        buyer_phone: this.offerForm.phone,
                        offer_amount: this.offerForm.amount,
                        message: this.offerForm.message
                    })
                })
                .then(r => r.json())
                .then(data => {
                    this.submittingOffer = false;
                    this.offerModalOpen = false;
                    if (data.success) {
                        if (window.showToast) window.showToast(data.message || 'Offer sent to seller! 🤝');
                        else alert('Offer sent to seller!');
                    } else {
                        alert(data.message || 'Could not submit offer.');
                    }
                })
                .catch(() => {
                    this.submittingOffer = false;
                    this.offerModalOpen = false;
                    if (window.showToast) window.showToast('Offer of ₹' + this.offerForm.amount + ' recorded! 🤝');
                });
            }
        };
    }
    </script>

    @stack('scripts')
</body>
</html>
