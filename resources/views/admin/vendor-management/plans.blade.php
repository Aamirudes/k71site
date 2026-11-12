<x-layouts.admin title="Subscription Plans & Versioning">
    @include('admin.vendor-management._nav')

    @php
        $activeSectorForForm = old('sector_key', $editPlan?->sector_key ?? ($sectorFilter ?: 'products'));
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ formSector: '{{ $activeSectorForForm }}', isFree: {{ old('is_free', $editPlan?->is_free ?? false) ? 'true' : 'false' }} }">
        <!-- Create / Edit Plan Form -->
        <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-black text-zinc-950">{{ $editPlan ? 'Edit Plan (Creates v' . ($editPlan->current_version + 1) . ')' : 'Create Subscription Plan' }}</h3>
                @if($editPlan)
                    <a href="{{ route('admin.vendor_management.plans') }}" class="text-xs text-zinc-500 underline">+ New Plan</a>
                @endif
            </div>

            <form method="POST" action="{{ $editPlan ? route('admin.vendor_management.plans.update', $editPlan) : route('admin.vendor_management.plans.store') }}" class="space-y-3">
                @csrf
                @if($editPlan) @method('PUT') @endif

                <div>
                    <label class="block text-[11px] font-bold text-zinc-700 mb-1">Sector</label>
                    <select name="sector_key" x-model="formSector" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                        @foreach($sectors as $s)
                            <option value="{{ $s->key }}">{{ $s->name }}</option>
                        @endforeach
                        <option value="all">Cross-Sector Bundle (All Sectors)</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Plan Name</label>
                        <input type="text" name="name" required value="{{ old('name', $editPlan?->name) }}" placeholder="e.g. Retail Growth" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Code (Internal)</label>
                        <input type="text" name="code" value="{{ old('code', $editPlan?->code) }}" placeholder="RETAIL_GROWTH" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-mono">
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-zinc-700 mb-1">Description</label>
                    <textarea name="description" rows="2" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">{{ old('description', $editPlan?->description) }}</textarea>
                </div>

                <div class="grid grid-cols-3 gap-2 items-end">
                    <label class="inline-flex items-center gap-1.5 text-xs font-bold pb-2">
                        <input type="checkbox" name="is_free" value="1" x-model="isFree"> Free Plan
                    </label>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Price (₹ INR)</label>
                        <input type="number" step="0.01" name="price_rupees" :disabled="isFree" value="{{ old('price_rupees', $editPlan ? ($editPlan->price_amount / 100) : 999) }}" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Duration (Days)</label>
                        <input type="number" name="duration_days" required min="1" value="{{ old('duration_days', $editPlan?->duration_days ?? 30) }}" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Default Commission Rule</label>
                        <select name="default_commission_rule_id" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            <option value="">Use Sector / Platform Default</option>
                            @foreach($commissionRules as $cr)
                                <option value="{{ $cr->id }}" @selected((int) old('default_commission_rule_id', $editPlan?->default_commission_rule_id) === (int) $cr->id)>
                                    {{ $cr->name }} ({{ $cr->rate_percent }}%)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Plan Status</label>
                        <select name="status" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            @foreach(['active' => 'Active (Assignable)', 'draft' => 'Draft', 'archived' => 'Archived'] as $stK => $stL)
                                <option value="{{ $stK }}" @selected(old('status', $editPlan?->status ?? 'active') === $stK)>{{ $stL }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Dynamic Sector Entitlement Schema -->
                <div class="border border-zinc-200 rounded-xl p-3 bg-zinc-50/70 space-y-2.5">
                    <p class="text-[11px] font-black uppercase text-zinc-800">Entitlement Limits &amp; Feature Gates</p>
                    @php
                        $editEnts = $editPlan ? ($editPlan->latestVersion()?->entitlements_json ?? $editPlan->features ?? []) : [];
                    @endphp
                    @foreach($entitlementCatalog as $catSector => $fields)
                        <div x-show="formSector === '{{ $catSector }}' || formSector === 'all'" class="space-y-2">
                            @if(count($entitlementCatalog) > 1)
                                <p class="text-[10px] font-mono uppercase text-zinc-400" x-show="formSector === 'all'">{{ ucfirst($catSector) }} Entitlements</p>
                            @endif
                            @foreach($fields as $fKey => $meta)
                                @php $val = old("entitlements.{$fKey}", $editEnts[$fKey] ?? $meta['default']); @endphp
                                @if($meta['type'] === 'bool')
                                    <label class="flex items-center justify-between text-xs gap-2">
                                        <span class="text-zinc-700">{{ $meta['label'] }}</span>
                                        <input type="checkbox" name="entitlements[{{ $fKey }}]" value="1" @checked((bool)$val)>
                                    </label>
                                @else
                                    <div class="flex items-center justify-between gap-2">
                                        <label class="text-[11px] text-zinc-700">{{ $meta['label'] }}</label>
                                        <input type="number" name="entitlements[{{ $fKey }}]" value="{{ $val }}" placeholder="{{ $meta['type'] === 'nullable_int' ? 'Unlimited' : '' }}" class="w-24 rounded-lg border border-zinc-200 px-2 py-1 text-xs bg-white">
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <!-- Expiry Policy -->
                <div class="border border-zinc-200 rounded-xl p-3 bg-zinc-50/70 space-y-2">
                    <p class="text-[11px] font-black uppercase text-zinc-800">Expiry &amp; Grace Period Behavior</p>
                    @php $editExp = $editPlan?->latestVersion()?->expiry_policy_json ?? []; @endphp
                    <div class="flex items-center justify-between gap-2">
                        <label class="text-[11px] text-zinc-700">Grace Days</label>
                        <input type="number" name="grace_days" min="0" max="90" value="{{ old('grace_days', $editExp['grace_days'] ?? 3) }}" class="w-20 rounded-lg border border-zinc-200 px-2 py-1 text-xs bg-white">
                    </div>
                    <label class="flex items-center justify-between text-xs">
                        <span>Keep visible publicly during grace period</span>
                        <input type="checkbox" name="grace_keep_visible" value="1" @checked($editExp['grace_keep_visible'] ?? true)>
                    </label>
                    <label class="flex items-center justify-between text-xs">
                        <span>Allow creating/editing during grace period</span>
                        <input type="checkbox" name="grace_keep_features" value="1" @checked($editExp['grace_keep_features'] ?? false)>
                    </label>
                </div>

                <input type="text" name="change_notes" placeholder="Version change notes (for audit trail)" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">

                <button type="submit" class="w-full py-2.5 rounded-xl bg-zinc-950 text-white text-xs font-bold hover:bg-zinc-800">
                    {{ $editPlan ? 'Save New Plan Version' : 'Create Plan (v1)' }}
                </button>
            </form>
        </div>

        <!-- Existing Plans List -->
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-2">
                <form method="GET" class="flex flex-wrap gap-2">
                    <select name="sector" class="rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                        <option value="">All Sectors</option>
                        @foreach($sectors as $s)
                            <option value="{{ $s->key }}" @selected($sectorFilter === $s->key)>{{ $s->name }}</option>
                        @endforeach
                        <option value="all" @selected($sectorFilter === 'all')>Cross-Sector</option>
                    </select>
                    <select name="status" class="rounded-xl border border-zinc-200 px-3 py-1.5 text-xs">
                        <option value="">All Statuses</option>
                        <option value="active" @selected($statusFilter === 'active')>Active</option>
                        <option value="draft" @selected($statusFilter === 'draft')>Draft</option>
                        <option value="archived" @selected($statusFilter === 'archived')>Archived</option>
                    </select>
                    <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-zinc-950 text-white text-xs font-bold">Filter</button>
                </form>
                <span class="text-[11px] text-zinc-500">Editing a plan creates a new version — active vendors stay protected on their signed snapshot.</span>
            </div>

            @forelse($plans as $plan)
                <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-black text-zinc-950">{{ $plan->name }}</span>
                                <span class="px-2 py-0.5 rounded bg-zinc-100 text-[10px] font-mono uppercase">{{ $plan->sector_key }}</span>
                                <span class="px-2 py-0.5 rounded bg-zinc-950 text-white text-[10px] font-mono">v{{ $plan->current_version }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $plan->status === 'active' ? 'bg-emerald-100 text-emerald-800' : ($plan->status === 'draft' ? 'bg-amber-100 text-amber-800' : 'bg-zinc-200 text-zinc-600') }}">
                                    {{ $plan->status }}
                                </span>
                            </div>
                            <p class="text-xs text-zinc-600 mt-1">
                                {{ $plan->is_free ? 'FREE' : '₹' . number_format($plan->price_amount / 100, 0) }} / {{ $plan->duration_days }} days
                                • {{ $plan->active_subscriptions_count }} active vendor(s) ({{ $plan->subscriptions_count }} total)
                                @if($plan->defaultCommissionRule)
                                    • Default Commission: <strong>{{ $plan->defaultCommissionRule->rate_percent }}%</strong>
                                @endif
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-1.5">
                            <a href="{{ route('admin.vendor_management.plans', ['edit' => $plan->id]) }}" class="px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-bold hover:bg-zinc-50">Edit / New Version</a>
                            <form method="POST" action="{{ route('admin.vendor_management.plans.duplicate', $plan) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-bold hover:bg-zinc-50">Duplicate</button>
                            </form>
                            @if($plan->status !== 'archived')
                                <form method="POST" action="{{ route('admin.vendor_management.plans.archive', $plan) }}">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-800 text-xs font-bold hover:bg-rose-100">Archive</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    @if($plan->description)
                        <p class="text-xs text-zinc-500">{{ $plan->description }}</p>
                    @endif

                    <div class="flex flex-wrap gap-1.5">
                        @foreach(($plan->latestVersion()?->entitlements_json ?? $plan->features ?? []) as $eKey => $eVal)
                            <span class="px-2 py-0.5 rounded-md border text-[10px] font-mono {{ $eVal === false ? 'bg-zinc-50 text-zinc-400 border-zinc-200 line-through' : 'bg-zinc-100 text-zinc-800 border-zinc-200' }}">
                                {{ $eKey }}: {{ is_bool($eVal) ? ($eVal ? 'YES' : 'NO') : ($eVal === null ? 'Unlimited' : $eVal) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="bg-white border border-zinc-200/80 rounded-2xl p-8 text-center text-xs text-zinc-400">
                    No subscription plans found yet. Create your first plan on the left.
                </div>
            @endforelse
        </div>
    </div>
</x-layouts.admin>
