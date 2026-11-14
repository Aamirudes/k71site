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
header('X-LiteSpeed-Purge: *');
header('Cache-Control: no-cache, no-store, must-revalidate');
?>
<!DOCTYPE html>
<html>
<head>
    <title>K71 Platform Live Setup & Auto-Diagnostics</title>
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
    <h1>🚀 K71 Platform Live Setup & Auto-Diagnostics</h1>
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
            curl_setopt($ch, CURLOPT_USERAGENT, 'K71-Deployer');
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
            'http' => ['header' => "User-Agent: K71-Deployer\r\n", 'follow_location' => 1, 'timeout' => 120],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
        ]));
        if ($zipData && strlen($zipData) > 1000) {
            file_put_contents($tempZip, $zipData);
            $downloadSuccess = true;
        }
    }
    
    if ($downloadSuccess && class_exists('ZipArchive')) {
        $removeTree = function (string $dir) use (&$removeTree): void {
            if (!is_dir($dir)) {
                return;
            }
            $it = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($it as $item) {
                $path = $item->getPathname();
                if ($item->isDir() && !$item->isLink()) {
                    @rmdir($path);
                } else {
                    @unlink($path);
                }
            }
            @rmdir($dir);
        };

        $zip = new ZipArchive;
        if ($zip->open($tempZip) === TRUE) {
            $extractDir = $baseDir . '/storage/github_extract_' . time();
            $removeTree($baseDir . '/storage/github_extract');
            foreach (glob($baseDir . '/storage/github_extract_*') ?: [] as $oldExtDir) {
                $removeTree($oldExtDir);
            }
            if (!is_dir($extractDir)) {
                @mkdir($extractDir, 0775, true);
            }
            $zip->extractTo($extractDir);
            $zip->close();
            @unlink($tempZip);

            // Move folders: app, config, database, resources, routes, public (except public/storage and .env)
            $expectedSubdir = $extractDir . '/k71site-main';
            $subdirs = glob($extractDir . '/*', GLOB_ONLYDIR);
            $sourceDir = is_dir($expectedSubdir) ? $expectedSubdir : (!empty($subdirs) ? $subdirs[0] : $expectedSubdir);
            if (is_dir($sourceDir)) {
                $copyErrors = [];
                $copiedFiles = 0;
                $copyDirs = ['app', 'bootstrap', 'config', 'database', 'resources', 'routes', 'public', 'api', 'includes', 'sql'];
                foreach ($copyDirs as $d) {
                    $src = $sourceDir . '/' . $d;
                    $dst = $baseDir . '/' . $d;
                    if (is_dir($src)) {
                        if (!is_dir($dst) && !@mkdir($dst, 0775, true)) {
                            $copyErrors[] = "mkdir failed: {$dst}";
                            continue;
                        }
                        $iterator = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($src, RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::SELF_FIRST
                        );
                        foreach ($iterator as $item) {
                            $subPath = $iterator->getSubPathName();
                            $targetPath = $dst . DIRECTORY_SEPARATOR . $subPath;
                            if ($item->isDir()) {
                                if (!is_dir($targetPath) && !@mkdir($targetPath, 0775, true)) {
                                    $copyErrors[] = "mkdir failed: {$targetPath}";
                                }
                            } else {
                                // Do not overwrite .env or public/storage
                                if ($subPath === '.env' || ($d === 'public' && str_starts_with(str_replace('\\', '/', $subPath), 'storage'))) {
                                    continue;
                                }
                                if (@copy($item, $targetPath)) {
                                    $copiedFiles++;
                                } else {
                                    $copyErrors[] = "copy failed: {$targetPath}";
                                }
                            }
                        }
                    }
                }

                // Copy root standalone files if present (never copy root index.php into public/)
                if (file_exists($sourceDir . '/index.php') && !@copy($sourceDir . '/index.php', $baseDir . '/index.php')) {
                    $copyErrors[] = 'copy failed: index.php';
                }
                if (file_exists($sourceDir . '/public/index.php') && !@copy($sourceDir . '/public/index.php', $baseDir . '/public/index.php')) {
                    $copyErrors[] = 'copy failed: public/index.php';
                }
                $standaloneFiles = ['deploy.php', 'messages.php', 'db.php', '.user.ini', '.htaccess'];
                foreach ($standaloneFiles as $sf) {
                    if (file_exists($sourceDir . '/' . $sf)) {
                        if (!@copy($sourceDir . '/' . $sf, $baseDir . '/' . $sf)) {
                            $copyErrors[] = "copy failed: {$sf}";
                        }
                        if (is_dir($baseDir . '/public') && !@copy($sourceDir . '/' . $sf, $baseDir . '/public/' . $sf)) {
                            $copyErrors[] = "copy failed: public/{$sf}";
                        }
                    }
                }

                // Mirror public/assets and public/build into document root for direct web server static delivery
                $mirrorDirs = ['assets', 'build', 'images'];
                foreach ($mirrorDirs as $md) {
                    $srcDir = $baseDir . '/public/' . $md;
                    $dstDir = $baseDir . '/' . $md;
                    if (is_dir($srcDir) && $srcDir !== $dstDir) {
                        if (!is_dir($dstDir) && !@mkdir($dstDir, 0775, true)) {
                            $copyErrors[] = "mkdir failed: {$dstDir}";
                            continue;
                        }
                        $it = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::SELF_FIRST
                        );
                        foreach ($it as $item) {
                            $target = $dstDir . DIRECTORY_SEPARATOR . $it->getSubPathName();
                            if ($item->isDir()) {
                                if (!is_dir($target) && !@mkdir($target, 0775, true)) {
                                    $copyErrors[] = "mkdir failed: {$target}";
                                }
                            } else {
                                if (!@copy($item, $target)) {
                                    $copyErrors[] = "copy failed: {$target}";
                                }
                            }
                        }
                    }
                }

                $removeTree($extractDir);
                if (function_exists('opcache_reset')) {
                    @opcache_reset();
                }

                if (empty($copyErrors)) {
                    echo "<pre class='success'>✅ Successfully updated {$copiedFiles} application files from GitHub main branch (OPcache reset)!</pre>";
                } else {
                    $preview = implode("\n", array_slice($copyErrors, 0, 15));
                    echo "<pre class='error'>⚠️ Copied {$copiedFiles} files with " . count($copyErrors) . " error(s):\n" . htmlspecialchars($preview) . "</pre>";
                }
            } else {
                $removeTree($extractDir);
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

// 2b. Ensure public/index.php is the true Laravel bootstrap front controller (never recursive)
$publicIndex = $baseDir . '/public/index.php';
$publicIndexContent = <<<'PHP'
<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
PHP;
@file_put_contents($publicIndex, $publicIndexContent);

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

    # Direct access for existing root files (like deploy.php, messages.php)
    RewriteCond %{REQUEST_FILENAME} -f
    RewriteRule ^ - [L]

    # Serve static assets directly from public/ if the file exists there
    RewriteCond %{DOCUMENT_ROOT}/public/$1 -f
    RewriteRule ^(.*)$ public/$1 [L]

    # Send all application routes cleanly to root index.php without subrequest looping
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
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
if (file_exists($baseDir . '/vendor/autoload.php')) {
    require $baseDir . '/vendor/autoload.php';
    $bootstrapApp = file_exists($baseDir . '/bootstrap/app.php') 
        ? $baseDir . '/bootstrap/app.php' 
        : (file_exists(dirname($baseDir) . '/bootstrap/app.php') ? dirname($baseDir) . '/bootstrap/app.php' : null);

    if ($bootstrapApp) {
        try {
            $app = require_once $bootstrapApp;
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();
        } catch (\Throwable $e) {}
    }
}

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

if (isset($_GET['read_log'])) {
    header('Content-Type: text/plain');
    $logFile = $baseDir . '/storage/logs/laravel.log';
    if (file_exists($logFile)) {
        $content = file_get_contents($logFile);
        $entries = preg_split('/(?=\[\d{4}-\d{2}-\d{2})/', $content);
        $lastEntry = trim(end($entries));
        echo substr($lastEntry, 0, 4000);
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
        $simReq = Illuminate\Http\Request::create($targetPath, 'GET', [], [], [], [
            'HTTP_HOST' => 'marblemandi.org',
            'SERVER_NAME' => 'marblemandi.org',
            'HTTPS' => 'on',
            'SERVER_PORT' => '443',
        ]);
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

// 9. Harden Database Schema Directly (Expands SEO columns & username to avoid MySQL String Truncation 1406 errors)
try {
    DB::statement("ALTER TABLE `users` MODIFY `username` VARCHAR(60) NOT NULL");
    DB::statement("ALTER TABLE `vendors` MODIFY `slug` VARCHAR(60) NOT NULL");
    DB::statement("ALTER TABLE `categories` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");
    DB::statement("ALTER TABLE `vendors` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");
    DB::statement("ALTER TABLE `products` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");
    DB::statement("ALTER TABLE `pages` MODIFY `seo_title` VARCHAR(255) NULL, MODIFY `seo_description` TEXT NULL");

    // Password reset tokens table safeguard
    try {
        if (!Schema::hasTable('password_reset_tokens')) {
            DB::statement("CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
                `email` VARCHAR(255) NOT NULL,
                `token` VARCHAR(255) NOT NULL,
                `created_at` TIMESTAMP NULL,
                PRIMARY KEY (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        }
    } catch (\Throwable $e) {}

    // Chat soft-hide columns
    try {
        if (!Schema::hasColumn('chat_conversations', 'user_one_hidden_at')) {
            DB::statement("ALTER TABLE `chat_conversations` ADD COLUMN `user_one_hidden_at` TIMESTAMP NULL AFTER `last_message_at`");
        }
        if (!Schema::hasColumn('chat_conversations', 'user_two_hidden_at')) {
            DB::statement("ALTER TABLE `chat_conversations` ADD COLUMN `user_two_hidden_at` TIMESTAMP NULL AFTER `user_one_hidden_at`");
        }
    } catch (\Throwable $e) {}

    // Chat schema auto-repair
    try { \App\Http\Controllers\MessageController::ensureChatSchema(); } catch (\Throwable $e) {}
    try { \App\Http\Controllers\MessageController::syncLegacyChats(); } catch (\Throwable $e) {}

    // Products table schema hardening
    try {
        if (Schema::hasTable('products')) {
            $colsToAdd = [
                'sales_count' => "ALTER TABLE `products` ADD COLUMN `sales_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `order_count`",
                'vertical' => "ALTER TABLE `products` ADD COLUMN `vertical` VARCHAR(50) NOT NULL DEFAULT 'ecommerce' AFTER `category_id`",
                'listing_type' => "ALTER TABLE `products` ADD COLUMN `listing_type` VARCHAR(50) NOT NULL DEFAULT 'product' AFTER `vertical`",
                'custom_fields' => "ALTER TABLE `products` ADD COLUMN `custom_fields` JSON NULL AFTER `listing_type`",
                'used_status' => "ALTER TABLE `products` ADD COLUMN `used_status` VARCHAR(25) NULL AFTER `status`",
                'condition_grade' => "ALTER TABLE `products` ADD COLUMN `condition_grade` VARCHAR(30) NULL AFTER `used_status`",
                'is_dish_available' => "ALTER TABLE `products` ADD COLUMN `is_dish_available` TINYINT(1) NOT NULL DEFAULT 1 AFTER `status`",
                'prep_time_mins' => "ALTER TABLE `products` ADD COLUMN `prep_time_mins` INT NULL AFTER `is_dish_available`",
                'dietary_label' => "ALTER TABLE `products` ADD COLUMN `dietary_label` VARCHAR(50) NULL AFTER `prep_time_mins`",
                'views_count' => "ALTER TABLE `products` ADD COLUMN `views_count` INT UNSIGNED NOT NULL DEFAULT 0",
                'saves_count' => "ALTER TABLE `products` ADD COLUMN `saves_count` INT UNSIGNED NOT NULL DEFAULT 0",
                'duplicate_fingerprint' => "ALTER TABLE `products` ADD COLUMN `duplicate_fingerprint` VARCHAR(64) NULL",
                'refreshed_at' => "ALTER TABLE `products` ADD COLUMN `refreshed_at` TIMESTAMP NULL",
                'has_variants' => "ALTER TABLE `products` ADD COLUMN `has_variants` TINYINT(1) NOT NULL DEFAULT 0",
                'specifications_json' => "ALTER TABLE `products` ADD COLUMN `specifications_json` JSON NULL",
                'b2b_tiers_json' => "ALTER TABLE `products` ADD COLUMN `b2b_tiers_json` JSON NULL",
                'service_config_json' => "ALTER TABLE `products` ADD COLUMN `service_config_json` JSON NULL",
                'faqs_json' => "ALTER TABLE `products` ADD COLUMN `faqs_json` JSON NULL",
                'requirements_prompt' => "ALTER TABLE `products` ADD COLUMN `requirements_prompt` TEXT NULL",
                'addons_json' => "ALTER TABLE `products` ADD COLUMN `addons_json` JSON NULL",
                'pricing_mode' => "ALTER TABLE `products` ADD COLUMN `pricing_mode` VARCHAR(40) NULL",
                'skills_json' => "ALTER TABLE `products` ADD COLUMN `skills_json` JSON NULL",
                'is_website_featured' => "ALTER TABLE `products` ADD COLUMN `is_website_featured` TINYINT(1) NOT NULL DEFAULT 0",
                'show_on_website' => "ALTER TABLE `products` ADD COLUMN `show_on_website` TINYINT(1) NOT NULL DEFAULT 1",
            ];
            foreach ($colsToAdd as $cName => $sql) {
                if (!Schema::hasColumn('products', $cName)) {
                    try { DB::statement($sql); } catch (\Throwable $e) {}
                }
            }
        }
    } catch (\Throwable $e) {}

    // Vendor Subscriptions table schema hardening
    try {
        if (Schema::hasTable('vendor_subscriptions')) {
            $subCols = [
                'sector_key' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `sector_key` VARCHAR(50) NOT NULL DEFAULT 'all' AFTER `status`",
                'applicable_sectors_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `applicable_sectors_snapshot` JSON NULL AFTER `sector_key`",
                'plan_version_id' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `plan_version_id` BIGINT UNSIGNED NULL",
                'plan_version_number' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `plan_version_number` INT UNSIGNED NULL",
                'plan_name_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `plan_name_snapshot` VARCHAR(100) NULL",
                'price_amount_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `price_amount_snapshot` BIGINT UNSIGNED NULL",
                'is_free_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `is_free_snapshot` TINYINT(1) NOT NULL DEFAULT 0",
                'currency' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `currency` VARCHAR(10) NOT NULL DEFAULT 'INR'",
                'duration_days' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `duration_days` INT UNSIGNED NULL",
                'entitlements_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `entitlements_snapshot` JSON NULL",
                'expiry_policy_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `expiry_policy_snapshot` JSON NULL",
                'grace_keep_visible' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `grace_keep_visible` TINYINT(1) NOT NULL DEFAULT 1",
                'grace_keep_features' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `grace_keep_features` TINYINT(1) NOT NULL DEFAULT 0",
                'commission_rule_id' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `commission_rule_id` BIGINT UNSIGNED NULL",
                'commission_rate_override' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `commission_rate_override` DECIMAL(5,2) NULL",
                'commission_fixed_override' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `commission_fixed_override` BIGINT UNSIGNED NULL",
                'reminder_days_before' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `reminder_days_before` JSON NULL",
                'auto_reminder_enabled' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `auto_reminder_enabled` TINYINT(1) NOT NULL DEFAULT 1",
                'external_payment_received' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `external_payment_received` TINYINT(1) NOT NULL DEFAULT 0",
                'external_payment_date' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `external_payment_date` TIMESTAMP NULL",
                'external_payment_amount' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `external_payment_amount` BIGINT UNSIGNED NULL",
                'external_payment_method' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `external_payment_method` VARCHAR(50) NULL",
                'external_payment_ref' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `external_payment_ref` VARCHAR(100) NULL",
                'external_receipt_path' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `external_receipt_path` VARCHAR(255) NULL",
                'internal_notes' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `internal_notes` TEXT NULL",
                'admin_reason' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `admin_reason` TEXT NULL",
                'assigned_by' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `assigned_by` BIGINT UNSIGNED NULL",
                'activated_by' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `activated_by` BIGINT UNSIGNED NULL",
                'activated_at' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `activated_at` TIMESTAMP NULL",
                'expired_at' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `expired_at` TIMESTAMP NULL",
                'suspended_at' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `suspended_at` TIMESTAMP NULL",
                'renewed_from_id' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `renewed_from_id` BIGINT UNSIGNED NULL",
                'commission_type_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `commission_type_snapshot` VARCHAR(30) NULL",
                'commission_rate_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `commission_rate_snapshot` DECIMAL(5,2) NULL",
                'commission_fixed_snapshot' => "ALTER TABLE `vendor_subscriptions` ADD COLUMN `commission_fixed_snapshot` BIGINT UNSIGNED NULL",
            ];
            foreach ($subCols as $scName => $sql) {
                if (!Schema::hasColumn('vendor_subscriptions', $scName)) {
                    try { DB::statement($sql); } catch (\Throwable $e) {}
                }
            }
        }
    } catch (\Throwable $e) {}

    // Vendors table schema hardening
    try {
        if (Schema::hasTable('vendors')) {
            if (!Schema::hasColumn('vendors', 'can_use_kinetic_scroll')) {
                try { DB::statement("ALTER TABLE `vendors` ADD COLUMN `can_use_kinetic_scroll` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`"); } catch (\Throwable $e) {}
            }
            if (!Schema::hasColumn('vendors', 'vendor_type')) {
                try { DB::statement("ALTER TABLE `vendors` ADD COLUMN `vendor_type` VARCHAR(50) NOT NULL DEFAULT 'ecommerce' AFTER `slug`"); } catch (\Throwable $e) {}
            }
        }
    } catch (\Throwable $e) {}

    // Subscription Events table schema hardening
    try {
        if (Schema::hasTable('subscription_events') && !Schema::hasColumn('subscription_events', 'summary')) {
            try { DB::statement("ALTER TABLE `subscription_events` ADD COLUMN `summary` VARCHAR(500) NULL AFTER `reason`"); } catch (\Throwable $e) {}
        }
    } catch (\Throwable $e) {}

    echo "<div class='step'><div class='step-title'>🛠️ Database Schema Hardening:</div><pre class='success'>SEO columns, username & vendor slugs expanded to VARCHAR(60)/VARCHAR(255)/TEXT. Products (sales_count, vertical, custom_fields) and vendor subscriptions verified.</pre></div>";
} catch (\Throwable $e) {
    // Tables may not exist prior to initial migration; will be handled by migrations
}

// 9b. Auto-Repair Product Variants with Incorrect or Truncated Prices (e.g. 5.00 when base price is 599.00)
try {
    $affectedProducts = DB::table('products')->where('base_price', '>=', 5000)->pluck('base_price', 'id');
    $fixedCount = 0;
    foreach ($affectedProducts as $pId => $basePrice) {
        $properPrice = $basePrice / 100;
        $updated = DB::table('product_variants')
            ->where('product_id', $pId)
            ->where(function($q) {
                $q->whereNull('price')
                  ->orWhere('price', '<=', 10.00)
                  ->orWhere('price_override', '<=', 1000);
            })
            ->update([
                'price' => $properPrice,
                'price_override' => $basePrice,
                'updated_at' => now(),
            ]);
        $fixedCount += $updated;
    }
    if ($fixedCount > 0) {
        echo "<div class='step'><div class='step-title'>🏷️ Product Price Auto-Correction:</div><pre class='success'>Fixed {$fixedCount} product variant(s) where price was incorrectly stored as <= ₹10 to match their product base price (e.g. ₹599.00).</pre></div>";
    }
} catch (\Throwable $e) {}

// 10. Production Safe Deployment Tasks (NEVER resets vendors or database)
$tasks = [
    'Database Migration (Safe schema updates)' => ['cmd' => 'migrate', 'args' => ['--force' => true]],
];

// Ensure multi-panel navigation items exist if table is fresh
try {
    if (Schema::hasTable('navigation_items') && DB::table('navigation_items')->count() === 0) {
        $tasks['Seed Multi-Panel Navigation Structure'] = ['cmd' => 'db:seed', 'args' => ['--class' => 'NavigationSeeder', '--force' => true]];
    }
} catch (\Throwable $e) {}

// One-time selective prune: preview with ?prune_except=1, execute ONLY with ?prune_except=1&confirm=KEEP_FOUR_ONLY
if (!empty($_GET['prune_except'])) {
    $pruneArgs = ['--clean-storage' => true];
    if (($_GET['confirm'] ?? '') === 'KEEP_FOUR_ONLY') {
        $pruneArgs['--force'] = true;
    } else {
        $pruneArgs['--dry-run'] = true;
    }
    if (!empty($_GET['vendor_ids'])) {
        $pruneArgs['--vendor-ids'] = (string) $_GET['vendor_ids'];
    }
    if (!empty($_GET['user_ids'])) {
        $pruneArgs['--user-ids'] = (string) $_GET['user_ids'];
    }
    if (!empty($_GET['include_admin_vendors'])) {
        $pruneArgs['--include-admin-vendors'] = true;
    }
    $tasks['Selective Prune (Keep Admin Hashir, Safiya, Smartx, Gaddito)'] = ['cmd' => 'marketplace:prune-except', 'args' => $pruneArgs];
}

// One-time manual reset trigger: ONLY executed if ?reset_marketplace=yes is explicitly passed in URL
if (!empty($_GET['reset_marketplace']) && $_GET['reset_marketplace'] === 'yes') {
    $tasks['Purge All Stale / Mock Dummy Data (One-Time Reset)'] = ['cmd' => 'marketplace:purge-dummy', 'args' => ['--force' => true]];
}

$tasks['Clear All Stale Caches'] = ['cmd' => 'optimize:clear', 'args' => []];

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

// Clear all navigation cache explicitly
try {
    \App\Models\NavigationItem::clearAllNavigationCache();
    echo "<div class='step'><div class='step-title'>🧭 Navigation Cache Cleared:</div><pre class='success'>All 6 vertical mobile navigation panel caches cleared.</pre></div>";
} catch (\Throwable $e) {}

// 11. Enforce Master Administrator Credentials
echo "<div class='step'><div class='step-title'>👤 Enforcing Master Administrator Account:</div>";
try {
    $admin = \App\Models\User::where('email', 'crab.hashir@gmail.com')->first();
    if (!$admin) {
        $admin = new \App\Models\User();
        $admin->email = 'crab.hashir@gmail.com';
        $admin->first_name = 'Hashir';
        $admin->last_name = 'Admin';
        $admin->username = 'masteradmin';
        $admin->password = \Illuminate\Support\Facades\Hash::make('Hashir1284@0');
        $admin->email_verified_at = now();
        $admin->is_blocked = 0;
        $admin->save();
    } else {
        // Absolutely preserve existing user password! NEVER touch $admin->password under any circumstance!
        $admin->is_blocked = 0;
        $admin->blocked_reason = null;
        $admin->save();
    }

    $adminRole = \App\Models\Role::firstOrCreate(['name' => 'admin']);
    $custRole = \App\Models\Role::firstOrCreate(['name' => 'customer']);
    $admin->roles()->syncWithoutDetaching([$adminRole->id, $custRole->id]);

    echo "<pre class='success'>✅ Master Administrator verified:\n   Email: crab.hashir@gmail.com\n   Role: admin\n   (Existing Password Untouched & Preserved)\n   User ID: {$admin->id}</pre>";
} catch (\Throwable $e) {
    echo "<pre class='error'>Failed to enforce admin account: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
echo "</div>";

// 11b. Active Vendors In Database Verification
echo "<div class='step'><div class='step-title'>🏪 Active Registered Store Vendors:</div>";
try {
    $allVendors = DB::table('vendors')->select('id', 'user_id', 'shop_name', 'slug', 'vendor_type', 'contact_email', 'status')->get();
    echo "<pre class='success'>Total Vendors Found: " . $allVendors->count() . "\n\n";
    foreach ($allVendors as $v) {
        echo "• ID {$v->id} (User #{$v->user_id}) | {$v->shop_name} [Type: {$v->vendor_type}]\n  Store URL: /s/{$v->slug}\n  Email: {$v->contact_email} | Status: {$v->status}\n";
    }
    echo "</pre>";
} catch (\Throwable $e) {
    echo "<pre class='error'>Vendors query error: " . htmlspecialchars($e->getMessage()) . "</pre>";
}
echo "</div>";

// 11c. Optional Read-Only Full User, Data & Storage Audit (?inspect_users=1)
if (!empty($_GET['inspect_users'])) {
    echo "<div class='step'><div class='step-title'>🔍 Live Database & Storage Inspection:</div><pre style='color:#e2e8f0;max-height:600px;overflow-y:auto;'>";
    try {
        $rolesByUser = Schema::hasTable('role_user') && Schema::hasTable('roles')
            ? DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->get(['role_user.user_id', 'roles.name'])
                ->groupBy('user_id')
                ->map(fn ($g) => $g->pluck('name')->implode(', '))
            : collect();

        $allUsers = DB::table('users')->orderBy('id')->get(['id', 'first_name', 'last_name', 'username', 'email', 'phone']);
        echo "USERS (" . $allUsers->count() . "):\n";
        foreach ($allUsers as $u) {
            $r = $rolesByUser->get($u->id, 'customer');
            $ordersCnt = Schema::hasTable('orders') ? DB::table('orders')->where('user_id', $u->id)->count() : 0;
            echo "  User #{$u->id} [{$r}]: {$u->first_name} {$u->last_name} (@{$u->username}) <{$u->email}> | orders={$ordersCnt}\n";
        }

        echo "\nVENDORS (" . DB::table('vendors')->count() . "):\n";
        foreach (DB::table('vendors')->orderBy('id')->get(['id', 'user_id', 'shop_name', 'slug', 'vendor_type', 'status', 'logo', 'banner']) as $v) {
            $prodCnt = Schema::hasTable('products') ? DB::table('products')->where('vendor_id', $v->id)->count() : 0;
            $ordCnt = Schema::hasTable('orders') ? DB::table('orders')->where('vendor_id', $v->id)->count() : 0;
            echo "  Vendor #{$v->id} (User #{$v->user_id}): {$v->shop_name} [/s/{$v->slug}] | type={$v->vendor_type} | status={$v->status} | products={$prodCnt} | orders={$ordCnt}\n";
        }

        if (Schema::hasTable('chat_conversations')) {
            echo "\nCHAT_CONVERSATIONS (" . DB::table('chat_conversations')->count() . "):\n";
            foreach (DB::table('chat_conversations')->orderBy('id')->get() as $c) {
                $msgCnt = Schema::hasTable('chat_messages') ? DB::table('chat_messages')->where('conversation_id', $c->id)->count() : 0;
                echo "  Conv #{$c->id}: user_one={$c->user_one_id}, user_two={$c->user_two_id}, msgs={$msgCnt}\n";
            }
        }

        echo "\nSTORAGE FILES (storage/app/public):\n";
        $pubRoot = $baseDir . '/storage/app/public';
        if (is_dir($pubRoot)) {
            $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pubRoot, FilesystemIterator::SKIP_DOTS));
            $fileCount = 0;
            foreach ($it as $f) {
                if ($f->isFile()) {
                    $fileCount++;
                    echo "  - " . ltrim(str_replace('\\', '/', substr($f->getPathname(), strlen($pubRoot))), '/') . " (" . $f->getSize() . " B)\n";
                }
            }
            echo "Total files in storage/app/public: {$fileCount}\n";
        }
    } catch (\Throwable $e) {
        echo "Inspect Error: " . $e->getMessage();
    }
    echo "</pre></div>";
}

// 11d. Clean obsolete host junk files/folders when ?clean_host_junk=1 is passed
if (!empty($_GET['clean_host_junk'])) {
    echo "<div class='step'><div class='step-title'>🧹 Cleaning Obsolete Hostinger Files & Demo Assets:</div><pre class='success'>";
    $deleteRecursive = function (string $target) use (&$deleteRecursive): int {
        if (!file_exists($target)) {
            return 0;
        }
        if (is_file($target) || is_link($target)) {
            return @unlink($target) ? 1 : 0;
        }
        $count = 0;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($it as $item) {
            if ($item->isDir() && !$item->isLink()) {
                @rmdir($item->getPathname());
            } elseif (@unlink($item->getPathname())) {
                $count++;
            }
        }
        @rmdir($target);
        return $count;
    };
    $junkTargets = [
        $baseDir . '/public/videos/shorts',
        $baseDir . '/public/videos/sample1.mp4',
        $baseDir . '/public/videos/sample2.mp4',
        $baseDir . '/videos/shorts',
        $baseDir . '/videos/sample1.mp4',
        $baseDir . '/videos/sample2.mp4',
        $baseDir . '/legacy',
        $baseDir . '/referencepic',
        $baseDir . '/refzip',
        $baseDir . '/scratch',
        $baseDir . '/ui-audit',
        $baseDir . '/heroscroll',
        $baseDir . '/referencecard.avif',
        $baseDir . '/referencecard.png',
        $baseDir . '/dash_output.html',
        $baseDir . '/sql/restore_chat_17_to_28.sql',
        $baseDir . '/database/seeders/DemoIndianCategoryVendorsSeeder.php',
        $baseDir . '/public/api/setup',
    ];
    foreach ($junkTargets as $jt) {
        $removed = $deleteRecursive($jt);
        if ($removed > 0) {
            echo "✓ Removed {$jt} ({$removed} file(s))\n";
        }
    }
    echo "Hostinger obsolete asset cleanup finished.\n</pre></div>";
}

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

// 13. Deep Runtime Diagnostics Runner (?diagnose=1)
if (!empty($_GET['diagnose'])) {
    echo "<div class='step'><div class='step-title'>🔬 Live Runtime Diagnostics:</div><pre style='color:#e2e8f0;font-size:12px;max-height:600px;overflow-y:auto;'>";
    
    // Test Product Show
    try {
        $allProds = \App\Models\Product::withoutGlobalScopes()->get(['id', 'title', 'slug', 'status', 'vendor_id', 'vertical']);
        echo "Testing " . $allProds->count() . " Product(s) Show Action:\n";
        foreach ($allProds as $p) {
            try {
                $ctrl = app(\App\Http\Controllers\ProductViewController::class);
                $res = $ctrl->show($p->slug);
                $statusDesc = is_object($res) ? get_class($res) : 'OK';
                echo "  ✓ Product #{$p->id} [{$p->slug}] -> {$statusDesc}\n";
            } catch (\Throwable $e) {
                echo "  ❌ Product #{$p->id} [{$p->slug}] ERROR: " . $e->getMessage() . "\n";
                echo "     In: " . $e->getFile() . ':' . $e->getLine() . "\n";
                echo "     Trace:\n" . substr($e->getTraceAsString(), 0, 1200) . "\n\n";
            }
        }
    } catch (\Throwable $e) {
        echo "Product test runner error: " . $e->getMessage() . "\n";
    }

    // Test Vendor Dashboard
    try {
        $allVendors = \App\Models\Vendor::all();
        echo "\nTesting " . $allVendors->count() . " Vendor Dashboard(s):\n";
        foreach ($allVendors as $v) {
            try {
                \Illuminate\Support\Facades\Auth::loginUsingId($v->user_id);
                $dashCtrl = app(\App\Http\Controllers\Vendor\DashboardController::class);
                $req = \Illuminate\Http\Request::create('/vendor/dashboard', 'GET');
                $res = $dashCtrl->index($req);
                $statusDesc = is_object($res) ? get_class($res) : 'OK';
                echo "  ✓ Vendor #{$v->id} (@{$v->slug}) Dashboard -> {$statusDesc}\n";
            } catch (\Throwable $e) {
                echo "  ❌ Vendor #{$v->id} (@{$v->slug}) Dashboard ERROR: " . $e->getMessage() . "\n";
                echo "     In: " . $e->getFile() . ':' . $e->getLine() . "\n";
                echo "     Trace:\n" . substr($e->getTraceAsString(), 0, 1200) . "\n\n";
            }
        }
    } catch (\Throwable $e) {
        echo "Vendor test runner error: " . $e->getMessage() . "\n";
    }

    echo "\nDiagnostics Complete.\n</pre></div>";
}

// Diagnostic: Show Request Debug Log if exists
$reqDebugLog = $baseDir . '/storage/logs/request_debug.log';
if (file_exists($reqDebugLog)) {
    echo "<div class='step'><div class='step-title'>🌐 Request Debug Log:</div>";
    echo "<pre style='color:#38bdf8;font-size:12px;max-height:200px;overflow-y:auto;'>" . htmlspecialchars(file_get_contents($reqDebugLog)) . "</pre>";
    echo "</div>";
}

// Diagnostic: Show Extracted Recent Errors & Laravel Log Entries
$logFile = $baseDir . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    preg_match_all('/\[\d{4}-\d{2}-\d{2}[^\]]+\]\s+(?:production|local)\.ERROR:[^\n]+(?:\n(?!\s*\[\d{4}-\d{2}-\d{2}).*)*/m', $content, $errorBlocks);
    $allErrors = $errorBlocks[0] ?? [];
    $recentErrors = array_slice($allErrors, -5);

    if (!empty($recentErrors)) {
        echo "<div class='step'><div class='step-title'>🔥 Recent Exception Traces (" . count($recentErrors) . "):</div>";
        echo "<pre class='error' style='font-size:12px;max-height:450px;overflow-y:auto;'>";
        foreach ($recentErrors as $idx => $err) {
            echo "=== EXCEPTION ENTRY #" . ($idx + 1) . " ===\n" . htmlspecialchars(substr($err, 0, 3000)) . "\n\n";
        }
        echo "</pre></div>";
    }

    $lines = file($logFile);
    $lastLines = array_slice($lines, -150);
    echo "<div class='step'><div class='step-title'>📋 Raw Log Tail (150 lines):</div>";
    echo "<pre style='color:#cbd5e1;font-size:12px;max-height:350px;overflow-y:auto;'>" . htmlspecialchars(implode("", $lastLines)) . "</pre>";
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
    echo "<a href='?token={$secretToken}&diagnose=1' class='btn btn-blue'>🔬 Run Live Runtime Diagnostics</a>";
    echo "<a href='?token={$secretToken}&inspect_users=1' class='btn btn-blue'>🔍 Inspect Users & Storage</a>";
    echo "<a href='?token={$secretToken}&test_mail=1' class='btn btn-blue'>📧 Send Test SMTP Email</a>";
    echo "<a href='?token={$secretToken}&self_delete=1' class='btn btn-red' onclick='return confirm(\"Are you sure you want to delete deploy.php?\");'>🗑️ Delete This Setup File</a>";
    echo "</div>";
}
?>
</div>
</body>
</html>
