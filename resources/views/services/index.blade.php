<x-layouts.services 
    title="K71 Services — Freelance & Professional Services Marketplace"
    metaDescription="Discover top-rated freelancers and vetted professionals on K71 Services. Web development, graphic design, video editing, marketing, and skilled craftsmanship with milestone protection."
>
    @php
        $ss = $servicesSettings ?? [];
    @endphp

    <!-- GRAIL EDITORIAL FREELANCE MARKETPLACE -->
    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- 1. OBSIDIAN HERO & SEARCH -->
        <section class="grail-hero-band relative overflow-hidden pt-8 sm:pt-12 pb-12 sm:pb-14 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-4xl mx-auto text-center space-y-4 relative z-10">
                <!-- Simple Trust Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 border border-white/15 text-xs font-semibold text-[#EFE9F2] backdrop-blur-md">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>{{ $ss['services_hero_badge'] ?? 'Verified Freelancers & Professional Services' }}</span>
                </div>

                <!-- Clear Direct Editorial Title -->
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-light tracking-tight text-white leading-tight" style="font-family: var(--font-grail-display);">
                    {{ $ss['services_hero_title'] ?? 'Find the Perfect Freelance Services' }}
                </h1>

                <p class="max-w-xl mx-auto text-sm sm:text-base text-[#EFE9F2]/75 font-normal">
                    {{ $ss['services_hero_subtitle'] ?? 'Hire proven specialists with upfront package pricing, milestone safety, and fast delivery.' }}
                </p>

                <!-- Sleek Obsidian Search Bar -->
                <div class="max-w-2xl mx-auto pt-2">
                    <form action="{{ route('services.index') }}" method="GET" 
                          class="p-1.5 sm:p-2 rounded-full bg-white/10 border border-white/15 hover:border-white/30 focus-within:border-[#ECD2F3] focus-within:bg-white/15 shadow-lg flex items-center transition-all backdrop-blur-md">
                        <div class="relative flex-1 flex items-center pl-4">
                            <svg class="w-5 h-5 text-[#ECD2F3] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="What service are you looking for today? (e.g. Logo Design, Website)"
                                   class="w-full px-3 py-2 text-xs sm:text-sm bg-transparent border-0 focus:outline-none text-white placeholder-white/45 font-medium">
                        </div>

                        @if($selectedCategory)
                            <input type="hidden" name="cat" value="{{ $selectedCategory }}">
                        @endif

                        <button type="submit" 
                                class="grail-cta-pill px-6 py-2.5 text-xs sm:text-sm font-bold transition shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer active:scale-95">
                            <span>Search</span>
                        </button>
                    </form>
                </div>

                <!-- Popular Quick-Tag Suggestions -->
                <div class="flex flex-wrap items-center justify-center gap-2 pt-1 text-xs">
                    <span class="text-[#C9A24A] font-semibold uppercase tracking-[0.12em] text-[10px]">Popular:</span>
                    <a href="{{ route('services.index', ['search' => 'Logo Design']) }}" class="px-3 py-1 rounded-full bg-white/10 hover:bg-[#ECD2F3]/20 text-[#EFE9F2]/85 hover:text-[#FFCDFF] font-medium transition border border-white/15">Logo Design</a>
                    <a href="{{ route('services.index', ['search' => 'Website']) }}" class="px-3 py-1 rounded-full bg-white/10 hover:bg-[#ECD2F3]/20 text-[#EFE9F2]/85 hover:text-[#FFCDFF] font-medium transition border border-white/15">Web Development</a>
                    <a href="{{ route('services.index', ['search' => 'Video']) }}" class="px-3 py-1 rounded-full bg-white/10 hover:bg-[#ECD2F3]/20 text-[#EFE9F2]/85 hover:text-[#FFCDFF] font-medium transition border border-white/15">Video Editing</a>
                    <a href="{{ route('services.index', ['search' => 'App']) }}" class="px-3 py-1 rounded-full bg-white/10 hover:bg-[#ECD2F3]/20 text-[#EFE9F2]/85 hover:text-[#FFCDFF] font-medium transition border border-white/15">Mobile Apps</a>
                    <a href="{{ route('services.index', ['search' => 'Marble']) }}" class="px-3 py-1 rounded-full bg-white/10 hover:bg-[#ECD2F3]/20 text-[#EFE9F2]/85 hover:text-[#FFCDFF] font-medium transition border border-white/15">Marble Polishing</a>
                </div>

                <!-- Fiverr Packages vs Upwork Custom Projects Switcher -->
                <div class="pt-3 flex flex-wrap items-center justify-center gap-2.5 text-xs">
                    <a href="{{ route('services.index') }}" class="px-4 py-2 rounded-full bg-[#ECD2F3] text-[#1A1018] font-bold shadow-xs">
                        Service Packages
                    </a>
                    <a href="{{ route('services.projects.index') }}" class="px-4 py-2 rounded-full bg-white/10 hover:bg-white/20 text-white font-semibold border border-white/15 transition">
                        Open Project Briefs (Upwork Style) →
                    </a>
                    <a href="{{ route('services.projects.create') }}" class="px-4 py-2 rounded-full bg-white/10 hover:bg-[#ECD2F3]/20 text-[#ECD2F3] font-semibold border border-[#ECD2F3]/30 transition">
                        + Post a Project Brief
                    </a>
                </div>
            </div>
        </section>

        <!-- CURVED EDITORIAL SHEET BODY -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-24 md:pb-16 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">

                <!-- 2. CATEGORY SLIDER -->
                <section x-data="{
                    scrollLeft() {
                        $refs.categorySlider.scrollBy({ left: -340, behavior: 'smooth' });
                    },
                    scrollRight() {
                        $refs.categorySlider.scrollBy({ left: 340, behavior: 'smooth' });
                    }
                }" class="space-y-4" data-grail-reveal>
                    
                    <!-- Section Header with Slider Navigation Controls -->
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight flex flex-wrap items-center gap-2.5" style="font-family: var(--font-grail-display);">
                                <span>Explore Services by Category</span>
                                <span class="text-xs font-sans font-semibold px-2.5 py-0.5 rounded-full bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3]">Slide to browse</span>
                            </h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-normal">Select a category to view verified specialists</p>
                        </div>

                        <!-- Previous & Next Arrow Buttons -->
                        <div class="flex items-center gap-2">
                            <button @click="scrollLeft()" 
                                    aria-label="Previous Category"
                                    class="w-9 h-9 rounded-full bg-white hover:bg-[#EFE9F2] border border-[#E6E2E8] text-[#1A1018] flex items-center justify-center transition shadow-2xs active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button @click="scrollRight()" 
                                    aria-label="Next Category"
                                    class="w-9 h-9 rounded-full bg-white hover:bg-[#EFE9F2] border border-[#E6E2E8] text-[#1A1018] flex items-center justify-center transition shadow-2xs active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- 4-Column Category Icon Grid -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                        
                        <!-- 'All Categories' Reset Card -->
                        <a href="{{ route('services.index') }}" 
                           class="rounded-2xl p-4 flex flex-col justify-between border transition-all duration-300 relative overflow-hidden group {{ empty($selectedCategory) ? 'bg-[#0D0D0D] surface-dark text-white border-[#1A1018] shadow-md' : 'grail-card bg-white text-[#1A1018] border-[#E6E2E8] hover:border-[#623562]/40 hover:shadow-2xs' }}">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ empty($selectedCategory) ? 'bg-[#ECD2F3] text-[#1A1018]' : 'bg-[#F8EFFA] text-[#623562]' }}">
                                ⚡
                            </div>
                            <div class="mt-4">
                                <h3 class="font-bold text-sm leading-snug {{ empty($selectedCategory) ? 'text-white' : 'text-[#1A1018]' }}">All Services</h3>
                                <p class="text-[11px] mt-0.5 {{ empty($selectedCategory) ? 'text-[#EFE9F2]/70' : 'text-[#1A1018]/55' }}">
                                    Complete Directory
                                </p>
                            </div>
                        </a>

                        <!-- Category Cards with Photography Images & Guaranteed Scrim Readability -->
                        @foreach($servicesCategories as $cat)
                            @php
                                $isSelected = ($selectedCategory === $cat->slug);
                                $categoryImg = $cat->image_url;
                            @endphp
                            <a href="{{ route('services.category', $cat->slug) }}" 
                               class="rounded-2xl relative overflow-hidden group aspect-[4/3] sm:aspect-[16/11] border transition-all duration-300 shadow-2xs {{ $isSelected ? 'border-[#623562] ring-2 ring-[#ECD2F3] shadow-md' : 'border-[#E6E2E8] hover:border-[#623562]/40 hover:shadow-md' }}">
                                
                                <!-- Category Photography Cover -->
                                <img src="{{ $categoryImg }}" 
                                     alt="{{ $cat->name }}" 
                                     loading="lazy"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                                
                                <!-- Dark Gradient Overlay for Readability -->
                                <div class="k71-photo-scrim"></div>

                                <!-- Active Indicator Ribbon -->
                                @if($isSelected)
                                    <div class="absolute top-2.5 right-2.5 px-2.5 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold shadow-xs flex items-center gap-1 z-10">
                                        <span>✓ Active</span>
                                    </div>
                                @endif

                                <!-- Bottom Content -->
                                <div class="absolute inset-x-0 bottom-0 p-3 sm:p-4 text-white z-10 photo-text">
                                    <h3 class="font-bold text-xs sm:text-sm text-white group-hover:text-[#FFCDFF] transition leading-snug line-clamp-2 drop-shadow-md">
                                        {{ $cat->name }}
                                    </h3>
                                    <div class="flex items-center justify-between mt-1 text-[11px] text-[#EFE9F2]/80 font-medium">
                                        <span>{{ $cat->products_count }} Services</span>
                                        <span class="group-hover:translate-x-1 transition duration-200">→</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>

                <!-- 3. SERVICES CATALOG WITH CLEAR FILTER STATUS & SIMPLE CONTROLS -->
                <section id="services-feed" class="space-y-4 pt-2" data-grail-reveal>
                    
                    <div class="flex items-center justify-between mb-1">
                        <div>
                            <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Trending Services &amp; Packages</h2>
                            <p class="text-xs sm:text-sm text-[#1A1018]/60 font-normal">Browse verified services with transparent 3-tier packages</p>
                        </div>
                    </div>

                    <!-- Filter Bar -->
                    <div class="grail-card flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-white border border-[#E6E2E8] shadow-2xs">
                        <div class="flex items-center flex-wrap gap-2">
                            @if($selectedCategory)
                                @php
                                    $activeCatObj = $servicesCategories->firstWhere('slug', $selectedCategory);
                                @endphp
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#F8EFFA] text-[#623562] text-xs font-bold border border-[#ECD2F3]">
                                    <span>Category: {{ $activeCatObj?->name ?? $selectedCategory }}</span>
                                    <a href="{{ route('services.index', array_filter(['search' => $search, 'sort' => $sort])) }}" 
                                       class="hover:text-[#1A1018] font-black" title="Remove filter">✕</a>
                                </div>
                            @endif

                            @if($search)
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#F8EFFA] text-[#623562] text-xs font-bold border border-[#ECD2F3]">
                                    <span>Search: "{{ $search }}"</span>
                                    <a href="{{ route('services.index', array_filter(['cat' => $selectedCategory, 'sort' => $sort])) }}" 
                                       class="hover:text-[#1A1018] font-black" title="Clear search">✕</a>
                                </div>
                            @endif

                            <span class="text-xs sm:text-sm font-medium text-[#1A1018]/65">
                                Showing <strong class="text-[#1A1018]">{{ $services->total() }}</strong> verified services
                            </span>
                        </div>

                        <!-- Clean Work Mode + Sort Filters -->
                        <form action="{{ route('services.index') }}" method="GET" class="flex flex-wrap items-center gap-2 self-end sm:self-auto">
                            @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                            @if($selectedCategory)<input type="hidden" name="cat" value="{{ $selectedCategory }}">@endif
                            @if($city)<input type="hidden" name="city" value="{{ $city }}">@endif

                            <select name="work_mode" onchange="this.form.submit()" class="grail-input-light px-3 py-1.5 text-xs font-semibold cursor-pointer">
                                <option value="">All Work Modes</option>
                                <option value="remote" {{ ($workMode ?? '') === 'remote' ? 'selected' : '' }}>Remote</option>
                                <option value="hybrid" {{ ($workMode ?? '') === 'hybrid' ? 'selected' : '' }}>Hybrid</option>
                                <option value="onsite" {{ ($workMode ?? '') === 'onsite' ? 'selected' : '' }}>On-Site</option>
                            </select>
                            
                            <label for="sort" class="text-xs font-bold text-[#1A1018]/55">Sort by:</label>
                            <select name="sort" id="sort" onchange="this.form.submit()" 
                                    class="grail-input-light px-3 py-1.5 text-xs font-semibold cursor-pointer">
                                <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                                <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                                <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                                <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Top Rated</option>
                            </select>
                        </form>
                    </div>

                    <!-- SERVICES GRID WITH ALPINE COMPARISON DRAWER -->
                    <div x-data="{
                        compareList: [],
                        compareOpen: false,
                        toggleCompare(item) {
                            const idx = this.compareList.findIndex(x => x.id === item.id);
                            if (idx > -1) {
                                this.compareList.splice(idx, 1);
                            } else if (this.compareList.length < 3) {
                                this.compareList.push(item);
                            }
                        },
                        isSelected(id) {
                            return this.compareList.some(x => x.id === id);
                        }
                    }">
                    @if($services->isEmpty())
                        <div class="grail-card p-10 text-center rounded-3xl bg-white border border-[#E6E2E8] text-[#1A1018]/70 space-y-3">
                            <div class="w-14 h-14 rounded-full bg-[#F8EFFA] text-[#623562] flex items-center justify-center text-2xl mx-auto">
                                🔍
                            </div>
                            <h3 class="text-base font-bold text-[#1A1018]">No Services Found</h3>
                            <p class="text-xs text-[#1A1018]/55 max-w-sm mx-auto">We couldn't find any services matching this selection. Try clearing your filters.</p>
                            <a href="{{ route('services.index') }}" 
                               class="grail-btn-obsidian inline-block px-5 py-2 text-xs font-bold transition shadow-xs">
                                View All Services
                            </a>
                        </div>
                    @else
                        <!-- HORIZONTAL LIST CARDS -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($services as $svc)
                                @php
                                    $imgUrl = $svc->primary_image_url;
                                    $basicPkg = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                                    $price = $basicPkg ? $basicPkg->price : ($svc->base_price / 100);
                                    $realRating = ($svc->vendor?->average_rating && $svc->vendor->average_rating > 0) ? number_format((float) $svc->vendor->average_rating, 1) : null;
                                    $comparePayload = [
                                        'id' => $svc->id,
                                        'title' => $svc->title,
                                        'provider' => $svc->vendor?->shop_name ?? 'Provider',
                                        'price' => '₹' . number_format($price, 0),
                                        'delivery' => ($basicPkg?->delivery_days ?? 3) . ' days',
                                        'revisions' => ($basicPkg?->revisions ?? 2) . ' revisions',
                                        'tiers' => $svc->gigPackages->count() ?: 1,
                                        'url' => route('services.show', $svc->slug),
                                    ];
                                @endphp
                                <div class="group grail-card rounded-2xl bg-white border border-[#E6E2E8] hover:border-[#623562]/45 hover:shadow-md transition-all duration-300 overflow-hidden flex flex-row items-stretch p-3 gap-3.5">
                                    
                                    <!-- 96px Image Left -->
                                    <a href="{{ route('services.show', $svc->slug) }}" class="w-24 h-24 shrink-0 rounded-xl overflow-hidden bg-[#FAFAF8] relative">
                                        <img src="{{ $imgUrl }}" alt="{{ $svc->title }}" 
                                             loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    </a>

                                    <!-- Card Body Right -->
                                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                                        <div>
                                            <!-- Category & Verified Pro Badge -->
                                            <div class="flex items-center justify-between gap-1.5 text-[10px] mb-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="px-2 py-0.5 rounded-full bg-[#EFE9F2] text-[#1A1018] font-semibold">{{ $svc->category?->name ?? 'Service' }}</span>
                                                    @if($svc->vendor?->is_verified_pro || $svc->vendor?->is_verified)
                                                        <span class="px-2 py-0.5 rounded-full bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3] font-bold flex items-center gap-0.5">
                                                            <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                                            <span>Verified</span>
                                                        </span>
                                                    @endif
                                                </div>

                                                <button type="button"
                                                        @click="toggleCompare({{ Js::from($comparePayload) }})"
                                                        :class="isSelected({{ $svc->id }}) ? 'bg-[#1A1018] text-[#ECD2F3] border-[#1A1018]' : 'bg-white text-[#1A1018]/60 border-[#E6E2E8] hover:border-[#623562]'"
                                                        class="px-2 py-0.5 rounded-full border font-bold transition cursor-pointer shrink-0">
                                                    <span x-text="isSelected({{ $svc->id }}) ? '✓ Comparing' : '+ Compare'"></span>
                                                </button>
                                            </div>

                                            <!-- Title -->
                                            <h3 class="k71-card-title text-[#1A1018] group-hover:text-[#623562] transition">
                                                <a href="{{ route('services.show', $svc->slug) }}">
                                                    {{ $svc->title }}
                                                </a>
                                            </h3>

                                            <!-- Provider, Rating & Delivery -->
                                            <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] text-[#1A1018]/65 font-medium">
                                                @if($svc->vendor)
                                                    <a href="{{ route('services.provider', $svc->vendor->slug) }}" class="font-semibold text-[#1A1018] hover:text-[#623562]">{{ $svc->vendor->shop_name }}</a>
                                                    <span>•</span>
                                                @endif
                                                @if($realRating)
                                                    <span class="text-[#C9A24A] font-bold">★ {{ $realRating }}</span>
                                                    <span>•</span>
                                                @endif
                                                <span>⏱️ {{ $basicPkg?->delivery_days ?? 3 }} Days</span>
                                            </div>
                                        </div>

                                        <!-- Price & CTA -->
                                        <div class="mt-2 pt-2 border-t border-[#E6E2E8] flex items-center justify-between gap-2">
                                            <div>
                                                <span class="text-[10px] uppercase font-bold text-[#1A1018]/50 block leading-none">Starting at</span>
                                                <span class="k71-price text-[#1A1018]">
                                                    ₹{{ number_format($price, 2) }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                @if($svc->vendor?->isWebsitePublished())
                                                    <a href="{{ $svc->vendor->storefront_url }}" target="_blank" class="px-2.5 py-1.5 rounded-full border border-[#E6E2E8] hover:border-[#623562] text-[10px] font-bold text-[#1A1018]/75">
                                                        Website ↗
                                                    </a>
                                                @endif
                                                <a href="{{ route('services.show', $svc->slug) }}" 
                                                   class="grail-btn-obsidian px-4 py-1.5 text-xs font-bold transition shadow-2xs active:scale-95">
                                                    View Package
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Clean Pagination -->
                        <div class="mt-8 flex justify-center">
                            {{ $services->links() }}
                        </div>

                        <!-- Floating Service Comparison Bar + Drawer -->
                        <div x-show="compareList.length > 0" x-cloak class="fixed bottom-16 md:bottom-6 inset-x-0 z-40 px-4 pointer-events-none">
                            <div class="max-w-3xl mx-auto rounded-2xl bg-[#0D0D0D] text-white border border-[#ECD2F3]/35 p-3.5 shadow-2xl pointer-events-auto space-y-3">
                                <div class="flex items-center justify-between gap-3 text-xs">
                                    <div>
                                        <strong class="text-[#ECD2F3]" x-text="compareList.length"></strong> service(s) selected for comparison (max 3)
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="compareOpen = !compareOpen" class="px-3.5 py-1.5 rounded-full bg-[#ECD2F3] text-[#1A1018] font-bold cursor-pointer">
                                            <span x-text="compareOpen ? 'Hide Comparison' : 'Compare Side-by-Side'"></span>
                                        </button>
                                        <button type="button" @click="compareList = []; compareOpen = false" class="px-2.5 py-1.5 rounded-full bg-white/10 text-white/75 hover:text-white cursor-pointer">
                                            Clear
                                        </button>
                                    </div>
                                </div>

                                <div x-show="compareOpen" class="overflow-x-auto border-t border-white/10 pt-3">
                                    <table class="w-full text-left text-xs">
                                        <thead class="text-[#C9A24A] uppercase text-[10px]">
                                            <tr>
                                                <th class="py-1.5 pr-3">Service</th>
                                                <th class="py-1.5 px-3">Provider</th>
                                                <th class="py-1.5 px-3">Starting</th>
                                                <th class="py-1.5 px-3">Delivery</th>
                                                <th class="py-1.5 px-3">Revisions</th>
                                                <th class="py-1.5 pl-3 text-right">Open</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-white/10">
                                            <template x-for="item in compareList" :key="item.id">
                                                <tr>
                                                    <td class="py-2 pr-3 font-semibold text-white" x-text="item.title"></td>
                                                    <td class="py-2 px-3 text-white/75" x-text="item.provider"></td>
                                                    <td class="py-2 px-3 font-bold text-[#ECD2F3]" x-text="item.price"></td>
                                                    <td class="py-2 px-3 text-white/75" x-text="item.delivery"></td>
                                                    <td class="py-2 px-3 text-white/75" x-text="item.revisions"></td>
                                                    <td class="py-2 pl-3 text-right"><a :href="item.url" class="text-[#ECD2F3] underline font-bold">Select →</a></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                    </div>
                </section>

                <!-- 4. HOW IT WORKS: 3 EASY STEPS FOR CUSTOMERS -->
                <section class="grail-card p-6 sm:p-8 rounded-3xl bg-white border border-[#E6E2E8] shadow-2xs" data-grail-reveal>
                    <div class="text-center max-w-xl mx-auto mb-6">
                        <span class="grail-eyebrow !text-[#C9A24A] block mb-1">Escrow Protected Workflow</span>
                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">How Hiring Works on K71</h2>
                        <p class="text-xs sm:text-sm text-[#1A1018]/60 mt-1">Simple, transparent, and completely risk-free</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-5 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] text-center sm:text-left">
                            <div class="w-10 h-10 rounded-xl bg-[#1A1018] text-[#ECD2F3] font-bold flex items-center justify-center text-sm mb-3 mx-auto sm:mx-0 shadow-xs">
                                1
                            </div>
                            <h3 class="font-bold text-sm text-[#1A1018]">Choose Service &amp; Package</h3>
                            <p class="text-xs text-[#1A1018]/65 mt-1 leading-relaxed">
                                Pick a package with transparent pricing, guaranteed delivery timeline, and revision scope.
                            </p>
                        </div>

                        <div class="p-5 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] text-center sm:text-left">
                            <div class="w-10 h-10 rounded-xl bg-[#1A1018] text-[#ECD2F3] font-bold flex items-center justify-center text-sm mb-3 mx-auto sm:mx-0 shadow-xs">
                                2
                            </div>
                            <h3 class="font-bold text-sm text-[#1A1018]">Collaborate in Workspace</h3>
                            <p class="text-xs text-[#1A1018]/65 mt-1 leading-relaxed">
                                Submit your project details and communicate with your specialist in a dedicated real-time chat workspace.
                            </p>
                        </div>

                        <div class="p-5 rounded-2xl bg-[#FAFAF8] border border-[#E6E2E8] text-center sm:text-left">
                            <div class="w-10 h-10 rounded-xl bg-[#1A1018] text-[#ECD2F3] font-bold flex items-center justify-center text-sm mb-3 mx-auto sm:mx-0 shadow-xs">
                                3
                            </div>
                            <h3 class="font-bold text-sm text-[#1A1018]">Approve &amp; Release Funds</h3>
                            <p class="text-xs text-[#1A1018]/65 mt-1 leading-relaxed">
                                Download your finished deliverables. Funds are released to the provider only after your final satisfaction.
                            </p>
                        </div>
                    </div>
                </section>

                <!-- 5. TOP SPECIALISTS -->
                @if($topProviders->isNotEmpty())
                    <section class="space-y-4" data-grail-reveal>
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Top-Rated Freelancers</h2>
                                <p class="text-xs sm:text-sm text-[#1A1018]/60 font-normal">Proven independent professionals ready for your next project</p>
                            </div>
                            <a href="{{ route('services.become_seller') }}" class="text-xs font-bold text-[#623562] hover:text-[#1A1018] underline">
                                Become a Seller →
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @foreach($topProviders as $pro)
                                @php
                                    $pAvatar = $pro->logo ? format_image_url($pro->logo) : null;
                                    $pInitial = strtoupper(substr($pro->shop_name ?: 'K', 0, 1));
                                    $pRating = ($pro->average_rating && $pro->average_rating > 0) ? number_format((float) $pro->average_rating, 1) : null;
                                @endphp
                                <div class="grail-card p-4 rounded-2xl bg-white border border-[#E6E2E8] hover:border-[#623562]/45 hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center gap-3 mb-2.5">
                                            @if($pAvatar)
                                                <img src="{{ $pAvatar }}" alt="{{ $pro->shop_name }}" class="w-11 h-11 rounded-full object-cover border border-[#E6E2E8] shrink-0">
                                            @else
                                                <div class="w-11 h-11 rounded-full bg-[#1A1018] text-[#ECD2F3] font-bold text-sm flex items-center justify-center shrink-0">{{ $pInitial }}</div>
                                            @endif
                                            <div class="min-w-0">
                                                <h4 class="font-bold text-xs sm:text-sm text-[#1A1018] leading-tight truncate">{{ $pro->shop_name }}</h4>
                                                <div class="text-[11px] text-[#1A1018]/65 flex items-center gap-1 mt-0.5">
                                                    @if($pRating)
                                                        <span class="text-[#C9A24A] font-bold">★ {{ $pRating }}</span>
                                                        <span>·</span>
                                                    @endif
                                                    <span>{{ $pro->completed_projects ?? 0 }} completed</span>
                                                </div>
                                            </div>
                                        </div>

                                        <p class="text-xs text-[#1A1018]/65 line-clamp-2 leading-relaxed">
                                            {{ $pro->headline ?? $pro->tagline ?? $pro->description }}
                                        </p>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-[#E6E2E8] flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-[#1A1018]">
                                            {{ $pro->hourly_rate ? '₹' . number_format($pro->hourly_rate) . '/hr' : 'Fixed' }}
                                        </span>
                                        <div class="flex items-center gap-1.5">
                                            @if($pro->isWebsitePublished())
                                                <a href="{{ $pro->storefront_url }}" target="_blank" class="px-2.5 py-1.5 rounded-full border border-[#ECD2F3] bg-[#F8EFFA] text-[#623562] text-[10px] font-bold hover:bg-[#ECD2F3]/50 transition">
                                                    Website ↗
                                                </a>
                                            @endif
                                            <a href="{{ route('services.provider', $pro->slug) }}" 
                                               class="px-3 py-1.5 rounded-full bg-[#EFE9F2] hover:bg-[#1A1018] hover:text-white text-[#1A1018] text-[11px] font-bold transition">
                                                Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <!-- 6. SELLER ONBOARDING BANNER -->
                <section class="grail-dark-card p-6 sm:p-8 rounded-3xl text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-lg border border-white/10" data-grail-reveal>
                    <div class="text-center sm:text-left space-y-1">
                        <span class="grail-eyebrow block">K71 Freelance Network</span>
                        <h3 class="text-xl sm:text-2xl font-light text-white" style="font-family: var(--font-grail-display);">Offer Your Skills on K71 Services</h3>
                        <p class="text-xs sm:text-sm text-[#EFE9F2]/75">Create service packages, get client inquiries, and manage project orders with zero setup fee.</p>
                    </div>
                    <a href="{{ route('services.become_seller') }}" 
                       class="grail-cta-pill px-5 py-2.5 text-xs sm:text-sm font-bold tracking-wide transition shrink-0 shadow-xs max-h-11 flex items-center justify-center">
                        Start Selling Now
                    </a>
                </section>

            </div>
        </div>

        <!-- Sticky Mobile "Post a Project" Floating Button -->
        <div class="fixed bottom-0 inset-x-0 p-3 bg-[#0D0D0D]/95 backdrop-blur-md border-t border-white/15 z-30 flex justify-center shadow-lg md:hidden">
            <a href="{{ route('services.projects.create') }}"
               class="grail-cta-pill w-full max-w-md h-11 font-bold text-sm shadow-md flex items-center justify-center gap-2 transition active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Post a Project Brief</span>
            </a>
        </div>

    </div>
</x-layouts.services>
