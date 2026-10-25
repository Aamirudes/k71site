<?php
header('Content-Type: application/json; charset=utf-8');

$findRoots = [
    dirname(dirname(dirname(__DIR__))),
    dirname(dirname(__DIR__)),
    __DIR__ . '/../../../',
    __DIR__ . '/../../',
];
$laravelRoot = null;
foreach ($findRoots as $root) {
    if (file_exists($root . '/vendor/autoload.php') && file_exists($root . '/bootstrap/app.php')) {
        $laravelRoot = $root;
        break;
    }
}

if ($laravelRoot) {
    require_once $laravelRoot . '/vendor/autoload.php';
    $app = require_once $laravelRoot . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    $request = \Illuminate\Http\Request::capture();
    $controller = $app->make(\App\Http\Controllers\MessageController::class);
    $response = $controller->apiDeleteConversation($request);
    $response->send();
    exit;
}

http_response_code(500);
echo json_encode(['success' => false, 'message' => 'Application root not found']);
exit;
