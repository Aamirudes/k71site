<?php
/**
 * API: Get Conversations List with Unread Counts & Fast Load
 * File: api/chat/get_conversations.php
 * K71 Universal DM Engine
 */

header('Content-Type: application/json; charset=utf-8');

// Locate existing db.php
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
require_once $dbPath;

$currentUserId = require_auth();

try {
    // 1. Fetch conversations for current user
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

    // Prepared statements for details
    $stmtUser = $pdo->prepare("
        SELECT 
            u.id, 
            u.username, 
            u.first_name, 
            u.last_name, 
            u.avatar,
            v.shop_name
        FROM users u 
        LEFT JOIN vendors v ON v.user_id = u.id
        WHERE u.id = ? AND u.deleted_at IS NULL 
        LIMIT 1
    ");

    $stmtLatestMsg = $pdo->prepare("
        SELECT id, sender_id, body, is_read, created_at 
        FROM chat_messages 
        WHERE conversation_id = ? 
        ORDER BY id DESC 
        LIMIT 1
    ");

    $stmtUnread = $pdo->prepare("
        SELECT COUNT(*) 
        FROM chat_messages 
        WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
    ");

    foreach ($rawConversations as $conv) {
        $recipientId = (int) $conv['recipient_id'];
        $convId = (int) $conv['conversation_id'];

        // Fetch recipient user details
        $stmtUser->execute([$recipientId]);
        $recipientUser = $stmtUser->fetch();
        if (!$recipientUser) {
            continue;
        }

        $personName = trim(($recipientUser['first_name'] ?? '') . ' ' . ($recipientUser['last_name'] ?? ''));
        if ($personName === '') {
            $personName = $recipientUser['username'] ?? ('User #' . $recipientId);
        }

        $displayName = $personName;
        if (!empty($recipientUser['shop_name'])) {
            $displayName = $recipientUser['shop_name'] . ' (' . $personName . ')';
        }

        // Fetch latest message
        $stmtLatestMsg->execute([$convId]);
        $latestMsg = $stmtLatestMsg->fetch();

        // Calculate unread count
        $stmtUnread->execute([$convId, $currentUserId]);
        $unreadCount = (int) $stmtUnread->fetchColumn();

        $previewText = 'No messages yet';
        $messageTime = $conv['last_message_at'];
        $isSentByMe = false;

        if ($latestMsg) {
            $previewText = $latestMsg['body'];
            $messageTime = $latestMsg['created_at'];
            $isSentByMe = ((int) $latestMsg['sender_id'] === $currentUserId);
        }

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

    echo json_encode([
        'success' => true,
        'data' => $conversations
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
