@php
    $brandCfg = $config['brand'] ?? [];
    $headerCfg = $config['header'] ?? [];
    $storeName = $brandCfg['store_name'] ?? $vendor->shop_name;
    $storeLogo = $brandCfg['logo_url'] ?? $vendor->logo_url;
    
    $cartCount = 0;
    if (empty($isServiceWebsite) && auth()->check()) {
        $cart = \App\Models\Cart::where('user_id', auth()->id())->first();
        $cartCount = $cart ? $cart->items()->sum('quantity') : 0;
    }
    
    $hidePublicPhone = !empty($isServiceWebsite) && empty($providerSite['contact']['show_phone']);
    $waPhone = $hidePublicPhone ? null : preg_replace('/[^0-9]/', '', (string) ($brandCfg['whatsapp'] ?? $vendor->contact_phone));
    if ($waPhone && strlen($waPhone) === 10) { $waPhone = '91' . $waPhone; }
    $waUrl = $waPhone ? "https://wa.me/{$waPhone}?text=" . rawurlencode(!empty($isServiceWebsite) ? "Hi {$storeName}, I viewed your portfolio site." : "Namaste {$storeName}! I am viewing your boutique on K71.") : null;
    $searchQuery = request('search', '');
    $isDark = in_array($presetKey ?? '', ['noire', 'orbit', 'nexus', 'elysium', 'kinetic']) || !empty($theme['dark_mode']);
@endphp

<!-- UNIVERSAL SLIDE-FROM-LEFT FULL-VH MENU DRAWER (TELEPORTED TO BODY FOR ABSOLUTE TOP Z-INDEX) -->
<template x-teleport="body">
    <div x-show="drawerOpen" 
         x-cloak
         x-effect="if (drawerOpen) { document.body.style.overflow = 'hidden'; } else { document.body.style.overflow = ''; }"
         class="relative"
         style="z-index: 9999999 !important;">

        <!-- Full-Screen Backdrop -->
        <div x-show="drawerOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; background-color: rgba(0, 0, 0, 0.85) !important; backdrop-filter: blur(6px) !important; z-index: 9999998 !important;"
             @click="drawerOpen = false">
        </div>

        <!-- Slide Drawer Panel (Slides Left to Front Half Full VH) -->
        <div x-show="drawerOpen"
             x-transition:enter="transform transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-350"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition ease-[cubic-bezier(0.16,1,0.3,1)] duration-250"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full"
             style="position: fixed !important; top: 0 !important; bottom: 0 !important; left: 0 !important; height: 100vh !important; min-height: 100vh !important; z-index: 9999999 !important; box-shadow: 0 25px 80px rgba(0,0,0,0.85) !important;"
             class="w-[88vw] sm:w-[420px] md:w-[460px] max-w-xl {{ $isDark ? 'bg-[#090b10] text-zinc-100 border-zinc-800' : 'bg-white text-zinc-900 border-stone-200' }} flex flex-col justify-between overflow-y-auto border-r selection:bg-amber-100"
             @keydown.window.escape="drawerOpen = false">
            
            <div class="p-6 sm:p-8 space-y-6">
                <!-- Top: Brand Identity & Close -->
                <div class="flex items-center justify-between pb-5 border-b {{ $isDark ? 'border-zinc-800' : 'border-stone-200' }}">
                    <div class="flex items-center gap-3.5 min-w-0">
                        @if(!empty($storeLogo))
                            <div class="p-1 rounded-2xl {{ $isDark ? 'bg-zinc-900 border-zinc-700' : 'bg-stone-100 border-stone-200' }} border shrink-0">
                                <img src="{{ $storeLogo }}" alt="{{ $storeName }}" class="h-9 sm:h-10 w-auto max-w-[100px] object-contain rounded-xl">
                            </div>
                        @else
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center font-black text-white text-base shadow-sm shrink-0 border border-white/20"
                                 style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                                {{ strtoupper(substr($storeName, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0">
                            <span class="font-black font-heading text-lg sm:text-xl truncate block">
                                {{ $storeName }}
                            </span>
                            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-500">
                                @if(!empty($isServiceWebsite))
                                    <span>✦</span> {{ $providerSite['identity']['provider_mode_label'] ?? 'Portfolio Website' }}
                                @else
                                    <span>✦</span> Verified Boutique Studio
                                @endif
                            </span>
                        </div>
                    </div>
                    <button type="button" 
                            @click="drawerOpen = false" 
                            class="w-10 h-10 rounded-2xl {{ $isDark ? 'bg-zinc-900 text-zinc-400 hover:text-white' : 'bg-stone-100 text-stone-500 hover:text-stone-900' }} flex items-center justify-center transition cursor-pointer shrink-0"
                            aria-label="Close navigation menu">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if(!empty($isServiceWebsite))
                    @php $navLabels = $providerSite['nav_labels'] ?? []; @endphp
                    <nav class="space-y-1.5">
                        <a href="#overview" @click="drawerOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold {{ $isDark ? 'hover:bg-zinc-900' : 'hover:bg-stone-100' }} transition">
                            {{ $navLabels['overview'] ?? 'Overview' }}
                        </a>
                        <a href="#portfolio" @click="drawerOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold {{ $isDark ? 'hover:bg-zinc-900' : 'hover:bg-stone-100' }} transition">
                            {{ $navLabels['work'] ?? 'Work' }}
                        </a>
                        <a href="#services" @click="drawerOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold {{ $isDark ? 'hover:bg-zinc-900' : 'hover:bg-stone-100' }} transition">
                            {{ $navLabels['services'] ?? 'Services' }}
                        </a>
                        <a href="#about" @click="drawerOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold {{ $isDark ? 'hover:bg-zinc-900' : 'hover:bg-stone-100' }} transition">
                            {{ $navLabels['about'] ?? 'About' }}
                        </a>
                        <a href="#reviews" @click="drawerOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold {{ $isDark ? 'hover:bg-zinc-900' : 'hover:bg-stone-100' }} transition">
                            {{ $navLabels['reviews'] ?? 'Reviews' }}
                        </a>
                        <a href="#contact" @click="drawerOpen = false" class="block px-4 py-3 rounded-2xl text-sm font-bold {{ $isDark ? 'bg-white text-black' : 'bg-black text-white' }} text-center mt-3">
                            {{ $providerSite['identity']['cta_primary_text'] ?? 'Start a Project' }}
                        </a>
                    </nav>
                @else
                <!-- Quick Search Form -->
                <div>
                    <form action="{{ $vendor->storefrontUrl([], 'catalog') }}" method="GET" class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ $searchQuery }}" 
                               placeholder="Search products in {{ $storeName }}..." 
                               class="w-full pl-10 pr-4 py-3 text-xs sm:text-sm font-medium rounded-2xl {{ $isDark ? 'bg-zinc-900 border-zinc-700 text-white placeholder-zinc-500' : 'bg-stone-100 border-stone-200 text-stone-900 placeholder-stone-400' }} border focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <svg class="w-4 h-4 {{ $isDark ? 'text-zinc-500' : 'text-stone-400' }} absolute left-3.5 top-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </form>
                </div>

                <!-- Interactive Bag Quick Link Strip Inside Menu -->
                <div class="p-4 rounded-2xl {{ $isDark ? 'bg-zinc-900/80 border-zinc-800' : 'bg-stone-50 border-stone-200' }} border flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl {{ $isDark ? 'bg-zinc-800 text-white' : 'bg-white text-black' }} flex items-center justify-center shadow-xs">
                            <svg class="w-5 h-5 {{ $isDark ? 'text-white' : 'text-black' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold">Shopping Bag</div>
                            <div class="text-[11px] {{ $isDark ? 'text-zinc-400' : 'text-stone-500' }}">
                                <span>{{ $cartCount }}</span> items in bag
                            </div>
                        </div>
                    </div>
                    <button type="button" 
                            @click="drawerOpen = false; if(window.openK71Cart) window.openK71Cart();"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-bold {{ $isDark ? 'bg-white text-black hover:bg-zinc-200' : 'bg-black text-white hover:bg-stone-800' }} transition cursor-pointer">
                        View Bag →
                    </button>
                </div>

                <!-- Catalog Departments -->
                <div class="space-y-1.5">
                    <span class="text-[10px] font-black uppercase tracking-[0.2em] {{ $isDark ? 'text-zinc-500' : 'text-stone-400' }} block px-2 mb-2">
                        Departments &amp; Categories
                    </span>

                    <a href="{{ $vendor->storefrontUrl([], 'catalog') }}" 
                       @click="drawerOpen = false"
                       class="flex items-center justify-between px-4 py-3 rounded-2xl text-xs sm:text-sm font-bold {{ $isDark ? 'hover:bg-zinc-900' : 'hover:bg-stone-100' }} transition">
                        <span class="flex items-center gap-2.5">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <span>All Products</span>
                        </span>
                        <span class="text-xs font-mono font-semibold px-2.5 py-0.5 rounded-full {{ $isDark ? 'bg-zinc-800 text-zinc-300' : 'bg-stone-100 text-stone-600' }}">
                            {{ $products->total() ?? $products->count() }}
                        </span>
                    </a>

                    @foreach($vendorCategories as $cat)
                        <a href="{{ $vendor->storefrontUrl(['category' => $cat->slug], 'catalog') }}" 
                           @click="drawerOpen = false"
                           class="flex items-center justify-between px-4 py-2.5 rounded-2xl text-xs sm:text-sm font-medium {{ $isDark ? 'text-zinc-300 hover:text-white hover:bg-zinc-900' : 'text-stone-700 hover:text-stone-950 hover:bg-stone-100' }} transition">
                            <span>{{ $cat->name }}</span>
                            <span class="text-[11px] font-mono {{ $isDark ? 'text-zinc-500 bg-zinc-800' : 'text-stone-400 bg-stone-100' }} px-2 py-0.5 rounded-full">
                                {{ $cat->products_count }}
                            </span>
                        </a>
                    @endforeach
                </div>

                <!-- Direct Anchor Links -->
                <div class="pt-2 space-y-1 border-t {{ $isDark ? 'border-zinc-800' : 'border-stone-200' }}">
                    <a href="{{ $vendor->storefrontUrl([], 'reviews') }}" 
                       @click="drawerOpen = false"
                       class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold {{ $isDark ? 'text-zinc-400 hover:text-white' : 'text-stone-500 hover:text-stone-900' }} transition">
                        <span>★</span> Genuine Customer Reviews
                    </a>
                    <a href="{{ $vendor->storefrontUrl([], 'happy_customers') }}" 
                       @click="drawerOpen = false"
                       class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold {{ $isDark ? 'text-zinc-400 hover:text-white' : 'text-stone-500 hover:text-stone-900' }} transition">
                        <span>📸</span> Happy Customer Stories
                    </a>
                    <a href="{{ $vendor->storefrontUrl([], 'trust') }}" 
                       @click="drawerOpen = false"
                       class="flex items-center gap-2.5 px-4 py-2 text-xs font-semibold {{ $isDark ? 'text-zinc-400 hover:text-white' : 'text-stone-500 hover:text-stone-900' }} transition">
                        <span>◈</span> Verified Workshop &amp; Dispatch Guarantees
                    </a>
                </div>
                @endif
            </div>

            <!-- Drawer Footer -->
            <div class="p-6 sm:p-8 border-t {{ $isDark ? 'border-zinc-800 bg-zinc-950/80' : 'border-stone-200 bg-stone-50' }} space-y-3">
                @if($waUrl)
                    <a href="{{ $waUrl }}" target="_blank" 
                       class="w-full py-3.5 px-4 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shadow-sm transition hover:scale-[1.01] cursor-pointer">
                        <span>💬</span>
                        <span>{{ !empty($isServiceWebsite) ? 'Message on WhatsApp' : 'Chat with Boutique Stylist on WhatsApp' }}</span>
                    </a>
                @endif
                @if(!empty($isServiceWebsite))
                    <a href="{{ route('services.provider', $vendor->slug) }}" 
                       class="block text-center text-xs font-semibold {{ $isDark ? 'text-zinc-400 hover:text-white' : 'text-stone-500 hover:text-stone-800' }} transition py-1">
                        View on K71 Services Marketplace →
                    </a>
                @else
                    <a href="{{ route('stores.index') }}" 
                       class="block text-center text-xs font-semibold {{ $isDark ? 'text-zinc-500 hover:text-zinc-300' : 'text-stone-500 hover:text-stone-800' }} transition py-1">
                        &larr; Return to K71 Marketplace Stores Directory
                    </a>
                @endif
            </div>

        </div>
    </div>
</template>
