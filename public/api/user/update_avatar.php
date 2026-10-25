<?php
/**
 * Standalone & Laravel API proxy for customer avatar upload
 * File: api/user/update_avatar.php
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
        $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

        $request = \Illuminate\Http\Request::capture();
        $response = $kernel->handle($request);
        
        if ($response->getStatusCode() === 404) {
            $controller = $app->make(\App\Http\Controllers\AccountController::class);
            $response = $controller->updateAvatar($request);
        }
        $response->send();
        $kernel->terminate($request, $response);
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['success' => false, 'error' => 'Application bootstrap failed.']);
