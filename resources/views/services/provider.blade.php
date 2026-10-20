<x-layouts.public 
    :title="$vendor->shop_name . ' — Professional Services Profile | K71'"
    :metaDescription="'View verified services, rates, service area, and book appointments with ' . $vendor->shop_name . ' on K71 Services.'"
>
    <!-- PROVIDER HERO HEADER -->
    <header class="relative rounded-3xl bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-transparent border border-zinc-200/80 p-6 sm:p-8 mb-8 shadow-xs">
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <!-- Avatar / Logo -->
            <div class="relative w-24 h-24 sm:w-28 sm:h-28 rounded-2xl bg-white border border-zinc-200 p-1 shrink-0 shadow-xs flex items-center justify-center overflow-hidden">
                @if($vendor->logo)
                    <img src="{{ asset('storage/' . $vendor->logo) }}" alt="{{ $vendor->shop_name }}" class="w-full h-full object-cover rounded-xl">
                @else
                    <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                @endif
                <div class="absolute -bottom-1 -right-1 bg-emerald-600 text-white rounded-full p-1 text-[10px] font-bold shadow-xs">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                </div>
            </div>

            <!-- Info -->
            <div class="flex-1 text-center md:text-left">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700 text-xs font-semibold tracking-wide mb-2">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    <span>Verified Service Professional</span>
                </div>

                <h1 class="text-2xl sm:text-4xl font-bold text-zinc-950 tracking-tight">
                    {{ $vendor->shop_name }}
                </h1>

                @if($vendor->description)
                    <p class="text-xs sm:text-sm text-zinc-600 mt-2 max-w-2xl leading-relaxed">
                        {{ $vendor->description }}
                    </p>
                @endif

                <!-- Badges Row -->
                <div class="flex flex-wrap items-center justify-center md:justify-start gap-2.5 mt-4 text-xs text-zinc-700">
                    @if($vendor->city)
                        <div class="flex items-center gap-1.5 bg-white border border-zinc-200 px-3 py-1.5 rounded-xl shadow-xs">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>{{ $vendor->city }} {{ $vendor->state ? ', ' . $vendor->state : '' }}</span>
                        </div>
                    @endif

                    @if($vendor->settings?->service_working_hours)
                        <div class="flex items-center gap-1.5 bg-white border border-zinc-200 px-3 py-1.5 rounded-xl shadow-xs">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $vendor->settings->service_working_hours }}</span>
                        </div>
                    @endif

                    @if($vendor->settings?->service_radius_km)
                        <div class="flex items-center gap-1.5 bg-white border border-zinc-200 px-3 py-1.5 rounded-xl shadow-xs">
                            <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <span>Service Radius: Up to {{ $vendor->settings->service_radius_km }} km</span>
                        </div>
                    @endif

                    @if($vendor->settings?->service_experience_years)
                        <div class="flex items-center gap-1.5 bg-white border border-zinc-200 px-3 py-1.5 rounded-xl shadow-xs">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <span>{{ $vendor->settings->service_experience_years }} Years Experience</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action CTAs -->
            @php
                $cleanPhone = preg_replace('/[^0-9]/', '', $vendor->phone ?? $vendor->contact_phone ?? '919876543210');
                if (strlen($cleanPhone) === 10) { $cleanPhone = '91' . $cleanPhone; }
                $whatsappUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode("Hi {$vendor->shop_name}, I saw your profile on K71 Services and would like to inquire about booking a service.");
            @endphp
            <div class="shrink-0 flex items-center gap-3">
                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
                   class="px-5 py-2.5 rounded-2xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition shadow-xs active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    <span>Chat on WhatsApp</span>
                </a>
            </div>
        </div>
    </header>

    <!-- SERVICES LIST -->
    <div class="pb-16">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-zinc-950">Services Offered</h2>
                <p class="text-xs text-zinc-500 mt-0.5">Choose a service to schedule a doorstep appointment</p>
            </div>
            <a href="{{ route('services.index') }}" class="text-xs text-indigo-600 hover:text-indigo-700 font-semibold transition">
                ← Browse all professionals
            </a>
        </div>

        @if($services->isEmpty())
            <div class="p-12 text-center rounded-3xl bg-white border border-zinc-200 max-w-md mx-auto my-8 shadow-xs">
                <div class="w-12 h-12 rounded-full bg-zinc-100 text-zinc-400 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-zinc-900 mb-1">No services listed yet</h3>
                <p class="text-xs text-zinc-500">This professional hasn't published active services yet.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($services as $item)
                    @php
                        $duration = $item->getCustomField('duration', '60 mins');
                        $warranty = $item->getCustomField('service_warranty');
                    @endphp
                    <div class="rounded-2xl bg-white border border-zinc-200/80 hover:border-zinc-300 p-5 transition flex flex-col justify-between shadow-xs hover:shadow-md">
                        <div>
                            <div class="flex items-center justify-between text-xs text-zinc-500 mb-2">
                                <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold border border-indigo-100">
                                    {{ $item->category?->name ?? 'Service' }}
                                </span>
                                @if($duration)
                                    <span>{{ $duration }}</span>
                                @endif
                            </div>

                            <h3 class="text-base font-bold text-zinc-900 mb-1.5">
                                <a href="{{ route('product.show', $item->slug) }}" class="hover:text-indigo-600 transition">
                                    {{ $item->title }}
                                </a>
                            </h3>

                            <p class="text-xs text-zinc-600 line-clamp-3 mb-4 leading-relaxed">
                                {{ $item->description }}
                            </p>

                            @if($warranty)
                                <div class="text-[11px] text-emerald-700 font-medium mb-3">
                                    Warranty: {{ $warranty }}
                                </div>
                            @endif
                        </div>

                        <div class="pt-4 border-t border-zinc-100 flex items-center justify-between gap-3">
                            <div>
                                <div class="text-[10px] text-zinc-400 uppercase font-semibold">Service Fee</div>
                                <div class="text-lg font-bold text-zinc-950">
                                    ₹{{ number_format($item->base_price / 100, 2) }}
                                </div>
                            </div>

                            <a href="{{ route('product.show', $item->slug) }}" 
                               class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition shadow-xs active:scale-95 flex items-center gap-1.5">
                                <span>Book Now</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.public>
