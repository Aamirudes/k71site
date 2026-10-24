<?php
/**
 * API: Search Users & Instant Conversation Initialization
 * File: api/chat/search_users.php
 * K71 Universal Search & DM Engine
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
            SELECT id, user_one_id, user_two_id, status 
            FROM chat_conversations 
            WHERE (user_one_id = :u1 AND user_two_id = :u2) 
               OR (user_one_id = :u2 AND user_two_id = :u1)
            LIMIT 1
        ");
        $stmtFind->execute([':u1' => $currentUserId, ':u2' => $targetUserId]);
        $conv = $stmtFind->fetch();

        if ($conv) {
            $convId = (int)$conv['id'];
            echo json_encode([
                'success' => true,
                'conversation_id' => $convId,
                'status' => 'accepted',
                'is_new' => false
            ]);
            exit;
        }

        // Create new conversation with accepted status so both can chat immediately
        $userOne = min($currentUserId, $targetUserId);
        $userTwo = max($currentUserId, $targetUserId);

        $now = date('Y-m-d H:i:s');
        $stmtInsert = $pdo->prepare("
            INSERT INTO chat_conversations (user_one_id, user_two_id, status, requested_by, last_message_at) 
            VALUES (?, ?, 'accepted', ?, ?)
        ");
        $stmtInsert->execute([$userOne, $userTwo, $currentUserId, $now]);
        $newConvId = (int)$pdo->lastInsertId();

        echo json_encode([
            'success' => true,
            'conversation_id' => $newConvId,
            'status' => 'accepted',
            'requested_by' => $currentUserId,
            'is_new' => true
        ]);
        exit;

    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
        exit;
    }
}

// GET: User & Vendor Search
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
    $term1 = '%' . ($terms[0] ?? $query) . '%';
    $term2 = '%' . ($terms[1] ?? ($terms[0] ?? $query)) . '%';

    // Portable SQL query working on both MySQL and SQLite
    $stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.username, 
            u.first_name, 
            u.last_name, 
            u.avatar, 
            u.email,
            v.id AS vendor_id,
            v.shop_name
        FROM users u
        LEFT JOIN vendors v ON v.user_id = u.id
        WHERE u.id != :me 
          AND u.deleted_at IS NULL
          AND (
              u.username LIKE :q1 
              OR u.first_name LIKE :q2 
              OR u.last_name LIKE :q3
              OR u.email LIKE :q4
              OR v.shop_name LIKE :q5
              OR (u.first_name LIKE :t1 AND u.last_name LIKE :t2)
              OR (v.shop_name LIKE :t1 AND v.shop_name LIKE :t2)
          )
        ORDER BY 
            CASE 
                WHEN v.shop_name LIKE :starts THEN 1
                WHEN u.username LIKE :starts THEN 2
                ELSE 3 
            END,
            u.id DESC
        LIMIT 25
    ");

    $stmt->execute([
        ':me' => $currentUserId,
        ':q1' => $searchWildcard,
        ':q2' => $searchWildcard,
        ':q3' => $searchWildcard,
        ':q4' => $searchWildcard,
        ':q5' => $searchWildcard,
        ':t1' => $term1,
        ':t2' => $term2,
        ':starts' => $query . '%',
    ]);

    $matchedUsers = $stmt->fetchAll();
    $results = [];

    $stmtConv = $pdo->prepare("
        SELECT id, status 
        FROM chat_conversations 
        WHERE (user_one_id = :u1 AND user_two_id = :u2) 
           OR (user_one_id = :u2 AND user_two_id = :u1)
        LIMIT 1
    ");

    foreach ($matchedUsers as $user) {
        $targetId = (int) $user['id'];

        $stmtConv->execute([':u1' => $currentUserId, ':u2' => $targetId]);
        $convRow = $stmtConv->fetch();

        $conversationId = $convRow ? (int)$convRow['id'] : null;

        $personName = trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''));
        if ($personName === '') {
            $personName = $user['username'] ?? ('User #' . $targetId);
        }

        $displayName = $personName;
        if (!empty($user['shop_name'])) {
            $displayName = $user['shop_name'] . ' (' . $personName . ')';
        }

        $results[] = [
            'id' => $targetId,
            'username' => $user['username'] ?? ('user_' . $targetId),
            'name' => $displayName,
            'shop_name' => $user['shop_name'] ?? null,
            'profile_pic' => format_user_avatar($user['avatar'] ?? null, $displayName),
            'conversation_id' => $conversationId,
            'follow_status' => 'accepted', // Auto-accepted for e-commerce
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
