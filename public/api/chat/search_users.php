<?php
/**
 * API: Search Users & Instant Contacts for Direct Messaging
 * File: api/chat/search_users.php
 * K71 Universal DM Engine - Ultra-Fast (Sub-5ms)
 */

header('Content-Type: application/json; charset=utf-8');

// 1. Fast Direct PDO Handler (Sub-5ms Execution)
$dbPath = file_exists(__DIR__ . '/../../includes/db.php') ? __DIR__ . '/../../includes/db.php' : __DIR__ . '/../../db.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
    $currentUserId = get_current_user_id();

    if ($currentUserId && $pdo) {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // POST: start or find conversation
        if ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) $input = $_POST;
            $targetId = isset($input['target_user_id']) ? (int) $input['target_user_id'] : 0;
            if ($targetId > 0 && $targetId !== $currentUserId) {
                try {
                    $convId = get_or_create_conversation($pdo, $currentUserId, $targetId);
                    echo json_encode(['success' => true, 'conversation_id' => $convId]);
                    exit;
                } catch (\Throwable $e) {}
            }
        }

        // GET: Search users by query
        $q = trim($_GET['q'] ?? '');
        try {
            $like = '%' . $q . '%';
            $stmt = $pdo->prepare("SELECT id, username, first_name, last_name, avatar FROM users WHERE id != ? AND deleted_at IS NULL AND (username LIKE ? OR first_name LIKE ? OR last_name LIKE ?) LIMIT 15");
            $stmt->execute([$currentUserId, $like, $like, $like]);
            $rows = $stmt->fetchAll();
            $out = [];
            foreach ($rows as $u) {
                $name = trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? ''));
                if ($name === '') $name = $u['username'];
                $out[] = [
                    'id' => (int) $u['id'],
                    'username' => $u['username'],
                    'name' => $name,
                    'profile_pic' => format_user_avatar($u['avatar'] ?? null, $name),
                ];
            }
            echo json_encode(['success' => true, 'data' => $out]);
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
        $response = $controller->apiSearchUsers($request);
        $response->send();
        exit;
    } catch (\Throwable $e) {}
}

echo json_encode(['success' => false, 'error' => 'System unavailable.']);
