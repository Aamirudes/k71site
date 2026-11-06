@php
    $headerCfg = $config['header'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    $themeCfg = $config['theme'] ?? [];
    
    $storeName = $brandCfg['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brandCfg['logo_url'] ?? $vendor->logo_url;
    $logoHeight = $headerCfg['logo_height'] ?? 36;
    $isSticky = $headerCfg['sticky'] ?? true;
    $showSearch = $headerCfg['show_search'] ?? true;
    $elementsOrder = $headerCfg['elements_order'] ?? ['hamburger', 'logo', 'name', 'cart'];
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

<!-- FLOATING LIQUID GLASS HEADER WITH TRANSLUCENT SCROLL ANIMATION -->
<div class="w-full fixed top-0 inset-x-0 z-50 transition-all duration-400 ease-[cubic-bezier(0.16,1,0.3,1)]"
     x-data="{ 
         mobileMenuOpen: false, 
         isScrolled: false,
         init() {
             this.checkScroll();
             window.addEventListener('scroll', () => this.checkScroll(), { passive: true });
         },
         checkScroll() {
             this.isScrolled = window.pageYOffset > 45;
         }
     }">
    
    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    <div class="px-3 sm:px-6 transition-all duration-400"
         :class="{
             'pt-2 sm:pt-3': isScrolled,
             'pt-3 sm:pt-5': !isScrolled
         }">
    
    <header id="storefront-header"
            class="max-w-7xl mx-auto rounded-3xl transition-all duration-400 ease-[cubic-bezier(0.16,1,0.3,1)]"
            :class="{
                'bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl shadow-[0_12px_40px_rgba(0,0,0,0.08)] border border-zinc-200/70 dark:border-zinc-800/70 text-zinc-900 dark:text-white': isScrolled,
                '{{ $transparentOnHero ? 'bg-black/25 backdrop-blur-md border border-white/20 text-white shadow-none' : 'bg-white/90 backdrop-blur-xl border border-stone-200/80 text-stone-900 shadow-sm' }}': !isScrolled
            }">
        
        <div class="px-3.5 sm:px-6 h-15 sm:h-18 flex items-center justify-between gap-3 sm:gap-6">
            
            <!-- LEFT REGION: Hamburger Icon, Store Logo, Store Name (Default Marketplace Alignment) -->
            <div class="flex items-center gap-2.5 sm:gap-4 shrink-0">
                
                <!-- Hamburger Menu Trigger (Opens Navigation Drawer) -->
                <button type="button" 
                        @click="mobileMenuOpen = true"
                        class="p-2 -ml-1 rounded-2xl transition-all duration-200 flex items-center justify-center cursor-pointer"
                        :class="{
                            'text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800': isScrolled,
                            'text-white hover:bg-white/20': !isScrolled
                        }"
                        aria-label="Open storefront navigation menu">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <!-- Store Brand Logo & Name -->
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-2.5 sm:gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="relative p-1 rounded-2xl transition-all duration-300 group-hover:scale-105 shrink-0"
                             :class="{
                                 'bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700': isScrolled,
                                 'bg-white/20 backdrop-blur-md border border-white/30': !isScrolled
                             }">
                            <img src="{{ $storeLogo }}" 
                                 alt="{{ $storeName }}" 
                                 style="height: {{ $logoHeight }}px;" 
                                 class="w-auto object-contain rounded-xl"
                                 onerror="this.style.display='none'; document.getElementById('hdr-fallback-name').style.display='block';">
                        </div>
                        <span id="hdr-fallback-name" style="display:none;" class="text-base sm:text-lg font-black font-heading truncate max-w-[140px] sm:max-w-[200px]">
                            {{ $storeName }}
                        </span>
                    @else
                        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-2xl flex items-center justify-center font-black text-white text-sm sm:text-base shadow-sm shrink-0 border border-white/25"
                             style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                    @endif

                    <!-- Store Name Typography -->
                    <div class="flex flex-col">
                        <span class="text-sm sm:text-lg font-black tracking-tight font-heading leading-tight truncate max-w-[130px] sm:max-w-[220px] transition-colors"
                              :class="{
                                  'text-zinc-950 dark:text-white': isScrolled,
                                  'text-white': !isScrolled
                              }">
                            {{ $storeName }}
                        </span>
                        @if(!empty($brandCfg['city']))
                            <span class="text-[9px] uppercase tracking-widest font-semibold transition-colors truncate"
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

            <!-- CENTER REGION: Desktop Category Quick Navigation / In-Store Search -->
            <div class="hidden md:flex items-center gap-3 flex-1 justify-center max-w-md">
                @if($showSearch)
                    <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="w-full relative">
                        <input type="text" 
                               name="search" 
                               value="{{ $searchQuery }}" 
                               placeholder="Search catalog in {{ $storeName }}..." 
                               class="w-full pl-9 pr-4 py-2 text-xs rounded-full transition-all focus:outline-none"
                               :class="{
                                   'bg-zinc-100/80 dark:bg-zinc-800/80 text-zinc-900 dark:text-white border border-zinc-200 dark:border-zinc-700 focus:bg-white dark:focus:bg-zinc-900 focus:ring-2 focus:ring-zinc-900/20': isScrolled,
                                   'bg-white/15 backdrop-blur-md text-white placeholder:text-white/70 border border-white/20 focus:bg-black/40': !isScrolled
                               }">
                        <svg class="w-4 h-4 absolute left-3 top-2.5 transition-colors" 
                             :class="{ 'text-zinc-400': isScrolled, 'text-white/70': !isScrolled }"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </form>
                @endif
            </div>

            <!-- RIGHT REGION: Shopping Bag / Cart Button (Dispatches to Glass Popup Drawer) -->
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                
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
                        class="group relative flex items-center gap-2 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300 hover:scale-105 cursor-pointer shadow-md"
                        :class="{
                            'bg-zinc-950 text-white hover:bg-black border border-zinc-800': isScrolled,
                            'bg-white text-zinc-950 hover:bg-zinc-100 border border-white/50 shadow-lg': !isScrolled
                        }"
                        aria-label="Open Shopping Bag Popup">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="font-mono text-xs font-black">Bag</span>
                    <span id="cart-counter-badge" 
                          class="min-w-[19px] h-[19px] px-1 text-[10px] font-black rounded-full flex items-center justify-center transition-all shadow-xs"
                          style="background-color: var(--sf-accent, #c59b27); color: #000000;">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </div>

        <!-- FULL-SCREEN / SIDE NAVIGATION DRAWER (Opened via Hamburger Menu) -->
        <div x-show="mobileMenuOpen" 
             x-cloak
             class="fixed inset-0 z-50 flex"
             role="dialog" 
             aria-modal="true">
            
            <!-- Backdrop -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="mobileMenuOpen = false"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm"></div>

            <!-- Drawer Container -->
            <div x-show="mobileMenuOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="relative mr-auto w-full max-w-xs sm:max-w-sm h-full bg-white dark:bg-zinc-950 shadow-2xl p-6 flex flex-col justify-between overflow-y-auto">
                
                <div>
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-200 dark:border-zinc-800">
                        <div class="flex items-center gap-3">
                            @if(!empty($storeLogo))
                                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-8 w-auto object-contain rounded-lg">
                            @endif
                            <span class="font-black font-heading text-lg text-zinc-900 dark:text-white truncate">
                                {{ $storeName }}
                            </span>
                        </div>
                        <button type="button" 
                                @click="mobileMenuOpen = false"
                                class="p-2 rounded-xl text-zinc-500 hover:text-zinc-900 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Search Form -->
                    <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="mt-5 relative">
                        <input type="text" 
                               name="search" 
                               value="{{ $searchQuery }}" 
                               placeholder="Search catalog..." 
                               class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-zinc-200 dark:border-zinc-800 bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:bg-white focus:outline-none focus:ring-2 focus:ring-zinc-900">
                        <svg class="w-4 h-4 text-zinc-400 absolute left-3 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </form>

                    <!-- Catalog Navigation Links -->
                    <div class="mt-6 space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-zinc-400 block px-3 mb-2">Boutique Sections</span>
                        <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                           @click="mobileMenuOpen = false"
                           class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs font-bold text-zinc-900 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                            <span>All Products</span>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-zinc-100 dark:bg-zinc-800">{{ $products->total() ?? 0 }}</span>
                        </a>

                        @foreach($vendorCategories as $cat)
                            <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}#catalog" 
                               @click="mobileMenuOpen = false"
                               class="flex items-center justify-between px-3.5 py-2 rounded-xl text-xs font-medium text-zinc-600 dark:text-zinc-400 hover:text-zinc-950 dark:hover:text-white hover:bg-zinc-100 dark:hover:bg-zinc-900 transition">
                                <span>{{ $cat->name }}</span>
                                <span class="text-[10px] font-mono text-zinc-400">{{ $cat->products_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Drawer Footer -->
                <div class="pt-6 border-t border-zinc-200 dark:border-zinc-800 space-y-3">
                    @if($waUrl)
                        <a href="{{ $waUrl }}" target="_blank" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-sm transition">
                            <span>💬 WhatsApp Stylist Desk</span>
                        </a>
                    @endif
                    <a href="{{ route('stores.index') }}" class="block text-center text-[11px] font-semibold text-zinc-500 hover:text-zinc-800 transition">
                        &larr; Return to K71 Stores Directory
                    </a>
                </div>

            </div>
        </div>

    </header>
    </div>
</div>
