<x-layouts.admin title="Storefront & Content Management">
    @php
        $hp = $homepage ?? [];
        $sp = $servicesPage ?? [];
        $fp = $foodPage ?? [];
        $up = $usedPage ?? [];
        $seoData = $seo ?? [];
    @endphp

    <div x-data="{
        activeTab: 'homepage', // 'homepage', 'services', 'food', 'used', 'seo'
        
        // Homepage Live State
        hp_hero_badge: '{{ addslashes($hp['hero_badge'] ?? 'K71 UNIFIED MARKETPLACE • Verified Stores & Secure Escrow') }}',
        hp_title_line1: '{{ addslashes($hp['hero_title_line1'] ?? 'Discover Top Stores &') }}',
        hp_title_line2: '{{ addslashes($hp['hero_title_line2'] ?? 'Trending Products') }}',
        hp_description: '{{ addslashes($hp['hero_description'] ?? 'Shop directly from authorized distributor storefronts, boutique ateliers, and certified suppliers nationwide.') }}',
        hp_cta_text: '{{ addslashes($hp['hero_cta_primary_text'] ?? 'Search') }}',
        hp_search_placeholder: '{{ addslashes($hp['search_placeholder'] ?? 'Search products, brands, model numbers, stores...') }}',
        hp_arrivals_title: '{{ addslashes($hp['arrivals_section_title'] ?? 'Trending Products & Deals') }}',
        hp_services_title: '{{ addslashes($hp['services_section_title'] ?? 'Top Freelance & Professional Services') }}',
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
        food_hero_badge: '{{ addslashes($fp['food_hero_badge'] ?? '25-30 MINUTE EXPRESS DOORSTEP DISPATCH') }}',
        food_hero_title: '{{ addslashes($fp['food_hero_title'] ?? 'Crave it? Get it hot & fresh from top cloud kitchens.') }}',
        food_hero_sub: '{{ addslashes($fp['food_hero_subtitle'] ?? 'Explore hand-crafted dum biryanis, wood-fired artisan pizzas, gourmet burgers, and pure veg royal thalis.') }}',

        // Used Market Live State
        used_hero_badge: '{{ addslashes($up['used_hero_badge'] ?? 'K71 VERIFIED PRE-LOVED & RE-COMMERCE') }}',
        used_hero_title: '{{ addslashes($up['used_hero_title'] ?? 'Certified Second-Hand Goods & Direct Classifieds') }}',
        used_hero_sub: '{{ addslashes($up['used_hero_subtitle'] ?? 'Buy and sell authenticated pre-owned smartphones, laptops, electronics, and vehicles safely.') }}',
    }" class="space-y-6">

        <!-- Top Header & Primary Actions -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-zinc-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-mono uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-zinc-100 text-zinc-700">
                        Content Management
                    </span>
                    <span class="text-xs text-zinc-400">• Production Storefront</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-zinc-950 tracking-tight">
                    Storefront &amp; Merchandising Studio
                </h2>
                <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                    Configure homepage hero copy, vertical spotlight titles, search placeholders, and global SEO metadata.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <a :href="activeTab === 'services' ? '{{ k71_vertical_url('services') }}' : (activeTab === 'food' ? '{{ k71_vertical_url('food') }}' : (activeTab === 'used' ? '{{ k71_vertical_url('used') }}' : '{{ url('/') }}'))"
                   target="_blank"
                   class="px-3.5 py-2 rounded-lg bg-zinc-50 hover:bg-zinc-100 text-zinc-700 hover:text-zinc-950 text-xs font-semibold border border-zinc-200/80 transition flex items-center gap-1.5">
                    <span>Preview Live</span>
                    <span>↗</span>
                </a>

                <button type="submit" form="page-studio-form"
                        class="px-5 py-2 rounded-lg bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-semibold tracking-wide transition shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Changes</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-mono font-bold">Published</span>
            </div>
        @endif

        <!-- Minimal Monochrome Tabs Bar -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar border-b border-zinc-200 pb-px">
            <button type="button" @click="activeTab = 'homepage'"
                    :class="activeTab === 'homepage' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer">
                Homepage &amp; Retail
            </button>
            <button type="button" @click="activeTab = 'services'"
                    :class="activeTab === 'services' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer">
                Services Hub
            </button>
            <button type="button" @click="activeTab = 'food'"
                    :class="activeTab === 'food' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer">
                Food &amp; Dining
            </button>
            <button type="button" @click="activeTab = 'used'"
                    :class="activeTab === 'used' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer">
                Pre-Loved &amp; Used
            </button>
            <button type="button" @click="activeTab = 'seo'"
                    :class="activeTab === 'seo' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer">
                SEO &amp; Meta Tags
            </button>
        </div>

        <!-- Master Form -->
        <form id="page-studio-form" action="{{ route('admin.homepage.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <!-- TAB 1: HOMEPAGE CONTROLS -->
            <div x-show="activeTab === 'homepage'" class="space-y-6">
                
                <!-- Hero Header Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Homepage Hero Header
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Primary above-the-fold banner displayed on marblemandi.org.</p>
                        </div>
                        <span class="text-[10px] bg-zinc-100 text-zinc-700 font-mono font-semibold px-2 py-0.5 rounded">Above Fold</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Super Badge Pill</label>
                            <input type="text" name="hero_badge" x-model="hp_hero_badge"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-medium">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Headline Part 1</label>
                                <input type="text" name="hero_title_line1" x-model="hp_title_line1"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Headline Part 2</label>
                                <input type="text" name="hero_title_line2" x-model="hp_title_line2"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Hero Subtitle / Description</label>
                            <textarea name="hero_description" x-model="hp_description" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-medium"></textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Search Placeholder Text</label>
                                <input type="text" name="search_placeholder" x-model="hp_search_placeholder"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-medium">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Primary CTA Button Label</label>
                                <input type="text" name="hero_cta_primary_text" x-model="hp_cta_text"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Front Banner 3-Slide Carousel Studio Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-6" x-data="{ activeSlideTab: 1 }">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 flex-wrap gap-2">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-[10px] uppercase tracking-wider">3-Slide Carousel</span>
                                <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                    Homepage Front Banner Carousel
                                </h3>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">Customize images, titles, badges, and CTA buttons for all 3 rotating slides.</p>
                        </div>

                        <!-- Slide Switcher Pills -->
                        <div class="flex items-center gap-1.5 bg-zinc-100 p-1 rounded-xl">
                            <button type="button" @click="activeSlideTab = 1" 
                                    :class="activeSlideTab === 1 ? 'bg-white text-zinc-950 shadow-xs font-bold' : 'text-zinc-600 hover:text-zinc-950 font-medium'"
                                    class="px-3 py-1 rounded-lg text-xs transition cursor-pointer">
                                Slide 1 (Bazaar)
                            </button>
                            <button type="button" @click="activeSlideTab = 2" 
                                    :class="activeSlideTab === 2 ? 'bg-white text-zinc-950 shadow-xs font-bold' : 'text-zinc-600 hover:text-zinc-950 font-medium'"
                                    class="px-3 py-1 rounded-lg text-xs transition cursor-pointer">
                                Slide 2 (Dining)
                            </button>
                            <button type="button" @click="activeSlideTab = 3" 
                                    :class="activeSlideTab === 3 ? 'bg-white text-zinc-950 shadow-xs font-bold' : 'text-zinc-600 hover:text-zinc-950 font-medium'"
                                    class="px-3 py-1 rounded-lg text-xs transition cursor-pointer">
                                Slide 3 (Craft & Pro)
                            </button>
                        </div>
                    </div>

                    <!-- Slide 1 Fields -->
                    <div x-show="activeSlideTab === 1" class="space-y-4 text-xs">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Badge Tagline</label>
                                <input type="text" name="slide1_badge" value="{{ $hp['slide1_badge'] ?? '🔥 Mega Product Bazaar • Lowest Factory Prices' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Headline</label>
                                <input type="text" name="slide1_title" value="{{ $hp['slide1_title'] ?? 'Direct Products. Factory Direct Prices.' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Subtitle / Description</label>
                            <textarea name="slide1_subtitle" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">{{ $hp['slide1_subtitle'] ?? 'Shop trending fashion, smartphones, home living, cloud dining, and marble craft directly from verified Indian sellers with PhonePe escrow protection.' }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200/80 space-y-2">
                                <span class="font-bold text-zinc-900 block">Primary Button (Left)</span>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Text</label>
                                    <input type="text" name="slide1_btn1_text" value="{{ $hp['slide1_btn1_text'] ?? 'Browse All Products' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Link / URL</label>
                                    <input type="text" name="slide1_btn1_link" value="{{ $hp['slide1_btn1_link'] ?? '#product-showcase' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                                </div>
                            </div>

                            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200/80 space-y-2">
                                <span class="font-bold text-zinc-900 block">Secondary Button (Right)</span>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Text</label>
                                    <input type="text" name="slide1_btn2_text" value="{{ $hp['slide1_btn2_text'] ?? '₹199 Budget Store' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Link / URL</label>
                                    <input type="text" name="slide1_btn2_link" value="{{ $hp['slide1_btn2_link'] ?? '/catalog?sort=price_low' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Slide 1 Image File Upload</label>
                                <input type="file" name="slide1_image" accept="image/*"
                                       class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                                @if(!empty($hp['slide1_image']))
                                    <div class="mt-2 flex items-center gap-2">
                                        <img src="{{ asset('storage/' . $hp['slide1_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom uploaded image active</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Or Image URL</label>
                                <input type="text" name="slide1_image_url" value="{{ $hp['slide1_image_url'] ?? '' }}" placeholder="https://..."
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>

                    <!-- Slide 2 Fields -->
                    <div x-show="activeSlideTab === 2" class="space-y-4 text-xs" style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Badge Tagline</label>
                                <input type="text" name="slide2_badge" value="{{ $hp['slide2_badge'] ?? '⚡ Cloud Kitchen Dining • 30 Min Express' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Headline</label>
                                <input type="text" name="slide2_title" value="{{ $hp['slide2_title'] ?? 'Authentic Flavors. Delivered Hot & Fresh.' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Subtitle / Description</label>
                            <textarea name="slide2_subtitle" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">{{ $hp['slide2_subtitle'] ?? 'Experience royal Nawabi thalis, aromatic dum biryanis, gourmet chef treats, and hygienic contactless doorstep delivery.' }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200/80 space-y-2">
                                <span class="font-bold text-zinc-900 block">Primary Button (Left)</span>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Text</label>
                                    <input type="text" name="slide2_btn1_text" value="{{ $hp['slide2_btn1_text'] ?? 'Order Food Online' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Link / URL</label>
                                    <input type="text" name="slide2_btn1_link" value="{{ $hp['slide2_btn1_link'] ?? '/food' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                                </div>
                            </div>

                            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200/80 space-y-2">
                                <span class="font-bold text-zinc-900 block">Secondary Button (Right)</span>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Text</label>
                                    <input type="text" name="slide2_btn2_text" value="{{ $hp['slide2_btn2_text'] ?? 'View Restaurants' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Link / URL</label>
                                    <input type="text" name="slide2_btn2_link" value="{{ $hp['slide2_btn2_link'] ?? '/food#restaurants' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Slide 2 Image File Upload</label>
                                <input type="file" name="slide2_image" accept="image/*"
                                       class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                                @if(!empty($hp['slide2_image']))
                                    <div class="mt-2 flex items-center gap-2">
                                        <img src="{{ asset('storage/' . $hp['slide2_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom uploaded image active</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Or Image URL</label>
                                <input type="text" name="slide2_image_url" value="{{ $hp['slide2_image_url'] ?? '' }}" placeholder="https://..."
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>

                    <!-- Slide 3 Fields -->
                    <div x-show="activeSlideTab === 3" class="space-y-4 text-xs" style="display: none;">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Badge Tagline</label>
                                <input type="text" name="slide3_badge" value="{{ $hp['slide3_badge'] ?? '💎 Artisanal Craft & Certified Home Pro' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Headline</label>
                                <input type="text" name="slide3_title" value="{{ $hp['slide3_title'] ?? 'Makrana Marble & Pro Verified Services.' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Subtitle / Description</label>
                            <textarea name="slide3_subtitle" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">{{ $hp['slide3_subtitle'] ?? 'Quarry-direct marble slabs, temple carvings, diamond polishing, and verified home professionals with milestone payment security.' }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200/80 space-y-2">
                                <span class="font-bold text-zinc-900 block">Primary Button (Left)</span>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Text</label>
                                    <input type="text" name="slide3_btn1_text" value="{{ $hp['slide3_btn1_text'] ?? 'Explore Services' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Link / URL</label>
                                    <input type="text" name="slide3_btn1_link" value="{{ $hp['slide3_btn1_link'] ?? '/services' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                                </div>
                            </div>

                            <div class="p-3 bg-zinc-50 rounded-xl border border-zinc-200/80 space-y-2">
                                <span class="font-bold text-zinc-900 block">Secondary Button (Right)</span>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Text</label>
                                    <input type="text" name="slide3_btn2_text" value="{{ $hp['slide3_btn2_text'] ?? 'Submit B2B Enquiry' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                                </div>
                                <div>
                                    <label class="block text-zinc-500 mb-0.5">Button Link / URL</label>
                                    <input type="text" name="slide3_btn2_link" value="{{ $hp['slide3_btn2_link'] ?? '/enquiry' }}"
                                           class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Slide 3 Image File Upload</label>
                                <input type="file" name="slide3_image" accept="image/*"
                                       class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                                @if(!empty($hp['slide3_image']))
                                    <div class="mt-2 flex items-center gap-2">
                                        <img src="{{ asset('storage/' . $hp['slide3_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom uploaded image active</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Or Image URL</label>
                                <input type="text" name="slide3_image_url" value="{{ $hp['slide3_image_url'] ?? '' }}" placeholder="https://..."
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Titles Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Section Titles &amp; Headlines
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Customise editorial headlines across homepage discovery blocks.</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Trending Products Title</label>
                            <input type="text" name="arrivals_section_title" x-model="hp_arrivals_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Freelance Showcase Title</label>
                                <input type="text" name="services_section_title" x-model="hp_services_title"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Freelance Showcase Subtitle</label>
                                <input type="text" name="services_section_subtitle" x-model="hp_services_sub"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Structured Categories Title</label>
                                <input type="text" name="cat_section_title" x-model="hp_cat_title"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Structured Categories Subtitle</label>
                                <input type="text" name="cat_section_subtitle" x-model="hp_cat_sub"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-medium">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Visibility Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Section Visibility Toggles
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Toggle homepage sections on or off without altering database records.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Trending Products &amp; Deals</span>
                            <input type="checkbox" name="show_trending_products" value="1" x-model="hp_show_trending" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Services &amp; Gigs Showcase</span>
                            <input type="checkbox" name="show_services_showcase" value="1" x-model="hp_show_services" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Structured Categories</span>
                            <input type="checkbox" name="show_categories" value="1" x-model="hp_show_categories" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Value Assurance Pillars</span>
                            <input type="checkbox" name="show_value_pillars" value="1" x-model="hp_show_pillars" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition sm:col-span-2">
                            <span class="font-semibold text-zinc-900">Seller Onboarding Banner</span>
                            <input type="checkbox" name="show_seller_banner" value="1" x-model="hp_show_seller" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>
                    </div>
                </div>

            </div>

            <!-- TAB 2: SERVICES HUB CONTROLS -->
            <div x-show="activeTab === 'services'" class="space-y-6">
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Services Hub Header &amp; Discovery
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Configure services.marblemandi.org and /services hero information.</p>
                        </div>
                        <span class="text-[10px] bg-zinc-100 text-zinc-700 font-mono font-semibold px-2 py-0.5 rounded">services.k71.in</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Services Trust Badge</label>
                            <input type="text" name="services_hero_badge" x-model="srv_hero_badge"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Services Hero Title</label>
                            <input type="text" name="services_hero_title" x-model="srv_hero_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold text-sm">
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Services Hero Subtitle</label>
                            <textarea name="services_hero_subtitle" x-model="srv_hero_sub" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium"></textarea>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Services Search Placeholder</label>
                            <input type="text" name="services_search_placeholder" x-model="srv_search_placeholder"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Category Slider Title</label>
                                <input type="text" name="services_cat_title" x-model="srv_cat_title"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Trending Gigs Title</label>
                                <input type="text" name="services_trending_title" x-model="srv_trending_title"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold">
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Freelancer Onboarding Title</label>
                            <input type="text" name="services_seller_cta_title" x-model="srv_seller_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold">
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: FOOD & DINING CONTROLS -->
            <div x-show="activeTab === 'food'" class="space-y-6">
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Food &amp; Cloud Kitchens Header
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Configure food.marblemandi.org and /food discovery copy.</p>
                        </div>
                        <span class="text-[10px] bg-zinc-100 text-zinc-700 font-mono font-semibold px-2 py-0.5 rounded">food.k71.in</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Express Delivery Badge</label>
                            <input type="text" name="food_hero_badge" x-model="food_hero_badge"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Food Hero Title</label>
                            <input type="text" name="food_hero_title" x-model="food_hero_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold text-sm">
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Food Hero Subtitle</label>
                            <textarea name="food_hero_subtitle" x-model="food_hero_sub" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: PRE-LOVED & USED CONTROLS -->
            <div x-show="activeTab === 'used'" class="space-y-6">
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Pre-Loved &amp; Re-Commerce Hub
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Configure used.marblemandi.org and /used certified classifieds.</p>
                        </div>
                        <span class="text-[10px] bg-zinc-100 text-zinc-700 font-mono font-semibold px-2 py-0.5 rounded">used.k71.in</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Re-Commerce Badge</label>
                            <input type="text" name="used_hero_badge" x-model="used_hero_badge"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Used Market Hero Title</label>
                            <input type="text" name="used_hero_title" x-model="used_hero_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold text-sm">
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Used Market Subtitle</label>
                            <textarea name="used_hero_subtitle" x-model="used_hero_sub" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 5: SEO & META TAGS -->
            <div x-show="activeTab === 'seo'" class="space-y-6">
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Search Engine Metadata &amp; Social Previews
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Directly influences Google rankings, OpenGraph cards, and Twitter previews.</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Meta Title Tag</label>
                            <input type="text" name="seo_meta_title" value="{{ $seoData['seo_meta_title'] ?? 'K71 • Curated Multi-Vendor Marketplace' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Meta Description Tag</label>
                            <textarea name="seo_meta_description" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">{{ $seoData['seo_meta_description'] ?? 'Discover verified brand stores, boutique ateliers, and trending products on K71.' }}</textarea>
                        </div>

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Target Keywords</label>
                            <input type="text" name="seo_meta_keywords" value="{{ $seoData['seo_meta_keywords'] ?? 'marketplace, ecommerce, freelance services, cloud kitchen food' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Floating / Sticky Save Footer -->
            <div class="pt-4 flex items-center justify-end gap-3">
                <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 rounded-lg text-xs font-semibold text-zinc-600 hover:text-zinc-950 hover:bg-zinc-100 transition">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-lg bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-semibold tracking-wide transition shadow-xs flex items-center gap-2 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save &amp; Publish Storefront</span>
                </button>
            </div>
        </form>

    </div>
</x-layouts.admin>
