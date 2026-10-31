<?php
/**
 * Database Connection & User Session Bridge
 * File: includes/db.php
 * K71 Marketplace & Instagram Direct DM Clone
 */

if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.cookie_httponly', '1');
    @ini_set('session.use_only_cookies', '1');
    @session_start();
}

// 1. Locate and parse environment variables (.env)
$basePath = dirname(__DIR__);
if (!file_exists($basePath . '/.env') && file_exists(dirname($basePath) . '/.env')) {
    $basePath = dirname($basePath);
}
$envFile = $basePath . '/.env';
$envVars = [];

if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            $envVars[$key] = $val;
        }
    }
}

// 2. Establish PDO Connection
$pdo = null;
$dbConnection = $envVars['DB_CONNECTION'] ?? 'mysql';

try {
    if ($dbConnection === 'sqlite') {
        $dbPath = $envVars['DB_DATABASE'] ?? ($basePath . '/database/database.sqlite');
        if (!file_exists($dbPath) && file_exists(dirname($basePath) . '/database/database.sqlite')) {
            $dbPath = dirname($basePath) . '/database/database.sqlite';
        }
        $pdo = new PDO("sqlite:" . $dbPath, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 10,
        ]);
        // Enable foreign keys and WAL mode on SQLite
        $pdo->exec("PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;");
    } else {
        $host = $envVars['DB_HOST'] ?? '127.0.0.1';
        $port = $envVars['DB_PORT'] ?? '3306';
        $dbname = $envVars['DB_DATABASE'] ?? 'u891587304_shopdost';
        $username = $envVars['DB_USERNAME'] ?? 'u891587304_shopdost';
        $password = $envVars['DB_PASSWORD'] ?? '';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);
    }
} catch (\Throwable $e) {
    $pdo = null;
}

// 2b. If direct PDO connection was not established, bootstrap Laravel to obtain official PDO
if (!$pdo) {
    if (class_exists('\Illuminate\Support\Facades\DB')) {
        try {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } catch (\Throwable $ex) {
            $pdo = null;
        }
    } else {
        $candidateAutoloads = [
            $basePath . '/vendor/autoload.php',
            dirname($basePath) . '/vendor/autoload.php',
            dirname(dirname($basePath)) . '/vendor/autoload.php'
        ];
        foreach ($candidateAutoloads as $al) {
            if (file_exists($al)) {
                $rootD = dirname($al);
                if (file_exists($rootD . '/bootstrap/app.php')) {
                    try {
                        require_once $al;
                        $appBoot = require_once $rootD . '/bootstrap/app.php';
                        $appBoot->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
                        $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
                        break;
                    } catch (\Throwable $bt) {}
                }
            }
        }
    }
}

// 2c. Self-healing schema validation: ensure chat_conversations has status and requested_by
if ($pdo) {
    try {
        if ($dbConnection === 'sqlite') {
            @$pdo->exec("ALTER TABLE chat_conversations ADD COLUMN status TEXT DEFAULT 'accepted'");
            @$pdo->exec("ALTER TABLE chat_conversations ADD COLUMN requested_by INTEGER NULL");
        } else {
            $checkCols = $pdo->query("SHOW COLUMNS FROM `chat_conversations` LIKE 'status'")->fetch();
            if (!$checkCols) {
                $pdo->exec("ALTER TABLE `chat_conversations` ADD COLUMN `status` VARCHAR(20) NOT NULL DEFAULT 'accepted'");
            }
            $checkCols2 = $pdo->query("SHOW COLUMNS FROM `chat_conversations` LIKE 'requested_by'")->fetch();
            if (!$checkCols2) {
                $pdo->exec("ALTER TABLE `chat_conversations` ADD COLUMN `requested_by` BIGINT UNSIGNED NULL");
            }
            $checkCols3 = $pdo->query("SHOW COLUMNS FROM `chat_messages` LIKE 'attachment_url'")->fetch();
            if (!$checkCols3) {
                $pdo->exec("ALTER TABLE `chat_messages` ADD COLUMN `attachment_url` VARCHAR(500) NULL");
                $pdo->exec("ALTER TABLE `chat_messages` ADD COLUMN `attachment_type` VARCHAR(30) NULL");
            }
            $checkCols4 = $pdo->query("SHOW COLUMNS FROM `chat_messages` LIKE 'reply_to_id'")->fetch();
            if (!$checkCols4) {
                $pdo->exec("ALTER TABLE `chat_messages` ADD COLUMN `reply_to_id` BIGINT UNSIGNED NULL");
                $pdo->exec("ALTER TABLE `chat_messages` ADD COLUMN `reply_to_body` TEXT NULL");
                $pdo->exec("ALTER TABLE `chat_messages` ADD COLUMN `reply_to_sender_name` VARCHAR(100) NULL");
            }
        }
    } catch (\Throwable $st) {}
}

// Aliases for compatibility
$conn = $pdo;
$db = $pdo;

// 3. User Session Resolution (Bridges $_SESSION and Laravel Session)
if (!function_exists('get_current_user_id')) {
function get_current_user_id(): ?int
{
    // Check direct PHP session variables first
    if (!empty($_SESSION['user_id'])) {
        return (int) $_SESSION['user_id'];
    }
    if (!empty($_SESSION['uid'])) {
        return (int) $_SESSION['uid'];
    }
    if (!empty($_SESSION['id'])) {
        return (int) $_SESSION['id'];
    }

    // Check fast bridge cookie
    if (!empty($_COOKIE['k71_uid']) && is_numeric($_COOKIE['k71_uid'])) {
        $uid = (int) $_COOKIE['k71_uid'];
        $_SESSION['user_id'] = $uid;
        return $uid;
    }

    // Check if Laravel Auth is available in runtime
    if (class_exists('\Illuminate\Support\Facades\Auth')) {
        $laravelId = \Illuminate\Support\Facades\Auth::id();
        if ($laravelId) {
            $_SESSION['user_id'] = (int) $laravelId;
            return (int) $laravelId;
        }
    }

    // Fallback: Check Laravel file & database session from cookie
    global $basePath, $pdo, $envVars;
    
    // Detect session cookie value from any candidate cookie
    $cookieVal = null;
    foreach ($_COOKIE as $k => $v) {
        if ($k === 'k71-session' || $k === 'laravel-session' || str_contains($k, 'session')) {
            if (!empty($v) && is_string($v)) {
                $cookieVal = $v;
                break;
            }
        }
    }

    if ($cookieVal) {
        // Attempt decryption with APP_KEY if available
        $rawSessionId = null;
        if (!empty($envVars['APP_KEY'])) {
            $appKey = $envVars['APP_KEY'];
            $key = str_starts_with($appKey, 'base64:') ? base64_decode(substr($appKey, 7)) : $appKey;
            $payload = json_decode(base64_decode(urldecode($cookieVal)), true);
            if (isset($payload['iv'], $payload['value'])) {
                $dec = openssl_decrypt(base64_decode($payload['value']), 'AES-256-CBC', $key, OPENSSL_RAW_DATA, base64_decode($payload['iv']));
                if ($dec) {
                    $unser = @unserialize($dec);
                    $candidate = ($unser !== false && is_string($unser)) ? $unser : $dec;
                    $rawSessionId = preg_replace('/[^a-zA-Z0-9_-]/', '', $candidate);
                }
            }
        }

        $sessionIds = array_filter([$rawSessionId, preg_replace('/[^a-zA-Z0-9_-]/', '', $cookieVal)]);

        // 1. Try file session driver
        $sessionDir = $basePath . '/storage/framework/sessions';
        if (!is_dir($sessionDir) && is_dir(dirname($basePath) . '/storage/framework/sessions')) {
            $sessionDir = dirname($basePath) . '/storage/framework/sessions';
        }

        if (is_dir($sessionDir)) {
            foreach ($sessionIds as $sId) {
                $directFile = $sessionDir . '/' . $sId;
                if (file_exists($directFile)) {
                    $content = @file_get_contents($directFile);
                    if ($content && preg_match('/login_web_[a-f0-9]+";i:(\d+)/', $content, $matches)) {
                        $uid = (int) $matches[1];
                        $_SESSION['user_id'] = $uid;
                        return $uid;
                    }
                }
            }

            // Fallback: Check most recent session files
            $files = glob($sessionDir . '/*');
            if ($files) {
                usort($files, function ($a, $b) {
                    return filemtime($b) <=> filemtime($a);
                });
                foreach (array_slice($files, 0, 10) as $f) {
                    $content = @file_get_contents($f);
                    if ($content && preg_match('/login_web_[a-f0-9]+";i:(\d+)/', $content, $matches)) {
                        $uid = (int) $matches[1];
                        $_SESSION['user_id'] = $uid;
                        return $uid;
                    }
                }
            }
        }

        // 2. Try database session driver if PDO available
        if ($pdo) {
            foreach ($sessionIds as $sId) {
                try {
                    $stmtDb = $pdo->prepare("SELECT user_id FROM sessions WHERE id = ? AND user_id IS NOT NULL LIMIT 1");
                    $stmtDb->execute([$sId]);
                    $dbUid = $stmtDb->fetchColumn();
                    if ($dbUid) {
                        $uid = (int) $dbUid;
                        $_SESSION['user_id'] = $uid;
                        return $uid;
                    }
                } catch (\Throwable $t) {}
            }
        }
    }

    return null;
}
}

// 4. Authentication Guard for APIs
if (!function_exists('require_auth')) {
function require_auth(): int
{
    $userId = get_current_user_id();
    if (!$userId) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized. Please log in to access messages.'
        ]);
        exit;
    }
    return $userId;
}
}

// 5. User Helper
if (!function_exists('format_user_avatar')) {
function format_user_avatar(?string $avatar, string $name): string
{
    if (!empty($avatar)) {
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://') || str_starts_with($avatar, '/')) {
            return $avatar;
        }
        return '/storage/' . ltrim($avatar, '/');
    }
    // High-resolution UI Avatar fallback
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0095F6&color=ffffff&size=200&bold=true&format=svg';
}
}

if (!function_exists('get_chat_user_info')) {
function get_chat_user_info(\PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.username, 
            u.first_name, 
            u.last_name, 
            u.avatar, 
            u.email,
            v.shop_name
        FROM users u
        LEFT JOIN vendors v ON v.user_id = u.id
        WHERE u.id = ? AND u.deleted_at IS NULL 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $personName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    if ($personName === '') {
        $personName = $row['username'] ?? 'User';
    }

    $displayName = $personName;
    if (!empty($row['shop_name'])) {
        $displayName = $row['shop_name'] . ' (' . $personName . ')';
    }

    return [
        'id' => (int) $row['id'],
        'username' => $row['username'] ?? ('user_' . $row['id']),
        'name' => $displayName,
        'shop_name' => $row['shop_name'] ?? null,
        'profile_pic' => format_user_avatar($row['avatar'] ?? null, $displayName),
        'email' => $row['email'] ?? '',
    ];
}
}

// 6. Time ago formatter (e.g. 5m, 2h, 1d, 3w)
if (!function_exists('format_chat_time_ago')) {
function format_chat_time_ago(string $datetime): string
{
    $time = strtotime($datetime);
    if (!$time) {
        return 'now';
    }
    $diff = time() - $time;
    if ($diff < 60) {
        return 'now';
    }
    if ($diff < 3600) {
        return floor($diff / 60) . 'm';
    }
    if ($diff < 86400) {
        return floor($diff / 3600) . 'h';
    }
    if ($diff < 604800) {
        return floor($diff / 86400) . 'd';
    }
    return floor($diff / 604800) . 'w';
}
}
