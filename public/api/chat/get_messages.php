<?php
/**
 * API: Get Messages for Conversation with Security & Read Status Sync
 * File: api/chat/get_messages.php
 */

header('Content-Type: application/json; charset=utf-8');

// Locate existing db.php
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
require_once $dbPath;

$currentUserId = require_auth();

$conversationId = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : 0;

if ($conversationId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Valid conversation_id is required.'
    ]);
    exit;
}

try {
    // 1. Verify user is a participant of the conversation
    $stmtConv = $pdo->prepare("
        SELECT id, user_one_id, user_two_id, last_message_at 
        FROM chat_conversations 
        WHERE id = ? AND (user_one_id = ? OR user_two_id = ?) 
        LIMIT 1
    ");
    $stmtConv->execute([$conversationId, $currentUserId, $currentUserId]);
    $conv = $stmtConv->fetch();

    if (!$conv) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Conversation not found or access denied.'
        ]);
        exit;
    }

    $recipientId = ((int) $conv['user_one_id'] === $currentUserId) 
        ? (int) $conv['user_two_id'] 
        : (int) $conv['user_one_id'];

    // 2. SECURITY CHECK: Verify mutual follow relation is 'accepted'
    $stmtFollow = $pdo->prepare("
        SELECT status 
        FROM user_follows 
        WHERE ((follower_id = :me AND following_id = :target) OR (follower_id = :target AND following_id = :me))
          AND status = 'accepted'
        LIMIT 1
    ");
    $stmtFollow->execute([':me' => $currentUserId, ':target' => $recipientId]);
    $isAccepted = $stmtFollow->fetchColumn();

    if (!$isAccepted) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Follow request must be accepted to chat.'
        ]);
        exit;
    }

    // 3. Mark incoming unread messages as read
    $stmtMarkRead = $pdo->prepare("
        UPDATE chat_messages 
        SET is_read = 1 
        WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
    ");
    $stmtMarkRead->execute([$conversationId, $currentUserId]);

    // 4. Fetch all messages in ascending order
    $stmtMessages = $pdo->prepare("
        SELECT id, conversation_id, sender_id, body, is_read, created_at 
        FROM chat_messages 
        WHERE conversation_id = ? 
        ORDER BY id ASC
    ");
    $stmtMessages->execute([$conversationId]);
    $rawMessages = $stmtMessages->fetchAll();

    $formattedMessages = [];
    foreach ($rawMessages as $msg) {
        $senderId = (int) $msg['sender_id'];
        $timeStr = $msg['created_at'];
        $formattedMessages[] = [
            'id' => (int) $msg['id'],
            'body' => $msg['body'],
            'sender_id' => $senderId,
            'is_mine' => ($senderId === $currentUserId),
            'is_read' => (bool) $msg['is_read'],
            'created_at' => $timeStr,
            'time_formatted' => date('g:i A', strtotime($timeStr)),
            'date_header' => date('M j, Y', strtotime($timeStr)),
        ];
    }

    // 5. Fetch recipient profile
    $recipientInfo = get_chat_user_info($pdo, $recipientId);

    echo json_encode([
        'success' => true,
        'data' => [
            'conversation' => [
                'id' => $conversationId,
                'last_message_at' => $conv['last_message_at'],
            ],
            'recipient' => $recipientInfo,
            'messages' => $formattedMessages
        ]
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
