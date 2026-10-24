<x-layouts.used 
    title="K71 Used — Pre-Owned & Second-Hand Marketplace | Kishangarh & India"
    metaDescription="Buy and sell verified second-hand cars, iPhones, laptops, furniture, appliances, and bikes with direct negotiation in Kishangarh, Ajmer & Rajasthan on K71 Used."
>
    @php
        $currentCity = $city ?? 'Kishangarh, Rajasthan';
        $currentSort = $sort ?? 'newest';
        $currentCond = $condition ?? 'all';
        $currentCat = $selectedCategory ?? 'all';
    @endphp

    <div x-data="{
        activeTab: window.location.hash === '#my-ads' ? 'myads' : 'explore',
        selectedCondition: '{{ $currentCond }}',
        activeCity: '{{ $currentCity }}',
        activeSort: '{{ $currentSort }}',
        quickBudget: '{{ !empty($maxPrice) ? $maxPrice : 'all' }}',
        
        applyCondition(cond) {
            let url = new URL(window.location.href);
            if (cond === 'all') {
                url.searchParams.delete('condition');
            } else {
                url.searchParams.set('condition', cond);
            }
            window.location.href = url.toString();
        },

        applyCategory(catSlug) {
            let url = new URL(window.location.href);
            if (catSlug === 'all') {
                url.searchParams.delete('cat');
            } else {
                url.searchParams.set('cat', catSlug);
            }
            window.location.href = url.toString();
        },

        applySort(sortVal) {
            let url = new URL(window.location.href);
            url.searchParams.set('sort', sortVal);
            window.location.href = url.toString();
        },

        setBudget(max) {
            let url = new URL(window.location.href);
            if (max === 'all') {
                url.searchParams.delete('max_price');
            } else {
                url.searchParams.set('max_price', max);
            }
            window.location.href = url.toString();
        }
    }" class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6 text-slate-900 font-sans selection:bg-blue-600 selection:text-white">

        <!-- ===================================================================
             1. CATEGORY PILLS BAR (OLX STYLE HORIZONTAL SCROLL)
             =================================================================== -->
        @php
            $catFilters = [
                'all' => ['name' => 'All Items', 'icon' => '🏷️'],
                'mobiles' => ['name' => 'Mobiles & Tablets', 'icon' => '📱'],
                'vehicles' => ['name' => 'Cars & Vehicles', 'icon' => '🚗'],
                'bikes' => ['name' => 'Bikes & Scooters', 'icon' => '🏍️'],
                'electronics' => ['name' => 'Laptops & Electronics', 'icon' => '💻'],
                'furniture' => ['name' => 'Furniture & Decor', 'icon' => '🛋️'],
                'appliances' => ['name' => 'Appliances & TV', 'icon' => '📺'],
                'commercial' => ['name' => 'Commercial & Industrial', 'icon' => '🚜'],
            ];
        @endphp

        <div class="mb-5 pb-2 overflow-x-auto no-scrollbar -mx-3 px-3 sm:mx-0 sm:px-0">
            <div class="flex items-center gap-2 min-w-max">
                @foreach($catFilters as $catKey => $catData)
                    @php
                        $isCatActive = ($catKey === 'all' && (empty($currentCat) || $currentCat === 'all')) || ($currentCat === $catKey);
                    @endphp
                    <button type="button" 
                            @click="applyCategory('{{ $catKey }}')"
                            class="px-3.5 py-2 rounded-full text-xs font-bold transition cursor-pointer flex items-center gap-1.5 shrink-0 {{ $isCatActive ? 'bg-blue-600 text-white shadow-sm' : 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-200' }}">
                        <span>{{ $catData['icon'] }}</span>
                        <span>{{ $catData['name'] }}</span>
                        @if($catKey === 'all')
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $isCatActive ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $items->total() }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- ===================================================================
             2. CLEAN TOOLBAR: LOCATION CHIP + CONDITION TABS + SORT
             =================================================================== -->
        <div class="bg-white rounded-2xl border border-slate-200/90 p-3 sm:p-4 mb-6 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            
            <!-- Left: Condition Filter Pills (Tested by UsedItemsAndOffersTest) -->
            <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 md:pb-0">
                <span class="text-[11px] font-black uppercase text-slate-400 mr-1 shrink-0">Condition:</span>
                
                <button type="button" 
                        @click="applyCondition('all')"
                        :class="selectedCondition === 'all' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer shrink-0">
                    All
                </button>

                <button type="button" 
                        @click="applyCondition('Like New')"
                        :class="selectedCondition === 'Like New' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer shrink-0">
                    Like New
                </button>

                <button type="button" 
                        @click="applyCondition('Brand New')"
                        :class="selectedCondition === 'Brand New' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer shrink-0">
                    Brand New
                </button>

                <button type="button" 
                        @click="applyCondition('Good Condition')"
                        :class="selectedCondition === 'Good Condition' ? 'bg-blue-600 text-white' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                        class="px-3 py-1.5 rounded-xl text-xs font-bold transition cursor-pointer shrink-0">
                    Good Condition
                </button>
            </div>

            <!-- Right: Budget & Sorting Dropdowns -->
            <div class="flex items-center gap-2.5 shrink-0">
                <!-- Budget Filter -->
                <select @change="setBudget($event.target.value)" 
                        class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600 cursor-pointer">
                    <option value="all" {{ empty($maxPrice) ? 'selected' : '' }}>💰 All Budgets</option>
                    <option value="15000" {{ ($maxPrice ?? '') == '15000' ? 'selected' : '' }}>Under ₹15,000</option>
                    <option value="35000" {{ ($maxPrice ?? '') == '35000' ? 'selected' : '' }}>Under ₹35,000</option>
                    <option value="75000" {{ ($maxPrice ?? '') == '75000' ? 'selected' : '' }}>Under ₹75,000</option>
                    <option value="200000" {{ ($maxPrice ?? '') == '200000' ? 'selected' : '' }}>Under ₹2 Lakhs</option>
                </select>

                <!-- Sort Filter -->
                <select @change="applySort($event.target.value)" 
                        class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600 cursor-pointer">
                    <option value="newest" {{ ($sort ?? '') == 'newest' ? 'selected' : '' }}>⚡ Newest Listed</option>
                    <option value="price_low" {{ ($sort ?? '') == 'price_low' ? 'selected' : '' }}>🏷️ Price: Low to High</option>
                    <option value="price_high" {{ ($sort ?? '') == 'price_high' ? 'selected' : '' }}>💎 Price: High to Low</option>
                </select>
            </div>

        </div>

        <!-- ===================================================================
             3. SECTION TABS: "EXPLORE ADS" vs "MY ADS"
             =================================================================== -->
        <div class="flex items-center justify-between mb-4 pb-2 border-b border-slate-200/80">
            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="activeTab = 'explore'; window.location.hash = 'explore';" 
                        :class="activeTab === 'explore' ? 'text-blue-700 border-b-2 border-blue-600 font-black' : 'text-slate-500 font-bold hover:text-slate-900'"
                        class="pb-2 text-sm sm:text-base tracking-tight transition cursor-pointer flex items-center gap-1.5">
                    <span>Fresh Recommendations in {{ explode(',', $currentCity)[0] }}</span>
                    <span class="text-xs text-slate-400 font-normal">({{ $items->total() }})</span>
                </button>

                @auth
                    <button type="button" 
                            @click="activeTab = 'myads'; window.location.hash = 'my-ads';" 
                            :class="activeTab === 'myads' ? 'text-blue-700 border-b-2 border-blue-600 font-black' : 'text-slate-500 font-bold hover:text-slate-900'"
                            class="pb-2 text-sm sm:text-base tracking-tight transition cursor-pointer flex items-center gap-1.5">
                        <span>My Posted Ads</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $myAds->count() > 0 ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $myAds->count() }}</span>
                    </button>
                @endauth
            </div>

            <!-- Clear filters shortcut if active -->
            @if(!empty($search) || (!empty($selectedCategory) && $selectedCategory !== 'all') || (!empty($condition) && $condition !== 'all') || !empty($maxPrice))
                <a href="{{ route('used.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                    ✕ Reset All Filters
                </a>
            @endif
        </div>

        <!-- ===================================================================
             4. ALL ADS RESPONSIVE GRID (OLX BLUE & WHITE CARD FORMAT)
             =================================================================== -->
        <section x-show="activeTab === 'explore'">
            @if($items->isEmpty())
                <!-- Clean Empty State -->
                <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 text-center max-w-md mx-auto my-8 shadow-xs space-y-3">
                    <div class="text-4xl">🔍</div>
                    <h3 class="text-base font-black text-slate-900">No Listings Found</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        No pre-owned items currently match your chosen filters in {{ $currentCity }}.
                    </p>
                    <div class="pt-2 flex items-center justify-center gap-3">
                        <a href="{{ route('used.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                            Reset Filters
                        </a>
                        <button type="button" @click="$dispatch('open-post-ad')" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition">
                            + Post Free Ad
                        </button>
                    </div>
                </div>
            @else
                <!-- Responsive Grid: 2 Columns on Mobile, 3 on Tablet, 4 on Desktop -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                    @foreach($items as $item)
                        @php
                            $firstImg = $item->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=600&q=80';
                            $custom = $item->custom_fields ?? [];
                            $badge = $custom['badge'] ?? ($item->is_featured ? 'FEATURED' : null);
                            $location = $custom['location'] ?? ($item->vendor->city ?? 'Kishangarh, Rajasthan');
                            $itemCond = $custom['condition'] ?? ($custom['specs']['Condition'] ?? 'Good Condition');
                            $isNegotiable = $item->is_negotiable ?? true;
                        @endphp

                        <div class="bg-white rounded-2xl border border-slate-200/90 hover:border-blue-500 hover:shadow-lg transition-all duration-200 flex flex-col justify-between overflow-hidden group">
                            
                            <!-- Image Container -->
                            <div class="relative w-full aspect-[4/3] bg-slate-100 overflow-hidden">
                                <a href="{{ route('used.show', $item->slug) }}" class="block w-full h-full">
                                    <img src="{{ $firstImg }}" 
                                         alt="{{ $item->title }}" 
                                         loading="lazy" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </a>

                                <!-- Badges -->
                                @if($badge)
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-blue-600 text-white font-black text-[9px] uppercase tracking-wider shadow-xs z-10">
                                        {{ $badge }}
                                    </span>
                                @endif

                                <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md bg-slate-900/80 backdrop-blur-xs text-white font-black text-[9px] uppercase tracking-wider z-10">
                                    {{ $itemCond }}
                                </span>

                                <!-- Wishlist Heart Button -->
                                <button type="button" 
                                        @click="toggleWishlist({{ $item->id }})"
                                        class="absolute top-2 right-2 z-20 w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white/90 backdrop-blur-md flex items-center justify-center text-slate-600 hover:text-rose-500 shadow-sm transition cursor-pointer"
                                        aria-label="Add to favorites">
                                    <svg class="w-4 h-4" :class="isWishlisted({{ $item->id }}) ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Card Body -->
                            <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between">
                                <div>
                                    <!-- Price + Negotiable Pill -->
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="text-base sm:text-lg font-black text-slate-950 tracking-tight">
                                            ₹ {{ number_format($item->base_price / 100) }}
                                        </div>
                                        @if($isNegotiable)
                                            <span class="text-[9.5px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">
                                                Negotiable
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Title -->
                                    <a href="{{ route('used.show', $item->slug) }}" class="block mt-1">
                                        <h4 class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-blue-700 transition line-clamp-1">
                                            {{ $item->title }}
                                        </h4>
                                    </a>

                                    <!-- Location & Time -->
                                    <div class="mt-2 text-[10px] text-slate-500 flex items-center justify-between">
                                        <span class="truncate">📍 {{ explode(',', $location)[0] }}</span>
                                        <span class="shrink-0 text-slate-400">{{ $item->created_at ? $item->created_at->diffForHumans(null, true) : '2d' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-3 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-1.5">
                                    <button type="button" 
                                            @click="$dispatch('open-offer-modal', { id: {{ $item->id }}, title: '{{ addslashes($item->title) }}', price: {{ $item->base_price / 100 }}, image: '{{ $firstImg }}' })"
                                            class="py-1.5 px-2 rounded-xl bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-[11px] font-black transition text-center cursor-pointer">
                                        🤝 Offer
                                    </button>

                                    <a href="{{ route('used.show', $item->slug) }}" 
                                       class="py-1.5 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-bold transition text-center">
                                        View
                                    </a>
                                </div>

                            </div>

                        </div>
                    @endforeach
                </div>

                <!-- Pagination Links -->
                <div class="mt-8">
                    {{ $items->links() }}
                </div>
            @endif
        </section>

        <!-- ===================================================================
             5. USER DASHBOARD: "MY ADS" SECTION
             =================================================================== -->
        <section x-show="activeTab === 'myads'">
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 shadow-xs">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-black text-slate-900">My Posted Classified Ads</h3>
                        <p class="text-xs text-slate-500">Manage your listings, mark items as sold, or view offers.</p>
                    </div>
                    <button type="button" 
                            @click="$dispatch('open-post-ad')" 
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition self-start sm:self-auto cursor-pointer">
                        + Post New Ad
                    </button>
                </div>

                @if($myAds->isEmpty())
                    <div class="py-12 text-center max-w-sm mx-auto space-y-3">
                        <div class="text-4xl">📦</div>
                        <h4 class="text-sm font-black text-slate-800">You haven't posted any ads yet</h4>
                        <p class="text-xs text-slate-500">Have an old iPhone, bike, or furniture to sell? Post your ad with real photos now.</p>
                        <button type="button" @click="$dispatch('open-post-ad')" class="px-4 py-2 rounded-xl bg-blue-600 text-white text-xs font-black">
                            + Post Free Ad
                        </button>
                    </div>
                @else
                    <div class="mt-4 space-y-3">
                        @foreach($myAds as $myAd)
                            @php
                                $adImg = $myAd->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=300&q=80';
                                $isSold = $myAd->is_sold ?? false;
                            @endphp
                            <div class="p-3 sm:p-4 rounded-xl border {{ $isSold ? 'border-slate-200 bg-slate-50 opacity-75' : 'border-slate-200 bg-white' }} flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-100 shrink-0">
                                        <img src="{{ $adImg }}" alt="{{ $myAd->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase {{ $isSold ? 'bg-slate-300 text-slate-700' : 'bg-emerald-100 text-emerald-800' }}">
                                            {{ $isSold ? 'SOLD' : 'ACTIVE' }}
                                        </span>
                                        <h4 class="text-xs sm:text-sm font-black text-slate-900 truncate mt-0.5">
                                            {{ $myAd->title }}
                                        </h4>
                                        <div class="text-xs font-bold text-blue-700">
                                            ₹ {{ number_format($myAd->base_price / 100) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <form action="{{ route('used.toggle-sold', $myAd->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                                            {{ $isSold ? 'Re-activate' : 'Mark as Sold' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('used.show', $myAd->slug) }}" class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-xs font-bold text-blue-700 transition">
                                        View
                                    </a>

                                    <form action="{{ route('used.delete', $myAd->id) }}" method="POST" onsubmit="return confirm('Permanently delete this listing?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-xs font-bold text-rose-600 transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

    </div>
</x-layouts.used>
