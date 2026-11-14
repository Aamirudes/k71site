<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\OrderCommission;
use App\Models\SubscriptionPlan;
use App\Models\Vendor;
use App\Services\Monetization\CommissionService;
use App\Services\Monetization\SectorRegistryService;
use App\Services\Monetization\SubscriptionLifecycleService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlanAndCommissionController extends Controller
{
    public function __construct(
        protected SectorRegistryService $sectors,
        protected SubscriptionLifecycleService $lifecycle,
        protected CommissionService $commissionService
    ) {}

    /**
     * Subscription Plans & Versioning Console.
     */
    public function plans(Request $request)
    {
        $this->sectors->ensureDefaultThreeTierPlans();

        $sectorFilter = $request->query('sector', 'products');
        if ($sectorFilter === 'all_list') {
            $sectorFilter = '';
        }
        $statusFilter = $request->query('status', '');

        $allPlans = SubscriptionPlan::with(['versions', 'defaultCommissionRule'])
            ->withCount([
                'subscriptions',
                'subscriptions as active_subscriptions_count' => fn($q) => $q->where('status', 'active'),
            ])
            ->when($statusFilter !== '', fn($q) => $q->where('status', $statusFilter))
            ->orderBy('sector_key')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $plans = $sectorFilter !== ''
            ? $allPlans->where('sector_key', $sectorFilter)->values()
            : $allPlans;

        $sectorTierPlans = $allPlans->where('sector_key', $sectorFilter ?: 'products')
            ->sortBy('sort_order')
            ->take(3)
            ->values();

        $editPlan = $request->filled('edit')
            ? SubscriptionPlan::with('versions')->find((int) $request->query('edit'))
            : ($request->boolean('new') ? null : $sectorTierPlans->first());

        $sectors = $this->sectors->getAllSectors();
        $entitlementCatalog = $this->sectors->getEntitlementCatalog();
        $commissionRules = CommissionRule::where('is_active', true)->orderBy('name')->get();

        return view('admin.vendor-management.plans', compact(
            'plans',
            'allPlans',
            'sectorTierPlans',
            'editPlan',
            'sectors',
            'entitlementCatalog',
            'commissionRules',
            'sectorFilter',
            'statusFilter'
        ));
    }

    public function storePlan(Request $request)
    {
        $validated = $this->validatePlanPayload($request);
        $entitlements = $this->buildEntitlementsArray($request, $validated['sector_key']);
        $benefits = $this->parseBenefitsLines((string) ($validated['benefits_text'] ?? ''));

        $durationUnit = $validated['duration_unit'] ?? 'days';
        $durationValue = (int) ($validated['duration_value'] ?? ($validated['duration_days'] ?? 30));
        $durationDays = $durationUnit === 'months'
            ? max(1, $durationValue * 30)
            : max(1, (int) ($validated['duration_days'] ?? $durationValue));

        $plan = $this->lifecycle->createOrUpdatePlan([
            'code' => $validated['code'] ?? null,
            'name' => $validated['name'],
            'badge_text' => $validated['badge_text'] ?? null,
            'description' => $validated['description'] ?? null,
            'sector_key' => $validated['sector_key'],
            'tier_key' => $validated['tier_key'] ?? null,
            'applicable_sectors' => $validated['applicable_sectors'] ?? [$validated['sector_key']],
            'is_cross_sector' => $request->boolean('is_cross_sector'),
            'is_free' => $request->boolean('is_free'),
            'price_amount' => $request->boolean('is_free') ? 0 : (int) round(((float) ($validated['price_rupees'] ?? 0)) * 100),
            'currency' => 'INR',
            'duration_days' => $durationDays,
            'duration_unit' => $durationUnit,
            'duration_value' => $durationValue,
            'custom_duration_allowed' => $request->boolean('custom_duration_allowed', true),
            'default_commission_rule_id' => $validated['default_commission_rule_id'] ?? null,
            'commission_type' => $validated['commission_type'] ?? 'default',
            'commission_rate' => $validated['commission_rate'] ?? null,
            'commission_fixed_paise' => isset($validated['commission_fixed_rupees']) && $validated['commission_fixed_rupees'] !== ''
                ? (int) round(((float) $validated['commission_fixed_rupees']) * 100)
                : 0,
            'status' => $validated['status'] ?? 'active',
            'is_public_display' => $request->boolean('is_public_display', false),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'entitlements' => $entitlements,
            'benefits' => $benefits,
            'expiry_policy' => [
                'hide_storefront' => $request->boolean('hide_storefront', true),
                'hide_listings' => $request->boolean('hide_listings', true),
                'block_new_listings' => $request->boolean('block_new_listings', true),
                'block_editing' => $request->boolean('block_editing', true),
                'grace_days' => (int) ($validated['grace_days'] ?? 3),
                'grace_keep_visible' => $request->boolean('grace_keep_visible', true),
                'grace_keep_features' => $request->boolean('grace_keep_features', false),
            ],
        ], null, auth()->id(), $validated['change_notes'] ?? 'Initial plan created', $request->ip());

        return redirect()->route('admin.vendor_management.plans', ['sector' => $plan->sector_key, 'edit' => $plan->id])
            ->with('success', "Plan '{$plan->name}' (v{$plan->current_version}) created for sector '{$plan->sector_key}'.");
    }

    public function updatePlan(Request $request, SubscriptionPlan $plan)
    {
        $validated = $this->validatePlanPayload($request, $plan->id);
        $entitlements = $this->buildEntitlementsArray($request, $validated['sector_key']);
        $benefits = $request->has('benefits_text')
            ? $this->parseBenefitsLines((string) ($validated['benefits_text'] ?? ''))
            : ($plan->benefits_json ?? []);

        $durationUnit = $validated['duration_unit'] ?? ($plan->duration_unit ?? 'days');
        $durationValue = (int) ($validated['duration_value'] ?? ($validated['duration_days'] ?? ($plan->duration_value ?? 30)));
        $durationDays = $durationUnit === 'months'
            ? max(1, $durationValue * 30)
            : max(1, (int) ($validated['duration_days'] ?? $durationValue));

        $applyToExisting = $request->boolean('apply_to_existing', false);

        $updated = $this->lifecycle->createOrUpdatePlan([
            'code' => $validated['code'] ?? $plan->code,
            'name' => $validated['name'],
            'badge_text' => $validated['badge_text'] ?? $plan->badge_text,
            'description' => $validated['description'] ?? null,
            'sector_key' => $validated['sector_key'],
            'tier_key' => $validated['tier_key'] ?? $plan->tier_key,
            'applicable_sectors' => $validated['applicable_sectors'] ?? [$validated['sector_key']],
            'is_cross_sector' => $request->boolean('is_cross_sector'),
            'is_free' => $request->boolean('is_free'),
            'price_amount' => $request->boolean('is_free') ? 0 : (int) round(((float) ($validated['price_rupees'] ?? 0)) * 100),
            'currency' => 'INR',
            'duration_days' => $durationDays,
            'duration_unit' => $durationUnit,
            'duration_value' => $durationValue,
            'custom_duration_allowed' => $request->boolean('custom_duration_allowed', true),
            'default_commission_rule_id' => $validated['default_commission_rule_id'] ?? null,
            'commission_type' => $validated['commission_type'] ?? ($plan->commission_type ?? 'default'),
            'commission_rate' => $validated['commission_rate'] ?? $plan->commission_rate,
            'commission_fixed_paise' => isset($validated['commission_fixed_rupees']) && $validated['commission_fixed_rupees'] !== ''
                ? (int) round(((float) $validated['commission_fixed_rupees']) * 100)
                : ($plan->commission_fixed_paise ?? 0),
            'status' => $validated['status'] ?? $plan->status,
            'is_public_display' => $request->boolean('is_public_display', false),
            'sort_order' => (int) ($validated['sort_order'] ?? $plan->sort_order),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'entitlements' => $entitlements,
            'benefits' => $benefits,
            'apply_to_existing' => $applyToExisting,
            'expiry_policy' => [
                'hide_storefront' => $request->boolean('hide_storefront', true),
                'hide_listings' => $request->boolean('hide_listings', true),
                'block_new_listings' => $request->boolean('block_new_listings', true),
                'block_editing' => $request->boolean('block_editing', true),
                'grace_days' => (int) ($validated['grace_days'] ?? 3),
                'grace_keep_visible' => $request->boolean('grace_keep_visible', true),
                'grace_keep_features' => $request->boolean('grace_keep_features', false),
            ],
        ], $plan, auth()->id(), $validated['change_notes'] ?? 'Plan updated by admin', $request->ip());

        $msg = $applyToExisting
            ? "Plan '{$updated->name}' updated as v{$updated->current_version} and pushed to existing active subscribers."
            : "Plan '{$updated->name}' updated as v{$updated->current_version}. Existing vendor subscriptions retain their immutable snapshot unless upgraded manually.";

        return redirect()->route('admin.vendor_management.plans', ['sector' => $updated->sector_key, 'edit' => $updated->id])
            ->with('success', $msg);
    }

    public function duplicatePlan(Request $request, SubscriptionPlan $plan)
    {
        $latest = $plan->latestVersion();
        $copy = $this->lifecycle->createOrUpdatePlan([
            'name' => $plan->name . ' (Copy)',
            'description' => $plan->description,
            'sector_key' => $plan->sector_key,
            'applicable_sectors' => $plan->applicable_sectors ?: [$plan->sector_key],
            'is_cross_sector' => $plan->is_cross_sector,
            'is_free' => $plan->is_free,
            'price_amount' => $plan->price_amount,
            'duration_days' => $plan->duration_days,
            'default_commission_rule_id' => $plan->default_commission_rule_id,
            'status' => 'draft',
            'entitlements' => $latest?->entitlements_json ?? $plan->features ?? [],
            'expiry_policy' => $latest?->expiry_policy_json ?? [],
        ], null, auth()->id(), "Duplicated from plan #{$plan->id}", $request->ip());

        return redirect()->route('admin.vendor_management.plans', ['edit' => $copy->id])
            ->with('success', "Duplicated '{$plan->name}' as Draft '{$copy->name}'.");
    }

    public function archivePlan(Request $request, SubscriptionPlan $plan)
    {
        $plan->update(['status' => 'archived', 'is_active' => false]);
        AuditLog::create([
            'user_id' => auth()->id(),
            'sector_key' => $plan->sector_key,
            'auditable_type' => SubscriptionPlan::class,
            'auditable_id' => $plan->id,
            'action' => 'subscription_plan_archived',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Plan '{$plan->name}' archived (existing active vendor subscriptions continue until expiry).");
    }

    /**
     * Commission Rule Engine, Simulator & Ledger Console.
     */
    public function commissions(Request $request)
    {
        $rules = CommissionRule::with(['plan', 'vendor', 'category'])
            ->orderByRaw("CASE scope WHEN 'vendor' THEN 1 WHEN 'category' THEN 2 WHEN 'plan' THEN 3 WHEN 'sector' THEN 4 ELSE 5 END")
            ->orderByDesc('priority')
            ->orderByDesc('id')
            ->get();

        $ledger = OrderCommission::with(['order', 'vendor', 'rule'])
            ->when($request->filled('sector'), fn($q) => $q->where('sector_key', $request->query('sector')))
            ->when($request->filled('event_type'), fn($q) => $q->where('event_type', $request->query('event_type')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $sectors = $this->sectors->getAllSectors();
        $plans = SubscriptionPlan::orderBy('sector_key')->orderBy('name')->get();
        $vendors = Vendor::orderBy('shop_name')->get(['id', 'shop_name', 'vendor_type']);
        $categories = Category::orderBy('name')->get(['id', 'name', 'vertical']);

        // Simulator preview
        $simulation = null;
        if ($request->filled('sim_vendor_id') && $request->filled('sim_amount')) {
            $simVendor = Vendor::find((int) $request->query('sim_vendor_id'));
            if ($simVendor) {
                $simSector = $request->query('sim_sector') ?: $this->sectors->resolveVendorPrimarySector($simVendor);
                $simCategoryId = $request->filled('sim_category_id') ? (int) $request->query('sim_category_id') : null;
                $grossPaise = max(0, (int) round(((float) $request->query('sim_amount')) * 100));
                $rule = $this->commissionService->resolveEffectiveRule($simVendor, $simSector, $simCategoryId);
                $calc = $this->commissionService->calculateAmounts($grossPaise, (float) $rule['rate_percent'], (int) $rule['fixed_fee_paise']);
                $simulation = array_merge($rule, $calc);
            }
        }

        return view('admin.vendor-management.commissions', compact(
            'rules',
            'ledger',
            'sectors',
            'plans',
            'vendors',
            'categories',
            'simulation'
        ));
    }

    public function storeCommissionRule(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:160',
            'scope' => 'required|in:platform,sector,plan,vendor,category',
            'sector_key' => 'nullable|string|in:products,food,services,used,enquiry',
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'category_id' => 'nullable|exists:categories,id',
            'rate_percent' => 'required|numeric|min:0|max:100',
            'fixed_fee_rupees' => 'nullable|numeric|min:0|max:100000',
            'priority' => 'nullable|integer|min:0|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $rule = CommissionRule::create([
            'name' => $validated['name'],
            'scope' => $validated['scope'],
            'sector_key' => in_array($validated['scope'], ['sector', 'plan', 'category', 'vendor'], true) ? ($validated['sector_key'] ?? null) : null,
            'plan_id' => $validated['scope'] === 'plan' ? ($validated['plan_id'] ?? null) : null,
            'vendor_id' => $validated['scope'] === 'vendor' ? ($validated['vendor_id'] ?? null) : null,
            'category_id' => $validated['scope'] === 'category' ? ($validated['category_id'] ?? null) : null,
            'calculation_type' => ((float) ($validated['fixed_fee_rupees'] ?? 0) > 0) ? 'hybrid' : 'percentage',
            'rate_percent' => round((float) $validated['rate_percent'], 2),
            'fixed_fee_paise' => (int) round(((float) ($validated['fixed_fee_rupees'] ?? 0)) * 100),
            'priority' => (int) ($validated['priority'] ?? 10),
            'is_active' => true,
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        AuditLog::create([
            'user_id' => auth()->id(),
            'vendor_id' => $rule->vendor_id,
            'sector_key' => $rule->sector_key,
            'auditable_type' => CommissionRule::class,
            'auditable_id' => $rule->id,
            'action' => 'commission_rule_created',
            'new_values' => $rule->toArray(),
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Commission rule '{$rule->name}' ({$rule->rate_percent}%) saved.");
    }

    public function toggleCommissionRule(Request $request, CommissionRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);
        return back()->with('success', "Commission rule '{$rule->name}' is now " . ($rule->is_active ? 'ACTIVE' : 'PAUSED') . '.');
    }

    public function overrideOrderCommission(Request $request, OrderCommission $orderCommission)
    {
        $validated = $request->validate([
            'rate_percent' => 'required|numeric|min:0|max:100',
            'fixed_fee_rupees' => 'nullable|numeric|min:0|max:100000',
            'reason' => 'required|string|max:500',
        ]);

        $this->commissionService->overrideTransactionCommission(
            $orderCommission,
            (float) $validated['rate_percent'],
            (int) round(((float) ($validated['fixed_fee_rupees'] ?? 0)) * 100),
            $validated['reason'],
            auth()->id(),
            $request->ip()
        );

        return back()->with('success', "Transaction commission #{$orderCommission->id} manually adjusted cleanly.");
    }

    public function exportCommissionsCsv(): StreamedResponse
    {
        $rows = OrderCommission::with(['order', 'vendor'])->orderByDesc('id')->get();
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Order #', 'Vendor', 'Sector', 'Rule Source', 'Rate %', 'Fixed (INR)', 'Commission Base (INR)', 'Commission (INR)', 'Vendor Net (INR)', 'Event / Refund', 'Created At']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->order?->order_number ?? $r->order_number ?? ('#' . $r->transaction_id),
                    $r->vendor?->shop_name ?? ('Vendor #' . $r->vendor_id),
                    $r->sector_key,
                    $r->rule_source,
                    $r->rate_percent,
                    number_format($r->fixed_fee_paise / 100, 2, '.', ''),
                    number_format($r->commission_base_paise / 100, 2, '.', ''),
                    number_format($r->calculated_commission_paise / 100, 2, '.', ''),
                    number_format($r->vendor_net_paise / 100, 2, '.', ''),
                    $r->event_type . ($r->refund_state !== 'none' ? ' (' . $r->refund_state . ')' : ''),
                    $r->created_at?->toDateTimeString() ?? '',
                ]);
            }
            fclose($out);
        }, 'k71-commission-ledger-' . now()->format('Ymd-His') . '.csv');
    }

    protected function validatePlanPayload(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'code' => 'nullable|string|max:60',
            'name' => 'required|string|max:120',
            'badge_text' => 'nullable|string|max:60',
            'description' => 'nullable|string|max:1000',
            'sector_key' => 'required|string|in:products,food,services,used,enquiry,all',
            'tier_key' => 'nullable|string|in:starter,professional,elite',
            'applicable_sectors' => 'nullable|array',
            'applicable_sectors.*' => 'string|in:products,food,services,used,enquiry,all',
            'price_rupees' => 'nullable|numeric|min:0|max:10000000',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'duration_unit' => 'nullable|in:days,months',
            'duration_value' => 'nullable|integer|min:1|max:3650',
            'default_commission_rule_id' => 'nullable|exists:commission_rules,id',
            'commission_type' => 'nullable|in:default,percentage,fixed,hybrid,zero',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'commission_fixed_rupees' => 'nullable|numeric|min:0|max:100000',
            'status' => 'nullable|in:draft,active,archived',
            'sort_order' => 'nullable|integer|min:0|max:999',
            'grace_days' => 'nullable|integer|min:0|max:90',
            'benefits_text' => 'nullable|string|max:3000',
            'admin_notes' => 'nullable|string|max:2000',
            'change_notes' => 'nullable|string|max:500',
        ]);
    }

    protected function parseBenefitsLines(string $raw): array
    {
        return array_values(array_filter(array_map(
            fn ($line) => trim($line),
            preg_split('/\r\n|\r|\n/', $raw) ?: []
        )));
    }

    protected function buildEntitlementsArray(Request $request, string $sectorKey): array
    {
        $catalog = $this->sectors->getEntitlementsForSector($sectorKey);
        $raw = (array) $request->input('entitlements', []);
        $result = [];

        foreach ($catalog as $key => $meta) {
            if ($meta['type'] === 'bool') {
                $result[$key] = !empty($raw[$key]);
            } elseif ($meta['type'] === 'int') {
                $val = $raw[$key] ?? ($meta['default'] ?? 5);
                $result[$key] = max(0, (int) $val);
            } else { // nullable_int
                $val = $raw[$key] ?? null;
                $result[$key] = ($val === '' || $val === null) ? null : max(0, (int) $val);
            }
        }

        return $result;
    }
}
