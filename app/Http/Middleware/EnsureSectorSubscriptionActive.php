<?php

namespace App\Http\Middleware;

use App\Models\Product;
use App\Models\SubscriptionRenewalRequest;
use App\Services\Monetization\EntitlementService;
use App\Services\Monetization\SectorRegistryService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSectorSubscriptionActive
{
    public function __construct(
        protected SectorRegistryService $sectors,
        protected EntitlementService $entitlements
    ) {}

    public function handle(Request $request, Closure $next, string $sectorMode = 'primary'): Response
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            return $next($request);
        }

        if (!$this->entitlements->isStrictEnforcementEnabled()) {
            return $next($request);
        }

        $sectorKey = $this->resolveTargetSector($request, $vendor, $sectorMode);
        $lockState = $this->entitlements->resolveSectorLockState($vendor, $sectorKey);

        if (!$lockState['locked']) {
            $request->attributes->set('current_sector_key', $sectorKey);
            $request->attributes->set('current_subscription', $lockState['effective_sub']);
            return $next($request);
        }

        $reason = $lockState['reason'] ?? "Access to {$lockState['sector_label']} is currently paused pending subscription renewal.";

        if ($request->expectsJson() || $request->ajax() || str_contains($request->path(), 'ajax')) {
            return response()->json([
                'success' => false,
                'locked' => true,
                'state' => $lockState['state'],
                'sector_key' => $sectorKey,
                'message' => $reason,
            ], 403);
        }

        if (!$request->isMethod('GET') && !$request->isMethod('HEAD')) {
            return redirect()->route('vendor.dashboard', ['sector' => $sectorKey])
                ->with('error', $reason);
        }

        $renewalRequests = SubscriptionRenewalRequest::with(['requestedPlan', 'subscription'])
            ->where('vendor_id', $vendor->id)
            ->orderByDesc('id')
            ->take(10)
            ->get();

        return response()->view('vendor.partials.expired-sector-lock', [
            'vendor' => $vendor,
            'lockState' => $lockState,
            'renewalRequests' => $renewalRequests,
        ], 200);
    }

    protected function resolveTargetSector(Request $request, $vendor, string $sectorMode): string
    {
        if (in_array($sectorMode, ['products', 'food', 'services', 'used', 'enquiry'], true)) {
            return $sectorMode;
        }

        if ($sectorMode === 'enquiry_or_primary') {
            $hasEnquiryScope = (is_array($vendor->operating_sectors) && in_array('enquiry', $vendor->operating_sectors, true))
                || \App\Models\VendorSubscription::where('vendor_id', $vendor->id)
                    ->where('sector_key', 'enquiry')
                    ->exists();
            if ($hasEnquiryScope) {
                return 'enquiry';
            }

            return $this->sectors->resolveVendorPrimarySector($vendor);
        }

        if ($sectorMode === 'product_item') {
            $routeProduct = $request->route('product');
            if ($routeProduct instanceof Product) {
                return $this->sectors->resolveProductSector($routeProduct);
            }
            if ($request->filled('vertical')) {
                return $this->sectors->normalizeSectorKey((string) $request->input('vertical'));
            }
            if ($request->filled('sector')) {
                return $this->sectors->normalizeSectorKey((string) $request->input('sector'));
            }
        }

        if ($request->filled('sector')) {
            return $this->sectors->normalizeSectorKey((string) $request->input('sector'));
        }

        return $this->sectors->resolveVendorPrimarySector($vendor);
    }
}

