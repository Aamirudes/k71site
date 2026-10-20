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

        // Fetch active spin prizes with associated coupons
        $prizes = SpinPrize::with('coupon')->where('is_active', true)->get();

        if ($prizes->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No active prizes currently available.',
            ], 404);
        }

        // Weighted selection based on prize probability
        $pool = [];
        foreach ($prizes as $prize) {
            $weight = max(1, $prize->probability);
            for ($i = 0; $i < $weight; $i++) {
                $pool[] = $prize;
            }
        }

        /** @var SpinPrize $selectedPrize */
        $selectedPrize = $pool[array_rand($pool)];

        // Enforce Decision D8: Must map to an active real coupon
        $couponCode = $selectedPrize->value;
        if ($selectedPrize->coupon) {
            $couponCode = $selectedPrize->coupon->code;
        } else {
            // Verify or fallback to real coupon from database
            $realCoupon = Coupon::where('is_active', true)->first();
            if ($realCoupon) {
                $couponCode = $realCoupon->code;
            }
        }

        // Record cooldown in Cache
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
