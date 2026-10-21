<?php
/**
 * API: Search Users & Instant Conversation Initialization
 * File: api/chat/search_users.php
 * K71 Universal Search & DM Requests
 */

header('Content-Type: application/json; charset=utf-8');

// Locate existing db.php
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
require_once $dbPath;

$currentUserId = require_auth();

// Support POST action=start_or_find to create/get conversation immediately
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rawInput = file_get_contents('php://input');
    $jsonData = json_decode($rawInput, true);

    $targetUserId = isset($jsonData['target_user_id']) ? (int) $jsonData['target_user_id'] : (int) ($_POST['target_user_id'] ?? 0);
    $action = trim((string)($jsonData['action'] ?? ($_POST['action'] ?? '')));

    if ($targetUserId <= 0 || $targetUserId === $currentUserId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid target user ID.'
        ]);
        exit;
    }

    try {
        // Check if conversation already exists (order-independent)
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
            $convId = (int)$conv['id'];
            $convStatus = $conv['status'];
            $requestedBy = (int)$conv['requested_by'];

            // If conversation was declined or closed, reopen as pending
            if ($convStatus === 'declined') {
                $stmtReopen = $pdo->prepare("UPDATE chat_conversations SET status = 'pending', requested_by = ? WHERE id = ?");
                $stmtReopen->execute([$currentUserId, $convId]);
                $convStatus = 'pending';
                $requestedBy = $currentUserId;
            }

            echo json_encode([
                'success' => true,
                'conversation_id' => $convId,
                'status' => $convStatus,
                'requested_by' => $requestedBy,
                'is_new' => false
            ]);
            exit;
        }

        // Create new conversation with pending status
        $userOne = min($currentUserId, $targetUserId);
        $userTwo = max($currentUserId, $targetUserId);

        $stmtInsert = $pdo->prepare("
            INSERT INTO chat_conversations (user_one_id, user_two_id, status, requested_by, last_message_at) 
            VALUES (?, ?, 'pending', ?, CURRENT_TIMESTAMP)
        ");
        $stmtInsert->execute([$userOne, $userTwo, $currentUserId]);
        $newConvId = (int)$pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'conversation_id' => $newConvId,
            'status' => 'pending',
            'requested_by' => $currentUserId,
            'is_new' => true
        ]);
        exit;

    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// GET: User Search
$query = trim($_GET['q'] ?? '');

if (mb_strlen($query) < 1) {
    echo json_encode([
        'success' => true,
        'data' => []
    ]);
    exit;
}

try {
    $searchWildcard = '%' . $query . '%';
    $terms = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);
    $term1 = $terms[0] ?? $query;
    $term2 = $terms[1] ?? $query;

    // Portable SQL query working on both MySQL and SQLite
    $stmt = $pdo->prepare("
        SELECT id, username, first_name, last_name, avatar, email 
        FROM users 
        WHERE id != :me 
          AND deleted_at IS NULL
          AND (
              username LIKE :q1 
              OR first_name LIKE :q2 
              OR last_name LIKE :q3
              OR (first_name LIKE :t1 AND last_name LIKE :t2)
          )
        ORDER BY 
            CASE 
                WHEN username = :exact THEN 1
                WHEN username LIKE :starts THEN 2
                ELSE 3 
            END,
            id DESC
        LIMIT 25
    ");

    $stmt->execute([
        ':me' => $currentUserId,
        ':q1' => $searchWildcard,
        ':q2' => $searchWildcard,
        ':q3' => $searchWildcard,
        ':t1' => '%' . $term1 . '%',
        ':t2' => '%' . $term2 . '%',
        ':exact' => $query,
        ':starts' => $query . '%',
    ]);

    $matchedUsers = $stmt->fetchAll();
    $results = [];

    // Prepared statements for conversation and follow status
    $stmtConv = $pdo->prepare("
        SELECT id, status, requested_by 
        FROM chat_conversations 
        WHERE (user_one_id = :u1 AND user_two_id = :u2) 
           OR (user_one_id = :u2 AND user_two_id = :u1)
        LIMIT 1
    ");

    $stmtOutgoing = $pdo->prepare("SELECT status FROM user_follows WHERE follower_id = :me AND following_id = :target LIMIT 1");
    $stmtIncoming = $pdo->prepare("SELECT status FROM user_follows WHERE follower_id = :target AND following_id = :me LIMIT 1");

    foreach ($matchedUsers as $user) {
        $targetId = (int) $user['id'];

        // 1. Follow status
        $stmtOutgoing->execute([':me' => $currentUserId, ':target' => $targetId]);
        $outgoingStatus = $stmtOutgoing->fetchColumn();

        $stmtIncoming->execute([':me' => $currentUserId, ':target' => $targetId]);
        $incomingStatus = $stmtIncoming->fetchColumn();

        $followStatus = 'none';
        if ($outgoingStatus === 'accepted' || $incomingStatus === 'accepted') {
            $followStatus = 'accepted';
        } elseif ($outgoingStatus === 'pending') {
            $followStatus = 'pending_outgoing';
        } elseif ($incomingStatus === 'pending') {
            $followStatus = 'pending_incoming';
        }

        // 2. Existing conversation check
        $stmtConv->execute([':u1' => $currentUserId, ':u2' => $targetId]);
        $convRow = $stmtConv->fetch();

        $conversationId = null;
        $conversationStatus = 'none';

        if ($convRow) {
            $conversationId = (int)$convRow['id'];
            $cStatus = $convRow['status'] ?? 'accepted';
            $cReqBy = (int)($convRow['requested_by'] ?? 0);

            if ($cStatus === 'accepted') {
                $conversationStatus = 'accepted';
            } elseif ($cStatus === 'pending') {
                $conversationStatus = ($cReqBy === $currentUserId) ? 'pending_sent' : 'pending_received';
            } else {
                $conversationStatus = $cStatus;
            }
        }

        $fullName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($fullName === '') {
            $fullName = $user['username'] ?? ('user_' . $targetId);
        }

        $results[] = [
            'id' => $targetId,
            'username' => $user['username'] ?? ('user_' . $targetId),
            'name' => $fullName,
            'profile_pic' => format_user_avatar($user['avatar'] ?? null, $fullName),
            'follow_status' => $followStatus,
            'conversation_id' => $conversationId,
            'conversation_status' => $conversationStatus,
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $results
    ]);

} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
