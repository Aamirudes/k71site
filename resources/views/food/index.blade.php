<x-layouts.public 
    title="Fresh Gourmet & Fast Food Delivery — K71 Food"
    metaDescription="Order freshly prepared meals, burgers, pizza, salads, biryani, and desserts on K71 Food. 25-minute fast delivery, live tracking, and top-rated restaurants."
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
    }" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300 pb-16">

        <!-- TOP BAR: DELIVER TO & ICONS (Exact Image 3 Style) -->
        <div class="mb-5 flex items-center justify-between gap-3 p-3.5 bg-white dark:bg-zinc-900 rounded-3xl border border-emerald-100 dark:border-zinc-800 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">food.{{ config('k71.root_domain', 'marblemandi.org') }} • Deliver to</div>
                    <div class="flex items-center gap-1 font-black text-xs sm:text-sm text-zinc-950 dark:text-white cursor-pointer">
                        <span>Times Square / Local City, 10036</span>
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Header Action Icons -->
            <div class="flex items-center gap-2">
                <!-- Pure Veg Switch -->
                <a href="{{ $pureVegOnly ? route('food.index') : route('food.index', ['pure_veg' => 1]) }}" 
                   class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 {{ $pureVegOnly ? 'bg-emerald-600 text-white shadow-xs' : 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800' }}">
                    <span class="w-2.5 h-2.5 rounded-full {{ $pureVegOnly ? 'bg-white' : 'bg-emerald-600' }}"></span>
                    <span class="hidden sm:inline">Pure Veg</span>
                </a>

                <!-- Filter Button -->
                <button type="button" @click="filterModal = true" class="p-2.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-700 dark:text-zinc-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                </button>

                <!-- Cart Button -->
                <button type="button" @click="cartDrawer = true" class="relative p-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-md shadow-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-1.5 -right-1.5 w-5 h-5 rounded-full bg-rose-500 text-white text-[10px] font-black flex items-center justify-center border-2 border-white dark:border-zinc-900"></span>
                </button>
            </div>
        </div>

        <!-- SPECIAL OFFERS CAROUSEL HERO (Image 3 Top Feature) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-600 via-emerald-500 to-teal-600 p-6 sm:p-8 text-white shadow-xl mb-6">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="max-w-md text-center md:text-left">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black tracking-wider uppercase mb-3">
                        <span>🔥 Today's Special Offers</span>
                    </div>
                    <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight mb-2">
                        30% DISCOUNT <br>
                        <span class="text-emerald-100">ONLY FOR TODAY!</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-emerald-50 mb-5 leading-relaxed">
                        Enjoy fresh chef-crafted burgers, hand-tossed gourmet pizzas, and crunchy organic salads delivered to your doorstep in 25 mins.
                    </p>
                    <button type="button" @click="activeFilterSort = 'deals'" 
                            class="px-6 py-2.5 rounded-2xl bg-white text-emerald-800 hover:bg-emerald-50 font-black text-xs uppercase tracking-wider shadow-lg transition active:scale-95">
                        Claim 30% Off
                    </button>
                </div>

                <div class="relative shrink-0 flex items-center justify-center">
                    <div class="w-44 h-44 sm:w-52 sm:h-52 rounded-full bg-white/10 backdrop-blur-md border border-white/20 p-2 shadow-2xl flex items-center justify-center">
                        <img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=400&q=80" 
                             alt="Gourmet Burger" class="w-full h-full object-cover rounded-full shadow-lg">
                    </div>
                    <div class="absolute -bottom-2 -left-2 px-3 py-1 rounded-full bg-white text-emerald-800 text-xs font-black shadow-lg">
                        ⭐ 4.9 Super Chef
                    </div>
                </div>
            </div>
        </div>

        <!-- SEARCH BAR (Image 3 Style) -->
        <div class="mb-6">
            <form action="{{ route('food.index') }}" method="GET" class="relative">
                <div class="absolute left-4 top-3.5 text-zinc-400">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search restaurant, burger, salad, artisan pizza, dessert..." 
                       class="w-full pl-12 pr-28 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 border border-emerald-100 dark:border-zinc-800 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none transition shadow-xs">
                <button type="submit" class="absolute right-2 top-2 px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                    Search
                </button>
            </form>
        </div>

        <!-- CATEGORIES HORIZONTAL CIRCLES (Exact Image 3 Screen 3) -->
        <div class="mb-8">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">More Categories</h3>
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-bold cursor-pointer">View All</span>
            </div>

            <div class="flex items-center gap-4 overflow-x-auto no-scrollbar pb-2">
                @php
                    $foodIcons = [
                        ['name' => 'All', 'icon' => '🍽️', 'query' => ''],
                        ['name' => 'Hamburger', 'icon' => '🍔', 'query' => 'burger'],
                        ['name' => 'Pizza', 'icon' => '🍕', 'query' => 'pizza'],
                        ['name' => 'Noodles', 'icon' => '🍜', 'query' => 'noodles'],
                        ['name' => 'Meat', 'icon' => '🥩', 'query' => 'meat'],
                        ['name' => 'Vegetables', 'icon' => '🥗', 'query' => 'salad'],
                        ['name' => 'Dessert', 'icon' => '🍰', 'query' => 'dessert'],
                        ['name' => 'Drink', 'icon' => '🧃', 'query' => 'beverage'],
                        ['name' => 'Bread', 'icon' => '🥖', 'query' => 'bakery'],
                        ['name' => 'Croissant', 'icon' => '🥐', 'query' => 'croissant'],
                        ['name' => 'Pancakes', 'icon' => '🥞', 'query' => 'pancake'],
                        ['name' => 'Cheese', 'icon' => '🧀', 'query' => 'cheese'],
                        ['name' => 'Fries', 'icon' => '🍟', 'query' => 'fries'],
                    ];
                @endphp
                @foreach($foodIcons as $item)
                    <a href="{{ route('food.index', ['search' => $item['query']]) }}" 
                       class="group flex flex-col items-center shrink-0 cursor-pointer text-center">
                        <div class="w-16 h-16 rounded-2xl bg-white dark:bg-zinc-900 border border-emerald-100 dark:border-zinc-800 group-hover:border-emerald-500 shadow-xs flex items-center justify-center text-2xl group-hover:scale-105 transition-all duration-200">
                            {{ $item['icon'] }}
                        </div>
                        <span class="text-xs font-bold text-zinc-700 dark:text-zinc-300 group-hover:text-emerald-600 dark:group-hover:text-emerald-400 mt-2 transition">
                            {{ $item['name'] }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- RECOMMENDED DISHES & RESTAURANTS (Image 3 Grid Style) -->
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Recommended For You 😋</h3>
            <span class="text-xs text-zinc-400">Fast 25-Min Dispatch</span>
        </div>

        @if($dishes->isEmpty() && $restaurants->isEmpty())
            <div class="text-center py-16 px-4 bg-emerald-50/50 dark:bg-zinc-900/50 rounded-3xl border border-emerald-100 dark:border-zinc-800 max-w-md mx-auto my-8">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    🥗
                </div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-white mb-1">No dishes found</h3>
                <p class="text-xs text-zinc-500 mb-4">Try searching for burger, pizza, salad, or biryani.</p>
                <a href="{{ route('food.index') }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold">
                    View All Menus
                </a>
            </div>
        @else
            <!-- Dishes Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5 mb-10">
                @foreach($dishes as $dish)
                    @php
                        $priceRupees = $dish->base_price / 100;
                        $firstImg = $dish->images->first()?->path;
                        $isVeg = $dish->getCustomField('is_veg', true);
                        $calories = $dish->getCustomField('calories', '320 kcal');
                        $prepTime = $dish->getCustomField('prep_time', '20-25 min');
                        $restaurantName = $dish->vendor?->shop_name ?? 'Artisan Kitchen';
                        $imgUrl = $firstImg ? asset('storage/' . $firstImg) : 'https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=400&q=80';
                    @endphp
                    <div class="group rounded-3xl bg-white dark:bg-zinc-900 border border-emerald-100/80 dark:border-zinc-800 hover:border-emerald-400 overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-lg relative">
                        
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
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                 onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=400&q=80';">

                            <!-- Promo Pill Top-Left -->
                            <div class="absolute top-3 left-3 flex items-center gap-1.5">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white shadow-xs">
                                    30% OFF
                                </span>
                            </div>

                            <!-- Rating Top-Right -->
                            <div class="absolute top-3 right-3 px-2 py-0.5 rounded-lg bg-black/60 backdrop-blur-md text-white text-[10px] font-black flex items-center gap-1">
                                <span class="text-amber-400">★</span> 4.8
                            </div>
                        </div>

                        <!-- Dish Info -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-[11px] text-zinc-400 dark:text-zinc-500 mb-1">
                                    <span class="truncate">{{ $restaurantName }}</span>
                                    <span>2.4 km • {{ $prepTime }}</span>
                                </div>

                                <h4 class="text-sm font-bold text-zinc-950 dark:text-white group-hover:text-emerald-600 line-clamp-1 leading-snug cursor-pointer"
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

                            <div class="pt-3 mt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                                <div>
                                    <span class="text-base font-black text-emerald-600 dark:text-emerald-400">
                                        ₹{{ number_format($priceRupees, 0) }}
                                    </span>
                                    <span class="text-[10px] text-zinc-400 line-through ml-1">
                                        ₹{{ number_format($priceRupees * 1.3, 0) }}
                                    </span>
                                </div>

                                <!-- Add to Basket Pill Button -->
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
                                        class="px-3.5 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-xs cursor-pointer flex items-center gap-1">
                                    <span>+ Add</span>
                                </button>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Top Restaurants Section -->
            @if($restaurants->isNotEmpty())
                <div class="mb-6 flex items-center justify-between">
                    <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Top Rated Restaurants 🏆</h3>
                    <span class="text-xs text-zinc-400">Verified Hygiene</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-5 mb-10">
                    @foreach($restaurants as $rest)
                        <div class="p-4 rounded-3xl bg-white dark:bg-zinc-900 border border-emerald-100 dark:border-zinc-800 flex items-center gap-4 shadow-xs hover:border-emerald-400 transition">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 flex items-center justify-center font-black text-xl shrink-0">
                                🍴
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-black text-zinc-950 dark:text-white truncate">{{ $rest->shop_name }}</h4>
                                    <span class="px-1.5 py-0.5 rounded-md bg-emerald-100 text-emerald-800 text-[9px] font-bold shrink-0">⭐ 4.8</span>
                                </div>
                                <p class="text-[11px] text-zinc-400 truncate mt-0.5">{{ $rest->city ?? 'Local Gourmet' }} • 25-30 mins</p>
                                <span class="inline-block mt-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-bold">Free Delivery on First 3 Orders</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <!-- ======================================================== -->
        <!-- DETAILED DISH CUSTOMIZATION MODAL (Image 3 Screen 19-20) -->
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
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-md w-full overflow-hidden shadow-2xl border border-emerald-100 dark:border-zinc-800 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Close Button -->
                <button type="button" @click="activeDishModal = false" class="absolute top-4 right-4 z-20 w-8 h-8 rounded-full bg-black/50 text-white flex items-center justify-center font-bold hover:bg-black/70">
                    ✕
                </button>

                <!-- Hero Image -->
                <div class="relative w-full aspect-16/10 bg-zinc-100 dark:bg-zinc-900">
                    <img :src="selectedDish ? selectedDish.image : ''" alt="Dish photo" class="w-full h-full object-cover">
                    <div class="absolute bottom-3 left-3 px-2.5 py-1 rounded-lg bg-black/70 backdrop-blur-md text-white text-[11px] font-bold flex items-center gap-1.5">
                        <span class="text-amber-400">★</span> 4.8 (1.4k reviews)
                    </div>
                </div>

                <div class="p-6">
                    <div class="flex items-baseline justify-between mb-1">
                        <h3 class="text-xl font-black text-zinc-950 dark:text-white" x-text="selectedDish ? selectedDish.title : ''"></h3>
                        <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">
                            ₹<span x-text="selectedDish ? (selectedDish.price * dishQty) : 0"></span>
                        </span>
                    </div>

                    <p class="text-xs text-zinc-500 mb-4 leading-relaxed" x-text="selectedDish ? selectedDish.desc : ''"></p>

                    <div class="flex items-center gap-3 text-xs text-zinc-600 dark:text-zinc-400 mb-5 p-2.5 bg-emerald-50/50 dark:bg-zinc-900 rounded-2xl border border-emerald-100 dark:border-zinc-800">
                        <span>🔥 <strong x-text="selectedDish ? selectedDish.calories : '320 kcal'"></strong></span>
                        <span>⏱️ <strong x-text="selectedDish ? selectedDish.time : '25 min'"></strong> Delivery</span>
                        <span>🌱 <strong>100% Organic Prep</strong></span>
                    </div>

                    <!-- Portion Size -->
                    <div class="mb-5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Select Portion Size</label>
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" @click="selectedPortion = 'Regular'" 
                                    :class="selectedPortion === 'Regular' ? 'border-emerald-600 bg-emerald-50 text-emerald-900 dark:bg-emerald-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2 rounded-xl border text-xs text-center">
                                Regular
                            </button>
                            <button type="button" @click="selectedPortion = 'Medium (+₹40)'" 
                                    :class="selectedPortion === 'Medium (+₹40)' ? 'border-emerald-600 bg-emerald-50 text-emerald-900 dark:bg-emerald-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2 rounded-xl border text-xs text-center">
                                Medium
                            </button>
                            <button type="button" @click="selectedPortion = 'Large (+₹80)'" 
                                    :class="selectedPortion === 'Large (+₹80)' ? 'border-emerald-600 bg-emerald-50 text-emerald-900 dark:bg-emerald-950/40 dark:text-white font-bold' : 'border-zinc-200 dark:border-zinc-800 text-zinc-600 dark:text-zinc-400'"
                                    class="py-2 rounded-xl border text-xs text-center">
                                Large
                            </button>
                        </div>
                    </div>

                    <!-- Quantity Stepper & Add to Basket -->
                    <div class="flex items-center gap-3 pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="flex items-center rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-1">
                            <button type="button" @click="if (dishQty > 1) dishQty--" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 font-black text-sm flex items-center justify-center hover:bg-zinc-200">-</button>
                            <span class="w-10 text-center font-black text-sm" x-text="dishQty"></span>
                            <button type="button" @click="dishQty++" class="w-8 h-8 rounded-xl bg-white dark:bg-zinc-800 font-black text-sm flex items-center justify-center hover:bg-zinc-200">+</button>
                        </div>

                        <button type="button" @click="addToCart()" 
                                class="flex-1 py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-600/20 active:scale-95 cursor-pointer text-center">
                            Add to Basket • ₹<span x-text="selectedDish ? (selectedDish.price * dishQty) : 0"></span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ======================================================== -->
        <!-- CART SLIDE-OVER DRAWER -->
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
                        <button type="button" @click="cartDrawer = false" class="text-zinc-400 hover:text-zinc-600 text-xl font-bold">✕</button>
                    </div>

                    <!-- Cart Items List -->
                    <template x-if="cartItems.length === 0">
                        <div class="text-center py-16">
                            <div class="text-4xl mb-2">🍽️</div>
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">Your basket is empty</h4>
                            <p class="text-xs text-zinc-400 mt-1">Add items from the menu to get started.</p>
                        </div>
                    </template>

                    <div class="space-y-3 max-h-[60vh] overflow-y-auto">
                        <template x-for="(item, idx) in cartItems" :key="idx">
                            <div class="p-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <img :src="item.image" alt="item" class="w-12 h-12 rounded-xl object-cover">
                                    <div>
                                        <h5 class="text-xs font-bold text-zinc-900 dark:text-white" x-text="item.title"></h5>
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
                    <div class="space-y-1.5 text-xs text-zinc-500 mb-4">
                        <div class="flex justify-between">
                            <span>Subtotal</span>
                            <span class="font-bold text-zinc-900 dark:text-white" x-text="'₹' + cartTotal"></span>
                        </div>
                        <div class="flex justify-between text-emerald-600 font-bold">
                            <span>Delivery Fee</span>
                            <span>FREE (Promo)</span>
                        </div>
                        <div class="flex justify-between text-sm font-black text-zinc-950 dark:text-white pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <span>Grand Total</span>
                            <span class="text-emerald-600" x-text="'₹' + cartTotal"></span>
                        </div>
                    </div>

                    <button type="button" @click="alert('Order placed successfully via K71 Fast Food Dispatch!'); clearCart();" 
                            class="w-full py-3.5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-emerald-600/30 active:scale-95">
                        Place Order • 25 Mins Delivery
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-layouts.public>
