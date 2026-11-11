<x-layouts.used
    :title="!empty($city) ? 'K71 Used — Pre-Owned Marketplace in ' . $city : 'K71 Used — Second-Hand & Pre-Owned Marketplace'"
    metaDescription="Discover pre-owned phones, laptops, electronics, furniture, appliances, books and vehicles with transparent condition grading, genuine photos, and direct buyer-seller negotiation on K71 Used."
>
    @php
        $currentCity = $city ?? '';
        $currentSort = $sort ?? 'newest';
        $currentCond = $condition ?? '';
        $currentCat = $selectedCategory ?? '';
        $hasActiveFilters = !empty($search)
            || (!empty($currentCat) && $currentCat !== 'all')
            || (!empty($currentCond) && $currentCond !== 'all')
            || !empty($currentCity)
            || !empty($minPrice)
            || !empty($maxPrice)
            || !empty($pricingMode)
            || !empty($fulfillment)
            || !empty($posted)
            || !empty($refurbished)
            || !empty($showSold)
            || !empty($specFilters);
    @endphp

    <div x-data="{
        activeTab: window.location.hash === '#my-ads' ? 'myads' : 'explore',
        filtersOpen: {{ ($minPrice || $pricingMode || $fulfillment || $posted || $refurbished || $showSold || !empty($specFilters)) ? 'true' : 'false' }},
        updateParam(key, val) {
            let url = new URL(window.location.href);
            url.searchParams.delete('page');
            if (val === '' || val === null || val === 'all') {
                url.searchParams.delete(key);
            } else {
                url.searchParams.set(key, val);
            }
            window.location.href = url.toString();
        }
    }" class="bg-black text-[#EFE9F2] selection:bg-[#ECD2F3] selection:text-[#1A1018] w-full overflow-x-clip">

        <!-- ===================================================================
             1. DISCOVERY-FIRST GRAIL OBSIDIAN HERO BAND
             =================================================================== -->
        <section class="grail-hero-band pt-6 sm:pt-8 pb-6 px-3 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto space-y-5">
                <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
                    <div>
                        <span class="grail-eyebrow block mb-1">Direct Peer-to-Peer Classifieds</span>
                        <h1 class="text-2xl sm:text-4xl lg:text-5xl font-light text-white tracking-tight leading-[1.02]" style="font-family: var(--font-grail-display);">
                            @if(!empty($currentCity) && $currentCity !== 'All India')
                                Pre-Owned Goods in <span class="text-[#ECD2F3] italic">{{ explode(',', $currentCity)[0] }}</span>
                            @else
                                Pre-Owned <span class="text-[#ECD2F3] italic">Classifieds</span>
                            @endif
                        </h1>
                        <p class="text-xs sm:text-sm text-[#EFE9F2]/65 mt-1.5 max-w-2xl">
                            Transparent condition disclosures, genuine seller photos, price negotiation, and local handover across India.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <a href="{{ route('used.sell') }}" class="px-5 py-2.5 grail-cta-pill text-xs font-bold shadow-md">
                            + Sell an Item
                        </a>
                        @auth
                            <a href="{{ route('used.dashboard') }}" class="px-4 py-2.5 rounded-full bg-white/10 hover:bg-white/15 border border-white/15 text-white text-xs font-semibold transition">
                                Dashboard
                            </a>
                        @endauth
                    </div>
                </div>

                <!-- Primary Search + City Discovery Bar -->
                <form action="{{ route('used.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-2.5 bg-white/[0.06] border border-white/15 rounded-2xl p-2 sm:p-2.5">
                    @if(!empty($currentCat) && $currentCat !== 'all')
                        <input type="hidden" name="cat" value="{{ $currentCat }}">
                    @endif
                    @if(!empty($currentCond) && $currentCond !== 'all')
                        <input type="hidden" name="condition" value="{{ $currentCond }}">
                    @endif
                    <div class="md:col-span-6 flex items-center bg-black/50 border border-white/10 rounded-xl px-3">
                        <svg class="w-4 h-4 text-[#ECD2F3] shrink-0 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Search iPhone, MacBook, Bike, Sofa, Camera..."
                               class="w-full py-2.5 text-xs sm:text-sm bg-transparent text-white placeholder:text-white/40 focus:outline-none">
                    </div>
                    <div class="md:col-span-4 flex items-center bg-black/50 border border-white/10 rounded-xl px-3">
                        <span class="mr-2 text-xs">📍</span>
                        <select name="city" class="w-full py-2.5 text-xs sm:text-sm bg-transparent text-white focus:outline-none">
                            <option value="" class="bg-[#161616] text-white">All Cities (India)</option>
                            @foreach($availableCities as $cityOpt)
                                <option value="{{ $cityOpt }}" class="bg-[#161616] text-white" {{ strcasecmp($currentCity, $cityOpt) === 0 ? 'selected' : '' }}>
                                    {{ $cityOpt }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2 flex gap-2">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#ECD2F3] hover:bg-[#FFCDFF] text-[#1A1018] text-xs font-bold transition cursor-pointer">
                            Search Ads
                        </button>
                    </div>
                </form>

                <!-- Configurable Category Pills Bar -->
                <div class="pb-1 overflow-x-auto no-scrollbar">
                    <div class="flex items-center gap-2 min-w-max">
                        @php $isAllActive = empty($currentCat) || $currentCat === 'all'; @endphp
                        <button type="button"
                                @click="updateParam('cat', 'all')"
                                class="px-3.5 py-2 rounded-full text-xs font-bold transition cursor-pointer flex items-center gap-1.5 shrink-0 {{ $isAllActive ? 'bg-[#ECD2F3] text-[#1A1018] shadow-sm' : 'bg-white/[0.06] hover:bg-white/[0.12] text-[#EFE9F2]/85 border border-white/15' }}">
                            <span>🏷️</span>
                            <span>All Items</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $isAllActive ? 'bg-[#1A1018] text-[#ECD2F3]' : 'bg-white/15 text-white' }}">{{ $items->total() }}</span>
                        </button>

                        @foreach($parentCategories as $cat)
                            @php
                                $isCatActive = ($currentCat === $cat->slug)
                                    || ($activeCategoryObj && ($activeCategoryObj->id === $cat->id || $activeCategoryObj->parent_id === $cat->id));
                                $catIcon = config('used_marketplace.category_icons.' . $cat->slug) ?: '📦';
                            @endphp
                            <button type="button"
                                    @click="updateParam('cat', '{{ $cat->slug }}')"
                                    class="px-3.5 py-2 rounded-full text-xs font-bold transition cursor-pointer flex items-center gap-1.5 shrink-0 {{ $isCatActive ? 'bg-[#ECD2F3] text-[#1A1018] shadow-sm' : 'bg-white/[0.06] hover:bg-white/[0.12] text-[#EFE9F2]/85 border border-white/15' }}">
                                <span>{{ $catIcon }}</span>
                                <span>{{ $cat->name }}</span>
                                @if(($cat->active_items_count ?? 0) > 0)
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $isCatActive ? 'bg-[#1A1018] text-[#ECD2F3]' : 'bg-white/15 text-white' }}">{{ $cat->active_items_count }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Subcategory Pills if viewing a parent category or child subcategory -->
                @if($activeCategoryObj && ($activeCategoryObj->children->isNotEmpty() || $activeCategoryObj->parent))
                    @php
                        $subParent = $activeCategoryObj->parent ?? $activeCategoryObj;
                        $subChildren = $subParent->children->isNotEmpty() ? $subParent->children : \App\Models\Category::where('parent_id', $subParent->id)->where('is_active', true)->orderBy('sort_order')->get();
                    @endphp
                    @if($subChildren->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                            <span class="text-[#C9A24A] font-semibold uppercase tracking-[0.12em] mr-1">{{ $subParent->name }}:</span>
                            <button type="button" @click="updateParam('cat', '{{ $subParent->slug }}')"
                                    class="px-3 py-1 rounded-full border {{ $currentCat === $subParent->slug ? 'bg-[#ECD2F3] text-[#1A1018] border-[#ECD2F3] font-bold' : 'bg-black/40 text-white/80 border-white/15 hover:border-white/40' }} transition cursor-pointer">
                                All {{ $subParent->name }}
                            </button>
                            @foreach($subChildren as $subCat)
                                <button type="button" @click="updateParam('cat', '{{ $subCat->slug }}')"
                                        class="px-3 py-1 rounded-full border {{ $currentCat === $subCat->slug ? 'bg-[#ECD2F3] text-[#1A1018] border-[#ECD2F3] font-bold' : 'bg-black/40 text-white/80 border-white/15 hover:border-white/40' }} transition cursor-pointer">
                                    {{ $subCat->name }}
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif

                <!-- Popular Categories Strip & Saved Searches Shortcuts -->
                @if($popularCategories->isNotEmpty() || ($savedSearches->isNotEmpty() && auth()->check()) || $hasActiveFilters)
                    <div class="pt-2 border-t border-white/10 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                        @if($popularCategories->isNotEmpty())
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="text-white/50 font-semibold">Active categories:</span>
                                @foreach($popularCategories->take(4) as $pop)
                                    <button type="button" @click="updateParam('cat', '{{ $pop->slug }}')" class="px-2.5 py-0.5 rounded-full bg-white/5 hover:bg-white/15 text-[#ECD2F3] transition cursor-pointer">
                                        {{ $pop->name }} ({{ $pop->active_items_count }})
                                    </button>
                                @endforeach
                            </div>
                        @endif

                        @if($hasActiveFilters && auth()->check())
                            <form action="{{ route('used.saved_searches.store') }}" method="POST" class="inline-flex items-center gap-1.5">
                                @csrf
                                <input type="hidden" name="query" value="{{ $search }}">
                                <input type="hidden" name="city" value="{{ $currentCity }}">
                                <input type="hidden" name="cat" value="{{ $currentCat }}">
                                <input type="hidden" name="condition" value="{{ $currentCond }}">
                                <input type="hidden" name="min_price" value="{{ $minPrice }}">
                                <input type="hidden" name="max_price" value="{{ $maxPrice }}">
                                @if($negotiableOnly || $pricingMode === 'negotiable')
                                    <input type="hidden" name="negotiable" value="1">
                                @endif
                                <button type="submit" class="px-3 py-1 rounded-full bg-[#C9A24A]/20 hover:bg-[#C9A24A]/35 border border-[#C9A24A]/40 text-[#F8E2A6] font-semibold transition cursor-pointer">
                                    🔖 Save Search Shortcut
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                @auth
                    @if($savedSearches->isNotEmpty())
                        <div class="flex flex-wrap items-center gap-2 text-[11px]">
                            <span class="text-[#ECD2F3] font-semibold">Saved Searches:</span>
                            @foreach($savedSearches as $ss)
                                <div class="inline-flex items-center rounded-full bg-white/10 border border-white/15 overflow-hidden">
                                    <a href="{{ route('used.index', $ss->toQueryParams()) }}" class="px-2.5 py-1 text-white hover:text-[#ECD2F3] font-medium transition">
                                        🔖 {{ $ss->name }}
                                    </a>
                                    <form action="{{ route('used.saved_searches.destroy', $ss) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1 text-white/50 hover:text-rose-300 cursor-pointer" title="Delete saved search">✕</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endauth
            </div>
        </section>

        <!-- ===================================================================
             2. CURVED WARM STONE EDITORIAL DISCOVERY SHEET
             =================================================================== -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-6 sm:py-8 px-3 sm:px-6 lg:px-8 border-0 min-h-screen shadow-[0_-18px_50px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto">

                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-5 p-3.5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Condition Pills + Quick Budget + Sort + Advanced Filters Toggle -->
                <div class="grail-card p-3 sm:p-4 mb-5 space-y-3">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
                        <!-- Condition Pills -->
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1 lg:pb-0">
                            <span class="grail-eyebrow !text-[#623562] mr-1.5 shrink-0">Condition:</span>
                            @php
                                $condOptions = [
                                    'all' => 'All',
                                    'Like New' => 'Like New',
                                    'Brand New' => 'Brand New',
                                    'Good Condition' => 'Good Condition',
                                    'Fair / Working' => 'Fair',
                                    'for_parts' => 'For Parts',
                                ];
                            @endphp
                            @foreach($condOptions as $cVal => $cLabel)
                                @php
                                    $isCondActive = ($cVal === 'all' && empty($currentCond)) || strcasecmp($currentCond, $cVal) === 0;
                                @endphp
                                <button type="button"
                                        @click="updateParam('condition', '{{ $cVal }}')"
                                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition cursor-pointer shrink-0 {{ $isCondActive ? 'bg-[#1A1018] text-[#ECD2F3]' : 'bg-[#FAFAF8] border border-black/8 text-[#1A1018]/75 hover:bg-[#ECD2F3]/30' }}">
                                    {{ $cLabel }}
                                </button>
                            @endforeach
                        </div>

                        <!-- Right: Budget, Sort & Filters Toggle -->
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <select @change="updateParam('max_price', $event.target.value)"
                                    class="px-3 py-2 grail-input-light text-xs font-bold text-[#1A1018] cursor-pointer">
                                <option value="" {{ empty($maxPrice) ? 'selected' : '' }}>All Budgets</option>
                                <option value="10000" {{ ($maxPrice ?? '') == '10000' ? 'selected' : '' }}>Under ₹10,000</option>
                                <option value="25000" {{ ($maxPrice ?? '') == '25000' ? 'selected' : '' }}>Under ₹25,000</option>
                                <option value="50000" {{ ($maxPrice ?? '') == '50000' ? 'selected' : '' }}>Under ₹50,000</option>
                                <option value="100000" {{ ($maxPrice ?? '') == '100000' ? 'selected' : '' }}>Under ₹1 Lakh</option>
                            </select>

                            <select @change="updateParam('sort', $event.target.value)"
                                    class="px-3 py-2 grail-input-light text-xs font-bold text-[#1A1018] cursor-pointer">
                                <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest First</option>
                                @if(!empty($search))
                                    <option value="relevance" {{ $currentSort === 'relevance' ? 'selected' : '' }}>Most Relevant</option>
                                @endif
                                <option value="price_low" {{ $currentSort === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_high" {{ $currentSort === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            </select>

                            <button type="button" @click="filtersOpen = !filtersOpen"
                                    class="px-3.5 py-2 rounded-xl border border-black/15 bg-white hover:bg-[#F8EFFA] text-xs font-bold text-[#1A1018] transition cursor-pointer">
                                ⚙ More Filters
                            </button>
                        </div>
                    </div>

                    <!-- Collapsible Advanced Filter Drawer -->
                    <form x-show="filtersOpen" x-cloak action="{{ route('used.index') }}" method="GET"
                          class="pt-3 border-t border-black/10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                        @if(!empty($search)) <input type="hidden" name="search" value="{{ $search }}"> @endif
                        @if(!empty($currentCat)) <input type="hidden" name="cat" value="{{ $currentCat }}"> @endif
                        @if(!empty($currentCond)) <input type="hidden" name="condition" value="{{ $currentCond }}"> @endif
                        @if(!empty($currentCity)) <input type="hidden" name="city" value="{{ $currentCity }}"> @endif
                        @if(!empty($currentSort)) <input type="hidden" name="sort" value="{{ $currentSort }}"> @endif

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-[#1A1018]/60 mb-1">Min Price (₹)</label>
                                <input type="number" name="min_price" value="{{ $minPrice }}" min="0" placeholder="0" class="grail-input-light w-full px-3 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase text-[#1A1018]/60 mb-1">Max Price (₹)</label>
                                <input type="number" name="max_price" value="{{ $maxPrice }}" min="0" placeholder="Any" class="grail-input-light w-full px-3 py-1.5 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-[#1A1018]/60 mb-1">Pricing Mode</label>
                            <select name="pricing" class="grail-input-light w-full px-3 py-1.5 text-xs">
                                <option value="">All (Negotiable &amp; Fixed)</option>
                                <option value="negotiable" {{ $pricingMode === 'negotiable' || $negotiableOnly ? 'selected' : '' }}>Negotiable / Offers Open</option>
                                <option value="fixed" {{ $pricingMode === 'fixed' ? 'selected' : '' }}>Fixed Price Only</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-[#1A1018]/60 mb-1">Handover / Fulfillment</label>
                            <select name="fulfillment" class="grail-input-light w-full px-3 py-1.5 text-xs">
                                <option value="">Any Handover Mode</option>
                                <option value="local_pickup" {{ $fulfillment === 'local_pickup' ? 'selected' : '' }}>Local Pickup</option>
                                <option value="public_meetup" {{ $fulfillment === 'public_meetup' ? 'selected' : '' }}>Public Meetup Spot</option>
                                <option value="seller_shipping" {{ $fulfillment === 'seller_shipping' ? 'selected' : '' }}>Seller-Arranged Shipping</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase text-[#1A1018]/60 mb-1">Posted Within</label>
                            <select name="posted" class="grail-input-light w-full px-3 py-1.5 text-xs">
                                <option value="">Any Time</option>
                                <option value="24h" {{ $posted === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                                <option value="7d" {{ $posted === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                                <option value="30d" {{ $posted === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                            </select>
                        </div>

                        @if($activeFilterCategoryFields->isNotEmpty())
                            @foreach($activeFilterCategoryFields as $cf)
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-[#1A1018]/60 mb-1">{{ $cf->label }}</label>
                                    @if($cf->field_type === 'select' && is_array($cf->options))
                                        <select name="spec[{{ $cf->name }}]" class="grail-input-light w-full px-3 py-1.5 text-xs">
                                            <option value="">Any {{ $cf->label }}</option>
                                            @foreach($cf->options as $opt)
                                                <option value="{{ $opt }}" {{ ($specFilters[$cf->name] ?? '') === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input type="text" name="spec[{{ $cf->name }}]" value="{{ $specFilters[$cf->name] ?? '' }}" placeholder="{{ $cf->placeholder ?? 'Filter by ' . $cf->label }}"
                                               class="grail-input-light w-full px-3 py-1.5 text-xs">
                                    @endif
                                </div>
                            @endforeach
                        @endif

                        <div class="sm:col-span-2 lg:col-span-4 flex flex-wrap items-center justify-between gap-3 pt-2">
                            <div class="flex flex-wrap items-center gap-4">
                                <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                    <input type="checkbox" name="refurbished" value="1" {{ $refurbished === '1' ? 'checked' : '' }} class="accent-[#623562]">
                                    <span>Refurbished Only</span>
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer font-semibold">
                                    <input type="checkbox" name="include_sold" value="1" {{ $showSold ? 'checked' : '' }} class="accent-[#623562]">
                                    <span>Include Sold Archive</span>
                                </label>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('used.index') }}" class="px-4 py-2 grail-btn-ghost-light text-xs font-bold">Reset</a>
                                <button type="submit" class="px-5 py-2 grail-btn-obsidian text-xs font-bold cursor-pointer">Apply Filters</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Genuine Featured Listings Strip (Shown ONLY when genuinely eligible/featured) -->
                @if($featuredItems->isNotEmpty() && !$hasActiveFilters)
                    <section class="mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="grail-eyebrow !text-[#623562] block">Promoted Spotlight</span>
                                <h2 class="text-lg sm:text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Featured Classifieds</h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($featuredItems as $fItem)
                                @include('used.partials.card', ['item' => $fItem])
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Items Near Selected Location Strip -->
                @if($nearLocationItems->isNotEmpty() && empty($search))
                    <section class="mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="grail-eyebrow !text-[#623562] block">Local Discovery</span>
                                <h2 class="text-lg sm:text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Recently Listed Near {{ explode(',', $currentCity)[0] }}</h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($nearLocationItems as $nItem)
                                @include('used.partials.card', ['item' => $nItem])
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Relevant Recommendations From Real Activity -->
                @if($relevantRecommendations->isNotEmpty() && !$hasActiveFilters)
                    <section class="mb-8">
                        <div class="flex items-center justify-between mb-3">
                            <div>
                                <span class="grail-eyebrow !text-[#623562] block">Based On Your Browsing &amp; Saved Categories</span>
                                <h2 class="text-lg sm:text-xl font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Recommended Used Finds</h2>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                            @foreach($relevantRecommendations as $rItem)
                                @include('used.partials.card', ['item' => $rItem])
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- Tabs Header: Explore All Ads vs My Ads -->
                <div class="flex flex-wrap items-center justify-between gap-2 mb-5 pb-2 border-b border-black/10">
                    <div class="flex items-center gap-4">
                        <button type="button"
                                @click="activeTab = 'explore'; window.location.hash = 'explore';"
                                :class="activeTab === 'explore' ? 'text-[#1A1018] border-b-2 border-[#1A1018] font-bold' : 'text-[#1A1018]/55 font-medium hover:text-[#1A1018]'"
                                class="pb-2 text-sm sm:text-base tracking-tight transition cursor-pointer flex items-center gap-1.5">
                            <span>{{ $hasActiveFilters ? 'Filtered Classifieds' : 'Recently Listed Items' }}</span>
                            <span class="text-xs text-[#623562] font-bold">({{ $items->total() }})</span>
                        </button>

                        @auth
                            <button type="button"
                                    @click="activeTab = 'myads'; window.location.hash = 'my-ads';"
                                    :class="activeTab === 'myads' ? 'text-[#1A1018] border-b-2 border-[#1A1018] font-bold' : 'text-[#1A1018]/55 font-medium hover:text-[#1A1018]'"
                                    class="pb-2 text-sm sm:text-base tracking-tight transition cursor-pointer flex items-center gap-1.5">
                                <span>My Listings</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] {{ $myAds->count() > 0 ? 'bg-[#1A1018] text-[#ECD2F3]' : 'bg-black/10 text-[#1A1018]' }}">{{ $myAds->count() }}</span>
                            </button>
                        @endauth
                    </div>

                    @if($hasActiveFilters)
                        <a href="{{ route('used.index') }}" class="text-xs font-bold text-rose-600 hover:underline">
                            ✕ Reset All Filters
                        </a>
                    @endif
                </div>

                <!-- Main Ads Grid -->
                <section x-show="activeTab === 'explore'">
                    @if($items->isEmpty())
                        <div class="grail-card p-8 sm:p-12 text-center max-w-md mx-auto my-8 space-y-3">
                            <div class="text-4xl">🔍</div>
                            <h3 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">No Listings Match Yet</h3>
                            <p class="text-xs text-[#1A1018]/60 leading-relaxed">
                                Try broadening your search, clearing filters, or switching to All Cities.
                            </p>
                            <div class="pt-2 flex items-center justify-center gap-3">
                                <a href="{{ route('used.index') }}" class="px-4 py-2 grail-btn-ghost-light text-xs font-bold">Reset Filters</a>
                                <a href="{{ route('used.sell') }}" class="px-4 py-2 grail-btn-obsidian text-xs font-bold">+ Sell an Item</a>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-5">
                            @foreach($items as $item)
                                @include('used.partials.card', ['item' => $item])
                            @endforeach
                        </div>

                        <div class="mt-8">
                            {{ $items->links() }}
                        </div>
                    @endif

                    <!-- Recently Viewed Strip -->
                    @if($recentlyViewed->isNotEmpty())
                        <section class="mt-10 pt-8 border-t border-black/10">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-lg font-medium text-[#1A1018]" style="font-family: var(--font-grail-display);">Recently Viewed</h3>
                            </div>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-4">
                                @foreach($recentlyViewed as $rvItem)
                                    @include('used.partials.card', ['item' => $rvItem])
                                @endforeach
                            </div>
                        </section>
                    @endif
                </section>

                <!-- Quick "My Listings" Section on Index -->
                @auth
                    <section x-show="activeTab === 'myads'" x-cloak>
                        <div class="grail-card p-5 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-black/10">
                                <div>
                                    <span class="grail-eyebrow !text-[#623562] block">Seller Console</span>
                                    <h3 class="text-xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">My Used Listings</h3>
                                    <p class="text-xs text-[#1A1018]/60">Full analytics, offers, enquiries &amp; handovers live in your Used Dashboard.</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('used.dashboard') }}" class="px-4 py-2 grail-btn-ghost-light text-xs font-bold">Open Full Dashboard →</a>
                                    <a href="{{ route('used.sell') }}" class="px-4 py-2 grail-btn-obsidian text-xs font-bold">+ Sell an Item</a>
                                </div>
                            </div>

                            @if($myAds->isEmpty())
                                <div class="py-10 text-center max-w-sm mx-auto space-y-3">
                                    <div class="text-4xl">📦</div>
                                    <h4 class="text-base font-bold text-[#1A1018]">You haven't posted any used items yet</h4>
                                    <p class="text-xs text-[#1A1018]/60">Create drafts, upload genuine photos, and start receiving offers from local buyers.</p>
                                    <a href="{{ route('used.sell') }}" class="inline-block px-5 py-2.5 grail-btn-obsidian text-xs font-bold">+ Create Your First Listing</a>
                                </div>
                            @else
                                <div class="mt-4 space-y-3">
                                    @foreach($myAds as $myAd)
                                        @php $eff = $myAd->effectiveUsedStatus(); @endphp
                                        <div class="p-3 sm:p-4 rounded-2xl border border-black/10 bg-white flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-[#141115] border border-black/8 shrink-0 flex items-center justify-center">
                                                    @if($myAd->hasUploadedImages())
                                                        <img src="{{ $myAd->images->first()->url }}" alt="{{ $myAd->title }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span class="text-base opacity-60">📷</span>
                                                    @endif
                                                </div>
                                                <div class="min-w-0">
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase {{ $eff === 'sold' ? 'bg-rose-100 text-rose-800' : ($eff === 'reserved' ? 'bg-amber-100 text-amber-800' : ($eff === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-black/10 text-[#1A1018]/70')) }}">
                                                        {{ strtoupper($eff) }}
                                                    </span>
                                                    <h4 class="text-xs sm:text-sm font-bold text-[#1A1018] truncate mt-0.5">{{ $myAd->title }}</h4>
                                                    <div class="text-xs font-black text-[#623562]">₹ {{ number_format($myAd->base_price / 100) }}</div>
                                                </div>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                                <a href="{{ route('used.edit', $myAd->id) }}" class="px-3.5 py-1.5 grail-btn-ghost-light text-xs font-bold">Edit</a>
                                                <form action="{{ route('used.toggle-sold', $myAd->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-3.5 py-1.5 grail-btn-ghost-light text-xs font-bold cursor-pointer">
                                                        {{ $myAd->isSold() ? 'Re-activate' : 'Mark Sold' }}
                                                    </button>
                                                </form>
                                                <a href="{{ route('used.show', $myAd->slug) }}" class="px-3.5 py-1.5 rounded-full bg-[#ECD2F3]/50 hover:bg-[#ECD2F3] text-xs font-bold text-[#1A1018] transition">
                                                    View
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </section>
                @endauth

            </div>
        </div>

    </div>
</x-layouts.used>
