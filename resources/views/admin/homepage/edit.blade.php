<x-layouts.admin title="Page Studio • Visual Elementor Site Builder">
    @php
        $hp = $homepage ?? [];
        $sp = $servicesPage ?? [];
        $fp = $foodPage ?? [];
        $up = $usedPage ?? [];
        $seoData = $seo ?? [];
    @endphp

    <div x-data="{
        activeVertical: 'homepage', // 'homepage', 'services', 'food', 'used', 'seo'
        previewMode: 'desktop', // 'desktop', 'mobile'
        
        // Homepage Live State
        hp_hero_badge: '{{ addslashes($hp['hero_badge'] ?? 'K71 UNIFIED MARKETPLACE • Verified Stores & Secure Escrow') }}',
        hp_title_line1: '{{ addslashes($hp['hero_title_line1'] ?? 'Discover Top Stores &') }}',
        hp_title_line2: '{{ addslashes($hp['hero_title_line2'] ?? 'Trending Products') }}',
        hp_description: '{{ addslashes($hp['hero_description'] ?? 'Shop directly from authorized distributor storefronts, boutique ateliers, and certified suppliers nationwide.') }}',
        hp_cta_text: '{{ addslashes($hp['hero_cta_primary_text'] ?? 'Search') }}',
        hp_search_placeholder: '{{ addslashes($hp['search_placeholder'] ?? 'Search products, brands, model numbers, stores...') }}',
        hp_arrivals_title: '{{ addslashes($hp['arrivals_section_title'] ?? '🔥 Trending Products & Deals') }}',
        hp_services_title: '{{ addslashes($hp['services_section_title'] ?? '💜 Top Freelance & Professional Services') }}',
        hp_services_sub: '{{ addslashes($hp['services_section_subtitle'] ?? 'Hire vetted specialists for tech, video, marketing & home craftsmanship') }}',
        hp_cat_title: '{{ addslashes($hp['cat_section_title'] ?? 'Structured Categories') }}',
        hp_cat_sub: '{{ addslashes($hp['cat_section_subtitle'] ?? 'Explore retail and trade verticals tailored for specific sectors.') }}',
        hp_show_trending: {{ ($hp['show_trending_products'] ?? true) ? 'true' : 'false' }},
        hp_show_services: {{ ($hp['show_services_showcase'] ?? true) ? 'true' : 'false' }},
        hp_show_categories: {{ ($hp['show_categories'] ?? true) ? 'true' : 'false' }},
        hp_show_pillars: {{ ($hp['show_value_pillars'] ?? true) ? 'true' : 'false' }},
        hp_show_seller: {{ ($hp['show_seller_banner'] ?? true) ? 'true' : 'false' }},

        // Services Hub Live State
        srv_hero_badge: '{{ addslashes($sp['services_hero_badge'] ?? 'services.k71.in • K71 Verified Freelancers & Services') }}',
        srv_hero_title: '{{ addslashes($sp['services_hero_title'] ?? 'Find the Perfect Freelance Services') }}',
        srv_hero_sub: '{{ addslashes($sp['services_hero_subtitle'] ?? 'Hire proven specialists with upfront package pricing, milestone safety, and fast delivery.') }}',
        srv_search_placeholder: '{{ addslashes($sp['services_search_placeholder'] ?? 'What service are you looking for today? (e.g. Logo Design, Website)') }}',
        srv_cat_title: '{{ addslashes($sp['services_cat_title'] ?? 'Explore Services by Category') }}',
        srv_cat_sub: '{{ addslashes($sp['services_cat_subtitle'] ?? 'Select a category to view verified specialists') }}',
        srv_trending_title: '{{ addslashes($sp['services_trending_title'] ?? 'Trending Services & Packages') }}',
        srv_trending_sub: '{{ addslashes($sp['services_trending_subtitle'] ?? 'Vetted freelancers ready to start on your project today') }}',
        srv_seller_title: '{{ addslashes($sp['services_seller_cta_title'] ?? 'Are You a Skilled Freelancer or Professional?') }}',
        srv_seller_sub: '{{ addslashes($sp['services_seller_cta_subtitle'] ?? 'Join K71 Services and start earning. Create your gigs, set your packages, and receive guaranteed escrow payouts directly to your bank account.') }}',

        // Food Hub Live State
        food_hero_badge: '{{ addslashes($fp['food_hero_badge'] ?? '⚡ 25-30 MINUTE EXPRESS DOORSTEP DISPATCH') }}',
        food_hero_title: '{{ addslashes($fp['food_hero_title'] ?? 'Crave it? Get it hot & fresh from top cloud kitchens.') }}',
        food_hero_sub: '{{ addslashes($fp['food_hero_subtitle'] ?? 'Explore hand-crafted dum biryanis, wood-fired artisan pizzas, gourmet burgers, and pure veg royal thalis.') }}',

        // Used Market Live State
        used_hero_badge: '{{ addslashes($up['used_hero_badge'] ?? '♻️ K71 VERIFIED PRE-LOVED & RE-COMMERCE') }}',
        used_hero_title: '{{ addslashes($up['used_hero_title'] ?? 'Certified Second-Hand Goods & Direct Classifieds') }}',
        used_hero_sub: '{{ addslashes($up['used_hero_subtitle'] ?? 'Buy and sell authenticated pre-owned smartphones, laptops, electronics, and vehicles safely.') }}',
    }" class="space-y-6">

        <!-- TOP MASTER TOOLBAR (ELEMENTOR HEADER) -->
        <div class="bg-slate-900 text-white rounded-3xl p-4 sm:p-6 shadow-xl border border-slate-800 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-purple-600 text-white">
                        Visual Page Studio
                    </span>
                    <span class="text-xs text-slate-400">• Elementor-Style Live Customizer</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-white tracking-tight flex items-center gap-2">
                    <span>⚡ K71 Page Studio &amp; Visual Layout Editor</span>
                </h1>
                <p class="text-xs text-slate-400 mt-0.5">
                    Live editing for Homepage, Services Hub, Food &amp; Cloud Kitchens, and Used Marketplaces.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <!-- Device Preview Switcher -->
                <div class="bg-slate-800 p-1 rounded-2xl flex items-center gap-1 border border-slate-700">
                    <button type="button" @click="previewMode = 'desktop'"
                            :class="previewMode === 'desktop' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>🖥️ Desktop</span>
                    </button>
                    <button type="button" @click="previewMode = 'mobile'"
                            :class="previewMode === 'mobile' ? 'bg-purple-600 text-white shadow-xs' : 'text-slate-400 hover:text-white'"
                            class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>📱 Mobile</span>
                    </button>
                </div>

                <!-- Live Preview External Link -->
                <a :href="activeVertical === 'services' ? '{{ k71_vertical_url('services') }}' : (activeVertical === 'food' ? '{{ k71_vertical_url('food') }}' : (activeVertical === 'used' ? '{{ k71_vertical_url('used') }}' : '{{ url('/') }}'))"
                   target="_blank"
                   class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition flex items-center gap-1.5">
                    <span>View Live</span>
                    <span>↗</span>
                </a>

                <!-- Publish Form Button -->
                <button type="submit" form="page-studio-form"
                        class="px-5 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white text-xs font-black tracking-wide shadow-lg shadow-purple-600/30 transition active:scale-95 flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Publish Changes Live</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 text-emerald-800 rounded-2xl border border-emerald-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs sm:text-sm font-bold">{{ session('success') }}</span>
                </div>
                <span class="text-xs bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-md font-bold">Published Live</span>
            </div>
        @endif

        <!-- PAGE SELECTOR TABS -->
        <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <button type="button" @click="activeVertical = 'homepage'"
                    :class="activeVertical === 'homepage' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200'"
                    class="px-4 py-2.5 rounded-2xl text-xs font-black transition flex items-center gap-2 shrink-0">
                <span>🏠 Homepage</span>
            </button>
            <button type="button" @click="activeVertical = 'services'"
                    :class="activeVertical === 'services' ? 'bg-purple-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200'"
                    class="px-4 py-2.5 rounded-2xl text-xs font-black transition flex items-center gap-2 shrink-0">
                <span>💜 Services Hub</span>
            </button>
            <button type="button" @click="activeVertical = 'food'"
                    :class="activeVertical === 'food' ? 'bg-orange-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200'"
                    class="px-4 py-2.5 rounded-2xl text-xs font-black transition flex items-center gap-2 shrink-0">
                <span>🍕 Food &amp; Dining</span>
            </button>
            <button type="button" @click="activeVertical = 'used'"
                    :class="activeVertical === 'used' ? 'bg-teal-600 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200'"
                    class="px-4 py-2.5 rounded-2xl text-xs font-black transition flex items-center gap-2 shrink-0">
                <span>♻️ Pre-Loved &amp; Used</span>
            </button>
            <button type="button" @click="activeVertical = 'seo'"
                    :class="activeVertical === 'seo' ? 'bg-slate-900 text-white shadow-md' : 'bg-white hover:bg-slate-100 text-slate-700 border border-slate-200'"
                    class="px-4 py-2.5 rounded-2xl text-xs font-black transition flex items-center gap-2 shrink-0">
                <span>🔍 SEO &amp; Meta</span>
            </button>
        </div>

        <!-- MAIN SPLIT WORKSPACE: LEFT CONTROLS (5 cols) + RIGHT LIVE CANVAS (7 cols) -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

            <!-- LEFT CONTROL PANEL (INSPECTOR) -->
            <div class="xl:col-span-5 space-y-6">
                <form id="page-studio-form" action="{{ route('admin.homepage.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- TAB 1: HOMEPAGE CONTROLS -->
                    <div x-show="activeVertical === 'homepage'" class="space-y-5">
                        
                        <!-- Hero Section -->
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider flex items-center gap-2">
                                    <span>👑 Homepage Hero Header</span>
                                </h3>
                                <span class="text-[10px] bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded-full font-bold">Above Fold</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Super Badge Pill</label>
                                    <input type="text" name="hero_badge" x-model="hp_hero_badge"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-medium">
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">Headline Part 1</label>
                                        <input type="text" name="hero_title_line1" x-model="hp_title_line1"
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-bold">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">Headline Part 2 (Gradient)</label>
                                        <input type="text" name="hero_title_line2" x-model="hp_title_line2"
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-bold text-indigo-600">
                                    </div>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Hero Subtitle / Description</label>
                                    <textarea name="hero_description" x-model="hp_description" rows="2"
                                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-medium"></textarea>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">Search Placeholder</label>
                                        <input type="text" name="search_placeholder" x-model="hp_search_placeholder"
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                    </div>
                                    <div>
                                        <label class="block font-bold text-slate-700 mb-1">Primary CTA Button</label>
                                        <input type="text" name="hero_cta_primary_text" x-model="hp_cta_text"
                                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-bold">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section Headers -->
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="pb-3 border-b border-slate-100">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                                    <span>📑 Section Titles &amp; Headings</span>
                                </h3>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Trending Products Title</label>
                                    <input type="text" name="arrivals_section_title" x-model="hp_arrivals_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-bold">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Freelance Showcase Title</label>
                                    <input type="text" name="services_section_title" x-model="hp_services_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-bold text-purple-700">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Freelance Showcase Subtitle</label>
                                    <input type="text" name="services_section_subtitle" x-model="hp_services_sub"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Structured Categories Title</label>
                                    <input type="text" name="cat_section_title" x-model="hp_cat_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 font-bold">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Structured Categories Subtitle</label>
                                    <input type="text" name="cat_section_subtitle" x-model="hp_cat_sub"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500">
                                </div>
                            </div>
                        </div>

                        <!-- Section Visibility Toggles -->
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="pb-3 border-b border-slate-100">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                                    <span>👁️ Section Visibility Toggles</span>
                                </h3>
                                <p class="text-[11px] text-slate-500">Turn homepage blocks on or off with instant preview.</p>
                            </div>

                            <div class="space-y-2.5 text-xs">
                                <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                                    <span class="font-bold text-slate-800">Show Trending Products &amp; Deals</span>
                                    <input type="checkbox" name="show_trending_products" value="1" x-model="hp_show_trending" class="w-4 h-4 text-indigo-600 rounded">
                                </label>

                                <label class="flex items-center justify-between p-3 rounded-2xl bg-purple-50/50 border border-purple-200 cursor-pointer">
                                    <span class="font-bold text-purple-900">Show Freelance &amp; Services Showcase</span>
                                    <input type="checkbox" name="show_services_showcase" value="1" x-model="hp_show_services" class="w-4 h-4 text-purple-600 rounded">
                                </label>

                                <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                                    <span class="font-bold text-slate-800">Show Structured Categories</span>
                                    <input type="checkbox" name="show_categories" value="1" x-model="hp_show_categories" class="w-4 h-4 text-indigo-600 rounded">
                                </label>

                                <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                                    <span class="font-bold text-slate-800">Show Value Assurance Pillars</span>
                                    <input type="checkbox" name="show_value_pillars" value="1" x-model="hp_show_pillars" class="w-4 h-4 text-indigo-600 rounded">
                                </label>

                                <label class="flex items-center justify-between p-3 rounded-2xl bg-slate-50 border border-slate-200 cursor-pointer">
                                    <span class="font-bold text-slate-800">Show Seller Onboarding Banner</span>
                                    <input type="checkbox" name="show_seller_banner" value="1" x-model="hp_show_seller" class="w-4 h-4 text-indigo-600 rounded">
                                </label>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: SERVICES HUB CONTROLS -->
                    <div x-show="activeVertical === 'services'" class="space-y-5">
                        
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-purple-200 shadow-sm space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-purple-100">
                                <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider">
                                    <span>💜 Services Hub Hero &amp; Brand</span>
                                </h3>
                                <span class="text-[10px] bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-bold">services.k71.in</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Services Trust Badge</label>
                                    <input type="text" name="services_hero_badge" x-model="srv_hero_badge"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 font-medium">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Services Hero Title</label>
                                    <input type="text" name="services_hero_title" x-model="srv_hero_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 font-black text-sm">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Services Hero Subtitle</label>
                                    <textarea name="services_hero_subtitle" x-model="srv_hero_sub" rows="2"
                                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500"></textarea>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Services Search Placeholder</label>
                                    <input type="text" name="services_search_placeholder" x-model="srv_search_placeholder"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Category Slider Title</label>
                                    <input type="text" name="services_cat_title" x-model="srv_cat_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 font-bold">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Trending Gigs Title</label>
                                    <input type="text" name="services_trending_title" x-model="srv_trending_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 font-bold">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Freelancer Onboarding Title</label>
                                    <input type="text" name="services_seller_cta_title" x-model="srv_seller_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-purple-500 font-bold">
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 3: FOOD & DINING CONTROLS -->
                    <div x-show="activeVertical === 'food'" class="space-y-5">
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-orange-200 shadow-sm space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-orange-100">
                                <h3 class="text-sm font-black text-orange-950 uppercase tracking-wider">
                                    <span>🍕 Food Hub Hero &amp; Kitchens</span>
                                </h3>
                                <span class="text-[10px] bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full font-bold">food.k71.in</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Express Delivery Badge</label>
                                    <input type="text" name="food_hero_badge" x-model="food_hero_badge"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 font-medium">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Food Hero Title</label>
                                    <input type="text" name="food_hero_title" x-model="food_hero_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500 font-black text-sm">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Food Hero Subtitle</label>
                                    <textarea name="food_hero_subtitle" x-model="food_hero_sub" rows="2"
                                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-orange-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: PRE-LOVED & USED CONTROLS -->
                    <div x-show="activeVertical === 'used'" class="space-y-5">
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-teal-200 shadow-sm space-y-4">
                            <div class="flex items-center justify-between pb-3 border-b border-teal-100">
                                <h3 class="text-sm font-black text-teal-950 uppercase tracking-wider">
                                    <span>♻️ Pre-Loved Re-Commerce Hub</span>
                                </h3>
                                <span class="text-[10px] bg-teal-100 text-teal-700 px-2 py-0.5 rounded-full font-bold">used.k71.in</span>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Re-Commerce Badge</label>
                                    <input type="text" name="used_hero_badge" x-model="used_hero_badge"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 font-medium">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Used Market Hero Title</label>
                                    <input type="text" name="used_hero_title" x-model="used_hero_title"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500 font-black text-sm">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Used Market Subtitle</label>
                                    <textarea name="used_hero_subtitle" x-model="used_hero_sub" rows="2"
                                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-teal-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 5: SEO CONTROLS -->
                    <div x-show="activeVertical === 'seo'" class="space-y-5">
                        <div class="bg-white rounded-3xl p-5 sm:p-6 border border-slate-200 shadow-sm space-y-4">
                            <div class="pb-3 border-b border-slate-100">
                                <h3 class="text-sm font-black text-slate-900 uppercase tracking-wider">
                                    <span>🔍 Search Engine Meta &amp; Social Previews</span>
                                </h3>
                            </div>

                            <div class="space-y-3 text-xs">
                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Meta Title</label>
                                    <input type="text" name="seo_meta_title" value="{{ $seoData['seo_meta_title'] ?? 'K71 • Curated Multi-Vendor Marketplace' }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-700 font-medium">
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Meta Description</label>
                                    <textarea name="seo_meta_description" rows="2"
                                              class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-700">{{ $seoData['seo_meta_description'] ?? 'Discover verified brand stores, boutique ateliers, and trending products on K71.' }}</textarea>
                                </div>

                                <div>
                                    <label class="block font-bold text-slate-700 mb-1">Target Meta Keywords</label>
                                    <input type="text" name="seo_meta_keywords" value="{{ $seoData['seo_meta_keywords'] ?? 'marketplace, ecommerce, freelance services, cloud kitchen food' }}"
                                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-slate-700">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- RIGHT LIVE INTERACTIVE VISUAL CANVAS (7 cols) -->
            <div class="xl:col-span-7 sticky top-6">
                
                <!-- Browser / Device Wrapper -->
                <div class="rounded-3xl border-2 border-slate-800 bg-slate-950 p-2 shadow-2xl transition-all duration-300"
                     :class="previewMode === 'mobile' ? 'max-w-[390px] mx-auto rounded-[3rem] p-3 border-4 border-slate-700' : 'w-full'">
                    
                    <!-- Chrome Header Mockup Bar -->
                    <div class="flex items-center justify-between px-3 py-2 border-b border-slate-800 text-xs text-slate-400">
                        <div class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                        </div>
                        <div class="px-3 py-0.5 rounded-full bg-slate-900 border border-slate-800 text-[10px] font-mono text-slate-400 truncate max-w-xs">
                            <span x-text="activeVertical === 'services' ? 'https://services.marblemandi.org/' : (activeVertical === 'food' ? 'https://food.marblemandi.org/' : (activeVertical === 'used' ? 'https://used.marblemandi.org/' : 'https://marblemandi.org/'))"></span>
                        </div>
                        <div class="text-[10px] font-bold uppercase text-purple-400" x-text="previewMode.toUpperCase()"></div>
                    </div>

                    <!-- Inner Live Render Stage -->
                    <div class="bg-[#faf5ff] text-zinc-900 rounded-2xl overflow-y-auto max-h-[750px] p-4 sm:p-6 no-scrollbar space-y-6">

                        <!-- VIEW 1: HOMEPAGE LIVE SIMULATION -->
                        <div x-show="activeVertical === 'homepage'" class="space-y-6">
                            
                            <!-- Hero Preview -->
                            <div class="text-center space-y-3 pt-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-slate-200 text-slate-900 text-[10px] font-black shadow-xs">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span x-text="hp_hero_badge"></span>
                                </div>

                                <h2 class="text-xl sm:text-3xl font-black text-slate-950 tracking-tight leading-tight">
                                    <span x-text="hp_title_line1"></span> <br>
                                    <span class="bg-gradient-to-r from-indigo-600 via-purple-600 to-cyan-600 bg-clip-text text-transparent" x-text="hp_title_line2"></span>
                                </h2>

                                <p class="text-xs text-slate-600 max-w-md mx-auto leading-relaxed" x-text="hp_description"></p>

                                <div class="p-1 rounded-full bg-white border border-slate-200 shadow-md flex items-center gap-2 max-w-sm mx-auto">
                                    <span class="pl-3 text-indigo-600">🔍</span>
                                    <input type="text" :placeholder="hp_search_placeholder" disabled class="w-full bg-transparent border-0 text-xs text-slate-700">
                                    <span class="px-4 py-1.5 rounded-full bg-indigo-600 text-white font-bold text-xs" x-text="hp_cta_text"></span>
                                </div>
                            </div>

                            <!-- Trending Products Preview -->
                            <div x-show="hp_show_trending" class="bg-white p-4 rounded-2xl border border-slate-200 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-black text-slate-900" x-text="hp_arrivals_title"></h4>
                                    <span class="text-[10px] text-indigo-600 font-bold">All Items &rarr;</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-center text-[10px]">
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="h-16 rounded-lg bg-indigo-100/50 flex items-center justify-center text-lg mb-1">📱</div>
                                        <div class="font-bold truncate">5G Smartphone</div>
                                        <div class="text-indigo-600 font-black">₹18,999</div>
                                    </div>
                                    <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                                        <div class="h-16 rounded-lg bg-indigo-100/50 flex items-center justify-center text-lg mb-1">👗</div>
                                        <div class="font-bold truncate">Silk Kurti Atelier</div>
                                        <div class="text-indigo-600 font-black">₹2,499</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Dedicated Services Showcase Preview -->
                            <div x-show="hp_show_services" class="p-4 rounded-2xl bg-gradient-to-br from-purple-100/60 to-purple-50 border-2 border-purple-200 space-y-3">
                                <div>
                                    <span class="px-2 py-0.5 rounded-full bg-purple-600 text-white text-[9px] font-black uppercase">Talent Hub</span>
                                    <h4 class="text-xs font-black text-purple-950 mt-1" x-text="hp_services_title"></h4>
                                    <p class="text-[10px] text-purple-700" x-text="hp_services_sub"></p>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-left text-[10px]">
                                    <div class="p-2 bg-white rounded-xl border border-purple-100 shadow-2xs">
                                        <div class="h-14 rounded-lg bg-purple-100 flex items-center justify-center text-lg mb-1">💻</div>
                                        <div class="font-bold text-zinc-900 truncate">Web Development</div>
                                        <div class="text-purple-700 font-black">From ₹4,999</div>
                                    </div>
                                    <div class="p-2 bg-white rounded-xl border border-purple-100 shadow-2xs">
                                        <div class="h-14 rounded-lg bg-purple-100 flex items-center justify-center text-lg mb-1">🎬</div>
                                        <div class="font-bold text-zinc-900 truncate">Reels &amp; Video Edit</div>
                                        <div class="text-purple-700 font-black">From ₹999</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Structured Categories Preview -->
                            <div x-show="hp_show_categories" class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-xs font-black text-slate-900" x-text="hp_cat_title"></h4>
                                    <span class="text-[10px] text-indigo-600">Explore &rarr;</span>
                                </div>
                                <div class="grid grid-cols-4 gap-1.5 text-center text-[9px] font-bold text-slate-700">
                                    <div class="p-2 bg-white rounded-xl border border-slate-100">📱 Gadgets</div>
                                    <div class="p-2 bg-white rounded-xl border border-slate-100">👗 Apparel</div>
                                    <div class="p-2 bg-white rounded-xl border border-slate-100">🍽️ Food</div>
                                    <div class="p-2 bg-white rounded-xl border border-slate-100">🔧 Services</div>
                                </div>
                            </div>

                            <!-- Value Assurance Pillars Preview -->
                            <div x-show="hp_show_pillars" class="grid grid-cols-2 gap-2 text-center text-[10px]">
                                <div class="p-2.5 bg-white rounded-xl border border-slate-100 font-bold">🛡️ Verified Sellers</div>
                                <div class="p-2.5 bg-white rounded-xl border border-slate-100 font-bold">⚡ Instant UPI</div>
                            </div>

                            <!-- Seller Banner Preview -->
                            <div x-show="hp_show_seller" class="p-4 rounded-2xl bg-slate-950 text-white text-center space-y-1">
                                <span class="text-[9px] uppercase font-bold text-indigo-400">0% Listing Fee</span>
                                <h5 class="text-xs font-black">Sell on K71 Today</h5>
                                <span class="inline-block px-3 py-1 bg-white text-slate-950 text-[10px] font-bold rounded-lg mt-1">Register Store &rarr;</span>
                            </div>

                        </div>

                        <!-- VIEW 2: SERVICES HUB LIVE SIMULATION -->
                        <div x-show="activeVertical === 'services'" class="space-y-6">
                            <div class="text-center space-y-3 pt-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white border border-purple-200 text-purple-700 text-[10px] font-black shadow-xs">
                                    <span class="w-2 h-2 rounded-full bg-purple-600 animate-pulse"></span>
                                    <span x-text="srv_hero_badge"></span>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight" x-text="srv_hero_title"></h2>
                                <p class="text-xs text-zinc-600 max-w-md mx-auto" x-text="srv_hero_sub"></p>

                                <div class="p-1 rounded-full bg-white border border-purple-200 shadow-md flex items-center gap-2 max-w-sm mx-auto">
                                    <span class="pl-3 text-purple-600">🔍</span>
                                    <input type="text" :placeholder="srv_search_placeholder" disabled class="w-full bg-transparent border-0 text-xs text-zinc-700">
                                    <span class="px-4 py-1.5 rounded-full bg-purple-600 text-white font-bold text-xs">Search</span>
                                </div>
                            </div>

                            <div class="p-4 bg-white rounded-2xl border border-purple-100 space-y-3">
                                <h4 class="text-xs font-black text-zinc-950" x-text="srv_cat_title"></h4>
                                <div class="grid grid-cols-2 gap-2 text-[10px]">
                                    <div class="p-2 rounded-xl bg-purple-50 text-purple-900 font-bold">💻 Web &amp; Apps</div>
                                    <div class="p-2 rounded-xl bg-purple-50 text-purple-900 font-bold">🎨 Graphic &amp; Logo</div>
                                    <div class="p-2 rounded-xl bg-purple-50 text-purple-900 font-bold">🎬 Video Editing</div>
                                    <div class="p-2 rounded-xl bg-purple-50 text-purple-900 font-bold">🔧 Craftsmanship</div>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-white border border-purple-100 space-y-3">
                                <h4 class="text-xs font-black text-zinc-950" x-text="srv_trending_title"></h4>
                                <div class="p-2.5 rounded-xl border border-purple-100 flex items-center justify-between text-xs">
                                    <div>
                                        <div class="font-bold text-zinc-900">Italian Marble Polishing</div>
                                        <div class="text-[10px] text-zinc-400">Artisan Ateliers • ⭐ 5.0</div>
                                    </div>
                                    <span class="font-black text-purple-700">₹2,499</span>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-gradient-to-r from-purple-700 to-indigo-700 text-white text-center space-y-1">
                                <h5 class="text-xs font-black" x-text="srv_seller_title"></h5>
                                <p class="text-[10px] text-purple-200" x-text="srv_seller_sub"></p>
                                <span class="inline-block px-3 py-1 bg-white text-purple-900 text-[10px] font-bold rounded-lg mt-1">Become a Specialist &rarr;</span>
                            </div>
                        </div>

                        <!-- VIEW 3: FOOD HUB LIVE SIMULATION -->
                        <div x-show="activeVertical === 'food'" class="space-y-6">
                            <div class="text-center space-y-3 pt-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-100 text-[#ea580c] text-[10px] font-black">
                                    <span x-text="food_hero_badge"></span>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-black text-stone-950 tracking-tight" x-text="food_hero_title"></h2>
                                <p class="text-xs text-stone-600 max-w-md mx-auto" x-text="food_hero_sub"></p>

                                <div class="p-1 rounded-full bg-white border border-orange-200 shadow-md flex items-center gap-2 max-w-sm mx-auto">
                                    <span class="pl-3 text-orange-600">🍲</span>
                                    <input type="text" placeholder="Search biryani, pizza, thalis..." disabled class="w-full bg-transparent border-0 text-xs text-stone-700">
                                    <span class="px-4 py-1.5 rounded-full bg-orange-600 text-white font-bold text-xs">Order</span>
                                </div>
                            </div>
                        </div>

                        <!-- VIEW 4: USED MARKET LIVE SIMULATION -->
                        <div x-show="activeVertical === 'used'" class="space-y-6">
                            <div class="text-center space-y-3 pt-2">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-800 text-[10px] font-black">
                                    <span x-text="used_hero_badge"></span>
                                </div>

                                <h2 class="text-xl sm:text-2xl font-black text-teal-950 tracking-tight" x-text="used_hero_title"></h2>
                                <p class="text-xs text-slate-600 max-w-md mx-auto" x-text="used_hero_sub"></p>
                            </div>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</x-layouts.admin>
