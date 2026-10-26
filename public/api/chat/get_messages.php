<?php
/**
 * API: Get Messages for Conversation with Security & Read Status Sync
 * File: api/chat/get_messages.php
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
    if ($currentUserId && $pdo) {
        $conversationId = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : 0;
        if ($conversationId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Valid conversation_id is required.']);
            exit;
        }

        try {
            $stmtConv = $pdo->prepare("SELECT id, user_one_id, user_two_id, status, requested_by, last_message_at FROM chat_conversations WHERE id = ? AND (user_one_id = ? OR user_two_id = ?) LIMIT 1");
            $stmtConv->execute([$conversationId, $currentUserId, $currentUserId]);
            $conv = $stmtConv->fetch();
            if (!$conv) {
                http_response_code(404);
                echo json_encode(['success' => false, 'error' => 'Conversation not found or access denied.']);
                exit;
            }

            $recipientId = ((int) $conv['user_one_id'] === $currentUserId) ? (int) $conv['user_two_id'] : (int) $conv['user_one_id'];

            // Mark unread messages as read only if unread exist
            $stmtMarkRead = $pdo->prepare("UPDATE chat_messages SET is_read = 1 WHERE conversation_id = ? AND sender_id != ? AND is_read = 0");
            $stmtMarkRead->execute([$conversationId, $currentUserId]);

            // Fetch last 100 messages
            $stmtMessages = $pdo->prepare("SELECT id, conversation_id, sender_id, body, attachment_url, attachment_type, is_read, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY id DESC LIMIT 100");
            $stmtMessages->execute([$conversationId]);
            $rawMessages = array_reverse($stmtMessages->fetchAll());

            $formattedMessages = [];
            foreach ($rawMessages as $msg) {
                $senderId = (int) $msg['sender_id'];
                $timeStr = $msg['created_at'];
                $formattedMessages[] = [
                    'id' => (int) $msg['id'],
                    'body' => $msg['body'],
                    'attachment_url' => $msg['attachment_url'] ?? null,
                    'attachment_type' => $msg['attachment_type'] ?? null,
                    'sender_id' => $senderId,
                    'is_mine' => ($senderId === $currentUserId),
                    'is_read' => (bool) $msg['is_read'],
                    'created_at' => $timeStr,
                    'time_formatted' => date('g:i A', strtotime($timeStr)),
                    'date_header' => date('M j, Y', strtotime($timeStr)),
                ];
            }

            $recipientInfo = get_chat_user_info($pdo, $recipientId);

            $convData = [
                'id' => $conversationId,
                'status' => $conv['status'] ?? 'accepted',
                'requested_by' => $conv['requested_by'] ? (int)$conv['requested_by'] : null,
                'last_message_at' => $conv['last_message_at'],
                'is_incoming_request' => (($conv['status'] ?? 'accepted') === 'pending' && (int)($conv['requested_by'] ?? 0) !== $currentUserId),
            ];

            echo json_encode([
                'success' => true,
                'conversation' => $convData,
                'recipient' => $recipientInfo,
                'is_typing' => false,
                'messages' => $formattedMessages,
                'data' => [
                    'conversation' => $convData,
                    'recipient' => $recipientInfo,
                    'is_typing' => false,
                    'messages' => $formattedMessages
                ]
            ]);
            exit;
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
        $response = $controller->apiGetMessages($request);
        $response->send();
        exit;
    } catch (\Throwable $e) {}
}

echo json_encode(['success' => false, 'error' => 'System unavailable.']);
