<x-layouts.services 
    title="K71 Services — Freelance & Professional Services Marketplace"
    metaDescription="Discover top-rated freelancers and vetted professionals on K71 Services. Web development, graphic design, video editing, marketing, and skilled craftsmanship with milestone protection."
>
    @php
        $ss = $servicesSettings ?? [];
    @endphp

    <!-- MINIMAL ARCHITECTURAL FREELANCE MARKETPLACE -->
    <div class="min-h-screen bg-[#fafafa] text-zinc-900 pb-20">

        <!-- 1. HERO & SEARCH: SIMPLE, MINIMAL & CLEAN -->
        <section class="relative overflow-hidden pt-8 pb-10 px-4 sm:px-6 lg:px-8 border-b border-slate-200/60 bg-white">
            <div class="max-w-4xl mx-auto text-center space-y-4">
                <!-- Simple Trust Badge -->
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-slate-50 border border-slate-200/90 shadow-2xs text-xs font-bold text-slate-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>{{ $ss['services_hero_badge'] ?? 'services.k71.in • K71 Verified Freelancers & Services' }}</span>
                </div>

                <!-- Clear Direct Title -->
                <h1 class="text-3xl sm:text-5xl font-black tracking-tight text-slate-950 leading-tight">
                    {{ $ss['services_hero_title'] ?? 'Find the Perfect Freelance Services' }}
                </h1>

                <p class="max-w-xl mx-auto text-sm sm:text-base text-slate-600 font-medium">
                    {{ $ss['services_hero_subtitle'] ?? 'Hire proven specialists with upfront package pricing, milestone safety, and fast delivery.' }}
                </p>

                <!-- Clean, Friendly Search Bar -->
                <div class="max-w-2xl mx-auto pt-2">
                    <form action="{{ route('services.index') }}" method="GET" 
                          class="p-1.5 sm:p-2 rounded-full bg-slate-50 border border-slate-200 hover:border-slate-400 focus-within:border-slate-900 focus-within:bg-white shadow-xs flex items-center transition-all">
                        <div class="relative flex-1 flex items-center pl-4">
                            <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                            </svg>
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="What service are you looking for today? (e.g. Logo Design, Website)"
                                   class="w-full px-3 py-2 text-xs sm:text-sm bg-transparent border-0 focus:outline-none text-slate-900 placeholder-slate-400 font-medium">
                        </div>

                        @if($selectedCategory)
                            <input type="hidden" name="cat" value="{{ $selectedCategory }}">
                        @endif

                        <button type="submit" 
                                class="px-6 py-2.5 rounded-full bg-slate-900 hover:bg-black active:scale-95 text-white text-xs sm:text-sm font-bold transition shadow-xs flex items-center gap-1.5 shrink-0 cursor-pointer">
                            <span>Search</span>
                        </button>
                    </form>
                </div>

                <!-- Popular Quick-Tag Suggestions -->
                <div class="flex flex-wrap items-center justify-center gap-2 pt-1 text-xs">
                    <span class="text-slate-400 font-medium">Popular:</span>
                    <a href="{{ route('services.index', ['search' => 'Logo Design']) }}" class="px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold transition border border-slate-200/80">Logo Design</a>
                    <a href="{{ route('services.index', ['search' => 'Website']) }}" class="px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold transition border border-slate-200/80">Web Development</a>
                    <a href="{{ route('services.index', ['search' => 'Video']) }}" class="px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold transition border border-slate-200/80">Video Editing</a>
                    <a href="{{ route('services.index', ['search' => 'App']) }}" class="px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold transition border border-slate-200/80">Mobile Apps</a>
                    <a href="{{ route('services.index', ['search' => 'Marble']) }}" class="px-3 py-1 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold transition border border-slate-200/80">Marble Polishing</a>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 pt-8">

            <!-- 2. CATEGORY SLIDER -->
            <section x-data="{
                scrollLeft() {
                    $refs.categorySlider.scrollBy({ left: -340, behavior: 'smooth' });
                },
                scrollRight() {
                    $refs.categorySlider.scrollBy({ left: 340, behavior: 'smooth' });
                }
            }" class="space-y-4">
                
                <!-- Section Header with Slider Navigation Controls -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg sm:text-2xl font-black text-slate-950 tracking-tight flex items-center gap-2">
                            <span>Explore Services by Category</span>
                            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 border border-slate-200">Slide to browse</span>
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Select a category to view verified specialists</p>
                    </div>

                    <!-- Previous & Next Arrow Buttons -->
                    <div class="flex items-center gap-2">
                        <button @click="scrollLeft()" 
                                aria-label="Previous Category"
                                class="w-9 h-9 rounded-full bg-white hover:bg-slate-100 border border-slate-200 text-slate-800 flex items-center justify-center transition shadow-2xs active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button @click="scrollRight()" 
                                aria-label="Next Category"
                                class="w-9 h-9 rounded-full bg-white hover:bg-slate-100 border border-slate-200 text-slate-800 flex items-center justify-center transition shadow-2xs active:scale-95 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- Horizontal Category Carousel -->
                <div x-ref="categorySlider" 
                     class="flex gap-3 sm:gap-4 overflow-x-auto scroll-smooth pb-3 scrollbar-none snap-x snap-mandatory focus:outline-none -mx-4 px-4 sm:mx-0 sm:px-0">
                    
                    <!-- 'All Categories' Reset Card -->
                    <a href="{{ route('services.index') }}" 
                       class="w-36 sm:w-48 shrink-0 snap-start rounded-2xl p-4 flex flex-col justify-between border transition-all duration-300 relative overflow-hidden group {{ empty($selectedCategory) ? 'bg-slate-950 text-white border-slate-950 shadow-md' : 'bg-white text-slate-900 border-slate-200 hover:border-slate-400 hover:shadow-2xs' }}">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg {{ empty($selectedCategory) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-800' }}">
                            ⚡
                        </div>
                        <div class="mt-8">
                            <h3 class="font-bold text-sm leading-snug">All Services</h3>
                            <p class="text-[11px] mt-0.5 {{ empty($selectedCategory) ? 'text-slate-300' : 'text-slate-500' }}">
                                Complete Directory
                            </p>
                        </div>
                    </a>

                    <!-- Category Cards with Photography Images -->
                    @foreach($servicesCategories as $cat)
                        @php
                            $isSelected = ($selectedCategory === $cat->slug);
                            $categoryImg = $cat->image_url;
                        @endphp
                        <a href="{{ route('services.category', $cat->slug) }}" 
                           class="w-44 sm:w-56 shrink-0 snap-start rounded-2xl relative overflow-hidden group aspect-[4/3] sm:aspect-[16/11] border transition-all duration-300 shadow-2xs {{ $isSelected ? 'border-slate-950 ring-2 ring-slate-950 shadow-md' : 'border-slate-200/90 hover:border-slate-400 hover:shadow-md' }}">
                            
                            <!-- Category Photography Cover -->
                            <img src="{{ $categoryImg }}" 
                                 alt="{{ $cat->name }}" 
                                 loading="lazy"
                                 class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            
                            <!-- Dark Gradient Overlay for Readability -->
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/10 group-hover:from-black/90 transition-colors duration-300"></div>

                            <!-- Active Indicator Ribbon -->
                            @if($isSelected)
                                <div class="absolute top-2.5 right-2.5 px-2 py-0.5 rounded-full bg-slate-950 text-white text-[10px] font-bold shadow-xs flex items-center gap-1">
                                    <span>✓ Active</span>
                                </div>
                            @endif

                            <!-- Bottom Content -->
                            <div class="absolute inset-x-0 bottom-0 p-3.5 sm:p-4 text-white">
                                <h3 class="font-bold text-xs sm:text-sm text-white group-hover:text-slate-200 transition leading-snug line-clamp-1 drop-shadow-xs">
                                    {{ $cat->name }}
                                </h3>
                                <div class="flex items-center justify-between mt-1 text-[11px] text-slate-300 font-medium">
                                    <span>{{ $cat->products_count }} Services</span>
                                    <span class="group-hover:translate-x-1 transition duration-200">→</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- 3. SERVICES CATALOG WITH CLEAR FILTER STATUS & SIMPLE CONTROLS -->
            <section id="services-feed" class="space-y-4 pt-2">
                
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">Trending Services &amp; Packages</h2>
                        <p class="text-xs sm:text-sm text-slate-500 font-medium">Browse verified services with transparent 3-tier packages</p>
                    </div>
                </div>

                <!-- Filter Bar: Clear & Simple for Customers -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                    <div class="flex items-center flex-wrap gap-2">
                        @if($selectedCategory)
                            @php
                                $activeCatObj = $servicesCategories->firstWhere('slug', $selectedCategory);
                            @endphp
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                <span>Category: {{ $activeCatObj?->name ?? $selectedCategory }}</span>
                                <a href="{{ route('services.index', array_filter(['search' => $search, 'sort' => $sort])) }}" 
                                   class="hover:text-black font-black" title="Remove filter">✕</a>
                            </div>
                        @endif

                        @if($search)
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-800 text-xs font-bold border border-slate-200">
                                <span>Search: "{{ $search }}"</span>
                                <a href="{{ route('services.index', array_filter(['cat' => $selectedCategory, 'sort' => $sort])) }}" 
                                   class="hover:text-black font-black" title="Clear search">✕</a>
                            </div>
                        @endif

                        <span class="text-xs sm:text-sm font-semibold text-slate-600">
                            Showing <strong class="text-slate-950">{{ $services->total() }}</strong> verified services
                        </span>
                    </div>

                    <!-- Clean Sort Dropdown -->
                    <form action="{{ route('services.index') }}" method="GET" class="flex items-center gap-2 self-end sm:self-auto">
                        @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                        @if($selectedCategory)<input type="hidden" name="cat" value="{{ $selectedCategory }}">@endif
                        
                        <label for="sort" class="text-xs font-bold text-slate-500">Sort by:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()" 
                                class="px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-50 border border-slate-200 text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 cursor-pointer">
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Top Rated</option>
                        </select>
                    </form>
                </div>

                <!-- SERVICES GRID -->
                @if($services->isEmpty())
                    <div class="p-10 text-center rounded-3xl bg-white border border-slate-200 text-slate-600 space-y-3">
                        <div class="w-14 h-14 rounded-full bg-slate-100 text-slate-600 flex items-center justify-center text-2xl mx-auto">
                            🔍
                        </div>
                        <h3 class="text-base font-bold text-slate-900">No Services Found</h3>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto">We couldn't find any services matching this selection. Try clearing your filters.</p>
                        <a href="{{ route('services.index') }}" 
                           class="inline-block px-5 py-2 rounded-full bg-slate-900 text-white text-xs font-bold hover:bg-black transition shadow-xs">
                            View All Services
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                        @foreach($services as $svc)
                            @php
                                $imgUrl = $svc->primary_image_url;
                                $basicPkg = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                                $sellerAvatar = $svc->vendor?->logo ? format_image_url($svc->vendor->logo) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
                                $price = $basicPkg ? $basicPkg->price : ($svc->base_price / 100);
                            @endphp
                            <div class="group rounded-2xl bg-white border border-slate-200/90 hover:border-slate-400 hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                
                                <!-- Cover Photo -->
                                <div class="relative aspect-[16/10] overflow-hidden bg-slate-100">
                                    <a href="{{ route('services.show', $svc->slug) }}">
                                        <img src="{{ $imgUrl }}" alt="{{ $svc->title }}" 
                                             loading="lazy"
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    </a>
                                    <!-- Category Pill -->
                                    <div class="absolute top-2.5 left-2.5 px-2.5 py-0.5 rounded-full bg-white/95 backdrop-blur-md text-[10px] font-bold text-slate-900 border border-slate-200 shadow-2xs">
                                        {{ $svc->category?->name ?? 'Service' }}
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-4 flex-1 flex flex-col justify-between">
                                    <div>
                                        <!-- Seller Row -->
                                        <div class="flex items-center gap-2 mb-2">
                                            <img src="{{ $sellerAvatar }}" alt="{{ $svc->vendor?->shop_name }}" class="w-6 h-6 rounded-full object-cover border border-slate-200">
                                            <a href="{{ route('services.provider', $svc->vendor?->slug ?? '') }}" class="text-xs font-bold text-slate-800 hover:text-black truncate">
                                                {{ $svc->vendor?->shop_name ?? 'Verified Pro' }}
                                            </a>
                                            @if($svc->vendor?->is_verified_pro)
                                                <span class="text-emerald-600 text-xs" title="Verified Specialist">✓</span>
                                            @endif
                                        </div>

                                        <!-- Title -->
                                        <h3 class="font-bold text-xs sm:text-sm text-slate-950 group-hover:text-black transition line-clamp-2 leading-snug">
                                            <a href="{{ route('services.show', $svc->slug) }}">
                                                {{ $svc->title }}
                                            </a>
                                        </h3>

                                        <!-- Rating & Deliverables -->
                                        <div class="flex items-center gap-2 mt-2 text-[11px] text-slate-500 font-medium">
                                            <span class="text-amber-500 font-bold flex items-center gap-0.5">
                                                ★ 5.0
                                            </span>
                                            <span>•</span>
                                            <span>⏱️ {{ $basicPkg?->delivery_days ?? 3 }} Days</span>
                                        </div>
                                    </div>

                                    <!-- Price & CTA -->
                                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                        <div>
                                            <span class="text-[10px] uppercase font-bold text-slate-400 block leading-none">Starting at</span>
                                            <span class="text-base font-black text-slate-950 leading-tight">
                                                ₹{{ number_format($price, 2) }}
                                            </span>
                                        </div>
                                        <a href="{{ route('services.show', $svc->slug) }}" 
                                           class="px-3.5 py-1.5 rounded-xl bg-slate-900 hover:bg-black text-white text-xs font-bold transition shadow-2xs">
                                            View Package
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Clean Pagination -->
                    <div class="mt-8 flex justify-center">
                        {{ $services->links() }}
                    </div>
                @endif
            </section>

            <!-- 4. HOW IT WORKS: 3 EASY STEPS FOR CUSTOMERS -->
            <section class="p-6 sm:p-8 rounded-3xl bg-white border border-slate-200 shadow-2xs">
                <div class="text-center max-w-xl mx-auto mb-6">
                    <h2 class="text-xl sm:text-2xl font-black text-slate-950 tracking-tight">How Hiring Works on K71</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">Simple, transparent, and completely risk-free</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-center sm:text-left">
                        <div class="w-10 h-10 rounded-xl bg-slate-950 text-white font-black flex items-center justify-center text-sm mb-3 mx-auto sm:mx-0 shadow-xs">
                            1
                        </div>
                        <h3 class="font-bold text-sm text-slate-900">Choose Service &amp; Package</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Pick a package with transparent pricing, guaranteed delivery timeline, and revision scope.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-center sm:text-left">
                        <div class="w-10 h-10 rounded-xl bg-slate-950 text-white font-black flex items-center justify-center text-sm mb-3 mx-auto sm:mx-0 shadow-xs">
                            2
                        </div>
                        <h3 class="font-bold text-sm text-slate-900">Collaborate in Workspace</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Submit your project details and communicate with your specialist in a dedicated real-time chat workspace.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 text-center sm:text-left">
                        <div class="w-10 h-10 rounded-xl bg-slate-950 text-white font-black flex items-center justify-center text-sm mb-3 mx-auto sm:mx-0 shadow-xs">
                            3
                        </div>
                        <h3 class="font-bold text-sm text-slate-900">Approve &amp; Release Funds</h3>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Download your finished deliverables. Funds are released to the provider only after your final satisfaction.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 5. TOP SPECIALISTS: CLEAN, LIGHT CARDS -->
            @if($topProviders->isNotEmpty())
                <section class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg sm:text-2xl font-black text-slate-950 tracking-tight">Top-Rated Freelancers</h2>
                            <p class="text-xs sm:text-sm text-slate-500 font-medium">Proven independent professionals ready for your next project</p>
                        </div>
                        <a href="{{ route('services.become_seller') }}" class="text-xs font-bold text-slate-900 hover:text-black underline">
                            Become a Seller →
                        </a>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($topProviders as $pro)
                            @php
                                $pAvatar = $pro->logo ? format_image_url($pro->logo) : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80';
                            @endphp
                            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 hover:border-slate-400 hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-3 mb-2.5">
                                        <img src="{{ $pAvatar }}" alt="{{ $pro->shop_name }}" class="w-11 h-11 rounded-full object-cover border border-slate-200">
                                        <div>
                                            <h4 class="font-bold text-xs sm:text-sm text-slate-900 leading-tight">{{ $pro->shop_name }}</h4>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                                <span class="text-amber-500 font-bold">★ {{ number_format($pro->rating ?? 5.0, 1) }}</span>
                                                <span>({{ $pro->completed_projects ?? 0 }} orders)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                        {{ $pro->tagline ?? $pro->description }}
                                    </p>
                                </div>

                                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-slate-950">
                                        {{ $pro->hourly_rate ? '₹' . number_format($pro->hourly_rate) . '/hr' : 'Fixed Price' }}
                                    </span>
                                    <a href="{{ route('services.provider', $pro->slug) }}" 
                                       class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-900 hover:text-white text-slate-800 text-xs font-bold transition">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- 6. SELLER ONBOARDING BANNER -->
            <section class="p-6 sm:p-8 rounded-3xl bg-slate-950 text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-md">
                <div class="text-center sm:text-left space-y-1">
                    <h3 class="text-xl sm:text-2xl font-black">Offer Your Skills on K71 Services</h3>
                    <p class="text-xs sm:text-sm text-slate-300">Create service packages, get client inquiries, and manage project orders with zero setup fee.</p>
                </div>
                <a href="{{ route('services.become_seller') }}" 
                   class="px-6 py-3 rounded-full bg-white text-slate-950 hover:bg-slate-100 text-xs sm:text-sm font-bold uppercase tracking-wider transition shrink-0 shadow-xs">
                    Start Selling Now
                </a>
            </section>

        </div>

    </div>
</x-layouts.services>
