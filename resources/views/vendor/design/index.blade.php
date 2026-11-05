<x-layouts.vendor>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-10">
        
        <!-- Header Banner with Ambient Glow -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-6 sm:p-10 rounded-3xl bg-gradient-to-br from-stone-900 via-stone-850 to-stone-950 text-white shadow-2xl relative overflow-hidden border border-white/10">
            <div class="relative z-10 max-w-2xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-amber-300 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Storefront 2.0 Engine · Glassmorphic Liquid Presets</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-heading">
                    Store Design &amp; Presets
                </h1>
                <p class="text-xs sm:text-sm text-stone-300 leading-relaxed">
                    Select from five world-class website designs. Each template delivers a distinct mood, liquid bubble glassmorphic UI/UX, product-first showcase, and an interactive popup cart drawer.
                </p>
                <div class="flex flex-wrap items-center gap-4 text-xs text-stone-300 pt-1">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span>Active Preset: <strong id="hero-active-preset" class="text-white uppercase font-bold tracking-wider">{{ $activePresetKey }}</strong></span>
                    </span>
                    <span>•</span>
                    <span>Catalog Connected: <strong class="text-white">{{ $vendor->products()->where('status', 'active')->count() }} Products</strong></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 shrink-0 relative z-10">
                <a href="{{ route('storefront.show', $vendor->slug) }}" 
                   target="_blank" 
                   rel="noopener"
                   class="px-6 py-4 rounded-2xl bg-white hover:bg-stone-100 text-stone-900 font-bold text-sm shadow-xl transition-all duration-200 flex items-center justify-center gap-2 group hover:scale-[1.02]">
                    <span>👁️ Open Live Store</span>
                    <span class="text-xs">↗</span>
                </a>
            </div>

            <!-- Ambient Radial Glow -->
            <div class="absolute -right-10 -bottom-10 w-96 h-96 rounded-full bg-amber-500/15 blur-3xl pointer-events-none"></div>
        </div>

        <!-- Notification Toast -->
        <div id="design-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
            <div class="px-5 py-3.5 rounded-2xl bg-stone-900 text-white text-xs sm:text-sm font-semibold shadow-2xl border border-white/15 flex items-center gap-3">
                <span id="design-toast-icon" class="text-emerald-400 font-bold text-base">✓</span>
                <span id="design-toast-msg">Settings updated successfully</span>
            </div>
        </div>

        <!-- 5 Signature Storefront Presets Gallery -->
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white font-heading">
                        Five Signature Storefront Presets
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                        Click <strong>Activate &amp; Publish Live</strong> on any preset to instantly update your public store without losing any product data.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($presets as $key => $preset)
                    @php
                        $isActive = ($activePresetKey === $key);
                    @endphp
                    <div id="preset-card-{{ $key }}" 
                         class="rounded-3xl border transition-all duration-300 overflow-hidden flex flex-col justify-between {{ $isActive ? 'border-amber-500 bg-amber-500/[0.03] shadow-xl ring-2 ring-amber-500/30' : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:shadow-lg hover:border-slate-300' }}">
                        
                        <!-- Top Preset Header & Canvas Preview -->
                        <div class="p-6 relative overflow-hidden" 
                             style="background-color: {{ $preset['preview_bg'] }}; color: {{ $preset['preview_text'] }}; border-bottom: 1px solid rgba(0,0,0,0.06);">
                            
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                      style="background-color: {{ $preset['preview_accent'] }}22; color: {{ $preset['preview_accent'] }};">
                                    {{ $preset['badge'] }}
                                </span>
                                
                                <span id="status-badge-{{ $key }}" 
                                      class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider {{ $isActive ? 'bg-stone-900 text-white shadow-xs' : 'hidden' }}">
                                    Active Live
                                </span>
                            </div>

                            <h3 class="text-2xl font-black font-heading mb-1" style="font-family: '{{ $preset['display_font'] }}', serif;">
                                {{ $preset['name'] }}
                            </h3>
                            <p class="text-xs opacity-80 font-medium mb-4">
                                {{ $preset['tagline'] }}
                            </p>

                            <!-- Micro Mockup Representation -->
                            <div class="p-3.5 rounded-2xl border bg-white/70 dark:bg-black/30 backdrop-blur-md space-y-2.5 text-[10px]" style="border-color: rgba(0,0,0,0.08);">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5 dark:border-white/5">
                                    <span class="font-bold truncate max-w-[140px]">{{ $vendor->shop_name }}</span>
                                    <span class="opacity-75 font-mono">Bag (1)</span>
                                </div>
                                <div class="h-12 rounded-xl flex items-center justify-center font-bold text-xs uppercase tracking-wider shadow-inner"
                                     style="background-color: {{ $preset['preview_accent'] }}25; color: {{ $preset['preview_accent'] }};">
                                    FLAGSHIP SHOWCASE
                                </div>
                                <div class="grid grid-cols-3 gap-1.5 pt-0.5">
                                    <div class="aspect-square rounded-lg bg-black/5 dark:bg-white/5"></div>
                                    <div class="aspect-square rounded-lg bg-black/5 dark:bg-white/5"></div>
                                    <div class="aspect-square rounded-lg bg-black/5 dark:bg-white/5"></div>
                                </div>
                            </div>

                            <!-- Typography & Palette Badges -->
                            <div class="mt-4 pt-3 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[11px] opacity-80">
                                <span>Font: <strong>{{ $preset['display_font'] }}</strong></span>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $preset['preview_accent'] }};"></span>
                                    <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $preset['preview_card_bg'] }};"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Description & Action Button -->
                        <div class="p-6 space-y-4 flex flex-col justify-between flex-1">
                            <div>
                                <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed">
                                    {{ $preset['description'] }}
                                </p>
                                <div class="mt-3 text-[11px] text-slate-400 dark:text-zinc-500">
                                    <strong>Best for:</strong> {{ $preset['best_for'] }}
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 dark:border-zinc-800 flex items-center gap-2">
                                <button type="button" 
                                        id="btn-activate-{{ $key }}"
                                        onclick="activatePresetInstant('{{ $key }}', '{{ $preset['name'] }}')"
                                        class="flex-1 py-3 px-4 rounded-xl text-xs font-bold text-center transition-all duration-200 {{ $isActive ? 'bg-stone-100 text-stone-400 cursor-default' : 'bg-stone-900 hover:bg-stone-800 text-white shadow-md hover:scale-[1.01]' }}"
                                        {{ $isActive ? 'disabled' : '' }}>
                                    {{ $isActive ? '✓ Currently Active' : '⚡ Activate & Make Live' }}
                                </button>
                                
                                <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'preview' => 1]) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="p-3 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-300 transition"
                                   title="Preview in new tab">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <!-- Instant Brand Customizer Card (No Complex Visual Editor) -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white font-heading">
                        Brand Customizer
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                        Fine-tune your store's announcement ribbon, brand bio, and liquid glow accent color.
                    </p>
                </div>

                <button type="button" 
                        id="save-branding-btn"
                        onclick="saveBrandSettings()"
                        class="px-6 py-3 rounded-2xl bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs shadow-md transition-all duration-200 flex items-center justify-center gap-2">
                    <span>💾 Save &amp; Publish Live</span>
                </button>
            </div>

            <form id="quick-branding-form" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                            Store Tagline / Bio
                        </label>
                        <textarea id="brand-tagline" 
                                  rows="3" 
                                  class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-stone-900 dark:focus:ring-white"
                                  placeholder="e.g. Handcrafted bespoke luxury apparel tailored in our private studio.">{{ $currentConfig['brand']['description'] ?? $vendor->description }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Displayed in your boutique header and footer bio.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                            Liquid Glow Accent Color
                        </label>
                        <div class="flex flex-wrap items-center gap-3">
                            @php
                                $acc = $currentConfig['theme']['accent_color'] ?? '#c59b27';
                                $swatches = [
                                    '#c59b27' => 'Champagne Gold',
                                    '#06b6d4' => 'Cyber Cyan',
                                    '#2563eb' => 'Cobalt Blue',
                                    '#c85a32' => 'Terracotta',
                                    '#22c55e' => 'Electric Lime',
                                    '#f43f5e' => 'Rose Magenta',
                                ];
                            @endphp
                            @foreach($swatches as $hex => $label)
                                <button type="button" 
                                        onclick="selectColorSwatch('{{ $hex }}')"
                                        class="flex items-center gap-2 px-3 py-1.5 rounded-full border text-xs font-semibold transition hover:scale-105"
                                        style="border-color: {{ $hex }}; background-color: {{ $hex }}15; color: {{ $hex }};">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $hex }};"></span>
                                    <span>{{ $label }}</span>
                                </button>
                            @endforeach
                            <input type="color" 
                                   id="brand-accent-color" 
                                   value="{{ $acc }}"
                                   class="w-9 h-9 rounded-full cursor-pointer border-0 bg-transparent">
                        </div>
                    </div>
                </div>

                <!-- Right Column: Top Announcement Bar -->
                <div class="space-y-5 p-5 rounded-2xl bg-slate-50 dark:bg-zinc-850/60 border border-slate-100 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                            Top Announcement Ribbon
                        </label>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" 
                                   id="announcement-toggle" 
                                   class="sr-only peer" 
                                   {{ !empty($currentConfig['header']['announcement_enabled']) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                        </label>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-zinc-400 mb-1">
                            Ribbon Message
                        </label>
                        <input type="text" 
                               id="announcement-text" 
                               value="{{ $currentConfig['header']['announcement_text'] ?? 'Complimentary Pan-India Express Shipping on all orders.' }}"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-stone-900">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-500 dark:text-zinc-400 mb-1">
                            Ribbon Link (Optional)
                        </label>
                        <input type="text" 
                               id="announcement-link" 
                               value="{{ $currentConfig['header']['announcement_link'] ?? '' }}"
                               placeholder="https://... or #catalog"
                               class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-stone-900">
                    </div>
                </div>
            </form>
        </div>

        <!-- Section Architecture & Live Store Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl">
                    🛍️
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                    Live Product Catalog
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">
                    Your storefront renders <strong>{{ $vendor->products()->where('status', 'active')->count() }} active items</strong> with automatic stock sync.
                </p>
                <div class="pt-1">
                    <a href="{{ route('vendor.products.index') }}" class="text-xs font-bold text-amber-600 hover:underline">
                        Manage products →
                    </a>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-xl">
                    ⚡
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                    Popup Cart Engine
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">
                    Built-in slide-out glass drawer with free shipping tracker and direct vendor checkout.
                </p>
                <div class="pt-1">
                    <a href="{{ route('storefront.show', $vendor->slug) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:underline">
                        Test live popup cart ↗
                    </a>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl">
                    🚀
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                    Instant Deployment
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">
                    All preset changes and brand updates publish instantaneously to your live storefront.
                </p>
                <div class="pt-1">
                    <a href="{{ route('storefront.show', $vendor->slug) }}" target="_blank" class="text-xs font-bold text-emerald-600 hover:underline">
                        View live storefront ↗
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script>
        function showToast(msg, isSuccess = true) {
            const toast = document.getElementById('design-toast');
            const msgEl = document.getElementById('design-toast-msg');
            const iconEl = document.getElementById('design-toast-icon');

            msgEl.textContent = msg;
            iconEl.textContent = isSuccess ? '✓' : '✕';
            iconEl.className = isSuccess ? 'text-emerald-400 font-bold text-base' : 'text-rose-400 font-bold text-base';

            toast.classList.remove('translate-y-24', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');

            setTimeout(() => {
                toast.classList.remove('translate-y-0', 'opacity-100');
                toast.classList.add('translate-y-24', 'opacity-0');
            }, 3000);
        }

        function selectColorSwatch(hex) {
            document.getElementById('brand-accent-color').value = hex;
        }

        function activatePresetInstant(key, name) {
            const btn = document.getElementById('btn-activate-' + key);
            if (!btn) return;
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Activating...';

            fetch('{{ route('vendor.design.activate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ preset: key })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast('✓ ' + name + ' preset activated and live!');

                    // Update UI state
                    document.getElementById('hero-active-preset').textContent = key;

                    // Update all cards
                    ['atelier', 'object', 'commerce', 'market', 'signature'].forEach(k => {
                        const card = document.getElementById('preset-card-' + k);
                        const b = document.getElementById('btn-activate-' + k);
                        const badge = document.getElementById('status-badge-' + k);

                        if (card && b && badge) {
                            if (k === key) {
                                card.classList.add('border-amber-500', 'bg-amber-500/[0.03]', 'shadow-xl', 'ring-2', 'ring-amber-500/30');
                                card.classList.remove('border-slate-200', 'dark:border-zinc-800');
                                b.className = 'flex-1 py-3 px-4 rounded-xl text-xs font-bold text-center transition-all duration-200 bg-stone-100 text-stone-400 cursor-default';
                                b.textContent = '✓ Currently Active';
                                b.disabled = true;
                                badge.classList.remove('hidden');
                            } else {
                                card.classList.remove('border-amber-500', 'bg-amber-500/[0.03]', 'shadow-xl', 'ring-2', 'ring-amber-500/30');
                                card.classList.add('border-slate-200', 'dark:border-zinc-800');
                                b.className = 'flex-1 py-3 px-4 rounded-xl text-xs font-bold text-center transition-all duration-200 bg-stone-900 hover:bg-stone-800 text-white shadow-md hover:scale-[1.01]';
                                b.textContent = '⚡ Activate & Make Live';
                                b.disabled = false;
                                badge.classList.add('hidden');
                            }
                        }
                    });
                } else {
                    showToast(data.message || 'Failed to activate preset', false);
                    btn.disabled = false;
                    btn.textContent = originalText;
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Network error while activating preset', false);
                btn.disabled = false;
                btn.textContent = originalText;
            });
        }

        function saveBrandSettings() {
            const btn = document.getElementById('save-branding-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span>Saving...</span>';

            const payload = {
                tagline: document.getElementById('brand-tagline').value,
                accent_color: document.getElementById('brand-accent-color').value,
                announcement_enabled: document.getElementById('announcement-toggle').checked,
                announcement_text: document.getElementById('announcement-text').value,
                announcement_link: document.getElementById('announcement-link').value
            };

            fetch('{{ route('vendor.design.quick_settings') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (data.status === 'success') {
                    showToast('✓ Branding changes saved and live!');
                } else {
                    showToast(data.message || 'Error saving changes', false);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showToast('Failed to save changes', false);
            });
        }
    </script>
</x-layouts.vendor>
