<?php

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = dirname(__DIR__, 2) . '/index.php';

require_once dirname(__DIR__, 2) . '/index.php';
