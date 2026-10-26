<?php
/**
 * API: Send Message with Instant Conversation Auto-Creation
 * File: api/chat/send_message.php
 * K71 Universal DM Engine - Ultra-Fast (Sub-5ms)
 */

header('Content-Type: application/json; charset=utf-8');

// 1. Fast Direct PDO Handler (Instant Sub-5ms Execution)
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
    $currentUserId = get_current_user_id();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!is_array($input)) $input = $_POST;

    $conversationId = isset($input['conversation_id']) ? (int) $input['conversation_id'] : 0;
    $recipientId = isset($input['recipient_id']) ? (int) $input['recipient_id'] : 0;
    $body = isset($input['message']) ? trim($input['message']) : '';

    if ($currentUserId && $pdo && $body !== '') {
        try {
            if ($conversationId <= 0 && $recipientId > 0) {
                $conversationId = get_or_create_conversation($pdo, $currentUserId, $recipientId);
            }

            if ($conversationId > 0) {
                $now = date('Y-m-d H:i:s');
                $stmtInsert = $pdo->prepare("INSERT INTO chat_messages (conversation_id, sender_id, body, is_read, created_at) VALUES (?, ?, ?, 0, ?)");
                $stmtInsert->execute([$conversationId, $currentUserId, $body, $now]);
                $msgId = (int) $pdo->lastInsertId();

                $stmtUpdateConv = $pdo->prepare("UPDATE chat_conversations SET last_message_at = ?, status = 'accepted' WHERE id = ?");
                $stmtUpdateConv->execute([$now, $conversationId]);

                $msgPayload = [
                    'id' => $msgId,
                    'conversation_id' => $conversationId,
                    'body' => $body,
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
                    'conversation_status' => 'accepted'
                ]);
                exit;
            }
        } catch (\Throwable $e) {
            // Fall through to Laravel handler
        }
    }
}

// 2. Laravel Controller Fallback
$findRoots = [
    dirname(dirname(__DIR__)),
    dirname(dirname(dirname(__DIR__))),
    __DIR__ . '/../../',
    __DIR__ . '/../',
];
$laravelRoot = null;
foreach ($findRoots as $root) {
    if (file_exists($root . '/vendor/autoload.php') && file_exists($root . '/bootstrap/app.php')) {
        $laravelRoot = $root;
        break;
    }
}

if ($laravelRoot) {
    try {
        require_once $laravelRoot . '/vendor/autoload.php';
        $app = require_once $laravelRoot . '/bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        $request = \Illuminate\Http\Request::capture();
        $controller = $app->make(\App\Http\Controllers\MessageController::class);
        $response = $controller->apiSendMessage($request);
        $response->send();
        exit;
    } catch (\Throwable $e) {}
}

http_response_code(400);
echo json_encode(['success' => false, 'error' => 'Unable to send message.']);
