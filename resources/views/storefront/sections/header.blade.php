@php
    $headerCfg = $config['header'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    $themeCfg = $config['theme'] ?? [];
    
    $storeName = $brandCfg['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brandCfg['logo_url'] ?? $vendor->logo_url;
    $logoHeight = $headerCfg['logo_height'] ?? 38;
    $layout = $headerCfg['layout'] ?? 'split';
    $isSticky = $headerCfg['sticky'] ?? true;
    $showSearch = $headerCfg['show_search'] ?? true;
    $showCart = $headerCfg['show_cart'] ?? true;
    $showWishlist = $headerCfg['show_wishlist'] ?? true;
    
    $cartCount = 0;
    if (auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
    $waPhone = preg_replace('/[^0-9]/', '', (string) ($brandCfg['whatsapp'] ?? $vendor->contact_phone));
    if (strlen($waPhone) === 10) { $waPhone = '91' . $waPhone; }
    $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text=" . rawurlencode("Namaste {$storeName}! I am browsing your store on K71.") : null;

    $searchQuery = request('search', '');
@endphp

<header id="storefront-header" 
        data-section-id="header"
        class="w-full border-b transition-all duration-200 z-30 {{ $isSticky ? 'sticky top-0 backdrop-blur-md bg-opacity-95' : 'relative' }}"
        style="background-color: var(--sf-surface, #ffffff); border-color: rgba(0,0,0,0.06);"
        x-data="{ mobileMenuOpen: false, searchOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {{-- LAYOUT 1: SPLIT (Default Atelier & Luxury) --}}
        @if($layout === 'split' || $layout === 'minimal')
        <div class="flex items-center justify-between h-16 sm:h-20 gap-4">
            <!-- Mobile Menu Button -->
            <div class="flex items-center md:hidden">
                <button type="button" 
                        @click="mobileMenuOpen = !mobileMenuOpen" 
                        class="p-2 -ml-2 rounded-xl text-stone-700 hover:text-stone-900 hover:bg-stone-100 transition"
                        aria-label="Open navigation menu">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Store Brand / Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <img src="{{ $storeLogo }}" 
                             alt="{{ $storeName }}" 
                             style="height: {{ $logoHeight }}px;" 
                             class="w-auto object-contain transition-transform group-hover:scale-102"
                             onerror="this.style.display='none'; document.getElementById('logo-fallback-text').style.display='block';">
                        <span id="logo-fallback-text" style="display:none;" class="text-xl sm:text-2xl font-bold tracking-tight text-stone-900 group-hover:opacity-80 transition font-heading">
                            {{ $storeName }}
                        </span>
                    @else
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-white text-base shadow-sm shrink-0"
                             style="background-color: var(--sf-primary, #18181b);">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg sm:text-xl font-bold tracking-tight text-stone-900 font-heading leading-tight">
                                {{ $storeName }}
                            </span>
                            @if(!empty($brandCfg['city']))
                                <span class="text-[10px] uppercase tracking-widest text-stone-400 font-semibold">
                                    {{ $brandCfg['city'] }}
                                </span>
                            @endif
                        </div>
                    @endif
                </a>
            </div>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center space-x-6 text-sm font-medium text-stone-600">
                <a href="{{ route('storefront.show', $vendor->slug) }}" 
                   class="hover:text-stone-900 transition {{ !request()->filled('category') && !request()->filled('search') ? 'text-stone-950 font-bold border-b-2 border-stone-900 pb-0.5' : '' }}">
                    All Items
                </a>
                @foreach($vendorCategories->take(4) as $cat)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}" 
                       class="hover:text-stone-900 transition {{ request('category') === $cat->slug ? 'text-stone-950 font-bold border-b-2 border-stone-900 pb-0.5' : '' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
                <a href="#story" class="hover:text-stone-900 transition">About Studio</a>
                <a href="#contact" class="hover:text-stone-900 transition">Client Help</a>
            </nav>

            <!-- Actions: Search, Wishlist, Cart, WhatsApp -->
            <div class="flex items-center gap-2 sm:gap-3">
                @if($showSearch)
                    <div class="relative hidden sm:block">
                        <form action="{{ route('storefront.show', $vendor->slug) }}" method="GET">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $searchQuery }}" 
                                   placeholder="Search {{ $storeName }}..." 
                                   class="pl-9 pr-4 py-1.5 text-xs rounded-full border border-stone-200 bg-stone-50/70 focus:bg-white focus:outline-none focus:ring-1 focus:ring-stone-900 focus:border-stone-900 w-36 lg:w-48 transition-all">
                            <svg class="w-3.5 h-3.5 text-stone-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </form>
                    </div>
                @endif

                @if($waUrl)
                    <a href="{{ $waUrl }}" 
                       target="_blank" 
                       rel="noopener"
                       title="Chat with {{ $storeName }}"
                       class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold text-emerald-800 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>WhatsApp Stylist</span>
                    </a>
                @endif

                @if($showCart)
                    <button type="button" 
                            onclick="if(window.openK71Cart) { window.openK71Cart(); } else { window.location.href='{{ route('cart.index') }}'; }" 
                            class="relative p-2 rounded-xl text-stone-700 hover:text-stone-950 hover:bg-stone-100 transition"
                            aria-label="View shopping bag">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span id="cart-counter-badge" 
                              class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white rounded-full flex items-center justify-center shadow-xs transition-transform {{ $cartCount > 0 ? 'scale-100' : 'scale-0' }}"
                              style="background-color: var(--sf-accent, #c59b27);">
                            {{ $cartCount }}
                        </span>
                    </button>
                @endif
            </div>
        </div>

        {{-- LAYOUT 2: SEARCH PROMINENT (Commerce & High Velocity) --}}
        @elseif($layout === 'search_prominent')
        <div class="py-3 sm:py-4 flex flex-col sm:flex-row items-center justify-between gap-3 sm:gap-6">
            <div class="w-full sm:w-auto flex items-center justify-between">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3">
                    @if(!empty($storeLogo))
                        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" style="height: {{ $logoHeight }}px;" class="w-auto object-contain">
                    @else
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center font-bold text-white text-base shadow-sm shrink-0"
                             style="background-color: var(--sf-primary, #1e40af);">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                        <span class="text-xl font-black tracking-tight text-stone-900 font-heading">
                            {{ $storeName }}
                        </span>
                    @endif
                </a>

                <div class="flex items-center gap-2 sm:hidden">
                    <button type="button" 
                            onclick="if(window.openK71Cart) { window.openK71Cart(); } else { window.location.href='{{ route('cart.index') }}'; }" 
                            class="relative p-2 rounded-xl text-stone-800">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span class="absolute top-0 right-0 min-w-[16px] h-[16px] px-1 text-[9px] font-bold text-white rounded-full flex items-center justify-center bg-blue-600">
                            {{ $cartCount }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Large Center Search Bar -->
            <div class="w-full flex-1 max-w-xl">
                <form action="{{ route('storefront.show', $vendor->slug) }}" method="GET" class="relative">
                    <input type="text" 
                           name="search" 
                           value="{{ $searchQuery }}" 
                           placeholder="Search from {{ $products->total() ?? 'all' }} items in {{ $storeName }}..." 
                           class="w-full pl-10 pr-24 py-2 text-xs sm:text-sm rounded-full border border-stone-300 bg-stone-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition shadow-inner">
                    <svg class="w-4 h-4 text-stone-400 absolute left-3.5 top-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <button type="submit" 
                            class="absolute right-1.5 top-1 px-4 py-1.5 rounded-full text-xs font-bold text-white bg-stone-900 hover:bg-stone-800 transition">
                        Find
                    </button>
                </form>
            </div>

            <!-- Actions Right -->
            <div class="hidden sm:flex items-center gap-3 shrink-0">
                @if($waUrl)
                    <a href="{{ $waUrl }}" target="_blank" class="px-3 py-2 rounded-xl text-xs font-bold bg-emerald-500/10 text-emerald-700 hover:bg-emerald-500/20 transition flex items-center gap-1.5">
                        <span>💬 Quick Chat</span>
                    </a>
                @endif
                <button type="button" 
                        onclick="if(window.openK71Cart) { window.openK71Cart(); } else { window.location.href='{{ route('cart.index') }}'; }" 
                        class="flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold text-white bg-stone-900 hover:bg-stone-800 transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span>Bag ({{ $cartCount }})</span>
                </button>
            </div>
        </div>

        {{-- LAYOUT 3: CENTERED OR STACKED (Signature & Bold) --}}
        @else
        <div class="flex items-center justify-between h-16 sm:h-20">
            <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="p-2 md:hidden text-inherit">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex-1 text-center">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="inline-block">
                    @if(!empty($storeLogo))
                        <img src="{{ $storeLogo }}" alt="{{ $storeName }}" style="height: {{ $logoHeight }}px;" class="h-auto max-h-12 mx-auto object-contain">
                    @else
                        <span class="text-xl sm:text-2xl font-black uppercase tracking-wider font-heading">
                            {{ $storeName }}
                        </span>
                    @endif
                </a>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" 
                        onclick="if(window.openK71Cart) { window.openK71Cart(); } else { window.location.href='{{ route('cart.index') }}'; }" 
                        class="relative p-2 rounded-full hover:bg-stone-100 dark:hover:bg-zinc-800 transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span class="absolute top-0 right-0 w-4 h-4 rounded-full bg-emerald-500 text-black text-[9px] font-black flex items-center justify-center">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>
        </div>
        @endif
    </div>

    <!-- Mobile Navigation Drawer -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="md:hidden border-t border-stone-200 bg-white dark:bg-zinc-900 px-4 py-6 shadow-xl space-y-4"
         style="display: none;">
        <!-- Mobile Search -->
        <form action="{{ route('storefront.show', $vendor->slug) }}" method="GET" class="relative">
            <input type="text" 
                   name="search" 
                   value="{{ $searchQuery }}" 
                   placeholder="Search {{ $storeName }}..." 
                   class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
            <svg class="w-4 h-4 text-stone-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </form>

        <!-- Categories List -->
        <div class="space-y-1">
            <div class="text-[10px] uppercase font-bold text-stone-400 tracking-wider px-2">Store Departments</div>
            <a href="{{ route('storefront.show', $vendor->slug) }}" class="block px-3 py-2 rounded-lg text-sm font-semibold text-stone-800 hover:bg-stone-50">
                All Products ({{ $products->total() ?? 0 }})
            </a>
            @foreach($vendorCategories as $cat)
                <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}" 
                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm text-stone-700 hover:bg-stone-50">
                    <span>{{ $cat->name }}</span>
                    <span class="text-xs text-stone-400 font-semibold">{{ $cat->products_count }}</span>
                </a>
            @endforeach
        </div>

        @if($waUrl)
            <div class="pt-2 border-t border-stone-100">
                <a href="{{ $waUrl }}" target="_blank" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-sm">
                    <span>💬 WhatsApp Studio Stylist</span>
                </a>
            </div>
        @endif
    </div>
</header>
