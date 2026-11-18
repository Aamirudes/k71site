<?php

namespace App\Services\Storefront;

use App\Models\AuditLog;
use App\Models\Setting;
use App\Models\StorefrontTheme;
use App\Models\ThemeAuditLog;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorStorefrontConfig;
use App\Models\VendorStorefrontRevision;
use App\Models\VendorThemeDraft;
use App\Models\VendorThemeEntitlement;
use App\Models\VendorThemeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ThemeMarketplaceService
{
    public const SETTING_WHATSAPP_NUMBER = 'theme_marketplace_whatsapp_number';
    public const SETTING_REVOCATION_POLICY = 'theme_marketplace_revocation_policy';

    /**
     * Strict server-side legal state transitions for theme requests.
     */
    public const ALLOWED_TRANSITIONS = [
        VendorThemeRequest::STATUS_REQUESTED => [
            VendorThemeRequest::STATUS_PENDING_REVIEW,
            VendorThemeRequest::STATUS_PAYMENT_AWAITING,
            VendorThemeRequest::STATUS_PAYMENT_CONFIRMED,
            VendorThemeRequest::STATUS_DECLINED,
            VendorThemeRequest::STATUS_CANCELLED,
        ],
        VendorThemeRequest::STATUS_PENDING_REVIEW => [
            VendorThemeRequest::STATUS_PAYMENT_AWAITING,
            VendorThemeRequest::STATUS_PAYMENT_CONFIRMED,
            VendorThemeRequest::STATUS_DECLINED,
            VendorThemeRequest::STATUS_CANCELLED,
        ],
        VendorThemeRequest::STATUS_PAYMENT_AWAITING => [
            VendorThemeRequest::STATUS_PAYMENT_CONFIRMED,
            VendorThemeRequest::STATUS_DECLINED,
            VendorThemeRequest::STATUS_CANCELLED,
        ],
        VendorThemeRequest::STATUS_PAYMENT_CONFIRMED => [
            VendorThemeRequest::STATUS_ACCESS_GRANTED,
            VendorThemeRequest::STATUS_DECLINED,
        ],
        VendorThemeRequest::STATUS_ACCESS_GRANTED => [
            VendorThemeRequest::STATUS_ACCESS_REVOKED,
        ],
        VendorThemeRequest::STATUS_DECLINED => [],
        VendorThemeRequest::STATUS_CANCELLED => [],
        VendorThemeRequest::STATUS_ACCESS_REVOKED => [],
    ];

    public function __construct(
        protected ThemeMarketplaceRegistry $registry
    ) {}

    /**
     * Idempotently sync all 61 themes into storefront_themes table.
     * NEVER overwrites admin-configured prices, visibility, availability, or overrides on existing rows.
     */
    public function syncThemesToDatabase(): void
    {
        $all = $this->registry->getAllThemes();
        $existingIds = StorefrontTheme::pluck('theme_id')->flip()->all();

        $sort = 1;
        foreach ($all as $id => $def) {
            if (!isset($existingIds[$id])) {
                StorefrontTheme::create([
                    'theme_id' => $id,
                    'slug' => $def['slug'],
                    'name' => $def['name'],
                    'category' => $def['category'],
                    'tagline' => $def['tagline'] ?? '',
                    'description' => $def['description'] ?? '',
                    'version' => $def['version'] ?? '1.0.0',
                    'is_legacy' => !empty($def['is_legacy']),
                    'is_free' => !empty($def['is_free']),
                    'implementation_status' => 'ready',
                    'is_visible_in_catalogue' => true,
                    'is_available_for_request' => empty($def['is_free']),
                    'price_paise' => null, // Admin configures individual price in Admin Theme Center
                    'currency' => 'INR',
                    'sort_order' => $sort,
                ]);
            } else {
                // Only update code-controlled version if changed, preserving all admin settings
                StorefrontTheme::where('theme_id', $id)->update([
                    'version' => $def['version'] ?? '1.0.0',
                    'is_legacy' => !empty($def['is_legacy']),
                ]);
            }
            $sort++;
        }
    }

    /**
     * Ensure catalogue is populated on first load.
     */
    public function ensureSynced(): void
    {
        if (StorefrontTheme::count() < 61) {
            $this->syncThemesToDatabase();
        }
    }

    /**
     * Official admin-configured WhatsApp number (never hardcoded in source code).
     */
    public function getAdminWhatsAppNumber(): ?string
    {
        $configured = trim((string) Setting::get(self::SETTING_WHATSAPP_NUMBER, ''));
        if ($configured !== '') {
            return $configured;
        }
        return null;
    }

    public function getRevocationPolicy(): string
    {
        $policy = (string) Setting::get(self::SETTING_REVOCATION_POLICY, 'fallback_to_velora');
        return in_array($policy, ['fallback_to_velora', 'keep_live_flagged'], true) ? $policy : 'fallback_to_velora';
    }

    /**
     * Authoritative server-side entitlement check for a vendor + theme.
     */
    public function isVendorEntitled(Vendor $vendor, string $themeId): bool
    {
        $cleanId = strtolower(trim($themeId));
        if (!$this->registry->isValidThemeId($cleanId)) {
            return false;
        }

        // Original 10 standard legacy themes remain free/included for backward compatibility
        if ($this->registry->isLegacyTheme($cleanId)) {
            if ($cleanId === 'kinetic') {
                if ($vendor->hasKineticScrollAccess()) {
                    return true;
                }
                return VendorThemeEntitlement::where('vendor_id', $vendor->id)
                    ->where('theme_id', 'kinetic')
                    ->where('status', VendorThemeEntitlement::STATUS_GRANTED)
                    ->exists();
            }
            return true;
        }

        // Check DB catalogue record (if admin explicitly marked a theme free & ready)
        $dbTheme = StorefrontTheme::where('theme_id', $cleanId)->first();
        if ($dbTheme && $dbTheme->is_free && $dbTheme->isReady()) {
            return true;
        }

        // Paid new themes strictly require an explicit active 'granted' entitlement record
        return VendorThemeEntitlement::where('vendor_id', $vendor->id)
            ->where('theme_id', $cleanId)
            ->where('status', VendorThemeEntitlement::STATUS_GRANTED)
            ->exists();
    }

    /**
     * Resolve the active published theme ID for a public storefront safely.
     * Enforces revocation policy without mutating vendor drafts or products.
     */
    public function resolvePublicThemeId(Vendor $vendor): string
    {
        $configRecord = $vendor->relationLoaded('storefrontConfig')
            ? $vendor->storefrontConfig
            : $vendor->storefrontConfig()->first();

        $candidate = $configRecord?->published_theme_id
            ?: ($configRecord?->published_config['theme_id'] ?? null)
            ?: ($configRecord?->published_config['preset'] ?? null)
            ?: ($configRecord?->preset_key ?? null)
            ?: ($vendor->layout_preset ?: 'velora');

        $clean = strtolower(trim((string) $candidate));

        // Legacy alias compatibility
        if (!$this->registry->isValidThemeId($clean)) {
            $clean = app(StorefrontPresetService::class)->normalizePresetKeyForVendor($clean, $vendor);
        }

        if ($this->registry->isNewTheme($clean)) {
            $dbTheme = StorefrontTheme::where('theme_id', $clean)->first();
            if ($dbTheme && !$dbTheme->isReady()) {
                return 'velora';
            }

            if (!$this->isVendorEntitled($vendor, $clean)) {
                if ($this->getRevocationPolicy() === 'keep_live_flagged') {
                    return $clean;
                }
                return 'velora';
            }

            return $clean;
        }

        return app(StorefrontPresetService::class)->normalizePresetKeyForVendor($clean, $vendor);
    }

    /**
     * Build URL-encoded WhatsApp coordination link for a theme request.
     * Never fabricates a phone number; returns null if admin has not configured one.
     */
    public function buildWhatsAppRequestUrl(VendorThemeRequest $request): ?string
    {
        $rawNumber = $this->getAdminWhatsAppNumber();
        if (!$rawNumber) {
            return null;
        }

        $digits = preg_replace('/[^0-9]/', '', $rawNumber);
        if (strlen($digits) < 8) {
            return null;
        }

        $themeName = $request->theme?->name ?? $request->theme_id;
        $shopName = $request->vendor?->shop_name ?? ('Vendor #' . $request->vendor_id);
        $priceText = $request->formatted_price_snapshot;

        $msg = "Hello K71 Admin, I would like to coordinate payment for the storefront theme \"{$themeName}\" ({$priceText}).\n"
            . "Store: {$shopName}\n"
            . "Request Ref: {$request->reference_code}\n"
            . "Please share payment details so you can verify and grant theme access.";

        return 'https://wa.me/' . $digits . '?text=' . rawurlencode($msg);
    }

    /**
     * Vendor creates a persistent access request for a paid theme with a price snapshot.
     * NEVER confirms payment or grants access.
     */
    public function createRequest(Vendor $vendor, string $themeId, ?string $vendorNote = null): VendorThemeRequest
    {
        $this->ensureSynced();
        $cleanId = strtolower(trim($themeId));

        $theme = StorefrontTheme::where('theme_id', $cleanId)->first();
        if (!$theme || !$this->registry->isValidThemeId($cleanId)) {
            throw new \InvalidArgumentException('Selected theme does not exist.');
        }

        if (!$theme->isReady()) {
            throw new \DomainException('This theme is not yet ready for requests.');
        }

        if (!$theme->is_visible_in_catalogue || !$theme->is_available_for_request) {
            throw new \DomainException('This theme is currently unavailable for new requests.');
        }

        if ($this->isVendorEntitled($vendor, $cleanId)) {
            throw new \DomainException('Your store already has access to this theme.');
        }

        $existingPending = VendorThemeRequest::where('vendor_id', $vendor->id)
            ->where('theme_id', $cleanId)
            ->whereIn('status', VendorThemeRequest::ACTIVE_PENDING_STATUSES)
            ->first();

        if ($existingPending) {
            throw new \DomainException("You already have an active request ({$existingPending->reference_code}) for this theme.");
        }

        return DB::transaction(function () use ($vendor, $theme, $vendorNote) {
            $ref = 'THM-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
            $history = [[
                'status' => VendorThemeRequest::STATUS_REQUESTED,
                'payment_status' => VendorThemeRequest::PAYMENT_UNVERIFIED,
                'at' => now()->toIso8601String(),
                'actor' => 'vendor',
                'note' => 'Theme access requested via dashboard.',
            ]];

            $req = VendorThemeRequest::create([
                'reference_code' => $ref,
                'vendor_id' => $vendor->id,
                'theme_id' => $theme->theme_id,
                'price_snapshot_paise' => $theme->price_paise,
                'currency' => $theme->currency ?: 'INR',
                'status' => VendorThemeRequest::STATUS_REQUESTED,
                'payment_verification_status' => VendorThemeRequest::PAYMENT_UNVERIFIED,
                'vendor_note' => $vendorNote ? mb_substr(trim(strip_tags($vendorNote)), 0, 500) : null,
                'status_history' => $history,
            ]);

            $this->recordAudit(
                action: 'theme.request.created',
                actor: auth()->user(),
                actorRole: 'vendor',
                vendorId: $vendor->id,
                themeId: $theme->theme_id,
                requestId: $req->id,
                afterState: [
                    'reference_code' => $req->reference_code,
                    'status' => $req->status,
                    'price_snapshot_paise' => $req->price_snapshot_paise,
                ],
                notes: 'Vendor requested paid theme access (payment unverified).'
            );

            return $req;
        });
    }

    /**
     * Vendor cancels their own pending request (only before access is granted).
     */
    public function cancelVendorRequest(Vendor $vendor, VendorThemeRequest $req): VendorThemeRequest
    {
        if ((int) $req->vendor_id !== (int) $vendor->id) {
            throw new \DomainException('Unauthorized request access.');
        }

        if (!in_array($req->status, [
            VendorThemeRequest::STATUS_REQUESTED,
            VendorThemeRequest::STATUS_PENDING_REVIEW,
            VendorThemeRequest::STATUS_PAYMENT_AWAITING,
        ], true)) {
            throw new \DomainException('This request can no longer be cancelled.');
        }

        return DB::transaction(function () use ($req, $vendor) {
            $before = ['status' => $req->status];
            $history = is_array($req->status_history) ? $req->status_history : [];
            $history[] = [
                'status' => VendorThemeRequest::STATUS_CANCELLED,
                'payment_status' => $req->payment_verification_status,
                'at' => now()->toIso8601String(),
                'actor' => 'vendor',
                'note' => 'Cancelled by vendor.',
            ];

            $req->update([
                'status' => VendorThemeRequest::STATUS_CANCELLED,
                'status_history' => $history,
            ]);

            $this->recordAudit(
                action: 'theme.request.cancelled',
                actor: auth()->user(),
                actorRole: 'vendor',
                vendorId: $vendor->id,
                themeId: $req->theme_id,
                requestId: $req->id,
                beforeState: $before,
                afterState: ['status' => $req->status],
                notes: 'Vendor cancelled pending theme request.'
            );

            return $req->fresh();
        });
    }

    /**
     * Admin transitions request status (e.g. pending_review, payment_awaiting_confirmation).
     */
    public function transitionRequestStatus(VendorThemeRequest $req, string $targetStatus, User $admin, ?string $notes = null): VendorThemeRequest
    {
        $this->assertLegalTransition($req->status, $targetStatus);

        if ($targetStatus === VendorThemeRequest::STATUS_PAYMENT_CONFIRMED) {
            return $this->recordPaymentConfirmation($req, $admin, null, $notes);
        }
        if ($targetStatus === VendorThemeRequest::STATUS_ACCESS_GRANTED) {
            throw new \DomainException('Use the explicit Grant Entitlement action to authorize access.');
        }

        return DB::transaction(function () use ($req, $targetStatus, $admin, $notes) {
            $locked = VendorThemeRequest::lockForUpdate()->findOrFail($req->id);
            $this->assertLegalTransition($locked->status, $targetStatus);

            $before = [
                'status' => $locked->status,
                'payment_verification_status' => $locked->payment_verification_status,
            ];

            $paymentStatus = $locked->payment_verification_status;
            if ($targetStatus === VendorThemeRequest::STATUS_PAYMENT_AWAITING && $paymentStatus === VendorThemeRequest::PAYMENT_UNVERIFIED) {
                $paymentStatus = VendorThemeRequest::PAYMENT_AWAITING;
            }

            $history = is_array($locked->status_history) ? $locked->status_history : [];
            $history[] = [
                'status' => $targetStatus,
                'payment_status' => $paymentStatus,
                'at' => now()->toIso8601String(),
                'actor' => 'admin:' . $admin->id,
                'note' => $notes ?: "Moved to {$targetStatus}.",
            ];

            $locked->update([
                'status' => $targetStatus,
                'payment_verification_status' => $paymentStatus,
                'admin_notes' => $notes ? mb_substr(trim(strip_tags($notes)), 0, 1000) : $locked->admin_notes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'status_history' => $history,
            ]);

            $this->recordAudit(
                action: 'theme.request.status_updated',
                actor: $admin,
                actorRole: 'admin',
                vendorId: $locked->vendor_id,
                themeId: $locked->theme_id,
                requestId: $locked->id,
                beforeState: $before,
                afterState: [
                    'status' => $locked->status,
                    'payment_verification_status' => $locked->payment_verification_status,
                ],
                notes: $notes
            );

            return $locked->fresh();
        });
    }

    /**
     * Admin manually confirms external WhatsApp-coordinated payment.
     * SEPARATE from granting entitlement.
     */
    public function recordPaymentConfirmation(
        VendorThemeRequest $req,
        User $admin,
        ?string $paymentReference = null,
        ?string $paymentNotes = null,
        bool $acknowledgePriceChange = false
    ): VendorThemeRequest {
        return DB::transaction(function () use ($req, $admin, $paymentReference, $paymentNotes, $acknowledgePriceChange) {
            $locked = VendorThemeRequest::with('theme')->lockForUpdate()->findOrFail($req->id);

            if ($locked->isPaymentConfirmed()) {
                throw new \DomainException('Payment is already confirmed for this request.');
            }

            $this->assertLegalTransition($locked->status, VendorThemeRequest::STATUS_PAYMENT_CONFIRMED);

            if ($locked->hasPriceChangedSinceRequest() && !$acknowledgePriceChange) {
                throw new \DomainException('Theme price changed since this request was created. Please review both prices and confirm acknowledgement before verifying payment.');
            }

            $before = [
                'status' => $locked->status,
                'payment_verification_status' => $locked->payment_verification_status,
            ];

            $cleanRef = $paymentReference ? mb_substr(trim(strip_tags($paymentReference)), 0, 120) : null;
            $cleanNotes = $paymentNotes ? mb_substr(trim(strip_tags($paymentNotes)), 0, 1000) : null;

            $history = is_array($locked->status_history) ? $locked->status_history : [];
            $history[] = [
                'status' => VendorThemeRequest::STATUS_PAYMENT_CONFIRMED,
                'payment_status' => VendorThemeRequest::PAYMENT_CONFIRMED,
                'at' => now()->toIso8601String(),
                'actor' => 'admin:' . $admin->id,
                'note' => 'Manual payment verified by admin' . ($cleanRef ? " (Ref: {$cleanRef})" : '') . '.',
            ];

            $locked->update([
                'status' => VendorThemeRequest::STATUS_PAYMENT_CONFIRMED,
                'payment_verification_status' => VendorThemeRequest::PAYMENT_CONFIRMED,
                'payment_verified_by' => $admin->id,
                'payment_verified_at' => now(),
                'payment_reference' => $cleanRef,
                'payment_notes' => $cleanNotes,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'status_history' => $history,
            ]);

            $this->recordAudit(
                action: 'theme.payment.confirmed',
                actor: $admin,
                actorRole: 'admin',
                vendorId: $locked->vendor_id,
                themeId: $locked->theme_id,
                requestId: $locked->id,
                beforeState: $before,
                afterState: [
                    'status' => $locked->status,
                    'payment_verification_status' => $locked->payment_verification_status,
                    'payment_reference' => $cleanRef,
                    'price_snapshot_paise' => $locked->price_snapshot_paise,
                ],
                notes: $cleanNotes
            );

            return $locked->fresh();
        });
    }

    /**
     * Admin explicitly grants entitlement for a vendor + theme.
     * NEVER silently publishes the vendor's storefront.
     */
    public function grantEntitlement(
        Vendor $vendor,
        string $themeId,
        User $admin,
        ?VendorThemeRequest $request = null,
        ?string $notes = null
    ): VendorThemeEntitlement {
        $cleanId = strtolower(trim($themeId));
        if (!$this->registry->isValidThemeId($cleanId)) {
            throw new \InvalidArgumentException('Invalid theme ID.');
        }

        return DB::transaction(function () use ($vendor, $cleanId, $admin, $request, $notes) {
            $lockedRequest = null;
            if ($request) {
                $lockedRequest = VendorThemeRequest::lockForUpdate()->findOrFail($request->id);
                if ((int) $lockedRequest->vendor_id !== (int) $vendor->id || $lockedRequest->theme_id !== $cleanId) {
                    throw new \DomainException('Request does not match the specified vendor and theme.');
                }
                if (!$lockedRequest->isPaymentConfirmed()) {
                    throw new \DomainException('Payment must be manually verified before granting access for this request.');
                }
                if ($lockedRequest->status === VendorThemeRequest::STATUS_DECLINED || $lockedRequest->status === VendorThemeRequest::STATUS_CANCELLED) {
                    throw new \DomainException('Cannot grant entitlement from a declined or cancelled request.');
                }
                if ($lockedRequest->status === VendorThemeRequest::STATUS_ACCESS_GRANTED) {
                    throw new \DomainException('Access has already been granted for this request.');
                }
            }

            $existing = VendorThemeEntitlement::lockForUpdate()
                ->where('vendor_id', $vendor->id)
                ->where('theme_id', $cleanId)
                ->first();

            if ($existing && $existing->isGranted()) {
                throw new \DomainException('This vendor already holds an active entitlement for this theme.');
            }

            $before = $existing ? ['status' => $existing->status] : null;
            $cleanNotes = $notes ? mb_substr(trim(strip_tags($notes)), 0, 1000) : null;

            $entitlement = VendorThemeEntitlement::updateOrCreate(
                ['vendor_id' => $vendor->id, 'theme_id' => $cleanId],
                [
                    'status' => VendorThemeEntitlement::STATUS_GRANTED,
                    'request_id' => $lockedRequest?->id,
                    'granted_by' => $admin->id,
                    'granted_at' => now(),
                    'revoked_by' => null,
                    'revoked_at' => null,
                    'revocation_reason' => null,
                    'notes' => $cleanNotes,
                ]
            );

            if ($lockedRequest) {
                $history = is_array($lockedRequest->status_history) ? $lockedRequest->status_history : [];
                $history[] = [
                    'status' => VendorThemeRequest::STATUS_ACCESS_GRANTED,
                    'payment_status' => $lockedRequest->payment_verification_status,
                    'at' => now()->toIso8601String(),
                    'actor' => 'admin:' . $admin->id,
                    'note' => 'Entitlement granted by administrator.',
                ];
                $lockedRequest->update([
                    'status' => VendorThemeRequest::STATUS_ACCESS_GRANTED,
                    'reviewed_by' => $admin->id,
                    'reviewed_at' => now(),
                    'status_history' => $history,
                ]);
            }

            $this->recordAudit(
                action: 'theme.entitlement.granted',
                actor: $admin,
                actorRole: 'admin',
                vendorId: $vendor->id,
                themeId: $cleanId,
                requestId: $lockedRequest?->id,
                entitlementId: $entitlement->id,
                beforeState: $before,
                afterState: ['status' => VendorThemeEntitlement::STATUS_GRANTED],
                notes: $cleanNotes ?: 'Admin granted vendor theme entitlement.'
            );

            return $entitlement->fresh();
        });
    }

    /**
     * Admin declines a theme request. Declined requests NEVER create entitlement.
     */
    public function declineRequest(VendorThemeRequest $req, User $admin, ?string $reason = null): VendorThemeRequest
    {
        return DB::transaction(function () use ($req, $admin, $reason) {
            $locked = VendorThemeRequest::lockForUpdate()->findOrFail($req->id);
            $this->assertLegalTransition($locked->status, VendorThemeRequest::STATUS_DECLINED);

            $before = ['status' => $locked->status];
            $cleanReason = $reason ? mb_substr(trim(strip_tags($reason)), 0, 1000) : 'Declined by administrator.';
            $history = is_array($locked->status_history) ? $locked->status_history : [];
            $history[] = [
                'status' => VendorThemeRequest::STATUS_DECLINED,
                'payment_status' => $locked->payment_verification_status,
                'at' => now()->toIso8601String(),
                'actor' => 'admin:' . $admin->id,
                'note' => $cleanReason,
            ];

            $locked->update([
                'status' => VendorThemeRequest::STATUS_DECLINED,
                'admin_notes' => $cleanReason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'status_history' => $history,
            ]);

            $this->recordAudit(
                action: 'theme.request.declined',
                actor: $admin,
                actorRole: 'admin',
                vendorId: $locked->vendor_id,
                themeId: $locked->theme_id,
                requestId: $locked->id,
                beforeState: $before,
                afterState: ['status' => VendorThemeRequest::STATUS_DECLINED],
                notes: $cleanReason
            );

            return $locked->fresh();
        });
    }

    /**
     * Admin revokes a vendor's entitlement.
     * NEVER deletes drafts, published config, or products.
     * Public rendering respects the configured revocation policy (`fallback_to_velora` or `keep_live_flagged`).
     */
    public function revokeEntitlement(VendorThemeEntitlement $entitlement, User $admin, ?string $reason = null): VendorThemeEntitlement
    {
        return DB::transaction(function () use ($entitlement, $admin, $reason) {
            $locked = VendorThemeEntitlement::lockForUpdate()->findOrFail($entitlement->id);
            if ($locked->isRevoked()) {
                throw new \DomainException('Entitlement is already revoked.');
            }

            $before = ['status' => $locked->status];
            $cleanReason = $reason ? mb_substr(trim(strip_tags($reason)), 0, 1000) : 'Access revoked by administrator.';

            $locked->update([
                'status' => VendorThemeEntitlement::STATUS_REVOKED,
                'revoked_by' => $admin->id,
                'revoked_at' => now(),
                'revocation_reason' => $cleanReason,
            ]);

            if ($locked->request_id) {
                $req = VendorThemeRequest::find($locked->request_id);
                if ($req && $req->status === VendorThemeRequest::STATUS_ACCESS_GRANTED) {
                    $history = is_array($req->status_history) ? $req->status_history : [];
                    $history[] = [
                        'status' => VendorThemeRequest::STATUS_ACCESS_REVOKED,
                        'payment_status' => $req->payment_verification_status,
                        'at' => now()->toIso8601String(),
                        'actor' => 'admin:' . $admin->id,
                        'note' => $cleanReason,
                    ];
                    $req->update([
                        'status' => VendorThemeRequest::STATUS_ACCESS_REVOKED,
                        'status_history' => $history,
                    ]);
                }
            }

            $vendor = Vendor::find($locked->vendor_id);
            $vendor?->bumpWebsiteCache();

            $this->recordAudit(
                action: 'theme.entitlement.revoked',
                actor: $admin,
                actorRole: 'admin',
                vendorId: $locked->vendor_id,
                themeId: $locked->theme_id,
                requestId: $locked->request_id,
                entitlementId: $locked->id,
                beforeState: $before,
                afterState: [
                    'status' => VendorThemeEntitlement::STATUS_REVOKED,
                    'revocation_policy' => $this->getRevocationPolicy(),
                ],
                notes: $cleanReason
            );

            return $locked->fresh();
        });
    }

    /**
     * Save vendor's customized draft for an entitled theme.
     * STRICTLY updates draft storage only; NEVER changes the live storefront.
     */
    public function saveDraft(Vendor $vendor, string $themeId, array $rawConfig): VendorThemeDraft
    {
        $cleanId = strtolower(trim($themeId));
        if (!$this->registry->isValidThemeId($cleanId)) {
            throw new \InvalidArgumentException('Invalid theme selected.');
        }

        if (!$this->isVendorEntitled($vendor, $cleanId)) {
            throw new \DomainException('You do not have active access to customize this theme.');
        }

        $manifest = $this->registry->getManifest($cleanId);
        $sanitized = $this->registry->sanitizeConfig($cleanId, $rawConfig, $vendor);

        return DB::transaction(function () use ($vendor, $cleanId, $manifest, $sanitized) {
            $draft = VendorThemeDraft::updateOrCreate(
                ['vendor_id' => $vendor->id, 'theme_id' => $cleanId],
                [
                    'theme_version' => $manifest['version'] ?? '1.0.0',
                    'config_data' => $sanitized,
                ]
            );

            // Sync draft pointer on vendor_storefront_configs WITHOUT modifying published_theme_id or published_config
            $configRecord = VendorStorefrontConfig::where('vendor_id', $vendor->id)->first();
            if (!$configRecord) {
                $fallbackLive = app(StorefrontPresetService::class)->normalizePresetKeyForVendor((string) ($vendor->layout_preset ?: 'velora'), $vendor);
                $fallbackConfig = app(StorefrontPresetService::class)->getDefaultConfig($fallbackLive, $vendor);
                VendorStorefrontConfig::create([
                    'vendor_id' => $vendor->id,
                    'preset_key' => $fallbackLive,
                    'published_theme_id' => $fallbackLive,
                    'published_theme_version' => '2.0.0',
                    'published_config' => $fallbackConfig,
                    'draft_theme_id' => $cleanId,
                    'draft_config' => $sanitized,
                    'schema_version' => 3,
                    'is_published' => true,
                    'last_saved_at' => now(),
                ]);
            } else {
                $configRecord->update([
                    'draft_theme_id' => $cleanId,
                    'draft_config' => $sanitized,
                    'last_saved_at' => now(),
                ]);
            }

            $this->recordAudit(
                action: 'theme.draft.saved',
                actor: auth()->user(),
                actorRole: 'vendor',
                vendorId: $vendor->id,
                themeId: $cleanId,
                afterState: ['theme_id' => $cleanId, 'theme_version' => $draft->theme_version],
                notes: 'Vendor saved unpublished draft.'
            );

            return $draft;
        });
    }

    /**
     * Reset a vendor's draft for an entitled theme back to that theme's clean defaults.
     */
    public function resetDraft(Vendor $vendor, string $themeId): VendorThemeDraft
    {
        $defaults = $this->registry->getDefaultConfig($themeId, $vendor);
        return $this->saveDraft($vendor, $themeId, $defaults);
    }

    /**
     * Explicit vendor publication action.
     * Validates: theme exists & ready, vendor is entitled, configuration is valid.
     * Updates published_theme_id + published_config atomically and invalidates cache.
     */
    public function publishTheme(Vendor $vendor, string $themeId): VendorStorefrontConfig
    {
        $this->ensureSynced();
        $cleanId = strtolower(trim($themeId));

        if (!$this->registry->isValidThemeId($cleanId)) {
            throw new \InvalidArgumentException('Selected theme is invalid.');
        }

        $dbTheme = StorefrontTheme::where('theme_id', $cleanId)->first();
        if ($dbTheme && !$dbTheme->isReady()) {
            throw new \DomainException('This theme is not currently ready for live publication.');
        }

        if (!$this->isVendorEntitled($vendor, $cleanId)) {
            throw new \DomainException('Active entitlement is required before publishing this theme.');
        }

        $manifest = $this->registry->getManifest($cleanId);
        $draftRow = VendorThemeDraft::where('vendor_id', $vendor->id)
            ->where('theme_id', $cleanId)
            ->first();

        $rawConfig = $draftRow?->config_data ?? $this->registry->getDefaultConfig($cleanId, $vendor);
        $sanitized = $this->registry->sanitizeConfig($cleanId, $rawConfig, $vendor);

        return DB::transaction(function () use ($vendor, $cleanId, $manifest, $sanitized) {
            $configRecord = VendorStorefrontConfig::lockForUpdate()
                ->where('vendor_id', $vendor->id)
                ->first();

            $beforeTheme = $configRecord?->published_theme_id ?: ($configRecord?->preset_key ?: $vendor->layout_preset);

            if (!$configRecord) {
                $configRecord = VendorStorefrontConfig::create([
                    'vendor_id' => $vendor->id,
                    'preset_key' => $cleanId,
                    'draft_theme_id' => $cleanId,
                    'published_theme_id' => $cleanId,
                    'published_theme_version' => $manifest['version'] ?? '1.0.0',
                    'schema_version' => 3,
                    'draft_config' => $sanitized,
                    'published_config' => $sanitized,
                    'is_published' => true,
                    'last_saved_at' => now(),
                    'last_published_at' => now(),
                ]);
            } else {
                $configRecord->update([
                    'preset_key' => $cleanId,
                    'draft_theme_id' => $cleanId,
                    'published_theme_id' => $cleanId,
                    'published_theme_version' => $manifest['version'] ?? '1.0.0',
                    'schema_version' => 3,
                    'draft_config' => $sanitized,
                    'published_config' => $sanitized,
                    'is_published' => true,
                    'last_saved_at' => now(),
                    'last_published_at' => now(),
                ]);
            }

            VendorStorefrontRevision::create([
                'vendor_id' => $vendor->id,
                'preset_key' => $cleanId,
                'config_data' => $sanitized,
                'summary' => 'Published theme: ' . ($manifest['name'] ?? $cleanId) . ' (v' . ($manifest['version'] ?? '1.0.0') . ')',
                'created_by' => auth()->id(),
            ]);

            $vendor->update(['layout_preset' => $cleanId]);
            $vendor->bumpWebsiteCache();

            $this->recordAudit(
                action: 'theme.published',
                actor: auth()->user(),
                actorRole: 'vendor',
                vendorId: $vendor->id,
                themeId: $cleanId,
                beforeState: ['published_theme_id' => $beforeTheme],
                afterState: [
                    'published_theme_id' => $cleanId,
                    'version' => $manifest['version'] ?? '1.0.0',
                ],
                notes: 'Vendor explicitly published entitled theme to live storefront.'
            );

            return $configRecord->fresh();
        });
    }

    /**
     * Get the vendor's draft config for a given theme (falling back to theme defaults without mutating anything).
     */
    public function getDraftConfigForTheme(Vendor $vendor, string $themeId): array
    {
        $draft = VendorThemeDraft::where('vendor_id', $vendor->id)
            ->where('theme_id', $themeId)
            ->first();

        return $this->registry->resolveEffectiveConfig($themeId, $draft?->config_data, $vendor);
    }

    /**
     * Get the vendor's published config for a given theme (never leaks drafts).
     */
    public function getPublishedConfigForTheme(Vendor $vendor, string $themeId): array
    {
        $configRecord = $vendor->relationLoaded('storefrontConfig')
            ? $vendor->storefrontConfig
            : $vendor->storefrontConfig()->first();

        $published = $configRecord?->published_config;
        if (is_array($published) && (($published['theme_id'] ?? $published['preset'] ?? null) === $themeId)) {
            return $this->registry->resolveEffectiveConfig($themeId, $published, $vendor);
        }

        return $this->registry->getDefaultConfig($themeId, $vendor);
    }

    /**
     * Admin updates a theme's catalogue metadata, price, visibility, availability, or QA status.
     */
    public function updateThemeCatalogue(StorefrontTheme $theme, array $data, User $admin): StorefrontTheme
    {
        $before = [
            'name' => $theme->name,
            'category' => $theme->category,
            'price_paise' => $theme->price_paise,
            'is_free' => $theme->is_free,
            'implementation_status' => $theme->implementation_status,
            'is_visible_in_catalogue' => $theme->is_visible_in_catalogue,
            'is_available_for_request' => $theme->is_available_for_request,
        ];

        $updates = [];
        if (isset($data['name']) && trim((string) $data['name']) !== '') {
            $updates['name'] = mb_substr(trim(strip_tags((string) $data['name'])), 0, 120);
        }
        if (isset($data['category']) && array_key_exists($data['category'], ThemeMarketplaceRegistry::CATEGORY_LABELS)) {
            $updates['category'] = $data['category'];
        }
        if (array_key_exists('description', $data)) {
            $updates['description'] = mb_substr(trim(strip_tags((string) $data['description'])), 0, 1000);
        }
        if (array_key_exists('price_rupees', $data)) {
            $raw = trim((string) $data['price_rupees']);
            $updates['price_paise'] = ($raw === '' || !is_numeric($raw)) ? null : max(0, (int) round(((float) $raw) * 100));
        }
        if (array_key_exists('is_free', $data)) {
            $updates['is_free'] = (bool) $data['is_free'];
        }
        if (isset($data['implementation_status']) && in_array($data['implementation_status'], ['ready', 'qa_pending', 'draft', 'deprecated'], true)) {
            $updates['implementation_status'] = $data['implementation_status'];
        }
        if (array_key_exists('is_visible_in_catalogue', $data)) {
            $updates['is_visible_in_catalogue'] = (bool) $data['is_visible_in_catalogue'];
        }
        if (array_key_exists('is_available_for_request', $data)) {
            $updates['is_available_for_request'] = (bool) $data['is_available_for_request'];
        }

        // Prevent marking an unfinished theme as available for purchase/request
        $effectiveStatus = $updates['implementation_status'] ?? $theme->implementation_status;
        if ($effectiveStatus !== 'ready') {
            $updates['is_available_for_request'] = false;
        }

        $theme->update($updates);
        $theme->refresh();

        $after = [
            'name' => $theme->name,
            'category' => $theme->category,
            'price_paise' => $theme->price_paise,
            'is_free' => $theme->is_free,
            'implementation_status' => $theme->implementation_status,
            'is_visible_in_catalogue' => $theme->is_visible_in_catalogue,
            'is_available_for_request' => $theme->is_available_for_request,
        ];

        $this->recordAudit(
            action: 'theme.catalogue.updated',
            actor: $admin,
            actorRole: 'admin',
            themeId: $theme->theme_id,
            beforeState: $before,
            afterState: $after,
            notes: 'Administrator updated theme catalogue/pricing settings.'
        );

        return $theme;
    }

    /**
     * Admin updates global Theme Marketplace settings (WhatsApp number & revocation policy).
     */
    public function updateMarketplaceSettings(array $data, User $admin): void
    {
        $before = [
            'whatsapp_number' => $this->getAdminWhatsAppNumber(),
            'revocation_policy' => $this->getRevocationPolicy(),
        ];

        if (array_key_exists('whatsapp_number', $data)) {
            $cleanWa = preg_replace('/[^0-9+\-\s]/', '', trim((string) $data['whatsapp_number']));
            Setting::set(self::SETTING_WHATSAPP_NUMBER, $cleanWa ?: null, 'theme_marketplace', 'string');
        }

        if (isset($data['revocation_policy']) && in_array($data['revocation_policy'], ['fallback_to_velora', 'keep_live_flagged'], true)) {
            Setting::set(self::SETTING_REVOCATION_POLICY, $data['revocation_policy'], 'theme_marketplace', 'string');
        }

        $this->recordAudit(
            action: 'theme.settings.updated',
            actor: $admin,
            actorRole: 'admin',
            beforeState: $before,
            afterState: [
                'whatsapp_number' => $this->getAdminWhatsAppNumber(),
                'revocation_policy' => $this->getRevocationPolicy(),
            ],
            notes: 'Administrator updated Theme Marketplace global settings.'
        );
    }

    protected function assertLegalTransition(string $from, string $to): void
    {
        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];
        if (!in_array($to, $allowed, true)) {
            throw new \DomainException("Illegal theme request transition from '{$from}' to '{$to}'.");
        }
    }

    protected function recordAudit(
        string $action,
        ?User $actor = null,
        ?string $actorRole = null,
        ?int $vendorId = null,
        ?string $themeId = null,
        ?int $requestId = null,
        ?int $entitlementId = null,
        ?array $beforeState = null,
        ?array $afterState = null,
        ?string $notes = null
    ): void {
        try {
            $ip = request()?->ip();
            ThemeAuditLog::create([
                'actor_user_id' => $actor?->id,
                'actor_role' => $actorRole ?: ($actor?->hasRole('admin') ? 'admin' : 'vendor'),
                'action' => $action,
                'vendor_id' => $vendorId,
                'theme_id' => $themeId,
                'request_id' => $requestId,
                'entitlement_id' => $entitlementId,
                'before_state' => $beforeState,
                'after_state' => $afterState,
                'notes' => $notes,
                'ip_address' => $ip,
            ]);

            // Mirror into K71 global AuditLog for unified governance
            AuditLog::create([
                'user_id' => $actor?->id,
                'vendor_id' => $vendorId,
                'action' => $action,
                'reason' => $notes,
                'old_values' => $beforeState,
                'new_values' => $afterState,
                'ip_address' => $ip,
                'user_agent' => request()?->userAgent() ? mb_substr((string) request()->userAgent(), 0, 250) : null,
            ]);
        } catch (\Throwable $e) {
            // Never interrupt core transaction on non-critical audit mirror
        }
    }
}
