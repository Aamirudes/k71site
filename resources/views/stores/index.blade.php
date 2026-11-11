<x-layouts.public 
    :title="'Verified Stores & Brand Ateliers | K71 Marketplace'"
    :metaDescription="'Discover authorized manufacturers, boutique craftsmen, culinary kitchens, and certified distributor storefronts across India on K71.'"
    :fullWidth="true"
>
    <!-- Grail Obsidian Hero Stage -->
    <section class="grail-hero-band text-[#EFE9F2] border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-12 sm:pt-10 sm:pb-16">
            <!-- Breadcrumb Navigation -->
            <nav aria-label="Breadcrumb" class="mb-6 text-xs text-white/60 flex items-center gap-1.5 flex-wrap">
                <a href="{{ url('/') }}" class="hover:text-[#ECD2F3] font-medium transition-colors">Home</a>
                <span class="text-white/30">/</span>
                <span class="font-bold text-[#ECD2F3]">Verified Stores Directory</span>
                @if(!empty($selectedType) && $selectedType !== 'all')
                    <span class="text-white/30">/</span>
                    <span class="font-semibold text-[#C9A24A] capitalize">{{ $typesList[$selectedType]['label'] ?? $selectedType }}</span>
                @endif
            </nav>

            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/8 backdrop-blur-md border border-white/15 text-[#FFCDFF] text-[11px] font-bold uppercase tracking-[0.12em] mb-4">
                        <span class="w-2 h-2 rounded-full bg-[#ECD2F3] animate-pulse"></span>
                        <span>{{ $totalStoresCount }} Verified Storefronts</span>
                        <span class="text-white/30">&bull;</span>
                        <span>Direct Seller Escrow</span>
                    </div>

                    <h1 class="grail-display text-[clamp(2rem,4.5vw,3.5rem)] font-bold text-white tracking-tight leading-[1.04]">
                        Verified Stores &amp; Brand Ateliers
                    </h1>

                    <p class="text-xs sm:text-sm text-white/70 mt-3 leading-relaxed max-w-xl">
                        Explore certified manufacturers, boutique studios, culinary kitchens, and authorized suppliers nationwide. Shop directly from brand owners with full buyer protection.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('vendor.register') }}" class="grail-cta-pill px-5 py-2.5 text-xs sm:text-sm font-bold shadow-md active:scale-95">
                        🏪 Open Your Store &rarr;
                    </a>
                    <a href="{{ route('catalog.index') }}" class="grail-btn-ghost-dark px-4 py-2.5 text-xs sm:text-sm font-semibold">
                        📦 Browse Products
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Grail Stone Sheet Directory -->
    <div class="grail-sheet -mt-4 sm:-mt-6 rounded-t-[28px] sm:rounded-t-[36px] min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-[24px] border border-black/8 p-4 sm:p-6 mb-8 shadow-[0_12px_36px_rgba(26,16,24,0.05)]">
            <form action="{{ route('stores.index') }}" method="GET" class="space-y-4">
                
                <!-- Main Search Bar -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute left-4 top-3.5 text-[#623562]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"></path>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ $searchQuery }}" 
                            placeholder="Search store name, craft, city (e.g. Lucknow, Banarasi, Electronics, Spares)..." 
                            class="grail-input-light w-full pl-11 pr-4 py-3 rounded-2xl text-xs sm:text-sm font-semibold"
                        >
                    </div>

                    <!-- City Selector Dropdown -->
                    <div class="w-full sm:w-56 shrink-0">
                        <select 
                            name="city" 
                            onchange="this.form.submit()" 
                            class="grail-input-light w-full py-3 px-3.5 rounded-2xl text-xs sm:text-sm font-semibold cursor-pointer"
                        >
                            <option value="all">📍 All Locations</option>
                            @foreach($cities as $city)
                                <option value="{{ $city }}" {{ $selectedCity === $city ? 'selected' : '' }}>
                                    📍 {{ $city }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="w-full sm:w-48 shrink-0">
                        <select 
                            name="sort" 
                            onchange="this.form.submit()" 
                            class="grail-input-light w-full py-3 px-3.5 rounded-2xl text-xs sm:text-sm font-semibold cursor-pointer"
                        >
                            <option value="popular" {{ $activeSort === 'popular' ? 'selected' : '' }}>🔥 Most Products</option>
                            <option value="newest" {{ $activeSort === 'newest' ? 'selected' : '' }}>✨ Newly Verified</option>
                            <option value="alpha" {{ $activeSort === 'alpha' ? 'selected' : '' }}>🔤 Alphabetical (A-Z)</option>
                        </select>
                    </div>

                    <button 
                        type="submit" 
                        class="grail-btn-obsidian px-6 py-3 text-xs sm:text-sm font-bold shadow-md active:scale-95 cursor-pointer shrink-0"
                    >
                        Search Stores
                    </button>
                </div>

                <!-- Category / Sector Filter Pills -->
                <div class="pt-3 border-t border-black/8">
                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1 no-scrollbar">
                        @foreach($typesList as $typeKey => $typeData)
                            @php
                                $isActive = ($selectedType === $typeKey) || ($typeKey === 'all' && empty($selectedType));
                                $filterUrl = route('stores.index', array_merge(request()->except(['page', 'type']), ['type' => $typeKey === 'all' ? null : $typeKey]));
                            @endphp
                            <a 
                                href="{{ $filterUrl }}" 
                                class="px-3.5 py-1.5 rounded-full text-xs font-bold whitespace-nowrap transition-all shrink-0 flex items-center gap-1.5 {{ 
                                    $isActive 
                                        ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' 
                                        : 'bg-[#FAF8F5] hover:bg-[#ECD2F3]/35 text-[#1A1018] border border-black/8' 
                                }}"
                            >
                                <span>{{ $typeData['icon'] }}</span>
                                <span>{{ $typeData['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <!-- Active Filter Tags Indicator -->
                @if(!empty($searchQuery) || ($selectedType && $selectedType !== 'all') || ($selectedCity && $selectedCity !== 'all'))
                    <div class="flex items-center gap-2 pt-2 text-xs flex-wrap">
                        <span class="text-[#1A1018]/50 font-bold uppercase tracking-wider text-[10px]">Active Filters:</span>
                        
                        @if(!empty($searchQuery))
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#ECD2F3]/50 text-[#1A1018] font-semibold border border-[#623562]/20">
                                Keyword: "{{ $searchQuery }}"
                            </span>
                        @endif

                        @if($selectedType && $selectedType !== 'all')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-[#ECD2F3]/50 text-[#1A1018] font-semibold border border-[#623562]/20 capitalize">
                                {{ $typesList[$selectedType]['label'] ?? $selectedType }}
                            </span>
                        @endif

                        @if($selectedCity && $selectedCity !== 'all')
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-200">
                                📍 {{ $selectedCity }}
                            </span>
                        @endif

                        <a href="{{ route('stores.index') }}" class="text-rose-600 hover:underline font-bold text-xs ml-1">
                            Clear All Filters &times;
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Stores Count Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="grail-display text-lg sm:text-2xl font-bold text-[#1A1018] tracking-tight flex items-center gap-2.5 flex-wrap">
                    <span>Authorized Brand Storefronts</span>
                    <span class="px-3 py-0.5 rounded-full text-xs font-bold bg-[#1A1018] text-[#ECD2F3] tabular-nums">
                        {{ $vendors->total() }} Available
                    </span>
                </h2>
                <p class="text-xs text-[#1A1018]/60 mt-0.5">Direct communication, verified inventories, and express tracked shipping.</p>
            </div>
        </div>

        <!-- Stores Grid -->
        @if($vendors->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($vendors as $vendor)
                    <div class="grail-card-reveal grail-card group overflow-hidden flex flex-col justify-between" style="--delay: {{ ($loop->index % 6) * 55 }}ms;">
                        
                        <div>
                            <!-- Store Banner & Avatar Header -->
                            <div class="relative h-36 bg-gradient-to-br from-[#060606] via-[#1A1018] to-[#060606] overflow-hidden">
                                @if($vendor->cover_image_url)
                                    <img 
                                        src="{{ $vendor->cover_image_url }}" 
                                        alt="{{ $vendor->shop_name }}" 
                                        class="w-full h-full object-cover opacity-85 group-hover:scale-105 transition-transform duration-500"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-[#ECD2F3]/30 bg-radial from-[#623562]/30 to-black">
                                        <span class="text-4xl opacity-40">🏪</span>
                                    </div>
                                @endif

                                <!-- Sector Tag Overlay -->
                                <div class="absolute top-3 right-3">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-black/75 backdrop-blur-md text-[#ECD2F3] border border-white/15">
                                        {{ $vendor->vendor_type ?: 'Storefront' }}
                                    </span>
                                </div>

                                <!-- Floating Circular Brand Logo -->
                                <div class="absolute -bottom-6 left-5">
                                    <div class="w-16 h-16 rounded-2xl bg-white p-1 border-2 border-white shadow-lg overflow-hidden group-hover:scale-105 transition-transform">
                                        @if($vendor->logo_url)
                                            <img 
                                                src="{{ $vendor->logo_url }}" 
                                                alt="{{ $vendor->shop_name }}" 
                                                class="w-full h-full object-cover rounded-xl"
                                                loading="lazy"
                                            >
                                        @else
                                            <div class="w-full h-full bg-[#1A1018] text-[#ECD2F3] font-serif font-bold text-xl flex items-center justify-center rounded-xl shadow-inner">
                                                {{ strtoupper(substr($vendor->shop_name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Store Body Info -->
                            <div class="p-5 pt-8">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ $vendor->storefront_url }}" class="block">
                                            <h3 class="text-base font-bold text-[#1A1018] group-hover:text-[#623562] transition-colors truncate">
                                                {{ $vendor->shop_name }}
                                            </h3>
                                        </a>

                                        <!-- Location & Verification -->
                                        <div class="flex items-center gap-2 mt-1 text-xs text-[#1A1018]/60 flex-wrap">
                                            <span class="text-emerald-600 font-bold inline-flex items-center gap-0.5">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                                <span>Verified Seller</span>
                                            </span>

                                            @if($vendor->city)
                                                <span class="text-black/20">&bull;</span>
                                                <span class="text-[#1A1018]/70 font-medium">📍 {{ $vendor->city }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Rating & Product Count Badges -->
                                    <div class="text-right shrink-0">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-[#FAF8F5] text-[#C9A24A] text-xs font-black border border-[#C9A24A]/30">
                                            ★ 4.9
                                        </span>
                                        <p class="text-[10px] text-[#1A1018]/50 font-semibold mt-0.5 tabular-nums">
                                            {{ $vendor->products_count }} {{ Str::plural('Product', $vendor->products_count) }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Description / Story -->
                                <p class="text-xs text-[#1A1018]/65 mt-3 line-clamp-2 leading-relaxed">
                                    {{ $vendor->description ?: 'Authorized merchant offering high-quality curated products with standard manufacturer warranty and fast shipping.' }}
                                </p>

                                <!-- Live Products Preview Showcase Strip -->
                                @if($vendor->products->isNotEmpty())
                                    <div class="mt-4 pt-3 border-t border-black/8">
                                        <span class="text-[10px] font-bold uppercase tracking-wider text-[#623562] block mb-2">
                                            Top Products in Store:
                                        </span>
                                        <div class="grid grid-cols-4 gap-2">
                                            @foreach($vendor->products as $pItem)
                                                <a 
                                                    href="{{ route('product.show', $pItem->slug) }}" 
                                                    class="group/item relative rounded-xl bg-[#F4F1EC] border border-black/8 overflow-hidden hover:border-[#623562] transition-all flex flex-col aspect-square"
                                                    title="{{ $pItem->title }} - ₹{{ number_format($pItem->base_price / 100, 2) }}"
                                                >
                                                    <img 
                                                        src="{{ $pItem->primary_image_url }}" 
                                                        alt="{{ $pItem->title }}" 
                                                        class="w-full h-full object-cover group-hover/item:scale-110 transition-transform duration-300"
                                                        loading="lazy"
                                                    >
                                                    <div class="absolute inset-x-0 bottom-0 bg-black/70 backdrop-blur-xs p-0.5 text-center">
                                                        <span class="text-[9px] font-bold text-[#ECD2F3] font-mono block truncate tabular-nums">
                                                            ₹{{ number_format($pItem->base_price / 100, 0) }}
                                                        </span>
                                                    </div>
                                                </a>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="p-4 pt-0">
                            <div class="pt-3 border-t border-black/8 flex items-center gap-2">
                                <a 
                                    href="{{ $vendor->storefront_url }}" 
                                    class="grail-btn-obsidian flex-1 py-2.5 text-xs font-bold text-center shadow-xs flex items-center justify-center gap-1.5"
                                >
                                    <span>Visit Storefront</span>
                                    <span class="text-[#ECD2F3]">&rarr;</span>
                                </a>

                                <a 
                                    href="{{ route('messages.index', ['vendor' => $vendor->id]) }}" 
                                    class="p-2.5 rounded-full bg-[#FAF8F5] hover:bg-[#ECD2F3]/35 text-[#1A1018] hover:text-[#623562] border border-black/10 transition-colors"
                                    title="Message {{ $vendor->shop_name }}"
                                    aria-label="Direct message seller"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="mt-10">
                {{ $vendors->links() }}
            </div>

        @else
            <!-- Zero State / No Stores Found -->
            <div class="p-10 sm:p-16 rounded-[28px] bg-white border border-black/8 text-center shadow-xs">
                <div class="w-16 h-16 rounded-2xl bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
                    🏪
                </div>
                <h3 class="grail-display text-xl font-bold text-[#1A1018] tracking-tight mb-2">
                    No Storefronts Found Matching Your Criteria
                </h3>
                <p class="text-xs sm:text-sm text-[#1A1018]/65 max-w-md mx-auto mb-6 leading-relaxed">
                    Try adjusting your search terms or clearing your location and category filters to browse all verified stores.
                </p>
                <div class="flex items-center justify-center gap-3">
                    <a href="{{ route('stores.index') }}" class="grail-btn-obsidian px-5 py-2.5 text-xs font-bold shadow-xs">
                        View All Stores
                    </a>
                    <a href="{{ route('vendor.register') }}" class="grail-btn-ghost-light px-5 py-2.5 text-xs font-bold">
                        Become a Seller
                    </a>
                </div>
            </div>
        @endif

        <!-- Footer Call to Action: Become a Vendor -->
        <div class="grail-dark-card mt-14 sm:mt-20 p-6 sm:p-10 text-[#EFE9F2] shadow-xl relative overflow-hidden">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div>
                    <span class="grail-eyebrow block mb-1.5">
                        ⚡ Zero Listing Fee &bull; Instant Onboarding
                    </span>
                    <h3 class="grail-display text-xl sm:text-3xl font-bold text-white tracking-tight">
                        Sell Your Products on K71
                    </h3>
                    <p class="text-xs sm:text-sm text-white/70 mt-1.5 max-w-xl">
                        Open your branded storefront with integrated logistics, direct PhonePe payouts, and access to thousands of daily buyers.
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('vendor.register') }}" class="grail-cta-pill px-6 py-3 text-xs sm:text-sm font-bold shadow-md active:scale-95">
                        Launch Store Now &rarr;
                    </a>
                </div>
            </div>
        </div>

        </div>
    </div>
</x-layouts.public>
