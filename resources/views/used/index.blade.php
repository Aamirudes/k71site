<x-layouts.public 
    title="K71 Classifieds & Pre-Loved Marketplace — Kishangarh, Rajasthan"
    metaDescription="Buy & sell used cars, iPhones, second-hand furniture, bikes, laptops and appliances in Kishangarh, Ajmer & Rajasthan on K71 Used."
>
    @php
        $selectedCity = $city ?? 'Kishangarh, Rajasthan';
        $openSlug = request()->query('ad', $openAdSlug ?? '');
    @endphp

    <div x-data="{
        activeTab: 'home', // 'home', 'chats', 'myads', 'account'
        selectedCity: '{{ $selectedCity }}',
        locationModal: false,
        citySearch: '',
        searchQuery: '{{ $search ?? '' }}',
        selectedCategory: '{{ $selectedCategory ?? 'all' }}',
        activeProduct: null,
        detailModal: false,
        galleryIndex: 0,
        activeProductViews: 6,
        
        // Wishlist
        wishlist: JSON.parse(localStorage.getItem('k71_used_wishlist') || '[]'),
        isWishlisted(id) {
            return this.wishlist.includes(Number(id));
        },
        toggleWishlist(id) {
            id = Number(id);
            if (this.wishlist.includes(id)) {
                this.wishlist = this.wishlist.filter(x => x !== id);
                if (window.showToast) window.showToast('Removed from favorites', false);
            } else {
                this.wishlist.push(id);
                if (window.showToast) window.showToast('Saved to your favorites! ❤️');
            }
            localStorage.setItem('k71_used_wishlist', JSON.stringify(this.wishlist));
        },
        wishlistFilterActive: false,

        // Call Modal
        callModal: false,
        activeSellerPhone: '+919549311580',
        activeSellerName: 'Yusuf khan',

        // Chat Drawer
        chatDrawer: false,
        chatMessages: [
            { sender: 'seller', text: 'Namaste! Yes, the item is still available for inspection in Kishangarh. Let me know if you want to see it today.', time: '10:45 AM' }
        ],
        newChatMessage: '',
        sendChatMessage(msgText = null) {
            const text = (msgText || this.newChatMessage).trim();
            if (!text) return;
            this.chatMessages.push({ sender: 'buyer', text: text, time: 'Just now' });
            this.newChatMessage = '';
            setTimeout(() => {
                const autoReplies = [
                    'Price is slightly negotiable on table. When can you come to Shivaji Nagar?',
                    'All original bill, box and accessories are available. Condition is 100% spotless.',
                    'Sure! You can inspect it thoroughly. I am available near Bus Stand, Kishangarh.',
                    'Please give me a call at +91 95493 11580 so we can finalize the deal quickly.'
                ];
                const reply = autoReplies[Math.floor(Math.random() * autoReplies.length)];
                this.chatMessages.push({ sender: 'seller', text: reply, time: 'Just now' });
            }, 900);
        },

        // Make Offer Keypad
        offerModal: false,
        offerAmount: '20000',
        offerStatus: 'input', // 'input', 'submitting', 'accepted', 'counter'
        openOfferModal(item) {
            this.activeProduct = item;
            this.offerAmount = String(Math.round((item.base_price / 100) * 0.9));
            this.offerStatus = 'input';
            this.offerModal = true;
        },
        appendDigit(d) {
            if (this.offerAmount === '0') {
                this.offerAmount = d;
            } else if (this.offerAmount.length < 8) {
                this.offerAmount += d;
            }
        },
        backspaceDigit() {
            if (this.offerAmount.length > 1) {
                this.offerAmount = this.offerAmount.slice(0, -1);
            } else {
                this.offerAmount = '0';
            }
        },
        submitOffer() {
            this.offerStatus = 'submitting';
            setTimeout(() => {
                const numericOffer = Number(this.offerAmount);
                const baseNumeric = this.activeProduct ? (this.activeProduct.base_price / 100) : 22000;
                if (numericOffer >= baseNumeric * 0.8) {
                    this.offerStatus = 'accepted';
                } else {
                    this.offerStatus = 'counter';
                }
            }, 1000);
        },

        // Sell Ad Modal
        sellModal: false,
        sellStep: 1,
        adForm: {
            title: '',
            category: 'Mobiles',
            price: '',
            brand: '',
            condition: 'Like New',
            city: 'Kishangarh, Rajasthan',
            phone: '+91 95493 11580',
            description: '',
            image_url: 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80'
        },
        userAds: JSON.parse(localStorage.getItem('k71_my_ads') || '[]'),
        publishAd() {
            if (!this.adForm.title || !this.adForm.price) {
                alert('Please provide title and price');
                return;
            }
            const newAd = {
                id: Date.now(),
                title: this.adForm.title,
                price: Number(this.adForm.price),
                category: this.adForm.category,
                city: this.adForm.city,
                condition: this.adForm.condition,
                image: this.adForm.image_url,
                views: 1,
                date: 'Just now'
            };
            this.userAds.unshift(newAd);
            localStorage.setItem('k71_my_ads', JSON.stringify(this.userAds));
            this.sellModal = false;
            if (window.showToast) window.showToast('🎉 Your Ad has been posted successfully and is now LIVE!');
            this.activeTab = 'myads';
        },

        // Verified Modal
        verifiedModal: false,
        isUserVerified: false,
        activateVerification() {
            this.isUserVerified = true;
            this.verifiedModal = false;
            if (window.showToast) window.showToast('🛡️ Congratulations! Your Trust Badge has been activated.');
        },

        // Voice Search Simulation
        voiceListening: false,
        startVoiceSearch() {
            this.voiceListening = true;
            setTimeout(() => {
                this.voiceListening = false;
                this.searchQuery = 'iPhone 14';
                if (window.showToast) window.showToast('🎙️ Searched for: iPhone 14');
            }, 1600);
        },

        // Open Product Details
        openDetail(product) {
            this.activeProduct = product;
            this.galleryIndex = 0;
            this.activeProductViews = (product.custom_fields && product.custom_fields.views_past_hour) ? product.custom_fields.views_past_hour : 6;
            this.activeSellerName = (product.vendor && product.vendor.shop_name) ? product.vendor.shop_name : 'Yusuf khan';
            this.activeSellerPhone = (product.vendor && product.vendor.phone) ? product.vendor.phone : '+91 95493 11580';
            this.detailModal = true;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        init() {
            @if($openSlug)
                const targetSlug = '{{ $openSlug }}';
                const foundItem = @json($items->items()).find(i => i.slug === targetSlug);
                if (foundItem) {
                    this.openDetail(foundItem);
                }
            @endif
        }
    }" class="max-w-5xl mx-auto px-2 sm:px-4 pb-28 pt-1 text-slate-900 dark:text-slate-100 font-sans selection:bg-blue-600 selection:text-white">

        <!-- ===================================================================
             1. TOP HEADER BAR (Matching Screenshot 1 & 2)
             =================================================================== -->
        <header class="sticky top-14 z-30 bg-white/95 dark:bg-slate-950/95 backdrop-blur-md pt-2 pb-3 border-b border-slate-100 dark:border-slate-800 -mx-2 px-3 sm:-mx-4 sm:px-4 shadow-xs">
            <div class="flex items-center justify-between gap-3 mb-2.5">
                <!-- OLX / K71 Classifieds Brand Mark -->
                <a href="{{ route('used.index') }}" class="flex items-center gap-2 group shrink-0">
                    <div class="h-9 w-9 rounded-xl bg-[#0600c2] text-white flex items-center justify-center font-black text-base shadow-md shadow-blue-900/20 group-hover:scale-105 transition-transform">
                        71
                    </div>
                    <div class="flex flex-col">
                        <span class="text-lg font-black tracking-tighter leading-none text-slate-950 dark:text-white flex items-center gap-1">
                            K71<span class="text-[#0600c2] text-sm">.USED</span>
                        </span>
                        <span class="text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                            Verified Classifieds
                        </span>
                    </div>
                </a>

                <!-- Location Selector (Kishangarh, Rajasthan ▾) -->
                <button type="button" 
                        @click="locationModal = true" 
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-800 dark:text-slate-200 hover:border-blue-500 transition-all max-w-[200px] truncate cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/>
                    </svg>
                    <span x-text="selectedCity" class="truncate text-left">{{ $selectedCity }}</span>
                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                </button>
            </div>

            <!-- Search Bar + Voice Mic + Heart + Bell Strip (Matching Screenshot 1) -->
            <div class="flex items-center gap-2">
                <div class="relative flex-1">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <form method="GET" action="{{ route('used.index') }}" @submit.prevent="window.location.href = '{{ route('used.index') }}?search=' + encodeURIComponent(searchQuery) + '&city=' + encodeURIComponent(selectedCity)">
                        <input type="text" 
                               x-model="searchQuery" 
                               placeholder='Search "Jobs", "iPhone", "Sofa", "Car"...' 
                               class="w-full pl-10 pr-10 py-2.5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs sm:text-sm font-medium placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:bg-white dark:focus:bg-slate-950 transition-all">
                    </form>
                    <!-- Voice Search Mic Button -->
                    <button type="button" 
                            @click="startVoiceSearch()" 
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-blue-600 cursor-pointer"
                            title="Voice Search">
                        <svg class="w-4 h-4" :class="voiceListening ? 'text-red-500 animate-pulse' : ''" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3zm5-3c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-2.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                        </svg>
                    </button>
                </div>

                <!-- Wishlist Heart Trigger -->
                <button type="button" 
                        @click="wishlistFilterActive = !wishlistFilterActive; if(window.showToast) window.showToast(wishlistFilterActive ? 'Showing your saved ads' : 'Showing all ads')" 
                        class="relative p-2.5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-red-500 hover:border-red-200 transition-colors cursor-pointer shrink-0"
                        title="Saved Favorites">
                    <svg class="w-5 h-5" :class="wishlistFilterActive ? 'fill-red-500 text-red-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                    <span x-show="wishlist.length > 0" 
                          x-text="wishlist.length" 
                          class="absolute -top-1 -right-1 bg-red-500 text-white font-black text-[9px] w-4 h-4 rounded-full flex items-center justify-center leading-none"></span>
                </button>

                <!-- Notification Bell -->
                <button type="button" 
                        @click="if(window.showToast) window.showToast('🔔 No new unread price alerts for {{ $selectedCity }}')" 
                        class="relative p-2.5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:text-blue-600 transition-colors cursor-pointer shrink-0"
                        title="Notifications">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="absolute 1.5 top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                </button>
            </div>
        </header>


        <!-- ===================================================================
             2. HERO PROMOTIONAL BANNER CAROUSEL (Matching Screenshot 1)
             =================================================================== -->
        <div class="mt-3.5 mb-5">
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#0052cc] via-[#0066ff] to-[#00b4d8] text-white p-5 sm:p-6 shadow-xl shadow-blue-900/15">
                <div class="flex items-center justify-between gap-4 relative z-10">
                    <div class="space-y-2 max-w-xs sm:max-w-md">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-yellow-400 text-slate-950 font-black text-[10px] tracking-wide uppercase shadow-sm">
                            <span>Starting @ ₹99 Only</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight leading-tight">
                            Dikhegi tabhi toh <br class="hidden sm:inline">Bikegi⚡
                        </h2>
                        <p class="text-xs text-blue-100 font-medium">
                            Highlight Your Ad with Featured Tag &amp; get 10x buyers.
                        </p>
                        <div class="pt-1">
                            <button type="button" 
                                    @click="sellModal = true" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full bg-yellow-400 hover:bg-yellow-300 text-slate-950 font-extrabold text-xs shadow-md active:scale-95 transition-all cursor-pointer">
                                <span>Upgrade Now</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Clean Geometric Shape Graphic (No ugly stock texture) -->
                    <div class="shrink-0 relative hidden sm:flex items-center justify-center">
                        <div class="w-36 h-28 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 p-2.5 flex flex-col justify-between shadow-2xl rotate-2">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-lg bg-yellow-400 text-slate-950 flex items-center justify-center font-bold text-xs">★</div>
                                <div class="text-[10px] font-bold leading-tight">
                                    <div class="text-yellow-300 uppercase tracking-widest text-[8px]">FEATURED AD</div>
                                    <div class="truncate">Toyota Fortuner 4x4</div>
                                </div>
                            </div>
                            <div class="text-xs font-black text-white">₹ 32,50,000</div>
                            <div class="text-[8px] text-blue-200">📍 Kishangarh • 2 days ago</div>
                        </div>
                    </div>
                </div>

                <!-- Abstract Decorative Shapes -->
                <div class="absolute -right-12 -bottom-12 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>
                <div class="absolute right-20 -top-10 w-32 h-32 rounded-full bg-yellow-400/20 blur-xl pointer-events-none"></div>
            </div>
        </div>


        <!-- ===================================================================
             3. 9 CATEGORY VECTOR SHAPE CARDS (Matching Screenshot 1)
             Pure CSS & SVG Geometric Shapes — NO Static Low-Res Placeholder Photos!
             =================================================================== -->
        <section class="mb-6">
            <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-9 gap-2.5 sm:gap-3 text-center">

                <!-- 1. Mobiles -->
                <a href="{{ route('used.index', ['cat' => 'mobile', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'mobile'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-purple-100 to-indigo-50 dark:from-purple-950/60 dark:to-indigo-950/40 border border-purple-200/60 dark:border-purple-800/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector Smartphone Shape -->
                        <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17 1.01L7 1c-1.1 0-2 .9-2 2v18c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V3c0-1.1-.9-1.99-2-1.99zM17 19H7V5h10v14zm-5 1a1 1 0 100-2 1 1 0 000 2z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        Mobiles
                    </span>
                </a>

                <!-- 2. Jobs -->
                <a href="{{ route('used.index', ['cat' => 'jobs', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'jobs'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-amber-100 to-yellow-50 dark:from-amber-950/60 dark:to-yellow-950/40 border border-amber-200/60 dark:border-amber-800/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector Briefcase Shape -->
                        <svg class="w-7 h-7 text-amber-700 dark:text-amber-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        Jobs
                    </span>
                </a>

                <!-- 3. Fashion -->
                <a href="{{ route('used.index', ['cat' => 'fashion', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'fashion'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-sky-100 to-cyan-50 dark:from-sky-950/60 dark:to-cyan-950/40 border border-sky-200/60 dark:border-sky-800/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector T-Shirt Shape -->
                        <svg class="w-7 h-7 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M21.9 8.89l-3.71-3.71c-.39-.39-1.02-.39-1.41 0L14 8V4c0-.55-.45-1-1-1h-2c-.55 0-1 .45-1 1v4L7.22 5.18c-.39-.39-1.02-.39-1.41 0L2.1 8.89c-.39.39-.39 1.02 0 1.41l2.83 2.83c.39.39 1.02.39 1.41 0L8 11.47V20c0 .55.45 1 1 1h6c.55 0 1-.45 1-1v-8.53l1.66 1.66c.39.39 1.02.39 1.41 0l2.83-2.83c.39-.39.39-1.03 0-1.41z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        Fashion
                    </span>
                </a>

                <!-- 4. Books, Sports & Hobbies -->
                <a href="{{ route('used.index', ['cat' => 'books', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'books'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-orange-100 to-rose-50 dark:from-orange-950/60 dark:to-rose-950/40 border border-orange-200/60 dark:border-orange-800/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector Guitar / Music Shape -->
                        <svg class="w-7 h-7 text-orange-600 dark:text-orange-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600 leading-tight">
                        Hobbies
                    </span>
                </a>

                <!-- 5. Commercial Vehicles & Spares -->
                <a href="{{ route('used.index', ['cat' => 'vehicles', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'vehicles'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-yellow-100 to-amber-50 dark:from-yellow-950/60 dark:to-amber-950/40 border border-yellow-200/60 dark:border-yellow-800/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector Car / Vehicle Shape -->
                        <svg class="w-7 h-7 text-yellow-700 dark:text-yellow-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        Vehicles
                    </span>
                </a>

                <!-- 6. Furniture -->
                <a href="{{ route('used.index', ['cat' => 'furniture', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'furniture'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-amber-200/70 to-orange-100 dark:from-amber-900/40 dark:to-orange-950/40 border border-amber-300/60 dark:border-amber-700/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector Armchair Shape -->
                        <svg class="w-7 h-7 text-amber-800 dark:text-amber-300" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M20 9V6c0-1.1-.9-2-2-2H6c-1.1 0-2 .9-2 2v3c-1.1 0-2 .9-2 2v5c0 1.1.9 2 2 2v2c0 .55.45 1 1 1s1-.45 1-1v-2h12v2c0 .55.45 1 1 1s1-.45 1-1v-2c1.1 0 2-.9 2-2v-5c0-1.1-.9-2-2-2zm-14-3h12v3h-12V6zm14 9H4v-4h2v2h12v-2h2v4z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        Furniture
                    </span>
                </a>

                <!-- 7. Pets -->
                <a href="{{ route('used.index', ['cat' => 'pets', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'pets'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-stone-200 to-slate-100 dark:from-stone-900 dark:to-slate-900 border border-stone-300/60 dark:border-stone-700/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector Pet Paw Shape -->
                        <svg class="w-7 h-7 text-slate-700 dark:text-slate-300" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 11.5c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2c0-2.33-4.67-3.5-7-3.5zm-7-9C3.9 4.5 3 5.4 3 6.5s.9 2 2 2 2-.9 2-2-.9-2-2-2zm14 0c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        Pets
                    </span>
                </a>

                <!-- 8. Services -->
                <a href="{{ route('used.index', ['cat' => 'services', 'city' => $selectedCity]) }}" 
                   @click.prevent="selectedCategory = 'services'"
                   class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-gradient-to-tr from-blue-100 to-slate-100 dark:from-blue-950/60 dark:to-slate-900 border border-blue-200/60 dark:border-blue-800/60 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:shadow-md transition-all duration-200">
                        <!-- Pure Vector Crossed Tools Shape -->
                        <svg class="w-7 h-7 text-blue-700 dark:text-blue-400" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.7 19l-9.1-9.1c.9-2.3.4-5-1.5-6.9-2-2-5-2.4-7.4-1.3L9 6 6 9 1.6 4.7C.4 7.1.9 10.1 2.9 12.1c1.9 1.9 4.6 2.4 6.9 1.5l9.1 9.1c.4.4 1 .4 1.4 0l2.3-2.3c.5-.4.5-1.1.1-1.4z"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        Services
                    </span>
                </a>

                <!-- 9. See All Pill -->
                <button type="button" 
                        @click="selectedCategory = 'all'" 
                        class="group flex flex-col items-center cursor-pointer">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center shadow-xs group-hover:scale-105 group-hover:border-blue-500 transition-all duration-200">
                        <svg class="w-6 h-6 text-slate-600 dark:text-slate-300 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </div>
                    <span class="mt-1.5 text-[11px] font-bold tracking-tight text-slate-800 dark:text-slate-200 group-hover:text-blue-600">
                        See all
                    </span>
                </button>

            </div>
        </section>


        <!-- ===================================================================
             4. GET VERIFIED TRUST BANNER (Matching Screenshot 1)
             =================================================================== -->
        <div class="mb-6 p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-blue-50 dark:bg-blue-950/60 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0 border border-blue-200/60 dark:border-blue-800/60 shadow-xs">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-xs sm:text-sm font-extrabold text-slate-950 dark:text-white leading-tight">
                        You are eligible for Verified
                    </h3>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                        Gain instant trust of buyers &amp; sell 3x faster
                    </p>
                </div>
            </div>
            <button type="button" 
                    @click="verifiedModal = true" 
                    class="px-4 py-2 rounded-xl bg-[#0052cc] hover:bg-[#003d99] text-white font-extrabold text-xs shadow-sm active:scale-95 transition-all shrink-0 cursor-pointer">
                Get Verified
            </button>
        </div>


        <!-- ===================================================================
             5. RECOMMENDED FOR YOU (Matching Screenshot 1)
             Powered by Vaya AI
             =================================================================== -->
        <section class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-1.5">
                    <span class="text-blue-600 font-bold text-sm">✨</span>
                    <h3 class="text-sm sm:text-base font-extrabold text-slate-950 dark:text-white">
                        Recommended For You
                    </h3>
                    <span class="text-[10px] text-slate-400 font-medium">Powered by Vaya AI</span>
                </div>
                <button type="button" @click="selectedCategory = 'all'" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline">
                    View all
                </button>
            </div>

            <!-- Horizontal Swipeable AI Recommendations Strip -->
            <div class="flex gap-3 overflow-x-auto no-scrollbar pb-2 -mx-2 px-2 sm:-mx-4 sm:px-4">
                @foreach($recommendedItems as $rec)
                    @php
                        $recImg = $rec->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=400&q=80';
                        $recBadge = $rec->custom_fields['badge'] ?? 'ELITE';
                        $recLocation = $rec->custom_fields['location'] ?? ($rec->vendor->city ?? 'Kishangarh, Rajasthan');
                    @endphp
                    <div @click="openDetail({{ json_encode($rec) }})" 
                         class="group w-44 sm:w-48 shrink-0 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/80 dark:border-slate-800 p-2 shadow-xs hover:shadow-md transition-all cursor-pointer">
                        <div class="relative w-full h-32 rounded-xl overflow-hidden bg-slate-100 dark:bg-slate-800">
                            <img src="{{ $recImg }}" alt="{{ $rec->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <span class="absolute top-1.5 left-1.5 px-2 py-0.5 rounded-md font-black text-[8.5px] uppercase tracking-wider {{ $recBadge === 'ELITE' ? 'bg-[#0600c2] text-white' : 'bg-yellow-400 text-slate-950' }}">
                                {{ $recBadge }}
                            </span>
                        </div>
                        <div class="mt-2 space-y-1">
                            <div class="text-sm font-black text-slate-950 dark:text-white">
                                ₹ {{ number_format($rec->base_price / 100) }}
                            </div>
                            <div class="text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase truncate">
                                {{ $rec->title }}
                            </div>
                            <div class="text-[9.5px] text-slate-400 truncate">
                                📍 {{ $recLocation }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>


        <!-- ===================================================================
             6. FRESH RECOMMENDATIONS 2-COLUMN GRID (Matching Screenshot 2)
             =================================================================== -->
        <section class="mb-8">
            <div class="flex items-center justify-between mb-3.5">
                <h3 class="text-base sm:text-lg font-black text-slate-950 dark:text-white tracking-tight">
                    Fresh recommendations
                </h3>
                <span class="text-xs font-bold text-slate-400">
                    <span x-text="wishlistFilterActive ? 'Favorites (' + wishlist.length + ')' : '{{ $items->total() }} Ads in ' + selectedCity.split(',')[0]"></span>
                </span>
            </div>

            <!-- 2-Column Responsive Card Grid (Screenshot 2 Pixel Match) -->
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                @foreach($items as $item)
                    @php
                        $firstImg = $item->images->first()?->path ?? 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=600&q=80';
                        $custom = $item->custom_fields ?? [];
                        $badge = $custom['badge'] ?? ($item->is_featured ? 'FEATURED' : 'ELITE');
                        $location = $custom['location'] ?? ($item->vendor->city ?? 'Kishangarh, Rajasthan');
                        $adId = $custom['ad_id'] ?? ('1857' . $item->id);
                        $vendorPhone = $item->vendor->phone ?? '+919549311580';
                    @endphp

                    <div x-show="!wishlistFilterActive || isWishlisted({{ $item->id }})" 
                         x-transition
                         class="group bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/90 dark:border-slate-800 shadow-xs hover:shadow-xl hover:border-blue-400 dark:hover:border-blue-600 transition-all duration-300 flex flex-col justify-between overflow-hidden relative">

                        <!-- Image Box with Badges & Heart -->
                        <div class="relative w-full aspect-[4/3] bg-slate-100 dark:bg-slate-800 overflow-hidden cursor-pointer"
                             @click="openDetail({{ json_encode($item) }})">
                            
                            <!-- Badges -->
                            @if($badge === 'ELITE')
                                <div class="absolute top-0 inset-x-0 bg-[#0600c2] text-white text-[9.5px] font-black uppercase tracking-wider py-1 px-2.5 flex items-center justify-center gap-1 z-10 shadow-sm">
                                    <span>👑</span>
                                    <span>ELITE</span>
                                </div>
                            @elseif($badge === 'FEATURED')
                                <span class="absolute top-2 left-2 bg-yellow-400 text-slate-950 font-black text-[9px] uppercase tracking-wider px-2 py-0.5 rounded-md z-10 shadow-sm">
                                    FEATURED
                                </span>
                            @endif

                            <!-- Favorite Heart Button (Top Right Circle) -->
                            <button type="button" 
                                    @click.stop="toggleWishlist({{ $item->id }})" 
                                    class="absolute top-2.5 right-2.5 z-20 w-8 h-8 rounded-full bg-white/90 dark:bg-slate-900/90 backdrop-blur-md flex items-center justify-center text-slate-700 dark:text-slate-200 shadow-md hover:scale-110 active:scale-90 transition-all cursor-pointer"
                                    aria-label="Add to favorites">
                                <svg class="w-4 h-4 transition-colors" 
                                     :class="isWishlisted({{ $item->id }}) ? 'fill-red-500 text-red-500' : 'fill-none stroke-current'" 
                                     stroke-width="2.2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/>
                                </svg>
                            </button>

                            <img src="{{ $firstImg }}" 
                                 alt="{{ $item->title }}" 
                                 loading="lazy"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        </div>

                        <!-- Card Body Details -->
                        <div class="p-3 sm:p-3.5 flex-1 flex flex-col justify-between cursor-pointer"
                             @click="openDetail({{ json_encode($item) }})">
                            <div class="space-y-1">
                                <!-- Price -->
                                <div class="text-base sm:text-lg font-black text-slate-950 dark:text-white leading-tight">
                                    ₹ {{ number_format($item->base_price / 100) }}
                                </div>

                                <!-- Title (Uppercase line-clamp-1 exactly matching Screenshot 2) -->
                                <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-tight line-clamp-1">
                                    {{ $item->title }}
                                </h4>
                            </div>

                            <!-- Bottom Row: Location + Direct Call Blue Button (Screenshot 2) -->
                            <div class="mt-3 pt-2 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-1">
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium truncate flex items-center gap-1">
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                                    <span class="truncate">{{ strtoupper($location) }}</span>
                                </div>

                                <!-- Round Blue Call Button (Screenshot 2) -->
                                <a href="tel:{{ $vendorPhone }}" 
                                   @click.stop="" 
                                   class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#0052cc] hover:bg-[#003d99] text-white flex items-center justify-center shrink-0 shadow-sm shadow-blue-900/30 active:scale-90 transition-transform"
                                   title="Call Seller Directly">
                                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                                        <path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                                    </svg>
                                </a>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty Wishlist / Search Notice -->
            <div x-show="wishlistFilterActive && wishlist.length === 0" class="text-center py-12 bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 p-6 my-4">
                <div class="text-3xl mb-2">🤍</div>
                <h4 class="text-sm font-extrabold text-slate-800 dark:text-slate-200">No favorite ads saved yet</h4>
                <p class="text-xs text-slate-500 mt-1">Tap the heart icon on any ad to quickly compare and track items here.</p>
                <button type="button" @click="wishlistFilterActive = false" class="mt-3 px-4 py-2 rounded-full bg-blue-600 text-white font-bold text-xs">
                    Browse All Ads
                </button>
            </div>
        </section>


        <!-- ===================================================================
             7. PRODUCT DETAIL VIEW MODAL (Screenshots 3, 4, 5 Pixel Match)
             =================================================================== -->
        <div x-show="detailModal" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex justify-center items-start sm:p-4">
            
            <div @click.away="detailModal = false" 
                 class="w-full max-w-2xl bg-white dark:bg-slate-950 min-h-screen sm:min-h-0 sm:rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden pb-24 relative">
                
                <template x-if="activeProduct">
                    <div>
                        <!-- Sticky Top Action Bar: Back + Title + Share (Screenshot 3 & 4) -->
                        <div class="sticky top-0 z-20 bg-white/95 dark:bg-slate-950/95 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 px-4 py-3 flex items-center justify-between gap-3">
                            <button type="button" 
                                    @click="detailModal = false" 
                                    class="p-2 -ml-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>

                            <div class="flex-1 truncate text-xs font-extrabold text-slate-900 dark:text-white">
                                <span x-text="activeProduct.title"></span>
                                <span class="text-blue-600 dark:text-blue-400 font-black ml-1.5" x-text="'₹ ' + Number(activeProduct.base_price / 100).toLocaleString()"></span>
                            </div>

                            <button type="button" 
                                    @click="if (navigator.share) { navigator.share({ title: activeProduct.title, url: window.location.href }); } else { navigator.clipboard.writeText(window.location.href); if (window.showToast) window.showToast('Link copied to clipboard!'); }" 
                                    class="p-2 rounded-full hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer"
                                    title="Share Ad">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                            </button>
                        </div>

                        <!-- Top Strip: Find Verified Users Instantly (Screenshot 3) -->
                        <div class="bg-amber-100/60 dark:bg-amber-950/40 px-4 py-2 flex items-center justify-between text-xs border-b border-amber-200/50 dark:border-amber-900/50">
                            <div class="flex items-center gap-2 text-slate-800 dark:text-amber-200 font-bold">
                                <svg class="w-4 h-4 text-blue-600 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/></svg>
                                <span>Find <strong>Verified Users</strong> instantly</span>
                            </div>
                            <button type="button" @click="verifiedModal = true" class="text-blue-600 dark:text-blue-400 font-black hover:underline cursor-pointer">
                                Get Verified &gt;
                            </button>
                        </div>

                        <!-- Image Carousel with 1/4 Pagination Counter (Screenshot 4) -->
                        <div class="relative w-full aspect-[4/3] bg-black">
                            <template x-if="activeProduct.images && activeProduct.images.length > 0">
                                <img :src="activeProduct.images[galleryIndex].path" 
                                     :alt="activeProduct.title" 
                                     class="w-full h-full object-contain">
                            </template>
                            
                            <!-- Pagination Counter 1/4 (Screenshot 4) -->
                            <div class="absolute bottom-3 right-3 px-2.5 py-1 rounded-md bg-black/60 text-white text-xs font-mono font-bold z-10">
                                <span x-text="(galleryIndex + 1) + '/' + (activeProduct.images ? activeProduct.images.length : 1)"></span>
                            </div>

                            <!-- Carousel Previous & Next buttons -->
                            <template x-if="activeProduct.images && activeProduct.images.length > 1">
                                <div>
                                    <button type="button" 
                                            @click="galleryIndex = (galleryIndex > 0) ? galleryIndex - 1 : activeProduct.images.length - 1" 
                                            class="absolute left-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/80">
                                        &larr;
                                    </button>
                                    <button type="button" 
                                            @click="galleryIndex = (galleryIndex < activeProduct.images.length - 1) ? galleryIndex + 1 : 0" 
                                            class="absolute right-2 top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-black/50 text-white flex items-center justify-center hover:bg-black/80">
                                        &rarr;
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Live View Count Banner (Screenshot 4 & 5) -->
                        <div class="bg-slate-900 text-white px-4 py-2 flex items-center gap-2 text-xs font-medium">
                            <span>👁</span>
                            <span><strong x-text="activeProductViews">6</strong> views in past hour - message the seller now!</span>
                        </div>

                        <!-- Product Pricing & Identity Block (Screenshot 4 & 5) -->
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 space-y-2.5 bg-white dark:bg-slate-950">
                            <!-- ELITE Badge -->
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded bg-[#0600c2] text-white text-[10px] font-black uppercase tracking-wider">
                                    <span>👑</span>
                                    <span>ELITE</span>
                                </span>
                                <button type="button" 
                                        @click="toggleWishlist(activeProduct.id)" 
                                        class="p-2 rounded-full border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:text-red-500">
                                    <svg class="w-5 h-5" :class="isWishlisted(activeProduct.id) ? 'fill-red-500 text-red-500' : 'fill-none stroke-current'" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
                                </button>
                            </div>

                            <!-- Title -->
                            <h2 class="text-base sm:text-lg font-black text-slate-950 dark:text-white leading-snug" x-text="activeProduct.title"></h2>

                            <!-- Price + Fair Deal Badge (Screenshot 5) -->
                            <div class="flex items-center gap-3 pt-1">
                                <div class="text-2xl font-black text-slate-950 dark:text-white" x-text="'₹ ' + Number(activeProduct.base_price / 100).toLocaleString()"></div>
                                <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 font-extrabold text-xs">
                                    <span>₹</span>
                                    <span>Fair Deal</span>
                                    <span class="text-[10px] opacity-70">ℹ️</span>
                                </div>
                            </div>

                            <!-- Location & Posted Date -->
                            <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 pt-1">
                                <div class="flex items-center gap-1 truncate">
                                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 110-5 2.5 2.5 0 010 5z"/></svg>
                                    <span x-text="(activeProduct.custom_fields && activeProduct.custom_fields.location) ? activeProduct.custom_fields.location : 'Shivaji Nagar, Kishangarh Ajmer District'"></span>
                                </div>
                                <div class="shrink-0 font-medium">
                                    Posted On 20 Oct '26
                                </div>
                            </div>
                        </div>

                        <!-- Market Price Valuation Gauge Meter (Screenshot 5) -->
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-sky-50/50 dark:bg-slate-900/40">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <div>
                                    <span class="font-extrabold text-slate-900 dark:text-white">Are you buying at the market price?</span>
                                    <div class="text-[11px] text-slate-500">Check market price with <span class="font-bold text-blue-600">👑 ELITE</span></div>
                                </div>
                                <button type="button" @click="if (window.showToast) window.showToast('✅ Verified Fair Market Valuation: ₹ ' + Number(activeProduct.base_price / 100).toLocaleString())" class="px-3 py-1 rounded-lg border border-slate-300 dark:border-slate-700 font-bold text-xs bg-white dark:bg-slate-900 hover:bg-slate-100">
                                    🔒 Check Now
                                </button>
                            </div>
                            <!-- Gradient Bar with Indicator Pin (Screenshot 5) -->
                            <div class="relative pt-2 pb-1">
                                <div class="h-2.5 rounded-full bg-gradient-to-r from-sky-400 via-emerald-400 to-amber-400 w-full"></div>
                                <div class="absolute top-1 left-1/2 -translate-x-1/2 w-3.5 h-4 bg-slate-950 dark:bg-white rounded-sm shadow-md border-2 border-white dark:border-slate-900"></div>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 font-bold pt-1">
                                <span>Low Price</span>
                                <span class="text-emerald-600 font-extrabold">Fair Market Price Range</span>
                                <span>High Price</span>
                            </div>
                        </div>

                        <!-- Check If Seller Is Verified (Screenshot 4) -->
                        <div @click="verifiedModal = true" class="p-4 border-b border-slate-200 dark:border-slate-800 bg-amber-50/60 dark:bg-amber-950/20 flex items-center justify-between cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-xs shrink-0">
                                    ✓
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-900 dark:text-white">Check if this seller is Verified</div>
                                    <div class="text-[11px] text-slate-500">Buy confidently with genuine sellers</div>
                                </div>
                            </div>
                            <span class="text-slate-400 font-bold text-sm">&gt;</span>
                        </div>

                        <!-- Product Information Accordion (Screenshot 4 & 5) -->
                        <div x-data="{ infoOpen: true }" class="border-b border-slate-200 dark:border-slate-800">
                            <button type="button" 
                                    @click="infoOpen = !infoOpen" 
                                    class="w-full p-4 flex items-center justify-between font-extrabold text-sm text-slate-900 dark:text-white hover:bg-slate-50 dark:hover:bg-slate-900 transition-colors">
                                <span>Product Information</span>
                                <svg class="w-4 h-4 transition-transform" :class="infoOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div x-show="infoOpen" x-transition class="px-4 pb-4 space-y-2 text-xs">
                                <template x-if="activeProduct.custom_fields && activeProduct.custom_fields.specs">
                                    <div class="grid grid-cols-2 gap-2 bg-slate-50 dark:bg-slate-900/60 p-3 rounded-2xl">
                                        <template x-for="(val, key) in activeProduct.custom_fields.specs" :key="key">
                                            <div class="py-1 border-b border-slate-200/60 dark:border-slate-800/60 last:border-0">
                                                <div class="text-slate-400 font-medium" x-text="key"></div>
                                                <div class="font-bold text-slate-800 dark:text-slate-200" x-text="val"></div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Description Block (Screenshot 4) -->
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800">
                            <h4 class="text-xs font-extrabold text-slate-900 dark:text-white mb-2">Description</h4>
                            <p class="text-xs text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-line" x-text="activeProduct.description"></p>
                        </div>

                        <!-- Seller Profile Card (Screenshot 3) -->
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950">
                            <div class="flex items-start gap-3.5">
                                <div class="w-14 h-14 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-200 flex items-center justify-center font-black text-lg overflow-hidden border border-slate-300 dark:border-slate-700 shrink-0">
                                    <span x-text="activeSellerName.substring(0, 2).toUpperCase()">YK</span>
                                </div>
                                <div class="flex-1 min-w-0 space-y-1">
                                    <div class="text-[10px] text-slate-400 font-medium">Posted by</div>
                                    <div class="text-sm font-black text-slate-950 dark:text-white truncate" x-text="activeSellerName">Yusuf khan</div>
                                    <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-[#0600c2] text-white text-[9px] font-black uppercase tracking-wider">
                                        <span>👑</span>
                                        <span>ELITE SELLER</span>
                                        <span>ℹ️</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4 pt-2 text-xs">
                                        <div>
                                            <div class="font-black text-slate-900 dark:text-white">Nov 2025</div>
                                            <div class="text-[10px] text-slate-400">Member Since</div>
                                        </div>
                                        <div>
                                            <div class="font-black text-slate-900 dark:text-white">8748</div>
                                            <div class="text-[10px] text-slate-400">Items listed</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Have a similar Item? Sell Now (Screenshot 3) -->
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/60 flex items-center justify-between">
                            <div class="flex items-center gap-2.5 text-xs font-bold text-slate-900 dark:text-white">
                                <div class="w-7 h-7 rounded-full bg-cyan-400 text-slate-950 flex items-center justify-center font-black">+</div>
                                <span>Have a similar Item?</span>
                            </div>
                            <button type="button" @click="sellModal = true" class="px-4 py-2 rounded-xl border-2 border-blue-600 text-blue-600 font-extrabold text-xs hover:bg-blue-600 hover:text-white transition-colors cursor-pointer">
                                Sell Now
                            </button>
                        </div>

                        <!-- Ad Posted At Map & Ad ID (Screenshot 3) -->
                        <div class="p-4 border-b border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="text-xs font-extrabold text-slate-900 dark:text-white">Ad posted at</div>
                            <div class="w-full h-36 rounded-2xl bg-gradient-to-tr from-sky-100 via-blue-50 to-indigo-50 dark:from-slate-900 dark:to-slate-800 border border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-center p-3 relative overflow-hidden">
                                <div class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center font-bold text-xs shadow-lg mb-1 animate-bounce">📍</div>
                                <div class="font-bold text-xs text-slate-900 dark:text-white" x-text="activeProduct.custom_fields && activeProduct.custom_fields.location ? activeProduct.custom_fields.location : 'Shivaji Nagar, Kishangarh Ajmer District'"></div>
                                <div class="text-[10px] text-slate-500">Kishangarh Fort • Sarwadi Gate • Ajmer Road</div>
                            </div>
                            <div class="flex items-center justify-between text-xs pt-1">
                                <div class="font-mono text-slate-400 text-[11px]">
                                    AD ID: <span class="font-bold text-slate-700 dark:text-slate-300" x-text="activeProduct.custom_fields && activeProduct.custom_fields.ad_id ? activeProduct.custom_fields.ad_id : '1857261533'">1857261533</span>
                                </div>
                                <button type="button" @click="if (window.showToast) window.showToast('Report submitted for review')" class="px-3 py-1 rounded-lg border border-red-300 text-red-600 font-bold text-[11px] hover:bg-red-50">
                                    Report AD
                                </button>
                            </div>
                        </div>

                        <!-- Floating Bottom Sticky Bar (Screenshot 3, 4, 5) -->
                        <div class="fixed bottom-0 inset-x-0 z-30 max-w-2xl mx-auto bg-white dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 p-3 shadow-2xl">
                            <!-- Notice Banner (Screenshot 3 & 5) -->
                            <div class="text-center text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-2 flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                <span>Call directly to the Elite Seller of this product</span>
                            </div>

                            <!-- 3 Working Action Buttons -->
                            <div class="grid grid-cols-3 gap-2">
                                <!-- 1. Chat Button -->
                                <button type="button" 
                                        @click="chatDrawer = true" 
                                        class="py-3 px-3 rounded-xl border-2 border-[#0052cc] text-[#0052cc] dark:text-blue-400 font-extrabold text-xs flex items-center justify-center gap-1.5 hover:bg-blue-50 dark:hover:bg-slate-900 active:scale-95 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                    <span>Chat</span>
                                </button>

                                <!-- 2. Make Offer Button -->
                                <button type="button" 
                                        @click="openOfferModal(activeProduct)" 
                                        class="py-3 px-3 rounded-xl bg-slate-900 text-white font-extrabold text-xs flex items-center justify-center gap-1.5 hover:bg-slate-800 active:scale-95 transition-all cursor-pointer">
                                    <span>Make Offer</span>
                                </button>

                                <!-- 3. Call Button -->
                                <a :href="'tel:' + activeSellerPhone" 
                                   class="py-3 px-3 rounded-xl bg-[#0052cc] hover:bg-[#003d99] text-white font-extrabold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-blue-900/30 active:scale-95 transition-all cursor-pointer">
                                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                                    <span>Call</span>
                                </a>
                            </div>
                        </div>

                    </div>
                </template>

            </div>
        </div>


        <!-- ===================================================================
             8. CHAT DRAWER WITH SELLER (Matching Screenshot 3 & 4)
             =================================================================== -->
        <div x-show="chatDrawer" 
             x-cloak 
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex justify-end">
            <div @click.away="chatDrawer = false" 
                 class="w-full max-w-md bg-white dark:bg-slate-950 h-full flex flex-col justify-between shadow-2xl">
                <!-- Chat Header -->
                <div class="p-3.5 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-900">
                    <div class="flex items-center gap-2.5">
                        <button type="button" @click="chatDrawer = false" class="p-1 rounded-full text-slate-500 hover:text-slate-900">&larr;</button>
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center">YK</div>
                        <div>
                            <div class="text-xs font-black text-slate-900 dark:text-white" x-text="activeSellerName">Yusuf khan</div>
                            <div class="text-[10px] text-emerald-500 font-bold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Online in Kishangarh</span>
                            </div>
                        </div>
                    </div>
                    <a :href="'tel:' + activeSellerPhone" class="p-2 rounded-full bg-blue-50 text-blue-600 font-bold text-xs">📞</a>
                </div>

                <!-- Messages Stream -->
                <div class="flex-1 p-4 overflow-y-auto space-y-3">
                    <template x-for="(m, i) in chatMessages" :key="i">
                        <div :class="m.sender === 'buyer' ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="m.sender === 'buyer' ? 'bg-[#0052cc] text-white rounded-2xl rounded-tr-none' : 'bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 rounded-2xl rounded-tl-none'"
                                 class="p-3 max-w-[80%] text-xs shadow-xs space-y-1">
                                <p x-text="m.text"></p>
                                <div class="text-[9px] opacity-70 text-right" x-text="m.time"></div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Quick Prompts Chips -->
                <div class="px-3 py-2 border-t border-slate-100 dark:border-slate-800 flex gap-1.5 overflow-x-auto no-scrollbar">
                    <button type="button" @click="sendChatMessage('Is this still available in Kishangarh?')" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[10.5px] font-bold text-slate-700 dark:text-slate-300 shrink-0">
                        Is this still available?
                    </button>
                    <button type="button" @click="sendChatMessage('What is your best final price?')" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[10.5px] font-bold text-slate-700 dark:text-slate-300 shrink-0">
                        What is your best price?
                    </button>
                    <button type="button" @click="sendChatMessage('Can I come inspect it today?')" class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-[10.5px] font-bold text-slate-700 dark:text-slate-300 shrink-0">
                        Can I inspect it today?
                    </button>
                </div>

                <!-- Input Box -->
                <div class="p-3 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 flex gap-2">
                    <input type="text" 
                           x-model="newChatMessage" 
                           @keydown.enter.prevent="sendChatMessage()"
                           placeholder="Type a message to Yusuf..." 
                           class="flex-1 px-4 py-2.5 rounded-full bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs focus:outline-none focus:ring-2 focus:ring-blue-600">
                    <button type="button" @click="sendChatMessage()" class="p-2.5 rounded-full bg-blue-600 text-white font-bold text-xs shadow-sm">
                        Send
                    </button>
                </div>
            </div>
        </div>


        <!-- ===================================================================
             9. MAKE AN OFFER KEYPAD MODAL (Matching Screenshot 4)
             =================================================================== -->
        <div x-show="offerModal" 
             x-cloak 
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div @click.away="offerModal = false" 
                 class="w-full max-w-md bg-white dark:bg-slate-900 rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl space-y-4">
                
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Make an Instant Counter Offer</h3>
                    <button type="button" @click="offerModal = false" class="text-slate-400 hover:text-slate-900">&times;</button>
                </div>

                <template x-if="offerStatus === 'input'">
                    <div class="space-y-4">
                        <div class="text-center py-2">
                            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Your Offer Price</span>
                            <div class="text-3xl font-black text-slate-950 dark:text-white mt-1">
                                ₹ <span x-text="Number(offerAmount).toLocaleString()"></span>
                            </div>
                        </div>

                        <!-- Numeric Keypad -->
                        <div class="grid grid-cols-3 gap-2 text-lg font-bold text-slate-900 dark:text-white">
                            <template x-for="d in ['1','2','3','4','5','6','7','8','9','00','0']" :key="d">
                                <button type="button" @click="appendDigit(d)" class="py-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 active:scale-95 transition-all text-center">
                                    <span x-text="d"></span>
                                </button>
                            </template>
                            <button type="button" @click="backspaceDigit()" class="py-3 rounded-xl bg-slate-200 dark:bg-slate-700 text-center font-bold">
                                ⌫
                            </button>
                        </div>

                        <button type="button" @click="submitOffer()" class="w-full py-3.5 rounded-xl bg-[#0052cc] hover:bg-[#003d99] text-white font-extrabold text-xs uppercase tracking-wider shadow-lg active:scale-95 transition-all">
                            Send Offer to Seller
                        </button>
                    </div>
                </template>

                <template x-if="offerStatus === 'submitting'">
                    <div class="py-12 text-center space-y-3">
                        <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto"></div>
                        <p class="text-xs font-bold text-slate-600">Checking with seller pricing algorithm...</p>
                    </div>
                </template>

                <template x-if="offerStatus === 'accepted'">
                    <div class="py-6 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-black text-2xl mx-auto">✓</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Offer Accepted!</h4>
                        <p class="text-xs text-slate-500">The seller accepted your offer of ₹ <span x-text="Number(offerAmount).toLocaleString()"></span>. Call or chat now to finalize pickup.</p>
                        <div class="flex gap-2 pt-2">
                            <button type="button" @click="offerModal = false; chatDrawer = true;" class="flex-1 py-3 rounded-xl bg-blue-600 text-white font-bold text-xs">Chat with Seller</button>
                        </div>
                    </div>
                </template>

                <template x-if="offerStatus === 'counter'">
                    <div class="py-6 text-center space-y-3">
                        <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center font-black text-2xl mx-auto">⚡</div>
                        <h4 class="text-base font-black text-slate-900 dark:text-white">Seller Counter Offer</h4>
                        <p class="text-xs text-slate-500">The seller considers this slightly low, but is ready to close at ₹ <span x-text="Number(Math.round(offerAmount * 1.08)).toLocaleString()"></span>.</p>
                        <button type="button" @click="offerStatus = 'input'" class="w-full py-3 rounded-xl bg-slate-900 text-white font-bold text-xs">Revise Offer</button>
                    </div>
                </template>

            </div>
        </div>


        <!-- ===================================================================
             10. LOCATION PICKER MODAL (Screenshot 1)
             =================================================================== -->
        <div x-show="locationModal" 
             x-cloak 
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="locationModal = false" class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl p-5 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-950 dark:text-white">Select Your Location</h3>
                    <button type="button" @click="locationModal = false" class="text-slate-400 hover:text-slate-900">&times;</button>
                </div>
                
                <input type="text" 
                       x-model="citySearch" 
                       placeholder="Search city, district, state..." 
                       class="w-full px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-xs font-medium focus:outline-none focus:ring-2 focus:ring-blue-600">

                <div class="max-h-60 overflow-y-auto space-y-1">
                    @foreach($availableCities as $c)
                        <button type="button" 
                                x-show="'{{ strtolower($c) }}'.includes(citySearch.toLowerCase())"
                                @click="selectedCity = '{{ $c }}'; locationModal = false; window.location.href = '{{ route('used.index') }}?city=' + encodeURIComponent('{{ $c }}')"
                                class="w-full px-3 py-2 rounded-xl text-left text-xs font-bold hover:bg-slate-100 dark:hover:bg-slate-800 flex items-center justify-between text-slate-700 dark:text-slate-300 cursor-pointer">
                            <span>📍 {{ $c }}</span>
                            <span x-show="selectedCity === '{{ $c }}'" class="text-blue-600 font-black">✓</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>


        <!-- ===================================================================
             11. SELL POST AD MODAL (Elevated Sell Button Action)
             =================================================================== -->
        <div x-show="sellModal" 
             x-cloak 
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
            <div @click.away="sellModal = false" class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-5 shadow-2xl space-y-4 my-auto">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-extrabold text-slate-950 dark:text-white">Post Free Classified Ad in Rajasthan</h3>
                    <button type="button" @click="sellModal = false" class="text-slate-400 hover:text-slate-900">&times;</button>
                </div>

                <form @submit.prevent="publishAd()" class="space-y-3 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Ad Title *</label>
                        <input type="text" x-model="adForm.title" required placeholder="e.g. iPhone 14 Pro Max 256GB Like New" class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Category *</label>
                            <select x-model="adForm.category" class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                                <option>Mobiles</option>
                                <option>Vehicles</option>
                                <option>Furniture</option>
                                <option>Electronics</option>
                                <option>Fashion</option>
                                <option>Jobs</option>
                                <option>Services</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Price (₹) *</label>
                            <input type="number" x-model="adForm.price" required placeholder="22000" class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">City / Locality *</label>
                            <input type="text" x-model="adForm.city" required placeholder="Kishangarh, Rajasthan" class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Contact Phone *</label>
                            <input type="text" x-model="adForm.phone" required placeholder="+91 95493 11580" class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Description *</label>
                        <textarea x-model="adForm.description" rows="3" placeholder="Condition, bill, box, reason for selling..." class="w-full px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 rounded-xl bg-[#0052cc] hover:bg-[#003d99] text-white font-black uppercase tracking-wider text-xs shadow-lg active:scale-95 transition-all">
                        Post Ad Now &rarr;
                    </button>
                </form>
            </div>
        </div>


        <!-- ===================================================================
             12. GET VERIFIED MODAL
             =================================================================== -->
        <div x-show="verifiedModal" 
             x-cloak 
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="verifiedModal = false" class="w-full max-w-sm bg-white dark:bg-slate-900 rounded-3xl p-5 shadow-2xl space-y-3.5 text-center">
                <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-black text-2xl mx-auto">🛡️</div>
                <h3 class="text-base font-black text-slate-900 dark:text-white">Get Verified Seller Badge</h3>
                <p class="text-xs text-slate-500">Verified members get 3x faster inquiries, official shield badge, and phone verification tag on every listing.</p>
                <div class="p-3 rounded-xl bg-slate-100 dark:bg-slate-800 text-left text-xs space-y-1">
                    <div class="font-bold text-slate-800 dark:text-slate-200">✓ Instant OTP Phone Verification</div>
                    <div class="font-bold text-slate-800 dark:text-slate-200">✓ Location Proof: Kishangarh, Rajasthan</div>
                    <div class="font-bold text-slate-800 dark:text-slate-200">✓ Zero Scams Escrow Protection</div>
                </div>
                <button type="button" @click="activateVerification()" class="w-full py-3 rounded-xl bg-[#0052cc] hover:bg-[#003d99] text-white font-extrabold text-xs">
                    Confirm &amp; Activate Badge
                </button>
            </div>
        </div>

    </div>
</x-layouts.public>
