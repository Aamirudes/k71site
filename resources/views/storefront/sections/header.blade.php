@php
    $headerCfg = $config['header'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    $themeCfg = $config['theme'] ?? [];
    
    $storeName = $brandCfg['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brandCfg['logo_url'] ?? $vendor->logo_url;
    $logoHeight = $headerCfg['logo_height'] ?? 36;
    $isSticky = $headerCfg['sticky'] ?? true;
    $transparentOnHero = empty($isServiceWebsite) ? ($headerCfg['transparent_on_hero'] ?? true) : false;
    
    $cartCount = 0;
    if (empty($isServiceWebsite) && auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
    
    $hidePublicPhone = !empty($isServiceWebsite) && empty($providerSite['contact']['show_phone']);
    $waPhone = $hidePublicPhone ? null : preg_replace('/[^0-9]/', '', (string) ($brandCfg['whatsapp'] ?? $vendor->contact_phone));
    if ($waPhone && strlen($waPhone) === 10) { $waPhone = '91' . $waPhone; }
    $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text=" . rawurlencode(!empty($isServiceWebsite) ? "Hi {$storeName}, I visited your portfolio site." : "Namaste {$storeName}! I am viewing your boutique on K71.") : null;

    $searchQuery = request('search', '');
    $isDarkPreset = in_array($presetKey ?? '', ['noire', 'orbit', 'nexus', 'elysium']) || !empty($themeCfg['dark_mode']);
@endphp

<!-- FLOATING LIQUID GLASS VENDOR STOREFRONT HEADER CAPSULE (ULTRA-RESPONSIVE) -->
<div class="w-full fixed top-0 inset-x-0 z-50 transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
     x-data="{ 
         mobileMenuOpen: false, 
         mobileSearchOpen: false,
         isScrolled: false,
         init() {
             this.checkScroll();
             window.addEventListener('scroll', () => this.checkScroll(), { passive: true });
         },
         checkScroll() {
             this.isScrolled = window.pageYOffset > 35;
         }
     }"
     @keydown.escape.window="mobileMenuOpen = false; mobileSearchOpen = false;">

    @if(!empty($headerCfg['announcement_enabled']) && !empty($headerCfg['announcement_text']) && trim($headerCfg['announcement_text']) !== 'Free Delivery')
        @include('storefront.sections.announcement')
    @endif

    <div class="px-2.5 sm:px-6 transition-all duration-300"
         :class="{
             'pt-2 sm:pt-3': isScrolled,
             'pt-2.5 sm:pt-4 lg:pt-5': !isScrolled
         }">
    
        <header id="storefront-header"
                class="max-w-7xl mx-auto rounded-2xl sm:rounded-3xl transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-visible shadow-lg"
                :class="{
                    'bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl shadow-[0_12px_40px_rgba(0,0,0,0.12)] border border-zinc-200/90 dark:border-zinc-800/90 text-zinc-900 dark:text-white': isScrolled,
                    '{{ $transparentOnHero ? 'bg-black/35 backdrop-blur-xl border border-white/20 text-white shadow-[0_8px_32px_rgba(0,0,0,0.25)]' : 'bg-white/95 backdrop-blur-xl border border-stone-200/90 text-stone-900 shadow-md' }}': !isScrolled
                }">
            
            <div class="px-3 sm:px-6 h-13 sm:h-16 flex items-center justify-between gap-2 sm:gap-6 min-w-0">
                
                <!-- LEFT: Hamburger Menu Icon + Store Logo + Store Name -->
                <div class="flex items-center gap-1.5 sm:gap-3.5 shrink min-w-0">
                    
                    <!-- Hamburger Drawer Trigger -->
                    <button type="button" 
                            @click="mobileMenuOpen = true"
                            class="p-1.5 sm:p-2 -ml-1 rounded-xl sm:rounded-2xl transition-all duration-200 flex items-center justify-center shrink-0 cursor-pointer"
                            :class="{
                                'text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800': isScrolled,
                                'text-white hover:bg-white/20': !isScrolled
                            }"
                            aria-label="Open boutique menu">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Store Logo & Name -->
                    <a href="{{ $vendor->storefront_url }}" class="flex items-center gap-2 sm:gap-3 group min-w-0">
                        @if(!empty($storeLogo))
                            <div class="relative p-0.5 sm:p-1 rounded-xl sm:rounded-2xl transition-all duration-300 group-hover:scale-105 shrink-0"
                                 :class="{
                                     'bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700': isScrolled,
                                     'bg-white/15 backdrop-blur-md border border-white/25': !isScrolled
                                 }">
                                <img src="{{ $storeLogo }}" 
                                     alt="{{ $storeName }}" 
                                     class="h-7 sm:h-9 w-auto max-w-[80px] sm:max-w-[120px] object-contain rounded-lg sm:rounded-xl"
                                     onerror="this.style.display='none'; document.getElementById('hdr-fallback-name').style.display='block';">
                            </div>
                            <span id="hdr-fallback-name" style="display:none;" class="text-xs sm:text-base font-black font-heading truncate max-w-[110px] sm:max-w-[200px]">
                                {{ $storeName }}
                            </span>
                        @else
                            <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl sm:rounded-2xl flex items-center justify-center font-black text-white text-xs sm:text-sm shadow-sm shrink-0 border border-white/25"
                                 style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                                {{ strtoupper(substr($storeName, 0, 1)) }}
                            </div>
                        @endif

                        <!-- Brand Typography -->
                        <div class="flex flex-col min-w-0 justify-center">
                            <span class="text-xs sm:text-base font-black tracking-tight font-heading leading-tight truncate max-w-[110px] sm:max-w-[220px] transition-colors"
                                  :class="{
                                      'text-zinc-950 dark:text-white': isScrolled,
                                      'text-white': !isScrolled
                                  }">
                                {{ $storeName }}
                            </span>
                            @if(!empty($isServiceWebsite))
                                <span class="text-[8px] sm:text-[9.5px] uppercase tracking-wider font-semibold transition-colors truncate hidden sm:inline-block"
                                      :class="{
                                          'text-zinc-500 dark:text-zinc-400': isScrolled,
                                          'text-white/80': !isScrolled
                                      }">
                                    {{ $providerSite['identity']['provider_mode_label'] ?? 'Portfolio & Services' }}
                                </span>
                            @elseif(!empty($brandCfg['city']))
                                <span class="text-[8px] sm:text-[9.5px] uppercase tracking-wider font-semibold transition-colors truncate hidden sm:inline-block"
                                      :class="{
                                          'text-zinc-500 dark:text-zinc-400': isScrolled,
                                          'text-white/80': !isScrolled
                                      }">
                                    📍 {{ $brandCfg['city'] }} · Studio
                                </span>
                            @endif
                        </div>
                    </a>
                </div>

                @if(!empty($isServiceWebsite))
                    @php $navLabels = $providerSite['nav_labels'] ?? []; @endphp
                    <nav class="hidden md:flex items-center gap-6 text-xs font-bold tracking-wide">
                        <a href="#overview" class="hover:opacity-75 transition">{{ $navLabels['overview'] ?? 'Overview' }}</a>
                        <a href="#portfolio" class="hover:opacity-75 transition">{{ $navLabels['work'] ?? 'Work' }}</a>
                        <a href="#services" class="hover:opacity-75 transition">{{ $navLabels['services'] ?? 'Services' }}</a>
                        <a href="#about" class="hover:opacity-75 transition">{{ $navLabels['about'] ?? 'About' }}</a>
                        <a href="#reviews" class="hover:opacity-75 transition">{{ $navLabels['reviews'] ?? 'Reviews' }}</a>
                    </nav>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="#contact"
                           class="px-4 py-2 rounded-full text-xs font-bold bg-stone-900 text-white hover:opacity-90 transition shadow-sm">
                            {{ $providerSite['identity']['cta_primary_text'] ?? 'Start a Project' }}
                        </a>
                    </div>
                @else
                <!-- CENTER: Desktop Search Bar (Hidden on Mobile, Visible >= 768px) -->
                <div class="hidden md:flex items-center gap-3 flex-1 justify-center max-w-md mx-3 lg:mx-6 min-w-0">
                    <form action="{{ $vendor->storefrontUrl([], 'catalog') }}" method="GET" class="w-full relative">
                        <input type="text" 
                               name="search" 
                               value="{{ $searchQuery }}" 
                               placeholder="Search catalog in {{ $storeName }}..." 
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-full transition-all focus:outline-none"
                               :class="{
                                   'bg-zinc-100/90 dark:bg-zinc-800/90 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-zinc-900/20': isScrolled,
                                   'bg-white/15 backdrop-blur-md text-white placeholder:text-white/70 border border-white/25 focus:bg-black/50': !isScrolled
                               }">
                        <svg class="w-4 h-4 absolute left-3 top-2.5 transition-colors" 
                             :class="{ 'text-zinc-400': isScrolled, 'text-white/70': !isScrolled }"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </form>
                </div>

                <!-- RIGHT: Mobile Search Toggle + Stylist Desk + Shopping Bag -->
                <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                    
                    <!-- Mobile Search Expander Button (Mobile Only) -->
                    <button type="button" 
                            @click="mobileSearchOpen = !mobileSearchOpen; if(mobileSearchOpen){ $nextTick(() => $refs.mobileSearchInput?.focus()); }"
                            class="md:hidden p-1.5 rounded-xl transition-all duration-200 flex items-center justify-center cursor-pointer"
                            :class="{
                                'text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800': isScrolled,
                                'text-white hover:bg-white/20': !isScrolled
                            }"
                            aria-label="Search boutique">
                        <svg x-show="!mobileSearchOpen" class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <svg x-show="mobileSearchOpen" x-cloak class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    @if($waUrl)
                        <a href="{{ $waUrl }}" 
                           target="_blank" 
                           rel="noopener" 
                           title="Direct WhatsApp Stylist"
                           class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-xs"
                           :class="{
                               'bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100': isScrolled,
                               'bg-white/15 backdrop-blur-md text-white border border-white/25 hover:bg-white/25': !isScrolled
                           }">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Stylist Desk</span>
                        </a>
                    @endif

                    <!-- Cart / Bag Trigger Button (Opens Glassmorphic Cart Popup Drawer) -->
                    <button type="button" 
                            onclick="if(window.openK71Cart){ window.openK71Cart(); } else { window.location.href='{{ route('cart.index') }}'; }" 
                            class="group relative flex items-center justify-center gap-1.5 p-2 sm:px-3.5 sm:py-2 rounded-xl sm:rounded-2xl transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer shadow-md {{ $isDarkPreset ? 'bg-zinc-900 border border-zinc-700 text-white' : 'bg-white border border-stone-200 text-black' }}"
                            aria-label="Open shopping bag">
                        
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 {{ $isDarkPreset ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        
                        <!-- Real-time Count Badge -->
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black font-mono transition-transform group-hover:scale-110 {{ $isDarkPreset ? 'bg-white text-black' : 'bg-black text-white' }}">
                            {{ $cartCount }}
                        </span>
                    </button>

                </div>
                @endif
            </div>

            @if(empty($isServiceWebsite))
            <!-- MOBILE SEARCH SLIDE-DOWN DRAWER (Interactive quick-search on mobile) -->
            <div x-show="mobileSearchOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-98"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-98"
                 class="md:hidden px-3 pb-3 pt-1">
                <form action="{{ $vendor->storefrontUrl([], 'catalog') }}" method="GET" class="relative flex items-center">
                    <input type="text" 
                           name="search" 
                           x-ref="mobileSearchInput"
                           value="{{ $searchQuery }}" 
                           placeholder="Search catalog in {{ $storeName }}..." 
                           class="w-full pl-9 pr-9 py-2 text-xs rounded-full bg-white/95 dark:bg-zinc-900/95 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 shadow-xl focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-white">
                    <svg class="w-4 h-4 text-zinc-400 absolute left-3 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="button" 
                            @click="mobileSearchOpen = false" 
                            class="absolute right-2.5 p-1 rounded-full text-zinc-400 hover:text-zinc-700 dark:hover:text-white">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endif

        </header>
    </div>

    <!-- FULL 100VH HALF-WIDTH SLIDE DRAWER (SLIDES DIRECT FROM LEFT TO FRONT, TELEPORTED TO BODY) -->
    <template x-teleport="body">
        <div x-show="mobileMenuOpen" 
             x-cloak
             x-effect="if (mobileMenuOpen) { document.body.style.overflow = 'hidden'; } else { document.body.style.overflow = ''; }"
             style="z-index: 9999999 !important;"
             class="relative">

            <div x-show="mobileMenuOpen" 
                 x-transition:enter="transition-opacity ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0, 0, 0, 0.85) !important; backdrop-filter: blur(6px) !important; z-index: 9999998 !important;"
                 @click="mobileMenuOpen = false">
            </div>

            <div x-show="mobileMenuOpen"
                 x-transition:enter="transform transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-350"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-250"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 style="position: fixed !important; top: 0 !important; bottom: 0 !important; left: 0 !important; height: 100vh !important; min-height: 100vh !important; z-index: 9999999 !important; box-shadow: 0 25px 80px rgba(0,0,0,0.85) !important;"
                 class="w-[88vw] sm:w-[420px] md:w-[460px] max-w-xl bg-white dark:bg-zinc-950 flex flex-col justify-between overflow-y-auto border-r border-zinc-200/80 dark:border-zinc-800 selection:bg-amber-100"
                 @keydown.window.escape="mobileMenuOpen = false">
                
                <div class="p-6 sm:p-8 space-y-6">
                    <!-- Drawer Top: Brand Identity & Close -->
                    <div class="flex items-center justify-between pb-5 border-b border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3.5 min-w-0">
                            @if(!empty($storeLogo))
                                <div class="p-1 rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shrink-0">
                                    <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-9 sm:h-10 w-auto max-w-[100px] object-contain rounded-xl">
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-white text-base shadow-sm shrink-0 border border-white/20"
                                     style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                                    {{ strtoupper(substr($storeName, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <span class="font-black font-heading text-lg sm:text-xl text-zinc-950 dark:text-white truncate block">
                                    {{ $storeName }}
                                </span>
                                <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                    @if(!empty($isServiceWebsite))
                                        <span>✦</span> {{ $providerSite['identity']['provider_mode_label'] ?? 'Portfolio Website' }}
                                    @else
                                        <span>✦</span> Verified Boutique Studio
                                    @endif
                                </span>
                            </div>
                        </div>
                        <button type="button" 
                                @click="mobileMenuOpen = false" 
                                class="w-10 h-10 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-zinc-500 hover:text-zinc-950 dark:hover:text-white flex items-center justify-center transition cursor-pointer shrink-0"
                                aria-label="Close navigation menu">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    @if(!empty($isServiceWebsite))
                        @php $navLabels = $providerSite['nav_labels'] ?? []; @endphp
                        <nav class="space-y-1.5">
                            <a href="#overview" @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                                {{ $navLabels['overview'] ?? 'Overview' }}
                            </a>
                            <a href="#portfolio" @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                                {{ $navLabels['work'] ?? 'Work' }}
                            </a>
                            <a href="#services" @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                                {{ $navLabels['services'] ?? 'Services' }}
                            </a>
                            <a href="#about" @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                                {{ $navLabels['about'] ?? 'About' }}
                            </a>
                            <a href="#reviews" @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                                {{ $navLabels['reviews'] ?? 'Reviews' }}
                            </a>
                            <a href="#contact" @click="mobileMenuOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold bg-zinc-950 text-white dark:bg-white dark:text-black text-center mt-3">
                                {{ $providerSite['identity']['cta_primary_text'] ?? 'Start a Project' }}
                            </a>
                        </nav>
                    @else
                    <!-- In-Drawer Quick Search Form -->
                    <div>
                        <form action="{{ $vendor->storefrontUrl([], 'catalog') }}" method="GET" class="relative">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $searchQuery }}" 
                                   placeholder="Search creations in {{ $storeName }}..." 
                                   class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm font-medium rounded-2xl bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 text-zinc-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-zinc-900 dark:focus:ring-white">
                            <svg class="w-4 h-4 text-zinc-400 absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </form>
                    </div>

                    <!-- Interactive Bag Quick Link Strip Inside Menu -->
                    <div class="p-4 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200/80 dark:border-zinc-800 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl {{ $isDarkPreset ? 'bg-zinc-800 text-white' : 'bg-white text-black' }} flex items-center justify-center shadow-xs">
                                <svg class="w-5 h-5 {{ $isDarkPreset ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-zinc-900 dark:text-white">Boutique Bag</div>
                                <div class="text-[11px] text-zinc-500">Tap to inspect or checkout</div>
                            </div>
                        </div>
                        <button type="button" 
                                @click="mobileMenuOpen = false; if(window.openK71Cart) window.openK71Cart();"
                                class="px-4 py-2 rounded-xl text-xs font-bold {{ $isDarkPreset ? 'bg-white text-black' : 'bg-black text-white' }} hover:opacity-90 transition cursor-pointer">
                            View Bag →
                        </button>
                    </div>

                    <!-- Catalog Navigation Links -->
                    <div class="space-y-1.5">
                        <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 block px-2 mb-2">
                            Boutique Departments
                        </span>

                        <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center justify-between px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                            <span class="flex items-center gap-2.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <span>Complete Collection</span>
                            </span>
                            <span class="text-xs font-mono font-semibold px-2.5 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300">
                                {{ $products->total() ?? $products->count() }}
                            </span>
                        </a>

                        @foreach($vendorCategories as $cat)
                            <a href="{{ $vendor->storefrontUrl(['category' => $cat->slug], 'catalog') }}" 
                               @click="mobileMenuOpen = false"
                               class="flex items-center justify-between px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:text-zinc-950 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                                <span>{{ $cat->name }}</span>
                                <span class="text-[11px] font-mono text-zinc-400 px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800">
                                    {{ $cat->products_count }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <!-- Direct Store Anchor Jumps -->
                    <div class="pt-2 space-y-1 border-t border-zinc-100 dark:border-zinc-800">
                        <a href="{{ $vendor->storefrontUrl([], 'reviews') }}" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-950 dark:hover:text-white transition">
                            <span>★</span> Genuine Customer Reviews
                        </a>
                        <a href="{{ $vendor->storefrontUrl([], 'happy_customers') }}" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-950 dark:hover:text-white transition">
                            <span>📸</span> Happy Customer Stories
                        </a>
                        <a href="{{ $vendor->storefrontUrl([], 'trust') }}" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-950 dark:hover:text-white transition">
                            <span>◈</span> Verified Workshop &amp; Dispatch Guarantees
                        </a>
                    </div>
                    @endif
                </div>

                <!-- Drawer Footer -->
                <div class="p-6 sm:p-8 border-t border-zinc-200 dark:border-zinc-800 space-y-3 bg-zinc-50/70 dark:bg-zinc-900/50">
                    @if($waUrl)
                        <a href="{{ $waUrl }}" target="_blank" 
                           class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition hover:scale-[1.01] cursor-pointer">
                            <span>💬</span>
                            <span>{{ !empty($isServiceWebsite) ? 'Message on WhatsApp' : 'Chat with Boutique Stylist on WhatsApp' }}</span>
                        </a>
                    @endif
                    @if(!empty($isServiceWebsite))
                        <a href="{{ route('services.provider', $vendor->slug) }}" 
                           class="block text-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300 transition py-1">
                            View on K71 Services Marketplace →
                        </a>
                    @else
                        <a href="{{ route('stores.index') }}" 
                           class="block text-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300 transition py-1">
                            &larr; Return to K71 Marketplace Stores Directory
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </template>
</div>
