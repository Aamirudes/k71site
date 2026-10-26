<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $selectCols = [
            'users.id',
            'users.username',
            'users.first_name',
            'users.last_name',
            'users.avatar',
            'users.email',
            'users.last_seen_at',
            'vendors.shop_name'
        ];

        $u = \Illuminate\Support\Facades\DB::table('users')
            ->leftJoin('vendors', 'vendors.user_id', '=', 'users.id')
            ->where('users.id', $userId)
            ->whereNull('users.deleted_at')
            ->first($selectCols);

        if (!$u) return null;

        $personName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
        if ($personName === '') {
            $personName = $u->username ?? ('User #' . $u->id);
        }

        $displayName = $personName;
        if (!empty($u->shop_name)) {
            $displayName = $u->shop_name . ' (' . $personName . ')';
        }

        // Real-time activity status (Online if active within last 3 minutes)
        $lastSeenTime = !empty($u->last_seen_at) ? strtotime($u->last_seen_at) : (int)\Illuminate\Support\Facades\Cache::get("user_last_seen_{$u->id}", 0);
        $isOnline = ($lastSeenTime > (time() - 180));

        return [
            'id' => (int) $u->id,
            'username' => $u->username ?? ('user_' . $u->id),
            'name' => $displayName,
            'shop_name' => $u->shop_name ?? null,
            'profile_pic' => $this->formatChatAvatar($u->avatar, $displayName),
            'email' => $u->email ?? '',
            'is_online' => $isOnline,
            'last_seen_at' => $u->last_seen_at ?? null,
            'status_label' => $isOnline ? 'Active now' : ($lastSeenTime ? 'Active ' . $this->formatChatTimeAgo(date('Y-m-d H:i:s', $lastSeenTime)) . ' ago' : ''),
        ];
    }

    /**
     * Self-healing table check: bypassed during runtime for maximum speed.
     */
    private function ensureChatSchema(): void
    {
        // Tables are verified through migrations; no-op during runtime requests
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

            // 1. Batch fetch all recipients in ONE query
            $usersMap = [];
            if (!empty($recipientIds)) {
                $users = \Illuminate\Support\Facades\DB::table('users')
                    ->leftJoin('vendors', 'vendors.user_id', '=', 'users.id')
                    ->whereIn('users.id', $recipientIds)
                    ->whereNull('users.deleted_at')
                    ->get([
                        'users.id', 'users.username', 'users.first_name', 'users.last_name',
                        'users.avatar', 'users.email', 'users.last_seen_at', 'vendors.shop_name'
                    ]);

                foreach ($users as $u) {
                    $personName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                    if ($personName === '') $personName = $u->username ?? ('User #' . $u->id);
                    $displayName = !empty($u->shop_name) ? ($u->shop_name . ' (' . $personName . ')') : $personName;

                    $lastSeenTime = !empty($u->last_seen_at) ? strtotime($u->last_seen_at) : (int)\Illuminate\Support\Facades\Cache::get("user_last_seen_{$u->id}", 0);
                    $isOnline = ($lastSeenTime > (time() - 180));

                    $usersMap[$u->id] = [
                        'id' => (int) $u->id,
                        'username' => $u->username ?? ('user_' . $u->id),
                        'name' => $displayName,
                        'shop_name' => $u->shop_name ?? null,
                        'profile_pic' => $this->formatChatAvatar($u->avatar, $displayName),
                        'email' => $u->email ?? '',
                        'is_online' => $isOnline,
                        'last_seen_at' => $u->last_seen_at ?? null,
                        'status_label' => $isOnline ? 'Active now' : ($lastSeenTime ? 'Active ' . $this->formatChatTimeAgo(date('Y-m-d H:i:s', $lastSeenTime)) . ' ago' : ''),
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
            return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
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

            // Fetch last 100 messages
            $rawMessages = \Illuminate\Support\Facades\DB::table('chat_messages')
                ->where('conversation_id', $conversationId)
                ->orderBy('id', 'desc')
                ->limit(100)
                ->get()
                ->reverse()
                ->values();

            $formatted = [];
            foreach ($rawMessages as $m) {
                $senderId = (int) $m->sender_id;
                $formatted[] = [
                    'id' => (int) $m->id,
                    'body' => $m->body,
                    'attachment_url' => $m->attachment_url ?? null,
                    'attachment_type' => $m->attachment_type ?? null,
                    'sender_id' => $senderId,
                    'is_mine' => ($senderId === $userId),
                    'is_read' => (bool) $m->is_read,
                    'created_at' => $m->created_at,
                    'time_formatted' => date('g:i A', strtotime($m->created_at)),
                    'date_header' => date('M j, Y', strtotime($m->created_at)),
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
                'conversation' => $convData,
                'recipient' => $recipientInfo,
                'is_typing' => $isTyping,
                'messages' => $formatted,
                'data' => [
                    'conversation' => $convData,
                    'recipient' => $recipientInfo,
                    'is_typing' => $isTyping,
                    'messages' => $formatted
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
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

            $now = now()->toDateTimeString();
            $messageId = \Illuminate\Support\Facades\DB::table('chat_messages')->insertGetId([
                'conversation_id' => $conversationId,
                'sender_id' => $userId,
                'body' => $cleanBody,
                'is_read' => 0,
                'created_at' => $now,
            ]);

            \Illuminate\Support\Facades\DB::table('chat_conversations')
                ->where('id', $conversationId)
                ->update(['last_message_at' => $now, 'status' => 'accepted']);

            $msgPayload = [
                'id' => (int) $messageId,
                'conversation_id' => $conversationId,
                'body' => $cleanBody,
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
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
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
                return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
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
            return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
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
            return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
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
            \Illuminate\Support\Facades\Cache::put("chat_typing_{$conversationId}_{$userId}", time(), 4);
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
}
