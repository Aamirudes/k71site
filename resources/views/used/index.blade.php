<x-layouts.public 
    title="Minimalist Pre-Owned & Peer-to-Peer Marketplace — K71 Used"
    metaDescription="Buy and sell verified pre-owned electronics, mobiles, luxury cars, laptops, and gadgets on K71 Used. Real-time make-an-offer price negotiation, verified owners, and escrow wallet."
>
    <div x-data="{
        activeTab: 'browse', // 'browse', 'orders', 'messages', 'wallet'
        offerModal: false,
        offerStep: 1, // 1: keypad, 2: processing, 3: accepted, 4: rejected, 5: checkout
        activeProduct: null,
        offerAmount: '170000',
        offerNote: 'Hi! I am ready for instant inspection and payment if my offer is accepted.',
        orderTab: 'active', // 'active', 'completed'
        walletBalance: 299677,
        selectedShipping: 'truck',
        selectedAddress: 'home',

        openOffer(item) {
            this.activeProduct = item;
            this.offerAmount = String(Math.round(item.rawPrice * 0.9));
            this.offerStep = 1;
            this.offerModal = true;
        },
        appendDigit(d) {
            if (this.offerAmount === '0' && d !== '.') {
                this.offerAmount = d;
            } else {
                if (d === '.' && this.offerAmount.includes('.')) return;
                if (this.offerAmount.length < 9) {
                    this.offerAmount += d;
                }
            }
        },
        backspaceDigit() {
            if (this.offerAmount.length > 1) {
                this.offerAmount = this.offerAmount.slice(0, -1);
            } else {
                this.offerAmount = '0';
            }
        },
        setQuickAmount(amt) {
            this.offerAmount = String(amt);
        },
        submitOffer() {
            this.offerStep = 2; // processing
            setTimeout(() => {
                // Simulate intelligent counter offer logic
                if (Number(this.offerAmount) >= (this.activeProduct ? this.activeProduct.rawPrice * 0.75 : 50000)) {
                    this.offerStep = 3; // accepted!
                } else {
                    this.offerStep = 4; // rejected
                }
            }, 1200);
        },
        proceedToCheckout() {
            this.offerStep = 5;
        },
        closeOffer() {
            this.offerModal = false;
            this.offerStep = 1;
        }
    }" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300">

        <!-- FLOATING '+ SELL NOW' BUTTON -->
        <div class="fixed bottom-6 right-6 z-40 sm:bottom-8 sm:right-8">
            <a href="{{ route('vendor.register') }}" 
               class="group flex items-center gap-2.5 px-6 py-3.5 rounded-full bg-zinc-950 hover:bg-zinc-800 text-white dark:bg-white dark:hover:bg-zinc-200 dark:text-zinc-950 font-bold text-xs uppercase tracking-wider shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 border border-zinc-700/20">
                <span class="w-5 h-5 rounded-full bg-white text-zinc-950 dark:bg-zinc-950 dark:text-white flex items-center justify-center text-xs font-black group-hover:rotate-90 transition-transform duration-300">+</span>
                <span>Post Your Ad</span>
            </a>
        </div>

        <!-- TOP MINIMAL NAV & MODE SWITCHER (Matching Image 1 Super-App Bar) -->
        <div class="mb-6 border-b border-zinc-200 dark:border-zinc-800 pb-4">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>used.{{ config('k71.root_domain', 'marblemandi.org') }} • Peer-to-Peer Verified Marketplace</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-zinc-950 dark:text-white">
                        K71 Pre-Owned &amp; Direct Deals
                    </h1>
                </div>

                <!-- Navigation Tabs (Browse, Orders, Messages, Escrow Wallet) -->
                <div class="flex items-center p-1 rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-xs font-bold w-full md:w-auto overflow-x-auto no-scrollbar">
                    <button type="button" @click="activeTab = 'browse'" 
                            :class="activeTab === 'browse' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                        <span>Marketplace</span>
                    </button>
                    <button type="button" @click="activeTab = 'orders'" 
                            :class="activeTab === 'orders' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span>My Orders ({{ $items->total() > 0 ? 2 : 0 }})</span>
                    </button>
                    <button type="button" @click="activeTab = 'messages'" 
                            :class="activeTab === 'messages' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                        <span>Store Chats</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </button>
                    <button type="button" @click="activeTab = 'wallet'" 
                            :class="activeTab === 'wallet' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-500 hover:text-zinc-900 dark:hover:text-zinc-200'"
                            class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <span>E-Wallet</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 1: BROWSE MARKETPLACE (Image 1 Style) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'browse'" x-transition:enter="transition ease-out duration-200">
            <!-- Search & Filters -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <form action="{{ route('used.index') }}" method="GET" class="md:col-span-8 relative">
                    <div class="absolute left-4 top-3 text-zinc-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="Search BMW, iPhone 15 Pro, MacBook M3, Sony A7IV, Rolex..." 
                           class="w-full pl-12 pr-28 py-3 rounded-2xl bg-zinc-50 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-sm font-medium focus:ring-2 focus:ring-zinc-950 dark:focus:ring-white focus:outline-none transition">
                    <button type="submit" class="absolute right-2 top-2 px-4 py-1.5 rounded-xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 text-xs font-bold transition">
                        Search
                    </button>
                </form>

                <div class="md:col-span-4 flex items-center gap-2 justify-end">
                    <a href="{{ route('used.index', array_merge(request()->query(), ['negotiable' => $negotiableOnly ? 0 : 1])) }}" 
                       class="px-4 py-3 rounded-2xl border text-xs font-bold transition flex items-center gap-2 shrink-0 {{ $negotiableOnly ? 'bg-zinc-950 text-white dark:bg-white dark:text-zinc-950 border-transparent shadow-xs' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-zinc-200 dark:border-zinc-800 hover:bg-zinc-50' }}">
                        <span>🤝 Negotiable Only</span>
                    </a>

                    @if($availableCities->isNotEmpty())
                        <form action="{{ route('used.index') }}" method="GET" class="shrink-0">
                            @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                            <select name="city" onchange="this.form.submit()" 
                                    class="px-3.5 py-3 rounded-2xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-xs font-bold text-zinc-800 dark:text-zinc-200 focus:outline-none cursor-pointer">
                                <option value="">📍 All Cities</option>
                                @foreach($availableCities as $c)
                                    <option value="{{ $c }}" {{ $city === $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Categories Horizontal Rail -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-4 mb-6">
                <a href="{{ route('used.index', array_merge(request()->query(), ['cat' => ''])) }}" 
                   class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 {{ empty($selectedCategory) ? 'bg-zinc-950 dark:bg-white text-white dark:text-zinc-950' : 'bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 border border-zinc-200 dark:border-zinc-800' }}">
                    All Items
                </a>
                @foreach($usedCategories as $cat)
                    <a href="{{ route('used.index', array_merge(request()->query(), ['cat' => $cat->slug])) }}" 
                       class="px-4 py-2 rounded-full text-xs font-bold transition shrink-0 {{ $selectedCategory === $cat->slug ? 'bg-zinc-950 dark:bg-white text-white dark:text-zinc-950' : 'bg-zinc-100 dark:bg-zinc-900 text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 border border-zinc-200 dark:border-zinc-800' }}">
                        {{ $cat->name }} ({{ $cat->products_count }})
                    </a>
                @endforeach
            </div>

            <!-- Products Grid (Image 1 Ultra Minimalist Monochrome Design) -->
            @if($items->isEmpty())
                <div class="text-center py-16 px-4 bg-zinc-50 dark:bg-zinc-900/50 rounded-3xl border border-zinc-200 dark:border-zinc-800 max-w-md mx-auto my-8">
                    <div class="w-14 h-14 rounded-2xl bg-zinc-200 dark:bg-zinc-800 text-zinc-500 flex items-center justify-center mx-auto mb-3 text-2xl">
                        📦
                    </div>
                    <h3 class="text-base font-bold text-zinc-900 dark:text-white mb-1">No items found</h3>
                    <p class="text-xs text-zinc-500 mb-4">Try clearing search terms or changing filters.</p>
                    <a href="{{ route('used.index') }}" class="px-5 py-2.5 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold">
                        Reset Filters
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                    @foreach($items as $item)
                        @php
                            $itemCondition = $item->getCustomField('condition', 'Gently Used');
                            $priceRupees = $item->base_price / 100;
                            $sellerCity = $item->vendor?->city ?? 'Local';
                            $sellerName = $item->vendor?->shop_name ?? 'Verified Owner';
                            $sellerPhone = $item->vendor?->contact_phone ?? '';
                            $firstImg = $item->images->first()?->path;
                        @endphp
                        <div class="group rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-zinc-400 dark:hover:border-zinc-600 overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-lg">
                            
                            <!-- 4:3 Image Container -->
                            <div class="relative w-full aspect-4/3 bg-zinc-100 dark:bg-zinc-800 overflow-hidden">
                                @if($firstImg)
                                    <img src="{{ asset('storage/' . $firstImg) }}" alt="{{ $item->title }}"
                                         class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                                         onerror="this.onerror=null; this.src='{{ asset('images/product-placeholder.svg') }}';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-400 text-3xl">
                                        📷
                                    </div>
                                @endif

                                <!-- Condition Tag Top-Left -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-black/70 backdrop-blur-md text-white border border-white/20">
                                        {{ $itemCondition }}
                                    </span>
                                </div>

                                <!-- Negotiable Tag Top-Right -->
                                @if($item->isNegotiable())
                                    <div class="absolute top-3 right-3">
                                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-emerald-500/90 text-white shadow-xs">
                                            Negotiable
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Content -->
                            <div class="p-4 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-baseline justify-between mb-1.5">
                                        <span class="text-xl font-black tracking-tight text-zinc-950 dark:text-white">
                                            ₹{{ number_format($priceRupees, 0) }}
                                        </span>
                                        <span class="text-[11px] font-semibold text-zinc-500 dark:text-zinc-400">
                                            {{ $item->category?->name ?? 'Pre-Owned' }}
                                        </span>
                                    </div>

                                    <h3 class="text-xs font-bold text-zinc-900 dark:text-zinc-100 group-hover:text-zinc-600 dark:group-hover:text-zinc-300 line-clamp-2 leading-snug">
                                        <a href="{{ route('product.show', $item->slug) }}">
                                            {{ $item->title }}
                                        </a>
                                    </h3>
                                </div>

                                <div class="pt-3 mt-3 border-t border-zinc-100 dark:border-zinc-800">
                                    <div class="flex items-center justify-between text-[11px] text-zinc-400 dark:text-zinc-500 mb-3">
                                        <span class="truncate">📍 {{ $sellerCity }}</span>
                                        <span class="font-bold">Verified Owner</span>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-2">
                                        @if(!$item->isSold() && $item->isNegotiable())
                                            <button type="button" 
                                                    @click="openOffer({
                                                        id: '{{ $item->id }}',
                                                        title: '{{ addslashes($item->title) }}',
                                                        price: '{{ number_format($priceRupees, 0) }}',
                                                        rawPrice: {{ $priceRupees }},
                                                        vendor: '{{ addslashes($sellerName) }}',
                                                        phone: '{{ $sellerPhone }}',
                                                        action: '{{ route('offers.store', $item->id) }}'
                                                    })"
                                                    class="flex-1 py-2.5 px-3 rounded-xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 text-xs font-black uppercase tracking-wider transition shadow-xs active:scale-95 cursor-pointer text-center">
                                                Make Offer
                                            </button>
                                        @endif
                                        <a href="{{ route('product.show', $item->slug) }}" 
                                           class="py-2.5 px-3 rounded-xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-zinc-800 dark:text-zinc-200 text-xs font-bold transition text-center {{ ($item->isSold() || !$item->isNegotiable()) ? 'flex-1' : '' }}">
                                            Details
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

        <!-- ========================================== -->
        <!-- TAB 2: MY ORDERS & NEGOTIATIONS (Image 1 Style) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'orders'" x-cloak class="max-w-2xl mx-auto py-4">
            <!-- Active vs Completed Tabs -->
            <div class="flex p-1 rounded-2xl bg-zinc-100 dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 mb-6">
                <button type="button" @click="orderTab = 'active'" 
                        :class="orderTab === 'active' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-500'"
                        class="flex-1 py-2.5 text-xs font-bold rounded-xl transition cursor-pointer">
                    Active Deals &amp; Negotiations (2)
                </button>
                <button type="button" @click="orderTab = 'completed'" 
                        :class="orderTab === 'completed' ? 'bg-white dark:bg-zinc-800 text-zinc-950 dark:text-white shadow-xs' : 'text-zinc-500'"
                        class="flex-1 py-2.5 text-xs font-bold rounded-xl transition cursor-pointer">
                    Completed (14)
                </button>
            </div>

            <div x-show="orderTab === 'active'" class="space-y-4">
                <!-- Order Item 1 -->
                <div class="p-4 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden shrink-0 flex items-center justify-center text-2xl">
                            🚗
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Offer Accepted</span>
                                <span class="text-[11px] text-zinc-400">#ORD-9021</span>
                            </div>
                            <h4 class="text-sm font-black text-zinc-950 dark:text-white mt-1">BMW M2 Series Coupe (2022)</h4>
                            <div class="text-xs font-bold text-zinc-600 dark:text-zinc-400 mt-0.5">Negotiated: ₹1,70,000 <span class="line-through text-zinc-400 font-normal">₹1,95,000</span></div>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <button type="button" @click="offerModal = true; offerStep = 5;" class="px-4 py-2 rounded-xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 text-xs font-bold transition">
                            Track Order
                        </button>
                    </div>
                </div>

                <!-- Order Item 2 -->
                <div class="p-4 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden shrink-0 flex items-center justify-center text-2xl">
                            💻
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Under Seller Review</span>
                                <span class="text-[11px] text-zinc-400">#OFF-4412</span>
                            </div>
                            <h4 class="text-sm font-black text-zinc-950 dark:text-white mt-1">Apple MacBook Pro 16" M2 Max</h4>
                            <div class="text-xs font-bold text-zinc-600 dark:text-zinc-400 mt-0.5">Your Offer: ₹1,45,000</div>
                        </div>
                    </div>
                    <div class="shrink-0 text-right">
                        <button type="button" @click="activeTab = 'messages'" class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-xs font-bold hover:bg-zinc-200 transition">
                            Chat Seller
                        </button>
                    </div>
                </div>
            </div>

            <div x-show="orderTab === 'completed'" x-cloak class="space-y-4">
                <div class="p-4 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 flex items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center gap-3.5">
                        <div class="w-16 h-16 rounded-2xl bg-zinc-100 dark:bg-zinc-800 overflow-hidden shrink-0 flex items-center justify-center text-2xl">
                            🏎️
                        </div>
                        <div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Delivered</span>
                            <h4 class="text-sm font-black text-zinc-950 dark:text-white mt-1">Porsche 911 GT3 RS Diecast</h4>
                            <div class="text-xs font-bold text-zinc-500">₹72,000 • Paid via Escrow</div>
                        </div>
                    </div>
                    <button type="button" class="px-4 py-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 text-xs font-bold">
                        Leave Review
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 3: P2P STORE CHAT (Image 1 Style) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'messages'" x-cloak class="max-w-3xl mx-auto py-4">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-zinc-200 dark:border-zinc-800 overflow-hidden shadow-xl">
                <!-- Chat Header -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-800/60 border-b border-zinc-200 dark:border-zinc-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-zinc-950 text-white font-black flex items-center justify-center text-xs">
                            BMW
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-black text-zinc-950 dark:text-white">BMW Official Store</h4>
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            </div>
                            <p class="text-[11px] text-zinc-400">Verified Dealership • Online Now</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" class="p-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200">
                            📞
                        </button>
                        <button type="button" class="p-2 rounded-xl bg-zinc-100 dark:bg-zinc-800 text-zinc-600 dark:text-zinc-300 hover:bg-zinc-200">
                            ℹ️
                        </button>
                    </div>
                </div>

                <!-- Chat Feed -->
                <div class="p-4 sm:p-6 space-y-4 max-h-[420px] overflow-y-auto bg-zinc-50/50 dark:bg-zinc-950/30">
                    <!-- Seller Message -->
                    <div class="flex items-start gap-2.5 max-w-[80%]">
                        <div class="w-7 h-7 rounded-full bg-zinc-950 text-white text-[10px] font-bold flex items-center justify-center shrink-0">B</div>
                        <div class="p-3.5 rounded-2xl rounded-tl-xs bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-xs text-zinc-800 dark:text-zinc-200 shadow-xs">
                            Hello! Welcome to BMW Pre-Owned Store. Is there anything we can assist you with today regarding the M2 Series?
                            <span class="block text-[9px] text-zinc-400 mt-1">10:45 AM</span>
                        </div>
                    </div>

                    <!-- Buyer Message with Counter Offer -->
                    <div class="flex items-start justify-end gap-2.5 ml-auto max-w-[80%]">
                        <div class="p-3.5 rounded-2xl rounded-tr-xs bg-zinc-950 text-white text-xs shadow-xs">
                            Good morning! I want to buy the BMW M2 Series. Can I get a discount? After reviewing the vehicle condition, my final cash offer is <strong>₹1,70,000</strong>.
                            <span class="block text-[9px] text-zinc-400 mt-1 text-right">10:48 AM • Sent</span>
                        </div>
                    </div>

                    <!-- Offer Confirmation Card Inside Chat -->
                    <div class="mx-auto max-w-sm p-4 rounded-2xl bg-zinc-100 dark:bg-zinc-800/80 border border-zinc-300 dark:border-zinc-700 text-center">
                        <div class="text-xs font-black text-zinc-900 dark:text-white mb-1">🎉 Offer Accepted by Seller</div>
                        <p class="text-[11px] text-zinc-500 dark:text-zinc-400 mb-3">Seller accepted your counter-offer of ₹1,70,000. Escrow holds funds safely until handover.</p>
                        <button type="button" @click="offerModal = true; offerStep = 5;" class="px-5 py-2 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold shadow-xs">
                            Proceed to Escrow Payment
                        </button>
                    </div>
                </div>

                <!-- Chat Input -->
                <div class="p-3 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 flex items-center gap-2">
                    <input type="text" placeholder="Type counter-offer or message..." 
                           class="flex-1 px-4 py-2.5 rounded-xl bg-zinc-100 dark:bg-zinc-800 border-none text-xs text-zinc-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-zinc-400">
                    <button type="button" class="px-4 py-2.5 rounded-xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 text-xs font-bold">
                        Send
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- TAB 4: ESCROW WALLET (Image 1 Style) -->
        <!-- ========================================== -->
        <div x-show="activeTab === 'wallet'" x-cloak class="max-w-md mx-auto py-4">
            <!-- Digital Black Matte Card -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-zinc-950 via-zinc-900 to-black p-6 sm:p-7 text-white shadow-2xl border border-zinc-800 mb-6">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-[10px] uppercase tracking-widest text-zinc-400 font-bold">K71 Escrow Wallet</span>
                        <div class="text-xs text-zinc-300 font-mono mt-0.5">•••• 8839</div>
                    </div>
                    <span class="text-xl font-black italic tracking-tighter text-zinc-200">VISA</span>
                </div>

                <div class="mb-4">
                    <span class="text-xs text-zinc-400">Available Balance</span>
                    <div class="text-3xl font-black tracking-tight text-white mt-0.5">
                        ₹ <span x-text="walletBalance.toLocaleString('en-IN')">2,99,677</span>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs text-zinc-400 pt-3 border-t border-zinc-800">
                    <span>HOLDER: {{ auth()->user()?->name ?? 'Verified Buyer' }}</span>
                    <span>EXP: 10/29</span>
                </div>
            </div>

            <!-- Quick Top-Up Chips -->
            <div class="p-5 rounded-3xl bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 mb-6 shadow-xs">
                <h4 class="text-xs font-black uppercase tracking-wider text-zinc-400 mb-3">Top Up E-Wallet</h4>
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button type="button" @click="walletBalance += 10000" class="py-2.5 px-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        +₹10,000
                    </button>
                    <button type="button" @click="walletBalance += 50000" class="py-2.5 px-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        +₹50,000
                    </button>
                    <button type="button" @click="walletBalance += 100000" class="py-2.5 px-3 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                        +₹1,00,000
                    </button>
                </div>
                <div class="text-[11px] text-zinc-400">Funds remain 100% in escrow until you receive and inspect the item. 0% broker fee.</div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- THE ICONIC "MAKE AN OFFER" MODAL (Exact Image 1 Replica) -->
        <!-- ======================================================== -->
        <div x-show="offerModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-md flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            
            <div @click.away="closeOffer()" 
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border border-zinc-200 dark:border-zinc-800 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Close Button -->
                <button type="button" @click="closeOffer()" class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xl font-bold p-1">
                    ✕
                </button>

                <!-- STEP 1: NUMERIC KEYPAD & OFFER AMOUNT (Image 1 Screen 3) -->
                <div x-show="offerStep === 1">
                    <div class="text-center mb-6">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-400">Make an Offer</span>
                        <div class="text-3xl sm:text-4xl font-black tracking-tight text-zinc-950 dark:text-white mt-1">
                            ₹ <span x-text="Number(offerAmount).toLocaleString('en-IN')">1,70,000</span>
                        </div>
                        <p class="text-[11px] text-zinc-400 mt-1">
                            Listed at ₹<span x-text="activeProduct ? activeProduct.price : '1,95,000'"></span>
                        </p>
                    </div>

                    <!-- Quick Preset Chips -->
                    <div class="grid grid-cols-3 gap-2 mb-6">
                        <button type="button" @click="setQuickAmount(activeProduct ? Math.round(activeProduct.rawPrice * 0.90) : 150000)" 
                                class="py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            -10%
                        </button>
                        <button type="button" @click="setQuickAmount(activeProduct ? Math.round(activeProduct.rawPrice * 0.85) : 170000)" 
                                class="py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            -15%
                        </button>
                        <button type="button" @click="setQuickAmount(activeProduct ? Math.round(activeProduct.rawPrice * 0.80) : 190000)" 
                                class="py-2 rounded-xl border border-zinc-200 dark:border-zinc-800 text-xs font-bold hover:bg-zinc-100 dark:hover:bg-zinc-800 transition">
                            -20%
                        </button>
                    </div>

                    <!-- Send Offer Button -->
                    <button type="button" @click="submitOffer()" 
                            class="w-full py-3.5 rounded-2xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 font-black text-xs uppercase tracking-wider transition shadow-lg mb-6 active:scale-95 cursor-pointer">
                        Send Offer
                    </button>

                    <!-- Interactive On-Screen Minimalist Keypad (Exact Screen 3) -->
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <template x-for="n in ['1', '2', '3', '4', '5', '6', '7', '8', '9']" :key="n">
                            <button type="button" @click="appendDigit(n)" 
                                    class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-bold text-lg text-zinc-950 dark:text-white transition active:scale-95">
                                <span x-text="n"></span>
                            </button>
                        </template>
                        <button type="button" @click="appendDigit('.')" class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-bold text-lg text-zinc-950 dark:text-white active:scale-95">.</button>
                        <button type="button" @click="appendDigit('0')" class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-bold text-lg text-zinc-950 dark:text-white active:scale-95">0</button>
                        <button type="button" @click="backspaceDigit()" class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-zinc-200 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-bold text-lg text-zinc-950 dark:text-white active:scale-95">⌫</button>
                    </div>
                </div>

                <!-- STEP 2: OFFER PROCESSING (Image 1 Screen 4) -->
                <div x-show="offerStep === 2" class="text-center py-10">
                    <div class="w-16 h-16 mx-auto mb-4 border-4 border-zinc-200 dark:border-zinc-800 border-t-zinc-950 dark:border-t-white rounded-full animate-spin"></div>
                    <h3 class="text-lg font-black text-zinc-950 dark:text-white mb-1">Your offer is being processed</h3>
                    <p class="text-xs text-zinc-500 max-w-xs mx-auto">Please check notification periodically to see if your offer was accepted by the seller.</p>
                </div>

                <!-- STEP 3: OFFER ACCEPTED (Image 1 Screen 6) -->
                <div x-show="offerStep === 3" class="text-center py-6">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto mb-4 text-3xl">
                        ✓
                    </div>
                    <h3 class="text-lg font-black text-zinc-950 dark:text-white mb-1">Congrats! Your offer has been accepted!</h3>
                    <p class="text-xs text-zinc-500 mb-6">Your offer of ₹<span x-text="Number(offerAmount).toLocaleString('en-IN')"></span> has been confirmed by the seller.</p>
                    <button type="button" @click="proceedToCheckout()" 
                            class="w-full py-3.5 rounded-2xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 font-black text-xs uppercase tracking-wider transition shadow-lg active:scale-95">
                        Proceed to Checkout
                    </button>
                </div>

                <!-- STEP 4: OFFER REJECTED (Image 1 Screen 5) -->
                <div x-show="offerStep === 4" class="text-center py-6">
                    <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto mb-4 text-3xl">
                        ✕
                    </div>
                    <h3 class="text-lg font-black text-zinc-950 dark:text-white mb-1">We're sorry, your offer was rejected</h3>
                    <p class="text-xs text-zinc-500 mb-6">The seller felt this offer was below their minimum threshold. Try submitting a slightly higher counter-offer.</p>
                    <button type="button" @click="offerStep = 1" 
                            class="w-full py-3.5 rounded-2xl bg-zinc-950 dark:bg-white text-white dark:text-zinc-950 font-black text-xs uppercase tracking-wider transition shadow-lg">
                        Make an Offer Again
                    </button>
                </div>

                <!-- STEP 5: CHECKOUT & SHIPPING (Image 1 Screens 7-9) -->
                <div x-show="offerStep === 5" class="py-2">
                    <h3 class="text-base font-black text-zinc-950 dark:text-white mb-4">Checkout &amp; Escrow Deposit</h3>
                    
                    <!-- Address Selection -->
                    <div class="mb-4">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Shipping Address</label>
                        <div class="space-y-2">
                            <label class="flex items-center justify-between p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 cursor-pointer text-xs"
                                   :class="selectedAddress === 'home' ? 'ring-2 ring-zinc-950 dark:ring-white bg-zinc-50 dark:bg-zinc-900' : ''">
                                <div class="flex items-center gap-2">
                                    <input type="radio" value="home" x-model="selectedAddress" class="sr-only">
                                    <span>🏠 Home (61480 Sunbrook Park, PC 5679)</span>
                                </div>
                                <span class="text-zinc-400 text-[10px]">Primary</span>
                            </label>
                            <label class="flex items-center justify-between p-3 rounded-2xl border border-zinc-200 dark:border-zinc-800 cursor-pointer text-xs"
                                   :class="selectedAddress === 'office' ? 'ring-2 ring-zinc-950 dark:ring-white bg-zinc-50 dark:bg-zinc-900' : ''">
                                <div class="flex items-center gap-2">
                                    <input type="radio" value="office" x-model="selectedAddress" class="sr-only">
                                    <span>🏢 Office (Tech Tower, Sector 44)</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Shipping Method -->
                    <div class="mb-5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-zinc-400 mb-2">Choose Shipping</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button type="button" @click="selectedShipping = 'truck'" 
                                    :class="selectedShipping === 'truck' ? 'border-zinc-950 dark:border-white bg-zinc-50 dark:bg-zinc-900 font-bold' : 'border-zinc-200 dark:border-zinc-800'"
                                    class="p-2.5 rounded-xl border text-left">
                                <div>🚚 Road Cargo</div>
                                <div class="text-[10px] text-zinc-400">₹500 • 2-3 Days</div>
                            </button>
                            <button type="button" @click="selectedShipping = 'air'" 
                                    :class="selectedShipping === 'air' ? 'border-zinc-950 dark:border-white bg-zinc-50 dark:bg-zinc-900 font-bold' : 'border-zinc-200 dark:border-zinc-800'"
                                    class="p-2.5 rounded-xl border text-left">
                                <div>✈️ Air Express</div>
                                <div class="text-[10px] text-zinc-400">₹1,200 • Next Day</div>
                            </button>
                        </div>
                    </div>

                    <!-- Total -->
                    <div class="p-3 rounded-2xl bg-zinc-100 dark:bg-zinc-900 flex items-center justify-between text-xs mb-5">
                        <span class="font-bold">Total with Escrow Guarantee</span>
                        <span class="font-black text-sm">₹<span x-text="(Number(offerAmount) + (selectedShipping === 'air' ? 1200 : 500)).toLocaleString('en-IN')"></span></span>
                    </div>

                    <button type="button" @click="alert('Escrow payment simulated successfully! Your funds are securely locked until delivery.'); closeOffer();" 
                            class="w-full py-3.5 rounded-2xl bg-zinc-950 hover:bg-zinc-800 dark:bg-white dark:hover:bg-zinc-200 text-white dark:text-zinc-950 font-black text-xs uppercase tracking-wider transition shadow-lg active:scale-95">
                        Pay &amp; Lock via Escrow
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-layouts.public>
