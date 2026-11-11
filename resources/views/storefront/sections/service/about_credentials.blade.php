@php
    $presetKey = $presetKey ?? ($providerSite['preset_key'] ?? 'velora');
    $ps = $ps ?? ($providerSite['tokens'] ?? \App\Services\Services\ServiceWebsitePresenter::presetTokens($presetKey, $theme ?? ($providerSite['theme'] ?? [])));
    $id = $providerSite['identity'] ?? [];
    $skills = $providerSite['skills'] ?? [];
    $languages = $providerSite['languages'] ?? [];
    $links = $providerSite['external_links'] ?? [];
    $contact = $providerSite['contact'] ?? [];
@endphp

@if(($providerSite['section_visibility']['about'] ?? true))
<section id="about" data-section-id="about" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="{{ $ps['card'] }} p-6 sm:p-10 lg:p-12">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12">
            <div class="lg:col-span-7 space-y-5">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-[0.2em] {{ $ps['muted'] }} block mb-1">
                        Background &amp; Expertise
                    </span>
                    <h2 class="text-2xl sm:text-4xl {{ $ps['heading'] }}">
                        About {{ $id['name'] ?? $vendor->shop_name }}
                    </h2>
                </div>

                @if(!empty($id['bio']))
                    <div class="text-sm sm:text-base leading-relaxed {{ $ps['muted'] }} whitespace-pre-line">
                        {{ $id['bio'] }}
                    </div>
                @endif

                @if(!empty($skills))
                    <div class="space-y-2 pt-2">
                        <h3 class="text-xs font-bold uppercase tracking-widest {{ $ps['muted'] }}">Core Capabilities &amp; Stack</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach($skills as $skill)
                                <span class="px-3 py-1.5 text-xs font-semibold {{ $ps['pill'] }}">{{ $skill }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="lg:col-span-5 space-y-4">
                <div class="{{ $ps['surface_alt'] }} p-5 sm:p-6 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-widest {{ $ps['muted'] }}">Practice Profile</h3>

                    <dl class="space-y-3 text-xs sm:text-sm">
                        <div class="flex items-center justify-between gap-4 py-1.5 border-b {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }}">
                            <dt class="{{ $ps['muted'] }}">Practice Type</dt>
                            <dd class="font-bold">{{ $id['provider_mode_label'] ?? 'Specialist' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 py-1.5 border-b {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }}">
                            <dt class="{{ $ps['muted'] }}">Collaboration</dt>
                            <dd class="font-bold">{{ $id['work_mode_label'] ?? 'Remote' }}</dd>
                        </div>
                        @if(!empty($contact['city']))
                            <div class="flex items-center justify-between gap-4 py-1.5 border-b {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }}">
                                <dt class="{{ $ps['muted'] }}">Based In</dt>
                                <dd class="font-bold">{{ $contact['city'] }}</dd>
                            </div>
                        @endif
                        @if(!empty($id['years_experience']))
                            <div class="flex items-center justify-between gap-4 py-1.5 border-b {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }}">
                                <dt class="{{ $ps['muted'] }}">Experience</dt>
                                <dd class="font-bold">{{ $id['years_experience'] }} Years</dd>
                            </div>
                        @endif
                        @if(!empty($languages))
                            <div class="flex items-center justify-between gap-4 py-1.5">
                                <dt class="{{ $ps['muted'] }}">Languages</dt>
                                <dd class="font-bold text-right">{{ implode(', ', $languages) }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if(!empty($links))
                        <div class="pt-3 border-t {{ $ps['dark'] ? 'border-white/10' : 'border-stone-200/70' }} flex flex-wrap gap-2">
                            @foreach($links as $label => $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener noreferrer"
                                   class="px-3 py-1.5 text-xs font-bold inline-flex items-center gap-1 {{ $ps['btn_secondary'] }}">
                                    <span>{{ ucfirst($label) }}</span>
                                    <span aria-hidden="true">↗</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endif
