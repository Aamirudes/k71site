<?php
/**
 * API: Handle Direct Message Request Actions (Accept / Decline)
 * File: api/chat/request_action.php
 * K71 Instagram Direct Requests Flow
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

$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

$conversationId = isset($jsonData['conversation_id']) ? (int) $jsonData['conversation_id'] : (int) ($_POST['conversation_id'] ?? 0);
$action = trim((string)($jsonData['action'] ?? ($_POST['action'] ?? '')));

if ($conversationId <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Valid conversation_id is required.'
    ]);
    exit;
}

if (!in_array($action, ['accept', 'decline', 'delete'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action. Allowed: accept, decline, delete'
    ]);
    exit;
}

try {
    // 1. Fetch conversation and verify current user is a participant
    $stmt = $pdo->prepare("
        SELECT id, user_one_id, user_two_id, status, requested_by 
        FROM chat_conversations 
        WHERE id = ? AND (user_one_id = ? OR user_two_id = ?) 
        LIMIT 1
    ");
    $stmt->execute([$conversationId, $currentUserId, $currentUserId]);
    $conv = $stmt->fetch();

    if (!$conv) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Conversation not found or access denied.'
        ]);
        exit;
    }

    $otherUserId = ((int)$conv['user_one_id'] === $currentUserId) 
        ? (int)$conv['user_two_id'] 
        : (int)$conv['user_one_id'];

    if ($action === 'accept') {
        // Set conversation status to accepted
        $stmtUpdate = $pdo->prepare("UPDATE chat_conversations SET status = 'accepted' WHERE id = ?");
        $stmtUpdate->execute([$conversationId]);

        // Establish reciprocal user_follows records so follow status stays in sync
        $stmtFollow1 = $pdo->prepare("
            INSERT INTO user_follows (follower_id, following_id, status, created_at, updated_at) 
            VALUES (?, ?, 'accepted', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ON DUPLICATE KEY UPDATE status = 'accepted', updated_at = CURRENT_TIMESTAMP
        ");
        try {
            $stmtFollow1->execute([$otherUserId, $currentUserId]);
            $stmtFollow1->execute([$currentUserId, $otherUserId]);
        } catch (\PDOException $e) {
            // SQLite or MySQL syntax fallback
            $stmtCheck = $pdo->prepare("SELECT id FROM user_follows WHERE follower_id = ? AND following_id = ? LIMIT 1");
            $stmtCheck->execute([$otherUserId, $currentUserId]);
            if ($row = $stmtCheck->fetch()) {
                $pdo->prepare("UPDATE user_follows SET status = 'accepted' WHERE id = ?")->execute([$row['id']]);
            } else {
                $pdo->prepare("INSERT INTO user_follows (follower_id, following_id, status) VALUES (?, ?, 'accepted')")->execute([$otherUserId, $currentUserId]);
            }

            $stmtCheck->execute([$currentUserId, $otherUserId]);
            if ($row = $stmtCheck->fetch()) {
                $pdo->prepare("UPDATE user_follows SET status = 'accepted' WHERE id = ?")->execute([$row['id']]);
            } else {
                $pdo->prepare("INSERT INTO user_follows (follower_id, following_id, status) VALUES (?, ?, 'accepted')")->execute([$currentUserId, $otherUserId]);
            }
        }

        echo json_encode([
            'success' => true,
            'message' => 'Message request accepted.',
            'status' => 'accepted',
            'conversation_id' => $conversationId
        ]);
        exit;

    } elseif ($action === 'decline' || $action === 'delete') {
        // Mark conversation as declined
        $stmtUpdate = $pdo->prepare("UPDATE chat_conversations SET status = 'declined' WHERE id = ?");
        $stmtUpdate->execute([$conversationId]);

        echo json_encode([
            'success' => true,
            'message' => 'Message request declined.',
            'status' => 'declined',
            'conversation_id' => $conversationId
        ]);
        exit;
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
    exit;
}
