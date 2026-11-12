<x-layouts.admin title="Commission Rules, Simulator & Ledger">
    @include('admin.vendor-management._nav')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ scope: 'sector' }">
        <!-- Left: Create Rule + Simulator -->
        <div class="space-y-6">
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
                <div>
                    <h3 class="text-sm font-black text-zinc-950">Create Commission Rule</h3>
                    <p class="text-[11px] text-zinc-500 mt-0.5">Priority order: Transaction Override &gt; Vendor Rule &gt; Category Rule &gt; Plan Rule &gt; Sector Rule &gt; Platform Default.</p>
                </div>

                <form method="POST" action="{{ route('admin.vendor_management.commission_rules.store') }}" class="space-y-3">
                    @csrf
                    <input type="text" name="name" required placeholder="Rule Name (e.g. Food Standard 8%)" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Scope</label>
                            <select name="scope" x-model="scope" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                                <option value="platform">Platform Default</option>
                                <option value="sector">Sector Rule</option>
                                <option value="plan">Plan Rule</option>
                                <option value="category">Category Rule</option>
                                <option value="vendor">Vendor Override</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Sector (optional)</label>
                            <select name="sector_key" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                                <option value="">Any Sector</option>
                                @foreach($sectors as $s)
                                    <option value="{{ $s->key }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div x-show="scope === 'plan'">
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Target Plan</label>
                        <select name="plan_id" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            <option value="">Select Plan...</option>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sector_key }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="scope === 'vendor'">
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Target Vendor</label>
                        <select name="vendor_id" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            <option value="">Select Vendor...</option>
                            @foreach($vendors as $v)
                                <option value="{{ $v->id }}">{{ $v->shop_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="scope === 'category'">
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Target Category</label>
                        <select name="category_id" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            <option value="">Select Category...</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->vertical ?: 'products' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-3 gap-2">
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="rate_percent" required value="5.00" class="w-full rounded-xl border border-zinc-200 px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Fixed (₹)</label>
                            <input type="number" step="0.01" min="0" name="fixed_fee_rupees" value="0" class="w-full rounded-xl border border-zinc-200 px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Priority</label>
                            <input type="number" name="priority" value="10" class="w-full rounded-xl border border-zinc-200 px-2.5 py-1.5 text-xs">
                        </div>
                    </div>

                    <textarea name="notes" rows="2" placeholder="Rule notes / agreement details..." class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs"></textarea>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">Save Commission Rule</button>
                </form>
            </div>

            <!-- Commission Simulator -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                <h3 class="text-sm font-black text-zinc-950">Commission Simulator</h3>
                <form method="GET" class="space-y-2.5">
                    <select name="sim_vendor_id" required class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                        <option value="">Choose Vendor...</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" @selected(request('sim_vendor_id') == $v->id)>{{ $v->shop_name }}</option>
                        @endforeach
                    </select>
                    <div class="grid grid-cols-2 gap-2">
                        <select name="sim_sector" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            @foreach($sectors as $s)
                                <option value="{{ $s->key }}" @selected(request('sim_sector') === $s->key)>{{ ucfirst($s->key) }}</option>
                            @endforeach
                        </select>
                        <input type="number" step="0.01" name="sim_amount" value="{{ request('sim_amount', '1000') }}" placeholder="Order ₹" class="rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    </div>
                    <button type="submit" class="w-full py-2 rounded-xl border border-zinc-950 text-xs font-bold hover:bg-zinc-950 hover:text-white transition">Simulate Breakdown</button>
                </form>

                @if($simulation)
                    <div class="rounded-xl bg-zinc-950 text-white p-3.5 text-xs space-y-1">
                        <p class="text-[10px] font-mono uppercase text-zinc-400">Matched: {{ $simulation['source'] }} — {{ $simulation['name'] }}</p>
                        <p>Gross Base: <strong>₹{{ number_format($simulation['commission_base_paise'] / 100, 2) }}</strong></p>
                        <p>Commission ({{ $simulation['rate_percent'] }}%{{ $simulation['fixed_fee_paise'] ? ' + ₹' . number_format($simulation['fixed_fee_paise']/100, 2) : '' }}): <strong>₹{{ number_format($simulation['calculated_commission_paise'] / 100, 2) }}</strong></p>
                        <p>Vendor Net Payout: <strong class="text-emerald-400">₹{{ number_format($simulation['vendor_net_paise'] / 100, 2) }}</strong></p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right 2 Columns: Active Rules + Ledger -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                <h3 class="text-sm font-black text-zinc-950">Configured Commission Hierarchy</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-zinc-100 text-[10px] font-mono uppercase text-zinc-400">
                                <th class="py-2 px-2">Scope &amp; Target</th>
                                <th class="py-2 px-2">Name</th>
                                <th class="py-2 px-2">Rate / Fixed</th>
                                <th class="py-2 px-2">Status</th>
                                <th class="py-2 px-2 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @foreach($rules as $rule)
                                <tr>
                                    <td class="py-2.5 px-2">
                                        <span class="px-2 py-0.5 rounded bg-zinc-100 font-mono text-[10px] uppercase font-bold">{{ $rule->scope }}</span>
                                        <span class="block text-[11px] text-zinc-500 mt-0.5">
                                            {{ $rule->vendor?->shop_name ?? $rule->category?->name ?? $rule->plan?->name ?? ($rule->sector_key ? ucfirst($rule->sector_key) : 'Platform-Wide') }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 font-semibold text-zinc-900">{{ $rule->name }}</td>
                                    <td class="py-2.5 px-2 font-bold">
                                        {{ $rule->rate_percent }}%
                                        @if($rule->fixed_fee_paise > 0) + ₹{{ number_format($rule->fixed_fee_paise / 100, 2) }} @endif
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $rule->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-zinc-200 text-zinc-600' }}">
                                            {{ $rule->is_active ? 'ACTIVE' : 'PAUSED' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2 text-right">
                                        <form method="POST" action="{{ route('admin.vendor_management.commission_rules.toggle', $rule) }}">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg border border-zinc-200 text-[11px] font-bold hover:bg-zinc-50">
                                                {{ $rule->is_active ? 'Pause' : 'Enable' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Transaction Commission Ledger -->
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-black text-zinc-950">Order Commission Ledger (Immutable Snapshots &amp; Reversals)</h3>
                    <a href="{{ route('admin.vendor_management.commissions.export') }}" class="px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-bold hover:bg-zinc-50">Export CSV ↓</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-zinc-100 text-[10px] font-mono uppercase text-zinc-400">
                                <th class="py-2 px-2">Order</th>
                                <th class="py-2 px-2">Vendor / Sector</th>
                                <th class="py-2 px-2">Rule Snapshot</th>
                                <th class="py-2 px-2">Gross</th>
                                <th class="py-2 px-2">Commission</th>
                                <th class="py-2 px-2">Vendor Net</th>
                                <th class="py-2 px-2">Status</th>
                                <th class="py-2 px-2">Manual Override</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100">
                            @forelse($ledger as $entry)
                                <tr>
                                    <td class="py-2.5 px-2 font-mono font-bold">
                                        @if($entry->order)
                                            <a href="{{ route('admin.orders.show', $entry->order) }}" class="underline">{{ $entry->order->order_number }}</a>
                                        @else
                                            #{{ $entry->transaction_id }}
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-2">
                                        {{ $entry->vendor?->shop_name ?? 'Vendor #' . $entry->vendor_id }}
                                        <span class="block text-[10px] uppercase text-zinc-400">{{ $entry->sector_key }}</span>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        <span class="font-semibold">{{ $entry->rate_percent }}%</span>
                                        <span class="block text-[10px] text-zinc-400">{{ $entry->rule_source }}</span>
                                    </td>
                                    <td class="py-2.5 px-2">₹{{ number_format($entry->commission_base_paise / 100, 2) }}</td>
                                    <td class="py-2.5 px-2 font-bold {{ $entry->calculated_commission_paise < 0 ? 'text-rose-700' : 'text-zinc-950' }}">
                                        ₹{{ number_format($entry->calculated_commission_paise / 100, 2) }}
                                    </td>
                                    <td class="py-2.5 px-2 text-emerald-700 font-semibold">₹{{ number_format($entry->vendor_net_paise / 100, 2) }}</td>
                                    <td class="py-2.5 px-2">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $entry->event_type === 'charge' && $entry->refund_state === 'none' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                            {{ $entry->event_type }}{{ $entry->refund_state !== 'none' ? ' • ' . $entry->refund_state : '' }}
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-2">
                                        @if($entry->event_type === 'charge' && $entry->refund_state === 'none' && $entry->calculated_commission_paise >= 0)
                                            <form method="POST" action="{{ route('admin.vendor_management.order_commissions.override', $entry) }}" class="flex items-center gap-1">
                                                @csrf
                                                <input type="number" step="0.01" min="0" max="100" name="rate_percent" value="{{ $entry->rate_percent }}" class="w-16 rounded border border-zinc-200 px-1.5 py-1 text-[11px]" title="New Rate %">
                                                <input type="text" name="reason" required placeholder="Reason" class="w-24 rounded border border-zinc-200 px-1.5 py-1 text-[11px]">
                                                <button type="submit" class="px-2 py-1 rounded bg-zinc-950 text-white text-[10px] font-bold">Adjust</button>
                                            </form>
                                        @else
                                            <span class="text-[10px] text-zinc-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-zinc-400">No commission ledger entries recorded yet. Paid orders automatically record immutable commission snapshots here.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $ledger->links() }}
            </div>
        </div>
    </div>
</x-layouts.admin>
