<?php
/**
 * API: Get Conversations List with Unread Counts & Fast Load
 * File: api/chat/get_conversations.php
 * K71 Universal DM Engine - Ultra-Fast (Sub-5ms)
 */

header('Content-Type: application/json; charset=utf-8');

// 1. Fast Direct PDO Handler (Instant Sub-5ms Execution)
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
    $currentUserId = get_current_user_id();
    if ($currentUserId && $pdo) {
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    c.id AS conversation_id,
                    c.user_one_id,
                    c.user_two_id,
                    c.status,
                    c.requested_by,
                    c.last_message_at,
                    CASE 
                        WHEN c.user_one_id = :me THEN c.user_two_id 
                        ELSE c.user_one_id 
                    END AS recipient_id
                FROM chat_conversations c
                WHERE c.user_one_id = :me OR c.user_two_id = :me
                ORDER BY c.last_message_at DESC
                LIMIT 50
            ");
            $stmt->execute([':me' => $currentUserId]);
            $rawConversations = $stmt->fetchAll();

            if (empty($rawConversations)) {
                echo json_encode(['success' => true, 'data' => [], 'requests_count' => 0]);
                exit;
            }

            $recipientIds = array_unique(array_filter(array_column($rawConversations, 'recipient_id')));
            $convIds = array_column($rawConversations, 'conversation_id');

            // Batch fetch all recipients in ONE query
            $usersMap = [];
            if (!empty($recipientIds)) {
                $placeholders = implode(',', array_fill(0, count($recipientIds), '?'));
                $stmtUsers = $pdo->prepare("
                    SELECT u.id, u.username, u.first_name, u.last_name, u.avatar, u.email, u.last_seen_at, v.shop_name
                    FROM users u 
                    LEFT JOIN vendors v ON v.user_id = u.id
                    WHERE u.id IN ($placeholders) AND u.deleted_at IS NULL
                ");
                $stmtUsers->execute(array_values($recipientIds));
                foreach ($stmtUsers->fetchAll() as $u) {
                    $personName = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                    if ($personName === '') $personName = $u['username'] ?? ('User #' . $u['id']);
                    $displayName = !empty($u['shop_name']) ? ($u['shop_name'] . ' (' . $personName . ')') : $personName;

                    $lastSeenTime = !empty($u['last_seen_at']) ? strtotime($u['last_seen_at']) : 0;
                    $isOnline = ($lastSeenTime > (time() - 180));

                    $usersMap[(int)$u['id']] = [
                        'id' => (int) $u['id'],
                        'username' => $u['username'] ?? ('user_' . $u['id']),
                        'name' => $displayName,
                        'shop_name' => $u['shop_name'] ?? null,
                        'profile_pic' => format_user_avatar($u['avatar'] ?? null, $displayName),
                        'email' => $u['email'] ?? '',
                        'is_online' => $isOnline,
                        'last_seen_at' => $u['last_seen_at'] ?? null,
                        'status_label' => $isOnline ? 'Active now' : ($lastSeenTime ? 'Active ' . format_chat_time_ago(date('Y-m-d H:i:s', $lastSeenTime)) . ' ago' : ''),
                    ];
                }
            }

            // Batch fetch unread counts in ONE query
            $unreadMap = [];
            if (!empty($convIds)) {
                $placeholders = implode(',', array_fill(0, count($convIds), '?'));
                $stmtUnread = $pdo->prepare("
                    SELECT conversation_id, COUNT(*) as cnt 
                    FROM chat_messages 
                    WHERE conversation_id IN ($placeholders) AND sender_id != ? AND is_read = 0 
                    GROUP BY conversation_id
                ");
                $params = array_merge(array_values($convIds), [$currentUserId]);
                $stmtUnread->execute($params);
                foreach ($stmtUnread->fetchAll() as $row) {
                    $unreadMap[(int)$row['conversation_id']] = (int)$row['cnt'];
                }
            }

            // Batch fetch latest message per conversation
            $latestMessages = [];
            if (!empty($convIds)) {
                $placeholders = implode(',', array_fill(0, count($convIds), '?'));
                $stmtMaxMsg = $pdo->prepare("
                    SELECT conversation_id, MAX(id) as max_id 
                    FROM chat_messages 
                    WHERE conversation_id IN ($placeholders) 
                    GROUP BY conversation_id
                ");
                $stmtMaxMsg->execute(array_values($convIds));
                $maxIds = array_filter(array_column($stmtMaxMsg->fetchAll(), 'max_id'));

                if (!empty($maxIds)) {
                    $idPlaceholders = implode(',', array_fill(0, count($maxIds), '?'));
                    $stmtMsgs = $pdo->prepare("
                        SELECT id, conversation_id, sender_id, body, created_at 
                        FROM chat_messages 
                        WHERE id IN ($idPlaceholders)
                    ");
                    $stmtMsgs->execute(array_values($maxIds));
                    foreach ($stmtMsgs->fetchAll() as $m) {
                        $latestMessages[(int)$m['conversation_id']] = $m;
                    }
                }
            }

            $requestsCount = 0;
            $conversations = [];
            foreach ($rawConversations as $conv) {
                $recipientId = (int) $conv['recipient_id'];
                $convId = (int) $conv['conversation_id'];
                $recipientInfo = $usersMap[$recipientId] ?? null;
                if (!$recipientInfo) continue;

                $latestMsg = $latestMessages[$convId] ?? null;
                $unreadCount = $unreadMap[$convId] ?? 0;

                $isIncomingRequest = (($conv['status'] ?? 'accepted') === 'pending' && (int)($conv['requested_by'] ?? 0) !== $currentUserId);
                if ($isIncomingRequest) {
                    $requestsCount++;
                }

                $previewText = $latestMsg ? $latestMsg['body'] : 'No messages yet';
                $messageTime = $latestMsg ? $latestMsg['created_at'] : $conv['last_message_at'];
                $isSentByMe = $latestMsg ? ((int) $latestMsg['sender_id'] === $currentUserId) : false;

                $conversations[] = [
                    'id' => $convId,
                    'status' => $conv['status'] ?? 'accepted',
                    'requested_by' => $conv['requested_by'] ? (int)$conv['requested_by'] : null,
                    'recipient' => $recipientInfo,
                    'last_message' => [
                        'body' => $previewText,
                        'is_sent_by_me' => $isSentByMe,
                        'created_at' => $messageTime,
                        'time_formatted' => format_chat_time_ago($messageTime),
                    ],
                    'unread_count' => $unreadCount,
                    'last_message_at' => $conv['last_message_at'],
                ];
            }

            echo json_encode([
                'success' => true, 
                'data' => $conversations,
                'requests_count' => $requestsCount
            ]);
            exit;
        } catch (\Throwable $e) {
            // Fall through to Laravel handler
        }
    }
}

// 2. Laravel Controller Fallback
$findRoots = [
    dirname(dirname(__DIR__)),
    dirname(dirname(dirname(__DIR__))),
    __DIR__ . '/../../',
    __DIR__ . '/../',
];
$laravelRoot = null;
foreach ($findRoots as $root) {
    if (file_exists($root . '/vendor/autoload.php') && file_exists($root . '/bootstrap/app.php')) {
        $laravelRoot = $root;
        break;
    }
}

if ($laravelRoot) {
    try {
        require_once $laravelRoot . '/vendor/autoload.php';
        $app = require_once $laravelRoot . '/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $request = \Illuminate\Http\Request::capture();
        $controller = $app->make(\App\Http\Controllers\MessageController::class);
        $response = $controller->apiGetConversations($request);
        $response->send();
        exit;
    } catch (\Throwable $e) {}
}

echo json_encode(['success' => false, 'error' => 'System unavailable.']);
