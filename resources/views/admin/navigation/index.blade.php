<x-layouts.admin title="Mobile Panel & Navigation Studio">
    @php
        $activeItems = $allPanels->get($activeGroup, collect());
    @endphp

    <div x-data="{
        createModalOpen: false,
        editModalOpen: false,
        editingItem: null,
        activeTab: '{{ $activeGroup }}',
        previewItems: {{ Js::from($activeItems->values()) }},
        newImagePreview: null,
        editImagePreview: null,
        newImageUrl: '',
        
        openEdit(item) {
            this.editingItem = JSON.parse(JSON.stringify(item));
            this.editImagePreview = item.image_url || null;
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
                    <span class="text-xs text-slate-400 font-mono">v3.0 Multi-Vertical Docks</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white flex items-center gap-2.5 font-sans">
                    <span>📱 Mobile Panel &amp; Navigation Studio</span>
                </h1>
                <p class="text-sm text-slate-300 max-w-2xl mt-1">
                    Manage the unique, dedicated mobile navigation docks for each marketplace vertical (Universal, Food, Services, Pre-Owned, Shorts) and the global drawer. Fully customizable in real-time.
                </p>
            </div>

            <div class="flex items-center gap-3 relative z-10">
                <form action="{{ route('admin.navigation.reset') }}" method="POST" onsubmit="return confirm('Reset the active panel back to its platform defaults?');">
                    @csrf
                    <input type="hidden" name="group" value="{{ $activeGroup }}">
                    <button type="submit" class="px-4 py-2.5 rounded-2xl bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-bold border border-slate-700 transition flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Reset Panel</span>
                    </button>
                </form>

                <button @click="createModalOpen = true" 
                        class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-slate-950 text-xs font-black shadow-lg shadow-emerald-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0 flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Item to Panel</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm font-semibold flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Panel Group Selector Tabs (Universal + 4 Subdomain Panels + Drawer) -->
        <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-2 shadow-xl">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2">
                @foreach($supportedGroups as $groupKey => $groupInfo)
                    @php
                        $isCurrent = $activeGroup === $groupKey;
                        $count = $allPanels->get($groupKey, collect())->count();
                    @endphp
                    <a href="{{ route('admin.navigation.index', ['group' => $groupKey]) }}"
                       class="flex flex-col items-center justify-center p-3 rounded-2xl transition-all cursor-pointer text-center relative {{ $isCurrent ? 'bg-gradient-to-b from-indigo-600 to-indigo-700 text-white shadow-lg ring-2 ring-indigo-400/30 font-bold' : 'bg-slate-950/60 hover:bg-slate-800/80 text-slate-300 border border-slate-800' }}">
                        <span class="text-2xl mb-1">{{ $groupInfo['icon'] }}</span>
                        <span class="text-xs font-bold leading-tight">{{ $groupInfo['label'] }}</span>
                        <div class="mt-1.5 flex items-center gap-1.5">
                            <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black uppercase {{ $isCurrent ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400' }}">
                                {{ $count }} Items
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Main Layout: Editor & Live Mobile Preview -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left 8 Columns: Navigation Items List & Management -->
            <div class="lg:col-span-8 space-y-6">
                
                <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-6 shadow-xl">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-6">
                        <div class="flex items-center gap-3">
                            <span class="text-3xl">{{ $supportedGroups[$activeGroup]['icon'] ?? '📱' }}</span>
                            <div>
                                <h2 class="text-lg font-bold text-white font-sans flex items-center gap-2">
                                    <span>{{ $supportedGroups[$activeGroup]['label'] ?? 'Panel Items' }}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                        {{ $activeItems->count() }} Tabs
                                    </span>
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    @if($activeGroup === 'mobile_bottom')
                                        Universal bottom panel displayed across the core K71 marketplace.
                                    @elseif($activeGroup === 'mobile_food')
                                        Dedicated Saffron &amp; Flame bottom dock for the Food Delivery vertical.
                                    @elseif($activeGroup === 'mobile_services')
                                        Dedicated Crystal Purple bottom dock for Doorstep Services &amp; Bookings.
                                    @elseif($activeGroup === 'mobile_used')
                                        Dedicated Emerald ReCommerce dock with instant Sell button for Pre-Owned.
                                    @elseif($activeGroup === 'mobile_shorts')
                                        Dedicated OLED Cinema dock with Live Video Shopping highlight for Shorts.
                                    @elseif($activeGroup === 'mobile_drawer')
                                        Side navigation drawer accessible from the mobile top app header.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($activeItems->isEmpty())
                        <div class="py-12 text-center text-slate-400">
                            <p class="text-sm">No items in this panel yet.</p>
                            <button @click="createModalOpen = true" class="mt-3 text-xs text-amber-400 hover:underline font-bold cursor-pointer">+ Add item to {{ $supportedGroups[$activeGroup]['label'] }}</button>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($activeItems as $index => $item)
                                <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 hover:border-slate-700 transition-all group">
                                    
                                    <!-- Left: Drag / Order Controls & Icon Preview -->
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="flex flex-col items-center gap-1 shrink-0">
                                            <button @click="moveItem({{ $index }}, -1)" 
                                                    class="p-1 rounded text-slate-500 hover:text-white hover:bg-slate-800 transition disabled:opacity-30 cursor-pointer" 
                                                    {{ $index === 0 ? 'disabled' : '' }} title="Move Up">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
                                            </button>
                                            <span class="text-[10px] font-mono font-bold text-slate-400">#{{ $item->sort_order }}</span>
                                            <button @click="moveItem({{ $index }}, 1)" 
                                                    class="p-1 rounded text-slate-500 hover:text-white hover:bg-slate-800 transition disabled:opacity-30 cursor-pointer" 
                                                    {{ $index === $activeItems->count() - 1 ? 'disabled' : '' }} title="Move Down">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                        </div>

                                        <!-- Icon / Image Preview -->
                                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-slate-900 border-2 border-slate-700/80 flex items-center justify-center shrink-0 overflow-hidden shadow-inner p-2 group-hover:border-amber-400/50 transition-colors">
                                            @if($item->icon_type === 'image' && $item->image_url)
                                                <img src="{{ $item->image_url }}" alt="{{ $item->title }}" class="w-full h-full object-contain rounded-xl drop-shadow">
                                            @elseif($item->icon_type === 'emoji' && $item->icon)
                                                <span class="text-3xl sm:text-4xl leading-none">{{ $item->icon }}</span>
                                            @elseif($item->icon)
                                                <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold border border-slate-700/60 transition flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            <span>Edit</span>
                                        </button>

                                        <form action="{{ route('admin.navigation.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete this navigation item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 border border-rose-500/20 transition cursor-pointer" title="Delete">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Info Card -->
                <div class="p-6 rounded-3xl bg-slate-900/50 border border-slate-800 text-xs text-slate-300 space-y-2">
                    <div class="font-bold text-sm text-white flex items-center gap-2">
                        <span>💡 Isolated Per-Vertical Navigation Architecture</span>
                    </div>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-400 pl-1">
                        <li>Each of the 4 specialized pages (<code>/food</code>, <code>/services</code>, <code>/used</code>, <code>/shorts</code>) automatically uses its dedicated panel group.</li>
                        <li>All other pages across the K71 marketplace default to the Universal Panel (<code>mobile_bottom</code>).</li>
                        <li>Any edits made here take effect immediately on live production with instantaneous cache invalidation.</li>
                    </ul>
                </div>

            </div>

            <!-- Right 4 Columns: Interactive Live Mobile Device Preview -->
            <div class="lg:col-span-4 sticky top-6 space-y-4">
                <div class="bg-slate-900/90 rounded-3xl border border-slate-800 p-6 shadow-xl">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-ping"></span>
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-300">Live Device Mockup</span>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400 capitalize">{{ str_replace('_', ' ', $activeGroup) }}</span>
                    </div>

                    <!-- Smartphone Bezel Mockup -->
                    <div class="mx-auto max-w-[280px] rounded-[36px] bg-slate-950 border-[6px] border-slate-800 p-2 shadow-2xl relative overflow-hidden">
                        <!-- Top Speaker / Dynamic Island -->
                        <div class="w-20 h-3.5 bg-slate-800 rounded-full mx-auto mb-2"></div>
                        
                        <!-- Screen Canvas -->
                        <div class="h-96 rounded-[24px] bg-slate-900 border border-slate-800/80 p-3 flex flex-col justify-between relative overflow-hidden">
                            <!-- Dummy App Screen Content -->
                            <div class="space-y-2.5 opacity-40">
                                <div class="h-6 w-3/4 bg-slate-800 rounded-lg"></div>
                                <div class="h-20 bg-slate-800 rounded-xl"></div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="h-24 bg-slate-800 rounded-xl"></div>
                                    <div class="h-24 bg-slate-800 rounded-xl"></div>
                                </div>
                            </div>

                            <!-- Mobile Bottom Dock Preview -->
                            <div class="absolute bottom-1 inset-x-1 p-1 rounded-2xl {{ $activeGroup === 'mobile_shorts' ? 'bg-black/90 border border-zinc-800 text-white' : ($activeGroup === 'mobile_food' ? 'bg-white border border-orange-200 text-stone-900' : ($activeGroup === 'mobile_services' ? 'bg-slate-950/95 border border-purple-500/30 text-white' : ($activeGroup === 'mobile_used' ? 'bg-white border border-emerald-200 text-stone-900' : 'bg-white/95 border border-slate-200 text-slate-900'))) }} shadow-xl backdrop-blur-md">
                                <div class="flex items-center justify-around py-1">
                                    <template x-for="(item, idx) in previewItems" :key="item.id || idx">
                                        <div class="flex flex-col items-center justify-center relative px-1">
                                            <!-- Center Elevation check -->
                                            <template x-if="item.badge === 'LIVE' || item.badge === 'SELL'">
                                                <div class="w-8 h-8 rounded-full -mt-3 flex items-center justify-center text-white shadow-md text-xs font-bold {{ $activeGroup === 'mobile_used' ? 'bg-emerald-600' : 'bg-gradient-to-tr from-rose-600 to-amber-500' }}">
                                                    ★
                                                </div>
                                            </template>
                                            <template x-if="item.badge !== 'LIVE' && item.badge !== 'SELL'">
                                                <div class="w-5 h-5 flex items-center justify-center">
                                                    <template x-if="item.icon_type === 'emoji'">
                                                        <span class="text-sm" x-text="item.icon"></span>
                                                    </template>
                                                    <template x-if="item.icon_type === 'image'">
                                                        <img :src="item.image_url" class="w-4 h-4 object-contain">
                                                    </template>
                                                    <template x-if="item.icon_type === 'svg'">
                                                        <div class="w-3.5 h-3.5 text-current" x-html="item.icon"></div>
                                                    </template>
                                                </div>
                                            </template>
                                            <span class="text-[8px] font-bold mt-0.5 max-w-[42px] truncate" x-text="item.title"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Gesture Bar -->
                        <div class="w-24 h-1 bg-slate-700 rounded-full mx-auto mt-2"></div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- CREATE MODAL -->
        <!-- ========================================== -->
        <div x-show="createModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div @click.away="createModalOpen = false" 
                 class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl text-white space-y-5">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>➕ Add Navigation Item</span>
                    </h3>
                    <button @click="createModalOpen = false" class="text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                </div>

                <form action="{{ route('admin.navigation.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Target Panel *</label>
                            <select name="group" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                                @foreach($supportedGroups as $k => $info)
                                    <option value="{{ $k }}" {{ $activeGroup === $k ? 'selected' : '' }}>{{ $info['icon'] }} {{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Explore" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">URL / Route *</label>
                        <input type="text" name="url" required placeholder="/catalog, /food, https://..." class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs font-mono text-indigo-300">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Icon Type *</label>
                            <select name="icon_type" id="new_icon_type" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                                <option value="svg">SVG Code</option>
                                <option value="emoji">Emoji Character</option>
                                <option value="image">Uploaded Image</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Badge (Optional)</label>
                            <input type="text" name="badge" placeholder="e.g. LIVE, NEW, 50%" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Icon SVG or Emoji</label>
                        <textarea name="icon" rows="2" placeholder="SVG <path> or emoji character" class="w-full rounded-xl bg-slate-950 border border-slate-800 p-2.5 text-xs font-mono text-slate-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Upload Icon Image (Optional PNG/SVG)</label>
                        <input type="file" name="image_file" accept="image/*" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-slate-800 file:text-white">
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-700 bg-slate-950 text-emerald-500">
                            <span>Enable Tab Immediately</span>
                        </label>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="createModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:text-white cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-black shadow-md cursor-pointer">Save Item</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

        <!-- ========================================== -->
        <!-- EDIT MODAL -->
        <!-- ========================================== -->
        <div x-show="editModalOpen" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div @click.away="editModalOpen = false" 
                 class="w-full max-w-lg bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl text-white space-y-5"
                 x-show="editingItem">
                
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>✏️ Edit: <span x-text="editingItem?.title"></span></span>
                    </h3>
                    <button @click="editModalOpen = false" class="text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                </div>

                <form :action="'{{ url('admin/navigation') }}/' + editingItem?.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Target Panel *</label>
                            <select name="group" x-model="editingItem.group" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                                @foreach($supportedGroups as $k => $info)
                                    <option value="{{ $k }}">{{ $info['icon'] }} {{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Title *</label>
                            <input type="text" name="title" x-model="editingItem.title" required class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">URL / Route *</label>
                        <input type="text" name="url" x-model="editingItem.url" required class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs font-mono text-indigo-300">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Icon Type *</label>
                            <select name="icon_type" x-model="editingItem.icon_type" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                                <option value="svg">SVG Code</option>
                                <option value="emoji">Emoji Character</option>
                                <option value="image">Uploaded Image</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Badge</label>
                            <input type="text" name="badge" x-model="editingItem.badge" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-2 text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Icon SVG or Emoji</label>
                        <textarea name="icon" x-model="editingItem.icon" rows="2" class="w-full rounded-xl bg-slate-950 border border-slate-800 p-2.5 text-xs font-mono text-slate-200"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Upload New Icon Image</label>
                        <input type="file" name="image_file" accept="image/*" class="w-full rounded-xl bg-slate-950 border border-slate-800 px-3 py-1.5 text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:text-[10px] file:bg-slate-800 file:text-white">
                    </div>

                    <div class="flex items-center justify-between pt-3 border-t border-slate-800">
                        <label class="flex items-center gap-2 text-xs font-bold text-slate-300 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" :checked="editingItem.is_active" class="rounded border-slate-700 bg-slate-950 text-emerald-500">
                            <span>Active (Visible on Mobile)</span>
                        </label>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-800 text-xs font-bold text-slate-300 hover:text-white cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-500 hover:bg-indigo-400 text-white text-xs font-black shadow-md cursor-pointer">Update Item</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-layouts.admin>
