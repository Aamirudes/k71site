<x-layouts.public 
    :title="$vendor->shop_name . ' — Freelancer Profile | K71 Services'"
    :metaDescription="'Hire ' . $vendor->shop_name . ' on K71 Services. ' . ($vendor->tagline ?? $vendor->description)"
>
    @php
        $avatar = $vendor->logo ? format_image_url($vendor->logo) : 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=300&q=80';
        $banner = $vendor->banner ? format_image_url($vendor->banner) : 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?auto=format&fit=crop&w=1200&q=80';
        $cleanPhone = preg_replace('/[^0-9]/', '', $vendor->phone ?? '9876543210');
        if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
    @endphp

    <div class="min-h-screen bg-gradient-to-b from-[#faf5ff] via-white to-[#faf5ff] text-zinc-900 pb-20">

        <!-- COVER BANNER & PROFILE HEADER -->
        <div class="relative">
            <div class="h-48 sm:h-64 lg:h-80 w-full overflow-hidden bg-purple-900">
                <img src="{{ $banner }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-cover opacity-80">
            </div>

            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative -mt-20 sm:-mt-24 pb-8 border-b border-purple-100 flex flex-col sm:flex-row items-start sm:items-end justify-between gap-6">
                    <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5">
                        <img src="{{ $avatar }}" alt="{{ $vendor->shop_name }}" 
                             class="w-32 h-32 sm:w-40 sm:h-40 rounded-3xl object-cover border-4 border-white shadow-xl shrink-0 bg-white">
                        
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl sm:text-3xl font-black text-zinc-950">{{ $vendor->shop_name }}</h1>
                                @if($vendor->is_verified_pro)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-100 text-purple-800 text-[11px] font-black uppercase">
                                        ✓ Verified Pro
                                    </span>
                                @endif
                            </div>

                            <p class="text-xs sm:text-sm text-purple-900 font-bold">
                                {{ $vendor->tagline ?? 'Independent Creative & Technical Specialist' }}
                            </p>

                            <div class="flex flex-wrap items-center gap-4 text-xs text-zinc-500 pt-1">
                                <span>📍 {{ $vendor->city ?? 'India' }}</span>
                                <span>⭐ {{ number_format($vendor->rating ?? 5.0, 1) }} ({{ $vendor->completed_projects ?? 0 }} Completed Orders)</span>
                                <span>⚡ Avg Response: {{ $vendor->response_time ?? '1 Hour' }}</span>
                                @if($vendor->settings?->service_working_hours)
                                    <span>🕒 {{ $vendor->settings->service_working_hours }}</span>
                                @endif
                                @if($vendor->settings?->service_experience_years)
                                    <span>🏅 {{ $vendor->settings->service_experience_years }} Years Experience</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Direct Contact CTA -->
                    <div class="flex items-center gap-3 w-full sm:w-auto">
                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ urlencode('Hi ' . $vendor->shop_name . ', I would like to discuss a project on K71 Services.') }}" 
                           target="_blank" rel="noopener noreferrer"
                           class="w-full sm:w-auto px-6 py-3 rounded-2xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs uppercase tracking-wider transition shadow-md shadow-purple-500/25 text-center active:scale-95 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Contact Provider</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN BODY: LEFT SIDEBAR BIO + RIGHT SERVICES & PORTFOLIO -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                <!-- LEFT SIDEBAR (4 COLS): ABOUT, SKILLS, LANGUAGES, STATS -->
                <div class="lg:col-span-4 space-y-6">

                    <!-- BIO & DESCRIPTION -->
                    <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-4">
                        <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider">About Freelancer</h3>
                        <p class="text-xs sm:text-sm text-zinc-600 leading-relaxed whitespace-pre-line">
                            {{ $vendor->description }}
                        </p>

                        <div class="pt-4 border-t border-purple-50 grid grid-cols-2 gap-4 text-center">
                            <div class="p-3 rounded-2xl bg-purple-50/50">
                                <span class="text-xs text-zinc-400 block font-semibold">Rate</span>
                                <span class="text-base font-black text-purple-700">
                                    {{ $vendor->hourly_rate ? '₹' . number_format($vendor->hourly_rate) . '/hr' : 'Fixed' }}
                                </span>
                            </div>
                            <div class="p-3 rounded-2xl bg-purple-50/50">
                                <span class="text-xs text-zinc-400 block font-semibold">Orders Done</span>
                                <span class="text-base font-black text-purple-700">
                                    {{ $vendor->completed_projects ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- SKILLS -->
                    @if(!empty($vendor->skills_json) && is_array($vendor->skills_json))
                        <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-3">
                            <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider">Verified Skills</h3>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($vendor->skills_json as $skill)
                                    <span class="px-3 py-1 rounded-xl bg-purple-100/70 text-purple-800 text-xs font-bold">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- LANGUAGES -->
                    @if(!empty($vendor->languages_json) && is_array($vendor->languages_json))
                        <div class="p-6 rounded-3xl bg-white/80 backdrop-blur-md border border-purple-100 shadow-sm space-y-3">
                            <h3 class="text-sm font-black text-purple-900 uppercase tracking-wider">Languages</h3>
                            <ul class="space-y-1.5 text-xs text-zinc-600 font-medium">
                                @foreach($vendor->languages_json as $lang)
                                    <li class="flex items-center gap-2">
                                        <span class="text-purple-600 font-bold">•</span>
                                        <span>{{ $lang }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                </div>

                <!-- RIGHT MAIN CONTENT (8 COLS): ACTIVE SERVICES & PORTFOLIO -->
                <div class="lg:col-span-8 space-y-10">

                    <!-- SERVICES / GIGS OFFERED -->
                    <div>
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-xl font-black text-zinc-950 tracking-tight">Services Offered by {{ $vendor->shop_name }}</h2>
                                <p class="text-xs text-zinc-500">Order directly with fixed packages and instant kickoff</p>
                            </div>
                        </div>

                        @if($services->isEmpty())
                            <div class="p-8 rounded-3xl bg-white/70 border border-purple-100 text-center text-xs text-zinc-500">
                                No active services published by this provider at the moment.
                            </div>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                @foreach($services as $svc)
                                    @php
                                        $primary = $svc->primary_image_url;
                                        $basic = $svc->gigPackages->firstWhere('tier', 'basic') ?? $svc->gigPackages->first();
                                    @endphp
                                    <div class="group rounded-3xl bg-white/90 backdrop-blur-md border border-purple-100 hover:border-purple-300 hover:shadow-[0_10px_25px_rgba(124,58,237,0.08)] transition-all duration-300 overflow-hidden flex flex-col justify-between">
                                        <div class="aspect-video overflow-hidden bg-purple-50">
                                            <a href="{{ route('services.show', $svc->slug) }}">
                                                <img src="{{ $primary }}" alt="{{ $svc->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                            </a>
                                        </div>

                                        <div class="p-5 flex-1 flex flex-col justify-between">
                                            <div>
                                                <div class="text-[11px] font-bold text-purple-700 uppercase tracking-wider mb-1">
                                                    {{ $svc->category?->name ?? 'Professional Service' }}
                                                </div>
                                                <h3 class="font-bold text-sm text-zinc-950 group-hover:text-purple-700 transition line-clamp-2 leading-snug">
                                                    <a href="{{ route('services.show', $svc->slug) }}">
                                                        {{ $svc->title }}
                                                    </a>
                                                </h3>
                                            </div>

                                            <div class="mt-4 pt-3 border-t border-purple-50 flex items-center justify-between">
                                                <span class="text-xs text-zinc-400 font-medium">Starting at</span>
                                                <span class="text-base font-black text-purple-700">
                                                    ₹{{ number_format($basic ? $basic->price : ($svc->base_price / 100)) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- PORTFOLIO SAMPLES -->
                    @if(!empty($vendor->portfolio_items) && is_array($vendor->portfolio_items))
                        <div>
                            <div class="mb-6">
                                <h2 class="text-xl font-black text-zinc-950 tracking-tight">Portfolio &amp; Work Samples</h2>
                                <p class="text-xs text-zinc-500">Previous projects delivered with excellence</p>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($vendor->portfolio_items as $item)
                                    <div class="group rounded-2xl overflow-hidden bg-white border border-purple-100 shadow-xs">
                                        <div class="aspect-[4/3] overflow-hidden bg-purple-50">
                                            <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['title'] ?? 'Portfolio Item' }}" 
                                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                        </div>
                                        <div class="p-3">
                                            <div class="font-bold text-xs text-zinc-900 truncate">{{ $item['title'] ?? 'Sample Project' }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                </div>

            </div>
        </div>

    </div>
</x-layouts.public>
