<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionNotification;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionRenewalRequest;
use App\Models\VendorSubscription;
use App\Services\Monetization\CommissionService;
use App\Services\Monetization\EntitlementService;
use App\Services\Monetization\SectorRegistryService;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SectorRegistryService $sectors,
        protected EntitlementService $entitlements,
        protected CommissionService $commissions
    ) {}

    /**
     * Read-only Vendor Subscription, Entitlements & Commission Portal.
     * STRICT RULE: No self-checkout, no payment gateway, no self-activation, NO internal admin notes exposed!
     */
    public function index(Request $request)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            return redirect()->route('vendor.register');
        }

        $this->entitlements->syncJustInTimeExpiry($vendor);

        $subscriptions = VendorSubscription::with(['plan', 'planVersion', 'commissionRule'])
            ->where('vendor_id', $vendor->id)
            ->orderByDesc('id')
            ->get();

        $vendorSectors = $this->sectors->resolveVendorSectors($vendor);
        $sectorCards = [];

        foreach ($vendorSectors as $sectorKey) {
            $activeSub = $this->entitlements->resolveEffectiveSubscription($vendor, $sectorKey);
            $latestSub = $activeSub ?: $subscriptions->first(fn($s) => $s->coversSector($sectorKey));
            $commission = $this->commissions->resolveEffectiveRule($vendor, $sectorKey, null, $activeSub);
            $usage = $this->entitlements->getUsageSummaryForVendor($vendor, $sectorKey);

            $sectorCards[$sectorKey] = [
                'sector_key' => $sectorKey,
                'sector_label' => ucfirst($sectorKey),
                'active_sub' => $activeSub,
                'latest_sub' => $latestSub,
                'commission' => $commission,
                'usage' => $usage,
            ];
        }

        $notifications = SubscriptionNotification::where('vendor_id', $vendor->id)
            ->orderByDesc('id')
            ->take(15)
            ->get();

        // Mark visible notifications as read
        SubscriptionNotification::where('vendor_id', $vendor->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $publicPlans = SubscriptionPlan::where('status', 'active')
            ->where('is_public_display', true)
            ->orderBy('sort_order')
            ->get();

        $renewalRequests = SubscriptionRenewalRequest::with(['requestedPlan'])
            ->where('vendor_id', $vendor->id)
            ->latest('id')
            ->take(10)
            ->get();

        return view('vendor.subscription.index', compact(
            'vendor',
            'subscriptions',
            'sectorCards',
            'notifications',
            'publicPlans',
            'renewalRequests'
        ));
    }

    /**
     * Submit an offline renewal / upgrade request to Admin (requires WhatsApp/manual fulfillment).
     */
    public function requestRenewal(Request $request)
    {
        $vendor = auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        $validated = $request->validate([
            'sector_key' => 'required|string|in:products,food,services,used,enquiry,all',
            'requested_plan_id' => 'nullable|integer|exists:subscription_plans,id',
            'message' => 'nullable|string|max:1500',
        ]);

        if (!empty($validated['requested_plan_id'])) {
            $plan = SubscriptionPlan::where('id', (int) $validated['requested_plan_id'])
                ->where('status', 'active')
                ->first();
            if (!$plan) {
                return back()->with('error', 'The selected subscription plan is not currently active.');
            }
        }

        $existingPending = SubscriptionRenewalRequest::where('vendor_id', $vendor->id)
            ->where('sector_key', $validated['sector_key'])
            ->where('status', 'pending')
            ->exists();
        if ($existingPending) {
            return back()->with('info', 'You already have a pending renewal request for this sector. Our admin team will follow up via WhatsApp.');
        }

        $currentSub = VendorSubscription::where('vendor_id', $vendor->id)
            ->where('sector_key', $validated['sector_key'])
            ->orderByDesc('id')
            ->first();

        $renewalReq = SubscriptionRenewalRequest::create([
            'vendor_id' => $vendor->id,
            'vendor_subscription_id' => $currentSub?->id,
            'requested_plan_id' => $validated['requested_plan_id'] ?? null,
            'sector_key' => $validated['sector_key'],
            'message' => trim((string) ($validated['message'] ?? '')) ?: 'Vendor requested renewal/plan assistance via vendor portal.',
            'status' => 'pending',
        ]);

        app(\App\Services\NotificationCenterService::class)->notifyAdmins(
            '📋',
            'New Vendor Renewal Request',
            "{$vendor->shop_name} requested subscription renewal/upgrade for {$validated['sector_key']}.",
            route('admin.vendor_management.index', ['tab' => 'renewals']),
            'subscriptions'
        );

        return back()->with('success', 'Your renewal / plan change request has been sent to the K71 administrator. Please coordinate via WhatsApp for confirmation.');
    }
}
