<x-layouts.services 
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
         class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- OBSIDIAN GIG HERO HEADER -->
        <section class="grail-hero-band pt-6 sm:pt-8 pb-8 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-7xl mx-auto space-y-5">
                <!-- BREADCRUMBS & TOP BAR -->
                <div class="flex items-center justify-between gap-3 text-xs text-[#EFE9F2]/65 overflow-hidden">
                    <div class="flex items-center gap-1.5 min-w-0 overflow-x-auto no-scrollbar whitespace-nowrap py-1">
                        <a href="{{ route('services.index') }}" class="hover:text-[#ECD2F3] transition shrink-0">Services</a>
                        <span class="text-white/25">/</span>
                        @if($service->category)
                            <a href="{{ route('services.category', $service->category->slug) }}" class="hover:text-[#ECD2F3] transition font-medium shrink-0 max-w-[120px] sm:max-w-none truncate">
                                {{ $service->category->name }}
                            </a>
                            <span class="text-white/25">/</span>
                        @endif
                        <span class="text-white font-semibold truncate max-w-[150px] sm:max-w-xs">{{ $service->title }}</span>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('services.become_seller') }}" class="text-[#ECD2F3] hover:text-[#FFCDFF] font-semibold whitespace-nowrap text-[11px] sm:text-xs transition">
                            Become a Seller &rarr;
                        </a>
                    </div>
                </div>

                <!-- SERVICE HEADER & SELLER SUMMARY -->
                <div>
                    <h1 class="text-2xl sm:text-4xl font-light text-white tracking-tight leading-tight mb-4" style="font-family: var(--font-grail-display);">
                        {{ $service->title }}
                    </h1>

                    <div class="flex flex-wrap items-center gap-4 text-xs">
                        <!-- Seller Pill -->
                        @if($vendor && $vendor->slug)
                            <a href="{{ route('services.provider', $vendor->slug) }}" class="flex items-center gap-2.5 group">
                        @else
                            <div class="flex items-center gap-2.5">
                        @endif
                            <img src="{{ $vendorAvatar }}" alt="{{ $vendor?->shop_name }}" class="w-9 h-9 rounded-full object-cover border border-[#ECD2F3]/50">
                            <div>
                                <div class="font-bold text-white group-hover:text-[#ECD2F3] transition flex items-center gap-1">
                                    <span>{{ $vendor?->shop_name ?? 'Verified Specialist' }}</span>
                                    @if($vendor?->is_verified_pro)
                                        <span class="text-[#ECD2F3] text-[11px]" title="Verified Pro">✓</span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-[#C9A24A] font-semibold uppercase tracking-[0.12em]">
                                    Level 2 Verified Freelancer
                                </div>
                            </div>
                        @if($vendor && $vendor->slug)
                            </a>
                        @else
                            </div>
                        @endif

                        <div class="h-6 w-px bg-white/15 hidden sm:block"></div>

                        <!-- Rating & Reviews -->
                        <div class="flex items-center gap-1.5 text-[#C9A24A] font-bold">
                            <span>⭐ {{ number_format($vendor?->rating ?? 5.0, 1) }}</span>
                            <span class="text-[#EFE9F2]/60 font-medium">({{ $vendor?->completed_projects ?? 0 }} orders completed)</span>
                        </div>

                        <div class="h-6 w-px bg-white/15 hidden sm:block"></div>

                        <!-- Fast Response Time -->
                        <div class="text-[#EFE9F2]/65 flex items-center gap-1">
                            <span>⚡ Avg Response:</span>
                            <span class="font-bold text-white">{{ $vendor?->response_time ?? '1 Hour' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CURVED EDITORIAL SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-10 pb-24 lg:pb-16 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

                <!-- MAIN GRID: LEFT CONTENT + RIGHT STICKY PACKAGE CARD -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                    <!-- LEFT COLUMN (8 COLS): GALLERY, DESCRIPTION, PACKAGES, FAQS, REVIEWS -->
                    <div class="lg:col-span-8 space-y-6">

                        <!-- IMAGE / PORTFOLIO GALLERY -->
                        <div class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-3.5 sm:p-4 shadow-xs space-y-3" data-grail-reveal>
                            <div class="relative aspect-video rounded-2xl overflow-hidden bg-[#0D0D0D] border border-[#E6E2E8]">
                                <img :src="activeImage" alt="{{ $service->title }}" class="w-full h-full object-cover">
                            </div>

                            @if($service->images->count() > 1)
                                <div class="flex items-center gap-3 overflow-x-auto no-scrollbar pt-1">
                                    @foreach($service->images as $img)
                                        <button type="button" @click="activeImage = '{{ $img->url }}'" 
                                                :class="activeImage === '{{ $img->url }}' ? 'ring-2 ring-[#623562] border-[#1A1018]' : 'opacity-65 hover:opacity-100 border-[#E6E2E8]'"
                                                class="w-20 h-14 rounded-xl overflow-hidden border shrink-0 transition">
                                            <img src="{{ $img->url }}" alt="{{ $img->alt_text }}" class="w-full h-full object-cover">
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- ABOUT THIS GIG (Full Description) -->
                        <div class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-6 sm:p-8 shadow-xs space-y-4" data-grail-reveal>
                            <h2 class="text-xl sm:text-2xl font-light text-[#1A1018]" style="font-family: var(--font-grail-display);">
                                About This Service
                            </h2>
                            
                            <div class="text-sm text-[#1A1018]/80 leading-relaxed space-y-4 whitespace-pre-line font-normal">
                                {{ $service->description }}
                            </div>
                        </div>

                        <!-- 3-TIER PACKAGE COMPARISON TABLE -->
                        @if($service->gigPackages->isNotEmpty())
                            <div class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-6 sm:p-8 shadow-xs space-y-6" data-grail-reveal>
                                <h2 class="text-xl sm:text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                    Compare Package Deliverables
                                </h2>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- BASIC -->
                                    @if($basicPkg)
                                        <div class="p-5 rounded-2xl border-2 transition duration-300 flex flex-col justify-between"
                                             :class="selectedTier === 'basic' ? 'border-[#623562] bg-[#F8EFFA]/70 shadow-md' : 'border-[#E6E2E8] bg-[#FAFAF8]'">
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="grail-eyebrow !text-[#623562]">Basic</span>
                                                    <span class="text-lg font-bold text-[#1A1018]">₹{{ number_format($basicPkg->price) }}</span>
                                                </div>
                                                <h4 class="font-bold text-xs text-[#1A1018] mb-2">{{ $basicPkg->title }}</h4>
                                                <p class="text-xs text-[#1A1018]/65 mb-4 leading-relaxed">{{ $basicPkg->description }}</p>

                                                <div class="text-xs font-bold text-[#1A1018]/80 mb-3 flex items-center gap-3">
                                                    <span>⏱️ {{ $basicPkg->delivery_days }} Days Delivery</span>
                                                    <span>🔄 {{ $basicPkg->revisions }} Revisions</span>
                                                </div>

                                                @if(!empty($basicPkg->features_json))
                                                    <ul class="space-y-1.5 text-xs text-[#1A1018]/75">
                                                        @foreach($basicPkg->features_json as $f)
                                                            <li class="flex items-center gap-2">
                                                                <span class="text-[#623562] font-bold">✓</span>
                                                                <span>{{ $f }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>

                                            <button type="button" @click="selectedTier = 'basic'"
                                                    class="mt-6 w-full py-2.5 rounded-full text-xs font-bold transition cursor-pointer"
                                                    :class="selectedTier === 'basic' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'bg-[#EFE9F2] text-[#1A1018] hover:bg-[#ECD2F3]/60'">
                                                Select Basic
                                            </button>
                                        </div>
                                    @endif

                                    <!-- STANDARD -->
                                    @if($standardPkg)
                                        <div class="p-5 rounded-2xl border-2 transition duration-300 flex flex-col justify-between"
                                             :class="selectedTier === 'standard' ? 'border-[#623562] bg-[#F8EFFA]/70 shadow-md' : 'border-[#E6E2E8] bg-[#FAFAF8]'">
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="grail-eyebrow !text-[#623562]">Standard</span>
                                                    <span class="text-lg font-bold text-[#1A1018]">₹{{ number_format($standardPkg->price) }}</span>
                                                </div>
                                                <h4 class="font-bold text-xs text-[#1A1018] mb-2">{{ $standardPkg->title }}</h4>
                                                <p class="text-xs text-[#1A1018]/65 mb-4 leading-relaxed">{{ $standardPkg->description }}</p>

                                                <div class="text-xs font-bold text-[#1A1018]/80 mb-3 flex items-center gap-3">
                                                    <span>⏱️ {{ $standardPkg->delivery_days }} Days Delivery</span>
                                                    <span>🔄 {{ $standardPkg->revisions }} Revisions</span>
                                                </div>

                                                @if(!empty($standardPkg->features_json))
                                                    <ul class="space-y-1.5 text-xs text-[#1A1018]/75">
                                                        @foreach($standardPkg->features_json as $f)
                                                            <li class="flex items-center gap-2">
                                                                <span class="text-[#623562] font-bold">✓</span>
                                                                <span>{{ $f }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>

                                            <button type="button" @click="selectedTier = 'standard'"
                                                    class="mt-6 w-full py-2.5 rounded-full text-xs font-bold transition cursor-pointer"
                                                    :class="selectedTier === 'standard' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'bg-[#EFE9F2] text-[#1A1018] hover:bg-[#ECD2F3]/60'">
                                                Select Standard
                                            </button>
                                        </div>
                                    @endif

                                    <!-- PREMIUM -->
                                    @if($premiumPkg)
                                        <div class="p-5 rounded-2xl border-2 transition duration-300 flex flex-col justify-between"
                                             :class="selectedTier === 'premium' ? 'border-[#623562] bg-[#F8EFFA]/70 shadow-md' : 'border-[#E6E2E8] bg-[#FAFAF8]'">
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="grail-eyebrow !text-[#C9A24A]">Premium Pro</span>
                                                    <span class="text-lg font-bold text-[#1A1018]">₹{{ number_format($premiumPkg->price) }}</span>
                                                </div>
                                                <h4 class="font-bold text-xs text-[#1A1018] mb-2">{{ $premiumPkg->title }}</h4>
                                                <p class="text-xs text-[#1A1018]/65 mb-4 leading-relaxed">{{ $premiumPkg->description }}</p>

                                                <div class="text-xs font-bold text-[#1A1018]/80 mb-3 flex items-center gap-3">
                                                    <span>⏱️ {{ $premiumPkg->delivery_days }} Days Delivery</span>
                                                    <span>🔄 {{ $premiumPkg->revisions }} Revisions</span>
                                                </div>

                                                @if(!empty($premiumPkg->features_json))
                                                    <ul class="space-y-1.5 text-xs text-[#1A1018]/75">
                                                        @foreach($premiumPkg->features_json as $f)
                                                            <li class="flex items-center gap-2">
                                                                <span class="text-[#623562] font-bold">✓</span>
                                                                <span>{{ $f }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </div>

                                            <button type="button" @click="selectedTier = 'premium'"
                                                    class="mt-6 w-full py-2.5 rounded-full text-xs font-bold transition cursor-pointer"
                                                    :class="selectedTier === 'premium' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'bg-[#EFE9F2] text-[#1A1018] hover:bg-[#ECD2F3]/60'">
                                                Select Premium
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- FREQUENTLY ASKED QUESTIONS (Accordion) -->
                        @if(!empty($service->faqs_json) && is_array($service->faqs_json))
                            <div class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-6 sm:p-8 shadow-xs space-y-4" data-grail-reveal>
                                <h2 class="text-xl sm:text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">
                                    Frequently Asked Questions
                                </h2>

                                <div class="space-y-3">
                                    @foreach($service->faqs_json as $faq)
                                        <div x-data="{ open: false }" class="rounded-2xl border border-[#E6E2E8] overflow-hidden bg-[#FAFAF8]">
                                            <button type="button" @click="open = !open" 
                                                    class="w-full p-4 text-left font-bold text-xs sm:text-sm text-[#1A1018] flex items-center justify-between hover:bg-[#F8EFFA] transition">
                                                <span>{{ $faq['q'] ?? '' }}</span>
                                                <span class="text-[#623562] font-black text-base" x-text="open ? '−' : '+'"></span>
                                            </button>
                                            <div x-show="open" x-collapse class="p-4 pt-0 text-xs text-[#1A1018]/75 leading-relaxed border-t border-[#E6E2E8] bg-white">
                                                {{ $faq['a'] ?? '' }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- GENUINE CLIENT REVIEWS -->
                        <div class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-6 sm:p-8 shadow-xs space-y-6" data-grail-reveal>
                            <div class="flex items-center justify-between">
                                <div>
                                    <h2 class="text-xl sm:text-2xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Client Feedback &amp; Reviews</h2>
                                    <p class="text-xs text-[#1A1018]/55">Verified completed project reviews</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-bold text-[#1A1018]">⭐ {{ number_format($vendor?->rating ?? 5.0, 1) }}</span>
                                    <span class="text-xs text-[#1A1018]/45 block">Out of 5.0</span>
                                </div>
                            </div>

                            @if($service->reviews->isEmpty())
                                <div class="p-6 rounded-2xl bg-[#F8EFFA]/70 border border-[#ECD2F3] text-center text-xs text-[#1A1018]/65">
                                    Be the first client to complete a project and review this service!
                                </div>
                            @else
                                <div class="space-y-4">
                                    @foreach($service->reviews as $rev)
                                        <div class="p-4 rounded-2xl border border-[#E6E2E8] bg-[#FAFAF8] space-y-2">
                                            <div class="flex items-center justify-between">
                                                <div class="font-bold text-xs text-[#1A1018]">{{ $rev->user?->name ?? 'Verified Client' }}</div>
                                                <div class="text-[#C9A24A] text-xs">
                                                    @for($i = 0; $i < $rev->rating; $i++)★@endfor
                                                </div>
                                            </div>
                                            <p class="text-xs text-[#1A1018]/75 leading-relaxed">{{ $rev->comment }}</p>
                                            <div class="text-[10px] text-[#1A1018]/45 font-medium">{{ $rev->created_at->diffForHumans() }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>

                    <!-- RIGHT COLUMN (4 COLS): STICKY ORDER & PACKAGE CARD -->
                    <div class="lg:col-span-4 lg:sticky lg:top-24 space-y-6">

                        <!-- GRAIL PACKAGE SELECTOR & CHECKOUT CTA -->
                        <div class="grail-card rounded-3xl bg-white border border-[#ECD2F3] p-6 shadow-md space-y-6">

                            <!-- Tier Selector Tabs -->
                            <div class="grid grid-cols-3 gap-1 p-1 rounded-full bg-[#F8EFFA] border border-[#ECD2F3] text-xs font-bold text-center">
                                <button type="button" @click="selectedTier = 'basic'"
                                        :class="selectedTier === 'basic' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'text-[#1A1018] hover:text-[#623562]'"
                                        class="py-2 rounded-full transition cursor-pointer">
                                    Basic
                                </button>
                                <button type="button" @click="selectedTier = 'standard'"
                                        :class="selectedTier === 'standard' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'text-[#1A1018] hover:text-[#623562]'"
                                        class="py-2 rounded-full transition cursor-pointer">
                                    Standard
                                </button>
                                <button type="button" @click="selectedTier = 'premium'"
                                        :class="selectedTier === 'premium' ? 'bg-[#1A1018] text-[#ECD2F3] shadow-xs' : 'text-[#1A1018] hover:text-[#623562]'"
                                        class="py-2 rounded-full transition cursor-pointer">
                                    Premium
                                </button>
                            </div>

                            <!-- Active Package Summary -->
                            <div>
                                <div class="flex items-baseline justify-between mb-2">
                                    <span class="grail-eyebrow !text-[#623562]" x-text="selectedTier.toUpperCase() + ' PACKAGE'"></span>
                                    <span class="text-3xl font-bold text-[#1A1018]" x-text="'₹' + Number(prices[selectedTier]).toLocaleString('en-IN')"></span>
                                </div>

                                <!-- Delivery & Revisions Badge -->
                                <div class="flex items-center gap-4 text-xs font-semibold text-[#1A1018]/75 py-3 border-y border-[#E6E2E8] my-4">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#623562]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span x-text="selectedTier === 'basic' ? '{{ $basicPkg?->delivery_days ?? 3 }} Days Delivery' : (selectedTier === 'standard' ? '{{ $standardPkg?->delivery_days ?? 5 }} Days' : '{{ $premiumPkg?->delivery_days ?? 7 }} Days')"></span>
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-[#623562]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        <span x-text="selectedTier === 'basic' ? '{{ $basicPkg?->revisions ?? 2 }} Revisions' : (selectedTier === 'standard' ? '{{ $standardPkg?->revisions ?? 5 }} Revisions' : 'Unlimited Revisions')"></span>
                                    </div>
                                </div>

                                <!-- Deliverables List -->
                                <div class="space-y-2 text-xs text-[#1A1018]/75 mb-6">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#623562] font-bold">✓</span>
                                        <span>Milestone payment escrow security</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#623562] font-bold">✓</span>
                                        <span>Collaborative order workspace &amp; chat</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#623562] font-bold">✓</span>
                                        <span>100% Commercial rights transfer</span>
                                    </div>
                                </div>

                                <!-- CONTINUE TO ORDER BUTTON -->
                                <a :href="'{{ route('services.order.checkout', $service) }}?tier=' + selectedTier"
                                   class="grail-cta-pill w-full py-4 font-bold text-xs uppercase tracking-wider text-center block transition active:scale-95">
                                    <span>Continue to Order →</span>
                                </a>

                                <div class="text-[11px] text-center text-[#1A1018]/50 mt-3 font-medium">
                                    You won't be charged until project milestone approval.
                                </div>
                            </div>

                        </div>

                        <!-- FREELANCER MINI PROFILE CARD -->
                        <div class="grail-card rounded-3xl bg-white border border-[#E6E2E8] p-6 shadow-xs space-y-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $vendorAvatar }}" alt="{{ $vendor?->shop_name }}" class="w-14 h-14 rounded-2xl object-cover border border-[#ECD2F3]">
                                <div>
                                    <h3 class="font-bold text-sm text-[#1A1018]">{{ $vendor?->shop_name }}</h3>
                                    <p class="text-xs text-[#623562] font-semibold">{{ $vendor?->city ?? 'All-India Verified Pro' }}</p>
                                    <div class="text-[11px] text-[#1A1018]/60 mt-0.5">⭐ {{ number_format($vendor?->rating ?? 5.0, 1) }} ({{ $vendor?->completed_projects ?? 0 }} orders)</div>
                                </div>
                            </div>

                            <p class="text-xs text-[#1A1018]/70 leading-relaxed">
                                {{ $vendor?->tagline ?? $vendor?->description }}
                            </p>

                            <div class="pt-3 border-t border-[#E6E2E8] flex items-center justify-between gap-2">
                                <a href="{{ route('services.provider', $vendor?->slug ?? '') }}" 
                                   class="flex-1 py-2.5 rounded-full bg-[#EFE9F2] hover:bg-[#ECD2F3]/60 text-[#1A1018] text-xs font-bold text-center transition">
                                    View Profile
                                </a>
                                <a href="https://wa.me/91{{ preg_replace('/[^0-9]/', '', $vendor?->phone ?? '9876543210') }}?text={{ urlencode('Hi, I saw your service ' . $service->title . ' on K71 Services.') }}" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="grail-btn-obsidian flex-1 py-2.5 text-xs font-bold text-center transition flex items-center justify-center gap-1.5">
                                    <span>Contact</span>
                                </a>
                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

        <!-- STICKY MOBILE BOTTOM ORDER BAR -->
        <div class="lg:hidden fixed bottom-0 inset-x-0 z-50 p-3 bg-[#0D0D0D]/95 backdrop-blur-xl border-t border-white/15 shadow-2xl flex items-center justify-between gap-3"
             style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            <div>
                <div class="grail-eyebrow" x-text="selectedTier.toUpperCase() + ' PACKAGE'"></div>
                <div class="text-lg font-bold text-[#ECD2F3]" x-text="'₹' + Number(prices[selectedTier]).toLocaleString('en-IN')"></div>
            </div>
            <a :href="'{{ route('services.order.checkout', $service) }}?tier=' + selectedTier"
               class="grail-cta-pill px-6 py-3 font-bold text-xs uppercase tracking-wider flex items-center gap-2 transition active:scale-95">
                <span>Continue to Order</span>
                <span>&rarr;</span>
            </a>
        </div>

    </div>
</x-layouts.services>
