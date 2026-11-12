<x-layouts.admin title="WhatsApp Onboarding Leads">
    @include('admin.vendor-management._nav')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Add New Lead -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
            <h3 class="text-sm font-black text-zinc-950">Add WhatsApp Prospect</h3>
            <p class="text-[11px] text-zinc-500">Track prospective vendors you speak to on WhatsApp before they even create a K71 account.</p>

            <form method="POST" action="{{ route('admin.vendor_management.leads.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="business_name" required placeholder="Business / Shop Name" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                <div class="grid grid-cols-2 gap-2">
                    <input type="text" name="contact_person" placeholder="Owner Name" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    <input type="text" name="whatsapp_number" required placeholder="WhatsApp Number" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <input type="email" name="email" placeholder="Email (optional)" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    <input type="text" name="city" placeholder="City" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <select name="sector_key" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                        @foreach($sectors as $s)
                            <option value="{{ $s->key }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    <select name="status" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="negotiating">Negotiating</option>
                        <option value="converted">Converted</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <select name="proposed_plan_id" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    <option value="">Discussed Plan (Optional)</option>
                    @foreach($plans as $p)
                        <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sector_key }})</option>
                    @endforeach
                </select>
                <div>
                    <label class="block text-[10px] font-mono uppercase text-zinc-400 mb-1">Follow-up Date</label>
                    <input type="date" name="follow_up_at" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                </div>
                <textarea name="notes" rows="2" placeholder="WhatsApp discussion summary..." class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs"></textarea>
                <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">Save Lead</button>
            </form>
        </div>

        <!-- Leads Pipeline Table -->
        <div class="lg:col-span-2 bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search business, owner, WhatsApp..." class="flex-1 rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                <select name="status" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    <option value="">All Statuses</option>
                    @foreach(['new', 'contacted', 'negotiating', 'converted', 'archived'] as $st)
                        <option value="{{ $st }}" @selected($statusFilter === $st)>{{ ucfirst($st) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-zinc-950 text-white text-xs font-bold">Filter</button>
            </form>

            <div class="space-y-3">
                @forelse($leads as $lead)
                    <div class="border border-zinc-200 rounded-xl p-3.5 space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-bold text-sm text-zinc-950">{{ $lead->business_name }}</span>
                                <span class="ml-2 px-2 py-0.5 rounded bg-zinc-100 text-[10px] font-mono uppercase">{{ $lead->sector_key ?: 'products' }}</span>
                                <span class="ml-1 px-2 py-0.5 rounded bg-blue-50 text-blue-800 text-[10px] font-bold uppercase">{{ $lead->status }}</span>
                            </div>
                            @php
                                $d = preg_replace('/\D+/', '', (string) $lead->whatsapp_number);
                                $leadWa = strlen($d) >= 10 ? 'https://wa.me/' . (strlen($d) === 10 ? '91' . $d : $d) : null;
                            @endphp
                            @if($leadWa)
                                <a href="{{ $leadWa }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-600 text-white text-[11px] font-bold">WhatsApp ↗</a>
                            @endif
                        </div>

                        <p class="text-[11px] text-zinc-600">
                            {{ $lead->contact_person ?: 'Owner' }} • {{ $lead->whatsapp_number }} {{ $lead->city ? '• ' . $lead->city : '' }}
                            @if($lead->proposedPlan) • Discussed: {{ $lead->proposedPlan->name }} @endif
                            @if($lead->linkedVendor) • Linked: <a href="{{ route('admin.vendor_management.show', $lead->linkedVendor) }}" class="underline font-bold text-zinc-950">{{ $lead->linkedVendor->shop_name }}</a> @endif
                        </p>

                        <form method="POST" action="{{ route('admin.vendor_management.leads.update', $lead) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-2">
                            @csrf
                            @method('PUT')
                            <select name="status" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs">
                                @foreach(['new', 'contacted', 'negotiating', 'converted', 'archived'] as $st)
                                    <option value="{{ $st }}" @selected($lead->status === $st)>{{ ucfirst($st) }}</option>
                                @endforeach
                            </select>
                            <select name="linked_vendor_id" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs">
                                <option value="">Link to Registered Vendor...</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" @selected((int)$lead->linked_vendor_id === (int)$v->id)>{{ $v->shop_name }} (/#{{ $v->id }})</option>
                                @endforeach
                            </select>
                            <input type="text" name="notes" value="{{ $lead->notes }}" placeholder="Notes" class="rounded-lg border border-zinc-200 px-2.5 py-1.5 text-xs">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">Update</button>
                        </form>
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-zinc-400">No onboarding leads recorded yet.</p>
                @endforelse
            </div>

            {{ $leads->links() }}
        </div>
    </div>
</x-layouts.admin>
