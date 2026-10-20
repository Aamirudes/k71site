<?php
/**
 * Global Header / Navigation Include
 * File: includes/header.php
 * K71 Creative Agency & Marketplace
 */

if (!isset($pdo)) {
    $dbFile = file_exists(__DIR__ . '/db.php') ? __DIR__ . '/db.php' : __DIR__ . '/../db.php';
    require_once $dbFile;
}

$pageTitle = $pageTitle ?? 'Direct Messages — K71';
$currentUserId = get_current_user_id();
$currentUser = $currentUserId ? get_chat_user_info($pdo, $currentUserId) : null;
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="description" content="Instagram Direct style messaging on K71 Marketplace.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #FAFAFA;
            color: #262626;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        /* Top Navigation Header */
        .k71-global-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #FFFFFF;
            border-bottom: 1px solid #DBDBDB;
            padding: 0 16px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .k71-nav-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #262626;
        }
        .k71-brand-logo {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: #0600c2;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 15px;
            letter-spacing: -0.5px;
        }
        .k71-brand-title {
            font-weight: 800;
            font-size: 18px;
            letter-spacing: -0.5px;
        }
        .k71-nav-links {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .k71-nav-link {
            text-decoration: none;
            color: #737373;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 10px;
            border-radius: 8px;
            transition: all 0.15s ease;
        }
        .k71-nav-link:hover, .k71-nav-link.active {
            color: #0600c2;
            background: rgba(6, 0, 194, 0.05);
        }
        .k71-nav-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .k71-user-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
            border: 1px solid #DBDBDB;
        }
        .k71-user-name {
            font-size: 13px;
            font-weight: 700;
            color: #262626;
        }
        .k71-auth-btn {
            background: #0095F6;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 14px;
            border-radius: 8px;
            transition: background 0.15s ease;
        }
        .k71-auth-btn:hover {
            background: #1877F2;
        }
        @media (max-width: 640px) {
            .k71-nav-links .hide-mobile { display: none; }
        }
    </style>
</head>
<body>

<header class="k71-global-nav">
    <div style="display: flex; align-items: center; gap: 20px;">
        <a href="/" class="k71-nav-brand">
            <div class="k71-brand-logo">71</div>
            <span class="k71-brand-title">K71</span>
        </a>
        <nav class="k71-nav-links">
            <a href="/" class="k71-nav-link hide-mobile">Marketplace</a>
            <a href="/shorts" class="k71-nav-link hide-mobile">Shorts</a>
            <a href="/used" class="k71-nav-link hide-mobile">Used</a>
            <a href="/messages.php" class="k71-nav-link active">Direct Messages</a>
        </nav>
    </div>

    <div class="k71-nav-user">
        <?php if ($currentUser): ?>
            <a href="/account" style="display: flex; align-items: center; gap: 8px; text-decoration: none;">
                <img src="<?= htmlspecialchars($currentUser['profile_pic'], ENT_QUOTES, 'UTF-8') ?>" 
                     alt="<?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?>" 
                     class="k71-user-avatar">
                <span class="k71-user-name hide-mobile">@<?= htmlspecialchars($currentUser['username'], ENT_QUOTES, 'UTF-8') ?></span>
            </a>
        <?php else: ?>
            <a href="/login?redirect=/messages.php" class="k71-auth-btn">Log In</a>
        <?php endif; ?>
    </div>
</header>
