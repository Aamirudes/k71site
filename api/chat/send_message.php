<?php
/**
 * API: Send Message with Instant Instagram DM Request & Reply Auto-Accept
 * File: api/chat/send_message.php
 * K71 Realtime Direct Messaging Engine
 */

header('Content-Type: application/json; charset=utf-8');

// Locate existing db.php
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
require_once $dbPath;

$currentUserId = require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. POST required.'
    ]);
    exit;
}

// Parse input from JSON body or POST form data
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

$conversationId = isset($jsonData['conversation_id']) ? (int) $jsonData['conversation_id'] : (int) ($_POST['conversation_id'] ?? 0);
$targetUserId = isset($jsonData['target_user_id']) ? (int) $jsonData['target_user_id'] : (int) ($_POST['target_user_id'] ?? 0);
$rawMessage = $jsonData['message'] ?? ($_POST['message'] ?? ($jsonData['body'] ?? ($_POST['body'] ?? '')));

// Trim and sanitize message
$cleanBody = htmlspecialchars(trim((string) $rawMessage), ENT_QUOTES, 'UTF-8');

if ($cleanBody === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Message body cannot be empty.'
    ]);
    exit;
}

try {
    // 1. If conversation_id is not given, find or create one with target_user_id
    if ($conversationId <= 0 && $targetUserId > 0 && $targetUserId !== $currentUserId) {
        $stmtFind = $pdo->prepare("
            SELECT id, user_one_id, user_two_id, status, requested_by 
            FROM chat_conversations 
            WHERE (user_one_id = :u1 AND user_two_id = :u2) 
               OR (user_one_id = :u2 AND user_two_id = :u1) 
            LIMIT 1
        ");
        $stmtFind->execute([':u1' => $currentUserId, ':u2' => $targetUserId]);
        $conv = $stmtFind->fetch();

        if ($conv) {
            $conversationId = (int)$conv['id'];
        } else {
            $u1 = min($currentUserId, $targetUserId);
            $u2 = max($currentUserId, $targetUserId);
            $stmtNew = $pdo->prepare("
                INSERT INTO chat_conversations (user_one_id, user_two_id, status, requested_by, last_message_at) 
                VALUES (?, ?, 'pending', ?, CURRENT_TIMESTAMP)
            ");
            $stmtNew->execute([$u1, $u2, $currentUserId]);
            $conversationId = (int)$pdo->lastInsertId();
        }
    }

    if ($conversationId <= 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Valid conversation_id or target_user_id is required.'
        ]);
        exit;
    }

    // 2. Fetch conversation and verify participation
    $stmtConv = $pdo->prepare("
        SELECT id, user_one_id, user_two_id, status, requested_by 
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
    $recipientId = ((int) $conv['user_one_id'] === $currentUserId) 
        ? (int) $conv['user_two_id'] 
        : (int) $conv['user_one_id'];

    // 3. Instagram Auto-Accept on Reply:
    // If conversation is pending and the RECIPIENT of the request replies, automatically accept!
    if ($convStatus === 'pending' && $requestedBy !== null && $requestedBy !== $currentUserId) {
        $convStatus = 'accepted';
        $stmtPromote = $pdo->prepare("UPDATE chat_conversations SET status = 'accepted' WHERE id = ?");
        $stmtPromote->execute([$conversationId]);

        // Establish mutual follow records
        try {
            $stmtFollow = $pdo->prepare("
                INSERT INTO user_follows (follower_id, following_id, status, created_at, updated_at) 
                VALUES (?, ?, 'accepted', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ON DUPLICATE KEY UPDATE status = 'accepted', updated_at = CURRENT_TIMESTAMP
            ");
            $stmtFollow->execute([$currentUserId, $recipientId]);
            $stmtFollow->execute([$recipientId, $currentUserId]);
        } catch (\Throwable $t) {
            // Silently handle if table structure differs
        }
    }

    // 4. Insert message
    $now = date('Y-m-d H:i:s');
    $stmtInsert = $pdo->prepare("
        INSERT INTO chat_messages (conversation_id, sender_id, body, is_read, created_at) 
        VALUES (?, ?, ?, 0, ?)
    ");
    $stmtInsert->execute([$conversationId, $currentUserId, $cleanBody, $now]);
    $messageId = (int) $pdo->lastInsertId();

    // 5. Update last_message_at on conversation
    $stmtUpdateConv = $pdo->prepare("
        UPDATE chat_conversations 
        SET last_message_at = ? 
        WHERE id = ?
    ");
    $stmtUpdateConv->execute([$now, $conversationId]);

    echo json_encode([
        'success' => true,
        'conversation_id' => $conversationId,
        'conversation_status' => $convStatus,
        'data' => [
            'id' => $messageId,
            'conversation_id' => $conversationId,
            'sender_id' => $currentUserId,
            'body' => $cleanBody,
            'is_read' => false,
            'created_at' => $now,
            'time_formatted' => date('g:i A', strtotime($now)),
        ]
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
