<?php
/**
 * One-Click Web Artisan Deployment Runner for Hostinger
 * Usage: Visit https://marblemandi.org/deploy.php?token=marblemandi2026
 */

$secretToken = 'marblemandi2026';

if (($_GET['token'] ?? '') !== $secretToken) {
    http_response_code(403);
    die('<h2 style="font-family:sans-serif;color:red;text-align:center;margin-top:50px;">Access Denied. Pass the correct ?token= to run.</h2>');
}

// Allow script to run
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>K71 Live Deployment Runner</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 30px; margin: 0; }
        .card { max-width: 800px; margin: 0 auto; background: #1e293b; border-radius: 16px; padding: 24px; border: 1px solid #334155; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5); }
        h1 { margin-top: 0; font-size: 22px; color: #38bdf8; display: flex; align-items: center; gap: 10px; }
        pre { background: #020617; padding: 16px; border-radius: 8px; border: 1px solid #1e293b; color: #4ade80; font-size: 13px; overflow-x: auto; line-height: 1.5; }
        .step { margin-bottom: 20px; }
        .step-title { font-weight: bold; font-size: 14px; color: #cbd5e1; margin-bottom: 6px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 9999px; font-size: 11px; font-weight: bold; background: #059669; color: white; margin-bottom: 15px; }
        .btn-delete { background: #ef4444; color: white; text-decoration: none; padding: 10px 20px; border-radius: 8px; font-weight: bold; display: inline-block; margin-top: 15px; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 K71 Live Setup & Database Migration</h1>
    <span class="badge">HOSTINGER AUTO-DEPLOYMENT</span>
<?php

$baseDir = dirname(__DIR__);
if (!file_exists($baseDir . '/vendor/autoload.php')) {
    echo '<div style="color:#f87171;">Error: vendor/autoload.php not found. Composer dependencies may still be installing.</div>';
    echo '</div></body></html>';
    exit;
}

require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;

$commands = [
    'Key Generate' => ['command' => 'key:generate', 'params' => ['--force' => true]],
    'Database Migration (Creates All Tables)' => ['command' => 'migrate', 'params' => ['--force' => true]],
    'Storage Link' => ['command' => 'storage:link', 'params' => []],
    'Optimize & Cache Configuration' => ['command' => 'config:cache', 'params' => []],
    'Cache Routes' => ['command' => 'route:cache', 'params' => []],
    'Cache Blade Views' => ['command' => 'view:cache', 'params' => []],
];

foreach ($commands as $label => $task) {
    echo "<div class='step'>";
    echo "<div class='step-title'>⏳ Running: {$label}...</div>";
    try {
        Artisan::call($task['command'], $task['params']);
        $output = Artisan::output();
        echo "<pre>" . htmlspecialchars(trim($output) ?: 'Command completed successfully with 0 output.') . "</pre>";
    } catch (\Throwable $e) {
        echo "<pre style='color:#f87171;'>ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    echo "</div>";
}

if (isset($_GET['self_delete'])) {
    @unlink(__FILE__);
    echo "<p style='color:#4ade80;font-weight:bold;'>✅ deploy.php has been permanently deleted for security.</p>";
} else {
    echo "<p style='color:#38bdf8;'>Setup complete! You can now visit your website:</p>";
    echo "<a href='/' style='color:#38bdf8;font-weight:bold;margin-right:20px;'>Visit Homepage &rarr;</a>";
    echo "<a href='?token={$secretToken}&self_delete=1' class='btn-delete'>Delete This Setup File (Recommended)</a>";
}
?>
</div>
</body>
</html>
