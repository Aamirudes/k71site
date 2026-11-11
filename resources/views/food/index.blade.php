<x-layouts.food 
    title="K71 Food — Order Online from Top Fresh Cloud Kitchens & Restaurants"
    metaDescription="Order fresh handi biryani, wood-fired artisan pizza, smash burgers, pure veg royal thalis, and desserts on K71 Food. 25-minute superfast doorstep delivery, FSSAI hygiene verified, and zero delivery fee offers."
>
    @php
        $dishes = $trendingDishes ?? collect();
        $seededRestaurant = $restaurants->first();
        $restaurantMenuSlug = $seededRestaurant?->slug ?? 'dawat-e-khaas-royal-biryani-kebabs';
    @endphp

    <div x-data="k71FoodHomepage()" class="bg-black text-[#EFE9F2]">

        <!-- 1. GRAIL OBSIDIAN FOOD HERO & LIVE SEARCH SECTION -->
        <section class="grail-hero-band pt-8 sm:pt-12 pb-10 sm:pb-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                @php
                    $fs = $foodSettings ?? [];
                @endphp
                <div class="max-w-3xl space-y-3" data-grail-reveal>
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-[#ECD2F3] text-[11px] font-bold uppercase tracking-[0.12em] flex-wrap backdrop-blur-md">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $fs['food_hero_badge'] ?? '⚡ 25-30 MINUTE EXPRESS DOORSTEP DISPATCH' }}</span>
                        </span>
                        <span class="text-white/25">•</span>
                        <button type="button" @click="locationModalOpen = true" class="hover:text-white flex items-center gap-1 cursor-pointer transition">
                            <span class="text-[#C9A24A]">📍 DELIVERING TO</span>
                            <span class="font-bold underline decoration-[#ECD2F3]/50" x-text="currentLocation">Jaipur Central</span>
                            <span>▾</span>
                        </button>
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-light text-white tracking-tight leading-[0.98]" style="font-family: var(--font-grail-display);">
                        Crave it? Get it hot &amp; fresh from <span class="text-[#ECD2F3] italic">your favourite kitchens</span>.
                    </h1>
                    <p class="text-sm sm:text-base text-[#EFE9F2]/75 font-normal leading-relaxed">
                        {{ $fs['food_hero_subtitle'] ?? 'Explore hand-crafted dum biryanis, wood-fired artisan pizzas, sizzling tandoori kebabs, and pure veg royal thalis.' }}
                    </p>
                </div>

                <!-- Live Auto-Suggest Search Bar with Glowing Focus -->
                <div class="max-w-3xl relative" x-data="{ query: '{{ $search ?? '' }}', results: null, loading: false }" data-grail-reveal>
                    <form action="{{ route('food.index') }}" method="GET" class="relative">
                        <div class="relative flex items-center bg-[#141414] rounded-full border border-white/20 shadow-[0_18px_50px_rgba(0,0,0,0.65)] focus-within:border-[#ECD2F3] focus-within:ring-4 focus-within:ring-[#ECD2F3]/20 transition-all overflow-hidden">
                            <div class="pl-5 text-[#ECD2F3] flex items-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" x-model="query"
                                   @input.debounce.300ms="if(query.length >= 2){ loading = true; fetch('/food/suggest?q=' + encodeURIComponent(query)).then(r=>r.json()).then(d=>{ results = d; loading = false; }); } else { results = null; }"
                                   placeholder="Search restaurant, dish, or cuisine (e.g. Biryani, Pizza, Dal Makhani)..." 
                                   class="w-full bg-transparent py-4 pl-3.5 pr-28 text-sm sm:text-base font-medium text-white placeholder:text-[#EFE9F2]/40 focus:outline-none">
                            
                            <div class="absolute right-2 flex items-center gap-1.5">
                                @if($pureVegOnly)
                                    <input type="hidden" name="pure_veg" value="1">
                                @endif
                                <button type="submit" class="px-5 py-2.5 grail-cta-pill text-xs sm:text-sm cursor-pointer flex items-center gap-1.5">
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
                         class="absolute top-full left-0 right-0 mt-2 bg-[#FAFAF8] text-[#1A1018] rounded-[24px] shadow-2xl border border-black/10 p-4 z-50 space-y-4 max-h-96 overflow-y-auto no-scrollbar">
                        
                        <!-- Matching Restaurants -->
                        <template x-if="results?.restaurants?.length > 0">
                            <div>
                                <span class="grail-eyebrow !text-[#623562] block mb-2">Restaurants &amp; Kitchens</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="r in results.restaurants" :key="r.name">
                                        <a :href="r.url" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[#ECD2F3]/35 transition border border-transparent hover:border-[#623562]/20">
                                            <div class="w-10 h-10 rounded-lg bg-[#F6F6F6] overflow-hidden shrink-0 border border-black/8">
                                                <img :src="r.image || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=150&q=80'" :alt="r.name" class="w-full h-full object-cover">
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-[#1A1018] truncate" x-text="r.name"></div>
                                                <div class="text-[10px] text-[#1A1018]/60" x-text="'📍 ' + (r.city || 'Express Delivery')"></div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Matching Dishes -->
                        <template x-if="results?.dishes?.length > 0">
                            <div>
                                <span class="grail-eyebrow !text-[#623562] block mb-2">Popular Dishes</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="d in results.dishes" :key="d.title">
                                        <a :href="d.restaurant_url" class="flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-[#ECD2F3]/35 transition border border-transparent hover:border-[#623562]/20">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-3.5 h-3.5 rounded-xs border-2 flex items-center justify-center p-0.5 shrink-0"
                                                      :class="d.dietary === 'Veg' ? 'border-emerald-600' : 'border-rose-600'">
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="d.dietary === 'Veg' ? 'bg-emerald-600' : 'bg-rose-600'"></span>
                                                </span>
                                                <div class="min-w-0">
                                                    <div class="text-xs font-bold text-[#1A1018] truncate" x-text="d.title"></div>
                                                    <div class="text-[10px] text-[#1A1018]/55 truncate" x-text="d.restaurant"></div>
                                                </div>
                                            </div>
                                            <span class="text-xs font-black text-[#1A1018] shrink-0" x-text="'₹' + d.price"></span>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Trending Quick Chips with Food Emojis -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-1" data-grail-reveal>
                    <span class="grail-eyebrow shrink-0 mr-1">Popular:</span>
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
                           class="px-3.5 py-1.5 rounded-full bg-white/8 hover:bg-[#ECD2F3] border border-white/15 hover:border-[#ECD2F3] text-xs font-semibold text-[#EFE9F2] hover:text-[#1A1018] transition-all shrink-0 flex items-center gap-1.5">
                            <span>{{ $chip['emoji'] }}</span>
                            <span>{{ $chip['label'] }}</span>
                        </a>
                    @endforeach
                </div>

                <!-- Live Foodie Assurance Banner -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-2" data-grail-reveal>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-[#ECD2F3]/15 text-[#ECD2F3] border border-[#ECD2F3]/25 flex items-center justify-center text-sm font-bold shrink-0">⚡</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">25 Min Delivery</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">Hot insulated bags</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-400/25 flex items-center justify-center text-sm font-bold shrink-0">🛡️</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">FSSAI Certified</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">100% hygiene tested</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-[#C9A24A]/15 text-[#C9A24A] border border-[#C9A24A]/25 flex items-center justify-center text-sm font-bold shrink-0">🔥</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">Freshly Prepared</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">Cooked on order</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-white/10 text-[#FFCDFF] border border-white/15 flex items-center justify-center text-sm font-bold shrink-0">🛵</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">Live Tracking</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">Real-time GPS dispatch</div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- CURVED LIGHT EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-0 shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
        <div class="max-w-7xl mx-auto space-y-12 sm:space-y-16 pb-10">

            <!-- 2. "WHAT'S ON YOUR MIND?" VISUAL CUISINE DISCOVERY RAIL -->
            <section class="space-y-5" data-grail-reveal>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Curated Cravings</span>
                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2" style="font-family: var(--font-grail-display);">
                            <span>What's on your mind?</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium mt-0.5">Explore curated collections of top Indian and international comfort food.</p>
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
                           class="flex flex-col items-center p-3 grail-card hover:-translate-y-1 transition-all duration-300 group text-center cursor-pointer">
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-full overflow-hidden mb-2.5 shadow-sm group-hover:scale-105 transition-transform duration-300 border-2 border-[#ECD2F3]">
                                <img src="{{ $col['img'] }}" alt="{{ $col['name'] }}" class="w-full h-full object-cover">
                            </div>
                            <span class="text-xs font-bold text-[#1A1018] group-hover:text-[#623562] truncate w-full transition-colors">{{ $col['name'] }}</span>
                            <span class="text-[10px] text-[#623562]/80 font-semibold">Explore ›</span>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- 3. PROMOTIONAL DEALS & COUPON CARDS -->
            <section id="deals" class="space-y-5" data-grail-reveal>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Member Privileges</span>
                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap" style="font-family: var(--font-grail-display);">
                            <span>Offers &amp; Feast Deals</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-sans font-bold uppercase tracking-wider">Save Big</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium">Verified promo codes automatically applied at checkout.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Deal 1 -->
                    <div class="rounded-[24px] bg-gradient-to-br from-[#1A1018] via-[#2B1528] to-[#0D0D0D] p-6 text-white shadow-xl border border-[#ECD2F3]/25 relative overflow-hidden flex flex-col justify-between">
                        <div class="space-y-2 relative z-10">
                            <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-black uppercase tracking-wider">🔥 FLAT 50% OFF</span>
                            <h3 class="text-2xl font-light tracking-tight leading-tight text-white" style="font-family: var(--font-grail-display);">Up to ₹150 Off First 3 Orders</h3>
                            <p class="text-xs text-[#EFE9F2]/70">Applicable on all cloud kitchens and restaurants with min. order ₹249.</p>
                        </div>
                        <div class="pt-5 flex items-center justify-between relative z-10 border-t border-white/15 mt-4">
                            <div class="bg-black/40 px-3.5 py-1.5 rounded-full font-mono font-bold text-sm text-[#ECD2F3] border border-[#ECD2F3]/30">
                                K71FEAST
                            </div>
                            <button type="button" @click="copyCoupon('K71FEAST')" class="px-4 py-2 grail-cta-pill text-xs cursor-pointer">
                                Copy Code
                            </button>
                        </div>
                    </div>

                    <!-- Deal 2 -->
                    <div class="rounded-[24px] bg-gradient-to-br from-[#0F0F12] via-[#1C1224] to-[#060606] p-6 text-white shadow-xl border border-[#C9A24A]/30 relative overflow-hidden flex flex-col justify-between">
                        <div class="space-y-2 relative z-10">
                            <span class="px-2.5 py-0.5 rounded-full bg-[#C9A24A]/20 text-[#C9A24A] border border-[#C9A24A]/40 text-[10px] font-black uppercase tracking-wider">✨ BIG BINGE</span>
                            <h3 class="text-2xl font-light tracking-tight leading-tight text-white" style="font-family: var(--font-grail-display);">Flat ₹100 Off On Combos</h3>
                            <p class="text-xs text-[#EFE9F2]/70">Enjoy biryani handis, family pizza meals, and party boxes on orders above ₹299.</p>
                        </div>
                        <div class="pt-5 flex items-center justify-between relative z-10 border-t border-white/15 mt-4">
                            <div class="bg-black/40 px-3.5 py-1.5 rounded-full font-mono font-bold text-sm text-[#C9A24A] border border-[#C9A24A]/30">
                                TASTY100
                            </div>
                            <button type="button" @click="copyCoupon('TASTY100')" class="px-4 py-2 grail-cta-pill text-xs cursor-pointer">
                                Copy Code
                            </button>
                        </div>
                    </div>

                    <!-- Deal 3 -->
                    <div class="rounded-[24px] bg-gradient-to-br from-[#0B1712] via-[#111916] to-[#060606] p-6 text-white shadow-xl border border-emerald-400/25 relative overflow-hidden flex flex-col justify-between">
                        <div class="space-y-2 relative z-10">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-[10px] font-black uppercase tracking-wider">🛵 ZERO DELIVERY FEE</span>
                            <h3 class="text-2xl font-light tracking-tight leading-tight text-white" style="font-family: var(--font-grail-display);">Free Doorstep Delivery</h3>
                            <p class="text-xs text-[#EFE9F2]/70">Superfast hygienic dispatch completely free on all orders over ₹199.</p>
                        </div>
                        <div class="pt-5 flex items-center justify-between relative z-10 border-t border-white/15 mt-4">
                            <div class="bg-black/40 px-3.5 py-1.5 rounded-full font-mono font-bold text-sm text-emerald-300 border border-emerald-400/30">
                                FREEDEL
                            </div>
                            <button type="button" @click="copyCoupon('FREEDEL')" class="px-4 py-2 rounded-full bg-emerald-500 hover:bg-emerald-400 text-[#060606] text-xs font-bold transition active:scale-95 cursor-pointer">
                                Copy Code
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. RECENT ORDERS & 1-CLICK REORDER STRIP (When user has order history) -->
            @if(isset($recentFoodOrders) && $recentFoodOrders->isNotEmpty())
                <section class="space-y-4" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">1-Tap Repeat</span>
                            <h2 class="text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                Order Again
                            </h2>
                            <p class="text-xs text-[#1A1018]/60 font-medium">Reorder your past favorites in one quick tap.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($recentFoodOrders as $pastOrder)
                            <div class="p-4 grail-card flex flex-col justify-between space-y-3">
                                <div>
                                    <div class="flex items-center justify-between text-xs text-[#1A1018]/55 mb-1">
                                        <span class="font-bold text-[#1A1018]">{{ $pastOrder->vendor?->shop_name }}</span>
                                        <span>{{ $pastOrder->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach($pastOrder->items->take(2) as $it)
                                            <div class="text-xs text-[#1A1018]/80 font-semibold truncate">
                                                {{ $it->quantity }}x {{ $it->product?->title ?? $it->snapshot['title'] ?? 'Dish' }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-xs font-black text-[#623562] mt-2">
                                        ₹{{ number_format($pastOrder->total_amount / 100, 2) }}
                                    </div>
                                </div>
                                <form action="{{ route('food.reorder', $pastOrder->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">
                                        Reorder These Items ›
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 5. TOP CLOUD KITCHENS & RESTAURANTS SECTION -->
            <section id="restaurants-section" class="space-y-6" data-grail-reveal>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-black/10">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Partner Kitchens</span>
                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap" style="font-family: var(--font-grail-display);">
                            <span>Top Cloud Kitchens &amp; Restaurants</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-sans font-bold">
                                {{ $restaurants->total() }} Verified Places
                            </span>
                        </h2>
                        <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium flex items-center gap-1.5 flex-wrap mt-1">
                            <span>Hand-picked, hygiene-certified kitchens serving</span>
                            <button type="button" @click="locationModalOpen = true" class="font-bold text-[#623562] hover:underline cursor-pointer inline-flex items-center gap-1">
                                <span class="underline decoration-[#623562]/40" x-text="currentLocation">Jaipur Central</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                        </p>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#1A1018]/60">Sort by:</span>
                        <select onchange="window.location.href = this.value" class="px-3.5 py-2 grail-input-light text-xs font-bold text-[#1A1018]">
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
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-2 {{ $pureVegOnly ? 'bg-emerald-700 text-white shadow-md shadow-emerald-700/25' : 'bg-white text-[#1A1018] border border-black/10 hover:border-emerald-400' }}">
                        <span class="w-3.5 h-3.5 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        </span>
                        <span>Pure Veg</span>
                    </a>

                    <!-- Fast Delivery Filter -->
                    <a href="{{ ($deliveryTime ?? '') === 'under_30' ? request()->fullUrlWithQuery(['delivery_time' => null]) : request()->fullUrlWithQuery(['delivery_time' => 'under_30']) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($deliveryTime ?? '') === 'under_30' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-md' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>⚡</span>
                        <span>Fast Delivery (&lt;30 mins)</span>
                    </a>

                    <!-- Budget Filter -->
                    <a href="{{ ($budget ?? '') === 'under_200' ? request()->fullUrlWithQuery(['budget' => null]) : request()->fullUrlWithQuery(['budget' => 'under_200']) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($budget ?? '') === 'under_200' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-md' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>💰</span>
                        <span>Budget Friendly (Under ₹200)</span>
                    </a>

                    <!-- Active Filters Clearer -->
                    @if($search || $cuisine || $pureVegOnly || ($deliveryTime ?? false) || ($budget ?? false) || (($sort ?? 'recommended') !== 'recommended'))
                        <a href="{{ route('food.index') }}" class="px-4 py-2 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition shrink-0">
                            ✕ Clear All Filters
                        </a>
                    @endif
                </div>

                <!-- Restaurants Cards Grid -->
                @if($restaurants->isEmpty())
                    <div class="grail-card p-12 text-center max-w-lg mx-auto space-y-4">
                        <span class="text-5xl block">🔍</span>
                        <h3 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">No restaurants match your filters</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">
                            Try resetting your filters or search keywords to discover all delicious cloud kitchens in your zone.
                        </p>
                        <a href="{{ route('food.index') }}" class="inline-block px-6 py-2.5 grail-btn-obsidian text-xs font-bold">
                            Reset Filters &amp; View All
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($restaurants as $rest)
                            @php
                                $rCover = $rest->banner_url ?: 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1200&q=80';
                                $rLogo = $rest->logo_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($rest->shop_name) . '&background=1A1018&color=ECD2F3&size=120');
                                $isVeg = $rest->settings?->is_pure_veg;
                                $cuisines = $rest->cuisine_list ?? ['Mughlai', 'Awadhi Biryani', 'Kebabs'];
                                $prepTime = $rest->settings?->avg_prep_time_mins ?? 25;
                                $deliveryFee = ($rest->settings?->food_delivery_fee ?? 4000) / 100;
                                $isOpen = $rest->is_open ?? true;
                            @endphp
                            <div class="grail-card overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1">
                                <div>
                                    <!-- Banner Image with Floating Logo Overlay -->
                                    <div class="relative aspect-[16/9] w-full overflow-hidden bg-[#0D0D0D]">
                                        <img src="{{ $rCover }}" alt="{{ $rest->shop_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-black/20"></div>
                                        
                                        <!-- Top Left Badges -->
                                        <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap z-10">
                                            @if($isVeg)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-700 text-white shadow-sm flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    <span>Pure Veg</span>
                                                </span>
                                            @endif
                                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-black/80 backdrop-blur-md text-[#C9A24A] border border-[#C9A24A]/35 shadow-sm flex items-center gap-1">
                                                <span>★</span> 4.9 <span class="text-[#EFE9F2]/60 text-[9px] font-medium">(500+)</span>
                                            </span>
                                            <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black uppercase bg-emerald-950/85 backdrop-blur-md text-emerald-300 border border-emerald-400/30 shadow-sm flex items-center gap-1">
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
                                                <div class="text-[11px] font-black tracking-wider uppercase text-[#ECD2F3] flex items-center gap-2 mb-0.5">
                                                    <span>⚡ {{ $prepTime }}-{{ $prepTime + 10 }} MINS</span>
                                                    <span>•</span>
                                                    <span>2.4 KM</span>
                                                </div>
                                                <h3 class="text-xl font-black truncate text-white drop-shadow-md tracking-tight">{{ $rest->shop_name }}</h3>
                                            </div>
                                            <!-- Storefront Logo Avatar -->
                                            <div class="w-12 h-12 rounded-2xl bg-white p-1 border-2 border-[#ECD2F3]/50 shadow-lg overflow-hidden shrink-0">
                                                <img src="{{ $rLogo }}" alt="{{ $rest->shop_name }} Logo" class="w-full h-full object-cover rounded-xl">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Restaurant Info Details -->
                                    <div class="p-5 space-y-3">
                                        <!-- Cuisines Tagline -->
                                        <div class="flex items-center gap-1.5 text-xs font-bold text-[#1A1018]">
                                            <span class="text-[#C9A24A]">🍲</span>
                                            <span class="truncate">{{ is_array($cuisines) ? implode(' • ', $cuisines) : $cuisines }}</span>
                                        </div>

                                        <p class="text-xs text-[#1A1018]/65 line-clamp-2 leading-relaxed">
                                            {{ $rest->description ?: 'Authentic dum-cooked royal specialties, fragrant basmati biryani, and succulent kebabs prepared with heirloom spices.' }}
                                        </p>

                                        <!-- Highlight Pills: Delivery & Location -->
                                        <div class="flex items-center justify-between text-xs font-bold text-[#1A1018] pt-3 border-t border-black/8">
                                            <span class="flex items-center gap-1 text-[#1A1018]/60">
                                                <span>📍</span>
                                                <span class="truncate max-w-[140px]">{{ $rest->city ?: 'Jaipur Central Hub' }}</span>
                                            </span>
                                            <span class="text-emerald-700 font-black bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80">
                                                ₹0 Delivery &gt; ₹199
                                            </span>
                                        </div>

                                        <!-- Special Offer Ribbon -->
                                        <div class="flex items-center gap-2 p-2.5 rounded-xl bg-[#ECD2F3]/35 border border-[#623562]/15 text-[#623562] text-[11px] font-bold">
                                            <span>🏷️</span>
                                            <span class="truncate">Flat 50% OFF up to ₹150 • Use Code K71FEAST</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="p-5 pt-0">
                                    <a href="{{ route('food.restaurant', $rest->slug) }}" 
                                       class="w-full block py-3 grail-btn-obsidian text-center text-xs font-bold shadow-xs cursor-pointer">
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
                <section class="space-y-6" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">Chef Favorites</span>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                Trending Dishes &amp; Chef Specials
                            </h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium">Curated customer favorites freshly prepared in verified cloud kitchens.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        @foreach($dishes as $dish)
                            @php
                                $dImage = $dish->images->first()?->path ? (str_starts_with($dish->images->first()->path, 'http') ? $dish->images->first()->path : asset('storage/' . $dish->images->first()->path)) : 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=600&q=80';
                                $isDietVeg = $dish->isVeg();
                                $price = $dish->base_price ? $dish->base_price / 100 : 249;
                            @endphp
                            <div class="grail-card hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                                <div>
                                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#FAFAF8]">
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
                                            <span class="px-2.5 py-0.5 rounded-full text-[9.5px] font-black uppercase tracking-wider shadow-md bg-[#1A1018] text-[#ECD2F3]">
                                                FOOD
                                            </span>
                                        </div>

                                        <div class="absolute bottom-3 left-3 px-2.5 py-0.5 rounded-full bg-black/80 backdrop-blur-sm text-white text-[10px] font-bold flex items-center gap-1">
                                            <span class="text-[#C9A24A]">★</span> 4.9
                                            <span class="text-white/60 font-normal">(180+)</span>
                                        </div>
                                    </div>

                                    <div class="p-4 space-y-2">
                                        <div>
                                            <h3 class="text-sm font-bold text-[#1A1018] group-hover:text-[#623562] transition-colors line-clamp-1">
                                                {{ $dish->title }}
                                            </h3>
                                            <a href="{{ route('food.restaurant', $dish->vendor?->slug ?? '') }}" class="text-[11px] text-[#623562] font-semibold hover:underline truncate block">
                                                {{ $dish->vendor?->shop_name ?: 'Specialty Kitchen' }}
                                            </a>
                                        </div>
                                        
                                        <p class="text-xs text-[#1A1018]/65 line-clamp-2 leading-relaxed">
                                            {{ $dish->description ?: 'Freshly prepared specialty meal with premium farm ingredients.' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="p-4 pt-3 flex items-center justify-between border-t border-black/8">
                                    <div>
                                        <span class="text-base font-black text-[#1A1018]">₹{{ number_format($price, 0) }}</span>
                                    </div>

                                    <button type="button" 
                                            @click="quickAddToCart($event, {{ $dish->id }}, '{{ addslashes($dish->title) }}', {{ $dish->vendor_id }}, {{ $dish->variants->first()?->id ?? 'null' }})"
                                            class="px-4 py-2 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] shadow-sm text-xs font-bold transition-all cursor-pointer active:scale-95 flex items-center gap-1.5">
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
            <section class="space-y-5" data-grail-reveal>
                <div class="text-center max-w-xl mx-auto space-y-1.5">
                    <span class="grail-eyebrow !text-[#623562]">WHY DINERS LOVE US</span>
                    <h2 class="text-3xl sm:text-4xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">The K71 Food Standard</h2>
                    <p class="text-xs text-[#1A1018]/60">Uncompromising quality, blistering dispatch, and certified hygiene on every meal.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-[#ECD2F3]/50 text-[#1A1018] flex items-center justify-center text-2xl mx-auto">🚀</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">25-Min Express Dispatch</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">Dedicated thermal insulated food pods ensure your meal arrives piping hot and steam fresh.</p>
                    </div>

                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mx-auto">🛡️</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">100% FSSAI Verified</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">Routine audits, live staff temperature screenings, and strict sanitization in partner cloud kitchens.</p>
                    </div>

                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl mx-auto">🌿</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">Dedicated Pure Veg Care</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">Certified separate kitchens and cookware for pure vegetarian dining with zero cross-contamination.</p>
                    </div>

                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-[#FAF5EB] border border-[#C9A24A]/30 text-[#5F5138] flex items-center justify-center text-2xl mx-auto">💳</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">Instant UPI Refunds</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">No-questions-asked instant resolution if food temperature or packaging is ever unsatisfactory.</p>
                    </div>
                </div>
            </section>

            <!-- 8. RESTAURANT PARTNER ONBOARDING BANNER -->
            <section class="rounded-[28px] bg-gradient-to-br from-[#060606] via-[#120D14] to-[#000000] p-8 sm:p-12 text-white border border-white/10 shadow-2xl relative overflow-hidden" data-grail-reveal>
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-xl space-y-3">
                        <span class="inline-block px-3.5 py-1 rounded-full bg-[#ECD2F3]/15 text-[#ECD2F3] border border-[#ECD2F3]/30 text-[11px] font-bold uppercase tracking-[0.12em]">
                            👨‍🍳 FOR CLOUD KITCHENS &amp; RESTAURANTS
                        </span>
                        <h2 class="text-2xl sm:text-3xl font-light tracking-tight leading-tight text-white" style="font-family: var(--font-grail-display);">
                            Partner with K71 Food &amp; Grow Your Culinary Reach
                        </h2>
                        <p class="text-xs sm:text-sm text-[#EFE9F2]/70 leading-relaxed">
                            Open your digital cloud storefront in minutes. Direct payouts, transparent commissions, live kitchen dispatch system, and access to thousands of hungry foodies.
                        </p>
                    </div>
                    <div class="shrink-0 flex flex-col sm:flex-row items-center gap-3">
                        <a href="{{ route('vendor.register') }}" class="w-full sm:w-auto px-5 py-3 grail-cta-pill text-xs sm:text-sm text-center flex items-center justify-center">
                            Register Cloud Kitchen →
                        </a>
                        <a href="{{ route('vendor.login') }}" class="w-full sm:w-auto px-5 py-3 grail-btn-ghost-dark text-xs sm:text-sm text-center flex items-center justify-center">
                            Partner Login
                        </a>
                    </div>
                </div>
            </section>

        </div>
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
                            btn.classList.remove('bg-[#1A1018]', 'hover:bg-[#623562]', 'text-[#ECD2F3]');
                            btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700', 'text-white');
                            btn.innerHTML = '<span>✓ ADDED</span>';
                            setTimeout(() => {
                                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700', 'text-white');
                                btn.classList.add('bg-[#1A1018]', 'hover:bg-[#623562]', 'text-[#ECD2F3]');
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
