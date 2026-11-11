<x-layouts.services
    :title="$vendor->shop_name . ' — Provider Profile | K71 Services'"
    :metaDescription="'Hire ' . $vendor->shop_name . ' on K71 Services. ' . ($vendor->headline ?: ($vendor->tagline ?? $vendor->description))"
>
    @php
        $avatar = $vendor->logo_url;
        $banner = $vendor->banner_url;
        $initials = collect(explode(' ', trim($vendor->shop_name ?: 'K')))
            ->filter()
            ->take(2)
            ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
            ->implode('') ?: 'K';

        $contactPrefs = is_array($vendor->public_contact_preferences) ? $vendor->public_contact_preferences : [];
        $showPhone = !empty($contactPrefs['show_phone']);
        $rawPhone = $showPhone ? ($vendor->contact_phone ?: $vendor->phone) : null;
        $cleanPhone = $rawPhone ? preg_replace('/[^0-9]/', '', (string) $rawPhone) : null;
        if ($cleanPhone && strlen($cleanPhone) === 10) {
            $cleanPhone = '91' . $cleanPhone;
        }

        $displayRating = ($vendor->average_rating && $vendor->average_rating > 0)
            ? number_format((float) $vendor->average_rating, 1)
            : null;
        $expYears = $vendor->years_experience ?? $vendor->settings?->service_experience_years;
        $isWebsiteLive = $vendor->isWebsitePublished();
    @endphp

    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- GRAIL OBSIDIAN COVER BANNER & PROFILE HEADER -->
        <div class="grail-hero-band relative pb-10">
            <div class="relative h-48 sm:h-64 lg:h-72 w-full overflow-hidden bg-gradient-to-br from-[#1A1018] via-[#2D1833] to-[#0D0D0D]">
                @if($banner)
                    <img src="{{ $banner }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-cover opacity-65">
                @else
                    <div class="w-full h-full opacity-35 bg-[radial-gradient(circle_at_top_right,_#ECD2F3,_transparent_55%)]"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black via-black/45 to-transparent"></div>
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="relative -mt-20 sm:-mt-24 flex flex-col lg:flex-row items-start lg:items-end justify-between gap-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5">
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="{{ $vendor->shop_name }}"
                                 class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl object-cover border-4 border-[#ECD2F3] shadow-2xl shrink-0 bg-[#0D0D0D]">
                        @else
                            <div class="w-28 h-28 sm:w-36 sm:h-36 rounded-3xl border-4 border-[#ECD2F3] shadow-2xl shrink-0 bg-[#1A1018] text-[#ECD2F3] flex items-center justify-center text-3xl sm:text-4xl font-semibold tracking-wider" style="font-family: var(--font-grail-display);">
                                {{ $initials }}
                            </div>
                        @endif

                        <div class="space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-white/10 border border-white/20 text-[#EFE9F2] text-[10px] font-bold uppercase tracking-wider">
                                    {{ $vendor->provider_mode === 'agency' ? 'Agency Studio' : 'Independent Specialist' }}
                                </span>
                                @if($vendor->is_verified_pro)
                                    <span class="inline-flex items-center gap-1 px-3 py-0.5 rounded-full bg-[#ECD2F3] text-[#1A1018] text-[10px] font-bold uppercase tracking-wider">
                                        Verified Pro
                                    </span>
                                @endif
                                @if($vendor->availability_status)
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 border border-emerald-400/30 text-emerald-200 text-[10px] font-bold uppercase tracking-wider">
                                        {{ ucfirst($vendor->availability_status) }}
                                    </span>
                                @endif
                            </div>

                            <h1 class="text-2xl sm:text-4xl font-light text-white" style="font-family: var(--font-grail-display);">{{ $vendor->shop_name }}</h1>

                            <p class="text-xs sm:text-sm text-[#ECD2F3] font-semibold">
                                {{ $vendor->headline ?: ($vendor->tagline ?? 'Independent Creative & Technical Specialist') }}
                            </p>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-[#EFE9F2]/75 pt-1">
                                <span>{{ $vendor->city ?? 'India' }} · {{ ucfirst($vendor->work_mode ?: 'remote') }}</span>
                                <span class="text-[#C9A24A] font-semibold">
                                    @if($displayRating)
                                        ★ {{ $displayRating }}
                                    @else
                                        New Provider
                                    @endif
                                    <span class="text-[#EFE9F2]/65 font-normal">({{ $vendor->completed_projects ?? 0 }} Completed Orders)</span>
                                </span>
                                <span>Avg Response: {{ $vendor->response_time ?? ($vendor->response_time_minutes ? $vendor->response_time_minutes . ' mins' : '1 Hour') }}</span>
                                @if($vendor->settings?->service_working_hours)
                                    <span>{{ $vendor->settings->service_working_hours }}</span>
                                @endif
                                @if($expYears)
                                    <span>{{ $expYears }} Years Experience</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Primary Website + Direct Contact CTAs -->
                    <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                        @if($isWebsiteLive)
                            <a href="{{ $vendor->storefront_url }}"
                               class="grail-cta-pill px-6 py-3 font-bold text-xs uppercase tracking-wider transition text-center active:scale-95 flex items-center justify-center gap-2">
                                <span>Visit Official Website ↗</span>
                            </a>
                        @endif

                        @if($cleanPhone)
                            <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Hi ' . $vendor->shop_name . ', I would like to discuss a project on K71 Services.') }}"
                               target="_blank" rel="noopener noreferrer"
                               class="px-5 py-3 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs uppercase tracking-wider transition text-center">
                                WhatsApp Provider
                            </a>
                        @elseif($isWebsiteLive)
                            <a href="{{ $vendor->storefrontUrl([], 'contact') }}"
                               class="px-5 py-3 rounded-full bg-white/10 hover:bg-white/20 border border-white/20 text-white font-bold text-xs uppercase tracking-wider transition text-center">
                                Send Enquiry
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- CURVED EDITORIAL SHEET BODY: LEFT SIDEBAR BIO + RIGHT SERVICES, PORTFOLIO & REVIEWS -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-20 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    <!-- LEFT SIDEBAR (4 COLS): ABOUT, SKILLS, LANGUAGES, EXTERNAL LINKS -->
                    <div class="lg:col-span-4 space-y-6">

                        <!-- BIO & DESCRIPTION -->
                        <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-4">
                            <h3 class="grail-eyebrow !text-[#623562]">About {{ $vendor->provider_mode === 'agency' ? 'Studio' : 'Specialist' }}</h3>
                            <p class="text-xs sm:text-sm text-[#1A1018]/75 leading-relaxed whitespace-pre-line">
                                {{ $vendor->description ?: 'Verified K71 Services provider.' }}
                            </p>

                            <div class="pt-4 border-t border-[#E6E2E8] grid grid-cols-2 gap-4 text-center">
                                <div class="p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3]">
                                    <span class="text-xs text-[#1A1018]/55 block font-semibold">Starting Rate</span>
                                    <span class="text-base font-bold text-[#623562]">
                                        {{ $vendor->hourly_rate ? '₹' . number_format($vendor->hourly_rate) . '/hr' : 'Custom / Fixed' }}
                                    </span>
                                </div>
                                <div class="p-3 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3]">
                                    <span class="text-xs text-[#1A1018]/55 block font-semibold">Orders Done</span>
                                    <span class="text-base font-bold text-[#1A1018]">
                                        {{ $vendor->completed_projects ?? 0 }}
                                    </span>
                                </div>
                            </div>

                            @if($isWebsiteLive)
                                <a href="{{ $vendor->storefront_url }}"
                                   class="block w-full py-2.5 px-4 rounded-2xl bg-[#1A1018] hover:bg-[#623562] text-[#ECD2F3] text-xs font-bold text-center transition">
                                    Open Personal Portfolio Website ↗
                                </a>
                            @endif
                        </div>

                        <!-- SKILLS -->
                        @if(!empty($vendor->skills_json) && is_array($vendor->skills_json))
                            <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-3">
                                <h3 class="grail-eyebrow !text-[#623562]">Verified Skills</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($vendor->skills_json as $skill)
                                        <span class="px-3 py-1 rounded-full bg-[#F8EFFA] text-[#623562] border border-[#ECD2F3] text-xs font-semibold">
                                            {{ $skill }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- LANGUAGES -->
                        @if(!empty($vendor->languages_json) && is_array($vendor->languages_json))
                            <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-3">
                                <h3 class="grail-eyebrow !text-[#623562]">Languages</h3>
                                <ul class="space-y-1.5 text-xs text-[#1A1018]/75 font-medium">
                                    @foreach($vendor->languages_json as $lang)
                                        <li class="flex items-center gap-2">
                                            <span class="text-[#623562] font-bold">•</span>
                                            <span>{{ $lang }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- EXTERNAL PORTFOLIO / SOCIAL LINKS -->
                        @if(!empty($vendor->external_links) && is_array($vendor->external_links))
                            @php
                                $cleanLinks = array_filter($vendor->external_links);
                            @endphp
                            @if(!empty($cleanLinks))
                                <div class="grail-card p-6 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-2.5">
                                    <h3 class="grail-eyebrow !text-[#623562]">External Profiles</h3>
                                    @foreach($cleanLinks as $platform => $linkUrl)
                                        <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer"
                                           class="flex items-center justify-between px-3 py-2 rounded-xl bg-[#FAFAF8] border border-[#E6E2E8] hover:border-[#623562] text-xs font-semibold text-[#1A1018]">
                                            <span class="capitalize">{{ str_replace('_', ' ', $platform) }}</span>
                                            <span>↗</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                    </div>

                    <!-- RIGHT MAIN CONTENT (8 COLS): PORTFOLIO CASE STUDIES, ACTIVE SERVICES & REVIEWS -->
                    <div class="lg:col-span-8 space-y-10">

                        <!-- PORTFOLIO CASE STUDIES (Layer A -> Layer B Case Studies) -->
                        @if(isset($portfolioProjects) && $portfolioProjects->isNotEmpty())
                            <div>
                                <div class="flex items-center justify-between mb-6">
                                    <div>
                                        <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Selected Portfolio Case Studies</h2>
                                        <p class="text-xs text-[#1A1018]/60">Published client outcomes curated in this provider's studio</p>
                                    </div>
                                    @if($isWebsiteLive)
                                        <a href="{{ $vendor->storefrontUrl([], 'work') }}" class="text-xs font-bold text-[#623562] hover:underline">
                                            View Full Showcase ↗
                                        </a>
                                    @endif
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($portfolioProjects as $proj)
                                        @php
                                            $projUrl = $isWebsiteLive
                                                ? route('storefront.portfolio.show', [$vendor->slug, $proj->slug])
                                                : null;
                                        @endphp
                                        <div class="group grail-card rounded-3xl bg-white border border-[#E6E2E8] hover:border-[#623562]/45 hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                            <div class="aspect-[16/10] overflow-hidden bg-gradient-to-br from-[#1A1018] to-[#3B2142]">
                                                @if($proj->cover_image_url)
                                                    <img src="{{ $proj->cover_image_url }}" alt="{{ $proj->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center p-6 text-center text-[#ECD2F3] font-light text-lg" style="font-family: var(--font-grail-display);">
                                                        {{ $proj->title }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="p-5 flex-1 flex flex-col justify-between space-y-3">
                                                <div>
                                                    <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-[#623562] mb-1">
                                                        <span>{{ $proj->work_category ?: ($proj->category?->name ?? 'Case Study') }}</span>
                                                        @if($proj->client_name)
                                                            <span class="text-[#1A1018]/50">{{ $proj->client_name }}</span>
                                                        @endif
                                                    </div>
                                                    <h3 class="font-bold text-sm text-[#1A1018] leading-snug">
                                                        @if($projUrl)
                                                            <a href="{{ $projUrl }}" class="hover:text-[#623562] transition">{{ $proj->title }}</a>
                                                        @else
                                                            {{ $proj->title }}
                                                        @endif
                                                    </h3>
                                                    @if($proj->summary)
                                                        <p class="text-xs text-[#1A1018]/65 line-clamp-2 mt-1">{{ $proj->summary }}</p>
                                                    @endif
                                                </div>

                                                @if($projUrl)
                                                    <a href="{{ $projUrl }}" class="inline-flex items-center gap-1 text-xs font-bold text-[#623562] hover:text-[#1A1018]">
                                                        <span>Read Case Study</span>
                                                        <span>→</span>
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif(!empty($vendor->portfolio_items) && is_array($vendor->portfolio_items))
                            <div>
                                <div class="mb-6">
                                    <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Portfolio &amp; Work Samples</h2>
                                    <p class="text-xs text-[#1A1018]/60">Previous projects delivered by {{ $vendor->shop_name }}</p>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    @foreach($vendor->portfolio_items as $item)
                                        <div class="group grail-card rounded-2xl overflow-hidden bg-white border border-[#E6E2E8] shadow-xs">
                                            <div class="aspect-[4/3] overflow-hidden bg-[#FAFAF8]">
                                                <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['title'] ?? 'Portfolio Item' }}"
                                                     class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                            </div>
                                            <div class="p-3">
                                                <div class="font-bold text-xs text-[#1A1018] truncate">{{ $item['title'] ?? 'Sample Project' }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- SERVICES / GIGS OFFERED -->
                        <div>
                            <div class="flex items-center justify-between mb-6">
                                <div>
                                    <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Services Offered by {{ $vendor->shop_name }}</h2>
                                    <p class="text-xs text-[#1A1018]/60">Order directly with fixed packages and escrow protection</p>
                                </div>
                            </div>

                            @if($services->isEmpty())
                                <div class="grail-card p-8 rounded-3xl bg-white border border-[#E6E2E8] text-center text-xs text-[#1A1018]/55">
                                    No active services published by this provider at the moment.
                                </div>
                            @else
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                    @foreach($services as $svc)
                                        @php
                                            $primary = $svc->primary_image_url;
                                            $basic = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                                        @endphp
                                        <div class="group grail-card rounded-3xl bg-white border border-[#E6E2E8] hover:border-[#623562]/45 hover:shadow-md transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                            <div class="aspect-video overflow-hidden bg-[#FAFAF8]">
                                                <a href="{{ route('services.show', $svc->slug) }}">
                                                    <img src="{{ $primary }}" alt="{{ $svc->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                                </a>
                                            </div>

                                            <div class="p-5 flex-1 flex flex-col justify-between">
                                                <div>
                                                    <div class="grail-eyebrow !text-[#623562] mb-1">
                                                        {{ $svc->category?->name ?? 'Professional Service' }}
                                                    </div>
                                                    <h3 class="font-bold text-sm text-[#1A1018] group-hover:text-[#623562] transition line-clamp-2 leading-snug">
                                                        <a href="{{ route('services.show', $svc->slug) }}">
                                                            {{ $svc->title }}
                                                        </a>
                                                    </h3>
                                                </div>

                                                <div class="mt-4 pt-3 border-t border-[#E6E2E8] flex items-center justify-between">
                                                    <span class="text-xs text-[#1A1018]/50 font-medium">Starting at</span>
                                                    <span class="text-base font-bold text-[#1A1018]">
                                                        ₹{{ number_format($basic ? $basic->price : ($svc->base_price / 100)) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- VERIFIED CLIENT REVIEWS & PROVIDER REPLIES -->
                        @if(isset($reviews) && $reviews->isNotEmpty())
                            <div>
                                <div class="mb-6">
                                    <h2 class="text-2xl sm:text-3xl font-light text-[#1A1018] tracking-tight" style="font-family: var(--font-grail-display);">Verified Client Reviews</h2>
                                    <p class="text-xs text-[#1A1018]/60">Authentic feedback from completed K71 Services engagements</p>
                                </div>

                                <div class="space-y-4">
                                    @foreach($reviews as $rev)
                                        <div class="grail-card p-5 rounded-3xl bg-white border border-[#E6E2E8] space-y-3">
                                            <div class="flex flex-wrap items-center justify-between gap-2">
                                                <div class="flex items-center gap-2.5">
                                                    <div class="w-8 h-8 rounded-full bg-[#1A1018] text-[#ECD2F3] font-bold text-xs flex items-center justify-center shrink-0">
                                                        {{ strtoupper(substr($rev->user?->name ?? 'C', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <div class="text-xs font-bold text-[#1A1018]">{{ $rev->user?->name ?? 'Verified Client' }}</div>
                                                        @if($rev->product)
                                                            <div class="text-[11px] text-[#1A1018]/55">{{ $rev->product->title }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="text-xs font-bold text-[#C9A24A]">
                                                    ★ {{ number_format((float) $rev->rating, 1) }} / 5.0
                                                </div>
                                            </div>

                                            <p class="text-xs sm:text-sm text-[#1A1018]/80 leading-relaxed">{{ $rev->body ?? $rev->comment }}</p>

                                            @if($rev->vendor_reply)
                                                <div class="p-3.5 rounded-2xl bg-[#F8EFFA] border border-[#ECD2F3] text-xs space-y-1">
                                                    <div class="font-bold text-[#623562]">Official Reply from {{ $vendor->shop_name }}:</div>
                                                    <p class="text-[#1A1018]/80">{{ $rev->vendor_reply }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                    </div>

                </div>
            </div>
        </div>

    </div>
</x-layouts.services>
