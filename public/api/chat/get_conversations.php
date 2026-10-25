<?php
/**
 * API: Get Conversations List with Unread Counts & Fast Load
 * File: api/chat/get_conversations.php
 * K71 Universal DM Engine
 */

header('Content-Type: application/json; charset=utf-8');

// 1. Forward directly to Laravel MessageController for universal DB & session handling
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
    } catch (\Throwable $e) {
        // Fallback to standalone handler below if needed
    }
}

// 2. Standalone Fallback
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
    $currentUserId = require_auth();
    try {
        if (!$pdo) {
            throw new Exception("Database connection unavailable.");
        }
        $stmt = $pdo->prepare("
            SELECT 
                c.id AS conversation_id,
                c.user_one_id,
                c.user_two_id,
                c.last_message_at,
                CASE 
                    WHEN c.user_one_id = :me THEN c.user_two_id 
                    ELSE c.user_one_id 
                END AS recipient_id
            FROM chat_conversations c
            WHERE c.user_one_id = :me OR c.user_two_id = :me
            ORDER BY c.last_message_at DESC
        ");
        $stmt->execute([':me' => $currentUserId]);
        $rawConversations = $stmt->fetchAll();
        $conversations = [];

        $stmtUser = $pdo->prepare("
            SELECT u.id, u.username, u.first_name, u.last_name, u.avatar, v.shop_name
            FROM users u 
            LEFT JOIN vendors v ON v.user_id = u.id
            WHERE u.id = ? AND u.deleted_at IS NULL LIMIT 1
        ");
        $stmtLatestMsg = $pdo->prepare("SELECT id, sender_id, body, is_read, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY id DESC LIMIT 1");
        $stmtUnread = $pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE conversation_id = ? AND sender_id != ? AND is_read = 0");

        foreach ($rawConversations as $conv) {
            $recipientId = (int) $conv['recipient_id'];
            $convId = (int) $conv['conversation_id'];
            $stmtUser->execute([$recipientId]);
            $recipientUser = $stmtUser->fetch();
            if (!$recipientUser) continue;

            $personName = trim(($recipientUser['first_name'] ?? '') . ' ' . ($recipientUser['last_name'] ?? ''));
            if ($personName === '') $personName = $recipientUser['username'] ?? ('User #' . $recipientId);
            $displayName = !empty($recipientUser['shop_name']) ? ($recipientUser['shop_name'] . ' (' . $personName . ')') : $personName;

            $stmtLatestMsg->execute([$convId]);
            $latestMsg = $stmtLatestMsg->fetch();

            $stmtUnread->execute([$convId, $currentUserId]);
            $unreadCount = (int) $stmtUnread->fetchColumn();

            $previewText = $latestMsg ? $latestMsg['body'] : 'No messages yet';
            $messageTime = $latestMsg ? $latestMsg['created_at'] : $conv['last_message_at'];
            $isSentByMe = $latestMsg ? ((int) $latestMsg['sender_id'] === $currentUserId) : false;

            $conversations[] = [
                'id' => $convId,
                'recipient' => [
                    'id' => $recipientId,
                    'username' => $recipientUser['username'] ?? ('user_' . $recipientId),
                    'name' => $displayName,
                    'shop_name' => $recipientUser['shop_name'] ?? null,
                    'profile_pic' => format_user_avatar($recipientUser['avatar'] ?? null, $displayName),
                ],
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

        echo json_encode(['success' => true, 'data' => $conversations]);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'System unavailable.']);
