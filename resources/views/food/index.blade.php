<x-layouts.food 
    title="K71 Food — Order Online from Local Kitchens & Restaurants"
    metaDescription="Discover local restaurants and cloud kitchens on K71 Food. Filter by delivery zone, pure veg, cuisines, live kitchen status, delivery or self-pickup."
>
    @php
        $dishes = $trendingDishes ?? collect();
        $fs = $foodSettings ?? [];
        $activeLocLabel = $selectedLocation['label'] ?? null;
        $hasLocationFilter = !empty($selectedLocation['city']) || !empty($selectedLocation['pincode']);
        $dealsCollection = $activeDeals ?? collect();

        // Build honest quick cuisine & filter chips from actual categories & partner kitchens
        $cuisineItems = collect();
        foreach (($foodCategories ?? collect()) as $fc) {
            $cuisineItems->push([
                'name' => $fc->name,
                'slug' => $fc->name,
                'count' => (int) ($fc->products_count ?? 0),
                'image' => !empty($fc->image) ? format_image_url($fc->image) : null,
                'type' => 'category',
            ]);
        }
        foreach ($restaurants as $restItem) {
            foreach ((array) ($restItem->cuisine_list ?? []) as $cName) {
                $trimmed = trim((string) $cName);
                if ($trimmed !== '' && strtolower($trimmed) !== 'multi-cuisine' && !$cuisineItems->contains(fn ($it) => strtolower($it['name']) === strtolower($trimmed))) {
                    $cuisineItems->push([
                        'name' => $trimmed,
                        'slug' => $trimmed,
                        'count' => null,
                        'image' => null,
                        'type' => 'cuisine',
                    ]);
                }
            }
        }
        $cuisineItems = $cuisineItems->take(10);

        $hasActiveFilters = (bool) ($search || $cuisine || $pureVegOnly || ($openNowOnly ?? false) || ($takeawayOnly ?? false) || ($rating ?? false) || ($deliveryTime ?? false) || ($budget ?? false) || (($sort ?? 'recommended') !== 'recommended'));
    @endphp

    <div x-data="k71FoodHomepage()" class="bg-black text-[#EFE9F2]">

        <!-- 1. GRAIL OBSIDIAN FOOD HERO & LIVE SEARCH SECTION -->
        <section class="grail-hero-band pt-8 sm:pt-12 pb-10 sm:pb-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <div class="max-w-3xl space-y-3" data-grail-reveal>
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-white/10 border border-white/15 text-[#ECD2F3] text-[11px] font-bold uppercase tracking-[0.12em] flex-wrap backdrop-blur-md">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>{{ $fs['food_hero_badge'] ?? 'LOCAL RESTAURANTS • DELIVERY & SELF-PICKUP' }}</span>
                        </span>
                        <span class="text-white/25">•</span>
                        <button type="button" @click="locationModalOpen = true" class="hover:text-white flex items-center gap-1 cursor-pointer transition">
                            <span class="text-[#C9A24A]">📍 {{ $hasLocationFilter ? 'DELIVERING TO' : 'AREA' }}</span>
                            <span class="font-bold underline decoration-[#ECD2F3]/50" x-text="currentLocation">{{ $activeLocLabel ?: 'All Locations' }}</span>
                            <span>▾</span>
                        </button>
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-light text-white tracking-tight leading-[0.98]" style="font-family: var(--font-grail-display);">
                        Fresh meals from <span class="text-[#ECD2F3] italic">local kitchens</span> near you.
                    </h1>
                    <p class="text-sm sm:text-base text-[#EFE9F2]/75 font-normal leading-relaxed">
                        {{ $fs['food_hero_subtitle'] ?? 'Browse real-time restaurant menus, customize portions and add-ons, filter by pure veg or open kitchens, and track every order live.' }}
                    </p>
                </div>

                <!-- Live Auto-Suggest Search Bar -->
                <div class="max-w-3xl relative" x-data="{ query: @js($search ?? ''), results: null, loading: false }" data-grail-reveal>
                    <form action="{{ route('food.index') }}" method="GET" class="relative">
                        <div class="relative flex items-center bg-[#141414] rounded-full border border-white/20 shadow-[0_18px_50px_rgba(0,0,0,0.65)] focus-within:border-[#ECD2F3] focus-within:ring-4 focus-within:ring-[#ECD2F3]/20 transition-all overflow-hidden">
                            <div class="pl-5 text-[#ECD2F3] flex items-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" x-model="query"
                                   @input.debounce.300ms="if(query.length >= 2){ loading = true; fetch('/food/suggest?q=' + encodeURIComponent(query)).then(r=>r.json()).then(d=>{ results = d; loading = false; }).catch(()=>{ loading = false; }); } else { results = null; }"
                                   placeholder="Search restaurant, dish, city, or cuisine..." 
                                   class="w-full bg-transparent py-4 pl-3.5 pr-28 text-sm sm:text-base font-medium text-white placeholder:text-[#EFE9F2]/40 focus:outline-none">
                            
                            <div class="absolute right-2 flex items-center gap-1.5">
                                @if($pureVegOnly)
                                    <input type="hidden" name="pure_veg" value="1">
                                @endif
                                @if($openNowOnly ?? false)
                                    <input type="hidden" name="open_now" value="1">
                                @endif
                                @if($takeawayOnly ?? false)
                                    <input type="hidden" name="takeaway" value="1">
                                @endif
                                @if($cuisine)
                                    <input type="hidden" name="cuisine" value="{{ $cuisine }}">
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
                                    <template x-for="r in results.restaurants" :key="r.url">
                                        <a :href="r.url" class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-[#ECD2F3]/35 transition border border-transparent hover:border-[#623562]/20">
                                            <div class="w-10 h-10 rounded-lg bg-[#1A1018] text-[#ECD2F3] overflow-hidden shrink-0 border border-black/8 flex items-center justify-center font-bold text-xs">
                                                <template x-if="r.image">
                                                    <img :src="r.image" :alt="r.name" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!r.image">
                                                    <span x-text="(r.name || 'K').slice(0, 2).toUpperCase()"></span>
                                                </template>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="text-xs font-bold text-[#1A1018] truncate" x-text="r.name"></div>
                                                <div class="text-[10px] text-[#1A1018]/60 flex items-center gap-1.5">
                                                    <span x-text="r.city ? ('📍 ' + r.city) : 'Local Kitchen'"></span>
                                                    <template x-if="r.rating">
                                                        <span class="font-bold text-[#623562]" x-text="'★ ' + Number(r.rating).toFixed(1)"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </template>

                        <!-- Matching Dishes -->
                        <template x-if="results?.dishes?.length > 0">
                            <div>
                                <span class="grail-eyebrow !text-[#623562] block mb-2">Menu Dishes</span>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <template x-for="d in results.dishes" :key="d.restaurant_url + '-' + d.title">
                                        <a :href="d.restaurant_url" class="flex items-center justify-between gap-3 p-2.5 rounded-xl hover:bg-[#ECD2F3]/35 transition border border-transparent hover:border-[#623562]/20">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <span class="w-3.5 h-3.5 rounded-xs border-2 flex items-center justify-center p-0.5 shrink-0"
                                                      :class="d.is_veg ? 'border-emerald-600' : 'border-rose-600'">
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="d.is_veg ? 'bg-emerald-600' : 'bg-rose-600'"></span>
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

                <!-- Dynamic Category / Cuisine Quick Pills (DB-derived only) -->
                @if($cuisineItems->isNotEmpty())
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-1" data-grail-reveal>
                        <span class="grail-eyebrow shrink-0 mr-1">Explore:</span>
                        @foreach($cuisineItems as $cItem)
                            <a href="{{ route('food.index', array_merge(request()->except(['page', 'cuisine']), ['cuisine' => $cItem['slug']])) }}" 
                               class="px-3.5 py-1.5 rounded-full {{ strtolower((string) $cuisine) === strtolower($cItem['slug']) ? 'bg-[#ECD2F3] text-[#1A1018] border-[#ECD2F3]' : 'bg-white/8 hover:bg-[#ECD2F3] border-white/15 hover:border-[#ECD2F3] text-[#EFE9F2] hover:text-[#1A1018]' }} border text-xs font-semibold transition-all shrink-0 flex items-center gap-1.5">
                                <span>{{ $cItem['name'] }}</span>
                                @if(!is_null($cItem['count']) && $cItem['count'] > 0)
                                    <span class="text-[10px] opacity-70">({{ $cItem['count'] }})</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Live Capabilities Strip (Honest Operational Pillars Only) -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 pt-2" data-grail-reveal>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-[#ECD2F3]/15 text-[#ECD2F3] border border-[#ECD2F3]/25 flex items-center justify-center text-sm font-bold shrink-0">🛵</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">Delivery &amp; Self-Pickup</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">Zero delivery fee on takeaway</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-400/25 flex items-center justify-center text-sm font-bold shrink-0">🛡️</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">Audited FSSAI Badges</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">Displayed only when verified</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-[#C9A24A]/15 text-[#C9A24A] border border-[#C9A24A]/25 flex items-center justify-center text-sm font-bold shrink-0">🔥</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">Custom Portions &amp; Add-ons</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">Live menu availability</div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-3.5 grail-dark-card">
                        <div class="w-8 h-8 rounded-xl bg-white/10 text-[#FFCDFF] border border-white/15 flex items-center justify-center text-sm font-bold shrink-0">📋</div>
                        <div class="min-w-0">
                            <div class="text-[11.5px] font-bold text-white truncate">Live Kitchen Tracking</div>
                            <div class="text-[10px] text-[#EFE9F2]/55 truncate">Placed → Preparing → Ready</div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- CURVED LIGHT EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-10 sm:py-14 px-4 sm:px-6 lg:px-8 border-0 shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
        <div class="max-w-7xl mx-auto space-y-12 sm:space-y-14 pb-10">

            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <!-- 2. FOOD CATEGORIES & CUISINE DISCOVERY RAIL (Real Categories/Cuisines Only) -->
            @if($cuisineItems->isNotEmpty())
                <section class="space-y-5" data-grail-reveal>
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">Browse Menu Categories</span>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                What are you craving today?
                            </h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium mt-0.5">Filter active kitchens and dishes by food category or cuisine.</p>
                        </div>
                        @if($cuisine)
                            <a href="{{ route('food.index', request()->except(['page', 'cuisine'])) }}" class="px-3.5 py-1.5 rounded-full bg-white border border-black/10 text-xs font-bold text-[#623562] hover:bg-[#ECD2F3]/35 transition shrink-0">
                                Clear "{{ $cuisine }}" ✕
                            </a>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3 sm:gap-4">
                        @foreach($cuisineItems as $col)
                            @php
                                $isCurrentCuisine = strtolower((string) $cuisine) === strtolower($col['slug']);
                            @endphp
                            <a href="{{ route('food.index', array_merge(request()->except(['page', 'cuisine']), ['cuisine' => $col['slug']])) }}" 
                               class="flex items-center gap-3 p-3.5 grail-card hover:-translate-y-0.5 transition-all duration-200 group cursor-pointer {{ $isCurrentCuisine ? 'ring-2 ring-[#623562] bg-[#F8EFFA]' : '' }}">
                                <div class="w-12 h-12 rounded-2xl overflow-hidden shrink-0 bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center font-bold text-sm border border-[#ECD2F3]/40">
                                    @if(!empty($col['image']))
                                        <img src="{{ $col['image'] }}" alt="{{ $col['name'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <span>{{ strtoupper(substr($col['name'], 0, 2)) }}</span>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <span class="text-xs font-bold text-[#1A1018] group-hover:text-[#623562] truncate block transition-colors">{{ $col['name'] }}</span>
                                    <span class="text-[10px] text-[#623562]/80 font-semibold block">
                                        @if(!is_null($col['count']) && $col['count'] > 0)
                                            {{ $col['count'] }} {{ Str::plural('dish', $col['count']) }} ›
                                        @else
                                            Explore kitchens ›
                                        @endif
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 3. GENUINE ACTIVE RESTAURANT OFFERS & COUPON CARDS (Zero Fake Coupons) -->
            @if($dealsCollection->isNotEmpty())
                <section id="deals" class="space-y-5" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">Active Restaurant Promotions</span>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap" style="font-family: var(--font-grail-display);">
                                <span>Live Kitchen Offers &amp; Promo Codes</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-sans font-bold uppercase tracking-wider">{{ $dealsCollection->count() }} Active</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium">Real coupons configured by verified partner kitchens, validated server-side at checkout.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($dealsCollection as $deal)
                            @php
                                $isPct = in_array($deal->type, ['percent', 'percentage'], true);
                                $headline = $isPct
                                    ? ($deal->value . '% OFF' . ($deal->max_discount ? (' up to ₹' . number_format($deal->max_discount / 100, 0)) : ''))
                                    : ('Flat ₹' . number_format($deal->value / 100, 0) . ' OFF');
                            @endphp
                            <div class="rounded-[24px] bg-gradient-to-br from-[#1A1018] via-[#2B1528] to-[#0D0D0D] p-6 text-white shadow-xl border border-[#ECD2F3]/25 relative overflow-hidden flex flex-col justify-between">
                                <div class="space-y-2 relative z-10">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-black uppercase tracking-wider">{{ $headline }}</span>
                                        @if($deal->vendor)
                                            <span class="text-[11px] font-semibold text-[#C9A24A] truncate">{{ $deal->vendor->shop_name }}</span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-light tracking-tight leading-tight text-white" style="font-family: var(--font-grail-display);">
                                        {{ $headline }} at {{ $deal->vendor?->shop_name ?? 'Partner Kitchen' }}
                                    </h3>
                                    <p class="text-xs text-[#EFE9F2]/70">
                                        @if($deal->min_cart_value)
                                            Min. bag subtotal ₹{{ number_format($deal->min_cart_value / 100, 0) }}.
                                        @else
                                            Valid on eligible menu items.
                                        @endif
                                        @if($deal->expires_at)
                                            Expires {{ $deal->expires_at->format('d M Y') }}.
                                        @endif
                                    </p>
                                </div>
                                <div class="pt-5 flex items-center justify-between gap-2 relative z-10 border-t border-white/15 mt-4">
                                    <div class="bg-black/40 px-3.5 py-1.5 rounded-full font-mono font-bold text-xs text-[#ECD2F3] border border-[#ECD2F3]/30 select-all">
                                        {{ $deal->code }}
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="copyCoupon('{{ addslashes($deal->code) }}')" class="px-3.5 py-2 grail-cta-pill text-[11px] cursor-pointer">
                                            Copy
                                        </button>
                                        @if($deal->vendor)
                                            <a href="{{ route('food.restaurant', $deal->vendor->slug) }}" class="px-3.5 py-2 rounded-full bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold transition">
                                                Menu ›
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 4. RECENT ORDERS & 1-CLICK REORDER STRIP (When user has food order history) -->
            @if(isset($recentFoodOrders) && $recentFoodOrders->isNotEmpty())
                <section class="space-y-4" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">1-Tap Repeat</span>
                            <h2 class="text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                Order Again
                            </h2>
                            <p class="text-xs text-[#1A1018]/60 font-medium">Revalidates live dish availability, pricing, and kitchen status before adding to your bag.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach($recentFoodOrders as $pastOrder)
                            <div class="p-4 grail-card flex flex-col justify-between space-y-3">
                                <div>
                                    <div class="flex items-center justify-between text-xs text-[#1A1018]/55 mb-1">
                                        <a href="{{ $pastOrder->vendor ? route('food.restaurant', $pastOrder->vendor->slug) : '#' }}" class="font-bold text-[#1A1018] hover:text-[#623562] truncate">
                                            {{ $pastOrder->vendor?->shop_name ?? 'Restaurant' }}
                                        </a>
                                        <span class="shrink-0">{{ $pastOrder->created_at->diffForHumans() }}</span>
                                    </div>
                                    <div class="space-y-1">
                                        @foreach($pastOrder->items->take(3) as $it)
                                            <div class="text-xs text-[#1A1018]/80 font-semibold truncate">
                                                {{ $it->quantity }}x {{ $it->product?->title ?? $it->snapshot['title'] ?? 'Dish' }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-xs font-black text-[#623562] mt-2 flex items-center justify-between">
                                        <span>₹{{ number_format($pastOrder->total_amount / 100, 2) }}</span>
                                        <span class="text-[10px] font-semibold text-[#1A1018]/55">{{ $pastOrder->food_stage_label }}</span>
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
            <section id="restaurants" class="space-y-6" data-grail-reveal>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-3 border-b border-black/10">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-1">Partner Kitchens</span>
                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap" style="font-family: var(--font-grail-display);">
                            <span>Restaurants &amp; Cloud Kitchens</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-sans font-bold">
                                {{ $restaurants->total() }} {{ Str::plural('Kitchen', $restaurants->total()) }}
                            </span>
                        </h2>
                        <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium flex items-center gap-1.5 flex-wrap mt-1">
                            <span>Showing kitchens serving</span>
                            <button type="button" @click="locationModalOpen = true" class="font-bold text-[#623562] hover:underline cursor-pointer inline-flex items-center gap-1">
                                <span class="underline decoration-[#623562]/40">{{ $activeLocLabel ?: 'All Locations' }}</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            @if($hasLocationFilter)
                                <form action="{{ route('food.location.set') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="clear" value="1">
                                    <button type="submit" class="text-[11px] font-bold text-rose-600 hover:underline ml-1 cursor-pointer">(Show All Locations)</button>
                                </form>
                            @endif
                        </p>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-[#1A1018]/60">Sort by:</span>
                        <select onchange="window.location.href = this.value" class="px-3.5 py-2 grail-input-light text-xs font-bold text-[#1A1018]">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'recommended', 'page' => null]) }}" {{ ($sort ?? 'recommended') === 'recommended' ? 'selected' : '' }}>Recommended</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'rating', 'page' => null]) }}" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>Highest Rated</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'delivery_time', 'page' => null]) }}" {{ ($sort ?? '') === 'delivery_time' ? 'selected' : '' }}>Fastest Prep</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'cost_low', 'page' => null]) }}" {{ ($sort ?? '') === 'cost_low' ? 'selected' : '' }}>Cost: Low to High</option>
                        </select>
                    </div>
                </div>

                <!-- Interactive Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
                    <!-- Pure Veg Toggle -->
                    <a href="{{ $pureVegOnly ? request()->fullUrlWithQuery(['pure_veg' => null, 'veg' => null, 'page' => null]) : request()->fullUrlWithQuery(['pure_veg' => 1, 'page' => null]) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-2 {{ $pureVegOnly ? 'bg-emerald-700 text-white shadow-md shadow-emerald-700/25' : 'bg-white text-[#1A1018] border border-black/10 hover:border-emerald-400' }}">
                        <span class="w-3.5 h-3.5 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        </span>
                        <span>Pure Veg</span>
                    </a>

                    <!-- Open Now Filter -->
                    <a href="{{ ($openNowOnly ?? false) ? request()->fullUrlWithQuery(['open_now' => null, 'page' => null]) : request()->fullUrlWithQuery(['open_now' => 1, 'page' => null]) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($openNowOnly ?? false) ? 'bg-[#1A1018] text-[#ECD2F3] shadow-md' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span class="w-2 h-2 rounded-full {{ ($openNowOnly ?? false) ? 'bg-emerald-400' : 'bg-emerald-500' }}"></span>
                        <span>Open Now</span>
                    </a>

                    <!-- Takeaway / Self-Pickup Filter -->
                    <a href="{{ ($takeawayOnly ?? false) ? request()->fullUrlWithQuery(['takeaway' => null, 'page' => null]) : request()->fullUrlWithQuery(['takeaway' => 1, 'page' => null]) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($takeawayOnly ?? false) ? 'bg-[#1A1018] text-[#ECD2F3] shadow-md' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>🥡</span>
                        <span>Takeaway / Pickup</span>
                    </a>

                    <!-- Rating 4+ Filter -->
                    <a href="{{ ($rating ?? '') === '4plus' ? request()->fullUrlWithQuery(['rating' => null, 'page' => null]) : request()->fullUrlWithQuery(['rating' => '4plus', 'page' => null]) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($rating ?? '') === '4plus' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-md' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>★</span>
                        <span>Rated 4.0+</span>
                    </a>

                    <!-- Fast Prep Filter -->
                    <a href="{{ ($deliveryTime ?? '') === 'under_30' ? request()->fullUrlWithQuery(['delivery_time' => null, 'page' => null]) : request()->fullUrlWithQuery(['delivery_time' => 'under_30', 'page' => null]) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($deliveryTime ?? '') === 'under_30' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-md' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>⚡</span>
                        <span>Fast Prep (&le;25 mins)</span>
                    </a>

                    <!-- Budget Filter -->
                    <a href="{{ ($budget ?? '') === 'under_200' ? request()->fullUrlWithQuery(['budget' => null, 'page' => null]) : request()->fullUrlWithQuery(['budget' => 'under_200', 'page' => null]) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($budget ?? '') === 'under_200' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-md' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>💰</span>
                        <span>Dishes Under ₹200</span>
                    </a>

                    <!-- Active Filters Clearer -->
                    @if($hasActiveFilters)
                        <a href="{{ route('food.index') }}" class="px-4 py-2 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition shrink-0">
                            ✕ Clear All Filters
                        </a>
                    @endif
                </div>

                <!-- Restaurants Cards Grid / Honest Unserviceable Empty State -->
                @if($restaurants->isEmpty())
                    <div class="grail-card p-10 sm:p-12 text-center max-w-xl mx-auto space-y-4">
                        <span class="text-5xl block">{{ $hasLocationFilter ? '📍' : '🔍' }}</span>
                        <h3 class="text-2xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">
                            @if($hasLocationFilter)
                                No kitchens currently serve {{ $activeLocLabel }}
                            @else
                                No restaurants match your filters
                            @endif
                        </h3>
                        <p class="text-xs text-[#1A1018]/65 leading-relaxed">
                            @if($hasLocationFilter)
                                Try selecting another city or pincode, or clear your delivery location filter to browse all partner restaurants on K71 Food.
                            @else
                                Try resetting your filters or search keywords to browse all active restaurants and cloud kitchens.
                            @endif
                        </p>
                        <div class="flex items-center justify-center gap-3 flex-wrap pt-1">
                            @if($hasLocationFilter)
                                <form action="{{ route('food.location.set') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="clear" value="1">
                                    <button type="submit" class="px-5 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">
                                        Show All Locations
                                    </button>
                                </form>
                                <button type="button" @click="locationModalOpen = true" class="px-5 py-2.5 rounded-full bg-white border border-black/15 text-[#1A1018] text-xs font-bold hover:bg-[#F6F6F6] cursor-pointer">
                                    Change Delivery Zone
                                </button>
                            @endif
                            @if($hasActiveFilters)
                                <a href="{{ route('food.index') }}" class="px-5 py-2.5 rounded-full bg-white border border-black/15 text-[#1A1018] text-xs font-bold hover:bg-[#F6F6F6]">
                                    Reset All Filters
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($restaurants as $rest)
                            @php
                                $rCover = $rest->banner_url ?: asset('images/brand-banner.jpg');
                                $rLogo = $rest->logo_url;
                                $isVeg = (bool) $rest->settings?->is_pure_veg;
                                $cuisines = $rest->cuisine_list ?? ['Multi-Cuisine'];
                                $prepTime = (int) ($rest->settings?->avg_prep_time_mins ?? 25);
                                $deliveryFee = ($rest->settings?->food_delivery_fee ?? 0) / 100;
                                $freeAbove = $rest->settings?->food_free_delivery_above ? ($rest->settings->food_free_delivery_above / 100) : null;
                                $opStatus = $rest->operational_status ?? ['accepting_orders' => (bool) ($rest->is_open ?? true), 'status_key' => 'open', 'label' => 'Open'];
                                $acceptingOrders = (bool) ($opStatus['accepting_orders'] ?? true);
                                $realRating = $rest->food_rating;
                                $realReviewCount = (int) ($rest->food_review_count_value ?? 0);
                                $deliveryMode = $rest->settings?->food_delivery_mode ?? 'both';
                                $modeLabel = match($deliveryMode) {
                                    'delivery' => 'Delivery Only',
                                    'takeaway' => 'Self-Pickup Only',
                                    default => 'Delivery & Pickup',
                                };
                                $restCoupon = $dealsCollection->firstWhere('vendor_id', $rest->id);
                            @endphp
                            <div class="grail-card overflow-hidden hover:shadow-2xl transition-all duration-300 group flex flex-col justify-between hover:-translate-y-1 {{ !$acceptingOrders ? 'opacity-90' : '' }}">
                                <div>
                                    <!-- Banner Image with Floating Logo Overlay -->
                                    <div class="relative aspect-[16/9] w-full overflow-hidden bg-[#0D0D0D]">
                                        <img src="{{ $rCover }}" alt="{{ $rest->shop_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 {{ !$acceptingOrders ? 'grayscale-[45%]' : '' }}">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-black/20"></div>
                                        
                                        <!-- Top Left Honest Badges -->
                                        <div class="absolute top-3 left-3 flex items-center gap-1.5 flex-wrap z-10">
                                            @if($isVeg)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-700 text-white shadow-sm flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                                    <span>Pure Veg</span>
                                                </span>
                                            @endif
                                            @if($realRating !== null && $realReviewCount > 0)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-black/80 backdrop-blur-md text-[#C9A24A] border border-[#C9A24A]/35 shadow-sm flex items-center gap-1">
                                                    <span>★</span> {{ number_format($realRating, 1) }} <span class="text-[#EFE9F2]/60 text-[9px] font-medium">({{ $realReviewCount }})</span>
                                                </span>
                                            @endif
                                            @if($rest->isFssaiVerified())
                                                <span class="px-2 py-0.5 rounded-full text-[9.5px] font-black uppercase bg-emerald-950/85 backdrop-blur-md text-emerald-300 border border-emerald-400/30 shadow-sm flex items-center gap-1">
                                                    <span>🛡️</span> FSSAI Verified
                                                </span>
                                            @endif
                                            @if(!$acceptingOrders)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-600 text-white shadow-sm">
                                                    {{ $opStatus['label'] ?? 'Currently Closed' }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Bottom Floating Strip on Banner -->
                                        <div class="absolute bottom-3 left-3 right-3 text-white z-10 flex items-end justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-[11px] font-black tracking-wider uppercase text-[#ECD2F3] flex items-center gap-2 mb-0.5">
                                                    <span>Prep: ~{{ $prepTime }} mins</span>
                                                    <span>•</span>
                                                    <span>{{ $modeLabel }}</span>
                                                </div>
                                                <h3 class="text-xl font-black truncate text-white drop-shadow-md tracking-tight">{{ $rest->shop_name }}</h3>
                                            </div>
                                            <!-- Storefront Logo Avatar -->
                                            <div class="w-12 h-12 rounded-2xl bg-white p-1 border-2 border-[#ECD2F3]/50 shadow-lg overflow-hidden shrink-0 flex items-center justify-center">
                                                @if($rLogo)
                                                    <img src="{{ $rLogo }}" alt="{{ $rest->shop_name }} Logo" class="w-full h-full object-cover rounded-xl">
                                                @else
                                                    <div class="w-full h-full rounded-xl bg-[#1A1018] text-[#ECD2F3] font-black text-sm flex items-center justify-center">
                                                        {{ strtoupper(substr($rest->shop_name, 0, 2)) }}
                                                    </div>
                                                @endif
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

                                        @if($rest->description)
                                            <p class="text-xs text-[#1A1018]/65 line-clamp-2 leading-relaxed">
                                                {{ $rest->description }}
                                            </p>
                                        @endif

                                        <!-- Highlight Pills: Delivery Fee & Location -->
                                        <div class="flex items-center justify-between text-xs font-bold text-[#1A1018] pt-3 border-t border-black/8 gap-2">
                                            <span class="flex items-center gap-1 text-[#1A1018]/60 truncate">
                                                <span>📍</span>
                                                <span class="truncate max-w-[150px]">{{ $rest->city ?: 'Local Kitchen' }}</span>
                                            </span>
                                            @if($deliveryMode === 'takeaway')
                                                <span class="text-emerald-700 font-black bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80 shrink-0">
                                                    Self-Pickup (₹0 Fee)
                                                </span>
                                            @elseif($deliveryFee <= 0)
                                                <span class="text-emerald-700 font-black bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200/80 shrink-0">
                                                    Free Delivery
                                                </span>
                                            @else
                                                <span class="text-[#1A1018] font-bold bg-[#FAFAF8] px-2.5 py-0.5 rounded-full border border-black/10 shrink-0">
                                                    ₹{{ number_format($deliveryFee, 0) }} Delivery{{ $freeAbove ? ' • Free ≥ ₹' . number_format($freeAbove, 0) : '' }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Genuine Restaurant Coupon Ribbon (Only if real active coupon exists for this kitchen) -->
                                        @if($restCoupon)
                                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-[#ECD2F3]/35 border border-[#623562]/15 text-[#623562] text-[11px] font-bold">
                                                <span>🏷️</span>
                                                <span class="truncate">
                                                    {{ in_array($restCoupon->type, ['percent', 'percentage']) ? ($restCoupon->value . '% OFF') : ('₹' . number_format($restCoupon->value / 100, 0) . ' OFF') }} • Use code {{ $restCoupon->code }}
                                                </span>
                                            </div>
                                        @elseif(!$acceptingOrders && !empty($opStatus['reason']))
                                            <div class="flex items-center gap-2 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-[11px] font-semibold">
                                                <span>⏸</span>
                                                <span class="truncate">{{ $opStatus['reason'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="p-5 pt-0">
                                    <a href="{{ route('food.restaurant', $rest->slug) }}" 
                                       class="w-full block py-3 grail-btn-obsidian text-center text-xs font-bold shadow-xs cursor-pointer">
                                        {{ $acceptingOrders ? 'View Menu & Order ›' : 'Browse Menu ›' }}
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

            <!-- 6. TRENDING / AVAILABLE DISHES (Only real active available dishes from DB) -->
            @if($dishes->isNotEmpty())
                <section class="space-y-6" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">Fresh from the Menu</span>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                Popular Dishes &amp; Kitchen Specials
                            </h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-medium">Available right now with live restaurant pricing and customisation options.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                        @foreach($dishes as $dish)
                            @php
                                $dImage = $dish->primary_image_url;
                                $isDietVeg = $dish->isVeg();
                                $price = $dish->base_price ? ($dish->base_price / 100) : 0;
                                $requiresConfig = ($dish->optionGroups?->where('is_active', true)->isNotEmpty() ?? false)
                                    || ($dish->modifiers?->isNotEmpty() ?? false)
                                    || (($dish->variants?->count() ?? 0) > 1);
                                $vendorUrl = $dish->vendor ? route('food.restaurant', $dish->vendor->slug) : '#';
                            @endphp
                            <div class="grail-card hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between group relative overflow-hidden">
                                <div>
                                    <div class="relative aspect-[4/3] w-full overflow-hidden bg-[#FAFAF8]">
                                        <a href="{{ $vendorUrl }}">
                                            <img src="{{ $dImage }}" alt="{{ $dish->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                        </a>
                                        
                                        <div class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm p-1 rounded-md shadow-xs flex items-center justify-center" title="{{ $dish->dietary_label_resolved }}">
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
                                                {{ $dish->dietary_label_resolved }}
                                            </span>
                                        </div>

                                        @if(!empty($dish->avg_rating) && (int) ($dish->review_count ?? 0) > 0)
                                            <div class="absolute bottom-3 left-3 px-2.5 py-0.5 rounded-full bg-black/80 backdrop-blur-sm text-white text-[10px] font-bold flex items-center gap-1">
                                                <span class="text-[#C9A24A]">★</span> {{ number_format($dish->avg_rating, 1) }}
                                                <span class="text-white/60 font-normal">({{ $dish->review_count }})</span>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="p-4 space-y-2">
                                        <div>
                                            <h3 class="text-sm font-bold text-[#1A1018] group-hover:text-[#623562] transition-colors line-clamp-1">
                                                <a href="{{ $vendorUrl }}">{{ $dish->title }}</a>
                                            </h3>
                                            <a href="{{ $vendorUrl }}" class="text-[11px] text-[#623562] font-semibold hover:underline truncate block">
                                                {{ $dish->vendor?->shop_name ?: 'Partner Kitchen' }}
                                            </a>
                                        </div>
                                        
                                        @if($dish->description)
                                            <p class="text-xs text-[#1A1018]/65 line-clamp-2 leading-relaxed">
                                                {{ Str::limit(strip_tags($dish->description), 90) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="p-4 pt-3 flex items-center justify-between border-t border-black/8">
                                    <div>
                                        <span class="text-base font-black text-[#1A1018]">₹{{ number_format($price, 0) }}</span>
                                    </div>

                                    @if($requiresConfig)
                                        <a href="{{ $vendorUrl }}"
                                           class="px-4 py-2 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] shadow-sm text-xs font-bold transition-all flex items-center gap-1">
                                            <span>Customise</span>
                                            <span>›</span>
                                        </a>
                                    @else
                                        <button type="button" 
                                                @click="quickAddToCart($event, {{ $dish->id }}, '{{ addslashes($dish->title) }}', {{ $dish->vendor_id }}, {{ $dish->variants->first()?->id ?? 'null' }})"
                                                class="px-4 py-2 rounded-full bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] shadow-sm text-xs font-bold transition-all cursor-pointer active:scale-95 flex items-center gap-1.5">
                                            <span>ADD</span>
                                            <span class="text-sm font-bold leading-none">+</span>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 7. THE K71 FOOD STANDARD (Honest Platform Features) -->
            <section class="space-y-5" data-grail-reveal>
                <div class="text-center max-w-xl mx-auto space-y-1.5">
                    <span class="grail-eyebrow !text-[#623562]">HOW K71 FOOD WORKS</span>
                    <h2 class="text-3xl sm:text-4xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Transparent Local Food Ordering</h2>
                    <p class="text-xs text-[#1A1018]/60">Real kitchen availability, clear packaging and delivery charges, and direct tracking from placement to handover.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-[#ECD2F3]/50 text-[#1A1018] flex items-center justify-center text-2xl mx-auto">🛵</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">Delivery or Self-Pickup</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">Select doorstep delivery or pick up directly at the restaurant with zero delivery fees and a 4-digit pickup code.</p>
                    </div>

                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mx-auto">🛡️</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">Verified Compliance</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">FSSAI Verified badges appear exclusively for restaurants whose license numbers have been reviewed.</p>
                    </div>

                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-green-100 text-green-700 flex items-center justify-center text-2xl mx-auto">🌿</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">Pure Veg &amp; Dietary Filters</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">Filter strictly by Pure Veg kitchens or vegetarian dishes with required/optional add-on customization.</p>
                    </div>

                    <div class="p-5 grail-card space-y-2 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-[#FAF5EB] border border-[#C9A24A]/30 text-[#5F5138] flex items-center justify-center text-2xl mx-auto">💳</div>
                        <h3 class="text-sm font-bold text-[#1A1018]">Upfront Fee Breakdown</h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">See item total, packaging fees, free-delivery thresholds, and coupon discounts before placing your order.</p>
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
                            Manage Your Menu &amp; Kitchen Orders on K71 Food
                        </h2>
                        <p class="text-xs sm:text-sm text-[#EFE9F2]/70 leading-relaxed">
                            Create custom menu sections, add-on groups, operating hours, delivery zones, pause/resume controls, and real-time kitchen order status updates.
                        </p>
                    </div>
                    <div class="shrink-0 flex flex-col sm:flex-row items-center gap-3">
                        <a href="{{ route('vendor.register') }}" class="w-full sm:w-auto px-5 py-3 grail-cta-pill text-xs sm:text-sm text-center flex items-center justify-center">
                            Register Kitchen →
                        </a>
                        <a href="{{ route('vendor.login') }}" class="w-full sm:w-auto px-5 py-3 grail-btn-ghost-dark text-xs sm:text-sm text-center flex items-center justify-center">
                            Partner Login
                        </a>
                    </div>
                </div>
            </section>

        </div>
        </div>

        <!-- Single-Restaurant Bag Conflict Modal (409) -->
        <div x-show="conflictModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm">
            <div class="bg-[#FAFAF8] text-[#1A1018] rounded-[28px] max-w-md w-full p-6 shadow-2xl border border-black/10 space-y-4" @click.outside="conflictModalOpen = false">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-2xl font-bold">🛍️</div>
                <h3 class="text-xl font-bold text-[#1A1018]" style="font-family: var(--font-grail-display);">Replace items in your bag?</h3>
                <p class="text-xs text-[#1A1018]/75 leading-relaxed" x-text="conflictData?.message || 'Your bag contains dishes from another restaurant. Each food order goes to one kitchen.'"></p>
                <div class="flex items-center justify-end gap-2.5 pt-2">
                    <button type="button" @click="conflictModalOpen = false; pendingQuickAdd = null;" class="px-4 py-2.5 rounded-full border border-black/15 text-xs font-bold text-[#1A1018] hover:bg-black/5 cursor-pointer">
                        Keep Current Bag
                    </button>
                    <button type="button" @click="confirmClearAndAdd()" class="px-5 py-2.5 grail-btn-obsidian text-xs font-bold cursor-pointer">
                        Clear Bag &amp; Add Dish
                    </button>
                </div>
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function k71FoodHomepage() {
        return {
            currentLocation: @js($activeLocLabel ?: 'All Locations'),
            conflictModalOpen: false,
            conflictData: null,
            pendingQuickAdd: null,
            init() {
                window.addEventListener('food-location-changed', (e) => {
                    if (e.detail) this.currentLocation = e.detail;
                });
            },
            copyCoupon(code) {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(code).then(() => {
                        if (window.showToast) {
                            window.showToast('Coupon code ' + code + ' copied! 🏷️');
                        } else {
                            alert('Coupon ' + code + ' copied! Use at checkout.');
                        }
                    });
                }
            },
            async quickAddToCart(e, productId, title, vendorId, variantId, clearExisting = false) {
                const btn = e?.currentTarget;
                const originalHtml = btn ? btn.innerHTML : '<span>ADD</span><span class="text-sm font-bold leading-none">+</span>';
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '<span>ADDING...</span>';
                }

                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                try {
                    const res = await fetch('/cart/add', {
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
                            quantity: 1,
                            clear_existing: clearExisting ? 1 : 0
                        })
                    });
                    const data = await res.json();

                    if (res.status === 409 && data.conflict) {
                        this.conflictData = data;
                        this.pendingQuickAdd = { productId, title, vendorId, variantId };
                        this.conflictModalOpen = true;
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = originalHtml;
                        }
                        return;
                    }

                    if (res.ok && (data.success || data.cart_count)) {
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
                } catch (err) {
                    console.error(err);
                    if (window.showToast) {
                        window.showToast('Failed to add to bag. Please try again.', false);
                    }
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                }
            },
            confirmClearAndAdd() {
                if (!this.pendingQuickAdd) {
                    this.conflictModalOpen = false;
                    return;
                }
                const item = this.pendingQuickAdd;
                this.conflictModalOpen = false;
                this.pendingQuickAdd = null;
                this.quickAddToCart(null, item.productId, item.title, item.vendorId, item.variantId, true);
            }
        };
    }
    </script>
    @endpush
</x-layouts.food>
