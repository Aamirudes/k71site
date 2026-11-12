<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotificationCenterService
{
    /**
     * Dispatch a standardized, deduplication-aware database notification to a user.
     */
    public function notifyUser(
        ?int $userId,
        string $icon,
        string $title,
        string $message,
        string $url = '/',
        string $category = 'general',
        ?string $dedupeKey = null
    ): bool {
        if (!$userId || $userId <= 0 || !Schema::hasTable('notifications')) {
            return false;
        }

        try {
            if ($dedupeKey !== null && $dedupeKey !== '') {
                $recentExists = DB::table('notifications')
                    ->where('notifiable_type', User::class)
                    ->where('notifiable_id', $userId)
                    ->whereNull('read_at')
                    ->where('created_at', '>=', now()->subHours(12))
                    ->where('data', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $dedupeKey) . '%')
                    ->exists();

                if ($recentExists) {
                    return false;
                }
            }

            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\MarketplacePlatformNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'icon' => $icon,
                    'category' => $category,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                    'dedupe_key' => $dedupeKey,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }
}

