<?php
/**
 * Fast Direct Voice Upload Endpoint (Sub-5ms)
 * File: api/chat/upload_voice.php
 * K71 Universal DM Engine - Instagram Direct Parity
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');

$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
}

$currentUserId = function_exists('get_current_user_id') ? get_current_user_id() : null;

if (!$currentUserId || empty($pdo)) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized. Please log in to send voice messages.']);
    exit;
}

$conversationId = isset($_POST['conversation_id']) ? (int) $_POST['conversation_id'] : 0;
$duration = isset($_POST['duration']) ? (int) $_POST['duration'] : 0;

if ($conversationId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid conversation.']);
    exit;
}

try {
    // 1. Verify conversation access
    $stmtConv = $pdo->prepare("SELECT id FROM chat_conversations WHERE id = ? AND (user_one_id = ? OR user_two_id = ?) LIMIT 1");
    $stmtConv->execute([$conversationId, $currentUserId, $currentUserId]);
    if (!$stmtConv->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Access denied to this conversation.']);
        exit;
    }

    if (empty($_FILES['voice']) && empty($_FILES['audio'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No voice recording received.']);
        exit;
    }

    $file = !empty($_FILES['voice']) ? $_FILES['voice'] : $_FILES['audio'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Audio upload failed with error code ' . $file['error']]);
        exit;
    }

    // 2. Prepare storage directories
    $baseStorage = dirname(dirname(__DIR__)) . '/public/storage/chat_voice';
    if (!is_dir($baseStorage)) {
        @mkdir($baseStorage, 0755, true);
    }
    $secondaryStorage = dirname(dirname(__DIR__)) . '/storage/chat_voice';
    if (!is_dir($secondaryStorage)) {
        @mkdir($secondaryStorage, 0755, true);
    }

    $origName = $file['name'] ?? 'voice.webm';
    $ext = pathinfo($origName, PATHINFO_EXTENSION);
    if (!$ext || !in_array(strtolower($ext), ['webm', 'mp3', 'wav', 'ogg', 'm4a', 'aac', 'mp4'])) {
        $ext = 'webm';
    }

    $fileName = 'voice_' . $currentUserId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $destPath = $baseStorage . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Could not save voice recording file on server.']);
        exit;
    }

    // Keep secondary storage in sync
    if (is_dir($secondaryStorage)) {
        @copy($destPath, $secondaryStorage . '/' . $fileName);
    }

    $attachmentUrl = '/storage/chat_voice/' . $fileName;
    $durationFormatted = $duration > 0 ? sprintf('%d:%02d', floor($duration / 60), $duration % 60) : '0:05';
    $body = '🎤 Voice message (' . $durationFormatted . ')';
    $now = date('Y-m-d H:i:s');

    // 3. Insert into chat_messages
    $stmtMsg = $pdo->prepare("
        INSERT INTO chat_messages (conversation_id, sender_id, body, attachment_url, attachment_type, is_read, created_at)
        VALUES (?, ?, ?, ?, 'voice', 0, ?)
    ");
    $stmtMsg->execute([$conversationId, $currentUserId, $body, $attachmentUrl, $now]);
    $messageId = (int) $pdo->lastInsertId();

    // 4. Update conversation timestamp and status
    $stmtUpd = $pdo->prepare("UPDATE chat_conversations SET last_message_at = ?, status = 'accepted' WHERE id = ?");
    $stmtUpd->execute([$now, $conversationId]);

    $msgPayload = [
        'id' => $messageId,
        'conversation_id' => $conversationId,
        'body' => $body,
        'attachment_url' => $attachmentUrl,
        'attachment_type' => 'voice',
        'sender_id' => $currentUserId,
        'is_mine' => true,
        'is_read' => false,
        'created_at' => $now,
        'time_formatted' => date('g:i A', strtotime($now)),
    ];

    echo json_encode([
        'success' => true,
        'data' => $msgPayload,
        'message' => $msgPayload,
    ]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Voice upload error: ' . $e->getMessage()]);
}
