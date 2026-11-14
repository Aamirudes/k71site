<?php

namespace App\Services\Used;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use App\Models\UsedSellerProfile;
use App\Models\UsedThread;
use App\Models\UsedThreadMessage;
use App\Models\UsedTransaction;
use App\Models\User;
use App\Models\Wishlist;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UsedMarketplaceService
{
    public function normalizeConditionGrade(?string $input): string
    {
        $conditions = config('used_marketplace.conditions', []);
        $raw = strtolower(trim((string) $input));

        if ($raw !== '' && isset($conditions[$raw])) {
            return $raw;
        }

        foreach ($conditions as $key => $meta) {
            foreach ($meta['aliases'] ?? [] as $alias) {
                if ($raw === $alias || str_contains($raw, $alias)) {
                    return $key;
                }
            }
        }

        return 'good';
    }

    public function conditionLabel(string $grade): string
    {
        return config("used_marketplace.conditions.{$grade}.short", 'Good');
    }

    public function detectProhibitedContent(string ...$texts): ?string
    {
        $combined = strtolower(implode(' ', $texts));
        foreach (config('used_marketplace.prohibited_keywords', []) as $keyword) {
            $pattern = '/(?<![a-z0-9])' . preg_quote(strtolower($keyword), '/') . '(?![a-z0-9])/i';
            if (preg_match($pattern, $combined)) {
                return $keyword;
            }
        }

        return null;
    }

    public function computeDuplicateFingerprint(int $userId, int $categoryId, string $title): string
    {
        $normalizedTitle = preg_replace('/\s+/', ' ', strtolower(trim($title)));
        return hash('sha256', "{$userId}|{$categoryId}|{$normalizedTitle}");
    }

    public function findDuplicateActiveListing(int $userId, int $categoryId, string $title, ?int $excludeProductId = null): ?Product
    {
        $fingerprint = $this->computeDuplicateFingerprint($userId, $categoryId, $title);

        $query = Product::withoutGlobalScopes()
            ->where('vertical', 'used')
            ->where('duplicate_fingerprint', $fingerprint)
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhereIn('used_status', ['active', 'reserved']);
            });

        if ($excludeProductId) {
            $query->where('id', '!=', $excludeProductId);
        }

        return $query->first();
    }

    public function getOrCreateSellerProfile(User $user): UsedSellerProfile
    {
        $existing = UsedSellerProfile::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        $displayName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($displayName === '') {
            $displayName = $user->username ?: 'K71 Member';
        }

        $baseHandle = Str::slug($user->username ?: $displayName) ?: 'seller';
        $baseHandle = substr($baseHandle, 0, 45);
        $handle = $baseHandle;
        $counter = 1;

        while (UsedSellerProfile::where('handle', $handle)->exists()) {
            $handle = substr($baseHandle, 0, 40) . '-' . $counter;
            $counter++;
        }

        return UsedSellerProfile::create([
            'user_id' => $user->id,
            'handle' => $handle,
            'display_name' => $displayName,
            'city' => $user->vendor?->city,
            'preferred_contact_methods' => ['k71_chat'],
            'preferred_handoff_methods' => ['local_pickup', 'public_meetup'],
            'show_sold_history' => true,
            'show_phone_publicly' => false,
        ]);
    }

    /**
     * Store uploaded photo files genuinely; never fabricate stock placeholders.
     */
    public function uploadListingPhotos(Product $product, array $files, ?int $coverIndex = null): int
    {
        $maxPhotos = (int) config('used_marketplace.max_photos_per_listing', 10);
        $existingCount = $product->images()->count();
        $slotsRemaining = max(0, $maxPhotos - $existingCount);
        $uploaded = 0;
        $createdImages = [];

        foreach ($files as $idx => $file) {
            if ($uploaded >= $slotsRemaining) {
                break;
            }
            if (!$file instanceof UploadedFile || !$file->isValid()) {
                continue;
            }

            $storedPath = $file->store('products/used', 'public');
            k71_sync_public_storage($storedPath);

            $createdImages[] = [
                'input_idx' => $idx,
                'model' => ProductImage::create([
                    'product_id' => $product->id,
                    'path' => $storedPath,
                    'alt_text' => $product->title,
                    'sort_order' => $existingCount + $uploaded,
                ]),
            ];
            $uploaded++;
        }

        if ($coverIndex !== null && !empty($createdImages) && $existingCount === 0) {
            foreach ($createdImages as $entry) {
                if ((int) $entry['input_idx'] === (int) $coverIndex) {
                    $this->setCoverImage($product, $entry['model']->id);
                    break;
                }
            }
        }

        return $uploaded;
    }

    public function setCoverImage(Product $product, int $imageId): void
    {
        $images = $product->images()->orderBy('sort_order')->get();
        $target = $images->firstWhere('id', $imageId);
        if (!$target) {
            return;
        }

        $target->update(['sort_order' => 0]);
        $order = 1;
        foreach ($images as $img) {
            if ($img->id === $target->id) {
                continue;
            }
            $img->update(['sort_order' => $order++]);
        }
    }

    public function pruneAndReorderPhotos(Product $product, array $removeIds = [], array $orderedIds = [], ?int $coverImageId = null): void
    {
        if (!empty($removeIds)) {
            $toDelete = $product->images()->whereIn('id', $removeIds)->get();
            foreach ($toDelete as $img) {
                if ($img->path && !str_starts_with($img->path, 'http')) {
                    k71_delete_public_storage($img->path);
                }
                $img->delete();
            }
        }

        if (!empty($orderedIds)) {
            $order = 0;
            foreach ($orderedIds as $imgId) {
                $img = $product->images()->where('id', (int) $imgId)->first();
                if ($img) {
                    $img->update(['sort_order' => $order++]);
                }
            }
        }

        if ($coverImageId) {
            $this->setCoverImage($product, $coverImageId);
        }

        // Compact remaining sort orders cleanly
        $remaining = $product->images()->orderBy('sort_order')->orderBy('id')->get();
        foreach ($remaining as $idx => $img) {
            if ((int) $img->sort_order !== $idx) {
                $img->update(['sort_order' => $idx]);
            }
        }
    }

    public function recordListingView(Product $product, Request $request): void
    {
        if ($product->isDraft() || $product->isPaused()) {
            return;
        }

        $viewer = auth()->user();
        if ($viewer && $product->isOwnedBy($viewer)) {
            return;
        }

        $sessionKey = 'used_viewed_' . $product->id;
        if (!$request->session()->has($sessionKey)) {
            $request->session()->put($sessionKey, time());
            Product::withoutGlobalScopes()
                ->where('id', $product->id)
                ->update([
                    'views_count' => DB::raw('views_count + 1'),
                    'view_count' => DB::raw('view_count + 1'),
                ]);
        }

        $recent = collect($request->session()->get('used_recently_viewed', []))
            ->reject(fn($id) => (int) $id === (int) $product->id)
            ->prepend($product->id)
            ->take(10)
            ->values()
            ->all();

        $request->session()->put('used_recently_viewed', $recent);
    }

    public function syncSavesCount(int $productId): int
    {
        $count = Wishlist::where('product_id', $productId)->count();
        Product::withoutGlobalScopes()
            ->where('id', $productId)
            ->update(['saves_count' => $count]);

        return $count;
    }

    public function startOrSendEnquiry(User $buyer, Product $product, string $body, string $messageType = 'text'): UsedThread
    {
        $sellerId = $product->ownerUserId();
        if (!$sellerId) {
            throw new \RuntimeException('Listing seller account could not be resolved.');
        }

        $thread = UsedThread::firstOrCreate(
            [
                'product_id' => $product->id,
                'buyer_id' => $buyer->id,
            ],
            [
                'seller_id' => $sellerId,
                'status' => $product->isSold() ? 'locked' : 'open',
                'last_message_at' => now(),
            ]
        );

        UsedThreadMessage::create([
            'used_thread_id' => $thread->id,
            'sender_id' => $buyer->id,
            'message_type' => $messageType,
            'body' => trim($body),
            'is_read' => false,
        ]);

        $thread->update(['last_message_at' => now()]);

        $this->mirrorMessageToChat($buyer->id, $sellerId, $buyer->id, "[{$product->title}] " . trim($body), $thread, $product);

        $this->notifyUser(
            $sellerId,
            '💬',
            'New Used Item Enquiry',
            "{$buyer->name} asked about \"{$product->title}\": " . Str::limit(trim($body), 80),
            route('used.threads.show', $thread)
        );

        return $thread;
    }

    public function appendThreadMessage(UsedThread $thread, User $sender, string $body, string $messageType = 'text'): UsedThreadMessage
    {
        $msg = UsedThreadMessage::create([
            'used_thread_id' => $thread->id,
            'sender_id' => $sender->id,
            'message_type' => $messageType,
            'body' => trim($body),
            'is_read' => false,
        ]);

        $thread->update(['last_message_at' => now()]);

        $recipientId = (int) $sender->id === (int) $thread->buyer_id ? $thread->seller_id : $thread->buyer_id;
        $product = $thread->product;
        $prefix = $product ? "[{$product->title}] " : '';
        $this->mirrorMessageToChat($thread->buyer_id, $thread->seller_id, $sender->id, $prefix . trim($body), $thread, $product);

        if ($recipientId) {
            $this->notifyUser(
                $recipientId,
                '💬',
                'New Message on Used Listing',
                "{$sender->name}: " . Str::limit(trim($body), 80),
                route('used.threads.show', $thread)
            );
        }

        return $msg;
    }

    private function mirrorMessageToChat(int $buyerId, int $sellerId, int $senderId, string $body, UsedThread $thread, ?Product $product): void
    {
        if ($buyerId === $sellerId) {
            return;
        }

        try {
            MessageController::ensureChatSchema();
            $u1 = min($buyerId, $sellerId);
            $u2 = max($buyerId, $sellerId);
            $nowStr = now()->toDateTimeString();

            $chatConv = DB::table('chat_conversations')
                ->where('user_one_id', $u1)
                ->where('user_two_id', $u2)
                ->first();

            if (!$chatConv) {
                $chatConvId = DB::table('chat_conversations')->insertGetId([
                    'user_one_id' => $u1,
                    'user_two_id' => $u2,
                    'last_message_at' => $nowStr,
                    'status' => 'accepted',
                    'requested_by' => $senderId,
                ]);
            } else {
                $chatConvId = $chatConv->id;
                DB::table('chat_conversations')->where('id', $chatConvId)->update([
                    'last_message_at' => $nowStr,
                    'status' => 'accepted',
                ]);
            }

            if (!$thread->chat_conversation_id) {
                $thread->update(['chat_conversation_id' => $chatConvId]);
            }

            DB::table('chat_messages')->insert([
                'conversation_id' => $chatConvId,
                'sender_id' => $senderId,
                'body' => $body,
                'is_read' => 0,
                'created_at' => $nowStr,
            ]);

            if ($product && $product->vendor_id) {
                $conv = Conversation::firstOrCreate(
                    [
                        'vendor_id' => $product->vendor_id,
                        'user_id' => $buyerId,
                        'product_id' => $product->id,
                    ],
                    [
                        'subject' => 'Enquiry: ' . $product->title,
                        'last_message_at' => now(),
                    ]
                );

                Message::create([
                    'conversation_id' => $conv->id,
                    'sender_type' => $senderId === $sellerId ? 'vendor' : 'user',
                    'sender_id' => $senderId === $sellerId ? $product->vendor_id : $buyerId,
                    'body' => $body,
                    'is_read' => false,
                ]);

                $conv->update(['last_message_at' => now()]);
            }
        } catch (\Throwable $e) {
            // Do not break Used thread if optional mirror fails
        }
    }

    public function notifyUser(int $userId, string $icon, string $title, string $message, string $url = '/used'): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        try {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\UsedMarketplaceNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $userId,
                'data' => json_encode([
                    'icon' => $icon,
                    'title' => $title,
                    'message' => $message,
                    'url' => $url,
                ]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Non-fatal
        }
    }

    public function recalculateSellerStats(int $userId): void
    {
        $user = User::with('vendor')->find($userId);
        if (!$user) {
            return;
        }

        $profile = UsedSellerProfile::where('user_id', $userId)->first()
            ?: $this->getOrCreateSellerProfile($user);

        $reviewsCount = Review::where('reviewed_user_id', $userId)
            ->where('is_approved', true)
            ->count();

        $avgRating = $reviewsCount > 0
            ? round((float) Review::where('reviewed_user_id', $userId)->where('is_approved', true)->avg('rating'), 2)
            : null;

        $soldCompletedTx = UsedTransaction::where('seller_id', $userId)
            ->where('status', 'completed')
            ->count();

        $userVendorId = $user->vendor?->id;
        $soldListingsCount = Product::withoutGlobalScopes()
            ->where('vertical', 'used')
            ->where(function ($q) use ($userId, $userVendorId) {
                $q->where('user_id', $userId);
                if ($userVendorId) {
                    $q->orWhere('vendor_id', $userVendorId);
                }
            })
            ->where('is_sold', true)
            ->count();

        $profile->update([
            'reviews_count' => $reviewsCount,
            'avg_rating' => $avgRating,
            'sold_count' => max($soldCompletedTx, $soldListingsCount),
        ]);
    }

    /**
     * Sweep stale unrenewed classified ads older than given days into 'expired'.
     */
    public function sweepExpiredListings(int $days = 60): int
    {
        $cutoff = now()->subDays($days);
        $expiredCount = 0;

        $staleListings = Product::withoutGlobalScopes()
            ->where('vertical', 'used')
            ->where('status', 'active')
            ->where('is_sold', false)
            ->where(function ($q) {
                $q->whereNull('used_status')->orWhere('used_status', 'active');
            })
            ->where(function ($q) use ($cutoff) {
                $q->where(function ($sq) use ($cutoff) {
                    $sq->whereNotNull('refreshed_at')->where('refreshed_at', '<', $cutoff);
                })->orWhere(function ($sq) use ($cutoff) {
                    $sq->whereNull('refreshed_at')->where('created_at', '<', $cutoff);
                });
            })
            ->get();

        foreach ($staleListings as $listing) {
            $listing->update(['used_status' => 'expired']);
            $expiredCount++;
        }

        if ($expiredCount > 0) {
            Cache::forget('sitemap_products_xml');
        }

        return $expiredCount;
    }
}

