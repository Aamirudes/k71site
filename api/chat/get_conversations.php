<?php
/**
 * API: Get Conversations List with Primary vs Requests Tab Support
 * File: api/chat/get_conversations.php
 * K71 Instagram Direct Dual-Inbox Architecture
 */

header('Content-Type: application/json; charset=utf-8');

// Locate existing db.php
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
require_once $dbPath;

$currentUserId = require_auth();

$tab = isset($_GET['tab']) && in_array(strtolower(trim($_GET['tab'])), ['requests', 'primary'], true) 
    ? strtolower(trim($_GET['tab'])) 
    : 'primary';

try {
    // 1. Calculate incoming requests count across all conversations
    $stmtReqCount = $pdo->prepare("
        SELECT COUNT(*) 
        FROM chat_conversations 
        WHERE (user_one_id = :me OR user_two_id = :me) 
          AND status = 'pending' 
          AND (requested_by IS NULL OR requested_by != :me)
    ");
    $stmtReqCount->execute([':me' => $currentUserId]);
    $requestsCount = (int) $stmtReqCount->fetchColumn();

    // 2. Fetch conversations based on active tab
    if ($tab === 'requests') {
        // Incoming message requests waiting for recipient approval
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
            WHERE (c.user_one_id = :me OR c.user_two_id = :me)
              AND c.status = 'pending'
              AND (c.requested_by IS NULL OR c.requested_by != :me)
            ORDER BY c.last_message_at DESC
        ");
    } else {
        // Primary tab: accepted conversations OR pending conversations initiated by me
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
            WHERE (c.user_one_id = :me OR c.user_two_id = :me)
              AND (
                  c.status = 'accepted' 
                  OR (c.status = 'pending' AND c.requested_by = :me)
                  OR c.status IS NULL
              )
            ORDER BY c.last_message_at DESC
        ");
    }

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
        $cStatus = $conv['status'] ?? 'accepted';
        $cRequestedBy = isset($conv['requested_by']) ? (int)$conv['requested_by'] : null;
        $isIncomingRequest = ($cStatus === 'pending' && $cRequestedBy !== null && $cRequestedBy !== $currentUserId);

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

        $previewText = ($cStatus === 'pending' && $cRequestedBy === $currentUserId) ? 'Request sent • Waiting for acceptance' : 'No messages yet';
        $messageTime = $conv['last_message_at'];
        $isSentByMe = false;

        if ($latestMsg) {
            $previewText = $latestMsg['body'];
            $messageTime = $latestMsg['created_at'];
            $isSentByMe = ((int) $latestMsg['sender_id'] === $currentUserId);
        }

        $conversations[] = [
            'id' => $convId,
            'status' => $cStatus,
            'requested_by' => $cRequestedBy,
            'is_incoming_request' => $isIncomingRequest,
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
        ];
    }

    echo json_encode([
        'success' => true,
        'tab' => $tab,
        'requests_count' => $requestsCount,
        'data' => $conversations
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
