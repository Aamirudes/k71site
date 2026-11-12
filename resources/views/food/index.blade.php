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

        <!-- 1. COMPACT GRAIL OBSIDIAN FOOD HERO & LIVE SEARCH -->
        <section class="grail-hero-band pt-5 sm:pt-7 pb-7 sm:pb-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-4">

                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4" data-grail-reveal>
                    <div class="max-w-2xl space-y-2">
                        <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 border border-white/15 text-[#ECD2F3] text-[10.5px] font-bold uppercase tracking-[0.12em] flex-wrap backdrop-blur-md">
                            <span class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>{{ $fs['food_hero_badge'] ?? 'K71 FOOD • DELIVERY & SELF-PICKUP' }}</span>
                            </span>
                            <span class="text-white/25">•</span>
                            <button type="button" @click="locationModalOpen = true" class="hover:text-white flex items-center gap-1 cursor-pointer transition">
                                <span class="text-[#C9A24A]">📍 {{ $hasLocationFilter ? 'DELIVERING TO' : 'AREA' }}</span>
                                <span class="font-bold underline decoration-[#ECD2F3]/50" x-text="currentLocation">{{ $activeLocLabel ?: 'All Locations' }}</span>
                                <span>▾</span>
                            </button>
                        </div>

                        <h1 class="text-3xl sm:text-4xl lg:text-[2.75rem] font-light text-white tracking-tight leading-[1.02]" style="font-family: var(--font-grail-display);">
                            Fresh meals from <span class="text-[#ECD2F3] italic">local kitchens</span> near you.
                        </h1>
                    </div>

                    <!-- Live Auto-Suggest Search Bar (Inline Right on Desktop) -->
                    <div class="w-full lg:max-w-md xl:max-w-lg relative" x-data="{ query: @js($search ?? ''), results: null, loading: false }">
                        <form action="{{ route('food.index') }}" method="GET" class="relative">
                            <div class="relative flex items-center bg-[#141414] rounded-full border border-white/20 shadow-[0_14px_40px_rgba(0,0,0,0.6)] focus-within:border-[#ECD2F3] focus-within:ring-4 focus-within:ring-[#ECD2F3]/20 transition-all overflow-hidden">
                                <div class="pl-4 text-[#ECD2F3] flex items-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                                </div>
                                <input type="text" name="search" x-model="query"
                                       @input.debounce.300ms="if(query.length >= 2){ loading = true; fetch('/food/suggest?q=' + encodeURIComponent(query)).then(r=>r.json()).then(d=>{ results = d; loading = false; }).catch(()=>{ loading = false; }); } else { results = null; }"
                                       placeholder="Search dishes, kitchens, city..." 
                                       class="w-full bg-transparent py-3 pl-3 pr-24 text-sm font-medium text-white placeholder:text-[#EFE9F2]/40 focus:outline-none">
                                
                                <div class="absolute right-1.5 flex items-center gap-1">
                                    @if($pureVegOnly)<input type="hidden" name="pure_veg" value="1">@endif
                                    @if($openNowOnly ?? false)<input type="hidden" name="open_now" value="1">@endif
                                    @if($takeawayOnly ?? false)<input type="hidden" name="takeaway" value="1">@endif
                                    @if($cuisine)<input type="hidden" name="cuisine" value="{{ $cuisine }}">@endif
                                    <button type="submit" class="px-4 py-2 grail-cta-pill text-xs cursor-pointer flex items-center gap-1">
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
                            <template x-if="results?.dishes?.length > 0">
                                <div>
                                    <span class="grail-eyebrow !text-[#623562] block mb-2">Menu Dishes</span>
                                    <div class="grid grid-cols-1 gap-1.5">
                                        <template x-for="d in results.dishes" :key="d.restaurant_url + '-' + d.title">
                                            <a :href="d.restaurant_url" class="flex items-center justify-between gap-3 p-2 rounded-xl hover:bg-[#ECD2F3]/35 transition border border-transparent hover:border-[#623562]/20">
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

                            <template x-if="results?.restaurants?.length > 0">
                                <div>
                                    <span class="grail-eyebrow !text-[#623562] block mb-2">Restaurants &amp; Kitchens</span>
                                    <div class="grid grid-cols-1 gap-1.5">
                                        <template x-for="r in results.restaurants" :key="r.url">
                                            <a :href="r.url" class="flex items-center gap-3 p-2 rounded-xl hover:bg-[#ECD2F3]/35 transition border border-transparent hover:border-[#623562]/20">
                                                <div class="w-9 h-9 rounded-lg bg-[#1A1018] text-[#ECD2F3] overflow-hidden shrink-0 border border-black/8 flex items-center justify-center font-bold text-xs">
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
                        </div>
                    </div>
                </div>

                <!-- Dynamic Category / Cuisine Quick Pills (Compact Single Row) -->
                @if($cuisineItems->isNotEmpty())
                    <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pt-0.5">
                        <a href="{{ route('food.index', request()->except(['page', 'cuisine'])) }}"
                           class="px-3.5 py-1.5 rounded-full {{ empty($cuisine) ? 'bg-[#ECD2F3] text-[#1A1018] border-[#ECD2F3]' : 'bg-white/8 hover:bg-white/15 border-white/15 text-[#EFE9F2]' }} border text-xs font-semibold transition-all shrink-0">
                            All Food
                        </a>
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

            </div>
        </section>

        <!-- CURVED LIGHT EDITORIAL SHEET — FOOD PRODUCTS FIRST FOR EFFORTLESS ORDERING -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-6 sm:py-9 px-4 sm:px-6 lg:px-8 border-0 shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
        <div class="max-w-7xl mx-auto space-y-10 sm:space-y-12 pb-10">

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

            <!-- 2. TOP STICKY-CLEAN QUICK CONTROLS, VIEW JUMPS & FILTER PILLS -->
            <div class="space-y-3 pb-4 border-b border-black/10">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <a href="#dishes" class="px-4 py-2 rounded-full bg-[#1A1018] text-[#ECD2F3] text-xs font-bold shadow-xs inline-flex items-center gap-1.5">
                            <span>🍽️ Dishes</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-white/15 text-[10px]">{{ $dishes->count() }}</span>
                        </a>
                        <a href="#restaurants" class="px-4 py-2 rounded-full bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40 text-xs font-bold inline-flex items-center gap-1.5 transition">
                            <span>🏪 Kitchens</span>
                            <span class="px-1.5 py-0.2 rounded-full bg-black/8 text-[10px]">{{ $restaurants->total() }}</span>
                        </a>
                        @if($dealsCollection->isNotEmpty())
                            <a href="#deals" class="px-4 py-2 rounded-full bg-white text-[#623562] border border-[#623562]/20 hover:bg-[#ECD2F3]/30 text-xs font-bold inline-flex items-center gap-1 transition">
                                <span>🏷️ Offers ({{ $dealsCollection->count() }})</span>
                            </a>
                        @endif
                    </div>

                    <div class="flex items-center justify-between sm:justify-end gap-2">
                        <div class="flex items-center gap-1.5 text-xs text-[#1A1018]/65">
                            <span>Area:</span>
                            <button type="button" @click="locationModalOpen = true" class="font-bold text-[#623562] hover:underline cursor-pointer inline-flex items-center gap-1">
                                <span>{{ $activeLocLabel ?: 'All Locations' }}</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            @if($hasLocationFilter)
                                <form action="{{ route('food.location.set') }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="clear" value="1">
                                    <button type="submit" class="text-[11px] font-bold text-rose-600 hover:underline cursor-pointer">(Reset)</button>
                                </form>
                            @endif
                        </div>

                        <select onchange="window.location.href = this.value" aria-label="Sort food items" class="px-3.5 py-2 grail-input-light text-xs font-bold text-[#1A1018]">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'recommended', 'page' => null]) }}" {{ ($sort ?? 'recommended') === 'recommended' ? 'selected' : '' }}>Sort: Recommended</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'cost_low', 'page' => null]) }}" {{ ($sort ?? '') === 'cost_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'delivery_time', 'page' => null]) }}" {{ ($sort ?? '') === 'delivery_time' ? 'selected' : '' }}>Fastest Prep</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'rating', 'page' => null]) }}" {{ ($sort ?? '') === 'rating' ? 'selected' : '' }}>Highest Rated</option>
                        </select>
                    </div>
                </div>

                <!-- Interactive Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-0.5">
                    <a href="{{ $pureVegOnly ? request()->fullUrlWithQuery(['pure_veg' => null, 'veg' => null, 'page' => null]) : request()->fullUrlWithQuery(['pure_veg' => 1, 'page' => null]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-2 {{ $pureVegOnly ? 'bg-emerald-700 text-white shadow-sm' : 'bg-white text-[#1A1018] border border-black/10 hover:border-emerald-400' }}">
                        <span class="w-3 h-3 rounded-xs border-2 border-current flex items-center justify-center p-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                        </span>
                        <span>Pure Veg</span>
                    </a>

                    <a href="{{ ($openNowOnly ?? false) ? request()->fullUrlWithQuery(['open_now' => null, 'page' => null]) : request()->fullUrlWithQuery(['open_now' => 1, 'page' => null]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($openNowOnly ?? false) ? 'bg-[#1A1018] text-[#ECD2F3] shadow-sm' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span class="w-2 h-2 rounded-full {{ ($openNowOnly ?? false) ? 'bg-emerald-400' : 'bg-emerald-500' }}"></span>
                        <span>Open Now</span>
                    </a>

                    <a href="{{ ($takeawayOnly ?? false) ? request()->fullUrlWithQuery(['takeaway' => null, 'page' => null]) : request()->fullUrlWithQuery(['takeaway' => 1, 'page' => null]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($takeawayOnly ?? false) ? 'bg-[#1A1018] text-[#ECD2F3] shadow-sm' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>🥡 Takeaway</span>
                    </a>

                    <a href="{{ ($rating ?? '') === '4plus' ? request()->fullUrlWithQuery(['rating' => null, 'page' => null]) : request()->fullUrlWithQuery(['rating' => '4plus', 'page' => null]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($rating ?? '') === '4plus' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-sm' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>★ 4.0+</span>
                    </a>

                    <a href="{{ ($deliveryTime ?? '') === 'under_30' ? request()->fullUrlWithQuery(['delivery_time' => null, 'page' => null]) : request()->fullUrlWithQuery(['delivery_time' => 'under_30', 'page' => null]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($deliveryTime ?? '') === 'under_30' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-sm' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>⚡ Prep &le;25m</span>
                    </a>

                    <a href="{{ ($budget ?? '') === 'under_200' ? request()->fullUrlWithQuery(['budget' => null, 'page' => null]) : request()->fullUrlWithQuery(['budget' => 'under_200', 'page' => null]) }}" 
                       class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all shrink-0 flex items-center gap-1.5 {{ ($budget ?? '') === 'under_200' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-sm' : 'bg-white text-[#1A1018] border border-black/10 hover:border-[#623562]/40' }}">
                        <span>Under ₹200</span>
                    </a>

                    @if($hasActiveFilters)
                        <a href="{{ route('food.index') }}" class="px-3.5 py-1.5 rounded-full bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold transition shrink-0">
                            ✕ Clear Filters
                        </a>
                    @endif
                </div>
            </div>

            <!-- 3. FOOD DISHES / PRODUCTS AT THE VERY TOP (IMMEDIATE DISCOVERY & 1-CLICK ADD) -->
            <section id="dishes" class="space-y-5" data-grail-reveal>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-0.5">Order Direct</span>
                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap" style="font-family: var(--font-grail-display);">
                            <span>Fresh Dishes &amp; Kitchen Specials</span>
                            @if($cuisine)
                                <span class="px-3 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-xs font-sans font-bold">{{ $cuisine }}</span>
                            @endif
                        </h2>
                    </div>
                    <span class="text-xs font-semibold text-[#1A1018]/55 shrink-0">
                        {{ $dishes->count() }} {{ Str::plural('dish', $dishes->count()) }}
                    </span>
                </div>

                @if($dishes->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                        @foreach($dishes as $dish)
                            @include('food.partials.dish_card', ['dish' => $dish])
                        @endforeach
                    </div>
                @else
                    <div class="grail-card p-8 sm:p-10 text-center max-w-xl mx-auto space-y-3">
                        <span class="text-4xl block">🍽️</span>
                        <h3 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">
                            @if($hasLocationFilter)
                                No dishes available for {{ $activeLocLabel }}
                            @elseif($hasActiveFilters)
                                No dishes match your current filters
                            @else
                                Partner kitchens are updating their menus
                            @endif
                        </h3>
                        <p class="text-xs text-[#1A1018]/60 leading-relaxed">
                            @if($hasLocationFilter)
                                Switch delivery area or clear the location filter to see dishes across all partner kitchens.
                            @elseif($hasActiveFilters)
                                Try clearing some filters or searching with different keywords.
                            @else
                                New fresh dishes added by verified kitchens will appear right here.
                            @endif
                        </p>
                        @if($hasLocationFilter || $hasActiveFilters)
                            <div class="flex items-center justify-center gap-2.5 pt-1 flex-wrap">
                                @if($hasLocationFilter)
                                    <form action="{{ route('food.location.set') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="clear" value="1">
                                        <button type="submit" class="px-4 py-2 grail-btn-obsidian text-xs font-bold cursor-pointer">
                                            Show All Locations
                                        </button>
                                    </form>
                                @endif
                                @if($hasActiveFilters)
                                    <a href="{{ route('food.index') }}" class="px-4 py-2 rounded-full bg-white border border-black/15 text-xs font-bold text-[#1A1018] hover:bg-[#F6F6F6]">
                                        Reset Filters
                                    </a>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            <!-- 4. RESTAURANTS & CLOUD KITCHENS -->
            <section id="restaurants" class="space-y-5 pt-2 border-t border-black/8" data-grail-reveal>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <span class="grail-eyebrow !text-[#623562] block mb-0.5">Browse by Kitchen</span>
                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap" style="font-family: var(--font-grail-display);">
                            <span>Restaurants &amp; Cloud Kitchens</span>
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-sans font-bold">
                                {{ $restaurants->total() }} {{ Str::plural('Kitchen', $restaurants->total()) }}
                            </span>
                        </h2>
                    </div>
                </div>

                <!-- Restaurants Cards Grid / Honest Unserviceable Empty State -->
                @if($restaurants->isEmpty())
                    <div class="grail-card p-10 text-center max-w-xl mx-auto space-y-4">
                        <span class="text-4xl block">{{ $hasLocationFilter ? '📍' : '🔍' }}</span>
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
                                $cuisines = $rest->cuisine_list ?? [];
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
                                    <div class="relative aspect-[16/9] w-full overflow-hidden bg-[#0D0D0D]">
                                        <img src="{{ $rCover }}" alt="{{ $rest->shop_name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 {{ !$acceptingOrders ? 'grayscale-[45%]' : '' }}">
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-black/20"></div>
                                        
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

                                        <div class="absolute bottom-3 left-3 right-3 text-white z-10 flex items-end justify-between gap-3">
                                            <div class="min-w-0">
                                                <div class="text-[11px] font-black tracking-wider uppercase text-[#ECD2F3] flex items-center gap-2 mb-0.5">
                                                    <span>Prep: ~{{ $prepTime }} mins</span>
                                                    <span>•</span>
                                                    <span>{{ $modeLabel }}</span>
                                                </div>
                                                <h3 class="text-xl font-black truncate text-white drop-shadow-md tracking-tight">{{ $rest->shop_name }}</h3>
                                            </div>
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

                                    <div class="p-5 space-y-3">
                                        @if(!empty($cuisines))
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-[#1A1018]">
                                                <span class="text-[#C9A24A]">🍲</span>
                                                <span class="truncate">{{ is_array($cuisines) ? implode(' • ', $cuisines) : $cuisines }}</span>
                                            </div>
                                        @endif

                                        @if($rest->description)
                                            <p class="text-xs text-[#1A1018]/65 line-clamp-2 leading-relaxed">
                                                {{ $rest->description }}
                                            </p>
                                        @endif

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

                                <div class="p-5 pt-0">
                                    <a href="{{ route('food.restaurant', $rest->slug) }}" 
                                       class="w-full block py-3 grail-btn-obsidian text-center text-xs font-bold shadow-xs cursor-pointer">
                                        {{ $acceptingOrders ? 'View Menu & Order ›' : 'Browse Menu ›' }}
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if(method_exists($restaurants, 'links'))
                        <div class="pt-4">
                            {{ $restaurants->links() }}
                        </div>
                    @endif
                @endif
            </section>

            <!-- 5. GENUINE ACTIVE RESTAURANT OFFERS & COUPON CARDS (Zero Fake Coupons) -->
            @if($dealsCollection->isNotEmpty())
                <section id="deals" class="space-y-5 pt-2 border-t border-black/8" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">Active Restaurant Promotions</span>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap" style="font-family: var(--font-grail-display);">
                                <span>Live Kitchen Offers &amp; Promo Codes</span>
                                <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-sans font-bold uppercase tracking-wider">{{ $dealsCollection->count() }} Active</span>
                            </h2>
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

            <!-- 6. RECENT ORDERS & 1-CLICK REORDER STRIP (When user has food order history) -->
            @if(isset($recentFoodOrders) && $recentFoodOrders->isNotEmpty())
                <section class="space-y-4 pt-2 border-t border-black/8" data-grail-reveal>
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="grail-eyebrow !text-[#623562] block mb-1">1-Tap Repeat</span>
                            <h2 class="text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                Order Again
                            </h2>
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

            <!-- 7. RESTAURANT PARTNER ONBOARDING BANNER -->
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
