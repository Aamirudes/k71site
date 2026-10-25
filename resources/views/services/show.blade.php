<x-layouts.public 
    :title="$service->title . ' — K71 Services'"
    :metaDescription="Str::limit(strip_tags($service->description), 155)"
>
    @php
        $basicPkg = $service->gigPackages->firstWhere('tier', 'basic') ?? $service->gigPackages->first();
        $standardPkg = $service->gigPackages->firstWhere('tier', 'standard');
        $premiumPkg = $service->gigPackages->firstWhere('tier', 'premium');
        $vendor = $service->vendor;
        $vendorAvatar = $vendor?->logo ? format_image_url($vendor->logo) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
    @endphp

    <div x-data="{ 
            selectedTier: 'basic',
            activeImage: '{{ $service->primary_image_url }}',
            prices: {
                'basic': {{ $basicPkg ? $basicPkg->price : ($service->base_price / 100) }},
                'standard': {{ $standardPkg ? $standardPkg->price : 0 }},
                'premium': {{ $premiumPkg ? $premiumPkg->price : 0 }}
            }
         }"
         class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20 pt-6">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- BREADCRUMBS & TOP BAR -->
            <div class="flex flex-wrap items-center justify-between gap-3 text-xs mb-6 text-zinc-500">
                <div class="flex items-center gap-2">
                    <a href="{{ route('services.index') }}" class="hover:text-purple-700 transition">Services</a>
                    <span>/</span>
                    @if($service->category)
                        <a href="{{ route('services.category', $service->category->slug) }}" class="hover:text-purple-700 transition font-medium">
                            {{ $service->category->name }}
                        </a>
                        <span>/</span>
                    @endif
                    <span class="text-zinc-800 font-bold truncate max-w-xs">{{ $service->title }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('services.become_seller') }}" class="text-purple-700 hover:text-purple-900 font-bold">
                        Become a Seller
                    </a>
                </div>
            </div>

            <!-- MAIN GRID: LEFT CONTENT + RIGHT STICKY PACKAGE CARD -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- LEFT COLUMN (8 COLS): GALLERY, OVERVIEW, DESCRIPTION, FAQS, REVIEWS -->
                <div class="lg:col-span-8 space-y-8">

                    <!-- SERVICE HEADER & SELLER SUMMARY -->
                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black text-zinc-950 tracking-tight leading-tight mb-4">
                            {{ $service->title }}
                        </h1>

                        <div class="flex flex-wrap items-center gap-4 pb-6 border-b border-purple-100 text-xs">
                            <!-- Seller Pill -->
                            <a href="{{ route('services.provider', $vendor?->slug ?? '') }}" class="flex items-center gap-2 group">
                                <img src="{{ $vendorAvatar }}" alt="{{ $vendor?->shop_name }}" class="w-8 h-8 rounded-full object-cover border border-purple-200">
                                <div>
                                    <div class="font-bold text-zinc-900 group-hover:text-purple-700 transition flex items-center gap-1">
                                        <span>{{ $vendor?->shop_name ?? 'Verified Specialist' }}</span>
                                        @if($vendor?->is_verified_pro)
                                            <span class="text-purple-600 text-[11px]" title="Verified Pro">✓</span>
                                        @endif
                                    </div>
                                    <div class="text-[10px] text-zinc-400 font-medium">
                                        Level 2 Verified Freelancer
                                    </div>
                                </div>
                            </a>

                            <div class="h-6 w-px bg-purple-100 hidden sm:block"></div>

                            <!-- Rating & Reviews -->
                            <div class="flex items-center gap-1 text-amber-500 font-bold">
                                <span>⭐ {{ number_format($vendor?->rating ?? 5.0, 1) }}</span>
                                <span class="text-zinc-400 font-medium">({{ $vendor?->completed_projects ?? 0 }} orders completed)</span>
                            </div>

                            <div class="h-6 w-px bg-purple-100 hidden sm:block"></div>

                            <!-- Fast Response Time -->
                            <div class="text-zinc-500 flex items-center gap-1">
                                <span>⚡ Avg Response:</span>
                                <span class="font-bold text-zinc-800">{{ $vendor?->response_time ?? '1 Hour' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- IMAGE / PORTFOLIO GALLERY (Glass Container) -->
                    <div class="rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 p-4 shadow-sm space-y-3">
                        <div class="relative aspect-video rounded-2xl overflow-hidden bg-purple-50 border border-purple-50">
                            <img :src="activeImage" alt="{{ $service->title }}" class="w-full h-full object-cover">
                        </div>

                        @if($service->images->count() > 1)
                            <div class="flex items-center gap-3 overflow-x-auto no-scrollbar pt-1">
                                @foreach($service->images as $img)
                                    <button type="button" @click="activeImage = '{{ $img->url }}'" 
                                            :class="activeImage === '{{ $img->url }}' ? 'ring-2 ring-purple-600 border-transparent' : 'opacity-70 hover:opacity-100 border-purple-100'"
                                            class="w-20 h-14 rounded-xl overflow-hidden border shrink-0 transition">
                                        <img src="{{ $img->url }}" alt="{{ $img->alt_text }}" class="w-full h-full object-cover">
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- ABOUT THIS GIG (Full Description) -->
                    <div class="rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 p-6 sm:p-8 shadow-sm space-y-4">
                        <h2 class="text-lg font-black text-zinc-950 uppercase tracking-wider text-purple-900">
                            About This Service
                        </h2>
                        
                        <div class="text-sm text-zinc-700 leading-relaxed space-y-4 whitespace-pre-line font-normal">
                            {{ $service->description }}
                        </div>
                    </div>

                    <!-- 3-TIER PACKAGE COMPARISON TABLE -->
                    @if($service->gigPackages->isNotEmpty())
                        <div class="rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 p-6 sm:p-8 shadow-sm space-y-6">
                            <h2 class="text-lg font-black text-zinc-950 tracking-tight">
                                Compare Package Deliverables
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <!-- BASIC -->
                                @if($basicPkg)
                                    <div class="p-5 rounded-2xl border-2 transition duration-300 flex flex-col justify-between"
                                         :class="selectedTier === 'basic' ? 'border-purple-600 bg-purple-50/50 shadow-md' : 'border-purple-100 bg-white'">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-black uppercase tracking-wider text-purple-700">Basic</span>
                                                <span class="text-lg font-black text-zinc-950">₹{{ number_format($basicPkg->price) }}</span>
                                            </div>
                                            <h4 class="font-bold text-xs text-zinc-900 mb-2">{{ $basicPkg->title }}</h4>
                                            <p class="text-xs text-zinc-500 mb-4 leading-relaxed">{{ $basicPkg->description }}</p>

                                            <div class="text-xs font-bold text-zinc-700 mb-3 flex items-center gap-3">
                                                <span>⏱️ {{ $basicPkg->delivery_days }} Days Delivery</span>
                                                <span>🔄 {{ $basicPkg->revisions }} Revisions</span>
                                            </div>

                                            @if(!empty($basicPkg->features_json))
                                                <ul class="space-y-1.5 text-xs text-zinc-600">
                                                    @foreach($basicPkg->features_json as $f)
                                                        <li class="flex items-center gap-2">
                                                            <span class="text-purple-600 font-bold">✓</span>
                                                            <span>{{ $f }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>

                                        <button type="button" @click="selectedTier = 'basic'"
                                                class="mt-6 w-full py-2.5 rounded-xl text-xs font-bold transition"
                                                :class="selectedTier === 'basic' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'">
                                            Select Basic
                                        </button>
                                    </div>
                                @endif

                                <!-- STANDARD -->
                                @if($standardPkg)
                                    <div class="p-5 rounded-2xl border-2 transition duration-300 flex flex-col justify-between"
                                         :class="selectedTier === 'standard' ? 'border-purple-600 bg-purple-50/50 shadow-md' : 'border-purple-100 bg-white'">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-black uppercase tracking-wider text-purple-700">Standard</span>
                                                <span class="text-lg font-black text-zinc-950">₹{{ number_format($standardPkg->price) }}</span>
                                            </div>
                                            <h4 class="font-bold text-xs text-zinc-900 mb-2">{{ $standardPkg->title }}</h4>
                                            <p class="text-xs text-zinc-500 mb-4 leading-relaxed">{{ $standardPkg->description }}</p>

                                            <div class="text-xs font-bold text-zinc-700 mb-3 flex items-center gap-3">
                                                <span>⏱️ {{ $standardPkg->delivery_days }} Days Delivery</span>
                                                <span>🔄 {{ $standardPkg->revisions }} Revisions</span>
                                            </div>

                                            @if(!empty($standardPkg->features_json))
                                                <ul class="space-y-1.5 text-xs text-zinc-600">
                                                    @foreach($standardPkg->features_json as $f)
                                                        <li class="flex items-center gap-2">
                                                            <span class="text-purple-600 font-bold">✓</span>
                                                            <span>{{ $f }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>

                                        <button type="button" @click="selectedTier = 'standard'"
                                                class="mt-6 w-full py-2.5 rounded-xl text-xs font-bold transition"
                                                :class="selectedTier === 'standard' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'">
                                            Select Standard
                                        </button>
                                    </div>
                                @endif

                                <!-- PREMIUM -->
                                @if($premiumPkg)
                                    <div class="p-5 rounded-2xl border-2 transition duration-300 flex flex-col justify-between"
                                         :class="selectedTier === 'premium' ? 'border-purple-600 bg-purple-50/50 shadow-md' : 'border-purple-100 bg-white'">
                                        <div>
                                            <div class="flex items-center justify-between mb-2">
                                                <span class="text-xs font-black uppercase tracking-wider text-purple-700">Premium Pro</span>
                                                <span class="text-lg font-black text-zinc-950">₹{{ number_format($premiumPkg->price) }}</span>
                                            </div>
                                            <h4 class="font-bold text-xs text-zinc-900 mb-2">{{ $premiumPkg->title }}</h4>
                                            <p class="text-xs text-zinc-500 mb-4 leading-relaxed">{{ $premiumPkg->description }}</p>

                                            <div class="text-xs font-bold text-zinc-700 mb-3 flex items-center gap-3">
                                                <span>⏱️ {{ $premiumPkg->delivery_days }} Days Delivery</span>
                                                <span>🔄 {{ $premiumPkg->revisions }} Revisions</span>
                                            </div>

                                            @if(!empty($premiumPkg->features_json))
                                                <ul class="space-y-1.5 text-xs text-zinc-600">
                                                    @foreach($premiumPkg->features_json as $f)
                                                        <li class="flex items-center gap-2">
                                                            <span class="text-purple-600 font-bold">✓</span>
                                                            <span>{{ $f }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </div>

                                        <button type="button" @click="selectedTier = 'premium'"
                                                class="mt-6 w-full py-2.5 rounded-xl text-xs font-bold transition"
                                                :class="selectedTier === 'premium' ? 'bg-purple-600 text-white shadow-xs' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'">
                                            Select Premium
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- FREQUENTLY ASKED QUESTIONS (Accordion) -->
                    @if(!empty($service->faqs_json) && is_array($service->faqs_json))
                        <div class="rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 p-6 sm:p-8 shadow-sm space-y-4">
                            <h2 class="text-lg font-black text-zinc-950 tracking-tight">
                                Frequently Asked Questions
                            </h2>

                            <div class="space-y-3">
                                @foreach($service->faqs_json as $faq)
                                    <div x-data="{ open: false }" class="rounded-2xl border border-purple-100 overflow-hidden bg-white">
                                        <button type="button" @click="open = !open" 
                                                class="w-full p-4 text-left font-bold text-xs sm:text-sm text-zinc-900 flex items-center justify-between hover:bg-purple-50/50 transition">
                                            <span>{{ $faq['q'] ?? '' }}</span>
                                            <span class="text-purple-600 font-black text-base" x-text="open ? '−' : '+'"></span>
                                        </button>
                                        <div x-show="open" x-collapse class="p-4 pt-0 text-xs text-zinc-600 leading-relaxed border-t border-purple-50 bg-purple-50/20">
                                            {{ $faq['a'] ?? '' }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- GENUINE CLIENT REVIEWS -->
                    <div class="rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 p-6 sm:p-8 shadow-sm space-y-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-black text-zinc-950 tracking-tight">Client Feedback &amp; Reviews</h2>
                                <p class="text-xs text-zinc-500">Verified completed project reviews</p>
                            </div>
                            <div class="text-right">
                                <span class="text-2xl font-black text-purple-700">⭐ {{ number_format($vendor?->rating ?? 5.0, 1) }}</span>
                                <span class="text-xs text-zinc-400 block">Out of 5.0</span>
                            </div>
                        </div>

                        @if($service->reviews->isEmpty())
                            <div class="p-6 rounded-2xl bg-purple-50/50 border border-purple-100 text-center text-xs text-zinc-500">
                                Be the first client to complete a project and review this service!
                            </div>
                        @else
                            <div class="space-y-4">
                                @foreach($service->reviews as $rev)
                                    <div class="p-4 rounded-2xl border border-purple-100 bg-white space-y-2">
                                        <div class="flex items-center justify-between">
                                            <div class="font-bold text-xs text-zinc-900">{{ $rev->user?->name ?? 'Verified Client' }}</div>
                                            <div class="text-amber-500 text-xs">
                                                @for($i = 0; $i < $rev->rating; $i++)★@endfor
                                            </div>
                                        </div>
                                        <p class="text-xs text-zinc-600 leading-relaxed">{{ $rev->comment }}</p>
                                        <div class="text-[10px] text-zinc-400 font-medium">{{ $rev->created_at->diffForHumans() }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>

                <!-- RIGHT COLUMN (4 COLS): STICKY ORDER & PACKAGE CARD -->
                <div class="lg:col-span-4 sticky top-6 space-y-6">

                    <!-- PURPLE GLASSMORPHIC PACKAGE SELECTOR & CHECKOUT CTA -->
                    <div class="rounded-3xl bg-white/95 backdrop-blur-xl border-2 border-purple-200/90 p-6 shadow-[0_15px_40px_rgba(124,58,237,0.12)] space-y-6">

                        <!-- Tier Selector Tabs -->
                        <div class="grid grid-cols-3 gap-1 p-1 rounded-2xl bg-purple-50 border border-purple-200/80 text-xs font-bold text-center">
                            <button type="button" @click="selectedTier = 'basic'"
                                    :class="selectedTier === 'basic' ? 'bg-purple-600 text-white shadow-xs' : 'text-purple-900 hover:text-purple-600'"
                                    class="py-2 rounded-xl transition">
                                Basic
                            </button>
                            <button type="button" @click="selectedTier = 'standard'"
                                    :class="selectedTier === 'standard' ? 'bg-purple-600 text-white shadow-xs' : 'text-purple-900 hover:text-purple-600'"
                                    class="py-2 rounded-xl transition">
                                Standard
                            </button>
                            <button type="button" @click="selectedTier = 'premium'"
                                    :class="selectedTier === 'premium' ? 'bg-purple-600 text-white shadow-xs' : 'text-purple-900 hover:text-purple-600'"
                                    class="py-2 rounded-xl transition">
                                Premium
                            </button>
                        </div>

                        <!-- Active Package Summary -->
                        <div>
                            <div class="flex items-baseline justify-between mb-2">
                                <span class="text-xs font-black uppercase tracking-wider text-purple-700" x-text="selectedTier.toUpperCase() + ' PACKAGE'"></span>
                                <span class="text-3xl font-black text-zinc-950" x-text="'₹' + Number(prices[selectedTier]).toLocaleString('en-IN')"></span>
                            </div>

                            <!-- Delivery & Revisions Badge -->
                            <div class="flex items-center gap-4 text-xs font-bold text-zinc-600 py-3 border-y border-purple-100 my-4">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-text="selectedTier === 'basic' ? '{{ $basicPkg?->delivery_days ?? 3 }} Days Delivery' : (selectedTier === 'standard' ? '{{ $standardPkg?->delivery_days ?? 5 }} Days' : '{{ $premiumPkg?->delivery_days ?? 7 }} Days')"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                    <span x-text="selectedTier === 'basic' ? '{{ $basicPkg?->revisions ?? 2 }} Revisions' : (selectedTier === 'standard' ? '{{ $standardPkg?->revisions ?? 5 }} Revisions' : 'Unlimited Revisions')"></span>
                                </div>
                            </div>

                            <!-- Deliverables List -->
                            <div class="space-y-2 text-xs text-zinc-600 mb-6">
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>Milestone payment escrow security</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>Collaborative order workspace &amp; chat</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-purple-600 font-bold">✓</span>
                                    <span>100% Commercial rights transfer</span>
                                </div>
                            </div>

                            <!-- CONTINUE TO ORDER BUTTON -->
                            <a :href="'{{ route('services.order.checkout', $service) }}?tier=' + selectedTier"
                               class="w-full py-4 rounded-2xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-black text-xs uppercase tracking-wider text-center block transition shadow-lg shadow-purple-500/30">
                                <span>Continue to Order →</span>
                            </a>

                            <div class="text-[11px] text-center text-zinc-400 mt-3 font-medium">
                                You won't be charged until project milestone approval.
                            </div>
                        </div>

                    </div>

                    <!-- FREELANCER MINI PROFILE CARD -->
                    <div class="rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $vendorAvatar }}" alt="{{ $vendor?->shop_name }}" class="w-14 h-14 rounded-2xl object-cover border border-purple-200">
                            <div>
                                <h3 class="font-bold text-sm text-zinc-950">{{ $vendor?->shop_name }}</h3>
                                <p class="text-xs text-purple-700 font-semibold">{{ $vendor?->city ?? 'All-India Verified Pro' }}</p>
                                <div class="text-[11px] text-zinc-500 mt-0.5">⭐ {{ number_format($vendor?->rating ?? 5.0, 1) }} ({{ $vendor?->completed_projects ?? 0 }} orders)</div>
                            </div>
                        </div>

                        <p class="text-xs text-zinc-600 leading-relaxed">
                            {{ $vendor?->tagline ?? $vendor?->description }}
                        </p>

                        <div class="pt-3 border-t border-purple-50 flex items-center justify-between gap-2">
                            <a href="{{ route('services.provider', $vendor?->slug ?? '') }}" 
                               class="flex-1 py-2 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 text-xs font-bold text-center transition">
                                View Profile
                            </a>
                            <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $vendor?->phone ?? '9876543210') }}?text={{ urlencode('Hi, I saw your service ' . $service->title . ' on K71 Services.') }}" 
                               target="_blank" rel="noopener noreferrer"
                               class="flex-1 py-2 rounded-xl bg-zinc-900 hover:bg-black text-white text-xs font-bold text-center transition flex items-center justify-center gap-1.5">
                                <span>Contact</span>
                            </a>
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>
</x-layouts.public>
