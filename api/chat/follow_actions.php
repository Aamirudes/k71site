<?php
/**
 * API: Follow Actions Handler (Follow, Accept, Decline, Cancel, Unfollow)
 * File: api/chat/follow_actions.php
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

$targetId = isset($jsonData['target_id']) ? (int) $jsonData['target_id'] : (int) ($_POST['target_id'] ?? 0);
$action = trim($jsonData['action'] ?? ($_POST['action'] ?? ''));

if ($targetId <= 0 || $targetId === $currentUserId) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid target user ID.'
    ]);
    exit;
}

$validActions = ['follow', 'accept', 'decline', 'unfollow', 'cancel'];
if (!in_array($action, $validActions, true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'Invalid action. Allowed: ' . implode(', ', $validActions)
    ]);
    exit;
}

try {
    // Verify target user exists in database
    $stmtUser = $pdo->prepare("SELECT id FROM users WHERE id = ? AND deleted_at IS NULL LIMIT 1");
    $stmtUser->execute([$targetId]);
    if (!$stmtUser->fetchColumn()) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'Target user does not exist.'
        ]);
        exit;
    }

    $newStatus = 'none';
    $conversationId = null;

    if ($action === 'follow') {
        // Insert or update follow request with pending status
        $stmt = $pdo->prepare("
            SELECT id, status FROM user_follows 
            WHERE follower_id = ? AND following_id = ? 
            LIMIT 1
        ");
        $stmt->execute([$currentUserId, $targetId]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmtUpdate = $pdo->prepare("
                UPDATE user_follows 
                SET status = 'pending', updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$existing['id']]);
        } else {
            $stmtInsert = $pdo->prepare("
                INSERT INTO user_follows (follower_id, following_id, status, created_at, updated_at) 
                VALUES (?, ?, 'pending', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $stmtInsert->execute([$currentUserId, $targetId]);
        }
        $newStatus = 'pending_outgoing';

    } elseif ($action === 'cancel') {
        // Cancel pending follow request
        $stmt = $pdo->prepare("
            DELETE FROM user_follows 
            WHERE follower_id = ? AND following_id = ? AND status = 'pending'
        ");
        $stmt->execute([$currentUserId, $targetId]);
        $newStatus = 'none';

    } elseif ($action === 'accept') {
        // Accept incoming follow request: target is follower, current user is following
        $stmt = $pdo->prepare("
            SELECT id, status FROM user_follows 
            WHERE follower_id = ? AND following_id = ?
            LIMIT 1
        ");
        $stmt->execute([$targetId, $currentUserId]);
        $incoming = $stmt->fetch();

        if ($incoming) {
            $stmtUpdate = $pdo->prepare("
                UPDATE user_follows 
                SET status = 'accepted', updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$incoming['id']]);
        } else {
            // If direct record missing, create accepted relation
            $stmtInsert = $pdo->prepare("
                INSERT INTO user_follows (follower_id, following_id, status, created_at, updated_at) 
                VALUES (?, ?, 'accepted', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $stmtInsert->execute([$targetId, $currentUserId]);
        }

        // Also ensure reciprocal follow record exists
        $stmtReciprocal = $pdo->prepare("
            SELECT id FROM user_follows 
            WHERE follower_id = ? AND following_id = ?
            LIMIT 1
        ");
        $stmtReciprocal->execute([$currentUserId, $targetId]);
        $reciprocal = $stmtReciprocal->fetch();

        if ($reciprocal) {
            $stmtUpdRec = $pdo->prepare("UPDATE user_follows SET status = 'accepted', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmtUpdRec->execute([$reciprocal['id']]);
        } else {
            $stmtInsRec = $pdo->prepare("
                INSERT INTO user_follows (follower_id, following_id, status, created_at, updated_at) 
                VALUES (?, ?, 'accepted', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $stmtInsRec->execute([$currentUserId, $targetId]);
        }

        // Check if chat_conversations exists for LEAST(me, target) and GREATEST(me, target)
        $userOne = min($currentUserId, $targetId);
        $userTwo = max($currentUserId, $targetId);

        $stmtConv = $pdo->prepare("
            SELECT id FROM chat_conversations 
            WHERE user_one_id = ? AND user_two_id = ? 
            LIMIT 1
        ");
        $stmtConv->execute([$userOne, $userTwo]);
        $convRow = $stmtConv->fetch();

        if ($convRow) {
            $conversationId = (int) $convRow['id'];
        } else {
            $stmtCreateConv = $pdo->prepare("
                INSERT INTO chat_conversations (user_one_id, user_two_id, last_message_at) 
                VALUES (?, ?, CURRENT_TIMESTAMP)
            ");
            $stmtCreateConv->execute([$userOne, $userTwo]);
            $conversationId = (int) $pdo->lastInsertId();
        }

        $newStatus = 'accepted';

    } elseif ($action === 'decline') {
        // Decline incoming request
        $stmt = $pdo->prepare("
            DELETE FROM user_follows 
            WHERE follower_id = ? AND following_id = ? AND status = 'pending'
        ");
        $stmt->execute([$targetId, $currentUserId]);
        $newStatus = 'none';

    } elseif ($action === 'unfollow') {
        // Remove follow relations between both users
        $stmt = $pdo->prepare("
            DELETE FROM user_follows 
            WHERE (follower_id = ? AND following_id = ?) 
               OR (follower_id = ? AND following_id = ?)
        ");
        $stmt->execute([$currentUserId, $targetId, $targetId, $currentUserId]);
        $newStatus = 'none';
    }

    echo json_encode([
        'success' => true,
        'action' => $action,
        'new_status' => $newStatus,
        'target_id' => $targetId,
        'conversation_id' => $conversationId
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
