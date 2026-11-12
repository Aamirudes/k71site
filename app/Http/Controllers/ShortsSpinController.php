<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Setting;
use App\Models\SpinPrize;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ShortsSpinController extends Controller
{
    /**
     * Get unique cache key for spin cooldown by user ID or IP hash.
     */
    protected function getSpinKey(Request $request): string
    {
        $userId = auth()->id();
        if ($userId) {
            return "zsu_spin_user_{$userId}";
        }
        $ipHash = md5($request->ip() ?: '127.0.0.1');
        return "zsu_spin_ip_{$ipHash}";
    }

    /**
     * Check if visitor has spins remaining.
     */
    public function check(Request $request): JsonResponse
    {
        $key = $this->getSpinKey($request);
        $lastSpin = Cache::get($key);

        $cooldownHours = (int) (Setting::get('zsu_spin_cooldown_hours', 24));
        $cooldownSeconds = $cooldownHours * 3600;

        if ($lastSpin && (time() - $lastSpin) < $cooldownSeconds) {
            $remaining = ceil(($cooldownSeconds - (time() - $lastSpin)) / 3600);
            return response()->json([
                'success' => true,
                'can_spin' => false,
                'remaining_hours' => max(1, (int) $remaining),
                'message' => "You have already spun today. Next spin in {$remaining}h.",
            ]);
        }

        return response()->json([
            'success' => true,
            'can_spin' => true,
            'remaining_hours' => 0,
            'message' => '1 spin remaining today!',
        ]);
    }

    /**
     * Execute spin and award a real coupon prize (Decision D8).
     */
    public function result(Request $request): JsonResponse
    {
        $key = $this->getSpinKey($request);
        $lastSpin = Cache::get($key);

        $cooldownHours = (int) (Setting::get('zsu_spin_cooldown_hours', 24));
        $cooldownSeconds = $cooldownHours * 3600;

        if ($lastSpin && (time() - $lastSpin) < $cooldownSeconds) {
            $remaining = ceil(($cooldownSeconds - (time() - $lastSpin)) / 3600);
            return response()->json([
                'success' => false,
                'can_spin' => false,
                'message' => "Spin cooldown active. Try again in {$remaining}h.",
            ], 429);
        }

        $now = now();
        $visibility = app(\App\Services\Monetization\VisibilityService::class);

        $isValidCoupon = static function (?Coupon $coupon) use ($now, $visibility): bool {
            if (!$coupon || !$coupon->is_active) {
                return false;
            }
            if ($coupon->starts_at && $coupon->starts_at->gt($now)) {
                return false;
            }
            if ($coupon->expires_at && $coupon->expires_at->lt($now)) {
                return false;
            }
            if ($coupon->total_limit !== null && (int) $coupon->usage_count >= (int) $coupon->total_limit) {
                return false;
            }
            if ($coupon->vendor_id) {
                $vendor = $coupon->vendor;
                if (!$vendor || !$visibility->isVendorPubliclyVisible($vendor)) {
                    return false;
                }
            }
            return true;
        };

        // Fetch active spin prizes with valid, un-expired, within-limit coupons
        $prizes = SpinPrize::with(['coupon.vendor'])->where('is_active', true)->get();

        if ($prizes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active prizes currently available.',
            ], 404);
        }

        // Pre-fetch fallback pool of real currently valid coupons in case a prize lacks a valid linked coupon
        $fallbackCoupon = Coupon::with('vendor')
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now))
            ->where(fn ($q) => $q->whereNull('total_limit')->orWhereColumn('usage_count', '<', 'total_limit'))
            ->get()
            ->first(fn (Coupon $c) => $isValidCoupon($c));

        $eligiblePrizes = $prizes->filter(fn (SpinPrize $p) => $isValidCoupon($p->coupon));
        $candidatePool = $eligiblePrizes->isNotEmpty()
            ? $eligiblePrizes
            : ($fallbackCoupon ? $prizes : collect());

        if ($candidatePool->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid coupon rewards are currently available.',
            ], 404);
        }

        // Weighted selection based on prize probability
        $pool = [];
        foreach ($candidatePool as $prize) {
            $weight = max(1, (int) $prize->probability);
            for ($i = 0; $i < $weight; $i++) {
                $pool[] = $prize;
            }
        }

        /** @var SpinPrize $selectedPrize */
        $selectedPrize = $pool[array_rand($pool)];

        // Enforce Decision D8: Must resolve to a verified valid coupon (never an expired or fake code)
        $resolvedCoupon = $isValidCoupon($selectedPrize->coupon) ? $selectedPrize->coupon : $fallbackCoupon;
        if (!$resolvedCoupon) {
            return response()->json([
                'success' => false,
                'message' => 'No valid coupon rewards are currently available.',
            ], 404);
        }
        $couponCode = $resolvedCoupon->code;

        // Record cooldown in Cache only after a real valid coupon reward is selected
        Cache::put($key, time(), $cooldownSeconds);

        return response()->json([
            'success' => true,
            'won' => true,
            'prize_id' => $selectedPrize->id,
            'label' => $selectedPrize->label,
            'color' => $selectedPrize->color,
            'type' => $selectedPrize->type,
            'value' => $couponCode,
            'coupon_code' => $couponCode,
            'message' => "Congratulations! You won {$selectedPrize->label}! Use coupon code {$couponCode} at checkout.",
        ]);
    }
}
