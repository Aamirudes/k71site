@php
    $headerCfg = $config['header'] ?? [];
    $brandCfg = $config['brand'] ?? [];
    $themeCfg = $config['theme'] ?? [];
    
    $storeName = $brandCfg['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brandCfg['logo_url'] ?? $vendor->logo_url;
    $logoHeight = $headerCfg['logo_height'] ?? 36;
    $isSticky = $headerCfg['sticky'] ?? true;
    $showSearch = $headerCfg['show_search'] ?? true;
    
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

<!-- FLOATING LIQUID GLASS HEADER CAPSULE -->
<div class="w-full px-3 sm:px-6 pt-3 sm:pt-4 {{ $isSticky ? 'sticky top-0 z-40' : 'relative z-20' }}"
     x-data="{ mobileMenuOpen: false }">
    <header id="storefront-header"
            class="max-w-7xl mx-auto rounded-3xl transition-all duration-300 shadow-[0_8px_30px_rgb(0,0,0,0.06)] border border-white/60 dark:border-white/10"
            style="background: rgba(255, 255, 255, 0.78); backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);">
        
        <div class="px-4 sm:px-6 h-16 sm:h-20 flex items-center justify-between gap-3 sm:gap-6">
            
            <!-- Mobile Menu Trigger -->
            <button type="button" 
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="p-2 -ml-2 rounded-2xl md:hidden text-stone-700 hover:text-stone-950 hover:bg-white/60 transition"
                    aria-label="Toggle navigation">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Store Brand / Liquid Logo -->
            <div class="flex items-center gap-3">
                <a href="{{ route('storefront.show', $vendor->slug) }}" class="flex items-center gap-3 group">
                    @if(!empty($storeLogo))
                        <div class="relative p-1 rounded-2xl bg-white/80 shadow-2xs border border-white/80 group-hover:scale-102 transition">
                            <img src="{{ $storeLogo }}" 
                                 alt="{{ $storeName }}" 
                                 style="height: {{ $logoHeight }}px;" 
                                 class="w-auto object-contain rounded-xl"
                                 onerror="this.style.display='none'; document.getElementById('hdr-fallback-name').style.display='block';">
                        </div>
                        <span id="hdr-fallback-name" style="display:none;" class="text-lg sm:text-xl font-bold font-heading text-stone-950">
                            {{ $storeName }}
                        </span>
                    @else
                        <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-bold text-white text-base shadow-sm shrink-0 border border-white/20"
                             style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                            {{ strtoupper(substr($storeName, 0, 1)) }}
                        </div>
                        <div class="flex flex-col">
                            <span class="text-base sm:text-lg font-black tracking-tight text-stone-950 font-heading leading-tight group-hover:opacity-85 transition">
                                {{ $storeName }}
                            </span>
                            @if(!empty($brandCfg['city']))
                                <span class="text-[9px] uppercase tracking-widest text-stone-400 font-bold">
                                    {{ $brandCfg['city'] }} · Studio
                                </span>
                            @endif
                        </div>
                    @endif
                </a>
            </div>

            <!-- Center Navigation: Liquid Frosted Category Pills -->
            <nav class="hidden md:flex items-center gap-1.5 p-1 rounded-full bg-stone-100/70 border border-stone-200/60 shadow-inner">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" 
                   class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ !request()->filled('category') && !request()->filled('search') ? 'bg-white text-stone-950 shadow-xs' : 'text-stone-600 hover:text-stone-950 hover:bg-white/50' }}">
                    All Items
                </a>
                @foreach($vendorCategories->take(4) as $cat)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}#catalog" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-semibold transition-all {{ request('category') === $cat->slug ? 'bg-white text-stone-950 shadow-xs font-bold' : 'text-stone-600 hover:text-stone-950 hover:bg-white/50' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </nav>

            <!-- Right Actions: Liquid Search, WhatsApp & Floating Popup Cart Capsule -->
            <div class="flex items-center gap-2 sm:gap-3">
                @if($showSearch)
                    <div class="relative hidden sm:block">
                        <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET">
                            <input type="text" 
                                   name="search" 
                                   value="{{ $searchQuery }}" 
                                   placeholder="Search catalog..." 
                                   class="pl-8 pr-3 py-1.5 text-xs rounded-full border border-stone-200/80 bg-white/70 focus:bg-white focus:outline-none focus:ring-1 focus:ring-stone-800 w-32 md:w-44 transition-all">
                            <svg class="w-3.5 h-3.5 text-stone-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </form>
                    </div>
                @endif

                @if($waUrl)
                    <a href="{{ $waUrl }}" 
                       target="_blank" 
                       rel="noopener"
                       title="WhatsApp Stylist"
                       class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold text-emerald-800 bg-emerald-500/10 border border-emerald-400/30 hover:bg-emerald-500/20 transition shadow-2xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Stylist</span>
                    </a>
                @endif

                <!-- FLOATING LIQUID CART POPUP BUTTON -->
                <button type="button" 
                        onclick="if(window.openK71Cart){ window.openK71Cart(); } else { window.location.href='{{ route('cart.index') }}'; }" 
                        class="group relative flex items-center gap-2 px-3.5 sm:px-4 py-2 rounded-2xl text-xs font-extrabold uppercase tracking-wider text-white shadow-md hover:shadow-lg transition-all duration-300 hover:scale-102 cursor-pointer"
                        style="background: linear-gradient(135deg, var(--sf-primary, #18181b), #27272a); border: 1px solid rgba(255, 255, 255, 0.2);"
                        aria-label="Open Shopping Bag Popup">
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <span class="hidden sm:inline">Bag</span>
                    <span id="cart-counter-badge" 
                          class="min-w-[18px] h-[18px] px-1 text-[10px] font-black text-stone-950 rounded-full flex items-center justify-center transition shadow-xs"
                          style="background-color: var(--sf-accent, #c59b27);">
                        {{ $cartCount }}
                    </span>
                </button>
            </div>

        </div>

        <!-- Mobile Drawer Navigation -->
        <div x-show="mobileMenuOpen" 
             x-collapse
             class="md:hidden border-t border-stone-200/60 p-4 space-y-3 bg-white/90 rounded-b-3xl"
             style="display: none;">
            <form action="{{ route('storefront.show', $vendor->slug) }}#catalog" method="GET" class="relative">
                <input type="text" 
                       name="search" 
                       value="{{ $searchQuery }}" 
                       placeholder="Search products in store..." 
                       class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-stone-200 bg-stone-50 focus:bg-white focus:outline-none">
                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </form>

            <div class="space-y-1 pt-1">
                <a href="{{ route('storefront.show', $vendor->slug) }}#catalog" class="block px-3 py-2 rounded-xl text-xs font-bold text-stone-900 hover:bg-stone-100">
                    All Products ({{ $products->total() ?? 0 }})
                </a>
                @foreach($vendorCategories as $cat)
                    <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'category' => $cat->slug]) }}#catalog" 
                       class="flex items-center justify-between px-3 py-2 rounded-xl text-xs text-stone-700 hover:bg-stone-100">
                        <span>{{ $cat->name }}</span>
                        <span class="text-[10px] text-stone-400 font-bold px-1.5 py-0.5 rounded-full bg-stone-100">{{ $cat->products_count }}</span>
                    </a>
                @endforeach
            </div>

            @if($waUrl)
                <div class="pt-2 border-t border-stone-100">
                    <a href="{{ $waUrl }}" target="_blank" class="w-full py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-sm">
                        <span>💬 Message Stylist on WhatsApp</span>
                    </a>
                </div>
            @endif
        </div>

    </header>
</div>
