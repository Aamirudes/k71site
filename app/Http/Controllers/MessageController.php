<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MessageController extends Controller
{
    /**
     * Customer messages inbox.
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (!auth()->check()) {
            return redirect()->guest(route('login'))->with('error', 'Please log in to access your direct messages.');
        }

        $user = auth()->user();
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username ?? ($user->first_name ?? 'User');
            @setcookie('k71_uid', (string)$user->id, time() + 86400 * 30, '/', '', false, false);

            self::ensureChatSchema();
            self::syncLegacyChats($user->id);
        }

        $targetUserId = null;
        if ($request->filled('user')) {
            $targetUserId = (int)$request->input('user');
        } elseif ($request->filled('chat_with')) {
            $targetUserId = (int)$request->input('chat_with');
        } elseif ($request->filled('vendor')) {
            $vendor = \App\Models\Vendor::find($request->input('vendor'));
            if ($vendor && $vendor->user_id) {
                $targetUserId = (int)$vendor->user_id;
            }
        }

        return view('messages.index', compact('user', 'targetUserId'));
    }

    /**
     * Customer view conversation thread.
     */
    public function show(Conversation $conversation): View
    {
        if ($conversation->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        // Mark vendor messages as read
        $conversation->messages()
            ->where('sender_type', 'vendor')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->load(['vendor', 'product', 'messages']);

        return view('messages.show', compact('conversation'));
    }

    /**
     * Customer initiate a new conversation or send initial message.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'product_id' => 'nullable|exists:products,id',
            'subject' => 'nullable|string|max:200',
            'body' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        // Check if conversation already exists for user and vendor (and optionally product)
        $conversationQuery = Conversation::where('user_id', $user->id)
            ->where('vendor_id', $validated['vendor_id']);

        if (!empty($validated['product_id'])) {
            $conversationQuery->where('product_id', $validated['product_id']);
        }

        $conversation = $conversationQuery->first();

        if (!$conversation) {
            $subject = $validated['subject'] ?? null;
            if (!$subject && !empty($validated['product_id'])) {
                $product = Product::find($validated['product_id']);
                $subject = 'Inquiry about: ' . ($product ? $product->title : 'Product');
            }

            $conversation = Conversation::create([
                'vendor_id' => $validated['vendor_id'],
                'user_id' => $user->id,
                'product_id' => $validated['product_id'] ?? null,
                'subject' => $subject ?: 'Direct Inquiry',
                'last_message_at' => now(),
            ]);
        }

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Seamless Mirror to Instagram Direct DM Chat Engine
        try {
            $vendor = Vendor::find($validated['vendor_id']);
            if ($vendor && $vendor->user_id) {
                self::ensureChatSchema();
                $u1 = min($user->id, (int)$vendor->user_id);
                $u2 = max($user->id, (int)$vendor->user_id);
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
                        'requested_by' => $user->id,
                    ]);
                } else {
                    $chatConvId = $chatConv->id;
                    DB::table('chat_conversations')->where('id', $chatConvId)->update(['last_message_at' => $nowStr]);
                }

                DB::table('chat_messages')->insert([
                    'conversation_id' => $chatConvId,
                    'sender_id' => $user->id,
                    'body' => $validated['body'],
                    'is_read' => 0,
                    'created_at' => $nowStr,
                ]);

                return redirect()->route('messages.index', ['chat_with' => $vendor->user_id])
                    ->with('success', 'Message sent successfully.');
            }
        } catch (\Throwable $e) {}

        return redirect()->route('messages.show', $conversation->id)
            ->with('success', 'Message sent successfully.');
    }

    /**
     * Customer reply to conversation.
     */
    public function reply(Request $request, Conversation $conversation): RedirectResponse
    {
        if ($conversation->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'user',
            'sender_id' => auth()->id(),
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Mirror reply to chat_messages
        try {
            $vendor = $conversation->vendor;
            if ($vendor && $vendor->user_id) {
                self::ensureChatSchema();
                $u1 = min(auth()->id(), (int)$vendor->user_id);
                $u2 = max(auth()->id(), (int)$vendor->user_id);
                $nowStr = now()->toDateTimeString();

                $chatConv = DB::table('chat_conversations')
                    ->where('user_one_id', $u1)
                    ->where('user_two_id', $u2)
                    ->first();

                if ($chatConv) {
                    DB::table('chat_conversations')->where('id', $chatConv->id)->update(['last_message_at' => $nowStr]);
                    DB::table('chat_messages')->insert([
                        'conversation_id' => $chatConv->id,
                        'sender_id' => auth()->id(),
                        'body' => $validated['body'],
                        'is_read' => 0,
                        'created_at' => $nowStr,
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        return back()->with('success', 'Reply sent successfully.');
    }

    /**
     * Vendor inbox.
     */
    public function vendorIndex(Request $request): View
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor) {
            abort(403, 'Vendor account required.');
        }

        $conversations = $vendor->conversations()
            ->with(['user', 'latestMessage', 'product'])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('vendor.messages.index', compact('conversations', 'vendor'));
    }

    /**
     * Vendor view conversation thread.
     */
    public function vendorShow(Request $request, Conversation $conversation): View
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor || $conversation->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        // Mark user messages as read
        $conversation->messages()
            ->where('sender_type', 'user')
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->load(['user', 'product', 'messages']);

        return view('vendor.messages.show', compact('conversation', 'vendor'));
    }

    /**
     * Vendor reply to conversation.
     */
    public function vendorReply(Request $request, Conversation $conversation): RedirectResponse
    {
        $vendor = $request->attributes->get('current_vendor') ?? auth()->user()?->vendor;
        if (!$vendor || $conversation->vendor_id !== $vendor->id) {
            abort(403, 'Unauthorized access to this conversation.');
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'vendor',
            'sender_id' => $vendor->id,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        // Mirror vendor reply to chat_messages
        try {
            if ($vendor->user_id && $conversation->user_id) {
                self::ensureChatSchema();
                $u1 = min((int)$conversation->user_id, (int)$vendor->user_id);
                $u2 = max((int)$conversation->user_id, (int)$vendor->user_id);
                $nowStr = now()->toDateTimeString();

                $chatConv = DB::table('chat_conversations')
                    ->where('user_one_id', $u1)
                    ->where('user_two_id', $u2)
                    ->first();

                if ($chatConv) {
                    DB::table('chat_conversations')->where('id', $chatConv->id)->update(['last_message_at' => $nowStr]);
                    DB::table('chat_messages')->insert([
                        'conversation_id' => $chatConv->id,
                        'sender_id' => (int)$vendor->user_id,
                        'body' => $validated['body'],
                        'is_read' => 0,
                        'created_at' => $nowStr,
                    ]);
                }
            }
        } catch (\Throwable $e) {}

        return back()->with('success', 'Reply sent successfully.');
    }

    /* -------------------------------------------------------------------------
     * INSTAGRAM DIRECT REAL-TIME CHAT API (NATIVE LARAVEL ROUTES)
     * ------------------------------------------------------------------------- */

    /**
     * Resolve the active user ID from Auth session, PHP session, or secure cookie.
     */
    private function resolveChatUserId(): ?int
    {
        $uid = null;
        try {
            if (auth()->check()) {
                $uid = (int) auth()->id();
            }
        } catch (\Throwable $e) {
            // Session guard not initialized in standalone script context
        }

        if (!$uid && !empty($_SESSION['user_id'])) {
            $uid = (int) $_SESSION['user_id'];
        } elseif (!$uid && !empty($_COOKIE['k71_uid']) && is_numeric($_COOKIE['k71_uid'])) {
            $uid = (int) $_COOKIE['k71_uid'];
        }

        if ($uid > 0) {
            // Heartbeat online activity (cached for 5 min, throttled DB write)
            \Illuminate\Support\Facades\Cache::put("user_last_seen_{$uid}", time(), 300);
            $cacheDbKey = "user_db_seen_{$uid}";
            if (!\Illuminate\Support\Facades\Cache::has($cacheDbKey)) {
                try {
                    \Illuminate\Support\Facades\DB::table('users')
                        ->where('id', $uid)
                        ->update(['last_seen_at' => now()]);
                    \Illuminate\Support\Facades\Cache::put($cacheDbKey, 1, 60);
                } catch (\Throwable $e) {}
            }
        }

        return $uid;
    }

    private function formatChatAvatar(?string $avatar, string $name): string
    {
        if (!empty($avatar)) {
            if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://') || str_starts_with($avatar, '/')) {
                return $avatar;
            }
            return '/storage/' . ltrim($avatar, '/');
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0095F6&color=ffffff&size=200&bold=true&format=svg';
    }

    private function formatChatTimeAgo(?string $datetime): string
    {
        if (!$datetime) return 'now';
        $time = strtotime($datetime);
        if (!$time) return 'now';
        $diff = time() - $time;
        if ($diff < 60) return 'now';
        if ($diff < 3600) return floor($diff / 60) . 'm';
        if ($diff < 86400) return floor($diff / 3600) . 'h';
        if ($diff < 604800) return floor($diff / 86400) . 'd';
        return floor($diff / 604800) . 'w';
    }

    private function getChatUserInfo(int $userId): ?array
    {
        $u = \App\Models\User::with(['vendor', 'roles'])->find($userId);
        if (!$u) return null;

        $personName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
        if ($personName === '') {
            $personName = $u->username ?? ('User #' . $u->id);
        }

        $vendor = $u->vendor;
        $displayName = $personName;
        if ($vendor && !empty($vendor->shop_name)) {
            $displayName = $vendor->shop_name . ' (' . $personName . ')';
        }

        $lastSeenTime = !empty($u->last_seen_at) ? strtotime($u->last_seen_at) : (int)\Illuminate\Support\Facades\Cache::get("user_last_seen_{$u->id}", 0);
        $isOnline = ($lastSeenTime > (time() - 180));

        $isVendor = ($vendor && in_array($vendor->status, ['approved', 'active']));
        $storeUrl = $isVendor ? $vendor->storefront_url : null;
        $subdomain = $isVendor ? "{$vendor->slug}." . config('marketplace.domain') : null;

        $logoUrl = null;
        if ($vendor?->logo) {
            $logoUrl = str_starts_with($vendor->logo, 'http') ? $vendor->logo : \Illuminate\Support\Facades\Storage::url($vendor->logo);
        }
        $bannerUrl = null;
        if ($vendor?->banner) {
            $bannerUrl = str_starts_with($vendor->banner, 'http') ? $vendor->banner : \Illuminate\Support\Facades\Storage::url($vendor->banner);
        }

        return [
            'id' => (int) $u->id,
            'username' => $u->username ?? ('user_' . $u->id),
            'name' => $displayName,
            'raw_name' => $personName,
            'shop_name' => $vendor?->shop_name,
            'profile_pic' => $this->formatChatAvatar($u->avatar, $displayName),
            'email' => $u->email ?? '',
            'is_online' => $isOnline,
            'last_seen_at' => $u->last_seen_at ?? null,
            'status_label' => $isOnline ? 'Active now' : ($lastSeenTime ? 'Active ' . $this->formatChatTimeAgo(date('Y-m-d H:i:s', $lastSeenTime)) . ' ago' : 'Offline'),
            'is_vendor' => $isVendor,
            'vendor_slug' => $vendor?->slug,
            'vendor_city' => $vendor?->city ?? 'India',
            'vendor_state' => $vendor?->state,
            'vendor_rating' => $vendor?->rating ?? 4.9,
            'store_url' => $storeUrl,
            'store_domain' => $subdomain,
            'banner_url' => $bannerUrl,
            'logo_url' => $logoUrl,
            'bio' => $vendor?->description ?? 'Member of K71 Direct Commerce community.',
            'member_since' => $u->created_at ? $u->created_at->format('M Y') : '2026',
            'role_label' => $isVendor ? 'Verified Merchant' : ($u->roles && $u->roles->contains('slug', 'admin') ? 'Staff Admin' : 'K71 Member'),
        ];
    }

    /**
     * Self-healing table check: guarantees all tables & columns exist across SQLite & MySQL.
     */
    public static function ensureChatSchema(): void
    {
        try {
            if (!Schema::hasTable('chat_conversations')) {
                Schema::create('chat_conversations', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('user_one_id');
                    $table->unsignedBigInteger('user_two_id');
                    $table->timestamp('last_message_at')->useCurrent();
                    $table->string('status', 20)->default('accepted');
                    $table->unsignedBigInteger('requested_by')->nullable();
                    $table->unique(['user_one_id', 'user_two_id'], 'idx_conversation_pair');
                    $table->index('user_one_id');
                    $table->index('user_two_id');
                    $table->index('last_message_at');
                });
            } else {
                Schema::table('chat_conversations', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!Schema::hasColumn('chat_conversations', 'status')) {
                        $table->string('status', 20)->default('accepted');
                    }
                    if (!Schema::hasColumn('chat_conversations', 'requested_by')) {
                        $table->unsignedBigInteger('requested_by')->nullable();
                    }
                });
            }

            if (!Schema::hasTable('chat_messages')) {
                Schema::create('chat_messages', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('conversation_id');
                    $table->unsignedBigInteger('sender_id');
                    $table->text('body');
                    $table->string('attachment_url', 500)->nullable();
                    $table->string('attachment_type', 30)->nullable();
                    $table->unsignedBigInteger('reply_to_id')->nullable();
                    $table->text('reply_to_body')->nullable();
                    $table->string('reply_to_sender_name', 100)->nullable();
                    $table->boolean('is_read')->default(false);
                    $table->timestamp('created_at')->useCurrent();
                    $table->index('conversation_id');
                    $table->index('sender_id');
                    $table->index('created_at');
                });
            } else {
                Schema::table('chat_messages', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!Schema::hasColumn('chat_messages', 'attachment_url')) {
                        $table->string('attachment_url', 500)->nullable();
                    }
                    if (!Schema::hasColumn('chat_messages', 'attachment_type')) {
                        $table->string('attachment_type', 30)->nullable();
                    }
                    if (!Schema::hasColumn('chat_messages', 'reply_to_id')) {
                        $table->unsignedBigInteger('reply_to_id')->nullable();
                    }
                    if (!Schema::hasColumn('chat_messages', 'reply_to_body')) {
                        $table->text('reply_to_body')->nullable();
                    }
                    if (!Schema::hasColumn('chat_messages', 'reply_to_sender_name')) {
                        $table->string('reply_to_sender_name', 100)->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Seamless legacy chat synchronization.
     * Brings all chats from 'conversations' & 'messages' into 'chat_conversations' & 'chat_messages'.
     */
    public static function syncLegacyChats(?int $forUserId = null): void
    {
        try {
            if (!Schema::hasTable('conversations') || !Schema::hasTable('messages') || !Schema::hasTable('chat_conversations') || !Schema::hasTable('chat_messages')) {
                return;
            }

            $cacheKey = 'synced_legacy_chats_v2_' . ($forUserId ?: 'all');
            if (Cache::has($cacheKey)) {
                return;
            }
            Cache::put($cacheKey, 1, 180);

            $query = DB::table('conversations as c')
                ->join('vendors as v', 'v.id', '=', 'c.vendor_id')
                ->select('c.id as legacy_conv_id', 'c.user_id as customer_id', 'v.user_id as vendor_user_id', 'c.last_message_at');

            if ($forUserId) {
                $query->where(function($q) use ($forUserId) {
                    $q->where('c.user_id', $forUserId)->orWhere('v.user_id', $forUserId);
                });
            }

            $legacyConvs = $query->get();
            foreach ($legacyConvs as $lc) {
                $cId = (int)$lc->customer_id;
                $vId = (int)$lc->vendor_user_id;
                if (!$cId || !$vId || $cId === $vId) continue;

                $userOne = min($cId, $vId);
                $userTwo = max($cId, $vId);

                $chatConv = DB::table('chat_conversations')
                    ->where('user_one_id', $userOne)
                    ->where('user_two_id', $userTwo)
                    ->first();

                if (!$chatConv) {
                    $chatConvId = DB::table('chat_conversations')->insertGetId([
                        'user_one_id' => $userOne,
                        'user_two_id' => $userTwo,
                        'last_message_at' => $lc->last_message_at ?: now()->toDateTimeString(),
                        'status' => 'accepted',
                        'requested_by' => $cId
                    ]);
                } else {
                    $chatConvId = $chatConv->id;
                }

                $legacyMessages = DB::table('messages')
                    ->where('conversation_id', $lc->legacy_conv_id)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($legacyMessages as $lm) {
                    $senderId = ($lm->sender_type === 'vendor') ? $vId : $cId;
                    $createdAt = $lm->created_at ?: now()->toDateTimeString();

                    $alreadyInChat = DB::table('chat_messages')
                        ->where('conversation_id', $chatConvId)
                        ->where('sender_id', $senderId)
                        ->where('created_at', $createdAt)
                        ->exists();

                    if (!$alreadyInChat) {
                        DB::table('chat_messages')->insert([
                            'conversation_id' => $chatConvId,
                            'sender_id' => $senderId,
                            'body' => $lm->body,
                            'is_read' => $lm->is_read ? 1 : 0,
                            'created_at' => $createdAt
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}
    }

    /**
     * API: Get all active conversations for current user with high-performance batching.
     */
    public function apiGetConversations(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in to access messages.'], 401);
        }

        self::ensureChatSchema();
        self::syncLegacyChats($userId);

        try {
            $rawConversations = \Illuminate\Support\Facades\DB::table('chat_conversations')
                ->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId)
                ->orderByDesc('last_message_at')
                ->limit(50)
                ->get();

            if ($rawConversations->isEmpty()) {
                return response()->json(['success' => true, 'data' => [], 'requests_count' => 0]);
            }

            $convIds = $rawConversations->pluck('id')->toArray();
            $recipientIds = $rawConversations->map(function($c) use ($userId) {
                return ($c->user_one_id == $userId) ? (int)$c->user_two_id : (int)$c->user_one_id;
            })->unique()->filter()->values()->toArray();

            // 1. Batch fetch all recipients with Eloquent to load vendor details
            $usersMap = [];
            if (!empty($recipientIds)) {
                $users = \App\Models\User::with(['vendor', 'roles'])
                    ->whereIn('id', $recipientIds)
                    ->get();

                foreach ($users as $u) {
                    $personName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                    if ($personName === '') $personName = $u->username ?? ('User #' . $u->id);
                    $vendor = $u->vendor;
                    $displayName = ($vendor && !empty($vendor->shop_name)) ? ($vendor->shop_name . ' (' . $personName . ')') : $personName;

                    $lastSeenTime = !empty($u->last_seen_at) ? strtotime($u->last_seen_at) : (int)\Illuminate\Support\Facades\Cache::get("user_last_seen_{$u->id}", 0);
                    $isOnline = ($lastSeenTime > (time() - 180));
                    $isVendor = ($vendor && in_array($vendor->status, ['approved', 'active']));

                    $logoUrl = null;
                    if ($vendor?->logo) {
                        $logoUrl = str_starts_with($vendor->logo, 'http') ? $vendor->logo : \Illuminate\Support\Facades\Storage::url($vendor->logo);
                    }
                    $bannerUrl = null;
                    if ($vendor?->banner) {
                        $bannerUrl = str_starts_with($vendor->banner, 'http') ? $vendor->banner : \Illuminate\Support\Facades\Storage::url($vendor->banner);
                    }

                    $usersMap[$u->id] = [
                        'id' => (int) $u->id,
                        'username' => $u->username ?? ('user_' . $u->id),
                        'name' => $displayName,
                        'raw_name' => $personName,
                        'shop_name' => $vendor?->shop_name,
                        'profile_pic' => $this->formatChatAvatar($u->avatar, $displayName),
                        'email' => $u->email ?? '',
                        'is_online' => $isOnline,
                        'last_seen_at' => $u->last_seen_at ?? null,
                        'status_label' => $isOnline ? 'Active now' : ($lastSeenTime ? 'Active ' . $this->formatChatTimeAgo(date('Y-m-d H:i:s', $lastSeenTime)) . ' ago' : 'Offline'),
                        'is_vendor' => $isVendor,
                        'vendor_slug' => $vendor?->slug,
                        'vendor_city' => $vendor?->city ?? 'India',
                        'vendor_state' => $vendor?->state,
                        'vendor_rating' => $vendor?->rating ?? 4.9,
                        'store_url' => $isVendor ? $vendor->storefront_url : null,
                        'store_domain' => $isVendor ? "{$vendor->slug}." . config('marketplace.domain') : null,
                        'banner_url' => $bannerUrl,
                        'logo_url' => $logoUrl,
                        'bio' => $vendor?->description ?? 'Member of K71 Direct Commerce community.',
                        'member_since' => $u->created_at ? $u->created_at->format('M Y') : '2026',
                        'role_label' => $isVendor ? 'Verified Merchant' : ($u->roles && $u->roles->contains('slug', 'admin') ? 'Staff Admin' : 'K71 Member'),
                    ];
                }
            }

            // 2. Batch fetch unread counts in ONE query
            $unreadCounts = \Illuminate\Support\Facades\DB::table('chat_messages')
                ->select('conversation_id', \Illuminate\Support\Facades\DB::raw('count(*) as aggregate'))
                ->whereIn('conversation_id', $convIds)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', 0)
                ->groupBy('conversation_id')
                ->pluck('aggregate', 'conversation_id')
                ->toArray();

            // 3. Batch fetch latest messages using max id
            $maxMsgIds = \Illuminate\Support\Facades\DB::table('chat_messages')
                ->select(\Illuminate\Support\Facades\DB::raw('MAX(id) as max_id'))
                ->whereIn('conversation_id', $convIds)
                ->groupBy('conversation_id')
                ->pluck('max_id')
                ->filter()
                ->toArray();

            $latestMessages = [];
            if (!empty($maxMsgIds)) {
                $latestMsgs = \Illuminate\Support\Facades\DB::table('chat_messages')
                    ->whereIn('id', $maxMsgIds)
                    ->get();
                foreach ($latestMsgs as $lm) {
                    $latestMessages[$lm->conversation_id] = $lm;
                }
            }

            $requestsCount = 0;
            $conversations = [];
            foreach ($rawConversations as $conv) {
                $recipientId = ($conv->user_one_id == $userId) ? (int)$conv->user_two_id : (int)$conv->user_one_id;
                $recipientInfo = $usersMap[$recipientId] ?? null;
                if (!$recipientInfo) continue;

                $latestMsg = $latestMessages[$conv->id] ?? null;
                $unreadCount = $unreadCounts[$conv->id] ?? 0;

                $isIncomingRequest = (($conv->status ?? 'accepted') === 'pending' && (int)($conv->requested_by ?? 0) !== $userId);
                if ($isIncomingRequest) {
                    $requestsCount++;
                }

                $conversations[] = [
                    'id' => (int) $conv->id,
                    'status' => $conv->status ?? 'accepted',
                    'requested_by' => $conv->requested_by ? (int)$conv->requested_by : null,
                    'recipient' => $recipientInfo,
                    'last_message' => $latestMsg ? [
                        'body' => $latestMsg->body,
                        'is_sent_by_me' => ((int)$latestMsg->sender_id === $userId),
                        'created_at' => $latestMsg->created_at,
                        'time_formatted' => $this->formatChatTimeAgo($latestMsg->created_at)
                    ] : null,
                    'unread_count' => (int) $unreadCount,
                    'last_message_at' => $conv->last_message_at
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $conversations,
                'requests_count' => $requestsCount
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('apiGetConversations error: ' . $e->getMessage());
            return response()->json(['success' => true, 'data' => [], 'requests_count' => 0]);
        }
    }

    /**
     * API: Get messages for a specific conversation (Ultra-fast & indexed).
     */
    public function apiGetMessages(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in to access messages.'], 401);
        }

        $conversationId = (int) $request->input('conversation_id', 0);
        if ($conversationId <= 0) {
            return response()->json(['success' => false, 'error' => 'Valid conversation_id is required.'], 400);
        }

        self::ensureChatSchema();

        try {
            $conv = \Illuminate\Support\Facades\DB::table('chat_conversations')
                ->where('id', $conversationId)
                ->where(function($q) use ($userId) {
                    $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
                })
                ->first();

            if (!$conv) {
                return response()->json(['success' => false, 'error' => 'Conversation not found or access denied.'], 404);
            }

            $recipientId = ($conv->user_one_id == $userId) ? (int)$conv->user_two_id : (int)$conv->user_one_id;

            // Mark incoming messages as read only if unread exist
            \Illuminate\Support\Facades\DB::table('chat_messages')
                ->where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            // Chunked pagination without deleting any older messages
            $beforeId = (int) $request->input('before_id', 0);
            $limit = min(60, max(15, (int) $request->input('limit', 40)));

            $msgQuery = \Illuminate\Support\Facades\DB::table('chat_messages')
                ->where('conversation_id', $conversationId);

            if ($beforeId > 0) {
                $msgQuery->where('id', '<', $beforeId);
            }

            $rawMessages = $msgQuery->orderBy('id', 'desc')
                ->limit($limit)
                ->get()
                ->reverse()
                ->values();

            $earliestId = $rawMessages->first() ? $rawMessages->first()->id : 0;
            $hasMore = $earliestId > 0 ? \Illuminate\Support\Facades\DB::table('chat_messages')
                ->where('conversation_id', $conversationId)
                ->where('id', '<', $earliestId)
                ->exists() : false;

            $formatted = [];
            foreach ($rawMessages as $m) {
                $senderId = (int) $m->sender_id;
                $carbonDate = \Carbon\Carbon::parse($m->created_at)->timezone('Asia/Kolkata');
                $formatted[] = [
                    'id' => (int) $m->id,
                    'body' => $m->body,
                    'attachment_url' => $m->attachment_url ?? null,
                    'attachment_type' => $m->attachment_type ?? null,
                    'reply_to_id' => !empty($m->reply_to_id) ? (int)$m->reply_to_id : null,
                    'reply_to_body' => $m->reply_to_body ?? null,
                    'reply_to_sender_name' => $m->reply_to_sender_name ?? null,
                    'sender_id' => $senderId,
                    'is_mine' => ($senderId === $userId),
                    'is_read' => (bool) $m->is_read,
                    'created_at' => $carbonDate->toIso8601String(),
                    'time_formatted' => $carbonDate->format('g:i A'),
                    'date_header' => $carbonDate->format('M j, Y'),
                ];
            }

            $recipientInfo = $this->getChatUserInfo($recipientId);
            $isTyping = (bool) \Illuminate\Support\Facades\Cache::has("chat_typing_{$conversationId}_{$recipientId}");

            $convData = [
                'id' => $conversationId,
                'status' => $conv->status ?? 'accepted',
                'requested_by' => $conv->requested_by ? (int)$conv->requested_by : null,
                'last_message_at' => $conv->last_message_at,
                'is_incoming_request' => (($conv->status ?? 'accepted') === 'pending' && (int)($conv->requested_by ?? 0) !== $userId),
            ];

            return response()->json([
                'success' => true,
                'has_more' => $hasMore,
                'conversation' => $convData,
                'recipient' => $recipientInfo,
                'is_typing' => $isTyping,
                'messages' => $formatted,
                'data' => [
                    'has_more' => $hasMore,
                    'conversation' => $convData,
                    'recipient' => $recipientInfo,
                    'is_typing' => $isTyping,
                    'messages' => $formatted
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('apiGetMessages error: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'has_more' => false,
                'conversation' => ['id' => $conversationId],
                'recipient' => null,
                'is_typing' => false,
                'messages' => [],
                'data' => [
                    'has_more' => false,
                    'conversation' => ['id' => $conversationId],
                    'recipient' => null,
                    'is_typing' => false,
                    'messages' => []
                ]
            ]);
        }
    }

    /**
     * API: Send a message in a conversation.
     */
    public function apiSendMessage(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in to access messages.'], 401);
        }

        $conversationId = (int) ($request->input('conversation_id') ?: $request->json('conversation_id', 0));
        $rawBody = $request->input('message') ?: ($request->input('body') ?: ($request->json('message') ?: $request->json('body', '')));
        $cleanBody = htmlspecialchars(trim((string)$rawBody), ENT_QUOTES, 'UTF-8');

        if ($conversationId <= 0 || $cleanBody === '') {
            return response()->json(['success' => false, 'error' => 'Invalid conversation or message body.'], 400);
        }

        self::ensureChatSchema();

        try {
            $conv = \Illuminate\Support\Facades\DB::table('chat_conversations')
                ->where('id', $conversationId)
                ->where(function($q) use ($userId) {
                    $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
                })
                ->first();

            if (!$conv) {
                return response()->json(['success' => false, 'error' => 'Conversation not found or access denied.'], 404);
            }

            $replyToId = $request->input('reply_to_id') ?: $request->json('reply_to_id');
            $replyToBody = $request->input('reply_to_body') ?: $request->json('reply_to_body');
            $replyToSender = $request->input('reply_to_sender_name') ?: $request->json('reply_to_sender_name');

            $now = now()->toDateTimeString();
            $carbonNow = \Carbon\Carbon::now('Asia/Kolkata');

            $insertData = [
                'conversation_id' => $conversationId,
                'sender_id' => $userId,
                'body' => $cleanBody,
                'is_read' => 0,
                'created_at' => $now,
            ];

            if ($replyToId) {
                $insertData['reply_to_id'] = (int) $replyToId;
                $insertData['reply_to_body'] = Str::limit($replyToBody, 200);
                $insertData['reply_to_sender_name'] = $replyToSender;
            }

            try {
                $messageId = \Illuminate\Support\Facades\DB::table('chat_messages')->insertGetId($insertData);
            } catch (\Throwable $colErr) {
                $baseInsert = [
                    'conversation_id' => $conversationId,
                    'sender_id' => $userId,
                    'body' => $cleanBody,
                    'is_read' => 0,
                    'created_at' => $now,
                ];
                $messageId = \Illuminate\Support\Facades\DB::table('chat_messages')->insertGetId($baseInsert);
            }

            \Illuminate\Support\Facades\DB::table('chat_conversations')
                ->where('id', $conversationId)
                ->update(['last_message_at' => $now, 'status' => 'accepted']);

            $msgPayload = [
                'id' => (int) $messageId,
                'conversation_id' => $conversationId,
                'body' => $cleanBody,
                'reply_to_id' => $replyToId ? (int)$replyToId : null,
                'reply_to_body' => $replyToBody,
                'reply_to_sender_name' => $replyToSender,
                'sender_id' => $userId,
                'is_mine' => true,
                'is_read' => false,
                'created_at' => $carbonNow->toIso8601String(),
                'time_formatted' => $carbonNow->format('g:i A')
            ];

            return response()->json([
                'success' => true,
                'data' => $msgPayload,
                'message' => $msgPayload,
                'conversation_status' => 'accepted'
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('apiSendMessage error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Could not send message. Please try again.'], 500);
        }
    }

    /**
     * API: Search users or start conversation (POST action=start_or_find).
     */
    public function apiSearchUsers(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in to access messages.'], 401);
        }

        $this->ensureChatSchema();

        // 1. Handle POST start_or_find
        if ($request->isMethod('post')) {
            $targetUserId = (int) ($request->input('target_user_id') ?: $request->json('target_user_id', 0));
            if ($targetUserId <= 0 || $targetUserId === $userId) {
                return response()->json(['success' => false, 'error' => 'Invalid target user ID.'], 400);
            }

            try {
                $existing = \Illuminate\Support\Facades\DB::table('chat_conversations')
                    ->where(function($q) use ($userId, $targetUserId) {
                        $q->where('user_one_id', $userId)->where('user_two_id', $targetUserId);
                    })
                    ->orWhere(function($q) use ($userId, $targetUserId) {
                        $q->where('user_one_id', $targetUserId)->where('user_two_id', $userId);
                    })
                    ->first();

                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'conversation_id' => (int) $existing->id,
                        'status' => $existing->status ?? 'accepted',
                        'is_new' => false
                    ]);
                }

                $userOne = min($userId, $targetUserId);
                $userTwo = max($userId, $targetUserId);
                $now = now()->toDateTimeString();

                $newId = \Illuminate\Support\Facades\DB::table('chat_conversations')->insertGetId([
                    'user_one_id' => $userOne,
                    'user_two_id' => $userTwo,
                    'status' => 'accepted',
                    'requested_by' => $userId,
                    'last_message_at' => $now,
                ]);

                return response()->json([
                    'success' => true,
                    'conversation_id' => (int) $newId,
                    'status' => 'accepted',
                    'requested_by' => $userId,
                    'is_new' => true
                ]);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('apiSearchUsers start error: ' . $e->getMessage());
                return response()->json(['success' => false, 'error' => 'Could not initiate chat. Please try again.'], 500);
            }
        }

        // 2. Handle GET user search
        $query = trim((string) $request->input('q', ''));
        if (mb_strlen($query) < 1) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            $searchWildcard = '%' . $query . '%';
            $matched = \Illuminate\Support\Facades\DB::table('users')
                ->leftJoin('vendors', 'vendors.user_id', '=', 'users.id')
                ->where('users.id', '!=', $userId)
                ->whereNull('users.deleted_at')
                ->where(function($q) use ($searchWildcard) {
                    $q->where('users.username', 'like', $searchWildcard)
                      ->orWhere('users.first_name', 'like', $searchWildcard)
                      ->orWhere('users.last_name', 'like', $searchWildcard)
                      ->orWhere('users.email', 'like', $searchWildcard)
                      ->orWhere('vendors.shop_name', 'like', $searchWildcard);
                })
                ->limit(25)
                ->get([
                    'users.id',
                    'users.username',
                    'users.first_name',
                    'users.last_name',
                    'users.avatar',
                    'users.email',
                    'vendors.shop_name'
                ]);

            $results = [];
            foreach ($matched as $u) {
                $targetId = (int) $u->id;
                $personName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                if ($personName === '') $personName = $u->username ?? ('User #' . $targetId);
                $displayName = $personName;
                if (!empty($u->shop_name)) {
                    $displayName = $u->shop_name . ' (' . $personName . ')';
                }

                $existingConv = \Illuminate\Support\Facades\DB::table('chat_conversations')
                    ->where(function($q) use ($userId, $targetId) {
                        $q->where('user_one_id', $userId)->where('user_two_id', $targetId);
                    })
                    ->orWhere(function($q) use ($userId, $targetId) {
                        $q->where('user_one_id', $targetId)->where('user_two_id', $userId);
                    })
                    ->first(['id']);

                $results[] = [
                    'id' => $targetId,
                    'username' => $u->username ?? ('user_' . $targetId),
                    'name' => $displayName,
                    'shop_name' => $u->shop_name ?? null,
                    'profile_pic' => $this->formatChatAvatar($u->avatar, $displayName),
                    'conversation_id' => $existingConv ? (int)$existingConv->id : null,
                    'follow_status' => 'accepted'
                ];
            }

            return response()->json(['success' => true, 'data' => $results]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('apiSearchUsers query error: ' . $e->getMessage());
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * API: Follow actions (accept, decline, unfollow, follow).
     */
    public function apiFollowActions(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in to access messages.'], 401);
        }

        $targetId = (int) ($request->input('target_id') ?: $request->json('target_id', 0));
        $conversationId = (int) ($request->input('conversation_id') ?: $request->json('conversation_id', 0));
        $action = trim((string)($request->input('action') ?: $request->json('action', '')));

        $this->ensureChatSchema();

        try {
            if ($conversationId > 0) {
                $status = ($action === 'accept') ? 'accepted' : 'declined';
                \Illuminate\Support\Facades\DB::table('chat_conversations')
                    ->where('id', $conversationId)
                    ->where(function($q) use ($userId) {
                        $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
                    })
                    ->update(['status' => $status]);

                return response()->json([
                    'success' => true,
                    'action' => $action,
                    'conversation_id' => $conversationId,
                    'status' => $status
                ]);
            }

            if ($targetId <= 0 || $targetId === $userId) {
                return response()->json(['success' => false, 'error' => 'Invalid target user ID.'], 400);
            }

            if ($action === 'accept' || $action === 'decline') {
                $status = ($action === 'accept') ? 'accepted' : 'declined';
                \Illuminate\Support\Facades\DB::table('chat_conversations')
                    ->where(function($q) use ($userId, $targetId) {
                        $q->where('user_one_id', $userId)->where('user_two_id', $targetId);
                    })
                    ->orWhere(function($q) use ($userId, $targetId) {
                        $q->where('user_one_id', $targetId)->where('user_two_id', $userId);
                    })
                    ->update(['status' => $status]);

                return response()->json(['success' => true, 'action' => $action, 'target_id' => $targetId, 'status' => $status]);
            }

            return response()->json(['success' => true, 'action' => $action, 'target_id' => $targetId, 'status' => 'accepted']);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('apiSearchUsers query error: ' . $e->getMessage());
            return response()->json(['success' => true, 'data' => []]);
        }
    }

    /**
     * API: Broadcast typing indicator (Instagram-style).
     */
    public function apiTyping(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $conversationId = (int) ($request->input('conversation_id') ?: $request->json('conversation_id', 0));
        if ($conversationId > 0) {
            \Illuminate\Support\Facades\Cache::put("chat_typing_{$conversationId}_{$userId}", time(), now()->addSeconds(8));
        }

        return response()->json(['success' => true]);
    }

    /**
     * API: Unsend a message (Instagram unsend feature).
     */
    public function apiUnsendMessage(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $messageId = (int) ($request->input('message_id') ?: $request->json('message_id', 0));
        if ($messageId <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid message ID.'], 400);
        }

        $msg = \Illuminate\Support\Facades\DB::table('chat_messages')
            ->where('id', $messageId)
            ->where('sender_id', $userId)
            ->first();

        if (!$msg) {
            return response()->json(['success' => false, 'error' => 'Message not found or you cannot unsend this message.'], 403);
        }

        \Illuminate\Support\Facades\DB::table('chat_messages')->where('id', $messageId)->delete();

        // Update conversation last_message_at if needed
        $latest = \Illuminate\Support\Facades\DB::table('chat_messages')
            ->where('conversation_id', $msg->conversation_id)
            ->latest('id')
            ->first();
        if ($latest) {
            \Illuminate\Support\Facades\DB::table('chat_conversations')
                ->where('id', $msg->conversation_id)
                ->update(['last_message_at' => $latest->created_at]);
        }

        return response()->json([
            'success' => true,
            'message_id' => $messageId,
            'conversation_id' => (int) $msg->conversation_id
        ]);
    }

    /**
     * API: Delete entire conversation (Long-press / touch action).
     */
    public function apiDeleteConversation(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $conversationId = (int) ($request->input('conversation_id') ?: $request->json('conversation_id', 0));
        if ($conversationId <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid conversation ID.'], 400);
        }

        $conv = \Illuminate\Support\Facades\DB::table('chat_conversations')
            ->where('id', $conversationId)
            ->where(function($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->first();

        if (!$conv) {
            return response()->json(['success' => false, 'error' => 'Conversation not found or access denied.'], 404);
        }

        \Illuminate\Support\Facades\DB::table('chat_messages')->where('conversation_id', $conversationId)->delete();
        \Illuminate\Support\Facades\DB::table('chat_conversations')->where('id', $conversationId)->delete();

        return response()->json([
            'success' => true,
            'conversation_id' => $conversationId
        ]);
    }

    /**
     * API: Upload Photo / Attachment in chat (Instagram browser feature).
     */
    public function apiUploadPhoto(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in.'], 401);
        }

        $conversationId = (int) $request->input('conversation_id');
        if ($conversationId <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid conversation.'], 400);
        }

        $this->ensureChatSchema();

        $conv = \Illuminate\Support\Facades\DB::table('chat_conversations')
            ->where('id', $conversationId)
            ->where(function($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->first();

        if (!$conv) {
            return response()->json(['success' => false, 'error' => 'Conversation not found or access denied.'], 404);
        }

        if (!$request->hasFile('photo')) {
            return response()->json(['success' => false, 'error' => 'No photo uploaded.'], 400);
        }

        $file = $request->file('photo');
        if (!$file->isValid()) {
            return response()->json(['success' => false, 'error' => 'Uploaded photo is invalid.'], 400);
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            return response()->json(['success' => false, 'error' => 'Only JPG, PNG, WEBP, or GIF images are supported.'], 400);
        }

        if ($file->getSize() > 10 * 1024 * 1024) {
            return response()->json(['success' => false, 'error' => 'Photo size exceeds maximum limit of 10MB.'], 400);
        }

        $ext = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName = 'chat_' . $userId . '_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;

        $targetDir = public_path('storage/chat_attachments');
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $fileName);
        $attachmentUrl = asset('storage/chat_attachments/' . $fileName);

        $caption = htmlspecialchars(trim((string)$request->input('caption', '')), ENT_QUOTES, 'UTF-8');
        $body = $caption !== '' ? $caption : '📷 Photo';
        $now = now()->toDateTimeString();

        $messageId = \Illuminate\Support\Facades\DB::table('chat_messages')->insertGetId([
            'conversation_id' => $conversationId,
            'sender_id' => $userId,
            'body' => $body,
            'attachment_url' => $attachmentUrl,
            'attachment_type' => 'image',
            'is_read' => 0,
            'created_at' => $now,
        ]);

        \Illuminate\Support\Facades\DB::table('chat_conversations')
            ->where('id', $conversationId)
            ->update(['last_message_at' => $now, 'status' => 'accepted']);

        $msgPayload = [
            'id' => (int) $messageId,
            'conversation_id' => $conversationId,
            'body' => $body,
            'attachment_url' => $attachmentUrl,
            'attachment_type' => 'image',
            'sender_id' => $userId,
            'is_mine' => true,
            'is_read' => false,
            'created_at' => $now,
            'time_formatted' => date('g:i A', strtotime($now))
        ];

        return response()->json([
            'success' => true,
            'data' => $msgPayload,
            'message' => $msgPayload,
            'conversation_status' => 'accepted'
        ]);
    }

    /**
     * API: Upload Voice Note / Audio Message (Instagram-style hold-to-speak).
     */
    public function apiUploadVoice(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in.'], 401);
        }

        $conversationId = (int) $request->input('conversation_id');
        if ($conversationId <= 0) {
            return response()->json(['success' => false, 'error' => 'Invalid conversation.'], 400);
        }

        $this->ensureChatSchema();

        $conv = \Illuminate\Support\Facades\DB::table('chat_conversations')
            ->where('id', $conversationId)
            ->where(function($q) use ($userId) {
                $q->where('user_one_id', $userId)->orWhere('user_two_id', $userId);
            })
            ->first();

        if (!$conv) {
            return response()->json(['success' => false, 'error' => 'Conversation not found or access denied.'], 404);
        }

        if (!$request->hasFile('audio') && !$request->hasFile('voice')) {
            return response()->json(['success' => false, 'error' => 'No voice recording received.'], 400);
        }

        $file = $request->file('audio') ?: $request->file('voice');
        if (!$file->isValid()) {
            return response()->json(['success' => false, 'error' => 'Uploaded audio is invalid.'], 400);
        }

        if ($file->getSize() > 25 * 1024 * 1024) {
            return response()->json(['success' => false, 'error' => 'Audio file exceeds limit of 25MB.'], 400);
        }

        $ext = $file->getClientOriginalExtension() ?: 'webm';
        if (!in_array(strtolower($ext), ['webm', 'mp3', 'wav', 'ogg', 'm4a', 'aac', 'mp4'])) {
            $ext = 'webm';
        }
        $fileName = 'voice_' . $userId . '_' . time() . '_' . \Illuminate\Support\Str::random(8) . '.' . $ext;

        $targetDir = public_path('storage/chat_voice');
        if (!file_exists($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        $file->move($targetDir, $fileName);
        $attachmentUrl = asset('storage/chat_voice/' . $fileName);

        $duration = (int) $request->input('duration', 0);
        $durationFormatted = $duration > 0 ? sprintf('%d:%02d', floor($duration / 60), $duration % 60) : '0:05';
        $body = '🎤 Voice message (' . $durationFormatted . ')';
        $now = now()->toDateTimeString();

        $messageId = \Illuminate\Support\Facades\DB::table('chat_messages')->insertGetId([
            'conversation_id' => $conversationId,
            'sender_id' => $userId,
            'body' => $body,
            'attachment_url' => $attachmentUrl,
            'attachment_type' => 'voice',
            'is_read' => 0,
            'created_at' => $now,
        ]);

        \Illuminate\Support\Facades\DB::table('chat_conversations')
            ->where('id', $conversationId)
            ->update(['last_message_at' => $now, 'status' => 'accepted']);

        \Illuminate\Support\Facades\Cache::forget("chat_typing_{$conversationId}_{$userId}");

        $msgPayload = [
            'id' => (int) $messageId,
            'conversation_id' => $conversationId,
            'body' => $body,
            'attachment_url' => $attachmentUrl,
            'attachment_type' => 'voice',
            'sender_id' => $userId,
            'is_mine' => true,
            'is_read' => false,
            'created_at' => $now,
            'time_formatted' => date('g:i A', strtotime($now))
        ];

        return response()->json([
            'success' => true,
            'data' => $msgPayload,
            'message' => $msgPayload,
            'conversation_status' => 'accepted'
        ]);
    }
}
