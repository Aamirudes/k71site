<x-layouts.admin title="Theme Requests & Manual Payment Verification">
<div class="p-6 max-w-7xl mx-auto space-y-6">
    @include('admin.themes._tabs')

    <div class="flex flex-wrap items-center gap-2">
        @foreach(['all' => 'All Requests', 'pending' => 'Pending Queue', 'payment_confirmed' => 'Payment Confirmed (Ready to Grant)', 'access_granted' => 'Access Granted', 'declined' => 'Declined', 'cancelled' => 'Cancelled'] as $stKey => $stLabel)
            <a href="{{ route('admin.themes.requests', ['status' => $stKey]) }}"
               class="px-3.5 py-1.5 rounded-full text-xs font-bold border transition {{ $statusFilter === $stKey ? 'bg-zinc-950 text-white border-zinc-950' : 'bg-white text-zinc-700 border-zinc-200 hover:bg-zinc-50' }}">
                {{ $stLabel }}
            </a>
        @endforeach
    </div>

    @if($themeRequests->isEmpty())
        <div class="rounded-2xl bg-white border border-zinc-200 p-12 text-center text-sm text-zinc-500">
            No theme access requests found in this queue.
        </div>
    @else
        <div class="space-y-4">
            @foreach($themeRequests as $req)
                @php
                    $ent = $entitlementsMap->get($req->vendor_id . ':' . $req->theme_id);
                    $priceChanged = $req->hasPriceChangedSinceRequest();
                @endphp
                <div class="rounded-2xl bg-white border border-zinc-200 p-5 space-y-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-xs font-extrabold px-2.5 py-0.5 rounded bg-zinc-100 text-zinc-900">{{ $req->reference_code }}</span>
                                <span class="text-sm font-extrabold text-zinc-950">{{ $req->theme?->name ?? $req->theme_id }}</span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $req->isPaymentConfirmed() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                    Payment: {{ ucwords(str_replace('_', ' ', $req->payment_verification_status)) }}
                                </span>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase bg-indigo-50 text-indigo-800">
                                    State: {{ $req->status_label }}
                                </span>
                            </div>
                            <p class="text-xs text-zinc-600">
                                Vendor: <strong>{{ $req->vendor?->shop_name ?? ('Vendor #' . $req->vendor_id) }}</strong>
                                @if($req->vendor?->storefront_url)
                                    (<a href="{{ $req->vendor->storefront_url }}" target="_blank" class="text-indigo-600 hover:underline">{{ $req->vendor->storefront_url }}</a>)
                                @endif
                                · Requested {{ $req->created_at?->format('M d, Y H:i') }}
                            </p>
                            <div class="flex flex-wrap items-center gap-3 text-xs pt-1">
                                <span>Request-Time Price Snapshot: <strong>{{ $req->formatted_price_snapshot }}</strong></span>
                                <span>Current Catalogue Price: <strong>{{ $req->theme?->formatted_price ?? 'N/A' }}</strong></span>
                                @if($priceChanged)
                                    <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-900 font-bold text-[11px]">⚠ Price changed since request</span>
                                @endif
                            </div>
                            @if($req->payment_verified_at)
                                <p class="text-[11px] text-emerald-700 font-medium">
                                    Payment manually confirmed by {{ $req->paymentVerifier?->name ?? ('Admin #' . $req->payment_verified_by) }} on {{ $req->payment_verified_at->format('M d, Y H:i') }}
                                    @if($req->payment_reference) · Ref: {{ $req->payment_reference }} @endif
                                </p>
                            @endif
                            @if($req->vendor_note)
                                <p class="text-xs text-zinc-500 italic">Vendor note: "{{ $req->vendor_note }}"</p>
                            @endif
                        </div>

                        <div class="text-right text-xs">
                            @if($ent && $ent->isGranted())
                                <span class="inline-block px-3 py-1 rounded-full bg-emerald-600 text-white font-bold">Entitlement Active</span>
                            @elseif($ent && $ent->isRevoked())
                                <span class="inline-block px-3 py-1 rounded-full bg-rose-600 text-white font-bold">Entitlement Revoked</span>
                            @else
                                <span class="inline-block px-3 py-1 rounded-full bg-zinc-100 text-zinc-600 font-bold">Not Yet Entitled</span>
                            @endif
                        </div>
                    </div>

                    {{-- Separate Step Controls (Never auto-grants on WhatsApp or request) --}}
                    @if($req->isPending())
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 pt-3 border-t border-zinc-100">
                            {{-- Step 1: Confirm External Manual Payment --}}
                            @if(!$req->isPaymentConfirmed())
                                <form action="{{ route('admin.themes.requests.confirm_payment', $req) }}" method="POST"
                                      onsubmit="return confirm('Confirm that you have manually verified payment for {{ $req->reference_code }}?');"
                                      class="p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 space-y-2">
                                    @csrf
                                    <p class="text-[11px] font-extrabold text-amber-950 uppercase">Step 1: Confirm Manual Payment</p>
                                    <input type="text" name="payment_reference" placeholder="Non-sensitive payment ref (optional)" maxlength="120" class="w-full rounded-lg border border-amber-200 bg-white px-2.5 py-1.5 text-xs">
                                    <input type="text" name="payment_notes" placeholder="Verification note (no bank secrets)..." maxlength="500" class="w-full rounded-lg border border-amber-200 bg-white px-2.5 py-1.5 text-xs">
                                    @if($priceChanged)
                                        <label class="flex items-center gap-1.5 text-[11px] font-bold text-amber-900">
                                            <input type="checkbox" name="acknowledge_price_change" value="1" required>
                                            Acknowledge price difference before confirming
                                        </label>
                                    @endif
                                    <button type="submit" class="w-full px-3 py-2 rounded-lg bg-amber-600 text-white text-xs font-bold hover:bg-amber-700 cursor-pointer">
                                        Record Payment Confirmed
                                    </button>
                                </form>
                            @else
                                {{-- Step 2: Explicitly Grant Theme Access --}}
                                <form action="{{ route('admin.themes.requests.grant', $req) }}" method="POST"
                                      onsubmit="return confirm('Grant {{ $req->vendor?->shop_name }} access to {{ $req->theme_id }}? This unlocks customization; it will NOT silently publish their live store.');"
                                      class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 space-y-2">
                                    @csrf
                                    <p class="text-[11px] font-extrabold text-emerald-950 uppercase">Step 2: Grant Vendor Entitlement</p>
                                    <input type="text" name="notes" placeholder="Grant notes (optional)..." maxlength="500" class="w-full rounded-lg border border-emerald-200 bg-white px-2.5 py-1.5 text-xs">
                                    <button type="submit" class="w-full px-3 py-2 rounded-lg bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700 cursor-pointer">
                                        Grant Theme Access
                                    </button>
                                </form>
                            @endif

                            {{-- Move Status (Review / Awaiting Payment) --}}
                            @if(!$req->isPaymentConfirmed())
                                <form action="{{ route('admin.themes.requests.status', $req) }}" method="POST" class="p-3.5 rounded-xl bg-zinc-50 border border-zinc-200 space-y-2">
                                    @csrf
                                    <p class="text-[11px] font-extrabold text-zinc-700 uppercase">Update Queue Status</p>
                                    <select name="status" class="w-full rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs">
                                        <option value="pending_review">Pending Review</option>
                                        <option value="payment_awaiting_confirmation">Payment Awaiting Confirmation</option>
                                    </select>
                                    <input type="text" name="admin_notes" placeholder="Internal review note..." class="w-full rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-xs">
                                    <button type="submit" class="w-full px-3 py-2 rounded-lg bg-zinc-800 text-white text-xs font-bold hover:bg-zinc-900 cursor-pointer">
                                        Update Status
                                    </button>
                                </form>
                            @endif

                            {{-- Decline Request --}}
                            <form action="{{ route('admin.themes.requests.decline', $req) }}" method="POST"
                                  onsubmit="return confirm('Decline request {{ $req->reference_code }}? No entitlement will be created.');"
                                  class="p-3.5 rounded-xl bg-rose-50/70 border border-rose-200 space-y-2">
                                @csrf
                                <p class="text-[11px] font-extrabold text-rose-900 uppercase">Decline Request</p>
                                <input type="text" name="reason" placeholder="Reason for declining (optional)..." class="w-full rounded-lg border border-rose-200 bg-white px-2.5 py-1.5 text-xs">
                                <button type="submit" class="w-full px-3 py-2 rounded-lg bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 cursor-pointer">
                                    Decline Request
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div>{{ $themeRequests->links() }}</div>
    @endif
</div>
</x-layouts.admin>
