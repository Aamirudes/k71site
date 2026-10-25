<x-layouts.public 
    title="K71 Services — Freelance & Professional Services Marketplace"
    metaDescription="Discover top-rated freelancers and vetted professionals on K71 Services. Web development, graphic design, video editing, marketing, and skilled craftsmanship with milestone protection."
>
    <!-- PURPLE & WHITE GLASSMORPHIC FREELANCE HUB -->
    <div class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20">

        <!-- HERO SECTION: PURPLE GLASSMORPHISM WITH SEARCH -->
        <section class="relative overflow-hidden pt-8 pb-14 px-4 sm:px-6 lg:px-8">
            <!-- Subtle Background Gradients -->
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-96 bg-gradient-to-tr from-purple-200/40 via-purple-100/30 to-indigo-100/40 rounded-full blur-3xl pointer-events-none -z-10"></div>
            
            <div class="max-w-6xl mx-auto text-center space-y-6">
                <!-- Top Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-white/80 backdrop-blur-md border border-purple-200/80 shadow-xs text-xs font-bold text-purple-700">
                    <span class="w-2 h-2 rounded-full bg-purple-600 animate-pulse"></span>
                    <span>services.k71.in • K71 SERVICES • VERIFIED FREELANCE &amp; PRO MARKETPLACE</span>
                </div>

                <!-- Main Headline -->
                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight text-zinc-950 leading-[1.15]">
                    Hire Top Freelancers &amp; <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-700 via-purple-600 to-indigo-600">
                        World-Class Professionals
                    </span>
                </h1>

                <p class="max-w-2xl mx-auto text-sm sm:text-base text-zinc-600 font-medium leading-relaxed">
                    From digital development, branding, and video editing to expert craftsmanship. Fixed-price tiers, milestone security, and guaranteed delivery.
                </p>

                <!-- Search & Filter Bar (Glassmorphic) -->
                <div class="max-w-3xl mx-auto mt-6">
                    <form action="{{ route('services.index') }}" method="GET" 
                          class="p-2 sm:p-2.5 rounded-3xl bg-white/90 backdrop-blur-xl border border-purple-200/80 shadow-[0_10px_35px_rgba(124,58,237,0.10)] flex flex-col sm:flex-row items-center gap-2">
                        <div class="relative flex-1 w-full flex items-center pl-4">
                            <svg class="w-5 h-5 text-purple-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <input type="text" name="search" value="{{ $search }}"
                                   placeholder="Try 'modern logo design', 'laravel app', 'reels editing'..."
                                   class="w-full px-3 py-3 text-sm bg-transparent border-0 focus:outline-none text-zinc-900 placeholder-zinc-400 font-medium">
                        </div>

                        <!-- Category Dropdown Filter -->
                        <div class="w-full sm:w-auto px-2">
                            <select name="cat" class="w-full sm:w-auto px-3 py-2.5 text-xs font-semibold rounded-2xl bg-purple-50/70 border border-purple-200/60 text-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-400">
                                <option value="">All Categories</option>
                                @foreach($servicesCategories as $cat)
                                    <option value="{{ $cat->slug }}" {{ $selectedCategory === $cat->slug ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white text-xs font-bold uppercase tracking-wider transition shadow-md shadow-purple-500/25 flex items-center justify-center gap-2 shrink-0">
                            <span>Search Gigs</span>
                        </button>
                    </form>
                </div>

                <!-- Popular Tags & Action Buttons -->
                <div class="flex flex-wrap items-center justify-center gap-2 pt-2 text-xs">
                    <span class="text-zinc-500 font-semibold">Popular:</span>
                    <a href="{{ route('services.index', ['search' => 'Logo Design']) }}" class="px-3 py-1 rounded-full bg-white/80 border border-purple-100 text-purple-700 hover:bg-purple-50 transition font-medium shadow-2xs">Logo Design</a>
                    <a href="{{ route('services.index', ['search' => 'Laravel']) }}" class="px-3 py-1 rounded-full bg-white/80 border border-purple-100 text-purple-700 hover:bg-purple-50 transition font-medium shadow-2xs">Web Apps</a>
                    <a href="{{ route('services.index', ['search' => 'Reels']) }}" class="px-3 py-1 rounded-full bg-white/80 border border-purple-100 text-purple-700 hover:bg-purple-50 transition font-medium shadow-2xs">Video Reels</a>
                    <a href="{{ route('services.index', ['search' => 'Marble']) }}" class="px-3 py-1 rounded-full bg-white/80 border border-purple-100 text-purple-700 hover:bg-purple-50 transition font-medium shadow-2xs">Marble Restoration</a>
                    
                    <span class="text-zinc-300 mx-1">•</span>
                    <a href="{{ route('services.become_seller') }}" class="font-bold text-purple-700 hover:text-purple-900 underline underline-offset-4 flex items-center gap-1">
                        <span>Become a Seller</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                </div>
            </div>
        </section>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-16">

            <!-- SERVICE CATEGORIES GRID (Glass Cards) -->
            <section>
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">Explore Services by Category</h2>
                        <p class="text-xs sm:text-sm text-zinc-500 font-medium">Browse verified professionals specialized in your industry.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4">
                    @php
                        $categoryIcons = [
                            'graphic-design-branding' => '🎨',
                            'website-web-development' => '💻',
                            'app-development' => '📱',
                            'video-editing-animation' => '🎬',
                            'digital-marketing-ads' => '📈',
                            'seo-content-writing' => '✍️',
                            'business-consulting' => '📊',
                            'marble-home-craftsmanship' => '🏛️',
                        ];
                    @endphp

                    @foreach($servicesCategories as $cat)
                        <a href="{{ route('services.category', $cat->slug) }}" 
                           class="group p-5 rounded-3xl bg-white/80 backdrop-blur-sm border border-purple-100 hover:border-purple-300 hover:bg-white hover:shadow-[0_10px_25px_rgba(124,58,237,0.08)] transition-all duration-300 flex flex-col justify-between">
                            <div>
                                <div class="w-12 h-12 rounded-2xl bg-purple-50 group-hover:bg-purple-600 group-hover:text-white text-purple-700 flex items-center justify-center text-2xl transition duration-300 shadow-2xs">
                                    {{ $categoryIcons[$cat->slug] ?? '⚡' }}
                                </div>
                                <h3 class="mt-4 font-bold text-sm text-zinc-900 group-hover:text-purple-700 transition leading-snug">
                                    {{ $cat->name }}
                                </h3>
                                @if($cat->description)
                                    <p class="text-[11px] text-zinc-500 line-clamp-2 mt-1 leading-relaxed">
                                        {{ $cat->description }}
                                    </p>
                                @endif
                            </div>
                            <div class="mt-4 pt-3 border-t border-purple-50 flex items-center justify-between text-[11px] font-semibold text-purple-600">
                                <span>{{ $cat->products_count }} Services</span>
                                <span class="group-hover:translate-x-1 transition duration-300">→</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <!-- FEATURED FREELANCE PACKAGES (FIVERR 3-TIER PREVIEWS) -->
            @if($featuredPackages->isNotEmpty())
                <section>
                    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2 mb-6">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-purple-100 text-purple-800 text-[10px] font-black uppercase tracking-wider mb-2">
                                <span>⭐ Handpicked Quality</span>
                            </div>
                            <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">Trending Services &amp; Packages</h2>
                            <p class="text-xs sm:text-sm text-zinc-500 font-medium">Ready-to-order service packages with clear deliverables.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($featuredPackages as $svc)
                            @php
                                $primaryImg = $svc->primary_image_url;
                                $basicPkg = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                            @endphp
                            <div class="group rounded-3xl bg-white/90 backdrop-blur-md border border-purple-100/90 hover:border-purple-300 hover:shadow-[0_12px_35px_rgba(124,58,237,0.10)] transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                <!-- Cover Image -->
                                <div class="relative aspect-video overflow-hidden bg-purple-50">
                                    <a href="{{ route('services.show', $svc->slug) }}">
                                        <img src="{{ $primaryImg }}" alt="{{ $svc->title }}" 
                                             class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    </a>
                                    <div class="absolute top-3 left-3 px-2.5 py-1 rounded-full bg-white/90 backdrop-blur-md border border-purple-200/60 text-[10px] font-bold text-purple-800">
                                        {{ $svc->category?->name ?? 'Professional Service' }}
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <!-- Provider Header -->
                                        <div class="flex items-center gap-2.5 mb-3">
                                            @php
                                                $vLogo = $svc->vendor?->logo ? format_image_url($svc->vendor->logo) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80';
                                            @endphp
                                            <img src="{{ $vLogo }}" alt="{{ $svc->vendor?->shop_name }}" class="w-7 h-7 rounded-full object-cover border border-purple-200">
                                            <div class="min-w-0">
                                                <a href="{{ route('services.provider', $svc->vendor?->slug ?? '') }}" class="text-xs font-bold text-zinc-900 hover:text-purple-700 truncate block">
                                                    {{ $svc->vendor?->shop_name ?? 'Verified Pro' }}
                                                </a>
                                                @if($svc->vendor?->is_verified_pro)
                                                    <span class="text-[10px] text-purple-600 font-semibold flex items-center gap-0.5">
                                                        <span>✓ Verified Pro</span>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Title -->
                                        <h3 class="font-bold text-sm text-zinc-950 group-hover:text-purple-700 transition line-clamp-2 leading-snug">
                                            <a href="{{ route('services.show', $svc->slug) }}">
                                                {{ $svc->title }}
                                            </a>
                                        </h3>

                                        <!-- Quick Deliverables Snippet -->
                                        @if($basicPkg && !empty($basicPkg->features_json))
                                            <div class="mt-3 space-y-1">
                                                @foreach(array_slice($basicPkg->features_json, 0, 2) as $feat)
                                                    <div class="text-[11px] text-zinc-500 flex items-center gap-1.5">
                                                        <span class="text-purple-600 font-bold">✓</span>
                                                        <span class="truncate">{{ $feat }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Bottom Price & Delivery Bar -->
                                    <div class="mt-5 pt-3 border-t border-purple-50 flex items-center justify-between">
                                        <div class="text-[11px] text-zinc-500 font-medium">
                                            <span>⏱️ {{ $basicPkg?->delivery_days ?? 3 }} Days</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-[10px] uppercase font-bold text-zinc-400 block">Starting at</span>
                                            <span class="text-base font-black text-purple-700">
                                                ₹{{ number_format($basicPkg ? $basicPkg->price : ($svc->base_price / 100)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- TOP RECOMMENDED FREELANCERS & PROFESSIONALS -->
            @if($topProviders->isNotEmpty())
                <section class="p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-purple-900 via-purple-950 to-indigo-950 text-white shadow-xl relative overflow-hidden">
                    <div class="absolute -right-20 -bottom-20 w-80 h-80 rounded-full bg-purple-600/20 blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-[10px] font-bold uppercase tracking-wider text-purple-300 mb-2">
                                <span>🏆 Vetted Talent Network</span>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-black tracking-tight">Hire Top-Rated Independent Specialists</h2>
                            <p class="text-xs sm:text-sm text-purple-200 mt-1">Direct collaboration with pre-vetted engineers, designers, and artisans.</p>
                        </div>
                        <a href="{{ route('services.become_seller') }}" 
                           class="inline-block px-5 py-2.5 rounded-2xl bg-white text-purple-900 hover:bg-purple-50 text-xs font-bold uppercase tracking-wider transition active:scale-95 shrink-0 text-center">
                            Join as a Provider
                        </a>
                    </div>

                    <div class="relative z-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($topProviders as $pro)
                            @php
                                $pAvatar = $pro->logo ? format_image_url($pro->logo) : 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=300&q=80';
                            @endphp
                            <div class="p-5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/15 hover:border-white/30 hover:bg-white/15 transition-all duration-300 flex flex-col justify-between">
                                <div>
                                    <div class="flex items-center gap-3 mb-3">
                                        <img src="{{ $pAvatar }}" alt="{{ $pro->shop_name }}" class="w-12 h-12 rounded-full object-cover border-2 border-purple-300/40">
                                        <div>
                                            <h4 class="font-bold text-sm text-white leading-tight">{{ $pro->shop_name }}</h4>
                                            <div class="text-[11px] text-purple-200 flex items-center gap-1">
                                                <span>⭐ {{ number_format($pro->rating ?? 5.0, 1) }}</span>
                                                <span>({{ $pro->completed_projects ?? 0 }} orders)</span>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="text-xs text-purple-100 font-medium line-clamp-2 leading-relaxed">
                                        {{ $pro->tagline ?? $pro->description }}
                                    </p>

                                    <!-- Skills Tags -->
                                    @if(!empty($pro->skills_json) && is_array($pro->skills_json))
                                        <div class="flex flex-wrap gap-1 mt-3">
                                            @foreach(array_slice($pro->skills_json, 0, 3) as $skill)
                                                <span class="px-2 py-0.5 rounded-md bg-white/15 text-[10px] text-purple-200 font-medium">
                                                    {{ $skill }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 pt-3 border-t border-white/10 flex items-center justify-between">
                                    <span class="text-xs font-bold text-purple-200">
                                        {{ $pro->hourly_rate ? '₹' . number_format($pro->hourly_rate) . '/hr' : 'Fixed Rates' }}
                                    </span>
                                    <a href="{{ route('services.provider', $pro->slug) }}" 
                                       class="px-3 py-1.5 rounded-xl bg-purple-500/80 hover:bg-purple-500 text-white text-[11px] font-bold transition">
                                        View Profile
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <!-- ALL SERVICES DISCOVERY FEED (With Filters) -->
            <section id="services-feed">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-black text-zinc-950 tracking-tight">All Available Services</h2>
                        <p class="text-xs sm:text-sm text-zinc-500 font-medium">Showing {{ $services->total() }} services matching your criteria</p>
                    </div>

                    <!-- Sort Controls -->
                    <form action="{{ route('services.index') }}" method="GET" class="flex items-center gap-2">
                        @if($search)<input type="hidden" name="search" value="{{ $search }}">@endif
                        @if($selectedCategory)<input type="hidden" name="cat" value="{{ $selectedCategory }}">@endif
                        <label for="sort" class="text-xs font-bold text-zinc-500">Sort by:</label>
                        <select name="sort" id="sort" onchange="this.form.submit()" 
                                class="px-3 py-2 text-xs font-semibold rounded-xl bg-white border border-purple-200 text-zinc-800 focus:outline-none focus:ring-2 focus:ring-purple-400 shadow-2xs">
                            <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>Newest Listed</option>
                            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="rating" {{ $sort === 'rating' ? 'selected' : '' }}>Top Rated</option>
                        </select>
                    </form>
                </div>

                @if($services->isEmpty())
                    <div class="p-12 text-center rounded-3xl bg-white/70 backdrop-blur-md border border-purple-100 text-zinc-600 space-y-4">
                        <div class="w-16 h-16 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-3xl mx-auto">
                            🔍
                        </div>
                        <h3 class="text-lg font-bold text-zinc-900">No Services Found</h3>
                        <p class="text-xs text-zinc-500 max-w-md mx-auto">We couldn't find services matching your current filters. Try searching with broader keywords or clear your filters.</p>
                        <a href="{{ route('services.index') }}" class="inline-block px-6 py-2.5 rounded-2xl bg-purple-600 text-white text-xs font-bold uppercase tracking-wider shadow-md hover:bg-purple-700 transition">
                            Reset All Filters
                        </a>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
                        @foreach($services as $svc)
                            @php
                                $imgUrl = $svc->primary_image_url;
                                $basic = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                            @endphp
                            <div class="group rounded-3xl bg-white/90 backdrop-blur-md border border-purple-100/90 hover:border-purple-300 hover:shadow-[0_10px_25px_rgba(124,58,237,0.08)] transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                <div class="relative aspect-[4/3] overflow-hidden bg-purple-50">
                                    <a href="{{ route('services.show', $svc->slug) }}">
                                        <img src="{{ $imgUrl }}" alt="{{ $svc->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                    </a>
                                    <div class="absolute top-2.5 left-2.5 px-2 py-0.5 rounded-full bg-white/90 backdrop-blur-sm text-[10px] font-bold text-purple-800 border border-purple-100">
                                        {{ $svc->category?->name ?? 'Service' }}
                                    </div>
                                </div>

                                <div class="p-4 flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="text-[11px] text-zinc-500 font-semibold mb-1">
                                            {{ $svc->vendor?->shop_name ?? 'K71 Specialist' }}
                                        </div>
                                        <h4 class="font-bold text-xs sm:text-sm text-zinc-950 group-hover:text-purple-700 transition line-clamp-2 leading-snug">
                                            <a href="{{ route('services.show', $svc->slug) }}">
                                                {{ $svc->title }}
                                            </a>
                                        </h4>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-purple-50 flex items-center justify-between text-xs">
                                        <span class="text-[11px] text-zinc-400 font-medium">Starting</span>
                                        <span class="font-black text-purple-700">
                                            ₹{{ number_format($basic ? $basic->price : ($svc->base_price / 100), 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="mt-10">
                        {{ $services->links() }}
                    </div>
                @endif
            </section>

            <!-- TRUST & SAFETY SECTION (Purple & White Glass) -->
            <section class="p-8 sm:p-10 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-200/80 shadow-[0_10px_30px_rgba(124,58,237,0.05)]">
                <div class="text-center max-w-2xl mx-auto mb-8">
                    <h2 class="text-2xl font-black text-zinc-950 tracking-tight">The K71 Services Guarantee</h2>
                    <p class="text-xs sm:text-sm text-zinc-500 mt-1">Peace of mind for both buyers and service providers across every project.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-center sm:text-left">
                    <div class="p-5 rounded-2xl bg-purple-50/50 border border-purple-100">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-lg mb-3 shadow-xs">
                            🔒
                        </div>
                        <h4 class="font-bold text-sm text-zinc-950">Milestone Protection</h4>
                        <p class="text-xs text-zinc-600 mt-1 leading-relaxed">
                            Payment is released to the provider only when you review and approve the finished project deliverables.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-purple-50/50 border border-purple-100">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-lg mb-3 shadow-xs">
                            🛡️
                        </div>
                        <h4 class="font-bold text-sm text-zinc-950">Verified Professional Profiles</h4>
                        <p class="text-xs text-zinc-600 mt-1 leading-relaxed">
                            Identity verification and genuine past client reviews ensure you always collaborate with proven specialists.
                        </p>
                    </div>

                    <div class="p-5 rounded-2xl bg-purple-50/50 border border-purple-100">
                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-lg mb-3 shadow-xs">
                            💬
                        </div>
                        <h4 class="font-bold text-sm text-zinc-950">Dedicated Project Workspace</h4>
                        <p class="text-xs text-zinc-600 mt-1 leading-relaxed">
                            Centralized project timeline, file sharing, revision requests, and direct messaging built right into your order.
                        </p>
                    </div>
                </div>
            </section>

        </div>

    </div>
</x-layouts.public>
