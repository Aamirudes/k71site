<x-layouts.admin title="Storefront & Content Management">
    @php
        $hp = $homepage ?? [];
        $sp = $servicesPage ?? [];
        $fp = $foodPage ?? [];
        $up = $usedPage ?? [];
        $seoData = $seo ?? [];
        $seq = $scrollSequence ?? \App\Services\ScrollSequenceService::resolveSequence($hp['scroll_seq_folder'] ?? null);
        $seqEnabled = array_key_exists('scroll_seq_enabled', $hp) ? (bool) $hp['scroll_seq_enabled'] : true;
        $homeCategoriesCount = isset($categories) ? $categories->where('show_on_home', true)->count() : 0;
        $totalCategoriesCount = isset($categories) ? $categories->count() : 0;
    @endphp

    <div x-data="{
        activeTab: @js($activeTab ?? 'homepage'), // 'homepage', 'categories', 'scroll_sequence', 'services', 'food', 'used', 'seo'
        catSearch: '',
        catFilter: 'all',
        catMatches(el) {
            const q = (this.catSearch || '').trim().toLowerCase();
            const text = (el.dataset.search || '');
            const onHome = el.dataset.home === '1';
            const vert = (el.dataset.vertical || 'marketplace');
            if (q && !text.includes(q)) return false;
            if (this.catFilter === 'home' && !onHome) return false;
            if (this.catFilter === 'hidden' && onHome) return false;
            if (this.catFilter === 'marketplace' && vert !== 'marketplace') return false;
            return true;
        },

        // Homepage Live State
        hp_hero_badge: @js($hp['hero_badge'] ?? 'K71 UNIFIED MARKETPLACE • Verified Stores & Secure Escrow'),
        hp_title_line1: @js($hp['hero_title_line1'] ?? 'Discover Top Stores &'),
        hp_title_line2: @js($hp['hero_title_line2'] ?? 'Trending Products'),
        hp_description: @js($hp['hero_description'] ?? 'Shop directly from authorized distributor storefronts, boutique ateliers, and certified suppliers nationwide.'),
        hp_cta_text: @js($hp['hero_cta_primary_text'] ?? 'Search'),
        hp_search_placeholder: @js($hp['search_placeholder'] ?? 'Search products, brands, model numbers, stores...'),
        hp_arrivals_title: @js(trim((string) ($hp['arrivals_section_title'] ?? '')) ?: 'Latest Products'),
        hp_services_title: @js(trim((string) ($hp['services_section_title'] ?? '')) ?: 'Verified Services'),
        hp_services_sub: @js(trim((string) ($hp['services_section_subtitle'] ?? '')) ?: 'Marble restoration, web engineering, photography & vetted home specialists.'),
        hp_food_title: @js(trim((string) ($hp['food_section_title'] ?? '')) ?: 'Artisan Kitchens'),
        hp_food_sub: @js(trim((string) ($hp['food_section_subtitle'] ?? '')) ?: 'Hygiene-certified cloud kitchens with fresh 30–45 min doorstep dispatch.'),
        hp_cat_title: @js($hp['cat_section_title'] ?? 'Structured Categories'),
        hp_cat_sub: @js($hp['cat_section_subtitle'] ?? 'Explore retail and trade verticals tailored for specific sectors.'),
        hp_show_hero_carousel: @js(array_key_exists('show_hero_carousel', $hp) ? (bool) $hp['show_hero_carousel'] : true),
        hp_show_trending: @js(array_key_exists('show_trending_products', $hp) ? (bool) $hp['show_trending_products'] : true),
        hp_show_food: @js(array_key_exists('show_food_showcase', $hp) ? (bool) $hp['show_food_showcase'] : true),
        hp_show_services: @js(array_key_exists('show_services_showcase', $hp) ? (bool) $hp['show_services_showcase'] : true),
        hp_show_categories: @js(array_key_exists('show_categories', $hp) ? (bool) $hp['show_categories'] : true),
        hp_show_pillars: @js(array_key_exists('show_value_pillars', $hp) ? (bool) $hp['show_value_pillars'] : true),
        hp_show_seller: @js(array_key_exists('show_seller_banner', $hp) ? (bool) $hp['show_seller_banner'] : true),

        // Services Hub Live State
        srv_hero_badge: @js($sp['services_hero_badge'] ?? 'K71 Verified Freelancers & Services'),
        srv_hero_title: @js($sp['services_hero_title'] ?? 'Find the Perfect Freelance Services'),
        srv_hero_sub: @js($sp['services_hero_subtitle'] ?? 'Hire proven specialists with upfront package pricing, milestone safety, and fast delivery.'),
        srv_search_placeholder: @js($sp['services_search_placeholder'] ?? 'What service are you looking for today? (e.g. Logo Design, Website)'),
        srv_cat_title: @js($sp['services_cat_title'] ?? 'Explore Services by Category'),
        srv_cat_sub: @js($sp['services_cat_subtitle'] ?? 'Select a category to view verified specialists'),
        srv_trending_title: @js($sp['services_trending_title'] ?? 'Trending Services & Packages'),
        srv_trending_sub: @js($sp['services_trending_subtitle'] ?? 'Vetted freelancers ready to start on your project today'),
        srv_seller_title: @js($sp['services_seller_cta_title'] ?? 'Are You a Skilled Freelancer or Professional?'),
        srv_seller_sub: @js($sp['services_seller_cta_subtitle'] ?? 'Join K71 Services and start earning. Create your gigs, set your packages, and receive guaranteed escrow payouts directly to your bank account.'),

        // Food Hub Live State
        food_hero_badge: @js($fp['food_hero_badge'] ?? '25-30 MINUTE EXPRESS DOORSTEP DISPATCH'),
        food_hero_title: @js($fp['food_hero_title'] ?? 'Crave it? Get it hot & fresh from top cloud kitchens.'),
        food_hero_sub: @js($fp['food_hero_subtitle'] ?? 'Explore hand-crafted dum biryanis, wood-fired artisan pizzas, fresh burgers, and pure veg royal thalis.'),

        // Used Market Live State
        used_hero_badge: @js($up['used_hero_badge'] ?? 'K71 VERIFIED PRE-LOVED & RE-COMMERCE'),
        used_hero_title: @js($up['used_hero_title'] ?? 'Certified Second-Hand Goods & Direct Classifieds'),
        used_hero_sub: @js($up['used_hero_subtitle'] ?? 'Buy and sell authenticated pre-owned smartphones, laptops, electronics, and vehicles safely.'),
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
                    Toggle homepage sections, manage main homepage categories &amp; images, and configure vertical headlines.
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

        @if($errors->any())
            <div class="p-4 bg-rose-50 text-rose-800 rounded-xl border border-rose-200 shadow-xs space-y-1">
                <div class="text-xs sm:text-sm font-bold">Please fix the following issue(s):</div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Minimal Monochrome Tabs Bar -->
        <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar border-b border-zinc-200 pb-px">
            <button type="button" @click="activeTab = 'homepage'"
                    :class="activeTab === 'homepage' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer">
                Homepage &amp; Retail
            </button>
            <button type="button" @click="activeTab = 'categories'"
                    :class="activeTab === 'categories' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer flex items-center gap-1.5">
                <span>Homepage Categories</span>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-mono"
                      :class="activeTab === 'categories' ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-700'">{{ $homeCategoriesCount }}/{{ $totalCategoriesCount }}</span>
            </button>
            <button type="button" @click="activeTab = 'scroll_sequence'"
                    :class="activeTab === 'scroll_sequence' ? 'bg-zinc-950 text-white shadow-xs font-bold' : 'bg-white hover:bg-zinc-100 text-zinc-700 border border-zinc-200/80'"
                    class="px-3.5 py-2 rounded-lg text-xs font-semibold transition shrink-0 cursor-pointer flex items-center gap-1.5">
                <span>Scroll Sequence</span>
                <span class="px-1.5 py-0.2 rounded-full text-[9px] font-mono"
                      :class="activeTab === 'scroll_sequence' ? 'bg-white/20 text-white' : 'bg-zinc-100 text-zinc-700'"
                      id="seq-tab-badge-count">{{ (int) ($seq['count'] ?? 0) }}f</span>
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

        <!-- Standalone Form Above Master Form: Add New Category to Main Homepage (Zero Nested Forms) -->
        <div x-show="activeTab === 'categories'" class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4" style="display: none;">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-zinc-100">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded bg-zinc-950 text-white font-bold text-[10px] uppercase tracking-wider">New Category</span>
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">Add Category to Main Homepage</h3>
                    </div>
                    <p class="text-xs text-zinc-500 mt-0.5">Create a new storefront category with custom image, emoji icon, and immediate homepage visibility.</p>
                </div>
            </div>

            <form action="{{ route('admin.homepage.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-zinc-800 mb-1">Category Name <span class="text-rose-600">*</span></label>
                        <input type="text" name="name" required maxlength="100" placeholder="e.g. Marble Slabs, Wedding Jewellery"
                               class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-semibold">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-800 mb-1">Vertical</label>
                        <select name="vertical" class="w-full px-3 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                            <option value="marketplace" selected>Marketplace (Retail)</option>
                            <option value="food">Food &amp; Dining</option>
                            <option value="services">Services Hub</option>
                            <option value="enquiry">B2B / Wholesale</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Icon / Emoji</label>
                            <input type="text" name="icon" maxlength="20" placeholder="🏺"
                                   class="w-full px-3 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium text-center">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Sort Order</label>
                            <input type="number" name="sort_order" min="0" max="9999" value="0"
                                   class="w-full px-3 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-zinc-800 mb-1">Category Thumbnail Image (Upload)</label>
                        <input type="file" name="image" accept="image/*"
                               class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                    </div>
                    <div>
                        <label class="block font-semibold text-zinc-800 mb-1">Or Image URL</label>
                        <input type="text" name="image_url" maxlength="500" placeholder="https://..."
                               class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">
                    <div class="flex flex-wrap items-center gap-4">
                        <label class="inline-flex items-center gap-2 font-semibold text-zinc-900 cursor-pointer">
                            <input type="hidden" name="show_on_home" value="0">
                            <input type="checkbox" name="show_on_home" value="1" checked class="w-4 h-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950">
                            <span>Show on Main Homepage Ribbon</span>
                        </label>
                        <label class="inline-flex items-center gap-2 font-semibold text-zinc-900 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950">
                            <span>Active in Catalog</span>
                        </label>
                    </div>

                    <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-zinc-950 hover:bg-zinc-800 text-white font-semibold text-xs transition shadow-xs cursor-pointer">
                        + Add Category to Homepage
                    </button>
                </div>
            </form>
        </div>

        <!-- Master Form -->
        <form id="page-studio-form" action="{{ route('admin.homepage.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="active_tab" :value="activeTab">

            <!-- TAB 1: HOMEPAGE CONTROLS -->
            <div x-show="activeTab === 'homepage'" class="space-y-6">

                <!-- Quick Links Banner for Carousel & Homepage Categories -->
                <div class="bg-zinc-950 text-white rounded-2xl p-5 border border-zinc-800 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-[10px] font-mono uppercase tracking-[0.18em] text-fuchsia-300 font-bold block">Quick Storefront Controls</span>
                        <h3 class="text-sm sm:text-base font-bold text-white">3-Slide Carousel Visibility &amp; Main Homepage Categories</h3>
                        <p class="text-xs text-zinc-400">
                            Carousel is currently <strong class="text-white" x-text="hp_show_hero_carousel ? 'VISIBLE' : 'HIDDEN'"></strong> •
                            <strong class="text-white">{{ $homeCategoriesCount }}</strong> categories showing on main homepage ribbon.
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button"
                                @click="hp_show_hero_carousel = !hp_show_hero_carousel"
                                :class="hp_show_hero_carousel ? 'bg-emerald-500/20 text-emerald-200 border-emerald-400/40' : 'bg-amber-500/20 text-amber-200 border-amber-400/40'"
                                class="px-4 py-2 rounded-lg border text-xs font-semibold transition cursor-pointer flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full" :class="hp_show_hero_carousel ? 'bg-emerald-400' : 'bg-amber-400'"></span>
                            <span x-text="hp_show_hero_carousel ? '3-Slide Carousel: ON (Click to Hide)' : '3-Slide Carousel: OFF (Click to Show)'"></span>
                        </button>
                        <button type="button"
                                @click="activeTab = 'categories'"
                                class="px-4 py-2 rounded-lg bg-white text-zinc-950 hover:bg-zinc-100 text-xs font-bold transition cursor-pointer">
                            Edit Category Images &amp; Add Categories &rarr;
                        </button>
                    </div>
                </div>
                
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
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100 flex-wrap gap-3">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 font-bold text-[10px] uppercase tracking-wider">3-Slide Carousel</span>
                                <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                    Homepage Front Banner Carousel
                                </h3>
                                <span x-show="!hp_show_hero_carousel" x-cloak class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-bold">
                                    Hidden on Homepage
                                </span>
                            </div>
                            <p class="text-xs text-zinc-500 mt-0.5">Customize images, titles, badges, and CTA buttons for all 3 rotating slides, or toggle visibility below.</p>
                        </div>

                        <div class="flex items-center flex-wrap gap-3">
                            <!-- Carousel Visibility Toggle Switch -->
                            <button type="button"
                                    role="switch"
                                    :aria-checked="hp_show_hero_carousel ? 'true' : 'false'"
                                    @click="hp_show_hero_carousel = !hp_show_hero_carousel"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl border text-xs font-semibold transition cursor-pointer"
                                    :class="hp_show_hero_carousel ? 'bg-zinc-950 text-white border-zinc-950' : 'bg-zinc-100 text-zinc-700 border-zinc-300'">
                                <span class="w-7 h-4 rounded-full relative transition-colors flex items-center px-0.5"
                                      :class="hp_show_hero_carousel ? 'bg-emerald-400' : 'bg-zinc-400'">
                                    <span class="w-3 h-3 rounded-full bg-white shadow transition-transform"
                                          :class="hp_show_hero_carousel ? 'translate-x-3' : 'translate-x-0'"></span>
                                </span>
                                <span x-text="hp_show_hero_carousel ? 'Shown on Homepage' : 'Hidden on Homepage'"></span>
                            </button>

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
                                    Slide 3 (Craft &amp; Pro)
                                </button>
                            </div>
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
                                    <input type="text" name="slide1_btn1_link" value="{{ $hp['slide1_btn1_link'] ?? '#latest-products' }}"
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
                                    <div class="mt-2 flex items-center gap-3 flex-wrap">
                                        <img src="{{ format_image_url($hp['slide1_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom uploaded image active</span>
                                        <label class="inline-flex items-center gap-1 text-[11px] text-rose-700 font-semibold cursor-pointer">
                                            <input type="checkbox" name="remove_slide1_image" value="1" class="rounded border-zinc-300 text-rose-600">
                                            <span>Remove image</span>
                                        </label>
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
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">{{ $hp['slide2_subtitle'] ?? 'Experience royal Nawabi thalis, aromatic dum biryanis, fresh chef treats, and hygienic contactless doorstep delivery.' }}</textarea>
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
                                    <div class="mt-2 flex items-center gap-3 flex-wrap">
                                        <img src="{{ format_image_url($hp['slide2_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom uploaded image active</span>
                                        <label class="inline-flex items-center gap-1 text-[11px] text-rose-700 font-semibold cursor-pointer">
                                            <input type="checkbox" name="remove_slide2_image" value="1" class="rounded border-zinc-300 text-rose-600">
                                            <span>Remove image</span>
                                        </label>
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
                                    <div class="mt-2 flex items-center gap-3 flex-wrap">
                                        <img src="{{ format_image_url($hp['slide3_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom uploaded image active</span>
                                        <label class="inline-flex items-center gap-1 text-[11px] text-rose-700 font-semibold cursor-pointer">
                                            <input type="checkbox" name="remove_slide3_image" value="1" class="rounded border-zinc-300 text-rose-600">
                                            <span>Remove image</span>
                                        </label>
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
                            <label class="block font-semibold text-zinc-800 mb-1">Latest Products Title (Shown Right After Scroll Stage)</label>
                            <input type="text" name="arrivals_section_title" x-model="hp_arrivals_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Food Showcase Title</label>
                                <input type="text" name="food_section_title" x-model="hp_food_title"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Food Showcase Subtitle</label>
                                <input type="text" name="food_section_subtitle" x-model="hp_food_sub"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-medium">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Services Showcase Title</label>
                                <input type="text" name="services_section_title" x-model="hp_services_title"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 focus:border-zinc-950 font-bold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Services Showcase Subtitle</label>
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
                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-indigo-50/70 border border-indigo-200/90 cursor-pointer hover:bg-indigo-100/70 transition sm:col-span-2">
                            <div>
                                <span class="font-bold text-zinc-950 block">3-Slide Hero Banner Carousel</span>
                                <span class="text-[11px] text-zinc-600">Uncheck to hide the 3-slide carousel from the top of the main homepage</span>
                            </div>
                            <input type="hidden" name="show_hero_carousel" value="0">
                            <input type="checkbox" name="show_hero_carousel" value="1" x-model="hp_show_hero_carousel" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Top Category Ribbon</span>
                            <input type="hidden" name="show_categories" value="0">
                            <input type="checkbox" name="show_categories" value="1" x-model="hp_show_categories" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Latest Products Showcase</span>
                            <input type="hidden" name="show_trending_products" value="0">
                            <input type="checkbox" name="show_trending_products" value="1" x-model="hp_show_trending" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Food &amp; Cloud Kitchens Showcase</span>
                            <input type="hidden" name="show_food_showcase" value="0">
                            <input type="checkbox" name="show_food_showcase" value="1" x-model="hp_show_food" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Services &amp; Gigs Showcase</span>
                            <input type="hidden" name="show_services_showcase" value="0">
                            <input type="checkbox" name="show_services_showcase" value="1" x-model="hp_show_services" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Value Assurance Pillars</span>
                            <input type="hidden" name="show_value_pillars" value="0">
                            <input type="checkbox" name="show_value_pillars" value="1" x-model="hp_show_pillars" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>

                        <label class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200/80 cursor-pointer hover:bg-zinc-100 transition">
                            <span class="font-semibold text-zinc-900">Seller Onboarding Banner</span>
                            <input type="hidden" name="show_seller_banner" value="0">
                            <input type="checkbox" name="show_seller_banner" value="1" x-model="hp_show_seller" class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                        </label>
                    </div>
                </div>

            </div>

            <!-- TAB 1B: HOMEPAGE CATEGORIES (EDIT IMAGES, ORDER, HOMEPAGE RIBBON) -->
            <div x-show="activeTab === 'categories'" class="space-y-6" style="display: none;">
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-5">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Main Homepage Categories &amp; Images
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                Upload custom images, paste external image URLs, reorder tiles, or toggle which categories appear on the main homepage top ribbon.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <input type="search" x-model="catSearch" placeholder="Search categories..."
                                   class="px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 w-48 sm:w-56">
                            <div class="flex items-center gap-1 bg-zinc-100 p-1 rounded-lg">
                                <button type="button" @click="catFilter = 'all'"
                                        :class="catFilter === 'all' ? 'bg-white text-zinc-950 font-bold shadow-xs' : 'text-zinc-600'"
                                        class="px-2.5 py-1 rounded text-[11px] cursor-pointer">All</button>
                                <button type="button" @click="catFilter = 'home'"
                                        :class="catFilter === 'home' ? 'bg-white text-zinc-950 font-bold shadow-xs' : 'text-zinc-600'"
                                        class="px-2.5 py-1 rounded text-[11px] cursor-pointer">On Home</button>
                                <button type="button" @click="catFilter = 'hidden'"
                                        :class="catFilter === 'hidden' ? 'bg-white text-zinc-950 font-bold shadow-xs' : 'text-zinc-600'"
                                        class="px-2.5 py-1 rounded text-[11px] cursor-pointer">Off Home</button>
                                <button type="button" @click="catFilter = 'marketplace'"
                                        :class="catFilter === 'marketplace' ? 'bg-white text-zinc-950 font-bold shadow-xs' : 'text-zinc-600'"
                                        class="px-2.5 py-1 rounded text-[11px] cursor-pointer">Retail</button>
                            </div>
                        </div>
                    </div>

                    @if(isset($categories) && $categories->isNotEmpty())
                        <div class="divide-y divide-zinc-200/80 border border-zinc-200/80 rounded-xl overflow-hidden">
                            @foreach($categories as $cat)
                                @php
                                    $catVert = $cat->vertical ?: 'marketplace';
                                    $canDeleteCat = ($cat->products_count ?? 0) === 0 && ($cat->children_count ?? 0) === 0;
                                @endphp
                                <div x-show="catMatches($el)"
                                     data-search="{{ strtolower($cat->name . ' ' . $cat->slug) }}"
                                     data-home="{{ $cat->show_on_home ? '1' : '0' }}"
                                     data-vertical="{{ $catVert }}"
                                     class="p-4 bg-white hover:bg-zinc-50/70 transition space-y-3 text-xs">
                                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                                        <!-- Preview Thumbnail -->
                                        <div class="flex items-center gap-3 shrink-0">
                                            <div class="w-16 h-16 rounded-xl bg-zinc-950 text-white border border-zinc-200 flex items-center justify-center overflow-hidden shrink-0">
                                                @if(!empty($cat->image))
                                                    <img src="{{ format_image_url($cat->image) }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                                                @elseif(!empty($cat->icon))
                                                    <span class="text-2xl leading-none">{{ $cat->icon }}</span>
                                                @else
                                                    <span class="text-sm font-bold text-fuchsia-200 uppercase">{{ mb_substr($cat->name, 0, 2) }}</span>
                                                @endif
                                            </div>
                                            <div class="lg:hidden">
                                                <div class="font-bold text-zinc-950 text-sm">{{ $cat->name }}</div>
                                                <div class="text-[11px] text-zinc-500">{{ ucfirst($catVert) }} • {{ $cat->products_count ?? 0 }} products</div>
                                            </div>
                                        </div>

                                        <!-- Editable Meta Inputs -->
                                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                                            <div class="sm:col-span-4">
                                                <label class="block text-[11px] font-semibold text-zinc-600 mb-1">Name</label>
                                                <input type="text" name="category_meta[{{ $cat->id }}][name]" value="{{ $cat->name }}"
                                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-[11px] font-semibold text-zinc-600 mb-1">Icon / Emoji</label>
                                                <input type="text" name="category_meta[{{ $cat->id }}][icon]" value="{{ $cat->icon }}" placeholder="🛍️"
                                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white text-center">
                                            </div>
                                            <div class="sm:col-span-2">
                                                <label class="block text-[11px] font-semibold text-zinc-600 mb-1">Order</label>
                                                <input type="number" name="category_meta[{{ $cat->id }}][sort_order]" min="0" max="9999" value="{{ (int) $cat->sort_order }}"
                                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono">
                                            </div>
                                            <div class="sm:col-span-4 flex flex-wrap items-center gap-3 pb-1.5">
                                                <label class="inline-flex items-center gap-1.5 font-semibold text-zinc-900 cursor-pointer">
                                                    <input type="hidden" name="category_meta[{{ $cat->id }}][show_on_home]" value="0">
                                                    <input type="checkbox" name="category_meta[{{ $cat->id }}][show_on_home]" value="1"
                                                           {{ $cat->show_on_home ? 'checked' : '' }}
                                                           class="w-4 h-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950">
                                                    <span>Show on Home</span>
                                                </label>
                                                <label class="inline-flex items-center gap-1.5 font-semibold text-zinc-700 cursor-pointer">
                                                    <input type="hidden" name="category_meta[{{ $cat->id }}][is_active]" value="0">
                                                    <input type="checkbox" name="category_meta[{{ $cat->id }}][is_active]" value="1"
                                                           {{ $cat->is_active ? 'checked' : '' }}
                                                           class="w-4 h-4 rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950">
                                                    <span>Active</span>
                                                </label>
                                                @if($canDeleteCat)
                                                    <button type="button"
                                                            onclick="if(confirm('Delete unused category?')) document.getElementById('delete-category-{{ $cat->id }}').submit();"
                                                            class="px-2.5 py-1 rounded bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold text-[11px] cursor-pointer">
                                                        Delete
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Image Upload / URL / Removal Row -->
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-zinc-100 items-center">
                                        <div class="sm:col-span-5">
                                            <label class="block text-[11px] font-semibold text-zinc-600 mb-1">Upload Custom Image</label>
                                            <input type="file" name="category_images[{{ $cat->id }}]" accept="image/*"
                                                   class="w-full text-[11px] text-zinc-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                                        </div>
                                        <div class="sm:col-span-5">
                                            <label class="block text-[11px] font-semibold text-zinc-600 mb-1">Or Image URL</label>
                                            <input type="text" name="category_image_urls[{{ $cat->id }}]"
                                                   value="{{ (!empty($cat->image) && str_starts_with($cat->image, 'http')) ? $cat->image : '' }}"
                                                   placeholder="https://..."
                                                   class="w-full px-3 py-1.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                                        </div>
                                        <div class="sm:col-span-2 flex flex-col justify-end gap-1">
                                            <span class="text-[10px] font-mono text-zinc-400 truncate">{{ ucfirst($catVert) }} • {{ $cat->products_count ?? 0 }} items</span>
                                            @if(!empty($cat->image))
                                                <label class="inline-flex items-center gap-1.5 text-[11px] text-rose-700 font-semibold cursor-pointer">
                                                    <input type="checkbox" name="remove_category_image[{{ $cat->id }}]" value="1" class="rounded border-zinc-300 text-rose-600">
                                                    <span>Remove img</span>
                                                </label>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center rounded-xl bg-zinc-50 border border-zinc-200 text-xs text-zinc-500">
                            No categories yet. Use the form above to add your first main homepage category.
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 2: SERVICES HUB CONTROLS -->
            <div x-show="activeTab === 'services'" class="space-y-6">
                
                <!-- Services Hero & Header Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Services Hub Header &amp; Discovery
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Configure services.k71.in and /services freelance hub hero copy.</p>
                        </div>
                        <span class="text-[10px] bg-indigo-50 text-indigo-700 font-mono font-semibold px-2 py-0.5 rounded">services.k71.in</span>
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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Services Hero Banner Image Upload</label>
                                <input type="file" name="services_hero_image" accept="image/*"
                                       class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                                @if(!empty($sp['services_hero_image']))
                                    <div class="mt-2 flex items-center gap-2">
                                        <img src="{{ format_image_url($sp['services_hero_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom banner active</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Or Banner Image URL</label>
                                <input type="text" name="services_hero_image_url" value="{{ $sp['services_hero_image_url'] ?? '' }}" placeholder="https://..."
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services Section Titles Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Section Titles &amp; Headings
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Customize category sliders and trending gig showcases.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Category Slider Title</label>
                            <input type="text" name="services_cat_title" x-model="srv_cat_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Category Slider Subtitle</label>
                            <input type="text" name="services_cat_subtitle" x-model="srv_cat_sub"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Trending Gigs Title</label>
                            <input type="text" name="services_trending_title" x-model="srv_trending_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Trending Gigs Subtitle</label>
                            <input type="text" name="services_trending_subtitle" x-model="srv_trending_sub"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>
                    </div>
                </div>

                <!-- Freelancer Onboarding CTA Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Freelancer &amp; Specialist Onboarding CTA
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Recruit skilled freelancers with escrow milestone guarantees.</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Banner Title</label>
                            <input type="text" name="services_seller_cta_title" x-model="srv_seller_title"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Banner Subtitle / Value Proposition</label>
                            <textarea name="services_seller_cta_subtitle" x-model="srv_seller_sub" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium"></textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Button Text</label>
                                <input type="text" name="services_seller_cta_btn_text" value="{{ $sp['services_seller_cta_btn_text'] ?? 'Start Selling Services' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Button Link</label>
                                <input type="text" name="services_seller_cta_btn_link" value="{{ $sp['services_seller_cta_btn_link'] ?? '/vendor/register' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Services Visual Simulation Card -->
                <div class="bg-zinc-950 text-white rounded-2xl p-6 border border-zinc-800 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-indigo-400 font-bold block">Interactive Preview</span>
                            <h4 class="text-sm font-bold text-white mt-0.5">Services Hub Hero Simulation</h4>
                        </div>
                        <a href="{{ k71_vertical_url('services') }}" target="_blank" class="text-xs text-indigo-300 hover:underline font-mono">Open Live ↗</a>
                    </div>

                    <div class="p-4 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-3">
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-black uppercase tracking-wider"
                              x-text="srv_hero_badge || 'SERVICES TRUST'"></span>
                        <h3 class="text-base sm:text-lg font-black text-white" x-text="srv_hero_title"></h3>
                        <p class="text-xs text-zinc-400 line-clamp-2" x-text="srv_hero_sub"></p>
                        <div class="flex items-center gap-2 pt-1">
                            <div class="flex-1 py-2 px-3 rounded-lg bg-zinc-800 text-zinc-400 text-xs border border-zinc-700 flex items-center justify-between">
                                <span x-text="srv_search_placeholder || 'Search services...'"></span>
                                <span class="text-zinc-500">🔍</span>
                            </div>
                            <span class="px-3.5 py-2 rounded-lg bg-white text-zinc-950 font-bold text-xs">Search</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- TAB 3: FOOD & DINING CONTROLS -->
            <div x-show="activeTab === 'food'" class="space-y-6">
                
                <!-- Food Hero & Express Delivery Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Food &amp; Cloud Kitchens Header
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Configure food.k71.in and /food express doorstep dining hub.</p>
                        </div>
                        <span class="text-[10px] bg-amber-50 text-amber-800 font-mono font-semibold px-2 py-0.5 rounded">food.k71.in</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Express Delivery Trust Badge</label>
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

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Food Search Placeholder</label>
                            <input type="text" name="food_search_placeholder" value="{{ $fp['food_search_placeholder'] ?? 'Search biryani, pizza, thali, rolls, desserts...' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Food Hero Banner Image Upload</label>
                                <input type="file" name="food_hero_image" accept="image/*"
                                       class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                                @if(!empty($fp['food_hero_image']))
                                    <div class="mt-2 flex items-center gap-2">
                                        <img src="{{ format_image_url($fp['food_hero_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom banner active</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Or Banner Image URL</label>
                                <input type="text" name="food_hero_image_url" value="{{ $fp['food_hero_image_url'] ?? '' }}" placeholder="https://..."
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Food Section Titles Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Section Titles &amp; Headings
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Customize cuisine collections and cloud kitchen showcases.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Cuisines Slider Title</label>
                            <input type="text" name="food_cuisines_title" value="{{ $fp['food_cuisines_title'] ?? 'Popular Regional Cuisines' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Cuisines Slider Subtitle</label>
                            <input type="text" name="food_cuisines_subtitle" value="{{ $fp['food_cuisines_subtitle'] ?? 'Authentic flavors delivered hot to your doorstep' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Cloud Kitchens Showcase Title</label>
                            <input type="text" name="food_restaurants_title" value="{{ $fp['food_restaurants_title'] ?? 'Top Hygiene-Certified Cloud Kitchens' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Cloud Kitchens Showcase Subtitle</label>
                            <input type="text" name="food_restaurants_subtitle" value="{{ $fp['food_restaurants_subtitle'] ?? 'Hand-crafted meals by vetted culinary masters' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                        </div>
                    </div>
                </div>

                <!-- Kitchen Partner Onboarding CTA Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Cloud Kitchen &amp; Restaurant Onboarding CTA
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Recruit cloud kitchens, home chefs, and dining ateliers.</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Banner Title</label>
                            <input type="text" name="food_seller_cta_title" value="{{ $fp['food_seller_cta_title'] ?? 'Are You a Restaurant, Cloud Kitchen, or Home Baker?' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Banner Subtitle / Value Proposition</label>
                            <textarea name="food_seller_cta_subtitle" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">{{ $fp['food_seller_cta_subtitle'] ?? 'List your menu on K71 Food with zero extortionate commission rates. Direct customer payouts, automated dispatch alerts, and verified customer reviews.' }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Button Text</label>
                                <input type="text" name="food_seller_cta_btn_text" value="{{ $fp['food_seller_cta_btn_text'] ?? 'Register Kitchen' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Button Link</label>
                                <input type="text" name="food_seller_cta_btn_link" value="{{ $fp['food_seller_cta_btn_link'] ?? '/vendor/register' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Food Visual Simulation Card -->
                <div class="bg-zinc-950 text-white rounded-2xl p-6 border border-zinc-800 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-amber-400 font-bold block">Interactive Preview</span>
                            <h4 class="text-sm font-bold text-white mt-0.5">Food Hub Hero Simulation</h4>
                        </div>
                        <a href="{{ k71_vertical_url('food') }}" target="_blank" class="text-xs text-amber-300 hover:underline font-mono">Open Live ↗</a>
                    </div>

                    <div class="p-4 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-3">
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-black uppercase tracking-wider"
                              x-text="food_hero_badge || 'EXPRESS DELIVERY'"></span>
                        <h3 class="text-base sm:text-lg font-black text-white" x-text="food_hero_title"></h3>
                        <p class="text-xs text-zinc-400 line-clamp-2" x-text="food_hero_sub"></p>
                    </div>
                </div>

            </div>

            <!-- TAB 4: PRE-LOVED & USED CONTROLS -->
            <div x-show="activeTab === 'used'" class="space-y-6">
                
                <!-- Used Re-Commerce Hero Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                                Pre-Loved &amp; Re-Commerce Hub
                            </h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Configure used.k71.in and /used certified classifieds &amp; thrift.</p>
                        </div>
                        <span class="text-[10px] bg-cyan-50 text-cyan-800 font-mono font-semibold px-2 py-0.5 rounded">used.k71.in</span>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Re-Commerce Verification Badge</label>
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

                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Search Placeholder</label>
                            <input type="text" name="used_search_placeholder" value="{{ $up['used_search_placeholder'] ?? 'Search iPhone 15, Royal Enfield, Dell XPS, DSLR, Vintage watches...' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white focus:ring-2 focus:ring-zinc-950 font-medium">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Used Hero Banner Image Upload</label>
                                <input type="file" name="used_hero_image" accept="image/*"
                                       class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200">
                                @if(!empty($up['used_hero_image']))
                                    <div class="mt-2 flex items-center gap-2">
                                        <img src="{{ format_image_url($up['used_hero_image']) }}" class="w-16 h-10 object-cover rounded border">
                                        <span class="text-[11px] text-zinc-500">Custom banner active</span>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Or Banner Image URL</label>
                                <input type="text" name="used_hero_image_url" value="{{ $up['used_hero_image_url'] ?? '' }}" placeholder="https://..."
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Used Section Titles Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Section Titles &amp; Headings
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Customize verified categories and inspected deal showcases.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Categories Grid Title</label>
                            <input type="text" name="used_cat_title" value="{{ $up['used_cat_title'] ?? 'Browse Certified Re-Commerce Verticals' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Categories Grid Subtitle</label>
                            <input type="text" name="used_cat_subtitle" value="{{ $up['used_cat_subtitle'] ?? 'Inspected electronics, vehicles, furniture and vintage collectibles' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Trending Deals Title</label>
                            <input type="text" name="used_trending_title" value="{{ $up['used_trending_title'] ?? 'Fresh Inspected Pre-Owned Deals' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Trending Deals Subtitle</label>
                            <input type="text" name="used_trending_subtitle" value="{{ $up['used_trending_subtitle'] ?? 'Every item inspected by our certified technical team' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">
                        </div>
                    </div>
                </div>

                <!-- Seller Classified Onboarding CTA Card -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-4">
                    <div class="pb-3 border-b border-zinc-100">
                        <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">
                            Post Free Classified / Sell Used Items CTA
                        </h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Encourage customers and traders to sell pre-owned items directly.</p>
                    </div>

                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Banner Title</label>
                            <input type="text" name="used_seller_cta_title" value="{{ $up['used_seller_cta_title'] ?? 'Got Something to Sell? Turn Pre-Owned Items into Cash' }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold">
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Banner Subtitle / Value Proposition</label>
                            <textarea name="used_seller_cta_subtitle" rows="2"
                                      class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium">{{ $up['used_seller_cta_subtitle'] ?? 'Post free verified classifieds in 60 seconds. Connect directly with buyers across India with optional PhonePe escrow inspection protection.' }}</textarea>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Button Text</label>
                                <input type="text" name="used_seller_cta_btn_text" value="{{ $up['used_seller_cta_btn_text'] ?? 'Post Free Ad' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-semibold">
                            </div>
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Button Link</label>
                                <input type="text" name="used_seller_cta_btn_link" value="{{ $up['used_seller_cta_btn_link'] ?? '/used/create' }}"
                                       class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px]">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Used Visual Simulation Card -->
                <div class="bg-zinc-950 text-white rounded-2xl p-6 border border-zinc-800 shadow-xl space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-zinc-800">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-widest text-cyan-400 font-bold block">Interactive Preview</span>
                            <h4 class="text-sm font-bold text-white mt-0.5">Pre-Loved Hub Hero Simulation</h4>
                        </div>
                        <a href="{{ k71_vertical_url('used') }}" target="_blank" class="text-xs text-cyan-300 hover:underline font-mono">Open Live ↗</a>
                    </div>

                    <div class="p-4 rounded-xl bg-zinc-900/80 border border-zinc-800 space-y-3">
                        <span class="inline-block px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-[10px] font-black uppercase tracking-wider"
                              x-text="used_hero_badge || 'PRE-LOVED VERIFIED'"></span>
                        <h3 class="text-base sm:text-lg font-black text-white" x-text="used_hero_title"></h3>
                        <p class="text-xs text-zinc-400 line-clamp-2" x-text="used_hero_sub"></p>
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

            <!-- TAB 6: SCROLL FRAME SEQUENCE STUDIO -->
            <div x-show="activeTab === 'scroll_sequence'" class="space-y-6" style="display: none;">

                <!-- Active Sequence Status & Micro-Scrub Preview -->
                <div class="bg-zinc-950 text-white rounded-2xl p-6 border border-zinc-800 shadow-xl space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-zinc-800">
                        <div>
                            <span class="text-[10px] font-mono uppercase tracking-[0.2em] text-fuchsia-300 font-bold block">Sticky Pinned Scrollytelling Engine</span>
                            <h3 class="text-base sm:text-lg font-bold text-white mt-0.5">Active Scroll Frame Sequence</h3>
                            <p class="text-xs text-zinc-400 mt-0.5" id="seq-status-label">{{ $seq['label'] ?? 'Studio 3D Preview' }}</p>
                        </div>
                        <div class="flex items-center gap-2.5 shrink-0">
                            <span class="px-3 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-mono text-amber-300 font-semibold" id="seq-status-count">
                                {{ (int) ($seq['count'] ?? 0) }} Frames
                            </span>
                            <button type="button"
                                    id="seq-clear-btn"
                                    class="px-3.5 py-1.5 rounded-lg bg-rose-500/20 hover:bg-rose-500/35 text-rose-200 border border-rose-500/30 text-xs font-semibold transition cursor-pointer {{ ($seq['source'] ?? 'fallback') === 'uploaded' ? '' : 'hidden' }}">
                                Clear Uploaded Frames
                            </button>
                        </div>
                    </div>

                    <!-- Micro Scrub Previewer -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">
                        <div class="lg:col-span-5">
                            <div class="aspect-video rounded-xl overflow-hidden bg-black border border-zinc-800 relative flex items-center justify-center">
                                <img id="seq-preview-img"
                                     src="{{ !empty($seq['frames'][0]) ? $seq['frames'][0] : '' }}"
                                     alt="Frame Preview"
                                     class="w-full h-full object-cover {{ empty($seq['frames']) ? 'hidden' : '' }}">
                                <div id="seq-preview-empty"
                                     class="p-6 text-center space-y-1.5 {{ empty($seq['frames']) ? '' : 'hidden' }}">
                                    <span class="text-xs font-semibold text-fuchsia-300 block">3D Procedural Preview Active on Homepage</span>
                                    <p class="text-[11px] text-zinc-400 leading-relaxed">Upload a video (.mp4/.webm/.mov), numbered frame images, or a .zip below to replace the 3D monolith with your custom camera frames.</p>
                                </div>
                                <span id="seq-preview-badge"
                                      class="absolute bottom-2 right-2 px-2 py-0.5 rounded bg-black/75 text-white text-[10px] font-mono border border-white/15 {{ empty($seq['frames']) ? 'hidden' : '' }}">
                                    Frame 1 / {{ (int) ($seq['count'] ?? 0) }}
                                </span>
                            </div>
                        </div>

                        <div class="lg:col-span-7 space-y-4">
                            <div>
                                <div class="flex items-center justify-between text-xs mb-1.5">
                                    <span class="font-semibold text-zinc-200">Test Scrub Slider</span>
                                    <span class="font-mono text-zinc-400" id="seq-scrub-readout">0%</span>
                                </div>
                                <input type="range"
                                       id="seq-preview-slider"
                                       min="0"
                                       max="{{ max(0, count($seq['frames'] ?? []) - 1) }}"
                                       value="0"
                                       class="w-full accent-fuchsia-300 cursor-pointer"
                                       {{ empty($seq['frames']) ? 'disabled' : '' }}>
                            </div>

                            <!-- Live Upload Progress Telemetry -->
                            <div id="seq-progress-box" class="hidden p-3.5 rounded-xl bg-zinc-900 border border-zinc-800 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-fuchsia-200" id="seq-progress-msg">Preparing frames...</span>
                                    <span class="font-mono text-amber-300" id="seq-progress-pct">0%</span>
                                </div>
                                <div class="w-full h-1.5 bg-zinc-800 rounded-full overflow-hidden">
                                    <div id="seq-progress-fill" class="h-full bg-gradient-to-r from-amber-300 to-fuchsia-300 transition-all duration-150" style="width: 0%;"></div>
                                </div>
                            </div>

                            <div id="seq-feedback-banner" class="hidden p-3 rounded-xl text-xs font-medium"></div>
                        </div>
                    </div>
                </div>

                <!-- Upload Options Grid (Pure JS/Fetch Actions - No Nested Forms) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Option A: In-Browser Video to WebP Frames Converter -->
                    <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs space-y-4">
                        <div class="pb-3 border-b border-zinc-100">
                            <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded bg-zinc-950 text-white font-bold">Option A • Recommended</span>
                            <h4 class="text-sm font-bold text-zinc-950 mt-2">Video to Frames (In-Browser)</h4>
                            <p class="text-xs text-zinc-500 mt-0.5">Select an MP4, WebM, or MOV. Extracted directly to crisp WebP frames and uploaded in chunks.</p>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Choose Video File</label>
                                <input type="file" id="seq-video-input" accept="video/mp4,video/webm,video/quicktime"
                                       class="w-full text-xs text-zinc-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-950 file:text-white hover:file:bg-zinc-800 cursor-pointer">
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-zinc-800 mb-1">Frames</label>
                                    <select id="seq-video-count" class="w-full px-2.5 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono">
                                        <option value="48">48 frames (Fast)</option>
                                        <option value="72" selected>72 frames (Smooth)</option>
                                        <option value="96">96 frames (Cinema)</option>
                                        <option value="120">120 frames (Ultra)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-zinc-800 mb-1">Max Width</label>
                                    <select id="seq-video-width" class="w-full px-2.5 py-2 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono">
                                        <option value="960">960px</option>
                                        <option value="1280" selected>1280px (HD)</option>
                                        <option value="1440">1440px</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">WebP Quality (<span id="seq-q-val">0.82</span>)</label>
                                <input type="range" id="seq-video-quality" min="0.65" max="0.92" step="0.01" value="0.82"
                                       oninput="document.getElementById('seq-q-val').textContent = Number(this.value).toFixed(2)"
                                       class="w-full accent-zinc-950">
                            </div>

                            <button type="button"
                                    id="seq-video-upload-btn"
                                    class="w-full py-2.5 px-4 rounded-xl bg-zinc-950 hover:bg-zinc-800 text-white font-semibold text-xs transition cursor-pointer active:scale-98">
                                Extract Video &amp; Upload Frames
                            </button>
                        </div>
                    </div>

                    <!-- Option B: Direct Pre-Converted Frame Images -->
                    <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs space-y-4">
                        <div class="pb-3 border-b border-zinc-100">
                            <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded bg-zinc-100 text-zinc-700 font-bold">Option B • Images</span>
                            <h4 class="text-sm font-bold text-zinc-950 mt-2">Upload Converted Frame Images</h4>
                            <p class="text-xs text-zinc-500 mt-0.5">Already converted your video into numbered JPG/WebP/PNG frames? Select all images at once.</p>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Select All Sequence Frames</label>
                                <input type="file" id="seq-images-input" multiple accept="image/webp,image/jpeg,image/png"
                                       class="w-full text-xs text-zinc-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-900 hover:file:bg-zinc-200 cursor-pointer">
                            </div>

                            <p class="text-[11px] text-zinc-500 leading-relaxed">
                                Files are automatically sorted naturally (<code class="font-mono bg-zinc-100 px-1 rounded">frame_1, frame_2, ..., frame_90</code>) and uploaded in 8-frame batches.
                            </p>

                            <button type="button"
                                    id="seq-images-upload-btn"
                                    class="w-full py-2.5 px-4 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white font-semibold text-xs transition cursor-pointer">
                                Upload Selected Frames
                            </button>
                        </div>
                    </div>

                    <!-- Option C: ZIP Archive or Drop-In Folder -->
                    <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs space-y-4">
                        <div class="pb-3 border-b border-zinc-100">
                            <span class="text-[10px] font-mono uppercase tracking-wider px-2 py-0.5 rounded bg-zinc-100 text-zinc-700 font-bold">Option C • ZIP / CLI</span>
                            <h4 class="text-sm font-bold text-zinc-950 mt-2">Upload .zip Archive or Drop Folder</h4>
                            <p class="text-xs text-zinc-500 mt-0.5">Upload a compressed <code class="font-mono">.zip</code> containing numbered frames, or drop files directly in the repo folder.</p>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div>
                                <label class="block font-semibold text-zinc-800 mb-1">Choose Frames .zip File</label>
                                <input type="file" id="seq-zip-input" accept=".zip,application/zip"
                                       class="w-full text-xs text-zinc-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-900 hover:file:bg-zinc-200 cursor-pointer">
                            </div>

                            <button type="button"
                                    id="seq-zip-upload-btn"
                                    class="w-full py-2.5 px-4 rounded-xl bg-zinc-900 hover:bg-zinc-800 text-white font-semibold text-xs transition cursor-pointer">
                                Upload &amp; Unpack .zip Frames
                            </button>

                            <div class="p-3 rounded-xl bg-zinc-50 border border-zinc-200/80 text-[11px] text-zinc-600 space-y-1">
                                <span class="font-bold text-zinc-800 block">Repo Drop-In / CLI Helper:</span>
                                <code class="block font-mono text-[10px] text-zinc-700 break-all">public/images/scroll-frames/frame_0001.webp</code>
                                <code class="block font-mono text-[10px] text-zinc-700 break-all">node tools/convert-video-frames.mjs video.mp4</code>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Scroll Choreography & Story Beats Configuration (Saved with Master Form) -->
                <div class="bg-white rounded-2xl p-6 border border-zinc-200/80 shadow-xs space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-100">
                        <div>
                            <h3 class="text-sm font-bold text-zinc-950 uppercase tracking-wider">Stage Timing &amp; Synchronized Story Beats</h3>
                            <p class="text-xs text-zinc-500 mt-0.5">Configure sticky runway height, inertial smoothing, and the 3 headlines scrubbed over your animation.</p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-zinc-900 cursor-pointer">
                            <input type="hidden" name="scroll_seq_enabled" value="0">
                            <input type="checkbox" name="scroll_seq_enabled" value="1"
                                   class="rounded border-zinc-300 text-zinc-950 focus:ring-zinc-950"
                                   {{ $seqEnabled ? 'checked' : '' }}>
                            <span>Enable Scroll Sequence Stage on Homepage</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Scroll Track Height (vh)</label>
                            <input type="number" name="scroll_seq_height_vh" min="180" max="450" step="10"
                                   value="{{ (int) (($hp['scroll_seq_height_vh'] ?? 0) ?: 290) }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono">
                            <span class="text-[10px] text-zinc-400 mt-1 block">Recommended: 260–340 vh</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Inertial Smoothing (0.04 – 0.35)</label>
                            <input type="number" name="scroll_seq_smoothing" min="0.04" max="0.35" step="0.01"
                                   value="{{ (float) (($hp['scroll_seq_smoothing'] ?? 0) ?: 0.12) }}"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono">
                            <span class="text-[10px] text-zinc-400 mt-1 block">Lower = silkier cinema glide (0.12 default)</span>
                        </div>
                        <div>
                            <label class="block font-semibold text-zinc-800 mb-1">Optional Subfolder (<code class="font-mono">public/images/</code>)</label>
                            <input type="text" name="scroll_seq_folder"
                                   value="{{ $hp['scroll_seq_folder'] ?? '' }}"
                                   placeholder="scroll-frames"
                                   class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono">
                            <span class="text-[10px] text-zinc-400 mt-1 block">Leave empty to auto-detect uploaded or default folder</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-2 text-xs">
                        <!-- Beat 1 -->
                        <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/80 space-y-3">
                            <span class="font-bold text-zinc-900 block uppercase tracking-wider text-[11px]">Beat 1 (Start 0% – 33%)</span>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Kicker Badge</label>
                                <input type="text" name="scroll_seq_beat1_kicker"
                                       value="{{ trim((string) ($hp['scroll_seq_beat1_kicker'] ?? '')) ?: 'Chapter I • Quarry & Craft' }}"
                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900 font-medium">
                            </div>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Headline</label>
                                <input type="text" name="scroll_seq_beat1_title"
                                       value="{{ trim((string) ($hp['scroll_seq_beat1_title'] ?? '')) ?: 'Sculpted in Stone. Revealed in Motion.' }}"
                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900 font-bold">
                            </div>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Caption</label>
                                <textarea name="scroll_seq_beat1_desc" rows="2"
                                          class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900">{{ trim((string) ($hp['scroll_seq_beat1_desc'] ?? '')) ?: 'Every contour unfolds frame by frame as you scroll through K71 curated Indian craftsmanship and direct-factory finds.' }}</textarea>
                            </div>
                        </div>

                        <!-- Beat 2 -->
                        <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/80 space-y-3">
                            <span class="font-bold text-zinc-900 block uppercase tracking-wider text-[11px]">Beat 2 (Mid 33% – 66%)</span>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Kicker Badge</label>
                                <input type="text" name="scroll_seq_beat2_kicker"
                                       value="{{ trim((string) ($hp['scroll_seq_beat2_kicker'] ?? '')) ?: 'Chapter II • Material Provenance' }}"
                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900 font-medium">
                            </div>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Headline</label>
                                <input type="text" name="scroll_seq_beat2_title"
                                       value="{{ trim((string) ($hp['scroll_seq_beat2_title'] ?? '')) ?: 'Pure Veins & Authentic Workshop Polish.' }}"
                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900 font-bold">
                            </div>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Caption</label>
                                <textarea name="scroll_seq_beat2_desc" rows="2"
                                          class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900">{{ trim((string) ($hp['scroll_seq_beat2_desc'] ?? '')) ?: 'Inspect luminous surfaces, handloom weaves, and artisan detail directly from verified regional ateliers.' }}</textarea>
                            </div>
                        </div>

                        <!-- Beat 3 -->
                        <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200/80 space-y-3">
                            <span class="font-bold text-zinc-900 block uppercase tracking-wider text-[11px]">Beat 3 (End 66% – 100%)</span>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Kicker Badge</label>
                                <input type="text" name="scroll_seq_beat3_kicker"
                                       value="{{ trim((string) ($hp['scroll_seq_beat3_kicker'] ?? '')) ?: 'Chapter III • Direct Dispatch' }}"
                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900 font-medium">
                            </div>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Headline</label>
                                <input type="text" name="scroll_seq_beat3_title"
                                       value="{{ trim((string) ($hp['scroll_seq_beat3_title'] ?? '')) ?: 'Own the Genuine Piece Direct.' }}"
                                       class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900 font-bold">
                            </div>
                            <div>
                                <label class="block text-zinc-600 font-semibold mb-1">Caption</label>
                                <textarea name="scroll_seq_beat3_desc" rows="2"
                                          class="w-full px-3 py-2 rounded-lg border border-zinc-300 bg-white text-zinc-900">{{ trim((string) ($hp['scroll_seq_beat3_desc'] ?? '')) ?: 'Backed by PhonePe escrow settlements, GST-vetted merchants, and insured Pan-India delivery.' }}</textarea>
                            </div>
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

        @if(isset($categories) && $categories->isNotEmpty())
            @foreach($categories as $cat)
                @if(($cat->products_count ?? 0) === 0 && ($cat->children_count ?? 0) === 0)
                    <form id="delete-category-{{ $cat->id }}" action="{{ route('admin.homepage.categories.destroy', $cat) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            @endforeach
        @endif

    </div>

    @php
        $adminSeqConfigPayload = [
            'batchUrl' => route('admin.homepage.scroll_frames.batch'),
            'zipUrl' => route('admin.homepage.scroll_frames.zip'),
            'clearUrl' => route('admin.homepage.scroll_frames.clear'),
            'sequence' => $seq,
        ];
    @endphp
    <script type="application/json" id="admin-seq-config">{!! json_encode($adminSeqConfigPayload, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES) !!}</script>

    <script>
        (function () {
            const cfgEl = document.getElementById('admin-seq-config');
            if (!cfgEl) return;

            let cfg = {};
            try { cfg = JSON.parse(cfgEl.textContent || '{}'); } catch (e) { return; }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            let currentFrames = (cfg.sequence && Array.isArray(cfg.sequence.frames)) ? cfg.sequence.frames : [];

            const statusLabel = document.getElementById('seq-status-label');
            const statusCount = document.getElementById('seq-status-count');
            const tabBadge = document.getElementById('seq-tab-badge-count');
            const clearBtn = document.getElementById('seq-clear-btn');
            const previewImg = document.getElementById('seq-preview-img');
            const previewEmpty = document.getElementById('seq-preview-empty');
            const previewBadge = document.getElementById('seq-preview-badge');
            const previewSlider = document.getElementById('seq-preview-slider');
            const scrubReadout = document.getElementById('seq-scrub-readout');
            const progressBox = document.getElementById('seq-progress-box');
            const progressMsg = document.getElementById('seq-progress-msg');
            const progressPct = document.getElementById('seq-progress-pct');
            const progressFill = document.getElementById('seq-progress-fill');
            const feedbackBanner = document.getElementById('seq-feedback-banner');

            const setProgress = (visible, msg, pct) => {
                if (!progressBox) return;
                progressBox.classList.toggle('hidden', !visible);
                if (msg !== undefined && progressMsg) progressMsg.textContent = msg;
                if (pct !== undefined) {
                    const p = Math.min(100, Math.max(0, Math.round(pct)));
                    if (progressPct) progressPct.textContent = p + '%';
                    if (progressFill) progressFill.style.width = p + '%';
                }
            };

            const setFeedback = (ok, msg) => {
                if (!feedbackBanner) return;
                feedbackBanner.classList.remove('hidden', 'bg-emerald-950/60', 'border-emerald-700', 'text-emerald-200', 'bg-rose-950/60', 'border-rose-700', 'text-rose-200');
                feedbackBanner.classList.add('border', ok ? 'bg-emerald-950/60' : 'bg-rose-950/60', ok ? 'border-emerald-700' : 'border-rose-700', ok ? 'text-emerald-200' : 'text-rose-200');
                feedbackBanner.textContent = msg;
            };

            const applySequenceUI = (seq) => {
                if (!seq) return;
                currentFrames = Array.isArray(seq.frames) ? seq.frames : [];
                const cnt = Number(seq.count || currentFrames.length || 0);
                if (statusLabel) statusLabel.textContent = seq.label || 'Studio 3D Preview';
                if (statusCount) statusCount.textContent = `${cnt} Frames`;
                if (tabBadge) tabBadge.textContent = `${cnt}f`;
                if (clearBtn) clearBtn.classList.toggle('hidden', seq.source !== 'uploaded');

                if (cnt > 0 && currentFrames[0]) {
                    previewImg?.classList.remove('hidden');
                    previewEmpty?.classList.add('hidden');
                    previewBadge?.classList.remove('hidden');
                    if (previewSlider) {
                        previewSlider.disabled = false;
                        previewSlider.max = String(cnt - 1);
                        previewSlider.value = '0';
                    }
                    updatePreviewAtIndex(0);
                } else {
                    previewImg?.classList.add('hidden');
                    previewEmpty?.classList.remove('hidden');
                    previewBadge?.classList.add('hidden');
                    if (previewSlider) {
                        previewSlider.disabled = true;
                        previewSlider.max = '0';
                        previewSlider.value = '0';
                    }
                    if (scrubReadout) scrubReadout.textContent = '0%';
                }
            };

            const updatePreviewAtIndex = (idx) => {
                if (!currentFrames.length) return;
                const safeIdx = Math.min(currentFrames.length - 1, Math.max(0, Number(idx) || 0));
                if (previewImg) previewImg.src = currentFrames[safeIdx];
                if (previewBadge) previewBadge.textContent = `Frame ${safeIdx + 1} / ${currentFrames.length}`;
                const pct = currentFrames.length > 1 ? Math.round((safeIdx / (currentFrames.length - 1)) * 100) : 100;
                if (scrubReadout) scrubReadout.textContent = `${pct}%`;
            };

            previewSlider?.addEventListener('input', (e) => {
                updatePreviewAtIndex(e.target.value);
            });

            const uploadChunk = async (blobsOrFiles, startIndex, resetFlag) => {
                const fd = new FormData();
                fd.append('start_index', String(startIndex));
                fd.append('reset', resetFlag ? '1' : '0');
                blobsOrFiles.forEach((item, i) => {
                    fd.append('frames[]', item.blob || item, item.name || `frame_${startIndex + i}.webp`);
                });
                const res = await fetch(cfg.batchUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                    credentials: 'same-origin',
                    body: fd,
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.ok) {
                    throw new Error(data.message || `Batch upload failed (HTTP ${res.status})`);
                }
                return data;
            };

            // Option A: Video -> WebP extraction & chunked upload
            const videoBtn = document.getElementById('seq-video-upload-btn');
            videoBtn?.addEventListener('click', async () => {
                const fileInput = document.getElementById('seq-video-input');
                const file = fileInput?.files?.[0];
                if (!file) {
                    setFeedback(false, 'Please choose an MP4, WebM, or MOV video file first.');
                    return;
                }

                const targetCount = Number(document.getElementById('seq-video-count')?.value || 72);
                const maxWidth = Number(document.getElementById('seq-video-width')?.value || 1280);
                const quality = Number(document.getElementById('seq-video-quality')?.value || 0.82);

                videoBtn.disabled = true;
                setFeedback(true, 'Loading video metadata in browser...');
                setProgress(true, 'Preparing video decoder...', 2);

                const objUrl = URL.createObjectURL(file);
                const video = document.createElement('video');
                video.muted = true;
                video.playsInline = true;
                video.preload = 'auto';
                video.src = objUrl;

                try {
                    await new Promise((resolve, reject) => {
                        video.onloadedmetadata = () => resolve();
                        video.onerror = () => reject(new Error('Your browser could not decode this video format. Try MP4 (H.264) or WebM.'));
                    });

                    const duration = video.duration;
                    if (!Number.isFinite(duration) || duration <= 0.1) {
                        throw new Error('Video duration is invalid or too short.');
                    }

                    const vw = video.videoWidth || 1280;
                    const vh = video.videoHeight || 720;
                    const outW = Math.min(maxWidth, vw);
                    const outH = Math.max(2, Math.round(outW * (vh / vw)));

                    const offCanvas = document.createElement('canvas');
                    offCanvas.width = outW;
                    offCanvas.height = outH;
                    const offCtx = offCanvas.getContext('2d');

                    const toFrameBlob = (c, q) => new Promise((resolve) => {
                        c.toBlob((blob) => {
                            if (blob) return resolve({ blob, ext: 'webp' });
                            c.toBlob((jpg) => resolve({ blob: jpg, ext: 'jpg' }), 'image/jpeg', q);
                        }, 'image/webp', q);
                    });

                    const chunkSize = 8;
                    let batch = [];
                    let nextStartIdx = 1;
                    let isFirstBatch = true;
                    let latestSeq = null;

                    for (let i = 0; i < targetCount; i++) {
                        const seekTime = Math.min(duration - 0.02, Math.max(0, (i / Math.max(1, targetCount - 1)) * (duration - 0.03)));
                        await new Promise((resolve) => {
                            let done = false;
                            const finish = () => {
                                if (!done) {
                                    done = true;
                                    resolve();
                                }
                            };
                            video.addEventListener('seeked', finish, { once: true });
                            setTimeout(finish, 1800);
                            video.currentTime = seekTime;
                        });

                        offCtx.drawImage(video, 0, 0, outW, outH);
                        const { blob, ext } = await toFrameBlob(offCanvas, quality);
                        if (blob) {
                            batch.push({
                                blob,
                                name: `frame_${String(i + 1).padStart(5, '0')}.${ext}`,
                            });
                        }

                        const pct = ((i + 1) / targetCount) * 100;
                        setProgress(true, `Extracting & uploading frame ${i + 1} / ${targetCount}...`, pct);

                        if (batch.length >= chunkSize || i === targetCount - 1) {
                            const res = await uploadChunk(batch, nextStartIdx, isFirstBatch);
                            latestSeq = res.sequence;
                            nextStartIdx += batch.length;
                            batch = [];
                            isFirstBatch = false;
                        }
                    }

                    if (latestSeq) applySequenceUI(latestSeq);
                    setProgress(false);
                    setFeedback(true, `Successfully converted video into ${targetCount} WebP frames and published!`);
                } catch (err) {
                    setProgress(false);
                    setFeedback(false, err.message || 'Video extraction failed.');
                } finally {
                    URL.revokeObjectURL(objUrl);
                    videoBtn.disabled = false;
                }
            });

            // Option B: Direct Multi-Image Upload
            const imagesBtn = document.getElementById('seq-images-upload-btn');
            imagesBtn?.addEventListener('click', async () => {
                const input = document.getElementById('seq-images-input');
                const files = Array.from(input?.files || []);
                if (!files.length) {
                    setFeedback(false, 'Please select one or more image frames first.');
                    return;
                }

                files.sort((a, b) => a.name.localeCompare(b.name, undefined, { numeric: true, sensitivity: 'base' }));
                const capped = files.slice(0, 360);
                imagesBtn.disabled = true;
                setProgress(true, `Uploading ${capped.length} sorted image frames...`, 5);

                try {
                    const chunkSize = 8;
                    let latestSeq = null;
                    for (let i = 0; i < capped.length; i += chunkSize) {
                        const slice = capped.slice(i, i + chunkSize);
                        const res = await uploadChunk(slice, i + 1, i === 0);
                        latestSeq = res.sequence;
                        setProgress(true, `Uploaded ${Math.min(i + chunkSize, capped.length)} / ${capped.length} frames...`, ((i + slice.length) / capped.length) * 100);
                    }
                    if (latestSeq) applySequenceUI(latestSeq);
                    setProgress(false);
                    setFeedback(true, `Uploaded ${capped.length} frames in natural sequence order!`);
                } catch (err) {
                    setProgress(false);
                    setFeedback(false, err.message || 'Image batch upload failed.');
                } finally {
                    imagesBtn.disabled = false;
                }
            });

            // Option C: ZIP Upload
            const zipBtn = document.getElementById('seq-zip-upload-btn');
            zipBtn?.addEventListener('click', async () => {
                const input = document.getElementById('seq-zip-input');
                const file = input?.files?.[0];
                if (!file) {
                    setFeedback(false, 'Please select a .zip archive containing image frames.');
                    return;
                }

                zipBtn.disabled = true;
                setProgress(true, 'Uploading and unpacking .zip archive on server...', 55);

                try {
                    const fd = new FormData();
                    fd.append('archive', file);
                    const res = await fetch(cfg.zipUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        credentials: 'same-origin',
                        body: fd,
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok) {
                        throw new Error(data.message || `ZIP upload failed (HTTP ${res.status})`);
                    }
                    applySequenceUI(data.sequence);
                    setProgress(false);
                    setFeedback(true, `Extracted ${data.sequence?.count || 0} frames cleanly from ZIP archive!`);
                } catch (err) {
                    setProgress(false);
                    setFeedback(false, err.message || 'ZIP extraction failed.');
                } finally {
                    zipBtn.disabled = false;
                }
            });

            // Clear Uploaded Frames
            clearBtn?.addEventListener('click', async () => {
                clearBtn.disabled = true;
                setProgress(true, 'Clearing uploaded scroll frames...', 50);
                try {
                    const res = await fetch(cfg.clearUrl, {
                        method: 'DELETE',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        credentials: 'same-origin',
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.ok) {
                        throw new Error(data.message || 'Failed to clear frames.');
                    }
                    applySequenceUI(data.sequence);
                    setProgress(false);
                    setFeedback(true, 'Cleared uploaded frames. Homepage reverted cleanly to drop-in folder or 3D studio preview.');
                } catch (err) {
                    setProgress(false);
                    setFeedback(false, err.message || 'Failed to clear frames.');
                } finally {
                    clearBtn.disabled = false;
                }
            });
        })();
    </script>
</x-layouts.admin>
