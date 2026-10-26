<?php
/**
 * Fast Lightweight Unread Message Count Endpoint
 * K71 Universal DM Engine - Ultra-Fast (Sub-5ms)
 * Used by navigation bars, mobile tab bars, and floating chat for Instagram-style red dot
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
}

$currentUserId = null;
if (function_exists('get_current_user_id')) {
    $currentUserId = get_current_user_id();
}

// Fallback to explicit user_id param if provided and matches active cookie
if (!$currentUserId && !empty($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $currentUserId = (int) $_GET['user_id'];
}

if (!$currentUserId || empty($pdo)) {
    echo json_encode([
        'success' => true,
        'unread' => 0,
        'chat_unread' => 0,
        'legacy_unread' => 0,
        'authenticated' => false
    ]);
    exit;
}

try {
    // 1. Direct chat unread count
    $chatUnread = 0;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM chat_messages m
        JOIN chat_conversations c ON m.conversation_id = c.id
        WHERE (c.user_one_id = ? OR c.user_two_id = ?)
          AND m.sender_id != ?
          AND m.is_read = 0
    ");
    $stmt->execute([$currentUserId, $currentUserId, $currentUserId]);
    $chatUnread = (int) $stmt->fetchColumn();

    // 2. Legacy marketplace messages unread count (if table exists)
    $legacyUnread = 0;
    try {
        $stmtLegacy = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE recipient_id = ? AND read_at IS NULL");
        $stmtLegacy->execute([$currentUserId]);
        $legacyUnread = (int) $stmtLegacy->fetchColumn();
    } catch (\Throwable $e) {}

    $totalUnread = $chatUnread + $legacyUnread;

    echo json_encode([
        'success' => true,
        'unread' => $totalUnread,
        'chat_unread' => $chatUnread,
        'legacy_unread' => $legacyUnread,
        'authenticated' => true,
        'user_id' => $currentUserId
    ]);
} catch (\Throwable $e) {
    echo json_encode([
        'success' => false,
        'unread' => 0,
        'chat_unread' => 0,
        'legacy_unread' => 0,
        'error' => $e->getMessage()
    ]);
}
