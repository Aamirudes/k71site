<?php

namespace App\Services\Monetization;

use App\Models\AuditLog;
use App\Models\MonetizationSetting;
use App\Models\ScheduledJobRun;
use App\Models\SubscriptionEvent;
use App\Models\SubscriptionNotification;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionPlanVersion;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SubscriptionLifecycleService
{
    public function __construct(
        protected SectorRegistryService $sectors
    ) {}

    /**
     * Admin manually verifies / rejects / suspends / updates vendor verification status.
     * Verification NEVER automatically activates or creates a subscription!
     */
    public function updateVerificationStatus(
        Vendor $vendor,
        string $newVerificationStatus,
        ?int $adminId = null,
        ?string $notes = null,
        ?string $ip = null
    ): Vendor {
        return DB::transaction(function () use ($vendor, $newVerificationStatus, $adminId, $notes, $ip) {
            $oldValues = [
                'status' => $vendor->status,
                'verification_status' => $vendor->verification_status,
                'verification_notes' => $vendor->verification_notes,
            ];

            $accountStatus = match ($newVerificationStatus) {
                'verified' => 'approved',
                'suspended' => 'suspended',
                'rejected' => 'closed',
                'pending_review', 'unverified' => 'pending',
                default => $vendor->status,
            };

            $updates = [
                'verification_status' => $newVerificationStatus,
                'status' => $accountStatus,
                'last_admin_action' => 'verification_' . $newVerificationStatus,
                'last_admin_action_at' => now(),
                'last_admin_action_by' => $adminId,
            ];

            if ($notes !== null) {
                $updates['verification_notes'] = $notes;
            }
            if ($newVerificationStatus === 'verified') {
                $updates['verified_at'] = now();
                $updates['verified_by'] = $adminId;
                $updates['approved_at'] = $vendor->approved_at ?? now();
                $updates['approved_by'] = $vendor->approved_by ?? $adminId;
            }
            if ($newVerificationStatus === 'rejected') {
                $updates['rejection_reason'] = $notes;
            }

            $vendor->update($updates);
            $vendor->bumpWebsiteCache();

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $vendor->id,
                'auditable_type' => Vendor::class,
                'auditable_id' => $vendor->id,
                'action' => 'vendor_verification_' . $newVerificationStatus,
                'reason' => $notes,
                'old_values' => $oldValues,
                'new_values' => $vendor->only(['status', 'verification_status', 'verification_notes']),
                'ip_address' => $ip,
            ]);

            return $vendor->fresh();
        });
    }

    /**
     * Create or update a versioned subscription plan.
     */
    public function createOrUpdatePlan(array $data, ?SubscriptionPlan $plan = null, ?int $adminId = null, ?string $changeNotes = null, ?string $ip = null): SubscriptionPlan
    {
        return DB::transaction(function () use ($data, $plan, $adminId, $changeNotes, $ip) {
            $isNew = $plan === null;
            $oldSnapshot = $plan ? $plan->toArray() : null;

            $sectorKey = $this->sectors->normalizeSectorKey($data['sector_key'] ?? 'products');
            if (($data['sector_key'] ?? '') === 'all') {
                $sectorKey = 'all';
            }

            $applicableSectors = $data['applicable_sectors'] ?? [$sectorKey];
            if ($sectorKey === 'all') {
                $applicableSectors = ['all'];
            }
            $isCrossSector = (bool) ($data['is_cross_sector'] ?? ($sectorKey === 'all' || count($applicableSectors) > 1));
            $isFree = (bool) ($data['is_free'] ?? false);
            $priceAmount = $isFree ? 0 : max(0, (int) ($data['price_amount'] ?? 0));
            $durationDays = max(1, (int) ($data['duration_days'] ?? 30));

            $slugBase = Str::slug($data['slug'] ?? $data['name'] ?? 'plan') ?: 'plan';
            $slug = $isNew ? $slugBase : ($plan->slug ?: $slugBase);
            if ($isNew) {
                $i = 1;
                while (SubscriptionPlan::withTrashed()->where('slug', $slug)->exists()) {
                    $slug = $slugBase . '-' . $i++;
                }
            }

            $code = !empty($data['code'])
                ? strtoupper(preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $data['code']))
                : ($plan?->code ?: strtoupper(str_replace('-', '_', $slug)));

            $nextVersion = $isNew ? 1 : ((int) $plan->current_version + 1);
            $status = $data['status'] ?? ($plan?->status ?? 'active');

            $planPayload = [
                'code' => $code,
                'name' => trim((string) $data['name']),
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'sector_key' => $sectorKey,
                'applicable_sectors' => array_values(array_unique($applicableSectors)),
                'is_cross_sector' => $isCrossSector,
                'is_free' => $isFree,
                'price_amount' => $priceAmount,
                'price_monthly' => $priceAmount,
                'currency' => $data['currency'] ?? 'INR',
                'duration_days' => $durationDays,
                'custom_duration_allowed' => (bool) ($data['custom_duration_allowed'] ?? true),
                'default_commission_rule_id' => $data['default_commission_rule_id'] ?? null,
                'status' => $status,
                'is_active' => $status === 'active',
                'is_public_display' => (bool) ($data['is_public_display'] ?? false),
                'sort_order' => (int) ($data['sort_order'] ?? 0),
                'features' => $data['entitlements'] ?? [],
                'current_version' => $nextVersion,
                'admin_notes' => $data['admin_notes'] ?? null,
                'updated_by' => $adminId,
            ];

            if ($isNew) {
                $planPayload['created_by'] = $adminId;
                $plan = SubscriptionPlan::create($planPayload);
            } else {
                $plan->update($planPayload);
            }

            $defaultExpiry = $this->sectors->getDefaultExpiryPolicy();
            $expiryPolicy = array_merge($defaultExpiry, $data['expiry_policy'] ?? []);

            SubscriptionPlanVersion::create([
                'plan_id' => $plan->id,
                'version_number' => $nextVersion,
                'name' => $plan->name,
                'description' => $plan->description,
                'is_free' => $plan->is_free,
                'price_amount' => $plan->price_amount,
                'currency' => $plan->currency,
                'duration_days' => $plan->duration_days,
                'default_commission_rule_id' => $plan->default_commission_rule_id,
                'entitlements_json' => $data['entitlements'] ?? [],
                'expiry_policy_json' => $expiryPolicy,
                'promotional_benefits_json' => $data['promotional_benefits'] ?? null,
                'change_notes' => $changeNotes ?: ($isNew ? 'Initial plan version created by admin' : 'Updated plan terms by admin'),
                'created_by' => $adminId,
            ]);

            AuditLog::create([
                'user_id' => $adminId,
                'sector_key' => $sectorKey,
                'auditable_type' => SubscriptionPlan::class,
                'auditable_id' => $plan->id,
                'action' => $isNew ? 'subscription_plan_created' : 'subscription_plan_updated',
                'reason' => $changeNotes,
                'old_values' => $oldSnapshot,
                'new_values' => $plan->fresh()->toArray(),
                'ip_address' => $ip,
            ]);

            return $plan->fresh(['versions']);
        });
    }

    /**
     * Step 1 of Admin-Controlled Workflow:
     * Assign a plan to a vendor in a specific sector in PENDING_ACTIVATION state.
     * Does NOT activate subscription and does NOT grant access yet.
     */
    public function assignSubscription(
        Vendor $vendor,
        SubscriptionPlan $plan,
        string $sectorKey,
        array $options = [],
        ?int $adminId = null,
        ?string $ip = null
    ): VendorSubscription {
        return DB::transaction(function () use ($vendor, $plan, $sectorKey, $options, $adminId, $ip) {
            $sectorKey = ($sectorKey === 'all') ? 'all' : $this->sectors->normalizeSectorKey($sectorKey);

            if ($plan->status === 'archived') {
                throw new \InvalidArgumentException("Archived plan '{$plan->name}' cannot be newly assigned.");
            }
            if ($sectorKey !== 'all' && !$plan->coversSector($sectorKey)) {
                throw new \InvalidArgumentException("Plan '{$plan->name}' does not cover sector '{$sectorKey}'.");
            }

            $version = $plan->latestVersion();
            $entitlements = array_merge($version?->entitlements_json ?? ($plan->features ?? []), $options['entitlements_override'] ?? []);
            $expiryPolicy = array_merge($this->sectors->getDefaultExpiryPolicy(), $version?->expiry_policy_json ?? [], $options['expiry_policy_override'] ?? []);

            $durationDays = max(1, (int) ($options['duration_days'] ?? $plan->duration_days ?? 30));
            $startsAt = !empty($options['starts_at']) ? Carbon::parse($options['starts_at']) : null;
            $endsAt = !empty($options['ends_at']) ? Carbon::parse($options['ends_at']) : ($startsAt ? $startsAt->copy()->addDays($durationDays) : null);

            $graceDays = max(0, (int) ($options['grace_days'] ?? ($expiryPolicy['grace_days'] ?? 3)));
            $graceKeepVisible = (bool) ($options['grace_keep_visible'] ?? ($expiryPolicy['grace_keep_visible'] ?? true));
            $graceKeepFeatures = (bool) ($options['grace_keep_features'] ?? ($expiryPolicy['grace_keep_features'] ?? false));

            $reminderDays = $options['reminder_days_before'] ?? MonetizationSetting::getValue('default_reminder_days', [7, 3, 1]);

            $sub = VendorSubscription::create([
                'vendor_id' => $vendor->id,
                'plan_id' => $plan->id,
                'sector_key' => $sectorKey,
                'applicable_sectors_snapshot' => $plan->applicable_sectors ?: [$sectorKey],
                'plan_version_id' => $version?->id,
                'plan_version_number' => $version?->version_number ?? $plan->current_version ?? 1,
                'plan_name_snapshot' => $plan->name,
                'price_amount_snapshot' => $plan->price_amount,
                'is_free_snapshot' => $plan->is_free,
                'currency' => $plan->currency ?? 'INR',
                'duration_days' => $durationDays,
                'entitlements_snapshot' => $entitlements,
                'expiry_policy_snapshot' => $expiryPolicy,
                'status' => VendorSubscription::STATUS_PENDING_ACTIVATION,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'grace_ends_at' => null,
                'grace_keep_visible' => $graceKeepVisible,
                'grace_keep_features' => $graceKeepFeatures,
                'commission_rule_id' => $options['commission_rule_id'] ?? $plan->default_commission_rule_id,
                'commission_rate_override' => $options['commission_rate_override'] ?? null,
                'commission_fixed_override' => $options['commission_fixed_override'] ?? null,
                'reminder_days_before' => $reminderDays,
                'auto_reminder_enabled' => (bool) ($options['auto_reminder_enabled'] ?? true),
                'external_payment_received' => false,
                'internal_notes' => $options['internal_notes'] ?? null,
                'admin_reason' => $options['admin_reason'] ?? 'Plan assigned by admin pending activation confirmation',
                'assigned_by' => $adminId,
            ]);

            $this->logEvent($sub, 'assigned', null, VendorSubscription::STATUS_PENDING_ACTIVATION, $adminId, 'admin', $sub->admin_reason, [
                'plan_id' => $plan->id,
                'plan_version' => $sub->plan_version_number,
            ]);

            $vendor->forceFill([
                'last_admin_action' => "assigned_plan:{$sectorKey}",
                'last_admin_action_at' => now(),
                'last_admin_action_by' => $adminId,
            ])->saveQuietly();

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $vendor->id,
                'sector_key' => $sectorKey,
                'auditable_type' => VendorSubscription::class,
                'auditable_id' => $sub->id,
                'action' => 'subscription_assigned',
                'reason' => $sub->admin_reason,
                'new_values' => $sub->toArray(),
                'ip_address' => $ip,
            ]);

            return $sub;
        });
    }

    /**
     * Step 2: Record admin offline payment confirmation.
     * STRICT BUSINESS RULE: Recording payment DOES NOT activate the subscription automatically!
     */
    public function recordExternalPayment(
        VendorSubscription $subscription,
        array $paymentData,
        ?int $adminId = null,
        ?string $ip = null
    ): VendorSubscription {
        return DB::transaction(function () use ($subscription, $paymentData, $adminId, $ip) {
            $old = $subscription->only([
                'external_payment_received',
                'external_payment_date',
                'external_payment_amount',
                'external_payment_method',
                'external_payment_ref',
                'external_receipt_path',
                'status',
            ]);

            $received = (bool) ($paymentData['external_payment_received'] ?? true);
            $subscription->update([
                'external_payment_received' => $received,
                'external_payment_date' => !empty($paymentData['external_payment_date'])
                    ? Carbon::parse($paymentData['external_payment_date'])
                    : ($received ? now() : null),
                'external_payment_amount' => isset($paymentData['external_payment_amount']) && $paymentData['external_payment_amount'] !== ''
                    ? (int) $paymentData['external_payment_amount']
                    : $subscription->external_payment_amount,
                'external_payment_method' => $paymentData['external_payment_method'] ?? $subscription->external_payment_method,
                'external_payment_ref' => $paymentData['external_payment_ref'] ?? $subscription->external_payment_ref,
                'external_receipt_path' => $paymentData['external_receipt_path'] ?? $subscription->external_receipt_path,
                'internal_notes' => $paymentData['internal_notes'] ?? $subscription->internal_notes,
            ]);

            // IMPORTANT: status intentionally untouched (e.g., stays pending_activation)
            $this->logEvent($subscription, 'payment_recorded', $subscription->status, $subscription->status, $adminId, 'admin', 'External payment confirmation recorded by admin', [
                'received' => $received,
                'amount' => $subscription->external_payment_amount,
                'method' => $subscription->external_payment_method,
                'ref' => $subscription->external_payment_ref,
            ]);

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $subscription->vendor_id,
                'sector_key' => $subscription->sector_key,
                'auditable_type' => VendorSubscription::class,
                'auditable_id' => $subscription->id,
                'action' => 'subscription_payment_recorded',
                'reason' => 'Manual external payment details recorded (awaiting explicit activation confirmation)',
                'old_values' => $old,
                'new_values' => $subscription->fresh()->only(array_keys($old)),
                'ip_address' => $ip,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Step 3: Explicit Admin Activation Confirmation.
     * Prevents overlapping active subscriptions in the same sector by superseding prior active ones cleanly.
     */
    public function activateSubscription(
        VendorSubscription $subscription,
        array $options = [],
        ?int $adminId = null,
        ?string $ip = null
    ): VendorSubscription {
        return DB::transaction(function () use ($subscription, $options, $adminId, $ip) {
            $vendor = $subscription->vendor;
            if (!$vendor || !$vendor->isVerifiedMerchant()) {
                throw new \InvalidArgumentException('Vendor must be verified by admin before activating a subscription.');
            }
            if (!$subscription->is_free_snapshot && !empty($options['require_payment']) && !$subscription->external_payment_received) {
                throw new \InvalidArgumentException('External payment confirmation must be recorded for this paid plan before activation.');
            }

            $oldStatus = $subscription->status;
            $durationDays = max(1, (int) ($options['duration_days'] ?? $subscription->duration_days ?? 30));
            $startsAt = !empty($options['starts_at'])
                ? Carbon::parse($options['starts_at'])
                : ($subscription->starts_at ?: now());
            $endsAt = !empty($options['ends_at'])
                ? Carbon::parse($options['ends_at'])
                : $startsAt->copy()->addDays($durationDays);

            // Prevent overlapping active/grace subscriptions for same vendor+sector unless admin explicitly confirmed replace
            $existingConflicting = VendorSubscription::query()
                ->where('vendor_id', $vendor->id)
                ->where('sector_key', $subscription->sector_key)
                ->where('id', '!=', $subscription->id)
                ->whereIn('status', [VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD])
                ->get();

            foreach ($existingConflicting as $prev) {
                if (empty($options['replace_existing'])) {
                    throw new \InvalidArgumentException("Vendor already has an active subscription (#{$prev->id}) in sector '{$subscription->sector_key}'. Cancel or renew it cleanly.");
                }
                $prevFrom = $prev->status;
                $prev->update([
                    'status' => VendorSubscription::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                    'admin_reason' => 'Superseded by manual activation of subscription #' . $subscription->id,
                ]);
                $this->logEvent($prev, 'superseded', $prevFrom, VendorSubscription::STATUS_CANCELLED, $adminId, 'admin', 'Superseded by new subscription #' . $subscription->id);
            }

            $subscription->update([
                'status' => VendorSubscription::STATUS_ACTIVE,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duration_days' => $durationDays,
                'grace_ends_at' => null,
                'activated_by' => $adminId,
                'activated_at' => now(),
                'admin_reason' => $options['reason'] ?? ($subscription->admin_reason ?: 'Manually activated by K71 admin'),
            ]);

            // Ensure operating_sectors on vendor includes the subscribed sector
            $sectors = $this->sectors->resolveVendorSectors($vendor);
            if ($subscription->sector_key !== 'all' && !in_array($subscription->sector_key, $sectors, true)) {
                $sectors[] = $subscription->sector_key;
            }
            $vendor->update([
                'operating_sectors' => array_values(array_unique($sectors)),
                'last_admin_action' => "activated_subscription:{$subscription->sector_key}",
                'last_admin_action_at' => now(),
                'last_admin_action_by' => $adminId,
            ]);
            $vendor->bumpWebsiteCache();

            $this->logEvent($subscription, 'activated', $oldStatus, VendorSubscription::STATUS_ACTIVE, $adminId, 'admin', $subscription->admin_reason, [
                'starts_at' => $startsAt->toIso8601String(),
                'ends_at' => $endsAt->toIso8601String(),
            ]);

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $vendor->id,
                'sector_key' => $subscription->sector_key,
                'auditable_type' => VendorSubscription::class,
                'auditable_id' => $subscription->id,
                'action' => 'subscription_activated',
                'reason' => $subscription->admin_reason,
                'old_values' => ['status' => $oldStatus],
                'new_values' => [
                    'status' => VendorSubscription::STATUS_ACTIVE,
                    'starts_at' => $startsAt->toDateTimeString(),
                    'ends_at' => $endsAt->toDateTimeString(),
                ],
                'ip_address' => $ip,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Manual Renewal: extends an active, grace, or expired subscription with full audit trail.
     */
    public function renewSubscription(
        VendorSubscription $subscription,
        array $options = [],
        ?int $adminId = null,
        ?string $ip = null
    ): VendorSubscription {
        return DB::transaction(function () use ($subscription, $options, $adminId, $ip) {
            $oldValues = $subscription->only(['status', 'starts_at', 'ends_at', 'plan_id', 'plan_version_number']);
            $durationDays = max(1, (int) ($options['duration_days'] ?? $subscription->duration_days ?? 30));

            // If extending currently active period, stack from ends_at unless reset_from_now is specified
            $baseStart = (!empty($options['starts_at']))
                ? Carbon::parse($options['starts_at'])
                : (($subscription->ends_at && $subscription->ends_at->isFuture() && empty($options['reset_from_now']))
                    ? $subscription->ends_at->copy()
                    : now());

            $newEndsAt = !empty($options['ends_at'])
                ? Carbon::parse($options['ends_at'])
                : $baseStart->copy()->addDays($durationDays);

            $updates = [
                'status' => VendorSubscription::STATUS_ACTIVE,
                'starts_at' => $subscription->starts_at ?: now(),
                'ends_at' => $newEndsAt,
                'duration_days' => $durationDays,
                'grace_ends_at' => null,
                'expired_at' => null,
                'activated_by' => $adminId ?? $subscription->activated_by,
                'activated_at' => now(),
                'admin_reason' => $options['reason'] ?? 'Manually renewed by K71 admin',
            ];

            if (!empty($options['external_payment_received'])) {
                $updates['external_payment_received'] = true;
                $updates['external_payment_date'] = now();
                $updates['external_payment_amount'] = (int) ($options['external_payment_amount'] ?? $subscription->price_amount_snapshot);
                $updates['external_payment_method'] = $options['external_payment_method'] ?? $subscription->external_payment_method;
                $updates['external_payment_ref'] = $options['external_payment_ref'] ?? $subscription->external_payment_ref;
            }

            if (!empty($options['sync_to_latest_plan_version']) && $subscription->plan) {
                $latestVersion = $subscription->plan->latestVersion();
                if ($latestVersion) {
                    $updates['plan_version_id'] = $latestVersion->id;
                    $updates['plan_version_number'] = $latestVersion->version_number;
                    $updates['plan_name_snapshot'] = $latestVersion->name;
                    $updates['price_amount_snapshot'] = $latestVersion->price_amount;
                    $updates['is_free_snapshot'] = $latestVersion->is_free;
                    $updates['entitlements_snapshot'] = $latestVersion->entitlements_json;
                    $updates['expiry_policy_snapshot'] = $latestVersion->expiry_policy_json;
                }
            }

            $subscription->update($updates);
            $subscription->vendor?->bumpWebsiteCache();

            $this->logEvent($subscription, 'renewed', $oldValues['status'], VendorSubscription::STATUS_ACTIVE, $adminId, 'admin', $updates['admin_reason'], [
                'old_ends_at' => (string) $oldValues['ends_at'],
                'new_ends_at' => $newEndsAt->toIso8601String(),
            ]);

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $subscription->vendor_id,
                'sector_key' => $subscription->sector_key,
                'auditable_type' => VendorSubscription::class,
                'auditable_id' => $subscription->id,
                'action' => 'subscription_renewed',
                'reason' => $updates['admin_reason'],
                'old_values' => $oldValues,
                'new_values' => $subscription->fresh()->only(array_keys($oldValues)),
                'ip_address' => $ip,
            ]);

            return $subscription->fresh();
        });
    }

    public function suspendSubscription(VendorSubscription $subscription, string $reason, ?int $adminId = null, ?string $ip = null): VendorSubscription
    {
        return $this->changeStatus($subscription, VendorSubscription::STATUS_SUSPENDED, 'suspended', $reason, $adminId, $ip);
    }

    public function cancelSubscription(VendorSubscription $subscription, string $reason, ?int $adminId = null, ?string $ip = null): VendorSubscription
    {
        return $this->changeStatus($subscription, VendorSubscription::STATUS_CANCELLED, 'cancelled', $reason, $adminId, $ip);
    }

    protected function changeStatus(VendorSubscription $subscription, string $toStatus, string $eventType, string $reason, ?int $adminId, ?string $ip): VendorSubscription
    {
        return DB::transaction(function () use ($subscription, $toStatus, $eventType, $reason, $adminId, $ip) {
            $oldStatus = $subscription->status;
            $updates = [
                'status' => $toStatus,
                'admin_reason' => $reason,
            ];
            if ($toStatus === VendorSubscription::STATUS_CANCELLED) {
                $updates['cancelled_at'] = now();
            }
            if ($toStatus === VendorSubscription::STATUS_SUSPENDED) {
                $updates['suspended_at'] = now();
            }

            $subscription->update($updates);
            $subscription->vendor?->bumpWebsiteCache();

            $this->logEvent($subscription, $eventType, $oldStatus, $toStatus, $adminId, 'admin', $reason);

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $subscription->vendor_id,
                'sector_key' => $subscription->sector_key,
                'auditable_type' => VendorSubscription::class,
                'auditable_id' => $subscription->id,
                'action' => 'subscription_' . $eventType,
                'reason' => $reason,
                'old_values' => ['status' => $oldStatus],
                'new_values' => ['status' => $toStatus],
                'ip_address' => $ip,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Explicit admin opt-in to upgrade an existing subscription's snapshot to a newer plan version.
     */
    public function applyPlanVersion(VendorSubscription $subscription, SubscriptionPlanVersion $version, ?int $adminId = null, ?string $reason = null, ?string $ip = null): VendorSubscription
    {
        return DB::transaction(function () use ($subscription, $version, $adminId, $reason, $ip) {
            $old = $subscription->only(['plan_version_id', 'plan_version_number', 'entitlements_snapshot']);

            $subscription->update([
                'plan_version_id' => $version->id,
                'plan_version_number' => $version->version_number,
                'plan_name_snapshot' => $version->name,
                'entitlements_snapshot' => $version->entitlements_json,
                'expiry_policy_snapshot' => $version->expiry_policy_json,
            ]);
            $subscription->vendor?->bumpWebsiteCache();

            $this->logEvent($subscription, 'version_applied', $subscription->status, $subscription->status, $adminId, 'admin', $reason ?: "Applied plan version v{$version->version_number}");

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $subscription->vendor_id,
                'sector_key' => $subscription->sector_key,
                'auditable_type' => VendorSubscription::class,
                'auditable_id' => $subscription->id,
                'action' => 'subscription_version_upgraded',
                'reason' => $reason ?: "Upgraded snapshot from v{$old['plan_version_number']} to v{$version->version_number}",
                'old_values' => ['plan_version_number' => $old['plan_version_number']],
                'new_values' => ['plan_version_number' => $version->version_number],
                'ip_address' => $ip,
            ]);

            return $subscription->fresh();
        });
    }

    /**
     * Transition any passed overdue active / grace_period subscriptions idempotently.
     */
    public function transitionOverdueSubscriptions(Collection $overdueSubs, string $source = 'scheduler'): array
    {
        $expiredCount = 0;
        $graceCount = 0;
        $notificationsCount = 0;

        foreach ($overdueSubs as $sub) {
            DB::transaction(function () use ($sub, $source, &$expiredCount, &$graceCount, &$notificationsCount) {
                $locked = VendorSubscription::lockForUpdate()->find($sub->id);
                if (!$locked) {
                    return;
                }

                $policy = $locked->expiry_policy_snapshot ?? $this->sectors->getDefaultExpiryPolicy();
                $graceDays = max(0, (int) ($policy['grace_days'] ?? 0));

                if ($locked->status === VendorSubscription::STATUS_ACTIVE && $locked->ends_at && $locked->ends_at->isPast()) {
                    $graceEnds = $graceDays > 0 ? $locked->ends_at->copy()->addDays($graceDays) : null;

                    if ($graceEnds && $graceEnds->isFuture()) {
                        $locked->update([
                            'status' => VendorSubscription::STATUS_GRACE_PERIOD,
                            'grace_ends_at' => $graceEnds,
                        ]);
                        $this->logEvent($locked, 'entered_grace_period', VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_GRACE_PERIOD, null, $source, 'Subscription end date reached; entered grace period');
                        $notificationsCount += $this->createDeduplicatedNotification($locked, 'grace_period_started', null, "Subscription in Grace Period ({$locked->sector_key})", "Your K71 {$locked->sector_key} subscription expired on {$locked->ends_at->format('d M Y')} and is currently in grace period until {$graceEnds->format('d M Y')}. Contact K71 admin on WhatsApp to renew.");
                        $graceCount++;
                    } else {
                        $locked->update([
                            'status' => VendorSubscription::STATUS_EXPIRED,
                            'expired_at' => now(),
                        ]);
                        $this->logEvent($locked, 'expired', VendorSubscription::STATUS_ACTIVE, VendorSubscription::STATUS_EXPIRED, null, $source, 'Subscription expired');
                        $notificationsCount += $this->createDeduplicatedNotification($locked, 'expired', null, "Subscription Expired ({$locked->sector_key})", "Your K71 {$locked->sector_key} subscription ({$locked->plan_name_snapshot}) expired on {$locked->ends_at->format('d M Y')}. Public storefront and sector features are now paused until admin renewal.");
                        $expiredCount++;
                    }
                    $locked->vendor?->bumpWebsiteCache();
                } elseif ($locked->status === VendorSubscription::STATUS_GRACE_PERIOD && ($locked->grace_ends_at === null || $locked->grace_ends_at->isPast())) {
                    $locked->update([
                        'status' => VendorSubscription::STATUS_EXPIRED,
                        'expired_at' => now(),
                    ]);
                    $this->logEvent($locked, 'expired', VendorSubscription::STATUS_GRACE_PERIOD, VendorSubscription::STATUS_EXPIRED, null, $source, 'Grace period ended; subscription expired');
                    $notificationsCount += $this->createDeduplicatedNotification($locked, 'expired', null, "Grace Period Ended — Subscription Expired ({$locked->sector_key})", "Your K71 {$locked->sector_key} subscription grace period has ended. Please contact K71 admin on WhatsApp to renew.");
                    $locked->vendor?->bumpWebsiteCache();
                    $expiredCount++;
                }
            });
        }

        return [
            'expired' => $expiredCount,
            'grace' => $graceCount,
            'notifications' => $notificationsCount,
        ];
    }

    /**
     * Full idempotent scheduled expiry & reminder sweep.
     */
    public function runScheduledExpirySweep(string $trigger = 'scheduler'): ScheduledJobRun
    {
        $run = ScheduledJobRun::create([
            'job_name' => 'subscriptions:process-expiry',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $overdue = VendorSubscription::query()
                ->where(function ($q) {
                    $q->where('status', VendorSubscription::STATUS_ACTIVE)
                      ->whereNotNull('ends_at')
                      ->where('ends_at', '<=', now());
                })
                ->orWhere(function ($q) {
                    $q->where('status', VendorSubscription::STATUS_GRACE_PERIOD)
                      ->where(function ($g) {
                          $g->whereNull('grace_ends_at')->orWhere('grace_ends_at', '<=', now());
                      });
                })
                ->get();

            $transitions = $this->transitionOverdueSubscriptions($overdue, $trigger);

            // Upcoming expiry reminders for active subscriptions
            $remindersCreated = 0;
            $activeSubs = VendorSubscription::with('vendor')
                ->where('status', VendorSubscription::STATUS_ACTIVE)
                ->where('auto_reminder_enabled', true)
                ->whereNotNull('ends_at')
                ->where('ends_at', '>', now())
                ->get();

            $defaultOffsets = MonetizationSetting::getValue('default_reminder_days', [7, 3, 1]);
            if (!is_array($defaultOffsets) || empty($defaultOffsets)) {
                $defaultOffsets = [7, 3, 1];
            }

            foreach ($activeSubs as $sub) {
                $daysLeft = (int) now()->startOfDay()->diffInDays($sub->ends_at->copy()->startOfDay(), false);
                $offsets = is_array($sub->reminder_days_before) && !empty($sub->reminder_days_before)
                    ? array_map('intval', $sub->reminder_days_before)
                    : array_map('intval', $defaultOffsets);

                foreach ($offsets as $offsetDay) {
                    if ($daysLeft <= $offsetDay && $daysLeft >= max(0, $offsetDay - 1)) {
                        $remindersCreated += $this->createDeduplicatedNotification(
                            $sub,
                            'expiry_reminder',
                            $offsetDay,
                            "Subscription Expiring in {$daysLeft} Day(s) — {$sub->sector_key}",
                            "Reminder: Your K71 {$sub->sector_key} subscription ({$sub->plan_name_snapshot}) expires on {$sub->ends_at->format('d M Y')}. Contact K71 admin on WhatsApp to renew."
                        );
                    }
                }
            }

            $totalNotifications = $transitions['notifications'] + $remindersCreated;
            MonetizationSetting::setValue('last_expiry_run_at', now()->toDateTimeString(), 'string');

            $run->update([
                'status' => 'success',
                'processed_count' => $overdue->count() + $activeSubs->count(),
                'expired_count' => $transitions['expired'],
                'grace_count' => $transitions['grace'],
                'reminders_created_count' => $totalNotifications,
                'summary' => "Expired: {$transitions['expired']}, Grace: {$transitions['grace']}, Reminders/Alerts: {$totalNotifications}",
                'finished_at' => now(),
            ]);

            return $run->fresh();
        } catch (\Throwable $e) {
            Log::error('Subscription expiry sweep failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $run->update([
                'status' => 'failed',
                'failed_count' => 1,
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
            throw $e;
        }
    }

    public function buildWhatsAppReminderDraft(VendorSubscription $sub): string
    {
        $template = (string) MonetizationSetting::getValue(
            'whatsapp_renewal_template',
            'Hello {contact_person}, your K71 {sector_name} subscription for {shop_name} (Plan: {plan_name}) expires on {expiry_date} ({days_remaining} day(s) left). Reply on WhatsApp to renew.'
        );

        $vendor = $sub->vendor;
        $daysLeft = $sub->days_remaining ?? 0;

        return str_replace(
            ['{contact_person}', '{shop_name}', '{sector_name}', '{plan_name}', '{expiry_date}', '{days_remaining}'],
            [
                $vendor?->contact_person ?: ($vendor?->user?->name ?: $vendor?->shop_name ?: 'Partner'),
                $vendor?->shop_name ?? 'your store',
                ucfirst($sub->sector_key),
                $sub->plan_name_snapshot ?: ($sub->plan?->name ?? 'Plan'),
                $sub->ends_at?->format('d M Y') ?? 'soon',
                (string) $daysLeft,
            ],
            $template
        );
    }

    protected function createDeduplicatedNotification(
        VendorSubscription $sub,
        string $eventKey,
        ?int $reminderDay,
        string $title,
        string $message
    ): int {
        $dedupeKey = implode(':', [
            'sub',
            $sub->id,
            $eventKey,
            $reminderDay !== null ? 'd' . $reminderDay : 'once',
            $sub->ends_at?->format('Ymd') ?? 'none',
        ]);

        if (SubscriptionNotification::where('dedupe_key', $dedupeKey)->exists()) {
            return 0;
        }

        SubscriptionNotification::create([
            'vendor_subscription_id' => $sub->id,
            'vendor_id' => $sub->vendor_id,
            'sector_key' => $sub->sector_key,
            'recipient_type' => 'vendor',
            'event_key' => $eventKey,
            'reminder_day' => $reminderDay,
            'title' => $title,
            'message' => $message,
            'whatsapp_message' => $this->buildWhatsAppReminderDraft($sub),
            'channel' => 'in_app',
            'dedupe_key' => $dedupeKey,
            'sent_at' => now(),
        ]);

        return 1;
    }

    public function logEvent(
        VendorSubscription $sub,
        string $eventType,
        ?string $fromStatus,
        ?string $toStatus,
        ?int $actorId = null,
        string $actorType = 'admin',
        ?string $reason = null,
        array $payload = []
    ): SubscriptionEvent {
        return SubscriptionEvent::create([
            'vendor_subscription_id' => $sub->id,
            'vendor_id' => $sub->vendor_id,
            'sector_key' => $sub->sector_key,
            'event_type' => $eventType,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'actor_id' => $actorId,
            'actor_type' => $actorType,
            'reason' => $reason,
            'payload_json' => $payload ?: null,
            'created_at' => now(),
        ]);
    }
}
