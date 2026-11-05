<x-layouts.vendor>
    <div class="py-8 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">
        
        <!-- Header Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-6 sm:p-8 rounded-3xl bg-gradient-to-br from-stone-900 via-stone-850 to-stone-950 text-white shadow-xl relative overflow-hidden">
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-white/10 text-amber-300 border border-white/10 mb-3">
                    <span>✨ Storefront 2.0 Engine</span>
                </div>
                <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight font-heading">
                    Store Design &amp; Presets
                </h1>
                <p class="text-sm text-stone-300 mt-2 leading-relaxed">
                    Transform your boutique with five signature website presets and a full visual editor. Customize sections, fonts, colors, and layouts with real-time responsive preview.
                </p>
                <div class="flex flex-wrap items-center gap-4 mt-4 text-xs text-stone-300">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $hasUnpublishedDraft ? 'bg-amber-400 animate-pulse' : 'bg-emerald-400' }}"></span>
                        <span>{{ $hasUnpublishedDraft ? 'Unpublished Draft in Progress' : 'Published & Live' }}</span>
                    </span>
                    <span>•</span>
                    <span>Current Preset: <strong class="text-white uppercase">{{ $activePresetKey }}</strong></span>
                </div>
            </div>

            <div class="flex flex-wrap sm:flex-col gap-3 shrink-0 relative z-10">
                <a href="{{ route('vendor.design.editor') }}" 
                   class="px-6 py-3.5 rounded-2xl bg-white hover:bg-stone-100 text-stone-900 font-bold text-sm shadow-lg transition-all duration-200 flex items-center justify-center gap-2 group hover:scale-102">
                    <span>🎨 Open Visual Editor</span>
                    <span class="transition-transform group-hover:translate-x-0.5">→</span>
                </a>
                <a href="{{ route('storefront.show', $vendor->slug) }}" 
                   target="_blank" 
                   rel="noopener"
                   class="px-6 py-3 rounded-2xl bg-white/10 hover:bg-white/15 text-white font-semibold text-xs border border-white/15 transition flex items-center justify-center gap-2">
                    <span>👁️ View Live Store</span>
                    <span class="text-[10px]">↗</span>
                </a>
            </div>

            <!-- Decorative Ambient Glow -->
            <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm font-semibold flex items-center gap-3">
                <span class="text-base">✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- 5 Signature Storefront Presets Showcase -->
        <div class="space-y-6">
            <div class="flex items-end justify-between">
                <div>
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-white font-heading">
                        Five Signature Storefront Presets
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-zinc-400 mt-1">
                        Each design features distinct layout geometry, typography, and mood. Switching presets preserves all your products and inventory data.
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($presets as $key => $preset)
                    @php
                        $isActive = ($activePresetKey === $key);
                    @endphp
                    <div class="rounded-3xl border transition-all duration-300 overflow-hidden flex flex-col justify-between {{ $isActive ? 'border-amber-400 bg-amber-500/[0.02] shadow-lg ring-2 ring-amber-400/30 dark:border-amber-400/80' : 'border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:shadow-md hover:border-slate-300' }}">
                        
                        <!-- Preset Card Top Canvas Preview -->
                        <div class="p-6 relative overflow-hidden" style="background-color: {{ $preset['preview_bg'] }}; color: {{ $preset['preview_text'] }}; border-bottom: 1px solid rgba(0,0,0,0.06);">
                            <div class="flex items-center justify-between mb-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider"
                                      style="background-color: {{ $preset['preview_accent'] }}22; color: {{ $preset['preview_accent'] }};">
                                    {{ $preset['badge'] }}
                                </span>
                                @if($isActive)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-stone-900 text-white shadow-xs">
                                        Active Preset
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-2xl font-bold font-heading mb-1" style="font-family: '{{ $preset['display_font'] }}', serif;">
                                {{ $preset['name'] }}
                            </h3>
                            <p class="text-xs opacity-75 font-medium mb-4">
                                {{ $preset['tagline'] }}
                            </p>

                            <!-- Micro Visual Layout Wireframe Representation -->
                            <div class="p-3 rounded-xl border bg-white/70 dark:bg-black/20 space-y-2 text-[10px]" style="border-color: rgba(0,0,0,0.08);">
                                <div class="flex items-center justify-between pb-1.5 border-b border-black/5">
                                    <span class="font-bold">{{ $vendor->shop_name }}</span>
                                    <span class="opacity-60">Cart (0)</span>
                                </div>
                                <div class="h-10 rounded-lg flex items-center justify-center font-bold text-xs uppercase tracking-wider"
                                     style="background-color: {{ $preset['preview_accent'] }}20; color: {{ $preset['preview_accent'] }};">
                                    {{ $key === 'signature' ? 'HERO DROP 2026' : 'EDITORIAL SHOWCASE' }}
                                </div>
                                <div class="grid grid-cols-3 gap-1.5 pt-1">
                                    <div class="aspect-[3/4] rounded bg-black/5"></div>
                                    <div class="aspect-[3/4] rounded bg-black/5"></div>
                                    <div class="aspect-[3/4] rounded bg-black/5"></div>
                                </div>
                            </div>

                            <!-- Typography & Palette Badges -->
                            <div class="mt-4 pt-3 border-t border-black/5 flex items-center justify-between text-[11px] opacity-80">
                                <span>Font: <strong>{{ $preset['display_font'] }}</strong></span>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $preset['preview_accent'] }};"></span>
                                    <span class="w-3.5 h-3.5 rounded-full border border-black/20" style="background-color: {{ $preset['preview_card_bg'] }};"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Preset Description & Actions Bottom -->
                        <div class="p-6 space-y-4 flex flex-col justify-between flex-1">
                            <div>
                                <p class="text-xs text-slate-600 dark:text-zinc-400 leading-relaxed">
                                    {{ $preset['description'] }}
                                </p>
                                <div class="mt-3 text-[11px] text-slate-400 dark:text-zinc-500">
                                    <strong>Best for:</strong> {{ $preset['best_for'] }}
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-100 dark:border-zinc-800 flex items-center gap-3">
                                @if($isActive)
                                    <a href="{{ route('vendor.design.editor') }}" 
                                       class="w-full py-2.5 px-4 rounded-xl bg-stone-900 text-white hover:bg-stone-800 text-xs font-bold text-center transition shadow-sm">
                                        Customize in Editor
                                    </a>
                                @else
                                    <button type="button" 
                                            onclick="applyPresetToDraft('{{ $key }}', '{{ $preset['name'] }}')"
                                            class="w-full py-2.5 px-4 rounded-xl border border-slate-300 dark:border-zinc-700 hover:border-slate-800 text-slate-800 dark:text-zinc-200 text-xs font-bold text-center transition hover:bg-slate-50 dark:hover:bg-zinc-800">
                                        Apply to Draft
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>

        <!-- Section Architecture & Live Store Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 flex items-center justify-center text-xl">
                    📑
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                    Active Sections
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">
                    Currently configured with <strong>{{ count($currentConfig['sections'] ?? []) }} sections</strong> on your homepage.
                </p>
                <div class="pt-2">
                    <a href="{{ route('vendor.design.editor') }}" class="text-xs font-bold text-amber-600 hover:underline">
                        Edit sections in builder →
                    </a>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-blue-500/10 text-blue-600 flex items-center justify-center text-xl">
                    🛍️
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                    Connected Products
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">
                    Your storefront dynamically renders <strong>{{ $vendor->products()->where('status', 'active')->count() }} live items</strong> with real inventory.
                </p>
                <div class="pt-2">
                    <a href="{{ route('vendor.products.index') }}" class="text-xs font-bold text-blue-600 hover:underline">
                        Manage products catalog →
                    </a>
                </div>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 shadow-2xs space-y-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center text-xl">
                    🚀
                </div>
                <h3 class="text-base font-bold text-slate-900 dark:text-white font-heading">
                    Instant Publishing
                </h3>
                <p class="text-xs text-slate-500 dark:text-zinc-400">
                    Last published: <strong>{{ $configRecord?->last_published_at ? $configRecord->last_published_at->diffForHumans() : 'Never' }}</strong>.
                </p>
                <div class="pt-2">
                    <a href="{{ route('storefront.show', $vendor->slug) }}" target="_blank" class="text-xs font-bold text-emerald-600 hover:underline">
                        Open public storefront ↗
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Apply Preset Confirmation Modal -->
    <div id="apply-preset-modal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4 hidden">
        <div class="bg-white dark:bg-zinc-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 dark:border-zinc-800 space-y-4">
            <h3 class="text-lg font-bold text-stone-900 dark:text-white font-heading">
                Apply <span id="modal-preset-name">Preset</span>?
            </h3>
            <p class="text-xs text-stone-600 dark:text-zinc-400 leading-relaxed">
                This will set up your draft storefront with this design preset's layout, fonts, and signature section hierarchy. Your live store will remain unchanged until you click Publish in the visual editor.
            </p>
            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" 
                        onclick="document.getElementById('apply-preset-modal').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-xs font-semibold text-stone-600 hover:bg-stone-100 dark:hover:bg-zinc-800">
                    Cancel
                </button>
                <button type="button" 
                        id="confirm-apply-btn"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold bg-stone-900 text-white hover:bg-stone-800 shadow-sm">
                    Apply &amp; Open Editor
                </button>
            </div>
        </div>
    </div>

    <script>
        let targetPresetKey = null;

        function applyPresetToDraft(key, name) {
            targetPresetKey = key;
            document.getElementById('modal-preset-name').textContent = name;
            document.getElementById('apply-preset-modal').classList.remove('hidden');
        }

        document.getElementById('confirm-apply-btn').addEventListener('click', function() {
            if (!targetPresetKey) return;
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Applying...';

            fetch('{{ route('vendor.design.preset') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ preset: targetPresetKey })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    window.location.href = '{{ route('vendor.design.editor') }}';
                } else {
                    alert(data.message || 'Failed to apply preset');
                    btn.disabled = false;
                    btn.textContent = 'Apply & Open Editor';
                }
            })
            .catch(err => {
                console.error(err);
                btn.disabled = false;
                btn.textContent = 'Apply & Open Editor';
            });
        });
    </script>
</x-layouts.vendor>

