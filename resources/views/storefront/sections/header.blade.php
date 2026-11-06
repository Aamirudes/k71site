@php
    $headerCfg = $config['header'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    $themeCfg = $config['theme'] ?? [];
    
    $storeName = $brandCfg['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brandCfg['logo_url'] ?? $vendor->logo_url;
    $logoHeight = $headerCfg['logo_height'] ?? 36;
    $isSticky = $headerCfg['sticky'] ?? true;
    $transparentOnHero = $headerCfg['transparent_on_hero'] ?? true;
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
    
    $waPhone = preg_replace('/[^0-9]/', '', (string) ($brandCfg['whatsapp'] ?? $vendor->contact_phone));
    if (strlen($waPhone) === 10) { $waPhone = '91' . $waPhone; }
    $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text=" . rawurlencode("Namaste {$storeName}! I am viewing your boutique on K71.") : null;

    $searchQuery = request('search', '');
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
                    <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-2 sm:gap-3 group min-w-0">
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
                            @if(!empty($brandCfg['city']))
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

                <!-- CENTER: Desktop Search Bar (Hidden on Mobile, Visible >= 768px) -->
                <div class="hidden md:flex items-center gap-3 flex-1 justify-center max-w-md mx-3 lg:mx-6 min-w-0">
                    <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="w-full relative">
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
                            class="group relative flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-4 py-1.5 sm:py-2.5 rounded-xl sm:rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300 hover:scale-105 active:scale-95 cursor-pointer shadow-md"
                            :class="{
                                'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950': isScrolled,
                                'bg-white text-zinc-950': !isScrolled
                            }"
                            aria-label="Open shopping bag">
                        
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        
                        <span class="hidden sm:inline font-sans">Bag</span>
                        
                        <!-- Real-time Count Badge -->
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] font-black font-mono transition-transform group-hover:scale-110"
                              :class="{
                                  'bg-white text-black dark:bg-black dark:text-white': isScrolled,
                                  'bg-black text-white': !isScrolled
                              }">
                            {{ $cartCount }}
                        </span>
                    </button>

                </div>
            </div>

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
                <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="relative flex items-center">
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

        </header>
    </div>

    <!-- FULL 100VH HALF-WIDTH SLIDE DRAWER (SLIDES DIRECT FROM LEFT TO FRONT) -->
    <div x-show="mobileMenuOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[70] bg-black/75 backdrop-blur-md"
         @click="mobileMenuOpen = false">
    </div>

    <div x-show="mobileMenuOpen"
         x-cloak
         x-transition:enter="transform transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-350"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transform transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-250"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full"
         class="fixed inset-y-0 left-0 z-[80] w-full sm:w-1/2 md:w-1/2 lg:w-1/2 max-w-2xl h-screen min-h-screen bg-white dark:bg-zinc-950 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.6)] flex flex-col justify-between overflow-y-auto border-r border-zinc-200/80 dark:border-zinc-800 selection:bg-amber-100">
        
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
                            <span>✦</span> Verified Boutique Studio
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

            <!-- In-Drawer Quick Search Form -->
            <div>
                <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="relative">
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
                    <span class="text-xl">🛍️</span>
                    <div>
                        <div class="text-xs font-bold text-zinc-900 dark:text-white">Boutique Bag</div>
                        <div class="text-[11px] text-zinc-500">Tap to inspect or checkout</div>
                    </div>
                </div>
                <button type="button" 
                        @click="mobileMenuOpen = false; if(window.openK71Cart) window.openK71Cart();"
                        class="px-4 py-2 rounded-xl text-xs font-bold bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 hover:opacity-90 transition cursor-pointer">
                    View Bag →
                </button>
            </div>

            <!-- Catalog Navigation Links -->
            <div class="space-y-1.5">
                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500 block px-2 mb-2">
                    Boutique Departments
                </span>

                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
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
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}#catalog" 
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
                <a href="{{ route('storefront.show', $vendor->slug) }}#trust" 
                   @click="mobileMenuOpen = false"
                   class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-950 dark:hover:text-white transition">
                    <span>◈</span> Verified Workshop & Dispatch Guarantees
                </a>
                <a href="{{ route('storefront.show', $vendor->slug) }}#reviews" 
                   @click="mobileMenuOpen = false"
                   class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold text-zinc-500 hover:text-zinc-950 dark:hover:text-white transition">
                    <span>★</span> Genuine Customer Reviews
                </a>
            </div>
        </div>

        <!-- Drawer Footer: WhatsApp Stylist Concierge & Return Link -->
        <div class="p-6 sm:p-8 border-t border-zinc-200 dark:border-zinc-800 space-y-3 bg-zinc-50/70 dark:bg-zinc-900/50">
            @if($waUrl)
                <a href="{{ $waUrl }}" target="_blank" 
                   class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition hover:scale-[1.01] cursor-pointer">
                    <span>💬</span>
                    <span>Chat with Boutique Stylist on WhatsApp</span>
                </a>
            @endif
            <a href="{{ route('stores.index') }}" 
               class="block text-center text-xs font-semibold text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300 transition py-1">
                &larr; Return to K71 Marketplace Stores Directory
            </a>
        </div>

    </div>
</div>
