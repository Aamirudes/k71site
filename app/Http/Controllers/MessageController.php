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
    public function index(Request $request): View
    {
        $user = auth()->user();
        if ($user) {
            if (session_status() === PHP_SESSION_NONE) {
                @session_start();
            }
            $_SESSION['user_id'] = $user->id;
            $_SESSION['username'] = $user->username ?? ($user->first_name ?? 'User');
            @setcookie('k71_uid', (string)$user->id, time() + 86400 * 30, '/', '', false, false);
        }
        return view('messages.index', compact('user'));
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
        if (auth()->check()) {
            return (int) auth()->id();
        }
        if (!empty($_SESSION['user_id'])) {
            return (int) $_SESSION['user_id'];
        }
        if (!empty($_COOKIE['k71_uid']) && is_numeric($_COOKIE['k71_uid'])) {
            return (int) $_COOKIE['k71_uid'];
        }
        return null;
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
        $u = \Illuminate\Support\Facades\DB::table('users')
            ->leftJoin('vendors', 'vendors.user_id', '=', 'users.id')
            ->where('users.id', $userId)
            ->whereNull('users.deleted_at')
            ->first([
                'users.id',
                'users.username',
                'users.first_name',
                'users.last_name',
                'users.avatar',
                'users.email',
                'vendors.shop_name'
            ]);

        if (!$u) return null;

        $personName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
        if ($personName === '') {
            $personName = $u->username ?? ('User #' . $u->id);
        }

        $displayName = $personName;
        if (!empty($u->shop_name)) {
            $displayName = $u->shop_name . ' (' . $personName . ')';
        }

        return [
            'id' => (int) $u->id,
            'username' => $u->username ?? ('user_' . $u->id),
            'name' => $displayName,
            'shop_name' => $u->shop_name ?? null,
            'profile_pic' => $this->formatChatAvatar($u->avatar, $displayName),
            'email' => $u->email ?? '',
        ];
    }

    /**
     * Self-healing table check: ensure chat_conversations, chat_messages, user_follows exist.
     */
    private function ensureChatSchema(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('chat_conversations')) {
                \Illuminate\Support\Facades\Schema::create('chat_conversations', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('user_one_id');
                    $table->unsignedBigInteger('user_two_id');
                    $table->string('status', 20)->default('accepted');
                    $table->unsignedBigInteger('requested_by')->nullable();
                    $table->timestamp('last_message_at')->useCurrent();
                    $table->index('user_one_id');
                    $table->index('user_two_id');
                    $table->index('last_message_at');
                });
            } else {
                if (!\Illuminate\Support\Facades\Schema::hasColumn('chat_conversations', 'status')) {
                    \Illuminate\Support\Facades\Schema::table('chat_conversations', function ($t) {
                        $t->string('status', 20)->default('accepted')->after('user_two_id');
                    });
                }
                if (!\Illuminate\Support\Facades\Schema::hasColumn('chat_conversations', 'requested_by')) {
                    \Illuminate\Support\Facades\Schema::table('chat_conversations', function ($t) {
                        $t->unsignedBigInteger('requested_by')->nullable()->after('status');
                    });
                }
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('chat_messages')) {
                \Illuminate\Support\Facades\Schema::create('chat_messages', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('conversation_id');
                    $table->unsignedBigInteger('sender_id');
                    $table->text('body');
                    $table->boolean('is_read')->default(false);
                    $table->timestamp('created_at')->useCurrent();
                    $table->index('conversation_id');
                    $table->index('sender_id');
                });
            }

            if (!\Illuminate\Support\Facades\Schema::hasTable('user_follows')) {
                \Illuminate\Support\Facades\Schema::create('user_follows', function ($table) {
                    $table->id();
                    $table->unsignedBigInteger('follower_id');
                    $table->unsignedBigInteger('following_id');
                    $table->string('status', 20)->default('accepted');
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * API: Get all active conversations for current user.
     */
    public function apiGetConversations(Request $request)
    {
        $userId = $this->resolveChatUserId();
        if (!$userId) {
            return response()->json(['success' => false, 'error' => 'Unauthorized. Please log in to access messages.'], 401);
        }

        $this->ensureChatSchema();

        try {
            $rawConversations = \Illuminate\Support\Facades\DB::table('chat_conversations')
                ->where('user_one_id', $userId)
                ->orWhere('user_two_id', $userId)
                ->orderByDesc('last_message_at')
                ->get();

            $conversations = [];
            foreach ($rawConversations as $conv) {
                $recipientId = ($conv->user_one_id == $userId) ? (int)$conv->user_two_id : (int)$conv->user_one_id;
                $recipientInfo = $this->getChatUserInfo($recipientId);
                if (!$recipientInfo) continue;

                $latestMsg = \Illuminate\Support\Facades\DB::table('chat_messages')
                    ->where('conversation_id', $conv->id)
                    ->orderByDesc('id')
                    ->first();

                $unreadCount = \Illuminate\Support\Facades\DB::table('chat_messages')
                    ->where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $userId)
                    ->where('is_read', 0)
                    ->count();

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

            return response()->json(['success' => true, 'data' => $conversations]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Get messages for a specific conversation.
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

        $this->ensureChatSchema();

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

            // Mark incoming messages as read
            \Illuminate\Support\Facades\DB::table('chat_messages')
                ->where('conversation_id', $conversationId)
                ->where('sender_id', '!=', $userId)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);

            $rawMessages = \Illuminate\Support\Facades\DB::table('chat_messages')
                ->where('conversation_id', $conversationId)
                ->orderBy('id', 'asc')
                ->get();

            $formatted = [];
            foreach ($rawMessages as $m) {
                $senderId = (int) $m->sender_id;
                $formatted[] = [
                    'id' => (int) $m->id,
                    'body' => $m->body,
                    'sender_id' => $senderId,
                    'is_mine' => ($senderId === $userId),
                    'is_read' => (bool) $m->is_read,
                    'created_at' => $m->created_at,
                    'time_formatted' => date('g:i A', strtotime($m->created_at)),
                    'date_header' => date('M j, Y', strtotime($m->created_at)),
                ];
            }

            $recipientInfo = $this->getChatUserInfo($recipientId);
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
                'messages' => $formatted,
                'data' => [
                    'conversation' => $convData,
                    'recipient' => $recipientInfo,
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

        $this->ensureChatSchema();

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
}
