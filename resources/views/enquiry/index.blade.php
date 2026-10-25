<x-layouts.public 
    title="K71 Enquiry — India's Premium B2B Sourcing & RFQ Marketplace"
    metaDescription="Discover verified suppliers, post bulk buying requirements, compare factory quotations, and do business directly with verified manufacturers on K71 Enquiry."
>
    <div x-data="k71EnquiryHub()" class="min-h-screen text-slate-900 pb-20">

        <!-- 1. FLOATING GLASS B2B HERO & SMART SEARCH DOCK -->
        <section class="relative pt-6 sm:pt-10 pb-12 sm:pb-16 px-4 sm:px-6 lg:px-8 border-b border-slate-200/60 bg-gradient-to-b from-slate-100/60 via-white to-transparent">
            <!-- Subtle Ambient Light -->
            <div class="absolute top-0 inset-x-0 h-96 pointer-events-none -z-10 overflow-hidden" aria-hidden="true">
                <div class="absolute -top-24 left-1/2 -translate-x-1/2 w-[700px] h-[300px] bg-gradient-to-b from-indigo-500/10 via-cyan-500/5 to-transparent blur-3xl rounded-full"></div>
            </div>

            <div class="max-w-7xl mx-auto space-y-6">

                <!-- Breadcrumb & Sourcing Hub Tag -->
                <div class="flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/80 border border-slate-200/80 shadow-2xs text-slate-700 font-bold backdrop-blur-md">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="tracking-wide uppercase text-[10.5px] text-slate-500 font-extrabold">B2B Sourcing Hub</span>
                        <span class="text-slate-300">•</span>
                        <span>{{ number_format($stats['total_rfqs'] ?? 42) }}+ Active Buyer Requirements</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('vendor.register') }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs flex items-center gap-1 transition">
                            <span>Become a Verified Supplier</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
                </div>

                <!-- Minimal Headline & Mission -->
                <div class="max-w-3xl space-y-2.5">
                    <h1 class="text-3xl sm:text-5xl font-black text-slate-950 tracking-tight leading-tight">
                        Find suppliers. Compare offers. Do business.
                    </h1>
                    <p class="text-sm sm:text-base text-slate-600 font-medium leading-relaxed">
                        The next-generation B2B enquiry marketplace. Connect directly with verified manufacturers, request customized bulk quotations, and trade with complete transparency.
                    </p>
                </div>

                <!-- 2. PREMIUM FLOATING GLASS SEARCH PANEL -->
                <div class="bg-white/90 backdrop-blur-2xl rounded-3xl p-3 sm:p-4 border border-slate-200/80 shadow-xl shadow-slate-200/50">
                    <form action="{{ route('enquiry.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-2.5 items-center">
                        
                        <!-- Search Keyword Input (5 cols) -->
                        <div class="md:col-span-5 relative">
                            <div class="absolute left-3.5 top-3.5 text-indigo-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                            </div>
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="Enter product, machine, or service (e.g. Makrana Marble, Gangsaw, CNC)..."
                                   class="w-full pl-11 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs sm:text-sm font-medium text-slate-900 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 outline-none transition-all placeholder:text-slate-400">
                        </div>

                        <!-- Category Selector (3 cols) -->
                        <div class="md:col-span-3 relative">
                            <select name="cat" class="w-full px-4 py-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs sm:text-sm font-semibold text-slate-800 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 outline-none transition-all cursor-pointer">
                                <option value="">All B2B Categories</option>
                                @foreach($enquiryCategories as $cat)
                                    <option value="{{ $cat->slug }}" {{ $selectedCategory === $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Location Selector (2 cols) -->
                        <div class="md:col-span-2 relative">
                            <select name="city" class="w-full px-3 py-3 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs sm:text-sm font-semibold text-slate-800 focus:bg-white focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 outline-none transition-all cursor-pointer">
                                <option value="">All Locations</option>
                                @foreach($availableCities as $c)
                                    <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                                <option value="Jaipur">Jaipur</option>
                                <option value="Makrana">Makrana</option>
                                <option value="Kishangarh">Kishangarh</option>
                                <option value="Mumbai">Mumbai</option>
                                <option value="Delhi">Delhi NCR</option>
                                <option value="Ahmedabad">Ahmedabad</option>
                            </select>
                        </div>

                        <!-- Action Buttons (2 cols) -->
                        <div class="md:col-span-2 flex items-center gap-2">
                            <button type="submit" 
                                    class="w-full py-3 rounded-2xl bg-slate-900 hover:bg-black active:scale-95 text-white text-xs sm:text-sm font-bold shadow-md transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                                <span>Search</span>
                            </button>
                        </div>

                    </form>

                    <!-- Popular Sourcing Chips + Quick RFQ CTA Strip -->
                    <div class="mt-3 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar py-0.5">
                            <span class="text-slate-400 font-bold uppercase text-[10px] tracking-wider shrink-0">Trending:</span>
                            @foreach(['Makrana Marble', 'Diamond Blades', 'Granite Slabs', 'CNC Machines', 'Packaging Material', 'Quartz Stone'] as $chip)
                                <a href="{{ route('enquiry.index', ['search' => $chip]) }}" 
                                   class="px-3 py-1 rounded-full bg-slate-100 hover:bg-indigo-50 border border-slate-200/70 hover:border-indigo-200 text-slate-700 hover:text-indigo-600 text-xs font-semibold transition shrink-0">
                                    {{ $chip }}
                                </a>
                            @endforeach
                        </div>

                        <button type="button" 
                                @click="openRfqModal()" 
                                class="px-4 py-2 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 active:scale-95 transition flex items-center gap-1.5 cursor-pointer shrink-0">
                            <span>✨ Post Your Requirement</span>
                            <span>&rarr;</span>
                        </button>
                    </div>
                </div>

            </div>
        </section>

        <!-- 3. MAIN BODY CONTAINER -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16 mt-8 sm:mt-12">

            <!-- A. CATEGORY DISCOVERY GRID -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">Key Industry Categories</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Explore high-volume manufacturing sectors and raw materials.</p>
                    </div>
                    @if($selectedCategory)
                        <a href="{{ route('enquiry.index') }}" class="text-xs font-bold text-indigo-600 hover:underline">Clear Filter ✕</a>
                    @endif
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                    @php
                        $sampleCategoryIcons = [
                            'natural-stone' => '🏛️',
                            'machinery' => '⚙️',
                            'construction' => '🏗️',
                            'minerals' => '💎',
                            'chemicals' => '🧪',
                            'packaging' => '📦',
                        ];
                    @endphp

                    @forelse($enquiryCategories as $cat)
                        <a href="{{ route('enquiry.index', ['cat' => $cat->slug]) }}" 
                           class="p-4 rounded-3xl bg-white/80 hover:bg-white border transition-all duration-300 group flex flex-col justify-between shadow-2xs hover:shadow-lg hover:shadow-slate-200/60 {{ $selectedCategory === $cat->slug ? 'border-indigo-600 ring-2 ring-indigo-100 bg-white' : 'border-slate-200/80 hover:border-indigo-200' }}">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50/70 border border-indigo-100/60 text-indigo-700 flex items-center justify-center text-xl font-bold mb-3 group-hover:scale-110 transition-transform">
                                @if($cat->image)
                                    <img src="{{ format_image_url($cat->image) }}" alt="{{ $cat->name }}" class="w-7 h-7 object-contain">
                                @else
                                    <span>{{ $sampleCategoryIcons[$cat->slug] ?? '🏭' }}</span>
                                @endif
                            </div>
                            <div>
                                <h3 class="text-xs font-black text-slate-900 group-hover:text-indigo-600 transition-colors leading-snug">
                                    {{ $cat->name }}
                                </h3>
                                <p class="text-[10.5px] text-slate-500 font-medium mt-0.5">
                                    {{ $cat->enquiries_count }} Active RFQs
                                </p>
                            </div>
                        </a>
                    @empty
                        <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                            No dedicated categories configured yet. All general wholesale requirements are supported.
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- B. RECOMMENDED B2B WHOLESALE PRODUCTS -->
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">Recommended Industrial &amp; Wholesale Listings</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Direct factory-priced products ready for bulk dispatch and custom quotation.</p>
                    </div>
                    <a href="{{ route('catalog.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1">
                        <span>Browse Catalog</span>
                        <span>&rarr;</span>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @forelse($b2bProducts as $prod)
                        <div class="bg-white/95 backdrop-blur-xl rounded-3xl border border-slate-200/80 hover:border-indigo-300 shadow-xs hover:shadow-xl hover:shadow-slate-200/60 transition-all duration-300 flex flex-col justify-between group overflow-hidden">
                            <div>
                                <!-- Image Preview -->
                                <div class="relative aspect-[4/3] w-full overflow-hidden bg-slate-100">
                                    <img src="{{ $prod->primary_image_url }}" 
                                         alt="{{ $prod->title }}" 
                                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                         onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                    
                                    <div class="absolute top-3 left-3 px-2.5 py-0.5 rounded-full bg-slate-900/80 backdrop-blur-md text-white text-[9.5px] font-black uppercase tracking-wider">
                                        {{ $prod->category?->name ?? 'Wholesale' }}
                                    </div>

                                    @if($prod->vendor)
                                        <div class="absolute bottom-3 left-3 px-2.5 py-1 rounded-xl bg-white/95 backdrop-blur-sm border border-slate-200/60 text-slate-900 text-[10px] font-bold shadow-xs flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            <span class="truncate max-w-[140px]">{{ $prod->vendor->shop_name }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Product Details -->
                                <div class="p-4 space-y-2">
                                    <h3 class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-2 leading-snug">
                                        <a href="{{ route('product.show', $prod->slug) }}">
                                            {{ $prod->title }}
                                        </a>
                                    </h3>

                                    <div class="flex items-baseline gap-2">
                                        <span class="text-base font-black text-slate-950">
                                            ₹{{ number_format($prod->base_price / 100, 2) }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-semibold uppercase">Per Unit / Slab</span>
                                    </div>

                                    <!-- Specs Snippet -->
                                    <div class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed">
                                        {{ Str::limit(strip_tags($prod->description), 110) ?: 'High grade commercial quality with factory dispatch guarantee.' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Card Action: Get Best Price / Send Enquiry -->
                            <div class="p-4 pt-0 border-t border-slate-100 flex items-center justify-between gap-2 mt-2">
                                <a href="{{ route('product.show', $prod->slug) }}" 
                                   class="flex-1 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold text-center transition">
                                    View Specs
                                </a>
                                <button type="button" 
                                        @click="openRfqModal('{{ addslashes($prod->title) }}', {{ $prod->vendor_id ?? 'null' }}, {{ $prod->id }})"
                                        class="flex-1 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-black text-center shadow-md shadow-indigo-600/20 transition cursor-pointer">
                                    Get Best Price
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-8 text-center text-slate-400 text-xs">
                            No wholesale products matching this filter. Post your requirement below to get direct quotes from suppliers.
                        </div>
                    @endforelse
                </div>
            </section>

            <!-- C. TRUSTED SUPPLIER DISCOVERY -->
            @if($verifiedSuppliers->isNotEmpty())
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">Verified Suppliers &amp; Manufacturers</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Pre-audited verified businesses with direct contact and quoting capabilities.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($verifiedSuppliers as $sup)
                        <div class="p-5 rounded-3xl bg-white/90 backdrop-blur-xl border border-slate-200/80 hover:border-indigo-300 shadow-2xs hover:shadow-lg transition-all flex flex-col justify-between group">
                            <div class="flex items-start gap-4">
                                <div class="w-14 h-14 rounded-2xl bg-slate-100 border border-slate-200 overflow-hidden shrink-0">
                                    <img src="{{ $sup->logo ? format_image_url($sup->logo) : asset('images/default-avatar.png') }}" 
                                         alt="{{ $sup->shop_name }}" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 mb-1">
                                        <h3 class="text-sm font-black text-slate-900 truncate">{{ $sup->shop_name }}</h3>
                                        <span class="px-1.5 py-0.2 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[9.5px] font-black uppercase shrink-0">
                                            ✓ Verified
                                        </span>
                                    </div>
                                    <p class="text-[11px] text-slate-500 flex items-center gap-1">
                                        <span>📍</span>
                                        <span class="truncate">{{ $sup->city ?? 'Jaipur' }}, {{ $sup->state ?? 'India' }}</span>
                                    </p>
                                    <p class="text-[10px] text-slate-400 font-semibold mt-1">
                                        {{ $sup->products_count }} Published Catalog Products
                                    </p>
                                </div>
                            </div>

                            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center gap-2">
                                <a href="{{ route('storefront.show', $sup->slug) }}" 
                                   class="flex-1 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold text-center transition">
                                    View Storefront
                                </a>
                                <button type="button" 
                                        @click="openRfqModal('', {{ $sup->id }})"
                                        class="flex-1 py-2 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold text-center transition cursor-pointer">
                                    Contact Supplier
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
            @endif

            <!-- D. LIVE BUYER REQUIREMENTS & RFQ BOARD -->
            <section class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight flex items-center gap-2">
                            <span>Open Buyer Requirements &amp; Leads</span>
                            <span class="text-xs px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold">Live Board</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Direct RFQs posted by verified commercial buyers waiting for supplier quotations.</p>
                    </div>

                    <button type="button" 
                            @click="openRfqModal()" 
                            class="px-4 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition self-start sm:self-auto cursor-pointer">
                        + Post Requirement Free
                    </button>
                </div>

                <!-- Enquiries List / Table -->
                <div class="bg-white/90 backdrop-blur-xl rounded-3xl border border-slate-200/80 shadow-xs divide-y divide-slate-100 overflow-hidden">
                    @forelse($enquiries as $enq)
                        <div class="p-5 sm:p-6 hover:bg-slate-50/70 transition-all flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div class="space-y-2 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-[10px] font-mono font-black text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md">
                                        {{ $enq->enquiry_number }}
                                    </span>
                                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2.5 py-0.5 rounded-full">
                                        {{ $enq->category?->name ?? 'General Sourcing' }}
                                    </span>
                                    @if($enq->urgency === 'immediate')
                                        <span class="text-[10px] font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded-full">
                                            ⚡ Immediate Need
                                        </span>
                                    @endif
                                    <span class="text-xs text-slate-400">
                                        Posted {{ $enq->created_at->diffForHumans() }}
                                    </span>
                                </div>

                                <h3 class="text-base sm:text-lg font-black text-slate-900 hover:text-indigo-600 transition leading-snug">
                                    <a href="{{ route('enquiry.show', $enq->enquiry_number) }}">
                                        {{ $enq->title }}
                                    </a>
                                </h3>

                                <p class="text-xs text-slate-600 line-clamp-2 max-w-3xl leading-relaxed">
                                    {{ $enq->description }}
                                </p>

                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 font-semibold pt-1">
                                    <span>📦 Quantity: <strong class="text-slate-800">{{ $enq->quantity }}</strong></span>
                                    <span>📍 Delivery: <strong class="text-slate-800">{{ $enq->city }}</strong></span>
                                    @if($enq->target_budget)
                                        <span>💰 Target: <strong class="text-emerald-700">₹{{ number_format($enq->target_budget / 100, 2) }}</strong></span>
                                    @endif
                                </div>
                            </div>

                            <!-- Right CTA -->
                            <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center gap-2 shrink-0 pt-2 sm:pt-0">
                                <div class="text-right">
                                    <span class="text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-xl border border-emerald-100 block">
                                        💬 {{ $enq->quotes->count() }} Quotes Submitted
                                    </span>
                                </div>
                                <a href="{{ route('enquiry.show', $enq->enquiry_number) }}" 
                                   class="px-5 py-2.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-xs">
                                    Submit Quotation &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-16 text-center space-y-3">
                            <div class="text-3xl">📋</div>
                            <h4 class="text-base font-bold text-slate-800">No matching requirements found</h4>
                            <p class="text-xs text-slate-500 max-w-md mx-auto">Be the first to submit a requirement or adjust your keyword and location search.</p>
                            <button type="button" @click="openRfqModal()" class="px-5 py-2.5 rounded-full bg-indigo-600 text-white text-xs font-bold">
                                Post New Requirement
                            </button>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($enquiries->hasPages())
                    <div class="pt-4">
                        {{ $enquiries->links() }}
                    </div>
                @endif
            </section>

            <!-- E. HOW IT WORKS (3 SIMPLE STEPS) -->
            <section class="rounded-3xl bg-white/80 backdrop-blur-xl border border-slate-200/80 p-8 sm:p-12 shadow-xs">
                <div class="text-center max-w-xl mx-auto mb-10 space-y-2">
                    <span class="text-xs font-black uppercase tracking-wider text-indigo-600">Seamless Sourcing Journey</span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-950 tracking-tight">How K71 B2B Works</h2>
                    <p class="text-xs sm:text-sm text-slate-500">Connecting bulk buyers and verified manufacturers in 3 transparent steps.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 relative">
                    <!-- Step 1 -->
                    <div class="space-y-3 text-center sm:text-left p-6 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-md shadow-indigo-600/20">
                            1
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Discover or Post Requirement</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Search wholesale catalog products or submit your customized RFQ with volume, delivery destination, and specs.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="space-y-3 text-center sm:text-left p-6 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-md shadow-indigo-600/20">
                            2
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Compare Factory Quotations</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Verified manufacturers submit competitive structured quotations detailing unit prices, MOQ, and lead times.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="space-y-3 text-center sm:text-left p-6 rounded-2xl bg-slate-50/80 border border-slate-100">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center font-black text-lg shadow-md shadow-indigo-600/20">
                            3
                        </div>
                        <h3 class="text-base font-bold text-slate-900">Connect &amp; Close Deals</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Chat directly via direct messaging or phone with zero commission middlemen or inflated price tags.
                        </p>
                    </div>
                </div>
            </section>

        </div>

        <!-- 4. INTERACTIVE POST REQUIREMENT (RFQ) GLASS MODAL -->
        <div x-show="rfqModalOpen" 
             x-cloak
             @keydown.escape.window="rfqModalOpen = false"
             class="fixed inset-0 z-[105] flex items-center justify-center p-4 sm:p-6 bg-slate-950/60 backdrop-blur-md">
            
            <div @click.outside="rfqModalOpen = false"
                 class="w-full max-w-xl bg-white/95 backdrop-blur-2xl rounded-3xl border border-white/60 shadow-2xl p-6 sm:p-8 overflow-hidden relative max-h-[92vh] overflow-y-auto no-scrollbar">

                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600">RFQ SOURCING WIZARD</span>
                        <h3 class="text-lg sm:text-xl font-black text-slate-950 tracking-tight">Post Your Requirement</h3>
                    </div>
                    <button type="button" @click="rfqModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Form Error Banner -->
                <div x-show="rfqError" x-cloak class="mt-3 p-3 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">
                    <span x-text="rfqError"></span>
                </div>

                <form @submit.prevent="submitRfqForm()" class="space-y-4 pt-4">
                    <input type="hidden" name="vendor_id" x-model="rfqVendorId">
                    <input type="hidden" name="product_id" x-model="rfqProductId">

                    <!-- Product / Requirement Title -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Product or Requirement Needed *</label>
                        <input type="text" 
                               x-model="rfqTitle" 
                               placeholder="e.g. 5,000 Sq.Ft Makrana Pure White Marble Slabs 18mm"
                               required
                               class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-indigo-600 focus:ring-4 focus:ring-indigo-100 outline-none transition">
                    </div>

                    <!-- Category & Urgency -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Category</label>
                            <select x-model="rfqCategoryId" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 text-xs font-semibold text-slate-800 outline-none focus:border-indigo-600">
                                <option value="">Select Category</option>
                                @foreach($enquiryCategories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Requirement Timeline</label>
                            <select x-model="rfqUrgency" class="w-full px-3 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 text-xs font-semibold text-slate-800 outline-none focus:border-indigo-600">
                                <option value="immediate">Immediate (Ready stock needed)</option>
                                <option value="within_1_week">Within 1-2 Weeks</option>
                                <option value="flexible" selected>Flexible / Planning Phase</option>
                            </select>
                        </div>
                    </div>

                    <!-- Quantity & Target Budget -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Quantity &amp; Unit *</label>
                            <input type="text" 
                                   x-model="rfqQuantity" 
                                   placeholder="e.g. 5,000 Sq.Ft or 25 Metric Tonnes"
                                   required
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-indigo-600 outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Target Budget (Optional ₹)</label>
                            <input type="number" 
                                   x-model="rfqBudget" 
                                   placeholder="e.g. 250000"
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-indigo-600 outline-none">
                        </div>
                    </div>

                    <!-- Delivery City & Pincode -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Delivery Destination / City *</label>
                            <input type="text" 
                                   x-model="rfqCity" 
                                   placeholder="e.g. Jaipur, Delhi, Mumbai Port"
                                   required
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-indigo-600 outline-none">
                        </div>

                        <div class="space-y-1">
                            <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Destination Pincode</label>
                            <input type="text" 
                                   x-model="rfqPincode" 
                                   placeholder="e.g. 302001"
                                   class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs sm:text-sm font-medium text-slate-900 focus:border-indigo-600 outline-none">
                        </div>
                    </div>

                    <!-- Specifications / Notes -->
                    <div class="space-y-1">
                        <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">Technical Specifications &amp; Requirements *</label>
                        <textarea rows="3" 
                                  x-model="rfqDescription" 
                                  placeholder="Describe required grades, finish, dimensions, packing, or certification details..."
                                  required
                                  class="w-full px-4 py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 focus:bg-white text-xs font-medium text-slate-900 focus:border-indigo-600 outline-none"></textarea>
                    </div>

                    <!-- Buyer Contact Info -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
                        <div class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Your Buyer Contact Information</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <input type="text" 
                                   x-model="rfqBuyerName" 
                                   placeholder="Your Full Name *"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 bg-white">
                            
                            <input type="tel" 
                                   x-model="rfqBuyerPhone" 
                                   placeholder="Mobile / WhatsApp *"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 bg-white">
                            
                            <input type="email" 
                                   x-model="rfqBuyerEmail" 
                                   placeholder="Work Email"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-900 bg-white">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="pt-2 flex items-center justify-end gap-2.5">
                        <button type="button" 
                                @click="rfqModalOpen = false" 
                                class="px-5 py-2.5 rounded-2xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                            Cancel
                        </button>

                        <button type="submit" 
                                :disabled="rfqSubmitting" 
                                class="px-6 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider shadow-lg shadow-indigo-600/25 transition flex items-center gap-2 cursor-pointer disabled:opacity-50">
                            <span x-show="!rfqSubmitting">Submit Requirement &amp; Get Quotes &rarr;</span>
                            <span x-show="rfqSubmitting">Posting Requirement...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    function k71EnquiryHub() {
        return {
            rfqModalOpen: false,
            rfqSubmitting: false,
            rfqError: '',
            rfqTitle: '',
            rfqCategoryId: '',
            rfqUrgency: 'flexible',
            rfqQuantity: '',
            rfqBudget: '',
            rfqCity: 'Jaipur',
            rfqPincode: '',
            rfqDescription: '',
            rfqBuyerName: '{{ auth()->user()?->name ?? '' }}',
            rfqBuyerPhone: '{{ auth()->user()?->phone ?? '' }}',
            rfqBuyerEmail: '{{ auth()->user()?->email ?? '' }}',
            rfqVendorId: null,
            rfqProductId: null,

            openRfqModal(title = '', vendorId = null, productId = null) {
                this.rfqTitle = title;
                this.rfqVendorId = vendorId;
                this.rfqProductId = productId;
                this.rfqError = '';
                this.rfqModalOpen = true;
            },

            async submitRfqForm() {
                this.rfqSubmitting = true;
                this.rfqError = '';

                // If user is not logged in and needs authentication
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                try {
                    const res = await fetch('/enquiry', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            title: this.rfqTitle,
                            category_id: this.rfqCategoryId || null,
                            urgency: this.rfqUrgency,
                            quantity: this.rfqQuantity,
                            target_budget: this.rfqBudget ? parseFloat(this.rfqBudget) : null,
                            city: this.rfqCity,
                            pincode: this.rfqPincode || null,
                            description: this.rfqDescription,
                            buyer_name: this.rfqBuyerName,
                            buyer_phone: this.rfqBuyerPhone,
                            buyer_email: this.rfqBuyerEmail,
                            vendor_id: this.rfqVendorId,
                            product_id: this.rfqProductId
                        })
                    });

                    const data = await res.json();
                    if (res.ok && data.success) {
                        this.rfqModalOpen = false;
                        if (window.showToast) {
                            window.showToast('Requirement posted! RFQ: ' + data.enquiry_number + ' 🎉');
                        }
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 800);
                        } else {
                            window.location.reload();
                        }
                    } else {
                        this.rfqError = data.message || (data.errors ? Object.values(data.errors)[0][0] : 'Could not submit requirement.');
                    }
                } catch(e) {
                    console.error(e);
                    this.rfqError = 'Network error while submitting requirement. Please try again.';
                } finally {
                    this.rfqSubmitting = false;
                }
            }
        };
    }
    </script>
    @endpush
</x-layouts.public>
