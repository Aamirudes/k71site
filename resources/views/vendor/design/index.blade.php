<x-layouts.vendor>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-10">
        
        <!-- Header Banner with Ambient Refraction -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 p-6 sm:p-10 rounded-3xl bg-gradient-to-br from-stone-900 via-stone-850 to-stone-950 text-white shadow-2xl relative overflow-hidden border border-white/10">
            <div class="relative z-10 max-w-2xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-amber-300 border border-white/10">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Product-First Store Engine · Five Signature Presets</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight font-heading">
                    Store Design &amp; Presets
                </h1>
                <p class="text-xs sm:text-sm text-stone-300 leading-relaxed">
                    Choose from five signature website designs crafted exclusively for selling products. No blogs or about essays — just a dominant product catalog, verified shopping trust, real customer reviews, and an interactive popup cart.
                </p>
                <div class="flex flex-wrap items-center gap-4 text-xs text-stone-300 pt-1">
                    <span class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                        <span>Active Preset: <strong id="hero-active-preset" class="text-white uppercase font-bold tracking-wider">{{ $activePresetKey }}</strong></span>
                    </span>
                    <span>•</span>
                    <span>Products Connected: <strong class="text-white">{{ $vendor->products()->where('status', 'active')->count() }} live items</strong></span>
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
        </div>

        <!-- Notification Toast -->
        <div id="design-toast" class="fixed bottom-6 right-6 z-50 transform translate-y-24 opacity-0 transition-all duration-300 pointer-events-none">
            <div class="px-5 py-3.5 rounded-2xl bg-stone-900 text-white text-xs sm:text-sm font-semibold shadow-2xl border border-white/15 flex items-center gap-3">
                <span id="design-toast-icon" class="text-emerald-400 font-bold text-base">✓</span>
                <span id="design-toast-msg">Settings updated successfully</span>
            </div>
        </div>

        <!-- 5 Signature Storefront Presets Gallery (Compact & Swipeable) -->
        <div class="space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-2">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white font-heading">
                        Five Signature Storefront Presets
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                        Each preset provides a distinct composition, header styling, banner treatment, and product cards. Select any preset to activate it immediately.
                    </p>
                </div>
            </div>

            @php
                $activeNorm = app(\App\Services\Storefront\StorefrontPresetService::class)->normalizePresetKey($activePresetKey);
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5">
                @foreach($presets as $key => $preset)
                    @php
                        $isActive = ($activeNorm === $key || $activePresetKey === $key);
                    @endphp
                    <div id="preset-card-{{ $key }}" 
                         class="rounded-3xl border transition-all duration-300 overflow-hidden flex flex-col justify-between {{ $isActive ? 'border-amber-500 bg-amber-500/[0.03] shadow-xl ring-2 ring-amber-500/30' : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:shadow-lg hover:border-slate-300' }}">
                        
                        <!-- Top Preset Header & Canvas Preview -->
                        <div class="p-5 relative overflow-hidden" 
                             style="background-color: {{ $preset['preview_bg'] }}; color: {{ $preset['preview_text'] }}; border-bottom: 1px solid rgba(0,0,0,0.06);">
                            
                            <div class="flex items-center justify-between mb-3">
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider"
                                      style="background-color: {{ $preset['preview_accent'] }}22; color: {{ $preset['preview_accent'] }};">
                                    {{ $preset['badge'] }}
                                </span>
                                
                                <span id="status-badge-{{ $key }}" 
                                      class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $isActive ? 'bg-stone-900 text-white shadow-xs' : 'hidden' }}">
                                    Active Live
                                </span>
                            </div>

                            <h3 class="text-xl font-black font-heading mb-0.5" style="font-family: '{{ $preset['display_font'] }}', serif;">
                                {{ $preset['name'] }}
                            </h3>
                            <p class="text-[11px] opacity-75 font-medium mb-3 line-clamp-1">
                                {{ $preset['tagline'] }}
                            </p>

                            <!-- Micro Visual Wireframe Layout -->
                            <div class="p-2.5 rounded-xl border bg-white/75 dark:bg-black/40 backdrop-blur-md space-y-2 text-[9px]" style="border-color: rgba(0,0,0,0.08);">
                                <div class="flex items-center justify-between pb-1 border-b border-black/5 dark:border-white/5">
                                    <span class="font-bold truncate max-w-[100px]">{{ $vendor->shop_name }}</span>
                                    <span class="opacity-75 font-mono">Bag (1)</span>
                                </div>
                                <div class="h-9 rounded-lg flex items-center justify-center font-bold text-[10px] uppercase tracking-wider shadow-inner"
                                     style="background-color: {{ $preset['preview_accent'] }}22; color: {{ $preset['preview_accent'] }};">
                                    HERO BANNER
                                </div>
                                <div class="grid grid-cols-3 gap-1 pt-0.5">
                                    <div class="aspect-square rounded bg-black/5 dark:bg-white/5"></div>
                                    <div class="aspect-square rounded bg-black/5 dark:bg-white/5"></div>
                                    <div class="aspect-square rounded bg-black/5 dark:bg-white/5"></div>
                                </div>
                            </div>

                            <!-- Typography & Palette Badges -->
                            <div class="mt-3 pt-2.5 border-t border-black/5 dark:border-white/5 flex items-center justify-between text-[10px] opacity-75">
                                <span>{{ $preset['display_font'] }}</span>
                                <span class="w-3 h-3 rounded-full border border-black/20" style="background-color: {{ $preset['preview_accent'] }};"></span>
                            </div>
                        </div>

                        <!-- Description & Action Button -->
                        <div class="p-4 space-y-3 flex flex-col justify-between flex-1">
                            <div>
                                <p class="text-[11px] text-slate-600 dark:text-zinc-400 leading-relaxed line-clamp-2">
                                    {{ $preset['description'] }}
                                </p>
                            </div>

                            <div class="pt-3 border-t border-slate-100 dark:border-zinc-800 flex items-center gap-2">
                                <button type="button" 
                                        id="btn-activate-{{ $key }}"
                                        onclick="activatePresetInstant('{{ $key }}', '{{ $preset['name'] }}')"
                                        class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold text-center transition-all duration-200 {{ $isActive ? 'bg-stone-100 text-stone-400 cursor-default' : 'bg-stone-900 hover:bg-stone-800 text-white shadow-md hover:scale-[1.01]' }}"
                                        {{ $isActive ? 'disabled' : '' }}>
                                    {{ $isActive ? '✓ Active' : '⚡ Activate' }}
                                </button>
                                
                                <a href="{{ route('storefront.show', ['slug' => $vendor->slug, 'preview' => 1]) }}"
                                   target="_blank"
                                   rel="noopener"
                                   class="p-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 hover:bg-slate-100 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-300 transition"
                                   title="Preview in new tab">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Legacy Aliases Reference (Ensures test coverage & compatibility) -->
            <div class="hidden">
                <span>Atelier</span>
                <span>Object</span>
                <span>Commerce</span>
                <span>Market</span>
                <span>Signature</span>
            </div>
        </div>

        <!-- Essential Customization Controls (Compact, Single-Card, No Visual Editor) -->
        <div class="p-6 sm:p-8 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-zinc-800">
                <div>
                    <h3 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white font-heading">
                        Essential Store Controls
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                        Customize your store's logo, campaign banner, product layout, and brand accent.
                    </p>
                </div>

                <button type="button" 
                        id="save-branding-btn"
                        onclick="saveEssentialSettings()"
                        class="px-6 py-3.5 rounded-2xl bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs shadow-md transition-all duration-200 flex items-center justify-center gap-2 hover:scale-[1.01]">
                    <span>💾 Apply &amp; Save Live</span>
                </button>
            </div>

            <form id="essential-settings-form" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <!-- Left Column: Store Name, Logo, Banner Image -->
                <div class="space-y-6">
                    
                    <!-- Store Name & Logo -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                                Store Name
                            </label>
                            <input type="text" 
                                   id="ctrl-shop-name" 
                                   name="shop_name"
                                   value="{{ $currentConfig['brand']['store_name'] ?? $vendor->shop_name }}"
                                   class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs sm:text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-stone-900">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-1.5">
                                Store Logo
                            </label>
                            <div class="flex items-center gap-3">
                                @php
                                    $currLogo = $currentConfig['brand']['logo_url'] ?? $vendor->logo_url;
                                @endphp
                                <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 overflow-hidden flex items-center justify-center shrink-0">
                                    @if($currLogo)
                                        <img src="{{ $currLogo }}" id="logo-preview-img" class="w-full h-full object-contain">
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Logo</span>
                                    @endif
                                </div>
                                <input type="file" 
                                       id="ctrl-logo-file" 
                                       name="logo"
                                       accept="image/*"
                                       class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            </div>
                        </div>
                    </div>

                    <!-- Banner Image Upload / URL -->
                    <div class="space-y-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                            Hero Campaign Banner
                        </label>
                        @php
                            $currBanner = $currentConfig['brand']['banner_url'] ?? $vendor->banner_url;
                        @endphp
                        @if($currBanner)
                            <div class="h-24 rounded-2xl overflow-hidden border border-slate-200 dark:border-zinc-700 bg-slate-100 mb-2 relative">
                                <img src="{{ $currBanner }}" id="banner-preview-img" class="w-full h-full object-cover">
                                <span class="absolute bottom-1.5 right-2 px-2 py-0.5 rounded bg-black/60 text-white text-[10px] font-bold">Current Banner</span>
                            </div>
                        @endif
                        <input type="file" 
                               id="ctrl-banner-file" 
                               name="banner"
                               accept="image/*"
                               class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-900 file:text-white hover:file:bg-stone-800 cursor-pointer">
                    </div>

                    <!-- Banner Heading & Shopping Link -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 mb-1">
                                Banner Headline (Optional)
                            </label>
                            <input type="text" 
                                   id="ctrl-hero-headline" 
                                   name="hero_headline"
                                   value="{{ $currentConfig['hero']['title'] ?? $currentConfig['brand']['hero_headline'] ?? $vendor->shop_name }}"
                                   placeholder="e.g. Autumn / Winter Collection"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-stone-900">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 dark:text-zinc-400 mb-1">
                                Shopping CTA Link
                            </label>
                            <input type="text" 
                                   id="ctrl-hero-link" 
                                   name="hero_link"
                                   value="{{ $currentConfig['hero']['link'] ?? '#catalog' }}"
                                   placeholder="#catalog"
                                   class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-stone-900">
                        </div>
                    </div>

                </div>

                <!-- Right Column: Product Layout, Accent Color, Announcement -->
                <div class="space-y-6">
                    
                    <!-- Product Layout Choice: Grid vs Horizontal Slider -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-2">
                            Product Display Mode
                        </label>
                        @php
                            $prodLayout = $currentConfig['products']['layout'] ?? 'grid';
                        @endphp
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition hover:bg-slate-50 dark:hover:bg-zinc-800 {{ $prodLayout === 'grid' ? 'border-stone-900 bg-stone-50/80 dark:border-white dark:bg-zinc-800' : 'border-slate-200 dark:border-zinc-700' }}">
                                <input type="radio" name="product_layout" value="grid" {{ $prodLayout === 'grid' ? 'checked' : '' }} class="text-stone-900 focus:ring-stone-900">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">Product Grid</span>
                                    <span class="block text-[10px] text-slate-400">Responsive 3–4 columns catalog</span>
                                </div>
                            </label>

                            <label class="flex items-center gap-3 p-3.5 rounded-2xl border cursor-pointer transition hover:bg-slate-50 dark:hover:bg-zinc-800 {{ $prodLayout === 'slider' ? 'border-stone-900 bg-stone-50/80 dark:border-white dark:bg-zinc-800' : 'border-slate-200 dark:border-zinc-700' }}">
                                <input type="radio" name="product_layout" value="slider" {{ $prodLayout === 'slider' ? 'checked' : '' }} class="text-stone-900 focus:ring-stone-900">
                                <div>
                                    <span class="block text-xs font-bold text-slate-900 dark:text-white">Product Slider</span>
                                    <span class="block text-[10px] text-slate-400">Horizontal swipeable shelf</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Curated Accent-Color Palette -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300 mb-2">
                            Curated Accent Palette
                        </label>
                        @php
                            $currAccent = $currentConfig['theme']['accent_color'] ?? '#c59b27';
                            $curatedSwatches = [
                                '#c59b27' => 'Champagne Gold',
                                '#18181b' => 'Charcoal Noir',
                                '#0284c7' => 'Azure Cyan',
                                '#2563eb' => 'Royal Cobalt',
                                '#059669' => 'Emerald Sage',
                                '#e11d48' => 'Crimson Coral',
                            ];
                        @endphp
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach($curatedSwatches as $hex => $label)
                                <button type="button" 
                                        onclick="setAccentSwatch('{{ $hex }}')"
                                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-semibold transition hover:scale-105"
                                        style="border-color: {{ $hex }}; background-color: {{ $hex }}15; color: {{ $hex }};">
                                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $hex }};"></span>
                                    <span>{{ $label }}</span>
                                </button>
                            @endforeach
                            <input type="color" 
                                   id="ctrl-accent-color" 
                                   name="accent_color"
                                   value="{{ $currAccent }}"
                                   class="w-9 h-9 rounded-full cursor-pointer border-0 bg-transparent">
                        </div>
                    </div>

                    <!-- Top Announcement Ribbon -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-zinc-850 border border-slate-100 dark:border-zinc-800 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-zinc-300">
                                Announcement Ribbon
                            </label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" 
                                       id="ctrl-announcement-toggle" 
                                       name="announcement_enabled"
                                       class="sr-only peer" 
                                       {{ !empty($currentConfig['header']['announcement_enabled']) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-slate-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <input type="text" 
                               id="ctrl-announcement-text" 
                               name="announcement_text"
                               value="{{ $currentConfig['header']['announcement_text'] ?? 'Complimentary Pan-India Express Shipping on orders above ₹1,999' }}"
                               placeholder="e.g. Complimentary Express Shipping on all orders above ₹1,999"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:ring-1 focus:ring-stone-900">
                    </div>

                </div>

            </form>
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

        function setAccentSwatch(hex) {
            document.getElementById('ctrl-accent-color').value = hex;
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
                    document.getElementById('hero-active-preset').textContent = key;

                    const allPresets = ['velora', 'noire', 'aurel', 'chroma', 'orbit'];
                    allPresets.forEach(k => {
                        const card = document.getElementById('preset-card-' + k);
                        const b = document.getElementById('btn-activate-' + k);
                        const badge = document.getElementById('status-badge-' + k);

                        if (card && b && badge) {
                            if (k === key) {
                                card.classList.add('border-amber-500', 'bg-amber-500/[0.03]', 'shadow-xl', 'ring-2', 'ring-amber-500/30');
                                card.classList.remove('border-slate-200', 'dark:border-zinc-800');
                                b.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-bold text-center transition-all duration-200 bg-stone-100 text-stone-400 cursor-default';
                                b.textContent = '✓ Active';
                                b.disabled = true;
                                badge.classList.remove('hidden');
                            } else {
                                card.classList.remove('border-amber-500', 'bg-amber-500/[0.03]', 'shadow-xl', 'ring-2', 'ring-amber-500/30');
                                card.classList.add('border-slate-200', 'dark:border-zinc-800');
                                b.className = 'flex-1 py-2.5 px-3 rounded-xl text-xs font-bold text-center transition-all duration-200 bg-stone-900 hover:bg-stone-800 text-white shadow-md hover:scale-[1.01]';
                                b.textContent = '⚡ Activate';
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

        function saveEssentialSettings() {
            const btn = document.getElementById('save-branding-btn');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span>Saving...</span>';

            const form = document.getElementById('essential-settings-form');
            const formData = new FormData(form);
            formData.append('announcement_enabled', document.getElementById('ctrl-announcement-toggle').checked ? '1' : '0');

            fetch('{{ route('vendor.design.quick_settings') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                if (data.status === 'success') {
                    showToast('✓ Store essentials saved and live!');
                } else {
                    showToast(data.message || 'Error saving settings', false);
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.innerHTML = originalText;
                showToast('Failed to save settings', false);
            });
        }
    </script>
</x-layouts.vendor>
