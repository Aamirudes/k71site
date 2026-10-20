<?php
/**
 * API: Send Message with Security Verification & Timestamp Update
 * File: api/chat/send_message.php
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
$rawMessage = $jsonData['message'] ?? ($_POST['message'] ?? ($jsonData['body'] ?? ($_POST['body'] ?? '')));

// Trim and sanitize message
$cleanBody = htmlspecialchars(trim((string) $rawMessage), ENT_QUOTES, 'UTF-8');

if ($conversationId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Valid conversation_id is required.'
    ]);
    exit;
}

if ($cleanBody === '') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Message body cannot be empty.'
    ]);
    exit;
}

try {
    // 1. Verify user is a participant of the conversation
    $stmtConv = $pdo->prepare("
        SELECT id, user_one_id, user_two_id 
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

    // 2. SECURITY CHECK: Verify mutual 'accepted' follow status
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
            'error' => 'Follow request must be accepted to send messages.'
        ]);
        exit;
    }

    // 3. Insert new message
    $now = date('Y-m-d H:i:s');
    $stmtInsert = $pdo->prepare("
        INSERT INTO chat_messages (conversation_id, sender_id, body, is_read, created_at) 
        VALUES (?, ?, ?, 0, ?)
    ");
    $stmtInsert->execute([$conversationId, $currentUserId, $cleanBody, $now]);
    $messageId = (int) $pdo->lastInsertId();

    // 4. Update last_message_at on conversation
    $stmtUpdateConv = $pdo->prepare("
        UPDATE chat_conversations 
        SET last_message_at = ? 
        WHERE id = ?
    ");
    $stmtUpdateConv->execute([$now, $conversationId]);

    echo json_encode([
        'success' => true,
        'message' => [
            'id' => $messageId,
            'conversation_id' => $conversationId,
            'body' => $cleanBody,
            'sender_id' => $currentUserId,
            'is_mine' => true,
            'is_read' => false,
            'created_at' => $now,
            'time_formatted' => date('g:i A', strtotime($now))
        ]
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
