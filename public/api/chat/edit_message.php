<?php
/**
 * API: Edit Chat Message
 * File: api/chat/edit_message.php
 * K71 Universal DM Engine - Ultra-Fast (Sub-5ms)
 */

header('Content-Type: application/json; charset=utf-8');

$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';

$currentUserId = 0;
$pdo = null;

if (file_exists($dbPath)) {
    require_once $dbPath;
    if (function_exists('get_current_user_id')) {
        $currentUserId = (int) get_current_user_id();
    }
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

if (!$currentUserId && !empty($input['user_id'])) {
    $currentUserId = (int) $input['user_id'];
}

$messageId = isset($input['message_id']) ? (int) $input['message_id'] : 0;
$newBody = isset($input['new_body']) ? trim($input['new_body']) : (isset($input['body']) ? trim($input['body']) : '');

if ($messageId <= 0 || $newBody === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Message ID and new body text are required.']);
    exit;
}

if (!$currentUserId) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Authentication required to edit messages.']);
    exit;
}

if ($pdo) {
    try {
        // Verify message ownership
        $stmtCheck = $pdo->prepare("SELECT id, conversation_id, sender_id, body FROM chat_messages WHERE id = ? LIMIT 1");
        $stmtCheck->execute([$messageId]);
        $msg = $stmtCheck->fetch();

        if (!$msg) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Message not found.']);
            exit;
        }

        if ((int) $msg['sender_id'] !== $currentUserId) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'You can only edit your own messages.']);
            exit;
        }

        $now = date('Y-m-d H:i:s');
        try {
            $stmtUpdate = $pdo->prepare("UPDATE chat_messages SET body = ?, is_edited = 1, edited_at = ? WHERE id = ?");
            $stmtUpdate->execute([$newBody, $now, $messageId]);
        } catch (\Throwable $colErr) {
            $stmtUpdate = $pdo->prepare("UPDATE chat_messages SET body = ? WHERE id = ?");
            $stmtUpdate->execute([$newBody, $messageId]);
        }

        echo json_encode([
            'success' => true,
            'message_id' => $messageId,
            'conversation_id' => (int) $msg['conversation_id'],
            'new_body' => $newBody,
            'is_edited' => true,
            'edited_at' => $now
        ]);
        exit;
    } catch (\Throwable $e) {
        // Fall through to Laravel
    }
}

// Laravel fallback
try {
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
    $app = require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(\Illuminate\Http\Request::capture());

    $authId = \Illuminate\Support\Facades\Auth::id() ?: $currentUserId;
    if (!$authId) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit;
    }

    $msg = \Illuminate\Support\Facades\DB::table('chat_messages')->where('id', $messageId)->first();
    if (!$msg) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Message not found.']);
        exit;
    }

    if ((int) $msg->sender_id !== (int) $authId) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'You can only edit your own messages.']);
        exit;
    }

    $now = date('Y-m-d H:i:s');
    \Illuminate\Support\Facades\DB::table('chat_messages')->where('id', $messageId)->update([
        'body' => $newBody,
        'is_edited' => true,
        'edited_at' => $now
    ]);

    echo json_encode([
        'success' => true,
        'message_id' => $messageId,
        'conversation_id' => (int) $msg->conversation_id,
        'new_body' => $newBody,
        'is_edited' => true,
        'edited_at' => $now
    ]);
    exit;
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}
