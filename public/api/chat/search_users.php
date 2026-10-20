<?php
/**
 * API: Search Users with Instagram Follow Status Detection
 * File: api/chat/search_users.php
 */

header('Content-Type: application/json; charset=utf-8');

// Locate existing db.php
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
require_once $dbPath;

$currentUserId = require_auth();

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

    // Search users by username, first_name, last_name, or full name
    $stmt = $pdo->prepare("
        SELECT id, username, first_name, last_name, avatar, email 
        FROM users 
        WHERE id != :me 
          AND deleted_at IS NULL
          AND (
              username LIKE :q1 
              OR first_name LIKE :q2 
              OR last_name LIKE :q3
              OR (first_name || ' ' || last_name) LIKE :q4
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
        ':q4' => $searchWildcard,
        ':exact' => $query,
        ':starts' => $query . '%',
    ]);

    $matchedUsers = $stmt->fetchAll();
    $results = [];

    // Prepared statements for follow status
    $stmtOutgoing = $pdo->prepare("SELECT status FROM user_follows WHERE follower_id = :me AND following_id = :target LIMIT 1");
    $stmtIncoming = $pdo->prepare("SELECT status FROM user_follows WHERE follower_id = :target AND following_id = :me LIMIT 1");

    foreach ($matchedUsers as $user) {
        $targetId = (int) $user['id'];

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
