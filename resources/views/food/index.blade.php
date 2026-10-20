<x-layouts.public 
    title="Zomato & Swiggy Style Food Hub — K71 Food"
    metaDescription="Order fresh chef-crafted biryanis, gourmet pizzas, crispy burgers, and healthy salads on K71 Food. 25-minute fast delivery, live tracking, and pure veg options."
>
    @php
        $dishes = $trendingDishes ?? collect();
    @endphp

    <style>
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-6px); }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 15px rgba(252, 128, 25, 0.35); }
            50% { box-shadow: 0 0 28px rgba(252, 128, 25, 0.65); }
        }
        .animate-float-slow { animation: floatSlow 4s ease-in-out infinite; }
        .swiggy-glow { animation: pulseGlow 3s ease-in-out infinite; }
        .glass-swiggy {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(252, 128, 25, 0.18);
        }
        .dark .glass-swiggy {
            background: rgba(24, 24, 27, 0.88);
            border: 1px solid rgba(252, 128, 25, 0.25);
        }
    </style>

    <div x-data="{
        filterModal: false,
        activeDishModal: false,
        cartDrawer: false,
        cartCount: 0,
        cartTotal: 0,
        cartItems: [],
        selectedDish: null,
        dishQty: 1,
        selectedPortion: 'Regular',
        activeFilterSort: 'recommended',
        activeDietary: 'all',

        openDishModal(dish) {
            this.selectedDish = dish;
            this.dishQty = 1;
            this.selectedPortion = 'Regular';
            this.activeDishModal = true;
        },
        addToCart() {
            if (!this.selectedDish) return;
            const price = this.selectedDish.price * this.dishQty;
            this.cartItems.push({
                title: this.selectedDish.title,
                portion: this.selectedPortion,
                qty: this.dishQty,
                price: price,
                image: this.selectedDish.image
            });
            this.cartCount += this.dishQty;
            this.cartTotal += price;
            this.activeDishModal = false;
            this.cartDrawer = true;
        },
        clearCart() {
            this.cartItems = [];
            this.cartCount = 0;
            this.cartTotal = 0;
            this.cartDrawer = false;
        }
    }" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300 pb-24 font-sans">

        <!-- ======================================================== -->
        <!-- 1. SWIGGY/ZOMATO SIGNATURE TOP HEADER WITH ORANGE & GREEN -->
        <!-- ======================================================== -->
        <div class="mb-5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3.5 sm:p-4 rounded-3xl bg-gradient-to-r from-[#fc8019] via-[#ff5200] to-[#e04500] text-white shadow-xl border border-orange-400/40">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-white text-[#fc8019] flex items-center justify-center font-black text-2xl shrink-0 shadow-md transform hover:rotate-12 transition-transform duration-300">
                    🍔
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-black/30 text-white px-2 py-0.5 rounded-md">
                            food.{{ config('k71.root_domain', 'k71.in') }}
                        </span>
                        <span class="text-[11px] text-orange-100 font-semibold hidden sm:inline">⚡ 25-Min Superfast Express Dispatch</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-black text-sm text-white mt-0.5 cursor-pointer hover:text-amber-200 transition">
                        <span>📍 Deliver to:</span>
                        <span class="underline decoration-dotted font-extrabold">Current Location / Central Hub</span>
                        <svg class="w-3.5 h-3.5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Action Controls (Pure Veg Switch, Filter, Cart) -->
            <div class="flex items-center gap-2.5 justify-end">
                <!-- Pure Veg Toggle Button with Official Indian Green Veg Mark -->
                <a href="{{ $pureVegOnly ? route('food.index') : route('food.index', ['pure_veg' => 1]) }}" 
                   class="px-3.5 py-2 rounded-2xl text-xs font-black transition flex items-center gap-2 shadow-md {{ $pureVegOnly ? 'bg-emerald-600 text-white ring-2 ring-emerald-300' : 'bg-white text-zinc-900 hover:bg-emerald-50' }}">
                    <div class="w-4 h-4 rounded-sm border-2 border-emerald-600 flex items-center justify-center p-0.5 bg-white">
                        <div class="w-2 h-2 rounded-full bg-emerald-600"></div>
                    </div>
                    <span class="font-extrabold">PURE VEG</span>
                </a>

                <!-- Cart Quick Launcher Button -->
                <button type="button" @click="cartDrawer = true" 
                        class="relative px-4 py-2 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg flex items-center gap-1.5 active:scale-95 cursor-pointer border border-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Basket</span>
                    <span x-show="cartCount > 0" x-text="cartCount" class="w-5 h-5 rounded-full bg-amber-400 text-zinc-950 text-[10px] font-black flex items-center justify-center shadow"></span>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. ZOMATO/SWIGGY HERO BANNER: ORANGE & GREEN CELEBRATION -->
        <!-- ======================================================== -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#fc8019] via-[#ff5c00] to-[#10b981] p-6 sm:p-9 text-white shadow-2xl mb-8 border border-orange-300/30">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="max-w-xl text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black tracking-wider uppercase mb-3 border border-white/30 text-white">
                        <span class="w-2 h-2 rounded-full bg-emerald-300 animate-ping"></span>
                        <span>🔥 CRAVINGS FESTIVAL • FLAT 50% OFF</span>
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight uppercase drop-shadow-md">
                        Gourmet Meals <br>
                        <span class="text-emerald-200">Delivered Hot In 25 Mins</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-orange-50 font-medium mt-3 mb-6 max-w-lg leading-relaxed">
                        Order hand-tossed Domino's style cheesy pizzas, royal dum biryanis, smash burgers, and fresh hydroponic salads crafted by 5-star master chefs.
                    </p>
                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <a href="#dishesGrid" class="px-7 py-3 rounded-2xl bg-white text-[#fc8019] hover:bg-orange-50 font-black text-xs uppercase tracking-wider shadow-xl transition active:scale-95 flex items-center gap-2">
                            <span>Explore Menus 🍲</span>
                        </a>
                        <div class="flex items-center gap-2 text-xs font-bold text-white bg-black/25 backdrop-blur-md px-3.5 py-2.5 rounded-2xl border border-white/15">
                            <span class="text-emerald-300">✓</span> Free Delivery on orders above ₹199
                        </div>
                    </div>
                </div>

                <!-- Floating 3D Food Showcase -->
                <div class="relative shrink-0 flex items-center justify-center">
                    <div class="w-48 h-48 sm:w-60 sm:h-60 rounded-full bg-white/15 backdrop-blur-xl border-4 border-white/30 p-2 shadow-2xl flex items-center justify-center animate-float-slow">
                        <img src="https://images.unsplash.com/photo-1565299624946-b28f40a0ae38?auto=format&fit=crop&w=500&q=80" 
                             alt="Gourmet Artisan Dish" class="w-full h-full object-cover rounded-full shadow-2xl">
                    </div>
                    <div class="absolute -bottom-3 -left-3 px-3.5 py-1.5 rounded-xl bg-white text-zinc-900 text-xs font-black shadow-xl border border-orange-100 flex items-center gap-1.5">
                        <span class="text-amber-500">★</span> 4.9 Super Chef (12k+)
                    </div>
                    <div class="absolute -top-3 -right-3 px-3 py-1.5 rounded-xl bg-emerald-600 text-white text-[11px] font-black uppercase tracking-wider shadow-xl flex items-center gap-1">
                        <span>⚡ 25 MINS</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 3. SEARCH BAR WITH FOOD SUGGESTIONS                     -->
        <!-- ======================================================== -->
        <div class="mb-8">
            <form action="{{ route('food.index') }}" method="GET" class="relative">
                <div class="absolute left-4 top-4 text-[#fc8019]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search for restaurants, dum biryani, artisan pizza, burger, pasta, dessert..." 
                       class="w-full pl-12 pr-32 py-4 rounded-3xl bg-white dark:bg-zinc-900 border-2 border-orange-200 dark:border-zinc-800 text-sm font-semibold focus:ring-4 focus:ring-orange-500/20 focus:border-[#fc8019] focus:outline-none transition shadow-sm">
                <button type="submit" class="absolute right-2.5 top-2.5 px-6 py-2.5 rounded-2xl bg-gradient-to-r from-[#fc8019] to-[#ff5200] hover:from-[#ff5200] hover:to-[#e04500] text-white text-xs font-black uppercase tracking-wider transition shadow-md cursor-pointer">
                    Search Food
                </button>
            </form>
        </div>

        <!-- ======================================================== -->
        <!-- 4. WHAT'S ON YOUR MIND? COLORFUL CIRCULAR CATEGORIES    -->
        <!-- ======================================================== -->
        <div class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                        <span>What's on your mind?</span>
                        <span class="text-orange-500">😋</span>
                    </h3>
                    <p class="text-xs text-zinc-400">Curated cloud kitchens and artisanal bakeries</p>
                </div>
                <a href="{{ route('food.index') }}" class="text-xs font-black text-[#fc8019] hover:underline cursor-pointer">View All</a>
            </div>

            <div class="flex items-center gap-5 overflow-x-auto no-scrollbar py-2">
                @php
                    $foodIcons = [
                        ['name' => 'All Menus', 'icon' => '🍽️', 'query' => '', 'color' => 'from-orange-500 to-amber-500'],
                        ['name' => 'Biryani', 'icon' => '🍲', 'query' => 'biryani', 'color' => 'from-amber-600 to-yellow-500'],
                        ['name' => 'Pizza', 'icon' => '🍕', 'query' => 'pizza', 'color' => 'from-red-500 to-orange-500'],
                        ['name' => 'Burgers', 'icon' => '🍔', 'query' => 'burger', 'color' => 'from-orange-600 to-red-500'],
                        ['name' => 'Noodles & Rolls', 'icon' => '🍜', 'query' => 'noodles', 'color' => 'from-emerald-500 to-teal-500'],
                        ['name' => 'Pure Veg', 'icon' => '🥗', 'query' => 'salad', 'color' => 'from-green-600 to-emerald-500'],
                        ['name' => 'Chicken & Meat', 'icon' => '🥩', 'query' => 'meat', 'color' => 'from-rose-600 to-red-600'],
                        ['name' => 'Desserts & Cakes', 'icon' => '🍰', 'query' => 'dessert', 'color' => 'from-pink-500 to-rose-400'],
                        ['name' => 'Beverages', 'icon' => '🧃', 'query' => 'beverage', 'color' => 'from-blue-500 to-cyan-500'],
                        ['name' => 'Bakery & Breads', 'icon' => '🥖', 'query' => 'bakery', 'color' => 'from-yellow-600 to-amber-500'],
                    ];
                @endphp
                @foreach($foodIcons as $item)
                    <a href="{{ route('food.index', ['search' => $item['query']]) }}" 
                       class="group flex flex-col items-center shrink-0 cursor-pointer text-center min-w-[76px]">
                        <div class="w-18 h-18 sm:w-20 sm:h-20 rounded-full p-1 bg-gradient-to-tr {{ $item['color'] }} shadow-md group-hover:scale-110 group-hover:shadow-xl transition-all duration-300 flex items-center justify-center">
                            <div class="w-full h-full rounded-full bg-white dark:bg-zinc-900 flex items-center justify-center text-3xl">
                                {{ $item['icon'] }}
                            </div>
                        </div>
                        <span class="text-xs font-bold text-zinc-800 dark:text-zinc-200 group-hover:text-[#fc8019] mt-2 transition">
                            {{ $item['name'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 5. RECOMMENDED DISHES & RESTAURANTS (SWIGGY/DOMINOS)   -->
        <!-- ======================================================== -->
        <div id="dishesGrid" class="mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                    <span>Popular Dishes Near You</span>
                    <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black uppercase">LIVE DISPATCH</span>
                </h3>
                <p class="text-xs text-zinc-400">Guaranteed within 25-30 minutes with live GPS updates</p>
            </div>
            <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold">● Kitchens Open</span>
        </div>

        @if($dishes->isEmpty() && $restaurants->isEmpty())
            <div class="text-center py-16 px-4 glass-swiggy rounded-3xl max-w-md mx-auto my-8">
                <div class="w-16 h-16 rounded-2xl bg-orange-100 dark:bg-orange-950/40 text-[#fc8019] flex items-center justify-center mx-auto mb-3 text-3xl">
                    🍲
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white mb-1">No dishes found in this filter</h3>
                <p class="text-xs text-zinc-500 mb-4">Try searching for biryani, burger, pizza, salad, or clear filters.</p>
                <a href="{{ route('food.index') }}" class="px-6 py-2.5 rounded-2xl bg-gradient-to-r from-[#fc8019] to-[#ff5200] text-white text-xs font-black uppercase tracking-wider shadow-md">
                    View All Kitchens
                </a>
            </div>
        @else
            <!-- Food Grid: Premium Glassmorphic Swiggy/Domino's Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-12">
                @foreach($dishes as $dish)
                    @php
                        $priceRupees = $dish->base_price / 100;
                        $firstImg = $dish->images->first()?->path;
                        $isVeg = $dish->getCustomField('is_veg', true);
                        $calories = $dish->getCustomField('calories', '380 kcal');
                        $prepTime = $dish->getCustomField('prep_time', '20-25 min');
                        $restaurantName = $dish->vendor?->shop_name ?? 'Artisan Kitchen';
                        $imgUrl = $firstImg ? asset('storage/' . $firstImg) : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=500&q=80';
                    @endphp
                    <div class="group rounded-3xl glass-swiggy overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-2xl hover:-translate-y-1.5 relative">
                        
                        <!-- Image Container with Veg Badge & Distance -->
                        <div class="relative w-full aspect-4/3 bg-zinc-100 dark:bg-zinc-800 overflow-hidden cursor-pointer"
                             @click="openDishModal({
                                 title: '{{ addslashes($dish->title) }}',
                                 price: {{ $priceRupees }},
                                 image: '{{ $imgUrl }}',
                                 restaurant: '{{ addslashes($restaurantName) }}',
                                 calories: '{{ $calories }}',
                                 time: '{{ $prepTime }}',
                                 desc: '{{ addslashes($dish->description ?? 'Freshly prepared with handpicked ingredients, aromatic herbs, and balanced seasoning.') }}'
                             })">
                            <img src="{{ $imgUrl }}" alt="{{ $dish->title }}"
                                 class="w-full h-full object-cover group-hover:scale-108 transition duration-700 ease-out"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=500&q=80';">

                            <!-- Top Badges Overlay -->
                            <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-[#fc8019] text-white shadow-md">
                                    50% OFF UP TO ₹100
                                </span>
                            </div>

                            <div class="absolute top-3 right-3 px-2 py-0.5 rounded-lg bg-black/70 backdrop-blur-md text-white text-[10px] font-black flex items-center gap-1">
                                <span class="text-amber-400">★</span> 4.8
                            </div>

                            <!-- Delivery ETA Pill Bottom-Left -->
                            <div class="absolute bottom-3 left-3 px-2.5 py-1 rounded-lg bg-black/70 backdrop-blur-md text-white text-[10px] font-bold flex items-center gap-1">
                                <span>⚡ {{ $prepTime }}</span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-zinc-400 dark:text-zinc-500 mb-1">
                                    <span class="truncate font-semibold">{{ $restaurantName }}</span>
                                    <span>2.1 km</span>
                                </div>

                                <div class="flex items-start gap-2">
                                    <!-- Indian Standard Veg / Non-Veg Icon -->
                                    <div class="w-4 h-4 rounded-sm border-2 {{ $isVeg ? 'border-emerald-600' : 'border-rose-600' }} flex items-center justify-center p-0.5 shrink-0 mt-0.5">
                                        <div class="w-2 h-2 rounded-full {{ $isVeg ? 'bg-emerald-600' : 'bg-rose-600' }}"></div>
                                    </div>

                                    <h4 class="text-sm sm:text-base font-black text-zinc-950 dark:text-white group-hover:text-[#fc8019] line-clamp-1 leading-snug cursor-pointer transition-colors"
                                        @click="openDishModal({
                                            title: '{{ addslashes($dish->title) }}',
                                            price: {{ $priceRupees }},
                                            image: '{{ $imgUrl }}',
                                            restaurant: '{{ addslashes($restaurantName) }}',
                                            calories: '{{ $calories }}',
                                            time: '{{ $prepTime }}',
                                            desc: '{{ addslashes($dish->description ?? 'Freshly prepared with handpicked ingredients, aromatic herbs, and balanced seasoning.') }}'
                                        })">
                                        {{ $dish->title }}
                                    </h4>
                                </div>
                            </div>

                            <div class="pt-4 mt-3 border-t border-orange-100 dark:border-zinc-800 flex items-center justify-between">
                                <div>
                                    <span class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400">
                                        ₹{{ number_format($priceRupees, 0) }}
                                    </span>
                                    <span class="text-[11px] text-zinc-400 line-through ml-1.5">
                                        ₹{{ number_format($priceRupees * 1.4, 0) }}
                                    </span>
                                </div>

                                <!-- Add to Basket Pill Button with Domino's / Swiggy Pulse -->
                                <button type="button" 
                                        @click="openDishModal({
                                            title: '{{ addslashes($dish->title) }}',
                                            price: {{ $priceRupees }},
                                            image: '{{ $imgUrl }}',
                                            restaurant: '{{ addslashes($restaurantName) }}',
                                            calories: '{{ $calories }}',
                                            time: '{{ $prepTime }}',
                                            desc: '{{ addslashes($dish->description ?? 'Freshly prepared with handpicked ingredients, aromatic herbs, and balanced seasoning.') }}'
                                        })"
                                        class="px-4 py-2 rounded-2xl bg-gradient-to-r from-[#fc8019] to-[#ff5200] hover:from-[#ff5200] hover:to-[#e04500] text-white text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-md shadow-orange-500/25 cursor-pointer flex items-center gap-1.5">
                                    <span>+ ADD</span>
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Top Verified Kitchens & Brands -->
            @if($restaurants->isNotEmpty())
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                            <span>Top Chef Kitchens &amp; Brands</span>
                            <span class="text-amber-500">🏆</span>
                        </h3>
                        <p class="text-xs text-zinc-400">Inspected for 100% kitchen hygiene and premium ingredients</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5 mb-12">
                    @foreach($restaurants as $rest)
                        <div class="p-4 rounded-3xl glass-swiggy flex items-center gap-4 hover:border-orange-400 transition-all duration-300 hover:shadow-lg">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 text-white flex items-center justify-center font-black text-2xl shrink-0 shadow-md">
                                👨‍🍳
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-black text-zinc-950 dark:text-white truncate">{{ $rest->shop_name }}</h4>
                                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[10px] font-black shrink-0">⭐ 4.9</span>
                                </div>
                                <p class="text-[11px] text-zinc-400 truncate mt-0.5">{{ $rest->city ?? 'Local Gourmet Hub' }} • 20-25 mins</p>
                                <span class="inline-block mt-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-extrabold uppercase tracking-wide">
                                    ✓ FREE DELIVERY WITH SWIGGY ONE
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <!-- ======================================================== -->
        <!-- 6. DOMINO'S / SWIGGY DISH CUSTOMIZATION MODAL           -->
        <!-- ======================================================== -->
        <div x-show="activeDishModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="activeDishModal = false" 
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl border border-orange-200 dark:border-zinc-800 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Close Button -->
                <button type="button" @click="activeDishModal = false" class="absolute top-4 right-4 z-20 w-8 h-8 rounded-full bg-black/60 text-white flex items-center justify-center font-bold hover:bg-black/80 cursor-pointer">
                    ✕
                </button>

                <!-- Hero Image with Gradient Overlay -->
                <div class="relative w-full aspect-16/10 bg-zinc-100 dark:bg-zinc-900">
                    <img :src="selectedDish ? selectedDish.image : ''" alt="Dish photo" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 px-3 py-1 rounded-xl bg-black/70 backdrop-blur-md text-white text-xs font-bold flex items-center gap-1.5">
                        <span class="text-amber-400">★</span> 4.8 (2,400+ reviews)
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex items-baseline justify-between mb-1">
                        <h3 class="text-xl sm:text-2xl font-black text-zinc-950 dark:text-white" x-text="selectedDish ? selectedDish.title : ''"></h3>
                        <span class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400">
                            ₹<span x-text="selectedDish ? (selectedDish.price * dishQty) : 0"></span>
                        </span>
                    </div>

                    <p class="text-xs text-zinc-500 mb-4 leading-relaxed" x-text="selectedDish ? selectedDish.desc : ''"></p>

                    <!-- Nutrition & Delivery Badges -->
                    <div class="flex items-center gap-3 text-xs text-zinc-700 dark:text-zinc-300 mb-5 p-3 rounded-2xl bg-orange-50 dark:bg-zinc-900 border border-orange-100 dark:border-zinc-800">
                        <span>🔥 <strong x-text="selectedDish ? selectedDish.calories : '380 kcal'"></strong></span>
                        <span>⏱️ <strong x-text="selectedDish ? selectedDish.time : '25 min'"></strong> Fast Delivery</span>
                        <span>🌱 <strong>100% Certified Hygiene</strong></span>
                    </div>

                    <!-- Portion / Domino's Crust Customizer -->
                    <div class="mb-5">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-zinc-400 mb-2">Select Portion / Crust</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="selectedPortion = 'Regular'" 
                                    :class="selectedPortion === 'Regular' ? 'border-[#fc8019] bg-orange-50 text-orange-900 dark:bg-orange-950/40 dark:text-white font-bold ring-2 ring-orange-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-2xl border text-xs text-center transition cursor-pointer">
                                Regular
                            </button>
                            <button type="button" @click="selectedPortion = 'Medium (+₹40)'" 
                                    :class="selectedPortion === 'Medium (+₹40)' ? 'border-[#fc8019] bg-orange-50 text-orange-900 dark:bg-orange-950/40 dark:text-white font-bold ring-2 ring-orange-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-2xl border text-xs text-center transition cursor-pointer">
                                Medium (+₹40)
                            </button>
                            <button type="button" @click="selectedPortion = 'Large (+₹80)'" 
                                    :class="selectedPortion === 'Large (+₹80)' ? 'border-[#fc8019] bg-orange-50 text-orange-900 dark:bg-orange-950/40 dark:text-white font-bold ring-2 ring-orange-400' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2.5 rounded-2xl border text-xs text-center transition cursor-pointer">
                                Large (+₹80)
                            </button>
                        </div>
                    </div>

                    <!-- Quantity Stepper & Add to Basket -->
                    <div class="flex items-center gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-1">
                            <button type="button" @click="if (dishQty > 1) dishQty--" class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-800 font-black text-sm flex items-center justify-center hover:bg-zinc-200 cursor-pointer">-</button>
                            <span class="w-10 text-center font-black text-sm" x-text="dishQty"></span>
                            <button type="button" @click="dishQty++" class="w-9 h-9 rounded-xl bg-white dark:bg-zinc-800 font-black text-sm flex items-center justify-center hover:bg-zinc-200 cursor-pointer">+</button>
                        </div>

                        <button type="button" @click="addToCart()" 
                                class="flex-1 py-4 rounded-2xl bg-gradient-to-r from-[#fc8019] via-[#ff5200] to-[#10b981] hover:opacity-95 text-white font-black text-xs uppercase tracking-wider transition shadow-xl shadow-orange-500/25 active:scale-95 cursor-pointer text-center">
                            Add to Basket • ₹<span x-text="selectedDish ? (selectedDish.price * dishQty) : 0"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 7. LIVE FLOATING CART BAR (SWIGGY STYLE BOTTOM PILL)    -->
        <!-- ======================================================== -->
        <div x-show="cartCount > 0" 
             x-cloak 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-y-24 opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="translate-y-24 opacity-0"
             class="fixed bottom-6 inset-x-4 max-w-xl mx-auto z-40">
            <div @click="cartDrawer = true" 
                 class="p-4 rounded-3xl bg-gradient-to-r from-emerald-600 to-teal-700 text-white shadow-2xl border border-emerald-400 flex items-center justify-between cursor-pointer hover:scale-[1.02] transition-transform">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-white text-emerald-700 flex items-center justify-center font-black text-lg shadow">
                        🛍️
                    </div>
                    <div>
                        <div class="text-xs font-black uppercase tracking-wider" x-text="cartCount + ' ITEMS SELECTED'"></div>
                        <div class="text-sm font-extrabold text-emerald-100">Total: ₹<span x-text="cartTotal"></span></div>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 font-black text-xs uppercase tracking-wider bg-white/20 backdrop-blur-md px-4 py-2 rounded-2xl">
                    <span>View Cart</span>
                    <span>→</span>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 8. CART SLIDE-OVER DRAWER                               -->
        <!-- ======================================================== -->
        <div x-show="cartDrawer" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-hidden bg-black/60 backdrop-blur-xs flex justify-end"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="cartDrawer = false" 
                 class="w-full max-w-md bg-white dark:bg-zinc-950 h-full p-6 flex flex-col justify-between shadow-2xl border-l border-zinc-200 dark:border-zinc-800"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full">
                
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-100 dark:border-zinc-800 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="text-xl font-black text-zinc-950 dark:text-white">Your Order Basket</span>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold" x-text="cartCount + ' items'"></span>
                        </div>
                        <button type="button" @click="cartDrawer = false" class="text-zinc-400 hover:text-zinc-600 text-xl font-bold p-1 cursor-pointer">✕</button>
                    </div>

                    <!-- Cart Items List -->
                    <template x-if="cartItems.length === 0">
                        <div class="text-center py-16">
                            <div class="text-5xl mb-3">🍽️</div>
                            <h4 class="text-sm font-black text-zinc-900 dark:text-white">Your basket is empty</h4>
                            <p class="text-xs text-zinc-400 mt-1">Explore our restaurants and add your favorite dishes.</p>
                        </div>
                    </template>

                    <div class="space-y-3 max-h-[60vh] overflow-y-auto">
                        <template x-for="(item, idx) in cartItems" :key="idx">
                            <div class="p-3.5 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <img :src="item.image" alt="item" class="w-12 h-12 rounded-xl object-cover">
                                    <div>
                                        <h5 class="text-xs font-black text-zinc-900 dark:text-white" x-text="item.title"></h5>
                                        <div class="text-[10px] text-zinc-400" x-text="item.portion + ' × ' + item.qty"></div>
                                    </div>
                                </div>
                                <span class="text-xs font-black text-emerald-600 dark:text-emerald-400" x-text="'₹' + item.price"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Footer Summary & Checkout -->
                <div x-show="cartItems.length > 0" class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
                    <div class="space-y-2 text-xs text-zinc-500 mb-4">
                        <div class="flex justify-between">
                            <span>Item Total</span>
                            <span class="font-bold text-zinc-900 dark:text-white" x-text="'₹' + cartTotal"></span>
                        </div>
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span>Delivery Fee</span>
                            <span>FREE (Promo)</span>
                        </div>
                        <div class="flex justify-between text-base font-black text-zinc-950 dark:text-white pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <span>To Pay</span>
                            <span class="text-emerald-600" x-text="'₹' + cartTotal"></span>
                        </div>
                    </div>

                    <button type="button" @click="alert('Order placed successfully via K71 Fast Food Dispatch!'); clearCart();" 
                            class="w-full py-4 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-700 hover:opacity-95 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-600/30 active:scale-95 cursor-pointer">
                        Place Order • 25 Mins Express Delivery
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-layouts.public>
