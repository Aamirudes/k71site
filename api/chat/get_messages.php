<?php
/**
 * API: Get Messages for Conversation with Dynamic Request Status
 * File: api/chat/get_messages.php
 * K71 Realtime Direct Messaging Engine
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
        SELECT id, user_one_id, user_two_id, status, requested_by, last_message_at 
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

    $convStatus = $conv['status'] ?? 'accepted';
    $requestedBy = isset($conv['requested_by']) ? (int)$conv['requested_by'] : null;
    $isIncomingRequest = ($convStatus === 'pending' && $requestedBy !== null && $requestedBy !== $currentUserId);

    $recipientId = ((int) $conv['user_one_id'] === $currentUserId) 
        ? (int) $conv['user_two_id'] 
        : (int) $conv['user_one_id'];

    // 2. Mark incoming unread messages as read
    $stmtMarkRead = $pdo->prepare("
        UPDATE chat_messages 
        SET is_read = 1 
        WHERE conversation_id = ? AND sender_id != ? AND is_read = 0
    ");
    $stmtMarkRead->execute([$conversationId, $currentUserId]);

    // 3. Fetch all messages in ascending order
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

    // 4. Fetch recipient profile
    $recipientInfo = get_chat_user_info($pdo, $recipientId);

    echo json_encode([
        'success' => true,
        'conversation' => [
            'id' => $conversationId,
            'status' => $convStatus,
            'requested_by' => $requestedBy,
            'is_incoming_request' => $isIncomingRequest,
        ],
        'recipient' => $recipientInfo,
        'messages' => $formattedMessages
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
