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

            // Chunked pagination support
            $beforeId = isset($_GET['before_id']) ? (int) $_GET['before_id'] : 0;
            $limit = isset($_GET['limit']) ? min(60, max(15, (int) $_GET['limit'])) : 40;

            $sql = "SELECT id, conversation_id, sender_id, body, attachment_url, attachment_type, reply_to_id, reply_to_body, reply_to_sender_name, is_read, created_at FROM chat_messages WHERE conversation_id = ? ";
            $params = [$conversationId];
            if ($beforeId > 0) {
                $sql .= "AND id < ? ";
                $params[] = $beforeId;
            }
            $sql .= "ORDER BY id DESC LIMIT " . $limit;

            try {
                $stmtMessages = $pdo->prepare($sql);
                $stmtMessages->execute($params);
                $rawMessages = array_reverse($stmtMessages->fetchAll());
            } catch (\Throwable $queryErr) {
                // Fallback query if optional columns are absent
                $fallbackSql = "SELECT id, conversation_id, sender_id, body, is_read, created_at FROM chat_messages WHERE conversation_id = ? ";
                $fallbackParams = [$conversationId];
                if ($beforeId > 0) {
                    $fallbackSql .= "AND id < ? ";
                    $fallbackParams[] = $beforeId;
                }
                $fallbackSql .= "ORDER BY id DESC LIMIT " . $limit;
                $stmtMessages = $pdo->prepare($fallbackSql);
                $stmtMessages->execute($fallbackParams);
                $rawMessages = array_reverse($stmtMessages->fetchAll());
            }

            $hasMore = count($rawMessages) >= $limit;

            $formattedMessages = [];
            foreach ($rawMessages as $msg) {
                $senderId = (int) $msg['sender_id'];
                $timeStr = $msg['created_at'];
                $formattedMessages[] = [
                    'id' => (int) $msg['id'],
                    'body' => $msg['body'],
                    'attachment_url' => $msg['attachment_url'] ?? null,
                    'attachment_type' => $msg['attachment_type'] ?? null,
                    'reply_to_id' => !empty($msg['reply_to_id']) ? (int)$msg['reply_to_id'] : null,
                    'reply_to_body' => $msg['reply_to_body'] ?? null,
                    'reply_to_sender_name' => $msg['reply_to_sender_name'] ?? null,
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
                'has_more' => $hasMore,
                'conversation' => $convData,
                'recipient' => $recipientInfo,
                'is_typing' => false,
                'messages' => $formattedMessages,
                'data' => [
                    'has_more' => $hasMore,
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
