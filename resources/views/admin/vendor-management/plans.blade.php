<x-layouts.admin title="Plan Builder • 3-Tier Sector Configurator">
    @include('admin.vendor-management._nav')

    @php
        $currentSector = $sectorFilter ?: ($editPlan?->sector_key ?? 'products');
        $formSector = old('sector_key', $editPlan?->sector_key ?? $currentSector);
        $commType = old('commission_type', $editPlan?->commission_type ?? 'default');
        $durUnit = old('duration_unit', $editPlan?->duration_unit ?? 'days');
        $benefitsDefault = old('benefits_text', implode("\n", (array) ($editPlan?->benefits_json ?? [])));
        $editEnts = $editPlan ? ($editPlan->latestVersion()?->entitlements_json ?? $editPlan->features ?? []) : [];
        $editExp = $editPlan?->latestVersion()?->expiry_policy_json ?? [];
    @endphp

    <!-- Sector Selector Strip -->
    <div class="bg-white border border-zinc-200/80 rounded-2xl p-4 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-sm font-black text-zinc-950">Sector Plan Builder</h2>
                <p class="text-[11px] text-zinc-500">Select a sector to configure its 3 tiers (Starter, Professional, Elite) — quotas, real backend feature gates, pricing, duration, commission &amp; expiry rules.</p>
            </div>
            <a href="{{ route('admin.vendor_management.plans', ['sector' => $currentSector, 'new' => 1]) }}"
               class="px-3 py-1.5 rounded-xl border border-zinc-300 text-xs font-bold text-zinc-800 hover:bg-zinc-50 transition">
                + Create Custom / Bundle Plan
            </a>
        </div>

        <div class="flex flex-wrap gap-1.5">
            @foreach($sectors as $s)
                <a href="{{ route('admin.vendor_management.plans', ['sector' => $s->key]) }}"
                   class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $currentSector === $s->key ? 'bg-zinc-950 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' }}">
                    {{ $s->name }}
                </a>
            @endforeach
            <a href="{{ route('admin.vendor_management.plans', ['sector' => 'all']) }}"
               class="px-3.5 py-2 rounded-xl text-xs font-bold transition {{ $currentSector === 'all' ? 'bg-zinc-950 text-white' : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200' }}">
                Cross-Sector Bundles
            </a>
        </div>

        <!-- 3-Tier Cards for Selected Sector -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-1">
            @forelse($sectorTierPlans as $cardPlan)
                @php
                    $isSelected = $editPlan && $editPlan->id === $cardPlan->id;
                    $cardEnts = $cardPlan->latestVersion()?->entitlements_json ?? $cardPlan->features ?? [];
                    $enabledFeatureCount = collect($cardEnts)->filter(fn($v) => $v === true)->count();
                @endphp
                <div class="rounded-2xl border p-4 flex flex-col justify-between gap-3 transition {{ $isSelected ? 'border-zinc-950 bg-zinc-950 text-white shadow-sm' : 'border-zinc-200 bg-zinc-50/70 text-zinc-950 hover:border-zinc-400' }}">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase {{ $isSelected ? 'bg-white text-zinc-950' : 'bg-zinc-200 text-zinc-800' }}">
                                {{ $cardPlan->badge_text ?: ucfirst($cardPlan->tier_key ?: 'Tier') }}
                            </span>
                            <span class="text-[10px] font-mono {{ $isSelected ? 'text-zinc-400' : 'text-zinc-500' }}">v{{ $cardPlan->current_version }} • {{ strtoupper($cardPlan->status) }}</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black">{{ $cardPlan->name }}</h3>
                            <p class="text-xl font-black mt-0.5">
                                {{ $cardPlan->is_free ? 'FREE' : '₹' . number_format($cardPlan->price_amount / 100, 0) }}
                                <span class="text-xs font-medium {{ $isSelected ? 'text-zinc-400' : 'text-zinc-500' }}">/ {{ $cardPlan->duration_days }}d</span>
                            </p>
                        </div>
                        <p class="text-[11px] {{ $isSelected ? 'text-zinc-300' : 'text-zinc-600' }} line-clamp-2">{{ $cardPlan->description }}</p>
                        <div class="text-[11px] font-mono space-y-1 pt-1 {{ $isSelected ? 'text-zinc-300' : 'text-zinc-700' }}">
                            <div>
                                Commission:
                                <strong>
                                    @if(($cardPlan->commission_type ?? 'default') === 'zero') Zero (0%)
                                    @elseif(($cardPlan->commission_type ?? 'default') === 'percentage') {{ $cardPlan->commission_rate ?? 0 }}%
                                    @elseif(($cardPlan->commission_type ?? 'default') === 'fixed') ₹{{ number_format(($cardPlan->commission_fixed_paise ?? 0) / 100, 2) }} fixed
                                    @elseif(($cardPlan->commission_type ?? 'default') === 'hybrid') {{ $cardPlan->commission_rate ?? 0 }}% + ₹{{ number_format(($cardPlan->commission_fixed_paise ?? 0) / 100, 2) }}
                                    @else {{ $cardPlan->defaultCommissionRule ? $cardPlan->defaultCommissionRule->rate_percent . '% Rule' : 'Sector/Global Default' }}
                                    @endif
                                </strong>
                            </div>
                            <div>{{ $enabledFeatureCount }} features enabled • {{ $cardPlan->active_subscriptions_count }} active</div>
                        </div>
                    </div>
                    <a href="{{ route('admin.vendor_management.plans', ['sector' => $currentSector, 'edit' => $cardPlan->id]) }}"
                       class="w-full py-2 rounded-xl text-center text-xs font-black transition {{ $isSelected ? 'bg-white text-zinc-950' : 'bg-zinc-950 text-white hover:bg-zinc-800' }}">
                        {{ $isSelected ? 'Editing Now' : 'Configure Plan' }}
                    </a>
                </div>
            @empty
                <div class="md:col-span-3 rounded-xl border border-dashed border-zinc-300 p-6 text-center text-xs text-zinc-500">
                    No plans created under '{{ $currentSector }}' yet. Use the editor below to create your plan.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Interactive Complete Plan Control Form -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6"
         x-data="{
            formSector: '{{ $formSector }}',
            isFree: {{ old('is_free', $editPlan?->is_free ?? false) ? 'true' : 'false' }},
            commType: '{{ $commType }}'
         }">
        <div class="lg:col-span-8 bg-white border border-zinc-200/80 rounded-2xl p-6 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-zinc-100 pb-4">
                <div>
                    <h3 class="text-base font-black text-zinc-950">
                        {{ $editPlan ? "Configure '{$editPlan->name}' (" . ucfirst($editPlan->sector_key) . " • v{$editPlan->current_version})" : 'Create New Subscription Plan' }}
                    </h3>
                    <p class="text-[11px] text-zinc-500 mt-0.5">All switches map directly to live backend K71 authorization &amp; quota checks.</p>
                </div>
                @if($editPlan)
                    <span class="px-2.5 py-1 rounded-full bg-zinc-100 text-[10px] font-mono uppercase text-zinc-700">
                        {{ $editPlan->active_subscriptions_count ?? 0 }} Active • {{ $editPlan->subscriptions_count ?? 0 }} Historical
                    </span>
                @endif
            </div>

            <form method="POST" action="{{ $editPlan ? route('admin.vendor_management.plans.update', $editPlan) : route('admin.vendor_management.plans.store') }}" class="space-y-5">
                @csrf
                @if($editPlan) @method('PUT') @endif

                <!-- Identity & Pricing Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Sector</label>
                        <select name="sector_key" x-model="formSector" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                            @foreach($sectors as $s)
                                <option value="{{ $s->key }}">{{ $s->name }}</option>
                            @endforeach
                            <option value="all">Cross-Sector Bundle (All)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Plan Name</label>
                        <input type="text" name="name" required value="{{ old('name', $editPlan?->name) }}" placeholder="e.g. Professional" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs font-semibold">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Badge Label</label>
                        <input type="text" name="badge_text" value="{{ old('badge_text', $editPlan?->badge_text) }}" placeholder="e.g. Most Popular" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Tier Slot</label>
                        <select name="tier_key" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            <option value="">Custom</option>
                            @foreach(['starter' => 'Tier 1: Starter', 'professional' => 'Tier 2: Professional', 'elite' => 'Tier 3: Elite'] as $tk => $tl)
                                <option value="{{ $tk }}" @selected(old('tier_key', $editPlan?->tier_key) === $tk)>{{ $tl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Status</label>
                        <select name="status" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                            @foreach(['active' => 'Active (Assignable)', 'draft' => 'Draft', 'archived' => 'Archived'] as $stK => $stL)
                                <option value="{{ $stK }}" @selected(old('status', $editPlan?->status ?? 'active') === $stK)>{{ $stL }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Sort Order</label>
                        <input type="number" name="sort_order" min="0" value="{{ old('sort_order', $editPlan?->sort_order ?? 1) }}" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs">
                    </div>
                    <div class="flex items-end pb-2">
                        <label class="inline-flex items-center gap-2 text-xs font-bold text-zinc-800">
                            <input type="checkbox" name="is_public_display" value="1" @checked(old('is_public_display', $editPlan?->is_public_display ?? true))> Visible in Directory
                        </label>
                    </div>
                </div>

                <!-- Pricing & Duration -->
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 grid grid-cols-1 sm:grid-cols-5 gap-3 items-end">
                    <label class="inline-flex items-center gap-2 text-xs font-bold pb-2">
                        <input type="checkbox" name="is_free" value="1" x-model="isFree"> Free Plan
                    </label>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Price (₹ INR)</label>
                        <input type="number" step="0.01" name="price_rupees" :disabled="isFree" value="{{ old('price_rupees', $editPlan ? ($editPlan->price_amount / 100) : 999) }}" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Duration Value</label>
                        <input type="number" name="duration_value" min="1" required value="{{ old('duration_value', $editPlan?->duration_value ?? ($editPlan?->duration_days ?? 30)) }}" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs bg-white">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-zinc-700 mb-1">Duration Unit</label>
                        <select name="duration_unit" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs bg-white">
                            <option value="days" @selected($durUnit === 'days')>Days</option>
                            <option value="months" @selected($durUnit === 'months')>Months (30d/mo)</option>
                        </select>
                    </div>
                    <label class="inline-flex items-center gap-1.5 text-[11px] font-semibold pb-2 text-zinc-700">
                        <input type="checkbox" name="custom_duration_allowed" value="1" @checked(old('custom_duration_allowed', $editPlan?->custom_duration_allowed ?? true))> Allow Custom Days
                    </label>
                </div>

                <!-- Inline Commission Configurator -->
                <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-[11px] font-black uppercase tracking-wider text-zinc-800">Plan Commission Terms</p>
                        <span class="text-[10px] font-mono text-zinc-500">Vendor overrides still take highest priority</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Commission Mode</label>
                            <select name="commission_type" x-model="commType" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs bg-white font-semibold">
                                <option value="default">Use Rule / Sector Default</option>
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Per Order (₹)</option>
                                <option value="hybrid">Hybrid (% + Fixed ₹)</option>
                                <option value="zero">Zero Commission (0%)</option>
                            </select>
                        </div>
                        <div x-show="commType === 'percentage' || commType === 'hybrid'">
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Commission Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="100" name="commission_rate" value="{{ old('commission_rate', $editPlan?->commission_rate ?? 5.00) }}" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs bg-white">
                        </div>
                        <div x-show="commType === 'fixed' || commType === 'hybrid'">
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Fixed Fee (₹)</label>
                            <input type="number" step="0.01" min="0" name="commission_fixed_rupees" value="{{ old('commission_fixed_rupees', ($editPlan?->commission_fixed_paise ?? 0) / 100) }}" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs bg-white">
                        </div>
                        <div x-show="commType === 'default'">
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Optional Linked Rule</label>
                            <select name="default_commission_rule_id" class="w-full rounded-xl border border-zinc-200 px-3 py-2 text-xs bg-white">
                                <option value="">Sector / Global Default</option>
                                @foreach($commissionRules as $cr)
                                    <option value="{{ $cr->id }}" @selected((int) old('default_commission_rule_id', $editPlan?->default_commission_rule_id) === (int) $cr->id)>
                                        {{ $cr->name }} ({{ $cr->rate_percent }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Real K71 Backend-Enforced Quotas & Feature Switches -->
                <div class="rounded-xl border border-zinc-200 p-4 space-y-4">
                    <p class="text-[11px] font-black uppercase tracking-wider text-zinc-900">Real Features &amp; Limits (Enforced by Backend Controllers)</p>
                    @foreach($entitlementCatalog as $catSector => $fields)
                        @php
                            $grouped = collect($fields)->groupBy(fn($m) => $m['group'] ?? 'Features');
                        @endphp
                        <div x-show="formSector === '{{ $catSector }}' || formSector === 'all'" class="space-y-3">
                            @if($catSector !== $formSector)
                                <p class="text-[10px] font-mono uppercase text-zinc-400" x-show="formSector === 'all'">{{ ucfirst($catSector) }} Sector Entitlements</p>
                            @endif
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach($grouped as $groupName => $groupFields)
                                    <div class="rounded-xl border border-zinc-200/80 bg-zinc-50/70 p-3 space-y-2.5">
                                        <p class="text-[10px] font-mono uppercase tracking-wider text-zinc-500 font-bold">{{ $groupName }}</p>
                                        @foreach($groupFields as $fKey => $meta)
                                            @php $val = old("entitlements.{$fKey}", array_key_exists($fKey, $editEnts) ? $editEnts[$fKey] : $meta['default']); @endphp
                                            @if($meta['type'] === 'bool')
                                                <label class="flex items-center justify-between gap-2 text-xs py-0.5">
                                                    <span class="text-zinc-800">{{ $meta['label'] }}</span>
                                                    <input type="checkbox" name="entitlements[{{ $fKey }}]" value="1" @checked((bool) $val)>
                                                </label>
                                            @else
                                                <div class="flex items-center justify-between gap-2 text-xs py-0.5">
                                                    <label class="text-zinc-800">{{ $meta['label'] }}</label>
                                                    <input type="number" name="entitlements[{{ $fKey }}]" value="{{ $val }}"
                                                           placeholder="{{ $meta['type'] === 'nullable_int' ? 'Unlimited' : '' }}"
                                                           class="w-24 rounded-lg border border-zinc-200 px-2.5 py-1 text-xs bg-white text-right font-mono">
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Expiry Behavior & Rollout Option -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 space-y-2 text-xs">
                        <p class="text-[11px] font-black uppercase text-zinc-800">Expiry &amp; Grace Period Behavior</p>
                        <div class="flex items-center justify-between">
                            <span>Grace Period (Days)</span>
                            <input type="number" name="grace_days" min="0" max="90" value="{{ old('grace_days', $editExp['grace_days'] ?? 3) }}" class="w-20 rounded-lg border border-zinc-200 px-2 py-1 text-xs bg-white text-right">
                        </div>
                        <label class="flex items-center justify-between"><span class="text-zinc-700">Hide Public Storefront on Expiry</span><input type="checkbox" name="hide_storefront" value="1" @checked(old('hide_storefront', $editExp['hide_storefront'] ?? true))></label>
                        <label class="flex items-center justify-between"><span class="text-zinc-700">Hide Listings on Expiry</span><input type="checkbox" name="hide_listings" value="1" @checked(old('hide_listings', $editExp['hide_listings'] ?? true))></label>
                        <label class="flex items-center justify-between"><span class="text-zinc-700">Block New Listings on Expiry</span><input type="checkbox" name="block_new_listings" value="1" @checked(old('block_new_listings', $editExp['block_new_listings'] ?? true))></label>
                        <label class="flex items-center justify-between"><span class="text-zinc-700">Block Editing on Expiry</span><input type="checkbox" name="block_editing" value="1" @checked(old('block_editing', $editExp['block_editing'] ?? true))></label>
                        <label class="flex items-center justify-between"><span class="text-zinc-700">Keep Store Visible in Grace</span><input type="checkbox" name="grace_keep_visible" value="1" @checked(old('grace_keep_visible', $editExp['grace_keep_visible'] ?? true))></label>
                        <label class="flex items-center justify-between"><span class="text-zinc-700">Keep Features Active in Grace</span><input type="checkbox" name="grace_keep_features" value="1" @checked(old('grace_keep_features', $editExp['grace_keep_features'] ?? false))></label>
                    </div>

                    <div class="rounded-xl border border-zinc-200 bg-zinc-50/70 p-4 space-y-3 text-xs">
                        <div>
                            <label class="block text-[11px] font-black uppercase text-zinc-800 mb-1">Plan Description</label>
                            <textarea name="description" rows="2" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs bg-white">{{ old('description', $editPlan?->description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-black uppercase text-zinc-800 mb-1">Key Benefits (1 per line)</label>
                            <textarea name="benefits_text" rows="3" placeholder="Priority marketplace ranking&#10;Dedicated WhatsApp onboarding" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs bg-white">{{ $benefitsDefault }}</textarea>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-zinc-700 mb-1">Change Log Note</label>
                            <input type="text" name="change_notes" value="{{ old('change_notes') }}" placeholder="Summary of edits for audit trail" class="w-full rounded-xl border border-zinc-200 px-3 py-1.5 text-xs bg-white">
                        </div>
                    </div>
                </div>

                @if($editPlan)
                    <div class="rounded-xl border border-zinc-300 bg-zinc-100/80 p-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div>
                            <strong class="text-zinc-950">Existing Subscriber Rollout Policy</strong>
                            <p class="text-[11px] text-zinc-600">By default, existing subscribers stay on their immutable snapshot until renewal. Check to push updates immediately.</p>
                        </div>
                        <label class="inline-flex items-center gap-2 font-bold text-zinc-950 shrink-0 bg-white px-3 py-2 rounded-lg border border-zinc-300">
                            <input type="checkbox" name="apply_to_existing" value="1"> Push Updates to Existing Active Subscribers
                        </label>
                    </div>
                @endif

                <div class="flex items-center gap-3">
                    <button type="submit" class="px-6 py-3 rounded-xl bg-zinc-950 text-white text-xs font-black uppercase tracking-wider hover:bg-zinc-800 transition">
                        {{ $editPlan ? 'Save Plan Updates (Create v' . ($editPlan->current_version + 1) . ')' : 'Create Subscription Plan' }}
                    </button>
                    @if($editPlan)
                        <a href="{{ route('admin.vendor_management.plans', ['sector' => $currentSector]) }}" class="px-4 py-3 rounded-xl border border-zinc-200 text-xs font-semibold text-zinc-600 hover:bg-zinc-50">
                            Cancel
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Sidebar Column: Plan Version History & Quick Actions -->
        <div class="lg:col-span-4 space-y-4">
            @if($editPlan)
                <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                    <h4 class="text-xs font-black uppercase text-zinc-900">Plan Actions</h4>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.vendor_management.plans.duplicate', $editPlan) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 rounded-xl border border-zinc-200 text-xs font-bold hover:bg-zinc-50">Duplicate Plan</button>
                        </form>
                        @if($editPlan->status !== 'archived')
                            <form method="POST" action="{{ route('admin.vendor_management.plans.archive', $editPlan) }}">
                                @csrf
                                <button type="submit" class="px-3 py-2 rounded-xl border border-rose-200 text-xs font-bold text-rose-700 hover:bg-rose-50">Archive Plan</button>
                            </form>
                        @endif
                    </div>
                </div>

                <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-3">
                    <h4 class="text-xs font-black uppercase text-zinc-900">Version History ({{ $editPlan->versions->count() }})</h4>
                    <div class="divide-y divide-zinc-100 text-xs max-h-[420px] overflow-y-auto">
                        @foreach($editPlan->versions as $ver)
                            <div class="py-2.5 space-y-0.5">
                                <div class="flex items-center justify-between">
                                    <strong class="text-zinc-950">v{{ $ver->version_number }} • {{ $ver->is_free ? 'FREE' : '₹' . number_format($ver->price_amount / 100, 0) }}</strong>
                                    <span class="text-[10px] font-mono text-zinc-400">{{ $ver->created_at?->format('d M Y') }}</span>
                                </div>
                                <p class="text-[11px] text-zinc-600">{{ $ver->change_notes ?: 'Plan revision' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-white border border-zinc-200/80 rounded-2xl p-5 space-y-2 text-xs">
                <h4 class="text-xs font-black uppercase text-zinc-900">All Plans Directory ({{ $allPlans->count() }})</h4>
                <div class="divide-y divide-zinc-100 max-h-[360px] overflow-y-auto">
                    @foreach($allPlans as $ap)
                        <a href="{{ route('admin.vendor_management.plans', ['sector' => $ap->sector_key, 'edit' => $ap->id]) }}"
                           class="py-2 flex items-center justify-between gap-2 hover:bg-zinc-50 px-1.5 rounded-lg">
                            <div>
                                <span class="font-bold text-zinc-950">{{ $ap->name }}</span>
                                <span class="text-[10px] font-mono uppercase text-zinc-400 ml-1">{{ $ap->sector_key }}</span>
                            </div>
                            <span class="text-[11px] font-mono text-zinc-600">{{ $ap->is_free ? 'Free' : '₹' . number_format($ap->price_amount / 100, 0) }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin>
