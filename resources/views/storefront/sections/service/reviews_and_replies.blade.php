@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $reviews = $providerSite['reviews'] ?? collect();
    $metrics = $providerSite['metrics'] ?? [];
@endphp

@if(($providerSite['section_visibility']['reviews'] ?? true) && ($reviews->isNotEmpty() || !empty($providerSite['is_preview'])))
<section id="reviews" data-section-id="reviews" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] {{ $ps['muted'] }} block mb-1">
                Verified Engagements Only
            </span>
            <h2 class="text-2xl sm:text-4xl {{ $ps['heading'] }}">
                {{ $providerSite['nav_labels']['reviews'] ?? 'Client Reviews' }}
            </h2>
        </div>

        @if($reviews->isNotEmpty() && ($metrics['average_rating'] ?? null) !== null)
            <div class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold {{ $ps['pill'] }}">
                <span class="text-amber-500">★</span>
                <span>{{ number_format((float)$metrics['average_rating'], 1) }} / 5.0</span>
                <span class="opacity-70">({{ $metrics['reviews_count'] }} Verified)</span>
            </div>
        @endif
    </div>

    @if($reviews->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($reviews as $rev)
                <article class="{{ $ps['card'] }} p-5 sm:p-6 flex flex-col justify-between space-y-4">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-amber-500 text-sm tracking-tight" aria-label="{{ $rev['rating'] }} out of 5 stars">
                                {{ str_repeat('★', max(1, min(5, (int)$rev['rating']))) }}{{ str_repeat('☆', max(0, 5 - (int)$rev['rating'])) }}
                            </span>
                            <span class="px-2.5 py-0.5 text-[10px] font-semibold {{ $ps['pill'] }}">
                                {{ $rev['source'] }}
                            </span>
                        </div>

                        <p class="text-xs sm:text-sm leading-relaxed {{ $ps['muted'] }}">
                            “{{ $rev['body'] }}”
                        </p>
                    </div>

                    <div class="space-y-3 pt-3 border-t {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full flex items-center justify-center font-black text-xs shrink-0 {{ $ps['accent_badge'] }}">
                                {{ $rev['reviewer_initial'] }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-bold truncate">{{ $rev['reviewer_name'] }}</div>
                                <div class="text-[11px] {{ $ps['muted'] }} truncate">{{ $rev['service_title'] }}</div>
                            </div>
                        </div>

                        @if(!empty($rev['vendor_reply']))
                            <div class="p-3 {{ $ps['surface_alt'] }} text-xs space-y-1">
                                <div class="font-bold text-[10px] uppercase tracking-wider {{ $ps['muted'] }}">
                                    Reply from {{ $providerSite['identity']['name'] ?? $vendor->shop_name }}
                                </div>
                                <p class="leading-relaxed">{{ $rev['vendor_reply'] }}</p>
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="{{ $ps['surface_alt'] }} p-8 text-center">
            <p class="text-sm font-bold">Verified client feedback appears here after completed orders &amp; contracts.</p>
        </div>
    @endif
</section>
@endif
