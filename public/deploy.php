<?php
/**
 * One-Click Web Artisan Deployment Runner & Diagnostics for Hostinger
 * Usage: Visit https://marblemandi.org/deploy.php?token=marblemandi2026
 */

$secretToken = 'marblemandi2026';

if (($_GET['token'] ?? '') !== $secretToken) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:red;text-align:center;margin-top:50px;">Access Denied. Pass the correct ?token= to run.</h2>');
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>K71 Live Deployment Runner & Diagnostics</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
        .card { max-width: 900px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 24px; border: 1px solid #334155; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; font-size: 22px; color: #38bdf8; display: flex; align-items: center; gap: 10px; }
        pre { background: #020617; padding: 16px; border-radius: 8px; border: 1px solid #1e293b; color: #4ade80; font-size: 13px; overflow-x: auto; line-height: 1.5; white-space: pre-wrap; word-break: break-all; }
        .step { margin-bottom: 20px; }
        .step-title { font-weight: bold; font-size: 14px; color: #cbd5e1; margin-bottom: 6px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: bold; background: #059669; color: white; margin-bottom: 15px; }
        .btn { text-decoration: none; padding: 10px 18px; border-radius: 8px; font-weight: bold; display: inline-block; margin-top: 15px; margin-right: 10px; }
        .btn-blue { background: #0284c7; color: white; }
        .btn-red { background: #ef4444; color: white; }
        .error { color: #f87171 !important; font-weight: bold; }
        .success { color: #4ade80 !important; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 K71 Live Setup & Auto-Diagnostics</h1>
    <span class="badge">HOSTINGER AUTO-DEPLOYMENT</span>
<?php

$baseDir = dirname(__DIR__);
if (!file_exists($baseDir . '/vendor/autoload.php')) {
    echo '<div class="error">Error: vendor/autoload.php not found. Composer dependencies may still be installing.</div>';
    echo '</div></body></html>';
    exit;
}

// 1. Remove stale cached configs first so fresh .env is loaded
$staleCacheFiles = [
    $baseDir . '/bootstrap/cache/config.php',
    $baseDir . '/bootstrap/cache/routes-v7.php',
    $baseDir . '/bootstrap/cache/services.php',
    $baseDir . '/bootstrap/cache/packages.php',
];
foreach ($staleCacheFiles as $f) {
    if (file_exists($f)) {
        @unlink($f);
    }
}

// 1b. Ensure .env has a valid APP_KEY and MARKETPLACE_USE_SUBDOMAINS=false
$envPath = $baseDir . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    if (!str_contains($envContent, 'APP_KEY=base64:') || preg_match('/APP_KEY=\s*$/m', $envContent)) {
        if (preg_match('/^APP_KEY=.*$/m', $envContent)) {
            $envContent = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=base64:H2KViLNYSJmCwl6wQA/CHr0Nz7QEEWgWPIjdOkYpW8E=', $envContent);
        } else {
            $envContent .= "\nAPP_KEY=base64:H2KViLNYSJmCwl6wQA/CHr0Nz7QEEWgWPIjdOkYpW8E=\n";
        }
    }
    // Ensure subdomains are enabled
    if (str_contains($envContent, 'MARKETPLACE_USE_SUBDOMAINS=false')) {
        $envContent = str_replace('MARKETPLACE_USE_SUBDOMAINS=false', 'MARKETPLACE_USE_SUBDOMAINS=true', $envContent);
    } elseif (!str_contains($envContent, 'MARKETPLACE_USE_SUBDOMAINS=')) {
        $envContent .= "\nMARKETPLACE_USE_SUBDOMAINS=true\n";
    }
    @file_put_contents($envPath, $envContent);
}

// 2. Ensure storage directories exist and are writable
$storageDirs = [
    $baseDir . '/storage/framework/views',
    $baseDir . '/storage/framework/sessions',
    $baseDir . '/storage/framework/cache',
    $baseDir . '/storage/framework/cache/data',
    $baseDir . '/storage/logs',
    $baseDir . '/storage/app/public',
    $baseDir . '/bootstrap/cache',
];
foreach ($storageDirs as $dir) {
    if (!file_exists($dir)) {
        @mkdir($dir, 0775, true);
    }
    @chmod($dir, 0775);
}

// 3. Fallback pure PHP symlink for storage (if host allows symlink)
$target = $baseDir . '/storage/app/public';
$link = $baseDir . '/public/storage';
if (!file_exists($link) && function_exists('symlink')) {
    try {
        @symlink($target, $link);
    } catch (\Throwable $e) {}
}

// 4. Bootstrap Laravel cleanly
require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// Diagnostic: Check APP_KEY
$appKey = config('app.key');
echo "<div class='step'><div class='step-title'>🔑 Application Encryption Key:</div>";
if (empty($appKey)) {
    echo "<pre class='error'>APP_KEY is currently empty! Generating now...</pre>";
    Artisan::call('key:generate', ['--force' => true]);
    echo "<pre class='success'>" . htmlspecialchars(Artisan::output()) . "</pre>";
} else {
    echo "<pre class='success'>APP_KEY is present: " . htmlspecialchars(substr($appKey, 0, 15)) . "...</pre>";
}
echo "</div>";

// Diagnostic: Check Database Connection
echo "<div class='step'><div class='step-title'>🗄️ Database Connection Status:</div>";
try {
    DB::connection()->getPdo();
    $dbName = DB::connection()->getDatabaseName();
    echo "<pre class='success'>Connected to database: " . htmlspecialchars($dbName) . "</pre>";
} catch (\Throwable $e) {
    echo "<pre class='error'>Database Error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
echo "</div>";

// Run Migrations & Seeders
$tasks = [
    'Database Migration (Creates & updates tables)' => ['cmd' => 'migrate', 'args' => ['--force' => true]],
    'Clear All Stale Caches' => ['cmd' => 'optimize:clear', 'args' => []],
];

foreach ($tasks as $label => $task) {
    echo "<div class='step'><div class='step-title'>⏳ Running: {$label}...</div>";
    try {
        Artisan::call($task['cmd'], $task['args']);
        echo "<pre class='success'>" . htmlspecialchars(trim(Artisan::output()) ?: 'OK') . "</pre>";
    } catch (\Throwable $e) {
        echo "<pre class='error'>ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    echo "</div>";
}

// Diagnostic: Show Last 30 Lines of Laravel Log (to catch the exact 500 error cause!)
echo "<div class='step'><div class='step-title'>📋 Latest Laravel Errors from storage/logs/laravel.log:</div>";
$logFile = $baseDir . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -40);
    echo "<pre style='color:#f8fafc;font-size:12px;max-height:300px;overflow-y:auto;'>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
} else {
    echo "<pre class='success'>No log file created yet (no fatal errors logged).</pre>";
}
echo "</div>";

if (isset($_GET['self_delete'])) {
    @unlink(__FILE__);
    echo "<p class='success'>✅ deploy.php has been permanently deleted for security.</p>";
} else {
    echo "<div style='margin-top:20px;'>";
    echo "<a href='/' class='btn btn-blue'>🚀 Visit Homepage &rarr;</a>";
    echo "<a href='?token={$secretToken}&self_delete=1' class='btn btn-red'>🗑️ Delete This Setup File</a>";
    echo "</div>";
}
?>
</div>
</body>
</html>
