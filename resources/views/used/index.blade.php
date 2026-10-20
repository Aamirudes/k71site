<x-layouts.public 
    title="OLX Minimal & Premium Glassmorphic Pre-Owned Marketplace — K71 Used"
    metaDescription="Buy and sell verified pre-owned luxury electronics, iPhones, laptops, motor vehicles, and designer furniture on K71 Used. Real-time make-an-offer numeric keypad negotiation, verified owners, and escrow wallet."
>
    <style>
        .red-gradient {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 50%, #ef4444 100%);
        }
        .red-glow:hover {
            box-shadow: 0 10px 25px -5px rgba(220, 38, 38, 0.4);
        }
        .glass-olx {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(220, 38, 38, 0.15);
        }
        .dark .glass-olx {
            background: rgba(24, 24, 27, 0.92);
            border: 1px solid rgba(220, 38, 38, 0.25);
        }
    </style>

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
                if (Number(this.offerAmount) >= (this.activeProduct ? this.activeProduct.rawPrice * 0.75 : 50000)) {
                    this.offerStep = 3; // accepted
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
    }" class="min-h-screen text-zinc-900 dark:text-zinc-100 transition-colors duration-300 pb-20 font-sans">

        <!-- ======================================================== -->
        <!-- FLOATING OLX '+ SELL NOW' CRIMSON ACTION BUTTON          -->
        <!-- ======================================================== -->
        <div class="fixed bottom-6 right-6 z-40 sm:bottom-8 sm:right-8">
            <a href="{{ route('vendor.register') }}" 
               class="group flex items-center gap-2.5 px-6 py-4 rounded-full bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white font-black text-xs uppercase tracking-wider shadow-2xl hover:scale-105 active:scale-95 transition-all duration-300 border-2 border-white ring-4 ring-red-500/20">
                <span class="w-6 h-6 rounded-full bg-white text-red-600 flex items-center justify-center text-sm font-black group-hover:rotate-90 transition-transform duration-300 shadow-sm">+</span>
                <span>Post Your Ad Free</span>
            </a>
        </div>

        <!-- ======================================================== -->
        <!-- 1. OLX RED & WHITE SIGNATURE TOP BAR                     -->
        <!-- ======================================================== -->
        <div class="mb-6 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 p-4 rounded-3xl red-gradient text-white shadow-xl border border-red-400/40">
            <div class="flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-white text-red-600 flex items-center justify-center font-black text-2xl shrink-0 shadow-md">
                    🏷️
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-black uppercase tracking-wider bg-black/30 text-white px-2 py-0.5 rounded-md">
                            used.{{ config('k71.root_domain', 'k71.in') }}
                        </span>
                        <span class="text-[11px] text-red-100 font-semibold hidden sm:inline">100% Inspected Direct Peer-to-Peer Marketplace</span>
                    </div>
                    <h1 class="text-base sm:text-lg font-black tracking-wide text-white mt-0.5">
                        K71 Pre-Owned Verified Deals &bull; Real-Time Price Negotiation
                    </h1>
                </div>
            </div>

            <!-- Navigation Tabs (Marketplace, Orders, Chats, Escrow Wallet) -->
            <div class="flex items-center p-1 rounded-2xl bg-black/25 backdrop-blur-md border border-white/20 text-xs font-bold w-full md:w-auto overflow-x-auto no-scrollbar">
                <button type="button" @click="activeTab = 'browse'" 
                        :class="activeTab === 'browse' ? 'bg-white text-red-600 shadow-md' : 'text-red-100 hover:text-white'"
                        class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 font-black uppercase tracking-wider cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                    <span>Marketplace</span>
                </button>
                <button type="button" @click="activeTab = 'orders'" 
                        :class="activeTab === 'orders' ? 'bg-white text-red-600 shadow-md' : 'text-red-100 hover:text-white'"
                        class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 font-black uppercase tracking-wider cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    <span>Orders ({{ $items->total() > 0 ? 2 : 0 }})</span>
                </button>
                <button type="button" @click="activeTab = 'messages'" 
                        :class="activeTab === 'messages' ? 'bg-white text-red-600 shadow-md' : 'text-red-100 hover:text-white'"
                        class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 font-black uppercase tracking-wider cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>Store Chats</span>
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                </button>
                <button type="button" @click="activeTab = 'wallet'" 
                        :class="activeTab === 'wallet' ? 'bg-white text-red-600 shadow-md' : 'text-red-100 hover:text-white'"
                        class="px-4 py-2 rounded-xl transition flex items-center gap-1.5 shrink-0 font-black uppercase tracking-wider cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span>Escrow Wallet</span>
                </button>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 1: BROWSE MARKETPLACE (OLX MINIMAL & GLASSMORPHIC)   -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'browse'" x-transition:enter="transition ease-out duration-200">
            <!-- Search & Filters -->
            <div class="mb-6 grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                <form action="{{ route('used.index') }}" method="GET" class="md:col-span-8 relative">
                    <div class="absolute left-4 top-4 text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" 
                           placeholder="Search pre-owned iPhone 15 Pro, BMW M2, MacBook M3, Sony A7IV, Rolex, Teak Sofa..." 
                           class="w-full pl-12 pr-32 py-4 rounded-3xl bg-white dark:bg-zinc-900 border-2 border-red-200 dark:border-zinc-800 text-sm font-semibold focus:ring-4 focus:ring-red-500/20 focus:border-red-600 focus:outline-none transition shadow-sm">
                    <button type="submit" class="absolute right-2.5 top-2.5 px-6 py-2.5 rounded-2xl red-gradient text-white text-xs font-black uppercase tracking-wider transition shadow-md cursor-pointer hover:opacity-95">
                        Search
                    </button>
                </form>

                <div class="md:col-span-4 flex items-center gap-2 justify-end">
                    <!-- Negotiable Only Toggle Pill -->
                    <a href="{{ route('used.index', array_merge(request()->query(), ['negotiable' => $negotiableOnly ? 0 : 1])) }}" 
                       class="px-4 py-3.5 rounded-2xl border text-xs font-black uppercase tracking-wider transition flex items-center gap-2 shrink-0 {{ $negotiableOnly ? 'bg-red-600 text-white border-red-600 shadow-md' : 'bg-white dark:bg-zinc-900 text-zinc-700 dark:text-zinc-300 border-red-200 dark:border-zinc-800 hover:border-red-400' }}">
                        <span>🤝 Negotiable Only</span>
                    </a>

                    @if($availableCities->isNotEmpty())
                        <div class="relative">
                            <select onchange="window.location.href = this.value;" 
                                    class="appearance-none pl-4 pr-9 py-3.5 rounded-2xl bg-white dark:bg-zinc-900 border-2 border-red-200 dark:border-zinc-800 text-xs font-black uppercase tracking-wider text-zinc-800 dark:text-zinc-200 focus:outline-none cursor-pointer">
                                <option value="{{ route('used.index', array_merge(request()->query(), ['city' => ''])) }}">📍 All Cities</option>
                                @foreach($availableCities as $c)
                                    <option value="{{ route('used.index', array_merge(request()->query(), ['city' => $c])) }}" {{ ($city ?? '') === $c ? 'selected' : '' }}>
                                        {{ $c }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            <!-- OLX Condition Filter Pills (Like New, Gently Used, Fair) -->
            <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-3 mb-6">
                <a href="{{ route('used.index', array_merge(request()->query(), ['condition' => ''])) }}" 
                   class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 {{ empty($condition) ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300' }}">
                    All Items
                </a>
                <a href="{{ route('used.index', array_merge(request()->query(), ['condition' => 'Like New'])) }}" 
                   class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 {{ ($condition ?? '') === 'Like New' ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400' }}">
                    💎 Like New (10/10)
                </a>
                <a href="{{ route('used.index', array_merge(request()->query(), ['condition' => 'Gently Used'])) }}" 
                   class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 {{ ($condition ?? '') === 'Gently Used' ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400' }}">
                    ⚡ Gently Used
                </a>
                <a href="{{ route('used.index', array_merge(request()->query(), ['condition' => 'Heavily Used'])) }}" 
                   class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-wider transition shrink-0 {{ ($condition ?? '') === 'Heavily Used' ? 'bg-red-600 text-white shadow-md' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 text-zinc-700 dark:text-zinc-300 hover:border-red-400' }}">
                    🏷️ Heavily Used / Value
                </a>
            </div>

            <!-- Items Feed Section -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h3 class="text-base sm:text-lg font-black uppercase tracking-wider text-zinc-900 dark:text-white flex items-center gap-2">
                        <span>Verified Pre-Owned Gear</span>
                        <span class="px-2.5 py-0.5 rounded-md bg-red-100 text-red-700 text-[10px] font-black uppercase">ESCROW PROTECTED</span>
                    </h3>
                    <p class="text-xs text-zinc-400">Direct peer-to-peer deals with real-time counter-offer negotiation</p>
                </div>
            </div>

            @if($items->isEmpty())
                <div class="text-center py-16 px-4 glass-olx rounded-3xl max-w-md mx-auto my-8">
                    <div class="w-16 h-16 rounded-2xl bg-red-100 dark:bg-red-950/40 text-red-600 flex items-center justify-center mx-auto mb-3 text-3xl">
                        📦
                    </div>
                    <h3 class="text-base font-black text-zinc-900 dark:text-white mb-1">No pre-owned listings found</h3>
                    <p class="text-xs text-zinc-500 mb-4">Try clearing filters or search for iPhone, MacBook, or camera.</p>
                    <a href="{{ route('used.index') }}" class="px-6 py-2.5 rounded-2xl red-gradient text-white text-xs font-black uppercase tracking-wider shadow-md">
                        View All Listings
                    </a>
                </div>
            @else
                <!-- Pre-Owned Listings Grid (Insane Glassmorphic OLX Style) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 mb-12">
                    @foreach($items as $item)
                        @php
                            $priceRupees = $item->base_price / 100;
                            $firstImg = $item->images->first()?->path;
                            $condition = $item->getCustomField('condition', 'Like New');
                            $usageDuration = $item->getCustomField('usage_duration', '8 months');
                            $city = $item->vendor?->city ?? 'Metro Area';
                            $sellerName = $item->vendor?->shop_name ?? 'Verified Owner';
                            $imgUrl = $firstImg ? asset('storage/' . $firstImg) : 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=500&q=80';
                        @endphp
                        <div class="group rounded-3xl glass-olx overflow-hidden flex flex-col justify-between transition-all duration-300 hover:shadow-2xl hover:-translate-y-1.5 relative border border-red-500/20">
                            
                            <!-- Image Container with Condition Badge & Verified Seller Tag -->
                            <div class="relative w-full aspect-4/3 bg-zinc-100 dark:bg-zinc-800 overflow-hidden cursor-pointer"
                                 @click="openOffer({
                                     id: '{{ $item->id }}',
                                     title: '{{ addslashes($item->title) }}',
                                     price: '{{ number_format($priceRupees, 0) }}',
                                     rawPrice: {{ $priceRupees }},
                                     condition: '{{ $condition }}',
                                     city: '{{ addslashes($city) }}',
                                     seller: '{{ addslashes($sellerName) }}',
                                     image: '{{ $imgUrl }}',
                                     desc: '{{ addslashes($item->description ?? 'Inspected authentic device in spotless working condition. Bill and box included.') }}'
                                 })">
                                <img src="{{ $imgUrl }}" alt="{{ $item->title }}"
                                     class="w-full h-full object-cover group-hover:scale-108 transition duration-700 ease-out"
                                     onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=500&q=80';">

                                <!-- Condition Pill Top-Left -->
                                <div class="absolute top-3 left-3">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider bg-red-600 text-white shadow-md">
                                        {{ $condition }}
                                    </span>
                                </div>

                                <!-- Verified Owner Badge Top-Right -->
                                <div class="absolute top-3 right-3 px-2 py-0.5 rounded-lg bg-black/70 backdrop-blur-md text-white text-[10px] font-black flex items-center gap-1">
                                    <span class="text-emerald-400">🛡️</span> Verified
                                </div>

                                <!-- Usage Time Bottom-Left -->
                                <div class="absolute bottom-3 left-3 px-2.5 py-1 rounded-lg bg-black/70 backdrop-blur-md text-white text-[10px] font-bold flex items-center gap-1">
                                    <span>⏱️ {{ $usageDuration }} used</span>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="p-4 sm:p-5 flex-1 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center justify-between text-[11px] text-zinc-400 dark:text-zinc-500 mb-1">
                                        <span class="truncate font-semibold">{{ $sellerName }}</span>
                                        <span>📍 {{ $city }}</span>
                                    </div>

                                    <h4 class="text-sm sm:text-base font-black text-zinc-950 dark:text-white group-hover:text-red-600 line-clamp-2 leading-snug cursor-pointer transition-colors"
                                        @click="openOffer({
                                            id: '{{ $item->id }}',
                                            title: '{{ addslashes($item->title) }}',
                                            price: '{{ number_format($priceRupees, 0) }}',
                                            rawPrice: {{ $priceRupees }},
                                            condition: '{{ $condition }}',
                                            city: '{{ addslashes($city) }}',
                                            seller: '{{ addslashes($sellerName) }}',
                                            image: '{{ $imgUrl }}',
                                            desc: '{{ addslashes($item->description ?? 'Inspected authentic device in spotless working condition. Bill and box included.') }}'
                                        })">
                                        {{ $item->title }}
                                    </h4>

                                    <!-- Inspection Checklist -->
                                    <div class="mt-2 text-[10px] text-zinc-500 space-y-0.5">
                                        <div class="flex items-center gap-1">
                                            <span class="text-emerald-600 font-bold">✓</span> Battery Health &amp; Performance Tested
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-emerald-600 font-bold">✓</span> Escrow Inspection Handover Guarantee
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-4 mt-3 border-t border-red-100 dark:border-zinc-800 flex items-center justify-between">
                                    <div>
                                        <span class="text-[10px] text-zinc-400 uppercase font-black block">Asking Price</span>
                                        <span class="text-base sm:text-lg font-black text-red-600 dark:text-red-400">
                                            ₹{{ number_format($priceRupees, 0) }}
                                        </span>
                                    </div>

                                    <!-- Make an Offer Trigger Button -->
                                    <button type="button" 
                                            @click="openOffer({
                                                id: '{{ $item->id }}',
                                                title: '{{ addslashes($item->title) }}',
                                                price: '{{ number_format($priceRupees, 0) }}',
                                                rawPrice: {{ $priceRupees }},
                                                condition: '{{ $condition }}',
                                                city: '{{ addslashes($city) }}',
                                                seller: '{{ addslashes($sellerName) }}',
                                                image: '{{ $imgUrl }}',
                                                desc: '{{ addslashes($item->description ?? 'Inspected authentic device in spotless working condition. Bill and box included.') }}'
                                            })"
                                            class="px-4 py-2 rounded-2xl red-gradient text-white text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-md shadow-red-500/25 cursor-pointer hover:opacity-95 flex items-center gap-1">
                                        <span>Make Offer</span>
                                    </button>
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

        <!-- ======================================================== -->
        <!-- TAB 2: MY ORDERS                                         -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'orders'" x-cloak class="max-w-2xl mx-auto py-6">
            <div class="p-5 rounded-3xl glass-olx shadow-sm border border-red-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md">UNDER ESCROW INSPECTION</span>
                    <span class="text-sm font-black text-red-600">₹1,70,000</span>
                </div>
                <h4 class="text-sm font-black text-zinc-950 dark:text-white">BMW M2 Series Performance Coupe</h4>
                <p class="text-xs text-zinc-400 mt-1">Order #ESC-77492 &bull; Carrier Dispatched &bull; Inspect and release funds within 48h</p>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 3: STORE CHATS                                       -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'messages'" x-cloak class="max-w-2xl mx-auto py-6">
            <div class="rounded-3xl glass-olx overflow-hidden shadow-xl border border-red-200">
                <div class="p-4 bg-red-600 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-white text-red-600 font-black flex items-center justify-center">B</div>
                        <div>
                            <div class="text-xs font-black uppercase">BMW Pre-Owned Store</div>
                            <div class="text-[10px] text-red-100">● Online Now</div>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-white bg-black/30 px-3 py-1 rounded-xl">Negotiation Channel</span>
                </div>
                <div class="p-6 space-y-4">
                    <div class="p-4 rounded-2xl bg-zinc-100 dark:bg-zinc-800 text-xs">
                        Hello! Welcome to BMW Pre-Owned Store. Is there anything we can assist you with today regarding the M2 Series?
                    </div>
                    <div class="p-4 rounded-2xl bg-red-600 text-white text-xs ml-auto max-w-[80%]">
                        Good morning! I want to buy the BMW M2 Series. My final counter-offer is <strong>₹1,70,000</strong>.
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 text-center text-xs">
                        🎉 Seller accepted your counter offer! Escrow holds funds safely until handover.
                    </div>
                </div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- TAB 4: ESCROW WALLET                                     -->
        <!-- ======================================================== -->
        <div x-show="activeTab === 'wallet'" x-cloak class="max-w-md mx-auto py-6">
            <div class="relative overflow-hidden rounded-3xl red-gradient p-7 text-white shadow-2xl mb-6 border border-red-300">
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <span class="text-[10px] uppercase tracking-widest text-red-200 font-bold">K71 Peer Escrow Card</span>
                        <div class="text-xs text-white font-mono mt-0.5">•••• 8839</div>
                    </div>
                    <span class="text-xl font-black italic tracking-tighter text-white">VISA</span>
                </div>
                <div class="mb-4">
                    <span class="text-xs text-red-100">Available Balance</span>
                    <div class="text-3xl font-black tracking-tight text-white mt-0.5">
                        ₹ <span x-text="walletBalance.toLocaleString('en-IN')">2,99,677</span>
                    </div>
                </div>
                <div class="flex items-center justify-between text-xs text-red-100 pt-3 border-t border-red-400/50">
                    <span>HOLDER: {{ auth()->user()?->name ?? 'Verified Buyer' }}</span>
                    <span>EXP: 10/29</span>
                </div>
            </div>

            <div class="p-5 rounded-3xl glass-olx shadow-sm">
                <h4 class="text-xs font-black uppercase tracking-wider text-zinc-400 mb-3">Top Up E-Wallet</h4>
                <div class="grid grid-cols-3 gap-2 mb-4">
                    <button type="button" @click="walletBalance += 10000" class="py-2.5 px-3 rounded-xl border border-red-200 dark:border-zinc-800 text-xs font-bold hover:bg-red-50 dark:hover:bg-zinc-800 transition">
                        +₹10,000
                    </button>
                    <button type="button" @click="walletBalance += 50000" class="py-2.5 px-3 rounded-xl border border-red-200 dark:border-zinc-800 text-xs font-bold hover:bg-red-50 dark:hover:bg-zinc-800 transition">
                        +₹50,000
                    </button>
                    <button type="button" @click="walletBalance += 100000" class="py-2.5 px-3 rounded-xl border border-red-200 dark:border-zinc-800 text-xs font-bold hover:bg-red-50 dark:hover:bg-zinc-800 transition">
                        +₹1,00,000
                    </button>
                </div>
                <div class="text-[11px] text-zinc-400">Funds remain 100% in escrow until you receive and inspect the item. 0% broker fee.</div>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- THE ICONIC "MAKE AN OFFER" MODAL (OLX RED KEYPAD)       -->
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
                 class="bg-white dark:bg-zinc-950 rounded-3xl max-w-sm w-full p-6 sm:p-7 shadow-2xl border-2 border-red-200 dark:border-zinc-800 relative text-zinc-950 dark:text-white"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Close Button -->
                <button type="button" @click="closeOffer()" class="absolute top-4 right-4 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 text-xl font-bold p-1 cursor-pointer">
                    ✕
                </button>

                <!-- STEP 1: NUMERIC KEYPAD & OFFER AMOUNT -->
                <div x-show="offerStep === 1">
                    <div class="text-center mb-6">
                        <span class="text-[11px] font-black uppercase tracking-wider text-red-600 dark:text-red-400">OLX Make an Offer</span>
                        <div class="text-3xl sm:text-4xl font-black tracking-tight text-zinc-950 dark:text-white mt-1">
                            ₹ <span x-text="Number(offerAmount).toLocaleString('en-IN')">1,70,000</span>
                        </div>
                        <p class="text-[11px] text-zinc-400 mt-1">
                            Listed asking price: ₹<span x-text="activeProduct ? activeProduct.price : '1,95,000'"></span>
                        </p>
                    </div>

                    <!-- Quick Preset Chips -->
                    <div class="grid grid-cols-3 gap-2 mb-6">
                        <button type="button" @click="setQuickAmount(activeProduct ? Math.round(activeProduct.rawPrice * 0.90) : 150000)" 
                                class="py-2.5 rounded-2xl border border-red-200 dark:border-zinc-800 text-xs font-black text-red-600 hover:bg-red-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                            -10%
                        </button>
                        <button type="button" @click="setQuickAmount(activeProduct ? Math.round(activeProduct.rawPrice * 0.85) : 170000)" 
                                class="py-2.5 rounded-2xl border border-red-200 dark:border-zinc-800 text-xs font-black text-red-600 hover:bg-red-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                            -15%
                        </button>
                        <button type="button" @click="setQuickAmount(activeProduct ? Math.round(activeProduct.rawPrice * 0.80) : 190000)" 
                                class="py-2.5 rounded-2xl border border-red-200 dark:border-zinc-800 text-xs font-black text-red-600 hover:bg-red-50 dark:hover:bg-zinc-800 transition cursor-pointer">
                            -20%
                        </button>
                    </div>

                    <!-- Send Offer Button -->
                    <button type="button" @click="submitOffer()" 
                            class="w-full py-4 rounded-2xl red-gradient text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-red-600/30 mb-6 active:scale-95 cursor-pointer hover:opacity-95">
                        Submit Cash Offer
                    </button>

                    <!-- Interactive On-Screen Minimalist Keypad -->
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <template x-for="n in ['1', '2', '3', '4', '5', '6', '7', '8', '9']" :key="n">
                            <button type="button" @click="appendDigit(n)" 
                                    class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-red-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-black text-lg text-zinc-950 dark:text-white transition active:scale-95 border border-zinc-200 dark:border-zinc-800 cursor-pointer">
                                <span x-text="n"></span>
                            </button>
                        </template>
                        <button type="button" @click="appendDigit('.')" class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-red-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-black text-lg text-zinc-950 dark:text-white active:scale-95 border border-zinc-200 dark:border-zinc-800 cursor-pointer">.</button>
                        <button type="button" @click="appendDigit('0')" class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-red-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-black text-lg text-zinc-950 dark:text-white active:scale-95 border border-zinc-200 dark:border-zinc-800 cursor-pointer">0</button>
                        <button type="button" @click="backspaceDigit()" class="py-3.5 rounded-2xl bg-zinc-100 hover:bg-red-50 dark:bg-zinc-900 dark:hover:bg-zinc-800 font-black text-lg text-zinc-950 dark:text-white active:scale-95 border border-zinc-200 dark:border-zinc-800 cursor-pointer">⌫</button>
                    </div>
                </div>

                <!-- STEP 2: PROCESSING -->
                <div x-show="offerStep === 2" class="text-center py-10">
                    <div class="w-16 h-16 mx-auto mb-4 border-4 border-red-200 dark:border-zinc-800 border-t-red-600 rounded-full animate-spin"></div>
                    <h3 class="text-lg font-black text-zinc-950 dark:text-white mb-1">Evaluating Offer...</h3>
                    <p class="text-xs text-zinc-500 max-w-xs mx-auto">Evaluating seller minimum reserve price and instant counter algorithms.</p>
                </div>

                <!-- STEP 3: ACCEPTED -->
                <div x-show="offerStep === 3" class="text-center py-6">
                    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4 text-3xl font-black shadow-lg">
                        ✓
                    </div>
                    <h3 class="text-xl font-black text-zinc-950 dark:text-white mb-1">Offer Accepted!</h3>
                    <p class="text-xs text-zinc-500 mb-6">Your offer of ₹<span x-text="Number(offerAmount).toLocaleString('en-IN')"></span> has been confirmed by the seller.</p>
                    <button type="button" @click="proceedToCheckout()" 
                            class="w-full py-4 rounded-2xl red-gradient text-white font-black text-xs uppercase tracking-wider transition shadow-lg active:scale-95 cursor-pointer">
                        Proceed to Escrow Checkout
                    </button>
                </div>

                <!-- STEP 4: REJECTED -->
                <div x-show="offerStep === 4" class="text-center py-6">
                    <div class="w-16 h-16 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-3xl font-black">
                        ✕
                    </div>
                    <h3 class="text-xl font-black text-zinc-950 dark:text-white mb-1">Offer Declined</h3>
                    <p class="text-xs text-zinc-500 mb-6">The seller felt this offer was below their minimum threshold. Try submitting a slightly higher counter-offer.</p>
                    <button type="button" @click="offerStep = 1" 
                            class="w-full py-4 rounded-2xl red-gradient text-white font-black text-xs uppercase tracking-wider transition shadow-lg cursor-pointer">
                        Make an Offer Again
                    </button>
                </div>

                <!-- STEP 5: CHECKOUT -->
                <div x-show="offerStep === 5" class="py-2">
                    <h3 class="text-base font-black text-zinc-950 dark:text-white mb-4">Escrow Checkout</h3>
                    
                    <div class="mb-4">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-zinc-400 mb-2">Shipping Destination</label>
                        <div class="space-y-2">
                            <label class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-red-200 dark:border-zinc-800 cursor-pointer text-xs"
                                   :class="selectedAddress === 'home' ? 'ring-2 ring-red-600 bg-red-50/50 dark:bg-zinc-900' : ''">
                                <div class="flex items-center gap-2">
                                    <input type="radio" value="home" x-model="selectedAddress" class="sr-only">
                                    <span>🏠 Home (61480 Sunbrook Park, PC 5679)</span>
                                </div>
                                <span class="text-red-600 font-bold text-[10px]">Primary</span>
                            </label>
                            <label class="flex items-center justify-between p-3.5 rounded-2xl border-2 border-zinc-200 dark:border-zinc-800 cursor-pointer text-xs"
                                   :class="selectedAddress === 'office' ? 'ring-2 ring-red-600 bg-red-50/50 dark:bg-zinc-900' : ''">
                                <div class="flex items-center gap-2">
                                    <input type="radio" value="office" x-model="selectedAddress" class="sr-only">
                                    <span>🏢 Office (Tech Tower, Sector 44)</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="block text-[11px] font-black uppercase tracking-wider text-zinc-400 mb-2">Delivery Speed</label>
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <button type="button" @click="selectedShipping = 'truck'" 
                                    :class="selectedShipping === 'truck' ? 'border-red-600 bg-red-50 dark:bg-zinc-900 font-black' : 'border-zinc-200 dark:border-zinc-800'"
                                    class="p-3 rounded-2xl border-2 text-left cursor-pointer">
                                <div>🚚 Road Cargo</div>
                                <div class="text-[10px] text-zinc-400">₹500 • 2-3 Days</div>
                            </button>
                            <button type="button" @click="selectedShipping = 'air'" 
                                    :class="selectedShipping === 'air' ? 'border-red-600 bg-red-50 dark:bg-zinc-900 font-black' : 'border-zinc-200 dark:border-zinc-800'"
                                    class="p-3 rounded-2xl border-2 text-left cursor-pointer">
                                <div>✈️ Air Express</div>
                                <div class="text-[10px] text-zinc-400">₹1,200 • Next Day</div>
                            </button>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-2xl bg-red-50 dark:bg-zinc-900 border border-red-200 dark:border-zinc-800 flex items-center justify-between text-xs mb-5">
                        <span class="font-bold">Total with Escrow Guarantee</span>
                        <span class="font-black text-sm text-red-600 dark:text-red-400">₹<span x-text="(Number(offerAmount) + (selectedShipping === 'air' ? 1200 : 500)).toLocaleString('en-IN')"></span></span>
                    </div>

                    <button type="button" @click="alert('Escrow payment simulated successfully! Your funds are securely locked until delivery.'); closeOffer();" 
                            class="w-full py-4 rounded-2xl red-gradient text-white font-black text-xs uppercase tracking-wider transition shadow-lg shadow-red-600/30 active:scale-95 cursor-pointer">
                        Lock Funds in Escrow
                    </button>
                </div>

            </div>
        </div>

    </div>
</x-layouts.public>
