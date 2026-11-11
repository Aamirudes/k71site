@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $isSubdomainHost = $isSubdomainHost ?? (request()->attributes->has('current_vendor') && !request()->is('s/*'));
    $portfolioUrlFn = $portfolioUrlFn ?? (fn (string $projectSlug) => $isSubdomainHost
        ? url('/portfolio/' . $projectSlug) . (!empty($isPreview) ? '?preview=1' : '')
        : route('storefront.portfolio.show', [$vendor->slug, $projectSlug]) . (!empty($isPreview) ? '?preview=1' : ''));
    $projects = $providerSite['portfolio_projects'] ?? collect();
    $workCats = $projects->pluck('work_category')->filter()->unique()->values();
@endphp

@if(($providerSite['section_visibility']['portfolio'] ?? true) && ($projects->isNotEmpty() || !empty($providerSite['is_preview'])))
<section id="portfolio" data-section-id="portfolio" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ activeCat: 'all' }">
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 mb-6">
        <div>
            <span class="text-[11px] font-bold uppercase tracking-[0.2em] {{ $ps['muted'] }} block mb-1">
                Selected Works &amp; Case Studies
            </span>
            <h2 class="text-2xl sm:text-4xl {{ $ps['heading'] }}">
                {{ $providerSite['nav_labels']['work'] ?? 'Portfolio' }}
            </h2>
        </div>

        @if($workCats->count() > 1)
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="activeCat = 'all'"
                        :class="activeCat === 'all' ? '{{ $ps['accent_badge'] }}' : '{{ $ps['pill'] }}'"
                        class="px-3.5 py-1.5 text-xs font-bold transition cursor-pointer">
                    All ({{ $projects->count() }})
                </button>
                @foreach($workCats as $catName)
                    <button type="button" @click="activeCat = '{{ addslashes($catName) }}'"
                            :class="activeCat === '{{ addslashes($catName) }}' ? '{{ $ps['accent_badge'] }}' : '{{ $ps['pill'] }}'"
                            class="px-3.5 py-1.5 text-xs font-bold transition cursor-pointer">
                        {{ $catName }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    @if($projects->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($projects as $proj)
                <article x-show="activeCat === 'all' || activeCat === '{{ addslashes($proj->work_category ?? '') }}'"
                         class="{{ $ps['card'] }} {{ $ps['card_hover'] }} overflow-hidden flex flex-col justify-between">
                    <a href="{{ $portfolioUrlFn($proj->slug) }}" class="block group">
                        <div class="aspect-[16/10] w-full overflow-hidden {{ $ps['dark'] ? 'bg-black/40' : 'bg-stone-100' }}">
                            @if($proj->cover_image_url)
                                <img src="{{ $proj->cover_image_url }}" alt="{{ $proj->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            @else
                                <div class="w-full h-full flex items-center justify-center p-6 text-center"
                                     style="background: linear-gradient(135deg, var(--sf-primary, #18181b), var(--sf-accent, #c59b27));">
                                    <span class="text-white/90 font-bold text-sm uppercase tracking-wider">{{ $proj->work_category ?: 'Case Study' }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 sm:p-6 space-y-3">
                            <div class="flex flex-wrap items-center gap-2 text-[11px]">
                                @if($proj->work_category)
                                    <span class="px-2.5 py-0.5 {{ $ps['pill'] }}">{{ $proj->work_category }}</span>
                                @endif
                                @if($proj->client_name)
                                    <span class="{{ $ps['muted'] }}">Client: {{ $proj->client_name }}</span>
                                @endif
                                @if($proj->status === 'draft')
                                    <span class="px-2 py-0.5 bg-amber-500/20 text-amber-500 rounded-full font-bold">Draft</span>
                                @endif
                            </div>

                            <h3 class="text-lg sm:text-xl font-bold leading-snug group-hover:underline">
                                {{ $proj->title }}
                            </h3>

                            @if($proj->summary || $proj->description)
                                <p class="text-xs sm:text-sm {{ $ps['muted'] }} line-clamp-2 leading-relaxed">
                                    {{ $proj->summary ?: Str::limit(strip_tags($proj->description), 140) }}
                                </p>
                            @endif

                            @if(!empty($proj->skills_json))
                                <div class="flex flex-wrap gap-1.5 pt-1">
                                    @foreach(array_slice((array)$proj->skills_json, 0, 3) as $skill)
                                        <span class="px-2 py-0.5 text-[10px] {{ $ps['surface_alt'] }}">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="px-5 sm:px-6 pb-5 pt-2 flex items-center justify-between border-t {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }}">
                        <a href="{{ $portfolioUrlFn($proj->slug) }}" class="text-xs font-bold hover:underline inline-flex items-center gap-1">
                            <span>View Case Study</span>
                            <span aria-hidden="true">→</span>
                        </a>
                        @if($proj->role)
                            <span class="text-[11px] {{ $ps['muted'] }}">{{ $proj->role }}</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @else
        <div class="{{ $ps['surface_alt'] }} p-8 text-center space-y-2">
            <p class="text-sm font-bold">No portfolio projects published yet</p>
            <p class="text-xs {{ $ps['muted'] }}">Add case studies from your Provider Dashboard to showcase verified work samples here.</p>
            <a href="{{ route('vendor.portfolio.index') }}" class="inline-block mt-2 px-4 py-2 text-xs {{ $ps['btn_primary'] }}">Add Portfolio Project →</a>
        </div>
    @endif
</section>
@endif
