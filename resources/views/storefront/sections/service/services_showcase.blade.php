@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $services = $providerSite['services'] ?? collect();
@endphp

@if(($providerSite['section_visibility']['services'] ?? true) && ($services->isNotEmpty() || !empty($providerSite['is_preview'])))
<section id="services" data-section-id="services" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] {{ $ps['muted'] }} block mb-1">
                Bookable Packages &amp; Custom Scopes
            </span>
            <h2 class="text-2xl sm:text-4xl {{ $ps['heading'] }}">
                {{ $providerSite['nav_labels']['services'] ?? 'Services' }}
            </h2>
        </div>
        <a href="#contact" class="text-xs font-bold hover:underline">Need a tailored quote? Request custom scope →</a>
    </div>

    @if($services->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($services as $service)
                @php
                    $startingPrice = $service->gigPackages->min('price') ?? $service->base_price;
                    $minDelivery = $service->gigPackages->min('delivery_days');
                    $coverImg = $service->images->first()?->path ? \App\Services\ImageService::url($service->images->first()->path, 800) : null;
                @endphp
                <article class="{{ $ps['card'] }} {{ $ps['card_hover'] }} overflow-hidden flex flex-col justify-between">
                    <div>
                        <a href="{{ route('services.show', $service->slug) }}" class="block group">
                            <div class="aspect-[16/10] w-full overflow-hidden {{ $ps['dark'] ? 'bg-black/40' : 'bg-stone-100' }}">
                                @if($coverImg)
                                    <img src="{{ $coverImg }}" alt="{{ $service->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                @else
                                    <div class="w-full h-full flex items-center justify-center p-6 text-center"
                                         style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                                        <span class="text-white/95 font-bold text-sm uppercase tracking-wider">{{ $service->category?->name ?? 'Service Offering' }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="p-5 sm:p-6 space-y-2.5">
                                <div class="flex items-center justify-between gap-2 text-[11px]">
                                    <span class="px-2.5 py-0.5 {{ $ps['pill'] }}">{{ $service->category?->name ?? 'Service' }}</span>
                                    @if($minDelivery)
                                        <span class="{{ $ps['muted'] }}">⏱️ From {{ $minDelivery }} {{ Str::plural('day', $minDelivery) }}</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold leading-snug group-hover:underline">{{ $service->title }}</h3>
                                <p class="text-xs sm:text-sm {{ $ps['muted'] }} line-clamp-2 leading-relaxed">
                                    {{ Str::limit(strip_tags($service->description), 130) }}
                                </p>
                            </div>
                        </a>

                        @if($service->gigPackages->isNotEmpty())
                            <div class="px-5 sm:px-6 pb-4">
                                <div class="grid grid-cols-{{ min(3, $service->gigPackages->count()) }} gap-2">
                                    @foreach($service->gigPackages->take(3) as $pkg)
                                        <div class="p-2 {{ $ps['surface_alt'] }} text-center">
                                            <div class="text-[10px] uppercase font-bold {{ $ps['muted'] }}">{{ ucfirst($pkg->tier) }}</div>
                                            <div class="text-xs font-black mt-0.5">₹{{ number_format((float)$pkg->price) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="px-5 sm:px-6 py-4 border-t {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }} flex items-center justify-between gap-3">
                        <div>
                            <span class="text-[10px] uppercase font-bold {{ $ps['muted'] }} block">Starting at</span>
                            <span class="text-lg font-black">₹{{ number_format((float)$startingPrice) }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="{{ route('services.show', $service->slug) }}"
                               class="px-4 py-2 text-xs transition {{ $ps['btn_primary'] }}">
                                Book Service
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="{{ $ps['surface_alt'] }} p-8 text-center space-y-2">
            <p class="text-sm font-bold">Custom Consulting &amp; Direct Enquiries Open</p>
            <p class="text-xs {{ $ps['muted'] }}">Publish your service listings from Services Studio or invite clients to submit a project brief below.</p>
            @if(!empty($providerSite['is_preview']))
                <a href="{{ route('vendor.services.create') }}" class="inline-block mt-2 px-4 py-2 text-xs {{ $ps['btn_primary'] }}">Create Service Listing →</a>
            @endif
        </div>
    @endif
</section>
@endif
