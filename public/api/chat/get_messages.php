<?php
/**
 * API: Get Messages for Conversation with Security & Read Status Sync
 * File: api/chat/get_messages.php
 * K71 Universal DM Engine
 */

header('Content-Type: application/json; charset=utf-8');

// 1. Forward directly to Laravel MessageController
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
        $response = $controller->apiGetMessages($request);
        $response->send();
        exit;
    } catch (\Throwable $e) {}
}

// 2. Standalone Fallback
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
    $currentUserId = require_auth();
    $conversationId = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : 0;
    if ($conversationId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Valid conversation_id is required.']);
        exit;
    }

    try {
        if (!$pdo) throw new Exception("Database connection unavailable.");
        $stmtConv = $pdo->prepare("SELECT id, user_one_id, user_two_id, last_message_at FROM chat_conversations WHERE id = ? AND (user_one_id = ? OR user_two_id = ?) LIMIT 1");
        $stmtConv->execute([$conversationId, $currentUserId, $currentUserId]);
        $conv = $stmtConv->fetch();
        if (!$conv) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Conversation not found or access denied.']);
            exit;
        }

        $recipientId = ((int) $conv['user_one_id'] === $currentUserId) ? (int) $conv['user_two_id'] : (int) $conv['user_one_id'];

        $stmtMarkRead = $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ? AND is_read = 0");
        $stmtMarkRead->execute([$conversationId, $currentUserId]);

        $stmtMessages = $pdo->prepare("SELECT id, conversation_id, sender_id, body, is_read, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY id ASC");
        $stmtMessages->execute([$conversationId]);
        $rawMessages = $stmtMessages->fetchAll();

        $formattedMessages = [];
        foreach ($rawMessages as $msg) {
            $senderId = (int) $msg['sender_id'];
            $timeStr = $msg['created_at'];
            $formattedMessages[] = [
                'id' => (int) $msg['id'],
                'body' => $msg['body'],
                'sender_id' => $senderId,
                'is_mine' => ($senderId === $currentUserId),
                'is_read' => (bool) $msg['is_read'],
                'created_at' => $timeStr,
                'time_formatted' => date('g:i A', strtotime($timeStr)),
                'date_header' => date('M j, Y', strtotime($timeStr)),
            ];
        }

        $recipientInfo = get_chat_user_info($pdo, $recipientId);

        echo json_encode([
            'success' => true,
            'data' => [
                'conversation' => ['id' => $conversationId, 'last_message_at' => $conv['last_message_at']],
                'recipient' => $recipientInfo,
                'messages' => $formattedMessages
            ],
            'conversation' => ['id' => $conversationId, 'last_message_at' => $conv['last_message_at']],
            'recipient' => $recipientInfo,
            'messages' => $formattedMessages
        ]);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'System unavailable.']);
