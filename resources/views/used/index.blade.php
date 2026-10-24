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
        filterModal: false,
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
    }" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 text-slate-900 font-sans selection:bg-blue-600 selection:text-white">

        <!-- ===================================================================
             1. BREADCRUMBS & TOP METRICS STRIP (BLUE AND WHITE)
             =================================================================== -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-4 mb-5 border-b border-blue-100">
            <!-- Breadcrumbs -->
            <nav class="flex items-center gap-2 text-xs font-semibold text-slate-500" aria-label="Breadcrumb">
                <a href="{{ url('/') }}" class="hover:text-blue-600 transition flex items-center gap-1">
                    <span>🏠</span>
                    <span>K71 Market</span>
                </a>
                <span class="text-blue-300">/</span>
                <a href="{{ route('used.index') }}" class="text-blue-700 font-bold hover:underline flex items-center gap-1">
                    <span>🔄</span>
                    <span>Used &amp; Pre-Owned</span>
                </a>
                @if(!empty($selectedCategory) && $selectedCategory !== 'all')
                    <span class="text-blue-300">/</span>
                    <span class="text-slate-800 font-bold capitalize">{{ $selectedCategory }}</span>
                @endif
                @if(!empty($condition) && $condition !== 'all')
                    <span class="text-blue-300">/</span>
                    <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 text-[10px] font-black uppercase">{{ $condition }}</span>
                @endif
            </nav>

            <!-- Status Indicator & Live Classified Count -->
            <div class="flex items-center gap-4 text-xs font-bold">
                <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 border border-blue-200/80 text-blue-900">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ $items->total() }} Active Listings in {{ explode(',', $currentCity)[0] }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" 
                            @click="activeTab = 'explore'; window.location.hash = 'explore';" 
                            :class="activeTab === 'explore' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200'"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-black transition cursor-pointer">
                        🔍 Explore Ads
                    </button>
                    @auth
                        <button type="button" 
                                @click="activeTab = 'myads'; window.location.hash = 'my-ads';" 
                                :class="activeTab === 'myads' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-blue-50 border border-slate-200'"
                                class="px-3.5 py-1.5 rounded-xl text-xs font-black transition cursor-pointer flex items-center gap-1.5">
                            <span>📦 My Ads</span>
                            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $myAds->count() > 0 ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $myAds->count() }}</span>
                        </button>
                    @endauth
                </div>
            </div>
        </div>

        <!-- ===================================================================
             2. HERO BANNER: OLX-STYLE BLUE & WHITE DISCOVERY
             =================================================================== -->
        <div x-show="activeTab === 'explore'" class="mb-8">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 text-white p-6 sm:p-8 lg:p-10 shadow-xl shadow-blue-950/20 border border-blue-700/50">
                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="space-y-3 max-w-2xl">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/20 text-blue-200 text-xs font-black uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span>Direct Buyer-to-Seller Deals • 0% Commission</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black tracking-tight leading-tight">
                            Buy &amp; Sell Pre-Owned Items with <br class="hidden sm:inline">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-200 via-white to-cyan-200">
                                Direct Negotiation in {{ explode(',', $currentCity)[0] }}
                            </span>
                        </h1>
                        <p class="text-xs sm:text-sm text-blue-100 font-medium leading-relaxed max-w-xl">
                            Verified electronics, iPhones, commercial vehicles, cars, and home furniture. Make an offer or chat directly with sellers across Rajasthan &amp; India.
                        </p>
                        
                        <!-- Action CTA Bar -->
                        <div class="pt-2 flex flex-wrap items-center gap-3">
                            <button type="button" 
                                    @click="$dispatch('open-post-ad')"
                                    class="px-5 py-3 rounded-2xl bg-white hover:bg-blue-50 text-blue-900 font-black text-xs sm:text-sm shadow-lg shadow-black/10 active:scale-95 transition flex items-center gap-2 cursor-pointer">
                                <span class="text-base">📢</span>
                                <span>+ Post Free Ad Now</span>
                            </button>
                            <a href="#browse-listings" 
                               class="px-5 py-3 rounded-2xl bg-white/15 hover:bg-white/25 border border-white/20 text-white font-bold text-xs sm:text-sm transition flex items-center gap-1.5">
                                <span>Browse {{ $items->total() }} Ads</span>
                                <span>↓</span>
                            </a>
                        </div>
                    </div>

                    <!-- Right Visual Card: Live Sample Ad Preview -->
                    <div class="hidden lg:flex shrink-0">
                        <div class="w-72 bg-white/10 backdrop-blur-md border border-white/20 rounded-3xl p-4 shadow-2xl space-y-3">
                            <div class="flex items-center justify-between text-xs font-black">
                                <span class="px-2 py-0.5 rounded-lg bg-emerald-500 text-white text-[10px]">VERIFIED AD</span>
                                <span class="text-blue-200 text-[10px]">Just now</span>
                            </div>
                            <div class="h-36 rounded-2xl overflow-hidden bg-slate-800">
                                <img src="https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80" 
                                     alt="iPhone Preview" 
                                     class="w-full h-full object-cover">
                            </div>
                            <div>
                                <div class="text-sm font-black text-white">Apple iPhone 14 Pro 128GB</div>
                                <div class="text-xs text-blue-200 mt-0.5">₹ 64,000 • <span class="text-emerald-300 font-bold">Negotiable</span></div>
                                <div class="text-[10px] text-blue-300 mt-1">📍 Madanganj, Kishangarh</div>
                            </div>
                            <div class="pt-1">
                                <button type="button" 
                                        @click="$dispatch('open-offer-modal', { id: 1, title: 'Apple iPhone 14 Pro 128GB', price: 64000, image: 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80' })"
                                        class="w-full py-2 rounded-xl bg-blue-500 hover:bg-blue-400 text-white font-black text-xs text-center transition">
                                    🤝 Make an Offer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Abstract Decorative Blue Highlights -->
                <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-blue-500/20 blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
            </div>
        </div>

        <!-- ===================================================================
             3. 8 POPULAR CATEGORY CARDS (BLUE & WHITE ACCENTS)
             =================================================================== -->
        <section x-show="activeTab === 'explore'" class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                        Browse by Category
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">
                        Explore verified pre-owned listings across Kishangarh &amp; India
                    </p>
                </div>
                @if(!empty($selectedCategory) && $selectedCategory !== 'all')
                    <a href="{{ route('used.index') }}" class="text-xs font-bold text-blue-700 hover:underline flex items-center gap-1">
                        <span>✕ Clear Category Filter</span>
                    </a>
                @endif
            </div>

            <!-- Category Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                
                <!-- 1. Mobiles & Tablets -->
                <a href="{{ route('used.index', ['cat' => 'mobiles']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'mobiles' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        📱
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Mobiles
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">iPhones, 5G</span>
                </a>

                <!-- 2. Laptops & Electronics -->
                <a href="{{ route('used.index', ['cat' => 'electronics']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'electronics' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        💻
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Electronics
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">Laptops, Audio</span>
                </a>

                <!-- 3. Cars & Vehicles -->
                <a href="{{ route('used.index', ['cat' => 'vehicles']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'vehicles' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        🚗
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Vehicles
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">Cars, SUVs</span>
                </a>

                <!-- 4. Motorcycles & Scooters -->
                <a href="{{ route('used.index', ['cat' => 'bikes']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'bikes' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        🏍️
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Bikes
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">Bullet, Scooty</span>
                </a>

                <!-- 5. Furniture & Decor -->
                <a href="{{ route('used.index', ['cat' => 'furniture']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'furniture' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        🛋️
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Furniture
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">Sofa, Beds</span>
                </a>

                <!-- 6. Home Appliances -->
                <a href="{{ route('used.index', ['cat' => 'appliances']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'appliances' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        📺
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Appliances
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">TV, Fridge, AC</span>
                </a>

                <!-- 7. Watches & Fashion -->
                <a href="{{ route('used.index', ['cat' => 'fashion']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'fashion' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        ⌚
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Watches
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">Smartwatches</span>
                </a>

                <!-- 8. Commercial & Machinery -->
                <a href="{{ route('used.index', ['cat' => 'commercial']) }}" 
                   class="group p-3.5 rounded-2xl bg-white border {{ $selectedCategory === 'commercial' ? 'border-blue-600 ring-2 ring-blue-600/20 shadow-md' : 'border-slate-200/90 hover:border-blue-400 hover:shadow-md' }} text-center transition flex flex-col items-center">
                    <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-700 group-hover:scale-110 group-hover:bg-blue-600 group-hover:text-white transition flex items-center justify-center text-xl shadow-xs">
                        🚜
                    </div>
                    <span class="mt-2 text-xs font-black text-slate-900 group-hover:text-blue-700 transition">
                        Commercial
                    </span>
                    <span class="text-[10px] text-slate-400 font-medium mt-0.5">Trucks, Tools</span>
                </a>

            </div>
        </section>

        <!-- ===================================================================
             4. PROMOTED & FEATURED PICKS (OLX VERIFIED FINDS)
             =================================================================== -->
        @if($recommendedItems->isNotEmpty() && $items->currentPage() === 1)
            <section x-show="activeTab === 'explore'" class="mb-8">
                <div class="flex items-center justify-between mb-3.5">
                    <div class="flex items-center gap-2">
                        <span class="text-blue-600 font-black text-base">★</span>
                        <h2 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                            Promoted &amp; Top Value Finds
                        </h2>
                        <span class="px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 text-[10px] font-black uppercase">
                            Verified
                        </span>
                    </div>
                    <span class="text-xs text-slate-400 font-medium">Curated Deals</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                    @foreach($recommendedItems as $rec)
                        @php
                            $recImg = $rec->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=400&q=80';
                            $recLoc = $rec->custom_fields['location'] ?? ($rec->vendor->city ?? 'Kishangarh');
                            $recCond = $rec->custom_fields['condition'] ?? 'Like New';
                        @endphp
                        <div class="bg-white rounded-2xl border border-blue-200/90 shadow-sm hover:shadow-md hover:border-blue-500 transition-all flex flex-col justify-between overflow-hidden group">
                            <a href="{{ route('used.show', $rec->slug) }}" class="block">
                                <div class="relative aspect-square bg-slate-100 overflow-hidden">
                                    <img src="{{ $recImg }}" alt="{{ $rec->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" loading="lazy">
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-blue-600 text-white font-black text-[9px] uppercase tracking-wider shadow-xs">
                                        FEATURED
                                    </span>
                                </div>
                                <div class="p-3">
                                    <div class="text-sm font-black text-blue-900">
                                        ₹ {{ number_format($rec->base_price / 100) }}
                                    </div>
                                    <div class="text-xs font-bold text-slate-800 line-clamp-1 mt-0.5">
                                        {{ $rec->title }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 truncate mt-1 flex items-center gap-1">
                                        <span>📍</span>
                                        <span>{{ explode(',', $recLoc)[0] }}</span>
                                    </div>
                                </div>
                            </a>
                            <div class="p-3 pt-0">
                                <button type="button" 
                                        @click="$dispatch('open-offer-modal', { id: {{ $rec->id }}, title: '{{ addslashes($rec->title) }}', price: {{ $rec->base_price / 100 }}, image: '{{ $recImg }}' })"
                                        class="w-full py-1.5 rounded-xl bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-[11px] font-black transition cursor-pointer">
                                    🤝 Offer Price
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- ===================================================================
             5. INTERACTIVE FILTER & SEARCH CONTROL STRIP
             =================================================================== -->
        <section id="browse-listings" x-show="activeTab === 'explore'" class="mb-6">
            <div class="bg-white p-4 rounded-3xl border border-blue-100 shadow-sm space-y-3">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                    
                    <!-- Condition Filter Pills (Tested by UsedItemsAndOffersTest) -->
                    <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 md:pb-0">
                        <span class="text-xs font-black uppercase text-slate-400 mr-1 shrink-0">Condition:</span>
                        
                        <button type="button" 
                                @click="applyCondition('all')"
                                :class="selectedCondition === 'all' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition cursor-pointer shrink-0">
                            All Conditions
                        </button>

                        <button type="button" 
                                @click="applyCondition('Brand New')"
                                :class="selectedCondition === 'Brand New' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition cursor-pointer shrink-0">
                            Brand New
                        </button>

                        <button type="button" 
                                @click="applyCondition('Like New')"
                                :class="selectedCondition === 'Like New' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition cursor-pointer shrink-0">
                            Like New
                        </button>

                        <button type="button" 
                                @click="applyCondition('Good Condition')"
                                :class="selectedCondition === 'Good Condition' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition cursor-pointer shrink-0">
                            Good Condition
                        </button>

                        <button type="button" 
                                @click="applyCondition('Fair / Working')"
                                :class="selectedCondition === 'Fair / Working' ? 'bg-blue-600 text-white shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-blue-50'"
                                class="px-3 py-1.5 rounded-xl text-xs font-extrabold transition cursor-pointer shrink-0">
                            Fair / Working
                        </button>
                    </div>

                    <!-- Right Controls: Price Budget + Sorting Dropdown -->
                    <div class="flex items-center gap-2.5 shrink-0">
                        <!-- Quick Budget Dropdown -->
                        <div class="relative">
                            <select @change="setBudget($event.target.value)" 
                                    class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600 cursor-pointer">
                                <option value="all" {{ empty($maxPrice) ? 'selected' : '' }}>💰 Any Budget</option>
                                <option value="10000" {{ ($maxPrice ?? '') == '10000' ? 'selected' : '' }}>Under ₹10,000</option>
                                <option value="30000" {{ ($maxPrice ?? '') == '30000' ? 'selected' : '' }}>Under ₹30,000</option>
                                <option value="60000" {{ ($maxPrice ?? '') == '60000' ? 'selected' : '' }}>Under ₹60,000</option>
                                <option value="100000" {{ ($maxPrice ?? '') == '100000' ? 'selected' : '' }}>Under ₹1,00,000</option>
                            </select>
                        </div>

                        <!-- Sort Dropdown -->
                        <div class="relative">
                            <select @change="applySort($event.target.value)" 
                                    class="px-3 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-bold text-slate-800 focus:outline-none focus:border-blue-600 cursor-pointer">
                                <option value="newest" {{ ($sort ?? '') == 'newest' ? 'selected' : '' }}>⚡ Newest Listed</option>
                                <option value="price_low" {{ ($sort ?? '') == 'price_low' ? 'selected' : '' }}>🏷️ Price: Low to High</option>
                                <option value="price_high" {{ ($sort ?? '') == 'price_high' ? 'selected' : '' }}>💎 Price: High to Low</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Info Tag -->
                @if(!empty($search) || !empty($selectedCategory) || !empty($condition) || !empty($maxPrice))
                    <div class="pt-2 border-t border-slate-100 flex flex-wrap items-center gap-2 text-xs">
                        <span class="text-slate-400 font-bold">Active Filters:</span>
                        @if(!empty($search))
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold">Search: "{{ $search }}"</span>
                        @endif
                        @if(!empty($selectedCategory) && $selectedCategory !== 'all')
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold capitalize">Category: {{ $selectedCategory }}</span>
                        @endif
                        @if(!empty($condition) && $condition !== 'all')
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold">Condition: {{ $condition }}</span>
                        @endif
                        @if(!empty($maxPrice))
                            <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 font-bold">Budget &le; ₹{{ number_format($maxPrice) }}</span>
                        @endif
                        <a href="{{ route('used.index') }}" class="text-rose-600 font-bold hover:underline ml-1">Reset All</a>
                    </div>
                @endif
            </div>
        </section>

        <!-- ===================================================================
             6. ALL ADS RESPONSIVE GRID (OLX BLUE & WHITE CARD FORMAT)
             =================================================================== -->
        <section x-show="activeTab === 'explore'" class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <h3 class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                        Fresh Recommendations
                    </h3>
                    <span class="text-xs text-slate-500 font-medium">({{ $items->total() }} Ads available)</span>
                </div>
            </div>

            @if($items->isEmpty())
                <!-- Clean Empty State -->
                <div class="bg-white rounded-3xl border border-blue-100 p-8 sm:p-12 text-center max-w-lg mx-auto shadow-sm space-y-4">
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-3xl mx-auto">
                        🔍
                    </div>
                    <h4 class="text-base font-black text-slate-900">No Listings Match Your Search</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        We couldn't find any pre-owned ads matching your exact filters in {{ $currentCity }}. Try removing filters or be the first to post!
                    </p>
                    <div class="flex items-center justify-center gap-3 pt-2">
                        <a href="{{ route('used.index') }}" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold transition">
                            Reset Filters
                        </a>
                        <button type="button" @click="$dispatch('open-post-ad')" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition">
                            + Post Free Ad
                        </button>
                    </div>
                </div>
            @else
                <!-- Responsive Listing Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                    @foreach($items as $item)
                        @php
                            $firstImg = $item->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=600&q=80';
                            $custom = $item->custom_fields ?? [];
                            $badge = $custom['badge'] ?? ($item->is_featured ? 'FEATURED' : null);
                            $location = $custom['location'] ?? ($item->vendor->city ?? 'Kishangarh, Rajasthan');
                            $itemCond = $custom['condition'] ?? ($custom['specs']['Condition'] ?? 'Good Condition');
                            $vendorPhone = $item->vendor->phone ?? '+919549311580';
                            $isNegotiable = $item->is_negotiable ?? true;
                        @endphp

                        <div class="bg-white rounded-2xl border border-slate-200/90 hover:border-blue-500 hover:shadow-xl transition-all duration-200 flex flex-col justify-between overflow-hidden group relative">
                            
                            <!-- Top Image Container with Badges & Wishlist -->
                            <div class="relative w-full aspect-[4/3] bg-slate-100 overflow-hidden">
                                <a href="{{ route('used.show', $item->slug) }}" class="block w-full h-full">
                                    <img src="{{ $firstImg }}" 
                                         alt="{{ $item->title }}" 
                                         loading="lazy" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                </a>

                                <!-- Promoted / Elite Badge -->
                                @if($badge)
                                    <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-blue-600 text-white font-black text-[9px] uppercase tracking-wider shadow-sm z-10">
                                        {{ $badge }}
                                    </span>
                                @endif

                                <!-- Condition Tag (Top Right or Bottom Left) -->
                                <span class="absolute bottom-2 left-2 px-2 py-0.5 rounded-md bg-slate-900/80 backdrop-blur-xs text-white font-black text-[9px] uppercase tracking-wider z-10">
                                    {{ $itemCond }}
                                </span>

                                <!-- Wishlist Heart Button -->
                                <button type="button" 
                                        @click="toggleWishlist({{ $item->id }})"
                                        class="absolute top-2 right-2 z-20 w-8 h-8 rounded-full bg-white/90 backdrop-blur-md flex items-center justify-center text-slate-600 hover:text-rose-500 shadow-md transition cursor-pointer"
                                        aria-label="Add to favorites">
                                    <svg class="w-4 h-4" :class="isWishlisted({{ $item->id }}) ? 'fill-rose-500 text-rose-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Card Body -->
                            <div class="p-3 sm:p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <!-- Price + Negotiable Pill -->
                                    <div class="flex items-center justify-between gap-1">
                                        <div class="text-base sm:text-lg font-black text-slate-900 tracking-tight">
                                            ₹ {{ number_format($item->base_price / 100) }}
                                        </div>
                                        @if($isNegotiable)
                                            <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
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

                                    <!-- Location & Date -->
                                    <div class="mt-2 text-[10px] text-slate-500 flex items-center justify-between">
                                        <span class="truncate">📍 {{ explode(',', $location)[0] }}</span>
                                        <span class="shrink-0 text-slate-400">{{ $item->created_at ? $item->created_at->diffForHumans(null, true) : '2d' }}</span>
                                    </div>
                                </div>

                                <!-- Action Buttons: Make Offer + Call -->
                                <div class="mt-3 pt-2.5 border-t border-slate-100 grid grid-cols-2 gap-2">
                                    <button type="button" 
                                            @click="$dispatch('open-offer-modal', { id: {{ $item->id }}, title: '{{ addslashes($item->title) }}', price: {{ $item->base_price / 100 }}, image: '{{ $firstImg }}' })"
                                            class="py-1.5 px-2 rounded-xl bg-blue-50 hover:bg-blue-600 hover:text-white text-blue-700 text-[11px] font-black transition flex items-center justify-center gap-1 cursor-pointer">
                                        <span>🤝</span>
                                        <span>Offer</span>
                                    </button>

                                    <a href="{{ route('used.show', $item->slug) }}" 
                                       class="py-1.5 px-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-[11px] font-black transition text-center">
                                        View Ad
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
             7. USER'S SELLER DASHBOARD: "MY ADS" SECTION
             =================================================================== -->
        <section x-show="activeTab === 'myads'" class="mb-10">
            <div class="bg-white rounded-3xl border border-blue-100 p-6 sm:p-8 shadow-sm">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xl">📦</span>
                            <h2 class="text-lg font-black text-slate-900">My Posted Classified Ads</h2>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">
                            Manage your live listings, mark products as sold, or review buyer negotiation offers.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('vendor.offers.index') }}" class="px-4 py-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold transition flex items-center gap-1.5">
                            <span>💰 Received Offers</span>
                        </a>
                        <button type="button" 
                                @click="$dispatch('open-post-ad')" 
                                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition flex items-center gap-1.5">
                            <span>+ Post New Ad</span>
                        </button>
                    </div>
                </div>

                <!-- Listings Table / Cards -->
                @if($myAds->isEmpty())
                    <div class="py-12 text-center max-w-sm mx-auto space-y-3">
                        <div class="text-4xl">🏷️</div>
                        <h4 class="text-sm font-black text-slate-800">You haven't posted any classified ads yet</h4>
                        <p class="text-xs text-slate-500">
                            Got old gadgets, furniture, or a vehicle to sell? Reach thousands of active buyers across Rajasthan instantly.
                        </p>
                        <button type="button" 
                                @click="$dispatch('open-post-ad')" 
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-black transition">
                            + Post Your First Free Ad
                        </button>
                    </div>
                @else
                    <div class="mt-6 space-y-3">
                        @foreach($myAds as $myAd)
                            @php
                                $adImg = $myAd->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=300&q=80';
                                $adCond = $myAd->custom_fields['condition'] ?? 'Good Condition';
                                $isSold = $myAd->is_sold ?? false;
                            @endphp
                            <div class="p-4 rounded-2xl border {{ $isSold ? 'border-slate-200 bg-slate-50 opacity-75' : 'border-blue-100 bg-white' }} flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-slate-100 shrink-0">
                                        <img src="{{ $adImg }}" alt="{{ $myAd->title }}" class="w-full h-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded-md text-[9px] font-black uppercase {{ $isSold ? 'bg-slate-300 text-slate-700' : 'bg-emerald-100 text-emerald-800' }}">
                                                {{ $isSold ? 'SOLD' : 'ACTIVE & LIVE' }}
                                            </span>
                                            <span class="text-[10px] text-slate-400 font-mono">ID: 1857{{ $myAd->id }}</span>
                                        </div>
                                        <h4 class="text-xs sm:text-sm font-black text-slate-900 truncate mt-1">
                                            <a href="{{ route('used.show', $myAd->slug) }}" class="hover:text-blue-600 transition">
                                                {{ $myAd->title }}
                                            </a>
                                        </h4>
                                        <div class="text-xs font-bold text-blue-700 mt-0.5">
                                            ₹ {{ number_format($myAd->base_price / 100) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center gap-2 shrink-0">
                                    <form action="{{ route('used.toggle-sold', $myAd->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-xs font-bold text-slate-700 transition">
                                            {{ $isSold ? 'Re-activate Ad' : 'Mark as Sold' }}
                                        </button>
                                    </form>

                                    <a href="{{ route('used.show', $myAd->slug) }}" class="px-3 py-1.5 rounded-xl bg-blue-50 hover:bg-blue-100 text-xs font-bold text-blue-700 transition">
                                        View
                                    </a>

                                    <form action="{{ route('used.delete', $myAd->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this ad permanently?');">
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

        <!-- ===================================================================
             8. TRUST, SAFETY & VERIFICATION INFORMATION (BLUE & WHITE)
             =================================================================== -->
        <section x-show="activeTab === 'explore'" class="mb-12">
            <div class="rounded-3xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200/80 p-6 sm:p-8">
                <div class="max-w-3xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-600 text-white text-[10px] font-black uppercase tracking-wider mb-3">
                        <span>🛡️ Safety First</span>
                    </div>
                    <h3 class="text-lg sm:text-xl font-black text-slate-900 tracking-tight">
                        Safe Buying &amp; Selling Guidelines on K71 Used
                    </h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        To protect our community in Kishangarh, Ajmer, Jaipur and across India, please follow these core guidelines when transacting pre-owned items:
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
                    <div class="bg-white p-4 rounded-2xl border border-blue-100 shadow-xs space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                            1
                        </div>
                        <h4 class="text-xs font-black text-slate-900">Meet in Public Daytime Locations</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Always inspect items at busy public locations like Kishangarh Bus Stand, Shivaji Nagar market or City Centers.
                        </p>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-blue-100 shadow-xs space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                            2
                        </div>
                        <h4 class="text-xs font-black text-slate-900">Thoroughly Inspect Before Paying</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Verify device serial numbers, battery health, original bill, box and working condition before releasing payment.
                        </p>
                    </div>

                    <div class="bg-white p-4 rounded-2xl border border-blue-100 shadow-xs space-y-1.5">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">
                            3
                        </div>
                        <h4 class="text-xs font-black text-slate-900">Never Transfer Advance Money</h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            Legitimate sellers never ask for advance booking fees or fake QR code scans. Deal hand-to-hand in person.
                        </p>
                    </div>
                </div>
            </div>
        </section>

    </div>
</x-layouts.used>
