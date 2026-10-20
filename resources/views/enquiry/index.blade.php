<x-layouts.public 
    title="IndiaMART Style B2B Wholesale Machinery & Quotes Hub — K71 Industrial"
    metaDescription="Source heavy machinery, industrial plants, raw chemicals, packaging, and commercial goods on K71 B2B. Get verified supplier quotes, TrustSEAL verification, and MOQ volume discounts."
>
    <style>
        .indiamart-navy {
            background: linear-gradient(135deg, #09192f 0%, #0f223d 50%, #173258 100%);
        }
        .indiamart-yellow {
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        }
        .yellow-glow:hover {
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.45);
        }
        .glass-indiamart {
            background: rgba(255, 255, 255, 0.96);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(15, 34, 61, 0.12);
        }
        .dark .glass-indiamart {
            background: rgba(15, 23, 42, 0.96);
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
    </style>

    <div x-data="{
        rfqModal: false,
        membershipModal: false,
        activeTab: 'deals', // 'deals', 'leads', 'membership'
        rfqProductTitle: '',
        rfqQuantity: '100',
        rfqTargetPrice: '150',
        rfqCity: 'Mumbai',
        countdownHours: '05',
        countdownMins: '42',
        countdownSecs: '18',
        openRfq(title = '') {
            this.rfqProductTitle = title;
            this.rfqModal = true;
        }
    }" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300 pb-20 font-sans">

        <!-- ======================================================== -->
        <!-- 1. INDIAMART INDUSTRIAL TOP BAR WITH NAVY & BOLD YELLOW  -->
        <!-- ======================================================== -->
        <div class="mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 p-4 rounded-3xl indiamart-navy text-white shadow-xl border border-blue-400/20">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl indiamart-yellow text-zinc-950 flex items-center justify-center font-black text-2xl shrink-0 shadow-lg">
                    ⚙️
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-black/40 text-[#fbbf24] px-2.5 py-0.5 rounded-md border border-amber-400/30">
                            enquiry.{{ config('k71.root_domain', 'k71.in') }}
                        </span>
                        <span class="text-[11px] text-blue-200 hidden sm:inline">India's Premier Industrial B2B Network &bull; 100% GST Verified</span>
                    </div>
                    <div class="flex items-center gap-1.5 font-black text-sm text-white mt-0.5 cursor-pointer hover:text-amber-300 transition">
                        <span>📍 Delivery Hub:</span>
                        <span class="underline decoration-dotted font-extrabold">{{ request('city', 'Nhava Sheva / Inland Industrial Corridor') }}</span>
                        <svg class="w-3.5 h-3.5 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Header Action Controls -->
            <div class="flex items-center gap-2.5 justify-end">
                <button type="button" @click="openRfq()" 
                        class="px-5 py-2.5 rounded-2xl indiamart-yellow text-zinc-950 font-black text-xs uppercase tracking-wider transition shadow-lg hover:brightness-105 active:scale-95 flex items-center gap-1.5 cursor-pointer">
                    <span class="text-sm font-black">+</span>
                    <span>Post RFQ Requirement</span>
                </button>

                <button type="button" @click="membershipModal = true" 
                        class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-black text-xs uppercase tracking-wider transition border border-white/20 flex items-center gap-1.5 cursor-pointer">
                    <span>🛡️ TrustSEAL</span>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 2. INDIAMART INDUSTRIAL HERO: NAVY & GOLDEN YELLOW       -->
        <!-- ======================================================== -->
        <div class="relative overflow-hidden rounded-3xl indiamart-navy p-6 sm:p-10 text-white shadow-2xl mb-8 border border-blue-400/25">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl text-center md:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 backdrop-blur-md text-[11px] font-black tracking-wider uppercase mb-3 border border-amber-400/30 text-amber-300">
                        <span>🏭 INDIA'S LARGEST B2B SOURCING PLATFORM</span>
                    </div>
                    <h2 class="text-3xl sm:text-5xl font-black tracking-tight leading-tight uppercase drop-shadow-md">
                        Get Best Price <br>
                        <span class="text-amber-400">Direct From Verified Mills</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-blue-100 font-medium mt-3 mb-6 max-w-lg leading-relaxed">
                        Source CNC machinery, food processing lines, industrial raw chemicals, packaging materials, and wholesale fabrics with verified manufacturer pricing.
                    </p>

                    <div class="flex flex-wrap items-center justify-center md:justify-start gap-3">
                        <button type="button" @click="openRfq()" 
                                class="px-7 py-3.5 rounded-2xl indiamart-yellow text-zinc-950 font-black text-xs uppercase tracking-wider shadow-xl transition active:scale-95 flex items-center gap-2 cursor-pointer hover:brightness-105">
                            <span>Get Quotations in 60s ⚡</span>
                        </button>
                        <div class="flex items-center gap-2 text-xs font-bold text-white bg-black/40 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-white/15">
                            <span class="text-emerald-400">✓</span> 0% Middlemen &bull; Tiered MOQ Margins
                        </div>
                    </div>
                </div>

                <!-- Industrial Specs / IndiaMART Verified TrustSEAL Visual -->
                <div class="relative shrink-0 flex items-center justify-center">
                    <div class="w-52 h-52 sm:w-64 sm:h-64 rounded-3xl bg-white/10 backdrop-blur-xl border-2 border-amber-400/40 p-5 shadow-2xl flex flex-col justify-between text-left">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2 py-0.5 rounded-md bg-amber-400 text-zinc-950 font-black text-[9px] uppercase">TrustSEAL CERTIFIED</span>
                                <span class="text-emerald-400 text-xs font-bold">● Active Mill</span>
                            </div>
                            <h4 class="text-base font-black text-white">Heavy Industrial Sourcing</h4>
                            <p class="text-[11px] text-blue-200 mt-1">Multi-Tier Volume Price Slabs with Free Factory Inspection Warranty</p>
                        </div>
                        <div class="pt-3 border-t border-white/15 text-xs">
                            <div class="flex justify-between text-zinc-300 text-[10px]">
                                <span>Avg. Supplier Callback</span>
                                <strong class="text-amber-300 font-bold">&lt; 15 Mins</strong>
                            </div>
                            <div class="flex justify-between text-zinc-300 text-[10px] mt-1">
                                <span>Verified Manufacturers</span>
                                <strong class="text-emerald-300 font-bold">14,800+</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 3. SEARCH BAR WITH INDUSTRIAL QUERY SUGGESTIONS          -->
        <!-- ======================================================== -->
        <div class="mb-8">
            <form action="{{ route('enquiry.index') }}" method="GET" class="relative">
                <div class="absolute left-4 top-4 text-[#0f223d] dark:text-amber-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Enter Product / Service Name (e.g. CNC Lathe Machine, Raw Cotton, Plastic Granules, Packaging Boxes)..." 
                       class="w-full pl-12 pr-40 py-4 rounded-3xl bg-white dark:bg-zinc-900 border-2 border-blue-200 dark:border-zinc-800 text-sm font-semibold focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none transition shadow-sm">
                
                <button type="submit" class="absolute right-2.5 top-2.5 px-6 py-2.5 rounded-2xl indiamart-yellow text-zinc-950 text-xs font-black uppercase tracking-wider transition shadow-md cursor-pointer hover:brightness-105">
                    Get Best Price
                </button>
            </form>
        </div>

        <!-- ======================================================== -->
        <!-- 4. CATEGORIES HORIZONTAL RAIL & CITY FILTER              -->
        <!-- ======================================================== -->
        <div class="mb-8 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-2">
                <a href="{{ route('enquiry.index', array_merge(request()->query(), ['cat' => ''])) }}" 
                   class="px-4 py-2.5 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 {{ empty($selectedCategory) ? 'indiamart-yellow text-zinc-950 shadow-md' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300' }}">
                    All B2B Deals
                </a>
                <a href="{{ route('enquiry.index', ['search' => 'machinery']) }}" class="px-4 py-2.5 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-amber-400">
                    ⚙️ Industrial Machinery
                </a>
                <a href="{{ route('enquiry.index', ['search' => 'textiles']) }}" class="px-4 py-2.5 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-amber-400">
                    🧵 Wholesale Fabrics &amp; Apparel
                </a>
                <a href="{{ route('enquiry.index', ['search' => 'packaging']) }}" class="px-4 py-2.5 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-amber-400">
                    📦 Corrugated Packaging
                </a>
                <a href="{{ route('enquiry.index', ['search' => 'chemicals']) }}" class="px-4 py-2.5 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-amber-400">
                    🧪 Industrial Chemicals
                </a>
                @foreach($enquiryCategories as $cat)
                    <a href="{{ route('enquiry.index', array_merge(request()->query(), ['cat' => $cat->slug])) }}" 
                       class="px-4 py-2.5 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 {{ $selectedCategory === $cat->slug ? 'indiamart-yellow text-zinc-950 shadow-md' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300' }}">
                        {{ $cat->name }}
                    </a>
                @endforeach
            </div>

            <!-- City Filter -->
            @if($availableCities->isNotEmpty())
                <div class="shrink-0">
                    <select onchange="window.location.href = this.value;" 
                            class="w-full sm:w-auto appearance-none pl-4 pr-9 py-2.5 rounded-2xl bg-white dark:bg-zinc-900 border-2 border-blue-200 dark:border-zinc-800 text-xs font-black uppercase tracking-wider text-zinc-800 dark:text-zinc-200 focus:outline-none cursor-pointer">
                        <option value="{{ route('enquiry.index', array_merge(request()->query(), ['city' => ''])) }}">📍 Pan-India Hubs</option>
                        @foreach($availableCities as $c)
                            <option value="{{ route('enquiry.index', array_merge(request()->query(), ['city' => $c])) }}" {{ ($city ?? '') === $c ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- 5. INDIA MART POPULAR VOLUME SLAB DEALS                  -->
        <!-- ======================================================== -->
        <div class="mb-10">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                        <span>Wholesale Volume Tiered Slabs</span>
                        <span class="px-2.5 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-black uppercase">DIRECT FACTORY RATE</span>
                    </h3>
                    <p class="text-xs text-zinc-400">Lock factory discounts by buying in commercial MOQ batches</p>
                </div>
            </div>

            <!-- IndiaMART Volume Slabs Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
                @php
                    $sampleDeals = [
                        ['title' => 'Industrial CNC Lathe Machine 3-Phase', 'price' => '₹4,50,000 / unit', 'moq' => 'MOQ: 1 Unit', 'spec' => 'Automatic &bull; 15HP', 'img' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=400&q=80'],
                        ['title' => 'Bulk Refined White Sugar (50kg Bags)', 'price' => '₹38 / kg', 'moq' => 'MOQ: 100 Bags', 'spec' => 'Grade A &bull; FSSAI', 'img' => 'https://images.unsplash.com/photo-1581447109200-bf276912b649?auto=format&fit=crop&w=400&q=80'],
                        ['title' => '3-Ply Corrugated Shipping Boxes (Pack of 500)', 'price' => '₹18 / piece', 'moq' => 'MOQ: 1000 Pcs', 'spec' => 'Kraft 180 GSM', 'img' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&w=400&q=80'],
                        ['title' => 'Stainless Steel 304 Pipes & Tubes', 'price' => '₹240 / kg', 'moq' => 'MOQ: 500 Kg', 'spec' => 'Seamless &bull; ASTM A312', 'img' => 'https://images.unsplash.com/photo-1504917599217-d4dc5ebe6122?auto=format&fit=crop&w=400&q=80'],
                        ['title' => 'Commercial Flour Pulverizer Mill Machine', 'price' => '₹1,25,000 / unit', 'moq' => 'MOQ: 1 Unit', 'spec' => 'Capacity 400kg/hr', 'img' => 'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=400&q=80'],
                    ];
                @endphp
                @foreach($sampleDeals as $deal)
                    <div class="group rounded-3xl glass-indiamart p-4 flex flex-col justify-between hover:border-amber-400 transition-all duration-300 hover:shadow-xl relative border border-blue-900/10">
                        <div>
                            <div class="aspect-4/3 rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden mb-3 relative">
                                <img src="{{ $deal['img'] }}" alt="{{ $deal['title'] }}" class="w-full h-full object-cover group-hover:scale-108 transition duration-500">
                                <span class="absolute top-2 left-2 px-2.5 py-1 rounded-lg indiamart-yellow text-zinc-950 text-[10px] font-black uppercase shadow">
                                    {{ $deal['moq'] }}
                                </span>
                            </div>
                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-extrabold uppercase tracking-wide block mb-0.5">{{ $deal['spec'] }}</span>
                            <h4 class="text-xs sm:text-sm font-black text-zinc-950 dark:text-white line-clamp-2 leading-snug">{{ $deal['title'] }}</h4>
                        </div>

                        <div class="pt-3 mt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                            <div>
                                <span class="text-xs sm:text-sm font-black text-[#0f223d] dark:text-amber-400 block">{{ $deal['price'] }}</span>
                            </div>
                            <!-- IndiaMART Signature "Get Best Price" Button -->
                            <button type="button" @click="openRfq('{{ addslashes($deal['title']) }}')" 
                                    class="px-3 py-1.5 rounded-xl indiamart-yellow text-zinc-950 text-[11px] font-black uppercase tracking-wider shadow-sm transition hover:brightness-105 active:scale-95 cursor-pointer">
                                Get Best Price
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- 6. ACTIVE BUYER RFQS & LEADS ENGINE                      -->
        <!-- ======================================================== -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                    <span>Live Buyer Requirements &amp; RFQ Board</span>
                    <span class="text-emerald-500 font-bold">● Active Bids</span>
                </h3>
                <p class="text-xs text-zinc-400">{{ $enquiries->total() }} Open RFQs actively seeking supplier quotations</p>
            </div>
        </div>

        @if($enquiries->isEmpty())
            <div class="text-center py-16 px-4 glass-indiamart rounded-3xl max-w-md mx-auto my-8">
                <div class="w-16 h-16 rounded-2xl bg-amber-100 dark:bg-amber-950/40 text-amber-700 flex items-center justify-center mx-auto mb-3 text-3xl">
                    📑
                </div>
                <h3 class="text-base font-black text-zinc-900 dark:text-white mb-1">No open RFQs found</h3>
                <p class="text-xs text-zinc-500 mb-4">Post your first buying requirement and receive direct factory bids within 2 hours.</p>
                <button type="button" @click="openRfq()" class="px-6 py-2.5 rounded-2xl indiamart-yellow text-zinc-950 text-xs font-black uppercase tracking-wider shadow-md">
                    Post 1st Requirement
                </button>
            </div>
        @else
            <!-- RFQ Cards Grid (IndiaMART Lead Style) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                @foreach($enquiries as $enquiry)
                    <div class="p-5 sm:p-6 rounded-3xl glass-indiamart hover:border-amber-400 transition-all duration-300 hover:shadow-xl flex flex-col justify-between border border-blue-900/15 relative">
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2.5 py-1 rounded-lg indiamart-yellow text-zinc-950 text-[10px] font-black uppercase shadow-xs">
                                    {{ $enquiry->status === 'quoted' ? 'Quotes Received' : 'Open for Factory Bids' }}
                                </span>
                                <span class="text-xs text-zinc-400 font-bold">📍 {{ $enquiry->city ?? 'Pan-India' }}</span>
                            </div>

                            @if($enquiry->category)
                                <div class="text-[11px] font-black uppercase text-amber-600 dark:text-amber-400 mb-1">
                                    {{ $enquiry->category->name }}
                                </div>
                            @endif

                            <h4 class="text-base font-black text-zinc-950 dark:text-white line-clamp-2 leading-snug mb-2">
                                {{ $enquiry->title }}
                            </h4>
                            <p class="text-xs text-zinc-500 line-clamp-2 mb-4 leading-relaxed">
                                {{ $enquiry->description }}
                            </p>

                            <!-- Quantity & Target Budget Slabs -->
                            <div class="grid grid-cols-2 gap-3 p-3 rounded-2xl bg-blue-50/50 dark:bg-zinc-900/80 text-xs mb-4 border border-blue-100 dark:border-zinc-800">
                                <div>
                                    <span class="text-[10px] text-zinc-400 uppercase font-bold block">TARGET QUANTITY</span>
                                    <span class="text-sm font-black text-zinc-950 dark:text-white">{{ $enquiry->quantity }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-zinc-400 uppercase font-bold block">TARGET BUDGET</span>
                                    <span class="text-sm font-black text-amber-600 dark:text-amber-400">{{ $enquiry->formatted_budget }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                            <div class="text-xs text-zinc-500">
                                <span>Buyer: <strong class="text-zinc-900 dark:text-white font-bold">{{ $enquiry->buyer_name ?? 'Verified Buyer' }}</strong></span>
                            </div>

                            <a href="{{ route('enquiry.show', $enquiry->enquiry_number ?? $enquiry->id) }}" 
                               class="px-4 py-2 rounded-2xl indiamart-yellow text-zinc-950 text-xs font-black uppercase tracking-wider transition hover:brightness-105 active:scale-95 shadow-md">
                                Submit Quote
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $enquiries->links() }}
            </div>
        @endif

        <!-- ======================================================== -->
        <!-- POST BUYING REQUIREMENT RFQ MODAL (60 Seconds Flow)      -->
        <!-- ======================================================== -->
        <div x-show="rfqModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="rfqModal = false" 
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border-2 border-amber-400/50 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <button type="button" @click="rfqModal = false" class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 text-xl font-bold p-1 cursor-pointer">
                    ✕
                </button>

                <div class="mb-5">
                    <span class="text-[11px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400">Post Requirement &bull; Get Best Price</span>
                    <h3 class="text-xl sm:text-2xl font-black text-zinc-950 dark:text-white mt-0.5">Tell verified manufacturers what you need</h3>
                    <p class="text-xs text-zinc-500 mt-1">Receive direct wholesale bids within 2 hours. Zero broker fee.</p>
                </div>

                <form action="{{ route('enquiry.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Product or Material Name *</label>
                        <input type="text" name="title" required x-model="rfqProductTitle" 
                               placeholder="e.g. 500 Bags of White Refined Sugar / 2000 Cotton T-Shirts" 
                               class="w-full px-4 py-3 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target Quantity *</label>
                            <input type="text" name="quantity" required x-model="rfqQuantity" 
                                   placeholder="e.g. 500 units / 10 tons"
                                   class="w-full px-4 py-3 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Est. Budget (₹) *</label>
                            <input type="number" name="target_budget" required x-model="rfqTargetPrice" min="100" 
                                   class="w-full px-4 py-3 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Delivery City *</label>
                            <input type="text" name="city" required x-model="rfqCity" 
                                   class="w-full px-4 py-3 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Sourcing Urgency</label>
                            <select name="urgency" class="w-full px-4 py-3 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:outline-none cursor-pointer">
                                <option value="immediate">⚡ Immediate (&lt; 48 Hours)</option>
                                <option value="within_1_week">Within 1 Week</option>
                                <option value="planning">Market Research / Planning</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Your Full Name *</label>
                            <input type="text" name="buyer_name" required value="{{ auth()->user()?->name ?? 'Industrial Buyer' }}" 
                                   class="w-full px-4 py-3 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Mobile / WhatsApp *</label>
                            <input type="text" name="buyer_phone" required value="{{ auth()->user()?->phone ?? '9829011222' }}" 
                                   class="w-full px-4 py-3 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Specific Technical Requirements / Material Grades</label>
                        <textarea name="description" rows="3" placeholder="Provide details like power voltage, automation grade, ASTM standard, packing format..." 
                                  class="w-full px-4 py-2.5 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                        <button type="submit" 
                                class="w-full py-4 rounded-2xl indiamart-yellow text-zinc-950 font-black text-xs uppercase tracking-wider transition shadow-lg hover:brightness-105 active:scale-95 cursor-pointer">
                            Request Instant Quotations (Pan-India)
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-layouts.public>
