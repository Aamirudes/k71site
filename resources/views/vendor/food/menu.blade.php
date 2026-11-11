<x-layouts.vendor title="Menu Builder & Kitchen Control">
@php
    $isAccepting = (bool) ($settings->is_accepting_food_orders ?? true);
    $statusKey = $operationalStatus['status_key'] ?? ($isAccepting ? 'open' : 'paused');
@endphp

    <div class="max-w-7xl mx-auto space-y-6 pb-16" x-data="foodMenuBuilder()">
        <!-- Header -->
        <div class="bw-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="bw-badge text-[10px] font-mono uppercase tracking-wider px-2.5 py-0.5">
                        K71 Food Kitchen Studio
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-bold px-2.5 py-0.5 rounded-full border {{ $operationalStatus['accepting_orders'] ?? false ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $operationalStatus['accepting_orders'] ?? false ? 'bg-emerald-600' : 'bg-amber-500' }}"></span>
                        {{ $operationalStatus['label'] ?? ($isAccepting ? 'Open & Accepting Orders' : 'Paused') }}
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-black tracking-tight">Menu Builder &amp; Kitchen Control</h1>
                <p class="text-xs text-zinc-500 mt-0.5">
                    Organize menu sections, toggle dish Sold Out state in one click, and control live order intake for {{ $vendor->shop_name }}.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('vendor.products.create') }}" class="bw-btn-black px-4 py-2.5 text-xs font-bold flex items-center gap-1.5 shadow-xs">
                    <span>+ Add New Dish</span>
                </a>
                <a href="{{ route('vendor.settings') }}" class="bw-btn-white px-4 py-2.5 text-xs font-bold">
                    Kitchen &amp; FSSAI Settings
                </a>
                @if($vendor->slug)
                    <a href="{{ route('food.restaurant', $vendor->slug) }}" target="_blank" class="bw-btn-white px-4 py-2.5 text-xs font-bold">
                        View Menu Live ↗
                    </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 text-emerald-900 border border-emerald-200 rounded-2xl text-xs sm:text-sm font-bold flex items-center justify-between shadow-xs">
                <span>✓ {{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 bg-rose-50 text-rose-900 border border-rose-200 rounded-2xl text-xs sm:text-sm font-bold flex items-center justify-between shadow-xs">
                <span>✕ {{ session('error') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 bg-rose-50 text-rose-900 border border-rose-200 rounded-2xl text-xs space-y-1">
                @foreach($errors->all() as $err)
                    <div class="font-semibold">• {{ $err }}</div>
                @endforeach
            </div>
        @endif

        <!-- Live Kitchen Intake Switch Banner -->
        <div class="rounded-3xl p-5 sm:p-6 border-2 transition-all {{ $isAccepting ? 'bg-emerald-50/60 border-emerald-200' : 'bg-amber-50/80 border-amber-300' }}">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $isAccepting ? 'bg-emerald-600 animate-pulse' : 'bg-amber-600' }}"></span>
                        <h2 class="text-base sm:text-lg font-black text-gray-900">
                            {{ $isAccepting ? 'Kitchen is Accepting New Orders' : 'Kitchen Order Intake is Paused' }}
                        </h2>
                    </div>
                    <p class="text-xs text-gray-700">
                        Operating Hours: <strong>{{ $operationalStatus['hours_text'] ?? (($settings->opening_time ?? '09:00') . ' - ' . ($settings->closing_time ?? '23:00')) }}</strong>
                        @if(!empty($operationalStatus['reason']))
                            • <span class="font-semibold text-amber-900">{{ $operationalStatus['reason'] }}</span>
                        @endif
                    </p>
                </div>

                <form action="{{ route('vendor.food.toggle_orders') }}" method="POST" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                    @csrf
                    <input type="hidden" name="is_accepting_food_orders" value="{{ $isAccepting ? '0' : '1' }}">
                    @if($isAccepting)
                        <input type="text"
                               name="paused_reason"
                               value="{{ old('paused_reason') }}"
                               placeholder="Pause reason (optional, e.g. High kitchen rush)"
                               class="rounded-xl border border-gray-300 bg-white px-3 py-2 text-xs text-gray-900 sm:w-64">
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-black shadow-xs transition cursor-pointer">
                            ⏸ Pause Incoming Orders
                        </button>
                    @else
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black shadow-xs transition cursor-pointer">
                            ▶ Resume Accepting Orders
                        </button>
                    @endif
                </form>
            </div>
        </div>

        <!-- KPI Row -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bw-card p-5">
                <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-500 font-bold block">Total Dishes</span>
                <div class="text-3xl font-black text-black font-mono mt-1">{{ $allDishesCount }}</div>
                <p class="text-xs text-zinc-500 mt-0.5">All dishes across your kitchen catalog</p>
            </div>
            <div class="bw-card p-5">
                <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-500 font-bold block">Available Right Now</span>
                <div class="text-3xl font-black text-emerald-700 font-mono mt-1">{{ $availableDishesCount }}</div>
                <p class="text-xs text-zinc-500 mt-0.5">Active dishes open for ordering</p>
            </div>
            <div class="bw-card p-5">
                <span class="text-[10px] font-mono uppercase tracking-wider text-zinc-500 font-bold block">Menu Sections</span>
                <div class="text-3xl font-black text-black font-mono mt-1">{{ $menuCategories->count() }}</div>
                <p class="text-xs text-zinc-500 mt-0.5">Structured storefront menu tabs</p>
            </div>
        </div>

        <!-- Add Menu Section Card -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-sm sm:text-base font-black text-gray-900">+ Create Menu Section</h2>
                    <p class="text-xs text-gray-500">Group your menu into clear sections like Starters, Biryani, Thalis, Breads, or Beverages.</p>
                </div>
            </div>

            <form action="{{ route('vendor.food.categories.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                @csrf
                <div class="sm:col-span-4">
                    <label for="cat-name" class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Section Name *</label>
                    <input id="cat-name" type="text" name="name" required placeholder="e.g. Signature Biryanis" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-semibold">
                </div>
                <div class="sm:col-span-4">
                    <label for="cat-desc" class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Short Subtitle (Optional)</label>
                    <input id="cat-desc" type="text" name="description" placeholder="Slow-cooked in earthen pots" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs">
                </div>
                <div class="sm:col-span-2">
                    <label for="cat-order" class="block text-[11px] font-bold uppercase tracking-wider text-gray-600 mb-1">Sort Order</label>
                    <input id="cat-order" type="number" name="sort_order" min="0" value="{{ $menuCategories->count() + 1 }}" class="w-full rounded-xl border border-gray-300 px-3 py-2 text-xs font-mono">
                </div>
                <div class="sm:col-span-2 flex items-center justify-between sm:justify-end gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                        <span>Visible</span>
                    </label>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold shadow-xs transition cursor-pointer">
                        Add Section
                    </button>
                </div>
            </form>
        </div>

        <!-- Menu Sections & Dishes -->
        @php
            $orderedIds = $menuCategories->pluck('id')->values()->all();
        @endphp

        <div class="space-y-5">
            @forelse($menuCategories as $idx => $cat)
                <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden" x-data="{ editingCat: false }">
                    <div class="px-5 py-4 bg-gray-50/80 border-b border-gray-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-mono font-bold text-gray-400">#{{ $cat->sort_order }}</span>
                                <h3 class="text-base font-black text-gray-900">{{ $cat->name }}</h3>
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border {{ $cat->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-300' }}">
                                    {{ $cat->is_active ? 'Visible on Menu' : 'Hidden' }}
                                </span>
                                <span class="text-[11px] font-semibold text-gray-500">({{ $cat->products->count() }} {{ \Illuminate\Support\Str::plural('dish', $cat->products->count()) }})</span>
                            </div>
                            @if($cat->description)
                                <p class="text-xs text-gray-500">{{ $cat->description }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-1.5">
                            @if($idx > 0)
                                <button type="button"
                                        @click="moveCategory(@js($orderedIds), {{ $idx }}, -1)"
                                        class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-bold cursor-pointer"
                                        title="Move section up">
                                    ↑ Up
                                </button>
                            @endif
                            @if($idx < $menuCategories->count() - 1)
                                <button type="button"
                                        @click="moveCategory(@js($orderedIds), {{ $idx }}, 1)"
                                        class="px-2.5 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-bold cursor-pointer"
                                        title="Move section down">
                                    ↓ Down
                                </button>
                            @endif
                            <button type="button"
                                    @click="editingCat = !editingCat"
                                    class="px-3 py-1 rounded-lg bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 text-xs font-bold cursor-pointer">
                                <span x-text="editingCat ? 'Close' : 'Edit Section'"></span>
                            </button>
                            <form action="{{ route('vendor.food.categories.destroy', $cat) }}" method="POST" onsubmit="return confirm('Delete section \'{{ addslashes($cat->name) }}\'? Dishes inside will move to Uncategorized.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold cursor-pointer">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Inline Edit Category Form -->
                    <div x-show="editingCat" x-cloak class="px-5 py-4 bg-amber-50/40 border-b border-amber-200/60">
                        <form action="{{ route('vendor.food.categories.update', $cat) }}" method="POST" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-end">
                            @csrf
                            @method('PUT')
                            <div class="sm:col-span-4">
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Section Name</label>
                                <input type="text" name="name" required value="{{ $cat->name }}" class="w-full rounded-xl border border-gray-300 px-3 py-1.5 text-xs font-semibold bg-white">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Description</label>
                                <input type="text" name="description" value="{{ $cat->description }}" class="w-full rounded-xl border border-gray-300 px-3 py-1.5 text-xs bg-white">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block text-[11px] font-bold text-gray-600 mb-1">Order</label>
                                <input type="number" name="sort_order" min="0" value="{{ $cat->sort_order }}" class="w-full rounded-xl border border-gray-300 px-3 py-1.5 text-xs font-mono bg-white">
                            </div>
                            <div class="sm:col-span-2 flex items-center justify-between gap-2">
                                <input type="hidden" name="is_active" value="0">
                                <label class="inline-flex items-center gap-1.5 text-xs font-semibold text-gray-700 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" {{ $cat->is_active ? 'checked' : '' }} class="rounded border-gray-300 text-emerald-600">
                                    <span>Visible</span>
                                </label>
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-gray-900 hover:bg-black text-white text-xs font-bold cursor-pointer">
                                    Save
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Dishes in Section -->
                    @if($cat->products->isEmpty())
                        <div class="p-6 text-center text-xs text-gray-500">
                            No dishes assigned to this section yet.
                            <a href="{{ route('vendor.products.create') }}" class="font-bold text-gray-900 underline ml-1">Add a dish</a>
                        </div>
                    @else
                        <div class="divide-y divide-gray-100">
                            @foreach($cat->products as $dish)
                                @include('vendor.food.partials.dish_row', ['dish' => $dish])
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-dashed border-gray-300 p-8 text-center">
                    <p class="text-sm font-bold text-gray-900">No menu sections created yet</p>
                    <p class="text-xs text-gray-500 mt-1">Create your first section above (for example Starters, Mains, or Beverages) to structure your menu cleanly.</p>
                </div>
            @endforelse

            @if($uncategorizedDishes->isNotEmpty())
                <div class="bg-white rounded-3xl border border-amber-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 bg-amber-50/70 border-b border-amber-200/70 flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-amber-950">Uncategorized Dishes ({{ $uncategorizedDishes->count() }})</h3>
                            <p class="text-xs text-amber-800">Dishes below are not yet linked to a kitchen menu section.</p>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach($uncategorizedDishes as $dish)
                            @include('vendor.food.partials.dish_row', ['dish' => $dish])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function foodMenuBuilder() {
            return {
                async moveCategory(ids, index, delta) {
                    const nextIdx = index + delta;
                    if (!Array.isArray(ids) || nextIdx < 0 || nextIdx >= ids.length) return;
                    const updated = [...ids];
                    const moved = updated.splice(index, 1)[0];
                    updated.splice(nextIdx, 0, moved);

                    try {
                        const res = await fetch('{{ route('vendor.food.categories.reorder') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ order: updated })
                        });
                        if (res.ok) {
                            window.location.reload();
                        }
                    } catch (e) {
                        console.error('Reorder failed:', e);
                    }
                }
            };
        }
    </script>
</x-layouts.vendor>

