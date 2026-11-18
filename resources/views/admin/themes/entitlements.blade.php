<x-layouts.admin title="Vendor Theme Entitlements">
<div class="p-6 max-w-7xl mx-auto space-y-6">
    @include('admin.themes._tabs')

    {{-- Direct Grant Form --}}
    <form action="{{ route('admin.themes.entitlements.direct_grant') }}" method="POST"
          onsubmit="return confirm('Explicitly grant this vendor entitlement to the selected theme? This will NOT silently publish their live store.');"
          class="rounded-2xl bg-white border border-zinc-200 p-5 grid grid-cols-1 lg:grid-cols-12 gap-3 items-end">
        @csrf
        <div class="lg:col-span-3">
            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Vendor</label>
            <select name="vendor_id" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                <option value="">Select vendor...</option>
                @foreach($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->shop_name }} (/s/{{ $v->slug }})</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-3">
            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Theme</label>
            <select name="theme_id" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                <option value="">Select paid theme...</option>
                @foreach($paidThemes as $pt)
                    <option value="{{ $pt->theme_id }}">{{ $pt->name }} ({{ $pt->formatted_price }})</option>
                @endforeach
            </select>
        </div>
        <div class="lg:col-span-4">
            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Audit Reason / Payment Note (Required)</label>
            <input type="text" name="notes" required minlength="4" placeholder="e.g. Offline WhatsApp payment verified / Partner grant" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
        </div>
        <div class="lg:col-span-2">
            <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 cursor-pointer">
                Grant Access
            </button>
        </div>
    </form>

    {{-- Active & Revoked Entitlements Table --}}
    <div class="rounded-2xl bg-white border border-zinc-200 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-zinc-100 flex items-center justify-between">
            <h2 class="text-sm font-extrabold text-zinc-900">Vendor Theme Entitlements</h2>
            <span class="text-[11px] text-zinc-500">Revocation Policy: <strong>{{ $revocationPolicy }}</strong></span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-zinc-50 border-b border-zinc-200 text-[10px] font-mono uppercase text-zinc-500">
                    <tr>
                        <th class="py-3 px-4">Vendor &amp; Live/Draft State</th>
                        <th class="py-3 px-3">Theme</th>
                        <th class="py-3 px-3">Entitlement</th>
                        <th class="py-3 px-3">Audit Details</th>
                        <th class="py-3 px-4 text-right">Revoke</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100">
                    @forelse($entitlements as $ent)
                        @php
                            $cfg = $ent->vendor?->storefrontConfig;
                            $liveTheme = $cfg?->published_theme_id ?: ($cfg?->preset_key ?: ($ent->vendor?->layout_preset ?: 'velora'));
                            $vendorDrafts = $draftsByVendor->get($ent->vendor_id, collect());
                        @endphp
                        <tr>
                            <td class="py-3 px-4">
                                <div class="font-bold text-zinc-950">{{ $ent->vendor?->shop_name ?? ('Vendor #' . $ent->vendor_id) }}</div>
                                <div class="text-[11px] text-zinc-500">
                                    Live: <strong class="font-mono text-zinc-800">{{ $liveTheme }}</strong>
                                    @if($vendorDrafts->isNotEmpty())
                                        · Drafts: <span class="font-mono text-indigo-600">{{ $vendorDrafts->pluck('theme_id')->implode(', ') }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-3 px-3 font-bold text-zinc-900">{{ $ent->theme?->name ?? $ent->theme_id }}</td>
                            <td class="py-3 px-3">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $ent->isGranted() ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $ent->status }}
                                </span>
                            </td>
                            <td class="py-3 px-3 text-[11px] text-zinc-600 space-y-0.5">
                                @if($ent->granted_at)
                                    <div>Granted {{ $ent->granted_at->format('M d, Y H:i') }} by {{ $ent->grantor?->name ?? ('Admin #' . $ent->granted_by) }}</div>
                                @endif
                                @if($ent->revoked_at)
                                    <div class="text-rose-700">Revoked {{ $ent->revoked_at->format('M d, Y H:i') }}: {{ $ent->revocation_reason }}</div>
                                @endif
                                @if($ent->notes)
                                    <div class="italic text-zinc-500">{{ $ent->notes }}</div>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right">
                                @if($ent->isGranted())
                                    <form action="{{ route('admin.themes.entitlements.revoke', $ent) }}" method="POST"
                                          onsubmit="return confirm('Revoke {{ $ent->vendor?->shop_name }} access to {{ $ent->theme_id }}? Drafts and products will be preserved.');"
                                          class="inline-flex items-center gap-1.5">
                                        @csrf
                                        <input type="text" name="reason" required minlength="3" placeholder="Revocation reason..." class="rounded-lg border border-zinc-200 px-2 py-1 text-[11px] w-40">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-600 text-white text-[11px] font-bold hover:bg-rose-700 cursor-pointer">Revoke</button>
                                    </form>
                                @else
                                    <span class="text-[11px] text-zinc-400">Revoked</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-zinc-500">No paid theme entitlements granted yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>{{ $entitlements->links() }}</div>
</div>
</x-layouts.admin>
