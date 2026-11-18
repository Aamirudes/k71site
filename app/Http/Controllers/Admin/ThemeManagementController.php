<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorefrontTheme;
use App\Models\ThemeAuditLog;
use App\Models\Vendor;
use App\Models\VendorStorefrontConfig;
use App\Models\VendorThemeDraft;
use App\Models\VendorThemeEntitlement;
use App\Models\VendorThemeRequest;
use App\Services\Storefront\ThemeMarketplaceRegistry;
use App\Services\Storefront\ThemeMarketplaceService;
use Illuminate\Http\Request;

class ThemeManagementController extends Controller
{
    public function __construct(
        protected ThemeMarketplaceRegistry $registry,
        protected ThemeMarketplaceService $service
    ) {}

    /**
     * Tab 1: Theme Catalogue & Individual Pricing Management (All 61 themes).
     */
    public function index(Request $request)
    {
        $this->service->ensureSynced();

        $search = trim((string) $request->input('q', ''));
        $group = trim((string) $request->input('group', 'all')); // all, legacy, new
        $category = trim((string) $request->input('category', 'all'));

        $query = StorefrontTheme::query()
            ->withCount([
                'entitlements as granted_count' => fn ($q) => $q->where('status', VendorThemeEntitlement::STATUS_GRANTED),
                'requests as pending_requests_count' => fn ($q) => $q->whereIn('status', VendorThemeRequest::ACTIVE_PENDING_STATUSES),
            ]);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('theme_id', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($group === 'legacy') {
            $query->where('is_legacy', true);
        } elseif ($group === 'new') {
            $query->where('is_legacy', false);
        }

        if ($category !== '' && $category !== 'all') {
            $query->where('category', $category);
        }

        $themes = $query->orderBy('sort_order')->get();

        $liveCounts = VendorStorefrontConfig::query()
            ->selectRaw('COALESCE(published_theme_id, preset_key) as active_tid, COUNT(*) as cnt')
            ->groupBy('active_tid')
            ->pluck('cnt', 'active_tid')
            ->all();

        $stats = [
            'total_themes' => StorefrontTheme::count(),
            'legacy_themes' => StorefrontTheme::where('is_legacy', true)->count(),
            'new_themes' => StorefrontTheme::where('is_legacy', false)->count(),
            'pending_requests' => VendorThemeRequest::whereIn('status', VendorThemeRequest::ACTIVE_PENDING_STATUSES)->count(),
            'active_entitlements' => VendorThemeEntitlement::where('status', VendorThemeEntitlement::STATUS_GRANTED)->count(),
        ];

        return view('admin.themes.index', [
            'themes' => $themes,
            'liveCounts' => $liveCounts,
            'stats' => $stats,
            'categories' => ThemeMarketplaceRegistry::CATEGORY_LABELS,
            'manifests' => $this->registry->getAllThemes(),
            'search' => $search,
            'group' => $group,
            'category' => $category,
            'whatsappNumber' => $this->service->getAdminWhatsAppNumber(),
            'revocationPolicy' => $this->service->getRevocationPolicy(),
        ]);
    }

    /**
     * Update a theme's individual price, category, visibility, availability, or implementation status.
     */
    public function updateTheme(Request $request, StorefrontTheme $theme)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'category' => 'required|string|max:60',
            'description' => 'nullable|string|max:1000',
            'price_rupees' => 'nullable|numeric|min:0|max:9999999',
            'currency' => 'nullable|in:INR,USD',
            'compatible_categories' => 'nullable|string|max:300',
            'implementation_status' => 'required|in:ready,qa_pending,draft,deprecated',
            'is_free' => 'nullable|boolean',
            'qa_verified' => 'nullable|boolean',
            'is_visible_in_catalogue' => 'nullable|boolean',
            'is_available_for_request' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_preview_enabled' => 'nullable|boolean',
        ]);

        $this->service->updateThemeCatalogue($theme, [
            'name' => $validated['name'],
            'category' => $validated['category'],
            'description' => $validated['description'] ?? '',
            'price_rupees' => $request->input('price_rupees'),
            'currency' => $validated['currency'] ?? $theme->currency,
            'compatible_categories' => $validated['compatible_categories'] ?? '',
            'implementation_status' => $validated['implementation_status'],
            'is_free' => $request->boolean('is_free'),
            'qa_verified' => $request->boolean('qa_verified'),
            'is_visible_in_catalogue' => $request->boolean('is_visible_in_catalogue'),
            'is_available_for_request' => $request->boolean('is_available_for_request'),
            'is_featured' => $request->boolean('is_featured'),
            'is_preview_enabled' => $request->boolean('is_preview_enabled'),
        ], auth()->user());

        return back()->with('success', "Updated catalogue settings & pricing for \"{$theme->name}\".");
    }

    /**
     * Admin Theme QA Preview in a New Tab (strictly admin-protected, noindex, read-only).
     */
    public function previewTheme(Request $request, string $themeId)
    {
        $this->service->ensureSynced();
        $cleanId = strtolower(trim($themeId));
        $theme = StorefrontTheme::where('theme_id', $cleanId)->firstOrFail();
        $manifest = $this->registry->getManifest($cleanId);
        if (!$manifest) {
            abort(404);
        }

        $sampleVendor = Vendor::whereIn('status', ['approved', 'active'])->latest('id')->first()
            ?? Vendor::first()
            ?? new Vendor(['shop_name' => 'K71 Preview Store', 'slug' => 'preview', 'city' => 'Mumbai', 'description' => 'Admin Quality Control Preview Store']);

        return response()
            ->view('vendor.themes.preview', [
                'vendor' => $sampleVendor,
                'theme' => $theme,
                'manifest' => $manifest,
                'isEntitled' => true,
                'isLive' => false,
                'activeRequest' => null,
                'whatsappUrl' => null,
                'signedFrameBase' => route('admin.themes.preview_frame', $cleanId),
                'isAdminPreview' => true,
                'returnUrl' => route('admin.themes.index'),
                'initialPage' => in_array($request->input('page'), ['home', 'collection', 'product'], true) ? $request->input('page') : 'home',
            ])
            ->header('X-Robots-Tag', 'noindex, nofollow')
            ->header('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
    }

    public function previewThemeFrame(Request $request, string $themeId)
    {
        $cleanId = strtolower(trim($themeId));
        $manifest = $this->registry->getManifest($cleanId);
        if (!$manifest) {
            abort(404);
        }

        $sampleVendor = Vendor::whereIn('status', ['approved', 'active'])->latest('id')->first()
            ?? Vendor::first()
            ?? new Vendor(['id' => 0, 'shop_name' => 'K71 Preview Store', 'slug' => 'preview', 'city' => 'Mumbai']);

        return app(\App\Http\Controllers\Vendor\ThemeMarketplaceController::class)
            ->renderIsolatedPreviewFrame($request, $sampleVendor, $manifest, route('admin.themes.preview_frame', $cleanId), false);
    }

    /**
     * Update global Theme Marketplace settings (Official Admin WhatsApp number & revocation policy).
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'whatsapp_number' => 'nullable|string|max:30',
            'revocation_policy' => 'required|in:fallback_to_velora,keep_live_flagged',
        ]);

        $this->service->updateMarketplaceSettings($validated, auth()->user());

        return back()->with('success', 'Theme Marketplace settings updated.');
    }

    /**
     * Tab 2: Vendor Theme Requests & Manual Payment Verification Queue.
     */
    public function requests(Request $request)
    {
        $this->service->ensureSynced();
        $statusFilter = trim((string) $request->input('status', 'all'));

        $query = VendorThemeRequest::with(['vendor', 'theme', 'paymentVerifier', 'reviewer'])->latest('id');
        if ($statusFilter === 'pending') {
            $query->whereIn('status', VendorThemeRequest::ACTIVE_PENDING_STATUSES);
        } elseif ($statusFilter !== '' && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $themeRequests = $query->paginate(25)->withQueryString();
        $entitlementsMap = VendorThemeEntitlement::all()
            ->keyBy(fn ($e) => $e->vendor_id . ':' . $e->theme_id);

        return view('admin.themes.requests', [
            'themeRequests' => $themeRequests,
            'entitlementsMap' => $entitlementsMap,
            'statusFilter' => $statusFilter,
            'service' => $this->service,
        ]);
    }

    /**
     * Move request to review / awaiting payment confirmation.
     */
    public function updateRequestStatus(Request $request, VendorThemeRequest $themeRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending_review,payment_awaiting_confirmation',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->transitionRequestStatus($themeRequest, $validated['status'], auth()->user(), $validated['admin_notes'] ?? null);
            return back()->with('success', "Request {$themeRequest->reference_code} updated to {$validated['status']}.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Step 1: Manually confirm external WhatsApp-coordinated payment (does NOT grant entitlement automatically).
     */
    public function confirmPayment(Request $request, VendorThemeRequest $themeRequest)
    {
        $validated = $request->validate([
            'payment_reference' => 'nullable|string|max:120',
            'payment_notes' => 'nullable|string|max:1000',
            'acknowledge_price_change' => 'nullable|boolean',
        ]);

        try {
            $this->service->recordPaymentConfirmation(
                $themeRequest,
                auth()->user(),
                $validated['payment_reference'] ?? null,
                $validated['payment_notes'] ?? null,
                $request->boolean('acknowledge_price_change')
            );
            return back()->with('success', "Payment manually confirmed for {$themeRequest->reference_code}. You can now explicitly grant theme access.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Step 2: Explicitly grant entitlement from a payment-confirmed request.
     */
    public function grantFromRequest(Request $request, VendorThemeRequest $themeRequest)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $vendor = Vendor::findOrFail($themeRequest->vendor_id);
            $this->service->grantEntitlement($vendor, $themeRequest->theme_id, auth()->user(), $themeRequest, $validated['notes'] ?? null);
            return back()->with('success', "Access granted to {$vendor->shop_name} for theme \"{$themeRequest->theme_id}\". Vendor may now customize and explicitly publish it.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Decline a vendor theme request (never grants entitlement).
     */
    public function declineRequest(Request $request, VendorThemeRequest $themeRequest)
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $this->service->declineRequest($themeRequest, auth()->user(), $validated['reason'] ?? null);
            return back()->with('success', "Request {$themeRequest->reference_code} declined.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tab 3: Vendor Entitlements, Live/Draft Inspection & Direct Grant / Revocation.
     */
    public function entitlements(Request $request)
    {
        $this->service->ensureSynced();

        $entitlements = VendorThemeEntitlement::with(['vendor.storefrontConfig', 'theme', 'grantor', 'revoker', 'request'])
            ->latest('updated_at')
            ->paginate(30);

        $vendors = Vendor::with('storefrontConfig')->orderBy('shop_name')->get(['id', 'shop_name', 'slug', 'layout_preset']);
        $paidThemes = StorefrontTheme::where('is_free', false)->orderBy('sort_order')->get(['theme_id', 'name', 'price_paise', 'implementation_status']);

        $draftsByVendor = VendorThemeDraft::select('vendor_id', 'theme_id', 'updated_at')
            ->get()
            ->groupBy('vendor_id');

        return view('admin.themes.entitlements', [
            'entitlements' => $entitlements,
            'vendors' => $vendors,
            'paidThemes' => $paidThemes,
            'draftsByVendor' => $draftsByVendor,
            'revocationPolicy' => $this->service->getRevocationPolicy(),
        ]);
    }

    /**
     * Direct Admin Grant (e.g. complimentary/partner grant with mandatory audit reason).
     */
    public function directGrant(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => 'required|integer|exists:vendors,id',
            'theme_id' => 'required|string|max:80',
            'notes' => 'required|string|min:4|max:1000',
        ]);

        try {
            $vendor = Vendor::findOrFail($validated['vendor_id']);
            $this->service->grantEntitlement($vendor, $validated['theme_id'], auth()->user(), null, $validated['notes']);
            return back()->with('success', "Directly granted access for \"{$validated['theme_id']}\" to {$vendor->shop_name}.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Revoke Vendor Entitlement (preserves vendor drafts/products, applies revocation policy).
     */
    public function revokeEntitlement(Request $request, VendorThemeEntitlement $entitlement)
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:3|max:1000',
        ]);

        try {
            $this->service->revokeEntitlement($entitlement, auth()->user(), $validated['reason']);
            return back()->with('success', "Revoked access for theme \"{$entitlement->theme_id}\". Vendor drafts and products were preserved.");
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Tab 4: Theme Audit History (immutable log without secrets).
     */
    public function auditLogs(Request $request)
    {
        $logs = ThemeAuditLog::with(['actor', 'vendor', 'theme'])
            ->latest('id')
            ->paginate(40);

        return view('admin.themes.audit', [
            'logs' => $logs,
        ]);
    }
}
