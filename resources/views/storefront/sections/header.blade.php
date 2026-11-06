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
    $activeCategory = request('category', '');
@endphp

<!-- FLOATING LIQUID GLASS HEADER CAPSULE (1-FORM COMPACT WITH SLIDE-IN SEARCH & CATEGORIES) -->
<div class="w-full fixed top-0 inset-x-0 z-50 transition-all duration-400 ease-[cubic-bezier(0.16,1,0.3,1)]"
     x-data="{ 
         mobileMenuOpen: false, 
         expandDrawer: false,
         isScrolled: false,
         init() {
             this.checkScroll();
             window.addEventListener('scroll', () => this.checkScroll(), { passive: true });
         },
         checkScroll() {
             this.isScrolled = window.pageYOffset > 45;
         }
     }"
     @click.outside="expandDrawer = false"
     @keydown.escape.window="expandDrawer = false; mobileMenuOpen = false;">
    
    {{-- Top Announcement Bar --}}
    @include('storefront.sections.announcement')

    <div class="px-3 sm:px-6 transition-all duration-400"
         :class="{
             'pt-2 sm:pt-3': isScrolled,
             'pt-3 sm:pt-5': !isScrolled
         }">
    
        <header id="storefront-header"
                class="max-w-7xl mx-auto rounded-3xl transition-all duration-400 ease-[cubic-bezier(0.16,1,0.3,1)] overflow-visible"
                :class="{
                    'bg-white/95 dark:bg-zinc-950/95 backdrop-blur-2xl shadow-[0_12px_40px_rgba(0,0,0,0.12)] border border-zinc-200/80 dark:border-zinc-800/80 text-zinc-900 dark:text-white': isScrolled || expandDrawer,
                    '{{ $transparentOnHero ? 'bg-black/30 backdrop-blur-md border border-white/25 text-white shadow-none' : 'bg-white/90 backdrop-blur-xl border border-stone-200/80 text-stone-900 shadow-sm' }}': !isScrolled && !expandDrawer
                }">
            
            <!-- 1ST FORM: MAIN COMPACT SINGLE-LINE HEADER BAR -->
            <div class="px-3.5 sm:px-6 h-14 sm:h-16 flex items-center justify-between gap-2.5 sm:gap-4">
                
                <!-- LEFT REGION: Hamburger Icon + Logo + Store Name -->
                <div class="flex items-center gap-2 sm:gap-3.5 shrink-0">
                    
                    <!-- Hamburger Menu Trigger (Opens Navigation Side Drawer) -->
                    <button type="button" 
                            @click="mobileMenuOpen = true"
                            class="p-2 -ml-1 rounded-2xl transition-all duration-200 flex items-center justify-center cursor-pointer"
                            :class="{
                                'text-zinc-800 dark:text-white hover:bg-zinc-100 dark:hover:bg-zinc-800': isScrolled || expandDrawer,
                                'text-white hover:bg-white/20': !isScrolled && !expandDrawer
                            }"
                            aria-label="Open boutique menu">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Store Logo & Name -->
                    <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-2 sm:gap-3 group">
                        @if(!empty($storeLogo))
                            <div class="relative p-1 rounded-2xl transition-all duration-300 group-hover:scale-105 shrink-0"
                                 :class="{
                                     'bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700': isScrolled || expandDrawer,
                                     'bg-white/20 backdrop-blur-md border border-white/30': !isScrolled && !expandDrawer
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
                            <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-2xl flex items-center justify-center font-black text-white text-xs sm:text-base shadow-sm shrink-0 border border-white/25"
                                 style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                                {{ strtoupper(substr($storeName, 0, 1)) }}
                            </div>
                        @endif

                        <!-- Brand Typography -->
                        <div class="flex flex-col">
                            <span class="text-xs sm:text-base font-black tracking-tight font-heading leading-tight truncate max-w-[120px] sm:max-w-[200px] transition-colors"
                                  :class="{
                                      'text-zinc-950 dark:text-white': isScrolled || expandDrawer,
                                      'text-white': !isScrolled && !expandDrawer
                                  }">
                                {{ $storeName }}
                            </span>
                            @if(!empty($brandCfg['city']))
                                <span class="text-[8.5px] uppercase tracking-widest font-semibold transition-colors truncate"
                                      :class="{
                                          'text-zinc-500 dark:text-zinc-400': isScrolled || expandDrawer,
                                          'text-white/80': !isScrolled && !expandDrawer
                                      }">
                                    📍 {{ $brandCfg['city'] }} · Studio
                                </span>
                            @endif
                        </div>
                    </a>
                </div>

                <!-- CENTER REGION: Down Arrow Trigger (Slides In Form 2 Search & Form 3 Categories) -->
                <div class="flex items-center justify-center">
                    <button type="button" 
                            @click="expandDrawer = !expandDrawer"
                            class="inline-flex items-center gap-1.5 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-xs font-bold transition-all duration-300 border shadow-xs cursor-pointer select-none"
                            :class="{
                                'bg-zinc-900 text-white border-zinc-900 dark:bg-white dark:text-zinc-950 scale-105 shadow-md': expandDrawer,
                                'bg-zinc-100/90 dark:bg-zinc-800/90 text-zinc-800 dark:text-zinc-200 border-zinc-300 dark:border-zinc-700 hover:bg-zinc-200': isScrolled && !expandDrawer,
                                'bg-white/20 backdrop-blur-md text-white border-white/30 hover:bg-white/30': !isScrolled && !expandDrawer
                            }"
                            aria-expanded="false"
                            :aria-expanded="expandDrawer.toString()">
                        
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        
                        <span class="hidden sm:inline" x-text="expandDrawer ? 'Close Explore' : 'Search & Collections'">Search &amp; Collections</span>
                        <span class="sm:hidden" x-text="expandDrawer ? 'Close' : 'Explore'">Explore</span>

                        <!-- Interactive Down/Up Animated Chevron -->
                        <svg class="w-3.5 h-3.5 transition-transform duration-300 transform" 
                             :class="{ 'rotate-180': expandDrawer }"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                <!-- RIGHT REGION: Shopping Bag & Stylist -->
                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                    
                    @if($waUrl)
                        <a href="{{ $waUrl }}" 
                           target="_blank" 
                           rel="noopener"
                           title="Direct WhatsApp Stylist"
                           class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-xs"
                           :class="{
                               'bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100': isScrolled || expandDrawer,
                               'bg-white/15 backdrop-blur-md text-white border border-white/25 hover:bg-white/25': !isScrolled && !expandDrawer
                           }">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Stylist Desk</span>
                        </a>
                    @endif

                    <!-- Cart / Bag Trigger Button (Opens Glassmorphic Cart Popup Drawer) -->
                    <button type="button" 
                            onclick="if(window.openK71Cart){ window.openK71Cart(); } else { window.location.href='{{ route('cart.index') }}'; }" 
                            class="group relative flex items-center gap-1.5 sm:gap-2 px-3 sm:px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-wider transition-all duration-300 hover:scale-105 cursor-pointer shadow-md"
                            :class="{
                                'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950': isScrolled || expandDrawer,
                                'bg-white text-zinc-950': !isScrolled && !expandDrawer
                            }"
                            aria-label="Open shopping bag">
                        
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        
                        <span class="font-sans">Bag</span>
                        
                        <!-- Real-time Count Badge -->
                        <span class="ml-0.5 px-1.5 py-0.2 rounded-full text-[10px] font-black font-mono transition-transform group-hover:scale-110"
                              :class="{
                                  'bg-white text-black dark:bg-black dark:text-white': isScrolled || expandDrawer,
                                  'bg-black text-white': !isScrolled && !expandDrawer
                              }">
                            {{ $cartCount }}
                        </span>
                    </button>

                </div>
            </div>

            <!-- SLIDE-IN ACCORDION: FORMS 2 & 3 (SLIDES DOWN ON DOWN ARROW CLICK) -->
            <div x-show="expandDrawer" 
                 x-cloak
                 x-transition:enter="transition-all duration-300 ease-out"
                 x-transition:enter-start="opacity-0 -translate-y-3 max-h-0"
                 x-transition:enter-end="opacity-100 translate-y-0 max-h-[600px]"
                 x-transition:leave="transition-all duration-200 ease-in"
                 x-transition:leave-start="opacity-100 translate-y-0 max-h-[600px]"
                 x-transition:leave-end="opacity-0 -translate-y-3 max-h-0"
                 class="overflow-hidden border-t border-zinc-200/80 dark:border-zinc-800/80 px-4 sm:px-6 py-4 space-y-4 bg-white/98 dark:bg-zinc-950/98 backdrop-blur-2xl rounded-b-3xl shadow-2xl">
                
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-zinc-500 dark:text-zinc-400">Search Catalog &amp; Collections</span>
                    </div>
                    <button type="button" 
                            @click="expandDrawer = false" 
                            class="text-[11px] font-bold text-zinc-500 hover:text-zinc-900 dark:hover:text-white flex items-center gap-1 cursor-pointer transition">
                        <span>Close</span>
                        <span class="text-xs">✕</span>
                    </button>
                </div>

                <!-- FORM 2: FULL RESPONSIVE SEARCH BAR -->
                <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $searchQuery }}" 
                           placeholder="Search all items in {{ $storeName }}..." 
                           class="w-full pl-10 pr-24 py-2.5 sm:py-3 text-xs sm:text-sm rounded-2xl bg-zinc-100 dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-700 text-zinc-900 dark:text-white placeholder:text-zinc-400 focus:outline-none focus:ring-2 focus:ring-zinc-900/20">
                    <svg class="w-4 h-4 absolute left-3.5 top-3.5 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="submit" class="absolute right-2 top-2 px-4 py-1.5 sm:py-2 rounded-xl bg-zinc-900 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold hover:opacity-90 transition cursor-pointer">
                        Search
                    </button>
                </form>

                <!-- FORM 3: HORIZONTAL CATEGORY COLLECTIONS SLIDER (IMAGE ONLY / CIRCULAR 100PX BUBBLES) -->
                @if($vendorCategories->isNotEmpty())
                    <div class="space-y-2 pt-1 border-t border-zinc-100 dark:border-zinc-800/80">
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300">Quick Collections Filter</span>
                            @if(!empty($activeCategory))
                                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                                   @click="expandDrawer = false"
                                   class="text-[10px] font-bold text-amber-600 hover:underline">
                                    ✕ Clear Filter
                                </a>
                            @endif
                        </div>

                        <div class="flex items-center gap-3.5 overflow-x-auto pb-2 scrollbar-none pt-1">
                            <!-- All Items Circle -->
                            <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                               @click="expandDrawer = false"
                               class="flex flex-col items-center group shrink-0 text-center w-16 sm:w-20">
                                <div class="w-13 h-13 sm:w-15 sm:h-15 p-0.5 border-2 transition-all duration-300 {{ empty($activeCategory) ? 'border-zinc-900 dark:border-white scale-105 shadow-md' : 'border-zinc-200 dark:border-zinc-700 hover:scale-105' }}"
                                     style="border-radius: 100px;">
                                    <div class="w-full h-full bg-zinc-900 text-white flex flex-col items-center justify-center font-black text-[11px]"
                                         style="border-radius: 100px;">
                                        <span>ALL</span>
                                        <span class="text-[8px] opacity-75 font-mono">{{ $products->total() ?? 0 }}</span>
                                    </div>
                                </div>
                                <span class="text-[10.5px] font-bold text-zinc-800 dark:text-zinc-200 mt-1.5 truncate w-full">All Drops</span>
                            </a>

                            @foreach($vendorCategories as $cat)
                                @php
                                    $isCur = ($activeCategory === $cat->slug);
                                    $sampleImg = $cat->sample_image ? (str_starts_with($cat->sample_image, 'http') ? $cat->sample_image : asset('storage/' . $cat->sample_image)) : null;
                                @endphp
                                <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}#catalog" 
                                   @click="expandDrawer = false"
                                   class="flex flex-col items-center group shrink-0 text-center w-16 sm:w-20">
                                    <div class="w-13 h-13 sm:w-15 sm:h-15 p-0.5 border-2 transition-all duration-300 {{ $isCur ? 'border-zinc-900 dark:border-white scale-105 shadow-md' : 'border-zinc-200 dark:border-zinc-700 hover:scale-105' }}"
                                         style="border-radius: 100px;">
                                        <div class="w-full h-full overflow-hidden bg-zinc-100 flex items-center justify-center relative shadow-inner"
                                             style="border-radius: 100px;">
                                            @if($sampleImg)
                                                <img src="{{ $sampleImg }}" alt="{{ $cat->name }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300" style="border-radius: 100px;">
                                            @else
                                                <span class="text-lg">🏷️</span>
                                            @endif
                                        </div>
                                    </div>
                                    <span class="text-[10.5px] font-medium text-zinc-700 dark:text-zinc-300 mt-1.5 truncate w-full group-hover:text-zinc-950 dark:group-hover:text-white {{ $isCur ? 'font-black' : '' }}">
                                        {{ $cat->name }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>

            <!-- FULL SIDE NAVIGATION DRAWER (ACCESSED VIA HAMBURGER ICON) -->
            <div x-show="mobileMenuOpen" 
                 x-cloak
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 bg-black/70 backdrop-blur-sm flex justify-start"
                 @click.self="mobileMenuOpen = false">
                
                <div x-show="mobileMenuOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="w-full max-w-sm bg-white dark:bg-zinc-950 h-full shadow-2xl p-6 flex flex-col justify-between overflow-y-auto">
                    
                    <div>
                        <!-- Drawer Header -->
                        <div class="flex items-center justify-between pb-6 border-b border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                @if(!empty($storeLogo))
                                    <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-8 w-auto object-contain">
                                @endif
                                <span class="font-black font-heading text-lg text-zinc-950 dark:text-white">{{ $storeName }}</span>
                            </div>
                            <button type="button" 
                                    @click="mobileMenuOpen = false" 
                                    class="p-2 rounded-xl text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

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
