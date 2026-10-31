<x-layouts.admin title="Floating 6-in-1 Launcher Studio">
    <div x-data="{
        launcherOpen: true,
        items: {{ Js::from($items) }},
        activeItemIndex: 0
    }" class="space-y-6 pb-16">

        <!-- Top Header & Primary Actions -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-zinc-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="text-[10px] font-mono uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-zinc-900 text-white">
                        Floating Navigation Studio
                    </span>
                    <span class="text-xs text-zinc-400">• Universal Radial 6-in-1 Menu</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-zinc-950 tracking-tight">
                    Floating 6-in-1 Launcher Studio
                </h2>
                <p class="text-xs sm:text-sm text-zinc-500 mt-0.5">
                    Customize labels, destination URLs, icons, gradients, and visibility for the universal draggable radial fan menu.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <form action="{{ route('admin.floating_launcher.reset') }}" method="POST" onsubmit="return confirm('Reset all 6 floating buttons to factory defaults?')">
                    @csrf
                    <button type="submit"
                            class="px-3.5 py-2 rounded-lg bg-zinc-50 hover:bg-zinc-100 text-zinc-700 hover:text-zinc-950 text-xs font-semibold border border-zinc-200/80 transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset Defaults</span>
                    </button>
                </form>

                <button type="submit" form="floating-launcher-form"
                        class="px-5 py-2 rounded-lg bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-semibold tracking-wide transition shadow-xs flex items-center gap-1.5 cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Launcher</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-xs sm:text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded font-mono font-bold">Live Synced</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left Side: Items Edit Form (8 cols) -->
            <div class="lg:col-span-8 space-y-4">
                <form id="floating-launcher-form" action="{{ route('admin.floating_launcher.save') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="space-y-4">
                        @foreach($items as $index => $item)
                            <div class="bg-white rounded-2xl p-5 border border-zinc-200/80 shadow-xs space-y-4 transition hover:border-zinc-300">
                                
                                <div class="flex items-center justify-between pb-3 border-b border-zinc-100">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-6 h-6 rounded-lg bg-zinc-950 text-white text-[11px] font-black flex items-center justify-center font-mono">
                                            {{ $index + 1 }}
                                        </span>
                                        <div>
                                            <h3 class="text-sm font-bold text-zinc-950">
                                                Slot {{ $index + 1 }}: <span x-text="items[{{ $index }}]?.name || '{{ $item['name'] }}'"></span>
                                            </h3>
                                            <span class="text-[10px] text-zinc-400 font-mono">ID: {{ $item['id'] }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <span class="text-xs font-semibold text-zinc-700">Active</span>
                                            <input type="checkbox" name="items[{{ $index }}][is_active]" value="1"
                                                   {{ !empty($item['is_active']) ? 'checked' : '' }}
                                                   @change="items[{{ $index }}].is_active = $event.target.checked"
                                                   class="w-4 h-4 text-zinc-950 rounded border-zinc-300 focus:ring-zinc-950">
                                        </label>
                                    </div>
                                </div>

                                <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item['id'] }}">

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                    <div>
                                        <label class="block font-semibold text-zinc-800 mb-1">Button Short Name (Max 10 chars)</label>
                                        <input type="text" name="items[{{ $index }}][name]" value="{{ $item['name'] }}"
                                               @input="items[{{ $index }}].name = $event.target.value"
                                               class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-bold focus:ring-2 focus:ring-zinc-950">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-zinc-800 mb-1">Hover Tooltip Label</label>
                                        <input type="text" name="items[{{ $index }}][label]" value="{{ $item['label'] }}"
                                               @input="items[{{ $index }}].label = $event.target.value"
                                               class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-medium focus:ring-2 focus:ring-zinc-950">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                                    <div>
                                        <label class="block font-semibold text-zinc-800 mb-1">Target Link URL</label>
                                        <input type="text" name="items[{{ $index }}][url]" value="{{ $item['url'] }}"
                                               @input="items[{{ $index }}].url = $event.target.value"
                                               class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px] focus:ring-2 focus:ring-zinc-950">
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-zinc-800 mb-1">Top Sheen Gradient (Tailwind classes)</label>
                                        <input type="text" name="items[{{ $index }}][color]" value="{{ $item['color'] }}"
                                               @input="items[{{ $index }}].color = $event.target.value"
                                               class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px] focus:ring-2 focus:ring-zinc-950">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-1">
                                    <div>
                                        <label class="block font-semibold text-zinc-800 mb-1">Custom Image / Icon File (PNG / SVG / WebP)</label>
                                        <input type="file" name="items[{{ $index }}][image_file]" accept="image/*"
                                               class="w-full text-xs text-zinc-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-zinc-100 file:text-zinc-800 hover:file:bg-zinc-200 cursor-pointer">
                                        @if(!empty($item['image_url']))
                                            <div class="mt-2 flex items-center gap-2">
                                                <img src="{{ $item['image_url'] }}" class="w-8 h-8 object-contain rounded bg-zinc-900 p-1 border">
                                                <span class="text-[11px] text-zinc-500 font-mono">Custom icon active</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <label class="block font-semibold text-zinc-800 mb-1">Or Direct Icon / Image URL</label>
                                        <input type="text" name="items[{{ $index }}][image_url]" value="{{ $item['image_url'] ?? '' }}" placeholder="https://... or /storage/..."
                                               @input="items[{{ $index }}].image_url = $event.target.value"
                                               class="w-full px-3.5 py-2.5 rounded-lg border border-zinc-300 text-zinc-900 bg-white font-mono text-[11px] focus:ring-2 focus:ring-zinc-950">
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3">
                        <button type="submit"
                                class="px-6 py-2.5 rounded-lg bg-zinc-950 hover:bg-zinc-800 text-white text-xs font-semibold tracking-wide transition shadow-xs flex items-center gap-2 cursor-pointer active:scale-95">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Save &amp; Publish Launcher</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Side: Live Visual Radial Simulation Box (4 cols sticky) -->
            <div class="lg:col-span-4 sticky top-6 space-y-4">
                <div class="bg-zinc-950 text-white rounded-3xl p-6 border border-zinc-800 shadow-2xl relative overflow-hidden min-h-[460px] flex flex-col justify-between">
                    
                    <div>
                        <div class="flex items-center justify-between pb-3 border-b border-zinc-800/80">
                            <div>
                                <span class="text-[9px] font-mono uppercase tracking-widest text-amber-400 font-bold block">Live Geometry</span>
                                <h4 class="text-sm font-bold text-white mt-0.5">Radial Semicircle Fan</h4>
                            </div>
                            <button type="button" @click="launcherOpen = !launcherOpen"
                                    class="px-2.5 py-1 rounded-full text-[10px] font-bold border border-white/20 transition cursor-pointer"
                                    :class="launcherOpen ? 'bg-amber-400 text-black' : 'bg-zinc-800 text-white'">
                                <span x-text="launcherOpen ? 'Fan Expanded' : 'Fan Retracted'"></span>
                            </button>
                        </div>

                        <p class="text-[11px] text-zinc-400 mt-2 leading-relaxed">
                            Simulating the right-side dynamic trajectory. When user clicks the launcher trigger, 6 buttons fan out smoothly along a 95px radius arc.
                        </p>
                    </div>

                    <!-- Canvas Area -->
                    <div class="relative w-full h-[260px] flex items-center justify-center my-4 overflow-hidden rounded-2xl bg-zinc-900/60 border border-zinc-800">
                        
                        <!-- Radial Items Container -->
                        <div x-show="launcherOpen" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-50"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="absolute inset-0 pointer-events-none flex items-center justify-center">
                            
                            <template x-for="(item, idx) in items" :key="item.id || idx">
                                <div class="absolute pointer-events-auto transition-all duration-300 ease-out"
                                     :style="(() => {
                                        const radius = 95;
                                        const baseAngle = 95 + (idx * 34);
                                        const rad = (baseAngle * Math.PI) / 180;
                                        const x = Math.round(Math.cos(rad) * radius);
                                        const y = Math.round(Math.sin(rad) * radius);
                                        return 'transform: translate3d(' + x + 'px, ' + y + 'px, 0);';
                                     })()">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-900/90 border border-white/20 shadow-xl flex flex-col items-center justify-center relative overflow-hidden"
                                         :class="item.is_active ? 'opacity-100' : 'opacity-30 grayscale'">
                                        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r" :class="item.color || 'from-amber-400 to-yellow-500'"></div>
                                        
                                        <template x-if="item.image_url">
                                            <img :src="item.image_url" class="w-4 h-4 object-contain">
                                        </template>
                                        <template x-if="!item.image_url">
                                            <span class="text-[10px] font-black text-amber-300 uppercase leading-none" x-text="item.name?.substring(0,2) || 'K7'"></span>
                                        </template>
                                        
                                        <span class="text-[7.5px] font-bold text-white/90 leading-none mt-1 truncate max-w-[40px]" x-text="item.name"></span>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Center Trigger Launcher Button -->
                        <button type="button" @click="launcherOpen = !launcherOpen"
                                class="w-13 h-13 rounded-full bg-black text-white shadow-2xl border border-white/30 backdrop-blur-2xl flex items-center justify-center relative z-20 cursor-pointer hover:scale-105 active:scale-95 transition-transform">
                            <span x-show="!launcherOpen" class="flex flex-col items-center justify-center">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="3" width="7" height="7" rx="2"></rect><rect x="14" y="14" width="7" height="7" rx="2"></rect><rect x="3" y="14" width="7" height="7" rx="2"></rect></svg>
                                <span class="text-[7px] font-black uppercase text-white/80 leading-none mt-0.5">NAV</span>
                            </span>
                            <span x-show="launcherOpen" class="text-xl font-black text-amber-300 leading-none">✱</span>
                        </button>
                    </div>

                    <!-- Footer Tip -->
                    <div class="text-[10px] text-zinc-500 font-mono text-center">
                        Active on all pages &bull; Draggable across desktop &amp; mobile
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-layouts.admin>
