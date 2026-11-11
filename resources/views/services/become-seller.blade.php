<x-layouts.services 
    title="Become a K71 Service Provider — Personal Website & Marketplace Onboarding"
    metaDescription="Launch your own K71 service provider website, claim a custom subdomain, choose from 10+ premium presets, and get hired on K71 Services."
>
    @php
        $stepsMeta = [
            1 => ['num' => 1, 'title' => 'Identity & Expertise', 'sub' => 'Individual or agency profile'],
            2 => ['num' => 2, 'title' => 'Website Subdomain', 'sub' => 'Claim your .k71 domain'],
            3 => ['num' => 3, 'title' => 'Choose Design Preset', 'sub' => '10+ signature themes'],
            4 => ['num' => 4, 'title' => 'Portfolio Setup', 'sub' => 'Hero, links & case study'],
            5 => ['num' => 5, 'title' => 'Publish Live', 'sub' => 'Checklist & launch'],
        ];
        $activeStep = $step ?? 1;
        $isPublished = $vendor?->isWebsitePublished() ?? false;
    @endphp

    <div class="min-h-screen w-full selection:bg-[#ECD2F3] selection:text-[#1A1018]">

        <!-- HERO HEADER -->
        <section class="grail-hero-band py-8 sm:py-12 px-4 sm:px-6 lg:px-8 border-b border-white/10">
            <div class="max-w-5xl mx-auto text-center space-y-3">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 border border-white/15 text-[#ECD2F3] text-[11px] font-bold uppercase tracking-[0.14em]">
                    <span>✨ K71 Services 2.0 · Personal Website + Marketplace Studio</span>
                </div>
                <h1 class="text-3xl sm:text-5xl font-light text-white tracking-tight" style="font-family: var(--font-grail-display);">
                    Become a K71 Service Provider
                </h1>
                <p class="text-xs sm:text-sm text-[#EFE9F2]/75 max-w-2xl mx-auto font-normal leading-relaxed">
                    Get your own independent portfolio website on a custom subdomain, showcase your best work across 10+ luxury design presets, offer fixed packages, and bid on custom client briefs.
                </p>

                <!-- 5-STEP PROGRESS STEPPER -->
                <div class="pt-6 grid grid-cols-2 sm:grid-cols-5 gap-2.5 text-left">
                    @foreach($stepsMeta as $num => $meta)
                        @php
                            $isCurrent = $activeStep === $num;
                            $isDone = ($vendor && ($vendor->onboarding_step ?? 1) > $num) || ($vendor && $isPublished);
                            $canJump = (bool) $vendor;
                        @endphp
                        <a href="{{ $canJump ? route('services.onboarding', ['step' => $num]) : '#' }}"
                           class="group rounded-2xl p-3 border transition {{ $isCurrent ? 'bg-white text-[#1A1018] border-white shadow-lg' : ($isDone ? 'bg-white/12 text-white border-emerald-400/40 hover:bg-white/20' : 'bg-white/5 text-white/65 border-white/10') }} {{ !$canJump && !$isCurrent ? 'pointer-events-none opacity-60' : '' }}">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-[10px] font-extrabold uppercase tracking-wider {{ $isCurrent ? 'text-[#623562]' : 'text-[#ECD2F3]' }}">
                                    Step {{ $num }}
                                </span>
                                @if($isDone && !$isCurrent)
                                    <span class="text-emerald-300 text-xs font-bold">✓</span>
                                @endif
                            </div>
                            <div class="text-xs font-bold truncate">{{ $meta['title'] }}</div>
                            <div class="text-[10px] {{ $isCurrent ? 'text-[#1A1018]/65' : 'text-white/50' }} truncate">{{ $meta['sub'] }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- MAIN WORKSPACE SHEET -->
        <div class="grail-sheet bg-[#F6F6F6] text-[#1A1018] rounded-t-[28px] sm:rounded-t-[36px] py-8 sm:py-12 pb-24 px-4 sm:px-6 lg:px-8 shadow-[0_-18px_48px_rgba(0,0,0,0.45)]">
            <div class="max-w-5xl mx-auto space-y-6">

                @if(session('success'))
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('info'))
                    <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-900 text-xs sm:text-sm font-medium">
                        {{ session('info') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-medium">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                        <div class="font-bold">Please fix the following before continuing:</div>
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @guest
                    <div class="rounded-3xl bg-white border border-[#E6E2E8] p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
                        <div>
                            <h2 class="text-lg font-bold text-[#1A1018]">Sign in or create your K71 account first</h2>
                            <p class="text-xs text-[#1A1018]/65 mt-1">Your personal portfolio website, subdomain, and client dashboard connect directly to your single K71 identity.</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <a href="{{ route('login', ['intended' => route('services.onboarding')]) }}" class="px-5 py-2.5 rounded-xl border border-[#E6E2E8] text-xs font-bold hover:bg-slate-50">Sign In</a>
                            <a href="{{ route('register') }}" class="grail-btn-obsidian px-5 py-2.5 text-xs uppercase tracking-wider">Create Account →</a>
                        </div>
                    </div>
                @endguest

                {{-- STEP 1: IDENTITY --}}
                @if($activeStep === 1)
                    <form action="{{ route('services.onboarding.save') }}" method="POST" enctype="multipart/form-data"
                          class="grail-card p-6 sm:p-10 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-6">
                        @csrf
                        <input type="hidden" name="step" value="1">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-[#E6E2E8]">
                            <div>
                                <span class="grail-eyebrow !text-[#623562]">Step 1 of 5</span>
                                <h2 class="text-xl sm:text-2xl font-bold text-[#1A1018] mt-1">Create Your Provider Identity</h2>
                            </div>
                            <span class="text-xs text-[#1A1018]/60">Saved safely to your private draft profile</span>
                        </div>

                        <!-- Individual vs Agency Selector -->
                        <div x-data="{ mode: '{{ old('provider_mode', $vendor?->provider_mode ?? 'individual') }}' }" class="space-y-2">
                            <label class="block text-xs font-bold text-[#1A1018]/80">Account Type *</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <label @click="mode = 'individual'"
                                       :class="mode === 'individual' ? 'border-[#1A1018] bg-[#FAF6FC] ring-2 ring-[#1A1018]/10' : 'border-[#E6E2E8] bg-white hover:bg-slate-50'"
                                       class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition">
                                    <input type="radio" name="provider_mode" value="individual" x-model="mode" class="mt-1">
                                    <div>
                                        <div class="text-sm font-bold text-[#1A1018]">Individual Freelancer / Specialist</div>
                                        <div class="text-[11px] text-[#1A1018]/60">Personal portfolio website in your name or craft.</div>
                                    </div>
                                </label>
                                <label @click="mode = 'agency'"
                                       :class="mode === 'agency' ? 'border-[#1A1018] bg-[#FAF6FC] ring-2 ring-[#1A1018]/10' : 'border-[#E6E2E8] bg-white hover:bg-slate-50'"
                                       class="flex items-start gap-3 p-4 rounded-2xl border cursor-pointer transition">
                                    <input type="radio" name="provider_mode" value="agency" x-model="mode" class="mt-1">
                                    <div>
                                        <div class="text-sm font-bold text-[#1A1018]">Creative Studio / Service Agency</div>
                                        <div class="text-[11px] text-[#1A1018]/60">Agency showcase for teams, studios, and local businesses.</div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Public Display / Brand Name *</label>
                                <input type="text" name="shop_name" value="{{ old('shop_name', $vendor?->shop_name) }}" required
                                       placeholder="e.g. Arjun Verma · Pixel Studio"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Professional Headline *</label>
                                <input type="text" name="headline" value="{{ old('headline', $vendor?->headline ?? $vendor?->tagline) }}" required
                                       placeholder="e.g. Product Designer & Full-Stack Laravel Architect"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">About You / Agency Introduction *</label>
                            <textarea name="description" rows="4" required
                                      placeholder="Share your expertise, industries you serve, achievements, and what clients gain by working with you..."
                                      class="grail-input-light w-full p-4 text-xs sm:text-sm">{{ old('description', $vendor?->description) }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Core Skills & Specialties (Comma-separated) *</label>
                                @php
                                    $skillsVal = old('skills');
                                    if (!$skillsVal && $vendor && is_array($vendor->skills_json)) {
                                        $skillsVal = implode(', ', $vendor->skills_json);
                                    }
                                @endphp
                                <input type="text" name="skills" value="{{ $skillsVal }}" required
                                       placeholder="e.g. Figma, UI/UX, React, Laravel, Branding"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Work Mode *</label>
                                <select name="work_mode" class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                                    @php $wm = old('work_mode', $vendor?->work_mode ?? 'remote'); @endphp
                                    <option value="remote" {{ $wm === 'remote' ? 'selected' : '' }}>Remote (Worldwide / Anywhere)</option>
                                    <option value="hybrid" {{ $wm === 'hybrid' ? 'selected' : '' }}>Hybrid (Remote & Local Visits)</option>
                                    <option value="onsite" {{ $wm === 'onsite' ? 'selected' : '' }}>On-Site / Local City Service</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">City / Base Location</label>
                                <input type="text" name="city" value="{{ old('city', $vendor?->city ?? 'Bengaluru') }}"
                                       placeholder="e.g. Bengaluru, Mumbai, Jaipur"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Starting Hourly Rate (₹)</label>
                                <input type="number" name="hourly_rate" value="{{ old('hourly_rate', $vendor?->hourly_rate) }}" min="0" step="50"
                                       placeholder="e.g. 1500"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Years of Experience</label>
                                <input type="number" name="years_experience" value="{{ old('years_experience', $vendor?->years_experience ?? 3) }}" min="0" max="60"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Languages Spoken</label>
                                @php
                                    $langsVal = old('languages');
                                    if (!$langsVal && $vendor && is_array($vendor->languages_json)) {
                                        $langsVal = implode(', ', $vendor->languages_json);
                                    }
                                @endphp
                                <input type="text" name="languages" value="{{ $langsVal ?? 'English, Hindi' }}"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Business Email (Optional)</label>
                                <input type="email" name="contact_email" value="{{ old('contact_email', $vendor?->contact_email ?? auth()->user()?->email) }}"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Profile Photo / Studio Logo</label>
                                <input type="file" name="logo" accept="image/*"
                                       class="block w-full text-xs text-[#1A1018]/75 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-[#1A1018] file:text-white file:text-xs file:font-bold">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E6E2E8] flex items-center justify-end gap-3">
                            <button type="submit" class="grail-btn-obsidian px-8 py-3.5 text-xs uppercase tracking-wider">
                                Save Identity &amp; Choose Subdomain →
                            </button>
                        </div>
                    </form>

                {{-- STEP 2: SUBDOMAIN --}}
                @elseif($activeStep === 2)
                    <form action="{{ route('services.onboarding.save') }}" method="POST"
                          x-data="{
                              slug: '{{ old('subdomain', $suggestedSlug) }}',
                              status: 'idle',
                              message: '',
                              available: false,
                              timer: null,
                              check() {
                                  clearTimeout(this.timer);
                                  const cleaned = (this.slug || '').toLowerCase().replace(/[^a-z0-9-]/g, '');
                                  this.slug = cleaned;
                                  if (cleaned.length < 3) {
                                      this.status = 'invalid';
                                      this.available = false;
                                      this.message = 'Enter at least 3 characters.';
                                      return;
                                  }
                                  this.status = 'checking';
                                  this.timer = setTimeout(() => {
                                      fetch('{{ route('services.check_subdomain') }}?slug=' + encodeURIComponent(cleaned))
                                          .then(r => r.json())
                                          .then(data => {
                                              this.available = !!data.available;
                                              this.status = data.available ? 'ok' : 'taken';
                                              this.message = data.message || '';
                                          })
                                          .catch(() => { this.status = 'idle'; });
                                  }, 280);
                              }
                          }"
                          x-init="if (slug.length >= 3) check()"
                          class="grail-card p-6 sm:p-10 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-6">
                        @csrf
                        <input type="hidden" name="step" value="2">

                        <div class="pb-4 border-b border-[#E6E2E8]">
                            <span class="grail-eyebrow !text-[#623562]">Step 2 of 5</span>
                            <h2 class="text-xl sm:text-2xl font-bold text-[#1A1018] mt-1">Claim Your Personal Website Subdomain</h2>
                            <p class="text-xs text-[#1A1018]/65 mt-1">This becomes your permanent branded URL on K71's wildcard domain infrastructure.</p>
                        </div>

                        <div class="rounded-2xl bg-[#FAF6FC] border border-[#E6DCEF] p-5 space-y-3">
                            <label class="block text-xs font-bold text-[#1A1018]">Choose Website Address *</label>
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                                <div class="flex flex-1 items-center rounded-xl bg-white border border-[#D4CADB] overflow-hidden focus-within:ring-2 focus-within:ring-[#623562]">
                                    <span class="pl-4 pr-1 text-xs font-semibold text-[#1A1018]/45">https://</span>
                                    <input type="text" name="subdomain" x-model="slug" @input="check()" required
                                           maxlength="30" placeholder="arjun"
                                           class="w-full py-3 px-1 text-sm font-bold text-[#1A1018] bg-transparent focus:outline-none">
                                    <span class="pr-4 pl-2 text-xs font-bold text-[#623562] bg-slate-50 py-3 border-l border-[#E6E2E8]">.{{ $domain }}</span>
                                </div>
                                <button type="button" @click="check()" class="px-4 py-3 rounded-xl border border-[#D4CADB] bg-white text-xs font-bold hover:bg-slate-50">
                                    Check Availability
                                </button>
                            </div>

                            <div class="text-xs font-semibold flex items-center gap-2 min-h-[20px]">
                                <span x-show="status === 'checking'" class="text-slate-500">Checking availability...</span>
                                <span x-show="status === 'ok'" class="text-emerald-700" x-text="'✓ ' + message"></span>
                                <span x-show="status === 'taken' || status === 'invalid'" class="text-rose-600" x-text="'✕ ' + message"></span>
                            </div>

                            <p class="text-[11px] text-[#1A1018]/60">
                                Preview fallback URL: <code class="px-1.5 py-0.5 rounded bg-white border border-[#E6E2E8]">{{ url('/s') }}/<span x-text="slug || 'yourname'"></span></code>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-[#E6E2E8] flex items-center justify-between gap-3">
                            <a href="{{ route('services.onboarding', ['step' => 1]) }}" class="px-5 py-3 rounded-xl border border-[#E6E2E8] text-xs font-bold hover:bg-slate-50">
                                ← Back to Identity
                            </a>
                            <button type="submit" class="grail-btn-obsidian px-8 py-3.5 text-xs uppercase tracking-wider">
                                Confirm Subdomain &amp; Choose Design →
                            </button>
                        </div>
                    </form>

                {{-- STEP 3: CHOOSE FROM 10+ EXISTING SIGNATURE PRESETS --}}
                @elseif($activeStep === 3)
                    <form action="{{ route('services.onboarding.save') }}" method="POST"
                          x-data="{ chosen: '{{ old('preset_key', $selectedPreset) }}' }"
                          class="grail-card p-6 sm:p-10 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-6">
                        @csrf
                        <input type="hidden" name="step" value="3">
                        <input type="hidden" name="preset_key" x-model="chosen">

                        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 pb-4 border-b border-[#E6E2E8]">
                            <div>
                                <span class="grail-eyebrow !text-[#623562]">Step 3 of 5 · 10 Signature Website Themes</span>
                                <h2 class="text-xl sm:text-2xl font-bold text-[#1A1018] mt-1">Choose Your Website Design Preset</h2>
                                <p class="text-xs text-[#1A1018]/65 mt-1">You can switch presets anytime later without losing your portfolio, services, or reviews.</p>
                            </div>
                            <a href="{{ $vendor->storefrontUrl(['preview' => 1]) }}" target="_blank" class="px-4 py-2 rounded-xl border border-[#E6E2E8] text-xs font-bold hover:bg-slate-50 shrink-0">
                                ↗ Preview Current Draft
                            </a>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($presets as $key => $preset)
                                <div @click="chosen = '{{ $key }}'"
                                     :class="chosen === '{{ $key }}' ? 'ring-2 ring-[#623562] border-[#623562] shadow-lg' : 'border-[#E6E2E8] hover:border-[#1A1018]/40'"
                                     class="rounded-2xl border p-4 cursor-pointer transition bg-white flex flex-col justify-between gap-4">
                                    <!-- Authentic Visual Mini-Preview Card built from Preset Swatches & Fonts -->
                                    <div class="rounded-xl p-4 border border-black/10 relative overflow-hidden"
                                         style="background: {{ $preset['preview_bg'] }}; color: {{ $preset['preview_text'] }};">
                                        <div class="flex items-center justify-between text-[10px] uppercase tracking-widest opacity-75 mb-3">
                                            <span>{{ $vendor?->slug }}.{{ $domain }}</span>
                                            <span class="px-2 py-0.5 rounded-full font-bold"
                                                  style="background: {{ $preset['preview_accent'] }}; color: {{ $preset['preview_bg'] === '#121214' || $preset['preview_bg'] === '#07080f' || $preset['preview_bg'] === '#0a0b0e' || $preset['preview_bg'] === '#060913' ? '#09090b' : '#ffffff' }};">
                                                {{ $preset['badge'] }}
                                            </span>
                                        </div>
                                        <div class="text-lg font-bold leading-snug mb-1" style="font-family: '{{ $preset['display_font'] }}', serif;">
                                            {{ $vendor?->shop_name ?: $preset['name'] }}
                                        </div>
                                        <div class="text-[11px] opacity-80 line-clamp-1 mb-3" style="font-family: '{{ $preset['body_font'] }}', sans-serif;">
                                            {{ $vendor?->headline ?: $preset['tagline'] }}
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div class="h-8 rounded-lg border border-black/10 p-1.5 flex items-center justify-between text-[9px] font-semibold"
                                                 style="background: {{ $preset['preview_card_bg'] }};">
                                                <span>Portfolio</span>
                                                <span class="w-2 h-2 rounded-full" style="background: {{ $preset['preview_accent'] }};"></span>
                                            </div>
                                            <div class="h-8 rounded-lg border border-black/10 p-1.5 flex items-center justify-between text-[9px] font-semibold"
                                                 style="background: {{ $preset['preview_card_bg'] }};">
                                                <span>Services</span>
                                                <span>{{ $preset['display_font'] }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-sm font-bold text-[#1A1018]">{{ $preset['name'] }}</h3>
                                                <span x-show="chosen === '{{ $key }}'" class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                                                    Selected ✓
                                                </span>
                                            </div>
                                            <p class="text-[11px] text-[#1A1018]/65 mt-0.5">{{ $preset['best_for'] }}</p>
                                        </div>
                                        <span class="w-5 h-5 rounded-full border-2 flex items-center justify-center shrink-0 mt-0.5"
                                              :class="chosen === '{{ $key }}' ? 'border-[#623562] bg-[#623562] text-white' : 'border-slate-300'">
                                            <span x-show="chosen === '{{ $key }}'" class="text-[10px]">✓</span>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="pt-4 border-t border-[#E6E2E8] flex items-center justify-between gap-3">
                            <a href="{{ route('services.onboarding', ['step' => 2]) }}" class="px-5 py-3 rounded-xl border border-[#E6E2E8] text-xs font-bold hover:bg-slate-50">
                                ← Back to Subdomain
                            </a>
                            <button type="submit" class="grail-btn-obsidian px-8 py-3.5 text-xs uppercase tracking-wider">
                                Save Preset &amp; Set Up Portfolio →
                            </button>
                        </div>
                    </form>

                {{-- STEP 4: PORTFOLIO WEBSITE SETUP --}}
                @elseif($activeStep === 4)
                    <form action="{{ route('services.onboarding.save') }}" method="POST" enctype="multipart/form-data"
                          class="grail-card p-6 sm:p-10 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-6">
                        @csrf
                        <input type="hidden" name="step" value="4">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-[#E6E2E8]">
                            <div>
                                <span class="grail-eyebrow !text-[#623562]">Step 4 of 5</span>
                                <h2 class="text-xl sm:text-2xl font-bold text-[#1A1018] mt-1">Customize Your Website &amp; Portfolio</h2>
                            </div>
                            <a href="{{ $vendor->storefrontUrl(['preview' => 1]) }}" target="_blank" class="px-4 py-2 rounded-xl border border-[#E6E2E8] text-xs font-bold hover:bg-slate-50 shrink-0">
                                👁 Live Website Draft Preview
                            </a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Hero Headline</label>
                                <input type="text" name="hero_headline"
                                       value="{{ old('hero_headline', $effectiveConfig['service_site']['hero_headline'] ?? $vendor?->headline) }}"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Hero Subtitle / Positioning</label>
                                <input type="text" name="hero_subtitle"
                                       value="{{ old('hero_subtitle', $effectiveConfig['service_site']['hero_subtitle'] ?? $vendor?->tagline) }}"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">About Section Heading</label>
                                <input type="text" name="about_title"
                                       value="{{ old('about_title', $effectiveConfig['service_site']['about_title'] ?? 'About Me') }}"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Primary CTA Button Text</label>
                                <input type="text" name="cta_primary_text"
                                       value="{{ old('cta_primary_text', $effectiveConfig['service_site']['cta_primary_text'] ?? 'Start a Project') }}"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">Hero / Cover Banner Image</label>
                                <input type="file" name="banner" accept="image/*"
                                       class="block w-full text-xs text-[#1A1018]/75 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-[#1A1018] file:text-white file:text-xs file:font-bold">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">External Website / Dribbble / GitHub URL</label>
                                <input type="url" name="website_link" value="{{ old('website_link', $vendor?->external_links['website'] ?? '') }}"
                                       placeholder="https://dribbble.com/yourhandle"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-[#1A1018]/80 mb-1">LinkedIn Profile URL</label>
                                <input type="url" name="linkedin_link" value="{{ old('linkedin_link', $vendor?->external_links['linkedin'] ?? '') }}"
                                       placeholder="https://linkedin.com/in/yourprofile"
                                       class="grail-input-light w-full px-4 py-3 text-xs sm:text-sm">
                            </div>
                        </div>

                        <!-- Optional Quick First Portfolio Case Study -->
                        <div class="rounded-2xl bg-[#FAF6FC] border border-[#E6DCEF] p-5 space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-sm font-bold text-[#1A1018]">Add Your First Portfolio Case Study (Optional)</h3>
                                    <p class="text-[11px] text-[#1A1018]/65">You already have {{ $portfolioCount }} project(s) and {{ $servicesCount }} service(s). Add one featured work sample right here or manage more later from your dashboard.</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <input type="text" name="portfolio_title" value="{{ old('portfolio_title') }}"
                                       placeholder="Case study title (e.g. FinTech SaaS Redesign)"
                                       class="grail-input-light w-full px-4 py-2.5 text-xs sm:text-sm bg-white">
                                <input type="text" name="portfolio_category" value="{{ old('portfolio_category') }}"
                                       placeholder="Discipline (e.g. Brand Identity, Web App)"
                                       class="grail-input-light w-full px-4 py-2.5 text-xs sm:text-sm bg-white">
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <textarea name="portfolio_summary" rows="2" placeholder="Briefly describe challenge, approach & outcome..."
                                          class="grail-input-light w-full p-3 text-xs sm:text-sm bg-white">{{ old('portfolio_summary') }}</textarea>
                                <div>
                                    <label class="block text-[11px] font-bold text-[#1A1018]/75 mb-1">Case Study Cover Image</label>
                                    <input type="file" name="portfolio_cover" accept="image/*"
                                           class="block w-full text-xs text-[#1A1018]/75 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-[#623562] file:text-white file:text-xs file:font-bold">
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-[#E6E2E8] flex items-center justify-between gap-3">
                            <a href="{{ route('services.onboarding', ['step' => 3]) }}" class="px-5 py-3 rounded-xl border border-[#E6E2E8] text-xs font-bold hover:bg-slate-50">
                                ← Back to Presets
                            </a>
                            <button type="submit" class="grail-btn-obsidian px-8 py-3.5 text-xs uppercase tracking-wider">
                                Save &amp; Review Launch Checklist →
                            </button>
                        </div>
                    </form>

                {{-- STEP 5: CHECKLIST & PUBLISH LIVE --}}
                @else
                    <div class="grail-card p-6 sm:p-10 rounded-3xl bg-white border border-[#E6E2E8] shadow-xs space-y-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#E6E2E8]">
                            <div>
                                <span class="grail-eyebrow !text-[#623562]">Step 5 of 5 · Website Launch Checklist</span>
                                <h2 class="text-xl sm:text-2xl font-bold text-[#1A1018] mt-1">Review &amp; Publish Your Website</h2>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-black text-[#1A1018]">{{ $readiness['score'] ?? 0 }}%</div>
                                <div class="text-[10px] uppercase tracking-wider text-[#1A1018]/55 font-bold">Readiness Score</div>
                            </div>
                        </div>

                        @if($readiness)
                            <div class="space-y-3">
                                @foreach($readiness['items'] as $item)
                                    <div class="flex items-center justify-between p-4 rounded-2xl border {{ $item['passed'] ? 'bg-emerald-50/60 border-emerald-200' : ($item['required'] ? 'bg-rose-50/70 border-rose-200' : 'bg-amber-50/60 border-amber-200') }}">
                                        <div class="flex items-center gap-3">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $item['passed'] ? 'bg-emerald-600 text-white' : ($item['required'] ? 'bg-rose-600 text-white' : 'bg-amber-500 text-white') }}">
                                                {{ $item['passed'] ? '✓' : '!' }}
                                            </span>
                                            <div>
                                                <div class="text-sm font-bold text-[#1A1018]">{{ $item['label'] }}</div>
                                                <div class="text-[11px] text-[#1A1018]/60">
                                                    {{ $item['required'] ? 'Required before publishing live' : 'Recommended for higher client conversion' }}
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-xs font-bold {{ $item['passed'] ? 'text-emerald-700' : 'text-amber-700' }}">
                                            {{ $item['passed'] ? 'Complete' : ($item['required'] ? 'Missing' : 'Optional') }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="rounded-2xl bg-slate-50 border border-[#E6E2E8] p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div>
                                <div class="text-[11px] font-bold uppercase tracking-wider text-[#623562]">Your Website Domain &amp; Fallback Route</div>
                                <div class="text-base font-bold text-[#1A1018] mt-0.5">{{ $vendor?->storefront_url }}</div>
                                <div class="text-[11px] text-[#1A1018]/60 mt-0.5">Active Preset: <span class="font-bold">{{ ucfirst($selectedPreset) }}</span> · Status: <span class="font-bold uppercase">{{ $vendor?->website_status ?? 'draft' }}</span></div>
                            </div>
                            <a href="{{ $vendor?->storefrontUrl(['preview' => 1]) }}" target="_blank"
                               class="px-4 py-2.5 rounded-xl border border-[#1A1018] text-xs font-bold text-[#1A1018] hover:bg-[#1A1018] hover:text-white transition text-center shrink-0">
                                Preview Draft Website ↗
                            </a>
                        </div>

                        <form action="{{ route('services.onboarding.save') }}" method="POST" class="pt-4 border-t border-[#E6E2E8] flex flex-col sm:flex-row items-center justify-between gap-3">
                            @csrf
                            <input type="hidden" name="step" value="5">

                            <a href="{{ route('services.onboarding', ['step' => 4]) }}" class="px-5 py-3 rounded-xl border border-[#E6E2E8] text-xs font-bold hover:bg-slate-50 w-full sm:w-auto text-center">
                                ← Back to Setup
                            </a>

                            <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                                <button type="submit" name="publish_action" value="draft"
                                        class="w-full sm:w-auto px-6 py-3.5 rounded-xl border border-[#E6E2E8] bg-white text-xs font-bold text-[#1A1018] hover:bg-slate-50">
                                    Keep as Private Draft
                                </button>
                                <button type="submit" name="publish_action" value="publish"
                                        @if(!($readiness['can_publish'] ?? false)) disabled @endif
                                        class="w-full sm:w-auto grail-btn-obsidian px-8 py-3.5 text-xs uppercase tracking-wider disabled:opacity-40">
                                    Publish My Website Live →
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-layouts.services>
