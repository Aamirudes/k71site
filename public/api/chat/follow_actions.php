<?php
/**
 * API: Follow Actions & Request Accept/Decline Bridge
 * File: api/chat/follow_actions.php
 * K71 Universal DM Engine
 */

header('Content-Type: application/json; charset=utf-8');

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
        $response = $controller->apiFollowActions($request);
        $response->send();
        exit;
    } catch (\Throwable $e) {}
}

echo json_encode(['success' => true, 'action' => 'completed']);
