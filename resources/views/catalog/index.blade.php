<x-layouts.public 
    :title="$seoTitle ?? 'Shop All Products'"
    :metaDescription="$seoDescription ?? null"
    :canonicalUrl="$canonicalUrl ?? null"
    :robots="$robotsMeta ?? null"
    :schemaJson="$catalogSchema ?? null"
    :fullWidth="true"
>
    <div x-data="catalogApp()" class="relative w-full">

        <!-- GRAIL OBSIDIAN CATALOG STAGE HEADER -->
        <section class="grail-hero-band pt-6 sm:pt-10 pb-8 sm:pb-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto">
                <!-- Breadcrumb Navigation -->
                <nav aria-label="Breadcrumb" class="mb-4 text-[11px] sm:text-xs text-[#EFE9F2]/55 flex items-center gap-1.5 flex-wrap">
                    <a href="{{ url('/') }}" class="hover:text-[#FFCDFF] transition-colors">Home</a>
                    <span class="text-white/25">/</span>
                    <a href="{{ route('catalog.index') }}" @click.prevent="clearAllFilters()" class="hover:text-[#FFCDFF] transition-colors">Catalog</a>
                    @if(isset($parentCategory) && $parentCategory)
                        <span class="text-white/25">/</span>
                        <a href="{{ route('category.show', $parentCategory->slug) }}" class="hover:text-[#FFCDFF] transition-colors">{{ $parentCategory->name }}</a>
                    @endif
                    @if(isset($currentCategory) && $currentCategory)
                        <span class="text-white/25">/</span>
                        <span class="font-medium text-[#ECD2F3]">{{ $currentCategory->name }}</span>
                    @elseif(isset($currentStore) && $currentStore)
                        <span class="text-white/25">/</span>
                        <span class="font-medium text-[#ECD2F3]">{{ $currentStore->shop_name }}</span>
                    @elseif(request('gender'))
                        <span class="text-white/25">/</span>
                        <span class="font-medium text-[#ECD2F3]">{{ ucfirst(request('gender')) }}'s Fashion</span>
                    @endif
                </nav>

                <!-- Store Spotlight Banner (When filtered by specific Boutique / Atelier) -->
                @if(isset($currentStore) && $currentStore)
                    <div class="mb-6 rounded-3xl bg-[#161616] text-[#EFE9F2] p-4 sm:p-6 border border-white/12 shadow-2xl relative overflow-hidden">
                        <div class="absolute -right-8 -bottom-8 w-56 h-56 rounded-full bg-[radial-gradient(circle,rgba(236,210,243,0.16),transparent_70%)] pointer-events-none"></div>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 relative z-10">
                            <div class="flex items-center gap-3 sm:gap-4">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl bg-[#FAFAF8] p-1 shrink-0 overflow-hidden border border-white/15">
                                    @if($currentStore->logo)
                                        <img src="{{ \App\Services\ImageService::url($currentStore->logo, 120) }}" alt="{{ $currentStore->shop_name }}" class="w-full h-full object-cover rounded-xl">
                                    @else
                                        <div class="w-full h-full bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center font-serif text-lg sm:text-xl font-bold rounded-xl">
                                            {{ strtoupper(substr($currentStore->shop_name, 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="grail-display text-xl sm:text-2xl text-white">{{ $currentStore->shop_name }}</h2>
                                        <span class="px-2.5 py-0.5 rounded-full bg-[#ECD2F3]/15 text-[#FFCDFF] text-[10px] font-semibold border border-[#ECD2F3]/30">Verified Seller</span>
                                    </div>
                                    <p class="text-xs text-[#EFE9F2]/65 mt-1 max-w-xl line-clamp-1">
                                        {{ $currentStore->description ?: 'Curated fashion collection and verified quality.' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 self-start sm:self-auto shrink-0">
                                <a href="{{ $currentStore->storefront_url }}" class="grail-cta-pill px-4 py-2 text-xs">
                                    Storefront &rarr;
                                </a>
                                <button type="button" 
                                        @click="updateFilter('store', '')" 
                                        class="grail-btn-ghost-dark px-3.5 py-2 text-xs cursor-pointer" 
                                        title="Clear store filter">
                                    ✕ Clear
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Editorial Heading Row & Results Counter -->
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
                    <div>
                        <p class="grail-eyebrow mb-2">K71 Curated Archive</p>
                        <div class="flex items-baseline gap-3 flex-wrap">
                            <h1 class="grail-display text-4xl sm:text-5xl lg:text-6xl text-white">
                                @if(isset($currentCategory) && $currentCategory)
                                    {{ $currentCategory->name }}
                                @elseif(isset($currentStore) && $currentStore)
                                    {{ $currentStore->shop_name }} Collection
                                @elseif(request('gender'))
                                    {{ ucfirst(request('gender')) }}'s Collection
                                @elseif(request('search'))
                                    Search Results
                                @else
                                    All Creations
                                @endif
                            </h1>
                            <span id="results-counter" class="text-[11px] sm:text-xs font-semibold text-[#1A1018] bg-[#ECD2F3] px-3 py-1 rounded-full whitespace-nowrap">
                                {{ $products->total() }} {{ Str::plural('piece', $products->total()) }}
                            </span>
                        </div>
                    </div>

                    @if(isset($currentCategory) && $currentCategory && $currentCategory->description)
                        <p class="text-xs sm:text-sm text-[#EFE9F2]/65 max-w-md leading-relaxed">
                            {{ $currentCategory->description }}
                        </p>
                    @else
                        <p class="text-xs sm:text-sm text-[#EFE9F2]/60 max-w-md leading-relaxed">
                            Direct from verified Indian ateliers, independent studios, and trusted sellers — secured with PhonePe escrow.
                        </p>
                    @endif
                </div>

                <!-- Search Bar + Quick Category Rail -->
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="w-full lg:max-w-md">
                        <div class="relative">
                            <input type="text" 
                                   x-model="filters.search" 
                                   @input.debounce.350ms="applyFilters(1)"
                                   placeholder="Search creations, fabric, boutique..." 
                                   class="w-full pl-11 pr-10 py-3 bg-white/[0.06] border border-white/15 rounded-full text-xs sm:text-sm text-white placeholder-[#EFE9F2]/40 focus:outline-none focus:bg-white/[0.10] focus:border-[#ECD2F3] focus:ring-4 focus:ring-[#ECD2F3]/15 transition">
                            <svg class="w-4 h-4 text-[#ECD2F3] absolute left-4 top-1/2 -translate-y-1/2 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <button type="button" 
                                    x-show="filters.search" 
                                    @click="filters.search = ''; applyFilters(1)" 
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[#EFE9F2]/50 hover:text-white text-xs p-1 cursor-pointer"
                                    title="Clear search">
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Quick Category Horizontal Rail -->
                    <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto no-scrollbar pb-1" style="scrollbar-width: none; -ms-overflow-style: none; -webkit-overflow-scrolling: touch;">
                        <button type="button" 
                                @click="updateFilter('category', '')" 
                                class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition-all cursor-pointer active:scale-95"
                                :class="!filters.category ? 'bg-[#ECD2F3] text-[#1A1018] shadow-[0_6px_18px_rgba(236,210,243,0.22)]' : 'bg-white/[0.05] border border-white/15 text-[#EFE9F2]/80 hover:bg-white/10 hover:text-white'">
                            All Categories
                        </button>
                        @foreach($categories as $cat)
                            <button type="button" 
                                    @click="updateFilter('category', '{{ $cat->slug }}')" 
                                    class="px-3.5 py-1.5 rounded-full text-xs font-semibold shrink-0 transition-all flex items-center gap-1.5 cursor-pointer active:scale-95"
                                    :class="filters.category === '{{ $cat->slug }}' ? 'bg-[#ECD2F3] text-[#1A1018] shadow-[0_6px_18px_rgba(236,210,243,0.22)]' : 'bg-white/[0.05] border border-white/15 text-[#EFE9F2]/80 hover:bg-white/10 hover:text-white'">
                                <span>{{ $cat->name }}</span>
                                @if($cat->products_count > 0)
                                    <span class="text-[10px] px-1.5 py-0.2 rounded-full"
                                          :class="filters.category === '{{ $cat->slug }}' ? 'bg-[#1A1018]/15 text-[#1A1018]' : 'bg-white/10 text-[#EFE9F2]/65'">
                                        {{ $cat->products_count }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <!-- GRAIL STONE SHEET CATALOG WORKSPACE -->
        <section class="grail-sheet rounded-t-[28px] sm:rounded-t-[36px] -mt-3 relative z-10 px-3 sm:px-6 lg:px-8 py-6 sm:py-10 min-h-[60vh]">
            <div class="max-w-7xl mx-auto">

                <!-- Active Filter Chips Bar (Live Instant Removal) -->
                <div id="active-chips-container">
                    @include('catalog.partials.active_chips', ['activeFilters' => $activeFilters])
                </div>

                <!-- Main Marketplace Body: Desktop Left Sidebar + Product Grid -->
                <div class="flex flex-col lg:flex-row gap-5 sm:gap-8 items-start">
                    
                    <!-- Mobile Sticky Filter & Sort Bar -->
                    <div class="lg:hidden w-full flex items-center justify-between gap-2.5 mb-2 sticky top-16 z-30 bg-[#FAFAF8]/95 backdrop-blur-md p-2 rounded-2xl border border-black/10 shadow-sm">
                        <button type="button" 
                                @click="mobileFiltersOpen = true" 
                                class="flex-1 flex items-center justify-center gap-2 py-2.5 px-3 rounded-full bg-[#1A1018] text-white text-xs font-semibold transition active:scale-98 shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-[#ECD2F3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                            <span>Filters</span>
                            <span x-show="activeFilterCount() > 0" 
                                  x-text="activeFilterCount()" 
                                  class="w-4 h-4 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold flex items-center justify-center"></span>
                        </button>

                        <button type="button" 
                                @click="mobileSortOpen = true" 
                                class="flex-1 flex items-center justify-between py-2.5 px-3 rounded-full bg-white hover:bg-[#EEEAE3] border border-black/10 text-[#1A1018] text-xs font-semibold transition active:scale-98 cursor-pointer">
                            <div class="flex items-center gap-1.5 truncate">
                                <svg class="w-3.5 h-3.5 text-[#623562] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/></svg>
                                <span class="truncate" x-text="'Sort: ' + sortLabel()"></span>
                            </div>
                            <svg class="w-3 h-3 text-[#7C4F74] shrink-0 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <!-- Mobile Sort Bottom Sheet -->
                    <div x-show="mobileSortOpen" 
                         x-cloak
                         class="fixed inset-0 z-50 lg:hidden overflow-hidden" 
                         style="display: none;">
                        <div x-show="mobileSortOpen" 
                             x-transition:enter="transition-opacity ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             @click="mobileSortOpen = false" 
                             class="fixed inset-0 bg-black/65 backdrop-blur-xs"></div>

                        <div x-show="mobileSortOpen" 
                             x-transition:enter="transition ease-out duration-300 transform"
                             x-transition:enter-start="translate-y-full"
                             x-transition:enter-end="translate-y-0"
                             x-transition:leave="transition ease-in duration-200 transform"
                             x-transition:leave-start="translate-y-0"
                             x-transition:leave-end="translate-y-full"
                             class="fixed inset-x-0 bottom-0 bg-[#FAFAF8] rounded-t-3xl shadow-2xl flex flex-col z-50 max-h-[80vh] overflow-hidden">
                            
                            <div class="pt-3 pb-1 flex justify-center cursor-pointer" @click="mobileSortOpen = false">
                                <div class="w-10 h-1.5 rounded-full bg-black/15"></div>
                            </div>

                            <div class="px-5 py-3 border-b border-black/8 flex items-center justify-between">
                                <h3 class="grail-display text-xl text-[#1A1018]">Sort Pieces By</h3>
                                <button type="button" @click="mobileSortOpen = false" class="p-1 rounded-lg text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
                                    ✕
                                </button>
                            </div>

                            <div class="p-4 space-y-1.5 overflow-y-auto">
                                @php
                                    $mobileSortItems = [
                                        'recommended' => ['label' => 'Featured', 'desc' => 'Best match & handpicked items'],
                                        'newest' => ['label' => 'Newest Arrivals', 'desc' => 'Recently added items'],
                                        'price_low' => ['label' => 'Price: Low to High', 'desc' => 'Most affordable first'],
                                        'price_high' => ['label' => 'Price: High to Low', 'desc' => 'Premium first'],
                                        'discount' => ['label' => 'Biggest Discount %', 'desc' => 'Greatest savings'],
                                        'popular' => ['label' => 'Popularity', 'desc' => 'Most ordered & reviewed']
                                    ];
                                @endphp
                                @foreach($mobileSortItems as $sVal => $sMeta)
                                    <button type="button" 
                                            @click="updateFilter('sort', '{{ $sVal }}'); mobileSortOpen = false;"
                                            class="w-full flex items-center justify-between p-3.5 rounded-2xl transition text-left cursor-pointer"
                                            :class="filters.sort === '{{ $sVal }}' ? 'bg-[#1A1018] text-white shadow-xs font-semibold' : 'bg-white hover:bg-[#ECD2F3]/30 text-[#1A1018]'">
                                        <div class="flex items-center gap-3">
                                            <div>
                                                <div class="text-xs font-bold">{{ $sMeta['label'] }}</div>
                                                <div class="text-[10px]" :class="filters.sort === '{{ $sVal }}' ? 'text-[#EFE9F2]/70' : 'text-[#7C4F74]'">{{ $sMeta['desc'] }}</div>
                                            </div>
                                        </div>
                                        <div x-show="filters.sort === '{{ $sVal }}'" class="text-[#ECD2F3] text-sm font-bold">
                                            ✓
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Filter Bottom Sheet -->
                    <div x-show="mobileFiltersOpen" 
                         x-cloak
                         class="fixed inset-0 z-50 lg:hidden overflow-hidden" 
                         style="display: none;">
                        <div x-show="mobileFiltersOpen" 
                             x-transition:enter="transition-opacity ease-out duration-300"
                             x-transition:enter-start="opacity-0"
                             x-transition:enter-end="opacity-100"
                             x-transition:leave="transition-opacity ease-in duration-200"
                             x-transition:leave-start="opacity-100"
                             x-transition:leave-end="opacity-0"
                             @click="mobileFiltersOpen = false" 
                             class="fixed inset-0 bg-black/65 backdrop-blur-xs"></div>

                        <div x-show="mobileFiltersOpen" 
                             x-transition:enter="transition ease-out duration-300 transform"
                             x-transition:enter-start="translate-y-full"
                             x-transition:enter-end="translate-y-0"
                             x-transition:leave="transition ease-in duration-200 transform"
                             x-transition:leave-start="translate-y-0"
                             x-transition:leave-end="translate-y-full"
                             class="fixed inset-x-0 bottom-0 max-h-[90vh] bg-[#FAFAF8] rounded-t-3xl shadow-2xl flex flex-col z-50">
                            
                            <div class="pt-3 pb-1 flex justify-center cursor-pointer" @click="mobileFiltersOpen = false">
                                <div class="w-10 h-1.5 rounded-full bg-black/15"></div>
                            </div>

                            <div class="px-5 py-3 border-b border-black/8 flex items-center justify-between bg-white/80">
                                <div class="flex items-center gap-2">
                                    <span class="grail-display text-xl text-[#1A1018]">Filters &amp; Refine</span>
                                    <span x-show="activeFilterCount() > 0" 
                                          x-text="activeFilterCount() + ' active'" 
                                          class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018]"></span>
                                </div>
                                <button type="button" @click="mobileFiltersOpen = false" class="p-1 rounded-lg text-[#7C4F74] hover:text-[#1A1018] cursor-pointer">
                                    ✕
                                </button>
                            </div>

                            <div class="flex-1 overflow-y-auto p-5 space-y-6">
                                @include('catalog.partials.filter_form', ['formId' => 'mobile_filter'])
                            </div>

                            <div class="p-4 border-t border-black/10 bg-white flex items-center gap-3">
                                <button type="button" 
                                        @click="clearAllFilters(); mobileFiltersOpen = false;" 
                                        class="flex-1 py-3 grail-btn-ghost-light text-center text-xs cursor-pointer">
                                    Clear All
                                </button>
                                <button type="button" 
                                        @click="mobileFiltersOpen = false" 
                                        class="flex-2 py-3 grail-btn-obsidian text-center text-xs shadow-md cursor-pointer">
                                    View Results →
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- DESKTOP STICKY LEFT SIDEBAR -->
                    <aside class="hidden lg:block w-64 xl:w-72 shrink-0 sticky top-24 space-y-5">
                        <div class="bg-white rounded-3xl border border-black/8 p-5 shadow-[0_8px_28px_-14px_rgba(24,24,27,0.08)] space-y-6">
                            <div class="flex items-center justify-between pb-3 border-b border-black/8">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-[#623562]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#1A1018]">Refine Search</span>
                                </div>
                                <button type="button" 
                                        @click="clearAllFilters()" 
                                        class="text-[11px] font-semibold text-rose-600 hover:text-rose-800 transition cursor-pointer">
                                    Reset
                                </button>
                            </div>

                            @include('catalog.partials.filter_form', ['formId' => 'desktop_filter'])
                        </div>
                    </aside>

                    <!-- PRODUCT GRID & SORTING AREA -->
                    <main class="flex-1 w-full min-w-0">
                        
                        <!-- Desktop Sorting Toolbar & View Switcher -->
                        <div class="hidden sm:flex items-center justify-between gap-3 mb-5 p-2.5 rounded-2xl bg-white border border-black/8 shadow-2xs">
                            <div class="flex items-center gap-1.5 text-xs flex-wrap min-w-0">
                                <span class="font-bold text-[#623562] uppercase tracking-wider text-[10.5px] mr-1 shrink-0 pl-1">Sort By:</span>
                                @php
                                    $sortOpts = [
                                        'recommended' => 'Featured',
                                        'newest' => 'Newest',
                                        'price_low' => 'Price: Low to High',
                                        'price_high' => 'Price: High to Low',
                                        'discount' => 'Discount %',
                                        'popular' => 'Popular'
                                    ];
                                @endphp
                                @foreach($sortOpts as $sKey => $sLabel)
                                    <button type="button" 
                                            @click="updateFilter('sort', '{{ $sKey }}')" 
                                            class="px-3 py-1.5 rounded-full text-xs font-medium transition shrink-0 cursor-pointer"
                                            :class="filters.sort === '{{ $sKey }}' ? 'bg-[#1A1018] text-white font-semibold shadow-2xs' : 'text-[#1A1018]/75 hover:bg-[#ECD2F3]/35 hover:text-[#1A1018]'">
                                        {{ $sLabel }}
                                    </button>
                                @endforeach
                            </div>

                            <!-- Grid Layout Switcher -->
                            <div class="flex items-center gap-1 border-l border-black/10 pl-2.5 shrink-0">
                                <button type="button" 
                                        @click="setGrid('grid-cols-2 lg:grid-cols-3')" 
                                        :class="gridCols.includes('lg:grid-cols-3') ? 'text-[#1A1018] bg-[#ECD2F3]/50' : 'text-zinc-400 hover:text-[#1A1018]'" 
                                        class="p-1.5 rounded-lg transition cursor-pointer" 
                                        title="3 Columns">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H3a1 1 0 01-1-1V4zm7 0a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4zm7 0a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1V4zM2 11a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1H3a1 1 0 01-1-1v-3zm7 0a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-3zm7 0a1 1 0 011-1h3a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-3z"/></svg>
                                </button>
                                <button type="button" 
                                        @click="setGrid('grid-cols-2 md:grid-cols-3 xl:grid-cols-4')" 
                                        :class="gridCols.includes('xl:grid-cols-4') ? 'text-[#1A1018] bg-[#ECD2F3]/50' : 'text-zinc-400 hover:text-[#1A1018]'" 
                                        class="p-1.5 rounded-lg transition cursor-pointer" 
                                        title="4 Columns">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M2 4a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1H3a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1H8a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1h-2a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1h-2a1 1 0 01-1-1V4zM2 11a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1H3a1 1 0 01-1-1v-3zm5 0a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1H8a1 1 0 01-1-1v-3zm5 0a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3zm5 0a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 01-1 1h-2a1 1 0 01-1-1v-3z"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Product Grid Container with Live Loading Pulse -->
                        <div class="relative min-h-[300px]">
                            <div x-show="loading" 
                                 x-transition:enter="transition-opacity ease-out duration-150"
                                 x-transition:enter-start="opacity-0"
                                 x-transition:enter-end="opacity-100"
                                 x-transition:leave="transition-opacity ease-in duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="absolute inset-0 bg-[#F6F6F6]/70 backdrop-blur-[2px] z-20 flex items-start justify-center pt-24 rounded-3xl"
                                 style="display: none;">
                                <div class="px-4 py-2 rounded-full bg-[#1A1018] text-[#EFE9F2] text-xs font-medium shadow-lg flex items-center gap-2 border border-[#ECD2F3]/30">
                                    <svg class="animate-spin h-3.5 w-3.5 text-[#ECD2F3]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    <span>Updating pieces...</span>
                                </div>
                            </div>

                            <!-- Live Grid Target -->
                            <div id="products-grid-container" :class="loading ? 'opacity-50' : ''" class="transition-opacity duration-200">
                                @include('catalog.partials.products_grid', ['products' => $products])
                            </div>
                        </div>

                        <!-- Pagination Container -->
                        <div id="pagination-container" class="mt-8 sm:mt-10 pt-5 sm:pt-6 border-t border-black/10">
                            @if($products->hasPages())
                                {{ $products->links() }}
                            @endif
                        </div>

                        <!-- RECOMMENDED FOR YOU / TRENDING EDITS -->
                        @if(isset($recommendedProducts) && $recommendedProducts->count() > 0)
                            <section class="mt-12 sm:mt-16 pt-8 sm:pt-10 border-t border-black/10">
                                <div class="flex items-center justify-between mb-5 sm:mb-6">
                                    <div>
                                        <span class="grail-eyebrow text-[#623562] block mb-1">Curated For You</span>
                                        <h3 class="grail-display text-2xl sm:text-3xl text-[#1A1018]">Curated Masterpieces</h3>
                                        <p class="text-xs text-[#7C4F74] mt-1">Recommended creations you might love</p>
                                    </div>
                                    <span class="text-[11px] sm:text-xs font-semibold text-[#1A1018] bg-[#ECD2F3] px-3 py-1 rounded-full">
                                        Handpicked
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-2.5 sm:gap-5">
                                    @foreach($recommendedProducts->take(4) as $recProd)
                                        <x-product-card :product="$recProd" />
                                    @endforeach
                                </div>
                            </section>
                        @endif
                    </main>
                </div>
            </div>
        </section>
    </div>

    <!-- Live Client-side Controller for Instant Ajax Filtration & Debounced Search -->
    <script>
        function catalogApp() {
            return {
                gridCols: 'grid-cols-2 md:grid-cols-3 xl:grid-cols-4',
                mobileFiltersOpen: false,
                mobileSortOpen: false,
                loading: false,
                filters: {
                    search: '{{ addslashes(request('search', '')) }}',
                    store: '{{ addslashes(request('store', request('vendor', ''))) }}',
                    category: '{{ addslashes(request('category', '')) }}',
                    gender: '{{ addslashes(request('gender', '')) }}',
                    price_range: '{{ addslashes(request('price_range', '')) }}',
                    min_price: '{{ addslashes(request('min_price', '')) }}',
                    max_price: '{{ addslashes(request('max_price', '')) }}',
                    discount: '{{ addslashes(request('discount', '')) }}',
                    rating: '{{ addslashes(request('rating', '')) }}',
                    in_stock: {{ request()->boolean('in_stock') ? 'true' : 'false' }},
                    featured: {{ request()->boolean('featured') ? 'true' : 'false' }},
                    sort: '{{ addslashes(request('sort', 'recommended')) }}',
                    page: {{ (int) request('page', 1) }}
                },
                setGrid(cols) {
                    this.gridCols = cols;
                },
                activeFilterCount() {
                    let count = 0;
                    if (this.filters.store) count++;
                    if (this.filters.category) count++;
                    if (this.filters.gender) count++;
                    if (this.filters.price_range || this.filters.min_price || this.filters.max_price) count++;
                    if (this.filters.discount) count++;
                    if (this.filters.rating) count++;
                    if (this.filters.in_stock) count++;
                    if (this.filters.featured) count++;
                    if (this.filters.search) count++;
                    return count;
                },
                sortLabel() {
                    const map = {
                        'recommended': 'Featured',
                        'newest': 'Newest',
                        'price_low': 'Low to High',
                        'price_high': 'High to Low',
                        'discount': 'Discount %',
                        'popular': 'Popular'
                    };
                    return map[this.filters.sort] || 'Featured';
                },
                updateFilter(key, value) {
                    this.filters[key] = value;
                    this.applyFilters(1);
                },
                removeFilter(key) {
                    if (key === 'store') this.filters.store = '';
                    else if (key === 'category') this.filters.category = '';
                    else if (key === 'gender') this.filters.gender = '';
                    else if (key === 'price_range') this.filters.price_range = '';
                    else if (key === 'min_price') this.filters.min_price = '';
                    else if (key === 'max_price') this.filters.max_price = '';
                    else if (key === 'discount') this.filters.discount = '';
                    else if (key === 'rating') this.filters.rating = '';
                    else if (key === 'in_stock') this.filters.in_stock = false;
                    else if (key === 'featured') this.filters.featured = false;
                    else if (key === 'search') this.filters.search = '';
                    this.applyFilters(1);
                },
                clearAllFilters() {
                    this.filters.search = '';
                    this.filters.store = '';
                    this.filters.category = '';
                    this.filters.gender = '';
                    this.filters.price_range = '';
                    this.filters.min_price = '';
                    this.filters.max_price = '';
                    this.filters.discount = '';
                    this.filters.rating = '';
                    this.filters.in_stock = false;
                    this.filters.featured = false;
                    this.filters.sort = 'recommended';
                    this.applyFilters(1);
                },
                applyFilters(page = 1) {
                    this.filters.page = page;
                    this.loading = true;

                    const params = new URLSearchParams();
                    for (const [k, v] of Object.entries(this.filters)) {
                        if (v !== '' && v !== false && v !== null && v !== undefined) {
                            if (k === 'page' && v === 1) continue;
                            if (k === 'sort' && v === 'recommended') continue;
                            params.set(k, v === true ? '1' : v);
                        }
                    }

                    const queryString = params.toString();
                    const targetUrl = '{{ route('catalog.index') }}' + (queryString ? '?' + queryString : '');

                    // Instant URL update without full page reload
                    window.history.pushState(null, '', targetUrl);

                    fetch(targetUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.success) {
                            const gridEl = document.getElementById('products-grid-container');
                            if (gridEl) gridEl.innerHTML = data.products_html;

                            const countEl = document.getElementById('results-counter');
                            if (countEl) countEl.innerText = data.total_text;

                            const chipsEl = document.getElementById('active-chips-container');
                            if (chipsEl) chipsEl.innerHTML = data.active_chips_html;

                            const pagEl = document.getElementById('pagination-container');
                            if (pagEl) pagEl.innerHTML = data.pagination_html;

                            if (data.seo_title) document.title = data.seo_title;
                            window.dispatchEvent(new CustomEvent('catalog:updated'));
                        }
                    })
                    .catch(err => {
                        console.error('AJAX filtering error:', err);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                }
            };
        }
    </script>
</x-layouts.public>
