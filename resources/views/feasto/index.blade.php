<x-layouts.public 
    title="Feasto — 10-Minute Hyperlocal Delivery | Blinkit Speed by K71"
    :fullWidth="true"
    :hideMainHeader="true"
>
    <!-- FEASTO REACTIVE BLINKIT APP CONTAINER -->
    <div class="min-h-screen bg-[#F4F6F8] text-slate-900 antialiased selection:bg-emerald-600 selection:text-white"
         x-data="feastoApp({
            initialProducts: {{ Js::from($allProducts) }},
            initialCategories: {{ Js::from($categories) }}
         })">

        <!-- ====================================================================
             1. THIN SLEEK TOP STRIP: BACK TO K71 MARKETPLACE
             ==================================================================== -->
        <div class="bg-black text-white text-xs border-b border-white/10 px-4 sm:px-6 py-2 flex items-center justify-between z-40 relative">
            <div class="flex items-center gap-3">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-zinc-300 hover:text-white transition font-medium">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to K71 Universal Marketplace</span>
                </a>
                <span class="text-zinc-600">|</span>
                <span class="text-emerald-400 font-bold text-[11px] hidden sm:inline flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Feasto 10-Min Hyperlocal Network
                </span>
            </div>
            <div class="flex items-center gap-4 text-[11px] text-zinc-400">
                <span class="hidden md:inline font-mono">IST UTC+5:30 (Rajasthan)</span>
                <a href="{{ route('delivery.onboarding') }}" class="text-amber-300 hover:text-amber-200 font-bold transition flex items-center gap-1">
                    <span>🛵 Earn as Delivery Partner</span>
                </a>
            </div>
        </div>

        <!-- ====================================================================
             2. BESPOKE BLINKIT APP HEADER (STICKY)
             ==================================================================== -->
        <header class="sticky top-0 z-30 bg-white border-b border-slate-200 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
                
                <!-- Left: Feasto Brand Logo & 10-Min Location Pill -->
                <div class="flex items-center gap-4 sm:gap-6 shrink-0">
                    <a href="{{ route('feasto.index') }}" class="flex items-center gap-2 group">
                        <div class="w-10 h-10 rounded-2xl bg-[#FFD700] text-black font-black flex items-center justify-center text-xl shadow-md group-hover:scale-105 transition-transform">
                            ⚡
                        </div>
                        <div>
                            <span class="text-2xl sm:text-3xl font-black tracking-tight text-slate-950 font-sans block leading-none">
                                feasto
                            </span>
                            <span class="text-[9.5px] font-black uppercase tracking-wider text-emerald-700 block leading-tight mt-0.5">
                                10 MINS DELIVERY
                            </span>
                        </div>
                    </a>

                    <!-- Location Selector (Blinkit Signature) -->
                    <div class="hidden sm:flex flex-col cursor-pointer border-l border-slate-200 pl-4 py-1"
                         @click="showLocationModal = true">
                        <div class="flex items-center gap-1 text-[11px] font-black uppercase text-slate-950 tracking-tight">
                            <span>Delivery in 10 minutes</span>
                        </div>
                        <div class="flex items-center gap-1 text-xs text-slate-500 font-semibold group hover:text-slate-900 transition">
                            <span class="truncate max-w-[170px]" x-text="selectedLocation">Makrana Central, Rajasthan</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-900 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Center: Wide Search Pill with Typewriter Animated Placeholder -->
                <div class="flex-1 max-w-2xl relative">
                    <div class="relative flex items-center">
                        <div class="absolute left-4 pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" 
                               x-model="searchQuery"
                               @input.debounce.200ms="filterProducts()"
                               :placeholder="currentPlaceholder"
                               class="w-full pl-11 pr-10 py-3 bg-[#F4F6F8] border border-slate-200 rounded-2xl text-xs sm:text-sm font-medium text-slate-900 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:ring-2 focus:ring-slate-950 transition-all outline-hidden shadow-2xs">
                        <button x-show="searchQuery" 
                                @click="searchQuery = ''; filterProducts()"
                                class="absolute right-3.5 text-slate-400 hover:text-slate-700 text-xs font-bold">
                            ✕
                        </button>
                    </div>
                </div>

                <!-- Right: Account & Cart Trigger Button -->
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('account') }}" class="hidden lg:flex items-center gap-1.5 text-xs font-bold text-slate-700 hover:text-black py-2 px-3 rounded-xl hover:bg-slate-100 transition">
                        <span>Account</span>
                    </a>

                    <!-- Blinkit Style Green Cart Pill -->
                    <button type="button" 
                            @click="cartDrawerOpen = true"
                            class="flex items-center gap-2.5 px-4 sm:px-5 py-2.5 sm:py-3 rounded-2xl bg-[#0C831F] hover:bg-[#0B721B] active:scale-95 text-white font-bold text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <div class="flex flex-col text-left leading-tight">
                            <span class="text-[10px] font-extrabold text-emerald-200 uppercase" x-text="cartCount > 0 ? `${cartCount} items` : 'My Cart'">My Cart</span>
                            <span class="text-xs sm:text-sm font-black" x-text="cartCount > 0 ? `₹${cartTotal}` : '₹0'">₹0</span>
                        </div>
                    </button>
                </div>

            </div>
        </header>

        <!-- Mobile Location Strip -->
        <div class="sm:hidden bg-slate-100 px-4 py-2 border-b border-slate-200 flex items-center justify-between text-xs cursor-pointer"
             @click="showLocationModal = true">
            <div class="flex items-center gap-1.5 font-bold text-slate-900 truncate">
                <span class="text-amber-500">⚡</span>
                <span>Delivery to:</span>
                <span class="font-normal underline truncate" x-text="selectedLocation">Makrana Central, Rajasthan</span>
            </div>
            <span class="text-slate-400 font-bold">Change ▾</span>
        </div>

        <!-- ====================================================================
             3. MAIN BLINKIT 2-COLUMN APP ARCHITECTURE
             ==================================================================== -->
        <main class="max-w-7xl mx-auto px-2 sm:px-6 lg:px-8 py-5">
            <div class="flex flex-col md:flex-row gap-5 items-start">
                
                <!-- LEFT CATEGORY RAIL (STICKY ON DESKTOP, PILL SCROLL ON MOBILE) -->
                <aside class="w-full md:w-64 shrink-0 md:sticky md:top-28 md:max-h-[calc(100vh-8rem)] md:overflow-y-auto no-scrollbar bg-white rounded-3xl p-3 border border-slate-200 shadow-xs">
                    <div class="text-[11px] font-black uppercase tracking-wider text-slate-400 px-3 py-2 hidden md:block">
                        Categories (Instant 10m)
                    </div>
                    
                    <div class="flex md:flex-col gap-1.5 overflow-x-auto md:overflow-visible pb-1 md:pb-0 no-scrollbar">
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button" 
                                    @click="selectCategory(cat.id)"
                                    :class="activeCategory === cat.id ? 'bg-[#0C831F] text-white shadow-xs font-bold' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold'"
                                    class="flex items-center justify-between px-3.5 py-2.5 rounded-2xl text-xs transition-all shrink-0 cursor-pointer text-left md:w-full group">
                                <div class="flex items-center gap-2.5 truncate">
                                    <span class="text-base" x-text="cat.icon"></span>
                                    <span class="truncate" x-text="cat.name"></span>
                                </div>
                                <span class="hidden md:inline-block text-[10px] font-mono px-2 py-0.5 rounded-full"
                                      :class="activeCategory === cat.id ? 'bg-white/20 text-white' : 'bg-slate-200/80 text-slate-600'"
                                      x-text="cat.count"></span>
                            </button>
                        </template>
                    </div>
                </aside>

                <!-- RIGHT PRODUCT FEED -->
                <section class="flex-1 min-w-0 w-full space-y-4">
                    
                    <!-- Feed Header -->
                    <div class="bg-white rounded-3xl p-4 sm:p-5 border border-slate-200 shadow-xs flex items-center justify-between flex-wrap gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-base sm:text-xl font-black text-slate-950 capitalize" x-text="getActiveCategoryTitle()">
                                    All Quick Items
                                </h1>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-mono font-bold" x-text="`${filteredProducts.length} items`"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Dispatched within 2 minutes from nearest K71 dark store hub</p>
                        </div>

                        <!-- 10-Min Speed Micro Banner -->
                        <div class="flex items-center gap-2 bg-[#FFF9E6] border border-[#FFE885] px-3 py-1.5 rounded-xl text-xs font-bold text-amber-950">
                            <span>⚡ Delivery in 10-12 mins guaranteed</span>
                        </div>
                    </div>

                    <!-- Products Grid (Blinkit Card Layout) -->
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                        <template x-for="product in filteredProducts" :key="product.id">
                            <div class="bg-white rounded-3xl p-3 border border-slate-200 hover:border-slate-400 hover:shadow-lg transition-all duration-200 flex flex-col justify-between group relative">
                                
                                <!-- Top Badges: 10 MINS & Discount -->
                                <div class="relative aspect-square rounded-2xl overflow-hidden bg-slate-50 mb-2.5 p-2 flex items-center justify-center">
                                    <img :src="product.featured_image" :alt="product.title" class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300">
                                    
                                    <!-- 10 MINS Pill Badge (Blinkit Signature) -->
                                    <div class="absolute top-2 left-2 bg-[#0C831F] text-white px-2 py-0.5 rounded-md text-[9px] font-black tracking-tight uppercase flex items-center gap-0.5 shadow-xs">
                                        <span>⚡</span>
                                        <span x-text="product.delivery_minutes || '10 MINS'">10 MINS</span>
                                    </div>

                                    <!-- Discount Pill -->
                                    <template x-if="product.compare_at_price > product.base_price">
                                        <div class="absolute bottom-2 left-2 bg-slate-900 text-white px-1.5 py-0.5 rounded text-[8.5px] font-black uppercase tracking-wider"
                                              x-text="`${Math.round(((product.compare_at_price - product.base_price) / product.compare_at_price) * 100)}% OFF`">
                                        </div>
                                    </template>
                                </div>

                                <!-- Body -->
                                <div class="space-y-1 flex-1 flex flex-col justify-between">
                                    <div>
                                        <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block truncate" x-text="product.brand || 'Fresh'"></span>
                                        <h3 class="text-xs sm:text-sm font-bold text-slate-900 leading-snug line-clamp-2" :title="product.title" x-text="product.title"></h3>
                                        <div class="text-[11px] text-slate-500 font-medium mt-0.5" x-text="product.weight || '1 unit'"></div>
                                    </div>

                                    <!-- Price & Blinkit ADD Button Row -->
                                    <div class="pt-2.5 mt-2 border-t border-slate-100 flex items-center justify-between gap-1.5">
                                        <div class="flex flex-col">
                                            <div class="flex items-baseline gap-1">
                                                <span class="text-sm sm:text-base font-black text-slate-950" x-text="`₹${Math.round(product.base_price)}`"></span>
                                                <template x-if="product.compare_at_price > product.base_price">
                                                    <span class="text-[10px] text-slate-400 line-through font-semibold" x-text="`₹${Math.round(product.compare_at_price)}`"></span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- ADD Button / Stepper (Blinkit Pattern) -->
                                        <div class="shrink-0">
                                            <!-- Not in cart: White Button with Green Border -->
                                            <template x-if="!cart[product.id] || cart[product.id].qty <= 0">
                                                <button type="button" 
                                                        @click="addToCart(product)"
                                                        class="px-3.5 py-1.5 rounded-xl border border-[#0C831F] text-[#0C831F] bg-[#F7FFF8] hover:bg-[#0C831F] hover:text-white font-extrabold text-xs transition-all shadow-2xs active:scale-95 cursor-pointer flex items-center gap-1">
                                                    <span>ADD</span>
                                                    <span class="text-sm font-black leading-none">+</span>
                                                </button>
                                            </template>

                                            <!-- In cart: Solid Green Stepper -->
                                            <template x-if="cart[product.id] && cart[product.id].qty > 0">
                                                <div class="flex items-center bg-[#0C831F] text-white rounded-xl overflow-hidden shadow-xs">
                                                    <button type="button" 
                                                            @click="decrementCart(product.id)" 
                                                            class="px-2.5 py-1.5 text-xs font-black hover:bg-black/20 transition cursor-pointer">
                                                        –
                                                    </button>
                                                    <span class="px-2 text-xs font-black" x-text="cart[product.id].qty"></span>
                                                    <button type="button" 
                                                            @click="addToCart(product)" 
                                                            class="px-2.5 py-1.5 text-xs font-black hover:bg-black/20 transition cursor-pointer">
                                                        +
                                                    </button>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </template>
                    </div>

                    <!-- Empty State -->
                    <div x-show="filteredProducts.length === 0" class="text-center py-16 bg-white rounded-3xl border border-dashed border-slate-300 p-6">
                        <div class="text-4xl mb-2">🔍</div>
                        <h3 class="text-base font-bold text-slate-800">No matching items found</h3>
                        <p class="text-xs text-slate-500 mt-1">Try searching for 'milk', 'chips', 'bread', or 'cola'</p>
                        <button type="button" @click="searchQuery = ''; activeCategory = 'all'; filterProducts()" class="mt-4 px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs">
                            View All Items
                        </button>
                    </div>

                </section>
            </div>
        </main>

        <!-- ====================================================================
             4. SIGNATURE STICKY BOTTOM CART BAR (BLINKIT PROPRIETARY UI)
             ==================================================================== -->
        <div x-show="cartCount > 0" 
             x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="translate-y-full opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-full opacity-0"
             class="fixed bottom-4 inset-x-0 z-40 px-4 sm:px-6 pointer-events-none">
            <div class="max-w-2xl mx-auto bg-[#0C831F] text-white p-3 sm:p-4 rounded-3xl shadow-[0_10px_35px_rgba(12,131,31,0.45)] border border-white/20 flex items-center justify-between pointer-events-auto backdrop-blur-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-lg shrink-0">
                        🛍️
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm sm:text-base font-black" x-text="`${cartCount} ITEMS • ₹${cartTotal}`"></span>
                            <span class="px-2 py-0.2 rounded-full bg-black/25 text-[10px] font-bold text-amber-200 uppercase">⚡ 10 Mins</span>
                        </div>
                        <span class="text-[11px] text-emerald-100 font-medium block">Standard delivery charges waived on this order</span>
                    </div>
                </div>

                <button type="button" 
                        @click="cartDrawerOpen = true"
                        class="px-5 py-2.5 rounded-2xl bg-white text-[#0C831F] font-black text-xs sm:text-sm hover:bg-emerald-50 active:scale-95 transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                    <span>View Cart</span>
                    <span>›</span>
                </button>
            </div>
        </div>

        <!-- ====================================================================
             5. BLINKIT SLIDE-OVER CART DRAWER
             ==================================================================== -->
        <div x-show="cartDrawerOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-hidden" 
             role="dialog" 
             aria-modal="true">
            <div class="absolute inset-0 bg-black/60 backdrop-blur-xs transition-opacity" 
                 @click="cartDrawerOpen = false"></div>

            <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
                <div class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full">
                    
                    <!-- Drawer Header -->
                    <div class="p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-[#0C831F] text-white flex items-center justify-center font-bold text-sm">
                                ⚡
                            </div>
                            <div>
                                <h2 class="text-sm font-black text-slate-900 uppercase tracking-tight">Delivery in 10 minutes</h2>
                                <p class="text-[11px] text-slate-500 font-medium">To <strong x-text="selectedLocation">Makrana Central</strong></p>
                            </div>
                        </div>
                        <button type="button" @click="cartDrawerOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-black">
                            ✕
                        </button>
                    </div>

                    <!-- Drawer Scroll Area -->
                    <div class="flex-1 overflow-y-auto p-5 space-y-5">
                        
                        <!-- Empty Drawer State -->
                        <template x-if="cartCount <= 0">
                            <div class="text-center py-12 space-y-3">
                                <div class="text-4xl">🛒</div>
                                <h3 class="font-bold text-slate-800 text-sm">Your cart is empty</h3>
                                <p class="text-xs text-slate-500">Add fresh groceries, snacks and drinks for 10-minute instant delivery.</p>
                                <button type="button" @click="cartDrawerOpen = false" class="px-4 py-2 rounded-xl bg-[#0C831F] text-white text-xs font-bold">
                                    Start Shopping
                                </button>
                            </div>
                        </template>

                        <!-- Itemized Cart List -->
                        <div class="space-y-3" x-show="cartCount > 0">
                            <div class="text-xs font-black uppercase tracking-wider text-slate-400">Order Items</div>
                            <template x-for="item in Object.values(cart)" :key="item.id">
                                <div class="flex items-center justify-between gap-3 p-3 rounded-2xl border border-slate-200 bg-slate-50/60">
                                    <img :src="item.featured_image" :alt="item.title" class="w-12 h-12 object-contain rounded-xl bg-white p-1 border border-slate-200 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-xs font-bold text-slate-900 truncate" x-text="item.title"></h4>
                                        <p class="text-[10px] text-slate-500" x-text="item.weight || '1 unit'"></p>
                                        <div class="text-xs font-black text-slate-950 mt-0.5" x-text="`₹${Math.round(item.base_price * item.qty)}`"></div>
                                    </div>
                                    <div class="flex items-center bg-[#0C831F] text-white rounded-xl overflow-hidden shadow-2xs shrink-0">
                                        <button type="button" @click="decrementCart(item.id)" class="px-2 py-1 text-xs font-black hover:bg-black/20">–</button>
                                        <span class="px-2 text-xs font-black" x-text="item.qty"></span>
                                        <button type="button" @click="addToCart(item)" class="px-2 py-1 text-xs font-black hover:bg-black/20">+</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Tip Your Delivery Partner -->
                        <div class="bg-amber-50/80 rounded-2xl p-4 border border-amber-200/80 space-y-2.5" x-show="cartCount > 0">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-amber-950">Tip your delivery boy</span>
                                <span class="text-[10px] text-amber-800 font-medium">100% goes to partner</span>
                            </div>
                            <div class="grid grid-cols-4 gap-2">
                                <template x-for="amt in [10, 20, 30, 50]" :key="amt">
                                    <button type="button" 
                                            @click="tipAmount = (tipAmount === amt ? 0 : amt)"
                                            :class="tipAmount === amt ? 'bg-amber-500 text-white font-bold border-amber-600' : 'bg-white text-amber-900 border-amber-200 font-semibold'"
                                            class="py-1.5 rounded-xl border text-xs transition text-center cursor-pointer"
                                            x-text="`₹${amt}`">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Bill Details (Blinkit Exact Format) -->
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200 space-y-2 text-xs" x-show="cartCount > 0">
                            <div class="font-black uppercase tracking-wider text-slate-400 mb-1">Bill Details</div>
                            <div class="flex justify-between text-slate-600">
                                <span>Items Total</span>
                                <span class="font-mono font-bold text-slate-900" x-text="`₹${cartTotal}`"></span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Delivery Partner Fee (10-min speed)</span>
                                <span class="text-emerald-700 font-bold uppercase text-[11px]">FREE</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Handling & Platform Fee</span>
                                <span class="font-mono text-slate-900">₹2</span>
                            </div>
                            <template x-if="tipAmount > 0">
                                <div class="flex justify-between text-amber-800 font-semibold">
                                    <span>Delivery Partner Tip</span>
                                    <span class="font-mono" x-text="`₹${tipAmount}`"></span>
                                </div>
                            </template>
                            <div class="pt-2 border-t border-slate-200 flex justify-between font-black text-sm text-slate-950">
                                <span>Grand Total</span>
                                <span class="font-mono" x-text="`₹${cartTotal + 2 + tipAmount}`"></span>
                            </div>
                        </div>

                    </div>

                    <!-- Drawer Footer / Checkout -->
                    <div class="p-5 border-t border-slate-200 bg-white" x-show="cartCount > 0">
                        <div class="flex items-center justify-between mb-3 text-xs">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold block">DELIVERY ADDRESS</span>
                                <span class="font-bold text-slate-900 truncate block max-w-[200px]" x-text="selectedLocation">Makrana Central</span>
                            </div>
                            <button type="button" @click="showLocationModal = true" class="text-xs font-bold text-[#0C831F] hover:underline">
                                Change
                            </button>
                        </div>

                        <a href="{{ route('checkout.index') }}" 
                           class="w-full py-3.5 px-4 rounded-2xl bg-[#0C831F] hover:bg-[#0B721B] active:scale-98 text-white font-black text-sm flex items-center justify-between shadow-lg shadow-emerald-700/20 transition-all cursor-pointer">
                            <div class="flex flex-col text-left leading-tight">
                                <span class="text-base font-black" x-text="`₹${cartTotal + 2 + tipAmount}`"></span>
                                <span class="text-[10px] text-emerald-200 uppercase font-semibold">Total to Pay</span>
                            </div>
                            <div class="flex items-center gap-1.5 font-black uppercase text-xs tracking-wider">
                                <span>Proceed to Checkout</span>
                                <span>→</span>
                            </div>
                        </a>
                    </div>

                </div>
            </div>
        </div>

        <!-- ====================================================================
             6. LOCATION PICKER MODAL
             ==================================================================== -->
        <div x-show="showLocationModal" x-cloak class="fixed inset-0 z-50 bg-black/70 flex items-center justify-center p-4 backdrop-blur-xs" @click.self="showLocationModal = false">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 space-y-4 border border-slate-200 shadow-2xl">
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📍</span>
                        <h3 class="font-black text-slate-900 text-sm uppercase">Select Delivery Location</h3>
                    </div>
                    <button type="button" @click="showLocationModal = false" class="text-slate-400 hover:text-black">✕</button>
                </div>

                <div class="space-y-2">
                    <template x-for="loc in popularLocations" :key="loc">
                        <button type="button" 
                                @click="selectedLocation = loc; showLocationModal = false"
                                :class="selectedLocation === loc ? 'border-[#0C831F] bg-emerald-50 text-slate-900 font-bold' : 'border-slate-200 hover:border-slate-400 text-slate-700 font-medium'"
                                class="w-full p-3 rounded-2xl border text-left text-xs flex items-center justify-between transition cursor-pointer">
                            <span x-text="loc"></span>
                            <span class="text-[10px] font-mono font-bold text-emerald-700">10 MINS</span>
                        </button>
                    </template>
                </div>
            </div>
        </div>

    </div>

    <!-- FEASTO REACTIVE ENGINE (ALPINE COMPONENT) -->
    <script>
        function feastoApp(config) {
            return {
                allProducts: config.initialProducts || [],
                filteredProducts: config.initialProducts || [],
                categories: config.initialCategories || [],
                activeCategory: 'all',
                searchQuery: '',
                selectedLocation: 'Makrana Central, Rajasthan (305801)',
                showLocationModal: false,
                cartDrawerOpen: false,
                cart: {},
                cartCount: 0,
                cartTotal: 0,
                tipAmount: 0,
                popularLocations: [
                    'Makrana Central, Rajasthan (305801)',
                    'Jaipur Vaishali Nagar, Rajasthan (302021)',
                    'Ajmer Civil Lines, Rajasthan (305001)',
                    'Jodhpur Sardarpura, Rajasthan (342003)',
                    'Kota Talwandi, Rajasthan (324005)'
                ],
                currentPlaceholder: 'Search "milk"',
                placeholderList: [
                    'Search "milk"', 
                    'Search "dahi"', 
                    'Search "chips"', 
                    'Search "maggi"', 
                    'Search "kaju katli"', 
                    'Search "cola"', 
                    'Search "bread"'
                ],
                placeholderIdx: 0,

                init() {
                    // Start animated placeholder
                    setInterval(() => {
                        this.placeholderIdx = (this.placeholderIdx + 1) % this.placeholderList.length;
                        this.currentPlaceholder = this.placeholderList[this.placeholderIdx];
                    }, 2500);

                    // Load saved cart if present in session
                    try {
                        const saved = localStorage.getItem('k71_feasto_cart');
                        if (saved) {
                            this.cart = JSON.parse(saved);
                            this.updateCartTotals();
                        }
                    } catch(e) {}
                },

                selectCategory(catId) {
                    this.activeCategory = catId;
                    this.filterProducts();
                },

                getActiveCategoryTitle() {
                    if (this.activeCategory === 'all') return 'All Quick Items';
                    const c = this.categories.find(x => x.id === this.activeCategory);
                    return c ? c.name : 'Quick Items';
                },

                filterProducts() {
                    let list = this.allProducts;

                    if (this.activeCategory !== 'all') {
                        list = list.filter(p => p.category_slug === this.activeCategory);
                    }

                    if (this.searchQuery && this.searchQuery.trim().length > 0) {
                        const q = this.searchQuery.toLowerCase().trim();
                        list = list.filter(p => 
                            (p.title && p.title.toLowerCase().includes(q)) ||
                            (p.brand && p.brand.toLowerCase().includes(q)) ||
                            (p.category_slug && p.category_slug.toLowerCase().includes(q))
                        );
                    }

                    this.filteredProducts = list;
                },

                addToCart(product) {
                    if (!this.cart[product.id]) {
                        this.cart[product.id] = { ...product, qty: 1 };
                    } else {
                        this.cart[product.id].qty++;
                    }
                    this.updateCartTotals();
                },

                decrementCart(productId) {
                    if (this.cart[productId]) {
                        this.cart[productId].qty--;
                        if (this.cart[productId].qty <= 0) {
                            delete this.cart[productId];
                        }
                    }
                    this.updateCartTotals();
                },

                updateCartTotals() {
                    let count = 0;
                    let total = 0;
                    Object.values(this.cart).forEach(i => {
                        count += i.qty;
                        total += (i.qty * parseFloat(i.base_price));
                    });
                    this.cartCount = count;
                    this.cartTotal = Math.round(total);
                    try {
                        localStorage.setItem('k71_feasto_cart', JSON.stringify(this.cart));
                    } catch(e) {}
                }
            };
        }
    </script>
</x-layouts.public>
