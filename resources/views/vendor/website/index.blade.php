<x-layouts.vendor title="Personal Website Hub">
    @php
        $isLive = $vendor->isWebsitePublished();
        $identity = $siteData['identity'] ?? [];
        $vis = $siteData['section_visibility'] ?? [];
        $nav = $siteData['nav_labels'] ?? [];
        $contact = $siteData['contact'] ?? [];
        $prefs = is_array($vendor->public_contact_preferences) ? $vendor->public_contact_preferences : [];
        $ext = is_array($vendor->external_links) ? $vendor->external_links : [];
    @endphp

    <div class="max-w-7xl mx-auto space-y-6 pb-12">

        <!-- HERO STATUS & PUBLISH CONTROL -->
        <div class="bg-white rounded-2xl border-2 border-zinc-900 p-6 space-y-5" x-data="{ copied: false }">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase border {{ $isLive ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                            {{ $isLive ? '● Website Live' : '○ Draft Mode' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold uppercase bg-zinc-100 text-zinc-700 border border-zinc-200">
                            Active Preset: {{ strtoupper($activePreset) }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-black text-zinc-950 tracking-tight">Personal Website Hub</h1>
                    <div class="flex flex-wrap items-center gap-2.5 text-xs">
                        <span class="font-mono font-bold text-black bg-zinc-100 px-3 py-1 rounded-lg border border-zinc-200">{{ $vendor->storefront_url }}</span>
                        <button type="button"
                                @click="navigator.clipboard?.writeText('{{ $vendor->storefront_url }}'); copied = true; setTimeout(() => copied = false, 2000);"
                                class="px-3 py-1 rounded-lg bg-zinc-900 text-white font-bold hover:bg-black cursor-pointer">
                            <span x-text="copied ? 'Copied!' : 'Copy URL'"></span>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <a href="{{ $vendor->storefrontUrl(['preview' => 1]) }}" target="_blank"
                       class="px-4 py-2.5 rounded-xl border border-zinc-300 bg-white hover:bg-zinc-50 text-xs font-bold text-zinc-800">
                        Live Preview ↗
                    </a>
                    @if($isLive)
                        <a href="{{ $vendor->storefront_url }}" target="_blank"
                           class="px-4 py-2.5 rounded-xl border border-emerald-600 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold">
                            Open Public Website ↗
                        </a>
                        <form action="{{ route('vendor.website.publish') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="unpublish">
                            <button type="submit" class="px-4 py-2.5 rounded-xl border border-zinc-300 bg-zinc-100 hover:bg-zinc-200 text-zinc-800 text-xs font-bold cursor-pointer">
                                Switch to Draft
                            </button>
                        </form>
                    @else
                        <form action="{{ route('vendor.website.publish') }}" method="POST">
                            @csrf
                            <input type="hidden" name="action" value="publish">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer shadow-xs">
                                Publish Website Live
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- READINESS CHECKLIST -->
            <div class="pt-4 border-t border-zinc-200 space-y-2.5">
                <div class="flex items-center justify-between text-xs font-bold">
                    <span>Launch Readiness ({{ (int) ($readiness['score'] ?? 0) }}%)</span>
                    <span class="font-mono text-zinc-500">{{ $readiness['can_publish'] ? 'Ready to publish' : 'Complete required items' }}</span>
                </div>
                <div class="w-full h-2 rounded-full bg-zinc-100 overflow-hidden">
                    <div class="h-full bg-black transition-all" style="width: {{ (int) ($readiness['score'] ?? 0) }}%;"></div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
                    @foreach($readiness['items'] as $item)
                        <div class="px-3 py-2 rounded-xl border text-xs flex items-center gap-2 {{ $item['passed'] ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900' : 'bg-zinc-50 border-zinc-200 text-zinc-600' }}">
                            <span class="font-black">{{ $item['passed'] ? '✓' : '○' }}</span>
                            <span class="truncate">{{ $item['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 10 SIGNATURE PRESETS (NON-DESTRUCTIVE SWITCH) -->
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-black text-zinc-950">Website Presets (Non-Destructive Switch)</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Switch freely across all 10 K71 design presets — your bio, portfolio case studies, services, reviews, and subdomain stay intact.</p>
                </div>
                <a href="{{ route('vendor.design.index') }}" class="px-3.5 py-2 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-700 hover:bg-zinc-50 shrink-0">
                    Open Advanced Visual Studio →
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                @foreach($presets as $key => $preset)
                    @php
                        $isCurrent = strtolower($activePreset) === strtolower($key);
                    @endphp
                    <div class="rounded-2xl border p-3.5 flex flex-col justify-between gap-3 {{ $isCurrent ? 'border-black ring-2 ring-black/10 bg-zinc-50/60' : 'border-zinc-200 bg-white' }}">
                        <div class="space-y-2">
                            <div class="h-12 rounded-xl px-3 flex items-center justify-between border border-black/10"
                                 style="background: {{ $preset['preview_bg'] ?? '#111111' }}; color: {{ $preset['preview_text'] ?? '#ffffff' }};">
                                <span class="text-xs font-black truncate">{{ $preset['name'] ?? strtoupper($key) }}</span>
                                <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $preset['preview_accent'] ?? '#f59e0b' }};"></span>
                            </div>
                            <div class="flex items-center justify-between gap-1">
                                <span class="text-[10px] font-mono uppercase text-zinc-500 font-bold">{{ $preset['badge'] ?? 'Signature' }}</span>
                                @if($isCurrent)
                                    <span class="px-1.5 py-0.5 rounded bg-black text-white text-[9px] font-mono font-bold uppercase">Active</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <a href="{{ $vendor->storefrontUrl(['preview' => 1, 'preset' => $key]) }}" target="_blank"
                               class="flex-1 text-center px-2 py-1.5 rounded-lg border border-zinc-300 text-[11px] font-bold text-zinc-700 hover:bg-zinc-100">
                                Preview
                            </a>
                            <form action="{{ route('vendor.website.preset') }}" method="POST" class="flex-1">
                                @csrf
                                <input type="hidden" name="preset_key" value="{{ $key }}">
                                <button type="submit"
                                        class="w-full px-2 py-1.5 rounded-lg text-[11px] font-bold cursor-pointer {{ $isCurrent ? 'bg-zinc-200 text-zinc-600' : 'bg-black hover:bg-zinc-800 text-white' }}">
                                    {{ $isCurrent ? 'Selected' : 'Apply' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SUBDOMAIN & IDENTITY / SECTIONS GRID -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- LEFT COLUMN: SUBDOMAIN + SECTIONS + PRIVACY -->
            <div class="space-y-6">
                <!-- Subdomain Manager -->
                <div class="bg-white rounded-2xl border border-zinc-200 p-6 space-y-4" x-data="{
                    slugInput: '{{ old('slug', $vendor->slug) }}',
                    statusMsg: '',
                    available: null,
                    checking: false,
                    async checkAvailability() {
                        if (!this.slugInput || this.slugInput.length < 3) return;
                        this.checking = true;
                        try {
                            const res = await fetch('{{ route('services.check_subdomain') }}?slug=' + encodeURIComponent(this.slugInput), { headers: { 'Accept': 'application/json' } });
                            const data = await res.json();
                            this.available = data.available;
                            this.statusMsg = data.message || '';
                        } catch (e) {}
                        this.checking = false;
                    }
                }">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider text-zinc-950">Website Subdomain</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Claims your unique wildcard address on {{ $domain }}. Old addresses automatically 301-redirect.</p>
                    </div>

                    <form action="{{ route('vendor.website.subdomain') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Subdomain Name</label>
                            <div class="flex items-center rounded-xl border border-zinc-300 overflow-hidden focus-within:border-black">
                                <input type="text" name="slug" x-model="slugInput" required
                                       class="w-full px-3 py-2 text-xs font-mono font-bold text-black focus:outline-none">
                                <span class="px-2.5 py-2 bg-zinc-100 text-zinc-500 font-mono text-[11px] border-l border-zinc-200 shrink-0">.{{ $domain }}</span>
                            </div>
                            @error('slug')
                                <p class="text-[11px] text-rose-600 font-semibold mt-1">{{ $message }}</p>
                            @enderror
                            <div x-show="statusMsg" class="text-[11px] font-semibold mt-1" :class="available ? 'text-emerald-700' : 'text-rose-600'" x-text="statusMsg"></div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="checkAvailability()" class="px-3 py-2 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-700 hover:bg-zinc-50 cursor-pointer">
                                <span x-text="checking ? 'Checking...' : 'Check Availability'"></span>
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                                Save Subdomain
                            </button>
                        </div>
                    </form>

                    @if($recentSlugRedirects->isNotEmpty())
                        <div class="pt-3 border-t border-zinc-200 space-y-1.5">
                            <div class="text-[10px] font-mono uppercase font-bold text-zinc-500">Active 301 Redirects</div>
                            @foreach($recentSlugRedirects as $hist)
                                <div class="text-[11px] font-mono text-zinc-600 bg-zinc-50 px-2.5 py-1 rounded-lg border border-zinc-200">
                                    {{ $hist->slug }}.{{ $domain }} → {{ $vendor->slug }}.{{ $domain }}
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Section Visibility & Custom Nav Labels -->
                <div class="bg-white rounded-2xl border border-zinc-200 p-6 space-y-4">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider text-zinc-950">Sections &amp; Navigation</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Choose which sections appear and customize top nav wording.</p>
                    </div>

                    <form action="{{ route('vendor.website.sections') }}" method="POST" class="space-y-3">
                        @csrf
                        @foreach(['hero' => 'Hero Introduction', 'portfolio' => 'Portfolio / Case Studies', 'services' => 'Services & Pricing Packages', 'about' => 'About, Skills & Languages', 'reviews' => 'Verified Client Reviews', 'contact' => 'Direct Contact & Enquiry'] as $sKey => $sLabel)
                            <label class="flex items-center justify-between px-3 py-2 rounded-xl border border-zinc-200 text-xs font-semibold text-zinc-800 cursor-pointer hover:bg-zinc-50">
                                <span>{{ $sLabel }}</span>
                                <input type="hidden" name="vis_{{ $sKey }}" value="0">
                                <input type="checkbox" name="vis_{{ $sKey }}" value="1" {{ ($vis[$sKey] ?? true) ? 'checked' : '' }} class="rounded border-zinc-400">
                            </label>
                        @endforeach

                        <div class="pt-2 grid grid-cols-2 gap-2.5">
                            @foreach(['overview' => 'Hero Nav', 'work' => 'Work Nav', 'services' => 'Services Nav', 'about' => 'About Nav', 'reviews' => 'Reviews Nav', 'contact' => 'Contact Nav'] as $nKey => $nTitle)
                                <div>
                                    <label class="block text-[10px] font-bold uppercase text-zinc-500 mb-1">{{ $nTitle }}</label>
                                    <input type="text" name="nav_{{ $nKey }}" value="{{ $nav[$nKey] ?? ucfirst($nKey) }}"
                                           class="w-full px-2.5 py-1.5 rounded-lg border border-zinc-300 text-xs font-semibold">
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                            Save Layout &amp; Labels
                        </button>
                    </form>
                </div>

                <!-- Contact Privacy -->
                <div class="bg-white rounded-2xl border border-zinc-200 p-6 space-y-4">
                    <div>
                        <h2 class="text-sm font-black uppercase tracking-wider text-zinc-950">Public Contact Privacy</h2>
                        <p class="text-xs text-zinc-500 mt-0.5">Control which channels visitors see on your personal website.</p>
                    </div>

                    <form action="{{ route('vendor.website.privacy') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Public Contact Email</label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $vendor->contact_email ?: $vendor->email) }}"
                                   class="w-full px-3 py-2 rounded-xl border border-zinc-300 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Public Contact Phone / WhatsApp</label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $vendor->contact_phone ?: $vendor->phone) }}"
                                   class="w-full px-3 py-2 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        @foreach(['show_email' => 'Show Email Address Publicly', 'show_phone' => 'Show Phone / WhatsApp Publicly', 'show_city' => 'Show City & Region', 'allow_direct_enquiry' => 'Enable Direct Website Enquiry Form', 'allow_k71_chat' => 'Show Message on K71 Option'] as $pKey => $pLabel)
                            <label class="flex items-center justify-between px-3 py-2 rounded-xl border border-zinc-200 text-xs font-semibold text-zinc-800 cursor-pointer">
                                <span>{{ $pLabel }}</span>
                                <input type="hidden" name="{{ $pKey }}" value="0">
                                <input type="checkbox" name="{{ $pKey }}" value="1" {{ ($prefs[$pKey] ?? ($contact[$pKey] ?? false)) ? 'checked' : '' }} class="rounded border-zinc-400">
                            </label>
                        @endforeach

                        <button type="submit" class="w-full py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                            Save Privacy Rules
                        </button>
                    </form>
                </div>
            </div>

            <!-- RIGHT 2 COLUMNS: BRAND IDENTITY & WEBSITE COPY -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 space-y-5">
                <div>
                    <h2 class="text-base font-black text-zinc-950">Brand Identity, Copy &amp; Credentials</h2>
                    <p class="text-xs text-zinc-500 mt-0.5">Updates both your personal website hero/about sections and your K71 Services provider profile.</p>
                </div>

                @if($errors->any())
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                        @foreach($errors->all() as $err)
                            <div>• {{ $err }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('vendor.website.identity') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Display Name / Studio Name *</label>
                            <input type="text" name="shop_name" value="{{ old('shop_name', $vendor->shop_name) }}" required
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-semibold">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Provider Type *</label>
                            <select name="provider_mode" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-semibold">
                                <option value="individual" {{ old('provider_mode', $vendor->provider_mode) === 'individual' ? 'selected' : '' }}>Individual Freelancer / Specialist</option>
                                <option value="agency" {{ old('provider_mode', $vendor->provider_mode) === 'agency' ? 'selected' : '' }}>Studio / Agency / Firm</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Hero Headline *</label>
                            <input type="text" name="headline" value="{{ old('headline', $identity['headline'] ?? $vendor->headline) }}" required
                                   placeholder="e.g. Brand Designer & Webflow Architect for High-Growth Startups"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm font-semibold">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Hero Subheadline</label>
                            <input type="text" name="hero_subtitle" value="{{ old('hero_subtitle', $identity['subtitle'] ?? $vendor->tagline) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs sm:text-sm">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Full About / Practice Bio *</label>
                            <textarea name="description" rows="4" required class="w-full p-3.5 rounded-xl border border-zinc-300 text-xs sm:text-sm">{{ old('description', $vendor->description) }}</textarea>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Working Mode *</label>
                            <select name="work_mode" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs font-semibold">
                                <option value="remote" {{ old('work_mode', $vendor->work_mode) === 'remote' ? 'selected' : '' }}>Remote</option>
                                <option value="hybrid" {{ old('work_mode', $vendor->work_mode) === 'hybrid' ? 'selected' : '' }}>Hybrid (Remote + Local)</option>
                                <option value="onsite" {{ old('work_mode', $vendor->work_mode) === 'onsite' ? 'selected' : '' }}>On-Site / Local</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Availability Status *</label>
                            <select name="availability_status" class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs font-semibold">
                                <option value="available" {{ old('availability_status', $vendor->availability_status) === 'available' ? 'selected' : '' }}>Available for New Projects</option>
                                <option value="busy" {{ old('availability_status', $vendor->availability_status) === 'busy' ? 'selected' : '' }}>Limited Availability</option>
                                <option value="away" {{ old('availability_status', $vendor->availability_status) === 'away' ? 'selected' : '' }}>Currently Away</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Typical Response Time</label>
                            <input type="text" name="response_time" value="{{ old('response_time', $vendor->response_time ?: 'Within 2 hours') }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-bold text-zinc-700 mb-1">Years Exp</label>
                                <input type="number" name="years_experience" min="0" max="70" value="{{ old('years_experience', $vendor->years_experience) }}"
                                       class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 text-xs">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-zinc-700 mb-1">Hourly Rate (₹)</label>
                                <input type="number" name="hourly_rate" min="0" value="{{ old('hourly_rate', $vendor->hourly_rate) }}"
                                       class="w-full px-3 py-2.5 rounded-xl border border-zinc-300 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Skills (comma separated)</label>
                            <input type="text" name="skills_input" value="{{ old('skills_input', implode(', ', (array) ($vendor->skills_json ?? []))) }}"
                                   placeholder="Brand Identity, Figma, Laravel, SEO"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Languages (comma separated)</label>
                            <input type="text" name="languages_input" value="{{ old('languages_input', implode(', ', (array) ($vendor->languages_json ?? []))) }}"
                                   placeholder="English, Hindi"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">City</label>
                            <input type="text" name="city" value="{{ old('city', $vendor->city) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">State</label>
                            <input type="text" name="state" value="{{ old('state', $vendor->state) }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Primary Hero CTA Button</label>
                            <input type="text" name="cta_primary_text" value="{{ old('cta_primary_text', $identity['cta_primary_text'] ?? 'Start a Project') }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Secondary Hero Button</label>
                            <input type="text" name="cta_secondary_text" value="{{ old('cta_secondary_text', $identity['cta_secondary_text'] ?? 'View Work') }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">External Website</label>
                            <input type="url" name="external_website" value="{{ old('external_website', $ext['website'] ?? '') }}" placeholder="https://"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">LinkedIn URL</label>
                            <input type="url" name="external_linkedin" value="{{ old('external_linkedin', $ext['linkedin'] ?? '') }}" placeholder="https://linkedin.com/in/..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">GitHub URL</label>
                            <input type="url" name="external_github" value="{{ old('external_github', $ext['github'] ?? '') }}" placeholder="https://github.com/..."
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Behance / Dribbble / Portfolio URL</label>
                            <input type="url" name="external_behance" value="{{ old('external_behance', $ext['portfolio'] ?? '') }}" placeholder="https://"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-zinc-300 text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Profile Avatar / Brand Logo</label>
                            <input type="file" name="logo" accept="image/*" class="w-full text-xs">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-zinc-700 mb-1">Hero Banner / Cover</label>
                            <input type="file" name="banner" accept="image/*" class="w-full text-xs">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-zinc-200 flex justify-end">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-black hover:bg-zinc-800 text-white text-xs font-bold cursor-pointer">
                            Save Website Identity &amp; Publish Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.vendor>

