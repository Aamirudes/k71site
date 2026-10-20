<?php
/**
 * API: Get Conversations List with Mutual Follow Check & Unread Counts
 * File: api/chat/get_conversations.php
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
        SELECT id, username, first_name, last_name, avatar 
        FROM users 
        WHERE id = ? AND deleted_at IS NULL 
        LIMIT 1
    ");

    $stmtFollow = $pdo->prepare("
        SELECT status 
        FROM user_follows 
        WHERE ((follower_id = :me AND following_id = :target) OR (follower_id = :target AND following_id = :me))
          AND status = 'accepted'
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

        // Verify mutual follow connection
        $stmtFollow->execute([':me' => $currentUserId, ':target' => $recipientId]);
        if (!$stmtFollow->fetchColumn()) {
            continue; // Skip conversations without accepted follow
        }

        // Fetch recipient user details
        $stmtUser->execute([$recipientId]);
        $recipientUser = $stmtUser->fetch();
        if (!$recipientUser) {
            continue;
        }

        $fullName = trim(($recipientUser['first_name'] ?? '') . ' ' . ($recipientUser['last_name'] ?? ''));
        if ($fullName === '') {
            $fullName = $recipientUser['username'] ?? ('user_' . $recipientId);
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
                'name' => $fullName,
                'profile_pic' => format_user_avatar($recipientUser['avatar'] ?? null, $fullName),
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
