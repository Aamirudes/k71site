<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$request = Request::capture();
file_put_contents(
    __DIR__ . '/../storage/logs/request_debug.log',
    date('Y-m-d H:i:s') . " | HOST: " . $request->getHost() . " | SERVER_HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? '') . " | URI: " . $request->getRequestUri() . " | IP: " . $request->ip() . "\n",
    FILE_APPEND
);

$app->handleRequest($request);
