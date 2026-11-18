<x-layouts.admin title="Theme Catalogue & Pricing">
<div class="p-6 max-w-7xl mx-auto space-y-6">
    @include('admin.themes._tabs')

    {{-- Summary KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="p-4 rounded-2xl bg-white border border-zinc-200">
            <p class="text-[10px] font-mono uppercase text-zinc-400 font-bold">Total Themes</p>
            <p class="text-2xl font-extrabold text-zinc-950 mt-1">{{ $stats['total_themes'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-zinc-200">
            <p class="text-[10px] font-mono uppercase text-zinc-400 font-bold">Classic Preserved</p>
            <p class="text-2xl font-extrabold text-zinc-950 mt-1">{{ $stats['legacy_themes'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-zinc-200">
            <p class="text-[10px] font-mono uppercase text-zinc-400 font-bold">New Studio Themes</p>
            <p class="text-2xl font-extrabold text-zinc-950 mt-1">{{ $stats['new_themes'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-zinc-200">
            <p class="text-[10px] font-mono uppercase text-zinc-400 font-bold">Pending Requests</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $stats['pending_requests'] }}</p>
        </div>
        <div class="p-4 rounded-2xl bg-white border border-zinc-200">
            <p class="text-[10px] font-mono uppercase text-zinc-400 font-bold">Active Entitlements</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ $stats['active_entitlements'] }}</p>
        </div>
    </div>

    {{-- Global Marketplace Settings: Official WhatsApp Number & Revocation Policy --}}
    <form action="{{ route('admin.themes.settings.update') }}" method="POST" class="rounded-2xl bg-white border border-zinc-200 p-5 grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
        @csrf
        <div class="lg:col-span-5">
            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Official Admin WhatsApp Number (for payment coordination)</label>
            <input type="text" name="whatsapp_number" value="{{ $whatsappNumber }}" placeholder="e.g. +91 98765 43210 (include country code)"
                   class="w-full rounded-xl border border-zinc-200 px-3.5 py-2 text-xs font-medium text-zinc-900">
            <p class="text-[10px] text-zinc-400 mt-1">Used to prefill WhatsApp payment-coordination links with the vendor's request reference. Never hardcoded.</p>
        </div>
        <div class="lg:col-span-5">
            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Entitlement Revocation Policy (Live Storefront Behavior)</label>
            <select name="revocation_policy" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-medium text-zinc-900">
                <option value="fallback_to_velora" {{ $revocationPolicy === 'fallback_to_velora' ? 'selected' : '' }}>Fall back live storefront safely to Classic Velora (preserves vendor draft &amp; products)</option>
                <option value="keep_live_flagged" {{ $revocationPolicy === 'keep_live_flagged' ? 'selected' : '' }}>Keep currently live storefront visible while blocking future edits/re-publishing</option>
            </select>
        </div>
        <div class="lg:col-span-2">
            <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800 cursor-pointer">
                Save Settings
            </button>
        </div>
    </form>

    {{-- Filter Bar --}}
    <form action="{{ route('admin.themes.index') }}" method="GET" class="rounded-2xl bg-white border border-zinc-200 p-4 flex flex-wrap items-center gap-3">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search theme name or ID..." class="flex-1 min-w-[200px] rounded-xl border border-zinc-200 px-3.5 py-2 text-xs">
        <select name="group" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
            <option value="all" {{ $group === 'all' ? 'selected' : '' }}>All Groups (61)</option>
            <option value="new" {{ $group === 'new' ? 'selected' : '' }}>50 New Studio Themes</option>
            <option value="legacy" {{ $group === 'legacy' ? 'selected' : '' }}>11 Classic Themes</option>
        </select>
        <select name="category" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
            <option value="all">All Categories</option>
            @foreach($categories as $k => $lbl)
                <option value="{{ $k }}" {{ $category === $k ? 'selected' : '' }}>{{ $lbl }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold cursor-pointer">Filter</button>
    </form>

    {{-- Themes Management Table --}}
    <div class="rounded-2xl bg-white border border-zinc-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-[10px] font-mono uppercase tracking-wider text-zinc-500">
                    <tr>
                        <th class="py-3 px-4">Theme &amp; ID</th>
                        <th class="py-3 px-3">Individual Price (₹)</th>
                        <th class="py-3 px-3">Category &amp; Status</th>
                        <th class="py-3 px-3">Visibility / Request Controls</th>
                        <th class="py-3 px-3">Usage</th>
                        <th class="py-3 px-4 text-right">Save</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @foreach($themes as $t)
                        @php $pal = $manifests[$t->theme_id]['palette'] ?? ['bg' => '#fafaf9', 'accent' => '#18181b']; @endphp
                        <tr class="hover:bg-zinc-50/60 align-top">
                            <form action="{{ route('admin.themes.catalogue.update', $t) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <td class="py-3 px-4 min-w-[220px]">
                                    <div class="flex items-start gap-2.5">
                                        <span class="w-6 h-6 rounded-lg shrink-0 border border-black/10 mt-0.5" style="background: linear-gradient(135deg, {{ $pal['bg'] }} 50%, {{ $pal['accent'] }} 50%);"></span>
                                        <div class="space-y-1 flex-1">
                                            <input type="text" name="name" value="{{ $t->name }}" class="w-full rounded-lg border border-zinc-200 px-2 py-1 text-xs font-bold text-zinc-950">
                                            <div class="flex items-center gap-1.5 text-[10px] font-mono text-zinc-400">
                                                <span>{{ $t->theme_id }}</span>
                                                <span>·</span>
                                                <span>v{{ $t->version }}</span>
                                                @if($t->is_legacy)
                                                    <span class="px-1.5 py-0.5 rounded bg-zinc-100 text-zinc-700 font-sans font-bold">Classic 11</span>
                                                @endif
                                            </div>
                                            <input type="text" name="description" value="{{ $t->description }}" placeholder="Description..." class="w-full rounded-lg border border-zinc-200 px-2 py-1 text-[11px] text-zinc-600">
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-3 min-w-[140px]">
                                    <input type="number" step="1" min="0" name="price_rupees"
                                           value="{{ $t->price_paise !== null ? (int) round($t->price_paise / 100) : '' }}"
                                           placeholder="Not set"
                                           class="w-28 rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs font-bold text-zinc-900">
                                    <label class="mt-1.5 flex items-center gap-1.5 text-[11px] text-zinc-600">
                                        <input type="checkbox" name="is_free" value="1" {{ $t->is_free ? 'checked' : '' }}>
                                        Free / Included
                                    </label>
                                </td>
                                <td class="py-3 px-3 min-w-[170px] space-y-1.5">
                                    <select name="category" class="w-full rounded-lg border border-zinc-200 px-2 py-1 text-[11px]">
                                        @foreach($categories as $ck => $cl)
                                            <option value="{{ $ck }}" {{ $t->category === $ck ? 'selected' : '' }}>{{ $cl }}</option>
                                        @endforeach
                                    </select>
                                    <select name="implementation_status" class="w-full rounded-lg border border-zinc-200 px-2 py-1 text-[11px] font-semibold">
                                        <option value="ready" {{ $t->implementation_status === 'ready' ? 'selected' : '' }}>QA Ready</option>
                                        <option value="qa_pending" {{ $t->implementation_status === 'qa_pending' ? 'selected' : '' }}>QA Pending</option>
                                        <option value="draft" {{ $t->implementation_status === 'draft' ? 'selected' : '' }}>Draft (Hidden from Req)</option>
                                        <option value="deprecated" {{ $t->implementation_status === 'deprecated' ? 'selected' : '' }}>Deprecated</option>
                                    </select>
                                </td>
                                <td class="py-3 px-3 min-w-[160px] space-y-1.5 text-[11px]">
                                    <label class="flex items-center gap-1.5 font-medium text-zinc-700">
                                        <input type="checkbox" name="is_visible_in_catalogue" value="1" {{ $t->is_visible_in_catalogue ? 'checked' : '' }}>
                                        Catalogue Visible
                                    </label>
                                    <label class="flex items-center gap-1.5 font-medium text-zinc-700">
                                        <input type="checkbox" name="is_available_for_request" value="1" {{ $t->is_available_for_request ? 'checked' : '' }}>
                                        Requests Enabled
                                    </label>
                                </td>
                                <td class="py-3 px-3 whitespace-nowrap text-[11px] text-zinc-600 space-y-0.5">
                                    <div>Entitled: <strong>{{ $t->granted_count }}</strong></div>
                                    <div>Pending Req: <strong>{{ $t->pending_requests_count }}</strong></div>
                                    <div>Live Stores: <strong>{{ $liveCounts[$t->theme_id] ?? 0 }}</strong></div>
                                </td>
                                <td class="py-3 px-4 text-right whitespace-nowrap">
                                    <button type="submit" class="px-3.5 py-2 rounded-xl bg-zinc-950 text-white text-[11px] font-bold hover:bg-zinc-800 cursor-pointer">
                                        Save
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.admin>
