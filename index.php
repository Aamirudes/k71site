<?php

/**
 * Laravel - Gateway for Shared Hosting (Hostinger)
 * Forwards requests cleanly to public/index.php
 */

define('LARAVEL_START', microtime(true));

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/'
);

// Serve static assets from public/ if matched
$publicFile = __DIR__ . '/public' . $uri;
if ($uri !== '/' && file_exists($publicFile) && !is_dir($publicFile)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
