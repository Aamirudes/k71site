<?php
header('Content-Type: application/json; charset=utf-8');

$dbPath = file_exists(__DIR__ . '/../../includes/db.php') 
    ? __DIR__ . '/../../includes/db.php' 
    : __DIR__ . '/../../db.php';

if (file_exists($dbPath)) {
    require_once $dbPath;
    $uid = get_current_user_id();
    if ($uid) {
        $input = json_decode(file_get_contents('php://input'), true);
        $convId = isset($input['conversation_id']) ? (int) $input['conversation_id'] : 0;
        if ($convId > 0) {
            $typingFile = sys_get_temp_dir() . "/k71_typing_{$convId}_{$uid}";
            @file_put_contents($typingFile, time());
            echo json_encode(['success' => true]);
            exit;
        }
    }
}

echo json_encode(['success' => true]);
exit;
