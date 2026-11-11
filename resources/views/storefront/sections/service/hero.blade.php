@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $id = $providerSite['identity'] ?? [];
    $metrics = $providerSite['metrics'] ?? [];
    $contact = $providerSite['contact'] ?? [];
    $banner = $id['banner_url'] ?? null;
    $logo = $id['logo_url'] ?? null;
@endphp

@if(!empty($providerSite['is_preview']))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
        <div class="px-4 py-2.5 rounded-2xl bg-amber-500/15 border border-amber-500/40 text-amber-600 dark:text-amber-300 text-xs font-semibold flex flex-wrap items-center justify-between gap-2">
            <span>👁️ Preview Mode — Only visible to you. Visitors see 404 until published.</span>
            <a href="{{ route('vendor.website.index') }}" class="underline font-bold">Return to Website Hub →</a>
        </div>
    </div>
@endif

<section id="overview" data-section-id="hero" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 sm:pt-10">
    <div class="{{ $ps['card'] }} overflow-hidden relative p-6 sm:p-10 lg:p-14">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-center">
            <div class="lg:col-span-7 space-y-6">
                <div class="flex flex-wrap items-center gap-2.5 text-[11px] font-semibold">
                    <span class="px-3 py-1 {{ $ps['pill'] }}">
                        {{ $id['provider_mode_label'] ?? 'Independent Specialist' }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 {{ $ps['pill'] }}">
                        <span class="w-2 h-2 rounded-full {{ ($id['availability'] ?? 'available') === 'available' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                        {{ $id['availability_label'] ?? 'Available for New Projects' }}
                    </span>
                    @if(!empty($contact['city']))
                        <span class="px-3 py-1 {{ $ps['pill'] }}">📍 {{ $contact['city'] }}</span>
                    @endif
                    <span class="px-3 py-1 {{ $ps['pill'] }}">{{ $id['work_mode_label'] ?? 'Remote Worldwide' }}</span>
                </div>

                <div class="space-y-3">
                    <h1 class="text-3xl sm:text-5xl lg:text-6xl leading-[1.06] {{ $ps['heading'] }}">
                        {{ $id['headline'] ?: ($id['name'] ?? $vendor->shop_name) }}
                    </h1>
                    @if(!empty($id['subtitle']) && $id['subtitle'] !== ($id['headline'] ?? ''))
                        <p class="text-base sm:text-xl font-medium opacity-90 max-w-2xl">
                            {{ $id['subtitle'] }}
                        </p>
                    @endif
                    @if(!empty($id['bio']))
                        <p class="text-sm sm:text-base {{ $ps['muted'] }} max-w-2xl leading-relaxed line-clamp-4">
                            {{ $id['bio'] }}
                        </p>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="#contact" class="px-6 py-3.5 text-xs sm:text-sm inline-flex items-center gap-2 transition {{ $ps['btn_primary'] }}">
                        <span>{{ $id['cta_primary_text'] ?? 'Start a Project' }}</span>
                        <span aria-hidden="true">→</span>
                    </a>
                    @if(($metrics['portfolio_count'] ?? 0) > 0)
                        <a href="#portfolio" class="px-6 py-3.5 text-xs sm:text-sm inline-flex items-center gap-2 transition {{ $ps['btn_secondary'] }}">
                            <span>{{ $id['cta_secondary_text'] ?? 'View Work' }}</span>
                        </a>
                    @elseif(($metrics['services_count'] ?? 0) > 0)
                        <a href="#services" class="px-6 py-3.5 text-xs sm:text-sm inline-flex items-center gap-2 transition {{ $ps['btn_secondary'] }}">
                            <span>Explore Services</span>
                        </a>
                    @endif
                </div>

                {{-- Genuine Verified Proof Metrics ONLY (never fabricated) --}}
                @php
                    $hasRealMetrics = ($metrics['completed_projects'] ?? 0) > 0
                        || ($metrics['reviews_count'] ?? 0) > 0
                        || !empty($id['years_experience'])
                        || ($metrics['services_count'] ?? 0) > 0;
                @endphp
                @if($hasRealMetrics)
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/80' }}">
                        @if(($metrics['completed_projects'] ?? 0) > 0)
                            <div class="p-3 {{ $ps['surface_alt'] }}">
                                <div class="text-lg sm:text-xl font-black">{{ $metrics['completed_projects'] }}</div>
                                <div class="text-[11px] {{ $ps['muted'] }}">Delivered Projects</div>
                            </div>
                        @endif
                        @if(($metrics['average_rating'] ?? null) !== null && ($metrics['reviews_count'] ?? 0) > 0)
                            <div class="p-3 {{ $ps['surface_alt'] }}">
                                <div class="text-lg sm:text-xl font-black">★ {{ number_format((float)$metrics['average_rating'], 1) }}</div>
                                <div class="text-[11px] {{ $ps['muted'] }}">{{ $metrics['reviews_count'] }} Client {{ Str::plural('Review', $metrics['reviews_count']) }}</div>
                            </div>
                        @endif
                        @if(!empty($id['years_experience']))
                            <div class="p-3 {{ $ps['surface_alt'] }}">
                                <div class="text-lg sm:text-xl font-black">{{ $id['years_experience'] }}+ Yrs</div>
                                <div class="text-[11px] {{ $ps['muted'] }}">Experience</div>
                            </div>
                        @endif
                        @if(!empty($id['response_time']))
                            <div class="p-3 {{ $ps['surface_alt'] }}">
                                <div class="text-sm sm:text-base font-black truncate">{{ $id['response_time'] }}</div>
                                <div class="text-[11px] {{ $ps['muted'] }}">Typical Response</div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="lg:col-span-5">
                <div class="{{ $ps['surface_alt'] }} overflow-hidden p-4 sm:p-5 space-y-4">
                    @if($banner)
                        <div class="aspect-[4/3] w-full overflow-hidden {{ $ps['radius'] }}">
                            <img src="{{ $banner }}" alt="{{ $id['name'] ?? $vendor->shop_name }}" class="w-full h-full object-cover">
                        </div>
                    @elseif(($providerSite['portfolio_projects'] ?? collect())->isNotEmpty() && $providerSite['portfolio_projects']->first()->cover_image_url)
                        @php $leadProj = $providerSite['portfolio_projects']->first(); @endphp
                        <div class="aspect-[4/3] w-full overflow-hidden {{ $ps['radius'] }}">
                            <img src="{{ $leadProj->cover_image_url }}" alt="{{ $leadProj->title }}" class="w-full h-full object-cover">
                        </div>
                    @else
                        <div class="aspect-[4/3] w-full flex flex-col items-center justify-center p-8 text-center {{ $ps['radius'] }}"
                             style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $id['name'] ?? $vendor->shop_name }}" class="h-20 w-auto object-contain mb-3 rounded-2xl bg-white/95 p-2 shadow-md">
                            @else
                                <div class="w-20 h-20 rounded-full bg-white/15 backdrop-blur-md border border-white/30 flex items-center justify-center text-white text-2xl font-black mb-3">
                                    {{ $id['initials'] ?? 'K7' }}
                                </div>
                            @endif
                            <div class="text-white font-bold text-lg">{{ $id['name'] ?? $vendor->shop_name }}</div>
                            <div class="text-white/80 text-xs mt-1">{{ $id['provider_mode_label'] ?? 'Verified K71 Provider' }}</div>
                        </div>
                    @endif

                    <div class="flex items-center justify-between gap-3 pt-1">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($logo)
                                <img src="{{ $logo }}" alt="{{ $id['name'] ?? $vendor->shop_name }}" class="w-11 h-11 rounded-2xl object-cover border {{ $ps['dark'] ? 'border-white/20' : 'border-stone-200' }} shrink-0 bg-white">
                            @else
                                <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-sm shrink-0 {{ $ps['accent_badge'] }}">
                                    {{ $id['initials'] ?? 'K7' }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <div class="text-sm font-bold truncate">{{ $id['name'] ?? $vendor->shop_name }}</div>
                                <div class="text-xs {{ $ps['muted'] }} truncate">{{ $vendor->business_category ?: 'Professional Services' }}</div>
                            </div>
                        </div>
                        @if(!empty($id['hourly_rate']))
                            <span class="px-3 py-1 text-xs font-bold shrink-0 {{ $ps['pill'] }}">
                                From ₹{{ number_format((float)$id['hourly_rate']) }}/hr
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
