<x-layouts.food 
    title="K71 Food — Order Online from Top Fresh Cloud Kitchens & Restaurants"
    metaDescription="Order fresh handi biryani, wood-fired artisan pizza, smash burgers, pure veg royal thalis, and desserts on K71 Food. 25-minute superfast doorstep delivery, FSSAI hygiene verified, and zero delivery fee offers."
>
    @php
        $dishes = $trendingDishes ?? collect();
        $seededRestaurant = $restaurants->first();
        $restaurantMenuSlug = $seededRestaurant?->slug ?? 'dawat-e-khaas-royal-biryani-kebabs';
    @endphp

    <div x-data="k71FoodHomepage()" class="space-y-8 sm:space-y-12 pb-16">

        <!-- 1. HIGH-IMPACT FRESH & LOCAL HERO & LIVE SEARCH SECTION -->
        <section class="relative bg-gradient-to-b from-orange-500/15 via-amber-500/5 to-transparent pt-6 sm:pt-10 pb-8 px-4 sm:px-6 lg:px-8 border-b border-orange-100/70">
            <div class="max-w-7xl mx-auto space-y-6">
                
                @php
                    $fs = $foodSettings ?? [];
                @endphp
                <div class="max-w-3xl space-y-3">
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-orange-100/90 border border-orange-200 text-[#c2410c] text-[11px] font-black uppercase tracking-wider flex-wrap shadow-2xs">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ $fs['food_hero_badge'] ?? '⚡ 25-30 MINUTE EXPRESS DOORSTEP DISPATCH' }}</span>
                        </span>
                        <span class="text-stone-300">•</span>
                        <button type="button" @click="locationModalOpen = true" class="hover:underline flex items-center gap-1 cursor-pointer">
                            <span>📍 DELIVERING TO</span>
                            <span class="font-black underline decoration-orange-400" x-text="currentLocation">Jaipur Central</span>
                            <span>▾</span>
                        </button>
                    </div>
                    
                    <h1 class="k71-display text-stone-950 tracking-tight leading-tight">
                        Crave it? Get it hot &amp; fresh from <span class="text-[#EA580C]">your favourite kitchens</span>.
                    </h1>
                    <p class="text-sm sm:text-base text-stone-600 font-medium leading-relaxed">
                        {{ $fs['food_hero_subtitle'] ?? 'Explore hand-crafted dum biryanis, wood-fired artisan pizzas, sizzling tandoori kebabs, and pure veg royal thalis.' }}
                    </p>
                </div>

                <!-- Live Auto-Suggest Search Bar with Glowing Focus -->
                <div class="max-w-3xl relative" x-data="{ query: '{{ $search ?? '' }}', results: null, loading: false }">
                    <form action="{{ route('food.index') }}" method="GET" class="relative">
                        <div class="relative flex items-center bg-white rounded-2xl sm:rounded-3xl border-2 border-stone-200/90 shadow-lg shadow-orange-500/5 focus-within:border-[#ea580c] focus-within:ring-4 focus-within:ring-orange-500/15 transition-all overflow-hidden">
                            <div class="pl-4 sm:pl-5 text-[#ea580c] flex items-center">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" x-model="query"
                                   @input.debounce.300ms="if(query.length >= 2){ loading = true; fetch('/food/suggest?q=' + encodeURIComponent(query)).then(r=>r.json()).then(d=>{ results = d; loading = false; }); } else { results = null; }"
                                   placeholder="Search restaurant, dish, or cuisine (e.g. Biryani, Pizza, Dal Makhani)..." 
                                   class="w-full bg-transparent py-4 pl-3.5 pr-28 text-sm sm:text-base font-semibold text-stone-900 placeholder:text-stone-400 focus:outline-none">
                            
                            <div class="absolute right-2 sm:right-3 flex items-center gap-1.5">
                                @if($pureVegOnly)
                                    <input type="hidden" name="pure_veg" value="1">
                                @endif
                                <button type="submit" class="px-5 py-2.5 rounded-xl sm:rounded-2xl bg-gradient-to-r from-[#ea580c] to-[#dc2626] hover:brightness-105 text-white text-xs sm:text-sm font-black shadow-md shadow-orange-500/25 transition cursor-pointer flex items-center gap-1.5">
                                    <span>Search</span>
                                    <span>→</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Auto-Suggest Dropdown Box -->
                    <div x-show="results && (results.restaurants?.length > 0 || results.dishes?.length > 0)" 
                         x-cloak
                         @click.outside="results = null"
                         class="absolute top-full left-0 right-0 mt-2 bg-white rounded-3xl shadow-2xl border border-stone-200 p-4 z-50 space-y-4 max-h-96 overflow-y-auto no-scrollbar">
                        
                        <!-- Matching Restaurants -->
                        <template x-if="results?.restaurants?.length > 0">
                            <div>
                                <span class="text-[10.5px] font-black uppercase tracking-wider text-stone-600 block mb-2">Restaurants &amp; Kitchens</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="r in results.restaurants" :key="r.name">
                                        <a :href="r.url" class="flex items-center gap-3 p-2 rounded-xl hover:bg-orange-50 transition border border-transparent hover:border-orange-100">
                                            <div class="w-10 h-10 rounded-lg bg-stone-100 overflow-hidden shrink-0">
                                                <img :src="r.image || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=150&q=80'" :alt="r.name" class="w-full h-full object-cover">
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-stone-900 truncate" x-text="r.name"></div>
                                                <div class="text-[10px] text-stone-600" x-text="'📍 ' + (r.city || 'Express Delivery')"></div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Matching Dishes -->
                        <template x-if="results?.dishes?.length > 0">
                            <div>
                                <span class="text-[10.5px] font-black uppercase tracking-wider text-stone-600 block mb-2">Popular Dishes</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="d in results.dishes" :key="d.title">
                                        <a :href="d.restaurant_url" class="flex items-center justify-between gap-3 p-2 rounded-xl hover:bg-orange-50 transition border border-transparent hover:border-orange-100">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-3.5 h-3.5 rounded-xs border-2 flex items-center justify-center p-0.5 shrink-0"
                                                      :class="d.dietary === 'Veg' ? 'border-emerald-600' : 'border-rose-600'">
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="d.dietary === 'Veg' ? 'bg-emerald-600' : 'bg-rose-600'"></span>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-stone-900 truncate" x-text="d.title"></div>
                                                    <div class="text-[10px] text-stone-500 truncate" x-text="d.restaurant"></div>
                                                </div>
                                            </div>
                                            <span class="text-xs font-black text-stone-900 shrink-0" x-text="'₹' + d.price"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Trending Quick Chips with Food Emojis -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-1">
                    <span class="text-[11px] font-black uppercase tracking-wider text-stone-600 shrink-0">Popular:</span>
                    @php
                        $popularChips = [
                            ['label' => 'Dum Biryani', 'emoji' => '🍲'],
                            ['label' => 'Artisan Pizza', 'emoji' => '🍕'],
                            ['label' => 'Butter Chicken', 'emoji' => '🍗'],
                            ['label' => 'Paneer Tikka', 'emoji' => '🧀'],
                            ['label' => 'Smash Burger', 'emoji' => '🍔'],
                            ['label' => 'Amritsari Kulcha', 'emoji' => '🥞'],
                            ['label' => 'Mysore Dosa', 'emoji' => '🥞'],
                            ['label' => 'Hakka Noodles', 'emoji' => '🍜'],
                            ['label' => 'Belgian Waffle', 'emoji' => '🧇'],
                            ['label' => 'Royal Chai', 'emoji' => '☕'],
                        ];
                    @endphp
                    @foreach($popularChips as $chip)
                        <a href="{{ route('food.index', ['search' => $chip['label']]) }}" 
                           class="px-3.5 py-1.5 rounded-full bg-white hover:bg-orange-50 border border-stone-200 hover:border-orange-300 text-xs font-bold text-stone-700 hover:text-[#ea580c] transition-all shrink-0 shadow-2xs hover:scale-105 flex items-center gap-1.5">
                            <span>{{ $chip['emoji'] }}</span>
                            <span>{{ $chip['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Live Foodie Assurance Banner -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-3">
                    <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/80 backdrop-blur-sm border border-orange-100 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-orange-100 text-[#ea580c] flex items-center justify-center text-sm font-bold shrink-0">⚡</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-black text-stone-900 truncate">25 Min Delivery</div>
                            <div class="text-[10px] text-stone-500 truncate">Hot insulated bags</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/80 backdrop-blur-sm border border-emerald-100 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-sm font-bold shrink-0">🛡️</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-black text-stone-900 truncate">FSSAI Certified</div>
                            <div class="text-[10px] text-stone-500 truncate">100% hygiene tested</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/80 backdrop-blur-sm border border-amber-100 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center text-sm font-bold shrink-0">🔥</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-black text-stone-900 truncate">Freshly Prepared</div>
                            <div class="text-[10px] text-stone-500 truncate">Cooked on order</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3 rounded-2xl bg-white/80 backdrop-blur-sm border border-purple-100 shadow-2xs">
                        <div class="w-8 h-8 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-sm font-bold shrink-0">🛵</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-black text-stone-900 truncate">Live Tracking</div>
                            <div class="text-[10px] text-stone-500 truncate">Real-time GPS dispatch</div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10 sm:space-y-14">

            <!-- 2. "WHAT'S ON YOUR MIND?" VISUAL CUISINE DISCOVERY RAIL -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                            <span>What's on your mind?</span>
                            <span class="text-xl">✨</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-stone-500 font-medium">Explore curated collections of top Indian and international comfort food.</p>
                    </div>
                </div>

                <div class="grid grid-cols-4 sm:grid-cols-6 lg:grid-cols-8 gap-3 sm:gap-4">
                    @php
                        $cuisineCollections = [
                            ['name' => 'Dum Biryani', 'slug' => 'Biryani', 'icon' => '🍲', 'img' => 'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=300&q=80'],
                            ['name' => 'Artisan Pizza', 'slug' => 'Pizza', 'icon' => '🍕', 'img' => 'https://images.unsplash.com/photo-1604382355076-af4b0eb60143?auto=format&fit=crop&w=300&q=80'],
                            ['name' => 'Smash Burgers', 'slug' => 'Burger', 'icon' => '🍔', 'img' => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=300&q=80'],
                            ['name' => 'North Indian', 'slug' => 'North Indian', 'icon' => '🍛', 'img' => 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?auto=format&fit=crop&w=300&q=80'],
                            ['name' => 'South Indian', 'slug' => 'South Indian', 'icon' => '🥞', 'img' => 'https://images.unsplash.com/photo-1668236543090-82eba5ee5976?auto=format&fit=crop&w=300&q=80'],
                            ['name' => 'Pan-Asian Wok', 'slug' => 'Chinese', 'icon' => '🍜', 'img' => 'https://images.unsplash.com/photo-1585032226651-759b368d7246?auto=format&fit=crop&w=300&q=80'],
                            ['name' => 'Desserts & Cakes', 'slug' => 'Dessert', 'icon' => '🍰', 'img' => 'https://images.unsplash.com/photo-1562376552-0d160a2f238d?auto=format&fit=crop&w=300&q=80'],
                            ['name' => 'Chai & Snacks', 'slug' => 'Coffee', 'icon' => '☕', 'img' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=300&q=80'],
                        ];
                    @endphp

                    @foreach($cuisineCollections as $col)
                        <a href="{{ route('food.index', ['cuisine' => $col['slug']]) }}" 
                           class="flex flex-col items-center p-3 rounded-3xl bg-white border border-stone-200/80 hover:border-orange-300 hover:shadow-xl transition-all duration-300 group text-center cursor-pointer">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden mb-2.5 shadow-sm group-hover:scale-110 transition-transform duration-300 border-2 border-orange-100">
                                <img src="{{ $col['img'] }}" alt="{{ $col['name'] }}" class="w-full h-full object-cover">
                            </div>
                            <span class="text-xs font-black text-stone-900 group-hover:text-[#ea580c] truncate w-full transition-colors">{{ $col['name'] }}</span>
                            <span class="text-[10px] text-stone-600 font-semibold group-hover:text-orange-500">Explore ›</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- 3. SWIGGY-STYLE PROMOTIONAL DEALS & COUPON CARDS -->
            <section id="deals" class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                            <span>Offers &amp; Feast Deals</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-700 text-xs font-black uppercase">Save Big</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-stone-500 font-medium">Verified promo codes automatically applied at checkout.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Deal 1 -->
                    <div class="rounded-3xl bg-gradient-to-br from-[#ea580c] via-rose-600 to-amber-500 p-6 text-white shadow-xl relative overflow-hidden flex flex-col justify-between">
                        <div class="space-y-2 relative z-10">
                            <span class="px-2.5 py-0.5 rounded-full bg-white/20 text-[10px] font-black uppercase tracking-wider">🔥 FLAT 50% OFF</span>
                            <h3 class="text-2xl font-black tracking-tight leading-tight">Up to ₹150 Off First 3 Orders</h3>
                            <p class="text-xs text-orange-100">Applicable on all cloud kitchens and restaurants with min. order ₹249.</p>
                        </div>
                        <div class="pt-5 flex items-center justify-between relative z-10 border-t border-white/20 mt-4">
                            <div class="bg-black/30 backdrop-blur-md px-3.5 py-1.5 rounded-xl font-mono font-black text-sm text-amber-200">
                                K71FEAST
                            </div>
                            <button type="button" @click="copyCoupon('K71FEAST')" class="px-4 py-2 rounded-xl bg-white hover:bg-orange-50 text-[#ea580c] text-xs font-black transition active:scale-95 cursor-pointer">
                                Copy Code
                            </button>
                        </div>
                    </div>

                    <!-- Deal 2 -->
                    <div class="rounded-3xl bg-gradient-to-br from-indigo-900 via-purple-900 to-stone-900 p-6 text-white shadow-xl relative overflow-hidden flex flex-col justify-between">
                        <div class="space-y-2 relative z-10">
                            <span class="px-2.5 py-0.5 rounded-full bg-purple-500/30 text-purple-200 text-[10px] font-black uppercase tracking-wider">✨ BIG BINGE</span>
                            <h3 class="text-2xl font-black tracking-tight leading-tight">Flat ₹100 Off On Combos</h3>
                            <p class="text-xs text-stone-300">Enjoy biryani handis, family pizza meals, and party boxes on orders above ₹299.</p>
                        </div>
                        <div class="pt-5 flex items-center justify-between relative z-10 border-t border-white/20 mt-4">
                            <div class="bg-black/40 backdrop-blur-md px-3.5 py-1.5 rounded-xl font-mono font-black text-sm text-purple-200">
                                TASTY100
                            </div>
                            <button type="button" @click="copyCoupon('TASTY100')" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 text-white text-xs font-black transition active:scale-95 cursor-pointer">
                                Copy Code
                            </button>
                        </div>
                    </div>

                    <!-- Deal 3 -->
                    <div class="rounded-3xl bg-gradient-to-br from-emerald-800 via-teal-900 to-stone-900 p-6 text-white shadow-xl relative overflow-hidden flex flex-col justify-between">
                        <div class="space-y-2 relative z-10">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/30 text-emerald-200 text-[10px] font-black uppercase tracking-wider">🛵 ZERO DELIVERY FEE</span>
                            <h3 class="text-2xl font-black tracking-tight leading-tight">Free Doorstep Delivery</h3>
                            <p class="text-xs text-emerald-100">Superfast hygienic dispatch completely free on all orders over ₹199.</p>
                        </div>
                        <div class="pt-5 flex items-center justify-between relative z-10 border-t border-white/20 mt-4">
                            <div class="bg-black/40 backdrop-blur-md px-3.5 py-1.5 rounded-xl font-mono font-black text-sm text-emerald-200">
                                FREEDEL
                            </div>
                            <button type="button" @click="copyCoupon('FREEDEL')" class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-stone-950 text-xs font-black transition active:scale-95 cursor-pointer">
                                Copy Code
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. RECENT ORDERS & 1-CLICK REORDER STRIP (When user has order history) -->
            @if(isset($recentFoodOrders) && $recentFoodOrders->isNotEmpty())
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                                <span>Order Again</span>
                                <span class="text-sm">🔁</span>
                            </h2>
                            <p class="text-xs text-stone-500 font-medium">Reorder your past favorites in one quick tap.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($recentFoodOrders as $pastOrder)
                            <div class="p-4 rounded-3xl bg-white border border-stone-200/90 shadow-sm flex flex-col justify-between space-y-3">
                                <div>
                                    <div class="flex items-center justify-between text-xs text-stone-500 mb-1">
                                        <span class="font-bold text-stone-900">{{ $pastOrder->vendor?->shop_name }}</span>
                                        <span>{{ $pastOrder->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach($pastOrder->items->take(2) as $it)
                                            <div class="text-xs text-stone-700 font-semibold truncate">
                                                {{ $it->quantity }}x {{ $it->product?->title ?? $it->snapshot['title'] ?? 'Dish' }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-xs font-black text-[#ea580c] mt-2">
                                        ₹{{ number_format($pastOrder->total_amount / 100, 2) }}
                                    </div>
                                </div>
                                <form action="{{ route('food.reorder', $pastOrder->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2 rounded-xl bg-orange-50 hover:bg-[#ea580c] text-[#ea580c] hover:text-white text-xs font-black transition cursor-pointer">
                                        Reorder These Items ›
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 5. TOP CLOUD KITCHENS & RESTAURANTS SECTION -->
            <section id="restaurants-section" class="space-y-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2 border-b border-stone-200">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight flex items-center gap-2 flex-wrap">
                            <span>Top Cloud Kitchens &amp; Restaurants</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-black">
                                {{ $restaurants->total() }} Verified Places
                            </span>
                        </h2>
                        <p class="text-xs sm:text-sm text-stone-500 font-medium flex items-center gap-1.5 flex-wrap">
                            <span>Hand-picked, hygiene-certified kitchens serving</span>
                            <button type="button" @click="locationModalOpen = true" class="font-bold text-[#ea580c] hover:underline cursor-pointer inline-flex items-center gap-1">
                                <span class="underline decoration-orange-300" x-text="currentLocation">Jaipur Central</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </p>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-stone-600">Sort by:</span>
                        <select onchange="window.location.href = this.value" class="px-3 py-1.5 rounded-xl bg-white border border-stone-200 text-xs font-bold text-stone-800 focus:outline-none focus:border-[#ea580c]">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'recommended']) }}" {{ ($sort ?? 'recommended') === 'recommended' ? 'selected' : '' }}>Recommended</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'delivery_time']) }}" {{ ($sort ?? '') === 'delivery_time' ? 'selected' : '' }}>Fastest Dispatch</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'cost_low']) }}" {{ ($sort ?? '') === 'cost_low' ? 'selected' : '' }}>Cost: Low to High</option>
                        </select>
                    </div>
                </div>

                <!-- Interactive Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <!-- Pure Veg Toggle -->
                    <a href="{{ $pureVegOnly ? request()->fullUrlWithQuery(['pure_veg' => null, 'veg' => null]) : request()->fullUrlWithQuery(['pure_veg' => 1]) }}" 
                       class="px-3.5 py-2 rounded-full text-xs font-black transition-all shrink-0 flex items-center gap-2 {{ $pureVegOnly ? 'bg-emerald-700 text-white shadow-md shadow-emerald-700/25' : 'bg-white text-stone-700 border border-stone-200 hover:border-emerald-300' }}">
                        <span class="w-3.5 h-3.5 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        </span>
                        <span>Pure Veg</span>
                    </a>

                    <!-- Fast Delivery Filter -->
                    <a href="{{ ($deliveryTime ?? '') === 'under_30' ? request()->fullUrlWithQuery(['delivery_time' => null]) : request()->fullUrlWithQuery(['delivery_time' => 'under_30']) }}" 
                       class="px-3.5 py-2 rounded-full text-xs font-black transition-all shrink-0 flex items-center gap-1.5 {{ ($deliveryTime ?? '') === 'under_30' ? 'bg-[#ea580c] text-white shadow-md' : 'bg-white text-stone-700 border border-stone-200 hover:border-orange-300' }}">
                        <span>⚡</span>
                        <span>Fast Delivery (&lt;30 mins)</span>
                    </a>

                    <!-- Budget Filter -->
                    <a href="{{ ($budget ?? '') === 'under_200' ? request()->fullUrlWithQuery(['budget' => null]) : request()->fullUrlWithQuery(['budget' => 'under_200']) }}" 
                       class="px-3.5 py-2 rounded-full text-xs font-black transition-all shrink-0 flex items-center gap-1.5 {{ ($budget ?? '') === 'under_200' ? 'bg-[#ea580c] text-white shadow-md' : 'bg-white text-stone-700 border border-stone-200 hover:border-orange-300' }}">
                        <span>💰</span>
                        <span>Budget Friendly (Under ₹200)</span>
                    </a>

                    <!-- Active Filters Clearer -->
                    @if($search || $cuisine || $pureVegOnly || ($deliveryTime ?? false) || ($budget ?? false) || (($sort ?? 'recommended') !== 'recommended'))
                        <a href="{{ route('food.index') }}" class="px-3.5 py-2 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-black transition shrink-0">
                            ✕ Clear All Filters
                        </a>
                    @endif
                </div>

                <!-- Restaurants Cards Grid -->
                @if($restaurants->isEmpty())
                    <div class="bg-white rounded-3xl p-12 text-center border border-stone-200/90 max-w-lg mx-auto space-y-4 shadow-sm">
                        <span class="text-5xl block">🔍</span>
                        <h3 class="text-lg font-black text-stone-900">No restaurants match your filters</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">
                            Try resetting your filters or search keywords to discover all delicious cloud kitchens in your zone.
                        </p>
                        <a href="{{ route('food.index') }}" class="inline-block px-5 py-2.5 rounded-xl bg-[#ea580c] text-white text-xs font-black shadow-md hover:bg-[#d94e08] transition">
                            Reset Filters &amp; View All
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($restaurants as $rest)
                            @php
                                $rCover = $rest->banner_url ?: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80';
                                $rLogo = $rest->logo_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($rest->shop_name) . '&background=EA580C&color=fff&size=120');
                                $isVeg = $rest->settings?->is_pure_veg;
                                $cuisines = $rest->cuisine_list ?? ['Mughlai', 'Awadhi Biryani', 'Kebabs'];
                                $prepTime = $rest->settings?->avg_prep_time_mins ?? 25;
                                $deliveryFee = ($rest->settings?->food_delivery_fee ?? 4000) / 100;
                                $isOpen = $rest->is_open ?? true;
                            @endphp
                            <div class="bg-white rounded-3xl border border-stone-200/90 overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                                <div>
                                    <!-- Banner Image with Floating Logo Overlay -->
                                    <div class="relative aspect-[16/9] w-full overflow-hidden bg-stone-900">
                                        <img src="{{ $rCover }}" alt="{{ $rest->shop_name }}" class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-stone-950/90 via-stone-950/30 to-black/20"></div>
                                        
                                        <!-- Top Left Badges -->
                                        <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap z-10">
                                            @if($isVeg)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-700 text-white shadow-sm flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    <span>Pure Veg</span>
                                                </span>
                                            @endif
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-stone-900/85 backdrop-blur-md text-amber-400 border border-amber-400/30 shadow-sm flex items-center gap-1">
                                                <span>★</span> 4.9 <span class="text-stone-300 text-[9px] font-medium">(500+)</span>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black uppercase bg-emerald-950/80 backdrop-blur-md text-emerald-300 border border-emerald-400/30 shadow-sm flex items-center gap-1">
                                                <span>🛡️</span> FSSAI Verified
                                            </span>
                                            @if(!$isOpen)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-600 text-white shadow-sm">
                                                    Closed Now
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Bottom Floating Strip on Banner -->
                                        <div class="absolute bottom-3 left-3 right-3 text-white z-10 flex items-end justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-[11px] font-black tracking-wider uppercase text-amber-300 flex items-center gap-2 mb-0.5">
                                                    <span>⚡ {{ $prepTime }}-{{ $prepTime + 10 }} MINS</span>
                                                    <span>•</span>
                                                    <span>2.4 KM</span>
                                                </div>
                                                <h3 class="text-xl font-black truncate text-white drop-shadow-md tracking-tight">{{ $rest->shop_name }}</h3>
                                            </div>
                                            <!-- Storefront Logo Avatar -->
                                            <div class="w-12 h-12 rounded-2xl bg-white p-1 border-2 border-white shadow-lg overflow-hidden shrink-0">
                                                <img src="{{ $rLogo }}" alt="{{ $rest->shop_name }} Logo" class="w-full h-full object-cover rounded-xl">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Restaurant Info Details -->
                                    <div class="p-5 space-y-3">
                                        <!-- Cuisines Tagline -->
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-stone-700">
                                            <span class="text-[#ea580c]">🍲</span>
                                            <span class="truncate">{{ is_array($cuisines) ? implode(' • ', $cuisines) : $cuisines }}</span>
                                        </div>

                                        <p class="text-xs text-stone-500 line-clamp-2 leading-relaxed">
                                            {{ $rest->description ?: 'Authentic dum-cooked royal specialties, fragrant basmati biryani, and succulent kebabs prepared with heirloom spices.' }}
                                        </p>

                                        <!-- Highlight Pills: Delivery & Location -->
                                        <div class="flex items-center justify-between text-xs font-bold text-stone-700 pt-3 border-t border-stone-100">
                                            <span class="flex items-center gap-1 text-stone-500">
                                                <span>📍</span>
                                                <span class="truncate max-w-[140px]">{{ $rest->city ?: 'Jaipur Central Hub' }}</span>
                                            </span>
                                            <span class="text-emerald-700 font-black bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-100">
                                                ₹0 Delivery &gt; ₹199
                                            </span>
                                        </div>

                                        <!-- Special Offer Ribbon -->
                                        <div class="flex items-center gap-2 p-2 rounded-xl bg-orange-50/80 border border-orange-100 text-[#c2410c] text-[11px] font-black">
                                            <span>🏷️</span>
                                            <span class="truncate">Flat 50% OFF up to ₹150 • Use Code K71FEAST</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="p-5 pt-0">
                                    <a href="{{ route('food.restaurant', $rest->slug) }}" 
                                       class="w-full block py-3 rounded-2xl bg-gradient-to-r from-orange-50 to-amber-50 hover:from-[#ea580c] hover:to-[#dc2626] text-[#c2410c] hover:text-white text-center text-xs font-black transition-all shadow-xs cursor-pointer border border-orange-200/80 hover:border-transparent group-hover:shadow-md">
                                        View Digital Menu &amp; Order ›
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($restaurants, 'links'))
                        <div class="pt-6">
                            {{ $restaurants->links() }}
                        </div>
                    @endif
                @endif
            </section>

            <!-- 6. TRENDING DISHES (Only shown if real dishes exist in database) -->
            @if($dishes->isNotEmpty())
                <section class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-black text-stone-900 tracking-tight flex items-center gap-2">
                                <span>Trending Dishes &amp; Chef Specials</span>
                                <span class="text-2xl">🔥</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-stone-500 font-medium">Curated customer favorites freshly prepared in verified cloud kitchens.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        @foreach($dishes as $dish)
                            @php
                                $dImage = $dish->images->first()?->path ? (str_starts_with($dish->images->first()->path, 'http') ? $dish->images->first()->path : asset('storage/' . $dish->images->first()->path)) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80';
                                $isDietVeg = $dish->isVeg();
                                $price = $dish->base_price ? $dish->base_price / 100 : 249;
                            @endphp
                            <div class="bg-white rounded-3xl border border-orange-200 hover:border-[#ea580c] shadow-xs hover:shadow-xl hover:shadow-orange-500/15 transition-all duration-300 flex flex-col justify-between group relative">
                                <div>
                                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-stone-100 rounded-t-3xl">
                                        <img src="{{ $dImage }}" alt="{{ $dish->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        
                                        <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm p-1 rounded-md shadow-xs flex items-center justify-center">
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

                                        <div class="absolute top-3 right-3 z-10">
                                            <span class="px-2.5 py-0.5 rounded-md text-white text-[9.5px] font-black uppercase tracking-wider shadow-md" style="background-color: #c2410c !important; color: #ffffff !important;">
                                                FOOD
                                            </span>
                                        </div>

                                        <div class="absolute bottom-3 left-3 px-2.5 py-0.5 rounded-md bg-stone-950/80 backdrop-blur-sm text-white text-[10px] font-black flex items-center gap-1">
                                            <span class="text-amber-400">★</span> 4.9
                                            <span class="text-stone-300 font-normal">(180+)</span>
                                        </div>
                                    </div>

                                    <div class="p-4 space-y-2">
                                        <div>
                                            <h3 class="text-sm font-black text-stone-900 group-hover:text-[#ea580c] transition-colors line-clamp-1">
                                                {{ $dish->title }}
                                            </h3>
                                            <a href="{{ route('food.restaurant', $dish->vendor?->slug ?? '') }}" class="text-[11px] text-stone-500 font-bold hover:text-[#ea580c] transition-colors truncate block">
                                                {{ $dish->vendor?->shop_name ?: 'Specialty Kitchen' }}
                                            </a>
                                        </div>
                                        
                                        <p class="text-xs text-stone-600 line-clamp-2 leading-relaxed">
                                            {{ $dish->description ?: 'Freshly prepared specialty meal with premium farm ingredients.' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-4 pt-0 flex items-center justify-between border-t border-stone-100 mt-2">
                                    <div>
                                        <span class="text-base font-black text-stone-900">₹{{ number_format($price, 0) }}</span>
                                    </div>

                                    <button type="button" 
                                            @click="quickAddToCart($event, {{ $dish->id }}, '{{ addslashes($dish->title) }}', {{ $dish->vendor_id }}, {{ $dish->variants->first()?->id ?? 'null' }})"
                                            class="px-4 py-2 rounded-xl bg-[#ea580c] hover:bg-[#c2410c] text-white shadow-md shadow-orange-500/25 text-xs font-black transition-all cursor-pointer active:scale-95 flex items-center gap-1.5">
                                        <span>ADD</span>
                                        <span class="text-sm font-bold leading-none">+</span>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 7. THE K71 FOOD PROMISE & TRUST BADGES -->
            <section class="space-y-4">
                <div class="text-center max-w-xl mx-auto space-y-1">
                    <span class="text-[11px] font-black uppercase tracking-widest text-[#c2410c]">WHY DINERS LOVE US</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-stone-950 tracking-tight">The K71 Food Standard</h2>
                    <p class="text-xs text-stone-500">Uncompromising quality, blistering dispatch, and certified hygiene on every meal.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                    <div class="p-5 rounded-3xl bg-white border border-stone-200/90 shadow-sm space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-orange-100 text-[#ea580c] flex items-center justify-center text-2xl mx-auto">🚀</div>
                        <h3 class="text-sm font-black text-stone-900">25-Min Express Dispatch</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">Dedicated thermal insulated food pods ensure your meal arrives piping hot and steam fresh.</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200/90 shadow-sm space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mx-auto">🛡️</div>
                        <h3 class="text-sm font-black text-stone-900">100% FSSAI Verified</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">Routine audits, live staff temperature screenings, and strict sanitization in partner cloud kitchens.</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200/90 shadow-sm space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl mx-auto">🌿</div>
                        <h3 class="text-sm font-black text-stone-900">Dedicated Pure Veg Care</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">Certified separate kitchens and cookware for pure vegetarian dining with zero cross-contamination.</p>
                    </div>

                    <div class="p-5 rounded-3xl bg-white border border-stone-200/90 shadow-sm space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto">💳</div>
                        <h3 class="text-sm font-black text-stone-900">Instant UPI Refunds</h3>
                        <p class="text-xs text-stone-500 leading-relaxed">No-questions-asked instant resolution if food temperature or packaging is ever unsatisfactory.</p>
                    </div>
                </div>
            </section>

            <!-- 8. RESTAURANT PARTNER ONBOARDING BANNER -->
            <section class="rounded-3xl bg-gradient-to-r from-stone-900 via-stone-950 to-stone-900 p-8 sm:p-12 text-white border border-stone-800 shadow-2xl relative overflow-hidden">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-xl space-y-3">
                        <span class="px-3 py-1 rounded-full bg-orange-500/20 text-[#ea580c] text-xs font-black uppercase tracking-wider">
                            👨‍🍳 FOR CLOUD KITCHENS &amp; RESTAURANTS
                        </span>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight leading-tight">
                            Partner with K71 Food &amp; Grow Your Culinary Reach
                        </h2>
                        <p class="text-xs sm:text-sm text-stone-400 leading-relaxed">
                            Open your digital cloud storefront in minutes. Direct payouts, transparent commissions, live kitchen dispatch system, and access to thousands of hungry foodies.
                        </p>
                    </div>
                    <div class="shrink-0 flex flex-col sm:flex-row items-center gap-3">
                        <a href="{{ route('vendor.register') }}" class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#ea580c] to-[#dc2626] hover:brightness-105 text-white text-xs sm:text-sm font-bold shadow-md transition active:scale-95 text-center max-h-11 flex items-center justify-center">
                            Register Cloud Kitchen →
                        </a>
                        <a href="{{ route('vendor.login') }}" class="w-full sm:w-auto px-4 sm:px-5 py-2.5 rounded-xl bg-stone-800 hover:bg-stone-700 text-stone-200 text-xs sm:text-sm font-semibold transition text-center border border-stone-700 max-h-11 flex items-center justify-center">
                            Partner Login
                        </a>
                    </div>
                </div>
            </section>

        </div>

    </div>

    @push('scripts')
    <script>
    function k71FoodHomepage() {
        return {
            currentLocation: localStorage.getItem('k71_food_location') || 'Jaipur Central (Express Hub)',
            init() {
                window.addEventListener('food-location-changed', (e) => {
                    if (e.detail) this.currentLocation = e.detail;
                });
            },
            copyCoupon(code) {
                navigator.clipboard.writeText(code).then(() => {
                    if (window.showToast) {
                        window.showToast('Coupon code ' + code + ' copied to clipboard! 🏷️');
                    } else {
                        alert('Coupon ' + code + ' copied! Use at checkout for discount.');
                    }
                });
            },
            quickAddToCart(e, productId, title, vendorId, variantId) {
                const btn = e?.currentTarget;
                const originalHtml = btn ? btn.innerHTML : '<span>ADD</span><span class="text-sm font-bold leading-none">+</span>';
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="inline-block animate-spin mr-1">⏳</span><span>ADDING...</span>';
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({
                        product_id: productId,
                        variant_id: variantId,
                        quantity: 1
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success || data.cart_count) {
                        window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                        window.dispatchEvent(new CustomEvent('cart-count-updated', { detail: { count: data.cart_count } }));
                        window.dispatchEvent(new CustomEvent('open-mini-cart'));
                        if (window.showToast) {
                            window.showToast(title + ' added to your bag! 🛍️');
                        }
                        if (btn) {
                            btn.classList.remove('bg-[#ea580c]', 'hover:bg-[#c2410c]');
                            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                            btn.innerHTML = '<span>✓ ADDED</span>';
                            setTimeout(() => {
                                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                                btn.classList.add('bg-[#ea580c]', 'hover:bg-[#c2410c]');
                                btn.disabled = false;
                                btn.innerHTML = originalHtml;
                            }, 1800);
                        }
                    } else {
                        if (window.showToast) {
                            window.showToast(data.message || 'Could not add item to bag.', false);
                        } else {
                            alert(data.message || 'Could not add item to bag.');
                        }
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (window.showToast) {
                        window.showToast('Failed to add to bag. Please try again.', false);
                    }
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                });
            }
        };
    }
    </script>
    @endpush
</x-layouts.food>
