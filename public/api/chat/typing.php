<?php
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$uid = 0;
$pdo = null;

$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
    if (function_exists('get_current_user_id')) {
        $uid = (int) get_current_user_id();
    }
}

if (!$uid && !empty($input['user_id'])) {
    $uid = (int) $input['user_id'];
}

$convId = isset($input['conversation_id']) ? (int) $input['conversation_id'] : 0;

if ($convId > 0 && $uid > 0) {
    // 1. Filesystem sync
    $storageDir = dirname(__DIR__, 2) . '/storage/framework/typing';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0777, true);
    }
    
    $file1 = $storageDir . "/k71_typing_{$convId}_{$uid}";
    $file2 = sys_get_temp_dir() . "/k71_typing_{$convId}_{$uid}";

    if (!empty($input['stop'])) {
        @unlink($file1);
        @unlink($file2);
        if ($pdo) {
            try {
                $st = $pdo->prepare("UPDATE chat_conversations SET typing_user_id = NULL, typing_at = NULL WHERE id = ? AND typing_user_id = ?");
                $st->execute([$convId, $uid]);
            } catch (\Throwable $e) {}
        }
    } else {
        @file_put_contents($file1, time());
        @file_put_contents($file2, time());
        if ($pdo) {
            try {
                $st = $pdo->prepare("UPDATE chat_conversations SET typing_user_id = ?, typing_at = ? WHERE id = ?");
                $st->execute([$uid, date('Y-m-d H:i:s'), $convId]);
            } catch (\Throwable $e) {}
        }
    }
}

echo json_encode(['success' => true]);
exit;
