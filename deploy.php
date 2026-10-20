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
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>MarbleMandi Live Setup & Auto-Diagnostics</title>
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
        .btn-purple { background: #8b5cf6; color: white; }
        .btn-red { background: #ef4444; color: white; }
        .error { color: #f87171 !important; font-weight: bold; }
        .success { color: #4ade80 !important; font-weight: bold; }
    </style>
</head>
<body>
<div class="card">
    <h1>🚀 MarbleMandi Live Setup & Auto-Diagnostics</h1>
    <span class="badge">HOSTINGER AUTO-DEPLOYMENT & REPAIR</span>
<?php

$baseDir = file_exists(__DIR__ . '/artisan') ? __DIR__ : (file_exists(dirname(__DIR__) . '/artisan') ? dirname(__DIR__) : __DIR__);

// 0. Auto-Pull from GitHub via Zip if requested or via shell if available
$syncRequested = isset($_GET['sync']) || isset($_GET['pull']);
if ($syncRequested) {
    echo "<div class='step'><div class='step-title'>🔄 Synchronizing Code Directly from GitHub (k71site)...</div>";
    $zipUrl = 'https://github.com/Aamirudes/k71site/archive/refs/heads/main.zip';
    $storageDir = $baseDir . '/storage';
    if (!is_dir($storageDir)) {
        @mkdir($storageDir, 0775, true);
    }
    $tempZip = $storageDir . '/github_latest.zip';
    
    // Download using curl
    $downloadSuccess = false;
    if (function_exists('curl_init')) {
        $fp = @fopen($tempZip, 'w+');
        if ($fp) {
            $ch = curl_init($zipUrl);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'MarbleMandi-Deployer');
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            fclose($fp);
            if ($httpCode === 200 && file_exists($tempZip) && filesize($tempZip) > 1000) {
                $downloadSuccess = true;
            }
        }
    }
    
    if (!$downloadSuccess) {
        $zipData = @file_get_contents($zipUrl, false, stream_context_create([
            'http' => ['header' => "User-Agent: MarbleMandi-Deployer\r\n", 'follow_location' => 1, 'timeout' => 120],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]));
        if ($zipData && strlen($zipData) > 1000) {
            file_put_contents($tempZip, $zipData);
            $downloadSuccess = true;
        }
    }
    
    if ($downloadSuccess && class_exists('ZipArchive')) {
        $zip = new ZipArchive;
        if ($zip->open($tempZip) === TRUE) {
            $extractDir = $baseDir . '/storage/github_extract';
            if (!file_exists($extractDir)) {
                @mkdir($extractDir, 0775, true);
            }
            $zip->extractTo($extractDir);
            $zip->close();
            @unlink($tempZip);
            
            // Move folders: app, config, database, resources, routes, public (except storage and .env)
            $subdirs = glob($extractDir . '/*', GLOB_ONLYDIR);
            $sourceDir = !empty($subdirs) ? $subdirs[0] : ($extractDir . '/k71site-main');
            if (file_exists($sourceDir)) {
                $copyDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'public', 'api', 'includes', 'sql'];
                foreach ($copyDirs as $d) {
                    $src = $sourceDir . '/' . $d;
                    $dst = $baseDir . '/' . $d;
                    if (file_exists($src)) {
                        $iterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::SELF_FIRST
                        );
                        foreach ($iterator as $item) {
                            $subPath = $iterator->getSubPathName();
                            $targetPath = $dst . DIRECTORY_SEPARATOR . $subPath;
                            if ($item->isDir()) {
                                if (!file_exists($targetPath)) {
                                    @mkdir($targetPath, 0775, true);
                                }
                            } else {
                                // Do not overwrite .env or public/storage
                                if ($subPath === '.env' || str_starts_with($subPath, 'storage')) {
                                    continue;
                                }
                                @copy($item, $targetPath);
                            }
                        }
                    }
                }
                
                // Copy root standalone files if present
                $standaloneFiles = ['index.php', 'deploy.php', 'messages.php', 'db.php'];
                foreach ($standaloneFiles as $sf) {
                    if (file_exists($sourceDir . '/' . $sf)) {
                        @copy($sourceDir . '/' . $sf, $baseDir . '/' . $sf);
                        if (file_exists($baseDir . '/public')) {
                            @copy($sourceDir . '/' . $sf, $baseDir . '/public/' . $sf);
                        }
                    }
                }
                
                echo "<pre class='success'>✅ Successfully updated all application code & chat files from GitHub main branch!</pre>";
            } else {
                echo "<pre class='error'>Error: Archive directory structure not recognized.</pre>";
            }
        } else {
            echo "<pre class='error'>Error opening downloaded zip archive.</pre>";
        }
    } elseif ($downloadSuccess) {
        echo "<pre class='error'>ZipArchive extension not available on this PHP installation.</pre>";
    } else {
        echo "<pre class='error'>Failed to download GitHub code archive.</pre>";
    }
    echo "</div>";
}

// 1. Remove stale cached bootstrap configs
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

// 2. Ensure root index.php forwards cleanly to public/index.php
$rootIndex = $baseDir . '/index.php';
$rootIndexContent = "<?php\n\n/**\n * Laravel - Shared Hosting Gateway\n */\nif (!defined('LARAVEL_START')) {\n    define('LARAVEL_START', microtime(true));\n}\nrequire_once __DIR__ . '/public/index.php';\n";
@file_put_contents($rootIndex, $rootIndexContent);

// 3. Ensure root .htaccess routes everything to public/
$rootHtaccess = $baseDir . '/.htaccess';
$rootHtaccessContent = <<<HTACCESS
<IfModule mod_rewrite.c>
    RewriteEngine On
    Options -Indexes

    # Block access to hidden and sensitive files
    RewriteRule ^(\.env|\.git|composer\.(json|lock)|package\.(json|lock)|artisan) - [F,L,NC]
    RewriteRule ^(app|bootstrap|config|database|resources|routes|storage/app|storage/logs|tests|docs|legacy)(/.*)?$ - [F,L,NC]
    RewriteRule ^vendor/(autoload\.php|composer/|bin/|.*\.json|.*\.lock|.*\.md) - [F,L,NC]

    # Direct access for existing root files (like deploy.php)
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]

    # Rewrite all traffic to public/
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS;
@file_put_contents($rootHtaccess, $rootHtaccessContent);

// 4. Clean up any leftover WordPress files in public_html
$staleWpFiles = [
    $baseDir . '/wp-blog-header.php',
    $baseDir . '/wp-config.php',
    $baseDir . '/wp-load.php',
    $baseDir . '/wp-settings.php',
    $baseDir . '/wp-cron.php',
    $baseDir . '/license.txt',
    $baseDir . '/readme.html',
    $baseDir . '/xmlrpc.php',
];
foreach ($staleWpFiles as $wpf) {
    if (file_exists($wpf)) {
        @unlink($wpf);
    }
}

// 5. Ensure .env has valid settings
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
    
    // Auto-sync K71 brand identity and SMTP settings to .env
    $updateEnv = function(&$content, $key, $val) {
        if (preg_match('/^' . preg_quote($key, '/') . '=.*/m', $content)) {
            $content = preg_replace('/^' . preg_quote($key, '/') . '=.*/m', "{$key}={$val}", $content);
        } else {
            $content .= "\n{$key}={$val}\n";
        }
    };

    $updateEnv($envContent, 'APP_NAME', 'K71');
    $updateEnv($envContent, 'MARKETPLACE_NAME', 'K71');
    $updateEnv($envContent, 'MAIL_MAILER', 'smtp');
    $updateEnv($envContent, 'MAIL_HOST', 'smtp.gmail.com');
    $updateEnv($envContent, 'MAIL_PORT', '587');
    $updateEnv($envContent, 'MAIL_USERNAME', 'hashir0867@gmail.com');
    $updateEnv($envContent, 'MAIL_PASSWORD', '"mhuj gjdv rjjy kqwl"');
    $updateEnv($envContent, 'MAIL_ENCRYPTION', 'tls');
    $updateEnv($envContent, 'MAIL_FROM_ADDRESS', '"crab.hashir@gmail.com"');
    $updateEnv($envContent, 'MAIL_FROM_NAME', '"K71"');
    $updateEnv($envContent, 'ADMIN_EMAIL', 'crab.hashir@gmail.com');
    $updateEnv($envContent, 'QUEUE_CONNECTION', 'sync');
    $updateEnv($envContent, 'MARKETPLACE_USE_SUBDOMAINS', 'true');

    @file_put_contents($envPath, $envContent);
}

// 6. Ensure storage directories exist
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

// 7. Symlink storage
$target = $baseDir . '/storage/app/public';
$link = $baseDir . '/public/storage';
if (!file_exists($link) && function_exists('symlink')) {
    try {
        @symlink($target, $link);
    } catch (\Throwable $e) {}
}

// 8. Bootstrap Laravel
if (!file_exists($baseDir . '/vendor/autoload.php')) {
    echo '<div class="error">Error: vendor/autoload.php not found. Composer dependencies missing.</div></div></body></html>';
    exit;
}

require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

if (isset($_GET['read_log'])) {
    header('Content-Type: text/plain');
    $logFile = $baseDir . '/storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $lines = file($logFile);
        echo implode("", array_slice($lines, -50));
    } else {
        echo "Log file does not exist\n";
    }
    exit;
}

if (isset($_GET['diag'])) {
    echo "<h1>Live Diagnostics</h1><pre>";
    $rawHost = $_SERVER['HTTP_HOST'] ?? 'unknown';
    echo "Raw HTTP_HOST: " . $rawHost . "\n";
    echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'unknown') . "\n";
    
    echo "\n--- ALL HTTP HEADERS & RELEVANT SERVER VARS ---\n";
    foreach ($_SERVER as $k => $v) {
        if (str_starts_with($k, 'HTTP_') || in_array($k, ['SERVER_NAME', 'REQUEST_URI', 'REDIRECT_URL', 'SCRIPT_URI', 'SERVER_ADDR', 'REMOTE_ADDR'])) {
            echo "{$k} = " . (is_string($v) ? $v : json_encode($v)) . "\n";
        }
    }
    
    $extracted = k71_extract_subdomain($rawHost);
    echo "\nk71_extract_subdomain: " . ($extracted ?? 'NULL') . "\n";
    echo "k71_root_domain: " . k71_root_domain($rawHost) . "\n";
    echo "</pre>";
    exit;
}

if (isset($_GET['test_run'])) {
    echo "<h1>Live Request Simulator</h1><pre>";
    $targetPath = $_GET['path'] ?? '/';
    echo "Testing path: {$targetPath}\n\n";
    try {
        $httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $simReq = Illuminate\Http\Request::create($targetPath, 'GET');
        $simRes = $httpKernel->handle($simReq);
        echo "HTTP Status: " . $simRes->getStatusCode() . "\n";
        echo "Headers: " . print_r($simRes->headers->all(), true) . "\n";
        echo "Content preview (first 1000 chars):\n" . htmlspecialchars(substr($simRes->getContent(), 0, 1000)) . "\n";
    } catch (\Throwable $e) {
        echo "EXCEPTION CAUGHT:\n" . $e->getMessage() . "\n\n";
        echo "In " . $e->getFile() . " on line " . $e->getLine() . "\n\n";
        echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
    }
    echo "</pre>";
    exit;
}

// Diagnostic: Check APP_KEY
$appKey = config('app.key');
echo "<div class='step'><div class='step-title'>🔑 Application Encryption Key:</div>";
if (empty($appKey)) {
    echo "<pre class='error'>APP_KEY is empty! Generating now...</pre>";
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

// 9. Harden Database Schema Directly (Expands SEO columns to avoid MySQL String Truncation 1406 errors)
try {
    DB::statement("ALTER TABLE `categories` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");
    DB::statement("ALTER TABLE `vendors` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");
    DB::statement("ALTER TABLE `products` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");
    DB::statement("ALTER TABLE `pages` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");
    echo "<div class='step'><div class='step-title'>🛠️ Database Schema Hardening:</div><pre class='success'>SEO columns in categories, products, vendors, and pages expanded to VARCHAR(255)/TEXT (truncation-proof).</pre></div>";
} catch (\Throwable $e) {
    // Tables may not exist prior to initial migration; will be handled by migrations
}

// 10. Run Migrations & Seeders
$tasks = [
    'Database Migration (Creates & updates tables)' => ['cmd' => 'migrate', 'args' => ['--force' => true]],
    'Seed Database with Live Products, Real Stores & Categories' => ['cmd' => 'db:seed', 'args' => ['--force' => true]],
    'Clear All Stale Caches' => ['cmd' => 'optimize:clear', 'args' => []],
];

foreach ($tasks as $label => $task) {
    echo "<div class='step'><div class='step-title'>⏳ Running: {$label}...</div>";
    try {
        Artisan::call($task['cmd'], $task['args']);
        echo "<pre class='success'>" . htmlspecialchars(trim(Artisan::output()) ?: 'DONE') . "</pre>";
    } catch (\Throwable $e) {
        echo "<pre class='error'>ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    echo "</div>";
}

// 11. Enforce Master Administrator Credentials
echo "<div class='step'><div class='step-title'>👤 Enforcing Master Administrator Account:</div>";
try {
    $admin = \App\Models\User::firstOrNew(['email' => 'crab.hashir@gmail.com']);
    $admin->first_name = 'Hashir';
    $admin->last_name = 'Admin';
    $admin->username = 'masteradmin';
    $admin->password = \Illuminate\Support\Facades\Hash::make('Hashir1284@0');
    $admin->is_blocked = 0;
    $admin->blocked_reason = null;
    $admin->email_verified_at = $admin->email_verified_at ?: now();
    $admin->save();

    $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
    $custRole = \App\Models\Role::firstOrCreate(['name' => 'customer']);
    $admin->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);

    echo "<pre class='success'>✅ Master Administrator verified:\n   Email: crab.hashir@gmail.com\n   Role: admin\n   Password: Hashir1284@0\n   User ID: {$admin->id}</pre>";
} catch (\Throwable $e) {
    echo "<pre class='error'>Failed to enforce admin account: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
echo "</div>";

// 12. Optional SMTP Email Test Runner
if (isset($_GET['test_mail'])) {
    echo "<div class='step'><div class='step-title'>📧 Testing Live SMTP Connection to smtp.gmail.com:</div>";
    try {
        $transport = new \Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport(
            'smtp.gmail.com',
            587,
            false
        );
        $transport->setUsername('hashir0867@gmail.com');
        $transport->setPassword('mhujgjdvrjjykqwl');
        $transport->start();
        
        $mailer = new \Symfony\Component\Mailer\Mailer($transport);
        $email = (new \Symfony\Component\Mime\Email())
            ->from(new \Symfony\Component\Mime\Address('crab.hashir@gmail.com', 'K71 Platform'))
            ->to('crab.hashir@gmail.com')
            ->subject('K71 Live SMTP Diagnostic Success')
            ->text('This is a test notification confirming that the K71 Google Workspace/Gmail SMTP connection is active and authenticated.');

        $mailer->send($email);
        echo "<pre class='success'>✅ SMTP Success! Sent test email to crab.hashir@gmail.com via smtp.gmail.com (Auth: hashir0867@gmail.com).</pre>";
    } catch (\Throwable $e) {
        echo "<pre class='error'>❌ SMTP Test Failed: " . htmlspecialchars($e->getMessage()) . "</pre>";
    }
    echo "</div>";
}

// Diagnostic: Show Request Debug Log if exists
$reqDebugLog = $baseDir . '/storage/logs/request_debug.log';
if (file_exists($reqDebugLog)) {
    echo "<div class='step'><div class='step-title'>🌐 Request Debug Log:</div>";
    echo "<pre style='color:#38bdf8;font-size:12px;max-height:200px;overflow-y:auto;'>" . htmlspecialchars(file_get_contents($reqDebugLog)) . "</pre>";
    echo "</div>";
}

// Diagnostic: Show Last 30 Lines of Laravel Log if there are errors
$logFile = $baseDir . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -35);
    echo "<div class='step'><div class='step-title'>📋 Latest Laravel Log Entries:</div>";
    echo "<pre style='color:#cbd5e1;font-size:12px;max-height:250px;overflow-y:auto;'>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
    echo "</div>";
}

if (isset($_GET['self_delete'])) {
    @unlink(__FILE__);
    if (file_exists($baseDir . '/deploy.php')) {
        @unlink($baseDir . '/deploy.php');
    }
    echo "<p class='success'>✅ deploy.php has been permanently deleted for security.</p>";
} else {
    echo "<div style='margin-top:20px; display:flex; flex-wrap:wrap; gap:10px;'>";
    echo "<a href='/?t=" . time() . "' class='btn btn-blue'>🚀 Visit Homepage &rarr;</a>";
    echo "<a href='?token={$secretToken}&sync=1' class='btn btn-purple'>🔄 Pull & Sync Latest Code from GitHub</a>";
    echo "<a href='?token={$secretToken}&test_mail=1' class='btn btn-blue'>📧 Send Test SMTP Email</a>";
    echo "<a href='?token={$secretToken}&self_delete=1' class='btn btn-red' onclick='return confirm(\"Are you sure you want to delete deploy.php?\");'>🗑️ Delete This Setup File</a>";
    echo "</div>";
}
?>
</div>
</body>
</html>
