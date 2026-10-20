<x-layouts.admin title="Mobile Panel & Navigation Studio">
    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        editingItem: null,
        activeTab: 'mobile_bottom',
        previewItems: {{ Js::from($bottomItems) }},
        
        openEdit(item) {
            this.editingItem = JSON.parse(JSON.stringify(item));
            this.editModalOpen = true;
        },

        moveItem(index, direction) {
            const newIndex = index + direction;
            if (newIndex < 0 || newIndex >= this.previewItems.length) return;
            const temp = this.previewItems[index];
            this.previewItems[index] = this.previewItems[newIndex];
            this.previewItems[newIndex] = temp;
            this.saveOrder();
        },

        saveOrder() {
            const ids = this.previewItems.map(i => i.id);
            fetch('{{ route('admin.navigation.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').getAttribute('content')
                },
                body: JSON.stringify({ items: ids })
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    // updated
                }
            });
        }
    }" class="space-y-8 pb-16">

        <!-- Top Breadcrumb & Page Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 rounded-3xl border border-indigo-900/50 shadow-2xl text-white relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                        Live Storefront Control
                    </span>
                    <span class="text-xs text-slate-400 font-mono">v2.5 Dynamic Nav</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-2.5 font-sans">
                    <span>📱 Mobile Panel &amp; Navigation Studio</span>
                </h1>
                <p class="text-sm text-slate-300 max-w-2xl mt-1">
                    Manage the bottom navigation bar and mobile app menu. Add custom items, change icons, upload images, set badges, and link anywhere across your multi-vertical ecosystem.
                </p>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <form action="{{ route('admin.navigation.reset') }}" method="POST" onsubmit="return confirm('Reset mobile bottom navigation to the 5 default platform tabs?');">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 rounded-2xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold border border-slate-700 transition flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset Defaults</span>
                    </button>
                </form>

                <button @click="createModalOpen = true" 
                        class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Navigation Item</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-semibold flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Main Layout: Editor & Live Mobile Preview -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left 8 Columns: Navigation Items List & Management -->
            <div class="lg:col-span-8 space-y-6">
                
                <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-6 shadow-xl">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-white font-sans flex items-center gap-2">
                                <span>Active Navigation Tabs</span>
                                <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    {{ $bottomItems->count() }} Tabs
                                </span>
                            </h2>
                            <p class="text-xs text-slate-400 mt-0.5">These items appear in the mobile bottom navigation bar on all phones and tablets.</p>
                        </div>
                    </div>

                    @if($bottomItems->isEmpty())
                        <div class="py-12 text-center text-slate-400">
                            <p class="text-sm">No custom navigation items yet.</p>
                            <button @click="createModalOpen = true" class="mt-3 text-xs text-amber-400 hover:underline font-bold">+ Create your first item</button>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($bottomItems as $index => $item)
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700 transition-all group">
                                    
                                    <!-- Left: Drag / Order Controls & Icon Preview -->
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="flex flex-col items-center gap-1 shrink-0">
                                            <button @click="moveItem({{ $index }}, -1)" 
                                                    class="p-1 rounded text-slate-500 hover:text-white hover:bg-slate-800 transition disabled:opacity-30" 
                                                    {{ $index === 0 ? 'disabled' : '' }} title="Move Up">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                            </button>
                                            <span class="text-[10px] font-mono font-bold text-slate-400">#{{ $item->sort_order }}</span>
                                            <button @click="moveItem({{ $index }}, 1)" 
                                                    class="p-1 rounded text-slate-500 hover:text-white hover:bg-slate-800 transition disabled:opacity-30" 
                                                    {{ $index === $bottomItems->count() - 1 ? 'disabled' : '' }} title="Move Down">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                        </div>

                                        <!-- Icon / Image Preview -->
                                        <div class="w-12 h-12 rounded-2xl bg-slate-900 border border-slate-700/80 flex items-center justify-center shrink-0 overflow-hidden shadow-inner">
                                            @if($item->icon_type === 'image' && $item->image_url)
                                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-7 h-7 object-contain rounded-lg">
                                            @elseif($item->icon_type === 'emoji' && $item->icon)
                                                <span class="text-xl">{{ $item->icon }}</span>
                                            @elseif($item->icon)
                                                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    {!! $item->icon !!}
                                                </svg>
                                            @else
                                                <span class="text-xs font-bold text-slate-500">NAV</span>
                                            @endif
                                        </div>

                                        <!-- Title, URL & Badge -->
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-sm font-bold text-white truncate font-sans">{{ $item->title }}</h3>
                                                @if($item->badge)
                                                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider bg-{{ $item->badge_color ?? 'rose' }}-500/20 text-{{ $item->badge_color ?? 'rose' }}-300 border border-{{ $item->badge_color ?? 'rose' }}-500/30">
                                                        {{ $item->badge }}
                                                    </span>
                                                @endif
                                                @if(!$item->is_active)
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-800 text-slate-400">Hidden</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-slate-400 mt-0.5">
                                                <span class="font-mono text-indigo-400 truncate max-w-xs">{{ $item->url }}</span>
                                                @if($item->subtitle)
                                                    <span class="text-slate-500">• {{ $item->subtitle }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Action Buttons -->
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button @click="openEdit({{ Js::from($item) }})" 
                                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-700/60 transition flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Edit</span>
                                        </button>

                                        <form action="{{ route('admin.navigation.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this navigation item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 border border-rose-500/20 transition" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Guidance & Best Practices Card -->
                <div class="p-6 rounded-3xl bg-slate-900/50 border border-slate-800 text-xs text-slate-300 space-y-2">
                    <div class="font-bold text-sm text-white flex items-center gap-2">
                        <span>💡 Pro-Tips for Maximum Conversion</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-400 pl-1">
                        <li><strong>Recommended Length:</strong> Mobile screens look best with <strong>4 to 5 items</strong> in the bottom bar.</li>
                        <li><strong>Icons:</strong> You can either use pure SVG icons, upload custom image logos (PNG/WebP/SVG), or use vibrant emojis.</li>
                        <li><strong>Shorts / Reels:</strong> Highlight the center tab with a <span class="text-rose-400 font-bold">LIVE</span> badge or elevated button to maximize discovery.</li>
                        <li><strong>Instant Updates:</strong> Any additions or edits you make here are cached with high performance and update the live mobile storefront instantly.</li>
                    </ul>
                </div>

            </div>

            <!-- Right 4 Columns: Live Phone Screen Preview -->
            <div class="lg:col-span-4 sticky top-6">
                <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-6 shadow-2xl flex flex-col items-center">
                    
                    <div class="flex items-center justify-between w-full mb-4">
                        <span class="text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">Live Mobile Preview</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-300">Syncs Live</span>
                    </div>

                    <!-- Smartphone Device Frame -->
                    <div class="w-full max-w-[310px] rounded-[40px] bg-black p-3.5 shadow-2xl border-4 border-slate-800 relative">
                        <!-- Top Speaker / Dynamic Island -->
                        <div class="w-24 h-4 bg-zinc-900 rounded-full mx-auto mb-3 flex items-center justify-center">
                            <span class="w-2 h-2 rounded-full bg-zinc-800 mr-2"></span>
                            <span class="w-8 h-1 bg-zinc-800 rounded-full"></span>
                        </div>

                        <!-- Simulated Mobile Screen Content -->
                        <div class="w-full h-[450px] bg-gradient-to-b from-slate-900 to-slate-950 rounded-[28px] overflow-hidden flex flex-col justify-between border border-zinc-800/80 p-3 relative">
                            
                            <!-- Mock Header -->
                            <div class="flex items-center justify-between pt-1 pb-2 border-b border-zinc-800/80">
                                <span class="font-black text-sm text-white">Marble<span class="text-emerald-400">Mandi</span></span>
                                <div class="w-6 h-6 rounded-lg bg-zinc-800 flex items-center justify-center text-zinc-400 text-xs">🔍</div>
                            </div>

                            <!-- Mock Content Body -->
                            <div class="space-y-2 py-4">
                                <div class="w-full h-24 rounded-2xl bg-gradient-to-r from-blue-700 to-indigo-800 p-2.5 flex flex-col justify-between text-white">
                                    <span class="text-[9px] uppercase font-bold tracking-wider text-blue-200">Exclusive Mandi</span>
                                    <span class="text-xs font-black">Makrana Marble 30% OFF</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="h-20 rounded-xl bg-zinc-800/60 p-2 border border-zinc-700/40"></div>
                                    <div class="h-20 rounded-xl bg-zinc-800/60 p-2 border border-zinc-700/40"></div>
                                </div>
                            </div>

                            <!-- Real Live Rendered Bottom Bar in Phone Preview! -->
                            <div class="mt-auto -mx-3 -mb-3 bg-zinc-950/95 backdrop-blur-xl border-t border-zinc-800/80 py-2 px-2 shadow-2xl">
                                <div class="grid grid-cols-{{ max(count($bottomItems), 1) }} items-center justify-between text-center">
                                    @foreach($bottomItems as $item)
                                        <div class="flex flex-col items-center justify-center py-0.5 cursor-pointer group">
                                            <div class="relative">
                                                @if($item->icon_type === 'image' && $item->image_url)
                                                    <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-4 h-4 object-contain">
                                                @elseif($item->icon_type === 'emoji' && $item->icon)
                                                    <span class="text-sm leading-none">{{ $item->icon }}</span>
                                                @elseif($item->icon)
                                                    <svg class="w-4 h-4 text-zinc-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        {!! $item->icon !!}
                                                    </svg>
                                                @else
                                                    <div class="w-4 h-4 rounded-full bg-zinc-800 text-zinc-400 text-[8px] flex items-center justify-center">★</div>
                                                @endif

                                                @if($item->badge)
                                                    <span class="absolute -top-1 -right-2 bg-{{ $item->badge_color ?? 'rose' }}-500 text-white text-[7px] font-black px-1 rounded-full leading-tight">
                                                        {{ $item->badge }}
                                                    </span>
                                                @endif
                                            </div>
                                            <span class="text-[8px] font-bold text-zinc-400 group-hover:text-white tracking-tight mt-0.5 truncate max-w-[50px]">
                                                {{ $item->title }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                    </div>

                    <p class="text-[11px] text-slate-500 mt-4 text-center">Simulated Mobile Safari / Chrome rendering with active safe-area inset.</p>
                </div>
            </div>

        </div>

        <!-- CREATE MODAL -->
        <div x-show="createModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            
            <div @click.away="createModalOpen = false" 
                 class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 text-white shadow-2xl space-y-6">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white font-sans flex items-center gap-2">
                        <span>✨ Add Mobile Navigation Item</span>
                    </h3>
                    <button @click="createModalOpen = false" class="text-slate-400 hover:text-white p-1">✕</button>
                </div>

                <form action="{{ route('admin.navigation.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="group" value="mobile_bottom">

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Item Title / Text *</label>
                        <input type="text" name="title" required placeholder="e.g. Marble Mandi, Deals, Shorts" 
                               class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-amber-400 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Target Link / URL *</label>
                        <div class="space-y-2">
                            <input type="text" name="url" id="create-url-input" required placeholder="e.g. /catalog, /food, https://..." 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-amber-400 focus:outline-none">
                            <div class="flex flex-wrap gap-1.5 text-[10px]">
                                <span class="text-slate-400 self-center">Presets:</span>
                                <button type="button" onclick="document.getElementById('create-url-input').value = '/'" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Home (/)</button>
                                <button type="button" onclick="document.getElementById('create-url-input').value = '/catalog'" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Catalog</button>
                                <button type="button" onclick="document.getElementById('create-url-input').value = '/shorts'" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Shorts</button>
                                <button type="button" onclick="document.getElementById('create-url-input').value = '/food'" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Food Hub</button>
                                <button type="button" onclick="document.getElementById('create-url-input').value = '/services'" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Services</button>
                                <button type="button" onclick="document.getElementById('create-url-input').value = '/wishlist'" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Wishlist</button>
                                <button type="button" onclick="document.getElementById('create-url-input').value = '/account'" class="px-2 py-0.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-300">Account</button>
                            </div>
                        </div>
                    </div>

                    <div x-data="{ iconType: 'svg' }">
                        <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Icon Representation</label>
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-950 border border-slate-700 cursor-pointer text-xs font-semibold">
                                <input type="radio" name="icon_type" value="svg" x-model="iconType" class="text-amber-500">
                                <span>SVG Paths</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-950 border border-slate-700 cursor-pointer text-xs font-semibold">
                                <input type="radio" name="icon_type" value="image" x-model="iconType" class="text-amber-500">
                                <span>Image / Logo</span>
                            </label>
                            <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-950 border border-slate-700 cursor-pointer text-xs font-semibold">
                                <input type="radio" name="icon_type" value="emoji" x-model="iconType" class="text-amber-500">
                                <span>Emoji</span>
                            </label>
                        </div>

                        <!-- SVG Option -->
                        <div x-show="iconType === 'svg'" class="space-y-1">
                            <textarea name="icon" rows="2" placeholder='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="..."/>' 
                                      class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:border-amber-400 focus:outline-none"></textarea>
                            <p class="text-[10px] text-slate-400">Paste standard Heroicons or Feather SVG inner path.</p>
                        </div>

                        <!-- Image Upload Option -->
                        <div x-show="iconType === 'image'" class="space-y-2">
                            <input type="file" name="image_file" accept="image/*" 
                                   class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200 hover:file:bg-slate-700">
                            <div class="text-[11px] text-slate-400">Or paste direct Image URL:</div>
                            <input type="text" name="image_url" placeholder="https://example.com/icon.png" 
                                   class="w-full px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                        </div>

                        <!-- Emoji Option -->
                        <div x-show="iconType === 'emoji'" class="space-y-1">
                            <input type="text" name="icon" placeholder="🏛️, 🛒, 🎥, 💎, ⚡" 
                                   class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-lg focus:border-amber-400 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Badge Text (Optional)</label>
                            <input type="text" name="badge" placeholder="e.g. HOT, LIVE, NEW, 50%" 
                                   class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-amber-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Badge Color</label>
                            <select name="badge_color" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-amber-400 focus:outline-none">
                                <option value="emerald">Emerald (Green)</option>
                                <option value="rose">Rose (Red)</option>
                                <option value="amber">Amber (Gold)</option>
                                <option value="indigo">Indigo (Purple)</option>
                                <option value="blue">Blue</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded text-emerald-500 w-4 h-4 bg-slate-950 border-slate-700">
                            <span class="text-xs font-bold text-slate-300">Visible on Storefront</span>
                        </label>
                        <span class="text-xs text-slate-400">Order: Auto-added to end</span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                        <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-lg">Save &amp; Publish</button>
                    </div>

                </form>

            </div>
        </div>

        <!-- EDIT MODAL -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-4">
            
            <template x-if="editingItem">
                <div @click.away="editModalOpen = false" 
                     class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 text-white shadow-2xl space-y-6">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <h3 class="text-lg font-bold text-white font-sans flex items-center gap-2">
                            <span>✏️ Edit: <span x-text="editingItem.title"></span></span>
                        </h3>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-white p-1">✕</button>
                    </div>

                    <form :action="'/admin/navigation/' + editingItem.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="group" x-model="editingItem.group">

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Item Title / Text *</label>
                            <input type="text" name="title" required x-model="editingItem.title" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-amber-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Target Link / URL *</label>
                            <input type="text" name="url" required x-model="editingItem.url" 
                                   class="w-full px-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm focus:border-amber-400 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Icon Representation</label>
                            <div class="grid grid-cols-3 gap-2 mb-3">
                                <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-950 border border-slate-700 cursor-pointer text-xs font-semibold">
                                    <input type="radio" name="icon_type" value="svg" x-model="editingItem.icon_type" class="text-amber-500">
                                    <span>SVG Paths</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-950 border border-slate-700 cursor-pointer text-xs font-semibold">
                                    <input type="radio" name="icon_type" value="image" x-model="editingItem.icon_type" class="text-amber-500">
                                    <span>Image / Logo</span>
                                </label>
                                <label class="flex items-center gap-2 p-2 rounded-xl bg-slate-950 border border-slate-700 cursor-pointer text-xs font-semibold">
                                    <input type="radio" name="icon_type" value="emoji" x-model="editingItem.icon_type" class="text-amber-500">
                                    <span>Emoji</span>
                                </label>
                            </div>

                            <div x-show="editingItem.icon_type === 'svg'" class="space-y-1">
                                <textarea name="icon" rows="2" x-model="editingItem.icon" 
                                          class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white font-mono text-xs focus:border-amber-400 focus:outline-none"></textarea>
                            </div>

                            <div x-show="editingItem.icon_type === 'image'" class="space-y-2">
                                <input type="file" name="image_file" accept="image/*" 
                                       class="w-full text-xs text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-slate-200">
                                <input type="text" name="image_url" x-model="editingItem.image_url" placeholder="https://..." 
                                       class="w-full px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-700 text-white text-xs">
                            </div>

                            <div x-show="editingItem.icon_type === 'emoji'" class="space-y-1">
                                <input type="text" name="icon" x-model="editingItem.icon" 
                                       class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-lg">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Badge Text</label>
                                <input type="text" name="badge" x-model="editingItem.badge" placeholder="e.g. LIVE" 
                                       class="w-full px-4 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-300 uppercase mb-1">Badge Color</label>
                                <select name="badge_color" x-model="editingItem.badge_color" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-700 text-white text-sm">
                                    <option value="emerald">Emerald</option>
                                    <option value="rose">Rose</option>
                                    <option value="amber">Amber</option>
                                    <option value="indigo">Indigo</option>
                                    <option value="blue">Blue</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" :checked="editingItem.is_active" class="rounded text-emerald-500 w-4 h-4 bg-slate-950 border-slate-700">
                                <span class="text-xs font-bold text-slate-300">Visible on Storefront</span>
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-800">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black shadow-lg">Save Changes</button>
                        </div>

                    </form>
                </div>
            </template>
        </div>

    </div>
</x-layouts.admin>
