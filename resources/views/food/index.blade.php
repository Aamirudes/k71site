<x-layouts.public 
    title="K71 Food — Superfast Gourmet & Cloud Kitchen Delivery"
    metaDescription="Order fresh biryani, artisan pizza, burgers, thalis, and desserts on K71 Food. 20-minute superfast delivery, pure veg filters, and verified cloud kitchens."
>
    @php
        $dishes = $trendingDishes ?? collect();
    @endphp

    <div x-data="{
        filterModal: false,
        activeDishModal: false,
        cartDrawer: false,
        cartCount: 0,
        cartTotal: 0,
        cartItems: [],
        selectedDish: null,
        dishQty: 1,
        selectedPortion: 'Standard',
        activeFilterSort: 'recommended',
        activeDietary: 'all',
        locationModal: false,
        selectedLocation: 'Indiranagar, Bengaluru (12th Main)',

        openDishModal(dish) {
            this.selectedDish = dish;
            this.dishQty = 1;
            this.selectedPortion = 'Standard';
            this.activeDishModal = true;
        },
        addToCart(dish) {
            const target = dish || this.selectedDish;
            if (!target) return;
            const price = parseFloat(target.base_price || target.price || 199);
            const existing = this.cartItems.find(i => i.id === target.id);
            if (existing) {
                existing.qty += 1;
            } else {
                this.cartItems.push({
                    id: target.id,
                    title: target.title,
                    price: price,
                    qty: 1,
                    image: target.thumbnail_url || (target.images && target.images[0] ? '/storage/' + target.images[0].path : '')
                });
            }
            this.cartCount += 1;
            this.cartTotal += price;
            this.activeDishModal = false;
        },
        updateCartQty(id, delta) {
            const item = this.cartItems.find(i => i.id === id);
            if (!item) return;
            item.qty += delta;
            this.cartCount += delta;
            this.cartTotal += (item.price * delta);
            if (item.qty <= 0) {
                this.cartItems = this.cartItems.filter(i => i.id !== id);
            }
        },
        clearCart() {
            this.cartItems = [];
            this.cartCount = 0;
            this.cartTotal = 0;
            this.cartDrawer = false;
        }
    }" class="min-h-screen bg-[#faf8f5] text-stone-900 pb-24 font-sans">

        <!-- 1. FOOD SUBDOMAIN DISTINCT APP HEADER -->
        <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-xl border-b border-orange-100/80 shadow-[0_4px_20px_rgba(255,82,0,0.04)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
                <div class="flex items-center justify-between gap-4">
                    
                    <!-- Left: Delivery Location & Subdomain Indicator -->
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-[#ff5200] to-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-orange-500/20">
                            <span class="text-xl">🍕</span>
                        </div>
                        <div class="min-w-0">
                            <div class="flex items-center gap-1.5">
                                <span class="font-black text-sm tracking-tight text-stone-900">K71 Food</span>
                                <span class="text-[9px] font-black uppercase tracking-wider bg-orange-100 text-[#ff5200] px-1.5 py-0.2 rounded-md">20 MIN DELIVERY</span>
                            </div>
                            <div @click="locationModal = true" class="flex items-center gap-1 text-xs text-stone-500 hover:text-stone-900 cursor-pointer truncate">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 animate-ping"></span>
                                <span class="truncate font-semibold text-stone-700" x-text="selectedLocation"></span>
                                <svg class="w-3.5 h-3.5 text-stone-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Veg Mode, Search Quick Action & Live Cart -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        <!-- Veg Only Toggle -->
                        <a href="{{ $pureVegOnly ? route('food.index') : route('food.index', ['pure_veg' => 1]) }}" 
                           class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-black transition-all cursor-pointer {{ $pureVegOnly ? 'bg-emerald-600 text-white shadow-md shadow-emerald-600/25 ring-2 ring-emerald-500/30' : 'bg-emerald-50 text-emerald-800 border border-emerald-300 hover:bg-emerald-100' }}">
                            <span class="w-2 h-2 rounded-full {{ $pureVegOnly ? 'bg-white' : 'bg-emerald-600' }}"></span>
                            <span class="text-[11px] uppercase tracking-wider">PURE VEG</span>
                        </a>

                        <!-- Floating Food Cart Button -->
                        <button type="button" @click="cartDrawer = true" 
                                class="relative px-3.5 py-2 rounded-2xl bg-[#ff5200] hover:bg-[#e04800] text-white text-xs font-black transition shadow-lg shadow-orange-500/25 flex items-center gap-1.5 cursor-pointer active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span class="hidden sm:inline">Orders</span>
                            <span x-show="cartCount > 0" x-text="cartCount" class="w-4 h-4 rounded-full bg-stone-900 text-white text-[9px] font-black flex items-center justify-center"></span>
                        </button>
                    </div>

                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 pt-5 space-y-7">
            
            <!-- 2. HIGH-DENSITY FOOD SEARCH & TRENDING CHIPS -->
            <section class="space-y-3">
                <form action="{{ route('food.index') }}" method="GET" class="relative">
                    <div class="relative flex items-center bg-white rounded-2xl border-2 border-stone-200/80 shadow-sm focus-within:border-[#ff5200] focus-within:ring-4 focus-within:ring-orange-500/10 transition-all overflow-hidden">
                        <div class="pl-4 text-[#ff5200]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="search" value="{{ $search ?? '' }}" 
                               placeholder="Search for biryani, cheese burst pizza, butter chicken, thalis..." 
                               class="w-full bg-transparent py-3.5 pl-3 pr-24 text-sm font-medium text-stone-900 placeholder:text-stone-400 focus:outline-none">
                        
                        <div class="absolute right-2 flex items-center gap-1">
                            @if($pureVegOnly)
                                <input type="hidden" name="pure_veg" value="1">
                            @endif
                            <button type="submit" class="px-4 py-2 rounded-xl bg-gradient-to-r from-[#ff5200] to-amber-500 text-white text-xs font-black shadow-sm hover:opacity-95 transition cursor-pointer">
                                Search
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Trending Food Quick Chips -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <span class="text-[11px] font-black uppercase tracking-wider text-stone-400 shrink-0">Popular:</span>
                    @foreach(['Dum Biryani', 'Margherita Pizza', 'Paneer Tikka', 'Truffle Burger', 'Cold Coffee', 'Gulab Jamun', 'Masala Dosa'] as $chip)
                        <a href="{{ route('food.index', ['search' => $chip]) }}" 
                           class="px-3 py-1 rounded-full bg-white hover:bg-orange-50 border border-stone-200 hover:border-orange-200 text-xs font-bold text-stone-700 hover:text-[#ff5200] transition-colors shrink-0 shadow-2xs">
                            {{ $chip }}
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- 3. WHAT'S ON YOUR MIND? CATEGORIES HORIZONTAL RAIL -->
            <section class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                            <span>What's on your mind?</span>
                            <span class="text-base">✨</span>
                        </h2>
                        <p class="text-xs text-stone-500">Craving something specific? Pick from freshly curated food collections.</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-3">
                    @php
                        $cuisineIcons = [
                            ['name' => 'Biryani', 'icon' => '🍲', 'desc' => 'Dum & Handi', 'tag' => 'biryani'],
                            ['name' => 'Pizza', 'icon' => '🍕', 'desc' => 'Hand-Tossed', 'tag' => 'pizza'],
                            ['name' => 'Burgers', 'icon' => '🍔', 'desc' => 'Juicy Grills', 'tag' => 'burger'],
                            ['name' => 'North Indian', 'icon' => '🍛', 'desc' => 'Gravies & Roti', 'tag' => 'north indian'],
                            ['name' => 'Chinese', 'icon' => '🍜', 'desc' => 'Noodles & Dimsum', 'tag' => 'chinese'],
                            ['name' => 'Desserts', 'icon' => '🍰', 'desc' => 'Cakes & Sweets', 'tag' => 'dessert'],
                            ['name' => 'South Indian', 'icon' => '🥞', 'desc' => 'Dosa & Idli', 'tag' => 'south indian'],
                            ['name' => 'Chai & Snacks', 'icon' => '☕', 'desc' => 'Samosa & Tea', 'tag' => 'beverage'],
                        ];
                    @endphp

                    @foreach($cuisineIcons as $cItem)
                        <a href="{{ route('food.index', ['search' => $cItem['name']]) }}" 
                           class="flex flex-col items-center p-3 rounded-2xl bg-white border border-stone-200/80 hover:border-orange-300 hover:shadow-md transition-all group text-center cursor-pointer">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-b from-orange-50 to-amber-50 flex items-center justify-center text-3xl group-hover:scale-110 transition-transform shadow-inner mb-2">
                                {{ $cItem['icon'] }}
                            </div>
                            <span class="text-xs font-black text-stone-900 group-hover:text-[#ff5200] truncate w-full">{{ $cItem['name'] }}</span>
                            <span class="text-[9.5px] text-stone-400 font-medium truncate w-full">{{ $cItem['desc'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- 4. SAFFRON & FLAME PROMOTIONAL HERO BANNER -->
            <section class="rounded-3xl bg-gradient-to-r from-[#ff5200] via-rose-600 to-amber-500 p-6 sm:p-8 text-white shadow-xl relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-lg space-y-2">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black uppercase tracking-wider">
                            <span>🔥 FLASH FEAST DEAL</span>
                        </div>
                        <h2 class="text-2xl sm:text-4xl font-black tracking-tight leading-tight">
                            FLAT 50% OFF ON YOUR FIRST 3 MEALS
                        </h2>
                        <p class="text-xs sm:text-sm text-orange-100 font-medium leading-relaxed">
                            Order from top-rated cloud kitchens &amp; gourmet bistros. Delivered fresh, hot, and hygienically sealed in under 25 minutes.
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-3 shrink-0">
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 px-4 py-3 rounded-2xl text-center">
                            <div class="text-[10px] uppercase font-bold text-orange-200">Use Coupon</div>
                            <div class="font-mono font-black text-lg text-white">K71FEAST</div>
                        </div>
                        <a href="{{ route('food.index', ['pure_veg' => 0]) }}" class="px-6 py-3.5 rounded-2xl bg-white hover:bg-orange-50 text-[#ff5200] text-xs font-black shadow-lg transition-all active:scale-95 cursor-pointer">
                            Order Now ›
                        </a>
                    </div>
                </div>
            </section>

            <!-- 5. TOP CLOUD KITCHENS & RESTAURANTS -->
            @if($restaurants->isNotEmpty())
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg sm:text-xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                                <span>Top Cloud Kitchens &amp; Dine-Ins</span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-100 text-emerald-800">Verified Quality</span>
                            </h2>
                            <p class="text-xs text-stone-500">Hygiene audited kitchens with fastest dispatch times in your zone.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($restaurants as $rest)
                            @php
                                $rCover = $rest->banner_url ?: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80';
                                $isVeg = $rest->settings?->is_pure_veg;
                            @endphp
                            <div class="bg-white rounded-3xl border border-stone-200/80 overflow-hidden hover:shadow-xl transition-all duration-300 group flex flex-col justify-between">
                                <div>
                                    <!-- Banner Image & Tags -->
                                    <div class="relative aspect-video w-full overflow-hidden bg-stone-100">
                                        <img src="{{ $rCover }}" alt="{{ $rest->shop_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
                                        
                                        <!-- Top Left Badges -->
                                        <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                            @if($isVeg)
                                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-emerald-600 text-white shadow-sm">
                                                    Pure Veg
                                                </span>
                                            @endif
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase bg-white/90 backdrop-blur-sm text-stone-900 shadow-sm">
                                                ★ 4.8
                                            </span>
                                        </div>

                                        <!-- Bottom Text on Banner -->
                                        <div class="absolute bottom-3 left-3 right-3 text-white">
                                            <div class="text-xs font-black tracking-wider uppercase text-orange-300">20-25 MINS • 1.4 KM</div>
                                            <h3 class="text-base font-black truncate text-white drop-shadow-xs">{{ $rest->shop_name }}</h3>
                                        </div>
                                    </div>

                                    <!-- Content Info -->
                                    <div class="p-4 space-y-2">
                                        <p class="text-xs text-stone-500 line-clamp-2">
                                            {{ $rest->description ?: 'Authentic, hygienic freshly cooked specialties prepared to order.' }}
                                        </p>
                                        <div class="flex items-center justify-between text-xs font-semibold text-stone-600 pt-2 border-t border-stone-100">
                                            <span>📍 {{ $rest->city ?: 'Local Hub' }}</span>
                                            <span class="text-[#ff5200] font-black">₹350 for two</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 pt-0">
                                    <a href="{{ route('food.restaurant', $rest->slug) }}" 
                                       class="w-full block py-2.5 rounded-xl bg-orange-50 hover:bg-[#ff5200] text-[#ff5200] hover:text-white text-center text-xs font-black transition-colors cursor-pointer">
                                        View Digital Menu ›
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 6. FEATURED DISHES GRID WITH QUICK ADD -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg sm:text-xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                            <span>Trending Chef Specials</span>
                            <span class="text-xl">🔥</span>
                        </h2>
                        <p class="text-xs text-stone-500">Top ordered favorites by foodies across town.</p>
                    </div>
                </div>

                @if($dishes->isEmpty())
                    <div class="bg-white rounded-3xl p-12 text-center border border-stone-200/80 space-y-3">
                        <span class="text-5xl">🍲</span>
                        <h3 class="text-base font-bold text-stone-800">No dishes found matching your selection</h3>
                        <p class="text-xs text-stone-500 max-w-sm mx-auto">Try clearing search filters or browse other cuisines to explore fresh meals.</p>
                        <a href="{{ route('food.index') }}" class="inline-block px-5 py-2 rounded-xl bg-[#ff5200] text-white text-xs font-black">
                            Clear Filters
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
                        @foreach($dishes as $dish)
                            @php
                                $dImage = $dish->images->first() ? asset('storage/' . $dish->images->first()->path) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80';
                                $isDietVeg = str_contains(strtolower(json_encode($dish->custom_fields ?? [])), 'veg') && !str_contains(strtolower(json_encode($dish->custom_fields ?? [])), 'non-veg');
                                $price = $dish->base_price ? $dish->base_price / 100 : 249;
                            @endphp
                            <div class="bg-white rounded-3xl border border-stone-200/80 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                                <div>
                                    <!-- Dish Image -->
                                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-stone-100">
                                        <img src="{{ $dImage }}" alt="{{ $dish->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        
                                        <!-- Diet Type Icon (Green Square / Brown Triangle) -->
                                        <div class="absolute top-3 left-3 bg-white/90 backdrop-blur-sm p-1 rounded-md shadow-xs flex items-center justify-center">
                                            @if($isDietVeg)
                                                <div class="w-4 h-4 border-2 border-emerald-600 flex items-center justify-center rounded-xs">
                                                    <div class="w-2 h-2 rounded-full bg-emerald-600"></div>
                                                </div>
                                            @else
                                                <div class="w-4 h-4 border-2 border-rose-700 flex items-center justify-center rounded-xs">
                                                    <div class="w-0 h-0 border-l-[4px] border-l-transparent border-r-[4px] border-r-transparent border-b-[8px] border-b-rose-700"></div>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Dish Rating -->
                                        <div class="absolute bottom-3 left-3 px-2 py-0.5 rounded-md bg-stone-950/80 backdrop-blur-sm text-white text-[10px] font-black flex items-center gap-1">
                                            <span>★ 4.9</span>
                                            <span class="text-stone-400 font-normal">(120+)</span>
                                        </div>
                                    </div>

                                    <!-- Dish Details -->
                                    <div class="p-4 space-y-2">
                                        <div class="flex items-start justify-between gap-2">
                                            <div>
                                                <h3 class="text-sm font-black text-stone-900 group-hover:text-[#ff5200] transition-colors line-clamp-1">
                                                    {{ $dish->title }}
                                                </h3>
                                                <p class="text-[11px] text-stone-500 font-medium">
                                                    {{ $dish->vendor?->shop_name ?: 'Specialty Kitchen' }}
                                                </p>
                                            </div>
                                        </div>
                                        
                                        <p class="text-xs text-stone-400 line-clamp-2 leading-relaxed">
                                            {{ $dish->description ?: 'Freshly prepared specialty meal with premium farm ingredients.' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Price and Add Button -->
                                <div class="p-4 pt-0 flex items-center justify-between border-t border-stone-100 mt-2">
                                    <div>
                                        <span class="text-base font-black text-stone-900">₹{{ number_format($price, 0) }}</span>
                                        @if($dish->compare_at_price)
                                            <span class="text-xs text-stone-400 line-through ml-1">₹{{ number_format($dish->compare_at_price / 100, 0) }}</span>
                                        @endif
                                    </div>

                                    <!-- Quick Add Action -->
                                    <button type="button" 
                                            @click="addToCart({ id: {{ $dish->id }}, title: '{{ addslashes($dish->title) }}', base_price: {{ $price }}, thumbnail_url: '{{ $dImage }}' })"
                                            class="px-4 py-2 rounded-xl bg-orange-50 hover:bg-[#ff5200] text-[#ff5200] hover:text-white border border-orange-200 text-xs font-black transition-all cursor-pointer active:scale-95 flex items-center gap-1">
                                        <span>ADD</span>
                                        <span class="text-sm font-bold">+</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

        </main>

        <!-- FLOATING BOTTOM ORDER PILL -->
        <div x-show="cartCount > 0" 
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-full opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-full opacity-0"
             class="fixed bottom-20 inset-x-4 sm:inset-x-auto sm:right-6 sm:w-96 z-40 bg-[#ff5200] text-white p-3.5 rounded-2xl shadow-2xl shadow-orange-500/40 flex items-center justify-between gap-3 border border-orange-400/40 cursor-pointer"
             @click="cartDrawer = true">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center font-black text-sm">
                    <span x-text="cartCount"></span>
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-orange-100">Cart Total</div>
                    <div class="text-sm font-black">₹<span x-text="cartTotal.toFixed(0)"></span></div>
                </div>
            </div>
            <div class="flex items-center gap-1 font-black text-xs uppercase tracking-wider bg-white/20 hover:bg-white/30 px-3 py-1.5 rounded-xl transition">
                <span>View Order</span>
                <span>›</span>
            </div>
        </div>

        <!-- FOOD CART DRAWER SLIDEOUT -->
        <div x-show="cartDrawer" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-hidden bg-black/60 backdrop-blur-xs flex justify-end">
            <div @click.away="cartDrawer = false" 
                 class="w-full max-w-md bg-white h-full shadow-2xl flex flex-col justify-between p-6 overflow-y-auto">
                
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">🍲</span>
                            <div>
                                <h3 class="text-base font-black text-stone-900">Your Food Order</h3>
                                <p class="text-[11px] text-stone-500">Superfast Express Delivery (20 Mins)</p>
                            </div>
                        </div>
                        <button @click="cartDrawer = false" class="p-2 text-stone-400 hover:text-stone-900 text-lg cursor-pointer">&times;</button>
                    </div>

                    <!-- Cart Item List -->
                    <div class="py-4 divide-y divide-stone-100 space-y-3">
                        <template x-if="cartItems.length === 0">
                            <div class="py-12 text-center text-stone-400">
                                <span class="text-4xl block mb-2">🍽️</span>
                                <p class="text-xs font-bold">Your cart is hungry!</p>
                                <p class="text-[11px] text-stone-400 mt-1">Add your favorite dishes from the menu above.</p>
                            </div>
                        </template>

                        <template x-for="item in cartItems" :key="item.id">
                            <div class="flex items-center justify-between pt-3">
                                <div class="min-w-0 pr-3">
                                    <h4 class="text-xs font-bold text-stone-900 truncate" x-text="item.title"></h4>
                                    <div class="text-[11px] text-[#ff5200] font-black">₹<span x-text="(item.price * item.qty).toFixed(0)"></span></div>
                                </div>

                                <!-- Qty controls -->
                                <div class="flex items-center gap-2 bg-stone-100 px-2 py-1 rounded-xl">
                                    <button @click="updateCartQty(item.id, -1)" class="w-5 h-5 rounded-md bg-white text-stone-700 font-black text-xs hover:bg-orange-100 cursor-pointer">-</button>
                                    <span class="text-xs font-black text-stone-900 px-1" x-text="item.qty"></span>
                                    <button @click="updateCartQty(item.id, 1)" class="w-5 h-5 rounded-md bg-white text-stone-700 font-black text-xs hover:bg-orange-100 cursor-pointer">+</button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Drawer Checkout Footer -->
                <div x-show="cartItems.length > 0" class="pt-4 border-t border-stone-100 space-y-3">
                    <div class="flex items-center justify-between text-xs font-bold text-stone-600">
                        <span>Delivery Fee (Express 20m)</span>
                        <span class="text-emerald-600 font-black">FREE</span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-black text-stone-900">
                        <span>Total Payable</span>
                        <span class="text-[#ff5200]">₹<span x-text="cartTotal.toFixed(0)"></span></span>
                    </div>

                    <a href="{{ route('checkout.index') }}" 
                       class="w-full py-3.5 rounded-2xl bg-[#ff5200] hover:bg-[#e04800] text-white text-center text-xs font-black shadow-lg shadow-orange-500/30 block cursor-pointer transition">
                        Proceed to Superfast Checkout ›
                    </a>
                </div>

            </div>
        </div>

        <!-- DEDICATED FOOD MOBILE NAVIGATION DOCK -->
        <x-k71-bottom-nav active="food" />

    </div>
</x-layouts.public>
