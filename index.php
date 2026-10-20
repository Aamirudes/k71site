<?php

/**
 * Laravel - Gateway for Shared Hosting (Hostinger)
 * Forwards requests cleanly to public/index.php
 */

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

require_once __DIR__ . '/public/index.php';
