<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$request = \Illuminate\Http\Request::capture();
$controller = $app->make(\App\Http\Controllers\MessageController::class);
$response = $controller->apiDeleteConversation($request);
$response->send();
exit;
