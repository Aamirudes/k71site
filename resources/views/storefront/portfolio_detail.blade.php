@php
    $config = $config ?? $vendor->getStorefrontConfig($isPreview ?? false);
    $theme = $config['theme'] ?? [];
    $brand = $config['brand'] ?? [];
    $rawKey = strtolower(trim((string) ($config['preset'] ?? 'velora')));
    $presetKey = app(\App\Services\Storefront\StorefrontPresetService::class)->normalizePresetKey($rawKey);
    $isKineticPreset = $presetKey === 'kinetic';
    $isDarkPreset = in_array($presetKey, ['orbit', 'noire', 'nexus', 'elysium', 'kinetic'], true) || !empty($theme['dark_mode']);
    $headingFont = $theme['font_heading'] ?? 'Playfair Display';
    $bodyFont = $theme['font_body'] ?? 'Inter';
    $headingWeights = $headingFont === 'Cormorant Garamond' ? '300;400;500;600;700' : '400;600;700;800;900';
    $fontsQuery = str_replace(' ', '+', $headingFont) . ':wght@' . $headingWeights;
    if ($bodyFont !== $headingFont && $bodyFont !== 'Switzer') {
        $fontsQuery .= '&family=' . str_replace(' ', '+', $bodyFont) . ':wght@300;400;500;600;700';
    }
    $providerName = $brand['store_name'] ?? $vendor->shop_name;
    $isSubdomainHost = request()->attributes->has('current_vendor') && !request()->is('s/*');
    $homeUrl = $vendor->storefront_url . (!empty($isPreview) ? '?preview=1' : '');
@endphp

<x-layouts.public
    :title="$project->title . ' — ' . $providerName"
    :canonicalUrl="url()->current()"
    :metaDescription="Str::limit(strip_tags($project->summary ?: $project->description ?: $project->title), 160)"
    :ogImage="$project->cover_image_url"
    :fullWidth="true"
    :hideMainHeader="true"
    :hideFooter="true"
>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($isKineticPreset)
        <link rel="preconnect" href="https://api.fontshare.com" crossorigin>
        <link rel="stylesheet" href="https://api.fontshare.com/v2/css?f[]=switzer@300,400,500,600,700,800&display=swap">
    @endif
    <link href="https://fonts.googleapis.com/css2?family={{ $fontsQuery }}&family=Cormorant+Garamond:ital,wght@0,300;0,400;0,600;0,700;1,300&display=swap" rel="stylesheet">

    <style>
        :root {
            --sf-primary: {{ $theme['primary_color'] ?? ($isKineticPreset ? '#EFE9F2' : ($isDarkPreset ? '#ffffff' : '#18181b')) }};
            --sf-secondary: {{ $theme['secondary_color'] ?? ($isDarkPreset ? '#94a3b8' : '#71717a') }};
            --sf-accent: {{ $theme['accent_color'] ?? ($isKineticPreset ? '#ECD2F3' : ($isDarkPreset ? '#38bdf8' : '#c59b27')) }};
            --sf-bg: {{ $isKineticPreset ? '#000000' : ($isDarkPreset ? '#050711' : ($theme['bg_color'] ?? '#fafaf9')) }};
            --sf-surface: {{ $isKineticPreset ? '#121216' : ($isDarkPreset ? '#0c1228' : ($theme['surface_color'] ?? '#ffffff')) }};
            --sf-text: {{ $isKineticPreset ? '#EFE9F2' : ($isDarkPreset ? '#f8fafc' : ($theme['text_color'] ?? '#18181b')) }};
            --sf-font-heading: '{{ $headingFont }}', serif, sans-serif;
            --sf-font-body: '{{ $bodyFont }}', system-ui, -apple-system, sans-serif;
        }
        body {
            background-color: var(--sf-bg) !important;
            color: var(--sf-text) !important;
            font-family: var(--sf-font-body);
        }
        .font-heading { font-family: var(--sf-font-heading); }
    </style>

    @php
        $ps = $providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme);
        $portfolioUrlFn = fn (string $projectSlug) => $isSubdomainHost
            ? url('/portfolio/' . $projectSlug) . (!empty($isPreview) ? '?preview=1' : '')
            : route('storefront.portfolio.show', [$vendor->slug, $projectSlug]) . (!empty($isPreview) ? '?preview=1' : '');
    @endphp

    <div class="min-h-screen flex flex-col">
        <header class="border-b {{ $ps['dark'] ? 'border-white/10 bg-black/40' : 'border-stone-200 bg-white/80' }} backdrop-blur-xl sticky top-0 z-30">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                <a href="{{ $homeUrl }}" class="text-xs sm:text-sm font-bold inline-flex items-center gap-2 hover:underline">
                    <span>←</span>
                    <span>Back to {{ $providerName }}</span>
                </a>
                <a href="{{ $homeUrl }}#contact" class="px-4 py-2 text-xs {{ $ps['btn_primary'] }}">
                    Start Similar Project
                </a>
            </div>
        </header>

        <main class="flex-1 max-w-6xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 space-y-10">
            <article class="{{ $ps['card'] }} overflow-hidden">
                @if($project->cover_image_url)
                    <div class="aspect-[21/9] w-full overflow-hidden bg-black/20">
                        <img src="{{ $project->cover_image_url }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-6 sm:p-10 lg:p-12 space-y-8">
                    <div class="space-y-4">
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            @if($project->work_category)
                                <span class="px-3 py-1 {{ $ps['pill'] }}">{{ $project->work_category }}</span>
                            @endif
                            @if($project->completed_at)
                                <span class="{{ $ps['muted'] }}">Delivered {{ $project->completed_at->format('M Y') }}</span>
                            @endif
                            @if($project->status === 'draft')
                                <span class="px-3 py-1 rounded-full bg-amber-500/20 text-amber-500 font-bold">Draft Preview</span>
                            @endif
                        </div>

                        <h1 class="text-3xl sm:text-5xl {{ $ps['heading'] }} leading-tight">
                            {{ $project->title }}
                        </h1>

                        @if($project->summary)
                            <p class="text-base sm:text-xl {{ $ps['muted'] }} leading-relaxed max-w-3xl">
                                {{ $project->summary }}
                            </p>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @if($project->client_name)
                            <div class="p-4 {{ $ps['surface_alt'] }}">
                                <div class="text-[10px] uppercase font-bold {{ $ps['muted'] }}">Client</div>
                                <div class="text-sm font-bold mt-1">{{ $project->client_name }}</div>
                            </div>
                        @endif
                        @if($project->role)
                            <div class="p-4 {{ $ps['surface_alt'] }}">
                                <div class="text-[10px] uppercase font-bold {{ $ps['muted'] }}">Role</div>
                                <div class="text-sm font-bold mt-1">{{ $project->role }}</div>
                            </div>
                        @endif
                        @if($project->external_url)
                            <div class="p-4 {{ $ps['surface_alt'] }}">
                                <div class="text-[10px] uppercase font-bold {{ $ps['muted'] }}">Live Reference</div>
                                <a href="{{ $project->external_url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-bold underline mt-1 inline-block">
                                    Open Project ↗
                                </a>
                            </div>
                        @endif
                    </div>

                    @if($project->description)
                        <div class="prose max-w-none {{ $ps['dark'] ? 'prose-invert' : '' }} text-sm sm:text-base leading-relaxed whitespace-pre-line">
                            {{ $project->description }}
                        </div>
                    @endif

                    @if(!empty($project->deliverables_json) || !empty($project->skills_json))
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/80' }}">
                            @if(!empty($project->deliverables_json))
                                <div class="space-y-2.5">
                                    <h3 class="text-xs font-bold uppercase tracking-widest {{ $ps['muted'] }}">Key Deliverables</h3>
                                    <ul class="space-y-1.5 text-sm">
                                        @foreach((array)$project->deliverables_json as $del)
                                            <li class="flex items-start gap-2">
                                                <span class="text-emerald-500 font-bold">✓</span>
                                                <span>{{ $del }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(!empty($project->skills_json))
                                <div class="space-y-2.5">
                                    <h3 class="text-xs font-bold uppercase tracking-widest {{ $ps['muted'] }}">Skills &amp; Tools Applied</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach((array)$project->skills_json as $sk)
                                            <span class="px-3 py-1 text-xs font-semibold {{ $ps['pill'] }}">{{ $sk }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if(!empty($project->gallery_urls))
                        <div class="space-y-3 pt-4 border-t {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/80' }}">
                            <h3 class="text-xs font-bold uppercase tracking-widest {{ $ps['muted'] }}">Project Gallery</h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($project->gallery_urls as $imgUrl)
                                    <div class="overflow-hidden {{ $ps['radius'] }} border {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200' }}">
                                        <img src="{{ $imgUrl }}" alt="{{ $project->title }}" class="w-full h-64 object-cover">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($project->serviceProduct && $project->serviceProduct->status === 'active')
                        <div class="{{ $ps['surface_alt'] }} p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <span class="text-[10px] uppercase font-bold {{ $ps['muted'] }}">Related Bookable Service</span>
                                <h3 class="text-base sm:text-lg font-bold">{{ $project->serviceProduct->title }}</h3>
                            </div>
                            <a href="{{ route('services.show', $project->serviceProduct->slug) }}" class="px-5 py-2.5 text-xs text-center shrink-0 {{ $ps['btn_primary'] }}">
                                View Service &amp; Packages →
                            </a>
                        </div>
                    @endif
                </div>
            </article>

            @if($moreProjects->isNotEmpty())
                <section class="space-y-4">
                    <h2 class="text-xl sm:text-2xl {{ $ps['heading'] }}">More Case Studies</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        @foreach($moreProjects as $rel)
                            <a href="{{ $portfolioUrlFn($rel->slug) }}"
                               class="{{ $ps['card'] }} {{ $ps['card_hover'] }} p-5 space-y-2 block">
                                <div class="text-[11px] {{ $ps['muted'] }}">{{ $rel->work_category ?: 'Case Study' }}</div>
                                <div class="font-bold text-base">{{ $rel->title }}</div>
                                <div class="text-xs font-semibold hover:underline">Read case study →</div>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </main>

        @include('storefront.sections.service.footer', ['footerBaseUrl' => $homeUrl])
    </div>
</x-layouts.public>
