<x-layouts.public 
    title="B2B Wholesale & Custom Requirements Marketplace — K71 B2B"
    metaDescription="Post bulk buying requirements, compare factory quotes, and access wholesale deals on K71 B2B. Zero middlemen, volume discounts, and verified manufacturers."
>
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
    }" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300 pb-16">

        <!-- TOP BAR: LOCATION, SCAN & CART (Image 4 Style) -->
        <div class="mb-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 p-3.5 bg-white dark:bg-zinc-900 rounded-3xl border border-red-100 dark:border-zinc-800 shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-red-50 dark:bg-red-950/60 text-red-600 dark:text-red-400 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </div>
                <div>
                    <div class="text-[10px] font-bold uppercase tracking-wider text-red-600 dark:text-red-400">B2B.K71 • Wholesale Delivery Port</div>
                    <div class="flex items-center gap-1 font-black text-xs sm:text-sm text-zinc-950 dark:text-white cursor-pointer">
                        <span>Nhava Sheva / Inland Hub, India</span>
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
            </div>

            <!-- Header Action Buttons -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="button" @click="openRfq()" 
                        class="flex-1 sm:flex-none px-4 py-2.5 rounded-2xl bg-red-600 hover:bg-red-700 text-white text-xs font-black uppercase tracking-wider transition shadow-md shadow-red-600/20 active:scale-95 flex items-center justify-center gap-1.5 cursor-pointer">
                    <span>+ Post Requirement</span>
                </button>

                <button type="button" @click="membershipModal = true" 
                        class="px-4 py-2.5 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-300 text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <span>👑 Club Pass</span>
                </button>
            </div>
        </div>

        <!-- SEARCH BAR WITH BARCODE/SCAN ICON (Image 4 Screen 3) -->
        <div class="mb-6">
            <form action="{{ route('enquiry.index') }}" method="GET" class="relative">
                <div class="absolute left-4 top-3.5 text-zinc-400">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" 
                       placeholder="Search bulk products, raw cotton, CNC machinery, milk powder, packaging..." 
                       class="w-full pl-12 pr-28 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 border border-red-100 dark:border-zinc-800 text-sm font-medium focus:ring-2 focus:ring-red-500 focus:outline-none transition shadow-xs">
                
                <div class="absolute right-2 top-2 flex items-center gap-1.5">
                    <button type="button" title="Scan Barcode / Product Code" class="p-2 rounded-xl text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold transition shadow-xs">
                        Search
                    </button>
                </div>
            </form>
        </div>

        <!-- CATEGORIES / DEALS HORIZONTAL RAIL (Image 4 Screen 3) -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-6">
            <a href="{{ route('enquiry.index', array_merge(request()->query(), ['cat' => ''])) }}" 
               class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 flex items-center gap-1.5 {{ empty($selectedCategory) ? 'bg-red-600 text-white shadow-xs' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300' }}">
                <span>🔥 All Deals</span>
            </a>
            <a href="{{ route('enquiry.index', ['search' => 'groceries']) }}" class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400">
                🥫 Bulk Groceries
            </a>
            <a href="{{ route('enquiry.index', ['search' => 'textiles']) }}" class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400">
                🧵 Textiles &amp; Garments
            </a>
            <a href="{{ route('enquiry.index', ['search' => 'machinery']) }}" class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400">
                ⚙️ Industrial Machinery
            </a>
            <a href="{{ route('enquiry.index', ['search' => 'beverages']) }}" class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400">
                🥤 Beverages &amp; Milk
            </a>
            <a href="{{ route('enquiry.index', ['search' => 'packaging']) }}" class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400">
                📦 Corrugated Packaging
            </a>
            @foreach($enquiryCategories as $cat)
                <a href="{{ route('enquiry.index', array_merge(request()->query(), ['cat' => $cat->slug])) }}" 
                   class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 {{ $selectedCategory === $cat->slug ? 'bg-red-600 text-white shadow-xs' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>

        <!-- HERO BANNER: THOUSANDS OF DEALS (Exact Image 4 Screen 3 Banner) -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 p-6 sm:p-8 text-white shadow-xl mb-8">
            <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="max-w-md text-center md:text-left">
                    <span class="inline-block px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-[11px] font-black uppercase tracking-wider mb-3">
                        Wholesale Price Guarantee
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black tracking-tight leading-tight mb-2">
                        Thousands of deals. <br>
                        <span class="text-amber-200">Low Prices. Every day.</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-red-100 mb-5 leading-relaxed">
                        Source direct from verified mills, foundries, and food processing plants. Request custom quotes and lock factory wholesale margins.
                    </p>
                    <button type="button" @click="openRfq()" 
                            class="px-6 py-2.5 rounded-2xl bg-white text-red-700 hover:bg-red-50 font-black text-xs uppercase tracking-wider shadow-lg transition active:scale-95 cursor-pointer">
                        Post Buying Requirement (60s)
                    </button>
                </div>

                <div class="relative shrink-0 flex items-center justify-center">
                    <div class="w-44 h-44 sm:w-52 sm:h-52 rounded-3xl bg-white/10 backdrop-blur-md border border-white/20 p-4 shadow-2xl flex flex-col items-center justify-center text-center">
                        <span class="text-5xl mb-2">🛒</span>
                        <span class="text-lg font-black text-white">% B2B VOLUME</span>
                        <span class="text-[11px] text-amber-200 font-bold">Up to 45% Margins</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- FLASH DEALS WITH COUNTDOWN TIMER (Exact Image 4 Screen 3 Section) -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-3">
                    <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-1.5">
                        <span>Hot deals</span>
                        <span class="text-red-500">🔥</span>
                    </h3>
                    <div class="flex items-center gap-1.5 text-xs text-zinc-500 font-bold">
                        <span>Deals end in</span>
                        <span class="px-2 py-0.5 rounded-md bg-zinc-950 text-white font-mono text-[11px]" x-text="countdownHours">05</span>:
                        <span class="px-2 py-0.5 rounded-md bg-zinc-950 text-white font-mono text-[11px]" x-text="countdownMins">42</span>:
                        <span class="px-2 py-0.5 rounded-md bg-zinc-950 text-white font-mono text-[11px]" x-text="countdownSecs">18</span>
                    </div>
                </div>
                <span class="text-xs text-red-600 dark:text-red-400 font-bold cursor-pointer">View All Volume Deals</span>
            </div>

            <!-- Volume Slabs Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                @php
                    $sampleDeals = [
                        ['title' => 'Coca-Cola Soft Drinks (Pack of 24)', 'unit' => '₹45 / can', 'mrp' => '₹60', 'tier' => 'Min 10 Crates', 'img' => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=300&q=80'],
                        ['title' => 'Sponge Towels Ultra 6 Rolls', 'unit' => '₹89 / roll', 'mrp' => '₹140', 'tier' => 'Min 50 Packs', 'img' => 'https://images.unsplash.com/photo-1583947215259-38e31be8751f?auto=format&fit=crop&w=300&q=80'],
                        ['title' => 'Ditaliano Premium Sliced Bread', 'unit' => '₹28 / pack', 'mrp' => '₹45', 'tier' => 'Daily Morning Delivery', 'img' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=300&q=80'],
                        ['title' => 'Maxwell House Coffee Beans 925g', 'unit' => '₹380 / tin', 'mrp' => '₹550', 'tier' => 'Min 20 Tins', 'img' => 'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=300&q=80'],
                        ['title' => 'Natural Milk Powder Bulk 25kg', 'unit' => '₹240 / kg', 'mrp' => '₹320', 'tier' => 'Factory Direct Bag', 'img' => 'https://images.unsplash.com/photo-1528750997573-59b89d56f4f7?auto=format&fit=crop&w=300&q=80'],
                    ];
                @endphp
                @foreach($sampleDeals as $deal)
                    <div class="group rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 p-3 flex flex-col justify-between hover:border-red-400 transition shadow-xs">
                        <div class="aspect-square rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden mb-2 relative">
                            <img src="{{ $deal['img'] }}" alt="{{ $deal['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            <span class="absolute top-2 left-2 px-2 py-0.5 rounded-md bg-red-600 text-white text-[9px] font-black uppercase">Wholesale</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-zinc-400 font-semibold">{{ $deal['tier'] }}</span>
                            <h4 class="text-xs font-bold text-zinc-900 dark:text-white line-clamp-2 mt-0.5">{{ $deal['title'] }}</h4>
                        </div>
                        <div class="flex items-center justify-between pt-2 mt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <div>
                                <span class="text-xs font-black text-zinc-950 dark:text-white">{{ $deal['unit'] }}</span>
                                <span class="text-[10px] text-zinc-400 line-through block">{{ $deal['mrp'] }}</span>
                            </div>
                            <button type="button" @click="openRfq('{{ addslashes($deal['title']) }}')" 
                                    class="w-8 h-8 rounded-xl bg-red-600 hover:bg-red-700 text-white font-black text-sm flex items-center justify-center shadow-xs cursor-pointer">
                                +
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- OPEN BUYING REQUIREMENTS & RFQ FEED (B2B Engine) -->
        <div class="mb-6 flex items-center justify-between">
            <h3 class="text-sm font-black uppercase tracking-wider text-zinc-900 dark:text-white">Active Buyer RFQs &amp; Leads 📋</h3>
            <span class="text-xs text-zinc-400">{{ $enquiries->total() }} Open Inquiries</span>
        </div>

        @if($enquiries->isEmpty())
            <div class="text-center py-16 px-4 bg-red-50/40 dark:bg-zinc-900/40 rounded-3xl border border-red-100 dark:border-zinc-800 max-w-md mx-auto my-8">
                <div class="w-14 h-14 rounded-2xl bg-red-100 dark:bg-red-950/50 text-red-600 flex items-center justify-center mx-auto mb-3 text-2xl">
                    📑
                </div>
                <h3 class="text-base font-bold text-zinc-900 dark:text-white mb-1">No open RFQs found</h3>
                <p class="text-xs text-zinc-500 mb-4">Be the first buyer to post a requirement and receive factory bids.</p>
                <button type="button" @click="openRfq()" class="px-5 py-2.5 rounded-xl bg-red-600 text-white text-xs font-bold">
                    Post 1st Requirement
                </button>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">
                @foreach($enquiries as $enquiry)
                    <div class="p-5 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-red-400 transition shadow-xs flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="px-2.5 py-0.5 rounded-md bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-300 text-[10px] font-black uppercase">
                                    {{ $enquiry->status === 'quoted' ? 'Quotes Received' : 'Open for Bids' }}
                                </span>
                                <span class="text-[11px] text-zinc-400">📍 {{ $enquiry->city ?? 'Pan-India' }}</span>
                            </div>

                            @if($enquiry->category)
                                <div class="text-[10px] font-black uppercase text-red-600 dark:text-red-400 mb-1">
                                    {{ $enquiry->category->name }}
                                </div>
                            @endif

                            <h4 class="text-sm font-black text-zinc-950 dark:text-white line-clamp-2 mb-1">
                                {{ $enquiry->title }}
                            </h4>
                            <p class="text-xs text-zinc-500 line-clamp-2 mb-3">
                                {{ $enquiry->description }}
                            </p>

                            <div class="grid grid-cols-2 gap-2 p-2.5 rounded-2xl bg-zinc-50 dark:bg-zinc-800/60 text-xs mb-3">
                                <div>
                                    <span class="text-[10px] text-zinc-400 block font-bold">TARGET QUANTITY</span>
                                    <span class="font-black text-zinc-900 dark:text-white">{{ $enquiry->quantity }}</span>
                                </div>
                                <div>
                                    <span class="text-[10px] text-zinc-400 block font-bold">TARGET BUDGET</span>
                                    <span class="font-black text-red-600 dark:text-red-400">{{ $enquiry->formatted_budget }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                            <span class="text-[11px] text-zinc-400">By: {{ $enquiry->buyer_name ?? 'Verified Buyer' }}</span>
                            <a href="{{ route('enquiry.show', $enquiry->id) }}" 
                               class="px-3.5 py-1.5 rounded-xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 text-xs font-bold transition">
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
        <!-- POST BUYING REQUIREMENT RFQ MODAL (60 Seconds Flow) -->
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
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-lg w-full p-6 sm:p-8 shadow-2xl border border-red-100 dark:border-zinc-800 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <button type="button" @click="rfqModal = false" class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 text-xl font-bold p-1">
                    ✕
                </button>

                <div class="mb-5">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-red-600 dark:text-red-400">Post Buying Requirement</span>
                    <h3 class="text-xl font-black text-zinc-950 dark:text-white mt-0.5">Tell verified suppliers what you need</h3>
                    <p class="text-xs text-zinc-500 mt-1">Receive direct wholesale bids within 2 hours. Zero commission.</p>
                </div>

                <form action="{{ route('enquiry.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Product or Material Name *</label>
                        <input type="text" name="title" required x-model="rfqProductTitle" 
                               placeholder="e.g. 500 Bags of White Refined Sugar / 2000 Cotton T-Shirts" 
                               class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Target Quantity *</label>
                            <input type="number" name="quantity" required x-model="rfqQuantity" min="1" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Est. Budget (₹) *</label>
                            <input type="number" name="target_budget" required x-model="rfqTargetPrice" min="100" 
                                   class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Delivery Destination City *</label>
                        <input type="text" name="city" required x-model="rfqCity" placeholder="e.g. Mumbai, Surat, Delhi, Bengaluru" 
                               class="w-full px-4 py-2.5 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Detailed Specifications (GSM, Grade, Packaging)</label>
                        <textarea name="description" rows="2" placeholder="Mention exact material grade, lead time required, or sample request..." 
                                  class="w-full px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Contact Name *</label>
                            <input type="text" name="buyer_name" required value="{{ auth()->user()?->name }}" placeholder="Your Name" 
                                   class="w-full px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 dark:text-zinc-300 mb-1">Mobile / WhatsApp *</label>
                            <input type="tel" name="buyer_phone" required value="{{ auth()->user()?->phone }}" placeholder="WhatsApp Number" 
                                   class="w-full px-4 py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs bg-zinc-50 dark:bg-zinc-900 text-zinc-900 dark:text-white focus:ring-2 focus:ring-red-500 focus:outline-none">
                        </div>
                    </div>

                    <button type="submit" 
                            class="w-full py-3.5 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-red-600/20 active:scale-95 cursor-pointer">
                        Broadcast RFQ to Verified Suppliers
                    </button>
                </form>

            </div>
        </div>

        <!-- ======================================================== -->
        <!-- B2B MEMBERSHIP CLUB MODAL (Image 4 Screen 2) -->
        <!-- ======================================================== -->
        <div x-show="membershipModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4">
            
            <div @click.away="membershipModal = false" 
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-sm w-full p-6 shadow-2xl border border-amber-300 dark:border-amber-700 relative text-zinc-950 dark:text-white">
                
                <button type="button" @click="membershipModal = false" class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 text-xl font-bold p-1">
                    ✕
                </button>

                <!-- Gold Club Card (Exact Image 4 Style) -->
                <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-amber-400 via-amber-300 to-yellow-500 p-5 text-zinc-950 shadow-lg mb-5">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-[10px] font-black uppercase tracking-widest text-zinc-800">K71 Enterprise Pass</span>
                        <span class="text-xs font-black">★ CLUB GOLD</span>
                    </div>
                    <div class="text-xs font-bold text-zinc-800">Rewards Balance</div>
                    <div class="text-2xl font-black tracking-tight mb-3">2,550 pts</div>
                    <div class="text-[10px] text-zinc-800 font-medium">HOLDER: {{ auth()->user()?->name ?? 'Ahmed Enterprise' }}</div>
                </div>

                <div class="space-y-2 text-xs text-zinc-600 dark:text-zinc-400 mb-5">
                    <div class="flex items-center gap-2">✓ <strong>0% Escrow Fee</strong> on wholesale orders above ₹1,00,000</div>
                    <div class="flex items-center gap-2">✓ <strong>Direct Factory Contacts</strong> unmasked instantly</div>
                    <div class="flex items-center gap-2">✓ <strong>Free Third-Party Inspection</strong> on 1st container</div>
                </div>

                <button type="button" @click="alert('K71 Enterprise Membership Activated!'); membershipModal = false;" 
                        class="w-full py-3 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 font-black text-xs uppercase tracking-wider transition">
                    Activate Gold Benefits
                </button>
            </div>
        </div>

        <!-- UNIVERSAL DOCK -->
        <x-k71-bottom-nav />

    </div>
</x-layouts.public>
