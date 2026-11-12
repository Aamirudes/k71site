<?php

namespace App\Services\Monetization;

use App\Models\AuditLog;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\OrderCommission;
use App\Models\Vendor;
use App\Models\VendorSubscription;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    public function __construct(
        protected SectorRegistryService $sectors,
        protected EntitlementService $entitlements
    ) {}

    /**
     * Resolve commission rate + fixed fee according to strict priority hierarchy:
     * 1. Transaction-level manual override (passed in $transactionOverride)
     * 2. Vendor-specific override rule (VendorSubscription override OR active vendor CommissionRule)
     * 3. Category-specific rule
     * 4. Plan-specific rule (or active subscription's assigned rule)
     * 5. Sector default rule
     * 6. Platform default rule (5.00% fallback if none in DB)
     */
    public function resolveEffectiveRule(
        Vendor $vendor,
        string $sectorKey,
        ?int $categoryId = null,
        ?VendorSubscription $subscription = null,
        ?array $transactionOverride = null
    ): array {
        $sectorKey = $this->sectors->normalizeSectorKey($sectorKey);
        $now = now();

        // 1. Transaction-level manual override
        if (!empty($transactionOverride) && array_key_exists('rate_percent', $transactionOverride)) {
            return [
                'rule' => null,
                'rule_id' => null,
                'rule_version' => 1,
                'source' => 'transaction_override',
                'rate_percent' => round((float) $transactionOverride['rate_percent'], 2),
                'fixed_fee_paise' => (int) ($transactionOverride['fixed_fee_paise'] ?? 0),
                'name' => 'Manual Transaction Override',
            ];
        }

        // 2a. Subscription-level vendor commission override (explicitly set by admin on vendor's subscription)
        $sub = $subscription ?? $this->entitlements->resolveEffectiveSubscription($vendor, $sectorKey);
        if ($sub && ($sub->commission_rate_override !== null || $sub->commission_fixed_override !== null)) {
            return [
                'rule' => null,
                'rule_id' => $sub->commission_rule_id,
                'rule_version' => 1,
                'source' => 'vendor_override',
                'rate_percent' => round((float) ($sub->commission_rate_override ?? 5.00), 2),
                'fixed_fee_paise' => (int) ($sub->commission_fixed_override ?? 0),
                'name' => 'Vendor Subscription Commission Override',
            ];
        }

        // Helper for date-active rules
        $activeRuleQuery = fn() => CommissionRule::query()
            ->where('is_active', true)
            ->where(fn($q) => $q->whereNull('effective_from')->orWhere('effective_from', '<=', $now))
            ->where(fn($q) => $q->whereNull('effective_until')->orWhere('effective_until', '>=', $now))
            ->orderByDesc('priority')
            ->orderByDesc('id');

        // 2b. Vendor-specific CommissionRule (optionally matching sector)
        $vendorRule = $activeRuleQuery()
            ->where('scope', 'vendor')
            ->where('vendor_id', $vendor->id)
            ->where(fn($q) => $q->whereNull('sector_key')->orWhere('sector_key', $sectorKey))
            ->first();
        if ($vendorRule) {
            return $this->formatRuleResult($vendorRule, 'vendor');
        }

        // 3. Category-specific CommissionRule
        if ($categoryId) {
            $categoryRule = $activeRuleQuery()
                ->where('scope', 'category')
                ->where('category_id', $categoryId)
                ->where(fn($q) => $q->whereNull('sector_key')->orWhere('sector_key', $sectorKey))
                ->first();
            if ($categoryRule) {
                return $this->formatRuleResult($categoryRule, 'category');
            }
        }

        // 4. Plan-specific rule (explicit commission_rule_id, plan-scoped rule, OR inline plan commission terms)
        if ($sub) {
            if ($sub->commission_rule_id) {
                $assignedRule = $activeRuleQuery()->where('id', $sub->commission_rule_id)->first();
                if ($assignedRule) {
                    return $this->formatRuleResult($assignedRule, 'plan');
                }
            }
            if ($sub->plan_id) {
                $planRule = $activeRuleQuery()
                    ->where('scope', 'plan')
                    ->where('plan_id', $sub->plan_id)
                    ->where(fn($q) => $q->whereNull('sector_key')->orWhere('sector_key', $sectorKey))
                    ->first();
                if ($planRule) {
                    return $this->formatRuleResult($planRule, 'plan');
                }
            }

            $planCommType = $sub->commission_type_snapshot ?: $sub->plan?->commission_type;
            if ($planCommType && $planCommType !== 'default') {
                $planRate = $sub->commission_rate_snapshot ?? $sub->plan?->commission_rate ?? 0;
                $planFixed = $sub->commission_fixed_snapshot ?? $sub->plan?->commission_fixed_paise ?? 0;

                if ($planCommType === 'zero') {
                    return [
                        'rule' => null,
                        'rule_id' => null,
                        'rule_version' => (int) ($sub->plan_version_number ?? 1),
                        'source' => 'plan',
                        'rate_percent' => 0.00,
                        'fixed_fee_paise' => 0,
                        'name' => ($sub->plan_name_snapshot ?: 'Plan') . ' (Zero Commission)',
                    ];
                }

                return [
                    'rule' => null,
                    'rule_id' => null,
                    'rule_version' => (int) ($sub->plan_version_number ?? 1),
                    'source' => 'plan',
                    'rate_percent' => $planCommType === 'fixed' ? 0.00 : round((float) $planRate, 2),
                    'fixed_fee_paise' => in_array($planCommType, ['fixed', 'hybrid'], true) ? (int) $planFixed : 0,
                    'name' => ($sub->plan_name_snapshot ?: 'Plan') . ' Terms (' . ucfirst($planCommType) . ')',
                ];
            }
        }

        // 5. Sector default CommissionRule
        $sectorRule = $activeRuleQuery()
            ->where('scope', 'sector')
            ->where('sector_key', $sectorKey)
            ->first();
        if ($sectorRule) {
            return $this->formatRuleResult($sectorRule, 'sector');
        }

        // 6. Platform default CommissionRule
        $platformRule = $activeRuleQuery()
            ->where('scope', 'platform')
            ->first();
        if ($platformRule) {
            return $this->formatRuleResult($platformRule, 'platform');
        }

        // Deterministic code-level fallback matching baseline 5%
        return [
            'rule' => null,
            'rule_id' => null,
            'rule_version' => 1,
            'source' => 'platform_fallback',
            'rate_percent' => 5.00,
            'fixed_fee_paise' => 0,
            'name' => 'Fallback Platform Rate (5%)',
        ];
    }

    /**
     * Exact integer paise calculation.
     * Note: Matches K71's accounting base: max(0, subtotal - discount_amount) excluding shipping/packaging.
     */
    public function calculateBreakdownForOrder(Order $order, ?array $transactionOverride = null): array
    {
        $vendor = $order->relationLoaded('vendor') ? $order->vendor : $order->vendor()->first();
        $sectorKey = $this->sectors->resolveOrderSector($order);

        // Dominant category from first item if present
        $firstItem = $order->relationLoaded('items')
            ? $order->items->first()
            : $order->items()->with('product')->first();
        $categoryId = $firstItem?->product?->category_id;

        $sub = $vendor ? $this->entitlements->resolveEffectiveSubscription($vendor, $sectorKey) : null;
        $resolved = $vendor
            ? $this->resolveEffectiveRule($vendor, $sectorKey, $categoryId, $sub, $transactionOverride)
            : [
                'rule' => null,
                'rule_id' => null,
                'rule_version' => 1,
                'source' => 'platform_fallback',
                'rate_percent' => 5.00,
                'fixed_fee_paise' => 0,
                'name' => 'Fallback Platform Rate (5%)',
            ];

        $commissionBasePaise = max(0, (int) $order->subtotal - (int) $order->discount_amount);
        $ratePercent = (float) $resolved['rate_percent'];
        $fixedFeePaise = (int) $resolved['fixed_fee_paise'];

        $percentageFeePaise = (int) round($commissionBasePaise * ($ratePercent / 100));
        $calculatedCommissionPaise = min($commissionBasePaise, max(0, $percentageFeePaise + ($commissionBasePaise > 0 ? $fixedFeePaise : 0)));
        $vendorNetPaise = max(0, $commissionBasePaise - $calculatedCommissionPaise);

        return [
            'vendor' => $vendor,
            'sector_key' => $sectorKey,
            'subscription' => $sub,
            'resolved' => $resolved,
            'commission_base_paise' => $commissionBasePaise,
            'tax_paise' => (int) ($order->tax_amount ?? 0),
            'shipping_paise' => (int) ($order->shipping_amount ?? 0),
            'discount_paise' => (int) ($order->discount_amount ?? 0),
            'rate_percent' => $ratePercent,
            'fixed_fee_paise' => $fixedFeePaise,
            'calculated_commission_paise' => $calculatedCommissionPaise,
            'vendor_net_paise' => $vendorNetPaise,
        ];
    }

    /**
     * Record idempotent commission charge for an Order (payment callback / verification / cash settlement).
     * Never duplicates on webhook replay / retries because of unique (transaction_type, transaction_id, event_type).
     */
    public function recordOrderCommission(Order $order, ?array $transactionOverride = null, ?int $adminId = null): OrderCommission
    {
        return DB::transaction(function () use ($order, $transactionOverride, $adminId) {
            $existing = OrderCommission::where('transaction_type', 'order')
                ->where('transaction_id', $order->id)
                ->where('event_type', 'charge')
                ->first();

            if ($existing && $transactionOverride === null) {
                return $existing;
            }

            $calc = $this->calculateBreakdownForOrder($order, $transactionOverride);

            $payload = [
                'order_number' => $order->order_number,
                'vendor_id' => $order->vendor_id,
                'sector_key' => $calc['sector_key'],
                'subscription_id' => $calc['subscription']?->id,
                'plan_id' => $calc['subscription']?->plan_id,
                'plan_version_number' => $calc['subscription']?->plan_version_number,
                'commission_rule_id' => $calc['resolved']['rule_id'],
                'commission_rule_version' => $calc['resolved']['rule_version'],
                'rule_source' => $calc['resolved']['source'],
                'commission_base_paise' => $calc['commission_base_paise'],
                'tax_paise' => $calc['tax_paise'],
                'shipping_paise' => $calc['shipping_paise'],
                'discount_paise' => $calc['discount_paise'],
                'rate_percent' => $calc['rate_percent'],
                'fixed_fee_paise' => $calc['fixed_fee_paise'],
                'calculated_commission_paise' => $calc['calculated_commission_paise'],
                'vendor_net_paise' => $calc['vendor_net_paise'],
                'refund_state' => 'none',
                'settlement_status' => $order->settlement_status ?? 'recorded',
                'is_manual_override' => $transactionOverride !== null,
                'override_reason' => $transactionOverride['reason'] ?? null,
                'notes' => $transactionOverride['notes'] ?? null,
                'rule_snapshot_json' => [
                    'rule_name' => $calc['resolved']['name'],
                    'source' => $calc['resolved']['source'],
                    'rate_percent' => $calc['rate_percent'],
                    'fixed_fee_paise' => $calc['fixed_fee_paise'],
                ],
                'calculated_at' => now(),
                'created_by' => $adminId,
            ];

            if ($existing) {
                $old = $existing->only(['rate_percent', 'fixed_fee_paise', 'calculated_commission_paise', 'vendor_net_paise', 'rule_source']);
                $existing->update($payload);
                $record = $existing->fresh();

                AuditLog::create([
                    'user_id' => $adminId,
                    'vendor_id' => $order->vendor_id,
                    'sector_key' => $calc['sector_key'],
                    'auditable_type' => OrderCommission::class,
                    'auditable_id' => $record->id,
                    'action' => 'commission_transaction_overridden',
                    'reason' => $transactionOverride['reason'] ?? 'Manual commission override applied',
                    'old_values' => $old,
                    'new_values' => $record->only(array_keys($old)),
                ]);
            } else {
                $record = OrderCommission::create(array_merge([
                    'transaction_type' => 'order',
                    'transaction_id' => $order->id,
                    'event_type' => 'charge',
                ], $payload));
            }

            // Sync vendor_payout_amount if order is not yet paid out to vendor UPI
            if ($order->vendor_payout_status !== 'paid') {
                $order->forceFill(['vendor_payout_amount' => $calc['vendor_net_paise']])->saveQuietly();
            }

            return $record;
        });
    }

    /**
     * Record a reversal ledger entry when a paid order is cancelled or refunded (idempotent).
     */
    public function reverseOrderCommission(Order $order, ?string $reason = null, ?int $adminId = null): ?OrderCommission
    {
        return DB::transaction(function () use ($order, $reason, $adminId) {
            $charge = OrderCommission::where('transaction_type', 'order')
                ->where('transaction_id', $order->id)
                ->where('event_type', 'charge')
                ->first();

            if (!$charge) {
                return null;
            }

            $existingReversal = OrderCommission::where('transaction_type', 'order')
                ->where('transaction_id', $order->id)
                ->where('event_type', 'reversal')
                ->first();

            if ($existingReversal) {
                return $existingReversal;
            }

            $charge->update(['refund_state' => 'full']);

            return OrderCommission::create([
                'transaction_type' => 'order',
                'transaction_id' => $order->id,
                'order_number' => $order->order_number,
                'vendor_id' => $order->vendor_id,
                'sector_key' => $charge->sector_key,
                'subscription_id' => $charge->subscription_id,
                'plan_id' => $charge->plan_id,
                'plan_version_number' => $charge->plan_version_number,
                'commission_rule_id' => $charge->commission_rule_id,
                'commission_rule_version' => $charge->commission_rule_version,
                'rule_source' => $charge->rule_source,
                'commission_base_paise' => -$charge->commission_base_paise,
                'tax_paise' => -$charge->tax_paise,
                'shipping_paise' => -$charge->shipping_paise,
                'discount_paise' => -$charge->discount_paise,
                'rate_percent' => $charge->rate_percent,
                'fixed_fee_paise' => -$charge->fixed_fee_paise,
                'calculated_commission_paise' => -$charge->calculated_commission_paise,
                'vendor_net_paise' => -$charge->vendor_net_paise,
                'event_type' => 'reversal',
                'refund_state' => 'full',
                'settlement_status' => 'reversed',
                'notes' => $reason ?: 'Commission reversed due to order cancellation/refund',
                'rule_snapshot_json' => $charge->rule_snapshot_json,
                'calculated_at' => now(),
                'created_by' => $adminId,
            ]);
        });
    }

    public function calculateAmounts(int $commissionBasePaise, float $ratePercent, int $fixedFeePaise = 0): array
    {
        $base = max(0, $commissionBasePaise);
        $percentageFee = (int) round($base * ($ratePercent / 100));
        $commission = min($base, max(0, $percentageFee + ($base > 0 ? $fixedFeePaise : 0)));
        $vendorNet = max(0, $base - $commission);

        return [
            'commission_base_paise' => $base,
            'rate_percent' => round($ratePercent, 2),
            'fixed_fee_paise' => $fixedFeePaise,
            'calculated_commission_paise' => $commission,
            'vendor_net_paise' => $vendorNet,
        ];
    }

    public function overrideTransactionCommission(
        OrderCommission $record,
        float $ratePercent,
        int $fixedFeePaise,
        string $reason,
        ?int $adminId = null,
        ?string $ip = null
    ): OrderCommission {
        return DB::transaction(function () use ($record, $ratePercent, $fixedFeePaise, $reason, $adminId, $ip) {
            $old = $record->only(['rate_percent', 'fixed_fee_paise', 'calculated_commission_paise', 'vendor_net_paise', 'rule_source']);
            $calc = $this->calculateAmounts((int) $record->commission_base_paise, $ratePercent, $fixedFeePaise);

            $record->update([
                'rate_percent' => $calc['rate_percent'],
                'fixed_fee_paise' => $calc['fixed_fee_paise'],
                'calculated_commission_paise' => $calc['calculated_commission_paise'],
                'vendor_net_paise' => $calc['vendor_net_paise'],
                'rule_source' => 'transaction_override',
                'is_manual_override' => true,
                'override_reason' => $reason,
                'rule_snapshot_json' => array_merge($record->rule_snapshot_json ?? [], [
                    'source' => 'transaction_override',
                    'rate_percent' => $calc['rate_percent'],
                    'fixed_fee_paise' => $calc['fixed_fee_paise'],
                    'override_reason' => $reason,
                ]),
            ]);

            if ($record->transaction_type === 'order' && $record->transaction_id) {
                $order = Order::find($record->transaction_id);
                if ($order && $order->vendor_payout_status !== 'paid') {
                    $order->forceFill(['vendor_payout_amount' => $calc['vendor_net_paise']])->saveQuietly();
                }
            }

            AuditLog::create([
                'user_id' => $adminId,
                'vendor_id' => $record->vendor_id,
                'sector_key' => $record->sector_key,
                'auditable_type' => OrderCommission::class,
                'auditable_id' => $record->id,
                'action' => 'commission_transaction_overridden',
                'reason' => $reason,
                'old_values' => $old,
                'new_values' => $record->fresh()->only(array_keys($old)),
                'ip_address' => $ip,
            ]);

            return $record->fresh();
        });
    }

    protected function formatRuleResult(CommissionRule $rule, string $source): array
    {
        return [
            'rule' => $rule,
            'rule_id' => $rule->id,
            'rule_version' => (int) ($rule->version ?? 1),
            'source' => $source,
            'rate_percent' => round((float) $rule->rate_percent, 2),
            'fixed_fee_paise' => (int) $rule->fixed_fee_paise,
            'name' => $rule->name,
        ];
    }
}
