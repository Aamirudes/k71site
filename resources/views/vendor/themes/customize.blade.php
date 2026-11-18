<x-layouts.vendor title="Customize {{ $theme->name }}">
<div class="py-5 sm:py-7 px-4 sm:px-6 lg:px-8 max-w-[1600px] mx-auto space-y-6">

    {{-- Top Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-200">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('vendor.themes.index') }}" class="hover:underline font-semibold">&larr; Theme Marketplace</a>
                <span>/</span>
                <span class="font-mono">{{ $theme->name }} (v{{ $theme->version }})</span>
                <span class="px-2 py-0.5 rounded-full bg-zinc-900 text-white text-[10px] font-bold">Visual Studio</span>
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900">Customize Draft: {{ $theme->name }}</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Saving updates only your private draft &amp; refreshes the live studio canvas. Your public storefront stays unchanged until you click <strong>Publish to Live Storefront</strong>.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ $previewUrl ?? route('vendor.themes.preview', $theme->theme_id) }}" target="_blank" rel="noopener"
               class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-700 hover:bg-slate-50">
                Fullscreen Preview &nearr;
            </a>

            <form action="{{ route('vendor.themes.draft.reset', $theme->theme_id) }}" method="POST" onsubmit="return confirm('Reset draft to {{ $theme->name }} theme defaults? Your live storefront will not change until you publish.');">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-50 cursor-pointer">
                    Reset Defaults
                </button>
            </form>

            <form action="{{ route('vendor.themes.publish', $theme->theme_id) }}" method="POST" onsubmit="return confirm('Publish your validated {{ $theme->name }} draft to your public live storefront now?');">
                @csrf
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 shadow-xs cursor-pointer">
                    {{ $isLive ? 'Publish Updates Live' : 'Publish to Live Storefront' }}
                </button>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs sm:text-sm font-semibold">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm font-semibold">{{ session('error') }}</div>
    @endif

    {{-- SPLIT-SCREEN LIVE VISUAL THEME STUDIO (Left Inspector + Right Live Draft Preview Canvas) --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

    {{-- Draft Configuration Form (Left 5 Columns on Desktop) --}}
    <form action="{{ route('vendor.themes.draft.save', $theme->theme_id) }}" method="POST" enctype="multipart/form-data" class="xl:col-span-5 space-y-5">
        @csrf

        <div class="grid grid-cols-1 gap-5">
            {{-- 1. Palette & Typography --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="text-sm font-extrabold text-slate-900">1. Palette &amp; Typography</h2>
                    <span class="text-[10px] font-mono uppercase text-slate-400">1-Click Presets</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    @php
                        $studioPresets = [
                            ['label' => 'Obsidian Gold', 'accent' => '#c9a96e', 'bg' => '#09090b', 'surface' => '#141417', 'text' => '#f4f4f5'],
                            ['label' => 'Parisian Ivory', 'accent' => '#18181b', 'bg' => '#f8f5ef', 'surface' => '#ffffff', 'text' => '#18181b'],
                            ['label' => 'Swiss Cobalt', 'accent' => '#1d4ed8', 'bg' => '#f8fafc', 'surface' => '#ffffff', 'text' => '#0f172a'],
                            ['label' => 'Botanical Sage', 'accent' => '#2f5d46', 'bg' => '#f4f6f1', 'surface' => '#ffffff', 'text' => '#1e2b22'],
                            ['label' => 'Terracotta', 'accent' => '#b45309', 'bg' => '#faf6f0', 'surface' => '#ffffff', 'text' => '#261b14'],
                            ['label' => 'Midnight Cyan', 'accent' => '#22d3ee', 'bg' => '#090d16', 'surface' => '#111827', 'text' => '#f1f5f9'],
                        ];
                    @endphp
                    @foreach($studioPresets as $sp)
                        <button type="button"
                                onclick="
                                    document.getElementById('k71-pal-accent').value='{{ $sp['accent'] }}';
                                    document.getElementById('k71-pal-bg').value='{{ $sp['bg'] }}';
                                    document.getElementById('k71-pal-surface').value='{{ $sp['surface'] }}';
                                    document.getElementById('k71-pal-text').value='{{ $sp['text'] }}';
                                "
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-slate-200 bg-slate-50 hover:bg-slate-100 text-[10px] font-bold text-slate-700 cursor-pointer transition">
                            <span class="w-2.5 h-2.5 rounded-full border border-black/15" style="background-color: {{ $sp['bg'] }};"></span>
                            <span class="w-2.5 h-2.5 rounded-full -ml-1 border border-black/15" style="background-color: {{ $sp['accent'] }};"></span>
                            <span>{{ $sp['label'] }}</span>
                        </button>
                    @endforeach
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Accent Color</label>
                        <input id="k71-pal-accent" type="color" name="config[palette][accent]" value="{{ $draftConfig['palette']['accent'] ?? $manifest['palette']['accent'] }}" class="w-full h-10 rounded-lg border border-slate-200 p-1 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Page Background</label>
                        <input id="k71-pal-bg" type="color" name="config[palette][bg]" value="{{ $draftConfig['palette']['bg'] ?? $manifest['palette']['bg'] }}" class="w-full h-10 rounded-lg border border-slate-200 p-1 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Card Surface</label>
                        <input id="k71-pal-surface" type="color" name="config[palette][surface]" value="{{ $draftConfig['palette']['surface'] ?? $manifest['palette']['surface'] }}" class="w-full h-10 rounded-lg border border-slate-200 p-1 cursor-pointer">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Primary Text</label>
                        <input id="k71-pal-text" type="color" name="config[palette][text]" value="{{ $draftConfig['palette']['text'] ?? $manifest['palette']['text'] }}" class="w-full h-10 rounded-lg border border-slate-200 p-1 cursor-pointer">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Heading Font</label>
                        <select name="config[typography][heading_font]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium">
                            @foreach($headingFonts as $hf)
                                <option value="{{ $hf }}" {{ ($draftConfig['typography']['heading_font'] ?? '') === $hf ? 'selected' : '' }}>{{ $hf }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Body Font</label>
                        <select name="config[typography][body_font]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-medium">
                            @foreach($bodyFonts as $bf)
                                <option value="{{ $bf }}" {{ ($draftConfig['typography']['body_font'] ?? '') === $bf ? 'selected' : '' }}>{{ $bf }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- 2. Header & Announcement Strip --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <h2 class="text-sm font-extrabold text-slate-900">2. Header &amp; Announcement Strip</h2>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Logo Size</label>
                        <select name="config[header][logo_size]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="sm" {{ ($draftConfig['header']['logo_size'] ?? 'md') === 'sm' ? 'selected' : '' }}>Compact</option>
                            <option value="md" {{ ($draftConfig['header']['logo_size'] ?? 'md') === 'md' ? 'selected' : '' }}>Standard</option>
                            <option value="lg" {{ ($draftConfig['header']['logo_size'] ?? 'md') === 'lg' ? 'selected' : '' }}>Prominent</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 pt-5">
                        <input type="checkbox" name="config[header][sticky]" value="1" {{ !empty($draftConfig['header']['sticky']) ? 'checked' : '' }}>
                        Sticky Header
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 pt-5">
                        <input type="checkbox" name="config[header][show_search]" value="1" {{ !empty($draftConfig['header']['show_search']) ? 'checked' : '' }}>
                        Show Search
                    </label>
                </div>
                <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                    <input type="checkbox" name="config[header][announcement_enabled]" value="1" {{ !empty($draftConfig['header']['announcement_enabled']) ? 'checked' : '' }}>
                    Enable Top Announcement Strip
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input type="text" name="config[header][announcement_text]" value="{{ $draftConfig['header']['announcement_text'] ?? '' }}" placeholder="Announcement message..." maxlength="140" class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    <input type="text" name="config[header][announcement_link]" value="{{ $draftConfig['header']['announcement_link'] ?? '' }}" placeholder="Optional link (/collection or https://...)" class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                </div>
            </div>

            {{-- 3. Hero Composition & Media --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3.5">
                <h2 class="text-sm font-extrabold text-slate-900">3. Hero Composition</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Eyebrow Label</label>
                        <input type="text" name="config[hero][eyebrow]" value="{{ $draftConfig['hero']['eyebrow'] ?? '' }}" maxlength="60" placeholder="e.g. Autumn Collection" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">CTA Button Text</label>
                        <input type="text" name="config[hero][cta_label]" value="{{ $draftConfig['hero']['cta_label'] ?? 'Explore Collection' }}" maxlength="40" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Hero Headline</label>
                    <input type="text" name="config[hero][headline]" value="{{ $draftConfig['hero']['headline'] ?? $vendor->shop_name }}" maxlength="120" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold">
                </div>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Hero Subheadline</label>
                    <textarea name="config[hero][subheadline]" rows="2" maxlength="260" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">{{ $draftConfig['hero']['subheadline'] ?? '' }}</textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Hero Image URL</label>
                        <input type="text" name="config[hero][image_url]" value="{{ $draftConfig['hero']['image_url'] ?? '' }}" placeholder="Uploaded image URL" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Or Upload Hero Image</label>
                        <input type="file" name="hero_image_file" accept="image/*" class="w-full text-xs text-slate-600">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Optional Release Label</label>
                        <input type="text" name="config[hero][release_label]" value="{{ $draftConfig['hero']['release_label'] ?? '' }}" placeholder="e.g. Drop 04 Available" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Real Release Deadline (Optional)</label>
                        <input type="datetime-local" name="config[hero][release_deadline]" value="{{ !empty($draftConfig['hero']['release_deadline']) ? date('Y-m-d\TH:i', strtotime($draftConfig['hero']['release_deadline'])) : '' }}" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                </div>
            </div>

            {{-- 4. Grid, Motion, Glass & Editorial --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <h2 class="text-sm font-extrabold text-slate-900">4. Product Grid, Motion &amp; Glass</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Columns</label>
                        <select name="config[grid][columns]" class="w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs">
                            <option value="2" {{ (int)($draftConfig['grid']['columns'] ?? 4) === 2 ? 'selected' : '' }}>2 Col</option>
                            <option value="3" {{ (int)($draftConfig['grid']['columns'] ?? 4) === 3 ? 'selected' : '' }}>3 Col</option>
                            <option value="4" {{ (int)($draftConfig['grid']['columns'] ?? 4) === 4 ? 'selected' : '' }}>4 Col</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Image Ratio</label>
                        <select name="config[grid][card_aspect]" class="w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs">
                            <option value="3/4" {{ ($draftConfig['grid']['card_aspect'] ?? '3/4') === '3/4' ? 'selected' : '' }}>3:4 Portrait</option>
                            <option value="4/5" {{ ($draftConfig['grid']['card_aspect'] ?? '3/4') === '4/5' ? 'selected' : '' }}>4:5 Editorial</option>
                            <option value="1/1" {{ ($draftConfig['grid']['card_aspect'] ?? '3/4') === '1/1' ? 'selected' : '' }}>1:1 Square</option>
                            <option value="4/3" {{ ($draftConfig['grid']['card_aspect'] ?? '3/4') === '4/3' ? 'selected' : '' }}>4:3 Landscape</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Spacing</label>
                        <select name="config[grid][spacing]" class="w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs">
                            <option value="compact" {{ ($draftConfig['grid']['spacing'] ?? 'normal') === 'compact' ? 'selected' : '' }}>Compact</option>
                            <option value="normal" {{ ($draftConfig['grid']['spacing'] ?? 'normal') === 'normal' ? 'selected' : '' }}>Balanced</option>
                            <option value="spacious" {{ ($draftConfig['grid']['spacing'] ?? 'normal') === 'spacious' ? 'selected' : '' }}>Spacious</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Text Align</label>
                        <select name="config[grid][content_align]" class="w-full rounded-xl border border-slate-200 px-2.5 py-2 text-xs">
                            <option value="left" {{ ($draftConfig['grid']['content_align'] ?? 'left') === 'left' ? 'selected' : '' }}>Left</option>
                            <option value="center" {{ ($draftConfig['grid']['content_align'] ?? 'left') === 'center' ? 'selected' : '' }}>Centered</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Motion Profile Intensity</label>
                        <select name="config[motion][intensity]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="normal" {{ ($draftConfig['motion']['intensity'] ?? 'normal') === 'normal' ? 'selected' : '' }}>Full Theme Motion</option>
                            <option value="subtle" {{ ($draftConfig['motion']['intensity'] ?? 'normal') === 'subtle' ? 'selected' : '' }}>Subtle</option>
                            <option value="reduced" {{ ($draftConfig['motion']['intensity'] ?? 'normal') === 'reduced' ? 'selected' : '' }}>Reduced Motion</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 mb-1">Liquid Glass Surfaces</label>
                        <select name="config[motion][glass_strength]" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                            <option value="normal" {{ ($draftConfig['motion']['glass_strength'] ?? 'normal') === 'normal' ? 'selected' : '' }}>Theme Default</option>
                            <option value="subtle" {{ ($draftConfig['motion']['glass_strength'] ?? 'normal') === 'subtle' ? 'selected' : '' }}>Subtle Blur</option>
                            <option value="off" {{ ($draftConfig['motion']['glass_strength'] ?? 'normal') === 'off' ? 'selected' : '' }}>Solid Surfaces</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 space-y-2">
                    <h3 class="text-xs font-bold text-slate-800">Optional Editorial / Story Block (hidden when empty)</h3>
                    <input type="text" name="config[editorial][heading]" value="{{ $draftConfig['editorial']['heading'] ?? '' }}" placeholder="Story heading (optional)" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    <textarea name="config[editorial][body]" rows="2" placeholder="Factual brand story or craft details..." class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">{{ $draftConfig['editorial']['body'] ?? '' }}</textarea>
                    <input type="hidden" name="config[editorial][image_url]" value="{{ $draftConfig['editorial']['image_url'] ?? '' }}">
                    <input type="file" name="editorial_image_file" accept="image/*" class="w-full text-xs text-slate-600">
                </div>
            </div>

            {{-- 5. Section Visibility & Sequence --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-3">
                <h2 class="text-sm font-extrabold text-slate-900">5. Homepage Sections</h2>
                <p class="text-[11px] text-slate-500">Toggle supported sections on or off for this theme.</p>
                <div class="space-y-2">
                    @foreach(($draftConfig['sections'] ?? []) as $idx => $sec)
                        <div class="flex items-center justify-between gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="flex items-center gap-2.5">
                                <input type="hidden" name="config[sections][{{ $idx }}][type]" value="{{ $sec['type'] }}">
                                @if(!empty($sec['style']))
                                    <input type="hidden" name="config[sections][{{ $idx }}][style]" value="{{ $sec['style'] }}">
                                @endif
                                <input type="hidden" name="config[sections][{{ $idx }}][enabled]" value="0">
                                <input type="checkbox" name="config[sections][{{ $idx }}][enabled]" value="1" {{ !empty($sec['enabled']) ? 'checked' : '' }}>
                                <span class="text-xs font-bold text-slate-800">{{ ucwords(str_replace('_', ' ', $sec['type'])) }}</span>
                            </div>
                            @if($sec['type'] === 'product_grid')
                                <input type="text" name="config[sections][{{ $idx }}][title]" value="{{ $sec['title'] ?? 'Collection' }}" class="rounded-lg border border-slate-200 bg-white px-2.5 py-1 text-xs w-44">
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 6. Footer & Optional Promo Popup --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
                <h2 class="text-sm font-extrabold text-slate-900">6. Footer &amp; Optional Announcement Popup</h2>
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Footer About Text</label>
                    <textarea name="config[footer][about_text]" rows="2" maxlength="320" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">{{ $draftConfig['footer']['about_text'] ?? '' }}</textarea>
                </div>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="hidden" name="config[footer][show_policies]" value="0">
                        <input type="checkbox" name="config[footer][show_policies]" value="1" {{ !empty($draftConfig['footer']['show_policies']) ? 'checked' : '' }}>
                        Show Return Policy
                    </label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-700">
                        <input type="hidden" name="config[footer][show_contact]" value="0">
                        <input type="checkbox" name="config[footer][show_contact]" value="1" {{ !empty($draftConfig['footer']['show_contact']) ? 'checked' : '' }}>
                        Show Contact Info
                    </label>
                </div>
                <div class="pt-3 border-t border-slate-100 space-y-2">
                    <label class="flex items-center gap-2 text-xs font-bold text-slate-800">
                        <input type="hidden" name="config[popup][enabled]" value="0">
                        <input type="checkbox" name="config[popup][enabled]" value="1" {{ !empty($draftConfig['popup']['enabled']) ? 'checked' : '' }}>
                        Enable Optional Store Popup (shown once per session, off by default)
                    </label>
                    <input type="text" name="config[popup][heading]" value="{{ $draftConfig['popup']['heading'] ?? '' }}" placeholder="Popup heading..." class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    <textarea name="config[popup][body]" rows="2" placeholder="Popup message..." class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs">{{ $draftConfig['popup']['body'] ?? '' }}</textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" name="config[popup][cta_label]" value="{{ $draftConfig['popup']['cta_label'] ?? '' }}" placeholder="Button text (optional)" class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                        <input type="text" name="config[popup][cta_url]" value="{{ $draftConfig['popup']['cta_url'] ?? '' }}" placeholder="Button link (optional)" class="rounded-xl border border-slate-200 px-3 py-2 text-xs">
                    </div>
                </div>
            </div>
        </div>

        <div class="sticky bottom-4 z-20 p-3 rounded-2xl bg-white/95 backdrop-blur-md border border-slate-200 shadow-lg flex items-center justify-between gap-3">
            <span class="text-[11px] text-slate-500 font-medium pl-1">Private draft only</span>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-extrabold hover:bg-zinc-800 shadow-sm cursor-pointer">
                Save Draft Changes
            </button>
        </div>
    </form>

    {{-- Live Interactive Draft Preview Canvas (Right 7 Columns on Desktop) --}}
    @if(!empty($signedFrameBase))
        <aside class="hidden xl:flex xl:col-span-7 flex-col sticky top-6 rounded-2xl border border-slate-200 bg-zinc-950 overflow-hidden shadow-xl"
               style="height: calc(100vh - 5rem);"
               data-frame-base="{{ $signedFrameBase }}"
               x-data="{
                   page: 'home',
                   device: 'desktop',
                   base: '',
                   init() { this.base = this.$el.dataset.frameBase || ''; },
                   get src() {
                       if (!this.base) return 'about:blank';
                       const sep = this.base.includes('?') ? '&' : '?';
                       return this.base + sep + 'page=' + this.page;
                   },
                   reload() {
                       const f = this.$refs.canvasFrame;
                       if (f) f.src = this.src + '&_t=' + Date.now();
                   }
               }">
            <div class="px-4 py-2.5 bg-zinc-900 border-b border-white/10 flex items-center justify-between gap-3 text-xs text-zinc-200">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500/80"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-400/80"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500/80"></span>
                    <span class="ml-2 font-mono text-[11px] text-zinc-400">Live Draft Canvas · {{ $theme->name }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="inline-flex rounded-lg bg-white/5 border border-white/10 p-0.5 text-[11px] font-bold">
                        <button type="button" @click="page = 'home'" :class="page === 'home' ? 'bg-white text-zinc-950' : 'text-zinc-300'" class="px-2.5 py-1 rounded-md cursor-pointer">Home</button>
                        <button type="button" @click="page = 'collection'" :class="page === 'collection' ? 'bg-white text-zinc-950' : 'text-zinc-300'" class="px-2.5 py-1 rounded-md cursor-pointer">Collection</button>
                        <button type="button" @click="page = 'product'" :class="page === 'product' ? 'bg-white text-zinc-950' : 'text-zinc-300'" class="px-2.5 py-1 rounded-md cursor-pointer">Product</button>
                    </div>
                    <div class="inline-flex rounded-lg bg-white/5 border border-white/10 p-0.5 text-[11px] font-bold">
                        <button type="button" @click="device = 'desktop'" :class="device === 'desktop' ? 'bg-white text-zinc-950' : 'text-zinc-300'" class="px-2.5 py-1 rounded-md cursor-pointer">Desktop</button>
                        <button type="button" @click="device = 'mobile'" :class="device === 'mobile' ? 'bg-white text-zinc-950' : 'text-zinc-300'" class="px-2.5 py-1 rounded-md cursor-pointer">Mobile</button>
                    </div>
                    <button type="button" @click="reload()" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-white text-[11px] font-bold cursor-pointer" title="Reload preview">&#x21bb;</button>
                </div>
            </div>
            <div class="flex-1 flex items-center justify-center bg-zinc-900 overflow-hidden" :class="device === 'mobile' ? 'p-4' : 'p-0'">
                <iframe x-ref="canvasFrame" :src="src" title="Live draft studio preview"
                        class="bg-white border-0 transition-all duration-300"
                        :class="device === 'mobile' ? 'w-[390px] h-full rounded-[2rem] border-4 border-zinc-800 shadow-2xl' : 'w-full h-full'"></iframe>
            </div>
        </aside>
    @endif
    </div>
</div>
</x-layouts.vendor>
