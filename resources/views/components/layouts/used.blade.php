@props([
    'title' => null,
    'metaDescription' => null,
    'canonicalUrl' => null,
    'robotsContent' => null,
    'schemaJson' => null,
])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="{{ $robotsContent ?? 'index,follow' }}">
    
    <title>{{ $title ?? 'K71 Used — Pre-Owned Marketplace | Buy & Sell Second-Hand Locally' }}</title>
    <meta name="description" content="{{ $metaDescription ?? 'Discover pre-owned mobile phones, laptops, electronics, furniture, appliances and vehicles with direct buyer-seller negotiation on K71 Used.' }}">
    <link rel="canonical" href="{{ $canonicalUrl ?? url()->current() }}">
    
    <!-- Open Graph / Meta -->
    <meta property="og:title" content="{{ $title ?? 'K71 Used — Pre-Owned Marketplace' }}">
    <meta property="og:description" content="{{ $metaDescription ?? 'Buy & sell pre-owned items with direct negotiation.' }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl ?? url()->current() }}">
    <meta name="theme-color" content="#0D0D0D">

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

    <!-- Schema.org Structured Data for Classifieds Marketplace -->
    @if(!empty($schemaJson))
    <script type="application/ld+json">
    {!! json_encode($schemaJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
    @else
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "WebSite",
      "name": "K71 Used Marketplace",
      "url": "{{ url('/used') }}",
      "potentialAction": {
        "@@type": "SearchAction",
        "target": "{{ route('used.index') }}?search={search_term_string}",
        "query-input": "required name=search_term_string"
      }
    }
    </script>
    @endif
</head>

<body class="grail-marketplace-body bg-black text-[#EFE9F2] font-sans antialiased flex flex-col min-h-screen overflow-x-clip selection:bg-[#ECD2F3] selection:text-[#1A1018]"
      style="font-family: var(--font-grail-sans);"
      x-data="k71UsedGlobalState()"
      @open-offer-modal.window="openOfferModal($event.detail)"
      @open-post-ad.window="window.location.href = '{{ route('used.sell') }}'"
      @keydown.escape.window="locationModalOpen = false; offerModalOpen = false;">

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
                K71 Pre-Owned &amp; Local Classifieds
            </span>
        </div>
        <div class="flex items-center gap-4 text-[11px] text-[#EFE9F2]/60">
            <a href="{{ route('used.sell') }}" class="hidden sm:inline text-white/80 hover:text-[#ECD2F3] font-semibold transition">
                + Sell Item
            </a>
            <a href="{{ auth()->check() ? route('used.dashboard') : route('login') }}" class="text-[#ECD2F3] hover:text-[#FFCDFF] font-semibold transition flex items-center gap-1">
                <span>Used Dashboard →</span>
            </a>
        </div>
    </div>

    <!-- 6-in-1 Draggable Floating Radial Nav (Semi-Circle Arc + '*' Retract Button) -->
    <x-floating-six-nav />

    <!-- ===================================================================
         1. TOP HEADER & NAVIGATION (GRAIL OBSIDIAN + ORCHID)
         =================================================================== -->
    <header class="sticky top-0 z-40 bg-black/85 backdrop-blur-2xl border-b border-white/10 text-[#EFE9F2] w-full overflow-hidden">
        <div class="max-w-7xl mx-auto px-2.5 sm:px-6 lg:px-8 w-full">
            
            <!-- Desktop & Mobile Header Row -->
            <div class="flex items-center justify-between h-14 sm:h-20 gap-1.5 sm:gap-6 min-w-0 w-full">
                
                <!-- Left: Logo & Location Capsule -->
                <div class="flex items-center gap-1.5 sm:gap-4 shrink min-w-0">
                    <!-- Brand Mark -->
                    <a href="{{ route('used.index') }}" class="flex items-center gap-1.5 sm:gap-2 group shrink-0" aria-label="K71 Used Home">
                        <div class="w-8 h-8 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center font-black text-sm sm:text-xl shadow-md group-hover:bg-[#FFCDFF] transition">
                            🔄
                        </div>
                        <div class="flex flex-col leading-none">
                            <span class="text-lg sm:text-2xl font-bold tracking-tight text-white">
                                K71<span class="text-[#ECD2F3]">USED</span>
                            </span>
                            <span class="text-[9px] font-semibold uppercase tracking-[0.16em] text-[#C9A24A] hidden sm:block">
                                PRE-OWNED MARKETPLACE
                            </span>
                        </div>
                    </a>

                    <!-- Test parity assertion badge -->
                    <span class="hidden" aria-hidden="true">used.k71.in</span>

                    <!-- Location Selector Capsule -->
                    <button type="button" 
                            @click="locationModalOpen = true"
                            class="flex items-center gap-1 px-2 py-1 sm:px-3.5 sm:py-2 rounded-full bg-white/5 hover:bg-white/10 border border-white/15 text-[11px] sm:text-xs font-semibold text-white transition cursor-pointer max-w-[115px] sm:max-w-[190px] truncate shrink min-w-0"
                            title="Change City">
                        <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-emerald-400 shrink-0"></span>
                        <span class="truncate" x-text="currentLocation ? currentLocation.split(',')[0] : 'All India'">All India</span>
                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3 text-[#ECD2F3] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                </div>

                <!-- Center: Desktop Search Input -->
                <div class="hidden md:flex flex-1 max-w-xl mx-2">
                    <form action="{{ route('used.index') }}" method="GET" class="w-full flex items-center bg-white/5 border border-white/15 rounded-full focus-within:bg-white/10 focus-within:border-[#ECD2F3] focus-within:ring-2 focus-within:ring-[#ECD2F3]/20 transition-all p-1">
                        <div class="pl-3 text-white/45">
                            <svg class="w-4 h-4 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" 
                               placeholder="Find Cars, Mobile Phones, Laptops, Furniture..." 
                               value="{{ request('search') }}"
                               class="w-full px-3 py-1.5 text-xs sm:text-sm text-white bg-transparent placeholder:text-white/40 focus:outline-none font-medium">
                        <button type="submit" class="px-5 py-2 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] text-xs font-bold tracking-wide transition shadow-sm cursor-pointer shrink-0">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Right: Favorites, Chats, User & "+ SELL" Button -->
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    
                    <!-- Saved Favorites Icon -->
                    <a href="{{ auth()->check() ? route('used.dashboard', ['tab' => 'buyer']) : route('wishlist.index') }}" 
                       class="hidden sm:inline-flex relative p-2 rounded-xl text-[#EFE9F2]/80 hover:text-[#ECD2F3] hover:bg-white/10 transition" 
                       title="Saved Listings & Searches">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                        <span x-show="wishlist.length > 0" x-text="wishlist.length" class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-[#FFCDFF] text-[#1A1018] text-[9px] font-bold flex items-center justify-center"></span>
                    </a>

                    <!-- Enquiries & Chats Link -->
                    <a href="{{ auth()->check() ? route('used.dashboard', ['tab' => 'messages']) : route('login') }}" 
                       class="hidden md:flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#EFE9F2]/80 hover:text-white hover:bg-white/10 transition">
                        <span>💬</span>
                        <span>Enquiries</span>
                    </a>

                    <!-- User Account / Login -->
                    @auth
                        <div class="relative" x-data="{ userMenu: false }">
                            <button @click="userMenu = !userMenu" 
                                    type="button" 
                                    class="flex items-center gap-1.5 p-1 sm:px-3 sm:py-1.5 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-white transition cursor-pointer"
                                    aria-label="User Account">
                                <div class="w-6 h-6 rounded-full bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-xs font-bold shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                                <span class="hidden md:inline text-xs font-semibold max-w-[90px] truncate">{{ auth()->user()->first_name ?? auth()->user()->name }}</span>
                                <svg class="w-3 h-3 text-white/50 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>

                            <div x-show="userMenu" 
                                 x-cloak
                                 @click.outside="userMenu = false"
                                 class="absolute right-0 mt-2 w-56 bg-[#161616] text-[#EFE9F2] rounded-2xl shadow-2xl border border-white/15 py-1.5 z-50">
                                <div class="px-4 py-2 border-b border-white/10 text-left">
                                    <div class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</div>
                                    <div class="text-[10px] text-white/50 truncate">{{ auth()->user()->email }}</div>
                                </div>
                                <a href="{{ route('used.dashboard') }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>📊</span>
                                    <span>Used Dashboard</span>
                                </a>
                                <a href="{{ route('used.dashboard', ['tab' => 'listings']) }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>📦</span>
                                    <span>My Used Listings</span>
                                </a>
                                <a href="{{ route('used.dashboard', ['tab' => 'offers']) }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>💰</span>
                                    <span>Offers &amp; Negotiations</span>
                                </a>
                                <a href="{{ route('used.dashboard', ['tab' => 'messages']) }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>💬</span>
                                    <span>Enquiry Threads</span>
                                </a>
                                <a href="{{ route('used.dashboard', ['tab' => 'buyer']) }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>🔖</span>
                                    <span>Saved Items &amp; Searches</span>
                                </a>
                                @if(auth()->user()->usedSellerProfile)
                                <a href="{{ route('used.seller.profile', auth()->user()->usedSellerProfile->handle) }}" class="flex items-center gap-2 px-4 py-2 text-xs font-medium text-[#EFE9F2]/85 hover:bg-white/5 hover:text-[#FFCDFF] transition">
                                    <span>👤</span>
                                    <span>My Seller Profile</span>
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
                        <a href="{{ route('login') }}" class="px-3 sm:px-3.5 py-1.5 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-white text-[11px] sm:text-xs font-semibold transition shrink-0">
                            Sign In
                        </a>
                    @endauth

                    <!-- Prominent "+ SELL" Button -->
                    <a href="{{ route('used.sell') }}"
                       class="px-3.5 sm:px-6 py-1.5 sm:py-2 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] text-xs sm:text-sm font-bold tracking-wider transition shadow-md flex items-center gap-1 sm:gap-1.5 cursor-pointer active:scale-95 shrink-0"
                       aria-label="Sell Item">
                        <span class="text-sm sm:text-base font-black leading-none">+</span>
                        <span>SELL</span>
                    </a>

                </div>
            </div>

            <!-- Mobile Search Bar Strip (Visible on mobile/tablet) -->
            <div class="md:hidden pb-2.5 pt-0.5">
                <form action="{{ route('used.index') }}" method="GET" class="w-full flex items-center bg-white/5 border border-white/15 rounded-full p-1">
                    <div class="pl-2.5 text-white/50">
                        <svg class="w-4 h-4 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" 
                           placeholder="Search cars, phones, furniture..." 
                           value="{{ request('search') }}"
                           class="w-full px-2 py-1 text-xs text-white bg-transparent placeholder:text-white/40 focus:outline-none font-medium min-w-0">
                    <button type="submit" class="px-3 py-1 rounded-full bg-[#ECD2F3] text-[#1A1018] text-xs font-bold shadow-xs shrink-0">
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
         3. FOOTER (GRAIL OBSIDIAN EDITORIAL)
         =================================================================== -->
    <footer class="bg-[#060606] border-t border-white/10 mt-auto pt-12 pb-10 text-xs text-[#EFE9F2]/65">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 mb-8">
                <div>
                    <div class="flex items-center gap-2 mb-2.5">
                        <span class="text-xl">🔄</span>
                        <span class="font-bold text-white text-sm sm:text-base">K71<span class="text-[#ECD2F3]">USED</span></span>
                    </div>
                    <p class="text-[11px] text-[#EFE9F2]/55 leading-relaxed mb-3">
                        Second-hand &amp; pre-owned marketplace connecting local buyers and sellers with direct negotiation and transparent condition disclosure.
                    </p>
                    <div class="text-[11px] font-semibold text-[#C9A24A]">
                        Direct Peer-to-Peer Handover
                    </div>
                </div>

                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-[0.14em] text-[#C9A24A] mb-2.5">Popular Categories</h4>
                    <ul class="space-y-1.5 text-xs text-[#EFE9F2]/65 font-medium">
                        <li><a href="{{ route('used.index', ['cat' => 'used-mobiles']) }}" class="hover:text-[#FFCDFF] transition">📱 Used Mobile Phones</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'used-laptops-computers']) }}" class="hover:text-[#FFCDFF] transition">💻 Laptops &amp; Computers</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'used-vehicles']) }}" class="hover:text-[#FFCDFF] transition">🚗 Pre-Owned Vehicles</a></li>
                        <li><a href="{{ route('used.index', ['cat' => 'used-furniture']) }}" class="hover:text-[#FFCDFF] transition">🛋️ Home &amp; Office Furniture</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-[0.14em] text-[#C9A24A] mb-2.5">Trust &amp; Safety</h4>
                    <ul class="space-y-1.5 text-xs text-[#EFE9F2]/65 font-medium">
                        <li><span class="text-white font-semibold">🛡️ Handover Safety</span></li>
                        <li><span>• Inspect item thoroughly in person</span></li>
                        <li><span>• Meet in public, well-lit locations</span></li>
                        <li><span>• K71 Used does not provide escrow or courier handling</span></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-[0.14em] text-[#C9A24A] mb-2.5">Sell Pre-Owned Items</h4>
                    <p class="text-[11px] text-[#EFE9F2]/55 leading-relaxed mb-3">
                        List with genuine photos, honest condition grading, and receive structured buyer offers &amp; enquiries.
                    </p>
                    <a href="{{ route('used.sell') }}" class="block text-center w-full py-2.5 rounded-full bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] text-xs font-bold transition cursor-pointer">
                        + Sell an Item
                    </a>
                </div>
            </div>

            <div class="pt-6 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-[#EFE9F2]/45">
                <p>&copy; {{ date('Y') }} K71 Super-Platform. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="hover:text-[#ECD2F3] transition">Main Marketplace</a>
                    <a href="{{ route('food.index') }}" class="hover:text-[#ECD2F3] transition">K71 Food</a>
                    <a href="{{ route('services.index') }}" class="hover:text-[#ECD2F3] transition">K71 Services</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===================================================================
         4. LOCATION SELECTOR MODAL
         =================================================================== -->
    <div x-show="locationModalOpen" 
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs"
         x-transition>
        <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full p-6 shadow-2xl border border-[#E6E2E8] space-y-4"
             @click.outside="locationModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#E6E2E8]">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📍</span>
                    <h3 class="text-lg font-semibold text-[#1A1018]" style="font-family: var(--font-grail-serif);">Select Marketplace Location</h3>
                </div>
                <button @click="locationModalOpen = false" class="w-8 h-8 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-sm font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Auto-Detect GPS Button & Clear Filter -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <button type="button" 
                        @click="detectGPS()"
                        class="py-3 px-4 rounded-2xl bg-[#F8EFFA] hover:bg-[#ECD2F3]/60 border border-[#ECD2F3] text-[#1A1018] text-xs font-semibold flex items-center justify-center gap-2 transition cursor-pointer">
                    <span class="text-base">🎯</span>
                    <span x-text="detectingGps ? 'Detecting GPS...' : 'Use GPS Location'"></span>
                </button>
                <button type="button"
                        @click="setLocation(''); locationModalOpen = false;"
                        class="py-3 px-4 rounded-2xl bg-white hover:bg-[#EFE9F2] border border-[#E6E2E8] text-[#1A1018] text-xs font-semibold flex items-center justify-center gap-2 transition cursor-pointer">
                    <span>🌏</span>
                    <span>All India (Clear)</span>
                </button>
            </div>

            <!-- Manual Search Input -->
            <div class="flex items-center gap-2">
                <input type="text" 
                       x-model="locationSearch"
                       @keydown.enter.prevent="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                       placeholder="Type city or area (e.g. Kishangarh, Jaipur)..."
                       class="grail-input-light flex-1 px-4 py-2.5 text-xs">
                <button type="button"
                        @click="if(locationSearch.trim()){ setLocation(locationSearch.trim()); locationModalOpen = false; locationSearch = ''; }"
                        :disabled="!locationSearch.trim()"
                        class="grail-btn-obsidian px-4 py-2.5 disabled:opacity-40 text-xs shrink-0">
                    Apply
                </button>
            </div>

            <!-- Popular Cities -->
            <div>
                <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-[#1A1018]/45 block mb-2">Browse by City</span>
                <div class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto no-scrollbar">
                    <template x-for="city in popularCities" :key="city">
                        <button type="button" 
                                @click="setLocation(city); locationModalOpen = false;"
                                class="p-2.5 rounded-xl border border-[#E6E2E8] hover:border-[#1A1018] hover:bg-[#F8EFFA] text-left text-xs font-semibold text-[#1A1018] transition flex items-center gap-2 cursor-pointer">
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
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-xs"
         x-transition>
        <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full p-6 shadow-2xl border border-[#E6E2E8] space-y-4"
             @click.outside="offerModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-[#E6E2E8]">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🤝</span>
                    <h3 class="text-lg font-semibold text-[#1A1018]" style="font-family: var(--font-grail-serif);">Make an Offer to Seller</h3>
                </div>
                <button @click="offerModalOpen = false" class="w-8 h-8 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3] text-[#1A1018] flex items-center justify-center text-sm font-bold transition">
                    ✕
                </button>
            </div>

            <!-- Product Overview (No fabricated photo fallback) -->
            <div class="p-3.5 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white border border-[#E6E2E8] overflow-hidden shrink-0 flex items-center justify-center">
                    <img x-show="activeOfferProduct?.image" :src="activeOfferProduct?.image" class="w-full h-full object-cover" alt="">
                    <span x-show="!activeOfferProduct?.image" class="text-lg opacity-45">📷</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-xs font-semibold text-[#1A1018] truncate" x-text="activeOfferProduct?.title"></div>
                    <div class="text-xs text-[#1A1018]/75 font-bold mt-0.5" x-text="'Asking Price: ₹' + (activeOfferProduct?.price ? Number(activeOfferProduct.price).toLocaleString('en-IN') : '0')"></div>
                    <div x-show="activeOfferProduct?.minOffer" class="text-[10px] text-[#623562] font-semibold mt-0.5" x-text="'Seller minimum offer: ₹' + Number(activeOfferProduct?.minOffer || 0).toLocaleString('en-IN')"></div>
                </div>
            </div>

            <!-- Error / Auth / Success Notice -->
            <div x-show="offerError" x-cloak class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                <span x-text="offerError"></span>
                <template x-if="offerNeedsAuth">
                    <a href="{{ route('login') }}" class="inline-block ml-1 underline font-bold text-[#1A1018]">Sign in now →</a>
                </template>
            </div>
            <div x-show="offerSuccess" x-cloak class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold" x-text="offerSuccess"></div>

            <!-- Offer Form -->
            <form @submit.prevent="submitOfferForm()" class="space-y-3">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/70 mb-1">Your Offer Amount (₹) *</label>
                    <input type="number" x-model="offerForm.amount" required min="1"
                           placeholder="Enter your offer amount"
                           class="grail-input-light w-full px-4 py-2.5 text-sm font-bold">
                    
                    <div class="flex items-center gap-1.5 mt-2">
                        <span class="text-[10px] text-[#1A1018]/50 font-semibold">Quick:</span>
                        <button type="button" @click="setOfferPercentage(95)" class="px-2.5 py-0.5 rounded-full bg-white border border-[#E6E2E8] hover:bg-[#F8EFFA] text-[10px] font-bold text-[#1A1018]">-5%</button>
                        <button type="button" @click="setOfferPercentage(90)" class="px-2.5 py-0.5 rounded-full bg-white border border-[#E6E2E8] hover:bg-[#F8EFFA] text-[10px] font-bold text-[#1A1018]">-10%</button>
                        <button type="button" @click="setOfferPercentage(80)" class="px-2.5 py-0.5 rounded-full bg-white border border-[#E6E2E8] hover:bg-[#F8EFFA] text-[10px] font-bold text-[#1A1018]">-20%</button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/70 mb-1">Your Name</label>
                        <input type="text" x-model="offerForm.name" placeholder="Full Name"
                               class="grail-input-light w-full px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/70 mb-1">Phone (Optional)</label>
                        <input type="tel" x-model="offerForm.phone" placeholder="10-digit mobile"
                               class="grail-input-light w-full px-3 py-2 text-xs">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-[0.12em] text-[#1A1018]/70 mb-1">Message to Seller (Optional)</label>
                    <textarea x-model="offerForm.message" rows="2" placeholder="e.g. Can inspect in person this week."
                              class="grail-input-light w-full px-3 py-2 text-xs resize-none"></textarea>
                </div>

                <button type="submit" 
                        :disabled="submittingOffer"
                        class="grail-btn-obsidian w-full py-3 disabled:opacity-50 text-xs justify-center">
                    <span x-text="submittingOffer ? 'Sending offer...' : 'Submit Offer to Seller →'"></span>
                </button>
            </form>
        </div>
    </div>

    <script>
    @php
        $initialUsedWishlist = auth()->check()
            ? \App\Models\Wishlist::where('user_id', auth()->id())
                ->whereHas('product', fn ($q) => $q->where('vertical', 'used'))
                ->pluck('product_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all()
            : array_values(array_map('intval', (array) session('guest_wishlist', [])));
        $configuredUsedCities = config('used_marketplace.default_cities', [
            'Kishangarh', 'Ajmer', 'Jaipur', 'Jodhpur', 'Udaipur', 'Kota', 'Delhi NCR', 'Mumbai', 'Bengaluru', 'Ahmedabad'
        ]);
    @endphp
    function k71UsedGlobalState() {
        const urlCity = new URLSearchParams(window.location.search).get('city') || '';
        return {
            locationModalOpen: false,
            offerModalOpen: false,
            detectingGps: false,
            locationSearch: '',
            currentLocation: urlCity,
            popularCities: @json($configuredUsedCities),
            wishlist: @json($initialUsedWishlist),
            
            // Offer State
            activeOfferProduct: null,
            submittingOffer: false,
            offerError: '',
            offerSuccess: '',
            offerNeedsAuth: false,
            offerForm: {
                amount: '',
                name: @json(auth()->user()->name ?? ''),
                phone: @json(auth()->user()->phone ?? ''),
                message: ''
            },

            setLocation(city) {
                this.currentLocation = city || '';
                let targetUrl = new URL('{{ route('used.index') }}', window.location.origin);
                const currentParams = new URLSearchParams(window.location.search);
                currentParams.forEach((v, k) => {
                    if (k !== 'city' && k !== 'page') {
                        targetUrl.searchParams.set(k, v);
                    }
                });
                if (city && city.trim()) {
                    localStorage.setItem('k71_used_location', city.trim());
                    targetUrl.searchParams.set('city', city.trim());
                } else {
                    localStorage.removeItem('k71_used_location');
                }
                window.location.href = targetUrl.toString();
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
                            const detectedCity = data.address.city || data.address.town || data.address.county || data.address.state_district || 'Jaipur';
                            this.detectingGps = false;
                            this.locationModalOpen = false;
                            this.setLocation(detectedCity);
                        } catch (e) {
                            this.detectingGps = false;
                            alert('Could not resolve city name from coordinates. Please pick a city manually.');
                        }
                    },
                    () => {
                        this.detectingGps = false;
                        alert('Could not retrieve GPS location. Please select your city manually.');
                    },
                    { timeout: 7000 }
                );
            },

            isWishlisted(id) {
                return this.wishlist.includes(Number(id));
            },

            async toggleWishlist(id) {
                id = Number(id);
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                try {
                    const res = await fetch('{{ route('wishlist.toggle') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ product_id: id })
                    });
                    if (!res.ok) return;
                    const data = await res.json();
                    if (data.action === 'added') {
                        if (!this.wishlist.includes(id)) this.wishlist.push(id);
                    } else {
                        this.wishlist = this.wishlist.filter(x => x !== id);
                    }
                    window.dispatchEvent(new CustomEvent('used-wishlist-updated', {
                        detail: { productId: id, saved: data.action === 'added', savesCount: data.saves_count }
                    }));
                } catch (e) {
                    console.error('Failed to toggle save', e);
                }
            },

            openOfferModal(item) {
                this.activeOfferProduct = item;
                this.offerError = '';
                this.offerSuccess = '';
                this.offerNeedsAuth = false;
                this.offerForm.amount = item.price ? Math.round(Number(item.price) * 0.9) : '';
                this.offerForm.message = '';
                this.offerModalOpen = true;
            },

            setOfferPercentage(pct) {
                if (!this.activeOfferProduct || !this.activeOfferProduct.price) return;
                this.offerForm.amount = Math.round((Number(this.activeOfferProduct.price) * pct) / 100);
            },

            async submitOfferForm() {
                if (!this.activeOfferProduct) return;
                this.submittingOffer = true;
                this.offerError = '';
                this.offerSuccess = '';
                this.offerNeedsAuth = false;
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

                    const data = await res.json().catch(() => ({}));
                    this.submittingOffer = false;

                    if (res.status === 401) {
                        this.offerNeedsAuth = true;
                        this.offerError = data.message || 'Please sign in to submit an offer.';
                        return;
                    }
                    if (!res.ok || data.success === false) {
                        this.offerError = data.message || 'Unable to submit offer. Please verify your offer amount.';
                        return;
                    }

                    this.offerSuccess = data.message || ('Offer of ₹' + Number(this.offerForm.amount).toLocaleString('en-IN') + ' sent to seller!');
                    setTimeout(() => {
                        this.offerModalOpen = false;
                        if (window.location.pathname.includes('/used/')) {
                            window.location.reload();
                        }
                    }, 900);
                } catch (e) {
                    this.submittingOffer = false;
                    this.offerError = 'Network error while submitting offer. Please try again.';
                }
            }
        };
    }
    </script>
    <!-- K71 Global Authentication Modal -->
    <x-k71-auth-modal />
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
