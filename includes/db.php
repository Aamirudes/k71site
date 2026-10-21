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
    // If direct PDO fails, try Laravel facade if booted
    if (class_exists('\Illuminate\Support\Facades\DB')) {
        try {
            $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
        } catch (\Throwable $ex) {
            $pdo = null;
        }
    }
}

// Aliases for compatibility
$conn = $pdo;
$db = $pdo;

// 3. User Session Resolution (Bridges $_SESSION and Laravel Session)
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

    // Check if Laravel Auth is available in runtime
    if (class_exists('\Illuminate\Support\Facades\Auth')) {
        $laravelId = \Illuminate\Support\Facades\Auth::id();
        if ($laravelId) {
            $_SESSION['user_id'] = (int) $laravelId;
            return (int) $laravelId;
        }
    }

    // Fallback: Check Laravel file & database session from cookie
    global $basePath, $pdo;
    
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
        $cleanSessionId = preg_replace('/[^a-zA-Z0-9_-]/', '', $cookieVal);

        // 1. Try file session driver
        $sessionDir = $basePath . '/storage/framework/sessions';
        if (!is_dir($sessionDir) && is_dir(dirname($basePath) . '/storage/framework/sessions')) {
            $sessionDir = dirname($basePath) . '/storage/framework/sessions';
        }

        if (is_dir($sessionDir)) {
            // Check direct file matching session ID
            $directFile = $sessionDir . '/' . $cleanSessionId;
            if (file_exists($directFile)) {
                $content = @file_get_contents($directFile);
                if ($content && preg_match('/login_web_[a-f0-9]+";i:(\d+)/', $content, $matches)) {
                    $uid = (int) $matches[1];
                    $_SESSION['user_id'] = $uid;
                    return $uid;
                }
            }

            // Fallback: Check most recent files
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
            try {
                $stmtDb = $pdo->prepare("SELECT user_id FROM sessions WHERE id = ? AND user_id IS NOT NULL LIMIT 1");
                $stmtDb->execute([$cleanSessionId]);
                $dbUid = $stmtDb->fetchColumn();
                if ($dbUid) {
                    $uid = (int) $dbUid;
                    $_SESSION['user_id'] = $uid;
                    return $uid;
                }
            } catch (\Throwable $t) {
                // Ignore if sessions table does not exist
            }
        }
    }

    return null;
}

// 4. Authentication Guard for APIs
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

// 5. User Helper
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

function get_chat_user_info(\PDO $pdo, int $userId): ?array
{
    $stmt = $pdo->prepare("
        SELECT id, username, first_name, last_name, avatar, email 
        FROM users 
        WHERE id = ? AND deleted_at IS NULL 
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }

    $fullName = trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
    if ($fullName === '') {
        $fullName = $row['username'] ?? 'User';
    }

    return [
        'id' => (int) $row['id'],
        'username' => $row['username'] ?? ('user_' . $row['id']),
        'name' => $fullName,
        'profile_pic' => format_user_avatar($row['avatar'] ?? null, $fullName),
        'email' => $row['email'] ?? '',
    ];
}

// 6. Time ago formatter (e.g. 5m, 2h, 1d, 3w)
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
