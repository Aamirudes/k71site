<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\MonetizationSetting;
use App\Models\OnboardingLead;
use App\Models\Order;
use App\Models\OrderCommission;
use App\Models\ScheduledJobRun;
use App\Models\SubscriptionEvent;
use App\Models\SubscriptionNotification;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionRenewalRequest;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use App\Services\Monetization\CommissionService;
use App\Services\Monetization\EntitlementService;
use App\Services\Monetization\SectorRegistryService;
use App\Services\Monetization\SubscriptionLifecycleService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class VendorManagementController extends Controller
{
    public function __construct(
        protected SectorRegistryService $sectors,
        protected EntitlementService $entitlements,
        protected SubscriptionLifecycleService $lifecycle,
        protected CommissionService $commissions
    ) {}

    /**
     * Tab 1: Overview — Active Vendors, Expired Plans, Pending Renewals, Expiring Soon & Revenue Summaries.
     */
    public function overview()
    {
        $metrics = [
            'active_vendors' => VendorSubscription::whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD])->distinct('vendor_id')->count('vendor_id'),
            'active_subs' => VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)->count(),
            'expired_subs' => VendorSubscription::where('status', VendorSubscription::STATUS_EXPIRED)->count(),
            'pending_renewals' => SubscriptionRenewalRequest::where('status', 'pending')->count(),
            'expiring_7d' => VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)
                ->whereNotNull('ends_at')
                ->whereBetween('ends_at', [now(), now()->addDays(7)])
                ->count(),
            'pending_activation' => VendorSubscription::where('status', VendorSubscription::STATUS_PENDING_ACTIVATION)->count(),
            'sub_revenue_paise' => (int) VendorSubscription::where('external_payment_received', true)->sum('external_payment_amount'),
            'total_vendors' => Vendor::count(),
        ];

        $upcomingExpirations = VendorSubscription::with(['vendor', 'plan'])
            ->where('status', VendorSubscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->whereBetween('ends_at', [now(), now()->addDays(14)])
            ->orderBy('ends_at')
            ->take(12)
            ->get();

        $expiredList = VendorSubscription::with(['vendor', 'plan'])
            ->where('status', VendorSubscription::STATUS_EXPIRED)
            ->orderByDesc('expired_at')
            ->take(10)
            ->get();

        $pendingRenewalList = SubscriptionRenewalRequest::with(['vendor', 'requestedPlan', 'subscription'])
            ->where('status', 'pending')
            ->latest('id')
            ->take(10)
            ->get();

        $sectorHealth = [];
        foreach ($this->sectors->getAllSectors() as $sec) {
            $subs = VendorSubscription::where('sector_key', $sec->key)->get();
            $sectorHealth[] = [
                'sector' => $sec,
                'active' => $subs->where('status', VendorSubscription::STATUS_ACTIVE)->count(),
                'expired' => $subs->where('status', VendorSubscription::STATUS_EXPIRED)->count(),
                'pending' => $subs->where('status', VendorSubscription::STATUS_PENDING_ACTIVATION)->count(),
                'revenue_paise' => (int) $subs->where('external_payment_received', true)->sum('external_payment_amount'),
            ];
        }

        $strictEnforcement = $this->entitlements->isStrictEnforcementEnabled();
        $lastSweepAt = MonetizationSetting::getValue('last_expiry_run_at');

        return view('admin.vendor-management.overview', compact(
            'metrics',
            'upcomingExpirations',
            'expiredList',
            'pendingRenewalList',
            'sectorHealth',
            'strictEnforcement',
            'lastSweepAt'
        ));
    }

    /**
     * Main Vendor Management Directory & Status Queues.
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all');
        $search = trim((string) $request->query('search', ''));
        $sectorFilter = $request->query('sector', '');
        $planFilter = $request->query('plan_id', '');
        $paymentFilter = $request->query('payment_status', '');
        $sort = $request->query('sort', 'latest');

        $query = Vendor::query()
            ->with(['user', 'subscriptions' => fn($q) => $q->with('plan')->orderByDesc('id')])
            ->withCount('products');

        if ($search !== '') {
            $escaped = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('shop_name', 'like', "%{$escaped}%")
                  ->orWhere('slug', 'like', "%{$escaped}%")
                  ->orWhere('email', 'like', "%{$escaped}%")
                  ->orWhere('contact_person', 'like', "%{$escaped}%")
                  ->orWhere('contact_phone', 'like', "%{$escaped}%")
                  ->orWhere('whatsapp_number', 'like', "%{$escaped}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('email', 'like', "%{$escaped}%")->orWhere('first_name', 'like', "%{$escaped}%"));
            });
        }

        if ($sectorFilter !== '') {
            $types = $this->sectors->getVendorTypesForSector($sectorFilter);
            $query->where(function ($q) use ($sectorFilter, $types) {
                $q->whereIn('vendor_type', $types)
                  ->orWhereJsonContains('operating_sectors', $sectorFilter);
            });
        }

        if ($planFilter !== '') {
            $query->whereHas('subscriptions', fn($sq) => $sq->where('plan_id', (int) $planFilter));
        }

        if ($paymentFilter === 'received') {
            $query->whereHas('subscriptions', fn($sq) => $sq->where('external_payment_received', true));
        } elseif ($paymentFilter === 'pending') {
            $query->whereHas('subscriptions', fn($sq) => $sq->where('external_payment_received', false)->where('is_free_snapshot', false));
        }

        // Organized area tabs
        switch ($tab) {
            case 'pending':
                $query->where(function ($q) {
                    $q->whereIn('verification_status', ['pending_review', 'unverified'])
                      ->orWhere('status', 'pending');
                });
                break;
            case 'verified':
                $query->where('verification_status', 'verified');
                break;
            case 'active':
                $query->whereHas('subscriptions', fn($sq) => $sq->whereIn('status', [
                    VendorSubscription::STATUS_ACTIVE,
                    VendorSubscription::STATUS_GRACE_PERIOD,
                ]));
                break;
            case 'expiring':
                $query->whereHas('subscriptions', fn($sq) => $sq->where('status', VendorSubscription::STATUS_ACTIVE)
                    ->whereNotNull('ends_at')
                    ->whereBetween('ends_at', [now(), now()->addDays(7)]));
                break;
            case 'expired':
                $query->whereHas('subscriptions', fn($sq) => $sq->where('status', VendorSubscription::STATUS_EXPIRED));
                break;
            case 'suspended':
                $query->where(function ($q) {
                    $q->whereIn('verification_status', ['suspended', 'rejected'])
                      ->orWhereIn('status', ['suspended', 'closed', 'rejected'])
                      ->orWhereHas('subscriptions', fn($sq) => $sq->whereIn('status', [VendorSubscription::STATUS_SUSPENDED, VendorSubscription::STATUS_CANCELLED]));
                });
                break;
            case 'unsubscribed':
                $query->where('verification_status', 'verified')
                    ->whereDoesntHave('subscriptions', fn($sq) => $sq->whereIn('status', [
                        VendorSubscription::STATUS_ACTIVE,
                        VendorSubscription::STATUS_GRACE_PERIOD,
                    ]));
                break;
        }

        if ($sort === 'name') {
            $query->orderBy('shop_name');
        } elseif ($sort === 'expiring_first') {
            $query->orderBy('id');
        } else {
            $query->latest('id');
        }

        $vendors = $query->paginate(20)->withQueryString();

        // Tab metrics (real counts)
        $stats = [
            'all' => Vendor::count(),
            'pending' => Vendor::whereIn('verification_status', ['pending_review', 'unverified'])->orWhere('status', 'pending')->count(),
            'verified' => Vendor::where('verification_status', 'verified')->count(),
            'active_subs' => VendorSubscription::whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD])->count(),
            'pending_activation' => VendorSubscription::where('status', VendorSubscription::STATUS_PENDING_ACTIVATION)->count(),
            'expiring_7d' => VendorSubscription::where('status', VendorSubscription::STATUS_ACTIVE)->whereNotNull('ends_at')->whereBetween('ends_at', [now(), now()->addDays(7)])->count(),
            'expired_subs' => VendorSubscription::where('status', VendorSubscription::STATUS_EXPIRED)->count(),
            'suspended' => Vendor::whereIn('verification_status', ['suspended', 'rejected'])->orWhereIn('status', ['suspended', 'closed'])->count(),
            'verified_without_sub' => Vendor::where('verification_status', 'verified')
                ->whereDoesntHave('subscriptions', fn($sq) => $sq->whereIn('status', [
                    VendorSubscription::STATUS_ACTIVE,
                    VendorSubscription::STATUS_GRACE_PERIOD,
                ]))
                ->count(),
            'leads_count' => OnboardingLead::whereNotIn('status', ['converted', 'archived'])->count(),
            'renewal_requests' => SubscriptionRenewalRequest::where('status', 'pending')->count(),
        ];

        $sectors = $this->sectors->getAllSectors();
        $plans = SubscriptionPlan::where('status', '!=', 'archived')->orderBy('sector_key')->orderBy('sort_order')->get();
        $strictEnforcement = $this->entitlements->isStrictEnforcementEnabled();

        return view('admin.vendor-management.index', compact(
            'vendors',
            'tab',
            'search',
            'sectorFilter',
            'planFilter',
            'paymentFilter',
            'sort',
            'stats',
            'sectors',
            'plans',
            'strictEnforcement'
        ));
    }

    /**
     * Dedicated Single-Vendor Control Workspace:
     * Verification, Subscriptions per sector, Plan Assignment, Offline Payment Recording, Manual Activation, Commission Override, Logs.
     */
    public function show(Vendor $vendor)
    {
        $vendor->load([
            'user',
            'settings',
            'subscriptions' => fn($q) => $q->with(['plan.versions', 'planVersion', 'commissionRule', 'events', 'assignedByUser', 'activatedByUser'])->orderByDesc('id'),
            'commissionRules',
            'renewalRequests' => fn($q) => $q->latest()->take(10),
        ]);

        // Trigger lightweight JIT expiry check for accuracy
        $this->entitlements->syncJustInTimeExpiry($vendor);
        $vendor->load(['subscriptions.plan.versions', 'subscriptions.planVersion', 'subscriptions.events']);

        $allSectors = $this->sectors->getAllSectors();
        $vendorPrimarySector = $this->sectors->resolveVendorPrimarySector($vendor);
        $vendorSectors = $this->sectors->resolveVendorSectors($vendor);

        $plans = SubscriptionPlan::with('versions')
            ->where('status', 'active')
            ->orderBy('sector_key')
            ->orderBy('sort_order')
            ->get();

        $commissionRules = \App\Models\CommissionRule::where('is_active', true)->orderBy('scope')->orderBy('name')->get();

        // Build sector-by-sector usage summary + active commission preview
        $sectorSummaries = [];
        foreach ($allSectors as $sector) {
            $effectiveSub = $this->entitlements->resolveEffectiveSubscription($vendor, $sector->key);
            $commissionRule = $this->commissions->resolveEffectiveRule($vendor, $sector->key, null, $effectiveSub);
            $usage = $this->entitlements->getUsageSummaryForVendor($vendor, $sector->key);
            $sectorSummaries[$sector->key] = [
                'sector' => $sector,
                'effective_sub' => $effectiveSub,
                'commission' => $commissionRule,
                'usage' => $usage,
            ];
        }

        // WhatsApp reminder drafts for active/expiring/grace/expired subscriptions
        $whatsappDrafts = [];
        foreach ($vendor->subscriptions as $sub) {
            $whatsappDrafts[$sub->id] = $this->lifecycle->buildWhatsAppReminderDraft($sub);
        }

        $linkedLead = OnboardingLead::where('linked_vendor_id', $vendor->id)->first();
        $auditLogs = AuditLog::with('user')
            ->where(function ($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id)
                  ->orWhere(fn($aq) => $aq->where('auditable_type', Vendor::class)->where('auditable_id', $vendor->id));
            })
            ->latest('id')
            ->take(25)
            ->get();

        return view('admin.vendor-management.show', compact(
            'vendor',
            'allSectors',
            'vendorPrimarySector',
            'vendorSectors',
            'plans',
            'commissionRules',
            'sectorSummaries',
            'whatsappDrafts',
            'linkedLead',
            'auditLogs'
        ));
    }

    /**
     * Manual Admin Verification Decision (never auto-activates any subscription).
     */
    public function updateVerification(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'verification_status' => 'required|in:unverified,pending_review,verified,rejected,suspended',
            'verification_notes' => 'nullable|string|max:2000',
            'contact_person' => 'nullable|string|max:120',
            'whatsapp_number' => 'nullable|string|max:30',
            'operating_sectors' => 'nullable|array',
            'operating_sectors.*' => 'string|in:products,food,services,used,enquiry',
        ]);

        if (isset($validated['contact_person']) || isset($validated['whatsapp_number']) || isset($validated['operating_sectors'])) {
            $vendor->update(array_filter([
                'contact_person' => $validated['contact_person'] ?? $vendor->contact_person,
                'whatsapp_number' => $validated['whatsapp_number'] ?? $vendor->whatsapp_number,
                'operating_sectors' => $validated['operating_sectors'] ?? $vendor->operating_sectors,
            ], fn($v) => $v !== null));
        }

        $this->lifecycle->updateVerificationStatus(
            $vendor,
            $validated['verification_status'],
            auth()->id(),
            $validated['verification_notes'] ?? null,
            $request->ip()
        );

        return back()->with('success', "Vendor verification updated to '" . ucfirst(str_replace('_', ' ', $validated['verification_status'])) . "'. Assign and activate a sector plan separately when ready.");
    }

    /**
     * Step 1: Assign a Plan to Vendor in PENDING_ACTIVATION state.
     */
    public function assignPlan(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'sector_key' => 'required|string|in:products,food,services,used,enquiry,all',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'commission_rule_id' => 'nullable|exists:commission_rules,id',
            'commission_rate_override' => 'nullable|numeric|min:0|max:100',
            'commission_fixed_override_rupees' => 'nullable|numeric|min:0|max:100000',
            'grace_days' => 'nullable|integer|min:0|max:90',
            'grace_keep_visible' => 'nullable|boolean',
            'grace_keep_features' => 'nullable|boolean',
            'auto_reminder_enabled' => 'nullable|boolean',
            'internal_notes' => 'nullable|string|max:2000',
            'admin_reason' => 'nullable|string|max:500',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        try {
            $sub = $this->lifecycle->assignSubscription(
                $vendor,
                $plan,
                $validated['sector_key'],
                [
                    'duration_days' => $validated['duration_days'] ?? $plan->duration_days,
                    'starts_at' => $validated['starts_at'] ?? null,
                    'ends_at' => $validated['ends_at'] ?? null,
                    'commission_rule_id' => $validated['commission_rule_id'] ?? null,
                    'commission_rate_override' => $validated['commission_rate_override'] ?? null,
                    'commission_fixed_override' => isset($validated['commission_fixed_override_rupees']) && $validated['commission_fixed_override_rupees'] !== ''
                        ? (int) round(((float) $validated['commission_fixed_override_rupees']) * 100)
                        : null,
                    'grace_days' => $validated['grace_days'] ?? null,
                    'grace_keep_visible' => $request->has('grace_keep_visible') ? $request->boolean('grace_keep_visible') : null,
                    'grace_keep_features' => $request->has('grace_keep_features') ? $request->boolean('grace_keep_features') : null,
                    'auto_reminder_enabled' => $request->boolean('auto_reminder_enabled', true),
                    'internal_notes' => $validated['internal_notes'] ?? null,
                    'admin_reason' => $validated['admin_reason'] ?? null,
                ],
                auth()->id(),
                $request->ip()
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return back()->with('success', "Assigned plan '{$plan->name}' (v{$sub->plan_version_number}) for sector '{$sub->sector_key}' in PENDING ACTIVATION status. Record payment or confirm activation when ready.");
    }

    /**
     * Step 2: Record Admin External/WhatsApp Payment Confirmation (DOES NOT auto-activate!).
     */
    public function recordPayment(Request $request, VendorSubscription $subscription)
    {
        $validated = $request->validate([
            'external_payment_received' => 'required|boolean',
            'external_payment_date' => 'nullable|date',
            'external_payment_amount_rupees' => 'nullable|numeric|min:0|max:10000000',
            'external_payment_method' => 'nullable|string|max:100',
            'external_payment_ref' => 'nullable|string|max:150',
            'external_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf,webp|max:5120',
            'internal_notes' => 'nullable|string|max:2000',
        ]);

        $receiptPath = $subscription->external_receipt_path;
        if ($request->hasFile('external_receipt')) {
            $receiptPath = $request->file('external_receipt')->store('subscriptions/receipts', 'public');
            k71_sync_public_storage($receiptPath);
        }

        $this->lifecycle->recordExternalPayment($subscription, [
            'external_payment_received' => (bool) $validated['external_payment_received'],
            'external_payment_date' => $validated['external_payment_date'] ?? now(),
            'external_payment_amount' => isset($validated['external_payment_amount_rupees']) && $validated['external_payment_amount_rupees'] !== ''
                ? (int) round(((float) $validated['external_payment_amount_rupees']) * 100)
                : null,
            'external_payment_method' => $validated['external_payment_method'] ?? null,
            'external_payment_ref' => $validated['external_payment_ref'] ?? null,
            'external_receipt_path' => $receiptPath,
            'internal_notes' => $validated['internal_notes'] ?? $subscription->internal_notes,
        ], auth()->id(), $request->ip());

        return back()->with('success', "External payment confirmation recorded on subscription #{$subscription->id}. Subscription remains '{$subscription->fresh()->status}' until you explicitly confirm activation.");
    }

    /**
     * Step 3: Explicit Admin Activation Confirmation.
     */
    public function activateSubscription(Request $request, VendorSubscription $subscription)
    {
        $validated = $request->validate([
            'confirm_activation' => 'accepted',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after:starts_at',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'replace_existing' => 'nullable|boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $sub = $this->lifecycle->activateSubscription($subscription, [
                'starts_at' => $validated['starts_at'] ?? null,
                'ends_at' => $validated['ends_at'] ?? null,
                'duration_days' => $validated['duration_days'] ?? $subscription->duration_days,
                'replace_existing' => $request->boolean('replace_existing', true),
                'reason' => $validated['reason'] ?? null,
            ], auth()->id(), $request->ip());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Subscription #{$sub->id} ({$sub->plan_name_snapshot} for {$sub->sector_key}) is now ACTIVE until " . ($sub->ends_at?->format('d M Y') ?? 'unlimited') . '.');
    }

    /**
     * Manual Renewal / Extension.
     */
    public function renewSubscription(Request $request, VendorSubscription $subscription)
    {
        $validated = $request->validate([
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'ends_at' => 'nullable|date|after:today',
            'external_payment_received' => 'nullable|boolean',
            'external_payment_amount_rupees' => 'nullable|numeric|min:0',
            'external_payment_method' => 'nullable|string|max:100',
            'external_payment_ref' => 'nullable|string|max:150',
            'sync_to_latest_plan_version' => 'nullable|boolean',
            'reset_from_now' => 'nullable|boolean',
            'reason' => 'required|string|max:500',
        ]);

        $sub = $this->lifecycle->renewSubscription($subscription, [
            'duration_days' => $validated['duration_days'] ?? $subscription->duration_days,
            'ends_at' => $validated['ends_at'] ?? null,
            'external_payment_received' => $request->boolean('external_payment_received'),
            'external_payment_amount' => isset($validated['external_payment_amount_rupees']) && $validated['external_payment_amount_rupees'] !== ''
                ? (int) round(((float) $validated['external_payment_amount_rupees']) * 100)
                : $subscription->price_amount_snapshot,
            'external_payment_method' => $validated['external_payment_method'] ?? null,
            'external_payment_ref' => $validated['external_payment_ref'] ?? null,
            'sync_to_latest_plan_version' => $request->boolean('sync_to_latest_plan_version'),
            'reset_from_now' => $request->boolean('reset_from_now'),
            'reason' => $validated['reason'],
        ], auth()->id(), $request->ip());

        return back()->with('success', "Subscription #{$sub->id} manually renewed. Active until " . ($sub->ends_at?->format('d M Y') ?? 'unlimited') . '.');
    }

    public function suspendSubscription(Request $request, VendorSubscription $subscription)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $this->lifecycle->suspendSubscription($subscription, $validated['reason'], auth()->id(), $request->ip());
        return back()->with('success', "Subscription #{$subscription->id} suspended and public sector access paused.");
    }

    public function cancelSubscription(Request $request, VendorSubscription $subscription)
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $this->lifecycle->cancelSubscription($subscription, $validated['reason'], auth()->id(), $request->ip());
        return back()->with('success', "Subscription #{$subscription->id} cancelled.");
    }

    public function applyLatestVersion(Request $request, VendorSubscription $subscription)
    {
        $plan = $subscription->plan;
        $latestVersion = $plan?->latestVersion();
        if (!$latestVersion) {
            return back()->with('error', 'No newer plan version found.');
        }

        $this->lifecycle->applyPlanVersion(
            $subscription,
            $latestVersion,
            auth()->id(),
            $request->input('reason', 'Admin opted subscription into latest plan version v' . $latestVersion->version_number),
            $request->ip()
        );

        return back()->with('success', "Subscription #{$subscription->id} upgraded to plan version v{$latestVersion->version_number}.");
    }

    /**
     * WhatsApp / Offline Vendor Onboarding Leads Tracker.
     */
    public function leads(Request $request)
    {
        $statusFilter = $request->query('status', '');
        $search = trim((string) $request->query('search', ''));

        $escaped = addcslashes($search, '%_\\');
        $query = OnboardingLead::with(['linkedVendor', 'proposedPlan', 'creator'])
            ->when($statusFilter !== '', fn($q) => $q->where('status', $statusFilter))
            ->when($search !== '', fn($q) => $q->where(function ($sq) use ($escaped) {
                $sq->where('business_name', 'like', "%{$escaped}%")
                   ->orWhere('contact_person', 'like', "%{$escaped}%")
                   ->orWhere('whatsapp_number', 'like', "%{$escaped}%")
                   ->orWhere('city', 'like', "%{$escaped}%");
            }))
            ->latest('id');

        $leads = $query->paginate(20)->withQueryString();
        $sectors = $this->sectors->getAllSectors();
        $plans = SubscriptionPlan::where('status', 'active')->orderBy('sector_key')->get();
        $vendors = Vendor::orderBy('shop_name')->get(['id', 'shop_name', 'slug']);

        return view('admin.vendor-management.leads', compact('leads', 'statusFilter', 'search', 'sectors', 'plans', 'vendors'));
    }

    public function storeLead(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:150',
            'contact_person' => 'nullable|string|max:120',
            'whatsapp_number' => 'required|string|max:25',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:255',
            'city' => 'nullable|string|max:100',
            'sector_key' => 'nullable|string|in:products,food,services,used,enquiry',
            'status' => 'nullable|string|in:new,contacted,negotiating,converted,archived',
            'proposed_plan_id' => 'nullable|exists:subscription_plans,id',
            'notes' => 'nullable|string|max:2000',
            'follow_up_at' => 'nullable|date',
        ]);

        OnboardingLead::create([
            'business_name' => $validated['business_name'],
            'contact_person' => $validated['contact_person'] ?? null,
            'whatsapp_number' => $validated['whatsapp_number'],
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'city' => $validated['city'] ?? null,
            'sector_key' => $validated['sector_key'] ?? 'products',
            'status' => $validated['status'] ?? 'new',
            'proposed_plan_id' => $validated['proposed_plan_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'follow_up_at' => $validated['follow_up_at'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return back()->with('success', 'WhatsApp onboarding lead added.');
    }

    public function updateLead(Request $request, OnboardingLead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,contacted,negotiating,converted,archived',
            'linked_vendor_id' => 'nullable|exists:vendors,id',
            'proposed_plan_id' => 'nullable|exists:subscription_plans,id',
            'notes' => 'nullable|string|max:2000',
            'follow_up_at' => 'nullable|date',
        ]);

        $lead->update([
            'status' => $validated['status'],
            'linked_vendor_id' => array_key_exists('linked_vendor_id', $validated) ? $validated['linked_vendor_id'] : $lead->linked_vendor_id,
            'proposed_plan_id' => array_key_exists('proposed_plan_id', $validated) ? $validated['proposed_plan_id'] : $lead->proposed_plan_id,
            'notes' => array_key_exists('notes', $validated) ? $validated['notes'] : $lead->notes,
            'follow_up_at' => array_key_exists('follow_up_at', $validated) ? $validated['follow_up_at'] : $lead->follow_up_at,
            'updated_by' => auth()->id(),
        ]);

        if (!empty($validated['linked_vendor_id'])) {
            $vendor = Vendor::find($validated['linked_vendor_id']);
            if ($vendor && !$vendor->whatsapp_number) {
                $vendor->update([
                    'whatsapp_number' => $lead->whatsapp_number,
                    'contact_person' => $vendor->contact_person ?: $lead->contact_person,
                ]);
            }
        }

        return back()->with('success', "Lead '{$lead->business_name}' updated.");
    }

    /**
     * Subscription Events, Offline Payment Confirmations & Renewal Requests Queue.
     */
    public function history(Request $request)
    {
        $statusFilter = $request->query('status', '');

        $events = SubscriptionEvent::with(['vendor', 'subscription', 'actor'])
            ->latest('id')
            ->paginate(25, ['*'], 'events_page');

        $manualPayments = VendorSubscription::with(['vendor', 'plan', 'assignedByUser', 'activatedByUser'])
            ->where('external_payment_received', true)
            ->orderByDesc('external_payment_date')
            ->paginate(15, ['*'], 'payments_page');

        $renewalRequests = SubscriptionRenewalRequest::with(['vendor', 'currentSubscription', 'requestedPlan', 'handledBy'])
            ->when($statusFilter !== '', fn ($q) => $q->where('status', $statusFilter))
            ->orderByRaw("CASE status WHEN 'pending' THEN 0 WHEN 'reviewed' THEN 1 ELSE 2 END")
            ->latest('id')
            ->paginate(15, ['*'], 'requests_page')
            ->withQueryString();

        $activePlans = SubscriptionPlan::where('status', 'active')
            ->orderBy('sector_key')
            ->orderBy('sort_order')
            ->get();

        return view('admin.vendor-management.history', compact('events', 'manualPayments', 'renewalRequests', 'activePlans', 'statusFilter'));
    }

    public function resolveRenewalRequest(Request $request, SubscriptionRenewalRequest $renewalRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,fulfilled,dismissed,rejected,approved',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $renewalRequest->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? $renewalRequest->admin_notes,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', "Renewal request #{$renewalRequest->id} marked as '{$validated['status']}'.");
    }

    public function replyRenewalRequest(Request $request, SubscriptionRenewalRequest $renewalRequest)
    {
        $validated = $request->validate([
            'admin_reply' => 'required|string|max:2000',
            'status' => 'nullable|in:pending,reviewed,approved',
        ]);

        $this->lifecycle->replyToRenewalRequest(
            $renewalRequest,
            $validated['admin_reply'],
            auth()->id(),
            $validated['status'] ?? null,
            $request->ip()
        );

        return back()->with('success', "Reply sent to vendor for renewal request #{$renewalRequest->id}.");
    }

    public function rejectRenewalRequest(Request $request, SubscriptionRenewalRequest $renewalRequest)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $this->lifecycle->rejectRenewalRequest(
            $renewalRequest,
            $validated['rejection_reason'],
            auth()->id(),
            $request->ip()
        );

        return back()->with('success', "Renewal request #{$renewalRequest->id} rejected with explanation.");
    }

    public function approveRenewRenewalRequest(Request $request, SubscriptionRenewalRequest $renewalRequest)
    {
        $validated = $request->validate([
            'plan_id' => 'nullable|exists:subscription_plans,id',
            'duration_days' => 'nullable|integer|min:1|max:3650',
            'external_payment_received' => 'nullable|boolean',
            'external_payment_amount_rupees' => 'nullable|numeric|min:0',
            'external_payment_method' => 'nullable|string|max:100',
            'external_payment_ref' => 'nullable|string|max:150',
            'admin_reply' => 'nullable|string|max:2000',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $sub = $this->lifecycle->approveAndRenewFromRequest($renewalRequest, [
                'plan_id' => $validated['plan_id'] ?? null,
                'duration_days' => $validated['duration_days'] ?? null,
                'external_payment_received' => $request->boolean('external_payment_received'),
                'external_payment_amount' => isset($validated['external_payment_amount_rupees']) && $validated['external_payment_amount_rupees'] !== ''
                    ? (int) round(((float) $validated['external_payment_amount_rupees']) * 100)
                    : null,
                'external_payment_method' => $validated['external_payment_method'] ?? 'whatsapp_upi',
                'external_payment_ref' => $validated['external_payment_ref'] ?? null,
                'admin_reply' => $validated['admin_reply'] ?? null,
                'reason' => $validated['reason'] ?? null,
            ], auth()->id(), $request->ip());
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Renewal request #{$renewalRequest->id} approved! Subscription #{$sub->id} ({$sub->sector_key}) is now ACTIVE until " . ($sub->ends_at?->format('d M Y') ?? 'renewed term') . '.');
    }

    /**
     * Expiry Automation Settings, Reminders Inbox & Cron Runner.
     */
    public function automation()
    {
        $settings = [
            'enforce_subscription_visibility' => (bool) MonetizationSetting::getValue('enforce_subscription_visibility', true),
            'default_grace_days' => (int) MonetizationSetting::getValue('default_grace_days', 3),
            'default_grace_keep_visible' => (bool) MonetizationSetting::getValue('default_grace_keep_visible', true),
            'default_grace_keep_features' => (bool) MonetizationSetting::getValue('default_grace_keep_features', false),
            'default_reminder_days' => implode(',', (array) MonetizationSetting::getValue('default_reminder_days', [7, 3, 1])),
            'whatsapp_renewal_template' => (string) MonetizationSetting::getValue(
                'whatsapp_renewal_template',
                'Hello {contact_person}, your K71 {sector_name} subscription for {shop_name} (Plan: {plan_name}) expires on {expiry_date} ({days_remaining} day(s) left). Reply on WhatsApp to renew.'
            ),
            'last_expiry_run_at' => MonetizationSetting::getValue('last_expiry_run_at'),
        ];

        $jobRuns = ScheduledJobRun::orderByDesc('id')->take(20)->get();
        $notifications = SubscriptionNotification::with(['vendor', 'subscription'])
            ->latest('id')
            ->take(25)
            ->get();

        return view('admin.vendor-management.automation', compact('settings', 'jobRuns', 'notifications'));
    }

    public function updateAutomationSettings(Request $request)
    {
        $validated = $request->validate([
            'enforce_subscription_visibility' => 'nullable|boolean',
            'default_grace_days' => 'required|integer|min:0|max:90',
            'default_grace_keep_visible' => 'nullable|boolean',
            'default_grace_keep_features' => 'nullable|boolean',
            'default_reminder_days' => 'required|string|max:50',
            'whatsapp_renewal_template' => 'required|string|max:1000',
        ]);

        $daysArray = array_values(array_unique(array_filter(array_map('intval', explode(',', $validated['default_reminder_days'])), fn($d) => $d > 0)));
        if (empty($daysArray)) {
            $daysArray = [7, 3, 1];
        }
        rsort($daysArray);

        MonetizationSetting::setValue('enforce_subscription_visibility', $request->boolean('enforce_subscription_visibility'), 'bool');
        MonetizationSetting::setValue('default_grace_days', (int) $validated['default_grace_days'], 'int');
        MonetizationSetting::setValue('default_grace_keep_visible', $request->boolean('default_grace_keep_visible'), 'bool');
        MonetizationSetting::setValue('default_grace_keep_features', $request->boolean('default_grace_keep_features'), 'bool');
        MonetizationSetting::setValue('default_reminder_days', $daysArray, 'json');
        MonetizationSetting::setValue('whatsapp_renewal_template', $validated['whatsapp_renewal_template'], 'string');

        AuditLog::create([
            'user_id' => auth()->id(),
            'auditable_type' => MonetizationSetting::class,
            'auditable_id' => 1,
            'action' => 'monetization_settings_updated',
            'reason' => 'Admin updated expiry, grace & WhatsApp template settings',
            'new_values' => $validated,
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Expiry, grace period, visibility & reminder settings saved.');
    }

    public function runExpiryNow()
    {
        $run = $this->lifecycle->runScheduledExpirySweep('admin_manual');
        return back()->with('success', "Expiry sweep finished ({$run->summary}).");
    }

    /**
     * Reports & Revenue Analytics (real DB calculations only).
     */
    public function reports(Request $request)
    {
        $startDate = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : now()->subDays(90)->startOfDay();
        $endDate = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();

        // 1. Subscriptions by sector & status
        $sectorBreakdown = [];
        foreach ($this->sectors->getAllSectors() as $sector) {
            $subs = VendorSubscription::where('sector_key', $sector->key)->get();
            $sectorBreakdown[] = [
                'key' => $sector->key,
                'name' => $sector->name,
                'active' => $subs->where('status', VendorSubscription::STATUS_ACTIVE)->count(),
                'grace' => $subs->where('status', VendorSubscription::STATUS_GRACE_PERIOD)->count(),
                'pending' => $subs->where('status', VendorSubscription::STATUS_PENDING_ACTIVATION)->count(),
                'expired' => $subs->where('status', VendorSubscription::STATUS_EXPIRED)->count(),
                'sub_revenue_paise' => $subs->where('external_payment_received', true)->sum('external_payment_amount'),
            ];
        }

        // 2. Subscriptions & revenue by plan
        $planBreakdown = SubscriptionPlan::withCount('subscriptions')
            ->get()
            ->map(function ($plan) {
                $paid = VendorSubscription::where('plan_id', $plan->id)->where('external_payment_received', true)->sum('external_payment_amount');
                $active = VendorSubscription::where('plan_id', $plan->id)->where('status', VendorSubscription::STATUS_ACTIVE)->count();
                return [
                    'plan' => $plan,
                    'active' => $active,
                    'total' => $plan->subscriptions_count,
                    'revenue_paise' => $paid,
                ];
            });

        // 3. Offline Subscription Revenue (monthly)
        $subRevenueTotal = (int) VendorSubscription::where('external_payment_received', true)
            ->whereBetween('external_payment_date', [$startDate, $endDate])
            ->sum('external_payment_amount');

        // 4. Real Order Commission Totals from immutable order_commissions ledger
        $commissionsQuery = OrderCommission::whereBetween('created_at', [$startDate, $endDate]);

        $commissionSummary = [
            'gross_paise' => (int) (clone $commissionsQuery)->where('event_type', 'charge')->sum('commission_base_paise'),
            'commission_paise' => (int) (clone $commissionsQuery)->sum('calculated_commission_paise'),
            'vendor_net_paise' => (int) (clone $commissionsQuery)->sum('vendor_net_paise'),
            'transactions_count' => (clone $commissionsQuery)->where('event_type', 'charge')->count(),
        ];

        $commissionBySector = OrderCommission::select(
            'sector_key',
            DB::raw("SUM(CASE WHEN event_type = 'charge' THEN 1 ELSE 0 END) as tx_count"),
            DB::raw("SUM(CASE WHEN event_type = 'charge' THEN commission_base_paise ELSE 0 END) as gross_paise"),
            DB::raw('SUM(calculated_commission_paise) as commission_paise'),
            DB::raw('SUM(vendor_net_paise) as vendor_net_paise')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('sector_key')
            ->get();

        // 5. Lead / Merchant Conversion Funnel
        $funnel = [
            'leads_total' => OnboardingLead::count(),
            'vendors_pending' => Vendor::whereIn('verification_status', ['pending_review', 'unverified'])->count(),
            'vendors_verified' => Vendor::where('verification_status', 'verified')->count(),
            'vendors_with_active_sub' => Vendor::whereHas('subscriptions', fn($q) => $q->where('status', VendorSubscription::STATUS_ACTIVE))->count(),
        ];

        return view('admin.vendor-management.reports', compact(
            'startDate',
            'endDate',
            'sectorBreakdown',
            'planBreakdown',
            'subRevenueTotal',
            'commissionSummary',
            'commissionBySector',
            'funnel'
        ));
    }

    public function exportReportsCsv(Request $request): StreamedResponse
    {
        $rows = VendorSubscription::with(['vendor', 'plan'])
            ->orderByDesc('id')
            ->get();

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['ID', 'Vendor', 'Sector', 'Plan', 'Version', 'Status', 'Starts At', 'Ends At', 'Payment Received', 'Amount (INR)', 'Payment Method', 'Reference']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->vendor?->shop_name ?? 'Vendor #' . $r->vendor_id,
                    $r->sector_key,
                    $r->plan_name_snapshot ?: ($r->plan?->name ?? ''),
                    'v' . $r->plan_version_number,
                    $r->status,
                    $r->starts_at?->toDateTimeString() ?? '',
                    $r->ends_at?->toDateTimeString() ?? '',
                    $r->external_payment_received ? 'Yes' : 'No',
                    number_format(($r->external_payment_amount ?? 0) / 100, 2, '.', ''),
                    $r->external_payment_method ?? '',
                    $r->external_payment_ref ?? '',
                ]);
            }
            fclose($out);
        }, 'k71-vendor-subscriptions-' . now()->format('Ymd-His') . '.csv');
    }
}
